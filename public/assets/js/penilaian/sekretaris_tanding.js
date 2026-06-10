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

		// AJAX sync
		$.post(window.location.origin + '/sekretaris-pertandingan/toggle-timer-tanding/' + this.pertandingan.id_pertandingan, {
			status: this.is_playing ? 'playing' : 'paused',
			waktu: shared_timer.time_seconds
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
				shared_timer.reset(this.pertandingan.durasi_ronde || 120);
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
		const puluhMenit = parseInt($('.puluh-menit').text()) || 0;
		const satuanMenit = parseInt($('.satuan-menit').text()) || 0;
		const puluhDetik = parseInt($('.puluh-detik').text()) || 0;
		const satuanDetik = parseInt($('.satuan-detik').text()) || 0;

		const totalSeconds = ((puluhMenit * 10) + satuanMenit) * 60 + (puluhDetik * 10) + satuanDetik;
		shared_timer.set_time(totalSeconds);
		this._emit_waktu('SET', totalSeconds);

		$('#modalManualAturWaktu').modal('hide');
	},

	/**
	 * Navigate to round
	 */
	pindah_ronde: function(ronde) {
		this.ronde_aktif = ronde;

		// Update UI
		$('.btn-ronde').removeClass('btn-warning').addClass('btn-outline-light');
		$(`.btn-ronde[data-ronde="${ronde}"]`).removeClass('btn-outline-light').addClass('btn-warning');

		// Reset timer for new round
		shared_timer.reset(this.pertandingan.durasi_ronde || 120);
		this.is_playing = false;
		this._update_play_button();

		// AJAX
		$.post(window.location.origin + '/sekretaris-pertandingan/pindah-ronde-tanding/' + this.pertandingan.id_pertandingan, {
			ronde: ronde
		});

		this._emit_waktu('PINDAH_RONDE', shared_timer.time_seconds);
	},

	/**
	 * End match - submit winner decision
	 */
	selesaikan_pertandingan: function() {
		const pemenang = $('input[name="pemenang"]:checked').val();
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
				$.post(window.location.origin + '/sekretaris-pertandingan/selesaikan-pertandingan/' + this.pertandingan.id_pertandingan, {
					id_pemenang: pemenang,
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
	 * Jump to different match
	 */
	pindah_partai: function(nomor_partai) {
		$.post(window.location.origin + '/sekretaris-pertandingan/pindah-partai-tanding/' + nomor_partai, function(response) {
			if (response.status) {
				window.location.reload();
			} else {
				Swal.fire('Info', response.message || 'Partai tidak ditemukan', 'info');
			}
		}, 'json').fail(function() {
			window.location.href = window.location.origin + '/sekretaris-pertandingan/pindah-partai-tanding/' + nomor_partai;
		});
	},

	/**
	 * Change time settings
	 */
	ubah_waktu: function() {
		const data = {
			jumlah_ronde: $('input[name="jumlah_ronde"]:checked').val(),
			durasi_ronde: $('#durasi_ronde').val(),
			durasi_istirahat: $('#durasi_istirahat').val(),
			mode: $('input[name="mode_ubah_waktu"]:checked').val()
		};

		$.post(window.location.origin + '/sekretaris-pertandingan/ubah-waktu-tanding/' + this.pertandingan.id_pertandingan, data, function(response) {
			if (response.status) {
				Swal.fire('Berhasil', 'Pengaturan waktu berhasil diubah', 'success').then(() => {
					window.location.reload();
				});
			} else {
				Swal.fire('Error', response.message || 'Gagal mengubah waktu', 'error');
			}
		}, 'json');
	},

	/**
	 * Save sound settings
	 */
	simpan_pengaturan_suara: function() {
		const gongType = $('#jenis_gong').val();
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
			this.socket.emit('KONTROL_WAKTU', {
				id_pertandingan: this.pertandingan.id_pertandingan,
				action: action,
				waktu: waktu,
				ronde: this.ronde_aktif
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
