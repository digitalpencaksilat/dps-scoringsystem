<?php

namespace Tests\Services\Scoring;

use App\Services\Scoring\Persilat\PersilatTandingService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Parity test PERSILAT Tanding terhadap data nyata db_sudinpora.
 *
 * Strategi (lihat docs/RENCANA_MIGRASI_PENILAIAN_PERSILAT.md, Fase 2 §6.1):
 * algoritma hitung skor bersifat deterministik dari (nilai, timestamp) tiap
 * entry yang sudah tersimpan di kolom penilaian_merah/biru. Maka kita:
 *   1. Muat baris penilaian asli tiap partai bernilai.
 *   2. Jalankan PersilatTandingService::hitungSkorAtlet().
 *   3. Bandingkan skor_merah/skor_biru hasil service dengan nilai yang
 *      DISIMPAN legacy di tabel pertandingan (kolom skor_merah/skor_biru).
 *
 * Jika service menghasilkan skor identik untuk SELURUH partai bernilai,
 * berarti port algoritma sudah parity.
 *
 * @group parity
 */
final class PersilatTandingServiceTest extends CIUnitTestCase
{
    private PersilatTandingService $service;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PersilatTandingService();
        $this->conn = Database::connect('sudinpora');
    }

    /**
     * Ambil partai bernilai (skor>0, ringkasan ada, >=3 juri).
     *
     * @return array<int, array{0:int,1:int,2:int}> [id_pertandingan, skor_merah, skor_biru]
     */
    public static function partaiProvider(): array
    {
        $db = Database::connect('sudinpora');
        $rows = $db->query(
            "SELECT p.id_pertandingan, p.skor_merah, p.skor_biru,
                    COUNT(pt.id_penilaian_tanding) AS n_juri
             FROM pertandingan p
             JOIN penilaian_tanding pt ON pt.id_pertandingan = p.id_pertandingan
             WHERE pt.id_perangkat_pertandingan IS NOT NULL
               AND (p.skor_merah > 0 OR p.skor_biru > 0)
               AND p.ringkasan_nilai IS NOT NULL AND p.ringkasan_nilai != ''
             GROUP BY p.id_pertandingan
             HAVING n_juri >= 3
             ORDER BY p.id_pertandingan ASC"
        )->getResultArray();

        $cases = [];
        foreach ($rows as $r) {
            $id = (int) $r['id_pertandingan'];
            $cases['partai_' . $id] = [$id, (int) $r['skor_merah'], (int) $r['skor_biru']];
        }

        return $cases;
    }

    /**
     * @dataProvider partaiProvider
     */
    public function testSkorParityDenganDataLegacy(int $idPertandingan, int $skorMerahLegacy, int $skorBiruLegacy): void
    {
        $rows = $this->conn->table('penilaian_tanding')
            ->where('id_pertandingan', $idPertandingan)
            ->where('id_perangkat_pertandingan IS NOT NULL', null, false)
            ->orderBy('id_perangkat_pertandingan', 'ASC')
            ->get()
            ->getResult();

        $hasil = $this->service->hitungSkorAtlet($rows);

        $this->assertSame(
            $skorMerahLegacy,
            $hasil['skor_merah'],
            "Skor merah partai {$idPertandingan} tidak parity (legacy={$skorMerahLegacy}, service={$hasil['skor_merah']})"
        );
        $this->assertSame(
            $skorBiruLegacy,
            $hasil['skor_biru'],
            "Skor biru partai {$idPertandingan} tidak parity (legacy={$skorBiruLegacy}, service={$hasil['skor_biru']})"
        );
    }
}
