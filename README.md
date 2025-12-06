# 🚀 Sistem Monitoring & Kendali IoT Realtime (Suhu & Kelembaban)

**Proyek Akhir Mata Kuliah Internet of Things (IoT)** **Universitas Semarang (USM)**

Sistem ini adalah solusi IoT berbasis **NodeMCU ESP8266** yang berfungsi untuk memantau kondisi lingkungan (Suhu & Kelembaban) secara *real-time* melalui **LCD Fisik** dan **Web Dashboard**. Selain monitoring, sistem ini juga dilengkapi dengan fitur kendali jarak jauh (LED) dan sistem peringatan dini (Alarm Web).

---

## 📸 Tampilan Sistem

*(Silakan upload screenshot dashboard web kamu di sini dan ganti link gambarnya)*
![Dashboard Web](path/to/screenshot_dashboard.png)

---

## 🛠️ Fitur Utama

1.  **Monitoring Realtime:**
    * Menampilkan Suhu (°C) dan Kelembaban (%) dari sensor DHT11.
    * Data tampil ganda: Di **Layar LCD 16x2** (Lokal) dan **Web Dashboard** (Online/Lokal Network).
2.  **Grafik Live Data:**
    * Visualisasi data suhu & kelembaban menggunakan grafik dinamis (*Chart.js*).
3.  **Sistem Alarm Web:**
    * Website otomatis membunyikan suara **BEEP** jika suhu melebihi batas aman (> 29°C).
4.  **Kendali Aktuator (LED):**
    * Mengontrol 3 buah LED (Merah, Kuning, Hijau) secara nirkabel melalui tombol di website.
5.  **Database Logging:**
    * Semua data sensor disimpan secara otomatis ke database **MySQL** untuk keperluan rekam jejak (*history*).
6.  **Multi-Client Identity:**
    * Sistem mengenali identitas perangkat (NIM) dan Client Web secara unik.

---

## 🧰 Komponen & Teknologi

### Hardware
* **NodeMCU ESP8266** (Microcontroller)
* **Sensor DHT11** (Suhu & Kelembaban)
* **LCD 16x2 + Modul I2C** (Display Fisik)
* **LED 5mm** (3x: Merah, Kuning, Hijau)
* **Kabel Jumper & Breadboard**

### Software & Protokol
* **Bahasa:** C++ (Arduino), PHP, HTML, CSS, JavaScript.
* **Protokol:** MQTT (*Message Queuing Telemetry Transport*) & WebSocket.
* **Library Web:** Chart.js (Grafik), Paho MQTT (Koneksi IoT).
* **Database:** MySQL (XAMPP).
* **IDE:** Arduino IDE & VS Code.

---

## 🔌 Skema Rangkaian (Wiring)

Berikut adalah konfigurasi pin pada NodeMCU ESP8266:

| Komponen | Pin Komponen | Pin NodeMCU | Keterangan |
| :--- | :--- | :--- | :--- |
| **LCD I2C** | SDA | **D2** (GPIO 4) | Jalur Data |
| | SCL | **D1** (GPIO 5) | Jalur Clock |
| | VCC | **VU / Vin** | Wajib 5V agar terang |
| | GND | **G** (GND) | Ground |
| **Sensor DHT11**| DATA / OUT | **D5** (GPIO 14)| Data Sensor |
| | VCC | **3V3** | Power 3.3V |
| | GND | **G** (GND) | Ground |
| **LED 1** | Anoda (+) | **D6** (GPIO 12)| LED Merah |
| **LED 2** | Anoda (+) | **D7** (GPIO 13)| LED Kuning |
| **LED 3** | Anoda (+) | **D8** (GPIO 15)| LED Hijau |

> **Catatan:** Pastikan semua kaki negatif (Katoda) LED terhubung ke GND.

---

## 💻 Cara Instalasi & Menjalankan

### 1. Persiapan Database (XAMPP)
1.  Nyalakan **Apache** dan **MySQL** di XAMPP.
2.  Buka `http://localhost/phpmyadmin`.
3.  Buat database baru bernama `iot_project`.
4.  Import file SQL atau jalankan query berikut:
    ```sql
    CREATE TABLE sensor_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        suhu FLOAT,
        kelembaban FLOAT
    );
    ```

### 2. Konfigurasi Web Dashboard
1.  Copy folder project web ke dalam folder `htdocs` XAMPP.
2.  Buka file `index.php` dan sesuaikan konfigurasi MQTT Server (IP Broker & Port WebSocket).
3.  Akses melalui browser: `http://localhost/nama_folder_project`.

### 3. Konfigurasi Hardware (Arduino IDE)
1.  Buka file `.ino` di Arduino IDE.
2.  Install Library yang dibutuhkan via *Library Manager*:
    * `PubSubClient` (by Nick O'Leary)
    * `DHT sensor library` (by Adafruit)
    * `LiquidCrystal I2C` (by Frank de Brabander)
3.  Sesuaikan variabel `ssid`, `password`, dan `mqtt_server` dengan jaringan Anda.
4.  Upload program ke NodeMCU.

---

## 👨‍💻 Tim Pengembang

Project ini disusun oleh:

1.  **Natalie Gabriel**
    * NIM: G.231.23.0050
2.  **Bernardus Bima**
    * NIM: G.231.23.0057

---

## 📄 Lisensi

Project ini dibuat untuk memenuhi Tugas Akhir Mata Kuliah IoT di Universitas Semarang. Bebas digunakan untuk referensi belajar.
