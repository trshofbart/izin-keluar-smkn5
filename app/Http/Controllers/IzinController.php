<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PermohonanIzin;
use App\Models\PermohonanItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class IzinController extends Controller
{
    public function create(): View
    {
        return view('izin.form', [
            'kelas' => Kelas::with('jurusan')->orderBy('nama')->get(),
            'mapel' => Mapel::orderBy('nama')->get(),
            'guruMapel' => \App\Models\GuruMapel::with('guru')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Kompatibilitas form lama: isian siswa tunggal (nama_siswa, nis,
        // no_absen, kelas_id di level atas) dibungkus menjadi 1 baris siswa.
        if (! $request->has('siswa') && $request->has('nama_siswa')) {
            $request->merge(['siswa' => [[
                'nama_siswa' => $request->input('nama_siswa'),
                'nis' => $request->input('nis'),
                'no_absen' => $request->input('no_absen'),
                'kelas_id' => $request->input('kelas_id'),
            ]]]);
        }

        $validated = $request->validate([
            'siswa' => 'required|array|min:1',
            'siswa.*.nama_siswa' => 'required|string|max:100',
            'siswa.*.nis' => 'nullable|string|max:30',
            'siswa.*.no_absen' => 'required|string|max:10',
            'siswa.*.kelas_id' => 'required|exists:kelas,id',
            'keperluan' => 'required|string|max:1000',
            'jam_ke_mulai' => 'required|integer|min:1|max:10',
            'jam_ke_selesai' => 'required|integer|min:1|max:10|gte:jam_ke_mulai',
            'kendaraan' => 'required|in:Sepeda Motor,Mobil,Sepeda,Jalan Kaki,Antar Jemput,Lainnya',
            'cara_pulang' => 'nullable|in:dijemput,pulang_sendiri',
            'nama_penjemput' => 'required_if:cara_pulang,dijemput|nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.mapel_id' => 'required|exists:mapel,id',
            'items.*.guru_id' => 'required|exists:guru,id',
        ], [
            'jam_ke_selesai.gte' => 'Jam ke selesai harus sama atau lebih besar dari jam ke mulai.',
            'nama_penjemput.required_if' => 'Nama penjemput wajib diisi bila cara keluar "Dijemput".',
            'siswa.min' => 'Isi minimal 1 siswa.',
            'items.min' => 'Isi minimal 1 mapel & guru yang ditinggal.',
        ]);

        $permohonan = DB::transaction(function () use ($validated) {
            // Siswa pertama juga disimpan di kolom permohonan (rekap & filter lama).
            $pertama = $validated['siswa'][0];
            $kelasPertama = Kelas::findOrFail($pertama['kelas_id']);

            $permohonan = PermohonanIzin::create([
                'kode' => PermohonanIzin::generateKode(),
                'nama_siswa' => $pertama['nama_siswa'],
                'nis' => $pertama['nis'] ?? null,
                'no_absen' => $pertama['no_absen'],
                'kelas_id' => $kelasPertama->id,
                'jurusan_id' => $kelasPertama->jurusan_id,
                'keperluan' => $validated['keperluan'],
                'tanggal' => now()->toDateString(),
                'jam_ke_mulai' => $validated['jam_ke_mulai'],
                'jam_ke_selesai' => $validated['jam_ke_selesai'],
                'kendaraan' => $validated['kendaraan'],
                'cara_pulang' => $validated['cara_pulang'] ?? null,
                'nama_penjemput' => $validated['nama_penjemput'] ?? null,
                'status' => 'menunggu',
                'barcode_kode' => PermohonanIzin::generateBarcodeKode(),
            ]);

            // Semua siswa dalam permohonan ini.
            $kelasIds = [];
            foreach ($validated['siswa'] as $row) {
                $kelas = Kelas::findOrFail($row['kelas_id']);
                $kelasIds[$kelas->id] = $kelas;
                $permohonan->siswas()->create([
                    'nama_siswa' => $row['nama_siswa'],
                    'nis' => $row['nis'] ?? null,
                    'no_absen' => $row['no_absen'],
                    'kelas_id' => $kelas->id,
                    'jurusan_id' => $kelas->jurusan_id,
                ]);
            }

            // 1 item per pasangan mapel-guru yang diisi siswa (hindari duplikat).
            $seen = [];
            foreach ($validated['items'] as $row) {
                $key = $row['mapel_id'].'-'.$row['guru_id'];
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                PermohonanItem::create([
                    'permohonan_id' => $permohonan->id,
                    'mapel_id' => $row['mapel_id'],
                    'guru_id' => $row['guru_id'],
                    'peran' => 'guru_mapel',
                    'status' => 'menunggu',
                ]);
            }

            // 1 item otomatis untuk wali kelas dari SETIAP kelas siswa
            // (dedupe bila wali kelasnya orang yang sama).
            $waliSudah = [];
            foreach ($kelasIds as $kelas) {
                if ($kelas->wali_kelas_id && ! isset($waliSudah[$kelas->wali_kelas_id])) {
                    $waliSudah[$kelas->wali_kelas_id] = true;
                    PermohonanItem::create([
                        'permohonan_id' => $permohonan->id,
                        'mapel_id' => null,
                        'guru_id' => $kelas->wali_kelas_id,
                        'peran' => 'wali_kelas',
                        'status' => 'menunggu',
                    ]);
                }
            }

            return $permohonan;
        });

        return redirect()->route('izin.show', $permohonan->kode)
            ->with('success', 'Permohonan izin berhasil dibuat. Tunjukkan barcode ini kepada guru untuk dipindai.');
    }

    public function show(string $kode): View|RedirectResponse
    {
        $permohonan = PermohonanIzin::with(['kelas.jurusan', 'jurusan', 'siswas.kelas.jurusan', 'siswas.jurusan', 'items.guru', 'items.mapel', 'items.persetujuan'])
            ->where('kode', $kode)
            ->firstOrFail();

        // Setelah rekap dikirim ke pos satpam, izin dinonaktifkan: halaman
        // rekap tidak bisa diakses lagi oleh siswa dan ia langsung diarahkan
        // ke form baru.
        if ($permohonan->sudahDipakai()) {
            return redirect()->route('izin.create')
                ->with('success', 'Izin '.$permohonan->kode.' sudah digunakan pada '.$permohonan->dipakai_pada->format('d-m-Y H:i').' dan sudah dinonaktifkan. Silakan isi form baru bila perlu mengajukan izin lagi.');
        }

        return view('izin.show', compact('permohonan'));
    }
}
