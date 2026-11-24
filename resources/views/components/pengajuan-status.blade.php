@props(['p'])
<x-status :warna="$p->statusWarna()">{{ $p->statusLabel() }}</x-status>
