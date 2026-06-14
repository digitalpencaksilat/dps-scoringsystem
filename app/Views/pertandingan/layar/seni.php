<?php
/**
 * Layar Seni — Scoreboard untuk penampilan seni (PERSILAT).
 *
 * Visual: parity legacy CI3 dark mode (header logo + nama event,
 *   peserta + bendera, kolom nilai per juri, ringkasan median/std/penalty,
 *   nilai akhir & waktu tampil dua kolom besar).
 *
 * Real-time: requestAnimationFrame timer + Socket.IO + HTTP polling fallback
 *   (pattern proven yang sudah jalan di project).
 */
?>
<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    // Variabel dari controller Layar::seni
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

    <!-- ═══ HEADER KOMPETISI ═══ -->
    <div class="row bg-white bg-gradient-180-white mb-3 justify-content-around opacity" id="competition-title">
        <div class="col-1 px-0 py-2 d-flex justify-content-center align-items-center">
            <img src="<?= base_url('assets/images/brand/dps/logo-international-federation.png') ?>"
                 alt="Persilat"
                 class="img-fluid"
                 onerror="this.style.display='none'">
        </div>
        <div class="col-8 col-xxl-9">
            <div class="row">
                <div class="col-12 bg-gradient-180-gray-dark rounded-top rounded-3">
                    <p class="h2 text-center m-0 text-white my-2">
                        <?= esc($event_name ?? 'Pencak Silat Championship') ?>
                    </p>
                </div>
            </div>
            <div class="row justify-content-around py-1">
                <div class="col">
                    <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white">
                        <?= esc($partai_seni_berlangsung->nama_gelanggang ?? 'Gelanggang') ?> -
                        <?= esc($partai_seni_berlangsung->nomor_partai ?? '-') ?>
                    </p>
                </div>
                <?php if ($isBattle): ?>
                    <div class="col">
                        <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white">
                            <?= strtoupper(esc($partai_seni_berlangsung->babak_battle ?? '-')) ?>
                        </p>
                    </div>
                <?php endif; ?>
                <div class="col">
                    <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white px-2">
                        <?= strtoupper(esc($kompetisi_seni->nama_kategori_usia ?? '-')) ?>
                    </p>
                </div>
                <div class="col">
                    <p class="h4 my-1 text-center bg-gradient-180-gray-dark text-white px-2">
                        <?= strtoupper(esc($kompetisi_seni->jenis_seni ?? '-')) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-1 px-0 py-2 d-flex justify-content-center align-items-center">
            <img src="<?= base_url('assets/images/brand/dps/logo-federation.png') ?>"
                 alt="National Pencak Silat Federation"
                 class="img-fluid"
                 onerror="this.style.display='none'">
        </div>
    </div>

    <!-- ═══ PESERTA SENI ═══ -->
    <?php
        $peserta = $peserta_seni ?? [];
        $namaKontingen = !empty($peserta) ? ($peserta[0]->nama_kontingen ?? '-') : '-';
    ?>
    <div class="row my-4 justify-content-center opacity" id="daftar-peserta">
        <div class="<?= (count($peserta) > 1) ? 'col-12' : 'col-8' ?>">
            <div class="row">
                <div class="col-3 d-flex justify-content-center align-items-center bg-dark bg-gradient">
                    <img src="<?= base_url('assets/images/bendera/' . strtolower(str_replace(' ', '_', $namaKontingen)) . '.png') ?>"
                         class="img-fluid img-thumbnail"
                         alt="<?= esc($namaKontingen) ?>"
                         onerror="this.onerror=null; this.src='<?= base_url('assets/images/brand/dps/logo.png') ?>';">
                </div>
                <div class="col-9">
                    <div class="row h-100">
                        <div class="col-12 bg-gradient-180-gray-dark justify-content-center d-flex flex-column py-2">
                            <p class="h3 text-center text-truncate text-uppercase m-0 fw-bolder text-white">
                                <?php if (!empty($peserta)): ?>
                                    <?php foreach ($peserta as $idx => $p): ?>
                                        <?= esc($p->nama_pendaftar) ?><?= ($idx < count($peserta) - 1) ? ' - ' : '' ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?= esc($penampilan_seni_berlangsung->nama_kelompok ?? $penampilan_seni_berlangsung->nama_pendaftar ?? 'Peserta') ?>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-12 bg-white">
                            <div class="row h-100">
                                <div class="col-12 justify-content-center d-flex flex-column py-2">
                                    <p class="text-truncate m-0 h3 text-center"><?= esc($namaKontingen) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
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
    <div class="row shadow-lg" id="juri-grid">
        <div class="col-12">
            <div class="row urutan_total_nilai_juri">
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
                    <div class="col mb-3 kolom_total_nilai opacity <?= $terpilih ? 'terpilih' : 'tidak-terpilih' ?>"
                         data-id-perangkat="<?= (int) $juriId ?>">
                        <div class="row bg-white">
                            <div class="col-12 bg-gradient-180-gray-dark">
                                <p class="h5 fw-bolder text-white text-center my-2 text-uppercase nomor_juri label-juri">
                                    Juri <?= $i + 1 ?>
                                </p>
                            </div>
                            <div class="col-12 kolom_bobot_total_nilai">
                                <p class="fw-bolder text-center my-1 nilai-juri total_nilai_juri_<?= $juriId ?> juri_<?= $juriId ?>">
                                    <?= $nilaiJuri > 0 ? number_format($nilaiJuri, 3) : '-' ?>
                                </p>
                            </div>
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
        if ($catatan) {
            $cat = is_string($catatan) ? json_decode($catatan) : $catatan;
        }
        $medianKebenaran = $cat->median_kebenaran ?? 0;
        $stdDev          = $cat->standar_deviasi ?? 0;
        $median          = $cat->median ?? 0;
        $hukuman         = $cat->hukuman ?? 0;
    ?>
    <div class="row mt-3">
        <div class="col-12 col-md-3 mb-3 ps-md-0 kolom-median-kebenaran opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Median Kebenaran</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 median_kebenaran">
                        <?= number_format((float)$medianKebenaran, 3) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 mb-3 kolom-standar-deviasi opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Standard Deviation</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 standar_deviasi">
                        <?= number_format((float)$stdDev, 6) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 mb-3 kolom-median opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Median</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 median">
                        <?= number_format((float)$median, 3) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3 mb-3 pe-md-0 kolom-hukuman opacity">
            <div class="bg-white shadow-lg h-100">
                <div class="bg-gradient-180-gray-dark">
                    <p class="h4 text-white text-center my-2 text-uppercase">Penalty</p>
                </div>
                <div class="col-12">
                    <p class="fw-bolder text-center m-0 display-4 lh-1 hukuman">
                        <?= (float)$hukuman > 0 ? '-' . number_format((float)$hukuman, 1) : '0' ?>
                    </p>
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
    <div class="row mb-2">
        <div class="col-12 col-md-6 opacity kolom-nilai-akhir">
            <div class="row shadow-lg">
                <div class="col <?= $bgFinal ?> col-12">
                    <p class="lh-1 fw-bolder text-center my-1 text-white nilai_akhir" id="nilai-akhir-display">
                        <?= number_format($nilaiAkhir, 3) ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 opacity kolom-waktu">
            <div class="row shadow-lg">
                <div class="col-12 bg-white">
                    <p class="lh-1 fw-bolder text-center my-1 text-dark waktu_tampil" id="timer-seni">
                        <?= sprintf('%02d:%02d', floor($waktuTampil / 60), $waktuTampil % 60) ?>
                    </p>
                </div>
            </div>
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
    const elJuriGrid = document.getElementById('juri-grid');

    // ────────────────────────────────────────────────────────────────
    // Animation sequence (parity legacy fadeInDown stagger)
    // ────────────────────────────────────────────────────────────────
    function startAnimation() {
        const seq = [
            ['#competition-title', 'fadeInDown', 0],
            ['#daftar-peserta', 'fadeInDown', 700],
        ];
        seq.forEach(([sel, cls, delay]) => {
            setTimeout(() => {
                document.querySelectorAll(sel).forEach(el => {
                    el.classList.add('animated', cls);
                    el.classList.remove('opacity');
                });
            }, delay);
        });

        // Stagger juri columns
        const juriCols = document.querySelectorAll('.kolom_total_nilai');
        juriCols.forEach((el, i) => {
            setTimeout(() => {
                el.classList.add('animated', 'fadeInDown');
                el.classList.remove('opacity');
            }, 1400 + i * 200);
        });

        // Ringkasan + final
        const ringkasanDelay = 1400 + juriCols.length * 200;
        ['.kolom-median-kebenaran', '.kolom-standar-deviasi', '.kolom-median', '.kolom-hukuman'].forEach((sel, i) => {
            setTimeout(() => {
                document.querySelectorAll(sel).forEach(el => {
                    el.classList.add('animated', 'fadeInDown');
                    el.classList.remove('opacity');
                });
            }, ringkasanDelay + i * 200);
        });

        setTimeout(() => {
            document.querySelectorAll('.kolom-nilai-akhir').forEach(el => {
                el.classList.add('animated', 'fadeInLeft');
                el.classList.remove('opacity');
            });
            document.querySelectorAll('.kolom-waktu').forEach(el => {
                el.classList.add('animated', 'fadeInRight');
                el.classList.remove('opacity');
            });
        }, ringkasanDelay + 4 * 200);
    }

    // ────────────────────────────────────────────────────────────────
    // Count-up timer — requestAnimationFrame, no jQuery plugin
    // ────────────────────────────────────────────────────────────────
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
    function setTimerSeconds(seconds) {
        elapsedMs = (typeof seconds === 'number' ? seconds : 0) * 1000;
        elTimer.textContent = fmtTime(elapsedMs);
    }

    // ────────────────────────────────────────────────────────────────
    // Update nilai juri columns (sort ascending + terpilih highlight)
    // ────────────────────────────────────────────────────────────────
    function updateJuriColumns(dataNilai) {
        if (!dataNilai || !Array.isArray(dataNilai)) return;

        const juriScores = dataNilai.map((j, idx) => {
            let parsed = j.penilaian;
            if (typeof parsed === 'string') {
                try { parsed = JSON.parse(parsed); } catch (e) { parsed = null; }
            }
            const total = parsed && parsed.penilaian && parsed.penilaian.ringkasan
                ? parseFloat(parsed.penilaian.ringkasan.total_nilai) || 0 : 0;
            return {
                index: idx,
                idPerangkat: j.id_perangkat_pertandingan,
                total: total,
                terpilih: j.terpilih == 1
            };
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
            col.dataset.idPerangkat = juri.idPerangkat;
        });
    }

    function updateRingkasan(catatan) {
        if (!catatan) return;
        let c = catatan;
        if (typeof c === 'string') {
            try { c = JSON.parse(c); } catch (e) { return; }
        }
        if (c.median_kebenaran !== undefined) {
            document.querySelector('.median_kebenaran').textContent = parseFloat(c.median_kebenaran).toFixed(3);
        }
        if (c.standar_deviasi !== undefined) {
            document.querySelector('.standar_deviasi').textContent = parseFloat(c.standar_deviasi).toFixed(6);
        }
        if (c.median !== undefined) {
            document.querySelector('.median').textContent = parseFloat(c.median).toFixed(3);
        }
        if (c.hukuman !== undefined) {
            const h = parseFloat(c.hukuman);
            document.querySelector('.hukuman').textContent = h > 0 ? '-' + h.toFixed(1) : '0';
        }
    }

    // ────────────────────────────────────────────────────────────────
    // State sync from polling
    // ────────────────────────────────────────────────────────────────
    function syncState(data) {
        if (!data) return;
        if (data.penampilan_seni_berlangsung) {
            const p = data.penampilan_seni_berlangsung;
            if (p.nilai_akhir !== undefined) {
                elNilaiAkhir.textContent = parseFloat(p.nilai_akhir).toFixed(3);
            }
            if (p.catatan_nilai_sama) updateRingkasan(p.catatan_nilai_sama);
            if (typeof p.waktu_tampil !== 'undefined') {
                const sec = parseInt(p.waktu_tampil, 10) || 0;
                if (p.status_penampilan === 'sedang_tampil') {
                    if (!timerRunning) { setTimerSeconds(sec); startTimer(); }
                } else {
                    setTimerSeconds(sec);
                    stopTimer();
                }
            }
        }
        if (data.data_nilai && data.data_nilai[idPenampilan]) {
            updateJuriColumns(data.data_nilai[idPenampilan]);
        }
    }

    // ────────────────────────────────────────────────────────────────
    // Socket.IO real-time
    // ────────────────────────────────────────────────────────────────
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
            if (d.status_penampilan === 'sedang_tampil') {
                setTimerSeconds(sec);
                startTimer();
            } else {
                setTimerSeconds(sec);
                stopTimer();
            }
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
            stopTimer();
            poll();
        });

        socket.on('PENAMPILAN_SELESAI', (d) => {
            if (!d || String(d.id_penampilan_seni) !== String(idPenampilan)) return;
            setTimeout(() => window.location.reload(), 500);
        });

        socket.on('SENI_SELESAI', () => {
            window.location = '<?= base_url('layar/standby') ?>';
        });

        socket.on('ROOM_RESET', () => {
            window.location = '<?= base_url('layar/standby') ?>';
        });
    }

    // ────────────────────────────────────────────────────────────────
    // HTTP polling fallback
    // ────────────────────────────────────────────────────────────────
    function poll() {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('layar/refresh-status-seni') ?>/' + idPenampilan, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(r => r.json())
        .then(d => {
            if (d && d.csrf_hash) csrfHash = d.csrf_hash;
            if (d && d.status === true) {
                if (d.reload === true) { window.location.reload(); }
                else if (d.hasil_pool_seni === true) {
                    window.location.href = '<?= base_url('layar/hasil-pool-seni') ?>/' + d.id_kompetisi_seni;
                } else if (d.hasil_battle_seni === true) {
                    window.location.href = '<?= base_url('layar/hasil-battle-seni') ?>/' + d.id_battle_seni;
                }
                return;
            }
            if (d && d.status === false) syncState(d);
        })
        .catch(() => {});
    }

    setInterval(() => { if (!rtConnected) poll(); }, 2000);
    setInterval(() => { if (rtConnected) poll(); }, 10000);

    // ────────────────────────────────────────────────────────────────
    // Boot
    // ────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        startAnimation();
        if (root.dataset.statusPenampilan === 'sedang_tampil') {
            startTimer();
        }
        poll();
    });
})();
</script>
<?= $this->endSection() ?>
