<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruToken;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PermohonanIzin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinKeluarTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $jurusan = Jurusan::create(['kode' => 'RPL', 'nama' => 'Rekayasa Perangkat Lunak']);
        $this->wali = Guru::create(['nama' => 'Wali Kelas Test', 'nip' => 'NIPWALI001']);
        $this->guruMapel = Guru::create(['nama' => 'Guru Mapel Test', 'nip' => 'NIPGURU001']);
        $this->kelas = Kelas::create([
            'jurusan_id' => $jurusan->id, 'tingkat' => 'XII',
            'nama' => 'XII RPL A', 'wali_kelas_id' => $this->wali->id,
        ]);
        $this->mapel = Mapel::create(['nama' => 'Pemrograman Dasar', 'kode' => 'PDAS', 'kelompok' => 'C2', 'jurusan_id' => $jurusan->id]);

        $this->tokenWali = GuruToken::create(['guru_id' => $this->wali->id, 'token' => 'WALI01', 'aktif' => true]);
        $this->tokenGuru = GuruToken::create(['guru_id' => $this->guruMapel->id, 'token' => 'GURU01', 'aktif' => true]);
    }

    protected function payload(array $overrides = []): array
    {
        return array_merge([
            'nama_siswa' => 'Budi Contoh',
            'nis' => '12345',
            'no_absen' => '7',
            'kelas_id' => $this->kelas->id,
            'keperluan' => 'Berobat ke puskesmas',
            'jam_ke_mulai' => 3,
            'jam_ke_selesai' => 5,
            'kendaraan' => 'Sepeda Motor',
            'cara_pulang' => 'dijemput',
            'nama_penjemput' => 'Bapak Budi',
            'items' => [
                ['mapel_id' => $this->mapel->id, 'guru_id' => $this->guruMapel->id],
            ],
        ], $overrides);
    }

    protected function buatPermohonan(): PermohonanIzin
    {
        $this->post('/izin', $this->payload())->assertRedirect();
        return PermohonanIzin::firstOrFail();
    }

    /** (a) Submit form siswa valid -> permohonan + item mapel-guru + item wali kelas + redirect ke halaman barcode. */
    public function test_submit_form_membuat_permohonan_dan_item(): void
    {
        $response = $this->post('/izin', $this->payload());

        $permohonan = PermohonanIzin::firstOrFail();
        $response->assertRedirect(route('izin.show', $permohonan->kode));

        $this->assertEquals('Budi Contoh', $permohonan->nama_siswa);
        $this->assertEquals('menunggu', $permohonan->status);
        $this->assertMatchesRegularExpression('/^IZN-\d{8}-[A-Z0-9]{4}$/', $permohonan->kode);
        $this->assertEquals(32, strlen($permohonan->barcode_kode));

        // 1 item guru mapel + 1 item wali kelas otomatis
        $this->assertEquals(2, $permohonan->items()->count());
        $this->assertTrue($permohonan->items()->where('peran', 'guru_mapel')->where('guru_id', $this->guruMapel->id)->exists());
        $this->assertTrue($permohonan->items()->where('peran', 'wali_kelas')->where('guru_id', $this->wali->id)->whereNull('mapel_id')->exists());
    }

    public function test_validasi_jam_selesai_harus_lebih_besar(): void
    {
        $this->post('/izin', $this->payload(['jam_ke_mulai' => 5, 'jam_ke_selesai' => 2]))
            ->assertSessionHasErrors('jam_ke_selesai');
        $this->assertEquals(0, PermohonanIzin::count());
    }

    public function test_validasi_nama_penjemput_wajib_bila_dijemput(): void
    {
        $this->post('/izin', $this->payload(['nama_penjemput' => null]))
            ->assertSessionHasErrors('nama_penjemput');
    }

    /** (b) Halaman barcode menampilkan QR/kode. */
    public function test_halaman_barcode_menampilkan_qr_dan_kode(): void
    {
        $permohonan = $this->buatPermohonan();

        $response = $this->get(route('izin.show', $permohonan->kode));
        $response->assertOk();
        $response->assertSee($permohonan->kode);
        $response->assertSee('Budi Contoh');
        $response->assertSee('XII RPL A');
        $response->assertSee(route('guru.show', $permohonan->barcode_kode));
        // QR code SVG hadir
        $response->assertSee('<svg', false);
    }

    /** (c) Guru menyetujui dengan token benar berhasil. */
    public function test_guru_menyetujui_dengan_token_benar(): void
    {
        $permohonan = $this->buatPermohonan();
        $item = $permohonan->items()->where('peran', 'guru_mapel')->firstOrFail();

        $response = $this->post(route('guru.approve', $permohonan->barcode_kode), [
            'permohonan_item_id' => $item->id,
            'token' => 'GURU01',
        ]);

        $response->assertRedirect(route('guru.show', $permohonan->barcode_kode));
        $this->assertEquals('disetujui', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->disetujui_pada);
        $this->assertDatabaseHas('persetujuan', [
            'permohonan_item_id' => $item->id,
            'guru_id' => $this->guruMapel->id,
            'token_digunakan' => 'GURU01',
        ]);
        $this->assertEquals('disetujui_sebagian', $permohonan->fresh()->status);
    }

    /** (d) Token salah ditolak. */
    public function test_token_salah_ditolak(): void
    {
        $permohonan = $this->buatPermohonan();
        $item = $permohonan->items()->where('peran', 'guru_mapel')->firstOrFail();

        $response = $this->from(route('guru.show', $permohonan->barcode_kode))
            ->post(route('guru.approve', $permohonan->barcode_kode), [
                'permohonan_item_id' => $item->id,
                'token' => 'SALAH1',
            ]);

        $response->assertSessionHasErrors('token');
        $this->assertEquals('menunggu', $item->fresh()->status);
        $this->assertEquals(0, $item->persetujuan()->count());
        $this->assertEquals('menunggu', $permohonan->fresh()->status);
    }

    public function test_token_guru_lain_ditolak(): void
    {
        $permohonan = $this->buatPermohonan();
        $item = $permohonan->items()->where('peran', 'guru_mapel')->firstOrFail();

        // Token wali dipakai untuk item guru mapel -> harus ditolak.
        $this->from(route('guru.show', $permohonan->barcode_kode))
            ->post(route('guru.approve', $permohonan->barcode_kode), [
                'permohonan_item_id' => $item->id,
                'token' => 'WALI01',
            ])->assertSessionHasErrors('token');

        $this->assertEquals('menunggu', $item->fresh()->status);
    }

    public function test_guru_tidak_bisa_menyetujui_dua_kali(): void
    {
        $permohonan = $this->buatPermohonan();
        $item = $permohonan->items()->where('peran', 'guru_mapel')->firstOrFail();

        $this->post(route('guru.approve', $permohonan->barcode_kode), [
            'permohonan_item_id' => $item->id, 'token' => 'GURU01',
        ])->assertRedirect();

        $this->from(route('guru.show', $permohonan->barcode_kode))
            ->post(route('guru.approve', $permohonan->barcode_kode), [
                'permohonan_item_id' => $item->id, 'token' => 'GURU01',
            ])->assertSessionHasErrors('token');
    }

    /** (e) Setelah semua item disetujui, halaman /izin/{kode} menampilkan rekap disetujui. */
    public function test_halaman_barcode_berubah_jadi_rekap_setelah_semua_disetujui(): void
    {
        $permohonan = $this->buatPermohonan();

        foreach ($permohonan->items as $item) {
            $token = $item->peran === 'wali_kelas' ? 'WALI01' : 'GURU01';
            $this->post(route('guru.approve', $permohonan->barcode_kode), [
                'permohonan_item_id' => $item->id, 'token' => $token,
            ])->assertRedirect();
        }

        $this->assertEquals('disetujui_semua', $permohonan->fresh()->status);

        $response = $this->get(route('izin.show', $permohonan->kode));
        $response->assertOk();
        $response->assertSee('DIIZINKAN MENINGGALKAN SEKOLAH');
        $response->assertSee('Rekap Izin Keluar');
        $response->assertSee('Disetujui');
        $response->assertSee('Cetak Rekap');
        $response->assertSee('Budi Contoh');
        $response->assertSee('Wali Kelas');
    }

    public function test_halaman_guru_menampilkan_form_dan_status(): void
    {
        $permohonan = $this->buatPermohonan();

        $response = $this->get(route('guru.show', $permohonan->barcode_kode));
        $response->assertOk();
        $response->assertSee('Guru Mapel Test');
        $response->assertSee('Wali Kelas Test');
        $response->assertSee('Token Persetujuan');
        $response->assertSee('Menunggu');
    }

    public function test_admin_login_dan_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertOk();
    }
}
