<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';
?>
<div class="layar-wrapper" data-id-pertandingan="<?= $idP ?>" data-status="<?= esc($pertandingan->status_pertandingan, 'attr') ?>">
    <div class="layar-corner corner-biru">
        <div class="layar-kontingen"><?= esc($kontBiru) ?></div>
        <div class="layar-nama penilaian-display-font"><?= esc($namaBiru) ?></div>
        <div class="layar-skor penilaian-display-font" id="skor-biru"><?= (int) $pertandingan->skor_biru ?></div>
        <div class="layar-sudut-label">BIRU</div>
    </div>

    <div class="layar-center">
        <div class="layar-ronde penilaian-display-font">RONDE <span id="ronde-label"><?= esc($ronde) ?></span></div>
        <div class="layar-timer penilaian-display-font" id="timer-display">--:--</div>
        <div class="layar-status" id="status-label"><?= esc(str_replace('_', ' ', $pertandingan->status_pertandingan)) ?></div>
    </div>

    <div class="layar-corner corner-merah">
        <div class="layar-kontingen"><?= esc($kontMerah) ?></div>
        <div class="layar-nama penilaian-display-font"><?= esc($namaMerah) ?></div>
        <div class="layar-skor penilaian-display-font" id="skor-merah"><?= (int) $pertandingan->skor_merah ?></div>
        <div class="layar-sudut-label">MERAH</div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    const root = document.querySelector('.layar-wrapper');
    const idP = root.dataset.idPertandingan;
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    const elSkorMerah = document.getElementById('skor-merah');
    const elSkorBiru  = document.getElementById('skor-biru');
    const elRonde     = document.getElementById('ronde-label');
    const elStatus    = document.getElementById('status-label');
    const elTimer     = document.getElementById('timer-display');

    let durasiMs = 120000, sisaMs = 120000, ticking = false, lastTs = null;

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

    // Sinkronisasi state authoritative dari DB (recovery + live).
    function syncState(d) {
        if (!d) return;
        elSkorMerah.textContent = d.skor_merah;
        elSkorBiru.textContent  = d.skor_biru;
        elRonde.textContent     = d.ronde;
        elStatus.textContent    = (d.status_pertandingan || '').replace(/_/g, ' ');

        // Timer dari data_waktu ronde aktif: [start, durasi_ms, istirahat_ms] + sisa_ms opsional.
        if (d.data_waktu) {
            const dw = d.data_waktu;
            if (dw[d.ronde] && Array.isArray(dw[d.ronde]) && dw[d.ronde][1]) durasiMs = dw[d.ronde][1];
            if (typeof dw.sisa_ms === 'number') sisaMs = dw.sisa_ms;
        }
        // Hidupkan/matikan timer mengikuti status.
        if (d.status_pertandingan === 'berlangsung') {
            if (!ticking) { ticking = true; lastTs = null; requestAnimationFrame(loop); }
        } else {
            ticking = false;
            elTimer.textContent = fmt(sisaMs);
        }
    }

    // --- Real-time push via Socket.IO (Fase 8) ---
    // Polling tetap jalan sebagai fallback recovery (lihat docs §6.2), namun
    // diperlambat karena push event menangani update cepat.
    let rtConnected = false;
    if (window.io) {
        const rtUrl = '<?= env('RT_PUBLIC_URL', 'http://localhost:3000') ?>';
        const socket = io(rtUrl, { reconnection: true, reconnectionDelay: 1000 });

        socket.on('connect', () => {
            rtConnected = true;
            socket.emit('JOIN_ROOM', idP);
        });
        socket.on('disconnect', () => { rtConnected = false; });

        // Skor real-time dari juri/ketua/sekretaris.
        socket.on('UPDATE_SKOR', (d) => {
            if (!d) return;
            if (typeof d.skor_merah !== 'undefined') elSkorMerah.textContent = d.skor_merah;
            if (typeof d.skor_biru !== 'undefined')  elSkorBiru.textContent  = d.skor_biru;
            if (d.ronde) elRonde.textContent = d.ronde;
        });

        // Timer real-time dari sekretaris.
        socket.on('UPDATE_WAKTU', (d) => {
            if (!d) return;
            if (d.data_waktu) {
                const dw = d.data_waktu, r = d.ronde || elRonde.textContent;
                if (dw[r] && Array.isArray(dw[r]) && dw[r][1]) durasiMs = dw[r][1];
                if (typeof dw.sisa_ms === 'number') sisaMs = dw.sisa_ms;
            }
            if (d.status_pertandingan) {
                elStatus.textContent = d.status_pertandingan.replace(/_/g, ' ');
                if (d.status_pertandingan === 'berlangsung') {
                    if (!ticking) { ticking = true; lastTs = null; requestAnimationFrame(loop); }
                } else { ticking = false; elTimer.textContent = fmt(sisaMs); }
            }
        });

        // Partai selesai → reload untuk kembali ke standby / partai berikutnya.
        socket.on('ROOM_RESET', () => window.location.reload());
    }

    function poll() {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('layar/refresh-status-pertandingan') ?>/' + idP, {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.reload === true) { window.location.reload(); }
            else if (d && d.status === false) { syncState(d); }
        })
        .catch(() => {}); // toleran terhadap koneksi putus; lanjut poll berikutnya (recovery)
    }

    // Fallback polling: cepat (2s) bila RT putus, lambat (10s) bila RT aktif.
    setInterval(() => { if (!rtConnected) poll(); }, 2000);
    setInterval(() => { if (rtConnected) poll(); }, 10000); // re-sync periodik dgn DB authoritative
    poll(); // sinkron awal
})();
</script>
<?= $this->endSection() ?>
