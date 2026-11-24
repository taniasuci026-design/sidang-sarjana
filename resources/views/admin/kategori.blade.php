@extends('layouts.portal')
@section('title', 'Kategori Hasil')
@section('content')
@php $super = auth()->user()->isSuperAdmin(); @endphp
<h1 class="judul-halaman">Kategori Hasil</h1>
<div class="sub-halaman mb-3">Pantau mahasiswa di tiap kategori hasil pemeriksaan dan klik <b>Lihat</b> untuk membuka detail pengajuan.</div>
<div class="row g-3 mb-3">
    @foreach ($kategori as $k)
        <div class="col-md-6 col-xl-3"><div class="kartu h-100" style="border-top:4px solid var(--bs-{{ ['success'=>'success','warning'=>'warning','danger'=>'danger','indigo'=>'purple'][$k->warna()] }})">
            <div class="small text-3">{{ $k->label() }}</div><div class="fw-bold text-navy">{{ $k->nama_kategori }}</div>
            <div class="d-flex align-items-end justify-content-between mt-2"><span class="fs-2 fw-bold text-navy">{{ $k->pengajuan_count }}</span>
                @if ($super)<button class="btn btn-link btn-sm p-0" data-bs-toggle="modal" data-bs-target="#modalTeks" data-isi="{{ json_encode(['_aksi' => route('super.kategori.update', $k), 'teks_notifikasi' => $k->teks_notifikasi, 'nama' => $k->label() . ' · ' . $k->nama_kategori]) }}">Ubah teks</button>@endif</div>
            <div class="small text-2 mt-2">“{{ $k->teks_notifikasi }}”</div>
            <div class="text-3 mt-1" style="font-size:11px">Teks notifikasi diubah oleh {{ $k->pengubah?->nama ?? 'Superadmin' }}{{ $k->updated_at ? ' · ' . $k->updated_at->translatedFormat('d M Y') : '' }}</div>
        </div></div>
    @endforeach
</div>
<div class="kartu">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3"><div class="kartu-judul">Daftar Mahasiswa per Kategori</div>
        <form method="GET" class="d-flex gap-2"><input type="hidden" name="k" value="{{ $pilih }}"><div class="input-ikon"><i class="bi bi-search"></i><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau NPM"></div></form></div>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.kategori.index') }}" class="pil {{ $pilih ? '' : 'aktif' }}">Semua <span class="n">{{ $totalTerkategori }}</span></a>
        @foreach ($kategori as $k)<a href="{{ route('admin.kategori.index', ['k' => $k->id_kategori]) }}" class="pil {{ $pilih == $k->id_kategori ? 'aktif' : '' }}">{{ $k->nama_kategori }} <span class="n">{{ $k->pengajuan_count }}</span></a>@endforeach
    </div>
    @if ($pengajuan->isEmpty())<x-kosong judul="Belum ada mahasiswa di kategori ini" ikon="bi-tags" />
    @else
    <div class="table-responsive"><table class="tabel">
        <thead><tr><th>NPM</th><th>Nama</th><th>Paket</th><th>Kategori</th><th>Kekurangan</th><th>Diperbarui</th><th class="text-center">Aksi</th></tr></thead>
        <tbody>@foreach ($pengajuan as $p)
            @php $kurang = $p->dokumen->where('status_dokumen', 'perlu_perbaikan'); @endphp
            <tr><td>{{ $p->mahasiswa->npm }}</td><td class="nama">{{ $p->mahasiswa->nama_lengkap }}</td><td>{{ $p->paketLabel() }}</td><td><x-kategori :k="$p->kategori" /></td>
            <td class="small {{ $kurang->count() ? 'text-danger' : 'text-3' }}">{{ $kurang->count() ? $kurang->map(fn($d) => $d->jenis->nama_dokumen . ': ' . ($d->catatan_pemeriksaan ?: 'Perlu perbaikan'))->implode('; ') : ($p->kategori?->kode === '1' ? 'Tidak ada kekurangan' : $p->kategori?->nama_kategori) }}</td>
            <td class="text-3 text-nowrap">{{ $p->updated_at?->translatedFormat('d M Y H:i') }}</td>
            <td class="text-center"><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-outline-primary btn-sm">Lihat</a></td></tr>
        @endforeach</tbody>
    </table></div>
    <div class="mt-3">{{ $pengajuan->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@if ($super)
<div class="modal fade" id="modalTeks" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">@csrf @method('PUT')
    <div class="modal-header"><div><div class="modal-title">Ubah Teks Notifikasi</div><div class="small text-3" data-teks="nama"></div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><label class="form-label">Teks notifikasi untuk mahasiswa</label><textarea name="teks_notifikasi" rows="4" class="form-control" maxlength="500" required></textarea></div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
@endif
@endsection
