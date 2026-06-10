<?php

namespace App\Models;

use CodeIgniter\Model;

class KelasTandingModel extends Model
{
    protected $table            = 'kelas_tanding';
    protected $primaryKey       = 'id_kelas_tanding';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_kategori_lomba', 'berat_minimal', 'berat_maksimal',
        'jumlah_ronde', 'waktu_per_ronde', 'waktu_istirahat',
        'juara_tiga_bersama', 'label', 'format_penilaian',
        'biaya_pendaftaran_dn', 'biaya_pendaftaran_ln', 'keterangan',
    ];
}
