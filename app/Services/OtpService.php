<?php

namespace App\Services;

use App\Mail\KodeOtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/** Pembuatan & verifikasi OTP 6 digit; kode disimpan dalam bentuk hash (Kamus Data otp_codes). */
class OtpService
{
    public static function kirim(User $user, string $tujuan): string
    {
        OtpCode::where('id_user', $user->id_user)->where('tujuan', $tujuan)->whereNull('digunakan_at')
            ->update(['digunakan_at' => now()]);
        $kode = (string) random_int(100000, 999999);
        OtpCode::create([
            'id_user' => $user->id_user, 'tujuan' => $tujuan, 'kode_otp' => Hash::make($kode),
            'kedaluwarsa_at' => now()->addMinutes(config('sidang.otp.berlaku_menit')),
        ]);
        Mail::to($user->email)->send(new KodeOtpMail($user, $kode, $tujuan));
        return $kode;
    }

    /** @return string|null pesan error, null jika valid */
    public static function verifikasi(User $user, string $tujuan, string $kode): ?string
    {
        $otp = OtpCode::where('id_user', $user->id_user)->where('tujuan', $tujuan)->whereNull('digunakan_at')
            ->orderByDesc('id_otp')->first();
        if (! $otp) {
            return 'Kode OTP tidak ditemukan. Silakan kirim ulang kode.';
        }
        if ($otp->kedaluwarsa_at->isPast()) {
            return 'Kode OTP sudah kedaluwarsa. Silakan kirim ulang kode.';
        }
        if ($otp->percobaan >= config('sidang.otp.maks_percobaan')) {
            return 'Terlalu banyak percobaan. Silakan kirim ulang kode.';
        }
        if (! Hash::check($kode, $otp->kode_otp)) {
            $otp->increment('percobaan');
            return 'Kode OTP salah.';
        }
        $otp->update(['digunakan_at' => now()]);
        return null;
    }
}
