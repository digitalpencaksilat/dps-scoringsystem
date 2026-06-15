<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
/* ========================================================================
   Juri Home — 100vh Compact, Card-Based Selection
   ======================================================================== */
:root {
	--space-2: 0.5rem;
	--space-3: 0.75rem;
	--space-4: 1rem;
	--space-5: 1.5rem;
	--radius-md: 0.5rem;
	--radius-lg: 0.75rem;
	--radius-xl: 1rem;
	--surface-1: rgba(255, 255, 255, 0.03);
	--surface-2: rgba(255, 255, 255, 0.06);
	--border-subtle: rgba(255, 255, 255, 0.08);
	--text-primary: rgba(255, 255, 255, 0.95);
	--text-secondary: rgba(255, 255, 255, 0.65);
	--text-tertiary: rgba(255, 255, 255, 0.45);
	--accent-tanding: #d90429;
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
		radial-gradient(ellipse at top left, rgba(217, 4, 41, 0.08) 0%, transparent 50%),
		radial-gradient(ellipse at bottom right, rgba(197, 160, 23, 0.06) 0%, transparent 50%),
		linear-gradient(180deg, #0a0e13 0%, #0f1419 100%);
	color: var(--text-primary);
}

.navbar-custom {
	flex-shrink: 0;
	backdrop-filter: blur(12px);
	background: rgba(10, 14, 19, 0.85) !important;
}

/* Container */
.juri-home-container {
	flex: 1;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	padding: clamp(1rem, 3vw, 2rem);
	overflow-y: auto;
	max-width: 1100px;
	width: 100%;
	margin: 0 auto;
}

/* Header */
.home-header {
	text-align: center;
	margin-bottom: clamp(1.5rem, 4vw, 2.5rem);
}

.home-header h1 {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.5rem, 4vw, 2.25rem);
	font-weight: 700;
	margin: 0 0 0.5rem 0;
	text-transform: uppercase;
	letter-spacing: 1px;
	background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.7) 100%);
	-webkit-background-clip: text;
	-webkit-text-fill-color: transparent;
	background-clip: text;
}

.home-header p {
	font-size: clamp(0.85rem, 2vw, 1rem);
	color: var(--text-secondary);
	margin: 0;
}

/* Cards Grid */
.category-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(min(100%, 380px), 1fr));
	gap: clamp(1rem, 3vw, 1.5rem);
	width: 100%;
}

.category-card {
	background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-1) 100%);
	border: 1px solid var(--border-subtle);
	border-radius: var(--radius-xl);
	overflow: hidden;
	transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
	position: relative;
}

.category-card::before {
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

.category-card:hover {
	transform: translateY(-4px);
	border-color: rgba(255, 255, 255, 0.15);
	box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
}

.category-card:hover::before {
	opacity: 1;
}

.category-card.tanding {
	color: var(--accent-tanding);
}

.category-card.seni {
	color: var(--accent-seni);
}

/* Card Header */
.card-header-juri {
	display: flex;
	align-items: center;
	gap: var(--space-4);
	padding: clamp(1rem, 3vw, 1.5rem);
	border-bottom: 1px solid var(--border-subtle);
}

.category-icon {
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

.category-icon::before {
	content: '';
	position: absolute;
	inset: 0;
	background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, transparent 100%);
	opacity: 0;
	transition: opacity 0.3s ease;
}

.category-card:hover .category-icon::before {
	opacity: 1;
}

.category-icon.tanding {
	background: linear-gradient(135deg, #d90429 0%, #b90422 100%);
}

.category-icon.seni {
	background: linear-gradient(135deg, #c5a017 0%, #9a7d12 100%);
}

.category-info {
	flex: 1;
	min-width: 0;
}

.category-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.1rem, 3vw, 1.4rem);
	font-weight: 700;
	margin: 0;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: var(--text-primary);
}

.category-subtitle {
	font-size: clamp(0.75rem, 1.8vw, 0.85rem);
	color: var(--text-secondary);
	margin: 2px 0 0 0;
}

/* Card Body */
.card-body-juri {
	padding: clamp(1rem, 3vw, 1.5rem);
	display: flex;
	flex-direction: column;
	gap: var(--space-3);
}

/* Mode Buttons */
.mode-btn {
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

.mode-btn::before {
	content: '';
	position: absolute;
	inset: 0;
	background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
	opacity: 0;
	transition: opacity 0.2s ease;
}

.mode-btn:hover::before {
	opacity: 1;
}

.mode-btn i {
	font-size: clamp(0.9rem, 2.2vw, 1rem);
}

/* Primary mode button */
.mode-btn-primary {
	background: currentColor;
	color: white;
	border-color: currentColor;
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.category-card.tanding .mode-btn-primary {
	background: linear-gradient(135deg, #d90429 0%, #b90422 100%);
	border-color: #d90429;
}

.category-card.seni .mode-btn-primary {
	background: linear-gradient(135deg, #c5a017 0%, #9a7d12 100%);
	border-color: #c5a017;
}

.mode-btn-primary:hover {
	transform: translateY(-2px);
	box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
	color: white;
	text-decoration: none;
}

/* Outline mode button */
.mode-btn-outline {
	background: var(--surface-1);
	color: var(--text-primary);
	border-color: var(--border-subtle);
}

.mode-btn-outline:hover {
	background: var(--surface-2);
	border-color: rgba(255, 255, 255, 0.2);
	color: var(--text-primary);
	text-decoration: none;
}

/* Responsive */
@media (max-width: 768px) {
	.category-grid {
		grid-template-columns: 1fr;
	}

	.home-header {
		margin-bottom: 1.5rem;
	}
}

@media (max-width: 576px) {
	.juri-home-container {
		padding: 1rem;
	}

	.card-header-juri {
		padding: 1rem;
	}

	.card-body-juri {
		padding: 1rem;
	}
}

/* Landscape device */
@media (orientation: landscape) and (max-height: 600px) {
	.juri-home-container {
		padding: 0.75rem;
	}

	.home-header {
		margin-bottom: 1rem;
	}

	.home-header h1 {
		font-size: 1.25rem;
		margin-bottom: 0.25rem;
	}

	.home-header p {
		font-size: 0.8rem;
	}

	.category-grid {
		gap: 0.75rem;
	}

	.card-header-juri,
	.card-body-juri {
		padding: 0.75rem;
	}

	.category-icon {
		width: 2.5rem;
		height: 2.5rem;
		font-size: 1rem;
	}

	.category-title {
		font-size: 1rem;
	}

	.category-subtitle {
		font-size: 0.7rem;
	}

	.mode-btn {
		padding: 0.6rem 1rem;
		font-size: 0.8rem;
	}
}

/* Scrollbar */
.juri-home-container::-webkit-scrollbar {
	width: 6px;
}

.juri-home-container::-webkit-scrollbar-track {
	background: transparent;
}

.juri-home-container::-webkit-scrollbar-thumb {
	background: rgba(255, 255, 255, 0.1);
	border-radius: 3px;
}

.juri-home-container::-webkit-scrollbar-thumb:hover {
	background: rgba(255, 255, 255, 0.15);
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
	.category-card,
	.mode-btn,
	.category-icon::before,
	.mode-btn::before {
		transition: none !important;
	}

	.category-card:hover,
	.mode-btn-primary:hover {
		transform: none;
	}
}

.mode-btn:focus-visible,
.category-card:focus-within {
	outline: 2px solid rgba(217, 4, 41, 0.5);
	outline-offset: 2px;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'juri', 'nav_active' => 'home']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="juri-home-container">
	<!-- Header -->
	<header class="home-header">
		<h1>Pilih Kategori Penilaian</h1>
		<p>Silahkan pilih jenis penilaian yang akan dilakukan</p>
	</header>

	<!-- Category Grid -->
	<div class="category-grid">
		<!-- Tanding Card -->
		<div class="category-card tanding">
			<div class="card-header-juri">
				<div class="category-icon tanding">
					<i class="fas fa-fist-raised"></i>
				</div>
				<div class="category-info">
					<h2 class="category-title">Tanding</h2>
					<p class="category-subtitle">Penilaian pertandingan (fight)</p>
				</div>
			</div>
			<div class="card-body-juri">
				<a href="<?= base_url('juri/tanding/dark') ?>" class="mode-btn mode-btn-primary">
					<i class="fas fa-moon"></i>
					<span>Dark Mode</span>
				</a>
				<a href="<?= base_url('juri/tanding/light') ?>" class="mode-btn mode-btn-outline">
					<i class="fas fa-sun"></i>
					<span>Light Mode</span>
				</a>
			</div>
		</div>

		<!-- Seni Card -->
		<div class="category-card seni">
			<div class="card-header-juri">
				<div class="category-icon seni">
					<i class="fas fa-hand-sparkles"></i>
				</div>
				<div class="category-info">
					<h2 class="category-title">Seni</h2>
					<p class="category-subtitle">Penilaian penampilan seni (artistic)</p>
				</div>
			</div>
			<div class="card-body-juri">
				<a href="<?= base_url('juri/seni/sederhana') ?>" class="mode-btn mode-btn-primary">
					<i class="fas fa-list"></i>
					<span>Sederhana</span>
				</a>
				<a href="<?= base_url('juri/seni/terperinci') ?>" class="mode-btn mode-btn-outline">
					<i class="fas fa-table-cells"></i>
					<span>Terperinci</span>
				</a>
			</div>
		</div>
	</div>
</div>
<?= $this->endSection() ?>
