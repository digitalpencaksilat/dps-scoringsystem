<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top py-2">
	<div class="container">
		<a class="navbar-brand d-flex align-items-center" href="<?= base_url('sekretaris-pertandingan') ?>">
			<img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
				class="navbar-brand-img" alt="Logo" width="120"
				onerror="this.style.display='none'">
		</a>
		<button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navigation">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
				<li class="nav-item">
					<a class="nav-link" href="<?= base_url('sekretaris-pertandingan') ?>">
						<i class="fas fa-home me-1"></i> Dashboard
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_ganti_format_penilaian">
						<i class="fas fa-exchange-alt me-1"></i> Change Scoring Format
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link cursor-pointer" onclick="sekretaris_pertandingan.open_modal_input_juara()">
						<i class="fas fa-trophy me-1"></i> Change Winner
					</a>
				</li>
			</ul>
			<ul class="nav navbar-nav ms-auto align-items-center">
				<li class="nav-item d-none d-lg-block">
					<span class="badge bg-dark border border-secondary text-uppercase py-2 px-3">
						<i class="fas fa-user-shield me-1 text-danger"></i> Secretary
					</span>
				</li>
				<li class="nav-item">
					<a class="nav-link btn-logout-brand shadow-sm" href="<?= base_url('perangkat-pertandingan/logout') ?>">
						<i class="fas fa-power-off me-1"></i> LOGOUT
					</a>
				</li>
			</ul>
		</div>
	</div>
</nav>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
	$is_biru_active = (($battle->id_penampilan_seni_biru ?? '') == ($penampilan->id_penampilan_seni ?? ''));
	$is_merah_active = !$is_biru_active;
?>
<div class="container-fluid min-vh-100 pt-2 pt-lg-2 bg-super-dark bg-gradient overflow-hidden">

	<!-- TOP SECTION: INFO BOXES -->
	<div class="row mb-3 g-2 g-md-3 block-informasi justify-content-center">
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold"><?= esc($penampilan->nama_gelanggang ?? '') ?></span>
			</div>
		</div>
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold">Partai <?= esc($penampilan->nomor_partai ?? '') ?></span>
			</div>
		</div>
		<div class="col-auto opacity flex-fill">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center justify-content-center">
				<span class="fs-5 fw-bold text-wrap text-capitalize">
					<?= esc(($penampilan->nama_kategori_usia ?? '') . ' ' . ($penampilan->jenis_kelamin ?? '') . ' ' . ucwords($penampilan->jenis_seni ?? '') . ' ' . ($penampilan->nama_seni ?? '')) ?>
				</span>
			</div>
		</div>
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold"><?= esc(ucwords($battle->babak ?? '')) ?></span>
			</div>
		</div>
	</div>

	<!-- MIDDLE: TWO ATHLETES (BATTLE - BLUE vs RED) -->
	<div class="row mb-3 block-atlet">
		<div class="col-6 opacity atlet-biru">
			<div class="d-flex bg-blue bg-gradient rounded shadow-sm text-white overflow-hidden h-100 <?= $is_biru_active ? 'border border-4 border-warning' : '' ?>">
				<div class="flex-grow-1 p-3 d-flex flex-column justify-content-center">
					<?php foreach ($anggota_biru ?? [] as $peserta) : ?>
						<span class="fs-4 fw-bolder text-truncate"><?= esc($peserta->nama_pendaftar ?? '') ?></span>
					<?php endforeach; ?>
					<?php if (!empty($anggota_biru)) : ?>
						<span class="fs-5 mt-1 text-truncate"><?= esc($anggota_biru[0]->nama_kontingen ?? '') ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="col-6 opacity atlet-merah">
			<div class="d-flex bg-red bg-gradient rounded shadow-sm text-white overflow-hidden h-100 <?= $is_merah_active ? 'border border-4 border-warning' : '' ?>">
				<div class="flex-grow-1 p-3 d-flex flex-column justify-content-center text-end">
					<?php foreach ($anggota_merah ?? [] as $peserta) : ?>
						<span class="fs-4 fw-bolder text-truncate"><?= esc($peserta->nama_pendaftar ?? '') ?></span>
					<?php endforeach; ?>
					<?php if (!empty($anggota_merah)) : ?>
						<span class="fs-5 mt-1 text-truncate"><?= esc($anggota_merah[0]->nama_kontingen ?? '') ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- TIMER SECTION -->
	<div class="col-12 mb-3 block-stopwatch">
		<div class="row justify-content-center opacity">
			<div class="col-12 text-center">
				<div class="bg-navbar rounded p-3 shadow-lg w-100">
					<!-- Active corner indicator -->
					<?php if ($is_biru_active) : ?>
						<div class="bg-blue rounded shadow-sm py-2 mb-2 text-white fw-bold text-uppercase fs-5">Sudut Biru</div>
					<?php else : ?>
						<div class="bg-red rounded shadow-sm py-2 mb-2 text-white fw-bold text-uppercase fs-5">Sudut Merah</div>
					<?php endif; ?>

					<div class="d-block timer-seni text-center fw-bolder text-white lh-1 mb-2" style="font-size: min(8rem, 15vw);">
						00:00
					</div>
					<div class="row g-2 mt-3 block-kendali-waktu">
						<div class="col-4">
							<button class="btn btn-light w-100 h-100 py-2 py-lg-3 fs-5 fw-bold text-uppercase text-dark" onclick="sekretaris_pertandingan.open_modal_set_manual_waktu()">
								<i class="fas fa-cog d-none d-md-inline"></i> Manual Set
							</button>
						</div>
						<div class="col-4">
							<?php $btn_color_class = $is_biru_active ? 'bg-blue' : 'bg-red'; ?>
							<button class="btn <?= $btn_color_class ?> btn-gradient w-100 h-100 py-2 py-lg-3 fs-5 fw-bold text-uppercase button-play-state btn-timer btn-toggle-waktu-tampil text-white" data-status-penampilan="sedang_tampil" onclick="sekretaris_pertandingan.toggle_timer()">
								<i class="fas fa-play d-none d-md-inline"></i> START
							</button>
						</div>
						<div class="col-4">
							<button class="btn btn-danger w-100 h-100 py-2 py-lg-3 fs-5 fw-bold text-uppercase btn-timer" onclick="sekretaris_pertandingan.reset_timer()">
								<i class="fas fa-undo d-none d-md-inline"></i> RESET
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- END TURN & DISQUALIFY -->
	<div class="col-12 block-end-match">
		<div class="row opacity mt-2">
			<div class="col-12">
				<div class="row g-2 align-items-stretch">
					<div class="col-8">
						<button class="btn w-100 h-100 btn-lg bg-white text-dark btn_selesai fw-bold fs-4 py-3 shadow-sm text-uppercase" onclick="sekretaris_pertandingan.selesai_penampilan()">End Turn</button>
					</div>
					<div class="col-4">
						<button <?= (($penampilan->diskualifikasi ?? 0) == 1) ? '' : 'style="display:none;"' ?> class="btn btn-info text-white w-100 h-100 btn-lg fw-bold fs-5 py-3 shadow-sm text-uppercase btn-batal-diskualifikasi" onclick="sekretaris_pertandingan.batalkan_diskualifikasi_peserta()">Cancel Disq.</button>
						<button <?= (($penampilan->diskualifikasi ?? 0) == 0) ? '' : 'style="display:none;"' ?> class="btn btn-danger text-white w-100 h-100 btn-lg fw-bold fs-5 py-3 shadow-sm text-uppercase btn-diskualifikasi" onclick="sekretaris_pertandingan.diskualifikasi_peserta()">Disqualify</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- POST-PERFORMANCE NAVIGATION (hidden) -->
	<div class="row mt-3 mb-1 block-navigasi-partai d-none opacity">
		<div class="col-12 mb-2">
			<div class="row mb-4">
				<div class="col-12">
					<p class="display-3 text-center text-white fw-bold">Artistic Performance Finished!</p>
				</div>
			</div>
			<div class="row mb-6">
				<div class="col-12">
					<button type="button" class="btn w-100 text-white h4 bg-warning bg-gradient py-4 shadow-lg" onclick="sekretaris_pertandingan.open_modal_input_juara()">Winner Decision</button>
				</div>
			</div>
			<!-- Switch turn button (battle-specific) -->
			<div class="row mb-3">
				<div class="col-12">
					<?php if ($is_biru_active) : ?>
						<button type="button" class="btn w-100 text-white h4 bg-red py-4" onclick="sekretaris_pertandingan.mulai_penampilan_seni('<?= esc($battle->id_penampilan_seni_merah ?? '') ?>')">Start Red Turn</button>
					<?php else : ?>
						<button type="button" class="btn w-100 text-white h4 bg-blue py-4" onclick="sekretaris_pertandingan.mulai_penampilan_seni('<?= esc($battle->id_penampilan_seni_biru ?? '') ?>')">Start Blue Turn</button>
					<?php endif; ?>
				</div>
			</div>
			<div class="row">
				<div class="col-12 col-md-6">
					<button class="btn bg-light bg-gradient h4 w-100 py-3" onclick="sekretaris_pertandingan.pindah_partai(<?= ($penampilan->nomor_partai ?? 1) - 1 ?>)">
						Previous Match
					</button>
				</div>
				<div class="col-12 col-md-6">
					<button class="btn bg-light bg-gradient h4 w-100 py-3" onclick="sekretaris_pertandingan.pindah_partai(<?= ($penampilan->nomor_partai ?? 1) + 1 ?>)">
						Next Match
					</button>
				</div>
			</div>
		</div>
		<div class="col-12 mb-4">
			<?= $this->include('pertandingan/sekretaris/components/_offcanvas_pindah_partai_seni') ?>
		</div>
	</div>

	<style>
		.bg-navbar {
			background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%) !important;
			border-bottom: 3px solid #d90429;
			box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
		}

		@media (orientation: landscape) and (max-width: 1280px) {
			.block-informasi, .block-atlet, .block-stopwatch, .block-end-match {
				margin-bottom: 0.75vh !important;
			}
			.block-informasi .px-4.py-3 {
				padding-top: 0.5rem !important;
				padding-bottom: 0.5rem !important;
			}
			.block-atlet .p-3 {
				padding-top: 1vh !important;
				padding-bottom: 1vh !important;
			}
			.timer-seni {
				font-size: clamp(4rem, 25vh, 8rem) !important;
				margin-bottom: 0 !important;
			}
			.btn-timer, .btn_selesai, .btn-diskualifikasi, .btn-batal-diskualifikasi {
				padding-top: 1vh !important;
				padding-bottom: 1vh !important;
			}
			.fs-4 {
				font-size: clamp(1.1rem, 4vh, 1.5rem) !important;
			}
			.fs-5 {
				font-size: clamp(0.9rem, 3vh, 1.1rem) !important;
			}
		}
	</style>
</div>

<!-- MODAL: Manual Atur Waktu -->
<div class="modal fade" id="modalManualAturWaktu" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Set Clock Manually</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form action="#" id="formManualAturWaktu">
					<div class="row justify-content-center my-4">
						<div class="col-2 px-1">
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-puluh-menit" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.puluh-menit', 1, 5, this, '.btn-puluh-menit')">+</button>
							</div></div>
							<div class="row"><div class="col-12">
								<p class="text-center h1 m-0 py-2 bg-gradient-180-white puluh-menit">0</p>
							</div></div>
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-puluh-menit" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.puluh-menit', -1, 5, this, '.btn-puluh-menit')">-</button>
							</div></div>
						</div>
						<div class="col-2 px-1">
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-satuan-menit" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.satuan-menit', 1, 9, this, '.btn-satuan-menit')">+</button>
							</div></div>
							<div class="row"><div class="col-12">
								<p class="text-center h1 m-0 py-2 bg-gradient-180-white satuan-menit">0</p>
							</div></div>
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-satuan-menit" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.satuan-menit', -1, 9, this, '.btn-satuan-menit')">-</button>
							</div></div>
						</div>
						<div class="col-1 text-center px-1">
							<div class="row h-100 align-items-center"><p class="h3">:</p></div>
						</div>
						<div class="col-2 px-1">
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-puluh-detik" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.puluh-detik', 1, 5, this, '.btn-puluh-detik')">+</button>
							</div></div>
							<div class="row"><div class="col-12">
								<p class="text-center h1 m-0 py-2 bg-gradient-180-white puluh-detik">0</p>
							</div></div>
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-puluh-detik" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.puluh-detik', -1, 5, this, '.btn-puluh-detik')">-</button>
							</div></div>
						</div>
						<div class="col-2 px-1">
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-satuan-detik" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.satuan-detik', 1, 9, this, '.btn-satuan-detik')">+</button>
							</div></div>
							<div class="row"><div class="col-12">
								<p class="text-center h1 m-0 py-2 bg-gradient-180-white satuan-detik">0</p>
							</div></div>
							<div class="row"><div class="col-12">
								<button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-satuan-detik" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.satuan-detik', -1, 9, this, '.btn-satuan-detik')">-</button>
							</div></div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary mb-0 h5" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-warning mb-0 h5" onclick="sekretaris_pertandingan.tetapkan_perubahan_manual_waktu()">Set</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Penentuan Juara (Battle) -->
<div class="modal fade" id="modal_penentuan_juara" tabindex="-1">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<form role="form" id="form_keputusan_pemenang">
				<div class="modal-header">
					<h4 class="modal-title">Winning Decision</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="mt-2 mb-3">
						<p class="text-center h5 mb-3">The Winner Is:</p>
						<div class="row justify-content-center">
							<div class="col-4">
								<input type="radio" class="btn-check radio_pemenang_atlet_biru" name="id_penampilan_seni_pemenang" id="penampilan_seni_biru" autocomplete="off" value="<?= esc($battle->id_penampilan_seni_biru ?? '') ?>" required>
								<label class="btn btn-lg btn-outline-info w-100 h5 btn-winner-blue" for="penampilan_seni_biru">Blue Corner</label>
							</div>
							<div class="col-4">
								<input type="radio" class="btn-check radio_pemenang_atlet_merah" name="id_penampilan_seni_pemenang" id="penampilan_seni_merah" autocomplete="off" value="<?= esc($battle->id_penampilan_seni_merah ?? '') ?>" required>
								<label class="btn btn-lg btn-outline-primary w-100 h5 btn-winner-red" for="penampilan_seni_merah">Red Corner</label>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row w-100">
						<div class="col-6">
							<button type="button" class="btn btn-default btn-lg w-100 h5" data-bs-dismiss="modal">Cancel</button>
						</div>
						<div class="col-6">
							<button type="button" class="btn btn-warning btn-lg w-100 h5" onclick="sekretaris_pertandingan.submit_input_juara_seni()">Select Winner</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Ganti Format Penilaian Seni (Battle) -->
<div class="modal fade" id="modal_ganti_format_penilaian" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="formGantiFormatPenilaian" method="POST" action="<?= base_url('sekretaris-pertandingan/ganti-format-penilaian-seni/' . ($penampilan->id_penampilan_seni ?? '')) ?>">
				<div class="modal-header">
					<h4 class="modal-title">Ganti Format Penilaian</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Scoring Format:</label>
						<select name="format_penilaian" class="form-control text-capitalize" required>
							<?php foreach ($data_format_penilaian_seni ?? [] as $format) : ?>
								<option value="<?= esc($format) ?>"><?= esc(str_replace(['_', '.json'], [' ', ''], $format)) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Number of Jury</label>
						<?php foreach ([3, 4, 5, 6, 8, 10] as $jml) : ?>
							<div class="form-check">
								<input class="form-check-input" type="radio" name="jumlah_juri" value="<?= $jml ?>" id="juriBattle<?= $jml ?>" required>
								<label class="form-check-label" for="juriBattle<?= $jml ?>"><?= $jml ?> Jury</label>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label">Mode</label>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="mode" value="penampilan_seni_ini" id="mode_battle_penampilan" checked>
							<label class="form-check-label" for="mode_battle_penampilan">Change only for this performance</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="mode" value="kompetisi_seni_ini" id="mode_battle_kompetisi">
							<label class="form-check-label" for="mode_battle_kompetisi">Change only for this Pool</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="mode" value="sub_kategori_seni_ini" id="mode_battle_subkategori">
							<label class="form-check-label" for="mode_battle_subkategori">
								Change for this whole category
								(<?= esc(($penampilan->jenis_seni ?? '') . ' ' . ($penampilan->jenis_kelamin ?? '') . ' ' . ($penampilan->nama_seni ?? '') . ' - ' . ($penampilan->nama_kategori_usia ?? '')) ?>)
							</label>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-bs-dismiss="modal">Tutup</button>
					<button type="submit" class="btn btn-primary">Ganti Format</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/shared_timer.js') ?>"></script>
<script src="<?= base_url('assets/js/penilaian/sekretaris_seni.js') ?>"></script>
<script>
$(document).ready(function() {
	const penampilan_seni_berlangsung = <?= json_encode($penampilan ?? new stdClass()) ?>;
	const waktu_tampil = <?= json_encode($penampilan->waktu_tampil ?? 0) ?>;
	sekretaris_pertandingan.init(penampilan_seni_berlangsung, waktu_tampil);

	ui.animateIn();

	// Battle winner radio toggle styling
	$('.radio_pemenang_atlet_biru').on('change', function(e) {
		if (e.currentTarget.checked) {
			$('.btn-winner-blue').addClass('bg-blue text-white').removeClass('btn-outline-info');
			$('.btn-winner-red').removeClass('bg-red text-white').addClass('btn-outline-primary');
		}
	});
	$('.radio_pemenang_atlet_merah').on('change', function(e) {
		if (e.currentTarget.checked) {
			$('.btn-winner-red').addClass('bg-red text-white').removeClass('btn-outline-primary');
			$('.btn-winner-blue').removeClass('bg-blue text-white').addClass('btn-outline-info');
		}
	});
});

const ui = {
	animateIn: function() {
		$('.block-informasi').children('.opacity').each(function(i, v) {
			setTimeout(() => {
				$(v).addClass('animated slideInDown').removeClass('opacity');
			}, i * 150);
		});
		let delayAtlet = ($('.block-informasi').children('.opacity').length * 150) + 200;
		setTimeout(() => {
			$('.block-atlet .atlet-biru').addClass('animated slideInLeft').removeClass('opacity');
			$('.block-atlet .atlet-merah').addClass('animated slideInRight').removeClass('opacity');
			setTimeout(() => {
				$('.block-stopwatch').children('.opacity').addClass('animated slideInUp').removeClass('opacity');
				setTimeout(() => {
					$('.block-end-match').children('.row.opacity').addClass('animated slideInUp').removeClass('opacity');
				}, 300);
			}, 300);
		}, delayAtlet);
	},
	animateOut: function() {
		$('.block-informasi, .block-atlet, .block-stopwatch, .block-end-match').addClass('animated fadeOut');
		setTimeout(() => {
			$('.block-informasi, .block-atlet, .block-stopwatch, .block-end-match').addClass('d-none');
		}, 1000);
	},
	animateInNavigasiPartai: function() {
		const container = document.querySelector('.block-navigasi-partai');
		if (!container) return;
		container.classList.remove('d-none');
		container.style.opacity = '0';
		container.style.transform = 'translateY(50px)';
		container.style.transition = 'all 0.6s ease';
		setTimeout(() => {
			container.style.opacity = '1';
			container.style.transform = 'translateY(0)';
		}, 50);
	},
	animateOutNavigasiPartai: function() {
		const container = document.querySelector('.block-navigasi-partai');
		if (!container) return;
		container.style.opacity = '0';
		container.style.transform = 'translateY(50px)';
		setTimeout(() => { container.classList.add('d-none'); }, 600);
	}
};
</script>
<?= $this->endSection() ?>
