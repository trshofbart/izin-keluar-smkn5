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
        $validated = $request->validate([
            'nama_siswa' => 'required|string|max:100',
            'nis' => 'nullable|string|max:30',
            'no_absen' => 'required|string|max:10',
            'kelas_id' => 'required|exists:kelas,id',
            'keperluan' => 'required|string|max:1000',
            'jam_ke_mulai' => 'required|integer|min:1|max:10',
            'jam_ke_selesai' => 'required|integer|min:1|max:10|gte:jam_ke_mulai',
            'kendaraan' => 'required|in:Sepeda Motor,Mobil,Sepeda,Jalan Kaki,Antar Jemput,Lainnya',
            'cara_pulang' => 'required|in:dijemput,pulang_sendiri',
            'nama_penjemput' => 'required_if:cara_pulang,dijemput|nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.mapel_id' => 'required|exists:mapel,id',
            'items.*.guru_id' => 'required|exists:guru,id',
        ], [
            'jam_ke_selesai.gte' => 'Jam ke selesai harus sama atau lebih besar dari jam ke mulai.',
            'nama_penjemput.required_if' => 'Nama penjemput wajib diisi bila cara pulang "Dijemput".',
            'items.min' => 'Isi minimal 1 mapel & guru yang ditinggal.',
        ]);

        $kelas = Kelas::findOrFail($validated['kelas_id']);

        $permohonan = DB::transaction(function () use ($validated, $kelas) {
            $permohonan = PermohonanIzin::create([
                'kode' => PermohonanIzin::generateKode(),
                'nama_siswa' => $validated['nama_siswa'],
                'nis' => $validated['nis'] ?? null,
                'no_absen' => $validated['no_absen'],
                'kelas_id' => $kelas->id,
                'jurusan_id' => $kelas->jurusan_id,
                'keperluan' => $validated['keperluan'],
                'tanggal' => now()->toDateString(),
                'jam_ke_mulai' => $validated['jam_ke_mulai'],
                'jam_ke_selesai' => $validated['jam_ke_selesai'],
                'kendaraan' => $validated['kendaraan'],
                'cara_pulang' => $validated['cara_pulang'],
                'nama_penjemput' => $validated['nama_penjemput'] ?? null,
                'status' => 'menunggu',
                'barcode_kode' => PermohonanIzin::generateBarcodeKode(),
            ]);

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

            // 1 item otomatis untuk wali kelas.
            if ($kelas->wali_kelas_id) {
                PermohonanItem::create([
                    'permohonan_id' => $permohonan->id,
                    'mapel_id' => null,
                    'guru_id' => $kelas->wali_kelas_id,
                    'peran' => 'wali_kelas',
                    'status' => 'menunggu',
                ]);
            }

            return $permohonan;
        });

        return redirect()->route('izin.show', $permohonan->kode)
            ->with('success', 'Permohonan izin berhasil dibuat. Tunjukkan barcode ini kepada guru untuk dipindai.');
    }

    public function show(string $kode): View
    {
        $permohonan = PermohonanIzin::with(['kelas.jurusan', 'jurusan', 'items.guru', 'items.mapel', 'items.persetujuan'])
            ->where('kode', $kode)
            ->firstOrFail();

        return view('izin.show', compact('permohonan'));
    }
}
