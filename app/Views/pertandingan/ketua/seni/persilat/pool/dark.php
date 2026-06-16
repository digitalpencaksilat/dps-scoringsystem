<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/kp-seni.css') ?>">
<style>
html, body { height: 100%; overflow: hidden; margin: 0; }
body { background: #0a0e13; color: #fff; font-family: 'Poppins', sans-serif; }

#kp-seni-app {
	display: flex;
	flex-direction: column;
	height: 100dvh;
	overflow: hidden;
}

/* ─── Header ─────────────────────────────────────────────────────── */
.kps-header {
	flex-shrink: 0;
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
	padding: clamp(0.35rem, 0.8vw, 0.5rem);
	background: #0a0a0a;
	border-bottom: 1px solid rgba(255,255,255,0.06);
}

.kps-header-card {
	background: linear-gradient(180deg, #c5a017 0%, #9a7d12 100%);
	border: none;
	border-radius: 8px;
	padding: clamp(0.4rem, 1vw, 0.55rem) clamp(0.5rem, 1vw, 0.75rem);
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: space-between;
	gap: clamp(0.3rem, 0.8vw, 0.5rem);
}

.kps-header-card .kps-hc-label,
.kps-header-card .kps-hc-value,
.kps-header-card .kps-hc-sub {
	color: #fff;
}

.kps-hc-left {
	display: flex;
	flex-direction: column;
	gap: 0.1rem;
	min-width: 0;
}

.kps-hc-label {
	font-size: clamp(0.52rem, 1vw, 0.6rem);
	display: flex;
	align-items: center;
	gap: 0.25rem;
	opacity: 0.75;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.kps-hc-name {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.7rem, 1.5vw, 0.9rem);
	font-weight: 700;
	line-height: 1.15;
	color: #fff;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.kps-hc-number {
	flex-shrink: 0;
	display: flex;
	flex-direction: column;
	align-items: center;
	gap: 0.05rem;
}

.kps-hc-num-label {
	font-size: clamp(0.5rem, 1vw, 0.58rem);
	opacity: 0.7;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.kps-hc-num-value {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1.3rem, 3.5vw, 2rem);
	font-weight: 700;
	color: #fff;
	line-height: 1;
}

.kps-hc-sub {
	font-size: clamp(0.52rem, 1vw, 0.6rem);
	opacity: 0.7;
	white-space: nowrap;
}

/* ─── Tabs ───────────────────────────────────────────────────────── */
.kps-tabs {
	flex-shrink: 0;
	display: flex;
	padding: 4px clamp(0.5rem, 1vw, 0.75rem);
	border-bottom: 1px solid rgba(255,255,255,0.06);
	background: #0d0f12;
}

.kps-tabs .nav-link {
	background: transparent;
	color: rgba(255,255,255,0.5);
	border: none;
	border-radius: 6px;
	font-size: clamp(0.75rem, 1.5vw, 0.85rem);
	font-weight: 500;
	padding: 0.35rem 0.75rem;
	transition: all 0.2s ease;
	display: flex;
	align-items: center;
	gap: 0.35rem;
	white-space: nowrap;
}

.kps-tabs .nav-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
.kps-tabs .nav-link.active {
	background: rgba(197,160,23,0.15);
	color: #c5a017;
	border: 1px solid rgba(197,160,23,0.25);
}

/* ─── Tab Content ────────────────────────────────────────────────── */
.kps-tab-content {
	flex: 1;
	overflow-y: auto;
	padding: clamp(0.5rem, 1vw, 0.75rem);
}

.kps-tab-content::-webkit-scrollbar { width: 5px; }
.kps-tab-content::-webkit-scrollbar-track { background: transparent; }
.kps-tab-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }

/* ─── Peserta Card ───────────────────────────────────────────────── */
.kps-peserta-card {
	background: rgba(255,255,255,0.05);
	border: 1px solid rgba(255,255,255,0.1);
	border-radius: 8px;
	padding: clamp(0.6rem, 1.5vw, 0.85rem) clamp(0.75rem, 1.5vw, 1rem);
	margin-bottom: clamp(0.5rem, 1vw, 0.75rem);
	text-align: center;
}

.kps-peserta-nama {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1rem, 2.5vw, 1.3rem);
	font-weight: 700;
	margin: 0;
	line-height: 1.3;
	color: #fff;
}

.kps-peserta-kontingen {
	font-size: clamp(0.75rem, 1.5vw, 0.85rem);
	color: rgba(255,255,255,0.55);
	margin: 2px 0 0 0;
}

/* ─── Data Table ─────────────────────────────────────────────────── */
.kps-table-wrap {
	margin-bottom: clamp(0.5rem, 1vw, 0.75rem);
	background: rgba(255,255,255,0.02);
	border: 1px solid rgba(255,255,255,0.06);
	border-radius: 8px;
	overflow: hidden;
}

.kps-table {
	width: 100%;
	font-size: clamp(0.7rem, 1.4vw, 0.82rem);
	margin: 0;
	color: #fff;
}

.kps-table thead { background: rgba(255,255,255,0.04); }

.kps-table thead th {
	padding: clamp(0.4rem, 0.8vw, 0.5rem) clamp(0.3rem, 0.6vw, 0.5rem);
	font-weight: 600;
	text-transform: uppercase;
	font-size: clamp(0.6rem, 1.2vw, 0.7rem);
	color: rgba(255,255,255,0.5);
	letter-spacing: 0.5px;
	text-align: center;
	border-bottom: 1px solid rgba(255,255,255,0.06);
}

.kps-table tbody td {
	padding: clamp(0.35rem, 0.7vw, 0.45rem) clamp(0.3rem, 0.6vw, 0.5rem);
	text-align: center;
	vertical-align: middle;
	border-bottom: 1px solid rgba(255,255,255,0.03);
	font-weight: 500;
}

.kps-table tbody tr:last-child td { border-bottom: none; }
.kps-table tbody tr.total-row td { font-weight: 700; border-top: 1px solid rgba(197,160,23,0.3); }

/* ─── Sorted Jury ────────────────────────────────────────────────── */
.kps-sorted-bar {
	background: rgba(255,255,255,0.03);
	border: 1px solid rgba(255,255,255,0.06);
	border-radius: 8px;
	padding: clamp(0.5rem, 1vw, 0.75rem);
	margin-bottom: clamp(0.5rem, 1vw, 0.75rem);
}

.kps-sorted-title {
	font-size: clamp(0.65rem, 1.3vw, 0.72rem);
	font-weight: 600;
	text-transform: uppercase;
	color: rgba(255,255,255,0.45);
	letter-spacing: 1px;
	margin: 0 0 0.5rem 0;
	text-align: center;
}

.kps-sorted-row {
	display: flex;
	gap: clamp(0.25rem, 0.5vw, 0.4rem);
}

.kps-sorted-cell {
	flex: 1;
	background: rgba(255,255,255,0.05);
	border-radius: 6px;
	padding: 0.4rem 0.25rem;
	text-align: center;
}

.kps-sorted-cell .juri-num {
	font-size: clamp(0.55rem, 1.1vw, 0.65rem);
	color: rgba(255,255,255,0.4);
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.kps-sorted-cell .juri-val {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1rem, 2.5vw, 1.5rem);
	font-weight: 700;
	color: #fff;
}

.kps-sorted-cell.terpilih {
	background: rgba(197,160,23,0.12);
	border: 1px solid rgba(197,160,23,0.25);
}

/* ─── Stat Cards 3×2 ─────────────────────────────────────────────── */
.kps-stats-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: clamp(0.3rem, 0.6vw, 0.4rem);
	margin-bottom: clamp(0.5rem, 1vw, 0.75rem);
}

.kps-stat-card {
	background: rgba(255,255,255,0.05);
	border: 1px solid rgba(255,255,255,0.1);
	border-radius: 8px;
	overflow: hidden;
	text-align: center;
}

.kps-stat-label {
	font-size: clamp(0.55rem, 1.1vw, 0.65rem);
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: rgba(255,255,255,0.45);
	padding: 0.3rem 0.25rem;
	background: rgba(255,255,255,0.04);
}

.kps-stat-value {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(1rem, 2.5vw, 1.5rem);
	font-weight: 700;
	padding: 0.35rem 0.25rem;
	color: #fff;
}

.kps-stat-card.final .kps-stat-value {
	background: linear-gradient(180deg, #c5a017 0%, #9a7d12 100%);
}

/* ─── Hukuman Section ────────────────────────────────────────────── */
.kps-hukuman-wrap {
	background: rgba(255,255,255,0.02);
	border: 1px solid rgba(255,255,255,0.06);
	border-radius: 8px;
	overflow: hidden;
	margin-bottom: 0.5rem;
}

.kps-hukuman-row {
	display: flex;
	border-bottom: 1px solid rgba(255,255,255,0.04);
}

.kps-hukuman-row:last-child { border-bottom: none; }

.kps-hukuman-label {
	flex: 7;
	padding: 0.4rem 0.6rem;
	font-size: clamp(0.7rem, 1.3vw, 0.8rem);
	font-weight: 500;
	color: rgba(255,255,255,0.7);
	display: flex;
	align-items: center;
}

.kps-hukuman-val {
	flex: 5;
	padding: 0.4rem;
	text-align: center;
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.9rem, 2vw, 1.2rem);
	font-weight: 700;
	color: #ef5350;
	display: flex;
	align-items: center;
	justify-content: center;
	background: rgba(198,40,40,0.1);
	border-left: 1px solid rgba(198,40,40,0.15);
}

/* ─── Summary Table ──────────────────────────────────────────────── */
.kps-summary-table {
	width: 100%;
	font-size: clamp(0.65rem, 1.3vw, 0.78rem);
	margin: 0 0 0.75rem 0;
	color: #fff;
}

.kps-summary-table thead th {
	background: rgba(255,255,255,0.04);
	padding: 0.4rem 0.3rem;
	font-size: clamp(0.55rem, 1.1vw, 0.65rem);
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	color: rgba(255,255,255,0.45);
	text-align: center;
	border-bottom: 1px solid rgba(255,255,255,0.08);
}

.kps-summary-table tbody td {
	padding: 0.35rem 0.3rem;
	text-align: center;
	vertical-align: middle;
	border-bottom: 1px solid rgba(255,255,255,0.03);
}

.kps-summary-table .nama-cell {
	text-align: left;
	font-weight: 500;
}

.kps-summary-table .kontingen-cell {
	display: block;
	font-size: clamp(0.55rem, 1.1vw, 0.65rem);
	color: rgba(255,255,255,0.4);
}

/* ─── Section Title ──────────────────────────────────────────────── */
.kps-section-title {
	font-size: clamp(0.65rem, 1.3vw, 0.75rem);
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 1px;
	color: rgba(255,255,255,0.35);
	margin: 0 0 0.4rem 0;
	text-align: center;
	padding: 0.35rem 0;
	background: rgba(255,255,255,0.02);
	border-radius: 6px;
	border: 1px solid rgba(255,255,255,0.04);
}

/* ─── Disqualified Badge ─────────────────────────────────────────── */
.kps-disq {
	display: inline-flex;
	padding: 0.15rem 0.4rem;
	font-size: 0.6rem;
	font-weight: 700;
	text-transform: uppercase;
	color: #fff;
	background: #c62828;
	border-radius: 4px;
}

/* ─── Responsive ─────────────────────────────────────────────────── */
@media (max-width: 768px) {
	.kps-stats-grid { grid-template-columns: repeat(3, 1fr); }
	.kps-sorted-row { flex-wrap: wrap; }
	.kps-sorted-cell { min-width: 80px; }
}

@media (orientation: landscape) and (max-height: 500px) {
	.kps-tab-content { padding: 0.35rem; }
	.kps-stats-grid { gap: 0.2rem; }
	.kps-stat-label { font-size: 0.5rem; padding: 0.15rem; }
	.kps-stat-value { font-size: 0.85rem; padding: 0.2rem; }
	.kps-header { padding: 0.25rem; }
	.kps-peserta-card { padding: 0.4rem; margin-bottom: 0.35rem; }
	.kps-peserta-nama { font-size: 0.85rem; }
	.kps-table-wrap { margin-bottom: 0.35rem; }
	.kps-table { font-size: 0.6rem; }
	.kps-sorted-bar { padding: 0.35rem; margin-bottom: 0.35rem; }
	.kps-sorted-cell .juri-val { font-size: 1rem; }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="kp-seni-app">
	<!-- Header -->
	<div class="kps-header">
		<div class="kps-header-card">
			<div class="kps-hc-left">
				<div class="kps-hc-label"><i class="fas fa-map-marker-alt"></i> <?= esc($nama_gelanggang ?? '-') ?></div>
				<div class="kps-hc-sub"><?= esc($penampilan_seni_berlangsung->nama_seni ?? 'Seni') ?></div>
			</div>
			<div class="kps-hc-number">
				<span class="kps-hc-num-label">Partai</span>
				<span class="kps-hc-num-value"><?= esc($nomor_partai ?? '-') ?></span>
			</div>
		</div>
		<div class="kps-header-card">
			<div class="kps-hc-left">
				<div class="kps-hc-label"><i class="fas fa-user"></i> <?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?> · <?= esc($penampilan_seni_berlangsung->nama_kategori_usia ?? '') ?></div>
				<div class="kps-hc-sub">Pool</div>
			</div>
			<div class="kps-hc-number">
				<span class="kps-hc-num-value"><?= esc($penampilan_seni_berlangsung->nomor_pool ?? '-') ?></span>
			</div>
		</div>
	</div>

	<!-- Tabs -->
	<ul class="nav kps-tabs" role="tablist" id="tabNilai">
		<li class="nav-item">
			<button class="nav-link active" data-bs-toggle="tab" data-bs-target="#now_performing" type="button" role="tab" aria-selected="true" id="nowPerformingNav">
				<i class="fas fa-play"></i> Now Performing
			</button>
		</li>
		<li class="nav-item">
			<button class="nav-link" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab" aria-selected="false" id="summaryNav">
				<i class="fas fa-table-list"></i> Summary
			</button>
		</li>
	</ul>

	<div class="tab-content kps-tab-content">
		<!-- TAB: NOW PERFORMING -->
		<div class="tab-pane active" id="now_performing" role="tabpanel">

			<div class="kps-peserta-card">
				<p class="kps-peserta-nama"><?= str_replace('<br>', ' ', $penampilan_seni_berlangsung->anggota_kelompok_peserta_seni ?? '-') ?></p>
				<p class="kps-peserta-kontingen"><?= $penampilan_seni_berlangsung->nama_kontingen ?? '-' ?></p>
			</div>

			<?php $idNow = (int) $penampilan_seni_berlangsung->id_penampilan_seni; ?>
			<?php if (!empty($data_nilai[$idNow])): ?>
			<?php $juriNow = $data_nilai[$idNow]; ?>

			<!-- Unsur Nilai Table -->
			<div class="kps-table-wrap penampilan_seni_<?= $idNow ?>">
				<table class="kps-table table-sm">
					<thead>
						<tr>
							<th style="width:25%">Unsur</th>
							<?php for ($ji = 1; $ji <= count($juriNow); $ji++): ?>
								<th>J<?= $ji ?></th>
							<?php endfor ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($jenis_unsur_nilai as $jenis): ?>
						<tr>
							<td style="text-align:left;padding-left:0.5rem;font-weight:600;text-transform:capitalize;"><?= ucwords(str_replace('_', ' ', $jenis)) ?></td>
							<?php foreach ($juriNow as $juri): ?>
								<td class="<?= $jenis ?>_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
							<?php endforeach ?>
						</tr>
						<?php endforeach; ?>
						<tr class="total-row">
							<td style="text-align:left;padding-left:0.5rem;">Total</td>
							<?php foreach ($juriNow as $juri): ?>
								<td class="total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
							<?php endforeach ?>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Sorted Jury Score -->
			<div class="kps-sorted-bar penampilan_seni_<?= $idNow ?>">
				<p class="kps-sorted-title">Sorted Jury Score</p>
				<div class="kps-sorted-row urutan_total_nilai_juri">
					<?php foreach ($juriNow as $juri): ?>
					<div class="kps-sorted-cell kolom_total_nilai">
						<div class="juri-num nomor_juri"></div>
						<div class="juri-val total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>">0</div>
					</div>
					<?php endforeach ?>
				</div>
			</div>

			<?php endif; ?>

			<!-- Stat Cards: Median / Penalty / Final / StdDev / MedKebenaran / Time -->
			<div class="kps-stats-grid penampilan_seni_<?= $idNow ?>">
				<div class="kps-stat-card">
					<div class="kps-stat-label">Median</div>
					<div class="kps-stat-value median_<?= $idNow ?>">0</div>
				</div>
				<div class="kps-stat-card">
					<div class="kps-stat-label">Penalty</div>
					<div class="kps-stat-value hukuman_<?= $idNow ?>">0</div>
				</div>
				<div class="kps-stat-card">
					<div class="kps-stat-label">Med Kebenaran</div>
					<div class="kps-stat-value kebenaran_median_<?= $idNow ?>">0</div>
				</div>
				<div class="kps-stat-card">
					<div class="kps-stat-label">Std Dev</div>
					<div class="kps-stat-value standar_deviasi_<?= $idNow ?>">0</div>
				</div>
				<div class="kps-stat-card">
					<div class="kps-stat-label">Time</div>
					<div class="kps-stat-value waktu_<?= $idNow ?>">0</div>
				</div>
				<div class="kps-stat-card final">
					<div class="kps-stat-label">Final Score</div>
					<div class="kps-stat-value nilai_akhir_<?= $idNow ?>">0</div>
				</div>
			</div>

			<!-- Hukuman Detail -->
			<?php if (!empty($data_nilai[$idNow])): ?>
			<?php
				$sampel = json_decode($juriNow[0]->penilaian ?? '{}');
				$hukumanList = $sampel->penilaian->hukuman ?? null;
			?>
			<?php if ($hukumanList !== null): ?>
			<div class="kps-hukuman-wrap penampilan_seni_<?= $idNow ?>">
				<?php foreach ($hukumanList as $jenisHukuman => $valueHukuman): ?>
				<div class="kps-hukuman-row">
					<div class="kps-hukuman-label"><?= $valueHukuman->metadata->label ?? ucwords(str_replace('_', ' ', $jenisHukuman)) ?></div>
					<div class="kps-hukuman-val nilai_hukuman_<?= $jenisHukuman ?>">0</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>

		<!-- TAB: SUMMARY -->
		<div class="tab-pane" id="summary" role="tabpanel">

			<!-- Final Score Table -->
			<div class="kps-section-title">Final Score</div>
			<div class="kps-table-wrap">
				<table class="kps-summary-table table-sm" id="tabelSummaryPenampilan">
					<thead>
						<tr>
							<th>#</th>
							<th style="text-align:left;">Nama</th>
							<th>Med</th>
							<th>Pen</th>
							<th>Med Keb</th>
							<th>Std D</th>
							<th>Time</th>
							<th>Final</th>
							<th>Disq</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($semua_penampilan_seni as $idx => $pnSeni): ?>
						<tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>">
							<td><?= $idx + 1 ?></td>
							<td class="nama-cell">
								<?= str_replace('<br>', ' ', $pnSeni->anggota_kelompok_peserta_seni ?? '-') ?>
								<span class="kontingen-cell"><?= $pnSeni->nama_kontingen ?? '' ?></span>
							</td>
							<td class="median_<?= $pnSeni->id_penampilan_seni ?>"></td>
							<td class="hukuman_<?= $pnSeni->id_penampilan_seni ?>"></td>
							<td class="kebenaran_median_<?= $pnSeni->id_penampilan_seni ?>"></td>
							<td class="standar_deviasi_<?= $pnSeni->id_penampilan_seni ?>"></td>
							<td class="waktu_<?= $pnSeni->id_penampilan_seni ?>"><?= date("i:s", $pnSeni->waktu_tampil ?? 0) ?></td>
							<td class="nilai_akhir_<?= $pnSeni->id_penampilan_seni ?>"><?= number_format($pnSeni->nilai_akhir ?? 0, 3) ?></td>
							<td class="keterangan_<?= $pnSeni->id_penampilan_seni ?>">
								<?= ($pnSeni->diskualifikasi ?? 0) == 1 ? '<span class="kps-disq">Disq</span>' : '' ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- All Jury Score Table -->
			<?php $firstKey = array_key_first($data_nilai); ?>
			<div class="kps-section-title">All Jury Score</div>
			<div class="kps-table-wrap">
				<table class="kps-summary-table table-sm">
					<thead>
						<tr>
							<th>#</th>
							<th style="text-align:left;">Nama</th>
							<?php if ($firstKey !== null): ?>
								<?php for ($ji = 1; $ji <= count($data_nilai[$firstKey]); $ji++): ?>
									<th>J<?= $ji ?></th>
								<?php endfor ?>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($semua_penampilan_seni as $num => $pnSeni): ?>
						<tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>">
							<td><?= $num + 1 ?></td>
							<td class="nama-cell">
								<?= str_replace('<br>', ' ', $pnSeni->anggota_kelompok_peserta_seni ?? '-') ?>
								<span class="kontingen-cell"><?= $pnSeni->nama_kontingen ?? '' ?></span>
							</td>
							<?php if (isset($data_nilai[(int) $pnSeni->id_penampilan_seni])): ?>
								<?php foreach ($data_nilai[(int) $pnSeni->id_penampilan_seni] as $penilaian): ?>
									<td class="nilai_akhir_juri_<?= $penilaian->id_perangkat_pertandingan ?> juri_<?= $penilaian->id_perangkat_pertandingan ?>"></td>
								<?php endforeach; ?>
							<?php endif; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- Per Unsur Nilai Tables -->
			<?php foreach ($jenis_unsur_nilai as $jenis): ?>
			<div class="kps-section-title"><?= ucwords(str_replace('_', ' ', $jenis)) ?></div>
			<div class="kps-table-wrap">
				<table class="kps-summary-table table-sm">
					<thead>
						<tr>
							<th>#</th>
							<th style="text-align:left;">Nama</th>
							<?php if ($firstKey !== null): ?>
								<?php for ($ji = 1; $ji <= count($data_nilai[$firstKey]); $ji++): ?>
									<th>J<?= $ji ?></th>
								<?php endfor ?>
							<?php endif; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($semua_penampilan_seni as $num => $pnSeni): ?>
						<tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>">
							<td><?= $num + 1 ?></td>
							<td class="nama-cell">
								<?= str_replace('<br>', ' ', $pnSeni->anggota_kelompok_peserta_seni ?? '-') ?>
								<span class="kontingen-cell"><?= $pnSeni->nama_kontingen ?? '' ?></span>
							</td>
							<?php if (isset($data_nilai[(int) $pnSeni->id_penampilan_seni])): ?>
								<?php foreach ($data_nilai[(int) $pnSeni->id_penampilan_seni] as $penilaian): ?>
									<td class="<?= $jenis ?>_juri_<?= $penilaian->id_perangkat_pertandingan ?> juri_<?= $penilaian->id_perangkat_pertandingan ?>"></td>
								<?php endforeach; ?>
							<?php endif; ?>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/kp_seni_persilat.js') ?>"></script>
<script>
	var $data_nilai = <?= json_encode($data_nilai, JSON_NUMERIC_CHECK) ?>;
	var $penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung, JSON_NUMERIC_CHECK) ?>;
	var $semua_penampilan_seni = <?= json_encode($semua_penampilan_seni, JSON_NUMERIC_CHECK) ?>;
	var $autorefresh = true;

	$(document).ready(function() {
		ketua_pertandingan.init(
			<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>,
			$data_nilai,
			$penampilan_seni_berlangsung,
			$semua_penampilan_seni,
			$autorefresh
		);
	});
</script>
<?= $this->endSection() ?>
