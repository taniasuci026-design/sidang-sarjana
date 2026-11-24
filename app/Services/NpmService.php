<?php

namespace App\Services;

/** Validasi NPM dan pembacaan fakultas, program studi, angkatan dari NPM (FR-37). */
class NpmService
{
    public static function valid(string $npm): bool
    {
        $cfg = config('sidang.npm');
        return preg_match('/^\d{' . $cfg['panjang'] . '}$/', $npm) === 1
            && array_key_exists(substr($npm, 0, 2), $cfg['prefix_prodi']);
    }

    public static function info(string $npm): array
    {
        $prodi = config('sidang.npm.prefix_prodi')[substr($npm, 0, 2)] ?? ['fakultas' => null, 'program_studi' => null];
        $angkatan = (int) ('20' . substr($npm, 3, 2));
        return $prodi + ['angkatan' => $angkatan >= 2000 && $angkatan <= (int) date('Y') ? $angkatan : null];
    }

    /** Samarkan NPM untuk tampilan publik, mis. 101****18. */
    public static function samarkan(string $npm): string
    {
        return substr($npm, 0, 3) . str_repeat('*', max(strlen($npm) - 5, 0)) . substr($npm, -2);
    }
}
