<?php

namespace App\Models;

use CodeIgniter\Model;

class GelanggangModel extends Model
{
    protected $table            = 'gelanggang';
    protected $primaryKey       = 'id_gelanggang';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'nomor_gelanggang', 'nama_gelanggang', 'keterangan',
        'tipe_gong', 'beep_alarm', 'tipe_voice_over',
    ];
}
