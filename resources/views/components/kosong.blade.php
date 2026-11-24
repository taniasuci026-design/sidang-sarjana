@props(['judul' => 'Belum ada data', 'ikon' => 'bi-inbox'])
<div class="kosong"><i class="bi {{ $ikon }}"></i><b>{{ $judul }}</b>{{ $slot }}</div>
