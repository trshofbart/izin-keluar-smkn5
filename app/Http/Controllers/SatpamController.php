<?php

namespace App\Http\Controllers;

use App\Models\PosKirim;
use App\Models\PosQr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SatpamController extends Controller
{
    public function loginForm(): View
    {
        return view('satpam.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect()->intended('/admin');
        }

        if (! $user->isSatpam()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Akun ini tidak memiliki akses satpam.'])->onlyInput('email');
        }

        return redirect()->intended(route('satpam.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('satpam.login');
    }

    /** Dashboard satpam: semua pos + kiriman rekap hari ini. */
    public function index(): View
    {
        $posList = PosQr::withCount(['kirims as kirims_hari_ini' => fn ($q) => $q->whereDate('created_at', today())])
            ->orderBy('nama')
            ->get();

        $kirimanHariIni = PosKirim::with(['posQr', 'permohonan.siswas.kelas', 'permohonan.siswas.jurusan', 'permohonan.kelas', 'permohonan.jurusan'])
            ->whereDate('created_at', today())
            ->latest()
            ->limit(100)
            ->get();

        return view('satpam.index', compact('posList', 'kirimanHariIni'));
    }

    /** Cek isi satu QR pos: rekap apa saja yang sudah dikirim ke pos ini. */
    public function pos(string $kode): View
    {
        $pos = PosQr::where('kode', $kode)->firstOrFail();

        $kiriman = PosKirim::with(['permohonan.siswas.kelas', 'permohonan.siswas.jurusan', 'permohonan.kelas', 'permohonan.jurusan', 'permohonan.items'])
            ->where('pos_qr_id', $pos->id)
            ->latest()
            ->limit(200)
            ->get();

        $kirimanHariIni = $kiriman->filter(fn (PosKirim $k) => $k->created_at->isToday())->values();

        return view('satpam.pos', compact('pos', 'kiriman', 'kirimanHariIni'));
    }

    /** Form "cek QR" di dashboard: terima kode pos lalu arahkan ke halamannya. */
    public function cek(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:40',
        ]);

        // Hasil scan berupa URL penuh — ambil segmen terakhirnya sebagai kode.
        $kode = trim($validated['kode']);
        if (str_contains($kode, '/')) {
            $kode = basename(parse_url($kode, PHP_URL_PATH) ?? $kode);
        }

        $pos = PosQr::where('kode', $kode)->first();

        if (! $pos) {
            return back()->withErrors(['kode' => 'Kode QR pos tidak ditemukan.']);
        }

        return redirect()->route('satpam.pos', $pos->kode);
    }
}
