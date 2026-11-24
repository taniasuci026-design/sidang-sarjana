{{-- Filter pengajuan (FR-23) --}}
<form method="GET" class="kartu mb-3">
    <div class="kartu-judul mb-3">Filter pengajuan</div>
    <div class="row g-2">
        <div class="col-6 col-lg-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">Semua status</option>
            @foreach (\App\Models\Pengajuan::STATUS as $k => $l)<option value="{{ $k }}" @selected(($filter['status'] ?? '') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-3"><label class="form-label">Paket Sidang</label><select name="paket" class="form-select"><option value="">Semua paket sidang</option>
            @foreach (\App\Models\Pengajuan::PAKET as $k => $l)<option value="{{ $k }}" @selected(($filter['paket'] ?? '') === $k)>{{ $l }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-3"><label class="form-label">Kategori Hasil</label><select name="kategori" class="form-select"><option value="">Semua kategori hasil</option><option value="belum" @selected(($filter['kategori'] ?? '') === 'belum')>Belum Diperiksa</option>
            @foreach ($kategori as $k)<option value="{{ $k->id_kategori }}" @selected(($filter['kategori'] ?? '') == $k->id_kategori)>{{ $k->label() }} · {{ $k->nama_kategori }}</option>@endforeach</select></div>
        <div class="col-6 col-lg-3"><label class="form-label">Urutkan</label><select name="urut" class="form-select"><option value="terbaru">Terbaru</option><option value="terlama" @selected(request('urut') === 'terlama')>Terlama</option></select></div>
        <div class="col-6 col-lg-2"><label class="form-label">Dari</label><input type="date" name="dari" value="{{ $filter['dari'] ?? '' }}" class="form-control"></div>
        <div class="col-6 col-lg-2"><label class="form-label">Sampai</label><input type="date" name="sampai" value="{{ $filter['sampai'] ?? '' }}" class="form-control"></div>
        <div class="col-lg-6"><label class="form-label">Cari pengajuan</label><div class="input-ikon"><i class="bi bi-search"></i><input name="q" value="{{ $filter['q'] ?? '' }}" class="form-control" placeholder="Nama, NPM, atau no. pengajuan"></div></div>
        <div class="col-lg-2 d-flex align-items-end gap-2"><button class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Cari</button><a href="{{ url()->current() }}" class="btn btn-light border" title="Reset filter"><i class="bi bi-arrow-counterclockwise"></i></a></div>
    </div>
</form>
