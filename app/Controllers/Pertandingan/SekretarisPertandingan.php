<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\BattleSeniModel;
use App\Models\DetailJadwalSeniModel;
use App\Models\DetailJadwalTandingModel;
use App\Models\JadwalSeniModel;
use App\Models\JadwalTandingModel;
use App\Models\KelompokPesertaSeniModel;
use App\Models\KompetisiSeniModel;
use App\Models\KompetisiTandingModel;
use App\Models\PenampilanSeniModel;
use App\Models\PenilaianSeniModel;
use App\Models\PenilaianTandingModel;
use App\Models\PerangkatPertandinganModel;
use App\Models\PertandinganModel;
use App\Services\Scoring\Persilat\PersilatSeniService;
use App\Services\Scoring\Persilat\PersilatTandingService;

class SekretarisPertandingan extends BaseController
{
    protected PertandinganModel $pertandinganModel;
    protected PenilaianTandingModel $penilaianModel;
    protected PersilatTandingService $tandingService;
    protected PersilatSeniService $seniService;
    protected PenampilanSeniModel $penampilanSeniModel;
    protected BattleSeniModel $battleSeniModel;
    protected PenilaianSeniModel $penilaianSeniModel;
    protected KelompokPesertaSeniModel $kelompokPesertaSeniModel;
    protected KompetisiSeniModel $kompetisiSeniModel;
    protected KompetisiTandingModel $kompetisiTandingModel;
    protected JadwalTandingModel $jadwalTandingModel;
    protected JadwalSeniModel $jadwalSeniModel;
    protected DetailJadwalTandingModel $detailJadwalTandingModel;
    protected DetailJadwalSeniModel $detailJadwalSeniModel;

    public function __construct()
    {
        $this->pertandinganModel        = new PertandinganModel();
        $this->penilaianModel           = new PenilaianTandingModel();
        $this->tandingService           = new PersilatTandingService();
        $this->seniService              = new PersilatSeniService();
        $this->penampilanSeniModel      = new PenampilanSeniModel();
        $this->battleSeniModel          = new BattleSeniModel();
        $this->penilaianSeniModel       = new PenilaianSeniModel();
        $this->kelompokPesertaSeniModel = new KelompokPesertaSeniModel();
        $this->kompetisiSeniModel       = new KompetisiSeniModel();
        $this->kompetisiTandingModel    = new KompetisiTandingModel();
        $this->jadwalTandingModel       = new JadwalTandingModel();
        $this->jadwalSeniModel          = new JadwalSeniModel();
        $this->detailJadwalTandingModel = new DetailJadwalTandingModel();
        $this->detailJadwalSeniModel    = new DetailJadwalSeniModel();
    }

    private function idGelanggang(): int
    {
        return (int) session()->get('id_gelanggang');
    }

    /**
     * Landing: dashboard jadwal (home). Parity legacy index().
     */
    public function index()
    {
        return $this->home();
    }

    /**
     * Dashboard utama sekretaris — daftar jadwal tanding & seni di gelanggang aktif.
     * Parity legacy index() → view sekretaris/home.
     */
    public function home()
    {
        $idGelanggang = $this->idGelanggang();
        $db = \Config\Database::connect();

        $tanding = $db->table('jadwal_tanding')
            ->select('jadwal_tanding.id_jadwal_tanding, jadwal_tanding.keterangan,
                DATE_FORMAT(jadwal_tanding.tanggal, "%W, %D %M %Y") as tanggal_formatted,
                DATE_FORMAT(jadwal_tanding.jam_mulai, "%H:%i") as jam_mulai_formatted,
                DATE_FORMAT(jadwal_tanding.jam_selesai, "%H:%i") as jam_selesai_formatted,
                COUNT(detail_jadwal_tanding.id_detail_jadwal_tanding) as jumlah_partai')
            ->join('detail_jadwal_tanding', 'detail_jadwal_tanding.id_jadwal_tanding = jadwal_tanding.id_jadwal_tanding', 'left')
            ->where('jadwal_tanding.id_gelanggang', $idGelanggang)
            ->groupBy('jadwal_tanding.id_jadwal_tanding')
            ->get()->getResult();

        $seni = $db->table('jadwal_seni')
            ->select('jadwal_seni.id_jadwal_seni, jadwal_seni.keterangan,
                DATE_FORMAT(jadwal_seni.tanggal, "%W, %D %M %Y") as tanggal_formatted,
                DATE_FORMAT(jadwal_seni.jam_mulai, "%H:%i") as jam_mulai_formatted,
                DATE_FORMAT(jadwal_seni.jam_selesai, "%H:%i") as jam_selesai_formatted,
                COUNT(detail_jadwal_seni.id_detail_jadwal_seni) as jumlah_penampilan')
            ->join('detail_jadwal_seni', 'detail_jadwal_seni.id_jadwal_seni = jadwal_seni.id_jadwal_seni', 'left')
            ->where('jadwal_seni.id_gelanggang', $idGelanggang)
            ->groupBy('jadwal_seni.id_jadwal_seni')
            ->get()->getResult();

        $eventName = $db->table('site_builder_settings')
            ->select('value')
            ->where('setting', 'event_name')
            ->get()->getRow()?->value ?? '';

        return view('pertandingan/sekretaris/home', [
            'title'           => 'Sekretaris — Dashboard',
            'nama_gelanggang' => session()->get('nama_gelanggang'),
            'event_name'      => $eventName,
            'tanding'         => $tanding,
            'seni'            => $seni,
        ]);
    }

    /**
     * Detail jadwal tanding — daftar partai dalam satu jadwal.
     * Parity legacy jadwal_tanding($id).
     */
    public function jadwalTanding(int $idJadwalTanding)
    {
        $db = \Config\Database::connect();

        $jadwal = $db->table('jadwal_tanding')
            ->select('jadwal_tanding.*, gelanggang.nama_gelanggang,
                DATE_FORMAT(jadwal_tanding.tanggal, "%W, %D %M %Y") as tanggal_formatted,
                DATE_FORMAT(jadwal_tanding.jam_mulai, "%H:%i") as jam_mulai_formatted,
                DATE_FORMAT(jadwal_tanding.jam_selesai, "%H:%i") as jam_selesai_formatted')
            ->join('gelanggang', 'gelanggang.id_gelanggang = jadwal_tanding.id_gelanggang')
            ->where('jadwal_tanding.id_jadwal_tanding', $idJadwalTanding)
            ->get()->getRow();

        if ($jadwal === null) {
            return redirect()->to('/sekretaris-pertandingan')->with('error', 'Jadwal tidak ditemukan.');
        }

        $partai = $db->table('detail_jadwal_tanding')
            ->select('detail_jadwal_tanding.nomor_partai,
                pertandingan.id_pertandingan, pertandingan.babak, pertandingan.status_pertandingan,
                pertandingan.skor_merah, pertandingan.skor_biru, pertandingan.id_atlet_merah, pertandingan.id_atlet_biru,
                pertandingan.id_pemenang, pertandingan.jenis_kemenangan,
                kelas_tanding.label as nama_kelas,
                kategori_usia.nama_kategori_usia, kategori_usia.jenis_kelamin,
                pm.nama_pendaftar as nama_atlet_merah, km.nama_kontingen as nama_kontingen_merah,
                pb.nama_pendaftar as nama_atlet_biru, kb.nama_kontingen as nama_kontingen_biru')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
            ->join('kategori_usia', 'kategori_usia.id_kategori_usia = kategori_lomba.id_kategori_usia')
            // Atlet merah
            ->join('peserta_tanding as ptm', 'ptm.id_peserta_tanding = pertandingan.id_atlet_merah', 'left')
            ->join('pendaftar as pm', 'pm.id_pendaftar = ptm.id_pendaftar', 'left')
            ->join('kontingen as km', 'km.id_kontingen = pm.id_kontingen', 'left')
            // Atlet biru
            ->join('peserta_tanding as ptb', 'ptb.id_peserta_tanding = pertandingan.id_atlet_biru', 'left')
            ->join('pendaftar as pb', 'pb.id_pendaftar = ptb.id_pendaftar', 'left')
            ->join('kontingen as kb', 'kb.id_kontingen = pb.id_kontingen', 'left')
            ->where('detail_jadwal_tanding.id_jadwal_tanding', $idJadwalTanding)
            ->orderBy('detail_jadwal_tanding.nomor_partai * 1', 'ASC', false)
            ->get()->getResult();

        return view('pertandingan/sekretaris/jadwal_tanding', [
            'title'   => 'Jadwal Tanding',
            'jadwal'  => $jadwal,
            'partai'  => $partai,
            'nama_gelanggang' => session()->get('nama_gelanggang'),
        ]);
    }

    /**
     * Detail jadwal seni — daftar penampilan (sistem pool & battle).
     * Parity legacy jadwal_seni($id). Scope: listing + aksi start (sekretaris).
     */
    public function jadwalSeni(int $idJadwalSeni)
    {
        $db = \Config\Database::connect();

        $jadwal = $db->table('jadwal_seni')
            ->select('jadwal_seni.*, gelanggang.nama_gelanggang,
                DATE_FORMAT(jadwal_seni.tanggal, "%W, %D %M %Y") as tanggal_formatted,
                DATE_FORMAT(jadwal_seni.jam_mulai, "%H:%i") as jam_mulai_formatted,
                DATE_FORMAT(jadwal_seni.jam_selesai, "%H:%i") as jam_selesai_formatted')
            ->join('gelanggang', 'gelanggang.id_gelanggang = jadwal_seni.id_gelanggang')
            ->where('jadwal_seni.id_jadwal_seni', $idJadwalSeni)
            ->get()->getRow();

        if ($jadwal === null) {
            return redirect()->to('/sekretaris-pertandingan')->with('error', 'Jadwal seni tidak ditemukan.');
        }

        // Subquery anggota kelompok (group concat nama atlet) per penampilan.
        $anggota = static function (string $kolomPenampilan): string {
            return "(SELECT GROUP_CONCAT(p.nama_pendaftar SEPARATOR ', ')
                FROM pendaftar p
                JOIN peserta_seni psr ON psr.id_pendaftar = p.id_pendaftar
                JOIN penampilan_seni px ON px.id_kelompok_peserta_seni = psr.id_kelompok_peserta_seni
                WHERE px.id_penampilan_seni = {$kolomPenampilan})";
        };

        // --- Penampilan sistem POOL (id_penampilan_seni terisi, bukan battle) ---
        $pool = $db->table('detail_jadwal_seni djs')
            ->select('djs.id_detail_jadwal_seni, djs.nomor_partai, djs.nomor_urut,
                ps.id_penampilan_seni, ps.status_penampilan, ps.nilai_akhir, ps.waktu_tampil, ps.diskualifikasi, ps.babak,
                k.nama_kontingen, ks.nomor_pool, sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan,
                ku.nama_kategori_usia, ku.jenis_kelamin,
                pms.jenis_medali,
                ' . $anggota('ps.id_penampilan_seni') . ' as anggota', false)
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('perolehan_medali_seni pms', 'pms.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni', 'left')
            ->where('djs.id_jadwal_seni', $idJadwalSeni)
            ->where('djs.id_penampilan_seni IS NOT NULL')
            ->where('djs.id_battle_seni IS NULL')
            ->orderBy('djs.nomor_partai * 1', 'ASC', false)
            ->get()->getResult();

        // --- Penampilan sistem BATTLE (id_battle_seni terisi) ---
        $battle = $db->table('detail_jadwal_seni djs')
            ->select('djs.id_detail_jadwal_seni, djs.nomor_partai, djs.id_battle_seni,
                bs.babak, bs.id_penampilan_seni_biru, bs.id_penampilan_seni_merah, bs.id_penampilan_seni_pemenang,
                psb.id_penampilan_seni as penampilan_biru_id, psm.id_penampilan_seni as penampilan_merah_id,
                psb.status_penampilan as status_biru, psm.status_penampilan as status_merah,
                kb.nama_kontingen as nama_kontingen_biru, km.nama_kontingen as nama_kontingen_merah,
                ks.nomor_pool, sks.jenis_seni, sks.nama_seni, ku.nama_kategori_usia, ku.jenis_kelamin,
                pmsb.jenis_medali as medali_biru, pmsm.jenis_medali as medali_merah,
                ' . $anggota('bs.id_penampilan_seni_biru') . ' as anggota_biru,
                ' . $anggota('bs.id_penampilan_seni_merah') . ' as anggota_merah', false)
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')
            ->join('penampilan_seni psb', 'psb.id_penampilan_seni = bs.id_penampilan_seni_biru', 'left')
            ->join('penampilan_seni psm', 'psm.id_penampilan_seni = bs.id_penampilan_seni_merah', 'left')
            ->join('kelompok_peserta_seni kpsb', 'kpsb.id_kelompok_peserta_seni = psb.id_kelompok_peserta_seni', 'left')
            ->join('kontingen kb', 'kb.id_kontingen = kpsb.id_kontingen', 'left')
            ->join('kelompok_peserta_seni kpsm', 'kpsm.id_kelompok_peserta_seni = psm.id_kelompok_peserta_seni', 'left')
            ->join('kontingen km', 'km.id_kontingen = kpsm.id_kontingen', 'left')
            ->join('perolehan_medali_seni pmsb', 'pmsb.id_kelompok_peserta_seni = kpsb.id_kelompok_peserta_seni', 'left')
            ->join('perolehan_medali_seni pmsm', 'pmsm.id_kelompok_peserta_seni = kpsm.id_kelompok_peserta_seni', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = bs.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('djs.id_jadwal_seni', $idJadwalSeni)
            ->where('djs.id_battle_seni IS NOT NULL')
            ->orderBy('djs.nomor_partai * 1', 'ASC', false)
            ->get()->getResult();

        return view('pertandingan/sekretaris/jadwal_seni', [
            'title'           => 'Jadwal Seni',
            'jadwal'          => $jadwal,
            'pool'            => $pool,
            'battle'          => $battle,
            'nama_gelanggang' => session()->get('nama_gelanggang'),
        ]);
    }

    /**
     * Mulai sebuah penampilan seni (set status 'standby'). Parity legacy mulai_penampilan():
     * tidak boleh ada penampilan lain berlangsung di gelanggang.
     *
     * Catatan: layar Timer Seni belum dimigrasi (di luar scope penilaian inti),
     * sehingga setelah set standby user dikembalikan ke detail jadwal dengan notifikasi.
     */
    public function mulaiPenampilan(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();

        // Cek apakah ada penampilan seni berlangsung di gelanggang ini.
        // Check pool-based
        $aktifPool = $db->table('detail_jadwal_seni djs')
            ->select('ps.id_penampilan_seni')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('djs.id_penampilan_seni IS NOT NULL')
            ->whereNotIn('ps.status_penampilan', ['belum_tampil', 'sudah_tampil'])
            ->get()->getRow();

        // Check battle-based (biru/merah)
        $aktifBattle = null;
        $battleRows = $db->table('detail_jadwal_seni djs')
            ->select('bs.id_penampilan_seni_biru, bs.id_penampilan_seni_merah')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('djs.id_battle_seni IS NOT NULL')
            ->get()->getResult();

        foreach ($battleRows as $bRow) {
            foreach (['id_penampilan_seni_biru', 'id_penampilan_seni_merah'] as $col) {
                if ($bRow->$col !== null) {
                    $st = $db->table('penampilan_seni')
                        ->select('id_penampilan_seni, status_penampilan')
                        ->where('id_penampilan_seni', $bRow->$col)
                        ->whereNotIn('status_penampilan', ['belum_tampil', 'sudah_tampil'])
                        ->get(1)->getRow();
                    if ($st !== null) {
                        $aktifBattle = $st;
                        break 2;
                    }
                }
            }
        }

        $aktif = $aktifPool ?? $aktifBattle;

        // Tentukan redirect back URL
        // Cari jadwal via pool atau battle
        $jadwalId = $db->table('detail_jadwal_seni')
            ->select('id_jadwal_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();

        if ($jadwalId === null) {
            // Cari via battle_seni
            $jadwalId = $db->table('detail_jadwal_seni djs')
                ->select('djs.id_jadwal_seni')
                ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')
                ->groupStart()
                    ->where('bs.id_penampilan_seni_biru', $idPenampilanSeni)
                    ->orWhere('bs.id_penampilan_seni_merah', $idPenampilanSeni)
                ->groupEnd()
                ->get(1)->getRow();
        }

        $back = $jadwalId !== null
            ? '/sekretaris-pertandingan/jadwal-seni/' . (int) $jadwalId->id_jadwal_seni
            : '/sekretaris-pertandingan';

        if ($aktif !== null && (int) $aktif->id_penampilan_seni !== $idPenampilanSeni) {
            return redirect()->to($back)->with('error', 'Masih ada penampilan yang berlangsung.');
        }

        $db->table('penampilan_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->update(['status_penampilan' => 'standby']);

        // Broadcast ke Layar home/standby bahwa seni berlangsung.
        helper('realtime');
        realtime_emit_seni_berlangsung($this->idGelanggang(), $idPenampilanSeni);

        return redirect()->to($back)
            ->with('message', 'Penampilan seni dimulai (standby).');
    }

    /**
     * Mulai ulang penampilan seni yang sudah selesai (sudah_tampil → standby).
     * Reset nilai_akhir, waktu_tampil, diskualifikasi, hapus penilaian & medali lama.
     * Parity: analog dengan mulai_pertandingan() untuk tanding yang bisa dimulai ulang.
     */
    public function mulaiUlangPenampilan(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();

        // Ambil penampilan
        $penampilan = $db->table('penampilan_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();

        if ($penampilan === null) {
            return redirect()->back()->with('error', 'Penampilan tidak ditemukan.');
        }

        if ($penampilan->status_penampilan !== 'sudah_tampil') {
            return redirect()->back()->with('error', 'Hanya penampilan yang sudah selesai yang dapat diulang.');
        }

        // Cek apakah ada penampilan lain yang sedang berlangsung di gelanggang ini
        $aktif = $db->table('detail_jadwal_seni djs')
            ->select('ps.id_penampilan_seni')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('djs.id_penampilan_seni IS NOT NULL')
            ->whereNotIn('ps.status_penampilan', ['belum_tampil', 'sudah_tampil'])
            ->get()->getRow();

        // Tentukan back URL
        $jadwalRow = $db->table('detail_jadwal_seni')
            ->select('id_jadwal_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();
        $back = $jadwalRow
            ? '/sekretaris-pertandingan/jadwal-seni/' . (int) $jadwalRow->id_jadwal_seni
            : '/sekretaris-pertandingan';

        if ($aktif !== null) {
            return redirect()->to($back)->with('error', 'Masih ada penampilan yang berlangsung.');
        }

        // Reset penampilan: kembalikan ke kondisi awal
        $db->table('penampilan_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->update([
                'status_penampilan' => 'standby',
                'nilai_akhir'       => '0',
                'waktu_tampil'      => 0,
                'diskualifikasi'    => 0,
                'akses_penilaian'   => 'dibuka',
            ]);

        // Hapus penilaian lama
        $db->table('penilaian_seni')
            ->where('id_penampilan_seni', $idPenampilanSeni)
            ->delete();

        // Hapus medali lama untuk kelompok peserta ini
        $db->table('perolehan_medali_seni')
            ->where('id_kelompok_peserta_seni', $penampilan->id_kelompok_peserta_seni)
            ->delete();

        // Broadcast ke Layar bahwa seni berlangsung lagi
        helper('realtime');
        realtime_reset_room($idPenampilanSeni);
        realtime_emit_seni_berlangsung($this->idGelanggang(), $idPenampilanSeni);

        return redirect()->to($back)->with('message', 'Penampilan berhasil diulang dari awal.');
    }

    /**
     * Halaman timer tanding bila ada partai berlangsung; jika tidak, standby +
     * daftar partai untuk dimulai. Parity legacy timer_tanding().
     */
    public function timerTanding()
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());
        $daftarPartai = $this->pertandinganModel->getDaftarPartaiGelanggang($this->idGelanggang());

        if ($pertandingan === null) {
            return view('pertandingan/sekretaris/standby', [
                'title'         => 'Sekretaris — Standby',
                'mode_standby'  => 'tanding',
                'daftar_partai' => $daftarPartai,
                'daftar_seni'   => [],
                'nama_gelanggang' => session()->get('nama_gelanggang'),
            ]);
        }

        return view('pertandingan/sekretaris/timer_tanding', [
            'title'                  => 'Timer Tanding',
            'pertandingan'           => $pertandingan,
            'data_waktu'             => $pertandingan->data_waktu ? json_decode($pertandingan->data_waktu) : null,
            'atlet_merah'            => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'merah'),
            'atlet_biru'             => $this->pertandinganModel->getAtletPertandingan((int) $pertandingan->id_pertandingan, 'biru'),
            'data_format_penilaian'  => $this->getFormatListTanding(),
            'daftar_partai'          => $daftarPartai,
            'partai_next'            => $this->pertandinganModel->getPartaiTetangga((int) $pertandingan->id_jadwal_tanding, (int) $pertandingan->nomor_partai, 'next'),
            'partai_prev'            => $this->pertandinganModel->getPartaiTetangga((int) $pertandingan->id_jadwal_tanding, (int) $pertandingan->nomor_partai, 'prev'),
            'nama_gelanggang'        => session()->get('nama_gelanggang'),
        ]);
    }

    /**
     * Mulai sebuah partai (set status 'standby' → masuk timer).
     * Parity legacy mulai_pertandingan(): hanya boleh jika tak ada partai aktif
     * dan kedua atlet sudah ada.
     */
    public function mulaiPertandingan(int $idPertandingan)
    {
        $berlangsung = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());
        if ($berlangsung !== null) {
            return redirect()->to('/sekretaris-pertandingan/timer-tanding')
                ->with('error', 'Masih ada partai yang berlangsung.');
        }

        $target = $this->pertandinganModel->find($idPertandingan);
        if ($target === null) {
            return redirect()->to('/sekretaris-pertandingan')->with('error', 'Partai tidak ditemukan.');
        }
        if ($target->id_atlet_merah === null || $target->id_atlet_biru === null) {
            return redirect()->to('/sekretaris-pertandingan')->with('error', 'Atlet belum lengkap.');
        }

        $this->pertandinganModel->update($idPertandingan, ['status_pertandingan' => 'standby']);

        // Broadcast ke Layar home/standby bahwa tanding berlangsung.
        helper('realtime');
        realtime_emit_tanding_berlangsung($this->idGelanggang(), $idPertandingan);

        return redirect()->to('/sekretaris-pertandingan/timer-tanding');
    }

    /**
     * Pindah ke partai lain (Jump To Match). Parity legacy pindah_partai_tanding():
     * validasi atlet lengkap & tidak ada partai aktif, lalu mulai partai tujuan.
     */
    public function pindahPartaiTanding(int $idPertandingan)
    {
        $target = $this->pertandinganModel->find($idPertandingan);
        if ($target === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Partai tidak ditemukan.']);
        }
        if ($target->id_atlet_merah === null || $target->id_atlet_biru === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Atlet belum lengkap pada partai tujuan.']);
        }

        $berlangsung = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());
        if ($berlangsung !== null && (int) $berlangsung->id_pertandingan !== $idPertandingan) {
            return $this->response->setJSON(['status' => false, 'message' => 'Masih ada partai yang berlangsung.']);
        }

        // Set partai tujuan ke standby, redirect dilakukan client-side via reload.
        $this->pertandinganModel->update($idPertandingan, ['status_pertandingan' => 'standby']);

        helper('realtime');
        realtime_emit_tanding_berlangsung($this->idGelanggang(), $idPertandingan);
        
        // FIX #10: Emit ROOM_RESET agar juri/layar/kp reload state
        realtime_reset_room($idPertandingan);

        return $this->response->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Ubah konfigurasi waktu ronde (Configure Time). Parity legacy ubah_waktu_tanding().
     * Mode: pertandingan_ini | kelas_ini | kategori_lomba_ini | gelanggang_ini.
     */
    public function ubahWaktuTanding(int $idPertandingan)
    {
        $mode           = (string) $this->request->getPost('mode');
        $jumlahRonde    = (int) $this->request->getPost('jumlah_ronde');
        $waktuPerRonde  = (int) $this->request->getPost('waktu_per_ronde');
        $waktuIstirahat = (int) $this->request->getPost('waktu_istirahat');

        $modeLegal = ['pertandingan_ini', 'kelas_ini', 'kategori_lomba_ini', 'gelanggang_ini'];
        if (! in_array($mode, $modeLegal, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mode pengubah tidak ditemukan.']);
        }
        if (! in_array($jumlahRonde, [2, 3], true) || $waktuPerRonde <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Konfigurasi waktu tidak valid.']);
        }

        // Ambil partai aktif untuk konteks kelas/kategori/gelanggang.
        $partai = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());
        if ($partai === null || (int) $partai->id_pertandingan !== $idPertandingan) {
            return $this->response->setJSON(['status' => false, 'message' => 'Partai aktif tidak ditemukan.']);
        }

        $this->pertandinganModel->ubahWaktu(
            $idPertandingan,
            $mode,
            (int) $partai->id_kelas_tanding,
            (int) $partai->id_kategori_lomba,
            $this->idGelanggang(),
            $jumlahRonde,
            $waktuPerRonde,
            $waktuIstirahat
        );

        // Return JSON — JS (ubah_waktu) expects JSON response, then reloads.
        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'message' => 'Berhasil mengubah waktu.', 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Simpan status + data_waktu timer (AJAX). Parity legacy toggle_timer_tanding().
     *
     * Server-authoritative timer: bangun ulang data_waktu pakai build_data_waktu_state()
     * supaya semua client (layar, juri, KP) bisa drift-compensate via started_at_ms +
     * server_now_ms — sinkron walau hanya polling tanpa socket.
     */
    public function toggleTimerTanding(int $idPertandingan)
    {
        $status     = (string) $this->request->getPost('status_pertandingan');
        $rawWaktu   = $this->request->getPost('data_waktu');

        $statusLegal = ['berlangsung', 'berhenti', 'istirahat', 'standby', 'selesai'];
        if (! in_array($status, $statusLegal, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Status tidak valid.']);
        }

        // Parse client-side data_waktu (untuk ambil sisa_waktu + ronde + extra fields)
        $clientWaktu = null;
        if ($rawWaktu !== null) {
            $decoded = is_string($rawWaktu) ? json_decode((string) $rawWaktu, true) : (array) $rawWaktu;
            if (is_array($decoded)) {
                $clientWaktu = $decoded;
            }
        }

        // Build server-authoritative state
        helper('timer');
        $sisaWaktu = isset($clientWaktu['sisa_waktu']) ? (int) $clientWaktu['sisa_waktu'] : 0;
        $ronde     = isset($clientWaktu['ronde']) ? (int) $clientWaktu['ronde'] : 1;
        $authoritativeState = build_data_waktu_state($status, $sisaWaktu, $ronde, $clientWaktu);
        $authoritativeJson  = json_encode($authoritativeState);

        $this->pertandinganModel->setStatusDanWaktu($idPertandingan, $status, $authoritativeJson);

        // Push real-time timer ke room (Layar/Juri/KP).
        helper('realtime');
        realtime_emit_waktu($idPertandingan, $status, $authoritativeState);
        realtime_emit_match_status_change($idPertandingan, $status);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON([
                'status'     => true,
                'data_waktu' => $authoritativeState,
                'csrf_hash'  => csrf_hash(),
            ]);
    }

    /**
     * Pindah ronde aktif (AJAX). Parity legacy pindah_ronde_tanding().
     */
    public function pindahRondeTanding(int $idPertandingan)
    {
        $ronde = (string) $this->request->getPost('ronde_berikutnya');
        if (! in_array($ronde, ['1', '2', '3'], true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Ronde tidak valid.']);
        }

        $this->pertandinganModel->setRonde($idPertandingan, $ronde);
        $fresh = $this->pertandinganModel->find($idPertandingan);

        // Push real-time skor + ronde baru ke room.
        helper('realtime');
        realtime_emit_skor($idPertandingan, (int) $fresh->skor_merah, (int) $fresh->skor_biru, $ronde);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON([
                'status'       => true,
                'ronde'        => $ronde,
                'skor_merah'   => (int) $fresh->skor_merah,
                'skor_biru'    => (int) $fresh->skor_biru,
                'csrf_hash'    => csrf_hash(),
            ]);
    }

    /**
     * Selesaikan partai: set pemenang + jenis kemenangan.
     * Parity inti finalisasi (bracket-advancement di luar scope penilaian).
     */
    public function selesaikanPertandingan(int $idPertandingan)
    {
        $jenis  = (string) $this->request->getPost('jenis_kemenangan');
        $sudut  = (string) $this->request->getPost('sudut_pemenang'); // 'merah'|'biru'|''

        $jenisLegal = ['Teknik', 'BYE', 'Mutlak', 'Poin', 'Diskualifikasi', 'Undur Diri', 'Menang Angka'];
        if (! in_array($jenis, $jenisLegal, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Jenis kemenangan tidak valid.']);
        }

        $partai = $this->pertandinganModel->find($idPertandingan);
        if ($partai === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Partai tidak ditemukan.']);
        }

        $idPemenang = match ($sudut) {
            'merah' => $partai->id_atlet_merah,
            'biru'  => $partai->id_atlet_biru,
            default => null,
        };

        $this->pertandinganModel->selesaikanPertandingan($idPertandingan, $idPemenang !== null ? (int) $idPemenang : null, $jenis);

        // FIX #1: Advance bracket (medali + next-partai atlet + bagan JSON update)
        if ($idPemenang !== null) {
            $service = new \App\Services\BracketAdvancementService();
            $service->advanceTanding($partai, (int) $idPemenang, $jenis);
        }

        // Reset room real-time (bersihkan snapshot; klien akan reload via polling).
        helper('realtime');
        realtime_emit_pertandingan_selesai($idPertandingan, [
            'sudut_pemenang'    => $sudut,
            'jenis_kemenangan'  => $jenis,
        ]);
        realtime_reset_room($idPertandingan);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Polling status untuk sekretaris (tetap di timer, kirim skor terbaru).
     */
    public function refreshStatusPertandingan(?int $idPertandingan = null)
    {
        $pertandingan = $this->pertandinganModel->getPertandinganBerlangsung($this->idGelanggang());

        if ($pertandingan === null) {
            return $this->response->setJSON(['status' => true, 'reload' => $idPertandingan !== null, 'pindah_partai' => true]);
        }

        if ((int) $pertandingan->id_pertandingan !== (int) $idPertandingan) {
            return $this->response->setJSON(['status' => true, 'reload' => true]);
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

        return $this->response->setJSON([
            'status'        => false,
            'pertandingan'  => $pertandingan,
            'data_waktu'    => $dataWaktu,
            'server_now_ms' => (int) round(microtime(true) * 1000),
            'skor_merah'    => (int) $pertandingan->skor_merah,
            'skor_biru'     => (int) $pertandingan->skor_biru,
        ]);
    }

    // =====================================================================
    // PHASE 3 — TIMER SENI (POOL + BATTLE)
    // =====================================================================

    /**
     * Helper: query penampilan seni aktif di gelanggang.
     */
    private function getPenampilanSeniAktif(?int $idGelanggang = null): ?object
    {
        $idG = $idGelanggang ?? $this->idGelanggang();
        $db = \Config\Database::connect();

        // Step 1: Cari penampilan pool yang aktif (punya detail_jadwal_seni entry)
        $pool = $db->table('detail_jadwal_seni djs')
            ->select('ps.*, djs.nomor_partai, djs.id_jadwal_seni,
                kps.id_kompetisi_seni, kps.id_kontingen,
                k.nama_kontingen, ks.nomor_pool,
                sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan, sks.id_sub_kategori_seni,
                sks.format_penilaian as format_penilaian_sks,
                ku.nama_kategori_usia, ku.jenis_kelamin,
                js.id_gelanggang, g.nama_gelanggang')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang')
            ->where('js.id_gelanggang', $idG)
            ->where('djs.id_penampilan_seni IS NOT NULL')
            ->whereNotIn('ps.status_penampilan', ['belum_tampil', 'sudah_tampil'])
            ->get()->getRow();

        if ($pool !== null) {
            $pool->id_battle_seni = null;
            return $pool;
        }

        // Step 2: Cari penampilan battle yang aktif (via battle_seni)
        // Cek penampilan BIRU terlebih dahulu
        $battleBiru = $db->table('battle_seni bs')
            ->select('ps.*, djs.nomor_partai, djs.id_jadwal_seni, bs.id_battle_seni,
                kps.id_kompetisi_seni, kps.id_kontingen,
                k.nama_kontingen, ks.nomor_pool,
                sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan, sks.id_sub_kategori_seni,
                sks.format_penilaian as format_penilaian_sks,
                ku.nama_kategori_usia, ku.jenis_kelamin,
                js.id_gelanggang, g.nama_gelanggang')
            ->join('detail_jadwal_seni djs', 'djs.id_battle_seni = bs.id_battle_seni')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = bs.id_penampilan_seni_biru')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('js.id_gelanggang', $idG)
            ->whereNotIn('ps.status_penampilan', ['belum_tampil', 'sudah_tampil'])
            ->get()->getRow();

        if ($battleBiru !== null) {
            return $battleBiru;
        }

        // Jika biru tidak aktif, cek penampilan MERAH
        $battleMerah = $db->table('battle_seni bs')
            ->select('ps.*, djs.nomor_partai, djs.id_jadwal_seni, bs.id_battle_seni,
                kps.id_kompetisi_seni, kps.id_kontingen,
                k.nama_kontingen, ks.nomor_pool,
                sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan, sks.id_sub_kategori_seni,
                sks.format_penilaian as format_penilaian_sks,
                ku.nama_kategori_usia, ku.jenis_kelamin,
                js.id_gelanggang, g.nama_gelanggang')
            ->join('detail_jadwal_seni djs', 'djs.id_battle_seni = bs.id_battle_seni')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->join('gelanggang g', 'g.id_gelanggang = js.id_gelanggang')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = bs.id_penampilan_seni_merah')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('js.id_gelanggang', $idG)
            ->whereNotIn('ps.status_penampilan', ['belum_tampil', 'sudah_tampil'])
            ->get()->getRow();

        return $battleMerah;
    }

    /**
     * Helper: daftar penampilan seni pool di gelanggang (untuk standby + jump to match).
     */
    private function getDaftarSeniGelanggang(): array
    {
        $db = \Config\Database::connect();
        $idG = $this->idGelanggang();

        // Pool entries (id_penampilan_seni IS NOT NULL)
        $pool = $db->table('detail_jadwal_seni djs')
            ->select('djs.nomor_partai, djs.id_detail_jadwal_seni, djs.id_penampilan_seni, djs.id_battle_seni,
                ps.status_penampilan, ps.nilai_akhir, ps.diskualifikasi,
                k.nama_kontingen, sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan,
                ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni', 'left')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->where('js.id_gelanggang', $idG)
            ->where('djs.id_penampilan_seni IS NOT NULL')
            ->orderBy('djs.nomor_partai * 1', 'ASC', false)
            ->get()->getResult();

        // Battle entries — include both biru and merah penampilan as separate rows for jump-to-match
        $battle = $db->table('detail_jadwal_seni djs')
            ->select('djs.nomor_partai, djs.id_detail_jadwal_seni, djs.id_battle_seni,
                bs.id_penampilan_seni_biru as id_penampilan_seni,
                psb.status_penampilan, psb.nilai_akhir, psb.diskualifikasi,
                kb.nama_kontingen, sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan,
                ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')
            ->join('penampilan_seni psb', 'psb.id_penampilan_seni = bs.id_penampilan_seni_biru', 'left')
            ->join('kelompok_peserta_seni kpsb', 'kpsb.id_kelompok_peserta_seni = psb.id_kelompok_peserta_seni', 'left')
            ->join('kontingen kb', 'kb.id_kontingen = kpsb.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kpsb.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->where('js.id_gelanggang', $idG)
            ->where('djs.id_battle_seni IS NOT NULL')
            ->where('bs.id_penampilan_seni_biru IS NOT NULL')
            ->orderBy('djs.nomor_partai * 1', 'ASC', false)
            ->get()->getResult();

        // Also get battle merah entries
        $battleMerah = $db->table('detail_jadwal_seni djs')
            ->select('djs.nomor_partai, djs.id_detail_jadwal_seni, djs.id_battle_seni,
                bs.id_penampilan_seni_merah as id_penampilan_seni,
                psm.status_penampilan, psm.nilai_akhir, psm.diskualifikasi,
                km.nama_kontingen, sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan,
                ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('battle_seni bs', 'bs.id_battle_seni = djs.id_battle_seni')
            ->join('penampilan_seni psm', 'psm.id_penampilan_seni = bs.id_penampilan_seni_merah', 'left')
            ->join('kelompok_peserta_seni kpsm', 'kpsm.id_kelompok_peserta_seni = psm.id_kelompok_peserta_seni', 'left')
            ->join('kontingen km', 'km.id_kontingen = kpsm.id_kontingen', 'left')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kpsm.id_kompetisi_seni', 'left')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni', 'left')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba', 'left')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia', 'left')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->where('js.id_gelanggang', $idG)
            ->where('djs.id_battle_seni IS NOT NULL')
            ->where('bs.id_penampilan_seni_merah IS NOT NULL')
            ->orderBy('djs.nomor_partai * 1', 'ASC', false)
            ->get()->getResult();

        return array_merge($pool, $battle, $battleMerah);
    }

    /**
     * Helper: anggota kelompok peserta seni.
     */
    private function getAnggotaKelompok(int $idKelompokPesertaSeni): array
    {
        $db = \Config\Database::connect();
        return $db->table('peserta_seni')
            ->select('peserta_seni.id_peserta_seni, pendaftar.nama_pendaftar, kontingen.nama_kontingen')
            ->join('pendaftar', 'pendaftar.id_pendaftar = peserta_seni.id_pendaftar', 'left')
            ->join('kontingen', 'kontingen.id_kontingen = pendaftar.id_kontingen', 'left')
            ->where('peserta_seni.id_kelompok_peserta_seni', $idKelompokPesertaSeni)
            ->get()->getResult();
    }

    /**
     * Timer Seni — pool atau battle. Parity legacy timer_seni()/timer_seniv2().
     */
    public function timerSeni()
    {
        $db = \Config\Database::connect();
        $idGelanggang = $this->idGelanggang();

        $penampilanAktif = $this->getPenampilanSeniAktif($idGelanggang);

        if ($penampilanAktif === null) {
            // Standby mode seni
            return view('pertandingan/sekretaris/standby', [
                'title'           => 'Sekretaris — Standby Seni',
                'mode_standby'    => 'seni',
                'daftar_partai'   => [],
                'daftar_seni'     => $this->getDaftarSeniGelanggang(),
                'nama_gelanggang' => session()->get('nama_gelanggang'),
            ]);
        }

        // Ada penampilan aktif — tampilkan timer
        $daftarSeni = $this->getDaftarSeniGelanggang();
        $anggota    = $this->getAnggotaKelompok((int) $penampilanAktif->id_kelompok_peserta_seni);

        // Tentukan pool atau battle
        if ($penampilanAktif->id_battle_seni !== null) {
            // BATTLE mode
            $battle = $db->table('battle_seni')
                ->select('battle_seni.*, psb.id_kelompok_peserta_seni as kps_biru, psm.id_kelompok_peserta_seni as kps_merah')
                ->join('penampilan_seni psb', 'psb.id_penampilan_seni = battle_seni.id_penampilan_seni_biru', 'left')
                ->join('penampilan_seni psm', 'psm.id_penampilan_seni = battle_seni.id_penampilan_seni_merah', 'left')
                ->where('battle_seni.id_battle_seni', (int) $penampilanAktif->id_battle_seni)
                ->get()->getRow();

            $anggotaBiru  = $battle && $battle->kps_biru ? $this->getAnggotaKelompok((int) $battle->kps_biru) : [];
            $anggotaMerah = $battle && $battle->kps_merah ? $this->getAnggotaKelompok((int) $battle->kps_merah) : [];
            $isBiruActive = ($battle && (int) $penampilanAktif->id_penampilan_seni === (int) $battle->id_penampilan_seni_biru);

            return view('pertandingan/sekretaris/timer_seni_battle', [
                'title'           => 'Timer Seni Battle',
                'penampilan'      => $penampilanAktif,
                'battle'          => $battle,
                'anggota_biru'    => $anggotaBiru,
                'anggota_merah'   => $anggotaMerah,
                'is_biru_active'  => $isBiruActive,
                'format_list'     => $this->getFormatListSeni(),
                'daftar_seni'     => $daftarSeni,
                'nama_gelanggang' => session()->get('nama_gelanggang'),
            ]);
        }

        // POOL mode
        return view('pertandingan/sekretaris/timer_seni_pool', [
            'title'           => 'Timer Seni Pool',
            'penampilan'      => $penampilanAktif,
            'anggota'         => $anggota,
            'format_list'     => $this->getFormatListSeni(),
            'daftar_seni'     => $daftarSeni,
            'nama_gelanggang' => session()->get('nama_gelanggang'),
        ]);
    }

    /**
     * Toggle Timer Seni — start/stop/pause. Parity legacy toggle_timer_seni().
     */
    public function toggleTimerSeni(int $idPenampilanSeni)
    {
        $status    = (string) $this->request->getPost('status_penampilan');
        $waktu     = $this->request->getPost('waktu_tampil');

        $statusLegal = ['sedang_tampil', 'berhenti', 'standby'];
        if (! in_array($status, $statusLegal, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Status tidak valid.']);
        }

        $db = \Config\Database::connect();
        $update = ['status_penampilan' => $status];
        if ($waktu !== null) {
            $update['waktu_tampil'] = (int) $waktu;
        }
        $db->table('penampilan_seni')->where('id_penampilan_seni', $idPenampilanSeni)->update($update);

        // Emit realtime
        helper('realtime');
        realtime_emit('KONTROL_WAKTU_SENI', [
            'id_penampilan_seni' => $idPenampilanSeni,
            'status_penampilan'  => $status,
            'waktu_tampil'       => $waktu !== null ? (int) $waktu : null,
            'server_now_ms'      => (int) (microtime(true) * 1000),
        ]);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Reset Timer Seni. Parity legacy timer_reset_seni().
     */
    public function timerResetSeni(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();
        $penampilan = $db->table('penampilan_seni')->where('id_penampilan_seni', $idPenampilanSeni)->get()->getRow();

        if ($penampilan === null || ! in_array($penampilan->status_penampilan, ['berhenti', 'standby'], true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Tidak bisa reset saat ini.']);
        }

        $db->table('penampilan_seni')->where('id_penampilan_seni', $idPenampilanSeni)
            ->update(['status_penampilan' => 'standby', 'waktu_tampil' => 0]);

        helper('realtime');
        realtime_emit('KONTROL_WAKTU_SENI', [
            'id_penampilan_seni' => $idPenampilanSeni,
            'status_penampilan'  => 'standby',
            'waktu_tampil'       => 0,
        ]);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Selesaikan Penampilan Seni — hitung nilai akhir, set status sudah_tampil.
     * Parity legacy selesaikan_penampilan_seni().
     * Returns: { status: true, input_medali: bool }
     */
    public function selesaikanPenampilanSeni(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();
        $penampilan = $db->table('penampilan_seni ps')
            ->select('ps.*, kps.id_kompetisi_seni, sks.sistem_penampilan, ps.babak')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->where('ps.id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();

        if ($penampilan === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        // Hitung nilai akhir dari penilaian juri
        $nilaiAkhir = $this->hitungNilaiAkhirSeni($idPenampilanSeni);

        $db->table('penampilan_seni')->where('id_penampilan_seni', $idPenampilanSeni)
            ->update(['nilai_akhir' => $nilaiAkhir, 'status_penampilan' => 'sudah_tampil']);

        $inputMedali  = false;
        $giliranInfo  = null;
        if ($penampilan->sistem_penampilan === 'pool') {
            $belumTampil = $db->table('penampilan_seni ps')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->where('kps.id_kompetisi_seni', (int) $penampilan->id_kompetisi_seni)
                ->where('ps.babak', $penampilan->babak)
                ->where('ps.status_penampilan', 'belum_tampil')
                ->countAllResults();
            $inputMedali = ($belumTampil === 0);
        } else {
            $battle = $db->table('battle_seni')
                ->where('id_penampilan_seni_biru', $idPenampilanSeni)
                ->orWhere('id_penampilan_seni_merah', $idPenampilanSeni)
                ->get()->getRow();
            if ($battle) {
                $isBiru = ((int) $battle->id_penampilan_seni_biru === $idPenampilanSeni);
                $lawanId = $isBiru ? (int) $battle->id_penampilan_seni_merah : (int) $battle->id_penampilan_seni_biru;
                $lawan = $db->table('penampilan_seni')->where('id_penampilan_seni', $lawanId)->get()->getRow();
                $inputMedali = ($lawan && $lawan->status_penampilan === 'sudah_tampil');

                if (!$inputMedali) {
                    $db->table('penampilan_seni')->where('id_penampilan_seni', $lawanId)
                        ->update(['status_penampilan' => 'standby', 'waktu_tampil' => 0]);
                    $giliranInfo = [
                        'next_penampilan_id' => $lawanId,
                        'corner'             => $isBiru ? 'merah' : 'biru',
                        'label'              => $isBiru ? 'Sudut Merah' : 'Sudut Biru',
                    ];
                }
            }
        }

        helper('realtime');
        realtime_emit_penampilan_selesai($idPenampilanSeni, ['nilai_akhir' => $nilaiAkhir]);
        realtime_emit_seni_selesai($idPenampilanSeni, ['nilai_akhir' => $nilaiAkhir]);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'input_medali' => $inputMedali, 'nilai_akhir' => $nilaiAkhir,
                       'giliran_selanjutnya' => $giliranInfo, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Hitung nilai akhir seni menggunakan PersilatSeniService.
     * Service sudah handle update terpilih flag dan catatan_nilai_sama.
     */
    private function hitungNilaiAkhirSeni(int $idPenampilanSeni): string
    {
        $db = \Config\Database::connect();
        
        // Get penampilan object
        $penampilan = $db->table('penampilan_seni')->where('id_penampilan_seni', $idPenampilanSeni)->get()->getRow();
        if ($penampilan === null) {
            return '0';
        }

        // Get penilaian juri
        $penilaianJuri = $this->penilaianSeniModel->getByPenampilan($idPenampilanSeni);
        if (empty($penilaianJuri)) {
            return '0';
        }

        // Service sudah handle:
        // - hitung median, standar_deviasi, median_kebenaran, hukuman
        // - update terpilih flag (0/1) untuk juri yang dipilih
        // - save catatan_nilai_sama JSON ke penampilan_seni
        $nilaiAkhir = $this->seniService->hitungNilaiAkhir($penampilan, $penilaianJuri);

        if ($nilaiAkhir === false) {
            return '0';
        }

        return number_format($nilaiAkhir, 3, '.', '');
    }

    /**
     * Pilih Pemenang Battle Seni. Parity legacy pilih_pemenang_battle_seni().
     */
    public function pilihPemenangBattleSeni(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();
        $idPemenang = (int) $this->request->getPost('id_penampilan_seni_pemenang');
        $jenisKemenangan = (string) ($this->request->getPost('jenis_kemenangan') ?: 'poin');

        $battle = $db->table('battle_seni')
            ->where('id_penampilan_seni_biru', $idPenampilanSeni)
            ->orWhere('id_penampilan_seni_merah', $idPenampilanSeni)
            ->get()->getRow();

        if ($battle === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Battle tidak ditemukan.']);
        }

        $this->battleSeniModel->setPemenang((int) $battle->id_battle_seni, $idPemenang, $jenisKemenangan);

        // FIX #2: Advance bracket (medali + bagan_battle_seni JSON update)
        // Note: BattleSeniModel::setPemenang() already places winner into next battle slot;
        // BracketAdvancementService menangani medali + JSON bracket display.
        if ($idPemenang > 0) {
            $service = new \App\Services\BracketAdvancementService();
            $service->advanceBattleSeni($battle, $idPemenang);
        }

        // Realtime: kasih tahu klien (juri/layar) bahwa penampilan + battle selesai
        helper('realtime');
        realtime_emit_penampilan_selesai($idPenampilanSeni, [
            'id_battle_seni'    => $battle->id_battle_seni,
            'id_pemenang'       => $idPemenang,
            'jenis_kemenangan'  => $jenisKemenangan,
        ]);
        realtime_emit_seni_selesai($idPenampilanSeni, [
            'id_battle_seni'    => $battle->id_battle_seni,
            'id_pemenang'       => $idPemenang,
            'jenis_kemenangan'  => $jenisKemenangan,
        ]);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Pindah Partai Seni (jump to another performance). Parity legacy pindah_partai_seni().
     * Accept POST partai_selanjutnya (nomor_partai) and return JSON for AJAX.
     */
    public function pindahPartaiSeni()
    {
        $db = \Config\Database::connect();
        $nomorPartaiSelanjutnya = (int) $this->request->getPost('partai_selanjutnya');
        
        if ($nomorPartaiSelanjutnya <= 0) {
            return $this->response->setJSON(['status' => false, 'message' => 'Nomor partai tidak valid.']);
        }

        // Cari detail_jadwal_seni dengan nomor_partai di gelanggang ini
        $detailJadwalSeni = $db->table('detail_jadwal_seni djs')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('djs.nomor_partai', $nomorPartaiSelanjutnya)
            ->get()->getRow();

        if ($detailJadwalSeni === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Partai tidak ditemukan.']);
        }

        // Tentukan id_penampilan_seni dari detail_jadwal_seni
        $idPenampilanSeni = null;
        if (!empty($detailJadwalSeni->id_penampilan_seni)) {
            // Pool mode
            $idPenampilanSeni = (int) $detailJadwalSeni->id_penampilan_seni;
            
            // Verify penampilan exists
            $penampilanExists = $db->table('penampilan_seni')
                ->where('id_penampilan_seni', $idPenampilanSeni)
                ->countAllResults();
            if ($penampilanExists === 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Data penampilan tidak ditemukan di database.']);
            }
        } elseif (!empty($detailJadwalSeni->id_battle_seni)) {
            // Battle mode — set biru performance to standby (akan dimulai first)
            $battle = $db->table('battle_seni')
                ->where('id_battle_seni', (int) $detailJadwalSeni->id_battle_seni)
                ->get()->getRow();
            
            if (!$battle) {
                return $this->response->setJSON(['status' => false, 'message' => 'Data battle tidak ditemukan.']);
            }
            
            // Verify biru penampilan exists dan bukan null
            if (empty($battle->id_penampilan_seni_biru)) {
                return $this->response->setJSON(['status' => false, 'message' => 'Data penampilan battle tidak lengkap (sudut biru kosong).']);
            }
            
            $penampilanBiruExists = $db->table('penampilan_seni')
                ->where('id_penampilan_seni', (int) $battle->id_penampilan_seni_biru)
                ->countAllResults();
            if ($penampilanBiruExists === 0) {
                return $this->response->setJSON(['status' => false, 'message' => 'Data penampilan sudut biru tidak ditemukan di database.']);
            }
            
            $idPenampilanSeni = (int) $battle->id_penampilan_seni_biru;
        }

        if ($idPenampilanSeni === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Data penampilan seni tidak lengkap - partai tidak punya penampilan pool maupun battle.']);
        }

        // Pastikan tidak ada penampilan aktif lain
        $aktif = $this->getPenampilanSeniAktif();
        if ($aktif !== null && (int) $aktif->id_penampilan_seni !== $idPenampilanSeni) {
            return $this->response->setJSON(['status' => false, 'message' => 'Masih ada penampilan yang berlangsung.']);
        }

        // Set target ke standby
        $updated = $db->table('penampilan_seni')->where('id_penampilan_seni', $idPenampilanSeni)
            ->update(['status_penampilan' => 'standby']);

        // DEBUG: verify update sukses
        if (!$updated) {
            log_message('error', "[pindahPartaiSeni] Gagal update status_penampilan ke standby untuk id_penampilan_seni={$idPenampilanSeni}");
            return $this->response->setJSON(['status' => false, 'message' => 'Gagal mengupdate status penampilan.']);
        }

        // Verify penampilan exists in detail_jadwal_seni untuk gelanggang ini
        $verify = $db->table('detail_jadwal_seni djs')
            ->join('jadwal_seni js', 'js.id_jadwal_seni = djs.id_jadwal_seni')
            ->where('js.id_gelanggang', $this->idGelanggang())
            ->where('djs.nomor_partai', $nomorPartaiSelanjutnya)
            ->countAllResults();

        if ($verify === 0) {
            log_message('error', "[pindahPartaiSeni] Partai {$nomorPartaiSelanjutnya} tidak ditemukan di gelanggang {$this->idGelanggang()} setelah update");
        }

        // Emit SENI_BERLANGSUNG ke room gelanggang → layar standby auto-redirect ke /layar/seni.
        // (RESET_ROOM saja tidak cukup: layar standby dengar event SENI_BERLANGSUNG, bukan ROOM_RESET.)
        helper('realtime');
        realtime_reset_room($idPenampilanSeni);
        realtime_emit_seni_berlangsung($this->idGelanggang(), $idPenampilanSeni);

        log_message('info', "[pindahPartaiSeni] Sukses pindah ke partai {$nomorPartaiSelanjutnya}, id_penampilan_seni={$idPenampilanSeni}, id_gelanggang={$this->idGelanggang()}");

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Diskualifikasi penampilan seni. Parity legacy diskualifikasi_penampilan_seni().
     */
    public function diskualifikasiPenampilanSeni(int $idPenampilanSeni)
    {
        $this->penampilanSeniModel->diskualifikasi($idPenampilanSeni);

        $db = \Config\Database::connect();

        $penampilan = $db->table('penampilan_seni ps')
            ->select('kps.id_kompetisi_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->where('ps.id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();

        $inputMedali = false;
        if ($penampilan) {
            $belumTampil = $db->table('penampilan_seni ps')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->where('kps.id_kompetisi_seni', (int) $penampilan->id_kompetisi_seni)
                ->where('ps.status_penampilan', 'belum_tampil')
                ->countAllResults();
            $inputMedali = ($belumTampil === 0);
        }

        // FIX #10: Emit realtime event saat DQ agar klien refresh display
        helper('realtime');
        realtime_emit_hukuman_update($idPenampilanSeni, ['diskualifikasi' => 1]);
        realtime_emit_update_nilai_seni($idPenampilanSeni);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'input_medali' => $inputMedali, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Batalkan DQ seni. Parity legacy batalkan_diskualifikasi_penampilan_seni().
     */
    public function batalkanDiskualifikasiSeni(int $idPenampilanSeni)
    {
        $nilaiAkhir = $this->hitungNilaiAkhirSeni($idPenampilanSeni);

        $this->penampilanSeniModel->batalkanDiskualifikasi($idPenampilanSeni);
        $this->penampilanSeniModel->update($idPenampilanSeni, ['nilai_akhir' => $nilaiAkhir]);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'nilai_akhir' => $nilaiAkhir, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Input manual juara seni (set medali). Parity legacy input_manual_juara_seni().
     */
    public function inputManualJuaraSeni()
    {
        $jenisMedali = $this->request->getPost('jenis_medali');
        $idPenampilanSeni = (int) $this->request->getPost('id_penampilan_seni');

        if (empty($jenisMedali) || ! is_array($jenisMedali)) {
            return $this->response->setJSON(['status' => true]); // skip jika kosong (parity CI3)
        }

        $db = \Config\Database::connect();
        $penampilan = $db->table('penampilan_seni ps')
            ->select('kps.id_kompetisi_seni')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->where('ps.id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();

        if ($penampilan === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Penampilan tidak ditemukan.']);
        }

        // Get semua kelompok peserta seni di kompetisi ini, ordered by nilai_akhir DESC
        $kelompok = $db->table('kelompok_peserta_seni kps')
            ->select('kps.id_kelompok_peserta_seni, ps.id_penampilan_seni, ps.nilai_akhir')
            ->join('penampilan_seni ps', 'ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni')
            ->where('kps.id_kompetisi_seni', (int) $penampilan->id_kompetisi_seni)
            ->orderBy('CAST(ps.nilai_akhir AS DECIMAL(10,3))', 'DESC', false)
            ->get()->getResult();

        foreach ($kelompok as $idx => $row) {
            $medali = $jenisMedali[$idx] ?? null;
            if ($medali !== null) {
                $db->table('kelompok_peserta_seni')
                    ->where('id_kelompok_peserta_seni', (int) $row->id_kelompok_peserta_seni)
                    ->update(['jenis_medali' => $medali]);
            }
        }

        // FIX #10: Emit realtime update agar layar refresh medali display
        helper('realtime');
        realtime_emit_update_nilai_seni($idPenampilanSeni);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Data penentuan juara seni (pool). Parity legacy get_data_penentuan_juara().
     */
    public function getDataPenentuanJuara(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();
        $penampilan = $db->table('penampilan_seni ps')
            ->select('kps.id_kompetisi_seni, sks.sistem_penampilan')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->where('ps.id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();

        if ($penampilan === null) {
            return $this->response->setJSON([]);
        }

        if ($penampilan->sistem_penampilan === 'pool') {
            $data = $db->table('detail_jadwal_seni djs')
                ->select('djs.nomor_partai, ps.id_penampilan_seni, ps.nilai_akhir, ps.diskualifikasi,
                    ps.waktu_tampil, ps.catatan_nilai_sama,
                    k.nama_kontingen, kps.jenis_medali')
                ->join('penampilan_seni ps', 'ps.id_penampilan_seni = djs.id_penampilan_seni')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
                ->where('kps.id_kompetisi_seni', (int) $penampilan->id_kompetisi_seni)
                ->orderBy('CAST(ps.nilai_akhir AS DECIMAL(10,3))', 'DESC', false)
                ->get()->getResult();
            return $this->response->setJSON($data);
        }

        // Battle
        $battle = $db->table('battle_seni')
            ->where('id_penampilan_seni_biru', $idPenampilanSeni)
            ->orWhere('id_penampilan_seni_merah', $idPenampilanSeni)
            ->get()->getRow();

        return $this->response->setJSON($battle ?: []);
    }

    /**
     * Polling status penampilan seni aktif — refresh skor/nilai juri.
     * Parity legacy refresh_status_seni().
     */
    public function refreshStatusSeni()
    {
        $idPenampilanSeni = $this->request->getPost('id_penampilan_seni');
        $waktu            = $this->request->getPost('waktu');
        $db = \Config\Database::connect();

        $aktif = $this->getPenampilanSeniAktif();

        if ($aktif === null) {
            return $this->response
                ->setHeader('X-CSRF-TOKEN', csrf_hash())
                ->setJSON(['status' => true, 'reload' => false, 'csrf_hash' => csrf_hash()]);
        }

        if ($idPenampilanSeni !== null && (int) $aktif->id_penampilan_seni === (int) $idPenampilanSeni) {
            // Sync waktu ke DB (parity legacy)
            if ($waktu !== null) {
                $db->table('penampilan_seni')->where('id_penampilan_seni', (int) $idPenampilanSeni)
                    ->update(['waktu_tampil' => (int) $waktu]);
            }
            // Return data penilaian juri
            $dataNilai = $db->table('penilaian_seni')
                ->where('id_penampilan_seni', (int) $idPenampilanSeni)
                ->get()->getResult();

            return $this->response
                ->setHeader('X-CSRF-TOKEN', csrf_hash())
                ->setJSON(['status' => false, 'data_nilai' => $dataNilai, 'penampilan' => $aktif, 'csrf_hash' => csrf_hash()]);
        }

        // Penampilan aktif berbeda dari yang diminta — reload
        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'reload' => true, 'csrf_hash' => csrf_hash()]);
    }

    // =====================================================================
    // PHASE 4 — ADVANCED FEATURES
    // =====================================================================

    /**
     * Ganti Format Penilaian Tanding. Parity legacy ganti_format_penilaian_tanding().
     * Mode: pertandingan_ini | kelas_ini | kategori_lomba_ini | gelanggang_ini.
     * Hapus penilaian lama, buat ulang dengan format baru.
     */
    public function gantiFormatPenilaianTanding(int $idPertandingan)
    {
        $db = \Config\Database::connect();
        $formatPenilaian = (string) $this->request->getPost('format_penilaian');
        $jumlahJuri      = (int) $this->request->getPost('jumlah_juri');
        $mode            = (string) $this->request->getPost('mode');

        if (empty($formatPenilaian) || $jumlahJuri < 1) {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter tidak valid.']);
        }

        // Load format JSON — getFormatListTanding() returns filenames WITH .json extension
        // Strip .json if caller submitted full filename, then append .json for path lookup.
        $formatBase = preg_replace('/\.json$/i', '', $formatPenilaian);
        $jsonPath = FCPATH . 'assets/penilaian/format-penilaian/tanding/' . $formatBase . '.json';
        if (! is_file($jsonPath)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Format penilaian tidak ditemukan.']);
        }
        $formatJson = file_get_contents($jsonPath);

        // Ambil partai untuk konteks
        $partai = $db->table('detail_jadwal_tanding')
            ->select('detail_jadwal_tanding.*, pertandingan.*, kelas_tanding.id_kelas_tanding,
                kategori_lomba.id_kategori_lomba, jadwal_tanding.id_gelanggang')
            ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan')
            ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
            ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
            ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
            ->join('jadwal_tanding', 'jadwal_tanding.id_jadwal_tanding = detail_jadwal_tanding.id_jadwal_tanding')
            ->where('detail_jadwal_tanding.id_pertandingan', $idPertandingan)
            ->get()->getRow();

        if ($partai === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Pertandingan tidak ditemukan.']);
        }

        // Tentukan target berdasarkan mode
        $modeLegal = ['pertandingan_ini', 'kelas_ini', 'kategori_lomba_ini', 'gelanggang_ini'];
        if (! in_array($mode, $modeLegal, true)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Mode tidak valid.']);
        }

        switch ($mode) {
            case 'pertandingan_ini':
                $targetIds = [$idPertandingan];
                break;
            case 'kelas_ini':
                $targetIds = array_map(fn($r) => (int) $r->id_pertandingan,
                    $db->table('pertandingan')->select('pertandingan.id_pertandingan')
                        ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
                        ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                        ->where('kelas_tanding.id_kelas_tanding', (int) $partai->id_kelas_tanding)
                        ->get()->getResult());
                break;
            case 'kategori_lomba_ini':
                $targetIds = array_map(fn($r) => (int) $r->id_pertandingan,
                    $db->table('pertandingan')->select('pertandingan.id_pertandingan')
                        ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding')
                        ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                        ->join('kategori_lomba', 'kategori_lomba.id_kategori_lomba = kelas_tanding.id_kategori_lomba')
                        ->where('kategori_lomba.id_kategori_lomba', (int) $partai->id_kategori_lomba)
                        ->get()->getResult());
                break;
            case 'gelanggang_ini':
                $targetIds = array_map(fn($r) => (int) $r->id_pertandingan,
                    $this->pertandinganModel->getDaftarPartaiGelanggang($this->idGelanggang()));
                break;
        }

        // Ambil daftar juri di gelanggang
        $daftarJuri = $db->table('perangkat_pertandingan')
            ->where('id_gelanggang', $this->idGelanggang())
            ->where('posisi', 'juri')
            ->get()->getResult();

        // Untuk setiap target: hapus penilaian lama, buat ulang
        foreach ($targetIds as $targetId) {
            $db->table('penilaian_tanding')->where('id_pertandingan', $targetId)->delete();

            foreach ($daftarJuri as $idx => $juri) {
                if ($idx >= $jumlahJuri) break;
                $db->table('penilaian_tanding')->insert([
                    'id_pertandingan'          => $targetId,
                    'id_perangkat_pertandingan' => (int) $juri->id_perangkat_pertandingan,
                    'penilaian_merah'          => $formatJson,
                    'penilaian_biru'           => $formatJson,
                ]);
            }

            // Reset skor
            $db->table('pertandingan')->where('id_pertandingan', $targetId)
                ->update(['skor_merah' => 0, 'skor_biru' => 0]);
        }

        // Update format di kelas_tanding
        $db->table('kelas_tanding')->where('id_kelas_tanding', (int) $partai->id_kelas_tanding)
            ->update(['format_penilaian' => $formatPenilaian]);

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'message' => 'Format penilaian berhasil diubah.', 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Form Edit Atlet Tanding — return JSON data peserta.
     * Parity legacy form_edit_atlet_tanding().
     */
    public function formEditAtletTanding(int $idPertandingan)
    {
        $db = \Config\Database::connect();
        $pertandingan = $this->pertandinganModel->find($idPertandingan);
        if ($pertandingan === null) {
            return $this->response->setJSON(['status' => false, 'message' => 'Partai tidak ditemukan.']);
        }

        // Get peserta tanding dari kompetisi yang sama
        $pesertaTanding = $db->table('peserta_tanding pt')
            ->select('pt.id_peserta_tanding, p.nama_pendaftar, k.nama_kontingen')
            ->join('pendaftar p', 'p.id_pendaftar = pt.id_pendaftar')
            ->join('kontingen k', 'k.id_kontingen = p.id_kontingen')
            ->orderBy('p.nama_pendaftar', 'ASC')
            ->get()->getResult();

        return $this->response->setJSON([
            'status'           => true,
            'pertandingan'     => $pertandingan,
            'peserta_tanding'  => $pesertaTanding,
        ]);
    }

    /**
     * Edit Atlet Tanding — simpan perubahan. Parity legacy edit_atlet_tanding().
     */
    public function editAtletTanding(int $idPertandingan)
    {
        $idAtletBiru  = $this->request->getPost('id_atlet_biru');
        $idAtletMerah = $this->request->getPost('id_atlet_merah');

        $update = [
            'id_atlet_biru'  => ($idAtletBiru === 'NULL' || $idAtletBiru === '') ? null : (int) $idAtletBiru,
            'id_atlet_merah' => ($idAtletMerah === 'NULL' || $idAtletMerah === '') ? null : (int) $idAtletMerah,
        ];

        $this->pertandinganModel->update($idPertandingan, $update);

        $autoplay = $this->request->getPost('autoplay');
        if ($autoplay === 'true') {
            return redirect()->to('/sekretaris-pertandingan/mulai-pertandingan/' . $idPertandingan);
        }

        return redirect()->to('/sekretaris-pertandingan/timer-tanding')
            ->with('message', 'Atlet berhasil diperbarui.');
    }

    /**
     * Info Penimbangan (AJAX). Return data penimbangan dari partai aktif.
     */
    public function infoPenimbangan(int $idPertandingan)
    {
        $db = \Config\Database::connect();
        $pertandingan = $this->pertandinganModel->find($idPertandingan);
        if ($pertandingan === null) {
            return $this->response->setJSON(['status' => false]);
        }

        // Riwayat penimbangan: partai lain dimana atlet ini pernah bertanding
        $riwayatBiru = $db->table('pertandingan')
            ->select('pertandingan.nomor_pertandingan, pertandingan.berat_biru, pertandingan.berat_merah,
                pertandingan.hasil_timbang_biru, pertandingan.hasil_timbang_merah, pertandingan.id_atlet_biru, pertandingan.id_atlet_merah')
            ->where("(pertandingan.id_atlet_biru = {$pertandingan->id_atlet_biru} OR pertandingan.id_atlet_merah = {$pertandingan->id_atlet_biru})", null, false)
            ->orderBy('nomor_pertandingan', 'DESC')
            ->get()->getResult();

        $riwayatMerah = $db->table('pertandingan')
            ->select('pertandingan.nomor_pertandingan, pertandingan.berat_biru, pertandingan.berat_merah,
                pertandingan.hasil_timbang_biru, pertandingan.hasil_timbang_merah, pertandingan.id_atlet_biru, pertandingan.id_atlet_merah')
            ->where("(pertandingan.id_atlet_biru = {$pertandingan->id_atlet_merah} OR pertandingan.id_atlet_merah = {$pertandingan->id_atlet_merah})", null, false)
            ->orderBy('nomor_pertandingan', 'DESC')
            ->get()->getResult();

        return $this->response->setJSON([
            'status'          => true,
            'pertandingan'    => $pertandingan,
            'riwayat_biru'    => $riwayatBiru,
            'riwayat_merah'   => $riwayatMerah,
        ]);
    }

    /**
     * Ganti Format Penilaian Seni. Parity legacy ganti_format_penilaian_seni().
     * Menghapus penilaian_seni lama, buat ulang dengan format baru.
     */
    public function gantiFormatPenilaianSeni(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();
        $formatPenilaian = (string) $this->request->getPost('format_penilaian');
        $jumlahJuri      = (int) $this->request->getPost('jumlah_juri');
        $mode            = (string) ($this->request->getPost('mode') ?: 'penampilan_ini');
        $passcode        = (string) ($this->request->getPost('passcode') ?: '');

        if (empty($formatPenilaian) || $jumlahJuri < 1) {
            return $this->response->setJSON(['status' => false, 'message' => 'Parameter tidak valid.']);
        }

        // Load format JSON seni (support subdirectory path like persilat/tunggal/full or flat name)
        $jsonPath = FCPATH . 'assets/penilaian/format-penilaian/seni/' . $formatPenilaian;
        if (! str_ends_with($jsonPath, '.json')) $jsonPath .= '.json';
        if (! is_file($jsonPath)) {
            return $this->response->setJSON(['status' => false, 'message' => 'Format penilaian seni tidak ditemukan.']);
        }
        $formatJson = file_get_contents($jsonPath);

        $targetPenampilan = [$idPenampilanSeni];
        if ($mode === 'pool_ini' || $mode === 'battle_ini') {
            $penampilanRow = $db->table('penampilan_seni ps')
                ->select('kps.id_kompetisi_seni, ps.babak, sks.sistem_penampilan')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
                ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
                ->where('ps.id_penampilan_seni', $idPenampilanSeni)->get()->getRow();
            if ($penampilanRow) {
                $targetPenampilan = array_map(fn($r) => (int) $r->id_penampilan_seni,
                    $db->table('penampilan_seni ps')
                        ->select('ps.id_penampilan_seni')
                        ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                        ->where('kps.id_kompetisi_seni', (int) $penampilanRow->id_kompetisi_seni)
                        ->where('ps.babak', $penampilanRow->babak)
                        ->get()->getResult());
            }
        } elseif ($mode === 'kategori_ini') {
            $penampilanRow = $db->table('penampilan_seni ps')
                ->select('sks.id_sub_kategori_seni')
                ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
                ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
                ->where('ps.id_penampilan_seni', $idPenampilanSeni)->get()->getRow();
            if ($penampilanRow) {
                $targetPenampilan = array_map(fn($r) => (int) $r->id_penampilan_seni,
                    $db->table('penampilan_seni ps')
                        ->select('ps.id_penampilan_seni')
                        ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
                        ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
                        ->where('ks.id_sub_kategori_seni', (int) $penampilanRow->id_sub_kategori_seni)
                        ->get()->getResult());
            }
        }

        // Ambil daftar juri seni di gelanggang
        $daftarJuri = $db->table('perangkat_pertandingan')
            ->where('id_gelanggang', $this->idGelanggang())
            ->where('posisi', 'juri')
            ->get()->getResult();

        // Untuk setiap target: hapus penilaian lama, buat ulang
        foreach ($targetPenampilan as $targetId) {
            $this->penilaianSeniModel->hapusByPenampilan($targetId);

            foreach ($daftarJuri as $idx => $juri) {
                if ($idx >= $jumlahJuri) break;
                $this->penilaianSeniModel->buatPenilaian($targetId, (int) $juri->id_perangkat_pertandingan, $formatJson);
            }

            $this->penampilanSeniModel->update($targetId, ['nilai_akhir' => '0']);
        }

        return $this->response
            ->setHeader('X-CSRF-TOKEN', csrf_hash())
            ->setJSON(['status' => true, 'message' => 'Format penilaian seni berhasil diubah.', 'csrf_hash' => csrf_hash()]);
    }

    /**
     * Scan assets/penilaian/format-penilaian/seni/ for available JSON formats.
     */
    private function getFormatListSeni(): array
    {
        $basePath = FCPATH . 'assets/penilaian/format-penilaian/seni/persilat/';
        if (! is_dir($basePath)) return [];

        $list = [];
        $jenisDirs = array_filter(glob($basePath . '*'), 'is_dir');
        foreach ($jenisDirs as $jenisPath) {
            $jenis = basename($jenisPath);
            $files = glob($jenisPath . '/*.json');
            foreach ($files as $file) {
                $filename = basename($file);
                $label = ucwords(str_replace('_', ' ', pathinfo($filename, PATHINFO_FILENAME)));
                $list[] = [
                    'value' => 'persilat/' . $jenis . '/' . $filename,
                    'label' => $label . ' (PERSILAT ' . ucwords(str_replace('_', ' ', $jenis)) . ')',
                ];
            }
        }
        return $list;
    }

    /**
     * Print hasil penilaian seni pool (tunggal/beregu). Parity legacy print_tunggal()/print_beregu().
     * Menerima id_penampilan_seni untuk mencari konteks pool/kompetisi.
     */
    public function printSeniPool(int $idPenampilanSeni)
    {
        $db = \Config\Database::connect();
        $penampilan = $db->table('penampilan_seni ps')
            ->select('ps.*, kps.id_kompetisi_seni, kps.id_kontingen,
                ks.nomor_pool, sks.jenis_seni, sks.nama_seni, sks.sistem_penampilan,
                kl.peraturan_pertandingan, ku.nama_kategori_usia, ku.jenis_kelamin')
            ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
            ->join('kompetisi_seni ks', 'ks.id_kompetisi_seni = kps.id_kompetisi_seni')
            ->join('sub_kategori_seni sks', 'sks.id_sub_kategori_seni = ks.id_sub_kategori_seni')
            ->join('kategori_lomba kl', 'kl.id_kategori_lomba = sks.id_kategori_lomba')
            ->join('kategori_usia ku', 'ku.id_kategori_usia = kl.id_kategori_usia')
            ->where('ps.id_penampilan_seni', $idPenampilanSeni)
            ->get()->getRow();

        if ($penampilan === null) {
            return redirect()->to('/sekretaris-pertandingan/timer-seni')
                ->with('error', 'Data penampilan tidak ditemukan.');
        }

        $isBeregu = in_array(strtolower($penampilan->jenis_seni ?? ''), ['beregu', 'ganda', 'trio', 'berkelompok', 'berpasangan']);

        // Semua kelompok peserta di pool yang sama
        $kelompokList = $db->table('kelompok_peserta_seni kps')
            ->select('kps.id_kelompok_peserta_seni, kps.id_kontingen, k.nama_kontingen,
                djs.nomor_partai,
                ps.id_penampilan_seni, ps.waktu_tampil, ps.nilai_akhir, ps.diskualifikasi')
            ->join('penampilan_seni ps', 'ps.id_kelompok_peserta_seni = kps.id_kelompok_peserta_seni')
            ->join('kontingen k', 'k.id_kontingen = kps.id_kontingen')
            ->join('detail_jadwal_seni djs', 'djs.id_penampilan_seni = ps.id_penampilan_seni', 'left')
            ->where('kps.id_kompetisi_seni', (int) $penampilan->id_kompetisi_seni)
            ->where('ps.babak', $penampilan->babak ?? $penampilan->babak_pool)
            ->orderBy('djs.nomor_partai * 1', 'ASC', false)
            ->get()->getResult();

        // Anggota per kelompok
        $anggotaMap = [];
        foreach ($kelompokList as $k) {
            $anggotaMap[(int) $k->id_kelompok_peserta_seni] = $this->getAnggotaKelompok((int) $k->id_kelompok_peserta_seni);
        }

        // Perangkat juri di gelanggang
        $perangkatJuri = $db->table('perangkat_pertandingan')
            ->where('id_gelanggang', $this->idGelanggang())
            ->where('posisi', 'juri')
            ->get()->getResult();

        $jumlahJuri = count($perangkatJuri);

        // Penilaian seni per penampilan per juri
        $penilaianRows = $db->table('penilaian_seni')
            ->whereIn('id_penampilan_seni', array_map(fn($k) => (int) $k->id_penampilan_seni, $kelompokList))
            ->get()->getResult();

        $penilaianMap = [];
        foreach ($penilaianRows as $pn) {
            $penilaianMap[(int) $pn->id_penampilan_seni][(int) $pn->id_perangkat_pertandingan] = $pn;
        }

        // Parse penilaian JSON → breakdown per komponen
        $breakdownMap = [];
        foreach ($kelompokList as $k) {
            $pid = (int) $k->id_penampilan_seni;
            $breakdownMap[$pid] = [];
            foreach ($perangkatJuri as $j) {
                $jid = (int) $j->id_perangkat_pertandingan;
                $row = $penilaianMap[$pid][$jid] ?? null;
                if ($row && $row->penilaian) {
                    $parsed = json_decode($row->penilaian, true);
                    $breakdownMap[$pid][$jid] = $this->parseNilaiSeni($parsed, $isBeregu);
                } else {
                    $breakdownMap[$pid][$jid] = ['kebenaran' => 0, 'kemantapan' => 0, 'hukuman' => 0, 'total' => 0];
                }
            }
        }

        return view('pertandingan/sekretaris/print_seni_pool', [
            'title'            => $isBeregu ? 'Print Nilai Beregu' : 'Print Nilai Tunggal',
            'penampilan'       => $penampilan,
            'is_beregu'        => $isBeregu,
            'kelompok_list'    => $kelompokList,
            'anggota_map'      => $anggotaMap,
            'perangkat_juri'   => $perangkatJuri,
            'jumlah_juri'      => $jumlahJuri,
            'breakdown_map'    => $breakdownMap,
        ]);
    }

    private function parseNilaiSeni(array $parsed, bool $isBeregu): array
    {
        $kebenaran  = 0;
        $kemantapan = 0;
        $hukuman    = 0;

        $unsur = $parsed['penilaian']['unsur_nilai'] ?? [];

        // KEBENARAN: use kebenaran.nilai_diperoleh if available, else sum all nilai_diperoleh dalam jurus
        $keb = $unsur['kebenaran'] ?? [];
        if (isset($keb['nilai_diperoleh']) && is_numeric($keb['nilai_diperoleh'])) {
            $kebenaran = round((float) $keb['nilai_diperoleh'], 3);
        } else {
            $total = 0;
            array_walk_recursive($keb, function ($val, $key) use (&$total) {
                if ($key === 'nilai_diperoleh' && is_numeric($val)) $total += (float) $val;
            });
            $kebenaran = round($total, 3);
        }

        // KEMANTAPAN: use nilai_diperoleh from kemantapan block
        $kem = $unsur['kemantapan'] ?? [];
        if (isset($kem['nilai_diperoleh']) && is_numeric($kem['nilai_diperoleh'])) {
            $kemantapan = round((float) $kem['nilai_diperoleh'], 3);
        } else {
            array_walk_recursive($kem, function ($val) use (&$kemantapan) {
                if (is_numeric($val)) $kemantapan += (float) $val;
            });
            $kemantapan = round($kemantapan, 3);
        }

        // HUKUMAN: sum all nilai_hukuman fields
        $hkm = $unsur['hukuman'] ?? [];
        if (isset($hkm['ringkasan']) && isset($hkm['ringkasan']['total_hukuman'])) {
            $hukuman = round((float) $hkm['ringkasan']['total_hukuman'], 3);
        } else {
            array_walk_recursive($hkm, function ($val, $key) use (&$hukuman) {
                if ($key === 'nilai_hukuman' && is_numeric($val)) $hukuman += (float) $val;
            });
            $hukuman = round($hukuman, 3);
        }

        // TOTAL: from ringkasan.nilai_akhir or computed
        $ringkasan = $parsed['penilaian']['ringkasan'] ?? [];
        if (isset($ringkasan['nilai_akhir']) && is_numeric($ringkasan['nilai_akhir'])) {
            $total = round((float) $ringkasan['nilai_akhir'], 3);
        } else {
            $total = round($kebenaran + $kemantapan - $hukuman, 3);
        }

        return compact('kebenaran', 'kemantapan', 'hukuman', 'total');
    }

    private function getFormatListTanding(): array
    {
        $basePath = FCPATH . 'assets/penilaian/format-penilaian/tanding/';
        if (! is_dir($basePath)) return [];

        $list = [];
        $files = glob($basePath . '*.json');
        foreach ($files as $file) {
            $filename = basename($file);
            $list[] = $filename;
        }
        return $list;
    }
}
