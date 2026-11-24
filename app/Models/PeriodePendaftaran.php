<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodePendaftaran extends Model
{
    protected $table = 'periode_pendaftaran';
    protected $primaryKey = 'id_periode';
    protected $fillable = ['nama_periode', 'tanggal_buka', 'tanggal_tutup', 'is_dibuka', 'tautan_google_form', 'id_user'];
    protected $casts = ['tanggal_buka' => 'date', 'tanggal_tutup' => 'date', 'is_dibuka' => 'boolean'];

    public function pengubah() { return $this->belongsTo(User::class, 'id_user'); }

    /** Periode yang berlaku (terbaru). */
    public static function aktif(): ?self
    {
        return static::orderByDesc('id_periode')->first();
    }

    /** Pendaftaran dapat diakses jika saklar dibuka dan tanggal hari ini dalam rentang periode (FR-34, FR-35). */
    public function sedangDibuka(): bool
    {
        $hari = now()->startOfDay();
        return $this->is_dibuka && $hari->betweenIncluded($this->tanggal_buka->copy()->startOfDay(), $this->tanggal_tutup->copy()->endOfDay());
    }
}
