@extends('layouts.app')

@section('title', 'Form Permohonan Izin Keluar')

@section('content')
<div class="bg-white rounded-2xl shadow p-5 sm:p-6">
    <h2 class="text-xl font-bold text-green-800">Form Permohonan Izin Keluar Sekolah</h2>
    <p class="text-sm text-slate-500 mt-1 mb-5">Isi dengan benar. Setelah submit, barcode akan muncul untuk dipindai guru.</p>

    <form method="POST" action="{{ route('izin.store') }}" id="form-izin">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="nama_siswa" class="block text-sm font-semibold mb-1">Nama Lengkap *</label>
                <input type="text" id="nama_siswa" name="nama_siswa" value="{{ old('nama_siswa') }}" required
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="Nama siswa">
            </div>
            <div>
                <label for="nis" class="block text-sm font-semibold mb-1">NIS</label>
                <input type="text" id="nis" name="nis" value="{{ old('nis') }}"
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="Nomor Induk Siswa (opsional)">
            </div>
            <div>
                <label for="no_absen" class="block text-sm font-semibold mb-1">No. Absen *</label>
                <input type="text" id="no_absen" name="no_absen" value="{{ old('no_absen') }}" required
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="Contoh: 12">
            </div>
            <div>
                <label for="kelas_id" class="block text-sm font-semibold mb-1">Kelas *</label>
                <select id="kelas_id" name="kelas_id" required
                    class="w-full border rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-green-600">
                    <option value="">— Pilih Kelas —</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" data-jurusan="{{ $k->jurusan->nama }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4">
            <label class="block text-sm font-semibold mb-1">Jurusan</label>
            <input type="text" id="jurusan_nama" readonly
                class="w-full border rounded-lg px-3 py-2 bg-slate-100 text-slate-600"
                placeholder="Terisi otomatis setelah memilih kelas">
        </div>

        <div class="mt-4">
            <label for="keperluan" class="block text-sm font-semibold mb-1">Keperluan *</label>
            <textarea id="keperluan" name="keperluan" rows="3" required
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600"
                placeholder="Contoh: Berobat ke puskesmas, ada keperluan keluarga, dll.">{{ old('keperluan') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label for="jam_ke_mulai" class="block text-sm font-semibold mb-1">Jam Ke (mulai) *</label>
                <select id="jam_ke_mulai" name="jam_ke_mulai" required
                    class="w-full border rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-green-600">
                    @for ($j = 1; $j <= 10; $j++)
                        <option value="{{ $j }}" {{ old('jam_ke_mulai', 1) == $j ? 'selected' : '' }}>Jam ke-{{ $j }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="jam_ke_selesai" class="block text-sm font-semibold mb-1">Jam Ke (selesai) *</label>
                <select id="jam_ke_selesai" name="jam_ke_selesai" required
                    class="w-full border rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-green-600">
                    @for ($j = 1; $j <= 10; $j++)
                        <option value="{{ $j }}" {{ old('jam_ke_selesai', 1) == $j ? 'selected' : '' }}>Jam ke-{{ $j }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-semibold">Mapel &amp; Guru yang Ditinggal *</label>
                <button type="button" id="btn-tambah" class="text-sm bg-green-100 text-green-800 font-semibold px-3 py-1.5 rounded-lg hover:bg-green-200">+ Tambah Mapel &amp; Guru</button>
            </div>
            <p class="text-xs text-slate-500 mt-1">Minimal 1 baris. Guru difilter sesuai mapel yang dipilih (dari mapping guru &amp; mapel di admin).</p>
            <div id="items-container" class="mt-3 space-y-3">
                @php $oldItems = old('items', [['mapel_id' => '', 'guru_id' => '']]); @endphp
                @foreach ($oldItems as $idx => $row)
                    <div class="item-row grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end border rounded-xl p-3 bg-slate-50">
                        <div>
                            <label class="block text-xs font-semibold mb-1">Mata Pelajaran</label>
                            <select name="items[{{ $idx }}][mapel_id]" class="mapel-select w-full border rounded-lg px-3 py-2 bg-white" required>
                                <option value="">— Pilih Mapel —</option>
                                @foreach ($mapel as $m)
                                    <option value="{{ $m->id }}" {{ ($row['mapel_id'] ?? '') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold mb-1">Guru yang Ditinggal</label>
                            <select name="items[{{ $idx }}][guru_id]" class="guru-select w-full border rounded-lg px-3 py-2 bg-white" required data-selected="{{ $row['guru_id'] ?? '' }}">
                                <option value="">— Pilih Guru —</option>
                            </select>
                        </div>
                        <button type="button" class="btn-hapus text-sm text-red-600 font-semibold px-2 py-2 hover:underline">Hapus</button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
            <div>
                <label for="kendaraan" class="block text-sm font-semibold mb-1">Kendaraan *</label>
                <select id="kendaraan" name="kendaraan" required
                    class="w-full border rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-green-600">
                    <option value="">— Pilih Kendaraan —</option>
                    @foreach (['Sepeda Motor', 'Mobil', 'Sepeda', 'Jalan Kaki', 'Antar Jemput', 'Lainnya'] as $k)
                        <option value="{{ $k }}" {{ old('kendaraan') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <span class="block text-sm font-semibold mb-1">Cara Pulang *</span>
                <div class="flex gap-4 pt-2">
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="cara_pulang" value="dijemput" class="accent-green-700" {{ old('cara_pulang') == 'dijemput' ? 'checked' : '' }} required>
                        Dijemput
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="radio" name="cara_pulang" value="pulang_sendiri" class="accent-green-700" {{ old('cara_pulang') == 'pulang_sendiri' ? 'checked' : '' }}>
                        Pulang Sendiri
                    </label>
                </div>
                <label class="flex items-center gap-2 text-xs text-slate-500 mt-2">
                    <input type="checkbox" id="cek-persetujuan" class="accent-green-700" required>
                    Saya menyatakan data di atas benar.
                </label>
            </div>
        </div>

        <div id="penjemput-wrap" class="mt-4 hidden">
            <label for="nama_penjemput" class="block text-sm font-semibold mb-1">Nama Penjemput *</label>
            <input type="text" id="nama_penjemput" name="nama_penjemput" value="{{ old('nama_penjemput') }}"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" placeholder="Nama orang yang menjemput">
        </div>

        <button type="submit" class="mt-6 w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl text-lg">
            Buat Barcode Izin
        </button>
    </form>
</div>

<script>
const guruByMapel = @json($guruMapel->groupBy('mapel_id')->map(fn($rows) => $rows->map(fn($r) => ['id' => $r->guru->id, 'nama' => $r->guru->nama])->unique('id')->values()));
const semuaGuru = @json($guruMapel->map(fn($r) => ['id' => $r->guru->id, 'nama' => $r->guru->nama])->unique('id')->values());
const mapelOptions = @json($mapel->map(fn($m) => ['id' => $m->id, 'nama' => $m->nama]));

function isiGuru(row) {
    const mapelSelect = row.querySelector('.mapel-select');
    const guruSelect = row.querySelector('.guru-select');
    const selected = guruSelect.dataset.selected || guruSelect.value;
    const daftar = guruByMapel[mapelSelect.value] || semuaGuru;
    guruSelect.innerHTML = '<option value="">— Pilih Guru —</option>';
    daftar.forEach(g => {
        const opt = document.createElement('option');
        opt.value = g.id;
        opt.textContent = g.nama;
        if (String(g.id) === String(selected)) opt.selected = true;
        guruSelect.appendChild(opt);
    });
    guruSelect.dataset.selected = '';
}

document.querySelectorAll('.item-row').forEach(row => {
    isiGuru(row);
    row.querySelector('.mapel-select').addEventListener('change', () => isiGuru(row));
    row.querySelector('.btn-hapus').addEventListener('click', () => {
        if (document.querySelectorAll('.item-row').length > 1) row.remove();
        else alert('Minimal 1 baris mapel & guru.');
    });
});

let itemIndex = {{ count($oldItems) }};
document.getElementById('btn-tambah').addEventListener('click', () => {
    const container = document.getElementById('items-container');
    const row = document.createElement('div');
    row.className = 'item-row grid grid-cols-1 sm:grid-cols-[1fr_1fr_auto] gap-2 items-end border rounded-xl p-3 bg-slate-50';
    const mapelOpts = '<option value="">— Pilih Mapel —</option>' + mapelOptions.map(m => `<option value="${m.id}">${m.nama}</option>`).join('');
    row.innerHTML = `
        <div>
            <label class="block text-xs font-semibold mb-1">Mata Pelajaran</label>
            <select name="items[${itemIndex}][mapel_id]" class="mapel-select w-full border rounded-lg px-3 py-2 bg-white" required>${mapelOpts}</select>
        </div>
        <div>
            <label class="block text-xs font-semibold mb-1">Guru yang Ditinggal</label>
            <select name="items[${itemIndex}][guru_id]" class="guru-select w-full border rounded-lg px-3 py-2 bg-white" required><option value="">— Pilih Guru —</option></select>
        </div>
        <button type="button" class="btn-hapus text-sm text-red-600 font-semibold px-2 py-2 hover:underline">Hapus</button>`;
    container.appendChild(row);
    itemIndex++;
    isiGuru(row);
    row.querySelector('.mapel-select').addEventListener('change', () => isiGuru(row));
    row.querySelector('.btn-hapus').addEventListener('click', () => {
        if (document.querySelectorAll('.item-row').length > 1) row.remove();
        else alert('Minimal 1 baris mapel & guru.');
    });
});

// Jurusan terisi otomatis dari kelas yang dipilih.
const kelasSelect = document.getElementById('kelas_id');
const jurusanInput = document.getElementById('jurusan_nama');
function updateJurusan() {
    const opt = kelasSelect.options[kelasSelect.selectedIndex];
    jurusanInput.value = opt ? (opt.dataset.jurusan || '') : '';
}
kelasSelect.addEventListener('change', updateJurusan);
updateJurusan();

// Nama penjemput muncul bila "Dijemput".
const penjemputWrap = document.getElementById('penjemput-wrap');
const penjemputInput = document.getElementById('nama_penjemput');
function updatePenjemput() {
    const dijemput = document.querySelector('input[name="cara_pulang"]:checked')?.value === 'dijemput';
    penjemputWrap.classList.toggle('hidden', !dijemput);
    penjemputInput.required = dijemput;
}
document.querySelectorAll('input[name="cara_pulang"]').forEach(r => r.addEventListener('change', updatePenjemput));
updatePenjemput();
</script>
@endsection
