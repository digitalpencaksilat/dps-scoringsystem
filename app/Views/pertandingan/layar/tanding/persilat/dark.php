<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
    .fade-left, .fade-right, .fade-up, .fade-down {
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }
    .fade-left.show, .fade-right.show, .fade-up.show, .fade-down.show {
        opacity: 1;
        transform: translateY(0);
    }
    .fade-left { transform: translateX(-30px); }
    .fade-right { transform: translateX(30px); }
    .fade-up { transform: translateY(30px); }
    .fade-down { transform: translateY(-30px); }

    .display-score {
        font-size: clamp(7rem, 25vw, 15em);
        line-height: 1.15;
        font-weight: bolder;
        color: #212529;
        text-align: center;
        margin: auto 0;
        width: 100%;
        display: block;
        transition: font-size 0.3s ease;
    }
    @media (max-width: 576px) {
        .display-score {
            font-size: clamp(4rem, 25vw, 10em);
            line-height: 1;
        }
    }

    .score-changed {
        animation: score-change-animation 0.5s ease-in-out;
    }
    @keyframes score-change-animation {
        0%   { transform: scale(1); opacity: 0; }
        50%  { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }

    .bg-dim-blue {
        background-color: #0d2a49ff !important;
        transition: background 0.3s ease;
    }
    .bg-dim-red {
        background-color: #3b0a11 !important;
        transition: background 0.3s ease;
    }

    .bg-gradient-180-black {
        background: linear-gradient(180deg, #111 0%, #000 100%);
    }
    .bg-gradient-180-gray-dark {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
    }
    .bg-gradient-180-blue {
        background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%);
    }
    .bg-gradient-180-red {
        background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%);
    }
    .bg-gradient-180-white {
        background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .w-70 { width: 70%; }
    .w-80 { width: 80%; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid min-vh-100 bg-gradient-180-black overflow-hidden">

    <!-- Competition Title (shared component) -->
    <?php
        $info_left = strtoupper(esc($pertandingan->nama_kategori_usia ?? ''));
        $info_center = strtoupper(esc($pertandingan->jenis_kelamin ?? ''));
        $info_right = strtoupper(esc($pertandingan->label ?? ''));
    ?>
    <?= $this->include('pertandingan/layar/components/competition_title', [
        'event_name' => $event_name ?? 'Pencak Silat Championship',
        'info_left' => $info_left,
        'info_center' => $info_center,
        'info_right' => $info_right,
    ]) ?>

    <!-- Header: Athletes + Babak -->
    <?= $this->include('pertandingan/layar/tanding/persilat/components/header') ?>

    <!-- Nomor Partai | Waktu | Ronde -->
    <div class="row mt-1 mb-1">
        <div class="col-md-4 col-12 ps-0 pe-2">
            <div class="bg-gradient-180-gray-dark h-100 d-flex justify-content-center flex-column py-2 opacity" id="nomor-partai">
                <h2 class="display-3 fw-bolder text-white text-center m-0 lh-1 text-capitalize">
                    <?= esc($pertandingan->nama_gelanggang ?? '') ?> Partai <?= esc($pertandingan->nomor_partai ?? '') ?>
                </h2>
            </div>
        </div>
        <div class="col-md-4 col-12 px-0">
            <div class="bg-gradient-180-gray-dark h-100 d-flex justify-content-center flex-column py-2 opacity" id="waktu">
                <h2 class="display-2 fw-bolder stopwatch text-white text-center my-0 lh-1"></h2>
            </div>
        </div>
        <div class="col-md-4 col-12 pe-0 ps-2">
            <div class="bg-gradient-180-gray-dark h-100 d-flex justify-content-center flex-column py-2 opacity" id="ronde">
                <h2 class="display-2 fw-bolder text-white text-center m-0 lh-1 text-capitalize">
                    Ronde <span class="ronde_pertandingan"><?= esc($pertandingan->ronde_pertandingan ?? '1') ?></span>
                </h2>
            </div>
        </div>
    </div>

    <!-- Big Score Row -->
    <div class="row shadow-lg my-1 big-score">
        <!-- Indikator Pelanggaran BIRU -->
        <div class="col-2 indikator-pelanggaran-biru opacity">
            <div class="row flex-row h-100">
                <div class="col-12 mb-1">
                    <div class="row h-100">
                        <div class="indikator-binaan col me-1 d-flex align-items-center justify-content-center indikator-binaan-1 bg-dim-blue">
                            <img src="<?= base_url('assets/images/icon/binaan-1.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                        <div class="indikator-binaan col d-flex align-items-center justify-content-center indikator-binaan-2 bg-dim-blue">
                            <img src="<?= base_url('assets/images/icon/binaan-2.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-1">
                    <div class="row h-100">
                        <div class="indikator-teguran col me-1 d-flex align-items-center justify-content-center indikator-teguran-1 bg-dim-blue">
                            <img src="<?= base_url('assets/images/icon/teguran-1.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                        <div class="indikator-teguran col d-flex align-items-center justify-content-center indikator-teguran-2 bg-dim-blue">
                            <img src="<?= base_url('assets/images/icon/teguran-2.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row h-100">
                        <div class="indikator-peringatan col me-1 d-flex align-items-center justify-content-center indikator-peringatan-1 bg-dim-blue">
                            <img src="<?= base_url('assets/images/icon/peringatan-1.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                        <div class="indikator-peringatan col d-flex align-items-center justify-content-center indikator-peringatan-2 bg-dim-blue">
                            <img src="<?= base_url('assets/images/icon/peringatan-2.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Skor BIRU -->
        <div class="col-4 px-1 kolom-skor-biru opacity">
            <div class="h-100 d-flex justify-content-end align-items-center bg-gradient-180-white">
                <p class="display-score skor_biru text-center m-0">0</p>
            </div>
        </div>

        <!-- Skor MERAH -->
        <div class="col-4 px-1 kolom-skor-merah opacity">
            <div class="h-100 d-flex justify-content-end align-items-center bg-gradient-180-white">
                <p class="display-score skor_merah text-center m-0">0</p>
            </div>
        </div>

        <!-- Indikator Pelanggaran MERAH -->
        <div class="col-2 indikator-pelanggaran-merah opacity">
            <div class="row flex-row h-100">
                <div class="col-12 mb-1">
                    <div class="row h-100">
                        <div class="indikator-binaan col me-1 d-flex align-items-center justify-content-center indikator-binaan-1 bg-dim-red">
                            <img src="<?= base_url('assets/images/icon/binaan-1.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                        <div class="indikator-binaan col d-flex align-items-center justify-content-center indikator-binaan-2 bg-dim-red">
                            <img src="<?= base_url('assets/images/icon/binaan-2.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-1">
                    <div class="row h-100">
                        <div class="indikator-teguran col me-1 d-flex align-items-center justify-content-center indikator-teguran-1 bg-dim-red">
                            <img src="<?= base_url('assets/images/icon/teguran-1.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                        <div class="indikator-teguran col d-flex align-items-center justify-content-center indikator-teguran-2 bg-dim-red">
                            <img src="<?= base_url('assets/images/icon/teguran-2.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row h-100">
                        <div class="indikator-peringatan col me-1 d-flex align-items-center justify-content-center indikator-peringatan-1 bg-dim-red">
                            <img src="<?= base_url('assets/images/icon/peringatan-1.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                        <div class="indikator-peringatan col d-flex align-items-center justify-content-center indikator-peringatan-2 bg-dim-red">
                            <img src="<?= base_url('assets/images/icon/peringatan-2.png?v=2') ?>" class="w-70 img-fluid">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Jatuhan + Judge Indicators -->
    <div class="row my-1">
        <!-- Dropping BIRU -->
        <div class="col-12 col-md-2 bg-gradient-180-blue indikator-jatuhan-biru opacity d-flex flex-column justify-content-center align-items-center">
            <p class="total_jatuhan_biru fw-bolder lh-1 text-center m-0" style="color: #e5e8ebff; font-size: 6rem;">0</p>
            <p class="text-md text-center lh-1 m-0 fw-bolder" style="color: #e5e8ebff;">Dropping</p>
        </div>

        <!-- Judge Indicators -->
        <div class="col-12 col-md-8">
            <div class="row">
                <!-- Biru indicators (top row) -->
                <div class="col-12 col-xl-6">
                    <div class="row">
                        <?php foreach ($perangkat_pertandingan as $data): ?>
                        <div class="col px-1 indikator-poin opacity" style="min-height: 15vh;">
                            <div class="bg-gradient-180-gray-dark h-100 d-flex justify-content-center flex-column juri-<?= $data->id_perangkat_pertandingan ?>-biru-indikator">
                                <p class="display-2 fw-bolder text-white text-center my-1 lh-sm">
                                    <img src="<?= base_url('assets/penilaian/icons/pukulan.png?v=2') ?>" class="w-50 icon-pukulan d-none" alt="pukulan">
                                    <img src="<?= base_url('assets/penilaian/icons/tendangan-inverted.png?v=2') ?>" class="w-50 icon-tendangan-inverted d-none" alt="tendangan">
                                    <img src="<?= base_url('assets/penilaian/icons/jatuhan.png?v=2') ?>" class="w-50 icon-jatuhan d-none" alt="jatuhan">
                                    <img src="<?= base_url('assets/penilaian/icons/hukuman.png?v=2') ?>" class="w-50 icon-hukuman d-none" alt="hukuman">
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- Merah indicators (bottom row) -->
                <div class="col-12 col-xl-6">
                    <div class="row">
                        <?php foreach ($perangkat_pertandingan as $data): ?>
                        <div class="col px-1 indikator-poin opacity" style="min-height: 15vh;">
                            <div class="bg-gradient-180-gray-dark h-100 d-flex justify-content-center flex-column juri-<?= $data->id_perangkat_pertandingan ?>-merah-indikator">
                                <p class="display-2 fw-bolder text-white text-center my-1 lh-sm">
                                    <img src="<?= base_url('assets/penilaian/icons/pukulan-inverted.png?v=2') ?>" class="w-50 icon-pukulan-inverted d-none" alt="pukulan">
                                    <img src="<?= base_url('assets/penilaian/icons/tendangan.png?v=2') ?>" class="w-50 icon-tendangan d-none" alt="tendangan">
                                    <img src="<?= base_url('assets/penilaian/icons/jatuhan.png?v=2') ?>" class="w-50 icon-jatuhan d-none" alt="jatuhan">
                                    <img src="<?= base_url('assets/penilaian/icons/hukuman.png?v=2') ?>" class="w-50 icon-hukuman d-none" alt="hukuman">
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dropping MERAH -->
        <div class="col-12 col-md-2 bg-gradient-180-red indikator-jatuhan-merah opacity d-flex flex-column justify-content-center align-items-center">
            <p class="total_jatuhan_merah fw-bolder lh-1 text-center m-0" style="color: #e5e8ebff; font-size: 6rem;">0</p>
            <p class="text-md text-center lh-1 m-0 fw-bolder" style="color: #e5e8ebff;">Dropping</p>
        </div>
    </div>
</div>

<!-- Stinger (Round Transition) -->
<?= $this->include('pertandingan/layar/tanding/persilat/components/stinger') ?>

<!-- Verification Modals -->
<?= $this->include('pertandingan/layar/tanding/persilat/components/modal_verifikasi_jatuhan') ?>
<?= $this->include('pertandingan/layar/tanding/persilat/components/modal_verifikasi_pelanggaran') ?>
<?= $this->include('pertandingan/layar/tanding/persilat/components/modal_hasil_verifikasi') ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/plugins/timer.jquery.js') ?>"></script>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url('assets/js/penilaian/layar_tanding_persilat.js?v=' . time()) ?>"></script>
<script>
$(document).ready(function () {
    var $data_nilai = <?= json_encode($data_nilai) ?>;
    var $pertandingan = <?= json_encode($pertandingan) ?>;
    var $verifikasi_pertandingan = <?= json_encode($verifikasi_pertandingan) ?>;
    layar.init($data_nilai, $pertandingan, $verifikasi_pertandingan, 500);
    ui.start_animation();
});
</script>
<?= $this->endSection() ?>
