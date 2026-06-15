<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<style>
/* ════════════════════════════════════════════════════════════════════════
   Timer Seni Pool — 100dvh Compact Single-Athlete Performance Console
   ════════════════════════════════════════════════════════════════════════ */
html, body { height: 100%; overflow: hidden; margin: 0; }

body {
	font-family: 'Poppins', sans-serif;
	background:
		radial-gradient(ellipse at top, rgba(197, 160, 23, 0.08) 0%, transparent 55%),
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
	display: flex; align-items: center; justify-content: center;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}
.info-chip.flex-fill { flex: 1 1 auto; min-width: 0; }
.info-chip-label {
	font-size: clamp(0.5rem, 1.2vw, 0.62rem);
	text-transform: uppercase; letter-spacing: 1.5px;
	color: rgba(255, 255, 255, 0.4);
	display: block; line-height: 1; margin-bottom: 3px;
}
.info-chip-value {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.85rem, 2.2vw, 1.25rem);
	font-weight: 700; color: #fff; line-height: 1.1;
	text-align: center;
	white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.info-chip.flex-fill .info-chip-value {
	white-space: normal;
	font-size: clamp(0.75rem, 1.8vw, 1rem);
}

/* ─── ATHLETE CARD (single, gold accent) ───────────────────────────────── */
.athlete-section { flex-shrink: 0; }

.card-atlet-seni {
	background: linear-gradient(135deg, #c5a017 0%, #9a7d12 100%);
	border-radius: 0.85rem;
	overflow: hidden;
	padding: clamp(0.65rem, 2vh, 1.2rem) clamp(1rem, 3vw, 2rem);
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	text-align: center;
	box-shadow: 0 6px 24px rgba(197, 160, 23, 0.25);
	position: relative;
	overflow: hidden;
}

.card-atlet-seni::before {
	content: '';
	position: absolute;
	inset: 0;
	background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 50%);
	pointer-events: none;
}

.atlet-seni-nama {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.1rem, 4vw, 2.2rem);
	font-weight: 700; color: #fff;
	line-height: 1.05;
	white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
	max-width: 100%;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.atlet-seni-kontingen {
	font-size: clamp(0.75rem, 2vw, 1.1rem);
	color: rgba(255, 255, 255, 0.95);
	margin-top: 4px;
	white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
	max-width: 100%;
}

/* ─── TIMER SECTION ────────────────────────────────────────────────────── */
.timer-section {
	flex: 1 1 0;
	min-height: 0;
	display: flex;
	flex-direction: column;
	background: linear-gradient(180deg, #1a1d22 0%, #101317 100%);
	border: 1px solid rgba(255, 255, 255, 0.07);
	border-radius: 0.9rem;
	padding: clamp(0.5rem, 1.8vh, 1rem) clamp(0.75rem, 2.5vw, 1.5rem);
	box-shadow: 0 6px 24px rgba(0, 0, 0, 0.4);
	overflow: hidden;
	gap: clamp(0.4rem, 1.4vh, 0.75rem);
}

.timer-display {
	flex: 1 1 auto;
	display: flex;
	align-items: center;
	justify-content: center;
	font-family: 'Oswald', sans-serif;
	font-weight: 700; color: #fff;
	line-height: 1;
	letter-spacing: 0.04em;
	font-variant-numeric: tabular-nums;
	font-size: clamp(3rem, 18vh, 9rem);
	min-height: 0;
}

.timer-display.warning { color: #ffc107; animation: timerPulse 1s ease-in-out infinite; }
.timer-display.danger { color: #ff4757; }
@keyframes timerPulse { 0%,100%{opacity:1;} 50%{opacity:0.55;} }

.timer-controls {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: clamp(0.35rem, 1vw, 0.6rem);
}

.btn-timer {
	font-family: 'Oswald', sans-serif;
	font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
	border: none; border-radius: 0.6rem;
	padding: clamp(0.6rem, 2vh, 1rem) 0.5rem;
	font-size: clamp(0.75rem, 1.9vw, 1.1rem);
	color: #fff;
	transition: transform 0.06s ease, filter 0.15s ease;
	display: flex; align-items: center; justify-content: center; gap: 0.4rem;
}

.btn-timer:active { transform: scale(0.97); filter: brightness(0.9); }
.btn-timer-manual { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #212529; }
.btn-timer-start { background: linear-gradient(135deg, #c5a017 0%, #9a7d12 100%); }
.btn-timer-reset { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }

/* ─── END SECTION ──────────────────────────────────────────────────────── */
.end-section {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: 2fr 1fr;
	gap: clamp(0.35rem, 1vw, 0.6rem);
}

.btn-end-turn, .btn-disqualify {
	font-family: 'Oswald', sans-serif;
	font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
	border: none; border-radius: 0.7rem;
	padding: clamp(0.65rem, 2vh, 1.1rem);
	font-size: clamp(0.9rem, 2.2vw, 1.3rem);
	transition: transform 0.06s ease, filter 0.15s ease;
}
.btn-end-turn:active, .btn-disqualify:active { transform: scale(0.99); filter: brightness(0.95); }
.btn-end-turn {
	background: linear-gradient(135deg, #fff 0%, #e9ecef 100%);
	color: #212529;
	box-shadow: 0 4px 16px rgba(255, 255, 255, 0.1);
}
.btn-disqualify { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); color: #fff; }
.btn-disqualify.cancel { background: linear-gradient(135deg, #0dcaf0 0%, #0aa3c2 100%); }

/* ─── POST-PERFORMANCE NAVIGATION ──────────────────────────────────────── */
.block-navigasi-partai .nav-finished-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.4rem, 5vw, 2.4rem);
	font-weight: 700; color: #fff; text-align: center;
	text-transform: uppercase;
}
.btn-winner-decision {
	width: 100%;
	background: linear-gradient(135deg, #c5a017 0%, #9a7d12 100%);
	color: #fff;
	font-family: 'Oswald', sans-serif;
	font-weight: 700; text-transform: uppercase;
	border: none; border-radius: 0.7rem;
	padding: clamp(0.7rem, 2vh, 1rem);
	font-size: clamp(1rem, 2.3vw, 1.3rem);
	box-shadow: 0 4px 16px rgba(197, 160, 23, 0.3);
}
.btn-nav-match {
	width: 100%;
	font-family: 'Oswald', sans-serif; font-weight: 700;
	border-radius: 0.65rem;
	padding: clamp(0.6rem, 2vh, 0.9rem);
	font-size: clamp(0.85rem, 2vw, 1.1rem);
	background: linear-gradient(135deg, #f8f9fa 0%, #dee2e6 100%);
	color: #212529;
	border: none;
}

/* ─── RESPONSIVE ───────────────────────────────────────────────────────── */
@media (max-width: 575.98px) {
	.timer-controls { grid-template-columns: repeat(3, 1fr); }
	.end-section { grid-template-columns: 1fr; }
	.atlet-seni-nama { font-size: clamp(1rem, 5vw, 1.4rem); }
}

@media (orientation: landscape) and (max-height: 600px) {
	#timer-app { gap: 0.3rem; padding: 0.35rem; }
	.timer-display { font-size: clamp(2.5rem, 22vh, 7rem); }
	.card-atlet-seni { padding: 0.5rem 1rem; }
	.atlet-seni-nama { font-size: clamp(1rem, 5vh, 1.6rem); }
	.atlet-seni-kontingen { font-size: clamp(0.7rem, 2.5vh, 0.9rem); }
	.btn-timer { padding: 0.4rem; font-size: 0.8rem; }
	.btn-end-turn, .btn-disqualify { padding: 0.5rem; font-size: 0.9rem; }
	.info-chip { padding: 0.3rem 0.7rem; }
}

@media (prefers-reduced-motion: reduce) {
	.btn-timer, .btn-end-turn, .btn-disqualify, .timer-display { animation: none !important; transition: none !important; }
}
.btn-timer:focus-visible, .btn-end-turn:focus-visible, .btn-disqualify:focus-visible {
	outline: 3px solid rgba(197, 160, 23, 0.6);
	outline-offset: 2px;
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
			<div><span class="info-chip-label">Kategori</span><span class="info-chip-value text-capitalize"><?= esc(($penampilan->nama_kategori_usia ?? '') . ' ' . ($penampilan->jenis_kelamin ?? '') . ' ' . ucwords($penampilan->jenis_seni ?? '') . ' ' . ($penampilan->nama_seni ?? '') . ' Pool ' . ($penampilan->nomor_pool ?? '')) ?></span></div>
		</div>
		<div class="info-chip opacity">
			<div><span class="info-chip-label">Babak</span><span class="info-chip-value"><?= esc(ucwords($penampilan->babak ?? '-')) ?></span></div>
		</div>
	</div>

	<!-- ATHLETE CARD -->
	<div class="athlete-section block-atlet">
		<div class="card-atlet-seni opacity">
			<?php if (!empty($anggota)) : ?>
				<?php foreach ($anggota as $peserta) : ?>
					<span class="atlet-seni-nama"><?= esc($peserta->nama_pendaftar ?? '') ?></span>
				<?php endforeach; ?>
				<span class="atlet-seni-kontingen"><?= esc($anggota[0]->nama_kontingen ?? '') ?></span>
			<?php else : ?>
				<span class="atlet-seni-nama">-</span>
			<?php endif; ?>
		</div>
	</div>

	<!-- TIMER SECTION -->
	<div class="timer-section block-stopwatch">
		<div class="d-block timer-seni timer-display opacity">00:00</div>

		<div class="timer-controls block-kendali-waktu opacity">
			<button class="btn btn-timer btn-timer-manual" onclick="sekretaris_pertandingan.open_modal_set_manual_waktu()"><i class="fas fa-cog d-none d-md-inline"></i>Manual</button>
			<button class="btn btn-timer btn-timer-start button-play-state btn-toggle-waktu-tampil" data-status-penampilan="sedang_tampil" onclick="sekretaris_pertandingan.toggle_timer()"><i class="fas fa-play d-none d-md-inline"></i>START</button>
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

<!-- MODAL: Penentuan Juara (Pool) -->
<div class="modal fade" id="modal_penentuan_juara" tabindex="-1">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header"><h4 class="modal-title">Winner Decision</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<form action="<?= base_url('sekretaris-pertandingan/input-manual-juara-seni') ?>" method="post" id="formJenisMedali">
					<input type="hidden" name="id_penampilan_seni" value="<?= esc($penampilan->id_penampilan_seni ?? '') ?>">
					<table class="table table-striped" id="tabelInputJuara">
						<thead><tr><th>Name</th><th>Final Score</th><th>Time</th><th>Deviation Standard</th><th>Medal</th></tr></thead>
						<tbody>
							<?php foreach ($penampilan_seni_lain ?? [] as $penSeni) : ?>
								<tr>
									<td class="align-middle"><?= esc($penSeni->anggota_kelompok_peserta_seni ?? '') ?></td>
									<td class="text-end align-middle"><?= esc($penSeni->nilai_akhir ?? '-') ?></td>
									<td class="text-end align-middle"><?php $wt = $penSeni->waktu_tampil ?? 0; echo (floor($wt / 60)) . 'm ' . ($wt % 60) . 's'; ?></td>
									<td class="text-end align-middle"><?php $catatan = json_decode($penSeni->catatan_nilai_sama ?? '{}'); echo esc($catatan->standar_deviasi ?? ''); ?></td>
									<td>
										<?php $idKps = $penSeni->id_kelompok_peserta_seni ?? ''; ?>
										<div class="row">
											<div class="col-6 mb-2"><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="medaliEmas<?= $idKps ?>" value="emas" <?= ($penSeni->jenis_medali_pool ?? '') == 'emas' ? 'checked' : '' ?>><label class="form-check-label" for="medaliEmas<?= $idKps ?>">Gold</label></div></div>
											<div class="col-6 mb-2"><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="medaliPerak<?= $idKps ?>" value="perak" <?= ($penSeni->jenis_medali_pool ?? '') == 'perak' ? 'checked' : '' ?>><label class="form-check-label" for="medaliPerak<?= $idKps ?>">Silver</label></div></div>
											<div class="col-6 mb-2"><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="medaliPerunggu<?= $idKps ?>" value="perunggu" <?= ($penSeni->jenis_medali_pool ?? '') == 'perunggu' ? 'checked' : '' ?>><label class="form-check-label" for="medaliPerunggu<?= $idKps ?>">Bronze</label></div></div>
											<div class="col-6 mb-2"><div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="tanpaMedali<?= $idKps ?>" value="none" <?= ($penSeni->jenis_medali_pool ?? null) === null ? 'checked' : '' ?>><label class="form-check-label" for="tanpaMedali<?= $idKps ?>">No Medal</label></div></div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
				<button type="button" onclick="sekretaris_pertandingan.submit_input_juara_seni()" class="btn btn-primary">Update Medal</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Ganti Format Penilaian Seni -->
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
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_juri" value="<?= $jml ?>" id="juriPool<?= $jml ?>" required><label class="form-check-label" for="juriPool<?= $jml ?>"><?= $jml ?> Jury</label></div>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label">Mode</label>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="penampilan_seni_ini" id="mode_pool_penampilan" checked><label class="form-check-label" for="mode_pool_penampilan">Change only for this performance</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kompetisi_seni_ini" id="mode_pool_kompetisi"><label class="form-check-label" for="mode_pool_kompetisi">Change only for this Pool</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="sub_kategori_seni_ini" id="mode_pool_subkategori"><label class="form-check-label" for="mode_pool_subkategori">Change for this whole category (<?= esc(($penampilan->jenis_seni ?? '') . ' ' . ($penampilan->jenis_kelamin ?? '') . ' ' . ($penampilan->nama_seni ?? '') . ' - ' . ($penampilan->nama_kategori_usia ?? '')) ?>)</label></div>
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

	if ($('#tabelInputJuara').length) {
		$('#tabelInputJuara').DataTable({
			language: { paginate: { next: ">", previous: "<" } },
			autoWidth: false, paging: true, searching: true, ordering: true, info: true, responsive: true,
		});
	}
});

const ui = {
	animateIn: function() {
		$('.block-informasi').children('.opacity').each(function(i, v) {
			setTimeout(() => { $(v).addClass('animated slideInDown').removeClass('opacity'); }, i * 120);
		});
		let delayAtlet = ($('.block-informasi').children('.opacity').length * 120) + 150;
		setTimeout(() => {
			$('.block-atlet').children('.opacity').addClass('animated fadeIn').removeClass('opacity');
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
