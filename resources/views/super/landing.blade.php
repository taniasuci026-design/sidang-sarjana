@extends('layouts.portal')
@section('title', 'Kelola Landing Page')
@section('content')
<h1 class="judul-halaman">Kelola Landing Page</h1>
<div class="sub-halaman mb-3">Ubah isi halaman depan yang dilihat mahasiswa. Cek hasilnya di preview sebelum disimpan.</div>
<div class="row g-3">
    <div class="col-xl-6">
        <form method="POST" action="{{ route('super.landing.informasi') }}" class="kartu mb-3" id="formInfo">
            @csrf @method('PUT')
            <div class="kartu-judul">Bagian Utama</div><div class="kartu-sub mb-3">Teks di bagian paling atas halaman.</div>
            @foreach (['hero_label', 'hero_judul_1', 'hero_judul_2', 'hero_deskripsi', 'hero_tombol'] as $k)
                <div class="mb-3"><label class="form-label">{{ $label[$k] }}</label>
                    @if ($k === 'hero_deskripsi')<textarea name="{{ $k }}" rows="3" class="form-control" data-pratinjau="{{ $k }}" maxlength="500" required>{{ old($k, $info[$k] ?? '') }}</textarea>
                    @else<input name="{{ $k }}" value="{{ old($k, $info[$k] ?? '') }}" class="form-control" data-pratinjau="{{ $k }}" maxlength="150" required>@endif</div>
            @endforeach
            <div class="kartu-judul mt-4">Kontak Kami</div><div class="kartu-sub mb-3">Informasi kontak di bagian bawah halaman.</div>
            @foreach (['kontak_alamat', 'kontak_email', 'kontak_telepon'] as $k)
                <div class="mb-3"><label class="form-label">{{ $label[$k] }}</label><input name="{{ $k }}" value="{{ old($k, $info[$k] ?? '') }}" class="form-control" maxlength="{{ $k === 'kontak_alamat' ? 500 : 150 }}" required></div>
            @endforeach
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-3">@if ($terakhir)Terakhir disimpan: {{ $terakhir->updated_at?->translatedFormat('d M Y, H:i') }} oleh {{ $terakhir->pengubah?->nama }}@endif</span>
                <div class="d-flex gap-2"><a href="{{ route('super.landing.index') }}" class="btn btn-light border">Batal</a><button class="btn btn-primary">Simpan Perubahan</button></div>
            </div>
        </form>

        @foreach (['alur' => ['Alur Pengajuan', 'Langkah yang tampil di bagian “Alur Pengajuan”.', '+ Tambah langkah'], 'faq' => ['FAQ Pendaftaran Sidang', 'Pertanyaan yang tampil di bagian FAQ.', '+ Tambah pertanyaan']] as $bagian => [$judul, $sub, $tombol])
            <div class="kartu mb-3">
                <div class="d-flex justify-content-between align-items-start gap-2"><div><div class="kartu-judul">{{ $judul }}</div><div class="kartu-sub mb-3">{{ $sub }}</div></div>
                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalKonten" data-isi="{{ json_encode(['_aksi' => route('super.landing.store'), '_method' => 'POST', 'bagian' => $bagian, 'judul' => '', 'isi' => '', 'urutan' => '', 'is_tampil' => true, 'judulModal' => $bagian === 'faq' ? 'Tambah Pertanyaan FAQ' : 'Tambah Langkah Alur']) }}">{{ $tombol }}</button></div>
                <div class="border rounded-3 overflow-hidden">
                    @forelse (${$bagian} as $i => $k)
                        <div class="d-flex gap-3 p-3 {{ $i % 2 ? '' : 'bg-light' }}">
                            @if ($bagian === 'alur')<span class="alur-item p-0 border-0 bg-transparent m-0"><span class="no">{{ $i + 1 }}</span></span>@endif
                            <div class="flex-fill"><div class="fw-semibold small">{{ $k->judul }} @unless($k->is_tampil)<x-status warna="secondary" polos>Disembunyikan</x-status>@endunless</div><div class="small text-3" style="white-space:pre-line">{{ $k->isi }}</div></div>
                            <div class="text-nowrap"><button class="btn btn-link btn-sm p-0 me-2" data-bs-toggle="modal" data-bs-target="#modalKonten" data-isi="{{ json_encode(['_aksi' => route('super.landing.update', $k), '_method' => 'PUT', 'bagian' => $bagian, 'judul' => $k->judul, 'isi' => $k->isi, 'urutan' => $k->urutan, 'is_tampil' => $k->is_tampil, 'judulModal' => $bagian === 'faq' ? 'Ubah Pertanyaan FAQ' : 'Ubah Langkah Alur']) }}">Ubah</button>
                                <button class="btn btn-link btn-sm p-0 text-danger" data-bs-toggle="modal" data-bs-target="#modalHapusKonten" data-isi="{{ json_encode(['_aksi' => route('super.landing.destroy', $k), 'judul' => $k->judul, 'isi' => $k->isi]) }}">Hapus</button></div>
                        </div>
                    @empty
                        <div class="p-3 small text-3">Belum ada konten.</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
    <div class="col-xl-6">
        <div class="kartu" style="position:sticky;top:84px">
            <div class="d-flex justify-content-between align-items-center"><div class="kartu-judul">Preview Halaman Mahasiswa</div><a href="{{ route('landing') }}" target="_blank" class="small">Buka halaman <i class="bi bi-box-arrow-up-right"></i></a></div>
            <div class="kartu-sub mb-3">Bagian utama mengikuti isian di sebelah kiri sebelum disimpan.</div>
            <div class="border rounded-3 overflow-hidden" style="height:72vh"><iframe src="{{ route('landing') }}" id="pratinjauLanding" title="Preview landing page" style="width:166.7%;height:166.7%;border:0;transform:scale(.6);transform-origin:0 0"></iframe></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKonten" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:560px"><form method="POST" class="modal-content">@csrf<input type="hidden" name="_method" value="POST"><input type="hidden" name="bagian">
    <div class="modal-header"><div class="modal-title" data-teks="judulModal"></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="mb-3"><label class="form-label">Pertanyaan / judul <span class="wajib">*</span></label><input name="judul" class="form-control" required maxlength="150" placeholder="Contoh: Kapan jadwal sidang diumumkan?"></div>
        <div class="mb-3"><label class="form-label">Jawaban / isi <span class="wajib">*</span></label><textarea name="isi" rows="5" class="form-control" required maxlength="1000" placeholder="Tulis jawaban untuk pertanyaan ini"></textarea><div class="form-text">Tekan Enter untuk membuat baris baru.</div></div>
        <div class="row g-2 align-items-center"><div class="col-4"><label class="form-label">Urutan</label><input type="number" name="urutan" min="1" max="99" class="form-control"></div>
            <div class="col-8 pt-4"><input type="hidden" name="is_tampil" value="0"><label class="small"><input type="checkbox" name="is_tampil" value="1" class="form-check-input me-1">Tampilkan di landing page</label></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
</form></div></div>
<div class="modal fade" id="modalHapusKonten" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:440px"><form method="POST" class="modal-content text-center">@csrf @method('DELETE')
    <div class="modal-body pt-4"><div class="ikon-bahaya"><i class="bi bi-trash3"></i></div><div class="modal-title">Hapus konten ini?</div>
        <div class="bg-light border rounded p-2 small mt-3 text-start"><b data-teks="judul"></b><div class="text-3" data-teks="isi" style="white-space:pre-line"></div></div>
        <div class="small text-2 mt-2">Konten tidak akan muncul lagi di landing page.</div></div>
    <div class="d-flex gap-2 px-3 pb-3"><button type="button" class="btn btn-light border flex-fill" data-bs-dismiss="modal">Batal</button><button class="btn btn-danger flex-fill">Hapus</button></div>
</form></div></div>
@endsection
@push('scripts')
<script>
const frame = document.getElementById('pratinjauLanding');
function perbaruiPratinjau() {
  const doc = frame.contentDocument; if (!doc) return;
  document.querySelectorAll('[data-pratinjau]').forEach(el => doc.querySelectorAll(`[data-l="${el.dataset.pratinjau}"]`).forEach(t => t.textContent = el.value));
}
frame.addEventListener('load', perbaruiPratinjau);
document.querySelectorAll('[data-pratinjau]').forEach(el => el.addEventListener('input', perbaruiPratinjau));
</script>
@endpush
