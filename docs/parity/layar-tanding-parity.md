# Layar Tanding (Scoreboard Display) — Legacy CI3 Parity Document

## Overview

The "Layar Tanding" is a full-screen scoreboard display shown on a large monitor/TV during Pencak Silat fighting (tanding) matches. It shows real-time scores, timer, athlete info, penalty indicators, and verification modals. It uses Socket.IO for real-time timer updates and HTTP polling as fallback for score/state refresh.

---

## 1. CONTROLLER — Legacy CI3

**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/pertandingan/Layar.php`

### Class: `layar extends MY_Controller`

#### Constructor
- Loads `Layar_model`, `Penilaian_seni_library`
- Reads from session: `id_gelanggang`, `id_pertandingan`, `id_penampilan_seni`, `id_perangkat_pertandingan`
- Sets `$this->theme = 'dark'`
- Queries `Detail_jadwal_tanding_model` for `pertandingan_berlangsung` (active match)
- Access guard via `_remap()`: only allows `level == 'perangkat_pertandingan' && posisi == 'layar'`

#### Method: `tanding($theme = 'dark')`
**Data passed to view:**
| Variable | Source | Description |
|----------|--------|-------------|
| `$data['pertandingan']` | `Detail_jadwal_tanding_model->get_pertandingan_berlangsung()` | Active match object (with `bagan_pertandingan` and `data_waktu` json_decoded) |
| `$data['verifikasi_pertandingan']` | `Verifikasi_pertandingan_model->find()` | Latest verification record (DESC) |
| `$data['perangkat_pertandingan']` | `Perangkat_pertandingan_model->get()` | Array of judge devices |
| `$data['data_nilai']` | `Penilaian_tanding_model->kelompokkan_penilaian_tanding()` | Grouped scoring data (object) |
| `$data['atlet_biru']` | `Pertandingan_model->get_atlet_pertandingan()` | Blue corner athlete info |
| `$data['atlet_merah']` | `Pertandingan_model->get_atlet_pertandingan()` | Red corner athlete info |
| `$data['format_penilaian']` | derived from `$pertandingan->format_penilaian` | e.g. "persilat" |
| `$data['footer']` | `false` | Hides footer |

**View resolution:** `'pertandingan/layar/tanding/' . strtolower($peraturan_pertandingan) . '/' . $theme`
- For PERSILAT dark theme → `pertandingan/layar/tanding/persilat/dark` (but v3 is loaded as latest)
- Uses template: `pertandingan/layar/template`

**Fallback:** If no active match, calls `standby_tanding()` which shows `pertandingan/layar/tanding/transisi`

#### Method: `refresh_status_pertandingan($id_pertandingan = null)`
**HTTP polling endpoint** called repeatedly by JS.
- Returns JSON with `status`, `reload`, `pertandingan`, `data_nilai`, `verifikasi_pertandingan`
- Logic:
  - No active match + no id → `{status: true, reload: false}` (stay on standby)
  - No active match + has id → `{status: true, reload: true}` (go to standby)
  - Different match id → set session + `{status: true, reload: true}`
  - Same match → return full data payload (no reload)

---

## 2. TEMPLATE — Layout

**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/template.php`

### CSS Dependencies (loaded in `<head>`):
- `assets/penilaian/css/nucleo-icons.css`
- `assets/penilaian/css/argon-dashboard.css?v=2.1.0` (main UI framework)
- `assets/penilaian/css/plugins/datatables/datatables.min.css`
- `assets/fontawesome/css/all.min.css`
- `assets/penilaian/css/style-custom.css` (custom styles, gradients, navbar)
- `assets/bracket-pertandingan/jquery.bracket.min.css`
- `assets/penilaian/css/animation.css` (animate.css variant + `.opacity` class)

### JS Dependencies (loaded in `<head>`):
- `assets/penilaian/js/jquery/jquery.min.js`
- `assets/penilaian/js/template/core/popper.min.js`
- `assets/penilaian/js/template/core/bootstrap.min.js`
- `assets/penilaian/js/template/plugins/perfect-scrollbar.min.js`
- `assets/penilaian/js/template/plugins/smooth-scrollbar.min.js`
- `assets/penilaian/js/template/plugins/datatables/datatables.min.js`
- `assets/penilaian/js/template/plugins/sweetalert.min.js`
- `assets/penilaian/js/application/timer.jquery.js` ← **CRITICAL: jQuery timer plugin for countdown**
- `assets/penilaian/js/application/jquery.runner.js` ← **Runner/stopwatch plugin**
- `assets/penilaian/js/template/plugins/jquery-ui.min.js`
- `assets/admin/js/font-awesome.js`
- `assets/bracket-pertandingan/jquery.bracket.min.js`
- `assets/penilaian/js/template/plugins/waitingfor/bootstrap-waitingfor.min.js`
- `assets/penilaian/js/application/perangkat-pertandingan.js`
- `assets/penilaian/js/application/socket/socket.io.min.js` ← **Socket.IO client**

### Socket.IO Configuration:
```javascript
const SOCKET_URL = "<?= $this->config->item('socket_io_host') ?>";
```

### Template Structure:
```
<html>
  <head> [CSS + JS] </head>
  <body>
    [Optional navigation_bar]
    [Optional notification]
    [main_view] ← this is where tanding/v3.php loads
    [Optional footer]
    argon-dashboard.js
    heartbeat component
  </body>
</html>
```

---

## 3. MAIN VIEW — v3.php (Latest Tanding Scoreboard)

**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/tanding/persilat/v3.php`

### Inline CSS (lines 1-95):
- Fade animations: `.fade-left`, `.fade-right`, `.fade-up`, `.fade-down` with `.show` state
- `.display-score`: huge responsive score font (`clamp(7rem, 25vw, 15em)`)
- `.score-changed`: scale animation on score change
- `.bg-dim-blue` (`#0d2a49ff`): dimmed penalty indicator blue
- `.bg-dim-red` (`#3b0a11`): dimmed penalty indicator red

### HTML Structure:
```
div.container-fluid.min-vh-100.bg-gradient-180-black
├── [competition_title_light] — event name, category, weight class, logos
├── [header] — athlete names, flags/silhouettes, babak (round stage)
├── Row: nomor-partai | waktu (timer) | ronde
├── Row.big-score:
│   ├── col-2: indikator-pelanggaran-biru (binaan×2, teguran×2, peringatan×2)
│   ├── col-4: skor_biru (large score display)
│   ├── col-4: skor_merah (large score display)
│   └── col-2: indikator-pelanggaran-merah (binaan×2, teguran×2, peringatan×2)
├── Row:
│   ├── col-2: indikator-jatuhan-biru (dropping count)
│   ├── col-8: Judge indicators (2 rows × N judges, icons for pukulan/tendangan/jatuhan/hukuman)
│   └── col-2: indikator-jatuhan-merah (dropping count)
├── [stinger] — round transition animation overlay
├── [modal_hasil_verifikasi]
├── [modal_verifikasi_jatuhan]
└── [modal_verifikasi_pelanggaran]
<script src="persilat.js">
<script> inline init + ui object </script>
```

### Key Data Variables (passed via json_encode in inline script):
```javascript
$data_nilai = <?= json_encode($data_nilai); ?>;
$pertandingan = <?= json_encode($pertandingan) ?>;
$verifikasi_pertandingan = <?= json_encode($verifikasi_pertandingan) ?>;
layar.init($data_nilai, $pertandingan, $verifikasi_pertandingan, 500);
```

### Inline JS — `ui` Object (lines 304-860):
| Method | Purpose |
|--------|---------|
| `ui.start_animation()` | Staged fade-in animation of UI elements in 3 groups (1000ms, 2700ms, 4000ms) |
| `ui.update_tampilan_nilai()` | Updates all score displays, judge indicators, dropping counts, highlights |
| `ui.highlight_nilai_akhir()` | Highlights winning score column (blue/red/same) based on score comparison |
| `ui.highlight_nilai_sudut($sudut)` | Applies CSS class changes to highlight winner's score box |
| `ui.highlight_hukuman()` | Lights up penalty indicators (binaan, teguran, peringatan) based on penalty data |
| `ui.reset_highlight_juri($element, $timeout)` | Resets judge indicator highlight after timeout |
| `ui.open_modal_verifikasi_jatuhan()` | Opens drop verification modal |
| `ui.open_modal_verifikasi_pelanggaran()` | Opens penalty verification modal |
| `ui.open_modal_hasil_verifikasi($bg, $text)` | Opens verification result modal (auto-closes after 4s) |
| `adjustScoreFontSize()` | Reduces font for 3-digit scores |
| `updateScores()` + MutationObserver | Triggers animation on score DOM changes |

### Winner Determination Logic (tiebreaker hierarchy):
1. Higher score wins
2. If tied: fewer peringatan_2 (-10)
3. If tied: fewer peringatan_1 (-5)
4. If tied: fewer teguran_2 (-2)
5. If tied: fewer teguran_1 (-1)
6. If tied: fewer binaan_2
7. If tied: fewer binaan_1
8. If still tied: more jatuhan (drops)
9. If still tied: more tendangan (kicks)
10. If still tied: more pukulan (punches)
11. If completely same: highlight "sama"

---

## 4. COMPONENTS — Persilat-specific

### 4a. Header (Athlete Info Bar)
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/tanding/persilat/components/header.php`

- Shows athlete name, kontingen (team), flag/silhouette
- Blue athlete on left, Red on right, Babak (round stage) in center
- Uses config: `$this->config->item('tampilan_siluette_atlet', 'scoring/tanding')`
- Uses helper: `bendera($atlet->negara)` for flag image
- CSS classes: `bg-gradient-180-blue`, `bg-gradient-180-red`, `bg-gradient-180-gray-dark`

### 4b. Competition Title (Light)
**File:** `.../components/competition_title_light.php`

- Shows event name, category (kategori_usia), gender (jenis_kelamin), label (weight class)
- Shows international federation logo + national federation logo
- Uses: `get_instance()->get_setting('event_name')`
- Brand logos: `assets/images/brand/{abbreviation}/logo-international-federation.png`, `logo-federation.png`

### 4c. Verifikasi Pelanggaran (Penalty Verification — persilat-specific)
**File:** `.../components/verifikasi_pelanggaran.php`

- Simple Bootstrap modal with "Penalty Verification" text
- Modal ID: `#modalVerifikasiPelanggaran`

### 4d. Verifikasi Jatuhan (Drop Verification — persilat-specific)
**File:** `.../components/verifikasi_jatuhan.php`

- Simple Bootstrap modal with "Drop Verification" text
- Modal ID: `#modalVerifikasiJatuhan`

---

## 5. SHARED COMPONENTS

### 5a. Modal Verifikasi Pelanggaran (Shared Loader)
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/layar/components/modal_verifikasi_pelanggaran.php`

- Dynamically loads persilat-specific component based on `$format_penilaian`
- Checks for file existence at path: `pertandingan/layar/tanding/{format}/components/verifikasi_pelanggaran`

### 5b. Modal Verifikasi Jatuhan (Shared Loader)
**File:** `.../components/modal_verifikasi_jatuhan.php`

- Same dynamic loading pattern as above for jatuhan modals

### 5c. Modal Hasil Verifikasi
**File:** `.../components/modal_hasil_verifikasi.php`

- Modal ID: `#modalHasilVerifikasi`
- Shows verification result text (e.g., "Valid Drop!", "Invalid Violation!")
- Dynamic background color class applied via JS

### 5d. Navigation Bar
**File:** `.../components/navigation_bar.php`

- NOT loaded for tanding view (no `$data['navigation_bar']` set in tanding method)
- Contains brand logo, home link, language picker, user badge, logout

### 5e. Animasi Logo
**File:** `.../components/animasi_logo.php`

- Shows GIF or PNG logo during idle/commercial break
- CSS class: `.commercial-break.d-none` (hidden by default)
- Checks for GIF existence, falls back to animated PNG

### 5f. Video Sponsor
**File:** `.../components/video_sponsor.php`

- Plays sponsor videos in loop during idle
- Config: `$this->config->item('assets/video_sponsor')`
- Auto-advances through video playlist

### 5g. Stinger (Round Transition Animation)
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/views/pertandingan/components/stinger.php`

- Full-screen overlay with two diagonal slashes that slide in from left/right
- Shows text (e.g., "Round 2") in center
- JS object `stinger` with methods: `set_text()`, `set_font_size()`, `start_animation(callback)`, `end_animation(callback)`
- Z-index: 9999
- Used during ronde (round) transitions

---

## 6. JAVASCRIPT — Main Logic

**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/assets/penilaian/js/application/layar/tanding/persilat.js`

### Object: `layar` (global)

#### Properties:
| Property | Type | Description |
|----------|------|-------------|
| `interval_refresh` | number | Polling interval (default 1000ms) |
| `data_nilai` | object | Grouped scoring data from all judges |
| `pertandingan` | object | Current match state |
| `id_pertandingan` | string/int | Current match ID |
| `ronde_pertandingan` | string | Current round number |
| `verifikasi_pertandingan` | object/null | Current verification state |
| `waktu_sekarang` | number | Current time in milliseconds |
| `waktu_per_ronde` | number | Time per round |
| `stopwatch` | jQuery | Timer DOM element (`.stopwatch`) |
| `ringkasan_nilai` | object | Score summary parsed from JSON |
| `skor_biru_verifikasi` | number | Tracking verification answers |
| `skor_merah_verifikasi` | number | Tracking verification answers |
| `modalVerifikasiJatuhan` | Bootstrap.Modal | Drop verification modal instance |
| `modalVerifikasiPelanggaran` | Bootstrap.Modal | Penalty verification modal instance |
| `modalHasilVerifikasi` | Bootstrap.Modal | Result modal instance |

#### Socket.IO Events:

**Emitted:**
| Event | Data | When |
|-------|------|------|
| `JOIN_ROOM` | `layar.id_pertandingan` | On init — joins match-specific room |

**Listened:**
| Event | Handler | Description |
|-------|---------|-------------|
| `UPDATE_WAKTU` | Updates timer display | Receives `{waktu, action}`. If action='berlangsung' starts countdown, else pauses |

#### Key Methods:

| Method | Description |
|--------|-------------|
| `init($data_nilai, $pertandingan, $verifikasi, $interval)` | Bootstrap: setup modals, socket, variables, start polling |
| `set_variable(...)` | Sets all internal state from server data |
| `setup_modals()` | Initializes Bootstrap Modal instances for 3 modals |
| `update_timer()` | Fallback timer management when socket disconnected |
| `close_modal_verifikasi_jatuhan()` | Hides drop verification modal |
| `close_modal_verifikasi_pelanggaran()` | Hides penalty verification modal |
| `periksa_sistem_dialog()` | Checks verification state and shows/hides appropriate modals + results |
| `refresh_status_pertandingan()` | Recursive HTTP polling loop. POST to `layar/refresh-status-pertandingan/{id}` |

#### Polling Flow:
```
refresh_status_pertandingan() 
  → POST layar/refresh-status-pertandingan/{id}
  → if reload: window.location.reload()
  → else:
    → check ronde change → trigger stinger animation
    → set_variable() with new data
    → ui.update_tampilan_nilai()
    → update_timer() (if no socket)
    → periksa_sistem_dialog() (after 1s delay)
  → .always() → setTimeout → recursive call (every interval_refresh ms)
```

#### Timer Logic:
- **Socket connected:** Timer controlled by `UPDATE_WAKTU` events from server
- **Socket disconnected:** Fallback uses `update_timer()` which reads `waktu_sekarang` from polling data
- Uses jQuery Timer plugin: `.timer({format: "%M:%S", countdown: true, duration: seconds})`
- Timer states: `start`, `pause`, `remove`

#### Verification Dialog Flow:
1. `periksa_sistem_dialog()` checks `verifikasi_pertandingan` object
2. If `jenis_verifikasi === "jatuhan"`:
   - Status "berlangsung" → show drop verification modal
   - Status "selesai" + modal still open → close modal, show result after 700ms
3. If `jenis_verifikasi === "pelanggaran"`:
   - Same pattern as above for penalty verification
4. Result displayed: "Valid Drop!", "Valid Violation!", "Invalid Drop!", "Invalid Violation!"
5. Result modal auto-closes after 4000ms

---

## 7. CSS

### Primary Stylesheet
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/assets/penilaian/css/argon-dashboard.css`
- Main UI framework (Argon Dashboard theme for Bootstrap 5)
- Contains gradient classes: `bg-gradient-180-blue`, `bg-gradient-180-red`, `bg-gradient-180-black`, `bg-gradient-180-gray-dark`, `bg-gradient-180-white`

### Animation CSS
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/assets/penilaian/css/animation.css`
- Based on animate.css 3.7.2
- Defines `.opacity { opacity: 0; }` — initial hidden state for entrance animations
- Various animation keyframes (bounce, flash, fadeIn, etc.)
- Used with `flipXLogo` for logo animation

### Custom Timer CSS
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/assets/penilaian/css/custom-timer-tanding.css`
- Timer font sizing: `font-size: 5rem`
- Responsive adjustments for tablets (768px-1280px)
- Styling for `.block-ronde`, `.block-tombol-ronde`, `.timer-tanding`

### Custom Styles
**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps/assets/penilaian/css/style-custom.css`
- CSS variables: `--brand-red: #d90429`, `--brand-red-dark: #b90422`
- Navbar custom styling (`.navbar-custom`)
- Card styling, button styling
- Note: This file is more for the operator/juri interface, not the layar display itself

### Inline CSS in v3.php (Layar-specific):
- `.fade-left/right/up/down` + `.show` transitions
- `.display-score` — responsive giant score font
- `.score-changed` — scale animation keyframe
- `.bg-dim-blue` / `.bg-dim-red` — dimmed penalty indicator backgrounds

---

## 8. ASSETS — Images & Icons

### Penalty Indicator Icons (in v3.php view):
| Path | Description |
|------|-------------|
| `assets/images/icon/binaan-1.png` | Binaan level 1 indicator |
| `assets/images/icon/binaan-2.png` | Binaan level 2 indicator |
| `assets/images/icon/teguran-1.png` | Teguran (caution) level 1 |
| `assets/images/icon/teguran-2.png` | Teguran (caution) level 2 |
| `assets/images/icon/peringatan-1.png` | Peringatan (warning) level 1 |
| `assets/images/icon/peringatan-2.png` | Peringatan (warning) level 2 |
| `assets/images/icon/siluette_atlet.png` | Athlete silhouette placeholder |

### Judge Action Icons (in v3.php view):
| Path | Description |
|------|-------------|
| `assets/penilaian/icons/pukulan.png` | Punch icon (blue corner) |
| `assets/penilaian/icons/pukulan-inverted.png` | Punch icon inverted (red corner) |
| `assets/penilaian/icons/tendangan.png` | Kick icon (red corner) |
| `assets/penilaian/icons/tendangan-inverted.png` | Kick icon inverted (blue corner) |
| `assets/penilaian/icons/jatuhan.png` | Drop icon |
| `assets/penilaian/icons/hukuman.png` | Penalty icon |

### Brand Logos (dynamic based on config):
| Path Pattern | Description |
|------|-------------|
| `assets/images/brand/{abbreviation}/logo-international-federation.png` | International federation logo |
| `assets/images/brand/{abbreviation}/logo-federation.png` | National federation logo |
| `assets/images/brand/{abbreviation}/logo-digital-scoring.gif/.png` | Scoring system logo (idle) |
| `assets/images/brand/{abbreviation}/logo-match-operator.png` | Operator navbar logo |

---

## 9. DATA FLOW DIAGRAM

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│   Socket.IO     │         │   HTTP Polling    │         │   Page Load     │
│   Server:3000   │         │   (Fallback)      │         │   (Initial)     │
└────────┬────────┘         └────────┬──────────┘         └────────┬────────┘
         │                           │                              │
    UPDATE_WAKTU              POST refresh-status-          Controller::tanding()
    {waktu, action}           pertandingan/{id}             queries DB, builds data
         │                           │                              │
         ▼                           ▼                              ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                          layar.js (persilat.js)                              │
│                                                                             │
│  init() → set_variable() → ui.update_tampilan_nilai()                       │
│         → socket.emit('JOIN_ROOM')                                          │
│         → refresh_status_pertandingan() [recursive polling]                  │
│                                                                             │
│  Socket: UPDATE_WAKTU → update countdown timer display                       │
│  Polling: refresh data → update scores, penalties, verifications             │
│           → check ronde change → stinger animation                           │
│           → periksa_sistem_dialog() → show/hide verification modals          │
└─────────────────────────────────────────────────────────────────────────────┘
         │                           │                              │
         ▼                           ▼                              ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                              DOM Updates                                      │
│                                                                             │
│  .skor_biru / .skor_merah          → Score display                           │
│  .stopwatch                         → Timer (MM:SS countdown)                │
│  .ronde_pertandingan                → Round number                           │
│  .total_jatuhan_biru/merah          → Drop count                             │
│  .indikator-pelanggaran-*           → Penalty indicator lights               │
│  .juri-{id}-{sudut}-indikator       → Judge action flash (punch/kick/drop)   │
│  #modalVerifikasiJatuhan            → Drop verification overlay              │
│  #modalVerifikasiPelanggaran        → Penalty verification overlay           │
│  #modalHasilVerifikasi              → Verification result display            │
│  #stinger-container                 → Round transition animation             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 10. CI4 MIGRATION STATUS

**File:** `/Applications/XAMPP/xamppfiles/htdocs/dps-scoringsystem/app/Controllers/Pertandingan/Layar.php`

### What's Already Migrated:
- ✅ Controller skeleton with proper CI4 namespace
- ✅ `tanding($theme)` method — queries active match, returns view with basic data
- ✅ `refreshStatusPertandingan($id)` — polling endpoint (simplified, missing full data_nilai/verifikasi)
- ✅ `seni($theme)` method
- ✅ `refreshStatusSeni($id)` method
- ✅ `hasilPoolSeni($id)` and `hasilBattleSeni($id)`
- ✅ `transisi($mode)` and `hasilTanding($id)`
- ✅ `index()` home/landing
- ✅ CSRF token in JSON responses

### What's MISSING vs Legacy (Parity Gaps):

| Feature | Legacy | CI4 Status |
|---------|--------|------------|
| `data_nilai` (grouped scoring) | Full `kelompokkan_penilaian_tanding()` in response | ❌ Not included in tanding view or polling |
| `verifikasi_pertandingan` | Included in view data + polling | ❌ Not included |
| `perangkat_pertandingan` (judge list) | Passed to view for indicator rendering | ❌ Not included |
| `format_penilaian` | Passed to shared modal loaders | ❌ Not included |
| `bagan_pertandingan` json_decode | Done in controller | ❌ Not done |
| `data_waktu` json_decode | Done in controller, passed to JS | ⚠️ Partially (decoded but structure differs) |
| `ringkasan_nilai` | Available in polling response | ⚠️ Included in polling but not view |
| Session `id_pertandingan` update | Updated on match change | ❌ Not handled |
| `standby_tanding()` | Shows previous match result + next match info | ⚠️ Simplified to generic standby view |
| View per format/theme | Dynamic path resolution | ❌ Single view assumed |
| Views (tanding v3, components) | Full component tree | ❌ Views not yet created |
| JS (persilat.js) | Complete layar logic | ❌ Not yet migrated |
| Socket.IO integration | Full timer + room system | ❌ Not in views yet |
| Stinger animation | Round transition overlay | ❌ Not migrated |

### Models Used in Legacy (need CI4 equivalents):
- `Detail_jadwal_tanding_model` → `get_pertandingan_berlangsung($id_gelanggang)`
- `Penilaian_tanding_model` → `get()`, `kelompokkan_penilaian_tanding()`
- `Verifikasi_pertandingan_model` → `find()`
- `Perangkat_pertandingan_model` → `get()`
- `Pertandingan_model` → `get_atlet_pertandingan()`
- `Layar_model` → `refresh_status_pertandingan()`, `keluar_pertandingan()`

---

## 11. KEY CONFIGURATION ITEMS

| Config Key | Usage |
|------------|-------|
| `socket_io_host` | Socket.IO server URL (default `http://localhost:3000`) |
| `brand_abbreviation` | Brand folder name for logos |
| `brand_name` | Display name |
| `tampilan_siluette_atlet` (scoring/tanding) | Toggle athlete photo vs silhouette |
| `assets/video_sponsor` | Sponsor video playlist config |
| `event_name` (DB setting) | Event name shown in title bar |
| `event_logo` (DB setting) | Favicon |

---

## 12. SUMMARY OF FILES TO MIGRATE

### Priority 1 — Core Functionality:
1. `assets/penilaian/js/application/layar/tanding/persilat.js` → Port to CI4 public assets
2. View: `pertandingan/layar/tanding/persilat/v3.php` → CI4 view (Blade or PHP)
3. View: `pertandingan/layar/template.php` → CI4 layout
4. Component: `pertandingan/components/stinger.php`

### Priority 2 — Components:
5. `pertandingan/layar/tanding/persilat/components/header.php`
6. `pertandingan/layar/tanding/persilat/components/competition_title_light.php`
7. `pertandingan/layar/tanding/persilat/components/verifikasi_pelanggaran.php`
8. `pertandingan/layar/tanding/persilat/components/verifikasi_jatuhan.php`
9. `pertandingan/layar/components/modal_hasil_verifikasi.php`
10. `pertandingan/layar/components/modal_verifikasi_pelanggaran.php` (loader)
11. `pertandingan/layar/components/modal_verifikasi_jatuhan.php` (loader)

### Priority 3 — Assets:
12. All icons from `assets/penilaian/icons/` (7 files)
13. All icons from `assets/images/icon/` (binaan, teguran, peringatan, siluette)
14. CSS: `animation.css`, relevant classes from `argon-dashboard.css`
15. JS plugins: `timer.jquery.js`, `jquery.runner.js`, `socket.io.min.js`

### Priority 4 — Idle/Transition:
16. `pertandingan/layar/components/animasi_logo.php`
17. `pertandingan/layar/components/video_sponsor.php`
18. Standby/transisi views
