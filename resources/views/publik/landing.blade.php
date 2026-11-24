@extends('layouts.base')
@section('title', 'Pendaftaran Sidang Sarjana')
@php use App\Models\KontenLanding as K; @endphp
@section('body')
<section class="hero">
    <div class="container">
        @include('publik._nav')
        <div class="label" data-l="hero_label">{{ K::info('hero_label') }}</div>
        <h1><span data-l="hero_judul_1" style="color:inherit">{{ K::info('hero_judul_1') }}</span><br><span data-l="hero_judul_2">{{ K::info('hero_judul_2') }}</span></h1>
        <p data-l="hero_deskripsi">{{ K::info('hero_deskripsi') }}</p>
        <div class="d-flex flex-wrap gap-2 align-items-center mt-4">
            <a href="{{ auth()->check() ? route(auth()->user()->dashboardRoute()) : route('daftar') }}" class="btn btn-oranye px-4 py-2"><span data-l="hero_tombol">{{ K::info('hero_tombol', 'Daftar Sidang') }}</span> <i class="bi bi-arrow-right ms-1"></i></a>
            @if ($periode)
                <span class="small" style="color:#DBEAFE"><i class="bi bi-calendar-event me-1"></i>{{ $periode->nama_periode }} ·
                    @if ($periode->sedangDibuka()) <b class="text-white">Pendaftaran dibuka</b> s.d. {{ $periode->tanggal_tutup->translatedFormat('d M Y') }}
                    @else <b class="text-warning">Pendaftaran ditutup</b> @endif</span>
            @endif
        </div>
    </div>
</section>
<div class="container">
    <form class="cek-kartu" method="GET" action="{{ route('cek-status') }}">
        <span class="bulat-cari"><i class="bi bi-search"></i></span>
        <div class="fw-bold text-navy mb-3">Cek Status Pemberkasan</div>
        <div class="input-group mb-2">
            <input name="npm" class="form-control" placeholder="Masukkan NPM mahasiswa" inputmode="numeric" required>
            <button class="btn btn-oranye" aria-label="Cari"><i class="bi bi-arrow-right"></i></button>
        </div>
        <div class="dropdown d-inline-block">
            <a href="#" class="small fw-semibold text-decoration-underline" data-bs-toggle="dropdown">Cek berdasarkan kategori</a>
            <div class="dropdown-menu">
                @foreach ($kategori as $k)<a class="dropdown-item small" href="{{ route('cek-status', ['kategori' => $k->id_kategori]) }}">{{ $k->label() }} · {{ $k->nama_kategori }}</a>@endforeach
            </div>
        </div>
    </form>

    <section class="mt-5 pt-4" id="alur">
        <div class="seksi-judul"><i class="bi bi-file-earmark-check text-primary fs-4"></i><div class="lbl mt-1">LANGKAH PENDAFTARAN</div><h2>Alur Pengajuan</h2></div>
        <div class="mx-auto" style="max-width:760px">
            @foreach ($alur as $i => $a)
                <div class="alur-item"><span class="no">{{ $i + 1 }}</span><div><div class="fw-semibold">{{ $a->judul }}</div><div class="small text-3">{{ $a->isi }}</div></div></div>
            @endforeach
        </div>
    </section>

    <section class="mt-5 pt-3" id="faq">
        <div class="seksi-judul"><div class="lbl">PERTANYAAN UMUM</div><h2>FAQ Pendaftaran Sidang</h2></div>
        <div class="accordion faq mx-auto" id="faqAcc" style="max-width:760px">
            @foreach ($faq as $i => $f)
                <div class="accordion-item">
                    <h3 class="accordion-header"><button class="accordion-button {{ $i ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $f->id_konten }}">{{ $f->judul }}</button></h3>
                    <div id="faq{{ $f->id_konten }}" class="accordion-collapse collapse {{ $i ? '' : 'show' }}" data-bs-parent="#faqAcc"><div class="accordion-body">{{ $f->isi }}</div></div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@include('publik._footer')
@endsection
