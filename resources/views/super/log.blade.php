@extends('layouts.portal')
@section('title', 'Log Aktivitas')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div><h1 class="judul-halaman">Log Aktivitas</h1><div class="sub-halaman">Jejak seluruh aktivitas penting pengguna di sistem.</div></div>
    <a href="{{ route('super.log.index', request()->query() + ['ekspor' => 'csv']) }}" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Ekspor CSV</a>
</div>
<form method="GET" class="kartu mb-3"><div class="row g-2">
    <div class="col-md-4"><div class="input-ikon"><i class="bi bi-search"></i><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Pengguna, no. pengajuan, keterangan"></div></div>
    <div class="col-6 col-md-2"><select name="peran" class="form-select"><option value="">Semua peran</option>@foreach ($peran as $k => $l)<option value="{{ $k }}" @selected(request('peran') === $k)>{{ $l }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><select name="aksi" class="form-select"><option value="">Semua aksi</option>@foreach ($daftarAksi as $a)<option @selected(request('aksi') === $a)>{{ $a }}</option>@endforeach</select></div>
    <div class="col-6 col-md-1"><input type="date" name="dari" value="{{ request('dari') }}" class="form-control" title="Dari"></div>
    <div class="col-6 col-md-1"><input type="date" name="sampai" value="{{ request('sampai') }}" class="form-control" title="Sampai"></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Terapkan</button></div>
</div></form>
<div class="kartu">
    @if ($log->isEmpty())<x-kosong judul="Belum ada aktivitas" ikon="bi-clock-history" />@else
    <div class="table-responsive"><table class="tabel">
        <thead><tr><th>Waktu</th><th>Pengguna</th><th>Peran</th><th>Aksi</th><th>Terkait</th><th>Perubahan</th><th>Keterangan</th></tr></thead>
        <tbody>@foreach ($log as $r)<tr>
            <td class="text-nowrap">{{ $r->created_at?->translatedFormat('d M Y, H:i') }}</td><td class="nama">{{ $r->pelaku?->nama ?? 'Sistem' }}</td><td>{{ $r->pelaku?->roleLabel() }}</td>
            <td>{{ ucfirst($r->aksi) }}</td>
            <td class="small">@if($r->pengajuan)<a href="{{ route('admin.pengajuan.show', $r->pengajuan) }}">{{ $r->pengajuan->no_pengajuan }}</a>@elseif($r->mahasiswa)<a href="{{ route('super.mahasiswa.show', $r->mahasiswa) }}">{{ $r->mahasiswa->npm }}</a>@else - @endif</td>
            <td class="small">@if($r->status_lama || $r->status_baru){{ $r->status_lama ?? '-' }} → <b>{{ $r->status_baru }}</b>@else - @endif</td>
            <td class="small text-2">{{ \Illuminate\Support\Str::limit($r->keterangan, 120) }}</td></tr>@endforeach</tbody>
    </table></div>
    <div class="mt-3">{{ $log->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
