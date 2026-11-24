<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatatanPengajuan;
use App\Models\KategoriHasil;
use App\Models\Pengajuan;
use App\Models\PermintaanHapusAkun;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** UI-12 Daftar Pengajuan & UI-13 Pemeriksaan Berkas (FR-19, FR-22 – FR-29). */
class PengajuanController extends Controller
{
    use Filter;

    public function index(Request $request)
    {
        [$q, $f] = $this->queryPengajuan($request);
        $urut = $request->query('urut', 'terbaru');
        $q->orderBy('diajukan_at', $urut === 'terlama' ? 'asc' : 'desc');
        return view('admin.pengajuan-index', [
            'pengajuan' => $q->paginate(10)->withQueryString(),
            'filter' => $f, 'kategori' => KategoriHasil::orderBy('kode')->get(),
        ]);
    }

    private function aktif(Pengajuan $p): void
    {
        abort_if($p->mahasiswa->status_akun === 'arsip', 404, 'Akun mahasiswa sudah diarsipkan.');
    }

    public function show(Pengajuan $pengajuan)
    {
        $this->aktif($pengajuan);
        $pengajuan->load(['mahasiswa.user', 'materi', 'kategori', 'adminTerakhir', 'dokumen.jenis',
            'catatan' => fn ($q) => $q->with('penulis')->latest('id_catatan')]);
        $laporan = PermintaanHapusAkun::with(['pengaju', 'peninjau'])->where('id_mahasiswa', $pengajuan->id_mahasiswa)->latest('id_permintaan')->first();
        return view('admin.pengajuan-show', [
            'p' => $pengajuan, 'kategori' => KategoriHasil::orderBy('kode')->get(), 'laporan' => $laporan,
        ]);
    }

    /** Simpan hasil pemeriksaan: status per dokumen, status pengajuan, kategori, catatan (FR-25 – FR-28). */
    public function periksa(Request $request, Pengajuan $pengajuan)
    {
        $this->aktif($pengajuan);
        $pengajuan->load('dokumen.jenis', 'mahasiswa');
        $data = $request->validate([
            'status_pengajuan' => ['required', Rule::in(array_keys(Pengajuan::STATUS))],
            'id_kategori' => ['nullable', 'exists:kategori_hasil,id_kategori'],
            'dokumen' => ['array'],
            'dokumen.*.status' => ['required', Rule::in(['menunggu', 'lengkap', 'perlu_perbaikan'])],
            'dokumen.*.catatan' => ['nullable', 'string', 'max:500'],
            'catatan_mahasiswa' => ['nullable', 'string', 'max:2000'],
            'catatan_internal' => ['nullable', 'string', 'max:2000'],
        ], [], ['status_pengajuan' => 'status pengajuan', 'id_kategori' => 'kategori hasil']);

        $statusDok = collect($data['dokumen'] ?? [])->map(fn ($d) => $d['status']);
        $kategori = isset($data['id_kategori']) ? KategoriHasil::find($data['id_kategori']) : null;
        $status = $data['status_pengajuan'];
        $galat = [];
        foreach ($pengajuan->dokumen as $d) {
            $s = $statusDok[$d->id_dokumen] ?? $d->status_dokumen;
            if ($s === 'perlu_perbaikan' && blank($data['dokumen'][$d->id_dokumen]['catatan'] ?? null)) {
                $galat["dokumen.{$d->id_dokumen}.catatan"] = "Tulis alasan perbaikan untuk dokumen {$d->jenis->nama_dokumen}.";
            }
        }
        $adaPerbaikan = $pengajuan->dokumen->contains(fn ($d) => ($statusDok[$d->id_dokumen] ?? $d->status_dokumen) === 'perlu_perbaikan');
        $semuaLengkap = $pengajuan->dokumen->every(fn ($d) => ($statusDok[$d->id_dokumen] ?? $d->status_dokumen) === 'lengkap');
        if (in_array($status, ['perbaikan', 'selesai'], true) && ! $kategori) {
            $galat['id_kategori'] = 'Pilih kategori hasil untuk status ' . Pengajuan::STATUS[$status] . '.';
        }
        if ($status === 'selesai') {
            if (! $semuaLengkap) { $galat['status_pengajuan'] = 'Status Selesai hanya dapat dipilih jika semua dokumen Lengkap.'; }
            elseif ($kategori && $kategori->kode !== '1') { $galat['id_kategori'] = 'Status Selesai memerlukan kategori 1 (Bisa Daftar Sidang).'; }
            elseif ($pengajuan->status_verifikasi !== 'terverifikasi') { $galat['status_pengajuan'] = 'Verifikasi data mahasiswa terlebih dahulu sebelum menyelesaikan pengajuan.'; }
        }
        if ($status === 'perbaikan') {
            if ($kategori && $kategori->kode === '1') { $galat['id_kategori'] = 'Kategori 1 (Bisa Daftar Sidang) tidak dapat dipakai untuk status Perbaikan.'; }
            if ($kategori && $kategori->kode === '2b' && ! $adaPerbaikan) { $galat['dokumen'] = 'Kategori Kendala Berkas memerlukan minimal satu dokumen berstatus Perlu Perbaikan.'; }
        }
        if ($adaPerbaikan && ! in_array($status, ['perbaikan'], true)) {
            $galat['status_pengajuan'] = 'Ada dokumen Perlu Perbaikan; pilih status Perbaikan.';
        }
        if ($galat) {
            throw ValidationException::withMessages($galat);
        }

        $lama = $pengajuan->status_pengajuan;
        DB::transaction(function () use ($pengajuan, $data, $statusDok, $status, $kategori, $lama, $request) {
            $dokBerubah = [];
            foreach ($pengajuan->dokumen as $d) {
                $s = $statusDok[$d->id_dokumen] ?? $d->status_dokumen;
                $c = $s === 'perlu_perbaikan' ? ($data['dokumen'][$d->id_dokumen]['catatan'] ?? null) : null;
                if ($d->status_dokumen !== $s || $d->catatan_pemeriksaan !== $c) {
                    $dokBerubah[] = $d->jenis->nama_dokumen . ': ' . \App\Models\DokumenPengajuan::STATUS[$s];
                }
                $d->update(['status_dokumen' => $s, 'catatan_pemeriksaan' => $c]);
            }
            $pengajuan->update(['status_pengajuan' => $status, 'id_kategori' => in_array($status, ['menunggu', 'diproses'], true) ? ($kategori?->id_kategori) : $kategori?->id_kategori,
                'id_admin_terakhir' => $request->user()->id_user]);
            foreach (['catatan_mahasiswa' => true, 'catatan_internal' => false] as $kolom => $terlihat) {
                if (filled($data[$kolom] ?? null)) {
                    CatatanPengajuan::create(['id_pengajuan' => $pengajuan->id_pengajuan, 'id_user' => $request->user()->id_user, 'isi_catatan' => $data[$kolom], 'terlihat_mahasiswa' => $terlihat]);
                }
            }
            $info = ['id_pengajuan' => $pengajuan->id_pengajuan, 'id_mahasiswa' => $pengajuan->id_mahasiswa];
            if ($lama !== $status) {
                AktivitasService::catat('ubah status', $info + ['status_lama' => $lama, 'status_baru' => $status,
                    'keterangan' => 'Status diubah dari ' . Pengajuan::STATUS[$lama] . ' menjadi ' . Pengajuan::STATUS[$status] . ($kategori ? " · {$kategori->label()} ({$kategori->nama_kategori})" : '') . '.']);
            }
            if ($dokBerubah) {
                AktivitasService::catat('periksa dokumen', $info + ['keterangan' => implode('; ', $dokBerubah)]);
            }
            if (filled($data['catatan_mahasiswa'] ?? null) || filled($data['catatan_internal'] ?? null)) {
                AktivitasService::catat('tambah catatan', $info + ['keterangan' => 'Catatan pemeriksaan ditambahkan.']);
            }
            if ($lama !== $status || filled($data['catatan_mahasiswa'] ?? null) || $dokBerubah) {
                [$judul, $pesan] = $pengajuan->fresh('kategori')->pesanUntukMahasiswa();
                $judul = $status === 'perbaikan' ? 'Berkas perlu diperbaiki' : ($status === 'selesai' ? 'Berkas telah diterima' : 'Pembaruan pengajuan');
                AktivitasService::notif($pengajuan->mahasiswa->id_user, 'pengajuan', $judul, "{$pengajuan->no_pengajuan} · " . ($pesan ?: Pengajuan::STATUS[$status]), $pengajuan->id_pengajuan);
            }
        });
        return redirect()->route('admin.pengajuan.show', $pengajuan)->with('sukses', 'Hasil pemeriksaan berhasil disimpan.');
    }

    /** Hasil Verifikasi data mahasiswa. */
    public function verifikasi(Request $request, Pengajuan $pengajuan)
    {
        $this->aktif($pengajuan);
        if ($pengajuan->status_verifikasi !== 'terverifikasi') {
            $lama = $pengajuan->status_pengajuan;
            $pengajuan->update(['status_verifikasi' => 'terverifikasi', 'id_admin_terakhir' => $request->user()->id_user,
                'status_pengajuan' => $lama === 'menunggu' ? 'diproses' : $lama]);
            AktivitasService::catat('verifikasi data', ['id_pengajuan' => $pengajuan->id_pengajuan, 'id_mahasiswa' => $pengajuan->id_mahasiswa,
                'status_lama' => $lama !== $pengajuan->status_pengajuan ? $lama : null, 'status_baru' => $lama !== $pengajuan->status_pengajuan ? 'diproses' : null,
                'keterangan' => 'Data mahasiswa diverifikasi.']);
            if ($lama === 'menunggu') {
                AktivitasService::notif($pengajuan->mahasiswa->id_user, 'pengajuan', 'Pengajuan sedang diperiksa', "{$pengajuan->no_pengajuan} · Admin sedang memeriksa berkas Anda.", $pengajuan->id_pengajuan);
            }
        }
        return back()->with('sukses', 'Data mahasiswa terverifikasi.');
    }

    /** Admin melaporkan akun mencurigakan & mengajukan hapus akun ke Super Admin. */
    public function laporkan(Request $request, Pengajuan $pengajuan)
    {
        $this->aktif($pengajuan);
        $data = $request->validate(['alasan' => ['required', 'string', 'max:500']], [], ['alasan' => 'alasan']);
        $mhs = $pengajuan->mahasiswa;
        if (PermintaanHapusAkun::where('id_mahasiswa', $mhs->id_mahasiswa)->where('status_permintaan', 'menunggu')->exists()) {
            return back()->with('galat', 'Akun ini sudah dilaporkan dan menunggu tanggapan Superadmin.');
        }
        DB::transaction(function () use ($mhs, $data, $request, $pengajuan) {
            PermintaanHapusAkun::create(['id_mahasiswa' => $mhs->id_mahasiswa, 'id_pengaju' => $request->user()->id_user, 'alasan' => $data['alasan'],
                'status_permintaan' => 'menunggu', 'diajukan_at' => now()]);
            $mhs->update(['status_akun' => 'dilaporkan']);
            AktivitasService::catat('ajukan hapus akun', ['id_pengajuan' => $pengajuan->id_pengajuan, 'id_mahasiswa' => $mhs->id_mahasiswa, 'status_lama' => 'aktif', 'status_baru' => 'dilaporkan', 'keterangan' => 'Alasan: ' . $data['alasan']]);
            AktivitasService::notifStaf('akun', 'Permintaan hapus akun', "{$mhs->nama_lengkap} ({$mhs->npm}) dilaporkan oleh {$request->user()->nama}: {$data['alasan']}", null, true);
        });
        return back()->with('sukses', 'Laporan terkirim. Menunggu tanggapan Superadmin.');
    }

    /** Riwayat perubahan pengajuan (FR-29). */
    public function riwayat(Pengajuan $pengajuan)
    {
        $this->aktif($pengajuan);
        return view('admin.riwayat', ['p' => $pengajuan->load('mahasiswa'), 'riwayat' => $pengajuan->riwayat()->with('pelaku')->latest('id_riwayat')->paginate(20)]);
    }
}
