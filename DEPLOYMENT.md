# Deployment development di Dockploy

Repository ini siap dideploy sebagai **Docker Compose**. Satu deployment membuat empat container: `web` (Nginx), `app` (Laravel/PHP-FPM), `scheduler` (jadwal 10 detik), dan `queue`. Tidak ada service database — sesi/cache pakai driver `file` dan queue pakai `sync`. Folder `storage` berada pada named volume `laravel_storage` sehingga tetap ada setelah redeploy.

## 1. Persiapan repository

Push seluruh perubahan ini ke repository Git. Jangan commit `.env` atau `firebase-credentials.json`; keduanya berisi rahasia dan sudah ada di `.gitignore`/`.dockerignore`.

## 2. Buat project di Dockploy

1. Buat **Project** lalu **Service → Compose**.
2. Hubungkan repository dan branch development Anda.
3. Pilih file compose `docker-compose.yml` dan aktifkan build saat deploy.
4. Tambahkan domain ke service **web**, port internal **80**, lalu aktifkan HTTPS/Let's Encrypt.
5. Jangan publikasikan port host untuk `database`, `app`, `scheduler`, atau `queue`. Dokploy akan merutekan domain ke service `web` melalui jaringan internalnya.

## 3. Isi environment variables di Dockploy

Masukkan seluruh nilai di bawah pada halaman Environment Dockploy. Compose meneruskannya ke semua service Laravel; file `.env` di server tidak diperlukan. Aplikasi tidak memakai database, jadi tidak ada variabel `DB_*`.

| Variabel | Nilai/panduan |
| --- | --- |
| `APP_KEY` | Buat lokal dengan `php artisan key:generate --show`; hasilnya diawali `base64:`. |
| `APP_URL` | URL HTTPS final, mis. `https://dev-dashboard.example.com`. |
| `APP_ENV` | `development` atau `production`; untuk server publik disarankan `production`. |
| `APP_DEBUG` | `false` pada server yang dapat diakses publik. |
| `FIREBASE_*` | Salin dari Firebase Project Settings. |
| `FONNTE_TOKEN`, `WA_TARGET_NUMBER` | Token Fonnte dan nomor format `628…`. |
| `RECAP_SECRET` | String acak panjang khusus endpoint rekap. |
| `ALERT_AMONIA_THRESHOLD`, `ALERT_THI_THRESHOLD`, `ALERT_COOLDOWN_MINUTES` | Opsional; default `25`, `83`, `360` (6 jam). |
| `HISTORY_MAX_AMONIA_PPM` | Opsional; default `50`. Data lebih besar dari nilai ini tidak muncul pada riwayat, grafik, dan ekspor. |

Gunakan nilai dari `.env.example` sebagai daftar lengkap. Aplikasi berjalan tanpa database (cache/sesi `file`, queue `sync`), jadi tidak ada variabel `DB_*` yang perlu diisi.

## 4. Firebase Admin credentials

Jangan unggah file service-account JSON ke GitHub. Untuk hosting yang menyediakan secret environment variable, termasuk Wasmer, masukkan isi file tersebut dalam Base64 sebagai `FIREBASE_CREDENTIALS_BASE64`. Saat aplikasi mulai, Laravel memvalidasi nilainya lalu membuat `storage/app/firebase-credentials.json` secara lokal di container dengan izin file terbatas.

Di PowerShell, buat nilai Base64 satu baris dari file JSON yang diunduh dari Firebase Console:

```powershell
[Convert]::ToBase64String([IO.File]::ReadAllBytes('C:\path\ke\firebase-credentials.json'))
```

Salin seluruh hasilnya ke secret `FIREBASE_CREDENTIALS_BASE64`. Tetap gunakan `FIREBASE_CREDENTIALS=/var/www/html/storage/app/firebase-credentials.json`.

Untuk Wasmer: buka aplikasi di dashboard, pilih **Settings → Environment Vars**, tambahkan `FIREBASE_CREDENTIALS_BASE64`, lalu pilih **Save and Redeploy**. Wasmer menyimpan environment variable tersebut sebagai secret dan tidak memasukkannya ke repository. [Dokumentasi Wasmer](https://docs.wasmer.io/edge/learn/secrets/).

Metode upload file ke volume tetap dapat dipakai untuk Docker Compose; cukup biarkan `FIREBASE_CREDENTIALS_BASE64` kosong dan upload JSON ke `storage/app/firebase-credentials.json`.

## 5. Deploy dan verifikasi

Deploy. Saat container `app` pertama kali hidup, ia otomatis membuat symbolic link `public/storage` dan menyimpan cache konfigurasi. Cek berikut setelah deployment:

1. Buka `https://domain-anda/up`; harus mengembalikan status sehat Laravel.
2. Buka dashboard dan pastikan data Firebase tampil.
3. Lihat log `scheduler`; proses `php artisan schedule:work` harus aktif, karena alert berjalan setiap 10 detik.
4. Jalankan `php artisan whatsapp:test` dari terminal container `app` untuk menguji Fonnte.

## Operasional

- Redeploy aman dan tidak menghapus volume `laravel_storage`.
- Backup aman kredensial Firebase dari sumber aslinya; jangan backup atau commit file JSON ke repository.
- Log diarahkan ke stderr agar terlihat di Dockploy. Untuk menelusuri masalah, cek log service `app`, `scheduler`, dan `queue`.

## Wasmer Edge

Wasmer Edge bukan runtime Docker Compose yang selalu menyala; instance HTTP dapat dihentikan saat idle. Karena itu, jangan mengandalkan container `scheduler` pada `docker-compose.yml` untuk deployment Wasmer. Gunakan konfigurasi root [wasmer.toml](wasmer.toml) dan [app.yaml](app.yaml), lalu deploy dengan `wasmer deploy`.

`app.yaml` membuat Wasmer Jobs yang menjalankan command Artisan secara langsung: rekap setiap `13:00 UTC` (`20:00 WIB`) dan pemeriksaan alert setiap satu menit. Volume persisten `/data` menyimpan kredensial Firebase dan cache cooldown, sehingga alert tidak berulang setiap menit selama kondisi ekstrem masih berlangsung. Tambahkan secret aplikasi melalui dashboard/CLI Wasmer—minimal `APP_KEY`, seluruh `FIREBASE_*`, `FONNTE_TOKEN`, `WA_TARGET_NUMBER`, dan `FIREBASE_CREDENTIALS_BASE64`—kemudian redeploy. Jangan menyimpan secret pada `app.yaml`.
