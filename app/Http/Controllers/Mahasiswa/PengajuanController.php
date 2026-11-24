<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\JenisDokumen;
use App\Models\Mahasiswa;
use App\Models\MateriKompre;
use App\Models\Pengajuan;
use App\Models\PeriodePendaftaran;
use App\Services\AktivitasService;
use App\Services\NpmService;
use App\Services\PengajuanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** UI-06 s.d. UI-10: pengajuan sidang, status, ubah data, dan perbaikan berkas (FR-09 – FR-20, FR-35, FR-37). */
class PengajuanController extends Controller
{
    private function milik(Request $request, Pengajuan $p): void
    {
        abort_unless($p->mahasiswa && $p->mahasiswa->id_user === $request->user()->id_user, 404);
    }

    public function index(Request $request)
    {
        $mhs = $request->user()->mahasiswa;
        return view('mahasiswa.pengajuan-index', [
            'pengajuan' => $mhs ? $mhs->pengajuan()->with('kategori')->orderByDesc('id_pengajuan')->get() : collect(),
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $mhs = $user->mahasiswa;
        $periode = PeriodePendaftaran::aktif();
        if (! $periode || ! $periode->sedangDibuka()) {
            return view('mahasiswa.ditutup', ['periode' => $periode]);
        }
        $paket = PengajuanService::paketTersedia($mhs);
        if (! $paket) {
            return redirect()->route('mahasiswa.pengajuan.index')->with('galat', 'Anda sudah mencapai batas pengajuan (Skripsi lalu Komprehensif).');
        }
        return view('mahasiswa.pengajuan-create', [
            'user' => $user, 'mhs' => $mhs, 'periode' => $periode, 'paketTersedia' => $paket,
            'materi' => MateriKompre::orderBy('nama_paket')->get(),
            'jenis' => JenisDokumen::orderBy('urutan')->get(),
            'pengajuanKedua' => $mhs && $mhs->pengajuan()->exists(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $mhs = $user->mahasiswa;
        $periode = PeriodePendaftaran::aktif();
        if (! $periode || ! $periode->sedangDibuka()) {
            return redirect()->route('mahasiswa.pengajuan.create')->with('galat', 'Pendaftaran sidang sedang ditutup. Pengajuan baru tidak dapat dikirim.');
        }
        $paketTersedia = PengajuanService::paketTersedia($mhs);

        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'npm' => ['required', 'digits:' . config('sidang.npm.panjang'), Rule::unique('mahasiswa', 'npm')->ignore($mhs?->id_mahasiswa, 'id_mahasiswa')],
            'no_whatsapp' => ['required', 'regex:/^(08|628)\d{7,12}$/', Rule::unique('users', 'no_whatsapp')->ignore($user->id_user, 'id_user')],
            'jalur' => ['required', Rule::in(['reguler', 'transfer'])],
            'dosen_pembimbing_pi' => ['nullable', 'required_if:jalur,transfer', 'string', 'max:100'],
            'paket_sidang' => ['required', Rule::in($paketTersedia)],
            'id_materi' => ['nullable', 'required_if:paket_sidang,kompre', Rule::exists('materi_kompre', 'id_materi')->whereNull('deleted_at')],
            'pernyataan' => ['accepted'],
        ], [
            'npm.unique' => 'NPM sudah pernah dipakai mendaftar.',
            'paket_sidang.in' => 'Paket sidang tidak tersedia untuk Anda.',
            'dosen_pembimbing_pi.required_if' => 'Nama dosen pembimbing PI wajib diisi untuk jalur transfer.',
            'id_materi.required_if' => 'Pilih materi sidang komprehensif.',
            'pernyataan.accepted' => 'Centang pernyataan bahwa data dan dokumen yang diajukan benar.',
            'no_whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 atau 628.',
        ], ['npm' => 'NPM', 'nama_lengkap' => 'nama lengkap', 'no_whatsapp' => 'nomor WhatsApp', 'id_materi' => 'materi sidang']);

        if (! ($mhs && $mhs->npm === $data['npm']) && ! NpmService::valid($data['npm'])) {
            throw ValidationException::withMessages(['npm' => 'NPM tidak terdaftar sebagai NPM mahasiswa Program Studi Sistem Informasi Universitas Gunadarma.']);
        }

        $jenisWajib = Pengajuan::jenisDokumenUntuk($data['paket_sidang'], $data['jalur']);
        $aturan = [];
        $label = [];
        foreach ($jenisWajib as $j) {
            $aturan['berkas.' . $j->id_jenis] = PengajuanService::aturanBerkas($j);
            $label['berkas.' . $j->id_jenis] = 'berkas ' . $j->nama_dokumen;
        }
        $request->validate($aturan, [], $label);

        $pengajuan = DB::transaction(function () use ($user, $mhs, $data, $periode, $jenisWajib, $request) {
            $info = NpmService::info($data['npm']);
            $mhs = $mhs ?? new Mahasiswa(['id_user' => $user->id_user]);
            $mhs->fill(['npm' => $data['npm'], 'nama_lengkap' => $data['nama_lengkap']] + ($mhs->exists && $mhs->npm === $data['npm'] ? [] : $info));
            $mhs->save();
            $user->update(['no_whatsapp' => $data['no_whatsapp']]);

            $p = Pengajuan::create([
                'no_pengajuan' => Pengajuan::nomorBaru(), 'id_mahasiswa' => $mhs->id_mahasiswa, 'id_periode' => $periode->id_periode,
                'jalur' => $data['jalur'], 'paket_sidang' => $data['paket_sidang'],
                'id_materi' => $data['paket_sidang'] === 'kompre' ? $data['id_materi'] : null,
                'dosen_pembimbing_pi' => $data['jalur'] === 'transfer' ? $data['dosen_pembimbing_pi'] : null,
                'status_pengajuan' => 'menunggu', 'status_verifikasi' => 'belum', 'diajukan_at' => now(),
            ]);
            $p->setRelation('mahasiswa', $mhs);
            foreach ($jenisWajib as $j) {
                PengajuanService::simpanBerkas($p, $j, $request->file('berkas.' . $j->id_jenis));
            }
            AktivitasService::catat('pengajuan dibuat', ['id_pengajuan' => $p->id_pengajuan, 'id_mahasiswa' => $mhs->id_mahasiswa, 'status_baru' => 'menunggu',
                'keterangan' => 'Pengajuan dibuat dan ' . $jenisWajib->count() . ' dokumen persyaratan diunggah.']);
            AktivitasService::notifStaf('pengajuan', 'Pengajuan baru masuk', "{$p->no_pengajuan} · {$mhs->nama_lengkap} ({$p->paketLabel()})", $p->id_pengajuan);
            AktivitasService::notif($user->id_user, 'pengajuan', 'Pengajuan dikirim', "{$p->no_pengajuan} · Pengajuan masuk antrean verifikasi.", $p->id_pengajuan);
            return $p;
        });

        return redirect()->route('mahasiswa.pengajuan.show', $pengajuan)->with('sukses', "Pengajuan {$pengajuan->no_pengajuan} berhasil dikirim dan masuk antrean verifikasi.");
    }

    public function show(Request $request, Pengajuan $pengajuan)
    {
        $this->milik($request, $pengajuan);
        $pengajuan->load(['mahasiswa.user', 'materi', 'kategori', 'dokumen.jenis',
            'catatan' => fn ($q) => $q->where('terlihat_mahasiswa', true)->with('penulis')->latest('id_catatan'),
            'riwayat' => fn ($q) => $q->with('pelaku')->latest('id_riwayat')]);
        return view('mahasiswa.pengajuan-show', ['p' => $pengajuan, 'tab' => $request->query('tab', 'informasi')]);
    }

    public function edit(Request $request, Pengajuan $pengajuan)
    {
        $this->milik($request, $pengajuan);
        abort_unless(in_array($pengajuan->status_pengajuan, ['menunggu', 'perbaikan'], true), 403, 'Data hanya dapat diubah saat pengajuan Menunggu atau Perbaikan.');
        return view('mahasiswa.pengajuan-edit', ['p' => $pengajuan->load('mahasiswa.user'), 'materi' => MateriKompre::orderBy('nama_paket')->get()]);
    }

    /** "Ubah Data" — memperbaiki salah ketik termasuk NPM selama status Menunggu (revisi dosen no. 5). */
    public function update(Request $request, Pengajuan $pengajuan)
    {
        $this->milik($request, $pengajuan);
        abort_unless(in_array($pengajuan->status_pengajuan, ['menunggu', 'perbaikan'], true), 403);
        $mhs = $pengajuan->mahasiswa;
        $user = $request->user();
        $bolehNpm = $pengajuan->status_pengajuan === 'menunggu';
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'npm' => [$bolehNpm ? 'required' : 'prohibited', 'digits:' . config('sidang.npm.panjang'), Rule::unique('mahasiswa', 'npm')->ignore($mhs->id_mahasiswa, 'id_mahasiswa')],
            'no_whatsapp' => ['required', 'regex:/^(08|628)\d{7,12}$/', Rule::unique('users', 'no_whatsapp')->ignore($user->id_user, 'id_user')],
            'dosen_pembimbing_pi' => [$pengajuan->jalur === 'transfer' ? 'required' : 'nullable', 'string', 'max:100'],
            'id_materi' => [$pengajuan->paket_sidang === 'kompre' ? 'required' : 'nullable', Rule::exists('materi_kompre', 'id_materi')->whereNull('deleted_at')],
        ], ['npm.unique' => 'NPM sudah pernah dipakai mendaftar.'], ['npm' => 'NPM', 'nama_lengkap' => 'nama lengkap', 'no_whatsapp' => 'nomor WhatsApp']);
        if ($bolehNpm && $data['npm'] !== $mhs->npm && ! NpmService::valid($data['npm'])) {
            throw ValidationException::withMessages(['npm' => 'NPM tidak terdaftar sebagai NPM mahasiswa Program Studi Sistem Informasi Universitas Gunadarma.']);
        }

        $berubah = [];
        DB::transaction(function () use ($data, $mhs, $user, $pengajuan, $bolehNpm, &$berubah) {
            if ($mhs->nama_lengkap !== $data['nama_lengkap']) { $berubah[] = 'nama'; }
            if ($bolehNpm && $mhs->npm !== $data['npm']) { $berubah[] = 'NPM'; }
            if ($user->no_whatsapp !== $data['no_whatsapp']) { $berubah[] = 'WhatsApp'; }
            $mhs->fill(['nama_lengkap' => $data['nama_lengkap']] + ($bolehNpm ? ['npm' => $data['npm']] + NpmService::info($data['npm']) : []))->save();
            $user->update(['no_whatsapp' => $data['no_whatsapp']]);
            $baru = ['dosen_pembimbing_pi' => $pengajuan->jalur === 'transfer' ? $data['dosen_pembimbing_pi'] : null,
                     'id_materi' => $pengajuan->paket_sidang === 'kompre' ? $data['id_materi'] : null];
            if ($pengajuan->dosen_pembimbing_pi != $baru['dosen_pembimbing_pi']) { $berubah[] = 'dosen pembimbing PI'; }
            if ($pengajuan->id_materi != $baru['id_materi']) { $berubah[] = 'materi'; }
            $pengajuan->update($baru);
            if ($berubah) {
                $ket = 'Mahasiswa mengubah ' . implode(', ', $berubah) . '.';
                AktivitasService::catat('ubah data', ['id_pengajuan' => $pengajuan->id_pengajuan, 'id_mahasiswa' => $mhs->id_mahasiswa, 'keterangan' => $ket]);
                AktivitasService::notifStaf('pengajuan', 'Data pengajuan diubah mahasiswa', "{$pengajuan->no_pengajuan} · {$ket}", $pengajuan->id_pengajuan);
            }
        });
        return redirect()->route('mahasiswa.pengajuan.show', $pengajuan)->with('sukses', $berubah ? 'Data pengajuan berhasil diperbarui.' : 'Tidak ada data yang berubah.');
    }

    /** FR-18 & FR-20: unggah ulang hanya dokumen "Perlu Perbaikan", lalu kembali ke antrean pemeriksaan. */
    public function perbaikan(Request $request, Pengajuan $pengajuan)
    {
        $this->milik($request, $pengajuan);
        abort_unless($pengajuan->status_pengajuan === 'perbaikan', 403, 'Perbaikan hanya dapat dikirim saat status Perbaikan.');
        $perlu = $pengajuan->dokumen()->with('jenis')->where('status_dokumen', 'perlu_perbaikan')->get();
        $aturan = [];
        $label = [];
        foreach ($perlu as $d) {
            $aturan['berkas.' . $d->id_jenis] = PengajuanService::aturanBerkas($d->jenis);
            $label['berkas.' . $d->id_jenis] = 'berkas ' . $d->jenis->nama_dokumen;
        }
        if (! $perlu->count()) {
            $aturan['konfirmasi'] = ['accepted'];
        }
        $request->validate($aturan, ['konfirmasi.accepted' => 'Konfirmasi bahwa data sudah diperbaiki.'], $label);

        DB::transaction(function () use ($perlu, $pengajuan, $request) {
            $pengajuan->load('mahasiswa');
            foreach ($perlu as $d) {
                PengajuanService::simpanBerkas($pengajuan, $d->jenis, $request->file('berkas.' . $d->id_jenis));
            }
            $pengajuan->update(['status_pengajuan' => 'menunggu']);
            $nama = $perlu->pluck('jenis.nama_dokumen')->implode(', ') ?: 'data pengajuan';
            AktivitasService::catat('kirim perbaikan', ['id_pengajuan' => $pengajuan->id_pengajuan, 'id_mahasiswa' => $pengajuan->id_mahasiswa,
                'status_lama' => 'perbaikan', 'status_baru' => 'menunggu', 'keterangan' => "Mahasiswa mengunggah ulang: {$nama}."]);
            AktivitasService::notifStaf('pengajuan', 'Perbaikan berkas dikirim', "{$pengajuan->no_pengajuan} · {$pengajuan->mahasiswa->nama_lengkap} mengunggah ulang {$nama}.", $pengajuan->id_pengajuan);
        });
        return redirect()->route('mahasiswa.pengajuan.show', [$pengajuan, 'tab' => 'dokumen'])->with('sukses', 'Perbaikan terkirim. Pengajuan kembali ke antrean pemeriksaan admin.');
    }
}
