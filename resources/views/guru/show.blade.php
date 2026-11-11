@extends('layouts.app')

@section('title', 'Persetujuan Guru — ' . $permohonan->kode)

@section('content')
<div class="bg-white rounded-2xl shadow p-5 sm:p-6">
    <h2 class="text-xl font-bold text-green-800">Persetujuan Guru</h2>
    <p class="text-sm text-slate-500 mt-1">Halaman ini dibuka lewat scan barcode siswa.</p>

    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm mt-4 bg-slate-50 border rounded-xl p-4">
        <dt class="text-slate-500">Nama Siswa</dt><dd class="font-semibold">{{ $permohonan->nama_siswa }}</dd>
        <dt class="text-slate-500">NIS / Kelas</dt><dd>{{ $permohonan->nis }} / {{ $permohonan->kelas->nama }}</dd>
        <dt class="text-slate-500">Jurusan</dt><dd>{{ $permohonan->jurusan->nama }}</dd>
        <dt class="text-slate-500">Keperluan</dt><dd>{{ $permohonan->keperluan }}</dd>
        <dt class="text-slate-500">Jam Ke</dt><dd>Jam ke-{{ $permohonan->jam_ke_mulai }} s.d. Jam ke-{{ $permohonan->jam_ke_selesai }}</dd>
        <dt class="text-slate-500">Status</dt><dd class="font-semibold">{{ $permohonan->statusLabel() }}</dd>
    </dl>

    <h3 class="font-bold mt-6 mb-2">Daftar Guru yang Dimintai Izin</h3>
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
                        <td class="px-3 py-2">{{ $item->disetujui_pada ? $item->disetujui_pada->format('d-m-Y H:i') : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($pendingItems->isNotEmpty())
        <hr class="my-6">
        <h3 class="font-bold mb-2">Berikan Izin</h3>
        <p class="text-sm text-slate-500 mb-3">Pilih nama Anda, lalu masukkan token yang dibuat oleh admin. Token hanya berlaku untuk guru yang bersangkutan.</p>
        <form method="POST" action="{{ route('guru.approve', $permohonan->barcode_kode) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="permohonan_item_id" class="block text-sm font-semibold mb-1">1. Nama Guru *</label>
                    <select id="permohonan_item_id" name="permohonan_item_id" required
                        class="w-full border rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-green-600">
                        <option value="">— Pilih Nama Anda —</option>
                        @foreach ($pendingItems as $item)
                            <option value="{{ $item->id }}" {{ old('permohonan_item_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->guru->nama }} — {{ $item->peran === 'wali_kelas' ? 'Wali Kelas' : ($item->mapel->nama ?? 'Guru Mapel') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="token" class="block text-sm font-semibold mb-1">2. Token Persetujuan *</label>
                    <input type="text" id="token" name="token" value="{{ old('token') }}" required maxlength="10" autocomplete="off"
                        class="w-full border rounded-lg px-3 py-2 font-mono tracking-widest uppercase focus:outline-none focus:ring-2 focus:ring-green-600"
                        placeholder="Contoh: A1B2C3">
                    <p class="text-xs text-slate-500 mt-1">Token 6 karakter yang dibuat admin untuk Anda. Token salah tidak akan tersimpan.</p>
                </div>
                <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl">
                    Saya Izinkan Siswa Ini Keluar
                </button>
            </div>
        </form>
    @else
        <div class="mt-6 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg font-semibold text-center">
            Semua guru sudah memberikan persetujuan. Siswa diizinkan meninggalkan sekolah.
        </div>
    @endif

    <a href="{{ route('izin.show', $permohonan->kode) }}" class="block text-center mt-4 bg-white border border-green-700 text-green-800 font-bold py-3 rounded-xl">
        Lihat Status &amp; Rekap Permohonan
    </a>
</div>
@endsection
