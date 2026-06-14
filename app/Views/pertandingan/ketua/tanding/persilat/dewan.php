<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-tanding.css') ?>">
<style>
    .kp-dewan-wrapper { background: #000; min-height: 100vh; }
    .kp-dewan-header-row { padding: 1rem 1rem 0.5rem; }
    .kp-dewan-atlet-card { padding: 1rem; border-radius: 8px; color: #fff; }
    .kp-dewan-atlet-card .atlet-nama { font-size: 1.1rem; font-weight: 700; line-height: 1.3; }
    .kp-dewan-atlet-card .atlet-kontingen { font-size: 0.8rem; opacity: 0.85; }
    .kp-dewan-atlet-biru { background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%); }
    .kp-dewan-atlet-merah { background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%); }
    .kp-dewan-score-box {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        border-radius: 8px; height: 100%; display: flex; align-items: center; justify-content: center;
    }
    .kp-dewan-score-angka { font-family: 'Oswald', sans-serif; font-size: 2.2rem; font-weight: 700; color: #fff; }
    .kp-dewan-info-box {
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
        border-radius: 8px; padding: 0.5rem; display: flex; flex-direction: column;
        align-items: center; justify-content: center; height: 100%; color: #fff;
    }
    .kp-dewan-info-box .info-label { font-size: 0.7rem; opacity: 0.7; text-transform: uppercase; }
    .kp-dewan-info-box .info-value { font-size: 1rem; font-weight: 700; font-family: 'Oswald', sans-serif; }
    .kp-dewan-button-area { padding: 0.75rem; }
    .kp-dewan-button-card { background: #1a1a1a; border-radius: 12px; padding: 1rem; border: 1px solid #333; }
    .kp-dewan-btn-jatuhan {
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        width: 100%; padding: 0.8rem; border: none; border-radius: 8px; color: #fff;
        font-size: 0.95rem; font-weight: 600; transition: all 0.15s; cursor: pointer;
    }
    .kp-dewan-btn-jatuhan:active { transform: scale(0.96); }
    .kp-dewan-btn-jatuhan-biru { background: linear-gradient(135deg, #1565c0, #0d47a1); }
    .kp-dewan-btn-jatuhan-biru:active { background: #0b3d80; }
    .kp-dewan-btn-jatuhan-merah { background: linear-gradient(135deg, #c62828, #b71c1c); }
    .kp-dewan-btn-jatuhan-merah:active { background: #8b0000; }
    .kp-dewan-btn-delete {
        width: 100%; padding: 0.8rem; border: 1px solid #555; border-radius: 8px;
        background: transparent; color: #aaa; font-size: 0.85rem; cursor: pointer; transition: all 0.15s;
    }
    .kp-dewan-btn-delete:hover { background: rgba(255,255,255,0.05); color: #fff; border-color: #888; }
    .kp-dewan-btn-delete:active { transform: scale(0.96); }
    .kp-dewan-btn-hukuman {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 0.35rem; width: 100%; padding: 0.8rem 0.4rem; border: none; border-radius: 8px;
        color: #fff; font-size: 0.7rem; font-weight: 600; text-align: center;
        transition: all 0.15s; cursor: pointer; min-height: 130px;
    }
    .kp-dewan-btn-hukuman img { max-height: 60px; width: auto; filter: brightness(0) invert(1); }
    .kp-dewan-btn-hukuman:active { transform: scale(0.94); }
    .kp-dewan-btn-hukuman-biru { background: rgba(21, 101, 192, 0.35); }
    .kp-dewan-btn-hukuman-biru:active { background: rgba(21, 101, 192, 0.6); }
    .kp-dewan-btn-hukuman-merah { background: rgba(198, 40, 40, 0.35); }
    .kp-dewan-btn-hukuman-merah:active { background: rgba(198, 40, 40, 0.6); }
    .kp-dewan-verif-btn {
        width: 100%; padding: 1.2rem 0.5rem; border: 2px solid rgba(255,255,255,0.2);
        border-radius: 8px; background: rgba(255,255,255,0.05); color: #fff;
        font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: all 0.15s;
        display: flex; flex-direction: column; align-items: center; gap: 0.4rem;
    }
    .kp-dewan-verif-btn:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.4); }
    .kp-dewan-verif-btn:active { transform: scale(0.96); }
    .kp-dewan-verif-btn i { font-size: 1.2rem; }
    .kp-dewan-back-link { color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem; transition: color 0.15s; }
    .kp-dewan-back-link:hover { color: #fff; }
    .kp-dewan-topbar {
        background: #111; padding: 0.6rem 1rem; display: flex; align-items: center;
        justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;
    }
    .kp-dewan-switch-btn {
        border: 1px solid rgba(255,255,255,0.25); border-radius: 6px;
        background: transparent; color: rgba(255,255,255,0.7); padding: 0.3rem 0.75rem;
        font-size: 0.8rem; cursor: pointer; text-decoration: none; transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.35rem;
    }
    .kp-dewan-switch-btn:hover { background: rgba(255,255,255,0.08); color: #fff; }
    /* ─── Button States ─── */
    /* Locked: prerequisite not met OR cannot delete (higher level exists) */
    .kp-locked { opacity: 0.3; pointer-events: none; filter: grayscale(0.6); }
    /* Active: hukuman sudah diinput pada ronde/partai ini */
    .kp-active { border: 2px solid rgba(255,255,255,0.7) !important; box-shadow: inset 0 0 0 2px rgba(255,255,255,0.15); }
    .kp-active.kp-dewan-btn-hukuman-biru { background: rgba(21, 101, 192, 0.7) !important; }
    .kp-active.kp-dewan-btn-hukuman-merah { background: rgba(198, 40, 40, 0.7) !important; }
    /* Delete mode: button is now "hapus" — visual cue */
    .kp-delete-mode { position: relative; }
    .kp-delete-mode::after {
        content: '✕'; position: absolute; top: 4px; right: 8px;
        font-size: 0.7rem; color: rgba(255,255,255,0.8);
        background: rgba(220,53,69,0.8); border-radius: 50%;
        width: 16px; height: 16px; display: flex; align-items: center; justify-content: center;
        line-height: 1;
    }
    /* Loading state */
    .is-loading { opacity: 0.6; pointer-events: none; }
    /* Pulse feedback */
    .pulse-ok { animation: pulseOk 0.4s ease; }
    .pulse-fail { animation: pulseFail 0.4s ease; }
    @keyframes pulseOk { 0% { box-shadow: 0 0 0 0 rgba(40,167,69,0.6); } 100% { box-shadow: 0 0 0 15px rgba(40,167,69,0); } }
    @keyframes pulseFail { 0% { box-shadow: 0 0 0 0 rgba(220,53,69,0.6); } 100% { box-shadow: 0 0 0 15px rgba(220,53,69,0); } }
    /* Winner highlight on score box */
    .kp-winner-highlight { box-shadow: 0 0 12px 3px rgba(255,215,0,0.6); border: 1px solid rgba(255,215,0,0.5); }
    /* Helper color classes (for verifikasi modal) */
    .bg-red { background-color: #c62828 !important; }
    .bg-blue { background-color: #1565c0 !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'tanding']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP        = (int) $pertandingan->id_pertandingan;
    $ronde      = (string) $pertandingan->ronde_pertandingan;
    $namaBiru   = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $namaMerah  = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $kontBiru   = $atlet_biru->nama_kontingen ?? '-';
    $kontMerah  = $atlet_merah->nama_kontingen ?? '-';
    $dataJuri   = $data_nilai['juri'] ?? [];
    $jumlahJuri = count($dataJuri);
?>
<div class="kp-dewan-wrapper" id="kp-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-total-ronde="<?= (int) ($pertandingan->total_ronde ?? 3) ?>"
     data-endpoint-edit="<?= base_url('ketua-pertandingan/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('ketua-pertandingan/refresh-status-pertandingan/' . $idP) ?>"
     data-endpoint-verifikasi-create="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/create/' . $idP) ?>"
     data-endpoint-verifikasi-update="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/update/' . $idP) ?>"
     data-endpoint-verifikasi-jawaban="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/get-jawaban/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-jumlah-juri="<?= $jumlahJuri ?>"
     data-developer-passcode="<?= esc(config('Scoring')->developerPasscode, 'attr') ?>">

    <!-- ═══ Top Bar ═══ -->
    <div class="kp-dewan-topbar">
        <div class="d-flex align-items-center gap-3">
            <a href="<?= base_url('ketua-pertandingan') ?>" class="kp-dewan-back-link">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <span style="color:#fff;font-family:'Oswald',sans-serif;font-size:1rem;">DEWAN PERTANDINGAN</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="kp-timer penilaian-display-font" id="kp-timer" style="color:#fff; font-size:1.1rem;">--:--</span>
            <span class="kp-ronde penilaian-display-font" style="color:rgba(255,255,255,0.6);font-size:0.85rem;">Ronde <?= esc($ronde) ?></span>
            <a href="<?= base_url('ketua-pertandingan/tanding/monitoring/dark') ?>" class="kp-dewan-switch-btn">
                <i class="fas fa-chart-line"></i> Monitor
            </a>
            <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="kp-dewan-switch-btn" title="Keluar">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <!-- ═══ Header: Atlet + Skor ═══ -->
    <div class="kp-dewan-header-row">
        <div class="row g-2">
            <!-- Blue Athlete -->
            <div class="col-lg-4 col-md-4 col-12">
                <div class="kp-dewan-atlet-card kp-dewan-atlet-biru h-100 d-flex flex-column justify-content-center">
                    <div class="atlet-nama text-truncate"><?= esc(ucwords($namaBiru)) ?></div>
                    <div class="atlet-kontingen text-truncate"><?= esc($kontBiru) ?></div>
                </div>
            </div>
            <!-- Score + Info Center -->
            <div class="col-lg-4 col-md-4 col-12">
                <div class="row g-2 h-100">
                    <div class="col-3">
                        <div class="kp-dewan-score-box">
                            <span class="kp-dewan-score-angka" id="skor-biru"><?= (int) $pertandingan->skor_biru ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="kp-dewan-info-box">
                            <span class="info-label">Gelanggang / Partai</span>
                            <span class="info-value"><?= esc($pertandingan->nama_gelanggang ?? $pertandingan->nomor_gelanggang ?? '-') ?>-<?= esc($pertandingan->nomor_partai ?? '-') ?></span>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="kp-dewan-score-box">
                            <span class="kp-dewan-score-angka" id="skor-merah"><?= (int) $pertandingan->skor_merah ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Red Athlete -->
            <div class="col-lg-4 col-md-4 col-12">
                <div class="kp-dewan-atlet-card kp-dewan-atlet-merah h-100 d-flex flex-column justify-content-center text-end">
                    <div class="atlet-nama text-truncate"><?= esc(ucwords($namaMerah)) ?></div>
                    <div class="atlet-kontingen text-truncate"><?= esc($kontMerah) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Button Controller Area ═══ -->
    <div class="kp-dewan-button-area">
        <div class="kp-dewan-button-card">
            <div class="row g-2">
                <!-- ═══ Blue Side: 5 columns ═══ -->
                <div class="col-lg-5 col-md-5 col-12">
                    <!-- Jatuhan Row -->
                    <div class="row mb-2">
                        <div class="col-8 pe-1">
                            <button type="button" class="kp-big-btn kp-dewan-btn-jatuhan kp-dewan-btn-jatuhan-biru"
                                    data-sudut="biru" data-mode="jatuhan" data-jumlah="3">
                                <i class="fas fa-person-falling"></i> Dropping <small style="opacity:0.8">(Jatuhan)</small>
                            </button>
                        </div>
                        <div class="col-4 ps-1">
                            <button type="button" class="kp-big-btn kp-dewan-btn-delete"
                                    data-sudut="biru" data-mode="jatuhan" data-jumlah="hapus">
                                <i class="fas fa-trash-can me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                    <!-- Hukuman Grid: 3x2 -->
                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-biru"
                                    data-sudut="biru" data-mode="binaan_1" data-jumlah="1">
                                <img src="<?= base_url('assets/images/icon/binaan-1.png') ?>" alt="Binaan 1">
                                <span>Verbal Warning I</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-biru"
                                    data-sudut="biru" data-mode="binaan_2" data-jumlah="2">
                                <img src="<?= base_url('assets/images/icon/binaan-2.png') ?>" alt="Binaan 2">
                                <span>Verbal Warning II</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-biru"
                                    data-sudut="biru" data-mode="teguran_1" data-jumlah="-1">
                                <img src="<?= base_url('assets/images/icon/teguran-1.png') ?>" alt="Teguran 1">
                                <span>Reprimand Warning I</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-biru"
                                    data-sudut="biru" data-mode="teguran_2" data-jumlah="-2">
                                <img src="<?= base_url('assets/images/icon/teguran-2.png') ?>" alt="Teguran 2">
                                <span>Reprimand Warning II</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-biru"
                                    data-sudut="biru" data-mode="peringatan_1" data-jumlah="-5">
                                <img src="<?= base_url('assets/images/icon/peringatan-1.png') ?>" alt="Peringatan 1">
                                <span>Warning I</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-biru"
                                    data-sudut="biru" data-mode="peringatan_2" data-jumlah="-10">
                                <img src="<?= base_url('assets/images/icon/peringatan-2.png') ?>" alt="Peringatan 2">
                                <span>Warning II</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ═══ Center: Verification Buttons ═══ -->
                <div class="col-lg-2 col-md-2 col-12">
                    <div class="d-flex flex-lg-column flex-md-column flex-row gap-2 h-100 justify-content-center">
                        <button type="button" class="kp-dewan-verif-btn" id="btn-verifikasi-jatuhan" style="flex:1;">
                            <i class="fas fa-person-falling"></i>
                            <span>Drop<br>Verification</span>
                        </button>
                        <button type="button" class="kp-dewan-verif-btn" id="btn-verifikasi-pelanggaran" style="flex:1;">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span>Penalty<br>Verification</span>
                        </button>
                    </div>
                </div>

                <!-- ═══ Red Side: 5 columns (mirror of blue) ═══ -->
                <div class="col-lg-5 col-md-5 col-12">
                    <!-- Jatuhan Row -->
                    <div class="row mb-2">
                        <div class="col-8 pe-1">
                            <button type="button" class="kp-big-btn kp-dewan-btn-jatuhan kp-dewan-btn-jatuhan-merah"
                                    data-sudut="merah" data-mode="jatuhan" data-jumlah="3">
                                <i class="fas fa-person-falling"></i> Dropping <small style="opacity:0.8">(Jatuhan)</small>
                            </button>
                        </div>
                        <div class="col-4 ps-1">
                            <button type="button" class="kp-big-btn kp-dewan-btn-delete"
                                    data-sudut="merah" data-mode="jatuhan" data-jumlah="hapus">
                                <i class="fas fa-trash-can me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                    <!-- Hukuman Grid: 3x2 -->
                    <div class="row g-2">
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-merah"
                                    data-sudut="merah" data-mode="binaan_1" data-jumlah="1">
                                <img src="<?= base_url('assets/images/icon/binaan-1.png') ?>" alt="Binaan 1">
                                <span>Verbal Warning I</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-merah"
                                    data-sudut="merah" data-mode="binaan_2" data-jumlah="2">
                                <img src="<?= base_url('assets/images/icon/binaan-2.png') ?>" alt="Binaan 2">
                                <span>Verbal Warning II</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-merah"
                                    data-sudut="merah" data-mode="teguran_1" data-jumlah="-1">
                                <img src="<?= base_url('assets/images/icon/teguran-1.png') ?>" alt="Teguran 1">
                                <span>Reprimand Warning I</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-merah"
                                    data-sudut="merah" data-mode="teguran_2" data-jumlah="-2">
                                <img src="<?= base_url('assets/images/icon/teguran-2.png') ?>" alt="Teguran 2">
                                <span>Reprimand Warning II</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-merah"
                                    data-sudut="merah" data-mode="peringatan_1" data-jumlah="-5">
                                <img src="<?= base_url('assets/images/icon/peringatan-1.png') ?>" alt="Peringatan 1">
                                <span>Warning I</span>
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="kp-icon-btn kp-dewan-btn-hukuman kp-dewan-btn-hukuman-merah"
                                    data-sudut="merah" data-mode="peringatan_2" data-jumlah="-10">
                                <img src="<?= base_url('assets/images/icon/peringatan-2.png') ?>" alt="Peringatan 2">
                                <span>Warning II</span>
                            </button>
                        </div>
                    </div>
                </div>
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
                <div class="row w-100">
                    <div class="col-12"><p class="h6 text-center text-white mb-3">Select Your Answer:</p></div>
                </div>
                <div class="row w-100 g-2">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-lg h4 text-white w-100" style="background:#1565c0;" data-jawaban="biru">Valid Drop (Biru)</button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-lg h4 text-white w-100" style="background:#f59e0b;" data-jawaban="invalid">INVALID</button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-lg h4 text-white w-100" style="background:#c62828;" data-jawaban="merah">Valid Drop (Merah)</button>
                    </div>
                </div>
                <div class="row w-100 mt-2">
                    <div class="col-12">
                        <button type="button" class="btn btn-lg btn-link text-white w-100" data-jawaban="batal">Cancel</button>
                    </div>
                </div>
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
                <div class="row w-100">
                    <div class="col-12"><p class="h6 text-center text-white mb-3">Select Your Answer:</p></div>
                </div>
                <div class="row w-100 g-2">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-lg h4 text-white w-100" style="background:#1565c0;" data-jawaban="biru">Valid Violation (Biru)</button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-lg h4 text-white w-100" style="background:#f59e0b;" data-jawaban="invalid">INVALID</button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-lg h4 text-white w-100" style="background:#c62828;" data-jawaban="merah">Valid Violation (Merah)</button>
                    </div>
                </div>
                <div class="row w-100 mt-2">
                    <div class="col-12">
                        <button type="button" class="btn btn-lg btn-link text-white w-100" data-jawaban="batal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Developer Option ═══ -->
<?= view('pertandingan/ketua/components/developer_option') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const KP_INIT = {
        dataNilai: <?= json_encode($data_nilai ?? new stdClass()) ?>,
        pertandingan: <?= json_encode($pertandingan) ?>,
        ringkasan: <?= json_encode($ringkasan ?? new stdClass()) ?>,
        verifikasiBerlangsung: <?= json_encode($verifikasi_berlangsung ?? null) ?>,
        riwayatVerifikasi: <?= json_encode($riwayat_verifikasi ?? []) ?>,
    };
</script>
<script src="<?= base_url('assets/js/penilaian/kp_tanding.js') ?>"></script>
<?= $this->endSection() ?>
