<?php

namespace Tests\Feature;

use App\Filament\Resources\KelasResource\Pages\ListKelas;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\User;
use App\Support\KelasImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KelasImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jurusanRpl = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $this->jurusanTei = Jurusan::create(['kode' => 'TEI', 'nama' => 'Teknik Elektronika Industri']);
        $this->budi = Guru::create(['nama' => 'Budi Santoso, S.Pd.', 'nip' => 'NIP001']);
        $this->siti = Guru::create(['nama' => 'Siti Rahmawati, S.Pd.', 'nip' => 'NIP002']);
    }

    protected function tulisFile(string $isi, string $ext): string
    {
        $path = sys_get_temp_dir().'/import-kelas-'.uniqid().'.'.$ext;
        file_put_contents($path, $isi);

        return $path;
    }

    public function test_import_csv_membuat_kelas_baru(): void
    {
        $csv = "Jurusan,Tingkat,Nama Kelas,Wali Kelas\n"
            ."RPL,X,X RPL A,\"Budi Santoso, S.Pd.\"\n"
            ."TEI,12,XII TEI A,\n";
        $path = $this->tulisFile($csv, 'csv');

        $hasil = KelasImporter::import($path);

        $this->assertEquals(2, $hasil['dibuat']);
        $this->assertEquals(0, $hasil['dilewati']);
        $this->assertEquals([], $hasil['pesan']);

        $kelasRpl = Kelas::where('nama', 'X RPL A')->firstOrFail();
        $this->assertEquals($this->jurusanRpl->id, $kelasRpl->jurusan_id);
        $this->assertEquals('X', $kelasRpl->tingkat);
        $this->assertEquals($this->budi->id, $kelasRpl->wali_kelas_id);

        // Tingkat angka 12 diterima sebagai XII; wali kosong = tanpa wali.
        $kelasTei = Kelas::where('nama', 'XII TEI A')->firstOrFail();
        $this->assertEquals('XII', $kelasTei->tingkat);
        $this->assertNull($kelasTei->wali_kelas_id);
    }

    public function test_import_csv_pemisah_titik_koma(): void
    {
        $csv = "Jurusan;Tingkat;Nama Kelas;Wali Kelas\n"
            ."RPL;XI;XI RPL B;Siti Rahmawati, S.Pd.\n";
        $path = $this->tulisFile($csv, 'csv');

        $hasil = KelasImporter::import($path);

        $this->assertEquals(1, $hasil['dibuat']);
        $kelas = Kelas::where('nama', 'XI RPL B')->firstOrFail();
        $this->assertEquals($this->siti->id, $kelas->wali_kelas_id);
    }

    public function test_import_baris_salah_dilewati_dan_dilaporkan(): void
    {
        $csv = "Jurusan,Tingkat,Nama Kelas,Wali Kelas\n"
            ."XXX,X,X XXX A,\n"
            ."RPL,IX,IX RPL A,\n"
            ."RPL,X,X RPL A,\n";
        $path = $this->tulisFile($csv, 'csv');

        $hasil = KelasImporter::import($path);

        $this->assertEquals(1, $hasil['dibuat']);
        $this->assertEquals(2, $hasil['dilewati']);
        $this->assertStringContainsString('jurusan "XXX" tidak ditemukan', $hasil['pesan'][0]);
        $this->assertStringContainsString('tingkat "IX" tidak dikenal', $hasil['pesan'][1]);
        $this->assertEquals(1, Kelas::count());
    }

    public function test_import_ulang_memperbarui_wali_kelas(): void
    {
        $csv1 = "Jurusan,Tingkat,Nama Kelas,Wali Kelas\n"
            ."RPL,X,X RPL A,\"Budi Santoso, S.Pd.\"\n";
        KelasImporter::import($this->tulisFile($csv1, 'csv'));

        $csv2 = "Jurusan,Tingkat,Nama Kelas,Wali Kelas\n"
            ."RPL,X,X RPL A,\"Siti Rahmawati, S.Pd.\"\n";
        $hasil = KelasImporter::import($this->tulisFile($csv2, 'csv'));

        $this->assertEquals(0, $hasil['dibuat']);
        $this->assertEquals(1, $hasil['diperbarui']);
        $this->assertEquals(1, Kelas::count());
        $this->assertEquals($this->siti->id, Kelas::firstOrFail()->wali_kelas_id);

        // Import lagi dengan wali kosong: wali lama TIDAK terhapus.
        $csv3 = "Jurusan,Tingkat,Nama Kelas,Wali Kelas\n"
            ."RPL,X,X RPL A,\n";
        KelasImporter::import($this->tulisFile($csv3, 'csv'));
        $this->assertEquals($this->siti->id, Kelas::firstOrFail()->wali_kelas_id);
    }

    public function test_import_wali_tidak_ditemukan_kelas_tetap_disimpan(): void
    {
        $csv = "Jurusan,Tingkat,Nama Kelas,Wali Kelas\n"
            ."RPL,X,X RPL A,Orang Asing\n";
        $hasil = KelasImporter::import($this->tulisFile($csv, 'csv'));

        $this->assertEquals(1, $hasil['dibuat']);
        $this->assertNull(Kelas::firstOrFail()->wali_kelas_id);
        $this->assertStringContainsString('wali kelas "Orang Asing" tidak ditemukan', $hasil['pesan'][0]);
    }

    public function test_import_xlsx_hasil_template_bisa_dibaca(): void
    {
        $path = $this->tulisFile(KelasImporter::templateXlsx(), 'xlsx');

        $hasil = KelasImporter::import($path);

        $this->assertEquals(3, $hasil['dibuat']);
        $this->assertEquals(0, $hasil['dilewati']);
        $this->assertEquals($this->budi->id, Kelas::where('nama', 'X RPL A')->firstOrFail()->wali_kelas_id);
        $this->assertEquals($this->siti->id, Kelas::where('nama', 'XI RPL A')->firstOrFail()->wali_kelas_id);
        $this->assertNull(Kelas::where('nama', 'XII TEI A')->firstOrFail()->wali_kelas_id);
    }

    public function test_template_xlsx_bisa_diunduh_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('kelas.template'));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('PK', $response->getContent());
    }

    public function test_panel_kelas_memiliki_aksi_import(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Livewire::test(ListKelas::class)->assertTableActionExists('import');
    }
}
