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
const char* ssid     = "Farhan";
const char* password = "987654321";

// URL Produksi HTTPS
const String serverUrl  = "https://smartdoor.satcloud.tech/api/iot";
const String nomorKamar = "101";
const int    kamarId    = 1;

// ==========================================
// 2. KONFIGURASI PIN HARDWARE (JANGAN UBAH)
// ==========================================
#define RST_PIN        -1  // RST hardwired ke 3.3V
#define SS_PIN          5
#define RELAY_PIN      15
#define LED_R_PIN       2  // LED Merah (Gagal)
#define LED_G_PIN      13  // LED Hijau (Sukses)
#define BUZZER_PIN     12  // Buzzer terpisah (Bip)
#define CAM_TRIG_PIN   14  // Sinyal pemicu ke ESP32-CAM
// GPIO 0 punya pull-up internal, tidak butuh resistor!
// Sambung tombol langsung: GPIO 0 → GND. Jangan tekan saat ESP32 restart.
#define BTN_KELUAR_PIN  0  // Tombol keluar dari dalam (sambung langsung ke GND)

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
unsigned long lastBtnKeluar = 0; // Timestamp terakhir tombol keluar ditekan (anti-spam)

// Flag komunikasi antar core (Core 0: polling web, Core 1: keypad/RFID)
volatile bool perintahBukaWeb = false;

// Mutex untuk mencegah dua core pakai WiFi bersamaan
SemaphoreHandle_t wifiMutex;

// ==========================================
// 3. HELPER: KONEKSI WIFI
// ==========================================

// Forward declaration — displayMessage() didefinisikan di Section 4
void displayMessage(String line1, String line2 = "");
void kirimStatusPintu(String status);

// Coba koneksi WiFi dengan timeout. Jika gagal, restart board.
void koneksiWiFi() {
  Serial.printf("[WiFi] Menghubungkan ke '%s'...\n", ssid);
  displayMessage("MENGHUBUNGKAN...", "Mencoba WiFi...");

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 40) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  Serial.println();

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] GAGAL konek! Restart dalam 3 detik...");
    displayMessage("WIFI GAGAL!", "Restart...");
    delay(3000);
    ESP.restart(); // Lebih handal daripada hang selamanya
  }

  Serial.printf("[WiFi] Terhubung! IP: %s\n", WiFi.localIP().toString().c_str());
}

// ==========================================
// 4. FUNGSI LAYAR OLED & SOUND/LED FEEDBACK
// ==========================================
void displayMessage(String line1, String line2) {
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

// Bunyi Bip Panjang & LED Hijau Menyala untuk Sukses (tittttttt...)
void feedbackSukses() {
  digitalWrite(LED_G_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(800); // Buzzer bip panjang
  digitalWrite(BUZZER_PIN, LOW);
}

// Bunyi 3x Bip Pendek & LED Merah Kedip untuk Gagal (tit tit tit..)
void feedbackGagal() {
  digitalWrite(LED_G_PIN, LOW);
  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_R_PIN, HIGH);  // Merah nyala
    digitalWrite(BUZZER_PIN, HIGH); // Buzzer tit
    delay(200);
    digitalWrite(LED_R_PIN, LOW);   // Merah mati
    digitalWrite(BUZZER_PIN, LOW);  // Buzzer mati
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
  digitalWrite(LED_G_PIN, LOW); // Matikan LED Hijau setelah pintu tertutup

  displayMessage("PINTU TERTUTUP", "Silakan Tap/PIN");

  // Sinkronisasi status tertutup kembali ke server agar UI web terupdate otomatis
  kirimStatusPintu("tertutup");
}

// ==========================================
// 5. LOGIKA PENGIRIMAN HTTP REQUEST (HTTPS SECURE)
// ==========================================

// Mengirimkan status pintu terbaru (terbuka/tertutup) ke server
void kirimStatusPintu(String status) {
  if (WiFi.status() != WL_CONNECTED) return;
  if (xSemaphoreTake(wifiMutex, pdMS_TO_TICKS(5000)) != pdTRUE) return;

  WiFiClientSecure client;
  client.setInsecure();
  client.setTimeout(10);

  HTTPClient http;
  String url = serverUrl + "/konfirmasi-perintah/" + String(kamarId);

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");

  DynamicJsonDocument doc(256);
  doc["status_pintu"] = status;

  String jsonBody;
  serializeJson(doc, jsonBody);

  int httpCode = http.POST(jsonBody);
  if (httpCode == HTTP_CODE_OK) {
    Serial.printf("[SERVER] Status pintu '%s' berhasil diperbarui.\n", status.c_str());
  } else {
    Serial.printf("[SERVER] Gagal memperbarui status pintu. Code: %d\n", httpCode);
  }
  http.end();
  xSemaphoreGive(wifiMutex);
}

// Kirim Konfirmasi ke Server setelah mengeksekusi Perintah Buka Pintu
void konfirmasiBukaPintuWeb() {
  kirimStatusPintu("terbuka");
}

// Kirim data RFID ke Server untuk Verifikasi
// Server: POST /api/iot/access
// Response: { "success": bool, "message": str, "ambil_foto": bool }
// HTTP 200 = berhasil, 403 = ditolak, 404 = kamar/kartu tidak valid
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
  client.setTimeout(10);

  HTTPClient http;
  String url = serverUrl + "/access";

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");

  // Server mengharapkan: { "uid", "nomor_kamar", "aksi" }
  DynamicJsonDocument doc(256);
  doc["uid"]          = uid;
  doc["nomor_kamar"]  = nomorKamar;
  doc["aksi"]         = "masuk";

  String jsonBody;
  serializeJson(doc, jsonBody);

  int httpCode = http.POST(jsonBody);
  bool ambilFoto = false;

  if (httpCode == HTTP_CODE_OK || httpCode == 403) {
    String payload = http.getString();
    DynamicJsonDocument resp(512);
    deserializeJson(resp, payload);

    // Server response: { "success": bool, "message": str, "ambil_foto": bool }
    bool success   = resp["success"].as<bool>();
    ambilFoto      = resp["ambil_foto"].as<bool>();

    http.end();
    xSemaphoreGive(wifiMutex); // Lepaskan WiFi SEBELUM trigger kamera

    if (success) {
      aksesDiterima("ID: " + uid);
    } else {
      displayMessage("AKSES DITOLAK", "Kartu Tidak Cocok");
      feedbackGagal();
      if (ambilFoto) {
        triggerKamera();
      }
    }
  } else {
    Serial.printf("[RFID] POST Gagal, Code: %d\n", httpCode);
    displayMessage("AKSES DITOLAK", "Server Error: " + String(httpCode));
    feedbackGagal();
    http.end();
    xSemaphoreGive(wifiMutex);
  }
}

// Kirim data PIN ke Server untuk Verifikasi
// Server: POST /api/iot/akses-pin
// Response sukses (200): { "status": "berhasil", "pesan": str, "buka_pintu": true }
// Response gagal (403):  { "status": "gagal",    "pesan": str, "buka_pintu": false, "ambil_foto": bool }
// Response rate-limit (429): { "status": "gagal", "pesan": str, "buka_pintu": false }
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
  client.setTimeout(10);

  HTTPClient http;
  String url = serverUrl + "/akses-pin";

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");

  // Server mengharapkan: { "kamar_id": int, "pin": str }
  DynamicJsonDocument doc(256);
  doc["kamar_id"] = kamarId;
  doc["pin"]      = pin;

  String jsonBody;
  serializeJson(doc, jsonBody);

  int httpCode = http.POST(jsonBody);
  bool ambilFoto = false;

  if (httpCode == HTTP_CODE_OK) {
    // PIN benar — response: { "status": "berhasil", "buka_pintu": true }
    // Catatan: response sukses tidak ada field "ambil_foto"
    String payload = http.getString();
    DynamicJsonDocument resp(512);
    deserializeJson(resp, payload);

    bool bukaPintu = resp["buka_pintu"].as<bool>();

    http.end();
    xSemaphoreGive(wifiMutex);

    if (bukaPintu) {
      aksesDiterima("Selamat Datang!");
    } else {
      // Seharusnya tidak terjadi, tapi jaga-jaga
      displayMessage("PIN SALAH!", "Akses Ditolak");
      feedbackGagal();
    }

  } else if (httpCode == 403) {
    // PIN salah — response: { "status": "gagal", "buka_pintu": false, "ambil_foto": bool }
    String payload = http.getString();
    DynamicJsonDocument resp(512);
    deserializeJson(resp, payload);

    ambilFoto = resp["ambil_foto"].as<bool>();

    http.end();
    xSemaphoreGive(wifiMutex); // Lepaskan WiFi SEBELUM trigger kamera

    displayMessage("PIN SALAH!", "Akses Ditolak");
    feedbackGagal();
    if (ambilFoto) {
      triggerKamera();
    }

  } else if (httpCode == 429) {
    // Rate-limit — terlalu banyak percobaan
    Serial.println("[PIN] Rate-limited! Terlalu banyak percobaan.");
    http.end();
    xSemaphoreGive(wifiMutex);
    displayMessage("TERLALU BANYAK!", "Coba 5 menit lagi");
    feedbackGagal();

  } else {
    Serial.printf("[PIN] POST Gagal, Code: %d\n", httpCode);
    displayMessage("ERROR SERVER", "Gagal verifikasi");
    feedbackGagal();
    http.end();
    xSemaphoreGive(wifiMutex);
  }
}

// Fungsi Trigger untuk menyuruh ESP32-CAM memfoto pelaku (Active LOW)
void triggerKamera() {
  displayMessage("TRIGGER KAMERA", "Kirim sinyal...");
  Serial.println("[CAM] Mengirim sinyal trigger ke ESP32-CAM...");

  // Kirim pulsa LOW selama 2 detik ke ESP32-CAM (Active LOW)
  digitalWrite(CAM_TRIG_PIN, LOW);
  displayMessage("SINYAL DIKIRIM", "GPIO14 = LOW");
  delay(2000); // 2 detik penuh agar pasti tertangkap oleh ESP32-CAM

  digitalWrite(CAM_TRIG_PIN, HIGH); // Kembali ke HIGH (idle)
  displayMessage("SINYAL SELESAI", "GPIO14 = HIGH");
  Serial.println("[CAM] Sinyal trigger selesai dikirim (2 detik).");

  // Tunggu ESP32-CAM selesai memproses (ambil foto + upload ~10 detik)
  displayMessage("MENUNGGU CAM", "Upload foto...");
  delay(10000);

  displayMessage("CAM SELESAI", "Siap Digunakan");
  Serial.println("[CAM] Proses trigger kamera selesai.");
}

// ==========================================
// 6. SETUP & LOOP UTAMA ESP32
// ==========================================
void setup() {
  Serial.begin(115200);
  SPI.begin();
  rfid.PCD_Init();

  // Set Pin Modes
  pinMode(RELAY_PIN,      OUTPUT);
  pinMode(LED_R_PIN,      OUTPUT);
  pinMode(LED_G_PIN,      OUTPUT);
  pinMode(BUZZER_PIN,     OUTPUT);
  pinMode(CAM_TRIG_PIN,   OUTPUT);
  // GPIO 0 punya internal pull-up, tidak butuh resistor eksternal
  pinMode(BTN_KELUAR_PIN, INPUT_PULLUP);

  // Inisialisasi awal Pin (Active LOW untuk CAM_TRIG_PIN)
  digitalWrite(RELAY_PIN,    LOW);  // Kunci tertutup
  digitalWrite(LED_R_PIN,    LOW);
  digitalWrite(LED_G_PIN,    LOW);
  digitalWrite(BUZZER_PIN,   LOW);
  digitalWrite(CAM_TRIG_PIN, HIGH); // Idle = HIGH (Active LOW)

  // Inisialisasi OLED
  Wire.begin();
  if (!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
    Serial.println("Gagal menemukan OLED SSD1306!");
    for (;;);
  }
  display.clearDisplay();

  // Koneksi WiFi dengan timeout & auto-restart
  koneksiWiFi();
  displayMessage("SYSTEM ONLINE", "Silakan Tap/PIN");

  // Buat mutex WiFi agar Core 0 dan Core 1 tidak bentrok saat pakai WiFi
  wifiMutex = xSemaphoreCreateMutex();

  // Jalankan polling web di Core 0 (terpisah dari Core 1 yang menangani keypad/RFID)
  // Polling 10 detik — cukup responsif tanpa membebani WiFi stack
  xTaskCreatePinnedToCore(
    [](void* param) {
      for (;;) {
        vTaskDelay(pdMS_TO_TICKS(10000)); // Poll setiap 10 detik (bukan 3 detik)

        if (WiFi.status() != WL_CONNECTED) continue;

        // Tunggu giliran pakai WiFi (maksimal 3 detik)
        if (xSemaphoreTake(wifiMutex, pdMS_TO_TICKS(3000)) != pdTRUE) continue;

        WiFiClientSecure client;
        client.setInsecure();
        client.setTimeout(8); // 8 detik timeout SSL

        HTTPClient http;
        // Server: GET /api/iot/status-pintu/{kamar_id}
        // Response: { "kamar_id", "nomor_kamar", "perintah", "status_pintu", "updated_at" }
        String url = serverUrl + "/status-pintu/" + String(kamarId);
        http.begin(client, url);
        http.addHeader("Accept", "application/json");

        int code = http.GET();
        if (code == HTTP_CODE_OK) {
          String payload = http.getString();
          DynamicJsonDocument doc(512);
          deserializeJson(doc, payload);

          // Cek field "perintah" — server set "buka" saat ada perintah dari web
          String perintah = doc["perintah"].as<String>();
          if (perintah == "buka") {
            Serial.println("[WEB] Menerima perintah buka pintu dari Web!");
            perintahBukaWeb = true;
          }
        } else {
          Serial.printf("[POLL] Status-pintu error, code: %d\n", code);
        }

        http.end();
        xSemaphoreGive(wifiMutex); // Lepaskan mutex
      }
    },
    "TaskPollingWeb", // Nama task
    8192,             // Stack size
    NULL,             // Parameter
    1,                // Prioritas
    NULL,             // Task handle
    0                 // Jalankan di Core 0
  );
}

void loop() {
  // Re-koneksi WiFi otomatis jika terputus
  if (WiFi.status() != WL_CONNECTED) {
    displayMessage("RE-CONNECTING...", "WiFi Terputus");
    Serial.println("[WiFi] Terputus! Mencoba reconnect...");

    // Gunakan reconnect() saja — lebih aman daripada disconnect()+begin()
    WiFi.reconnect();
    unsigned long tReconnect = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - tReconnect < 10000) {
      delay(500);
    }

    if (WiFi.status() == WL_CONNECTED) {
      Serial.println("[WiFi] Terhubung kembali!");
      displayMessage("SYSTEM ONLINE", "Silakan Tap/PIN");
    } else {
      // Reconnect gagal setelah 10 detik → restart board
      Serial.println("[WiFi] Reconnect gagal! Restart...");
      delay(1000);
      ESP.restart();
    }
    return;
  }

  // 1. Cek flag perintah buka pintu dari Web (dikirim oleh task Core 0)
  if (perintahBukaWeb) {
    perintahBukaWeb = false; // Reset flag
    konfirmasiBukaPintuWeb();
    aksesDiterima("Buka Pintu via Web");
  }

  // 2. Cek tombol keluar dari dalam (tanpa verifikasi server)
  // Cooldown 6 detik agar tidak trigger ulang setelah door open 5 detik
  if (digitalRead(BTN_KELUAR_PIN) == LOW && millis() - lastBtnKeluar > 6000) {
    delay(50); // Debounce singkat
    if (digitalRead(BTN_KELUAR_PIN) == LOW) { // Konfirmasi masih ditekan
      lastBtnKeluar = millis();
      displayMessage("KELUAR", "Pintu Terbuka...");
      feedbackSukses();
      digitalWrite(RELAY_PIN, HIGH);
      delay(5000);
      digitalWrite(RELAY_PIN, LOW);
      digitalWrite(LED_G_PIN, LOW); // Matikan LED hijau
      displayMessage("SYSTEM ONLINE", "Silakan Tap/PIN");
    }
  }

  // 3. Baca Scan Kartu RFID
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

  // 4. Baca Input Keypad
  char key = keypad.getKey();
  if (key) {
    // Beri suara bip singkat feedback tombol ditekan (menggunakan Buzzer)
    digitalWrite(BUZZER_PIN, HIGH);
    delay(50);
    digitalWrite(BUZZER_PIN, LOW);

    if (key >= '0' && key <= '9') {
      if (inputPIN.length() < 6) {
        inputPIN += key;
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