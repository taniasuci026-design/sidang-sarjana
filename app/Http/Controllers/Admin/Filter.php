<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pengajuan;
use Illuminate\Http\Request;

/** Penyaringan daftar pengajuan: status, paket, kategori, rentang tanggal, pencarian (FR-23). */
trait Filter
{
    protected function queryPengajuan(Request $request)
    {
        $f = $request->validate([
            'status' => ['nullable', 'in:menunggu,diproses,perbaikan,selesai'],
            'paket' => ['nullable', 'in:skripsi,kompre'],
            'kategori' => ['nullable', 'string', 'max:10'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
            'q' => ['nullable', 'string', 'max:100'],
        ], ['sampai.after_or_equal' => 'Tanggal "sampai" harus sama atau setelah tanggal "dari".']);
        $q = Pengajuan::query()->with(['mahasiswa.user', 'kategori', 'materi'])
            ->whereHas('mahasiswa', fn ($m) => $m->where('status_akun', '!=', 'arsip'));
        if (! empty($f['status'])) { $q->where('status_pengajuan', $f['status']); }
        if (! empty($f['paket'])) { $q->where('paket_sidang', $f['paket']); }
        if (! empty($f['kategori'])) {
            $f['kategori'] === 'belum' ? $q->whereNull('id_kategori') : $q->where('id_kategori', $f['kategori']);
        }
        if (! empty($f['dari'])) { $q->whereDate('diajukan_at', '>=', $f['dari']); }
        if (! empty($f['sampai'])) { $q->whereDate('diajukan_at', '<=', $f['sampai']); }
        if (! empty($f['q'])) {
            $kata = '%' . $f['q'] . '%';
            $q->where(fn ($w) => $w->where('no_pengajuan', 'like', $kata)
                ->orWhereHas('mahasiswa', fn ($m) => $m->where('nama_lengkap', 'like', $kata)->orWhere('npm', 'like', $kata)));
        }
        return [$q, $f];
    }
}
