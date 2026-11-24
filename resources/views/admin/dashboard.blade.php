@extends('layouts.portal')
@section('title', auth()->user()->isSuperAdmin() ? 'Beranda' : 'Dashboard')
@section('content')
@php $super = auth()->user()->isSuperAdmin(); @endphp
<h1 class="judul-halaman">{{ $super ? 'Beranda' : 'Dashboard' }}</h1>
<div class="sub-halaman mb-3">Ringkasan pengajuan sidang sarjana{{ $periode ? ' · ' . $periode->nama_periode : '' }}.</div>
@if ($super)
    <div class="row g-3 mb-3">
        <div class="col-md-6"><div class="info-banner {{ $periode?->sedangDibuka() ? 'hijau' : 'kuning' }} h-100"><i class="bi bi-calendar-check"></i><div>
            <b>Pendaftaran {{ $periode?->sedangDibuka() ? 'dibuka' : 'ditutup' }}</b>@if($periode) · {{ $periode->tanggal_buka->translatedFormat('d M Y') }} – {{ $periode->tanggal_tutup->translatedFormat('d M Y') }}@endif
            <div><a href="{{ route('super.pengaturan.index') }}" class="fw-semibold">Kelola periode →</a></div></div></div></div>
        <div class="col-md-6"><div class="info-banner {{ $laporanMenunggu ? 'merah' : 'biru' }} h-100"><i class="bi bi-person-exclamation"></i><div>
            <b>{{ $laporanMenunggu }} permintaan hapus akun</b> menunggu tanggapan Anda.
            <div><a href="{{ route('super.mahasiswa.index', ['tab' => 'dilaporkan']) }}" class="fw-semibold">Tinjau laporan admin →</a></div></div></div></div>
    </div>
@endif
<div class="row g-3 mb-3">
    @foreach ([['total','Total Pengajuan','navy','bi-folder2'],['menunggu','Menunggu','oranye','bi-clock'],['diproses','Diproses','primer','bi-hourglass-split'],['perbaikan','Perbaikan','merah','bi-exclamation-circle'],['selesai','Selesai','hijau','bi-check-circle']] as [$k,$l,$w,$i])
        <div class="col-6 col-md-4 col-xl"><a href="{{ route('admin.pengajuan.index', $k === 'total' ? [] : ['status' => $k]) }}" class="d-block text-reset"><div class="stat {{ $w }}"><div class="lbl">{{ $l }} <i class="bi {{ $i }}"></i></div><div class="nilai">{{ $stat[$k] }}</div><div class="ket">{{ $k === 'total' ? $hariIni . ' masuk hari ini' : 'pengajuan' }}</div><div class="garis-bawah"></div></div></a></div>
    @endforeach
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="kartu h-100">
            <div class="d-flex justify-content-between mb-3"><div class="kartu-judul">Antrean Pemeriksaan</div><a href="{{ route('admin.pengajuan.index', ['status' => 'menunggu', 'urut' => 'terlama']) }}" class="small fw-semibold">Lihat semua</a></div>
            @if ($antrean->isEmpty())<x-kosong judul="Antrean kosong" ikon="bi-check2-all">Tidak ada pengajuan yang menunggu pemeriksaan.</x-kosong>
            @else<div class="table-responsive"><table class="tabel"><thead><tr><th>No. Pengajuan</th><th>Mahasiswa</th><th>Diajukan</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach ($antrean as $p)<tr><td class="fw-semibold">{{ $p->no_pengajuan }}</td><td><div class="nama">{{ $p->mahasiswa->nama_lengkap }}</div><div class="text-3 small">{{ $p->mahasiswa->npm }} · {{ $p->paketLabel() }}</div></td>
                    <td>{{ $p->diajukan_at?->diffForHumans() }}</td><td><x-pengajuan-status :p="$p" /></td><td><a href="{{ route('admin.pengajuan.show', $p) }}" class="btn btn-outline-primary btn-sm">Lihat</a></td></tr>@endforeach
            </tbody></table></div>@endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="kartu mb-3">
            <div class="kartu-judul mb-3">Kategori Hasil</div>
            @foreach ($kategori as [$k, $n])
                <a href="{{ route('admin.kategori.index', ['k' => $k->id_kategori]) }}" class="d-flex justify-content-between align-items-center py-2 border-bottom text-reset"><span><x-status :warna="$k->warna()" polos>{{ $k->label() }}</x-status> <span class="small ms-1">{{ $k->nama_kategori }}</span></span><b class="text-navy">{{ $n }}</b></a>
            @endforeach
        </div>
        <div class="kartu">
            <div class="kartu-judul mb-3">Aktivitas Terbaru</div>
            @forelse ($aktivitas as $a)
                <div class="small py-2 border-bottom"><b class="text-navy">{{ $a->pelaku?->nama ?? 'Sistem' }}</b> · {{ $a->aksi }} <a href="{{ $a->pengajuan ? route('admin.pengajuan.show', $a->pengajuan) : '#' }}">{{ $a->pengajuan?->no_pengajuan }}</a><div class="text-3" style="font-size:11px">{{ $a->created_at?->diffForHumans() }}</div></div>
            @empty <div class="small text-3">Belum ada aktivitas.</div> @endforelse
        </div>
    </div>
</div>
@endsection
