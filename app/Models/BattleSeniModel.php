<?php

namespace App\Models;

use CodeIgniter\Model;

class BattleSeniModel extends Model
{
    protected $table            = 'battle_seni';
    protected $primaryKey       = 'id_battle_seni';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_kompetisi_seni', 'nomor_battle', 'babak',
        'nomor_battle_selanjutnya', 'id_penampilan_seni_biru',
        'id_penampilan_seni_merah', 'id_penampilan_seni_pemenang',
        'jenis_kemenangan', 'keterangan',
    ];

    public function setPemenang(int $idBattle, int $idPenampilanPemenang, string $jenisKemenangan = 'poin'): bool
    {
        $battle = $this->find($idBattle);
        if (! $battle) {
            return false;
        }

        $this->update($idBattle, [
            'id_penampilan_seni_pemenang' => $idPenampilanPemenang,
            'jenis_kemenangan'            => $jenisKemenangan,
        ]);

        if (! empty($battle['nomor_battle_selanjutnya'])) {
            $nextBattle = $this->where('id_kompetisi_seni', $battle['id_kompetisi_seni'])
                               ->where('nomor_battle', $battle['nomor_battle_selanjutnya'])
                               ->first();

            if ($nextBattle) {
                if ($nextBattle['id_penampilan_seni_biru'] === null) {
                    $this->update($nextBattle['id_battle_seni'], [
                        'id_penampilan_seni_biru' => $idPenampilanPemenang,
                    ]);
                } elseif ($nextBattle['id_penampilan_seni_merah'] === null) {
                    $this->update($nextBattle['id_battle_seni'], [
                        'id_penampilan_seni_merah' => $idPenampilanPemenang,
                    ]);
                }
            }
        }

        return true;
    }
}
