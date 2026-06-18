<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.bg-gradient-180-blue { background: linear-gradient(180deg, #1565c0, #0d47a1) !important; }
.bg-gradient-180-red { background: linear-gradient(180deg, #c62828, #8e0000) !important; }
.bg-gradient-180-dark { background: linear-gradient(180deg, #2c2c2c, #1a1a1a) !important; }
.bg-gradient-180-white { background: linear-gradient(180deg, #f8f9fa, #e9ecef) !important; }
.bg-gradient-180-gray-dark { background: linear-gradient(180deg, #343a40, #212529) !important; }

.battle-hasil-page { background: radial-gradient(ellipse at 50% 30%, #1a2332 0%, #0b0d12 70%); min-height: 100vh; color: #fff; }

.countdown-screen, .comparison-screen, .winner-screen { display: none; }
.countdown-screen.active, .comparison-screen.active, .winner-screen.active { display: flex; min-height: 100vh; }

.countdown-num { font-size: clamp(6rem, 15vw, 14rem); font-weight: 700; color: #c5a017; }
.comparison-nilai { font-size: clamp(3rem, 8vw, 7rem); font-weight: 700; }
.comparison-label { font-size: clamp(0.9rem, 1.5vw, 1.2rem); opacity: 0.7; }
.comparison-val { font-size: clamp(1.2rem, 2vw, 1.6rem); font-weight: 600; }
.winner-nama { font-size: clamp(2rem, 5vw, 4rem); font-weight: 700; }
.winner-side { font-size: clamp(1rem, 2vw, 1.5rem); letter-spacing: 4px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$penampilanBiru   = $penampilan_seni_biru ?? null;
$penampilanMerah  = $penampilan_seni_merah ?? null;
$battle           = $battle_seni ?? null;
$pesertaBiru      = $peserta_seni_biru ?? [];
$pesertaMerah     = $peserta_seni_merah ?? [];

// Decode catatan_nilai_sama
$statsBiru  = !empty($penampilanBiru->catatan_nilai_sama) ? json_decode($penampilanBiru->catatan_nilai_sama) : null;
$statsMerah = !empty($penampilanMerah->catatan_nilai_sama) ? json_decode($penampilanMerah->catatan_nilai_sama) : null;

$nilaiBiru  = (float) ($penampilanBiru->nilai_akhir ?? 0);
$nilaiMerah = (float) ($penampilanMerah->nilai_akhir ?? 0);

$isWinnerBiru  = $battle && ($battle->id_penampilan_seni_pemenang ?? 0) == $penampilanBiru->id_penampilan_seni;
$isWinnerMerah = $battle && ($battle->id_penampilan_seni_pemenang ?? 0) == $penampilanMerah->id_penampilan_seni;
$hasWinner = $isWinnerBiru || $isWinnerMerah;

// Nama peserta
$namaBiru  = implode('<br>', array_map(fn($p) => esc($p->nama_pendaftar), $pesertaBiru)) ?: '-';
$namaMerah = implode('<br>', array_map(fn($p) => esc($p->nama_pendaftar), $pesertaMerah)) ?: '-';
?>
<div class="battle-hasil-page">

    <!-- COUNTDOWN -->
    <div class="countdown-screen active d-flex flex-column align-items-center justify-content-center" id="countdown-screen">
        <div style="font-size: clamp(1.5rem, 3vw, 2.5rem); letter-spacing: 4px; opacity: 0.7; margin-bottom: 2vh;">THE WINNER IS...</div>
        <div class="countdown-num penilaian-display-font" id="countdown-num">5</div>
    </div>

    <!-- COMPARISON -->
    <div class="comparison-screen d-none" id="comparison-screen">
        <div class="container-fluid px-3 py-4">
            <div class="row">
                <!-- BIRU -->
                <div class="col-5 bg-gradient-180-blue py-4 px-3 d-flex flex-column align-items-center">
                    <div style="font-size: clamp(1rem, 1.8vw, 1.3rem); letter-spacing: 4px; opacity: 0.7;">BIRU</div>
                    <div class="penilaian-display-font my-2 text-center" style="font-size: clamp(1.2rem, 2.5vw, 1.8rem); font-weight: 600;">
                        <?= $namaBiru ?>
                    </div>
                    <div style="font-size: clamp(0.8rem, 1.2vw, 1rem); opacity: 0.7;"><?= esc($penampilanBiru->nama_kontingen ?? '-') ?></div>
                    <div class="comparison-nilai penilaian-display-font mt-3"><?= number_format($nilaiBiru, 3) ?></div>
                </div>

                <!-- VS -->
                <div class="col-2 d-flex align-items-center justify-content-center">
                    <div class="penilaian-display-font" style="font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700; color: #c5a017;">VS</div>
                </div>

                <!-- MERAH -->
                <div class="col-5 bg-gradient-180-red py-4 px-3 d-flex flex-column align-items-center">
                    <div style="font-size: clamp(1rem, 1.8vw, 1.3rem); letter-spacing: 4px; opacity: 0.7;">MERAH</div>
                    <div class="penilaian-display-font my-2 text-center" style="font-size: clamp(1.2rem, 2.5vw, 1.8rem); font-weight: 600;">
                        <?= $namaMerah ?>
                    </div>
                    <div style="font-size: clamp(0.8rem, 1.2vw, 1rem); opacity: 0.7;"><?= esc($penampilanMerah->nama_kontingen ?? '-') ?></div>
                    <div class="comparison-nilai penilaian-display-font mt-3"><?= number_format($nilaiMerah, 3) ?></div>
                </div>
            </div>

            <!-- STATS -->
            <div class="row mt-4 gx-3">
                <div class="col-5">
                    <?php if ($statsBiru): ?>
                        <?php foreach (['median' => 'Median', 'hukuman' => 'Penalty', 'median_kebenaran' => 'Median Kebenaran', 'standar_deviasi' => 'Std Deviasi'] as $key => $label): ?>
                            <div class="row mb-1">
                                <div class="col-12 py-1 bg-gradient-180-gray-dark text-white text-center comparison-label"><?= $label ?></div>
                                <div class="col-12 py-1 bg-gradient-180-white text-dark text-center comparison-val">
                                    <?= isset($statsBiru->$key) ? (is_numeric($statsBiru->$key) ? number_format((float)$statsBiru->$key, 3) : esc($statsBiru->$key)) : '-' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="col-2"></div>
                <div class="col-5">
                    <?php if ($statsMerah): ?>
                        <?php foreach (['median' => 'Median', 'hukuman' => 'Penalty', 'median_kebenaran' => 'Median Kebenaran', 'standar_deviasi' => 'Std Deviasi'] as $key => $label): ?>
                            <div class="row mb-1">
                                <div class="col-12 py-1 bg-gradient-180-gray-dark text-white text-center comparison-label"><?= $label ?></div>
                                <div class="col-12 py-1 bg-gradient-180-white text-dark text-center comparison-val">
                                    <?= isset($statsMerah->$key) ? (is_numeric($statsMerah->$key) ? number_format((float)$statsMerah->$key, 3) : esc($statsMerah->$key)) : '-' ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- WINNER -->
    <?php if ($hasWinner): ?>
    <div class="winner-screen d-none" id="winner-screen">
        <div class="d-flex flex-column align-items-center justify-content-center min-vh-100">
            <i class="fa-solid fa-trophy mb-3" style="font-size: clamp(4rem, 10vw, 8rem); color: #ffd700;"></i>
            <div style="font-size: clamp(1.2rem, 2.5vw, 2rem); letter-spacing: 4px; opacity: 0.7;">CONGRATULATIONS!</div>
            <div class="w-100 py-4 mt-3 text-center <?= $isWinnerBiru ? 'bg-gradient-180-blue' : 'bg-gradient-180-red' ?>">
                <div class="winner-nama penilaian-display-font">
                    <?= $isWinnerBiru ? $namaBiru : $namaMerah ?>
                </div>
                <div class="winner-side mt-2">
                    <?= $isWinnerBiru ? esc($penampilanBiru->nama_kontingen ?? '-') : esc($penampilanMerah->nama_kontingen ?? '-') ?>
                </div>
                <div class="penilaian-display-font mt-3" style="font-size: clamp(3rem, 8vw, 6rem); font-weight: 700;">
                    <?= number_format($isWinnerBiru ? $nilaiBiru : $nilaiMerah, 3) ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var hasWinner = <?= $hasWinner ? 'true' : 'false' ?>;

    function showScreen(screenId) {
        document.querySelectorAll('.countdown-screen, .comparison-screen, .winner-screen').forEach(function(el) {
            el.classList.remove('active');
            el.style.display = 'none';
        });
        var el = document.getElementById(screenId);
        if (el) { el.style.display = 'flex'; el.classList.add('active'); }
    }

    // Countdown 5 -> 1
    var count = 5;
    var countdownInterval = setInterval(function() {
        count--;
        document.getElementById('countdown-num').textContent = count;
        if (count <= 0) {
            clearInterval(countdownInterval);
            showScreen('comparison-screen');

            if (hasWinner) {
                setTimeout(function() {
                    showScreen('winner-screen');

                    // Auto-redirect after winner display
                    setTimeout(function() {
                        window.location.href = '<?= base_url('layar/seni') ?>';
                    }, 15000);
                }, 10000);
            } else {
                setTimeout(function() {
                    window.location.href = '<?= base_url('layar/seni') ?>';
                }, 20000);
            }
        }
    }, 1000);

    // Polling for new penampilan
    var pollCount = 0;
    var maxPoll = 90;

    function pollNewPenampilan() {
        if (pollCount >= maxPoll) {
            window.location.href = '<?= base_url('layar/standby?mode=seni') ?>';
            return;
        }
        pollCount++;

        var body = new URLSearchParams();
        body.append(csrfName, csrfHash);

        fetch('<?= base_url('layar/refresh-status-seni') ?>', {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            if (d && d.status === false && d.id_penampilan_seni) {
                window.location.href = '<?= base_url('layar/seni') ?>';
            }
        })
        .catch(function() {})
        .finally(function() {
            if (pollCount < maxPoll) setTimeout(pollNewPenampilan, 2000);
        });
    }

    setTimeout(pollNewPenampilan, 30000);
})();
</script>
<?= $this->endSection() ?>
