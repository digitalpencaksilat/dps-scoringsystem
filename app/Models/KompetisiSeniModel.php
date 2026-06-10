<?php

namespace App\Models;

use CodeIgniter\Model;

class KompetisiSeniModel extends Model
{
    protected $table            = 'kompetisi_seni';
    protected $primaryKey       = 'id_kompetisi_seni';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_sub_kategori_seni', 'nomor_pool', 'max_peserta',
        'bagan_battle_seni', 'perhitungan_medali', 'keterangan',
    ];

    public function getWithSubKategori(int $id): ?array
    {
        return $this->select('kompetisi_seni.*, sub_kategori_seni.*,
                    kategori_lomba.peraturan_pertandingan,
                    kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin')
            ->join('sub_kategori_seni', 'sub_kategori_seni.id_sub_kategori_seni = kompetisi_seni.id_sub_kategori_seni')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = sub_kategori_seni.id_kategori_lomba', 'left')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia', 'left')
            ->where('kompetisi_seni.id_kompetisi_seni', $id)
            ->first();
    }
}
