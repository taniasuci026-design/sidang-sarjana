<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\Mahasiswa;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\PublikController;
use App\Http\Controllers\Super;
use Illuminate\Support\Facades\Route;

// ===== Publik (UI-01, UI-02) =====
Route::get('/', [PublikController::class, 'landing'])->name('landing');
Route::get('/cek-status', [PublikController::class, 'cekStatus'])->name('cek-status');

// ===== Autentikasi (UI-03, UI-04) =====
Route::middleware('guest')->group(function () {
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar'])->middleware('throttle:daftar');
    Route::get('/verifikasi-otp', [AuthController::class, 'formOtp'])->name('otp.form');
    Route::post('/verifikasi-otp', [AuthController::class, 'verifikasiOtp'])->middleware('throttle:otp')->name('otp.verifikasi');
    Route::post('/verifikasi-otp/kirim-ulang', [AuthController::class, 'kirimUlangOtp'])->middleware('throttle:kirim-otp')->name('otp.kirim-ulang');
    Route::get('/masuk', [AuthController::class, 'formLogin'])->name('login');
    Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/lupa-kata-sandi', [AuthController::class, 'formLupa'])->name('lupa');
    Route::post('/lupa-kata-sandi', [AuthController::class, 'kirimLupa'])->middleware('throttle:kirim-otp');
    Route::get('/lupa-kata-sandi/kode', [AuthController::class, 'formLupaKode'])->name('lupa.kode');
    Route::post('/lupa-kata-sandi/kode', [AuthController::class, 'verifikasiLupaKode'])->middleware('throttle:otp');
    Route::get('/lupa-kata-sandi/baru', [AuthController::class, 'formSandiBaru'])->name('lupa.baru');
    Route::post('/lupa-kata-sandi/baru', [AuthController::class, 'simpanSandiBaru']);
});
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ===== Bersama untuk pengguna login =====
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::get('/notifikasi/{notifikasi}', [NotifikasiController::class, 'buka'])->name('notifikasi.buka');
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::put('/profil/kata-sandi', [ProfilController::class, 'kataSandi'])->name('profil.sandi');
    Route::get('/berkas/{dokumen}', [BerkasController::class, 'lihat'])->name('berkas.lihat');
});

// ===== Mahasiswa (UI-05 - UI-10) =====
Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/', [Mahasiswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengajuan/baru', [Mahasiswa\PengajuanController::class, 'create'])->name('pengajuan.create');
    Route::post('/pengajuan', [Mahasiswa\PengajuanController::class, 'store'])->name('pengajuan.store');
    Route::get('/pengajuan', [Mahasiswa\PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{pengajuan}', [Mahasiswa\PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::get('/pengajuan/{pengajuan}/ubah', [Mahasiswa\PengajuanController::class, 'edit'])->name('pengajuan.edit');
    Route::put('/pengajuan/{pengajuan}', [Mahasiswa\PengajuanController::class, 'update'])->name('pengajuan.update');
    Route::post('/pengajuan/{pengajuan}/perbaikan', [Mahasiswa\PengajuanController::class, 'perbaikan'])->name('pengajuan.perbaikan');
});

// ===== Admin & Super Admin (UI-11 - UI-14) =====
Route::middleware(['auth', 'role:admin,super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pengajuan', [Admin\PengajuanController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/{pengajuan}', [Admin\PengajuanController::class, 'show'])->name('pengajuan.show');
    Route::put('/pengajuan/{pengajuan}/periksa', [Admin\PengajuanController::class, 'periksa'])->name('pengajuan.periksa');
    Route::post('/pengajuan/{pengajuan}/verifikasi', [Admin\PengajuanController::class, 'verifikasi'])->name('pengajuan.verifikasi');
    Route::post('/pengajuan/{pengajuan}/laporkan', [Admin\PengajuanController::class, 'laporkan'])->name('pengajuan.laporkan');
    Route::get('/pengajuan/{pengajuan}/riwayat', [Admin\PengajuanController::class, 'riwayat'])->name('pengajuan.riwayat');
    Route::get('/pengajuan/{pengajuan}/zip', [Admin\LaporanController::class, 'zipSatu'])->name('pengajuan.zip');
    Route::get('/kategori-hasil', [Admin\KategoriController::class, 'index'])->name('kategori.index');
    Route::get('/laporan', [Admin\LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/excel', [Admin\LaporanController::class, 'excel'])->name('laporan.excel');
    Route::get('/laporan/cetak', [Admin\LaporanController::class, 'cetak'])->name('laporan.cetak');
    Route::get('/laporan/zip', [Admin\LaporanController::class, 'zip'])->name('laporan.zip');
});

// ===== Khusus Super Admin (UI-15 - UI-17) =====
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('super.')->group(function () {
    Route::put('/kategori-hasil/{kategori}', [Admin\KategoriController::class, 'update'])->name('kategori.update');

    Route::get('/admin', [Super\AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin', [Super\AdminController::class, 'store'])->name('admin.store');
    Route::put('/admin/{user}', [Super\AdminController::class, 'update'])->name('admin.update');
    Route::post('/admin/{user}/status', [Super\AdminController::class, 'toggle'])->name('admin.toggle');
    Route::delete('/admin/{user}', [Super\AdminController::class, 'destroy'])->name('admin.destroy');

    Route::get('/mahasiswa', [Super\MahasiswaController::class, 'index'])->name('mahasiswa.index');
    Route::get('/mahasiswa/{mahasiswa}', [Super\MahasiswaController::class, 'show'])->name('mahasiswa.show');
    Route::post('/mahasiswa/{mahasiswa}/hapus', [Super\MahasiswaController::class, 'hapus'])->name('mahasiswa.hapus');
    Route::post('/mahasiswa/{mahasiswa}/tolak', [Super\MahasiswaController::class, 'tolak'])->name('mahasiswa.tolak');
    Route::get('/mahasiswa-arsip/ekspor', [Super\MahasiswaController::class, 'eksporArsip'])->name('mahasiswa.ekspor-arsip');

    Route::get('/pengaturan', [Super\PengaturanController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan/periode', [Super\PengaturanController::class, 'periode'])->name('pengaturan.periode');
    Route::post('/pengaturan/periode/status', [Super\PengaturanController::class, 'statusPeriode'])->name('pengaturan.status');
    Route::post('/pengaturan/dokumen', [Super\PengaturanController::class, 'dokumenStore'])->name('pengaturan.dokumen.store');
    Route::put('/pengaturan/dokumen/{jenis}', [Super\PengaturanController::class, 'dokumenUpdate'])->name('pengaturan.dokumen.update');
    Route::delete('/pengaturan/dokumen/{jenis}', [Super\PengaturanController::class, 'dokumenDestroy'])->name('pengaturan.dokumen.destroy');
    Route::post('/pengaturan/materi', [Super\PengaturanController::class, 'materiStore'])->name('pengaturan.materi.store');
    Route::put('/pengaturan/materi/{materi}', [Super\PengaturanController::class, 'materiUpdate'])->name('pengaturan.materi.update');
    Route::delete('/pengaturan/materi/{materi}', [Super\PengaturanController::class, 'materiDestroy'])->name('pengaturan.materi.destroy');

    Route::get('/landing', [Super\LandingController::class, 'index'])->name('landing.index');
    Route::put('/landing/informasi', [Super\LandingController::class, 'informasi'])->name('landing.informasi');
    Route::post('/landing/konten', [Super\LandingController::class, 'store'])->name('landing.store');
    Route::put('/landing/konten/{konten}', [Super\LandingController::class, 'update'])->name('landing.update');
    Route::delete('/landing/konten/{konten}', [Super\LandingController::class, 'destroy'])->name('landing.destroy');

    Route::get('/log', [Super\LogController::class, 'index'])->name('log.index');
});
