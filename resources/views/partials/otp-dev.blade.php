@if (session('otp_dev'))
    <div class="info-banner kuning mb-3"><i class="bi bi-tools"></i><div><b>Mode pengembangan:</b> email dicatat ke log, bukan dikirim. Kode OTP: <code class="fs-6">{{ session('otp_dev') }}</code></div></div>
@endif
