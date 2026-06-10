<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.hasil-tanding-wrapper {
    min-height: 100vh;
    display: grid; grid-template-rows: auto 1fr auto;
    background: linear-gradient(180deg, #0d1117 0%, #161b22 100%);
    color: #fff; padding: 3vh 3vw;
}
.hasil-header {
    text-align: center; padding: 2vh 0;
}
.hasil-header h1 {
    font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 700;
    color: var(--brand-secondary, #c5a017); margin: 0;
    letter-spacing: 3px;
}
.hasil-header .kelas-info {
    font-size: clamp(1rem, 2vw, 1.5rem); opacity: 0.7; margin-top: 0.5rem;
}

.hasil-body {
    display: grid; grid-template-columns: 1fr auto 1fr;
    align-items: center; gap: 2vw;
}

.hasil-sudut {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 4vh 2vw; border-radius: 16px;
    position: relative; overflow: hidden;
}
.hasil-sudut.corner-biru { background: linear-gradient(160deg, #1565c0, #0d47a1); }
.hasil-sudut.corner-merah { background: linear-gradient(160deg, #c62828, #8e0000); }
.hasil-sudut.winner::after {
    content: ''; position: absolute; inset: 0;
    border: 3px solid #ffd700;
    border-radius: 16px;
    animation: winnerGlow 2s ease-in-out infinite;
}
@keyframes winnerGlow {
    0%, 100% { box-shadow: inset 0 0 20px rgba(255,215,0,.2), 0 0 20px rgba(255,215,0,.3); }
    50% { box-shadow: inset 0 0 40px rgba(255,215,0,.3), 0 0 40px rgba(255,215,0,.5); }
}

.hasil-sudut-label {
    font-size: clamp(1rem, 1.8vw, 1.3rem);
    text-transform: uppercase; letter-spacing: 4px; opacity: 0.7;
}
.hasil-nama {
    font-size: clamp(1.5rem, 3vw, 2.5rem); font-weight: 700;
    margin: 1vh 0; text-align: center;
}
.hasil-kontingen {
    font-size: clamp(0.9rem, 1.5vw, 1.2rem); opacity: 0.7;
}
.hasil-skor-besar {
    font-size: clamp(5rem, 14vw, 12rem); font-weight: 700;
    line-height: 1; margin-top: 2vh;
    text-shadow: 0 4px 20px rgba(0,0,0,.4);
}
.hasil-trophy {
    position: absolute; top: 1.5vh; right: 1.5vw;
    font-size: 2.5rem; color: #ffd700;
    display: none;
}
.hasil-sudut.winner .hasil-trophy { display: block; }

.hasil-center {
    text-align: center;
}
.hasil-vs {
    font-size: clamp(1.5rem, 3vw, 2.5rem); font-weight: 700;
    color: var(--brand-secondary, #c5a017); letter-spacing: 4px;
}
.hasil-keterangan {
    font-size: clamp(0.9rem, 1.5vw, 1.2rem);
    color: #9aa4b2; margin-top: 2vh;
    text-transform: uppercase; letter-spacing: 2px;
}

.hasil-footer {
    text-align: center; padding: 2vh 0;
}
.hasil-footer .btn { font-size: 1.1rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $skorMerah = (int) ($pertandingan->skor_merah ?? 0);
    $skorBiru  = (int) ($pertandingan->skor_biru ?? 0);
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';

    // Determine winner
    $winnerSide = '';
    if ($skorMerah > $skorBiru) $winnerSide = 'merah';
    elseif ($skorBiru > $skorMerah) $winnerSide = 'biru';

    $keterangan = $pertandingan->keterangan_hasil ?? '';
?>
<div class="hasil-tanding-wrapper">
    <div class="hasil-header">
        <h1 class="penilaian-display-font">HASIL PERTANDINGAN</h1>
        <?php if (!empty($pertandingan->nama_kelas)): ?>
            <div class="kelas-info"><?= esc($pertandingan->nama_kelas) ?></div>
        <?php endif; ?>
    </div>

    <div class="hasil-body">
        <!-- BIRU -->
        <div class="hasil-sudut corner-biru <?= $winnerSide === 'biru' ? 'winner' : '' ?>">
            <div class="hasil-trophy"><i class="fa-solid fa-trophy"></i></div>
            <div class="hasil-sudut-label">BIRU</div>
            <div class="hasil-nama penilaian-display-font"><?= esc($namaBiru) ?></div>
            <div class="hasil-kontingen"><?= esc($kontBiru) ?></div>
            <div class="hasil-skor-besar penilaian-display-font"><?= $skorBiru ?></div>
        </div>

        <!-- CENTER -->
        <div class="hasil-center">
            <div class="hasil-vs penilaian-display-font">VS</div>
            <?php if ($keterangan): ?>
                <div class="hasil-keterangan"><?= esc($keterangan) ?></div>
            <?php endif; ?>
        </div>

        <!-- MERAH -->
        <div class="hasil-sudut corner-merah <?= $winnerSide === 'merah' ? 'winner' : '' ?>">
            <div class="hasil-trophy"><i class="fa-solid fa-trophy"></i></div>
            <div class="hasil-sudut-label">MERAH</div>
            <div class="hasil-nama penilaian-display-font"><?= esc($namaMerah) ?></div>
            <div class="hasil-kontingen"><?= esc($kontMerah) ?></div>
            <div class="hasil-skor-besar penilaian-display-font"><?= $skorMerah ?></div>
        </div>
    </div>

    <div class="hasil-footer">
        <a href="<?= base_url('layar/home') ?>" class="btn btn-outline-light btn-lg">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto-return ke home/standby setelah 20 detik
setTimeout(() => { window.location.href = '<?= base_url('layar/home') ?>'; }, 20000);
</script>
<?= $this->endSection() ?>
