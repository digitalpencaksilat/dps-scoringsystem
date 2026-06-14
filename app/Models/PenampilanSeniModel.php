<?php

namespace App\Models;

use CodeIgniter\Model;

class PenampilanSeniModel extends Model
{
    protected $table            = 'penampilan_seni';
    protected $primaryKey       = 'id_penampilan_seni';
    protected $returnType       = 'object';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'id_kelompok_peserta_seni', 'babak', 'waktu_tampil',
        'nilai_akhir', 'catatan_nilai_sama', 'akses_penilaian',
        'status_penampilan', 'diskualifikasi',
    ];

    /**
     * Get penampilan seni yang sedang berlangsung di gelanggang.
     * Parity legacy: Detail_jadwal_seni_model::get_penampilan_seni_berlangsung()
     * Handles both pool (id_penampilan_seni) and battle (id_battle_seni) scenarios.
     */
    public function getAktif(int $idGelanggang): ?object
    {
        $db = \Config\Database::connect();

        // Cari detail_jadwal_seni yang aktif di gelanggang ini
        // Case 1: Pool — id_penampilan_seni langsung (status_penampilan di penampilan_seni)
        $poolRow = $db->table('detail_jadwal_seni djs')
            ->select('djs.id_penampilan_seni, djs.id_battle_seni, djs.nomor_partai')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->where('js.id_gelanggang', $idGelanggang)
            ->where('djs.id_penampilan_seni IS NOT NULL')
            ->whereNotIn('ps.status_penampilan', ['belum_tampil', 'sudah_tampil'])
            ->orderBy('djs.nomor_partai', 'ASC')
            ->limit(1)
            ->get()->getRow();

        if ($poolRow !== null) {
            return $this->getFullPenampilan((int) $poolRow->id_penampilan_seni);
        }

        // Case 2: Battle — id_battle_seni (cek status_penampilan di masing-masing sudut)
        $battleRow = $db->table('detail_jadwal_seni djs')
            ->select('djs.id_battle_seni, bs.id_penampilan_seni_biru, bs.id_penampilan_seni_merah')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')
            ->where('js.id_gelanggang', $idGelanggang)
            ->where('djs.id_battle_seni IS NOT NULL')
            ->get()->getResult();

        foreach ($battleRow as $row) {
            // Cek sudut biru
            if ($row->id_penampilan_seni_biru !== null) {
                $statusBiru = $db->table('penampilan_seni')
                    ->select('status_penampilan')
                    ->where('id_penampilan_seni', $row->id_penampilan_seni_biru)
                    ->get(1)->getRow();
                if ($statusBiru !== null && !in_array($statusBiru->status_penampilan, ['belum_tampil', 'sudah_tampil'], true)) {
                    return $this->getFullPenampilan((int) $row->id_penampilan_seni_biru);
                }
            }
            // Cek sudut merah
            if ($row->id_penampilan_seni_merah !== null) {
                $statusMerah = $db->table('penampilan_seni')
                    ->select('status_penampilan')
                    ->where('id_penampilan_seni', $row->id_penampilan_seni_merah)
                    ->get(1)->getRow();
                if ($statusMerah !== null && !in_array($statusMerah->status_penampilan, ['belum_tampil', 'sudah_tampil'], true)) {
                    return $this->getFullPenampilan((int) $row->id_penampilan_seni_merah);
                }
            }
        }

        return null;
    }

    /**
     * Get full penampilan seni object with all joins.
     */
    private function getFullPenampilan(int $idPenampilanSeni): ?object
    {
        return $this->getFullPenampilanPublic($idPenampilanSeni);
    }

    /**
     * Public accessor for getFullPenampilan — used by controller after edit.
     */
    public function getFullPenampilanPublic(int $idPenampilanSeni): ?object
    {
        return $this->select("penampilan_seni.*, kelompok_peserta_seni.*,
                    kontingen.nama_kontingen, kompetisi_seni.nomor_pool,
                    sub_kategori_seni.jenis_seni, sub_kategori_seni.nama_seni,
                    sub_kategori_seni.sistem_penampilan, sub_kategori_seni.format_penilaian,
                    kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin,
                    penampilan_seni.catatan_nilai_sama,
                    (SELECT GROUP_CONCAT(CONCAT_WS(' ', pendaftar.nama_pendaftar) SEPARATOR ' ,<br> ')
                        FROM pendaftar
                        JOIN peserta_seni ON peserta_seni.id_pendaftar = pendaftar.id_pendaftar
                        WHERE peserta_seni.id_kelompok_peserta_seni = kelompok_peserta_seni.id_kelompok_peserta_seni) as anggota_kelompok_peserta_seni", false)
            ->join('kelompok_peserta_seni', 'kelompok_peserta_seni.id_kelompok_peserta_seni = penampilan_seni.id_kelompok_peserta_seni')
            ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen', 'left')
            ->join('kompetisi_seni', 'kompetisi_seni.id_kompetisi_seni = kelompok_peserta_seni.id_kompetisi_seni')
            ->join('sub_kategori_seni', 'sub_kategori_seni.id_sub_kategori_seni = kompetisi_seni.id_sub_kategori_seni')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = sub_kategori_seni.id_kategori_lomba', 'left')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia', 'left')
            ->where('penampilan_seni.id_penampilan_seni', $idPenampilanSeni)
            ->first();
    }

    public function setStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status_penampilan' => $status]);
    }

    public function setStatusDanWaktu(int $id, string $status, int $waktu): bool
    {
        return $this->update($id, [
            'status_penampilan' => $status,
            'waktu_tampil'      => $waktu,
        ]);
    }

    public function selesaikan(int $id, string $nilaiAkhir = '0'): bool
    {
        return $this->update($id, [
            'status_penampilan' => 'sudah_tampil',
            'akses_penilaian'   => 'ditutup',
            'nilai_akhir'       => $nilaiAkhir,
        ]);
    }

    public function diskualifikasi(int $id): bool
    {
        return $this->update($id, [
            'diskualifikasi'    => 1,
            'status_penampilan' => 'sudah_tampil',
            'akses_penilaian'   => 'ditutup',
            'nilai_akhir'       => '0',
        ]);
    }

    public function batalkanDiskualifikasi(int $id): bool
    {
        return $this->update($id, [
            'diskualifikasi'    => 0,
            'status_penampilan' => 'standby',
        ]);
    }
}
