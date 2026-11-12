# Izin Keluar Sekolah — SMKN 5 Surakarta

Aplikasi PWA **Laravel 12 + Filament 3 + Tailwind CSS 4 + MySQL** untuk permohonan izin
keluar sekolah. Siswa mengisi form, mendapat barcode (QR), guru memindai barcode lalu
memberikan persetujuan dengan token. Setelah semua guru menyetujui, halaman barcode
berubah menjadi rekap "DIIZINKAN MENINGGALKAN SEKOLAH" yang bisa dicetak.

## Alur

1. **Siswa** — buka `/`, isi form: data siswa — nama, NIS, no. absen, kelas (dropdown;
   jurusan terisi otomatis) — **bisa ditambah untuk banyak siswa** dalam satu permohonan
   (tombol "+ Tambah Siswa"), keperluan, jam ke mulai–selesai (1–10), daftar dinamis
   mapel & guru yang ditinggal (tombol "+ Tambah Mapel & Guru", guru difilter dari
   mapping guru–mapel), kendaraan, cara keluar (Dijemput / Pulang Sendiri + nama
   penjemput bila dijemput).
2. **Submit** — `POST /izin` menyimpan permohonan (kode `IZN-YYYYMMDD-XXXX`) beserta
   semua siswanya di `permohonan_siswa`, membuat 1 item persetujuan per pasangan
   mapel–guru **plus 1 item otomatis untuk wali kelas dari setiap kelas siswa**,
   lalu redirect ke `/izin/{kode}`.
3. **Halaman barcode** — `GET /izin/{kode}` menampilkan QR code (isi URL absolut
   `/guru/{barcode_kode}`) + barcode Code-39 + detail permohonan + status tiap guru.
4. **Guru** — scan QR → `GET /guru/{barcode_kode}`: identitas siswa + tabel item.
   Form persetujuan: (1) dropdown nama guru — hanya guru dari item yang **belum**
   disetujui, beserta peran & mapelnya; (2) isian token (dibuat admin). Token salah
   atau token guru lain → error "Token salah", tidak tersimpan. Token benar → item
   disetujui, tercatat di `persetujuan`, status permohonan naik
   `menunggu → disetujui_sebagian → disetujui_semua`.
5. **Rekap** — bila semua item disetujui, `/izin/{kode}` berubah menjadi rekap lengkap
   (banner hijau "DIIZINKAN MENINGGALKAN SEKOLAH", tabel semua guru + waktu persetujuan,
   token ditampilkan mask `AB****`) + tombol **Cetak**.
6. **Admin** — Filament di `/admin`: dashboard widget (permohonan hari ini, menunggu,
   disetujui, total), resource Permohonan Izin (rekap + filter status/kelas/jurusan/
   tanggal + relation manager item & persetujuan), Jurusan, Kelas (mapping wali kelas),
   Guru, Mata Pelajaran, Mapping Guru & Mapel, Token Guru (generate token acak 6 karakter).
7. **QR Pos Statis** — admin membuat pos di menu **QR Pos Statis** (`/admin/pos-qrs`):
   isi nama pos (mis. "Pos Satpam Gerbang Utama"), kode QR dibuat otomatis sekali dan
   tidak berubah. Tombol **Cetak QR** membuka halaman cetak — cetak dan tempel di pos.
8. **Siswa kirim rekap** — setelah izin disetujui semua guru, siswa memindai QR pos
   (fitur **Scan QR Pos Satpam** di halaman form & halaman rekap, atau kamera bawaan HP)
   → halaman pos `/pos/{kode}` terbuka → siswa mengisi **kode izin** → rekap tersimpan
   ke pos itu (tabel `pos_kirim`). Rekap hanya bisa dikirim bila status sudah
   `disetujui_semua`; pengiriman ulang tidak menggandakan catatan.
9. **Satpam cek tanpa login** — halaman pos `/pos/{kode}` bersifat publik dan selalu
   menampilkan bagian **"Rekap Masuk ke QR Ini — Hari Ini"**: daftar rekap yang sudah
   menempel ke QR tersebut hari ini. Satpam cukup memindai QR (yang ditempel di pos
   atau yang dibawa siswa) — rekap langsung tampil, **tanpa login**. Bila daftarnya
   kosong, tampil tanda bahwa QR belum berisi rekap untuk hari ini. Satpam
   mencocokkan nama siswa pada daftar dengan siswa yang membawa QR, lalu menekan
   tombol **Izinkan Keluar** pada rekap tersebut: data rekap **dihapus dari QR itu**
   (baris `pos_kirim`-nya) karena siswa sudah benar-benar keluar — QR kembali kosong.
   Data permohonan di admin tidak ikut terhapus, tetap ada sebagai arsip.
10. **Login satpam (opsional)** — akun role `satpam` (login di `/satpam/login`) tetap
    tersedia untuk melihat rekap **semua pos sekaligus** di dashboard `/satpam` dan
    riwayat per pos di `/satpam/pos/{kode}`; admin juga boleh masuk. QR dibuat admin
    sebanyak kebutuhan di menu QR Pos Statis (satu QR bisa dipakai banyak siswa, atau
    dibuat khusus per kelompok/keperluan).

## Akun admin default (seeder)

| | |
|---|---|
| Email | `admin@smkn5.sch.id` |
| Password | `admin12345` |

> ⚠️ **Wajib ganti password setelah instalasi pertama** (di Filament: klik nama akun
> kanan atas → profil, atau buat user baru lalu hapus akun ini). Jangan biarkan
> password default aktif di server yang diekspos ke internet.

## Akun satpam default (seeder)

| | |
|---|---|
| Email | `satpam@smkn5.sch.id` |
| Password | `satpam12345` |
| Login di | `/satpam/login` |

Akun satpam hanya bisa membuka halaman satpam (`/satpam`) dan halaman cetak QR —
**tidak** bisa masuk panel admin. ⚠️ Wajib ganti password juga sebelum produksi.

## Import data kelas

Di menu **Kelas** (`/admin/kelas`) ada tombol **Import File** untuk memasukkan
banyak kelas sekaligus dari file **CSV atau XLSX**:

- Kolom: **Jurusan** (kode jurusan, mis. `RPL` — nama jurusan persis juga bisa),
  **Tingkat** (`X`/`XI`/`XII`/`XIII`, angka 10–13 juga diterima),
  **Nama Kelas**, **Wali Kelas** (nama guru persis, boleh kosong).
- Template XLSX bisa diunduh dari jendela import (atau `GET /kelas/template`).
- Kelas yang sudah ada (jurusan + tingkat + nama sama) **diperbarui** wali
  kelasnya — sel wali yang kosong tidak menghapus wali lama. Baris yang salah
  (jurusan/tingkat tidak dikenal) dilewati dan dilaporkan di notifikasi hasil;
  wali kelas yang tidak ditemukan membuat kelas tetap disimpan tanpa wali,
  dengan peringatan.
- CSV dengan pemisah `;` (Excel Indonesia) maupun `,` sama-sama didukung.

## Token guru contoh (seeder — CONTOH SAJA)

Token dibuat admin di `/admin/guru-tokens`. Token contoh di bawah hanya untuk uji coba;
**wajib regenerate semua token sebelum dipakai produksi** (tombol "Generate Ulang" di
admin, atau tombol "Acak" saat membuat token baru).

Fitur token massal di menu Token Guru (tombol di atas tabel):

- **Generate Token Semua Guru** — membuatkan token untuk semua guru yang **belum**
  punya token. Token guru yang sudah ada tidak diubah. Aman dijalankan berulang kali.
- **Ubah Token Serentak** — admin mengisi **satu token baru** pada kolom yang
  disediakan, dan **semua** guru langsung memakai token yang sama itu. Token lama
  berhenti berlaku seketika; ada tombol "Acak" bila ingin token hasil acakan.
  Catatan: dengan token seragam, token tidak lagi membedakan guru — siapa pun
  yang mengetahui token tersebut dapat menyetujui item guru mana pun. Gunakan
  Generate Ulang per guru bila ingin token unik per guru lagi.
- **Generate Ulang** (per baris guru) — tetap ada untuk mengganti token satu guru saja.

| Guru | Token contoh |
|---|---|
| Budi Santoso, S.Pd. | `A1B2C3` |
| Siti Rahmawati, S.Pd. | `D4E5F6` |
| Agus Prasetyo, S.T. | `G7H8J9` |
| Dewi Lestari, M.Pd. | `K1L2M3` |
| Rina Wijaya, S.Pd. | `N4P5Q6` |
| Joko Susilo, S.T. | `R7S8T9` |
| Maya Kusuma, S.Pd. | `U1V2W3` |
| Andi Firmansyah, S.Kom. | `X4Y5Z6` |
| Lilis Suryani, S.Pd. | `B2C3D4` |
| Hendra Gunawan, S.T. | `E5F6G7` |
| Nina Marlina, S.Pd. | `H8J9K1` |
| Fajar Nugroho, S.T. | `L2M3N4` |
| Ratna Dewi, S.Pd. | `P5Q6R7` |
| Eko Purnomo, S.Kom. | `S8T9U1` |

Nama guru/siswa di seeder adalah **fiktif**; jurusan memakai jurusan nyata SMKN 5
(RPL, TEI, TPM, TOI, TKP, DPIB, TITL, TAV).

## Data contoh yang di-seed

- 8 jurusan, 50 kelas (X/XI/XII A & B per jurusan + XIII A & B untuk TOI),
  masing-masing dengan 1 wali kelas.
- 14 guru, 17 mapel (8 umum + 9 jurusan), 22 mapping guru–mapel, 14 token contoh.
- 1 user admin.

## Instalasi (pengembangan / VM)

```bash
composer install
cp .env.example .env          # lalu sesuaikan DB (lihat bawah)
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

**Database lokal di VM ini (tanpa MySQL):** `.env` memakai SQLite
(`DB_CONNECTION=sqlite`, `database/database.sqlite`) agar `php artisan test` jalan.
**Untuk produksi/Laragon pakai MySQL** — `.env.example` sudah berisi konfigurasi MySQL
(`DB_DATABASE=izin_keluar_smkn5`). Lihat `LARAGON_SETUP.md`.

## Route penting

| Route | Keterangan |
|---|---|
| `GET /` | Form siswa |
| `POST /izin` | Simpan permohonan |
| `GET /izin/{kode}` | Halaman barcode / rekap disetujui |
| `GET /guru/{barcode_kode}` | Halaman persetujuan guru (scan QR) |
| `POST /guru/{barcode_kode}/setujui` | Simpan persetujuan guru |
| `GET /offline` | Halaman fallback PWA offline |
| `/admin` | Panel admin Filament (login) |
| `GET /pos/{kode}` | Halaman pos tujuan QR statis: siswa kirim rekap di sini + daftar rekap hari ini tampil publik untuk cek satpam tanpa login |
| `POST /pos/{kode}/kirim` | Siswa mengirim rekap izin yang sudah disetujui ke pos |
| `POST /pos/{kode}/izinkan/{kirim}` | Satpam mengizinkan keluar — menghapus rekap tersebut dari QR |
| `GET /pos/{kode}/cetak` | Halaman cetak QR pos (login admin/satpam) |
| `GET /satpam` | Dashboard satpam (login role satpam/admin) |
| `GET /satpam/pos/{kode}` | Cek isi satu QR pos: rekap apa saja yang sudah masuk |
| `GET /satpam/login` | Login satpam |

## PWA

- `public/manifest.webmanifest` — name "Izin Keluar SMKN 5 Surakarta", display
  standalone, theme/background hijau sekolah (`#15803d` / `#f0fdf4`).
- `public/sw.js` — cache-first untuk halaman utama + form, network-first untuk
  navigasi dengan fallback ke `/offline`.
- Ikon `public/icons/icon-192.png`, `icon-512.png`, `icon-maskable-512.png`.

## Skema database

Lihat `database/migrations/` dan `schema.sql` (dump MySQL dari struktur
migrasi). Tabel: `jurusan`, `kelas`, `guru`, `mapel`, `guru_mapel`, `guru_token`,
`permohonan_izin`, `permohonan_item`, `persetujuan`, `permohonan_siswa`,
`pos_qr`, `pos_kirim` (+ tabel bawaan Laravel: `users` — kini dengan kolom
`role` admin/satpam —, `cache`, `jobs`).

## Tes

```bash
php artisan test
```

47 test, 263 assertion — mencakup submit form (termasuk banyak siswa per
permohonan) + item wali kelas otomatis, validasi jam & penjemput, NIS opsional,
halaman barcode (QR), persetujuan token benar/salah/token guru lain, tolak
persetujuan ganda, rekap setelah semua disetujui, halaman guru, login admin
(termasuk fallback POST), QR pos statis (kirim rekap hanya setelah disetujui
semua, anti-duplikat), dan akses satpam (login, cek pos berisi/kosong, satpam
ditolak dari panel admin, scan QR tanpa login yang langsung menampilkan
rekap hari ini, dan tombol Izinkan yang menghapus rekap dari QR, serta tombol Tambah QR Code di panel admin beserta alur buatnya).
Terakhir dijalankan: **47 passed** (2026-11-12).

## Yang belum terverifikasi

- MySQL sungguhan di Laragon (di VM ini tes memakai SQLite; struktur migrasi
  MySQL-compatible, tapi belum diuji terhadap server MySQL).
- Scan QR dari HP nyata (QR mengarah ke URL absolut `/guru/{barcode_kode}` — pastikan
  `APP_URL` di `.env` Laragon sesuai alamat yang bisa dijangkau HP).
- Instalasi PWA (Add to Home Screen) di HP nyata.
