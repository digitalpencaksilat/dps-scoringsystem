<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-tanding.css') ?>">
<style>.kp-wrapper-light { background: var(--bg-color, #f4f6f9) !important; }</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'tanding']) ?>
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
    $semua     = $ringkasan->semua_ronde ?? null;

    $aksiHukuman = [
        ['label' => 'Teguran 1',    'mode' => 'teguran_1',    'jumlah' => -1,  'cls' => 'warn',   'icon' => 'fa-comment-dots'],
        ['label' => 'Teguran 2',    'mode' => 'teguran_2',    'jumlah' => -2,  'cls' => 'warn',   'icon' => 'fa-comment-dots'],
        ['label' => 'Peringatan 1', 'mode' => 'peringatan_1', 'jumlah' => -5,  'cls' => 'danger', 'icon' => 'fa-triangle-exclamation'],
        ['label' => 'Peringatan 2', 'mode' => 'peringatan_2', 'jumlah' => -10, 'cls' => 'danger', 'icon' => 'fa-circle-exclamation'],
        ['label' => 'Jatuhan (+3)', 'mode' => 'jatuhan',      'jumlah' => 3,   'cls' => 'ok',     'icon' => 'fa-person-falling'],
        ['label' => 'Binaan',       'mode' => 'binaan',       'jumlah' => 1,   'cls' => 'muted',  'icon' => 'fa-hand-holding-heart'],
    ];
?>

<div class="kp-wrapper kp-wrapper-light" id="kp-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-endpoint-edit="<?= base_url('ketua-pertandingan/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('ketua-pertandingan/refresh-status-pertandingan/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <!-- ═══ Top Bar ═══ -->
    <header class="kp-topbar kp-topbar-light">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('ketua-pertandingan') ?>" class="text-dark" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="kp-ronde penilaian-display-font text-dark">Ronde <?= esc($ronde) ?></span>
        </div>
        <span class="kp-title text-dark">PERSILAT &middot; Ketua Pertandingan</span>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('ketua-pertandingan/tanding/dark') ?>" class="btn btn-sm btn-outline-dark" title="Dark Mode">
                <i class="fas fa-moon"></i>
            </a>
            <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="kp-logout text-danger" title="Keluar">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </header>

    <!-- ═══ Scoreboard ═══ -->
    <div class="kp-scoreboard kp-scoreboard-light">
        <div class="kp-score corner-biru">
            <div class="kp-score-nama"><?= esc($namaBiru) ?></div>
            <small class="kp-score-kontingen"><?= esc($kontBiru) ?></small>
            <div class="kp-score-angka penilaian-display-font" id="skor-biru"><?= (int) $pertandingan->skor_biru ?></div>
        </div>
        <div class="kp-score-vs penilaian-display-font">VS</div>
        <div class="kp-score corner-merah">
            <div class="kp-score-nama"><?= esc($namaMerah) ?></div>
            <small class="kp-score-kontingen"><?= esc($kontMerah) ?></small>
            <div class="kp-score-angka penilaian-display-font" id="skor-merah"><?= (int) $pertandingan->skor_merah ?></div>
        </div>
    </div>

    <!-- ═══ Controls ═══ -->
    <div class="kp-controls kp-controls-light">
        <?php foreach (['biru', 'merah'] as $sudut) : ?>
            <section class="kp-panel kp-panel-light corner-<?= $sudut ?>">
                <h2 class="kp-panel-title">
                    <span class="kp-panel-dot"></span>
                    <?= $sudut === 'biru' ? esc($namaBiru) : esc($namaMerah) ?>
                </h2>
                <div class="kp-buttons">
                    <?php foreach ($aksiHukuman as $aksi) : ?>
                        <button type="button" class="kp-btn kp-btn-light kp-<?= $aksi['cls'] ?>"
                                data-sudut="<?= $sudut ?>"
                                data-mode="<?= esc($aksi['mode'], 'attr') ?>"
                                data-jumlah="<?= esc((string) $aksi['jumlah'], 'attr') ?>">
                            <i class="fas <?= $aksi['icon'] ?> me-1"></i>
                            <?= esc($aksi['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="kp-rekap kp-rekap-light" id="rekap-<?= $sudut ?>">
                    <?php $r = $semua->{$sudut} ?? null; ?>
                    <div class="kp-rekap-item">
                        <span class="kp-rekap-label">Teguran</span>
                        <span class="kp-rekap-val rk-teguran"><?= ($r->teguran_1 ?? 0) + ($r->teguran_2 ?? 0) ?></span>
                    </div>
                    <div class="kp-rekap-item">
                        <span class="kp-rekap-label">Peringatan</span>
                        <span class="kp-rekap-val rk-peringatan"><?= ($r->peringatan_1 ?? 0) + ($r->peringatan_2 ?? 0) ?></span>
                    </div>
                    <div class="kp-rekap-item">
                        <span class="kp-rekap-label">Jatuhan</span>
                        <span class="kp-rekap-val rk-jatuhan"><?= $r->jatuhan ?? 0 ?></span>
                    </div>
                    <div class="kp-rekap-item">
                        <span class="kp-rekap-label">Binaan</span>
                        <span class="kp-rekap-val rk-binaan"><?= $r->binaan_1 ?? 0 ?></span>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</div>

<!-- Verifikasi Modals (same as dark) -->
<div class="modal fade" id="modalVerifikasiJatuhan" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-warning">
            <div class="modal-header border-warning"><h5 class="modal-title"><i class="fas fa-person-falling me-2 text-warning"></i>Verifikasi Jatuhan</h5></div>
            <div class="modal-body text-center py-4"><p class="fs-5 mb-3">Apakah jatuhan pada sudut <span id="verifikasi-jatuhan-sudut" class="fw-bold text-uppercase"></span> valid?</p></div>
            <div class="modal-footer border-warning justify-content-center gap-3">
                <button type="button" class="btn btn-success btn-lg px-5" data-jawaban="valid"><i class="fas fa-check me-2"></i>Valid</button>
                <button type="button" class="btn btn-danger btn-lg px-5" data-jawaban="tidak_valid"><i class="fas fa-xmark me-2"></i>Tidak Valid</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalVerifikasiPelanggaran" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-warning">
            <div class="modal-header border-warning"><h5 class="modal-title"><i class="fas fa-triangle-exclamation me-2 text-warning"></i>Verifikasi Pelanggaran</h5></div>
            <div class="modal-body text-center py-4"><p class="fs-5 mb-3">Apakah pelanggaran pada sudut <span id="verifikasi-pelanggaran-sudut" class="fw-bold text-uppercase"></span> valid?</p></div>
            <div class="modal-footer border-warning justify-content-center gap-3">
                <button type="button" class="btn btn-success btn-lg px-5" data-jawaban="valid"><i class="fas fa-check me-2"></i>Valid</button>
                <button type="button" class="btn btn-danger btn-lg px-5" data-jawaban="tidak_valid"><i class="fas fa-xmark me-2"></i>Tidak Valid</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/kp_tanding.js') ?>"></script>
<?= $this->endSection() ?>
