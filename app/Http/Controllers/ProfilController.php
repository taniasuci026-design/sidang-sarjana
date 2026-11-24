<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    public function show(Request $request)
    {
        return view('profil', ['u' => $request->user()->load('mahasiswa')]);
    }

    public function update(Request $request)
    {
        $u = $request->user();
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_whatsapp' => [$u->isMahasiswa() ? 'required' : 'nullable', 'regex:/^(08|628)\d{7,12}$/', Rule::unique('users', 'no_whatsapp')->ignore($u->id_user, 'id_user')],
        ], ['no_whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 atau 628.'], ['no_whatsapp' => 'nomor WhatsApp']);
        $u->update($data);
        AktivitasService::catat('ubah profil', ['id_mahasiswa' => $u->mahasiswa?->id_mahasiswa]);
        return back()->with('sukses', 'Profil berhasil diperbarui.');
    }

    public function kataSandi(Request $request)
    {
        $u = $request->user();
        $request->validate([
            'password_lama' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:password_lama'],
        ], [], ['password_lama' => 'kata sandi lama', 'password' => 'kata sandi baru']);
        if (! Hash::check($request->password_lama, $u->password)) {
            return back()->withErrors(['password_lama' => 'Kata sandi lama tidak sesuai.']);
        }
        $u->update(['password' => $request->password]);
        AktivitasService::catat('ubah kata sandi');
        return back()->with('sukses', 'Kata sandi berhasil diubah.');
    }
}
