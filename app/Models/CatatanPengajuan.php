<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatatanPengajuan extends Model
{
    protected $table = 'catatan_pengajuan';
    protected $primaryKey = 'id_catatan';
    public const UPDATED_AT = null;
    protected $fillable = ['id_pengajuan', 'id_user', 'isi_catatan', 'terlihat_mahasiswa'];
    protected $casts = ['terlihat_mahasiswa' => 'boolean'];

    public function pengajuan() { return $this->belongsTo(Pengajuan::class, 'id_pengajuan'); }
    public function penulis() { return $this->belongsTo(User::class, 'id_user'); }
}
