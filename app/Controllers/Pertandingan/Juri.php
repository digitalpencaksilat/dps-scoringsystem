<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\PenilaianTandingModel;
use App\Models\PenilaianSeniModel;
use App\Models\PertandinganModel;
use App\Models\PenampilanSeniModel;
use App\Services\Scoring\Persilat\PersilatTandingService;
use App\Services\Scoring\Persilat\PersilatSeniService;

/**
 * Controller Juri — input penilaian tanding & seni PERSILAT.
 *
 * Parity legacy: controllers/pertandingan/Juri.php
 * Scope: hanya format PERSILAT (tanding + seni).
 */
class Juri extends BaseController
{
    protected PertandinganModel $pertandinganModel;
    protected PenilaianTandingModel $penilaianTandingModel;
    protected PenilaianSeniModel $penilaianSeniModel;
    protected PenampilanSeniModel $penampilanSeniModel;
    protected PersilatTandingService $tandingService;
    protected PersilatSeniService $seniService;

    public function __construct()
    {
        $this->pertandinganModel    = new PertandinganModel();
        $this->penilaianTandingModel = new PenilaianTandingModel();
        $this->penilaianSeniModel   = new PenilaianSeniModel();
        $this->penampilanSeniModel  = new PenampilanSeniModel();
        $this->tandingService       = new PersilatTandingService();
        $this->seniService          = new PersilatSeniService();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    private function idGelanggang(): int
    {
        return (int) session()->get('id_gelanggang');
    }

    private function idPerangkat(): int
    {
        return (int) session()->get('id_perangkat_pertandingan');
    }

    private function jsonResponse(array $data, bool $withCsrf = true)
    {
        $resp = $this->response;
        if ($withCsrf) {
            $data['csrf_hash'] = csrf_hash();
            $resp = $resp->setHeader('X-CSRF-TOKEN', csrf_hash());
        }
        return $resp->setJSON($data);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HOME / DASHBOARD
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Halaman home juri — pilihan masuk ke tanding atau seni.
     * Parity legacy: Juri::index() → views/pertandingan/juri/home.php
     */
    public function index()
    {
        return view('pertandingan/juri/home', [
            'title' => 'Panel Juri',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TANDING
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Halaman input nilai juri tanding.
     * Theme: 'light' | 'dark'
     */
    public function tanding(string $theme = 'light')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $dataNilai = $this->penilaianTandingModel->getByPertandinganDanPerangkat(
            (int) $pertandingan->id_pertandingan,
            $this->idPerangkat()
        );

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
     * AJAX: tambah/hapus entry nilai tanding (incremental).
     */
    public function editPenilaianTanding(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->jsonResponse(['status' => false, 'message' => 'Partai tidak aktif.']);
        }

        $sudut = (string) $this->request->getPost('sudut');
        if (! in_array($sudut, ['merah', 'biru'], true)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Sudut tidak valid.']);
        }

        $entry = json_decode((string) $this->request->getPost('entry'), true);
        if (! is_array($entry)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Entry tidak valid.']);
        }

        $isRemove = isset($entry['action']) && $entry['action'] === 'remove';
        if (! $isRemove) {
            $nilai = (int) ($entry['nilai'] ?? 0);
            if (! $this->tandingService->isNilaiJuriLegal($nilai)) {
                return $this->jsonResponse(['status' => false, 'message' => 'Nilai tidak legal.']);
            }
            $entry = [
                'nilai'    => $nilai,
                'status'   => 'input',
                'warna'    => null,
                'id_nilai' => null,
                'tag'      => false,
            ];
        }

        $response = $this->penilaianTandingModel->prosesIncremental(
            $pertandingan,
            $this->idPerangkat(),
            $sudut,
            $entry,
            $this->tandingService
        );

        if ($response === false) {
            return $this->jsonResponse(['status' => false]);
        }

        // Push real-time skor
        helper('realtime');
        $terkini = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());
        if ($terkini !== null && (int) $terkini->id_pertandingan === $idPertandingan) {
            realtime_emit_skor(
                (int) $terkini->id_pertandingan,
                (int) $terkini->skor_merah,
                (int) $terkini->skor_biru,
                (string) $terkini->ronde_pertandingan
            );
        }

        return $this->jsonResponse(['status' => true, 'response' => $response]);
    }

    /**
     * Polling status pertandingan tanding.
     */
    public function refreshStatusPertandingan(?int $idPertandingan = null)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return $this->jsonResponse(['status' => true, 'reload' => $idPertandingan !== null]);
        }

        if ((int) $pertandingan->id_pertandingan !== (int) $idPertandingan) {
            $dataNilai = $this->penilaianTandingModel->getByPertandinganDanPerangkat(
                (int) $pertandingan->id_pertandingan,
                $this->idPerangkat()
            );
            return $this->jsonResponse(['status' => true, 'reload' => $dataNilai !== null]);
        }

        $dataNilai = $this->penilaianTandingModel->getByPertandinganDanPerangkat(
            (int) $pertandingan->id_pertandingan,
            $this->idPerangkat()
        );

        return $this->jsonResponse([
            'status'       => false,
            'pertandingan' => $pertandingan,
            'pemenang'     => $dataNilai->pemenang ?? null,
            'data_nilai'   => $dataNilai !== null ? [
                'merah' => json_decode($dataNilai->penilaian_merah),
                'biru'  => json_decode($dataNilai->penilaian_biru),
            ] : null,
        ]);
    }

    /**
     * AJAX: Submit jawaban verifikasi jatuhan/pelanggaran dari juri.
     */
    public function submitJawabanVerifikasi(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->jsonResponse(['status' => false, 'message' => 'Partai tidak aktif.']);
        }

        $sudut   = (string) $this->request->getPost('sudut');
        $jenis   = (string) $this->request->getPost('jenis'); // 'jatuhan' | 'pelanggaran'
        $jawaban = (string) $this->request->getPost('jawaban'); // 'ya' | 'tidak'

        if (! in_array($sudut, ['merah', 'biru'], true)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Sudut tidak valid.']);
        }
        if (! in_array($jenis, ['jatuhan', 'pelanggaran'], true)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Jenis verifikasi tidak valid.']);
        }
        if (! in_array($jawaban, ['ya', 'tidak'], true)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Jawaban tidak valid.']);
        }

        // Simpan jawaban verifikasi ke session (aggregated by KP)
        $verifikasiKey = "verifikasi_{$jenis}_{$idPertandingan}_{$sudut}";
        session()->set($verifikasiKey, $jawaban);

        return $this->jsonResponse(['status' => true, 'jawaban' => $jawaban]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SENI
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Halaman input nilai juri seni.
     * Mode: 'sederhana' | 'terperinci'
     */
    public function seni(string $mode = 'sederhana')
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        // Cek apakah juri ini punya row penilaian untuk penampilan ini
        $penilaian = $this->penilaianSeniModel
            ->where('id_penampilan_seni', (int) $penampilan->id_penampilan_seni)
            ->where('id_perangkat_pertandingan', $this->idPerangkat())
            ->first();

        if ($penilaian === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        // Load format penilaian berdasarkan sub_kategori_seni
        $formatPenilaian = $this->loadFormatPenilaianSeni($penampilan);

        $mode = in_array($mode, ['sederhana', 'terperinci'], true) ? $mode : 'sederhana';

        return view('pertandingan/juri/seni/persilat/' . $mode, [
            'title'             => 'Penilaian Seni',
            'penampilan'        => $penampilan,
            'penilaian'         => $penilaian,
            'format_penilaian'  => $formatPenilaian,
            'data_nilai'        => json_decode($penilaian->penilaian, true),
            'mode'              => $mode,
            'akses_penilaian'   => $penampilan->akses_penilaian ?? 'dibuka',
        ]);
    }

    /**
     * AJAX: Save seluruh JSON penilaian seni.
     */
    public function editPenilaianSeni(int $idPenampilanSeni)
    {
        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        // Cek akses penilaian
        if (($penampilan->akses_penilaian ?? 'dibuka') === 'ditutup') {
            return $this->jsonResponse(['status' => false, 'message' => 'Akses penilaian sudah ditutup.']);
        }

        $dataNilai = $this->request->getPost('data_nilai');
        $nilaiAkhirPerJuri = $this->request->getPost('nilai_akhir_per_juri');

        if (empty($dataNilai)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Data nilai kosong.']);
        }

        // Validasi JSON
        $decoded = is_string($dataNilai) ? json_decode($dataNilai, true) : $dataNilai;
        if (json_last_error() !== JSON_ERROR_NONE && is_string($dataNilai)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Format JSON tidak valid.']);
        }

        $jsonNilai = is_string($dataNilai) ? $dataNilai : json_encode($dataNilai);

        // Update penilaian
        $penilaian = $this->penilaianSeniModel
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->where('id_perangkat_pertandingan', $this->idPerangkat())
            ->first();

        if ($penilaian === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Record penilaian tidak ditemukan.']);
        }

        $this->penilaianSeniModel->update($penilaian->id_penilaian_seni, [
            'penilaian'          => $jsonNilai,
            'nilai_akhir_per_juri' => (string) round((float) $nilaiAkhirPerJuri, 4),
        ]);

        return $this->jsonResponse(['status' => true, 'new_nilai' => $jsonNilai]);
    }

    /**
     * Polling status penampilan seni.
     */
    public function refreshStatusSeni(?int $idPenampilanSeni = null)
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => true, 'reload' => $idPenampilanSeni !== null]);
        }

        if ((int) ($penampilan->id_penampilan_seni ?? 0) !== (int) $idPenampilanSeni) {
            // Penampilan berganti → reload
            $penilaian = $this->penilaianSeniModel
                ->where('id_penampilan_seni', (int) $penampilan->id_penampilan_seni)
                ->where('id_perangkat_pertandingan', $this->idPerangkat())
                ->first();
            return $this->jsonResponse(['status' => true, 'reload' => $penilaian !== null]);
        }

        // Sama → kirim hukuman terkini dari server (KP bisa update kapan saja)
        $semuaPenilaian = $this->penilaianSeniModel->getByPenampilan((int) $idPenampilanSeni);
        $hukumanTerkini = null;

        // Ambil hukuman dari salah satu juri (harus identik, diinput KP)
        if (! empty($semuaPenilaian)) {
            $sample = json_decode($semuaPenilaian[0]->penilaian, true);
            $hukumanTerkini = $sample['penilaian']['hukuman'] ?? null;
        }

        return $this->jsonResponse([
            'status'             => false,
            'penampilan_status'  => $penampilan->status_penampilan ?? 'sedang_tampil',
            'akses_penilaian'    => $penampilan->akses_penilaian ?? 'dibuka',
            'hukuman'            => $hukumanTerkini,
        ]);
    }

    /**
     * AJAX: Toggle ready flag juri seni.
     */
    public function toggleReadySeni(int $idPenampilanSeni)
    {
        $penilaian = $this->penilaianSeniModel
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->where('id_perangkat_pertandingan', $this->idPerangkat())
            ->first();

        if ($penilaian === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Record tidak ditemukan.']);
        }

        $newReady = ((int) $penilaian->status_ready === 0) ? 1 : 0;
        $this->penilaianSeniModel->update($penilaian->id_penilaian_seni, [
            'status_ready' => $newReady,
        ]);

        return $this->jsonResponse(['status' => true, 'ready' => (bool) $newReady]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────

    /**
     * Load format penilaian JSON berdasarkan format dari sub_kategori_seni.
     */
    private function loadFormatPenilaianSeni(object $penampilan): ?array
    {
        // format_penilaian dari join sub_kategori_seni → e.g. 'tunggal', 'ganda', 'beregu', 'solo_kreatif'
        $format = $penampilan->format_penilaian ?? 'tunggal';
        $path = FCPATH . "assets/penilaian/format-penilaian/seni/persilat/{$format}/persilat.json";

        if (! file_exists($path)) {
            log_message('warning', "Format penilaian seni tidak ditemukan: {$path}");
            return null;
        }

        $json = file_get_contents($path);
        return json_decode($json, true);
    }
}
