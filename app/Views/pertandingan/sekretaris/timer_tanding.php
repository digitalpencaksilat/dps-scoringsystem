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
					<a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_pengaturan_suara">
						<i class="fas fa-volume-up me-1"></i> Sound Setting
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_ganti_format_penilaian">
						<i class="fas fa-exchange-alt me-1"></i> Format Score
					</a>
				</li>
				<li class="nav-item">
					<a class="nav-link cursor-pointer" data-bs-toggle="modal" data-bs-target="#modal_ubah_waktu">
						<i class="fas fa-clock me-1"></i> Change Time
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
<div class="container-fluid min-vh-100 pt-2 pt-lg-3 bg-super-dark bg-gradient overflow-hidden">

	<!-- TOP INFO BOXES -->
	<div class="row mb-3 g-2 g-md-3 block-informasi justify-content-center">
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold"><?= esc($pertandingan->nama_gelanggang ?? '') ?></span>
			</div>
		</div>
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold">Partai <?= esc($pertandingan->nomor_partai ?? '') ?></span>
			</div>
		</div>
		<div class="col-auto opacity flex-fill">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center justify-content-center">
				<span class="fs-5 fw-bold text-wrap text-capitalize">
					<?= esc(($pertandingan->nama_kategori_usia ?? '') . ' ' . ($pertandingan->jenis_kelamin ?? '') . ' - ' . ($pertandingan->label ?? '')) ?>
				</span>
			</div>
		</div>
		<div class="col-auto opacity">
			<div class="px-4 py-3 bg-navbar rounded text-white text-center shadow-sm h-100 d-flex align-items-center">
				<span class="fs-4 fs-md-3 fw-bold"><?= esc(ucwords($pertandingan->babak ?? '')) ?></span>
			</div>
		</div>
	</div>

	<!-- ATHLETE CARDS: BLUE vs RED -->
	<div class="row mb-3 g-3 block-atlet">
		<div class="col-6 opacity atlet-biru">
			<div class="card-atlet bg-blue bg-gradient rounded-3 shadow text-white overflow-hidden h-100">
				<div class="d-flex align-items-center p-3 p-lg-4">
					<div class="flex-grow-1 d-flex flex-column justify-content-center">
						<span class="fs-2 fs-lg-1 fw-bolder text-truncate lh-sm"><?= esc($atlet_biru->nama_pendaftar ?? '-') ?></span>
						<span class="fs-5 mt-1 text-white-50 text-truncate"><?= esc($atlet_biru->nama_kontingen ?? '') ?></span>
					</div>
					<div class="ms-3 text-end">
						<span class="display-2 fw-bolder skor-biru lh-1"><?= (int)($pertandingan->skor_biru ?? 0) ?></span>
					</div>
				</div>
			</div>
		</div>
		<div class="col-6 opacity atlet-merah">
			<div class="card-atlet bg-red bg-gradient rounded-3 shadow text-white overflow-hidden h-100">
				<div class="d-flex align-items-center p-3 p-lg-4">
					<div class="text-start">
						<span class="display-2 fw-bolder skor-merah lh-1"><?= (int)($pertandingan->skor_merah ?? 0) ?></span>
					</div>
					<div class="flex-grow-1 ms-3 d-flex flex-column justify-content-center text-end">
						<span class="fs-2 fs-lg-1 fw-bolder text-truncate lh-sm"><?= esc($atlet_merah->nama_pendaftar ?? '-') ?></span>
						<span class="fs-5 mt-1 text-white-50 text-truncate"><?= esc($atlet_merah->nama_kontingen ?? '') ?></span>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- TIMER SECTION -->
	<div class="col-12 mb-3 block-stopwatch">
		<div class="row justify-content-center opacity">
			<div class="col-12 text-center">
				<div class="bg-navbar rounded-3 p-4 shadow-lg w-100">
					<div class="d-block timer-tanding text-center fw-bolder text-white lh-1 my-2" style="font-size: clamp(3.5rem, 12vw, 7rem); letter-spacing: 0.05em; font-variant-numeric: tabular-nums;">
						<?php
							$dataWaktu = json_decode($pertandingan->data_waktu ?? '{}');
							$waktuPerRonde = (int)($pertandingan->waktu_per_ronde ?? 120);
							$sisaWaktu = $waktuPerRonde;
							if (isset($dataWaktu->sisa_waktu)) $sisaWaktu = (int)$dataWaktu->sisa_waktu;
							$menit = str_pad(floor($sisaWaktu / 60), 2, '0', STR_PAD_LEFT);
							$detik = str_pad($sisaWaktu % 60, 2, '0', STR_PAD_LEFT);
						?>
						<span class="timer-menit"><?= $menit ?></span><span class="timer-separator">:</span><span class="timer-detik"><?= $detik ?></span>
					</div>

					<div class="row g-2 mb-3 justify-content-center block-navigasi-ronde">
						<?php
						$jumlahRonde = (int) ($pertandingan->jumlah_ronde ?? 3);
						$rondeAktif  = (int) ($pertandingan->ronde_pertandingan ?? 1);
						for ($r = 1; $r <= $jumlahRonde; $r++) :
						?>
							<div class="col-auto">
								<button class="btn <?= ($r == $rondeAktif) ? 'btn-warning' : 'btn-outline-light' ?> fw-bold btn-ronde px-4 py-2"
									data-ronde="<?= $r ?>"
									onclick="sekretaris_pertandingan.pindah_ronde(<?= $r ?>)">
									Ronde <?= $r ?>
								</button>
							</div>
						<?php endfor; ?>
					</div>

					<div class="row g-2 mt-2 block-kendali-waktu">
						<div class="col-3">
							<button class="btn btn-light w-100 h-100 py-3 fs-5 fw-bold text-uppercase text-dark rounded-3" onclick="sekretaris_pertandingan.open_modal_set_manual_waktu()">
								<i class="fas fa-cog d-none d-md-inline me-1"></i>Manual
							</button>
						</div>
						<div class="col-3">
							<button class="btn btn-warning btn-gradient w-100 h-100 py-3 fs-5 fw-bold text-uppercase button-play-state btn-timer btn-toggle-waktu rounded-3" onclick="sekretaris_pertandingan.toggle_timer()">
								<i class="fas fa-play d-none d-md-inline me-1"></i>START
							</button>
						</div>
						<div class="col-3">
							<button class="btn btn-danger w-100 h-100 py-3 fs-5 fw-bold text-uppercase btn-timer rounded-3" onclick="sekretaris_pertandingan.reset_timer()">
								<i class="fas fa-undo d-none d-md-inline me-1"></i>RESET
							</button>
						</div>
						<div class="col-3">
							<button class="btn btn-info text-white w-100 h-100 py-3 fs-5 fw-bold text-uppercase btn-timer rounded-3" data-bs-toggle="modal" data-bs-target="#modal_info_penimbangan">
								<i class="fas fa-weight d-none d-md-inline me-1"></i>Weight
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- END MATCH BUTTON -->
	<div class="col-12 block-end-match">
		<div class="row opacity mt-2">
			<div class="col-12">
				<button class="btn w-100 btn-lg bg-white text-dark btn_selesai fw-bold fs-4 py-3 shadow-sm text-uppercase" data-bs-toggle="modal" data-bs-target="#modal_keputusan_pemenang">
					End Match
				</button>
			</div>
		</div>
	</div>

	<!-- JUMP TO MATCH — selalu visible di bawah, sebelum navigasi partai -->
	<div class="col-12 mt-3 block-jump-to-match d-none">
		<div class="row">
			<div class="col-12">
				<button class="btn btn-outline-light w-100 py-3 fw-bold fs-5" type="button"
					data-bs-toggle="offcanvas" data-bs-target="#offcanvasPindahPartaiTanding"
					aria-controls="offcanvasPindahPartaiTanding">
					<i class="fas fa-list me-2"></i> Jump To Match
				</button>
			</div>
		</div>
	</div>

	<div class="row my-5 block-navigasi-partai d-none opacity">
		<div class="col-12 mb-2">
			<div class="row mb-4">
				<div class="col-12">
					<p class="display-3 text-center text-white fw-bold">Match Finished!</p>
				</div>
			</div>
			<div class="row">
				<div class="col-12 col-md-6 mb-2">
					<?php if (!empty($partai_prev)) : ?>
						<button class="btn bg-light bg-gradient h4 w-100 py-3" onclick="sekretaris_pertandingan.pindah_partai(<?= (int) $partai_prev->id_pertandingan ?>)">
							<i class="fas fa-arrow-left me-2"></i> Previous Match
							<br><small class="text-primary">Partai <?= esc($partai_prev->nomor_partai ?? '') ?></small>
						</button>
					<?php else : ?>
						<button class="btn bg-light bg-gradient h4 w-100 py-3" disabled>
							<i class="fas fa-arrow-left me-2"></i> Previous Match
							<br><small class="text-muted">Tidak ada</small>
						</button>
					<?php endif; ?>
				</div>
				<div class="col-12 col-md-6 mb-2">
					<?php if (!empty($partai_next)) : ?>
						<button class="btn bg-light bg-gradient h4 w-100 py-3" onclick="sekretaris_pertandingan.pindah_partai(<?= (int) $partai_next->id_pertandingan ?>)">
							Next Match <i class="fas fa-arrow-right ms-2"></i>
							<br><small class="text-primary">Partai <?= esc($partai_next->nomor_partai ?? '') ?></small>
						</button>
					<?php else : ?>
						<button class="btn bg-light bg-gradient h4 w-100 py-3" disabled>
							Next Match <i class="fas fa-arrow-right ms-2"></i>
							<br><small class="text-muted">Tidak ada</small>
						</button>
					<?php endif; ?>
				</div>
			</div>
			</div>
			</div>
</div>

<!-- MODAL: Keputusan Pemenang -->
<div class="modal fade" id="modal_keputusan_pemenang" tabindex="-1">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<form role="form" id="form_keputusan_pemenang">
				<div class="modal-header">
					<h4 class="modal-title">Keputusan Pemenang</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="mt-2 mb-3">
						<p class="text-center h5 mb-3">Pemenang Pertandingan:</p>
						<div class="row justify-content-center g-3">
							<div class="col-5">
								<input type="radio" class="btn-check" name="pemenang" id="pemenang_biru" autocomplete="off"
									value="biru" required>
								<label class="btn btn-lg btn-outline-info w-100 h5 py-3 btn-winner-blue" for="pemenang_biru">
									<i class="fas fa-user me-1"></i> Sudut Biru
								</label>
							</div>
							<div class="col-5">
								<input type="radio" class="btn-check" name="pemenang" id="pemenang_merah" autocomplete="off"
									value="merah" required>
								<label class="btn btn-lg btn-outline-danger w-100 h5 py-3 btn-winner-red" for="pemenang_merah">
									<i class="fas fa-user me-1"></i> Sudut Merah
								</label>
							</div>
						</div>
					</div>
					<div class="mt-4">
						<p class="text-center h5 mb-3">Jenis Kemenangan:</p>
						<div class="row justify-content-center g-2">
							<?php
							$jenis_menang = ['Poin', 'Teknik', 'Mutlak', 'Diskualifikasi', 'WO', 'BYE', 'Menang Angka'];
							foreach ($jenis_menang as $jIdx => $jenis) :
							?>
								<div class="col-auto">
									<input type="radio" class="btn-check" name="jenis_kemenangan" id="jenis_<?= $jIdx ?>" value="<?= $jenis ?>" required>
									<label class="btn btn-outline-dark" for="jenis_<?= $jIdx ?>"><?= $jenis ?></label>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<div class="row w-100">
						<div class="col-6">
							<button type="button" class="btn btn-default btn-lg w-100 h5" data-bs-dismiss="modal">Batal</button>
						</div>
						<div class="col-6">
							<button type="button" class="btn btn-warning btn-lg w-100 h5" onclick="sekretaris_pertandingan.selesaikan_pertandingan()">Selesaikan</button>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
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
				<div class="row justify-content-center my-4">
					<?php foreach (['puluh-menit', 'satuan-menit', 'puluh-detik', 'satuan-detik'] as $digIdx => $digClass) : ?>
						<?php if ($digIdx === 2) : ?><div class="col-1 text-center px-1"><div class="row h-100 align-items-center"><p class="h3">:</p></div></div><?php endif; ?>
						<div class="col-2 px-1">
							<div class="row"><div class="col-12"><button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-<?= $digClass ?>" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.<?= $digClass ?>', 1, <?= $digIdx >= 2 ? '5' : '9' ?>, this, '.btn-<?= $digClass ?>')">+</button></div></div>
							<div class="row"><div class="col-12"><p class="text-center h1 m-0 py-2 bg-gradient-180-white <?= $digClass ?>">0</p></div></div>
							<div class="row"><div class="col-12"><button type="button" class="btn btn-default w-100 d-block bg-dark bg-gradient text-white m-0 rounded-0 h2 btn-<?= $digClass ?>" onclick="sekretaris_pertandingan.ubah_manual_digit_waktu('.<?= $digClass ?>', -1, <?= $digIdx >= 2 ? '5' : '9' ?>, this, '.btn-<?= $digClass ?>')">-</button></div></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary mb-0 h5" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-warning mb-0 h5" onclick="sekretaris_pertandingan.tetapkan_perubahan_manual_waktu()">Set</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Ubah Waktu -->
<div class="modal fade" id="modal_ubah_waktu" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form id="formUbahWaktu">
				<div class="modal-header"><h5 class="modal-title">Configure Time</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Jumlah Ronde</label>
						<div class="d-flex gap-3">
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_ronde" value="2" id="ronde2" <?= ($pertandingan->jumlah_ronde ?? 3) == 2 ? 'checked' : '' ?>><label class="form-check-label" for="ronde2">2 Ronde</label></div>
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_ronde" value="3" id="ronde3" <?= ($pertandingan->jumlah_ronde ?? 3) == 3 ? 'checked' : '' ?>><label class="form-check-label" for="ronde3">3 Ronde</label></div>
						</div>
					</div>
					<div class="mb-3"><label class="form-label" for="waktu_per_ronde">Durasi per Ronde (detik)</label><input type="number" class="form-control" id="waktu_per_ronde" name="waktu_per_ronde" value="<?= esc($pertandingan->waktu_per_ronde ?? 120) ?>" min="30" max="600"></div>
					<div class="mb-3"><label class="form-label" for="waktu_istirahat">Waktu Istirahat (detik)</label><input type="number" class="form-control" id="waktu_istirahat" name="waktu_istirahat" value="<?= esc($pertandingan->waktu_istirahat ?? 60) ?>" min="10" max="300"></div>
					<div class="mb-3">
						<label class="form-label">Mode Perubahan</label>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="pertandingan_ini" id="mode_pertandingan" checked><label class="form-check-label" for="mode_pertandingan">Pertandingan ini saja</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kelas_ini" id="mode_kelas"><label class="form-check-label" for="mode_kelas">Seluruh kelas ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kategori_lomba_ini" id="mode_kategori"><label class="form-check-label" for="mode_kategori">Seluruh kategori ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="gelanggang_ini" id="mode_gelanggang"><label class="form-check-label" for="mode_gelanggang">Seluruh arena ini</label></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-primary" onclick="sekretaris_pertandingan.ubah_waktu()">Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Ganti Format Penilaian -->
<div class="modal fade" id="modal_ganti_format_penilaian" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<form id="formGantiFormatPenilaian" method="POST" action="<?= base_url('sekretaris-pertandingan/ganti-format-penilaian-tanding/' . ($pertandingan->id_pertandingan ?? '')) ?>">
				<div class="modal-header"><h4 class="modal-title">Ganti Format Penilaian</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Format Penilaian:</label>
						<select name="format_penilaian" class="form-control text-capitalize" required>
							<?php foreach ($data_format_penilaian ?? [] as $format) : ?>
								<option value="<?= esc($format) ?>"><?= esc(str_replace(['_', '.json'], [' ', ''], $format)) ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Jumlah Juri</label>
						<?php foreach ([1, 3, 4, 5] as $jml) : ?>
							<div class="form-check"><input class="form-check-input" type="radio" name="jumlah_juri" value="<?= $jml ?>" id="juri<?= $jml ?>" required><label class="form-check-label" for="juri<?= $jml ?>"><?= $jml ?> Juri</label></div>
						<?php endforeach; ?>
					</div>
					<div class="mb-3">
						<label class="form-label">Mode</label>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="pertandingan_ini" id="mode_fp_pertandingan" checked><label class="form-check-label" for="mode_fp_pertandingan">Pertandingan ini saja</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kelas_ini" id="mode_fp_kelas"><label class="form-check-label" for="mode_fp_kelas">Seluruh kelas ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="kategori_lomba_ini" id="mode_fp_kategori"><label class="form-check-label" for="mode_fp_kategori">Seluruh kategori ini</label></div>
						<div class="form-check"><input class="form-check-input" type="radio" name="mode" value="gelanggang_ini" id="mode_fp_gelanggang"><label class="form-check-label" for="mode_fp_gelanggang">Seluruh arena ini</label></div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
					<button type="button" class="btn btn-primary" onclick="sekretaris_pertandingan.ganti_format_penilaian()">Ganti Format</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL: Pengaturan Suara -->
<div class="modal fade" id="modal_pengaturan_suara" tabindex="-1">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Pengaturan Suara</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<div class="mb-3">
					<label class="form-label">Jenis Gong</label>
					<select class="form-select" id="jenis_gong">
						<option value="gong_1">Gong 1</option><option value="gong_2">Gong 2</option><option value="whistle_1">Whistle 1</option><option value="whistle_2">Whistle 2</option>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label">Alarm Beep (10 detik terakhir)</label>
					<div class="form-check"><input class="form-check-input" type="radio" name="beep_alarm" value="1" id="beep_ya" checked><label class="form-check-label" for="beep_ya">Ya</label></div>
					<div class="form-check"><input class="form-check-input" type="radio" name="beep_alarm" value="0" id="beep_tidak"><label class="form-check-label" for="beep_tidak">Tidak</label></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
				<button type="button" class="btn btn-primary" onclick="sekretaris_pertandingan.simpan_pengaturan_suara()">Simpan</button>
			</div>
		</div>
	</div>
</div>

<!-- MODAL: Info Penimbangan -->
<div class="modal fade" id="modal_info_penimbangan" tabindex="-1">
	<div class="modal-dialog modal-fullscreen">
		<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Informasi Penimbangan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-6">
						<div class="card border-primary mb-3">
							<div class="card-header bg-blue text-white fw-bold">Sudut Biru - <?= esc($atlet_biru->nama_pendaftar ?? '-') ?></div>
							<div class="card-body">
								<table class="table table-sm">
									<tr><th>Berat Badan</th><td class="text-end"><?= esc($pertandingan->berat_biru ?? '-') ?> kg</td></tr>
									<tr><th>Status Timbang</th><td class="text-end"><?= esc($pertandingan->hasil_timbang_biru ?? 'belum_timbang') ?></td></tr>
								</table>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="card border-danger mb-3">
							<div class="card-header bg-red text-white fw-bold">Sudut Merah - <?= esc($atlet_merah->nama_pendaftar ?? '-') ?></div>
							<div class="card-body">
								<table class="table table-sm">
									<tr><th>Berat Badan</th><td class="text-end"><?= esc($pertandingan->berat_merah ?? '-') ?> kg</td></tr>
									<tr><th>Status Timbang</th><td class="text-end"><?= esc($pertandingan->hasil_timbang_merah ?? 'belum_timbang') ?></td></tr>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
		</div>
	</div>
</div>

<?= view('pertandingan/sekretaris/components/_offcanvas_pindah_partai_tanding') ?>

<style>
	.bg-navbar { background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%) !important; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); }
	@media (orientation: landscape) and (max-width: 1280px) {
		.block-informasi, .block-atlet, .block-stopwatch, .block-end-match { margin-bottom: 0.75vh !important; }
		.block-informasi .px-4.py-3 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
		.block-atlet .p-3 { padding-top: 1vh !important; padding-bottom: 1vh !important; }
		.timer-tanding { font-size: clamp(4rem, 25vh, 8rem) !important; margin-bottom: 0 !important; }
		.btn-timer, .btn_selesai { padding-top: 1vh !important; padding-bottom: 1vh !important; }
		.display-5 { font-size: clamp(1.5rem, 5vh, 2.5rem) !important; }
		.display-3 { font-size: clamp(2rem, 8vh, 3.5rem) !important; }
		.fs-4, .fs-5 { font-size: clamp(0.9rem, 2.5vh, 1.25rem) !important; }
	}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/shared_timer.js') ?>"></script>
<script src="<?= base_url('assets/js/penilaian/sekretaris_tanding.js') ?>"></script>
<script>
$(document).ready(function() {
	const pertandingan = <?= json_encode($pertandingan ?? new stdClass()) ?>;
	<?php
		$dw = $data_waktu ?? null;
		$sisaWaktuJs = 0;
		if (is_object($dw) && isset($dw->sisa_waktu)) {
			$sisaWaktuJs = (int) $dw->sisa_waktu;
		} else {
			$sisaWaktuJs = (int) ($pertandingan->waktu_per_ronde ?? 120);
		}
	?>
	const waktu_pertandingan = <?= $sisaWaktuJs ?>;
	sekretaris_pertandingan.init(pertandingan, waktu_pertandingan);
	ui.animateIn();
});

const ui = {
	animateIn: function() {
		$('.block-informasi').children('.opacity').each(function(i, v) {
			setTimeout(() => { $(v).addClass('animated slideInDown').removeClass('opacity'); }, i * 150);
		});
		let delayAtlet = ($('.block-informasi').children('.opacity').length * 150) + 200;
		setTimeout(() => {
			$('.block-atlet .atlet-biru').addClass('animated slideInLeft').removeClass('opacity');
			$('.block-atlet .atlet-merah').addClass('animated slideInRight').removeClass('opacity');
			setTimeout(() => {
				$('.block-stopwatch').children('.opacity').addClass('animated slideInUp').removeClass('opacity');
				setTimeout(() => { $('.block-end-match').children('.row.opacity').addClass('animated slideInUp').removeClass('opacity'); }, 300);
				setTimeout(() => {
					$('.block-jump-to-match').removeClass('d-none').hide().fadeIn(400);
				}, 500);
			}, 300);
		}, delayAtlet);
	},
	animateOut: function() {
		$('.block-informasi, .block-atlet, .block-stopwatch, .block-end-match, .block-jump-to-match').addClass('animated fadeOut');
		setTimeout(() => { $('.block-informasi, .block-atlet, .block-stopwatch, .block-end-match, .block-jump-to-match').addClass('d-none').removeClass('animated fadeOut'); }, 1000);
	},
	animateInNavigasiPartai: function() {
		const container = document.querySelector('.block-navigasi-partai');
		if (!container) return;
		container.classList.remove('d-none'); container.style.opacity = '0'; container.style.transform = 'translateY(50px)'; container.style.transition = 'all 0.6s ease';
		setTimeout(() => { container.style.opacity = '1'; container.style.transform = 'translateY(0)'; }, 50);
	},
	animateOutNavigasiPartai: function() {
		const container = document.querySelector('.block-navigasi-partai');
		if (!container) return;
		container.style.opacity = '0'; container.style.transform = 'translateY(50px)';
		setTimeout(() => { container.classList.add('d-none'); }, 600);
	}
};
</script>
<?= $this->endSection() ?>
