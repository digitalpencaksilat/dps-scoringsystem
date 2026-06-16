<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/kp-seni.css') ?>">
<style>
html, body { height: 100%; overflow: hidden; margin: 0; }
body { background: #f4f6f9; color: #212529; font-family: 'Poppins', sans-serif; }

#kp-seni-app { display: flex; flex-direction: column; height: 100dvh; overflow: hidden; }

.kps-header { flex-shrink: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; padding: clamp(0.35rem, 0.8vw, 0.5rem); background: #fff; border-bottom: 1px solid #dee2e6; }
.kps-header-card { background: linear-gradient(180deg, #c5a017 0%, #9a7d12 100%); border: none; border-radius: 8px; padding: clamp(0.4rem, 1vw, 0.55rem) clamp(0.5rem, 1vw, 0.75rem); display: flex; align-items: center; justify-content: center; gap: 0.35rem; font-family: 'Oswald', sans-serif; font-size: clamp(0.75rem, 2vw, 1rem); font-weight: 600; color: #fff; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kps-header-card i { opacity: 0.7; }
.kps-hc-sep { opacity: 0.4; font-weight: 300; }

.kps-header-info { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.4rem; font-size: 0.62rem; color: #adb5bd; margin-top: 0.15rem; }
.kps-header-info span { display: inline-flex; align-items: center; gap: 0.2rem; white-space: nowrap; }

.kps-tabs { flex-shrink: 0; display: flex; padding: 4px 0.75rem; border-bottom: 1px solid #dee2e6; background: #fff; }
.kps-tabs .nav-link { background: transparent; color: #6c757d; border: 1px solid transparent; border-radius: 6px; font-size: clamp(0.75rem, 1.5vw, 0.85rem); font-weight: 500; padding: 0.35rem 0.75rem; transition: all 0.2s ease; display: flex; align-items: center; gap: 0.35rem; }
.kps-tabs .nav-link:hover { color: #212529; background: #e9ecef; }
.kps-tabs .nav-link.active { background: #c60000; color: #fff; border-color: #c60000; }

.kps-tab-content { flex: 1; overflow-y: auto; padding: clamp(0.5rem, 1vw, 0.75rem); }
.kps-tab-content::-webkit-scrollbar { width: 5px; }
.kps-tab-content::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 3px; }

.kps-peserta-card { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: clamp(0.6rem, 1.5vw, 0.85rem) 1rem; margin-bottom: 0.75rem; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.kps-peserta-nama { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.3rem); font-weight: 700; margin: 0; color: #212529; }
.kps-peserta-kontingen { font-size: clamp(0.75rem, 1.5vw, 0.85rem); color: #6c757d; margin: 2px 0 0 0; }

.kps-table-wrap { margin-bottom: 0.75rem; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.kps-table { width: 100%; font-size: clamp(0.7rem, 1.4vw, 0.82rem); margin: 0; color: #212529; }
.kps-table thead { background: #f8f9fa; }
.kps-table thead th { padding: 0.45rem 0.3rem; font-weight: 600; text-transform: uppercase; font-size: 0.65rem; color: #6c757d; letter-spacing: 0.5px; text-align: center; border-bottom: 2px solid #dee2e6; }
.kps-table tbody td { padding: 0.4rem 0.3rem; text-align: center; vertical-align: middle; border-bottom: 1px solid #f1f3f5; font-weight: 500; }
.kps-table tbody tr:last-child td { border-bottom: none; }
.kps-table tbody tr.total-row td { font-weight: 700; border-top: 2px solid #c5a017; background: #fffdf5; }

.kps-sorted-bar { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.kps-sorted-title { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: #adb5bd; letter-spacing: 1px; margin: 0 0 0.5rem 0; text-align: center; }
.kps-sorted-row { display: flex; gap: 0.4rem; }
.kps-sorted-cell { flex: 1; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 0.4rem 0.25rem; text-align: center; }
.kps-sorted-cell .juri-num { font-size: 0.6rem; color: #adb5bd; text-transform: uppercase; letter-spacing: 0.5px; }
.kps-sorted-cell .juri-val { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.4rem); font-weight: 700; color: #212529; }
.kps-sorted-cell.terpilih { background: #fffde7; border-color: #c5a017; }

.kps-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem; margin-bottom: 0.75rem; }
.kps-stat-card { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.kps-stat-label { font-size: 0.6rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d; padding: 0.3rem; background: #f8f9fa; }
.kps-stat-value { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.4rem); font-weight: 700; padding: 0.35rem; color: #212529; }
.kps-stat-card.final .kps-stat-value { background: linear-gradient(180deg, #c5a017 0%, #9a7d12 100%); color: #fff; }

.kps-hukuman-wrap { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; margin-bottom: 0.5rem; }
.kps-hukuman-row { display: flex; border-bottom: 1px solid #f1f3f5; }
.kps-hukuman-row:last-child { border-bottom: none; }
.kps-hukuman-label { flex: 7; padding: 0.4rem 0.6rem; font-size: 0.78rem; font-weight: 500; color: #495057; display: flex; align-items: center; }
.kps-hukuman-val { flex: 5; padding: 0.4rem; text-align: center; font-family: 'Oswald', sans-serif; font-size: 1.1rem; font-weight: 700; color: #c62828; display: flex; align-items: center; justify-content: center; border-left: 1px solid #dee2e6; background: #fff5f5; }

.kps-section-title { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #adb5bd; margin: 0 0 0.4rem 0; text-align: center; padding: 0.35rem; background: #fff; border-radius: 6px; border: 1px solid #dee2e6; }
.kps-summary-table { width: 100%; font-size: clamp(0.65rem, 1.3vw, 0.78rem); margin: 0 0 0.75rem 0; color: #212529; }
.kps-summary-table thead th { background: #f8f9fa; padding: 0.4rem 0.3rem; font-size: 0.6rem; font-weight: 600; text-transform: uppercase; color: #6c757d; text-align: center; border-bottom: 2px solid #dee2e6; }
.kps-summary-table tbody td { padding: 0.35rem 0.3rem; text-align: center; border-bottom: 1px solid #f1f3f5; }
.kps-summary-table .nama-cell { text-align: left; font-weight: 500; }
.kps-summary-table .kontingen-cell { display: block; font-size: 0.6rem; color: #adb5bd; }
.kps-disq { display: inline-flex; padding: 0.15rem 0.4rem; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; color: #fff; background: #c62828; border-radius: 4px; }

@media (max-width: 768px) { .kps-sorted-row { flex-wrap: wrap; } .kps-sorted-cell { min-width: 80px; } }
@media (orientation: landscape) and (max-height: 500px) { .kps-tab-content { padding: 0.35rem; } .kps-stat-value { font-size: 0.85rem; } .kps-header { padding: 0.3rem; } .kps-header-icon { width: 1.5rem; height: 1.5rem; font-size: 0.7rem; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="kp-seni-app">
	<div class="kps-header">
		<div class="kps-header-card"><i class="fas fa-map-marker-alt"></i> <?= esc($nama_gelanggang ?? '-') ?> <span class="kps-hc-sep">—</span> Partai <?= esc($nomor_partai ?? '-') ?></div>
		<div class="kps-header-card"><i class="fas fa-hand-sparkles"></i> <?= esc($penampilan_seni_berlangsung->nama_seni ?? 'Seni') ?> <span class="kps-hc-sep">—</span> <?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?> <span class="kps-hc-sep">—</span> Pool <?= esc($penampilan_seni_berlangsung->nomor_pool ?? '-') ?></div>
	</div>

	<ul class="nav kps-tabs" role="tablist" id="tabNilai">
		<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#now_performing" type="button" role="tab">Now Performing</button></li>
		<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">Summary</button></li>
	</ul>

	<div class="tab-content kps-tab-content">
		<div class="tab-pane active" id="now_performing" role="tabpanel">
			<div class="kps-peserta-card"><p class="kps-peserta-nama"><?= str_replace('<br>', ' ', $penampilan_seni_berlangsung->anggota_kelompok_peserta_seni ?? '-') ?></p><p class="kps-peserta-kontingen"><?= $penampilan_seni_berlangsung->nama_kontingen ?? '-' ?></p></div>
			<?php $idNow = (int) $penampilan_seni_berlangsung->id_penampilan_seni; ?>
			<?php if (!empty($data_nilai[$idNow])): ?><?php $juriNow = $data_nilai[$idNow]; ?>
			<div class="kps-table-wrap penampilan_seni_<?= $idNow ?>"><table class="kps-table table-sm"><thead><tr><th style="width:25%">Unsur</th><?php for ($ji=1; $ji<=count($juriNow); $ji++): ?><th>J<?= $ji ?></th><?php endfor ?></tr></thead><tbody><?php foreach ($jenis_unsur_nilai as $jenis): ?><tr><td style="text-align:left;padding-left:0.5rem;font-weight:600;text-transform:capitalize;"><?= ucwords(str_replace('_',' ',$jenis)) ?></td><?php foreach ($juriNow as $juri): ?><td class="<?= $jenis ?>_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td><?php endforeach ?></tr><?php endforeach; ?><tr class="total-row"><td style="text-align:left;padding-left:0.5rem;">Total</td><?php foreach ($juriNow as $juri): ?><td class="total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>"></td><?php endforeach ?></tr></tbody></table></div>
			<div class="kps-sorted-bar penampilan_seni_<?= $idNow ?>"><p class="kps-sorted-title">Sorted Jury Score</p><div class="kps-sorted-row urutan_total_nilai_juri"><?php foreach ($juriNow as $juri): ?><div class="kps-sorted-cell kolom_total_nilai"><div class="juri-num nomor_juri"></div><div class="juri-val total_nilai_juri_<?= $juri->id_perangkat_pertandingan ?> juri_<?= $juri->id_perangkat_pertandingan ?>">0</div></div><?php endforeach ?></div></div>
			<?php endif; ?>
			<div class="kps-stats-grid penampilan_seni_<?= $idNow ?>">
				<div class="kps-stat-card"><div class="kps-stat-label">Median</div><div class="kps-stat-value median_<?= $idNow ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Penalty</div><div class="kps-stat-value hukuman_<?= $idNow ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Med Kebenaran</div><div class="kps-stat-value kebenaran_median_<?= $idNow ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Std Dev</div><div class="kps-stat-value standar_deviasi_<?= $idNow ?>">0</div></div>
				<div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?= $idNow ?>">0</div></div>
				<div class="kps-stat-card final"><div class="kps-stat-label">Final Score</div><div class="kps-stat-value nilai_akhir_<?= $idNow ?>">0</div></div>
			</div>
			<?php if (!empty($data_nilai[$idNow])): ?><?php $sampel = json_decode($juriNow[0]->penilaian ?? '{}'); $hukumanList = $sampel->penilaian->hukuman ?? null; ?><?php if ($hukumanList !== null): ?><div class="kps-hukuman-wrap penampilan_seni_<?= $idNow ?>"><?php foreach ($hukumanList as $jenisH => $valH): ?><div class="kps-hukuman-row"><div class="kps-hukuman-label"><?= $valH->metadata->label ?? ucwords(str_replace('_',' ',$jenisH)) ?></div><div class="kps-hukuman-val nilai_hukuman_<?= $jenisH ?>">0</div></div><?php endforeach ?></div><?php endif ?><?php endif ?>
		</div>

		<div class="tab-pane" id="summary" role="tabpanel">
			<div class="kps-section-title">Final Score</div>
			<div class="kps-table-wrap"><table class="kps-summary-table table-sm" id="tabelSummaryPenampilan"><thead><tr><th>#</th><th style="text-align:left;">Nama</th><th>Med</th><th>Pen</th><th>Med Keb</th><th>Std D</th><th>Time</th><th>Final</th><th>Disq</th></tr></thead><tbody><?php foreach ($semua_penampilan_seni as $idx => $pnSeni): ?><tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>"><td><?= $idx+1 ?></td><td class="nama-cell"><?= str_replace('<br>',' ',$pnSeni->anggota_kelompok_peserta_seni ?? '-') ?><span class="kontingen-cell"><?= $pnSeni->nama_kontingen ?? '' ?></span></td><td class="median_<?= $pnSeni->id_penampilan_seni ?>"></td><td class="hukuman_<?= $pnSeni->id_penampilan_seni ?>"></td><td class="kebenaran_median_<?= $pnSeni->id_penampilan_seni ?>"></td><td class="standar_deviasi_<?= $pnSeni->id_penampilan_seni ?>"></td><td class="waktu_<?= $pnSeni->id_penampilan_seni ?>"><?= date("i:s", $pnSeni->waktu_tampil ?? 0) ?></td><td class="nilai_akhir_<?= $pnSeni->id_penampilan_seni ?>"><?= number_format($pnSeni->nilai_akhir ?? 0, 3) ?></td><td class="keterangan_<?= $pnSeni->id_penampilan_seni ?>"><?= ($pnSeni->diskualifikasi ?? 0) == 1 ? '<span class="kps-disq">Disq</span>' : '' ?></td></tr><?php endforeach ?></tbody></table></div>
			<?php $fk = array_key_first($data_nilai); ?>
			<div class="kps-section-title">All Jury Score</div><div class="kps-table-wrap"><table class="kps-summary-table table-sm"><thead><tr><th>#</th><th style="text-align:left;">Nama</th><?php if ($fk !== null): ?><?php for ($ji=1; $ji<=count($data_nilai[$fk]); $ji++): ?><th>J<?= $ji ?></th><?php endfor ?><?php endif ?></tr></thead><tbody><?php foreach ($semua_penampilan_seni as $num => $pnSeni): ?><tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>"><td><?= $num+1 ?></td><td class="nama-cell"><?= str_replace('<br>',' ',$pnSeni->anggota_kelompok_peserta_seni ?? '-') ?><span class="kontingen-cell"><?= $pnSeni->nama_kontingen ?? '' ?></span></td><?php if (isset($data_nilai[(int)$pnSeni->id_penampilan_seni])): ?><?php foreach ($data_nilai[(int)$pnSeni->id_penampilan_seni] as $penilaian): ?><td class="nilai_akhir_juri_<?= $penilaian->id_perangkat_pertandingan ?> juri_<?= $penilaian->id_perangkat_pertandingan ?>"></td><?php endforeach ?><?php endif ?></tr><?php endforeach ?></tbody></table></div>
			<?php foreach ($jenis_unsur_nilai as $jenis): ?><div class="kps-section-title"><?= ucwords(str_replace('_',' ',$jenis)) ?></div><div class="kps-table-wrap"><table class="kps-summary-table table-sm"><thead><tr><th>#</th><th style="text-align:left;">Nama</th><?php if ($fk !== null): ?><?php for ($ji=1; $ji<=count($data_nilai[$fk]); $ji++): ?><th>J<?= $ji ?></th><?php endfor ?><?php endif ?></tr></thead><tbody><?php foreach ($semua_penampilan_seni as $num => $pnSeni): ?><tr class="penampilan_seni_<?= $pnSeni->id_penampilan_seni ?>"><td><?= $num+1 ?></td><td class="nama-cell"><?= str_replace('<br>',' ',$pnSeni->anggota_kelompok_peserta_seni ?? '-') ?><span class="kontingen-cell"><?= $pnSeni->nama_kontingen ?? '' ?></span></td><?php if (isset($data_nilai[(int)$pnSeni->id_penampilan_seni])): ?><?php foreach ($data_nilai[(int)$pnSeni->id_penampilan_seni] as $penilaian): ?><td class="<?= $jenis ?>_juri_<?= $penilaian->id_perangkat_pertandingan ?> juri_<?= $penilaian->id_perangkat_pertandingan ?>"></td><?php endforeach ?><?php endif ?></tr><?php endforeach ?></tbody></table></div><?php endforeach ?>
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
	$(document).ready(function(){ ketua_pertandingan.init(<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>, $data_nilai, $penampilan_seni_berlangsung, $semua_penampilan_seni, $autorefresh); });
</script>
<?= $this->endSection() ?>
