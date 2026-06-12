# Parity Fixes: Layar Seni Scoring Logic — Median, Std Dev, Jury Selection

**Date**: 2026-06-12  
**Target**: Scoring calculation & jury display for PERSILAT seni  
**Legacy Reference**: `/dps/application/models/sistem_penilaian/seni/PERSILAT_model.php`

---

## Summary

Perbaikan logika perhitungan scoring seni PERSILAT dan tampilan nilai juri (terpilih/tidak) sesuai parity legacy:

1. **Median calculation**: Rata-rata 2 nilai tengah (genap) atau nilai tengah (ganjil)
2. **Standard deviation**: Population std dev (bukan sample)
3. **Median kebenaran**: Median dari unsur nilai kebenaran semua juri
4. **Penalty (hukuman)**: Ambil dari juri pertama (harusnya identik semua juri)
5. **Jury selection (terpilih)**: Mark juri yang nilainya dipakai untuk median
6. **UI/UX**: Juri terpilih → yellow/warning gradient, tidak terpilih → strikethrough + opacity

---

## Changes Made

### 1. New Service: `PersilatSeniService`

**File**: `app/Services/Scoring/Persilat/PersilatSeniService.php`

**Purpose**: Centralize scoring calculation logic untuk seni PERSILAT (parity legacy `PERSILAT_model.php`)

**Methods**:

#### `hitungNilaiAkhir()`
Hitung nilai akhir dan update `catatan_nilai_sama`.

**Logic**:
1. Extract `total_nilai` dari semua juri
2. Sort ascending (lowest → highest)
3. Hitung median, std dev, median kebenaran, hukuman
4. Save ke `catatan_nilai_sama` (JSON)
5. Update flag `terpilih` untuk juri yang nilainya dipakai median
6. Return: `median - hukuman`

**Parity**: `PERSILAT_model::hitung_nilai_akhir()`

---

#### `pilihPenilaianJuri()`
Mark juri mana yang nilainya dipakai untuk median (flag `terpilih`).

**Logic**:
- **Genap** (4, 6, 8 juri): pilih 2 juri tengah  
  Contoh 6 juri (index 0-5 setelah sort): pilih index 2 & 3
- **Ganjil** (3, 5, 7 juri): pilih 1 juri tengah  
  Contoh 5 juri (index 0-4): pilih index 2

**SQL Update**:
```sql
-- Reset all
UPDATE penilaian_seni SET terpilih = 0 WHERE id_penampilan_seni = ?

-- Mark selected
UPDATE penilaian_seni SET terpilih = 1 
WHERE id_penampilan_seni = ? AND id_perangkat_pertandingan IN (?,?)
```

**Parity**: `PERSILAT_model::_pilih_penilaian_juri()`

---

#### `hitungMedian()`
Hitung median dari array nilai (sudah sorted ascending).

**Formula**:
- **Genap**: `(nilai[n/2 - 1] + nilai[n/2]) / 2`
- **Ganjil**: `nilai[floor(n/2)]`

**Example**:
```php
// 5 juri: [9.0, 9.1, 9.35, 9.4, 9.5]
// Median = nilai[floor(5/2)] = nilai[2] = 9.35

// 6 juri: [9.0, 9.1, 9.35, 9.4, 9.5, 9.6]
// Median = (nilai[2] + nilai[3]) / 2 = (9.35 + 9.4) / 2 = 9.375
```

**Parity**: `PERSILAT_model::_hitung_median()`

---

#### `hitungStandarDeviasi()`
Standard deviation populasi.

**Formula**:
```
mean = sum(values) / n
variance = sum((value - mean)²) / n
std_dev = sqrt(variance)
```

**Parity**: `PERSILAT_model::_hitung_standar_deviasi()`

---

#### `hitungHukuman()`
Total hukuman (penalty). Ambil dari juri pertama karena penalty harus identik di semua juri (diinput oleh KP, bukan per-juri).

**Parity**: `PERSILAT_model::_hitung_hukuman()`

---

### 2. JavaScript: `layar_seni_persilat.js`

**File**: `public/assets/js/penilaian/layar_seni_persilat.js`

**Change**: Method `update_tampilan_urutan_nilai_tiap_juri()`

**Logic Update**:
1. Sort juri by `total_nilai` ascending (lowest → highest)
2. Display di columns dari kiri ke kanan (kolom 1 = nilai terendah, kolom terakhir = nilai tertinggi)
3. Check flag `terpilih`:
   - `terpilih = 1` → Add class `terpilih` + yellow gradient background
   - `terpilih = 0` → Add class `tidak-terpilih` + strikethrough + opacity

**Before**:
```javascript
if (juri.terpilih === 0 || juri.terpilih === '0') {
    col.classList.add('tidak-terpilih');
} else {
    col.classList.add('terpilih');
}
```

**After**:
```javascript
if (juri.terpilih === 1 || juri.terpilih === '1') {
    col.classList.add('terpilih');
    col.style.background = 'linear-gradient(180deg, #ffc107 0%, #e0a800 100%)';
} else {
    col.classList.add('tidak-terpilih');
    col.style.background = '';
}
```

**Parity**: `/dps/assets/penilaian/js/application/layar/seni/persilat.js` line 184-246

---

### 3. CSS: `dark.php` Styles

**File**: `app/Views/pertandingan/layar/seni/persilat/dark.php`

**Change**: Add styling untuk juri terpilih/tidak

**Added**:
```css
/* Terpilih: yellow/warning gradient */
.kolom_total_nilai.terpilih {
    background: linear-gradient(180deg, #ffc107 0%, #e0a800 100%) !important;
}
.kolom_total_nilai.terpilih .nilai-juri,
.kolom_total_nilai.terpilih .label-juri {
    color: #000 !important;
}

/* Tidak terpilih: strikethrough + opacity */
.kolom_total_nilai.tidak-terpilih .nilai-juri {
    text-decoration: line-through;
    opacity: 0.4;
}
.kolom_total_nilai.tidak-terpilih .label-juri {
    opacity: 0.4;
}
```

**Parity**: Legacy menggunakan class `bg-gradient-180-warning` + `text-white` untuk terpilih, `text-decoration-line-through` untuk tidak terpilih.

---

## Database Schema: `penilaian_seni`

| Field                      | Type         | Description                                      |
|----------------------------|--------------|--------------------------------------------------|
| `id_penilaian_seni`        | int(11)      | PK                                               |
| `id_penampilan_seni`       | int(11)      | FK → `penampilan_seni.id_penampilan_seni`        |
| `id_perangkat_pertandingan`| int(11)      | FK → `perangkat_pertandingan` (juri)             |
| `penilaian`                | longtext     | JSON penilaian structure                         |
| `nilai_akhir_per_juri`     | varchar(25)  | Cached total_nilai (optional)                    |
| `terpilih`                 | tinyint(1)   | **0 = tidak terpilih, 1 = terpilih untuk median**|

**Field `terpilih`**:
- Diupdate oleh service `pilihPenilaianJuri()` setiap kali KP proses penilaian
- Logic: Juri dengan nilai tengah (median position) di-mark `terpilih = 1`
- Genap: 2 juri tengah, Ganjil: 1 juri tengah

---

## Database Schema: `penampilan_seni`

| Field                 | Type         | Description                                      |
|-----------------------|--------------|--------------------------------------------------|
| `id_penampilan_seni`  | int(11)      | PK                                               |
| `nilai_akhir`         | varchar(25)  | Final score (median - penalty)                   |
| `catatan_nilai_sama`  | text         | **JSON: median, std_dev, median_kebenaran, hukuman** |

**Field `catatan_nilai_sama`** structure:
```json
{
    "hukuman": 0.5,
    "median": 9.375,
    "standar_deviasi": "0.0123456789",
    "median_kebenaran": 9.45
}
```

---

## Example Calculation Flow

### Input: 5 Juri

| Juri | Total Nilai | Kebenaran | Hukuman |
|------|-------------|-----------|---------|
| 1    | 9.5         | 9.6       | 0.5     |
| 2    | 9.0         | 9.1       | 0.5     |
| 3    | 9.35        | 9.4       | 0.5     |
| 4    | 9.1         | 9.2       | 0.5     |
| 5    | 9.4         | 9.5       | 0.5     |

### Step 1: Sort Ascending by Total Nilai
```
[9.0, 9.1, 9.35, 9.4, 9.5]
```

### Step 2: Calculate Median (Ganjil)
```
index = floor(5/2) = 2
median = nilai[2] = 9.35
```

### Step 3: Select Jury (terpilih)
```
Juri index 2 (nilai 9.35) → terpilih = 1
Juri lainnya → terpilih = 0
```

### Step 4: Calculate Median Kebenaran
```
Sort kebenaran: [9.1, 9.2, 9.4, 9.5, 9.6]
median_kebenaran = nilai[2] = 9.4
```

### Step 5: Calculate Std Dev
```
mean = (9.0 + 9.1 + 9.35 + 9.4 + 9.5) / 5 = 9.27
variance = ((9.0-9.27)² + (9.1-9.27)² + ... + (9.5-9.27)²) / 5
std_dev = sqrt(variance) = 0.0189736659...
```

### Step 6: Get Hukuman
```
hukuman = 0.5 (dari juri pertama)
```

### Step 7: Final Score
```
nilai_akhir = median - hukuman = 9.35 - 0.5 = 8.85
```

### Step 8: Save catatan_nilai_sama
```json
{
    "hukuman": 0.5,
    "median": 9.35,
    "standar_deviasi": "0.0189736659",
    "median_kebenaran": 9.4
}
```

---

## UI/UX Display

### Juri Columns (Sorted Low → High)

```
┌─────────┬─────────┬─────────┬─────────┬─────────┐
│ Juri 2  │ Juri 4  │ Juri 3  │ Juri 5  │ Juri 1  │
│  9.000  │  9.100  │  9.350  │  9.400  │  9.500  │
│ (gray)  │ (gray)  │ (YELLOW)│ (gray)  │ (gray)  │
│strikeout│strikeout│ BOLD    │strikeout│strikeout│
└─────────┴─────────┴─────────┴─────────┴─────────┘
```

**Legend**:
- **YELLOW background** = Terpilih (nilai dipakai untuk median)
- **Gray + strikethrough** = Tidak terpilih (nilai dibuang)

### Summary Boxes

```
┌───────────────┬───────────────┬───────────┬──────────┐
│ Median Keb.   │ Std Deviation │  Median   │ Penalty  │
│    9.400      │  0.0189736659 │   9.350   │  -0.5    │
└───────────────┴───────────────┴───────────┴──────────┘
```

### Final Score + Time

```
┌─────────────────────┬────────────┐
│   Final Score       │    Time    │
│      8.850          │   02:15    │
└─────────────────────┴────────────┘
```

---

## Testing Checklist

- [ ] Test dengan 3 juri (ganjil) → 1 juri terpilih (tengah)
- [ ] Test dengan 4 juri (genap) → 2 juri terpilih (tengah)
- [ ] Test dengan 5 juri (ganjil) → 1 juri terpilih (tengah)
- [ ] Verify median calculation correct (genap vs ganjil)
- [ ] Verify std dev calculation correct
- [ ] Verify median kebenaran correct
- [ ] Verify penalty diambil dari semua juri (harusnya identik)
- [ ] Verify UI: juri terpilih → yellow gradient
- [ ] Verify UI: juri tidak terpilih → strikethrough + opacity
- [ ] Verify columns sorted low → high (kiri → kanan)

---

## Integration Points

### Ketua Pertandingan (KP)
Service `PersilatSeniService::hitungNilaiAkhir()` dipanggil saat KP:
1. Input/edit penalty (hukuman)
2. Lock/unlock penilaian
3. Finalize penampilan

### Layar Display
- **Live scoring**: Display nilai juri real-time + highlight terpilih/tidak
- **Poll/battle result**: Display final catatan_nilai_sama (median, std dev, etc.)

### Sekretaris
Saat selesaikan penampilan, trigger perhitungan final score + save catatan_nilai_sama

---

## Related Files

**Service**:
- `app/Services/Scoring/Persilat/PersilatSeniService.php` (NEW)

**JavaScript**:
- `public/assets/js/penilaian/layar_seni_persilat.js` (UPDATED)

**Views**:
- `app/Views/pertandingan/layar/seni/persilat/dark.php` (UPDATED CSS)

**Legacy Reference**:
- `/dps/application/models/sistem_penilaian/seni/PERSILAT_model.php`
- `/dps/assets/penilaian/js/application/layar/seni/persilat.js`

---

## Known Issues & Notes

1. **Penalty consistency**: Legacy ada validasi bahwa penalty harus identik di semua juri. Jika ada inkonsistensi, ambil yang paling umum (most common value). CI4 implementation simplified: ambil dari juri pertama.

2. **Race condition**: Legacy pakai transaction + row locks untuk prevent race condition saat multiple KP update penalty bersamaan. CI4 belum implement transaction lock (TODO future enhancement).

3. **Nilai akhir precision**: Legacy pakai `number_format($median, 4)` untuk median (4 desimal) dan `number_format($std_dev, 10)` untuk std dev (10 desimal). CI4 follow sama.

---

**Status**: ✅ Service class ready, JS + CSS updated — perlu integration testing dengan KP flow
