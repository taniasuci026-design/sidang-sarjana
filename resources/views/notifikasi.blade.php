@extends('layouts.portal')
@section('title', 'Notifikasi')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div><h1 class="judul-halaman">Notifikasi</h1><div class="sub-halaman">Riwayat seluruh notifikasi akun Anda.</div></div>
    @if ($belum)<form method="POST" action="{{ route('notifikasi.baca-semua') }}">@csrf<button class="btn btn-outline-primary">Tandai semua dibaca</button></form>@endif
</div>
<div class="kartu p-0 overflow-hidden">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
        <b class="text-navy">Semua Notifikasi</b>
        <div class="d-flex gap-2">
            <a href="{{ route('notifikasi') }}" class="pil {{ request('filter') ? '' : 'aktif' }}">Semua <span class="n">{{ $total }}</span></a>
            <a href="{{ route('notifikasi', ['filter' => 'belum']) }}" class="pil {{ request('filter') ? 'aktif' : '' }}">Belum dibaca <span class="n">{{ $belum }}</span></a>
        </div>
    </div>
    @forelse ($notifikasi as $n)
        <a href="{{ route('notifikasi.buka', $n) }}" class="notif-item d-flex justify-content-between gap-3 {{ $n->dibaca_at ? '' : 'baru' }}">
            <div><div class="fw-semibold">{{ $n->judul }}</div><div class="small text-2">{{ $n->pesan }}</div><div class="text-3" style="font-size:11.5px">{{ $n->created_at?->translatedFormat('d M Y, H:i') }}</div></div>
            @unless ($n->dibaca_at)<span class="text-primary" title="Belum dibaca">●</span>@endunless
        </a>
    @empty
        <x-kosong judul="Belum ada notifikasi" ikon="bi-bell" />
    @endforelse
</div>
<div class="mt-3">{{ $notifikasi->links('pagination::bootstrap-5') }}</div>
@endsection
