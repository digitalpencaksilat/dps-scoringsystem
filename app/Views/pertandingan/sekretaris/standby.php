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
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" id="timerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="fas fa-stopwatch me-1"></i> Control Timer
					</a>
					<ul class="dropdown-menu dropdown-menu-dark border-0 shadow" aria-labelledby="timerDropdown" style="background: #222;">
						<li><a class="dropdown-item py-2" href="<?= base_url('sekretaris-pertandingan/timer-tanding') ?>" target="_blank">Timer Tanding</a></li>
						<li><hr class="dropdown-divider bg-secondary"></li>
						<li><a class="dropdown-item py-2" href="<?= base_url('sekretaris-pertandingan/timer-seni') ?>" target="_blank">Timer Seni (Pool)</a></li>
						<li><a class="dropdown-item py-2" href="<?= base_url('sekretaris-pertandingan/timer-seni/battle') ?>" target="_blank">Timer Seni (Battle)</a></li>
					</ul>
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
<div class="standby-wrapper" style="min-height: calc(100vh - 70px);">
    <!-- Icon -->
    <div class="standby-icon">
        <?php if (($mode_standby ?? 'tanding') === 'tanding'): ?>
            <i class="fa-solid fa-hand-fist"></i>
        <?php else: ?>
            <i class="fa-solid fa-masks-theater"></i>
        <?php endif; ?>
    </div>

    <!-- Badge -->
    <span class="standby-badge">
        <i class="fas fa-circle-dot fa-xs" style="color: var(--brand-primary);"></i>
        Sekretaris Pertandingan
    </span>

    <!-- Title -->
    <div class="standby-title">
        <?php if (($mode_standby ?? 'tanding') === 'tanding'): ?>
            Standby — No Match Playing
        <?php else: ?>
            Standby — No Performance Playing
        <?php endif; ?>
    </div>

    <!-- Subtitle -->
    <p class="standby-subtitle">
        <?php if (($mode_standby ?? 'tanding') === 'tanding'): ?>
            Pilih partai tanding untuk memulai pertandingan
        <?php else: ?>
            Pilih penampilan seni untuk memulai penilaian
        <?php endif; ?>
    </p>

    <!-- Action: pindah partai -->
    <div class="mt-3 position-relative" style="z-index: 1;">
        <?php if (($mode_standby ?? 'tanding') === 'tanding'): ?>
            <?= $this->include('pertandingan/sekretaris/components/_offcanvas_pindah_partai_tanding') ?>
        <?php else: ?>
            <?= $this->include('pertandingan/sekretaris/components/_offcanvas_pindah_partai_seni') ?>
        <?php endif; ?>
    </div>

    <!-- Spinner -->
    <div class="standby-spinner">
        <div class="spinner-border" role="status" aria-hidden="true"></div>
    </div>
</div>

<script>
const sekretaris_pertandingan = {
	// Dipakai oleh tombol "Pilih" pada offcanvas/tabel pindah partai di halaman standby.
	pindah_partai: function(nomor_partai) {
		$.post("<?= base_url('sekretaris-pertandingan/pindah-partai-seni') ?>",
			{ partai_selanjutnya: nomor_partai },
			function(response) {
				if (response.status) {
					// Redirect explicit ke timer-seni
					window.location.href = "<?= base_url('sekretaris-pertandingan/timer-seni') ?>";
				} else {
					Swal.fire('Info', response.message || 'Partai tidak ditemukan', 'info');
				}
			},
			"json"
		).fail(function(xhr) {
			Swal.fire('Error', 'Gagal pindah partai. Status: ' + xhr.status, 'error');
		});
	}
};

const perangkat_pertandingan = {
	refresh_status_seni_standby: () => {
		$.post("<?= base_url('sekretaris-pertandingan/refresh-status-seni') ?>",
			function(data) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {
					setTimeout(() => {
						perangkat_pertandingan.refresh_status_seni_standby();
					}, 5000);
				}
			},
			"json"
		);
	},
	refresh_status_pertandingan_standby: () => {
		$.post("<?= base_url('sekretaris-pertandingan/refresh-status-pertandingan') ?>",
			function(data) {
				if (data.status === true && data.reload === true) {
					window.location.reload();
				} else {
					setTimeout(() => {
						perangkat_pertandingan.refresh_status_pertandingan_standby();
					}, 5000);
				}
			},
			"json"
		);
	}
};

$(document).ready(function() {
	const tipe = "<?= esc($mode_standby ?? 'tanding') ?>";
	if (tipe == "tanding") {
		perangkat_pertandingan.refresh_status_pertandingan_standby();
	} else {
		perangkat_pertandingan.refresh_status_seni_standby();
	}
});
</script>
<?= $this->endSection() ?>
