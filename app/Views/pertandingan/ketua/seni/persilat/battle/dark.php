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

/* ─── Header ───────────────────────────────────── */
.kps-header { flex-shrink: 0; display: grid; grid-template-columns: 1fr 1fr; gap: clamp(0.3rem, 0.8vw, 0.5rem); padding: clamp(0.35rem, 0.8vw, 0.5rem); background: #0a0a0a; border-bottom: 1px solid rgba(255,255,255,0.06); }
.kps-header-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: clamp(0.35rem, 0.8vw, 0.5rem) clamp(0.5rem, 1vw, 0.75rem); display: flex; flex-direction: column; justify-content: center; gap: 0.15rem; }
.kps-header-card.arena { border-left: 3px solid #1565c0; background: linear-gradient(135deg, rgba(21,101,192,0.1) 0%, rgba(255,255,255,0.05) 100%); }
.kps-header-card.seni  { border-left: 3px solid #c5a017; background: linear-gradient(135deg, rgba(197,160,23,0.1) 0%, rgba(255,255,255,0.05) 100%); }
.kps-hc-label { font-size: clamp(0.55rem, 1.1vw, 0.62rem); color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 0.25rem; }
.kps-hc-value { font-family: 'Oswald', sans-serif; font-size: clamp(0.85rem, 2vw, 1.1rem); font-weight: 700; line-height: 1.2; }
.kps-hc-value.arena-name { color: #64b5f6; }
.kps-hc-value.seni-name { color: #fff; }
.kps-hc-sub { font-size: clamp(0.6rem, 1.2vw, 0.7rem); color: rgba(255,255,255,0.55); }

/* ─── Tabs ─────────────────────────────────────── */
.kps-tabs { flex-shrink: 0; display: flex; padding: 4px clamp(0.5rem, 1vw, 0.75rem); border-bottom: 1px solid rgba(255,255,255,0.06); background: #0d0f12; }
.kps-tabs .nav-link { background: transparent; color: rgba(255,255,255,0.5); border: none; border-radius: 6px; font-size: clamp(0.75rem, 1.5vw, 0.85rem); font-weight: 500; padding: 0.35rem 0.75rem; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.35rem; white-space: nowrap; }
.kps-tabs .nav-link:hover { color: #fff; background: rgba(255,255,255,0.05); }
.kps-tabs .nav-link.active { background: rgba(197,160,23,0.15); color: #c5a017; border: 1px solid rgba(197,160,23,0.25); }
.kps-tabs .nav-link.tab-blue.active { background: rgba(21,101,192,0.15); color: #64b5f6; border-color: rgba(21,101,192,0.25); }
.kps-tabs .nav-link.tab-red.active { background: rgba(198,40,40,0.15); color: #ef5350; border-color: rgba(198,40,40,0.25); }

/* ─── Tab Content ──────────────────────────────── */
.kps-tab-content { flex: 1; overflow-y: auto; padding: clamp(0.5rem, 1vw, 0.75rem); }
.kps-tab-content::-webkit-scrollbar { width: 5px; }
.kps-tab-content::-webkit-scrollbar-track { background: transparent; }
.kps-tab-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }

/* ─── Peserta Card ─────────────────────────────── */
.kps-peserta-card { border-radius: 8px; padding: clamp(0.6rem, 1.5vw, 0.85rem) 1rem; margin-bottom: clamp(0.5rem, 1vw, 0.75rem); text-align: center; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); }
.kps-peserta-card.blue { border-left: 3px solid #1565c0; background: linear-gradient(135deg, rgba(21,101,192,0.1) 0%, rgba(255,255,255,0.05) 100%); }
.kps-peserta-card.red { border-left: 3px solid #c62828; background: linear-gradient(135deg, rgba(198,40,40,0.1) 0%, rgba(255,255,255,0.05) 100%); }
.kps-peserta-nama { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.3rem); font-weight: 700; margin: 0; line-height: 1.3; color: #fff; }
.kps-peserta-kontingen { font-size: clamp(0.75rem, 1.5vw, 0.85rem); color: rgba(255,255,255,0.55); margin: 2px 0 0 0; }

/* ─── Data Table ───────────────────────────────── */
.kps-table-wrap { margin-bottom: clamp(0.5rem, 1vw, 0.75rem); background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; overflow: hidden; }
.kps-table { width: 100%; font-size: clamp(0.7rem, 1.4vw, 0.82rem); margin: 0; color: #fff; }
.kps-table thead { background: rgba(255,255,255,0.04); }
.kps-table thead th { padding: clamp(0.4rem, 0.8vw, 0.5rem) 0.3rem; font-weight: 600; text-transform: uppercase; font-size: clamp(0.6rem, 1.2vw, 0.7rem); color: rgba(255,255,255,0.5); letter-spacing: 0.5px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.06); }
.kps-table tbody td { padding: clamp(0.35rem, 0.7vw, 0.45rem) 0.3rem; text-align: center; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.03); font-weight: 500; }
.kps-table tbody tr:last-child td { border-bottom: none; }
.kps-table tbody tr.total-row td { font-weight: 700; border-top: 1px solid rgba(197,160,23,0.3); }

/* ─── Sorted Jury ──────────────────────────────── */
.kps-sorted-bar { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: clamp(0.5rem, 1vw, 0.75rem); margin-bottom: clamp(0.5rem, 1vw, 0.75rem); }
.kps-sorted-title { font-size: clamp(0.65rem, 1.3vw, 0.72rem); font-weight: 600; text-transform: uppercase; color: rgba(255,255,255,0.45); letter-spacing: 1px; margin: 0 0 0.5rem 0; text-align: center; }
.kps-sorted-row { display: flex; gap: clamp(0.25rem, 0.5vw, 0.4rem); }
.kps-sorted-cell { flex: 1; background: rgba(255,255,255,0.05); border-radius: 6px; padding: 0.4rem 0.25rem; text-align: center; }
.kps-sorted-cell .juri-num { font-size: clamp(0.55rem, 1.1vw, 0.65rem); color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.5px; }
.kps-sorted-cell .juri-val { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.5rem); font-weight: 700; color: #fff; }
.kps-sorted-cell.terpilih { background: rgba(197,160,23,0.12); border: 1px solid rgba(197,160,23,0.25); }

/* ─── Stat Cards 3×2 ───────────────────────────── */
.kps-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: clamp(0.3rem, 0.6vw, 0.4rem); margin-bottom: clamp(0.5rem, 1vw, 0.75rem); }
.kps-stat-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; overflow: hidden; text-align: center; }
.kps-stat-label { font-size: clamp(0.55rem, 1.1vw, 0.65rem); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.45); padding: 0.3rem 0.25rem; background: rgba(255,255,255,0.03); }
.kps-stat-value { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.5rem); font-weight: 700; padding: 0.35rem 0.25rem; color: #fff; }
.kps-stat-card.final-blue .kps-stat-value { background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%); }
.kps-stat-card.final-red .kps-stat-value { background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%); }

/* ─── Hukuman ──────────────────────────────────── */
.kps-hukuman-wrap { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; overflow: hidden; margin-bottom: 0.5rem; }
.kps-hukuman-row { display: flex; border-bottom: 1px solid rgba(255,255,255,0.04); }
.kps-hukuman-row:last-child { border-bottom: none; }
.kps-hukuman-label { flex: 7; padding: 0.4rem 0.6rem; font-size: clamp(0.7rem, 1.3vw, 0.8rem); font-weight: 500; color: rgba(255,255,255,0.7); display: flex; align-items: center; }
.kps-hukuman-val { flex: 5; padding: 0.4rem; text-align: center; font-family: 'Oswald', sans-serif; font-size: clamp(0.9rem, 2vw, 1.2rem); font-weight: 700; color: #ef5350; display: flex; align-items: center; justify-content: center; border-left: 1px solid rgba(198,40,40,0.15); background: rgba(198,40,40,0.08); }

/* ─── Summary Section ──────────────────────────── */
.kps-summary-table { width: 100%; font-size: clamp(0.65rem, 1.3vw, 0.78rem); margin: 0 0 0.75rem 0; color: #fff; }
.kps-summary-table thead th { background: rgba(255,255,255,0.04); padding: 0.4rem 0.3rem; font-size: clamp(0.55rem, 1.1vw, 0.65rem); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.45); text-align: center; border-bottom: 1px solid rgba(255,255,255,0.08); }
.kps-summary-table tbody td { padding: 0.35rem 0.3rem; text-align: center; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.03); }
.kps-summary-table .nama-cell { text-align: left; font-weight: 500; }
.kps-summary-table .kontingen-cell { display: block; font-size: clamp(0.55rem, 1.1vw, 0.65rem); color: rgba(255,255,255,0.4); }

.kps-section-title { font-size: clamp(0.65rem, 1.3vw, 0.75rem); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.35); margin: 0 0 0.4rem 0; text-align: center; padding: 0.35rem 0; background: rgba(255,255,255,0.02); border-radius: 6px; border: 1px solid rgba(255,255,255,0.04); }

.kps-disq { display: inline-flex; padding: 0.15rem 0.4rem; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; color: #fff; background: #c62828; border-radius: 4px; }

/* ─── VS Separator ─────────────────────────────── */
.kps-vs-sep { text-align: center; padding: 0.5rem 0; margin-bottom: clamp(0.5rem, 1vw, 0.75rem); }
.kps-vs-sep .vs-badge { display: inline-flex; align-items: center; justify-content: center; width: clamp(2.5rem, 5vw, 3.5rem); height: clamp(2.5rem, 5vw, 3.5rem); border-radius: 50%; background: linear-gradient(135deg, #333, #111); border: 2px solid rgba(255,255,255,0.1); font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2vw, 1.4rem); font-weight: 700; color: rgba(255,255,255,0.7); }

@media (max-width: 768px) { .kps-stats-grid { grid-template-columns: repeat(3, 1fr); } .kps-sorted-row { flex-wrap: wrap; } .kps-sorted-cell { min-width: 80px; } }
@media (orientation: landscape) and (max-height: 500px) { .kps-tab-content { padding: 0.35rem; } .kps-stat-value { font-size: 0.85rem; } .kps-header { padding: 0.3rem; } .kps-header-icon { width: 1.5rem; height: 1.5rem; font-size: 0.7rem; } .kps-peserta-card { padding: 0.4rem; } .kps-peserta-nama { font-size: 0.85rem; } .kps-table { font-size: 0.6rem; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="kp-seni-app">
	<!-- Header -->
	<div class="kps-header">
		<div class="kps-header-card arena">
			<div class="kps-hc-label"><i class="fas fa-map-marker-alt"></i> Gelanggang</div>
			<div class="kps-hc-value arena-name"><?= esc($nama_gelanggang ?? '-') ?></div>
			<div class="kps-hc-sub">Partai <?= esc($nomor_partai ?? '-') ?></div>
		</div>
		<div class="kps-header-card seni">
			<div class="kps-hc-label"><i class="fas fa-hand-sparkles"></i> <?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?> · <?= esc($penampilan_seni_berlangsung->nama_kategori_usia ?? '') ?></div>
			<div class="kps-hc-value seni-name"><?= esc($penampilan_seni_berlangsung->nama_seni ?? 'Seni') ?></div>
			<div class="kps-hc-sub">Pool <?= esc($penampilan_seni_berlangsung->nomor_pool ?? '-') ?> · Battle</div>
		</div>
	</div>

	<!-- Tabs -->
	<ul class="nav kps-tabs" role="tablist" id="tabNilai">
		<li class="nav-item">
			<button class="nav-link tab-blue active" data-bs-toggle="tab" data-bs-target="#blue_corner" type="button" role="tab" aria-selected="true" id="blueCornerNav">
				<i class="fas fa-circle" style="color:#1565c0;font-size:0.5rem;"></i> Blue
			</button>
		</li>
		<li class="nav-item">
			<button class="nav-link tab-red" data-bs-toggle="tab" data-bs-target="#red_corner" type="button" role="tab" aria-selected="false" id="redCornerNav">
				<i class="fas fa-circle" style="color:#c62828;font-size:0.5rem;"></i> Red
			</button>
		</li>
		<li class="nav-item">
			<button class="nav-link" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab" aria-selected="false" id="summaryNav">
				<i class="fas fa-table-list"></i> Summary
			</button>
		</li>
	</ul>

	<div class="tab-content kps-tab-content">
		<!-- ═══ TAB BLUE ═══ -->
		<div class="tab-pane active" id="blue_corner" role="tabpanel">
			<?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
			<?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_biru): ?>
			<?php $idPsB = (int) $penampilan_seni->id_penampilan_seni; ?>

			<div class="kps-peserta-card blue penampilan_seni_<?= $idPsB ?>">
				<p class="kps-peserta-nama"><?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p>
				<p class="kps-peserta-kontingen"><?= $penampilan_seni->nama_kontingen ?? '-' ?></p>
			</div>

			<?php if (!empty($data_nilai[$idPsB])): ?>
			<?php $juriB = $data_nilai[$idPsB]; ?>

			<!-- Unsur Nilai -->
			<div class="kps-table-wrap penampilan_seni_<?= $idPsB ?> blue-corner">
				<table class="kps-table table-sm">
					<thead>
						<tr>
							<th style="width:25%">Unsur</th>
							<?php for ($j = 1; $j <= count($juriB); $j++): ?><th>J<?= $j ?></th><?php endfor ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($jenis_unsur_nilai as $jenis): ?>
						<tr>
							<td style="text-align:left;padding-left:0.5rem;font-weight:600;text-transform:capitalize;"><?= ucwords(str_replace('_', ' ', $jenis)) ?></td>
							<?php foreach ($juriB as $juri): ?>
								<td class="<?= $jenis ?>_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
							<?php endforeach ?>
						</tr>
						<?php endforeach; ?>
						<tr class="total-row">
							<td style="text-align:left;padding-left:0.5rem;">Total</td>
							<?php foreach ($juriB as $juri): ?>
								<td class="total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
							<?php endforeach ?>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Sorted Jury -->
			<div class="kps-sorted-bar penampilan_seni_<?= $idPsB ?> blue-corner">
				<p class="kps-sorted-title">Jury Score (Without penalty)</p>
				<div class="kps-sorted-row urutan_total_nilai_juri">
					<?php foreach ($juriB as $juri): ?>
					<div class="kps-sorted-cell kolom_total_nilai_<?= $idPsB ?>">
						<div class="juri-num nomor_juri"></div>
						<div class="juri-val total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>">0</div>
					</div>
					<?php endforeach ?>
				</div>
			</div>

			<!-- Stats -->
			<div class="kps-stats-grid penampilan_seni_<?= $idPsB ?>">
				<div class="kps-stat-card"><div class="kps-stat-label">Median</div><div class="kps-stat-value median_<?= $idPsB ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Penalty</div><div class="kps-stat-value hukuman_<?= $idPsB ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Med Kebenaran</div><div class="kps-stat-value kebenaran_median_<?= $idPsB ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Std Dev</div><div class="kps-stat-value standar_deviasi_<?= $idPsB ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?= $idPsB ?>">0</div></div>
				<div class="kps-stat-card final-blue"><div class="kps-stat-label">Final Score</div><div class="kps-stat-value nilai_akhir_<?= $idPsB ?>">0</div></div>
			</div>

			<?php
				$sampelB = json_decode($juriB[0]->penilaian ?? '{}');
				$hukB = $sampelB->penilaian->hukuman ?? null;
			?>
			<?php if ($hukB !== null): ?>
			<div class="kps-hukuman-wrap penampilan_seni_<?= $idPsB ?> blue-corner">
				<?php foreach ($hukB as $jenisH => $valH): ?>
				<div class="kps-hukuman-row">
					<div class="kps-hukuman-label"><?= $valH->metadata->label ?? ucwords(str_replace('_', ' ', $jenisH)) ?></div>
					<div class="kps-hukuman-val nilai_hukuman_<?= $jenisH ?>">0</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php endif; ?>
			<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<!-- ═══ TAB RED ═══ -->
		<div class="tab-pane" id="red_corner" role="tabpanel">
			<?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
			<?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_merah): ?>
			<?php $idPsR = (int) $penampilan_seni->id_penampilan_seni; ?>

			<div class="kps-peserta-card red penampilan_seni_<?= $idPsR ?>">
				<p class="kps-peserta-nama"><?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p>
				<p class="kps-peserta-kontingen"><?= $penampilan_seni->nama_kontingen ?? '-' ?></p>
			</div>

			<?php if (!empty($data_nilai[$idPsR])): ?>
			<?php $juriR = $data_nilai[$idPsR]; ?>

			<!-- Unsur Nilai -->
			<div class="kps-table-wrap penampilan_seni_<?= $idPsR ?> red-corner">
				<table class="kps-table table-sm">
					<thead>
						<tr>
							<th style="width:25%">Unsur</th>
							<?php for ($j = 1; $j <= count($juriR); $j++): ?><th>J<?= $j ?></th><?php endfor ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($jenis_unsur_nilai as $jenis): ?>
						<tr>
							<td style="text-align:left;padding-left:0.5rem;font-weight:600;text-transform:capitalize;"><?= ucwords(str_replace('_', ' ', $jenis)) ?></td>
							<?php foreach ($juriR as $juri): ?>
								<td class="<?= $jenis ?>_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
							<?php endforeach ?>
						</tr>
						<?php endforeach; ?>
						<tr class="total-row">
							<td style="text-align:left;padding-left:0.5rem;">Total</td>
							<?php foreach ($juriR as $juri): ?>
								<td class="total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td>
							<?php endforeach ?>
						</tr>
					</tbody>
				</table>
			</div>

			<!-- Sorted Jury -->
			<div class="kps-sorted-bar penampilan_seni_<?= $idPsR ?> red-corner">
				<p class="kps-sorted-title">Jury Score (Without penalty)</p>
				<div class="kps-sorted-row urutan_total_nilai_juri">
					<?php foreach ($juriR as $juri): ?>
					<div class="kps-sorted-cell kolom_total_nilai_<?= $idPsR ?>">
						<div class="juri-num nomor_juri"></div>
						<div class="juri-val total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>">0</div>
					</div>
					<?php endforeach ?>
				</div>
			</div>

			<!-- Stats -->
			<div class="kps-stats-grid penampilan_seni_<?= $idPsR ?>">
				<div class="kps-stat-card"><div class="kps-stat-label">Median</div><div class="kps-stat-value median_<?= $idPsR ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Penalty</div><div class="kps-stat-value hukuman_<?= $idPsR ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Med Kebenaran</div><div class="kps-stat-value kebenaran_median_<?= $idPsR ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Std Dev</div><div class="kps-stat-value standar_deviasi_<?= $idPsR ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?= $idPsR ?>">0</div></div>
				<div class="kps-stat-card final-red"><div class="kps-stat-label">Final Score</div><div class="kps-stat-value nilai_akhir_<?= $idPsR ?>">0</div></div>
			</div>

			<?php
				$sampelR = json_decode($juriR[0]->penilaian ?? '{}');
				$hukR = $sampelR->penilaian->hukuman ?? null;
			?>
			<?php if ($hukR !== null): ?>
			<div class="kps-hukuman-wrap penampilan_seni_<?= $idPsR ?> red-corner">
				<?php foreach ($hukR as $jenisH => $valH): ?>
				<div class="kps-hukuman-row">
					<div class="kps-hukuman-label"><?= $valH->metadata->label ?? ucwords(str_replace('_', ' ', $jenisH)) ?></div>
					<div class="kps-hukuman-val nilai_hukuman_<?= $jenisH ?>">0</div>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php endif; ?>
			<?php endif; ?>
			<?php endforeach; ?>
		</div>

		<!-- ═══ TAB SUMMARY ═══ -->
		<div class="tab-pane" id="summary" role="tabpanel">
			<div class="row g-2">
				<?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
				<?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_biru): ?>
				<?php $idPsS = (int) $penampilan_seni->id_penampilan_seni; ?>
				<div class="col-12 col-md-6">
					<div class="kps-peserta-card blue penampilan_seni_<?= $idPsS ?>">
						<p class="kps-peserta-nama"><?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p>
						<p class="kps-peserta-kontingen"><?= $penampilan_seni->nama_kontingen ?? '-' ?></p>
					</div>
					<div class="kps-stats-grid penampilan_seni_<?= $idPsS ?> blue-corner">
						<div class="kps-stat-card"><div class="kps-stat-label">Med</div><div class="kps-stat-value median_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">Pen</div><div class="kps-stat-value hukuman_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">MK</div><div class="kps-stat-value kebenaran_median_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">Std</div><div class="kps-stat-value standar_deviasi_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card final-blue"><div class="kps-stat-label">Final</div><div class="kps-stat-value nilai_akhir_<?= $idPsS ?>">0</div></div>
					</div>
				</div>
				<?php endif; ?>
				<?php endforeach; ?>

				<?php foreach ($semua_penampilan_seni as $penampilan_seni): ?>
				<?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_merah): ?>
				<?php $idPsS = (int) $penampilan_seni->id_penampilan_seni; ?>
				<div class="col-12 col-md-6">
					<div class="kps-peserta-card red penampilan_seni_<?= $idPsS ?>">
						<p class="kps-peserta-nama"><?= str_replace('<br>', ' ', $penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p>
						<p class="kps-peserta-kontingen"><?= $penampilan_seni->nama_kontingen ?? '-' ?></p>
					</div>
					<div class="kps-stats-grid penampilan_seni_<?= $idPsS ?> red-corner">
						<div class="kps-stat-card"><div class="kps-stat-label">Med</div><div class="kps-stat-value median_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">Pen</div><div class="kps-stat-value hukuman_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">MK</div><div class="kps-stat-value kebenaran_median_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">Std</div><div class="kps-stat-value standar_deviasi_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?= $idPsS ?>">0</div></div>
						<div class="kps-stat-card final-red"><div class="kps-stat-label">Final</div><div class="kps-stat-value nilai_akhir_<?= $idPsS ?>">0</div></div>
					</div>
				</div>
				<?php endif; ?>
				<?php endforeach; ?>
			</div>
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

	<?php if ($battle_seni !== null && (int)$battle_seni->id_penampilan_seni_biru === (int)$penampilan_seni_berlangsung->id_penampilan_seni): ?>
	setTimeout(() => { document.getElementById('blueCornerNav').click(); }, 1000);
	<?php else: ?>
	setTimeout(() => { document.getElementById('redCornerNav').click(); }, 1000);
	<?php endif; ?>

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
