<?php

use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\IzinController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SatpamController;
use App\Http\Middleware\SatpamMiddleware;
use Illuminate\Support\Facades\Route;

// Halaman publik siswa — tanpa login.
Route::get('/', [IzinController::class, 'create'])->name('izin.create');
Route::post('/izin', [IzinController::class, 'store'])->name('izin.store');
Route::get('/izin/{kode}', [IzinController::class, 'show'])->name('izin.show');

// Halaman guru — dibuka lewat scan QR/barcode.
Route::get('/guru/{barcode_kode}', [GuruController::class, 'show'])->name('guru.show');
Route::post('/guru/{barcode_kode}/setujui', [GuruController::class, 'approve'])->name('guru.approve');

// QR pos statis — tujuan scan QR yang dicetak admin (mis. di pos satpam).
// Siswa membuka halaman ini lalu mengirim rekap izinnya ke pos tersebut.
Route::get('/pos/{kode}', [PosController::class, 'show'])->name('pos.show');
Route::post('/pos/{kode}/kirim', [PosController::class, 'kirim'])->name('pos.kirim');
Route::post('/pos/{kode}/izinkan/{kirim}', [PosController::class, 'izinkan'])->name('pos.izinkan');

// Halaman satpam (role satpam; admin juga boleh masuk).
Route::get('/satpam/login', [SatpamController::class, 'loginForm'])->name('satpam.login');
Route::post('/satpam/login', [SatpamController::class, 'login'])->name('satpam.login.store');
Route::post('/satpam/logout', [SatpamController::class, 'logout'])->name('satpam.logout')->middleware('auth');
Route::middleware(['auth', SatpamMiddleware::class])->group(function () {
    Route::get('/satpam', [SatpamController::class, 'index'])->name('satpam.index');
    Route::post('/satpam/cek', [SatpamController::class, 'cek'])->name('satpam.cek');
    Route::get('/satpam/pos/{kode}', [SatpamController::class, 'pos'])->name('satpam.pos');
    Route::get('/pos/{kode}/cetak', [PosController::class, 'cetak'])->name('pos.cetak');
});

// PWA offline fallback.
Route::get('/offline', fn () => view('offline'))->name('offline');

// Fallback login admin: route admin/login milik Filament hanya GET (login
// normal lewat Livewire). Bila Livewire tidak jalan, form terkirim sebagai
// POST biasa dan Laravel menjawab 405 — route ini yang menangkapnya.
Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.fallback');
