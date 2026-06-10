# Operasional & Cutover — Modul Penilaian PERSILAT (CI4)

Dokumen ini menjelaskan cara menjalankan, memverifikasi, dan melakukan cutover
modul penilaian pertandingan (PERSILAT, tanding) hasil migrasi CI3 → CI4.

Status implementasi: **Fase 0–9 selesai**. Inti algoritma scoring terbukti
parity 100% terhadap data legacy (307 partai, 614 assertions). Seluruh test suite
hijau (312/312).

---

## 1. Arsitektur Singkat

```
┌────────────┐   POST nilai/hukuman    ┌──────────────┐
│ Juri / KP  │ ──────────────────────► │  CI4 (PHP)   │
│ Sekretaris │                         │  Controller  │
└────────────┘                         │  + Service   │
                                       │  + Model     │
                                       └──────┬───────┘
                                              │ tulis (transaksi + row-lock)
                                              ▼
                                       ┌──────────────┐
                                       │ db_sudinpora │ ◄── SUMBER KEBENARAN
                                       └──────┬───────┘
                                              │ HTTP POST /emit (fire-and-forget)
                                              ▼
                                       ┌──────────────┐  Socket.IO   ┌────────────┐
                                       │ realtime-srv │ ───push────► │ Layar/OBS  │
                                       │  (Node:3000) │  UPDATE_*    │ (display)  │
                                       └──────────────┘              └────────────┘
```

Prinsip: **DB selalu authoritative**. Realtime server hanya transport + cache
snapshot untuk recovery cepat. Bila realtime mati, klien fallback ke polling DB
(tidak ada kehilangan data skor).

---

## 2. Menjalankan

### 2.1 Aplikasi CI4 (PHP)
Dilayani Apache XAMPP di `http://localhost:8080/` (lihat `app.baseURL` di `.env`).
Untuk dev lokal cepat bisa juga `php spark serve`.

### 2.2 Realtime server (Node.js, port 3000)
```bash
cd realtime-server
npm install          # sekali saja
node server.js       # atau: npm start
```
Env opsional:
- `RT_PORT` — port server (default 3000)
- `RT_ORIGIN` — CORS origin (default `*`; **batasi di produksi**)

Cek kesehatan:
```bash
curl http://localhost:3000/health
# {"status":"ok","rooms":N,"uptime":...}
```

### 2.3 Konfigurasi PHP → realtime (.env)
```
RT_HOST = 'http://localhost:3000'        # dipakai PHP server-side untuk /emit
RT_PUBLIC_URL = 'http://localhost:3000'  # dipakai browser klien (socket.io)
```
> Di produksi, `RT_HOST` sebaiknya `http://127.0.0.1:3000` (PHP & Node satu host)
> dan endpoint `/emit` dibatasi ke localhost. `RT_PUBLIC_URL` adalah URL yang
> diakses browser device (boleh domain/IP publik + reverse proxy WSS).

---

## 3. Perangkat & Login

Semua perangkat login lewat `http://localhost:8080/perangkat-pertandingan`
(username/password dari tabel `perangkat_pertandingan`). Posisi menentukan device:

| Posisi (DB)          | Slug URL               | Fungsi                                  |
|----------------------|------------------------|-----------------------------------------|
| `juri`               | `/juri`                | Input nilai (pukulan/tendangan/jatuhan) |
| `ketua_pertandingan` | `/ketua-pertandingan`  | Hukuman/teguran/peringatan/binaan       |
| `sekretaris`         | `/sekretaris-pertandingan` | Timer, ronde, keputusan pemenang    |
| `layar`              | `/layar`               | Papan skor (scoreboard)                 |
| `broadcast_operator` | `/broadcast-operator`  | Kontrol overlay grafis siaran           |

Overlay OBS (publik, tanpa login):
`http://localhost:8080/broadcast-operator/overlay/{id_gelanggang}`

---

## 4. Kontrak Event Realtime (parity + ekstensi)

Room = `id_pertandingan`. Klien `JOIN_ROOM <id>` saat connect.

| Arah        | Event           | Dipicu oleh             | Payload kunci                                  |
|-------------|-----------------|-------------------------|------------------------------------------------|
| C→S         | `JOIN_ROOM`     | semua device            | `id_pertandingan`                              |
| C→S / HTTP  | `KONTROL_SKOR`  | Juri/KP/Sekretaris (PHP)| `skor_merah, skor_biru, ronde, seq`            |
| C→S / HTTP  | `KONTROL_WAKTU` | Sekretaris (PHP)        | `status_pertandingan, data_waktu, seq`         |
| C→S / HTTP  | `RESET_ROOM`    | Sekretaris (selesai)    | `id_pertandingan`                              |
| S→C         | `UPDATE_SKOR`   | server broadcast        | mirror payload skor + `seq`                    |
| S→C         | `UPDATE_WAKTU`  | server broadcast        | mirror payload waktu + `seq`                   |
| S→C         | `STATE_SYNC`    | server (saat join)      | snapshot `{seq, waktu, skor}` untuk recovery   |
| S→C         | `ROOM_RESET`    | server                  | `id_pertandingan` (klien reload)               |

`seq` = nomor urut anti out-of-order. Event dengan `seq` lebih kecil dari
terakhir diabaikan server.

PHP mengirim event lewat helper `app/Helpers/realtime_helper.php`:
`realtime_emit_skor()`, `realtime_emit_waktu()`, `realtime_reset_room()`.

---

## 5. Reliability (scoring-critical)

- **Tulis transaksional + row-lock**: penambahan/penghapusan nilai juri dan
  hukuman KP berjalan dalam transaksi DB dengan `FOR UPDATE` agar tidak ada
  race / double-submission yang merusak agregat.
- **Validasi server-side**: nilai juri ilegal (di luar 1/2/3) dan jenis
  kemenangan/ronde tidak valid ditolak controller, tidak hanya di klien.
- **Recovery reconnect**: setiap device memiliki endpoint
  `refresh-status-pertandingan` yang memuat state authoritative dari DB. Layar
  juga menerima `STATE_SYNC` saat join. Saat realtime putus, polling DB lanjut.
- **CSRF**: `regenerate=false` (alasan di `app/Config/Security.php`) agar POST
  cepat-beruntun + polling paralel device tidak memicu 403 transient. Token tetap
  divalidasi per sesi.

---

## 6. Verifikasi / QA

### 6.1 Test otomatis
```bash
vendor/bin/phpunit                                  # seluruh suite (312 hijau)
vendor/bin/phpunit tests/Services/Scoring/PersilatTandingServiceTest.php  # parity 307 partai
```
Parity test bersifat data-driven: memuat tiap partai bernilai dari `db_sudinpora`,
menjalankan ulang pipeline scoring service, lalu membandingkan `skor_merah`/
`skor_biru` dengan hasil tersimpan legacy. **Jaring pengaman regresi utama.**

### 6.2 Test realtime server
```bash
cd realtime-server
node smoke-test.js          # round-trip skor + recovery snapshot (3 pass)
node integration-test.js    # PHP-emit simulasi → broadcast → klien (4 pass)
```

### 6.3 QA manual end-to-end (disarankan sebelum cutover)
1. Jalankan CI4 + realtime server.
2. Buka 6 tab: Juri ×3 (juri1/2/3), Ketua, Sekretaris, Layar + 1 overlay OBS.
3. Sekretaris: mulai partai → timer start. Pastikan Layar ikut jalan.
4. Juri input nilai serempak (uji verifikasi interval 2 detik, butuh ≥2 juri
   sependapat). Pastikan skor Layar/overlay update real-time.
5. Ketua input teguran/binaan → skor berkurang sesuai aturan.
6. Putus koneksi realtime (stop node) → pastikan device fallback polling, skor
   tetap benar; hidupkan lagi → state pulih otomatis (STATE_SYNC).
7. Sekretaris selesaikan partai → semua device kembali ke standby/partai berikut.

---

## 7. Cutover dari Legacy (CI3)

1. **Backup** `db_sudinpora` penuh sebelum apa pun.
2. Pastikan schema target identik (modul ini tidak mengubah schema legacy;
   semua model CI4 memakai `useTimestamps = false`).
3. Jalankan parity test pada **salinan data produksi** — harus 100% hijau.
4. Deploy CI4 + realtime server di host yang sama; arahkan device gelanggang ke
   URL baru.
5. Soft-launch satu gelanggang dulu (shadow / non-kritikal) sebelum seluruh arena.
6. Siapkan rollback: legacy CI3 tetap tersedia; karena DB sama, kembali ke
   legacy tidak kehilangan data partai yang sudah tersimpan.

---

## 8. Cakupan & Batasan

- Cakupan migrasi ini: **tanding PERSILAT** (juri, ketua, sekretaris, layar,
  broadcast). Sistem lain (IPSI 2012, Tapak Suci, FPSTI, Festival) dan mode seni
  **belum** diport — service-nya menyusul dengan pola yang sama (port pure +
  parity test data-driven).
- **Bracket-advancement** (mengisi atlet ke partai berikutnya setelah pemenang
  ditetapkan) berada di luar scope ketat penilaian; finalisasi pemenang +
  jenis kemenangan sudah diport. Integrasikan advancement saat modul penjadwalan
  ikut dimigrasi.
- Scene overlay lanjutan (`highlight-hukuman-*`) disiapkan strukturnya namun
  rendering detailnya bisa diperkaya sesuai kebutuhan siaran.

---

## 9. Berkas Kunci

| Area            | Path                                                            |
|-----------------|----------------------------------------------------------------|
| Service scoring | `app/Services/Scoring/Persilat/PersilatTandingService.php`     |
| Parity test     | `tests/Services/Scoring/PersilatTandingServiceTest.php`        |
| Controllers     | `app/Controllers/Pertandingan/{Juri,KetuaPertandingan,SekretarisPertandingan,Layar,BroadcastOperator}.php` |
| Models          | `app/Models/{Pertandingan,PenilaianTanding,BroadcastGraphic}Model.php` |
| Helper realtime | `app/Helpers/realtime_helper.php`                              |
| Realtime server | `realtime-server/{server.js,package.json}`                    |
| Routes          | `app/Config/Routes.php`                                        |
| DB group        | `app/Config/Database.php` (group `sudinpora`)                 |
| Tema/CSS        | `public/assets/css/penilaian/*.css`, `app/Views/layouts/penilaian.php` |
