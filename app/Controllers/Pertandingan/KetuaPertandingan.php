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

        // Parse data_waktu from pertandingan record
        $dataWaktu = null;
        if (!empty($pertandingan->data_waktu)) {
            $dataWaktu = is_string($pertandingan->data_waktu)
                ? json_decode($pertandingan->data_waktu)
                : $pertandingan->data_waktu;
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
     */
    private function recalculateNilaiAkhirSeni(int $idPenampilanSeni): void
    {
        $db = \Config\Database::connect();

        $rows = $db->table('penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->get()->getResult();

        if (empty($rows)) return;

        $nilaiAkhirList = [];
        $totalNilaiList = [];
        $totalHukuman = 0;

        foreach ($rows as $row) {
            $parsed = json_decode($row->penilaian);
            if ($parsed && isset($parsed->penilaian->ringkasan)) {
                $nilaiAkhirList[] = (float) ($parsed->penilaian->ringkasan->nilai_akhir ?? 0);
                $totalNilaiList[] = (float) ($parsed->penilaian->ringkasan->total_nilai ?? 0);
                $totalHukuman = (float) ($parsed->penilaian->ringkasan->total_hukuman ?? 0);
            }
        }

        if (empty($totalNilaiList)) return;

        // Median total_nilai
        sort($totalNilaiList);
        $count = count($totalNilaiList);
        $mid = (int) floor($count / 2);
        $medianTotalNilai = ($count % 2 === 0)
            ? ($totalNilaiList[$mid - 1] + $totalNilaiList[$mid]) / 2
            : $totalNilaiList[$mid];

        // Median nilai_akhir
        sort($nilaiAkhirList);
        $countNA = count($nilaiAkhirList);
        $midNA = (int) floor($countNA / 2);
        $medianNilaiAkhir = ($countNA % 2 === 0)
            ? ($nilaiAkhirList[$midNA - 1] + $nilaiAkhirList[$midNA]) / 2
            : $nilaiAkhirList[$midNA];

        // Standar deviasi total_nilai
        $mean = array_sum($totalNilaiList) / $count;
        $sumSquares = 0;
        foreach ($totalNilaiList as $val) {
            $sumSquares += ($val - $mean) ** 2;
        }
        $stdDev = sqrt($sumSquares / $count);

        $db->table('penampilan_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->update([
                'nilai_akhir' => round($medianNilaiAkhir, 4),
                'catatan_nilai_sama' => json_encode([
                    'median'          => round($medianTotalNilai, 6),
                    'hukuman'         => round($totalHukuman, 4),
                    'standar_deviasi' => round($stdDev, 6),
                ]),
            ]);
    }

    /**
     * Toggle akses penilaian seni (buka/tutup).
     * Parity legacy: ganti_akses_penilaian($id_penampilan_seni)
     * JS sends {akses_penilaian: 'dibuka'|'ditutup'} explicitly.
     */
    public function gantiAksesPenilaian(int $idPenampilanSeni)
    {
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

        // Update status
        $db = \Config\Database::connect();
        $statusBaru = ($hasil === 'batal') ? 'batal' : 'selesai';
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

            // Apply via prosesKp
            $this->penilaianTandingModel->prosesKp($pertandingan, $hasil, $ronde, $mode, $jumlah, $this->tandingService);

            // Refresh score and emit
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
