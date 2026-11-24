<?php

namespace App\Services;

use App\Models\DokumenPengajuan;
use App\Models\JenisDokumen;
use App\Models\Mahasiswa;
use App\Models\Pengajuan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PengajuanService
{
    /** Paket yang masih boleh diajukan mahasiswa: maks. dua pengajuan, skripsi lalu kompre. */
    public static function paketTersedia(?Mahasiswa $mhs): array
    {
        if (! $mhs) {
            return ['skripsi', 'kompre'];
        }
        $sudah = $mhs->pengajuan()->pluck('paket_sidang')->all();
        if (count($sudah) >= config('sidang.maks_pengajuan') || in_array('kompre', $sudah, true)) {
            return [];
        }
        return in_array('skripsi', $sudah, true) ? ['kompre'] : ['skripsi', 'kompre'];
    }

    public static function aturanBerkas(JenisDokumen $j, bool $wajib = true): array
    {
        return [$wajib ? 'required' : 'nullable', 'file', 'mimes:' . implode(',', $j->ekstensi()), 'max:' . ($j->ukuran_maks_mb * 1024)];
    }

    public static function simpanBerkas(Pengajuan $p, JenisDokumen $j, UploadedFile $file): DokumenPengajuan
    {
        $lama = DokumenPengajuan::where('id_pengajuan', $p->id_pengajuan)->where('id_jenis', $j->id_jenis)->first();
        $namaAman = Str::slug($j->nama_dokumen) . '-' . now()->format('YmdHis') . '-' . Str::random(6) . '.' . strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('pengajuan/' . $p->no_pengajuan, $namaAman, 'local');
        if ($lama && $lama->path_file !== $path) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($lama->path_file);
        }
        return DokumenPengajuan::updateOrCreate(
            ['id_pengajuan' => $p->id_pengajuan, 'id_jenis' => $j->id_jenis],
            ['nama_file' => mb_substr($file->getClientOriginalName(), 0, 250), 'path_file' => $path, 'ukuran_kb' => (int) ceil($file->getSize() / 1024),
             'status_dokumen' => 'menunggu', 'catatan_pemeriksaan' => null, 'uploaded_at' => now()]
        );
    }

    public static function namaFolder(Pengajuan $p): string
    {
        return $p->no_pengajuan . '_' . $p->mahasiswa->npm . '_' . Str::slug($p->mahasiswa->nama_lengkap, '_');
    }

    public static function aktor(): User
    {
        return auth()->user();
    }
}
