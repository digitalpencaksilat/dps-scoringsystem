<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-tanding.css') ?>">
<style>
/* ════════════════════════════════════════════════════════════════════════
   Juri Tanding PERSILAT — Dark Mode, 100dvh Compact Layout
   ════════════════════════════════════════════════════════════════════════ */
html, body { height: 100%; overflow: hidden; margin: 0; }

body {
	background: #000;
	font-family: 'Poppins', sans-serif;
}

#juri-wrapper {
	display: flex;
	flex-direction: column;
	height: 100dvh;
	background: #0a0a0a;
	overflow: hidden;
	padding: 0 !important;
}

/* ─── Tabel Skor Per Ronde ─────────────────────────────────────────────── */
#scoring-table-section {
	flex-shrink: 0;
	background: #0f1115;
	border-bottom: 1px solid rgba(255, 255, 255, 0.05);
	padding: clamp(0.4rem, 1.5vw, 0.6rem) clamp(0.5rem, 2vw, 0.75rem);
	overflow-x: auto;
	-webkit-overflow-scrolling: touch;
}

#scoring-table-section::-webkit-scrollbar {
	height: 4px;
}
#scoring-table-section::-webkit-scrollbar-track {
	background: #1a1a1a;
}
#scoring-table-section::-webkit-scrollbar-thumb {
	background: #444;
	border-radius: 2px;
}

/* ─── Header: Atlet + Skor ─────────────────────────────────────────────── */
#header-tanding {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: 1fr auto 1fr;
	gap: 0;
	margin: 0 !important;
	padding: 0 !important;
}

.header-atlet {
	padding: clamp(0.5rem, 2vw, 1rem) clamp(0.75rem, 2.5vw, 1.25rem);
	display: flex;
	flex-direction: column;
	justify-content: center;
	overflow: hidden;
	min-height: clamp(60px, 10vh, 90px);
	position: relative;
}

.header-atlet::before {
	content: '';
	position: absolute;
	left: 0;
	top: 0;
	bottom: 0;
	width: 4px;
	background: rgba(255, 255, 255, 0.3);
}

.header-atlet.biru {
	background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%);
}

.header-atlet.merah {
	background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%);
	text-align: right;
}

.header-atlet.merah::before {
	left: auto;
	right: 0;
}

.atlet-nama {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.85rem, 2.5vw, 1.25rem);
	font-weight: 700;
	color: #fff;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	line-height: 1.2;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.atlet-kontingen {
	font-size: clamp(0.65rem, 1.8vw, 0.85rem);
	color: rgba(255, 255, 255, 0.85);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	margin-top: 2px;
}

/* ─── Skor Center ──────────────────────────────────────────────────────── */
.header-center {
	display: grid;
	grid-template-columns: auto 1fr auto;
	background: linear-gradient(180deg, #1a1d24 0%, #0a0d11 100%);
	border-left: 2px solid rgba(255, 255, 255, 0.05);
	border-right: 2px solid rgba(255, 255, 255, 0.05);
	min-width: clamp(180px, 35vw, 280px);
}

.score-box {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 0 clamp(0.75rem, 2vw, 1.25rem);
	min-width: clamp(50px, 10vw, 80px);
}

.score-label {
	font-size: clamp(0.5rem, 1.4vw, 0.65rem);
	font-weight: 700;
	letter-spacing: 1.5px;
	text-transform: uppercase;
	margin-bottom: 2px;
}

.score-label.biru { color: #6691ff; }
.score-label.merah { color: #ff6b8a; }

.score-value {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.5rem, 5vw, 2.5rem);
	font-weight: 700;
	color: #fff;
	line-height: 1;
	font-variant-numeric: tabular-nums;
}

.babak-box {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	padding: 0 clamp(0.5rem, 1.5vw, 1rem);
	border-left: 1px solid rgba(255, 255, 255, 0.08);
	border-right: 1px solid rgba(255, 255, 255, 0.08);
	background: rgba(255, 255, 255, 0.02);
}

.babak-label {
	font-size: clamp(0.5rem, 1.4vw, 0.6rem);
	color: rgba(255, 255, 255, 0.4);
	text-transform: uppercase;
	letter-spacing: 1.5px;
}

.babak-value {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.85rem, 2.5vw, 1.1rem);
	font-weight: 700;
	color: #fff;
	white-space: nowrap;
	margin-top: 2px;
}

/* ─── Tabel Skor Per Ronde ─────────────────────────────────────────────── */
#scoring-table-section {
	flex-shrink: 0;
	background: #0f1115;
	border-bottom: 1px solid rgba(255, 255, 255, 0.05);
	padding: clamp(0.4rem, 1.5vw, 0.6rem) clamp(0.5rem, 2vw, 0.75rem);
}

#tabel-nilai-juri {
	width: 100%;
	margin: 0;
	background: transparent;
	border-collapse: separate;
	border-spacing: 0;
}

#tabel-nilai-juri thead th {
	background: #1a1d24;
	color: rgba(255, 255, 255, 0.6);
	font-size: clamp(0.55rem, 1.5vw, 0.7rem);
	font-weight: 700;
	letter-spacing: 1.5px;
	text-transform: uppercase;
	padding: clamp(0.3rem, 1vw, 0.5rem);
	border: 1px solid rgba(255, 255, 255, 0.05);
	text-align: center;
	vertical-align: middle;
}

#tabel-nilai-juri thead th.bg-gradient-180-blue {
	background: linear-gradient(180deg, #1d2af4 55%, #0118d8 75%) !important;
	color: #fff;
}

#tabel-nilai-juri thead th.bg-gradient-180-red {
	background: linear-gradient(180deg, #dd0a35 55%, #b80c0c 75%) !important;
	color: #fff;
}

#tabel-nilai-juri tbody td {
	background: #14171c;
	color: #e0e0e0;
	font-size: clamp(0.7rem, 2vw, 0.95rem);
	padding: clamp(0.25rem, 1vw, 0.4rem);
	border: 1px solid rgba(255, 255, 255, 0.05);
	text-align: center;
	vertical-align: middle;
	font-family: 'Oswald', sans-serif;
	font-weight: 600;
	font-variant-numeric: tabular-nums;
}

#tabel-nilai-juri tbody td .biru-ronde-1-total,
#tabel-nilai-juri tbody td[class*="biru-ronde"][class*="-total"] {
	color: #6691ff;
	font-weight: 700;
}

#tabel-nilai-juri tbody td[class*="merah-ronde"][class*="-total"] {
	color: #ff6b8a;
	font-weight: 700;
}

#tabel-nilai-juri tbody td[class*="ronde-"]:not([class*="-nilai"]):not([class*="-total"]) {
	background: linear-gradient(180deg, #23303e 0%, #0a0d11 100%);
	color: #fff;
	font-weight: 700;
	cursor: pointer;
	transition: background 0.15s ease;
}

#tabel-nilai-juri tbody td[class*="ronde-"]:not([class*="-nilai"]):not([class*="-total"]):hover {
	background: linear-gradient(180deg, #2d3a48 0%, #14171b 100%);
}

#tabel-nilai-juri tbody td[class*="ronde-"]:not([class*="-nilai"]):not([class*="-total"]).bg-warning {
	background: #ffc107 !important;
	color: #000 !important;
}

#tabel-nilai-juri td div[class*="-nilai"] {
	gap: 3px;
	font-size: clamp(0.65rem, 1.8vw, 0.85rem);
	scrollbar-width: thin;
	scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
}

#tabel-nilai-juri td div[class*="-nilai"]::-webkit-scrollbar {
	height: 3px;
}

#tabel-nilai-juri td div[class*="-nilai"]::-webkit-scrollbar-thumb {
	background: rgba(255, 255, 255, 0.15);
	border-radius: 2px;
}

#tabel-nilai-juri td div[class*="-nilai"] span {
	white-space: nowrap;
	flex-shrink: 0;
	padding: 2px 6px;
	background: rgba(255, 255, 255, 0.05);
	border-radius: 3px;
	font-weight: 600;
}

/* ─── Scoring Buttons Section ──────────────────────────────────────────── */
#scoring-buttons-section {
	flex: 1 1 0;
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 0;
	overflow: hidden;
	min-height: 0;
}

.scoring-column {
	display: flex;
	flex-direction: column;
	gap: clamp(2px, 0.5vw, 4px);
	padding: clamp(2px, 0.5vw, 4px);
	overflow: hidden;
}

.scoring-column.biru {
	background: linear-gradient(180deg, rgba(29, 42, 244, 0.05) 0%, rgba(1, 24, 216, 0.02) 100%);
}

.scoring-column.merah {
	background: linear-gradient(180deg, rgba(221, 10, 53, 0.05) 0%, rgba(184, 12, 12, 0.02) 100%);
}

.btn-scoring-legacy {
	flex: 1;
	min-height: 0 !important;
	display: flex;
	align-items: center;
	justify-content: center;
	border: none;
	border-radius: clamp(6px, 1.5vw, 12px);
	transition: transform 0.06s ease, filter 0.15s ease, box-shadow 0.15s ease;
	-webkit-tap-highlight-color: transparent;
	user-select: none;
	cursor: pointer;
	color: #fff;
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1px;
	position: relative;
	overflow: hidden;
	margin: 0 !important;
}

.btn-scoring-legacy.bg-blue {
	background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%) !important;
	box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 4px 12px rgba(1, 24, 216, 0.3);
}

.btn-scoring-legacy.bg-red {
	background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%) !important;
	box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.15), 0 4px 12px rgba(184, 12, 12, 0.3);
}

.btn-scoring-legacy:active {
	transform: scale(0.97);
	filter: brightness(0.85);
}

.btn-scoring-legacy:disabled {
	opacity: 0.35;
	pointer-events: none;
	filter: grayscale(0.5);
}

.btn-scoring-legacy img {
	pointer-events: none;
	max-height: clamp(40px, 12vh, 90px) !important;
	width: auto;
	transition: transform 0.2s ease;
	filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.btn-scoring-legacy:hover img {
	transform: scale(1.05);
}

/* ─── Modal Verifikasi ─────────────────────────────────────────────────── */
.modal-content {
	background: #14171c;
	color: #fff;
	border: 1px solid rgba(255, 255, 255, 0.08);
	border-radius: clamp(8px, 2vw, 16px);
	overflow: hidden;
}

.modal-header {
	background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
	color: #000;
	border-bottom: none;
	padding: clamp(0.75rem, 2vw, 1.25rem);
}

.modal-title {
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1px;
}

.modal-body {
	padding: clamp(1rem, 3vw, 1.5rem);
}

.modal-body p.h3, .modal-body p.h4 {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1rem, 3vw, 1.5rem);
	font-weight: 600;
	color: rgba(255, 255, 255, 0.85);
}

.btn-jawaban-verifikasi {
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1.5px;
	border: none;
	border-radius: clamp(8px, 2vw, 12px);
	padding: clamp(2rem, 6vw, 3.5rem) 1rem !important;
	transition: transform 0.06s ease, filter 0.15s ease;
	font-size: clamp(0.95rem, 3vw, 1.5rem) !important;
}

.btn-jawaban-verifikasi:active {
	transform: scale(0.97);
	filter: brightness(0.9);
}

.btn-jawaban-verifikasi.bg-blue {
	background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%) !important;
}

.btn-jawaban-verifikasi.bg-red {
	background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%) !important;
}

.btn-jawaban-verifikasi.bg-warning {
	background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
	color: #000 !important;
}

/* ─── Animation ────────────────────────────────────────────────────────── */
.opacity {
	animation: fadeIn 0.4s ease-out forwards;
	opacity: 0;
}

@keyframes fadeIn {
	from { opacity: 0; transform: translateY(-8px); }
	to { opacity: 1; transform: translateY(0); }
}

.opacity.fast { animation-duration: 0.25s; }

/* ─── Responsive: Mobile Portrait ──────────────────────────────────────── */
@media (max-width: 575.98px) {
	.header-atlet {
		padding: 0.5rem 0.75rem;
		min-height: 50px;
	}
	.atlet-nama { font-size: 0.8rem; }
	.atlet-kontingen { font-size: 0.65rem; }

	.score-box { padding: 0 0.5rem; min-width: 50px; }
	.score-value { font-size: 1.5rem; }
	.babak-value { font-size: 0.85rem; }

	#tabel-nilai-juri thead th { padding: 0.25rem; font-size: 0.55rem; }
	#tabel-nilai-juri tbody td { padding: 0.2rem; font-size: 0.7rem; }

	.scoring-column { gap: 2px; padding: 2px; }
}

/* ─── Responsive: Tablet Landscape (scoring device sweet spot) ─────────── */
@media (min-width: 768px) and (max-width: 1366px) {
	.btn-scoring-legacy img {
		max-height: clamp(50px, 15vh, 110px) !important;
	}
}

/* ─── Landscape Fullscreen (low-height scoring device) ─────────────────── */
@media (orientation: landscape) and (max-height: 600px) {
	.header-atlet {
		padding: 0.4rem 0.75rem;
		min-height: 48px;
	}
	.atlet-nama { font-size: 0.85rem; }
	.atlet-kontingen { font-size: 0.65rem; }

	.score-value { font-size: 1.6rem; }
	.babak-value { font-size: 0.85rem; }

	#scoring-table-section { padding: 0.3rem 0.5rem; }
	#tabel-nilai-juri thead th { padding: 0.2rem; font-size: 0.55rem; }
	#tabel-nilai-juri tbody td { padding: 0.2rem; font-size: 0.75rem; }

	.btn-scoring-legacy img {
		max-height: clamp(40px, 22vh, 110px) !important;
	}
}

/* ─── Accessibility ────────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
	.btn-scoring-legacy,
	.btn-jawaban-verifikasi,
	.opacity {
		animation: none !important;
		transition: none !important;
	}
}

.btn-scoring-legacy:focus-visible,
.btn-jawaban-verifikasi:focus-visible {
	outline: 3px solid rgba(255, 255, 255, 0.6);
	outline-offset: 2px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $totalRonde = (int) ($pertandingan->total_ronde ?? 3);
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';
    $skorMerah = (int) ($pertandingan->skor_merah ?? 0);
    $skorBiru  = (int) ($pertandingan->skor_biru ?? 0);
    $babak     = $pertandingan->babak ?? '';
?>

<div id="juri-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-endpoint-edit="<?= base_url('juri/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('juri/refresh-status-pertandingan/' . $idP) ?>"
     data-endpoint-verifikasi="<?= base_url('juri/submit-jawaban-verifikasi/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <!-- ═══ Header: Atlet + Skor ═══ -->
    <div id="header-tanding" class="opacity fast">
        <!-- Atlet Biru (kiri) -->
        <div class="header-atlet biru">
            <div class="atlet-nama"><?= esc($namaBiru) ?></div>
            <div class="atlet-kontingen"><?= esc($kontBiru) ?></div>
        </div>

        <!-- Skor Center -->
        <div class="header-center">
            <div class="score-box">
                <span class="score-label biru">Biru</span>
                <span id="total_nilai_akhir_biru" class="score-value"><?= $skorBiru ?></span>
            </div>
            <div class="babak-box">
                <span class="babak-label">Babak</span>
                <span class="babak-value"><?= esc($babak ?: '—') ?></span>
            </div>
            <div class="score-box">
                <span class="score-label merah">Merah</span>
                <span id="total_nilai_akhir_merah" class="score-value"><?= $skorMerah ?></span>
            </div>
        </div>

        <!-- Atlet Merah (kanan) -->
        <div class="header-atlet merah">
            <div class="atlet-nama"><?= esc($namaMerah) ?></div>
            <div class="atlet-kontingen"><?= esc($kontMerah) ?></div>
        </div>
    </div>

    <!-- ═══ Tabel Nilai Per Ronde ═══ -->
    <div id="scoring-table-section">
        <table class="table table-borderless mb-0" id="tabel-nilai-juri">
            <thead>
                <tr class="opacity">
                    <th colspan="5" style="font-size: clamp(0.6rem, 1.6vw, 0.75rem);">
                        <i class="fas fa-user-shield me-1"></i>
                        <?= esc(session()->get('nama') ?? 'Juri') ?>
                    </th>
                </tr>
                <tr class="opacity">
                    <th class="bg-gradient-180-blue" style="width:10%;">Total</th>
                    <th class="bg-gradient-180-blue" style="width:30%;">Nilai Biru</th>
                    <th style="width:10%;">Ronde</th>
                    <th class="bg-gradient-180-red" style="width:30%;">Nilai Merah</th>
                    <th class="bg-gradient-180-red" style="width:10%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($r = 1; $r <= $totalRonde; $r++):
                    $romanNumerals = ['I', 'II', 'III', 'IV', 'V'];
                    $romanLabel = $romanNumerals[$r - 1] ?? $r;
                ?>
                <tr class="opacity">
                    <td class="biru-ronde-<?= $r ?>-total">&nbsp;</td>
                    <td style="max-width: 0;">
                        <div class="biru-ronde-<?= $r ?>-nilai d-flex flex-row flex-nowrap overflow-auto"></div>
                    </td>
                    <td class="ronde-<?= $r ?>" onclick="juri.warning_pindah_babak()"><?= $romanLabel ?></td>
                    <td style="max-width: 0;">
                        <div class="merah-ronde-<?= $r ?>-nilai d-flex flex-row flex-nowrap overflow-auto"></div>
                    </td>
                    <td class="merah-ronde-<?= $r ?>-total">&nbsp;</td>
                </tr>
                <?php endfor; ?>
            </tbody>
        </table>
    </div>

    <!-- ═══ Scoring Buttons (2 columns: Biru | Merah) ═══ -->
    <div id="scoring-buttons-section" style="margin-bottom: clamp(4px, 1vh, 12px);">
        <!-- Sudut Biru (Kiri) -->
        <div class="scoring-column biru" id="button-biru">
            <button class="btn-scoring-legacy bg-blue opacity" data-sudut="biru" data-nilai="1">
                <img src="<?= base_url('assets/images/icons/pukulan.png') ?>" alt="Pukulan +1">
            </button>
            <button class="btn-scoring-legacy bg-blue opacity" data-sudut="biru" data-nilai="2">
                <img src="<?= base_url('assets/images/icons/tendangan-inverted.png') ?>" alt="Tendangan +2">
            </button>
        </div>

        <!-- Sudut Merah (Kanan) -->
        <div class="scoring-column merah" id="button-merah">
            <button class="btn-scoring-legacy bg-red opacity" data-sudut="merah" data-nilai="1">
                <img src="<?= base_url('assets/images/icons/pukulan-inverted.png') ?>" alt="Pukulan +1">
            </button>
            <button class="btn-scoring-legacy bg-red opacity" data-sudut="merah" data-nilai="2">
                <img src="<?= base_url('assets/images/icons/tendangan.png') ?>" alt="Tendangan +2">
            </button>
        </div>
    </div>
</div>

<!-- ═══ Modal Verifikasi Jatuhan ═══ -->
<div class="modal fade" id="modalVerifikasiJatuhan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-gavel me-2"></i>Drop Verification</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="h3 mb-4 text-center">Valid Drop For ?</p>
                    </div>
                    <div class="col">
                        <button class="btn bg-blue text-white w-100 btn-jawaban-verifikasi" data-jawaban="biru">
                            Blue
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-warning text-white w-100 btn-jawaban-verifikasi" data-jawaban="invalid">
                            INVALID
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-red text-white w-100 btn-jawaban-verifikasi" data-jawaban="merah">
                            Red
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Verifikasi Pelanggaran ═══ -->
<div class="modal fade" id="modalVerifikasiPelanggaran" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-triangle-exclamation me-2"></i>Penalty Verification</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <p class="h4 mb-4 text-center">Violation For ?</p>
                    </div>
                    <div class="col">
                        <button class="btn bg-blue text-white w-100 btn-jawaban-verifikasi" data-jawaban="biru">
                            Blue
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-warning text-white w-100 btn-jawaban-verifikasi" data-jawaban="invalid">
                            INVALID
                        </button>
                    </div>
                    <div class="col">
                        <button class="btn bg-red text-white w-100 btn-jawaban-verifikasi" data-jawaban="merah">
                            Red
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const JURI_INIT = {
        dataNilai: <?= json_encode($data_nilai) ?>,
        pertandingan: <?= json_encode($pertandingan) ?>,
        pemenang: <?= json_encode($pemenang ?? null) ?>,
        verifikasiPertandingan: <?= json_encode($verifikasi_pertandingan ?? null) ?>,
        jawabanVerifikasi: <?= json_encode($jawaban_verifikasi ?? null) ?>,
        totalRonde: <?= $totalRonde ?>
    };
</script>
<script src="<?= base_url('assets/js/penilaian/juri_tanding_persilat.js') ?>"></script>
<?= $this->endSection() ?>
