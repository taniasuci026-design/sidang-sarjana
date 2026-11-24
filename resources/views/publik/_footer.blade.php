@php use App\Models\KontenLanding as K; @endphp
<footer class="footer-publik">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-2"><span class="avatar"><i class="bi bi-mortarboard-fill"></i></span><b class="text-navy">SIDANG SARJANA</b></div>
                <p class="small text-2" style="max-width:380px">Sistem Informasi Pendaftaran dan Pengelolaan Sidang Sarjana Program Studi Sistem Informasi Universitas Gunadarma.</p>
            </div>
            <div class="col-6 col-lg-3">
                <div class="small fw-bold text-navy mb-2">LAYANAN</div>
                <a href="{{ route('daftar') }}" class="d-block small text-2 mb-1">Pendaftaran Sidang</a>
                <a href="{{ route('cek-status') }}" class="d-block small text-2">Cek Status Berkas</a>
            </div>
            <div class="col-lg-4">
                <div class="small fw-bold text-navy mb-2">KONTAK KAMI</div>
                <div class="small text-2 mb-1"><i class="bi bi-geo-alt text-oranye me-1"></i>{{ K::info('kontak_alamat') }}</div>
                <div class="small text-2 mb-1"><i class="bi bi-envelope text-oranye me-1"></i>{{ K::info('kontak_email') }}</div>
                <div class="small text-2"><i class="bi bi-telephone text-oranye me-1"></i>{{ K::info('kontak_telepon') }}</div>
            </div>
        </div>
        <div class="border-top mt-4 pt-3 small text-3">© {{ date('Y') }} Universitas Gunadarma.</div>
    </div>
</footer>
