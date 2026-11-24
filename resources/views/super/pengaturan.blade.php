@extends('layouts.portal')
@section('title', 'Pengaturan Sistem')
@section('content')
<h1 class="judul-halaman">Pengaturan Sistem</h1>
<div class="sub-halaman mb-3">Periode pendaftaran, dokumen persyaratan, dan paket materi sidang komprehensif. Perubahan berlaku untuk semua admin dan mahasiswa.</div>

<div class="kartu mb-3">
    <div class="kartu-judul mb-2">Status Pendaftaran Saat Ini</div>
    @if ($periode)
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3"><x-status :warna="$periode->sedangDibuka() ? 'success' : 'danger'" polos>Pendaftaran {{ $periode->sedangDibuka() ? 'Dibuka' : 'Ditutup' }}</x-status>
            <span class="small text-3">Terakhir diubah: {{ $periode->updated_at?->translatedFormat('d M Y, H:i') }} oleh {{ $periode->pengubah?->nama ?? '-' }}</span></div>
        <div class="info-banner {{ $periode->sedangDibuka() ? 'hijau' : 'merah' }}"><div><div class="fw-bold fs-6 text-navy">{{ $periode->nama_periode }}</div><div class="small">{{ $periode->tanggal_buka->translatedFormat('d M Y') }} – {{ $periode->tanggal_tutup->translatedFormat('d M Y') }}
            @if ($periode->is_dibuka && ! $periode->sedangDibuka()) · <b>Saklar dibuka, tetapi hari ini di luar rentang tanggal.</b>@endif</div></div></div>
    @else
        <div class="info-banner kuning">Belum ada periode. Isi formulir di bawah.</div>
    @endif
</div>

<form method="POST" action="{{ route('super.pengaturan.periode') }}" class="kartu mb-3">
    @csrf @method('PUT')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div class="kartu-judul">Periode Pendaftaran Sidang</div>
        @if ($periode)<button type="button" class="btn {{ $periode->is_dibuka ? 'btn-outline-danger' : 'btn-outline-primary' }} btn-sm" data-bs-toggle="modal" data-bs-target="#modalStatus">{{ $periode->is_dibuka ? 'Tutup Pendaftaran' : 'Buka Pendaftaran' }}</button>@endif</div>
    <div class="form-check form-switch mb-3"><input class="form-check-input" type="checkbox" role="switch" name="is_dibuka" value="1" id="swBuka" @checked(old('is_dibuka', $periode?->is_dibuka))><label class="form-check-label" for="swBuka">Pendaftaran dibuka</label></div>
    <div class="row g-3">
        <div class="col-12"><label class="form-label">Nama periode</label><input name="nama_periode" value="{{ old('nama_periode', $periode?->nama_periode) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Tanggal buka</label><input type="date" name="tanggal_buka" value="{{ old('tanggal_buka', $periode?->tanggal_buka?->format('Y-m-d')) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Tanggal tutup</label><input type="date" name="tanggal_tutup" value="{{ old('tanggal_tutup', $periode?->tanggal_tutup?->format('Y-m-d')) }}" class="form-control" required></div>
    </div>
    <div class="text-end mt-3"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Periode</button></div>
</form>

<div class="kartu mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3"><div class="kartu-judul">Dokumen Persyaratan</div>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDokumen" data-isi="{{ json_encode(['_aksi' => route('super.pengaturan.dokumen.store'), '_method' => 'POST', 'judul' => 'Tambah Dokumen Persyaratan', 'nama_dokumen' => '', 'keterangan' => '', 'wajib_skripsi' => true, 'wajib_kompre' => true, 'khusus_transfer' => false, 'format_file' => 'PDF', 'ukuran_maks_mb' => 2, 'urutan' => '']) }}">+ Tambah Dokumen</button></div>
    <div class="table-responsive"><table class="tabel">
        <thead><tr><th>Nama Dokumen</th><th>Wajib Skripsi</th><th>Wajib Komprehensif</th><th>Khusus Transfer</th><th>Format</th><th>Ukuran Maks.</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>@foreach ($dokumen as $d)<tr>
            <td><div class="fw-semibold">{{ $d->nama_dokumen }}</div><div class="small text-3">{{ $d->keterangan }}</div></td>
            @foreach (['wajib_skripsi', 'wajib_kompre', 'khusus_transfer'] as $k)<td>{!! $d->$k ? '<i class="bi bi-check2 text-primary"></i> Ya' : '<span class="text-3">Tidak</span>' !!}</td>@endforeach
            <td>{{ $d->format_file }}</td><td>{{ $d->ukuran_maks_mb }} MB</td>
            <td class="text-end text-nowrap"><button class="btn btn-link btn-sm" data-bs-toggle="modal" data-bs-target="#modalDokumen" data-isi="{{ json_encode(['_aksi' => route('super.pengaturan.dokumen.update', $d), '_method' => 'PUT', 'judul' => 'Ubah Dokumen Persyaratan'] + $d->only(['nama_dokumen', 'keterangan', 'wajib_skripsi', 'wajib_kompre', 'khusus_transfer', 'format_file', 'ukuran_maks_mb', 'urutan'])) }}">Ubah</button>
                <button class="btn btn-link btn-sm text-danger" data-bs-toggle="modal" data-bs-target="#modalHapusDok" data-isi="{{ json_encode(['_aksi' => route('super.pengaturan.dokumen.destroy', $d), 'nama' => $d->nama_dokumen]) }}">Hapus</button></td></tr>@endforeach</tbody>
    </table></div>
</div>

<div class="kartu mb-3">
    <div class="d-flex justify-content-between align-items-center mb-1"><div class="kartu-judul">Paket Materi Sidang Komprehensif</div>
        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMateri" data-isi="{{ json_encode(['_aksi' => route('super.pengaturan.materi.store'), '_method' => 'POST', 'judul' => 'Tambah Paket Materi', 'nama_paket' => '', 'mata_kuliah_1' => '', 'mata_kuliah_2' => '', 'mata_kuliah_3' => '']) }}">+ Tambah Paket Materi</button></div>
    <div class="kartu-sub mb-3">Setiap paket berisi 3 mata kuliah. Mahasiswa paket Komprehensif memilih salah satu paket.</div>
    <div class="d-grid gap-2">
        @forelse ($materi as $m)
            <div class="d-flex flex-wrap align-items-center gap-3 border rounded-3 p-3">
                <span class="status status-primary polos" style="background:var(--biru-muda);color:var(--navy)">{{ $m->nama_paket }}</span>
                <div class="d-flex flex-wrap gap-2 flex-fill">@foreach ($m->mataKuliah() as $mk)<span class="chip">{{ $mk }}</span>@endforeach</div>
                <div class="text-nowrap"><button class="btn btn-link btn-sm" data-bs-toggle="modal" data-bs-target="#modalMateri" data-isi="{{ json_encode(['_aksi' => route('super.pengaturan.materi.update', $m), '_method' => 'PUT', 'judul' => 'Ubah ' . $m->nama_paket] + $m->only(['nama_paket', 'mata_kuliah_1', 'mata_kuliah_2', 'mata_kuliah_3'])) }}">Ubah</button>
                    <button class="btn btn-link btn-sm text-danger" data-bs-toggle="modal" data-bs-target="#modalHapusMateri" data-isi="{{ json_encode(['_aksi' => route('super.pengaturan.materi.destroy', $m), 'nama' => $m->nama_paket, 'mk' => $m->teks()]) }}">Hapus</button></div>
            </div>
        @empty
            <x-kosong judul="Belum ada paket materi" ikon="bi-journal" />
        @endforelse
    </div>
    <div class="info-banner biru mt-3"><i class="bi bi-info-circle"></i><div>Paket ini tampil sebagai pilihan di formulir pengajuan mahasiswa (Langkah Data Sidang) saat memilih paket sidang <b>Komprehensif</b>.</div></div>
</div>

@if ($periode)
<x-konfirmasi id="modalStatus" :judul="$periode->is_dibuka ? 'Tutup pendaftaran sekarang?' : 'Buka pendaftaran sekarang?'" :aksi="route('super.pengaturan.status')" :tombol="$periode->is_dibuka ? 'Tutup Pendaftaran' : 'Buka Pendaftaran'" :bahaya="$periode->is_dibuka">
    {{ $periode->is_dibuka ? 'Mahasiswa tidak dapat mengirim pengajuan baru sampai dibuka kembali. Status pengajuan sebelumnya tetap dapat dilihat.' : 'Mahasiswa dapat mengirim pengajuan baru selama rentang tanggal periode.' }}
</x-konfirmasi>
@endif
<div class="modal fade" id="modalDokumen" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">@csrf<input type="hidden" name="_method" value="POST">
    <div class="modal-header"><div class="modal-title" data-teks="judul"></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nama dokumen <span class="wajib">*</span></label><input name="nama_dokumen" class="form-control" required maxlength="100" placeholder="Mis. Sertifikat Kursus"></div>
        <div class="mb-3"><label class="form-label">Petunjuk unggah</label><input name="keterangan" class="form-control" maxlength="255"></div>
        <div class="d-flex flex-wrap gap-3 mb-3">
            <label class="small"><input type="checkbox" name="wajib_skripsi" value="1" class="form-check-input me-1">Wajib untuk Skripsi</label>
            <label class="small"><input type="checkbox" name="wajib_kompre" value="1" class="form-check-input me-1">Wajib untuk Komprehensif</label>
            <label class="small"><input type="checkbox" name="khusus_transfer" value="1" class="form-check-input me-1">Khusus jalur Transfer</label></div>
        <div class="row g-2"><div class="col-5"><label class="form-label">Format</label><input name="format_file" class="form-control" required placeholder="PDF atau PDF, JPG, PNG"></div>
            <div class="col-4"><label class="form-label">Ukuran maks. (MB)</label><input type="number" name="ukuran_maks_mb" min="1" max="20" class="form-control" required></div>
            <div class="col-3"><label class="form-label">Urutan</label><input type="number" name="urutan" min="1" max="99" class="form-control"></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan Dokumen</button></div>
</form></div></div>
<div class="modal fade" id="modalMateri" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:520px"><form method="POST" class="modal-content">@csrf<input type="hidden" name="_method" value="POST">
    <div class="modal-header"><div><div class="modal-title" data-teks="judul"></div><div class="small text-3">Isi 3 mata kuliah untuk paket ini. Paket langsung muncul di pilihan mahasiswa.</div></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nama paket</label><input name="nama_paket" class="form-control" maxlength="50" placeholder="Otomatis: Paket N"></div>
        @for ($i = 1; $i <= 3; $i++)<div class="mb-3"><label class="form-label">Mata kuliah {{ $i }} <span class="wajib">*</span></label><input name="mata_kuliah_{{ $i }}" class="form-control" required maxlength="150" placeholder="Nama mata kuliah"></div>@endfor
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<div class="modal fade" id="modalHapusMateri" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:440px"><form method="POST" class="modal-content text-center">@csrf @method('DELETE')
    <div class="modal-body pt-4"><div class="ikon-bahaya"><i class="bi bi-trash3"></i></div><div class="modal-title">Hapus <span data-teks="nama"></span>?</div>
        <div class="bg-light border rounded p-2 small mt-3 text-start" data-teks="mk"></div><div class="small text-2 mt-2">Paket ini tidak akan muncul lagi di pilihan mahasiswa. Pengajuan lama yang memakainya tetap tersimpan.</div></div>
    <div class="d-flex gap-2 px-3 pb-3"><button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger flex-fill">Hapus</button></div>
</form></div></div>
<div class="modal fade" id="modalHapusDok" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:440px"><form method="POST" class="modal-content text-center">@csrf @method('DELETE')
    <div class="modal-body pt-4"><div class="ikon-bahaya"><i class="bi bi-trash3"></i></div><div class="modal-title">Hapus dokumen <span data-teks="nama"></span>?</div>
        <div class="small text-2 mt-2">Dokumen tidak lagi diminta di formulir pengajuan baru.</div></div>
    <div class="d-flex gap-2 px-3 pb-3"><button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger flex-fill">Hapus</button></div>
</form></div></div>
@endsection
