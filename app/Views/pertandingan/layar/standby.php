<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $mode = $mode ?? 'tanding'; ?>
<div class="layar-standby" id="standby-wrapper" data-mode="<?= esc($mode) ?>">
    <div class="layar-standby-content">
        <div class="layar-standby-icon">
            <?php if ($mode === 'seni'): ?>
                <i class="fa-solid fa-masks-theater"></i>
            <?php else: ?>
                <i class="fa-solid fa-hand-fist"></i>
            <?php endif; ?>
        </div>
        <h2 class="layar-standby-title penilaian-display-font">
            <?= esc($nama_gelanggang ?? 'Gelanggang') ?>
        </h2>
        <p class="layar-standby-text">
            <?= $mode === 'seni' ? 'Menunggu penampilan seni berikutnya...' : 'Menunggu pertandingan berikutnya...' ?>
        </p>
        <div class="layar-standby-pulse">
            <div class="layar-pulse"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
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

        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);

        fetch(endpoint, {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            // When status is false → active match/penampilan found, reload
            if (d && d.status === false) {
                window.location.reload();
            }
        })
        .catch(() => {});
    }

    // Poll every 3s
    setInterval(checkReload, 3000);
    checkReload();

    // Socket.IO auto-redirect
    if (window.io) {
        const rtUrl = '<?= env('RT_PUBLIC_URL', 'http://localhost:3000') ?>';
        const socket = io(rtUrl, { reconnection: true, reconnectionDelay: 1000 });
        const idGelanggang = '<?= (int) session()->get('id_gelanggang') ?>';

        socket.on('connect', () => {
            socket.emit('JOIN_ROOM', { id_gelanggang: idGelanggang });
        });

        if (mode === 'tanding') {
            socket.on('TANDING_BERLANGSUNG', () => { window.location.reload(); });
        } else {
            socket.on('SENI_BERLANGSUNG', () => { window.location.reload(); });
        }
    }
})();
</script>
<?= $this->endSection() ?>
