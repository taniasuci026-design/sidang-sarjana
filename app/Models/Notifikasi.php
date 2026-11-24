<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id_notifikasi';
    public const UPDATED_AT = null;
    protected $fillable = ['id_user', 'id_pengajuan', 'jenis', 'judul', 'pesan', 'dibaca_at'];
    protected $casts = ['dibaca_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class, 'id_user'); }
    public function pengajuan() { return $this->belongsTo(Pengajuan::class, 'id_pengajuan'); }
}
