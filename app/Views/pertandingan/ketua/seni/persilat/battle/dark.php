<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/kp-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid min-vh-100 bg-black pb-4" id="mainContainer">
    <!-- HEADER -->
    <div class="row py-2 px-2">
        <div class="col-12 text-center">
            <p class="text-white mb-0 fw-bold text-uppercase">
                <?= $penampilan_seni_berlangsung->nama_seni ?? 'Seni' ?> —
                <?= $penampilan_seni_berlangsung->nama_kategori_usia ?? '' ?>
                <?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?>
                (Battle)
            </p>
        </div>
    </div>

    <!-- NAV TABS: Blue / Red / Summary -->
    <div class="nav-wrapper position-relative end-0">
        <ul class="nav nav-pills nav-fill p-1" role="tablist" id="tabNilai">
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 active text-white" data-bs-toggle="tab" href="#blue_corner"
                    role="tab" aria-selected="true" id="blueCornerNav">BLUE</a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 text-white" data-bs-toggle="tab" href="#red_corner"
                    role="tab" aria-selected="false" id="redCornerNav">RED</a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 text-white" data-bs-toggle="tab" href="#summary"
                    role="tab" aria-selected="false" id="summaryNav">SUMMARY</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- ═══════════ TAB BLUE ═══════════ -->
            <div class="tab-pane active" id="blue_corner" role="tabpanel">
                <?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
                <?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_biru): ?>

                <!-- Peserta Blue -->
                <div class="row my-3 justify-content-center">
                    <div class="col-10 px-4">
                        <div class="row bg-blue bg-gradient h-100">
                            <div class="col-12 justify-content-center d-flex flex-column py-2">
                                <p class="h5 text-decoration-underline text-truncate m-0 fw-bolder text-white text-center">
                                    <?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?>
                                </p>
                                <p class="text-truncate m-0 text-white text-sm fw-lighter text-center"><?= $penampilan_seni->nama_kontingen ?? '' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php $idPs = (int) $penampilan_seni->id_penampilan_seni; ?>
                <?php if (!empty($data_nilai[$idPs])): ?>
                <?php $juriList = $data_nilai[$idPs]; ?>

                <!-- Tabel Unsur Nilai Blue -->
                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table w-100 table-sm penampilan_seni_<?= $idPs ?> blue-corner">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center w-25 py-2">Unsur</th>
                                    <th class="text-center py-2" colspan="<?= count($juriList) ?>">Juri</th>
                                </tr>
                                <tr>
                                    <?php for ($i = 1; $i <= count($juriList); $i++): ?>
                                        <th class="text-center py-2"><?= $i ?></th>
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

                <!-- Sorted Jury Score Blue -->
                <div class="row shadow-lg penampilan_seni_<?= $idPs ?> blue-corner mb-1 px-2">
                    <div class="col-12 bg-dark py-2">
                        <p class="text-sm fw-bolder text-white text-center m-0 text-uppercase">Jury Score (Without penalty)</p>
                    </div>
                    <div class="col-12">
                        <div class="row urutan_total_nilai_juri">
                            <?php foreach ($juriList as $juri): ?>
                            <div class="col mb-3 kolom_total_nilai_<?= $idPs ?>">
                                <div class="row bg-dark">
                                    <div class="col-12 bg-gradient-dark"><p class="text-sm fw-bolder text-white text-center my-2 text-uppercase nomor_juri"></p></div>
                                    <div class="col-12 kolom_bobot_total_nilai">
                                        <p class="fw-bolder text-center text-white my-1 h5 total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>">0</p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Blue -->
                <div class="row penampilan_seni_<?= $idPs ?> blue-corner mb-2">
                    <div class="col-12 col-xl-6 px-3">
                        <div class="row">
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Median</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white median_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Penalty</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white hukuman_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Final Score</p></div><div class="col-12 bg-blue"><p class="fw-bolder text-white text-center my-1 h3 nilai_akhir_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Std. Deviation</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white standar_deviasi_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Median Kebenaran</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white kebenaran_median_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Time</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white waktu_<?= $idPs ?>">0</p></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6 px-3">
                        <div class="row"><div class="col-12">
                            <?php
                                $sampelHukuman = json_decode($juriList[0]->penilaian ?? '{}');
                                $hukumanBlue = $sampelHukuman->penilaian->hukuman ?? null;
                            ?>
                            <?php if ($hukumanBlue !== null): ?>
                                <?php foreach ($hukumanBlue as $jenisHukuman => $valueHukuman): ?>
                                <div class="row mb-1">
                                    <div class="col-8 bg-dark text-end"><p class="my-2 small text-white"><?= $valueHukuman->metadata->label ?? ucwords(str_replace('_', ' ', $jenisHukuman)) ?></p></div>
                                    <div class="col-4 bg-secondary d-flex align-items-center justify-content-center"><p class="fw-bolder text-center text-white my-1 h4 nilai_hukuman_<?= $jenisHukuman ?>">0</p></div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div></div>
                    </div>
                </div>

                <?php endif; ?>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- ═══════════ TAB RED ═══════════ -->
            <div class="tab-pane" id="red_corner" role="tabpanel">
                <?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
                <?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_merah): ?>

                <!-- Peserta Red -->
                <div class="row my-3 justify-content-center">
                    <div class="col-10 px-4">
                        <div class="row bg-red bg-gradient h-100">
                            <div class="col-12 justify-content-center d-flex flex-column py-2">
                                <p class="h5 text-decoration-underline text-truncate m-0 fw-bolder text-white text-center">
                                    <?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?>
                                </p>
                                <p class="text-truncate m-0 text-white text-sm fw-lighter text-center"><?= $penampilan_seni->nama_kontingen ?? '' ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php $idPs = (int) $penampilan_seni->id_penampilan_seni; ?>
                <?php if (!empty($data_nilai[$idPs])): ?>
                <?php $juriList = $data_nilai[$idPs]; ?>

                <!-- Tabel Unsur Nilai Red -->
                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table w-100 table-sm penampilan_seni_<?= $idPs ?> red-corner">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th rowspan="2" class="align-middle text-center w-25 py-2">Unsur</th>
                                    <th class="text-center py-2" colspan="<?= count($juriList) ?>">Juri</th>
                                </tr>
                                <tr>
                                    <?php for ($i = 1; $i <= count($juriList); $i++): ?>
                                        <th class="text-center py-2"><?= $i ?></th>
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

                <!-- Sorted Jury Score Red -->
                <div class="row shadow-lg penampilan_seni_<?= $idPs ?> red-corner mb-1 px-2">
                    <div class="col-12 bg-dark py-2">
                        <p class="text-sm fw-bolder text-white text-center m-0 text-uppercase">Jury Score (Without penalty)</p>
                    </div>
                    <div class="col-12">
                        <div class="row urutan_total_nilai_juri">
                            <?php foreach ($juriList as $juri): ?>
                            <div class="col mb-3 kolom_total_nilai_<?= $idPs ?>">
                                <div class="row bg-dark">
                                    <div class="col-12 bg-gradient-dark"><p class="text-sm fw-bolder text-white text-center my-2 text-uppercase nomor_juri"></p></div>
                                    <div class="col-12 kolom_bobot_total_nilai">
                                        <p class="fw-bolder text-center text-white my-1 h5 total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>">0</p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Red -->
                <div class="row penampilan_seni_<?= $idPs ?> red-corner mb-2">
                    <div class="col-12 col-xl-6 px-3">
                        <div class="row">
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Median</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white median_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Penalty</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white hukuman_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Final Score</p></div><div class="col-12 bg-red"><p class="fw-bolder text-white text-center my-1 h3 nilai_akhir_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Std. Deviation</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white standar_deviasi_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Median Kebenaran</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white kebenaran_median_<?= $idPs ?>">0</p></div></div>
                            </div>
                            <div class="col-4 px-2 mb-3">
                                <div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2 text-uppercase">Time</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white waktu_<?= $idPs ?>">0</p></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6 px-3">
                        <div class="row"><div class="col-12">
                            <?php
                                $sampelHukuman = json_decode($juriList[0]->penilaian ?? '{}');
                                $hukumanRed = $sampelHukuman->penilaian->hukuman ?? null;
                            ?>
                            <?php if ($hukumanRed !== null): ?>
                                <?php foreach ($hukumanRed as $jenisHukuman => $valueHukuman): ?>
                                <div class="row mb-1">
                                    <div class="col-8 bg-dark text-end"><p class="my-2 small text-white"><?= $valueHukuman->metadata->label ?? ucwords(str_replace('_', ' ', $jenisHukuman)) ?></p></div>
                                    <div class="col-4 bg-secondary d-flex align-items-center justify-content-center"><p class="fw-bolder text-center text-white my-1 h4 nilai_hukuman_<?= $jenisHukuman ?>">0</p></div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div></div>
                    </div>
                </div>

                <?php endif; ?>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- ═══════════ TAB SUMMARY ═══════════ -->
            <div class="tab-pane" id="summary" role="tabpanel">
                <div class="row px-2 justify-content-between">
                    <!-- BLUE SUMMARY -->
                    <div class="col-12 col-md-6 pe-md-4">
                        <?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
                        <?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_biru): ?>
                        <?php $idPs = (int) $penampilan_seni->id_penampilan_seni; ?>
                        <div class="row my-3 justify-content-center px-1">
                            <div class="col-12">
                                <div class="row bg-blue bg-gradient h-100">
                                    <div class="col-12 justify-content-center d-flex flex-column py-2">
                                        <p class="h5 text-decoration-underline text-truncate m-0 fw-bolder text-white text-center"><?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p>
                                        <p class="text-truncate m-0 text-white text-sm fw-lighter text-center"><?= $penampilan_seni->nama_kontingen ?? '' ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row penampilan_seni_<?= $idPs ?> blue-corner mb-2">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Median</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white median_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Penalty</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white hukuman_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Final Score</p></div><div class="col-12 bg-blue"><p class="fw-bolder text-white text-center my-1 h3 nilai_akhir_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Std. Dev</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white standar_deviasi_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Med. Kebenaran</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white kebenaran_median_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Time</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white waktu_<?= $idPs ?>">0</p></div></div></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- RED SUMMARY -->
                    <div class="col-12 col-md-6 ps-md-4">
                        <?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
                        <?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_merah): ?>
                        <?php $idPs = (int) $penampilan_seni->id_penampilan_seni; ?>
                        <div class="row my-3 justify-content-center px-1">
                            <div class="col-12">
                                <div class="row bg-red bg-gradient h-100">
                                    <div class="col-12 justify-content-center d-flex flex-column py-2">
                                        <p class="h5 text-decoration-underline text-truncate m-0 fw-bolder text-white text-center"><?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p>
                                        <p class="text-truncate m-0 text-white text-sm fw-lighter text-center"><?= $penampilan_seni->nama_kontingen ?? '' ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row penampilan_seni_<?= $idPs ?> red-corner mb-2">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Median</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white median_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Penalty</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white hukuman_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Final Score</p></div><div class="col-12 bg-red"><p class="fw-bolder text-white text-center my-1 h3 nilai_akhir_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Std. Dev</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white standar_deviasi_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Med. Kebenaran</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white kebenaran_median_<?= $idPs ?>">0</p></div></div></div>
                                    <div class="col-4 px-2 mb-3"><div class="row shadow-lg stat-card"><div class="col-12 bg-dark"><p class="h6 text-white text-center my-2">Time</p></div><div class="col-12"><p class="fw-bolder text-center my-1 h3 text-white waktu_<?= $idPs ?>">0</p></div></div></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
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

    <?php if ($battle_seni !== null && (int)$battle_seni->id_penampilan_seni_biru === (int)$penampilan_seni_berlangsung->id_penampilan_seni): ?>
    setTimeout(() => { document.getElementById('blueCornerNav').click(); }, 1000);
    <?php else: ?>
    setTimeout(() => { document.getElementById('redCornerNav').click(); }, 1000);
    <?php endif; ?>

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
