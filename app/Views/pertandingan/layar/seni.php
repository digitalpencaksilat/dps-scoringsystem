<?php
/**
 * Layar Seni — Scoreboard penampilan seni (PERSILAT).
 *
 * Visual: parity layout & header dari tanding (edge-to-edge, no margins).
 * Real-time: requestAnimationFrame timer + Socket.IO + HTTP polling fallback.
 */
?>
<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
    /* Gradient utilities — parity tanding */
    .bg-gradient-180-black     { background: linear-gradient(180deg, #111 0%, #000 100%); }
    .bg-gradient-180-gray-dark { background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%); }
    .bg-gradient-180-blue      { background: linear-gradient(180deg, #1565c0 0%, #0d47a1 100%); }
    .bg-gradient-180-red       { background: linear-gradient(180deg, #c62828 0%, #b71c1c 100%); }
    .bg-gradient-180-white     { background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%); }

    /* Override: tidak ada border-radius di seluruh layar seni */
    .layar-seni-wrapper * { border-radius: 0 !important; }

    /* Animation hooks */
    .layar-seni-wrapper .opacity { opacity: 0; }
    .layar-seni-wrapper .animated { animation-duration: 0.5s; animation-fill-mode: both; }
    @keyframes seniFadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes seniFadeInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
    @keyframes seniFadeInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
    .layar-seni-wrapper .fadeInDown  { animation-name: seniFadeInDown; }
    .layar-seni-wrapper .fadeInLeft  { animation-name: seniFadeInLeft; }
    .layar-seni-wrapper .fadeInRight { animation-name: seniFadeInRight; }

    /* Peserta — kontingen text gelap, no bendera */
    .layar-seni-wrapper .nama-peserta {
        font-family: 'Oswald', sans-serif;
        font-size: clamp(1.5rem, 3vw, 2.4rem);
        font-weight: 700;
        letter-spacing: 1px;
    }
    .layar-seni-wrapper .nama-kontingen {
        font-size: clamp(1rem, 2vw, 1.5rem);
        font-weight: 600;
        color: #212529 !important;
    }

    /* Kolom juri — label & nilai besar */
    .layar-seni-wrapper .label-juri {
        font-size: clamp(0.95rem, 1.4vw, 1.25rem) !important;
        font-weight: 700;
        letter-spacing: 1px;
    }
    .layar-seni-wrapper .nilai-juri {
        font-size: clamp(1.6rem, 3vw, 2.4rem) !important;
        font-weight: 800;
        color: #212529;
    }
    .layar-seni-wrapper .kolom_total_nilai.terpilih .nilai-juri {
        background: linear-gradient(180deg, #ffc107, #ff9800) !important;
        color: #fff !important;
    }
    .layar-seni-wrapper .kolom_total_nilai.tidak-terpilih .nilai-juri {
        text-decoration: line-through;
        opacity: 0.5;
    }

    /* Ringkasan — label besar */
    .layar-seni-wrapper .ringkasan-label {
        font-size: clamp(0.95rem, 1.3vw, 1.2rem) !important;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .layar-seni-wrapper .ringkasan-value {
        font-size: clamp(2rem, 4vw, 3.2rem) !important;
        font-weight: 800;
        color: #212529;
        line-height: 1.1;
    }

    /* Hero: nilai akhir & waktu */
    .layar-seni-wrapper .nilai_akhir,
    .layar-seni-wrapper .waktu_tampil {
        font-family: 'Oswald', sans-serif;
        font-size: clamp(4rem, 12vw, 9rem);
        font-weight: 900;
        line-height: 1;
        margin: 0;
    }
    .layar-seni-wrapper .nilai_akhir { color: #fff; }
    .layar-seni-wrapper .waktu_tampil { color: #212529; }

    /* Header competition_title — parity tanding (no extra overrides, match tanding exactly) */
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idPenampilan     = (int) $penampilan_seni_berlangsung->id_penampilan_seni;
    $statusPenampilan = $penampilan_seni_berlangsung->status_penampilan ?? 'standby';
    $waktuTampil      = (int) ($penampilan_seni_berlangsung->waktu_tampil ?? 0);
    $sistemPenampilan = $kompetisi_seni->sistem_penampilan ?? 'pool';
    $isBattle         = $sistemPenampilan === 'battle';
    $idBiru           = $partai_seni_berlangsung->id_penampilan_seni_biru ?? null;
    $isSudutBiru      = $isBattle && $idBiru && (int)$idBiru === $idPenampilan;
?>
<div class="container-fluid min-vh-100 bg-gradient-180-black overflow-hidden layar-seni-wrapper"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-status-penampilan="<?= esc($statusPenampilan) ?>"
     data-waktu-tampil="<?= $waktuTampil ?>">

    <!-- HEADER COMPETITION TITLE - shared component (parity tanding) -->
    <?php
        $_il = strtoupper(esc(($partai_seni_berlangsung->nama_gelanggang ?? 'GELANGGANG') . ' - ' . ($partai_seni_berlangsung->nomor_partai ?? '-')));
        $_kat = strtoupper(esc($kompetisi_seni->nama_kategori_usia ?? '-'));
        $_jen = strtoupper(esc($kompetisi_seni->jenis_seni ?? '-'));
        if ($isBattle) {
            $_ic = strtoupper(esc($partai_seni_berlangsung->babak_battle ?? '-'));
            $_ir = $_kat . ' / ' . $_jen;
        } else {
            $_ic = $_kat;
            $_ir = $_jen;
        }
    ?>
    <?= $this->include('pertandingan/layar/components/competition_title', [
        'event_name'  => $event_name ?? 'Pencak Silat Championship',
        'info_left'   => $_il,
        'info_center' => $_ic,
        'info_right'  => $_ir,
    ]) ?>

    <!-- ═══ PESERTA — no bendera ═══ -->
    <?php
        $peserta = $peserta_seni ?? [];
        $namaKontingen = !empty($peserta) ? ($peserta[0]->nama_kontingen ?? '-') : '-';
        $namaPesertaArr = [];
        foreach ($peserta as $p) { $namaPesertaArr[] = $p->nama_pendaftar; }
        $namaPesertaStr = !empty($namaPesertaArr)
            ? implode(' - ', $namaPesertaArr)
            : ($penampilan_seni_berlangsung->nama_kelompok ?? $penampilan_seni_berlangsung->nama_pendaftar ?? 'Peserta');
    ?>
    <div class="row mb-1 opacity" id="daftar-peserta">
        <div class="col-12 px-0">
            <div class="row mx-0">
                <div class="col-12 bg-gradient-180-gray-dark py-2 px-3">
                    <p class="nama-peserta text-center text-truncate text-uppercase m-0 text-white">
                        <?= esc($namaPesertaStr) ?>
                    </p>
                </div>
            </div>
            <div class="row mx-0">
                <div class="col-12 bg-gradient-180-white py-2 px-3">
                    <p class="nama-kontingen text-center text-truncate text-uppercase m-0">
                        <?= esc($namaKontingen) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ KOLOM NILAI PER JURI ═══ -->
    <?php
        $jumlahJuri = !empty($data_nilai[$idPenampilan])
            ? count($data_nilai[$idPenampilan])
            : 5;
    ?>
    <div class="row mb-1" id="juri-grid">
        <div class="col-12 px-0">
            <div class="row mx-0 urutan_total_nilai_juri">
                <?php for ($i = 0; $i < $jumlahJuri; $i++):
                    $juriRow = $data_nilai[$idPenampilan][$i] ?? null;
                    $juriId  = $juriRow->id_perangkat_pertandingan ?? 0;
                    $terpilih = !empty($juriRow->terpilih);
                    $nilaiJuri = 0;
                    if ($juriRow && !empty($juriRow->penilaian)) {
                        $parsed = is_string($juriRow->penilaian) ? json_decode($juriRow->penilaian) : $juriRow->penilaian;
                        if ($parsed && isset($parsed->penilaian->ringkasan->total_nilai)) {
                            $nilaiJuri = (float) $parsed->penilaian->ringkasan->total_nilai;
                        }
                    }
                ?>
                    <div class="col px-1 kolom_total_nilai opacity <?= $terpilih ? 'terpilih' : 'tidak-terpilih' ?>"
                         data-id-perangkat="<?= (int) $juriId ?>">
                        <div class="bg-gradient-180-gray-dark text-center py-2">
                            <p class="label-juri text-white text-uppercase m-0">Juri <?= $i + 1 ?></p>
                        </div>
                        <div class="bg-gradient-180-white text-center py-3">
                            <p class="nilai-juri m-0"><?= $nilaiJuri > 0 ? number_format($nilaiJuri, 3) : '-' ?></p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- ═══ RINGKASAN NILAI (4 kolom) ═══ -->
    <?php
        $catatan = $penampilan_seni_berlangsung->catatan_nilai_sama ?? null;
        $cat = null;
        if ($catatan) { $cat = is_string($catatan) ? json_decode($catatan) : $catatan; }
        $medianKebenaran = $cat->median_kebenaran ?? 0;
        $stdDev          = $cat->standar_deviasi ?? 0;
        $median          = $cat->median ?? 0;
        $hukuman         = $cat->hukuman ?? 0;
    ?>
    <div class="row mb-1">
        <div class="col-12 px-0">
            <div class="row mx-0">
                <div class="col-3 px-1 opacity">
                    <div class="bg-gradient-180-gray-dark text-center py-2">
                        <p class="ringkasan-label text-white m-0">Median Kebenaran</p>
                    </div>
                    <div class="bg-gradient-180-white text-center py-3">
                        <p class="ringkasan-value m-0 median_kebenaran"><?= number_format((float)$medianKebenaran, 3) ?></p>
                    </div>
                </div>
                <div class="col-3 px-1 opacity">
                    <div class="bg-gradient-180-gray-dark text-center py-2">
                        <p class="ringkasan-label text-white m-0">Standard Deviation</p>
                    </div>
                    <div class="bg-gradient-180-white text-center py-3">
                        <p class="ringkasan-value m-0 standar_deviasi"><?= number_format((float)$stdDev, 6) ?></p>
                    </div>
                </div>
                <div class="col-3 px-1 opacity">
                    <div class="bg-gradient-180-gray-dark text-center py-2">
                        <p class="ringkasan-label text-white m-0">Median</p>
                    </div>
                    <div class="bg-gradient-180-white text-center py-3">
                        <p class="ringkasan-value m-0 median"><?= number_format((float)$median, 3) ?></p>
                    </div>
                </div>
                <div class="col-3 px-1 opacity">
                    <div class="bg-gradient-180-gray-dark text-center py-2">
                        <p class="ringkasan-label text-white m-0">Penalty</p>
                    </div>
                    <div class="bg-gradient-180-white text-center py-3">
                        <p class="ringkasan-value m-0 hukuman"><?= (float)$hukuman > 0 ? '-' . number_format((float)$hukuman, 1) : '0' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ NILAI AKHIR & WAKTU TAMPIL ═══ -->
    <?php
        $nilaiAkhir = (float) ($penampilan_seni_berlangsung->nilai_akhir ?? 0);
        $bgFinal = 'bg-gradient-180-gray-dark';
        if ($isBattle) {
            $bgFinal = $isSudutBiru ? 'bg-gradient-180-blue' : 'bg-gradient-180-red';
        }
    ?>
    <div class="row mb-1">
        <div class="col-12 px-0">
            <div class="row mx-0">
                <div class="col-6 px-1 opacity" id="hero-nilai-akhir">
                    <div class="<?= $bgFinal ?> d-flex align-items-center justify-content-center" style="min-height: 200px;">
                        <p class="nilai_akhir text-center" id="nilai-akhir-display"><?= number_format($nilaiAkhir, 3) ?></p>
                    </div>
                </div>
                <div class="col-6 px-1 opacity" id="hero-waktu">
                    <div class="bg-gradient-180-white d-flex align-items-center justify-content-center" style="min-height: 200px;">
                        <p class="waktu_tampil text-center" id="timer-seni">
                            <?= sprintf('%02d:%02d', floor($waktuTampil / 60), $waktuTampil % 60) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    'use strict';

    const root = document.querySelector('.layar-seni-wrapper');
    const idPenampilan = root.dataset.idPenampilan;
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    const elTimer = document.getElementById('timer-seni');
    const elNilaiAkhir = document.getElementById('nilai-akhir-display');

    // ── Animation (stagger fadeIn) ──
    function startAnimation() {
        const seq = [
            ['#competition-title', 'fadeInDown', 0],
            ['#daftar-peserta', 'fadeInDown', 400],
        ];
        seq.forEach(([sel, cls, delay]) => {
            setTimeout(() => {
                document.querySelectorAll(sel).forEach(el => {
                    el.classList.add('animated', cls);
                    el.classList.remove('opacity');
                });
            }, delay);
        });

        const juriCols = document.querySelectorAll('.kolom_total_nilai');
        juriCols.forEach((el, i) => {
            setTimeout(() => {
                el.classList.add('animated', 'fadeInDown');
                el.classList.remove('opacity');
            }, 800 + i * 150);
        });

        const ringkasanDelay = 800 + juriCols.length * 150;
        document.querySelectorAll('.row .col-3.opacity').forEach((el, i) => {
            setTimeout(() => {
                el.classList.add('animated', 'fadeInDown');
                el.classList.remove('opacity');
            }, ringkasanDelay + i * 150);
        });

        setTimeout(() => {
            const heroL = document.getElementById('hero-nilai-akhir');
            const heroR = document.getElementById('hero-waktu');
            if (heroL) { heroL.classList.add('animated', 'fadeInLeft'); heroL.classList.remove('opacity'); }
            if (heroR) { heroR.classList.add('animated', 'fadeInRight'); heroR.classList.remove('opacity'); }
        }, ringkasanDelay + 4 * 150);
    }

    // ── Timer (requestAnimationFrame) ──
    let timerRunning = false, lastTs = null;
    let elapsedMs = (parseInt(root.dataset.waktuTampil, 10) || 0) * 1000;

    function fmtTime(ms) {
        const s = Math.floor(Math.max(0, ms) / 1000);
        return String(Math.floor(s / 60)).padStart(2, '0') + ':' + String(s % 60).padStart(2, '0');
    }
    function timerLoop(ts) {
        if (!timerRunning) return;
        if (lastTs !== null) elapsedMs += (ts - lastTs);
        lastTs = ts;
        elTimer.textContent = fmtTime(elapsedMs);
        requestAnimationFrame(timerLoop);
    }
    function startTimer() {
        if (!timerRunning) { timerRunning = true; lastTs = null; requestAnimationFrame(timerLoop); }
    }
    function stopTimer() {
        timerRunning = false; lastTs = null;
        elTimer.textContent = fmtTime(elapsedMs);
    }
    function setTimerSeconds(sec) {
        elapsedMs = (typeof sec === 'number' ? sec : 0) * 1000;
        elTimer.textContent = fmtTime(elapsedMs);
    }

    // ── Update juri columns (sort ascending + terpilih) ──
    function updateJuriColumns(dataNilai) {
        if (!dataNilai || !Array.isArray(dataNilai)) return;
        const juriScores = dataNilai.map((j, idx) => {
            let parsed = j.penilaian;
            if (typeof parsed === 'string') { try { parsed = JSON.parse(parsed); } catch (e) { parsed = null; } }
            const total = parsed && parsed.penilaian && parsed.penilaian.ringkasan
                ? parseFloat(parsed.penilaian.ringkasan.total_nilai) || 0 : 0;
            return { index: idx, total: total, terpilih: j.terpilih == 1 };
        });

        const sorted = juriScores.slice().sort((a, b) => a.total - b.total);
        const cols = document.querySelectorAll('.urutan_total_nilai_juri .kolom_total_nilai');

        sorted.forEach((juri, displayIdx) => {
            const col = cols[displayIdx];
            if (!col) return;
            const nilaiEl = col.querySelector('.nilai-juri');
            const labelEl = col.querySelector('.label-juri');
            if (nilaiEl) nilaiEl.textContent = juri.total > 0 ? juri.total.toFixed(3) : '-';
            if (labelEl) labelEl.textContent = 'Juri ' + (juri.index + 1);
            col.classList.remove('terpilih', 'tidak-terpilih');
            col.classList.add(juri.terpilih ? 'terpilih' : 'tidak-terpilih');
        });
    }

    function updateRingkasan(catatan) {
        if (!catatan) return;
        let c = catatan;
        if (typeof c === 'string') { try { c = JSON.parse(c); } catch (e) { return; } }
        if (c.median_kebenaran !== undefined) document.querySelector('.median_kebenaran').textContent = parseFloat(c.median_kebenaran).toFixed(3);
        if (c.standar_deviasi !== undefined) document.querySelector('.standar_deviasi').textContent = parseFloat(c.standar_deviasi).toFixed(6);
        if (c.median !== undefined) document.querySelector('.median').textContent = parseFloat(c.median).toFixed(3);
        if (c.hukuman !== undefined) {
            const h = parseFloat(c.hukuman);
            document.querySelector('.hukuman').textContent = h > 0 ? '-' + h.toFixed(1) : '0';
        }
    }

    // ── State sync ──
    function syncState(data) {
        if (!data) return;
        if (data.penampilan_seni_berlangsung) {
            const p = data.penampilan_seni_berlangsung;
            if (p.nilai_akhir !== undefined) elNilaiAkhir.textContent = parseFloat(p.nilai_akhir).toFixed(3);
            if (p.catatan_nilai_sama) updateRingkasan(p.catatan_nilai_sama);
            if (typeof p.waktu_tampil !== 'undefined') {
                const sec = parseInt(p.waktu_tampil, 10) || 0;
                if (p.status_penampilan === 'sedang_tampil') {
                    if (!timerRunning) { setTimerSeconds(sec); startTimer(); }
                } else {
                    setTimerSeconds(sec); stopTimer();
                }
            }
        }
        if (data.data_nilai && data.data_nilai[idPenampilan]) {
            updateJuriColumns(data.data_nilai[idPenampilan]);
        }
    }

    // ── Socket.IO ──
    let rtConnected = false;
    if (window.io) {
        const rtUrl = '<?= env('RT_PUBLIC_URL', 'http://localhost:3000') ?>';
        const socket = io(rtUrl, { reconnection: true, reconnectionDelay: 1000 });

        socket.on('connect', () => {
            rtConnected = true;
            socket.emit('JOIN_ROOM', { id_penampilan_seni: idPenampilan });
        });
        socket.on('disconnect', () => { rtConnected = false; });

        socket.on('KONTROL_WAKTU_SENI', (d) => {
            if (!d || String(d.id_penampilan_seni) !== String(idPenampilan)) return;
            const sec = parseInt(d.waktu_tampil, 10) || 0;
            if (d.status_penampilan === 'sedang_tampil') { setTimerSeconds(sec); startTimer(); }
            else { setTimerSeconds(sec); stopTimer(); }
        });

        socket.on('UPDATE_NILAI_SENI', (d) => {
            if (!d || String(d.id_penampilan_seni) !== String(idPenampilan)) return;
            poll();
        });
        socket.on('HUKUMAN_UPDATE', (d) => {
            if (!d || String(d.id_penampilan_seni) !== String(idPenampilan)) return;
            poll();
        });
        socket.on('AKSES_PENILAIAN', (d) => {
            if (!d || String(d.id_penampilan_seni) !== String(idPenampilan)) return;
            poll();
        });
        socket.on('SENI_AKSES_DITUTUP', (d) => {
            if (!d || String(d.id_penampilan_seni) !== String(idPenampilan)) return;
            stopTimer(); poll();
        });
        socket.on('PENAMPILAN_SELESAI', (d) => {
            if (!d || String(d.id_penampilan_seni) !== String(idPenampilan)) return;
            setTimeout(() => window.location.reload(), 500);
        });
        socket.on('SENI_SELESAI', () => { window.location = '<?= base_url('layar/standby') ?>'; });
        socket.on('ROOM_RESET', () => { window.location = '<?= base_url('layar/standby') ?>'; });
    }

    // ── HTTP polling ──
    function poll() {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('layar/refresh-status-seni') ?>/' + idPenampilan, {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            if (d && d.status === true) {
                if (d.reload === true) window.location.reload();
                else if (d.hasil_pool_seni === true) window.location.href = '<?= base_url('layar/hasil-pool-seni') ?>/' + d.id_kompetisi_seni;
                else if (d.hasil_battle_seni === true) window.location.href = '<?= base_url('layar/hasil-battle-seni') ?>/' + d.id_battle_seni;
                return;
            }
            if (d && d.status === false) syncState(d);
        })
        .catch(() => {});
    }

    setInterval(() => { if (!rtConnected) poll(); }, 2000);
    setInterval(() => { if (rtConnected) poll(); }, 10000);

    // ── Boot ──
    document.addEventListener('DOMContentLoaded', () => {
        startAnimation();
        if (root.dataset.statusPenampilan === 'sedang_tampil') startTimer();
        poll();
    });
})();
</script>
<?= $this->endSection() ?>
