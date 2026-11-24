<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>Laporan Pengajuan Sidang</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<style>body{background:#fff;padding:24px;font-size:11px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #CBD5E1;padding:5px 6px;text-align:left;vertical-align:top}th{background:#EAF0FC;color:#1E3A8A}h1{font-size:18px;color:#1E3A8A;margin:0}@page{size:A4 landscape;margin:12mm}.no-print{margin-bottom:12px}@media print{.no-print{display:none}}</style></head>
<body>
<div class="no-print"><button onclick="window.print()" class="btn btn-primary btn-sm">Cetak / Simpan PDF</button></div>
<h1>Laporan Pengajuan Sidang Sarjana</h1>
<div style="margin:4px 0 12px;color:#475569">Program Studi Sistem Informasi · Universitas Gunadarma · Dicetak {{ now()->translatedFormat('d M Y H:i') }} oleh {{ auth()->user()->nama }}
    @if (array_filter($filter)) · Filter: @foreach (array_filter($filter) as $k => $v){{ $k }}={{ $v }} @endforeach @endif · Total {{ $pengajuan->count() }} pengajuan</div>
<table><thead><tr><th>No</th>@foreach (array_diff($kolom, ['Email', 'WhatsApp']) as $k)<th>{{ $k }}</th>@endforeach</tr></thead><tbody>
@forelse ($pengajuan as $i => $p)<tr><td>{{ $i + 1 }}</td><td>{{ $p->no_pengajuan }}</td><td>{{ $p->diajukan_at?->format('d/m/Y H:i') }}</td><td>{{ $p->mahasiswa->npm }}</td><td>{{ $p->mahasiswa->nama_lengkap }}</td>
<td>{{ $p->jalurLabel() }}</td><td>{{ $p->paketLabel() }}</td><td>{{ $p->materi?->teks() ?? '-' }}</td><td>{{ $p->dosen_pembimbing_pi ?? '-' }}</td><td>{{ $p->statusLabel() }}</td>
<td>{{ $p->kategori ? $p->kategori->label() . ' - ' . $p->kategori->nama_kategori : 'Belum Diperiksa' }}</td><td>{{ $p->updated_at?->format('d/m/Y H:i') }}</td></tr>
@empty<tr><td colspan="12" style="text-align:center">Tidak ada data.</td></tr>@endforelse
</tbody></table>
<script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
</body></html>
