<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\GelanggangModel;
use App\Models\DetailJadwalTandingModel;
use App\Models\DetailJadwalSeniModel;

class Monitoring extends BaseController
{
    /**
     * Menu pilihan monitoring.
     * Parity: legacy users/Monitoring::index() + view pilihan_monitoring.php.
     */
    public function index(): \CodeIgniter\HTTP\RedirectResponse
    {
        return redirect()->to('/monitoring/live-jadwal');
    }

    /**
     * Monitoring Partai — tampilkan kartu per-gelanggang dengan partai
     * tanding/seni yang sedang berlangsung. Auto-refresh 30s via JS.
     *
     * Parity: legacy users/Monitoring::live_jadwal.
     */
    public function liveJadwal(): string
    {
        $gelanggangModel = new GelanggangModel();
        $tandingModel    = new DetailJadwalTandingModel();
        $seniModel       = new DetailJadwalSeniModel();

        $gelanggang = $gelanggangModel->orderBy('nomor_gelanggang', 'ASC')->findAll();

        $dataPartai = [];
        foreach ($gelanggang as $glg) {
            $idGlg = (int) $glg['id_gelanggang'];

            // Cek seni dulu (parity legacy: seni diprioritaskan)
            $penampilanSeni = $seniModel->getPenampilanSeniBerlangsungByGelanggang($idGlg);

            if ($penampilanSeni) {
                // Ambil nomor partai dari detail_jadwal_seni
                $partaiSeni = $seniModel->getPartaiSeniBerlangsungByGelanggang($idGlg);
                $dataPartai[] = [
                    'id_gelanggang'   => $idGlg,
                    'nama_gelanggang' => $glg['nama_gelanggang'],
                    'jenis_partai'    => 'seni',
                    'nomor_partai'    => $partaiSeni['nomor_partai'] ?? null,
                    'nama_atlet'      => $penampilanSeni['anggota_kelompok_peserta_seni'] ?? '-',
                    'nama_kontingen'  => $penampilanSeni['nama_kontingen'] ?? '-',
                    'nama_seni'       => $penampilanSeni['nama_seni'] ?? null,
                    'jenis_seni'      => $penampilanSeni['jenis_seni'] ?? null,
                    'nilai_akhir'     => $penampilanSeni['nilai_akhir'] ?? 0,
                ];
                continue;
            }

            // Cek tanding
            $pertandingan = $tandingModel->getPertandinganBerlangsungByGelanggang($idGlg);

            if ($pertandingan) {
                $dataPartai[] = [
                    'id_gelanggang'      => $idGlg,
                    'nama_gelanggang'    => $glg['nama_gelanggang'],
                    'jenis_partai'       => 'tanding',
                    'nomor_partai'       => $pertandingan['nomor_partai'] ?? null,
                    'nama_atlet_biru'    => $pertandingan['nama_atlet_biru'] ?? '-',
                    'nama_kontingen_biru' => $pertandingan['nama_kontingen_biru'] ?? '-',
                    'nama_atlet_merah'   => $pertandingan['nama_atlet_merah'] ?? '-',
                    'nama_kontingen_merah' => $pertandingan['nama_kontingen_merah'] ?? '-',
                    'skor_biru'          => $pertandingan['skor_biru'] ?? 0,
                    'skor_merah'         => $pertandingan['skor_merah'] ?? 0,
                    'babak'              => $pertandingan['babak'] ?? null,
                ];
                continue;
            }

            // Idle
            $dataPartai[] = [
                'id_gelanggang'   => $idGlg,
                'nama_gelanggang' => $glg['nama_gelanggang'],
                'jenis_partai'    => 'idle',
                'nomor_partai'    => null,
            ];
        }

        return view('pertandingan/monitoring/live_jadwal', [
            'page_title'          => 'Monitoring Partai',
            'data_partai'         => $dataPartai,
            'jumlah_gelanggang'   => count($gelanggang),
        ]);
    }
}
