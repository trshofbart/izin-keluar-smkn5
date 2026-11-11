<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Fallback login admin (POST /admin/login).
 *
 * Normalnya login Filament berjalan lewat Livewire (POST livewire/update),
 * dan route `admin/login` milik Filament hanya menerima GET — sehingga bila
 * JavaScript Livewire tidak jalan (aset gagal dimuat, mis. diakses lewat
 * subdirektori/IP dengan APP_URL yang tidak cocok, atau cache lama), form
 * login terkirim sebagai POST biasa dan Laravel menjawab 405
 * "POST method is not supported for route admin/login".
 *
 * Route ini menangkap POST tersebut dan memproses login yang sama, supaya
 * admin tetap bisa masuk. Bila Livewire normal, route ini tidak pernah
 * dipakai.
 */
class AdminLoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }
}
