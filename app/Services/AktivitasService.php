<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\RiwayatAktivitas;
use App\Models\User;

/** Pencatatan riwayat aktivitas (FR-28) dan notifikasi dalam sistem. */
class AktivitasService
{
    public static function catat(string $aksi, array $data = []): RiwayatAktivitas
    {
        return RiwayatAktivitas::create([
            'id_user' => $data['id_user'] ?? auth()->id(),
            'id_pengajuan' => $data['id_pengajuan'] ?? null,
            'id_mahasiswa' => $data['id_mahasiswa'] ?? null,
            'aksi' => $aksi,
            'status_lama' => $data['status_lama'] ?? null,
            'status_baru' => $data['status_baru'] ?? null,
            'keterangan' => $data['keterangan'] ?? null,
        ]);
    }

    public static function notif(int $idUser, string $jenis, string $judul, ?string $pesan = null, ?int $idPengajuan = null): void
    {
        Notifikasi::create(['id_user' => $idUser, 'jenis' => $jenis, 'judul' => $judul, 'pesan' => $pesan, 'id_pengajuan' => $idPengajuan]);
    }

    /** Kirim notifikasi ke seluruh staf aktif (admin & super admin). */
    public static function notifStaf(string $jenis, string $judul, ?string $pesan = null, ?int $idPengajuan = null, bool $hanyaSuper = false): void
    {
        User::query()->where('is_active', true)
            ->whereIn('role', $hanyaSuper ? ['super_admin'] : ['admin', 'super_admin'])
            ->pluck('id_user')
            ->each(fn ($id) => self::notif($id, $jenis, $judul, $pesan, $idPengajuan));
    }
}
