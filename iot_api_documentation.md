# Dokumentasi API IoT - Smart Door Kos Bu Rini

Dokumen ini berisi penjelasan 6 endpoint yang disediakan oleh sistem backend Laravel untuk berkomunikasi dengan perangkat IoT (seperti ESP32, ESP8266, atau NodeMCU).

> [!NOTE]
> Semua endpoint berawalan `api/` dibebaskan dari validasi CSRF. Perangkat mikrokontroler Anda dapat langsung melakukan HTTP POST/GET tanpa memerlukan header token CSRF.

---

## 1. Polling Status Pintu (Cek Perintah dari Web)
Digunakan ketika penghuni menekan tombol "Buka Pintu" melalui web/dashboard mereka.

- **Method & URL:** `GET /api/iot/status-pintu/{kamar_id}` (Ganti `{kamar_id}` dengan ID kamar di database, misal: `1`).
- **Logika:** ESP32 disarankan melakukan *polling* ke URL ini secara rutin (misal setiap 2 detik sekali). Server akan mengembalikan objek JSON. Jika atribut `perintah` bernilai `"buka"`, maka ESP32 harus membunyikan buzzer pendek dan menyalakan relay solenoid door lock untuk membuka pintu.
- **Contoh Respons (JSON):**
  ```json
  {
      "kamar_id": 1,
      "nomor_kamar": "101",
      "perintah": "buka", 
      "status_pintu": "tertutup",
      "updated_at": "2026-05-20 12:00:00"
  }
  ```
  *(Jika tidak ada yang menekan tombol di web, nilai `"perintah"` akan berisikan `null`)*

---

## 2. Konfirmasi Perintah Selesai Dieksekusi
Digunakan untuk mereset status perintah di server setelah ESP32 berhasil membuka pintu.

- **Method & URL:** `POST /api/iot/konfirmasi-perintah/{kamar_id}`
- **Logika:** Setelah ESP32 berhasil mengeksekusi perintah buka pintu dari polling di atas, ESP32 **wajib** memberi tahu server bahwa pintunya sudah berstatus terbuka agar nilai "buka" di kolom `perintah` pada database di-reset kembali menjadi kosong/null.
- **Body (JSON):**
  ```json
  {
      "status_pintu": "terbuka"
  }
  ```

---

## 3. Akses Menggunakan Kartu RFID
Digunakan ketika penghuni menempelkan Kartu/Gantungan RFID (Mifare RC522) ke reader.

- **Method & URL:** `POST /api/iot/access`
- **Logika:** Saat kartu ditempel, ESP32 mengirim `uid` kartu ke server. Server akan memvalidasi apakah kartu tersebut terdaftar, aktif, dan cocok dengan kamar yang dituju.
  - Jika cocok, server mengembalikan kode **HTTP 200**, ESP32 menyalakan relay untuk membuka pintu.
  - Jika ditolak, server mengembalikan kode **HTTP 403**, ESP32 tolak buka pintu (bunyikan buzzer error). Server akan otomatis mencatat log aktivitasnya.
- **Body (JSON):**
  ```json
  {
      "uid": "AB123456",
      "nomor_kamar": "101",
      "aksi": "masuk"
  }
  ```
  *(Nilai `aksi` bisa diisi `"masuk"` atau `"keluar"` tergantung penempatan sensor)*

---

## 4. Akses Menggunakan PIN Keypad
Digunakan ketika penghuni menginputkan kombinasi PIN angka melalui Keypad Matrix 4x4.

- **Method & URL:** `POST /api/iot/akses-pin`
- **Logika:** ESP32 membaca input dari Keypad, lalu mengirimkan 6 digit angka ke server. Sama seperti RFID, server akan memvalidasi PIN tersebut.
  - Sistem ini memiliki **proteksi brute force**. Jika salah 3 kali, server akan menolak semua permintaan selama 5 menit dan memberikan balasan `ambil_foto: true`.
  - Jika variabel `ambil_foto` mengembalikan nilai `true`, ESP32 (jika menggunakan ESP32-CAM) harus mengaktifkan kamera dan memanggil endpoint ke-5 di bawah.
- **Body (JSON):**
  ```json
  {
      "kamar_id": 1,
      "pin": "123456"
  }
  ```

---

## 5. Laporan Percobaan Gagal (Mengirim Foto Pelaku)
Digunakan **hanya jika** sistem mendeteksi percobaan pembobolan (kartu ditolak berulang kali atau salah PIN 3 kali berturut-turut).

- **Method & URL:** `POST /api/iot/percobaan-gagal`
- **Logika:** ESP32-CAM memfoto pelaku dan mengirimkan gambarnya ke server. Admin kos nantinya bisa melihat log peringatan beserta wajah pelakunya di dashboard keamanan.
- **Format Data (Multipart / Form-Data):**
  - `kamar_id`: (Angka ID Kamar, misalnya: 1)
  - `rfid_uid`: (Isi dengan "KEYPAD" jika salah PIN, atau masukkan UID kartu jika karena masalah RFID)
  - `jumlah_percobaan`: (Misalnya 3)
  - `foto`: (Binary file / Buffer hasil capture kamera berformat JPG/PNG)

---

## 6. Update Status Pintu Otomatis (Magnetic Sensor)
Digunakan jika alat IoT Anda dilengkapi dengan sensor magnet (Magnetic Reed Switch) pada engsel atau kusen pintu.

- **Method & URL:** `POST /api/iot/door-status`
- **Logika:** Berguna untuk memberi tahu website secara *real-time* mengenai posisi fisik pintu (apakah saat ini sedang terbuka atau sudah tertutup rapat kembali).
- **Body (JSON):**
  ```json
  {
      "nomor_kamar": "101",
      "status_pintu": "tertutup" 
  }
  ```

---

> [!TIP]
> **Pesan Tambahan untuk implementasi C++ (Arduino/PlatformIO):**
> Pastikan Anda selalu menyertakan *HTTP Header* berikut saat mengirim request berformat JSON (Endpoint 2, 3, 4, dan 6) menggunakan pustaka HTTPClient:
> ```cpp
> http.addHeader("Content-Type", "application/json");
> http.addHeader("Accept", "application/json");
> ```
