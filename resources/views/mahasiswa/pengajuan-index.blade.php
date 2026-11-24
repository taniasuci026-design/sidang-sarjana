@extends('layouts.portal')
@section('title', 'Status Pengajuan')
@section('content')
<h1 class="judul-halaman">Status Pengajuan</h1>
<div class="sub-halaman mb-3">Riwayat seluruh pengajuan sidang Anda.</div>
<div class="kartu">
    @if ($pengajuan->isEmpty())
        <x-kosong judul="Belum ada pengajuan" ikon="bi-file-earmark-plus"><a href="{{ route('mahasiswa.pengajuan.create') }}">Ajukan sidang</a> untuk memulai.</x-kosong>
    @else
        <div class="table-responsive"><table class="tabel">
            <thead><tr><th>No. Pengajuan</th><th>Tanggal Diajukan</th><th>Paket Sidang</th><th>Jalur</th><th>Status</th><th>Kategori Hasil</th><th>Diperbarui</th><th></th></tr></thead>
            <tbody>@foreach ($pengajuan as $p)<tr>
                <td class="fw-bold">{{ $p->no_pengajuan }}</td><td>{{ $p->diajukan_at?->translatedFormat('d M Y H:i') }}</td>
                <td>{{ $p->paketLabel() }}</td><td>{{ $p->jalurLabel() }}</td><td><x-pengajuan-status :p="$p" /></td>
                <td><x-kategori :k="$p->kategori" /></td><td class="text-3">{{ $p->updated_at?->translatedFormat('d M Y H:i') }}</td>
                <td class="text-end"><a href="{{ route('mahasiswa.pengajuan.show', $p) }}" class="btn btn-outline-primary btn-sm">Detail</a></td></tr>@endforeach</tbody>
        </table></div>
    @endif
</div>
@endsection
