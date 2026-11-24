<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\RiwayatAktivitas;
use App\Models\User;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** UI-16 Manajemen Data Admin (FR-33). */
class AdminController extends Controller
{
    public function index(Request $request)
    {
        $q = User::whereIn('role', ['admin', 'super_admin'])
            ->when($request->filled('q'), fn ($w) => $w->where(fn ($x) => $x->where('nama', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%')))
            ->when($request->filled('peran'), fn ($w) => $w->where('role', $request->peran))
            ->when($request->filled('status'), fn ($w) => $w->where('is_active', $request->status === 'aktif'))
            ->orderByRaw("role = 'super_admin' desc")->orderBy('nama');
        return view('super.admin', ['admin' => $q->paginate(15)->withQueryString()]);
    }

    private function aturan(?User $u = null): array
    {
        return [
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($u?->id_user, 'id_user')],
            'role' => ['required', Rule::in(['admin', 'super_admin'])],
            'password' => [$u ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->aturan(), [], ['role' => 'peran', 'password' => 'kata sandi']);
        $u = User::create($data + ['is_active' => true, 'email_verified_at' => now()]);
        $u->email = strtolower($u->email);
        $u->save();
        AktivitasService::catat('tambah admin', ['keterangan' => "Akun {$u->roleLabel()} {$u->nama} ({$u->email}) ditambahkan."]);
        return back()->with('sukses', "Akun {$u->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->isStaf(), 404);
        $data = $request->validate($this->aturan($user), [], ['role' => 'peran', 'password' => 'kata sandi']);
        if ($user->id_user === $request->user()->id_user && $data['role'] !== 'super_admin') {
            return back()->with('galat', 'Anda tidak dapat menurunkan peran akun Anda sendiri.');
        }
        if (blank($data['password'])) {
            unset($data['password']);
        }
        $user->update($data);
        AktivitasService::catat('ubah admin', ['keterangan' => "Data akun {$user->nama} diubah."]);
        return back()->with('sukses', "Akun {$user->nama} berhasil diperbarui.");
    }

    public function toggle(Request $request, User $user)
    {
        abort_unless($user->isStaf(), 404);
        if ($user->id_user === $request->user()->id_user) {
            return back()->with('galat', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }
        $user->update(['is_active' => ! $user->is_active]);
        AktivitasService::catat($user->is_active ? 'aktifkan admin' : 'nonaktifkan admin', ['keterangan' => "Akun {$user->nama} " . ($user->is_active ? 'diaktifkan kembali.' : 'dinonaktifkan.')]);
        return back()->with('sukses', "Akun {$user->nama} " . ($user->is_active ? 'diaktifkan kembali.' : 'dinonaktifkan dan tidak dapat login.'));
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($user->isStaf(), 404);
        if ($user->id_user === $request->user()->id_user) {
            return back()->with('galat', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }
        if (RiwayatAktivitas::where('id_user', $user->id_user)->exists()) {
            return back()->with('galat', "Akun {$user->nama} sudah memiliki riwayat pemeriksaan sehingga tidak dihapus agar jejak audit tetap ada. Gunakan Nonaktifkan.");
        }
        $nama = $user->nama;
        $user->delete();
        AktivitasService::catat('hapus admin', ['keterangan' => "Akun {$nama} dihapus."]);
        return back()->with('sukses', "Akun {$nama} berhasil dihapus.");
    }
}
