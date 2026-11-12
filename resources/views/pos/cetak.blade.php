<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak QR — {{ $pos->nama }}</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="bg-white border-4 border-green-700 rounded-3xl shadow p-8 max-w-md w-full text-center">
        <p class="text-sm font-bold tracking-widest text-green-700">SMKN 5 SURAKARTA</p>
        <h1 class="text-2xl font-extrabold mt-1">IZIN KELUAR SEKOLAH</h1>
        <p class="text-lg font-bold text-green-800 mt-1">{{ $pos->nama }}</p>

        <div class="inline-block bg-white border-2 border-slate-300 rounded-2xl p-4 mt-5">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)->generate($pos->url()) !!}
        </div>

        <p class="mt-5 font-bold">SCAN QR INI SETELAH IZIN DISETUJUI SEMUA GURU</p>
        <p class="text-sm text-slate-500 mt-1">untuk mengirim rekap izin keluar ke pos ini.</p>
        <p class="text-xs text-slate-400 break-all mt-3">{{ $pos->url() }}</p>

        <button onclick="window.print()" class="no-print mt-6 bg-green-700 hover:bg-green-800 text-white font-bold px-6 py-3 rounded-xl">
            🖨️ Cetak
        </button>
    </div>
</body>
</html>
