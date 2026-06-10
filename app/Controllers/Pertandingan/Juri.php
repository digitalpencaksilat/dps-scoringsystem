<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\PenilaianTandingModel;
use App\Models\PertandinganModel;
use App\Services\Scoring\Persilat\PersilatTandingService;

/**
 * Controller Juri — input penilaian tanding PERSILAT.
 *
 * Parity legacy controllers/pertandingan/Juri.php (tanding, edit_penilaian_tanding,
 * refresh_status_pertandingan). Scope: hanya format PERSILAT.
 *
 * Reliability (docs Fase 3 §6.2): write nilai lewat model dengan row-lock + transaksi,
 * input divalidasi server-side (nilai legal 1/2/3), CSRF aktif.
 */
class Juri extends BaseController
{
    protected PertandinganModel $pertandinganModel;
    protected PenilaianTandingModel $penilaianModel;
    protected PersilatTandingService $service;

    public function __construct()
    {
        $this->pertandinganModel = new PertandinganModel();
        $this->penilaianModel    = new PenilaianTandingModel();
        $this->service           = new PersilatTandingService();
    }

    private function idGelanggang(): int
    {
        return (int) session()->get('id_gelanggang');
    }

    private function idPerangkat(): int
    {
        return (int) session()->get('id_perangkat_pertandingan');
    }

    /**
     * Halaman input nilai juri. Bila tidak ada partai berlangsung atau juri
     * tidak ditugaskan (skema 3 juri), arahkan ke standby.
     */
    public function tanding(string $theme = 'light')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $dataNilai = $this->penilaianModel->getByPertandinganDanPerangkat(
            (int) $pertandingan->id_pertandingan,
            $this->idPerangkat()
        );

        // Juri tidak ditugaskan pada partai ini → standby.
        if ($dataNilai === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';

        return view('pertandingan/juri/tanding/persilat/' . $theme, [
            'title'        => 'Penilaian Tanding',
            'pertandingan' => $pertandingan,
            'data_nilai'   => [
                'merah' => json_decode($dataNilai->penilaian_merah),
                'biru'  => json_decode($dataNilai->penilaian_biru),
            ],
            'pemenang'     => $dataNilai->pemenang,
            'atlet_merah'  => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah'),
            'atlet_biru'   => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru'),
            'theme'        => $theme,
        ]);
    }

    /**
     * Endpoint AJAX: tambah/hapus entry nilai (incremental) untuk juri ini.
     * Parity legacy edit_penilaian_tanding().
     */
    public function editPenilaianTanding(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->response->setJSON(['status' => false, 'message' => 'Partai tidak aktif.']);
        }

        $sudut = (string) $this->request->getPost('sudut');
        if (! in_array($sudut, ['merah', 'biru'], true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Sudut tidak valid.']);
        }

        $entry = json_decode((string) $this->request->getPost('entry'), true);
        if (! is_array($entry)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Entry tidak valid.']);
        }

        // Server-side guard: bila menambah nilai, hanya nilai legal yang diterima.
        $isRemove = isset($entry['action']) && $entry['action'] === 'remove';
        if (! $isRemove) {
            $nilai = (int) ($entry['nilai'] ?? 0);
            if (! $this->service->isNilaiJuriLegal($nilai)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Nilai tidak legal.']);
            }
            // Normalisasi: status awal 'input' (akan diverifikasi pipeline).
            $entry = [
                'nilai'  => $nilai,
                'status' => 'input',
                'warna'  => null,
                'id_nilai' => null,
                'tag'    => false,
            ];
        }

        $response = $this->penilaianModel->prosesIncremental(
            $pertandingan,
            $this->idPerangkat(),
            $sudut,
            $entry,
            $this->service
        );

        if ($response === false) {
            return $this->response->setJSON(['status' => false]);
        }

        // Push real-time skor terkini (fire-and-forget; DB tetap authoritative).
        helper('realtime');
        $terkini = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());
        if ($terkini !== null && (int) $terkini->id_pertandingan === (int) $pertandingan->id_pertandingan) {
            realtime_emit_skor(
                (int) $terkini->id_pertandingan,
                (int) $terkini->skor_merah,
                (int) $terkini->skor_biru,
                (string) $terkini->ronde_pertandingan
            );
        }

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'response' => $response, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Polling status partai untuk juri (parity refresh_status_pertandingan, cabang juri).
     * Akan digantikan push event Socket.IO di Fase 8.
     */
    public function refreshStatusPertandingan(?int $idPertandingan = null)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return $this->response->setJSON(['status' => true, 'reload' => $idPertandingan !== null]);
        }

        if ((int) $pertandingan->id_pertandingan !== (int) $idPertandingan) {
            // Partai berganti → klien perlu reload.
            $dataNilai = $this->penilaianModel->getByPertandinganDanPerangkat(
                (int) $pertandingan->id_pertandingan,
                $this->idPerangkat()
            );

            return $this->response->setJSON([
                'status' => true,
                'reload' => $dataNilai !== null, // juri tak ditugaskan → tetap standby
            ]);
        }

        // Partai sama → kirim nilai terbaru juri ini.
        $dataNilai = $this->penilaianModel->getByPertandinganDanPerangkat(
            (int) $pertandingan->id_pertandingan,
            $this->idPerangkat()
        );

        return $this->response->setJSON([
            'status'       => false,
            'pertandingan' => $pertandingan,
            'pemenang'     => $dataNilai->pemenang ?? null,
            'data_nilai'   => $dataNilai !== null ? [
                'merah' => json_decode($dataNilai->penilaian_merah),
                'biru'  => json_decode($dataNilai->penilaian_biru),
            ] : null,
        ]);
    }
}
