<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriHasil;
use App\Models\Pengajuan;
use App\Services\AktivitasService;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index(Request $request)
    {
        $kategori = KategoriHasil::with('pengubah')->withCount(['pengajuan' => fn ($q) => $q->whereHas('mahasiswa', fn ($m) => $m->where('status_akun', '!=', 'arsip'))])->orderBy('kode')->get();
        $pilih = $request->query('k');
        $q = Pengajuan::with(['mahasiswa', 'kategori', 'dokumen.jenis'])->whereNotNull('id_kategori')
            ->whereHas('mahasiswa', fn ($m) => $m->where('status_akun', '!=', 'arsip'))
            ->when($pilih, fn ($w) => $w->where('id_kategori', $pilih))
            ->when($request->filled('q'), fn ($w) => $w->whereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', '%' . $request->q . '%')->orWhere('npm', 'like', '%' . $request->q . '%')))
            ->orderByDesc('updated_at');
        return view('admin.kategori', [
            'kategori' => $kategori, 'pilih' => $pilih, 'totalTerkategori' => $kategori->sum('pengajuan_count'),
            'pengajuan' => $q->paginate(10)->withQueryString(),
        ]);
    }

    /** Ubah teks notifikasi kategori (Super Admin). */
    public function update(Request $request, KategoriHasil $kategori)
    {
        $data = $request->validate(['teks_notifikasi' => ['required', 'string', 'max:500']], [], ['teks_notifikasi' => 'teks notifikasi']);
        $kategori->update($data + ['id_user' => $request->user()->id_user]);
        AktivitasService::catat('ubah teks notifikasi', ['keterangan' => "Teks notifikasi {$kategori->label()} ({$kategori->nama_kategori}) diubah."]);
        return back()->with('sukses', "Teks notifikasi {$kategori->label()} berhasil diubah.");
    }
}
