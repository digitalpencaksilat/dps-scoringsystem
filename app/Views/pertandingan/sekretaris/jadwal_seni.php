<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/jadwal-detail.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('components/navbar_sekretaris', ['active' => 'dashboard', 'page_type' => 'home']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
	// Statistik
	$totalBattle = count($battle ?? []);
	$totalPool = count($pool ?? []);
	$battleSelesai = 0;
	$poolSelesai = 0;
	foreach (($battle ?? []) as $row) {
		$biru = $row->status_biru ?? 'belum_tampil';
		$merah = $row->status_merah ?? 'belum_tampil';
		if ($biru === 'sudah_tampil' && $merah === 'sudah_tampil') $battleSelesai++;
	}
	foreach (($pool ?? []) as $row) {
		if (($row->status_penampilan ?? '') === 'sudah_tampil') $poolSelesai++;
	}

	// Medali helper
	$renderMedali = static function (?string $jenis): string {
		if (empty($jenis)) {
			return '<span class="text-tertiary">—</span>';
		}
		$map = [
			'emas'    => 'emas',
			'perak'   => 'perak',
			'perunggu'=> 'perunggu',
		];
		$labels = ['emas' => 'Emas', 'perak' => 'Perak', 'perunggu' => 'Perunggu'];
		$cls = $map[$jenis] ?? null;
		if ($cls === null) return '<span class="text-tertiary">—</span>';
		return '<span class="medali-badge ' . $cls . '"><i class="fas fa-medal"></i> ' . $labels[$jenis] . '</span>';
	};
?>
<div class="detail-container">
	<!-- Header Bar -->
	<header class="detail-header">
		<div class="header-left">
			<a href="<?= base_url('sekretaris-pertandingan') ?>" class="btn-back" aria-label="Kembali ke Dashboard">
				<i class="fas fa-arrow-left"></i>
			</a>
			<div class="header-icon seni">
				<i class="fas fa-theater-masks"></i>
			</div>
			<div class="header-text">
				<div class="header-meta">
					<span class="badge-category">Kategori Seni</span>
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
					<span class="stat-chip-value"><?= $totalBattle + $totalPool ?></span>
					<span class="stat-chip-label">Total</span>
				</div>
			</div>
			<div class="stat-chip">
				<div class="stat-chip-icon stat-done">
					<i class="fas fa-check"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= $battleSelesai + $poolSelesai ?></span>
					<span class="stat-chip-label">Selesai</span>
				</div>
			</div>
			<div class="stat-chip">
				<div class="stat-chip-icon stat-pending">
					<i class="fas fa-hourglass-start"></i>
				</div>
				<div class="stat-chip-text">
					<span class="stat-chip-value"><?= ($totalBattle - $battleSelesai) + ($totalPool - $poolSelesai) ?></span>
					<span class="stat-chip-label">Sisa</span>
				</div>
			</div>
		</div>
	</header>

	<!-- Tab + Filter -->
	<div class="filter-row">
		<div class="detail-tabs" role="tablist">
			<button class="tab-btn active" data-tab="battle" type="button" role="tab" aria-selected="true">
				<i class="fas fa-users"></i> Battle
				<span class="badge-count"><?= $totalBattle ?></span>
			</button>
			<button class="tab-btn" data-tab="pool" type="button" role="tab" aria-selected="false">
				<i class="fas fa-user"></i> Pool
				<span class="badge-count"><?= $totalPool ?></span>
			</button>
		</div>

		<div class="search-wrapper">
			<i class="fas fa-search search-icon" aria-hidden="true"></i>
			<input type="text" id="seni-search" class="search-input" placeholder="Cari partai, kategori, peserta, kontingen..." aria-label="Cari penampilan">
		</div>

		<div class="filter-pills" role="group" aria-label="Filter status">
			<button type="button" class="filter-pill active" data-filter="all">Semua</button>
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

	<!-- Tab Content -->
	<!-- Battle Tab -->
	<div class="tab-pane-detail active" id="pane-battle" role="tabpanel">
		<div class="table-section">
			<?php if (!empty($battle)) : ?>
				<div class="table-wrapper">
					<table class="modern-table" id="tabelBattleSeni">
						<thead>
							<tr>
								<th class="col-no">#</th>
								<th class="col-partai">Partai</th>
								<th>Kategori / Babak</th>
								<th class="col-atlet col-blue">
									<div class="col-corner-header"><span class="corner-dot blue"></span> Sudut Biru</div>
								</th>
								<th class="col-atlet col-red">
									<div class="col-corner-header"><span class="corner-dot red"></span> Sudut Merah</div>
								</th>
								<th class="col-status">Status</th>
								<th>Medali</th>
								<th class="col-aksi text-end">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php $i = 1; ?>
							<?php foreach ($battle as $row) :
								$isAktif = in_array($row->status_biru ?? '', ['sedang_tampil', 'standby', 'berhenti'])
								        || in_array($row->status_merah ?? '', ['sedang_tampil', 'standby', 'berhenti']);
								$isAllSelesai = ($row->status_biru === 'sudah_tampil' && $row->status_merah === 'sudah_tampil');
								$status = $isAktif ? 'berlangsung' : ($isAllSelesai ? 'selesai' : 'belum_dimulai');
								$statusLabel = match ($status) {
									'berlangsung' => 'Berlangsung',
									'selesai' => 'Selesai',
									default => 'Belum Dimulai',
								};

								$searchKey = strtolower(trim(implode(' ', [
									$row->nomor_partai ?? '',
									$row->nama_kategori_usia ?? '',
									$row->jenis_kelamin ?? '',
									$row->jenis_seni ?? '',
									$row->nama_seni ?? '',
									$row->babak ?? '',
									$row->anggota_biru ?? '',
									$row->anggota_merah ?? '',
									$row->nama_kontingen_biru ?? '',
									$row->nama_kontingen_merah ?? '',
								])));
							?>
								<tr data-status="<?= $status ?>" data-search="<?= esc($searchKey) ?>">
									<td class="col-no"><?= $i++ ?></td>
									<td class="col-partai">
										<span class="partai-num"><?= esc($row->nomor_partai ?? '-') ?></span>
									</td>
									<td>
										<div class="kelas-info">
											<span class="kelas-name"><?= esc(($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? '') . ' ' . ($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? '')) ?></span>
											<span class="babak-tag"><?= esc(ucwords($row->babak ?? '')) ?></span>
										</div>
									</td>
									<td class="col-atlet col-blue">
										<div class="atlet-info">
											<span class="atlet-name"><?= esc($row->anggota_biru ?? '-') ?></span>
											<?php if (!empty($row->nama_kontingen_biru)) : ?>
												<span class="kontingen-name"><i class="fas fa-flag"></i> <?= esc($row->nama_kontingen_biru) ?></span>
											<?php endif; ?>
										</div>
									</td>
									<td class="col-atlet col-red">
										<div class="atlet-info">
											<span class="atlet-name"><?= esc($row->anggota_merah ?? '-') ?></span>
											<?php if (!empty($row->nama_kontingen_merah)) : ?>
												<span class="kontingen-name"><i class="fas fa-flag"></i> <?= esc($row->nama_kontingen_merah) ?></span>
											<?php endif; ?>
										</div>
									</td>
									<td class="col-status">
										<span class="status-badge status-<?= $status ?>">
											<span class="status-dot"></span>
											<?= $statusLabel ?>
										</span>
									</td>
									<td>
										<div class="medali-row">
											<div class="medali-row-item">
												<span class="medali-corner-label blue">Biru</span>
												<?= $renderMedali($row->medali_biru ?? null) ?>
											</div>
											<div class="medali-row-item">
												<span class="medali-corner-label red">Merah</span>
												<?= $renderMedali($row->medali_merah ?? null) ?>
											</div>
										</div>
									</td>
									<td class="col-aksi text-end">
										<?php if ($status === 'belum_dimulai' && !empty($row->penampilan_biru_id)) : ?>
											<a href="<?= base_url('sekretaris-pertandingan/mulai-penampilan/' . $row->penampilan_biru_id) ?>"
												class="btn-action btn-primary-action">
												<i class="fas fa-play"></i> Mulai Biru
											</a>
										<?php elseif ($status === 'berlangsung') : ?>
											<a href="<?= base_url('sekretaris-pertandingan/timer-seni') ?>" target="_blank"
												class="btn-action btn-success-action">
												<i class="fas fa-stopwatch"></i> Timer
											</a>
										<?php elseif ($status === 'selesai') : ?>
											<?php if (!empty($row->penampilan_biru_id)) : ?>
												<button type="button"
													onclick="konfirmasiMulaiUlang('<?= base_url('sekretaris-pertandingan/mulai-ulang-penampilan/' . $row->penampilan_biru_id) ?>', 'Sudut Biru')"
													class="btn-action btn-outline-blue">
													<i class="fas fa-redo"></i> Ulang Biru
												</button>
											<?php endif; ?>
											<?php if (!empty($row->penampilan_merah_id)) : ?>
												<button type="button"
													onclick="konfirmasiMulaiUlang('<?= base_url('sekretaris-pertandingan/mulai-ulang-penampilan/' . $row->penampilan_merah_id) ?>', 'Sudut Merah')"
													class="btn-action btn-outline-red">
													<i class="fas fa-redo"></i> Ulang Merah
												</button>
											<?php endif; ?>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="no-results" id="no-results-battle">
					<i class="fas fa-search fa-2x mb-2"></i>
					<p class="mb-0">Tidak ada battle yang cocok dengan pencarian/filter</p>
				</div>
			<?php else : ?>
				<div class="empty-state">
					<div class="empty-state-icon"><i class="fas fa-users"></i></div>
					<h3 class="empty-state-title">Tidak Ada Battle</h3>
					<p class="empty-state-description">Belum ada battle yang dijadwalkan.</p>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- Pool Tab -->
	<div class="tab-pane-detail" id="pane-pool" role="tabpanel">
		<div class="table-section">
			<?php if (!empty($pool)) : ?>
				<div class="table-wrapper">
					<table class="modern-table" id="tabelPoolSeni">
						<thead>
							<tr>
								<th class="col-no">#</th>
								<th class="col-partai">Partai</th>
								<th>Kategori</th>
								<th>Pool</th>
								<th>Peserta</th>
								<th class="col-status">Status</th>
								<th>Medali</th>
								<th class="col-aksi text-end">Aksi</th>
							</tr>
						</thead>
						<tbody>
							<?php $i = 1; ?>
							<?php foreach ($pool as $row) :
								$sp = $row->status_penampilan ?? 'belum_tampil';
								$statusKey = match ($sp) {
									'sedang_tampil', 'standby', 'berhenti' => 'berlangsung',
									'sudah_tampil' => 'selesai',
									default => 'belum_dimulai',
								};
								$statusLabel = match ($statusKey) {
									'berlangsung' => 'Berlangsung',
									'selesai' => 'Selesai',
									default => 'Belum Dimulai',
								};

								$searchKey = strtolower(trim(implode(' ', [
									$row->nomor_partai ?? '',
									$row->nama_kategori_usia ?? '',
									$row->jenis_kelamin ?? '',
									$row->jenis_seni ?? '',
									$row->nama_seni ?? '',
									$row->nomor_pool ?? '',
									$row->anggota ?? '',
									$row->nama_kontingen ?? '',
								])));
							?>
								<tr data-status="<?= $statusKey ?>" data-search="<?= esc($searchKey) ?>">
									<td class="col-no"><?= $i++ ?></td>
									<td class="col-partai">
										<span class="partai-num"><?= esc($row->nomor_partai ?? '-') ?></span>
									</td>
									<td>
										<div class="kelas-info">
											<span class="kelas-name"><?= esc(($row->nama_kategori_usia ?? '') . ' ' . ($row->jenis_kelamin ?? '') . ' ' . ($row->jenis_seni ?? '') . ' ' . ($row->nama_seni ?? '')) ?></span>
										</div>
									</td>
									<td>
										<span class="pool-tag"><i class="fas fa-layer-group"></i> Pool <?= esc($row->nomor_pool ?? '-') ?></span>
									</td>
									<td>
										<div class="atlet-info">
											<span class="atlet-name"><?= esc($row->anggota ?? '-') ?></span>
											<?php if (!empty($row->nama_kontingen)) : ?>
												<span class="kontingen-name"><i class="fas fa-flag"></i> <?= esc($row->nama_kontingen) ?></span>
											<?php endif; ?>
										</div>
									</td>
									<td class="col-status">
										<span class="status-badge status-<?= $statusKey ?>">
											<span class="status-dot"></span>
											<?= $statusLabel ?>
										</span>
									</td>
									<td>
										<?= $renderMedali($row->jenis_medali ?? null) ?>
									</td>
									<td class="col-aksi text-end">
										<?php if ($sp === 'belum_tampil') : ?>
											<a href="<?= base_url('sekretaris-pertandingan/mulai-penampilan/' . ($row->id_penampilan_seni ?? '')) ?>"
												class="btn-action btn-primary-action">
												<i class="fas fa-play"></i> Mulai
											</a>
										<?php elseif (in_array($sp, ['sedang_tampil', 'standby', 'berhenti'], true)) : ?>
											<a href="<?= base_url('sekretaris-pertandingan/timer-seni') ?>" target="_blank"
												class="btn-action btn-success-action">
												<i class="fas fa-stopwatch"></i> Timer
											</a>
										<?php elseif ($sp === 'sudah_tampil') : ?>
											<button type="button"
												onclick="konfirmasiMulaiUlang('<?= base_url('sekretaris-pertandingan/mulai-ulang-penampilan/' . ($row->id_penampilan_seni ?? '')) ?>', 'Penampilan')"
												class="btn-action btn-outline-action">
												<i class="fas fa-redo"></i> Buka Lagi
											</button>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="no-results" id="no-results-pool">
					<i class="fas fa-search fa-2x mb-2"></i>
					<p class="mb-0">Tidak ada pool yang cocok dengan pencarian/filter</p>
				</div>
			<?php else : ?>
				<div class="empty-state">
					<div class="empty-state-icon"><i class="fas fa-user"></i></div>
					<h3 class="empty-state-title">Tidak Ada Pool</h3>
					<p class="empty-state-description">Belum ada penampilan pool yang dijadwalkan.</p>
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

	// Tab switching
	const tabBtns = document.querySelectorAll('.detail-tabs .tab-btn');
	const tabPanes = document.querySelectorAll('.tab-pane-detail');

	tabBtns.forEach(btn => {
		btn.addEventListener('click', () => {
			tabBtns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected', 'false'); });
			btn.classList.add('active');
			btn.setAttribute('aria-selected', 'true');

			const target = btn.getAttribute('data-tab');
			tabPanes.forEach(p => p.classList.remove('active'));
			document.getElementById('pane-' + target)?.classList.add('active');

			applyFilters(); // re-apply to new tab
		});
	});

	// Filter + Search
	const searchInput = document.getElementById('seni-search');
	const filterPills = document.querySelectorAll('.filter-pill');
	let currentFilter = 'all';

	function applyFilters() {
		const query = (searchInput?.value || '').toLowerCase().trim();
		const activePane = document.querySelector('.tab-pane-detail.active');
		if (!activePane) return;

		const tbody = activePane.querySelector('tbody');
		const wrapper = activePane.querySelector('.table-wrapper');
		const noResults = activePane.querySelector('.no-results');
		if (!tbody) return;

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

		const showNoResults = visibleCount === 0;
		if (noResults) noResults.style.display = showNoResults ? 'flex' : 'none';
		if (wrapper) wrapper.style.display = showNoResults ? 'none' : '';
	}

	// Debounced search
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

	// Keyboard shortcuts
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

/**
 * Konfirmasi sebelum buka kembali penampilan
 */
function konfirmasiMulaiUlang(url, label) {
	Swal.fire({
		title: 'Buka Kembali Penampilan',
		html: `Buka kembali penampilan <strong>${label}</strong> untuk review atau koreksi nilai?<br><br>
			   <small class="text-muted">• Data penilaian dan waktu tetap tersimpan</small><br>
			   <small class="text-muted">• Status kembali ke Berlangsung</small>`,
		icon: 'question',
		showCancelButton: true,
		confirmButtonText: 'Ya, Buka Kembali',
		cancelButtonText: 'Batal',
		confirmButtonColor: '#c60000',
		cancelButtonColor: '#6c757d',
		background: '#1a1e2e',
		color: '#fff',
	}).then((result) => {
		if (result.isConfirmed) {
			window.location.href = url;
		}
	});
}
</script>
<?= $this->endSection() ?>
