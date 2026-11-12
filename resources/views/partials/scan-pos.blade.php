{{--
    Fitur scan QR pos statis.
    Parameter: $mode = 'siswa' (menuju /pos/{kode}) atau 'satpam' (menuju /satpam/pos/{kode}).
    Memakai library html5-qrcode dari CDN; bila kamera/CDN tidak tersedia,
    pengguna diarahkan memindai QR dengan kamera bawaan HP.
--}}
@php $uid = 'scan' . uniqid(); $mode = $mode ?? 'siswa'; @endphp
<div>
    <button type="button" id="{{ $uid }}-btn"
        class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl">
        📷 Scan QR Pos Satpam
    </button>
    <div id="{{ $uid }}-reader" class="hidden mt-3 overflow-hidden rounded-xl border"></div>
    <p id="{{ $uid }}-status" class="hidden text-sm text-slate-500 mt-2"></p>
</div>
<script>
document.getElementById('{{ $uid }}-btn').addEventListener('click', function () {
    const readerEl = document.getElementById('{{ $uid }}-reader');
    const statusEl = document.getElementById('{{ $uid }}-status');
    const btn = this;
    statusEl.classList.remove('hidden');

    function gagal(msg) {
        statusEl.textContent = msg + ' Alternatif: pindai QR pos dengan aplikasi kamera bawaan HP — QR akan membuka halaman pos secara langsung.';
        readerEl.classList.add('hidden');
        btn.disabled = false;
    }

    function mulaiScan() {
        if (typeof Html5Qrcode === 'undefined') {
            gagal('Pustaka pemindai gagal dimuat (periksa koneksi internet).');
            return;
        }
        readerEl.classList.remove('hidden');
        statusEl.textContent = 'Mengaktifkan kamera… arahkan ke QR pos.';
        const scanner = new Html5Qrcode('{{ $uid }}-reader');
        scanner.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 230, height: 230 } },
            (decoded) => {
                let kode = decoded.trim();
                if (kode.includes('/')) {
                    kode = kode.split('?')[0].replace(/\/+$/, '').split('/').pop();
                }
                scanner.stop().catch(() => {});
                const base = '{{ $mode === 'satpam' ? url('/satpam/pos') : url('/pos') }}';
                window.location.href = base + '/' + encodeURIComponent(kode);
            },
            () => {}
        ).catch(() => gagal('Kamera tidak dapat diakses. Pastikan izin kamera diberikan.'));
    }

    btn.disabled = true;
    if (typeof Html5Qrcode === 'undefined') {
        const s = document.createElement('script');
        s.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
        s.onload = mulaiScan;
        s.onerror = () => gagal('Pustaka pemindai gagal dimuat (periksa koneksi internet).');
        document.head.appendChild(s);
    } else {
        mulaiScan();
    }
});
</script>
