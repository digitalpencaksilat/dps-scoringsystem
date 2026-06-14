<?php
/**
 * Layar Seni — Scoreboard penampilan seni (PERSILAT).
 *
 * Visual: parity header tanding (competition_title), no rounded cards,
 *   no bendera/gambar, kontingen font gelap, label juri/ringkasan lebih besar.
 *
 * Real-time: requestAnimationFrame timer + Socket.IO + HTTP polling fallback.
 */
?>
<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar-seni.css') ?>">
<style>
/* ═══ Override: no rounded, presisi, label besar ═══ */
.layar-seni-legacy * { border-radius: 0 !important; }

.layar-seni-legacy {
    background: #000 !important;
    min-height: 100vh;
    padding: 1rem 2rem;
}

/* Header competition_title — parity tanding */
.layar-seni-legacy #competition-title {
    background: #fff !important;
    margin-bottom: 0.75rem;
}
.layar-seni-legacy #competition-title img {
    max-height: 70px;
    object-fit: contain;
}
.layar-seni-legacy #competition-title .event-name {
    font-family: 'Oswald', sans-serif;
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 1px;
}
.layar-seni-legacy #competition-title .info-pill {
    font-size: 1.1rem;
    font-weight: 600;
    padding: 0.4rem 0.75rem;
}

/* Peserta section — no bendera, kontingen dark text */
.layar-seni-legacy #daftar-peserta {
    margin: 0.75rem 0;
}
.layar-seni-legacy #daftar-peserta .nama-peserta {
    font-family: 'Oswald', sans-serif;
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: 1px;
}
.layar-seni-legacy #daftar-peserta .nama-kontingen {
    font-size: 1.3rem;
    font-weight: 600;
    color: #212529 !important;
}

/* Kolom juri — label lebih besar & visible */
.layar-seni-legacy .urutan_total_nilai_juri {
    display: flex;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
}
.layar-seni-legacy .kolom_total_nilai {
    flex: 1;
    min-width: 0;
    padding: 0;
}
.layar-seni-legacy .kolom_total_nilai .label-juri {
    font-size: 1.1rem !important;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 0.5rem 0;
}
.layar-seni-legacy .kolom_total_nilai .nilai-juri {
    font-size: 2rem !important;
    font-weight: 800;
    color: #212529;
    padding: 0.75rem 0;
}
.layar-seni-legacy .kolom_total_nilai.terpilih .nilai-juri {
    background: linear-gradient(180deg, #ffc107, #ff9800) !important;
    color: #fff !important;
}
.layar-seni-legacy .kolom_total_nilai.tidak-terpilih .nilai-juri {
    text-decoration: line-through;
    opacity: 0.5;
}

/* Ringkasan 4 kolom — label lebih besar */
.layar-seni-legacy .ringkasan-row {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.layar-seni-legacy .ringkasan-col {
    flex: 1;
    background: #fff;
    overflow: hidden;
}
.layar-seni-legacy .ringkasan-col .ringkasan-label {
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 1px;
    padding: 0.6rem 0.5rem;
    text-transform: uppercase;
}
.layar-seni-legacy .ringkasan-col .ringkasan-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #212529;
    padding: 0.5rem;
    line-height: 1.1;
}

/* Nilai akhir & waktu — 2 kolom hero */
.layar-seni-legacy .hero-row {
    display: flex;
    gap: 0.5rem;
    margin-top: 0.75rem;
}
.layar-seni-legacy .hero-col {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 180px;
}
.layar-seni-legacy .nilai_akhir {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(4rem, 10vw, 8rem);
    font-weight: 900;
    color: #fff;
    line-height: 1;
}
.layar-seni-legacy .waktu_tampil {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(4rem, 10vw, 8rem);
    font-weight: 900;
    color: #212529;
    line-height: 1;
}

/* Animation */
.layar-seni-legacy .opacity { opacity: 0; }
.layar-seni-legacy .animated { animation-duration: 0.5s; animation-fill-mode: both; }
@keyframes layarFadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes layarFadeInLeft {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}
@keyframes layarFadeInRight {
    from { opacity: 0; transform: translateX(30px); }
    to { opacity: 1; transform: translateX(0); }
}
.layar-seni-legacy .fadeInDown { animation-name: layarFadeInDown; }
.layar-seni-legacy .fadeInLeft { animation-name: layarFadeInLeft; }
.layar-seni-legacy .fadeInRight { animation-name: layarFadeInRight; }

/* Gradient utilities */
.layar-seni-legacy .bg-gradient-180-gray-dark {
    background: linear-gradient(180deg, #2d3436 0%, #27242c 100%) !important;
}
.layar-seni-legacy .bg-gradient-180-red {
    background: linear-gradient(180deg, #e63946 0%, #c62828 100%) !important;
}
.layar-seni-legacy .bg-gradient-180-blue {
    background: linear-gradient(180deg, #1e88e5 0%, #1565c0 100%) !important;
}
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
<div class="container-fluid layar-seni-legacy"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-status-penampilan="<?= esc($statusPenampilan) ?>"
     data-waktu-tampil="<?= $waktuTampil ?>">

    <!-- ═══ HEADER — parity tanding competition_title ═══ -->
    <div class="row bg-white justify-content-around opacity" id="competition-title">
        <div class="col-1 px-0 py-2 d-flex justify-content-center align-items-center">
            <img src="<?= base_url('assets/images/brand/dps/logo-international-federation.png') ?>"
                 alt="Persilat" class="img-fluid" onerror="this.style.display='none'">
        </div>
        <div class="col-8 col-xxl-9">
            <div class="row mb-1">
                <div class="col-12 bg-gradient-180-gray-dark">
                    <p class="event-name text-center m-0 text-white my-2">
                        <?= esc($event_name ?? 'Pencak Silat Championship') ?>
                    </p>
                </div>
            </div>
            <div class="row justify-content-around py-1">
                <div class="col-3">
                    <p class="info-pill m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block text-truncate">
                        <?= esc($partai_seni_berlangsung->nama_gelanggang ?? 'Gelanggang') ?> - <?= esc($partai_seni_berlangsung->nomor_partai ?? '-') ?>
                    </p>
                </div>
                <?php if ($isBattle): ?>
                <div class="col-3">
                    <p class="info-pill m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block text-truncate">
                        <?= strtoupper(esc($partai_seni_berlangsung->babak_battle ?? '-')) ?>
                    </p>
                </div>
                <?php endif; ?>
                <div class="col-3">
                    <p class="info-pill m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block text-truncate">
                        <?= strtoupper(esc($kompetisi_seni->nama_kategori_usia ?? '-')) ?>
                    </p>
                </div>
                <div class="col-3">
                    <p class="info-pill m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block text-truncate">
                        <?= strtoupper(esc($kompetisi_seni->jenis_seni ?? '-')) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-1 px-0 py-2 d-flex justify-content-center align-items-center">
            <img src="<?= base_url('assets/images/brand/dps/logo-federation.png') ?>"
                 alt="Federation" class="img-fluid" onerror="this.style.display='none'">
        </div>
    </div>

    <!-- ═══ PESERTA SENI (no bendera, kontingen dark) ═══ -->
    <?php
        $peserta = $peserta_seni ?? [];
        $namaKontingen = !empty($peserta) ? ($peserta[0]->nama_kontingen ?? '-') : '-';
        $namaPesertaArr = [];
        foreach ($peserta as $p) { $namaPesertaArr[] = $p->nama_pendaftar; }
        $namaPesertaStr = !empty($namaPesertaArr)
            ? implode(' - ', $namaPesertaArr)
            : ($penampilan_seni_berlangsung->nama_kelompok ?? $penampilan_seni_berlangsung->nama_pendaftar ?? 'Peserta');
    ?>
    <div class="row opacity" id="daftar-peserta">
        <div class="col-12">
            <div class="row">
                <div class="col-12 bg-gradient-180-gray-dark py-2">
                    <p class="nama-peserta text-center text-truncate text-uppercase m-0 text-white">
                        <?= esc($namaPesertaStr) ?>
                    </p>
                </div>
            </div>
            <div class="row">
                <div class="col-12 bg-white py-2">
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
    <div class="row mt-2" id="juri-grid">
        <div class="col-12">
            <div class="urutan_total_nilai_juri">
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
                    <div class="kolom_total_nilai opacity <?= $terpilih ? 'terpilih' : 'tidak-terpilih' ?>"
                         data-id-perangkat="<?= (int) $juriId ?>">
                        <div class="bg-gradient-180-gray-dark text-center">
                            <p class="label-juri text-white text-uppercase m-0">Juri <?= $i + 1 ?></p>
                        </div>
                        <div class="bg-white text-center">
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
    <div class="ringkasan-row">
        <div class="ringkasan-col opacity">
            <div class="bg-gradient-180-gray-dark text-center">
                <p class="ringkasan-label text-white m-0">Median Kebenaran</p>
            </div>
            <div class="text-center">
                <p class="ringkasan-value m-0 median_kebenaran"><?= number_format((float)$medianKebenaran, 3) ?></p>
            </div>
        </div>
        <div class="ringkasan-col opacity">
            <div class="bg-gradient-180-gray-dark text-center">
                <p class="ringkasan-label text-white m-0">Standard Deviation</p>
            </div>
            <div class="text-center">
                <p class="ringkasan-value m-0 standar_deviasi"><?= number_format((float)$stdDev, 6) ?></p>
            </div>
        </div>
        <div class="ringkasan-col opacity">
            <div class="bg-gradient-180-gray-dark text-center">
                <p class="ringkasan-label text-white m-0">Median</p>
            </div>
            <div class="text-center">
                <p class="ringkasan-value m-0 median"><?= number_format((float)$median, 3) ?></p>
            </div>
        </div>
        <div class="ringkasan-col opacity">
            <div class="bg-gradient-180-gray-dark text-center">
                <p class="ringkasan-label text-white m-0">Penalty</p>
            </div>
            <div class="text-center">
                <p class="ringkasan-value m-0 hukuman"><?= (float)$hukuman > 0 ? '-' . number_format((float)$hukuman, 1) : '0' ?></p>
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
    <div class="hero-row">
        <div class="hero-col <?= $bgFinal ?> opacity" id="hero-nilai-akhir">
            <p class="nilai_akhir m-0" id="nilai-akhir-display"><?= number_format($nilaiAkhir, 3) ?></p>
        </div>
        <div class="hero-col bg-white opacity" id="hero-waktu">
            <p class="waktu_tampil m-0" id="timer-seni">
                <?= sprintf('%02d:%02d', floor($waktuTampil / 60), $waktuTampil % 60) ?>
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
<script>
(function () {
    'use strict';

    const root = document.querySelector('.layar-seni-legacy');
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
        document.querySelectorAll('.ringkasan-col').forEach((el, i) => {
            setTimeout(() => {
                el.classList.add('animated', 'fadeInDown');
                el.classList.remove('opacity');
            }, ringkasanDelay + i * 150);
        });

        setTimeout(() => {
            document.getElementById('hero-nilai-akhir').classList.add('animated', 'fadeInLeft');
            document.getElementById('hero-nilai-akhir').classList.remove('opacity');
            document.getElementById('hero-waktu').classList.add('animated', 'fadeInRight');
            document.getElementById('hero-waktu').classList.remove('opacity');
        }, ringkasanDelay + 4 * 150);
    }

    // ── Timer (requestAnimationFrame, no jQuery plugin) ──
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
