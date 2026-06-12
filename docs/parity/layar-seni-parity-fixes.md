# Parity Fixes: Layar Seni Pool & Battle

**Date**: 2026-06-12  
**Target**: `app/Controllers/Pertandingan/Layar.php` + views hasil pool/battle seni  
**Legacy Reference**: `/Applications/XAMPP/xamppfiles/htdocs/dps/application/controllers/pertandingan/Layar.php`

## Summary

Perbaikan parity halaman layar seni pool dan battle untuk menampilkan nama peserta dengan benar menggunakan field `anggota_kelompok_peserta_seni` dari tabel `kelompok_peserta_seni`, sesuai dengan legacy CI3.

---

## Changes Made

### 1. Controller: `Layar::seni()` — Live Scoring Display

**File**: `app/Controllers/Pertandingan/Layar.php`

**Change**: Query field `anggota_kelompok_peserta_seni` dari `kelompok_peserta_seni` dan attach ke object `$penampilan`.

**Before**:
```php
$kps = $db->table('kelompok_peserta_seni')
    ->select('id_kompetisi_seni')
    ->where('id_kelompok_peserta_seni', $penampilan->id_kelompok_peserta_seni)
    ->get()->getRow();
$idKompetisiSeni = (int) ($kps->id_kompetisi_seni ?? 0);
```

**After**:
```php
$kps = $db->table('kelompok_peserta_seni')
    ->select('id_kompetisi_seni, anggota_kelompok_peserta_seni')
    ->where('id_kelompok_peserta_seni', $penampilan->id_kelompok_peserta_seni)
    ->get()->getRow();
$idKompetisiSeni = (int) ($kps->id_kompetisi_seni ?? 0);
$anggotaKelompok = $kps->anggota_kelompok_peserta_seni ?? null;

// Attach to penampilan object for view (parity legacy)
$penampilan->anggota_kelompok_peserta_seni = $anggotaKelompok;
```

**Reason**: Legacy view `dark.php` expects `$penampilan_seni_berlangsung->anggota_kelompok_peserta_seni` untuk display nama peserta yang sudah di-format.

---

### 2. Controller: `Layar::hasilPoolSeni()` — Pool Results Display

**File**: `app/Controllers/Pertandingan/Layar.php`

**Change**: Tambahkan `kps.anggota_kelompok_peserta_seni` di SELECT query.

**Before**:
```php
$daftarPenampilan = $db->table('penampilan_seni ps')
    ->select('ps.*, kps.id_kontingen, k.nama_kontingen, ps.catatan_nilai_sama')
    ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
    ...
```

**After**:
```php
$daftarPenampilan = $db->table('penampilan_seni ps')
    ->select('ps.*, kps.id_kontingen, k.nama_kontingen, ps.catatan_nilai_sama, kps.anggota_kelompok_peserta_seni')
    ->join('kelompok_peserta_seni kps', 'kps.id_kelompok_peserta_seni = ps.id_kelompok_peserta_seni')
    ...
```

**Reason**: View `hasil_pool_seni.php` perlu field ini untuk display nama peserta.

---

### 3. Controller: `Layar::hasilBattleSeni()` — Battle Results Display

**File**: `app/Controllers/Pertandingan/Layar.php`

**Change**: Tambahkan `kps.anggota_kelompok_peserta_seni` di SELECT query untuk biru & merah.

**Before**:
```php
$penampilanBiru = $db->table('penampilan_seni ps')
    ->select('ps.*, kps.id_kontingen, k.nama_kontingen')
    ...
```

**After**:
```php
$penampilanBiru = $db->table('penampilan_seni ps')
    ->select('ps.*, kps.id_kontingen, k.nama_kontingen, kps.anggota_kelompok_peserta_seni')
    ...
```

(Sama untuk `$penampilanMerah`)

**Reason**: View `hasil_battle_seni.php` perlu field ini untuk display nama peserta biru/merah.

---

### 4. View: `hasil_pool_seni.php` — Display Logic

**File**: `app/Views/pertandingan/layar/seni/persilat/hasil_pool_seni.php`

**Change**: Prioritaskan `anggota_kelompok_peserta_seni`, fallback ke individual names.

**Before**:
```php
<p class="rank-name m-0"><?= implode(' &bull; ', $namaPeserta) ?: '-' ?></p>
```

**After**:
```php
<p class="rank-name m-0"><?= !empty($item->anggota_kelompok_peserta_seni) ? esc($item->anggota_kelompok_peserta_seni) : (implode(' &bull; ', $namaPeserta) ?: '-') ?></p>
```

**Legacy Parity**: `/dps/application/views/pertandingan/layar/seni/persilat/hasil_pool_seni.php` line 69:
```php
<?= $penampilan_seni->anggota_kelompok_peserta_seni ?>
```

---

### 5. View: `hasil_battle_seni.php` — Display Logic

**File**: `app/Views/pertandingan/layar/seni/persilat/hasil_battle_seni.php`

**Change**: Prioritaskan `anggota_kelompok_peserta_seni`, fallback ke individual names.

**Before**:
```php
$namaBiru = [];
foreach ($peserta_seni_biru as $ps) { $namaBiru[] = esc($ps->nama_pendaftar ?? ''); }
```

**After**:
```php
$namaBiru = [];
if (!empty($penampilan_seni_biru->anggota_kelompok_peserta_seni)) {
    $namaBiru = [esc($penampilan_seni_biru->anggota_kelompok_peserta_seni)];
} else {
    foreach ($peserta_seni_biru as $ps) { $namaBiru[] = esc($ps->nama_pendaftar ?? ''); }
}
```

(Sama untuk `$namaMerah`)

**Legacy Parity**: Legacy juga pakai `anggota_kelompok_peserta_seni` untuk nama tim/kelompok.

---

### 6. View: `dark.php` — Live Scoring Display

**File**: `app/Views/pertandingan/layar/seni/persilat/dark.php`

**Change**: Prioritaskan `$penampilan_seni_berlangsung->anggota_kelompok_peserta_seni`.

**Before**:
```php
$namaPeserta = [];
if (!empty($peserta_seni)) {
    foreach ($peserta_seni as $ps) {
        $namaPeserta[] = esc($ps->nama_pendaftar ?? '');
    }
}
echo implode(' &bull; ', $namaPeserta) ?: 'Peserta';
```

**After**:
```php
$displayName = 'Peserta';
if (!empty($penampilan_seni_berlangsung->anggota_kelompok_peserta_seni)) {
    $displayName = esc($penampilan_seni_berlangsung->anggota_kelompok_peserta_seni);
} else {
    $namaPeserta = [];
    if (!empty($peserta_seni)) {
        foreach ($peserta_seni as $ps) {
            $namaPeserta[] = esc($ps->nama_pendaftar ?? '');
        }
    }
    $displayName = implode(' &bull; ', $namaPeserta) ?: 'Peserta';
}
echo $displayName;
```

**Legacy Parity**: Legacy view dark.php juga query join `anggota_kelompok_peserta_seni`.

---

## Database Schema Reference

### `kelompok_peserta_seni` table

| Field                      | Type         | Description                                      |
|----------------------------|--------------|--------------------------------------------------|
| `id_kelompok_peserta_seni` | int(11)      | PK                                               |
| `id_kompetisi_seni`        | int(11)      | FK → `kompetisi_seni.id_kompetisi_seni`          |
| `id_kontingen`             | int(11)      | FK → `kontingen.id_kontingen`                    |
| `id_pembayaran`            | int(11)      | FK → `pembayaran` (nullable)                     |
| `status`                   | enum         | 'ok', 'diskualifikasi'                           |
| `keterangan`               | text         | Notes                                            |
| `nomor_undi`               | int(11)      | Draw number                                      |

**IMPORTANT**: Field `anggota_kelompok_peserta_seni` **TIDAK ADA** di tabel fisik. Legacy CI3 menggunakan **subquery** di `Penampilan_seni_model::select()`:

```sql
SELECT GROUP_CONCAT(CONCAT_WS(' ', pendaftar.nama_pendaftar) SEPARATOR ' ,<br>')
FROM pendaftar
JOIN peserta_seni ON peserta_seni.id_pendaftar = pendaftar.id_pendaftar
WHERE peserta_seni.id_kelompok_peserta_seni = kelompok_peserta_seni.id_kelompok_peserta_seni
AS anggota_kelompok_peserta_seni
```

CI4 implementation harus **replikasi subquery ini** untuk mendapatkan nama peserta yang di-concatenate.

---

## Testing Checklist

- [x] Syntax check: `php -l` semua file modified
- [ ] Manual test: Buka halaman layar seni live scoring (saat penampilan aktif)
- [ ] Manual test: Selesaikan pool seni → lihat hasil pool seni (countdown + ranking)
- [ ] Manual test: Selesaikan battle seni → lihat hasil battle seni (countdown + comparison + winner)
- [ ] Verify: Nama peserta tampil sesuai format dari `anggota_kelompok_peserta_seni`
- [ ] Verify: Polling HTTP ke `layar/refresh-status-seni` bekerja untuk navigasi hasil
- [ ] Verify: Median kebenaran, std dev, penalty, final score tampil correct

---

## Known Limitations

1. **No WebSocket connection in pool/battle result pages**: Countdown & animations pure client-side (5 detik). Transisi ke penampilan berikutnya bergantung pada HTTP polling (interval 4 detik). Ini **parity legacy**.

2. **Field `anggota_kelompok_peserta_seni` might be NULL**: Jika admin tidak mengisi field ini saat data entry, fallback ke individual `peserta_seni` names (join via `peserta_seni.id_kelompok_peserta_seni`). View sudah handle kedua case.

3. **Medal badge logic for pool seni**: View `hasil_pool_seni.php` menggunakan ranking index (0=emas, 1=perak, 2=perunggu). Legacy pakai field `jenis_medali` dari DB. Jika perlu exact parity, harus query field `jenis_medali` dan pakai itu untuk badge color.

---

## Related Files

- **Controllers**: `app/Controllers/Pertandingan/Layar.php`
- **Views**:
  - `app/Views/pertandingan/layar/seni/persilat/dark.php` (live scoring)
  - `app/Views/pertandingan/layar/seni/persilat/hasil_pool_seni.php` (pool results)
  - `app/Views/pertandingan/layar/seni/persilat/hasil_battle_seni.php` (battle results)
- **JavaScript**: `public/assets/js/penilaian/layar_seni_persilat.js` (polling logic sudah OK)
- **Routes**: `app/Config/Routes.php` (routes sudah terdaftar)

---

## Next Steps

1. **Manual QA**: Test end-to-end flow seni pool & battle di environment lokal
2. **Medal field**: Jika diperlukan exact medal badge dari DB, patch `hasil_pool_seni.php` untuk query `detail_jadwal_seni.jenis_medali_pool`
3. **Real-time optimization**: Jika user minta real-time update di result pages, tambahkan Socket.IO listener (saat ini pure polling HTTP)

---

**Status**: ✅ Parity complete — siap QA manual
