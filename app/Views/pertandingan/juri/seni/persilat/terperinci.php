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
    $namaJuri     = session()->get('nama_perangkat') ?? 'Juri';
    $gelanggang   = $penampilan->nama_gelanggang ?? 'Gelanggang';
    $aksesLocked  = ($akses_penilaian === 'ditutup');
    $dataNilaiJson = json_encode($data_nilai);
    $formatJson    = json_encode($format_penilaian);
    $jenisSeni     = $penampilan->jenis_seni ?? 'tunggal';

    // Value range logic (parity legacy sederhanav2.php)
    $rangeMin = '0.00';
    $rangeMax = '0.10';
    if (in_array($jenisSeni, ['ganda', 'solo_kreatif'])) {
        $rangeMin = '0.20';
        $rangeMax = '0.30';
    }

    $accentClass = 'accent-warning';
?>

<div class="container-fluid bg-black min-vh-100 d-flex flex-column p-0"
     id="juri-seni-wrapper"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-endpoint-edit="<?= base_url('juri/edit-penilaian-seni/' . $idPenampilan) ?>"
     data-endpoint-refresh="<?= base_url('juri/refresh-status-seni/' . $idPenampilan) ?>"
     data-endpoint-toggle-ready="<?= base_url('juri/toggle-ready-seni/' . $idPenampilan) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-akses="<?= esc($akses_penilaian, 'attr') ?>"
     data-jenis-seni="<?= esc($jenisSeni, 'attr') ?>"
     data-range-min="<?= $rangeMin ?>"
     data-range-max="<?= $rangeMax ?>">

    <!-- ═══ Header Bar ═══ -->
    <div class="seni-header-bar <?= $accentClass ?>">
        <div class="d-flex align-items-center justify-content-between w-100">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-dark border border-secondary"><?= esc($gelanggang) ?></span>
                <span class="header-kategori"><?= esc($kategori) ?></span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="online-indicator" id="online-indicator">
                    <span class="blink-dot"></span>
                    <span class="online-text">Online</span>
                </span>
                <a href="<?= base_url('juri/seni/sederhana') ?>" class="btn btn-sm btn-outline-light border-secondary" title="Mode Sederhana">
                    <i class="fas fa-compress"></i>
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

    <!-- ═══ Main Scoring Area ═══ -->
    <div class="flex-grow-1 d-flex flex-column p-2 overflow-hidden" id="scoring-body">
        <div class="card bg-dark border-0 rounded-3 flex-grow-1 d-flex flex-column overflow-hidden">
            <div class="card-body d-flex flex-column p-3 overflow-hidden">

                <!-- Skor Akhir Display (compact) -->
                <div class="skor-akhir-display skor-compact mb-2">
                    <div class="d-flex align-items-baseline gap-2 justify-content-center">
                        <span class="skor-label-sm">Skor Akhir:</span>
                        <span class="skor-value-sm penilaian-display-font" id="total-nilai">0.00</span>
                    </div>
                </div>

                <!-- Kebenaran Section (Max - Deductions = Total) -->
                <div class="kebenaran-section mb-2">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <div class="kebenaran-box">
                            <span class="kebenaran-label">Max</span>
                            <span class="kebenaran-val penilaian-display-font" id="kebenaran-max">10.00</span>
                        </div>
                        <span class="kebenaran-op">−</span>
                        <div class="kebenaran-box">
                            <span class="kebenaran-label">Potongan</span>
                            <span class="kebenaran-val penilaian-display-font text-danger" id="kebenaran-potongan">0.00</span>
                        </div>
                        <span class="kebenaran-op">=</span>
                        <div class="kebenaran-box">
                            <span class="kebenaran-label">Total</span>
                            <span class="kebenaran-val penilaian-display-font text-success" id="kebenaran-total">10.00</span>
                        </div>
                    </div>
                </div>

                <!-- Gerakan List (scrollable) -->
                <div class="gerakan-container flex-grow-1 overflow-auto" id="gerakan-container">
                    <!-- Rendered by JS: per-gerakan rows with +/- buttons -->
                </div>

                <!-- Hukuman (from KP) -->
                <div class="hukuman-bar mt-2" id="hukuman-container"></div>

            </div>
        </div>
    </div>

    <!-- ═══ Bottom Action Buttons ═══ -->
    <div class="seni-action-bar <?= $aksesLocked ? 'is-locked' : '' ?>">
        <button type="button" class="btn-seni-action btn-next-move" id="btn-next-move">
            <i class="fas fa-forward-step"></i>
            <span>NEXT MOVE SET</span>
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

<!-- ═══ Modal Detail Gerakan ═══ -->
<div class="modal fade" id="modalDetailGerakan" tabindex="-1">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="modalDetailTitle">Detail Gerakan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3" id="modalDetailBody">
                <!-- Dynamic content per gerakan -->
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const SENI_FORMAT = <?= $formatJson ?>;
    const SENI_DATA = <?= $dataNilaiJson ?>;
    const SENI_READY = <?= (int) ($penilaian->status_ready ?? 0) ?>;
    const SENI_MODE = 'terperinci';
</script>
<script src="<?= base_url('assets/js/penilaian/juri_seni_persilat.js') ?>"></script>
<?= $this->endSection() ?>
