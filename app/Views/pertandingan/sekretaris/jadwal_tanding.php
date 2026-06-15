<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/jadwal-detail.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'sekretaris', 'nav_active' => 'dashboard', 'nav_page_type' => 'home']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
	// Hitung statistik partai
	$totalPartai = count($partai ?? []);
	$selesai = 0;
	$berlangsung = 0;
	$belum = 0;
	foreach (($partai ?? []) as $row) {
		$st = $row->status_pertandingan ?? 'belum_dimulai';
		if ($st === 'selesai') $selesai++;
		elseif (in_array($st, ['berlangsung', 'standby', 'berhenti'], true)) $berlangsung++;
		else $belum++;
	}
?>
<div class="detail-container">
	<!-- Header Bar -->
	<header class="detail-header">
		<div class="header-left">
			<a href="<?= base_url('sekretaris-pertandingan') ?>" class="btn-back" aria-label="Kembali ke Dashboard">
				<i class="fas fa-arrow-left"></i>
			</a>
			<div class="header-icon tanding">
				<i class="fas fa-fist-raised"></i>
			</div>
			<div class="header-text">
				<div class="header-meta">
					<span class="badge-category">Kategori Tanding</span>
				</div>
				<h1>Gelanggang <?= esc($jadwal->nama_gelanggang ?? $nama_gelanggang ?? '-') ?></h1>
				<p>
					<span><i class="far fa-calendar-alt"></i> <?= esc($jadwal->tanggal_formatted ?? '-') ?></span>
					<span class="separator">·</span>
					<span><i class="far fa-clock"></i> <?= esc($jadwal->jam_mulai_formatted ?? '00:00') ?> – <?= esc($jadwal->jam_selesai_formatted ?? '00:00') ?></span>
					<?php if (!empty($jadwal->keterangan)) : ?>
						<span class="separator">·</span>
						<span><i class="fas fa-info-circle"></i> <?= esc($jadwal->keterangan) ?></span>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<div class="stats-row">
			<div class="stat-chip">
				<div class="stat-chip-icon stat-total">
					<i class="fas fa-list-ol"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= $totalPartai ?></span>
					<span class="stat-chip-label">Total Partai</span>
				</div>
			</div>
			<div class="stat-chip">
				<div class="stat-chip-icon stat-active">
					<i class="fas fa-circle-play"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= $berlangsung ?></span>
					<span class="stat-chip-label">Berlangsung</span>
				</div>
			</div>
			<div class="stat-chip">
				<div class="stat-chip-icon stat-done">
					<i class="fas fa-check"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= $selesai ?></span>
					<span class="stat-chip-label">Selesai</span>
				</div>
			</div>
			<div class="stat-chip">
				<div class="stat-chip-icon stat-pending">
					<i class="fas fa-hourglass-start"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= $belum ?></span>
					<span class="stat-chip-label">Belum Dimulai</span>
				</div>
			</div>
		</div>
	</header>

	<!-- Filter / Search Bar -->
	<div class="filter-row">
		<div class="search-wrapper">
			<i class="fas fa-search search-icon" aria-hidden="true"></i>
			<input type="text" id="partai-search" class="search-input" placeholder="Cari partai, kelas, atlet, atau kontingen..." aria-label="Cari partai">
		</div>

		<div class="filter-pills" role="group" aria-label="Filter status">
			<button type="button" class="filter-pill active" data-filter="all">
				Semua
			</button>
			<button type="button" class="filter-pill" data-filter="belum_dimulai">
				<span class="dot pending"></span> Belum
			</button>
			<button type="button" class="filter-pill" data-filter="berlangsung">
				<span class="dot active"></span> Berlangsung
			</button>
			<button type="button" class="filter-pill" data-filter="selesai">
				<span class="dot done"></span> Selesai
			</button>
		</div>
	</div>

	<!-- Partai Table -->
	<div class="table-section">
		<?php if (!empty($partai)) : ?>
			<div class="table-wrapper">
				<table class="modern-table" id="tabelDetailJadwalTanding">
					<thead>
						<tr>
							<th class="col-no">#</th>
							<th class="col-partai">Partai</th>
							<th class="col-kelas">Kelas / Babak</th>
							<th class="col-atlet col-blue">
								<div class="col-corner-header">
									<span class="corner-dot blue"></span> Sudut Biru
								</div>
							</th>
							<th class="col-atlet col-red">
								<div class="col-corner-header">
									<span class="corner-dot red"></span> Sudut Merah
								</div>
							</th>
							<th class="col-pemenang">Pemenang</th>
							<th class="col-status">Status</th>
							<th class="col-aksi text-end">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php $i = 1; ?>
						<?php foreach ($partai as $row) :
							$status = $row->status_pertandingan ?? 'belum_dimulai';
							$statusKey = match ($status) {
								'berlangsung', 'standby', 'berhenti' => 'berlangsung',
								'selesai' => 'selesai',
								default => 'belum_dimulai',
							};
							$statusLabel = match ($statusKey) {
								'berlangsung' => 'Berlangsung',
								'selesai' => 'Selesai',
								default => 'Belum Dimulai',
							};

							// Search key
							$searchKey = strtolower(trim(implode(' ', [
								$row->nomor_partai ?? '',
								$row->nama_kelas ?? '',
								$row->babak ?? '',
								$row->nama_atlet_biru ?? '',
								$row->nama_atlet_merah ?? '',
								$row->nama_kontingen_biru ?? '',
								$row->nama_kontingen_merah ?? '',
							])));
						?>
							<tr data-status="<?= $statusKey ?>" data-search="<?= esc($searchKey) ?>">
								<td class="col-no"><?= $i++ ?></td>
								<td class="col-partai">
									<span class="partai-num"><?= esc($row->nomor_partai ?? '-') ?></span>
								</td>
								<td class="col-kelas">
									<div class="kelas-info">
										<span class="kelas-name"><?= esc($row->nama_kelas ?? '-') ?></span>
										<span class="babak-tag"><?= esc(ucwords($row->babak ?? '')) ?></span>
									</div>
								</td>
								<td class="col-atlet col-blue">
									<div class="atlet-info">
										<span class="atlet-name"><?= esc($row->nama_atlet_biru ?? '-') ?></span>
										<?php if (!empty($row->nama_kontingen_biru)) : ?>
											<span class="kontingen-name"><i class="fas fa-flag"></i> <?= esc($row->nama_kontingen_biru) ?></span>
										<?php endif; ?>
									</div>
								</td>
								<td class="col-atlet col-red">
									<div class="atlet-info">
										<span class="atlet-name"><?= esc($row->nama_atlet_merah ?? '-') ?></span>
										<?php if (!empty($row->nama_kontingen_merah)) : ?>
											<span class="kontingen-name"><i class="fas fa-flag"></i> <?= esc($row->nama_kontingen_merah) ?></span>
										<?php endif; ?>
									</div>
								</td>
								<td class="col-pemenang">
									<?php
									if ($status === 'selesai' && !empty($row->id_pemenang)) {
										if ($row->id_pemenang == $row->id_atlet_merah) {
											echo '<span class="winner-badge red"><i class="fas fa-trophy"></i> Merah</span>';
										} elseif ($row->id_pemenang == $row->id_atlet_biru) {
											echo '<span class="winner-badge blue"><i class="fas fa-trophy"></i> Biru</span>';
										}
										if (!empty($row->jenis_kemenangan)) {
											echo '<span class="winner-method">' . esc($row->jenis_kemenangan) . '</span>';
										}
									} else {
										echo '<span class="text-tertiary">—</span>';
									}
									?>
								</td>
								<td class="col-status">
									<span class="status-badge status-<?= $statusKey ?>">
										<span class="status-dot"></span>
										<?= $statusLabel ?>
									</span>
								</td>
								<td class="col-aksi text-end">
									<?php if (in_array($status, ['belum_dimulai', 'selesai'], true)) : ?>
										<a href="<?= base_url('sekretaris-pertandingan/mulai-pertandingan/' . ($row->id_pertandingan ?? '')) ?>"
											class="btn-action btn-primary-action">
											<i class="fas fa-play"></i>
											<?= $status === 'selesai' ? 'Buka Lagi' : 'Mulai' ?>
										</a>
									<?php elseif (in_array($status, ['berlangsung', 'standby', 'berhenti'], true)) : ?>
										<a href="<?= base_url('sekretaris-pertandingan/timer-tanding') ?>" target="_blank"
											class="btn-action btn-success-action">
											<i class="fas fa-stopwatch"></i> Timer
										</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<div class="no-results" id="no-results">
				<i class="fas fa-search fa-2x mb-2"></i>
				<p class="mb-0">Tidak ada partai yang cocok dengan pencarian/filter</p>
			</div>
		<?php else : ?>
			<div class="empty-state">
				<div class="empty-state-icon">
					<i class="fas fa-fist-raised"></i>
				</div>
				<h3 class="empty-state-title">Tidak Ada Partai</h3>
				<p class="empty-state-description">
					Belum ada partai yang dijadwalkan untuk jadwal ini.
				</p>
			</div>
		<?php endif; ?>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
	(function() {
		'use strict';

		const searchInput = document.getElementById('partai-search');
		const filterPills = document.querySelectorAll('.filter-pill');
		const noResults = document.getElementById('no-results');
		const table = document.getElementById('tabelDetailJadwalTanding');

		if (!table) return;

		const tbody = table.querySelector('tbody');
		const wrapper = document.querySelector('.table-wrapper');
		let currentFilter = 'all';

		function applyFilters() {
			const query = (searchInput?.value || '').toLowerCase().trim();
			const rows = tbody.querySelectorAll('tr');
			let visibleCount = 0;

			rows.forEach(row => {
				const status = row.getAttribute('data-status') || '';
				const searchData = row.getAttribute('data-search') || '';
				const statusMatch = currentFilter === 'all' || status === currentFilter;
				const searchMatch = query === '' || searchData.includes(query);
				const visible = statusMatch && searchMatch;
				row.style.display = visible ? '' : 'none';
				if (visible) visibleCount++;
			});

			if (noResults) {
				const showNoResults = visibleCount === 0;
				noResults.style.display = showNoResults ? 'flex' : 'none';
				if (wrapper) wrapper.style.display = showNoResults ? 'none' : '';
			}
		}

		// Search debounced
		let searchTimer;
		if (searchInput) {
			searchInput.addEventListener('input', () => {
				clearTimeout(searchTimer);
				searchTimer = setTimeout(applyFilters, 150);
			});
		}

		// Filter pills
		filterPills.forEach(pill => {
			pill.addEventListener('click', () => {
				filterPills.forEach(p => p.classList.remove('active'));
				pill.classList.add('active');
				currentFilter = pill.getAttribute('data-filter') || 'all';
				applyFilters();
			});
		});

		// Keyboard shortcut '/' focuses search
		document.addEventListener('keydown', e => {
			if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
				e.preventDefault();
				searchInput?.focus();
			}
			if (e.key === 'Escape' && document.activeElement === searchInput) {
				searchInput.value = '';
				applyFilters();
				searchInput.blur();
			}
		});
	})();
</script>
<?= $this->endSection() ?>
