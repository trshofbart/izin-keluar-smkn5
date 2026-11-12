<?php

namespace App\Support;

use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use RuntimeException;
use ZipArchive;

/**
 * Import data kelas dari file CSV / XLSX (menu Kelas di admin).
 *
 * Kolom yang dikenali (baris pertama = header, tidak peka huruf besar/kecil):
 * - Jurusan      : kode jurusan (mis. RPL) atau nama jurusan persis.
 * - Tingkat      : X / XI / XII / XIII (angka 10–13 juga diterima).
 * - Nama Kelas   : mis. "XII RPL A" (boleh juga header "Nama" / "Kelas").
 * - Wali Kelas   : nama guru persis (opsional, boleh kosong).
 *
 * Baris yang sudah ada (jurusan + tingkat + nama sama) diperbarui wali
 * kelasnya; baris baru dibuat. Baris yang salah dilewati dan dilaporkan.
 */
class KelasImporter
{
    /**
     * @return array{dibuat:int, diperbarui:int, dilewati:int, pesan:list<string>}
     */
    public static function import(string $path): array
    {
        $rows = static::bacaBaris($path);
        if ($rows === []) {
            throw new RuntimeException('File kosong atau tidak terbaca.');
        }

        $header = static::petakanHeader($rows[0]);
        foreach (['jurusan', 'tingkat', 'nama'] as $wajib) {
            if (! isset($header[$wajib])) {
                throw new RuntimeException('Kolom "'.$wajib.'" tidak ditemukan di baris header. Pakai template yang disediakan.');
            }
        }

        $hasil = ['dibuat' => 0, 'diperbarui' => 0, 'dilewati' => 0, 'pesan' => []];

        foreach (array_slice($rows, 1) as $i => $row) {
            $noBaris = $i + 2;
            $ambil = fn (string $kolom) => isset($header[$kolom]) ? trim((string) ($row[$header[$kolom]] ?? '')) : '';

            $jurusanIsi = $ambil('jurusan');
            $tingkatIsi = $ambil('tingkat');
            $nama = $ambil('nama');
            $waliIsi = $ambil('wali');

            if ($jurusanIsi === '' && $tingkatIsi === '' && $nama === '' && $waliIsi === '') {
                continue; // baris kosong, abaikan diam-diam
            }

            if ($nama === '') {
                $hasil['dilewati']++;
                $hasil['pesan'][] = "Baris {$noBaris} dilewati: nama kelas kosong.";
                continue;
            }

            $jurusan = Jurusan::whereRaw('LOWER(kode) = ?', [mb_strtolower($jurusanIsi)])
                ->orWhereRaw('LOWER(nama) = ?', [mb_strtolower($jurusanIsi)])
                ->first();
            if (! $jurusan) {
                $hasil['dilewati']++;
                $hasil['pesan'][] = "Baris {$noBaris} dilewati: jurusan \"{$jurusanIsi}\" tidak ditemukan.";
                continue;
            }

            $tingkat = match (strtoupper($tingkatIsi)) {
                'X', '10' => 'X',
                'XI', '11' => 'XI',
                'XII', '12' => 'XII',
                'XIII', '13' => 'XIII',
                default => null,
            };
            if (! $tingkat) {
                $hasil['dilewati']++;
                $hasil['pesan'][] = "Baris {$noBaris} dilewati: tingkat \"{$tingkatIsi}\" tidak dikenal (pakai X/XI/XII/XIII).";
                continue;
            }

            $waliId = null;
            $adaKolomWali = isset($header['wali']);
            if ($waliIsi !== '') {
                $wali = Guru::whereRaw('LOWER(nama) = ?', [mb_strtolower($waliIsi)])->first();
                if ($wali) {
                    $waliId = $wali->id;
                } else {
                    $hasil['pesan'][] = "Baris {$noBaris}: wali kelas \"{$waliIsi}\" tidak ditemukan — kelas disimpan tanpa wali kelas.";
                }
            }

            $kelas = Kelas::where('jurusan_id', $jurusan->id)
                ->where('tingkat', $tingkat)
                ->where('nama', $nama)
                ->first();

            if ($kelas) {
                // Sel kosong pada kolom wali = biarkan wali lama apa adanya.
                if ($adaKolomWali && $waliIsi !== '') {
                    $kelas->update(['wali_kelas_id' => $waliId]);
                }
                $hasil['diperbarui']++;
            } else {
                Kelas::create([
                    'jurusan_id' => $jurusan->id,
                    'tingkat' => $tingkat,
                    'nama' => $nama,
                    'wali_kelas_id' => $waliId,
                ]);
                $hasil['dibuat']++;
            }
        }

        return $hasil;
    }

    /** Petakan nama kolom header ke indeks kolom. */
    protected static function petakanHeader(array $headerRow): array
    {
        $peta = [];
        foreach ($headerRow as $idx => $namaKolom) {
            $kunci = preg_replace('/[^a-z0-9]+/', '_', mb_strtolower(trim((string) $namaKolom)));
            $kunci = trim($kunci, '_');
            match (true) {
                in_array($kunci, ['jurusan', 'kode_jurusan'], true) => $peta['jurusan'] = $idx,
                $kunci === 'tingkat' => $peta['tingkat'] = $idx,
                in_array($kunci, ['nama_kelas', 'nama', 'kelas'], true) => $peta['nama'] = $idx,
                in_array($kunci, ['wali_kelas', 'wali'], true) => $peta['wali'] = $idx,
                default => null,
            };
        }

        return $peta;
    }

    /** Baca semua baris file menjadi array of array string, sesuai ekstensi. */
    protected static function bacaBaris(string $path): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $ext === 'xlsx' ? static::bacaXlsx($path) : static::bacaCsv($path);
    }

    protected static function bacaCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException('File tidak bisa dibuka.');
        }

        // Deteksi pemisah dari baris pertama (Excel Indonesia memakai ';').
        $barisPertama = fgets($handle) ?: '';
        $pemisah = substr_count($barisPertama, ';') > substr_count($barisPertama, ',') ? ';' : ',';
        rewind($handle);

        $rows = [];
        while (($data = fgetcsv($handle, 0, $pemisah)) !== false) {
            $rows[] = array_map(fn ($v) => preg_replace('/^\xEF\xBB\xBF/', '', trim((string) $v)), $data);
        }
        fclose($handle);

        return $rows;
    }

    protected static function bacaXlsx(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File XLSX tidak bisa dibuka.');
        }

        $shared = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $ss = simplexml_load_string($ssXml);
            foreach ($ss->si as $si) {
                $teks = '';
                foreach ($si->xpath('.//*[local-name()="t"]') ?: [] as $t) {
                    $teks .= (string) $t;
                }
                $shared[] = $teks;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $nama = $zip->getNameIndex($i);
                if (str_starts_with($nama, 'xl/worksheets/') && str_ends_with($nama, '.xml')) {
                    $sheetXml = $zip->getFromName($nama);
                    break;
                }
            }
        }
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('Sheet tidak ditemukan di file XLSX.');
        }

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) $c['r'];
                $kolom = 0;
                foreach (str_split(preg_replace('/[^A-Z]/', '', strtoupper($ref))) as $huruf) {
                    $kolom = $kolom * 26 + (ord($huruf) - 64);
                }
                $tipe = (string) $c['t'];
                $nilai = match ($tipe) {
                    's' => $shared[(int) $c->v] ?? '',
                    'inlineStr' => (string) ($c->is->t ?? ''),
                    default => isset($c->v) ? (string) $c->v : '',
                };
                $cells[$kolom - 1] = trim($nilai);
            }
            if ($cells !== []) {
                // Rapatkan: sel kosong di tengah tidak ditulis Excel ke XML,
                // jadi isi celahnya dengan '' agar kolom tidak bergeser.
                $maks = max(array_keys($cells));
                $padat = [];
                for ($k = 0; $k <= $maks; $k++) {
                    $padat[] = $cells[$k] ?? '';
                }
                $rows[] = $padat;
            }
        }

        return $rows;
    }

    /** Isi file template XLSX (biner) untuk diunduh admin. */
    public static function templateXlsx(): string
    {
        $baris = [
            ['Jurusan', 'Tingkat', 'Nama Kelas', 'Wali Kelas'],
            ['RPL', 'X', 'X RPL A', 'Budi Santoso, S.Pd.'],
            ['RPL', 'XI', 'XI RPL A', 'Siti Rahmawati, S.Pd.'],
            ['TEI', 'XII', 'XII TEI A', ''],
        ];

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($baris as $r => $kolom) {
            $sheet .= '<row r="'.($r + 1).'">';
            foreach ($kolom as $c => $nilai) {
                $ref = chr(65 + $c).($r + 1);
                $sheet .= '<c r="'.$ref.'" t="inlineStr"><is><t>'.htmlspecialchars($nilai, ENT_XML1).'</t></is></c>';
            }
            $sheet .= '</row>';
        }
        $sheet .= '</sheetData></worksheet>';

        $berkas = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                .'<Default Extension="xml" ContentType="application/xml"/>'
                .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                .'</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                .'</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                .'<sheets><sheet name="Kelas" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                .'</Relationships>',
            'xl/worksheets/sheet1.xml' => $sheet,
        ];

        $tmp = tempnam(sys_get_temp_dir(), 'tplkelas');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);
        foreach ($berkas as $nama => $isi) {
            $zip->addFromString($nama, $isi);
        }
        $zip->close();

        $biner = (string) file_get_contents($tmp);
        unlink($tmp);

        return $biner;
    }
}
