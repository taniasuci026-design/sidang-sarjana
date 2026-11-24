<?php

namespace App\Http\Controllers;

use App\Models\KategoriHasil;
use App\Models\KontenLanding;
use App\Models\Pengajuan;
use App\Models\PeriodePendaftaran;
use App\Services\NpmService;
use Illuminate\Http\Request;

class PublikController extends Controller
{
    /** UI-01 Landing Page (FR-01) — konten dikelola Super Admin. */
    public function landing()
    {
        return view('publik.landing', [
            'alur' => KontenLanding::where('bagian', 'alur')->where('is_tampil', true)->orderBy('urutan')->get(),
            'faq' => KontenLanding::where('bagian', 'faq')->where('is_tampil', true)->orderBy('urutan')->get(),
            'periode' => PeriodePendaftaran::aktif(),
            'kategori' => KategoriHasil::orderBy('kode')->get(),
        ]);
    }

    /** UI-02 Cek Status Pemberkasan tanpa login (FR-02); data pribadi tidak ditampilkan. */
    public function cekStatus(Request $request)
    {
        $data = $request->validate([
            'npm' => ['nullable', 'string', 'max:20'],
            'kategori' => ['nullable', 'exists:kategori_hasil,id_kategori'],
        ]);
        $hasil = collect();
        $dicari = filled($data['npm'] ?? null) || filled($data['kategori'] ?? null);
        if ($dicari) {
            $q = Pengajuan::query()->with(['kategori', 'mahasiswa'])
                ->whereHas('mahasiswa', fn ($m) => $m->where('status_akun', '!=', 'arsip'))
                ->orderByDesc('diajukan_at');
            if (filled($data['npm'] ?? null)) {
                $npm = preg_replace('/\D/', '', $data['npm']);
                $q->whereHas('mahasiswa', fn ($m) => $m->where('npm', $npm));
            }
            if (filled($data['kategori'] ?? null)) {
                $q->where('id_kategori', $data['kategori']);
            }
            $hasil = $q->limit(50)->get()->map(fn ($p) => [
                'npm' => NpmService::samarkan($p->mahasiswa->npm),
                'no' => $p->no_pengajuan,
                'paket' => $p->paketLabel(),
                'status' => $p->statusLabel(), 'warna' => $p->statusWarna(),
                'kategori' => $p->kategori,
                'diperbarui' => $p->updated_at,
            ]);
        }
        return view('publik.cek-status', [
            'hasil' => $hasil, 'dicari' => $dicari, 'input' => $data,
            'kategori' => KategoriHasil::orderBy('kode')->get(),
        ]);
    }
}
