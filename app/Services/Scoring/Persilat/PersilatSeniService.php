<?php

namespace App\Services\Scoring\Persilat;

/**
 * PersilatSeniService — scoring calculation for PERSILAT seni
 * Parity: /dps/application/models/sistem_penilaian/seni/PERSILAT_model.php
 * 
 * Handles:
 * - Median calculation (nilai tengah)
 * - Standard deviation (standar deviasi)
 * - Median kebenaran calculation
 * - Penalty calculation (hukuman)
 * - Jury selection (terpilih flag) based on median
 * - Final score calculation (median - penalty)
 */
class PersilatSeniService
{
    /**
     * Calculate final score and update catatan_nilai_sama
     * Parity: PERSILAT_model::hitung_nilai_akhir()
     * 
     * @param object $penampilanSeni penampilan_seni row
     * @param array $dataNilai array of penilaian_seni rows
     * @return float|false Final score or false on error
     */
    public function hitungNilaiAkhir(object $penampilanSeni, array $dataNilai)
    {
        if (empty($dataNilai)) {
            return false;
        }

        // Extract total_nilai from each jury
        $arrayTotalNilai = [];
        foreach ($dataNilai as $penilaianJuri) {
            $nilaiAkhir = 0;

            // Parse penilaian JSON
            $penilaian = is_string($penilaianJuri->penilaian)
                ? json_decode($penilaianJuri->penilaian, true)
                : (array) $penilaianJuri->penilaian;

            if (isset($penilaian['penilaian']['ringkasan']['total_nilai'])) {
                $nilaiAkhir = (float) $penilaian['penilaian']['ringkasan']['total_nilai'];
            }

            $arrayTotalNilai[] = [
                'id_perangkat_pertandingan' => (int) $penilaianJuri->id_perangkat_pertandingan,
                'nilai_akhir' => $nilaiAkhir,
            ];
        }

        // Sort ascending (lowest to highest) — parity legacy
        usort($arrayTotalNilai, fn($a, $b) => $a['nilai_akhir'] <=> $b['nilai_akhir']);

        // Calculate components
        $valuesOnly = array_column($arrayTotalNilai, 'nilai_akhir');
        $median = $this->hitungMedian($valuesOnly);
        $hukuman = $this->hitungHukuman($dataNilai);
        $standarDeviasi = $this->hitungStandarDeviasi($valuesOnly);

        // Calculate median kebenaran
        $arrayKebenaranNilai = [];
        foreach ($dataNilai as $penilaianJuri) {
            $penilaian = is_string($penilaianJuri->penilaian)
                ? json_decode($penilaianJuri->penilaian, true)
                : (array) $penilaianJuri->penilaian;

            if (isset($penilaian['penilaian']['unsur_nilai']['kebenaran']['nilai_diperoleh'])) {
                $arrayKebenaranNilai[] = (float) $penilaian['penilaian']['unsur_nilai']['kebenaran']['nilai_diperoleh'];
            }
        }
        sort($arrayKebenaranNilai);
        $medianKebenaran = !empty($arrayKebenaranNilai) ? $this->hitungMedian($arrayKebenaranNilai) : 0;

        // Save calculation details to catatan_nilai_sama
        $catatanNilaiSama = [
            'hukuman' => $hukuman,
            'median' => $median,
            'standar_deviasi' => $standarDeviasi,
            'median_kebenaran' => $medianKebenaran,
        ];

        $db = \Config\Database::connect();
        $db->table('penampilan_seni')
            ->where('id_penampilan_seni', $penampilanSeni->id_penampilan_seni)
            ->update(['catatan_nilai_sama' => json_encode($catatanNilaiSama)]);

        // Update terpilih flag for selected jury (median jury)
        $this->pilihPenilaianJuri($penampilanSeni->id_penampilan_seni, $arrayTotalNilai);

        // Return final score
        return $median - $hukuman;
    }

    /**
     * Select jury based on median position (terpilih flag)
     * Parity: PERSILAT_model::_pilih_penilaian_juri()
     * 
     * Logic:
     * - Sort ascending by total_nilai
     * - If even count: select middle 2 jury
     * - If odd count: select middle 1 jury
     * 
     * @param int $idPenampilanSeni
     * @param array $arrayTotalNilai sorted array of ['id_perangkat_pertandingan', 'nilai_akhir']
     */
    private function pilihPenilaianJuri(int $idPenampilanSeni, array $arrayTotalNilai): void
    {
        $db = \Config\Database::connect();

        // Reset all to not selected
        $db->table('penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->update(['terpilih' => 0]);

        $jumlahData = count($arrayTotalNilai);

        if ($jumlahData % 2 === 0) {
            // Even: select middle 2
            $index1 = ($jumlahData / 2) - 1;
            $index2 = ($jumlahData / 2);

            $idPerangkat1 = $arrayTotalNilai[$index1]['id_perangkat_pertandingan'];
            $idPerangkat2 = $arrayTotalNilai[$index2]['id_perangkat_pertandingan'];

            $db->table('penilaian_seni')
                ->where('id_penampilan_seni', $idPenampilanSeni)
                ->whereIn('id_perangkat_pertandingan', [$idPerangkat1, $idPerangkat2])
                ->update(['terpilih' => 1]);
        } else {
            // Odd: select middle 1
            $indexMedian = (int) floor($jumlahData / 2);
            $idPerangkat = $arrayTotalNilai[$indexMedian]['id_perangkat_pertandingan'];

            $db->table('penilaian_seni')
                ->where('id_penampilan_seni', $idPenampilanSeni)
                ->where('id_perangkat_pertandingan', $idPerangkat)
                ->update(['terpilih' => 1]);
        }
    }

    /**
     * Calculate median from sorted array
     * Parity: PERSILAT_model::_hitung_median()
     * 
     * @param array $values sorted array of float values
     * @return float
     */
    private function hitungMedian(array $values): float
    {
        $count = count($values);

        if ($count === 0) {
            return 0.0;
        }

        if ($count % 2 === 0) {
            // Even: average of middle 2
            $median = ($values[($count / 2) - 1] + $values[$count / 2]) / 2;
        } else {
            // Odd: middle value
            $indexMedian = (int) floor($count / 2);
            $median = $values[$indexMedian];
        }

        return (float) number_format($median, 4, '.', '');
    }

    /**
     * Calculate standard deviation (population)
     * Parity: PERSILAT_model::_hitung_standar_deviasi()
     * 
     * @param array $values array of float values
     * @return float
     */
    private function hitungStandarDeviasi(array $values): float
    {
        $count = count($values);

        if ($count === 0) {
            return 0.0;
        }

        // Calculate mean
        $mean = array_sum($values) / $count;

        // Calculate variance
        $variance = 0.0;
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }

        // Standard deviation (population)
        $stdDev = sqrt($variance / $count);

        return (float) number_format($stdDev, 10, '.', '');
    }

    /**
     * Calculate total penalty (hukuman)
     * Parity: PERSILAT_model::_hitung_hukuman()
     * 
     * Note: Penalty should be identical across all jury (entered by KP).
     * We take the first jury's penalty value.
     * 
     * @param array $dataNilai array of penilaian_seni rows
     * @return float
     */
    private function hitungHukuman(array $dataNilai): float
    {
        if (empty($dataNilai)) {
            return 0.0;
        }

        $penilaian = is_string($dataNilai[0]->penilaian)
            ? json_decode($dataNilai[0]->penilaian, true)
            : (array) $dataNilai[0]->penilaian;

        $totalHukuman = $penilaian['penilaian']['ringkasan']['total_hukuman'] ?? 0;

        return (float) $totalHukuman;
    }

    /**
     * Get jenis unsur nilai from penilaian data
     * Parity: PERSILAT_model::get_jenis_unsur_nilai_seni()
     * 
     * @param array $dataNilai
     * @return array
     */
    public function getJenisUnsurNilai(array $dataNilai): array
    {
        if (empty($dataNilai)) {
            return [];
        }

        $penilaian = is_string($dataNilai[0]->penilaian)
            ? json_decode($dataNilai[0]->penilaian, true)
            : (array) $dataNilai[0]->penilaian;

        if (!isset($penilaian['penilaian']['unsur_nilai'])) {
            return [];
        }

        return array_keys($penilaian['penilaian']['unsur_nilai']);
    }

    /**
     * Get jenis hukuman from penilaian data
     * Parity: PERSILAT_model::get_jenis_hukuman_seni()
     * 
     * @param array $dataNilai
     * @return array
     */
    public function getJenisHukuman(array $dataNilai): array
    {
        if (empty($dataNilai)) {
            return [];
        }

        $penilaian = is_string($dataNilai[0]->penilaian)
            ? json_decode($dataNilai[0]->penilaian, true)
            : (array) $dataNilai[0]->penilaian;

        if (!isset($penilaian['penilaian']['hukuman'])) {
            return [];
        }

        return array_keys($penilaian['penilaian']['hukuman']);
    }
}
