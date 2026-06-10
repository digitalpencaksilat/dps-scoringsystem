<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\PertandinganModel;

/**
 * Controller Layar — papan skor (scoreboard) tanding PERSILAT.
 *
 * Parity legacy controllers/pertandingan/Layar.php (tanding, standby_tanding,
 * refresh_status_pertandingan cabang layar). Read-only: hanya menampilkan
 * state authoritative dari DB. Sinkronisasi real-time via polling (Fase 6) →
 * akan ditingkatkan ke push Socket.IO (Fase 8).
 *
 * Reliability (docs §6.2): saat reconnect, klien memanggil endpoint state untuk
 * memulihkan kondisi terkini (skor, ronde, timer) dari DB sebagai sumber kebenaran.
 */
class Layar extends BaseController
{
    protected PertandinganModel $pertandinganModel;

    public function __construct()
    {
        $this->pertandinganModel = new PertandinganModel();
    }

    private function idGelanggang(): int
    {
        return (int) session()->get('id_gelanggang');
    }

    public function index()
    {
        return $this->tanding();
    }

    public function tanding(string $theme = 'dark')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return view('pertandingan/layar/standby', [
                'title'           => 'Layar — Standby',
                'nama_gelanggang' => session()->get('nama_gelanggang'),
            ]);
        }

        return view('pertandingan/layar/tanding', [
            'title'        => 'Papan Skor',
            'pertandingan' => $pertandingan,
            'data_waktu'   => $pertandingan->data_waktu ? json_decode($pertandingan->data_waktu) : null,
            'atlet_merah'  => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah'),
            'atlet_biru'   => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru'),
            'theme'        => in_array($theme, ['light', 'dark'], true) ? $theme : 'dark',
        ]);
    }

    /**
     * Endpoint state authoritative untuk Layar (polling + recovery reconnect).
     * Mengembalikan skor, ronde, status, data_waktu terkini partai berlangsung.
     */
    public function refreshStatusPertandingan(?int $idPertandingan = null)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            // Tidak ada partai → reload bila sebelumnya menampilkan partai.
            return $this->response->setJSON(['status' => true, 'reload' => $idPertandingan !== null]);
        }

        if ((int) $pertandingan->id_pertandingan !== (int) $idPertandingan) {
            // Partai berganti → klien perlu reload untuk memuat atlet baru.
            return $this->response->setJSON(['status' => true, 'reload' => true]);
        }

        return $this->response->setJSON([
            'status'       => false,
            'id_pertandingan'     => (int) $pertandingan->id_pertandingan,
            'skor_merah'   => (int) $pertandingan->skor_merah,
            'skor_biru'    => (int) $pertandingan->skor_biru,
            'ronde'        => (string) $pertandingan->ronde_pertandingan,
            'status_pertandingan' => $pertandingan->status_pertandingan,
            'data_waktu'   => $pertandingan->data_waktu ? json_decode($pertandingan->data_waktu) : null,
        ]);
    }
}
