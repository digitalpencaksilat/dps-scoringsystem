<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-tanding.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'juri', 'nav_active' => 'tanding']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $totalRonde = (int) ($pertandingan->total_ronde ?? 3);
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';
    $skorMerah = (int) ($pertandingan->skor_merah ?? 0);
    $skorBiru  = (int) ($pertandingan->skor_biru ?? 0);
    $babak     = $pertandingan->babak ?? '';
?>

<div class="container-fluid bg-black min-vh-100 d-flex flex-column pb-2"
     id="juri-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-endpoint-edit="<?= base_url('juri/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('juri/refresh-status-pertandingan/' . $idP) ?>"
     data-endpoint-verifikasi="<?= base_url('juri/submit-jawaban-verifikasi/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <!-- ═══ Header: Atlet + Skor ═══ -->
    <div class="row py-2 px-3 opacity fast" id="header-tanding">
        <div class="col-lg-4 py-2">
            <div class="row h-100">
                <div class="col-12 bg-gradient-180-blue py-3 px-3 text-white d-flex justify-content-center flex-column">
                    <h1 class="h4 text-white m-0 fw-bolder text-truncate">
                        <?= esc($namaBiru) ?>
                    </h1>
                    <h2 class="h5 text-white m-0 text-truncate">
                        <?= esc($kontBiru) ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-lg-4 py-2">
            <div class="row h-100">
                <div class="col-sm-3 col-3 pe-0">
                    <div class="score score-biru h-100 bg-gradient-180-gray-dark d-flex justify-content-center flex-column">
                        <h3 id="total_nilai_akhir_biru" class="text-center text-white my-3"><?= $skorBiru ?></h3>
                    </div>
                </div>
                <div class="col-sm-6 col-md-6 col-6">
                    <div class="timer bg-gradient-180-gray-dark rounded rounded-lg h-100 d-flex justify-content-center flex-column">
                        <h4 class="text-white text-center my-3">
                            <?= esc($babak) ?>
                        </h4>
                    </div>
                </div>
                <div class="col-sm-3 col-3 ps-0">
                    <div class="score score-merah h-100 bg-gradient-180-gray-dark d-flex justify-content-center flex-column">
                        <h3 id="total_nilai_akhir_merah" class="text-center text-white my-3"><?= $skorMerah ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 py-2">
            <div class="row flex-row-reverse flex-md-row h-100">
                <div class="col-12 bg-gradient-180-red py-3 px-3 d-flex justify-content-center flex-column">
                    <h1 class="h4 text-white m-0 fw-bolder text-truncate text-end">
                        <?= esc($namaMerah) ?>
                    </h1>
                    <h2 class="h5 text-white m-0 text-truncate text-end">
                        <?= esc($kontMerah) ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Tabel Nilai Per Ronde ═══ -->
    <div class="row flex-grow-1 pb-2">
        <div class="col-12 d-flex flex-column">
            <div class="card bg-dark card-container-juri opacity flex-grow-1 d-flex flex-column">
                <div class="card-body p-2 d-flex flex-column">
                    <table class="table table-borderless mb-2" id="tabel-nilai-juri">
                        <thead>
                            <tr class="opacity">
                                <th class="bg-dark text-white text-center" colspan="5">
                                    <?= esc(session()->get('nama') ?? 'Juri') ?>
                                </th>
                            </tr>
                            <tr class="text-center opacity">
                                <th scope="col" class="border-dark bg-gradient-180-blue text-white" style="width:10%;">Total</th>
                                <th scope="col" class="border-dark bg-gradient-180-blue text-white" style="width:25%;">Nilai</th>
                                <th scope="col" class="border-dark bg-gradient-180-dark text-white" style="width:10%;">Ronde</th>
                                <th scope="col" class="border-dark bg-gradient-180-red text-white" style="width:25%;">Nilai</th>
                                <th scope="col" class="border-dark bg-gradient-180-red text-white" style="width:10%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($r = 1; $r <= $totalRonde; $r++):
                                $romanNumerals = ['I', 'II', 'III', 'IV', 'V'];
                                $romanLabel = $romanNumerals[$r - 1] ?? $r;
                            ?>
                            <tr class="text-center h4 opacity">
                                <td class="text-white py-2 biru-ronde-<?= $r ?>-total">&nbsp;</td>
                                <td class="text-white py-2 align-middle" style="max-width: 0;">
                                    <div class="biru-ronde-<?= $r ?>-nilai d-flex flex-row flex-nowrap overflow-auto pb-2"></div>
                                </td>
                                <td class="text-white fw-bolder align-middle ronde-<?= $r ?>" onclick="juri.warning_pindah_babak()"><?= $romanLabel ?></td>
                                <td class="text-white py-2 align-middle" style="max-width: 0;">
                                    <div class="merah-ronde-<?= $r ?>-nilai d-flex flex-row flex-nowrap overflow-auto pb-2"></div>
                                </td>
                                <td class="text-white py-2 merah-ronde-<?= $r ?>-total">&nbsp;</td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>

                    <!-- TOMBOL FLEXBOX -->
                    <div class="row flex-grow-1 align-items-stretch mt-1">
                        <!-- Sudut Biru (Kiri) -->
                        <div class="col-6 d-flex flex-column" id="button-biru">
                            <div class="row flex-grow-1 align-items-stretch">
                                <div class="col-12 d-flex flex-column px-2">
                                    <button class="btn h3 bg-blue text-white w-100 text-capitalize opacity mb-2 flex-grow-1 d-flex justify-content-center align-items-center btn-scoring-legacy" data-sudut="biru" data-nilai="1">
                                        <img src="<?= base_url('assets/images/icons/pukulan.png') ?>" alt="Pukulan" class="img-fluid" style="max-height: 80px;">
                                    </button>
                                    <button class="btn h3 bg-blue text-white w-100 text-capitalize opacity mb-0 flex-grow-1 d-flex justify-content-center align-items-center btn-scoring-legacy" data-sudut="biru" data-nilai="2">
                                        <img src="<?= base_url('assets/images/icons/tendangan-inverted.png') ?>" alt="Tendangan" class="img-fluid" style="max-height: 80px;">
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sudut Merah (Kanan) -->
                        <div class="col-6 d-flex flex-column" id="button-merah">
                            <div class="row flex-grow-1 align-items-stretch">
                                <div class="col-12 d-flex flex-column px-2">
                                    <button class="btn h3 bg-red text-white w-100 text-capitalize opacity mb-2 flex-grow-1 d-flex justify-content-center align-items-center btn-scoring-legacy" data-sudut="merah" data-nilai="1">
                                        <img src="<?= base_url('assets/images/icons/pukulan-inverted.png') ?>" alt="Pukulan" class="img-fluid" style="max-height: 80px;">
                                    </button>
                                    <button class="btn h3 bg-red text-white w-100 text-capitalize opacity mb-0 flex-grow-1 d-flex justify-content-center align-items-center btn-scoring-legacy" data-sudut="merah" data-nilai="2">
                                        <img src="<?= base_url('assets/images/icons/tendangan.png') ?>" alt="Tendangan" class="img-fluid" style="max-height: 80px;">
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Verifikasi Jatuhan (3 pilihan: Biru / Invalid / Merah) ═══ -->
<div class="modal fade" id="modalVerifikasiJatuhan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Drop Verification</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="h3 mb-4 text-center">Valid Drop For ?</p>
                    </div>
                    <div class="col">
                        <button class="btn bg-blue text-white py-7 w-100 h1 btn-jawaban-verifikasi" data-jawaban="biru">
                            Blue
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-warning text-white py-7 w-100 h1 btn-jawaban-verifikasi" data-jawaban="invalid">
                            INVALID
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-red text-white py-7 w-100 h1 btn-jawaban-verifikasi" data-jawaban="merah">
                            Red
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Verifikasi Pelanggaran (3 pilihan: Biru / Invalid / Merah) ═══ -->
<div class="modal fade" id="modalVerifikasiPelanggaran" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Penalty Verification</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="h4 mb-4 text-center">Violation For ?</p>
                    </div>
                    <div class="col">
                        <button class="btn bg-blue text-white py-7 w-100 h1 btn-jawaban-verifikasi" data-jawaban="biru">
                            Blue
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-warning text-white py-7 w-100 h1 btn-jawaban-verifikasi" data-jawaban="invalid">
                            INVALID
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-red text-white py-7 w-100 h1 btn-jawaban-verifikasi" data-jawaban="merah">
                            Red
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const JURI_INIT = {
        dataNilai: <?= json_encode($data_nilai) ?>,
        pertandingan: <?= json_encode($pertandingan) ?>,
        pemenang: <?= json_encode($pemenang ?? null) ?>,
        verifikasiPertandingan: <?= json_encode($verifikasi_pertandingan ?? null) ?>,
        jawabanVerifikasi: <?= json_encode($jawaban_verifikasi ?? null) ?>,
        totalRonde: <?= $totalRonde ?>
    };
</script>
<script src="<?= base_url('assets/js/penilaian/juri_tanding_persilat.js') ?>"></script>
<?= $this->endSection() ?>
