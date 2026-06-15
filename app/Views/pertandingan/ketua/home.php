<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
/* ========================================================================
   Ketua Pertandingan Home — 100vh Compact, Dark Theme
   Design tokens & visual language parity with sekretaris + juri dashboard
   ======================================================================== */
:root {
	--space-2: 0.5rem;
	--space-3: 0.75rem;
	--space-4: 1rem;
	--space-5: 1.5rem;
	--space-6: 2rem;
	--radius-sm: 0.375rem;
	--radius-md: 0.5rem;
	--radius-lg: 0.75rem;
	--radius-xl: 1rem;
	--surface-1: rgba(255, 255, 255, 0.03);
	--surface-2: rgba(255, 255, 255, 0.06);
	--surface-3: rgba(255, 255, 255, 0.09);
	--border-subtle: rgba(255, 255, 255, 0.08);
	--border-default: rgba(255, 255, 255, 0.12);
	--text-primary: rgba(255, 255, 255, 0.95);
	--text-secondary: rgba(255, 255, 255, 0.65);
	--text-tertiary: rgba(255, 255, 255, 0.45);
	--accent-tanding: #c60000;
	--accent-seni: #c5a017;
}

html, body {
	height: 100%;
	overflow: hidden;
}

body {
	display: flex;
	flex-direction: column;
	background:
		radial-gradient(ellipse at top left, rgba(198, 0, 0, 0.08) 0%, transparent 50%),
		radial-gradient(ellipse at bottom right, rgba(197, 160, 23, 0.06) 0%, transparent 50%),
		linear-gradient(180deg, #0a0e13 0%, #0f1419 100%);
	color: var(--text-primary);
}

.navbar-custom {
	flex-shrink: 0;
	backdrop-filter: blur(12px);
	background: rgba(10, 14, 19, 0.85) !important;
}

/* ========================================================================
   Main Container
   ======================================================================== */
.kp-dashboard {
	flex: 1;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	padding: clamp(0.75rem, 2vw, 1.5rem);
	gap: clamp(1rem, 2vw, 1.5rem);
	max-width: 1200px;
	width: 100%;
	margin: 0 auto;
}

/* ========================================================================
   Header Bar
   ======================================================================== */
.kp-header {
	flex-shrink: 0;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: var(--space-4);
	flex-wrap: wrap;
}

.kp-arena {
	display: flex;
	align-items: center;
	gap: var(--space-3);
	min-width: 0;
}

.kp-arena-icon {
	width: clamp(2.5rem, 5vw, 3rem);
	height: clamp(2.5rem, 5vw, 3rem);
	border-radius: var(--radius-md);
	background: linear-gradient(135deg, var(--accent-tanding) 0%, #890108 100%);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: clamp(1rem, 2.5vw, 1.25rem);
	color: white;
	flex-shrink: 0;
	box-shadow: 0 4px 12px rgba(198, 0, 0, 0.3);
}

.kp-arena-text { min-width: 0; }

.kp-arena-text h1 {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1rem, 3vw, 1.5rem);
	font-weight: 700;
	margin: 0;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	line-height: 1.2;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.kp-arena-text p {
	font-size: clamp(0.7rem, 1.8vw, 0.85rem);
	color: var(--text-secondary);
	margin: 0;
	line-height: 1.3;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.kp-badge {
	flex-shrink: 0;
	display: inline-flex;
	align-items: center;
	gap: var(--space-2);
	padding: var(--space-2) var(--space-3);
	background: linear-gradient(135deg, rgba(197, 160, 23, 0.12) 0%, rgba(197, 160, 23, 0.06) 100%);
	border: 1px solid rgba(197, 160, 23, 0.2);
	border-radius: var(--radius-md);
	font-size: clamp(0.75rem, 1.5vw, 0.85rem);
	font-weight: 600;
	color: var(--accent-seni);
}

.kp-badge i { font-size: 0.85rem; }

/* ========================================================================
   Card Grid
   ======================================================================== */
.kp-card-grid {
	flex: 1;
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));
	gap: clamp(0.75rem, 1.5vw, 1rem);
	align-content: center;
	padding: 4px;
}

.kp-card {
	background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-1) 100%);
	border: 1px solid var(--border-subtle);
	border-radius: var(--radius-xl);
	overflow: hidden;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
	position: relative;
	display: flex;
	flex-direction: column;
}

.kp-card::before {
	content: '';
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	height: 3px;
	background: linear-gradient(90deg, transparent, currentColor, transparent);
	opacity: 0;
	transition: opacity 0.3s ease;
}

.kp-card:hover {
	transform: translateY(-4px);
	border-color: var(--border-default);
	box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.kp-card:hover::before { opacity: 1; }

.kp-card.tanding { color: var(--accent-tanding); }
.kp-card.tanding:hover { border-color: rgba(198, 0, 0, 0.3); }
.kp-card.seni { color: var(--accent-seni); }
.kp-card.seni:hover { border-color: rgba(197, 160, 23, 0.3); }
.kp-card.daftar { color: rgba(255, 255, 255, 0.55); }
.kp-card.daftar:hover { border-color: rgba(255, 255, 255, 0.2); }

/* Card Header */
.kp-card-header {
	display: flex;
	align-items: center;
	gap: var(--space-4);
	padding: clamp(1rem, 3vw, 1.5rem);
	border-bottom: 1px solid var(--border-subtle);
}

.kp-card-icon {
	width: clamp(3rem, 8vw, 4rem);
	height: clamp(3rem, 8vw, 4rem);
	border-radius: var(--radius-lg);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: clamp(1.2rem, 3.5vw, 1.8rem);
	color: white;
	flex-shrink: 0;
	box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
	position: relative;
	overflow: hidden;
}

.kp-card-icon::before {
	content: '';
	position: absolute;
	inset: 0;
	background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 100%);
	opacity: 0;
	transition: opacity 0.3s ease;
}

.kp-card:hover .kp-card-icon::before { opacity: 1; }

.kp-card-icon.tanding { background: linear-gradient(135deg, #d90429 0%, #b90422 100%); }
.kp-card-icon.seni { background: linear-gradient(135deg, #c5a017 0%, #9a7d12 100%); }
.kp-card-icon.daftar { background: linear-gradient(135deg, #333 0%, #111 100%); }

.kp-card-info { flex: 1; min-width: 0; }

.kp-card-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.1rem, 3vw, 1.4rem);
	font-weight: 700;
	margin: 0;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--text-primary);
}

.kp-card-sub {
	font-size: clamp(0.75rem, 1.8vw, 0.85rem);
	color: var(--text-secondary);
	margin: 2px 0 0 0;
}

/* Card Body */
.kp-card-body {
	padding: clamp(1rem, 3vw, 1.5rem);
	display: flex;
	flex-direction: column;
	gap: var(--space-3);
	flex: 1;
}

/* Buttons */
.kp-btn {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: var(--space-2);
	padding: clamp(0.75rem, 2vw, 1rem) clamp(1rem, 3vw, 1.5rem);
	border-radius: var(--radius-md);
	font-size: clamp(0.85rem, 2vw, 0.95rem);
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	text-decoration: none;
	border: 2px solid transparent;
	transition: all 0.2s ease;
	cursor: pointer;
	position: relative;
	overflow: hidden;
}

.kp-btn::before {
	content: '';
	position: absolute;
	inset: 0;
	background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
	opacity: 0;
	transition: opacity 0.2s ease;
}

.kp-btn:hover::before { opacity: 1; }
.kp-btn i { font-size: clamp(0.9rem, 2.2vw, 1rem); }

.kp-btn-primary {
	background: currentColor;
	color: #fff !important;
	border-color: currentColor;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.tanding .kp-btn-primary {
	background: linear-gradient(135deg, #d90429 0%, #b90422 100%);
	border-color: #d90429;
}

.seni .kp-btn-primary {
	background: linear-gradient(135deg, #c5a017 0%, #9a7d12 100%);
	border-color: #c5a017;
}

.daftar .kp-btn-primary {
	background: linear-gradient(135deg, #444 0%, #222 100%);
	border-color: #444;
}

.kp-btn-primary:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
	color: #fff !important;
	text-decoration: none;
}

.kp-btn-outline {
	background: var(--surface-1);
	color: var(--text-primary);
	border-color: var(--border-subtle);
}

.kp-btn-outline:hover {
	background: var(--surface-2);
	border-color: rgba(255, 255, 255, 0.2);
	color: var(--text-primary);
	text-decoration: none;
}

/* ========================================================================
   Responsive
   ======================================================================== */
@media (max-width: 768px) {
	.kp-header { gap: var(--space-3); }
	.kp-card-grid { grid-template-columns: 1fr; }
}

@media (max-width: 576px) {
	.kp-dashboard { padding: 1rem; }
	.kp-card-header { padding: 1rem; }
	.kp-card-body { padding: 1rem; }
}

@media (orientation: landscape) and (max-height: 600px) {
	.kp-dashboard { padding: var(--space-2); gap: var(--space-2); }

	.kp-card-grid {
		grid-template-columns: repeat(3, 1fr);
		gap: var(--space-2);
	}

	.kp-card-header, .kp-card-body { padding: 0.75rem; }
	.kp-card-icon { width: 2.5rem; height: 2.5rem; font-size: 1rem; }
	.kp-card-title { font-size: 1rem; }
	.kp-card-sub { font-size: 0.7rem; }
	.kp-btn { padding: 0.6rem 0.75rem; font-size: 0.8rem; }
	.kp-arena-icon { width: 2rem; height: 2rem; font-size: 0.9rem; }
	.kp-arena-text h1 { font-size: 1rem; }
}

@media (prefers-reduced-motion: reduce) {
	.kp-card, .kp-btn, .kp-card-icon::before, .kp-btn::before {
		transition: none !important;
	}
	.kp-card:hover, .kp-btn-primary:hover { transform: none; }
}

.kp-btn:focus-visible, .kp-card:focus-within {
	outline: 2px solid rgba(217, 4, 41, 0.5);
	outline-offset: 2px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'home']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="kp-dashboard">
	<header class="kp-header">
		<div class="kp-arena">
			<div class="kp-arena-icon">
				<i class="fas fa-gavel"></i>
			</div>
			<div class="kp-arena-text">
				<h1>Arena <?= esc($nama_gelanggang ?? '-') ?></h1>
				<p><?= esc($event_name ?? 'Digital Pencak Silat') ?></p>
			</div>
		</div>
		<div class="kp-badge">
			<i class="fas fa-crown"></i> Ketua
		</div>
	</header>

	<div class="kp-card-grid">
		<!-- Tanding -->
		<div class="kp-card tanding">
			<div class="kp-card-header">
				<div class="kp-card-icon tanding">
					<i class="fas fa-fist-raised"></i>
				</div>
				<div class="kp-card-info">
					<h2 class="kp-card-title">Tanding</h2>
					<p class="kp-card-sub">Hukuman, jatuhan & verifikasi</p>
				</div>
			</div>
			<div class="kp-card-body">
				<a href="<?= base_url('ketua-pertandingan/tanding/monitoring') ?>" class="kp-btn kp-btn-primary">
					<i class="fas fa-chart-line"></i> Monitor
				</a>
				<a href="<?= base_url('ketua-pertandingan/tanding/dewan') ?>" class="kp-btn kp-btn-outline">
					<i class="fas fa-gavel"></i> Dewan
				</a>
			</div>
		</div>

		<!-- Seni -->
		<div class="kp-card seni">
			<div class="kp-card-header">
				<div class="kp-card-icon seni">
					<i class="fas fa-hand-sparkles"></i>
				</div>
				<div class="kp-card-info">
					<h2 class="kp-card-title">Seni</h2>
					<p class="kp-card-sub">Hukuman, akses & diskualifikasi</p>
				</div>
			</div>
			<div class="kp-card-body">
				<a href="<?= base_url('ketua-pertandingan/seni') ?>" class="kp-btn kp-btn-primary">
					<i class="fas fa-chart-line"></i> Monitor
				</a>
				<a href="<?= base_url('ketua-pertandingan/dewan-seni') ?>" class="kp-btn kp-btn-outline">
					<i class="fas fa-gavel"></i> Dewan
				</a>
			</div>
		</div>

		<!-- Daftar Nilai -->
		<div class="kp-card daftar">
			<div class="kp-card-header">
				<div class="kp-card-icon daftar">
					<i class="fas fa-list-ol"></i>
				</div>
				<div class="kp-card-info">
					<h2 class="kp-card-title">Daftar Nilai</h2>
					<p class="kp-card-sub">Rekap semua partai & penampilan</p>
				</div>
			</div>
			<div class="kp-card-body">
				<a href="<?= base_url('ketua-pertandingan/daftar-nilai-tanding') ?>" class="kp-btn kp-btn-primary">
					<i class="fas fa-table"></i> Daftar Nilai Tanding
				</a>
				<a href="<?= base_url('ketua-pertandingan/daftar-nilai-seni') ?>" class="kp-btn kp-btn-outline">
					<i class="fas fa-table"></i> Daftar Nilai Seni
				</a>
			</div>
		</div>
	</div>
</div>
<?= $this->endSection() ?>
