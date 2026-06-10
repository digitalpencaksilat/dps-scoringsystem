<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $totalRonde = (int) ($pertandingan->jumlah_ronde ?? 3);
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';
    $skorMerah = (int) $pertandingan->skor_merah;
    $skorBiru  = (int) $pertandingan->skor_biru;
    $status    = $pertandingan->status_pertandingan ?? 'belum_mulai';
    $waktuPerRonde = 120000; // default 2 menit
    if ($data_waktu && isset($data_waktu->waktu_per_ronde)) {
        $waktuPerRonde = (int) $data_waktu->waktu_per_ronde * 1000;
    }
?>
<div class="layar-wrapper theme-<?= esc($theme) ?>"
     data-id-pertandingan="<?= $idP ?>"
     data-status="<?= esc($status) ?>"
     data-ronde="<?= esc($ronde) ?>"
     data-total-ronde="<?= $totalRonde ?>"
     data-waktu-per-ronde="<?= $waktuPerRonde ?>">

    <!-- BIRU Corner (left) -->
    <div class="layar-corner corner-biru">
        <div class="layar-kontingen"><?= esc($kontBiru) ?></div>
        <div class="layar-nama penilaian-display-font"><?= esc($namaBiru) ?></div>
        <div class="layar-skor penilaian-display-font" id="skor-biru"><?= $skorBiru ?></div>
        <div class="layar-sudut-label">BIRU</div>
    </div>

    <!-- CENTER: Timer + Ronde + Status -->
    <div class="layar-center">
        <div class="layar-ronde penilaian-display-font">RONDE <span id="ronde-label"><?= esc($ronde) ?></span></div>
        <div class="layar-timer penilaian-display-font" id="timer-display">--:--</div>
        <div class="layar-status" id="status-label"><?= esc(str_replace('_', ' ', $status)) ?></div>
    </div>

    <!-- MERAH Corner (right) -->
    <div class="layar-corner corner-merah">
        <div class="layar-kontingen"><?= esc($kontMerah) ?></div>
        <div class="layar-nama penilaian-display-font"><?= esc($namaMerah) ?></div>
        <div class="layar-skor penilaian-display-font" id="skor-merah"><?= $skorMerah ?></div>
        <div class="layar-sudut-label">MERAH</div>
    </div>
</div>

<!-- Stinger overlay for round change -->
<div class="layar-stinger" id="stinger-overlay" style="display:none;">
    <div class="stinger-content penilaian-display-font" id="stinger-text"></div>
</div>

<!-- Verifikasi overlay -->
<div class="layar-verifikasi-overlay" id="verifikasi-overlay" style="display:none;">
    <div class="verifikasi-content">
        <div class="verifikasi-title penilaian-display-font" id="verifikasi-title">VERIFIKASI</div>
        <div class="verifikasi-detail" id="verifikasi-detail"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    'use strict';

    const root = document.querySelector('.layar-wrapper');
    const idP = root.dataset.idPertandingan;
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    const elSkorMerah = document.getElementById('skor-merah');
    const elSkorBiru  = document.getElementById('skor-biru');
    const elRonde     = document.getElementById('ronde-label');
    const elStatus    = document.getElementById('status-label');
    const elTimer     = document.getElementById('timer-display');
    const elStinger   = document.getElementById('stinger-overlay');
    const elStingerText = document.getElementById('stinger-text');
    const elVerifikasi = document.getElementById('verifikasi-overlay');
    const elVerifikasiTitle = document.getElementById('verifikasi-title');
    const elVerifikasiDetail = document.getElementById('verifikasi-detail');

    let durasiMs = parseInt(root.dataset.waktuPerRonde) || 120000;
    let sisaMs = durasiMs;
    let ticking = false, lastTs = null;
    let currentRonde = root.dataset.ronde;

    // ── Timer ────────────────────────────────────────────────────────
    function fmt(ms) {
        const t = Math.max(0, Math.round(ms / 1000));
        return String(Math.floor(t / 60)).padStart(2, '0') + ':' + String(t % 60).padStart(2, '0');
    }
    function loop(ts) {
        if (!ticking) return;
        if (lastTs !== null) { sisaMs -= (ts - lastTs); if (sisaMs <= 0) { sisaMs = 0; ticking = false; } }
        lastTs = ts; elTimer.textContent = fmt(sisaMs);
        if (ticking) requestAnimationFrame(loop);
    }
    function startTimer() {
        if (!ticking) { ticking = true; lastTs = null; requestAnimationFrame(loop); }
    }
    function stopTimer() {
        ticking = false; elTimer.textContent = fmt(sisaMs);
    }

    // ── Stinger (round change animation) ─────────────────────────────
    function showStinger(text, duration) {
        elStingerText.textContent = text;
        elStinger.style.display = 'flex';
        elStinger.classList.add('stinger-animate');
        setTimeout(() => {
            elStinger.style.display = 'none';
            elStinger.classList.remove('stinger-animate');
        }, duration || 3000);
    }

    // ── Verifikasi overlay ───────────────────────────────────────────
    function showVerifikasi(title, detail) {
        elVerifikasiTitle.textContent = title;
        elVerifikasiDetail.innerHTML = detail || '';
        elVerifikasi.style.display = 'flex';
    }
    function hideVerifikasi() {
        elVerifikasi.style.display = 'none';
    }

    // ── Sync state from DB ───────────────────────────────────────────
    function syncState(d) {
        if (!d) return;
        elSkorMerah.textContent = d.skor_merah;
        elSkorBiru.textContent  = d.skor_biru;
        elStatus.textContent    = (d.status_pertandingan || '').replace(/_/g, ' ').toUpperCase();

        // Ronde change → stinger
        if (d.ronde && d.ronde !== currentRonde) {
            showStinger('RONDE ' + d.ronde, 2500);
            currentRonde = d.ronde;
            elRonde.textContent = d.ronde;
            sisaMs = durasiMs; // reset timer for new round
        }

        // Timer from data_waktu
        if (d.data_waktu) {
            const dw = d.data_waktu;
            if (dw.waktu_per_ronde) durasiMs = parseInt(dw.waktu_per_ronde) * 1000;
            if (typeof dw.sisa_waktu === 'number') sisaMs = dw.sisa_waktu * 1000;
            else if (typeof dw.sisa_ms === 'number') sisaMs = dw.sisa_ms;
        }

        // Start/stop based on status
        if (d.status_pertandingan === 'berlangsung') {
            startTimer();
            hideVerifikasi();
        } else if (d.status_pertandingan === 'verifikasi_jatuhan') {
            stopTimer();
            showVerifikasi('VERIFIKASI JATUHAN', '');
        } else if (d.status_pertandingan === 'verifikasi_pelanggaran') {
            stopTimer();
            showVerifikasi('VERIFIKASI PELANGGARAN', '');
        } else if (d.status_pertandingan === 'istirahat') {
            stopTimer();
            showStinger('ISTIRAHAT', 3000);
        } else {
            stopTimer();
            hideVerifikasi();
        }
    }

    // ── Socket.IO real-time push ─────────────────────────────────────
    let rtConnected = false;
    if (window.io) {
        const rtUrl = '<?= env('RT_PUBLIC_URL', 'http://localhost:3000') ?>';
        const socket = io(rtUrl, { reconnection: true, reconnectionDelay: 1000 });

        socket.on('connect', () => {
            rtConnected = true;
            socket.emit('JOIN_ROOM', idP);
        });
        socket.on('disconnect', () => { rtConnected = false; });

        // Skor real-time
        socket.on('UPDATE_SKOR', (d) => {
            if (!d) return;
            if (typeof d.skor_merah !== 'undefined') elSkorMerah.textContent = d.skor_merah;
            if (typeof d.skor_biru !== 'undefined')  elSkorBiru.textContent  = d.skor_biru;
            if (d.ronde && d.ronde !== currentRonde) {
                showStinger('RONDE ' + d.ronde, 2500);
                currentRonde = d.ronde;
                elRonde.textContent = d.ronde;
                sisaMs = durasiMs; // reset timer for new round
                stopTimer();
            }
        });

        // Timer/waktu real-time — handles 2 payload formats:
        // Format A (from PHP controller via HTTP /emit): { status_pertandingan, data_waktu: {waktu_per_ronde, sisa_waktu} }
        // Format B (from sekretaris_tanding.js via socket): { id_pertandingan, action, waktu, ronde }
        socket.on('UPDATE_WAKTU', (d) => {
            if (!d) return;

            // Format B: direct from sekretaris JS socket emit (action + waktu fields)
            const action = d.action || d.aksi;
            if (action) {
                const waktu = d.waktu;
                if (action === 'TOGGLE' || action === 'start') {
                    if (action === 'TOGGLE' && ticking) {
                        // Toggle: stop if running
                        if (typeof waktu === 'number') sisaMs = waktu * 1000;
                        stopTimer();
                        elStatus.textContent = 'BERHENTI';
                    } else {
                        // Start
                        if (typeof waktu === 'number') sisaMs = waktu * 1000;
                        startTimer(); hideVerifikasi();
                        elStatus.textContent = 'BERLANGSUNG';
                    }
                } else if (action === 'TICK') {
                    // Sync timer value from sekretaris tick
                    if (typeof waktu === 'number') { sisaMs = waktu * 1000; }
                    if (!ticking) startTimer();
                } else if (action === 'stop' || action === 'pause' || action === 'STOP') {
                    if (typeof waktu === 'number') sisaMs = waktu * 1000;
                    stopTimer();
                    elStatus.textContent = 'BERHENTI';
                } else if (action === 'RESET' || action === 'reset') {
                    sisaMs = durasiMs; stopTimer();
                    elStatus.textContent = 'STANDBY';
                } else if (action === 'SET') {
                    if (typeof waktu === 'number') { sisaMs = waktu * 1000; }
                    elTimer.textContent = fmt(sisaMs);
                } else if (action === 'PINDAH_RONDE' || action === 'RONDE_SELESAI') {
                    if (d.ronde && d.ronde !== currentRonde) {
                        showStinger('RONDE ' + d.ronde, 2500);
                        currentRonde = d.ronde;
                        elRonde.textContent = d.ronde;
                    }
                    sisaMs = durasiMs; stopTimer();
                }
                // Update ronde if present
                if (d.ronde && d.ronde !== currentRonde) {
                    currentRonde = d.ronde;
                    elRonde.textContent = d.ronde;
                }
                return;
            }

            // Format A: from PHP controller (data_waktu wrapper)
            if (d.data_waktu) {
                const dw = d.data_waktu;
                if (dw.waktu_per_ronde) durasiMs = parseInt(dw.waktu_per_ronde) * 1000;
                if (typeof dw.sisa_waktu === 'number') sisaMs = dw.sisa_waktu * 1000;
                else if (typeof dw.sisa_ms === 'number') sisaMs = dw.sisa_ms;
                elTimer.textContent = fmt(sisaMs);
            }
            if (d.status_pertandingan) {
                elStatus.textContent = d.status_pertandingan.replace(/_/g, ' ').toUpperCase();
                if (d.status_pertandingan === 'berlangsung') {
                    startTimer(); hideVerifikasi();
                } else if (d.status_pertandingan === 'verifikasi_jatuhan') {
                    stopTimer(); showVerifikasi('VERIFIKASI JATUHAN', '');
                } else if (d.status_pertandingan === 'verifikasi_pelanggaran') {
                    stopTimer(); showVerifikasi('VERIFIKASI PELANGGARAN', '');
                } else {
                    stopTimer(); hideVerifikasi();
                }
            }
        });

        // Match status change (from controller emit)
        socket.on('MATCH_STATUS_CHANGE', (d) => {
            if (!d || !d.status_pertandingan) return;
            elStatus.textContent = d.status_pertandingan.replace(/_/g, ' ').toUpperCase();
            if (d.status_pertandingan === 'berlangsung') { startTimer(); hideVerifikasi(); }
            else if (d.status_pertandingan === 'verifikasi_jatuhan') { stopTimer(); showVerifikasi('VERIFIKASI JATUHAN', ''); }
            else if (d.status_pertandingan === 'verifikasi_pelanggaran') { stopTimer(); showVerifikasi('VERIFIKASI PELANGGARAN', ''); }
            else { stopTimer(); hideVerifikasi(); }
        });

        // Match over → reload standby
        socket.on('ROOM_RESET', () => window.location.reload());
        socket.on('PERTANDINGAN_SELESAI', () => window.location.reload());
    }

    // ── Polling fallback ─────────────────────────────────────────────
    function poll() {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('layar/refresh-status-pertandingan') ?>/' + idP, {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            if (d && d.reload === true) { window.location.reload(); }
            else if (d && d.status === false) { syncState(d); }
        })
        .catch(() => {});
    }

    // Fallback: cepat (2s) bila RT putus, lambat (10s) bila RT aktif
    setInterval(() => { if (!rtConnected) poll(); }, 2000);
    setInterval(() => { if (rtConnected) poll(); }, 10000);
    poll(); // sinkron awal

    // Inisial: set timer display
    elTimer.textContent = fmt(sisaMs);
    if (root.dataset.status === 'berlangsung') startTimer();
})();
</script>
<?= $this->endSection() ?>
