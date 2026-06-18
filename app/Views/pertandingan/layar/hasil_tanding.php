<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.bg-gradient-180-blue { background: linear-gradient(180deg, #1565c0, #0d47a1) !important; }
.bg-gradient-180-red { background: linear-gradient(180deg, #c62828, #8e0000) !important; }
.bg-gradient-180-dark { background: linear-gradient(180deg, #2c2c2c, #1a1a1a) !important; }
.bg-gradient-180-white { background: linear-gradient(180deg, #f8f9fa, #e9ecef) !important; }
.bg-gradient-180-gray-dark { background: linear-gradient(180deg, #343a40, #212529) !important; }

.hasil-tanding-page { background: radial-gradient(ellipse at 50% 30%, #1a2332 0%, #0b0d12 70%); min-height: 100vh; color: #fff; }

.hasil-winner-name { font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700; }
.hasil-winner-kontingen { font-size: clamp(1rem, 2vw, 1.5rem); opacity: 0.85; }
.hasil-winby { font-size: clamp(1.2rem, 2.5vw, 2rem); font-weight: 600; }
.hasil-skor-biru { font-size: clamp(6rem, 14vw, 12rem); font-weight: 700; line-height: 1; }
.hasil-skor-merah { font-size: clamp(6rem, 14vw, 12rem); font-weight: 700; line-height: 1; }

/* Slide animations */
@keyframes slideInDown {
    from { opacity: 0; transform: translateY(-40px); }
    to   { opacity: 1; transform: translateY(0); }
}
.animated { animation-duration: 0.6s; animation-fill-mode: both; }
.slideInDown { animation-name: slideInDown; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="hasil-tanding-page">
    <?php
        $info_left  = [];
        $info_center = [];
        $info_right = [];

        if (!empty($pertandingan->nama_gelanggang)) $info_left[]  = $pertandingan->nama_gelanggang;
        if (!empty($pertandingan->nomor_partai))     $info_left[]  = 'Partai ' . esc($pertandingan->nomor_partai);

        if (!empty($pertandingan->label)) $info_center[] = esc($pertandingan->label);
        if (!empty($pertandingan->nama_kategori_lomba)) $info_center[] = esc($pertandingan->nama_kategori_lomba);
        if (!empty($pertandingan->nama_kategori_usia))  $info_center[] = esc($pertandingan->nama_kategori_usia);

        if (!empty($pertandingan->jenis_kelamin))       $info_right[] = esc($pertandingan->jenis_kelamin);
        if (!empty($pertandingan->format_penilaian))    $info_right[] = esc($pertandingan->format_penilaian);
        if (!empty($pertandingan->babak))               $info_right[] = esc(ucwords($pertandingan->babak));

        $side = $winner_side ?? '';
    ?>
    <?= view('pertandingan/layar/components/competition_title', [
        'event_name'  => $event_name ?? 'Pencak Silat Championship',
        'info_left'   => $info_left,
        'info_center' => $info_center,
        'info_right'  => $info_right,
    ]) ?>

    <!-- WINNER SECTION (visible first) -->
    <div id="winner-section" class="animated slideInDown">
        <div class="container-fluid px-3 mt-3">
            <div class="row mb-2">
                <div class="col-12 text-center bg-gradient-180-dark py-2">
                    <span class="penilaian-display-font" style="font-size: clamp(1.2rem, 2.5vw, 1.8rem); font-weight: 600; letter-spacing: 2px;">
                        Partai <?= esc($pertandingan->nomor_partai ?? '-') ?> Winner
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-12 py-4 text-center <?= $side === 'biru' ? 'bg-gradient-180-blue' : 'bg-gradient-180-red' ?>">
                    <div class="hasil-winner-name penilaian-display-font">
                        <?= esc($pemenang->nama_pendaftar ?? ($side === 'biru' ? ($atlet_biru->nama_pendaftar ?? '-') : ($atlet_merah->nama_pendaftar ?? '-'))) ?>
                    </div>
                    <div class="hasil-winner-kontingen mt-2">
                        <?= esc($pemenang->nama_kontingen ?? ($side === 'biru' ? ($atlet_biru->nama_kontingen ?? '-') : ($atlet_merah->nama_kontingen ?? '-'))) ?>
                    </div>
                </div>
            </div>

            <div class="row mx-0 my-4 mx-3 shadow-lg">
                <?php if (!empty($pertandingan->jenis_kemenangan)): ?>
                    <div class="col-lg-6 px-0 bg-gradient-180-white text-dark text-center py-4 d-flex flex-column justify-content-center">
                        <div style="font-size: clamp(0.9rem, 1.5vw, 1.2rem); font-style: italic; opacity: 0.7;">Win By</div>
                        <div class="hasil-winby penilaian-display-font text-uppercase"><?= esc(str_replace('_', ' ', $pertandingan->jenis_kemenangan)) ?></div>
                    </div>
                    <div class="col-lg-6 px-0">
                <?php else: ?>
                    <div class="col-12 px-0">
                <?php endif; ?>
                    <div class="row gx-0">
                        <div class="col-6 px-0 bg-gradient-180-blue text-center py-4">
                            <div class="penilaian-display-font hasil-skor-biru"><?= (int) ($pertandingan->skor_biru ?? 0) ?></div>
                        </div>
                        <div class="col-6 px-0 bg-gradient-180-red text-center py-4">
                            <div class="penilaian-display-font hasil-skor-merah"><?= (int) ($pertandingan->skor_merah ?? 0) ?></div>
                        </div>
                    </div>
                </div>
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
    var pollCount = 0;
    var maxPoll = 90;

    // After 5s: redirect ke halaman statistik (ringkasan_nilai)
    var statsUrl = '<?= base_url('layar/statistik-tanding/' . ($pertandingan->id_pertandingan ?? 0)) ?>';
    setTimeout(function () {
        window.location.href = statsUrl;
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
