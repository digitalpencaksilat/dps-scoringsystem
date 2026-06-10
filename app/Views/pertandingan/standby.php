<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<div class="standby-wrapper">
    <span class="standby-badge">
        <i class="fas fa-hourglass-half"></i> Standby
    </span>
    <div class="standby-title">Menunggu Pertandingan</div>
    <p class="text-muted mb-1">
        <?= esc(ucwords(str_replace('_', ' ', (string) ($posisi ?? '')))) ?>
        <?php if (! empty($nama)) : ?> &middot; <?= esc($nama) ?><?php endif; ?>
    </p>

    <?php if (! empty($pertandingan)) : ?>
        <p class="text-success mt-2">
            <i class="fas fa-circle-play me-1"></i>
            Ada pertandingan berlangsung (No. <?= esc($pertandingan->nomor_pertandingan ?? '-') ?>).
        </p>
    <?php else : ?>
        <p class="text-muted small mt-2 mb-0">Belum ada partai aktif di gelanggang ini.</p>
    <?php endif; ?>

    <div class="standby-spinner">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
    </div>

    <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="btn btn-sm btn-outline-secondary rounded-pill mt-4">
        <i class="fas fa-right-from-bracket me-1"></i>Keluar
    </a>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Polling ringan status pertandingan (placeholder Fase 1).
    // Akan diganti push event Socket.IO di Fase 8.
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
        .catch(function () { /* abaikan error transient */ });
    }, 5000);
</script>
<?= $this->endSection() ?>
