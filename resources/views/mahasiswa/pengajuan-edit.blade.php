@extends('layouts.portal')
@section('title', 'Ubah Data Pengajuan')
@section('content')
@php $bolehNpm = $p->status_pengajuan === 'menunggu'; @endphp
<a href="{{ route('mahasiswa.pengajuan.show', $p) }}" class="tautan-kembali"><i class="bi bi-arrow-left"></i> Kembali ke detail</a>
<h1 class="judul-halaman mt-2">Ubah Data Pengajuan</h1>
<div class="sub-halaman mb-3">{{ $p->no_pengajuan }} · Perbaiki salah ketik pada data Anda. Admin akan menerima pemberitahuan perubahan.</div>
<form method="POST" action="{{ route('mahasiswa.pengajuan.update', $p) }}" class="kartu" style="max-width:820px">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Nama Lengkap <span class="wajib">*</span></label><input name="nama_lengkap" value="{{ old('nama_lengkap', $p->mahasiswa->nama_lengkap) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">NPM {!! $bolehNpm ? '<span class="wajib">*</span>' : '' !!}</label>
            @if ($bolehNpm)
                <input name="npm" value="{{ old('npm', $p->mahasiswa->npm) }}" class="form-control" required inputmode="numeric" maxlength="{{ config('sidang.npm.panjang') }}">
                <div class="form-text">NPM hanya dapat diubah selama status pengajuan Menunggu.</div>
            @else
                <input value="{{ $p->mahasiswa->npm }}" class="form-control bg-light" disabled><div class="form-text">NPM terkunci karena pengajuan sudah diperiksa.</div>
            @endif
        </div>
        <div class="col-md-6"><label class="form-label">Kontak WhatsApp <span class="wajib">*</span></label><input name="no_whatsapp" value="{{ old('no_whatsapp', $p->mahasiswa->user->no_whatsapp) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Jalur / Paket</label><input value="{{ $p->jalurLabel() }} · {{ $p->paketLabel() }}" class="form-control bg-light" disabled></div>
        @if ($p->jalur === 'transfer')
            <div class="col-12"><label class="form-label">Nama Dosen Pembimbing PI (beserta gelar) <span class="wajib">*</span></label><input name="dosen_pembimbing_pi" value="{{ old('dosen_pembimbing_pi', $p->dosen_pembimbing_pi) }}" class="form-control" required></div>
        @endif
        @if ($p->paket_sidang === 'kompre')
            <div class="col-12"><label class="form-label">Materi Sidang Komprehensif <span class="wajib">*</span></label>
                @foreach ($materi as $m)
                    <label class="pilihan mb-2"><input type="radio" name="id_materi" value="{{ $m->id_materi }}" @checked(old('id_materi', $p->id_materi) == $m->id_materi)> <span><b class="text-navy">{{ $m->nama_paket }}:</b> {{ $m->teks() }}</span></label>
                @endforeach
            </div>
        @endif
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="{{ route('mahasiswa.pengajuan.show', $p) }}" class="btn btn-light border">Batal</a><button class="btn btn-primary">Simpan Perubahan</button></div>
</form>
@endsection
