<?php

namespace App\Models;

use CodeIgniter\Model;

class KompetisiTandingModel extends Model
{
    protected $table            = 'kompetisi_tanding';
    protected $primaryKey       = 'id_kompetisi_tanding';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_kelas_tanding', 'max_peserta', 'nomor_pool',
        'bagan_pertandingan', 'perhitungan_medali', 'keterangan',
    ];

    public function getWithKelas(int $id): ?array
    {
        return $this->select('kompetisi_tanding.*, kelas_tanding.*')
                    ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                    ->where('kompetisi_tanding.id_kompetisi_tanding', $id)
                    ->first();
    }
}
