@extends('layouts.auth')
@section('title', 'Kata Sandi Baru')
@section('form')
    <div class="aksen"></div>
    <h2>Buat kata sandi baru</h2>
    <p class="text-3 mb-4">Kode berhasil diverifikasi. Buat kata sandi baru untuk akun {{ $user->email }}.</p>
    @include('partials.galat-form')
    <form method="POST" action="{{ route('lupa.baru') }}">
        @csrf
        <label class="form-label">Kata sandi baru</label>
        <div class="input-ikon mb-3"><i class="bi bi-lock"></i><input type="password" name="password" id="sandi1" class="form-control" required><button type="button" class="lihat-sandi"><i class="bi bi-eye"></i></button></div>
        <label class="form-label">Ulangi kata sandi baru</label>
        <div class="input-ikon mb-3"><i class="bi bi-lock"></i><input type="password" name="password_confirmation" id="sandi2" class="form-control" required><button type="button" class="lihat-sandi"><i class="bi bi-eye"></i></button></div>
        <ul class="list-unstyled small mb-4" id="syarat">
            <li data-s="len"><i class="bi bi-circle"></i> Minimal 8 karakter</li>
            <li data-s="mix"><i class="bi bi-circle"></i> Kombinasi huruf dan angka</li>
            <li data-s="sama"><i class="bi bi-circle"></i> Kedua kata sandi sama</li>
        </ul>
        <button class="btn btn-primary w-100 py-2">Simpan kata sandi</button>
    </form>
@endsection
@push('scripts')
<script>
const a=document.getElementById('sandi1'),b=document.getElementById('sandi2');
function cek(){const s={len:a.value.length>=8,mix:/[a-z]/i.test(a.value)&&/\d/.test(a.value),sama:a.value&&a.value===b.value};
document.querySelectorAll('#syarat li').forEach(li=>{const ok=s[li.dataset.s];li.className=ok?'text-success':'text-3';li.querySelector('i').className='bi '+(ok?'bi-check-circle-fill':'bi-circle');});}
a.addEventListener('input',cek);b.addEventListener('input',cek);
</script>
@endpush
