<?php

namespace App\Services\Scoring\Persilat;

/**
 * PERSILAT Seni scoring service.
 *
 * Parity legacy: application/models/sistem_penilaian/seni/PERSILAT_model.php
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
     * @param array $penilaianJuri array of penilaian_seni rows (objects)
     * @return array{nilai_akhir: float, median: float, hukuman: float, standar_deviasi: float, median_kebenaran: float, terpilih_ids: int[]}
     */
    public function hitungNilaiAkhir(array $penilaianJuri): array
    {
        $default = [
            'nilai_akhir'      => 0.0,
            'median'           => 0.0,
            'hukuman'          => 0.0,
            'standar_deviasi'  => 0.0,
            'median_kebenaran' => 0.0,
            'terpilih_ids'     => [],
        ];

        if (empty($penilaianJuri)) {
            return $default;
        }

        // Extract nilai per juri
        $arrayTotalNilai = [];
        $arrayKebenaranNilai = [];

        foreach ($penilaianJuri as $row) {
            $nilai = $this->extractNilaiAkhirPerJuri($row);
            if ($nilai === null) continue;

            $arrayTotalNilai[] = [
                'id_perangkat_pertandingan' => (int) $row->id_perangkat_pertandingan,
                'nilai_akhir'               => $nilai,
            ];

            // Extract kebenaran value if exists
            $kebenaranNilai = $this->extractKebenaranNilai($row);
            if ($kebenaranNilai !== null) {
                $arrayKebenaranNilai[] = $kebenaranNilai;
            }
        }

        if (empty($arrayTotalNilai)) {
            return $default;
        }

        // Sort ascending for median calculation
        usort($arrayTotalNilai, fn($a, $b) => $a['nilai_akhir'] <=> $b['nilai_akhir']);
        sort($arrayKebenaranNilai);

        $nilaiValues = array_column($arrayTotalNilai, 'nilai_akhir');
        $median = $this->hitungMedian($nilaiValues);
        $hukuman = $this->hitungHukuman($penilaianJuri);
        $stdDev = $this->hitungStandarDeviasi($nilaiValues);
        $medianKebenaran = !empty($arrayKebenaranNilai) ? $this->hitungMedian($arrayKebenaranNilai) : 0.0;
        $nilaiAkhir = round($median - $hukuman, 4);

        // Pilih juri terpilih (median position)
        $terpilihIds = $this->pilihJuriTerpilih($arrayTotalNilai);

        return [
            'nilai_akhir'      => $nilaiAkhir,
            'median'           => $median,
            'hukuman'          => $hukuman,
            'standar_deviasi'  => $stdDev,
            'median_kebenaran' => $medianKebenaran,
            'terpilih_ids'     => $terpilihIds,
        ];
    }

    /**
     * Urutkan juara — descending by nilai_akhir.
     * Parity legacy urutkan_juara().
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
     * Get jenis unsur nilai dari record penilaian.
     * Parity legacy get_jenis_unsur_nilai_seni().
     */
    public function getJenisUnsurNilai($dataNilai): array
    {
        $unsurNilai = [];

        if (is_object($dataNilai)) {
            $penilaian = isset($dataNilai->penilaian)
                ? (is_string($dataNilai->penilaian) ? json_decode($dataNilai->penilaian) : $dataNilai->penilaian)
                : null;

            if ($penilaian && isset($penilaian->penilaian->unsur_nilai)) {
                foreach ($penilaian->penilaian->unsur_nilai as $jenis => $isi) {
                    $unsurNilai[] = $jenis;
                }
            }
        } elseif (is_array($dataNilai) && !empty($dataNilai)) {
            $first = $dataNilai[0];
            $penilaian = json_decode($first->penilaian ?? '{}');
            if (isset($penilaian->penilaian->unsur_nilai)) {
                foreach ($penilaian->penilaian->unsur_nilai as $jenis => $isi) {
                    $unsurNilai[] = $jenis;
                }
            }
        }

        return $unsurNilai;
    }

    /**
     * Get jenis hukuman dari record penilaian.
     * Parity legacy get_jenis_hukuman_seni().
     */
    public function getJenisHukuman($dataNilai): array
    {
        $hukuman = [];

        if (is_object($dataNilai)) {
            $penilaian = isset($dataNilai->penilaian)
                ? (is_string($dataNilai->penilaian) ? json_decode($dataNilai->penilaian) : $dataNilai->penilaian)
                : null;

            if ($penilaian && isset($penilaian->penilaian->hukuman)) {
                foreach ($penilaian->penilaian->hukuman as $jenis => $isi) {
                    $hukuman[] = $jenis;
                }
            }
        } elseif (is_array($dataNilai) && !empty($dataNilai)) {
            $first = $dataNilai[0];
            $penilaian = json_decode($first->penilaian ?? '{}');
            if (isset($penilaian->penilaian->hukuman)) {
                foreach ($penilaian->penilaian->hukuman as $jenis => $isi) {
                    $hukuman[] = $jenis;
                }
            }
        }

        return $hukuman;
    }

    /**
     * Kelompokkan penilaian seni per id_penampilan_seni.
     * Parity legacy kelompokkan_penilaian_seni().
     */
    public function kelompokkanPenilaian(array $dataNilai): array
    {
        $kelompok = [];
        foreach ($dataNilai as $row) {
            $kelompok[$row->id_penampilan_seni][] = $row;
        }
        return $kelompok;
    }

    /**
     * Validasi konsistensi penalty di semua juri.
     * Parity legacy validate_penalty_consistency().
     */
    public function validatePenaltyConsistency(array $penilaianJuri): array
    {
        if (empty($penilaianJuri)) {
            return ['valid' => false, 'error' => 'No records found'];
        }

        $penaltyHashes = [];
        $penaltyTotals = [];

        foreach ($penilaianJuri as $row) {
            $data = is_string($row->penilaian) ? json_decode($row->penilaian, true) : (array) $row->penilaian;
            if (!$data) continue;

            $penalties = $data['penilaian']['hukuman'] ?? [];
            $totalHukuman = (float) ($data['penilaian']['ringkasan']['total_hukuman'] ?? 0);

            ksort($penalties);
            $penaltyHashes[] = md5(json_encode($penalties));
            $penaltyTotals[] = $totalHukuman;
        }

        $uniqueHashes = array_unique($penaltyHashes);
        $uniqueTotals = array_unique($penaltyTotals);

        $isValid = (count($uniqueHashes) <= 1) && (count($uniqueTotals) <= 1);

        return [
            'valid'             => $isValid,
            'error'             => !$isValid ? 'Inconsistent penalties across jury records' : null,
            'penalty_totals'    => $penaltyTotals,
            'unique_totals'     => count($uniqueTotals),
        ];
    }

    /**
     * Sync hukuman dari KP ke semua juri (update hukuman section di semua rows).
     * Dipanggil oleh KP saat mengubah hukuman.
     *
     * @param array  $penilaianJuri array of penilaian_seni rows
     * @param array  $hukumanBaru   hukuman object baru dari KP
     * @return array updated rows (penilaian JSON sudah dimodifikasi)
     */
    public function syncHukumanKeSemuaJuri(array $penilaianJuri, array $hukumanBaru): array
    {
        $totalHukuman = $this->hitungTotalDariHukumanObj($hukumanBaru);

        foreach ($penilaianJuri as $index => $row) {
            $data = is_string($row->penilaian) ? json_decode($row->penilaian, true) : (array) $row->penilaian;
            if (!$data) continue;

            $data['penilaian']['hukuman'] = $hukumanBaru;
            $data['penilaian']['ringkasan']['total_hukuman'] = $totalHukuman;

            // Recalc total_nilai = total_unsur_nilai - total_hukuman
            $totalUnsur = (float) ($data['penilaian']['ringkasan']['total_unsur_nilai'] ?? $data['penilaian']['ringkasan']['total_nilai'] ?? 0);
            $data['penilaian']['ringkasan']['total_nilai'] = round($totalUnsur - $totalHukuman, 4);

            $row->penilaian = json_encode($data);
            $penilaianJuri[$index] = $row;
        }

        return $penilaianJuri;
    }

    // ─── Private Methods ──────────────────────────────────────────────────

    /**
     * Ambil nilai akhir per juri dari row penilaian_seni.
     * Parity legacy: cek nilai_akhir_per_juri dulu, fallback ke JSON ringkasan.
     */
    private function extractNilaiAkhirPerJuri(object $row): ?float
    {
        if (!empty($row->nilai_akhir_per_juri) && (float) $row->nilai_akhir_per_juri !== 0.0) {
            return (float) $row->nilai_akhir_per_juri;
        }

        if (empty($row->penilaian)) return null;

        $parsed = is_string($row->penilaian) ? json_decode($row->penilaian, true) : (array) $row->penilaian;
        if (!$parsed || (is_string($row->penilaian) && json_last_error() !== JSON_ERROR_NONE)) return null;

        return (float) ($parsed['penilaian']['ringkasan']['total_nilai'] ?? 0);
    }

    /**
     * Extract kebenaran nilai_diperoleh dari row penilaian.
     */
    private function extractKebenaranNilai(object $row): ?float
    {
        if (empty($row->penilaian)) return null;

        $parsed = is_string($row->penilaian) ? json_decode($row->penilaian, true) : (array) $row->penilaian;
        if (!$parsed) return null;

        $kebenaran = $parsed['penilaian']['unsur_nilai']['kebenaran'] ?? null;
        if ($kebenaran && isset($kebenaran['nilai_diperoleh'])) {
            return (float) $kebenaran['nilai_diperoleh'];
        }

        return null;
    }

    /**
     * Hitung median dari array nilai yang sudah di-sort ascending.
     * Parity legacy _hitung_median().
     */
    private function hitungMedian(array $sortedNilai): float
    {
        $n = count($sortedNilai);
        if ($n === 0) return 0.0;

        if ($n % 2 === 0) {
            $median = ($sortedNilai[$n / 2 - 1] + $sortedNilai[$n / 2]) / 2;
        } else {
            $median = $sortedNilai[(int) floor($n / 2)];
        }

        return round((float) $median, 4);
    }

    /**
     * Hitung total hukuman — harus identik di semua juri (diisi oleh KP).
     * Parity legacy _hitung_hukuman().
     */
    private function hitungHukuman(array $penilaianJuri): float
    {
        $penaltyValues = [];

        foreach ($penilaianJuri as $row) {
            if (empty($row->penilaian)) continue;
            $parsed = is_string($row->penilaian) ? json_decode($row->penilaian, true) : (array) $row->penilaian;
            if (!$parsed) continue;
            $penaltyValues[] = (float) ($parsed['penilaian']['ringkasan']['total_hukuman'] ?? 0);
        }

        if (empty($penaltyValues)) return 0.0;

        $uniqueValues = array_unique($penaltyValues);
        if (count($uniqueValues) > 1) {
            log_message('warning', 'PERSILAT Seni: penalty inconsistency — values: ' . json_encode($penaltyValues));
            // Use most common value (parity legacy)
            $counts = array_count_values(array_map(fn($v) => (string) $v, $penaltyValues));
            $mostCommon = array_search(max($counts), $counts);
            return (float) $mostCommon;
        }

        return (float) $penaltyValues[0];
    }

    /**
     * Hitung standar deviasi populasi.
     * Parity legacy _hitung_standar_deviasi().
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
     * Parity legacy _pilih_penilaian_juri().
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

    /**
     * Hitung total hukuman dari object hukuman (bukan dari ringkasan).
     */
    private function hitungTotalDariHukumanObj(array $hukumanObj): float
    {
        $total = 0.0;
        foreach ($hukumanObj as $jenis => $hukuman) {
            if (isset($hukuman['detail_hukuman']['nilai_hukuman'])) {
                $total += (float) $hukuman['detail_hukuman']['nilai_hukuman'];
            } elseif (isset($hukuman['nilai_hukuman'])) {
                $total += (float) $hukuman['nilai_hukuman'];
            }
        }
        return round($total, 4);
    }
}
