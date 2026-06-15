<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<style>
/* ════════════════════════════════════════════════════════════════════════
   Timer Seni Battle — 100dvh Compact Blue vs Red Performance Console
   ════════════════════════════════════════════════════════════════════════ */
html, body { height: 100%; overflow: hidden; margin: 0; }

body {
	font-family: 'Poppins', sans-serif;
	background:
		radial-gradient(ellipse at top left, rgba(29, 42, 244, 0.06) 0%, transparent 45%),
		radial-gradient(ellipse at top right, rgba(221, 10, 53, 0.06) 0%, transparent 45%),
		linear-gradient(180deg, #0a0d11 0%, #14171c 100%);
}

#timer-app {
	display: flex;
	flex-direction: column;
	height: 100dvh;
	overflow: hidden;
	padding: clamp(0.4rem, 1.5vh, 0.85rem);
	gap: clamp(0.35rem, 1.2vh, 0.7rem);
}

/* ─── INFO BAR ─────────────────────────────────────────────────────────── */
.info-bar { flex-shrink: 0; display: flex; flex-wrap: wrap; gap: clamp(0.35rem, 1vw, 0.65rem); align-items: stretch; }

.info-chip {
	background: linear-gradient(135deg, #2c2f36 0%, #1a1d22 100%);
	border: 1px solid rgba(255, 255, 255, 0.07);
	border-radius: 0.65rem;
	padding: clamp(0.4rem, 1.2vh, 0.65rem) clamp(0.7rem, 2vw, 1.1rem);
	display: flex; align-items: center; justify-content: flex-start;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}
.info-chip.flex-fill { flex: 1 1 auto; min-width: 0; }
.info-chip-label { font-size: clamp(0.5rem, 1.2vw, 0.62rem); text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255, 255, 255, 0.4); display: block; line-height: 1; margin-bottom: 3px; }
.info-chip-value { font-family: 'Oswald', sans-serif; font-size: clamp(0.85rem, 2.2vw, 1.25rem); font-weight: 700; color: #fff; line-height: 1.1; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.info-chip.flex-fill .info-chip-value { white-space: normal; font-size: clamp(0.75rem, 1.8vw, 1rem); text-align: left; }

/* ─── ATHLETE CARDS (Battle: Blue vs Red) ──────────────────────────────── */
.athlete-row { flex-shrink: 0; display: grid; grid-template-columns: 1fr 1fr; gap: clamp(0.4rem, 1.2vw, 0.7rem); }

.card-atlet-battle {
	border-radius: 0.85rem;
	overflow: hidden;
	padding: clamp(0.4rem, 1vh, 0.7rem) clamp(0.6rem, 1.5vw, 1rem);
	display: flex; flex-direction: column; justify-content: center;
	box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
	position: relative;
	flex-shrink: 0;
}

.card-atlet-battle.biru { background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%); }
.card-atlet-battle.merah { background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%); text-align: right; }

.card-atlet-battle.active-corner { border: 3px solid #ffc107; box-shadow: 0 4px 20px rgba(255, 193, 7, 0.3); }

.atlet-battle-nama {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.85rem, 2.8vw, 1.5rem);
	font-weight: 700; color: #fff;
	line-height: 1.1;
	white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
	text-transform: uppercase;
}

.atlet-battle-kontingen {
	font-size: clamp(0.6rem, 1.6vw, 0.85rem);
	color: rgba(255, 255, 255, 0.75);
	white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
	margin-top: 2px;
}
.timer-section {
	flex: 1 1 auto;
	min-height: 0;
	max-height: 45vh;
	display: flex;
	flex-direction: column;
	background: linear-gradient(180deg, #1a1d22 0%, #101317 100%);
	border: 1px solid rgba(255, 255, 255, 0.07);
	border-radius: 0.9rem;
	padding: clamp(0.4rem, 1.5vh, 0.85rem) clamp(0.75rem, 2.5vw, 1.5rem);
	box-shadow: 0 6px 24px rgba(0, 0, 0, 0.4);
	overflow: hidden;
	gap: clamp(0.4rem, 1.4vh, 0.75rem);
}

/* Active corner indicator */
.active-indicator {
	flex-shrink: 0;
	text-align: center;
	padding: clamp(0.35rem, 1vh, 0.55rem);
	border-radius: 0.5rem;
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.75rem, 2vw, 1rem);
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1.5px;
	color: #fff;
}
.active-indicator.biru { background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%); }
.active-indicator.merah { background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%); }

.timer-display {
	flex: 1 1 auto;
	display: flex; align-items: center; justify-content: center;
	font-family: 'Oswald', sans-serif;
	font-weight: 700; color: #fff; line-height: 1;
	letter-spacing: 0.04em; font-variant-numeric: tabular-nums;
	font-size: clamp(4rem, 22vh, 10rem);
	min-height: 0;
	text-align: center;
}
.timer-display.warning { color: #ffc107; animation: timerPulse 1s ease-in-out infinite; }
.timer-display.danger { color: #ff4757; }
@keyframes timerPulse { 0%,100%{opacity:1;} 50%{opacity:0.55;} }

.timer-controls {
	flex-shrink: 0;
	display: grid; grid-template-columns: repeat(3, 1fr);
	gap: clamp(0.35rem, 1vw, 0.6rem);
}

.btn-timer {
	font-family: 'Oswald', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
	border: none; border-radius: 0.6rem;
	padding: clamp(0.6rem, 2vh, 1rem) 0.5rem;
	font-size: clamp(0.75rem, 1.9vw, 1.1rem);
	color: #fff;
	transition: transform 0.06s ease, filter 0.15s ease;
	display: flex; align-items: center; justify-content: center; gap: 0.4rem;
}
.btn-timer:active { transform: scale(0.97); filter: brightness(0.9); }
.btn-timer-manual { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #212529; }
.btn-timer-start.biru { background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%); }
.btn-timer-start.merah { background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%); }
.btn-timer-reset { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }

/* ─── END SECTION ──────────────────────────────────────────────────────── */
.end-section {
	flex-shrink: 0;
	display: grid; grid-template-columns: 2fr 1fr;
	gap: clamp(0.35rem, 1vw, 0.6rem);
}

.btn-end-turn, .btn-disqualify {
	font-family: 'Oswald', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
	border: none; border-radius: 0.7rem;
	padding: clamp(0.65rem, 2vh, 1.1rem);
	font-size: clamp(0.9rem, 2.2vw, 1.3rem);
	transition: transform 0.06s ease, filter 0.15s ease;
}
.btn-end-turn:active, .btn-disqualify:active { transform: scale(0.99); filter: brightness(0.95); }
.btn-end-turn { background: linear-gradient(135deg, #fff 0%, #e9ecef 100%); color: #212529; box-shadow: 0 4px 16px rgba(255, 255, 255, 0.1); }
.btn-disqualify { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: #fff; }
.btn-disqualify.cancel { background: linear-gradient(135deg, #0dcaf0 0%, #0aa3c2 100%); }

/* ─── POST-PERFORMANCE ─────────────────────────────────────────────────── */
.block-navigasi-partai .nav-finished-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.4rem, 5vw, 2.4rem);
	font-weight: 700; color: #fff; text-align: center; text-transform: uppercase;
}
.btn-winner-decision {
	width: 100%; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #000;
	font-family: 'Oswald', sans-serif; font-weight: 700; text-transform: uppercase;
	border: none; border-radius: 0.7rem;
	padding: clamp(0.7rem, 2vh, 1rem); font-size: clamp(1rem, 2.3vw, 1.3rem);
	box-shadow: 0 4px 16px rgba(255, 193, 7, 0.3);
}
.btn-switch-turn {
	width: 100%; font-family: 'Oswald', sans-serif; font-weight: 700; text-transform: uppercase;
	border: none; border-radius: 0.65rem;
	padding: clamp(0.65rem, 2vh, 1rem); font-size: clamp(0.9rem, 2.2vw, 1.2rem);
	color: #fff;
}
.btn-switch-turn.to-merah { background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%); }
.btn-switch-turn.to-biru { background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%); }
.btn-nav-match {
	width: 100%; font-family: 'Oswald', sans-serif; font-weight: 700;
	border-radius: 0.65rem; padding: clamp(0.6rem, 2vh, 0.9rem); font-size: clamp(0.85rem, 2vw, 1.1rem);
	background: linear-gradient(135deg, #f8f9fa 0%, #dee2e6 100%); color: #212529; border: none;
}

/* ─── RESPONSIVE ───────────────────────────────────────────────────────── */
@media (max-width: 575.98px) {
	.end-section { grid-template-columns: 1fr; }
	.atlet-battle-nama { font-size: clamp(0.75rem, 3.5vw, 1.1rem); }
}

@media (orientation: landscape) and (max-height: 600px) {
	#timer-app { gap: 0.3rem; padding: 0.35rem; }
	.timer-display { font-size: clamp(2.5rem, 22vh, 7rem); }
	.card-atlet-battle { padding: 0.4rem 0.75rem; }
	.atlet-battle-nama { font-size: clamp(0.8rem, 4vh, 1.2rem); }
	.btn-timer { padding: 0.4rem; font-size: 0.8rem; }
	.btn-end-turn, .btn-disqualify { padding: 0.5rem; font-size: 0.9rem; }
	.info-chip { padding: 0.3rem 0.7rem; }
	.active-indicator { padding: 0.25rem; font-size: 0.7rem; }
}

@media (prefers-reduced-motion: reduce) {
	.btn-timer, .btn-end-turn, .btn-disqualify, .timer-display { animation: none !important; transition: none !important; }
}
.btn-timer:focus-visible, .btn-end-turn:focus-visible, .btn-disqualify:focus-visible {
	outline: 3px solid rgba(255, 193, 7, 0.6); outline-offset: 2px;
}
.opacity { opacity: 0; }
.bg-navbar { background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%) !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-1">
	<div class="container-fluid px-3">
		<a class="navbar-brand d-flex align-items-center py-0" href="<?= base_url('sekretaris-pertandingan') ?>">
			<img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>" class="navbar-brand-img" alt="Logo" width="100" onerror="this.style.display='none'">
		</a>
		<button class="navbar-toggler border-0 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#navigation"><span class="navbar-toggler-icon"></span></button>
		<div class="collapse navbar-collapse" id="navigation">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
				<li class="nav-item"><a class="nav-link" href="<?= base_url('sekretaris-pertandingan') ?>"><i class="fas fa-home me-1"></i> Dashboard</a></li>
				<li class="nav-item"><a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_ganti_format_penilaian"><i class="fas fa-exchange-alt me-1"></i> Change Scoring Format</a></li>
				<li class="nav-item"><a class="nav-link cursor-pointer" onclick="sekretaris_pertandingan.open_modal_input_juara()"><i class="fas fa-trophy me-1"></i> Change Winner</a></li>
			</ul>
			<ul class="nav navbar-nav ms-auto align-items-center">
				<li class="nav-item d-none d-lg-block"><span class="badge bg-dark border border-secondary text-uppercase py-2 px-3"><i class="fas fa-user-shield me-1 text-danger"></i> Secretary</span></li>
				<li class="nav-item"><a class="nav-link btn-logout-brand shadow-sm" href="<?= base_url('perangkat-pertandingan/logout') ?>"><i class="fas fa-power-off me-1"></i> LOGOUT</a></li>
			</ul>
		</div>
	</div>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
	$is_biru_active = (($battle->id_penampilan_seni_biru ?? '') == ($penampilan->id_penampilan_seni ?? ''));
	$is_merah_active = !$is_biru_active;
?>
<div id="timer-app">

	<!-- INFO BAR -->
	<div class="info-bar block-informasi">
		<div class="info-chip opacity">
			<div><span class="info-chip-label">Arena</span><span class="info-chip-value"><?= esc($penampilan->nama_gelanggang ?? '-') ?></span></div>
		</div>
		<div class="info-chip opacity">
			<div><span class="info-chip-label">Partai</span><span class="info-chip-value"><?= esc($penampilan->nomor_partai ?? '-') ?></span></div>
		</div>
		<div class="info-chip flex-fill opacity">
			<div><span class="info-chip-label">Kategori</span><span class="info-chip-value text-capitalize"><?= esc(($penampilan->nama_kategori_usia ?? '') . ' ' . ($penampilan->jenis_kelamin ?? '') . ' ' . ucwords($penampilan->jenis_seni ?? '') . ' ' . ($penampilan->nama_seni ?? '')) ?></span></div>
		</div>
		<div class="info-chip opacity">
			<div><span class="info-chip-label">Babak</span><span class="info-chip-value"><?= esc(ucwords($battle->babak ?? '-')) ?></span></div>
		</div>
	</div>

	<!-- ATHLETE CARDS: BLUE vs RED -->
	<div class="athlete-row block-atlet">
		<div class="card-atlet-battle biru opacity atlet-biru <?= $is_biru_active ? 'active-corner' : '' ?>">
			<?php foreach ($anggota_biru ?? [] as $peserta) : ?>
				<span class="atlet-battle-nama"><?= esc($peserta->nama_pendaftar ?? '') ?></span>
			<?php endforeach; ?>
			<?php if (!empty($anggota_biru)) : ?>
				<span class="atlet-battle-kontingen"><?= esc($anggota_biru[0]->nama_kontingen ?? '') ?></span>
			<?php endif; ?>
		</div>
		<div class="card-atlet-battle merah opacity atlet-merah <?= $is_merah_active ? 'active-corner' : '' ?>">
			<?php foreach ($anggota_merah ?? [] as $peserta) : ?>
				<span class="atlet-battle-nama"><?= esc($peserta->nama_pendaftar ?? '') ?></span>
			<?php endforeach; ?>
			<?php if (!empty($anggota_merah)) : ?>
				<span class="atlet-battle-kontingen"><?= esc($anggota_merah[0]->nama_kontingen ?? '') ?></span>
			<?php endif; ?>
		</div>
	</div>

	<!-- TIMER SECTION -->
	<div class="timer-section block-stopwatch">
		<!-- Active corner indicator -->
		<?php if ($is_biru_active) : ?>
			<div class="active-indicator biru opacity"><i class="fas fa-chevron-right me-1"></i> Sudut Biru Performing</div>
		<?php else : ?>
			<div class="active-indicator merah opacity"><i class="fas fa-chevron-left me-1"></i> Sudut Merah Performing</div>
		<?php endif; ?>

		<div class="d-block timer-seni timer-display opacity">00:00</div>

		<div class="timer-controls block-kendali-waktu opacity">
			<button class="btn btn-timer btn-timer-manual" onclick="sekretaris_pertandingan.open_modal_set_manual_waktu()"><i class="fas fa-cog d-none d-md-inline"></i>Manual</button>
			<?php $btn_color_class = $is_biru_active ? 'biru' : 'merah'; ?>
			<button class="btn btn-timer btn-timer-start <?= $btn_color_class ?> button-play-state btn-toggle-waktu-tampil" data-status-penampilan="sedang_tampil" onclick="sekretaris_pertandingan.toggle_timer()"><i class="fas fa-play d-none d-md-inline"></i>START</button>
			<button class="btn btn-timer btn-timer-reset" onclick="sekretaris_pertandingan.reset_timer()"><i class="fas fa-undo d-none d-md-inline"></i>RESET</button>
		</div>
	</div>

	<!-- END TURN & DISQUALIFY -->
	<div class="end-section block-end-match">
		<div class="opacity h-100">
			<button class="btn btn-end-turn w-100 h-100 btn_selesai" onclick="sekretaris_pertandingan.selesai_penampilan()">End Turn</button>
		</div>
		<div class="opacity h-100">
			<button <?= (($penampilan->diskualifikasi ?? 0) == 1) ? '' : 'style="display:none;"' ?> class="btn btn-disqualify cancel w-100 h-100 btn-batal-diskualifikasi" onclick="sekretaris_pertandingan.batalkan_diskualifikasi_peserta()">Cancel Disq.</button>
			<button <?= (($penampilan->diskualifikasi ?? 0) == 0) ? '' : 'style="display:none;"' ?> class="btn btn-disqualify w-100 h-100 btn-diskualifikasi" onclick="sekretaris_pertandingan.diskualifikasi_peserta()">Disqualify</button>
		</div>
	</div>

	<!-- POST-PERFORMANCE NAVIGATION -->
	<div class="block-navigasi-partai d-none opacity">
		<p class="nav-finished-title mb-3">Artistic Performance Finished!</p>
		<button class="btn btn-winner-decision mb-2" onclick="sekretaris_pertandingan.open_modal_input_juara()">Winner Decision</button>
		<!-- Switch turn -->
		<?php if ($is_biru_active) : ?>
			<button class="btn btn-switch-turn to-merah mb-2" onclick="sekretaris_pertandingan.mulai_penampilan_seni('<?= esc($battle->id_penampilan_seni_merah ?? '') ?>')"><i class="fas fa-exchange-alt me-2"></i>Start Red Turn</button>
		<?php else : ?>
			<button class="btn btn-switch-turn to-biru mb-2" onclick="sekretaris_pertandingan.mulai_penampilan_seni('<?= esc($battle->id_penampilan_seni_biru ?? '') ?>')"><i class="fas fa-exchange-alt me-2"></i>Start Blue Turn</button>
		<?php endif; ?>
		<div class="row g-2">
			<div class="col-12 col-md-6"><button class="btn btn-nav-match" onclick="sekretaris_pertandingan.pindah_partai(<?= ($penampilan->nomor_partai ?? 1) - 1 ?>)">Previous Match</button></div>
			<div class="col-12 col-md-6"><button class="btn btn-nav-match" onclick="sekretaris_pertandingan.pindah_partai(<?= ($penampilan->nomor_partai ?? 1) + 1 ?>)">Next Match</button></div>
		</div>
	</div>
</div>

<?= $this->include('pertandingan/sekretaris/components/_offcanvas_pindah_partai_seni') ?>

<!-- MODAL: Manual Atur Waktu -->
<div class="modal fade" id="modalManualAturWaktu" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Set Clock Manually</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<form action="#" id="formManualAturWaktu">
					<div class="row justify-content-center my-4">
						<?php foreach (['puluh-menit', 'satuan-menit', 'puluh-detik', 'satuan-detik'] as $digIdx => $digClass) : ?>
							<?php if ($digIdx === 2) : ?><div class="col-1 text-center px-1"><div class="row h-100 align-items-center"><p class="h3">:</p></div></div><?php endif; ?>
							<div class="col-2 px-1">
								<div class="row"><div class="col-12"><button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-<?= $digClass ?>" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.<?= $digClass ?>', 1, <?= $digIdx >= 2 ? '5' : '9' ?>, this, '.btn-<?= $digClass ?>')">+</button></div></div>
								<div class="row"><div class="col-12"><p class="text-center h1 m-0 py-2 bg-gradient-180-white <?= $digClass ?>">0</p></div></div>
								<div class="row"><div class="col-12"><button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-<?= $digClass ?>" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.<?= $digClass ?>', -1, <?= $digIdx >= 2 ? '5' : '9' ?>, this, '.btn-<?= $digClass ?>')">-</button></div></div>
							</div>
						<?php endforeach; ?>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary mb-0 h5" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-warning mb-0 h5" onclick="sekretaris_pertandingan.tetapkan_perubahan_manual_waktu()">Set</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Penentuan Juara (Battle) -->
<div class="modal fade" id="modal_penentuan_juara" tabindex="-1">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<form role="form" id="form_keputusan_pemenang">
				<div class="modal-header"><h4 class="modal-title">Winning Decision</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
				<div class="modal-body">
					<div class="mt-2 mb-3">
						<p class="text-center h5 mb-3">The Winner Is:</p>
						<div class="row justify-content-center">
							<div class="col-4">
								<input type="radio" class="btn-check radio_pemenang_atlet_biru" name="id_penampilan_seni_pemenang" id="penampilan_seni_biru" autocomplete="off" value="<?= esc($battle->id_penampilan_seni_biru ?? '') ?>" required>
								<label class="btn btn-lg btn-outline-info w-100 h5 btn-winner-blue" for="penampilan_seni_biru">Blue Corner</label>
							</div>
							<div class="col-4">
								<input type="radio" class="btn-check radio_pemenang_atlet_merah" name="id_penampilan_seni_pemenang" id="penampilan_seni_merah" autocomplete="off" value="<?= esc($battle->id_penampilan_seni_merah ?? '') ?>" required>
								<label class="btn btn-lg btn-outline-primary w-100 h5 btn-winner-red" for="penampilan_seni_merah">Red Corner</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row w-100">
						<div class="col-6"><button type="button" class="btn btn-default btn-lg w-100 h5" data-bs-dismiss="modal">Cancel</button></div>
						<div class="col-6"><button type="button" class="btn btn-warning btn-lg w-100 h5" onclick="sekretaris_pertandingan.submit_input_juara_seni()">Select Winner</button></div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Ganti Format Penilaian Seni (Battle) -->
<div class="modal fade" id="modal_ganti_format_penilaian" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="formGantiFormatPenilaian" method="POST" action="<?= base_url('sekretaris-pertandingan/ganti-format-penilaian-seni/' . ($penampilan->id_penampilan_seni ?? '')) ?>">
				<div class="modal-header"><h4 class="modal-title">Ganti Format Penilaian</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Scoring Format:</label>
						<select name="format_penilaian" class="form-control text-capitalize" required>
							<?php foreach ($data_format_penilaian_seni ?? [] as $format) : ?>
								<option value="<?= esc($format) ?>"><?= esc(str_replace(['_', '.json'], [' ', ''], $format)) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Number of Jury</label>
						<?php foreach ([3, 4, 5, 6, 8, 10] as $jml) : ?>
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_juri" value="<?= $jml ?>" id="juriBattle<?= $jml ?>" required><label class="form-check-label" for="juriBattle<?= $jml ?>"><?= $jml ?> Jury</label></div>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label">Mode</label>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="penampilan_seni_ini" id="mode_battle_penampilan" checked><label class="form-check-label" for="mode_battle_penampilan">Change only for this performance</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kompetisi_seni_ini" id="mode_battle_kompetisi"><label class="form-check-label" for="mode_battle_kompetisi">Change only for this Pool</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="sub_kategori_seni_ini" id="mode_battle_subkategori"><label class="form-check-label" for="mode_battle_subkategori">Change for this whole category (<?= esc(($penampilan->jenis_seni ?? '') . ' ' . ($penampilan->jenis_kelamin ?? '') . ' ' . ($penampilan->nama_seni ?? '') . ' - ' . ($penampilan->nama_kategori_usia ?? '')) ?>)</label></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-bs-dismiss="modal">Tutup</button>
					<button type="submit" class="btn btn-primary">Ganti Format</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/shared_timer.js') ?>"></script>
<script src="<?= base_url('assets/js/penilaian/sekretaris_seni.js') ?>"></script>
<script>
$(document).ready(function() {
	const penampilan_seni_berlangsung = <?= json_encode($penampilan ?? new stdClass()) ?>;
	const waktu_tampil = <?= json_encode($penampilan->waktu_tampil ?? 0) ?>;
	sekretaris_pertandingan.init(penampilan_seni_berlangsung, waktu_tampil);
	ui.animateIn();

	$('.radio_pemenang_atlet_biru').on('change', function(e) {
		if (e.currentTarget.checked) {
			$('.btn-winner-blue').addClass('bg-blue text-white').removeClass('btn-outline-info');
			$('.btn-winner-red').removeClass('bg-red text-white').addClass('btn-outline-primary');
		}
	});
	$('.radio_pemenang_atlet_merah').on('change', function(e) {
		if (e.currentTarget.checked) {
			$('.btn-winner-red').addClass('bg-red text-white').removeClass('btn-outline-primary');
			$('.btn-winner-blue').removeClass('bg-blue text-white').addClass('btn-outline-info');
		}
	});
});

const ui = {
	animateIn: function() {
		$('.block-informasi').children('.opacity').each(function(i, v) {
			setTimeout(() => { $(v).addClass('animated slideInDown').removeClass('opacity'); }, i * 120);
		});
		let delayAtlet = ($('.block-informasi').children('.opacity').length * 120) + 150;
		setTimeout(() => {
			$('.block-atlet .atlet-biru').addClass('animated slideInLeft').removeClass('opacity');
			$('.block-atlet .atlet-merah').addClass('animated slideInRight').removeClass('opacity');
			setTimeout(() => {
				$('.block-stopwatch').children('.opacity').addClass('animated fadeIn').removeClass('opacity');
				setTimeout(() => {
					$('.block-end-match').children('.opacity').addClass('animated slideInUp').removeClass('opacity');
				}, 250);
			}, 250);
		}, delayAtlet);
	},
	animateOut: function() {
		$('.block-informasi, .block-atlet, .block-stopwatch, .block-end-match').addClass('animated fadeOut');
		setTimeout(() => { $('.block-informasi, .block-atlet, .block-stopwatch, .block-end-match').addClass('d-none'); }, 800);
	},
	animateInNavigasiPartai: function() {
		const container = document.querySelector('.block-navigasi-partai');
		if (!container) return;
		container.classList.remove('d-none');
		container.style.opacity = '0';
		container.style.transform = 'translateY(40px)';
		container.style.transition = 'all 0.5s ease';
		setTimeout(() => { container.style.opacity = '1'; container.style.transform = 'translateY(0)'; }, 50);
	},
	animateOutNavigasiPartai: function() {
		const container = document.querySelector('.block-navigasi-partai');
		if (!container) return;
		container.style.opacity = '0';
		container.style.transform = 'translateY(40px)';
		setTimeout(() => { container.classList.add('d-none'); }, 500);
	}
};
</script>
<?= $this->endSection() ?>
