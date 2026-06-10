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
 * Controller Ketua Pertandingan — kontrol hukuman/teguran/peringatan/binaan/jatuhan
 * untuk tanding + seni PERSILAT.
 * Parity legacy: controllers/pertandingan/Ketua_pertandingan.php
 */
class KetuaPertandingan extends BaseController
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

    private function jsonResponse(array $data)
    {
        $data['csrf_hash'] = csrf_hash();
        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON($data);
    }

    /** Mode KP yang diizinkan (anti payload ilegal). */
    private const MODE_LEGAL = [
        'binaan', 'binaan_1', 'binaan_2',
        'teguran', 'teguran_1', 'teguran_2',
        'peringatan', 'peringatan_1', 'peringatan_2',
        'jatuhan', 'serangan', 'hukuman',
    ];

    // ═══════════════════════════════════════════════════════════════════════
    //  HOME / INDEX
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Dashboard KP — pilih Tanding atau Seni.
     * Parity legacy: Ketua_pertandingan::index() → views/ketua_pertandingan/home.php
     */
    public function index()
    {
        return view('pertandingan/ketua/home', [
            'title' => 'Panel Ketua Pertandingan',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TANDING
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Halaman kontrol KP tanding — skor, hukuman, jatuhan.
     * Parity legacy: Ketua_pertandingan::tanding($versi)
     */
    public function tanding(string $theme = 'dark')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $ringkasan = $pertandingan->ringkasan_nilai
            ? json_decode($pertandingan->ringkasan_nilai)
            : null;

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/ketua/tanding/persilat/' . $theme, [
            'title'        => 'Kontrol Ketua Pertandingan - Tanding',
            'pertandingan' => $pertandingan,
            'ringkasan'    => $ringkasan,
            'atlet_merah'  => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah'),
            'atlet_biru'   => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru'),
            'theme'        => $theme,
        ]);
    }

    /**
     * AJAX: terapkan hukuman/binaan/jatuhan ke semua juri.
     * Parity legacy: edit_penilaian_tanding()
     */
    public function editPenilaianTanding(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->jsonResponse(['status' => false, 'message' => 'Partai tidak aktif.']);
        }

        $sudut     = (string) $this->request->getPost('sudut');
        $mode      = (string) $this->request->getPost('mode');
        $jumlahRaw = $this->request->getPost('jumlah');

        if (! in_array($sudut, ['merah', 'biru'], true) || ! in_array($mode, self::MODE_LEGAL, true)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Parameter tidak valid.']);
        }

        $jumlah = ($jumlahRaw === null || $jumlahRaw === '' || $jumlahRaw === 'hapus')
            ? 'hapus'
            : (int) $jumlahRaw;

        $ronde = (string) $pertandingan->ronde_pertandingan;

        $rows = $this->penilaianTandingModel->prosesKp($pertandingan, $sudut, $ronde, $mode, $jumlah, $this->tandingService);

        if ($rows === false) {
            return $this->jsonResponse(['status' => false, 'message' => 'Gagal menyimpan.']);
        }

        // Ambil ringkasan terbaru
        $fresh = $this->pertandinganModel->find($idPertandingan);

        // Push real-time skor
        helper('realtime');
        realtime_emit_skor(
            (int) $idPertandingan,
            (int) $fresh->skor_merah,
            (int) $fresh->skor_biru,
            (string) $fresh->ronde_pertandingan
        );

        return $this->jsonResponse([
            'status'     => true,
            'skor_merah' => (int) $fresh->skor_merah,
            'skor_biru'  => (int) $fresh->skor_biru,
            'ringkasan'  => $fresh->ringkasan_nilai ? json_decode($fresh->ringkasan_nilai) : null,
        ]);
    }

    /**
     * Polling status partai tanding.
     */
    public function refreshStatusPertandingan(?int $idPertandingan = null)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return $this->jsonResponse(['status' => true, 'reload' => $idPertandingan !== null]);
        }

        if ((int) $pertandingan->id_pertandingan !== (int) $idPertandingan) {
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }

        return $this->jsonResponse([
            'status'     => false,
            'skor_merah' => (int) $pertandingan->skor_merah,
            'skor_biru'  => (int) $pertandingan->skor_biru,
            'ringkasan'  => $pertandingan->ringkasan_nilai ? json_decode($pertandingan->ringkasan_nilai) : null,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SENI
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Halaman kontrol KP seni — hukuman, akses, diskualifikasi.
     * Parity legacy: Ketua_pertandingan::seni($theme)
     */
    public function seni(string $theme = 'dark')
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $idPenampilan = (int) $penampilan->id_penampilan_seni;

        // Get all juri scores for this penampilan
        $dataNilaiJuri = $this->penilaianSeniModel
            ->where('id_penampilan_seni', $idPenampilan)
            ->findAll();

        // Determine sistem (pool or battle)
        $sistemPenampilan = $penampilan->sistem_penampilan ?? 'pool';

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/ketua/seni/persilat/' . $sistemPenampilan . '/' . $theme, [
            'title'            => 'Kontrol KP - Seni',
            'penampilan'       => $penampilan,
            'data_nilai_juri'  => $dataNilaiJuri,
            'sistem'           => $sistemPenampilan,
            'akses_penilaian'  => $penampilan->akses_penilaian ?? 'dibuka',
            'theme'            => $theme,
        ]);
    }

    /**
     * AJAX: Input/edit hukuman seni dari KP.
     * Parity legacy: edit_penilaian_seni($id_penampilan_seni)
     * KP controls penalties; syncs to all juri records.
     */
    public function editPenilaianSeni(int $idPenampilanSeni)
    {
        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        $jenis   = (string) $this->request->getPost('jenis_hukuman'); // 'pengulangan' | 'waktu' | 'kostum' | etc
        $jumlah  = (int) $this->request->getPost('jumlah');

        if (empty($jenis)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Jenis hukuman wajib diisi.']);
        }

        // Build penalty data
        $hukumanData = [
            'jenis'   => $jenis,
            'jumlah'  => $jumlah,
            'sumber'  => 'kp',
        ];

        // Apply penalty to all juri records via service
        $result = $this->penilaianSeniModel->prosesHukumanKp(
            $idPenampilanSeni,
            $hukumanData,
            $this->seniService
        );

        if ($result === false) {
            return $this->jsonResponse(['status' => false, 'message' => 'Gagal menyimpan hukuman.']);
        }

        return $this->jsonResponse([
            'status'  => true,
            'message' => 'Hukuman berhasil diterapkan.',
        ]);
    }

    /**
     * Toggle akses penilaian seni (buka/tutup).
     * Parity legacy: ganti_akses_penilaian($id_penampilan_seni)
     */
    public function gantiAksesPenilaian(int $idPenampilanSeni)
    {
        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        $currentAkses = $penampilan->akses_penilaian ?? 'dibuka';
        $newAkses     = ($currentAkses === 'dibuka') ? 'ditutup' : 'dibuka';

        $this->penampilanSeniModel->update($idPenampilanSeni, [
            'akses_penilaian' => $newAkses,
        ]);

        // Emit socket event for all juri to lock/unlock
        helper('realtime');
        if (function_exists('realtime_emit_akses_penilaian')) {
            realtime_emit_akses_penilaian($idPenampilanSeni, $newAkses);
        }

        return $this->jsonResponse([
            'status'           => true,
            'akses_penilaian'  => $newAkses,
            'message'          => 'Akses penilaian: ' . $newAkses,
        ]);
    }

    /**
     * Diskualifikasi penampilan seni.
     * Parity legacy: diskualifikasi_penampilan_seni
     */
    public function diskualifikasiPenampilanSeni(int $idPenampilanSeni)
    {
        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        $alasan = (string) $this->request->getPost('alasan');

        $this->penampilanSeniModel->update($idPenampilanSeni, [
            'diskualifikasi' => 1,
        ]);

        return $this->jsonResponse([
            'status'  => true,
            'message' => 'Penampilan telah didiskualifikasi.',
        ]);
    }

    /**
     * Batalkan diskualifikasi.
     * Parity legacy: batalkan_diskualifikasi
     */
    public function batalkanDiskualifikasi(int $idPenampilanSeni)
    {
        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        $this->penampilanSeniModel->update($idPenampilanSeni, [
            'diskualifikasi' => 0,
        ]);

        return $this->jsonResponse([
            'status'  => true,
            'message' => 'Diskualifikasi dibatalkan.',
        ]);
    }

    /**
     * Polling status penampilan seni — return all juri data + ready status.
     * Parity legacy: refresh_status_seni($id_penampilan_seni)
     */
    public function refreshStatusSeni(?int $idPenampilanSeni = null)
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => true, 'reload' => $idPenampilanSeni !== null]);
        }

        if ((int) $penampilan->id_penampilan_seni !== (int) $idPenampilanSeni) {
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }

        // Get all juri penilaian
        $dataNilaiJuri = $this->penilaianSeniModel
            ->where('id_penampilan_seni', (int) $penampilan->id_penampilan_seni)
            ->findAll();

        // Check ready status per juri
        $juriReady = [];
        foreach ($dataNilaiJuri as $row) {
            $juriReady[] = [
                'id_perangkat' => $row->id_perangkat_pertandingan,
                'ready'        => (int) ($row->status_ready ?? 0),
                'nilai_akhir'  => (float) ($row->nilai_akhir_per_juri ?? 0),
            ];
        }

        return $this->jsonResponse([
            'status'          => false,
            'akses_penilaian' => $penampilan->akses_penilaian ?? 'dibuka',
            'juri_ready'      => $juriReady,
            'diskualifikasi'  => (int) ($penampilan->diskualifikasi ?? 0),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  DAFTAR NILAI
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Daftar nilai tanding (semua partai di gelanggang ini).
     * Parity legacy: daftar_nilai_tanding
     */
    public function daftarNilaiTanding()
    {
        return view('pertandingan/ketua/daftar_nilai_tanding', [
            'title' => 'Daftar Nilai Tanding',
        ]);
    }

    /**
     * Daftar nilai seni (semua penampilan di gelanggang ini).
     * Parity legacy: daftar_nilai_seni
     */
    public function daftarNilaiSeni()
    {
        return view('pertandingan/ketua/daftar_nilai_seni', [
            'title' => 'Daftar Nilai Seni',
        ]);
    }
}
