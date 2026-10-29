# SKARP IoT Dashboard — Laravel

Dashboard monitoring sensor IoT realtime berbasis Laravel + Blade + Firebase JS SDK.

## Tech Stack
- **Framework:** Laravel 11
- **Styling:** Tailwind CSS (CDN)
- **Chart:** Chart.js (CDN)
- **Realtime:** Firebase JS SDK v10 (CDN) — `onValue` listener
- **WA Bot:** Fonnte API via Laravel HTTP Client
- **Scheduler:** Laravel Task Scheduler (cron)

---

## Instalasi

### 1. Install dependencies
```bash
composer install
```

### 2. Setup environment
```bash
cp .env.example .env
php artisan key:generate
```

Isi variabel berikut di `.env`:

| Variabel | Cara dapat |
|---|---|
| `FIREBASE_API_KEY` | Firebase Console → Project Settings → General |
| `FIREBASE_DATABASE_URL` | Firebase Console → Realtime Database → Data tab |
| `FIREBASE_MESSAGING_SENDER_ID` | Project Settings → General → Your apps |
| `FIREBASE_APP_ID` | Project Settings → General → Your apps |
| `FIREBASE_CREDENTIALS` | Project Settings → Service Accounts → Generate new private key → simpan ke `storage/app/firebase-credentials.json` |
| `FONNTE_TOKEN` | [fonnte.com](https://fonnte.com) → Dashboard → Token |
| `WA_TARGET_NUMBER` | Nomor tujuan, format: `628xxxxxxxxxx` |
| `ALERT_AMONIA_THRESHOLD` | Ambang amonia untuk peringatan tinggi, default: `25` ppm |
| `ALERT_THI_THRESHOLD` | Ambang THI untuk peringatan tinggi, default: `83` |
| `ALERT_COOLDOWN_MINUTES` | Waktu tunggu sebelum sistem memindai ulang kondisi ekstrem, default: `360` menit (6 jam). |
| `HISTORY_MAX_AMONIA_PPM` | Batas data amonia valid untuk riwayat, grafik, dan ekspor; nilai `> 50` ppm diabaikan. |
| `RECAP_SECRET` | String acak bebas, contoh: `skarp-iot-2025-secret` |

Untuk deployment tanpa file rahasia (misalnya Wasmer), isi `FIREBASE_CREDENTIALS_BASE64` dengan JSON service-account Firebase yang sudah diubah menjadi Base64 satu baris. Aplikasi akan membuat file kredensial secara otomatis ketika berjalan.

### 3. Jalankan
```bash
php artisan serve
```
Buka [http://localhost:8000](http://localhost:8000)

---

## WhatsApp Recap

### Test manual
```bash
php artisan whatsapp:test

# Atau dengan isi pesan sendiri
php artisan whatsapp:test "Tes WhatsApp dari SKARP"
```

Command tersebut mengirim langsung ke `WA_TARGET_NUMBER` dan tidak membutuhkan koneksi Firebase. Untuk menguji rekap lengkap yang membaca Firebase:
```bash
php artisan sensor:send-daily-recap
```

### Jadwal otomatis
Tambahkan ke crontab server:
```
* * * * * php /path/ke/project/artisan schedule:run >> /dev/null 2>&1
```

Jadwal default:

- Rekap setiap hari pukul **20:00 WIB**.
- Setelah WhatsApp terkirim, sistem menunggu 6 jam tanpa memindai Firebase. Setelahnya, sistem hanya mengirim ulang bila amonia masih `> 50 ppm` atau THI masih `> 85`; siklus ini berulang seterusnya.
- Pengiriman dianggap gagal bila Fonnte mengembalikan status penolakan, meski HTTP-nya `200`; alert akan dicoba lagi pada jadwal berikutnya dan tidak masuk cooldown.

Pada Windows untuk pengembangan, jalankan proses scheduler terpisah:
```bash
php artisan schedule:work
```

Jadwal aplikasi didefinisikan di `routes/console.php`.

### Hosting di Wasmer Edge

Wasmer Edge tidak menjalankan `docker-compose.yml` atau proses `schedule:work` secara terus-menerus: instance HTTP dapat berhenti saat idle. Repository ini menyediakan `wasmer.toml` dan `app.yaml` agar **Wasmer Jobs** memanggil command Artisan langsung:

- rekap harian: `20:00 WIB` (`13:00 UTC`),
- cek kondisi ekstrem: setiap `1 menit` (batas minimum Wasmer Jobs).

File `app.yaml` juga membuat volume persisten `/data`: kredensial Firebase hasil decode dan cache cooldown alert disimpan di sana. Ini mencegah warning terkirim berulang setiap menit saat nilai ekstrem belum turun.

Deploy dari root repository dengan `wasmer deploy`, lalu masukkan seluruh environment variable pada Wasmer, khususnya `FONNTE_TOKEN`, `WA_TARGET_NUMBER`, `FIREBASE_CREDENTIALS_BASE64`, dan `APP_KEY`. Jangan menyalin rahasia ke `app.yaml`. Manifest sengaja tidak mengunci namespace package, sehingga CLI Wasmer akan memakai akun Anda saat deploy. Setelah deploy, periksa log dua job di dashboard Wasmer; keduanya harus berstatus sukses.

---

## Struktur Firebase
Data dari Arduino dikirim ke path:
```
SKARP/SensorData/
  ├── Amonia_PPM
  ├── Amonia_Raw
  ├── Kelembapan
  ├── Suhu
  └── THI
```

---

## Deploy ke Shared Hosting / VPS

1. Upload semua file (kecuali `node_modules`, `.git`)
2. Jalankan `composer install --no-dev`
3. Set `.env` di server
4. `php artisan key:generate`
5. Pastikan `storage/` dan `bootstrap/cache/` writable
6. Setup crontab untuk scheduler
7. Custom domain: arahkan domain ke folder `public/`
