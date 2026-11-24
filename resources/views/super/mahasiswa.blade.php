@extends('layouts.portal')
@section('title', 'Data Mahasiswa')
@section('content')
<h1 class="judul-halaman">Data Mahasiswa</h1>
<div class="sub-halaman mb-3">Pantau akun mahasiswa pengaju. Superadmin hanya bisa melihat data dan menghapus akun yang dilaporkan admin.</div>
<div class="info-banner biru mb-3"><i class="bi bi-info-circle"></i><div><b>Superadmin tidak bisa masuk ke akun mahasiswa.</b> Data akun dan berkas hanya bisa diubah oleh mahasiswa sendiri.</div></div>
<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach (['semua' => 'Semua', 'aktif' => 'Aktif', 'dilaporkan' => 'Dilaporkan', 'arsip' => 'Arsip'] as $k => $l)
        <a href="{{ route('super.mahasiswa.index', ['tab' => $k]) }}" class="pil {{ $tab === $k ? 'aktif' : '' }}">{{ $l }} <span class="n">{{ $hitung[$k] }}</span></a>
    @endforeach
</div>
@if ($tab === 'arsip')
    <div class="info-banner biru mb-3"><i class="bi bi-archive"></i><div>Arsip berisi akun yang sudah dihapus. Data disimpan sebagai <b>laporan</b> dan tidak bisa diubah atau dipulihkan.</div></div>
@endif
<div class="kartu">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <form method="GET" class="flex-fill" style="max-width:520px"><input type="hidden" name="tab" value="{{ $tab }}"><div class="input-ikon"><i class="bi bi-search"></i><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Nama, NPM, atau email"></div></form>
        @if ($tab === 'arsip')<a href="{{ route('super.mahasiswa.ekspor-arsip') }}" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Ekspor Laporan</a>@endif
    </div>
    @if ($mahasiswa->isEmpty())<x-kosong judul="Tidak ada data mahasiswa" ikon="bi-people" />@else
    <div class="table-responsive"><table class="tabel">
        @if ($tab === 'arsip')
            <thead><tr><th>NPM</th><th>Nama</th><th>Alasan Laporan</th><th>Dilaporkan</th><th>Dihapus oleh</th><th>Tanggal Hapus</th><th>Aksi</th></tr></thead>
            <tbody>@foreach ($mahasiswa as $m) @php $l = $m->permintaanHapus->first(); @endphp<tr>
                <td>{{ $m->npm }}</td><td><div class="nama">{{ $m->nama_lengkap }}</div><div class="small text-3">{{ $m->user?->email }}</div></td>
                <td>{{ $l?->alasan ?? '-' }}</td><td>{{ $l?->pengaju?->nama ?? '-' }}</td><td>{{ $l?->peninjau?->nama ?? '-' }}</td><td class="text-nowrap">{{ $m->diarsipkan_at?->translatedFormat('d M Y') }}</td>
                <td><a href="{{ route('super.mahasiswa.show', $m) }}" class="btn btn-outline-primary btn-sm">Lihat</a></td></tr>@endforeach</tbody>
        @else
            <thead><tr><th>NPM</th><th>Nama</th><th>Email</th><th>Pengajuan</th><th>Status Akun</th><th>Aksi</th></tr></thead>
            <tbody>@foreach ($mahasiswa as $m)<tr style="{{ $m->status_akun === 'dilaporkan' ? 'background:#FFFBEB' : '' }}">
                <td>{{ $m->npm }}</td><td><div class="nama">{{ $m->nama_lengkap }}</div>@if($m->laporanAktif)<div class="small" style="color:#B45309">Dilaporkan {{ $m->laporanAktif->pengaju?->nama }}: {{ $m->laporanAktif->alasan }}</div>@endif</td>
                <td>{{ $m->user?->email }}</td><td>{{ $m->pengajuan_count }}</td>
                <td><x-status :warna="$m->status_akun === 'dilaporkan' ? 'warning' : 'success'">{{ $m->status_akun === 'dilaporkan' ? 'Dilaporkan' : 'Aktif' }}</x-status></td>
                <td class="text-nowrap"><a href="{{ route('super.mahasiswa.show', $m) }}" class="btn btn-outline-primary btn-sm">Lihat</a>
                    @if ($m->status_akun === 'dilaporkan')<a href="{{ route('super.mahasiswa.show', [$m, 'hapus' => 1]) }}" class="btn btn-outline-danger btn-sm">Hapus</a>@endif</td></tr>@endforeach</tbody>
        @endif
    </table></div>
    <div class="mt-3">{{ $mahasiswa->links('pagination::bootstrap-5') }}</div>@endif
</div>
@if ($tab !== 'arsip')
    <div class="info-banner kuning mt-3"><i class="bi bi-info-circle"></i><div>Tombol <b>Hapus</b> hanya muncul di akun berstatus <b>Dilaporkan</b> (dilaporkan admin). Akun yang dihapus tidak hilang, tapi pindah ke tab <b>Arsip</b> sebagai laporan.</div></div>
@endif
@endsection
