# Parity Juri — Sistem Penilaian PERSILAT (Tanding & Seni)

> Dokumen ini adalah hasil analisis mendalam terhadap modul Juri pada legacy CI3
> dan mapping ke CI4 `dps-scoringsystem`. Scope: **hanya PERSILAT**.

---

## 1. Status Saat Ini di CI4

### ✅ Sudah Ada (Tanding PERSILAT)
| Komponen | File | Status |
|----------|------|--------|
| Controller | `app/Controllers/Pertandingan/Juri.php` (189 LOC) | Ada, basic — hanya tanding |
| View Light | `app/Views/pertandingan/juri/tanding/persilat/light.php` (169 LOC) | Ada, functional |
| View Dark | `app/Views/pertandingan/juri/tanding/persilat/dark.php` (7 LOC) | Ada, wrapper |
| CSS | `public/assets/css/penilaian/juri-tanding.css` (139 LOC) | Ada |
| Service | `app/Services/Scoring/Persilat/PersilatTandingService.php` (611 LOC) | Ada, lengkap |
| Model | `app/Models/PenilaianTandingModel.php` (220 LOC) | Ada |
| Routes | 6 routes under `/juri` group | Ada |

### ❌ Belum Ada (Seni PERSILAT)
| Komponen | File Target | Keterangan |
|----------|-------------|------------|
| Controller method `seni()` | `Juri.php` | Method untuk halaman penilaian seni |
| Controller method `editPenilaianSeni()` | `Juri.php` | AJAX save nilai seni |
| Controller method `refreshStatusSeni()` | `Juri.php` | Polling status penampilan |
| Controller method `toggleReadySeni()` | `Juri.php` | Toggle juri ready status |
| View Seni Sederhana | `pertandingan/juri/seni/persilat/sederhana.php` | UI input seni mode sederhana |
| View Seni Terperinci | `pertandingan/juri/seni/persilat/terperinci.php` | UI input seni mode terperinci |
| CSS Seni | `public/assets/css/penilaian/juri-seni.css` | Styling seni-specific |
| JS Seni | `public/assets/js/penilaian/juri_seni_persilat.js` | Logic client-side seni |
| Service Seni | `app/Services/Scoring/Persilat/PersilatSeniService.php` | Hitung nilai akhir seni |
| Model Seni | `app/Models/PenilaianSeniModel.php` | Sudah ada — perlu verifikasi |
| Routes Seni | `/juri/seni`, `/juri/edit-penilaian-seni/:id`, dll | Belum ada |

### ⚠️ Perlu Perbaikan (Tanding)
- View `light.php` masih inline JS (belum modular file terpisah seperti sekretaris)
- Belum ada verifikasi modal (jatuhan/pelanggaran) — di legacy ada `components/verifikasi_jatuhan.php` dan `components/verifikasi_pelanggaran.php`
- Belum ada monitor nilai (view ringkasan real-time yang dipakai KP/Layar)

---

## 2. Legacy Architecture Deep-Dive

### 2.1 Controller CI3: `Juri.php` (421 LOC)

**Access Control:**
```php
function _remap($method, $params = []) {
    if ($this->session->userdata('level') == 'perangkat_pertandingan' 
        && $this->session->userdata('posisi') == 'juri') {
        $this->$method(...$params);
    } else {
        redirect('pertandingan');
    }
}
```

**Methods:**

| Method | HTTP | Fungsi |
|--------|------|--------|
| `index()` | GET | Home — link ke tanding/seni |
| `tanding($theme)` | GET | Halaman scoring tanding (light/dark/controller) |
| `seni($theme)` | GET | Halaman scoring seni (persilat/tapak_suci/ipsi/fpsti/festival) |
| `edit_penilaian_tanding($id)` | POST (AJAX) | Incremental entry untuk tanding |
| `edit_penilaian_seni($id)` | POST (AJAX) | Save seluruh JSON seni |
| `refresh_status_pertandingan($id)` | POST (AJAX) | Poll status tanding |
| `refresh_status_seni($id)` | POST (AJAX) | Poll status seni |
| `toggle_ready_seni($id)` | POST (AJAX) | Toggle ready |
| `submit_jawaban_verifikasi_pertandingan($id)` | POST (AJAX) | Submit jawaban verifikasi jatuhan/pelanggaran |

### 2.2 Tanding Flow (PERSILAT)

```
[Juri Device] → tap Pukulan/Tendangan → 
    AJAX POST /juri/edit_penilaian_tanding/{id} 
    → body: {sudut, entry: {nilai, timestamp}}
    → Server: lock row → append entry → hitung_skor_atlet() → save → respond
    ← Response: {merah: {...}, biru: {...}}
    → Client updates UI skor
```

**Scoring Values:**
| Jenis | Nilai | Siapa Input |
|-------|-------|-------------|
| Pukulan | +1 | Juri |
| Tendangan | +2 | Juri |
| Jatuhan | +3 | KP (via verifikasi) |
| Teguran 1 | -1 | KP |
| Teguran 2 | -2 | KP |
| Peringatan 1 | -5 | KP |
| Peringatan 2 | -10 | KP |
| Binaan | catatan (bukan nilai) | KP |

**Verification Algorithm (kunci PERSILAT):**
1. Setiap entry juri punya `timestamp` (unix time server-side)
2. Entry dari 2+ juri yang sama `nilai` DAN `abs(timestamp_A - timestamp_B) <= 2 detik` → `status: 'verified'`
3. Hanya entry `verified` yang dihitung ke skor akhir
4. Entry yang tidak terverifikasi tetap tersimpan tapi `status: 'input'`
5. Entry diberi `warna` (color-code) berdasarkan grup verifikasi
6. Soft-delete: `is_deleted: true, deleted_at: timestamp`

**JSON Structure `penilaian_merah`/`penilaian_biru`:**
```json
{
  "ronde_pertandingan": {
    "1": {
      "rincian": [
        {"nilai": 1, "timestamp": 1718000000, "status": "verified", "warna": "#FF5733", "id_nilai": 1, "is_deleted": false},
        {"nilai": 2, "timestamp": 1718000003, "status": "input", "warna": null, "id_nilai": null, "is_deleted": false}
      ],
      "ringkasan": {"total_nilai_terinput": 3, "total_nilai": 1, "total_hukuman": 0, "nilai_akhir": 1},
      "kategori_nilai": {"pukulan": 1, "tendangan": 0, "jatuhan": 0, "hukuman": 0},
      "catatan": {"binaan": 0}
    },
    "2": { ... },
    "3": { ... }
  },
  "ringkasan": {"total_nilai_terinput": 0, "total_nilai": 0, "total_hukuman": 0, "nilai_akhir": 0},
  "kategori_nilai": {"pukulan": 0, "tendangan": 0, "jatuhan": 0, "hukuman": 0}
}
```

### 2.3 Seni Flow (PERSILAT)

```
[Juri Device] → input nilai per unsur → 
    AJAX POST /juri/edit_penilaian_seni/{id_penampilan_seni}
    → body: {data_nilai: JSON, nilai_akhir_per_juri: float}
    → Server: find row by juri+penampilan → update penilaian + nilai_akhir_per_juri
    ← Response: {status: true, new_nilai: JSON}
```

**Unsur Penilaian Seni PERSILAT:**
1. **Kebenaran Jurus** — pointer system per gerakan (rangkaian gerak)
   - Setiap jurus dinilai 0-10 (default 10, dikurangi per kesalahan)
   - Dihitung per jurus dalam rangkaian
2. **Kemantapan** — overall score
3. **Kekompakan** (untuk regu/ganda) 
4. **Hukuman** — diinput oleh KP, di-sync ke semua juri (server-authoritative)

**JSON Structure `penilaian` (seni):**
```json
{
  "penilaian": {
    "unsur_nilai": {
      "kebenaran": {
        "jurus": {
          "jurus_1": {"1": {"nilai": 10}, "2": {"nilai": 8}, ...},
          "jurus_2": {"1": {"nilai": 10}, ...}
        },
        "nilai_diperoleh": 45.5,
        "total_gerakan": 50
      },
      "kemantapan": {
        "nilai_diperoleh": 8.5
      },
      "kekompakan": {
        "nilai_diperoleh": 7.0
      }
    },
    "hukuman": {
      "waktu_kurang": {"detail_hukuman": {"nilai_hukuman": 0.5}},
      "waktu_lebih": {"detail_hukuman": {"nilai_hukuman": 0}},
      "keluar_arena": {"detail_hukuman": {"nilai_hukuman": 0}},
      "tidak_sesuai_aturan": {"detail_hukuman": {"nilai_hukuman": 0}}
    },
    "ringkasan": {
      "total_unsur_nilai": 61.0,
      "total_hukuman": 0.5,
      "total_nilai": 60.5
    }
  }
}
```

**Hitung Nilai Akhir Seni (per penampilan):**
1. Kumpulkan `nilai_akhir_per_juri` dari semua juri
2. Sort ascending
3. Hitung **median** (bukan rata-rata!)
4. Hitung **hukuman** (dari KP, harus identik di semua juri — validasi konsistensi)
5. `nilai_akhir = median - hukuman`
6. Simpan juga: standar_deviasi, median_kebenaran di `catatan_nilai_sama`
7. Tandai juri yang `terpilih` (yang nilainya = median)

**Ready System:**
- Juri bisa toggle `status_ready` (0/1) di `penilaian_seni`
- Sekretaris melihat berapa juri yang sudah ready
- Tidak mempengaruhi perhitungan — hanya indikator UI

### 2.4 Verifikasi Modal (Tanding)

Legacy punya 2 modal popup yang muncul di juri saat KP meminta verifikasi:
1. **Verifikasi Jatuhan** — "Apakah ada jatuhan?" → Juri jawab ya/tidak
2. **Verifikasi Pelanggaran** — "Apakah ada pelanggaran?" → Juri jawab ya/tidak

Di CI4 belum diimplementasi. Ini di-trigger via socket event dari KP.

### 2.5 Socket Events (Juri-related)

| Event | Direction | Payload | Purpose |
|-------|-----------|---------|---------|
| `JOIN_ROOM` | Juri→Server | `{id_pertandingan}` | Join match room |
| `NILAI_UPDATE` | Server→Juri | `{skor_merah, skor_biru}` | Score refresh after any juri entry |
| `VERIFIKASI_JATUHAN` | KP→Juri | `{id_pertandingan, sudut}` | Trigger jatuhan modal |
| `VERIFIKASI_PELANGGARAN` | KP→Juri | `{id_pertandingan, sudut}` | Trigger pelanggaran modal |
| `MATCH_STATUS_CHANGE` | Server→All | `{status}` | Match state change (berlangsung→selesai) |

---

## 3. Implementation Plan

### Phase 1: Tanding PERSILAT — Perbaikan & Penguatan

#### Task 1.1: Modularisasi JS Tanding
**Target:** `public/assets/js/penilaian/juri_tanding_persilat.js`

Pindahkan inline JS dari `light.php` ke file terpisah:
- AJAX scoring (pukulan/tendangan/hapus)
- CSRF rotation
- Score rendering
- Polling refresh (4 detik)
- Socket connection + event handlers

#### Task 1.2: Verifikasi Modal Components
**Target:** 
- `app/Views/pertandingan/juri/tanding/persilat/components/_verifikasi_jatuhan.php`
- `app/Views/pertandingan/juri/tanding/persilat/components/_verifikasi_pelanggaran.php`

Legacy behavior:
- Modal muncul saat socket event diterima
- Juri menjawab "Ya" / "Tidak"
- Jawaban dikirim via AJAX ke `submit_jawaban_verifikasi_pertandingan`

#### Task 1.3: Controller Method `submitJawabanVerifikasi`
**Target:** Tambah method di `Juri.php`

```php
public function submitJawabanVerifikasi(int $idPertandingan)
{
    $sudut = $this->request->getPost('sudut');
    $jenis = $this->request->getPost('jenis'); // 'jatuhan' | 'pelanggaran'
    $jawaban = $this->request->getPost('jawaban'); // 'ya' | 'tidak'
    // ... process
}
```

#### Task 1.4: View UI Enhancement (Parity Legacy)
**Improvement dari legacy:**
- Tombol lebih besar dan touch-friendly (parity dengan `persilat.js` legacy: auto-scroll ke area aktif)
- Feedback visual saat tap (ripple/pulse)
- Skor biru kiri, merah kanan (atau sebaliknya, sesuai legacy)
- Badge ronde aktif

---

### Phase 2: Seni PERSILAT — Full Implementation

#### Task 2.1: Routes Seni
**Target:** `app/Config/Routes.php`

```php
// Dalam group 'juri':
$routes->get('seni', 'Pertandingan\\Juri::seni');
$routes->get('seni/(:segment)', 'Pertandingan\\Juri::seni/$1');
$routes->post('edit-penilaian-seni/(:num)', 'Pertandingan\\Juri::editPenilaianSeni/$1');
$routes->post('refresh-status-seni/(:num)', 'Pertandingan\\Juri::refreshStatusSeni/$1');
$routes->post('refresh-status-seni', 'Pertandingan\\Juri::refreshStatusSeni');
$routes->post('toggle-ready-seni/(:num)', 'Pertandingan\\Juri::toggleReadySeni/$1');
```

#### Task 2.2: Controller Methods Seni
**Target:** Tambah 4 methods di `Juri.php`

| Method | HTTP | Fungsi |
|--------|------|--------|
| `seni($theme = 'sederhana')` | GET | Load view scoring seni |
| `editPenilaianSeni($idPenampilanSeni)` | POST | Save JSON penilaian seni |
| `refreshStatusSeni($idPenampilanSeni)` | POST | Poll status penampilan |
| `toggleReadySeni($idPenampilanSeni)` | POST | Toggle ready flag |

**`seni()` Logic:**
1. Get session `id_perangkat_pertandingan`, `id_gelanggang`
2. Find active penampilan seni di gelanggang
3. If none → redirect standby
4. Get format penilaian JSON dari file `public/assets/penilaian/format-penilaian/seni/persilat/...`
5. Get existing penilaian row for this juri+penampilan
6. Pass to view

**`editPenilaianSeni()` Logic:**
1. Receive full `data_nilai` JSON + `nilai_akhir_per_juri`
2. Find `penilaian_seni` row where `id_penampilan_seni` + `id_perangkat_pertandingan`
3. Update `penilaian` column + `nilai_akhir_per_juri`
4. Return JSON response

**`toggleReadySeni()` Logic:**
1. Find penilaian row
2. Toggle `status_ready` (0→1, 1→0)
3. Return new status

#### Task 2.3: View Seni Sederhana
**Target:** `app/Views/pertandingan/juri/seni/persilat/sederhana.php`

Layout (dari legacy `sederhanav2.php`):
```
┌─────────────────────────────────────────┐
│ Navbar: Gelanggang | Partai | Kategori  │
├─────────────────────────────────────────┤
│ Info Peserta: Nama, Kontingen           │
├─────────────────────────────────────────┤
│ ┌─────────────────────────────────────┐ │
│ │ KEBENARAN JURUS                     │ │
│ │ Jurus 1: [10][10][10][8][10]...     │ │
│ │ Jurus 2: [10][10][10][10]...        │ │
│ │ Total: 85.5 / 100                   │ │
│ └─────────────────────────────────────┘ │
│ ┌─────────────────────────────────────┐ │
│ │ KEMANTAPAN: [slider/input] 8.5      │ │
│ └─────────────────────────────────────┘ │
│ ┌─────────────────────────────────────┐ │
│ │ KEKOMPAKAN: [slider/input] 7.0      │ │
│ └─────────────────────────────────────┘ │
│ ┌─────────────────────────────────────┐ │
│ │ HUKUMAN (read-only dari KP)         │ │
│ │ Waktu Kurang: 0.5                   │ │
│ └─────────────────────────────────────┘ │
├─────────────────────────────────────────┤
│ TOTAL NILAI: 60.5                       │
│ [READY ✓]              [SIMPAN]         │
└─────────────────────────────────────────┘
```

**Key UI Elements:**
- Kebenaran input per gerakan: tombol -1 untuk mengurangi dari default 10
- Kemantapan/Kekompakan: input numerik atau slider
- Hukuman: **read-only** (diisi oleh KP, di-poll dari server)
- Total auto-calculated client-side
- Tombol READY: toggle status_ready
- Auto-save setiap perubahan (debounced 500ms)

#### Task 2.4: CSS Seni
**Target:** `public/assets/css/penilaian/juri-seni.css`

- `.kebenaran-grid` — grid tombol per gerakan jurus
- `.nilai-cell` — individual score cell (default hijau=10, kuning=<10, merah=0)
- `.unsur-section` — section container per unsur nilai
- `.total-bar` — sticky bottom total + buttons
- Responsive: tablet landscape optimized

#### Task 2.5: JS Seni
**Target:** `public/assets/js/penilaian/juri_seni_persilat.js`

Responsibilities:
- Load format penilaian JSON
- Render grid kebenaran (jumlah jurus × jumlah gerakan)
- Handle tap: decrement score per cell
- Calculate totals client-side (unsur_nilai, ringkasan)
- Auto-save (debounced AJAX)
- Poll hukuman dari server (setiap 3 detik) — update hukuman section
- Toggle ready
- Offline fallback (localStorage)
- Socket: listen for `PENAMPILAN_SELESAI` event → lock input

#### Task 2.6: PersilatSeniService Enhancement
**Target:** `app/Services/Scoring/Persilat/PersilatSeniService.php`

Existing file needs enhancement:
- `hitungNilaiAkhir(array $penilaianJuri): string` — median - hukuman
- `hitungMedian(array $values): float`
- `hitungHukuman(array $penilaianJuri): float`
- `hitungStandarDeviasi(array $values): float`
- `urutkanJuara(array $penampilanList): array`
- `validatePenaltyConsistency(array $penilaianJuri): array`
- `pilihPenilaianJuri(int $idPenampilan, array $sortedValues)` — mark `terpilih`

---

### Phase 3: Model & Data Layer

#### Task 3.1: PenilaianSeniModel Enhancement
**Target:** `app/Models/PenilaianSeniModel.php`

Tambah methods:
```php
public function getByJuriDanPenampilan(int $idPerangkat, int $idPenampilan): ?array
public function updateNilai(int $id, string $penilaian, string $nilaiAkhirPerJuri): bool
public function toggleReady(int $id): bool
public function getAllByPenampilan(int $idPenampilan): array
public function resetAllReady(int $idPenampilan): bool
```

#### Task 3.2: PenilaianTandingModel Enhancement  
**Target:** `app/Models/PenilaianTandingModel.php`

Verify existing methods cover:
```php
public function getByPertandinganDanPerangkat(int $idPertandingan, int $idPerangkat): ?array
public function lockAndGet(int $idPertandingan, int $idPerangkat): ?array  // FOR UPDATE
public function updatePenilaian(int $id, array $data): bool
```

---

### Phase 4: Format Penilaian JSON

#### Task 4.1: Verify Format Files Exist

Format penilaian seni PERSILAT harus tersedia di:
```
public/assets/penilaian/format-penilaian/seni/persilat/tunggal/persilat.json
public/assets/penilaian/format-penilaian/seni/persilat/ganda/persilat.json
public/assets/penilaian/format-penilaian/seni/persilat/beregu/persilat.json
```

Isi JSON ini mendefinisikan:
- Jumlah jurus per kategori
- Jumlah gerakan per jurus
- Unsur nilai yang aktif (kebenaran, kemantapan, kekompakan)
- Jenis hukuman yang berlaku

---

## 4. Endpoint Map (Frontend ↔ Backend)

### Tanding

| # | URL | Method | Request | Response |
|---|-----|--------|---------|----------|
| 1 | `/juri/tanding` | GET | — | HTML view |
| 2 | `/juri/tanding/dark` | GET | — | HTML view (dark) |
| 3 | `/juri/edit-penilaian-tanding/{id}` | POST | `{sudut, entry: {nilai, action?}}` | `{status, merah: {...}, biru: {...}, skor_merah, skor_biru, csrf_hash}` |
| 4 | `/juri/refresh-status-pertandingan/{id}` | POST | — | `{status, reload?, skor_merah, skor_biru}` |
| 5 | `/juri/submit-jawaban-verifikasi/{id}` | POST | `{sudut, jenis, jawaban}` | `{status}` |

### Seni

| # | URL | Method | Request | Response |
|---|-----|--------|---------|----------|
| 6 | `/juri/seni` | GET | — | HTML view |
| 7 | `/juri/seni/terperinci` | GET | — | HTML view (terperinci mode) |
| 8 | `/juri/edit-penilaian-seni/{id}` | POST | `{data_nilai: JSON, nilai_akhir_per_juri: float}` | `{status, new_nilai, csrf_hash}` |
| 9 | `/juri/refresh-status-seni/{id}` | POST | — | `{status, reload?, hukuman: {...}, penampilan_status}` |
| 10 | `/juri/toggle-ready-seni/{id}` | POST | — | `{status, ready: bool, csrf_hash}` |

---

## 5. Database Tables Used

| Table | Usage | Key Fields |
|-------|-------|------------|
| `penilaian_tanding` | Store per-juri tanding scores | `id_pertandingan`, `id_perangkat_pertandingan`, `penilaian_merah` (TEXT/JSON), `penilaian_biru` (TEXT/JSON), `pemenang` |
| `penilaian_seni` | Store per-juri seni scores | `id_penampilan_seni`, `id_perangkat_pertandingan`, `penilaian` (TEXT/JSON), `nilai_akhir_per_juri`, `terpilih`, `status_ready` |
| `pertandingan` | Match state | `status_pertandingan`, `ronde_pertandingan`, `skor_merah`, `skor_biru`, `ringkasan_nilai` |
| `penampilan_seni` | Performance state | `status_penampilan`, `nilai_akhir`, `akses_penilaian`, `diskualifikasi` |
| `perangkat_pertandingan` | Juri device auth | `id_gelanggang`, `posisi='juri'` |

---

## 6. Risiko & Catatan Khusus

1. **Race condition pada verification**: 2 juri tap bersamaan → timestamp harus server-side (`time()` di PHP), BUKAN client timestamp. CI4 service sudah handle ini.

2. **Penalty sync (seni)**: Hukuman diinput KP dan harus identik di semua juri. Legacy menggunakan `proses_penilaian_kp()` transactional. Di CI4, polling dari juri harus include latest hukuman dari server.

3. **Offline support (seni)**: Legacy JS punya localStorage fallback. Di CI4, implement debounced save yang queue entries saat offline dan flush saat reconnect.

4. **Format penilaian dinamis**: Format JSON bisa berbeda per sub-kategori seni (tunggal/ganda/beregu). View harus render UI berdasarkan format yang di-load, bukan hardcode.

5. **Akses penilaian**: Column `akses_penilaian` di `penampilan_seni` = `'ditutup'` artinya juri TIDAK boleh input lagi. Frontend harus respect ini.

6. **Multiple juri per gelanggang**: Satu gelanggang bisa punya 3-5 juri. Semua scoring ke pertandingan/penampilan yang sama. Row `penilaian_tanding`/`penilaian_seni` per juri (1 row per `id_perangkat_pertandingan`).

7. **Terpilih flag (seni)**: Setelah `hitung_nilai_akhir`, juri yang nilainya = median ditandai `terpilih=1`. Ini untuk display di KP/Layar.

---

## 7. File Changes Summary

### Files to CREATE:
```
app/Views/pertandingan/juri/seni/persilat/sederhana.php
app/Views/pertandingan/juri/seni/persilat/terperinci.php
app/Views/pertandingan/juri/tanding/persilat/components/_verifikasi_jatuhan.php
app/Views/pertandingan/juri/tanding/persilat/components/_verifikasi_pelanggaran.php
public/assets/css/penilaian/juri-seni.css
public/assets/js/penilaian/juri_tanding_persilat.js
public/assets/js/penilaian/juri_seni_persilat.js
```

### Files to MODIFY:
```
app/Controllers/Pertandingan/Juri.php — add seni methods + verifikasi
app/Config/Routes.php — add seni routes
app/Models/PenilaianSeniModel.php — add helper methods
app/Services/Scoring/Persilat/PersilatSeniService.php — implement full logic
app/Views/pertandingan/juri/tanding/persilat/light.php — extract inline JS
```

### Files to VERIFY:
```
public/assets/penilaian/format-penilaian/seni/persilat/tunggal/persilat.json
public/assets/penilaian/format-penilaian/seni/persilat/ganda/persilat.json
public/assets/penilaian/format-penilaian/seni/persilat/beregu/persilat.json
```

---

## 8. Execution Priority

```
Phase 1 (Tanding fixes)     → 4 tasks, ~1 jam
Phase 2 (Seni full)         → 6 tasks, ~3 jam  
Phase 3 (Model/Data)        → 2 tasks, ~30 menit
Phase 4 (Format JSON)       → 1 task, ~15 menit
```

**Total estimasi: ~5 jam implementasi**

Rekomendasi urutan:
1. Phase 3 (model layer) — foundation
2. Phase 1 (tanding fixes) — improve existing
3. Phase 4 (verify format files)  
4. Phase 2 (seni full) — heaviest, depends on 3+4
