<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/kp-seni.css') ?>">
<style>
html, body { height: 100%; overflow: hidden; margin: 0; }
body { background: #f4f6f9; color: #212529; font-family: 'Poppins', sans-serif; }

#kp-seni-app { display: flex; flex-direction: column; height: 100dvh; overflow: hidden; }

.kps-header { flex-shrink: 0; display: flex; align-items: center; justify-content: center; gap: 0.5rem; padding: clamp(0.5rem, 1vw, 0.75rem) 1rem; background: #fff; border-bottom: 1px solid #dee2e6; }
.kps-header-icon { width: clamp(2rem, 4vw, 2.5rem); height: clamp(2rem, 4vw, 2.5rem); border-radius: 8px; background: linear-gradient(135deg, #c5a017, #9a7d12); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff; flex-shrink: 0; }
.kps-header-title { font-family: 'Oswald', sans-serif; font-size: clamp(0.85rem, 2vw, 1rem); font-weight: 700; text-transform: uppercase; text-align: center; line-height: 1.2; color: #212529; }
.kps-header-sub { font-size: clamp(0.65rem, 1.5vw, 0.75rem); color: #6c757d; }

.kps-header-info { display: flex; flex-wrap: wrap; justify-content: center; gap: 0.4rem; font-size: 0.62rem; color: #adb5bd; margin-top: 0.15rem; }
.kps-header-info span { display: inline-flex; align-items: center; gap: 0.2rem; white-space: nowrap; }

.kps-tabs { flex-shrink: 0; display: flex; padding: 4px 0.75rem; border-bottom: 1px solid #dee2e6; background: #fff; }
.kps-tabs .nav-link { background: transparent; color: #6c757d; border: 1px solid transparent; border-radius: 6px; font-size: clamp(0.75rem, 1.5vw, 0.85rem); font-weight: 500; padding: 0.35rem 0.75rem; transition: all 0.2s; display: flex; align-items: center; gap: 0.35rem; }
.kps-tabs .nav-link:hover { color: #212529; background: #e9ecef; }
.kps-tabs .nav-link.active { background: #c60000; color: #fff; border-color: #c60000; }
.kps-tabs .nav-link.tab-blue.active { background: #1565c0; border-color: #1565c0; }
.kps-tabs .nav-link.tab-red.active { background: #c62828; border-color: #c62828; }

.kps-tab-content { flex: 1; overflow-y: auto; padding: clamp(0.5rem, 1vw, 0.75rem); }
.kps-tab-content::-webkit-scrollbar { width: 5px; }
.kps-tab-content::-webkit-scrollbar-thumb { background: #ced4da; border-radius: 3px; }

.kps-peserta-card { border-radius: 10px; padding: clamp(0.6rem, 1.5vw, 0.85rem) 1rem; margin-bottom: 0.75rem; text-align: center; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.kps-peserta-card.blue { border: 1px solid #90caf9; border-top: 3px solid #1565c0; }
.kps-peserta-card.red { border: 1px solid #ef9a9a; border-top: 3px solid #c62828; }
.kps-peserta-nama { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.3rem); font-weight: 700; margin: 0; color: #212529; }
.kps-peserta-kontingen { font-size: clamp(0.75rem, 1.5vw, 0.85rem); color: #6c757d; margin: 2px 0 0 0; }

.kps-table-wrap { margin-bottom: 0.75rem; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.kps-table { width: 100%; font-size: clamp(0.7rem, 1.4vw, 0.82rem); margin: 0; color: #212529; }
.kps-table thead { background: #f8f9fa; }
.kps-table thead th { padding: 0.45rem 0.3rem; font-weight: 600; text-transform: uppercase; font-size: 0.65rem; color: #6c757d; text-align: center; border-bottom: 2px solid #dee2e6; }
.kps-table tbody td { padding: 0.4rem 0.3rem; text-align: center; border-bottom: 1px solid #f1f3f5; }
.kps-table tbody tr:last-child td { border-bottom: none; }
.kps-table tbody tr.total-row td { font-weight: 700; border-top: 2px solid #c5a017; background: #fffdf5; }

.kps-sorted-bar { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; padding: 0.75rem; margin-bottom: 0.75rem; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.kps-sorted-title { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: #adb5bd; letter-spacing: 1px; margin: 0 0 0.5rem 0; text-align: center; }
.kps-sorted-row { display: flex; gap: 0.4rem; }
.kps-sorted-cell { flex: 1; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 0.4rem; text-align: center; }
.kps-sorted-cell .juri-num { font-size: 0.6rem; color: #adb5bd; text-transform: uppercase; }
.kps-sorted-cell .juri-val { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.4rem); font-weight: 700; color: #212529; }
.kps-sorted-cell.terpilih { background: #fffde7; border-color: #c5a017; }

.kps-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.4rem; margin-bottom: 0.75rem; }
.kps-stat-card { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.03); }
.kps-stat-label { font-size: 0.6rem; font-weight: 600; text-transform: uppercase; color: #6c757d; padding: 0.3rem; background: #f8f9fa; }
.kps-stat-value { font-family: 'Oswald', sans-serif; font-size: clamp(1rem, 2.5vw, 1.4rem); font-weight: 700; padding: 0.35rem; color: #212529; }
.kps-stat-card.final-blue .kps-stat-value { background: linear-gradient(180deg, #1565c0, #0d47a1); color: #fff; }
.kps-stat-card.final-red .kps-stat-value { background: linear-gradient(180deg, #c62828, #b71c1c); color: #fff; }

.kps-hukuman-wrap { background: #fff; border: 1px solid #dee2e6; border-radius: 8px; overflow: hidden; margin-bottom: 0.5rem; }
.kps-hukuman-row { display: flex; border-bottom: 1px solid #f1f3f5; }
.kps-hukuman-row:last-child { border-bottom: none; }
.kps-hukuman-label { flex: 7; padding: 0.4rem 0.6rem; font-size: 0.78rem; font-weight: 500; color: #495057; display: flex; align-items: center; }
.kps-hukuman-val { flex: 5; padding: 0.4rem; text-align: center; font-family: 'Oswald', sans-serif; font-size: 1.1rem; font-weight: 700; color: #c62828; display: flex; align-items: center; justify-content: center; border-left: 1px solid #dee2e6; background: #fff5f5; }

.kps-section-title { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: #adb5bd; margin: 0 0 0.4rem 0; text-align: center; padding: 0.35rem; background: #fff; border-radius: 6px; border: 1px solid #dee2e6; }
.kps-summary-table { width: 100%; font-size: clamp(0.65rem, 1.3vw, 0.78rem); margin: 0 0 0.75rem 0; color: #212529; }
.kps-summary-table thead th { background: #f8f9fa; padding: 0.4rem; font-size: 0.6rem; font-weight: 600; text-transform: uppercase; color: #6c757d; text-align: center; border-bottom: 2px solid #dee2e6; }
.kps-summary-table tbody td { padding: 0.35rem; text-align: center; border-bottom: 1px solid #f1f3f5; }
.kps-summary-table .nama-cell { text-align: left; font-weight: 500; }
.kps-summary-table .kontingen-cell { display: block; font-size: 0.6rem; color: #adb5bd; }
.kps-disq { padding: 0.15rem 0.4rem; font-size: 0.6rem; font-weight: 700; text-transform: uppercase; color: #fff; background: #c62828; border-radius: 4px; }

@media (max-width: 768px) { .kps-sorted-row { flex-wrap: wrap; } .kps-sorted-cell { min-width: 80px; } }
@media (orientation: landscape) and (max-height: 500px) { .kps-tab-content { padding: 0.3rem; } .kps-stat-value { font-size: 0.8rem; } .kps-header { padding: 0.3rem; } }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div id="kp-seni-app">
	<div class="kps-header"><div class="kps-header-icon"><i class="fas fa-hand-sparkles"></i></div><div><div class="kps-header-title"><?= $penampilan_seni_berlangsung->nama_seni ?? 'Seni' ?></div><div class="kps-header-sub text-center"><?= $penampilan_seni_berlangsung->nama_kategori_usia ?? '' ?> <?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?> (Battle)</div><div class="kps-header-info"><?php if(!empty($nama_gelanggang)):?><span><i class="fas fa-map-marker-alt"></i> <?= esc($nama_gelanggang) ?></span><?php endif?><?php if(!empty($nomor_partai)):?><span><i class="fas fa-hashtag"></i> Partai <?= esc($nomor_partai) ?></span><?php endif?><?php if(!empty($penampilan_seni_berlangsung->nomor_pool)):?><span><i class="fas fa-layer-group"></i> Pool <?= esc($penampilan_seni_berlangsung->nomor_pool) ?></span><?php endif?></div></div></div>

	<ul class="nav kps-tabs" role="tablist" id="tabNilai">
		<li class="nav-item"><button class="nav-link tab-blue active" data-bs-toggle="tab" data-bs-target="#blue_corner" type="button" role="tab"><i class="fas fa-circle" style="color:#1565c0;font-size:0.4rem;"></i> Blue</button></li>
		<li class="nav-item"><button class="nav-link tab-red" data-bs-toggle="tab" data-bs-target="#red_corner" type="button" role="tab"><i class="fas fa-circle" style="color:#c62828;font-size:0.4rem;"></i> Red</button></li>
		<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">Summary</button></li>
	</ul>

	<div class="tab-content kps-tab-content">
		<!-- BLUE -->
		<div class="tab-pane active" id="blue_corner" role="tabpanel">
			<?php foreach ($semua_penampilan_seni as $penampilan_seni): ?><?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_biru): ?><?php $idB = (int)$penampilan_seni->id_penampilan_seni; ?>
			<div class="kps-peserta-card blue penampilan_seni_<?= $idB ?>"><p class="kps-peserta-nama"><?= str_replace('<br>',' ',$penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p><p class="kps-peserta-kontingen"><?= $penampilan_seni->nama_kontingen ?? '-' ?></p></div>
			<?php if (!empty($data_nilai[$idB])): ?><?php $juriB = $data_nilai[$idB]; ?>
			<div class="kps-table-wrap penampilan_seni_<?= $idB ?> blue-corner"><table class="kps-table"><thead><tr><th style="width:25%">Unsur</th><?php for($j=1;$j<=count($juriB);$j++):?><th>J<?=$j?></th><?php endfor?></tr></thead><tbody><?php foreach($jenis_unsur_nilai as $jenis): ?><tr><td style="text-align:left;padding-left:0.5rem;font-weight:600;text-transform:capitalize;"><?= ucwords(str_replace('_',' ',$jenis)) ?></td><?php foreach($juriB as $juri): ?><td class="<?=$jenis?>_juri_<?=$juri->id_perangkat_pertandingan?> juri_<?=$juri->id_perangkat_pertandingan?>"></td><?php endforeach?></tr><?php endforeach?><tr class="total-row"><td style="text-align:left;padding-left:0.5rem;">Total</td><?php foreach($juriB as $juri): ?><td class="total_nilai_juri_<?=$juri->id_perangkat_pertandingan?> juri_<?=$juri->id_perangkat_pertandingan?>"></td><?php endforeach?></tr></tbody></table></div>
			<div class="kps-sorted-bar penampilan_seni_<?= $idB ?> blue-corner"><p class="kps-sorted-title">Jury Score</p><div class="kps-sorted-row urutan_total_nilai_juri"><?php foreach($juriB as $juri):?><div class="kps-sorted-cell kolom_total_nilai_<?=$idB?>"><div class="juri-num nomor_juri"></div><div class="juri-val total_nilai_juri_<?=$juri->id_perangkat_pertandingan?> juri_<?=$juri->id_perangkat_pertandingan?>">0</div></div><?php endforeach?></div></div>
			<div class="kps-stats-grid penampilan_seni_<?= $idB ?>"><div class="kps-stat-card"><div class="kps-stat-label">Median</div><div class="kps-stat-value median_<?=$idB?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Penalty</div><div class="kps-stat-value hukuman_<?=$idB?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Med Keb</div><div class="kps-stat-value kebenaran_median_<?=$idB?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Std Dev</div><div class="kps-stat-value standar_deviasi_<?=$idB?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?=$idB?>">0</div></div><div class="kps-stat-card final-blue"><div class="kps-stat-label">Final</div><div class="kps-stat-value nilai_akhir_<?=$idB?>">0</div></div></div>
			<?php $sB=json_decode($juriB[0]->penilaian??'{}');$hB=$sB->penilaian->hukuman??null;?><?php if($hB):?><div class="kps-hukuman-wrap penampilan_seni_<?=$idB?> blue-corner"><?php foreach($hB as $jH=>$vH):?><div class="kps-hukuman-row"><div class="kps-hukuman-label"><?=$vH->metadata->label??ucwords(str_replace('_',' ',$jH))?></div><div class="kps-hukuman-val nilai_hukuman_<?=$jH?>">0</div></div><?php endforeach?></div><?php endif?>
			<?php endif?><?php endif?><?php endforeach?>
		</div>

		<!-- RED -->
		<div class="tab-pane" id="red_corner" role="tabpanel">
			<?php foreach ($semua_penampilan_seni as $penampilan_seni): ?><?php if ($battle_seni !== null && (int)$penampilan_seni->id_penampilan_seni === (int)$battle_seni->id_penampilan_seni_merah): ?><?php $idR = (int)$penampilan_seni->id_penampilan_seni; ?>
			<div class="kps-peserta-card red penampilan_seni_<?= $idR ?>"><p class="kps-peserta-nama"><?= str_replace('<br>',' ',$penampilan_seni->anggota_kelompok_peserta_seni ?? '-') ?></p><p class="kps-peserta-kontingen"><?= $penampilan_seni->nama_kontingen ?? '-' ?></p></div>
			<?php if (!empty($data_nilai[$idR])): ?><?php $juriR = $data_nilai[$idR]; ?>
			<div class="kps-table-wrap penampilan_seni_<?= $idR ?> red-corner"><table class="kps-table"><thead><tr><th style="width:25%">Unsur</th><?php for($j=1;$j<=count($juriR);$j++):?><th>J<?=$j?></th><?php endfor?></tr></thead><tbody><?php foreach($jenis_unsur_nilai as $jenis): ?><tr><td style="text-align:left;padding-left:0.5rem;font-weight:600;text-transform:capitalize;"><?= ucwords(str_replace('_',' ',$jenis)) ?></td><?php foreach($juriR as $juri): ?><td class="<?=$jenis?>_juri_<?=$juri->id_perangkat_pertandingan?> juri_<?=$juri->id_perangkat_pertandingan?>"></td><?php endforeach?></tr><?php endforeach?><tr class="total-row"><td style="text-align:left;padding-left:0.5rem;">Total</td><?php foreach($juriR as $juri): ?><td class="total_nilai_juri_<?=$juri->id_perangkat_pertandingan?> juri_<?=$juri->id_perangkat_pertandingan?>"></td><?php endforeach?></tr></tbody></table></div>
			<div class="kps-sorted-bar penampilan_seni_<?= $idR ?> red-corner"><p class="kps-sorted-title">Jury Score</p><div class="kps-sorted-row urutan_total_nilai_juri"><?php foreach($juriR as $juri):?><div class="kps-sorted-cell kolom_total_nilai_<?=$idR?>"><div class="juri-num nomor_juri"></div><div class="juri-val total_nilai_juri_<?=$juri->id_perangkat_pertandingan?> juri_<?=$juri->id_perangkat_pertandingan?>">0</div></div><?php endforeach?></div></div>
			<div class="kps-stats-grid penampilan_seni_<?= $idR ?>"><div class="kps-stat-card"><div class="kps-stat-label">Median</div><div class="kps-stat-value median_<?=$idR?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Penalty</div><div class="kps-stat-value hukuman_<?=$idR?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Med Keb</div><div class="kps-stat-value kebenaran_median_<?=$idR?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Std Dev</div><div class="kps-stat-value standar_deviasi_<?=$idR?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?=$idR?>">0</div></div><div class="kps-stat-card final-red"><div class="kps-stat-label">Final</div><div class="kps-stat-value nilai_akhir_<?=$idR?>">0</div></div></div>
			<?php $sR=json_decode($juriR[0]->penilaian??'{}');$hR=$sR->penilaian->hukuman??null;?><?php if($hR):?><div class="kps-hukuman-wrap penampilan_seni_<?=$idR?> red-corner"><?php foreach($hR as $jH=>$vH):?><div class="kps-hukuman-row"><div class="kps-hukuman-label"><?=$vH->metadata->label??ucwords(str_replace('_',' ',$jH))?></div><div class="kps-hukuman-val nilai_hukuman_<?=$jH?>">0</div></div><?php endforeach?></div><?php endif?>
			<?php endif?><?php endif?><?php endforeach?>
		</div>

		<!-- SUMMARY -->
		<div class="tab-pane" id="summary" role="tabpanel">
			<div class="row g-2">
				<?php foreach($semua_penampilan_seni as $p):?><?php if($battle_seni!==null&&(int)$p->id_penampilan_seni===(int)$battle_seni->id_penampilan_seni_biru):?><?php $ids=(int)$p->id_penampilan_seni;?>
				<div class="col-12 col-md-6"><div class="kps-peserta-card blue penampilan_seni_<?=$ids?>"><p class="kps-peserta-nama"><?=str_replace('<br>',' ',$p->anggota_kelompok_peserta_seni??'-')?></p><p class="kps-peserta-kontingen"><?=$p->nama_kontingen??'-'?></p></div><div class="kps-stats-grid penampilan_seni_<?=$ids?> blue-corner"><div class="kps-stat-card"><div class="kps-stat-label">Med</div><div class="kps-stat-value median_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Pen</div><div class="kps-stat-value hukuman_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">MK</div><div class="kps-stat-value kebenaran_median_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Std</div><div class="kps-stat-value standar_deviasi_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?=$ids?>">0</div></div><div class="kps-stat-card final-blue"><div class="kps-stat-label">Final</div><div class="kps-stat-value nilai_akhir_<?=$ids?>">0</div></div></div></div>
				<?php endif?><?php endforeach?>
				<?php foreach($semua_penampilan_seni as $p):?><?php if($battle_seni!==null&&(int)$p->id_penampilan_seni===(int)$battle_seni->id_penampilan_seni_merah):?><?php $ids=(int)$p->id_penampilan_seni;?>
				<div class="col-12 col-md-6"><div class="kps-peserta-card red penampilan_seni_<?=$ids?>"><p class="kps-peserta-nama"><?=str_replace('<br>',' ',$p->anggota_kelompok_peserta_seni??'-')?></p><p class="kps-peserta-kontingen"><?=$p->nama_kontingen??'-'?></p></div><div class="kps-stats-grid penampilan_seni_<?=$ids?> red-corner"><div class="kps-stat-card"><div class="kps-stat-label">Med</div><div class="kps-stat-value median_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Pen</div><div class="kps-stat-value hukuman_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">MK</div><div class="kps-stat-value kebenaran_median_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Std</div><div class="kps-stat-value standar_deviasi_<?=$ids?>">0</div></div><div class="kps-stat-card"><div class="kps-stat-label">Time</div><div class="kps-stat-value waktu_<?=$ids?>">0</div></div><div class="kps-stat-card final-red"><div class="kps-stat-label">Final</div><div class="kps-stat-value nilai_akhir_<?=$ids?>">0</div></div></div></div>
				<?php endif?><?php endforeach?>
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
	<?php if($battle_seni!==null&&(int)$battle_seni->id_penampilan_seni_biru===(int)$penampilan_seni_berlangsung->id_penampilan_seni):?>setTimeout(()=>{document.getElementById('blueCornerNav').click();},1000);<?php else:?>setTimeout(()=>{document.getElementById('redCornerNav').click();},1000);<?php endif?>
	$(document).ready(function(){ketua_pertandingan.init(<?=$penampilan_seni_berlangsung->id_penampilan_seni?>,$data_nilai,$penampilan_seni_berlangsung,$semua_penampilan_seni,$autorefresh);});
</script>
<?= $this->endSection() ?>
