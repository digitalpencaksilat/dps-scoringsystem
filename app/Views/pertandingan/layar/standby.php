<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $mode = $mode ?? 'tanding'; ?>
<div class="standby-wrapper" id="standby-wrapper" data-mode="<?= esc($mode) ?>">
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
        <?= $mode === 'seni' ? 'Menunggu Penampilan Seni' : 'Menunggu Pertandingan' ?>
    </div>

    <?php if (!empty($nama_gelanggang)): ?>
        <div class="standby-gelanggang">
            <div class="standby-gelanggang-label">Gelanggang</div>
            <div class="standby-gelanggang-name"><?= esc($nama_gelanggang) ?></div>
        </div>
    <?php endif; ?>

    <div class="standby-spinner">
        <svg class="standby-loader-ring" viewBox="25 25 50 50">
            <circle cx="50" cy="50" r="20"></circle>
        </svg>
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
