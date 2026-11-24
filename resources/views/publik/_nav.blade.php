<div class="nav-publik">
    <a href="{{ route('landing') }}" class="d-flex align-items-center gap-2 text-white">
        <span class="avatar" style="background:#fff"><i class="bi bi-mortarboard-fill text-navy fs-5"></i></span>
        <span class="lh-sm"><span class="d-block fw-bold">SIDANG</span><span class="d-block" style="font-size:10px;letter-spacing:.06em;color:#BFD0F5">UNIVERSITAS GUNADARMA</span></span>
    </a>
    <div class="d-flex gap-2">
        @auth
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-oranye btn-sm px-3"><i class="bi bi-grid me-1"></i>Dashboard</a>
        @else
            <a href="{{ route('daftar') }}" class="btn btn-outline-light btn-sm px-3">Daftar</a>
            <a href="{{ route('login') }}" class="btn btn-oranye btn-sm px-3"><i class="bi bi-box-arrow-in-right me-1"></i>Masuk</a>
        @endauth
    </div>
</div>
