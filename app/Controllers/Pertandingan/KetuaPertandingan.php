<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\PenilaianTandingModel;
use App\Models\PertandinganModel;
use App\Services\Scoring\Persilat\PersilatTandingService;

/**
 * Controller Ketua Pertandingan — kontrol hukuman/teguran/peringatan/binaan/jatuhan
 * untuk tanding PERSILAT. Parity legacy controllers/pertandingan/Ketua_pertandingan.php
 * (button_controller_tanding, edit_penilaian_tanding, refresh_status_pertandingan).
 */
class KetuaPertandingan extends BaseController
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

    /** Mode KP yang diizinkan (anti payload ilegal). */
    private const MODE_LEGAL = [
        'binaan', 'binaan_1', 'binaan_2',
        'teguran', 'teguran_1', 'teguran_2',
        'peringatan', 'peringatan_1', 'peringatan_2',
        'jatuhan', 'serangan', 'hukuman',
    ];

    public function tanding(string $theme = 'light')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $ringkasan = $pertandingan->ringkasan_nilai
            ? json_decode($pertandingan->ringkasan_nilai)
            : null;

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';

        return view('pertandingan/ketua/tanding/persilat/' . $theme, [
            'title'        => 'Kontrol Ketua Pertandingan',
            'pertandingan' => $pertandingan,
            'ringkasan'    => $ringkasan,
            'atlet_merah'  => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah'),
            'atlet_biru'   => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru'),
            'theme'        => $theme,
        ]);
    }

    /**
     * Endpoint AJAX KP: terapkan hukuman/binaan/jatuhan ke semua juri.
     * Parity legacy edit_penilaian_tanding().
     */
    public function editPenilaianTanding(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->response->setJSON(['status' => false]);
        }

        $sudut  = (string) $this->request->getPost('sudut');
        $mode   = (string) $this->request->getPost('mode');
        $jumlahRaw = $this->request->getPost('jumlah'); // null/'hapus' => hapus

        if (! in_array($sudut, ['merah', 'biru'], true) || ! in_array($mode, self::MODE_LEGAL, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter tidak valid.']);
        }

        $jumlah = ($jumlahRaw === null || $jumlahRaw === '' || $jumlahRaw === 'hapus')
            ? 'hapus'
            : (int) $jumlahRaw;

        $ronde = (string) $pertandingan->ronde_pertandingan;

        $rows = $this->penilaianModel->prosesKp($pertandingan, $sudut, $ronde, $mode, $jumlah, $this->service);

        if ($rows === false) {
            return $this->response->setJSON(['status' => false]);
        }

        // Ambil ringkasan terbaru (sudah disimpan oleh prosesKp).
        $fresh = $this->pertandinganModel->find($idPertandingan);

        // Push real-time skor terkini (fire-and-forget).
        helper('realtime');
        realtime_emit_skor(
            (int) $idPertandingan,
            (int) $fresh->skor_merah,
            (int) $fresh->skor_biru,
            (string) $fresh->ronde_pertandingan
        );

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON([
                'status'    => true,
                'skor_merah' => (int) $fresh->skor_merah,
                'skor_biru'  => (int) $fresh->skor_biru,
                'ringkasan'  => $fresh->ringkasan_nilai ? json_decode($fresh->ringkasan_nilai) : null,
                'csrf_hash'  => csrf_hash(),
            ]);
    }

    /**
     * Polling status partai untuk KP (kirim ringkasan agregat terbaru).
     */
    public function refreshStatusPertandingan(?int $idPertandingan = null)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return $this->response->setJSON(['status' => true, 'reload' => $idPertandingan !== null]);
        }

        if ((int) $pertandingan->id_pertandingan !== (int) $idPertandingan) {
            return $this->response->setJSON(['status' => true, 'reload' => true]);
        }

        return $this->response->setJSON([
            'status'     => false,
            'skor_merah' => (int) $pertandingan->skor_merah,
            'skor_biru'  => (int) $pertandingan->skor_biru,
            'ringkasan'  => $pertandingan->ringkasan_nilai ? json_decode($pertandingan->ringkasan_nilai) : null,
        ]);
    }
}
