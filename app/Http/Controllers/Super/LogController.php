<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\RiwayatAktivitas;
use App\Models\User;
use Illuminate\Http\Request;

/** Log Aktivitas seluruh pengguna (FR-28, FR-29). */
class LogController extends Controller
{
    public function index(Request $request)
    {
        $q = RiwayatAktivitas::with(['pelaku', 'pengajuan', 'mahasiswa'])
            ->when($request->filled('peran'), fn ($w) => $w->whereHas('pelaku', fn ($u) => $u->where('role', $request->peran)))
            ->when($request->filled('aksi'), fn ($w) => $w->where('aksi', $request->aksi))
            ->when($request->filled('dari'), fn ($w) => $w->whereDate('created_at', '>=', $request->dari))
            ->when($request->filled('sampai'), fn ($w) => $w->whereDate('created_at', '<=', $request->sampai))
            ->when($request->filled('q'), fn ($w) => $w->where(fn ($x) => $x->where('keterangan', 'like', '%' . $request->q . '%')
                ->orWhereHas('pelaku', fn ($u) => $u->where('nama', 'like', '%' . $request->q . '%'))
                ->orWhereHas('pengajuan', fn ($p) => $p->where('no_pengajuan', 'like', '%' . $request->q . '%'))))
            ->latest('id_riwayat');
        if ($request->query('ekspor') === 'csv') {
            $h = fopen('php://temp', 'r+');
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, ['Waktu', 'Pengguna', 'Peran', 'Aksi', 'No. Pengajuan', 'Status Lama', 'Status Baru', 'Keterangan'], ';');
            foreach ($q->limit(5000)->get() as $r) {
                fputcsv($h, [$r->created_at?->format('d/m/Y H:i'), $r->pelaku?->nama, $r->pelaku?->roleLabel(), $r->aksi, $r->pengajuan?->no_pengajuan, $r->status_lama, $r->status_baru, $r->keterangan], ';');
            }
            rewind($h);
            return response(stream_get_contents($h), 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="log-aktivitas-' . now()->format('Ymd') . '.csv"']);
        }
        return view('super.log', [
            'log' => $q->paginate(20)->withQueryString(),
            'daftarAksi' => RiwayatAktivitas::select('aksi')->distinct()->orderBy('aksi')->pluck('aksi'),
            'peran' => User::ROLE_LABEL,
        ]);
    }
}
