<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-tanding.css') ?>">
<style>
/* ════════════════════════════════════════════════════════════════════════
   Dewan Tanding — 100dvh Compact, Dark Theme
   ════════════════════════════════════════════════════════════════════════ */
html, body { height: 100%; overflow: hidden; margin: 0; }

body {
	background: #000;
	font-family: 'Poppins', sans-serif;
	color: #fff;
}

#kp-wrapper {
	display: flex;
	flex-direction: column;
	height: 100dvh;
	overflow: hidden;
}

/* ─── Header: Atlet + Skor ─────────────────────────────────────────── */
#dewan-header {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: 1fr auto 1fr;
	gap: clamp(0.4rem, 1vw, 0.75rem);
	align-items: stretch;
	padding: clamp(0.4rem, 1.5vw, 0.75rem);
	background: #0a0a0a;
	border-bottom: 1px solid rgba(255,255,255,0.06);
}

.dewan-atlet-card {
	display: flex;
	flex-direction: column;
	justify-content: center;
	padding: clamp(0.5rem, 1.5vw, 1rem);
	border-radius: 8px;
	color: #fff;
	gap: 0.15rem;
	min-width: 0;
}

.dewan-atlet-biru {
	background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%);
	text-align: left;
}

.dewan-atlet-merah {
	background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%);
	text-align: right;
}

.dewan-atlet-nama {
	font-size: clamp(0.9rem, 2.5vw, 1.15rem);
	font-weight: 700;
	line-height: 1.2;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.dewan-atlet-kontingen {
	font-size: clamp(0.7rem, 1.5vw, 0.82rem);
	opacity: 0.85;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

/* Center: Score + Info */
#dewan-center {
	display: grid;
	grid-template-columns: 1fr auto 1fr;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
	align-items: stretch;
	min-width: 0;
}

.dewan-score-box {
	display: flex;
	align-items: center;
	justify-content: center;
	background: linear-gradient(180deg, #1a1a1a 0%, #111 100%);
	border-radius: 8px;
	padding: 0 clamp(0.3rem, 1vw, 0.75rem);
	min-width: clamp(2.5rem, 8vw, 4rem);
}

.dewan-score-angka {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.4rem, 4vw, 2.4rem);
	font-weight: 700;
}

#skor-biru { color: #64b5f6; }
#skor-merah { color: #ef5350; }

.dewan-info-box {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	background: linear-gradient(180deg, #1a1a1a 0%, #111 100%);
	border-radius: 8px;
	padding: clamp(0.3rem, 1vw, 0.5rem);
	gap: 0.1rem;
}

.dewan-info-label {
	font-size: clamp(0.6rem, 1.2vw, 0.72rem);
	opacity: 0.6;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	white-space: nowrap;
}

.dewan-info-value {
	font-size: clamp(0.8rem, 1.8vw, 1rem);
	font-weight: 700;
	font-family: 'Oswald', sans-serif;
}

#dewan-ronde {
	color: rgba(255,255,255,0.9);
}

/* ─── Body: Button Area ────────────────────────────────────────────── */
#dewan-body {
	flex: 1;
	display: flex;
	overflow: hidden;
	padding: clamp(0.3rem, 1vw, 0.75rem);
	gap: clamp(0.3rem, 1vw, 0.75rem);
}

/* Left / Right panels for Biru & Merah */
.dewan-panel {
	flex: 5;
	display: flex;
	flex-direction: column;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
	min-width: 0;
	overflow-y: auto;
}

.dewan-panel-center {
	flex: 2;
	display: flex;
	flex-direction: column;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
	min-width: 0;
}

/* Jatuhan row */
.dewan-jatuhan-row {
	display: flex;
	gap: clamp(0.25rem, 0.6vw, 0.4rem);
	flex-shrink: 0;
}

.dewan-btn-jatuhan {
	flex: 8;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.5rem;
	padding: clamp(0.6rem, 1.5vw, 0.9rem);
	border: none;
	border-radius: 8px;
	color: #fff;
	font-size: clamp(0.8rem, 1.8vw, 0.95rem);
	font-weight: 700;
	cursor: pointer;
	transition: all 0.12s;
}

.dewan-btn-jatuhan:active { transform: scale(0.96); }

.dewan-btn-jatuhan-biru { background: linear-gradient(135deg, #1565c0, #0d47a1); }
.dewan-btn-jatuhan-biru:active { background: #0b3d80; }

.dewan-btn-jatuhan-merah { background: linear-gradient(135deg, #c62828, #b71c1c); }
.dewan-btn-jatuhan-merah:active { background: #8b0000; }

.dewan-btn-delete {
	flex: 4;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.3rem;
	padding: clamp(0.6rem, 1.5vw, 0.9rem);
	border: 1px solid #444;
	border-radius: 8px;
	background: transparent;
	color: #999;
	font-size: clamp(0.7rem, 1.5vw, 0.85rem);
	cursor: pointer;
	transition: all 0.12s;
	white-space: nowrap;
}

.dewan-btn-delete:hover { background: rgba(255,255,255,0.05); color: #fff; border-color: #777; }
.dewan-btn-delete:active { transform: scale(0.96); }

/* Penalty Grid — 2×3, column-major order */
/* col1: Verbal Warning I/II  |  col2: Reprimand I/II  |  col3: Warning I/II */
.dewan-penalty-grid {
	flex: 1;
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	grid-template-rows: 1fr 1fr;
	grid-auto-flow: column;
	gap: clamp(0.25rem, 0.6vw, 0.4rem);
}

.dewan-btn-hukuman {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: clamp(0.2rem, 0.6vw, 0.4rem);
	width: 100%;
	height: 100%;
	border: none;
	border-radius: 8px;
	color: #fff;
	font-size: clamp(0.6rem, 1.3vw, 0.75rem);
	font-weight: 600;
	text-align: center;
	cursor: pointer;
	transition: all 0.12s;
	padding: clamp(0.3rem, 1vw, 0.6rem) clamp(0.2rem, 0.5vw, 0.4rem);
	line-height: 1.2;
}

.dewan-btn-hukuman img {
	max-height: clamp(2.5rem, 8vw, 4rem);
	width: auto;
	max-width: 85%;
	object-fit: contain;
}

.dewan-btn-hukuman:active { transform: scale(0.94); }

.dewan-btn-hukuman-biru { background: rgba(21,101,192,0.3); }
.dewan-btn-hukuman-biru:active { background: rgba(21,101,192,0.6); }

.dewan-btn-hukuman-merah { background: rgba(198,40,40,0.3); }
.dewan-btn-hukuman-merah:active { background: rgba(198,40,40,0.6); }

/* Center verification buttons */
.dewan-verif-col {
	flex: 1;
	display: flex;
	flex-direction: column;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
	justify-content: center;
}

.dewan-verif-btn {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	gap: clamp(0.25rem, 0.5vw, 0.5rem);
	width: 100%;
	border: 2px solid rgba(255,255,255,0.15);
	border-radius: 8px;
	background: rgba(255,255,255,0.04);
	color: #fff;
	font-size: clamp(0.6rem, 1.3vw, 0.8rem);
	font-weight: 600;
	cursor: pointer;
	transition: all 0.15s;
	padding: 0.5rem;
	text-align: center;
}

.dewan-verif-btn:hover {
	background: rgba(255,255,255,0.08);
	border-color: rgba(255,255,255,0.3);
}

.dewan-verif-btn:active { transform: scale(0.96); }

.dewan-verif-btn i { font-size: clamp(1rem, 2.2vw, 1.4rem); }

/* ─── Button States ────────────────────────────────────────────────── */
.kp-locked { opacity: 0.3; pointer-events: none; filter: grayscale(0.6); }

.kp-active { border: 2px solid rgba(255,255,255,0.7) !important; box-shadow: inset 0 0 0 2px rgba(255,255,255,0.15); }
.kp-active.dewan-btn-hukuman-biru { background: rgba(21,101,192,0.7) !important; }
.kp-active.dewan-btn-hukuman-merah { background: rgba(198,40,40,0.7) !important; }

.kp-delete-mode { position: relative; }
.kp-delete-mode::after {
	content: '✕'; position: absolute; top: 4px; right: 6px;
	font-size: 0.65rem; color: rgba(255,255,255,0.9);
	background: rgba(220,53,69,0.85); border-radius: 50%;
	width: 14px; height: 14px; display: flex;
	align-items: center; justify-content: center; line-height: 1;
}

.is-loading { opacity: 0.6; pointer-events: none; }

.pulse-ok { animation: pulseOk 0.4s ease; }
.pulse-fail { animation: pulseFail 0.4s ease; }
@keyframes pulseOk { 0% { box-shadow: 0 0 0 0 rgba(40,167,69,0.6); } 100% { box-shadow: 0 0 0 12px rgba(40,167,69,0); } }
@keyframes pulseFail { 0% { box-shadow: 0 0 0 0 rgba(220,53,69,0.6); } 100% { box-shadow: 0 0 0 12px rgba(220,53,69,0); } }

.kp-winner-highlight { box-shadow: 0 0 12px 3px rgba(255,215,0,0.6); border: 1px solid rgba(255,215,0,0.5); }

.bg-red { background-color: #c62828 !important; }
.bg-blue { background-color: #1565c0 !important; }

/* ─── Scrollbar ────────────────────────────────────────────────────── */
.dewan-panel::-webkit-scrollbar { width: 4px; }
.dewan-panel::-webkit-scrollbar-track { background: transparent; }
.dewan-panel::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }

/* ─── Responsive ───────────────────────────────────────────────────── */
@media (max-width: 768px) {
	#dewan-header { grid-template-columns: 1fr 1fr 1fr; }
	#dewan-body { flex-direction: column; }
	.dewan-panel { overflow-y: visible; }
	.dewan-penalty-grid { min-height: 180px; }
}

@media (orientation: landscape) and (max-height: 500px) {
	.dewan-btn-hukuman img { max-height: 2rem; }
	.dewan-btn-hukuman { font-size: 0.6rem; gap: 0.15rem; }
	.dewan-penalty-grid { gap: 0.2rem; }
}

@media (prefers-reduced-motion: reduce) {
	.dewan-btn-jatuhan, .dewan-btn-hukuman, .dewan-btn-delete, .dewan-verif-btn {
		transition: none !important;
	}
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

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
<div id="kp-wrapper"
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

	<!-- Header: Atlet + Skor -->
	<div id="dewan-header">
		<div class="dewan-atlet-card dewan-atlet-biru">
			<div class="dewan-atlet-nama"><?= esc(ucwords($namaBiru)) ?></div>
			<div class="dewan-atlet-kontingen"><?= esc($kontBiru) ?></div>
		</div>

		<div id="dewan-center">
			<div class="dewan-score-box">
				<span class="dewan-score-angka" id="skor-biru"><?= (int) $pertandingan->skor_biru ?></span>
			</div>
			<div class="dewan-info-box">
				<span class="dewan-info-label">Ronde</span>
				<span class="dewan-info-value kp-ronde" id="dewan-ronde"><?= esc($ronde) ?></span>
			</div>
			<div class="dewan-score-box">
				<span class="dewan-score-angka" id="skor-merah"><?= (int) $pertandingan->skor_merah ?></span>
			</div>
		</div>

		<div class="dewan-atlet-card dewan-atlet-merah">
			<div class="dewan-atlet-nama"><?= esc(ucwords($namaMerah)) ?></div>
			<div class="dewan-atlet-kontingen"><?= esc($kontMerah) ?></div>
		</div>
	</div>

	<!-- Hidden timer — used by JS for display via #kp-timer -->
	<span id="kp-timer" style="display:none;">--:--</span>

	<!-- Body: Button Area -->
	<div id="dewan-body">
		<!-- Blue Panel -->
		<div class="dewan-panel">
			<div class="dewan-jatuhan-row">
				<button type="button" class="kp-big-btn dewan-btn-jatuhan dewan-btn-jatuhan-biru"
					data-sudut="biru" data-mode="jatuhan" data-jumlah="3">
					<i class="fas fa-person-falling"></i> Dropping <small style="opacity:0.75">(Jatuhan)</small>
				</button>
				<button type="button" class="kp-big-btn dewan-btn-delete"
					data-sudut="biru" data-mode="jatuhan" data-jumlah="hapus">
					<i class="fas fa-trash-can"></i> Del
				</button>
			</div>

			<div class="dewan-penalty-grid">
				<!-- Column 1: Verbal Warning I & II -->
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-biru"
					data-sudut="biru" data-mode="binaan_1" data-jumlah="1">
					<img src="<?= base_url('assets/images/icon/binaan-1.png') ?>" alt="Binaan 1">
					<span>Verbal Warning I</span>
				</button>
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-biru"
					data-sudut="biru" data-mode="binaan_2" data-jumlah="2">
					<img src="<?= base_url('assets/images/icon/binaan-2.png') ?>" alt="Binaan 2">
					<span>Verbal Warning II</span>
				</button>

				<!-- Column 2: Reprimand I & II -->
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-biru"
					data-sudut="biru" data-mode="teguran_1" data-jumlah="-1">
					<img src="<?= base_url('assets/images/icon/teguran-1.png') ?>" alt="Teguran 1">
					<span>Reprimand I</span>
				</button>
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-biru"
					data-sudut="biru" data-mode="teguran_2" data-jumlah="-2">
					<img src="<?= base_url('assets/images/icon/teguran-2.png') ?>" alt="Teguran 2">
					<span>Reprimand II</span>
				</button>

				<!-- Column 3: Warning I & II -->
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-biru"
					data-sudut="biru" data-mode="peringatan_1" data-jumlah="-5">
					<img src="<?= base_url('assets/images/icon/peringatan-1.png') ?>" alt="Peringatan 1">
					<span>Warning I</span>
				</button>
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-biru"
					data-sudut="biru" data-mode="peringatan_2" data-jumlah="-10">
					<img src="<?= base_url('assets/images/icon/peringatan-2.png') ?>" alt="Peringatan 2">
					<span>Warning II</span>
				</button>
			</div>
		</div>

		<!-- Center: Verifikasi -->
		<div class="dewan-panel-center">
			<div class="dewan-verif-col">
				<button type="button" class="dewan-verif-btn" id="btn-verifikasi-jatuhan">
					<i class="fas fa-person-falling"></i>
					<span>Drop<br>Verification</span>
				</button>
				<button type="button" class="dewan-verif-btn" id="btn-verifikasi-pelanggaran">
					<i class="fas fa-triangle-exclamation"></i>
					<span>Penalty<br>Verification</span>
				</button>
			</div>
		</div>

		<!-- Red Panel -->
		<div class="dewan-panel">
			<div class="dewan-jatuhan-row">
				<button type="button" class="kp-big-btn dewan-btn-jatuhan dewan-btn-jatuhan-merah"
					data-sudut="merah" data-mode="jatuhan" data-jumlah="3">
					<i class="fas fa-person-falling"></i> Dropping <small style="opacity:0.75">(Jatuhan)</small>
				</button>
				<button type="button" class="kp-big-btn dewan-btn-delete"
					data-sudut="merah" data-mode="jatuhan" data-jumlah="hapus">
					<i class="fas fa-trash-can"></i> Del
				</button>
			</div>

			<div class="dewan-penalty-grid">
				<!-- Column 1: Verbal Warning I & II -->
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-merah"
					data-sudut="merah" data-mode="binaan_1" data-jumlah="1">
					<img src="<?= base_url('assets/images/icon/binaan-1.png') ?>" alt="Binaan 1">
					<span>Verbal Warning I</span>
				</button>
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-merah"
					data-sudut="merah" data-mode="binaan_2" data-jumlah="2">
					<img src="<?= base_url('assets/images/icon/binaan-2.png') ?>" alt="Binaan 2">
					<span>Verbal Warning II</span>
				</button>

				<!-- Column 2: Reprimand I & II -->
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-merah"
					data-sudut="merah" data-mode="teguran_1" data-jumlah="-1">
					<img src="<?= base_url('assets/images/icon/teguran-1.png') ?>" alt="Teguran 1">
					<span>Reprimand I</span>
				</button>
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-merah"
					data-sudut="merah" data-mode="teguran_2" data-jumlah="-2">
					<img src="<?= base_url('assets/images/icon/teguran-2.png') ?>" alt="Teguran 2">
					<span>Reprimand II</span>
				</button>

				<!-- Column 3: Warning I & II -->
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-merah"
					data-sudut="merah" data-mode="peringatan_1" data-jumlah="-5">
					<img src="<?= base_url('assets/images/icon/peringatan-1.png') ?>" alt="Peringatan 1">
					<span>Warning I</span>
				</button>
				<button type="button" class="kp-icon-btn dewan-btn-hukuman dewan-btn-hukuman-merah"
					data-sudut="merah" data-mode="peringatan_2" data-jumlah="-10">
					<img src="<?= base_url('assets/images/icon/peringatan-2.png') ?>" alt="Peringatan 2">
					<span>Warning II</span>
				</button>
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
