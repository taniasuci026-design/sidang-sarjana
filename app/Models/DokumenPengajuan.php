<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPengajuan extends Model
{
    protected $table = 'dokumen_pengajuan';
    protected $primaryKey = 'id_dokumen';
    public $timestamps = false;
    protected $fillable = ['id_pengajuan', 'id_jenis', 'nama_file', 'path_file', 'ukuran_kb', 'status_dokumen', 'catatan_pemeriksaan', 'uploaded_at'];
    protected $casts = ['uploaded_at' => 'datetime'];

    public const STATUS = ['menunggu' => 'Menunggu', 'lengkap' => 'Lengkap', 'perlu_perbaikan' => 'Perlu Perbaikan'];
    public const STATUS_WARNA = ['menunggu' => 'secondary', 'lengkap' => 'success', 'perlu_perbaikan' => 'danger'];

    public function pengajuan() { return $this->belongsTo(Pengajuan::class, 'id_pengajuan'); }
    public function jenis() { return $this->belongsTo(JenisDokumen::class, 'id_jenis'); }

    public function statusLabel(): string { return self::STATUS[$this->status_dokumen] ?? $this->status_dokumen; }
    public function statusWarna(): string { return self::STATUS_WARNA[$this->status_dokumen] ?? 'secondary'; }
}
