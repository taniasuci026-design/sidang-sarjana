@extends('layouts.base')
@php
    $u = auth()->user();
    $menu = function ($route, $ikon, $label, $pola = null) {
        $aktif = request()->routeIs($pola ?? $route) ? 'aktif' : '';
        return '<a href="'.route($route).'" class="menu '.$aktif.'" title="'.e($label).'"><i class="bi '.$ikon.'"></i><span class="teks-menu">'.e($label).'</span></a>';
    };
    $notifTerbaru = $u->notifikasi()->latest('id_notifikasi')->limit(5)->get();
    $jumlahBaru = $u->jumlahNotifikasiBaru();
@endphp
@section('body')
<div class="portal">
    <aside class="sidebar" aria-label="Navigasi utama">
        <button class="tombol-ciut" type="button" data-ciut-sidebar aria-label="Ciutkan menu"><i class="bi bi-chevron-double-left"></i></button>
        <a href="{{ route($u->dashboardRoute()) }}" class="brand mt-1">
            <span class="brand-logo"><i class="bi bi-mortarboard-fill"></i></span>
            <span class="brand-teks"><span class="d-block brand-nama">Sidang Sarjana</span><span class="d-block brand-sub">UNIVERSITAS GUNADARMA</span></span>
        </a>
        @if ($u->isStaf())<span class="badge-peran">{{ strtoupper($u->roleLabel()) }}</span>@endif
        <nav class="sidebar-nav mt-2">
            @if ($u->isMahasiswa())
                <div class="grup">Portal Mahasiswa</div>
                {!! $menu('mahasiswa.dashboard', 'bi-grid-1x2', 'Dashboard') !!}
                {!! $menu('mahasiswa.pengajuan.create', 'bi-file-earmark-text', 'Pengajuan Sidang') !!}
                {!! $menu('mahasiswa.pengajuan.index', 'bi-check2-circle', 'Status Pengajuan', 'mahasiswa.pengajuan.index|mahasiswa.pengajuan.show|mahasiswa.pengajuan.edit') !!}
            @else
                @if ($u->isSuperAdmin())<div class="grup">Umum</div>@endif
                {!! $menu('admin.dashboard', 'bi-grid-1x2', $u->isSuperAdmin() ? 'Beranda' : 'Dashboard') !!}
                @if ($u->isSuperAdmin())<div class="grup">Operasional (sama dengan admin)</div>@endif
                {!! $menu('admin.pengajuan.index', 'bi-files', 'Daftar Pengajuan', 'admin.pengajuan.*') !!}
                {!! $menu('admin.kategori.index', 'bi-tags', 'Kategori Hasil') !!}
                {!! $menu('admin.laporan.index', 'bi-bar-chart-line', 'Laporan', 'admin.laporan.*') !!}
                @if ($u->isSuperAdmin())
                    <div class="grup">Khusus Superadmin</div>
                    {!! $menu('super.admin.index', 'bi-shield-check', 'Manajemen Admin', 'super.admin.*') !!}
                    {!! $menu('super.mahasiswa.index', 'bi-people', 'Data Mahasiswa', 'super.mahasiswa.*') !!}
                    {!! $menu('super.landing.index', 'bi-layout-text-window', 'Kelola Landing Page', 'super.landing.*') !!}
                    {!! $menu('super.pengaturan.index', 'bi-gear', 'Pengaturan Sistem', 'super.pengaturan.*') !!}
                    {!! $menu('super.log.index', 'bi-clock-history', 'Log Aktivitas', 'super.log.*') !!}
                @endif
            @endif
        </nav>
        <div class="bawah">
            {!! $menu('profil', 'bi-person', 'Profil') !!}
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="menu w-100 border-0 bg-transparent" title="Keluar"><i class="bi bi-box-arrow-right"></i><span class="teks-menu">Keluar</span></button>
            </form>
        </div>
    </aside>
    <div class="utama">
        <header class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-light border d-lg-none" type="button" data-buka-sidebar aria-label="Buka menu"><i class="bi bi-list"></i></button>
                <span class="judul-sistem d-none d-sm-inline">Sistem Pengajuan Sidang Sarjana</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dropdown">
                    <button class="lonceng" data-bs-toggle="dropdown" aria-label="Notifikasi"><i class="bi bi-bell"></i>@if($jumlahBaru)<span class="jumlah">{{ $jumlahBaru > 99 ? '99+' : $jumlahBaru }}</span>@endif</button>
                    <div class="dropdown-menu dropdown-menu-end dropdown-notif">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"><b class="text-navy">Notifikasi</b>
                            @if($jumlahBaru)<form method="POST" action="{{ route('notifikasi.baca-semua') }}">@csrf<button class="btn btn-link btn-sm p-0">Tandai dibaca</button></form>@endif</div>
                        @forelse ($notifTerbaru as $n)
                            <a href="{{ route('notifikasi.buka', $n) }}" class="notif-item {{ $n->dibaca_at ? '' : 'baru' }}">
                                <div class="fw-semibold small text-primary">{{ $n->judul }}</div>
                                <div class="small text-2">{{ \Illuminate\Support\Str::limit($n->pesan, 90) }}</div>
                                <div class="text-3" style="font-size:11px">{{ $n->created_at?->diffForHumans() }}</div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-3 small">Belum ada notifikasi.</div>
                        @endforelse
                        <a href="{{ route('notifikasi') }}" class="d-block text-center py-2 small fw-semibold">Lihat semua notifikasi</a>
                    </div>
                </div>
                <a href="{{ route('profil') }}" class="d-flex align-items-center gap-2 border-start ps-3 text-reset">
                    <span class="avatar">{{ $u->inisial() }}</span>
                    <span class="d-none d-md-block lh-sm"><span class="d-block fw-bold text-navy small">{{ $u->nama }}</span><span class="d-block text-primary" style="font-size:11px">{{ $u->roleLabel() }}</span></span>
                </a>
            </div>
        </header>
        <main class="konten">
            @if ($errors->any() && ! View::hasSection('tanpa-galat-umum'))
                <div class="info-banner merah mb-3"><i class="bi bi-exclamation-circle"></i><div>
                    <b>Periksa kembali isian Anda.</b>
                    <ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div></div>
            @endif
            @yield('content')
        </main>
        <footer class="kaki"><b>SIDANG SARJANA</b> · Universitas Gunadarma · © {{ date('Y') }}</footer>
    </div>
</div>
@endsection
