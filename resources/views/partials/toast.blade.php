<div class="toast-wadah" id="toastWadah">
    @if (session('sukses'))
        <div class="toast-x" role="status"><div class="fw-bold text-success">Berhasil</div><div class="text-2 mt-1">{{ session('sukses') }}</div></div>
    @endif
    @if (session('galat'))
        <div class="toast-x galat" role="alert"><div class="fw-bold text-danger">Gagal</div><div class="text-2 mt-1">{{ session('galat') }}</div></div>
    @endif
</div>
