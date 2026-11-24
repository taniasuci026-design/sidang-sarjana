<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanHapusAkun extends Model
{
    protected $table = 'permintaan_hapus_akun';
    protected $primaryKey = 'id_permintaan';
    public $timestamps = false;
    protected $fillable = ['id_mahasiswa', 'id_pengaju', 'alasan', 'status_permintaan', 'id_peninjau', 'catatan_peninjau', 'diajukan_at', 'ditinjau_at'];
    protected $casts = ['diajukan_at' => 'datetime', 'ditinjau_at' => 'datetime'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa'); }
    public function pengaju() { return $this->belongsTo(User::class, 'id_pengaju'); }
    public function peninjau() { return $this->belongsTo(User::class, 'id_peninjau'); }
}
