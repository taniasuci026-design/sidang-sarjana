<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\KontenLanding;
use App\Services\AktivitasService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Kelola Landing Page (UC-26): bagian informasi, alur, dan FAQ. */
class LandingController extends Controller
{
    public const INFO = [
        'hero_label' => 'Label kecil', 'hero_judul_1' => 'Judul baris 1', 'hero_judul_2' => 'Judul baris 2 (warna oranye)',
        'hero_deskripsi' => 'Deskripsi', 'hero_tombol' => 'Teks tombol',
        'kontak_alamat' => 'Alamat', 'kontak_email' => 'Email', 'kontak_telepon' => 'Telepon',
    ];

    public function index()
    {
        return view('super.landing', [
            'info' => KontenLanding::where('bagian', 'informasi')->pluck('isi', 'judul'),
            'alur' => KontenLanding::where('bagian', 'alur')->orderBy('urutan')->get(),
            'faq' => KontenLanding::where('bagian', 'faq')->orderBy('urutan')->get(),
            'terakhir' => KontenLanding::with('pengubah')->latest('updated_at')->first(),
            'label' => self::INFO,
        ]);
    }

    public function informasi(Request $request)
    {
        $aturan = [];
        foreach (array_keys(self::INFO) as $k) {
            $aturan[$k] = ['required', 'string', $k === 'hero_deskripsi' || $k === 'kontak_alamat' ? 'max:500' : 'max:150'];
        }
        $data = $request->validate($aturan, [], self::INFO);
        $i = 1;
        foreach ($data as $k => $v) {
            KontenLanding::updateOrCreate(['bagian' => 'informasi', 'judul' => $k], ['isi' => $v, 'urutan' => $i++, 'id_user' => $request->user()->id_user]);
        }
        AktivitasService::catat('ubah landing page', ['keterangan' => 'Bagian utama dan kontak diperbarui.']);
        return back()->with('sukses', 'Konten landing page berhasil disimpan.');
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'bagian' => ['required', Rule::in(['alur', 'faq'])],
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:1', 'max:99'],
        ], [], ['judul' => 'pertanyaan / judul', 'isi' => 'jawaban / isi']);
    }

    public function store(Request $request)
    {
        $d = $this->data($request);
        $d['urutan'] = $d['urutan'] ?? (KontenLanding::where('bagian', $d['bagian'])->max('urutan') + 1);
        KontenLanding::create($d + ['is_tampil' => $request->boolean('is_tampil', true), 'id_user' => $request->user()->id_user]);
        AktivitasService::catat('ubah landing page', ['keterangan' => ($d['bagian'] === 'faq' ? 'FAQ ditambahkan: ' : 'Langkah alur ditambahkan: ') . $d['judul']]);
        return back()->with('sukses', $d['bagian'] === 'faq' ? 'Pertanyaan FAQ ditambahkan.' : 'Langkah alur ditambahkan.');
    }

    public function update(Request $request, KontenLanding $konten)
    {
        abort_if($konten->bagian === 'informasi', 404);
        $d = $this->data($request);
        $konten->update($d + ['is_tampil' => $request->boolean('is_tampil', true), 'id_user' => $request->user()->id_user]);
        AktivitasService::catat('ubah landing page', ['keterangan' => 'Konten diubah: ' . $konten->judul]);
        return back()->with('sukses', 'Konten berhasil diperbarui.');
    }

    public function destroy(KontenLanding $konten)
    {
        abort_if($konten->bagian === 'informasi', 404);
        $konten->delete();
        AktivitasService::catat('ubah landing page', ['keterangan' => 'Konten dihapus: ' . $konten->judul]);
        return back()->with('sukses', 'Konten dihapus dari landing page.');
    }
}
