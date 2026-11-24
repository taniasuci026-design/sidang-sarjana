<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\PermintaanHapusAkun;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** UI-17 Manajemen Data Mahasiswa: lihat data, hapus akun yang dilaporkan admin (diarsipkan), tolak laporan. */
class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->tab, ['aktif', 'dilaporkan', 'arsip'], true) ? $request->tab : 'semua';
        $q = Mahasiswa::with(['user', 'laporanAktif.pengaju', 'permintaanHapus' => fn ($x) => $x->where('status_permintaan', 'disetujui')->with(['pengaju', 'peninjau'])])
            ->withCount('pengajuan')
            ->when($tab === 'semua', fn ($w) => $w->where('status_akun', '!=', 'arsip'))
            ->when($tab !== 'semua', fn ($w) => $w->where('status_akun', $tab))
            ->when($request->filled('q'), fn ($w) => $w->where(fn ($x) => $x->where('nama_lengkap', 'like', '%' . $request->q . '%')->orWhere('npm', 'like', '%' . $request->q . '%')
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%' . $request->q . '%'))))
            ->orderByRaw("status_akun = 'dilaporkan' desc")->orderBy('nama_lengkap');
        $hitung = Mahasiswa::selectRaw('status_akun, count(*) n')->groupBy('status_akun')->pluck('n', 'status_akun');
        return view('super.mahasiswa', [
            'mahasiswa' => $q->paginate(15)->withQueryString(), 'tab' => $tab,
            'hitung' => ['semua' => ($hitung['aktif'] ?? 0) + ($hitung['dilaporkan'] ?? 0), 'aktif' => $hitung['aktif'] ?? 0, 'dilaporkan' => $hitung['dilaporkan'] ?? 0, 'arsip' => $hitung['arsip'] ?? 0],
        ]);
    }

    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['user', 'pengajuan' => fn ($q) => $q->orderByDesc('id_pengajuan'), 'laporanAktif.pengaju',
            'permintaanHapus' => fn ($q) => $q->with(['pengaju', 'peninjau'])->latest('id_permintaan'),
            'riwayat' => fn ($q) => $q->with('pelaku')->latest('id_riwayat')->limit(20)]);
        return view('super.mahasiswa-show', ['m' => $mahasiswa]);
    }

    /** Hapus & arsipkan: akun tidak bisa login, data tetap tersimpan sebagai laporan (revisi dosen no. 4). */
    public function hapus(Request $request, Mahasiswa $mahasiswa)
    {
        abort_unless($mahasiswa->status_akun === 'dilaporkan', 403, 'Hanya akun berstatus Dilaporkan yang dapat dihapus.');
        $data = $request->validate(['catatan' => ['required', 'string', 'max:1000'], 'yakin' => ['accepted']],
            ['yakin.accepted' => 'Centang konfirmasi bahwa Anda sudah memeriksa laporan admin.'], ['catatan' => 'catatan Superadmin']);
        $laporan = $mahasiswa->laporanAktif;
        DB::transaction(function () use ($mahasiswa, $laporan, $data, $request) {
            $laporan?->update(['status_permintaan' => 'disetujui', 'id_peninjau' => $request->user()->id_user, 'catatan_peninjau' => $data['catatan'], 'ditinjau_at' => now()]);
            $mahasiswa->update(['status_akun' => 'arsip', 'diarsipkan_at' => now()]);
            $mahasiswa->user->update(['is_active' => false]);
            AktivitasService::catat('hapus akun mahasiswa', ['id_mahasiswa' => $mahasiswa->id_mahasiswa, 'status_lama' => 'dilaporkan', 'status_baru' => 'arsip', 'keterangan' => 'Catatan Superadmin: ' . $data['catatan']]);
            if ($laporan) {
                AktivitasService::notif($laporan->id_pengaju, 'akun', 'Permintaan hapus akun disetujui', "Akun {$mahasiswa->nama_lengkap} ({$mahasiswa->npm}) dihapus dan dipindah ke Arsip.");
            }
        });
        return redirect()->route('super.mahasiswa.index', ['tab' => 'arsip'])->with('sukses', "Akun {$mahasiswa->nama_lengkap} ({$mahasiswa->npm}) dihapus dan dipindah ke tab Arsip.");
    }

    public function tolak(Request $request, Mahasiswa $mahasiswa)
    {
        abort_unless($mahasiswa->status_akun === 'dilaporkan', 403);
        $data = $request->validate(['catatan' => ['required', 'string', 'max:1000']], [], ['catatan' => 'alasan penolakan']);
        $laporan = $mahasiswa->laporanAktif;
        DB::transaction(function () use ($mahasiswa, $laporan, $data, $request) {
            $laporan?->update(['status_permintaan' => 'ditolak', 'id_peninjau' => $request->user()->id_user, 'catatan_peninjau' => $data['catatan'], 'ditinjau_at' => now()]);
            $mahasiswa->update(['status_akun' => 'aktif']);
            AktivitasService::catat('tolak hapus akun', ['id_mahasiswa' => $mahasiswa->id_mahasiswa, 'status_lama' => 'dilaporkan', 'status_baru' => 'aktif', 'keterangan' => 'Alasan penolakan: ' . $data['catatan']]);
            if ($laporan) {
                $p = $mahasiswa->pengajuan()->latest('id_pengajuan')->first();
                AktivitasService::notif($laporan->id_pengaju, 'akun', 'Permintaan hapus akun ditolak', "Akun {$mahasiswa->nama_lengkap} tetap aktif. Alasan: {$data['catatan']}", $p?->id_pengajuan);
            }
        });
        return back()->with('sukses', "Permintaan hapus ditolak. Akun {$mahasiswa->nama_lengkap} kembali aktif.");
    }

    public function eksporArsip()
    {
        $data = Mahasiswa::with(['user', 'permintaanHapus' => fn ($q) => $q->where('status_permintaan', 'disetujui')->with(['pengaju', 'peninjau'])])->where('status_akun', 'arsip')->orderByDesc('diarsipkan_at')->get();
        $h = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF");
        fputcsv($h, ['NPM', 'Nama', 'Email', 'Alasan Laporan', 'Dilaporkan oleh', 'Dihapus oleh', 'Catatan Superadmin', 'Tanggal Hapus'], ';');
        foreach ($data as $m) {
            $l = $m->permintaanHapus->first();
            fputcsv($h, [$m->npm, $m->nama_lengkap, $m->user?->email, $l?->alasan, $l?->pengaju?->nama, $l?->peninjau?->nama, $l?->catatan_peninjau, $m->diarsipkan_at?->format('d/m/Y H:i')], ';');
        }
        rewind($h);
        return response(stream_get_contents($h), 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="arsip-akun-mahasiswa-' . now()->format('Ymd') . '.csv"']);
    }
}
