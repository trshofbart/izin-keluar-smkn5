@extends('layouts.app')

@section('title', 'Barcode Izin ' . $permohonan->kode)

@section('content')
@php
    $semua = $permohonan->isDisetujuiSemua();
    $guruUrl = route('guru.show', $permohonan->barcode_kode);
@endphp

@if ($semua)
    <div class="bg-green-600 text-white rounded-2xl shadow p-5 text-center mb-4">
        <p class="text-2xl font-extrabold">DIIZINKAN MENINGGALKAN SEKOLAH</p>
        <p class="text-green-100 text-sm mt-1">Semua guru telah memberikan persetujuan.</p>
    </div>
@else
    <div class="bg-amber-100 border border-amber-300 text-amber-800 rounded-2xl shadow p-5 text-center mb-4">
        <p class="text-xl font-bold">{{ $permohonan->statusLabel() }}</p>
        <p class="text-sm mt-1">Tunjukkan barcode di bawah kepada guru untuk dipindai dan dimintai persetujuan.</p>
    </div>
@endif

<div class="bg-white rounded-2xl shadow p-5 sm:p-6" id="area-cetak">
    @if (! $semua)
        <div class="text-center">
            <div class="inline-block bg-white border-4 border-green-700 rounded-2xl p-4">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(240)->generate($guruUrl) !!}
            </div>
            <p class="mt-3 text-sm text-slate-500">Scan QR untuk halaman persetujuan guru</p>
            <p class="font-mono font-bold text-lg tracking-wider mt-1">{{ $permohonan->kode }}</p>
            <div class="mt-2">{!! \Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($permohonan->kode, 'C39', 2, 50) !!}</div>
            <p class="text-xs text-slate-400 break-all mt-1">{{ $guruUrl }}</p>
        </div>
        <hr class="my-5">
    @endif

    <h2 class="text-lg font-bold text-green-800 mb-3">{{ $semua ? 'Rekap Izin Keluar — Disetujui' : 'Detail Permohonan' }}</h2>

    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
        <dt class="text-slate-500">Kode Izin</dt><dd class="font-mono font-semibold">{{ $permohonan->kode }}</dd>
        <dt class="text-slate-500">Siswa</dt>
        <dd>
            @forelse ($permohonan->siswas as $s)
                <div class="font-semibold">{{ $s->nama_siswa }}</div>
                <div class="text-slate-500 text-xs mb-1">NIS {{ $s->nis ?? '—' }} · Absen {{ $s->no_absen }} · {{ $s->kelas->nama }} — {{ $s->jurusan->nama }}</div>
            @empty
                <div class="font-semibold">{{ $permohonan->nama_siswa }}</div>
                <div class="text-slate-500 text-xs">NIS {{ $permohonan->nis ?? '—' }} · Absen {{ $permohonan->no_absen }} · {{ $permohonan->kelas->nama }} — {{ $permohonan->jurusan->nama }}</div>
            @endforelse
        </dd>
        <dt class="text-slate-500">Tanggal</dt><dd>{{ $permohonan->tanggal->format('d-m-Y') }}</dd>
        <dt class="text-slate-500">Jam Ke</dt><dd>Jam ke-{{ $permohonan->jam_ke_mulai }} s.d. Jam ke-{{ $permohonan->jam_ke_selesai }}</dd>
        <dt class="text-slate-500">Keperluan</dt><dd>{{ $permohonan->keperluan }}</dd>
        <dt class="text-slate-500">Kendaraan</dt><dd>{{ $permohonan->kendaraan }}</dd>
        <dt class="text-slate-500">Cara Keluar</dt><dd>{{ $permohonan->cara_pulang === 'dijemput' ? 'Dijemput' . ($permohonan->nama_penjemput ? ' ('.$permohonan->nama_penjemput.')' : '') : 'Pulang Sendiri' }}</dd>
        <dt class="text-slate-500">Status</dt><dd class="font-semibold {{ $semua ? 'text-green-700' : 'text-amber-700' }}">{{ $permohonan->statusLabel() }}</dd>
    </dl>

    <h3 class="font-bold mt-6 mb-2">Persetujuan Guru</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm border rounded-lg overflow-hidden">
            <thead class="bg-green-700 text-white">
                <tr>
                    <th class="text-left px-3 py-2">Guru</th>
                    <th class="text-left px-3 py-2">Peran / Mapel</th>
                    <th class="text-left px-3 py-2">Status</th>
                    <th class="text-left px-3 py-2">Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permohonan->items as $item)
                    <tr class="border-t">
                        <td class="px-3 py-2 font-semibold">{{ $item->guru->nama }}</td>
                        <td class="px-3 py-2">{{ $item->peran === 'wali_kelas' ? 'Wali Kelas' : 'Guru Mapel — '.($item->mapel->nama ?? '-') }}</td>
                        <td class="px-3 py-2">
                            @if ($item->status === 'disetujui')
                                <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs font-bold">Disetujui</span>
                            @else
                                <span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full text-xs font-bold">Menunggu</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            {{ $item->disetujui_pada ? $item->disetujui_pada->format('d-m-Y H:i') : '—' }}
                            @if ($item->persetujuan->first())
                                <span class="text-xs text-slate-400 block">token {{ $item->persetujuan->first()->tokenMasked() }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($semua)
        <p class="text-sm text-slate-600 mt-4">Rekap ini adalah bukti izin keluar sekolah yang telah disetujui oleh semua guru yang bersangkutan dan wali kelas.</p>
    @endif
</div>

<div class="flex gap-3 mt-4">
    @if ($semua)
        <button onclick="window.print()" class="flex-1 bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl">Cetak Rekap</button>
    @endif
    <a href="{{ $guruUrl }}" class="flex-1 text-center bg-white border border-green-700 text-green-800 font-bold py-3 rounded-xl">Buka Halaman Guru</a>
</div>
@endsection
