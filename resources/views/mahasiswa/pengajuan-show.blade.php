@extends('layouts.portal')
@section('title', $p->no_pengajuan)
@section('content')
@php
    [$judulPesan, $isiPesan] = $p->pesanUntukMahasiswa();
    $banner = ['menunggu' => 'kuning', 'diproses' => 'biru', 'perbaikan' => 'merah', 'selesai' => 'hijau'][$p->status_pengajuan];
    $dok = $p->dokumen->sortBy('jenis.urutan');
    $perlu = $dok->where('status_dokumen', 'perlu_perbaikan');
    $statDok = $dok->contains('status_dokumen', 'perlu_perbaikan') ? ['danger', 'Perlu perbaikan'] : ($dok->every(fn($d) => $d->status_dokumen === 'lengkap') ? ['success', 'Lengkap'] : ['primary', 'Sedang diperiksa']);
    $statVer = $p->status_verifikasi === 'terverifikasi' ? ['success', 'Terverifikasi'] : ['primary', 'Sedang diperiksa'];
    $bisaUbah = in_array($p->status_pengajuan, ['menunggu', 'perbaikan'], true);
@endphp
<a href="{{ route('mahasiswa.pengajuan.index') }}" class="tautan-kembali"><i class="bi bi-arrow-left"></i> Kembali ke daftar</a>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-2">
    <div><div class="label-atas">Detail Pengajuan</div><h1 class="judul-halaman">{{ $p->no_pengajuan }}</h1><div class="sub-halaman">Diajukan pada {{ $p->diajukan_at?->translatedFormat('d M Y, H:i') }} WIB</div></div>
    <div class="d-flex gap-2 align-items-center">
        <x-pengajuan-status :p="$p" />
        @if ($bisaUbah)<a href="{{ route('mahasiswa.pengajuan.edit', $p) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>Ubah Data</a>@endif
    </div>
</div>

<ul class="nav tab-garis my-3">
    @foreach (['informasi' => 'Informasi', 'dokumen' => 'Dokumen', 'riwayat' => 'Riwayat'] as $k => $l)
        <li class="nav-item"><a class="nav-link {{ $tab === $k ? 'active' : '' }}" href="{{ route('mahasiswa.pengajuan.show', [$p, 'tab' => $k]) }}">{{ $l }}@if($k==='dokumen' && $perlu->count()) <span class="badge rounded-pill text-bg-danger">{{ $perlu->count() }}</span>@endif</a></li>
    @endforeach
</ul>

@if ($tab === 'informasi')
    <div class="info-banner {{ $banner }} mb-3"><i class="bi bi-info-circle-fill"></i><div><b>{{ $judulPesan }}</b>@if($isiPesan) · {{ $isiPesan }}@endif</div></div>
    <div class="kartu mb-3" style="background:#F5F8FF;border-color:#C7D7FB">
        <div class="d-flex align-items-center gap-2 mb-2"><span class="avatar avatar-sm" style="background:var(--navy);color:#fff">AD</span><div><div class="fw-bold text-navy">Catatan dari Admin</div><div class="small text-3">{{ $p->catatan->count() ? $p->catatan->count() . ' catatan' : 'Belum ada catatan' }}</div></div></div>
        @forelse ($p->catatan as $c)
            <div class="border-top pt-2 mt-2"><div class="small">{{ $c->isi_catatan }}</div><div class="text-3" style="font-size:11px">{{ $c->penulis?->nama }} · {{ $c->created_at?->translatedFormat('d M Y, H:i') }}</div></div>
        @empty
            <div class="small text-2">Belum ada catatan dari admin. Catatan akan muncul di sini setelah pengajuan Anda diperiksa.</div>
        @endforelse
    </div>
    <div class="kartu mb-3">
        <dl class="kv mb-0">
            <dt>Nama Mahasiswa</dt><dd>{{ $p->mahasiswa->nama_lengkap }}</dd>
            <dt>NPM</dt><dd>{{ $p->mahasiswa->npm }}</dd>
            <dt>Email</dt><dd>{{ $p->mahasiswa->user->email }}</dd>
            <dt>WhatsApp</dt><dd>{{ $p->mahasiswa->user->no_whatsapp }}</dd>
            <dt>Jalur</dt><dd>{{ $p->jalurLabel() }}</dd>
            <dt>Paket Sidang</dt><dd>{{ $p->paketLabel() }}</dd>
            <dt>Dosen Pembimbing PI</dt><dd>{{ $p->dosen_pembimbing_pi ?: '-' }}</dd>
            @if ($p->materi)<dt>Materi Kompre</dt><dd>{{ $p->materi->nama_paket }}: {{ $p->materi->teks() }}</dd>@endif
            <dt>Kategori Hasil</dt><dd><x-kategori :k="$p->kategori" /></dd>
        </dl>
    </div>
    <div class="kartu">
        <div class="kartu-judul mb-3">Status Pemeriksaan</div>
        <table class="tabel"><thead><tr><th>Bagian Pemeriksaan</th><th>Status</th></tr></thead><tbody>
            <tr><td class="text-oranye fw-semibold">Data Mahasiswa</td><td><x-status :warna="$statVer[0]">{{ $statVer[1] }}</x-status></td></tr>
            <tr><td class="text-oranye fw-semibold">Data Sidang</td><td><x-status warna="success">Lengkap</x-status></td></tr>
            <tr><td class="text-oranye fw-semibold">Dokumen Persyaratan</td><td><x-status :warna="$statDok[0]">{{ $statDok[1] }}</x-status></td></tr>
            <tr><td class="text-oranye fw-semibold">Verifikasi Operator</td><td>@if($p->status_pengajuan === 'selesai')<x-status warna="success">Selesai</x-status>@else<x-pengajuan-status :p="$p" />@endif</td></tr>
        </tbody></table>
        <a href="{{ route('mahasiswa.pengajuan.show', [$p, 'tab' => 'dokumen']) }}" class="d-inline-block mt-3 fw-semibold small">Lihat Detail Dokumen <i class="bi bi-arrow-up-right"></i></a>
    </div>
@elseif ($tab === 'dokumen')
    @if ($p->status_pengajuan === 'perbaikan')
        <div class="info-banner merah mb-3"><i class="bi bi-exclamation-triangle"></i><div><b>Perlu perbaikan.</b> Unggah ulang hanya dokumen bertanda <b>Perlu Perbaikan</b>, lalu klik <b>Kirim Perbaikan</b>. Dokumen lain tidak berubah.</div></div>
    @endif
    <form method="POST" action="{{ route('mahasiswa.pengajuan.perbaikan', $p) }}" enctype="multipart/form-data">
        @csrf
        <div class="d-grid gap-2">
            @foreach ($dok as $d)
                <div class="unggah {{ $d->status_dokumen === 'perlu_perbaikan' ? 'galat' : '' }}">
                    <span class="ikon"><i class="bi bi-file-earmark-pdf"></i></span>
                    <div class="flex-fill">
                        <div class="d-flex flex-wrap gap-2 align-items-center"><b>{{ $d->jenis->nama_dokumen }}</b><x-status :warna="$d->statusWarna()">{{ $d->statusLabel() }}</x-status></div>
                        <div class="small"><a href="{{ route('berkas.lihat', $d) }}" target="_blank" class="nama-file">{{ $d->nama_file }}</a> <span class="text-3">· {{ $d->ukuran_kb }} KB · {{ $d->uploaded_at?->translatedFormat('d M Y H:i') }}</span></div>
                        @if ($d->catatan_pemeriksaan)<div class="small text-danger mt-1"><i class="bi bi-chat-left-text me-1"></i>{{ $d->catatan_pemeriksaan }}</div>@endif
                        @if ($d->status_dokumen === 'perlu_perbaikan' && $p->status_pengajuan === 'perbaikan')
                            <input type="file" name="berkas[{{ $d->id_jenis }}]" accept="{{ $d->jenis->accept() }}" class="form-control form-control-sm mt-2" required>
                            <div class="form-text">{{ $d->jenis->format_file }}, maks. {{ $d->jenis->ukuran_maks_mb }} MB</div>
                        @endif
                    </div>
                    <a href="{{ route('berkas.lihat', $d) }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat</a>
                </div>
            @endforeach
        </div>
        @if ($p->status_pengajuan === 'perbaikan')
            @if ($perlu->isEmpty())
                <label class="d-flex gap-2 small mt-3"><input type="checkbox" name="konfirmasi" value="1" class="form-check-input" required> Saya sudah memperbaiki data sesuai catatan admin (melalui Ubah Data).</label>
            @endif
            <div class="text-end mt-3"><button class="btn btn-primary"><i class="bi bi-send me-1"></i>Kirim Perbaikan</button></div>
        @endif
    </form>
@else
    <div class="kartu">
        <div class="kartu-judul mb-3">Riwayat Pengajuan</div>
        @if ($p->riwayat->isEmpty())
            <x-kosong judul="Belum ada riwayat" ikon="bi-clock-history" />
        @else
            <ul class="timeline">
                @foreach ($p->riwayat as $r)
                    <li><div class="waktu">{{ $r->created_at?->translatedFormat('d M Y, H:i') }} · {{ $r->pelaku?->isMahasiswa() ? 'Anda' : ($r->pelaku?->nama ?? 'Sistem') }}</div>
                        <div class="small">{{ ucfirst($r->aksi) }}@if($r->status_lama || $r->status_baru): {{ \App\Models\Pengajuan::STATUS[$r->status_lama] ?? $r->status_lama ?? '-' }} → <b>{{ \App\Models\Pengajuan::STATUS[$r->status_baru] ?? $r->status_baru }}</b>@endif</div>
                        @if ($r->keterangan)<div class="small text-3">{{ $r->keterangan }}</div>@endif</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
@endsection
