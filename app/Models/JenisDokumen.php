<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    protected $table = 'jenis_dokumen';
    protected $primaryKey = 'id_jenis';
    public $timestamps = false;
    protected $fillable = ['nama_dokumen', 'keterangan', 'wajib_skripsi', 'wajib_kompre', 'khusus_transfer', 'format_file', 'ukuran_maks_mb', 'urutan'];
    protected $casts = ['wajib_skripsi' => 'boolean', 'wajib_kompre' => 'boolean', 'khusus_transfer' => 'boolean'];

    public function dokumen() { return $this->hasMany(DokumenPengajuan::class, 'id_jenis'); }

    /** Ekstensi yang diizinkan, mis. "PDF, JPG, PNG" -> ['pdf','jpg','png']. */
    public function ekstensi(): array
    {
        $daftar = array_filter(array_map(fn ($e) => strtolower(trim($e)), explode(',', $this->format_file)));
        if (in_array('jpg', $daftar, true)) {
            $daftar[] = 'jpeg';
        }
        return array_values(array_unique($daftar));
    }

    public function accept(): string
    {
        return implode(',', array_map(fn ($e) => '.' . $e, $this->ekstensi()));
    }
}
