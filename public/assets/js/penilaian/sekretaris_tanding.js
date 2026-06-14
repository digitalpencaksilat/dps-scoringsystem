/**
 * DPS Scoring System - Sekretaris Tanding Timer JS
 * Handles tanding match timer controls, round navigation, and match flow.
 * Parity with legacy CI3 sekretaris_pertandingan/tanding.js
 */
const sekretaris_pertandingan = {
	// State
	pertandingan: null,
	waktu_pertandingan: 0,
	ronde_aktif: 1,
	is_playing: false,
	socket: null,

	/**
	 * Initialize
	 */
	init: function(pertandingan, waktu_pertandingan) {
		this.pertandingan = pertandingan;
		// FIX: legacy field is waktu_per_ronde, not durasi_ronde
		this.waktu_pertandingan = waktu_pertandingan || (pertandingan.waktu_per_ronde || 120);
		this.ronde_aktif = pertandingan.ronde_pertandingan || 1;

		// Init shared timer (countdown mode for tanding)
		shared_timer.init({
			countdown: true,
			seconds: this.waktu_pertandingan,
			max_seconds: pertandingan.waktu_per_ronde || 120,
			on_tick: this._on_tick.bind(this),
			on_finish: this._on_ronde_finish.bind(this),
		});

		// Connect socket
		this._connect_socket();
	},

	/**
	 * Toggle timer (start/pause)
	 */
	toggle_timer: function() {
		this.is_playing = shared_timer.toggle();
		this._update_play_button();

		// Emit to realtime server
		this._emit_waktu('TOGGLE', shared_timer.time_seconds);

		// FIX: controller reads 'status_pertandingan' expecting 'berlangsung'/'berhenti'
		$.post(window.location.origin + '/sekretaris-pertandingan/toggle-timer-tanding/' + this.pertandingan.id_pertandingan, {
			status_pertandingan: this.is_playing ? 'berlangsung' : 'berhenti',
			waktu: shared_timer.time_seconds,
			data_waktu: JSON.stringify({ sisa_waktu: shared_timer.time_seconds, ronde: this.ronde_aktif })
		});
	},

	/**
	 * Reset timer
	 */
	reset_timer: function() {
		Swal.fire({
			title: 'Reset Timer?',
			text: 'Timer akan direset ke waktu awal ronde',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Ya, Reset',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				// FIX: field is waktu_per_ronde, not durasi_ronde
				shared_timer.reset(this.pertandingan.waktu_per_ronde || 120);
				this.is_playing = false;
				this._update_play_button();
				this._emit_waktu('RESET', shared_timer.time_seconds);
			}
		});
	},

	/**
	 * Open manual clock set modal
	 */
	open_modal_set_manual_waktu: function() {
		// Pre-fill current time digits
		const currentTime = shared_timer.time_seconds;
		const mins = Math.floor(currentTime / 60);
		const secs = currentTime % 60;

		$('.puluh-menit').text(Math.floor(mins / 10));
		$('.satuan-menit').text(mins % 10);
		$('.puluh-detik').text(Math.floor(secs / 10));
		$('.satuan-detik').text(secs % 10);

		$('#modalManualAturWaktu').modal('show');
	},

	/**
	 * Change digit in manual time setter
	 */
	ubah_manual_digit_waktu: function(selector, increment, maxVal, btn, btnClass) {
		let current = parseInt($(selector).text()) || 0;
		current += increment;
		if (current > maxVal) current = 0;
		if (current < 0) current = maxVal;
		$(selector).text(current);
	},

	/**
	 * Apply manual time change
	 */
	tetapkan_perubahan_manual_waktu: function() {
		const puluhMenit  = parseInt($('.puluh-menit').text()) || 0;
		const satuanMenit = parseInt($('.satuan-menit').text()) || 0;
		const puluhDetik  = parseInt($('.puluh-detik').text()) || 0;
		const satuanDetik = parseInt($('.satuan-detik').text()) || 0;

		const totalSeconds = ((puluhMenit * 10) + satuanMenit) * 60 + (puluhDetik * 10) + satuanDetik;
		shared_timer.set_time(totalSeconds);
		this._emit_waktu('SET', totalSeconds);

		$('#modalManualAturWaktu').modal('hide');
	},

	/**
	 * Navigate to round — shows confirmation first
	 */
	pindah_ronde: function(ronde) {
		Swal.fire({
			title: 'Pindah ke Ronde ' + ronde + '?',
			text: 'Timer akan direset ke waktu awal ronde ' + ronde,
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: 'Ya, Pindah',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (!result.isConfirmed) return;

			this.ronde_aktif = ronde;

			// Update UI
			$('.btn-ronde').removeClass('btn-warning').addClass('btn-outline-light');
			$(`.btn-ronde[data-ronde="${ronde}"]`).removeClass('btn-outline-light').addClass('btn-warning');

			// Reset timer for new round
			// FIX: field is waktu_per_ronde, not durasi_ronde
			shared_timer.reset(this.pertandingan.waktu_per_ronde || 120);
			this.is_playing = false;
			this._update_play_button();

			// FIX: controller reads 'ronde_berikutnya', not 'ronde'
			$.post(window.location.origin + '/sekretaris-pertandingan/pindah-ronde-tanding/' + this.pertandingan.id_pertandingan, {
				ronde_berikutnya: ronde
			});

			this._emit_waktu('PINDAH_RONDE', shared_timer.time_seconds);
		});
	},

	/**
	 * End match - submit winner decision
	 */
	selesaikan_pertandingan: function() {
		const pemenang        = $('input[name="pemenang"]:checked').val();
		const jenisKemenangan = $('input[name="jenis_kemenangan"]:checked').val();

		if (!pemenang || !jenisKemenangan) {
			Swal.fire('Peringatan', 'Pilih pemenang dan jenis kemenangan terlebih dahulu', 'warning');
			return;
		}

		Swal.fire({
			title: 'Selesaikan Pertandingan?',
			text: 'Pastikan keputusan sudah benar',
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: 'Ya, Selesaikan',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				// FIX: controller reads 'sudut_pemenang', not 'id_pemenang'
				$.post(window.location.origin + '/sekretaris-pertandingan/selesaikan-pertandingan/' + this.pertandingan.id_pertandingan, {
					sudut_pemenang: pemenang,
					jenis_kemenangan: jenisKemenangan
				}, function(response) {
					if (response.status) {
						$('#modal_keputusan_pemenang').modal('hide');
						shared_timer.pause();
						ui.animateOut();
						setTimeout(() => { ui.animateInNavigasiPartai(); }, 1200);

						// Emit refresh
						if (sekretaris_pertandingan.socket) {
							sekretaris_pertandingan.socket.emit('trigger_refresh_tanding', {
								id_pertandingan: sekretaris_pertandingan.pertandingan.id_pertandingan
							});
						}
					} else {
						Swal.fire('Error', response.message || 'Gagal menyimpan', 'error');
					}
				}, 'json').fail(function() {
					Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
				});
			}
		});
	},

	/**
	 * Jump to different match by id_pertandingan
	 * NOTE: caller must pass id_pertandingan (PK), not nomor_partai
	 */
	pindah_partai: function(id_pertandingan) {
		if (!id_pertandingan || id_pertandingan <= 0) {
			Swal.fire('Info', 'Partai tidak tersedia', 'info');
			return;
		}
		$.post(window.location.origin + '/sekretaris-pertandingan/pindah-partai-tanding/' + id_pertandingan, function(response) {
			if (response.status) {
				window.location.reload();
			} else {
				Swal.fire('Info', response.message || 'Partai tidak ditemukan', 'info');
			}
		}, 'json').fail(function() {
			window.location.href = window.location.origin + '/sekretaris-pertandingan/pindah-partai-tanding/' + id_pertandingan;
		});
	},

	/**
	 * Change time settings (AJAX — controller now returns JSON)
	 */
	ubah_waktu: function() {
		// FIX: read correct field IDs and names matching the view
		const data = {
			jumlah_ronde:    $('input[name="jumlah_ronde"]:checked').val(),
			waktu_per_ronde: $('#waktu_per_ronde').val(),
			waktu_istirahat: $('#waktu_istirahat').val(),
			mode:            $('input[name="mode"]:checked', '#formUbahWaktu').val()
		};

		if (!data.jumlah_ronde || !data.waktu_per_ronde || !data.mode) {
			Swal.fire('Peringatan', 'Isi semua field konfigurasi waktu', 'warning');
			return;
		}

		$.post(window.location.origin + '/sekretaris-pertandingan/ubah-waktu-tanding/' + this.pertandingan.id_pertandingan, data, function(response) {
			if (response.status) {
				Swal.fire('Berhasil', 'Pengaturan waktu berhasil diubah', 'success').then(() => {
					window.location.reload();
				});
			} else {
				Swal.fire('Error', response.message || 'Gagal mengubah waktu', 'error');
			}
		}, 'json').fail(function() {
			Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
		});
	},

	/**
	 * Submit format score change via AJAX
	 */
	ganti_format_penilaian: function() {
		const form = $('#formGantiFormatPenilaian');
		const format     = form.find('select[name="format_penilaian"]').val();
		const jumlahJuri = form.find('input[name="jumlah_juri"]:checked').val();
		const mode       = form.find('input[name="mode"]:checked').val();

		if (!format || !jumlahJuri || !mode) {
			Swal.fire('Peringatan', 'Isi semua field format penilaian', 'warning');
			return;
		}

		$.post(form.attr('action'), {
			format_penilaian: format,
			jumlah_juri:      jumlahJuri,
			mode:             mode
		}, function(response) {
			if (response.status) {
				$('#modal_ganti_format_penilaian').modal('hide');
				Swal.fire({ icon: 'success', title: 'Format diganti', timer: 1500, showConfirmButton: false });
			} else {
				Swal.fire('Error', response.message || 'Gagal mengganti format', 'error');
			}
		}, 'json').fail(function() {
			Swal.fire('Error', 'Terjadi kesalahan jaringan', 'error');
		});
	},

	/**
	 * Save sound settings
	 */
	simpan_pengaturan_suara: function() {
		const gongType   = $('#jenis_gong').val();
		const beepEnabled = $('input[name="beep_alarm"]:checked').val() === '1';
		shared_timer.set_sound(gongType, beepEnabled);
		$('#modal_pengaturan_suara').modal('hide');
		Swal.fire({ icon: 'success', title: 'Tersimpan', timer: 1000, showConfirmButton: false });
	},

	// ===================== Private Methods =====================

	_on_tick: function(seconds) {
		this._emit_waktu('TICK', seconds);
	},

	_on_ronde_finish: function() {
		this.is_playing = false;
		this._update_play_button();
		this._emit_waktu('RONDE_SELESAI', 0);
	},

	_update_play_button: function() {
		const btn = $('.btn-toggle-waktu, .button-play-state');
		if (this.is_playing) {
			btn.html('<i class="fas fa-pause d-none d-md-inline"></i> PAUSE');
			btn.removeClass('btn-success').addClass('btn-warning');
			btn.addClass('is-playing');
		} else {
			btn.html('<i class="fas fa-play d-none d-md-inline"></i> START');
			btn.removeClass('btn-warning').addClass('btn-success');
			btn.removeClass('is-playing');
		}
	},

	_emit_waktu: function(action, waktu) {
		if (this.socket && this.pertandingan) {
			// Build enriched payload supaya client subscriber dapat drift-compensate langsung
			// tanpa nunggu polling response berikutnya.
			var nowMs = Date.now();
			var isRunning = (action === 'START' || action === 'RESUME' || (action === 'TOGGLE' && this.is_playing));

			this.socket.emit('KONTROL_WAKTU', {
				id_pertandingan: this.pertandingan.id_pertandingan,
				action: action,
				waktu: waktu,
				ronde: this.ronde_aktif,
				// Enriched: server-authoritative state hint untuk drift compensation
				state: isRunning ? 'running' : 'paused',
				sisa_waktu_at_save: waktu,
				started_at_ms: isRunning ? nowMs : null,
				server_now_ms: nowMs
			});
		}
	},

	_connect_socket: function() {
		if (typeof io === 'undefined' || !SOCKET_URL) return;
		try {
			this.socket = io(SOCKET_URL);
			this.socket.on('connect', () => {
				this.socket.emit('JOIN_ROOM', {
					id_pertandingan: this.pertandingan.id_pertandingan
				});
			});
		} catch (e) {
			console.warn('Socket connection failed:', e);
		}
	}
};
