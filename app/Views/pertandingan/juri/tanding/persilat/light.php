<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-tanding.css') ?>">
<?php if (($theme ?? 'light') === 'dark') : ?>
<style>
body.penilaian-body { background: #0f1115; }
.juri-topbar { background: #1a1d24; color: #fff; }
.juri-corners { background: #1a1d24; }
</style>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';
?>
<div class="juri-wrapper" id="juri-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-endpoint-edit="<?= base_url('juri/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('juri/refresh-status-pertandingan/' . $idP) ?>"
     data-endpoint-verifikasi="<?= base_url('juri/submit-jawaban-verifikasi/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <header class="juri-topbar">
        <span class="juri-ronde-badge penilaian-display-font">Ronde <?= esc($ronde) ?></span>
        <span class="juri-format">PERSILAT &middot; Juri</span>
        <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="juri-logout" title="Keluar">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </header>

    <div class="juri-corners">
        <!-- SUDUT BIRU -->
        <section class="juri-corner corner-biru">
            <div class="corner-head">
                <div class="corner-skor penilaian-display-font" id="skor-biru">0</div>
                <div class="corner-atlet">
                    <div class="corner-nama"><?= esc($namaBiru) ?></div>
                    <div class="corner-kontingen"><?= esc($kontBiru) ?></div>
                </div>
            </div>
            <div class="corner-buttons">
                <button type="button" class="btn-nilai" data-sudut="biru" data-nilai="1">
                    <span class="nilai-angka penilaian-display-font">1</span>
                    <span class="nilai-label">Pukulan</span>
                </button>
                <button type="button" class="btn-nilai" data-sudut="biru" data-nilai="2">
                    <span class="nilai-angka penilaian-display-font">2</span>
                    <span class="nilai-label">Tendangan</span>
                </button>
                <button type="button" class="btn-hapus" data-sudut="biru">
                    <i class="fas fa-rotate-left"></i>
                    <span class="nilai-label">Hapus</span>
                </button>
            </div>
        </section>

        <!-- SUDUT MERAH -->
        <section class="juri-corner corner-merah">
            <div class="corner-head">
                <div class="corner-skor penilaian-display-font" id="skor-merah">0</div>
                <div class="corner-atlet">
                    <div class="corner-nama"><?= esc($namaMerah) ?></div>
                    <div class="corner-kontingen"><?= esc($kontMerah) ?></div>
                </div>
            </div>
            <div class="corner-buttons">
                <button type="button" class="btn-nilai" data-sudut="merah" data-nilai="1">
                    <span class="nilai-angka penilaian-display-font">1</span>
                    <span class="nilai-label">Pukulan</span>
                </button>
                <button type="button" class="btn-nilai" data-sudut="merah" data-nilai="2">
                    <span class="nilai-angka penilaian-display-font">2</span>
                    <span class="nilai-label">Tendangan</span>
                </button>
                <button type="button" class="btn-hapus" data-sudut="merah">
                    <i class="fas fa-rotate-left"></i>
                    <span class="nilai-label">Hapus</span>
                </button>
            </div>
        </section>
    </div>
</div>

<!-- Modal Verifikasi Jatuhan -->
<div class="modal fade" id="modalVerifikasiJatuhan" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-warning">
            <div class="modal-header border-warning">
                <h5 class="modal-title"><i class="fas fa-gavel me-2"></i>Verifikasi Jatuhan</h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fs-5 mb-3">Apakah terjadi <strong>jatuhan</strong> pada sudut <span id="verifikasi-jatuhan-sudut" class="fw-bold text-uppercase"></span>?</p>
            </div>
            <div class="modal-footer border-warning justify-content-center gap-3">
                <button type="button" class="btn btn-success btn-lg px-5" data-jawaban="ya">
                    <i class="fas fa-check me-2"></i>Ya
                </button>
                <button type="button" class="btn btn-danger btn-lg px-5" data-jawaban="tidak">
                    <i class="fas fa-xmark me-2"></i>Tidak
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verifikasi Pelanggaran -->
<div class="modal fade" id="modalVerifikasiPelanggaran" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-warning">
            <div class="modal-header border-warning">
                <h5 class="modal-title"><i class="fas fa-triangle-exclamation me-2"></i>Verifikasi Pelanggaran</h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fs-5 mb-3">Apakah terjadi <strong>pelanggaran</strong> pada sudut <span id="verifikasi-pelanggaran-sudut" class="fw-bold text-uppercase"></span>?</p>
            </div>
            <div class="modal-footer border-warning justify-content-center gap-3">
                <button type="button" class="btn btn-success btn-lg px-5" data-jawaban="ya">
                    <i class="fas fa-check me-2"></i>Ya
                </button>
                <button type="button" class="btn btn-danger btn-lg px-5" data-jawaban="tidak">
                    <i class="fas fa-xmark me-2"></i>Tidak
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const JURI_TANDING_INIT = <?= json_encode($data_nilai) ?>;
</script>
<script src="<?= base_url('assets/js/penilaian/juri_tanding_persilat.js') ?>"></script>
<?= $this->endSection() ?>
