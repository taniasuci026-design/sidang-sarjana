<?php

namespace App\Http\Controllers;

use App\Models\DokumenPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Berkas disimpan di disk privat dan hanya dapat dibuka lewat controller yang memeriksa hak akses (SDD Tabel 2 no. 7). */
class BerkasController extends Controller
{
    public function lihat(Request $request, DokumenPengajuan $dokumen)
    {
        $u = $request->user();
        $milik = $u->isMahasiswa() && $dokumen->pengajuan->mahasiswa?->id_user === $u->id_user;
        abort_unless($u->isStaf() || $milik, 403);
        abort_unless(Storage::disk('local')->exists($dokumen->path_file), 404, 'Berkas tidak ditemukan.');
        $mode = $request->boolean('unduh') ? 'attachment' : 'inline';
        return Storage::disk('local')->response($dokumen->path_file, $dokumen->nama_file, [
            'Content-Disposition' => $mode . '; filename="' . addslashes($dokumen->nama_file) . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
