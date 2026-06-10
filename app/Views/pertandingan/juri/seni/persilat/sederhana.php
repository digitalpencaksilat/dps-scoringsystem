<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idPenampilan = (int) $penampilan->id_penampilan_seni;
    $namaPeserta  = $penampilan->nama_peserta ?? $penampilan->nama_kontingen ?? 'Peserta';
    $kontingen    = $penampilan->nama_kontingen ?? '-';
    $kategori     = ($penampilan->nama_sub_kategori_seni ?? '') . ' - ' . ($penampilan->nama_kategori_usia ?? '');
    $aksesLocked  = ($akses_penilaian === 'ditutup');
    $dataNilaiJson = json_encode($data_nilai);
    $formatJson    = json_encode($format_penilaian);
?>

<div class="juri-seni-wrapper" id="juri-seni-wrapper"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-endpoint-edit="<?= base_url('juri/edit-penilaian-seni/' . $idPenampilan) ?>"
     data-endpoint-refresh="<?= base_url('juri/refresh-status-seni/' . $idPenampilan) ?>"
     data-endpoint-toggle-ready="<?= base_url('juri/toggle-ready-seni/' . $idPenampilan) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-akses="<?= esc($akses_penilaian, 'attr') ?>">

    <!-- Navbar -->
    <header class="juri-seni-topbar">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-brand-primary"><?= esc($penampilan->nama_gelanggang ?? 'Gelanggang') ?></span>
            <span class="topbar-kategori"><?= esc($kategori) ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="<?= base_url('juri/seni/terperinci') ?>" class="btn btn-sm btn-outline-light" title="Mode Terperinci">
                <i class="fas fa-expand"></i>
            </a>
            <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="juri-logout" title="Keluar">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </header>

    <!-- Info Peserta -->
    <div class="peserta-info-bar">
        <div class="peserta-nama"><?= esc($namaPeserta) ?></div>
        <div class="peserta-kontingen"><?= esc($kontingen) ?></div>
    </div>

    <!-- Main Scoring Area -->
    <div class="seni-scoring-body <?= $aksesLocked ? 'is-locked' : '' ?>" id="scoring-body">

        <!-- Unsur Nilai Section (dynamic from format) -->
        <div id="unsur-nilai-container">
            <!-- Rendered by JS based on format_penilaian -->
        </div>

        <!-- Hukuman Section (read-only, from KP) -->
        <div class="scoring-section hukuman-section">
            <div class="section-header">
                <i class="fas fa-gavel me-2"></i>Hukuman
                <span class="badge bg-secondary ms-2">Dari Ketua Pertandingan</span>
            </div>
            <div id="hukuman-container" class="hukuman-grid">
                <!-- Rendered by JS -->
            </div>
        </div>

    </div>

    <!-- Bottom Bar: Total + Actions -->
    <footer class="seni-bottom-bar">
        <div class="total-section">
            <span class="total-label">Total Nilai</span>
            <span class="total-value penilaian-display-font" id="total-nilai">0.00</span>
        </div>
        <div class="action-buttons">
            <button type="button" class="btn btn-ready" id="btn-ready">
                <i class="fas fa-check-circle me-1"></i>
                <span id="ready-text">READY</span>
            </button>
            <button type="button" class="btn btn-simpan" id="btn-simpan">
                <i class="fas fa-save me-1"></i> SIMPAN
            </button>
        </div>
    </footer>

    <?php if ($aksesLocked): ?>
    <div class="locked-overlay">
        <div class="locked-message">
            <i class="fas fa-lock fa-2x mb-2"></i>
            <p>Penilaian Ditutup</p>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const SENI_FORMAT = <?= $formatJson ?>;
    const SENI_DATA = <?= $dataNilaiJson ?>;
    const SENI_READY = <?= (int) ($penilaian->status_ready ?? 0) ?>;
</script>
<script src="<?= base_url('assets/js/penilaian/juri_seni_persilat.js') ?>"></script>
<?= $this->endSection() ?>
