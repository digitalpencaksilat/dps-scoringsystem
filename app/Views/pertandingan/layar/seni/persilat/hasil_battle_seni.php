<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
    body { background: #000 !important; overflow: hidden; }

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

    .comparison-screen, .winner-screen { display: none; min-height: 100vh; }

    .corner-card {
        border-radius: 1rem;
        padding: 2rem;
        min-height: 60vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .corner-blue { background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%); }
    .corner-red { background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%); }

    .corner-card .corner-name {
        font-size: clamp(1.5rem, 4vw, 2.5rem);
        font-weight: 800;
        color: #fff;
    }
    .corner-card .corner-kontingen {
        font-size: 1.2rem;
        color: rgba(255,255,255,0.8);
    }
    .corner-card .corner-score {
        font-size: clamp(5rem, 15vw, 10rem);
        font-weight: 900;
        color: #fff;
        line-height: 1;
    }
    .corner-card .corner-stats {
        display: flex;
        gap: 1.5rem;
        margin-top: 1rem;
    }
    .corner-card .stat-item {
        text-align: center;
    }
    .corner-card .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
    }
    .corner-card .stat-label {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.7);
        text-transform: uppercase;
    }

    .vs-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        font-weight: 900;
        color: #ffc107;
    }

    .winner-screen {
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .winner-badge {
        font-size: 2rem;
        font-weight: 700;
        color: #ffc107;
        text-transform: uppercase;
        letter-spacing: 5px;
        margin-bottom: 1rem;
    }
    .winner-name {
        font-size: clamp(2rem, 6vw, 4rem);
        font-weight: 900;
        color: #fff;
    }
    .winner-kontingen {
        font-size: 1.5rem;
        color: rgba(255,255,255,0.8);
    }
    .winner-score {
        font-size: clamp(6rem, 18vw, 12rem);
        font-weight: 900;
        line-height: 1;
        margin: 1rem 0;
    }
    .winner-blue .winner-score { color: #42a5f5; }
    .winner-red .winner-score { color: #ef5350; }
    .winner-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
    }
    .winner-stats .stat-item { text-align: center; }
    .winner-stats .stat-value { font-size: 1.8rem; font-weight: 700; color: #fff; }
    .winner-stats .stat-label { font-size: 0.85rem; color: rgba(255,255,255,0.6); text-transform: uppercase; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // Parse catatan_nilai_sama for both sides
    $catatanBiru = null;
    $catatanMerah = null;
    if (!empty($penampilan_seni_biru->catatan_nilai_sama)) {
        $catatanBiru = is_string($penampilan_seni_biru->catatan_nilai_sama)
            ? json_decode($penampilan_seni_biru->catatan_nilai_sama) : $penampilan_seni_biru->catatan_nilai_sama;
    }
    if (!empty($penampilan_seni_merah->catatan_nilai_sama)) {
        $catatanMerah = is_string($penampilan_seni_merah->catatan_nilai_sama)
            ? json_decode($penampilan_seni_merah->catatan_nilai_sama) : $penampilan_seni_merah->catatan_nilai_sama;
    }

    $nilaiAkhirBiru = (float) ($penampilan_seni_biru->nilai_akhir ?? 0);
    $nilaiAkhirMerah = (float) ($penampilan_seni_merah->nilai_akhir ?? 0);

    $waktuBiru = (int) ($penampilan_seni_biru->waktu_tampil ?? 0);
    $waktuMerah = (int) ($penampilan_seni_merah->waktu_tampil ?? 0);

    // Peserta names (prefer anggota_kelompok_peserta_seni from kps, fallback to individual names)
    $namaBiru = [];
    if (!empty($penampilan_seni_biru->anggota_kelompok_peserta_seni)) {
        $namaBiru = [esc($penampilan_seni_biru->anggota_kelompok_peserta_seni)];
    } else {
        foreach ($peserta_seni_biru as $ps) { $namaBiru[] = esc($ps->nama_pendaftar ?? ''); }
    }
    $namaMerah = [];
    if (!empty($penampilan_seni_merah->anggota_kelompok_peserta_seni)) {
        $namaMerah = [esc($penampilan_seni_merah->anggota_kelompok_peserta_seni)];
    } else {
        foreach ($peserta_seni_merah as $ps) { $namaMerah[] = esc($ps->nama_pendaftar ?? ''); }
    }

    // Winner
    $idPemenang = $battle_seni->id_penampilan_seni_pemenang ?? null;
    $isWinnerBiru = $idPemenang && (int)$idPemenang === (int)($penampilan_seni_biru->id_penampilan_seni ?? 0);
?>

<!-- Countdown -->
<div class="container-fluid min-vh-100 bg-black" id="countdown-container">
    <div class="countdown-screen">
        <p class="countdown-text">The Winner is..</p>
        <p class="countdown-number" id="countdown-number">5</p>
    </div>
</div>

<!-- Comparison -->
<div class="container-fluid bg-black comparison-screen px-4 py-3" id="comparison-container">
    <div class="row h-100 align-items-center">
        <!-- Blue Corner -->
        <div class="col-5">
            <div class="corner-card corner-blue">
                <p class="corner-name m-0"><?= implode(' &bull; ', $namaBiru) ?: 'Sudut Biru' ?></p>
                <p class="corner-kontingen m-0"><?= strtoupper(esc($penampilan_seni_biru->nama_kontingen ?? '')) ?></p>
                <p class="corner-score m-0"><?= number_format($nilaiAkhirBiru, 3) ?></p>
                <div class="corner-stats">
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= sprintf('%02d:%02d', floor($waktuBiru/60), $waktuBiru%60) ?></p>
                        <p class="stat-label m-0">Time</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= number_format((float)($catatanBiru->median ?? 0), 3) ?></p>
                        <p class="stat-label m-0">Median</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= number_format((float)($catatanBiru->hukuman ?? 0), 1) ?></p>
                        <p class="stat-label m-0">Penalty</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= number_format((float)($catatanBiru->standar_deviasi ?? 0), 3) ?></p>
                        <p class="stat-label m-0">Std Dev</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- VS -->
        <div class="col-2">
            <div class="vs-divider">VS</div>
        </div>

        <!-- Red Corner -->
        <div class="col-5">
            <div class="corner-card corner-red">
                <p class="corner-name m-0"><?= implode(' &bull; ', $namaMerah) ?: 'Sudut Merah' ?></p>
                <p class="corner-kontingen m-0"><?= strtoupper(esc($penampilan_seni_merah->nama_kontingen ?? '')) ?></p>
                <p class="corner-score m-0"><?= number_format($nilaiAkhirMerah, 3) ?></p>
                <div class="corner-stats">
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= sprintf('%02d:%02d', floor($waktuMerah/60), $waktuMerah%60) ?></p>
                        <p class="stat-label m-0">Time</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= number_format((float)($catatanMerah->median ?? 0), 3) ?></p>
                        <p class="stat-label m-0">Median</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= number_format((float)($catatanMerah->hukuman ?? 0), 1) ?></p>
                        <p class="stat-label m-0">Penalty</p>
                    </div>
                    <div class="stat-item">
                        <p class="stat-value m-0"><?= number_format((float)($catatanMerah->standar_deviasi ?? 0), 3) ?></p>
                        <p class="stat-label m-0">Std Dev</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Winner -->
<?php if ($idPemenang): ?>
<div class="container-fluid bg-black winner-screen px-4 py-3 <?= $isWinnerBiru ? 'winner-blue' : 'winner-red' ?>" id="winner-container">
    <p class="winner-badge">🏆 Congratulations!</p>
    <p class="winner-name m-0"><?= $isWinnerBiru ? implode(' &bull; ', $namaBiru) : implode(' &bull; ', $namaMerah) ?></p>
    <p class="winner-kontingen m-0"><?= strtoupper(esc($isWinnerBiru ? ($penampilan_seni_biru->nama_kontingen ?? '') : ($penampilan_seni_merah->nama_kontingen ?? ''))) ?></p>
    <p class="winner-score m-0"><?= number_format($isWinnerBiru ? $nilaiAkhirBiru : $nilaiAkhirMerah, 3) ?></p>
    <div class="winner-stats">
        <?php $catatanWinner = $isWinnerBiru ? $catatanBiru : $catatanMerah; $waktuWinner = $isWinnerBiru ? $waktuBiru : $waktuMerah; ?>
        <div class="stat-item">
            <p class="stat-value m-0"><?= sprintf('%02d:%02d', floor($waktuWinner/60), $waktuWinner%60) ?></p>
            <p class="stat-label m-0">Time</p>
        </div>
        <div class="stat-item">
            <p class="stat-value m-0"><?= number_format((float)($catatanWinner->median_kebenaran ?? 0), 3) ?></p>
            <p class="stat-label m-0">Median Keb.</p>
        </div>
        <div class="stat-item">
            <p class="stat-value m-0"><?= number_format((float)($catatanWinner->standar_deviasi ?? 0), 3) ?></p>
            <p class="stat-label m-0">Std Dev</p>
        </div>
    </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    var count = 5;
    var hasWinner = <?= $idPemenang ? 'true' : 'false' ?>;

    // Countdown
    var countdownInterval = setInterval(function () {
        count--;
        $('#countdown-number').text(count);
        if (count <= 0) {
            clearInterval(countdownInterval);
            $('#countdown-container').fadeOut(500, function () {
                // Show comparison
                $('#comparison-container').css('display', 'flex').hide().fadeIn(800);

                // After 7 seconds show winner (if exists)
                if (hasWinner) {
                    setTimeout(function () {
                        $('#comparison-container').fadeOut(500, function () {
                            $('#winner-container').css('display', 'flex').hide().fadeIn(800);
                        });
                    }, 7000);
                }
            });
        }
    }, 1000);

    // Poll for navigation
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
