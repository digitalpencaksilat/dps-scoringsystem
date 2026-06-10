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
<div class="container-fluid min-vh-100 pt-2 pt-lg-2 bg-super-dark bg-gradient overflow-hidden">

	<!-- TOP SECTION: INFO BOXES -->
	<div class="row mb-3 g-2 g-md-3 block-informasi justify-content-center">
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold"><?= esc($partai_seni_berlangsung->nama_gelanggang ?? '') ?></span>
			</div>
		</div>
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold">Partai <?= esc($partai_seni_berlangsung->nomor_partai ?? '') ?></span>
			</div>
		</div>
		<div class="col-auto opacity flex-fill">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center justify-content-center">
				<span class="fs-5 fw-bold text-wrap text-capitalize">
					<?= esc(($penampilan_seni_berlangsung->nama_kategori_usia ?? '') . ' ' . ($penampilan_seni_berlangsung->jenis_kelamin ?? '') . ' ' . ucwords($penampilan_seni_berlangsung->jenis_seni ?? '') . ' ' . ($penampilan_seni_berlangsung->nama_seni ?? '') . ' Pool ' . ($penampilan_seni_berlangsung->nomor_pool ?? '')) ?>
				</span>
			</div>
		</div>
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold"><?= esc(ucwords($penampilan_seni_berlangsung->babak ?? '')) ?></span>
			</div>
		</div>
	</div>

	<!-- MIDDLE: ATHLETE INFO (single athlete, gold/warning style) -->
	<div class="row mb-3 block-atlet">
		<div class="col-12 opacity">
			<div class="bg-warning bg-gradient rounded shadow-sm py-4 text-white text-center overflow-hidden h-100 d-flex flex-column justify-content-center">
				<?php if (!empty($peserta_seni)) : ?>
					<?php foreach ($peserta_seni as $peserta) : ?>
						<span class="display-5 fw-bolder text-truncate"><?= esc($peserta->nama_pendaftar ?? '') ?></span>
					<?php endforeach; ?>
					<span class="fs-3 mt-1 text-truncate"><?= esc($peserta_seni[0]->nama_kontingen ?? '') ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- BOTTOM SECTION: TIMER -->
	<div class="col-12 mb-3 block-stopwatch">
		<div class="row justify-content-center opacity">
			<div class="col-12 text-center">
				<div class="bg-navbar rounded p-3 shadow-lg w-100">
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
							<button class="btn btn-warning btn-gradient w-100 h-100 py-2 py-lg-3 fs-5 fw-bold text-uppercase button-play-state btn-timer btn-toggle-waktu-tampil" data-status-penampilan="sedang_tampil" onclick="sekretaris_pertandingan.toggle_timer()">
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

	<!-- BOTTOM SECTION: END TURN & DISQUALIFY -->
	<div class="col-12 block-end-match">
		<div class="row opacity mt-2">
			<div class="col-12">
				<div class="row g-2 align-items-stretch">
					<div class="col-8">
						<button class="btn w-100 h-100 btn-lg bg-white text-dark btn_selesai fw-bold fs-4 py-3 shadow-sm text-uppercase" onclick="sekretaris_pertandingan.selesai_penampilan()">End Turn</button>
					</div>
					<div class="col-4">
						<button <?= (($penampilan_seni_berlangsung->diskualifikasi ?? 0) == 1) ? '' : 'style="display:none;"' ?> class="btn btn-info text-white w-100 h-100 btn-lg fw-bold fs-5 py-3 shadow-sm text-uppercase btn-batal-diskualifikasi" onclick="sekretaris_pertandingan.batalkan_diskualifikasi_peserta()">Cancel Disq.</button>
						<button <?= (($penampilan_seni_berlangsung->diskualifikasi ?? 0) == 0) ? '' : 'style="display:none;"' ?> class="btn btn-danger text-white w-100 h-100 btn-lg fw-bold fs-5 py-3 shadow-sm text-uppercase btn-diskualifikasi" onclick="sekretaris_pertandingan.diskualifikasi_peserta()">Disqualify</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- POST-PERFORMANCE NAVIGATION (hidden) -->
	<div class="row my-5 block-navigasi-partai d-none opacity">
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
			<div class="row">
				<div class="col-12 col-md-6">
					<button class="btn bg-light bg-gradient h4 w-100 py-3" onclick="sekretaris_pertandingan.pindah_partai(<?= ($partai_seni_berlangsung->nomor_partai ?? 1) - 1 ?>)">
						Previous Match
					</button>
				</div>
				<div class="col-12 col-md-6">
					<button class="btn bg-light bg-gradient h4 w-100 py-3" onclick="sekretaris_pertandingan.pindah_partai(<?= ($partai_seni_berlangsung->nomor_partai ?? 1) + 1 ?>)">
						Next Match
					</button>
				</div>
			</div>
		</div>
		<div class="col-12">
			<?= $this->include('pertandingan/sekretaris/components/_offcanvas_pindah_partai_seni') ?>
		</div>
	</div>

	<style>
		.bg-navbar {
			background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%) !important;
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
			.block-atlet .py-4 {
				padding-top: 1.5vh !important;
				padding-bottom: 1.5vh !important;
			}
			.timer-seni {
				font-size: clamp(4rem, 25vh, 8rem) !important;
				margin-bottom: 0 !important;
			}
			.btn-timer, .btn_selesai, .btn-diskualifikasi, .btn-batal-diskualifikasi {
				padding-top: 1vh !important;
				padding-bottom: 1vh !important;
			}
			.display-5 {
				font-size: clamp(1.5rem, 5vh, 2.5rem) !important;
			}
			.fs-3 {
				font-size: clamp(1rem, 3vh, 1.5rem) !important;
			}
			.fs-4, .fs-5 {
				font-size: clamp(0.9rem, 2.5vh, 1.25rem) !important;
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

<!-- MODAL: Penentuan Juara (Pool) -->
<div class="modal fade" id="modal_penentuan_juara" tabindex="-1">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Winner Decision</h4>
				<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form action="<?= base_url('sekretaris-pertandingan/input-manual-juara-seni') ?>" method="post" id="formJenisMedali">
					<input type="hidden" name="id_penampilan_seni" value="<?= esc($penampilan_seni_berlangsung->id_penampilan_seni ?? '') ?>">
					<table class="table table-striped" id="tabelInputJuara">
						<thead>
							<tr>
								<th>Name</th>
								<th>Final Score</th>
								<th>Time</th>
								<th>Deviation Standard</th>
								<th>Medal</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($penampilan_seni_lain ?? [] as $penSeni) : ?>
								<tr>
									<td class="align-middle"><?= esc($penSeni->anggota_kelompok_peserta_seni ?? '') ?></td>
									<td class="text-end align-middle"><?= esc($penSeni->nilai_akhir ?? '-') ?></td>
									<td class="text-end align-middle">
										<?php
										$wt = $penSeni->waktu_tampil ?? 0;
										echo (floor($wt / 60)) . 'm ' . ($wt % 60) . 's';
										?>
									</td>
									<td class="text-end align-middle">
										<?php
										$catatan = json_decode($penSeni->catatan_nilai_sama ?? '{}');
										echo esc($catatan->standar_deviasi ?? '');
										?>
									</td>
									<td>
										<?php $idKps = $penSeni->id_kelompok_peserta_seni ?? ''; ?>
										<div class="row">
											<div class="col-6 mb-2">
												<div class="form-check form-check-inline">
													<input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="medaliEmas<?= $idKps ?>" value="emas" <?= ($penSeni->jenis_medali_pool ?? '') == 'emas' ? 'checked' : '' ?>>
													<label class="form-check-label" for="medaliEmas<?= $idKps ?>">Gold</label>
												</div>
											</div>
											<div class="col-6 mb-2">
												<div class="form-check form-check-inline">
													<input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="medaliPerak<?= $idKps ?>" value="perak" <?= ($penSeni->jenis_medali_pool ?? '') == 'perak' ? 'checked' : '' ?>>
													<label class="form-check-label" for="medaliPerak<?= $idKps ?>">Silver</label>
												</div>
											</div>
											<div class="col-6 mb-2">
												<div class="form-check form-check-inline">
													<input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="medaliPerunggu<?= $idKps ?>" value="perunggu" <?= ($penSeni->jenis_medali_pool ?? '') == 'perunggu' ? 'checked' : '' ?>>
													<label class="form-check-label" for="medaliPerunggu<?= $idKps ?>">Bronze</label>
												</div>
											</div>
											<div class="col-6 mb-2">
												<div class="form-check form-check-inline">
													<input class="form-check-input" type="radio" name="jenis_medali[<?= $idKps ?>]" id="tanpaMedali<?= $idKps ?>" value="none" <?= ($penSeni->jenis_medali_pool ?? null) === null ? 'checked' : '' ?>>
													<label class="form-check-label" for="tanpaMedali<?= $idKps ?>">No Medal</label>
												</div>
											</div>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-bs-dismiss="modal">Close</button>
				<button type="button" onclick="sekretaris_pertandingan.submit_input_juara_seni()" class="btn btn-primary">Update Medal</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Ganti Format Penilaian Seni -->
<div class="modal fade" id="modal_ganti_format_penilaian" tabindex="-1">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<form id="formGantiFormatPenilaian" method="POST" action="<?= base_url('sekretaris-pertandingan/ganti-format-penilaian-seni/' . ($penampilan_seni_berlangsung->id_penampilan_seni ?? '')) ?>">
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
								<input class="form-check-input" type="radio" name="jumlah_juri" value="<?= $jml ?>" id="juriPool<?= $jml ?>" required>
								<label class="form-check-label" for="juriPool<?= $jml ?>"><?= $jml ?> Jury</label>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label">Mode</label>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="mode" value="penampilan_seni_ini" id="mode_pool_penampilan" checked>
							<label class="form-check-label" for="mode_pool_penampilan">Change only for this performance</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="mode" value="kompetisi_seni_ini" id="mode_pool_kompetisi">
							<label class="form-check-label" for="mode_pool_kompetisi">Change only for this Pool</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="mode" value="sub_kategori_seni_ini" id="mode_pool_subkategori">
							<label class="form-check-label" for="mode_pool_subkategori">
								Change for this whole category
								(<?= esc(($penampilan_seni_berlangsung->jenis_seni ?? '') . ' ' . ($penampilan_seni_berlangsung->jenis_kelamin ?? '') . ' ' . ($penampilan_seni_berlangsung->nama_seni ?? '') . ' - ' . ($penampilan_seni_berlangsung->nama_kategori_usia ?? '')) ?>)
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
	const penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung ?? new stdClass()) ?>;
	const waktu_tampil = <?= json_encode($waktu_tampil ?? 0) ?>;
	sekretaris_pertandingan.init(penampilan_seni_berlangsung, waktu_tampil);

	ui.animateIn();

	if ($('#tabelInputJuara').length) {
		$('#tabelInputJuara').DataTable({
			language: { paginate: { next: ">", previous: "<" } },
			autoWidth: false, paging: true, searching: true, ordering: true, info: true, responsive: true,
		});
	}
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
			$('.block-atlet').children('.opacity').addClass('animated fadeIn').removeClass('opacity');
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
