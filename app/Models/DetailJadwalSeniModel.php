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
}
