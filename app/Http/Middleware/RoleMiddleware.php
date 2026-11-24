<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Pembatasan akses halaman berdasarkan peran (FR-06, NFR-02). */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }
        if (! $user->is_active || ($user->isMahasiswa() && $user->mahasiswa?->status_akun === 'arsip')) {
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan. Hubungi bagian akademik prodi.']);
        }
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        return $next($request);
    }
}
