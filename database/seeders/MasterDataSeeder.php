<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $jurusan = [
            ['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak'],
            ['kode' => 'TEI', 'nama' => 'Teknik Elektronika Industri'],
            ['kode' => 'TPM', 'nama' => 'Teknik Pemesinan'],
            ['kode' => 'TOI', 'nama' => 'Teknik Otomasi Industri'],
            ['kode' => 'TKP', 'nama' => 'Teknik Konstruksi dan Perumahan'],
            ['kode' => 'DPIB', 'nama' => 'Desain Pemodelan dan Informasi Bangunan'],
            ['kode' => 'TITL', 'nama' => 'Teknik Instalasi Tenaga Listrik'],
            ['kode' => 'TAV', 'nama' => 'Teknik Audio Video'],
        ];

        foreach ($jurusan as $j) {
            Jurusan::firstOrCreate(['kode' => $j['kode']], $j);
        }

        // 14 guru contoh (nama fiktif)
        $guru = [
            ['nama' => 'Budi Santoso, S.Pd.', 'nip' => '198001012005011001', 'no_hp' => '081234567801'],
            ['nama' => 'Siti Rahmawati, S.Pd.', 'nip' => '198203152008012002', 'no_hp' => '081234567802'],
            ['nama' => 'Agus Prasetyo, S.T.', 'nip' => '197905202004011003', 'no_hp' => '081234567803'],
            ['nama' => 'Dewi Lestari, M.Pd.', 'nip' => '198507102010012004', 'no_hp' => '081234567804'],
            ['nama' => 'Rina Wijaya, S.Pd.', 'nip' => '198811202012012005', 'no_hp' => '081234567805'],
            ['nama' => 'Joko Susilo, S.T.', 'nip' => '198302182007011006', 'no_hp' => '081234567806'],
            ['nama' => 'Maya Kusuma, S.Pd.', 'nip' => '199001252015012007', 'no_hp' => '081234567807'],
            ['nama' => 'Andi Firmansyah, S.Kom.', 'nip' => '198906102014011008', 'no_hp' => '081234567808'],
            ['nama' => 'Lilis Suryani, S.Pd.', 'nip' => '198408302009012009', 'no_hp' => '081234567809'],
            ['nama' => 'Hendra Gunawan, S.T.', 'nip' => '197812122003011010', 'no_hp' => '081234567810'],
            ['nama' => 'Nina Marlina, S.Pd.', 'nip' => '199203182017012011', 'no_hp' => '081234567811'],
            ['nama' => 'Fajar Nugroho, S.T.', 'nip' => '198710052011011012', 'no_hp' => '081234567812'],
            ['nama' => 'Ratna Dewi, S.Pd.', 'nip' => '198606142010012013', 'no_hp' => '081234567813'],
            ['nama' => 'Eko Purnomo, S.Kom.', 'nip' => '199105202016011014', 'no_hp' => '081234567814'],
        ];

        $guruModels = [];
        foreach ($guru as $g) {
            $guruModels[] = Guru::firstOrCreate(['nip' => $g['nip']], $g);
        }

        // Kelas: X/XI/XII A & B per jurusan, + XIII A & B untuk TOI.
        // 1 wali kelas per kelas (round-robin dari daftar guru).
        $i = 0;
        foreach (Jurusan::all() as $j) {
            $tingkats = ['X', 'XI', 'XII'];
            if ($j->kode === 'TOI') {
                $tingkats[] = 'XIII';
            }
            foreach ($tingkats as $tingkat) {
                foreach (['A', 'B'] as $rombel) {
                    $wali = $guruModels[$i % count($guruModels)];
                    $i++;
                    Kelas::firstOrCreate(
                        ['jurusan_id' => $j->id, 'tingkat' => $tingkat, 'nama' => "$tingkat {$j->kode} $rombel"],
                        ['wali_kelas_id' => $wali->id]
                    );
                }
            }
        }
    }
}
