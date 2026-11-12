<?php

namespace App\Http\Controllers;

use App\Models\GuruToken;
use App\Models\PermohonanIzin;
use App\Models\PermohonanItem;
use App\Models\Persetujuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GuruController extends Controller
{
    public function show(string $barcodeKode): View
    {
        $permohonan = PermohonanIzin::with(['kelas.jurusan', 'jurusan', 'siswas.kelas.jurusan', 'siswas.jurusan', 'items.guru', 'items.mapel', 'items.persetujuan'])
            ->where('barcode_kode', $barcodeKode)
            ->firstOrFail();

        // Hanya guru dari item yang BELUM disetujui yang bisa memilih di form.
        $pendingItems = $permohonan->items->where('status', 'menunggu')->values();

        return view('guru.show', compact('permohonan', 'pendingItems'));
    }

    public function approve(Request $request, string $barcodeKode): RedirectResponse
    {
        $permohonan = PermohonanIzin::with('items')
            ->where('barcode_kode', $barcodeKode)
            ->firstOrFail();

        $validated = $request->validate([
            'permohonan_item_id' => 'required|exists:permohonan_item,id',
            'token' => 'required|string|max:10',
        ]);

        $item = PermohonanItem::where('id', $validated['permohonan_item_id'])
            ->where('permohonan_id', $permohonan->id)
            ->firstOrFail();

        if ($item->status === 'disetujui') {
            return back()->withErrors(['token' => 'Guru ini sudah memberikan persetujuan sebelumnya.']);
        }

        $token = GuruToken::where('guru_id', $item->guru_id)
            ->where('token', strtoupper(trim($validated['token'])))
            ->where('aktif', true)
            ->first();

        if (! $token) {
            return back()->withErrors(['token' => 'Token salah. Periksa kembali token yang diberikan admin untuk guru ini.'])
                ->withInput();
        }

        DB::transaction(function () use ($item, $token, $permohonan) {
            $item->update([
                'status' => 'disetujui',
                'disetujui_pada' => now(),
            ]);

            Persetujuan::create([
                'permohonan_item_id' => $item->id,
                'guru_id' => $item->guru_id,
                'token_digunakan' => $token->token,
                'disetujui_pada' => now(),
            ]);

            $permohonan->refreshStatus();
        });

        return redirect()->route('guru.show', $permohonan->barcode_kode)
            ->with('success', 'Persetujuan dari '.$item->guru->nama.' berhasil dicatat.')
            ->with('izin_kode', $permohonan->kode);
    }
}
