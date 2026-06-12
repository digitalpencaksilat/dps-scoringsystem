# Rencana Perbaikan — Modul Ketua Pertandingan (KP)

> Audit tanggal: 11 Juni 2026 | Total temuan: 15 (5 kritis, 6 menengah, 4 minor)

---

## Ringkasan

Modul Ketua Pertandingan (Head Referee) mencakup **592 baris controller**, **9 view file**, **2 file JS** (`kp_tanding.js` 791 baris, `kp_seni.js` 279 baris), dan **2 file CSS** (`ketua-tanding.css` 709 baris, `ketua-seni.css` 159 baris).

Jalur **tanding** sudah solid (transaksi + row lock + dual-speed polling). Jalur **seni** dan **verifikasi** memiliki gap kritis yang harus diselesaikan sebelum production.

---

## Fase 1: Perbaikan Kritis (5 item)

Harus selesai sebelum system digunakan live. Estimasi total: **~6 jam**.

### #1 🔴 `JOIN_ROOM` tidak re-emit saat socket reconnect (kp_seni.js)

- **File**: `public/assets/js/penilaian/kp_seni.js:256`
- **Masalah**: `JOIN_ROOM` hanya di-emit sekali saat init. Setelah disconnect → reconnect, KP tidak join room ulang, silent lost semua event real-time (`JURI_READY_UPDATE`, `PENAMPILAN_SELESAI`)
- **Perbaikan**: Pindahkan `socket.emit('JOIN_ROOM', ...)` ke dalam handler `socket.on('connect', ...)`
- **Estimasi**: 30 menit

### #2 🔴 6 method seni tidak validasi `idGelanggang`

- **File**: `app/Controllers/Pertandingan/KetuaPertandingan.php:265-423`
- **Method terdampak**: `editPenilaianSeni`, `gantiAksesPenilaian`, `diskualifikasiPenampilanSeni`, `batalkanDiskualifikasi`, `getJawabanVerifikasi`, `refreshStatusSeni`
- **Masalah**: Hanya cek `find($id)` — tidak cocokkan `id_penampilan_seni` terhadap `id_gelanggang` KP. Bisa diskualifikasi/ubah penampilan gelanggang lain via manipulasi URL
- **Perbaikan**: Setelah `find($id)`, verifikasi bahwa `$penampilan->id_gelanggang === $this->idGelanggang()`. Return 403 jika tidak cocok
- **Model tambahan**: Pastikan `PenampilanSeniModel` punya kolom `id_gelanggang` atau join ke `partai_seni` → `jadwal_seni` → `gelanggang`
- **Estimasi**: 2 jam (termasuk verifikasi schema)

### #3 🔴 Split transaction di `updateVerifikasi`

- **File**: `app/Controllers/Pertandingan/KetuaPertandingan.php:510-537`
- **Masalah**: Raw UPDATE ke `verifikasi_pertandingan` (line 512-517) di luar transaksi. Jika `prosesKp()` gagal, verifikasi sudah ditandai `selesai` tapi skor tidak terupdate. State inkonsisten
- **Perbaikan**: Bungkus raw UPDATE + `prosesKp()` dalam satu transaksi. Jika `prosesKp` gagal → rollback keduanya
- **Estimasi**: 1 jam

### #4 🔴 Stale UI setelah socket event (kp_tanding.js)

- **File**: `public/assets/js/penilaian/kp_tanding.js:628-687`
- **Masalah**: Handler `NILAI_UPDATE` / `UPDATE_SKOR` hanya update `#skor-merah` / `#skor-biru`. Tidak pernah panggil `applyRingkasan()` atau `updateTampilanNilai()`. Tabel monitoring, rekap panel, dan button lock KP basi sampai polling berikutnya (max 8 detik)
- **Dampak**: KP bisa apply hukuman di atas state basi (misal tekan `binaan_2` padahal `binaan_1` sudah di-apply device lain)
- **Perbaikan**: Di handler `UPDATE_SKOR`, tambahkan panggilan:
  ```js
  if (data.ringkasan) this.applyRingkasan(data.ringkasan);
  if (data.data_nilai) this.updateTampilanNilai(data.data_nilai);
  ```
  Atau alternatif: setelah socket event, trigger satu kali refresh polling segera (tidak tunggu interval)
- **Estimasi**: 1 jam

### #5 🔴 `prosesHukumanKp` tidak pakai `SELECT ... FOR UPDATE`

- **File**: `app/Models/PenilaianSeniModel.php:239-287`
- **Masalah**: Transaksi ada (`transStart`/`transComplete`) tapi tanpa row lock. Dua KP device concurrent bisa timpa JSON `penilaian` satu sama lain
- **Perbaikan**: Tambahkan `SELECT ... FOR UPDATE` sebelum `findAll()`, parity dengan `prosesKp` di `PenilaianTandingModel.php:170-221`
- **Estimasi**: 30 menit

---

## Fase 2: Perbaikan Menengah (6 item)

Bisa dikerjakan paralel atau bertahap. Estimasi total: **~8 jam**.

### #6 🟡 `daftar_nilai_tanding.php` & `daftar_nilai_seni.php` tidak diimplementasikan

- **File**: `app/Views/pertandingan/ketua/daftar_nilai_tanding.php` + `daftar_nilai_seni.php`
- **Masalah**: Tabel kosong + TODO comment. DataTables tidak di-init. Tidak ada AJAX endpoint
- **Perbaikan**:
  - Buat endpoint controller untuk data JSON (server-side DataTables)
  - Init DataTables dengan AJAX source
  - Kolom tanding: partai, nama atlet, sudut, skor akhir, ronde
  - Kolom seni: partai, nama atlet, nilai akhir, peringkat
- **Estimasi**: 3 jam

### #7 🟡 Duplikasi masif dark/light views

- **File**: 4 pasang view (~900 LOC duplikasi)
  - `tanding/persilat/{light,dark}.php`
  - `seni/persilat/battle/{light,dark}.php`
  - `seni/persilat/pool/{light,dark}.php`
- **Masalah**: Setiap perubahan layout harus dilakukan di 2 tempat. ~900 LOC redundan
- **Perbaikan**: Refactor jadi single view per mode. Tema dikontrol via CSS class `kp-theme-light` / `kp-theme-dark` pada wrapper, bukan via file terpisah. CSS sudah mendukung ini (`*-light` modifier classes sudah ada)
- **Catatan**: Lakukan setelah Fase 1 selesai. Risiko regresi tinggi — test menyeluruh tiap view
- **Estimasi**: 4 jam

### #8 🟡 `$alasan` di `diskualifikasiPenampilanSeni` dead code

- **File**: `app/Controllers/Pertandingan/KetuaPertandingan.php:353`
- **Masalah**: `$alasan` diambil dari POST tapi tidak disimpan ke database
- **Perbaikan**: Simpan `$alasan` ke kolom `alasan_diskualifikasi` di tabel `penampilan_seni`, atau hapus baris pengambilan jika memang tidak diperlukan
- **Estimasi**: 15 menit

### #9 🟡 `idPerangkat()` defined but never called

- **File**: `app/Controllers/Pertandingan/KetuaPertandingan.php:44-47`
- **Masalah**: Dead code, private method tidak dipanggil
- **Perbaikan**: Hapus method atau gunakan di method yang butuh tracking `id_perangkat_pertandingan` (misal untuk audit log)
- **Estimasi**: 5 menit

### #10 🟡 Verifikasi double-polling race

- **File**: `public/assets/js/penilaian/kp_tanding.js:702-740`
- **Masalah**: Socket `VERIFIKASI_JATUHAN` + response polling bisa start 2 concurrent `pollJawabanVerifikasi()` loop untuk `jenis` yang sama
- **Perbaikan**: Sebelum start polling loop, clear interval sebelumnya untuk `jenis` yang sama. Guard dengan `if (this._pollVerifikasiInterval) clearInterval(...)`
- **Estimasi**: 30 menit

### #11 🟡 `data-sistem` missing di pool views

- **File**: `app/Views/pertandingan/ketua/seni/persilat/pool/{light,dark}.php`
- **Masalah**: JS `kp_seni.js` tidak bisa bedakan pool vs battle tanpa attribute ini
- **Perbaikan**: Tambahkan `data-sistem="pool"` di wrapper div pool views, parity dengan battle views yang sudah punya `data-sistem="battle"`
- **Estimasi**: 10 menit

---

## Fase 3: Perbaikan Minor / UX (4 item)

Nice-to-have, tidak blocking. Estimasi total: **~4 jam**.

### #12 🟢 Tidak ada loading/empty/error states

- **File**: Semua view ketua
- **Masalah**: User lihat `0` atau `-` tanpa tahu apakah data sedang dimuat atau benar-benar kosong
- **Perbaikan**:
  - Tambah skeleton loader / spinner saat initial load
  - Empty state: "Belum ada data nilai" dengan ikon
  - Error state: "Gagal memuat data" dengan tombol retry
- **Estimasi**: 2 jam

### #13 🟢 Zero accessibility

- **File**: Semua view ketua
- **Masalah**: Tidak ada `aria-*`, `role`, keyboard navigation. Scoring screen digunakan dalam tekanan match live
- **Perbaikan**:
  - Tambah `aria-label` pada tombol-tombol dewan
  - Tambah `role="tab"`, `role="tabpanel"` pada tab interface
  - Pastikan semua interaksi bisa via keyboard (tab/enter/escape)
  - Fokus indikator yang jelas
- **Estimasi**: 1.5 jam

### #14 🟢 Image atlet tidak ada fallback `onerror`

- **File**: `app/Views/pertandingan/ketua/tanding/persilat/{light,dark}.php`
- **Masalah**: Broken image icon jika foto tidak ditemukan
- **Perbaikan**: Tambah `onerror="this.src='<?= base_url('assets/images/icon/siluette_atlet.png') ?>'"` pada `<img>` atlet
- **Estimasi**: 10 menit

### #15 🟢 Inline CSS di `home.php`

- **File**: `app/Views/pertandingan/ketua/home.php`
- **Masalah**: ~50 baris CSS di dalam `<style>` tag — tidak reusable, tidak bisa di-cache terpisah
- **Perbaikan**: Pindahkan ke `public/assets/css/penilaian/ketua-tanding.css` (atau file baru `ketua-home.css`)
- **Estimasi**: 20 menit

---

## Bonus: Perbaikan CSS Seni

### 🟡 `ketua-seni.css` perlu ditingkatkan ke parity dengan tanding

- **File**: `public/assets/css/penilaian/ketua-seni.css`
- **Masalah**:
  - `.kp-light-mode` tidak ada di CSS (hanya inline di view)
  - Light variant untuk battle tabs tidak ada
  - Hanya 2 breakpoint vs 5 di tanding
  - Warna hardcoded, tidak pakai design token
- **Perbaikan**: Tambah light variants, responsive breakpoints 991px/767px/400px, ganti warna hardcoded dengan CSS variables
- **Estimasi**: 2 jam

---

## Timeline Usulan

```
Minggu 1          Minggu 2          Minggu 3
├─────────────────┼─────────────────┼─────────────────┤
│ Fase 1 (6 jam)  │ Fase 2 (8 jam)  │ Fase 3 (4 jam)  │
│ Kritis: #1-5    │ Menengah: #6-11 │ Minor: #12-15   │
│ MUST production │ SHOULD sebelum  │ NICE-TO-HAVE    │
│                 │ testing penuh   │                 │
└─────────────────┴─────────────────┴─────────────────┘
```

---

## Verifikasi Pasca-Perbaikan

Setelah tiap fase, lakukan:

1. **PHP syntax check**: `php -l` pada semua file yang diubah
2. **Unit test scoring**: `vendor/bin/phpunit` untuk service scoring (jika ada)
3. **Manual test end-to-end**:
   - 2 device KP login bersamaan → test concurrent hukuman tanding
   - 2 device KP login bersamaan → test concurrent hukuman seni
   - Putuskan koneksi jaringan → verifikasi reconnect + JOIN_ROOM
   - Verifikasi jatuhan: KP start → Juri jawab → KP tetapkan → cek skor terupdate
   - Verifikasi pelanggaran: KP start → Juri jawab → KP tetapkan → cek skor terupdate
   - Polling: cek semua endpoint refresh-status return data lengkap
   - Cross-gelanggang: KP gelanggang A tidak bisa akses penampilan gelanggang B
4. **Browser console**: zero error, zero warning
5. **Network tab**: tidak ada 403/404/500, polling interval sesuai ekspektasi

---

## File Terkait (Referensi)

| Kategori | File |
|---|---|
| Controller | `app/Controllers/Pertandingan/KetuaPertandingan.php` (592 LOC) |
| Models | `app/Models/PenilaianTandingModel.php`, `app/Models/PenilaianSeniModel.php`, `app/Models/PertandinganModel.php`, `app/Models/PenampilanSeniModel.php` |
| Services | `app/Services/Scoring/Persilat/PersilatTandingService.php`, `app/Services/Scoring/Persilat/PersilatSeniService.php` |
| Views (9) | `app/Views/pertandingan/ketua/` — home, daftar_nilai_*, tanding/{light,dark}, seni/battle/{light,dark}, seni/pool/{light,dark} |
| Layout | `app/Views/layouts/penilaian.php` |
| JS | `public/assets/js/penilaian/kp_tanding.js` (791 LOC), `public/assets/js/penilaian/kp_seni.js` (279 LOC) |
| CSS | `public/assets/css/penilaian/ketua-tanding.css` (709 LOC), `public/assets/css/penilaian/ketua-seni.css` (159 LOC), `public/assets/css/penilaian/penilaian.css` (264 LOC) |
| Filter | `app/Filters/PerangkatAuthFilter.php` |
| Routes | `app/Config/Routes.php:51-80` |
