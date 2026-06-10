<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\PertandinganModel;
use App\Models\PenampilanSeniModel;
use App\Models\PenilaianSeniModel;

/**
 * Controller Layar — papan skor (scoreboard display) tanding & seni PERSILAT.
 *
 * Parity legacy: controllers/pertandingan/Layar.php
 * Read-only display: menampilkan state authoritative dari DB.
 * Real-time sync: Socket.IO push (primary) + HTTP polling (fallback/recovery).
 */
class Layar extends BaseController
{
    protected PertandinganModel $pertandinganModel;
    protected PenampilanSeniModel $penampilanSeniModel;
    protected PenilaianSeniModel $penilaianSeniModel;

    public function __construct()
    {
        $this->pertandinganModel   = new PertandinganModel();
        $this->penampilanSeniModel = new PenampilanSeniModel();
        $this->penilaianSeniModel  = new PenilaianSeniModel();
    }

    private function idGelanggang(): int
    {
        return (int) session()->get('id_gelanggang');
    }

    private function jsonResponse(array $data)
    {
        $data['csrf_hash'] = csrf_hash();
        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON($data);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HOME / INDEX
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Landing page layar — logo animation + sponsor video (idle state).
     * Polls for active match/penampilan to auto-switch.
     * Parity legacy: Layar::index()
     */
    public function index()
    {
        return view('pertandingan/layar/home', [
            'title'           => 'Layar — Scoreboard',
            'nama_gelanggang' => session()->get('nama_gelanggang') ?? 'Gelanggang',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TANDING
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Scoreboard tanding — live score display.
     * Parity legacy: Layar::tanding($theme)
     */
    public function tanding(string $theme = 'dark')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return view('pertandingan/layar/standby', [
                'title'           => 'Layar — Standby',
                'nama_gelanggang' => session()->get('nama_gelanggang'),
                'mode'            => 'tanding',
            ]);
        }

        return view('pertandingan/layar/tanding', [
            'title'        => 'Papan Skor Tanding',
            'pertandingan' => $pertandingan,
            'data_waktu'   => $pertandingan->data_waktu ? json_decode($pertandingan->data_waktu) : null,
            'atlet_merah'  => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah'),
            'atlet_biru'   => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru'),
            'theme'        => in_array($theme, ['light', 'dark'], true) ? $theme : 'dark',
        ]);
    }

    /**
     * Polling state authoritative tanding.
     * Parity legacy: refresh_status_pertandingan($id_pertandingan)
     *
     * Dua use-case:
     *  1. Dari home.php (null id) → cek ada pertandingan aktif? Return status:false = ada.
     *  2. Dari tanding.php (specific id) → kirim live data; reload jika id berubah.
     */
    public function refreshStatusPertandingan(?int $idPertandingan = null)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            // Tidak ada pertandingan aktif
            return $this->jsonResponse(['status' => true, 'reload' => false]);
        }

        // Case 1: Dipanggil dari home/standby (null id) → ada pertandingan aktif, suruh redirect
        if ($idPertandingan === null || $idPertandingan === 0) {
            return $this->jsonResponse([
                'status'          => false,
                'id_pertandingan' => (int) $pertandingan->id_pertandingan,
            ]);
        }

        // Case 2: ID berubah → reload
        if ((int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }

        // Case 3: Sama → kirim live data
        return $this->jsonResponse([
            'status'              => false,
            'id_pertandingan'     => (int) $pertandingan->id_pertandingan,
            'skor_merah'          => (int) $pertandingan->skor_merah,
            'skor_biru'           => (int) $pertandingan->skor_biru,
            'ronde'               => (string) $pertandingan->ronde_pertandingan,
            'status_pertandingan' => $pertandingan->status_pertandingan,
            'data_waktu'          => $pertandingan->data_waktu ? json_decode($pertandingan->data_waktu) : null,
            'ringkasan_nilai'     => $pertandingan->ringkasan_nilai ? json_decode($pertandingan->ringkasan_nilai) : null,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SENI
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Scoreboard seni — live penampilan display.
     * Shows participant info, timer, juri scores in real-time.
     * Parity legacy: Layar::seni($mode)
     */
    public function seni(string $theme = 'dark')
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return view('pertandingan/layar/standby', [
                'title'           => 'Layar — Standby Seni',
                'nama_gelanggang' => session()->get('nama_gelanggang'),
                'mode'            => 'seni',
            ]);
        }

        $idPenampilan = (int) $penampilan->id_penampilan_seni;

        // Get all juri scores
        $dataNilaiJuri = $this->penilaianSeniModel
            ->where('id_penampilan_seni', $idPenampilan)
            ->findAll();

        // Determine sistem (pool or battle)
        $sistemPenampilan = $penampilan->sistem_penampilan ?? 'pool';

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/layar/seni', [
            'title'           => 'Papan Skor Seni',
            'penampilan'      => $penampilan,
            'data_nilai_juri' => $dataNilaiJuri,
            'sistem'          => $sistemPenampilan,
            'theme'           => $theme,
        ]);
    }

    /**
     * Polling state authoritative seni.
     * Returns juri scores, ready status, akses penilaian.
     * Parity legacy: refresh_status_seni($id_penampilan_seni)
     *
     * Dua use-case:
     *  1. Dari home.php (null id) → cek ada penampilan aktif? Return status:false = ada.
     *  2. Dari seni.php (specific id) → kirim live data; reload jika id berubah.
     */
    public function refreshStatusSeni(?int $idPenampilanSeni = null)
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            // Tidak ada penampilan aktif
            return $this->jsonResponse(['status' => true, 'reload' => false]);
        }

        // Case 1: Dipanggil dari home/standby (null id) → ada penampilan aktif, suruh redirect
        if ($idPenampilanSeni === null || $idPenampilanSeni === 0) {
            return $this->jsonResponse([
                'status'             => false,
                'id_penampilan_seni' => (int) $penampilan->id_penampilan_seni,
            ]);
        }

        // Case 2: ID berubah → reload
        if ((int) $penampilan->id_penampilan_seni !== $idPenampilanSeni) {
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }

        // Get all juri scores
        $dataNilaiJuri = $this->penilaianSeniModel
            ->where('id_penampilan_seni', (int) $penampilan->id_penampilan_seni)
            ->findAll();

        $juriData = [];
        foreach ($dataNilaiJuri as $row) {
            $juriData[] = [
                'id_perangkat'     => (int) $row->id_perangkat_pertandingan,
                'nilai_akhir'      => (float) ($row->nilai_akhir_per_juri ?? 0),
                'ready'            => (int) ($row->status_ready ?? 0),
                'terpilih'         => (int) ($row->terpilih ?? 0),
            ];
        }

        return $this->jsonResponse([
            'status'           => false,
            'id_penampilan'    => (int) $penampilan->id_penampilan_seni,
            'akses_penilaian'  => $penampilan->akses_penilaian ?? 'dibuka',
            'status_penampilan' => $penampilan->status_penampilan ?? 'sedang_tampil',
            'juri_data'        => $juriData,
            'nilai_akhir'      => (float) ($penampilan->nilai_akhir ?? 0),
            'diskualifikasi'   => (int) ($penampilan->diskualifikasi ?? 0),
        ]);
    }

    /**
     * Hasil pool seni — ranking display after all performances.
     * Parity legacy: Layar::hasil_pool_seni($id_kompetisi_seni)
     */
    public function hasilPoolSeni(int $idKompetisiSeni)
    {
        // Get all penampilan for this kompetisi, ordered by nilai_akhir DESC
        $daftarPenampilan = $this->penampilanSeniModel
            ->select('penampilan_seni.*, kelompok_peserta_seni.*, kontingen.nama_kontingen')
            ->join('kelompok_peserta_seni', 'kelompok_peserta_seni.id_kelompok_peserta_seni = penampilan_seni.id_kelompok_peserta_seni')
            ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen', 'left')
            ->join('kompetisi_seni', 'kompetisi_seni.id_kompetisi_seni = kelompok_peserta_seni.id_kompetisi_seni')
            ->join('detail_jadwal_seni', 'detail_jadwal_seni.id_penampilan_seni = penampilan_seni.id_penampilan_seni')
            ->join('jadwal_seni', 'jadwal_seni.id_jadwal_seni = detail_jadwal_seni.id_jadwal_seni')
            ->where('jadwal_seni.id_gelanggang', $this->idGelanggang())
            ->where('kelompok_peserta_seni.id_kompetisi_seni', $idKompetisiSeni)
            ->orderBy('penampilan_seni.nilai_akhir', 'DESC')
            ->findAll();

        return view('pertandingan/layar/hasil_pool_seni', [
            'title'     => 'Hasil Pool Seni',
            'daftar'    => $daftarPenampilan,
        ]);
    }

    /**
     * Hasil battle seni — winner display.
     * Parity legacy: Layar::hasil_battle_seni($id_battle_seni)
     */
    public function hasilBattleSeni(int $idBattleSeni)
    {
        // Simple view showing battle winner
        return view('pertandingan/layar/hasil_battle_seni', [
            'title'         => 'Hasil Battle Seni',
            'id_battle_seni' => $idBattleSeni,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TRANSISI & HASIL TANDING
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Transisi/idle screen between matches.
     * Shows logo animation or sponsor video, polls for next match.
     * Parity legacy: Layar standby_tanding() with video_sponsor/animasi_logo
     */
    public function transisi(string $mode = 'tanding')
    {
        $mode = in_array($mode, ['tanding', 'seni'], true) ? $mode : 'tanding';

        return view('pertandingan/layar/transisi', [
            'title'           => 'Layar — Transisi',
            'nama_gelanggang' => session()->get('nama_gelanggang') ?? 'Gelanggang',
            'mode'            => $mode,
        ]);
    }

    /**
     * Hasil pertandingan tanding — winner display after match ends.
     * Parity legacy: Layar with hasil_pertandingan/dark view
     */
    public function hasilTanding(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->find($idPertandingan);

        if ($pertandingan === null) {
            return redirect()->to(base_url('layar/home'));
        }

        return view('pertandingan/layar/hasil_tanding', [
            'title'        => 'Hasil Pertandingan',
            'pertandingan' => $pertandingan,
            'atlet_merah'  => $this->pertandinganModel->getAtletPertandingan($idPertandingan, 'merah'),
            'atlet_biru'   => $this->pertandinganModel->getAtletPertandingan($idPertandingan, 'biru'),
        ]);
    }
}
