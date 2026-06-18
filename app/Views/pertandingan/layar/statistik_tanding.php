<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.bg-gradient-180-blue { background: linear-gradient(180deg, #1565c0, #0d47a1) !important; }
.bg-gradient-180-red { background: linear-gradient(180deg, #c62828, #8e0000) !important; }
.bg-gradient-180-dark { background: linear-gradient(180deg, #2c2c2c, #1a1a1a) !important; }
.bg-gradient-180-white { background: linear-gradient(180deg, #f8f9fa, #e9ecef) !important; }
.bg-gradient-180-gray-dark { background: linear-gradient(180deg, #343a40, #212529) !important; }

.statistik-page { background: radial-gradient(ellipse at 50% 30%, #1a2332 0%, #0b0d12 70%); min-height: 100vh; color: #fff; }

.statistik-biru-header { font-size: clamp(1.5rem, 3vw, 2.5rem); font-weight: 700; letter-spacing: 2px; }
.statistik-merah-header { font-size: clamp(1.5rem, 3vw, 2.5rem); font-weight: 700; letter-spacing: 2px; }
.statistik-nilai { font-size: clamp(1.2rem, 2vw, 1.8rem); font-weight: 600; }
.statistik-label { font-size: clamp(0.8rem, 1.2vw, 1rem); font-weight: 500; opacity: 0.85; }

/* Slide-in anim */
@keyframes slideInDown {
    from { opacity: 0; transform: translateY(-40px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes slideInUp {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animated { animation-duration: 0.6s; animation-fill-mode: both; }
.slideInDown { animation-name: slideInDown; }
.slideInUp { animation-name: slideInUp; }
.delay-1s { animation-delay: 1s; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$labelMap = [
    'pukulan'      => 'Punches',
    'tendangan'    => 'Kicks',
    'jatuhan'      => 'Dropping',
    'binaan_1'     => 'Verbal Warning 1',
    'binaan_2'     => 'Verbal Warning 2',
    'teguran_1'    => 'Reprimand Warning 1',
    'teguran_2'    => 'Reprimand Warning 2',
    'peringatan_1' => 'Warning 1',
    'peringatan_2' => 'Warning 2',
    'nilai_akhir'  => 'Final Score',
];

$hasStats = !empty($ringkasan_nilai) && isset($ringkasan_nilai->semua_ronde);

// Build competition title info
$info_left   = [];
$info_center = [];
$info_right  = [];

if (!empty($pertandingan->nama_gelanggang)) $info_left[]  = $pertandingan->nama_gelanggang;
if (!empty($pertandingan->nomor_partai))     $info_left[]  = 'Partai ' . esc($pertandingan->nomor_partai);

if (!empty($pertandingan->label))              $info_center[] = esc($pertandingan->label);
if (!empty($pertandingan->nama_kategori_lomba)) $info_center[] = esc($pertandingan->nama_kategori_lomba);
if (!empty($pertandingan->nama_kategori_usia))  $info_center[] = esc($pertandingan->nama_kategori_usia);

if (!empty($pertandingan->jenis_kelamin))    $info_right[] = esc($pertandingan->jenis_kelamin);
if (!empty($pertandingan->format_penilaian)) $info_right[] = esc($pertandingan->format_penilaian);
if (!empty($pertandingan->babak))            $info_right[] = esc(ucwords($pertandingan->babak));

$namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
$namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
?>
<div class="statistik-page">
    <?= view('pertandingan/layar/components/competition_title', [
        'event_name'  => $event_name ?? 'Pencak Silat Championship',
        'info_left'   => $info_left,
        'info_center' => $info_center,
        'info_right'  => $info_right,
    ]) ?>

    <div class="container-fluid px-3 mt-3">
        <!-- Statistik header -->
        <div class="row mb-3 animated slideInDown">
            <div class="col-12 text-center bg-gradient-180-dark py-3">
                <span class="penilaian-display-font" style="font-size: clamp(1.5rem, 3.5vw, 2.5rem); font-weight: 700; letter-spacing: 3px;">
                    RINGKASAN NILAI
                </span>
                <div style="font-size: clamp(0.8rem, 1.2vw, 1rem); opacity: 0.7; margin-top: 0.25rem;">
                    Partai <?= esc($pertandingan->nomor_partai ?? '-') ?> — Final Score: <?= (int) ($pertandingan->skor_biru ?? 0) ?> - <?= (int) ($pertandingan->skor_merah ?? 0) ?>
                </div>
            </div>
        </div>

        <?php if ($hasStats): ?>
            <!-- Sudut headers -->
            <div class="row mb-2 animated slideInDown delay-1s">
                <div class="col-5 text-center py-2 bg-gradient-180-blue statistik-biru-header penilaian-display-font">
                    <?= esc($namaBiru) ?>
                </div>
                <div class="col-2"></div>
                <div class="col-5 text-center py-2 bg-gradient-180-red statistik-merah-header penilaian-display-font">
                    <?= esc($namaMerah) ?>
                </div>
            </div>

            <!-- Stats table -->
            <div class="row animated slideInUp delay-1s">
                <div class="col-12 px-4">
                    <div class="row justify-content-center">
                        <div class="col">
                            <?php foreach ($ringkasan_nilai->semua_ronde->biru as $jenis => $nilai): ?>
                                <div class="row mb-1">
                                    <div class="col-12 py-2 text-center statistik-nilai bg-gradient-180-white text-dark">
                                        <?= is_numeric($nilai) ? number_format((float)$nilai, 1) : esc($nilai) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-3">
                            <?php foreach ($ringkasan_nilai->semua_ronde->biru as $jenis => $nilai): ?>
                                <div class="row mb-1">
                                    <div class="col-12 py-2 text-center text-truncate statistik-label bg-gradient-180-gray-dark text-white">
                                        <?= esc($labelMap[$jenis] ?? ucwords(str_replace('_', ' ', $jenis))) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col">
                            <?php foreach ($ringkasan_nilai->semua_ronde->merah as $jenis => $nilai): ?>
                                <div class="row mb-1">
                                    <div class="col-12 py-2 text-center statistik-nilai bg-gradient-180-white text-dark">
                                        <?= is_numeric($nilai) ? number_format((float)$nilai, 1) : esc($nilai) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- fallback: no ringkasan_nilai -->
            <div class="row animated slideInUp delay-1s">
                <div class="col-12 text-center py-5">
                    <i class="fas fa-chart-bar mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                    <p style="font-size: clamp(1rem, 2vw, 1.5rem); opacity: 0.5;">
                        Data ringkasan nilai belum tersedia.<br>
                        <small>Skor Akhir: Biru <?= (int) ($pertandingan->skor_biru ?? 0) ?> — <?= (int) ($pertandingan->skor_merah ?? 0) ?> Merah</small>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var pollCount = 0;
    var maxPoll = 90;

    // Redirect ke standby setelah 5 detik
    setTimeout(function () {
        window.location.href = '<?= base_url('layar/standby?mode=tanding') ?>';
    }, 5000);

    // Polling for new match (runs in background)
    function pollNextMatch() {
        if (pollCount >= maxPoll) return;
        pollCount++;

        var body = new URLSearchParams();
        body.append(csrfName, csrfHash);

        fetch('<?= base_url('layar/refresh-status-pertandingan') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            if (d && d.status === false && d.id_pertandingan) {
                window.location.href = '<?= base_url('layar/tanding') ?>';
            }
        })
        .catch(function() {})
        .finally(function() {
            if (pollCount < maxPoll) setTimeout(pollNextMatch, 2000);
        });
    }

    setTimeout(pollNextMatch, 2000);
})();
</script>
<?= $this->endSection() ?>
