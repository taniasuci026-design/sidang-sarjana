@extends('layouts.portal')
@section('title', 'Dashboard')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div><h1 class="judul-halaman">Halo, {{ auth()->user()->nama }}</h1><div class="sub-halaman">Selamat datang di sistem pengajuan sidang sarjana. Kelola pengajuan Anda di sini.</div></div>
    @if ($bisaAjukan)
        <a href="{{ route('mahasiswa.pengajuan.create') }}" class="btn btn-oranye"><i class="bi bi-plus-lg me-1"></i>Ajukan Sidang Baru</a>
    @elseif (! $periode?->sedangDibuka())
        <span class="status status-secondary polos"><i class="bi bi-lock me-1"></i>Pendaftaran ditutup</span>
    @endif
</div>
<div class="row g-3 mb-3">
    @foreach ([['total','Total Pengajuan','Seluruh pengajuan sidang','navy','bi-folder2'],['menunggu','Menunggu','Dalam antrean verifikasi','oranye','bi-clock'],['diproses','Diproses','Sedang diperiksa admin','primer','bi-hourglass-split'],['perbaikan','Perbaikan','Memerlukan tindak lanjut','merah','bi-exclamation-circle'],['selesai','Selesai','Berkas telah diterima','hijau','bi-check-circle']] as [$k,$l,$ket,$w,$i])
        <div class="col-6 col-md-4 col-xl"><div class="stat {{ $w }}"><div class="lbl">{{ $l }} <i class="bi {{ $i }}"></i></div><div class="nilai">{{ str_pad($stat[$k], 2, '0', STR_PAD_LEFT) }}</div><div class="ket">{{ $ket }}</div><div class="garis-bawah"></div></div></div>
    @endforeach
</div>
<div class="row g-3">
    <div class="col-lg-8">
        <div class="kartu">
            <div class="d-flex justify-content-between mb-3"><div class="kartu-judul">Pengajuan Terbaru</div><a href="{{ route('mahasiswa.pengajuan.index') }}" class="small fw-semibold">LIHAT SEMUA <i class="bi bi-arrow-up-right"></i></a></div>
            @if ($pengajuan->isEmpty())
                <x-kosong judul="Belum ada pengajuan" ikon="bi-file-earmark-plus">Klik <b>Ajukan Sidang Baru</b> untuk memulai.</x-kosong>
            @else
                <div class="table-responsive"><table class="tabel">
                    <thead><tr><th>No. Pengajuan</th><th>Tanggal</th><th>Paket</th><th>Status</th><th></th></tr></thead>
                    <tbody>@foreach ($pengajuan->take(5) as $p)<tr>
                        <td><a href="{{ route('mahasiswa.pengajuan.show', $p) }}" class="fw-bold">{{ $p->no_pengajuan }}</a><div class="text-3" style="font-size:11px">{{ $p->paketLabel() }} · {{ $p->jalurLabel() }}</div></td>
                        <td>{{ $p->diajukan_at?->translatedFormat('d M Y') }}</td><td>{{ $p->paketLabel() }}</td>
                        <td><x-pengajuan-status :p="$p" /></td>
                        <td class="text-end"><a href="{{ route('mahasiswa.pengajuan.show', $p) }}" class="small fw-semibold">Detail <i class="bi bi-arrow-up-right"></i></a></td></tr>@endforeach</tbody>
                </table></div>
            @endif
        </div>
    </div>
    <div class="col-lg-4">
        <div class="kartu p-0 overflow-hidden">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 text-white" style="background:var(--biru)"><span class="small fw-bold"><i class="bi bi-info-circle me-1"></i>CATATAN PERBAIKAN</span><span class="small">{{ str_pad($perbaikan->count(), 2, '0', STR_PAD_LEFT) }}</span></div>
            <div class="p-3">
                @forelse ($perbaikan as $d)
                    <div class="mb-3">
                        <div class="small fw-bold text-danger">● BERKAS: {{ strtoupper($d->jenis->nama_dokumen) }} <span class="text-3 fw-normal">· {{ $d->pengajuan->no_pengajuan }}</span></div>
                        <div class="small text-2">{{ $d->catatan_pemeriksaan ?: 'Dokumen perlu diperbaiki.' }}</div>
                        <a href="{{ route('mahasiswa.pengajuan.show', [$d->pengajuan, 'tab' => 'dokumen']) }}" class="small fw-bold">UNGGAH ULANG <i class="bi bi-arrow-up-right"></i></a>
                    </div>
                @empty
                    <div class="text-center text-3 small py-3">Belum ada status perbaikan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection