<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model broadcast_graphic — kontrol scene overlay siaran per gelanggang.
 * Skema legacy db_sudinpora — tanpa timestamp.
 */
class BroadcastGraphicModel extends Model
{
    protected $table            = 'broadcast_graphic';
    protected $primaryKey       = 'id_broadcast_graphic';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_gelanggang',
        'jenis',
        'scene',
        'autorefresh',
        'status',
    ];

    /**
     * Semua scene untuk satu gelanggang + jenis (tanding/seni).
     */
    public function getByGelanggangJenis(int $idGelanggang, string $jenis): array
    {
        return $this->where('id_gelanggang', $idGelanggang)
            ->where('jenis', $jenis)
            ->orderBy('id_broadcast_graphic', 'ASC')
            ->findAll();
    }

    /**
     * Scene yang sedang aktif (status='active') untuk overlay.
     */
    public function getSceneAktif(int $idGelanggang, string $jenis): ?object
    {
        return $this->where('id_gelanggang', $idGelanggang)
            ->where('jenis', $jenis)
            ->where('status', 'active')
            ->orderBy('id_broadcast_graphic', 'DESC')
            ->first();
    }

    /**
     * Aktifkan satu scene (set yang lain non-aktif untuk jenis+gelanggang sama).
     */
    public function aktifkanScene(int $idGelanggang, string $jenis, string $scene): bool
    {
        $this->where('id_gelanggang', $idGelanggang)
            ->where('jenis', $jenis)
            ->set('status', 'inactive')
            ->update();

        return $this->where('id_gelanggang', $idGelanggang)
            ->where('jenis', $jenis)
            ->where('scene', $scene)
            ->set('status', 'active')
            ->update();
    }
}
