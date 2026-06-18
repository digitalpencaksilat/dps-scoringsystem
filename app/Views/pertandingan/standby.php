<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<div class="standby-wrapper">
    <div class="standby-grid"></div>
    <div class="standby-glow"></div>
    <div class="standby-ring ring-1"></div>
    <div class="standby-ring ring-2"></div>
    <div class="standby-ring ring-3"></div>

    <div class="standby-logo-wrap">
        <img src="<?= base_url('assets/images/brand/dps/logo-digital-scoring.png') ?>"
             alt="Digital Pencak Silat"
             class="standby-logo">
    </div>

    <div class="standby-divider"></div>

    <div class="standby-status-text">
        <span class="standby-pulse"></span>
        Menunggu Pertandingan
    </div>

    <?php if (!empty($pertandingan)) : ?>
        <div class="standby-match-alert">
            <i class="fas fa-circle-play fa-xs"></i>
            Pertandingan No. <?= esc($pertandingan->nomor_pertandingan ?? '-') ?> sedang berlangsung
        </div>
    <?php endif; ?>

    <div class="standby-spinner">
        <svg class="standby-loader-ring" viewBox="25 25 50 50">
            <circle cx="50" cy="50" r="20"></circle>
        </svg>
    </div>

    <div class="standby-logout">
        <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="btn btn-outline-secondary rounded-pill">
            <i class="fas fa-right-from-bracket me-1"></i>Keluar
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Polling status pertandingan
    setInterval(function () {
        fetch('<?= base_url('perangkat-pertandingan/refresh-status') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data && data.reload === true) {
                window.location.reload();
            }
        })
        .catch(function () {});
    }, 5000);
</script>
<?= $this->endSection() ?>
