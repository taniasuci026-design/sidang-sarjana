<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MateriKompre extends Model
{
    use SoftDeletes;

    protected $table = 'materi_kompre';
    protected $primaryKey = 'id_materi';
    protected $fillable = ['nama_paket', 'mata_kuliah_1', 'mata_kuliah_2', 'mata_kuliah_3'];

    public function mataKuliah(): array
    {
        return [$this->mata_kuliah_1, $this->mata_kuliah_2, $this->mata_kuliah_3];
    }

    public function teks(): string
    {
        return implode(', ', $this->mataKuliah());
    }
}
