@extends('layouts.base')
@section('body')
<div class="auth">
    <div class="auth-kiri">
        <a href="{{ route('landing') }}" class="d-flex align-items-center gap-2 text-white">
            <span class="avatar" style="background:#fff"><i class="bi bi-mortarboard-fill text-navy fs-5"></i></span>
            <span><span class="d-block fw-bold">Sidang Sarjana</span><span class="d-block" style="font-size:10px;letter-spacing:.06em;color:#BFD0F5">UNIVERSITAS GUNADARMA</span></span>
        </a>
        <div class="my-auto" style="max-width:480px;position:relative;z-index:1">
            <div class="aksen"></div>
            <h1>@yield('kiri-judul', 'Proses Pengecekan Sidang Sarjana')</h1>
            <p class="mt-3" style="color:#E2E8F0;font-size:17px">@yield('kiri-sub', 'Sistem Informasi Pendaftaran Sidang · Program Studi Sistem Informasi')</p>
        </div>
    </div>
    <div class="auth-kanan">
        <div class="auth-form">
            @yield('form')
        </div>
    </div>
</div>
@endsection
