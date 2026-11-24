@props(['k' => null])
@if ($k)
    <x-status :warna="$k->warna()" polos>{{ $k->nama_kategori }}</x-status>
@else
    <span class="text-3">Belum Diperiksa</span>
@endif
