<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<div class="layar-standby">
    <div class="layar-standby-inner">
        <div class="layar-standby-brand penilaian-display-font">Digital Pencak Silat</div>
        <div class="layar-standby-title penilaian-display-font">MENUNGGU PERTANDINGAN</div>
        <div class="layar-standby-gelanggang"><?= esc($nama_gelanggang ?? 'Gelanggang') ?></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Standby: cek apakah ada partai berlangsung, reload bila ada.
    setInterval(function () {
        const body = new URLSearchParams();
        body.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fetch('<?= base_url('layar/refresh-status-pertandingan') ?>', {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(d => { if (d && d.status === false) window.location.reload(); })
        .catch(() => {});
    }, 3000);
</script>
<?= $this->endSection() ?>
