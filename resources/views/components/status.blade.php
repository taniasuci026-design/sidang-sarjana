@props(['warna' => 'secondary', 'polos' => false])
<span {{ $attributes->merge(['class' => 'status status-'.$warna.($polos ? ' polos' : '')]) }}>{{ $slot }}</span>
