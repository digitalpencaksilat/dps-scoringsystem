<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'layar', 'nav_active' => 'seni']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="layar-hasil-wrapper theme-dark layar-battle-hasil">
    <div class="layar-hasil-header">
        <h1 class="penilaian-display-font">HASIL BATTLE SENI</h1>
        <p class="layar-hasil-subtitle">Perbandingan Sudut Merah vs Biru</p>
    </div>

    <div class="layar-battle-container">
        <!-- Sudut Biru -->
        <div class="layar-battle-side corner-biru">
            <div class="battle-sudut-label">BIRU</div>
            <div class="battle-peserta penilaian-display-font" id="nama-biru">-</div>
            <div class="battle-kontingen" id="kontingen-biru">-</div>
            <div class="battle-nilai penilaian-display-font" id="nilai-biru">--.---</div>
        </div>

        <!-- VS / Winner -->
        <div class="layar-battle-center">
            <div class="battle-vs penilaian-display-font">VS</div>
            <div class="battle-winner" id="winner-display" style="display:none;">
                <i class="fa-solid fa-trophy"></i>
                <span class="penilaian-display-font" id="winner-label">PEMENANG</span>
            </div>
        </div>

        <!-- Sudut Merah -->
        <div class="layar-battle-side corner-merah">
            <div class="battle-sudut-label">MERAH</div>
            <div class="battle-peserta penilaian-display-font" id="nama-merah">-</div>
            <div class="battle-kontingen" id="kontingen-merah">-</div>
            <div class="battle-nilai penilaian-display-font" id="nilai-merah">--.---</div>
        </div>
    </div>

    <div class="layar-hasil-footer">
        <a href="<?= base_url('layar/home') ?>" class="btn btn-outline-light btn-lg">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    const idBattle = <?= (int) $id_battle_seni ?>;

    // Load battle result data
    // For now this page auto-returns; real data loaded via future endpoint
    setTimeout(() => { window.location.href = '<?= base_url('layar/home') ?>'; }, 30000);
})();
</script>
<?= $this->endSection() ?>
