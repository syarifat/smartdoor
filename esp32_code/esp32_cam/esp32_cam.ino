#include <WiFi.h>
#include <WiFiClientSecure.h>
#include "esp_camera.h"
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

// ==========================================
// 1. KONFIGURASI JARINGAN & API PRODUCTION
// ==========================================
const char* ssid     = "satcloud";
const char* password = "matahary02";

const char*   serverHost = "smartdoor.satcloud.tech";
const int     serverPort = 443;
const String  apiPath    = "/api/iot/percobaan-gagal";
const int     kamarId    = 1;

// ==========================================
// 2. PIN DEFINITIONS
// ==========================================
#define TRIGGER_PIN   13  // Dari PIN 14 ESP32 Utama
#define FLASH_LED_PIN  4  // Flash LED bawaan ESP32-CAM AI-Thinker
#define RED_LED_PIN   33  // LED merah kecil di belakang board (Active LOW)

// Pin kamera AI-Thinker (JANGAN UBAH)
#define PWDN_GPIO_NUM     32
#define RESET_GPIO_NUM    -1
#define XCLK_GPIO_NUM      0
#define SIOD_GPIO_NUM     26
#define SIOC_GPIO_NUM     27
#define Y9_GPIO_NUM       35
#define Y8_GPIO_NUM       34
#define Y7_GPIO_NUM       39
#define Y6_GPIO_NUM       36
#define Y5_GPIO_NUM       21
#define Y4_GPIO_NUM       19
#define Y3_GPIO_NUM       18
#define Y2_GPIO_NUM        5
#define VSYNC_GPIO_NUM    25
#define HREF_GPIO_NUM     23
#define PCLK_GPIO_NUM     22

// ==========================================
// 3. HELPER: INDIKATOR LED DEBUG VISUAL
// ==========================================

// Kedip Flash cepat N kali → menandai tahapan
void kedipFlash(int n) {
  for (int i = 0; i < n; i++) {
    digitalWrite(FLASH_LED_PIN, HIGH);
    delay(80);
    digitalWrite(FLASH_LED_PIN, LOW);
    delay(120);
  }
}

// Kedip Red cepat N kali
void kedipRed(int n) {
  for (int i = 0; i < n; i++) {
    digitalWrite(RED_LED_PIN, LOW);  // Active LOW
    delay(80);
    digitalWrite(RED_LED_PIN, HIGH);
    delay(120);
  }
}

// Flash menyala PANJANG N detik → tanda sukses upload
void flashPanjang(int ms) {
  digitalWrite(FLASH_LED_PIN, HIGH);
  delay(ms);
  digitalWrite(FLASH_LED_PIN, LOW);
}

// Red menyala terus → tanda ERROR FATAL
void redNyalaTerus() {
  digitalWrite(RED_LED_PIN, LOW); // Active LOW = ON
}

// ==========================================
// 4. LOGIKA PENGAMBILAN & PENGIRIMAN FOTO
// ==========================================
void ambilDanKirimFoto() {
  Serial.println("\n=== [TRIGGER] Mulai proses foto ===");

  // --- TAHAP 1: Pemanasan Sensor Kamera (WAJIB, agar tidak hitam!) ---
  // Sensor OV2640 butuh beberapa frame untuk auto-exposure menyesuaikan diri
  Serial.println("[1/5] Pemanasan sensor kamera (3 frame dummy)...");
  
  // Nyalakan flash lebih awal agar sensor bisa adjust eksposur terhadap cahaya flash
  digitalWrite(FLASH_LED_PIN, HIGH);
  delay(100);

  // Ambil dan buang 3 frame dummy agar AE/AWB sensor stabil
  for (int i = 0; i < 3; i++) {
    camera_fb_t* dummy = esp_camera_fb_get();
    if (dummy) {
      esp_camera_fb_return(dummy);
      Serial.printf("[1/5] Frame dummy %d dibuang.\n", i + 1);
    }
    delay(80);
  }

  // Tunggu lagi agar eksposur benar-benar stabil sebelum jepret asli
  delay(300);

  // --- TAHAP 2: Ambil Foto Asli ---
  Serial.println("[2/5] Mengambil foto asli...");

  // Ambil buffer gambar dari sensor kamera
  camera_fb_t* fb = esp_camera_fb_get();

  // Matikan Flash LED
  digitalWrite(FLASH_LED_PIN, LOW);

  if (!fb) {
    Serial.println("[ERROR] Gagal ambil foto dari kamera!");
    // Indikator GAGAL KAMERA: Red kedip cepat 5x
    kedipRed(5);
    return;
  }
  Serial.printf("[OK] Foto terambil: %dx%d, %d bytes\n", fb->width, fb->height, fb->len);
  // Indikator foto berhasil: Flash 2x pendek
  kedipFlash(2);

  // --- TAHAP 2: Koneksi SSL ke Server ---
  Serial.println("[2/5] Menghubungkan ke server HTTPS...");
  // Indikator: Red berkedip lambat selama connecting
  WiFiClientSecure client;
  client.setInsecure();
  client.setTimeout(20); // 20 detik timeout

  bool connected = false;
  for (int attempt = 1; attempt <= 3; attempt++) {
    Serial.printf("[2/5] Percobaan koneksi %d/3...\n", attempt);
    digitalWrite(RED_LED_PIN, LOW);
    delay(300);
    digitalWrite(RED_LED_PIN, HIGH);

    if (client.connect(serverHost, serverPort)) {
      connected = true;
      break;
    }
    delay(1000);
  }

  if (!connected) {
    Serial.println("[ERROR] Gagal koneksi SSL ke server setelah 3 percobaan!");
    // Indikator: Red + Flash bergantian 4x
    for (int i = 0; i < 4; i++) {
      digitalWrite(RED_LED_PIN, LOW);
      delay(150);
      digitalWrite(RED_LED_PIN, HIGH);
      digitalWrite(FLASH_LED_PIN, HIGH);
      delay(150);
      digitalWrite(FLASH_LED_PIN, LOW);
    }
    esp_camera_fb_return(fb);
    return;
  }
  Serial.println("[OK] Koneksi SSL berhasil!");
  kedipFlash(1); // 1 kedip flash = koneksi OK

  // --- TAHAP 3: Bangun & Kirim Request Multipart ---
  Serial.println("[3/5] Membangun HTTP request...");

  String boundary = "ESP32Boundary" + String(millis());

  String head = "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"kamar_id\"\r\n\r\n" + String(kamarId) + "\r\n";
  head += "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"foto\"; filename=\"capture.jpg\"\r\n";
  head += "Content-Type: image/jpeg\r\n\r\n";

  String tail = "\r\n--" + boundary + "--\r\n";

  uint32_t totalLen = head.length() + fb->len + tail.length();

  client.println("POST " + apiPath + " HTTP/1.1");
  client.println("Host: " + String(serverHost));
  client.println("Content-Type: multipart/form-data; boundary=" + boundary);
  client.println("Content-Length: " + String(totalLen));
  client.println("Connection: close");
  client.println();
  client.print(head);

  // Kirim data biner foto dalam chunk 1KB
  Serial.println("[4/5] Mengirim data foto...");
  uint8_t* buf    = fb->buf;
  size_t   bufLen = fb->len;
  size_t   chunk  = 1024;
  size_t   sent   = 0;
  for (size_t n = 0; n < bufLen; n += chunk) {
    size_t toWrite = (n + chunk < bufLen) ? chunk : (bufLen - n);
    client.write(buf + n, toWrite);
    sent += toWrite;
  }
  client.print(tail);
  Serial.printf("[OK] %d bytes terkirim.\n", sent);

  esp_camera_fb_return(fb); // Bebaskan memori segera

  // --- TAHAP 4: Tunggu Respons Server ---
  Serial.println("[5/5] Menunggu respons server (max 20 detik)...");
  long startMs = millis();
  bool gotResponse = false;
  while (millis() - startMs < 20000) {
    if (client.available()) {
      gotResponse = true;
      break;
    }
    delay(50);
  }

  if (!gotResponse) {
    Serial.println("[ERROR] Timeout - tidak ada respons server!");
    // Indikator TIMEOUT: Flash 3x lambat
    for (int i = 0; i < 3; i++) {
      digitalWrite(FLASH_LED_PIN, HIGH);
      delay(400);
      digitalWrite(FLASH_LED_PIN, LOW);
      delay(400);
    }
    client.stop();
    return;
  }

  // Baca status HTTP dari BARIS PERTAMA response
  String httpStatus = client.readStringUntil('\n');
  Serial.println("[SERVER STATUS] " + httpStatus);

  // Baca sisa response body untuk log
  String body = "";
  while (client.available()) {
    String line = client.readStringUntil('\n');
    if (line.startsWith("{") || line.startsWith("[")) {
      body = line;
    }
  }
  client.stop();
  Serial.println("[SERVER BODY] " + body);

  // Cek status HTTP: baris pertama "HTTP/1.1 200 OK"
  bool sukses = httpStatus.indexOf(" 200 ") >= 0 || httpStatus.indexOf(" 201 ") >= 0;

  if (sukses) {
    Serial.println("[OK] Upload foto BERHASIL!");
    // Indikator SUKSES: Flash menyala panjang 1,5 detik
    flashPanjang(1500);
  } else {
    Serial.println("[ERROR] Server menolak upload! Status: " + httpStatus);
    // Indikator GAGAL SERVER: Red + Flash bergantian 5x
    for (int i = 0; i < 5; i++) {
      digitalWrite(RED_LED_PIN, LOW);
      delay(100);
      digitalWrite(RED_LED_PIN, HIGH);
      digitalWrite(FLASH_LED_PIN, HIGH);
      delay(100);
      digitalWrite(FLASH_LED_PIN, LOW);
    }
  }

  Serial.println("=== [SELESAI] Kamera siap kembali ===\n");
}

// ==========================================
// 5. SETUP
// ==========================================
void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0); // Matikan brownout detector

  Serial.begin(115200);
  delay(100);
  Serial.println("\n\n=== ESP32-CAM BOOTING ===");

  // Init pin LED
  pinMode(FLASH_LED_PIN, OUTPUT);
  pinMode(RED_LED_PIN, OUTPUT);
  digitalWrite(FLASH_LED_PIN, LOW);
  digitalWrite(RED_LED_PIN, HIGH); // Active LOW = OFF

  // Init pin trigger
  pinMode(TRIGGER_PIN, INPUT_PULLDOWN);

  // INDIKATOR BOOT: Flash 3 kedip cepat
  Serial.println("[BOOT] Indikator boot...");
  kedipFlash(3);
  delay(300);

  // ---- Init Kamera ----
  Serial.println("[BOOT] Inisialisasi kamera...");
  camera_config_t config;
  config.ledc_channel  = LEDC_CHANNEL_0;
  config.ledc_timer    = LEDC_TIMER_0;
  config.pin_d0        = Y2_GPIO_NUM;
  config.pin_d1        = Y3_GPIO_NUM;
  config.pin_d2        = Y4_GPIO_NUM;
  config.pin_d3        = Y5_GPIO_NUM;
  config.pin_d4        = Y6_GPIO_NUM;
  config.pin_d5        = Y7_GPIO_NUM;
  config.pin_d6        = Y8_GPIO_NUM;
  config.pin_d7        = Y9_GPIO_NUM;
  config.pin_xclk      = XCLK_GPIO_NUM;
  config.pin_pclk      = PCLK_GPIO_NUM;
  config.pin_vsync     = VSYNC_GPIO_NUM;
  config.pin_href      = HREF_GPIO_NUM;
  config.pin_sscb_sda  = SIOD_GPIO_NUM;
  config.pin_sscb_scl  = SIOC_GPIO_NUM;
  config.pin_pwdn      = PWDN_GPIO_NUM;
  config.pin_reset     = RESET_GPIO_NUM;
  config.xclk_freq_hz  = 20000000;
  config.pixel_format  = PIXFORMAT_JPEG;

  if (psramFound()) {
    config.frame_size   = FRAMESIZE_VGA;
    config.jpeg_quality = 12;
    config.fb_count     = 2;
    Serial.println("[BOOT] PSRAM ditemukan, resolusi VGA");
  } else {
    config.frame_size   = FRAMESIZE_QVGA;
    config.jpeg_quality = 15;
    config.fb_count     = 1;
    Serial.println("[BOOT] Tanpa PSRAM, resolusi QVGA");
  }

  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("[ERROR FATAL] Kamera gagal init! Kode: 0x%x\n", err);
    // INDIKATOR ERROR KAMERA: Red nyala terus selamanya
    redNyalaTerus();
    for (;;) {
      // Blink flash lambat selamanya untuk tanda error
      kedipFlash(1);
      delay(1000);
    }
  }
  Serial.println("[BOOT] Kamera OK!");
  // Indikator kamera OK: Flash 1x
  kedipFlash(1);
  delay(300);

  // ---- Koneksi WiFi ----
  Serial.printf("[BOOT] Menghubungkan WiFi ke '%s'...\n", ssid);
  WiFi.begin(ssid, password);

  int wifiTimeout = 0;
  while (WiFi.status() != WL_CONNECTED && wifiTimeout < 40) {
    // Indikator WiFi connecting: Red blink cepat
    digitalWrite(RED_LED_PIN, LOW);
    delay(250);
    digitalWrite(RED_LED_PIN, HIGH);
    delay(250);
    wifiTimeout++;
    Serial.print(".");
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\n[ERROR FATAL] Gagal konek WiFi!");
    // INDIKATOR ERROR WIFI: Red + Flash bergantian selamanya
    for (;;) {
      digitalWrite(RED_LED_PIN, LOW);
      delay(200);
      digitalWrite(RED_LED_PIN, HIGH);
      digitalWrite(FLASH_LED_PIN, HIGH);
      delay(200);
      digitalWrite(FLASH_LED_PIN, LOW);
    }
  }

  Serial.printf("\n[BOOT] WiFi Terhubung! IP: %s\n", WiFi.localIP().toString().c_str());
  // INDIKATOR WIFI OK: Red nyala panjang 1 detik lalu mati
  digitalWrite(RED_LED_PIN, LOW);
  delay(1000);
  digitalWrite(RED_LED_PIN, HIGH);

  Serial.println("[READY] ESP32-CAM siap! Menunggu trigger pada GPIO 13...\n");
}

// ==========================================
// 6. LOOP UTAMA
// ==========================================
void loop() {
  // Reconnect WiFi jika putus
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WARN] WiFi terputus! Reconnecting...");
    // Indikator: Red + Flash cepat bergantian selama reconnect
    WiFi.reconnect();
    int retry = 0;
    while (WiFi.status() != WL_CONNECTED && retry < 20) {
      digitalWrite(RED_LED_PIN, LOW);
      delay(200);
      digitalWrite(RED_LED_PIN, HIGH);
      digitalWrite(FLASH_LED_PIN, HIGH);
      delay(200);
      digitalWrite(FLASH_LED_PIN, LOW);
      retry++;
    }
    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("[OK] WiFi terhubung kembali.");
    }
    return;
  }

  // Deteksi sinyal trigger HIGH dari ESP32 Utama (GPIO 14 → GPIO 13)
  if (digitalRead(TRIGGER_PIN) == HIGH) {
    Serial.println("[TRIGGER] Sinyal HIGH diterima dari ESP32 Utama!");
    // Indikator trigger diterima: Red + Flash kedip bersamaan 2x
    for (int i = 0; i < 2; i++) {
      digitalWrite(RED_LED_PIN, LOW);
      digitalWrite(FLASH_LED_PIN, HIGH);
      delay(150);
      digitalWrite(RED_LED_PIN, HIGH);
      digitalWrite(FLASH_LED_PIN, LOW);
      delay(150);
    }

    ambilDanKirimFoto();

    // Anti-spam: tunggu 5 detik sebelum mau trigger lagi
    Serial.println("[WAIT] Cooldown 5 detik...");
    delay(5000);
  }

  delay(10);
}
