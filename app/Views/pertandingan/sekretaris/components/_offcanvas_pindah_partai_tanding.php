<!-- Offcanvas Pindah Partai Tanding -->
<button class="btn btn-success w-100 btn-lg h3 py-5" type="button" data-bs-toggle="offcanvas"
	data-bs-target="#offcanvasPindahPartaiTanding" aria-controls="offcanvasPindahPartaiTanding">
	Jump To Match
</button>

<div class="offcanvas offcanvas-bottom min-vh-75" data-bs-scroll="true" tabindex="-1" id="offcanvasPindahPartaiTanding"
	aria-labelledby="offcanvasPindahPartaiTandingTitle">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="offcanvasPindahPartaiTandingTitle">
			Select Match - Arena <?= esc($nama_gelanggang ?? session('nama_gelanggang') ?? '') ?>
		</h5>
		<button type="button" class="btn btn-default text-dark text-lg shadow-none" data-bs-dismiss="offcanvas" aria-label="Close">
			&times;
		</button>
	</div>
	<div class="offcanvas-body">
		<div class="card">
			<div class="card-body table-responsive">
				<table class="table table-striped" id="tabelPindahPartaiTanding">
					<thead>
						<tr>
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
						<?php foreach ($daftar_partai ?? [] as $row) : ?>
							<tr>
								<td><?= esc($row->nomor_partai ?? '') ?></td>
								<td><?= esc($row->nama_kelas ?? '') ?></td>
								<td><?= esc($row->babak ?? '') ?></td>
								<td><?= esc($row->nama_atlet_biru ?? '-') ?></td>
								<td><?= esc($row->nama_atlet_merah ?? '-') ?></td>
								<td>
									<?php
									$status = $row->status_pertandingan ?? 'belum_dimulai';
									$badgeClass = match ($status) {
										'berlangsung', 'standby', 'berhenti' => 'bg-success',
										'selesai' => 'bg-secondary',
										default => 'bg-warning text-dark',
									};
									$statusLabel = match ($status) {
										'berlangsung', 'standby', 'berhenti' => 'Berlangsung',
										'selesai' => 'Selesai',
										default => 'Belum Dimulai',
									};
									?>
									<span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span>
								</td>
								<td>
									<a href="<?= base_url('sekretaris-pertandingan/pindah-partai-tanding/' . ($row->id_pertandingan ?? '')) ?>"
										class="btn btn-sm btn-primary">Pilih</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
