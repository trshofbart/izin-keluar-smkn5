<?php

namespace App\Http\Controllers;

use App\Models\PermohonanIzin;
use App\Models\PosKirim;
use App\Models\PosQr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Halaman tujuan QR pos statis (yang dicetak dan ditempel di pos).
     * Siswa membuka halaman ini (scan kamera bawaan atau fitur scan di
     * aplikasi) lalu mengirim rekap izinnya ke pos ini.
     */
    public function show(string $kode): View
    {
        $pos = PosQr::where('kode', $kode)->firstOrFail();

        $rekap = null;
        if ($kirimId = session('kirim_id')) {
            $kirim = PosKirim::with(['permohonan.siswas.kelas', 'permohonan.siswas.jurusan', 'permohonan.kelas', 'permohonan.jurusan'])
                ->find($kirimId);
            if ($kirim && $kirim->pos_qr_id === $pos->id) {
                $rekap = $kirim;
            }
        }

        // Daftar rekap hari ini tampil publik di halaman ini: satpam cukup
        // memindai QR yang dibawa siswa untuk memeriksa isinya, tanpa login.
        $kirimanHariIni = PosKirim::with(['permohonan.siswas.kelas', 'permohonan.siswas.jurusan', 'permohonan.kelas', 'permohonan.jurusan'])
            ->where('pos_qr_id', $pos->id)
            ->whereDate('created_at', today())
            ->latest()
            ->get();

        return view('pos.show', compact('pos', 'rekap', 'kirimanHariIni'));
    }

    /** Siswa mengirim data rekap izinnya ke QR pos ini. */
    public function kirim(Request $request, string $kode): RedirectResponse
    {
        $pos = PosQr::where('kode', $kode)->firstOrFail();

        if (! $pos->aktif) {
            return back()->withErrors(['izin_kode' => 'Pos ini sedang nonaktif dan tidak menerima kiriman rekap. Hubungi petugas.']);
        }

        $validated = $request->validate([
            'izin_kode' => 'required|string|max:30',
        ], [
            'izin_kode.required' => 'Isi kode izin dari halaman barcode permohonanmu.',
        ]);

        $permohonan = PermohonanIzin::where('kode', strtoupper(trim($validated['izin_kode'])))->first();

        if (! $permohonan) {
            return back()->withErrors(['izin_kode' => 'Kode izin tidak ditemukan. Periksa kembali kode pada halaman barcode permohonanmu.'])
                ->withInput();
        }

        if (! $permohonan->isDisetujuiSemua()) {
            return back()->withErrors(['izin_kode' => 'Izin ini belum disetujui semua guru ('.$permohonan->statusLabel().'). Rekap hanya bisa dikirim setelah semua guru menyetujui.'])
                ->withInput();
        }

        // Izin yang sudah pernah dikirim ke pos mana pun sudah dipakai dan
        // dinonaktifkan — tidak bisa dikirim/diakses lagi oleh siswa.
        if ($permohonan->sudahDipakai()) {
            return back()->withErrors(['izin_kode' => 'Kode izin ini sudah digunakan pada '.$permohonan->dipakai_pada->format('d-m-Y H:i').' dan sudah dinonaktifkan. Izin tidak bisa dipakai lagi.'])
                ->withInput();
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($pos, $permohonan) {
            PosKirim::firstOrCreate([
                'pos_qr_id' => $pos->id,
                'permohonan_id' => $permohonan->id,
            ]);

            $permohonan->update(['dipakai_pada' => now()]);
        });

        // Siswa langsung diarahkan ke form baru; rekapnya tersimpan di sisi
        // server untuk dicek satpam pada halaman pos ini.
        return redirect()->route('izin.create')
            ->with('success', 'Rekap izin '.$permohonan->kode.' berhasil dikirim ke '.$pos->nama.'. Izinmu sudah dipakai dan dinonaktifkan — form baru sudah dibuka bila kamu perlu mengajukan izin lagi.');
    }

    /**
     * Satpam menekan "Izinkan Keluar" setelah memeriksa rekap di halaman
     * hasil scan: data rekap tersebut dihapus dari QR ini (siswa sudah
     * benar-benar keluar, QR kembali kosong untuk pemakaian berikutnya).
     * Data permohonan di admin tidak ikut terhapus.
     */
    public function izinkan(string $kode, PosKirim $kirim): RedirectResponse
    {
        $pos = PosQr::where('kode', $kode)->firstOrFail();

        abort_unless($kirim->pos_qr_id === $pos->id, 404);

        $kirim->loadMissing('permohonan.siswas');
        $nama = $kirim->permohonan->siswas->pluck('nama_siswa');
        $label = $nama->isNotEmpty()
            ? $nama->first().($nama->count() > 1 ? ' dkk ('.$nama->count().' siswa)' : '')
            : $kirim->permohonan->nama_siswa;

        $kirim->delete();

        return redirect()->route('pos.show', $pos->kode)
            ->with('success', $label.' diizinkan keluar. Data rekapnya sudah dihapus dari QR ini.');
    }

    /** Halaman cetak QR pos (admin/satpam yang sudah login). */
    public function cetak(string $kode): View
    {
        $pos = PosQr::where('kode', $kode)->firstOrFail();

        return view('pos.cetak', compact('pos'));
    }
}
