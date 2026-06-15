<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<style>
/* ════════════════════════════════════════════════════════════════════════
   Timer Tanding — 100dvh Compact Scoring Console
   ════════════════════════════════════════════════════════════════════════ */
html, body { height: 100%; overflow: hidden; margin: 0; }

body {
	font-family: 'Poppins', sans-serif;
	background:
		radial-gradient(ellipse at top, rgba(217, 4, 41, 0.06) 0%, transparent 55%),
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
.info-bar {
	flex-shrink: 0;
	display: flex;
	flex-wrap: wrap;
	gap: clamp(0.35rem, 1vw, 0.65rem);
	align-items: stretch;
}

.info-chip {
	background: linear-gradient(135deg, #2c2f36 0%, #1a1d22 100%);
	border: 1px solid rgba(255, 255, 255, 0.07);
	border-radius: 0.65rem;
	padding: clamp(0.4rem, 1.2vh, 0.65rem) clamp(0.7rem, 2vw, 1.1rem);
	display: flex;
	align-items: center;
	justify-content: center;
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.info-chip.flex-fill { flex: 1 1 auto; min-width: 0; }

.info-chip-label {
	font-size: clamp(0.5rem, 1.2vw, 0.62rem);
	text-transform: uppercase;
	letter-spacing: 1.5px;
	color: rgba(255, 255, 255, 0.4);
	display: block;
	line-height: 1;
	margin-bottom: 3px;
}

.info-chip-value {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.85rem, 2.2vw, 1.25rem);
	font-weight: 700;
	color: #fff;
	line-height: 1.1;
	text-align: center;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.info-chip.flex-fill .info-chip-value {
	white-space: normal;
	font-size: clamp(0.75rem, 1.8vw, 1rem);
}

/* ─── ATHLETE CARDS ────────────────────────────────────────────────────── */
.athlete-row {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: clamp(0.4rem, 1.2vw, 0.7rem);
}

.card-atlet {
	border-radius: 0.85rem;
	overflow: hidden;
	box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
	display: flex;
	align-items: center;
	padding: clamp(0.5rem, 1.6vh, 1rem) clamp(0.75rem, 2vw, 1.4rem);
	gap: clamp(0.5rem, 1.5vw, 1rem);
	min-height: 0;
}

.card-atlet.biru { background: linear-gradient(135deg, #1d2af4 0%, #0118d8 100%); }
.card-atlet.merah { background: linear-gradient(135deg, #dd0a35 0%, #b80c0c 100%); }

.atlet-info { flex: 1 1 auto; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
.card-atlet.merah .atlet-info { text-align: right; order: 2; }

.atlet-nama {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.95rem, 3vw, 1.7rem);
	font-weight: 700;
	color: #fff;
	line-height: 1.05;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	text-transform: uppercase;
}

.atlet-kontingen {
	font-size: clamp(0.65rem, 1.7vw, 0.95rem);
	color: rgba(255, 255, 255, 0.75);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	margin-top: 2px;
}

.atlet-skor {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(2.2rem, 8vw, 4.5rem);
	font-weight: 700;
	color: #fff;
	line-height: 0.9;
	font-variant-numeric: tabular-nums;
	flex-shrink: 0;
}

/* ─── TIMER SECTION (flex-grow center) ─────────────────────────────────── */
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
}

.timer-display {
	flex: 1 1 auto;
	display: flex;
	align-items: center;
	justify-content: center;
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	color: #fff;
	line-height: 1;
	letter-spacing: 0.04em;
	font-variant-numeric: tabular-nums;
	font-size: clamp(3rem, 16vh, 9rem);
	min-height: 0;
}

.timer-display.warning { color: #ffc107; animation: timerPulse 1s ease-in-out infinite; }
.timer-display.danger { color: #ff4757; }

@keyframes timerPulse { 0%,100%{opacity:1;} 50%{opacity:0.55;} }

/* Round navigation */
.round-nav {
	flex-shrink: 0;
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
	gap: clamp(0.3rem, 1vw, 0.55rem);
	margin-bottom: clamp(0.4rem, 1.4vh, 0.75rem);
}

.btn-ronde {
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	border-radius: 0.55rem;
	padding: clamp(0.3rem, 1vh, 0.5rem) clamp(0.85rem, 2.5vw, 1.5rem);
	font-size: clamp(0.7rem, 1.8vw, 0.95rem);
	border: 2px solid rgba(255, 255, 255, 0.25);
	background: transparent;
	color: rgba(255, 255, 255, 0.7);
	transition: all 0.15s ease;
}

.btn-ronde:hover { border-color: rgba(255, 255, 255, 0.5); color: #fff; }
.btn-ronde.btn-warning, .btn-ronde.active {
	background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
	border-color: #ffc107;
	color: #000;
}

/* Timer control buttons */
.timer-controls {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: repeat(4, 1fr);
	gap: clamp(0.35rem, 1vw, 0.6rem);
}

.btn-timer {
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	border: none;
	border-radius: 0.6rem;
	padding: clamp(0.6rem, 2vh, 1rem) 0.5rem;
	font-size: clamp(0.75rem, 1.9vw, 1.1rem);
	color: #fff;
	transition: transform 0.06s ease, filter 0.15s ease;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.4rem;
}

.btn-timer:active { transform: scale(0.97); filter: brightness(0.9); }
.btn-timer-manual { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); color: #212529; }
.btn-timer-start { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #000; }
.btn-timer-reset { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }
.btn-timer-weight { background: linear-gradient(135deg, #0dcaf0 0%, #0aa3c2 100%); }

/* ─── END MATCH ────────────────────────────────────────────────────────── */
.end-match-section { flex-shrink: 0; }

.btn-end-match {
	width: 100%;
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 1px;
	border: none;
	border-radius: 0.7rem;
	padding: clamp(0.65rem, 2vh, 1.1rem);
	font-size: clamp(1rem, 2.5vw, 1.4rem);
	background: linear-gradient(135deg, #fff 0%, #e9ecef 100%);
	color: #212529;
	box-shadow: 0 4px 16px rgba(255, 255, 255, 0.1);
	transition: transform 0.06s ease, filter 0.15s ease;
}

.btn-end-match:active { transform: scale(0.99); filter: brightness(0.95); }

/* ─── POST-MATCH NAVIGATION ────────────────────────────────────────────── */
.block-navigasi-partai .nav-finished-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.5rem, 5vw, 2.5rem);
	font-weight: 700;
	color: #fff;
	text-align: center;
	text-transform: uppercase;
}

.btn-nav-match {
	width: 100%;
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	border-radius: 0.65rem;
	padding: clamp(0.6rem, 2vh, 1rem);
	font-size: clamp(0.85rem, 2vw, 1.15rem);
	background: linear-gradient(135deg, #f8f9fa 0%, #dee2e6 100%);
	color: #212529;
	border: none;
	transition: transform 0.06s ease;
}
.btn-nav-match:active { transform: scale(0.98); }
.btn-nav-match:disabled { opacity: 0.45; }

.btn-jump-match {
	width: 100%;
	font-family: 'Oswald', sans-serif;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	border-radius: 0.65rem;
	padding: clamp(0.55rem, 1.8vh, 0.9rem);
	font-size: clamp(0.8rem, 1.9vw, 1.05rem);
	border: 2px solid rgba(255, 255, 255, 0.3);
	background: transparent;
	color: rgba(255, 255, 255, 0.85);
	transition: all 0.15s ease;
}
.btn-jump-match:hover { border-color: rgba(255, 255, 255, 0.55); color: #fff; }

/* ─── RESPONSIVE ───────────────────────────────────────────────────────── */
@media (max-width: 575.98px) {
	.athlete-row { grid-template-columns: 1fr 1fr; }
	.atlet-skor { font-size: clamp(1.8rem, 9vw, 2.8rem); }
	.atlet-nama { font-size: clamp(0.8rem, 3.5vw, 1.1rem); }
	.timer-controls { grid-template-columns: repeat(2, 1fr); }
	.info-chip-value { font-size: clamp(0.7rem, 3vw, 0.95rem); }
}

@media (orientation: landscape) and (max-height: 600px) {
	#timer-app { gap: 0.3rem; padding: 0.35rem; }
	.timer-display { font-size: clamp(2.5rem, 22vh, 7rem); }
	.atlet-skor { font-size: clamp(1.8rem, 11vh, 3.5rem); }
	.card-atlet { padding: 0.4rem 0.85rem; }
	.btn-timer { padding: clamp(0.4rem, 1.5vh, 0.7rem) 0.4rem; }
	.btn-end-match { padding: clamp(0.45rem, 1.5vh, 0.8rem); }
	.info-chip { padding: 0.3rem 0.7rem; }
}

/* ─── ACCESSIBILITY ────────────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
	.btn-timer, .btn-ronde, .btn-end-match, .btn-nav-match, .timer-display { animation: none !important; transition: none !important; }
}

.btn-timer:focus-visible, .btn-ronde:focus-visible, .btn-end-match:focus-visible {
	outline: 3px solid rgba(255, 193, 7, 0.6);
	outline-offset: 2px;
}

/* Override legacy classes that JS/animations reference */
.opacity { opacity: 0; }
.bg-navbar { background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%) !important; }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-1">
	<div class="container-fluid px-3">
		<a class="navbar-brand d-flex align-items-center py-0" href="<?= base_url('sekretaris-pertandingan') ?>">
			<img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
				class="navbar-brand-img" alt="Logo" width="100"
				onerror="this.style.display='none'">
		</a>
		<button class="navbar-toggler border-0 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navigation">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
				<li class="nav-item">
					<a class="nav-link" href="<?= base_url('sekretaris-pertandingan') ?>"><i class="fas fa-home me-1"></i> Dashboard</a>
				</li>
				<li class="nav-item">
					<a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_pengaturan_suara"><i class="fas fa-volume-up me-1"></i> Sound Setting</a>
				</li>
				<li class="nav-item">
					<a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_ganti_format_penilaian"><i class="fas fa-exchange-alt me-1"></i> Format Score</a>
				</li>
				<li class="nav-item">
					<a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_ubah_waktu"><i class="fas fa-clock me-1"></i> Change Time</a>
				</li>
			</ul>
			<ul class="nav navbar-nav ms-auto align-items-center">
				<li class="nav-item d-none d-lg-block">
					<span class="badge bg-dark border border-secondary text-uppercase py-2 px-3"><i class="fas fa-user-shield me-1 text-danger"></i> Secretary</span>
				</li>
				<li class="nav-item">
					<a class="nav-link btn-logout-brand shadow-sm" href="<?= base_url('perangkat-pertandingan/logout') ?>"><i class="fas fa-power-off me-1"></i> LOGOUT</a>
				</li>
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
			<div>
				<span class="info-chip-label">Arena</span>
				<span class="info-chip-value"><?= esc($pertandingan->nama_gelanggang ?? '-') ?></span>
			</div>
		</div>
		<div class="info-chip opacity">
			<div>
				<span class="info-chip-label">Partai</span>
				<span class="info-chip-value"><?= esc($pertandingan->nomor_partai ?? '-') ?></span>
			</div>
		</div>
		<div class="info-chip flex-fill opacity">
			<div>
				<span class="info-chip-label">Kategori</span>
				<span class="info-chip-value text-capitalize"><?= esc(($pertandingan->nama_kategori_usia ?? '') . ' ' . ($pertandingan->jenis_kelamin ?? '') . ' - ' . ($pertandingan->label ?? '')) ?></span>
			</div>
		</div>
		<div class="info-chip opacity">
			<div>
				<span class="info-chip-label">Babak</span>
				<span class="info-chip-value"><?= esc(ucwords($pertandingan->babak ?? '-')) ?></span>
			</div>
		</div>
	</div>

	<!-- ATHLETE CARDS -->
	<div class="athlete-row block-atlet">
		<div class="card-atlet biru opacity atlet-biru">
			<div class="atlet-info">
				<span class="atlet-nama"><?= esc($atlet_biru->nama_pendaftar ?? '-') ?></span>
				<span class="atlet-kontingen"><?= esc($atlet_biru->nama_kontingen ?? '') ?></span>
			</div>
			<span class="atlet-skor skor-biru"><?= (int)($pertandingan->skor_biru ?? 0) ?></span>
		</div>
		<div class="card-atlet merah opacity atlet-merah">
			<div class="atlet-info">
				<span class="atlet-nama"><?= esc($atlet_merah->nama_pendaftar ?? '-') ?></span>
				<span class="atlet-kontingen"><?= esc($atlet_merah->nama_kontingen ?? '') ?></span>
			</div>
			<span class="atlet-skor skor-merah"><?= (int)($pertandingan->skor_merah ?? 0) ?></span>
		</div>
	</div>

	<!-- TIMER SECTION -->
	<div class="timer-section block-stopwatch">
		<div class="d-block timer-tanding timer-display opacity">
			<?php
				$dataWaktu = json_decode($pertandingan->data_waktu ?? '{}');
				$waktuPerRonde = (int)($pertandingan->waktu_per_ronde ?? 120);
				$sisaWaktu = $waktuPerRonde;
				if (isset($dataWaktu->sisa_waktu)) $sisaWaktu = (int)$dataWaktu->sisa_waktu;
				$menit = str_pad(floor($sisaWaktu / 60), 2, '0', STR_PAD_LEFT);
				$detik = str_pad($sisaWaktu % 60, 2, '0', STR_PAD_LEFT);
			?>
			<span class="timer-menit"><?= $menit ?></span><span class="timer-separator">:</span><span class="timer-detik"><?= $detik ?></span>
		</div>

		<div class="round-nav block-navigasi-ronde opacity">
			<?php
			$jumlahRonde = (int) ($pertandingan->jumlah_ronde ?? 3);
			$rondeAktif  = (int) ($pertandingan->ronde_pertandingan ?? 1);
			for ($r = 1; $r <= $jumlahRonde; $r++) :
			?>
				<button class="btn btn-ronde <?= ($r == $rondeAktif) ? 'btn-warning active' : '' ?>" data-ronde="<?= $r ?>" onclick="sekretaris_pertandingan.pindah_ronde(<?= $r ?>)">Ronde <?= $r ?></button>
			<?php endfor; ?>
		</div>

		<div class="timer-controls block-kendali-waktu opacity">
			<button class="btn btn-timer btn-timer-manual" onclick="sekretaris_pertandingan.open_modal_set_manual_waktu()"><i class="fas fa-cog d-none d-md-inline"></i>Manual</button>
			<button class="btn btn-timer btn-timer-start button-play-state btn-toggle-waktu" onclick="sekretaris_pertandingan.toggle_timer()"><i class="fas fa-play d-none d-md-inline"></i>START</button>
			<button class="btn btn-timer btn-timer-reset" onclick="sekretaris_pertandingan.reset_timer()"><i class="fas fa-undo d-none d-md-inline"></i>RESET</button>
			<button class="btn btn-timer btn-timer-weight" data-bs-toggle="modal" data-bs-target="#modal_info_penimbangan"><i class="fas fa-weight d-none d-md-inline"></i>Weight</button>
		</div>
	</div>

	<!-- END MATCH -->
	<div class="end-match-section block-end-match">
		<div class="opacity">
			<button class="btn btn-end-match btn_selesai" data-bs-toggle="modal" data-bs-target="#modal_keputusan_pemenang">End Match</button>
		</div>
	</div>

	<!-- JUMP TO MATCH (hidden by default) -->
	<div class="end-match-section block-jump-to-match d-none">
		<button class="btn btn-jump-match" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasPindahPartaiTanding" aria-controls="offcanvasPindahPartaiTanding">
			<i class="fas fa-list me-2"></i> Jump To Match
		</button>
	</div>

	<!-- POST-MATCH NAVIGATION (hidden by default) -->
	<div class="block-navigasi-partai d-none opacity">
		<p class="nav-finished-title mb-3">Match Finished!</p>
		<div class="row g-2">
			<div class="col-12 col-md-6">
				<?php if (!empty($partai_prev)) : ?>
					<button class="btn btn-nav-match" onclick="sekretaris_pertandingan.pindah_partai(<?= (int) $partai_prev->id_pertandingan ?>)">
						<i class="fas fa-arrow-left me-2"></i> Previous Match
						<br><small class="text-primary">Partai <?= esc($partai_prev->nomor_partai ?? '') ?></small>
					</button>
				<?php else : ?>
					<button class="btn btn-nav-match" disabled><i class="fas fa-arrow-left me-2"></i> Previous Match<br><small class="text-muted">Tidak ada</small></button>
				<?php endif; ?>
			</div>
			<div class="col-12 col-md-6">
				<?php if (!empty($partai_next)) : ?>
					<button class="btn btn-nav-match" onclick="sekretaris_pertandingan.pindah_partai(<?= (int) $partai_next->id_pertandingan ?>)">
						Next Match <i class="fas fa-arrow-right ms-2"></i>
						<br><small class="text-primary">Partai <?= esc($partai_next->nomor_partai ?? '') ?></small>
					</button>
				<?php else : ?>
					<button class="btn btn-nav-match" disabled>Next Match <i class="fas fa-arrow-right ms-2"></i><br><small class="text-muted">Tidak ada</small></button>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Keputusan Pemenang -->
<div class="modal fade" id="modal_keputusan_pemenang" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<form role="form" id="form_keputusan_pemenang">
				<div class="modal-header">
					<h4 class="modal-title">Keputusan Pemenang</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="mt-2 mb-3">
						<p class="text-center h5 mb-3">Pemenang Pertandingan:</p>
						<div class="row justify-content-center g-3">
							<div class="col-5">
								<input type="radio" class="btn-check" name="pemenang" id="pemenang_biru" autocomplete="off" value="biru" required>
								<label class="btn btn-lg btn-outline-info w-100 h5 py-3 btn-winner-blue" for="pemenang_biru"><i class="fas fa-user me-1"></i> Sudut Biru</label>
							</div>
							<div class="col-5">
								<input type="radio" class="btn-check" name="pemenang" id="pemenang_merah" autocomplete="off" value="merah" required>
								<label class="btn btn-lg btn-outline-danger w-100 h5 py-3 btn-winner-red" for="pemenang_merah"><i class="fas fa-user me-1"></i> Sudut Merah</label>
							</div>
						</div>
					</div>
					<div class="mt-4">
						<p class="text-center h5 mb-3">Jenis Kemenangan:</p>
						<div class="row justify-content-center g-2">
							<?php
							$jenis_menang = ['Poin', 'Teknik', 'Mutlak', 'Diskualifikasi', 'WO', 'BYE', 'Menang Angka'];
							foreach ($jenis_menang as $jIdx => $jenis) :
							?>
								<div class="col-auto">
									<input type="radio" class="btn-check" name="jenis_kemenangan" id="jenis_<?= $jIdx ?>" value="<?= $jenis ?>" required>
									<label class="btn btn-outline-dark" for="jenis_<?= $jIdx ?>"><?= $jenis ?></label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row w-100">
						<div class="col-6"><button type="button" class="btn btn-default btn-lg w-100 h5" data-bs-dismiss="modal">Batal</button></div>
						<div class="col-6"><button type="button" class="btn btn-warning btn-lg w-100 h5" onclick="sekretaris_pertandingan.selesaikan_pertandingan()">Selesaikan</button></div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Manual Atur Waktu -->
<div class="modal fade" id="modalManualAturWaktu" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Set Clock Manually</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
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
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary mb-0 h5" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-warning mb-0 h5" onclick="sekretaris_pertandingan.tetapkan_perubahan_manual_waktu()">Set</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Ubah Waktu -->
<div class="modal fade" id="modal_ubah_waktu" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form id="formUbahWaktu">
				<div class="modal-header"><h5 class="modal-title">Configure Time</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Jumlah Ronde</label>
						<div class="d-flex gap-3">
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_ronde" value="2" id="ronde2" <?= ($pertandingan->jumlah_ronde ?? 3) == 2 ? 'checked' : '' ?>><label class="form-check-label" for="ronde2">2 Ronde</label></div>
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_ronde" value="3" id="ronde3" <?= ($pertandingan->jumlah_ronde ?? 3) == 3 ? 'checked' : '' ?>><label class="form-check-label" for="ronde3">3 Ronde</label></div>
						</div>
					</div>
					<div class="mb-3"><label class="form-label" for="waktu_per_ronde">Durasi per Ronde (detik)</label><input type="number" class="form-control" id="waktu_per_ronde" name="waktu_per_ronde" value="<?= esc($pertandingan->waktu_per_ronde ?? 120) ?>" min="30" max="600"></div>
					<div class="mb-3"><label class="form-label" for="waktu_istirahat">Waktu Istirahat (detik)</label><input type="number" class="form-control" id="waktu_istirahat" name="waktu_istirahat" value="<?= esc($pertandingan->waktu_istirahat ?? 60) ?>" min="10" max="300"></div>
					<div class="mb-3">
						<label class="form-label">Mode Perubahan</label>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="pertandingan_ini" id="mode_pertandingan" checked><label class="form-check-label" for="mode_pertandingan">Pertandingan ini saja</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kelas_ini" id="mode_kelas"><label class="form-check-label" for="mode_kelas">Seluruh kelas ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kategori_lomba_ini" id="mode_kategori"><label class="form-check-label" for="mode_kategori">Seluruh kategori ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="gelanggang_ini" id="mode_gelanggang"><label class="form-check-label" for="mode_gelanggang">Seluruh arena ini</label></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-primary" onclick="sekretaris_pertandingan.ubah_waktu()">Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Ganti Format Penilaian -->
<div class="modal fade" id="modal_ganti_format_penilaian" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<form id="formGantiFormatPenilaian" method="POST" action="<?= base_url('sekretaris-pertandingan/ganti-format-penilaian-tanding/' . ($pertandingan->id_pertandingan ?? '')) ?>">
				<div class="modal-header"><h4 class="modal-title">Ganti Format Penilaian</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Format Penilaian:</label>
						<select name="format_penilaian" class="form-control text-capitalize" required>
							<?php foreach ($data_format_penilaian ?? [] as $format) : ?>
								<option value="<?= esc($format) ?>"><?= esc(str_replace(['_', '.json'], [' ', ''], $format)) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Jumlah Juri</label>
						<?php foreach ([1, 3, 4, 5] as $jml) : ?>
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_juri" value="<?= $jml ?>" id="juri<?= $jml ?>" required><label class="form-check-label" for="juri<?= $jml ?>"><?= $jml ?> Juri</label></div>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label">Mode</label>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="pertandingan_ini" id="mode_fp_pertandingan" checked><label class="form-check-label" for="mode_fp_pertandingan">Pertandingan ini saja</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kelas_ini" id="mode_fp_kelas"><label class="form-check-label" for="mode_fp_kelas">Seluruh kelas ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kategori_lomba_ini" id="mode_fp_kategori"><label class="form-check-label" for="mode_fp_kategori">Seluruh kategori ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="gelanggang_ini" id="mode_fp_gelanggang"><label class="form-check-label" for="mode_fp_gelanggang">Seluruh arena ini</label></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-primary" onclick="sekretaris_pertandingan.ganti_format_penilaian()">Ganti Format</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Pengaturan Suara -->
<div class="modal fade" id="modal_pengaturan_suara" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Pengaturan Suara</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<div class="mb-3">
					<label class="form-label">Jenis Gong</label>
					<select class="form-select" id="jenis_gong">
						<option value="gong_1">Gong 1</option><option value="gong_2">Gong 2</option><option value="whistle_1">Whistle 1</option><option value="whistle_2">Whistle 2</option>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label">Alarm Beep (10 detik terakhir)</label>
					<div class="form-check"><input class="form-check-input" type="radio" name="beep_alarm" value="1" id="beep_ya" checked><label class="form-check-label" for="beep_ya">Ya</label></div>
					<div class="form-check"><input class="form-check-input" type="radio" name="beep_alarm" value="0" id="beep_tidak"><label class="form-check-label" for="beep_tidak">Tidak</label></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
				<button type="button" class="btn btn-primary" onclick="sekretaris_pertandingan.simpan_pengaturan_suara()">Simpan</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Info Penimbangan -->
<div class="modal fade" id="modal_info_penimbangan" tabindex="-1">
	<div class="modal-dialog modal-fullscreen">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Informasi Penimbangan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6">
						<div class="card border-primary mb-3">
							<div class="card-header bg-blue text-white fw-bold">Sudut Biru - <?= esc($atlet_biru->nama_pendaftar ?? '-') ?></div>
							<div class="card-body">
								<table class="table table-sm">
									<tr><th>Berat Badan</th><td class="text-end"><?= esc($pertandingan->berat_biru ?? '-') ?> kg</td></tr>
									<tr><th>Status Timbang</th><td class="text-end"><?= esc($pertandingan->hasil_timbang_biru ?? 'belum_timbang') ?></td></tr>
								</table>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card border-danger mb-3">
							<div class="card-header bg-red text-white fw-bold">Sudut Merah - <?= esc($atlet_merah->nama_pendaftar ?? '-') ?></div>
							<div class="card-body">
								<table class="table table-sm">
									<tr><th>Berat Badan</th><td class="text-end"><?= esc($pertandingan->berat_merah ?? '-') ?> kg</td></tr>
									<tr><th>Status Timbang</th><td class="text-end"><?= esc($pertandingan->hasil_timbang_merah ?? 'belum_timbang') ?></td></tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
		</div>
	</div>
</div>

<?= view('pertandingan/sekretaris/components/_offcanvas_pindah_partai_tanding') ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/shared_timer.js') ?>"></script>
<script src="<?= base_url('assets/js/penilaian/sekretaris_tanding.js') ?>"></script>
<script>
$(document).ready(function() {
	const pertandingan = <?= json_encode($pertandingan ?? new stdClass()) ?>;
	<?php
		$dw = $data_waktu ?? null;
		$sisaWaktuJs = 0;
		if (is_object($dw) && isset($dw->sisa_waktu)) {
			$sisaWaktuJs = (int) $dw->sisa_waktu;
		} else {
			$sisaWaktuJs = (int) ($pertandingan->waktu_per_ronde ?? 120);
		}
	?>
	const waktu_pertandingan = <?= $sisaWaktuJs ?>;
	sekretaris_pertandingan.init(pertandingan, waktu_pertandingan);
	ui.animateIn();
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
		setTimeout(() => {
			$('.block-informasi, .block-atlet, .block-stopwatch, .block-end-match').addClass('d-none');
		}, 800);
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
