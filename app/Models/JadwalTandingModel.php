<?php

namespace App\Models;

use CodeIgniter\Model;

class JadwalTandingModel extends Model
{
    protected $table            = 'jadwal_tanding';
    protected $primaryKey       = 'id_jadwal_tanding';
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
