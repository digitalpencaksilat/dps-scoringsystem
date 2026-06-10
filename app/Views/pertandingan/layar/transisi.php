<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.transisi-wrapper {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    background: radial-gradient(ellipse at 50% 40%, #1a2332 0%, #0b0d12 70%);
    color: #fff; text-align: center;
}
.transisi-logo {
    animation: logoFloat 4s ease-in-out infinite;
    margin-bottom: 4vh;
}
.transisi-logo img {
    max-width: 240px; max-height: 160px;
    filter: drop-shadow(0 8px 32px rgba(197,160,23,.3));
}
.transisi-text {
    font-size: clamp(1.2rem, 2.5vw, 2rem);
    color: #9aa4b2; letter-spacing: 2px;
}
.transisi-gelanggang {
    font-size: clamp(2rem, 4vw, 3.5rem);
    font-weight: 700; color: #fff;
    margin-bottom: 1vh;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $mode = $mode ?? 'tanding'; ?>
<div class="transisi-wrapper" data-mode="<?= esc($mode) ?>">
    <div>
        <div class="transisi-logo">
            <img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
                 alt="DPS" onerror="this.src='<?= base_url('assets/images/brand/dps/logo.png') ?>'">
        </div>
        <div class="transisi-gelanggang penilaian-display-font"><?= esc($nama_gelanggang ?? 'Gelanggang') ?></div>
        <div class="transisi-text">
            <?= $mode === 'seni' ? 'Menunggu penampilan berikutnya...' : 'Menunggu pertandingan berikutnya...' ?>
        </div>
        <div class="layar-standby-pulse" style="margin-top:3vh; display:flex; justify-content:center;">
            <div class="layar-pulse"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    const mode = '<?= esc($mode ?? 'tanding') ?>';
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    function checkRefresh() {
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
            if ((d && d.status === true && d.reload === true) || (d && d.status === false)) {
                window.location.reload();
            }
        })
        .catch(() => {});
    }

    setInterval(checkRefresh, 4000);
    checkRefresh();
})();
</script>
<?= $this->endSection() ?>
