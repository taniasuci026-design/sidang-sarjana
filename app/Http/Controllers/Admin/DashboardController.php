<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriHasil;
use App\Models\Pengajuan;
use App\Models\PeriodePendaftaran;
use App\Models\PermintaanHapusAkun;
use App\Models\RiwayatAktivitas;

/** UI-11 Dashboard Admin / Beranda Super Admin (FR-21). */
class DashboardController extends Controller
{
    public function index()
    {
        $dasar = Pengajuan::query()->whereHas('mahasiswa', fn ($m) => $m->where('status_akun', '!=', 'arsip'));
        $perStatus = (clone $dasar)->selectRaw('status_pengajuan, count(*) n')->groupBy('status_pengajuan')->pluck('n', 'status_pengajuan');
        $perKategori = (clone $dasar)->selectRaw('id_kategori, count(*) n')->groupBy('id_kategori')->pluck('n', 'id_kategori');
        return view('admin.dashboard', [
            'stat' => ['total' => $perStatus->sum()] + collect(Pengajuan::STATUS)->map(fn ($l, $k) => $perStatus[$k] ?? 0)->all(),
            'kategori' => KategoriHasil::orderBy('kode')->get()->map(fn ($k) => [$k, $perKategori[$k->id_kategori] ?? 0]),
            'antrean' => (clone $dasar)->with('mahasiswa')->whereIn('status_pengajuan', ['menunggu', 'diproses'])->orderBy('diajukan_at')->limit(6)->get(),
            'aktivitas' => RiwayatAktivitas::with(['pelaku', 'pengajuan'])->whereNotNull('id_pengajuan')->latest('id_riwayat')->limit(8)->get(),
            'periode' => PeriodePendaftaran::aktif(),
            'laporanMenunggu' => PermintaanHapusAkun::where('status_permintaan', 'menunggu')->count(),
            'hariIni' => (clone $dasar)->whereDate('diajukan_at', today())->count(),
        ]);
    }
}
