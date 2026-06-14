<?php

namespace Tests\Services;

use App\Services\BracketAdvancementService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Unit tests for BracketAdvancementService.
 * Tests bracket advancement logic (medali + bagan update) for tanding and seni.
 *
 * @group bracket
 */
final class BracketAdvancementServiceTest extends CIUnitTestCase
{
    private BracketAdvancementService $service;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();

        // Tests butuh MySQL/MariaDB real data — skip jika cuma SQLite (CI env)
        try {
            $this->conn = Database::connect('sudinpora');
            $this->conn->query('SELECT 1');
            // Inject sudinpora connection ke service supaya read-back match write group
            $this->service = new BracketAdvancementService($this->conn);
        } catch (\Throwable $e) {
            $this->conn = null;
            $this->service = new BracketAdvancementService();
        }
    }

    private function requireRealDb(): void
    {
        if ($this->conn === null) {
            $this->markTestSkipped('Requires sudinpora MySQL connection (not available in SQLite test env).');
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  TANDING — medali logic
    // ═══════════════════════════════════════════════════════════════════════════

    public function testAdvanceTandingFinalWritesMedaliEmasPerak(): void
    {
        $this->requireRealDb();

        // Find a kompetisi_tanding with perhitungan_medali = 1
        $kompetisi = $this->conn->table('kompetisi_tanding')
            ->where('perhitungan_medali', 1)
            ->get()->getRow();

        if ($kompetisi === null) {
            $this->markTestSkipped('No kompetisi_tanding with perhitungan_medali=1 in test DB.');
        }

        // Find a Final match in this kompetisi
        $partai = $this->conn->table('pertandingan')
            ->where('id_kompetisi_tanding', $kompetisi->id_kompetisi_tanding)
            ->where('babak', 'Final')
            ->where('id_atlet_merah IS NOT NULL')
            ->where('id_atlet_biru IS NOT NULL')
            ->get()->getRow();

        if ($partai === null) {
            $this->markTestSkipped('No Final match with both athletes in kompetisi.');
        }

        $idPemenang = (int) $partai->id_atlet_merah;
        $idKalah = (int) $partai->id_atlet_biru;

        // Clean up any existing medals
        $this->conn->table('perolehan_medali_tanding')
            ->whereIn('id_peserta_tanding', [$idPemenang, $idKalah])
            ->delete();

        // Run advancement
        $this->service->advanceTanding($partai, $idPemenang, 'Poin');

        // Assert emas for winner
        $medalEmas = $this->conn->table('perolehan_medali_tanding')
            ->where('id_peserta_tanding', $idPemenang)
            ->where('jenis_medali', 'emas')
            ->countAllResults();
        $this->assertEquals(1, $medalEmas, 'Pemenang final harus dapat emas');

        // Assert perak for loser
        $medalPerak = $this->conn->table('perolehan_medali_tanding')
            ->where('id_peserta_tanding', $idKalah)
            ->where('jenis_medali', 'perak')
            ->countAllResults();
        $this->assertEquals(1, $medalPerak, 'Kalah final harus dapat perak');

        // Cleanup
        $this->conn->table('perolehan_medali_tanding')
            ->whereIn('id_peserta_tanding', [$idPemenang, $idKalah])
            ->delete();
    }

    public function testAdvanceTandingSemiFinalWritesBronze(): void
    {
        $this->requireRealDb();

        // Cari Semi Final di kompetisi MANA SAJA yang perhitungan_medali=1
        $partai = $this->conn->table('pertandingan p')
            ->select('p.*')
            ->join('kompetisi_tanding k', 'k.id_kompetisi_tanding = p.id_kompetisi_tanding')
            ->where('k.perhitungan_medali', 1)
            ->where('p.babak', 'Semi Final')
            ->where('p.id_atlet_merah IS NOT NULL')
            ->where('p.id_atlet_biru IS NOT NULL')
            ->limit(1)
            ->get()->getRow();

        if ($partai === null) {
            $this->markTestSkipped('No Semi Final match with both athletes.');
        }

        $idPemenang = (int) $partai->id_atlet_biru;
        $idKalah = (int) $partai->id_atlet_merah;

        // Clean
        $this->conn->table('perolehan_medali_tanding')
            ->where('id_peserta_tanding', $idKalah)
            ->delete();

        $this->service->advanceTanding($partai, $idPemenang, 'Poin');

        // Default juara_tiga_bersama = 1 → loser gets perunggu immediately
        $medalBronze = $this->conn->table('perolehan_medali_tanding')
            ->where('id_peserta_tanding', $idKalah)
            ->where('jenis_medali', 'perunggu')
            ->countAllResults();
        $this->assertEquals(1, $medalBronze, 'Kalah semi final harus dapat perunggu (juara 3 bersama)');

        // Cleanup
        $this->conn->table('perolehan_medali_tanding')
            ->where('id_peserta_tanding', $idKalah)
            ->delete();
    }

    public function testAdvanceTandingPlacesWinnerInNextMatch(): void
    {
        $this->requireRealDb();

        // Cari non-Final match LANGSUNG dari seluruh kompetisi yang perhitungan_medali=1
        $partai = $this->conn->table('pertandingan p')
            ->select('p.*')
            ->join('kompetisi_tanding k', 'k.id_kompetisi_tanding = p.id_kompetisi_tanding')
            ->where('k.perhitungan_medali', 1)
            ->where('p.babak !=', 'Final')
            ->where('p.nomor_pertandingan_selanjutnya IS NOT NULL')
            ->where('p.id_atlet_merah IS NOT NULL')
            ->where('p.id_atlet_biru IS NOT NULL')
            ->limit(1)
            ->get()->getRow();

        if ($partai === null) {
            $this->markTestSkipped('No non-Final match with next pointer in DB.');
        }

        $idPemenang = (int) $partai->id_atlet_merah;
        $nomorSelanjutnya = (int) $partai->nomor_pertandingan_selanjutnya;
        $nomorPertandingan = (int) $partai->nomor_pertandingan;

        // Determine expected slot
        $expectedSlot = ($nomorPertandingan % 2 === 1) ? 'id_atlet_biru' : 'id_atlet_merah';

        // Save original value in next match
        $nextMatch = $this->conn->table('pertandingan')
            ->where('id_kompetisi_tanding', $partai->id_kompetisi_tanding)
            ->where('nomor_pertandingan', $nomorSelanjutnya)
            ->get()->getRow();

        if ($nextMatch === null) {
            $this->markTestSkipped('Next match row not found.');
        }

        $originalValue = $nextMatch->{$expectedSlot};

        // Run
        $this->service->advanceTanding($partai, $idPemenang, 'Poin');

        // Check
        $updatedNext = $this->conn->table('pertandingan')
            ->where('id_pertandingan', $nextMatch->id_pertandingan)
            ->get()->getRow();

        $this->assertEquals($idPemenang, (int) $updatedNext->{$expectedSlot},
            "Pemenang harus ditempatkan di slot $expectedSlot pertandingan selanjutnya");

        // Restore original
        $this->conn->table('pertandingan')
            ->where('id_pertandingan', $nextMatch->id_pertandingan)
            ->update([$expectedSlot => $originalValue]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  SENI BATTLE — medali logic
    // ═══════════════════════════════════════════════════════════════════════════

    public function testAdvanceBattleSeniFinalWritesMedali(): void
    {
        $this->requireRealDb();

        // Cari Final battle LANGSUNG dari seluruh kompetisi seni yang perhitungan_medali=1
        $battle = $this->conn->table('battle_seni b')
            ->select('b.*')
            ->join('kompetisi_seni k', 'k.id_kompetisi_seni = b.id_kompetisi_seni')
            ->where('k.perhitungan_medali', 1)
            ->where('b.babak', 'Final')
            ->where('b.id_penampilan_seni_biru IS NOT NULL')
            ->where('b.id_penampilan_seni_merah IS NOT NULL')
            ->limit(1)
            ->get()->getRow();

        if ($battle === null) {
            $this->markTestSkipped('No Final battle with both penampilan.');
        }

        $idPemenang = (int) $battle->id_penampilan_seni_biru;
        $idKalah = (int) $battle->id_penampilan_seni_merah;

        // Get kelompok IDs
        $kelPemenang = $this->conn->table('penampilan_seni')
            ->select('id_kelompok_peserta_seni')
            ->where('id_penampilan_seni', $idPemenang)
            ->get()->getRow();
        $kelKalah = $this->conn->table('penampilan_seni')
            ->select('id_kelompok_peserta_seni')
            ->where('id_penampilan_seni', $idKalah)
            ->get()->getRow();

        if ($kelPemenang === null || $kelKalah === null) {
            $this->markTestSkipped('Could not resolve kelompok_peserta_seni.');
        }

        // Clean
        $this->conn->table('perolehan_medali_seni')
            ->whereIn('id_kelompok_peserta_seni', [
                (int) $kelPemenang->id_kelompok_peserta_seni,
                (int) $kelKalah->id_kelompok_peserta_seni,
            ])
            ->delete();

        $this->service->advanceBattleSeni($battle, $idPemenang);

        // Emas
        $emas = $this->conn->table('perolehan_medali_seni')
            ->where('id_kelompok_peserta_seni', (int) $kelPemenang->id_kelompok_peserta_seni)
            ->where('jenis_medali', 'emas')
            ->countAllResults();
        $this->assertEquals(1, $emas, 'Pemenang final seni harus dapat emas');

        // Perak
        $perak = $this->conn->table('perolehan_medali_seni')
            ->where('id_kelompok_peserta_seni', (int) $kelKalah->id_kelompok_peserta_seni)
            ->where('jenis_medali', 'perak')
            ->countAllResults();
        $this->assertEquals(1, $perak, 'Kalah final seni harus dapat perak');

        // Cleanup
        $this->conn->table('perolehan_medali_seni')
            ->whereIn('id_kelompok_peserta_seni', [
                (int) $kelPemenang->id_kelompok_peserta_seni,
                (int) $kelKalah->id_kelompok_peserta_seni,
            ])
            ->delete();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  NULL/edge cases — service must not crash
    // ═══════════════════════════════════════════════════════════════════════════

    public function testAdvanceTandingWithNullPemenangDoesNothing(): void
    {
        $fakePartai = (object) [
            'id_pertandingan' => 999999,
            'id_kompetisi_tanding' => 999999,
            'id_atlet_merah' => 1,
            'id_atlet_biru' => 2,
            'babak' => 'Final',
            'nomor_pertandingan' => 1,
            'nomor_pertandingan_selanjutnya' => null,
            'skor_merah' => 0,
            'skor_biru' => 0,
        ];

        // Should not throw or crash
        $this->service->advanceTanding($fakePartai, null, 'Poin');
        $this->assertTrue(true); // Reached here = no crash
    }

    public function testAdvanceBattleSeniWithInvalidBattleDoesNotCrash(): void
    {
        $fakeBattle = (object) [
            'id_battle_seni' => 999999,
            'id_kompetisi_seni' => 999999,
            'nomor_battle' => 1,
            'babak' => 'Final',
            'nomor_battle_selanjutnya' => null,
            'id_penampilan_seni_biru' => 999998,
            'id_penampilan_seni_merah' => 999997,
        ];

        // Should not throw — graceful handling when kompetisi not found
        $this->service->advanceBattleSeni($fakeBattle, 999998);
        $this->assertTrue(true);
    }
}
