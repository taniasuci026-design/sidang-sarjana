<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AktivitasService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ---------- Registrasi + OTP (FR-03, FR-04) ----------
    public function formDaftar()
    {
        return view('auth.daftar');
    }

    public function daftar(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:100'],
            'no_whatsapp' => ['required', 'regex:/^(08|628)\d{7,12}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'no_whatsapp.regex' => 'Nomor WhatsApp harus diawali 08 atau 628 dan berisi 9–14 digit.',
        ], ['no_whatsapp' => 'nomor WhatsApp', 'password' => 'kata sandi']);

        $email = strtolower($data['email']);
        $ada = User::where('email', $email)->first();
        if ($ada && ($ada->email_verified_at || ! $ada->isMahasiswa())) {
            return back()->withInput()->withErrors(['email' => 'Email sudah terdaftar. Silakan masuk atau gunakan Lupa Kata Sandi.']);
        }
        $waDipakai = User::where('no_whatsapp', $data['no_whatsapp'])->when($ada, fn ($q) => $q->where('id_user', '!=', $ada->id_user))->exists();
        if ($waDipakai) {
            return back()->withInput()->withErrors(['no_whatsapp' => 'Nomor WhatsApp sudah dipakai akun lain.']);
        }

        $user = $ada ?? new User(['role' => 'mahasiswa']);
        $user->fill(['nama' => $data['nama'], 'email' => $email, 'no_whatsapp' => $data['no_whatsapp'], 'password' => $data['password'], 'is_active' => true]);
        $user->role = 'mahasiswa';
        $user->save();

        $kode = OtpService::kirim($user, 'registrasi');
        $request->session()->put('otp_user', $user->id_user);
        return redirect()->route('otp.form')->with('sukses', 'Kode verifikasi telah dikirim ke ' . $email . '.')->with('otp_dev', config('sidang.otp.tampilkan_di_layar') ? $kode : null);
    }

    public function formOtp(Request $request)
    {
        $user = User::find($request->session()->get('otp_user'));
        if (! $user) {
            return redirect()->route('daftar');
        }
        return view('auth.otp', ['user' => $user, 'mode' => 'registrasi']);
    }

    public function verifikasiOtp(Request $request)
    {
        $request->validate(['kode' => ['required', 'digits:6']], [], ['kode' => 'kode OTP']);
        $user = User::find($request->session()->get('otp_user'));
        if (! $user) {
            return redirect()->route('daftar');
        }
        if ($galat = OtpService::verifikasi($user, 'registrasi', $request->kode)) {
            return back()->withErrors(['kode' => $galat]);
        }
        $user->update(['email_verified_at' => now()]);
        AktivitasService::catat('registrasi akun', ['id_user' => $user->id_user, 'keterangan' => 'Akun mahasiswa diverifikasi melalui OTP.']);
        $request->session()->forget('otp_user');
        return redirect()->route('login')->with('sukses', 'Akun berhasil diverifikasi. Silakan masuk.');
    }

    public function kirimUlangOtp(Request $request)
    {
        $user = User::find($request->session()->get('otp_user'));
        if (! $user) {
            return redirect()->route('daftar');
        }
        $kode = OtpService::kirim($user, 'registrasi');
        return back()->with('sukses', 'Kode baru telah dikirim.')->with('otp_dev', config('sidang.otp.tampilkan_di_layar') ? $kode : null);
    }

    // ---------- Login (FR-05, FR-06) ----------
    public function formLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $kred = $request->validate(['email' => ['required', 'email'], 'password' => ['required']], [], ['password' => 'kata sandi']);
        $kred['email'] = strtolower($kred['email']);
        $user = User::where('email', $kred['email'])->first();
        if (! $user || ! Auth::validate($kred)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau kata sandi salah.']);
        }
        if (! $user->is_active || $user->mahasiswa?->status_akun === 'arsip') {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi bagian akademik prodi.'])->with('akun_nonaktif', true);
        }
        if (! $user->email_verified_at) {
            $kode = OtpService::kirim($user, 'registrasi');
            $request->session()->put('otp_user', $user->id_user);
            return redirect()->route('otp.form')->with('sukses', 'Akun belum diverifikasi. Kode baru telah dikirim ke email Anda.')->with('otp_dev', config('sidang.otp.tampilkan_di_layar') ? $kode : null);
        }
        Auth::login($user, $request->boolean('ingat'));
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        return redirect()->intended(route($user->dashboardRoute()));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('sukses', 'Anda telah keluar.');
    }

    // ---------- Lupa kata sandi (FR-07) ----------
    public function formLupa()
    {
        return view('auth.lupa');
    }

    public function kirimLupa(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', strtolower($request->email))->where('is_active', true)->first();
        if (! $user) {
            return back()->withInput()->withErrors(['email' => 'Email tidak terdaftar atau akun tidak aktif.']);
        }
        $kode = OtpService::kirim($user, 'reset_password');
        $request->session()->put('reset_user', $user->id_user);
        $request->session()->forget('reset_ok');
        return redirect()->route('lupa.kode')->with('sukses', 'Kode verifikasi atur ulang kata sandi sudah dikirim ke email Anda.')->with('otp_dev', config('sidang.otp.tampilkan_di_layar') ? $kode : null);
    }

    public function formLupaKode(Request $request)
    {
        $user = User::find($request->session()->get('reset_user'));
        return $user ? view('auth.otp', ['user' => $user, 'mode' => 'reset']) : redirect()->route('lupa');
    }

    public function verifikasiLupaKode(Request $request)
    {
        $request->validate(['kode' => ['required', 'digits:6']], [], ['kode' => 'kode OTP']);
        $user = User::find($request->session()->get('reset_user'));
        if (! $user) {
            return redirect()->route('lupa');
        }
        if ($galat = OtpService::verifikasi($user, 'reset_password', $request->kode)) {
            return back()->withErrors(['kode' => $galat]);
        }
        $request->session()->put('reset_ok', true);
        return redirect()->route('lupa.baru');
    }

    public function formSandiBaru(Request $request)
    {
        $user = User::find($request->session()->get('reset_user'));
        if (! $user || ! $request->session()->get('reset_ok')) {
            return redirect()->route('lupa');
        }
        return view('auth.sandi-baru', ['user' => $user]);
    }

    public function simpanSandiBaru(Request $request)
    {
        $user = User::find($request->session()->get('reset_user'));
        if (! $user || ! $request->session()->get('reset_ok')) {
            return redirect()->route('lupa');
        }
        $request->validate(['password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]], [], ['password' => 'kata sandi']);
        $user->update(['password' => $request->password]);
        AktivitasService::catat('atur ulang kata sandi', ['id_user' => $user->id_user]);
        $request->session()->forget(['reset_user', 'reset_ok']);
        return redirect()->route('login')->with('sukses', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
    }
}
