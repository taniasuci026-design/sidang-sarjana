<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KontenLanding extends Model
{
    protected $table = 'konten_landing';
    protected $primaryKey = 'id_konten';
    public const CREATED_AT = null;
    protected $fillable = ['bagian', 'judul', 'isi', 'urutan', 'is_tampil', 'id_user'];
    protected $casts = ['is_tampil' => 'boolean'];

    public function pengubah() { return $this->belongsTo(User::class, 'id_user'); }

    /** Nilai bagian "informasi" berdasarkan kunci (kolom judul), mis. hero_judul_1. */
    public static function info(string $kunci, string $bawaan = ''): string
    {
        static $cache = null;
        if ($cache === null) {
            $cache = static::where('bagian', 'informasi')->pluck('isi', 'judul')->all();
        }
        return $cache[$kunci] ?? $bawaan;
    }
}
