<?php

/**
 * Quick test untuk verifikasi logic scoring seni PERSILAT
 * Run: php tests/scoring_seni_test.php
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Services/Scoring/Persilat/PersilatSeniService.php';

use App\Services\Scoring\Persilat\PersilatSeniService;

echo "=== Test Scoring Seni PERSILAT ===\n\n";

$service = new PersilatSeniService();

// Test case: 5 juri (ganjil)
echo "Test 1: 5 Juri (Ganjil)\n";
echo "Nilai: [9.0, 9.1, 9.35, 9.4, 9.5]\n";

$mockData = [
    (object)[
        'id_perangkat_pertandingan' => 1,
        'penilaian' => json_encode([
            'penilaian' => [
                'ringkasan' => [
                    'total_nilai' => 9.5,
                    'total_hukuman' => 0.5
                ],
                'unsur_nilai' => [
                    'kebenaran' => ['nilai_diperoleh' => 9.6]
                ]
            ]
        ]),
        'terpilih' => 0
    ],
    (object)[
        'id_perangkat_pertandingan' => 2,
        'penilaian' => json_encode([
            'penilaian' => [
                'ringkasan' => [
                    'total_nilai' => 9.0,
                    'total_hukuman' => 0.5
                ],
                'unsur_nilai' => [
                    'kebenaran' => ['nilai_diperoleh' => 9.1]
                ]
            ]
        ]),
        'terpilih' => 0
    ],
    (object)[
        'id_perangkat_pertandingan' => 3,
        'penilaian' => json_encode([
            'penilaian' => [
                'ringkasan' => [
                    'total_nilai' => 9.35,
                    'total_hukuman' => 0.5
                ],
                'unsur_nilai' => [
                    'kebenaran' => ['nilai_diperoleh' => 9.4]
                ]
            ]
        ]),
        'terpilih' => 0
    ],
    (object)[
        'id_perangkat_pertandingan' => 4,
        'penilaian' => json_encode([
            'penilaian' => [
                'ringkasan' => [
                    'total_nilai' => 9.1,
                    'total_hukuman' => 0.5
                ],
                'unsur_nilai' => [
                    'kebenaran' => ['nilai_diperoleh' => 9.2]
                ]
            ]
        ]),
        'terpilih' => 0
    ],
    (object)[
        'id_perangkat_pertandingan' => 5,
        'penilaian' => json_encode([
            'penilaian' => [
                'ringkasan' => [
                    'total_nilai' => 9.4,
                    'total_hukuman' => 0.5
                ],
                'unsur_nilai' => [
                    'kebenaran' => ['nilai_diperoleh' => 9.5]
                ]
            ]
        ]),
        'terpilih' => 0
    ],
];

$mockPenampilan = (object)['id_penampilan_seni' => 999];

// Disable DB update untuk test
echo "\nCalculating scores...\n";
$reflection = new ReflectionClass($service);

// Test median calculation
$hitungMedianMethod = $reflection->getMethod('hitungMedian');
$hitungMedianMethod->setAccessible(true);

$values = [9.0, 9.1, 9.35, 9.4, 9.5];
$median = $hitungMedianMethod->invoke($service, $values);
echo "Median (expected 9.35): " . $median . "\n";

// Test std dev
$hitungStdDevMethod = $reflection->getMethod('hitungStandarDeviasi');
$hitungStdDevMethod->setAccessible(true);

$stdDev = $hitungStdDevMethod->invoke($service, $values);
echo "Std Dev: " . $stdDev . "\n";
echo "Std Dev (6 decimals): " . number_format((float)$stdDev, 6, '.', '') . "\n";

// Test median kebenaran
$kebenaranValues = [9.1, 9.2, 9.4, 9.5, 9.6];
sort($kebenaranValues);
$medianKebenaran = $hitungMedianMethod->invoke($service, $kebenaranValues);
echo "Median Kebenaran (expected 9.4): " . $medianKebenaran . "\n";

// Test hukuman
$hitungHukumanMethod = $reflection->getMethod('hitungHukuman');
$hitungHukumanMethod->setAccessible(true);

$hukuman = $hitungHukumanMethod->invoke($service, $mockData);
echo "Hukuman (expected 0.5): " . $hukuman . "\n";

echo "\nFinal Score = Median - Hukuman = " . $median . " - " . $hukuman . " = " . ($median - $hukuman) . "\n";

// Test even number (6 juri)
echo "\n\nTest 2: 6 Juri (Genap)\n";
echo "Nilai: [9.0, 9.1, 9.35, 9.4, 9.5, 9.6]\n";

$valuesEven = [9.0, 9.1, 9.35, 9.4, 9.5, 9.6];
$medianEven = $hitungMedianMethod->invoke($service, $valuesEven);
echo "Median (expected 9.375): " . $medianEven . "\n";
echo "Formula: (9.35 + 9.4) / 2 = " . ((9.35 + 9.4) / 2) . "\n";

echo "\n=== Test Complete ===\n";
