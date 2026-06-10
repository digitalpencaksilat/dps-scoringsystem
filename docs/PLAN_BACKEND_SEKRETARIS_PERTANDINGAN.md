# Backend Sekretaris Pertandingan — Implementation Plan

> **Goal:** Membuat semua fungsi backend (Controller + Model + Service + Helper) untuk modul Sekretaris Pertandingan berjalan dengan baik, sehingga seluruh UI/UX yang sudah dibuat bisa terhubung dan berfungsi end-to-end.

**Arsitektur:**
- Controller: `SekretarisPertandingan` (sudah ada, 1497 LOC) — perlu dilengkapi/diperbaiki
- Models: CI4 models untuk tabel legacy `db_sudinpora` (timestamps OFF)
- Services: Scoring logic per sistem penilaian (tanding + seni)
- Helper: `realtime_helper.php` untuk emit socket events ke Node.js server

**Tech Stack:** CodeIgniter 4.6, MariaDB (db_sudinpora), Socket.IO (Node.js port 3000)

---

## Status Saat Ini

### Yang Sudah Ada di CI4:
- ✅ `SekretarisPertandingan.php` controller (1497 LOC, 28+ methods)
- ✅ `PertandinganModel.php` (282 LOC)
- ✅ `PenilaianTandingModel.php` (220 LOC)
- ✅ `PerangkatPertandinganModel.php` (67 LOC)
- ✅ `BroadcastGraphicModel.php` (68 LOC)
- ✅ `PersilatTandingService.php` (scoring tanding PERSILAT)
- ✅ `realtime_helper.php`
- ✅ Routes (32 routes sekretaris)
- ✅ Views (semua sekretaris views)
- ✅ JS + CSS assets

### Yang Belum Ada / Perlu Dibuat:
- ❌ Models untuk tabel seni: `PenampilanSeniModel`, `BattleSeniModel`, `KompetisiSeniModel`, `KelompokPesertaSeniModel`, `PenilaianSeniModel`
- ❌ Models pendukung: `DetailJadwalTandingModel`, `DetailJadwalSeniModel`, `JadwalTandingModel`, `JadwalSeniModel`, `GelanggangModel`, `KelasTandingModel`, `KompetisiTandingModel`
- ❌ Services scoring seni: `PersilatSeniService`, `Ipsi2012SeniService`, `TapakSuciSeniService`, `FPSTISeniService`, `FestivalSeniService`
- ❌ Services scoring tanding lainnya: `Ipsi2012TandingService`, `TapakSuciTandingService`
- ❌ Verifikasi bahwa semua controller methods sudah benar memanggil model/service yang tepat

---

## Phase 1: Models Dasar (Tabel Referensi & Jadwal)

### Task 1.1: GelanggangModel
**File:** `app/Models/GelanggangModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class GelanggangModel extends Model
{
    protected $table = 'gelanggang';
    protected $primaryKey = 'id_gelanggang';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'nomor_gelanggang', 'nama_gelanggang', 'keterangan',
        'tipe_gong', 'beep_alarm', 'tipe_voice_over'
    ];
}
```

**Verifikasi:** `php -l app/Models/GelanggangModel.php`

---

### Task 1.2: JadwalTandingModel
**File:** `app/Models/JadwalTandingModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class JadwalTandingModel extends Model
{
    protected $table = 'jadwal_tanding';
    protected $primaryKey = 'id_jadwal_tanding';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_gelanggang', 'tanggal', 'jam_mulai', 'jam_selesai',
        'keterangan', 'nama_file'
    ];

    /**
     * Get jadwal tanding untuk gelanggang tertentu, hari ini
     */
    public function getByGelanggang(int $idGelanggang): array
    {
        return $this->where('id_gelanggang', $idGelanggang)
                    ->orderBy('tanggal', 'ASC')
                    ->orderBy('jam_mulai', 'ASC')
                    ->findAll();
    }
}
```

---

### Task 1.3: JadwalSeniModel
**File:** `app/Models/JadwalSeniModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class JadwalSeniModel extends Model
{
    protected $table = 'jadwal_seni';
    protected $primaryKey = 'id_jadwal_seni';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_gelanggang', 'tanggal', 'jam_mulai', 'jam_selesai',
        'keterangan', 'nama_file'
    ];

    public function getByGelanggang(int $idGelanggang): array
    {
        return $this->where('id_gelanggang', $idGelanggang)
                    ->orderBy('tanggal', 'ASC')
                    ->orderBy('jam_mulai', 'ASC')
                    ->findAll();
    }
}
```

---

### Task 1.4: DetailJadwalTandingModel
**File:** `app/Models/DetailJadwalTandingModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class DetailJadwalTandingModel extends Model
{
    protected $table = 'detail_jadwal_tanding';
    protected $primaryKey = 'id_detail_jadwal_tanding';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'nomor_partai', 'id_jadwal_tanding', 'id_pertandingan',
        'keterangan', 'status_partai'
    ];

    /**
     * Get daftar partai dengan data pertandingan (join)
     */
    public function getPartaiByJadwal(int $idJadwal): array
    {
        return $this->select('detail_jadwal_tanding.*, pertandingan.*, 
                    am.nama as nama_atlet_merah, ab.nama as nama_atlet_biru,
                    km.nama_kontingen as kontingen_merah, kb.nama_kontingen as kontingen_biru,
                    kelas_tanding.label as label_kelas')
                    ->join('pertandingan', 'pertandingan.id_pertandingan = detail_jadwal_tanding.id_pertandingan', 'left')
                    ->join('kompetisi_tanding', 'kompetisi_tanding.id_kompetisi_tanding = pertandingan.id_kompetisi_tanding', 'left')
                    ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding', 'left')
                    ->join('pendaftar_tanding am', 'am.id_pendaftar_tanding = pertandingan.id_atlet_merah', 'left')
                    ->join('pendaftar_tanding ab', 'ab.id_pendaftar_tanding = pertandingan.id_atlet_biru', 'left')
                    ->join('kontingen km', 'km.id_kontingen = am.id_kontingen', 'left')
                    ->join('kontingen kb', 'kb.id_kontingen = ab.id_kontingen', 'left')
                    ->where('detail_jadwal_tanding.id_jadwal_tanding', $idJadwal)
                    ->orderBy('detail_jadwal_tanding.nomor_partai', 'ASC')
                    ->findAll();
    }

    /**
     * Get partai tetangga (prev/next) untuk pindah partai
     */
    public function getPartaiTetangga(int $idJadwal, int $nomorPartai): array
    {
        $prev = $this->where('id_jadwal_tanding', $idJadwal)
                     ->where('nomor_partai <', $nomorPartai)
                     ->orderBy('nomor_partai', 'DESC')
                     ->first();

        $next = $this->where('id_jadwal_tanding', $idJadwal)
                     ->where('nomor_partai >', $nomorPartai)
                     ->orderBy('nomor_partai', 'ASC')
                     ->first();

        return ['prev' => $prev, 'next' => $next];
    }
}
```

---

### Task 1.5: DetailJadwalSeniModel
**File:** `app/Models/DetailJadwalSeniModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class DetailJadwalSeniModel extends Model
{
    protected $table = 'detail_jadwal_seni';
    protected $primaryKey = 'id_detail_jadwal_seni';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_jadwal_seni', 'id_penampilan_seni', 'id_battle_seni',
        'nomor_partai', 'nomor_urut', 'keterangan', 'status_partai'
    ];

    /**
     * Get daftar partai seni dengan data penampilan (pool mode)
     */
    public function getPartaiPoolByJadwal(int $idJadwal): array
    {
        return $this->select('detail_jadwal_seni.*, penampilan_seni.*,
                    kelompok_peserta_seni.id_kontingen, kelompok_peserta_seni.nomor_undi,
                    kontingen.nama_kontingen,
                    kompetisi_seni.nomor_pool, kompetisi_seni.id_kompetisi_seni')
                    ->join('penampilan_seni', 'penampilan_seni.id_penampilan_seni = detail_jadwal_seni.id_penampilan_seni', 'left')
                    ->join('kelompok_peserta_seni', 'kelompok_peserta_seni.id_kelompok_peserta_seni = penampilan_seni.id_kelompok_peserta_seni', 'left')
                    ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen', 'left')
                    ->join('kompetisi_seni', 'kompetisi_seni.id_kompetisi_seni = kelompok_peserta_seni.id_kompetisi_seni', 'left')
                    ->where('detail_jadwal_seni.id_jadwal_seni', $idJadwal)
                    ->where('detail_jadwal_seni.id_penampilan_seni IS NOT NULL')
                    ->orderBy('detail_jadwal_seni.nomor_partai', 'ASC')
                    ->findAll();
    }

    /**
     * Get daftar partai seni battle mode
     */
    public function getPartaiBattleByJadwal(int $idJadwal): array
    {
        return $this->select('detail_jadwal_seni.*, battle_seni.*,
                    psm.id_kelompok_peserta_seni as id_peserta_merah,
                    psb.id_kelompok_peserta_seni as id_peserta_biru,
                    km.nama_kontingen as kontingen_merah,
                    kb.nama_kontingen as kontingen_biru')
                    ->join('battle_seni', 'battle_seni.id_battle_seni = detail_jadwal_seni.id_battle_seni', 'left')
                    ->join('penampilan_seni psm_data', 'psm_data.id_penampilan_seni = battle_seni.id_penampilan_seni_merah', 'left')
                    ->join('kelompok_peserta_seni psm', 'psm.id_kelompok_peserta_seni = psm_data.id_kelompok_peserta_seni', 'left')
                    ->join('kontingen km', 'km.id_kontingen = psm.id_kontingen', 'left')
                    ->join('penampilan_seni psb_data', 'psb_data.id_penampilan_seni = battle_seni.id_penampilan_seni_biru', 'left')
                    ->join('kelompok_peserta_seni psb', 'psb.id_kelompok_peserta_seni = psb_data.id_kelompok_peserta_seni', 'left')
                    ->join('kontingen kb', 'kb.id_kontingen = psb.id_kontingen', 'left')
                    ->where('detail_jadwal_seni.id_jadwal_seni', $idJadwal)
                    ->where('detail_jadwal_seni.id_battle_seni IS NOT NULL')
                    ->orderBy('detail_jadwal_seni.nomor_partai', 'ASC')
                    ->findAll();
    }
}
```

---

### Task 1.6: KelasTandingModel
**File:** `app/Models/KelasTandingModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class KelasTandingModel extends Model
{
    protected $table = 'kelas_tanding';
    protected $primaryKey = 'id_kelas_tanding';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_kategori_lomba', 'berat_minimal', 'berat_maksimal',
        'jumlah_ronde', 'waktu_per_ronde', 'waktu_istirahat',
        'juara_tiga_bersama', 'label', 'format_penilaian',
        'biaya_pendaftaran_dn', 'biaya_pendaftaran_ln', 'keterangan'
    ];
}
```

---

### Task 1.7: KompetisiTandingModel
**File:** `app/Models/KompetisiTandingModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class KompetisiTandingModel extends Model
{
    protected $table = 'kompetisi_tanding';
    protected $primaryKey = 'id_kompetisi_tanding';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_kelas_tanding', 'max_peserta', 'nomor_pool',
        'bagan_pertandingan', 'perhitungan_medali', 'keterangan'
    ];

    /**
     * Get kompetisi dengan data kelas
     */
    public function getWithKelas(int $id): ?array
    {
        return $this->select('kompetisi_tanding.*, kelas_tanding.*')
                    ->join('kelas_tanding', 'kelas_tanding.id_kelas_tanding = kompetisi_tanding.id_kelas_tanding')
                    ->where('kompetisi_tanding.id_kompetisi_tanding', $id)
                    ->first();
    }
}
```

---

## Phase 2: Models Seni

### Task 2.1: KompetisiSeniModel
**File:** `app/Models/KompetisiSeniModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class KompetisiSeniModel extends Model
{
    protected $table = 'kompetisi_seni';
    protected $primaryKey = 'id_kompetisi_seni';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_sub_kategori_seni', 'nomor_pool', 'max_peserta',
        'bagan_battle_seni', 'perhitungan_medali', 'keterangan'
    ];

    /**
     * Get kompetisi seni dengan sub-kategori info
     */
    public function getWithSubKategori(int $id): ?array
    {
        return $this->select('kompetisi_seni.*, sub_kategori_seni.*, kategori_seni.nama_kategori_seni')
                    ->join('sub_kategori_seni', 'sub_kategori_seni.id_sub_kategori_seni = kompetisi_seni.id_sub_kategori_seni')
                    ->join('kategori_seni', 'kategori_seni.id_kategori_seni = sub_kategori_seni.id_kategori_seni', 'left')
                    ->where('kompetisi_seni.id_kompetisi_seni', $id)
                    ->first();
    }
}
```

---

### Task 2.2: KelompokPesertaSeniModel
**File:** `app/Models/KelompokPesertaSeniModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class KelompokPesertaSeniModel extends Model
{
    protected $table = 'kelompok_peserta_seni';
    protected $primaryKey = 'id_kelompok_peserta_seni';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_kompetisi_seni', 'id_kontingen', 'id_pembayaran',
        'status', 'keterangan', 'nomor_undi'
    ];

    /**
     * Get peserta by kompetisi dengan kontingen
     */
    public function getByKompetisi(int $idKompetisi): array
    {
        return $this->select('kelompok_peserta_seni.*, kontingen.nama_kontingen')
                    ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen')
                    ->where('kelompok_peserta_seni.id_kompetisi_seni', $idKompetisi)
                    ->orderBy('kelompok_peserta_seni.nomor_undi', 'ASC')
                    ->findAll();
    }
}
```

---

### Task 2.3: PenampilanSeniModel
**File:** `app/Models/PenampilanSeniModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class PenampilanSeniModel extends Model
{
    protected $table = 'penampilan_seni';
    protected $primaryKey = 'id_penampilan_seni';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_kelompok_peserta_seni', 'babak', 'waktu_tampil',
        'nilai_akhir', 'catatan_nilai_sama', 'akses_penilaian',
        'status_penampilan', 'diskualifikasi'
    ];

    /**
     * Get penampilan yang sedang berlangsung di gelanggang
     */
    public function getPenampilanBerlangsung(int $idGelanggang): ?array
    {
        return $this->select('penampilan_seni.*, kelompok_peserta_seni.*, 
                    kontingen.nama_kontingen, kompetisi_seni.nomor_pool,
                    detail_jadwal_seni.nomor_partai, detail_jadwal_seni.id_jadwal_seni')
                    ->join('kelompok_peserta_seni', 'kelompok_peserta_seni.id_kelompok_peserta_seni = penampilan_seni.id_kelompok_peserta_seni')
                    ->join('kontingen', 'kontingen.id_kontingen = kelompok_peserta_seni.id_kontingen', 'left')
                    ->join('kompetisi_seni', 'kompetisi_seni.id_kompetisi_seni = kelompok_peserta_seni.id_kompetisi_seni')
                    ->join('detail_jadwal_seni', 'detail_jadwal_seni.id_penampilan_seni = penampilan_seni.id_penampilan_seni')
                    ->join('jadwal_seni', 'jadwal_seni.id_jadwal_seni = detail_jadwal_seni.id_jadwal_seni')
                    ->where('jadwal_seni.id_gelanggang', $idGelanggang)
                    ->whereIn('penampilan_seni.status_penampilan', ['standby', 'sedang_tampil', 'berhenti'])
                    ->first();
    }

    /**
     * Set status penampilan
     */
    public function setStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status_penampilan' => $status]);
    }

    /**
     * Selesaikan penampilan — simpan waktu dan nilai
     */
    public function selesaikan(int $id, int $waktuTampil, string $nilaiAkhir = '0'): bool
    {
        return $this->update($id, [
            'status_penampilan' => 'sudah_tampil',
            'akses_penilaian'  => 'ditutup',
            'waktu_tampil'     => $waktuTampil,
            'nilai_akhir'      => $nilaiAkhir,
        ]);
    }

    /**
     * Diskualifikasi
     */
    public function diskualifikasi(int $id): bool
    {
        return $this->update($id, [
            'diskualifikasi'   => 1,
            'status_penampilan'=> 'sudah_tampil',
            'akses_penilaian'  => 'ditutup',
            'nilai_akhir'      => '0',
        ]);
    }

    /**
     * Batalkan diskualifikasi
     */
    public function batalkanDiskualifikasi(int $id): bool
    {
        return $this->update($id, [
            'diskualifikasi' => 0,
        ]);
    }
}
```

---

### Task 2.4: BattleSeniModel
**File:** `app/Models/BattleSeniModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class BattleSeniModel extends Model
{
    protected $table = 'battle_seni';
    protected $primaryKey = 'id_battle_seni';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_kompetisi_seni', 'nomor_battle', 'babak',
        'nomor_battle_selanjutnya', 'id_penampilan_seni_biru',
        'id_penampilan_seni_merah', 'id_penampilan_seni_pemenang',
        'jenis_kemenangan', 'keterangan'
    ];

    /**
     * Get battle yang sedang berlangsung di gelanggang
     */
    public function getBattleBerlangsung(int $idGelanggang): ?array
    {
        return $this->select('battle_seni.*, 
                    psm.id_penampilan_seni as id_penampilan_merah,
                    psm.status_penampilan as status_merah,
                    psb.id_penampilan_seni as id_penampilan_biru,
                    psb.status_penampilan as status_biru,
                    kpm.id_kontingen as kontingen_id_merah, km.nama_kontingen as kontingen_merah,
                    kpb.id_kontingen as kontingen_id_biru, kb.nama_kontingen as kontingen_biru,
                    detail_jadwal_seni.nomor_partai, detail_jadwal_seni.id_jadwal_seni')
                    ->join('penampilan_seni psm', 'psm.id_penampilan_seni = battle_seni.id_penampilan_seni_merah', 'left')
                    ->join('penampilan_seni psb', 'psb.id_penampilan_seni = battle_seni.id_penampilan_seni_biru', 'left')
                    ->join('kelompok_peserta_seni kpm', 'kpm.id_kelompok_peserta_seni = psm.id_kelompok_peserta_seni', 'left')
                    ->join('kelompok_peserta_seni kpb', 'kpb.id_kelompok_peserta_seni = psb.id_kelompok_peserta_seni', 'left')
                    ->join('kontingen km', 'km.id_kontingen = kpm.id_kontingen', 'left')
                    ->join('kontingen kb', 'kb.id_kontingen = kpb.id_kontingen', 'left')
                    ->join('detail_jadwal_seni', 'detail_jadwal_seni.id_battle_seni = battle_seni.id_battle_seni')
                    ->join('jadwal_seni', 'jadwal_seni.id_jadwal_seni = detail_jadwal_seni.id_jadwal_seni')
                    ->where('jadwal_seni.id_gelanggang', $idGelanggang)
                    ->groupStart()
                        ->where('psm.status_penampilan', 'sedang_tampil')
                        ->orWhere('psb.status_penampilan', 'sedang_tampil')
                        ->orWhere('psm.status_penampilan', 'standby')
                        ->orWhere('psb.status_penampilan', 'standby')
                    ->groupEnd()
                    ->where('battle_seni.jenis_kemenangan', 'TBD')
                    ->first();
    }

    /**
     * Set pemenang battle dan advance ke bracket selanjutnya
     */
    public function setPemenang(int $idBattle, int $idPenampilanPemenang): bool
    {
        $battle = $this->find($idBattle);
        if (!$battle) return false;

        // Update pemenang di battle ini
        $this->update($idBattle, [
            'id_penampilan_seni_pemenang' => $idPenampilanPemenang,
            'jenis_kemenangan' => 'poin',
        ]);

        // Advance ke battle selanjutnya jika ada
        if (!empty($battle['nomor_battle_selanjutnya'])) {
            $nextBattle = $this->where('id_kompetisi_seni', $battle['id_kompetisi_seni'])
                               ->where('nomor_battle', $battle['nomor_battle_selanjutnya'])
                               ->first();

            if ($nextBattle) {
                // Tentukan posisi (merah/biru) berdasarkan nomor battle ganjil/genap
                $field = ($battle['nomor_battle'] % 2 !== 0)
                    ? 'id_penampilan_seni_merah'
                    : 'id_penampilan_seni_biru';

                $this->update($nextBattle['id_battle_seni'], [
                    $field => $idPenampilanPemenang
                ]);
            }
        }

        return true;
    }
}
```

---

### Task 2.5: PenilaianSeniModel
**File:** `app/Models/PenilaianSeniModel.php`

```php
<?php
namespace App\Models;
use CodeIgniter\Model;

class PenilaianSeniModel extends Model
{
    protected $table = 'penilaian_seni';
    protected $primaryKey = 'id_penilaian_seni';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'id_penampilan_seni', 'id_perangkat_pertandingan',
        'penilaian', 'nilai_akhir'
    ];

    /**
     * Get semua penilaian untuk penampilan tertentu
     */
    public function getByPenampilan(int $idPenampilan): array
    {
        return $this->where('id_penampilan_seni', $idPenampilan)->findAll();
    }

    /**
     * Get penilaian grouped by perangkat (juri)
     */
    public function getByPenampilanGrouped(int $idPenampilan): array
    {
        $rows = $this->where('id_penampilan_seni', $idPenampilan)->findAll();
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['id_perangkat_pertandingan']] = $row;
        }
        return $grouped;
    }
}
```

---

## Phase 3: Services Scoring Seni

### Task 3.1: SeniScoringInterface
**File:** `app/Services/Scoring/SeniScoringInterface.php`

```php
<?php
namespace App\Services\Scoring;

interface SeniScoringInterface
{
    /**
     * Hitung nilai akhir dari semua penilaian juri untuk satu penampilan
     * @param array $penilaianJuri — array of penilaian rows (per juri)
     * @return string nilai akhir (decimal string)
     */
    public function hitungNilaiAkhir(array $penilaianJuri): string;

    /**
     * Urutkan dan tentukan juara dalam satu kompetisi/pool
     * @param array $penampilanList — array of penampilan with nilai_akhir
     * @return array sorted by rank, with medali assigned
     */
    public function urutkanJuara(array $penampilanList): array;
}
```

---

### Task 3.2: PersilatSeniService
**File:** `app/Services/Scoring/Persilat/PersilatSeniService.php`

**Logic (dari legacy):**
- Ambil semua nilai juri per penampilan
- Hitung median dari semua juri (bukan average)
- Kurangi dengan total hukuman (median hukuman dari juri KP)
- Nilai akhir = median_teknis - hukuman
- Ranking: sort descending by nilai_akhir, handle tie via `catatan_nilai_sama`

**Referensi legacy:** `/dps/application/models/sistem_penilaian/seni/PERSILAT_model.php` (501 LOC)

---

### Task 3.3: Ipsi2012SeniService
**File:** `app/Services/Scoring/Ipsi2012/Ipsi2012SeniService.php`

**Logic (dari legacy):**
- 5 juri menilai, buang nilai tertinggi dan terendah
- Jumlahkan 3 nilai tengah
- Ranking: sort descending

**Referensi legacy:** `/dps/application/models/sistem_penilaian/seni/IPSI_2012_model.php` (215 LOC)

---

### Task 3.4: TapakSuciSeniService
**File:** `app/Services/Scoring/TapakSuci/TapakSuciSeniService.php`

**Logic:** Identical to IPSI 2012 (middle 3 of 5 juries, sum)

---

### Task 3.5: FPSTISeniService
**File:** `app/Services/Scoring/FPSTI/FPSTISeniService.php`

**Logic:** Similar to IPSI 2012, uses all jury scores, sum of middle 3 of 5

---

### Task 3.6: FestivalSeniService
**File:** `app/Services/Scoring/Festival/FestivalSeniService.php`

**Logic:** Identical to Tapak Suci

---

## Phase 4: Services Scoring Tanding (Tambahan)

### Task 4.1: TandingScoringInterface
**File:** `app/Services/Scoring/TandingScoringInterface.php`

```php
<?php
namespace App\Services\Scoring;

interface TandingScoringInterface
{
    /**
     * Hitung skor akhir dari penilaian semua juri
     * @param array $penilaianJuri — array of penilaian_tanding rows
     * @param int $idPertandingan
     * @return array ['skor_merah' => int, 'skor_biru' => int, 'ringkasan_nilai' => string(JSON)]
     */
    public function hitungSkor(array $penilaianJuri, int $idPertandingan): array;
}
```

---

### Task 4.2: Ipsi2012TandingService
**File:** `app/Services/Scoring/Ipsi2012/Ipsi2012TandingService.php`

**Logic (dari legacy):**
- Setiap juri memilih pemenang per ronde (merah/biru)
- Hitung suara mayoritas → pemenang ronde
- Pemenang pertandingan = yang menang mayoritas ronde

**Referensi:** `/dps/application/models/sistem_penilaian/tanding/IPSI_2012_model.php` (74 LOC)

---

### Task 4.3: TapakSuciTandingService
**File:** `app/Services/Scoring/TapakSuci/TapakSuciTandingService.php`

**Logic (dari legacy):**
- Per ronde: jumlahkan skor dari setiap juri (field `ringkasan.nilai_akhir`)
- Bandingkan total per ronde: merah vs biru
- Pemenang = yang menang lebih banyak ronde

**Referensi:** `/dps/application/models/sistem_penilaian/tanding/Tapak_Suci_model.php` (99 LOC)

---

## Phase 5: Controller Fixes & Completion

### Task 5.1: Audit & Fix Controller Dependencies

Periksa `SekretarisPertandingan.php` — pastikan semua method sudah memanggil model yang benar:

| Controller Method | Model/Service yang Dibutuhkan |
|---|---|
| `home()` | JadwalTandingModel, JadwalSeniModel, DetailJadwalTandingModel, DetailJadwalSeniModel |
| `jadwalTanding($id)` | DetailJadwalTandingModel (getPartaiByJadwal) |
| `jadwalSeni($id)` | DetailJadwalSeniModel (getPartaiPoolByJadwal, getPartaiBattleByJadwal) |
| `timerTanding()` | PertandinganModel (getPertandinganBerlangsung) |
| `mulaiPertandingan($id)` | PertandinganModel (setStatus → standby) |
| `toggleTimerTanding($id)` | PertandinganModel (setStatusDanWaktu) + realtime emit |
| `pindahRondeTanding($id)` | PertandinganModel (setRonde) + realtime emit |
| `selesaikanPertandingan($id)` | PertandinganModel, PenilaianTandingModel, *TandingService + bracket advance |
| `ubahWaktuTanding($id)` | PertandinganModel/KelasTandingModel (update waktu per scope) |
| `gantiFormatPenilaianTanding($id)` | KelasTandingModel (update format_penilaian per scope) |
| `timerSeni()` | PenampilanSeniModel (getPenampilanBerlangsung) / BattleSeniModel |
| `mulaiPenampilan($id)` | PenampilanSeniModel (setStatus → standby) |
| `toggleTimerSeni($id)` | PenampilanSeniModel (setStatus) + realtime emit |
| `timerResetSeni($id)` | PenampilanSeniModel + realtime emit |
| `selesaikanPenampilanSeni($id)` | PenampilanSeniModel, PenilaianSeniModel, *SeniService (hitungNilaiAkhir) |
| `pilihPemenangBattleSeni($id)` | BattleSeniModel (setPemenang) + medal logic |
| `diskualifikasiPenampilanSeni($id)` | PenampilanSeniModel (diskualifikasi) |
| `batalkanDiskualifikasiSeni($id)` | PenampilanSeniModel (batalkanDiskualifikasi) |
| `inputManualJuaraSeni()` | KelompokPesertaSeniModel + medal update |
| `getDataPenentuanJuara($id)` | PenampilanSeniModel, KelompokPesertaSeniModel, *SeniService (urutkanJuara) |
| `refreshStatusPertandingan($id)` | PertandinganModel (get status) |
| `refreshStatusSeni()` | PenampilanSeniModel (get status) |
| `pindahPartaiTanding($id)` | DetailJadwalTandingModel + PertandinganModel |
| `pindahPartaiSeni($id)` | DetailJadwalSeniModel + PenampilanSeniModel |
| `gantiFormatPenilaianSeni($id)` | KompetisiSeniModel / SubKategoriSeniModel |
| `printSeniPool($id)` | PenampilanSeniModel, PenilaianSeniModel, *SeniService |
| `formEditAtletTanding($id)` | PertandinganModel + PendaftarTandingModel |
| `editAtletTanding($id)` | PertandinganModel (update id_atlet) |
| `infoPenimbangan($id)` | PertandinganModel (get berat/hasil_timbang) |

---

### Task 5.2: Fix Model Loading di Controller

Saat ini controller kemungkinan masih hardcode `new Model()` untuk model yang belum dibuat. Ganti semua ke dependency injection atau `model()` helper CI4:

```php
// Di method atau constructor
$this->penampilanSeniModel = model(PenampilanSeniModel::class);
$this->battleSeniModel = model(BattleSeniModel::class);
// dst...
```

---

### Task 5.3: Scoring Service Factory

**File:** `app/Services/Scoring/ScoringFactory.php`

```php
<?php
namespace App\Services\Scoring;

class ScoringFactory
{
    /**
     * Get scoring service instance berdasarkan format_penilaian dan jenis (tanding/seni)
     */
    public static function make(string $formatPenilaian, string $jenis): object
    {
        $map = [
            'tanding' => [
                'persilat'   => \App\Services\Scoring\Persilat\PersilatTandingService::class,
                'ipsi_2012'  => \App\Services\Scoring\Ipsi2012\Ipsi2012TandingService::class,
                'tapak_suci' => \App\Services\Scoring\TapakSuci\TapakSuciTandingService::class,
            ],
            'seni' => [
                'persilat'   => \App\Services\Scoring\Persilat\PersilatSeniService::class,
                'ipsi_2012'  => \App\Services\Scoring\Ipsi2012\Ipsi2012SeniService::class,
                'tapak_suci' => \App\Services\Scoring\TapakSuci\TapakSuciSeniService::class,
                'fpsti'      => \App\Services\Scoring\FPSTI\FPSTISeniService::class,
                'festival'   => \App\Services\Scoring\Festival\FestivalSeniService::class,
            ],
        ];

        $class = $map[$jenis][$formatPenilaian] ?? null;
        if (!$class) {
            throw new \InvalidArgumentException("Scoring service not found: {$jenis}/{$formatPenilaian}");
        }

        return new $class();
    }
}
```

---

### Task 5.4: Realtime Helper Verification

Pastikan `app/Helpers/realtime_helper.php` sudah bisa emit events ke Node.js server. Perlu fungsi:

```php
function emit_realtime(string $event, array $data, ?string $room = null): bool
```

Yang memanggil HTTP POST ke `http://localhost:3000/emit` (atau endpoint internal Node server).

---

## Phase 6: Medal & Bracket Logic

### Task 6.1: Medal Assignment Service
**File:** `app/Services/MedalService.php`

Logic untuk assign medali setelah pool/battle selesai:
- Pool: ranking berdasarkan nilai_akhir → emas (rank 1), perak (rank 2), perunggu (rank 3 & 4 jika `juara_tiga_bersama`)
- Battle: pemenang final → emas, kalah final → perak, kalah semi → perunggu

Referensi: `Sekretaris_pertandingan_model.php` method `input_medali_seni()` + `input_manual_juara_seni()`

---

### Task 6.2: Bracket Advancement (Tanding)

Logic untuk advance pemenang ke pertandingan selanjutnya:
- Pertandingan punya `nomor_pertandingan_selanjutnya`
- Setelah selesai, cari pertandingan dengan `nomor_pertandingan = nomor_pertandingan_selanjutnya`
- Set `id_atlet_merah` atau `id_atlet_biru` (tergantung posisi genap/ganjil)

Referensi: `Ketua_pertandingan_model.php` lines ~300-400

---

## Phase 7: Testing & Validation

### Task 7.1: Unit Test — Scoring Services

```
tests/Services/Scoring/PersilatSeniServiceTest.php
tests/Services/Scoring/Ipsi2012SeniServiceTest.php
tests/Services/Scoring/Ipsi2012TandingServiceTest.php
tests/Services/Scoring/TapakSuciTandingServiceTest.php
```

Test cases:
- Hitung nilai akhir dengan data juri valid
- Handle edge case: kurang juri, nilai kosong
- Sorting/ranking benar
- Tie-breaking logic

---

### Task 7.2: Integration Test — Controller Endpoints

Manual test via browser/curl:
- Login sebagai sekretaris → GET /sekretaris-pertandingan ✓
- Navigasi jadwal → GET /sekretaris-pertandingan/jadwal-tanding/{id} ✓
- Mulai pertandingan → GET /sekretaris-pertandingan/mulai-pertandingan/{id} ✓
- Toggle timer → POST /sekretaris-pertandingan/toggle-timer-tanding/{id} ✓
- Selesaikan pertandingan → POST /sekretaris-pertandingan/selesaikan-pertandingan/{id} ✓
- Timer seni → semua endpoint seni ✓

---

### Task 7.3: PHP Syntax Check All New Files

```bash
find app/Models app/Services -name "*.php" -exec php -l {} \;
```

---

## Execution Order & Dependencies

```
Phase 1 (Models Dasar)          → Tidak ada dependency
Phase 2 (Models Seni)           → Tidak ada dependency  
Phase 3 (Services Scoring Seni) → Depends on Phase 2
Phase 4 (Services Scoring Tanding) → Depends on Phase 1
Phase 5 (Controller Fixes)      → Depends on Phase 1-4
Phase 6 (Medal & Bracket)       → Depends on Phase 2, 5
Phase 7 (Testing)               → Depends on Phase 1-6
```

**Estimasi total: 25-30 tasks, ~2-3 jam implementasi.**

---

## Risiko & Catatan

1. **Schema mismatch** — Beberapa tabel mungkin punya kolom tambahan yang belum ter-describe (cek `pendaftar_tanding`, `kontingen`, `sub_kategori_seni`, `kategori_seni`). Perlu DESCRIBE tambahan saat implementasi.

2. **Scoring PERSILAT Seni complex** — Model legacy 501 LOC, butuh perhatian khusus di median calculation dan penalty validation.

3. **Bracket logic fragile** — Advancing pemenang ke battle/pertandingan selanjutnya harus idempotent (jangan double-advance jika endpoint dipanggil ulang).

4. **Realtime emit** — Node server harus expose HTTP endpoint internal untuk PHP bisa emit. Verify dulu apakah sudah ada di `realtime-server/server.js`.

5. **Format penilaian per match** — `kelas_tanding.format_penilaian` dan scope-nya (per pertandingan, per kelas, per kategori, per gelanggang) harus di-handle dengan benar di `ubahWaktu` dan `gantiFormat`.

6. **`$useTimestamps = false`** — WAJIB di semua model baru (tabel legacy tanpa created_at/updated_at).
