<?php

namespace Tests\Feature;

use App\Filament\Resources\GuruTokenResource\Pages\ListGuruTokens;
use App\Models\Guru;
use App\Models\GuruToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TokenMassalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->guruA = Guru::create(['nama' => 'Guru A', 'nip' => 'NIPA001']);
        $this->guruB = Guru::create(['nama' => 'Guru B', 'nip' => 'NIPB001']);
        $this->guruC = Guru::create(['nama' => 'Guru C', 'nip' => 'NIPC001']);

        $this->tokenA = GuruToken::create(['guru_id' => $this->guruA->id, 'token' => 'AAAA11', 'aktif' => true]);
        $this->tokenB = GuruToken::create(['guru_id' => $this->guruB->id, 'token' => 'BBBB22', 'aktif' => true]);
        // Guru C sengaja belum punya token.
    }

    /** Generate Token Semua Guru: hanya guru yang belum punya token yang dibuatkan. */
    public function test_generate_token_semua_guru_hanya_untuk_yang_belum_punya(): void
    {
        Livewire::test(ListGuruTokens::class)
            ->callTableAction('generateSemua')
            ->assertHasNoTableActionErrors();

        $this->assertEquals(3, GuruToken::count());

        $tokenC = GuruToken::where('guru_id', $this->guruC->id)->firstOrFail();
        $this->assertEquals(6, strlen($tokenC->token));
        $this->assertTrue($tokenC->aktif);

        // Token guru yang sudah ada TIDAK berubah.
        $this->assertEquals('AAAA11', $this->tokenA->fresh()->token);
        $this->assertEquals('BBBB22', $this->tokenB->fresh()->token);

        // Jalan dua kali tidak menggandakan.
        Livewire::test(ListGuruTokens::class)->callTableAction('generateSemua');
        $this->assertEquals(3, GuruToken::count());
    }

    /** Ubah Token Serentak: semua token yang ada diganti baru sekaligus. */
    public function test_ubah_token_serentak_mengganti_semua_token(): void
    {
        Livewire::test(ListGuruTokens::class)
            ->callTableAction('ubahSerentak')
            ->assertHasNoTableActionErrors();

        $baruA = $this->tokenA->fresh()->token;
        $baruB = $this->tokenB->fresh()->token;
        $this->assertNotEquals('AAAA11', $baruA);
        $this->assertNotEquals('BBBB22', $baruB);
        $this->assertEquals(6, strlen($baruA));
        $this->assertEquals(6, strlen($baruB));

        // Jumlah token tidak berubah & guru tanpa token tetap tanpa token.
        $this->assertEquals(2, GuruToken::count());
        $this->assertEquals(0, GuruToken::where('guru_id', $this->guruC->id)->count());
    }

    /** Fitur lama dipertahankan: Generate Ulang per guru tetap bekerja. */
    public function test_generate_ulang_token_satu_guru_tetap_bekerja(): void
    {
        Livewire::test(ListGuruTokens::class)
            ->assertTableActionExists('regenerate')
            ->callTableAction('regenerate', $this->tokenA)
            ->assertHasNoTableActionErrors();

        $baru = $this->tokenA->fresh()->token;
        $this->assertNotEquals('AAAA11', $baru);
        $this->assertEquals(6, strlen($baru));
        // Token guru lain tidak ikut berubah.
        $this->assertEquals('BBBB22', $this->tokenB->fresh()->token);
    }
}
