<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\GuruToken;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PermohonanIzin;
use App\Models\PosKirim;
use App\Models\PosQr;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSatpamTest extends TestCase
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
        GuruToken::create(['guru_id' => $this->wali->id, 'token' => 'WALI01', 'aktif' => true]);
        GuruToken::create(['guru_id' => $this->guruMapel->id, 'token' => 'GURU01', 'aktif' => true]);

        $this->pos = PosQr::create(['nama' => 'Pos Satpam Gerbang Utama']);
    }

    protected function buatPermohonanDisetujui(): PermohonanIzin
    {
        $this->post('/izin', [
            'siswa' => [['nama_siswa' => 'Budi Contoh', 'nis' => '12345', 'no_absen' => '7', 'kelas_id' => $this->kelas->id]],
            'keperluan' => 'Berobat ke puskesmas',
            'jam_ke_mulai' => 3,
            'jam_ke_selesai' => 5,
            'kendaraan' => 'Sepeda Motor',
            'cara_pulang' => 'pulang_sendiri',
            'items' => [['mapel_id' => $this->mapel->id, 'guru_id' => $this->guruMapel->id]],
        ])->assertRedirect();

        $permohonan = PermohonanIzin::firstOrFail();
        foreach ($permohonan->items as $item) {
            $this->post(route('guru.approve', $permohonan->barcode_kode), [
                'permohonan_item_id' => $item->id,
                'token' => $item->peran === 'wali_kelas' ? 'WALI01' : 'GURU01',
            ])->assertRedirect();
        }
        $this->assertEquals('disetujui_semua', $permohonan->fresh()->status);

        return $permohonan;
    }

    public function test_pos_qr_kode_otomatis_dan_halaman_pos_terbuka(): void
    {
        $this->assertEquals(24, strlen($this->pos->kode));
        $this->assertTrue($this->pos->aktif);

        $this->get(route('pos.show', $this->pos->kode))
            ->assertOk()
            ->assertSee('Pos Satpam Gerbang Utama')
            ->assertSee('Kirim Rekap ke Pos Ini');
    }

    public function test_siswa_kirim_rekap_setelah_disetujui_semua(): void
    {
        $permohonan = $this->buatPermohonanDisetujui();

        $response = $this->post(route('pos.kirim', $this->pos->kode), [
            'izin_kode' => $permohonan->kode,
        ]);

        $response->assertRedirect(route('pos.show', $this->pos->kode));
        $response->assertSessionHas('kirim_id');
        $this->assertEquals(1, PosKirim::count());
        $this->assertDatabaseHas('pos_kirim', [
            'pos_qr_id' => $this->pos->id,
            'permohonan_id' => $permohonan->id,
        ]);

        // Kirim ulang tidak menggandakan catatan.
        $this->post(route('pos.kirim', $this->pos->kode), ['izin_kode' => $permohonan->kode])
            ->assertRedirect(route('pos.show', $this->pos->kode));
        $this->assertEquals(1, PosKirim::count());
    }

    public function test_kirim_rekap_ditolak_bila_belum_disetujui_semua(): void
    {
        $this->post('/izin', [
            'siswa' => [['nama_siswa' => 'Budi Contoh', 'nis' => null, 'no_absen' => '7', 'kelas_id' => $this->kelas->id]],
            'keperluan' => 'Berobat',
            'jam_ke_mulai' => 3,
            'jam_ke_selesai' => 5,
            'kendaraan' => 'Sepeda Motor',
            'cara_pulang' => 'pulang_sendiri',
            'items' => [['mapel_id' => $this->mapel->id, 'guru_id' => $this->guruMapel->id]],
        ])->assertRedirect();
        $permohonan = PermohonanIzin::firstOrFail();

        $this->from(route('pos.show', $this->pos->kode))
            ->post(route('pos.kirim', $this->pos->kode), ['izin_kode' => $permohonan->kode])
            ->assertSessionHasErrors('izin_kode');
        $this->assertEquals(0, PosKirim::count());
    }

    public function test_kirim_rekap_kode_izin_salah_ditolak(): void
    {
        $this->from(route('pos.show', $this->pos->kode))
            ->post(route('pos.kirim', $this->pos->kode), ['izin_kode' => 'IZN-20000101-XXXX'])
            ->assertSessionHasErrors('izin_kode');
        $this->assertEquals(0, PosKirim::count());
    }

    public function test_halaman_satpam_harus_login(): void
    {
        $this->get(route('satpam.index'))->assertRedirect(route('satpam.login'));
        $this->get(route('satpam.pos', $this->pos->kode))->assertRedirect(route('satpam.login'));
        $this->get(route('pos.cetak', $this->pos->kode))->assertRedirect(route('satpam.login'));
    }

    public function test_satpam_login_dan_cek_pos_berisi_rekap(): void
    {
        $permohonan = $this->buatPermohonanDisetujui();
        $this->post(route('pos.kirim', $this->pos->kode), ['izin_kode' => $permohonan->kode]);

        User::create([
            'name' => 'Satpam Test', 'email' => 'satpam@test.id',
            'password' => 'satpam12345', 'role' => 'satpam',
        ]);

        $this->post(route('satpam.login.store'), [
            'email' => 'satpam@test.id', 'password' => 'satpam12345',
        ])->assertRedirect(route('satpam.index'));

        $this->get(route('satpam.index'))
            ->assertOk()
            ->assertSee('Pos Satpam Gerbang Utama')
            ->assertSee('Budi Contoh');

        // QR pos ini SUDAH berisi rekap siswa tersebut.
        $this->get(route('satpam.pos', $this->pos->kode))
            ->assertOk()
            ->assertSee('Budi Contoh')
            ->assertSee($permohonan->kode)
            ->assertSee('Hari ini: 1');
    }

    public function test_satpam_cek_pos_kosong_ditandai_belum_berisi(): void
    {
        $satpam = User::create([
            'name' => 'Satpam Test', 'email' => 'satpam@test.id',
            'password' => 'satpam12345', 'role' => 'satpam',
        ]);

        $this->actingAs($satpam)->get(route('satpam.pos', $this->pos->kode))
            ->assertOk()
            ->assertSee('belum berisi');
    }

    public function test_satpam_tidak_bisa_masuk_panel_admin(): void
    {
        $satpam = User::create([
            'name' => 'Satpam Test', 'email' => 'satpam@test.id',
            'password' => 'satpam12345', 'role' => 'satpam',
        ]);

        $this->actingAs($satpam)->get('/admin')->assertForbidden();
    }

    public function test_admin_bisa_akses_halaman_satpam_dan_cetak_qr(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('satpam.index'))->assertOk();
        $this->actingAs($admin)->get(route('pos.cetak', $this->pos->kode))
            ->assertOk()
            ->assertSee('Pos Satpam Gerbang Utama')
            ->assertSee('<svg', false);
    }

    public function test_pos_tidak_ditemukan_404(): void
    {
        $this->get('/pos/kodeyangtidakada')->assertNotFound();
    }
}
