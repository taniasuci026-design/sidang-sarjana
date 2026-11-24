@extends('layouts.portal')
@section('title', 'Pengajuan Sidang')
@section('content')
@php $langkah = ['Data Mahasiswa', 'Data Sidang', 'Upload Dokumen', 'Konfirmasi']; @endphp
<div class="label-atas">Pendaftaran Sidang Sarjana · {{ $periode->nama_periode }}</div>
<h1 class="judul-halaman">Pengajuan Sidang Sarjana</h1>
<div class="sub-halaman mb-3">Lengkapi data berikut untuk mengajukan sidang sarjana.</div>

<div class="stepper mb-4" id="stepper">
    @foreach ($langkah as $i => $l)
        @if ($i)<span class="penghubung" data-penghubung="{{ $i }}"></span>@endif
        <span class="langkah" data-langkah="{{ $i }}"><span class="bulat">0{{ $i + 1 }}</span>{{ $l }}</span>
    @endforeach
</div>

<form method="POST" action="{{ route('mahasiswa.pengajuan.store') }}" enctype="multipart/form-data" id="formPengajuan" novalidate>
    @csrf
    {{-- 01 Data Mahasiswa --}}
    <section class="langkah-isi" data-isi="0">
        <div class="mb-3"><span class="text-3">01 /</span> <span class="kartu-judul">Data Mahasiswa</span><div class="kartu-sub">Pastikan informasi yang Anda isi sudah benar.</div></div>
        <div class="row g-3" style="max-width:820px">
            <div class="col-md-6"><label class="form-label">Nama Lengkap <span class="wajib">*</span></label>
                <input name="nama_lengkap" value="{{ old('nama_lengkap', $mhs->nama_lengkap ?? $user->nama) }}" class="form-control @error('nama_lengkap') is-invalid @enderror" required maxlength="100"></div>
            <div class="col-md-6"><label class="form-label">NPM <span class="wajib">*</span></label>
                <input name="npm" value="{{ old('npm', $mhs->npm ?? '') }}" class="form-control @error('npm') is-invalid @enderror" required inputmode="numeric" maxlength="{{ config('sidang.npm.panjang') }}" pattern="\d{{ '{'.config('sidang.npm.panjang').'}' }}" @readonly($mhs)>
                <div class="form-text">Program studi, fakultas, dan angkatan terbaca otomatis dari NPM. @if($mhs) NPM dapat diubah lewat “Ubah Data” selama pengajuan Menunggu. @endif</div>
                @error('npm')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label">Email Aktif</label><input value="{{ $user->email }}" class="form-control bg-light" disabled><div class="form-text">Email akun Anda.</div></div>
            <div class="col-md-6"><label class="form-label">Kontak WhatsApp Aktif <span class="wajib">*</span></label>
                <input name="no_whatsapp" value="{{ old('no_whatsapp', $user->no_whatsapp) }}" class="form-control @error('no_whatsapp') is-invalid @enderror" required inputmode="numeric" pattern="(08|628)\d{7,12}"></div>
        </div>
    </section>

    {{-- 02 Data Sidang --}}
    <section class="langkah-isi d-none" data-isi="1">
        <div class="mb-3"><span class="text-3">02 /</span> <span class="kartu-judul">Data Sidang</span><div class="kartu-sub">Pastikan informasi yang Anda isi sudah benar.</div></div>
        @if ($pengajuanKedua)
            <div class="info-banner biru mb-3" style="max-width:820px"><i class="bi bi-info-circle"></i><div>Ini pengajuan kedua Anda. Paket yang tersedia hanya Sidang Komprehensif.</div></div>
        @endif
        <div style="max-width:820px">
            <label class="form-label">Jalur Mahasiswa <span class="wajib">*</span></label>
            <div class="d-flex gap-2 flex-wrap mb-3">
                @foreach (['reguler' => 'Reguler', 'transfer' => 'Transfer'] as $v => $l)
                    <label class="pilihan" style="min-width:180px"><input type="radio" name="jalur" value="{{ $v }}" @checked(old('jalur') === $v) required> {{ $l }}</label>
                @endforeach
            </div>
            <div class="mb-3 d-none" id="grupDosen"><label class="form-label">Nama Dosen Pembimbing Penulisan Ilmiah (beserta gelar) <span class="wajib">*</span></label>
                <input name="dosen_pembimbing_pi" value="{{ old('dosen_pembimbing_pi') }}" class="form-control" placeholder="Contoh: Dr. Naeli Umniati, ST., MMSI." maxlength="100"></div>
            <label class="form-label">Paket Sidang <span class="wajib">*</span></label>
            <div class="d-flex gap-2 flex-wrap mb-3">
                @foreach (['skripsi' => 'Skripsi', 'kompre' => 'Komprehensif'] as $v => $l)
                    @php $ada = in_array($v, $paketTersedia, true); @endphp
                    <label class="pilihan" style="min-width:180px"><input type="radio" name="paket_sidang" value="{{ $v }}" @checked(old('paket_sidang', count($paketTersedia) === 1 ? $paketTersedia[0] : null) === $v) @disabled(! $ada) required> {{ $l }} @unless($ada)<span class="small text-3">Sudah diajukan</span>@endunless</label>
                @endforeach
            </div>
            <div class="d-none" id="grupMateri"><label class="form-label">Materi Sidang Komprehensif <span class="wajib">*</span></label>
                @forelse ($materi as $m)
                    <label class="pilihan mb-2"><input type="radio" name="id_materi" value="{{ $m->id_materi }}" data-teks="{{ $m->teks() }}" @checked(old('id_materi') == $m->id_materi)> <span><b class="text-navy">{{ $m->nama_paket }}:</b> {{ $m->teks() }}</span></label>
                @empty
                    <div class="info-banner kuning">Belum ada paket materi. Hubungi admin prodi.</div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- 03 Upload Dokumen --}}
    <section class="langkah-isi d-none" data-isi="2">
        <div class="mb-3"><span class="text-3">03 /</span> <span class="kartu-judul">Upload Dokumen</span><div class="kartu-sub">Unggah dokumen sesuai format dan ukuran yang tertera.</div></div>
        <div style="max-width:820px" class="d-grid gap-2">
            @foreach ($jenis as $j)
                <div class="unggah d-none @error('berkas.'.$j->id_jenis) galat @enderror" data-dokumen data-ws="{{ (int) $j->wajib_skripsi }}" data-wk="{{ (int) $j->wajib_kompre }}" data-tr="{{ (int) $j->khusus_transfer }}" data-nama="{{ $j->nama_dokumen }}" data-maks="{{ $j->ukuran_maks_mb }}" data-ext="{{ implode(',', $j->ekstensi()) }}">
                    <span class="ikon"><i class="bi bi-cloud-arrow-up"></i></span>
                    <div class="flex-fill">
                        <div class="fw-bold">{{ $j->nama_dokumen }} <span class="wajib text-danger">*</span></div>
                        <div class="small text-3">{{ $j->keterangan }} <span class="text-nowrap">· {{ $j->format_file }}, maks. {{ $j->ukuran_maks_mb }} MB</span></div>
                        <div class="nama-file d-none"></div>
                        @error('berkas.'.$j->id_jenis)<div class="small text-danger">{{ $message }}</div>@enderror
                        <div class="small text-danger d-none pesan-galat"></div>
                    </div>
                    <label class="btn btn-outline-primary btn-sm mb-0"><span class="lbl-tombol">Pilih {{ strtoupper(explode(',', $j->format_file)[0]) }}</span>
                        <input type="file" name="berkas[{{ $j->id_jenis }}]" accept="{{ $j->accept() }}" class="d-none" disabled></label>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 04 Konfirmasi --}}
    <section class="langkah-isi d-none" data-isi="3">
        <div class="mb-3"><span class="text-3">04 /</span> <span class="kartu-judul">Konfirmasi</span><div class="kartu-sub">Pastikan informasi yang Anda isi sudah benar.</div></div>
        <div class="kartu" style="max-width:820px">
            <div class="row g-3" id="ringkasan"></div>
            <div class="d-flex flex-wrap gap-2 mt-3" id="ringkasanDokumen"></div>
            <label class="d-flex gap-2 mt-4 small"><input type="checkbox" name="pernyataan" value="1" class="form-check-input" required> Saya menyatakan data dan dokumen yang diajukan benar.</label>
        </div>
    </section>

    <hr class="my-4" style="max-width:820px">
    <div class="d-flex justify-content-between align-items-center" style="max-width:820px">
        <button type="button" class="btn btn-light border" id="tblSebelum"><i class="bi bi-arrow-left me-1"></i>Sebelumnya</button>
        <span class="small text-3" id="infoLangkah">Langkah 1 dari 4</span>
        <button type="button" class="btn btn-primary" id="tblLanjut">Selanjutnya <i class="bi bi-arrow-right ms-1"></i></button>
        <button type="button" class="btn btn-primary d-none" id="tblKirim" data-bs-toggle="modal" data-bs-target="#modalKirim"><i class="bi bi-check2 me-1"></i>Kirim Pengajuan</button>
    </div>

    <div class="modal fade" id="modalKirim" tabindex="-1"><div class="modal-dialog modal-dialog-centered" style="max-width:420px"><div class="modal-content">
        <div class="modal-body pt-4"><div class="ikon-bahaya" style="background:#FFEDD5;color:#F97316;margin:0 0 12px"><i class="bi bi-send"></i></div>
            <div class="modal-title">Kirim pengajuan sidang?</div><div class="small text-2 mt-1">Pastikan seluruh data dan dokumen sudah benar sebelum dikirim ke operator.</div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary" id="tblKirimFinal">Kirim Pengajuan</button></div>
    </div></div></div>
</form>
@endsection
@push('scripts')
<script>
(() => {
  const f = document.getElementById('formPengajuan'), isi = [...f.querySelectorAll('.langkah-isi')];
  const nLangkah = isi.length; let aktif = 0;
  @if ($errors->hasAny(['jalur','paket_sidang','id_materi','dosen_pembimbing_pi'])) aktif = 1; @endif
  @if (collect($errors->keys())->contains(fn($k) => str_starts_with($k, 'berkas'))) aktif = 2; @endif
  @if ($errors->has('pernyataan')) aktif = 3; @endif
  const val = n => (f.querySelector(`[name="${n}"]:checked`) || f.querySelector(`[name="${n}"]`) || {}).value || '';
  function sinkron() {
    const jalur = val('jalur'), paket = f.querySelector('[name="paket_sidang"]:checked')?.value || '';
    document.getElementById('grupDosen').classList.toggle('d-none', jalur !== 'transfer');
    document.getElementById('grupMateri').classList.toggle('d-none', paket !== 'kompre');
    f.querySelectorAll('[data-dokumen]').forEach(d => {
      const tampil = paket && (paket === 'kompre' ? d.dataset.wk === '1' : d.dataset.ws === '1') && (d.dataset.tr !== '1' || jalur === 'transfer');
      d.classList.toggle('d-none', !tampil); d.querySelector('input[type=file]').disabled = !tampil;
    });
  }
  function tampil() {
    isi.forEach((s, i) => s.classList.toggle('d-none', i !== aktif));
    f.querySelectorAll('[data-langkah]').forEach(l => { const i = +l.dataset.langkah; l.classList.toggle('aktif', i === aktif); l.classList.toggle('selesai', i < aktif); l.querySelector('.bulat').innerHTML = i < aktif ? '<i class="bi bi-check-lg"></i>' : '0' + (i + 1); });
    f.querySelectorAll('[data-penghubung]').forEach(p => p.classList.toggle('selesai', +p.dataset.penghubung <= aktif));
    document.getElementById('infoLangkah').textContent = `Langkah ${aktif + 1} dari ${nLangkah}`;
    document.getElementById('tblSebelum').style.visibility = aktif ? 'visible' : 'hidden';
    document.getElementById('tblLanjut').classList.toggle('d-none', aktif === nLangkah - 1);
    document.getElementById('tblKirim').classList.toggle('d-none', aktif !== nLangkah - 1);
    if (aktif === nLangkah - 1) ringkas();
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
  function galat(el, pesan) { el.classList.toggle('is-invalid', !!pesan); return !pesan; }
  function cekLangkah(i) {
    let ok = true; const s = isi[i];
    if (i === 0) {
      s.querySelectorAll('input[required]').forEach(el => { ok = galat(el, !el.value.trim() || (el.pattern && !new RegExp('^' + el.pattern + '$').test(el.value)) ? 'x' : '') && ok; });
    }
    if (i === 1) {
      if (!val('jalur') || !f.querySelector('[name=jalur]:checked')) { ok = false; alertKecil(s, 'Pilih jalur mahasiswa.'); }
      else if (!f.querySelector('[name=paket_sidang]:checked')) { ok = false; alertKecil(s, 'Pilih paket sidang.'); }
      else if (val('jalur') === 'transfer' && !f.querySelector('[name=dosen_pembimbing_pi]').value.trim()) { ok = galat(f.querySelector('[name=dosen_pembimbing_pi]'), 'x'); alertKecil(s, 'Nama dosen pembimbing PI wajib diisi untuk jalur transfer.'); }
      else if (f.querySelector('[name=paket_sidang]:checked').value === 'kompre' && !f.querySelector('[name=id_materi]:checked')) { ok = false; alertKecil(s, 'Pilih materi sidang komprehensif.'); }
      else alertKecil(s, '');
    }
    if (i === 2) {
      s.querySelectorAll('[data-dokumen]:not(.d-none)').forEach(d => { const inp = d.querySelector('input[type=file]'); const p = d.querySelector('.pesan-galat');
        const kosong = !inp.files.length; p.textContent = kosong ? 'Dokumen wajib belum diunggah.' : ''; p.classList.toggle('d-none', !kosong); d.classList.toggle('galat', kosong); if (kosong) ok = false; });
    }
    return ok;
  }
  function alertKecil(s, pesan) { let a = s.querySelector('.alert-kecil'); if (!a) { a = document.createElement('div'); a.className = 'alert-kecil info-banner merah mt-2'; a.style.maxWidth = '820px'; s.appendChild(a); } a.textContent = pesan; a.classList.toggle('d-none', !pesan); }
  function ringkas() {
    const materi = f.querySelector('[name=id_materi]:checked'), paket = f.querySelector('[name=paket_sidang]:checked')?.value;
    const baris = [['Nama', val('nama_lengkap')], ['NPM', val('npm')], ['Email', @json($user->email)], ['WhatsApp', val('no_whatsapp')],
      ['Jalur', val('jalur') === 'transfer' ? 'Transfer' : 'Reguler'], ['Paket Sidang', paket === 'kompre' ? 'Komprehensif' : 'Skripsi']];
    if (val('jalur') === 'transfer') baris.push(['Dosen Pembimbing PI', f.querySelector('[name=dosen_pembimbing_pi]').value]);
    if (paket === 'kompre' && materi) baris.push(['Materi', materi.dataset.teks]);
    document.getElementById('ringkasan').innerHTML = baris.map(([k, v]) => `<div class="col-md-6"><div class="small fw-semibold text-oranye">${k}</div><div class="text-primary fw-semibold">${String(v).replace(/</g,'&lt;')}</div></div>`).join('');
    document.getElementById('ringkasanDokumen').innerHTML = [...f.querySelectorAll('[data-dokumen]:not(.d-none)')].map(d => `<span class="status status-success polos"><i class="bi bi-file-earmark-check"></i> ${d.dataset.nama}</span>`).join('');
  }
  f.querySelectorAll('input[type=file]').forEach(inp => inp.addEventListener('change', () => {
    const d = inp.closest('[data-dokumen]'), file = inp.files[0], nf = d.querySelector('.nama-file'), p = d.querySelector('.pesan-galat');
    let pesan = '';
    if (file) { const ext = file.name.split('.').pop().toLowerCase(); if (!d.dataset.ext.split(',').includes(ext)) pesan = 'Format file tidak diizinkan.'; else if (file.size > d.dataset.maks * 1048576) pesan = `Ukuran file melebihi ${d.dataset.maks} MB.`; }
    if (pesan) { inp.value = ''; }
    p.textContent = pesan; p.classList.toggle('d-none', !pesan); d.classList.toggle('galat', !!pesan);
    nf.textContent = inp.files[0] ? `${inp.files[0].name} · ${Math.ceil(inp.files[0].size / 1024)} KB` : ''; nf.classList.toggle('d-none', !inp.files[0]);
    d.classList.toggle('terisi', !!inp.files[0]); d.querySelector('.lbl-tombol').textContent = inp.files[0] ? 'Ganti file' : 'Pilih file';
  }));
  f.addEventListener('change', e => { if (['jalur', 'paket_sidang'].includes(e.target.name)) sinkron(); });
  document.getElementById('tblLanjut').addEventListener('click', () => { if (cekLangkah(aktif)) { aktif++; tampil(); } });
  document.getElementById('tblSebelum').addEventListener('click', () => { if (aktif) { aktif--; tampil(); } });
  document.getElementById('tblKirimFinal').addEventListener('click', e => {
    if (!f.querySelector('[name=pernyataan]').checked) { e.preventDefault(); bootstrap.Modal.getInstance(document.getElementById('modalKirim')).hide(); alertKecil(isi[3], 'Centang pernyataan terlebih dahulu.'); return; }
    for (let i = 0; i < nLangkah - 1; i++) if (!cekLangkah(i)) { e.preventDefault(); bootstrap.Modal.getInstance(document.getElementById('modalKirim')).hide(); aktif = i; tampil(); return; }
    e.target.disabled = true; e.target.textContent = 'Mengirim…'; f.submit();
  });
  sinkron(); tampil();
})();
</script>
@endpush
