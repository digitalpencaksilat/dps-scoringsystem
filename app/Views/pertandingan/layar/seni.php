<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idPenampilan   = (int) $penampilan->id_penampilan_seni;
    $namaPeserta    = $penampilan->nama_kelompok ?? $penampilan->nama_pendaftar ?? 'Peserta';
    $namaKontingen  = $penampilan->nama_kontingen ?? '-';
    $nomorTampil    = $penampilan->nomor_urut ?? '-';
    $statusAkses    = $penampilan->akses_penilaian ?? 'dibuka';
    $nilaiAkhir     = (float) ($penampilan->nilai_akhir ?? 0);
?>
<div class="layar-seni-wrapper theme-<?= esc($theme) ?>"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-status-akses="<?= esc($statusAkses) ?>">

    <!-- Header: Competition Info -->
    <div class="layar-seni-header">
        <div class="layar-seni-nomor">
            <span class="label-nomor">No. Tampil</span>
            <span class="nilai-nomor penilaian-display-font"><?= esc($nomorTampil) ?></span>
        </div>
        <div class="layar-seni-peserta">
            <div class="layar-seni-nama penilaian-display-font"><?= esc($namaPeserta) ?></div>
            <div class="layar-seni-kontingen"><?= esc($namaKontingen) ?></div>
        </div>
        <div class="layar-seni-timer-container">
            <div class="layar-seni-timer penilaian-display-font" id="timer-seni">00:00</div>
            <span class="layar-seni-timer-label">Waktu Tampil</span>
        </div>
    </div>

    <!-- Juri Score Grid -->
    <div class="layar-seni-juri-grid" id="juri-grid">
        <?php if (!empty($data_nilai_juri)): ?>
            <?php foreach ($data_nilai_juri as $idx => $juri): ?>
                <div class="layar-seni-juri-card <?= !empty($juri->terpilih) ? 'terpilih' : '' ?>"
                     data-id-perangkat="<?= (int) $juri->id_perangkat_pertandingan ?>">
                    <div class="juri-card-header">Juri <?= $idx + 1 ?></div>
                    <div class="juri-card-nilai penilaian-display-font">
                        <?= $statusAkses === 'ditutup' ? number_format((float)($juri->nilai_akhir_per_juri ?? 0), 2) : '--.--' ?>
                    </div>
                    <div class="juri-card-status">
                        <?php if (!empty($juri->status_ready)): ?>
                            <i class="fa-solid fa-circle-check text-success"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-clock text-muted"></i>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <div class="layar-seni-juri-card" data-id-perangkat="0">
                    <div class="juri-card-header">Juri <?= $i ?></div>
                    <div class="juri-card-nilai penilaian-display-font">--.--</div>
                    <div class="juri-card-status"><i class="fa-solid fa-clock text-muted"></i></div>
                </div>
            <?php endfor; ?>
        <?php endif; ?>
    </div>

    <!-- Nilai Akhir -->
    <div class="layar-seni-footer">
        <div class="layar-seni-nilai-akhir">
            <span class="label-akhir">NILAI AKHIR</span>
            <span class="nilai-akhir penilaian-display-font" id="nilai-akhir-display">
                <?= $statusAkses === 'ditutup' && $nilaiAkhir > 0 ? number_format($nilaiAkhir, 3) : '--.---' ?>
            </span>
        </div>
        <div class="layar-seni-status" id="status-seni-label">
            <?php if ($statusAkses === 'dibuka'): ?>
                <span class="badge bg-success pulse-badge">Penilaian Berlangsung</span>
            <?php elseif ($statusAkses === 'ditutup'): ?>
                <span class="badge bg-danger">Penilaian Selesai</span>
            <?php else: ?>
                <span class="badge bg-secondary">Menunggu</span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    'use strict';

    const root = document.querySelector('.layar-seni-wrapper');
    const idPenampilan = root.dataset.idPenampilan;
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    const elNilaiAkhir = document.getElementById('nilai-akhir-display');
    const elStatusLabel = document.getElementById('status-seni-label');
    const elTimer = document.getElementById('timer-seni');
    const elJuriGrid = document.getElementById('juri-grid');

    // Count-up timer (seni uses elapsed time, not countdown)
    let startTime = null, timerRunning = false, elapsedMs = 0;

    function fmtUp(ms) {
        const s = Math.floor(ms / 1000);
        return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    }

    function timerLoop(ts) {
        if (!timerRunning) return;
        if (startTime === null) startTime = ts;
        elapsedMs = ts - startTime;
        elTimer.textContent = fmtUp(elapsedMs);
        requestAnimationFrame(timerLoop);
    }

    function startTimer() {
        if (!timerRunning) { timerRunning = true; startTime = null; requestAnimationFrame(timerLoop); }
    }
    function stopTimer() { timerRunning = false; }

    // Update juri scores
    function updateJuriGrid(juriData) {
        if (!juriData || !juriData.length) return;
        const cards = elJuriGrid.querySelectorAll('.layar-seni-juri-card');
        juriData.forEach((juri, idx) => {
            const card = cards[idx];
            if (!card) return;
            const nilaiEl = card.querySelector('.juri-card-nilai');
            const statusEl = card.querySelector('.juri-card-status');
            if (juri.terpilih) {
                card.classList.add('terpilih');
            } else {
                card.classList.remove('terpilih');
            }
            if (juri.ready) {
                nilaiEl.textContent = parseFloat(juri.nilai_akhir).toFixed(2);
                statusEl.innerHTML = '<i class="fa-solid fa-circle-check text-success"></i>';
            }
        });
    }

    // Sync state from poll
    function syncState(d) {
        if (!d) return;
        if (d.juri_data) updateJuriGrid(d.juri_data);
        if (d.nilai_akhir && d.nilai_akhir > 0) {
            elNilaiAkhir.textContent = parseFloat(d.nilai_akhir).toFixed(3);
        }
        if (d.akses_penilaian === 'dibuka') {
            elStatusLabel.innerHTML = '<span class="badge bg-success pulse-badge">Penilaian Berlangsung</span>';
            startTimer();
        } else if (d.akses_penilaian === 'ditutup') {
            elStatusLabel.innerHTML = '<span class="badge bg-danger">Penilaian Selesai</span>';
            stopTimer();
        }
    }

    // Socket.IO real-time
    let rtConnected = false;
    if (window.io) {
        const rtUrl = '<?= env('RT_PUBLIC_URL', 'http://localhost:3000') ?>';
        const socket = io(rtUrl, { reconnection: true, reconnectionDelay: 1000 });

        socket.on('connect', () => {
            rtConnected = true;
            socket.emit('JOIN_ROOM', idPenampilan);
        });
        socket.on('disconnect', () => { rtConnected = false; });

        socket.on('UPDATE_NILAI_SENI', (d) => {
            if (!d) return;
            if (d.juri_data) updateJuriGrid(d.juri_data);
            if (d.nilai_akhir && d.nilai_akhir > 0) {
                elNilaiAkhir.textContent = parseFloat(d.nilai_akhir).toFixed(3);
            }
        });

        socket.on('SENI_AKSES_DITUTUP', () => {
            elStatusLabel.innerHTML = '<span class="badge bg-danger">Penilaian Selesai</span>';
            stopTimer();
            // Refresh to get final scores
            poll();
        });

        socket.on('SENI_SELESAI', () => { window.location.reload(); });
        socket.on('ROOM_RESET', () => { window.location.reload(); });
    }

    // Polling fallback
    function poll() {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('layar/refresh-status-seni') ?>/' + idPenampilan, {
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

    setInterval(() => { if (!rtConnected) poll(); }, 2000);
    setInterval(() => { if (rtConnected) poll(); }, 10000);
    poll();

    // Auto start timer if akses is open
    if (root.dataset.statusAkses === 'dibuka') startTimer();
})();
</script>
<?= $this->endSection() ?>
