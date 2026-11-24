@extends('layouts.auth')
@section('title', 'Daftar Akun')
@section('kiri-judul', 'Daftar Akun Mahasiswa')
@section('kiri-sub', 'Gunakan email aktif. Kode verifikasi (OTP) akan dikirim ke email Anda.')
@section('form')
    <div class="aksen"></div>
    <h2>Daftar Akun</h2>
    <p class="text-3 mb-4">Buat akun untuk mengajukan sidang sarjana.</p>
    @include('partials.galat-form')
    <form method="POST" action="{{ route('daftar') }}" novalidate>
        @csrf
        <div class="mb-3"><label class="form-label">Nama lengkap <span class="wajib">*</span></label>
            <input name="nama" value="{{ old('nama') }}" class="form-control @error('nama') is-invalid @enderror" placeholder="Nama sesuai data kampus" required></div>
        <div class="mb-3"><label class="form-label">Email aktif <span class="wajib">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="nama@email.com" required></div>
        <div class="mb-3"><label class="form-label">Nomor WhatsApp <span class="wajib">*</span></label>
            <input name="no_whatsapp" value="{{ old('no_whatsapp') }}" inputmode="numeric" class="form-control @error('no_whatsapp') is-invalid @enderror" placeholder="08xxxxxxxxxx" required></div>
        <div class="mb-3"><label class="form-label">Kata sandi <span class="wajib">*</span></label>
            <div class="input-ikon"><i class="bi bi-lock"></i><input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter, huruf & angka" required><button type="button" class="lihat-sandi"><i class="bi bi-eye"></i></button></div></div>
        <div class="mb-4"><label class="form-label">Ulangi kata sandi <span class="wajib">*</span></label>
            <div class="input-ikon"><i class="bi bi-lock"></i><input type="password" name="password_confirmation" class="form-control" required><button type="button" class="lihat-sandi"><i class="bi bi-eye"></i></button></div></div>
        <button class="btn btn-primary w-100 py-2">Daftar & Kirim Kode</button>
    </form>
    <p class="text-center small text-3 mt-4">Sudah punya akun? <a href="{{ route('login') }}" class="fw-semibold">Masuk</a></p>
@endsection
