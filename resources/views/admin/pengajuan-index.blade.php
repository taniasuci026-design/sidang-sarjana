@extends('layouts.portal')
@section('title', 'Daftar Pengajuan')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div><h1 class="judul-halaman">Daftar Pengajuan</h1><div class="sub-halaman">Seluruh pengajuan mahasiswa. Klik <b>Lihat</b> untuk memeriksa berkas dan memberi hasil pemeriksaan.</div></div>
    <div class="dropdown"><button class="btn btn-outline-primary" data-bs-toggle="dropdown"><i class="bi bi-download me-1"></i>Ekspor</button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('admin.laporan.excel', request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Excel (CSV)</a>
            <a class="dropdown-item" href="{{ route('admin.laporan.cetak', request()->query()) }}" target="_blank"><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>Cetak / PDF</a>
            <a class="dropdown-item" href="{{ route('admin.laporan.zip', request()->query()) }}"><i class="bi bi-file-earmark-zip me-2 text-primary"></i>Berkas (ZIP)</a>
        </div></div>
</div>
@include('admin._filter')
<div class="kartu">
    <div class="d-flex justify-content-between mb-2"><b class="text-navy">{{ $pengajuan->total() }} pengajuan ditemukan</b></div>
    @if ($pengajuan->isEmpty())
        <x-kosong judul="Tidak ada pengajuan" ikon="bi-search">Coba ubah filter atau kata kunci pencarian.</x-kosong>
    @else
    <div class="table-responsive"><table class="tabel">
        <thead><tr><th>No. Pengajuan</th><th>Tanggal</th><th>Nama Mahasiswa</th><th>NPM</th><th>Paket Sidang</th><th>Status</th><th>Kategori Hasil</th><th>Terakhir Diperbarui</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>@foreach ($pengajuan as $p)<tr>
            <td class="fw-semibold">{{ $p->no_pengajuan }}</td><td>{{ $p->diajukan_at?->translatedFormat('d M Y') }}</td>
            <td class="nama">{{ $p->mahasiswa->nama_lengkap }}@if($p->mahasiswa->status_akun === 'dilaporkan') <i class="bi bi-flag-fill text-danger" title="Dilaporkan"></i>@endif</td>
            <td>{{ $p->mahasiswa->npm }}</td><td>{{ $p->paketLabel() }}</td><td><x-pengajuan-status :p="$p" /></td>
            <td><x-kategori :k="$p->kategori" /></td><td class="text-3">{{ $p->updated_at?->translatedFormat('d M Y H:i') }}</td>
            <td class="text-center"><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-outline-primary btn-sm">Lihat</a></td></tr>@endforeach</tbody>
    </table></div>
    <div class="d-flex justify-content-between align-items-center mt-3 small text-3"><span>Menampilkan {{ $pengajuan->firstItem() }}–{{ $pengajuan->lastItem() }} dari {{ $pengajuan->total() }} pengajuan</span>{{ $pengajuan->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
