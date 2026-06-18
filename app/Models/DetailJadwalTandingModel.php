<?php

namespace App\Models;

use CodeIgniter\Model;

class DetailJadwalTandingModel extends Model
{
    protected $table            = 'detail_jadwal_tanding';
    protected $primaryKey       = 'id_detail_jadwal_tanding';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'nomor_partai', 'id_jadwal_tanding', 'id_pertandingan',
        'keterangan', 'status_partai',
    ];

    public function getPartaiByJadwal(int $idJadwal): array
    {
        return $this->select('detail_jadwal_tanding.*, pertandingan.*,
                    peserta_tanding_merah.id_peserta_tanding as id_peserta_merah,
                    peserta_tanding_biru.id_peserta_tanding as id_peserta_biru,
                    pendaftar_merah.nama_pendaftar as nama_atlet_merah,
                    pendaftar_biru.nama_pendaftar as nama_atlet_biru,
                    kontingen_merah.nama_kontingen as kontingen_merah,
                    kontingen_biru.nama_kontingen as kontingen_biru,
                    kelas_tanding.label as label_kelas,
                    kelas_tanding.jumlah_ronde, kelas_tanding.waktu_per_ronde,
                    kelas_tanding.waktu_istirahat')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan', 'left')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding', 'left')
            ->join('peserta_tanding as peserta_tanding_merah', 'peserta_tanding_merah.id_peserta_tanding = pertandingan.id_atlet_merah', 'left')
            ->join('peserta_tanding as peserta_tanding_biru', 'peserta_tanding_biru.id_peserta_tanding = pertandingan.id_atlet_biru', 'left')
            ->join('pendaftar as pendaftar_merah', 'pendaftar_merah.id_pendaftar = peserta_tanding_merah.id_pendaftar', 'left')
            ->join('pendaftar as pendaftar_biru', 'pendaftar_biru.id_pendaftar = peserta_tanding_biru.id_pendaftar', 'left')
            ->join('kontingen as kontingen_merah', 'kontingen_merah.id_kontingen = pendaftar_merah.id_kontingen', 'left')
            ->join('kontingen as kontingen_biru', 'kontingen_biru.id_kontingen = pendaftar_biru.id_kontingen', 'left')
            ->where('detail_jadwal_tanding.id_jadwal_tanding', $idJadwal)
            ->orderBy('detail_jadwal_tanding.nomor_partai', 'ASC')
            ->findAll();
    }

    public function getPartaiTetangga(int $idJadwal, int $nomorPartai): array
    {
        $prev = $this->where('id_jadwal_tanding', $idJadwal)
                     ->where('nomor_partai <', $nomorPartai)
                     ->orderBy('nomor_partai', 'DESC')
                     ->first();

        $next = $this->where('id_jadwal_tanding', $idJadwal)
                     ->where('nomor_partai >', $nomorPartai)
                     ->orderBy('nomor_partai', 'ASC')
                     ->first();

        return ['prev' => $prev, 'next' => $next];
    }

    /**
     * Ambil partai tanding yang sedang berlangsung di sebuah gelanggang.
     * Parity: legacy Detail_jadwal_tanding_model::get_pertandingan_berlangsung().
     *
     * Status berlangsung = status_pertandingan != 'belum_dimulai' AND != 'selesai'.
     */
    public function getPertandinganBerlangsungByGelanggang(int $idGelanggang): ?array
    {
        return $this->select('detail_jadwal_tanding.id_detail_jadwal_tanding,
                    detail_jadwal_tanding.nomor_partai,
                    pertandingan.id_pertandingan,
                    pertandingan.status_pertandingan,
                    pertandingan.babak,
                    pertandingan.skor_biru,
                    pertandingan.skor_merah,
                    pertandingan.data_waktu,
                    kelas_tanding.label,
                    kategori_usia.nama_kategori_usia,
                    kategori_usia.jenis_kelamin,
                    pendaftar_biru.nama_pendaftar as nama_atlet_biru,
                    kontingen_biru.nama_kontingen as nama_kontingen_biru,
                    pendaftar_merah.nama_pendaftar as nama_atlet_merah,
                    kontingen_merah.nama_kontingen as nama_kontingen_merah')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan', 'left')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding', 'left')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding', 'left')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba', 'left')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia', 'left')
            ->join('jadwal_tanding', 'jadwal_tanding.id_jadwal_tanding = detail_jadwal_tanding.id_jadwal_tanding', 'left')
            ->join('peserta_tanding as pt_biru', 'pt_biru.id_peserta_tanding = pertandingan.id_atlet_biru', 'left')
            ->join('pendaftar as pendaftar_biru', 'pendaftar_biru.id_pendaftar = pt_biru.id_pendaftar', 'left')
            ->join('kontingen as kontingen_biru', 'kontingen_biru.id_kontingen = pendaftar_biru.id_kontingen', 'left')
            ->join('peserta_tanding as pt_merah', 'pt_merah.id_peserta_tanding = pertandingan.id_atlet_merah', 'left')
            ->join('pendaftar as pendaftar_merah', 'pendaftar_merah.id_pendaftar = pt_merah.id_pendaftar', 'left')
            ->join('kontingen as kontingen_merah', 'kontingen_merah.id_kontingen = pendaftar_merah.id_kontingen', 'left')
            ->where('jadwal_tanding.id_gelanggang', $idGelanggang)
            ->where('pertandingan.status_pertandingan !=', 'belum_dimulai')
            ->where('pertandingan.status_pertandingan !=', 'selesai')
            ->orderBy('detail_jadwal_tanding.nomor_partai', 'ASC')
            ->first();
    }
}
