<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
:root {
	--dn-surface: rgba(255,255,255,0.03);
	--dn-surface-hover: rgba(255,255,255,0.06);
	--dn-border: rgba(255,255,255,0.07);
	--dn-text: rgba(255,255,255,0.9);
	--dn-text-muted: rgba(255,255,255,0.5);
	--dn-accent: #c60000;
	--dn-gold: #c5a017;
	--dn-blue: #1565c0;
	--dn-green: #198754;
	--dn-red: #dc3545;
	--dn-amber: #f59e0b;
}

html, body { height: 100%; overflow: hidden; margin: 0; }

body {
	display: flex;
	flex-direction: column;
	background:
		radial-gradient(ellipse at top, rgba(198,0,0,0.06) 0%, transparent 60%),
		linear-gradient(180deg, #0a0d12 0%, #0f1419 100%);
	font-family: 'Poppins', sans-serif;
	color: var(--dn-text);
}

.navbar-custom { flex-shrink: 0; }

/* ═══ Wrapper ════════════════════════════════════════════════════════ */
#dn-wrapper {
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
.dn-header {
	flex-shrink: 0;
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: clamp(0.5rem, 1.5vw, 1rem);
	padding: clamp(0.3rem, 0.8vw, 0.5rem) 0;
}

.dn-header-left {
	display: flex;
	align-items: center;
	gap: clamp(0.4rem, 1vw, 0.7rem);
	min-width: 0;
}

.dn-icon {
	width: clamp(2.2rem, 4.5vw, 2.8rem);
	height: clamp(2.2rem, 4.5vw, 2.8rem);
	border-radius: 8px;
	background: linear-gradient(135deg, var(--dn-accent) 0%, #890108 100%);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: clamp(0.95rem, 2vw, 1.2rem);
	color: #fff;
	flex-shrink: 0;
	box-shadow: 0 3px 10px rgba(198,0,0,0.3);
}

.dn-header-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.9rem, 2.2vw, 1.2rem);
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	line-height: 1.2;
	margin: 0;
}

.dn-header-sub {
	font-size: clamp(0.65rem, 1.3vw, 0.75rem);
	color: var(--dn-text-muted);
	line-height: 1.3;
	margin: 0;
}

.dn-header-right {
	display: flex;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
	flex-shrink: 0;
}

.dn-btn-back {
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
	padding: clamp(0.35rem, 0.8vw, 0.5rem) clamp(0.5rem, 1.2vw, 0.8rem);
	border-radius: 6px;
	font-size: clamp(0.7rem, 1.4vw, 0.8rem);
	font-weight: 600;
	color: var(--dn-text);
	background: rgba(255,255,255,0.06);
	border: 1px solid rgba(255,255,255,0.1);
	text-decoration: none;
	transition: all 0.2s;
	white-space: nowrap;
}

.dn-btn-back:hover { background: rgba(198,0,0,0.15); border-color: var(--dn-accent); color: #fff; }

.dn-badge-count {
	display: inline-flex;
	align-items: center;
	gap: 0.3rem;
	padding: clamp(0.3rem, 0.7vw, 0.45rem) clamp(0.4rem, 1vw, 0.65rem);
	border-radius: 6px;
	background: rgba(197,160,23,0.1);
	border: 1px solid rgba(197,160,23,0.2);
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.7rem, 1.4vw, 0.8rem);
	color: var(--dn-gold);
	font-weight: 600;
}

#dn-wrapper .text-muted,
#dn-wrapper small.text-muted {
	color: rgba(255,255,255,0.55) !important;
}

#dn-wrapper small {
	color: rgba(255,255,255,0.55);
}

/* ═══ Table Container ════════════════════════════════════════════════ */
.dn-table-wrap {
	flex: 1;
	display: flex;
	flex-direction: column;
	overflow: hidden;
	background: rgba(255,255,255,0.02);
	border: 1px solid var(--dn-border);
	border-radius: 10px;
}

.dn-table-scroll {
	flex: 1;
	overflow: auto;
}

.dn-table-scroll .dataTables_wrapper {
	padding: 0;
	height: 100%;
	display: flex;
	flex-direction: column;
}

.dn-table-scroll .dataTables_wrapper .dataTables_scroll {
	flex: 1;
	display: flex;
	flex-direction: column;
	overflow: hidden;
}

.dn-table-scroll .dataTables_scrollBody {
	flex: 1 !important;
	overflow-y: auto !important;
	max-height: none !important;
}

/* ═══ DataTables Dark Styling ════════════════════════════════════════ */
.dn-table-scroll .dataTables_length,
.dn-table-scroll .dataTables_filter,
.dn-table-scroll .dataTables_info,
.dn-table-scroll .dataTables_paginate {
	color: var(--dn-text-muted);
	font-size: clamp(0.65rem, 1.3vw, 0.75rem);
	padding: clamp(0.3rem, 0.7vw, 0.5rem) clamp(0.4rem, 1vw, 0.6rem);
}

.dn-table-scroll .dataTables_length select,
.dn-table-scroll .dataTables_filter input {
	background: rgba(255,255,255,0.06);
	border: 1px solid rgba(255,255,255,0.12);
	border-radius: 4px;
	color: #ddd;
	padding: 0.25rem 0.5rem;
	font-size: 0.75rem;
}

.dn-table-scroll .dataTables_filter input:focus {
	outline: none;
	border-color: var(--dn-accent);
	box-shadow: 0 0 0 1px rgba(198,0,0,0.2);
}

.dn-table-scroll .dataTables_length select {
	padding-right: 1.5rem;
	background: rgba(255,255,255,0.06);
	border: 1px solid rgba(255,255,255,0.12);
	color: #ddd;
}

.dn-table-scroll .dataTables_length select option {
	background: #1a1d22;
	color: #ddd;
}

.dn-table-scroll .paginate_button {
	background: rgba(255,255,255,0.04) !important;
	border: 1px solid rgba(255,255,255,0.08) !important;
	border-radius: 4px !important;
	color: var(--dn-text-muted) !important;
	padding: 0.25rem 0.6rem !important;
	font-size: 0.75rem !important;
	margin: 0 1px !important;
	transition: all 0.15s !important;
}

.dn-table-scroll .paginate_button:hover {
	background: rgba(198,0,0,0.15) !important;
	border-color: var(--dn-accent) !important;
	color: #fff !important;
}

.dn-table-scroll .paginate_button.current {
	background: linear-gradient(135deg, var(--dn-accent), #900000) !important;
	border-color: var(--dn-accent) !important;
	color: #fff !important;
	font-weight: 700;
}

.dn-table-scroll .paginate_button.disabled {
	opacity: 0.35 !important;
	pointer-events: none;
	color: var(--dn-text-muted) !important;
}

/* ═══ Table ═══════════════════════════════════════════════════════════ */
.dn-table {
	--bs-table-bg: transparent;
	--bs-table-color: var(--dn-text);
	--bs-table-striped-color: var(--dn-text);
	--bs-table-striped-bg: rgba(255,255,255,0.015);
	--bs-table-border-color: rgba(255,255,255,0.06);
	--bs-table-hover-color: #fff;
	--bs-table-hover-bg: var(--dn-surface-hover);
	font-size: clamp(0.72rem, 1.5vw, 0.82rem);
	margin: 0 !important;
	width: 100% !important;
}

.dn-table thead th {
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

.dn-table tbody td {
	background: transparent;
	padding: clamp(0.3rem, 0.7vw, 0.45rem) clamp(0.3rem, 0.6vw, 0.45rem);
	vertical-align: middle;
	border-color: var(--dn-border);
}

.dn-table tbody tr:hover td {
	background: var(--dn-surface-hover);
}

/* ═══ Status Badges ═══════════════════════════════════════════════════ */
.dn-badge {
	font-size: clamp(0.58rem, 1.1vw, 0.65rem);
	padding: 0.2rem 0.55rem;
	border-radius: 1rem;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.3px;
	white-space: nowrap;
}

.dn-badge-blue { background: rgba(21,101,192,0.2); color: #90caf9; border: 1px solid rgba(21,101,192,0.3); }
.dn-badge-red { background: rgba(198,40,40,0.2); color: #ef9a9a; border: 1px solid rgba(198,40,40,0.3); }
.dn-badge-green { background: rgba(25,135,84,0.2); color: #a5d6a7; border: 1px solid rgba(25,135,84,0.3); }
.dn-badge-amber { background: rgba(245,158,11,0.15); color: #ffcc80; border: 1px solid rgba(245,158,11,0.25); }
.dn-badge-muted { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.45); }

.dn-badge-solid-blue { background: var(--dn-blue); color: #fff; }
.dn-badge-solid-red { background: var(--dn-red); color: #fff; }
.dn-badge-solid-green { background: var(--dn-green); color: #fff; }
.dn-badge-solid-amber { background: var(--dn-amber); color: #212529; }

/* ═══ Empty State ═════════════════════════════════════════════════════ */
.dn-empty {
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

.dn-empty i {
	font-size: clamp(1.8rem, 4vw, 2.5rem);
	opacity: 0.4;
	margin-bottom: 0.25rem;
}

.dn-empty p {
	font-size: clamp(0.78rem, 1.5vw, 0.88rem);
	max-width: 320px;
	line-height: 1.5;
}

/* ═══ Scrollbar ═══════════════════════════════════════════════════════ */
.dn-table-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
.dn-table-scroll::-webkit-scrollbar-track { background: transparent; }
.dn-table-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
.dn-table-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.15); }

.dn-table-scroll .dataTables_scrollBody::-webkit-scrollbar { width: 5px; }
.dn-table-scroll .dataTables_scrollBody::-webkit-scrollbar-track { background: transparent; }
.dn-table-scroll .dataTables_scrollBody::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }

/* ═══ Responsive ══════════════════════════════════════════════════════ */
@media (max-width: 768px) {
	.dn-header { flex-wrap: wrap; }
	.dn-header-right { width: 100%; justify-content: flex-end; }
}

@media (max-width: 480px) {
	.dn-header-left { flex-wrap: wrap; }
	.dn-table { font-size: 0.7rem; }
}

@media (orientation: landscape) and (max-height: 500px) {
	#dn-wrapper { padding: clamp(0.25rem, 0.8vw, 0.4rem); gap: 0.3rem; }
	.dn-header { padding: 0.15rem 0; }
	.dn-icon { width: 1.8rem; height: 1.8rem; }
}

@media (prefers-reduced-motion: reduce) {
	.dn-btn-back, .paginate_button { transition: none !important; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'daftar_nilai_tanding']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="dn-wrapper">

	<!-- ═══ HEADER ═══ -->
	<div class="dn-header">
		<div class="dn-header-left">
			<div class="dn-icon">
				<i class="fas fa-clipboard-check"></i>
			</div>
			<div>
				<div class="dn-header-title">Daftar Nilai Tanding</div>
				<div class="dn-header-sub">Rekap semua pertandingan di gelanggang ini</div>
			</div>
		</div>
		<div class="dn-header-right">
			<span class="dn-badge-count">
				<i class="fas fa-list-ol"></i>
				<?= count($pertandinganList ?? []) ?> Partai
			</span>
			<a href="<?= base_url('ketua-pertandingan') ?>" class="dn-btn-back">
				<i class="fas fa-arrow-left"></i> Kembali
			</a>
		</div>
	</div>

	<!-- ═══ TABLE ═══ -->
	<?php if (empty($pertandinganList)): ?>
	<div class="dn-table-wrap">
		<div class="dn-empty">
			<i class="fas fa-inbox"></i>
			<p>Belum ada data pertandingan di gelanggang ini.</p>
		</div>
	</div>
	<?php else: ?>
	<div class="dn-table-wrap">
		<div class="dn-table-scroll">
			<table class="dn-table table table-striped align-middle mb-0" id="tabel-nilai-tanding">
				<thead>
					<tr>
						<th>No</th>
						<th>Partai</th>
						<th>Babak</th>
						<th>Atlet Biru</th>
						<th>Atlet Merah</th>
						<th>Skor</th>
						<th>Pemenang</th>
						<th>Kemenangan</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($pertandinganList as $idx => $p): ?>
					<?php
						$statusBadge = match($p->status_pertandingan ?? '') {
							'berlangsung'  => 'dn-badge-solid-blue',
							'selesai'      => 'dn-badge-solid-green',
							'berhenti'     => 'dn-badge-solid-red',
							default        => 'dn-badge-muted',
						};
						$statusLabel = match($p->status_pertandingan ?? '') {
							'belum_dimulai' => 'Belum',
							'berlangsung'   => 'Berlangsung',
							'selesai'       => 'Selesai',
							'berhenti'      => 'Berhenti',
							'standby'       => 'Standby',
							'istirahat'     => 'Istirahat',
							default         => ucfirst($p->status_pertandingan ?? '-'),
						};
						$pemenang = match($p->pemenang ?? '') {
							'biru'  => '<span class="dn-badge dn-badge-blue">Biru</span>',
							'merah' => '<span class="dn-badge dn-badge-red">Merah</span>',
							default => '-',
						};
						$skorBiru = (int)($p->skor_biru ?? 0);
						$skorMerah = (int)($p->skor_merah ?? 0);
					?>
					<tr>
						<td class="text-center" style="width:40px;"><?= $idx + 1 ?></td>
						<td class="fw-semibold text-center">P<?= esc($p->nomor_partai) ?></td>
						<td class="text-center"><small><?= esc($p->babak ?? '-') ?></small></td>
						<td>
							<div class="fw-semibold"><?= esc($p->nama_biru ?? '-') ?></div>
							<?php if (!empty($p->kontingen_biru)): ?>
							<small class="text-muted"><?= esc($p->kontingen_biru) ?></small>
							<?php endif ?>
						</td>
						<td>
							<div class="fw-semibold"><?= esc($p->nama_merah ?? '-') ?></div>
							<?php if (!empty($p->kontingen_merah)): ?>
							<small class="text-muted"><?= esc($p->kontingen_merah) ?></small>
							<?php endif ?>
						</td>
						<td class="text-center fw-bold" style="font-family:'Oswald',sans-serif;">
							<?= $skorBiru ?> &ndash; <?= $skorMerah ?>
						</td>
						<td class="text-center"><?= $pemenang ?></td>
						<td class="text-center"><small><?= esc($p->jenis_kemenangan ?? '-') ?></small></td>
						<td class="text-center"><span class="dn-badge <?= $statusBadge ?>"><?= $statusLabel ?></span></td>
					</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
	<?php endif ?>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
	if (typeof $.fn.DataTable === 'undefined') return;

	$('#tabel-nilai-tanding').DataTable({
		pageLength: 25,
		language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
		order: [[0, 'asc']],
		columnDefs: [
			{ orderable: false, targets: [6, 7] },
		],
		scrollY: false,
		pagingType: 'simple_numbers',
		dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 px-2 pt-2"lf>rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 px-2 pb-2"ip>',
	});
});
</script>
<?= $this->endSection() ?>
