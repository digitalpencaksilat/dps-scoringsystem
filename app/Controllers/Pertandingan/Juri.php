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

        // Verifikasi pertandingan berlangsung (parity legacy)
        $verifikasiPertandingan = $this->pertandinganModel->getVerifikasiBerlangsung(
            (int) $pertandingan->id_pertandingan
        );
        $jawabanVerifikasi = null;
        if ($verifikasiPertandingan !== null) {
            $jawabanVerifikasi = $this->pertandinganModel->getJawabanVerifikasiJuri(
                (int) $verifikasiPertandingan->id_verifikasi_pertandingan,
                $this->idPerangkat()
            );
        }

        return view('pertandingan/juri/tanding/persilat/' . $theme, [
            'title'                    => 'Penilaian Tanding',
            'pertandingan'             => $pertandingan,
            'data_nilai'               => [
                'merah' => json_decode($dataNilai->penilaian_merah),
                'biru'  => json_decode($dataNilai->penilaian_biru),
            ],
            'pemenang'                 => $dataNilai->pemenang,
            'atlet_merah'              => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah'),
            'atlet_biru'               => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru'),
            'verifikasi_pertandingan'  => $verifikasiPertandingan,
            'jawaban_verifikasi'       => $jawabanVerifikasi,
            'theme'                    => $theme,
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
            realtime_emit_nilai_update($idPertandingan, [
                'skor_merah' => (int) $terkini->skor_merah,
                'skor_biru'  => (int) $terkini->skor_biru,
                'ronde'      => (string) $terkini->ronde_pertandingan,
                'sudut'      => $sudut,
            ]);
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

        // Verifikasi pertandingan berlangsung (parity legacy)
        $verifikasiPertandingan = $this->pertandinganModel->getVerifikasiBerlangsung(
            (int) $pertandingan->id_pertandingan
        );
        $jawabanVerifikasi = null;
        if ($verifikasiPertandingan !== null) {
            $jawabanVerifikasi = $this->pertandinganModel->getJawabanVerifikasiJuri(
                (int) $verifikasiPertandingan->id_verifikasi_pertandingan,
                $this->idPerangkat()
            );
        }

        // Inject server_now_ms ke data_waktu untuk drift compensation di client
        helper('timer');
        $dataWaktu = null;
        if (! empty($pertandingan->data_waktu)) {
            $decoded = is_string($pertandingan->data_waktu)
                ? json_decode($pertandingan->data_waktu, true)
                : (array) $pertandingan->data_waktu;
            $dataWaktu = inject_server_now_ms($decoded);
        }

        return $this->jsonResponse([
            'status'                          => false,
            'pertandingan'                    => $pertandingan,
            'pemenang'                        => $dataNilai->pemenang ?? null,
            'data_nilai'                      => $dataNilai !== null ? [
                'merah' => json_decode($dataNilai->penilaian_merah),
                'biru'  => json_decode($dataNilai->penilaian_biru),
            ] : null,
            'data_waktu'                      => $dataWaktu,
            'server_now_ms'                   => (int) round(microtime(true) * 1000),
            'verifikasi_pertandingan'         => $verifikasiPertandingan,
            'jawaban_verifikasi_pertandingan' => $jawabanVerifikasi,
        ]);
    }

    /**
     * AJAX: Submit jawaban verifikasi jatuhan/pelanggaran dari juri.
     * Parity legacy: Juri::submit_jawaban_verifikasi_pertandingan()
     * Legacy menyimpan jawaban ke tabel jawaban_verifikasi_pertandingan.
     */
    public function submitJawabanVerifikasi(int $idPertandingan)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null || (int) $pertandingan->id_pertandingan !== $idPertandingan) {
            return $this->jsonResponse(['status' => false, 'message' => 'Partai tidak aktif.']);
        }

        // FIX #7: Accept legacy field name 'jawaban_sistem_dialog' (CI3 compat) AND new 'jawaban'.
        // Legacy juga pakai 'sah'/'tidak_sah' untuk verifikasi jatuhan — map ke biru/merah/invalid.
        $jawaban = (string) ($this->request->getPost('jawaban')
            ?? $this->request->getPost('jawaban_sistem_dialog') ?? '');

        // Map legacy values to new vocabulary
        $legacyMap = [
            'sah'         => 'biru',     // Legacy "sah" = jatuhan biru valid
            'tidak_sah'   => 'invalid',  // Legacy "tidak_sah" = invalid
            'null'        => '',
        ];
        if (isset($legacyMap[$jawaban])) {
            $jawaban = $legacyMap[$jawaban];
        }

        if (! in_array($jawaban, ['biru', 'merah', 'invalid'], true)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Jawaban tidak valid.']);
        }

        // Cari verifikasi yang sedang berlangsung
        $verifikasi = $this->pertandinganModel->getVerifikasiBerlangsung($idPertandingan);
        if ($verifikasi === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Tidak ada verifikasi aktif.']);
        }

        // Cari jawaban juri ini
        $jawabanRow = $this->pertandinganModel->getJawabanVerifikasiJuri(
            (int) $verifikasi->id_verifikasi_pertandingan,
            $this->idPerangkat()
        );

        $db = \Config\Database::connect();

        if ($jawabanRow === null) {
            // Row belum ada — INSERT baru (KP mungkin belum sempat create row untuk juri ini)
            $db->table('jawaban_verifikasi_pertandingan')->insert([
                'id_verifikasi_pertandingan' => (int) $verifikasi->id_verifikasi_pertandingan,
                'id_perangkat_pertandingan'  => $this->idPerangkat(),
                'jawaban'                    => $jawaban,
            ]);
        } else {
            // Update jawaban di DB
            $db->table('jawaban_verifikasi_pertandingan')
                ->where('id_jawaban_verifikasi_pertandingan', $jawabanRow->id_jawaban_verifikasi_pertandingan)
                ->update(['jawaban' => $jawaban]);
        }

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
            // Juri sedang tidak ditugaskan (skema 3 juri)
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        // Diskualifikasi → standby
        if ((int) ($penampilan->diskualifikasi ?? 0) === 1) {
            return redirect()->to('/perangkat-pertandingan/standby');
        }

        // Load data kompetisi seni (pool/battle detection)
        $db = \Config\Database::connect();
        $kompetisiSeni = $db->table('kompetisi_seni')
            ->where('id_kompetisi_seni', (int) $penampilan->id_kompetisi_seni)
            ->get()->getRow();

        // sistem_penampilan ada di sub_kategori_seni, bukan kompetisi_seni
        $sistemPenampilan = 'pool';
        if ($kompetisiSeni) {
            $subKategori = $db->table('sub_kategori_seni')
                ->where('id_sub_kategori_seni', (int) $kompetisiSeni->id_sub_kategori_seni)
                ->get()->getRow();
            if ($subKategori) {
                $sistemPenampilan = $subKategori->sistem_penampilan ?? 'pool';
            }
        }

        // Detect battle partai (biru/merah)
        $partaiSeni = null;
        $colorAccent = 'bg-gradient-180-warning';
        if ($sistemPenampilan === 'battle') {
            $partaiSeni = $db->table('battle_seni')
                ->where('id_penampilan_seni_biru', (int) $penampilan->id_penampilan_seni)
                ->orWhere('id_penampilan_seni_merah', (int) $penampilan->id_penampilan_seni)
                ->get()->getRow();
            if ($partaiSeni) {
                $colorAccent = ((int) $partaiSeni->id_penampilan_seni_biru === (int) $penampilan->id_penampilan_seni)
                    ? 'bg-gradient-180-blue'
                    : 'bg-gradient-180-red';
            }
        }

        // Load peserta seni
        $pesertaSeni = $db->table('peserta_seni')
            ->select('peserta_seni.*, pendaftar.nama_pendaftar, kontingen.nama_kontingen')
            ->join('pendaftar', 'pendaftar.id_pendaftar = peserta_seni.id_pendaftar')
            ->join('kontingen', 'kontingen.id_kontingen = pendaftar.id_kontingen', 'left')
            ->where('peserta_seni.id_kelompok_peserta_seni', (int) $penampilan->id_kelompok_peserta_seni)
            ->get()->getResult();

        $dataNilai = json_decode($penilaian->penilaian);

        $mode = in_array($mode, ['sederhana', 'terperinci'], true) ? $mode : 'sederhana';

        return view('pertandingan/juri/seni/persilat/' . $mode, [
            'title'             => 'Penilaian Seni',
            'penampilan_seni'   => $penampilan,
            'kompetisi_seni'    => $kompetisiSeni,
            'partai_seni'       => $partaiSeni,
            'peserta_seni'      => $pesertaSeni,
            'penilaian'         => $penilaian,
            'data_nilai'        => $dataNilai,
            'color_accent'      => $colorAccent,
            'status_ready'      => (int) ($penilaian->status_ready ?? 0),
            'akses_penilaian'   => $penampilan->akses_penilaian ?? 'dibuka',
            'mode'              => $mode,
        ]);
    }

    /**
     * AJAX: Save penilaian seni dari juri.
     * Parity legacy: edit_penilaian_seni($id_penampilan_seni)
     * Juri sends clean_data (unsur_nilai + ringkasan.total_nilai only).
     * Server merges with existing hukuman (KP-controlled) and recalculates nilai_akhir.
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

        $dataNilaiRaw = $this->request->getPost('data_nilai');

        if (empty($dataNilaiRaw)) {
            return $this->jsonResponse(['status' => false, 'message' => 'Data nilai kosong.']);
        }

        $newData = json_decode($dataNilaiRaw);
        if ($newData === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Format JSON tidak valid.']);
        }

        // Get current penilaian from DB
        $penilaian = $this->penilaianSeniModel
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->where('id_perangkat_pertandingan', $this->idPerangkat())
            ->first();

        if ($penilaian === null) {
            return $this->jsonResponse(['status' => false, 'message' => 'Record penilaian tidak ditemukan.']);
        }

        $currentJson = json_decode($penilaian->penilaian);

        // PERSILAT parity: Juri hanya boleh ubah unsur_nilai dan total_nilai.
        // Hukuman + total_hukuman + nilai_akhir dihitung server dari fresh penalties.
        if ($currentJson !== null && isset($currentJson->penilaian)) {
            // Update unsur_nilai from jury submission
            if (isset($newData->penilaian->unsur_nilai)) {
                $currentJson->penilaian->unsur_nilai = $newData->penilaian->unsur_nilai;
            }
            // Update total_nilai from jury calculation
            if (isset($newData->penilaian->ringkasan->total_nilai)) {
                $currentJson->penilaian->ringkasan->total_nilai = $newData->penilaian->ringkasan->total_nilai;
            }

            // Get fresh penalties (authoritative, from any juri row — KP sets them identically)
            $freshPenalties = $this->getFreshPenalties($idPenampilanSeni);
            if ($freshPenalties !== null) {
                $currentJson->penilaian->hukuman = $freshPenalties['hukuman'];
                $currentJson->penilaian->ringkasan->total_hukuman = $freshPenalties['total_hukuman'];
            }

            // Recalculate nilai_akhir server-side
            $totalNilai = (float) ($currentJson->penilaian->ringkasan->total_nilai ?? 0);
            $totalHukuman = (float) ($currentJson->penilaian->ringkasan->total_hukuman ?? 0);
            $currentJson->penilaian->ringkasan->nilai_akhir = $totalNilai - $totalHukuman;

            $finalJson = json_encode($currentJson);
        } else {
            // Non-PERSILAT or no existing data — save as-is
            $finalJson = $dataNilaiRaw;
        }

        $nilaiAkhirPerJuri = $currentJson->penilaian->ringkasan->nilai_akhir ?? 0;

        $this->penilaianSeniModel->update($penilaian->id_penilaian_seni, [
            'penilaian'          => $finalJson,
            'nilai_akhir_per_juri' => (string) round((float) $nilaiAkhirPerJuri, 4),
        ]);

        // Recalculate nilai_akhir penampilan (median across all juri)
        $this->recalculateNilaiAkhirPenampilan($idPenampilanSeni);

        return $this->jsonResponse(['status' => true, 'new_nilai' => $finalJson]);
    }

    /**
     * Get fresh penalties from existing juri records (KP sets them identically across all).
     */
    private function getFreshPenalties(int $idPenampilanSeni): ?array
    {
        $rows = $this->penilaianSeniModel
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->findAll();

        if (empty($rows)) return null;

        // Take from first available row that has penalty data
        foreach ($rows as $row) {
            $parsed = json_decode($row->penilaian);
            if ($parsed && isset($parsed->penilaian->hukuman)) {
                $totalHukuman = 0;
                foreach ($parsed->penilaian->hukuman as $h) {
                    $totalHukuman += (float) ($h->detail_hukuman->nilai_hukuman ?? 0);
                }
                return [
                    'hukuman' => $parsed->penilaian->hukuman,
                    'total_hukuman' => $totalHukuman,
                ];
            }
        }

        return ['hukuman' => (object)[], 'total_hukuman' => 0];
    }

    /**
     * Recalculate nilai_akhir for penampilan_seni (median of all juri nilai_akhir).
     * Also saves catatan_nilai_sama and updates terpilih flag.
     * Parity: PersilatSeniService::hitungNilaiAkhir()
     */
    private function recalculateNilaiAkhirPenampilan(int $idPenampilanSeni): void
    {
        $rows = $this->penilaianSeniModel
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->findAll();

        if (empty($rows)) return;

        $nilaiAkhirList = [];
        $totalNilaiList = [];
        $kebenaranList = [];
        $totalHukuman = 0;
        $arrayTotalNilai = []; // For terpilih selection

        foreach ($rows as $row) {
            $parsed = json_decode($row->penilaian);
            if ($parsed && isset($parsed->penilaian->ringkasan)) {
                $na = (float) ($parsed->penilaian->ringkasan->nilai_akhir ?? 0);
                $tn = (float) ($parsed->penilaian->ringkasan->total_nilai ?? 0);
                $nilaiAkhirList[] = $na;
                $totalNilaiList[] = $tn;
                // Hukuman same across all juri (KP sets identically)
                $totalHukuman = (float) ($parsed->penilaian->ringkasan->total_hukuman ?? 0);

                $arrayTotalNilai[] = [
                    'id_perangkat_pertandingan' => (int) $row->id_perangkat_pertandingan,
                    'nilai_akhir' => $tn,
                ];

                // Kebenaran for median_kebenaran
                if (isset($parsed->penilaian->unsur_nilai->kebenaran->nilai_diperoleh)) {
                    $kebenaranList[] = (float) $parsed->penilaian->unsur_nilai->kebenaran->nilai_diperoleh;
                }
            }
        }

        if (empty($totalNilaiList)) return;

        // Sort for median calculation
        usort($arrayTotalNilai, fn($a, $b) => $a['nilai_akhir'] <=> $b['nilai_akhir']);
        sort($totalNilaiList);
        $count = count($totalNilaiList);
        $mid = (int) floor($count / 2);
        $medianTotalNilai = ($count % 2 === 0)
            ? ($totalNilaiList[$mid - 1] + $totalNilaiList[$mid]) / 2
            : $totalNilaiList[$mid];

        // Median nilai_akhir (final score)
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

        // Median kebenaran
        sort($kebenaranList);
        $medianKebenaran = 0;
        if (!empty($kebenaranList)) {
            $countK = count($kebenaranList);
            $midK = (int) floor($countK / 2);
            $medianKebenaran = ($countK % 2 === 0)
                ? ($kebenaranList[$midK - 1] + $kebenaranList[$midK]) / 2
                : $kebenaranList[$midK];
        }

        $db = \Config\Database::connect();
        $db->table('penampilan_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->update([
                'nilai_akhir' => round($medianNilaiAkhir, 4),
                'catatan_nilai_sama' => json_encode([
                    'median'            => round($medianTotalNilai, 6),
                    'hukuman'           => round($totalHukuman, 4),
                    'standar_deviasi'   => round($stdDev, 6),
                    'median_kebenaran'  => round($medianKebenaran, 6),
                ]),
            ]);

        // Update terpilih flag — select median juri
        $db->table('penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->update(['terpilih' => 0]);

        if ($count % 2 === 0) {
            // Even: select middle 2
            $idx1 = ($count / 2) - 1;
            $idx2 = ($count / 2);
            $db->table('penilaian_seni')
                ->where('id_penampilan_seni', $idPenampilanSeni)
                ->whereIn('id_perangkat_pertandingan', [
                    $arrayTotalNilai[$idx1]['id_perangkat_pertandingan'],
                    $arrayTotalNilai[$idx2]['id_perangkat_pertandingan'],
                ])
                ->update(['terpilih' => 1]);
        } else {
            // Odd: select middle 1
            $idxMedian = (int) floor($count / 2);
            $db->table('penilaian_seni')
                ->where('id_penampilan_seni', $idPenampilanSeni)
                ->where('id_perangkat_pertandingan', $arrayTotalNilai[$idxMedian]['id_perangkat_pertandingan'])
                ->update(['terpilih' => 1]);
        }
    }

    /**
     * Polling status penampilan seni — parity legacy refresh_status_seni.
     * Returns: reload flag OR current penampilan_seni + data_nilai (for penalty sync).
     */
    public function refreshStatusSeni(?int $idPenampilanSeni = null)
    {
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            // Tidak ada penampilan berlangsung
            if ($idPenampilanSeni === null) {
                // Request dari standby → no reload
                return $this->jsonResponse(['status' => true, 'reload' => false]);
            } else {
                // Request dari halaman aktif → reload ke standby
                return $this->jsonResponse(['status' => true, 'reload' => true]);
            }
        }

        if ((int) ($penampilan->id_penampilan_seni ?? 0) === (int) $idPenampilanSeni
            || (int) ($penampilan->diskualifikasi ?? 0) === 1) {
            // Penampilan sama atau diskualifikasi → kirim data terkini
            $penilaian = $this->penilaianSeniModel
                ->where('id_penampilan_seni', (int) ($idPenampilanSeni ?? $penampilan->id_penampilan_seni))
                ->where('id_perangkat_pertandingan', $this->idPerangkat())
                ->first();

            if ($penilaian === null) {
                // Juri sedang tidak ditugaskan (skema 3 juri) → reload to standby
                return $this->jsonResponse(['status' => true, 'reload' => false, 'data_nilai' => null]);
            }

            $dataNilai = json_decode($penilaian->penilaian);

            return $this->jsonResponse([
                'penampilan_seni' => $penampilan,
                'data_nilai'      => $dataNilai,
            ]);
        } else {
            // Penampilan beda — cek apakah juri ditugaskan di penampilan baru
            $penilaian = $this->penilaianSeniModel
                ->where('id_penampilan_seni', (int) $penampilan->id_penampilan_seni)
                ->where('id_perangkat_pertandingan', $this->idPerangkat())
                ->first();

            if ($penilaian === null) {
                // Juri tidak ditugaskan → stay standby
                return $this->jsonResponse(['status' => true, 'reload' => false]);
            } else {
                // Juri ditugaskan di penampilan baru → reload
                return $this->jsonResponse(['status' => true, 'reload' => true]);
            }
        }
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

        // Broadcast juri ready status ke KP
        helper('realtime');
        realtime_emit_juri_ready_update($idPenampilanSeni, [
            'id_perangkat_pertandingan' => $this->idPerangkat(),
            'status_ready'              => (bool) $newReady,
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
