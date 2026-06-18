<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.bg-gradient-180-dark { background: linear-gradient(180deg, #2c2c2c, #1a1a1a) !important; }
.bg-gradient-180-white { background: linear-gradient(180deg, #f8f9fa, #e9ecef) !important; }
.bg-gradient-180-gray-dark { background: linear-gradient(180deg, #343a40, #212529) !important; }
.bg-gradient-180-light { background: linear-gradient(180deg, #e2e8f0, #cbd5e1) !important; }

.pool-hasil-page { background: radial-gradient(ellipse at 50% 30%, #1a2332 0%, #0b0d12 70%); min-height: 100vh; color: #fff; }

.countdown-screen, .result-screen { display: none; }
.countdown-screen.active, .result-screen.active { display: flex; }

.countdown-num { font-size: clamp(6rem, 15vw, 14rem); font-weight: 700; color: #c5a017; }

.medal-emas td { background: linear-gradient(180deg, #ffd700 55%, #ffa800 75%) !important; color: #1a1a1a !important; font-weight: 600; }
.medal-perak td { background: linear-gradient(180deg, #e2e8f0, #cbd5e1) !important; color: #1a1a1a !important; font-weight: 600; }
.medal-perunggu td { background: linear-gradient(180deg, #a86e00 55%, #513500 75%) !important; color: #fff !important; font-weight: 600; }
.medal-default td { background: linear-gradient(180deg, #343a40, #212529) !important; color: #fff !important; }

.rank-num { font-size: clamp(1.2rem, 2vw, 1.8rem); font-weight: 700; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$kompetisi = $kompetisi_seni ?? null;
$daftar    = $daftar ?? [];

$judul = esc($kompetisi->nama_kategori_usia ?? '') . ' - ' . esc($kompetisi->jenis_seni ?? 'Seni');
$jenisSeni = esc($kompetisi->jenis_seni ?? '');
?>
<div class="pool-hasil-page">

    <!-- COUNTDOWN -->
    <div class="countdown-screen active d-flex flex-column align-items-center justify-content-center" id="countdown-screen">
        <div style="font-size: clamp(1.5rem, 3vw, 2.5rem); letter-spacing: 4px; opacity: 0.7; margin-bottom: 2vh;">HASIL KOMPETISI</div>
        <div style="font-size: clamp(1rem, 2vw, 1.5rem); opacity: 0.6; margin-bottom: 3vh;"><?= esc($jenisSeni) ?></div>
        <div class="countdown-num penilaian-display-font" id="countdown-num">5</div>
    </div>

    <!-- RESULT TABLE -->
    <div class="result-screen d-none" id="result-screen">
        <div class="container-fluid px-3 py-4">
            <div class="row mb-3">
                <div class="col-12 text-center py-3 bg-gradient-180-gray-dark">
                    <div class="penilaian-display-font" style="font-size: clamp(1.5rem, 3.5vw, 2.5rem); font-weight: 700; letter-spacing: 3px;">
                        HASIL <?= strtoupper($jenisSeni) ?>
                    </div>
                    <div style="font-size: clamp(0.9rem, 1.5vw, 1.2rem); opacity: 0.7; margin-top: 0.25rem;">
                        <?= esc($kompetisi->nama_kategori_usia ?? '') ?> - Sistem Pool
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-borderless mb-0" style="font-size: clamp(0.8rem, 1.2vw, 1rem);">
                    <thead>
                        <tr class="bg-gradient-180-dark text-white">
                            <th class="text-center" style="width:70px">#</th>
                            <th>Peserta</th>
                            <th>Kontingen</th>
                            <th class="text-center">Median Kebenaran</th>
                            <th class="text-center">Nilai Akhir</th>
                            <th class="text-center">Waktu</th>
                            <th class="text-center">Std Deviasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($daftar)): ?>
                            <?php
                                $medaliMap = ['emas' => 'medal-emas', 'perak' => 'medal-perak', 'perunggu' => 'medal-perunggu'];
                            ?>
                            <?php foreach ($daftar as $idx => $item): ?>
                                <?php
                                    $medali   = $item->jenis_medali ?? null;
                                    $medaliClass = $medaliMap[$medali] ?? 'medal-default';
                                    $stats    = !empty($item->catatan_nilai_sama) ? json_decode($item->catatan_nilai_sama) : null;
                                ?>
                                <tr class="<?= $medaliClass ?>">
                                    <td class="text-center">
                                        <span class="rank-num penilaian-display-font"><?= $idx + 1 ?></span>
                                    </td>
                                    <td><?= esc($item->anggota_kelompok_peserta_seni ?? '-') ?></td>
                                    <td><?= esc($item->nama_kontingen ?? '-') ?></td>
                                    <td class="text-center">
                                        <?= isset($stats->median_kebenaran) ? number_format((float)$stats->median_kebenaran, 3) : '-' ?>
                                    </td>
                                    <td class="text-center penilaian-display-font" style="font-weight: 700;">
                                        <?= number_format((float) ($item->nilai_akhir ?? 0), 3) ?>
                                    </td>
                                    <td class="text-center"><?= esc((int) ($item->waktu_tampil ?? 0)) ?>s</td>
                                    <td class="text-center">
                                        <?= isset($stats->standar_deviasi) ? number_format((float)$stats->standar_deviasi, 3) : '-' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Belum ada data penilaian</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';

    function showScreen(screenId) {
        document.querySelectorAll('.countdown-screen, .result-screen').forEach(function(el) {
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
            showScreen('result-screen');
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

    setTimeout(pollNewPenampilan, 10000);
})();
</script>
<?= $this->endSection() ?>
