<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->user()->notifikasi()->latest('id_notifikasi');
        if ($request->filter === 'belum') {
            $q->whereNull('dibaca_at');
        }
        return view('notifikasi', [
            'notifikasi' => $q->paginate(15)->withQueryString(),
            'total' => $request->user()->notifikasi()->count(),
            'belum' => $request->user()->jumlahNotifikasiBaru(),
        ]);
    }

    public function buka(Request $request, Notifikasi $notifikasi)
    {
        abort_unless($notifikasi->id_user === $request->user()->id_user, 403);
        $notifikasi->update(['dibaca_at' => $notifikasi->dibaca_at ?? now()]);
        $u = $request->user();
        if ($notifikasi->id_pengajuan) {
            return redirect()->route($u->isMahasiswa() ? 'mahasiswa.pengajuan.show' : 'admin.pengajuan.show', $notifikasi->id_pengajuan);
        }
        if ($u->isSuperAdmin() && $notifikasi->jenis === 'akun') {
            return redirect()->route('super.mahasiswa.index', ['tab' => 'dilaporkan']);
        }
        return redirect()->route('notifikasi');
    }

    public function bacaSemua(Request $request)
    {
        $request->user()->notifikasi()->whereNull('dibaca_at')->update(['dibaca_at' => now()]);
        return back()->with('sukses', 'Semua notifikasi ditandai dibaca.');
    }
}
