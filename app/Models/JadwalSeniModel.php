<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalSeniModel extends Model
{
    protected $table            = 'jadwal_seni';
    protected $primaryKey       = 'id_jadwal_seni';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_gelanggang', 'tanggal', 'jam_mulai', 'jam_selesai',
        'keterangan', 'nama_file',
    ];

    public function getByGelanggang(int $idGelanggang): array
    {
        return $this->where('id_gelanggang', $idGelanggang)
                    ->orderBy('tanggal', 'ASC')
                    ->orderBy('jam_mulai', 'ASC')
                    ->findAll();
    }
}
