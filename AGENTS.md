# Project Instructions for Hermes/Codex

## Project Context

- This repository (`dps-scoringsystem`) is the CodeIgniter 4 migration target for the
  **scoring / match-officiating module ("Penilaian Pertandingan" / "Perangkat Pertandingan")**
  of the legacy DPS (Digital Pencak Silat) project.
- The legacy parity/reference project is located at `/Applications/XAMPP/xamppfiles/htdocs/dps`
  (CodeIgniter 3.1.x).
- The scope of this migration is **only the match-scoring subsystem**, not the whole DPS app.
  The rest of DPS (registration, scheduling, brackets, reporting) stays in the legacy app or
  other migration targets unless explicitly stated.
- Goal of this migration: **better performance and rock-solid reliability**. Scoring is the
  most critical, least-forgiving part of the system — an error during a live match is not
  acceptable. Treat correctness, real-time consistency, and minimal error surface as the
  top priorities, above cosmetic improvements.

## Legacy Module Map (parity reference)

The scoring subsystem in the legacy project lives mainly under these paths. Always inspect
them before editing the CI4 equivalent:

- Controllers: `application/controllers/pertandingan/`
  - `Juri.php` — judge scoring input (tanding & seni)
  - `Ketua_pertandingan.php` — head referee controls/decisions
  - `Sekretaris_pertandingan.php` — match secretary (timer, match flow, result recording; largest file ~1200 LOC)
  - `Layar.php` — scoreboard display screen
  - `Broadcast_operator.php` — broadcast/overlay operator
- Models: `application/models/pertandingan/` and `application/models/sistem_penilaian/`
  - `sistem_penilaian/tanding/` — `PERSILAT_model`, `IPSI_2012_model`, `Tapak_Suci_model`
  - `sistem_penilaian/seni/` — `PERSILAT_model`, `IPSI_2012_model`, `Tapak_Suci_model`, `FPSTI_model`, `Festival_model`
  - `Scoreboard_model.php`
- Views: `application/views/pertandingan/` (`juri/`, `ketua_pertandingan/`, `sekretaris/`,
  `layar/`, `broadcast_operator/`, plus `components/` and `shared_components/`)
- Real-time: `realtime-server/` — standalone Node.js service (Express 5 + socket.io 4) on port 3000.
  - Rooms keyed by `id_pertandingan`; events include `JOIN_ROOM`, `KONTROL_WAKTU`, `UPDATE_WAKTU`.
  - PHP side talks to it via `application/helpers/websocket_helper.php`.

Scoring rules differ per **sistem penilaian** (PERSILAT, IPSI 2012, Tapak Suci, FPSTI, Festival)
and per **mode** (tanding vs seni). Each has distinct scoring math, validation, and result
aggregation. Never assume one system's rules apply to another — verify against the matching
legacy model.

## Mandatory Workflow for Migration Requests

When the user asks to migrate, rebuild, fix, or improve any part of the scoring module, always
follow this workflow:

1. Understand the legacy flow deeply before editing.
   - Inspect the relevant legacy controller, model, view, routes, helpers, JavaScript, and
     database usage in `/Applications/XAMPP/xamppfiles/htdocs/dps`.
   - For scoring, this includes: who can input scores (role/session), the exact scoring math,
     how partial/judge scores aggregate into a final result, tie-breaking rules, disqualification
     handling, timer/round control, and how results are persisted.
   - Trace the **real-time path**: which socket events fire, what payload they carry, which room
     they target, and how Juri/Sekretaris/Layar stay in sync.
   - Identify request methods, redirects, flash messages, validation rules, session/auth
     assumptions, and edge cases.

2. Map the feature into CodeIgniter 4 architecture.
   - Request handling in CI4 controllers under `app/Controllers`.
   - Database access in CI4 models under `app/Models`; put non-trivial scoring logic in
     dedicated service classes under `app/Services` (one per sistem penilaian / mode where it
     keeps logic clean and testable).
   - Presentation in `app/Views`, following the existing project layout and shared components.
   - Register or adjust routes in `app/Config/Routes.php` using the existing route style.
   - Keep the real-time server (`realtime-server/`) compatible: reuse the same socket event
     contract and room model unless the user explicitly approves a protocol change. If the
     contract changes, update both the Node server and every PHP/JS client together.

3. Preserve parity first, then improve.
   - Match the legacy scoring rules, math, and recorded results **exactly** before changing
     anything else. When in doubt, reproduce legacy output and diff against it.
   - Keep compatibility with the existing DPS database schema (`db_sudinpora`) unless the user
     explicitly asks for schema changes.
   - Maintain existing role/session constraints (e.g. Juri, Ketua, Sekretaris, Super_admin) and
     access behavior.
   - After parity is confirmed, improve code structure, query efficiency, validation, and UI/UX.

4. Optimize queries and data access.
   - Avoid N+1 queries — judge scores and round data are read frequently during a match.
   - Select only required columns where practical.
   - Use joins, grouping, or aggregate queries when they reduce repeated calls.
   - Keep query conditions explicit and readable.
   - Prefer model/service methods over raw query duplication in controllers or views.
   - Most legacy `db_sudinpora` tables have **no `created_at`/`updated_at` columns** — set
     `protected $useTimestamps = false` in CI4 models targeting them, or INSERT/UPDATE fails
     with "Unknown column 'created_at'".

5. Real-time correctness and reliability (scoring-specific).
   - Treat the live-scoring path as safety-critical. Guard against double-submission, out-of-order
     events, dropped connections, and stale state.
   - Make scoring writes idempotent where feasible and validate server-side, never trust the
     client payload alone.
   - Keep the scoreboard (Layar) and officials' screens eventually consistent; on reconnect, a
     client must be able to recover the authoritative current state.
   - Verify timer/round control behaves identically to legacy under pause/resume/reset.

6. Improve UI/UX intentionally.
   - Treat legacy views as the reference for content, fields, actions, and user flow.
   - Modernize layout, spacing, hierarchy, forms, tables, empty/loading/error states, and
     responsive behavior — but never at the cost of input speed or clarity for officials during
     a live match. Scoring screens must stay fast, unambiguous, and hard to misclick.
   - Preserve the existing CI4 project visual language unless the user requests a redesign.
   - Use Bootstrap 5, DataTables, Toastr, SweetAlert2, and existing shared components consistently
     when already used in the module.

7. Validate the implementation.
   - Run relevant PHP syntax checks (`php -l`) for changed PHP files.
   - Write/run targeted tests for scoring math and aggregation — this is the part that must be
     provably correct. Use PHPUnit (already in `vendor/bin/phpunit`).
   - Manually verify the real-time flow end-to-end (Juri input → Sekretaris → Layar) when
     practical, including reconnect behavior.
   - Check routes, controller methods, view variables, form actions, CSRF expectations, redirects,
     and uploaded-file paths.
   - If full validation is not possible, clearly state what was checked and what still needs
     manual testing.

8. Protect existing work.
   - Do not revert unrelated user changes.
   - Before changing files that already have modifications, inspect them carefully and preserve
     unrelated edits.
   - Avoid destructive git commands unless explicitly requested by the user.

## UI Theme & CSS Baseline (ikut `htdocs/dps-ci4`)

Project ini **mewarisi tema visual dari `/Applications/XAMPP/xamppfiles/htdocs/dps-ci4`**.
Jadikan itu sebagai dasar/baseline, lalu sesuaikan per kebutuhan halaman penilaian
(scoreboard, juri, ketua, sekretaris). Jangan membuat tema dari nol — selaraskan dengan
sistem desain dps-ci4 berikut, baru lakukan penyesuaian khusus device penilaian.

### Stack pihak ketiga (via CDN helper `online_asset()`)
dps-ci4 memusatkan URL CDN di helper `online_asset(key)` (lihat
`app/Helpers/ci3_compat_helper.php`). Replikasi pola yang sama di project ini:
- Bootstrap **5.3.3** (CSS + bundle JS)
- jQuery **3.7.1**
- DataTables **1.13.8** (Bootstrap5) + Responsive 2.5.0 + Buttons 2.4.2 + JSZip
- Font Awesome **6.5.2** (ikon utama; tersedia juga FA 4.7.0 untuk legacy)
- Toastr (notifikasi) + SweetAlert2 **11** (konfirmasi)
- Select2 4.1.0-rc.0 (+ theme Bootstrap 5) bila perlu dropdown kaya
File project-spesifik/custom tetap lokal di `public/assets/`. Hanya library publik yang
lewat CDN.

### Font
- Body: **Poppins** (300–700). Display/heading kuat: **Oswald** (500–700).
- Dimuat via Google Fonts (`preconnect` + `css2?family=Oswald...&family=Poppins...`).

### Design tokens (CSS `:root`, dari `public/assets/css/admin/admin.css`)
Pakai variabel CSS yang sama supaya konsisten:
```
--brand-primary:  #c60000;   /* merah DPS (aksen utama, border sidebar) */
--brand-secondary:#c5a017;   /* emas */
--brand-dark:     #212529;
--corner-red:     #c62828;   /* SUDUT MERAH — relevan untuk scoreboard penilaian */
--corner-blue:    #1565c0;   /* SUDUT BIRU  — relevan untuk scoreboard penilaian */
--bg-color:       #f4f6f9;
```
Token `--corner-red`/`--corner-blue` sudah disiapkan untuk warna sudut atlet — gunakan ini
untuk papan skor merah/biru agar konsisten lintas modul.

### Pola layout & komponen
- Layout via CI4 view layout: `extend('layouts/admin')` + `renderSection('content')`.
  Buat layout khusus penilaian (mis. `layouts/penilaian.php`) yang meminjam baseline ini
  namun dioptimasi untuk device scoring (fullscreen, kontras tinggi, tombol besar).
- Custom CSS modul ditaruh lokal di `public/assets/css/...` (ikuti pola `admin/admin.css`).
- Komponen UI yang sudah dipakai dps-ci4 dan harus dipakai konsisten: Bootstrap 5,
  DataTables (init via helper `initAdminDataTable`), Toastr (flashdata → toast),
  SweetAlert2 (konfirmasi aksi).
- Aksi baris tabel: tombol/dropdown **`Aksi`** merah pill-style (bukan ikon-only) —
  konsisten dengan preferensi UI admin DPS.

### Aset penilaian yang sudah ada di dps-ci4 (bisa direferensikan/disalin)
- Format penilaian JSON: `public/assets/penilaian/format-penilaian/tanding/persilat.json`
  (dan varian seni). Ini sumber struktur unsur nilai/hukuman — pakai sebagai referensi
  format, bukan menebak.
- Folder `public/assets/sound/` untuk efek suara timer/scoring.

> Catatan: tema ini adalah **dasar**. Untuk layar device penilaian (juri/layar) nantinya
> disesuaikan (mode gelap/terang, kontras tinggi, ukuran besar) di atas baseline ini,
> bukan menggantikannya.

## Environment Notes

- Stack: XAMPP on macOS, MariaDB `db_sudinpora`. MySQL CLI:
  `/Applications/XAMPP/xamppfiles/bin/mysql -u root` (no password).
- Real-time server: `cd realtime-server && npm install && node server.js` (port 3000).
- New PHP files created via tooling sometimes land as mode 600 → Apache HTTP 500
  "Failed opening required". `chmod 755` new files and folders.

## Expected Response Style

For each completed migration/improvement request, respond in Indonesian and include:

- What legacy flow was reviewed (controller/model/view + real-time path).
- What CI4 files were changed.
- What query or architecture improvements were made.
- What scoring-correctness / reliability safeguards were added or verified.
- What UI/UX improvements were made.
- What validation/testing was performed.
- Any remaining manual QA steps or risks.

Keep responses concise but specific, with clickable file paths where relevant.
