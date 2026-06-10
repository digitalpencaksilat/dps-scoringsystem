<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model penilaian_tanding — nilai per juri per partai.
 * Kolom penilaian_merah / penilaian_biru berisi JSON (lihat docs/RENCANA_MIGRASI).
 * Skema legacy db_sudinpora — tanpa timestamp.
 */
class PenilaianTandingModel extends Model
{
    protected $table            = 'penilaian_tanding';
    protected $primaryKey       = 'id_penilaian_tanding';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_pertandingan',
        'id_perangkat_pertandingan',
        'penilaian_merah',
        'penilaian_biru',
        'pemenang',
    ];

    /**
     * Semua baris penilaian (semua juri) untuk satu pertandingan.
     * Hanya baris yang sudah terikat ke perangkat (id_perangkat_pertandingan != null).
     */
    public function getByPertandingan(int $idPertandingan): array
    {
        return $this->where('id_pertandingan', $idPertandingan)
            ->where('id_perangkat_pertandingan IS NOT NULL', null, false)
            ->orderBy('id_perangkat_pertandingan', 'ASC')
            ->findAll();
    }

    /**
     * Baris penilaian milik satu juri pada satu pertandingan.
     */
    public function getByPertandinganDanPerangkat(int $idPertandingan, int $idPerangkat): ?object
    {
        return $this->where('id_pertandingan', $idPertandingan)
            ->where('id_perangkat_pertandingan', $idPerangkat)
            ->first();
    }

    /**
     * Proses incremental nilai juri (tambah/hapus entry) dengan ROW LOCK + transaksi,
     * lalu recalculate skor seluruh atlet. Parity legacy
     * proses_penilaian_juri_incremental() + hitung_skor_atlet().
     *
     * Reliability (lihat docs Fase 5 §6.2): SELECT ... FOR UPDATE mengunci baris
     * juri agar input bersamaan tidak saling timpa; semua dibungkus transaksi.
     *
     * @param object $pertandingan baris pertandingan berlangsung (punya id_pertandingan, ronde_pertandingan)
     * @param int    $idPerangkat  id juri
     * @param string $sudut        'merah'|'biru'
     * @param array  $entry        ['nilai'=>int,'status'=>...] atau ['action'=>'remove']
     * @param \App\Services\Scoring\Persilat\PersilatTandingService $service
     * @return array{merah:object,biru:object}|false data nilai terbaru juri ini, atau false bila gagal
     */
    public function prosesIncremental(object $pertandingan, int $idPerangkat, string $sudut, array $entry, $service)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            // Kunci baris juri ini.
            $db->query(
                'SELECT id_penilaian_tanding FROM penilaian_tanding
                 WHERE id_pertandingan = ? AND id_perangkat_pertandingan = ? FOR UPDATE',
                [$pertandingan->id_pertandingan, $idPerangkat]
            );

            $row = $this->getByPertandinganDanPerangkat((int) $pertandingan->id_pertandingan, $idPerangkat);
            if ($row === null) {
                $db->transRollback();
                return false;
            }

            $ronde = (string) $pertandingan->ronde_pertandingan;
            $row   = $service->ubahEntryNilaiJuri($row, $sudut, $ronde, $entry);

            // Validasi format JSON sisi yang diubah sebelum simpan.
            $kolom = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';
            if (! $service->validasiFormatJson($row->$kolom)) {
                $db->transRollback();
                return false;
            }

            $this->where('id_pertandingan', $pertandingan->id_pertandingan)
                ->where('id_perangkat_pertandingan', $idPerangkat)
                ->set('penilaian_merah', $row->penilaian_merah)
                ->set('penilaian_biru', $row->penilaian_biru)
                ->update();

            // Recalculate skor seluruh juri partai ini.
            $this->hitungDanSimpanSkor((int) $pertandingan->id_pertandingan, $service);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return false;
            }

            $db->transCommit();

            $updated = $this->getByPertandinganDanPerangkat((int) $pertandingan->id_pertandingan, $idPerangkat);

            return [
                'merah' => json_decode($updated->penilaian_merah),
                'biru'  => json_decode($updated->penilaian_biru),
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'prosesIncremental gagal: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Jalankan pipeline skor service lalu persist baris juri + skor partai.
     * Parity legacy hitung_skor_atlet() + _simpan_semua_penilaian_juri() +
     * _simpan_nilai_akhir().
     *
     * @param \App\Services\Scoring\Persilat\PersilatTandingService $service
     */
    public function hitungDanSimpanSkor(int $idPertandingan, $service): array
    {
        $rows  = $this->getByPertandingan($idPertandingan);
        $hasil = $service->hitungSkorAtlet($rows);

        // Simpan ulang tiap baris juri (hasil verifikasi/warna/ringkasan).
        foreach ($hasil['rows'] as $row) {
            if (! $service->validasiFormatJson($row->penilaian_merah)
                || ! $service->validasiFormatJson($row->penilaian_biru)) {
                continue;
            }
            $this->where('id_pertandingan', $idPertandingan)
                ->where('id_perangkat_pertandingan', $row->id_perangkat_pertandingan)
                ->set('penilaian_merah', $row->penilaian_merah)
                ->set('penilaian_biru', $row->penilaian_biru)
                ->update();
        }

        // Simpan skor & ringkasan ke tabel pertandingan.
        $this->db->table('pertandingan')
            ->where('id_pertandingan', $idPertandingan)
            ->update([
                'skor_merah'      => $hasil['skor_merah'],
                'skor_biru'       => $hasil['skor_biru'],
                'ringkasan_nilai' => json_encode($hasil['ringkasan']),
            ]);

        return $hasil;
    }

    /**
     * Proses penilaian KP (hukuman/teguran/peringatan/binaan/jatuhan) ke seluruh
     * juri dengan transaksi + lock seluruh baris partai, lalu recalc skor.
     * Parity legacy proses_penilaian_kp() + hitung_skor_atlet().
     *
     * @param \App\Services\Scoring\Persilat\PersilatTandingService $service
     * @return array|false data nilai semua juri terbaru, atau false bila gagal
     */
    public function prosesKp(object $pertandingan, string $sudut, string $ronde, string $mode, string|int $jumlah, $service)
    {
        $db = $this->db;
        $db->transBegin();

        try {
            // Kunci semua baris penilaian partai ini.
            $db->query(
                'SELECT id_penilaian_tanding FROM penilaian_tanding
                 WHERE id_pertandingan = ? AND id_perangkat_pertandingan IS NOT NULL FOR UPDATE',
                [$pertandingan->id_pertandingan]
            );

            $rows = $this->getByPertandingan((int) $pertandingan->id_pertandingan);
            if (empty($rows)) {
                $db->transRollback();
                return false;
            }

            $rows = $service->prosesPenilaianKp($rows, $sudut, $ronde, $mode, $jumlah);

            foreach ($rows as $row) {
                $kolom = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';
                if (! $service->validasiFormatJson($row->$kolom)) {
                    $db->transRollback();
                    return false;
                }
                $this->where('id_pertandingan', $pertandingan->id_pertandingan)
                    ->where('id_perangkat_pertandingan', $row->id_perangkat_pertandingan)
                    ->set('penilaian_merah', $row->penilaian_merah)
                    ->set('penilaian_biru', $row->penilaian_biru)
                    ->update();
            }

            $this->hitungDanSimpanSkor((int) $pertandingan->id_pertandingan, $service);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return false;
            }

            $db->transCommit();

            return $this->getByPertandingan((int) $pertandingan->id_pertandingan);
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'prosesKp gagal: ' . $e->getMessage());
            return false;
        }
    }
}
