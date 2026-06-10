# Rencana Migrasi Sistem Penilaian Pertandingan (PERSILAT) — DPS CI3 → CI4

> **Dokumen ini adalah rencana panjang (master plan) migrasi modul Penilaian / Perangkat
> Pertandingan dari project legacy `htdocs/dps` (CodeIgniter 3.1.x) ke project baru
> `htdocs/dps-scoringsystem` (CodeIgniter 4.7, PHP 8.2).**
>
> **Scope format penilaian: HANYA PERSILAT.** Sistem IPSI 2012, Tapak Suci, FPSTI, dan
> Festival TIDAK termasuk dalam scope ini dan tidak akan dimigrasikan, namun arsitektur
> harus tetap memungkinkan penambahan sistem lain di masa depan tanpa refactor besar.
>
> Prioritas utama: **kebenaran perhitungan (correctness), konsistensi real-time, dan
> minimnya error saat pertandingan live.** Penilaian adalah bagian paling kritikal —
> kesalahan saat pertandingan berlangsung tidak dapat ditoleransi.

---

## 1. Ringkasan & Tujuan

### 1.1 Latar Belakang
Modul penilaian legacy adalah subsistem "digital scoring" yang melibatkan banyak perangkat
(device) yang harus tersinkronisasi secara real-time di satu gelanggang:

- **Juri** — input nilai (serangan, jatuhan) per sudut (merah/biru) per ronde.
- **Ketua Pertandingan** — kontrol verifikasi, hukuman/teguran/peringatan/binaan, keputusan.
- **Sekretaris Pertandingan** — kontrol timer, alur partai, ronde, hasil akhir.
- **Layar** — papan skor (scoreboard) untuk penonton/peserta.
- **Broadcast Operator** — overlay grafis untuk siaran.

Sinkronisasi real-time saat ini ditangani oleh **server Node.js terpisah** (`realtime-server/`,
Express 5 + Socket.IO 4, port 3000) dengan model "room per `id_pertandingan`".

### 1.2 Tujuan Migrasi
1. **Parity penuh** dengan perilaku PERSILAT legacy (matematika nilai, verifikasi, hukuman,
   pemilihan pemenang) — hasil harus identik.
2. **Performa lebih baik** — hilangkan N+1 query, kurangi decode/encode JSON berulang,
   gunakan transaksi & locking yang benar.
3. **Keandalan real-time** — anti double-submit, anti out-of-order, recovery state saat
   reconnect, server-side validation.
4. **Struktur kode CI4 yang bersih & teruji** — logika penilaian berada di Service class
   yang dapat di-unit-test, bukan tersebar di controller/view/JS.
5. **UI/UX modern** tanpa mengorbankan kecepatan & kejelasan input saat live.

### 1.3 Non-Tujuan (Out of Scope)
- Sistem penilaian selain PERSILAT.
- Migrasi modul pendaftaran, penjadwalan, bagan, laporan (kecuali tabel/relasi yang
  dibutuhkan untuk membaca data pertandingan).
- Perubahan skema database `db_sudinpora` (kecuali disetujui eksplisit oleh user).

---

## 2. Pemetaan Arsitektur Legacy (Referensi Parity)

### 2.1 Controllers Legacy (`application/controllers/pertandingan/`)
| File | LOC | Peran |
|------|-----|-------|
| `Juri.php` | 421 | Input nilai juri (tanding & seni), verifikasi pertandingan, refresh status |
| `Ketua_pertandingan.php` | 456 | Kontrol nilai, hukuman, diskualifikasi, daftar nilai |
| `Sekretaris_pertandingan.php` | 1210 | Timer, alur partai/ronde, ganti format, hasil akhir, edit atlet |
| `Layar.php` | 453 | Scoreboard tanding & seni, standby, hasil battle/pool |
| `Broadcast_operator.php` | 70 | Overlay grafis broadcast (tanding & seni) |
| `Perangkat_pertandingan.php` | 342 | Login/landing perangkat, routing peran |

### 2.2 Models Legacy
- `models/sistem_penilaian/tanding/PERSILAT_model.php` (**1046 LOC**) — **inti algoritma
  tanding PERSILAT**. Fungsi kunci:
  - `proses_penilaian_juri_incremental()` — tambah/hapus entry nilai juri (dengan row lock `FOR UPDATE`, soft-delete `is_deleted`).
  - `proses_penilaian_kp()` — Ketua input hukuman/jatuhan/binaan/teguran/peringatan.
  - `hitung_skor_atlet()` — pipeline: reset metadata → verifikasi → warna → ringkasan → simpan.
  - `_verifikasi_penilaian()` — verifikasi nilai sama antar ≥2 juri dalam interval waktu (`interval_verifikasi = 2` detik).
  - `_get_ringkasan_nilai()`, `_hitung_ringkasan_nilai_per_juri()`, `_simpan_nilai_akhir()`.
- `models/sistem_penilaian/seni/PERSILAT_model.php` (501 LOC) — algoritma seni PERSILAT
  (median, standar deviasi, hukuman konsisten antar juri, pemilihan juri terpilih).
- `models/pertandingan/`: `Juri_model`, `Ketua_pertandingan_model`,
  `Sekretaris_pertandingan_model`, `Layar_model`, `Broadcast_operator_model`.
- `models/Scoreboard_model.php`.
- Model resource pendukung: `Pertandingan_model`, `Penilaian_tanding_model`,
  `Penilaian_seni_model`, `Penampilan_seni_model`, `Verifikasi_pertandingan_model`,
  `Jawaban_verifikasi_pertandingan_model`, `Detail_jadwal_tanding_model`,
  `Detail_jadwal_seni_model`, dll.

### 2.3 Views Legacy (`application/views/pertandingan/`)
- `juri/tanding/persilat/` → `controller.php`, `dark.php`, `light.php`, +
  `components/verifikasi_jatuhan.php`, `components/verifikasi_pelanggaran.php`.
- `ketua_pertandingan/tanding/persilat/` → `button_controller_dark_page.php`, dll.
- `sekretaris/` → `timer_tanding.php`, `timer_tandingv2.php`, banyak komponen
  (`components/timer_tanding/header/persilat.php`, `navigasi_partai/persilat.php`,
  modal ubah waktu, keputusan pemenang, dll).
- `layar/` → scoreboard tanding/seni + `template.php` (klien Socket.IO).
- Shared: `template.php`, `components/`, `shared_components/`, `login.php`, `standby.php`.

### 2.4 Real-time Server (`realtime-server/server.js`)
- Express 5 + Socket.IO 4, listen port **3000**, `cors: { origin: "*" }`.
- Event contract saat ini (minimal):
  - `JOIN_ROOM` (payload: `id_pertandingan`) → `socket.join(String(id_pertandingan))`.
  - `KONTROL_WAKTU` (payload: `{ id_pertandingan, ... }`) → broadcast `UPDATE_WAKTU` ke room.
  - `disconnect`.
- **Catatan penting**: server saat ini HANYA me-relay timer. Sinkronisasi nilai/skor di
  legacy sebagian besar dilakukan via **AJAX polling** (`refresh_status_pertandingan`,
  `refresh_status_seni`) ke controller PHP, bukan via socket. Ini titik perbaikan utama
  untuk keandalan & performa di CI4.

### 2.5 Routes Legacy (`config/routes/pertandingan.php`)
Slug bersih: `perangkat-pertandingan`, `juri`, `ketua-pertandingan`,
`sekretaris-pertandingan`, `broadcast-operator`, `layar` — masing-masing dengan pola
`(:any)/(:any)`. Pola ini harus dipertahankan agar URL device tidak berubah.

---

## 3. Skema Database (Parity — `db_sudinpora`, JANGAN diubah)

> Mayoritas tabel **tidak punya** `created_at`/`updated_at`. Semua model CI4 yang menarget
> tabel ini WAJIB `protected $useTimestamps = false;`.

### 3.1 `pertandingan` (1 partai tanding)
`id_pertandingan` (PK), `nomor_pertandingan`, `nomor_pertandingan_selanjutnya`,
`id_kompetisi_tanding`, `id_atlet_merah`, `id_atlet_biru`, `id_pemenang`,
`id_official_merah`, `id_official_biru`, `babak` (enum 1/32 … final),
`ronde_pertandingan` (enum '1','2','3'), `skor_merah`, `skor_biru`,
`ringkasan_nilai` (text/JSON), `keterangan`, `data_waktu` (text/JSON),
`jenis_kemenangan` (enum Teknik/BYE/Mutlak/Poin/Diskualifikasi/…),
`status_pertandingan` (enum belum_dimulai/berlangsung/selesai/berhenti/standby/istirahat),
`berat_*`, `hasil_timbang_*`, `ttd_*`, `keterangan_penimbangan`, `waktu_penimbangan`.

### 3.2 `penilaian_tanding` (nilai per juri per partai)
`id_penilaian_tanding` (PK), `id_pertandingan`, `id_perangkat_pertandingan`,
`penilaian_merah` (text/JSON), `penilaian_biru` (text/JSON), `pemenang`.

**Struktur JSON `penilaian_merah`/`penilaian_biru`** (hasil reverse-engineering dari model):
```
{
  "ronde_pertandingan": {
    "1": {
      "rincian": [
        { "nilai": 1|2|3|-1|-2|-5|-10, "status": "input"|"verified",
          "warna": null|"#hex", "id_nilai": null|int, "tag": false,
          "timestamp": <unix>, "is_deleted": false, "deleted_at": <unix?> }
      ],
      "catatan": { "binaan": 0|1 }
    },
    "2": {...}, "3": {...}
  }
}
```
Nilai positif = serangan (1=tangan, 2=kaki, 3=jatuhan). Nilai negatif = hukuman
(-1/-2 teguran, -5/-10 peringatan). Soft-delete via `is_deleted`+`deleted_at`.

### 3.3 `perangkat_pertandingan` (device & auth)
`id_perangkat_pertandingan` (PK), `id_gelanggang`, `nama`, `username`, `password` (text),
`posisi` (enum juri/timer/ketua_pertandingan/operator/sekretaris/layar/broadcast_operator),
`session_id` (text). **Auth perangkat berbasis tabel ini, bukan tabel user utama.**

### 3.4 `verifikasi_pertandingan` & `jawaban_verifikasi_pertandingan`
Verifikasi jatuhan/pelanggaran: ketua membuka verifikasi, juri menjawab (merah/biru/invalid),
hasil diakumulasi. Kolom `timestamp`, `status` (berlangsung/selesai/batal).

### 3.5 Pendukung (read-only untuk konteks)
`kompetisi_tanding`, `kategori_lomba`, `kategori_usia`, `gelanggang`, `peserta_tanding`,
`pendaftar`, `kontingen`, `jadwal_tanding`, `detail_jadwal_tanding`.

---

## 4. Arsitektur Target CI4

### 4.1 Prinsip
- **Controllers tipis** — hanya orkestrasi request/response, auth filter, validasi input,
  panggil Service.
- **Service layer** memuat logika penilaian (`app/Services/Scoring/...`) — dapat di-unit-test
  tanpa HTTP. Inti PERSILAT (`PersilatTandingService`) adalah port 1:1 dari
  `PERSILAT_model` legacy, lalu dioptimasi setelah parity terbukti.
- **Models (`app/Models`)** — hanya akses data (query builder + entity), tanpa logika bisnis berat.
- **Entities/DTO** — bungkus JSON `penilaian_*` ke value object agar tidak ada
  `json_decode`/`json_encode` berserakan dan rawan typo key.
- **Real-time** — perluas `realtime-server` untuk broadcast event skor (bukan hanya timer),
  dengan kontrak event yang versioned. PHP mengirim event via HTTP→Node bridge atau Node
  membaca dari endpoint authoritative. **State authoritative tetap di DB** (Node hanya relay/cache).

### 4.2 Struktur Folder Target (usulan)
```
app/
  Config/
    Routes.php                      # group 'pertandingan' + slug device
    Database.php                    # koneksi db_sudinpora
  Controllers/Pertandingan/
    PerangkatPertandingan.php
    Juri.php
    KetuaPertandingan.php
    SekretarisPertandingan.php
    Layar.php
    BroadcastOperator.php
  Models/
    PertandinganModel.php
    PenilaianTandingModel.php
    PerangkatPertandinganModel.php
    VerifikasiPertandinganModel.php
    JawabanVerifikasiPertandinganModel.php
    KompetisiTandingModel.php
    (+ resource read-only)
  Entities/
    PenilaianTanding.php            # wrapper JSON merah/biru
    EntryNilai.php
  Services/Scoring/
    Contracts/ScoringSystemInterface.php
    Persilat/PersilatTandingService.php      # port dari PERSILAT_model
    Persilat/PersilatVerifikasiService.php
    ScoringServiceFactory.php                # pilih sistem (saat ini hanya 'persilat')
  Filters/
    PerangkatAuthFilter.php          # auth berbasis perangkat_pertandingan
  Libraries/Realtime/
    RealtimeClient.php               # bridge PHP → Node (emit event)
  Views/pertandingan/
    template.php, login.php, standby.php
    juri/tanding/persilat/ ...
    ketua/tanding/persilat/ ...
    sekretaris/ ...
    layar/ ...
realtime-server/                     # diperluas (event skor + auth room)
tests/
  Services/Scoring/PersilatTandingServiceTest.php   # unit test matematika nilai
docs/
  RENCANA_MIGRASI_PENILAIAN_PERSILAT.md  (dokumen ini)
```

### 4.3 Koneksi Database
- Tambah grup koneksi `db_sudinpora` di `app/Config/Database.php` + `.env`.
- Semua model penilaian pakai `protected $DBGroup = 'default'` (diarahkan ke `db_sudinpora`),
  `useTimestamps = false`, `returnType` ke Entity bila perlu.

---

## 5. Tahapan Migrasi (Fase Bertahap)

> Setiap fase: **(a) baca legacy → (b) port ke CI4 → (c) parity test → (d) optimasi → (e) verifikasi.**
> Tidak lanjut ke fase berikut sebelum parity fase berjalan terbukti.

### FASE 0 — Fondasi & Infrastruktur (prasyarat)
**Tujuan:** project CI4 siap terhubung ke `db_sudinpora` dan punya kerangka modul.
- [ ] Konfigurasi `.env` + `app/Config/Database.php` → koneksi `db_sudinpora` (read/write).
- [ ] Buat `BaseController` modul, layout `Views/pertandingan/template.php`.
- [ ] Setup routing group `pertandingan` + slug device (parity dengan legacy).
- [ ] Buat `PerangkatAuthFilter` (auth berbasis `perangkat_pertandingan.username/password/session_id`).
- [ ] Setup PHPUnit (sudah ada `vendor/bin/phpunit`) + folder `tests/Services/Scoring`.
- [ ] Verifikasi: koneksi DB OK, route device mengembalikan halaman login perangkat.
- **Risiko:** mode file 600 → HTTP 500 (chmod 755 file & folder baru).

### FASE 1 — Autentikasi & Routing Perangkat (`Perangkat_pertandingan`)
**Legacy:** `Perangkat_pertandingan.php` (login device, pilih gelanggang, redirect per posisi).
- [ ] Port login perangkat → controller + filter.
- [ ] Session perangkat (id_perangkat, id_gelanggang, posisi) — parity dengan `_remap` legacy.
- [ ] Halaman standby & landing per posisi.
- [ ] Verifikasi: tiap posisi (juri/ketua/sekretaris/layar/broadcast) login & ter-redirect benar.

### FASE 2 — Service Inti PERSILAT Tanding (paling kritikal)
**Legacy:** `models/sistem_penilaian/tanding/PERSILAT_model.php` (1046 LOC).
**Ini jantung sistem — kerjakan paling hati-hati, test paling ketat.**
- [ ] Port `proses_penilaian_juri_incremental()` → `PersilatTandingService::tambahEntryNilai()`
      / `hapusEntryNilai()` dengan row lock (`FOR UPDATE`) + soft-delete `is_deleted`.
- [ ] Port `hitung_skor_atlet()` pipeline lengkap:
      reset metadata → `_verifikasi_penilaian()` (interval 2 detik, ≥2 juri) →
      `_beri_warna()` → `_hitung_ringkasan_nilai_per_juri()` → `_get_ringkasan_nilai()` →
      simpan `penilaian_*` + `ringkasan_nilai` + `skor_merah`/`skor_biru`.
- [ ] Port `proses_penilaian_kp()` (hukuman/jatuhan/binaan/teguran/peringatan + hapus).
- [ ] Bungkus JSON `penilaian_merah/biru` ke Entity `PenilaianTanding` (hindari decode berulang).
- [ ] **Unit test parity**: siapkan fixture JSON dari data legacy nyata, jalankan service
      CI4, bandingkan `ringkasan_nilai` & `skor_*` byte-for-byte dengan output legacy.
- [ ] Optimasi setelah parity: kurangi pola `decode→modify→encode` berulang dalam loop,
      hindari N+1 saat ambil nilai semua juri.
- **Definition of done:** semua skenario (serangan tangan/kaki/jatuhan, teguran, peringatan,
  binaan, hapus, verifikasi 2 juri, tie) menghasilkan skor identik dengan legacy.

### FASE 3 — Controller & UI Juri (Tanding PERSILAT)
**Legacy:** `Juri.php::tanding()`, `edit_penilaian_tanding()`, `refresh_status_pertandingan()`.
- [ ] Controller Juri: tampilkan partai berlangsung, input nilai (AJAX), refresh status.
- [ ] Port view `juri/tanding/persilat/{dark,light}.php` + `controller.php` (logika tombol).
- [ ] **Anti double-submit**: debounce/lock tombol, idempotency key per entry, validasi server-side.
- [ ] Verifikasi jatuhan/pelanggaran (komponen `verifikasi_jatuhan`/`verifikasi_pelanggaran`).
- [ ] Verifikasi: 3 juri input bersamaan → nilai terverifikasi benar, tidak ada race.

### FASE 4 — Controller & UI Ketua Pertandingan (Tanding PERSILAT)
**Legacy:** `Ketua_pertandingan.php::tanding()`, `button_controller_tanding()`,
`edit_penilaian_tanding()`, `daftar_nilai_tanding()`.
- [ ] Kontrol hukuman/teguran/peringatan/binaan/jatuhan (panggil `proses_penilaian_kp`).
- [ ] Daftar nilai semua juri + verifikasi.
- [ ] Port view ketua persilat (button controller).
- [ ] Verifikasi: input KP konsisten ke semua juri (tidak ada inkonsistensi penalty).

### FASE 5 — Controller & UI Sekretaris (Timer & Alur Partai Tanding)
**Legacy:** `Sekretaris_pertandingan.php` (1210 LOC) — timer, ronde, mulai/pindah partai,
ubah waktu, keputusan pemenang, ganti format, edit atlet.
- [ ] Port `mulai_pertandingan`, `timer_tanding(v2)`, `toggle_timer_tanding`,
      `pindah_ronde_tanding`, `pindah_partai_tanding`, `ubah_waktu_tanding`.
- [ ] Port keputusan pemenang (`jenis_kemenangan`: Poin/Teknik/Mutlak/Diskualifikasi/BYE).
- [ ] Integrasi timer dengan real-time (`KONTROL_WAKTU` → `UPDATE_WAKTU`).
- [ ] Port view `sekretaris/timer_tanding(v2).php` + komponen header/navigasi persilat.
- [ ] Verifikasi: timer sinkron ke Layar, pause/resume/reset identik legacy.

### FASE 6 — Layar (Scoreboard Tanding PERSILAT)
**Legacy:** `Layar.php::tanding()`, `standby_tanding()`, `refresh_status_pertandingan()`.
- [ ] Scoreboard real-time: skor merah/biru, ronde, timer, nama atlet/kontingen.
- [ ] Klien Socket.IO join room `id_pertandingan`, terima `UPDATE_WAKTU` + update skor.
- [ ] **Recovery state**: saat reconnect, klien re-fetch state authoritative dari DB.
- [ ] Verifikasi: layar konsisten dengan input juri/sekretaris secara real-time.

### FASE 7 — Broadcast Operator (Tanding PERSILAT)
**Legacy:** `Broadcast_operator.php` (70 LOC) — overlay grafis.
- [ ] Port overlay tanding + `refresh_broadcast_graphic`.
- [ ] Verifikasi: overlay update real-time.

### FASE 8 — Real-time Hardening & Optimasi Menyeluruh
- [ ] Perluas `realtime-server`: event skor (bukan hanya timer), namespacing/versioning event.
- [ ] Anti out-of-order (sequence number per event), heartbeat/reconnect, room auth.
- [ ] Ganti AJAX polling `refresh_status_*` dengan push event (kurangi beban server).
- [ ] Audit performa: N+1, indeks query, ukuran payload JSON.
- [ ] Load/stress test: simulasi 1 gelanggang penuh (3 juri + ketua + sekretaris + layar).

### FASE 9 — QA End-to-End & Cutover
- [ ] Skenario lengkap satu partai dari mulai → selesai → pemenang, bandingkan dgn legacy.
- [ ] Uji edge case: koneksi putus saat input, double submit, refresh tengah ronde, BYE,
      diskualifikasi, seri/median.
- [ ] Checklist parity final ditandatangani.
- [ ] Rencana rollback & dokumentasi operasional (cara start realtime-server, dll).

---

## 6. Strategi Keandalan & Kebenaran (Wajib)

### 6.1 Kebenaran Perhitungan
- **Port 1:1 dulu, optimasi belakangan.** Jangan "memperbaiki" logika legacy sebelum parity
  terbukti — perbedaan kecil (pembulatan, urutan median) dapat mengubah pemenang.
- **Unit test berbasis fixture nyata**: ambil snapshot `penilaian_tanding` dari DB legacy,
  jalankan service CI4, assert `ringkasan_nilai`/`skor_*` identik.
- Pertahankan konstanta legacy: `interval_verifikasi = 2` detik, aturan verifikasi ≥2 juri,
  soft-delete (bukan hard delete).

### 6.2 Keandalan Real-time / Concurrency
- **Row locking** (`FOR UPDATE`) + transaksi pada setiap write nilai (sudah ada di legacy —
  pertahankan).
- **Idempotency** pada submit nilai juri (cegah double-count saat retry/klik ganda).
- **Validasi server-side**: jangan percaya payload klien; nilai hanya boleh dari himpunan
  legal (1/2/3/-1/-2/-5/-10/binaan).
- **Recovery**: state authoritative = DB. Socket hanya transport. Reconnect → re-sync dari DB.
- **Anti out-of-order**: sertakan timestamp/sequence; server yang menentukan urutan final.

### 6.3 Keamanan
- Auth perangkat via `PerangkatAuthFilter`; tiap posisi hanya akses endpoint miliknya.
- CSRF aktif untuk semua POST (CI4 default) — sesuaikan klien AJAX (`X-CSRF-TOKEN`).
- `realtime-server` `cors: origin:"*"` → batasi ke origin gelanggang; tambah auth join room.

---

## 7. Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|----------|
| Perbedaan halus matematika nilai | Pemenang salah | Port 1:1 + unit test parity fixture nyata |
| Race condition multi-juri | Skor inkonsisten | Row lock + transaksi + idempotency (pertahankan pola legacy) |
| Polling AJAX berat | Server lambat saat live | Migrasi ke push event di Fase 8 |
| File baru mode 600 | HTTP 500 Apache | chmod 755 file & folder baru |
| Tabel tanpa timestamp | INSERT/UPDATE gagal | `useTimestamps = false` di semua model |
| `cors origin:*` | Risiko keamanan | Batasi origin + auth room di Fase 8 |
| Skema JSON `penilaian_*` tak terdokumentasi | Bug parsing | Entity wrapper + validasi schema |

---

## 8. Definition of Done (Keseluruhan)
- [ ] Semua peran perangkat (juri/ketua/sekretaris/layar/broadcast) berfungsi untuk PERSILAT tanding.
- [ ] Skor & pemenang identik dengan legacy pada seluruh skenario uji.
- [ ] Real-time sinkron & pulih otomatis saat reconnect.
- [ ] Unit test matematika nilai PERSILAT hijau.
- [ ] Tidak ada N+1 pada path live scoring.
- [ ] Dokumentasi operasional + rollback siap.

---

## 9. Lampiran — Kontrak Event Real-time (Target)

| Event | Arah | Payload | Keterangan |
|-------|------|---------|------------|
| `JOIN_ROOM` | klien→server | `{ id_pertandingan, posisi, token }` | join room + auth (baru) |
| `KONTROL_WAKTU` | sekretaris→server | `{ id_pertandingan, aksi, waktu, ronde, seq }` | kontrol timer |
| `UPDATE_WAKTU` | server→room | sama + `seq` | broadcast timer |
| `UPDATE_SKOR` | server→room | `{ id_pertandingan, skor_merah, skor_biru, ringkasan, seq }` | **baru** — push skor |
| `STATE_SYNC` | server→klien | snapshot state penuh | dipanggil saat reconnect |

> Catatan: `seq` (sequence number) ditambahkan untuk anti out-of-order. State authoritative
> selalu DB; event hanya notifikasi perubahan.

---

*Dokumen hidup — perbarui seiring temuan saat implementasi tiap fase.*
