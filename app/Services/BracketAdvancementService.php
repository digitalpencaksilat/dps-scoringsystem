<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * BracketAdvancementService — Bracket advancement logic for TANDING and SENI BATTLE.
 *
 * Parity:
 *   - Tanding: legacy Pertandingan_model::selesaikan_pertandingan() → Sistem_gugur_tunggal_model
 *   - Seni: legacy Sekretaris_pertandingan_model::selesai_battle_seni() → Kompetisi_seni_model
 *
 * All writes wrapped in a single transaction. Fire-and-forget (no exceptions on bagan JSON edge cases).
 */
class BracketAdvancementService
{
    private BaseConnection $db;

    /**
     * @param BaseConnection|string|null $db Optional explicit DB connection (or group name).
     *                                       Defaults to the framework default group.
     *                                       Useful for tests that target a specific MySQL group.
     */
    public function __construct($db = null)
    {
        if ($db instanceof BaseConnection) {
            $this->db = $db;
        } elseif (is_string($db) && $db !== '') {
            $this->db = \Config\Database::connect($db);
        } else {
            $this->db = \Config\Database::connect();
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  TANDING
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Advance bracket after a tanding match is marked "selesai".
     * Must be called AFTER pertandingan row is updated with id_pemenang + jenis_kemenangan + status=selesai.
     *
     * @param object $pertandingan  The pertandingan row (must have: id_pertandingan, id_kompetisi_tanding,
     *                              id_atlet_merah, id_atlet_biru, babak, nomor_pertandingan,
     *                              nomor_pertandingan_selanjutnya, skor_merah, skor_biru)
     * @param int|null $idPemenang  id_peserta_tanding of the winner
     * @param string $jenisKemenangan
     */
    public function advanceTanding(object $pertandingan, ?int $idPemenang, string $jenisKemenangan): void
    {
        if ($idPemenang === null) {
            return; // Seri / belum ditentukan — nothing to advance
        }

        $this->db->transBegin();

        try {
            $this->inputMedaliTanding($pertandingan, $idPemenang);
            $this->inputAtletKePertandinganSelanjutnya($pertandingan, $idPemenang);
            $this->updateBaganPertandingan($pertandingan, $idPemenang);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                log_message('error', '[BracketAdvancement] Tanding transaction failed for partai #' . $pertandingan->id_pertandingan);
            } else {
                $this->db->transCommit();
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[BracketAdvancement] Tanding exception: ' . $e->getMessage());
        }
    }

    /**
     * Input medali tanding. Parity: Sistem_gugur_tunggal_model::input_medali_tanding()
     */
    private function inputMedaliTanding(object $pertandingan, int $idPemenang): void
    {
        $kompetisi = $this->db->table('kompetisi_tanding')
            ->where('id_kompetisi_tanding', $pertandingan->id_kompetisi_tanding)
            ->get()->getRow();

        if (! $kompetisi || (int) $kompetisi->perhitungan_medali !== 1) {
            return;
        }

        $idKalah = ((int) $idPemenang === (int) $pertandingan->id_atlet_merah)
            ? (int) $pertandingan->id_atlet_biru
            : (int) $pertandingan->id_atlet_merah;

        $babak = $pertandingan->babak;

        if ($babak === 'Final') {
            $this->setMedaliTanding($idPemenang, 'emas');
            $this->setMedaliTanding($idKalah, 'perak');
        } elseif ($babak === 'Semi Final') {
            // Juara tiga bersama — cek apakah ada field di pertandingan atau di kompetisi
            $juaraTigaBersama = property_exists($pertandingan, 'juara_tiga_bersama')
                ? (int) $pertandingan->juara_tiga_bersama
                : 1; // Default: co-bronze (most common in pencak silat)

            // Count peserta in this kompetisi for edge case
            $jumlahPeserta = (int) $this->db->table('pertandingan')
                ->where('id_kompetisi_tanding', $pertandingan->id_kompetisi_tanding)
                ->select('COUNT(DISTINCT CASE WHEN id_atlet_merah IS NOT NULL THEN id_atlet_merah END) + COUNT(DISTINCT CASE WHEN id_atlet_biru IS NOT NULL THEN id_atlet_biru END) as jml')
                ->get()->getRow()->jml;

            if ($juaraTigaBersama === 1 || ($juaraTigaBersama === 0 && $jumlahPeserta < 4)) {
                $this->setMedaliTanding($idKalah, 'perunggu');
            }
            // Else: loser goes to Perebutan Juara Tiga (handled by advance step)
        } elseif ($babak === 'Perebutan Juara Tiga') {
            $this->setMedaliTanding($idPemenang, 'perunggu');
        } else {
            // Penyisihan: check semua_dapat_medali flag
            $semuaDapatMedali = property_exists($pertandingan, 'semua_dapat_medali')
                ? (int) $pertandingan->semua_dapat_medali
                : 0;
            if ($semuaDapatMedali === 1) {
                $this->setMedaliTanding($idKalah, 'perunggu');
            }
        }
    }

    /**
     * Set/replace medali for one peserta_tanding.
     */
    private function setMedaliTanding(int $idPesertaTanding, string $jenisMedali): void
    {
        $this->db->table('perolehan_medali_tanding')
            ->where('id_peserta_tanding', $idPesertaTanding)
            ->delete();

        $this->db->table('perolehan_medali_tanding')->insert([
            'id_peserta_tanding' => $idPesertaTanding,
            'jenis_medali'       => $jenisMedali,
        ]);
    }

    /**
     * Advance pemenang ke pertandingan selanjutnya.
     * Parity: Sistem_gugur_tunggal_model::input_atlet_ke_pertandingan_selanjutnya()
     */
    private function inputAtletKePertandinganSelanjutnya(object $pertandingan, int $idPemenang): void
    {
        $nomorPertandingan = $pertandingan->nomor_pertandingan ?? null;
        $nomorSelanjutnya  = $pertandingan->nomor_pertandingan_selanjutnya ?? null;

        if ($nomorPertandingan === null || $nomorSelanjutnya === null) {
            return; // Final or Perebutan Juara Tiga — terminal node
        }

        // Path A: Semi Final without juara_tiga_bersama → place LOSER into bronze playoff
        if ($pertandingan->babak === 'Semi Final') {
            $juaraTigaBersama = property_exists($pertandingan, 'juara_tiga_bersama')
                ? (int) $pertandingan->juara_tiga_bersama
                : 1;

            if ($juaraTigaBersama === 0) {
                $idKalah = ((int) $idPemenang === (int) $pertandingan->id_atlet_merah)
                    ? (int) $pertandingan->id_atlet_biru
                    : (int) $pertandingan->id_atlet_merah;

                // Bronze match number = final's nomor_pertandingan_selanjutnya + 1
                $bronzeNomor = (int) $nomorSelanjutnya + 1;

                $slotKalah = ((int) $nomorPertandingan % 2 === 1) ? 'id_atlet_biru' : 'id_atlet_merah';

                $this->db->table('pertandingan')
                    ->where('nomor_pertandingan', $bronzeNomor)
                    ->where('id_kompetisi_tanding', $pertandingan->id_kompetisi_tanding)
                    ->update([$slotKalah => $idKalah]);
            }
        }

        // Path B: any non-Final round → place WINNER into next round
        if ($pertandingan->babak !== 'Final') {
            $slot = ((int) $nomorPertandingan % 2 === 1) ? 'id_atlet_biru' : 'id_atlet_merah';

            $this->db->table('pertandingan')
                ->where('nomor_pertandingan', (int) $nomorSelanjutnya)
                ->where('id_kompetisi_tanding', $pertandingan->id_kompetisi_tanding)
                ->update([$slot => $idPemenang]);
        }
    }

    /**
     * Update bagan_pertandingan JSON in kompetisi_tanding.
     * Parity: Sistem_gugur_tunggal_model::update_bagan_pertandingan()
     */
    private function updateBaganPertandingan(object $pertandingan, int $idPemenang): void
    {
        $kompetisi = $this->db->table('kompetisi_tanding')
            ->where('id_kompetisi_tanding', $pertandingan->id_kompetisi_tanding)
            ->get()->getRow();

        if (! $kompetisi || empty($kompetisi->bagan_pertandingan)) {
            return;
        }

        $bagan = json_decode($kompetisi->bagan_pertandingan);
        if ($bagan === null || ! isset($bagan->results)) {
            return;
        }

        $nomorPertandingan = (int) $pertandingan->nomor_pertandingan;
        $jumlahPertandingan = count($bagan->results[0][0] ?? []);

        if ($jumlahPertandingan < 1) {
            return;
        }

        $counterPertandingan = 1;
        $roundIndex = 0;

        for ($i = $jumlahPertandingan; $i >= 1; $i = intdiv($i, 2)) {
            for ($p = 0; $p < $i; $p++) {
                if ($nomorPertandingan === $counterPertandingan) {
                    $skorBiru  = (int) ($pertandingan->skor_biru ?? 0);
                    $skorMerah = (int) ($pertandingan->skor_merah ?? 0);

                    // If scores are equal (DQ/BYE/WO), use synthetic 0 vs 5
                    if ((int) $idPemenang === (int) $pertandingan->id_atlet_merah) {
                        if ($skorMerah === $skorBiru) {
                            $bagan->results[0][$roundIndex][$p] = [0, 5];
                        } else {
                            $bagan->results[0][$roundIndex][$p] = [$skorBiru, $skorMerah];
                        }
                    } else {
                        if ($skorMerah === $skorBiru) {
                            $bagan->results[0][$roundIndex][$p] = [5, 0];
                        } else {
                            $bagan->results[0][$roundIndex][$p] = [$skorBiru, $skorMerah];
                        }
                    }
                    break 2;
                }
                $counterPertandingan++;
            }
            $roundIndex++;

            if ($i === 1) {
                break;
            }
        }

        $this->db->table('kompetisi_tanding')
            ->where('id_kompetisi_tanding', $pertandingan->id_kompetisi_tanding)
            ->update(['bagan_pertandingan' => json_encode($bagan)]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  SENI BATTLE
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Advance bracket after seni battle winner is chosen.
     * Extends what BattleSeniModel::setPemenang() already does (which only sets winner + advances to next battle).
     * This adds: medali writes + bagan_battle_seni JSON update.
     *
     * @param object $battle            The battle_seni row (must have: id_battle_seni, id_kompetisi_seni,
     *                                   nomor_battle, babak, nomor_battle_selanjutnya,
     *                                   id_penampilan_seni_biru, id_penampilan_seni_merah)
     * @param int $idPenampilanPemenang  id_penampilan_seni of winner
     */
    public function advanceBattleSeni(object $battle, int $idPenampilanPemenang): void
    {
        $this->db->transBegin();

        try {
            $this->inputMedaliBattleSeni($battle, $idPenampilanPemenang);
            $this->updateBaganBattleSeni($battle, $idPenampilanPemenang);

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                log_message('error', '[BracketAdvancement] Seni battle transaction failed for battle #' . $battle->id_battle_seni);
            } else {
                $this->db->transCommit();
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', '[BracketAdvancement] Seni battle exception: ' . $e->getMessage());
        }
    }

    /**
     * Input medali seni battle. Parity: Sistem_gugur_tunggal_model::input_medali_battle_seni()
     */
    private function inputMedaliBattleSeni(object $battle, int $idPenampilanPemenang): void
    {
        $kompetisi = $this->db->table('kompetisi_seni')
            ->where('id_kompetisi_seni', $battle->id_kompetisi_seni)
            ->get()->getRow();

        if (! $kompetisi || (int) $kompetisi->perhitungan_medali !== 1) {
            return;
        }

        // Get kelompok_peserta_seni ID dari penampilan_seni
        $getPeserta = function (int $idPenampilan): ?int {
            $row = $this->db->table('penampilan_seni')
                ->select('id_kelompok_peserta_seni')
                ->where('id_penampilan_seni', $idPenampilan)
                ->get()->getRow();
            return $row ? (int) $row->id_kelompok_peserta_seni : null;
        };

        $idKelompokPemenang = $getPeserta($idPenampilanPemenang);
        $idPenampilanKalah = ((int) $idPenampilanPemenang === (int) $battle->id_penampilan_seni_biru)
            ? (int) $battle->id_penampilan_seni_merah
            : (int) $battle->id_penampilan_seni_biru;
        $idKelompokKalah = $getPeserta($idPenampilanKalah);

        $babak = $battle->babak ?? '';

        if ($babak === 'Final') {
            if ($idKelompokPemenang) $this->setMedaliSeni($idKelompokPemenang, 'emas');
            if ($idKelompokKalah) $this->setMedaliSeni($idKelompokKalah, 'perak');
        } elseif ($babak === 'Semi Final') {
            // Seni battles typically use co-bronze (juara_tiga_bersama = 1)
            if ($idKelompokKalah) $this->setMedaliSeni($idKelompokKalah, 'perunggu');
        } elseif ($babak === 'Perebutan Juara Tiga') {
            if ($idKelompokPemenang) $this->setMedaliSeni($idKelompokPemenang, 'perunggu');
        }
    }

    /**
     * Set/replace medali for one kelompok_peserta_seni.
     */
    private function setMedaliSeni(int $idKelompokPesertaSeni, string $jenisMedali): void
    {
        $this->db->table('perolehan_medali_seni')
            ->where('id_kelompok_peserta_seni', $idKelompokPesertaSeni)
            ->delete();

        $this->db->table('perolehan_medali_seni')->insert([
            'id_kelompok_peserta_seni' => $idKelompokPesertaSeni,
            'jenis_medali'             => $jenisMedali,
        ]);
    }

    /**
     * Update bagan_battle_seni JSON in kompetisi_seni.
     * Parity: Sistem_gugur_tunggal_model::update_bagan_battle_seni()
     */
    private function updateBaganBattleSeni(object $battle, int $idPenampilanPemenang): void
    {
        $kompetisi = $this->db->table('kompetisi_seni')
            ->where('id_kompetisi_seni', $battle->id_kompetisi_seni)
            ->get()->getRow();

        if (! $kompetisi || empty($kompetisi->bagan_battle_seni)) {
            return;
        }

        $bagan = json_decode($kompetisi->bagan_battle_seni);
        if ($bagan === null || ! isset($bagan->results)) {
            return;
        }

        $nomorBattle = (int) ($battle->nomor_battle ?? 0);
        $jumlahBattle = count($bagan->results[0][0] ?? []);

        if ($jumlahBattle < 1 || $nomorBattle < 1) {
            return;
        }

        // Get nilai_akhir for biru and merah penampilan_seni (display purposes in bagan)
        $getNilaiAkhir = function (int $idPenampilan): float {
            $row = $this->db->table('penampilan_seni')
                ->select('nilai_akhir')
                ->where('id_penampilan_seni', $idPenampilan)
                ->get()->getRow();
            return $row ? (float) $row->nilai_akhir : 0;
        };

        $nilaiBiru  = $getNilaiAkhir((int) $battle->id_penampilan_seni_biru);
        $nilaiMerah = $getNilaiAkhir((int) $battle->id_penampilan_seni_merah);

        // Find position in bracket JSON
        $counterBattle = 1;
        $roundIndex = 0;

        for ($i = $jumlahBattle; $i >= 1; $i = intdiv($i, 2)) {
            for ($p = 0; $p < $i; $p++) {
                if ($nomorBattle === $counterBattle) {
                    // Ensure winner shows higher score
                    if ((int) $idPenampilanPemenang === (int) $battle->id_penampilan_seni_merah) {
                        if ($nilaiMerah <= $nilaiBiru) {
                            $bagan->results[0][$roundIndex][$p] = [0, 5]; // Synthetic win
                        } else {
                            $bagan->results[0][$roundIndex][$p] = [$nilaiBiru, $nilaiMerah];
                        }
                    } else {
                        if ($nilaiBiru <= $nilaiMerah) {
                            $bagan->results[0][$roundIndex][$p] = [5, 0]; // Synthetic win
                        } else {
                            $bagan->results[0][$roundIndex][$p] = [$nilaiBiru, $nilaiMerah];
                        }
                    }
                    break 2;
                }
                $counterBattle++;
            }
            $roundIndex++;

            if ($i === 1) {
                break;
            }
        }

        $this->db->table('kompetisi_seni')
            ->where('id_kompetisi_seni', $battle->id_kompetisi_seni)
            ->update(['bagan_battle_seni' => json_encode($bagan)]);
    }
}
