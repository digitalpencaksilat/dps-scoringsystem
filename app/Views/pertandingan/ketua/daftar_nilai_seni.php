<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
:root {
	--ds-surface: rgba(255,255,255,0.03);
	--ds-surface-hover: rgba(255,255,255,0.06);
	--ds-border: rgba(255,255,255,0.07);
	--ds-text: rgba(255,255,255,0.9);
	--ds-text-muted: rgba(255,255,255,0.5);
	--ds-accent: #c60000;
	--ds-gold: #c5a017;
	--ds-blue: #1565c0;
	--ds-green: #198754;
	--ds-red: #dc3545;
	--ds-amber: #f59e0b;
}

html, body { height: 100%; overflow: hidden; margin: 0; }

body {
	display: flex;
	flex-direction: column;
	background:
		radial-gradient(ellipse at top, rgba(197,160,23,0.05) 0%, transparent 60%),
		linear-gradient(180deg, #0a0d12 0%, #0f1419 100%);
	font-family: 'Poppins', sans-serif;
	color: var(--ds-text);
}

.navbar-custom { flex-shrink: 0; }

/* ═══ Wrapper ════════════════════════════════════════════════════════ */
#ds-wrapper {
	flex: 1;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	padding: clamp(0.5rem, 1.2vw, 0.75rem);
	gap: clamp(0.4rem, 1vw, 0.6rem);
	max-width: 1400px;
	width: 100%;
	margin: 0 auto;
}

/* ═══ Header ══════════════════════════════════════════════════════════ */
.ds-header {
	flex-shrink: 0;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: clamp(0.5rem, 1.5vw, 1rem);
	padding: clamp(0.3rem, 0.8vw, 0.5rem) 0;
}

.ds-header-left {
	display: flex;
	align-items: center;
	gap: clamp(0.4rem, 1vw, 0.7rem);
	min-width: 0;
}

.ds-icon {
	width: clamp(2.2rem, 4.5vw, 2.8rem);
	height: clamp(2.2rem, 4.5vw, 2.8rem);
	border-radius: 8px;
	background: linear-gradient(135deg, var(--ds-gold) 0%, #9a7d0a 100%);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: clamp(0.95rem, 2vw, 1.2rem);
	color: #fff;
	flex-shrink: 0;
	box-shadow: 0 3px 10px rgba(197,160,23,0.3);
}

.ds-header-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.9rem, 2.2vw, 1.2rem);
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	line-height: 1.2;
	margin: 0;
}

.ds-header-sub {
	font-size: clamp(0.65rem, 1.3vw, 0.75rem);
	color: var(--ds-text-muted);
	line-height: 1.3;
	margin: 0;
}

.ds-header-right {
	display: flex;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
	flex-shrink: 0;
}

.ds-btn-back {
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
	padding: clamp(0.35rem, 0.8vw, 0.5rem) clamp(0.5rem, 1.2vw, 0.8rem);
	border-radius: 6px;
	font-size: clamp(0.7rem, 1.4vw, 0.8rem);
	font-weight: 600;
	color: var(--ds-text);
	background: rgba(255,255,255,0.06);
	border: 1px solid rgba(255,255,255,0.1);
	text-decoration: none;
	transition: all 0.2s;
	white-space: nowrap;
}

.ds-btn-back:hover { background: rgba(197,160,23,0.12); border-color: var(--ds-gold); color: #fff; }

/* ═══ Tabs ════════════════════════════════════════════════════════════ */
.ds-tabs {
	flex-shrink: 0;
	display: flex;
	gap: clamp(0.15rem, 0.4vw, 0.25rem);
	padding: 0.2rem;
	background: rgba(255,255,255,0.03);
	border: 1px solid var(--ds-border);
	border-radius: 8px;
}

.ds-tab-btn {
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
	padding: clamp(0.35rem, 0.8vw, 0.5rem) clamp(0.5rem, 1.2vw, 0.75rem);
	border: none;
	border-radius: 6px;
	background: transparent;
	color: var(--ds-text-muted);
	font-size: clamp(0.7rem, 1.4vw, 0.78rem);
	font-weight: 600;
	cursor: pointer;
	transition: all 0.2s;
	white-space: nowrap;
}

.ds-tab-btn:hover { color: #fff; background: rgba(255,255,255,0.05); }

.ds-tab-btn.active {
	background: linear-gradient(135deg, rgba(197,160,23,0.2) 0%, rgba(197,160,23,0.08) 100%);
	color: var(--ds-gold);
	box-shadow: 0 1px 6px rgba(197,160,23,0.15);
}

.ds-tab-badge {
	font-size: 0.7rem;
	padding: 0.1rem 0.45rem;
	border-radius: 1rem;
	background: rgba(255,255,255,0.08);
	color: var(--ds-text-muted);
}

.ds-tab-btn.active .ds-tab-badge {
	background: rgba(197,160,23,0.2);
	color: var(--ds-gold);
}

/* ═══ Tab Panes ══════════════════════════════════════════════════════ */
.ds-tab-content {
	flex: 1;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	min-height: 0;
}

.ds-tab-pane {
	display: none;
	flex: 1;
	flex-direction: column;
	overflow: hidden;
}

.ds-tab-pane.active {
	display: flex;
}

#ds-wrapper .text-muted,
#ds-wrapper small.text-muted {
	color: rgba(255,255,255,0.55) !important;
}

#ds-wrapper small {
	color: rgba(255,255,255,0.55);
}

/* ═══ Table Container ════════════════════════════════════════════════ */
.ds-table-wrap {
	flex: 1;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	background: rgba(255,255,255,0.02);
	border: 1px solid var(--ds-border);
	border-radius: 10px;
}

.ds-table-scroll {
	flex: 1;
	overflow: auto;
}

/* ═══ DataTables Dark Styling ════════════════════════════════════════ */
.ds-table-scroll .dataTables_length,
.ds-table-scroll .dataTables_filter,
.ds-table-scroll .dataTables_info,
.ds-table-scroll .dataTables_paginate {
	color: var(--ds-text-muted);
	font-size: clamp(0.65rem, 1.3vw, 0.75rem);
	padding: clamp(0.3rem, 0.7vw, 0.5rem) clamp(0.4rem, 1vw, 0.6rem);
}

.ds-table-scroll .dataTables_length select,
.ds-table-scroll .dataTables_filter input {
	background: rgba(255,255,255,0.06);
	border: 1px solid rgba(255,255,255,0.12);
	border-radius: 4px;
	color: #ddd;
	padding: 0.25rem 0.5rem;
	font-size: 0.75rem;
}

.ds-table-scroll .dataTables_filter input:focus {
	outline: none;
	border-color: var(--ds-gold);
	box-shadow: 0 0 0 1px rgba(197,160,23,0.2);
}

.ds-table-scroll .dataTables_length select {
	padding-right: 1.5rem;
	background: rgba(255,255,255,0.06);
	border: 1px solid rgba(255,255,255,0.12);
	color: #ddd;
}

.ds-table-scroll .dataTables_length select option {
	background: #1a1d22;
	color: #ddd;
}

.ds-table-scroll .paginate_button {
	background: rgba(255,255,255,0.04) !important;
	border: 1px solid rgba(255,255,255,0.08) !important;
	border-radius: 4px !important;
	color: var(--ds-text-muted) !important;
	padding: 0.25rem 0.6rem !important;
	font-size: 0.75rem !important;
	margin: 0 1px !important;
	transition: all 0.15s !important;
}

.ds-table-scroll .paginate_button:hover {
	background: rgba(197,160,23,0.12) !important;
	border-color: var(--ds-gold) !important;
	color: #fff !important;
}

.ds-table-scroll .paginate_button.current {
	background: linear-gradient(135deg, var(--ds-gold), #7a5f08) !important;
	border-color: var(--ds-gold) !important;
	color: #fff !important;
	font-weight: 700;
}

.ds-table-scroll .paginate_button.disabled {
	opacity: 0.35 !important;
	pointer-events: none;
	color: var(--ds-text-muted) !important;
}

/* ═══ Table ═══════════════════════════════════════════════════════════ */
.ds-table {
	--bs-table-bg: transparent;
	--bs-table-color: var(--ds-text);
	--bs-table-striped-color: var(--ds-text);
	--bs-table-striped-bg: rgba(255,255,255,0.015);
	--bs-table-border-color: rgba(255,255,255,0.06);
	--bs-table-hover-color: #fff;
	--bs-table-hover-bg: var(--ds-surface-hover);
	font-size: clamp(0.72rem, 1.5vw, 0.82rem);
	margin: 0 !important;
	width: 100% !important;
}

.ds-table thead th {
	background: rgba(255,255,255,0.05) !important;
	color: rgba(255,255,255,0.7) !important;
	font-weight: 600;
	font-size: clamp(0.62rem, 1.2vw, 0.7rem);
	text-transform: uppercase;
	letter-spacing: 0.5px;
	border-bottom: 2px solid rgba(255,255,255,0.08);
	padding: clamp(0.3rem, 0.7vw, 0.5rem) clamp(0.3rem, 0.6vw, 0.45rem);
	white-space: nowrap;
	position: sticky;
	top: 0;
	z-index: 2;
}

.ds-table tbody td {
	background: transparent;
	padding: clamp(0.3rem, 0.7vw, 0.45rem) clamp(0.3rem, 0.6vw, 0.45rem);
	vertical-align: middle;
	border-color: var(--ds-border);
}

.ds-table tbody tr:hover td {
	background: var(--ds-surface-hover);
}

/* ═══ Badges ══════════════════════════════════════════════════════════ */
.ds-badge {
	font-size: clamp(0.58rem, 1.1vw, 0.65rem);
	padding: 0.2rem 0.55rem;
	border-radius: 1rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.3px;
	white-space: nowrap;
}

.ds-badge-blue { background: rgba(21,101,192,0.2); color: #90caf9; border: 1px solid rgba(21,101,192,0.3); }
.ds-badge-red { background: rgba(198,40,40,0.2); color: #ef9a9a; border: 1px solid rgba(198,40,40,0.3); }
.ds-badge-green { background: rgba(25,135,84,0.2); color: #a5d6a7; border: 1px solid rgba(25,135,84,0.3); }
.ds-badge-amber { background: rgba(245,158,11,0.15); color: #ffcc80; border: 1px solid rgba(245,158,11,0.25); }

.ds-badge-solid-green { background: var(--ds-green); color: #fff; }
.ds-badge-solid-red { background: var(--ds-red); color: #fff; }

/* ═══ Empty State ═════════════════════════════════════════════════════ */
.ds-empty {
	flex: 1;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	color: rgba(255,255,255,0.25);
	text-align: center;
	padding: 3rem 1.5rem;
	gap: clamp(0.5rem, 1.5vw, 0.75rem);
}

.ds-empty i {
	font-size: clamp(1.8rem, 4vw, 2.5rem);
	opacity: 0.4;
	margin-bottom: 0.25rem;
}

.ds-empty p {
	font-size: clamp(0.78rem, 1.5vw, 0.88rem);
	max-width: 320px;
	line-height: 1.5;
}

/* ═══ Scrollbar ═══════════════════════════════════════════════════════ */
.ds-table-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
.ds-table-scroll::-webkit-scrollbar-track { background: transparent; }
.ds-table-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
.ds-table-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.15); }

/* ═══ Responsive ══════════════════════════════════════════════════════ */
@media (max-width: 768px) {
	.ds-header { flex-wrap: wrap; }
	.ds-header-right { width: 100%; justify-content: flex-end; }
	.ds-tabs { flex-wrap: wrap; }
}

@media (max-width: 480px) {
	.ds-header-left { flex-wrap: wrap; }
	.ds-table { font-size: 0.7rem; }
}

@media (orientation: landscape) and (max-height: 500px) {
	#ds-wrapper { padding: clamp(0.25rem, 0.8vw, 0.4rem); gap: 0.3rem; }
	.ds-header { padding: 0.15rem 0; }
	.ds-icon { width: 1.8rem; height: 1.8rem; }
}

@media (prefers-reduced-motion: reduce) {
	.ds-btn-back, .ds-tab-btn, .paginate_button { transition: none !important; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'daftar_nilai_seni']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="ds-wrapper">

	<!-- ═══ HEADER ═══ -->
	<div class="ds-header">
		<div class="ds-header-left">
			<div class="ds-icon">
				<i class="fas fa-clipboard-list"></i>
			</div>
			<div>
				<div class="ds-header-title">Daftar Nilai Seni</div>
				<div class="ds-header-sub">Rekap semua penampilan seni di gelanggang ini</div>
			</div>
		</div>
		<div class="ds-header-right">
			<a href="<?= base_url('ketua-pertandingan') ?>" class="ds-btn-back">
				<i class="fas fa-arrow-left"></i> Kembali
			</a>
		</div>
	</div>

	<!-- ═══ TABS ═══ -->
	<div class="ds-tabs" role="tablist">
		<button class="ds-tab-btn active" data-bs-toggle="tab" data-bs-target="#tab-pool" type="button" role="tab">
			<i class="fas fa-list"></i> Pool Seni
			<span class="ds-tab-badge"><?= count($poolList ?? []) ?></span>
		</button>
		<button class="ds-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-battle" type="button" role="tab">
			<i class="fas fa-swords"></i> Battle Seni
			<span class="ds-tab-badge"><?= count($battleList ?? []) ?></span>
		</button>
	</div>

	<!-- ═══ TAB PANES ═══ -->
	<div class="ds-tab-content">

		<!-- ── Pool ─────────────────────────────────────────── -->
		<div class="ds-tab-pane active" id="tab-pool" role="tabpanel">
			<?php if (empty($poolList)): ?>
			<div class="ds-table-wrap">
				<div class="ds-empty">
					<i class="fas fa-inbox"></i>
					<p>Belum ada data pool seni di gelanggang ini.</p>
				</div>
			</div>
			<?php else: ?>
			<div class="ds-table-wrap">
				<div class="ds-table-scroll">
					<table class="ds-table table table-striped align-middle mb-0" id="tabel-nilai-pool">
						<thead>
							<tr>
								<th style="width:40px;">No</th>
								<th>Urut</th>
								<th>Peserta / Kontingen</th>
								<th>Nilai Akhir</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($poolList as $idx => $row): ?>
							<tr>
								<td class="text-center"><?= $idx + 1 ?></td>
								<td class="text-center"><?= esc($row->nomor_urut ?? '-') ?></td>
								<td>
									<div class="fw-semibold"><?= esc($row->nama_pendaftar ?? '-') ?></div>
									<?php if (!empty($row->nama_kontingen)): ?>
									<small class="text-muted"><?= esc($row->nama_kontingen) ?></small>
									<?php endif ?>
								</td>
								<td class="text-center fw-bold" style="font-family:'Oswald',sans-serif;">
									<?php if ($row->diskualifikasi): ?>
									<span class="ds-badge ds-badge-solid-red">DQ</span>
									<?php else: ?>
									<?= number_format((float)($row->nilai_akhir ?? 0), 2) ?>
									<?php endif ?>
								</td>
								<td class="text-center">
									<span class="ds-badge <?= $row->diskualifikasi ? 'ds-badge-solid-red' : 'ds-badge-solid-green' ?>">
										<?= $row->diskualifikasi ? 'DQ' : 'OK' ?>
									</span>
								</td>
							</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif ?>
		</div>

		<!-- ── Battle ────────────────────────────────────────── -->
		<div class="ds-tab-pane" id="tab-battle" role="tabpanel">
			<?php if (empty($battleList)): ?>
			<div class="ds-table-wrap">
				<div class="ds-empty">
					<i class="fas fa-inbox"></i>
					<p>Belum ada data battle seni di gelanggang ini.</p>
				</div>
			</div>
			<?php else: ?>
			<div class="ds-table-wrap">
				<div class="ds-table-scroll">
					<table class="ds-table table table-striped align-middle mb-0" id="tabel-nilai-battle">
						<thead>
							<tr>
								<th style="width:40px;">No</th>
								<th>Babak</th>
								<th>Peserta Biru</th>
								<th>Nilai Biru</th>
								<th>Peserta Merah</th>
								<th>Nilai Merah</th>
								<th>Pemenang</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($battleList as $idx => $row): ?>
							<?php
								$nilaiBiru  = $row->dq_biru  ? '<span class="ds-badge ds-badge-solid-red">DQ</span>' : number_format((float)($row->nilai_biru  ?? 0), 2);
								$nilaiMerah = $row->dq_merah ? '<span class="ds-badge ds-badge-solid-red">DQ</span>' : number_format((float)($row->nilai_merah ?? 0), 2);
								$pemenang   = '-';
								if (!empty($row->id_biru) && !empty($row->id_merah)) {
									if (!$row->dq_biru && !$row->dq_merah) {
										if ((float)($row->nilai_biru ?? 0) > (float)($row->nilai_merah ?? 0)) {
											$pemenang = '<span class="ds-badge ds-badge-blue">Biru</span>';
										} elseif ((float)($row->nilai_merah ?? 0) > (float)($row->nilai_biru ?? 0)) {
											$pemenang = '<span class="ds-badge ds-badge-red">Merah</span>';
										} else {
											$pemenang = '<span class="ds-badge ds-badge-amber">Seri</span>';
										}
									} elseif ($row->dq_biru) {
										$pemenang = '<span class="ds-badge ds-badge-red">Merah</span>';
									} else {
										$pemenang = '<span class="ds-badge ds-badge-blue">Biru</span>';
									}
								}
							?>
							<tr>
								<td class="text-center"><?= $idx + 1 ?></td>
								<td class="text-center"><small><?= esc($row->babak ?? '-') ?></small></td>
								<td>
									<div class="fw-semibold"><?= esc($row->nama_biru ?? '-') ?></div>
									<?php if (!empty($row->kontingen_biru)): ?>
									<small class="text-muted"><?= esc($row->kontingen_biru) ?></small>
									<?php endif ?>
								</td>
								<td class="text-center fw-bold" style="font-family:'Oswald',sans-serif;"><?= $nilaiBiru ?></td>
								<td>
									<div class="fw-semibold"><?= esc($row->nama_merah ?? '-') ?></div>
									<?php if (!empty($row->kontingen_merah)): ?>
									<small class="text-muted"><?= esc($row->kontingen_merah) ?></small>
									<?php endif ?>
								</td>
								<td class="text-center fw-bold" style="font-family:'Oswald',sans-serif;"><?= $nilaiMerah ?></td>
								<td class="text-center"><?= $pemenang ?></td>
							</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif ?>
		</div>

	</div><!-- /ds-tab-content -->

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	if (typeof $.fn.DataTable === 'undefined') return;

	// Pool table — visible on load, initialize immediately
	$('#tabel-nilai-pool').DataTable({
		pageLength: 25,
		language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
		order: [[1, 'asc']],
		pagingType: 'simple_numbers',
		dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 px-2 pt-2"lf>rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 px-2 pb-2"ip>',
	});

	// Battle table — hidden in tab, init lazily
	var battleInitialized = false;
	var battleTab = document.querySelector('[data-bs-target="#tab-battle"]');

	if (battleTab) {
		battleTab.addEventListener('shown.bs.tab', function () {
			if (!battleInitialized) {
				$('#tabel-nilai-battle').DataTable({
					pageLength: 25,
					language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
					order: [[0, 'asc']],
					columnDefs: [{ orderable: false, targets: [6] }],
					pagingType: 'simple_numbers',
					dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 px-2 pt-2"lf>rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 px-2 pb-2"ip>',
				});
				battleInitialized = true;
			} else {
				$('#tabel-nilai-battle').DataTable().columns.adjust().draw();
			}
		});
	}
});
</script>
<?= $this->endSection() ?>
