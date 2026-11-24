<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\DokumenPengajuan;
use App\Models\PeriodePendaftaran;
use App\Services\PengajuanService;
use Illuminate\Http\Request;

/** UI-05 Dashboard Mahasiswa (FR-08). */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $mhs = $request->user()->mahasiswa;
        $pengajuan = $mhs ? $mhs->pengajuan()->with('kategori')->orderByDesc('id_pengajuan')->get() : collect();
        $hitung = fn ($s) => $pengajuan->where('status_pengajuan', $s)->count();
        $perbaikan = $mhs ? DokumenPengajuan::with(['jenis', 'pengajuan'])
            ->whereHas('pengajuan', fn ($q) => $q->where('id_mahasiswa', $mhs->id_mahasiswa)->where('status_pengajuan', 'perbaikan'))
            ->where('status_dokumen', 'perlu_perbaikan')->get() : collect();
        $periode = PeriodePendaftaran::aktif();
        return view('mahasiswa.dashboard', [
            'pengajuan' => $pengajuan,
            'stat' => ['total' => $pengajuan->count(), 'menunggu' => $hitung('menunggu'), 'diproses' => $hitung('diproses'), 'perbaikan' => $hitung('perbaikan'), 'selesai' => $hitung('selesai')],
            'perbaikan' => $perbaikan,
            'bisaAjukan' => $periode?->sedangDibuka() && count(PengajuanService::paketTersedia($mhs)) > 0,
            'periode' => $periode,
        ]);
    }
}
