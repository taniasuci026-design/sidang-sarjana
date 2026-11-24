<div style="font-family:Arial,sans-serif;max-width:480px;margin:auto;color:#1E293B">
    <h2 style="color:#1E3A8A">Sidang Sarjana · Universitas Gunadarma</h2>
    <p>Halo {{ $user->nama }},</p>
    <p>{{ $tujuan === 'registrasi' ? 'Gunakan kode berikut untuk memverifikasi akun Anda:' : 'Gunakan kode berikut untuk mengatur ulang kata sandi Anda:' }}</p>
    <p style="font-size:32px;font-weight:bold;letter-spacing:8px;color:#2563EB">{{ $kode }}</p>
    <p>Kode berlaku {{ config('sidang.otp.berlaku_menit') }} menit. Jangan bagikan kode ini kepada siapa pun.</p>
</div>
