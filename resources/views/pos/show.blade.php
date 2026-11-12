@extends('layouts.app')

@section('title', 'Pos — ' . $pos->nama)

@section('content')
<div class="bg-white rounded-2xl shadow p-5 sm:p-6">
    <h2 class="text-xl font-bold text-green-800">{{ $pos->nama }}</h2>
    <p class="text-sm text-slate-500 mt-1">Kirim rekap izin keluar yang <strong>sudah disetujui semua guru</strong> ke pos ini.</p>

    @if (! $pos->aktif)
        <div class="mt-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg font-semibold">
            Pos ini sedang nonaktif dan tidak menerima kiriman rekap. Hubungi petugas.
        </div>
    @elseif ($rekap)
        <div class="mt-4 bg-green-50 border border-green-300 rounded-xl p-4">
            <p class="font-bold text-green-800">✅ Rekap terkirim ke pos ini</p>
            @php $p = $rekap->permohonan; @endphp
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm mt-3">
                <dt class="text-slate-500">Kode Izin</dt><dd class="font-mono font-semibold">{{ $p->kode }}</dd>
                <dt class="text-slate-500">Siswa</dt>
                <dd>
                    @forelse ($p->siswas as $s)
                        <div class="font-semibold">{{ $s->nama_siswa }}</div>
                        <div class="text-slate-500 text-xs mb-1">{{ $s->kelas->nama }} — {{ $s->jurusan->nama }} · Absen {{ $s->no_absen }}</div>
                    @empty
                        <div class="font-semibold">{{ $p->nama_siswa }}</div>
                        <div class="text-slate-500 text-xs">{{ $p->kelas->nama }} — {{ $p->jurusan->nama }} · Absen {{ $p->no_absen }}</div>
                    @endforelse
                </dd>
                <dt class="text-slate-500">Jam Ke</dt><dd>Jam ke-{{ $p->jam_ke_mulai }} s.d. Jam ke-{{ $p->jam_ke_selesai }}</dd>
                <dt class="text-slate-500">Keperluan</dt><dd>{{ $p->keperluan }}</dd>
                <dt class="text-slate-500">Cara Keluar</dt><dd>{{ $p->cara_pulang === 'dijemput' ? 'Dijemput' . ($p->nama_penjemput ? ' ('.$p->nama_penjemput.')' : '') : 'Pulang Sendiri' }}</dd>
                <dt class="text-slate-500">Dikirim</dt><dd>{{ $rekap->created_at->format('d-m-Y H:i') }}</dd>
            </dl>
            <a href="{{ route('izin.show', $p->kode) }}" class="block text-center mt-4 bg-white border border-green-700 text-green-800 font-bold py-2.5 rounded-xl">Lihat Halaman Barcode Izin</a>
        </div>
    @else
        <form method="POST" action="{{ route('pos.kirim', $pos->kode) }}" class="mt-4">
            @csrf
            <label for="izin_kode" class="block text-sm font-semibold mb-1">Kode Izin *</label>
            <input type="text" id="izin_kode" name="izin_kode" value="{{ old('izin_kode') }}" required
                class="w-full border rounded-lg px-3 py-2 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-green-600"
                placeholder="Contoh: IZN-20261112-AB12">
            <p class="text-xs text-slate-500 mt-1">Kode ada di halaman barcode permohonanmu (setelah semua guru menyetujui).</p>
            <button type="submit" class="mt-4 w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl">
                Kirim Rekap ke Pos Ini
            </button>
        </form>
    @endif

    <hr class="my-6">
    <h3 class="font-bold mb-2">Rekap Masuk ke QR Ini — Hari Ini ({{ $kirimanHariIni->count() }})</h3>
    <p class="text-sm text-slate-500 mb-3">Daftar ini adalah isi QR ini untuk pemeriksaan satpam — tampil langsung setelah scan, tanpa login. Cocokkan nama siswa dengan yang membawa QR.</p>
    <div class="space-y-2">
        @forelse ($kirimanHariIni as $kirim)
            @include('partials.kiriman-item', ['kirim' => $kirim])
        @empty
            <div class="bg-amber-50 border border-amber-300 text-amber-800 rounded-xl px-4 py-3 text-sm font-semibold">
                QR ini belum berisi rekap izin untuk hari ini.
            </div>
        @endforelse
    </div>

    <p class="text-xs text-slate-400 mt-6">Satpam juga dapat login (opsional) untuk melihat rekap semua pos sekaligus di <a href="{{ route('satpam.login') }}" class="underline">halaman satpam</a>.</p>
</div>
@endsection
