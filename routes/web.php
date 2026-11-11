<?php

use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\IzinController;
use Illuminate\Support\Facades\Route;

// Halaman publik siswa — tanpa login.
Route::get('/', [IzinController::class, 'create'])->name('izin.create');
Route::post('/izin', [IzinController::class, 'store'])->name('izin.store');
Route::get('/izin/{kode}', [IzinController::class, 'show'])->name('izin.show');

// Halaman guru — dibuka lewat scan QR/barcode.
Route::get('/guru/{barcode_kode}', [GuruController::class, 'show'])->name('guru.show');
Route::post('/guru/{barcode_kode}/setujui', [GuruController::class, 'approve'])->name('guru.approve');

// PWA offline fallback.
Route::get('/offline', fn () => view('offline'))->name('offline');

// Fallback login admin: route admin/login milik Filament hanya GET (login
// normal lewat Livewire). Bila Livewire tidak jalan, form terkirim sebagai
// POST biasa dan Laravel menjawab 405 — route ini yang menangkapnya.
Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.fallback');
