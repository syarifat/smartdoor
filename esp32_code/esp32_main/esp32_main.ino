#include <WiFi.h>
#include <WiFiClientSecure.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Keypad.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <ArduinoJson.h>

// ==========================================
// 1. KONFIGURASI JARINGAN & API PRODUCTION
// ==========================================
const char* ssid = "satcloud";
const char* password = "matahary02";

// URL Produksi HTTPS
const String serverUrl = "https://smartdoor.satcloud.tech/api/iot";
const String nomorKamar = "101"; 
const int kamarId = 1;

// ==========================================
// 2. KONFIGURASI PIN HARDWARE (JANGAN UBAH)
// ==========================================
#define RST_PIN       -1  // RST hardwired ke 3.3V
#define SS_PIN        5
#define RELAY_PIN     15
#define LED_R_BUZ_PIN 2   // Gabungan Buzzer & LED Merah
#define LED_G_PIN     13  // LED Hijau (Sukses)
#define LED_Y_PIN     12  // LED Kuning (Proses/Trigger Kamera)
#define CAM_TRIG_PIN  14  // Sinyal pemicu ke ESP32-CAM

MFRC522 rfid(SS_PIN, RST_PIN);

// Konfigurasi Keypad 4x4
const byte ROWS = 4; 
const byte COLS = 4; 
char keys[ROWS][COLS] = {
  {'1','2','3','A'},
  {'4','5','6','B'},
  {'7','8','9','C'},
  {'*','0','#','D'}
};
byte rowPins[ROWS] = {27, 16, 17, 4}; 
byte colPins[COLS] = {32, 33, 25, 26};
Keypad keypad = Keypad(makeKeymap(keys), rowPins, colPins, ROWS, COLS);
String inputPIN = "";

// Konfigurasi Layar OLED (I2C)
Adafruit_SSD1306 display(128, 64, &Wire, -1);

// Variabel Kontrol
int pinSalahCount = 0;

// Flag komunikasi antar core (Core 0: polling web, Core 1: keypad/RFID)
volatile bool perintahBukaWeb = false;

// Mutex untuk mencegah dua core pakai WiFi bersamaan
SemaphoreHandle_t wifiMutex;

// ==========================================
// 3. FUNGSI LAYAR OLED & SOUND/LED FEEDBACK
// ==========================================
void displayMessage(String line1, String line2 = "") {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0, 10);
  display.println(line1);
  display.setCursor(0, 35);
  display.println(line2);
  display.display();
}

// Tampilan khusus input PIN: angka tampil polos + petunjuk tombol di bawah
void displayPIN(String pinSoFar) {
  display.clearDisplay();
  display.setTextColor(SSD1306_WHITE);

  // Baris 1: Label
  display.setTextSize(1);
  display.setCursor(0, 0);
  display.println("MASUKKAN PIN:");

  // Baris 2: Angka PIN besar dan jelas
  display.setTextSize(2);
  display.setCursor(0, 18);
  display.println(pinSoFar.length() > 0 ? pinSoFar : "-");

  // Baris 3: Petunjuk tombol (kecil di bawah)
  display.setTextSize(1);
  display.setCursor(0, 52);
  display.println("# Enter  * Hapus");

  display.display();
}

// Bunyi Bip & Kedip LED untuk Sukses
void feedbackSukses() {
  digitalWrite(LED_G_PIN, HIGH);
  digitalWrite(LED_R_BUZ_PIN, HIGH); // Buzzer bip pendek
  delay(150);
  digitalWrite(LED_R_BUZ_PIN, LOW);
  delay(100);
  digitalWrite(LED_R_BUZ_PIN, HIGH);
  delay(150);
  digitalWrite(LED_R_BUZ_PIN, LOW);
}

// Bunyi Bip Panjang & Kedip LED untuk Gagal
void feedbackGagal() {
  digitalWrite(LED_G_PIN, LOW);
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_R_BUZ_PIN, HIGH); // Bip error
    delay(200);
    digitalWrite(LED_R_BUZ_PIN, LOW);
    delay(100);
  }
}

// Aksi ketika Pintu Berhasil Dibuka
void aksesDiterima(String welcomeMsg) {
  displayMessage("AKSES DITERIMA", welcomeMsg);
  feedbackSukses();
  
  // Aktifkan Solenoid Relay (Active HIGH)
  digitalWrite(RELAY_PIN, HIGH); 
  delay(5000); // Pintu terbuka selama 5 detik
  digitalWrite(RELAY_PIN, LOW);
  digitalWrite(LED_G_PIN, LOW);
  
  displayMessage("PINTU TERTUTUP", "Silakan Tap/PIN");
}

// ==========================================
// 4. LOGIKA PENGIRIMAN HTTP REQUEST (HTTPS SECURE)
// ==========================================

// Fungsi Polling: Cek apakah ada perintah buka pintu dari Web
void cekPerintahWeb() {
  if (WiFi.status() != WL_CONNECTED) return;
  
  WiFiClientSecure client;
  client.setInsecure(); // Mengabaikan verifikasi sertifikat SSL untuk keandalan IoT
  
  HTTPClient http;
  String url = serverUrl + "/status-pintu/" + String(kamarId);
  
  http.begin(client, url);
  http.addHeader("Accept", "application/json");
  
  int httpCode = http.GET();
  if (httpCode == HTTP_CODE_OK) {
    String payload = http.getString();
    DynamicJsonDocument doc(1024);
    deserializeJson(doc, payload);
    
    String perintah = doc["perintah"].as<String>();
    if (perintah == "buka") {
      Serial.println("[WEB] Menerima perintah buka pintu dari Web!");
      
      // Kirim konfirmasi perintah diterima ke server
      konfirmasiBukaPintuWeb();
      
      // Jalankan aksi buka pintu
      aksesDiterima("Buka Pintu via Web");
    }
  }
  http.end();
}

// Kirim Konfirmasi ke Server setelah mengeksekusi Perintah Buka Pintu
void konfirmasiBukaPintuWeb() {
  if (WiFi.status() != WL_CONNECTED) return;
  if (xSemaphoreTake(wifiMutex, pdMS_TO_TICKS(5000)) != pdTRUE) return;
  
  WiFiClientSecure client;
  client.setInsecure();
  
  HTTPClient http;
  String url = serverUrl + "/konfirmasi-perintah/" + String(kamarId);
  
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  
  DynamicJsonDocument doc(256);
  doc["status_pintu"] = "terbuka";
  
  String jsonBody;
  serializeJson(doc, jsonBody);
  
  int httpCode = http.POST(jsonBody);
  if (httpCode == HTTP_CODE_OK) {
    Serial.println("[WEB] Konfirmasi perintah berhasil dikirim.");
  } else {
    Serial.printf("[WEB] Gagal kirim konfirmasi. Code: %d\n", httpCode);
  }
  http.end();
  xSemaphoreGive(wifiMutex);
}

// Kirim data RFID ke Server untuk Verifikasi
void verifikasiRFID(String uid) {
  displayMessage("MEMVERIFIKASI...", "Kartu: " + uid);
  
  if (WiFi.status() != WL_CONNECTED) {
    displayMessage("KONEKSI ERROR", "WiFi terputus!");
    feedbackGagal();
    return;
  }

  // Ambil giliran WiFi dari Core 0
  if (xSemaphoreTake(wifiMutex, pdMS_TO_TICKS(8000)) != pdTRUE) {
    displayMessage("AKSES DITOLAK", "WiFi sibuk!");
    feedbackGagal();
    return;
  }
  
  WiFiClientSecure client;
  client.setInsecure();
  
  HTTPClient http;
  String url = serverUrl + "/access";
  
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  
  DynamicJsonDocument doc(256);
  doc["uid"] = uid;
  doc["nomor_kamar"] = nomorKamar;
  doc["aksi"] = "masuk";
  
  String jsonBody;
  serializeJson(doc, jsonBody);
  
  int httpCode = http.POST(jsonBody);
  bool ambilFoto = false;
  if (httpCode == HTTP_CODE_OK || httpCode == 403) {
    String payload = http.getString();
    DynamicJsonDocument resp(512);
    deserializeJson(resp, payload);
    
    bool success = resp["success"].as<bool>();
    ambilFoto = resp["ambil_foto"].as<bool>();
    
    http.end();
    xSemaphoreGive(wifiMutex); // Lepaskan WiFi SEBELUM trigger kamera

    if (success) {
      pinSalahCount = 0;
      aksesDiterima("ID: " + uid);
    } else {
      displayMessage("AKSES DITOLAK", "Kartu Tidak Cocok");
      feedbackGagal();
      if (ambilFoto) {
        triggerKamera();
      }
    }
  } else {
    Serial.printf("RFID POST Gagal, Code: %d\n", httpCode);
    displayMessage("AKSES DITOLAK", "Server Error: " + String(httpCode));
    feedbackGagal();
    http.end();
    xSemaphoreGive(wifiMutex);
  }
}

// Kirim data PIN ke Server untuk Verifikasi
void verifikasiPIN(String pin) {
  displayMessage("VERIFIKASI PIN...", "Memproses...");
  
  if (WiFi.status() != WL_CONNECTED) {
    displayMessage("KONEKSI ERROR", "WiFi terputus!");
    feedbackGagal();
    return;
  }

  // Ambil giliran WiFi dari Core 0
  if (xSemaphoreTake(wifiMutex, pdMS_TO_TICKS(8000)) != pdTRUE) {
    displayMessage("ERROR SERVER", "WiFi sibuk!");
    feedbackGagal();
    return;
  }
  
  WiFiClientSecure client;
  client.setInsecure();
  
  HTTPClient http;
  String url = serverUrl + "/akses-pin";
  
  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  
  DynamicJsonDocument doc(256);
  doc["kamar_id"] = kamarId;
  doc["pin"] = pin;
  
  String jsonBody;
  serializeJson(doc, jsonBody);
  
  int httpCode = http.POST(jsonBody);
  bool ambilFoto = false;
  if (httpCode == HTTP_CODE_OK || httpCode == 403) {
    String payload = http.getString();
    DynamicJsonDocument resp(512);
    deserializeJson(resp, payload);
    
    bool bukaPintu = resp["buka_pintu"].as<bool>();
    ambilFoto = resp["ambil_foto"].as<bool>();

    http.end();
    xSemaphoreGive(wifiMutex); // Lepaskan WiFi SEBELUM trigger kamera

    if (bukaPintu) {
      pinSalahCount = 0;
      aksesDiterima("Selamat Datang!");
    } else {
      displayMessage("PIN SALAH!", "Akses Ditolak");
      feedbackGagal();
      if (ambilFoto) {
        triggerKamera();
      }
    }
  } else {
    Serial.printf("PIN POST Gagal, Code: %d\n", httpCode);
    displayMessage("ERROR SERVER", "Gagal verifikasi");
    feedbackGagal();
    http.end();
    xSemaphoreGive(wifiMutex);
  }
}

// Fungsi Trigger untuk menyuruh ESP32-CAM memfoto pelaku
void triggerKamera() {
  // STEP 1: Tampilkan di LCD bahwa kita akan trigger kamera
  displayMessage("TRIGGER KAMERA", "Kirim sinyal...");
  Serial.println("[CAM] Mengirim sinyal trigger ke ESP32-CAM...");
  
  digitalWrite(LED_Y_PIN, HIGH); // LED Kuning menyala = sedang trigger

  // STEP 2: Kirim pulsa HIGH selama 2 detik ke ESP32-CAM
  digitalWrite(CAM_TRIG_PIN, HIGH);
  displayMessage("SINYAL DIKIRIM", "GPIO14 = HIGH");
  delay(2000); // 2 detik penuh agar pasti tertangkap oleh ESP32-CAM

  digitalWrite(CAM_TRIG_PIN, LOW);
  displayMessage("SINYAL SELESAI", "GPIO14 = LOW");
  Serial.println("[CAM] Sinyal trigger selesai dikirim (2 detik).");

  // STEP 3: Tunggu ESP32-CAM selesai memproses (ambil foto + upload)
  displayMessage("MENUNGGU CAM", "Upload foto...");
  delay(10000); // Tunggu 10 detik untuk ESP32-CAM selesai upload

  digitalWrite(LED_Y_PIN, LOW);
  displayMessage("CAM SELESAI", "Siap Digunakan");
  Serial.println("[CAM] Proses trigger kamera selesai.");
}

// ==========================================
// 5. SETUP & LOOP UTAMA ESP32
// ==========================================
void setup() {
  Serial.begin(115200);
  SPI.begin();
  rfid.PCD_Init();
  
  // Set Pin Modes
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_R_BUZ_PIN, OUTPUT);
  pinMode(LED_G_PIN, OUTPUT);
  pinMode(LED_Y_PIN, OUTPUT);
  pinMode(CAM_TRIG_PIN, OUTPUT);
  
  // Inisialisasi awal Pin
  digitalWrite(RELAY_PIN, LOW);  // Kunci tertutup
  digitalWrite(LED_R_BUZ_PIN, LOW);
  digitalWrite(LED_G_PIN, LOW);
  digitalWrite(LED_Y_PIN, LOW);
  digitalWrite(CAM_TRIG_PIN, LOW);
  
  // Inisialisasi OLED
  Wire.begin();
  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) { 
    Serial.println("Gagal menemukan OLED SSD1306!");
    for(;;);
  }
  
  display.clearDisplay();
  displayMessage("MENGHUBUNGKAN...", "Mencoba WiFi...");
  
  // Koneksi WiFi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Terhubung!");
  displayMessage("SYSTEM ONLINE", "Silakan Tap/PIN");

  // Buat mutex WiFi agar Core 0 dan Core 1 tidak bentrok saat pakai WiFi
  wifiMutex = xSemaphoreCreateMutex();

  // Jalankan polling web di Core 0 (terpisah dari Core 1 yang menangani keypad/RFID)
  // Ini memastikan HTTPS request tidak pernah memblokir input keypad
  xTaskCreatePinnedToCore(
    [](void* param) {
      for (;;) {
        vTaskDelay(pdMS_TO_TICKS(3000)); // Poll setiap 3 detik
        if (WiFi.status() != WL_CONNECTED) continue;

        // Tunggu giliran pakai WiFi (maksimal 2 detik)
        if (xSemaphoreTake(wifiMutex, pdMS_TO_TICKS(2000)) != pdTRUE) continue;

        // Cek perintah dari web
        WiFiClientSecure client;
        client.setInsecure();
        client.setTimeout(5);
        HTTPClient http;
        String url = serverUrl + "/status-pintu/" + String(kamarId);
        http.begin(client, url);
        http.addHeader("Accept", "application/json");
        int code = http.GET();
        if (code == HTTP_CODE_OK) {
          String payload = http.getString();
          DynamicJsonDocument doc(512);
          deserializeJson(doc, payload);
          String perintah = doc["perintah"].as<String>();
          if (perintah == "buka") {
            perintahBukaWeb = true;
          }
        }
        http.end();
        xSemaphoreGive(wifiMutex); // Lepaskan mutex
      }
    },
    "TaskPollingWeb", // Nama task
    8192,            // Stack size
    NULL,            // Parameter
    1,               // Prioritas
    NULL,            // Task handle
    0                // Jalankan di Core 0
  );
}

void loop() {
  // Re-koneksi WiFi otomatis jika terputus
  if (WiFi.status() != WL_CONNECTED) {
    displayMessage("RE-CONNECTING...", "WiFi Terputus");
    WiFi.disconnect();
    WiFi.begin(ssid, password);
    int retry = 0;
    while (WiFi.status() != WL_CONNECTED && retry < 20) {
      delay(500);
      retry++;
    }
    if (WiFi.status() == WL_CONNECTED) {
      displayMessage("SYSTEM ONLINE", "Silakan Tap/PIN");
    }
    return;
  }
  
  // 1. Cek flag perintah buka pintu dari Web (dikirim oleh task Core 0)
  if (perintahBukaWeb) {
    perintahBukaWeb = false; // Reset flag
    konfirmasiBukaPintuWeb();
    aksesDiterima("Buka Pintu via Web");
  }
  
  // 2. Baca Scan Kartu RFID
  if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
    // Ambil UID Kartu
    String uidString = "";
    for (byte i = 0; i < rfid.uid.size; i++) {
      uidString += String(rfid.uid.uidByte[i] < 0x10 ? "0" : "");
      uidString += String(rfid.uid.uidByte[i], HEX);
    }
    uidString.toUpperCase();
    
    Serial.print("Kartu RFID Terbaca: ");
    Serial.println(uidString);
    
    // Verifikasi ke Server
    verifikasiRFID(uidString);
    
    // Stop RFID reading
    rfid.PICC_HaltA();
    rfid.PCD_StopCrypto1();
  }
  
  // 3. Baca Input Keypad
  char key = keypad.getKey();
  if (key) {
    // Beri suara bip singkat feedback tombol ditekan
    digitalWrite(LED_R_BUZ_PIN, HIGH);
    delay(50);
    digitalWrite(LED_R_BUZ_PIN, LOW);
    
    if (key >= '0' && key <= '9') {
      if (inputPIN.length() < 6) {
        inputPIN += key;
        // Tampilkan angka langsung tanpa sensor, dengan petunjuk tombol
        displayPIN(inputPIN);
      }
    } 
    else if (key == '*') {
      // Tombol Hapus: hapus 1 karakter terakhir dulu, baru semua jika sudah kosong
      if (inputPIN.length() > 0) {
        inputPIN.remove(inputPIN.length() - 1); // Hapus 1 digit terakhir
        if (inputPIN.length() == 0) {
          displayMessage("PIN DIHAPUS", "Ketik ulang PIN");
          delay(800);
          displayPIN("");
        } else {
          displayPIN(inputPIN); // Tampilkan sisa digit
        }
      } else {
        // Sudah kosong, kembali ke layar utama
        displayMessage("SYSTEM ONLINE", "Silakan Tap/PIN");
      }
    } 
    else if (key == '#') {
      // Tombol Enter/Kirim
      if (inputPIN.length() == 6) {
        verifikasiPIN(inputPIN);
        inputPIN = ""; // Reset setelah verifikasi
      } else if (inputPIN.length() == 0) {
        // Tidak ada input, abaikan saja
      } else {
        displayMessage("KURANG DIGIT!", String(6 - inputPIN.length()) + " lagi");
        feedbackGagal();
        delay(1500);
        displayPIN(inputPIN); // Kembali ke input, jangan hapus
      }
    }
  }
}
