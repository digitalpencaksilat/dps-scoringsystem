<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<style>
	/* ========================================================================
	   Home Dashboard - Responsive Compact Layout
	   ======================================================================== */
	body {
		display: flex;
		flex-direction: column;
		height: 100vh;
		background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
	}

	.navbar-custom {
		flex-shrink: 0;
		background: rgba(0, 0, 0, 0.7) !important;
		border-bottom: 2px solid var(--brand-primary);
	}

	.dashboard-container {
		flex: 1;
		display: flex;
		flex-direction: column;
		overflow: hidden;
		padding: 1rem;
		gap: 1rem;
	}

	/* Header section */
	.dashboard-header {
		flex-shrink: 0;
		text-align: center;
		color: white;
		padding-bottom: 0.5rem;
		border-bottom: 1px solid rgba(255, 255, 255, 0.1);
	}

	.dashboard-header h1 {
		font-family: 'Oswald', sans-serif;
		font-size: clamp(1.2rem, 4vw, 1.75rem);
		font-weight: 700;
		margin: 0;
		text-transform: uppercase;
		letter-spacing: 1px;
		color: var(--brand-primary);
	}

	.dashboard-header p {
		font-size: clamp(0.75rem, 2.5vw, 0.95rem);
		color: rgba(255, 255, 255, 0.7);
		margin: 0.25rem 0 0 0;
	}

	/* Tab navigation */
	.nav-wrapper {
		flex-shrink: 0;
	}

	.nav-pills {
		background: rgba(255, 255, 255, 0.05);
		border-radius: 0.5rem;
		gap: 0.5rem;
		padding: 0.5rem !important;
		border: 1px solid rgba(255, 255, 255, 0.1);
	}

	.nav-link {
		background: rgba(255, 255, 255, 0.08);
		color: rgba(255, 255, 255, 0.7);
		border: 1px solid transparent;
		border-radius: 0.4rem;
		font-size: clamp(0.8rem, 2vw, 0.95rem);
		font-weight: 500;
		padding: 0.5rem 1rem !important;
		transition: all 0.25s ease;
		white-space: nowrap;
	}

	.nav-link:hover {
		background: rgba(255, 255, 255, 0.12);
		color: white;
		border-color: rgba(255, 255, 255, 0.2);
	}

	.nav-link.active {
		background: var(--brand-primary);
		color: white;
		border-color: var(--brand-primary);
		box-shadow: 0 4px 12px rgba(198, 0, 0, 0.3);
	}

	/* Tab content */
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

	/* Card wrapper */
	.tab-pane .card {
		flex: 1;
		background: rgba(255, 255, 255, 0.03);
		border: 1px solid rgba(255, 255, 255, 0.1);
		border-radius: 0.75rem;
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}

	.tab-pane .card-body {
		flex: 1;
		overflow-y: auto;
		overflow-x: hidden;
		padding: 0;
	}

	/* Table styling */
	.table-responsive {
		height: 100%;
		display: flex;
		flex-direction: column;
	}

	.table {
		color: rgba(255, 255, 255, 0.85);
		margin: 0;
		font-size: clamp(0.75rem, 2vw, 0.9rem);
	}

	.table thead {
		position: sticky;
		top: 0;
		background: rgba(198, 0, 0, 0.15);
		border-bottom: 2px solid var(--brand-primary);
		z-index: 10;
	}

	.table th {
		color: white;
		font-weight: 600;
		padding: 0.75rem !important;
		text-transform: uppercase;
		font-size: clamp(0.7rem, 1.5vw, 0.8rem);
		letter-spacing: 0.5px;
	}

	.table tbody tr {
		border-bottom: 1px solid rgba(255, 255, 255, 0.08);
		transition: background 0.2s ease;
	}

	.table tbody tr:hover {
		background: rgba(255, 255, 255, 0.05);
	}

	.table td {
		padding: 0.65rem 0.75rem !important;
		vertical-align: middle;
	}

	/* Badge styling */
	.badge {
		font-size: clamp(0.65rem, 1.5vw, 0.75rem);
		padding: 0.35em 0.6em !important;
		font-weight: 500;
	}

	/* Action buttons */
	.btn-detail {
		background: var(--brand-primary);
		color: white;
		border: none;
		border-radius: 0.4rem;
		padding: 0.45rem 0.85rem;
		font-size: clamp(0.7rem, 1.8vw, 0.85rem);
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		transition: all 0.2s ease;
		white-space: nowrap;
		cursor: pointer;
		text-decoration: none;
		display: inline-block;
	}

	.btn-detail:hover {
		background: var(--corner-red);
		transform: translateY(-1px);
		box-shadow: 0 4px 8px rgba(198, 0, 0, 0.3);
		color: white;
		text-decoration: none;
	}

	/* Empty state */
	.empty-state {
		display: flex;
		align-items: center;
		justify-content: center;
		height: 100%;
		color: rgba(255, 255, 255, 0.4);
		text-align: center;
		flex-direction: column;
		gap: 1rem;
	}

	.empty-state i {
		font-size: 3rem;
		opacity: 0.3;
	}

	/* Responsive adjustments */
	@media (max-width: 768px) {
		.dashboard-container {
			padding: 0.75rem;
			gap: 0.75rem;
		}

		.table th {
			padding: 0.5rem !important;
		}

		.table td {
			padding: 0.5rem !important;
		}

		.nav-link {
			padding: 0.4rem 0.8rem !important;
		}
	}

	@media (max-width: 576px) {
		.dashboard-container {
			padding: 0.5rem;
			gap: 0.5rem;
		}

		.dashboard-header {
			padding-bottom: 0.3rem;
		}

		.dashboard-header h1 {
			margin-bottom: 0.1rem;
		}

		.table th {
			padding: 0.4rem 0.3rem !important;
			font-size: 0.65rem;
		}

		.table td {
			padding: 0.4rem 0.3rem !important;
		}

		.btn-detail {
			padding: 0.4rem 0.6rem;
			font-size: 0.7rem;
		}

		.nav-link {
			padding: 0.35rem 0.6rem !important;
			font-size: 0.75rem;
		}
	}

	/* Landscape mode optimization */
	@media (orientation: landscape) and (max-height: 600px) {
		.dashboard-container {
			padding: 0.5rem;
			gap: 0.5rem;
		}

		.dashboard-header {
			padding-bottom: 0.25rem;
		}

		.dashboard-header h1 {
			font-size: 1.1rem;
			margin-bottom: 0;
		}

		.dashboard-header p {
			font-size: 0.7rem;
		}

		.nav-link {
			padding: 0.3rem 0.6rem !important;
			font-size: 0.75rem;
		}

		.table th {
			padding: 0.35rem 0.3rem !important;
			font-size: 0.65rem;
		}

		.table td {
			padding: 0.35rem 0.3rem !important;
			font-size: 0.75rem;
		}

		.btn-detail {
			padding: 0.35rem 0.5rem;
			font-size: 0.65rem;
		}
	}

	/* Scrollbar styling */
	.table-responsive::-webkit-scrollbar {
		width: 6px;
	}

	.table-responsive::-webkit-scrollbar-track {
		background: rgba(255, 255, 255, 0.05);
		border-radius: 3px;
	}

	.table-responsive::-webkit-scrollbar-thumb {
		background: rgba(198, 0, 0, 0.4);
		border-radius: 3px;
	}

	.table-responsive::-webkit-scrollbar-thumb:hover {
		background: rgba(198, 0, 0, 0.6);
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
<div class="dashboard-container">
	<!-- Header -->
	<div class="dashboard-header">
		<h1>Arena <?= esc($nama_gelanggang ?? '') ?></h1>
		<p><?= esc($event_name ?? '') ?></p>
	</div>

	<!-- Tab Navigation -->
	<div class="nav-wrapper">
		<ul class="nav nav-pills nav-fill" role="tablist">
			<li class="nav-item" role="presentation">
				<button class="nav-link active" id="tab-seni" data-bs-toggle="tab" data-bs-target="#pane-seni" type="button" role="tab" aria-controls="pane-seni" aria-selected="true">
					<i class="fas fa-theater-masks me-2"></i> Artistic / Seni
				</button>
			</li>
			<li class="nav-item" role="presentation">
				<button class="nav-link" id="tab-tanding" data-bs-toggle="tab" data-bs-target="#pane-tanding" type="button" role="tab" aria-controls="pane-tanding" aria-selected="false">
					<i class="fas fa-fist-raised me-2"></i> Tanding
				</button>
			</li>
		</ul>
	</div>

	<!-- Tab Content -->
	<div class="tab-content">
		<!-- Seni Tab -->
		<div class="tab-pane active" id="pane-seni" role="tabpanel" aria-labelledby="tab-seni">
			<div class="card">
				<div class="card-body table-responsive">
					<?php if (!empty($seni)): ?>
						<table class="table table-hover mb-0">
							<thead>
								<tr>
									<th width="5%">#</th>
									<th width="12%">Penampilan</th>
									<th width="35%">Tanggal & Waktu</th>
									<th width="35%">Keterangan</th>
									<th width="13%" class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php $i = 1; ?>
								<?php foreach ($seni as $data): ?>
									<tr>
										<td><?= $i++ ?></td>
										<td>
											<span class="badge bg-info">
												<?= esc($data->jumlah_penampilan ?? 0) ?>
											</span>
										</td>
										<td>
											<small>
												<?= esc($data->tanggal_formatted ?? '') ?><br>
												<strong><?= esc($data->jam_mulai_formatted ?? '') ?> - <?= esc($data->jam_selesai_formatted ?? '') ?></strong>
											</small>
										</td>
										<td>
											<small><?= esc($data->keterangan_jadwal ?? $data->keterangan ?? '-') ?></small>
										</td>
										<td class="text-center">
											<a href="<?= base_url('sekretaris-pertandingan/jadwal-seni/' . ($data->id_jadwal_seni ?? '')) ?>" class="btn-detail">
												Detail
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else: ?>
						<div class="empty-state">
							<i class="fas fa-inbox"></i>
							<p>Tidak ada penampilan seni yang dijadwalkan</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Tanding Tab -->
		<div class="tab-pane" id="pane-tanding" role="tabpanel" aria-labelledby="tab-tanding">
			<div class="card">
				<div class="card-body table-responsive">
					<?php if (!empty($tanding)): ?>
						<table class="table table-hover mb-0">
							<thead>
								<tr>
									<th width="5%">#</th>
									<th width="12%">Partai</th>
									<th width="35%">Tanggal & Waktu</th>
									<th width="35%">Keterangan</th>
									<th width="13%" class="text-center">Aksi</th>
								</tr>
							</thead>
							<tbody>
								<?php $i = 1; ?>
								<?php foreach ($tanding as $data): ?>
									<tr>
										<td><?= $i++ ?></td>
										<td>
											<span class="badge bg-warning text-dark">
												<?= esc($data->jumlah_partai ?? 0) ?>
											</span>
										</td>
										<td>
											<small>
												<?= esc($data->tanggal_formatted ?? '') ?><br>
												<strong><?= esc($data->jam_mulai_formatted ?? '') ?> - <?= esc($data->jam_selesai_formatted ?? '') ?></strong>
											</small>
										</td>
										<td>
											<small><?= esc($data->keterangan_jadwal ?? '-') ?></small>
										</td>
										<td class="text-center">
											<a href="<?= base_url('sekretaris-pertandingan/jadwal-tanding/' . ($data->id_jadwal_tanding ?? '')) ?>" class="btn-detail">
												Detail
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else: ?>
						<div class="empty-state">
							<i class="fas fa-inbox"></i>
							<p>Tidak ada pertandingan yang dijadwalkan</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<?= $this->endSection() ?>
