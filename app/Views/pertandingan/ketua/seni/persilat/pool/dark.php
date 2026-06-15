<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/kp-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid min-vh-100 bg-black" id="mainContainer">
    <!-- HEADER -->
    <div class="row py-2 px-2">
        <div class="col-12 text-center">
            <p class="text-white mb-0 fw-bold text-uppercase">
                <?= $penampilan_seni_berlangsung->nama_seni ?? 'Seni' ?> —
                <?= $penampilan_seni_berlangsung->nama_kategori_usia ?? '' ?>
                <?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?>
            </p>
        </div>
    </div>

    <!-- NAV TABS -->
    <div class="nav-wrapper position-relative end-0">
        <ul class="nav nav-pills nav-fill p-1" role="tablist" id="tabNilai">
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 active text-white" data-bs-toggle="tab" href="#now_performing"
                    role="tab" aria-selected="true" id="nowPerformingNav">Now Performing</a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 text-white" data-bs-toggle="tab" href="#summary"
                    role="tab" aria-selected="false" id="summaryNav">SUMMARY</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB: NOW PERFORMING -->
            <div class="tab-pane active" id="now_performing" role="tabpanel">

                <!-- Peserta Info -->
                <div class="row my-3 justify-content-center">
                    <div class="col-10 px-4">
                        <div class="row bg-warning bg-gradient h-100">
                            <div class="col-12 justify-content-center d-flex flex-column py-2">
                                <p class="h5 text-decoration-underline text-truncate m-0 fw-bolder text-white text-center">
                                    <?= str_replace('<br>', ' ', $penampilan_seni_berlangsung->anggota_kelompok_peserta_seni ?? '-') ?>
                                </p>
                                <p class="text-truncate m-0 text-white text-sm fw-lighter text-center">
                                    <?= $penampilan_seni_berlangsung->nama_kontingen ?? '-' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABEL UNSUR NILAI -->
                <?php if (!empty($data_nilai[$penampilan_seni_berlangsung->id_penampilan_seni])): ?>
                <?php $juriList = $data_nilai[$penampilan_seni_berlangsung->id_penampilan_seni]; ?>
                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table w-100 table-sm penampilan_seni_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center w-25 py-2">Unsur</th>
                                    <th class="text-center py-2" colspan="<?= count($juriList) ?>">Juri</th>
                                </tr>
                                <tr>
                                    <?php for ($idx = 1; $idx <= count($juriList); $idx++): ?>
                                        <th class="text-center py-2"><?= $idx ?></th>
                                    <?php endfor ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jenis_unsur_nilai as $jenis): ?>
                                <tr>
                                    <td><p class="mb-0 fw-bolder text-capitalize text-center"><?= ucwords(str_replace('_', ' ', $jenis)) ?></p></td>
                                    <?php foreach ($juriList as $juri): ?>
                                        <td class="fw-bold text-center <?= $jenis ?>_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
                                    <?php endforeach ?>
                                </tr>
                                <?php endforeach; ?>

                                <!-- Total Nilai -->
                                <tr class="fw-bolder">
                                    <td><p class="mb-0 fw-bolder text-capitalize text-center">Total Nilai</p></td>
                                    <?php foreach ($juriList as $juri): ?>
                                        <td class="fw-bold text-center total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
                                    <?php endforeach ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
                <!-- END TABEL UNSUR NILAI -->

                <!-- SORTED JURY SCORE -->
                <div class="row shadow-lg penampilan_seni_sorted penampilan_seni_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?> mb-1 px-2">
                    <div class="col-12 bg-dark py-2">
                        <p class="text-sm fw-bolder text-white text-center m-0 text-uppercase">Sorted Jury Score</p>
                    </div>
                    <div class="col-12">
                        <div class="row urutan_total_nilai_juri">
                            <?php if (!empty($data_nilai[$penampilan_seni_berlangsung->id_penampilan_seni])): ?>
                            <?php foreach ($data_nilai[$penampilan_seni_berlangsung->id_penampilan_seni] as $juri): ?>
                                <div class="col mb-3 kolom_total_nilai">
                                    <div class="row bg-dark">
                                        <div class="col-12 bg-gradient-dark">
                                            <p class="text-sm fw-bolder text-white text-center my-2 text-uppercase nomor_juri"></p>
                                        </div>
                                        <div class="col-12 kolom_bobot_total_nilai">
                                            <p class="fw-bolder text-center text-white my-1 h5 total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>">0</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- END SORTED JURY SCORE -->

                <!-- RINGKASAN NILAI: Median, Penalty, Final Score, Std Dev, Median Kebenaran, Time -->
                <div class="row penampilan_seni_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?> mb-2">
                    <div class="col-12 col-xl-6 px-3">
                        <div class="row">
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card">
                                    <div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Median</p></div>
                                    <div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white median_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">0</p></div>
                                </div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card">
                                    <div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Penalty</p></div>
                                    <div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white hukuman_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">0</p></div>
                                </div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card">
                                    <div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Final Score</p></div>
                                    <div class="col-12 bg-warning"><p class="fw-bolder text-white text-center my-1 h3 nilai_akhir_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">0</p></div>
                                </div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card">
                                    <div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Std. Deviation</p></div>
                                    <div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white standar_deviasi_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">0</p></div>
                                </div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card">
                                    <div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Median Kebenaran</p></div>
                                    <div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white kebenaran_median_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">0</p></div>
                                </div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card">
                                    <div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Time</p></div>
                                    <div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white waktu_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">0</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6 px-3">
                        <!-- HUKUMAN DETAIL -->
                        <div class="row">
                            <div class="col-12">
                                <?php if (!empty($data_nilai[$penampilan_seni_berlangsung->id_penampilan_seni])): ?>
                                <?php
                                    $sampelPenilaian = json_decode($data_nilai[$penampilan_seni_berlangsung->id_penampilan_seni][0]->penilaian ?? '{}');
                                    $hukumanList = $sampelPenilaian->penilaian->hukuman ?? null;
                                ?>
                                <?php if ($hukumanList !== null): ?>
                                    <?php foreach ($hukumanList as $jenisHukuman => $valueHukuman): ?>
                                    <div class="row mb-1">
                                        <div class="col-8 bg-dark text-end">
                                            <p class="my-2 text-sm text-white"><?= $valueHukuman->metadata->label ?? ucwords(str_replace('_', ' ', $jenisHukuman)) ?></p>
                                        </div>
                                        <div class="col-4 bg-secondary d-flex align-items-center justify-content-center">
                                            <p class="fw-bolder text-center text-white my-1 h4 nilai_hukuman_<?= $jenisHukuman ?>">0</p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END RINGKASAN -->

            </div>

            <!-- TAB: SUMMARY -->
            <div class="tab-pane" id="summary" role="tabpanel">
                <!-- Final Score Table -->
                <div class="row">
                    <div class="col-12 table-responsive">
                        <div class="card bg-dark border-secondary p-0 my-2">
                            <div class="card-header p-0 bg-dark">
                                <h6 class="card-title text-center text-white my-2">Final Score</h6>
                            </div>
                            <div class="card-body py-0 px-2 table-responsive">
                                <table class="table table-sm w-100" id="tabelSummaryPenampilan">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th class="align-middle text-center">No</th>
                                            <th class="align-middle text-center w-25">Nama</th>
                                            <th class="align-middle text-center">Median Kebenaran</th>
                                            <th class="align-middle text-center">Median</th>
                                            <th class="align-middle text-center">Penalty</th>
                                            <th class="align-middle text-center">Final Score</th>
                                            <th class="align-middle text-center">Time</th>
                                            <th class="align-middle text-center">Std Dev</th>
                                            <th class="align-middle text-center">Disq.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($semua_penampilan_seni as $idx => $pnSeni): ?>
                                        <tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>">
                                            <td class="text-center align-middle"><?= $idx + 1 ?></td>
                                            <td class="align-middle">
                                                <p class="mb-0 fw-bolder text-capitalize"><?= str_replace('<br>', ' ', $pnSeni->anggota_kelompok_peserta_seni ?? '-') ?></p>
                                                <p class="mb-0 text-sm"><?= $pnSeni->nama_kontingen ?? '' ?></p>
                                            </td>
                                            <td class="align-middle text-center kebenaran_median_<?= $pnSeni->id_penampilan_seni ?>"></td>
                                            <td class="align-middle text-center median_<?= $pnSeni->id_penampilan_seni ?>"></td>
                                            <td class="align-middle text-center hukuman_<?= $pnSeni->id_penampilan_seni ?>"></td>
                                            <td class="text-center align-middle nilai_akhir_<?= $pnSeni->id_penampilan_seni ?>"><?= number_format($pnSeni->nilai_akhir ?? 0, 3) ?></td>
                                            <td class="text-center align-middle waktu_<?= $pnSeni->id_penampilan_seni ?>"><?= date("i:s", $pnSeni->waktu_tampil ?? 0) ?></td>
                                            <td class="text-center align-middle standar_deviasi_<?= $pnSeni->id_penampilan_seni ?>"></td>
                                            <td class="align-middle keterangan_<?= $pnSeni->id_penampilan_seni ?>">
                                                <?= ($pnSeni->diskualifikasi ?? 0) == 1 ? '<span class="badge bg-danger">Disqualified</span>' : '' ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- All Jury Score Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-dark border-secondary p-0 my-2">
                            <div class="card-header p-0 bg-dark">
                                <h6 class="card-title text-center text-white my-2">All Jury Score</h6>
                            </div>
                            <div class="card-body py-0 px-2 table-responsive">
                                <table class="table table-sm w-100">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th class="align-middle text-center">No</th>
                                            <th class="align-middle text-center w-25">Nama</th>
                                            <?php $firstKey = array_key_first($data_nilai); ?>
                                            <?php if ($firstKey !== null): ?>
                                                <?php for ($idx = 1; $idx <= count($data_nilai[$firstKey]); $idx++): ?>
                                                    <th class="text-center"><?= $idx ?></th>
                                                <?php endfor ?>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($semua_penampilan_seni as $num => $pnSeni): ?>
                                        <tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>">
                                            <td class="text-center align-middle"><?= $num + 1 ?></td>
                                            <td class="align-middle">
                                                <p class="mb-0 fw-bolder text-capitalize"><?= str_replace('<br>', ' ', $pnSeni->anggota_kelompok_peserta_seni ?? '-') ?></p>
                                                <p class="mb-0 text-sm"><?= $pnSeni->nama_kontingen ?? '' ?></p>
                                            </td>
                                            <?php if (isset($data_nilai[(int) $pnSeni->id_penampilan_seni])): ?>
                                                <?php foreach ($data_nilai[(int) $pnSeni->id_penampilan_seni] as $penilaian): ?>
                                                    <td class="align-middle text-center nilai_akhir_juri_<?= $penilaian->id_perangkat_pertandingan ?> juri_<?= $penilaian->id_perangkat_pertandingan ?>"></td>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Per Unsur Nilai Tables -->
                <?php foreach ($jenis_unsur_nilai as $jenis): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card bg-dark border-secondary p-0 my-2">
                            <div class="card-header p-0 bg-dark">
                                <h6 class="card-title text-center text-white my-2"><?= ucwords(str_replace('_', ' ', $jenis)) ?></h6>
                            </div>
                            <div class="card-body py-0 px-2 table-responsive">
                                <table class="table table-sm w-100">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th class="align-middle text-center">No</th>
                                            <th class="align-middle text-center w-25">Nama</th>
                                            <?php if ($firstKey !== null): ?>
                                                <?php for ($idx = 1; $idx <= count($data_nilai[$firstKey]); $idx++): ?>
                                                    <th class="text-center"><?= $idx ?></th>
                                                <?php endfor ?>
                                            <?php endif; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($semua_penampilan_seni as $num => $pnSeni): ?>
                                        <tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>">
                                            <td class="text-center align-middle"><?= $num + 1 ?></td>
                                            <td class="align-middle">
                                                <p class="mb-0 fw-bolder text-capitalize"><?= str_replace('<br>', ' ', $pnSeni->anggota_kelompok_peserta_seni ?? '-') ?></p>
                                                <p class="mb-0 text-sm"><?= $pnSeni->nama_kontingen ?? '' ?></p>
                                            </td>
                                            <?php if (isset($data_nilai[(int) $pnSeni->id_penampilan_seni])): ?>
                                                <?php foreach ($data_nilai[(int) $pnSeni->id_penampilan_seni] as $penilaian): ?>
                                                    <td class="align-middle text-center <?= $jenis ?>_juri_<?= $penilaian->id_perangkat_pertandingan ?> juri_<?= $penilaian->id_perangkat_pertandingan ?>"></td>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/kp_seni_persilat.js') ?>"></script>
<script>
    var $data_nilai = <?= json_encode($data_nilai, JSON_NUMERIC_CHECK) ?>;
    var $penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung, JSON_NUMERIC_CHECK) ?>;
    var $semua_penampilan_seni = <?= json_encode($semua_penampilan_seni, JSON_NUMERIC_CHECK) ?>;
    var $autorefresh = true;

    $(document).ready(function() {
        ketua_pertandingan.init(
            <?= $penampilan_seni_berlangsung->id_penampilan_seni ?>,
            $data_nilai,
            $penampilan_seni_berlangsung,
            $semua_penampilan_seni,
            $autorefresh
        );
    });
</script>
<?= $this->endSection() ?>
