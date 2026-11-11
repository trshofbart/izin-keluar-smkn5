<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#15803d">
    <meta name="description" content="Aplikasi Izin Keluar Sekolah SMKN 5 Surakarta — permohonan izin, persetujuan guru via barcode, dan rekap.">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <title>@yield('title', 'Izin Keluar Sekolah') — SMKN 5 Surakarta</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- Fallback bila hasil build Vite tidak ada (mis. clone dari GitHub tanpa npm run build):
             Tailwind dimuat dari CDN supaya tampilan tetap rapi. --}}
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(() => {});
            });
        }
    </script>
</head>
<body class="bg-green-50 min-h-screen text-slate-800">
    <header class="bg-green-700 text-white shadow">
        <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
            <img src="{{ asset('icons/icon-192.png') }}" alt="Logo SMKN 5" class="w-11 h-11 rounded-full bg-white p-1">
            <div>
                <h1 class="font-bold text-lg leading-tight">Izin Keluar Sekolah</h1>
                <p class="text-green-100 text-sm leading-tight">SMKN 5 Surakarta</p>
            </div>
            <a href="{{ route('izin.create') }}" class="ml-auto text-sm bg-green-800 hover:bg-green-900 px-3 py-2 rounded-lg">Form Baru</a>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-6">
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg">
                <p class="font-semibold mb-1">Periksa kembali isian berikut:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="max-w-2xl mx-auto px-4 pb-8 text-center text-xs text-slate-500">
        SMKN 5 Surakarta &middot; Izin keluar sekolah hanya berlaku setelah semua guru yang bersangkutan memberikan persetujuan.
    </footer>
</body>
</html>
