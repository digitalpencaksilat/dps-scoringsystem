<?php

namespace App\Services\Scoring\Persilat;

/**
 * PersilatTandingService — port 1:1 dari algoritma legacy
 * application/models/sistem_penilaian/tanding/PERSILAT_model.php (CI3).
 *
 * PRINSIP (lihat docs/RENCANA_MIGRASI_PENILAIAN_PERSILAT.md, Fase 2):
 * - Murni komputasi, TANPA akses database, agar dapat di-unit-test.
 * - Operasi I/O (load baris penilaian, simpan hasil) ditangani caller
 *   (controller / model), bukan service ini.
 * - Mempertahankan perilaku legacy persis: konstanta interval verifikasi = 2 detik,
 *   verifikasi minimal 2 juri, soft-delete (is_deleted), pembobotan nilai
 *   (1=pukulan, 2=tendangan, 3=jatuhan, negatif=hukuman), dan struktur ringkasan.
 *
 * Struktur baris penilaian (input/output) — array of stdClass dengan properti:
 *   - id_pertandingan
 *   - id_perangkat_pertandingan
 *   - penilaian_merah : string JSON
 *   - penilaian_biru  : string JSON
 *
 * Struktur JSON penilaian_(merah|biru):
 *   {
 *     "ronde_pertandingan": {
 *       "1": { "rincian": [ {nilai,status,warna,id_nilai,tag,timestamp,is_deleted,deleted_at}, ... ],
 *              "catatan": {"binaan": 0|1|2}? },
 *       "2": {...}, "3": {...}
 *     }
 *   }
 */
class PersilatTandingService
{
    /** Palet warna untuk nilai terverifikasi (identik legacy _daftar_kode_warna). */
    private array $daftarKodeWarna = [
        '#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FFD133',
        '#33FFF5', '#8D33FF', '#FF9633', '#33FF8D', '#FF33F5',
    ];

    private int $intervalVerifikasiBiru  = 2;
    private int $intervalVerifikasiMerah = 2;

    private function getKodeWarna(int $key): string
    {
        $jumlah = count($this->daftarKodeWarna);
        if ($key >= $jumlah) {
            return $this->daftarKodeWarna[$key % $jumlah];
        }

        return $this->daftarKodeWarna[$key];
    }

    /**
     * Pipeline utama: dari baris penilaian semua juri -> baris terupdate + ringkasan.
     * Parity dengan legacy hitung_skor_atlet().
     *
     * @param object[] $penilaianSemuaJuri
     * @return array{rows: object[], ringkasan: array, skor_merah: int, skor_biru: int}
     */
    public function hitungSkorAtlet(array $penilaianSemuaJuri): array
    {
        $penilaianSemuaJuri = $this->resetMetadataPenilaian($penilaianSemuaJuri);
        $penilaianSemuaJuri = $this->verifikasiPenilaian($penilaianSemuaJuri);

        $penilaianVerified  = $this->getPenilaianVerified($penilaianSemuaJuri);

        $penilaianSemuaJuri = $this->beriWarna($penilaianSemuaJuri, $penilaianVerified);
        $penilaianSemuaJuri = $this->hitungRingkasanNilaiPerJuri($penilaianSemuaJuri);

        $ringkasanNilai = $this->getRingkasanNilai($penilaianVerified, $penilaianSemuaJuri);

        return [
            'rows'       => $penilaianSemuaJuri,
            'ringkasan'  => $ringkasanNilai,
            'skor_merah' => (int) $ringkasanNilai['semua_ronde']['merah']['nilai_akhir'],
            'skor_biru'  => (int) $ringkasanNilai['semua_ronde']['biru']['nilai_akhir'],
        ];
    }

    /**
     * Reset status/tag/warna/id_nilai semua entry (skip yang soft-deleted).
     * Parity legacy _reset_metadata_penilaian().
     */
    private function resetMetadataPenilaian(array $penilaianSemuaJuri): array
    {
        foreach ($penilaianSemuaJuri as $indexJuri => $penilaianJuri) {
            foreach (['penilaian_merah', 'penilaian_biru'] as $kolom) {
                $decoded = json_decode($penilaianJuri->$kolom);
                $semuaRonde = $decoded->ronde_pertandingan;

                foreach ($semuaRonde as $ronde => $perRonde) {
                    foreach ($perRonde->rincian as $keyEntry => $entry) {
                        if (isset($entry->is_deleted) && $entry->is_deleted === true) {
                            continue;
                        }
                        $entry->status  = 'input';
                        $entry->warna   = null;
                        $entry->id_nilai = null;
                        $entry->tag     = false;
                        $decoded->ronde_pertandingan->$ronde->rincian[$keyEntry] = $entry;
                    }
                }

                $penilaianJuri->$kolom = json_encode($decoded);
                $penilaianSemuaJuri[$indexJuri] = $penilaianJuri;
            }
        }

        return $penilaianSemuaJuri;
    }

    /**
     * Verifikasi nilai: sebuah entry menjadi 'verified' bila ada >= 2 juri
     * meng-input nilai sama dalam selisih waktu <= interval (2 detik).
     * Parity legacy _verifikasi_penilaian().
     */
    private function verifikasiPenilaian(array $penilaianSemuaJuri): array
    {
        foreach (['merah', 'biru'] as $sudut) {
            $kolom    = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';
            $interval = $sudut === 'merah' ? $this->intervalVerifikasiMerah : $this->intervalVerifikasiBiru;

            foreach ($penilaianSemuaJuri as $indexJuri => $penilaianJuri) {
                $idNilai = 1;
                $semuaRonde = json_decode($penilaianJuri->$kolom)->ronde_pertandingan;

                foreach ($semuaRonde as $ronde => $perRonde) {
                    foreach ($perRonde->rincian as $keyEntry => $entry) {
                        if (isset($entry->is_deleted) && $entry->is_deleted === true) {
                            continue;
                        }

                        $jumlahJuriInput = 1;

                        if ($entry->status !== 'verified') {
                            foreach ($penilaianSemuaJuri as $indexPembanding => $juriPembanding) {
                                $rondePembanding = json_decode($juriPembanding->$kolom)->ronde_pertandingan->$ronde;

                                foreach ($rondePembanding->rincian as $keyPembanding => $entryPembanding) {
                                    if (isset($entryPembanding->is_deleted) && $entryPembanding->is_deleted === true) {
                                        continue;
                                    }

                                    if (
                                        $indexJuri !== $indexPembanding
                                        && $entry->nilai === $entryPembanding->nilai
                                        && $entryPembanding->status !== 'verified'
                                        && abs($entry->timestamp - $entryPembanding->timestamp) <= $interval
                                    ) {
                                        $decodedPembanding = json_decode($juriPembanding->$kolom);
                                        $decodedPembanding->ronde_pertandingan->$ronde->rincian[$keyPembanding]->status   = 'verified';
                                        $decodedPembanding->ronde_pertandingan->$ronde->rincian[$keyPembanding]->id_nilai = $idNilai;
                                        $juriPembanding->$kolom = json_encode($decodedPembanding);
                                        $penilaianSemuaJuri[$indexPembanding] = $juriPembanding;

                                        $jumlahJuriInput++;
                                        break;
                                    } elseif ($entryPembanding->timestamp - $entry->timestamp > $interval) {
                                        break;
                                    }
                                }

                                if ($jumlahJuriInput >= 2) {
                                    $entry->status   = 'verified';
                                    $entry->id_nilai = $idNilai;
                                }
                            }

                            $decoded = json_decode($penilaianJuri->$kolom);
                            $decoded->ronde_pertandingan->$ronde->rincian[$keyEntry] = $entry;
                            $penilaianJuri->$kolom = json_encode($decoded);
                            $penilaianSemuaJuri[$indexJuri] = $penilaianJuri;

                            $idNilai++;
                        }
                    }
                }
            }
        }

        return $penilaianSemuaJuri;
    }

    /**
     * Kelompokkan entry terverifikasi (per sudut) untuk pewarnaan.
     * Parity legacy _get_penilaian_verified().
     */
    private function getPenilaianVerified(array $penilaianSemuaJuri): array
    {
        $daftar = ['merah' => [], 'biru' => []];

        foreach (['merah', 'biru'] as $sudut) {
            $kolom    = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';
            $interval = $sudut === 'merah' ? $this->intervalVerifikasiMerah : $this->intervalVerifikasiBiru;
            $keyVerified = 1;

            foreach ($penilaianSemuaJuri as $indexJuri => $penilaianJuri) {
                $semuaRonde = json_decode($penilaianJuri->$kolom)->ronde_pertandingan;

                foreach ($semuaRonde as $ronde => $perRonde) {
                    foreach ($perRonde->rincian as $keyEntry => $entry) {
                        if (isset($entry->is_deleted) && $entry->is_deleted === true) {
                            continue;
                        }

                        if ($entry->status === 'verified' && $entry->tag !== true) {
                            $daftar[$sudut][$keyVerified] = [];
                            $daftar[$sudut][$keyVerified][] = [
                                'index_juri'  => $indexJuri,
                                'ronde'       => $ronde,
                                'index_nilai' => $keyEntry,
                                'entry_nilai' => $entry,
                            ];

                            foreach ($penilaianSemuaJuri as $indexPembanding => $juriPembanding) {
                                $rondePembanding = json_decode($juriPembanding->$kolom)->ronde_pertandingan->$ronde;

                                foreach ($rondePembanding->rincian as $keyPembanding => $entryPembanding) {
                                    if (isset($entryPembanding->is_deleted) && $entryPembanding->is_deleted === true) {
                                        continue;
                                    }

                                    if (
                                        $indexJuri !== $indexPembanding
                                        && $entry->nilai === $entryPembanding->nilai
                                        && abs($entry->timestamp - $entryPembanding->timestamp) <= $interval
                                        && $entryPembanding->status === 'verified'
                                        && $entryPembanding->tag !== true
                                        && $entry->id_nilai == $entryPembanding->id_nilai
                                    ) {
                                        $daftar[$sudut][$keyVerified][] = [
                                            'index_juri'  => $indexPembanding,
                                            'ronde'       => $ronde,
                                            'index_nilai' => $keyPembanding,
                                            'entry_nilai' => $entryPembanding,
                                        ];

                                        $decodedPembanding = json_decode($juriPembanding->$kolom);
                                        $decodedPembanding->ronde_pertandingan->$ronde->rincian[$keyPembanding]->tag = true;
                                        $juriPembanding->$kolom = json_encode($decodedPembanding);
                                        $penilaianSemuaJuri[$indexPembanding] = $juriPembanding;
                                    }
                                }
                            }

                            $keyVerified++;
                        }
                    }
                }
            }
        }

        ksort($daftar['merah']);
        ksort($daftar['biru']);
        $daftar['merah'] = array_values($daftar['merah']);
        $daftar['biru']  = array_values($daftar['biru']);

        return $daftar;
    }

    /**
     * Beri warna pada entry terverifikasi berdasarkan kelompoknya.
     * Parity legacy _beri_warna().
     */
    private function beriWarna(array $penilaianSemuaJuri, array $penilaianVerified): array
    {
        foreach ($penilaianVerified as $sudut => $kumpulanSudut) {
            $kolom = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';

            foreach ($kumpulanSudut as $keyNilaiVerified => $kumpulanPenilaian) {
                foreach ($kumpulanPenilaian as $penilaian) {
                    $decoded = json_decode($penilaianSemuaJuri[$penilaian['index_juri']]->$kolom);
                    $decoded->ronde_pertandingan->{$penilaian['ronde']}->rincian[$penilaian['index_nilai']]->warna
                        = $this->getKodeWarna($keyNilaiVerified);
                    $penilaianSemuaJuri[$penilaian['index_juri']]->$kolom = json_encode($decoded);
                }
            }
        }

        return $penilaianSemuaJuri;
    }

    /**
     * Hitung ringkasan & kategori nilai per juri (per ronde + semua ronde).
     * Parity legacy _hitung_ringkasan_nilai_per_juri().
     */
    private function hitungRingkasanNilaiPerJuri(array $penilaianSemuaJuri): array
    {
        foreach (['merah', 'biru'] as $sudut) {
            $kolom = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';

            foreach ($penilaianSemuaJuri as $indexJuri => $penilaianJuri) {
                $decoded    = json_decode($penilaianJuri->$kolom);
                $semuaRonde = $decoded->ronde_pertandingan;

                $ringkasanSemua = ['total_nilai_terinput' => 0, 'total_nilai' => 0, 'total_hukuman' => 0, 'nilai_akhir' => 0];
                $kategoriSemua  = ['pukulan' => 0, 'tendangan' => 0, 'jatuhan' => 0, 'hukuman' => 0];

                foreach ($semuaRonde as $ronde => $perRonde) {
                    $ringkasanRonde = ['total_nilai_terinput' => 0, 'total_nilai' => 0, 'total_hukuman' => 0, 'nilai_akhir' => 0];
                    $kategoriRonde  = ['pukulan' => 0, 'tendangan' => 0, 'jatuhan' => 0, 'hukuman' => 0];

                    foreach ($perRonde->rincian as $entry) {
                        if (isset($entry->is_deleted) && $entry->is_deleted === true) {
                            continue;
                        }

                        if ($entry->status === 'verified') {
                            if ($entry->nilai == 1) {
                                $ringkasanRonde['total_nilai'] += 1;
                                $kategoriRonde['pukulan']      += 1;
                            } elseif ($entry->nilai == 2) {
                                $ringkasanRonde['total_nilai'] += 2;
                                $kategoriRonde['tendangan']    += 1;
                            } elseif ($entry->nilai == 3) {
                                $ringkasanRonde['total_nilai'] += 3;
                                $kategoriRonde['jatuhan']      += 1;
                            } elseif ($entry->nilai < 0) {
                                $ringkasanRonde['total_hukuman'] += $entry->nilai;
                                $kategoriRonde['hukuman']        += 1;
                            }
                        }

                        $ringkasanRonde['total_nilai_terinput'] += $entry->nilai;
                    }

                    $ringkasanRonde['nilai_akhir'] = $ringkasanRonde['total_nilai'] + $ringkasanRonde['total_hukuman'];
                    $decoded->ronde_pertandingan->$ronde->ringkasan      = $ringkasanRonde;
                    $decoded->ronde_pertandingan->$ronde->kategori_nilai = $kategoriRonde;

                    $ringkasanSemua['total_nilai_terinput'] += $ringkasanRonde['total_nilai_terinput'];
                    $ringkasanSemua['total_nilai']          += $ringkasanRonde['total_nilai'];
                    $ringkasanSemua['total_hukuman']        += $ringkasanRonde['total_hukuman'];
                    $ringkasanSemua['nilai_akhir']          += $ringkasanRonde['nilai_akhir'];

                    $kategoriSemua['pukulan']   += $kategoriRonde['pukulan'];
                    $kategoriSemua['tendangan'] += $kategoriRonde['tendangan'];
                    $kategoriSemua['jatuhan']   += $kategoriRonde['jatuhan'];
                    $kategoriSemua['hukuman']   += $kategoriRonde['hukuman'];
                }

                $decoded->ringkasan      = $ringkasanSemua;
                $decoded->kategori_nilai = $kategoriSemua;

                $penilaianJuri->$kolom = json_encode($decoded);
                $penilaianSemuaJuri[$indexJuri] = $penilaianJuri;
            }
        }

        return $penilaianSemuaJuri;
    }

    /**
     * Ringkasan nilai pertandingan (dipakai KP & Layar): jumlah repetisi per
     * kategori per sudut + nilai_akhir. Hanya 1 nilai terhitung per kelompok
     * verifikasi (berapapun juri yang input). Parity legacy _get_ringkasan_nilai().
     */
    private function getRingkasanNilai(array $penilaianVerified, array $penilaianSemuaJuri): array
    {
        $template = static fn (): array => [
            'pukulan' => 0, 'tendangan' => 0, 'jatuhan' => 0,
            'binaan_1' => 0, 'binaan_2' => 0,
            'teguran_1' => 0, 'teguran_2' => 0,
            'peringatan_1' => 0, 'peringatan_2' => 0,
            'nilai_akhir' => 0,
        ];

        $ringkasan = [
            'semua_ronde' => ['merah' => $template(), 'biru' => $template()],
            'per_ronde'   => [
                '1' => ['merah' => $template(), 'biru' => $template()],
                '2' => ['merah' => $template(), 'biru' => $template()],
                '3' => ['merah' => $template(), 'biru' => $template()],
            ],
        ];

        foreach ($penilaianVerified as $sudut => $kumpulanSudut) {
            foreach ($kumpulanSudut as $kumpulanPenilaian) {
                foreach ($kumpulanPenilaian as $penilaian) {
                    $nilai = $penilaian['entry_nilai']->nilai;
                    $ronde = $penilaian['ronde'];

                    if (intval($nilai) === 1) {
                        $ringkasan['per_ronde'][$ronde][$sudut]['pukulan'] += 1;
                        $ringkasan['semua_ronde'][$sudut]['pukulan']       += 1;
                    } elseif (intval($nilai) === 2) {
                        $ringkasan['per_ronde'][$ronde][$sudut]['tendangan'] += 1;
                        $ringkasan['semua_ronde'][$sudut]['tendangan']       += 1;
                    } elseif ($nilai == -1) {
                        $ringkasan['per_ronde'][$ronde][$sudut]['teguran_1'] += 1;
                        $ringkasan['semua_ronde'][$sudut]['teguran_1']       += 1;
                    } elseif ($nilai == -2) {
                        $ringkasan['per_ronde'][$ronde][$sudut]['teguran_2'] += 1;
                        $ringkasan['semua_ronde'][$sudut]['teguran_2']       += 1;
                    } elseif ($nilai == -5) {
                        $ringkasan['per_ronde'][$ronde][$sudut]['peringatan_1'] += 1;
                        $ringkasan['semua_ronde'][$sudut]['peringatan_1']       += 1;
                    } elseif ($nilai == -10) {
                        $ringkasan['per_ronde'][$ronde][$sudut]['peringatan_2'] += 1;
                        $ringkasan['semua_ronde'][$sudut]['peringatan_2']       += 1;
                    } elseif ($nilai == 3) {
                        $ringkasan['per_ronde'][$ronde][$sudut]['jatuhan'] += 1;
                        $ringkasan['semua_ronde'][$sudut]['jatuhan']       += 1;
                    }

                    $ringkasan['per_ronde'][$ronde][$sudut]['nilai_akhir'] += intval($nilai);
                    $ringkasan['semua_ronde'][$sudut]['nilai_akhir']       += intval($nilai);
                    // Hanya 1 nilai terhitung per kelompok verifikasi.
                    break;
                }
            }
        }

        // Binaan diambil dari sampel juri pertama (catatan->binaan per ronde).
        $this->akumulasiBinaan($ringkasan, $penilaianSemuaJuri, 'biru');
        $this->akumulasiBinaan($ringkasan, $penilaianSemuaJuri, 'merah');

        return $ringkasan;
    }

    /**
     * Akumulasi binaan dari sampel juri pertama (parity legacy: index 0).
     */
    private function akumulasiBinaan(array &$ringkasan, array $penilaianSemuaJuri, string $sudut): void
    {
        $kolom  = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';
        $sampel = json_decode($penilaianSemuaJuri[0]->$kolom)->ronde_pertandingan;

        foreach ($sampel as $ronde => $nilaiRonde) {
            if (! isset($nilaiRonde->catatan->binaan)) {
                continue;
            }

            $binaan = intval($nilaiRonde->catatan->binaan);
            if ($binaan === 2) {
                $ringkasan['per_ronde'][$ronde][$sudut]['binaan_2'] += 1;
                $ringkasan['per_ronde'][$ronde][$sudut]['binaan_1'] += 1;
                $ringkasan['semua_ronde'][$sudut]['binaan_2']       += 1;
                $ringkasan['semua_ronde'][$sudut]['binaan_1']       += 1;
            } elseif ($binaan === 1) {
                $ringkasan['per_ronde'][$ronde][$sudut]['binaan_1'] += 1;
                $ringkasan['semua_ronde'][$sudut]['binaan_1']       += 1;
            }
        }
    }

    /**
     * Validasi struktur JSON penilaian tanding.
     * Parity legacy _validasi_format_json_penilaian() (versi setelah ringkasan dihitung).
     */
    public function validasiFormatJson(string|object $json): bool
    {
        $data = is_object($json) ? $json : json_decode($json);

        if (! is_object($data) || ! isset($data->ronde_pertandingan)) {
            return false;
        }

        foreach (['1', '2', '3'] as $ronde) {
            if (! property_exists($data->ronde_pertandingan, $ronde)) {
                return false;
            }
            $rondeData = $data->ronde_pertandingan->$ronde;
            if (! isset($rondeData->rincian) || ! is_array($rondeData->rincian)) {
                return false;
            }
        }

        return isset($data->kategori_nilai) && isset($data->ringkasan);
    }

    /**
     * Tambah / hapus satu entry nilai pada baris penilaian SATU juri (pure).
     * Parity legacy proses_penilaian_juri_incremental() bagian manipulasi JSON
     * (locking + persistence DB ditangani caller / model).
     *
     * @param object $row    baris penilaian_tanding satu juri (punya penilaian_merah/biru)
     * @param string $sudut  'merah' | 'biru'
     * @param string $ronde  '1' | '2' | '3'
     * @param array  $entry  ['action' => 'remove'] untuk hapus, atau
     *                       ['nilai' => int, 'status' => 'verified'|'input', ...] untuk tambah
     * @param int|null $now  timestamp server (default time()); param untuk testability
     * @return object baris yang sudah dimodifikasi (penilaian_merah/biru terupdate)
     */
    public function ubahEntryNilaiJuri(object $row, string $sudut, string $ronde, array $entry, ?int $now = null): object
    {
        $now   = $now ?? time();
        $kolom = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';

        $decoded  = json_decode($row->$kolom);
        $rincian  = &$decoded->ronde_pertandingan->$ronde->rincian;

        if (isset($entry['action']) && $entry['action'] === 'remove') {
            // Soft-delete entry non-deleted terakhir (bukan hard remove).
            for ($i = count($rincian) - 1; $i >= 0; $i--) {
                if (! isset($rincian[$i]->is_deleted) || $rincian[$i]->is_deleted === false) {
                    $rincian[$i]->is_deleted = true;
                    $rincian[$i]->deleted_at = $now;
                    break;
                }
            }
        } else {
            // Tambah entry baru dengan timestamp server-side + flag is_deleted=false.
            $entry['timestamp']  = $now;
            $entry['is_deleted'] = false;
            $rincian[] = (object) $entry;
        }

        $row->$kolom = json_encode($decoded);

        return $row;
    }

    /**
     * Validasi nilai input juri PERSILAT — server-side guard (anti payload ilegal).
     * Nilai legal: 1 (pukulan), 2 (tendangan), 3 (jatuhan).
     * Hukuman/binaan diinput oleh KP, bukan lewat jalur ini.
     */
    public function isNilaiJuriLegal(int $nilai): bool
    {
        return in_array($nilai, [1, 2, 3], true);
    }

    /**
     * Proses penilaian oleh Ketua Pertandingan (hukuman/teguran/peringatan/binaan/
     * jatuhan + penghapusannya) yang diterapkan ke SELURUH baris juri (pure).
     * Parity legacy proses_penilaian_kp().
     *
     * @param object[] $rows         baris penilaian semua juri
     * @param string   $sudut        'merah'|'biru'
     * @param string   $ronde        ronde aktif ('1'|'2'|'3')
     * @param string   $mode         'binaan'|'binaan_1'|'binaan_2'|'teguran'|'teguran_1'|
     *                               'teguran_2'|'peringatan'|'peringatan_1'|'peringatan_2'|
     *                               'jatuhan'|'serangan'|'hukuman'
     * @param string|int $jumlah     nilai (int) untuk menambah, atau 'hapus' untuk menghapus
     * @param int|null $now          timestamp server (testability)
     * @return object[] baris juri yang sudah dimodifikasi
     */
    public function prosesPenilaianKp(array $rows, string $sudut, string $ronde, string $mode, string|int $jumlah, ?int $now = null): array
    {
        $now   = $now ?? time();
        $kolom = $sudut === 'merah' ? 'penilaian_merah' : 'penilaian_biru';

        foreach ($rows as $i => $row) {
            $decoded   = json_decode($row->$kolom);
            $semuaRonde = $decoded->ronde_pertandingan;
            $rinciRef  = &$semuaRonde->$ronde->rincian;

            if ($jumlah !== 'hapus') {
                if ($mode !== 'binaan') {
                    // Tambah entry hukuman/jatuhan (verified langsung; diinput KP).
                    $rinciRef[] = (object) [
                        'status'     => 'verified',
                        'warna'      => null,
                        'nilai'      => (int) $jumlah,
                        'timestamp'  => $now,
                        'is_deleted' => false,
                    ];
                } else {
                    if (! isset($semuaRonde->$ronde->catatan)) {
                        $semuaRonde->$ronde->catatan = (object) ['binaan' => 0];
                    }
                    $semuaRonde->$ronde->catatan->binaan = (int) $jumlah;
                }
            } else {
                // Penghapusan.
                if ($mode === 'binaan' || $mode === 'binaan_1') {
                    if (! isset($semuaRonde->$ronde->catatan)) {
                        $semuaRonde->$ronde->catatan = (object) ['binaan' => 0];
                    }
                    $semuaRonde->$ronde->catatan->binaan = 0;
                } elseif ($mode === 'binaan_2') {
                    if (! isset($semuaRonde->$ronde->catatan)) {
                        $semuaRonde->$ronde->catatan = (object) ['binaan' => 1];
                    }
                    $semuaRonde->$ronde->catatan->binaan = 1;
                } else {
                    for ($k = count($rinciRef) - 1; $k >= 0; $k--) {
                        $entry = $rinciRef[$k];
                        if (isset($entry->is_deleted) && $entry->is_deleted === true) {
                            continue;
                        }
                        if (
                            ($mode === 'hukuman'     && $entry->nilai < 0)
                            || ($mode === 'teguran'    && $entry->nilai < 0 && $entry->nilai >= -2)
                            || ($mode === 'teguran_1'  && $entry->nilai == -1)
                            || ($mode === 'teguran_2'  && $entry->nilai == -2)
                            || ($mode === 'peringatan'   && $entry->nilai <= -5 && $entry->nilai >= -10)
                            || ($mode === 'peringatan_1' && $entry->nilai == -5)
                            || ($mode === 'peringatan_2' && $entry->nilai == -10)
                            || ($mode === 'jatuhan'    && $entry->nilai == 3)
                            || ($mode === 'serangan'   && $entry->nilai == 1 && $entry->status === 'verified')
                            || ($mode === 'serangan'   && $entry->nilai == 2 && $entry->status === 'verified')
                        ) {
                            $rinciRef[$k]->is_deleted = true;
                            $rinciRef[$k]->deleted_at = $now;
                            break;
                        }
                    }
                }
            }

            unset($rinciRef);
            $decoded->ronde_pertandingan = $semuaRonde;
            $row->$kolom = json_encode($decoded);
            $rows[$i] = $row;
        }

        return $rows;
    }
}
