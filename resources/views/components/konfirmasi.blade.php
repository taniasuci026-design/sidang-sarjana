{{-- Modal konfirmasi aksi berbahaya / penting --}}
@props(['id', 'judul', 'aksi', 'metode' => 'POST', 'tombol' => 'Ya, lanjutkan', 'bahaya' => true])
<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px">
        <form method="POST" action="{{ $aksi }}" class="modal-content text-center p-2">
            @csrf
            @if (strtoupper($metode) !== 'POST') @method($metode) @endif
            <div class="modal-body pt-4">
                <div class="ikon-bahaya" @if(!$bahaya) style="background:#DBEAFE;color:#1D4ED8" @endif><i class="bi {{ $bahaya ? 'bi-trash3' : 'bi-question-lg' }}"></i></div>
                <div class="modal-title mb-2">{{ $judul }}</div>
                <div class="text-2 small">{{ $slot }}</div>
            </div>
            <div class="d-flex gap-2 px-3 pb-3">
                <button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Batal</button>
                <button class="btn {{ $bahaya ? 'btn-danger' : 'btn-primary' }} flex-fill">{{ $tombol }}</button>
            </div>
        </form>
    </div>
</div>
