# Socket.IO Initialization & Event Handler Patterns
## CI4 DPS Scoring System — Persilat Module

---

## FILE 1: `layar_tanding_persilat.js`
**Purpose:** Display screen for Tanding (competitive fighting) matches  
**Type:** Display/Read-only (no scoring input)

### Socket Initialization
```javascript
// Lines 35-37
if (typeof io !== 'undefined' && typeof SOCKET_URL !== 'undefined') {
    layar.socket = io(SOCKET_URL, { reconnection: true, reconnectionDelay: 1000 });
}

// Lines 42-43
if (layar.socket) {
    layar.socket.emit('JOIN_ROOM', layar.id_pertandingan);
}
```

**Key Details:**
- Socket URL comes from `SOCKET_URL` global variable
- Room join uses: `JOIN_ROOM` event with `layar.id_pertandingan` (match ID)
- Reconnection enabled with 1000ms delay

### Existing Socket Event Handlers
```javascript
// Lines 44-73: UPDATE_WAKTU (timer sync from server)
socket.on('UPDATE_WAKTU', function (data) {
    layar.waktu_sekarang = data.waktu;
    layar.pertandingan.status_pertandingan = data.action;
    var seconds = Math.floor(layar.waktu_sekarang / 1000);
    
    // Stops/starts/pauses timer based on action state
    // Timer states: 'berlangsung' (running) or other (paused)
});
```

**Event Data Structure:**
```javascript
{
    waktu: <milliseconds>,
    action: 'berlangsung' | 'other'  // determines if timer runs
}
```

### Polling Function
- **Function Name:** `layar.refresh_status_pertandingan()`
- **Interval:** `layar.interval_refresh` (default 1000ms, configurable)
- **Endpoint:** `POST` to `BASE_URL + "layar/refresh-status-pertandingan/" + layar.id_pertandingan`
- **Lines:** 229-276

**What it fetches:**
- `data.pertandingan` — match object (scores, round, status)
- `data.data_nilai` — scoring data from all judges
- `data.verifikasi_pertandingan` — verification/challenge state
- Triggers stinger animation on round change
- Calls `layar.periksa_sistem_dialog()` to handle verification modals

**Fallback:** If socket disconnected, polling also calls `layar.update_timer()` (lines 261-263)

### DOM Update Patterns

**Score Display (Lines 408-410):**
```javascript
$('.skor_merah').html(layar.pertandingan.skor_merah);
$('.skor_biru').html(layar.pertandingan.skor_biru);
$('.ronde_pertandingan').html(layar.ronde_pertandingan);
```

**Timer Display (Lines 44-73):**
- Element: `.stopwatch`
- Uses jQuery Timer plugin with countdown format `%M:%S`
- States: running / paused / removed

**Judge Indicator Highlights (Lines 330-399):**
- Classes: `.juri-{id_perangkat_pertandingan}-{sudut}-indikator`
- Colors: `bg-gradient-180-red` / `bg-gradient-180-blue` / `bg-gradient-180-gray-dark`
- Icons: `.icon-pukulan` / `.icon-tendangan` / `.icon-jatuhan` / `.icon-hukuman` / `.icon-pukulan-inverted` / `.icon-tendangan-inverted`
- Penalty highlights (lines 472-555):
  - `.indikator-pelanggaran-{sudut}` with nested `.indikator-binaan`, `.indikator-teguran`, `.indikator-peringatan`

**Verification Modals (Lines 166-226):**
- `#modalVerifikasiJatuhan` — drop verification
- `#modalVerifikasiPelanggaran` — violation verification
- `#modalHasilVerifikasi` — verification result

### Config Object Structure (layar object)
```javascript
{
    interval_refresh: 1000,           // polling interval
    data_nilai: null,                 // judge scoring data
    pertandingan: null,               // match object
    id_pertandingan: null,            // match ID (extracted from pertandingan)
    ronde_pertandingan: null,         // current round
    verifikasi_pertandingan: null,    // verification state
    waktu_sekarang: 1,                // current time in ms
    waktu_per_ronde: null,            // seconds per round
    stopwatch: null,                  // jQuery reference to .stopwatch
    ringkasan_nilai: null,            // summary scores (parsed JSON)
    skor_biru_verifikasi: 0,          // verification scores
    skor_merah_verifikasi: 0,
    sistema_dialog_terdahulu: null,   // previous dialog state
    socket: null,                     // Socket.IO instance
    modalVerifikasiJatuhan: null,     // Bootstrap modals
    modalVerifikasiPelanggaran: null,
    modalHasilVerifikasi: null
}
```

**Key Variables from Server (set in `set_variable()`):**
- `pertandingan.id_pertandingan`
- `pertandingan.waktu_per_ronde`
- `pertandingan.ronde_pertandingan`
- `pertandingan.ringkasan_nilai` (string or object — JSON parsed if string)
- `pertandingan.data_waktu` — object keyed by round: `{ "1": [total, remaining], "2": [...], "3": [...] }`
- `pertandingan.skor_merah` / `.skor_biru`

---

## FILE 2: `layar_seni_persilat.js`
**Purpose:** Display screen for Seni (artistic) performances  
**Type:** Display/Read-only (no scoring input)

### Socket Initialization
```javascript
// Lines 24-27
if (typeof io !== 'undefined' && typeof SOCKET_URL !== 'undefined') {
    layar.socket = io(SOCKET_URL, { reconnection: true, reconnectionDelay: 1000 });
    
    layar.socket.emit('JOIN_ROOM', layar.id_penampilan_seni);
}
```

**Key Details:**
- Room join uses: `JOIN_ROOM` event with `layar.id_penampilan_seni` (performance ID)
- Reconnection enabled with 1000ms delay

### Existing Socket Event Handlers
```javascript
// Lines 29-55: UPDATE_WAKTU (timer sync from server)
socket.on('UPDATE_WAKTU', function (data) {
    var waktu = data.waktu;
    var seconds = Math.floor(waktu / 1000);
    
    if (data.action === 'sedang_tampil') {
        // Count-up timer (not countdown)
        layar.stopwatch.timer({
            format: "%M:%S",
            seconds: 0,
            duration: seconds,
            countdown: false,
            action: 'start'
        });
    } else {
        // Paused or stopped
        layar.stopwatch.timer("remove");
        if (seconds > 0) {
            layar.stopwatch.timer({
                format: "%M:%S",
                seconds: seconds,
                countdown: false
            });
        }
        layar.stopwatch.timer("pause");
    }
});
```

**Event Data Structure:**
```javascript
{
    waktu: <milliseconds>,
    action: 'sedang_tampil' | 'other'  // determines if timer runs
}
```

**Difference from Tanding:** Uses count-up (`countdown: false`) instead of countdown

### Polling Function
- **Function Name:** `layar.refresh_status_seni()`
- **Interval:** `layar.interval_refresh` (default 1000ms)
- **Endpoint:** `POST` to `BASE_URL + "layar/refresh-status-seni/" + layar.id_penampilan_seni`
- **Lines:** 104-154

**What it fetches:**
- `data.penampilan_seni_berlangsung` — performance object (status, time, format)
- `data.data_nilai` — judge scoring data
- Checks for format/juri count changes → triggers reload if changed
- Socket fallback: if socket disconnected, calls `layar.update_timer()` (lines 142-145)

**Server Response Handling:**
- `data.reload === true` → full page reload
- `data.hasil_pool_seni === true` → redirect to pool results
- `data.hasil_battle_seni === true` → redirect to battle results

### DOM Update Patterns

**Timer Display (Lines 29-54, 71-102):**
- Element: `.waktu_tampil`
- Format: `%M:%S` (count-up, not countdown)
- Action state: `'sedang_tampil'` to run, other states pause

**Judge Scores (Lines 187-257):**
- Container: `.urutan_total_nilai_juri .kolom_total_nilai`
- Score element: `.nilai-juri`
- Judge label: `.label-juri`
- Highlighting: Classes `.terpilih` (selected) vs `.tidak-terpilih` (not selected)
- Sorted ascending by score (lowest displayed first)

**Summary Boxes (Lines 263-294):**
- `.median_kebenaran` — accuracy/correctness median
- `.standar_deviasi` — standard deviation (6 decimal places)
- `.median` — overall median
- `.hukuman` — penalties (displayed as negative)
- `.nilai_akhir` — final score

### Config Object Structure (layar object)
```javascript
{
    penampilan_seni_berlangsung: null,  // performance object
    data_nilai: null,                   // judge scoring data: keyed by id_penampilan_seni
    id_penampilan_seni: null,           // performance ID
    stopwatch: null,                    // jQuery reference to .waktu_tampil
    socket: null,                       // Socket.IO instance
    format_penilaian: null,             // scoring format (used to detect changes)
    interval_refresh: 1000              // polling interval
}
```

**Key Variables from Server:**
- `penampilan_seni_berlangsung.id_penampilan_seni`
- `penampilan_seni_berlangsung.status_penampilan` — 'sedang_tampil' or other
- `penampilan_seni_berlangsung.waktu_tampil` — integer seconds remaining
- `penampilan_seni_berlangsung.format_penilaian` — format used (changes trigger reload)
- `penampilan_seni_berlangsung.catatan_nilai_sama` — JSON with median/std_dev/hukuman
- `penampilan_seni_berlangsung.nilai_akhir` — final calculated score
- `data_nilai[id_penampilan_seni]` — array of judge objects with `penilaian` (JSON) and `terpilih` flag

---

## FILE 3: `juri_tanding_persilat.js`
**Purpose:** Judge input interface for Tanding (competitive fighting)  
**Type:** Interactive (scoring input + verification responses)

### Socket Initialization
```javascript
// Lines 418-422
init_socket() {
    if (typeof io === 'undefined') return;
    
    const socket = io(window.REALTIME_URL || 'http://localhost:3000');
    socket.emit('JOIN_ROOM', { id_pertandingan: juri.id_pertandingan });
}
```

**Key Details:**
- Socket URL from: `window.REALTIME_URL` or fallback to `'http://localhost:3000'`
- Room join uses: `JOIN_ROOM` event with object `{ id_pertandingan: <match_id> }`
- No reconnection config (uses default)

### Existing Socket Event Handlers

#### 1. NILAI_UPDATE (Lines 424-434)
```javascript
socket.on('NILAI_UPDATE', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        if (data.skor_merah !== undefined) {
            juri.pertandingan.skor_merah = data.skor_merah;
        }
        if (data.skor_biru !== undefined) {
            juri.pertandingan.skor_biru = data.skor_biru;
        }
        juri.update_tampilan_nilai();
    }
});
```

**Event Data Structure:**
```javascript
{
    id_pertandingan: <match_id>,
    skor_merah: <integer>,     // optional
    skor_biru: <integer>       // optional
}
```

#### 2. VERIFIKASI_JATUHAN (Lines 436-446)
```javascript
socket.on('VERIFIKASI_JATUHAN', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        juri.verifikasi_pertandingan = {
            jenis_verifikasi: 'jatuhan',
            status: 'berlangsung'
        };
        juri.jawaban_verifikasi_pertandingan = null;
        juri.periksa_sistem_dialog();
    }
});
```

**Event Data Structure:**
```javascript
{
    id_pertandingan: <match_id>
    // Data only signals event; verifikasi object created client-side
}
```

#### 3. VERIFIKASI_PELANGGARAN (Lines 448-457)
```javascript
socket.on('VERIFIKASI_PELANGGARAN', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        juri.verifikasi_pertandingan = {
            jenis_verifikasi: 'pelanggaran',
            status: 'berlangsung'
        };
        juri.jawaban_verifikasi_pertandingan = null;
        juri.periksa_sistem_dialog();
    }
});
```

**Event Data Structure:** Same as VERIFIKASI_JATUHAN

#### 4. MATCH_STATUS_CHANGE (Lines 459-463)
```javascript
socket.on('MATCH_STATUS_CHANGE', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        window.location.reload();
    }
});
```

**Behavior:** Triggers full page reload (used for babak/stage transitions)

#### 5. KONTROL_WAKTU (Lines 465-472)
```javascript
socket.on('KONTROL_WAKTU', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        if (data.ronde_pertandingan) {
            juri.ronde_pertandingan = data.ronde_pertandingan;
            juri.set_ronde();
        }
    }
});
```

**Event Data Structure:**
```javascript
{
    id_pertandingan: <match_id>,
    ronde_pertandingan: <round_number>  // optional
}
```

**Usage:** Updates current round highlight on UI

### Polling Function
- **Function Name:** `juri.refresh_status_pertandingan()`
- **Interval:** 3000ms (3 seconds) — **Lines 362-406**
- **Endpoint:** `POST` to endpoint from `document.getElementById('juri-wrapper').dataset.endpointRefresh`
- **Method:** Fetch API with CSRF token

**What it fetches:**
- `data.data_nilai` — judge scoring data
- `data.pertandingan` — match object
- `data.pemenang` — winner info
- `data.verifikasi_pertandingan` — verification state
- `data.jawaban_verifikasi_pertandingan` — judge's verification answer

**Server Response Handling:**
- `data.reload === true` → full page reload
- Updates all local state variables
- Calls `juri.periksa_sistem_dialog()` to manage modals

### DOM Update Patterns

**Score Display (Lines 154-184):**
- Classes: `.{sudut}-ronde-{ronde}-nilai` (score spans container)
- Total per round: `.{sudut}-ronde-{ronde}-total`
- Display format: Each score entry as inline `<span>` with value
- Soft-deleted entries: `text-decoration-line-through` + strikethrough styling
- Unverified entries (status='input'): grayed out strikethrough
- Verified entries: normal display

**Final Score Display (Lines 162-184):**
- Elements: `#total_nilai_akhir_biru` / `#total_nilai_akhir_merah`
- Highlighting: Winner gets `bg-gradient-180-blue` or `bg-gradient-180-red`, loser gets `bg-gradient-180-gray-dark`

**Round Highlight (Lines 188-191):**
- Classes: `td.ronde-1`, `td.ronde-2`, `td.ronde-3`
- Active round gets: `bg-warning` class

**Modals:**
- `#modalVerifikasiJatuhan` — drop verification
- `#modalVerifikasiPelanggaran` — violation verification

### Config Object Structure (juri object)
```javascript
{
    data_nilai: null,                           // judge's scoring data
    data_waktu: null,                           // parsed match.data_waktu JSON
    waktu_sekarang: null,                       // current match time (ms)
    pertandingan: null,                         // match object
    id_pertandingan: null,                      // match ID
    ronde_pertandingan: null,                   // current round
    pemenang: null,                             // winner info
    verifikasi_pertandingan: null,              // verification state object
    jawaban_verifikasi_pertandingan: null,      // judge's verification answer
    totalRonde: 3,                              // number of rounds
    modalVerifikasiJatuhan: null,               // Bootstrap modal
    modalVerifikasiPelanggaran: null,           // Bootstrap modal
    csrfName: null,                             // CSRF token name
    csrfHash: null                              // CSRF token value
}
```

**Key Variables from Server/DOM:**
- `JURI_INIT` global object passed on init with: `dataNilai`, `pertandingan`, `pemenang`, `verifikasiPertandingan`, `jawabanVerifikasi`, `totalRonde`
- `#juri-wrapper` element carries: `data-csrfName`, `data-csrfHash`, `data-endpointEdit`, `data-endpointRefresh`, `data-endpointVerifikasi`
- `pertandingan.data_waktu` — JSON object by round: `{ "1": [total_ms, remaining_ms], ... }`
- `pertandingan.ronde_pertandingan` — current round number
- `pertandingan.skor_biru` / `.skor_merah` — match scores

---

## FILE 4: `juri_seni_persilat.js`
**Purpose:** Judge input interface for Seni (artistic performances)  
**Type:** Interactive (scoring input, technical + penalties, ready toggle)

### Socket Initialization
**Status:** NO SOCKET.IO CODE — Polling only

- Uses HTTP polling via `juri.refresh_status_seni()` every 2000ms
- No real-time events except offline localStorage fallback
- Purely HTTP-based with localStorage offline caching

### Existing Socket Event Handlers
**None.** This file has zero socket.io code.

### Polling Function
- **Function Name:** `juri.refresh_status_seni()`
- **Interval:** 2000ms (2 seconds) — **Lines 450-509**
- **Endpoint:** `POST` to `BASE_URL + "juri/refresh-status-seni/" + juri.id_penampilan_seni`
- **Method:** jQuery $.post()

**What it fetches:**
- `data.data_nilai` — judge's performance scoring
- `data.penampilan_seni` — performance object (status, acces_penilaian)
- `data.reload === true` → full page reload
- `data.data_nilai === null` → full page reload

**Update Logic (Lines 472-498):**
- Preserves local technical scoring: `juri.data_nilai.penilaian.unsur_nilai` (kebenaran, penampilan, unsur_nilai keys)
- Takes server-authoritative penalties: `penilaian.hukuman`
- Takes server ringkasan: `penilaian.ringkasan.total_hukuman` and `nilai_akhir`
- Re-calculates local totals: `juri.hitung_total_nilai()`
- Updates UI: `juri.update_tampilan_nilai_akhir()`
- Checks acces state: `juri.update_akses_penilaian(data.penampilan_seni.akses_penilaian)`

**Offline Fallback (Lines 36-56):**
- Checks `localStorage.getItem('offline_seni_' + juri.id_penampilan_seni)`
- If exists, attempts sync on next refresh
- On sync failure, shows offline indicator

### DOM Update Patterns

**Technical Scoring Inputs (Lines 364-404):**
- Kebenaran (correctness) per jurus/rangkaian_gerak:
  - Class: `.kebenaran_{jurus}_{nomor_rg}` (error count input)
  - Displays: `jumlah_kesalahan` value
  - Max attribute: `nilai_maksimal`
- Other unsur_nilai (penampilan, etc.):
  - Class: `.nilai_{key_unsur}` (input field)
  - Displays: `nilai_diperoleh` value

**Summary Display (Lines 395-403):**
- `.total_nilai` — total technical score
- `.total_hukuman` — total penalties
- `.nilai_akhir` — final score (juri mode: input, other: HTML display)

**Pointer Display (Lines 249-260):**
- `.pointer_jurus` — current jurus name
- `.pointer_rangkaian_gerak` — sequence number
- `.pointer_gerakan` — movement within sequence
- `.jumlah_kesalahan_rangkaian_gerak` — error count for current sequence
- `.jumlah_kebenaran_rangkaian_gerak` — correct count for current sequence
- Container highlight: `.container_{jurus}_{rangkaian_gerak}` gets class `juri.kelas_aksen_warna` (e.g., `bg-gradient-180-warning`)

**Access Lock Overlay (Lines 421-444):**
- Overlay ID: `#overlay`
- Shows when `acces_penilaian !== "dibuka"`
- Animation: `slideInDown` / `slideOutUp`
- Text: "Scoring Access Locked"

**Ready Toggle Button (Lines 512-541):**
- Data attribute: `data-status` (0 or 1)
- Styling: `btn-primary` (not ready) → `btn-success` (ready)
- Icon: `.ready-icon` (🔵 or ✅)
- Text: `.ready-text` (READY in both states)

### Config Object Structure (juri object)
```javascript
{
    penampilan_seni: null,              // performance object
    id_penampilan_seni: null,           // performance ID
    data_nilai: null,                   // judge's scoring object
    mode: null,                         // "juri" or other (affects valor display)
    is_offline: false,                  // offline state flag
    button_audio: Audio,                // button click sound
    kelas_aksen_warna: null             // CSS class for highlighting (e.g., "bg-gradient-180-warning")
}
```

**Key Variables from Server:**
- `penampilan_seni.id_penampilan_seni`
- `penampilan_seni.status_penampilan` — determines overlay state
- `penampilan_seni.akces_penilaian` — "dibuka" (open) or other (locked)
- `penampilan_seni.format_penilaian` — format (changes trigger reload)
- `data_nilai.penilaian.unsur_nilai` — object with keys: `kebenaran`, `penampilan`, etc.
  - Each has: `nilai_diperoleh`, `nilai_maksimal`, `nilai_minimal`
  - `kebenaran` also has: `jurus` object with `rangkaian_gerak` array
- `data_nilai.penilaian.hukuman` — penalties (read-only from KP, judge cannot edit)
- `data_nilai.penilaian.ringkasan` — `total_nilai`, `total_hukuman`, `nilai_akhir`, `nilai_minimal`

**Offline Storage Key:** `offline_seni_{id_penampilan_seni}`

---

## Summary Table

| File | Type | Socket | Polling | Interval | Room Join | Key Handlers |
|------|------|--------|---------|----------|-----------|--------------|
| layar_tanding | Display | Yes | Yes | 1s | `id_pertandingan` | UPDATE_WAKTU |
| layar_seni | Display | Yes | Yes | 1s | `id_penampilan_seni` | UPDATE_WAKTU |
| juri_tanding | Input | Yes | Yes | 3s | `{id_pertandingan}` | NILAI_UPDATE, VERIFIKASI_JATUHAN, VERIFIKASI_PELANGGARAN, MATCH_STATUS_CHANGE, KONTROL_WAKTU |
| juri_seni | Input | **No** | Yes | 2s | N/A | N/A |

---

## Key Patterns for New Socket Handlers

### 1. **ID Matching Pattern** (juri_tanding.js)
```javascript
if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
    // Process event
}
```
Always coerce to string for safety. This prevents cross-room interference.

### 2. **Timer Sync Pattern** (layar_tanding.js & layar_seni.js)
```javascript
socket.on('UPDATE_WAKTU', function (data) {
    var waktu = data.waktu;        // milliseconds or seconds
    var seconds = Math.floor(waktu / 1000);
    var action = data.action;       // determines state
    
    // Remove old timer first
    element.timer("remove");
    
    // Then initialize new timer
    if (action === 'running_state') {
        element.timer({
            format: "%M:%S",
            seconds: 0,
            duration: seconds,
            countdown: true/false,   // true for tanding, false for seni
            action: 'start'
        });
    } else {
        element.timer({...});
        element.timer("pause");      // or "remove"
    }
});
```

### 3. **State Update Pattern** (juri_tanding.js)
```javascript
socket.on('EVENT_NAME', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        // Update local state
        juri.someProperty = data.value;
        
        // Re-render UI
        juri.update_tampilan_nilai();
        
        // Trigger modal logic if needed
        juri.periksa_sistem_dialog();
    }
});
```

### 4. **Verification Trigger Pattern** (juri_tanding.js)
```javascript
socket.on('VERIFIKASI_TYPE', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        juri.verifikasi_pertandingan = {
            jenis_verifikasi: 'type_name',
            status: 'berlangsung'
        };
        juri.jawaban_verifikasi_pertandingan = null;  // Reset answer
        juri.periksa_sistem_dialog();  // Trigger modal auto-show
    }
});
```

### 5. **Navigation Trigger Pattern** (juri_tanding.js)
```javascript
socket.on('RELOAD_EVENT', data => {
    if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
        window.location.reload();  // Full page reload
    }
});
```

---

## Missing Socket Handlers (To Be Implemented)

Based on the polling responses and server architecture, the following socket handlers should likely be added:

### For `juri_seni_persilat.js`:
1. **ACCES_PENILAIAN_UPDATE** — notify when scoring locked/unlocked
2. **HUKUMAN_UPDATE** — notify when KP changes penalties
3. **PENAMPILAN_STATUS_CHANGE** — notify when performance status changes (sedang_tampil → selesai)

### For `layar_tanding_persilat.js`:
1. **RONDE_CHANGE** — to animate round transitions without polling
2. **VERIFICATION_COMPLETE** — to close modals when verification done

### For `layar_seni_persilat.js`:
1. **NILAI_AKHIR_UPDATE** — to update final score without polling
2. **PERFORMANCE_STATUS_CHANGE** — state transitions

---

## CSRF Token Pattern

Both juri files use CSRF tokens:

**juri_tanding.js (Lines 236-240):**
```javascript
const body = new URLSearchParams();
body.append(juri.csrfName, juri.csrfHash);
body.append('sudut', sudut);
body.append('entry', JSON.stringify(entry));
```

**juri_seni_persilat.js (Lines 42, 103, 458, 519):**
```javascript
{ data_nilai: offline_data, [CSRF_NAME]: CSRF_HASH }
// or
{ status_ready: newStatus, [CSRF_NAME]: CSRF_HASH }
```

Server responses rotate CSRF tokens:
```javascript
if (data && data.csrf_hash) CSRF_HASH = data.csrf_hash;
```

---

## Critical Implementation Notes

1. **ID Coercion:** Always use `String()` comparison for IDs to avoid type mismatch bugs
2. **Timer Lifecycle:** Always `.timer("remove")` before creating new timer to avoid conflicts
3. **Offline Support:** `juri_seni_persilat.js` has localStorage caching — follow this pattern for other juri screens
4. **Polling Fallback:** Socket disconnects still rely on polling — ensure both mechanisms stay in sync
5. **Modal States:** Use `periksa_sistem_dialog()` in juri_tanding to automatically manage modal visibility
6. **Penalty Read-only:** `juri_seni_persilat.js` shows penalties are KP-only — don't allow judge edits
7. **Reconnection Config:** Consider adding reconnection to juri_tanding socket for robustness
8. **Error Handling:** Current code lacks error handlers on socket.on — should add `.catch()` or error events
