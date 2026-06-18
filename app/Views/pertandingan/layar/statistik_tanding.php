<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.bg-gradient-180-blue { background: linear-gradient(180deg, #1565c0, #0d47a1) !important; }
.bg-gradient-180-red { background: linear-gradient(180deg, #c62828, #8e0000) !important; }
.bg-gradient-180-dark { background: linear-gradient(180deg, #2c2c2c, #1a1a1a) !important; }
.bg-gradient-180-white { background: linear-gradient(180deg, #f8f9fa, #e9ecef) !important; }
.bg-gradient-180-gray-dark { background: linear-gradient(180deg, #343a40, #212529) !important; }

.statistik-page {
    background: radial-gradient(ellipse at 50% 30%, #1a2332 0%, #0b0d12 70%);
    height: 100vh; overflow: hidden;
    color: #fff;
    display: flex; flex-direction: column;
}

/* Override competition_title default opacity:0 */
.statistik-page #competition-title { opacity: 1 !important; }

/* Competition title takes natural height */
.statistik-page > .row:first-child { flex-shrink: 0; }

/* Stats container fills remaining vh */
.statistik-body {
    flex: 1;
    display: flex; flex-direction: column;
    padding: 0 3vw;
    overflow: hidden;
}

.statistik-header-row {
    flex-shrink: 0;
    padding: 1vh 0;
    display: flex; align-items: center; justify-content: center;
}
.statistik-header-row .bg-gradient-180-dark {
    width: 100%; text-align: center;
    padding: 1.2vh 0;
}
.statistik-header-title {
    font-size: clamp(1.2rem, 2.5vw, 2rem);
    font-weight: 700; letter-spacing: 3px;
}
.statistik-header-sub {
    font-size: clamp(0.7rem, 1vw, 0.9rem);
    opacity: 0.7; margin-top: 0.25vh;
}

/* Athlete name row */
.statistik-names-row {
    flex-shrink: 0;
    display: flex; gap: 0.5vw;
    margin-bottom: 0.5vh;
}
.statistik-name-col {
    flex: 5;
    text-align: center;
    padding: 1vh 0;
    font-size: clamp(1rem, 1.8vw, 1.6rem);
    font-weight: 700;
}
.statistik-name-gap { flex: 2; }

/* Stats list: fills remaining vh with equal rows */
.statistik-list {
    flex: 1;
    display: flex; flex-direction: column;
    gap: 1px;
    overflow: hidden;
}
.statistik-row {
    flex: 1;
    display: flex; gap: 0.5vw;
    min-height: 0;
}
.statistik-row.final-row { flex: 1.3; }

.statistik-col {
    flex: 5;
    display: flex; align-items: center; justify-content: center;
    font-size: clamp(1.2rem, 2.2vw, 2rem);
    font-weight: 600;
}
.statistik-col-label {
    flex: 2;
    display: flex; align-items: center; justify-content: center;
    font-size: clamp(0.7rem, 1vw, 0.9rem);
    font-weight: 500; opacity: 0.85;
    text-align: center; line-height: 1.2;
}

/* Fallback */
.statistik-fallback {
    flex: 1;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    opacity: 0.5;
}

/* Slide anim */
@keyframes slideInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes slideInUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animated { animation-duration: 0.5s; animation-fill-mode: both; }
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

    <?php if ($hasStats):
        $statsBiru  = (array) $ringkasan_nilai->semua_ronde->biru;
        $statsMerah = (array) $ringkasan_nilai->semua_ronde->merah;
    ?>
    <div class="statistik-body animated slideInUp">
        <!-- Header -->
        <div class="statistik-header-row animated slideInDown">
            <div class="bg-gradient-180-dark">
                <div class="statistik-header-title penilaian-display-font">RINGKASAN NILAI</div>
                <div class="statistik-header-sub">
                    Partai <?= esc($pertandingan->nomor_partai ?? '-') ?>
                    &nbsp;—&nbsp;
                    Final Score: <strong><?= (int) ($pertandingan->skor_biru ?? 0) ?></strong>
                    &nbsp;-&nbsp;
                    <strong><?= (int) ($pertandingan->skor_merah ?? 0) ?></strong>
                </div>
            </div>
        </div>

        <!-- Athlete names -->
        <div class="statistik-names-row animated slideInDown delay-1s">
            <div class="statistik-name-col bg-gradient-180-blue penilaian-display-font"><?= esc($namaBiru) ?></div>
            <div class="statistik-name-gap"></div>
            <div class="statistik-name-col bg-gradient-180-red penilaian-display-font"><?= esc($namaMerah) ?></div>
        </div>

        <!-- Stats rows -->
        <div class="statistik-list animated slideInUp delay-1s">
            <?php
                $keys = array_keys($statsBiru);
                $last = end($keys);
            ?>
            <?php foreach ($statsBiru as $jenis => $nilaiBiru): ?>
                <?php $nilaiMerah = $statsMerah[$jenis] ?? 0; ?>
                <div class="statistik-row <?= $jenis === $last ? 'final-row' : '' ?>">
                    <div class="statistik-col bg-gradient-180-white text-dark penilaian-display-font">
                        <?= is_numeric($nilaiBiru) ? ((int)$nilaiBiru == $nilaiBiru ? (int)$nilaiBiru : number_format((float)$nilaiBiru, 1)) : esc($nilaiBiru) ?>
                    </div>
                    <div class="statistik-col-label bg-gradient-180-gray-dark text-white">
                        <?= esc($labelMap[$jenis] ?? ucwords(str_replace('_', ' ', $jenis))) ?>
                    </div>
                    <div class="statistik-col bg-gradient-180-white text-dark penilaian-display-font">
                        <?= is_numeric($nilaiMerah) ? ((int)$nilaiMerah == $nilaiMerah ? (int)$nilaiMerah : number_format((float)$nilaiMerah, 1)) : esc($nilaiMerah) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php else: ?>
    <div class="statistik-fallback animated slideInUp delay-1s">
        <i class="fas fa-chart-bar mb-3" style="font-size: 4rem;"></i>
        <p style="font-size: clamp(1rem, 2vw, 1.5rem);">
            Data ringkasan nilai belum tersedia.
        </p>
        <p style="font-size: clamp(0.8rem, 1.2vw, 1rem);">
            Skor Akhir: Biru <?= (int) ($pertandingan->skor_biru ?? 0) ?> — <?= (int) ($pertandingan->skor_merah ?? 0) ?> Merah
        </p>
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
    var pollCount = 0;
    var maxPoll = 90;

    setTimeout(function () {
        window.location.href = '<?= base_url('layar/standby?mode=tanding') ?>';
    }, 5000);

    function pollNextMatch() {
        if (pollCount >= maxPoll) return;
        pollCount++;
        var body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('layar/refresh-status-pertandingan') ?>', {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
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
