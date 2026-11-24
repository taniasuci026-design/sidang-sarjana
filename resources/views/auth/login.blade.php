@extends('layouts.auth')
@section('title', 'Masuk')
@section('form')
    <div class="aksen"></div>
    <h2>Login</h2>
    <p class="text-3 mb-4">Silakan masuk dengan akun yang telah disediakan</p>
    @if (session('akun_nonaktif'))
        <div class="info-banner merah mb-3"><i class="bi bi-slash-circle"></i><div><b>Akun dinonaktifkan</b><br>Akun ini tidak dapat digunakan untuk masuk. Hubungi bagian akademik prodi.</div></div>
    @else
        @include('partials.galat-form')
    @endif
    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <div class="input-ikon"><i class="bi bi-envelope"></i><input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Masukkan email" required autofocus></div>
        </div>
        <div class="mb-2">
            <label class="form-label" for="password">Kata sandi</label>
            <div class="input-ikon"><i class="bi bi-lock"></i><input id="password" type="password" name="password" class="form-control" placeholder="Masukkan kata sandi" required>
                <button type="button" class="lihat-sandi" aria-label="Tampilkan kata sandi"><i class="bi bi-eye"></i></button></div>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <label class="small text-2"><input type="checkbox" name="ingat" class="form-check-input me-1">Ingat saya</label>
            <a href="{{ route('lupa') }}" class="small fw-semibold text-oranye">Lupa kata sandi?</a>
        </div>
        <button class="btn btn-primary w-100 py-2">Login</button>
    </form>
    <p class="text-center small text-3 mt-4 mb-0">Mahasiswa belum punya akun? <a href="{{ route('daftar') }}" class="fw-semibold">Daftar di sini</a></p>
    <p class="text-center small mt-2"><a href="{{ route('landing') }}" class="text-3">← Kembali ke beranda</a></p>
@endsection
