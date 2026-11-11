@extends('layouts.app')

@section('title', 'Offline')

@section('content')
<div class="bg-white rounded-2xl shadow p-6 text-center">
    <h2 class="text-xl font-bold text-green-800">Anda Sedang Offline</h2>
    <p class="text-sm text-slate-500 mt-2">Koneksi internet terputus. Periksa koneksi Anda, lalu coba lagi.</p>
    <a href="{{ route('izin.create') }}" class="inline-block mt-5 bg-green-700 hover:bg-green-800 text-white font-bold px-6 py-3 rounded-xl">Kembali ke Form Izin</a>
</div>
@endsection
