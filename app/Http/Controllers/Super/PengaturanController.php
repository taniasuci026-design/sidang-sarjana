<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\JenisDokumen;
use App\Models\MateriKompre;
use App\Models\PeriodePendaftaran;
use App\Services\AktivitasService;
use Illuminate\Http\Request;

/** UI-15 Pengaturan Sistem: periode buka/tutup, dokumen persyaratan, paket materi kompre (FR-34 – FR-36). */
class PengaturanController extends Controller
{
    public function index()
    {
        return view('super.pengaturan', [
            'periode' => PeriodePendaftaran::with('pengubah')->orderByDesc('id_periode')->first(),
            'dokumen' => JenisDokumen::withCount('dokumen')->orderBy('urutan')->get(),
            'materi' => MateriKompre::orderBy('nama_paket')->get(),
        ]);
    }

    public function periode(Request $request)
    {
        $data = $request->validate([
            'nama_periode' => ['required', 'string', 'max:100'],
            'tanggal_buka' => ['required', 'date'],
            'tanggal_tutup' => ['required', 'date', 'after_or_equal:tanggal_buka'],
            'is_dibuka' => ['nullable', 'boolean'],
        ], ['tanggal_tutup.after_or_equal' => 'Tanggal tutup harus sama atau setelah tanggal buka.']);
        $data['is_dibuka'] = $request->boolean('is_dibuka');
        $p = PeriodePendaftaran::aktif() ?? new PeriodePendaftaran();
        $lama = $p->exists ? ($p->is_dibuka ? 'dibuka' : 'ditutup') : null;
        $p->fill($data + ['id_user' => $request->user()->id_user])->save();
        AktivitasService::catat('ubah periode', ['status_lama' => $lama, 'status_baru' => $p->is_dibuka ? 'dibuka' : 'ditutup',
            'keterangan' => "{$p->nama_periode} ({$p->tanggal_buka->format('d/m/Y')} – {$p->tanggal_tutup->format('d/m/Y')})"]);
        return back()->with('sukses', 'Periode pendaftaran berhasil disimpan.');
    }

    public function statusPeriode(Request $request)
    {
        $p = PeriodePendaftaran::aktif();
        abort_unless($p, 404);
        $p->update(['is_dibuka' => ! $p->is_dibuka, 'id_user' => $request->user()->id_user]);
        $s = $p->is_dibuka ? 'dibuka' : 'ditutup';
        AktivitasService::catat($p->is_dibuka ? 'buka pendaftaran' : 'tutup pendaftaran', ['status_lama' => $p->is_dibuka ? 'ditutup' : 'dibuka', 'status_baru' => $s, 'keterangan' => $p->nama_periode]);
        AktivitasService::notifStaf('periode', 'Pendaftaran ' . $s, "{$p->nama_periode} {$s} oleh {$request->user()->nama}.");
        return back()->with('sukses', "Pendaftaran berhasil {$s}.");
    }

    private function aturanDokumen(): array
    {
        return [
            'nama_dokumen' => ['required', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:255'],
            'wajib_skripsi' => ['nullable', 'boolean'], 'wajib_kompre' => ['nullable', 'boolean'], 'khusus_transfer' => ['nullable', 'boolean'],
            'format_file' => ['required', 'string', 'max:30', 'regex:/^\s*(pdf|jpg|png)(\s*,\s*(pdf|jpg|png))*\s*$/i'],
            'ukuran_maks_mb' => ['required', 'integer', 'min:1', 'max:20'],
            'urutan' => ['nullable', 'integer', 'min:1', 'max:99'],
        ];
    }

    private function dataDokumen(Request $request): array
    {
        $d = $request->validate($this->aturanDokumen(), ['format_file.regex' => 'Format hanya boleh PDF, JPG, PNG (pisahkan dengan koma).'], ['format_file' => 'format', 'ukuran_maks_mb' => 'ukuran maksimal']);
        foreach (['wajib_skripsi', 'wajib_kompre', 'khusus_transfer'] as $k) { $d[$k] = $request->boolean($k); }
        if (! $d['wajib_skripsi'] && ! $d['wajib_kompre']) {
            throw \Illuminate\Validation\ValidationException::withMessages(['wajib_skripsi' => 'Pilih minimal satu paket (Skripsi atau Komprehensif).']);
        }
        $d['format_file'] = strtoupper(preg_replace('/\s*,\s*/', ', ', trim($d['format_file'])));
        $d['urutan'] = $d['urutan'] ?? (JenisDokumen::max('urutan') + 1);
        return $d;
    }

    public function dokumenStore(Request $request)
    {
        $j = JenisDokumen::create($this->dataDokumen($request));
        AktivitasService::catat('tambah dokumen persyaratan', ['keterangan' => $j->nama_dokumen]);
        return back()->with('sukses', "Dokumen {$j->nama_dokumen} ditambahkan.");
    }

    public function dokumenUpdate(Request $request, JenisDokumen $jenis)
    {
        $jenis->update($this->dataDokumen($request));
        AktivitasService::catat('ubah dokumen persyaratan', ['keterangan' => $jenis->nama_dokumen]);
        return back()->with('sukses', "Dokumen {$jenis->nama_dokumen} diperbarui.");
    }

    public function dokumenDestroy(JenisDokumen $jenis)
    {
        if ($jenis->dokumen()->exists()) {
            return back()->with('galat', "Dokumen {$jenis->nama_dokumen} sudah dipakai pada pengajuan sehingga tidak dapat dihapus. Ubah syaratnya (wajib skripsi/kompre) bila tidak lagi diperlukan.");
        }
        $nama = $jenis->nama_dokumen;
        $jenis->delete();
        AktivitasService::catat('hapus dokumen persyaratan', ['keterangan' => $nama]);
        return back()->with('sukses', "Dokumen {$nama} dihapus.");
    }

    private function dataMateri(Request $request): array
    {
        return $request->validate([
            'nama_paket' => ['nullable', 'string', 'max:50'],
            'mata_kuliah_1' => ['required', 'string', 'max:150'], 'mata_kuliah_2' => ['required', 'string', 'max:150'], 'mata_kuliah_3' => ['required', 'string', 'max:150'],
        ], [], ['mata_kuliah_1' => 'mata kuliah 1', 'mata_kuliah_2' => 'mata kuliah 2', 'mata_kuliah_3' => 'mata kuliah 3']);
    }

    public function materiStore(Request $request)
    {
        $d = $this->dataMateri($request);
        $d['nama_paket'] = $d['nama_paket'] ?: 'Paket ' . (MateriKompre::withTrashed()->count() + 1);
        $m = MateriKompre::create($d);
        AktivitasService::catat('tambah paket materi', ['keterangan' => "{$m->nama_paket}: {$m->teks()}"]);
        return back()->with('sukses', "{$m->nama_paket} ditambahkan dan langsung muncul di pilihan mahasiswa.");
    }

    public function materiUpdate(Request $request, MateriKompre $materi)
    {
        $d = $this->dataMateri($request);
        $d['nama_paket'] = $d['nama_paket'] ?: $materi->nama_paket;
        $materi->update($d);
        AktivitasService::catat('ubah paket materi', ['keterangan' => "{$materi->nama_paket}: {$materi->teks()}"]);
        return back()->with('sukses', "{$materi->nama_paket} diperbarui.");
    }

    public function materiDestroy(MateriKompre $materi)
    {
        $materi->delete();
        AktivitasService::catat('hapus paket materi', ['keterangan' => $materi->nama_paket]);
        return back()->with('sukses', "{$materi->nama_paket} dihapus dan tidak muncul lagi di pilihan mahasiswa.");
    }
}
