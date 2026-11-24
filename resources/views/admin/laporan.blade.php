@extends('layouts.portal')
@section('title', 'Laporan')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div><h1 class="judul-halaman">Laporan</h1><div class="sub-halaman">Rekapitulasi pengajuan sidang sesuai filter. Unduh data atau berkas untuk arsip.</div></div>
    <div class="dropdown"><button class="btn btn-primary" data-bs-toggle="dropdown"><i class="bi bi-download me-1"></i>Ekspor</button>
        <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="{{ route('admin.laporan.excel', request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet me-2 text-success"></i>Excel (CSV)</a>
            <a class="dropdown-item" href="{{ route('admin.laporan.cetak', request()->query()) }}" target="_blank"><i class="bi bi-printer me-2 text-danger"></i>Cetak / Simpan PDF</a>
            <a class="dropdown-item" href="{{ route('admin.laporan.zip', request()->query()) }}"><i class="bi bi-file-earmark-zip me-2 text-primary"></i>Unduh Berkas (ZIP)</a>
        </div></div>
</div>
@include('admin._filter')
<div class="row g-3 mb-3">
    @foreach (\App\Models\Pengajuan::STATUS as $k => $l)
        <div class="col-6 col-lg-3"><div class="stat {{ ['menunggu'=>'oranye','diproses'=>'primer','perbaikan'=>'merah','selesai'=>'hijau'][$k] }}"><div class="lbl">{{ $l }}</div><div class="nilai">{{ $ringkas[$k] ?? 0 }}</div><div class="ket">pengajuan</div></div></div>
    @endforeach
</div>
<div class="kartu">
    <div class="mb-2"><b class="text-navy">{{ $pengajuan->total() }} pengajuan</b></div>
    @if ($pengajuan->isEmpty())<x-kosong judul="Tidak ada data" ikon="bi-bar-chart" />
    @else
    <div class="table-responsive"><table class="tabel">
        <thead><tr><th>No. Pengajuan</th><th>Tanggal</th><th>NPM</th><th>Nama</th><th>Jalur</th><th>Paket</th><th>Status</th><th>Kategori</th><th class="text-center">Berkas</th></tr></thead>
        <tbody>@foreach ($pengajuan as $p)<tr>
            <td><a href="{{ route('admin.pengajuan.show', $p) }}" class="fw-semibold">{{ $p->no_pengajuan }}</a></td><td>{{ $p->diajukan_at?->translatedFormat('d M Y') }}</td><td>{{ $p->mahasiswa->npm }}</td>
            <td class="nama">{{ $p->mahasiswa->nama_lengkap }}</td><td>{{ $p->jalurLabel() }}</td><td>{{ $p->paketLabel() }}</td><td><x-pengajuan-status :p="$p" /></td><td><x-kategori :k="$p->kategori" /></td>
            <td class="text-center"><a href="{{ route('admin.pengajuan.zip', $p) }}" class="btn btn-light border btn-sm" title="Unduh ZIP"><i class="bi bi-file-earmark-zip"></i></a></td></tr>@endforeach</tbody>
    </table></div>
    <div class="mt-3">{{ $pengajuan->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
