<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Struktur basis data mengikuti Kamus Data SDD (Tabel 3 - Tabel 16).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $t) {
            $t->increments('id_user');
            $t->string('nama', 100);
            $t->string('email', 100)->unique();
            $t->string('no_whatsapp', 15)->nullable()->unique();
            $t->string('password', 255);
            $t->string('google_id', 50)->nullable();
            $t->string('foto_profil', 255)->nullable();
            $t->enum('role', ['mahasiswa', 'admin', 'super_admin']);
            $t->boolean('is_active')->default(true);
            $t->dateTime('email_verified_at')->nullable();
            $t->dateTime('last_login_at')->nullable();
            $t->rememberToken();
            $t->dateTime('created_at')->nullable();
            $t->dateTime('updated_at')->nullable();
        });

        Schema::create('otp_codes', function (Blueprint $t) {
            $t->increments('id_otp');
            $t->unsignedInteger('id_user');
            $t->enum('tujuan', ['registrasi', 'reset_password']);
            $t->string('kode_otp', 255);
            $t->dateTime('kedaluwarsa_at');
            $t->dateTime('digunakan_at')->nullable();
            $t->unsignedTinyInteger('percobaan')->default(0);
            $t->dateTime('created_at')->nullable();
            $t->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
        });

        Schema::create('mahasiswa', function (Blueprint $t) {
            $t->increments('id_mahasiswa');
            $t->unsignedInteger('id_user')->unique();
            $t->string('npm', 10)->unique();
            $t->string('nama_lengkap', 100);
            $t->string('fakultas', 100)->nullable();
            $t->string('program_studi', 100)->nullable();
            $t->unsignedSmallInteger('angkatan')->nullable();
            $t->enum('status_akun', ['aktif', 'dilaporkan', 'arsip'])->default('aktif');
            $t->dateTime('diarsipkan_at')->nullable();
            $t->dateTime('created_at')->nullable();
            $t->dateTime('updated_at')->nullable();
            $t->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
        });

        Schema::create('periode_pendaftaran', function (Blueprint $t) {
            $t->increments('id_periode');
            $t->string('nama_periode', 100);
            $t->date('tanggal_buka');
            $t->date('tanggal_tutup');
            $t->boolean('is_dibuka')->default(false);
            $t->string('tautan_google_form', 255)->nullable();
            $t->unsignedInteger('id_user')->nullable();
            $t->dateTime('created_at')->nullable();
            $t->dateTime('updated_at')->nullable();
            $t->foreign('id_user')->references('id_user')->on('users')->nullOnDelete();
        });

        Schema::create('materi_kompre', function (Blueprint $t) {
            $t->increments('id_materi');
            $t->string('nama_paket', 50);
            $t->string('mata_kuliah_1', 150);
            $t->string('mata_kuliah_2', 150);
            $t->string('mata_kuliah_3', 150);
            $t->dateTime('deleted_at')->nullable();
            $t->dateTime('created_at')->nullable();
            $t->dateTime('updated_at')->nullable();
        });

        Schema::create('kategori_hasil', function (Blueprint $t) {
            $t->increments('id_kategori');
            $t->string('kode', 3)->unique();
            $t->string('nama_kategori', 50);
            $t->text('teks_notifikasi');
            $t->unsignedInteger('id_user')->nullable();
            $t->dateTime('updated_at')->nullable();
            $t->foreign('id_user')->references('id_user')->on('users')->nullOnDelete();
        });

        Schema::create('pengajuan', function (Blueprint $t) {
            $t->increments('id_pengajuan');
            $t->string('no_pengajuan', 20)->unique();
            $t->unsignedInteger('id_mahasiswa');
            $t->unsignedInteger('id_periode');
            $t->enum('jalur', ['reguler', 'transfer']);
            $t->enum('paket_sidang', ['skripsi', 'kompre']);
            $t->unsignedInteger('id_materi')->nullable();
            $t->string('dosen_pembimbing_pi', 100)->nullable();
            $t->enum('status_pengajuan', ['menunggu', 'diproses', 'perbaikan', 'selesai'])->default('menunggu');
            $t->enum('status_verifikasi', ['belum', 'terverifikasi'])->default('belum');
            $t->unsignedInteger('id_kategori')->nullable();
            $t->dateTime('diajukan_at')->nullable();
            $t->unsignedInteger('id_admin_terakhir')->nullable();
            $t->dateTime('created_at')->nullable();
            $t->dateTime('updated_at')->nullable();
            $t->foreign('id_mahasiswa')->references('id_mahasiswa')->on('mahasiswa')->cascadeOnDelete();
            $t->foreign('id_periode')->references('id_periode')->on('periode_pendaftaran');
            $t->foreign('id_materi')->references('id_materi')->on('materi_kompre');
            $t->foreign('id_kategori')->references('id_kategori')->on('kategori_hasil')->nullOnDelete();
            $t->foreign('id_admin_terakhir')->references('id_user')->on('users')->nullOnDelete();
        });

        Schema::create('jenis_dokumen', function (Blueprint $t) {
            $t->increments('id_jenis');
            $t->string('nama_dokumen', 100);
            $t->string('keterangan', 255)->nullable();
            $t->boolean('wajib_skripsi')->default(true);
            $t->boolean('wajib_kompre')->default(true);
            $t->boolean('khusus_transfer')->default(false);
            $t->string('format_file', 30)->default('PDF');
            $t->unsignedTinyInteger('ukuran_maks_mb')->default(2);
            $t->unsignedTinyInteger('urutan')->default(1);
        });

        Schema::create('dokumen_pengajuan', function (Blueprint $t) {
            $t->increments('id_dokumen');
            $t->unsignedInteger('id_pengajuan');
            $t->unsignedInteger('id_jenis');
            $t->string('nama_file', 255);
            $t->string('path_file', 255);
            $t->unsignedInteger('ukuran_kb');
            $t->enum('status_dokumen', ['menunggu', 'lengkap', 'perlu_perbaikan'])->default('menunggu');
            $t->text('catatan_pemeriksaan')->nullable();
            $t->dateTime('uploaded_at')->nullable();
            $t->unique(['id_pengajuan', 'id_jenis']);
            $t->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan')->cascadeOnDelete();
            $t->foreign('id_jenis')->references('id_jenis')->on('jenis_dokumen');
        });

        Schema::create('catatan_pengajuan', function (Blueprint $t) {
            $t->increments('id_catatan');
            $t->unsignedInteger('id_pengajuan');
            $t->unsignedInteger('id_user');
            $t->text('isi_catatan');
            $t->boolean('terlihat_mahasiswa')->default(true);
            $t->dateTime('created_at')->nullable();
            $t->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan')->cascadeOnDelete();
            $t->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
        });

        Schema::create('riwayat_aktivitas', function (Blueprint $t) {
            $t->increments('id_riwayat');
            $t->unsignedInteger('id_user')->nullable();
            $t->unsignedInteger('id_pengajuan')->nullable();
            $t->unsignedInteger('id_mahasiswa')->nullable();
            $t->string('aksi', 100);
            $t->string('status_lama', 20)->nullable();
            $t->string('status_baru', 20)->nullable();
            $t->text('keterangan')->nullable();
            $t->dateTime('created_at')->nullable();
            $t->foreign('id_user')->references('id_user')->on('users')->nullOnDelete();
            $t->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan')->nullOnDelete();
            $t->foreign('id_mahasiswa')->references('id_mahasiswa')->on('mahasiswa')->nullOnDelete();
        });

        Schema::create('notifikasi', function (Blueprint $t) {
            $t->increments('id_notifikasi');
            $t->unsignedInteger('id_user');
            $t->unsignedInteger('id_pengajuan')->nullable();
            $t->enum('jenis', ['pengajuan', 'akun', 'periode']);
            $t->string('judul', 150);
            $t->text('pesan')->nullable();
            $t->dateTime('dibaca_at')->nullable();
            $t->dateTime('created_at')->nullable();
            $t->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $t->foreign('id_pengajuan')->references('id_pengajuan')->on('pengajuan')->nullOnDelete();
        });

        Schema::create('permintaan_hapus_akun', function (Blueprint $t) {
            $t->increments('id_permintaan');
            $t->unsignedInteger('id_mahasiswa');
            $t->unsignedInteger('id_pengaju');
            $t->text('alasan');
            $t->enum('status_permintaan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
            $t->unsignedInteger('id_peninjau')->nullable();
            $t->text('catatan_peninjau')->nullable();
            $t->dateTime('diajukan_at')->nullable();
            $t->dateTime('ditinjau_at')->nullable();
            $t->foreign('id_mahasiswa')->references('id_mahasiswa')->on('mahasiswa')->cascadeOnDelete();
            $t->foreign('id_pengaju')->references('id_user')->on('users')->cascadeOnDelete();
            $t->foreign('id_peninjau')->references('id_user')->on('users')->nullOnDelete();
        });

        Schema::create('konten_landing', function (Blueprint $t) {
            $t->increments('id_konten');
            $t->enum('bagian', ['alur', 'faq', 'informasi']);
            $t->string('judul', 150);
            $t->text('isi');
            $t->unsignedTinyInteger('urutan')->default(1);
            $t->boolean('is_tampil')->default(true);
            $t->unsignedInteger('id_user')->nullable();
            $t->dateTime('updated_at')->nullable();
            $t->foreign('id_user')->references('id_user')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['konten_landing', 'permintaan_hapus_akun', 'notifikasi', 'riwayat_aktivitas', 'catatan_pengajuan',
            'dokumen_pengajuan', 'jenis_dokumen', 'pengajuan', 'kategori_hasil', 'materi_kompre',
            'periode_pendaftaran', 'mahasiswa', 'otp_codes', 'users'] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
