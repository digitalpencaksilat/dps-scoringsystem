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

    public function getAktif(int $idGelanggang): ?object
    {
        return $this->select('penampilan_seni.*, kelompok_peserta_seni.*,
                    kontingen.nama_kontingen, kompetisi_seni.nomor_pool,
                    detail_jadwal_seni.nomor_partai, detail_jadwal_seni.id_jadwal_seni,
                    detail_jadwal_seni.id_battle_seni,
                    sub_kategori_seni.jenis_seni, sub_kategori_seni.nama_seni,
                    sub_kategori_seni.sistem_penampilan, sub_kategori_seni.format_penilaian,
                    kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin')
            ->join('kelompok_peserta_seni', 'kelompok_peserta_seni.id_kelompok_peserta_seni = penampilan_seni.id_kelompok_peserta_seni')
            ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen', 'left')
            ->join('kompetisi_seni', 'kompetisi_seni.id_kompetisi_seni = kelompok_peserta_seni.id_kompetisi_seni')
            ->join('sub_kategori_seni', 'sub_kategori_seni.id_sub_kategori_seni = kompetisi_seni.id_sub_kategori_seni')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = sub_kategori_seni.id_kategori_lomba', 'left')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia', 'left')
            ->join('detail_jadwal_seni', 'detail_jadwal_seni.id_penampilan_seni = penampilan_seni.id_penampilan_seni')
            ->join('jadwal_seni', 'jadwal_seni.id_jadwal_seni = detail_jadwal_seni.id_jadwal_seni')
            ->where('jadwal_seni.id_gelanggang', $idGelanggang)
            ->whereNotIn('penampilan_seni.status_penampilan', ['belum_tampil', 'sudah_tampil'])
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
