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
        $db = \Config\Database::connect();
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return view('pertandingan/layar/standby', [
                'title'           => 'Layar — Standby',
                'nama_gelanggang' => session()->get('nama_gelanggang'),
                'mode'            => 'tanding',
            ]);
        }

        $idPertandingan = (int) $pertandingan->id_pertandingan;

        // Decode JSON fields (parity legacy)
        $pertandingan->data_waktu = !empty($pertandingan->data_waktu) ? json_decode($pertandingan->data_waktu) : null;
        $pertandingan->ringkasan_nilai = !empty($pertandingan->ringkasan_nilai) ? json_decode($pertandingan->ringkasan_nilai) : null;

        // Verifikasi pertandingan (latest)
        $verifikasiPertandingan = $db->table('verifikasi_pertandingan')
            ->where('id_pertandingan', $idPertandingan)
            ->orderBy('id_verifikasi_pertandingan', 'DESC')
            ->get()->getRow();

        // Perangkat pertandingan (juri list for indicators)
        // Parity legacy: only show juri that have penilaian records for this match
        $penilaianJuriIds = $db->table('penilaian_tanding')
            ->select('id_perangkat_pertandingan')
            ->where('id_pertandingan', $idPertandingan)
            ->get()->getResultArray();
        $juriIds = array_column($penilaianJuriIds, 'id_perangkat_pertandingan');

        $perangkatPertandingan = [];
        if (!empty($juriIds)) {
            $perangkatPertandingan = $db->table('perangkat_pertandingan')
                ->whereIn('id_perangkat_pertandingan', $juriIds)
                ->get()->getResult();
        }

        // Data nilai (grouped scoring) — parity legacy kelompokkan_penilaian_tanding()
        $dataNilai = $this->getGroupedPenilaianTanding($idPertandingan);

        // Get event name (from env or fallback)
        $eventName = env('app.eventName', 'Pencak Silat Championship');

        // Update session
        session()->set('id_pertandingan', $idPertandingan);

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/layar/tanding/persilat/dark', [
            'title'                    => 'Papan Skor Tanding',
            'pertandingan'             => $pertandingan,
            'verifikasi_pertandingan'  => $verifikasiPertandingan,
            'perangkat_pertandingan'   => $perangkatPertandingan,
            'data_nilai'               => $dataNilai,
            'data_waktu'               => $pertandingan->data_waktu,
            'atlet_merah'              => $this->pertandinganModel->getAtletPertandingan($idPertandingan, 'merah'),
            'atlet_biru'               => $this->pertandinganModel->getAtletPertandingan($idPertandingan, 'biru'),
            'event_name'               => $eventName,
            'theme'                    => $theme,
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
            if ($idPertandingan === null || $idPertandingan === 0) {
                return $this->jsonResponse(['status' => true, 'reload' => false]);
            }
            // Had match but now gone → reload to standby
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }

        // Case 1: Dipanggil dari home/standby (null id) → ada pertandingan aktif
        if ($idPertandingan === null || $idPertandingan === 0) {
            return $this->jsonResponse([
                'status'          => false,
                'id_pertandingan' => (int) $pertandingan->id_pertandingan,
            ]);
        }

        // Case 2: ID berubah → reload
        if ((int) $pertandingan->id_pertandingan !== $idPertandingan) {
            session()->set('id_pertandingan', (int) $pertandingan->id_pertandingan);
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }

        // Case 3: Sama → kirim full live data (parity legacy)
        $db = \Config\Database::connect();

        // Decode data_waktu + ringkasan_nilai
        $pertandingan->data_waktu = !empty($pertandingan->data_waktu) ? json_decode($pertandingan->data_waktu) : null;
        $pertandingan->ringkasan_nilai = !empty($pertandingan->ringkasan_nilai) ? json_decode($pertandingan->ringkasan_nilai) : null;

        // Verifikasi pertandingan (latest)
        $verifikasiPertandingan = $db->table('verifikasi_pertandingan')
            ->where('id_pertandingan', $idPertandingan)
            ->orderBy('id_verifikasi_pertandingan', 'DESC')
            ->get()->getRow();

        // Data nilai (grouped scoring)
        $dataNilai = $this->getGroupedPenilaianTanding($idPertandingan);

        return $this->jsonResponse([
            'status'                  => false,
            'pertandingan'            => $pertandingan,
            'data_nilai'              => $dataNilai,
            'verifikasi_pertandingan' => $verifikasiPertandingan,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SENI
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Scoreboard seni — live penampilan display.
     * Parity legacy: Layar::seni($mode)
     */
    public function seni(string $theme = 'dark')
    {
        $db = \Config\Database::connect();
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            return view('pertandingan/layar/standby', [
                'title'           => 'Layar — Standby Seni',
                'nama_gelanggang' => session()->get('nama_gelanggang'),
                'mode'            => 'seni',
            ]);
        }

        $idPenampilan = (int) $penampilan->id_penampilan_seni;

        // Get id_kompetisi_seni + compute anggota_kelompok_peserta_seni via subquery (parity legacy)
        $idKompetisiSeni = 0;
        $anggotaKelompok = null;
        if (!empty($penampilan->id_kelompok_peserta_seni)) {
            $kps = $db->table('kelompok_peserta_seni')
                ->select('id_kompetisi_seni')
                ->where('id_kelompok_peserta_seni', $penampilan->id_kelompok_peserta_seni)
                ->get()->getRow();
            $idKompetisiSeni = (int) ($kps->id_kompetisi_seni ?? 0);

            // Subquery: GROUP_CONCAT nama peserta (parity legacy Penampilan_seni_model::select())
            $anggotaKelompok = $db->query(
                "SELECT GROUP_CONCAT(CONCAT_WS(' ', p.nama_pendaftar) SEPARATOR ' ,<br>') AS anggota
                 FROM pendaftar p
                 JOIN peserta_seni ps ON ps.id_pendaftar = p.id_pendaftar
                 WHERE ps.id_kelompok_peserta_seni = ?",
                [$penampilan->id_kelompok_peserta_seni]
            )->getRow()->anggota ?? null;
        }

        // Attach anggota_kelompok_peserta_seni to penampilan object for view (parity legacy)
        $penampilan->anggota_kelompok_peserta_seni = $anggotaKelompok;

        // Get kompetisi_seni metadata (join sub_kategori_seni for sistem_penampilan)
        $kompetisiSeni = $db->table('kompetisi_seni ks')
            ->select('ks.*, sks.sistem_penampilan, ku.nama_kategori_usia, sks.jenis_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->where('ks.id_kompetisi_seni', $idKompetisiSeni)
            ->get()->getRow();

        // Get partai seni berlangsung (for battle info)
        $partaiSeni = $db->table('detail_jadwal_seni djs')
            ->select('djs.*, js.id_gelanggang, g.nama_gelanggang, djs.nomor_partai, bs.babak AS babak_battle')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang', 'left')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni', 'left')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('djs.id_penampilan_seni', $idPenampilan)
            ->get()->getRow();

        // Get peserta_seni
        $pesertaSeni = [];
        if (!empty($penampilan->id_kelompok_peserta_seni)) {
            $pesertaSeni = $db->table('peserta_seni ps')
                ->select('ps.*, p.nama_pendaftar, k.nama_kontingen')
                ->join('pendaftar p', 'p.id_pendaftar = ps.id_pendaftar')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
                ->where('ps.id_kelompok_peserta_seni', $penampilan->id_kelompok_peserta_seni)
                ->get()->getResult();
        }

        // Get grouped data_nilai (parity legacy kelompokkan_penilaian_seni)
        $dataNilai = $this->getGroupedPenilaianSeni($idPenampilan);

        // Event name (from CI4 config or fallback)
        $eventName = env('app.eventName', 'Pencak Silat Championship');

        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';

        return view('pertandingan/layar/seni', [
            'title'                        => 'Papan Skor Seni',
            'penampilan_seni_berlangsung'   => $penampilan,
            'kompetisi_seni'               => $kompetisiSeni,
            'partai_seni_berlangsung'      => $partaiSeni,
            'peserta_seni'                 => $pesertaSeni,
            'data_nilai'                   => $dataNilai,
            'event_name'                   => $eventName,
            'theme'                        => $theme,
        ]);
    }

    /**
     * Polling state authoritative seni.
     * Parity legacy: refresh_status_seni($id_penampilan_seni)
     * Complex state machine: detects pool/battle completion, returns live data.
     */
    public function refreshStatusSeni(?int $idPenampilanSeni = null)
    {
        $db = \Config\Database::connect();
        $penampilan = $this->penampilanSeniModel->getAktif($this->idGelanggang());

        if ($penampilan === null) {
            // No active performance — check if there's a completed pool/battle to show results
            if ($idPenampilanSeni !== null && $idPenampilanSeni > 0) {
                // Was watching a performance that just ended — check for result views
                $lastPenampilan = $db->table('penampilan_seni')
                    ->where('id_penampilan_seni', $idPenampilanSeni)
                    ->get()->getRow();

                if ($lastPenampilan) {
                    // Get id_kompetisi_seni via kelompok_peserta_seni
                    $kompetisiId = null;
                    if (!empty($lastPenampilan->id_kelompok_peserta_seni)) {
                        $kpsRow = $db->table('kelompok_peserta_seni')
                            ->select('id_kompetisi_seni')
                            ->where('id_kelompok_peserta_seni', $lastPenampilan->id_kelompok_peserta_seni)
                            ->get()->getRow();
                        $kompetisiId = $kpsRow ? (int) $kpsRow->id_kompetisi_seni : null;
                    }
                    if ($kompetisiId) {
                        // Get kompetisi metadata for sistem_penampilan
                        $kompetisi = $db->table('kompetisi_seni ks')
                            ->select('ks.*, sks.sistem_penampilan')
                            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
                            ->where('ks.id_kompetisi_seni', $kompetisiId)
                            ->get()->getRow();

                        $sistemPenampilan = $kompetisi->sistem_penampilan ?? 'pool';

                        if ($sistemPenampilan === 'battle') {
                            // Check for completed battle
                            $battle = $db->table('detail_jadwal_seni')
                                ->where('id_penampilan_seni', $idPenampilanSeni)
                                ->get()->getRow();
                            if ($battle && !empty($battle->id_battle_seni)) {
                                return $this->jsonResponse([
                                    'status'            => true,
                                    'reload'            => false,
                                    'hasil_battle_seni' => true,
                                    'id_battle_seni'    => (int) $battle->id_battle_seni,
                                ]);
                            }
                        } else {
                            // Pool: check if all penampilan in kompetisi are sudah_tampil
                            $belumTampil = $db->table('penampilan_seni ps')
                                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                                ->where('kps.id_kompetisi_seni', $kompetisiId)
                                ->where('ps.status_penampilan !=', 'sudah_tampil')
                                ->countAllResults();

                            if ($belumTampil === 0) {
                                return $this->jsonResponse([
                                    'status'           => true,
                                    'reload'           => false,
                                    'hasil_pool_seni'  => true,
                                    'id_kompetisi_seni' => (int) $kompetisiId,
                                ]);
                            }
                        }
                    }
                }

                // Performance ended, no special result → reload to standby
                return $this->jsonResponse(['status' => true, 'reload' => true]);
            }

            // Called from standby with no id
            return $this->jsonResponse(['status' => true, 'reload' => false]);
        }

        // Active performance found
        $idAktif = (int) $penampilan->id_penampilan_seni;

        // Case: called from standby (null id) → there's an active performance
        if ($idPenampilanSeni === null || $idPenampilanSeni === 0) {
            return $this->jsonResponse([
                'status'             => false,
                'id_penampilan_seni' => $idAktif,
            ]);
        }

        // Case: performance changed → reload
        if ($idAktif !== $idPenampilanSeni) {
            return $this->jsonResponse(['status' => true, 'reload' => true]);
        }

        // Same performance — return live data (parity legacy)
        $dataNilai = $this->getGroupedPenilaianSeni($idAktif);

        return $this->jsonResponse([
            'status'                       => false,
            'penampilan_seni_berlangsung'   => $penampilan,
            'data_nilai'                   => $dataNilai,
        ], JSON_NUMERIC_CHECK);
    }

    /**
     * Hasil pool seni — ranking display after all performances.
     * Parity legacy: Layar::hasil_pool_seni($id_kompetisi_seni)
     */
    public function hasilPoolSeni(int $idKompetisiSeni)
    {
        $db = \Config\Database::connect();

        // Get kompetisi metadata
        $kompetisiSeni = $db->table('kompetisi_seni ks')
            ->select('ks.*, sks.sistem_penampilan, sks.jenis_seni, ku.nama_kategori_usia')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->where('ks.id_kompetisi_seni', $idKompetisiSeni)
            ->get()->getRow();

        // Get all penampilan for this kompetisi ordered by nilai_akhir DESC
        // Use subquery for anggota_kelompok_peserta_seni (parity legacy Penampilan_seni_model::select())
        $daftarPenampilan = $db->query("
            SELECT ps.*,
                   kps.id_kontingen,
                   k.nama_kontingen,
                   ps.catatan_nilai_sama,
                   (SELECT GROUP_CONCAT(CONCAT_WS(' ', p.nama_pendaftar) SEPARATOR ' ,<br>')
                    FROM pendaftar p
                    JOIN peserta_seni pse ON pse.id_pendaftar = p.id_pendaftar
                    WHERE pse.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni
            FROM penampilan_seni ps
            JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni
            LEFT JOIN kontingen k ON k.id_kontingen = kps.id_kontingen
            WHERE kps.id_kompetisi_seni = ?
            ORDER BY ps.nilai_akhir DESC
        ", [$idKompetisiSeni])->getResult();

        // Attach peserta names to each penampilan (for display flexibility)
        foreach ($daftarPenampilan as &$penampilan) {
            $peserta = $db->table('peserta_seni pse')
                ->select('p.nama_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = pse.id_pendaftar')
                ->where('pse.id_kelompok_peserta_seni', $penampilan->id_kelompok_peserta_seni)
                ->get()->getResult();
            $penampilan->peserta = $peserta;
        }

        return view('pertandingan/layar/seni/persilat/hasil_pool_seni', [
            'title'           => 'Hasil Pool Seni',
            'kompetisi_seni'  => $kompetisiSeni,
            'daftar'          => $daftarPenampilan,
        ]);
    }

    /**
     * Hasil battle seni — winner display.
     * Parity legacy: Layar::hasil_battle_seni($id_battle_seni)
     */
    public function hasilBattleSeni(int $idBattleSeni)
    {
        $db = \Config\Database::connect();

        // Get battle info
        $battle = $db->table('battle_seni')
            ->where('id_battle_seni', $idBattleSeni)
            ->get()->getRow();

        if ($battle === null) {
            return redirect()->to(base_url('layar/seni'));
        }

        // Get penampilan biru & merah with subquery for anggota_kelompok_peserta_seni
        $penampilanBiru = $db->query("
            SELECT ps.*,
                   kps.id_kontingen,
                   k.nama_kontingen,
                   (SELECT GROUP_CONCAT(CONCAT_WS(' ', p.nama_pendaftar) SEPARATOR ' ,<br>')
                    FROM pendaftar p
                    JOIN peserta_seni pse ON pse.id_pendaftar = p.id_pendaftar
                    WHERE pse.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni
            FROM penampilan_seni ps
            JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni
            LEFT JOIN kontingen k ON k.id_kontingen = kps.id_kontingen
            WHERE ps.id_penampilan_seni = ?
        ", [$battle->id_penampilan_seni_biru ?? 0])->getRow();

        $penampilanMerah = $db->query("
            SELECT ps.*,
                   kps.id_kontingen,
                   k.nama_kontingen,
                   (SELECT GROUP_CONCAT(CONCAT_WS(' ', p.nama_pendaftar) SEPARATOR ' ,<br>')
                    FROM pendaftar p
                    JOIN peserta_seni pse ON pse.id_pendaftar = p.id_pendaftar
                    WHERE pse.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni) AS anggota_kelompok_peserta_seni
            FROM penampilan_seni ps
            JOIN kelompok_peserta_seni kps ON kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni
            LEFT JOIN kontingen k ON k.id_kontingen = kps.id_kontingen
            WHERE ps.id_penampilan_seni = ?
        ", [$battle->id_penampilan_seni_merah ?? 0])->getRow();

        // Get peserta names
        $pesertaBiru = [];
        if ($penampilanBiru) {
            $pesertaBiru = $db->table('peserta_seni pse')
                ->select('p.nama_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = pse.id_pendaftar')
                ->where('pse.id_kelompok_peserta_seni', $penampilanBiru->id_kelompok_peserta_seni)
                ->get()->getResult();
        }

        $pesertaMerah = [];
        if ($penampilanMerah) {
            $pesertaMerah = $db->table('peserta_seni pse')
                ->select('p.nama_pendaftar')
                ->join('pendaftar p', 'p.id_pendaftar = pse.id_pendaftar')
                ->where('pse.id_kelompok_peserta_seni', $penampilanMerah->id_kelompok_peserta_seni)
                ->get()->getResult();
        }

        return view('pertandingan/layar/seni/persilat/hasil_battle_seni', [
            'title'                => 'Hasil Battle Seni',
            'battle_seni'          => $battle,
            'penampilan_seni_biru' => $penampilanBiru,
            'penampilan_seni_merah' => $penampilanMerah,
            'peserta_seni_biru'    => $pesertaBiru,
            'peserta_seni_merah'   => $pesertaMerah,
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

    // ═══════════════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Grouped penilaian tanding — parity legacy kelompokkan_penilaian_tanding().
     * Structure per perangkat:
     *   - penilaian_tanding: { merah: {...}, biru: {...} }
     *   - pemenang
     *
     * Each sudut (merah/biru) is stored as JSON in penilaian_tanding table:
     *   { ronde_pertandingan: { 1: { rincian: [...], kategori_nilai: {...}, catatan: {...} }, ... },
     *     ringkasan: { total_nilai_terinput, total_hukuman } }
     */
    private function getGroupedPenilaianTanding(int $idPertandingan): object
    {
        $db = \Config\Database::connect();

        // Get all penilaian_tanding rows for this match
        $rows = $db->table('penilaian_tanding')
            ->where('id_pertandingan', $idPertandingan)
            ->get()->getResult();

        // Get perangkat (juri) — only those with penilaian records for this match
        $juriIds = array_unique(array_column(array_map(function($r) { return (array)$r; }, $rows), 'id_perangkat_pertandingan'));
        $perangkatList = [];
        if (!empty($juriIds)) {
            $perangkatList = $db->table('perangkat_pertandingan')
                ->whereIn('id_perangkat_pertandingan', $juriIds)
                ->get()->getResult();
        }

        // Group by perangkat
        $juriGrouped = [];
        foreach ($perangkatList as $perangkat) {
            $idPerangkat = (int) $perangkat->id_perangkat_pertandingan;

            // Find this juri's penilaian row
            $penilaianRow = null;
            foreach ($rows as $row) {
                if ((int) $row->id_perangkat_pertandingan === $idPerangkat) {
                    $penilaianRow = $row;
                    break;
                }
            }

            // Parse JSON penilaian
            $penilaianMerah = null;
            $penilaianBiru = null;
            $pemenang = '';

            if ($penilaianRow) {
                $penilaianMerah = !empty($penilaianRow->penilaian_merah) ? json_decode($penilaianRow->penilaian_merah) : null;
                $penilaianBiru = !empty($penilaianRow->penilaian_biru) ? json_decode($penilaianRow->penilaian_biru) : null;
                $pemenang = $penilaianRow->pemenang ?? '';
            }

            $juriGrouped[] = (object) [
                'id_perangkat_pertandingan' => $idPerangkat,
                'penilaian_tanding'         => (object) [
                    'merah' => $penilaianMerah,
                    'biru'  => $penilaianBiru,
                ],
                'pemenang' => $pemenang,
            ];
        }

        return (object) ['juri' => $juriGrouped];
    }

    /**
     * Grouped penilaian seni — parity legacy kelompokkan_penilaian_seni().
     * Returns: { [id_penampilan_seni] => [ { id_perangkat_pertandingan, penilaian (JSON string), terpilih } ] }
     */
    private function getGroupedPenilaianSeni(int $idPenampilanSeni): array
    {
        $db = \Config\Database::connect();

        $rows = $db->table('penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->orderBy('id_perangkat_pertandingan', 'ASC')
            ->get()->getResult();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$idPenampilanSeni][] = (object) [
                'id_perangkat_pertandingan' => (int) $row->id_perangkat_pertandingan,
                'penilaian'                 => $row->penilaian ?? '{}',
                'terpilih'                  => (int) ($row->terpilih ?? 0), // Default 0 = not selected
            ];
        }

        return $grouped;
    }
}
