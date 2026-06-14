<?php
/**
 * Layar Seni PERSILAT — Dark Mode (parity CI3 layar/seni/persilat/dark.php)
 *
 * Tampilan visual copy 1:1 dari legacy. Fungsi real-time (Socket.IO, polling,
 * timer, redirect) tetap ditangani oleh layar_seni_persilat.js yang sudah proven.
 */
?>
<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // Variabel dari controller (Layar::seni)
    $idPenampilan  = (int) $penampilan_seni_berlangsung->id_penampilan_seni;
    $sistemPenampilan = $kompetisi_seni->sistem_penampilan ?? 'pool';
?>
<div class="container-fluid layar-seni-legacy"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-status-penampilan="<?= esc($penampilan_seni_berlangsung->status_penampilan ?? 'standby') ?>">

    <!-- ═══ HEADER KOMPETISI (parity: components/header.php) ═══ -->
    <div class="row bg-white bg-gradient-180-white mb-3 justify-content-around opacity" id="competition-title">
        <div class="col-1 px-0 py-2 d-flex justify-content-center align-items-center">
            <img src="<?= base_url('assets/images/brand/dps/logo-international-federation.png') ?>" alt="Persilat" class="img-fluid">
        </div>
        <div class="col-8 col-xxl-9">
            <div class="row">
                <div class="col-12 bg-gradient-180-gray-dark rounded-top rounded-3">
                    <p class="h2 text-center m-0 text-white my-2">
                        <?= esc($event_name ?? 'Pencak Silat Championship') ?>
                    </p>
                </div>
            </div>
            <div class="row justify-content-around py-1">
                <div class="col">
                    <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white">
                        <?= esc($partai_seni_berlangsung->nama_gelanggang ?? 'Gelanggang') ?> - <?= esc($partai_seni_berlangsung->nomor_partai ?? '-') ?>
                    </p>
                </div>
                <?php if ($sistemPenampilan === 'battle'): ?>
                    <div class="col">
                        <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white">
                            <?= strtoupper(esc($partai_seni_berlangsung->babak_battle ?? '-')) ?>
                        </p>
                    </div>
                <?php endif; ?>
                <div class="col">
                    <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white px-2">
                        <?= strtoupper(esc($kompetisi_seni->nama_kategori_usia ?? '-')) ?>
                    </p>
                </div>
                <div class="col">
                    <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white px-2">
                        <?= strtoupper(esc($kompetisi_seni->jenis_seni ?? '-')) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-1 px-0 py-2 d-flex justify-content-center align-items-center">
            <img src="<?= base_url('assets/images/brand/dps/logo-federation.png') ?>" alt="National Pencak Silat Federation" class="img-fluid">
        </div>
    </div>

    <!-- ═══ PESERTA SENI ═══ -->
    <div class="row my-4 justify-content-center opacity" id="daftar-peserta">
        <div class="<?= (count($peserta_seni) > 1) ? 'col-12' : 'col-6' ?>">
            <div class="row">
                <div class="col-3 d-flex justify-content-center align-items-center bg-dark bg-gradient">
                    <?php
                        // Bendera kontingen — gunakan helper bendera() jika ada, fallback ke placeholder
                        $namaKontingen = $peserta_seni[0]->nama_kontingen ?? 'default';
                    ?>
                    <img src="<?= base_url('assets/images/bendera/' . strtolower($namaKontingen) . '.png') ?>"
                         class="img-fluid img-thumbnail"
                         alt="<?= esc($namaKontingen) ?>"
                         onerror="this.src='<?= base_url('assets/images/brand/dps/logo.png') ?>'">
                </div>
                <div class="col-9">
                    <div class="row h-100">
                        <div class="col-12 bg-gradient-180-gray-dark justify-content-center d-flex flex-column py-2">
                            <p class="h3 text-center text-truncate text-uppercase m-0 fw-bolder text-white">
                                <?php foreach ($peserta_seni as $key => $value): ?>
                                    <?= esc($value->nama_pendaftar) ?><?= ($key < (count($peserta_seni) - 1)) ? ' - ' : '' ?>
                                <?php endforeach; ?>
                            </p>
                        </div>
                        <div class="col-12 bg-white">
                            <div class="row h-100">
                                <div class="col-12 justify-content-center d-flex flex-column">
                                    <p class="text-truncate m-0 h3 text-center"><?= esc($namaKontingen) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ URUTAN NILAI PER JURI ═══ -->
    <div class="row shadow-lg">
        <div class="col-12">
            <div class="row urutan_total_nilai_juri">
                <?php
                $jumlahJuri = !empty($data_nilai[$idPenampilan])
                    ? count($data_nilai[$idPenampilan])
                    : 5;
                for ($i = 0; $i < $jumlahJuri; $i++):
                    $juriId = isset($data_nilai[$idPenampilan][$i])
                        ? $data_nilai[$idPenampilan][$i]->id_perangkat_pertandingan
                        : 0;
                ?>
                    <div class="col mb-3 kolom_total_nilai opacity">
                        <div class="row bg-white">
                            <div class="col-12 bg-gradient-180-gray-dark">
                                <p class="h5 fw-bolder text-white text-center my-2 text-uppercase nomor_juri">&nbsp;</p>
                            </div>
                            <div class="col-12 kolom_bobot_total_nilai">
                                <p class="fw-bolder text-center my-1 h6 total_nilai_juri_<?= $juriId ?> juri_<?= $juriId ?>">0</p>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- ═══ RINGKASAN NILAI (4 kolom: Median Kebenaran, Std Dev, Median, Penalty) ═══ -->
    <div class="row mt-3">
        <div class="col-12 col-md-3 col-xl-3 mb-3 ps-md-0 kolom-median-kebenaran opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Median Kebenaran</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 median_kebenaran">0</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 col-xl-3 mb-3 kolom-standar-deviasi opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Standard Deviation</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 standar_deviasi">0</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 col-xl-3 mb-3 kolom-median opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Median</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 median">0</p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 col-xl-3 mb-3 pe-md-0 kolom-hukuman opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Penalty</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 hukuman">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ NILAI AKHIR & WAKTU TAMPIL ═══ -->
    <div class="row mb-2">
        <div class="col-12 col-md-6 col-xl-6 opacity kolom-nilai-akhir">
            <div class="row shadow-lg">
                <?php if ($sistemPenampilan === 'battle'): ?>
                    <?php if (isset($partai_seni_berlangsung->id_penampilan_seni_biru) && $partai_seni_berlangsung->id_penampilan_seni_biru == $idPenampilan): ?>
                        <div class="col bg-gradient-180-blue col-12">
                            <p class="lh-1 fw-bolder text-center my-1 text-white nilai_akhir">0</p>
                        </div>
                    <?php else: ?>
                        <div class="col bg-gradient-180-red col-12">
                            <p class="lh-1 fw-bolder text-center my-1 text-white nilai_akhir">0</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="col bg-gradient-180-gray-dark col-12">
                        <p class="lh-1 fw-bolder text-center my-1 text-white nilai_akhir">0</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-6 opacity kolom-waktu">
            <div class="row shadow-lg">
                <div class="col-12 bg-white">
                    <p class="lh-1 fw-bolder text-center my-1 text-dark waktu_tampil">00:00</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- jQuery Timer plugin (dipakai oleh layar_seni_persilat.js) -->
<script src="https://cdn.jsdelivr.net/npm/jquery.timer.js@0.1.1/jquery.timer.min.js"></script>
<script src="<?= base_url('assets/js/penilaian/layar_seni_persilat.js') ?>"></script>
<script>
/**
 * Inisialisasi layar seni — data dari controller diteruskan ke JS existing.
 * ui.start_animation() menggerakkan fadeIn sequence parity legacy.
 */
var $data_nilai = <?= json_encode($data_nilai, JSON_NUMERIC_CHECK) ?>;
var $penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung, JSON_NUMERIC_CHECK) ?>;

$(document).ready(function() {
    layar.init($penampilan_seni_berlangsung, $data_nilai);

    // Override ui.start_animation dgn versi legacy (fadeInDown sequencing)
    (function legacyAnimation() {
        $('#competition-title').addClass('animated fadeInDown').removeClass('opacity');
        setTimeout(function() {
            $('#daftar-peserta').addClass('animated fadeInDown').removeClass('opacity');
            setTimeout(function() {
                $.each($('.kolom_total_nilai'), function(i, v) {
                    setTimeout(function() {
                        $(v).addClass('animated fadeInDown').removeClass('opacity');
                    }, 200 * i);
                });

                setTimeout(function() {
                    setTimeout(function() {
                        $('.kolom-median-kebenaran').addClass('animated fadeInDown').removeClass('opacity');
                    }, 200 * 1);
                    setTimeout(function() {
                        $('.kolom-standar-deviasi').addClass('animated fadeInDown').removeClass('opacity');
                    }, 200 * 2);
                    setTimeout(function() {
                        $('.kolom-median').addClass('animated fadeInDown').removeClass('opacity');
                    }, 200 * 3);
                    setTimeout(function() {
                        $('.kolom-hukuman').addClass('animated fadeInDown').removeClass('opacity');
                    }, 200 * 4);

                    setTimeout(function() {
                        $('.kolom-nilai-akhir').addClass('animated fadeInLeft').removeClass('opacity');
                        $('.kolom-waktu').addClass('animated fadeInRight').removeClass('opacity');
                    }, 200 * 4);
                }, 200 * $('.kolom_total_nilai').length);
            }, 700);
        }, 700);
    })();
});
</script>
<?= $this->endSection() ?>
