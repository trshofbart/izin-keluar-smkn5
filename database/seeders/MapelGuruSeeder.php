<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruMapel;
use App\Models\GuruToken;
use App\Models\Jurusan;
use App\Models\Mapel;
use Illuminate\Database\Seeder;

class MapelGuruSeeder extends Seeder
{
    /** Token contoh per guru (CONTOH SAJA — admin wajib regenerate di produksi). */
    public const CONTOH_TOKEN = [
        'A1B2C3', 'D4E5F6', 'G7H8J9', 'K1L2M3', 'N4P5Q6', 'R7S8T9', 'U1V2W3',
        'X4Y5Z6', 'B2C3D4', 'E5F6G7', 'H8J9K1', 'L2M3N4', 'P5Q6R7', 'S8T9U1',
    ];

    public function run(): void
    {
        $mapelUmum = [
            ['nama' => 'Pendidikan Agama dan Budi Pekerti', 'kode' => 'PABP', 'kelompok' => 'A'],
            ['nama' => 'Pendidikan Pancasila dan Kewarganegaraan', 'kode' => 'PPKN', 'kelompok' => 'A'],
            ['nama' => 'Bahasa Indonesia', 'kode' => 'BIN', 'kelompok' => 'A'],
            ['nama' => 'Matematika', 'kode' => 'MTK', 'kelompok' => 'A'],
            ['nama' => 'Bahasa Inggris', 'kode' => 'BIG', 'kelompok' => 'A'],
            ['nama' => 'Informatika', 'kode' => 'INF', 'kelompok' => 'B'],
            ['nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'kode' => 'PJOK', 'kelompok' => 'B'],
            ['nama' => 'Sejarah', 'kode' => 'SEJ', 'kelompok' => 'B'],
        ];

        $mapelJurusan = [
            ['nama' => 'Pemrograman Dasar', 'kode' => 'PDAS', 'kelompok' => 'C2', 'jurusan' => 'RPL'],
            ['nama' => 'Pemrograman Web dan Perangkat Bergerak', 'kode' => 'PWEB', 'kelompok' => 'C3', 'jurusan' => 'RPL'],
            ['nama' => 'Elektronika Dasar', 'kode' => 'ELDAS', 'kelompok' => 'C2', 'jurusan' => 'TEI'],
            ['nama' => 'Teknik Pemesinan Bubut', 'kode' => 'BUBUT', 'kelompok' => 'C3', 'jurusan' => 'TPM'],
            ['nama' => 'Sistem Kontrol Otomasi', 'kode' => 'OTOM', 'kelompok' => 'C3', 'jurusan' => 'TOI'],
            ['nama' => 'Gambar Teknik Bangunan', 'kode' => 'GAMTEK', 'kelompok' => 'C2', 'jurusan' => 'TKP'],
            ['nama' => 'Gambar Teknik', 'kode' => 'GAMTEK2', 'kelompok' => 'C2', 'jurusan' => 'DPIB'],
            ['nama' => 'Instalasi Listrik', 'kode' => 'INSLIS', 'kelompok' => 'C3', 'jurusan' => 'TITL'],
            ['nama' => 'Teknik Audio Video', 'kode' => 'TAVDAS', 'kelompok' => 'C2', 'jurusan' => 'TAV'],
        ];

        $mapelModels = [];
        foreach ($mapelUmum as $m) {
            $mapelModels[$m['kode']] = Mapel::firstOrCreate(['kode' => $m['kode']], $m);
        }
        foreach ($mapelJurusan as $m) {
            $jurusanId = Jurusan::where('kode', $m['jurusan'])->value('id');
            $mapelModels[$m['kode']] = Mapel::firstOrCreate(
                ['kode' => $m['kode']],
                ['nama' => $m['nama'], 'kode' => $m['kode'], 'kelompok' => $m['kelompok'], 'jurusan_id' => $jurusanId]
            );
        }

        $guru = Guru::orderBy('id')->get()->values();

        // Mapping guru -> mapel (tiap mapel diajar 1-2 guru; tiap guru mengajar 1-2 mapel).
        $mapping = [
            'PABP' => [0, 3], 'PPKN' => [4], 'BIN' => [1, 10], 'MTK' => [2, 5],
            'BIG' => [6, 12], 'INF' => [7, 13], 'PJOK' => [9], 'SEJ' => [8],
            'PDAS' => [7], 'PWEB' => [13], 'ELDAS' => [2], 'BUBUT' => [5],
            'OTOM' => [11], 'GAMTEK' => [10], 'GAMTEK2' => [10], 'INSLIS' => [11], 'TAVDAS' => [9],
        ];

        foreach ($mapping as $kodeMapel => $indeksGuru) {
            foreach ($indeksGuru as $idx) {
                $mapel = $mapelModels[$kodeMapel];
                GuruMapel::firstOrCreate([
                    'guru_id' => $guru[$idx]->id,
                    'mapel_id' => $mapel->id,
                    'kelas_id' => null,
                ], ['jurusan_id' => $mapel->jurusan_id]);
            }
        }

        // Token contoh untuk tiap guru.
        foreach ($guru as $idx => $g) {
            GuruToken::firstOrCreate(
                ['guru_id' => $g->id, 'token' => self::CONTOH_TOKEN[$idx % count(self::CONTOH_TOKEN)]],
                ['aktif' => true]
            );
        }
    }
}
