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

    /**
     * Verifikasi bahwa penampilan_seni tertentu memang ada di gelanggang KP yang login.
     * Mencegah cross-gelanggang access (CRITICAL #1) — KP dari Gelanggang A tidak boleh
     * memodifikasi entitas di Gelanggang B.
     *
     * @return int id_gelanggang dari penampilan, atau 0 bila tidak terhubung jadwal
     */
    private function getGelanggangIdFromPenampilanSeni(int $idPenampilanSeni): int
    {
        $db = \Config\Database::connect();

        // Coba via pool path: detail_jadwal_seni.id_penampilan_seni
        $poolRow = $db->table('jadwal_seni js')
            ->select('js.id_gelanggang')
            ->join('detail_jadwal_seni djs', 'djs.id_jadwal_seni = js.id_jadwal_seni')
            ->where('djs.id_penampilan_seni', $idPenampilanSeni)
            ->get(1)->getRow();

        if ($poolRow !== null) {
            return (int) $poolRow->id_gelanggang;
        }

        // Coba via battle path: battle_seni.id_penampilan_seni_biru / _merah
        $battleRow = $db->table('jadwal_seni js')
            ->select('js.id_gelanggang')
            ->join('detail_jadwal_seni djs', 'djs.id_jadwal_seni = js.id_jadwal_seni')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')
            ->groupStart()
                ->where('bs.id_penampilan_seni_biru', $idPenampilanSeni)
                ->orWhere('bs.id_penampilan_seni_merah', $idPenampilanSeni)
            ->groupEnd()
            ->get(1)->getRow();

        return $battleRow !== null ? (int) $battleRow->id_gelanggang : 0;
    }

    /**
     * Guard: pastikan penampilan_seni milik gelanggang KP yang login.
     * Return null jika OK; return JSON response error jika gelanggang mismatch.
     */
    private function guardSeniGelanggang(int $idPenampilanSeni)
    {
        $idGelanggangPenampilan = $this->getGelanggangIdFromPenampilanSeni($idPenampilanSeni);

        if ($idGelanggangPenampilan === 0) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Penampilan tidak terhubung dengan jadwal di gelanggang manapun.',
            ]);
        }

        if ($idGelanggangPenampilan !== $this->idGelanggang()) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Akses ditolak: penampilan ini bukan di gelanggang Anda.',
            ]);
        }

        return null;
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
     * Halaman monitoring nilai KP tanding — hanya tabel monitoring (tanpa dewan).
     * Parity legacy: Ketua_pertandingan::tanding($versi)
     */
    public function monitoringTanding(string $theme = 'dark')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $idP = (int) $pertandingan->id_pertandingan;

        $ringkasan = $pertandingan->ringkasan_nilai
            ? json_decode($pertandingan->ringkasan_nilai)
            : null;

        $dataNilai = $this->tandingService->getDataNilaiKp($idP, $this->penilaianTandingModel);
        $verifikasiBerlangsung = $this->pertandinganModel->getVerifikasiBerlangsung($idP);
        $riwayatVerifikasi = $this->pertandinganModel->getRiwayatVerifikasi($idP);

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/ketua/tanding/persilat/monitoring', [
            'title'                   => 'Monitor Nilai - Tanding',
            'pertandingan'            => $pertandingan,
            'ringkasan'               => $ringkasan,
            'atlet_merah'             => $this->pertandinganModel->getAtletPertandingan($idP, 'merah'),
            'atlet_biru'              => $this->pertandinganModel->getAtletPertandingan($idP, 'biru'),
            'data_nilai'              => $dataNilai,
            'verifikasi_berlangsung'  => $verifikasiBerlangsung,
            'riwayat_verifikasi'      => $riwayatVerifikasi,
            'jawaban_riwayat_verifikasi' => $this->buildJawabanRiwayat($riwayatVerifikasi),
            'theme'                   => $theme,
        ]);
    }

    /**
     * Halaman dewan kontrol KP tanding — hanya tombol hukuman (tanpa monitoring).
     * Parity legacy: Ketua_pertandingan::tanding($versi)
     */
    public function dewanTanding(string $theme = 'dark')
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        $idP = (int) $pertandingan->id_pertandingan;

        $ringkasan = $pertandingan->ringkasan_nilai
            ? json_decode($pertandingan->ringkasan_nilai)
            : null;

        $dataNilai = $this->tandingService->getDataNilaiKp($idP, $this->penilaianTandingModel);
        $verifikasiBerlangsung = $this->pertandinganModel->getVerifikasiBerlangsung($idP);
        $riwayatVerifikasi = $this->pertandinganModel->getRiwayatVerifikasi($idP);

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/ketua/tanding/persilat/dewan', [
            'title'                   => 'Dewan Kontrol - Tanding',
            'pertandingan'            => $pertandingan,
            'ringkasan'               => $ringkasan,
            'atlet_merah'             => $this->pertandinganModel->getAtletPertandingan($idP, 'merah'),
            'atlet_biru'              => $this->pertandinganModel->getAtletPertandingan($idP, 'biru'),
            'data_nilai'              => $dataNilai,
            'verifikasi_berlangsung'  => $verifikasiBerlangsung,
            'riwayat_verifikasi'      => $riwayatVerifikasi,
            'theme'                   => $theme,
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
        realtime_emit_nilai_update($idPertandingan, [
            'skor_merah' => (int) $fresh->skor_merah,
            'skor_biru'  => (int) $fresh->skor_biru,
            'ronde'      => (string) $fresh->ronde_pertandingan,
            'mode'       => $mode,
            'sudut'      => $sudut,
        ]);

        return $this->jsonResponse([
            'status'     => true,
            'skor_merah' => (int) $fresh->skor_merah,
            'skor_biru'  => (int) $fresh->skor_biru,
            'ringkasan'  => $fresh->ringkasan_nilai ? json_decode($fresh->ringkasan_nilai) : null,
        ]);
    }

    /**
     * Polling status partai tanding.
     * Returns: skor, ringkasan, data_nilai (per juri), data_waktu, verifikasi state.
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

        $idP = (int) $pertandingan->id_pertandingan;

        // Parse data_waktu from pertandingan record + inject server_now_ms (drift compensation)
        helper('timer');
        $dataWaktu = null;
        if (!empty($pertandingan->data_waktu)) {
            $decoded = is_string($pertandingan->data_waktu)
                ? json_decode($pertandingan->data_waktu, true)
                : (array) $pertandingan->data_waktu;
            $dataWaktu = inject_server_now_ms($decoded);
        }

        $riwayatVerif = $this->pertandinganModel->getRiwayatVerifikasi($idP);

        return $this->jsonResponse([
            'status'                  => false,
            'skor_merah'              => (int) $pertandingan->skor_merah,
            'skor_biru'               => (int) $pertandingan->skor_biru,
            'ronde'                   => (string) $pertandingan->ronde_pertandingan,
            'pertandingan'            => $pertandingan,
            'ringkasan'               => $pertandingan->ringkasan_nilai ? json_decode($pertandingan->ringkasan_nilai) : null,
            'data_nilai'              => $this->tandingService->getDataNilaiKp($idP, $this->penilaianTandingModel),
            'data_waktu'              => $dataWaktu,
            'server_now_ms'           => (int) round(microtime(true) * 1000),
            'verifikasi_pertandingan_berlangsung' => $this->pertandinganModel->getVerifikasiBerlangsung($idP),
            'riwayat_verifikasi_pertandingan'     => $riwayatVerif,
            'jawaban_riwayat_verifikasi_pertandingan' => $this->buildJawabanRiwayat($riwayatVerif),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Build jawaban riwayat verifikasi keyed by id_verifikasi_pertandingan.
     * Parity legacy: jawaban_riwayat_verifikasi_pertandingan[id] = [{jawaban: ...}, ...]
     */
    private function buildJawabanRiwayat(array $riwayat): object
    {
        $result = new \stdClass();
        foreach ($riwayat as $row) {
            $id = $row->id_verifikasi_pertandingan ?? null;
            if ($id === null) continue;

            // jawaban is already attached by getRiwayatVerifikasi as array of values
            // Re-wrap as objects for parity with legacy format
            $jawabanArr = $row->jawaban ?? [];
            $result->$id = array_map(fn($j) => (object) ['jawaban' => $j], $jawabanArr);
        }
        return $result;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SENI
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Halaman monitoring nilai seni — pool & battle.
     * Parity legacy: Ketua_pertandingan::seni($theme)
     */
    public function seni(string $theme = 'dark')
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return redirect()->to('/ketua-pertandingan')->with('info', 'Tidak ada penampilan seni berlangsung.');
        }

        $idPenampilan = (int) $penampilan->id_penampilan_seni;
        $sistemPenampilan = $penampilan->sistem_penampilan ?? 'pool';

        // Retrieve full scoring data (parity legacy _retrieve_data_seni)
        $scoringData = $this->retrieveDataSeni($penampilan);

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/ketua/seni/persilat/' . $sistemPenampilan . '/' . $theme, array_merge($scoringData, [
            'title' => 'KP Monitoring Seni',
            'theme' => $theme,
        ]));
    }

    /**
     * Halaman dewan pertandingan seni — button controller (hukuman, akses, diskualifikasi).
     * Parity legacy: Ketua_pertandingan::button_controller_seni($theme)
     * Halaman terpisah dari monitoring.
     */
    public function dewanSeni(string $theme = 'dark')
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return redirect()->to('/ketua-pertandingan')->with('info', 'Tidak ada penampilan seni berlangsung.');
        }

        $sistemPenampilan = $penampilan->sistem_penampilan ?? 'pool';

        // Retrieve full scoring data
        $scoringData = $this->retrieveDataSeni($penampilan);

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/ketua/seni/persilat/dewan_' . $theme, array_merge($scoringData, [
            'title' => 'KP Dewan Seni',
            'theme' => $theme,
        ]));
    }

    /**
     * Retrieve full scoring data for seni — parity legacy _retrieve_data_seni + get_data_scoring.
     * Returns: penampilan_seni_berlangsung, semua_penampilan_seni, data_nilai (grouped),
     *          jenis_unsur_nilai, battle_seni (for battle mode).
     */
    private function retrieveDataSeni(object $penampilan): array
    {
        $db = \Config\Database::connect();
        $idPenampilan = (int) $penampilan->id_penampilan_seni;
        $sistemPenampilan = $penampilan->sistem_penampilan ?? 'pool';

        $dataPenilaianSeni = [];
        $battleSeni = null;

        if ($sistemPenampilan === 'pool') {
            // Pool: get all penilaian in same kompetisi
            $dataPenilaianSeni = $db->table('penilaian_seni ps')
                ->select('ps.*')
                ->join('penampilan_seni pns', 'pns.id_penampilan_seni = ps.id_penampilan_seni')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = pns.id_kelompok_peserta_seni')
                ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
                ->where('ks.id_kompetisi_seni', $penampilan->id_kompetisi_seni)
                ->get()->getResult();
        } else {
            // Battle: find battle_seni record, get penilaian for both corners
            $battleSeni = $db->table('battle_seni')
                ->where('id_penampilan_seni_biru', $idPenampilan)
                ->orWhere('id_penampilan_seni_merah', $idPenampilan)
                ->get(1)->getRow();

            if ($battleSeni !== null) {
                if ($battleSeni->id_penampilan_seni_biru !== null) {
                    $rows = $db->table('penilaian_seni')
                        ->where('id_penampilan_seni', $battleSeni->id_penampilan_seni_biru)
                        ->get()->getResult();
                    $dataPenilaianSeni = array_merge($dataPenilaianSeni, $rows);
                }
                if ($battleSeni->id_penampilan_seni_merah !== null) {
                    $rows = $db->table('penilaian_seni')
                        ->where('id_penampilan_seni', $battleSeni->id_penampilan_seni_merah)
                        ->get()->getResult();
                    $dataPenilaianSeni = array_merge($dataPenilaianSeni, $rows);
                }
            }
        }

        // Group penilaian by id_penampilan_seni (parity legacy kelompokkan_penilaian_seni)
        $dataNilai = [];
        foreach ($dataPenilaianSeni as $row) {
            $dataNilai[(int) $row->id_penampilan_seni][] = $row;
        }

        // Get jenis_unsur_nilai from first penilaian JSON
        $jenisUnsurNilai = [];
        if (!empty($dataPenilaianSeni) && isset($dataPenilaianSeni[0]->penilaian)) {
            $parsed = json_decode($dataPenilaianSeni[0]->penilaian);
            if ($parsed && isset($parsed->penilaian->unsur_nilai)) {
                $jenisUnsurNilai = array_keys((array) $parsed->penilaian->unsur_nilai);
            }
        }

        // Get semua_penampilan_seni (all performers in same pool/battle)
        $semuaPenampilanSeni = $this->getSemuaPenampilanSeniSerupa($penampilan, $battleSeni);

        // Get partai info for battle
        $partaiSeni = $battleSeni;

        return [
            'penampilan_seni_berlangsung' => $penampilan,
            'semua_penampilan_seni'       => $semuaPenampilanSeni,
            'data_nilai'                  => $dataNilai,
            'jenis_unsur_nilai'           => $jenisUnsurNilai,
            'battle_seni'                 => $battleSeni,
            'akses_penilaian'             => $penampilan->akses_penilaian ?? 'dibuka',
        ];
    }

    /**
     * Get all penampilan seni in same pool or battle.
     * Parity legacy: get_semua_penampilan_seni_serupa()
     */
    private function getSemuaPenampilanSeniSerupa(object $penampilan, ?object $battleSeni): array
    {
        $db = \Config\Database::connect();

        // Subquery parity legacy: GROUP_CONCAT nama pendaftar sebagai anggota_kelompok_peserta_seni
        $subAnggota = "(SELECT GROUP_CONCAT(CONCAT_WS(' ', pendaftar.nama_pendaftar) SEPARATOR ' ,<br> ')
            FROM pendaftar
            JOIN peserta_seni ON peserta_seni.id_pendaftar = pendaftar.id_pendaftar
            WHERE peserta_seni.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) as anggota_kelompok_peserta_seni";

        if (($penampilan->sistem_penampilan ?? 'pool') === 'pool') {
            // Pool: all penampilan in same kompetisi
            return $db->table('penampilan_seni ps')
                ->select("ps.*, k.nama_kontingen, {$subAnggota}", false)
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
                ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
                ->where('ks.id_kompetisi_seni', $penampilan->id_kompetisi_seni)
                ->orderBy('ps.id_penampilan_seni', 'ASC')
                ->get()->getResult();
        } else {
            // Battle: only the two corners
            $ids = [];
            if ($battleSeni !== null) {
                if ($battleSeni->id_penampilan_seni_biru !== null) {
                    $ids[] = (int) $battleSeni->id_penampilan_seni_biru;
                }
                if ($battleSeni->id_penampilan_seni_merah !== null) {
                    $ids[] = (int) $battleSeni->id_penampilan_seni_merah;
                }
            }

            if (empty($ids)) return [];

            return $db->table('penampilan_seni ps')
                ->select("ps.*, k.nama_kontingen, {$subAnggota}", false)
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
                ->whereIn('ps.id_penampilan_seni', $ids)
                ->get()->getResult();
        }
    }

    /**
     * AJAX: Input/edit hukuman seni dari KP.
     * Parity legacy: edit_penilaian_seni($id_penampilan_seni)
     * Legacy approach: client modifies all juri penilaian JSON locally,
     * then sends the full modified data_nilai to server to be saved.
     */
    public function editPenilaianSeni(int $idPenampilanSeni)
    {
        // CRITICAL #1: Guard cross-gelanggang access
        if (($guard = $this->guardSeniGelanggang($idPenampilanSeni)) !== null) {
            return $guard;
        }

        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        $dataNilaiJson = $this->request->getPost('data_nilai');
        if (empty($dataNilaiJson)) {
            return $this->jsonResponse(['status' => false, 'message' => 'data_nilai kosong.']);
        }

        $dataNilai = json_decode($dataNilaiJson);
        if ($dataNilai === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'data_nilai invalid JSON.']);
        }

        $db = \Config\Database::connect();

        // Update each juri penilaian record
        foreach ($dataNilai as $penilaianJuri) {
            if (!isset($penilaianJuri->id_penilaian_seni) || !isset($penilaianJuri->penilaian)) {
                continue;
            }

            $penilaianDecoded = json_decode($penilaianJuri->penilaian);
            $nilaiAkhir = $penilaianDecoded->penilaian->ringkasan->nilai_akhir ?? null;
            $totalHukuman = $penilaianDecoded->penilaian->ringkasan->total_hukuman ?? 0;

            $db->table('penilaian_seni')
                ->where('id_penilaian_seni', (int) $penilaianJuri->id_penilaian_seni)
                ->update([
                    'penilaian' => $penilaianJuri->penilaian,
                ]);
        }

        // Update nilai_akhir di penampilan_seni (median-based, parity legacy)
        $this->recalculateNilaiAkhirSeni($idPenampilanSeni);

        // Retrieve fresh data and return
        $penampilanFresh = $this->penampilanSeniModel->getFullPenampilanPublic($idPenampilanSeni);
        $scoringData = $this->retrieveDataSeni($penampilanFresh);

        return $this->jsonResponse([
            'status' => true,
            'penampilan_seni_berlangsung' => $penampilanFresh,
            'data_nilai' => $scoringData['data_nilai'],
            'semua_penampilan_seni' => $scoringData['semua_penampilan_seni'],
        ]);
    }

    /**
     * Recalculate nilai_akhir for a penampilan_seni based on median of juri scores.
     * Also updates terpilih flag and median_kebenaran.
     * 
     * MEDIUM: Delegated to PersilatSeniService (parity dengan SekretarisPertandingan flow).
     * Service handles: median, hukuman, standar_deviasi, median_kebenaran, terpilih flag.
     */
    private function recalculateNilaiAkhirSeni(int $idPenampilanSeni): void
    {
        $db = \Config\Database::connect();

        // Fetch fresh penampilan + all juri rows
        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);
        if ($penampilan === null) return;

        $rows = $db->table('penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->get()->getResult();

        if (empty($rows)) return;

        // Delegate to service — handles median, terpilih, catatan_nilai_sama
        $nilaiAkhir = $this->seniService->hitungNilaiAkhir($penampilan, $rows);

        if ($nilaiAkhir !== false) {
            $db->table('penampilan_seni')
                ->where('id_penampilan_seni', $idPenampilanSeni)
                ->update(['nilai_akhir' => round((float) $nilaiAkhir, 4)]);
        }
    }

    /**
     * Toggle akses penilaian seni (buka/tutup).
     * Parity legacy: ganti_akses_penilaian($id_penampilan_seni)
     * JS sends {akses_penilaian: 'dibuka'|'ditutup'} explicitly.
     */
    public function gantiAksesPenilaian(int $idPenampilanSeni)
    {
        // CRITICAL #1: Guard cross-gelanggang access
        if (($guard = $this->guardSeniGelanggang($idPenampilanSeni)) !== null) {
            return $guard;
        }

        $penampilan = $this->penampilanSeniModel->find($idPenampilanSeni);

        if ($penampilan === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        // Legacy: JS sends the desired new value directly
        $newAkses = $this->request->getPost('akses_penilaian');
        if (!in_array($newAkses, ['dibuka', 'ditutup'], true)) {
            // Fallback: toggle
            $currentAkses = $penampilan->akses_penilaian ?? 'dibuka';
            $newAkses = ($currentAkses === 'dibuka') ? 'ditutup' : 'dibuka';
        }

        $db = \Config\Database::connect();
        $db->table('penampilan_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->update(['akses_penilaian' => $newAkses]);

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
        // CRITICAL #1: Guard cross-gelanggang access
        if (($guard = $this->guardSeniGelanggang($idPenampilanSeni)) !== null) {
            return $guard;
        }

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
        // CRITICAL #1: Guard cross-gelanggang access
        if (($guard = $this->guardSeniGelanggang($idPenampilanSeni)) !== null) {
            return $guard;
        }

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
     * Polling status penampilan seni — return all scoring data.
     * Parity legacy: refresh_status_seni($id_penampilan_seni)
     */
    public function refreshStatusSeni(?int $idPenampilanSeni = null)
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            // No active performance
            return $this->jsonResponse(['status' => true, 'reload' => ($idPenampilanSeni !== null)]);
        }

        // CRITICAL #1: Guard cross-gelanggang access
        if (($guard = $this->guardSeniGelanggang((int) $penampilan->id_penampilan_seni)) !== null) {
            return $guard;
        }

        if ((int) $penampilan->id_penampilan_seni === (int) $idPenampilanSeni
            || ($penampilan->diskualifikasi ?? 0) == 1
        ) {
            // Same penampilan — return full data update
            $scoringData = $this->retrieveDataSeni($penampilan);

            // Status ready juri
            $statusReadyJuri = [];
            $dataNilaiCurrent = $scoringData['data_nilai'][(int) $penampilan->id_penampilan_seni] ?? [];
            foreach ($dataNilaiCurrent as $row) {
                $statusReadyJuri[] = [
                    'id_perangkat_pertandingan' => $row->id_perangkat_pertandingan,
                    'status_ready'             => $row->status_ready ?? 0,
                ];
            }

            return $this->jsonResponse([
                'status'                    => false,
                'data_nilai'                => $scoringData['data_nilai'],
                'penampilan_seni_berlangsung' => $penampilan,
                'semua_penampilan_seni'     => $scoringData['semua_penampilan_seni'],
                'status_ready_juri'         => $statusReadyJuri,
            ], JSON_NUMERIC_CHECK);
        } else {
            // Different penampilan — reload page
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  VERIFIKASI PERTANDINGAN (Jatuhan & Pelanggaran)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Start verifikasi session — create record, emit socket event to juri.
     * Parity legacy: verifikasi_pertandingan/create
     */
    public function createVerifikasi(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->jsonResponse(['status' => false, 'message' => 'Partai tidak aktif.']);
        }

        $jenis = (string) $this->request->getPost('jenis_verifikasi');
        if (!in_array($jenis, ['jatuhan', 'pelanggaran'], true)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Jenis verifikasi tidak valid.']);
        }

        // Check if already have active verifikasi
        $existing = $this->pertandinganModel->getVerifikasiBerlangsung($idPertandingan);
        if ($existing) {
            return $this->jsonResponse(['status' => true, 'verifikasi' => $existing, 'message' => 'Verifikasi sudah berjalan.']);
        }

        // Create verifikasi record
        $db = \Config\Database::connect();
        $db->table('verifikasi_pertandingan')->insert([
            'id_pertandingan'     => $idPertandingan,
            'jenis_verifikasi'    => $jenis,
            'ronde_pertandingan'  => (string) $pertandingan->ronde_pertandingan,
            'status'              => 'berlangsung',
            'waktu'               => (string) time(),
        ]);

        $idVerifikasi = $db->insertID();

        // Emit socket event to notify Juri to answer
        helper('realtime');
        if ($jenis === 'jatuhan') {
            realtime_emit_verifikasi_jatuhan($idPertandingan, [
                'id_verifikasi' => $idVerifikasi,
                'jenis'         => $jenis,
            ]);
        } else {
            realtime_emit_verifikasi_pelanggaran($idPertandingan, [
                'id_verifikasi' => $idVerifikasi,
                'jenis'         => $jenis,
            ]);
        }

        return $this->jsonResponse([
            'status'     => true,
            'verifikasi' => (object) [
                'id_verifikasi_pertandingan' => $idVerifikasi,
                'jenis_verifikasi'           => $jenis,
                'status'                     => 'berlangsung',
            ],
        ]);
    }

    /**
     * Update verifikasi result (tetapkan/batalkan).
     * Parity legacy: verifikasi_pertandingan/update
     */
    public function updateVerifikasi(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->jsonResponse(['status' => false, 'message' => 'Partai tidak aktif.']);
        }

        $hasil = (string) $this->request->getPost('hasil'); // 'biru'|'merah'|'invalid'|'batal'
        $jenis = (string) $this->request->getPost('jenis_verifikasi');

        $verifikasi = $this->pertandinganModel->getVerifikasiBerlangsung($idPertandingan);
        if (!$verifikasi) {
            return $this->jsonResponse(['status' => false, 'message' => 'Tidak ada verifikasi aktif.']);
        }

        // CRITICAL #3: Atomic — verifikasi update + skor application di satu transaksi.
        // Tanpa ini, jika prosesKp gagal, verifikasi sudah berstatus 'selesai' tapi skor tidak masuk.
        $db = \Config\Database::connect();
        $statusBaru = ($hasil === 'batal') ? 'batal' : 'selesai';

        $db->transStart();

        $db->table('verifikasi_pertandingan')
            ->where('id_verifikasi_pertandingan', $verifikasi->id_verifikasi_pertandingan)
            ->update([
                'status'            => $statusBaru,
                'hasil_verifikasi'  => $hasil,
            ]);

        // If valid (biru/merah), apply jatuhan score
        if ($hasil === 'biru' || $hasil === 'merah') {
            $mode = $verifikasi->jenis_verifikasi; // 'jatuhan' or 'pelanggaran'
            $jumlah = ($mode === 'jatuhan') ? 3 : -5; // jatuhan +3, pelanggaran = peringatan_1 (-5)
            $ronde = (string) $pertandingan->ronde_pertandingan;

            // Apply via prosesKp (model method punya transStart/transComplete sendiri,
            // CI4 menyatukan nested transactions menjadi outer transaction)
            $this->penilaianTandingModel->prosesKp($pertandingan, $hasil, $ronde, $mode, $jumlah, $this->tandingService);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->jsonResponse([
                'status'  => false,
                'message' => 'Gagal memperbarui verifikasi. Silakan coba lagi.',
            ]);
        }

        // Refresh & emit hanya jika hasil valid (di luar transaksi — read-only operation)
        if ($hasil === 'biru' || $hasil === 'merah') {
            $fresh = $this->pertandinganModel->find($idPertandingan);
            helper('realtime');
            realtime_emit_skor(
                $idPertandingan,
                (int) $fresh->skor_merah,
                (int) $fresh->skor_biru,
                (string) $fresh->ronde_pertandingan
            );
        }

        return $this->jsonResponse(['status' => true, 'hasil' => $hasil]);
    }

    /**
     * Get jawaban juri for active verifikasi (polling by KP).
     * Parity legacy: get_jawaban_verifikasi_pertandingan
     */
    public function getJawabanVerifikasi(int $idPertandingan)
    {
        $verifikasi = $this->pertandinganModel->getVerifikasiBerlangsung($idPertandingan);

        if (!$verifikasi) {
            return $this->jsonResponse(['status' => false, 'jawaban' => []]);
        }

        $db = \Config\Database::connect();
        $jawaban = $db->table('jawaban_verifikasi_pertandingan')
            ->where('id_verifikasi_pertandingan', $verifikasi->id_verifikasi_pertandingan)
            ->orderBy('id_perangkat_pertandingan', 'ASC')
            ->get()
            ->getResult();

        return $this->jsonResponse([
            'status'  => true,
            'jawaban' => $jawaban,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  DAFTAR NILAI
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Daftar nilai tanding (semua partai di gelanggang ini).
     * Parity legacy: daftar_nilai_tanding
     * MEDIUM #6: Simple server-side render (defer AJAX to future iteration)
     */
    public function daftarNilaiTanding()
    {
        $db = \Config\Database::connect();

        // Join pertandingan → detail_jadwal_tanding → jadwal_tanding → pendaftar → kontingen
        $pertandinganList = $db->table('detail_jadwal_tanding djt')
            ->select('p.id_pertandingan, djt.nomor_partai, p.skor_merah, p.skor_biru, p.pemenang, p.status_pertandingan, p.jenis_kemenangan,
                      pd_merah.nama_pendaftar as nama_merah, pd_biru.nama_pendaftar as nama_biru,
                      k_merah.nama_kontingen as kontingen_merah, k_biru.nama_kontingen as kontingen_biru,
                      jt.babak')
            ->join('pertandingan p', 'p.id_pertandingan = djt.id_pertandingan', 'inner')
            ->join('jadwal_tanding jt', 'jt.id_jadwal_tanding = djt.id_jadwal_tanding', 'left')
            ->join('peserta_tanding pt_merah', 'pt_merah.id_peserta_tanding = p.id_atlet_merah', 'left')
            ->join('peserta_tanding pt_biru', 'pt_biru.id_peserta_tanding = p.id_atlet_biru', 'left')
            ->join('pendaftar pd_merah', 'pd_merah.id_pendaftar = pt_merah.id_pendaftar', 'left')
            ->join('pendaftar pd_biru', 'pd_biru.id_pendaftar = pt_biru.id_pendaftar', 'left')
            ->join('kontingen k_merah', 'k_merah.id_kontingen = pd_merah.id_kontingen', 'left')
            ->join('kontingen k_biru', 'k_biru.id_kontingen = pd_biru.id_kontingen', 'left')
            ->where('jt.id_gelanggang', $this->idGelanggang())
            ->orderBy('djt.nomor_partai', 'ASC')
            ->get()
            ->getResult();

        return view('pertandingan/ketua/daftar_nilai_tanding', [
            'title' => 'Daftar Nilai Tanding',
            'pertandinganList' => $pertandinganList,
        ]);
    }

    /**
     * Daftar nilai seni (semua penampilan di gelanggang ini).
     * Parity legacy: daftar_nilai_seni
     * MEDIUM #6: Simple server-side render (defer AJAX to future iteration)
     */
    public function daftarNilaiSeni()
    {
        $db = \Config\Database::connect();

        // Pool: penampilan_seni → kelompok_peserta_seni → pendaftar → kontingen + jadwal_seni
        $poolList = $db->table('penampilan_seni ps')
            ->select('ps.id_penampilan_seni, ps.nilai_akhir, ps.diskualifikasi, ps.alasan_diskualifikasi,
                      djs.nomor_urut, pd.nama_pendaftar, k.nama_kontingen,
                      js.id_gelanggang, "pool" as sistem')
            ->join('detail_jadwal_seni djs', 'djs.id_penampilan_seni = ps.id_penampilan_seni', 'inner')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni', 'inner')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->join('peserta_seni pst', 'pst.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni', 'left')
            ->join('pendaftar pd', 'pd.id_pendaftar = pst.id_pendaftar', 'left')
            ->join('kontingen k', 'k.id_kontingen = pd.id_kontingen', 'left')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('djs.id_battle_seni IS NULL')
            ->orderBy('djs.nomor_urut', 'ASC')
            ->get()
            ->getResult();

        // Battle: battle_seni dengan penampilan_seni biru/merah + jadwal_seni
        $battleList = $db->table('battle_seni bs')
            ->select('ps_biru.id_penampilan_seni as id_biru, ps_biru.nilai_akhir as nilai_biru, ps_biru.diskualifikasi as dq_biru,
                      ps_merah.id_penampilan_seni as id_merah, ps_merah.nilai_akhir as nilai_merah, ps_merah.diskualifikasi as dq_merah,
                      djs.nomor_urut,
                      pd_biru.nama_pendaftar as nama_biru, k_biru.nama_kontingen as kontingen_biru,
                      pd_merah.nama_pendaftar as nama_merah, k_merah.nama_kontingen as kontingen_merah,
                      bs.babak, bs.jenis_kemenangan, js.id_gelanggang, "battle" as sistem')
            ->join('detail_jadwal_seni djs', 'djs.id_detail_jadwal_seni = bs.id_detail_jadwal_seni', 'inner')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni', 'inner')
            ->join('penampilan_seni ps_biru', 'ps_biru.id_penampilan_seni = bs.id_penampilan_seni_biru', 'left')
            ->join('penampilan_seni ps_merah', 'ps_merah.id_penampilan_seni = bs.id_penampilan_seni_merah', 'left')
            ->join('kelompok_peserta_seni kps_biru', 'kps_biru.id_kelompok_peserta_seni = ps_biru.id_kelompok_peserta_seni', 'left')
            ->join('kelompok_peserta_seni kps_merah', 'kps_merah.id_kelompok_peserta_seni = ps_merah.id_kelompok_peserta_seni', 'left')
            ->join('peserta_seni pst_biru', 'pst_biru.id_kelompok_peserta_seni = kps_biru.id_kelompok_peserta_seni', 'left')
            ->join('peserta_seni pst_merah', 'pst_merah.id_kelompok_peserta_seni = kps_merah.id_kelompok_peserta_seni', 'left')
            ->join('pendaftar pd_biru', 'pd_biru.id_pendaftar = pst_biru.id_pendaftar', 'left')
            ->join('pendaftar pd_merah', 'pd_merah.id_pendaftar = pst_merah.id_pendaftar', 'left')
            ->join('kontingen k_biru', 'k_biru.id_kontingen = pd_biru.id_kontingen', 'left')
            ->join('kontingen k_merah', 'k_merah.id_kontingen = pd_merah.id_kontingen', 'left')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->orderBy('djs.nomor_urut', 'ASC')
            ->get()
            ->getResult();

        return view('pertandingan/ketua/daftar_nilai_seni', [
            'title' => 'Daftar Nilai Seni',
            'poolList' => $poolList,
            'battleList' => $battleList,
        ]);
    }

    /**
     * API: Get daftar nilai tanding untuk DataTables.
     * MEDIUM #6: Implementation for daftar_nilai_tanding table data.
     */
    public function apiDaftarNilaiTanding()
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('pertandingan p')
            ->select('p.id_pertandingan, p.no_partai, p.skor_merah, p.skor_biru, p.pemenang, p.status_pertandingan,
                      atlet_merah.nama_lengkap as nama_merah, atlet_biru.nama_lengkap as nama_biru,
                      kontingen_merah.nama_kontingen as kontingen_merah, kontingen_biru.nama_kontingen as kontingen_biru,
                      jt.babak')
            ->join('jadwal_tanding jt', 'jt.id_jadwal_tanding = p.id_jadwal_tanding', 'left')
            ->join('peserta atlet_merah', 'atlet_merah.id_peserta = p.id_peserta_merah', 'left')
            ->join('peserta atlet_biru', 'atlet_biru.id_peserta = p.id_peserta_biru', 'left')
            ->join('kontingen kontingen_merah', 'kontingen_merah.id_kontingen = atlet_merah.id_kontingen', 'left')
            ->join('kontingen kontingen_biru', 'kontingen_biru.id_kontingen = atlet_biru.id_kontingen', 'left')
            ->where('jt.id_gelanggang', $this->idGelanggang())
            ->orderBy('p.no_partai', 'ASC');

        $result = $builder->get()->getResult();

        $data = [];
        foreach ($result as $idx => $row) {
            $skor = "{$row->skor_biru} - {$row->skor_merah}";
            $pemenang = $row->pemenang === 'biru' ? 'Biru' : ($row->pemenang === 'merah' ? 'Merah' : '-');
            $status = ucfirst($row->status_pertandingan ?? 'belum_mulai');

            $data[] = [
                'no' => $idx + 1,
                'partai' => "Partai {$row->no_partai} ({$row->babak})",
                'atlet_biru' => ($row->nama_biru ?? '-') . '<br><small class="text-muted">' . ($row->kontingen_biru ?? '') . '</small>',
                'atlet_merah' => ($row->nama_merah ?? '-') . '<br><small class="text-muted">' . ($row->kontingen_merah ?? '') . '</small>',
                'skor' => $skor,
                'pemenang' => $pemenang,
                'status' => $status,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }

    /**
     * API: Get daftar nilai seni untuk DataTables.
     * MEDIUM #6: Implementation for daftar_nilai_seni table data.
     */
    public function apiDaftarNilaiSeni()
    {
        $db = \Config\Database::connect();
        
        // Pool seni
        $builderPool = $db->table('penampilan_seni ps')
            ->select('ps.id_penampilan_seni, ps.nilai_akhir, ps.diskualifikasi, ps.alasan_diskualifikasi,
                      djs.nama_partai, peserta.nama_lengkap as nama_peserta, kontingen.nama_kontingen,
                      js.nomor_pertandingan, js.jenis_kelamin, ku.nama_kategori_usia, "pool" as sistem')
            ->join('detail_jadwal_seni djs', 'djs.id_detail_jadwal_seni = ps.id_detail_jadwal_seni', 'left')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni', 'left')
            ->join('peserta', 'peserta.id_peserta = ps.id_peserta', 'left')
            ->join('kontingen', 'kontingen.id_kontingen = peserta.id_kontingen', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = js.id_kategori_usia', 'left')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('js.sistem_perlombaan', 'pool');

        $poolData = $builderPool->get()->getResult();

        // Battle seni (biru dan merah)
        $builderBattle = $db->table('battle_seni bs')
            ->select('ps_biru.id_penampilan_seni as id_biru, ps_biru.nilai_akhir as nilai_biru, ps_biru.diskualifikasi as dq_biru,
                      ps_merah.id_penampilan_seni as id_merah, ps_merah.nilai_akhir as nilai_merah, ps_merah.diskualifikasi as dq_merah,
                      djs.nama_partai, 
                      peserta_biru.nama_lengkap as nama_biru, kontingen_biru.nama_kontingen as kontingen_biru,
                      peserta_merah.nama_lengkap as nama_merah, kontingen_merah.nama_kontingen as kontingen_merah,
                      js.nomor_pertandingan, js.jenis_kelamin, ku.nama_kategori_usia, bs.babak, "battle" as sistem')
            ->join('penampilan_seni ps_biru', 'ps_biru.id_penampilan_seni = bs.id_penampilan_seni_biru', 'left')
            ->join('penampilan_seni ps_merah', 'ps_merah.id_penampilan_seni = bs.id_penampilan_seni_merah', 'left')
            ->join('detail_jadwal_seni djs', 'djs.id_detail_jadwal_seni = bs.id_detail_jadwal_seni', 'left')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni', 'left')
            ->join('peserta peserta_biru', 'peserta_biru.id_peserta = ps_biru.id_peserta', 'left')
            ->join('peserta peserta_merah', 'peserta_merah.id_peserta = ps_merah.id_peserta', 'left')
            ->join('kontingen kontingen_biru', 'kontingen_biru.id_kontingen = peserta_biru.id_kontingen', 'left')
            ->join('kontingen kontingen_merah', 'kontingen_merah.id_kontingen = peserta_merah.id_kontingen', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = js.id_kategori_usia', 'left')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('js.sistem_perlombaan', 'battle');

        $battleData = $builderBattle->get()->getResult();

        $data = [];
        $no = 1;

        // Pool
        foreach ($poolData as $row) {
            $kategori = "{$row->nomor_pertandingan} - {$row->nama_kategori_usia} - " . ($row->jenis_kelamin === 'L' ? 'Putra' : 'Putri');
            $dq = $row->diskualifikasi ? 'DQ' : '-';
            $nilai = $row->diskualifikasi ? '-' : number_format($row->nilai_akhir ?? 0, 2);

            $data[] = [
                'no' => $no++,
                'partai' => "{$row->nama_partai} (Pool)",
                'peserta' => ($row->nama_peserta ?? '-') . '<br><small class="text-muted">' . ($row->nama_kontingen ?? '') . '</small>',
                'kategori' => $kategori,
                'nilai_akhir' => $nilai,
                'status' => 'Selesai',
                'dq' => $dq,
            ];
        }

        // Battle
        foreach ($battleData as $row) {
            $kategori = "{$row->nomor_pertandingan} - {$row->nama_kategori_usia} - " . ($row->jenis_kelamin === 'L' ? 'Putra' : 'Putri');
            
            // Biru
            $dqBiru = $row->dq_biru ? 'DQ' : '-';
            $nilaiBiru = $row->dq_biru ? '-' : number_format($row->nilai_biru ?? 0, 2);
            $data[] = [
                'no' => $no++,
                'partai' => "{$row->nama_partai} ({$row->babak}) - Biru",
                'peserta' => ($row->nama_biru ?? '-') . '<br><small class="text-muted">' . ($row->kontingen_biru ?? '') . '</small>',
                'kategori' => $kategori,
                'nilai_akhir' => $nilaiBiru,
                'status' => 'Selesai',
                'dq' => $dqBiru,
            ];

            // Merah
            $dqMerah = $row->dq_merah ? 'DQ' : '-';
            $nilaiMerah = $row->dq_merah ? '-' : number_format($row->nilai_merah ?? 0, 2);
            $data[] = [
                'no' => $no++,
                'partai' => "{$row->nama_partai} ({$row->babak}) - Merah",
                'peserta' => ($row->nama_merah ?? '-') . '<br><small class="text-muted">' . ($row->kontingen_merah ?? '') . '</small>',
                'kategori' => $kategori,
                'nilai_akhir' => $nilaiMerah,
                'status' => 'Selesai',
                'dq' => $dqMerah,
            ];
        }

        return $this->response->setJSON(['data' => $data]);
    }
}
