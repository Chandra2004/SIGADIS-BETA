# SIGADIS (Sistem Informasi Gizi & Kesehatan Ibu Hamil) - GEMASTIK

Aplikasi terintegrasi multi-platform (**Web & Mobile Android**) untuk pemantauan kesehatan ibu hamil, pencegahan stunting, serta deteksi risiko kehamilan secara dini.

---

## 🛠️ Tech Stack

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** Vue 3 (Composition API) + Inertia.js
- **Styling:** TailwindCSS v4 + DaisyUI
- **Mobile Engine:** Capacitor v8 (Android)
- **WhatsApp Gateway:** Node.js Service (Baileys)
- **Database:** MySQL / PostgreSQL

---

## 📋 Prasyarat Sistem (Prerequisites)

| Kebutuhan | Versi Minimum / Rekomendasi |
| :--- | :--- |
| **PHP** | `^8.2` (dengan extension `pdo`, `mbstring`, `openssl`, `curl`, `gd`) |
| **Composer** | `^2.x` |
| **Node.js & NPM** | Node.js `^18.x` / `^20.x` & NPM `^9.x` |
| **Java JDK** | OpenJDK 17 atau 21 (`java --version` & `javac --version`) |
| **Android SDK & ADB** | Android Studio dengan Platform-Tools |

---

## 🚀 Setup Awal Proyek (Pertama Kali Clone)

Jalankan perintah berikut di terminal:

```bash
# 1. Install dependensi Backend & Frontend
composer install
npm install

# 2. Salin environment file
cp .env.example .env

# 3. Generate Application Key
php artisan key:generate

# 4. Konfigurasi database di .env, lalu migrasi & seeder
php artisan migrate --seed

# 5. Buat symlink storage
php artisan storage:link
```

---

## 💻 Panduan Menjalankan Proyek (Windows & macOS)

### 1. Web Development (Khusus Web Admin / Bidan / Faskes)

Jalankan server backend dan Vite secara bersamaan:

```bash
npm run dev:mobile
```
> Perintah ini otomatis menjalankan `php artisan serve --host=0.0.0.0 --port=8000` dan `npm run dev` (Vite) secara paralel.

Akses Web di browser: **`http://localhost:8000`**

---

### 2. Mobile Android Development (Role Ibu Hamil)

#### 🍏 Panduan untuk Pengguna macOS:
1. **Pastikan ADB terpasang:**
   - Jika sudah install Android Studio, tambahkan PATH ke `~/.zshrc`:
     ```bash
     echo 'export ANDROID_HOME=$HOME/Library/Android/sdk' >> ~/.zshrc
     echo 'export PATH=$PATH:$ANDROID_HOME/platform-tools' >> ~/.zshrc
     source ~/.zshrc
     ```
   - Cek dengan: `adb version`
2. **Buka Emulator Android Studio (AVD) atau Colok HP Fisik** (pastikan USB Debugging aktif).
3. **Jalankan Server Lokal di Terminal 1:**
   ```bash
   npm run dev:mobile
   ```
4. **Build & Jalankan Aplikasi ke Android di Terminal 2:**
   ```bash
   npm run cap:run
   ```

---

#### 🪟 Panduan untuk Pengguna Windows:

##### Opsi A: Menggunakan Emulator Android Studio (AVD) / HP Fisik
1. Pastikan `adb` sudah terdaftar di Environment Variables (PATH).
2. Hubungkan HP fisik (USB Debugging aktif) atau nyalakan AVD.
3. Jalankan server lokal:
   ```powershell
   npm run dev:mobile
   ```
4. Di terminal lain, deploy aplikasi:
   ```powershell
   npm run cap:run
   ```

##### Opsi B: Menggunakan Emulator MEmu (Port 21503)
1. Buka emulator **MEmu**.
2. Jalankan Keep-Alive daemon (agar koneksi ADB tidak putus otomatis):
   ```powershell
   npm run adb:keepalive:win
   ```
3. Di terminal terpisah, jalankan server backend:
   ```powershell
   npm run dev:mobile
   ```
4. Di terminal lainnya, jalankan build dan deploy ke MEmu:
   ```powershell
   npm run cap:run:memu
   ```

---

### 3. WhatsApp Notification Service

Untuk menjalankan gateway pengiriman OTP dan notifikasi WhatsApp:

```bash
# Menjalankan WhatsApp Service (Scan QR code saat pertama kali)
npm run whatsapp:service

# Logout sesi WhatsApp yang tersimpan
npm run whatsapp:logout
```

---

## 📜 Daftar Perintah NPM (NPM Scripts Reference)

| Perintah | Deskripsi | Kompatibilitas OS |
| :--- | :--- | :--- |
| `npm run dev` | Menjalankan Vite Dev Server | Windows & macOS |
| `npm run build` | Melakukan build bundle production frontend | Windows & macOS |
| `npm run dev:mobile` | Menjalankan Laravel Server (`0.0.0.0:8000`) + Vite bersamaan | Windows & macOS |
| `npm run cap:sync` | Build frontend dan sync aset web ke folder native Android | Windows & macOS |
| `npm run cap:run` | Sync dan deploy aplikasi ke emulator AVD / HP fisik aktif | Windows & macOS |
| `npm run cap:run:memu` | Konek ke MEmu (`127.0.0.1:21503`) lalu deploy aplikasi | Khusus Windows (MEmu) |
| `npm run adb:keepalive:win` | Menjaga koneksi ADB MEmu tetap hidup via PowerShell | Khusus Windows |
| `npm run assets:generate` | Generate icon dan splash screen Capacitor | Windows & macOS |
| `npm run whatsapp:service` | Menjalankan background worker WhatsApp Gateway | Windows & macOS |
| `npm run whatsapp:logout` | Menghapus sesi login WhatsApp bot | Windows & macOS |

---

## 🔧 Troubleshooting Umum

### 1. Aplikasi Mobile Tidak Bisa Konek ke Server Lokal (`Network Error` / `ERR_CONNECTION_REFUSED`)
Jika HP/Emulator tidak bisa menghubungi `http://localhost:8000`, lakukan reverse port ADB:
```bash
adb reverse tcp:8000 tcp:8000
adb reverse tcp:5173 tcp:5173
```

### 2. Device / Emulator Tidak Terdeteksi
Cek daftar perangkat yang terhubung:
```bash
adb devices
```
Jika kosong:
- **HP Fisik:** Pastikan mode **USB Debugging** aktif dan izinkan otorisasi komputer (*Always allow*).
- **MEmu:** Jalankan `adb connect 127.0.0.1:21503`.
- **Android Studio AVD:** Buka *Device Manager* di Android Studio dan klik tombol *Play* pada Virtual Device.
