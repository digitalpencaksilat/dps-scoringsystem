<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<div class="standby-wrapper">
    <!-- Icon -->
    <div class="standby-icon">
        <?php
            $icon = 'fa-solid fa-gavel';
            if (($posisi ?? '') === 'juri') $icon = 'fa-solid fa-scale-balanced';
            elseif (($posisi ?? '') === 'ketua_pertandingan') $icon = 'fa-solid fa-shield-halved';
            elseif (($posisi ?? '') === 'layar') $icon = 'fa-solid fa-tv';
        ?>
        <i class="<?= $icon ?>"></i>
    </div>

    <!-- Badge posisi -->
    <span class="standby-badge">
        <i class="fas fa-circle-dot fa-xs" style="color: var(--brand-primary);"></i>
        <?= esc(ucwords(str_replace('_', ' ', (string) ($posisi ?? 'Perangkat')))) ?>
    </span>

    <!-- Title -->
    <div class="standby-title">Menunggu Pertandingan</div>

    <!-- Subtitle: nama perangkat -->
    <?php if (!empty($nama)) : ?>
        <p class="standby-subtitle"><?= esc($nama) ?></p>
    <?php endif; ?>

    <!-- Gelanggang info -->
    <?php if (!empty($nama_gelanggang)) : ?>
        <div class="standby-gelanggang">
            <div class="standby-gelanggang-label">Gelanggang</div>
            <div class="standby-gelanggang-name"><?= esc($nama_gelanggang) ?></div>
        </div>
    <?php endif; ?>

    <!-- Status -->
    <?php if (!empty($pertandingan)) : ?>
        <div class="standby-status standby-status-active">
            <i class="fas fa-circle-play"></i>
            Ada pertandingan berlangsung (No. <?= esc($pertandingan->nomor_pertandingan ?? '-') ?>)
        </div>
    <?php else : ?>
        <div class="standby-status standby-status-waiting">
            <i class="fas fa-clock"></i>
            Belum ada partai aktif di gelanggang ini
        </div>
    <?php endif; ?>

    <!-- Spinner -->
    <div class="standby-spinner">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
    </div>

    <!-- Logout -->
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
