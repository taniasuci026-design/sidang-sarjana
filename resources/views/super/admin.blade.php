@extends('layouts.portal')
@section('title', 'Manajemen Admin')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div><h1 class="judul-halaman">Manajemen Admin</h1><div class="sub-halaman">Tambah, ubah, nonaktifkan, atau hapus akun admin dan superadmin.</div></div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdmin" data-isi="{{ json_encode(['_aksi' => route('super.admin.store'), '_method' => 'POST', 'nama' => '', 'email' => '', 'role' => 'admin', 'judul' => 'Tambah Admin']) }}"><i class="bi bi-plus-lg me-1"></i>Tambah Admin</button>
</div>
<form method="GET" class="kartu mb-3"><div class="row g-2">
    <div class="col-md-6"><div class="input-ikon"><i class="bi bi-search"></i><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Cari nama atau email"></div></div>
    <div class="col-6 col-md-2"><select name="peran" class="form-select"><option value="">Semua peran</option><option value="admin" @selected(request('peran')==='admin')>Admin</option><option value="super_admin" @selected(request('peran')==='super_admin')>Superadmin</option></select></div>
    <div class="col-6 col-md-2"><select name="status" class="form-select"><option value="">Semua status</option><option value="aktif" @selected(request('status')==='aktif')>Aktif</option><option value="nonaktif" @selected(request('status')==='nonaktif')>Nonaktif</option></select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Cari</button></div>
</div></form>
<div class="kartu">
    @if ($admin->isEmpty())<x-kosong judul="Tidak ada akun" ikon="bi-people" />@else
    <div class="table-responsive"><table class="tabel">
        <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Terakhir Masuk</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>@foreach ($admin as $a)<tr>
            <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm">{{ $a->inisial() }}</span><span class="nama">{{ $a->nama }}</span>@if($a->id_user === auth()->id())<span class="small text-3">(Anda)</span>@endif</div></td>
            <td>{{ $a->email }}</td><td><x-status :warna="$a->isSuperAdmin() ? 'indigo' : 'primary'" polos>{{ $a->roleLabel() }}</x-status></td>
            <td><x-status :warna="$a->is_active ? 'success' : 'secondary'">{{ $a->is_active ? 'Aktif' : 'Nonaktif' }}</x-status></td>
            <td class="text-3">{{ $a->last_login_at?->translatedFormat('d M Y, H:i') ?? 'Belum pernah' }}</td>
            <td class="text-end text-nowrap">
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAdmin" data-isi="{{ json_encode(['_aksi' => route('super.admin.update', $a), '_method' => 'PUT', 'nama' => $a->nama, 'email' => $a->email, 'role' => $a->role, 'judul' => 'Ubah Admin']) }}">Ubah</button>
                @if ($a->id_user !== auth()->id())
                    <form method="POST" action="{{ route('super.admin.toggle', $a) }}" class="d-inline">@csrf<button class="btn btn-light border btn-sm">{{ $a->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalHapus" data-isi="{{ json_encode(['_aksi' => route('super.admin.destroy', $a), 'nama' => $a->nama]) }}">Hapus</button>
                @endif
            </td></tr>@endforeach</tbody>
    </table></div>
    <div class="mt-3">{{ $admin->links('pagination::bootstrap-5') }}</div>@endif
</div>
<div class="modal fade" id="modalAdmin" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form method="POST" class="modal-content">@csrf
    <input type="hidden" name="_method" value="POST">
    <div class="modal-header"><div class="modal-title" data-teks="judul">Tambah Admin</div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Nama <span class="wajib">*</span></label><input name="nama" class="form-control" required maxlength="100"></div>
        <div class="mb-3"><label class="form-label">Email <span class="wajib">*</span></label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Peran <span class="wajib">*</span></label><select name="role" class="form-select"><option value="admin">Admin</option><option value="super_admin">Superadmin</option></select></div>
        <div class="row g-2"><div class="col-md-6"><label class="form-label">Kata sandi</label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
            <div class="col-md-6"><label class="form-label">Ulangi kata sandi</label><input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"></div></div>
        <div class="form-text">Wajib untuk akun baru (min. 8 karakter, huruf & angka). Saat mengubah, kosongkan jika tidak diganti.</div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<div class="modal fade" id="modalHapus" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:420px"><form method="POST" class="modal-content text-center">@csrf @method('DELETE')
    <div class="modal-body pt-4"><div class="ikon-bahaya"><i class="bi bi-trash3"></i></div><div class="modal-title">Hapus akun <span data-teks="nama"></span>?</div>
        <div class="small text-2 mt-1">Akun yang sudah memiliki riwayat pemeriksaan tidak dapat dihapus; gunakan Nonaktifkan.</div></div>
    <div class="d-flex gap-2 px-3 pb-3"><button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger flex-fill">Hapus</button></div>
</form></div></div>
@endsection
