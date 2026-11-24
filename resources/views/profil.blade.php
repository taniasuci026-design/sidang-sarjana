@extends('layouts.portal')
@section('title', 'Profil')
@section('content')
<h1 class="judul-halaman">Profil</h1>
<div class="sub-halaman mb-3">Kelola informasi akun dan kata sandi Anda.</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="kartu">
            <div class="d-flex align-items-center gap-3 mb-3"><span class="avatar" style="width:56px;height:56px;font-size:18px">{{ $u->inisial() }}</span>
                <div><div class="fw-bold text-navy fs-5">{{ $u->nama }}</div><x-status warna="primary" polos>{{ $u->roleLabel() }}</x-status></div></div>
            <form method="POST" action="{{ route('profil.update') }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Nama</label><input name="nama" value="{{ old('nama', $u->nama) }}" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input value="{{ $u->email }}" class="form-control bg-light" disabled></div>
                    <div class="col-md-6"><label class="form-label">Nomor WhatsApp</label><input name="no_whatsapp" value="{{ old('no_whatsapp', $u->no_whatsapp) }}" class="form-control"></div>
                    @if ($u->mahasiswa)
                        <div class="col-md-6"><label class="form-label">NPM</label><input value="{{ $u->mahasiswa->npm }}" class="form-control bg-light" disabled>
                            <div class="form-text">NPM dapat diubah melalui “Ubah Data” selama pengajuan masih Menunggu.</div></div>
                        <div class="col-md-6"><label class="form-label">Program Studi</label><input value="{{ $u->mahasiswa->program_studi }}" class="form-control bg-light" disabled></div>
                        <div class="col-md-6"><label class="form-label">Angkatan</label><input value="{{ $u->mahasiswa->angkatan }}" class="form-control bg-light" disabled></div>
                    @endif
                    <div class="col-md-6"><label class="form-label">Terakhir masuk</label><input value="{{ $u->last_login_at?->translatedFormat('d M Y, H:i') ?? '-' }}" class="form-control bg-light" disabled></div>
                </div>
                <div class="text-end mt-3"><button class="btn btn-primary">Simpan Profil</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="kartu">
            <div class="kartu-judul mb-3">Ubah Kata Sandi</div>
            <form method="POST" action="{{ route('profil.sandi') }}">
                @csrf @method('PUT')
                <div class="mb-3"><label class="form-label">Kata sandi lama</label><input type="password" name="password_lama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Kata sandi baru</label><input type="password" name="password" class="form-control" required><div class="form-text">Minimal 8 karakter, kombinasi huruf dan angka.</div></div>
                <div class="mb-3"><label class="form-label">Ulangi kata sandi baru</label><input type="password" name="password_confirmation" class="form-control" required></div>
                <button class="btn btn-primary w-100">Ubah Kata Sandi</button>
            </form>
        </div>
    </div>
</div>
@endsection
