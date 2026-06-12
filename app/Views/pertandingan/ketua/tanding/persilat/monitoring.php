<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-tanding.css') ?>">
<style>
    .kp-monitor-wrapper { background: #000; min-height: 100vh; }
    .kp-monitor-topbar {
        background: #111; padding: 0.6rem 1rem; display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
    }
    .kp-monitor-back-link { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem; transition: color 0.15s; }
    .kp-monitor-back-link:hover { color: #fff; }
    .kp-monitor-switch-btn {
        border: 1px solid rgba(255,255,255,0.25); border-radius: 6px;
        background: transparent; color: rgba(255,255,255,0.7); padding: 0.3rem 0.75rem;
        font-size: 0.8rem; cursor: pointer; text-decoration: none; transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.35rem;
    }
    .kp-monitor-switch-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }

    /* Header: atlet cards + scores */
    .kp-monitor-header-row { padding: 1rem 1rem 0.5rem; }
    .kp-monitor-atlet-card { padding: 1rem; border-radius: 8px; color: #fff; }
    .kp-monitor-atlet-card .atlet-nama { font-size: 1.1rem; font-weight: 700; line-height: 1.3; }
    .kp-monitor-atlet-card .atlet-kontingen { font-size: 0.8rem; opacity: 0.85; }
    .kp-monitor-atlet-biru { background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%); }
    .kp-monitor-atlet-merah { background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%); }
    .kp-monitor-score-box {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        border-radius: 8px; height: 100%; display: flex; align-items: center; justify-content: center;
    }
    .kp-monitor-score-angka { font-family: 'Oswald', sans-serif; font-size: 2.2rem; font-weight: 700; color: #fff; }
    .kp-monitor-info-box {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column;
        align-items: center; justify-content: center; height: 100%; color: #fff;
    }
    .kp-monitor-info-box .info-label { font-size: 0.7rem; opacity: 0.7; text-transform: uppercase; }
    .kp-monitor-info-box .info-value { font-size: 1rem; font-weight: 700; font-family: 'Oswald', sans-serif; }

    /* Tab navigation */
    .kp-monitor-tabs { border-bottom: 1px solid rgba(255,255,255,0.1); padding: 0 1rem; }
    .kp-monitor-tabs .nav-link { color: #fff; background: #1a1a1a; margin-right: 4px; border: none; border-radius: 0.25rem 0.25rem 0 0; font-weight: 600; font-size: 0.82rem; }
    .kp-monitor-tabs .nav-link:hover { background: rgba(255,255,255,0.08); }
    .kp-monitor-tabs .nav-link.active { color: #fff; border-bottom: 3px solid #fff; font-weight: 700; background: #2c2c2c; }

    /* Score tables */
    .kp-monitor-table { margin-bottom: 1.5rem; }
    .kp-monitor-table .table-dark-header {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        color: #fff; text-align: center; font-weight: 700; font-size: 1rem; padding: 0.75rem;
    }
    .kp-monitor-table .table-blue-header { background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%); color: #fff; text-align: center; }
    .kp-monitor-table .table-red-header { background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%); color: #fff; text-align: center; }
    .kp-monitor-table .table-dark-cell { background: #1a1a1a; color: #fff; text-align: center; font-weight: 700; }
    .kp-monitor-table td, .kp-monitor-table th { vertical-align: middle; padding: 0.5rem 0.75rem; }
    .kp-monitor-table .val-cell { font-size: 0.9rem; font-weight: 600; background: #2a2a2a; color: #e0e0e0; }
    .kp-monitor-table .final-row td { font-size: 1rem; background: #1a1a1a; color: #fff; font-weight: 700; }
    .kp-monitor-table table { background: #1e1e1e; border-collapse: collapse; }
    .kp-monitor-table table td { border: 1px solid rgba(255,255,255,0.08); color: #e0e0e0; }
    .kp-monitor-table table th { border: 1px solid rgba(255,255,255,0.1); color: #fff; }

    /* Force dark bg on ALL tables inside kp-monitor-wrapper (Penalty, Striking, Verification) */
    .kp-monitor-wrapper .table.bg-dark {
        --bs-table-bg: transparent;
        --bs-table-border-color: rgba(255,255,255,0.08);
        --bs-table-striped-bg: transparent;
    }
    .kp-monitor-wrapper .table.bg-dark td {
        background: #2a2a2a !important; color: #e0e0e0 !important;
        border: 1px solid rgba(255,255,255,0.08);
    }
    .kp-monitor-wrapper .table.bg-dark th {
        background: #1a1a1a !important; color: #fff !important;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .kp-monitor-wrapper .table.bg-dark .table-dark-header {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%) !important;
    }
    .kp-monitor-wrapper .table.bg-dark .kp-monitor-corner-biru { background: #1565c0 !important; color: #fff !important; }
    .kp-monitor-wrapper .table.bg-dark .kp-monitor-corner-merah { background: #c62828 !important; color: #fff !important; }
    .kp-monitor-wrapper .table.bg-dark .table-dark-cell { background: #1a1a1a !important; color: #fff !important; }

    /* Penalty / Striking tabs */
    .kp-monitor-corner-cell { font-weight: 700; text-align: center; }
    .kp-monitor-corner-biru { background: #1565c0; color: #fff; }
    .kp-monitor-corner-merah { background: #c62828; color: #fff; }

    .kp-locked { opacity: 0.45; pointer-events: none; }
    /* Helper color classes */
    .bg-red { background-color: #c62828 !important; }
    .bg-blue { background-color: #1565c0 !important; }
    /* Winner highlight */
    .kp-winner-highlight { box-shadow: 0 0 12px 3px rgba(255,215,0,0.6); border: 1px solid rgba(255,215,0,0.5); }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'tanding']) ?>
<?= $this->endSection() ?>

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

    <!-- ═══ Top Bar ═══ -->
    <div class="kp-monitor-topbar">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= base_url('ketua-pertandingan') ?>" class="kp-monitor-back-link">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <span style="color:#fff;font-family:'Oswald',sans-serif;font-size:1rem;">MONITOR NILAI</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="kp-timer penilaian-display-font" id="kp-timer" style="color:#fff;font-size:1.1rem;">--:--</span>
            <span class="kp-ronde penilaian-display-font" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Ronde <?= esc($ronde) ?></span>
            <a href="<?= base_url('ketua-pertandingan/tanding/dewan/dark') ?>" class="kp-monitor-switch-btn">
                <i class="fas fa-gavel"></i> Dewan
            </a>
            <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="kp-monitor-switch-btn" title="Keluar">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

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

    <!-- ═══ Tab Content ═══ -->
    <div class="tab-content px-3 pt-3">

        <!-- ══════ All Round ══════ -->
        <div class="tab-pane fade show active" id="tab-allround">
            <div class="kp-monitor-table">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <thead>
                        <tr><th colspan="5" class="table-dark-header">All Round Score</th></tr>
                        <tr>
                            <th class="table-blue-header" style="width:5%;">Total</th>
                            <th class="table-blue-header" style="width:38%;">Score</th>
                            <th class="table-dark-header" style="width:14%;">Jury</th>
                            <th class="table-red-header" style="width:38%;">Score</th>
                            <th class="table-red-header" style="width:5%;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-allround-body">
                        <?php $jurorNum = 1; foreach ($dataJuri as $idx => $j): ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center fw-bolder align-middle semua-ronde-biru-pukulan-tendangan" style="background:#0d47a1; color:#fff; font-size:1.2rem;">-</td>
                            <?php endif; ?>
                            <td class="val-cell juri-<?= $j->id_perangkat_pertandingan ?>-nilai-biru">0</td>
                            <td class="table-dark-cell"><?= $jurorNum++ ?></td>
                            <td class="val-cell juri-<?= $j->id_perangkat_pertandingan ?>-nilai-merah">0</td>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center fw-bolder align-middle semua-ronde-merah-pukulan-tendangan" style="background:#b71c1c; color:#fff; font-size:1.2rem;">-</td>
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
            <div class="kp-monitor-table" id="tabel_ronde_<?= $r ?>">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <thead>
                        <tr><th colspan="5" class="table-dark-header">Round <?= $r ?></th></tr>
                        <tr>
                            <th class="table-blue-header" style="width:5%;">Total</th>
                            <th class="table-blue-header" style="width:38%;">Score</th>
                            <th class="table-dark-header" style="width:14%;">Jury</th>
                            <th class="table-red-header" style="width:38%;">Score</th>
                            <th class="table-red-header" style="width:5%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $jNum = 1; foreach ($dataJuri as $idx => $j): ?>
                        <tr>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center fw-bolder align-middle ronde-<?= $r ?>-biru-pukulan-tendangan" style="background:#0d47a1; color:#fff; font-size:1.2rem;">-</td>
                            <?php endif; ?>
                            <td class="val-cell ronde-<?= $r ?>-juri-<?= $j->id_perangkat_pertandingan ?>-biru">0</td>
                            <td class="table-dark-cell"><?= $jNum++ ?></td>
                            <td class="val-cell ronde-<?= $r ?>-juri-<?= $j->id_perangkat_pertandingan ?>-merah">0</td>
                            <?php if ($idx === 0): ?>
                            <td rowspan="<?= $jumlahJuri + 1 ?>" class="text-center fw-bolder align-middle ronde-<?= $r ?>-merah-pukulan-tendangan" style="background:#b71c1c; color:#fff; font-size:1.2rem;">-</td>
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
            <div class="table-responsive mb-3">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <thead>
                        <tr><th colspan="20" class="table-dark-header">Penalty Summary</th></tr>
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
            <div class="table-responsive mb-3">
                <table class="table shadow-lg bg-dark table-borderless text-white">
                    <thead>
                        <tr><th colspan="20" class="table-dark-header">Striking Technique Summary</th></tr>
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
            <div class="table-responsive mb-3">
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
    </div>
</div>

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