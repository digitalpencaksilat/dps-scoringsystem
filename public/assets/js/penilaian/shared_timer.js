/**
 * DPS Scoring System - Shared Timer Module
 * Provides countdown/countup timer functionality used across sekretaris pages.
 * Parity with legacy CI3 shared_timer.js
 */
const shared_timer = {
	// State
	is_running: false,
	is_countdown: true, // true = countdown (tanding), false = countup (seni)
	time_seconds: 0,
	max_seconds: 120,
	interval_id: null,
	last_tick: null,

	// Audio
	audio_gong: null,
	audio_beep: null,
	beep_enabled: true,
	gong_type: 'gong_1',

	// Callbacks
	on_tick: null,
	on_finish: null,
	on_beep_zone: null,

	/**
	 * Initialize timer
	 * @param {object} options - { countdown, seconds, max_seconds, on_tick, on_finish, on_beep_zone }
	 */
	init: function(options = {}) {
		this.is_countdown = options.countdown !== undefined ? options.countdown : true;
		this.time_seconds = options.seconds || 0;
		this.max_seconds = options.max_seconds || 120;
		this.on_tick = options.on_tick || null;
		this.on_finish = options.on_finish || null;
		this.on_beep_zone = options.on_beep_zone || null;
		this.is_running = false;

		this.render();
	},

	/**
	 * Start the timer
	 */
	start: function() {
		if (this.is_running) return;
		this.is_running = true;
		this.last_tick = performance.now();

		this.interval_id = requestAnimationFrame(this._tick.bind(this));
	},

	/**
	 * Pause the timer
	 */
	pause: function() {
		this.is_running = false;
		if (this.interval_id) {
			cancelAnimationFrame(this.interval_id);
			this.interval_id = null;
		}
	},

	/**
	 * Toggle start/pause
	 */
	toggle: function() {
		if (this.is_running) {
			this.pause();
		} else {
			this.start();
		}
		return this.is_running;
	},

	/**
	 * Reset timer to initial value
	 * @param {number} seconds - reset to this value (countdown) or 0 (countup)
	 */
	reset: function(seconds) {
		this.pause();
		if (this.is_countdown) {
			this.time_seconds = seconds !== undefined ? seconds : this.max_seconds;
		} else {
			this.time_seconds = seconds !== undefined ? seconds : 0;
		}
		this.render();
	},

	/**
	 * Set time manually
	 * @param {number} seconds
	 */
	set_time: function(seconds) {
		this.time_seconds = seconds;
		this.render();
	},

	/**
	 * Internal tick (requestAnimationFrame-based for precision)
	 */
	_tick: function(timestamp) {
		if (!this.is_running) return;

		const elapsed = (timestamp - this.last_tick) / 1000;

		if (elapsed >= 1) {
			this.last_tick = timestamp;

			if (this.is_countdown) {
				this.time_seconds--;
				if (this.time_seconds <= 0) {
					this.time_seconds = 0;
					this.pause();
					this.render();
					this._play_gong();
					if (this.on_finish) this.on_finish();
					return;
				}
			} else {
				this.time_seconds++;
			}

			// Beep zone (last 10 seconds for countdown)
			if (this.is_countdown && this.time_seconds <= 10 && this.time_seconds > 0) {
				this._play_beep();
				if (this.on_beep_zone) this.on_beep_zone(this.time_seconds);
			}

			this.render();
			if (this.on_tick) this.on_tick(this.time_seconds);
		}

		this.interval_id = requestAnimationFrame(this._tick.bind(this));
	},

	/**
	 * Render timer display
	 */
	render: function() {
		const display = this.format_time(this.time_seconds);
		$('.timer-tanding, .timer-seni').text(display);
	},

	/**
	 * Format seconds to MM:SS
	 * @param {number} totalSeconds
	 * @returns {string}
	 */
	format_time: function(totalSeconds) {
		const absSeconds = Math.abs(totalSeconds);
		const minutes = Math.floor(absSeconds / 60);
		const seconds = absSeconds % 60;
		return String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
	},

	/**
	 * Play gong sound
	 */
	_play_gong: function() {
		try {
			const soundPath = `${window.location.origin}/assets/sound/${this.gong_type}.mp3`;
			if (!this.audio_gong) {
				this.audio_gong = new Audio(soundPath);
			} else {
				this.audio_gong.src = soundPath;
			}
			this.audio_gong.play().catch(() => {});
		} catch (e) {}
	},

	/**
	 * Play beep sound
	 */
	_play_beep: function() {
		if (!this.beep_enabled) return;
		try {
			const soundPath = `${window.location.origin}/assets/sound/beep.mp3`;
			if (!this.audio_beep) {
				this.audio_beep = new Audio(soundPath);
			}
			this.audio_beep.currentTime = 0;
			this.audio_beep.play().catch(() => {});
		} catch (e) {}
	},

	/**
	 * Set sound settings
	 */
	set_sound: function(gong_type, beep_enabled) {
		this.gong_type = gong_type || 'gong_1';
		this.beep_enabled = beep_enabled !== false;
	},

	/**
	 * Apply server-authoritative timer state with drift compensation.
	 *
	 * Server sends:
	 *   - state: 'running' | 'paused'
	 *   - sisa_waktu_at_save: sisa detik saat server save (snapshot)
	 *   - started_at_ms: Unix epoch ms saat START ditekan (null jika paused)
	 *   - server_now_ms: Unix epoch ms server "sekarang" saat response
	 *
	 * Client menghitung:
	 *   if (state === 'running') {
	 *       elapsed_ms = (server_now_ms - started_at_ms) + (client_now - response_arrival_ms);
	 *       sisa = sisa_waktu_at_save - elapsed_ms / 1000;
	 *   } else {
	 *       sisa = sisa_waktu_at_save;
	 *   }
	 *
	 * Hasilnya: smooth timer antar polling, sinkron dengan server state.
	 *
	 * @param {object} serverState data_waktu dari server (decoded JSON)
	 * @param {number} responseReceivedAtMs client timestamp saat response datang (optional, default: now)
	 */
	apply_server_state: function(serverState, responseReceivedAtMs) {
		if (!serverState || typeof serverState !== 'object') {
			return; // Invalid state, ignore
		}

		const state = serverState.state || 'paused';
		const sisaAtSave = Math.max(0, parseInt(serverState.sisa_waktu_at_save) || 0);
		const startedAtMs = parseInt(serverState.started_at_ms) || 0;
		const serverNowMs = parseInt(serverState.server_now_ms) || 0;
		const responseTime = responseReceivedAtMs || performance.now();
		const clientNowMs = Date.now();

		if (state === 'running' && startedAtMs > 0 && serverNowMs > 0) {
			// Timer is running server-side
			// Hitung elapsed time sejak server mulai
			const serverElapsedMs = serverNowMs - startedAtMs;
			// Tambah network jitter: delay dari response diterima hingga sekarang
			const clientJitterMs = clientNowMs - (responseTime / 1000) * 1000;
			const totalElapsedMs = serverElapsedMs + clientJitterMs;
			const totalElapsedSeconds = Math.floor(totalElapsedMs / 1000);

			const sisaSekarang = Math.max(0, sisaAtSave - totalElapsedSeconds);
			this.time_seconds = sisaSekarang;

			// Resume timer if not already running
			if (!this.is_running) {
				this.start();
			}
		} else {
			// Timer is paused or idle server-side
			this.time_seconds = sisaAtSave;
			if (this.is_running) {
				this.pause();
			}
		}

		this.render();
		if (this.on_tick) {
			this.on_tick(this.time_seconds);
		}
	}
};
