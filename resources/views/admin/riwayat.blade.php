@extends('layouts.portal')
@section('title', 'Riwayat Perubahan')
@section('content')
<a href="{{ route('admin.pengajuan.show', $p) }}" class="tautan-kembali"><i class="bi bi-arrow-left"></i> Kembali ke Pemeriksaan Berkas</a>
<h1 class="judul-halaman mt-2">Riwayat Perubahan</h1>
<div class="sub-halaman mb-3">{{ $p->no_pengajuan }} · {{ $p->mahasiswa->nama_lengkap }} ({{ $p->mahasiswa->npm }})</div>
<div class="kartu">
    @if ($riwayat->isEmpty())<x-kosong judul="Belum ada riwayat" ikon="bi-clock-history" />
    @else
    <div class="table-responsive"><table class="tabel">
        <thead><tr><th>Waktu</th><th>Pengguna</th><th>Peran</th><th>Aksi</th><th>Perubahan Status</th><th>Keterangan</th></tr></thead>
        <tbody>@foreach ($riwayat as $r)<tr>
            <td class="text-nowrap">{{ $r->created_at?->translatedFormat('d M Y, H:i') }}</td><td class="nama">{{ $r->pelaku?->nama ?? 'Sistem' }}</td>
            <td>{{ $r->pelaku?->roleLabel() }}</td><td>{{ ucfirst($r->aksi) }}</td>
            <td>@if($r->status_lama || $r->status_baru){{ \App\Models\Pengajuan::STATUS[$r->status_lama] ?? ($r->status_lama ?? '-') }} → <b>{{ \App\Models\Pengajuan::STATUS[$r->status_baru] ?? $r->status_baru }}</b>@else - @endif</td>
            <td class="small text-2">{{ $r->keterangan }}</td></tr>@endforeach</tbody>
    </table></div>
    <div class="mt-3">{{ $riwayat->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
