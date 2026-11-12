@extends('layouts.app')

@section('title', 'Cek Pos — ' . $pos->nama)

@section('content')
<div class="bg-white rounded-2xl shadow p-5 sm:p-6">
    <a href="{{ route('satpam.index') }}" class="text-sm text-green-800 font-semibold">← Kembali ke Halaman Satpam</a>

    <h2 class="text-xl font-bold text-green-800 mt-3">{{ $pos->nama }}</h2>
    <p class="text-sm text-slate-500 mt-1">
        Status QR: {{ $pos->aktif ? 'Aktif' : 'Nonaktif' }} ·
        Total rekap terkirim: {{ $kiriman->count() }} · Hari ini: {{ $kirimanHariIni->count() }}
    </p>

    <h3 class="font-bold mt-6 mb-2">Rekap di QR Ini — Hari Ini</h3>
    <div class="space-y-2">
        @forelse ($kirimanHariIni as $kirim)
            @include('partials.kiriman-item', ['kirim' => $kirim])
        @empty
            <div class="bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 text-sm font-semibold">
                QR ini <u>belum berisi</u> rekap izin untuk hari ini. Siswa yang mengaku sudah
                mengirim rekap ke pos ini hari ini belum tercatat — minta ia mengirim ulang
                dari halaman pos.
            </div>
        @endforelse
    </div>

    @if ($kiriman->count() > $kirimanHariIni->count())
        <h3 class="font-bold mt-6 mb-2">Semua Rekap di QR Ini</h3>
        <div class="space-y-2">
            @foreach ($kiriman as $kirim)
                <div class="border rounded-xl p-3">
                    <p class="text-xs text-slate-400">{{ $kirim->created_at->format('d-m-Y H:i') }}</p>
                    @include('partials.kiriman-item', ['kirim' => $kirim])
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
