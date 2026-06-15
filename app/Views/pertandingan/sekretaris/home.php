<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<style>
	/* ========================================================================
	   DPS Sekretaris Dashboard — UI/UX Refined
	   Design tokens: 8px spacing grid · clamp() for fluid typography
	   ======================================================================== */
	:root {
		--space-1: 0.25rem;
		--space-2: 0.5rem;
		--space-3: 0.75rem;
		--space-4: 1rem;
		--space-5: 1.5rem;
		--space-6: 2rem;
		--radius-sm: 0.375rem;
		--radius-md: 0.5rem;
		--radius-lg: 0.75rem;
		--radius-xl: 1rem;
		--surface-0: #0f1419;
		--surface-1: rgba(255, 255, 255, 0.03);
		--surface-2: rgba(255, 255, 255, 0.06);
		--surface-3: rgba(255, 255, 255, 0.09);
		--border-subtle: rgba(255, 255, 255, 0.08);
		--border-default: rgba(255, 255, 255, 0.12);
		--text-primary: rgba(255, 255, 255, 0.95);
		--text-secondary: rgba(255, 255, 255, 0.65);
		--text-tertiary: rgba(255, 255, 255, 0.45);
		--accent-seni: #c5a017;
		--accent-tanding: #c60000;
		--shadow-card: 0 4px 24px rgba(0, 0, 0, 0.25);
		--shadow-hover: 0 8px 32px rgba(0, 0, 0, 0.4);
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
	.dashboard-container {
		flex: 1;
		display: flex;
		flex-direction: column;
		overflow: hidden;
		padding: clamp(0.75rem, 2vw, 1.5rem);
		gap: clamp(0.75rem, 1.5vw, 1.25rem);
		max-width: 1600px;
		width: 100%;
		margin: 0 auto;
	}

	/* ========================================================================
	   Header Bar — Arena info + summary stats
	   ======================================================================== */
	.dashboard-header {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: var(--space-4);
		flex-wrap: wrap;
	}

	.arena-info {
		display: flex;
		align-items: center;
		gap: var(--space-3);
		min-width: 0;
	}

	.arena-icon {
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

	.arena-text {
		min-width: 0;
	}

	.arena-text h1 {
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

	.arena-text p {
		font-size: clamp(0.7rem, 1.8vw, 0.85rem);
		color: var(--text-secondary);
		margin: 0;
		line-height: 1.3;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
	}

	/* Summary stats chips */
	.stats-row {
		display: flex;
		gap: var(--space-2);
		flex-shrink: 0;
	}

	.stat-chip {
		display: flex;
		align-items: center;
		gap: var(--space-2);
		padding: var(--space-2) var(--space-3);
		background: var(--surface-2);
		border: 1px solid var(--border-subtle);
		border-radius: var(--radius-md);
		min-width: 90px;
	}

	.stat-chip-icon {
		width: 1.75rem;
		height: 1.75rem;
		border-radius: var(--radius-sm);
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 0.85rem;
		flex-shrink: 0;
	}

	.stat-chip.seni .stat-chip-icon {
		background: rgba(197, 160, 23, 0.15);
		color: var(--accent-seni);
	}

	.stat-chip.tanding .stat-chip-icon {
		background: rgba(198, 0, 0, 0.15);
		color: var(--accent-tanding);
	}

	.stat-chip-text {
		display: flex;
		flex-direction: column;
		line-height: 1;
	}

	.stat-chip-value {
		font-family: 'Oswald', sans-serif;
		font-size: clamp(1rem, 2.2vw, 1.25rem);
		font-weight: 700;
		color: var(--text-primary);
	}

	.stat-chip-label {
		font-size: clamp(0.6rem, 1.3vw, 0.7rem);
		color: var(--text-secondary);
		text-transform: uppercase;
		letter-spacing: 0.5px;
		margin-top: 2px;
	}

	/* ========================================================================
	   Tab Navigation — Segmented control style
	   ======================================================================== */
	.tab-control-row {
		flex-shrink: 0;
		display: flex;
		align-items: center;
		gap: var(--space-3);
		flex-wrap: wrap;
	}

	.nav-pills {
		background: var(--surface-1);
		border: 1px solid var(--border-subtle);
		border-radius: var(--radius-md);
		padding: 4px !important;
		gap: 2px;
		flex: 0 0 auto;
	}

	.nav-pills .nav-link {
		background: transparent;
		color: var(--text-secondary);
		border: none;
		border-radius: var(--radius-sm);
		font-size: clamp(0.8rem, 1.7vw, 0.9rem);
		font-weight: 500;
		padding: var(--space-2) var(--space-4) !important;
		transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
		display: flex;
		align-items: center;
		gap: var(--space-2);
		white-space: nowrap;
	}

	.nav-pills .nav-link:hover {
		color: var(--text-primary);
		background: var(--surface-2);
	}

	.nav-pills .nav-link.active {
		background: var(--surface-3);
		color: var(--text-primary);
		box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
	}

	.nav-pills .nav-link.active.tab-seni {
		box-shadow: 0 0 0 1px rgba(197, 160, 23, 0.3), 0 2px 8px rgba(0, 0, 0, 0.3);
	}

	.nav-pills .nav-link.active.tab-seni::before {
		content: '';
		display: inline-block;
		width: 6px;
		height: 6px;
		border-radius: 50%;
		background: var(--accent-seni);
		box-shadow: 0 0 8px var(--accent-seni);
	}

	.nav-pills .nav-link.active.tab-tanding {
		box-shadow: 0 0 0 1px rgba(198, 0, 0, 0.3), 0 2px 8px rgba(0, 0, 0, 0.3);
	}

	.nav-pills .nav-link.active.tab-tanding::before {
		content: '';
		display: inline-block;
		width: 6px;
		height: 6px;
		border-radius: 50%;
		background: var(--accent-tanding);
		box-shadow: 0 0 8px var(--accent-tanding);
	}

	/* Search bar */
	.search-wrapper {
		flex: 1;
		min-width: 200px;
		max-width: 400px;
		position: relative;
	}

	.search-input {
		width: 100%;
		background: var(--surface-1);
		border: 1px solid var(--border-subtle);
		border-radius: var(--radius-md);
		color: var(--text-primary);
		padding: var(--space-2) var(--space-3) var(--space-2) 2.5rem;
		font-size: clamp(0.8rem, 1.7vw, 0.9rem);
		transition: all 0.2s ease;
	}

	.search-input::placeholder {
		color: var(--text-tertiary);
	}

	.search-input:focus {
		outline: none;
		border-color: rgba(198, 0, 0, 0.4);
		background: var(--surface-2);
		box-shadow: 0 0 0 3px rgba(198, 0, 0, 0.1);
	}

	.search-icon {
		position: absolute;
		left: var(--space-3);
		top: 50%;
		transform: translateY(-50%);
		color: var(--text-tertiary);
		font-size: 0.85rem;
		pointer-events: none;
	}

	/* ========================================================================
	   Tab Content
	   ======================================================================== */
	.tab-content {
		flex: 1;
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}

	.tab-pane {
		height: 100%;
		display: none;
		flex-direction: column;
		overflow: hidden;
	}

	.tab-pane.active {
		display: flex;
	}

	/* ========================================================================
	   Card Grid Layout
	   ======================================================================== */
	.schedule-grid {
		flex: 1;
		overflow-y: auto;
		overflow-x: hidden;
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
		gap: clamp(0.75rem, 1.5vw, 1rem);
		padding: 4px;
		align-content: start;
	}

	.schedule-card {
		background: linear-gradient(135deg, var(--surface-2) 0%, var(--surface-1) 100%);
		border: 1px solid var(--border-subtle);
		border-radius: var(--radius-lg);
		padding: var(--space-4);
		display: flex;
		flex-direction: column;
		gap: var(--space-3);
		transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
		cursor: pointer;
		text-decoration: none;
		color: inherit;
		position: relative;
		overflow: hidden;
		min-height: 180px;
	}

	.schedule-card::before {
		content: '';
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		height: 3px;
		background: linear-gradient(90deg, transparent, currentColor, transparent);
		opacity: 0;
		transition: opacity 0.25s ease;
	}

	.schedule-card:hover {
		transform: translateY(-2px);
		border-color: var(--border-default);
		box-shadow: var(--shadow-hover);
		text-decoration: none;
		color: inherit;
	}

	.schedule-card:hover::before {
		opacity: 1;
	}

	.schedule-card.seni {
		color: var(--accent-seni);
	}

	.schedule-card.seni:hover {
		border-color: rgba(197, 160, 23, 0.3);
	}

	.schedule-card.tanding {
		color: var(--accent-tanding);
	}

	.schedule-card.tanding:hover {
		border-color: rgba(198, 0, 0, 0.3);
	}

	/* Card header — index + count */
	.card-header-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: var(--space-2);
	}

	.card-index {
		font-family: 'Oswald', sans-serif;
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--text-tertiary);
		letter-spacing: 1px;
	}

	.card-count {
		display: flex;
		align-items: baseline;
		gap: 4px;
		padding: 4px var(--space-2);
		background: var(--surface-2);
		border-radius: var(--radius-sm);
		border: 1px solid var(--border-subtle);
	}

	.card-count-num {
		font-family: 'Oswald', sans-serif;
		font-size: 0.95rem;
		font-weight: 700;
		color: var(--text-primary);
	}

	.card-count-label {
		font-size: 0.65rem;
		color: var(--text-tertiary);
		text-transform: uppercase;
		letter-spacing: 0.5px;
	}

	/* Card body — date + time */
	.card-datetime {
		display: flex;
		flex-direction: column;
		gap: var(--space-1);
		padding: var(--space-3) 0;
		border-top: 1px solid var(--border-subtle);
		border-bottom: 1px solid var(--border-subtle);
	}

	.card-date {
		font-size: clamp(0.85rem, 1.8vw, 0.95rem);
		font-weight: 600;
		color: var(--text-primary);
		display: flex;
		align-items: center;
		gap: var(--space-2);
	}

	.card-date i {
		color: currentColor;
		font-size: 0.85rem;
	}

	.card-time {
		font-family: 'Oswald', sans-serif;
		font-size: clamp(1.1rem, 2.5vw, 1.4rem);
		font-weight: 700;
		letter-spacing: 1px;
		color: currentColor;
		display: flex;
		align-items: center;
		gap: var(--space-2);
	}

	.card-time i {
		font-size: 0.85rem;
	}

	/* Card description */
	.card-description {
		font-size: clamp(0.75rem, 1.6vw, 0.85rem);
		color: var(--text-secondary);
		line-height: 1.5;
		flex: 1;
		display: -webkit-box;
		-webkit-line-clamp: 2;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}

	.card-description.empty {
		color: var(--text-tertiary);
		font-style: italic;
	}

	/* Card footer — action button */
	.card-footer-row {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: var(--space-2);
		padding-top: var(--space-2);
	}

	.card-action {
		font-size: clamp(0.75rem, 1.6vw, 0.85rem);
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		color: currentColor;
		display: flex;
		align-items: center;
		gap: var(--space-2);
		transition: gap 0.2s ease;
	}

	.schedule-card:hover .card-action {
		gap: 0.6rem;
	}

	.card-action i {
		transition: transform 0.2s ease;
	}

	.schedule-card:hover .card-action i {
		transform: translateX(2px);
	}

	/* ========================================================================
	   Empty State
	   ======================================================================== */
	.empty-state {
		flex: 1;
		display: flex;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		gap: var(--space-3);
		padding: var(--space-6);
		text-align: center;
	}

	.empty-state-icon {
		width: clamp(4rem, 10vw, 6rem);
		height: clamp(4rem, 10vw, 6rem);
		border-radius: 50%;
		background: var(--surface-1);
		border: 1px solid var(--border-subtle);
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: clamp(1.5rem, 4vw, 2.25rem);
		color: var(--text-tertiary);
		margin-bottom: var(--space-2);
	}

	.empty-state-title {
		font-size: clamp(1rem, 2.2vw, 1.15rem);
		font-weight: 600;
		color: var(--text-primary);
		margin: 0;
	}

	.empty-state-description {
		font-size: clamp(0.8rem, 1.7vw, 0.9rem);
		color: var(--text-secondary);
		margin: 0;
		max-width: 360px;
	}

	/* ========================================================================
	   No-results state (when search has no match)
	   ======================================================================== */
	.no-results {
		display: none;
		flex: 1;
		align-items: center;
		justify-content: center;
		flex-direction: column;
		gap: var(--space-2);
		padding: var(--space-5);
		text-align: center;
		color: var(--text-tertiary);
	}

	.no-results.show {
		display: flex;
	}

	/* ========================================================================
	   Custom Scrollbar
	   ======================================================================== */
	.schedule-grid::-webkit-scrollbar {
		width: 8px;
	}

	.schedule-grid::-webkit-scrollbar-track {
		background: transparent;
	}

	.schedule-grid::-webkit-scrollbar-thumb {
		background: var(--surface-3);
		border-radius: 4px;
	}

	.schedule-grid::-webkit-scrollbar-thumb:hover {
		background: rgba(255, 255, 255, 0.15);
	}

	/* ========================================================================
	   Responsive — Mobile, Tablet, Landscape Scoring Devices
	   ======================================================================== */
	@media (max-width: 768px) {
		.dashboard-header {
			gap: var(--space-3);
		}

		.stats-row {
			width: 100%;
			justify-content: space-between;
		}

		.stat-chip {
			flex: 1;
			min-width: 0;
			padding: var(--space-2);
		}

		.tab-control-row {
			flex-direction: column;
			align-items: stretch;
			gap: var(--space-2);
		}

		.nav-pills {
			width: 100%;
		}

		.search-wrapper {
			max-width: 100%;
		}

		.schedule-grid {
			grid-template-columns: 1fr;
		}
	}

	@media (max-width: 576px) {
		.arena-text h1 {
			font-size: 1rem;
		}

		.schedule-card {
			padding: var(--space-3);
			min-height: auto;
		}

		.card-time {
			font-size: 1.1rem;
		}
	}

	/* Landscape device penilaian (tablet horizontal pendek) */
	@media (orientation: landscape) and (max-height: 600px) {
		.dashboard-container {
			padding: var(--space-2);
			gap: var(--space-2);
		}

		.dashboard-header {
			padding: 0;
		}

		.arena-icon {
			width: 2rem;
			height: 2rem;
			font-size: 0.9rem;
		}

		.arena-text h1 {
			font-size: 0.95rem;
		}

		.arena-text p {
			font-size: 0.7rem;
		}

		.stat-chip {
			padding: 4px var(--space-2);
			min-width: 70px;
		}

		.stat-chip-value {
			font-size: 1rem;
		}

		.schedule-grid {
			grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr));
			gap: var(--space-2);
		}

		.schedule-card {
			padding: var(--space-3);
			min-height: auto;
			gap: var(--space-2);
		}

		.card-datetime {
			padding: var(--space-2) 0;
		}

		.card-time {
			font-size: 1rem;
		}
	}

	/* Reduce motion for users who prefer it */
	@media (prefers-reduced-motion: reduce) {
		.schedule-card,
		.nav-link,
		.search-input,
		.card-action,
		.card-action i {
			transition: none !important;
		}

		.schedule-card:hover {
			transform: none;
		}
	}

	/* Focus indicators for keyboard nav */
	.schedule-card:focus-visible {
		outline: 2px solid var(--accent-tanding);
		outline-offset: 2px;
	}

	.nav-pills .nav-link:focus-visible,
	.search-input:focus-visible {
		outline: 2px solid rgba(198, 0, 0, 0.5);
		outline-offset: 2px;
	}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-2">
	<div class="container-fluid">
		<a class="navbar-brand d-flex align-items-center" href="<?= base_url('sekretaris-pertandingan') ?>">
			<img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
				class="navbar-brand-img"
				alt="Logo"
				width="120"
				onerror="this.style.display='none'">
		</a>

		<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navigation">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
				<li class="nav-item">
					<a class="nav-link" href="<?= base_url('sekretaris-pertandingan') ?>">
						<i class="fas fa-home me-1"></i> Dashboard
					</a>
				</li>

				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="timerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="fas fa-stopwatch me-1"></i> Control Timer
					</a>
					<ul class="dropdown-menu dropdown-menu-dark border-0 shadow" aria-labelledby="timerDropdown" style="background: #222;">
						<li><a class="dropdown-item py-2" href="<?= base_url('sekretaris-pertandingan/timer-tanding') ?>" target="_blank">Timer Tanding</a></li>
						<li><hr class="dropdown-divider bg-secondary"></li>
						<li><a class="dropdown-item py-2" href="<?= base_url('sekretaris-pertandingan/timer-seni') ?>" target="_blank">Timer Seni (Pool)</a></li>
						<li><a class="dropdown-item py-2" href="<?= base_url('sekretaris-pertandingan/timer-seni/battle') ?>" target="_blank">Timer Seni (Battle)</a></li>
					</ul>
				</li>
			</ul>

			<ul class="nav navbar-nav ms-auto align-items-center">
				<li class="nav-item d-none d-lg-block">
					<span class="badge bg-dark border border-secondary text-uppercase py-2 px-3">
						<i class="fas fa-user-shield me-1 text-danger"></i> Secretary
					</span>
				</li>
				<li class="nav-item">
					<a class="nav-link btn-logout-brand shadow-sm" href="<?= base_url('perangkat-pertandingan/logout') ?>">
						<i class="fas fa-power-off me-1"></i> LOGOUT
					</a>
				</li>
			</ul>
		</div>
	</div>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
	$totalSeni = 0;
	foreach ($seni ?? [] as $row) {
		$totalSeni += (int) ($row->jumlah_penampilan ?? 0);
	}
	$totalTanding = 0;
	foreach ($tanding ?? [] as $row) {
		$totalTanding += (int) ($row->jumlah_partai ?? 0);
	}
?>
<div class="dashboard-container">
	<!-- Header: Arena Info + Summary Stats -->
	<header class="dashboard-header">
		<div class="arena-info">
			<div class="arena-icon">
				<i class="fas fa-map-marker-alt"></i>
			</div>
			<div class="arena-text">
				<h1>Arena <?= esc($nama_gelanggang ?? '-') ?></h1>
				<p><?= esc($event_name ?? 'Digital Pencak Silat') ?></p>
			</div>
		</div>

		<div class="stats-row">
			<div class="stat-chip seni">
				<div class="stat-chip-icon">
					<i class="fas fa-theater-masks"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= $totalSeni ?></span>
					<span class="stat-chip-label">Penampilan</span>
				</div>
			</div>
			<div class="stat-chip tanding">
				<div class="stat-chip-icon">
					<i class="fas fa-fist-raised"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= $totalTanding ?></span>
					<span class="stat-chip-label">Partai</span>
				</div>
			</div>
		</div>
	</header>

	<!-- Tab + Search Row -->
	<div class="tab-control-row">
		<ul class="nav nav-pills" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link tab-seni active" id="tab-seni" data-bs-toggle="tab" data-bs-target="#pane-seni" type="button" role="tab" aria-controls="pane-seni" aria-selected="true">
					<i class="fas fa-theater-masks"></i> Seni
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link tab-tanding" id="tab-tanding" data-bs-toggle="tab" data-bs-target="#pane-tanding" type="button" role="tab" aria-controls="pane-tanding" aria-selected="false">
					<i class="fas fa-fist-raised"></i> Tanding
				</button>
			</li>
		</ul>

		<div class="search-wrapper">
			<i class="fas fa-search search-icon" aria-hidden="true"></i>
			<input type="text" id="schedule-search" class="search-input" placeholder="Cari jadwal berdasarkan keterangan atau tanggal..." aria-label="Cari jadwal">
		</div>
	</div>

	<!-- Tab Content -->
	<div class="tab-content">
		<!-- Seni Tab -->
		<div class="tab-pane active" id="pane-seni" role="tabpanel" aria-labelledby="tab-seni">
			<?php if (!empty($seni)): ?>
				<div class="schedule-grid" id="grid-seni">
					<?php $i = 1; ?>
					<?php foreach ($seni as $data): ?>
						<?php
							$searchKey = strtolower(trim(
								($data->tanggal_formatted ?? '') . ' ' .
								($data->jam_mulai_formatted ?? '') . ' ' .
								($data->keterangan_jadwal ?? $data->keterangan ?? '')
							));
						?>
						<a href="<?= base_url('sekretaris-pertandingan/jadwal-seni/' . ($data->id_jadwal_seni ?? '')) ?>"
							class="schedule-card seni"
							data-search="<?= esc($searchKey) ?>">
							<div class="card-header-row">
								<span class="card-index">JADWAL #<?= str_pad($i++, 2, '0', STR_PAD_LEFT) ?></span>
								<div class="card-count">
									<span class="card-count-num"><?= esc($data->jumlah_penampilan ?? 0) ?></span>
									<span class="card-count-label">tampil</span>
								</div>
							</div>

							<div class="card-datetime">
								<div class="card-date">
									<i class="far fa-calendar-alt"></i>
									<span><?= esc($data->tanggal_formatted ?? '-') ?></span>
								</div>
								<div class="card-time">
									<i class="far fa-clock"></i>
									<span><?= esc($data->jam_mulai_formatted ?? '00:00') ?> – <?= esc($data->jam_selesai_formatted ?? '00:00') ?></span>
								</div>
							</div>

							<?php $ket = $data->keterangan_jadwal ?? $data->keterangan ?? ''; ?>
							<p class="card-description <?= empty($ket) ? 'empty' : '' ?>">
								<?= empty($ket) ? 'Tidak ada keterangan' : esc($ket) ?>
							</p>

							<div class="card-footer-row">
								<span class="card-action">
									Buka Detail <i class="fas fa-arrow-right"></i>
								</span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
				<div class="no-results" id="no-results-seni">
					<i class="fas fa-search fa-2x mb-2"></i>
					<p class="mb-0">Tidak ada jadwal seni yang cocok dengan pencarian</p>
				</div>
			<?php else: ?>
				<div class="empty-state">
					<div class="empty-state-icon">
						<i class="fas fa-theater-masks"></i>
					</div>
					<h3 class="empty-state-title">Belum Ada Jadwal Seni</h3>
					<p class="empty-state-description">
						Tidak ada penampilan seni yang dijadwalkan untuk arena ini saat ini.
					</p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Tanding Tab -->
		<div class="tab-pane" id="pane-tanding" role="tabpanel" aria-labelledby="tab-tanding">
			<?php if (!empty($tanding)): ?>
				<div class="schedule-grid" id="grid-tanding">
					<?php $i = 1; ?>
					<?php foreach ($tanding as $data): ?>
						<?php
							$searchKey = strtolower(trim(
								($data->tanggal_formatted ?? '') . ' ' .
								($data->jam_mulai_formatted ?? '') . ' ' .
								($data->keterangan_jadwal ?? '')
							));
						?>
						<a href="<?= base_url('sekretaris-pertandingan/jadwal-tanding/' . ($data->id_jadwal_tanding ?? '')) ?>"
							class="schedule-card tanding"
							data-search="<?= esc($searchKey) ?>">
							<div class="card-header-row">
								<span class="card-index">JADWAL #<?= str_pad($i++, 2, '0', STR_PAD_LEFT) ?></span>
								<div class="card-count">
									<span class="card-count-num"><?= esc($data->jumlah_partai ?? 0) ?></span>
									<span class="card-count-label">partai</span>
								</div>
							</div>

							<div class="card-datetime">
								<div class="card-date">
									<i class="far fa-calendar-alt"></i>
									<span><?= esc($data->tanggal_formatted ?? '-') ?></span>
								</div>
								<div class="card-time">
									<i class="far fa-clock"></i>
									<span><?= esc($data->jam_mulai_formatted ?? '00:00') ?> – <?= esc($data->jam_selesai_formatted ?? '00:00') ?></span>
								</div>
							</div>

							<?php $ket = $data->keterangan_jadwal ?? ''; ?>
							<p class="card-description <?= empty($ket) ? 'empty' : '' ?>">
								<?= empty($ket) ? 'Tidak ada keterangan' : esc($ket) ?>
							</p>

							<div class="card-footer-row">
								<span class="card-action">
									Buka Detail <i class="fas fa-arrow-right"></i>
								</span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
				<div class="no-results" id="no-results-tanding">
					<i class="fas fa-search fa-2x mb-2"></i>
					<p class="mb-0">Tidak ada jadwal tanding yang cocok dengan pencarian</p>
				</div>
			<?php else: ?>
				<div class="empty-state">
					<div class="empty-state-icon">
						<i class="fas fa-fist-raised"></i>
					</div>
					<h3 class="empty-state-title">Belum Ada Jadwal Tanding</h3>
					<p class="empty-state-description">
						Tidak ada pertandingan yang dijadwalkan untuk arena ini saat ini.
					</p>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
	(function() {
		'use strict';

		const searchInput = document.getElementById('schedule-search');
		if (!searchInput) return;

		// Filter cards by search query within the active tab only
		function filterCards() {
			const query = searchInput.value.toLowerCase().trim();
			const activePane = document.querySelector('.tab-pane.active');
			if (!activePane) return;

			const grid = activePane.querySelector('.schedule-grid');
			const noResults = activePane.querySelector('.no-results');
			if (!grid) return;

			const cards = grid.querySelectorAll('.schedule-card');
			let visibleCount = 0;

			cards.forEach(card => {
				const searchData = card.getAttribute('data-search') || '';
				const isMatch = query === '' || searchData.includes(query);
				card.style.display = isMatch ? '' : 'none';
				if (isMatch) visibleCount++;
			});

			if (noResults) {
				noResults.classList.toggle('show', visibleCount === 0 && query !== '');
				grid.style.display = (visibleCount === 0 && query !== '') ? 'none' : '';
			}
		}

		// Debounced input handler
		let searchTimer = null;
		searchInput.addEventListener('input', () => {
			clearTimeout(searchTimer);
			searchTimer = setTimeout(filterCards, 150);
		});

		// Re-apply filter when switching tabs
		document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
			tab.addEventListener('shown.bs.tab', filterCards);
		});

		// Keyboard shortcut: '/' to focus search
		document.addEventListener('keydown', (e) => {
			if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
				e.preventDefault();
				searchInput.focus();
			}
			if (e.key === 'Escape' && document.activeElement === searchInput) {
				searchInput.value = '';
				filterCards();
				searchInput.blur();
			}
		});
	})();
</script>
<?= $this->endSection() ?>
