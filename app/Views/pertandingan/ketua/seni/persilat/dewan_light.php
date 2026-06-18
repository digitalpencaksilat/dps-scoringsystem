<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-seni.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/kp-seni.css') ?>">
<style>
html, body { height: 100%; overflow: hidden; margin: 0; }

body { background: #f4f6f9; font-family: 'Poppins', sans-serif; color: #212529; }

#ds-wrapper {
	display: flex;
	flex-direction: column;
	height: 100dvh;
	overflow: hidden;
}

/* ─── Header ───────────────────────────────────────────────────────── */
#ds-header {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: 1fr 2fr 1fr;
	gap: clamp(0.3rem, 0.8vw, 0.6rem);
	align-items: stretch;
	padding: clamp(0.3rem, 1.2vw, 0.65rem);
	background: #fff;
	border-bottom: 2px solid #c60000;
	box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}

.ds-hdr-card {
	display: flex;
	flex-direction: column;
	justify-content: center;
	padding: clamp(0.3rem, 1vw, 0.6rem) clamp(0.4rem, 1.2vw, 0.75rem);
	border-radius: 8px;
	min-width: 0;
}

.ds-hdr-left {
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border: 1px solid #dee2e6;
	text-align: left;
}

.ds-hdr-center {
	background: linear-gradient(135deg, rgba(198,0,0,0.06) 0%, rgba(198,0,0,0.02) 100%);
	border: 1px solid rgba(198,0,0,0.15);
	align-items: center;
	justify-content: center;
}

.ds-hdr-right {
	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
	border: 1px solid #dee2e6;
	text-align: right;
	align-items: flex-end;
}

.ds-hdr-nama {
	font-size: clamp(0.8rem, 2vw, 1.05rem);
	font-weight: 700;
	line-height: 1.25;
	color: #212529;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}

.ds-hdr-sub {
	font-size: clamp(0.65rem, 1.3vw, 0.78rem);
	color: #6c757d;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	line-height: 1.3;
}

.ds-hdr-center .ds-hdr-nama {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.85rem, 2.2vw, 1.15rem);
	white-space: normal;
	text-align: center;
}

.ds-hdr-center .ds-hdr-sub { text-align: center; }

/* ─── Body ─────────────────────────────────────────────────────────── */
#ds-body {
	flex: 1;
	display: flex;
	overflow: hidden;
	gap: clamp(0.3rem, 0.8vw, 0.6rem);
	padding: clamp(0.3rem, 0.8vw, 0.6rem);
}

#ds-left {
	flex: 0 0 clamp(240px, 24vw, 320px);
	display: flex;
	flex-direction: column;
	gap: clamp(0.3rem, 0.8vw, 0.55rem);
	overflow-y: auto;
	min-width: 0;
}

#ds-right {
	flex: 1;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	min-width: 0;
	background: #fff;
	border: 1px solid #dee2e6;
	border-radius: 10px;
	box-shadow: 0 2px 12px rgba(0,0,0,0.05);
}

/* ─── Lock Button ──────────────────────────────────────────────────── */
.ds-lock-wrap { flex-shrink: 0; }

.ds-btn-lock {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: clamp(0.4rem, 1vw, 0.7rem);
	width: 100%;
	padding: clamp(0.8rem, 2.5vw, 1.1rem);
	border: none;
	border-radius: 10px;
	font-size: clamp(0.85rem, 1.8vw, 1rem);
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1px;
	cursor: pointer;
	transition: all 0.15s;
	color: #fff;
	box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.ds-btn-lock:active { transform: scale(0.97); }

.ds-btn-lock-unlock {
	background: linear-gradient(135deg, #1565c0, #0d47a1);
}

.ds-btn-lock-unlock:hover { background: linear-gradient(135deg, #1976d2, #1155a8); }

.ds-btn-lock-lock {
	background: linear-gradient(135deg, #c62828, #b71c1c);
}

.ds-btn-lock-lock:hover { background: linear-gradient(135deg, #d32f2f, #c62828); }

.ds-btn-lock i { font-size: clamp(1rem, 2.2vw, 1.3rem); }

/* ─── Total Penalties Card ─────────────────────────────────────────── */
.ds-card-total {
	flex-shrink: 0;
	background: #fff;
	border: 1px solid #dee2e6;
	border-radius: 10px;
	padding: clamp(0.5rem, 1.5vw, 0.75rem);
	text-align: center;
	box-shadow: 0 1px 8px rgba(0,0,0,0.04);
}

.ds-card-total-label {
	font-size: clamp(0.65rem, 1.2vw, 0.75rem);
	color: #6c757d;
	text-transform: uppercase;
	letter-spacing: 1px;
	margin-bottom: clamp(0.3rem, 0.8vw, 0.5rem);
}

.ds-card-total-value {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(2.5rem, 7vw, 4.5rem);
	font-weight: 700;
	line-height: 1;
	color: #c62828;
	background: #f8f9fa;
	border: 1px solid #e9ecef;
	border-radius: 8px;
	padding: clamp(0.3rem, 1vw, 0.5rem);
}

/* ─── Jury Readiness Card ──────────────────────────────────────────── */
.ds-card-jury {
	flex: 1;
	background: #fff;
	border: 1px solid #dee2e6;
	border-radius: 10px;
	padding: clamp(0.5rem, 1.2vw, 0.7rem);
	display: flex;
	flex-direction: column;
	min-height: 0;
	box-shadow: 0 1px 8px rgba(0,0,0,0.04);
}

.ds-card-jury-label {
	font-size: clamp(0.6rem, 1.1vw, 0.72rem);
	color: #6c757d;
	text-transform: uppercase;
	letter-spacing: 1px;
	margin-bottom: clamp(0.3rem, 0.8vw, 0.5rem);
	flex-shrink: 0;
}

.ds-jury-ready-list {
	flex: 1;
	overflow-y: auto;
	display: flex;
	flex-direction: column;
	gap: clamp(0.2rem, 0.5vw, 0.35rem);
}

.ds-jury-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: clamp(0.3rem, 0.8vw, 0.45rem) clamp(0.4rem, 1vw, 0.6rem);
	background: #f8f9fa;
	border-radius: 6px;
	font-size: clamp(0.65rem, 1.2vw, 0.78rem);
	border: 1px solid #e9ecef;
}

.ds-jury-item .ds-jury-nama {
	font-weight: 500;
	color: #495057;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	flex: 1;
}

.ds-jury-badge {
	font-size: clamp(0.58rem, 1vw, 0.68rem);
	padding: 0.15rem 0.55rem;
	border-radius: 1rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	flex-shrink: 0;
}

.ds-jury-ready {
	background: #d4edda;
	color: #155724;
	border: 1px solid #c3e6cb;
}

.ds-jury-waiting {
	background: #fff3cd;
	color: #856404;
	border: 1px solid #ffeeba;
}

/* ─── Right Panel Header ───────────────────────────────────────────── */
#ds-right-header {
	flex-shrink: 0;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: clamp(0.5rem, 1.2vw, 0.7rem) clamp(0.6rem, 1.5vw, 1rem);
	border-bottom: 1px solid #e9ecef;
	background: #fafafa;
}

.ds-right-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.85rem, 1.8vw, 1rem);
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1px;
	color: #212529;
}

.ds-right-title i {
	color: #c5a017;
	margin-right: 0.4rem;
	font-size: clamp(0.75rem, 1.6vw, 0.9rem);
}

/* ─── Penalty Rows ─────────────────────────────────────────────────── */
#ds-penalty-rows {
	flex: 1;
	overflow-y: auto;
	padding: clamp(0.4rem, 1vw, 0.6rem);
}

.ds-penalty-row {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: clamp(0.3rem, 0.8vw, 0.55rem);
	padding: clamp(0.55rem, 1.5vw, 0.85rem) clamp(0.5rem, 1.2vw, 0.75rem);
	margin-bottom: clamp(0.25rem, 0.6vw, 0.4rem);
	background: #f8f9fa;
	border: 1px solid #e9ecef;
	border-radius: 8px;
	transition: border-color 0.15s, box-shadow 0.15s;
}

.ds-penalty-row:hover { border-color: #c5a017; box-shadow: 0 1px 6px rgba(197,160,23,0.08); }

.ds-penalty-label {
	flex: 1 1 auto;
	min-width: 140px;
	font-weight: 600;
	font-size: clamp(0.75rem, 1.5vw, 0.88rem);
	color: #343a40;
	line-height: 1.3;
}

.ds-penalty-label small {
	display: block;
	color: #6c757d;
	font-weight: 400;
	font-size: clamp(0.6rem, 1.1vw, 0.68rem);
	margin-top: 1px;
}

.ds-penalty-actions {
	display: flex;
	align-items: center;
	gap: clamp(0.25rem, 0.6vw, 0.45rem);
	flex-wrap: wrap;
	flex-shrink: 0;
}

.ds-btn-reset {
	padding: clamp(0.35rem, 0.8vw, 0.45rem) clamp(0.55rem, 1.2vw, 0.75rem);
	background: #fff;
	border: 1px solid #dee2e6;
	border-radius: 6px;
	color: #6c757d;
	font-size: clamp(0.65rem, 1.2vw, 0.75rem);
	cursor: pointer;
	transition: all 0.15s;
	white-space: nowrap;
}

.ds-btn-reset:hover { background: #e9ecef; color: #212529; border-color: #adb5bd; }
.ds-btn-reset:active { transform: scale(0.96); }

.ds-btn-penalty-group {
	display: flex;
	gap: clamp(0.15rem, 0.4vw, 0.3rem);
	flex-wrap: wrap;
}

.ds-btn-penalty {
	min-width: clamp(44px, 8vw, 60px);
	padding: clamp(0.4rem, 1vw, 0.55rem) clamp(0.45rem, 1.2vw, 0.75rem);
	border: none;
	border-radius: 6px;
	font-weight: 700;
	font-size: clamp(0.75rem, 1.5vw, 0.88rem);
	color: #fff;
	cursor: pointer;
	transition: all 0.12s;
	text-align: center;
	box-shadow: 0 1px 6px rgba(0,0,0,0.1);
}

.ds-btn-penalty:active { transform: scale(0.94); }

.ds-btn-penalty-danger {
	background: linear-gradient(135deg, #c62828, #9a1b1b);
}
.ds-btn-penalty-danger:hover { background: linear-gradient(135deg, #d32f2f, #b71c1c); }

.ds-btn-penalty-warning {
	background: linear-gradient(135deg, #e65100, #bf360c);
}
.ds-btn-penalty-warning:hover { background: linear-gradient(135deg, #f57c00, #d84315); }

.ds-btn-penalty-dq {
	background: #fff;
	border: 2px solid #c62828;
	color: #c62828;
}
.ds-btn-penalty-dq:hover { background: #c62828; color: #fff; }

.ds-current-value {
	background: #fff;
	border: 1px solid #dee2e6;
	color: #212529;
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	font-size: clamp(0.85rem, 1.8vw, 1.05rem);
	padding: clamp(0.3rem, 0.7vw, 0.4rem) clamp(0.45rem, 1vw, 0.7rem);
	border-radius: 6px;
	min-width: clamp(38px, 7vw, 52px);
	text-align: center;
	flex-shrink: 0;
}

/* ─── Right Panel Footer ───────────────────────────────────────────── */
#ds-right-footer {
	flex-shrink: 0;
	display: flex;
	gap: clamp(0.3rem, 0.8vw, 0.55rem);
	padding: clamp(0.5rem, 1.2vw, 0.7rem);
	border-top: 1px solid #e9ecef;
	background: #fafafa;
}

.ds-btn-footer {
	flex: 1;
	padding: clamp(0.65rem, 1.8vw, 0.9rem) clamp(0.4rem, 1vw, 0.6rem);
	border: none;
	border-radius: 8px;
	font-weight: 700;
	font-size: clamp(0.7rem, 1.4vw, 0.82rem);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	cursor: pointer;
	transition: all 0.15s;
	text-align: center;
	white-space: nowrap;
	color: #fff;
	box-shadow: 0 1px 6px rgba(0,0,0,0.1);
}

.ds-btn-footer:active { transform: scale(0.97); }

.ds-btn-discard-dq {
	background: #e0f7fa;
	border: 1px solid #00bcd4;
	color: #00838f;
}
.ds-btn-discard-dq:hover { background: #b2ebf2; }

.ds-btn-disqualify {
	background: linear-gradient(135deg, #e65100, #bf360c);
}
.ds-btn-disqualify:hover { background: linear-gradient(135deg, #f57c00, #d84315); }

.ds-btn-reset-all {
	background: linear-gradient(135deg, #2e7d32, #1b5e20);
}
.ds-btn-reset-all:hover { background: linear-gradient(135deg, #388e3c, #2e7d32); }

/* ─── Empty State ──────────────────────────────────────────────────── */
.ds-empty-state {
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	height: 100%;
	color: #adb5bd;
	text-align: center;
	padding: 2rem;
}

.ds-empty-state i {
	font-size: clamp(2rem, 5vw, 3rem);
	margin-bottom: 1rem;
	opacity: 0.4;
}

.ds-empty-state p {
	font-size: clamp(0.8rem, 1.5vw, 0.95rem);
	max-width: 280px;
	line-height: 1.5;
}

/* ─── Loading Spinner ──────────────────────────────────────────────── */
.ds-loading {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 1rem;
	color: #adb5bd;
	font-size: 0.78rem;
	gap: 0.5rem;
}

.ds-loading .spinner-border { width: 1rem; height: 1rem; border-width: 2px; }

/* ─── Button States ────────────────────────────────────────────────── */
.ds-locked { opacity: 0.45; pointer-events: none; filter: grayscale(0.5); }
.ds-loading-btn { opacity: 0.55; pointer-events: none; }

.ds-pulse-ok { animation: dsPulseOkLight 0.45s ease; }
.ds-pulse-fail { animation: dsPulseFailLight 0.45s ease; }

@keyframes dsPulseOkLight {
	0% { box-shadow: 0 0 0 0 rgba(40,167,69,0.4); }
	100% { box-shadow: 0 0 0 12px rgba(40,167,69,0); }
}

@keyframes dsPulseFailLight {
	0% { box-shadow: 0 0 0 0 rgba(220,53,69,0.4); }
	100% { box-shadow: 0 0 0 12px rgba(220,53,69,0); }
}

/* ─── Scrollbar ────────────────────────────────────────────────────── */
#ds-left::-webkit-scrollbar,
#ds-penalty-rows::-webkit-scrollbar,
.ds-jury-ready-list::-webkit-scrollbar { width: 5px; }

#ds-left::-webkit-scrollbar-track,
#ds-penalty-rows::-webkit-scrollbar-track,
.ds-jury-ready-list::-webkit-scrollbar-track { background: transparent; }

#ds-left::-webkit-scrollbar-thumb,
#ds-penalty-rows::-webkit-scrollbar-thumb,
.ds-jury-ready-list::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 3px; }

#ds-left::-webkit-scrollbar-thumb:hover,
#ds-penalty-rows::-webkit-scrollbar-thumb:hover,
.ds-jury-ready-list::-webkit-scrollbar-thumb:hover { background: #adb5bd; }

/* ─── Responsive ───────────────────────────────────────────────────── */
@media (max-width: 768px) {
	#ds-body { flex-direction: column; }
	#ds-left { flex: 0 0 auto; max-height: 35vh; }
	#ds-right { flex: 1; }
	#ds-header { grid-template-columns: 1fr 1fr; }
	.ds-hdr-right { display: none; }
}

@media (max-width: 480px) {
	.ds-penalty-label { min-width: 0; width: 100%; }
	.ds-penalty-actions { width: 100%; justify-content: flex-start; }
	#ds-right-footer { flex-wrap: wrap; }
	.ds-btn-footer { flex: 1 1 auto; font-size: 0.7rem; }
}

@media (orientation: landscape) and (max-height: 500px) {
	#ds-left { flex: 0 0 clamp(180px, 28vw, 240px); }
	.ds-card-total-value { font-size: clamp(1.8rem, 5vw, 3rem); }
	.ds-btn-lock { padding: clamp(0.4rem, 1.5vw, 0.7rem); }
	.ds-penalty-row { padding: clamp(0.3rem, 0.8vw, 0.5rem); }
	.ds-hdr-nama { font-size: 0.75rem; }
}

@media (prefers-reduced-motion: reduce) {
	.ds-btn-lock, .ds-btn-reset, .ds-btn-penalty, .ds-btn-footer {
		transition: none !important;
	}
	.ds-pulse-ok, .ds-pulse-fail { animation: none !important; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
	$idPsBerlangsung = (int) $penampilan_seni_berlangsung->id_penampilan_seni;
	$isOpen = ($penampilan_seni_berlangsung->akses_penilaian ?? 'dibuka') === 'dibuka';
	$isDq = ($penampilan_seni_berlangsung->diskualifikasi ?? 0) == 1;

	$sampelJsonHukuman = null;
	if (!empty($data_nilai[$idPsBerlangsung]) && isset($data_nilai[$idPsBerlangsung][0]->penilaian)) {
		$parsedSampel = json_decode($data_nilai[$idPsBerlangsung][0]->penilaian);
		$sampelJsonHukuman = $parsedSampel->penilaian->hukuman ?? null;
	}
?>
<div id="ds-wrapper"
	 data-id-penampilan="<?= $idPsBerlangsung ?>"
	 data-akses="<?= $penampilan_seni_berlangsung->akses_penilaian ?? 'dibuka' ?>"
	 class="penampilan_seni_<?= $idPsBerlangsung ?>">

	<!-- ═══ HEADER ═══ -->
	<div id="ds-header">
		<div class="ds-hdr-card ds-hdr-left">
			<div class="ds-hdr-nama">
				<?= $penampilan_seni_berlangsung->nama_seni ?? 'Seni' ?>
			</div>
			<div class="ds-hdr-sub">
				<?= $penampilan_seni_berlangsung->nama_kategori_usia ?? '' ?>
				<?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?>
			</div>
		</div>

		<div class="ds-hdr-card ds-hdr-center">
			<div class="ds-hdr-nama">
				<?= str_replace('<br>', ' ', $penampilan_seni_berlangsung->anggota_kelompok_peserta_seni ?? '-') ?>
			</div>
			<div class="ds-hdr-sub">
				<?= $penampilan_seni_berlangsung->nama_kontingen ?? '' ?>
			</div>
		</div>

		<div class="ds-hdr-card ds-hdr-right">
			<div class="ds-hdr-nama" style="text-align:right;">
				<?= ucfirst($penampilan_seni_berlangsung->sistem_penampilan ?? 'pool') ?>
			</div>
			<div class="ds-hdr-sub" style="text-align:right;">
				Pool <?= $penampilan_seni_berlangsung->nomor_pool ?? '' ?>
			</div>
		</div>
	</div>

	<!-- ═══ BODY ═══ -->
	<div id="ds-body">

		<!-- LEFT PANEL: Controls -->
		<div id="ds-left">
			<!-- Lock / Unlock Scoring -->
			<div class="ds-lock-wrap">
				<button id="btn-toggle-akses-penilaian"
					class="ds-btn-lock <?= $isOpen ? 'ds-btn-lock-lock' : 'ds-btn-lock-unlock' ?>"
					onclick="ketua_pertandingan.ganti_akses_penilaian('<?= $isOpen ? 'ditutup' : 'dibuka' ?>')">
					<i class="fas fa-<?= $isOpen ? 'lock' : 'lock-open' ?>"></i>
					<span><?= $isOpen ? 'Lock Scoring' : 'Unlock Scoring' ?></span>
				</button>
			</div>

			<!-- Total Penalties -->
			<div class="ds-card-total">
				<div class="ds-card-total-label">Total Penalties</div>
				<div class="ds-card-total-value total_hukuman">0</div>
			</div>

			<!-- Jury Readiness -->
			<div class="ds-card-jury">
				<div class="ds-card-jury-label">
					<i class="fas fa-users me-1"></i>Status Kesiapan Juri
				</div>
				<div id="monitor-ready-juri" class="ds-jury-ready-list">
					<div class="ds-loading">
						<div class="spinner-border text-secondary" role="status"></div>
						<span>Memuat data juri...</span>
					</div>
				</div>
			</div>
		</div>

		<!-- RIGHT PANEL: Penalty Inputs -->
		<div id="ds-right">
			<!-- Header -->
			<div id="ds-right-header">
				<div class="ds-right-title">
					<i class="fas fa-gavel"></i>Input Penalties
				</div>
			</div>

			<!-- Penalty Rows -->
			<div id="ds-penalty-rows">
				<?php if ($sampelJsonHukuman !== null): ?>
					<?php foreach ($sampelJsonHukuman as $jenisHukuman => $valueHukuman): ?>
					<div class="ds-penalty-row">
						<div class="ds-penalty-label">
							<?php
								$label = str_replace("(", "<small>(", $valueHukuman->metadata->label ?? ucwords(str_replace('_', ' ', $jenisHukuman)));
								$label = str_replace(")", ")</small>", $label);
								echo $label;
							?>
						</div>
						<div class="ds-penalty-actions">
							<!-- Reset -->
							<?php if ($valueHukuman->tipe == 'pilihan ganda'): ?>
								<button class="ds-btn-reset"
									onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'terpilih' : '', 'nilai_hukuman' : 'reset'}, this)">
									<i class="fas fa-undo"></i> Reset
								</button>
							<?php else: ?>
								<button class="ds-btn-reset"
									onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'nilai_hukuman' : 'reset'}, this)">
									<i class="fas fa-undo"></i> Reset
								</button>
							<?php endif; ?>

							<!-- Value Buttons -->
							<?php if ($valueHukuman->tipe == 'pilihan ganda'): ?>
								<div class="ds-btn-penalty-group">
									<?php foreach ($valueHukuman->detail_hukuman->pilihan as $key => $value): ?>
										<?php if ($value == 'disk'): ?>
											<button class="ds-btn-penalty ds-btn-penalty-dq btn_hukuman_<?= $jenisHukuman ?>"
												onclick="ketua_pertandingan.diskualifikasi_peserta()">
												<?= $key ?>
											</button>
										<?php else: ?>
											<button class="ds-btn-penalty ds-btn-penalty-warning btn_hukuman_<?= $jenisHukuman ?>"
												onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'terpilih' : '<?= $key ?>', 'nilai_hukuman' : <?= $value ?>}, this)">
												<?= $key ?>
											</button>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
							<?php elseif ($valueHukuman->tipe == 'repetisi'): ?>
								<button class="ds-btn-penalty ds-btn-penalty-danger btn_hukuman_<?= $jenisHukuman ?>"
									onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'jumlah_repetisi' : 1}, this)">
									-<?= $valueHukuman->detail_hukuman->faktor_pengali ?>
								</button>
							<?php elseif ($valueHukuman->tipe == 'satu kali'): ?>
								<button class="ds-btn-penalty ds-btn-penalty-danger btn_hukuman_<?= $jenisHukuman ?>"
									onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'nilai_hukuman' : <?= $valueHukuman->detail_hukuman->faktor_pengali ?>}, this)">
									-<?= $valueHukuman->detail_hukuman->faktor_pengali ?>
								</button>
							<?php endif; ?>

							<!-- Current Value -->
							<div style="display:flex;gap:clamp(0.15rem,0.4vw,0.25rem);">
								<?php if ($valueHukuman->tipe == 'repetisi'): ?>
									<div class="ds-current-value jumlah_repetisi_<?= $jenisHukuman ?>" style="display:none;">
										<?= $valueHukuman->detail_hukuman->jumlah_repetisi ?? 0 ?>
									</div>
								<?php endif; ?>
								<div class="ds-current-value nilai_hukuman_<?= $jenisHukuman ?>">
									<?= $valueHukuman->detail_hukuman->nilai_hukuman ?? 0 ?>
								</div>
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				<?php else: ?>
					<div class="ds-empty-state">
						<i class="fas fa-exclamation-triangle"></i>
						<p>Tidak ada data hukuman tersedia.<br>Pastikan ada juri yang sudah menilai.</p>
					</div>
				<?php endif; ?>
			</div>

			<!-- Footer Actions -->
			<div id="ds-right-footer">
				<button class="ds-btn-footer ds-btn-discard-dq btn-batal-diskualifikasi"
					style="<?= $isDq ? '' : 'display:none;' ?>"
					onclick="ketua_pertandingan.batalkan_diskualifikasi_peserta()">
					<i class="fas fa-undo me-1"></i> Cancel DQ
				</button>
				<button class="ds-btn-footer ds-btn-disqualify btn-diskualifikasi"
					style="<?= $isDq ? 'display:none;' : '' ?>"
					onclick="ketua_pertandingan.diskualifikasi_peserta()">
					<i class="fas fa-ban me-1"></i> Disqualify
				</button>
				<button class="ds-btn-footer ds-btn-reset-all"
					onclick="ketua_pertandingan.reset_semua_hukuman(this)">
					<i class="fas fa-rotate-left me-1"></i> Reset All Penalties
				</button>
			</div>
		</div>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/kp_seni_persilat.js') ?>"></script>
<script>
	var $data_nilai = <?= json_encode($data_nilai ?? [], JSON_NUMERIC_CHECK) ?>;
	var $penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung, JSON_NUMERIC_CHECK) ?>;
	var $semua_penampilan_seni = <?= json_encode($semua_penampilan_seni ?? [], JSON_NUMERIC_CHECK) ?>;
	var $autorefresh = true;

	$(document).ready(function() {
		ketua_pertandingan.init(
			<?= $idPsBerlangsung ?>,
			$data_nilai,
			$penampilan_seni_berlangsung,
			$semua_penampilan_seni,
			$autorefresh
		);
	});
</script>
<?= $this->endSection() ?>
