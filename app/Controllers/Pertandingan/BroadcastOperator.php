<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\BroadcastGraphicModel;
use App\Models\PertandinganModel;

/**
 * Controller Broadcast Operator — kontrol overlay grafis siaran (tanding PERSILAT).
 *
 * Parity legacy controllers/pertandingan/Broadcast_operator.php:
 * - index/tanding: panel kontrol scene + daftar scene.
 * - refreshBroadcastGraphic: state scene aktif (dipakai overlay).
 * Tambahan: overlay() = halaman transparan untuk OBS/streaming yang menampilkan
 * lower-third / scoreboard sesuai scene aktif + skor live dari DB.
 */
class BroadcastOperator extends BaseController
{
    protected BroadcastGraphicModel $graphicModel;
    protected PertandinganModel $pertandinganModel;

    public function __construct()
    {
        $this->graphicModel      = new BroadcastGraphicModel();
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

    public function tanding()
    {
        $scenes = $this->graphicModel->getByGelanggangJenis($this->idGelanggang(), 'tanding');

        return view('pertandingan/broadcast_operator/tanding', [
            'title'           => 'Broadcast Operator',
            'scenes'          => $scenes,
            'nama_gelanggang' => session()->get('nama_gelanggang'),
        ]);
    }

    /**
     * Aktifkan sebuah scene (AJAX). Hanya scene milik gelanggang sesi.
     */
    public function setScene()
    {
        $scene = (string) $this->request->getPost('scene');
        if ($scene === '') {
            return $this->response->setJSON(['status' => false, 'message' => 'Scene kosong.']);
        }

        $ok = $this->graphicModel->aktifkanScene($this->idGelanggang(), 'tanding', $scene);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => $ok, 'scene' => $scene, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * State scene aktif + skor live (dipakai overlay & panel). Parity
     * refresh_broadcast_graphic + data skor untuk lower-third/scoreboard.
     *
     * @param int $idGelanggang 0 = pakai gelanggang sesi (panel operator);
     *                          >0 = mode publik (overlay OBS, tanpa sesi).
     */
    public function refreshBroadcastGraphic(int $idGelanggang = 0)
    {
        $gid = $idGelanggang > 0 ? $idGelanggang : $this->idGelanggang();

        $scene        = $this->graphicModel->getSceneAktif($gid, 'tanding');
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($gid);

        $payload = [
            'scene' => $scene->scene ?? 'kosong',
        ];

        if ($pertandingan !== null) {
            $merah = $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah');
            $biru  = $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru');

            $payload['pertandingan'] = [
                'skor_merah'      => (int) $pertandingan->skor_merah,
                'skor_biru'       => (int) $pertandingan->skor_biru,
                'ronde'           => (string) $pertandingan->ronde_pertandingan,
                'status'          => $pertandingan->status_pertandingan,
                'nama_merah'      => $merah->nama_pendaftar ?? 'Merah',
                'nama_biru'       => $biru->nama_pendaftar ?? 'Biru',
                'kontingen_merah' => $merah->nama_kontingen ?? '-',
                'kontingen_biru'  => $biru->nama_kontingen ?? '-',
            ];
        }

        return $this->response->setJSON($payload);
    }

    /**
     * Halaman overlay transparan untuk OBS/streaming.
     * Tanpa filter posisi agar bisa ditambahkan sebagai Browser Source publik
     * di gelanggang tertentu via query ?gelanggang=ID (default: sesi bila ada).
     */
    public function overlay(int $idGelanggang = 0)
    {
        $gid = $idGelanggang > 0 ? $idGelanggang : $this->idGelanggang();

        return view('pertandingan/broadcast_operator/overlay', [
            'id_gelanggang' => $gid,
        ]);
    }
}
