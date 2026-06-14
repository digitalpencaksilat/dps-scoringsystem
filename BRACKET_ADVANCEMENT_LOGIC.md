# SENI BATTLE Bracket Advancement Logic: CI3 vs CI4 Comparison

## Executive Summary

This document extracts the complete bracket advancement logic for SENI BATTLE matches from legacy CodeIgniter 3 and compares it with what the CI4 BattleSeniModel currently implements. The CI3 system performs a complex multi-step orchestration after a winner is chosen, while CI4 takes a minimalist approach.

---

## CI3 LEGACY SYSTEM: Complete Flow

### Entry Point: `Sekretaris_pertandingan_model::selesai_battle_seni()`

**File:** `/application/models/pertandingan/Sekretaris_pertandingan_model.php` (lines 50-71)

```php
public function selesai_battle_seni($id_penampilan_seni_pemenang, $jenis_kemenangan, $battle_seni)
{
    /**
     * Fungsi ini digunakan untuk mengerjakan 3 tugas sekaligus, yaitu menyelesaikan battle_seni,
     * menginput medali dan update bagan
     */

    $this->db->trans_start();

    // Step 1: Update battle_seni record with winner and winning type
    $this->Battle_seni_model->update($battle_seni->id_battle_seni, [
        'id_penampilan_seni_pemenang' => $id_penampilan_seni_pemenang,
        'jenis_kemenangan' => $jenis_kemenangan
    ]);

    // Step 2: Input medali (medals)
    $this->Kompetisi_seni_model->input_medali_battle_seni($id_penampilan_seni_pemenang, $battle_seni);
    
    // Step 3: Input atlet ke battle selanjutnya (advance to next round)
    $this->Kompetisi_seni_model->input_atlet_ke_battle_seni_selanjutnya($id_penampilan_seni_pemenang, $battle_seni);
    
    // Step 4: Update bagan (bracket visualization)
    $this->Kompetisi_seni_model->update_bagan_battle_seni($id_penampilan_seni_pemenang, $battle_seni);

    $this->db->trans_complete();

    return true;
}
```

### Step 1: Update Battle Record

**File:** `battle_seni` table

Updates the specific battle with:
- `id_penampilan_seni_pemenang` - ID of winning performance/group
- `jenis_kemenangan` - Type of win (e.g., 'poin', 'BYE', 'diskualifikasi')

---

### Step 2: Input Medal (`Kompetisi_seni_model::input_medali_battle_seni()`)

**File:** `/application/models/resources/Kompetisi_seni_model.php` (lines 340-351)

```php
public function input_medali_battle_seni($id_penampilan_seni_pemenang, $battle_seni)
{
    $model_name = $this->_get_sistem_bagan_model('Sistem_gugur_tunggal');
    $this->load->model('resources/bagan/Sistem_gugur_tunggal_model', 'Sistem_gugur_tunggal_model');
    return $this->{$model_name}->input_medali_battle_seni($id_penampilan_seni_pemenang, $battle_seni);
}
```

**Delegated to:** `Sistem_gugur_tunggal_model::input_medali_battle_seni()`

**File:** `/application/models/resources/bagan/Sistem_gugur_tunggal_model.php` (lines 1100-1165)

```php
public function input_medali_battle_seni($id_penampilan_seni_pemenang, $battle_seni)
{
    /**
     * Fungsi ini hanya ditugaskan untuk menginput medali, 
     * dapat digunakan oleh sekretaris pertandingan maupun sekretaris (admin)
     */
    $penampilan_seni_pemenang = $this->Penampilan_seni_model->find($id_penampilan_seni_pemenang);
    $kompetisi_seni = $this->Kompetisi_seni_model->find($battle_seni->id_kompetisi_seni);
    
    if ($battle_seni->perhitungan_medali == 1) {
        $id_penampilan_seni_kalah = ($id_penampilan_seni_pemenang == $battle_seni->id_penampilan_seni_merah) 
            ? $battle_seni->id_penampilan_seni_biru 
            : $battle_seni->id_penampilan_seni_merah;
        $penampilan_seni_kalah = $this->Penampilan_seni_model->find($id_penampilan_seni_kalah);
        $babak = $battle_seni->babak;
        
        if ($babak == "Final") {
            // GOLD MEDAL for final winner
            $this->Perolehan_medali_seni_model->delete(["id_kelompok_peserta_seni" => $penampilan_seni_pemenang->id_kelompok_peserta_seni]);
            $this->Perolehan_medali_seni_model->create([
                "id_kelompok_peserta_seni" => $penampilan_seni_pemenang->id_kelompok_peserta_seni,
                "jenis_medali" => "emas"
            ]);

            // SILVER MEDAL for final loser
            $this->Perolehan_medali_seni_model->delete(["id_kelompok_peserta_seni" => $penampilan_seni_kalah->id_kelompok_peserta_seni]);
            $this->Perolehan_medali_seni_model->create([
                "id_kelompok_peserta_seni" => $penampilan_seni_kalah->id_kelompok_peserta_seni,
                "jenis_medali" => "perak"
            ]);
            
        } else if ($babak == "Semi Final") {
            if ($battle_seni->juara_tiga_bersama == 1 || ($battle_seni->juara_tiga_bersama == 0 && $kompetisi_seni->jumlah_kelompok_peserta_seni < 4)) {
                /**
                 * JOINT THIRD PLACE: loser gets bronze
                 * winner advances to final
                 */
                $this->Perolehan_medali_seni_model->delete(["id_kelompok_peserta_seni" => $penampilan_seni_kalah->id_kelompok_peserta_seni]);
                $this->Perolehan_medali_seni_model->create([
                    "id_kelompok_peserta_seni" => $penampilan_seni_kalah->id_kelompok_peserta_seni,
                    "jenis_medali" => "perunggu"
                ]);
            }
            /**
             * WITHOUT JOINT THIRD PLACE: no medal assigned yet
             * loser goes to 3rd place playoff
             */
            
        } else if ($babak == "Perebutan Juara Tiga") {
            // BRONZE MEDAL for 3rd place winner
            $this->Perolehan_medali_seni_model->delete(["id_kelompok_peserta_seni" => $penampilan_seni_pemenang->id_kelompok_peserta_seni]);
            $this->Perolehan_medali_seni_model->create([
                "id_kelompok_peserta_seni" => $penampilan_seni_pemenang->id_kelompok_peserta_seni,
                "jenis_medali" => "perunggu"
            ]);
            
        } else {
            /**
             * ELIMINATION ROUND (penyisihan)
             * Only award bronze to losers if configured
             */
            if ($battle_seni->semua_dapat_medali == 1) {
                $this->Perolehan_medali_seni_model->delete(["id_kelompok_peserta_seni" => $penampilan_seni_kalah->id_kelompok_peserta_seni]);
                $this->Perolehan_medali_seni_model->create([
                    "id_kelompok_peserta_seni" => $penampilan_seni_kalah->id_kelompok_peserta_seni,
                    "jenis_medali" => "perunggu"
                ]);
            }
        }
    } else {
        return true;
    }
}
```

**Tables Modified:**
- `perolehan_medali_seni` - INSERT/DELETE operations
  - `id_kelompok_peserta_seni`
  - `jenis_medali` ('emas', 'perak', 'perunggu')

**Logic Summary:**
- **Final round:** Winner gets GOLD, loser gets SILVER
- **Semi-Final with joint 3rd:** Loser gets BRONZE
- **Semi-Final without joint 3rd:** No medal (goes to 3rd place playoff)
- **3rd Place Playoff:** Winner gets BRONZE
- **Elimination rounds:** Only if `semla_dapat_medali=1`, losers get BRONZE

---

### Step 3: Advance to Next Battle (`input_atlet_ke_battle_seni_selanjutnya()`)

**File:** `/application/models/resources/Kompetisi_seni_model.php` (lines 326-337)

```php
public function input_atlet_ke_battle_seni_selanjutnya($id_penampilan_seni_pemenang, $battle_seni)
{
    $model_name = $this->_get_sistem_bagan_model('Sistem_gugur_tunggal');
    $this->load->model('resources/bagan/Sistem_gugur_tunggal_model', 'Sistem_gugur_tunggal_model');
    return $this->{$model_name}->input_atlet_ke_battle_seni_selanjutnya($id_penampilan_seni_pemenang, $battle_seni);
}
```

**Delegated to:** `Sistem_gugur_tunggal_model::input_atlet_ke_battle_seni_selanjutnya()`

**File:** `/application/models/resources/bagan/Sistem_gugur_tunggal_model.php` (lines 914-1009)

```php
public function input_atlet_ke_battle_seni_selanjutnya($id_penampilan_seni_pemenang, $battle_seni)
{
    /**
     * Fungsi ini digunakan untuk menginputkan pesilat ke pertandingan selanjutnya
     */
    if ($battle_seni->nomor_battle !== NULL && $battle_seni->nomor_battle_selanjutnya !== NULL) {

        // Find the next battle in the bracket
        $battle_seni_selanjutnya = $this->Battle_seni_model->find([
            'nomor_battle' => $battle_seni->nomor_battle_selanjutnya,
            'battle_seni.id_kompetisi_seni' => $battle_seni->id_kompetisi_seni
        ]);

        if ($battle_seni->babak == 'Semi Final' && $battle_seni->juara_tiga_bersama == 0) {
            /**
             * SEMI-FINAL in non-joint-3rd system:
             * Loser creates NEW penampilan_seni record for 3rd place playoff
             */
            $id_penampilan_seni_kalah = ($id_penampilan_seni_pemenang == $battle_seni->id_penampilan_seni_merah) 
                ? $battle_seni->id_penampilan_seni_biru 
                : $battle_seni->id_penampilan_seni_merah;
            
            $penampilan_seni_kalah = $this->Penampilan_seni_model->find($id_penampilan_seni_kalah);
            
            // CREATE new penampilan_seni for 3rd place battle
            $new_penampilan_seni_kalah = $this->Penampilan_seni_model->create([
                'id_kelompok_peserta_seni' => $penampilan_seni_kalah->id_kelompok_peserta_seni,
                'babak' => 'Perebutan Juara Tiga'
            ]); // returns ID

            if ($new_penampilan_seni_kalah !== FALSE) {
                // Determine which corner (blue/red) based on odd/even match number
                if ($battle_seni->nomor_battle % 2 == 1) {
                    $update_data['id_penampilan_seni_biru'] = $new_penampilan_seni_kalah;
                } else {
                    $update_data['id_penampilan_seni_merah'] = $new_penampilan_seni_kalah;
                }

                // Find the 3rd place playoff battle
                $battle_seni_perebutan_juara_tiga = $this->Battle_seni_model->find([
                    'babak' => 'Perebutan Juara Tiga',
                    'battle_seni.id_kompetisi_seni' => $battle_seni->id_kompetisi_seni
                ]);

                // Get match details
                $detail_jadwal_seni_perebutan_juara_tiga = $this->Detail_jadwal_seni_model->find(
                    ['id_battle_seni' => $battle_seni_perebutan_juara_tiga->id_battle_seni]
                );
                
                // UPDATE the 3rd place battle with new loser
                $this->Battle_seni_model->update([
                    'babak' => 'Perebutan Juara Tiga',
                    'battle_seni.id_kompetisi_seni' => $battle_seni->id_kompetisi_seni
                ], $update_data);
                
                // Assign referee/judges to 3rd place battle
                if ($detail_jadwal_seni_perebutan_juara_tiga !== NULL) {
                    $this->Penilaian_seni_model->tugaskan_wasit_juri(
                        $new_penampilan_seni_kalah, 
                        $detail_jadwal_seni_perebutan_juara_tiga->id_gelanggang
                    );
                }
            } else {
                return FALSE;
            }
        }

        // If not final, advance winner to next round
        if ($battle_seni->babak !== 'Final') {

            $detail_jadwal_seni_selanjutnya = $this->Detail_jadwal_seni_model->find(
                ['id_battle_seni' => $battle_seni_selanjutnya->id_battle_seni]
            );
            
            $penampilan_seni_pemenang = $this->Penampilan_seni_model->find($id_penampilan_seni_pemenang);
            
            // CREATE new penampilan_seni record for next round
            $new_penampilan_seni_pemenang = $this->Penampilan_seni_model->create([
                'id_kelompok_peserta_seni' => $penampilan_seni_pemenang->id_kelompok_peserta_seni,
                'babak' => $battle_seni_selanjutnya->babak
            ]); // returns ID

            // Determine corner placement based on current match number parity
            if ($battle_seni->nomor_battle % 2 == 1) {
                $update_data['id_penampilan_seni_biru'] = $new_penampilan_seni_pemenang;
            } else {
                $update_data['id_penampilan_seni_merah'] = $new_penampilan_seni_pemenang;
            }

            // UPDATE next battle with winner
            $this->Battle_seni_model->update([
                'nomor_battle' => $battle_seni->nomor_battle_selanjutnya,
                'battle_seni.id_kompetisi_seni' => $battle_seni->id_kompetisi_seni
            ], $update_data);

            // Assign referee/judges to next battle
            if ($detail_jadwal_seni_selanjutnya !== NULL) {
                $this->Penilaian_seni_model->tugaskan_wasit_juri(
                    $new_penampilan_seni_pemenang, 
                    $detail_jadwal_seni_selanjutnya->id_gelanggang
                );
            }
        }
    }
    return true;
}
```

**Tables Modified:**
- `penampilan_seni` - INSERT operations
  - Creates new record with `id_kelompok_peserta_seni`, `babak`
- `battle_seni` - UPDATE operations
  - `id_penampilan_seni_biru` or `id_penampilan_seni_merah`

**Tables Indirectly Modified:**
- `penilaian_seni` - referee/judge assignment (via `tugaskan_wasit_juri()`)

**Logic Summary:**
- If Semi-Final with non-joint-3rd AND has loser: Create new `penampilan_seni` with "Perebutan Juara Tiga", update 3rd place battle
- If not Final: Create new `penampilan_seni` for next round, update next battle with winner in appropriate corner
- Corner assignment based on match parity (odd=blue, even=red)
- Assign judges/referees to updated battles

---

### Step 4: Update Bracket Visualization (`update_bagan_battle_seni()`)

**File:** `/application/models/resources/Kompetisi_seni_model.php` (lines 307-324)

```php
public function update_bagan_battle_seni($id_penampilan_seni_pemenang, $battle_seni)
{
    /**
     * @params (int) $id_pemenang
     * @params (object) hasil query find dari Battle_seni_model
     * Fungsi ini digunakan untuk update bagan / menginputkan pemenang ke bagan selanjutnya
     * TODO : Menangani berbagai macam format perlombaan (gugur tunggal, ganda, kompetisi) 
     */

    $model_name = $this->_get_sistem_bagan_model('Sistem_gugur_tunggal');
    $this->load->model('resources/bagan/Sistem_gugur_tunggal_model', 'Sistem_gugur_tunggal_model');
    $bagan_terupdate =  $this->{$model_name}->update_bagan_battle_seni($id_penampilan_seni_pemenang, $battle_seni);

    // UPDATE kompetisi_seni.bagan_battle_seni JSON field
    $data = array('bagan_battle_seni' => json_encode($bagan_terupdate));
    $this->Kompetisi_seni_model->update($battle_seni->id_kompetisi_seni, $data);

    return true;
}
```

**Delegated to:** `Sistem_gugur_tunggal_model::update_bagan_battle_seni()`

**File:** `/application/models/resources/bagan/Sistem_gugur_tunggal_model.php` (lines 1011-1098)

```php
public function update_bagan_battle_seni($id_penampilan_seni_pemenang, $battle_seni)
{
    $kompetisi_seni = $this->Kompetisi_seni_model->find($battle_seni->id_kompetisi_seni);
    $bagan = json_decode($kompetisi_seni->bagan_battle_seni);
    $nomor_battle = $battle_seni->nomor_battle;

    if ($bagan !== NULL && isset($bagan->results)) {
        $jumlah_battle_seni = count(($bagan->results[0][0]));

        $penampilan_seni_biru = $this->Penampilan_seni_model->find($battle_seni->id_penampilan_seni_biru);
        $penampilan_seni_merah = $this->Penampilan_seni_model->find($battle_seni->id_penampilan_seni_merah);

        $counter_battle_seni = 1; // Counter for match numbering
        $round_index = 0; // Round index (1/8 final, 1/4 final, etc.)

        // Iterate through bracket structure
        for ($i = $jumlah_battle_seni; $i >= 1; $i /= 2) {
            for ($battle_seni_bagan = 0; $battle_seni_bagan < $i; $battle_seni_bagan++) {
                
                // Regular match (not 3rd place playoff)
                if ($nomor_battle == $counter_battle_seni) {
                    // Found match in bracket, store scores
                    $bagan->results[0][$round_index][$battle_seni_bagan][0] = intval($penampilan_seni_merah->nilai_akhir);
                    $bagan->results[0][$round_index][$battle_seni_bagan][1] = intval($penampilan_seni_biru->nilai_akhir);

                    // Determine visual winner indicator (ensures winner has higher score visually)
                    if ($id_penampilan_seni_pemenang == $battle_seni->id_penampilan_seni_merah) {
                        // RED CORNER WINS
                        if (floatval($penampilan_seni_biru->nilai_akhir) >= floatval($penampilan_seni_merah->nilai_akhir)) {
                            // Equal/disqualification: show 0-1 (red wins)
                            $bagan->results[0][$round_index][$battle_seni_bagan][0] = 0;
                            $bagan->results[0][$round_index][$battle_seni_bagan][1] = 1;
                        } else {
                            // Normal: show actual scores with red higher
                            $bagan->results[0][$round_index][$battle_seni_bagan][0] = floatval($penampilan_seni_biru->nilai_akhir);
                            $bagan->results[0][$round_index][$battle_seni_bagan][1] = floatval($penampilan_seni_merah->nilai_akhir);
                        }
                    } else {
                        // BLUE CORNER WINS
                        if (floatval($penampilan_seni_merah->nilai_akhir) >= floatval($penampilan_seni_biru->nilai_akhir)) {
                            // Equal/disqualification: show 1-0 (blue wins)
                            $bagan->results[0][$round_index][$battle_seni_bagan][0] = 1;
                            $bagan->results[0][$round_index][$battle_seni_bagan][1] = 0;
                        } else {
                            // Normal: show actual scores with blue higher
                            $bagan->results[0][$round_index][$battle_seni_bagan][0] = floatval($penampilan_seni_biru->nilai_akhir);
                            $bagan->results[0][$round_index][$battle_seni_bagan][1] = floatval($penampilan_seni_merah->nilai_akhir);
                        }
                    }
                    // EXIT LOOPS
                    break 2;
                }

                // 3rd place playoff handling
                if ($battle_seni->juara_tiga_bersama == 0) {
                    if ($i == 1) { // Final round
                        if ($id_penampilan_seni_pemenang == $battle_seni->id_penampilan_seni_merah) {
                            // RED WINS 3RD PLACE
                            $bagan->results[0][$round_index][($battle_seni_bagan + 1)][0] = floatval($penampilan_seni_biru->nilai_akhir);
                            $bagan->results[0][$round_index][($battle_seni_bagan + 1)][1] = floatval($penampilan_seni_merah->nilai_akhir);
                        } else {
                            // BLUE WINS 3RD PLACE
                            $bagan->results[0][$round_index][($battle_seni_bagan + 1)][0] = floatval($penampilan_seni_biru->nilai_akhir);
                            $bagan->results[0][$round_index][($battle_seni_bagan + 1)][1] = floatval($penampilan_seni_merah->nilai_akhir);
                        }
                        break 2;
                    }
                }

                $counter_battle_seni++;
            }
            $round_index++;
        }
    } else {
        return TRUE;
    }

    return $bagan;
}
```

**Tables Modified:**
- `kompetisi_seni` - UPDATE operation
  - `bagan_battle_seni` (JSON field) - Updates visual scores in bracket

**Logic Summary:**
- Traverses bracket structure to find matching battle number
- Updates `bagan->results` array with final scores
- Adjusts score display to ensure winner shows higher visual score
- For 3rd place playoff: Updates array position immediately after final
- Returns updated bracket structure (JSON-encoded before saving)

---

## CI3 Summary: Tables Touched After "Pilih Pemenang"

| Table | Operation | Fields Modified |
|-------|-----------|-----------------|
| `battle_seni` | UPDATE | `id_penampilan_seni_pemenang`, `jenis_kemenangan` |
| `battle_seni` | UPDATE | `id_penampilan_seni_biru` or `id_penampilan_seni_merah` (next round) |
| `battle_seni` | UPDATE | `id_penampilan_seni_biru` or `id_penampilan_seni_merah` (3rd place) |
| `penampilan_seni` | INSERT | New records for next round / 3rd place |
| `perolehan_medali_seni` | DELETE/INSERT | Medal assignments based on round |
| `kompetisi_seni` | UPDATE | `bagan_battle_seni` (JSON) |
| `penilaian_seni` | INSERT | Judge/referee assignments (indirect) |

---

## CI4 CURRENT IMPLEMENTATION

### Entry Point: `SekretarisPertandingan::pilihPemenangBattleSeni()`

**File:** `/app/Controllers/Pertandingan/SekretarisPertandingan.php` (lines 980-1000)

```php
/**
 * Pilih Pemenang Battle Seni. Parity legacy pilih_pemenang_battle_seni().
 */
public function pilihPemenangBattleSeni(int $idPenampilanSeni)
{
    $db = \Config\Database::connect();
    $idPemenang = (int) $this->request->getPost('id_penampilan_seni_pemenang');
    $jenisKemenangan = (string) ($this->request->getPost('jenis_kemenangan') ?: 'poin');

    // Find battle associated with this performance
    $battle = $db->table('battle_seni')
        ->where('id_penampilan_seni_biru', $idPenampilanSeni)
        ->orWhere('id_penampilan_seni_merah', $idPenampilanSeni)
        ->first();

    if ($battle === null) {
        return $this->response->setJSON(['status' => false, 'message' => 'Battle tidak ditemukan.']);
    }

    // ONLY CALL: BattleSeniModel::setPemenang()
    $this->battleSeniModel->setPemenang((int) $battle->id_battle_seni, $idPemenang, $jenisKemenangan);

    return $this->response
        ->setHeader('X-CSRF-TOKEN', csrf_hash())
        ->setJSON(['status' => true, 'csrf_hash' => csrf_hash()]);
}
```

### Implementation: `BattleSeniModel::setPemenang()`

**File:** `/app/Models/BattleSeniModel.php` (lines 19-50)

```php
public function setPemenang(int $idBattle, int $idPenampilanPemenang, string $jenisKemenangan = 'poin'): bool
{
    $battle = $this->find($idBattle);
    if (! $battle) {
        return false;
    }

    // Step 1: Update current battle with winner
    $this->update($idBattle, [
        'id_penampilan_seni_pemenang' => $idPenampilanPemenang,
        'jenis_kemenangan'            => $jenisKemenangan,
    ]);

    // Step 2: ONLY if there's a next battle, advance winner
    if (! empty($battle['nomor_battle_selanjutnya'])) {
        $nextBattle = $this->where('id_kompetisi_seni', $battle['id_kompetisi_seni'])
                           ->where('nomor_battle', $battle['nomor_battle_selanjutnya'])
                           ->first();

        if ($nextBattle) {
            // Determine which corner (blue/red) is empty and place winner there
            if ($nextBattle['id_penampilan_seni_biru'] === null) {
                $this->update($nextBattle['id_battle_seni'], [
                    'id_penampilan_seni_biru' => $idPenampilanPemenang,
                ]);
            } elseif ($nextBattle['id_penampilan_seni_merah'] === null) {
                $this->update($nextBattle['id_battle_seni'], [
                    'id_penampilan_seni_merah' => $idPenampilanPemenang,
                ]);
            }
        }
    }

    return true;
}
```

### CI4 Summary: Tables Touched

| Table | Operation | Fields Modified |
|-------|-----------|-----------------|
| `battle_seni` | UPDATE | `id_penampilan_seni_pemenang`, `jenis_kemenangan` |
| `battle_seni` | UPDATE | `id_penampilan_seni_biru` or `id_penampilan_seni_merah` (next round only) |

---

## Side-by-Side Comparison

### What CI3 Does (Complete Orchestration)

1. ✅ Update current battle with winner
2. ✅ Assign medals based on round/config
3. ✅ Create new `penampilan_seni` records for next round
4. ✅ Create new `penampilan_seni` records for 3rd place playoff (if applicable)
5. ✅ Update next battle(s) with new performance IDs
6. ✅ Update 3rd place battle with new performance ID (if applicable)
7. ✅ Assign judges/referees to updated battles
8. ✅ Update bracket visualization JSON
9. ✅ Handle judge assignment for all affected battles
10. ✅ All within database transaction

### What CI4 Does (Minimal)

1. ✅ Update current battle with winner
2. ✅ Update next battle's empty corner with winner ID (if exists)
3. ❌ NO medal assignment
4. ❌ NO new `penampilan_seni` records created
5. ❌ NO 3rd place handling
6. ❌ NO judge/referee assignment
7. ❌ NO bracket visualization update
8. ❌ NO transaction wrapping

---

## Critical Differences

| Aspect | CI3 | CI4 |
|--------|-----|-----|
| **Transactionality** | ✅ Wrapped in `trans_start/trans_complete` | ❌ No explicit transaction |
| **Medal Assignment** | ✅ Complex logic per round | ❌ None |
| **Penampilan Creation** | ✅ Creates records for next round & 3rd place | ❌ None |
| **Judge Assignment** | ✅ `tugaskan_wasit_juri()` called | ❌ None |
| **Bracket Update** | ✅ JSON in `bagan_battle_seni` | ❌ None |
| **3rd Place Handling** | ✅ Full support for non-joint scenarios | ❌ None |
| **Corner Placement Logic** | ✅ Match parity-based (odd/even) | ❌ Simple first-free approach |
| **Code Lines** | ~250 LOC across 3 methods | ~30 LOC in 1 method |
| **Models Involved** | 8+ model classes | 1 model class |

---

## Data Flow Diagram: CI3

```
pilih_pemenang_battle_seni()
    ↓
selesai_battle_seni($id_penampilan_pemenang, $jenis_kemenangan, $battle_seni)
    ├─ Battle_seni_model->update() [Set winner + type]
    │   ↓ battle_seni.id_penampilan_seni_pemenang ← $id_penampilan_pemenang
    │   ↓ battle_seni.jenis_kemenangan ← $jenis_kemenangan
    │
    ├─ Kompetisi_seni_model->input_medali_battle_seni()
    │   ├─ Sistem_gugur_tunggal_model->input_medali_battle_seni()
    │   │   ├─ IF Final: emas (winner) + perak (loser)
    │   │   ├─ IF Semi-Final + joint-3rd: perunggu (loser)
    │   │   ├─ IF 3rd-Place-Playoff: perunggu (winner)
    │   │   └─ IF Elimination + all_get_medals: perunggu (loser)
    │   └─ perolehan_medali_seni: DELETE + INSERT
    │
    ├─ Kompetisi_seni_model->input_atlet_ke_battle_seni_selanjutnya()
    │   └─ Sistem_gugur_tunggal_model->input_atlet_ke_battle_seni_selanjutnya()
    │       ├─ Penampilan_seni_model->create() [New perf for next round]
    │       ├─ Penampilan_seni_model->create() [New perf for 3rd place if applicable]
    │       ├─ Battle_seni_model->update() [Next battle: id_penampilan_seni_biru/merah]
    │       ├─ Battle_seni_model->update() [3rd place battle: id_penampilan_seni_biru/merah]
    │       ├─ Penilaian_seni_model->tugaskan_wasit_juri() [Next battle judges]
    │       └─ Penilaian_seni_model->tugaskan_wasit_juri() [3rd place judges]
    │
    └─ Kompetisi_seni_model->update_bagan_battle_seni()
        ├─ Sistem_gugur_tunggal_model->update_bagan_battle_seni()
        │   └─ Parse JSON, find match in bracket, update scores
        └─ Kompetisi_seni_model->update() [bagan_battle_seni JSON]
```

---

## Missing in CI4 Implementation

To achieve full CI3 parity in CI4, the following must be implemented:

1. **Medal Assignment Service** - Logic to determine medal type per round
2. **Performance Creation** - Factory to create new `penampilan_seni` records
3. **Judge Assignment** - Service to assign referees/judges
4. **Bracket Visualization** - JSON update logic for `bagan_battle_seni`
5. **3rd Place Support** - Conditional logic for non-joint-3rd scenarios
6. **Transaction Wrapping** - Ensure atomicity across all operations
7. **Corner Placement** - Match parity-based logic (odd=blue, even=red)

---

## Conclusion

The CI3 system implements a **complete tournament orchestration engine** that handles:
- Result recording
- Medal assignment (dynamic per round/configuration)
- Performance progression (creates new records for next rounds)
- Referee management
- Bracket visualization update
- 3rd place tournament support

The CI4 implementation is **skeletal**, handling only:
- Result recording in current battle
- Direct advancement of winner ID to next battle's empty slot

**Recommendation:** CI4 needs a service layer (`PersilatSeniService` or similar) to restore full tournament logic before replacing CI3 for production battle workflows.

