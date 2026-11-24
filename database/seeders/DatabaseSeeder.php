<?php

namespace Database\Seeders;

use App\Models\JenisDokumen;
use App\Models\KategoriHasil;
use App\Models\KontenLanding;
use App\Models\MateriKompre;
use App\Models\PeriodePendaftaran;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Data master awal sistem. Bukan data dummy: akun awal Super Admin & Admin, kategori hasil,
 * jenis dokumen persyaratan, paket materi kompre, periode, dan isi landing page.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $super = User::updateOrCreate(['email' => 'superadmin@sidangsarjana.id'], [
            'nama' => 'Bagian Akademik', 'password' => 'Superadmin#2026', 'role' => 'super_admin',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
        User::updateOrCreate(['email' => 'admin@sidangsarjana.id'], [
            'nama' => 'Siti Rahayu', 'password' => 'Admin#2026', 'role' => 'admin',
            'is_active' => true, 'email_verified_at' => now(),
        ]);

        $kategori = [
            ['1', 'Bisa Daftar Sidang', 'Berkas Anda telah diterima. Silakan melanjutkan pendaftaran sidang sesuai jadwal prodi.'],
            ['2a', 'Kendala Pembayaran', 'Terdapat kendala pembayaran. Mohon selesaikan pembayaran lalu hubungi bagian keuangan.'],
            ['2b', 'Kendala Berkas', 'Mohon perbaiki dokumen yang ditandai agar verifikasi dapat dilanjutkan.'],
            ['3', 'Kendala Nilai', 'Terdapat nilai yang belum lengkap. Mohon lengkapi nilai mata kuliah wajib terlebih dahulu.'],
        ];
        foreach ($kategori as [$kode, $nama, $teks]) {
            KategoriHasil::updateOrCreate(['kode' => $kode], ['nama_kategori' => $nama, 'teks_notifikasi' => $teks, 'id_user' => $super->id_user]);
        }

        $dokumen = [
            ['KRS', 'Unggah KRS aktif PTA 2026/2027 semester berjalan.', 1, 1, 0, 'PDF', 2, 1],
            ['Workshop', 'Unggah 1 sertifikat workshop.', 1, 1, 0, 'PDF', 2, 2],
            ['Kursus', 'Unggah seluruh sertifikat kursus dalam satu PDF.', 1, 1, 0, 'PDF', 5, 3],
            ['Manual Book', 'Unggah manual book dalam bentuk PDF.', 1, 0, 0, 'PDF', 10, 4],
            ['Berkas Tambahan', 'Khusus mahasiswa transfer. Gabungkan pengesahan penyetaraan PI yang sudah ditandatangani, ijazah, konversi nilai, dan transkrip D3 dalam satu PDF.', 1, 1, 1, 'PDF', 10, 5],
        ];
        foreach ($dokumen as [$nama, $ket, $ws, $wk, $tr, $fmt, $mb, $urut]) {
            JenisDokumen::updateOrCreate(['nama_dokumen' => $nama], [
                'keterangan' => $ket, 'wajib_skripsi' => $ws, 'wajib_kompre' => $wk, 'khusus_transfer' => $tr,
                'format_file' => $fmt, 'ukuran_maks_mb' => $mb, 'urutan' => $urut,
            ]);
        }

        $materi = [
            ['Paket 1', 'Disain dan Manajemen Jaringan Komputer', 'Sistem Keamanan Teknologi Informasi', 'Sistem Operasi'],
            ['Paket 2', 'Analisis dan Perancangan Sistem Informasi', 'Interaksi Manusia dan Komputer', 'Sistem Basis Data 2'],
            ['Paket 3', 'Pemrograman Berorientasi Objek', 'Struktur dan Organisasi Data', 'Testing dan Implementasi Sistem'],
        ];
        foreach ($materi as [$p, $a, $b, $c]) {
            MateriKompre::updateOrCreate(['nama_paket' => $p], ['mata_kuliah_1' => $a, 'mata_kuliah_2' => $b, 'mata_kuliah_3' => $c]);
        }

        if (! PeriodePendaftaran::exists()) {
            PeriodePendaftaran::create([
                'nama_periode' => 'Sidang Sarjana Periode I 2026/2027', 'tanggal_buka' => '2026-10-01',
                'tanggal_tutup' => '2026-12-31', 'is_dibuka' => true, 'id_user' => $super->id_user,
            ]);
        }

        if (! KontenLanding::exists()) {
            $info = [
                'hero_label' => 'LAYANAN AKADEMIK • SIDANG SARJANA',
                'hero_judul_1' => 'Pendaftaran',
                'hero_judul_2' => 'Sidang Sarjana.',
                'hero_deskripsi' => 'Ajukan berkas sidang sarjana Program Studi Sistem Informasi secara daring, pantau status pemeriksaan, dan perbaiki berkas tanpa mengulang dari awal.',
                'hero_tombol' => 'Daftar Sidang',
                'kontak_alamat' => 'Kampus D: Jl. Margonda Raya No. 100, Pondok Cina, Depok 16424',
                'kontak_email' => 'sidang@gunadarma.ac.id',
                'kontak_telepon' => '(021) 78881112',
            ];
            $i = 1;
            foreach ($info as $k => $v) {
                KontenLanding::create(['bagian' => 'informasi', 'judul' => $k, 'isi' => $v, 'urutan' => $i++, 'id_user' => $super->id_user]);
            }
            $alur = [
                ['Daftar dan masuk ke akun', 'Buat akun dengan email aktif, verifikasi kode OTP, lalu lengkapi profil.'],
                ['Isi formulir & unggah PDF', 'Isi data mahasiswa dan data sidang, lalu unggah dokumen persyaratan.'],
                ['Petugas memeriksa berkas', 'Admin prodi memeriksa kelengkapan dan kesesuaian dokumen.'],
                ['Pantau hasil & catatan pemeriksaan', 'Lihat status, kategori hasil, dan perbaiki dokumen yang ditandai.'],
            ];
            foreach ($alur as $n => [$j, $isi]) {
                KontenLanding::create(['bagian' => 'alur', 'judul' => $j, 'isi' => $isi, 'urutan' => $n + 1, 'id_user' => $super->id_user]);
            }
            $faq = [
                ['Apa saja berkas yang perlu diunggah?', 'Unggah KRS aktif semester berjalan, unggah 1 sertifikat workshop, dan unggah seluruh sertifikat kursus dalam satu PDF.'],
                ['Apa persyaratan mahasiswa transfer?', 'Khusus mahasiswa transfer mengisi nama dosen pembimbing Penulisan Ilmiah beserta gelar.'],
                ['Apa pilihan paket sidang?', "Paket yang tersedia adalah Skripsi dan Komprehensif.\nUntuk Komprehensif, mahasiswa wajib memilih materi sidang yang disediakan operator."],
                ['Apa arti status pengajuan?', "Menunggu: dalam antrean atau terdapat kendala nilai yang belum tersedia.\nDiproses: berkas sedang diperiksa.\nPerbaikan: mahasiswa perlu melengkapi atau memperbaiki berkas.\nSelesai: berkas telah diterima."],
                ['Bagaimana jika ada berkas yang salah?', 'Buka Status Pengajuan, pilih Dokumen, lalu periksa catatan petugas dan unggah ulang berkas yang ditandai Perlu Perbaikan.'],
                ['Format dan ukuran berkas apa yang diterima?', 'Gabungkan sertifikat kursus dan berkas tambahan masing-masing dalam satu PDF. Ukuran maksimal tertera pada formulir.'],
            ];
            foreach ($faq as $n => [$j, $isi]) {
                KontenLanding::create(['bagian' => 'faq', 'judul' => $j, 'isi' => $isi, 'urutan' => $n + 1, 'id_user' => $super->id_user]);
            }
        }
    }
}
