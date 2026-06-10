<?php

namespace App\Services\Scoring\Persilat;

/**
 * PERSILAT Seni scoring service.
 *
 * Parity legacy: application/models/sistem_penilaian/seni/PERSILAT_model.php (501 LOC)
 * Logic:
 *   - Ambil nilai setiap juri (nilai_akhir_per_juri atau total_nilai dari JSON ringkasan)
 *   - Urutkan ascending → hitung median
 *   - Ambil total hukuman (penalties) — harus identik antar juri (diisi KP)
 *   - Standar deviasi
 *   - Nilai akhir = median - hukuman
 *   - Pilih juri terpilih (median position) → flag `terpilih` = 1
 */
class PersilatSeniService
{
    /**
     * Hitung nilai akhir dari semua penilaian juri untuk satu penampilan.
     *
     * @param array $penilaianJuri array of penilaian_seni rows (objects with penilaian, nilai_akhir_per_juri, id_perangkat_pertandingan)
     * @return array{nilai_akhir: float, median: float, hukuman: float, standar_deviasi: float, terpilih_ids: int[]}
     */
    public function hitungNilaiAkhir(array $penilaianJuri): array
    {
        if (empty($penilaianJuri)) {
            return ['nilai_akhir' => 0.0, 'median' => 0.0, 'hukuman' => 0.0, 'standar_deviasi' => 0.0, 'terpilih_ids' => []];
        }

        $arrayTotalNilai = [];
        foreach ($penilaianJuri as $row) {
            $nilai = $this->extractNilaiAkhirPerJuri($row);
            if ($nilai === null) continue;
            $arrayTotalNilai[] = [
                'id_perangkat_pertandingan' => (int) $row->id_perangkat_pertandingan,
                'nilai_akhir'               => $nilai,
            ];
        }

        if (empty($arrayTotalNilai)) {
            return ['nilai_akhir' => 0.0, 'median' => 0.0, 'hukuman' => 0.0, 'standar_deviasi' => 0.0, 'terpilih_ids' => []];
        }

        // Sort ascending for median calculation
        usort($arrayTotalNilai, fn($a, $b) => $a['nilai_akhir'] <=> $b['nilai_akhir']);

        $nilaiValues = array_column($arrayTotalNilai, 'nilai_akhir');
        $median = $this->hitungMedian($nilaiValues);
        $hukuman = $this->hitungHukuman($penilaianJuri);
        $stdDev = $this->hitungStandarDeviasi($nilaiValues);
        $nilaiAkhir = round($median - $hukuman, 4);

        // Pilih juri terpilih (median position)
        $terpilihIds = $this->pilihJuriTerpilih($arrayTotalNilai);

        return [
            'nilai_akhir'      => $nilaiAkhir,
            'median'           => $median,
            'hukuman'          => $hukuman,
            'standar_deviasi'  => $stdDev,
            'terpilih_ids'     => $terpilihIds,
        ];
    }

    /**
     * Urutkan juara — simple descending sort by nilai_akhir.
     */
    public function urutkanJuara(array $penampilanList): array
    {
        usort($penampilanList, function ($a, $b) {
            $na = is_object($a) ? (float) ($a->nilai_akhir ?? 0) : (float) ($a['nilai_akhir'] ?? 0);
            $nb = is_object($b) ? (float) ($b->nilai_akhir ?? 0) : (float) ($b['nilai_akhir'] ?? 0);
            return $nb <=> $na;
        });
        return $penampilanList;
    }

    /**
     * Ambil nilai akhir per juri dari row penilaian_seni.
     */
    private function extractNilaiAkhirPerJuri(object $row): ?float
    {
        if (! empty($row->nilai_akhir_per_juri) && (float) $row->nilai_akhir_per_juri !== 0.0) {
            return (float) $row->nilai_akhir_per_juri;
        }

        if (empty($row->penilaian)) return null;

        $parsed = json_decode($row->penilaian, true);
        if (! $parsed || json_last_error() !== JSON_ERROR_NONE) return null;

        return (float) ($parsed['penilaian']['ringkasan']['total_nilai'] ?? 0);
    }

    /**
     * Hitung median dari array nilai yang sudah di-sort ascending.
     */
    private function hitungMedian(array $sortedNilai): float
    {
        $n = count($sortedNilai);
        if ($n === 0) return 0.0;

        if ($n % 2 === 0) {
            return round(($sortedNilai[$n / 2 - 1] + $sortedNilai[$n / 2]) / 2, 4);
        }

        return round($sortedNilai[(int) floor($n / 2)], 4);
    }

    /**
     * Hitung total hukuman — harus identik di semua juri (diisi oleh KP).
     */
    private function hitungHukuman(array $penilaianJuri): float
    {
        $penaltyValues = [];
        foreach ($penilaianJuri as $row) {
            if (empty($row->penilaian)) continue;
            $parsed = json_decode($row->penilaian, true);
            if (! $parsed) continue;
            $penaltyValues[] = (float) ($parsed['penilaian']['ringkasan']['total_hukuman'] ?? 0);
        }

        if (empty($penaltyValues)) return 0.0;

        $uniqueValues = array_unique($penaltyValues);
        if (count($uniqueValues) > 1) {
            log_message('warning', 'PERSILAT Seni: penalty inconsistency detected — using most common value');
            $counts = array_count_values($penaltyValues);
            return (float) array_search(max($counts), $counts);
        }

        return (float) $penaltyValues[0];
    }

    /**
     * Hitung standar deviasi populasi.
     */
    private function hitungStandarDeviasi(array $nilai): float
    {
        $n = count($nilai);
        if ($n === 0) return 0.0;

        $mean = array_sum($nilai) / $n;
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $nilai)) / $n;

        return round(sqrt($variance), 10);
    }

    /**
     * Pilih juri terpilih — median position (middle 1 or 2 jurors).
     *
     * @param array $sortedNilai array of ['id_perangkat_pertandingan' => int, 'nilai_akhir' => float] sorted ascending
     * @return int[] list of id_perangkat_pertandingan yang terpilih
     */
    private function pilihJuriTerpilih(array $sortedNilai): array
    {
        $n = count($sortedNilai);
        if ($n === 0) return [];

        if ($n % 2 === 0) {
            return [
                $sortedNilai[$n / 2 - 1]['id_perangkat_pertandingan'],
                $sortedNilai[$n / 2]['id_perangkat_pertandingan'],
            ];
        }

        return [$sortedNilai[(int) floor($n / 2)]['id_perangkat_pertandingan']];
    }
}
