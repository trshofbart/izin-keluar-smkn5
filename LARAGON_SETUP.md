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
- **CSS tidak tampil:** pastikan `public/build/` ada (sudah termasuk di zip). Bila
  hilang, jalankan `npm install && npm run build`.
- **QR tidak bisa discan dari HP:** `APP_URL` harus alamat yang bisa dijangkau HP
  (bukan `http://localhost`). Bersihkan cache config: `php artisan config:clear`.
- **Login admin gagal:** pastikan seeder sudah jalan (`php artisan db:seed
  --class=AdminUserSeeder`) dan password `admin12345`.
