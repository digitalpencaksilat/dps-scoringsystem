<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/sekretaris.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('components/navbar_sekretaris', ['active' => 'dashboard', 'page_type' => 'home']) ?>
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
