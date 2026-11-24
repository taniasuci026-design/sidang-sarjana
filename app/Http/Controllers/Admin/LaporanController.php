<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriHasil;
use App\Models\Pengajuan;
use App\Services\AktivitasService;
use App\Services\PengajuanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/** UI-14 Laporan: tabel, ekspor Excel (CSV), cetak/PDF, dan unduh berkas ZIP (FR-30, FR-31). */
class LaporanController extends Controller
{
    use Filter;

    public function index(Request $request)
    {
        [$q, $f] = $this->queryPengajuan($request);
        $ringkas = (clone $q)->reorder()->selectRaw('status_pengajuan, count(*) n')->groupBy('status_pengajuan')->pluck('n', 'status_pengajuan');
        return view('admin.laporan', [
            'pengajuan' => $q->orderByDesc('diajukan_at')->paginate(15)->withQueryString(),
            'filter' => $f, 'kategori' => KategoriHasil::orderBy('kode')->get(), 'ringkas' => $ringkas,
        ]);
    }

    private function baris(Pengajuan $p): array
    {
        return [
            $p->no_pengajuan, $p->diajukan_at?->format('d/m/Y H:i'), $p->mahasiswa->npm, $p->mahasiswa->nama_lengkap,
            $p->mahasiswa->user?->email, $p->mahasiswa->user?->no_whatsapp, $p->jalurLabel(), $p->paketLabel(),
            $p->materi?->teks() ?? '-', $p->dosen_pembimbing_pi ?? '-', $p->statusLabel(),
            $p->kategori ? $p->kategori->label() . ' - ' . $p->kategori->nama_kategori : 'Belum Diperiksa', $p->updated_at?->format('d/m/Y H:i'),
        ];
    }

    private const KOLOM = ['No. Pengajuan', 'Tanggal Pengajuan', 'NPM', 'Nama Mahasiswa', 'Email', 'WhatsApp', 'Jalur', 'Paket Sidang', 'Materi Kompre', 'Dosen Pembimbing PI', 'Status', 'Kategori Hasil', 'Terakhir Diperbarui'];

    private function csv($data): string
    {
        $h = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF");
        fputcsv($h, self::KOLOM, ';');
        foreach ($data as $p) {
            fputcsv($h, $this->baris($p), ';');
        }
        rewind($h);
        return stream_get_contents($h);
    }

    public function excel(Request $request)
    {
        [$q] = $this->queryPengajuan($request);
        $data = $q->orderBy('diajukan_at')->get();
        AktivitasService::catat('ekspor laporan', ['keterangan' => "Ekspor Excel (CSV) {$data->count()} pengajuan."]);
        return response($this->csv($data), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="laporan-pengajuan-' . now()->format('Ymd-His') . '.csv"',
        ]);
    }

    public function cetak(Request $request)
    {
        [$q, $f] = $this->queryPengajuan($request);
        AktivitasService::catat('cetak laporan', ['keterangan' => 'Cetak laporan / simpan PDF.']);
        return view('admin.laporan-cetak', ['pengajuan' => $q->orderBy('diajukan_at')->get(), 'filter' => $f, 'kolom' => self::KOLOM]);
    }

    /** ZIP berisi rekap CSV + folder berkas per mahasiswa sesuai filter. */
    public function zip(Request $request)
    {
        [$q] = $this->queryPengajuan($request);
        $data = $q->with('dokumen.jenis')->orderBy('diajukan_at')->get();
        if ($data->isEmpty()) {
            return back()->with('galat', 'Tidak ada pengajuan untuk diunduh dengan filter ini.');
        }
        return $this->kirimZip($data, 'berkas-pengajuan-' . now()->format('Ymd-His') . '.zip');
    }

    public function zipSatu(Pengajuan $pengajuan)
    {
        return $this->kirimZip(collect([$pengajuan->load('dokumen.jenis', 'mahasiswa.user', 'kategori', 'materi')]), PengajuanService::namaFolder($pengajuan) . '.zip');
    }

    private function kirimZip($data, string $nama)
    {
        $tmp = tempnam(sys_get_temp_dir(), 'zip');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $zip->addFromString('rekap-pengajuan.csv', $this->csv($data));
        $jumlah = 0;
        foreach ($data as $p) {
            $folder = PengajuanService::namaFolder($p);
            foreach ($p->dokumen as $d) {
                if (Storage::disk('local')->exists($d->path_file)) {
                    $ext = pathinfo($d->path_file, PATHINFO_EXTENSION);
                    $zip->addFile(Storage::disk('local')->path($d->path_file), $folder . '/' . \Illuminate\Support\Str::slug($d->jenis->nama_dokumen) . '.' . $ext);
                    $jumlah++;
                }
            }
        }
        $zip->close();
        AktivitasService::catat('unduh zip', ['id_pengajuan' => $data->count() === 1 ? $data->first()->id_pengajuan : null,
            'keterangan' => "Unduh ZIP {$data->count()} pengajuan, {$jumlah} berkas."]);
        return response()->download($tmp, $nama, ['Content-Type' => 'application/zip'])->deleteFileAfterSend(true);
    }
}
