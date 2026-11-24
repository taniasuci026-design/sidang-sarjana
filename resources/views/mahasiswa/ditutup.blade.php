@extends('layouts.portal')
@section('title', 'Pendaftaran Ditutup')
@section('content')
<div class="label-atas">Pendaftaran Sidang Sarjana</div>
<h1 class="judul-halaman">Pengajuan Sidang Sarjana</h1>
<div class="kartu text-center py-5 mt-3">
    <div class="ikon-bahaya" style="background:#F1F5F9;color:#475569"><i class="bi bi-lock"></i></div>
    <div class="kartu-judul mb-1">Pendaftaran sidang sedang ditutup</div>
    <p class="text-2 mb-1">@if ($periode){{ $periode->nama_periode }} · {{ $periode->tanggal_buka->translatedFormat('d M Y') }} – {{ $periode->tanggal_tutup->translatedFormat('d M Y') }}@endif</p>
    <p class="text-3 small">Pengajuan baru belum dapat dikirim. Anda tetap dapat melihat status pengajuan sebelumnya.</p>
    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="btn btn-primary mt-2">Lihat Status Pengajuan</a>
</div>
@endsection
