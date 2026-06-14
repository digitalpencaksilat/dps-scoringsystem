<!-- Offcanvas Pindah Partai Seni -->
<button class="btn btn-success w-100 btn-lg h3 py-5" type="button" data-bs-toggle="offcanvas"
	data-bs-target="#offcanvasPindahPartaiSeni" aria-controls="offcanvasPindahPartaiSeni">
	Jump To Match
</button>

<div class="offcanvas offcanvas-bottom min-vh-75" data-bs-scroll="true" tabindex="-1" id="offcanvasPindahPartaiSeni"
	aria-labelledby="offcanvasPindahPartaiSeniTitle">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title" id="offcanvasPindahPartaiSeniTitle">
			Select Match - Arena <?= esc($nama_gelanggang ?? session('nama_gelanggang') ?? '') ?>
		</h5>
		<button type="button" class="btn btn-default text-dark text-lg shadow-none" data-bs-dismiss="offcanvas" aria-label="Close">
			&times;
		</button>
	</div>
	<div class="offcanvas-body">
		<div class="nav-wrapper position-relative end-0">
			<ul class="nav nav-pills nav-pills-primary nav-fill p-1" role="tablist">
				<li class="nav-item">
					<a class="nav-link h5 mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#offcanvas_battle_seni" role="tab" aria-selected="true">
						Battle Seni
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link h5 mb-0 px-0 py-1" data-bs-toggle="tab" href="#offcanvas_pool_seni" role="tab" aria-selected="false">
						Pool Seni
					</a>
				</li>
			</ul>
			<div class="tab-content">
				<div class="tab-pane active" id="offcanvas_battle_seni" role="tabpanel">
					<div class="card">
						<div class="card-body table-responsive">
							<table class="table table-striped" id="tabelPindahBattleSeni">
								<thead>
									<tr>
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
									<?php foreach ($data_detail_jadwal_seni_sistem_battle ?? [] as $row) : ?>
										<tr>
											<td><?= esc($row->nomor_partai ?? '') ?></td>
											<td class="text-capitalize">
												<?= esc(($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? '') . ' ' . ($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? '')) ?>
											</td>
											<td><?= esc(ucwords($row->babak_battle ?? '')) ?></td>
											<td><?= esc($row->nama_peserta_biru ?? '-') ?></td>
											<td><?= esc($row->nama_peserta_merah ?? '-') ?></td>
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
												<button type="button" class="btn btn-sm btn-primary"
													onclick="sekretaris_pertandingan.pindah_partai(<?= (int) ($row->nomor_partai ?? 0) ?>)">
													Pilih
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="offcanvas_pool_seni" role="tabpanel">
					<div class="card">
						<div class="card-body table-responsive">
							<table class="table table-striped" id="tabelPindahPoolSeni">
								<thead>
									<tr>
										<th>Partai</th>
										<th>Kategori</th>
										<th>Pool</th>
										<th>Peserta</th>
										<th>Status</th>
										<th>Aksi</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($data_detail_jadwal_seni_sistem_pool ?? [] as $row) : ?>
										<tr>
											<td><?= esc($row->nomor_partai ?? '') ?></td>
											<td class="text-capitalize">
												<?= esc(($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? '') . ' ' . ($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? '')) ?>
											</td>
											<td>Pool <?= esc($row->nomor_pool ?? '') ?></td>
											<td><?= esc($row->nama_peserta ?? '-') ?></td>
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
												<button type="button" class="btn btn-sm btn-primary"
													onclick="sekretaris_pertandingan.pindah_partai(<?= (int) ($row->nomor_partai ?? 0) ?>)">
													Pilih
												</button>
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
