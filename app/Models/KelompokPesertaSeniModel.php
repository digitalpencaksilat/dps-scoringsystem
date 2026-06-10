<?php

namespace App\Models;

use CodeIgniter\Model;

class KelompokPesertaSeniModel extends Model
{
    protected $table            = 'kelompok_peserta_seni';
    protected $primaryKey       = 'id_kelompok_peserta_seni';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_kompetisi_seni', 'id_kontingen', 'id_pembayaran',
        'status', 'keterangan', 'nomor_undi',
    ];

    public function getByKompetisi(int $idKompetisi): array
    {
        return $this->select('kelompok_peserta_seni.*, kontingen.nama_kontingen')
                    ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen')
                    ->where('kelompok_peserta_seni.id_kompetisi_seni', $idKompetisi)
                    ->orderBy('kelompok_peserta_seni.nomor_undi', 'ASC')
                    ->findAll();
    }
}
