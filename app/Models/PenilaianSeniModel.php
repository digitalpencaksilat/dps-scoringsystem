<?php

namespace App\Models;

use CodeIgniter\Model;

class PenilaianSeniModel extends Model
{
    protected $table            = 'penilaian_seni';
    protected $primaryKey       = 'id_penilaian_seni';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_penampilan_seni', 'id_perangkat_pertandingan',
        'penilaian', 'nilai_akhir_per_juri', 'terpilih', 'status_ready',
    ];

    public function getByPenampilan(int $idPenampilan): array
    {
        return $this->where('id_penampilan_seni', $idPenampilan)->findAll();
    }

    public function getTerpilih(int $idPenampilan): array
    {
        return $this->where('id_penampilan_seni', $idPenampilan)
                    ->where('terpilih', 1)
                    ->findAll();
    }

    public function hapusByPenampilan(int $idPenampilan): bool
    {
        return $this->where('id_penampilan_seni', $idPenampilan)->delete();
    }

    public function buatPenilaian(int $idPenampilan, int $idPerangkat, string $formatJson): bool
    {
        return (bool) $this->insert([
            'id_penampilan_seni'       => $idPenampilan,
            'id_perangkat_pertandingan' => $idPerangkat,
            'penilaian'                => $formatJson,
            'terpilih'                 => 1,
        ]);
    }
}
