<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale('id');
        // Pembatasan percobaan per akun (email) + IP agar banyak mahasiswa di satu jaringan kampus tidak saling terblokir.
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(8)->by(strtolower((string) $r->input('email')) . '|' . $r->ip()));
        RateLimiter::for('daftar', fn (Request $r) => Limit::perMinute(30)->by($r->ip()));
        RateLimiter::for('otp', fn (Request $r) => Limit::perMinute(10)->by($r->session()->getId()));
        RateLimiter::for('kirim-otp', fn (Request $r) => Limit::perMinute(3)->by($r->session()->getId() . '|' . strtolower((string) $r->input('email'))));
    }
}
