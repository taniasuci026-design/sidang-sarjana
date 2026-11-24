@extends('layouts.auth')
@section('title', 'Lupa Kata Sandi')
@section('form')
    <a href="{{ route('login') }}" class="tautan-kembali d-inline-block mb-3">← Kembali ke Login</a>
    <div class="aksen"></div>
    <h2>Cari akun Anda</h2>
    <p class="text-3 mb-4">Masukkan email yang terdaftar untuk menerima kode verifikasi.</p>
    @include('partials.galat-form')
    <form method="POST" action="{{ route('lupa') }}">
        @csrf
        <label class="form-label">Email</label>
        <div class="input-ikon mb-4"><i class="bi bi-envelope"></i><input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Masukkan email terdaftar" required autofocus></div>
        <button class="btn btn-primary w-100 py-2">Lanjut</button>
    </form>
@endsection
