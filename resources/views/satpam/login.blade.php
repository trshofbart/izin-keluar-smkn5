@extends('layouts.app')

@section('title', 'Login Satpam')

@section('content')
<div class="bg-white rounded-2xl shadow p-5 sm:p-6">
    <h2 class="text-xl font-bold text-green-800">Login Satpam</h2>
    <p class="text-sm text-slate-500 mt-1 mb-5">Masuk untuk memeriksa rekap izin keluar yang dikirim siswa ke QR pos.</p>

    <form method="POST" action="{{ route('satpam.login.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-semibold mb-1">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="satpam@smkn5.sch.id">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold mb-1">Password *</label>
                <input type="password" id="password" name="password" required
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="Password">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-500">
                <input type="checkbox" name="remember" value="1" class="accent-green-700">
                Ingat saya di perangkat ini
            </label>
            <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl">
                Masuk
            </button>
        </div>
    </form>
</div>
@endsection
