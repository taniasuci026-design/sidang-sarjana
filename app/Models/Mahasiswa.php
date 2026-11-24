<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';
    protected $primaryKey = 'id_mahasiswa';
    protected $fillable = ['id_user', 'npm', 'nama_lengkap', 'fakultas', 'program_studi', 'angkatan', 'status_akun', 'diarsipkan_at'];
    protected $casts = ['diarsipkan_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class, 'id_user'); }
    public function pengajuan() { return $this->hasMany(Pengajuan::class, 'id_mahasiswa'); }
    public function permintaanHapus() { return $this->hasMany(PermintaanHapusAkun::class, 'id_mahasiswa'); }
    public function laporanAktif() { return $this->hasOne(PermintaanHapusAkun::class, 'id_mahasiswa')->where('status_permintaan', 'menunggu')->latestOfMany('id_permintaan'); }
    public function riwayat() { return $this->hasMany(RiwayatAktivitas::class, 'id_mahasiswa'); }
}
