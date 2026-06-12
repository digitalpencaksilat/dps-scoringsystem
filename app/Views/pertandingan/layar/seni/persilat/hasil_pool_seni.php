<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
    body { background: #000 !important; overflow: hidden; }
    .rekap-pool { display: none; }

    .countdown-screen {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .countdown-screen .countdown-text {
        font-size: 4rem;
        font-weight: 700;
        color: #fff;
        animation: pulse 1s ease-in-out infinite;
    }
    .countdown-screen .countdown-number {
        font-size: 12rem;
        font-weight: 900;
        color: #ffc107;
        line-height: 1;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .result-screen { display: none; }
    .result-table { width: 100%; }
    .result-table .rank-row {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.5rem;
        margin-bottom: 0.5rem;
        border-radius: 0.5rem;
        transition: transform 0.3s ease;
    }
    .result-table .rank-row:hover { transform: scale(1.01); }
    .rank-row.emas { background: linear-gradient(135deg, #ffd700 0%, #ffa800 100%); color: #000; }
    .rank-row.perak { background: linear-gradient(135deg, #e8e8e8 0%, #b8b8b8 100%); color: #000; }
    .rank-row.perunggu { background: linear-gradient(135deg, #cd7f32 0%, #8b4513 100%); color: #fff; }
    .rank-row.default { background: linear-gradient(135deg, #2c2c2c 0%, #1a1a1a 100%); color: #fff; }

    .rank-number { font-size: 2.5rem; font-weight: 900; width: 80px; text-align: center; }
    .rank-info { flex: 1; padding: 0 1rem; }
    .rank-info .rank-name { font-size: 1.5rem; font-weight: 700; }
    .rank-info .rank-kontingen { font-size: 1rem; opacity: 0.8; }
    .rank-stats { display: flex; gap: 2rem; align-items: center; }
    .rank-stats .stat { text-align: center; }
    .rank-stats .stat-value { font-size: 1.8rem; font-weight: 800; }
    .rank-stats .stat-label { font-size: 0.75rem; text-transform: uppercase; opacity: 0.7; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Countdown -->
<div class="container-fluid min-vh-100 bg-black" id="countdown-container">
    <div class="countdown-screen">
        <p class="countdown-text">The Winner is..</p>
        <p class="countdown-number" id="countdown-number">5</p>
    </div>
</div>

<!-- Results -->
<div class="container-fluid min-vh-100 bg-black rekap-pool px-4 py-3" id="result-container">
    <div class="result-screen">
        <!-- Header -->
        <div class="row mb-3">
            <div class="col-12 text-center">
                <h1 class="text-white fw-bold mb-1">Performance Results</h1>
                <p class="text-white-50 h4 m-0">
                    <?= strtoupper(esc($kompetisi_seni->nama_kategori_usia ?? '')) ?> — <?= strtoupper(esc($kompetisi_seni->jenis_seni ?? '')) ?>
                </p>
            </div>
        </div>

        <!-- Rankings -->
        <div class="result-table">
            <?php
                $rankColors = ['emas', 'perak', 'perunggu'];
                foreach ($daftar as $index => $item):
                    $rankClass = $rankColors[$index] ?? 'default';
                    $namaPeserta = [];
                    if (!empty($item->peserta)) {
                        foreach ($item->peserta as $ps) {
                            $namaPeserta[] = esc($ps->nama_pendaftar ?? '');
                        }
                    }
                    $catatan = null;
                    if (!empty($item->catatan_nilai_sama)) {
                        $catatan = is_string($item->catatan_nilai_sama) ? json_decode($item->catatan_nilai_sama) : $item->catatan_nilai_sama;
                    }
                    $waktuTampil = (int) ($item->waktu_tampil ?? 0);
                    $menit = str_pad(floor($waktuTampil / 60), 2, '0', STR_PAD_LEFT);
                    $detik = str_pad($waktuTampil % 60, 2, '0', STR_PAD_LEFT);
            ?>
            <div class="rank-row <?= $rankClass ?>">
                <div class="rank-number"><?= $index + 1 ?></div>
                <div class="rank-info">
                    <p class="rank-name m-0"><?= !empty($item->anggota_kelompok_peserta_seni) ? esc($item->anggota_kelompok_peserta_seni) : (implode(' &bull; ', $namaPeserta) ?: '-') ?></p>
                    <p class="rank-kontingen m-0"><?= strtoupper(esc($item->nama_kontingen ?? '')) ?></p>
                </div>
                <div class="rank-stats">
                    <div class="stat">
                        <p class="stat-value m-0"><?= number_format((float)($catatan->median_kebenaran ?? 0), 3) ?></p>
                        <p class="stat-label m-0">Median Keb.</p>
                    </div>
                    <div class="stat">
                        <p class="stat-value m-0"><?= number_format((float)($item->nilai_akhir ?? 0), 3) ?></p>
                        <p class="stat-label m-0">Final Score</p>
                    </div>
                    <div class="stat">
                        <p class="stat-value m-0"><?= $menit ?>:<?= $detik ?></p>
                        <p class="stat-label m-0">Time</p>
                    </div>
                    <div class="stat">
                        <p class="stat-value m-0"><?= number_format((float)($catatan->standar_deviasi ?? 0), 3) ?></p>
                        <p class="stat-label m-0">Std Dev</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    var count = 5;
    var countdownInterval = setInterval(function () {
        count--;
        $('#countdown-number').text(count);
        if (count <= 0) {
            clearInterval(countdownInterval);
            $('#countdown-container').fadeOut(500, function () {
                $('#result-container').show().css('display', 'block');
                $('#result-container .result-screen').fadeIn(800);
            });
        }
    }, 1000);

    // Poll for navigation (next performance or back to standby)
    setInterval(function () {
        $.post(BASE_URL + "layar/refresh-status-seni", function (data) {
            if (data.status === false && data.id_penampilan_seni) {
                window.location.reload();
            }
        }, "json");
    }, 4000);
});
</script>
<?= $this->endSection() ?>
