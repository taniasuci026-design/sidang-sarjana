<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';
    protected $primaryKey = 'id_pengajuan';
    protected $fillable = ['no_pengajuan', 'id_mahasiswa', 'id_periode', 'jalur', 'paket_sidang', 'id_materi', 'dosen_pembimbing_pi',
        'status_pengajuan', 'status_verifikasi', 'id_kategori', 'diajukan_at', 'id_admin_terakhir'];
    protected $casts = ['diajukan_at' => 'datetime'];

    public const STATUS = ['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'perbaikan' => 'Perbaikan', 'selesai' => 'Selesai'];
    public const STATUS_WARNA = ['menunggu' => 'warning', 'diproses' => 'primary', 'perbaikan' => 'danger', 'selesai' => 'success'];
    public const PAKET = ['skripsi' => 'Skripsi', 'kompre' => 'Komprehensif'];
    public const JALUR = ['reguler' => 'Reguler', 'transfer' => 'Transfer'];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa'); }
    public function periode() { return $this->belongsTo(PeriodePendaftaran::class, 'id_periode'); }
    public function materi() { return $this->belongsTo(MateriKompre::class, 'id_materi')->withTrashed(); }
    public function kategori() { return $this->belongsTo(KategoriHasil::class, 'id_kategori'); }
    public function adminTerakhir() { return $this->belongsTo(User::class, 'id_admin_terakhir'); }
    public function dokumen() { return $this->hasMany(DokumenPengajuan::class, 'id_pengajuan'); }
    public function catatan() { return $this->hasMany(CatatanPengajuan::class, 'id_pengajuan'); }
    public function riwayat() { return $this->hasMany(RiwayatAktivitas::class, 'id_pengajuan'); }

    public function statusLabel(): string { return self::STATUS[$this->status_pengajuan] ?? $this->status_pengajuan; }
    public function statusWarna(): string { return self::STATUS_WARNA[$this->status_pengajuan] ?? 'secondary'; }
    public function paketLabel(): string { return self::PAKET[$this->paket_sidang] ?? $this->paket_sidang; }
    public function jalurLabel(): string { return self::JALUR[$this->jalur] ?? $this->jalur; }

    /** Jenis dokumen yang dipersyaratkan sesuai paket dan jalur (FR-12). */
    public static function jenisDokumenUntuk(string $paket, string $jalur)
    {
        return JenisDokumen::query()
            ->where($paket === 'kompre' ? 'wajib_kompre' : 'wajib_skripsi', true)
            ->when($jalur !== 'transfer', fn ($q) => $q->where('khusus_transfer', false))
            ->orderBy('urutan')->get();
    }

    public function jenisDokumenWajib()
    {
        return self::jenisDokumenUntuk($this->paket_sidang, $this->jalur);
    }

    public static function nomorBaru(): string
    {
        $tahun = now()->year;
        $prefix = "SPJ-{$tahun}-";
        $terakhir = static::where('no_pengajuan', 'like', $prefix . '%')->orderByDesc('no_pengajuan')->value('no_pengajuan');
        $urut = $terakhir ? ((int) substr($terakhir, -4)) + 1 : 1;
        return $prefix . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    /** Pratinjau teks notifikasi yang akan dilihat mahasiswa. */
    public function pesanUntukMahasiswa(): array
    {
        return match ($this->status_pengajuan) {
            'menunggu' => ['Dalam antrean verifikasi', 'Pengajuan Anda berada dalam antrean verifikasi.'],
            'diproses' => ['Sedang diperiksa', 'Pengajuan Anda sedang diperiksa admin.'],
            default => [$this->kategori?->nama_kategori ?? $this->statusLabel(), $this->kategori?->teks_notifikasi ?? ''],
        };
    }
}
