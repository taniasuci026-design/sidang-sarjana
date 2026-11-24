@extends('layouts.base')
@section('title', 'Cek Status Pemberkasan')
@section('body')
<section class="hero" style="padding-bottom:120px">
    <div class="container">@include('publik._nav')
        <div class="label" style="margin-top:40px">LAYANAN PUBLIK</div>
        <h1 style="font-size:40px">Cek Status <span>Pemberkasan</span></h1>
        <p>Cari status pemberkasan sidang berdasarkan NPM atau kategori hasil. Data pribadi mahasiswa tidak ditampilkan.</p>
    </div>
</section>
<div class="container" style="max-width:900px">
    <form class="kartu shadow-sm" style="margin-top:-80px;position:relative" method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-md-6"><label class="form-label">NPM</label><input name="npm" value="{{ $input['npm'] ?? '' }}" class="form-control" placeholder="Masukkan NPM" inputmode="numeric"></div>
            <div class="col-md-4"><label class="form-label">Kategori hasil</label>
                <select name="kategori" class="form-select"><option value="">Semua kategori</option>
                    @foreach ($kategori as $k)<option value="{{ $k->id_kategori }}" @selected(($input['kategori'] ?? '') == $k->id_kategori)>{{ $k->label() }} · {{ $k->nama_kategori }}</option>@endforeach
                </select></div>
            <div class="col-md-2 d-grid"><button class="btn btn-oranye"><i class="bi bi-search me-1"></i>Cari</button></div>
        </div>
    </form>
    <div class="kartu mt-3">
        @if (! $dicari)
            <x-kosong judul="Masukkan NPM atau pilih kategori" ikon="bi-search">Hasil pencarian akan tampil di sini.</x-kosong>
        @elseif ($hasil->isEmpty())
            <x-kosong judul="Data tidak ditemukan" ikon="bi-emoji-frown">Periksa kembali NPM yang Anda masukkan.</x-kosong>
        @else
            <div class="table-responsive"><table class="tabel">
                <thead><tr><th>NPM</th><th>No. Pengajuan</th><th>Paket Sidang</th><th>Status</th><th>Kategori Hasil</th><th>Diperbarui</th></tr></thead>
                <tbody>@foreach ($hasil as $h)<tr>
                    <td class="font-monospace">{{ $h['npm'] }}</td><td>{{ $h['no'] }}</td><td>{{ $h['paket'] }}</td>
                    <td><x-status :warna="$h['warna']">{{ $h['status'] }}</x-status></td><td><x-kategori :k="$h['kategori']" /></td>
                    <td class="text-3">{{ $h['diperbarui']?->translatedFormat('d M Y H:i') }}</td></tr>@endforeach</tbody>
            </table></div>
        @endif
    </div>
</div>
@include('publik._footer')
@endsection
