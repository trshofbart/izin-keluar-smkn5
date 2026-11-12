@extends('layouts.app')

@section('title', 'Halaman Satpam')

@section('content')
<div class="bg-white rounded-2xl shadow p-5 sm:p-6">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-green-800">Halaman Satpam</h2>
            <p class="text-sm text-slate-500 mt-1">{{ auth()->user()->name }} · {{ now()->format('d-m-Y') }}</p>
        </div>
        <form method="POST" action="{{ route('satpam.logout') }}">
            @csrf
            <button type="submit" class="text-sm bg-white border border-red-300 text-red-700 font-semibold px-3 py-2 rounded-lg">Keluar</button>
        </form>
    </div>

    <h3 class="font-bold mt-6 mb-2">Cek QR Pos</h3>
    <p class="text-sm text-slate-500 mb-3">Pindai QR pos yang tercetak, atau ketik kode posnya, untuk melihat rekap yang sudah masuk ke QR tersebut.</p>
    @include('partials.scan-pos', ['mode' => 'satpam'])
    <form method="POST" action="{{ route('satpam.cek') }}" class="mt-3 flex gap-2">
        @csrf
        <input type="text" name="kode" required placeholder="Kode pos / tempel URL hasil scan"
            class="flex-1 border rounded-lg px-3 py-2 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-green-600">
        <button type="submit" class="bg-green-700 hover:bg-green-800 text-white font-bold px-4 py-2 rounded-lg">Cek</button>
    </form>

    <h3 class="font-bold mt-6 mb-2">Daftar Pos QR</h3>
    <div class="space-y-2">
        @forelse ($posList as $pos)
            <a href="{{ route('satpam.pos', $pos->kode) }}" class="flex items-center justify-between border rounded-xl px-4 py-3 hover:bg-green-50">
                <span class="font-semibold">{{ $pos->nama }}</span>
                <span class="text-sm {{ $pos->kirims_hari_ini > 0 ? 'text-green-700 font-bold' : 'text-slate-400' }}">
                    {{ $pos->kirims_hari_ini }} rekap hari ini →
                </span>
            </a>
        @empty
            <p class="text-sm text-slate-500">Belum ada QR pos. Admin dapat membuatnya di menu "QR Pos Statis" pada panel admin.</p>
        @endforelse
    </div>

    <h3 class="font-bold mt-6 mb-2">Rekap Masuk Hari Ini ({{ $kirimanHariIni->count() }})</h3>
    <div class="space-y-2">
        @forelse ($kirimanHariIni as $kirim)
            @include('partials.kiriman-item', ['kirim' => $kirim, 'tampilkanPos' => true])
        @empty
            <div class="bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 text-sm font-semibold">
                Belum ada rekap izin yang dikirim siswa hari ini.
            </div>
        @endforelse
    </div>
</div>
@endsection
