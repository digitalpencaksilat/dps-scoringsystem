<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $mode = $mode ?? 'tanding'; ?>
<div class="standby-wrapper" id="standby-wrapper" data-mode="<?= esc($mode) ?>">
    <!-- Icon -->
    <div class="standby-icon">
        <?php if ($mode === 'seni'): ?>
            <i class="fa-solid fa-masks-theater"></i>
        <?php else: ?>
            <i class="fa-solid fa-hand-fist"></i>
        <?php endif; ?>
    </div>

    <!-- Badge -->
    <span class="standby-badge">
        <i class="fas fa-tv fa-xs" style="color: var(--brand-primary);"></i>
        Scoreboard
    </span>

    <!-- Title -->
    <div class="standby-title"><?= esc($nama_gelanggang ?? 'Gelanggang') ?></div>

    <!-- Subtitle -->
    <p class="standby-subtitle">
        <?= $mode === 'seni' ? 'Menunggu penampilan seni berikutnya...' : 'Menunggu pertandingan berikutnya...' ?>
    </p>

    <!-- Status -->
    <div class="standby-status standby-status-waiting">
        <i class="fas fa-clock"></i>
        Standby
    </div>

    <!-- Spinner -->
    <div class="standby-spinner">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    const mode = document.getElementById('standby-wrapper').dataset.mode;
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function checkReload() {
        const endpoint = mode === 'seni'
            ? '<?= base_url('layar/refresh-status-seni') ?>'
            : '<?= base_url('layar/refresh-status-pertandingan') ?>';
        
        console.log('[Standby] Polling ' + mode + ' status...');

        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);

        fetch(endpoint, {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            console.log('[Standby] Response:', JSON.stringify(d));
            if (d && d.status === false) {
                // Ada pertandingan/penampilan aktif → redirect ke halaman scoreboard
                if (mode === 'seni') {
                    console.log('[Standby] Redirect to layar/seni');
                    window.location.href = '<?= base_url('layar/seni') ?>';
                } else {
                    console.log('[Standby] Redirect to layar/tanding');
                    window.location.href = '<?= base_url('layar/tanding') ?>';
                }
            }
        })
        .catch(e => { console.warn('[Standby] Poll error:', e); });
    }

    setInterval(checkReload, 3000);
    checkReload();

    // Socket.IO auto-redirect
    if (window.io) {
        const rtUrl = '<?= env('RT_PUBLIC_URL', 'http://localhost:3000') ?>';
        const socket = io(rtUrl, { reconnection: true, reconnectionDelay: 1000 });
        const idGelanggang = '<?= (int) session()->get('id_gelanggang') ?>';

        socket.on('connect', () => {
            console.log('[Standby] Socket connected, joining room:', idGelanggang);
            socket.emit('JOIN_ROOM', { id_gelanggang: idGelanggang });
        });

        if (mode === 'tanding') {
            socket.on('TANDING_BERLANGSUNG', () => {
                console.log('[Standby] Socket event TANDING_BERLANGSUNG received');
                window.location.href = '<?= base_url('layar/tanding') ?>';
            });
        } else {
            socket.on('SENI_BERLANGSUNG', () => {
                console.log('[Standby] Socket event SENI_BERLANGSUNG received');
                window.location.href = '<?= base_url('layar/seni') ?>';
            });
        }
    }
})();
</script>
<?= $this->endSection() ?>
