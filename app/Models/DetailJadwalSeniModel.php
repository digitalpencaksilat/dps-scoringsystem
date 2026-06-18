<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailJadwalSeniModel extends Model
{
    protected $table            = 'detail_jadwal_seni';
    protected $primaryKey       = 'id_detail_jadwal_seni';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_jadwal_seni', 'id_penampilan_seni', 'id_battle_seni',
        'nomor_partai', 'nomor_urut', 'keterangan', 'status_partai',
    ];

    public function getPartaiPoolByJadwal(int $idJadwal): array
    {
        return $this->select('detail_jadwal_seni.*, penampilan_seni.*,
                    kelompok_peserta_seni.id_kontingen, kelompok_peserta_seni.nomor_undi,
                    kontingen.nama_kontingen,
                    kompetisi_seni.nomor_pool, kompetisi_seni.id_kompetisi_seni,
                    sub_kategori_seni.jenis_seni, sub_kategori_seni.nama_seni,
                    sub_kategori_seni.sistem_penampilan')
            ->join('penampilan_seni', 'penampilan_seni.id_penampilan_seni = detail_jadwal_seni.id_penampilan_seni', 'left')
            ->join('kelompok_peserta_seni', 'kelompok_peserta_seni.id_kelompok_peserta_seni = penampilan_seni.id_kelompok_peserta_seni', 'left')
            ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen', 'left')
            ->join('kompetisi_seni', 'kompetisi_seni.id_kompetisi_seni = kelompok_peserta_seni.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni', 'sub_kategori_seni.id_sub_kategori_seni = kompetisi_seni.id_sub_kategori_seni', 'left')
            ->where('detail_jadwal_seni.id_jadwal_seni', $idJadwal)
            ->where('detail_jadwal_seni.id_penampilan_seni IS NOT NULL')
            ->orderBy('detail_jadwal_seni.nomor_partai', 'ASC')
            ->findAll();
    }

    public function getPartaiBattleByJadwal(int $idJadwal): array
    {
        return $this->select('detail_jadwal_seni.*, battle_seni.*,
                    psb.id_kelompok_peserta_seni as kps_biru,
                    psm.id_kelompok_peserta_seni as kps_merah,
                    kb.nama_kontingen as kontingen_biru,
                    km.nama_kontingen as kontingen_merah')
            ->join('battle_seni', 'battle_seni.id_battle_seni = detail_jadwal_seni.id_battle_seni', 'left')
            ->join('penampilan_seni psb_data', 'psb_data.id_penampilan_seni = battle_seni.id_penampilan_seni_biru', 'left')
            ->join('penampilan_seni psm_data', 'psm_data.id_penampilan_seni = battle_seni.id_penampilan_seni_merah', 'left')
            ->join('kelompok_peserta_seni psm', 'psm.id_kelompok_peserta_seni = psm_data.id_kelompok_peserta_seni', 'left')
            ->join('kontingen km', 'km.id_kontingen = psm.id_kontingen', 'left')
            ->join('kelompok_peserta_seni psb', 'psb.id_kelompok_peserta_seni = psb_data.id_kelompok_peserta_seni', 'left')
            ->join('kontingen kb', 'kb.id_kontingen = psb.id_kontingen', 'left')
            ->where('detail_jadwal_seni.id_jadwal_seni', $idJadwal)
            ->where('detail_jadwal_seni.id_battle_seni IS NOT NULL')
            ->orderBy('detail_jadwal_seni.nomor_partai', 'ASC')
            ->findAll();
    }

    /**
     * Ambil detail_jadwal_seni yang sedang berlangsung di sebuah gelanggang.
     * Parity: legacy Detail_jadwal_seni_model::get_partai_berlangsung().
     *
     * Berlangsung = status_penampilan (pool) atau status_penampilan_seni_biru/merah
     * (battle) != 'sudah_tampil' AND != 'belum_tampil'.
     */
    public function getPartaiSeniBerlangsungByGelanggang(int $idGelanggang): ?array
    {
        $db = db_connect();

        return $db->query("
            SELECT djs.id_detail_jadwal_seni, djs.nomor_partai,
                   djs.id_penampilan_seni, djs.id_battle_seni,
                   ps_pool.status_penampilan as status_penampilan_pool,
                   (SELECT ps_biru.status_penampilan FROM penampilan_seni ps_biru
                     JOIN battle_seni bs ON bs.id_penampilan_seni_biru = ps_biru.id_penampilan_seni
                     WHERE bs.id_battle_seni = djs.id_battle_seni) as status_penampilan_seni_biru,
                   (SELECT ps_merah.status_penampilan FROM penampilan_seni ps_merah
                     JOIN battle_seni bs ON bs.id_penampilan_seni_merah = ps_merah.id_penampilan_seni
                     WHERE bs.id_battle_seni = djs.id_battle_seni) as status_penampilan_seni_merah,
                   (SELECT bs.id_penampilan_seni_biru FROM battle_seni bs
                     WHERE bs.id_battle_seni = djs.id_battle_seni) as id_penampilan_seni_biru,
                   (SELECT bs.id_penampilan_seni_merah FROM battle_seni bs
                     WHERE bs.id_battle_seni = djs.id_battle_seni) as id_penampilan_seni_merah
            FROM detail_jadwal_seni djs
            LEFT JOIN jadwal_seni js ON js.id_jadwal_seni = djs.id_jadwal_seni
            LEFT JOIN penampilan_seni ps_pool ON ps_pool.id_penampilan_seni = djs.id_penampilan_seni
            WHERE js.id_gelanggang = ?
              AND (
                (ps_pool.status_penampilan IS NOT NULL
                 AND ps_pool.status_penampilan NOT IN ('belum_tampil','sudah_tampil'))
                OR (
                  djs.id_battle_seni IS NOT NULL
                  AND (
                    (SELECT ps_biru.status_penampilan FROM penampilan_seni ps_biru
                      JOIN battle_seni bs ON bs.id_penampilan_seni_biru = ps_biru.id_penampilan_seni
                      WHERE bs.id_battle_seni = djs.id_battle_seni) NOT IN ('belum_tampil','sudah_tampil')
                    OR
                    (SELECT ps_merah.status_penampilan FROM penampilan_seni ps_merah
                      JOIN battle_seni bs ON bs.id_penampilan_seni_merah = ps_merah.id_penampilan_seni
                      WHERE bs.id_battle_seni = djs.id_battle_seni) NOT IN ('belum_tampil','sudah_tampil')
                  )
                )
              )
            ORDER BY djs.nomor_partai ASC
            LIMIT 1
        ", [$idGelanggang])->getRowArray() ?: null;
    }

    /**
     * Ambil penampilan_seni yang sedang tampil di sebuah gelanggang.
     * Parity: legacy Detail_jadwal_seni_model::get_penampilan_seni_berlangsung().
     *
     * Untuk pool: return penampilan dari id_penampilan_seni.
     * Untuk battle: return penampilan biru/merah yang sedang tampil.
     */
    public function getPenampilanSeniBerlangsungByGelanggang(int $idGelanggang): ?array
    {
        $partai = $this->getPartaiSeniBerlangsungByGelanggang($idGelanggang);
        if (!$partai) {
            return null;
        }

        $idPenampilan = null;

        // Sistem pool
        if (!empty($partai['id_penampilan_seni'])) {
            $idPenampilan = (int) $partai['id_penampilan_seni'];
        } elseif (!empty($partai['id_battle_seni'])) {
            // Sistem battle — pilih penampilan yang sedang tampil
            $statusBiru  = $partai['status_penampilan_seni_biru'] ?? null;
            $statusMerah = $partai['status_penampilan_seni_merah'] ?? null;

            if ($statusBiru !== null && $statusBiru !== 'belum_tampil' && $statusBiru !== 'sudah_tampil') {
                $idPenampilan = (int) $partai['id_penampilan_seni_biru'];
            } elseif ($statusMerah !== null && $statusMerah !== 'belum_tampil' && $statusMerah !== 'sudah_tampil') {
                $idPenampilan = (int) $partai['id_penampilan_seni_merah'];
            }
        }

        if (!$idPenampilan) {
            return null;
        }

        $db = db_connect();

        return $db->table('penampilan_seni ps')
            ->select('ps.id_penampilan_seni, ps.id_kelompok_peserta_seni,
                    ps.nilai_akhir, ps.status_penampilan,
                    kps.id_kompetisi_seni,
                    ks.nomor_pool,
                    sks.nama_seni, sks.jenis_seni,
                    ku.nama_kategori_usia, ku.jenis_kelamin,
                    kg.nama_kontingen,
                    (SELECT GROUP_CONCAT(pendaftar.nama_pendaftar SEPARATOR " - ")
                     FROM pendaftar
                     JOIN peserta_seni ON peserta_seni.id_pendaftar = pendaftar.id_pendaftar
                     WHERE peserta_seni.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni
                    ) as anggota_kelompok_peserta_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->join('kontingen kg', 'kg.id_kontingen = kps.id_kontingen', 'left')
            ->where('ps.id_penampilan_seni', $idPenampilan)
            ->get()
            ->getRowArray() ?: null;
    }
}
