<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $fillable = ['nama', 'email', 'no_whatsapp', 'password', 'role', 'is_active', 'email_verified_at', 'last_login_at', 'foto_profil'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public const ROLE_LABEL = ['mahasiswa' => 'Mahasiswa', 'admin' => 'Admin', 'super_admin' => 'Superadmin'];

    public function mahasiswa() { return $this->hasOne(Mahasiswa::class, 'id_user'); }
    public function notifikasi() { return $this->hasMany(Notifikasi::class, 'id_user'); }

    public function isMahasiswa(): bool { return $this->role === 'mahasiswa'; }
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isStaf(): bool { return in_array($this->role, ['admin', 'super_admin'], true); }

    public function roleLabel(): string { return self::ROLE_LABEL[$this->role] ?? $this->role; }

    public function inisial(): string
    {
        $bagian = preg_split('/\s+/', trim($this->nama));
        $i = mb_substr($bagian[0] ?? '', 0, 1) . mb_substr($bagian[1] ?? '', 0, 1);
        return mb_strtoupper($i ?: '?');
    }

    public function jumlahNotifikasiBaru(): int
    {
        return $this->notifikasi()->whereNull('dibaca_at')->count();
    }

    public function dashboardRoute(): string
    {
        return $this->isMahasiswa() ? 'mahasiswa.dashboard' : 'admin.dashboard';
    }
}
