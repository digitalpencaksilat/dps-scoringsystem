<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model pertandingan (1 partai tanding).
 * Skema legacy db_sudinpora — tanpa timestamp.
 */
class PertandinganModel extends Model
{
    protected $table            = 'pertandingan';
    protected $primaryKey       = 'id_pertandingan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nomor_pertandingan',
        'nomor_pertandingan_selanjutnya',
        'id_kompetisi_tanding',
        'id_atlet_merah',
        'id_atlet_biru',
        'id_pemenang',
        'id_official_merah',
        'id_official_biru',
        'babak',
        'ronde_pertandingan',
        'skor_merah',
        'skor_biru',
        'ringkasan_nilai',
        'keterangan',
        'data_waktu',
        'jenis_kemenangan',
        'status_pertandingan',
        'berat_biru',
        'berat_merah',
        'hasil_timbang_biru',
        'hasil_timbang_merah',
        'ttd_biru',
        'ttd_merah',
        'keterangan_penimbangan',
        'waktu_penimbangan',
    ];

    /**
     * Pertandingan yang sedang berlangsung di sebuah gelanggang.
     *
     * Parity dengan legacy Detail_jadwal_tanding_model::get_pertandingan_berlangsung():
     * base table detail_jadwal_tanding -> pertandingan -> jadwal_tanding -> gelanggang,
     * filter status != belum_dimulai dan != selesai.
     *
     * Versi ringan (kolom inti pertandingan). Detail atlet lengkap (nama/kontingen/foto)
     * ditarik terpisah saat Fase 3 agar tidak membebani query standby.
     */
    public function getPertandinganBerlangsung(int $idGelanggang): ?object
    {
        return $this->db->table('detail_jadwal_tanding')
            ->select('pertandingan.*, detail_jadwal_tanding.nomor_partai, detail_jadwal_tanding.id_jadwal_tanding,
                jadwal_tanding.id_gelanggang, gelanggang.nama_gelanggang,
                kelas_tanding.id_kelas_tanding, kelas_tanding.label, kelas_tanding.format_penilaian,
                kelas_tanding.jumlah_ronde, kelas_tanding.waktu_per_ronde, kelas_tanding.waktu_istirahat,
                kategori_lomba.id_kategori_lomba, kategori_lomba.nama_kategori_lomba,
                kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia')
            ->join('jadwal_tanding', 'jadwal_tanding.id_jadwal_tanding = detail_jadwal_tanding.id_jadwal_tanding')
            ->join('gelanggang', 'gelanggang.id_gelanggang = jadwal_tanding.id_gelanggang')
            ->where('jadwal_tanding.id_gelanggang', $idGelanggang)
            ->whereNotIn('pertandingan.status_pertandingan', ['belum_dimulai', 'selesai'])
            ->get()
            ->getRow();
    }

    /**
     * Ambil satu pertandingan by-id dengan full join (kelas, kategori, gelanggang, nomor_partai).
     * Untuk halaman hasil pertandingan — parity legacy Detail_jadwal_tanding_model->find().
     */
    public function getPertandinganLengkap(int $idPertandingan): ?object
    {
        return $this->db->table('detail_jadwal_tanding')
            ->select('pertandingan.*, detail_jadwal_tanding.nomor_partai, detail_jadwal_tanding.id_jadwal_tanding,
                jadwal_tanding.id_gelanggang, gelanggang.nama_gelanggang,
                kelas_tanding.id_kelas_tanding, kelas_tanding.label, kelas_tanding.format_penilaian,
                kelas_tanding.jumlah_ronde, kelas_tanding.waktu_per_ronde, kelas_tanding.waktu_istirahat,
                kategori_lomba.id_kategori_lomba, kategori_lomba.nama_kategori_lomba,
                kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia')
            ->join('jadwal_tanding', 'jadwal_tanding.id_jadwal_tanding = detail_jadwal_tanding.id_jadwal_tanding')
            ->join('gelanggang', 'gelanggang.id_gelanggang = jadwal_tanding.id_gelanggang')
            ->where('pertandingan.id_pertandingan', $idPertandingan)
            ->get()
            ->getRow();
    }

    /**
     * Ambil data pemenang (peserta_tanding + pendaftar + kontingen) dari pertandingan.
     * Parity legacy: Peserta_tanding_model->find($pertandingan->id_pemenang).
     */
    public function getPemenangPertandingan(int $idPertandingan): ?object
    {
        $pertandingan = $this->find($idPertandingan);
        if (!$pertandingan || empty($pertandingan->id_pemenang)) {
            return null;
        }

        return $this->db->table('peserta_tanding')
            ->select('peserta_tanding.*, pendaftar.nama_pendaftar, pendaftar.foto, kontingen.nama_kontingen')
            ->join('pendaftar', 'pendaftar.id_pendaftar = peserta_tanding.id_pendaftar')
            ->join('kontingen', 'kontingen.id_kontingen = pendaftar.id_kontingen')
            ->where('peserta_tanding.id_peserta_tanding', $pertandingan->id_pemenang)
            ->get()
            ->getRow();
    }

    /**
     * Ambil data atlet (pendaftar + kontingen) pada satu sudut pertandingan.
     * Parity legacy Pertandingan_model::get_atlet_pertandingan().
     *
     * @param string $sudut 'merah'|'biru'
     */
    public function getAtletPertandingan(int $idPertandingan, string $sudut = 'merah'): ?object
    {
        $kolomSudut = $sudut === 'merah' ? 'pertandingan.id_atlet_merah' : 'pertandingan.id_atlet_biru';

        return $this->db->table('pendaftar')
            ->select('pendaftar.*, kontingen.nama_kontingen')
            ->join('peserta_tanding', 'peserta_tanding.id_pendaftar = pendaftar.id_pendaftar')
            ->join('pertandingan', "{$kolomSudut} = peserta_tanding.id_peserta_tanding")
            ->join('kontingen', 'pendaftar.id_kontingen = kontingen.id_kontingen')
            ->where('pertandingan.id_pertandingan', $idPertandingan)
            ->get()
            ->getRow();
    }

    /**
     * Daftar partai dalam satu gelanggang (untuk jump-to-match & standby).
     * Diurutkan nomor_partai. Parity sumber data legacy timer_tanding (jump to match).
     */
    public function getDaftarPartaiGelanggang(int $idGelanggang): array
    {
        return $this->db->table('detail_jadwal_tanding')
            ->select('pertandingan.id_pertandingan, pertandingan.nomor_pertandingan, pertandingan.status_pertandingan,
                pertandingan.babak, pertandingan.skor_merah, pertandingan.skor_biru,
                pertandingan.id_atlet_merah, pertandingan.id_atlet_biru,
                detail_jadwal_tanding.nomor_partai,
                kelas_tanding.label as nama_kelas, kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin,
                pm.nama_pendaftar as nama_atlet_merah, pb.nama_pendaftar as nama_atlet_biru')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia')
            ->join('jadwal_tanding', 'jadwal_tanding.id_jadwal_tanding = detail_jadwal_tanding.id_jadwal_tanding')
            ->join('peserta_tanding as ptm', 'ptm.id_peserta_tanding = pertandingan.id_atlet_merah', 'left')
            ->join('pendaftar as pm', 'pm.id_pendaftar = ptm.id_pendaftar', 'left')
            ->join('peserta_tanding as ptb', 'ptb.id_peserta_tanding = pertandingan.id_atlet_biru', 'left')
            ->join('pendaftar as pb', 'pb.id_pendaftar = ptb.id_pendaftar', 'left')
            ->where('jadwal_tanding.id_gelanggang', $idGelanggang)
            ->orderBy('detail_jadwal_tanding.nomor_partai * 1', 'ASC', false)
            ->get()
            ->getResult();
    }

    /**
     * Partai sebelum/sesudah dalam satu jadwal (untuk navigasi prev/next).
     * Parity legacy timer_tanding() pertandingan_selanjutnya / pertandingan_sebelumnya.
     *
     * @param string $arah 'next'|'prev'
     */
    public function getPartaiTetangga(int $idJadwalTanding, int $nomorPartai, string $arah): ?object
    {
        $builder = $this->db->table('detail_jadwal_tanding')
            ->select('pertandingan.id_pertandingan, pertandingan.status_pertandingan, detail_jadwal_tanding.nomor_partai')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan')
            ->where('detail_jadwal_tanding.id_jadwal_tanding', $idJadwalTanding);

        if ($arah === 'next') {
            $builder->where('detail_jadwal_tanding.nomor_partai * 1 > ' . (int)$nomorPartai, null, false)
                ->orderBy('detail_jadwal_tanding.nomor_partai * 1', 'ASC', false);
        } else {
            $builder->where('detail_jadwal_tanding.nomor_partai * 1 < ' . (int)$nomorPartai, null, false)
                ->orderBy('detail_jadwal_tanding.nomor_partai * 1', 'DESC', false);
        }

        return $builder->get(1)->getRow();
    }

    /**
     * Bentuk objek data_waktu dari konfigurasi ronde.
     * Parity legacy Pertandingan_model::create_data_waktu() —
     * format { "1":[null, ronde_ms, istirahat_ms], "2":[...], "3":[...] } dalam milidetik.
     */
    public function createDataWaktu(int $jumlahRonde, int $waktuPerRondeDetik, int $waktuIstirahatDetik): array
    {
        $rondeMs     = $waktuPerRondeDetik * 1000;
        $istirahatMs = $waktuIstirahatDetik * 1000;

        $data = [];
        for ($r = 1; $r <= max(1, $jumlahRonde); $r++) {
            $data[(string) $r] = [null, $rondeMs, $istirahatMs];
        }

        return $data;
    }

    /**
     * Ubah konfigurasi waktu sesuai mode (parity legacy ubah_waktu_tanding).
     * Mode: pertandingan_ini | kelas_ini | kategori_lomba_ini | gelanggang_ini.
     */
    public function ubahWaktu(
        int $idPertandingan,
        string $mode,
        int $idKelasTanding,
        int $idKategoriLomba,
        int $idGelanggang,
        int $jumlahRonde,
        int $waktuPerRondeDetik,
        int $waktuIstirahatDetik
    ): bool {
        $dataWaktu     = $this->createDataWaktu($jumlahRonde, $waktuPerRondeDetik, $waktuIstirahatDetik);
        $dataWaktuJson = json_encode($dataWaktu, JSON_NUMERIC_CHECK);

        // Kumpulkan id_pertandingan target sesuai mode.
        switch ($mode) {
            case 'pertandingan_ini':
                $targetIds = [$idPertandingan];
                break;
            case 'kelas_ini':
                $targetIds = $this->idPertandinganByJoin('kelas_tanding.id_kelas_tanding', $idKelasTanding);
                break;
            case 'kategori_lomba_ini':
                $targetIds = $this->idPertandinganByJoin('kategori_lomba.id_kategori_lomba', $idKategoriLomba);
                break;
            case 'gelanggang_ini':
                $targetIds = array_map(
                    static fn ($r) => (int) $r->id_pertandingan,
                    $this->getDaftarPartaiGelanggang($idGelanggang)
                );
                break;
            default:
                return false;
        }

        if (empty($targetIds)) {
            return true;
        }

        // Update data_waktu untuk semua partai target.
        $this->db->table('pertandingan')
            ->whereIn('id_pertandingan', $targetIds)
            ->update(['data_waktu' => $dataWaktuJson]);

        // Simpan konfigurasi ke kelas_tanding (parity legacy Kelas_tanding_model::update).
        $this->db->table('kelas_tanding')
            ->where('id_kelas_tanding', $idKelasTanding)
            ->update([
                'jumlah_ronde'    => $jumlahRonde,
                'waktu_per_ronde' => $waktuPerRondeDetik,
                'waktu_istirahat' => $waktuIstirahatDetik,
            ]);

        return true;
    }

    /**
     * Helper: id_pertandingan berdasarkan kolom join kelas/kategori.
     */
    private function idPertandinganByJoin(string $kolom, int $nilai): array
    {
        $rows = $this->db->table('pertandingan')
            ->select('pertandingan.id_pertandingan')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
            ->where($kolom, $nilai)
            ->get()
            ->getResult();

        return array_map(static fn ($r) => (int) $r->id_pertandingan, $rows);
    }

    /**
     * Set status + data_waktu (toggle timer). Parity legacy toggle_timer_tanding().
     */
    public function setStatusDanWaktu(int $idPertandingan, string $status, ?string $dataWaktu = null): bool
    {
        $data = ['status_pertandingan' => $status];
        if ($dataWaktu !== null) {
            $data['data_waktu'] = $dataWaktu;
        }

        return $this->update($idPertandingan, $data);
    }

    /**
     * Pindah ronde aktif. Parity legacy pindah_ronde_tanding().
     */
    public function setRonde(int $idPertandingan, string $ronde): bool
    {
        return $this->update($idPertandingan, ['ronde_pertandingan' => $ronde]);
    }

    /**
     * Set pemenang & jenis kemenangan, status selesai.
     * Parity bagian inti finalisasi partai (tanpa bracket-advancement,
     * yang berada di domain penjadwalan — lihat catatan Fase 5 di docs).
     */
    public function selesaikanPertandingan(int $idPertandingan, ?int $idPemenang, string $jenisKemenangan): bool
    {
        return $this->update($idPertandingan, [
            'id_pemenang'         => $idPemenang,
            'jenis_kemenangan'    => $jenisKemenangan,
            'status_pertandingan' => 'selesai',
        ]);
    }

    /**
     * Get verifikasi yang sedang berlangsung (status = berlangsung).
     * Parity legacy: verifikasi_pertandingan table.
     */
    public function getVerifikasiBerlangsung(int $idPertandingan): ?object
    {
        return $this->db->table('verifikasi_pertandingan')
            ->where('id_pertandingan', $idPertandingan)
            ->where('status', 'berlangsung')
            ->orderBy('id_verifikasi_pertandingan', 'DESC')
            ->get(1)
            ->getRow();
    }

    /**
     * Get riwayat verifikasi (selesai) untuk satu pertandingan.
     * Parity legacy: verifikasi history di KP tanding view.
     */
    public function getRiwayatVerifikasi(int $idPertandingan): array
    {
        $rows = $this->db->table('verifikasi_pertandingan')
            ->where('id_pertandingan', $idPertandingan)
            ->where('status !=', 'berlangsung')
            ->orderBy('id_verifikasi_pertandingan', 'ASC')
            ->get()
            ->getResult();

        // Attach jawaban per juri
        foreach ($rows as &$row) {
            $jawabanRows = $this->db->table('jawaban_verifikasi_pertandingan')
                ->where('id_verifikasi_pertandingan', $row->id_verifikasi_pertandingan)
                ->orderBy('id_perangkat_pertandingan', 'ASC')
                ->get()
                ->getResult();

            $row->jawaban = array_map(fn($j) => $j->jawaban ?? '-', $jawabanRows);
            $row->hasil = $row->hasil_verifikasi ?? '-';
            $row->ronde = $row->ronde_pertandingan ?? '-';
        }

        return $rows;
    }

    /**
     * Get jawaban verifikasi dari satu juri tertentu.
     * Parity legacy: jawaban_verifikasi_pertandingan table.
     */
    public function getJawabanVerifikasiJuri(int $idVerifikasi, int $idPerangkat): ?object
    {
        return $this->db->table('jawaban_verifikasi_pertandingan')
            ->where('id_verifikasi_pertandingan', $idVerifikasi)
            ->where('id_perangkat_pertandingan', $idPerangkat)
            ->get(1)
            ->getRow();
    }
}
