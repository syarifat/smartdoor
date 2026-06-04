#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include "esp_camera.h"
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

// ==========================================
// 1. KONFIGURASI JARINGAN & API PRODUCTION
// ==========================================
const char* ssid     = "Farhan";
const char* password = "987654321";

const char*   serverHost = "smartdoor.satcloud.tech";
const int     serverPort = 443;
const String  apiPath    = "/api/iot/percobaan-gagal";
const int     kamarId    = 1;

// ==========================================
// 2. PIN DEFINITIONS
// ==========================================
#define TRIGGER_PIN   15  // Dari PIN 14 ESP32 Utama (Gunakan GPIO 15 agar lebih stabil)
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

// Flash menyala PANJANG N ms → tanda sukses upload
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
// 4. HELPER: KONEKSI WIFI
// ==========================================

// Koneksi WiFi dengan timeout & auto-restart.
// Dipanggil sekali saat setup (sebelum init kamera).
// Koneksi WiFi dengan timeout.
// Dipanggil sekali saat setup (sebelum init kamera).
// JANGAN restart board jika gagal connect saat startup, biarkan lanjut
// agar kamera tetap terinisialisasi dan loop() yang akan menyambung background.
void koneksiWiFi() {
  Serial.printf("[WiFi] Menghubungkan ke '%s'...\n", ssid);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  // Indikator: Red blink selama connecting
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 40) {
    digitalWrite(RED_LED_PIN, LOW);
    delay(250);
    digitalWrite(RED_LED_PIN, HIGH);
    delay(250);
    attempts++;
    Serial.print(".");
  }
  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] GAGAL konek saat startup! Melanjutkan boot...");
    // Indikator error: Red + Flash bergantian 3x
    for (int i = 0; i < 3; i++) {
      digitalWrite(RED_LED_PIN, LOW);
      delay(200);
      digitalWrite(RED_LED_PIN, HIGH);
      digitalWrite(FLASH_LED_PIN, HIGH);
      delay(200);
      digitalWrite(FLASH_LED_PIN, LOW);
    }
  } else {
    Serial.printf("[WiFi] Terhubung! IP: %s\n", WiFi.localIP().toString().c_str());
    // Indikator WiFi OK: Red menyala panjang 1 detik lalu mati
    digitalWrite(RED_LED_PIN, LOW);
    delay(1000);
    digitalWrite(RED_LED_PIN, HIGH);
  }
}

// ==========================================
// 5. LOGIKA PENGAMBILAN & PENGIRIMAN FOTO
// ==========================================
// Server: POST /api/iot/percobaan-gagal
// Form-data: { "kamar_id": int, "foto": file }
// Response: { "success": true, "message": str }
// Server: POST /api/iot/percobaan-gagal
// Form-data: { "kamar_id": int, "foto": file }
// Response: { "success": true, "message": str }
void ambilDanKirimFoto() {
  Serial.println("\n=== [TRIGGER] Mulai proses foto ===");

  // --- TAHAP 1: Flush buffer TANPA flash ---
  // Buang frame-frame lama yang ada di buffer (gelap/stale) SEBELUM flash nyala.
  Serial.println("[1/4] Flush buffer lama (tanpa flash)...");
  for (int i = 0; i < 3; i++) {
    camera_fb_t* dummy = esp_camera_fb_get();
    if (dummy) esp_camera_fb_return(dummy);
    delay(100);
  }

  // --- TAHAP 2: Flash ON → Jepret → Flash OFF (total ±1 detik) ---
  // Flash hanya menyala SAAT jepret saja — tidak lebih lama, agar maling tidak curiga
  Serial.println("[2/4] Flash ON, ambil foto (±1 detik)...");
  digitalWrite(FLASH_LED_PIN, HIGH); // Flash ON
  delay(700); // Beri waktu sensor AE menyesuaikan (~700ms), lalu jepret

  camera_fb_t* fb = esp_camera_fb_get(); // Ambil foto
  digitalWrite(FLASH_LED_PIN, LOW);      // Flash OFF segera setelah jepret

  if (!fb) {
    Serial.println("[ERROR] Gagal ambil foto dari kamera!");
    kedipRed(5);
    return;
  }
  Serial.printf("[OK] Foto terambil: %dx%d, %d bytes\n", fb->width, fb->height, fb->len);

  // --- TAHAP 2B: Hubungkan WiFi jika terputus (Capture First, Connect/Upload Second) ---
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] Terputus saat akan upload, mencoba menyambungkan kembali...");
    WiFi.begin(ssid, password);
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 12) { // Tunggu maksimal 6 detik
      digitalWrite(RED_LED_PIN, LOW);
      delay(250);
      digitalWrite(RED_LED_PIN, HIGH);
      delay(250);
      attempts++;
    }
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ERROR] WiFi tidak terhubung! Foto batal dikirim.");
    kedipRed(4);
    esp_camera_fb_return(fb); // Bebaskan frame buffer kamera
    return;
  }

  // --- TAHAP 3: Upload foto ke server via HTTPClient ---
  Serial.println("[3/4] Mengupload foto ke server...");

  WiFiClientSecure sslClient;
  sslClient.setInsecure();
  sslClient.setTimeout(15);

  HTTPClient http;
  String url = "https://" + String(serverHost) + apiPath;
  http.begin(sslClient, url);
  http.setTimeout(15000); // 15 detik timeout HTTP

  // Bangun body multipart secara manual lalu kirim via sendRequest
  String boundary = "ESP32Boundary" + String(millis());
  http.addHeader("Content-Type", "multipart/form-data; boundary=" + boundary);

  String head = "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"kamar_id\"\r\n\r\n" + String(kamarId) + "\r\n";
  head += "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"foto\"; filename=\"capture.jpg\"\r\n";
  head += "Content-Type: image/jpeg\r\n\r\n";
  String tail = "\r\n--" + boundary + "--\r\n";

  // Gabungkan semua bagian menjadi satu buffer untuk dikirim
  size_t totalLen = head.length() + fb->len + tail.length();
  uint8_t* body   = (uint8_t*) malloc(totalLen);

  if (!body) {
    Serial.println("[ERROR] Gagal alokasi memori untuk body upload!");
    kedipRed(4);
    esp_camera_fb_return(fb);
    http.end();
    return;
  }

  // Salin head + foto + tail ke buffer
  memcpy(body,                          (uint8_t*)head.c_str(), head.length());
  memcpy(body + head.length(),          fb->buf,                fb->len);
  memcpy(body + head.length() + fb->len, (uint8_t*)tail.c_str(), tail.length());

  esp_camera_fb_return(fb); // Bebaskan frame buffer kamera segera setelah dicopy

  Serial.printf("[3/4] Mengirim %d bytes...\n", totalLen);
  int httpCode = http.POST(body, totalLen);
  free(body); // Bebaskan buffer body

  Serial.printf("[3/4] HTTP Response Code: %d\n", httpCode);

  if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_CREATED) {
    String response = http.getString();
    Serial.println("[OK] Upload foto BERHASIL! Response: " + response);
  } else {
    Serial.printf("[ERROR] Upload gagal! Code: %d\n", httpCode);
    if (httpCode > 0) {
      Serial.println("[SERVER] " + http.getString());
    }
  }
  http.end();

  // --- TAHAP 4: Indikator hasil upload ---
  if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_CREATED) {
    // Indikator SUKSES: RED menyala 1 detik (stealth, bukan flash)
    digitalWrite(RED_LED_PIN, LOW);
    delay(1000);
    digitalWrite(RED_LED_PIN, HIGH);
  } else {
    // Indikator GAGAL: Red + Flash bergantian 5x
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
// 6. SETUP
// ==========================================
void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0); // Matikan brownout detector

  Serial.begin(115200);
  delay(100);
  Serial.println("\n\n=== ESP32-CAM BOOTING ===");

  // Init pin LED & trigger
  pinMode(FLASH_LED_PIN, OUTPUT);
  pinMode(RED_LED_PIN,   OUTPUT);
  pinMode(TRIGGER_PIN,   INPUT_PULLUP); // Active LOW dengan internal pull-up (plus physical pull-up 10k)
  digitalWrite(FLASH_LED_PIN, LOW);
  digitalWrite(RED_LED_PIN,   HIGH); // Active LOW = OFF

  // INDIKATOR BOOT: Flash 3 kedip cepat
  Serial.println("[BOOT] Indikator boot...");
  kedipFlash(3);
  delay(300);

  // ---- STEP 1: Koneksi WiFi DULU (sebelum init kamera) ----
  // Alasan: Init kamera + radio WiFi aktif bersamaan bisa menyebabkan
  // spike arus yang memicu brownout tersembunyi → WiFi gagal konek.
  Serial.println("[BOOT] Menghubungkan WiFi terlebih dahulu...");
  koneksiWiFi();

  // Beri jeda singkat setelah WiFi stabil sebelum init kamera
  delay(500);

  // ---- STEP 2: Init Kamera (setelah WiFi stabil) ----
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
    // Blink merah terus-menerus lambat, JANGAN me-restart agar tidak boot loop
    while (true) {
      digitalWrite(RED_LED_PIN, LOW); // ON
      delay(500);
      digitalWrite(RED_LED_PIN, HIGH); // OFF
      delay(500);
    }
  }
  Serial.println("[BOOT] Kamera OK!");
  kedipFlash(1);
  delay(300);

  Serial.println("[READY] ESP32-CAM siap! Menunggu trigger Active LOW pada GPIO 13...\n");
}

// ==========================================
// 7. LOOP UTAMA
// ==========================================
unsigned long lastWiFiCheck = 0;
unsigned long lastDebugPrint = 0;

void loop() {
  // Reconnect WiFi non-blocking secara background jika terputus (tanpa me-restart board)
  if (WiFi.status() != WL_CONNECTED) {
    if (millis() - lastWiFiCheck > 15000) { // Lakukan pengecekan setiap 15 detik
      lastWiFiCheck = millis();
      Serial.println("[WARN] WiFi terputus! Mencoba menghubungkan kembali secara background...");
      WiFi.disconnect();
      WiFi.begin(ssid, password);
    }
  }

  // Debug print state of TRIGGER_PIN setiap 2 detik untuk mempermudah pelacakan kabel
  if (millis() - lastDebugPrint > 2000) {
    lastDebugPrint = millis();
    Serial.printf("[DEBUG] Pin Trigger (GPIO %d) saat ini: %s\n", TRIGGER_PIN, (digitalRead(TRIGGER_PIN) == HIGH ? "HIGH (Idle/Diam)" : "LOW (Trigger Aktif/Memfoto)"));
  }

  // Deteksi sinyal trigger Active LOW dari ESP32 Utama (GPIO 14 → GPIO 15)
  // Pin bernilai LOW ketika dipicu oleh ESP32 Utama
  if (digitalRead(TRIGGER_PIN) == LOW) {
    Serial.println("[TRIGGER] Sinyal LOW diterima dari ESP32 Utama!");
    
    // Tunggu sedikit dan cek lagi untuk debounce singkat agar tidak false-trigger akibat noise
    delay(50);
    if (digitalRead(TRIGGER_PIN) == LOW) {
      // Indikator trigger: RED saja (TANPA flash)
      digitalWrite(RED_LED_PIN, LOW);
      delay(200);
      digitalWrite(RED_LED_PIN, HIGH);

      ambilDanKirimFoto();

      // Tunggu hingga pin kembali HIGH (idle) sebelum lanjut,
      // untuk mencegah double trigger jika proses ambil+upload sangat cepat.
      unsigned long tStartWait = millis();
      while (digitalRead(TRIGGER_PIN) == LOW && millis() - tStartWait < 5000) {
        delay(50);
      }
    }
  }

  delay(10);
}
