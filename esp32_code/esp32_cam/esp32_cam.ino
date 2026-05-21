#include <WiFi.h>
#include <WiFiClientSecure.h>
#include "esp_camera.h"
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

// ==========================================
// 1. KONFIGURASI JARINGAN & API PRODUCTION
// ==========================================
const char* ssid = "satcloud";
const char* password = "matahary02";

// Detail server hosting HTTPS
const char* serverHost = "smartdoor.satcloud.tech";
const int serverPort = 443; // Port HTTPS
const String apiPath = "/api/iot/percobaan-gagal";
const int kamarId = 1;

// ==========================================
// 2. PIN INPUT TRIGGER DARI ESP32 UTAMA
// ==========================================
#define TRIGGER_PIN 13  // Sambungkan PIN 14 (CAM_TRIG_PIN) ESP32 Utama ke PIN 13 ESP32-CAM ini

// ==========================================
// 3. KONFIGURASI PIN CAMERA (AI-THINKER BOARD)
// ==========================================
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
// 4. LOGIKA PENGAMBILAN & PENGIRIMAN FOTO (HTTPS MULTIPART)
// ==========================================
void ambilDanKirimFoto() {
  Serial.println("\n--- Memulai Proses Ambil & Kirim Foto ---");
  
  // Ambil buffer gambar dari sensor kamera
  camera_fb_t * fb = esp_camera_fb_get();
  if (!fb) {
    Serial.println("Gagal mengambil foto dari kamera!");
    return;
  }
  Serial.printf("Foto terambil. Resolusi: %dx%d, Ukuran: %d Bytes\n", fb->width, fb->height, fb->len);

  // Inisialisasi koneksi aman HTTPS
  WiFiClientSecure client;
  client.setInsecure(); // Abaikan verifikasi SSL untuk kestabilan handshaking di mikrokontroler
  
  Serial.print("Menghubungkan ke server HTTPS: ");
  Serial.println(serverHost);
  
  if (!client.connect(serverHost, serverPort)) {
    Serial.println("Gagal terhubung ke server HTTPS via SSL!");
    esp_camera_fb_return(fb); // Kembalikan buffer agar tidak leak memori
    return;
  }
  Serial.println("Koneksi SSL berhasil dibangun!");

  // Definisikan Boundary untuk Form-Data
  String boundary = "ESP32CAMBoundary" + String(millis());
  
  // Bangun Header Payload Multipart/Form-Data
  String head = "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"kamar_id\"\r\n\r\n" + String(kamarId) + "\r\n";
  head += "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"rfid_uid\"\r\n\r\nKEYPAD\r\n";
  head += "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"jumlah_percobaan\"\r\n\r\n3\r\n";
  head += "--" + boundary + "\r\n";
  head += "Content-Disposition: form-data; name=\"foto\"; filename=\"capture.jpg\"\r\n";
  head += "Content-Type: image/jpeg\r\n\r\n";

  // Penutup Body Multipart
  String tail = "\r\n--" + boundary + "--\r\n";

  // Hitung total panjang konten keseluruhan
  uint32_t totalLen = head.length() + fb->len + tail.length();

  Serial.println("Mengirim raw request HTTP POST over SSL...");
  
  // Kirim HTTP POST Header
  client.println("POST " + apiPath + " HTTP/1.1");
  client.println("Host: " + String(serverHost));
  client.println("Content-Type: multipart/form-data; boundary=" + boundary);
  client.println("Content-Length: " + String(totalLen));
  client.println("Connection: close");
  client.println(); // Batas kosong pemisah header dan body

  // Kirim Body Header Form-Data
  client.print(head);

  // Kirim gambar biner secara bertahap (chunking per 1 KB) agar hemat RAM
  uint8_t *fbBuf = fb->buf;
  size_t fbLen = fb->len;
  size_t chunkSize = 1024;
  
  Serial.println("Mengirim data biner foto...");
  for (size_t n = 0; n < fbLen; n += chunkSize) {
    if (n + chunkSize < fbLen) {
      client.write(fbBuf + n, chunkSize);
    } else {
      client.write(fbBuf + n, fbLen - n); // Sisa bit terakhir
    }
  }

  // Kirim Penutup Body Form-Data
  client.print(tail);
  
  // Tunggu balasan dari server (timeout 10 detik)
  Serial.println("Foto terkirim, menunggu respons server...");
  int timeoutTimer = 10000;
  long startTimer = millis();
  while (!client.available()) {
     delay(10);
     if ((millis() - startTimer) > timeoutTimer) {
         Serial.println("Gagal mendapatkan respons server (Timeout 10 Detik)!");
         client.stop();
         esp_camera_fb_return(fb);
         return;
     }
  }

  // Cetak respons server di Serial Monitor
  Serial.println("Respons Server:");
  while (client.available()) {
     String line = client.readStringUntil('\n');
     Serial.println(line);
  }
  
  // Bersihkan koneksi dan kembalikan buffer frame kamera
  client.stop();
  esp_camera_fb_return(fb);
  Serial.println("--- Selesai. Kamera Siap Kembali ---");
}

// ==========================================
// 5. SETUP & LOOP UTAMA ESP32-CAM
// ==========================================
void setup() {
  // Matikan Brownout Detector agar ESP32-CAM tidak restart/crash saat memotret (butuh arus besar)
  WRITE_PERI_REG(RTC_CNTL_BROWNOUT_REG, 0);
  
  Serial.begin(115200);
  Serial.println("Menginisialisasi ESP32-CAM...");

  // Konfigurasi pin trigger dari ESP32 Utama
  pinMode(TRIGGER_PIN, INPUT); // PULLDOWN eksternal disarankan di hardware Anda

  // Konfigurasi Sensor Kamera
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  // Optimasi Resolusi & Memori Frame Buffer
  if (psramFound()) {
    config.frame_size = FRAMESIZE_VGA; // Resolusi 640x480 (Ideal untuk deteksi wajah keamanan)
    config.jpeg_quality = 10;          // 0-63, makin rendah makin jernih
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_QVGA; // Resolusi 320x240 jika tanpa PSRAM
    config.jpeg_quality = 12;
    config.fb_count = 1;
  }

  // Inisialisasi Kamera
  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Kamera gagal diinisialisasi dengan kode error: 0x%x\n", err);
    for(;;);
  }
  Serial.println("Kamera berhasil diaktifkan.");

  // Koneksi ke WiFi
  Serial.print("Menghubungkan ke WiFi: ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung!");
  Serial.println("ESP32-CAM Siap Menunggu Sinyal Trigger dari ESP32 Utama...");
}

void loop() {
  // Hubungkan ulang WiFi jika putus di tengah jalan
  if (WiFi.status() != WL_CONNECTED) {
    WiFi.disconnect();
    WiFi.begin(ssid, password);
    int retry = 0;
    while (WiFi.status() != WL_CONNECTED && retry < 20) {
      delay(500);
      retry++;
    }
    return;
  }

  // Cek apakah pin trigger menerima sinyal HIGH dari ESP32 Utama
  if (digitalRead(TRIGGER_PIN) == HIGH) {
    // Jalankan pengambilan dan pengiriman foto ke server Laravel
    ambilDanKirimFoto();
    
    // Berikan jeda delay anti-spamming / debounce selama 5 detik setelah trigger selesai
    delay(5000); 
  }
  
  delay(10); // Sleep singkat untuk menjaga stabilitas CPU core
}
