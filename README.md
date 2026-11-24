<<<<<<< HEAD
# Sistem Informasi Pendaftaran Sidang Sarjana
Program Studi Sistem Informasi · Universitas Gunadarma · Regu Tulip (4KA01)

Aplikasi web fullstack sesuai **SRS v1.0** dan **SDD**:
- Laravel 11 (MVC, Blade) dengan Bootstrap 5
- MySQL/MariaDB
- Tiga peran: **Mahasiswa**, **Admin** (staf prodi), dan **Super Admin** (Kaprodi/Bagian Akademik)
- Halaman publik untuk landing page dan cek status pemberkasan

## 1. Kebutuhan
- PHP 8.2 atau lebih baru, dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `intl`
- MySQL 5.7+ atau MariaDB 10.4+
- Composer, hanya jika folder `vendor/` dihapus. Folder `vendor/` sudah disertakan, jadi bisa langsung jalan di cPanel tanpa Composer.

## 2. Instalasi lokal
```bash
cp .env.example .env          # lalu isi DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed    # membuat 14 tabel + data master awal
php artisan serve             # buka http://127.0.0.1:8000
```
Sebagai alternatif dari `migrate --seed`, impor `database/sidang_sarjana.sql`. File itu berisi struktur tabel dan data master awal.

## 3. Deploy ke cPanel (Rumahweb)
1. Unggah seluruh isi folder ke luar `public_html`, misalnya `~/sidang-sarjana`.
2. Isi `public_html` dengan isi folder `public/`. Ubah dua path di `public_html/index.php` ke `../sidang-sarjana/vendor/autoload.php` dan `../sidang-sarjana/bootstrap/app.php`. Cara lain: arahkan document root domain ke `sidang-sarjana/public`.
3. Buat database MySQL di cPanel, lalu impor `database/sidang_sarjana.sql`.
4. Atur `.env` sebagai berikut:
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda.id`
   - kredensial DB
   - **SMTP email hosting**: `MAIL_MAILER=smtp`, `MAIL_HOST`, `MAIL_PORT=465`, `MAIL_SCHEME=smtps`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`
5. Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis, lalu aktifkan SSL (HTTPS).

## 4. Akun awal (dari seeder)
| Peran | Email | Kata sandi |
|---|---|---|
| Super Admin | superadmin@sidangsarjana.id | `Superadmin#2026` |
| Admin | admin@sidangsarjana.id | `Admin#2026` |

**Segera ganti kedua kata sandi ini** lewat menu Profil setelah login pertama. Akun mahasiswa dibuat lewat halaman **Daftar**, dengan verifikasi OTP email.

## 5. Fitur dan keterlacakan kebutuhan
| Modul | Halaman | FR |
|---|---|---|
| Publik | Landing (alur, FAQ, kontak); Cek Status by NPM/kategori, NPM disamarkan | FR-01, FR-02 |
| Autentikasi | Registrasi + OTP email (kode di-hash, kedaluwarsa 10 menit, maks. 5 percobaan); Login per peran; Lupa kata sandi via OTP; akun nonaktif ditolak | FR-03 – FR-07 |
| Mahasiswa | Dashboard ringkasan dan catatan perbaikan | FR-08 |
| | Pengajuan 4 langkah: data mahasiswa, data sidang (jalur, paket, materi kompre), unggah dokumen sesuai paket/jalur, konfirmasi | FR-09 – FR-14 |
| | Detail status (Informasi, Dokumen, Riwayat); Ubah Data selama Menunggu/Perbaikan; unggah ulang hanya dokumen *Perlu Perbaikan*, lalu kembali ke antrean | FR-15 – FR-20 |
| | Maks. 2 pengajuan (Skripsi lalu Kompre); formulir ditolak saat periode ditutup, riwayat tetap terlihat | FR-35 |
| | Validasi NPM | FR-37 |
| Admin & Super Admin | Dashboard; Daftar Pengajuan (filter status/paket/kategori/tanggal, pencarian) | FR-21 – FR-23 |
| | Pemeriksaan Berkas: pratinjau PDF, status per dokumen + alasan, status pengajuan + kategori 1/2a/2b/3, catatan untuk mahasiswa atau internal, verifikasi data, lapor akun | FR-24 – FR-27 |
| | Riwayat Perubahan dan log otomatis | FR-28, FR-29 |
| | Kategori Hasil | – |
| | Laporan: tabel, ekspor Excel (CSV), cetak/PDF, unduh ZIP berkas + rekap | FR-30, FR-31 |
| Super Admin | Semua halaman admin | FR-32 |
| | Manajemen Admin (tambah/ubah/nonaktifkan/hapus) | FR-33 |
| | Pengaturan Sistem: periode buka/tutup, dokumen persyaratan, paket materi kompre | FR-34 – FR-36 |
| | Data Mahasiswa: aktif, dilaporkan, arsip. Hapus akun = arsip (soft delete), tolak laporan. | – |
| | Kelola Landing Page (bagian utama, alur, FAQ, kontak, dengan preview langsung); ubah teks notifikasi kategori; Log Aktivitas | – |

Keamanan (NFR-02):
- Kata sandi di-hash dengan bcrypt.
- Middleware peran membatasi akses di sisi server.
- Semua form dilindungi CSRF.
- Berkas disimpan di `storage/app/private` dan hanya bisa dibuka lewat controller yang memeriksa hak akses.
- Login dan OTP dibatasi jumlah percobaannya per akun + IP.

## 6. Catatan implementasi (perlu diketahui tim)
- **OTP saat pengembangan:** dengan `APP_ENV=local` dan `MAIL_MAILER=log`, email OTP tidak dikirim. Kode ditampilkan di layar (banner "Mode pengembangan") dan dicatat di `storage/logs/laravel.log`. Di produksi, isi SMTP dan kode hanya dikirim lewat email.
- **Validasi NPM (FR-37):** sistem tidak terhubung ke SIA (sesuai batasan SRS). NPM dianggap valid bila 8 digit dan berawalan kode prodi di `config/sidang.php` (saat ini `10` dan `11`). Angkatan dibaca dari digit ke-4–5. Sesuaikan kode prodi di file itu bila perlu.
- **Ekspor Excel** berupa file **CSV** (pemisah `;`, UTF-8) yang langsung terbuka di Excel. **PDF** dibuat lewat halaman cetak (Ctrl+P, lalu *Simpan sebagai PDF*).
- **Hapus akun mahasiswa** tidak menghapus data. Status akun menjadi `arsip`, akun tidak bisa login, dan data tetap ada sebagai laporan.
- **Akun admin** yang sudah punya riwayat pemeriksaan tidak bisa dihapus (jejak audit), tapi bisa dinonaktifkan.
- **Kolom `tautan_google_form`** tetap ada di tabel `periode_pendaftaran` sesuai Kamus Data, tapi tidak dipakai di antarmuka (Google Form sudah dihapus dari desain).
- **Tambahan kecil di luar kamus data:** `users.remember_token` (fitur "Ingat saya") dan `otp_codes.percobaan` (batas percobaan OTP).

## 7. Pengujian
Aplikasi diuji end-to-end di browser (Playwright) terhadap MySQL dengan **41 skenario, semuanya lulus**. Skenarionya mencakup:
- registrasi + OTP (salah/benar), login (salah/benar), lupa kata sandi
- validasi NPM dan format berkas, pengajuan skripsi reguler dan kompre transfer, batas 2 pengajuan, ubah data
- pemeriksaan admin (validasi status/kategori, pratinjau PDF, perlu perbaikan), unggah ulang, penyelesaian
- cek status publik, laporan CSV/cetak/ZIP
- lapor akun lalu hapus & arsip oleh super admin, akun arsip ditolak login
- CRUD paket materi dan dokumen, tutup/buka pendaftaran, kelola landing page, ubah teks kategori, manajemen admin (nonaktif ditolak login), log aktivitas, notifikasi
- akses lintas peran (403), tampilan mobile 390px
=======
# sidang-sarjana
>>>>>>> 2b9dd3e0b135c76375e5a22286e65824df93c351
