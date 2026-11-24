// Uji end-to-end Sistem Informasi Pendaftaran Sidang (tiga role) terhadap server lokal + MySQL.
const { chromium } = require('playwright');
const fs = require('fs'), path = require('path');
const BASE = 'http://127.0.0.1:8000', F = p => path.join(__dirname, 'files', p), SHOT = path.join(__dirname, 'shots');
const hasil = []; let browser;
async function langkah(nama, fn) {
  try { await fn(); hasil.push(['LULUS', nama]); console.log('✔', nama); }
  catch (e) { hasil.push(['GAGAL', nama, e.message.split('\n')[0]]); console.log('✘', nama, '—', e.message.split('\n')[0]); }
}
function cek(kondisi, pesan) { if (!kondisi) throw new Error(pesan); }
async function konteks() { const c = await browser.newContext({ viewport: { width: 1366, height: 900 }, acceptDownloads: true, locale: 'id-ID' }); return [c, await c.newPage()]; }
async function login(page, email, pass) {
  await page.goto(BASE + '/masuk'); await page.fill('#email', email); await page.fill('#password', pass);
  await Promise.all([page.waitForLoadState('networkidle'), page.click('button:has-text("Login")')]);
}
async function teks(page) { const sel = (await page.locator('main.konten').count()) ? 'main.konten' : 'body'; return (await page.textContent(sel)).replace(/\s+/g, ' '); }
async function toast(page) { return (await page.locator('.toast-x').allTextContents()).join(' ').replace(/\s+/g, ' '); }
async function shot(page, nama) { await page.screenshot({ path: path.join(SHOT, nama + '.png'), fullPage: true }); }
async function otpDariHalaman(page) { const m = (await teks(page)).match(/Kode OTP: (\d{6})/); cek(m, 'Kode OTP dev tidak tampil'); return m[1]; }
async function isiOtp(page, kode) { const k = page.locator('.kotak-otp input'); for (let i = 0; i < 6; i++) await k.nth(i).fill(kode[i]); await Promise.all([page.waitForLoadState('networkidle'), page.click('button:has-text("Verifikasi")')]); }

async function daftar(page, nama, email, wa, pass) {
  await page.goto(BASE + '/daftar');
  await page.fill('[name=nama]', nama); await page.fill('[name=email]', email); await page.fill('[name=no_whatsapp]', wa);
  await page.fill('[name=password]', pass); await page.fill('[name=password_confirmation]', pass);
  await Promise.all([page.waitForLoadState('networkidle'), page.click('button:has-text("Daftar")')]);
}
async function ajukan(page, o) {
  await page.goto(BASE + '/mahasiswa/pengajuan/baru');
  if (o.npm) await page.fill('[name=npm]', o.npm);
  await page.click('#tblLanjut');
  await page.check(`[name=jalur][value=${o.jalur}]`);
  if (o.jalur === 'transfer') await page.fill('[name=dosen_pembimbing_pi]', 'Dr. Naeli Umniati, ST., MMSI.');
  await page.check(`[name=paket_sidang][value=${o.paket}]`);
  if (o.paket === 'kompre') await page.locator('[name=id_materi]').first().check();
  await page.click('#tblLanjut');
  for (const [label, file] of o.berkas) {
    const kartu = page.locator('[data-dokumen]:not(.d-none)', { hasText: label }).first();
    await kartu.locator('input[type=file]').setInputFiles(F(file));
  }
  await page.click('#tblLanjut');
  await page.check('[name=pernyataan]');
  await page.click('#tblKirim'); await page.waitForSelector('#modalKirim.show');
  await Promise.all([page.waitForLoadState('networkidle'), page.click('#tblKirimFinal')]);
}

(async () => {
  browser = await chromium.launch();
  const [cA, mA] = await konteks(); const [cB, mB] = await konteks(); const [cAd, ad] = await konteks(); const [cS, sa] = await konteks(); const [cP, pub] = await konteks();
  let noA, noB;

  await langkah('UI-01 Landing page tampil (alur, FAQ, tombol daftar/masuk)', async () => {
    await pub.goto(BASE + '/'); const t = await teks(pub);
    cek(t.includes('Alur Pengajuan') && t.includes('FAQ Pendaftaran Sidang') && t.includes('Masuk') && t.includes('Daftar'), 'Konten landing kurang'); await shot(pub, '01-landing');
  });
  await langkah('UI-02 Cek status: NPM tidak ditemukan', async () => {
    await pub.goto(BASE + '/cek-status?npm=10199999'); cek((await teks(pub)).includes('Data tidak ditemukan'), 'Pesan tidak ditemukan tidak tampil');
  });

  await langkah('UI-03 Registrasi menolak email staf yang sudah terdaftar', async () => {
    await daftar(mA, 'Coba', 'admin@sidangsarjana.id', '081200000001', 'Rahasia123'); cek((await teks(mA)).includes('Email sudah terdaftar'), 'Email terdaftar tidak ditolak');
  });
  await langkah('UI-03 Registrasi mahasiswa A + OTP salah ditolak + OTP benar mengaktifkan akun', async () => {
    await daftar(mA, 'Nashwa Callista', 'nashwa@mail.com', '081234567801', 'Nashwa2026');
    const kode = await otpDariHalaman(mA); await shot(mA, '02-otp');
    await isiOtp(mA, kode === '111111' ? '222222' : '111111'); cek((await teks(mA)).includes('Kode OTP salah'), 'OTP salah tidak ditolak');
    await isiOtp(mA, kode); cek(mA.url().endsWith('/masuk') && (await toast(mA)).includes('berhasil diverifikasi'), 'Verifikasi gagal');
  });
  await langkah('UI-04 Login salah menampilkan error', async () => {
    await login(mA, 'nashwa@mail.com', 'salah123'); cek((await teks(mA)).includes('Email atau kata sandi salah'), 'Pesan error login tidak tampil');
  });
  await langkah('UI-04/05 Login mahasiswa → dashboard sesuai peran', async () => {
    await login(mA, 'nashwa@mail.com', 'Nashwa2026'); cek(mA.url().includes('/mahasiswa'), 'Tidak diarahkan ke dashboard mahasiswa');
    cek((await teks(mA)).includes('Belum ada pengajuan'), 'Empty state dashboard tidak tampil'); await shot(mA, '03-dashboard-kosong');
  });
  await langkah('FR-37 NPM bukan milik prodi SI ditolak', async () => {
    await ajukan(mA, { npm: '30123318', jalur: 'reguler', paket: 'skripsi', berkas: [['KRS', 'KRS.pdf'], ['Workshop', 'Workshop.pdf'], ['Kursus', 'Kursus.pdf'], ['Manual Book', 'Manual_Book.pdf']] });
    cek((await teks(mA)).includes('NPM tidak terdaftar'), 'NPM tidak valid lolos');
  });
  await langkah('FR-13 Format file salah ditolak di sisi klien', async () => {
    await mA.goto(BASE + '/mahasiswa/pengajuan/baru'); await mA.fill('[name=npm]', '10123318'); await mA.click('#tblLanjut');
    await mA.check('[name=jalur][value=reguler]'); await mA.check('[name=paket_sidang][value=skripsi]'); await mA.click('#tblLanjut');
    await mA.locator('[data-dokumen]:not(.d-none)', { hasText: 'KRS' }).first().locator('input[type=file]').setInputFiles(F('foto.png'));
    cek((await teks(mA)).includes('Format file tidak diizinkan'), 'File PNG untuk KRS tidak ditolak');
    await mA.click('#tblLanjut'); cek((await teks(mA)).includes('Dokumen wajib belum diunggah'), 'Dokumen wajib tidak ditandai');
  });
  await langkah('UI-06..09 Pengajuan Skripsi reguler terkirim, status Menunggu', async () => {
    await ajukan(mA, { npm: '10123318', jalur: 'reguler', paket: 'skripsi', berkas: [['KRS', 'KRS.pdf'], ['Workshop', 'Workshop.pdf'], ['Kursus', 'Kursus.pdf'], ['Manual Book', 'Manual_Book.pdf']] });
    const t = await teks(mA); const m = t.match(/SPJ-\d{4}-\d{4}/); cek(m && t.includes('Menunggu'), 'Pengajuan tidak terkirim: ' + t.slice(0, 200)); noA = m[0];
    await shot(mA, '04-detail-menunggu');
  });
  await langkah('UI-10 Ubah Data saat Menunggu (perbaiki salah ketik nama & WA)', async () => {
    await mA.click('a:has-text("Ubah Data")'); await mA.fill('[name=nama_lengkap]', 'Nashwa Callista Ryeka Putri'); await mA.fill('[name=no_whatsapp]', '081234567809');
    await Promise.all([mA.waitForLoadState('networkidle'), mA.click('button:has-text("Simpan Perubahan")')]);
    cek((await toast(mA)).includes('berhasil diperbarui') && (await teks(mA)).includes('Nashwa Callista Ryeka Putri'), 'Ubah data gagal');
  });
  await langkah('Batas pengajuan: Skripsi tidak bisa diajukan 2x, hanya Kompre tersedia', async () => {
    await mA.goto(BASE + '/mahasiswa/pengajuan/baru'); await mA.click('#tblLanjut');
    cek(await mA.locator('[name=paket_sidang][value=skripsi]').isDisabled(), 'Skripsi masih bisa dipilih'); cek((await teks(mA)).includes('Ini pengajuan kedua Anda'), 'Info pengajuan kedua tidak tampil');
  });

  await langkah('Registrasi & pengajuan mahasiswa B (Transfer + Kompre + Berkas Tambahan)', async () => {
    await daftar(mB, 'Rizky Aditya', 'rizky@mail.com', '081234567802', 'Rizky2026'); await isiOtp(mB, await otpDariHalaman(mB));
    await login(mB, 'rizky@mail.com', 'Rizky2026');
    await ajukan(mB, { npm: '11123292', jalur: 'transfer', paket: 'kompre', berkas: [['KRS', 'KRS.pdf'], ['Workshop', 'Workshop.pdf'], ['Kursus', 'Kursus.pdf'], ['Berkas Tambahan', 'Berkas_Tambahan.pdf']] });
    const m = (await teks(mB)).match(/SPJ-\d{4}-\d{4}/); cek(m, 'Pengajuan B gagal'); noB = m[0];
  });
  await langkah('Akses lintas peran: mahasiswa tidak bisa buka halaman admin (403)', async () => {
    const r = await mA.goto(BASE + '/admin'); cek(r.status() === 403, 'Status ' + r.status());
  });

  await langkah('UI-11 Login admin → dashboard & notifikasi pengajuan baru', async () => {
    await login(ad, 'admin@sidangsarjana.id', 'Admin#2026'); cek(ad.url().endsWith('/admin'), 'Bukan dashboard admin');
    const t = await teks(ad); cek(t.includes(noA) && t.includes('Antrean Pemeriksaan'), 'Antrean tidak tampil'); cek(await ad.locator('.lonceng .jumlah').count() === 1, 'Badge notifikasi tidak tampil'); await shot(ad, '05-admin-dashboard');
  });
  await langkah('Akses lintas peran: admin tidak bisa buka Pengaturan Sistem (403)', async () => {
    const r = await ad.goto(BASE + '/superadmin/pengaturan'); cek(r.status() === 403, 'Status ' + r.status());
  });
  await langkah('UI-12 Filter & pencarian daftar pengajuan', async () => {
    await ad.goto(BASE + '/admin/pengajuan?paket=kompre'); let t = await teks(ad); cek(t.includes(noB) && !t.includes(noA), 'Filter paket salah');
    await ad.goto(BASE + '/admin/pengajuan?q=10123318'); t = await teks(ad); cek(t.includes(noA) && !t.includes(noB), 'Pencarian NPM salah'); await shot(ad, '06-daftar-pengajuan');
  });
  await langkah('UI-13 Validasi: Selesai ditolak bila dokumen belum lengkap', async () => {
    await ad.goto(BASE + '/admin/pengajuan?q=' + noA); await ad.click('main table a:has-text("Lihat")');
    await ad.selectOption('#pilihStatus', 'selesai'); await ad.selectOption('#pilihKategori', { label: 'Kategori 1 · Bisa Daftar Sidang' });
    await Promise.all([ad.waitForLoadState('networkidle'), ad.click('button:has-text("Submit")')]);
    cek((await teks(ad)).includes('semua dokumen Lengkap'), 'Validasi selesai tidak jalan');
  });
  await langkah('UI-13 Lihat dokumen PDF di pop-up', async () => {
    await ad.locator('main table button:has-text("Lihat")').first().click(); await ad.waitForSelector('#modalDok.show');
    const src = await ad.getAttribute('#dokFrame', 'src'); const r = await ad.request.get(src); cek(r.status() === 200 && (r.headers()['content-type'] || '').includes('pdf'), 'Berkas tidak bisa dibuka');
    await shot(ad, '07-lihat-dokumen'); await ad.click('#modalDok .btn-close');
  });
  await langkah('UI-13 Verifikasi + tandai KRS Perlu Perbaikan → status Perbaikan (Kategori 2b)', async () => {
    await Promise.all([ad.waitForLoadState('networkidle'), ad.click('form[action$="/verifikasi"] button')]);
    const baris = ad.locator('#formPeriksa tbody tr', { hasText: 'KRS' });
    await baris.locator('select').selectOption('perlu_perbaikan'); await baris.locator('input').fill('Scan KRS buram. Unggah ulang yang terbaca jelas.');
    for (const n of ['Workshop', 'Kursus', 'Manual Book']) await ad.locator('#formPeriksa tbody tr', { hasText: n }).locator('select').selectOption('lengkap');
    await ad.selectOption('#pilihStatus', 'perbaikan'); await ad.selectOption('#pilihKategori', { label: 'Kategori 2b · Kendala Berkas' });
    await ad.fill('[name=catatan_mahasiswa]', 'Mohon unggah ulang KRS.'); await ad.fill('[name=catatan_internal]', 'Cek ulang tanda tangan dosen.');
    await Promise.all([ad.waitForLoadState('networkidle'), ad.click('button:has-text("Submit")')]);
    cek((await toast(ad)).includes('berhasil disimpan'), 'Simpan pemeriksaan gagal: ' + (await teks(ad)).slice(0, 300)); await shot(ad, '08-pemeriksaan');
  });
  await langkah('FR-29 Riwayat perubahan mencatat admin, aksi, status', async () => {
    await ad.click('a:has-text("Riwayat Perubahan")'); const t = await teks(ad); cek(t.includes('Siti Rahayu') && t.includes('Ubah status') && t.includes('Perbaikan'), 'Riwayat kurang');
  });

  await langkah('UI-10 Mahasiswa melihat status Perbaikan, catatan, & catatan internal tersembunyi', async () => {
    await mA.goto(BASE + '/mahasiswa'); let t = await teks(mA); cek(t.includes('BERKAS: KRS') && t.includes('Scan KRS buram'), 'Catatan perbaikan dashboard tidak tampil'); await shot(mA, '09-dashboard-perbaikan');
    await mA.goto(BASE + '/mahasiswa/pengajuan'); await mA.click(`tr:has-text("${noA}") a:has-text("Detail")`); t = await teks(mA);
    cek(t.includes('Mohon unggah ulang KRS.') && !t.includes('Cek ulang tanda tangan'), 'Visibilitas catatan salah'); cek(t.includes('Kendala Berkas'), 'Kategori tidak tampil');
  });
  await langkah('FR-18/20 Unggah ulang hanya KRS → kembali ke antrean (Menunggu)', async () => {
    await mA.click('a.nav-link:has-text("Dokumen")'); cek(await mA.locator('input[type=file]').count() === 1, 'Lebih dari satu dokumen bisa diunggah ulang');
    await mA.locator('input[type=file]').setInputFiles(F('KRS_revisi.pdf')); await Promise.all([mA.waitForLoadState('networkidle'), mA.click('button:has-text("Kirim Perbaikan")')]);
    cek((await toast(mA)).includes('kembali ke antrean'), 'Perbaikan gagal'); await mA.click('a.nav-link:has-text("Informasi")'); cek((await teks(mA)).includes('Menunggu'), 'Status tidak kembali Menunggu');
  });
  await langkah('UI-13 Admin menyelesaikan pengajuan A (semua Lengkap, Kategori 1)', async () => {
    await ad.goto(BASE + '/admin/pengajuan?q=' + noA); await ad.click('main table a:has-text("Lihat")');
    for (const s of await ad.locator('.pilih-dok').all()) await s.selectOption('lengkap');
    await ad.selectOption('#pilihStatus', 'selesai'); await ad.selectOption('#pilihKategori', { label: 'Kategori 1 · Bisa Daftar Sidang' });
    await Promise.all([ad.waitForLoadState('networkidle'), ad.click('button:has-text("Submit")')]); cek((await toast(ad)).includes('berhasil disimpan'), 'Gagal selesai');
  });
  await langkah('UI-02 Cek status publik: NPM tersamarkan, status & kategori tampil', async () => {
    await pub.goto(BASE + '/cek-status?npm=10123318'); const t = await teks(pub);
    cek(t.includes('101***18') && t.includes('Selesai') && t.includes('Bisa Daftar Sidang') && !t.includes('Nashwa'), 'Data publik tidak sesuai'); await shot(pub, '10-cek-status');
  });
  await langkah('Kategori Hasil: daftar per kategori & tombol Lihat', async () => {
    await ad.goto(BASE + '/admin/kategori-hasil'); const t = await teks(ad); cek(t.includes('Bisa Daftar Sidang') && t.includes('10123318'), 'Kategori kosong'); await shot(ad, '11-kategori');
  });
  await langkah('FR-30/31 Laporan: ekspor CSV, cetak, ZIP berisi berkas', async () => {
    await ad.goto(BASE + '/admin/laporan'); cek((await teks(ad)).includes(noA), 'Tabel laporan kosong'); await shot(ad, '12-laporan');
    const r1 = await ad.request.get(BASE + '/admin/laporan/excel'); const csv = await r1.text(); cek(r1.status() === 200 && csv.includes(noA) && csv.includes(noB), 'CSV salah');
    const r2 = await ad.request.get(BASE + '/admin/laporan/cetak'); cek(r2.status() === 200 && (await r2.text()).includes('Laporan Pengajuan Sidang Sarjana'), 'Cetak gagal');
    const r3 = await ad.request.get(BASE + '/admin/laporan/zip'); const buf = await r3.body(); fs.writeFileSync(path.join(__dirname, 'laporan.zip'), buf);
    cek(r3.status() === 200 && buf.slice(0, 2).toString() === 'PK', 'ZIP gagal');
  });
  await langkah('Admin melaporkan akun B (Hapus Akun → Dilaporkan)', async () => {
    await ad.goto(BASE + '/admin/pengajuan?q=' + noB); await ad.click('main table a:has-text("Lihat")'); await ad.click('button:has-text("Hapus Akun")');
    await ad.fill('[name=alasan]', 'Mengunggah foto, bukan KRS'); await Promise.all([ad.waitForLoadState('networkidle'), ad.click('button:has-text("Kirim Laporan")')]);
    cek((await toast(ad)).includes('Menunggu tanggapan Superadmin'), 'Laporan gagal');
  });

  await langkah('UI-16 Superadmin: beranda menampilkan permintaan hapus', async () => {
    await login(sa, 'superadmin@sidangsarjana.id', 'Superadmin#2026'); const t = await teks(sa);
    cek(sa.url().endsWith('/admin') && t.includes('Beranda') && t.includes('1 permintaan hapus akun'), 'Beranda superadmin salah'); await shot(sa, '13-beranda-super');
  });
  await langkah('UI-17 Data Mahasiswa: tab Dilaporkan → Hapus & Arsipkan', async () => {
    await sa.goto(BASE + '/superadmin/mahasiswa?tab=dilaporkan'); cek((await teks(sa)).includes('Rizky Aditya'), 'Akun dilaporkan tidak tampil'); await shot(sa, '14-data-mahasiswa');
    await sa.click('a.btn-outline-danger:has-text("Hapus")'); await sa.waitForSelector('#modalHapus.show');
    await sa.fill('#modalHapus [name=catatan]', 'Berkas KRS berupa foto, bukan dokumen.'); await sa.check('#modalHapus [name=yakin]');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('button:has-text("Hapus dan Arsipkan")')]);
    const t = await teks(sa); cek(sa.url().includes('tab=arsip') && t.includes('Rizky Aditya') && t.includes('Mengunggah foto'), 'Arsip tidak tampil'); await shot(sa, '15-arsip');
  });
  await langkah('Akun yang diarsipkan tidak bisa login', async () => {
    await cB.clearCookies(); await login(mB, 'rizky@mail.com', 'Rizky2026'); cek((await teks(mB)).includes('Akun dinonaktifkan'), 'Akun arsip masih bisa login');
  });
  await langkah('UI-15 Paket materi: tambah, ubah, hapus', async () => {
    await sa.goto(BASE + '/superadmin/pengaturan'); await sa.click('button:has-text("+ Tambah Paket Materi")'); await sa.waitForSelector('#modalMateri.show');
    await sa.fill('#modalMateri [name=mata_kuliah_1]', 'Kecerdasan Buatan'); await sa.fill('#modalMateri [name=mata_kuliah_2]', 'Sistem Pendukung Keputusan'); await sa.fill('#modalMateri [name=mata_kuliah_3]', 'Pengolahan Citra');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalMateri button:has-text("Simpan")')]); cek((await teks(sa)).includes('Pengolahan Citra'), 'Tambah paket gagal');
    const paket = sa.locator('.d-grid > div', { hasText: 'Pengolahan Citra' });
    await paket.locator('button:has-text("Ubah")').click(); await sa.waitForSelector('#modalMateri.show'); await sa.fill('#modalMateri [name=mata_kuliah_3]', 'Visi Komputer');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalMateri button:has-text("Simpan")')]); cek((await teks(sa)).includes('Visi Komputer'), 'Ubah paket gagal');
    await shot(sa, '16-pengaturan');
    await sa.locator('.d-grid > div', { hasText: 'Visi Komputer' }).locator('button:has-text("Hapus")').click(); await sa.waitForSelector('#modalHapusMateri.show');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalHapusMateri button.btn-danger')]); cek(!(await teks(sa)).includes('Visi Komputer'), 'Hapus paket gagal');
  });
  await langkah('UI-15 Tambah dokumen persyaratan', async () => {
    await sa.click('button:has-text("+ Tambah Dokumen")'); await sa.waitForSelector('#modalDokumen.show');
    await sa.fill('#modalDokumen [name=nama_dokumen]', 'Bukti Bebas Pustaka'); await sa.uncheck('#modalDokumen [name=wajib_kompre]'); await sa.fill('#modalDokumen [name=format_file]', 'PDF');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalDokumen button:has-text("Simpan Dokumen")')]); cek((await teks(sa)).includes('Bukti Bebas Pustaka'), 'Tambah dokumen gagal');
    await sa.locator('tr', { hasText: 'Bukti Bebas Pustaka' }).locator('button:has-text("Hapus")').click(); await sa.waitForSelector('#modalHapusDok.show');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalHapusDok button.btn-danger')]);
  });
  await langkah('FR-34/35 Tutup pendaftaran → mahasiswa tidak bisa mengajukan, status lama tetap terlihat', async () => {
    await sa.click('button:has-text("Tutup Pendaftaran")'); await sa.waitForSelector('#modalStatus.show'); await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalStatus button.btn-danger')]);
    cek((await toast(sa)).includes('ditutup'), 'Tutup gagal');
    await mA.goto(BASE + '/mahasiswa/pengajuan/baru'); cek((await teks(mA)).includes('Pendaftaran sidang sedang ditutup'), 'Form masih terbuka'); await shot(mA, '17-ditutup');
    await mA.goto(BASE + '/mahasiswa/pengajuan'); cek((await teks(mA)).includes(noA), 'Status lama tidak terlihat');
    await sa.click('button:has-text("Buka Pendaftaran")'); await sa.waitForSelector('#modalStatus.show'); await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalStatus .btn-primary')]);
  });
  await langkah('Mahasiswa A mengajukan pengajuan kedua (Kompre)', async () => {
    await ajukan(mA, { jalur: 'reguler', paket: 'kompre', berkas: [['KRS', 'KRS.pdf'], ['Workshop', 'Workshop.pdf'], ['Kursus', 'Kursus.pdf']] });
    cek(/SPJ-\d{4}-\d{4}/.test(await teks(mA)) && (await toast(mA)).includes('berhasil dikirim'), 'Pengajuan kedua gagal');
    await mA.goto(BASE + '/mahasiswa'); const t = await teks(mA); cek(!t.includes('Ajukan Sidang Baru'), 'Tombol ajukan masih ada setelah 2 pengajuan'); await shot(mA, '18-dashboard-2');
  });
  await langkah('Kelola Landing Page: ubah judul & tambah FAQ tampil di landing', async () => {
    await sa.goto(BASE + '/superadmin/landing'); await sa.fill('[name=hero_judul_1]', 'Pendaftaran Online'); await Promise.all([sa.waitForLoadState('networkidle'), sa.click('button:has-text("Simpan Perubahan")')]);
    await sa.click('button:has-text("+ Tambah pertanyaan")'); await sa.waitForSelector('#modalKonten.show');
    await sa.fill('#modalKonten [name=judul]', 'Kapan jadwal sidang diumumkan?'); await sa.fill('#modalKonten [name=isi]', 'Jadwal diumumkan prodi setelah berkas dinyatakan lengkap.');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalKonten button:has-text("Simpan")')]); await shot(sa, '19-kelola-landing');
    await pub.goto(BASE + '/'); const t = await teks(pub); cek(t.includes('Pendaftaran Online') && t.includes('Kapan jadwal sidang diumumkan?'), 'Landing tidak berubah');
  });
  await langkah('Kategori: Superadmin ubah teks notifikasi', async () => {
    await sa.goto(BASE + '/admin/kategori-hasil'); await sa.locator('button:has-text("Ubah teks")').first().click(); await sa.waitForSelector('#modalTeks.show');
    await sa.fill('#modalTeks [name=teks_notifikasi]', 'Selamat, berkas lengkap. Silakan daftar sidang.'); await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalTeks button:has-text("Simpan")')]);
    cek((await teks(sa)).includes('Selamat, berkas lengkap'), 'Teks tidak berubah');
  });
  await langkah('UI-16 Manajemen admin: tambah, nonaktifkan → login ditolak', async () => {
    await sa.goto(BASE + '/superadmin/admin'); await sa.click('button:has-text("Tambah Admin")'); await sa.waitForSelector('#modalAdmin.show');
    await sa.fill('#modalAdmin [name=nama]', 'Andi Wijaya'); await sa.fill('#modalAdmin [name=email]', 'andi@sidangsarjana.id'); await sa.fill('#modalAdmin [name=password]', 'Andi2026x'); await sa.fill('#modalAdmin [name=password_confirmation]', 'Andi2026x');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.click('#modalAdmin button:has-text("Simpan")')]); cek((await teks(sa)).includes('andi@sidangsarjana.id'), 'Tambah admin gagal'); await shot(sa, '20-manajemen-admin');
    await Promise.all([sa.waitForLoadState('networkidle'), sa.locator('tr', { hasText: 'andi@sidangsarjana.id' }).locator('button:has-text("Nonaktifkan")').click()]);
    const [c, p] = await konteks(); await login(p, 'andi@sidangsarjana.id', 'Andi2026x'); cek((await teks(p)).includes('Akun dinonaktifkan'), 'Admin nonaktif masih bisa login'); await c.close();
  });
  await langkah('Log Aktivitas menampilkan seluruh aksi', async () => {
    await sa.goto(BASE + '/superadmin/log'); const t = await teks(sa); cek(t.includes('Hapus akun mahasiswa') && t.includes('Tutup pendaftaran') && t.includes('Pengajuan dibuat'), 'Log kurang'); await shot(sa, '21-log');
  });
  await langkah('FR-07 Lupa kata sandi via OTP → login dengan sandi baru', async () => {
    const [c, p] = await konteks(); await p.goto(BASE + '/lupa-kata-sandi'); await p.fill('[name=email]', 'nashwa@mail.com');
    await Promise.all([p.waitForLoadState('networkidle'), p.click('button:has-text("Lanjut")')]); await isiOtp(p, await otpDariHalaman(p));
    await p.fill('[name=password]', 'BaruNashwa9'); await p.fill('[name=password_confirmation]', 'BaruNashwa9'); await Promise.all([p.waitForLoadState('networkidle'), p.click('button:has-text("Simpan kata sandi")')]);
    cek((await toast(p)).includes('Kata sandi berhasil diubah'), 'Reset gagal'); await login(p, 'nashwa@mail.com', 'BaruNashwa9'); cek(p.url().includes('/mahasiswa'), 'Login sandi baru gagal'); await c.close();
  });
  await langkah('Halaman notifikasi mahasiswa', async () => {
    await mA.goto(BASE + '/notifikasi'); const t = await teks(mA); cek(t.includes('Berkas perlu diperbaiki') && t.includes('Berkas telah diterima'), 'Notifikasi kurang'); await shot(mA, '22-notifikasi');
  });
  await langkah('Tampilan mobile (390px) tidak melebar horizontal', async () => {
    const c = await browser.newContext({ viewport: { width: 390, height: 844 } }); const p = await c.newPage(); await login(p, 'admin@sidangsarjana.id', 'Admin#2026');
    for (const u of ['/admin', '/admin/pengajuan', '/']) { await p.goto(BASE + u); const w = await p.evaluate(() => document.documentElement.scrollWidth); cek(w <= 392, `${u} lebar ${w}px`); }
    await shot(p, '23-mobile'); await c.close();
  });

  await browser.close();
  const gagal = hasil.filter(h => h[0] === 'GAGAL');
  fs.writeFileSync(path.join(__dirname, 'hasil.json'), JSON.stringify(hasil, null, 1));
  console.log(`\n${hasil.length - gagal.length}/${hasil.length} lulus`); gagal.forEach(g => console.log('  GAGAL:', g[1], '—', g[2]));
})();
