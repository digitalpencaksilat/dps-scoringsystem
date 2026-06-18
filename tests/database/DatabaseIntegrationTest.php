<?php

namespace Tests\Database;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Integration test koneksi database, struktur tabel, dan operasi dasar.
 * Menguji kedua koneksi: `default` (db_testing_event) & `sudinpora` (db_sudinpora).
 *
 * @group database
 */
final class DatabaseIntegrationTest extends CIUnitTestCase
{
    private $defaultDb;
    private $sudinporaDb;

    protected function setUp(): void
    {
        parent::setUp();

        // Selalu gunakan koneksi MySQL 'sudinpora' (bukan default group
        // yang menjadi SQLite :memory: di env testing).
        try {
            $this->sudinporaDb = Database::connect('sudinpora');
            $this->sudinporaDb->query('SELECT 1');
            $this->defaultDb = $this->sudinporaDb; // Gunakan koneksi yang sama
        } catch (\Throwable $e) {
            $this->sudinporaDb = null;
            $this->defaultDb = null;
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Koneksi
    // ═══════════════════════════════════════════════════════════════════════════

    public function testDefaultDbConnected(): void
    {
        $this->assertNotNull($this->defaultDb, 'Default DB (db_testing_event) tidak bisa konek');
        $result = $this->defaultDb->query('SELECT 1 as alive')->getRow();
        $this->assertSame('1', (string) $result->alive);
    }

    public function testSudinporaDbConnected(): void
    {
        $this->assertNotNull($this->sudinporaDb, 'Sudinpora DB (db_sudinpora) tidak bisa konek');
        $result = $this->sudinporaDb->query('SELECT 1 as alive')->getRow();
        $this->assertSame('1', (string) $result->alive);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Struktur Tabel — Core scoring tables must exist
    // ═══════════════════════════════════════════════════════════════════════════

    public function testCoreTablesExistInDefaultDb(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $requiredTables = [
            'pertandingan',
            'penilaian_tanding',
            'penampilan_seni',
            'penilaian_seni',
            'perangkat_pertandingan',
            'gelanggang',
            'kelas_tanding',
            'kompetisi_tanding',
            'kompetisi_seni',
            'perolehan_medali_tanding',
            'perolehan_medali_seni',
        ];

        $existing = $this->defaultDb->query('SHOW TABLES')->getResultArray();
        $existingNames = array_map(fn($r) => strtolower(array_values($r)[0]), $existing);

        foreach ($requiredTables as $table) {
            $this->assertContains(
                $table,
                $existingNames,
                "Table '$table' tidak ada di default DB."
            );
        }
    }

    public function testPenilaianTandingHasRequiredColumns(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $columns = $this->defaultDb->query('SHOW COLUMNS FROM penilaian_tanding')->getResultArray();
        $colNames = array_map(fn($r) => $r['Field'], $columns);

        $expected = ['id_penilaian_tanding', 'id_pertandingan', 'id_perangkat_pertandingan', 'penilaian_merah', 'penilaian_biru'];
        foreach ($expected as $col) {
            $this->assertContains($col, $colNames, "Column '$col' missing from penilaian_tanding");
        }
    }

    public function testPertandinganHasRequiredColumns(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $columns = $this->defaultDb->query('SHOW COLUMNS FROM pertandingan')->getResultArray();
        $colNames = array_map(fn($r) => $r['Field'], $columns);

        $expected = [
            'id_pertandingan', 'nomor_pertandingan', 'id_atlet_merah', 'id_atlet_biru',
            'id_pemenang', 'babak', 'skor_merah', 'skor_biru', 'ringkasan_nilai',
            'status_pertandingan', 'ronde_pertandingan', 'jenis_kemenangan',
        ];
        foreach ($expected as $col) {
            $this->assertContains($col, $colNames, "Column '$col' missing from pertandingan");
        }
    }

    public function testPenampilanSeniHasRequiredColumns(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $columns = $this->defaultDb->query('SHOW COLUMNS FROM penampilan_seni')->getResultArray();
        $colNames = array_map(fn($r) => $r['Field'], $columns);

        $expected = [
            'id_penampilan_seni', 'id_kelompok_peserta_seni', 'nilai_akhir',
            'catatan_nilai_sama', 'status_penampilan',
        ];
        foreach ($expected as $col) {
            $this->assertContains($col, $colNames, "Column '$col' missing from penampilan_seni");
        }
    }

    public function testPenilaianSeniHasRequiredColumns(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $columns = $this->defaultDb->query('SHOW COLUMNS FROM penilaian_seni')->getResultArray();
        $colNames = array_map(fn($r) => $r['Field'], $columns);

        $expected = [
            'id_penilaian_seni', 'id_penampilan_seni', 'id_perangkat_pertandingan',
            'penilaian', 'terpilih', 'status_ready',
        ];
        foreach ($expected as $col) {
            $this->assertContains($col, $colNames, "Column '$col' missing from penilaian_seni");
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Data Existence — core reference data should exist
    // ═══════════════════════════════════════════════════════════════════════════

    public function testGelanggangExists(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $count = $this->defaultDb->table('gelanggang')->countAllResults();
        $this->assertGreaterThanOrEqual(1, $count, 'Minimal 1 gelanggang harus ada');
    }

    public function testPerangkatPertandinganExists(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $count = $this->defaultDb->table('perangkat_pertandingan')->countAllResults();
        $this->assertGreaterThanOrEqual(1, $count, 'Minimal 1 perangkat pertandingan harus ada');
    }

    public function testKelasTandingExists(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $count = $this->defaultDb->table('kelas_tanding')->countAllResults();
        $this->assertGreaterThanOrEqual(1, $count, 'Minimal 1 kelas tanding harus ada');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Missing Timestamp Columns — safety check (AGENTS.md warning)
    // ═══════════════════════════════════════════════════════════════════════════

    public function testCoreTablesHaveNoCreatedAtColumn(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $legacyTables = ['pertandingan', 'penilaian_tanding', 'penilaian_seni', 'penampilan_seni'];
        foreach ($legacyTables as $table) {
            $columns = $this->defaultDb->query("SHOW COLUMNS FROM {$table}")->getResultArray();
            $colNames = array_map(fn($r) => $r['Field'], $columns);

            $this->assertNotContains(
                'created_at',
                $colNames,
                "Table `{$table}` punya created_at — pastikan model pakai \$useTimestamps=false!"
            );
            $this->assertNotContains(
                'updated_at',
                $colNames,
                "Table `{$table}` punya updated_at — pastikan model pakai \$useTimestamps=false!"
            );
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Relational Integrity — pastikan referensi antar tabel valid
    // ═══════════════════════════════════════════════════════════════════════════

    public function testPenilaianTandingRefsValidPertandingan(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        // Pastikan tidak ada penilaian_tanding dengan id_pertandingan orphan
        $orphans = $this->defaultDb->query(
            "SELECT COUNT(*) as cnt FROM penilaian_tanding pt
             LEFT JOIN pertandingan p ON p.id_pertandingan = pt.id_pertandingan
             WHERE p.id_pertandingan IS NULL"
        )->getRow();

        $this->assertSame('0', (string) $orphans->cnt, 'Ada penilaian_tanding dengan id_pertandingan orphan!');
    }

    public function testPenilaianSeniRefsValidPenampilanSeni(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $orphans = $this->defaultDb->query(
            "SELECT COUNT(*) as cnt FROM penilaian_seni pn
             LEFT JOIN penampilan_seni ps ON ps.id_penampilan_seni = pn.id_penampilan_seni
             WHERE ps.id_penampilan_seni IS NULL"
        )->getRow();

        $this->assertSame('0', (string) $orphans->cnt, 'Ada penilaian_seni dengan id_penampilan_seni orphan!');
    }

    public function testPertandinganRefsValidKompetisiTanding(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        $orphans = $this->defaultDb->query(
            "SELECT COUNT(*) as cnt FROM pertandingan p
             LEFT JOIN kompetisi_tanding kt ON kt.id_kompetisi_tanding = p.id_kompetisi_tanding
             WHERE kt.id_kompetisi_tanding IS NULL"
        )->getRow();

        $this->assertSame('0', (string) $orphans->cnt, 'Ada pertandingan dengan id_kompetisi_tanding orphan!');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Transactions
    // ═══════════════════════════════════════════════════════════════════════════

    public function testTransactionsWork(): void
    {
        if ($this->defaultDb === null) {
            $this->markTestSkipped('Default DB tidak tersedia.');
        }

        // Gunakan id_pertandingan valid (FK constraint) & marker unik utk deteksi.
        $testMarker = 'TRANSACTION_TEST_' . time();
        $validPartai = 96; // ID pertandingan yg ada di db_sudinpora

        $before = $this->defaultDb->query(
            "SELECT COUNT(*) as cnt FROM penilaian_tanding WHERE penilaian_merah = ?",
            [$testMarker]
        )->getRow();

        $this->defaultDb->transBegin();

        $this->defaultDb->query(
            "INSERT INTO penilaian_tanding (id_pertandingan, id_perangkat_pertandingan, penilaian_merah, penilaian_biru, pemenang) VALUES (?, ?, ?, ?, ?)",
            [$validPartai, 1, $testMarker, '{}', '']
        );

        $after = $this->defaultDb->query(
            "SELECT COUNT(*) as cnt FROM penilaian_tanding WHERE penilaian_merah = ?",
            [$testMarker]
        )->getRow();

        $this->assertSame('1', (string) $after->cnt, 'INSERT dalam transaksi gagal');

        $this->defaultDb->transRollback();

        $afterRollback = $this->defaultDb->query(
            "SELECT COUNT(*) as cnt FROM penilaian_tanding WHERE penilaian_merah = ?",
            [$testMarker]
        )->getRow();

        $this->assertSame('0', (string) $afterRollback->cnt, 'ROLLBACK tidak mengembalikan data');
    }
}
