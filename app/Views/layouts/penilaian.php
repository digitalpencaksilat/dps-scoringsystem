<!DOCTYPE html>
<html lang="id">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Digital Pencak Silat - Scoring System">
	<meta name="author" content="Digital Scoring System">
	<meta name="theme-color" content="#890108">
	<title><?= esc($page_title ?? 'Sekretaris Pertandingan') ?></title>
	<link rel="icon" type="image/png" href="<?= base_url('assets/images/brand/dps/logo.ico') ?>">
	<base href="<?= base_url() ?>">

	<!-- Google Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

	<!-- Bootstrap 5.3.3 -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

	<!-- Font Awesome 6.5.2 -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

	<!-- DataTables -->
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

	<!-- Select2 -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">

	<!-- Custom CSS -->
	<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/penilaian.css') ?>">
	<?= $this->renderSection('styles') ?>

	<!-- jQuery -->
	<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
</head>

<body>
	<?= $this->renderSection('navbar') ?>

	<!-- Toast Container untuk notification (parity CI3 notification component) -->
	<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 11000;" id="dps-toast-container"></div>

	<?= $this->renderSection('content') ?>

	<!-- Bootstrap Bundle JS -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

	<!-- DataTables -->
	<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

	<!-- Select2 -->
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

	<!-- SweetAlert2 -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<!-- Socket.IO -->
	<script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

	<!-- Realtime config -->
	<script>
		const SOCKET_URL = "<?= env('REALTIME_SERVER_URL', 'http://localhost:3000') ?>";
		const BASE_URL = "<?= base_url() ?>";
		var CSRF_NAME = "<?= csrf_token() ?>";
		var CSRF_HASH = "<?= csrf_hash() ?>";
	</script>

	<!-- DPS Notification helper (parity CI3 notification component) -->
	<script>
		window.DPSNotify = (function() {
			function showBootstrap(type, message, title) {
				var bgClass = {
					'success': 'bg-success', 'error': 'bg-danger',
					'warning': 'bg-warning text-dark', 'info': 'bg-info text-dark'
				}[type] || 'bg-secondary';
				var iconClass = {
					'success': 'fa-check-circle', 'error': 'fa-times-circle',
					'warning': 'fa-exclamation-triangle', 'info': 'fa-info-circle'
				}[type] || 'fa-bell';
				var toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7);
				var html = '<div id="' + toastId + '" class="toast align-items-center text-white ' + bgClass + ' border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">' +
					'<div class="d-flex">' +
						'<div class="toast-body fw-bold">' +
							'<i class="fas ' + iconClass + ' me-2"></i>' +
							(title ? '<strong>' + title + '</strong> — ' : '') + message +
						'</div>' +
						'<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
					'</div></div>';
				var $container = document.getElementById('dps-toast-container');
				if (!$container) return;
				$container.insertAdjacentHTML('beforeend', html);
				var el = document.getElementById(toastId);
				if (window.bootstrap && bootstrap.Toast) {
					var toast = new bootstrap.Toast(el, { delay: type === 'error' ? 6000 : 3500 });
					toast.show();
					el.addEventListener('hidden.bs.toast', function() { el.remove(); });
				}
			}
			return {
				success: function(msg, title) { showBootstrap('success', msg, title); },
				error:   function(msg, title) { showBootstrap('error', msg, title); },
				warning: function(msg, title) { showBootstrap('warning', msg, title); },
				info:    function(msg, title) { showBootstrap('info', msg, title); }
			};
		})();

		// Auto-display flash messages from CI4 session
		<?php
			$flashKeys = ['success', 'error', 'warning', 'info', 'message'];
			foreach ($flashKeys as $key):
				$msg = session()->getFlashdata($key);
				if ($msg):
					$type = $key === 'message' ? 'info' : $key;
		?>
		DPSNotify.<?= $type ?>(<?= json_encode($msg) ?>);
		<?php
				endif;
			endforeach;
		?>
	</script>

	<?= $this->renderSection('scripts') ?>
</body>

</html>
