@extends('layouts.auth')
@section('title', 'Verifikasi Kode')
@section('form')
    @php
        $reset = $mode === 'reset';
        [$depan, $domain] = array_pad(explode('@', $user->email), 2, '');
        $samar = mb_substr($depan, 0, 1) . str_repeat('*', max(mb_strlen($depan) - 2, 1)) . mb_substr($depan, -1) . '@' . $domain;
    @endphp
    <a href="{{ route($reset ? 'lupa' : 'daftar') }}" class="tautan-kembali d-inline-block mb-3">← Kembali</a>
    <div class="aksen"></div>
    <h2>Masukkan kode verifikasi</h2>
    <p class="text-3 mb-4">Kode 6 digit telah dikirim ke <b>{{ $samar }}</b></p>
    @include('partials.otp-dev')
    @include('partials.galat-form')
    <form method="POST" action="{{ $reset ? route('lupa.kode') : route('otp.verifikasi') }}" id="formOtp">
        @csrf
        <input type="hidden" name="kode" id="kodeOtp">
        <div class="kotak-otp mb-3" aria-label="Kode OTP">
            @for ($i = 0; $i < 6; $i++)<input inputmode="numeric" maxlength="1" aria-label="Digit {{ $i + 1 }}" @if($i===0) autofocus @endif>@endfor
        </div>
        <button class="btn btn-primary w-100 py-2 mt-2">Verifikasi</button>
    </form>
    <form method="POST" action="{{ $reset ? route('lupa') : route('otp.kirim-ulang') }}" class="text-center mt-3">
        @csrf
        @if ($reset)<input type="hidden" name="email" value="{{ $user->email }}">@endif
        <span class="small text-3">Belum menerima kode?</span> <button class="btn btn-link btn-sm p-0 fw-semibold">Kirim ulang kode</button>
    </form>
    <p class="text-center small mt-3"><a href="{{ route('login') }}">Masuk dengan kata sandi</a></p>
@endsection
@push('scripts')
<script>
const kotak=[...document.querySelectorAll('.kotak-otp input')];
kotak.forEach((k,i)=>{
  k.addEventListener('input',()=>{k.value=k.value.replace(/\D/g,'').slice(-1); if(k.value&&kotak[i+1]) kotak[i+1].focus();});
  k.addEventListener('keydown',e=>{if(e.key==='Backspace'&&!k.value&&kotak[i-1]) kotak[i-1].focus();});
  k.addEventListener('paste',e=>{const t=(e.clipboardData.getData('text')||'').replace(/\D/g,'').slice(0,6); if(!t) return; e.preventDefault(); t.split('').forEach((c,j)=>{if(kotak[j]) kotak[j].value=c;}); (kotak[t.length]||kotak[5]).focus();});
});
document.getElementById('formOtp').addEventListener('submit',()=>{document.getElementById('kodeOtp').value=kotak.map(k=>k.value).join('');});
</script>
@endpush
