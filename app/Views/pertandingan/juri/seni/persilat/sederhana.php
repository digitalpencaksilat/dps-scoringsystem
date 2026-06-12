<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idPenampilan = (int) $penampilan_seni->id_penampilan_seni;
    $aksesLocked  = ($akses_penilaian === 'ditutup');

    // Determine step for kebenaran
    $step = 0.01;
    if (isset($data_nilai->penilaian->unsur_nilai->kebenaran->metadata->step)) {
        $step = (float) $data_nilai->penilaian->unsur_nilai->kebenaran->metadata->step;
    }
?>

<div class="container-fluid min-vh-100 bg-black p-0" id="juri-seni-app">
    <style>
        @keyframes blink-animation {
            0% { opacity: 1; }
            50% { opacity: 0.4; }
            100% { opacity: 1; }
        }
        .blink-indicator {
            animation: blink-animation 2s ease-in-out infinite;
        }
        #offline-indicator { opacity: 0.75; z-index: 9999; }
    </style>

    <!-- Online/Offline Indicator -->
    <div id="offline-indicator" class="position-absolute top-0 end-0 mt-2 me-2 shadow-sm rounded px-3 py-1 fw-bold text-white bg-success blink-indicator">
        <small><i class="fas fa-wifi me-1"></i> Online</small>
    </div>

    <!-- ═══ Header Bar ═══ -->
    <div class="row justify-content-center mb-3">
        <div class="col-md-5 col-12 <?= $color_accent ?> py-2">
            <p class="h5 text-truncate m-0 fw-bolder text-white">
                <?php foreach ($peserta_seni as $ps): ?>
                    - <?= esc($ps->nama_pendaftar) ?> -
                <?php endforeach ?>
            </p>
            <p class="text-truncate m-0 text-white text-sm fw-lighter">
                <?= esc($peserta_seni[0]->nama_kontingen ?? '-') ?>
            </p>
        </div>
        <div class="col-md-2 col-12 bg-gradient-180-gray-dark py-2 d-flex align-items-center justify-content-center">
            <input class="form-control bg-transparent text-white border-0 fw-bolder h2 py-0 my-1 text-center nilai_akhir"
                disabled type="text" value="0" style="margin: 0;">
        </div>
        <div class="col-md-5 bg-white py-2">
            <p class="mb-2 text-uppercase mt-1 lh-1 text-end">
                <?= esc(strtoupper(session()->get('nama_perangkat') ?? 'JURI')) ?>
                <?php if ($partai_seni): ?>
                    - Match No <?= esc($partai_seni->nomor_partai ?? '') ?>
                <?php endif; ?>
            </p>
            <p class="h5 m-0 lh-1 text-end">
                <?= esc($penampilan_seni->nama_kategori_usia ?? '') ?> - <?= ucwords($penampilan_seni->jenis_kelamin ?? '') ?>
                <?= strtoupper($penampilan_seni->jenis_seni ?? '') ?>
            </p>
        </div>
    </div>

    <!-- ═══ Main Scoring Area ═══ -->
    <div class="row">
        <div class="col-12 min-vh-75 d-flex flex-column justify-content-center">

            <?php if (isset($data_nilai->penilaian->unsur_nilai->kebenaran)): ?>
            <!-- Kebenaran Section -->
            <div class="row mb-2 g-2" style="min-height: 180px;">
                <!-- Ready Button (kiri) -->
                <div class="col-4 d-flex">
                    <button class="btn w-100 <?= $status_ready ? 'btn-success' : 'btn-primary' ?> text-white fw-bold d-flex flex-column align-items-center justify-content-center"
                        data-status="<?= $status_ready ?>"
                        onclick="juri.toggle_ready(this)">
                        <span class="ready-icon fs-1 mb-2"><?= $status_ready ? '✅' : '🔵' ?></span>
                        <span class="ready-text fs-4">READY</span>
                    </button>
                </div>

                <div class="col-4 d-flex flex-column justify-content-center">
                    <!-- Correctness display -->
                    <div class="text-center fw-bold bg-gradient-180-gray-dark text-white py-2 mb-1">
                        Correctness :
                        <?= $data_nilai->penilaian->unsur_nilai->kebenaran->nilai_maksimal ?> - <i class="total_pengurangan_kebenaran_gerak"> </i> :
                    </div>
                    <input class="form-control fw-bolder h3 py-0 m-0 text-center total_nilai_kebenaran mb-2"
                        disabled type="number" value="0">
                    <button type="button" class="btn btn-sm bg-white w-100" data-bs-toggle="modal" data-bs-target="#modalNilaiPerKolom">
                        Movement details
                    </button>

                    <div class="modal fade" id="modalNilaiPerKolom" tabindex="-1" data-bs-backdrop="static"
                        role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered modal-fullscreen" role="document">
                            <div class="modal-content bg-black">
                                <div class="modal-header">
                                    <h5 class="modal-title text-white">Movement Details</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="modal-rincian-body">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Wrong Move Button (kanan, sama lebar dengan Ready) -->
                <div class="col-4 d-flex">
                    <button class="btn w-100 btn-danger text-white fw-bolder button_gerakan_salah d-flex align-items-center justify-content-center"
                        style="font-size: 2rem;"
                        onclick="juri.pointer.pindah_gerakan(1, -<?= $step ?>, this)">
                        Wrong Move
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Unsur Nilai selain kebenaran -->
            <?php if (isset($data_nilai->penilaian->unsur_nilai)): ?>
                <?php foreach ($data_nilai->penilaian->unsur_nilai as $jenis_unsur_nilai => $unsur_nilai): ?>
                    <?php if ($jenis_unsur_nilai !== 'kebenaran'): ?>
                    <div class="row justify-content-center container_<?= $jenis_unsur_nilai ?> mb-3">
                        <div class="col-10 bg-dark h6 fw-bolder border py-3 px-3 text-white text-center">
                            <?= esc($unsur_nilai->metadata->label ?? ucfirst($jenis_unsur_nilai)) ?>
                        </div>
                        <div class="col-10 bg-white">
                            <div class="row p-2">
                                <div class="col-4">
                                    <button class="btn py-3 h6 btn-danger w-100 m-0"
                                        onclick="juri.edit_unsur_nilai('<?= $jenis_unsur_nilai ?>', -0.01, this)">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                                <div class="col-4 p-0">
                                    <input class="form-control fw-bolder h3 py-1 my-1 text-center nilai_<?= $jenis_unsur_nilai ?>"
                                        disabled type="text" style="margin: 6px auto;">
                                </div>
                                <div class="col-4">
                                    <button class="btn py-3 h6 btn-success w-100 m-0"
                                        onclick="juri.edit_unsur_nilai('<?= $jenis_unsur_nilai ?>', 0.01, this)">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- NILAI AKHIR -->
            <div class="row justify-content-center mb-3">
                <div class="col-10 h5 text-center fw-bolder py-3 px-3 bg-dark text-white">
                    Total Score
                </div>
                <div class="col-10 py-2 bg-white">
                    <input class="form-control fw-bolder h2 py-0 my-1 text-center nilai_akhir" disabled type="number"
                        value="0" style="margin: 0;">
                </div>
            </div>
        </div>
    </div>

    <?php if ($aksesLocked): ?>
    <div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark d-flex justify-content-center align-items-center animated slideInDown" style="z-index: 9999; opacity: 0.95;">
        <div class="text-white h1">Scoring Access Locked</div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/juri_seni_persilat.js') ?>"></script>
<script>
    $(function() {
        var $data_nilai = <?= json_encode($data_nilai, JSON_NUMERIC_CHECK) ?>;
        var $penampilan_seni = <?= json_encode($penampilan_seni) ?>;
        juri.init_penilaian_seni($penampilan_seni, $data_nilai);
    });
</script>
<?= $this->endSection() ?>
