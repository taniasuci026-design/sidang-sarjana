<?php

return [
    // Validasi NPM (FR-37). Prodi Sistem Informasi UG memakai NPM 8 digit berawalan kode di bawah.
    // Kode dapat disesuaikan prodi tanpa mengubah kode program.
    'npm' => [
        'panjang' => 8,
        'prefix_prodi' => [
            '10' => ['fakultas' => 'Ilmu Komputer & Teknologi Informasi', 'program_studi' => 'Sistem Informasi'],
            '11' => ['fakultas' => 'Ilmu Komputer & Teknologi Informasi', 'program_studi' => 'Sistem Informasi'],
        ],
    ],
    'otp' => [
        'berlaku_menit' => 10,
        'maks_percobaan' => 5,
        // Tampilkan kode OTP di layar hanya saat pengembangan lokal dengan mailer "log".
        'tampilkan_di_layar' => env('APP_ENV') === 'local' && env('MAIL_MAILER', 'log') === 'log',
    ],
    'maks_pengajuan' => 2,
];
