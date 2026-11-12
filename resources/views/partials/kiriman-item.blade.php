{{-- Satu baris rekap terkirim (PosKirim) untuk halaman satpam. Butuh $kirim dengan relasi permohonan termuat. --}}
@php $p = $kirim->permohonan; @endphp
<div class="border rounded-xl p-3 bg-white">
    <div class="flex items-start justify-between gap-3">
        <div>
            @forelse ($p->siswas as $s)
                <p class="font-semibold leading-snug">{{ $s->nama_siswa }} <span class="text-slate-400 font-normal text-xs">· {{ $s->kelas->nama }} · Absen {{ $s->no_absen }}</span></p>
            @empty
                <p class="font-semibold leading-snug">{{ $p->nama_siswa }} <span class="text-slate-400 font-normal text-xs">· {{ $p->kelas->nama }} · Absen {{ $p->no_absen }}</span></p>
            @endforelse
        </div>
        <span class="shrink-0 bg-green-100 text-green-800 px-2 py-0.5 rounded-full text-xs font-bold">Disetujui Semua</span>
    </div>
    <p class="text-xs text-slate-500 mt-1">
        {{ $p->kode }} · Jam ke-{{ $p->jam_ke_mulai }} s.d. {{ $p->jam_ke_selesai }} ·
        {{ $p->caraKeluarLabel() }} ·
        masuk {{ $kirim->created_at->format('H:i') }}@isset($tampilkanPos) · {{ $kirim->posQr->nama }}@endisset
    </p>
    <p class="text-sm mt-1">{{ $p->keperluan }}</p>
    @isset($tombolIzinkan)
        <form method="POST" action="{{ route('pos.izinkan', [$posKode, $kirim->id]) }}" class="mt-3"
            onsubmit="return confirm('Izinkan siswa ini keluar? Data rekapnya akan dihapus dari QR ini.');">
            @csrf
            <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-2.5 rounded-lg">
                ✅ Izinkan Keluar
            </button>
        </form>
    @endisset
</div>
