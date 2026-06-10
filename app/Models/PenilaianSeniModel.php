<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model penilaian_seni — nilai per juri per penampilan seni.
 * Kolom `penilaian` berisi JSON (format berbeda per sub_kategori: tunggal/ganda/beregu/solo_kreatif).
 * Skema legacy db_sudinpora — tanpa timestamp.
 */
class PenilaianSeniModel extends Model
{
    protected $table            = 'penilaian_seni';
    protected $primaryKey       = 'id_penilaian_seni';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_penampilan_seni',
        'id_perangkat_pertandingan',
        'penilaian',
        'nilai_akhir_per_juri',
        'terpilih',
        'status_ready',
    ];

    // ─── Query Methods ────────────────────────────────────────────────────

    /**
     * Semua penilaian juri untuk satu penampilan.
     */
    public function getByPenampilan(int $idPenampilan): array
    {
        return $this->where('id_penampilan_seni', $idPenampilan)
            ->where('id_perangkat_pertandingan IS NOT NULL', null, false)
            ->orderBy('id_perangkat_pertandingan', 'ASC')
            ->findAll();
    }

    /**
     * Penilaian milik satu juri pada satu penampilan.
     */
    public function getByJuriDanPenampilan(int $idPerangkat, int $idPenampilan): ?object
    {
        return $this->where('id_penampilan_seni', $idPenampilan)
            ->where('id_perangkat_pertandingan', $idPerangkat)
            ->first();
    }

    /**
     * Juri yang terpilih (median position) untuk penampilan.
     */
    public function getTerpilih(int $idPenampilan): array
    {
        return $this->where('id_penampilan_seni', $idPenampilan)
            ->where('terpilih', 1)
            ->findAll();
    }

    // ─── Mutation Methods ─────────────────────────────────────────────────

    /**
     * Update nilai penilaian + nilai akhir per juri.
     */
    public function updateNilai(int $id, string $penilaian, string $nilaiAkhirPerJuri): bool
    {
        return (bool) $this->update($id, [
            'penilaian'          => $penilaian,
            'nilai_akhir_per_juri' => $nilaiAkhirPerJuri,
        ]);
    }

    /**
     * Toggle status ready (0↔1).
     * Returns new status.
     */
    public function toggleReady(int $id): int
    {
        $row = $this->find($id);
        if ($row === null) return 0;

        $newReady = ((int) $row->status_ready === 0) ? 1 : 0;
        $this->update($id, ['status_ready' => $newReady]);
        return $newReady;
    }

    /**
     * Reset semua status ready untuk penampilan (dipanggil saat penampilan baru dimulai).
     */
    public function resetAllReady(int $idPenampilan): bool
    {
        return (bool) $this->where('id_penampilan_seni', $idPenampilan)
            ->set('status_ready', 0)
            ->update();
    }

    /**
     * Buat row penilaian untuk satu juri pada satu penampilan.
     */
    public function buatPenilaian(int $idPenampilan, int $idPerangkat, string $formatJson): bool
    {
        return (bool) $this->insert([
            'id_penampilan_seni'       => $idPenampilan,
            'id_perangkat_pertandingan' => $idPerangkat,
            'penilaian'                => $formatJson,
            'terpilih'                 => 1,
            'status_ready'             => 0,
        ]);
    }

    /**
     * Hapus semua penilaian untuk penampilan.
     */
    public function hapusByPenampilan(int $idPenampilan): bool
    {
        return $this->where('id_penampilan_seni', $idPenampilan)->delete();
    }

    // ─── Aggregation Methods ──────────────────────────────────────────────

    /**
     * Hitung jumlah juri yang sudah ready untuk penampilan.
     */
    public function countReady(int $idPenampilan): int
    {
        return (int) $this->where('id_penampilan_seni', $idPenampilan)
            ->where('status_ready', 1)
            ->countAllResults();
    }

    /**
     * Hitung total juri yang ditugaskan untuk penampilan.
     */
    public function countJuri(int $idPenampilan): int
    {
        return (int) $this->where('id_penampilan_seni', $idPenampilan)
            ->where('id_perangkat_pertandingan IS NOT NULL', null, false)
            ->countAllResults();
    }

    /**
     * Set flag terpilih berdasarkan array id_perangkat_pertandingan.
     * Reset semua ke 0 lalu set yang terpilih ke 1.
     */
    public function setTerpilih(int $idPenampilan, array $idPerangkatTerpilih): bool
    {
        // Reset semua
        $this->where('id_penampilan_seni', $idPenampilan)
            ->set('terpilih', 0)
            ->update();

        if (empty($idPerangkatTerpilih)) return true;

        // Set terpilih
        return (bool) $this->where('id_penampilan_seni', $idPenampilan)
            ->whereIn('id_perangkat_pertandingan', $idPerangkatTerpilih)
            ->set('terpilih', 1)
            ->update();
    }

    // ─── KP Processing (transactional) ────────────────────────────────────

    /**
     * Proses penilaian KP seni: hitung nilai akhir, update flag terpilih,
     * simpan catatan_nilai_sama. Parity legacy proses_penilaian_kp().
     *
     * @param int $idPenampilan
     * @param \App\Services\Scoring\Persilat\PersilatSeniService $service
     * @return array|false hasil hitungan atau false jika gagal
     */
    public function prosesNilaiAkhir(int $idPenampilan, $service)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            // Lock semua row penilaian untuk penampilan ini
            $db->query(
                'SELECT id_penilaian_seni FROM penilaian_seni WHERE id_penampilan_seni = ? FOR UPDATE',
                [$idPenampilan]
            );

            $rows = $this->getByPenampilan($idPenampilan);
            if (empty($rows)) {
                $db->transRollback();
                return false;
            }

            // Hitung via service
            $hasil = $service->hitungNilaiAkhir($rows);

            // Update terpilih
            $this->setTerpilih($idPenampilan, $hasil['terpilih_ids']);

            // Simpan nilai_akhir ke penampilan_seni
            $db->table('penampilan_seni')
                ->where('id_penampilan_seni', $idPenampilan)
                ->update([
                    'nilai_akhir'        => (string) $hasil['nilai_akhir'],
                    'catatan_nilai_sama' => json_encode([
                        'hukuman'          => $hasil['hukuman'],
                        'median'           => $hasil['median'],
                        'standar_deviasi'  => $hasil['standar_deviasi'],
                    ]),
                ]);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return false;
            }

            $db->transCommit();
            return $hasil;
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'prosesNilaiAkhir gagal: ' . $e->getMessage());
            return false;
        }
    }
}
