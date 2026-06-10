<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'juri', 'nav_active' => 'seni']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idPenampilan = (int) $penampilan->id_penampilan_seni;
    $namaPeserta  = $penampilan->nama_peserta ?? $penampilan->nama_kontingen ?? 'Peserta';
    $kontingen    = $penampilan->nama_kontingen ?? '-';
    $kategori     = ($penampilan->nama_sub_kategori_seni ?? '') . ' - ' . ($penampilan->nama_kategori_usia ?? '');
    $namaJuri     = session()->get('nama_perangkat') ?? 'Juri';
    $gelanggang   = $penampilan->nama_gelanggang ?? 'Gelanggang';
    $aksesLocked  = ($akses_penilaian === 'ditutup');
    $dataNilaiJson = json_encode($data_nilai);
    $formatJson    = json_encode($format_penilaian);
    // Determine accent color (for battle: blue/red based on sudut, default: gold)
    $accentClass  = 'accent-warning';
?>

<div class="container-fluid bg-black min-vh-100 d-flex flex-column p-0"
     id="juri-seni-wrapper"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-endpoint-edit="<?= base_url('juri/edit-penilaian-seni/' . $idPenampilan) ?>"
     data-endpoint-refresh="<?= base_url('juri/refresh-status-seni/' . $idPenampilan) ?>"
     data-endpoint-toggle-ready="<?= base_url('juri/toggle-ready-seni/' . $idPenampilan) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-akses="<?= esc($akses_penilaian, 'attr') ?>">

    <!-- ═══ Header Bar ═══ -->
    <div class="seni-header-bar <?= $accentClass ?>">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary"><?= esc($gelanggang) ?></span>
                <span class="header-kategori"><?= esc($kategori) ?></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Online Indicator -->
                <span class="online-indicator" id="online-indicator">
                    <span class="blink-dot"></span>
                    <span class="online-text">Online</span>
                </span>
                <a href="<?= base_url('juri/seni/terperinci') ?>" class="btn btn-sm btn-outline-light border-secondary" title="Mode Terperinci">
                    <i class="fas fa-expand"></i>
                </a>
                <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="text-white opacity-75" title="Keluar">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- ═══ Peserta Info ═══ -->
    <div class="seni-peserta-bar">
        <div class="peserta-main">
            <span class="peserta-nama-display"><?= esc($namaPeserta) ?></span>
            <span class="peserta-kontingen-display"><?= esc($kontingen) ?></span>
        </div>
        <div class="peserta-sub">
            <span class="text-muted small"><?= esc($namaJuri) ?></span>
        </div>
    </div>

    <!-- ═══ Main Scoring Card ═══ -->
    <div class="flex-grow-1 d-flex flex-column p-2 overflow-auto" id="scoring-body">
        <div class="card bg-dark border-0 rounded-3 flex-grow-1">
            <div class="card-body d-flex flex-column p-3">

                <!-- Skor Akhir Display -->
                <div class="skor-akhir-display mb-3">
                    <div class="skor-label">Skor Akhir</div>
                    <div class="skor-value penilaian-display-font" id="total-nilai">0.00</div>
                </div>

                <!-- Unsur Nilai Buttons -->
                <div id="unsur-nilai-container" class="flex-grow-1">
                    <!-- Rendered by JS based on format_penilaian -->
                </div>

                <!-- Hukuman (read-only from KP) -->
                <div class="hukuman-bar mt-2" id="hukuman-container">
                    <!-- Rendered by JS -->
                </div>

            </div>
        </div>
    </div>

    <!-- ═══ Bottom Action Buttons (large, parity legacy 20vh) ═══ -->
    <div class="seni-action-bar <?= $aksesLocked ? 'is-locked' : '' ?>">
        <button type="button" class="btn-seni-action btn-ready-action" id="btn-ready">
            <i class="fas fa-check-circle"></i>
            <span id="ready-text">READY</span>
        </button>
        <button type="button" class="btn-seni-action btn-wrong-action" id="btn-wrong-move">
            <i class="fas fa-xmark-circle"></i>
            <span>GERAKAN SALAH</span>
        </button>
    </div>

    <?php if ($aksesLocked): ?>
    <div class="locked-overlay">
        <div class="locked-message">
            <i class="fas fa-lock fa-3x mb-3"></i>
            <p class="fs-5">Penilaian Ditutup</p>
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
    const SENI_MODE = 'sederhana';
</script>
<script src="<?= base_url('assets/js/penilaian/juri_seni_persilat.js') ?>"></script>
<?= $this->endSection() ?>
