<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-2">
	<div class="container">
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
			<ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
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
						<li>
							<hr class="dropdown-divider bg-secondary">
						</li>
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
<div class="container-fluid">
	<div class="row min-vh-75 align-content-center">
		<div class="col-md-12 text-center my-5">
			<h1 class="h2">Arena <?= esc($gelanggang->nama_gelanggang ?? '') ?></h1>
			<p><?= esc($event_name ?? '') ?></p>
		</div>
		<div class="col-12">
			<div class="nav-wrapper position-relative end-0">
				<ul class="nav nav-pills nav-pills-primary nav-fill p-1" role="tablist">
					<li class="nav-item">
						<a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#kategori_seni" role="tab" aria-controls="kategori_seni" aria-selected="true">
							Artistic / Seni
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#kategori_tanding" role="tab" aria-controls="kategori_tanding" aria-selected="false">
							Tanding
						</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="kategori_seni" role="tabpanel">
						<div class="card">
							<div class="card-body table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th>No</th>
											<th>Jumlah <br> Penampilan</th>
											<th>Tanggal <br>& Waktu</th>
											<th>Keterangan</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $i = 1; ?>
										<?php foreach ($seni ?? [] as $data) : ?>
											<tr>
												<td><?= $i++ ?></td>
												<td><?= esc($data->jumlah_penampilan ?? 0) ?></td>
												<td>
													<?= esc($data->tanggal_formatted ?? '') ?><br>
													<?= esc($data->jam_mulai_formatted ?? '') ?> - <?= esc($data->jam_selesai_formatted ?? '') ?>
												</td>
												<td><?= esc($data->keterangan_jadwal ?? $data->keterangan ?? '') ?></td>
												<td>
													<a class="btn btn-primary" href="<?= base_url('sekretaris-pertandingan/jadwal-seni/' . ($data->id_jadwal_seni ?? '')) ?>">
														Detail
													</a>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="kategori_tanding" role="tabpanel">
						<div class="card">
							<div class="card-body table-responsive">
								<table class="table table-responsive">
									<thead>
										<tr>
											<th>No</th>
											<th>Jumlah <br>Pertandingan</th>
											<th>Tanggal <br>& Waktu</th>
											<th>Keterangan</th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php $i = 1; ?>
										<?php foreach ($tanding ?? [] as $data) : ?>
											<tr>
												<td><?= $i++ ?></td>
												<td class="text-end"><?= esc($data->jumlah_partai ?? 0) ?></td>
												<td>
													<?= esc($data->tanggal_formatted ?? '') ?><br>
													<?= esc($data->jam_mulai_formatted ?? '') ?> - <?= esc($data->jam_selesai_formatted ?? '') ?>
												</td>
												<td><?= esc($data->keterangan_jadwal ?? '') ?></td>
												<td>
													<a class="btn btn-primary" href="<?= base_url('sekretaris-pertandingan/jadwal-tanding/' . ($data->id_jadwal_tanding ?? '')) ?>">
														Detail
													</a>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?= $this->endSection() ?>
