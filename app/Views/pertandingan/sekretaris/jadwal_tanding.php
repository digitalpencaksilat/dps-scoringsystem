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
		<div class="col-md-12 text-center">
			<h1 class="h3 text-capitalize">Gelanggang <?= esc($gelanggang->nama_gelanggang ?? '') ?></h1>
			<h4>Kategori Tanding</h4>
			<p>
				<?= esc($jadwal_tanding->tanggal_formatted ?? '') ?><br>
				<?= esc($jadwal_tanding->jam_mulai_formatted ?? '') ?> - <?= esc($jadwal_tanding->jam_selesai_formatted ?? '') ?><br>
				<?= esc($jadwal_tanding->keterangan_jadwal ?? '') ?>
			</p>
		</div>
		<div class="col-md-12">
			<div class="card">
				<div class="card-body table-responsive">
					<table class="table table-striped" id="tabelDetailJadwalTanding">
						<thead>
							<tr>
								<th>No</th>
								<th>Partai</th>
								<th>Kelas</th>
								<th>Babak</th>
								<th>Sudut Biru</th>
								<th>Sudut Merah</th>
								<th>Status</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php $i = 1; ?>
							<?php foreach ($data_detail_jadwal_tanding ?? [] as $row) : ?>
								<tr>
									<td><?= $i++ ?></td>
									<td><?= esc($row->nomor_partai ?? '') ?></td>
									<td><?= esc($row->nama_kelas ?? '') ?></td>
									<td><?= esc($row->babak ?? '') ?></td>
									<td>
										<?= esc($row->nama_atlet_biru ?? '-') ?>
										<?php if (!empty($row->nama_kontingen_biru)) : ?>
											<br><small class="text-muted"><?= esc($row->nama_kontingen_biru) ?></small>
										<?php endif; ?>
									</td>
									<td>
										<?= esc($row->nama_atlet_merah ?? '-') ?>
										<?php if (!empty($row->nama_kontingen_merah)) : ?>
											<br><small class="text-muted"><?= esc($row->nama_kontingen_merah) ?></small>
										<?php endif; ?>
									</td>
									<td>
										<?php
										$status = $row->status ?? 'belum_dimulai';
										$badgeClass = match ($status) {
											'sedang_berlangsung' => 'bg-success',
											'selesai' => 'bg-secondary',
											default => 'bg-warning text-dark',
										};
										$statusLabel = match ($status) {
											'sedang_berlangsung' => 'Berlangsung',
											'selesai' => 'Selesai',
											default => 'Belum Dimulai',
										};
										?>
										<span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
									</td>
									<td>
										<?php if ($status == 'belum_dimulai') : ?>
											<a href="<?= base_url('sekretaris-pertandingan/mulai-pertandingan/' . ($row->id_pertandingan ?? '')) ?>"
												class="btn btn-sm btn-primary">
												Mulai
											</a>
										<?php elseif ($status == 'sedang_berlangsung') : ?>
											<a href="<?= base_url('sekretaris-pertandingan/timer-tanding') ?>"
												class="btn btn-sm btn-success" target="_blank">
												Timer
											</a>
										<?php elseif ($status == 'selesai') : ?>
											<a href="<?= base_url('sekretaris-pertandingan/mulai-pertandingan/' . ($row->id_pertandingan ?? '')) ?>"
												class="btn btn-sm btn-warning btn-sm">
												Mulai Ulang
											</a>
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
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
	if ($('#tabelDetailJadwalTanding').length) {
		$('#tabelDetailJadwalTanding').DataTable({
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
	}
});
</script>
<?= $this->endSection() ?>
