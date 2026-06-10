# Audit UI/UX — Sekretaris Pertandingan (CI3 → CI4)

**Tanggal:** 9 Juni 2026  
**Scope:** Seluruh halaman device Sekretaris Pertandingan  
**Referensi Legacy:** `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/pertandingan/Sekretaris_pertandingan.php` (1210 LOC, 32 method)  
**Target CI4:** `/Applications/XAMPP/xamppfiles/htdocs/dps-scoringsystem/app/Controllers/Pertandingan/SekretarisPertandingan.php` (489 LOC, 13 method)

---

## 1. Ringkasan Inventaris

### CI3 Legacy — Method Controller (32 method)

| # | Method | Mode | Tipe | View | Realtime? |
|---|--------|------|------|------|-----------|
| 1 | `index()` | both | page | → home | — |
| 2 | `jadwal_tanding($id)` | tanding | page | jadwal_tanding | — |
| 3 | `jadwal_seni($id)` | seni | page | jadwal_seni | — |
| 4 | `mulai_pertandingan($id)` | tanding | action | redirect | — |
| 5 | `mulai_penampilan($id)` | seni | action | redirect | — |
| 6 | `pindah_partai_tanding($id)` | tanding | action | redirect | — |
| 7 | `pindah_partai_seni()` | seni | action | redirect | — |
| 8 | `timer_tanding()` | tanding | page | timer_tanding / standby | ✓ polling |
| 9 | `timer_tandingv2()` | tanding | page | timer_tandingv2 | ✓ polling+socket |
| 10 | `buat_partai_tanding_dengan_atlet_lama()` | tanding | page | form | — |
| 11 | `toggle_timer_tanding($id)` | tanding | ajax | JSON | ✓ emit KONTROL_WAKTU |
| 12 | `pindah_ronde_tanding($id)` | tanding | ajax | JSON | ✓ emit |
| 13 | `timer_seni($mode)` | seni | page | timer_seni_sistem_pool / _battle | ✓ polling |
| 14 | `timer_seniv2($mode)` | seni | page | timer_seni_*v2 | ✓ polling+socket |
| 15 | `get_data_penentuan_juara(...)` | seni | ajax | JSON | — |
| 16 | `get_daftar_format_penilaian_seni($id)` | seni | ajax | JSON | — |
| 17 | `toggle_timer_seni($id)` | seni | ajax | JSON | ✓ emit |
| 18 | `timer_reset_seni($id)` | seni | ajax | JSON | ✓ emit |
| 19 | `timer_istirahat()` | both | ajax | JSON | ✓ emit |
| 20 | `ubah_waktu_tanding($id)` | tanding | post | redirect | — |
| 21 | `selesaikan_penampilan_seni($id)` | seni | ajax | JSON | ✓ emit |
| 22 | `pilih_pemenang_battle_seni($id)` | seni | ajax | JSON | — |
| 23 | `ganti_format_penilaian_seni($id)` | seni | post | redirect/JSON | — |
| 24 | `ganti_format_penilaian_tanding($id)` | tanding | post | redirect/JSON | — |
| 25 | `diskualifikasi_penampilan_seni($id)` | seni | ajax | JSON | — |
| 26 | `batalkan_diskualifikasi_penampilan_seni($id)` | seni | ajax | JSON | — |
| 27 | `input_manual_juara_seni()` | seni | post | JSON | — |
| 28 | `refresh_status_seni($id)` | seni | ajax | JSON | — (polling) |
| 29 | `form_edit_atlet_tanding($id)` | tanding | page | edit_atlet_tanding | — |
| 30 | `edit_atlet_tanding($id)` | tanding | post | redirect | — |
| 31 | `_remap()` | — | routing | — | — |
| 32 | `__construct()` | — | init | — | — |

### CI3 Legacy — Views (52 file)

| Kategori | File |
|----------|------|
| Halaman utama | `home.php`, `standby.php`, `jadwal_tanding.php`, `jadwal_seni.php` |
| Timer Tanding | `timer_tanding.php`, `timer_tandingv2.php`, `timer_tanding-tampilan-lama.php` |
| Timer Seni | `timer_seni_sistem_pool.php`, `timer_seni_sistem_poolv2.php`, `timer_seni_sistem_battle.php`, `timer_seni_sistem_battlev2.php` |
| Form | `edit_atlet_tanding.php`, `buat_partai_tanding_dengan_atlet_lama.php` |
| Print | `print_tunggal.php`, `print_beregu.php` |
| Template | `template.php` |
| Komponen Timer Tanding | `components/timer_tanding/navigation_bar.php`, `modal_ganti_format_penilaian.php`, `modal_manual_atur_waktu.php`, `modal_pengaturan_suara.php`, `offcanvas_pindah_partai.php`, `modal_keputusan_pemenang.php`, `modal_ubah_waktu.php`, `modal_info_penimbangan.php` |
| Komponen Timer Seni Pool | `components/timer_seni_pool/navigation_bar.php`, `modal_ganti_format_penilaian.php`, `modal_manual_atur_waktu.php`, `offcanvas_pindah_partai.php`, `modal_penentuan_juara.php` |
| Komponen Timer Seni Battle | `components/timer_seni_battle/navigation_bar.php`, `modal_ganti_format_penilaian.php`, `modal_manual_atur_waktu.php`, `offcanvas_pindah_partai.php`, `modal_penentuan_juara.php` |
| Header per sistem | `components/timer_tanding/header/{persilat,ipsi_2012,tapak_suci}.php` |
| Navigasi per sistem | `components/timer_tanding/navigasi_partai/{persilat,ipsi_2012,tapak_suci}.php` |
| Navigasi seni | `components/timer_seni_pool/navigasi_partai/{persilat,tapak_suci,festival}.php`, `timer_seni_battle/navigasi_partai/persilat.php` |
| Legacy/backup | `sekre-lama/*.php` |

### CI4 — Yang Sudah Ada (5 view, 13 method)

| Method CI4 | View | Status |
|---|---|---|
| `index()` / `home()` | `home.php` | ✅ Ada |
| `jadwalTanding($id)` | `jadwal_tanding.php` | ✅ Ada |
| `jadwalSeni($id)` | `jadwal_seni.php` | ✅ Ada |
| `mulaiPenampilan($id)` | redirect | ⚠️ Sebagian (timer seni belum ada) |
| `timerTanding()` | `timer_tanding.php` / `standby.php` | ✅ Ada |
| `mulaiPertandingan($id)` | redirect | ✅ Ada |
| `pindahPartaiTanding($id)` | redirect | ✅ Ada |
| `ubahWaktuTanding($id)` | modal di timer | ✅ Ada |
| `toggleTimerTanding($id)` | AJAX | ✅ Ada + realtime |
| `pindahRondeTanding($id)` | AJAX | ✅ Ada + realtime |
| `selesaikanPertandingan($id)` | AJAX | ✅ Ada + realtime |
| `refreshStatusPertandingan($id)` | AJAX polling | ✅ Ada |

---

## 2. Gap Analysis — Fungsional (Fitur Belum Dimigrasi)

### 2.1 BELUM ADA (❌)

| # | Fitur CI3 | Method CI3 | Priority | Catatan |
|---|-----------|-----------|----------|---------|
| 1 | **Timer Seni (Pool)** | `timer_seni('komplit')` | 🔴 HIGH | Layar timer + kontrol penampilan seni pool |
| 2 | **Timer Seni (Battle)** | `timer_seni('battle')` | 🔴 HIGH | Layar timer seni battle (biru vs merah) |
| 3 | **Selesaikan Penampilan Seni** | `selesaikan_penampilan_seni()` | 🔴 HIGH | Set pemenang + jenis di pool/battle |
| 4 | **Pilih Pemenang Battle Seni** | `pilih_pemenang_battle_seni()` | 🔴 HIGH | Tentukan pemenang battle bracket |
| 5 | **Toggle Timer Seni** | `toggle_timer_seni()` | 🔴 HIGH | Start/stop timer seni + emit |
| 6 | **Timer Reset Seni** | `timer_reset_seni()` | 🟡 MED | Reset timer penampilan seni |
| 7 | **Ganti Format Penilaian Tanding** | `ganti_format_penilaian_tanding()` | 🟡 MED | Ubah sistem penilaian partai aktif |
| 8 | **Ganti Format Penilaian Seni** | `ganti_format_penilaian_seni()` | 🟡 MED | Ubah format seni aktif |
| 9 | **Edit Atlet Tanding** | `form_edit_atlet_tanding()` + `edit_atlet_tanding()` | 🟡 MED | Form ganti atlet selama pertandingan |
| 10 | **Penentuan Juara Seni** | `get_data_penentuan_juara()` | 🟡 MED | Data podium seni |
| 11 | **Diskualifikasi Penampilan Seni** | `diskualifikasi_penampilan_seni()` | 🟡 MED | DQ penampilan |
| 12 | **Batalkan DQ Seni** | `batalkan_diskualifikasi_penampilan_seni()` | 🟡 MED | Undo DQ |
| 13 | **Input Manual Juara Seni** | `input_manual_juara_seni()` | 🟡 MED | Override juara manual |
| 14 | **Pindah Partai Seni** | `pindah_partai_seni()` | 🟡 MED | Jump to another seni performance |
| 15 | **Pengaturan Suara** | modal via navigation_bar | 🟢 LOW | Sound effects setting |
| 16 | **Info Penimbangan** | modal_info_penimbangan | 🟢 LOW | Data berat badan atlet |
| 17 | **Buat Partai dengan Atlet Lama** | `buat_partai_tanding_dengan_atlet_lama()` | 🟢 LOW | Create ad-hoc match (commented out in CI3) |
| 18 | **Print Tunggal/Beregu** | `print_tunggal.php`, `print_beregu.php` | 🟢 LOW | Print hasil tanding |
| 19 | **Timer Istirahat** | `timer_istirahat()` | 🟢 LOW | Emit istirahat event |
| 20 | **Refresh Status Seni** | `refresh_status_seni()` | 🟡 MED | Polling status penampilan seni |

### 2.2 SUDAH ADA TAPI ADA GAP UI/UX (⚠️)

| # | Halaman | Gap UI/UX | Detail |
|---|---------|-----------|--------|
| 1 | **home.php** | Event name tidak ditampilkan | CI3 menampilkan `event_name` dari setting; CI4 hanya "Dashboard" |
| 2 | **home.php** | Tab order berbeda | CI3: Seni first, Tanding second. CI4: Tanding first |
| 3 | **home.php** | Kolom "Keterangan" | CI3 punya; CI4 sudah ada ✓ |
| 4 | **jadwal_tanding.php** | Kontingen atlet tidak ditampilkan | CI3 menampilkan kontingen; CI4 sudah select tapi mungkin tidak render |
| 5 | **jadwal_tanding.php** | Badge status warna | CI4 pakai `bg-secondary` semua; CI3 pakai warna per status |
| 6 | **standby.php** | Dua mode (tanding/seni) | CI3 standby bisa tanding ATAU seni (flashdata); CI4 hanya tanding |
| 7 | **standby.php** | Offcanvas partai list | CI3 standby punya offcanvas lengkap; CI4 pakai tabel inline |
| 8 | **timer_tanding.php** | Kontingen atlet | CI3 menampilkan kontingen di bawah nama; CI4 hanya nama |
| 9 | **timer_tanding.php** | Info kelas/berat badan | CI3 header punya box gelanggang + partai + kelas + berat + babak; CI4 lebih minimalis |
| 10 | **timer_tanding.php** | Pengaturan Suara | CI3 ada modal; CI4 belum |
| 11 | **timer_tanding.php** | Ganti Format Penilaian | CI3 ada di navbar; CI4 belum |
| 12 | **timer_tanding.php** | Info Penimbangan | CI3 ada modal; CI4 belum |
| 13 | **timer_tanding.php** | Sound Effects (countdown/bell) | CI3 punya audio play; CI4 belum |
| 14 | **timer_tanding.php** | Offcanvas Pindah Partai — info lebih | CI3 punya skor + navigasi per sistem penilaian; CI4 lebih basic |
| 15 | **timer_tanding.php** | Dark mode gradient background | CI3 `bg-super-dark bg-gradient`; CI4 pakai layout penilaian (sudah gelap) ✓ |
| 16 | **jadwal_seni.php** | Kolom anggota kelompok | CI3 menampilkan anggota; CI4 sudah ada ✓ |

---

## 3. Gap Analysis — UI/UX Detail per Halaman

### 3.1 `home.php` (Dashboard)

| Aspek | CI3 | CI4 | Rekomendasi |
|-------|-----|-----|-------------|
| Header | "Arena {gelanggang}" + event_name | "Sekretaris Pertandingan" + gelanggang | Tambah event_name dari DB/setting |
| Tab order | Seni → Tanding | Tanding → Seni | Sesuaikan ke CI3 (seni dulu) atau tetap CI4 (user preference) |
| Kolom header tabel | No, Number of Performances/Matches, Date and Times, Notes | No, Jumlah Partai/Penampilan, Tanggal & Waktu, Keterangan, Aksi | CI4 sudah lebih baik (Bahasa Indonesia ✓) |
| Tombol | `btn-primary` plain | `btn-danger rounded-pill` | CI4 sudah lebih baik ✓ |
| Empty state | Tidak ada | Ada text muted | CI4 lebih baik ✓ |
| Layout | Container centered `min-vh-75` | Layout penilaian topbar + body | CI4 lebih baik ✓ |

**Aksi migrasi:** Tambah event_name di header.

### 3.2 `jadwal_tanding.php`

| Aspek | CI3 | CI4 | Rekomendasi |
|-------|-----|-----|-------------|
| File | Tidak ada sebagai file terpisah di CI3 (embedded di timer logic) | Ada, terpisah | CI4 lebih baik ✓ |
| Badge status | Per-status color | `bg-secondary` semua | **Migrasi:** badge warna per status |
| Info kontingen | Ada di CI3 timer | CI4 select kontingen tapi belum render | Tambah kontingen di kolom atlet |
| Kolom skor | CI3 timer punya skor live | CI4 jadwal tabel hanya listing | OK (skor di timer, bukan listing) |

**Aksi migrasi:** Badge status berwarna + tampilkan kontingen.

### 3.3 `jadwal_seni.php`

| Aspek | CI3 | CI4 | Rekomendasi |
|-------|-----|-----|-------------|
| Anggota kelompok | CI3 tampilkan | CI4 sudah ada ✓ | — |
| Status badge | CI3 color-coded | CI4 sudah color-coded ✓ | — |
| Waktu tampil | CI3 ada | CI4 ada ✓ | — |

**Aksi migrasi:** Minimal — sudah cukup baik.

### 3.4 `standby.php`

| Aspek | CI3 | CI4 | Rekomendasi |
|-------|-----|-----|-------------|
| Dual mode | Tanding + Seni standby (via flashdata) | Hanya tanding | **Migrasi:** Tambah seni standby mode |
| Jump to match | Offcanvas lengkap (komponen terpisah) | Tabel inline sederhana | CI4 OK (fungsional sama, UI berbeda) |
| Polling | Dua endpoint (pertandingan + seni) | Hanya pertandingan | **Migrasi:** Tambah polling seni |
| Header | "Stand By - No Match Playing" | "Pilih Partai" | Perjelas label per mode |
| Auto-redirect | Ada (polling → reload) | Ada ✓ | — |

**Aksi migrasi:** Tambah dual mode standby (tanding/seni) + polling seni.

### 3.5 `timer_tanding.php` (HALAMAN UTAMA)

| Aspek | CI3 (v2) | CI4 | Rekomendasi |
|-------|----------|-----|-------------|
| Layout | Dark bg `bg-super-dark` + gradient | Layout penilaian (gelap) | ✓ Sama konsep |
| Info bar atas | Box: gelanggang, partai, kelas+berat, babak | Topbar: brand, partai, kelas, ronde | **Tambah:** berat badan range |
| Atlet card | Box biru/merah gradient + nama + kontingen + skor besar | Simplified: nama + skor | **Tambah:** kontingen di bawah nama |
| Timer display | `min(8rem, 15vw)` huge font | `penilaian-display-font` | ✓ |
| Kontrol | Manual Set, Play/Stop, Reset (3 tombol) | Mulai, Henti, Reset, Set Manual, Ronde, Selesaikan | CI4 lebih lengkap ✓ |
| Ronde control | Di navigasi partai component per sistem | Inline button group | CI4 lebih clean ✓ |
| Sound Setting | Modal tersedia | ❌ Belum | Tambah modal suara |
| Format Penilaian | Modal via navbar | ❌ Belum | Tambah tombol + modal |
| Info Penimbangan | Modal tersedia | ❌ Belum | Tambah modal (low priority) |
| Navigasi prev/next | Ada per sistem penilaian | Ada (generic) ✓ | — |
| Jump to match | Offcanvas kanan | Offcanvas bawah | ✓ Keduanya valid |
| Keputusan pemenang | Modal (format per sistem) | Modal (generic) ✓ | — |
| Ubah waktu | Modal lengkap | Modal lengkap ✓ | — |
| Polling skor | 5s interval | 4s interval ✓ | — |
| Audio countdown | Timer end → bell sound | ❌ Belum | Tambah sound effect |

**Aksi migrasi utama:**
1. Tambah kontingen atlet di scoreboard
2. Tambah berat badan info di header
3. Tambah sound effect (bell/countdown)
4. Tambah modal ganti format penilaian (future)

---

## 4. Prioritas Migrasi UI/UX (Quick Wins)

Berikut item yang bisa langsung dimigrasikan tanpa menambah method/route baru:

| # | Item | File Target | Effort |
|---|------|-------------|--------|
| 1 | Badge status berwarna di `jadwal_tanding.php` | `app/Views/pertandingan/sekretaris/jadwal_tanding.php` | Kecil |
| 2 | Tampilkan kontingen atlet di `timer_tanding.php` | `app/Views/pertandingan/sekretaris/timer_tanding.php` | Kecil |
| 3 | Tampilkan kontingen di `jadwal_tanding.php` | `app/Views/pertandingan/sekretaris/jadwal_tanding.php` | Kecil |
| 4 | Tambah berat badan di timer header | `app/Views/pertandingan/sekretaris/timer_tanding.php` | Kecil |
| 5 | Event name di home | `app/Views/pertandingan/sekretaris/home.php` + controller | Kecil |
| 6 | Standby dual mode (tanding/seni) | `standby.php` + controller + route | Sedang |
| 7 | Badge status berwarna di `standby.php` | `app/Views/pertandingan/sekretaris/standby.php` | Kecil |
| 8 | Sound effect (bell di timer habis) | `timer_tanding.php` + asset audio | Sedang |

---

## 5. Prioritas Migrasi Fungsional (Fitur Baru)

| Priority | Fitur | Estimasi Effort | Dependensi |
|----------|-------|-----------------|------------|
| 🔴 HIGH | Timer Seni Pool (view + controller + realtime) | Besar | Model seni, format penilaian seni |
| 🔴 HIGH | Timer Seni Battle (view + controller + realtime) | Besar | Model battle, bracket |
| 🔴 HIGH | Selesaikan Penampilan Seni + Pilih Pemenang Battle | Sedang | Timer seni ada dulu |
| 🟡 MED | Ganti Format Penilaian Tanding | Sedang | Modal + passcode + DB update |
| 🟡 MED | Edit Atlet Tanding | Sedang | Form + validasi + DB |
| 🟡 MED | Diskualifikasi / Batalkan DQ Seni | Kecil | Timer seni ada dulu |
| 🟡 MED | Input Manual Juara Seni | Kecil | Timer seni ada dulu |
| 🟢 LOW | Pengaturan Suara | Kecil | Asset audio + modal |
| 🟢 LOW | Info Penimbangan | Kecil | Data berat dari DB sudah ada |
| 🟢 LOW | Print Tunggal/Beregu | Sedang | Layout cetak terpisah |

---

## 6. Catatan Arsitektur CI4 yang Sudah Baik

✅ Yang SUDAH benar dan TIDAK perlu diubah:
- Layout `penilaian.php` terpisah dari admin (device-specific, fullscreen)
- Bootstrap 5.3.3 + Font Awesome 6.5.2 + Oswald/Poppins
- Custom CSS `sekretaris.css` terpisah
- Tombol pill merah (`btn-danger rounded-pill`) konsisten
- CSRF handling: header `X-CSRF-TOKEN` + JSON body
- Realtime emit via helper (fire-and-forget)
- Polling sebagai fallback
- `requestAnimationFrame` timer (lebih akurat dari setInterval)
- Modal/offcanvas Bootstrap native
- SweetAlert2 untuk konfirmasi penting
- Auth via `PerangkatAuthFilter` (device-based, bukan user-based)

---

## 7. Rekomendasi Urutan Eksekusi

### Phase 1 — Quick Wins UI/UX (bisa sekarang)
1. Badge status berwarna (semua view)
2. Kontingen atlet di timer + jadwal
3. Berat badan di timer header
4. Event name di home
5. Sound effect basic (bell)

### Phase 2 — Standby Dual Mode
1. Refactor standby untuk support mode seni
2. Polling seni endpoint
3. Auto-redirect saat penampilan aktif

### Phase 3 — Timer Seni (major feature)
1. Model/Service penampilan seni
2. Controller methods (timer_seni, toggle, reset, selesaikan, pindah)
3. Views (pool + battle)
4. Realtime emit untuk seni
5. Modal penentuan juara
6. Diskualifikasi/batalkan

### Phase 4 — Advanced Features
1. Ganti format penilaian (tanding + seni)
2. Edit atlet tanding
3. Input manual juara seni
4. Print tunggal/beregu
5. Info penimbangan
6. Pengaturan suara

---

## 8. File yang Perlu Diubah untuk Quick Wins

```
app/Views/pertandingan/sekretaris/home.php          — event_name
app/Views/pertandingan/sekretaris/jadwal_tanding.php — badge warna + kontingen
app/Views/pertandingan/sekretaris/standby.php       — badge warna
app/Views/pertandingan/sekretaris/timer_tanding.php — kontingen + berat
app/Controllers/Pertandingan/SekretarisPertandingan.php — pass event_name + kontingen data
app/Models/PertandinganModel.php                    — query kontingen jika belum
```

---

*Dokumen ini di-generate otomatis dan akan di-update seiring migrasi berlangsung.*
