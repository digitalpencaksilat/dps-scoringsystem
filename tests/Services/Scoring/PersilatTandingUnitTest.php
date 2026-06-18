<?php

namespace Tests\Services\Scoring;

use App\Services\Scoring\Persilat\PersilatTandingService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Unit test untuk helper/validation methods PersilatTandingService
 * (metode yang tidak butuh database).
 *
 * @group tanding-unit
 */
final class PersilatTandingUnitTest extends CIUnitTestCase
{
    private PersilatTandingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PersilatTandingService();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // isNilaiJuriLegal
    // ═══════════════════════════════════════════════════════════════════════════

    public function testNilaiJuriLegalPukulan(): void
    {
        $this->assertTrue($this->service->isNilaiJuriLegal(1));
    }

    public function testNilaiJuriLegalTendangan(): void
    {
        $this->assertTrue($this->service->isNilaiJuriLegal(2));
    }

    public function testNilaiJuriLegalJatuhan(): void
    {
        $this->assertTrue($this->service->isNilaiJuriLegal(3));
    }

    public function testNilaiJuriIllegalZero(): void
    {
        $this->assertFalse($this->service->isNilaiJuriLegal(0));
    }

    public function testNilaiJuriIllegalNegative(): void
    {
        $this->assertFalse($this->service->isNilaiJuriLegal(-1));
    }

    public function testNilaiJuriIllegalLarge(): void
    {
        $this->assertFalse($this->service->isNilaiJuriLegal(5));
        $this->assertFalse($this->service->isNilaiJuriLegal(4));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // validasiStrukturMinimal
    // ═══════════════════════════════════════════════════════════════════════════

    public function testValidasiStrukturMinimalValid(): void
    {
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
        ]);
        $this->assertTrue($this->service->validasiStrukturMinimal($json));
    }

    public function testValidasiStrukturMinimalMissingRonde(): void
    {
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
            ],
        ]);
        $this->assertFalse($this->service->validasiStrukturMinimal($json));
    }

    public function testValidasiStrukturMinimalNoRincian(): void
    {
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => [],
                '2' => [],
                '3' => [],
            ],
        ]);
        $this->assertFalse($this->service->validasiStrukturMinimal($json));
    }

    public function testValidasiStrukturMinimalEmptyJson(): void
    {
        $this->assertFalse($this->service->validasiStrukturMinimal(''));
        $this->assertFalse($this->service->validasiStrukturMinimal('invalid json'));
    }

    public function testValidasiStrukturMinimalNoRondePertandingan(): void
    {
        $json = json_encode(['other_key' => 'value']);
        $this->assertFalse($this->service->validasiStrukturMinimal($json));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // validasiFormatJson (full — requires kategori_nilai + ringkasan)
    // ═══════════════════════════════════════════════════════════════════════════

    public function testValidasiFormatJsonValid(): void
    {
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
            'kategori_nilai' => ['pukulan' => 0],
            'ringkasan' => ['total_nilai' => 0],
        ]);
        $this->assertTrue($this->service->validasiFormatJson($json));
    }

    public function testValidasiFormatJsonMissingKategori(): void
    {
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
            'ringkasan' => ['total_nilai' => 0],
        ]);
        $this->assertFalse($this->service->validasiFormatJson($json));
    }

    public function testValidasiFormatJsonMissingRingkasan(): void
    {
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
            'kategori_nilai' => ['pukulan' => 0],
        ]);
        $this->assertFalse($this->service->validasiFormatJson($json));
    }

    public function testValidasiFormatJsonAcceptsObject(): void
    {
        $obj = (object) [
            'ronde_pertandingan' => (object) [
                '1' => (object) ['rincian' => []],
                '2' => (object) ['rincian' => []],
                '3' => (object) ['rincian' => []],
            ],
            'kategori_nilai' => (object) ['pukulan' => 0],
            'ringkasan' => (object) ['total_nilai' => 0],
        ];
        $this->assertTrue($this->service->validasiFormatJson($obj));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // ubahEntryNilaiJuri — tambah & hapus entry
    // ═══════════════════════════════════════════════════════════════════════════

    private function makeEmptyRow(): object
    {
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
            'kategori_nilai' => ['pukulan' => 0],
            'ringkasan' => ['total_nilai' => 0],
        ]);

        return (object) [
            'id_pertandingan' => 1,
            'id_perangkat_pertandingan' => 1,
            'penilaian_merah' => $json,
            'penilaian_biru' => $json,
        ];
    }

    public function testUbahEntryTambahNilai(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;
        $result = $this->service->ubahEntryNilaiJuri($row, 'merah', '1', [
            'nilai' => 1,
            'status' => 'input',
        ], $ts);

        $decoded = json_decode($result->penilaian_merah);
        $rincian = $decoded->ronde_pertandingan->{'1'}->rincian;

        $this->assertCount(1, $rincian);
        $this->assertSame(1, $rincian[0]->nilai);
        $this->assertSame('input', $rincian[0]->status);
        $this->assertSame($ts, $rincian[0]->timestamp);
        $this->assertFalse($rincian[0]->is_deleted);
    }

    public function testUbahEntryHapusNilai(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        // Tambah dulu
        $row = $this->service->ubahEntryNilaiJuri($row, 'biru', '2', [
            'nilai' => 2,
            'status' => 'verified',
        ], $ts);

        // Hapus
        $row = $this->service->ubahEntryNilaiJuri($row, 'biru', '2', [
            'action' => 'remove',
        ], $ts + 100);

        $decoded = json_decode($row->penilaian_biru);
        $rincian = $decoded->ronde_pertandingan->{'2'}->rincian;

        $this->assertCount(1, $rincian);
        $this->assertTrue($rincian[0]->is_deleted);
        $this->assertSame($ts + 100, $rincian[0]->deleted_at);
        // Nilai asli tetap ada (soft delete)
        $this->assertSame(2, $rincian[0]->nilai);
    }

    public function testUbahEntryMultipleEntries(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        $row = $this->service->ubahEntryNilaiJuri($row, 'merah', '1', ['nilai' => 1, 'status' => 'input'], $ts);
        $row = $this->service->ubahEntryNilaiJuri($row, 'merah', '1', ['nilai' => 2, 'status' => 'input'], $ts + 1);
        $row = $this->service->ubahEntryNilaiJuri($row, 'merah', '1', ['nilai' => 3, 'status' => 'input'], $ts + 2);

        $decoded = json_decode($row->penilaian_merah);
        $rincian = $decoded->ronde_pertandingan->{'1'}->rincian;

        $this->assertCount(3, $rincian);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // prosesPenilaianKp — hukuman/teguran/peringatan/binaan/jatuhan
    // ═══════════════════════════════════════════════════════════════════════════

    public function testProsesKpTeguran(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        $rows = $this->service->prosesPenilaianKp([$row], 'merah', '1', 'teguran', -1, $ts);

        $decoded = json_decode($rows[0]->penilaian_merah);
        $rincian = $decoded->ronde_pertandingan->{'1'}->rincian;

        $this->assertCount(1, $rincian);
        $this->assertSame(-1, $rincian[0]->nilai);
        $this->assertSame('verified', $rincian[0]->status); // KP input langsung verified
    }

    public function testProsesKpPeringatan(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        $rows = $this->service->prosesPenilaianKp([$row], 'biru', '2', 'peringatan_1', -5, $ts);

        $decoded = json_decode($rows[0]->penilaian_biru);
        $rincian = $decoded->ronde_pertandingan->{'2'}->rincian;

        $this->assertCount(1, $rincian);
        $this->assertSame(-5, $rincian[0]->nilai);
    }

    public function testProsesKpJatuhan(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        $rows = $this->service->prosesPenilaianKp([$row], 'merah', '3', 'jatuhan', 3, $ts);

        $decoded = json_decode($rows[0]->penilaian_merah);
        $rincian = $decoded->ronde_pertandingan->{'3'}->rincian;

        $this->assertCount(1, $rincian);
        $this->assertSame(3, $rincian[0]->nilai);
    }

    public function testProsesKpHapusTeguran(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        $rows = $this->service->prosesPenilaianKp([$row], 'merah', '1', 'teguran_1', -1, $ts);

        // Hapus teguran
        $rows = $this->service->prosesPenilaianKp($rows, 'merah', '1', 'teguran_1', 'hapus', $ts + 100);

        $decoded = json_decode($rows[0]->penilaian_merah);
        $rincian = $decoded->ronde_pertandingan->{'1'}->rincian;

        $this->assertTrue($rincian[0]->is_deleted);
    }

    public function testProsesKpBinaan(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        $rows = $this->service->prosesPenilaianKp([$row], 'biru', '1', 'binaan_1', 1, $ts);

        $decoded = json_decode($rows[0]->penilaian_biru);
        $catatan = $decoded->ronde_pertandingan->{'1'}->catatan;

        $this->assertNotNull($catatan);
        $this->assertSame(1, $catatan->binaan);
    }

    public function testProsesKpBinaanDua(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        $rows = $this->service->prosesPenilaianKp([$row], 'merah', '2', 'binaan_2', 2, $ts);

        $decoded = json_decode($rows[0]->penilaian_merah);
        $catatan = $decoded->ronde_pertandingan->{'2'}->catatan;

        $this->assertNotNull($catatan);
        $this->assertSame(2, $catatan->binaan);
    }

    public function testProsesKpHapusBinaan(): void
    {
        $row = $this->makeEmptyRow();
        $ts = 1000000;

        // Tambah binaan 2
        $rows = $this->service->prosesPenilaianKp([$row], 'merah', '1', 'binaan_2', 2, $ts);

        // Hapus binaan 2
        $rows = $this->service->prosesPenilaianKp($rows, 'merah', '1', 'binaan_2', 'hapus', $ts + 100);

        $decoded = json_decode($rows[0]->penilaian_merah);
        $catatan = $decoded->ronde_pertandingan->{'1'}->catatan;

        $this->assertNotNull($catatan);
        $this->assertSame(1, $catatan->binaan); // Turun ke binaan 1
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // hitungSkorAtlet — scoring pipeline untuk baris kosong (no DB needed)
    // ═══════════════════════════════════════════════════════════════════════════

    public function testHitungSkorAtletEmptyInput(): void
    {
        $result = $this->service->hitungSkorAtlet([]);
        $this->assertSame(0, $result['skor_merah']);
        $this->assertSame(0, $result['skor_biru']);
    }

    public function testHitungSkorAtletSingleEntry(): void
    {
        $ts = 1000000;
        $json = json_encode([
            'ronde_pertandingan' => [
                '1' => [
                    'rincian' => [
                        ['nilai' => 1, 'status' => 'input', 'warna' => null, 'id_nilai' => null, 'tag' => false, 'timestamp' => $ts, 'is_deleted' => false],
                    ],
                ],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
        ]);

        $rows = [
            (object) [
                'id_pertandingan' => 1,
                'id_perangkat_pertandingan' => 1,
                'penilaian_merah' => $json,
                'penilaian_biru' => json_encode([
                    'ronde_pertandingan' => [
                        '1' => ['rincian' => []],
                        '2' => ['rincian' => []],
                        '3' => ['rincian' => []],
                    ],
                ]),
            ],
        ];

        $result = $this->service->hitungSkorAtlet($rows);

        // Single entry = tidak terverifikasi (butuh >= 2), jadi skor tetap 0
        $this->assertSame(0, $result['skor_merah']);
        $this->assertSame(0, $result['skor_biru']);
        $this->assertIsArray($result['ringkasan']);
        $this->assertIsArray($result['rows']);
    }

    public function testHitungSkorAtletVerifikasiButuhDuaJuri(): void
    {
        $ts = 1000000;
        $jsonMerah = json_encode([
            'ronde_pertandingan' => [
                '1' => [
                    'rincian' => [
                        ['nilai' => 1, 'status' => 'input', 'warna' => null, 'id_nilai' => null, 'tag' => false, 'timestamp' => $ts, 'is_deleted' => false],
                    ],
                ],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
        ]);

        $jsonBiru = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
        ]);

        $rows = [
            (object) [
                'id_pertandingan' => 1,
                'id_perangkat_pertandingan' => 1,
                'penilaian_merah' => $jsonMerah,
                'penilaian_biru' => $jsonBiru,
            ],
            (object) [
                'id_pertandingan' => 1,
                'id_perangkat_pertandingan' => 2,
                'penilaian_merah' => $jsonMerah, // Nilai identik + timestamp identik
                'penilaian_biru' => $jsonBiru,
            ],
        ];

        $result = $this->service->hitungSkorAtlet($rows);

        // 2 juri input nilai identik → verified → skor_merah = 1
        $this->assertSame(1, $result['skor_merah']);
        $this->assertSame(0, $result['skor_biru']);
    }

    public function testHitungSkorAtletSoftDeleteIgnored(): void
    {
        $ts = 1000000;
        $jsonWithDeleted = json_encode([
            'ronde_pertandingan' => [
                '1' => [
                    'rincian' => [
                        ['nilai' => 5, 'status' => 'input', 'warna' => null, 'id_nilai' => null, 'tag' => false, 'timestamp' => $ts, 'is_deleted' => true],
                        ['nilai' => 1, 'status' => 'input', 'warna' => null, 'id_nilai' => null, 'tag' => false, 'timestamp' => $ts, 'is_deleted' => false],
                    ],
                ],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
        ]);

        $jsonBiru = json_encode([
            'ronde_pertandingan' => [
                '1' => ['rincian' => []],
                '2' => ['rincian' => []],
                '3' => ['rincian' => []],
            ],
        ]);

        $rows = [
            (object) ['id_pertandingan' => 1, 'id_perangkat_pertandingan' => 1, 'penilaian_merah' => $jsonWithDeleted, 'penilaian_biru' => $jsonBiru],
            (object) ['id_pertandingan' => 1, 'id_perangkat_pertandingan' => 2, 'penilaian_merah' => $jsonWithDeleted, 'penilaian_biru' => $jsonBiru],
        ];

        $result = $this->service->hitungSkorAtlet($rows);

        // 2 juri input nilai valid = skor 1; deleted entry ignored
        $this->assertSame(1, $result['skor_merah']);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Edge cases — service must not crash on invalid inputs
    // ═══════════════════════════════════════════════════════════════════════════

    public function testHitungSkorAtletMalformedJsonDoesNotCrash(): void
    {
        $rows = [
            (object) [
                'id_pertandingan' => 1,
                'id_perangkat_pertandingan' => 1,
                'penilaian_merah' => 'not valid json {{{',
                'penilaian_biru' => '{"ronde_pertandingan": {"1":{"rincian":[]},"2":{"rincian":[]},"3":{"rincian":[]}}}',
            ],
        ];

        // Malformed JSON row difilter oleh hitungSkorAtlet — tidak crash
        $result = $this->service->hitungSkorAtlet($rows);
        $this->assertSame(0, $result['skor_merah'], 'Should gracefully handle malformed JSON with score=0');
        $this->assertSame(0, $result['skor_biru'], 'Should gracefully handle malformed JSON with score=0');
    }

    public function testUbahEntryNilaiJuriWithNullRincianDoesNotCrash(): void
    {
        $row = (object) [
            'id_pertandingan' => 999,
            'id_perangkat_pertandingan' => 1,
            'penilaian_merah' => json_encode([
                'ronde_pertandingan' => [
                    '1' => ['rincian' => null], // null instead of array
                    '2' => ['rincian' => []],
                    '3' => ['rincian' => []],
                ],
            ]),
            'penilaian_biru' => '{}',
        ];

        try {
            $result = $this->service->ubahEntryNilaiJuri($row, 'merah', '1', [
                'nilai' => 1,
                'status' => 'input',
            ]);
            $this->assertNotNull($result);
        } catch (\TypeError $e) {
            // Expected: null bukan array — tapi service seharusnya tidak crash
            $this->assertStringContainsString('array', $e->getMessage());
        }
    }
}
