# Setup Laragon — Izin Keluar SMKN 5 Surakarta

Panduan instalasi di laptop/server Windows memakai Laragon (Apache + MySQL + PHP 8.3).

## 1. Salin proyek

1. Ekstrak `izin-keluar-smkn5-laragon.zip` ke `C:\laragon\www\izin-keluar-smkn5`.
   (Zip sudah berisi `vendor/` dan hasil build Vite, jadi **tidak perlu**
   `composer install` / `npm install`.)
2. Salin `.env.laragon` menjadi `.env` di folder proyek.
3. Sesuaikan `APP_URL` di `.env` dengan alamat akses Anda, contoh:
   - Laragon lokal: `APP_URL=http://izin-keluar-smkn5.test`
   - IP publik + port: `APP_URL=http://36.89.237.203:8000`
4. Buat application key (sekali saja, di terminal Laragon / di folder proyek):

```bash
php artisan key:generate
```

## 2. Buat database MySQL

1. Jalankan Laragon → klik **Database** (HeidiSQL) atau buka phpMyAdmin.
2. Buat database baru bernama **`izin_keluar_smkn5`** (collation `utf8mb4_unicode_ci`).
3. Pastikan `.env` berisi:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=izin_keluar_smkn5
DB_USERNAME=root
DB_PASSWORD=
```

> Di Laragon standar, user MySQL `root` tanpa password. Jika MySQL Anda memakai
> password, isi `DB_PASSWORD` sesuai.

## 3. Migrasi + seed

Di terminal Laragon, dari folder proyek:

```bash
php artisan migrate --seed
```

Perintah ini membuat semua tabel dan mengisi data contoh: 8 jurusan, 50 kelas
(dengan wali kelas), 14 guru, 17 mapel, 22 mapping guru–mapel, 14 token contoh,
dan 1 user admin.

Alternatif: import `schema.sql` (struktur tabel MySQL) lewat HeidiSQL, lalu jalankan
`php artisan db:seed` untuk data contoh. Jangan jalankan `migrate` setelah import
`schema.sql` bila tabel `migrations` sudah terisi dari dump — pilih salah satu cara.

## 4. Akses aplikasi

- **Form siswa:** `http://izin-keluar-smkn5.test/` (atau sesuai `APP_URL`)
- **Panel admin:** `http://izin-keluar-smkn5.test/admin`

Login admin default:

| Email | Password |
|---|---|
| `admin@smkn5.sch.id` | `admin12345` |

## 4a. Akses lewat localhost (pilih salah satu cara)

**Cara A — VirtualHost port 8001 (disarankan).** Aplikasi diakses sebagai
`http://localhost:8001/` tanpa path di belakang, sama seperti domain `.test`
— cara paling aman karena semua aset (CSS, Livewire, QR) memakai alamat root:

1. Salin file `izin-keluar-smkn5-vhost.conf` (ada di repo) menjadi
   `C:\laragon\etc\apache2\sites-enabled\00-izin-keluar-smkn5.conf`.
2. Restart Apache di Laragon (Stop lalu Start).
3. Di `.env`: `APP_URL=http://localhost:8001`, lalu
   `php artisan config:clear`.
4. Buka `http://localhost:8001/` (admin: `http://localhost:8001/admin`).

Port 8001 dipakai supaya tidak bentrok dengan Buku Induk (port 8000).

**Cara B — Subdirektori `http://localhost/izin-keluar-smkn5/`.** Pakai Apache
`Alias` (file `laragon/izin-keluar-smkn5-alias.conf` di repo):

1. Salin `laragon/izin-keluar-smkn5-alias.conf` menjadi
   `C:\laragon\etc\apache2\sites-enabled\00-izin-keluar-smkn5-alias.conf`.
2. Restart Apache di Laragon (Stop lalu Start).
3. Di `.env`: `APP_URL=http://localhost/izin-keluar-smkn5`, lalu
   `php artisan config:clear`.
4. Buka `http://localhost/izin-keluar-smkn5/` (admin:
   `http://localhost/izin-keluar-smkn5/admin`).

Cara ini sudah diuji langsung dengan Apache: form, CSS, Livewire, login
admin, dan submit form semuanya jalan di subdirektori.

> Jangan memakai `.htaccess` di root proyek yang meneruskan request ke
> `public/` untuk subdirektori — itu TIDAK jalan untuk Laravel: file
> statis memang tampil, tapi semua route 404 (Laravel membaca path dari
> `REQUEST_URI`, dan prefix subdirektori tidak bisa dihilangkan oleh
> rewrite). Pola yang sama di Buku Induk juga belum pernah teruji di
> Apache subdirektori. `Alias` di atas adalah cara yang benar.

Tanpa konfigurasi Apache apa pun, alamat subdirektori yang tetap jalan
adalah `http://localhost/izin-keluar-smkn5/public/` (dengan `/public` di
tengah) — tapi alamat ini membuat QR dan login Livewire rawan gagal,
jadi pakai Cara A atau Cara B di atas.

PWA (service worker + manifest) sudah dibuat relatif supaya tetap jalan
di subdirektori maupun di root.

**Cara C — `php artisan serve` (paling cepat untuk tes).** Dari folder proyek:

```bash
php artisan serve --port=8001
```

Lalu buka `http://localhost:8001/` (dengan `APP_URL=http://localhost:8001`).
Terminal harus tetap jalan selama aplikasi dipakai; tutup terminal =
aplikasi mati. Untuk akses dari HP di WiFi yang sama:
`php artisan serve --host=0.0.0.0 --port=8001` lalu buka
`http://IP-LAN-laptop:8001/` di HP.

## 5. WAJIB sebelum dipakai / diekspos

1. **Ganti password admin** — login ke `/admin`, klik nama akun kanan atas →
   ubah password (atau buat user admin baru lalu hapus akun default).
2. **Regenerate semua token guru** — di `/admin/guru-tokens`, tiap guru → aksi
   "Generate Ulang". Token contoh di seeder (`A1B2C3`, dst.) bersifat publik di
   README dan **tidak boleh dipakai produksi**.
3. **Perbarui data master** — ganti nama guru fiktif di `/admin/gurus` dengan data
   asli, periksa mapping wali kelas di `/admin/kelas` dan mapping guru–mapel di
   `/admin/guru-mapels`.
4. Pastikan `APP_DEBUG=false` dan `APP_ENV=production` di `.env` bila server
   diekspos ke internet.

## 6. Update aplikasi (bila ada versi baru)

```bash
git pull            # bila memakai repo git
php artisan migrate
php artisan config:clear
php artisan view:clear
```

## Troubleshooting

- **500 saat buka halaman:** cek `storage/logs/laravel.log`; pastikan folder
  `storage` dan `bootstrap/cache` bisa ditulis.
- **"No such table" / tabel kosong:** jalankan `php artisan migrate --seed` dari
  folder proyek yang benar; periksa `DB_DATABASE` di `.env`.
- **CSS tidak tampil / tampilan polos:** pastikan `public/build/` ada (sudah
  termasuk di zip, dan ikut ter-commit di GitHub sejak 2026-11-11). Bila clone
  lama, jalankan `git pull`. Bila tetap hilang, jalankan
  `npm install && npm run build` — sebagai pengaman, layout juga memuat
  Tailwind dari CDN bila `public/build/` tidak ada.
- **QR tidak bisa discan dari HP:** `APP_URL` harus alamat yang bisa dijangkau HP
  (bukan `http://localhost`). Bersihkan cache config: `php artisan config:clear`.
- **Login admin gagal:** pastikan seeder sudah jalan (`php artisan db:seed
  --class=AdminUserSeeder`) dan password `admin12345`.
- **Error "POST method is not supported for route admin/login" (405) saat
  login:** login Filament normalnya lewat Livewire. Error ini muncul bila
  JavaScript Livewire tidak jalan di browser (biasanya karena diakses lewat
  subdirektori/`public` atau `APP_URL` tidak cocok dengan alamat yang dibuka,
  sehingga aset `livewire.js` 404). Perbaikan sudah ada sejak 2026-11-11
  (route fallback POST `/admin/login`), jadi jalankan `git pull` lalu
  `php artisan config:clear && php artisan route:clear && php artisan view:clear`,
  dan pastikan `APP_URL` di `.env` sama dengan alamat yang kamu buka.
  Akses yang benar di Laragon: `http://izin-keluar-smkn5.test/admin` (bukan
  `http://localhost/izin-keluar-smkn5/public/admin`).
