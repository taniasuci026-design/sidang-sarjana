<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $table = 'otp_codes';
    protected $primaryKey = 'id_otp';
    public const UPDATED_AT = null;
    protected $fillable = ['id_user', 'tujuan', 'kode_otp', 'kedaluwarsa_at', 'digunakan_at', 'percobaan'];
    protected $casts = ['kedaluwarsa_at' => 'datetime', 'digunakan_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class, 'id_user'); }
}
