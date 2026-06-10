/**
 * DPS Scoring System - Sekretaris Seni Timer JS
 * Handles seni performance timer controls (pool & battle modes).
 * Parity with legacy CI3 sekretaris_pertandingan/seni.js
 */
const sekretaris_pertandingan = {
	// State
	penampilan_seni: null,
	waktu_tampil: 0,
	is_playing: false,
	socket: null,
	mode: 'pool', // 'pool' or 'battle'

	/**
	 * Initialize
	 */
	init: function(penampilan_seni, waktu_tampil) {
		this.penampilan_seni = penampilan_seni;
		this.waktu_tampil = waktu_tampil || 0;
		this.mode = penampilan_seni.sistem_pertandingan === 'battle' ? 'battle' : 'pool';

		// Init shared timer (countup mode for seni)
		shared_timer.init({
			countdown: false,
			seconds: this.waktu_tampil,
			max_seconds: 600, // 10 min max
			on_tick: this._on_tick.bind(this),
		});

		// If already running (resume from page reload)
		if (penampilan_seni.status_penampilan === 'sedang_tampil' && this.waktu_tampil > 0) {
			shared_timer.set_time(this.waktu_tampil);
		}

		// Connect socket
		this._connect_socket();
	},

	/**
	 * Toggle timer (start/pause)
	 */
	toggle_timer: function() {
		this.is_playing = shared_timer.toggle();
		this._update_play_button();

		// AJAX sync
		$.post(window.location.origin + '/sekretaris-pertandingan/toggle-timer-seni/' + this.penampilan_seni.id_penampilan_seni, {
			status: this.is_playing ? 'playing' : 'paused',
			waktu: shared_timer.time_seconds
		});

		this._emit_waktu('TOGGLE', shared_timer.time_seconds);
	},

	/**
	 * Reset timer
	 */
	reset_timer: function() {
		Swal.fire({
			title: 'Reset Timer?',
			text: 'Timer akan direset ke 00:00',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Ya, Reset',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				shared_timer.reset(0);
				this.is_playing = false;
				this._update_play_button();
				this._emit_waktu('RESET', 0);
			}
		});
	},

	/**
	 * Open manual clock set modal
	 */
	open_modal_set_manual_waktu: function() {
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
	 * End performance turn
	 */
	selesai_penampilan: function() {
		Swal.fire({
			title: 'Selesaikan Penampilan?',
			text: 'Pastikan penampilan sudah selesai',
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: 'Ya, Selesai',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				shared_timer.pause();
				this.is_playing = false;
				this._update_play_button();

				$.post(window.location.origin + '/sekretaris-pertandingan/selesaikan-penampilan-seni/' + this.penampilan_seni.id_penampilan_seni, {
					waktu_tampil: shared_timer.time_seconds
				}, function(response) {
					if (response.status) {
						ui.animateOut();
						setTimeout(() => { ui.animateInNavigasiPartai(); }, 1200);

						// Emit refresh
						if (sekretaris_pertandingan.socket) {
							sekretaris_pertandingan.socket.emit('trigger_refresh_seni', {
								id_penampilan_seni: sekretaris_pertandingan.penampilan_seni.id_penampilan_seni
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
	 * Disqualify performer
	 */
	diskualifikasi_peserta: function() {
		Swal.fire({
			title: 'Diskualifikasi Peserta?',
			text: 'Peserta akan didiskualifikasi',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#d33',
			confirmButtonText: 'Ya, Diskualifikasi',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				$.post(window.location.origin + '/sekretaris-pertandingan/diskualifikasi-penampilan-seni/' + this.penampilan_seni.id_penampilan_seni, function(response) {
					if (response.status) {
						$('.btn-diskualifikasi').hide();
						$('.btn-batal-diskualifikasi').show();
						Swal.fire({ icon: 'success', title: 'Didiskualifikasi', timer: 1500, showConfirmButton: false });
					} else {
						Swal.fire('Error', response.message || 'Gagal', 'error');
					}
				}, 'json');
			}
		});
	},

	/**
	 * Cancel disqualification
	 */
	batalkan_diskualifikasi_peserta: function() {
		Swal.fire({
			title: 'Batalkan Diskualifikasi?',
			icon: 'question',
			showCancelButton: true,
			confirmButtonText: 'Ya, Batalkan',
			cancelButtonText: 'Tidak'
		}).then((result) => {
			if (result.isConfirmed) {
				$.post(window.location.origin + '/sekretaris-pertandingan/batalkan-diskualifikasi-penampilan-seni/' + this.penampilan_seni.id_penampilan_seni, function(response) {
					if (response.status) {
						$('.btn-batal-diskualifikasi').hide();
						$('.btn-diskualifikasi').show();
						Swal.fire({ icon: 'success', title: 'Diskualifikasi Dibatalkan', timer: 1500, showConfirmButton: false });
					} else {
						Swal.fire('Error', response.message || 'Gagal', 'error');
					}
				}, 'json');
			}
		});
	},

	/**
	 * Open winner/medal decision modal
	 */
	open_modal_input_juara: function() {
		$('#modal_penentuan_juara').modal('show');
	},

	/**
	 * Submit medal/winner decision (Pool mode)
	 */
	submit_input_juara_seni: function() {
		if (this.mode === 'battle') {
			// Battle: submit winner selection
			const pemenang = $('input[name="id_penampilan_seni_pemenang"]:checked').val();
			if (!pemenang) {
				Swal.fire('Peringatan', 'Pilih pemenang terlebih dahulu', 'warning');
				return;
			}

			$.post(window.location.origin + '/sekretaris-pertandingan/pilih-pemenang-battle-seni/' + this.penampilan_seni.id_penampilan_seni, {
				id_penampilan_seni_pemenang: pemenang
			}, function(response) {
				if (response.status) {
					$('#modal_penentuan_juara').modal('hide');
					Swal.fire({ icon: 'success', title: 'Pemenang Disimpan', timer: 1500, showConfirmButton: false });
				} else {
					Swal.fire('Error', response.message || 'Gagal', 'error');
				}
			}, 'json');
		} else {
			// Pool: submit medal form
			const formData = $('#formJenisMedali').serialize();
			$.post(window.location.origin + '/sekretaris-pertandingan/input-manual-juara-seni', formData, function(response) {
				if (response.status) {
					$('#modal_penentuan_juara').modal('hide');
					Swal.fire({ icon: 'success', title: 'Medali Disimpan', timer: 1500, showConfirmButton: false });
				} else {
					Swal.fire('Error', response.message || 'Gagal', 'error');
				}
			}, 'json');
		}
	},

	/**
	 * Jump to different match/performance
	 */
	pindah_partai: function(nomor_partai) {
		$.post(window.location.origin + '/sekretaris-pertandingan/pindah-partai-seni/' + nomor_partai, function(response) {
			if (response.status) {
				window.location.reload();
			} else {
				Swal.fire('Info', response.message || 'Partai tidak ditemukan', 'info');
			}
		}, 'json').fail(function() {
			window.location.href = window.location.origin + '/sekretaris-pertandingan/pindah-partai-seni/' + nomor_partai;
		});
	},

	/**
	 * Start specific performance (battle mode - switch turns)
	 */
	mulai_penampilan_seni: function(id_penampilan_seni) {
		$.post(window.location.origin + '/sekretaris-pertandingan/mulai-penampilan/' + id_penampilan_seni, function(response) {
			if (response.status) {
				window.location.reload();
			} else {
				Swal.fire('Error', response.message || 'Gagal memulai penampilan', 'error');
			}
		}, 'json').fail(function() {
			window.location.href = window.location.origin + '/sekretaris-pertandingan/mulai-penampilan/' + id_penampilan_seni;
		});
	},

	// ===================== Private Methods =====================

	_on_tick: function(seconds) {
		this._emit_waktu('TICK', seconds);
	},

	_update_play_button: function() {
		const btn = $('.btn-toggle-waktu-tampil, .button-play-state');
		if (this.is_playing) {
			btn.html('<i class="fas fa-pause d-none d-md-inline"></i> PAUSE');
			btn.addClass('is-playing');
		} else {
			btn.html('<i class="fas fa-play d-none d-md-inline"></i> START');
			btn.removeClass('is-playing');
		}
	},

	_emit_waktu: function(action, waktu) {
		if (this.socket && this.penampilan_seni) {
			this.socket.emit('KONTROL_WAKTU', {
				id_penampilan_seni: this.penampilan_seni.id_penampilan_seni,
				action: action,
				waktu: waktu
			});
		}
	},

	_connect_socket: function() {
		if (typeof io === 'undefined' || !SOCKET_URL) return;
		try {
			this.socket = io(SOCKET_URL);
			this.socket.on('connect', () => {
				this.socket.emit('JOIN_ROOM', {
					id_penampilan_seni: this.penampilan_seni.id_penampilan_seni
				});
			});
		} catch (e) {
			console.warn('Socket connection failed:', e);
		}
	}
};
