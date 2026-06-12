# Layar Seni — Legacy CI3 Parity Document

## Overview

The "Layar Seni" is the scoreboard display for artistic/seni pencak silat performances, shown on a large screen. It displays participant info, juri (judge) scores, timer, median/penalty/standard deviation, and results in real-time. It uses Socket.IO for real-time timer updates and HTTP polling (every 1s) as the primary data refresh mechanism.

---

## 1. Controller: `Layar.php` (CI3)

**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/pertandingan/Layar.php`

### Constructor (`__construct`)
- Loads `Layar_model`, `Penilaian_seni_library`
- Sets session variables:
  - `$this->id_gelanggang`
  - `$this->id_pertandingan`
  - `$this->id_penampilan_seni`
  - `$this->id_perangkat_pertandingan`
  - `$this->theme = 'dark'`
- Queries:
  - `$this->penampilan_seni_berlangsung` = active seni performance via `Detail_jadwal_seni_model->get_penampilan_seni_berlangsung(id_gelanggang)`
  - `$this->partai_seni_berlangsung` = active match/partai via `Detail_jadwal_seni_model->get_partai_berlangsung(id_gelanggang)`
- Access control via `_remap()`: must be `level=perangkat_pertandingan` AND `posisi=layar`

### Method: `seni($mode = 'dark')`
**Route:** `layar/seni` or `layar/seni/light`

**Logic:**
1. If `penampilan_seni_berlangsung != null` → show scoring view
2. Else → call `standby_seni()`

**Data passed to view:**
| Variable | Source | Description |
|----------|--------|-------------|
| `penampilan_seni_berlangsung` | Constructor query | Current active performance object |
| `penampilan_seni` | `Penampilan_seni_model->get(id_kompetisi_seni)` | All performances in same kompetisi |
| `kompetisi_seni` | `Kompetisi_seni_model->find(id_kompetisi_seni)` | Competition metadata (jenis_seni, kategori_usia, sistem_penampilan) |
| `partai_seni_berlangsung` | Constructor query | Current match/partai (has nomor_partai, babak_battle, id_penampilan_seni_biru/merah) |
| `peserta_seni` | `Peserta_seni_model->get(id_kelompok_peserta_seni)` | Array of participants (nama_pendaftar, nama_kontingen) |
| `data_nilai` | `Penilaian_seni_model->kelompokkan_penilaian_seni(...)` | Grouped scores keyed by id_penampilan_seni, array of juri objects |
| `jenis_unsur_nilai` | `Penilaian_seni_model->get_jenis_unsur_nilai_seni(...)` | Scoring element types |
| `jenis_hukuman` | `Penilaian_seni_model->get_jenis_hukuman_seni(...)` | Penalty types |
| `footer` | `false` | Disables footer |
| `main_view` | `'pertandingan/layar/seni/{peraturan}/{mode}'` | Dynamic view path based on peraturan_pertandingan (persilat, tapak_suci, fpsti, festival) |

**View resolution:** `peraturan_pertandingan` field is transformed: spaces → underscores, lowercased → used as folder name.

### Method: `standby_seni()`
- Shows transition/idle screen when no performance is active
- Passes `id_penampilan_seni` and loads `pertandingan/layar/seni/transisi` view
- Shows sponsor video or logo animation

### Method: `refresh_status_seni($id_penampilan_seni = null)`
**Route:** `layar/refresh-status-seni/` (POST)

**Complex state machine returning JSON:**

| Condition | Response |
|-----------|----------|
| No active performance + no payload id | Checks for completed battle/pool results → returns `{status:true, reload:false, hasil_battle_seni:true}` or `{status:true, reload:false, hasil_pool_seni:true}` |
| No active performance + payload id present | `{status:true, reload:true}` — performance ended, go to standby |
| Active performance differs from session | Updates session, `{status:true, reload:true}` |
| Same performance + same id (or diskualifikasi) | Returns live data: `{status:false, penampilan_seni_berlangsung: {...}, data_nilai: {...}}` with `JSON_NUMERIC_CHECK` |
| Same performance + different id from standby | `{status:true, reload:true}` |

**Key behavior:** The `data_nilai` in the polling response uses the same `kelompokkan_penilaian_seni()` grouping, keyed by `id_penampilan_seni` containing an array of juri score objects.

### Method: `hasil_pool_seni($id_kompetisi_seni = NULL)`
**Route:** `layar/hasil-pool-seni` or `layar/hasil-pool-seni/{id}`

**Data passed:**
| Variable | Description |
|----------|-------------|
| `data_penampilan_seni` | Array of all performances in the kompetisi (from Detail_jadwal_seni_model) |
| `Kompetisi_seni` | Competition metadata |

### Method: `hasil_battle_seni($id_battle_seni = NULL)`
**Route:** `layar/hasil-battle-seni` or `layar/hasil-battle-seni/{id}`

**Data passed:**
| Variable | Description |
|----------|-------------|
| `penampilan_seni_biru` | Blue corner performance |
| `penampilan_seni_merah` | Red corner performance |
| `battle_seni` | Battle metadata (id_penampilan_seni_pemenang) |
| `peserta_seni_biru` | Blue corner participants |
| `peserta_seni_merah` | Red corner participants |

---

## 2. Main Views

### 2a. Dark Theme
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/persilat/dark.php`

**Structure:**
```
container-fluid bg-black min-vh-100
├── components/header (competition title bar)
├── #daftar-peserta (participant info with flag image + names + kontingen)
├── .urutan_total_nilai_juri (juri score columns, one per judge)
│   └── .kolom_total_nilai (dynamically populated)
├── Summary row:
│   ├── .kolom-median-kebenaran → .median_kebenaran
│   ├── .kolom-standar-deviasi → .standar_deviasi
│   ├── .kolom-median → .median
│   └── .kolom-hukuman → .hukuman
├── Final score + timer:
│   ├── .kolom-nilai-akhir → .nilai_akhir (bg color: blue/red for battle, gray for pool)
│   └── .kolom-waktu → .waktu_tampil (9em font timer)
```

**JS Dependencies:**
- `assets/penilaian/js/application/shared_timer.js`
- `assets/penilaian/js/application/layar/seni/persilat.js`

**Inline JS data:**
```javascript
$data_nilai = <?= json_encode($data_nilai, JSON_NUMERIC_CHECK) ?>;
$penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung, JSON_NUMERIC_CHECK) ?>;
layar.init($penampilan_seni_berlangsung, $data_nilai);
ui.start_animation();
```

**Key differences from light:**
- Has country flag image: `bendera($penampilan_seni_berlangsung->negara)`
- Has `shared_timer.js` included
- 4 summary boxes: Median Kebenaran, Standard Deviation, Median, Penalty
- Calls `ui.start_animation()` in document.ready

### 2b. Light Theme
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/persilat/light.php`

**Structure:** Same layout but with `bg-white bg-gradient` background.

**Key differences from dark:**
- No country flag image
- No `shared_timer.js`
- 3 summary boxes only: Median, Penalty, Standard Deviation (no Median Kebenaran)
- Does NOT call `ui.start_animation()` in document.ready (only initializes layar)

### 2c. Jateng Variant
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/persilat/jateng.php`

**Key differences:**
- Minimal/clean layout for Jawa Tengah federation events
- Uses blue/red corner colors based on `partai_seni_berlangsung->id_penampilan_seni_biru`
- Shows TWO rows of juri columns: unsorted (original order) + sorted (ascending)
- Summary: Median, Penalty, Time Performance, Total Score, Standard Deviation
- No animations (`start_animation` is empty function)
- Shows "RESULT" separator between unsorted/sorted juri values
- `nilai_akhir` uses `.toFixed(4)` instead of `.toFixed(3)`

---

## 3. Component: Header
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/persilat/components/header.php`

**Required variables from controller:**
- `$this->config->item('brand_abbreviation')` — for logo paths
- `get_instance()->get_setting('event_name')` — event title
- `$partai_seni_berlangsung->nama_gelanggang` — arena name
- `$partai_seni_berlangsung->nomor_partai` — match number
- `$kompetisi_seni->sistem_penampilan` — 'battle' or 'pool'
- `$partai_seni_berlangsung->babak_battle` — battle round (shown if battle)
- `$kompetisi_seni->nama_kategori_usia` — age category
- `$kompetisi_seni->jenis_seni` — art type (tunggal/ganda/regu)

**Structure:**
```
row bg-white bg-gradient-180-white #competition-title
├── col-1: International Federation logo
├── col-8/9: Event name + metadata badges (gelanggang, babak, kategori_usia, jenis_seni)
└── col-1: National Federation logo
```

### Shared header_seni.php
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/components/header_seni.php`
- **Empty file** (0 bytes) — not used

---

## 4. JavaScript: `persilat.js`
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/assets/penilaian/js/application/layar/seni/persilat.js`

### Global Object: `layar`

**Properties:**
- `penampilan_seni_berlangsung` — current performance object
- `stopwatch` — jQuery element `.waktu_tampil`
- `data_nilai` — grouped scoring data
- `socket` — Socket.IO instance (optional)

**Methods:**

#### `layar.init(penampilan, data_nilai)`
1. Initializes Socket.IO if `io` is defined
2. Calls `set_variable()`
3. Binds stopwatch to `.waktu_tampil`
4. If socket connected:
   - Emits `JOIN_ROOM` with `id_penampilan_seni`
   - Listens for `UPDATE_WAKTU` event: `{action, waktu}`
5. Calls `ui.update_tampilan_nilai()`
6. Calls `layar.update_timer()`
7. Calls `layar.refresh_status_seni()`
8. Calls `ui.start_animation()`

#### `layar.update_timer()`
- Uses jQuery timer plugin: `.timer({format: "%M:%S", action: "start", seconds: waktu_tampil})`
- If `status_penampilan !== "sedang_tampil"` → freezes timer with `.timer("remove")`

#### `layar.refresh_status_seni()`
- POST to `layar/refresh-status-seni/{id_penampilan_seni}`
- **Reload conditions:**
  - `data.status === true && data.reload === true`
  - `format_penilaian` changed
  - `data_nilai` array length changed (new juri connected)
- **Update conditions (no reload):**
  - `data.status === false` with `penampilan_seni_berlangsung` present
  - Updates variables and calls `ui.update_tampilan_nilai()`
  - Only updates timer if socket is NOT connected (avoids conflict)
- **Polling interval:** 1000ms (always)

### Socket.IO Events

| Event | Direction | Data | Action |
|-------|-----------|------|--------|
| `JOIN_ROOM` | emit | `id_penampilan_seni` | Joins room for this performance |
| `UPDATE_WAKTU` | receive | `{action, waktu}` | Updates timer display; freezes if not 'sedang_tampil' |

### UI Object (defined inline in views)

#### `ui.start_animation()`
Cascading fadeIn animations:
1. `#competition-title` → fadeInDown
2. `#daftar-peserta` → fadeInDown (700ms delay)
3. `.kolom_total_nilai` → staggered fadeInDown (200ms each)
4. Summary boxes → staggered fadeInDown
5. `.kolom-nilai-akhir` → fadeInLeft, `.kolom-waktu` → fadeInRight

#### `ui.update_tampilan_urutan_nilai_tiap_juri(data_nilai)`
1. Extracts `total_nilai` from each juri's `penilaian` JSON
2. Sorts ascending by value
3. Updates `.kolom_total_nilai` elements with sorted juri labels + values
4. Marks selected/deselected juri with `.bg-gradient-180-warning` / `.text-decoration-line-through`

#### `ui.update_tampilan_nilai()`
1. Calls `update_tampilan_urutan_nilai_tiap_juri`
2. Updates `.nilai_akhir` from `penampilan_seni_berlangsung.nilai_akhir`
3. Iterates juri scores, updates per-juri elements
4. Parses `catatan_nilai_sama` JSON for median/standar_deviasi/hukuman/median_kebenaran
5. Falls back to default penilaian format values if no catatan_nilai_sama

### Data Structure: `data_nilai`
```javascript
{
  [id_penampilan_seni]: [
    {
      id_perangkat_pertandingan: int,
      penilaian: string (JSON), // Contains: { penilaian: { unsur_nilai: {...}, ringkasan: { total_nilai, nilai_akhir, total_hukuman } } }
      terpilih: int (0 or 1)
    },
    ...
  ]
}
```

### Data Structure: `penampilan_seni_berlangsung`
Key fields used by JS:
- `id_penampilan_seni`
- `waktu_tampil` (seconds)
- `status_penampilan` ('sedang_tampil', 'sudah_tampil', 'belum_tampil')
- `nilai_akhir` (float)
- `catatan_nilai_sama` (JSON string with: median, median_kebenaran, standar_deviasi, hukuman)
- `format_penilaian`
- `negara` (country code for flag)

---

## 5. Hasil (Result) Views

### 5a. `hasil_pool_seni.php`
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/persilat/hasil_pool_seni.php`

**Required data:** `$data_penampilan_seni` (array), `$Kompetisi_seni`

**Structure:**
```
container-fluid rekap-pool bg-black min-vh-100 (hidden initially)
├── countdown-screen (5-second countdown "The Winner is..")
└── result-screen (table with rankings)
    ├── Header: logos + "Performance Results"
    └── Table rows per penampilan:
        - Name + kontingen (with medal-colored backgrounds: gold/silver/bronze/gray)
        - Median Kebenaran
        - Final Score (nilai_akhir)
        - Time (MM:SS)
        - Standard Deviation
```

**Medal colors:**
- `emas` → gold gradient `#ffd700 → #ffa800`
- `perak` → light gradient
- `perunggu` → bronze gradient `#a86e00 → #513500`
- else → gray-dark

**JS behavior:**
- 5-second countdown animation
- Fades countdown → shows result table
- Polls `refresh_status_seni()` every 4000ms for navigation

### 5b. `hasil_battle_seni.php`
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/persilat/hasil_battle_seni.php`

**Required data:** `$penampilan_seni_biru`, `$penampilan_seni_merah`, `$battle_seni`, `$peserta_seni_biru`, `$peserta_seni_merah`

**Structure:**
```
container-fluid rekap-battle bg-black min-vh-100 (hidden initially)
├── countdown-screen (5-second countdown)
├── comparison-screen (side-by-side Blue vs Red)
│   ├── Blue corner: names, kontingen, nilai_akhir, waktu, median, penalty, median_kebenaran, std deviation
│   ├── "VS" divider
│   └── Red corner: same stats
├── winner-screen (shows winner with "Congratulations!")
│   └── Winner details: name, kontingen, nilai_akhir, waktu, std deviation, median_kebenaran
└── commercial-break (sponsor video or logo animation)
```

**JS behavior:**
1. 5-second countdown
2. Show comparison screen (7 seconds)
3. Show winner screen (if `id_penampilan_seni_pemenang` is set)
4. After 27 seconds → show commercial break / logo animation
5. Polls `refresh_status_seni()` every 4000ms

### 5c. `hasil_battle_seni_jateng.php`
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/persilat/hasil_battle_seni_jateng.php`

**Required data:** Same as `hasil_battle_seni.php`

**Key differences:**
- No countdown animation (shows directly)
- Clean table-like layout (not flashy)
- Side-by-side comparison with labeled rows: Median, Penalty, Time Performance, Score, Standard Deviation
- Winner highlighted with colored background (bg-blue/bg-red)
- Shows "WINNER" pill badge above winner's column
- No commercial break / sponsor video section
- Polls `refresh_status_seni()` every 4000ms

---

## 6. Transisi/Standby View
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/seni/transisi.php`

**Logic:**
- If `tampilkan_video_sponsor_di_layar` config is TRUE → shows `video_sponsor` component
- Else → shows `animasi_logo` component
- Polls `refresh_status_seni()` every 4000ms
- Handles navigation to:
  - `layar/hasil-battle-seni` (if `hasil_battle_seni == true`)
  - `layar/hasil-pool-seni` (if `hasil_pool_seni == true`)
  - Reload (if new performance started)

---

## 7. Template
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/template.php`

**CSS deps:** Argon Dashboard, nucleo-icons, datatables, fontawesome, style-custom, animation.css, bracket
**JS deps:** jQuery, Popper, Bootstrap, perfect-scrollbar, smooth-scrollbar, datatables, sweetalert, timer.jquery.js, jquery.runner.js, jquery-ui, bracket, waitingfor, perangkat-pertandingan.js

**Critical:** Sets `SOCKET_URL` from `$this->config->item('socket_io_host')` and loads `socket.io.min.js`

---

## 8. Other Seni JS Files (non-persilat variants)
Located at `/Applications/XAMPP/xamppfiles/htdocs/dps/assets/penilaian/js/application/layar/seni/`:
- `persilat.js` — Main (analyzed above)
- `festival.js` — Festival rules
- `tapak_suci.js` — Tapak Suci rules
- `ipsi_2012.js` — IPSI 2012 rules
- `fpsti.js` — FPSTI rules

---

## 9. CI4 Migration Status

**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps-scoringsystem/app/Controllers/Pertandingan/Layar.php`

### Already migrated:

| Method | Status | Gaps |
|--------|--------|------|
| `seni($theme)` | ✅ Partial | Returns simplified data; missing: `kompetisi_seni`, `partai_seni_berlangsung`, `peserta_seni`, `data_nilai` (grouped), `jenis_unsur_nilai`, `jenis_hukuman`; uses single flat view instead of per-peraturan views |
| `refreshStatusSeni($id)` | ✅ Partial | Missing: `data_nilai` in response (returns `juri_data` array instead), missing pool/battle result detection logic, missing `catatan_nilai_sama` |
| `hasilPoolSeni($id)` | ✅ Stub | Missing: `catatan_nilai_sama` decode, medal colors, countdown animation |
| `hasilBattleSeni($id)` | ✅ Stub | Only passes id_battle_seni; missing all comparison data |
| `standby_seni()` → `transisi()` | ✅ Basic | Missing sponsor video logic and poll routing |

### Key gaps to achieve parity:

1. **Data structure:** CI4 `refreshStatusSeni` returns a simplified `juri_data[]` array. Legacy returns full `data_nilai` grouped by `id_penampilan_seni` with raw `penilaian` JSON strings that the frontend JS parses.

2. **View routing:** Legacy dynamically picks view based on `peraturan_pertandingan` (persilat/tapak_suci/fpsti/festival). CI4 uses a single `seni` view.

3. **Missing views:** No dark/light/jateng variants, no hasil_battle_seni, no hasil_pool_seni with proper data in CI4.

4. **Polling logic complexity:** The `refresh_status_seni` state machine in legacy (detecting pool completion, battle winner, medal input) is not replicated in CI4.

5. **Socket.IO:** The `SOCKET_URL` config and Socket.IO client are part of the legacy template. CI4 needs equivalent setup.

6. **Timer plugin:** Legacy uses `timer.jquery.js` plugin with `{format: "%M:%S", action: "start", seconds: N}` API. CI4 needs this or equivalent.

7. **`catatan_nilai_sama`:** JSON field on `penampilan_seni` containing `{median, median_kebenaran, standar_deviasi, hukuman}` — displayed in both live and result views.

---

## 10. Data Flow Summary

```
[Controller]
    │
    ├─ penampilan_seni_berlangsung (active performance row)
    ├─ kompetisi_seni (competition metadata)
    ├─ partai_seni_berlangsung (match/partai row with battle info)
    ├─ peserta_seni[] (participant names)
    ├─ data_nilai[id_penampilan][juri_index] = {id_perangkat_pertandingan, penilaian(JSON), terpilih}
    │
    ▼
[View - dark/light/jateng]
    │
    ├─ Header component (event name, logos, metadata)
    ├─ Participant display (names, kontingen, flag)
    ├─ Juri score columns (dynamic count)
    ├─ Summary: median, median_kebenaran, std_deviation, penalty
    ├─ Final score + Timer
    │
    ▼
[JS - persilat.js]
    │
    ├─ Socket.IO: JOIN_ROOM, UPDATE_WAKTU
    ├─ HTTP Poll: refresh_status_seni (1s interval)
    ├─ Timer: jQuery timer plugin
    └─ UI updates: parse penilaian JSON, sort juri, update DOM
```

---

## 11. Required Models/Libraries for CI4 Parity

| CI3 Model/Library | Purpose |
|-------------------|---------|
| `Layar_model` | refresh_status_pertandingan logic |
| `Penilaian_seni_library` | Score calculation |
| `Penilaian_seni_model->kelompokkan_penilaian_seni()` | Groups raw scores by penampilan |
| `Penilaian_seni_model->get_jenis_unsur_nilai_seni()` | Gets scoring element types |
| `Penilaian_seni_model->get_jenis_hukuman_seni()` | Gets penalty types |
| `Detail_jadwal_seni_model->get_penampilan_seni_berlangsung()` | Gets active performance |
| `Detail_jadwal_seni_model->get_partai_berlangsung()` | Gets active match |
| `Penampilan_seni_model` | Performance CRUD |
| `Kompetisi_seni_model` | Competition metadata |
| `Peserta_seni_model` | Participant data |
| `Kelompok_peserta_seni` (implicit) | Group membership |
