<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-tanding.css') ?>">
<style>
    /* ═══ Modern Flexbox Layout for Viewport Height ═══ */
    .kp-monitor-wrapper {
        background: #0a0a0a;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        overflow: hidden;
    }

    /* Header: atlet cards + scores - Fixed height, no shrink */
    .kp-monitor-header-row {
        padding: 1.25rem;
        flex-shrink: 0;
        background: #0f0f0f;
    }
    .kp-monitor-atlet-card {
        padding: 1.25rem;
        border-radius: 10px;
        color: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        transition: transform 0.2s ease;
    }
    .kp-monitor-atlet-card:hover {
        transform: translateY(-2px);
    }
    .kp-monitor-atlet-card .atlet-nama {
        font-size: 1.15rem;
        font-weight: 700;
        line-height: 1.3;
        letter-spacing: 0.3px;
    }
    .kp-monitor-atlet-card .atlet-kontingen {
        font-size: 0.82rem;
        opacity: 0.9;
        font-weight: 400;
        margin-top: 0.25rem;
    }
    .kp-monitor-atlet-biru {
        background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
        border: 1px solid rgba(21, 101, 192, 0.3);
    }
    .kp-monitor-atlet-merah {
        background: linear-gradient(135deg, #c62828 0%, #b71c1c 100%);
        border: 1px solid rgba(198, 40, 40, 0.3);
    }
    .kp-monitor-score-box {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        border-radius: 10px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 2px 8px rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.05);
    }
    .kp-monitor-score-angka {
        font-family: 'Oswald', sans-serif;
        font-size: 2.5rem;
        font-weight: 700;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        letter-spacing: 1px;
    }
    .kp-monitor-info-box {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        border-radius: 10px;
        padding: 0.75rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #fff;
        box-shadow: inset 0 2px 8px rgba(0,0,0,0.4);
        border: 1px solid rgba(255,255,255,0.05);
    }
    .kp-monitor-info-box .info-label {
        font-size: 0.72rem;
        opacity: 0.7;
        text-transform: uppercase;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
    .kp-monitor-info-box .info-value {
        font-size: 1.05rem;
        font-weight: 700;
        font-family: 'Oswald', sans-serif;
        margin-top: 0.25rem;
    }

    /* Tab navigation - Fixed height, no shrink */
    .kp-monitor-tabs {
        border-bottom: 1px solid rgba(255,255,255,0.1);
        padding: 0 1.25rem;
        flex-shrink: 0;
        background: #0f0f0f;
    }
    .kp-monitor-tabs .nav-link {
        color: rgba(255,255,255,0.7);
        background: transparent;
        margin-right: 4px;
        border: none;
        border-radius: 8px 8px 0 0;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 0.65rem 1rem;
        transition: all 0.2s ease;
    }
    .kp-monitor-tabs .nav-link:hover {
        background: rgba(255,255,255,0.05);
        color: #fff;
    }
    .kp-monitor-tabs .nav-link.active {
        color: #fff;
        border-bottom: 3px solid var(--brand-primary, #c60000);
        font-weight: 700;
        background: rgba(255,255,255,0.08);
    }

    /* Tab Content - Flexible, scrollable */
    .kp-monitor-tab-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: auto;
        padding: 1.25rem;
        background: #0a0a0a;
    }

    /* Custom scrollbar for tab content */
    .kp-monitor-tab-content::-webkit-scrollbar {
        width: 8px;
    }
    .kp-monitor-tab-content::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    .kp-monitor-tab-content::-webkit-scrollbar-thumb {
        background: #444;
        border-radius: 4px;
    }
    .kp-monitor-tab-content::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Score tables */
    .kp-monitor-table {
        margin-bottom: 1.5rem;
    }
    .kp-monitor-table .table-dark-header {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        color: #fff;
        text-align: center;
        font-weight: 700;
        font-size: 1rem;
        padding: 0.75rem;
        letter-spacing: 0.5px;
    }
    .kp-monitor-table .table-blue-header {
        background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%);
        color: #fff;
        text-align: center;
        font-weight: 600;
    }
    .kp-monitor-table .table-red-header {
        background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%);
        color: #fff;
        text-align: center;
        font-weight: 600;
    }
    .kp-monitor-table .table-dark-cell {
        background: #1a1a1a;
        color: #fff;
        text-align: center;
        font-weight: 700;
    }
    .kp-monitor-table td,
    .kp-monitor-table th {
        vertical-align: middle;
        padding: 0.6rem 0.75rem;
    }
    .kp-monitor-table .val-cell {
        font-size: 0.9rem;
        font-weight: 600;
        background: #2a2a2a;
        color: #e0e0e0;
        max-width: 0;
        overflow-x: auto;
        white-space: nowrap;
    }
    .kp-monitor-table .val-cell::-webkit-scrollbar {
        height: 4px;
    }
    .kp-monitor-table .val-cell::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    .kp-monitor-table .val-cell::-webkit-scrollbar-thumb {
        background: #444;
        border-radius: 2px;
    }
    .kp-monitor-table .val-cell span {
        display: inline-block;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .kp-monitor-table .final-row td {
        font-size: 1.05rem;
        background: #1a1a1a;
        color: #fff;
        font-weight: 700;
        border-top: 2px solid rgba(255,215,0,0.3);
    }
    .kp-monitor-table table {
        background: #1e1e1e;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        table-layout: fixed;
        width: 100%;
    }
    .kp-monitor-table table td {
        border: 1px solid rgba(255,255,255,0.08);
        color: #e0e0e0;
    }
    .kp-monitor-table table th {
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
    }

    /* Force dark bg on ALL tables inside kp-monitor-wrapper */
    .kp-monitor-wrapper .table.bg-dark {
        --bs-table-bg: transparent;
        --bs-table-border-color: rgba(255,255,255,0.08);
        --bs-table-striped-bg: transparent;
    }
    .kp-monitor-wrapper .table.bg-dark td {
        background: #2a2a2a !important;
        color: #e0e0e0 !important;
        border: 1px solid rgba(255,255,255,0.08);
    }
    .kp-monitor-wrapper .table.bg-dark th {
        background: #1a1a1a !important;
        color: #fff !important;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .kp-monitor-wrapper .table.bg-dark .table-dark-header {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%) !important;
    }
    .kp-monitor-wrapper .table.bg-dark .kp-monitor-corner-biru {
        background: #1565c0 !important;
        color: #fff !important;
    }
    .kp-monitor-wrapper .table.bg-dark .kp-monitor-corner-merah {
        background: #c62828 !important;
        color: #fff !important;
    }
    .kp-monitor-wrapper .table.bg-dark .table-dark-cell {
        background: #1a1a1a !important;
        color: #fff !important;
    }

    /* Penalty / Striking tabs */
    .kp-monitor-corner-cell {
        font-weight: 700;
        text-align: center;
    }
    .kp-monitor-corner-biru {
        background: #1565c0;
        color: #fff;
    }
    .kp-monitor-corner-merah {
        background: #c62828;
        color: #fff;
    }

    .kp-locked {
        opacity: 0.45;
        pointer-events: none;
    }

    /* Helper color classes */
    .bg-red { background-color: #c62828 !important; }
    .bg-blue { background-color: #1565c0 !important; }

    /* Winner highlight */
    .kp-winner-highlight {
        box-shadow: 0 0 16px 4px rgba(255,215,0,0.6);
        border: 2px solid rgba(255,215,0,0.5);
        animation: pulseGold 2s ease-in-out infinite;
    }

    @keyframes pulseGold {
        0%, 100% { box-shadow: 0 0 16px 4px rgba(255,215,0,0.6); }
        50% { box-shadow: 0 0 24px 6px rgba(255,215,0,0.8); }
    }

    /* ═══ Responsive Design ═══ */

    /* Large screens (1400px+) */
    @media (min-width: 1400px) {
        .kp-monitor-score-angka {
            font-size: 3rem;
        }
        .kp-monitor-atlet-card .atlet-nama {
            font-size: 1.3rem;
        }
    }

    /* Medium screens (992px - 1399px) */
    @media (min-width: 992px) and (max-width: 1399.98px) {
        .kp-monitor-score-angka {
            font-size: 2.5rem;
        }
    }

    /* Tablet portrait & small desktop (768px - 991px) */
    @media (min-width: 768px) and (max-width: 991.98px) {
        .kp-monitor-header-row {
            padding: 1rem;
        }
        .kp-monitor-score-angka {
            font-size: 2rem;
        }
        .kp-monitor-atlet-card {
            padding: 1rem;
        }
        .kp-monitor-atlet-card .atlet-nama {
            font-size: 1rem;
        }
        .kp-monitor-tabs .nav-link {
            font-size: 0.78rem;
            padding: 0.55rem 0.8rem;
        }
    }

    /* Table horizontal scroll */
    .kp-monitor-table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .kp-monitor-table-scroll::-webkit-scrollbar {
        height: 5px;
    }
    .kp-monitor-table-scroll::-webkit-scrollbar-track {
        background: #1a1a1a;
    }
    .kp-monitor-table-scroll::-webkit-scrollbar-thumb {
        background: #444;
        border-radius: 3px;
    }
    .kp-monitor-table-scroll table {
        min-width: 0;
    }

    /* Mobile landscape & tablet portrait (576px - 767px) */
    @media (min-width: 576px) and (max-width: 767.98px) {
        .kp-monitor-header-row {
            padding: 0.75rem;
        }
        .kp-monitor-score-angka {
            font-size: 1.8rem;
        }
        .kp-monitor-atlet-card {
            padding: 0.85rem;
            margin-bottom: 0.5rem;
        }
        .kp-monitor-atlet-card .atlet-nama {
            font-size: 0.95rem;
        }
        .kp-monitor-atlet-card .atlet-kontingen {
            font-size: 0.75rem;
        }
        .kp-monitor-tabs {
            padding: 0 0.75rem;
        }
        .kp-monitor-tabs .nav-link {
            font-size: 0.75rem;
            padding: 0.5rem 0.7rem;
        }
        .kp-monitor-tab-content {
            padding: 1rem;
        }
    }

    /* Mobile portrait (< 576px) */
    @media (max-width: 575.98px) {
        .kp-monitor-header-row {
            padding: 0.75rem 0.75rem;
        }
        .kp-monitor-score-box {
            border-radius: 8px;
        }
        .kp-monitor-score-angka {
            font-size: 1.6rem;
        }
        .kp-monitor-info-box {
            padding: 0.5rem;
            border-radius: 8px;
        }
        .kp-monitor-info-box .info-label {
            font-size: 0.65rem;
        }
        .kp-monitor-info-box .info-value {
            font-size: 0.9rem;
        }
        .kp-monitor-atlet-card {
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .kp-monitor-atlet-card .atlet-nama {
            font-size: 0.9rem;
        }
        .kp-monitor-atlet-card .atlet-kontingen {
            font-size: 0.72rem;
        }
        .kp-monitor-tabs {
            padding: 0 0.5rem;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .kp-monitor-tabs .nav-item {
            flex-shrink: 0;
        }
        .kp-monitor-tabs .nav-link {
            font-size: 0.72rem;
            padding: 0.45rem 0.65rem;
            white-space: nowrap;
        }
        .kp-monitor-tab-content {
            padding: 0.75rem;
        }
        .kp-monitor-table td,
        .kp-monitor-table th {
            padding: 0.4rem 0.5rem;
            font-size: 0.85rem;
        }
        .kp-monitor-table .val-cell {
            font-size: 0.82rem;
        }
        .kp-monitor-table .final-row td {
            font-size: 0.95rem;
        }
    }

    /* Landscape orientation on small devices */
    @media (orientation: landscape) and (max-height: 600px) {
        .kp-monitor-header-row {
            padding: 0.5rem 1rem;
        }
        .kp-monitor-atlet-card {
            padding: 0.6rem;
        }
        .kp-monitor-score-angka {
            font-size: 1.8rem;
        }
        .kp-monitor-tabs .nav-link {
            padding: 0.4rem 0.7rem;
            font-size: 0.75rem;
        }
        .kp-monitor-tab-content {
            padding: 0.75rem;
        }
    }

    /* High DPI displays */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .kp-monitor-score-angka {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP        = (int) $pertandingan->id_pertandingan;
    $ronde      = (string) $pertandingan->ronde_pertandingan;
    $totalRonde = (int) ($pertandingan->total_ronde ?? 3);
    $namaBiru   = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $namaMerah  = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $kontBiru   = $atlet_biru->nama_kontingen ?? '-';
    $kontMerah  = $atlet_merah->nama_kontingen ?? '-';
    $dataJuri   = $data_nilai['juri'] ?? [];
    $jumlahJuri = count($dataJuri);
    $semua      = $ringkasan->semua_ronde ?? null;
?>
<div class="kp-monitor-wrapper" id="kp-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-total-ronde="<?= $totalRonde ?>"
     data-endpoint-edit="<?= base_url('ketua-pertandingan/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('ketua-pertandingan/refresh-status-pertandingan/' . $idP) ?>"
     data-endpoint-verifikasi-create="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/create/' . $idP) ?>"
     data-endpoint-verifikasi-update="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/update/' . $idP) ?>"
     data-endpoint-verifikasi-jawaban="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/get-jawaban/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-jumlah-juri="<?= $jumlahJuri ?>">

    <!-- ═══ Header Atlet + Skor ═══ -->
    <div class="kp-monitor-header-row">
        <div class="row g-2">
            <div class="col-lg-4 col-md-4 col-12">
                <div class="kp-monitor-atlet-card kp-monitor-atlet-biru h-100 d-flex flex-column justify-content-center">
                    <div class="atlet-nama text-truncate"><?= esc(ucwords($namaBiru)) ?></div>
                    <div class="atlet-kontingen text-truncate"><?= esc($kontBiru) ?></div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-12">
                <div class="row g-2 h-100">
                    <div class="col-3">
                        <div class="kp-monitor-score-box">
                            <span class="kp-monitor-score-angka" id="skor-biru"><?= (int) $pertandingan->skor_biru ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="kp-monitor-info-box">
                            <span class="info-label">Gelanggang / Partai</span>
                            <span class="info-value"><?= esc($pertandingan->nama_gelanggang ?? $pertandingan->nomor_gelanggang ?? '-') ?>-<?= esc($pertandingan->nomor_partai ?? '-') ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="kp-monitor-score-box">
                            <span class="kp-monitor-score-angka" id="skor-merah"><?= (int) $pertandingan->skor_merah ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-4 col-12">
                <div class="kp-monitor-atlet-card kp-monitor-atlet-merah h-100 d-flex flex-column justify-content-center text-end">
                    <div class="atlet-nama text-truncate"><?= esc(ucwords($namaMerah)) ?></div>
                    <div class="atlet-kontingen text-truncate"><?= esc($kontMerah) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Tab Navigation ═══ -->
    <ul class="nav nav-tabs kp-monitor-tabs flex-wrap justify-content-center" id="tabMonitor" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-allround" type="button">All Round</button></li>
        <?php for ($i = 1; $i <= $totalRonde; $i++): ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ronde<?= $i ?>" type="button">Round <?= $i ?></button></li>
        <?php endfor; ?>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-penalty" type="button">Penalty</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-striking" type="button">Striking</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-verifikasi" type="button">Verification</button></li>
    </ul>

    <!-- ═══ Tab Content (Scrollable Area) ═══ -->
    <div class="kp-monitor-tab-content">
        <div class="tab-content">

        <!-- ══════ All Round ══════ -->
        <div class="tab-pane fade show active" id="tab-allround">
            <div class="kp-monitor-table kp-monitor-table-scroll">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <colgroup>
                        <col style="width:7%">
                        <col style="width:35%">
                        <col style="width:16%">
                        <col style="width:35%">
                        <col style="width:7%">
                    </colgroup>
                    <thead>
                        <tr><th colspan="5" class="table-dark-header">All Round Score</th></tr>
                        <tr>
                            <th class="table-blue-header">Total</th>
                            <th class="table-blue-header">Score</th>
                            <th class="table-dark-header">Jury</th>
                            <th class="table-red-header">Score</th>
                            <th class="table-red-header">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-allround-body">
                        <?php $jurorNum = 1; foreach ($dataJuri as $idx => $j): ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center align-middle semua-ronde-biru-pukulan-tendangan" style="background:#0d47a1; color:#fff; font-size:0.85rem; padding:0 2px;">-</td>
                            <?php endif; ?>
                            <td class="val-cell juri-<?= $j->id_perangkat_pertandingan ?>-nilai-biru">0</td>
                            <td class="table-dark-cell"><?= $jurorNum++ ?></td>
                            <td class="val-cell juri-<?= $j->id_perangkat_pertandingan ?>-nilai-merah">0</td>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center align-middle semua-ronde-merah-pukulan-tendangan" style="background:#b71c1c; color:#fff; font-size:0.85rem; padding:0 2px;">-</td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td class="val-cell semua-ronde-biru-nilai-verified">-</td>
                            <td class="table-dark-cell">Valid Score</td>
                            <td class="val-cell semua-ronde-merah-nilai-verified">-</td>
                        </tr>
                        <tr>
                            <td class="text-center val-cell semua-ronde-biru-total-jatuhan">0</td>
                            <td class="val-cell semua-ronde-biru-rincian-nilai-jatuhan">-</td>
                            <td class="table-dark-cell">Dropping</td>
                            <td class="val-cell semua-ronde-merah-rincian-nilai-jatuhan">-</td>
                            <td class="text-center val-cell semua-ronde-merah-total-jatuhan">0</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="val-cell semua-ronde-biru-binaan">-</td>
                            <td class="table-dark-cell">Verbal Warning</td>
                            <td class="val-cell semua-ronde-merah-binaan">-</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center val-cell semua-ronde-biru-total-hukuman">0</td>
                            <td class="val-cell semua-ronde-biru-rincian-nilai-hukuman">-</td>
                            <td class="table-dark-cell">Penalty</td>
                            <td class="val-cell semua-ronde-merah-rincian-nilai-hukuman">-</td>
                            <td class="text-center val-cell semua-ronde-merah-total-hukuman">0</td>
                        </tr>
                        <tr class="final-row">
                            <td class="text-white text-center fw-bolder" id="total-biru-allround" style="font-size:1.2rem;">0</td>
                            <td colspan="3" class="table-dark-cell" style="font-size:1rem;">Final Score</td>
                            <td class="text-white text-center fw-bolder" id="total-merah-allround" style="font-size:1.2rem;">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══════ Per Ronde ══════ -->
        <?php for ($r = 1; $r <= $totalRonde; $r++): ?>
        <div class="tab-pane fade" id="tab-ronde<?= $r ?>">
            <div class="kp-monitor-table kp-monitor-table-scroll" id="tabel_ronde_<?= $r ?>">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <colgroup>
                        <col style="width:7%">
                        <col style="width:35%">
                        <col style="width:16%">
                        <col style="width:35%">
                        <col style="width:7%">
                    </colgroup>
                    <thead>
                        <tr><th colspan="5" class="table-dark-header">Round <?= $r ?></th></tr>
                        <tr>
                            <th class="table-blue-header">Total</th>
                            <th class="table-blue-header">Score</th>
                            <th class="table-dark-header">Jury</th>
                            <th class="table-red-header">Score</th>
                            <th class="table-red-header">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $jNum = 1; foreach ($dataJuri as $idx => $j): ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center align-middle ronde-<?= $r ?>-biru-pukulan-tendangan" style="background:#0d47a1; color:#fff; font-size:0.85rem; padding:0 2px;">-</td>
                            <?php endif; ?>
                            <td class="val-cell ronde-<?= $r ?>-juri-<?= $j->id_perangkat_pertandingan ?>-biru">0</td>
                            <td class="table-dark-cell"><?= $jNum++ ?></td>
                            <td class="val-cell ronde-<?= $r ?>-juri-<?= $j->id_perangkat_pertandingan ?>-merah">0</td>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center align-middle ronde-<?= $r ?>-merah-pukulan-tendangan" style="background:#b71c1c; color:#fff; font-size:0.85rem; padding:0 2px;">-</td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td class="val-cell ronde-<?= $r ?>-biru-nilai-verified">-</td>
                            <td class="table-dark-cell">Valid Score</td>
                            <td class="val-cell ronde-<?= $r ?>-merah-nilai-verified">-</td>
                        </tr>
                        <tr>
                            <td class="text-center val-cell ronde-<?= $r ?>-biru-total-jatuhan">0</td>
                            <td class="val-cell ronde-<?= $r ?>-biru-rincian-nilai-jatuhan">-</td>
                            <td class="table-dark-cell">Dropping</td>
                            <td class="val-cell ronde-<?= $r ?>-merah-rincian-nilai-jatuhan">-</td>
                            <td class="text-center val-cell ronde-<?= $r ?>-merah-total-jatuhan">0</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="val-cell ronde-<?= $r ?>-biru-binaan">-</td>
                            <td class="table-dark-cell">Verbal Warning</td>
                            <td class="val-cell ronde-<?= $r ?>-merah-binaan">-</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td class="text-center val-cell ronde-<?= $r ?>-biru-total-hukuman">0</td>
                            <td class="val-cell ronde-<?= $r ?>-biru-rincian-nilai-hukuman">-</td>
                            <td class="table-dark-cell">Penalty</td>
                            <td class="val-cell ronde-<?= $r ?>-merah-rincian-nilai-hukuman">-</td>
                            <td class="text-center val-cell ronde-<?= $r ?>-merah-total-hukuman">0</td>
                        </tr>
                        <tr class="final-row">
                            <td class="text-white text-center fw-bolder ronde-<?= $r ?>-biru-nilai-akhir">0</td>
                            <td colspan="3" class="table-dark-cell">Final Score</td>
                            <td class="text-white text-center fw-bolder ronde-<?= $r ?>-merah-nilai-akhir">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endfor; ?>

        <!-- ══════ Penalty Summary ══════ -->
        <div class="tab-pane fade" id="tab-penalty">
            <div class="kp-monitor-table-scroll mb-3">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <thead>
                        <tr><th colspan="<?= 1 + max(1, count((array)($semua->biru ?? []))) ?>" class="table-dark-header">Penalty Summary</th></tr>
                        <tr>
                            <th class="text-center text-white">Corner</th>
                            <?php if ($semua && isset($semua->biru)): ?>
                                <?php foreach ($semua->biru as $jenis => $nilai): ?>
                                    <?php if (!in_array($jenis, ['pukulan','tendangan','jatuhan','nilai_akhir'], true)): ?>
                                    <th class="text-center text-wrap"><?= esc(ucwords(str_replace('_', ' ', $jenis))) ?></th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="kp-monitor-corner-cell kp-monitor-corner-biru">Blue</td>
                            <?php if ($semua && isset($semua->biru)): ?>
                                <?php foreach ($semua->biru as $jenis => $nilai): ?>
                                    <?php if (!in_array($jenis, ['pukulan','tendangan','jatuhan','nilai_akhir'], true)): ?>
                                    <td class="text-center ringkasan-biru-<?= $jenis ?>"><?= (int) $nilai ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <td class="kp-monitor-corner-cell kp-monitor-corner-merah">Red</td>
                            <?php if ($semua && isset($semua->merah)): ?>
                                <?php foreach ($semua->merah as $jenis => $nilai): ?>
                                    <?php if (!in_array($jenis, ['pukulan','tendangan','jatuhan','nilai_akhir'], true)): ?>
                                    <td class="text-center ringkasan-merah-<?= $jenis ?>"><?= (int) $nilai ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══════ Striking Summary ══════ -->
        <div class="tab-pane fade" id="tab-striking">
            <div class="kp-monitor-table-scroll mb-3">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <thead>
                        <tr><th colspan="<?= 1 + max(1, count(array_intersect_key((array)($semua->biru ?? []), array_flip(['pukulan','tendangan','jatuhan'])))) ?>" class="table-dark-header">Striking Technique Summary</th></tr>
                        <tr>
                            <th class="text-center text-white">Corner</th>
                            <?php if ($semua && isset($semua->biru)): ?>
                                <?php foreach ($semua->biru as $jenis => $nilai): ?>
                                    <?php if (in_array($jenis, ['pukulan','tendangan','jatuhan'], true)): ?>
                                    <th class="text-white text-center"><?= esc(ucwords($jenis)) ?></th>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="kp-monitor-corner-cell kp-monitor-corner-biru">Blue</td>
                            <?php if ($semua && isset($semua->biru)): ?>
                                <?php foreach ($semua->biru as $jenis => $nilai): ?>
                                    <?php if (in_array($jenis, ['pukulan','tendangan','jatuhan'], true)): ?>
                                    <td class="text-center ringkasan-biru-<?= $jenis ?>"><?= (int) $nilai ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                        <tr>
                            <td class="kp-monitor-corner-cell kp-monitor-corner-merah">Red</td>
                            <?php if ($semua && isset($semua->merah)): ?>
                                <?php foreach ($semua->merah as $jenis => $nilai): ?>
                                    <?php if (in_array($jenis, ['pukulan','tendangan','jatuhan'], true)): ?>
                                    <td class="text-center ringkasan-merah-<?= $jenis ?>"><?= (int) $nilai ?></td>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ══════ Verification History ══════ -->
        <div class="tab-pane fade" id="tab-verifikasi">
            <div class="kp-monitor-table-scroll mb-3">
                <table class="table shadow-lg bg-dark table-borderless text-white" id="tabel_riwayat_verifikasi_pertandingan">
                    <thead>
                        <tr><th colspan="<?= 4 + $jumlahJuri ?>" class="table-dark-header">Verification History</th></tr>
                        <tr>
                            <th class="text-center text-white">Round</th>
                            <th class="text-center text-white">Time</th>
                            <th class="text-center text-white">Type</th>
                            <?php for ($j = 1; $j <= $jumlahJuri; $j++): ?>
                            <th class="text-center text-white">Jury <?= $j ?></th>
                            <?php endfor; ?>
                            <th class="text-center text-white">Result</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-verifikasi-body">
                        <tr><td colspan="<?= 4 + $jumlahJuri ?>" class="text-center text-muted py-3">Belum ada riwayat verifikasi</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        </div><!-- /.tab-content -->
    </div><!-- /.kp-monitor-tab-content -->
</div><!-- /.kp-monitor-wrapper -->

<!-- ═══ Modal Verifikasi Jatuhan ═══ -->
<div class="modal fade" id="modalVerifikasiJatuhan" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title w-100 text-center">Drop Verification</h5>
            </div>
            <div class="modal-body">
                <div class="row py-3" id="juri-cards-jatuhan">
                    <?php foreach ($dataJuri as $idx => $juri): ?>
                    <div class="col min-vh-25 d-flex justify-content-center align-items-center" id="card-jatuhan-juri-<?= $juri->id_perangkat_pertandingan ?>">
                        <div class="card bg-dark w-100 h-100 border-secondary">
                            <div class="card-header bg-black py-3 text-center text-white fw-bold">Jury <?= $idx + 1 ?></div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <p class="kp-juri-answer text-white text-center h3 my-5">Waiting Response</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <div class="row w-100"><div class="col-12"><p class="h6 text-center text-white mb-3">Select Your Answer:</p></div></div>
                <div class="row w-100 g-2">
                    <div class="col-md-4"><button type="button" class="btn btn-lg h4 text-white w-100" style="background:#1565c0;" data-jawaban="biru">Valid Drop (Biru)</button></div>
                    <div class="col-md-4"><button type="button" class="btn btn-lg h4 text-white w-100" style="background:#f59e0b;" data-jawaban="invalid">INVALID</button></div>
                    <div class="col-md-4"><button type="button" class="btn btn-lg h4 text-white w-100" style="background:#c62828;" data-jawaban="merah">Valid Drop (Merah)</button></div>
                </div>
                <div class="row w-100 mt-2"><div class="col-12"><button type="button" class="btn btn-lg btn-link text-white w-100" data-jawaban="batal">Cancel</button></div></div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Verifikasi Pelanggaran ═══ -->
<div class="modal fade" id="modalVerifikasiPelanggaran" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered modal-xl">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title w-100 text-center">Penalty Verification</h5>
            </div>
            <div class="modal-body">
                <div class="row py-3" id="juri-cards-pelanggaran">
                    <?php foreach ($dataJuri as $idx => $juri): ?>
                    <div class="col min-vh-25 d-flex justify-content-center align-items-center" id="card-pelanggaran-juri-<?= $juri->id_perangkat_pertandingan ?>">
                        <div class="card bg-dark w-100 h-100 border-secondary">
                            <div class="card-header bg-black py-3 text-center text-white fw-bold">Jury <?= $idx + 1 ?></div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <p class="kp-juri-answer text-white text-center h3 my-5">Waiting Response</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <div class="row w-100"><div class="col-12"><p class="h6 text-center text-white mb-3">Select Your Answer:</p></div></div>
                <div class="row w-100 g-2">
                    <div class="col-md-4"><button type="button" class="btn btn-lg h4 text-white w-100" style="background:#1565c0;" data-jawaban="biru">Valid Violation (Biru)</button></div>
                    <div class="col-md-4"><button type="button" class="btn btn-lg h4 text-white w-100" style="background:#f59e0b;" data-jawaban="invalid">INVALID</button></div>
                    <div class="col-md-4"><button type="button" class="btn btn-lg h4 text-white w-100" style="background:#c62828;" data-jawaban="merah">Valid Violation (Merah)</button></div>
                </div>
                <div class="row w-100 mt-2"><div class="col-12"><button type="button" class="btn btn-lg btn-link text-white w-100" data-jawaban="batal">Cancel</button></div></div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const KP_INIT = {
        dataNilai: <?= json_encode($data_nilai ?? new stdClass()) ?>,
        pertandingan: <?= json_encode($pertandingan) ?>,
        ringkasan: <?= json_encode($ringkasan ?? new stdClass()) ?>,
        verifikasiBerlangsung: <?= json_encode($verifikasi_berlangsung ?? null) ?>,
        riwayatVerifikasi: <?= json_encode($riwayat_verifikasi ?? []) ?>,
        jawabanRiwayatVerifikasi: <?= json_encode($jawaban_riwayat_verifikasi ?? new stdClass()) ?>,
    };
</script>
<script src="<?= base_url('assets/js/penilaian/kp_tanding.js') ?>"></script>
<?= $this->endSection() ?>