@extends('layouts.portal')
@section('title', 'Detail Akun Mahasiswa')
@section('content')
@php $lap = $m->laporanAktif; $warnaAkun = ['aktif' => 'success', 'dilaporkan' => 'warning', 'arsip' => 'secondary'][$m->status_akun]; @endphp
<a href="{{ route('super.mahasiswa.index', ['tab' => $m->status_akun === 'arsip' ? 'arsip' : 'semua']) }}" class="tautan-kembali"><i class="bi bi-arrow-left"></i> Kembali ke Data Mahasiswa</a>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2 mb-3">
    <div class="d-flex align-items-center gap-2"><h1 class="judul-halaman mb-0">Detail Akun Mahasiswa</h1><x-status :warna="$warnaAkun" polos>{{ ucfirst($m->status_akun) }}</x-status></div>
    @if ($m->status_akun === 'dilaporkan')
        <div class="d-flex gap-2"><button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTolak">Tolak Laporan</button><button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus">Hapus Akun</button></div>
    @endif
</div>
<div class="row g-3 mb-3">
    <div class="col-lg-6"><div class="kartu h-100">
        <div class="d-flex justify-content-between align-items-center mb-3"><div class="kartu-judul">Data Akun</div><span class="status status-secondary polos"><i class="bi bi-lock"></i> Hanya lihat</span></div>
        <dl class="kv mb-0" style="grid-template-columns:130px 1fr">
            <dt>Nama</dt><dd>{{ $m->nama_lengkap }}</dd><dt>NPM</dt><dd>{{ $m->npm }}</dd><dt>Email</dt><dd>{{ $m->user?->email }}</dd>
            <dt>WhatsApp</dt><dd>{{ $m->user?->no_whatsapp }}</dd><dt>Program Studi</dt><dd>{{ $m->program_studi ?? '-' }}</dd><dt>Terdaftar</dt><dd>{{ $m->user?->created_at?->translatedFormat('d M Y') }}</dd>
            @if ($m->diarsipkan_at)<dt>Diarsipkan</dt><dd>{{ $m->diarsipkan_at->translatedFormat('d M Y, H:i') }}</dd>@endif
        </dl></div></div>
    <div class="col-lg-6">
        @foreach ($m->permintaanHapus as $l)
            <div class="kartu mb-3" style="{{ $l->status_permintaan === 'menunggu' ? 'background:#FFFBEB;border-color:#FDE68A' : '' }}">
                <div class="d-flex justify-content-between"><div class="kartu-judul" style="{{ $l->status_permintaan === 'menunggu' ? 'color:#92400E' : '' }}">Laporan dari Admin</div>
                    <x-status :warna="['menunggu' => 'warning', 'disetujui' => 'danger', 'ditolak' => 'primary'][$l->status_permintaan]" polos>{{ ['menunggu' => 'Menunggu', 'disetujui' => 'Dihapus', 'ditolak' => 'Ditolak'][$l->status_permintaan] }}</x-status></div>
                <dl class="kv mt-2 mb-0" style="grid-template-columns:130px 1fr"><dt>Dilaporkan oleh</dt><dd>{{ $l->pengaju?->nama }}</dd><dt>Tanggal</dt><dd>{{ $l->diajukan_at?->translatedFormat('d M Y, H:i') }}</dd><dt>Alasan</dt><dd>{{ $l->alasan }}</dd>
                    @if ($l->peninjau)<dt>Ditinjau</dt><dd>{{ $l->peninjau->nama }} · {{ $l->ditinjau_at?->translatedFormat('d M Y, H:i') }}</dd><dt>Catatan</dt><dd>{{ $l->catatan_peninjau }}</dd>@endif</dl>
            </div>
        @endforeach
        <div class="kartu"><div class="kartu-judul mb-2">Pengajuan</div>
            <table class="tabel"><thead><tr><th>No. Pengajuan</th><th>Tanggal</th><th>Status</th><th></th></tr></thead><tbody>
            @forelse ($m->pengajuan as $p)<tr><td class="fw-semibold">{{ $p->no_pengajuan }}</td><td>{{ $p->diajukan_at?->translatedFormat('d M Y') }}</td><td><x-pengajuan-status :p="$p" /></td>
                <td>@if($m->status_akun !== 'arsip')<a href="{{ route('admin.pengajuan.show', $p) }}" class="small">Lihat</a>@endif</td></tr>
            @empty<tr><td colspan="4" class="text-3">Belum ada pengajuan.</td></tr>@endforelse</tbody></table></div>
    </div>
</div>
<div class="kartu"><div class="kartu-judul mb-2">Riwayat Akun</div>
    <table class="tabel"><thead><tr><th>Waktu</th><th>Aksi</th><th>Oleh</th><th>Keterangan</th></tr></thead><tbody>
    @forelse ($m->riwayat as $r)<tr><td class="text-nowrap">{{ $r->created_at?->translatedFormat('d M Y, H:i') }}</td><td>{{ ucfirst($r->aksi) }}</td><td>{{ $r->pelaku?->nama }} ({{ $r->pelaku?->roleLabel() }})</td><td class="small text-2">{{ $r->keterangan }}</td></tr>
    @empty<tr><td colspan="4" class="text-3">Belum ada riwayat.</td></tr>@endforelse</tbody></table></div>

@if ($m->status_akun === 'dilaporkan')
<div class="modal fade" id="modalHapus" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:500px"><form method="POST" action="{{ route('super.mahasiswa.hapus', $m) }}" class="modal-content">@csrf
    <div class="modal-body pt-4"><div class="ikon-bahaya" style="margin:0 0 12px"><i class="bi bi-trash3"></i></div>
        <div class="modal-title">Hapus akun {{ $m->nama_lengkap }}?</div>
        <div class="small text-2 mt-1">Mahasiswa tidak bisa masuk lagi. Data akun dan pengajuannya dipindah ke tab <b>Arsip</b> sebagai laporan, tidak hilang.</div>
        <div class="bg-light border rounded p-2 small mt-3"><div>NPM: <b>{{ $m->npm }}</b></div><div>Dilaporkan oleh: <b>{{ $lap?->pengaju?->nama }}</b></div><div>Alasan admin: <b>{{ $lap?->alasan }}</b></div></div>
        <label class="form-label mt-3">Catatan Superadmin <span class="wajib">*</span></label><textarea name="catatan" class="form-control" rows="3" required maxlength="1000"></textarea>
        <label class="d-flex gap-2 small mt-3"><input type="checkbox" name="yakin" value="1" class="form-check-input" required> Saya sudah memeriksa laporan admin dan yakin akun ini perlu dihapus.</label></div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger">Hapus dan Arsipkan</button></div>
</form></div></div>
<div class="modal fade" id="modalTolak" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{ route('super.mahasiswa.tolak', $m) }}" class="modal-content">@csrf
    <div class="modal-header"><div class="modal-title">Tolak permintaan hapus akun</div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body"><div class="small text-2 mb-2">Akun akan kembali berstatus Aktif. Admin pelapor menerima alasan penolakan.</div>
        <label class="form-label">Alasan penolakan <span class="wajib">*</span></label><textarea name="catatan" class="form-control" rows="3" required maxlength="1000" placeholder="Contoh: Akun masih memiliki pengajuan aktif."></textarea></div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Tolak Laporan</button></div>
</form></div></div>
@if (request('hapus'))@push('scripts')<script>bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHapus')).show();</script>@endpush @endif
@endif
@endsection
