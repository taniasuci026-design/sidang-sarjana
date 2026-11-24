@extends('layouts.portal')
@section('title', 'Pemeriksaan ' . $p->no_pengajuan)
@section('tanpa-galat-umum', true)
@section('content')
@php
    $super = auth()->user()->isSuperAdmin();
    $dok = $p->dokumen->sortBy('jenis.urutan');
    $menungguLaporan = $laporan && $laporan->status_permintaan === 'menunggu';
    $teksKategori = $kategori->mapWithKeys(fn ($k) => [$k->id_kategori => ['nama' => $k->nama_kategori, 'teks' => $k->teks_notifikasi, 'kode' => $k->kode]]);
@endphp
<a href="{{ route('admin.pengajuan.index') }}" class="tautan-kembali"><i class="bi bi-arrow-left"></i> Kembali ke Daftar Pengajuan</a>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mt-2 mb-3">
    <div><h1 class="judul-halaman">Pemeriksaan Berkas</h1>
        <div class="d-flex gap-2 align-items-center"><span class="font-monospace fw-bold text-2">{{ $p->no_pengajuan }}</span><x-pengajuan-status :p="$p" /></div></div>
    <a href="{{ route('admin.pengajuan.zip', $p) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-zip me-1"></i>Unduh ZIP</a>
</div>

@if ($errors->any())
    <div class="info-banner merah mb-3"><i class="bi bi-exclamation-circle"></i><div><b>Hasil pemeriksaan belum tersimpan.</b><ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div></div>
@endif

{{-- Hasil Verifikasi --}}
<div class="kartu mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center">
        <div><div class="kartu-judul">Hasil Verifikasi</div>
            <div class="kartu-sub">Pastikan identitas dan dokumen mahasiswa sesuai sebelum melakukan verifikasi. Jika terdapat ketidaksesuaian, laporkan akun beserta alasannya ke Superadmin.</div></div>
        <div class="d-flex gap-2">
            @if ($p->mahasiswa->status_akun === 'dilaporkan')
                <span class="status status-warning polos">Akun dilaporkan · menunggu Superadmin</span>
                @if ($super)<a href="{{ route('super.mahasiswa.show', $p->mahasiswa) }}" class="btn btn-danger btn-sm">Tinjau Laporan</a>@endif
            @else
                <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalLapor">Hapus Akun</button>
            @endif
            @if ($p->status_verifikasi === 'terverifikasi')
                <span class="status status-success polos"><i class="bi bi-patch-check"></i> Terverifikasi</span>
            @else
                <form method="POST" action="{{ route('admin.pengajuan.verifikasi', $p) }}">@csrf<button class="btn btn-primary">Verifikasi</button></form>
            @endif
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="kartu h-100">
            <div class="kartu-judul mb-3">Data Pengajuan</div>
            <dl class="kv mb-0" style="grid-template-columns:140px 1fr">
                <dt>Nama Mahasiswa</dt><dd>: {{ $p->mahasiswa->nama_lengkap }}</dd>
                <dt>NPM</dt><dd>: {{ $p->mahasiswa->npm }}</dd>
                <dt>Email</dt><dd>: {{ $p->mahasiswa->user->email }}</dd>
                <dt>WhatsApp</dt><dd>: {{ $p->mahasiswa->user->no_whatsapp }}</dd>
                <dt>Jalur</dt><dd>: {{ $p->jalurLabel() }}</dd>
                <dt>Paket Sidang</dt><dd>: {{ $p->paketLabel() }}</dd>
                <dt>Dosen Pembimbing PI</dt><dd>: {{ $p->dosen_pembimbing_pi ?: '-' }}</dd>
                @if ($p->materi)<dt>Materi Sidang Kompre</dt><dd>: {{ $p->materi->teks() }}</dd>@endif
                <dt>Tanggal Pengajuan</dt><dd>: {{ $p->diajukan_at?->translatedFormat('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="kartu h-100">
            <div class="kartu-judul mb-3">Dokumen</div>
            <table class="tabel"><thead><tr><th>No</th><th>Nama Dokumen</th><th>Diunggah</th><th>Status</th><th class="text-center">Aksi</th></tr></thead><tbody>
                @foreach ($dok as $i => $d)<tr>
                    <td class="text-oranye">{{ $loop->iteration }}</td><td>{{ $d->jenis->nama_dokumen }}<div class="text-3" style="font-size:11px">{{ $d->nama_file }} · {{ $d->ukuran_kb }} KB</div></td>
                    <td class="small">{{ $d->uploaded_at?->translatedFormat('d M Y H:i') }}</td>
                    <td><x-status :warna="$d->statusWarna()">{{ $d->statusLabel() }}</x-status></td>
                    <td class="text-center"><button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDok" data-src="{{ route('berkas.lihat', $d) }}" data-judul="{{ $d->jenis->nama_dokumen }} · {{ $p->mahasiswa->nama_lengkap }}" data-unduh="{{ route('berkas.lihat', [$d, 'unduh' => 1]) }}">Lihat</button></td></tr>@endforeach
            </tbody></table>
        </div>
    </div>
</div>

{{-- Hasil Pemeriksaan --}}
<form method="POST" action="{{ route('admin.pengajuan.periksa', $p) }}" class="kartu mb-3" id="formPeriksa">
    @csrf @method('PUT')
    <div class="kartu-judul mb-1">Hasil Pemeriksaan</div>
    <div class="kartu-sub mb-3">Tetapkan status tiap dokumen, status pengajuan, dan kategori hasil.@if($p->adminTerakhir) Terakhir diperiksa oleh <b>{{ $p->adminTerakhir->nama }}</b> ({{ $p->adminTerakhir->roleLabel() }}) · {{ $p->updated_at?->translatedFormat('d M Y, H:i') }}.@endif</div>
    <div class="table-responsive mb-3"><table class="tabel">
        <thead><tr><th style="width:200px">Dokumen</th><th style="width:200px">Status Dokumen</th><th>Alasan perbaikan (tampil ke mahasiswa)</th></tr></thead><tbody>
        @foreach ($dok as $d)<tr>
            <td class="fw-semibold">{{ $d->jenis->nama_dokumen }}</td>
            <td><select name="dokumen[{{ $d->id_dokumen }}][status]" class="form-select form-select-sm pilih-dok">
                @foreach (\App\Models\DokumenPengajuan::STATUS as $k => $l)<option value="{{ $k }}" @selected(old("dokumen.{$d->id_dokumen}.status", $d->status_dokumen) === $k)>{{ $l }}</option>@endforeach</select></td>
            <td><input name="dokumen[{{ $d->id_dokumen }}][catatan]" value="{{ old("dokumen.{$d->id_dokumen}.catatan", $d->catatan_pemeriksaan) }}" class="form-control form-control-sm @error("dokumen.{$d->id_dokumen}.catatan") is-invalid @enderror" placeholder="Contoh: Scan dokumen buram, unggah ulang yang terbaca jelas."></td>
        </tr>@endforeach
    </tbody></table></div>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Status Pengajuan</label>
            <select name="status_pengajuan" class="form-select @error('status_pengajuan') is-invalid @enderror" id="pilihStatus">
                @foreach (\App\Models\Pengajuan::STATUS as $k => $l)<option value="{{ $k }}" @selected(old('status_pengajuan', $p->status_pengajuan) === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Kategori Hasil</label>
            <select name="id_kategori" class="form-select @error('id_kategori') is-invalid @enderror" id="pilihKategori"><option value="">- Pilih Kategori -</option>
                @foreach ($kategori as $k)<option value="{{ $k->id_kategori }}" @selected(old('id_kategori', $p->id_kategori) == $k->id_kategori)>{{ $k->label() }} · {{ $k->nama_kategori }}</option>@endforeach</select></div>
        <div class="col-12"><label class="form-label">Pratinjau notifikasi untuk mahasiswa</label>
            <div class="info-banner biru" id="pratinjau"><i class="bi bi-circle-fill" style="font-size:8px;margin-top:6px"></i><div><b id="prJudul"></b><div id="prIsi"></div></div></div></div>
        <div class="col-md-6"><label class="form-label">Catatan untuk Mahasiswa <span class="text-3 fw-normal">(terlihat mahasiswa)</span></label>
            <textarea name="catatan_mahasiswa" rows="3" class="form-control" placeholder="Catatan yang akan dilihat mahasiswa…">{{ old('catatan_mahasiswa') }}</textarea></div>
        <div class="col-md-6"><label class="form-label">Catatan Internal <span class="text-3 fw-normal">(hanya admin)</span></label>
            <textarea name="catatan_internal" rows="3" class="form-control" placeholder="Catatan internal operator…">{{ old('catatan_internal') }}</textarea></div>
    </div>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
        <a href="{{ route('admin.pengajuan.riwayat', $p) }}" class="fw-semibold small text-decoration-underline">Riwayat Perubahan</a>
        <button class="btn btn-primary px-4">Submit</button>
    </div>
</form>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="kartu h-100">
            <div class="kartu-judul mb-3">Catatan Pemeriksaan</div>
            @forelse ($p->catatan as $c)
                <div class="border-bottom py-2">
                    <div class="d-flex justify-content-between"><b class="small text-navy">{{ $c->penulis?->nama }}</b>
                        <x-status :warna="$c->terlihat_mahasiswa ? 'primary' : 'secondary'" polos>{{ $c->terlihat_mahasiswa ? 'Terlihat mahasiswa' : 'Internal' }}</x-status></div>
                    <div class="small">{{ $c->isi_catatan }}</div><div class="text-3" style="font-size:11px">{{ $c->created_at?->translatedFormat('d M Y, H:i') }}</div>
                </div>
            @empty <div class="small text-3">Belum ada catatan.</div> @endforelse
        </div>
    </div>
    <div class="col-lg-5">
        <div class="kartu h-100">
            <div class="d-flex justify-content-between align-items-center mb-2"><div class="kartu-judul">Tanggapan Superadmin</div>
                @if ($laporan)<x-status :warna="['menunggu' => 'secondary', 'disetujui' => 'danger', 'ditolak' => 'primary'][$laporan->status_permintaan]" polos>{{ ['menunggu' => 'Menunggu tanggapan', 'disetujui' => 'Akun dihapus', 'ditolak' => 'Permintaan hapus ditolak'][$laporan->status_permintaan] }}</x-status>@endif</div>
            @if (! $laporan)
                <div class="small text-3">Belum ada laporan akun untuk mahasiswa ini.</div>
            @else
                <div class="small text-2">Dilaporkan oleh <b>{{ $laporan->pengaju?->nama }}</b> · {{ $laporan->diajukan_at?->translatedFormat('d M Y, H:i') }}<br>Alasan: {{ $laporan->alasan }}</div>
                @if ($laporan->peninjau)
                    <div class="d-flex gap-2 align-items-center mt-3"><span class="avatar avatar-sm">{{ $laporan->peninjau->inisial() }}</span><div><b class="small">{{ $laporan->peninjau->nama }}</b><div class="text-3" style="font-size:11px">Superadmin · {{ $laporan->ditinjau_at?->translatedFormat('d M Y, H:i') }}</div></div></div>
                    <div class="bg-light border rounded p-2 small mt-2">{{ $laporan->catatan_peninjau ?: '-' }}</div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Modal lihat dokumen (pratinjau semua halaman dengan bingkai scroll) --}}
<div class="modal fade" id="modalDok" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><div class="modal-title" id="dokJudul">Dokumen</div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="bingkai-dokumen"><iframe id="dokFrame" title="Pratinjau dokumen"></iframe></div></div>
    <div class="modal-footer"><a href="#" id="dokUnduh" class="btn btn-outline-primary"><i class="bi bi-download me-1"></i>Unduh</a><a href="#" id="dokTab" target="_blank" class="btn btn-light border">Buka di tab baru</a><button class="btn btn-primary" data-bs-dismiss="modal">Tutup</button></div>
</div></div></div>

{{-- Modal laporkan akun --}}
<div class="modal fade" id="modalLapor" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:500px">
    <form method="POST" action="{{ route('admin.pengajuan.laporkan', $p) }}" class="modal-content">@csrf
        <div class="modal-body pt-4"><div class="ikon-bahaya" style="margin:0 0 12px"><i class="bi bi-flag"></i></div>
            <div class="modal-title">Ajukan hapus akun {{ $p->mahasiswa->nama_lengkap }}?</div>
            <div class="small text-2 mt-1 mb-3">Akun akan berstatus <b>Dilaporkan</b> dan Superadmin akan meninjau permintaan ini.</div>
            <label class="form-label">Alasan <span class="wajib">*</span></label>
            <textarea name="alasan" class="form-control" rows="3" required maxlength="500" placeholder="Contoh: Mengunggah foto, bukan KRS"></textarea></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Kirim Laporan</button></div>
    </form>
</div></div>
@endsection
@push('scripts')
<script>
const teksKategori = @json($teksKategori);
const pesanStatus = {menunggu: ['Dalam antrean verifikasi', 'Pengajuan Anda berada dalam antrean verifikasi.'], diproses: ['Sedang diperiksa', 'Pengajuan Anda sedang diperiksa admin.']};
function pratinjau() {
  const s = document.getElementById('pilihStatus').value, k = teksKategori[document.getElementById('pilihKategori').value];
  let [j, i] = pesanStatus[s] || (k ? [k.nama, k.teks] : ['Pilih kategori hasil', 'Teks notifikasi mengikuti kategori yang dipilih.']);
  document.getElementById('prJudul').textContent = j; document.getElementById('prIsi').textContent = i;
  document.getElementById('pratinjau').className = 'info-banner ' + ({perbaikan: 'merah', selesai: 'hijau', menunggu: 'kuning'}[s] || 'biru');
}
['pilihStatus', 'pilihKategori'].forEach(id => document.getElementById(id).addEventListener('change', pratinjau));
document.querySelectorAll('.pilih-dok').forEach(s => s.addEventListener('change', () => {
  if ([...document.querySelectorAll('.pilih-dok')].some(x => x.value === 'perlu_perbaikan')) document.getElementById('pilihStatus').value = 'perbaikan';
  pratinjau();
}));
pratinjau();
document.getElementById('modalDok').addEventListener('show.bs.modal', e => {
  const b = e.relatedTarget; document.getElementById('dokFrame').src = b.dataset.src; document.getElementById('dokJudul').textContent = b.dataset.judul;
  document.getElementById('dokUnduh').href = b.dataset.unduh; document.getElementById('dokTab').href = b.dataset.src;
});
document.getElementById('modalDok').addEventListener('hidden.bs.modal', () => document.getElementById('dokFrame').src = 'about:blank');
</script>
@endpush
