<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-2">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center" href="<?= base_url('sekretaris-pertandingan') ?>">
			<img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
				class="navbar-brand-img" alt="Logo" width="120"
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
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="fas fa-stopwatch me-1"></i> Control Timer
					</a>
					<ul class="dropdown-menu dropdown-menu-dark border-0 shadow" style="background: #222;">
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
<div class="container">
	<div class="row min-vh-75 pt-5">
		<div class="col-md-12 text-center mb-3 mt-5">
			<h1 class="h3">Gelanggang <?= esc($gelanggang->nama_gelanggang ?? '') ?></h1>
			<h4 style="margin:3px">Kategori Seni</h4>
			<p>
				<?= esc($jadwal_seni->tanggal_formatted ?? '') ?><br>
				<?= esc($jadwal_seni->jam_mulai_formatted ?? '') ?> - <?= esc($jadwal_seni->jam_selesai_formatted ?? '') ?><br>
				<?= esc($jadwal_seni->keterangan ?? '') ?>
			</p>
		</div>
		<div class="col-12">
			<div class="nav-wrapper position-relative end-0">
				<ul class="nav nav-pills nav-pills-primary nav-fill p-1" role="tablist">
					<li class="nav-item">
						<a class="nav-link h5 mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#battle_seni" role="tab" aria-controls="battle_seni" aria-selected="true">
							Battle System
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link h5 mb-0 px-0 py-1" data-bs-toggle="tab" href="#pool_seni" role="tab" aria-controls="pool_seni" aria-selected="false">
							Pool System
						</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="battle_seni" role="tabpanel">
						<div class="card">
							<div class="card-body table-responsive">
								<table class="table table-striped" id="tabelBattleSeni">
									<thead>
										<tr>
											<th>No</th>
											<th>Partai</th>
											<th>Kategori</th>
											<th>Babak</th>
											<th>Sudut Biru</th>
											<th>Sudut Merah</th>
											<th>Status</th>
											<th>Aksi</th>
										</tr>
									</thead>
									<tbody>
										<?php $i = 1; ?>
										<?php foreach ($data_detail_jadwal_seni_sistem_battle ?? [] as $row) : ?>
											<tr>
												<td><?= $i++ ?></td>
												<td><?= esc($row->nomor_partai ?? '') ?></td>
												<td class="text-capitalize">
													<?= esc(($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? '') . ' ' . ($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? '')) ?>
												</td>
												<td><?= esc(ucwords($row->babak_battle ?? '')) ?></td>
												<td>
													<?= esc($row->nama_peserta_biru ?? '-') ?>
													<?php if (!empty($row->nama_kontingen_biru)) : ?>
														<br><small class="text-muted"><?= esc($row->nama_kontingen_biru) ?></small>
													<?php endif; ?>
												</td>
												<td>
													<?= esc($row->nama_peserta_merah ?? '-') ?>
													<?php if (!empty($row->nama_kontingen_merah)) : ?>
														<br><small class="text-muted"><?= esc($row->nama_kontingen_merah) ?></small>
													<?php endif; ?>
												</td>
												<td>
													<?php
													$status = $row->status ?? 'belum_dimulai';
													$badgeClass = match ($status) {
														'sedang_berlangsung', 'sedang_tampil' => 'bg-success',
														'selesai' => 'bg-secondary',
														default => 'bg-warning text-dark',
													};
													$statusLabel = match ($status) {
														'sedang_berlangsung', 'sedang_tampil' => 'Berlangsung',
														'selesai' => 'Selesai',
														default => 'Belum Dimulai',
													};
													?>
													<span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
												</td>
												<td>
													<?php if ($status == 'belum_dimulai') : ?>
														<a href="<?= base_url('sekretaris-pertandingan/mulai-penampilan/' . ($row->id_penampilan_seni ?? '')) ?>"
															class="btn btn-sm btn-primary">Mulai</a>
													<?php elseif (in_array($status, ['sedang_berlangsung', 'sedang_tampil'])) : ?>
														<a href="<?= base_url('sekretaris-pertandingan/timer-seni/battle') ?>"
															class="btn btn-sm btn-success" target="_blank">Timer</a>
													<?php endif; ?>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="pool_seni" role="tabpanel">
						<div class="card">
							<div class="card-body table-responsive">
								<table class="table table-striped" id="tabelPoolSeni">
									<thead>
										<tr>
											<th>No</th>
											<th>Partai</th>
											<th>Kategori</th>
											<th>Pool</th>
											<th>Peserta</th>
											<th>Status</th>
											<th>Aksi</th>
										</tr>
									</thead>
									<tbody>
										<?php $i = 1; ?>
										<?php foreach ($data_detail_jadwal_seni_sistem_pool ?? [] as $row) : ?>
											<tr>
												<td><?= $i++ ?></td>
												<td><?= esc($row->nomor_partai ?? '') ?></td>
												<td class="text-capitalize">
													<?= esc(($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? '') . ' ' . ($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? '')) ?>
												</td>
												<td>Pool <?= esc($row->nomor_pool ?? '') ?></td>
												<td>
													<?= esc($row->nama_peserta ?? '-') ?>
													<?php if (!empty($row->nama_kontingen)) : ?>
														<br><small class="text-muted"><?= esc($row->nama_kontingen) ?></small>
													<?php endif; ?>
												</td>
												<td>
													<?php
													$status = $row->status ?? 'belum_dimulai';
													$badgeClass = match ($status) {
														'sedang_berlangsung', 'sedang_tampil' => 'bg-success',
														'selesai' => 'bg-secondary',
														default => 'bg-warning text-dark',
													};
													$statusLabel = match ($status) {
														'sedang_berlangsung', 'sedang_tampil' => 'Berlangsung',
														'selesai' => 'Selesai',
														default => 'Belum Dimulai',
													};
													?>
													<span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
												</td>
												<td>
													<?php if ($status == 'belum_dimulai') : ?>
														<a href="<?= base_url('sekretaris-pertandingan/mulai-penampilan/' . ($row->id_penampilan_seni ?? '')) ?>"
															class="btn btn-sm btn-primary">Mulai</a>
													<?php elseif (in_array($status, ['sedang_berlangsung', 'sedang_tampil'])) : ?>
														<a href="<?= base_url('sekretaris-pertandingan/timer-seni') ?>"
															class="btn btn-sm btn-success" target="_blank">Timer</a>
													<?php endif; ?>
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

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
	$('#tabelBattleSeni, #tabelPoolSeni').each(function() {
		$(this).DataTable({
			language: {
				paginate: { next: ">", previous: "<" }
			},
			autoWidth: false,
			paging: true,
			searching: true,
			ordering: true,
			info: true,
			responsive: true,
		});
	});
});
</script>
<?= $this->endSection() ?>
