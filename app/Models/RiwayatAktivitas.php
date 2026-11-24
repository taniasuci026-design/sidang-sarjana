<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatAktivitas extends Model
{
    protected $table = 'riwayat_aktivitas';
    protected $primaryKey = 'id_riwayat';
    public const UPDATED_AT = null;
    protected $fillable = ['id_user', 'id_pengajuan', 'id_mahasiswa', 'aksi', 'status_lama', 'status_baru', 'keterangan'];

    public function pelaku() { return $this->belongsTo(User::class, 'id_user'); }
    public function pengajuan() { return $this->belongsTo(Pengajuan::class, 'id_pengajuan'); }
    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa'); }
}
