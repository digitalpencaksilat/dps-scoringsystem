<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="layar-home" id="layar-home"
     data-endpoint-tanding="<?= base_url('layar/refresh-status-pertandingan') ?>"
     data-endpoint-seni="<?= base_url('layar/refresh-status-seni') ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <!-- Logo Animation -->
    <div class="layar-home-logo">
        <img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
             alt="DPS Scoring System" class="layar-logo-img" onerror="this.src='<?= base_url('assets/images/brand/dps/logo.png') ?>'">
    </div>

    <!-- Gelanggang Info -->
    <div class="layar-home-info">
        <h2 class="layar-home-title penilaian-display-font"><?= esc($nama_gelanggang ?? 'Gelanggang') ?></h2>
        <p class="layar-home-subtitle">Digital Pencak Silat — Scoring System</p>
    </div>

    <!-- Status Indicator -->
    <div class="layar-home-status">
        <div class="layar-pulse"></div>
        <span>Menunggu pertandingan...</span>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    // Poll for active match — auto redirect
    function checkActive() {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);

        fetch('<?= base_url('layar/refresh-status-pertandingan') ?>', {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            if (d && d.status === false) {
                // Active tanding found
                window.location.href = '<?= base_url('layar/tanding') ?>';
                return;
            }
        })
        .catch(() => {});

        // Also check seni
        const body2 = new URLSearchParams();
        body2.append(csrfName, csrfHash);
        fetch('<?= base_url('layar/refresh-status-seni') ?>', {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body2
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            if (d && d.status === false) {
                // Active seni found
                window.location.href = '<?= base_url('layar/seni') ?>';
            }
        })
        .catch(() => {});
    }

    setInterval(checkActive, 3000);
    checkActive();

    // Socket.IO auto-redirect
    if (typeof io !== 'undefined') {
        const socket = io(window.SOCKET_URL || 'http://localhost:3000');
        socket.emit('JOIN_ROOM', { id_gelanggang: '<?= (int) session()->get('id_gelanggang') ?>' });
        socket.on('TANDING_BERLANGSUNG', () => { window.location.href = '<?= base_url('layar/tanding') ?>'; });
        socket.on('SENI_BERLANGSUNG', () => { window.location.href = '<?= base_url('layar/seni') ?>'; });
    }
})();
</script>
<?= $this->endSection() ?>
