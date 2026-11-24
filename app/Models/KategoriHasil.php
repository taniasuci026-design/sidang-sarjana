<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriHasil extends Model
{
    protected $table = 'kategori_hasil';
    protected $primaryKey = 'id_kategori';
    public const CREATED_AT = null;
    protected $fillable = ['kode', 'nama_kategori', 'teks_notifikasi', 'id_user'];

    public function pengubah() { return $this->belongsTo(User::class, 'id_user'); }
    public function pengajuan() { return $this->hasMany(Pengajuan::class, 'id_kategori'); }

    /** Warna badge per kode kategori. */
    public function warna(): string
    {
        return ['1' => 'success', '2a' => 'warning', '2b' => 'danger', '3' => 'indigo'][$this->kode] ?? 'secondary';
    }

    public function label(): string
    {
        return 'Kategori ' . $this->kode;
    }
}
