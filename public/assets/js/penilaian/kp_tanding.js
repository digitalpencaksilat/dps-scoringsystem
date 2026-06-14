/**
 * Ketua Pertandingan — Tanding JS (Full Rewrite v3)
 * Parity legacy: persilat.js (1381 lines)
 *
 * FEATURES:
 * - Monitoring: render rincian nilai per juri (spans + warna + popover + strikethrough)
 * - Monitoring: render penilaian_verified (Valid Score row)
 * - Monitoring: render binaan per ronde (badge)
 * - Monitoring: ringkasan per_ronde + semua_ronde (pukulan_tendangan, jatuhan, hukuman, nilai_akhir)
 * - Monitoring: ringkasan penalty/striking tabs realtime
 * - Monitoring: full tiebreaker highlight_nilai_akhir (PERSILAT cascade)
 * - Monitoring: riwayat verifikasi (waktu menit:detik, colored jawaban cells)
 * - Dewan: toggle behavior, sequential lock, reverse-delete protection
 * - Verifikasi: jatuhan + pelanggaran modals, polling jawaban
 * - Timer: sync via polling + socket.io
 * - Polling 3s / socket.io realtime
 */
(function () {
    'use strict';

    const wrapper = document.getElementById('kp-wrapper');
    if (!wrapper) return;

    // ─── Config from DOM ──────────────────────────────────────────────────
    const config = {
        idPertandingan: wrapper.dataset.idPertandingan,
        ronde: wrapper.dataset.ronde,
        totalRonde: parseInt(wrapper.dataset.totalRonde) || 3,
        endpointEdit: wrapper.dataset.endpointEdit,
        endpointRefresh: wrapper.dataset.endpointRefresh,
        endpointVerifikasiCreate: wrapper.dataset.endpointVerifikasiCreate,
        endpointVerifikasiUpdate: wrapper.dataset.endpointVerifikasiUpdate,
        endpointVerifikasiJawaban: wrapper.dataset.endpointVerifikasiJawaban,
        csrfName: wrapper.dataset.csrfName,
        csrfHash: wrapper.dataset.csrfHash,
        jumlahJuri: parseInt(wrapper.dataset.jumlahJuri) || 3,
    };

    // ─── State ────────────────────────────────────────────────────────────
    let locked = false;
    let dataNilai = (typeof KP_INIT !== 'undefined' && KP_INIT.dataNilai) ? KP_INIT.dataNilai : {};
    let pertandingan = (typeof KP_INIT !== 'undefined' && KP_INIT.pertandingan) ? KP_INIT.pertandingan : {};
    let ringkasan = (typeof KP_INIT !== 'undefined' && KP_INIT.ringkasan) ? KP_INIT.ringkasan : {};
    let verifikasiBerlangsung = (typeof KP_INIT !== 'undefined') ? KP_INIT.verifikasiBerlangsung : null;
    let riwayatVerifikasi = (typeof KP_INIT !== 'undefined' && KP_INIT.riwayatVerifikasi) ? KP_INIT.riwayatVerifikasi : [];
    let jawabanRiwayat = (typeof KP_INIT !== 'undefined' && KP_INIT.jawabanRiwayatVerifikasi) ? KP_INIT.jawabanRiwayatVerifikasi : {};
    let timerInterval = null;
    let sisaWaktu = 0;

    // Bootstrap modal instances
    let modalJatuhan = null;
    let modalPelanggaran = null;

    // ═══════════════════════════════════════════════════════════════════════
    //  CSRF & FETCH
    // ═══════════════════════════════════════════════════════════════════════

    function rotateCsrf(newHash) {
        if (newHash) config.csrfHash = newHash;
    }

    function buildBody(extra) {
        const body = new URLSearchParams();
        body.append(config.csrfName, config.csrfHash);
        if (extra) Object.entries(extra).forEach(function (kv) { body.append(kv[0], String(kv[1])); });
        return body;
    }

    function postJSON(url, params) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: buildBody(params),
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const csrfHeader = r.headers.get('X-CSRF-TOKEN');
            if (csrfHeader) rotateCsrf(csrfHeader);
            return r.json();
        }).then(function (data) {
            rotateCsrf(data.csrf_hash);
            return data;
        });
    }

    function getJSON(url) {
        return fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        }).then(function (data) {
            rotateCsrf(data.csrf_hash);
            return data;
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  UTILITIES
    // ═══════════════════════════════════════════════════════════════════════

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function getTimestamp(unixTs) {
        if (!unixTs) return '--:--:--';
        var d = new Date(unixTs * 1000);
        return d.getHours() + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
    }

    function el(sel) { return document.querySelector(sel); }
    function els(sel) { return document.querySelectorAll(sel); }

    function setHtml(sel, html) {
        var e = document.querySelector(sel);
        if (e) e.innerHTML = html;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  MONITORING — FULL RENDER (parity legacy update_tampilan_nilai)
    // ═══════════════════════════════════════════════════════════════════════

    function updateTampilanNilai(data) {
        if (!data || !data.juri) return;
        dataNilai = data;

        var juriArr = data.juri || [];

        // ─── RENDER RINCIAN NILAI PER JURI ──────────────────────────
        juriArr.forEach(function (juri) {
            var idP = juri.id_perangkat_pertandingan;
            var penilaianTanding = juri.penilaian_tanding || {};

            ['merah', 'biru'].forEach(function (sudut) {
                var decoded = penilaianTanding[sudut];
                if (!decoded || !decoded.ronde_pertandingan) return;

                var rincianSemuaRonde = '';
                var jatuhanSemuaRonde = '';
                var hukumanSemuaRonde = '';

                var rondes = decoded.ronde_pertandingan;
                for (var ronde in rondes) {
                    if (!rondes.hasOwnProperty(ronde)) continue;
                    var nilaiRonde = rondes[ronde];
                    var rincian = nilaiRonde.rincian || [];

                    var rincianPerRonde = '';
                    var jatuhanPerRonde = '';
                    var hukumanPerRonde = '';

                    rincian.forEach(function (nilai) {
                        var ts = getTimestamp(nilai.timestamp);

                        if (nilai.is_deleted === true) {
                            // Soft-deleted entry — strikethrough
                            var deletedTs = getTimestamp(nilai.deleted_at);
                            var spanDeleted = '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" ' +
                                'data-bs-toggle="popover" data-bs-placement="top" ' +
                                'title="deleted at ' + deletedTs + '" style="color: #999 !important;">' +
                                nilai.nilai + '</span>';

                            if (nilai.nilai > 0 && nilai.nilai < 3) {
                                rincianPerRonde += spanDeleted;
                            } else if (nilai.nilai == 3) {
                                jatuhanPerRonde += spanDeleted;
                            } else if (nilai.nilai < 0) {
                                hukumanPerRonde += spanDeleted;
                            }
                            return;
                        }

                        if (nilai.nilai > 0 && nilai.nilai < 3) {
                            // Pukulan / Tendangan
                            if (nilai.status === 'input' || (nilai.status === 'verified' && nilai.warna == null)) {
                                rincianPerRonde += '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" ' +
                                    'data-bs-toggle="popover" data-bs-placement="top" title="' +
                                    nilai.status + ' == ' + nilai.id_nilai + ' - ' + ts + '">' +
                                    nilai.nilai + '</span>';
                            } else {
                                rincianPerRonde += '<span class="text-white px-2 py-1 d-inline-block" style="background-color:' +
                                    nilai.warna + '" data-bs-toggle="popover" data-bs-placement="top" title="' +
                                    nilai.status + ' == ' + nilai.id_nilai + ' - ' + ts + '">' +
                                    nilai.nilai + '</span>';
                            }
                        } else if (nilai.nilai == 3) {
                            // Jatuhan
                            if (nilai.status === 'input' || (nilai.status === 'verified' && nilai.warna == null)) {
                                jatuhanPerRonde += '<span class="fw-lighter text-decoration-line-through px-2 py-1 d-inline-block" ' +
                                    'data-bs-toggle="popover" data-bs-placement="top" title="' +
                                    nilai.status + ' == ' + nilai.id_nilai + ' - ' + ts + '">' +
                                    nilai.nilai + '</span>';
                            } else {
                                jatuhanPerRonde += '<span class="text-white px-2 py-1 d-inline-block" style="background-color:' +
                                    nilai.warna + '" data-bs-toggle="popover" data-bs-placement="top" title="' +
                                    nilai.status + ' == ' + nilai.id_nilai + ' - ' + ts + '">' +
                                    nilai.nilai + '</span>';
                            }
                        } else if (nilai.nilai < 0) {
                            // Hukuman
                            hukumanPerRonde += '<span class="fw-lighter px-2 py-1 d-inline-block">' +
                                nilai.nilai + '</span>';
                        }
                    });

                    rincianSemuaRonde += rincianPerRonde;
                    jatuhanSemuaRonde += jatuhanPerRonde;
                    hukumanSemuaRonde += hukumanPerRonde;

                    // Per ronde cells
                    setHtml('.ronde-' + ronde + '-juri-' + idP + '-' + sudut, rincianPerRonde || '0');
                    setHtml('.ronde-' + ronde + '-' + sudut + '-rincian-nilai-jatuhan', jatuhanPerRonde || '-');
                    setHtml('.ronde-' + ronde + '-' + sudut + '-rincian-nilai-hukuman', hukumanPerRonde || '-');
                }

                // Semua ronde cells
                setHtml('.juri-' + idP + '-nilai-' + sudut, rincianSemuaRonde || '0');
                setHtml('.semua-ronde-' + sudut + '-rincian-nilai-jatuhan', jatuhanSemuaRonde || '-');
                setHtml('.semua-ronde-' + sudut + '-rincian-nilai-hukuman', hukumanSemuaRonde || '-');
            });
        });

        // ─── RENDER PENILAIAN VERIFIED (Valid Score row) ─────────────
        var verified = data.penilaian_verified || {};
        ['merah', 'biru'].forEach(function (sudut) {
            var items = verified[sudut] || [];
            var rincianVerified = { semua_ronde: [], per_ronde: {} };

            items.forEach(function (item) {
                var entryNilai = item.entry_nilai || item;
                var ronde = item.ronde || '1';
                var nilai = entryNilai.nilai || 0;
                var warna = entryNilai.warna || '#666';

                if (nilai > 0 && nilai < 3) {
                    var span = '<span class="text-white px-2 py-1 d-inline-block" style="background-color:' +
                        warna + '">' + nilai + '</span>';
                    rincianVerified.semua_ronde.push(span);
                    if (!rincianVerified.per_ronde[ronde]) rincianVerified.per_ronde[ronde] = [];
                    rincianVerified.per_ronde[ronde].push(span);
                }
            });

            // Semua ronde verified
            setHtml('.semua-ronde-' + sudut + '-nilai-verified', rincianVerified.semua_ronde.join('') || '-');

            // Per ronde verified
            for (var ronde in rincianVerified.per_ronde) {
                if (!rincianVerified.per_ronde.hasOwnProperty(ronde)) continue;
                setHtml('.ronde-' + ronde + '-' + sudut + '-nilai-verified',
                    rincianVerified.per_ronde[ronde].join('') || '-');
            }
        });

        // ─── RENDER RINGKASAN PER RONDE ─────────────────────────────
        var ring = data.ringkasan || ringkasan;
        if (ring && ring.per_ronde) {
            for (var ronde in ring.per_ronde) {
                if (!ring.per_ronde.hasOwnProperty(ronde)) continue;
                var rondeData = ring.per_ronde[ronde];
                ['merah', 'biru'].forEach(function (sudut) {
                    var ns = rondeData[sudut] || {};
                    var pukulanTendangan = (parseInt(ns.pukulan) || 0) + (parseInt(ns.tendangan) || 0) * 2;
                    var jatuhan = (parseInt(ns.jatuhan) || 0) * 3;
                    var hukuman = (parseInt(ns.teguran_1) || 0) * -1 +
                        (parseInt(ns.teguran_2) || 0) * -2 +
                        (parseInt(ns.peringatan_1) || 0) * -5 +
                        (parseInt(ns.peringatan_2) || 0) * -10;

                    setHtml('.ronde-' + ronde + '-' + sudut + '-pukulan-tendangan', String(pukulanTendangan));
                    setHtml('.ronde-' + ronde + '-' + sudut + '-total-jatuhan', String(jatuhan));
                    setHtml('.ronde-' + ronde + '-' + sudut + '-total-hukuman', String(hukuman));
                    setHtml('.ronde-' + ronde + '-' + sudut + '-nilai-akhir', String(ns.nilai_akhir || 0));
                });
            }
        }

        // ─── RENDER BINAAN PER RONDE (badges) ───────────────────────
        setHtml('.semua-ronde-biru-binaan', '');
        setHtml('.semua-ronde-merah-binaan', '');
        if (ring && ring.per_ronde) {
            for (var rondeBin in ring.per_ronde) {
                if (!ring.per_ronde.hasOwnProperty(rondeBin)) continue;
                var rondeBinData = ring.per_ronde[rondeBin];
                ['merah', 'biru'].forEach(function (sudut) {
                    var ns = rondeBinData[sudut] || {};
                    var binaanHtml = '';

                    if (parseInt(ns.binaan_1) || 0) {
                        binaanHtml += '<span class="me-2 badge bg-warning px-2 py-1 text-white">Binaan 1</span>';
                        var elSemua = document.querySelector('.semua-ronde-' + sudut + '-binaan');
                        if (elSemua) elSemua.innerHTML += '<span class="me-2 badge bg-warning px-2 py-1 text-white">Ronde ' + rondeBin + ' - Binaan 1</span>';
                    }
                    if (parseInt(ns.binaan_2) || 0) {
                        binaanHtml += '<span class="me-2 badge bg-warning px-2 py-1 text-white">Binaan 2</span>';
                        var elSemua2 = document.querySelector('.semua-ronde-' + sudut + '-binaan');
                        if (elSemua2) elSemua2.innerHTML += '<span class="me-2 badge bg-warning px-2 py-1 text-white">Ronde ' + rondeBin + ' - Binaan 2</span>';
                    }

                    setHtml('.ronde-' + rondeBin + '-' + sudut + '-binaan', binaanHtml || '-');
                });
            }
        }

        // ─── RENDER SEMUA RONDE TOTALS ──────────────────────────────
        if (ring && ring.semua_ronde) {
            ['merah', 'biru'].forEach(function (sudut) {
                var ns = ring.semua_ronde[sudut] || {};
                var pukulanTendangan = (parseInt(ns.pukulan) || 0) + (parseInt(ns.tendangan) || 0) * 2;
                var jatuhan = (parseInt(ns.jatuhan) || 0) * 3;
                var hukuman = (parseInt(ns.teguran_1) || 0) * -1 +
                    (parseInt(ns.teguran_2) || 0) * -2 +
                    (parseInt(ns.peringatan_1) || 0) * -5 +
                    (parseInt(ns.peringatan_2) || 0) * -10;

                setHtml('.semua-ronde-' + sudut + '-pukulan-tendangan', String(pukulanTendangan));
                setHtml('.semua-ronde-' + sudut + '-total-jatuhan', String(jatuhan));
                setHtml('.semua-ronde-' + sudut + '-total-hukuman', String(hukuman));
            });
        }

        // ─── UPDATE SKOR ────────────────────────────────────────────
        var skorM = pertandingan.skor_merah || 0;
        var skorB = pertandingan.skor_biru || 0;
        updateSkor(skorM, skorB);

        // ─── RINGKASAN PENALTY/STRIKING TABS (realtime) ─────────────
        updateTampilanRingkasanNilai(ring);

        // ─── HIGHLIGHT ──────────────────────────────────────────────
        highlightNilaiAkhir(ring);

        // ─── BUTTON LOCKS (if dewan page) ───────────────────────────
        updateButtonStates(ring);

        // ─── REINIT POPOVERS ────────────────────────────────────────
        initPopovers();
    }

    function initPopovers() {
        if (typeof bootstrap === 'undefined' || !bootstrap.Popover) return;
        var triggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        triggerList.forEach(function (triggerEl) {
            // Dispose existing
            var existing = bootstrap.Popover.getInstance(triggerEl);
            if (existing) existing.dispose();
            new bootstrap.Popover(triggerEl);
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  RINGKASAN NILAI — PENALTY/STRIKING TABS (parity update_tampilan_ringkasan_nilai)
    // ═══════════════════════════════════════════════════════════════════════

    function updateTampilanRingkasanNilai(ring) {
        if (!ring || !ring.semua_ronde) return;
        ['merah', 'biru'].forEach(function (sudut) {
            var ns = ring.semua_ronde[sudut] || {};
            for (var jenis in ns) {
                if (!ns.hasOwnProperty(jenis)) continue;
                setHtml('.ringkasan-' + sudut + '-' + jenis, String(ns[jenis]));
                // Legacy also uses ringkasan_nilai_ prefix
                setHtml('.ringkasan_nilai_' + sudut + '_' + jenis, String(ns[jenis]));
            }
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SKOR UPDATE
    // ═══════════════════════════════════════════════════════════════════════

    function updateSkor(skorMerah, skorBiru) {
        var elM = document.getElementById('skor-merah');
        var elB = document.getElementById('skor-biru');
        if (elM) elM.textContent = skorMerah;
        if (elB) elB.textContent = skorBiru;
        // Also legacy selectors
        els('#skor_merah, .skor_merah').forEach(function (e) { e.textContent = skorMerah; });
        els('#skor_biru, .skor_biru').forEach(function (e) { e.textContent = skorBiru; });

        var elTotalM = document.getElementById('total-merah-allround');
        var elTotalB = document.getElementById('total-biru-allround');
        if (elTotalM) elTotalM.textContent = skorMerah;
        if (elTotalB) elTotalB.textContent = skorBiru;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  HIGHLIGHT NILAI AKHIR — FULL PERSILAT TIEBREAKER
    //  Parity legacy: peringatan2 → peringatan1 → teguran2 → teguran1 →
    //                 binaan2 → binaan1 → jatuhan → tendangan → pukulan → sama
    // ═══════════════════════════════════════════════════════════════════════

    function highlightNilaiAkhir(ring) {
        var skorMerah = parseInt(pertandingan.skor_merah) || 0;
        var skorBiru = parseInt(pertandingan.skor_biru) || 0;

        if (skorBiru > skorMerah) {
            highlightSudut('biru');
        } else if (skorMerah > skorBiru) {
            highlightSudut('merah');
        } else {
            // Tiebreaker
            if (!ring || !ring.semua_ronde) { highlightSudut('sama'); return; }
            var b = ring.semua_ronde.biru || {};
            var m = ring.semua_ronde.merah || {};

            // Yang LEBIH BANYAK hukuman = KALAH
            var tiebreakFields = ['peringatan_2', 'peringatan_1', 'teguran_2', 'teguran_1', 'binaan_2', 'binaan_1'];
            for (var i = 0; i < tiebreakFields.length; i++) {
                var field = tiebreakFields[i];
                var bVal = parseInt(b[field]) || 0;
                var mVal = parseInt(m[field]) || 0;
                if (bVal > mVal) { highlightSudut('merah'); return; }
                if (bVal < mVal) { highlightSudut('biru'); return; }
            }

            // Teknik: yang LEBIH BANYAK = MENANG
            var techFields = ['jatuhan', 'tendangan', 'pukulan'];
            for (var j = 0; j < techFields.length; j++) {
                var tf = techFields[j];
                var bTech = parseInt(b[tf]) || 0;
                var mTech = parseInt(m[tf]) || 0;
                if (bTech > mTech) { highlightSudut('biru'); return; }
                if (bTech < mTech) { highlightSudut('merah'); return; }
            }

            highlightSudut('sama');
        }
    }

    function highlightSudut(sudut) {
        var boxBiru = document.getElementById('skor-biru');
        var boxMerah = document.getElementById('skor-merah');
        if (!boxBiru || !boxMerah) return;
        var parentB = boxBiru.closest('.kp-monitor-score-box') || boxBiru.parentElement;
        var parentM = boxMerah.closest('.kp-monitor-score-box') || boxMerah.parentElement;

        // Remove all highlight classes
        [parentB, parentM].forEach(function (p) {
            if (!p) return;
            p.classList.remove('kp-winner-blue', 'kp-winner-red', 'kp-winner-highlight');
            p.style.background = '';
        });

        if (sudut === 'biru' && parentB) {
            parentB.style.background = 'linear-gradient(180deg, #1565c0 0%, #0d47a1 100%)';
            parentB.classList.add('kp-winner-highlight');
        } else if (sudut === 'merah' && parentM) {
            parentM.style.background = 'linear-gradient(180deg, #c62828 0%, #b71c1c 100%)';
            parentM.classList.add('kp-winner-highlight');
        }
        // sama = no highlight (both stay default)
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  RIWAYAT VERIFIKASI (parity legacy update_tampilan_riwayat_verifikasi)
    // ═══════════════════════════════════════════════════════════════════════

    function updateTampilanRiwayatVerifikasi(riwayat, jawaban) {
        var tbody = document.getElementById('tabel-verifikasi-body');
        if (!tbody) {
            // Legacy table id
            var tbl = document.getElementById('tabel_riwayat_verifikasi_pertandingan');
            if (tbl) tbody = tbl.querySelector('tbody');
        }
        if (!tbody) return;

        if (!riwayat || riwayat.length === 0) {
            tbody.innerHTML = '<tr><td colspan="' + (4 + config.jumlahJuri) + '" class="text-center text-muted py-3">Belum ada riwayat verifikasi</td></tr>';
            return;
        }

        var html = '';
        riwayat.forEach(function (item) {
            var idVerif = item.id_verifikasi_pertandingan;
            var jenisLabel = item.jenis_verifikasi === 'jatuhan' ? 'Dropping' : 'Violation';
            if (item.status === 'batal') jenisLabel += '<br>(canceled)';

            // Waktu format: menit:detik
            var waktuMs = parseInt(item.waktu) || 0;
            var waktuFormatted = Math.floor(waktuMs / 1000 / 60) + ':' +
                ((waktuMs / 1000) % 60).toString().padStart(2, '0');

            html += '<tr class="text-white text-center">';
            html += '<td class="align-middle bg-white text-dark">' + (item.ronde_pertandingan || '-') + '</td>';
            html += '<td class="align-middle bg-white text-dark">' + waktuFormatted + '</td>';
            html += '<td class="align-middle bg-white text-dark">' + jenisLabel + '</td>';

            // Jawaban per juri
            var jawabanArr = (jawaban && jawaban[idVerif]) ? jawaban[idVerif] : (item.jawaban || []);
            for (var j = 0; j < config.jumlahJuri; j++) {
                var ans = jawabanArr[j] ? (jawabanArr[j].jawaban || null) : null;
                html += ubahWarnaSudutKeTd(ans);
            }

            // Hasil
            html += ubahWarnaSudutKeTd(item.hasil_verifikasi || null);
            html += '</tr>';
        });
        tbody.innerHTML = html;
    }

    function ubahWarnaSudutKeTd(sudut) {
        if (sudut === 'biru') {
            return '<td class="align-middle bg-blue text-white">Blue</td>';
        } else if (sudut === 'merah') {
            return '<td class="align-middle bg-red text-white">Red</td>';
        } else if (sudut === 'invalid') {
            return '<td class="align-middle bg-warning text-white">Invalid</td>';
        } else {
            return '<td class="align-middle bg-white text-dark text-sm">No<br>Answer</td>';
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  BUTTON STATE LOGIC — DEWAN (only if dewan buttons exist)
    // ═══════════════════════════════════════════════════════════════════════

    function updateButtonStates(ring) {
        if (!ring || !document.querySelector('.kp-icon-btn')) return;

        var perRonde = ring.per_ronde || {};
        var semuaRonde = ring.semua_ronde || {};
        var rondeSekarang = config.ronde;

        ['biru', 'merah'].forEach(function (sudut) {
            var perRondeSudut = (perRonde[rondeSekarang] && perRonde[rondeSekarang][sudut]) || {};
            var semuaRondeSudut = semuaRonde[sudut] || {};

            var binaan1  = parseInt(perRondeSudut.binaan_1) || 0;
            var binaan2  = parseInt(perRondeSudut.binaan_2) || 0;
            var teguran1 = parseInt(perRondeSudut.teguran_1) || 0;
            var teguran2 = parseInt(perRondeSudut.teguran_2) || 0;
            var peringatan1 = parseInt(semuaRondeSudut.peringatan_1) || 0;
            var peringatan2 = parseInt(semuaRondeSudut.peringatan_2) || 0;

            configureButton(sudut, 'binaan_1', {
                isActive: binaan1 >= 1, canInput: true,
                canDelete: binaan1 >= 1 && binaan2 === 0,
                inputMode: 'binaan_1', inputJumlah: '1', deleteMode: 'binaan_1',
            });
            configureButton(sudut, 'binaan_2', {
                isActive: binaan2 >= 1, canInput: binaan1 >= 1,
                canDelete: binaan2 >= 1,
                inputMode: 'binaan_2', inputJumlah: '2', deleteMode: 'binaan_2',
            });
            configureButton(sudut, 'teguran_1', {
                isActive: teguran1 >= 1, canInput: true,
                canDelete: teguran1 >= 1 && teguran2 === 0,
                inputMode: 'hukuman', inputJumlah: '-1', deleteMode: 'teguran_1',
            });
            configureButton(sudut, 'teguran_2', {
                isActive: teguran2 >= 1, canInput: teguran1 >= 1,
                canDelete: teguran2 >= 1,
                inputMode: 'hukuman', inputJumlah: '-2', deleteMode: 'teguran_2',
            });
            configureButton(sudut, 'peringatan_1', {
                isActive: peringatan1 >= 1, canInput: true,
                canDelete: peringatan1 >= 1 && peringatan2 === 0,
                inputMode: 'hukuman', inputJumlah: '-5', deleteMode: 'peringatan_1',
            });
            configureButton(sudut, 'peringatan_2', {
                isActive: peringatan2 >= 1, canInput: peringatan1 >= 1,
                canDelete: peringatan2 >= 1,
                inputMode: 'hukuman', inputJumlah: '-10', deleteMode: 'peringatan_2',
            });
        });
    }

    function configureButton(sudut, btnMode, opts) {
        var btn = document.querySelector('.kp-icon-btn[data-sudut="' + sudut + '"][data-mode="' + btnMode + '"]');
        if (!btn) return;
        btn.classList.remove('kp-locked', 'kp-active', 'kp-delete-mode');

        if (opts.isActive) {
            btn.classList.add('kp-active');
            if (opts.canDelete) {
                btn.classList.add('kp-delete-mode');
                btn.dataset.actionMode = opts.deleteMode;
                btn.dataset.actionJumlah = 'hapus';
            } else {
                btn.classList.add('kp-locked');
                btn.dataset.actionMode = '';
                btn.dataset.actionJumlah = '';
            }
        } else {
            if (opts.canInput) {
                btn.dataset.actionMode = opts.inputMode;
                btn.dataset.actionJumlah = opts.inputJumlah;
            } else {
                btn.classList.add('kp-locked');
                btn.dataset.actionMode = '';
                btn.dataset.actionJumlah = '';
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  DEWAN BUTTON CLICK HANDLER
    // ═══════════════════════════════════════════════════════════════════════

    function kirim(sudut, mode, jumlah, btn) {
        if (locked) return;
        locked = true;
        if (btn) btn.classList.add('is-loading');
        disableAllHukumanButtons(true);

        postJSON(config.endpointEdit, { sudut: sudut, mode: mode, jumlah: jumlah })
            .then(function (data) {
                if (data && data.status === true) {
                    pertandingan.skor_merah = data.skor_merah;
                    pertandingan.skor_biru = data.skor_biru;
                    if (data.ringkasan) ringkasan = data.ringkasan;
                    updateSkor(data.skor_merah, data.skor_biru);
                    if (data.ringkasan) {
                        updateButtonStates(data.ringkasan);
                        highlightNilaiAkhir(data.ringkasan);
                        updateTampilanRingkasanNilai(data.ringkasan);
                    }
                    pulseBtn(btn, true);
                } else {
                    pulseBtn(btn, false);
                    showError(data && data.message ? data.message : 'Gagal menyimpan');
                }
            })
            .catch(function () { pulseBtn(btn, false); showError('Koneksi gagal'); })
            .finally(function () {
                locked = false;
                if (btn) btn.classList.remove('is-loading');
                setTimeout(function () { disableAllHukumanButtons(false); }, 500);
            });
    }

    function disableAllHukumanButtons(disable) {
        els('.kp-icon-btn').forEach(function (btn) { btn.disabled = disable; });
    }

    function pulseBtn(btn, success) {
        if (!btn) return;
        var cls = success ? 'pulse-ok' : 'pulse-fail';
        btn.classList.add(cls);
        setTimeout(function () { btn.classList.remove(cls); }, 400);
    }

    function showError(msg) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Gagal', text: msg, timer: 2000, showConfirmButton: false });
        }
    }

    function bindDewanButtons() {
        els('.kp-big-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (this.classList.contains('kp-locked')) return;
                kirim(this.dataset.sudut, this.dataset.mode, this.dataset.jumlah, this);
            });
        });

        els('.kp-icon-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (this.classList.contains('kp-locked') || this.disabled) return;
                var sudut = this.dataset.sudut;
                var mode = this.dataset.actionMode || this.dataset.mode;
                var jumlah = this.dataset.actionJumlah || this.dataset.jumlah;
                if (!mode) return;

                var self = this;
                if ((mode === 'hukuman' && (jumlah === '-5' || jumlah === '-10')) && jumlah !== 'hapus') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Konfirmasi Peringatan',
                            html: '<strong>Sudut ' + sudut.toUpperCase() + '</strong><br>' +
                                (jumlah === '-5' ? 'PERINGATAN 1' : 'PERINGATAN 2') + ' (nilai: ' + jumlah + ')',
                            icon: 'warning', showCancelButton: true,
                            confirmButtonColor: '#c62828', cancelButtonText: 'Batal', confirmButtonText: 'Ya, Terapkan',
                        }).then(function (result) { if (result.isConfirmed) kirim(sudut, mode, jumlah, self); });
                    } else {
                        if (confirm('Terapkan peringatan ke sudut ' + sudut + '?')) kirim(sudut, mode, jumlah, self);
                    }
                } else {
                    kirim(sudut, mode, jumlah, this);
                }
            });
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  VERIFIKASI JATUHAN / PELANGGARAN
    // ═══════════════════════════════════════════════════════════════════════

    function initVerifikasiModals() {
        var elJ = document.getElementById('modalVerifikasiJatuhan');
        var elP = document.getElementById('modalVerifikasiPelanggaran');
        if (elJ) modalJatuhan = new bootstrap.Modal(elJ, { keyboard: false });
        if (elP) modalPelanggaran = new bootstrap.Modal(elP, { keyboard: false });

        var btnJ = document.getElementById('btn-verifikasi-jatuhan');
        var btnP = document.getElementById('btn-verifikasi-pelanggaran');
        if (btnJ) btnJ.addEventListener('click', function () { startVerifikasi('jatuhan'); });
        if (btnP) btnP.addEventListener('click', function () { startVerifikasi('pelanggaran'); });

        bindVerifikasiFooter('modalVerifikasiJatuhan', 'jatuhan');
        bindVerifikasiFooter('modalVerifikasiPelanggaran', 'pelanggaran');
    }

    function startVerifikasi(jenis) {
        postJSON(config.endpointVerifikasiCreate, { jenis_verifikasi: jenis })
            .then(function (data) {
                if (data && data.status === true) {
                    verifikasiBerlangsung = data.verifikasi || { jenis_verifikasi: jenis };
                    openVerifikasiModal(jenis);
                    pollJawabanVerifikasi(jenis);
                } else {
                    showError(data && data.message ? data.message : 'Gagal memulai verifikasi');
                }
            })
            .catch(function () { showError('Koneksi gagal'); });
    }

    function openVerifikasiModal(jenis) {
        var container = document.getElementById('juri-cards-' + jenis);
        if (container) {
            container.querySelectorAll('.card').forEach(function (card) {
                card.classList.remove('bg-red', 'bg-blue', 'bg-warning');
                card.classList.add('bg-dark');
                var ans = card.querySelector('.kp-juri-answer');
                if (ans) ans.textContent = 'Waiting Response';
            });
        }
        if (jenis === 'jatuhan' && modalJatuhan) modalJatuhan.show();
        else if (jenis === 'pelanggaran' && modalPelanggaran) modalPelanggaran.show();
    }

    function pollJawabanVerifikasi(jenis) {
        if (!verifikasiBerlangsung) return;
        var pollFn = function () {
            if (!verifikasiBerlangsung) return;
            getJSON(config.endpointVerifikasiJawaban + '?jenis=' + jenis)
                .then(function (data) {
                    if (data && data.jawaban) highlightJawabanCards(jenis, data.jawaban);
                    if (verifikasiBerlangsung) setTimeout(pollFn, 2000);
                })
                .catch(function () { if (verifikasiBerlangsung) setTimeout(pollFn, 3000); });
        };
        setTimeout(pollFn, 1500);
    }

    function highlightJawabanCards(jenis, jawaban) {
        var container = document.getElementById('juri-cards-' + jenis);
        if (!container) return;
        // Legacy uses index-based .card-jawaban-sistem-dialog-{N}
        var cards = container.querySelectorAll('.card');
        jawaban.forEach(function (item, idx) {
            var card = cards[idx];
            if (!card) return;
            var ansEl = card.querySelector('.kp-juri-answer');
            if (item.jawaban === 'merah') {
                card.classList.remove('bg-dark'); card.classList.add('bg-red');
                if (ansEl) ansEl.textContent = 'RED';
            } else if (item.jawaban === 'biru') {
                card.classList.remove('bg-dark'); card.classList.add('bg-blue');
                if (ansEl) ansEl.textContent = 'BLUE';
            } else if (item.jawaban === 'invalid') {
                card.classList.remove('bg-dark'); card.classList.add('bg-warning');
                if (ansEl) ansEl.textContent = 'INVALID';
            }
        });
    }

    function bindVerifikasiFooter(modalId, jenis) {
        var modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        modalEl.querySelectorAll('.modal-footer button[data-jawaban]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var jawaban = this.dataset.jawaban;
                if (jawaban === 'batal') batalkanVerifikasi(jenis);
                else tetapkanVerifikasi(jenis, jawaban);
            });
        });
    }

    function tetapkanVerifikasi(jenis, hasil) {
        postJSON(config.endpointVerifikasiUpdate, { jenis_verifikasi: jenis, hasil: hasil })
            .then(function (data) {
                verifikasiBerlangsung = null;
                closeVerifikasiModal(jenis);
                if (data && data.status === true && typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Verifikasi Ditetapkan', text: 'Hasil: ' + hasil.toUpperCase(), timer: 1500, showConfirmButton: false });
                }
            })
            .catch(function () { showError('Gagal menetapkan verifikasi'); });
    }

    function batalkanVerifikasi(jenis) {
        postJSON(config.endpointVerifikasiUpdate, { jenis_verifikasi: jenis, hasil: 'batal' })
            .then(function () { verifikasiBerlangsung = null; closeVerifikasiModal(jenis); })
            .catch(function () { verifikasiBerlangsung = null; closeVerifikasiModal(jenis); });
    }

    function closeVerifikasiModal(jenis) {
        if (jenis === 'jatuhan' && modalJatuhan) modalJatuhan.hide();
        else if (jenis === 'pelanggaran' && modalPelanggaran) modalPelanggaran.hide();
    }

    function periksaSistemDialog() {
        if (!verifikasiBerlangsung) {
            if (modalJatuhan) closeVerifikasiModal('jatuhan');
            if (modalPelanggaran) closeVerifikasiModal('pelanggaran');
        } else {
            var jenis = verifikasiBerlangsung.jenis_verifikasi;
            var modalEl = document.getElementById(jenis === 'jatuhan' ? 'modalVerifikasiJatuhan' : 'modalVerifikasiPelanggaran');
            if (modalEl && !modalEl.classList.contains('show')) {
                openVerifikasiModal(jenis);
                pollJawabanVerifikasi(jenis);
            }
            // Highlight setiap kali refresh
            if (verifikasiBerlangsung.id_verifikasi_pertandingan) {
                getJSON(config.endpointVerifikasiJawaban + '?jenis=' + jenis)
                    .then(function (data) { if (data && data.jawaban) highlightJawabanCards(jenis, data.jawaban); })
                    .catch(function () {});
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  TIMER
    // ═══════════════════════════════════════════════════════════════════════

    function updateTimerDisplay(seconds) {
        var e = document.getElementById('kp-timer');
        if (!e) return;
        if (seconds < 0) seconds = 0;
        e.textContent = pad(Math.floor(seconds / 60)) + ':' + pad(seconds % 60);
    }

    function startTimer(seconds) {
        stopTimer();
        sisaWaktu = seconds;
        updateTimerDisplay(sisaWaktu);
        timerInterval = setInterval(function () {
            sisaWaktu--;
            if (sisaWaktu <= 0) { sisaWaktu = 0; stopTimer(); }
            updateTimerDisplay(sisaWaktu);
        }, 1000);
    }

    function stopTimer() {
        if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
    }

    function handleTimerData(dataWaktu) {
        if (!dataWaktu) return;

        // NEW: Server-authoritative drift-compensated timer
        // Format baru: { state, sisa_waktu_at_save, started_at_ms, server_now_ms, ronde, sisa_waktu }
        if (dataWaktu.state !== undefined && dataWaktu.sisa_waktu_at_save !== undefined) {
            var sisaAtSave = parseInt(dataWaktu.sisa_waktu_at_save) || 0;
            var startedAtMs = parseInt(dataWaktu.started_at_ms) || 0;
            var serverNowMs = parseInt(dataWaktu.server_now_ms) || 0;
            var state = dataWaktu.state || 'paused';

            var sisaSekarang = sisaAtSave;
            if (state === 'running' && startedAtMs > 0 && serverNowMs > 0) {
                var clientNowMs = Date.now();
                var elapsedMs = (serverNowMs - startedAtMs) + (clientNowMs - serverNowMs);
                var elapsedSeconds = Math.floor(elapsedMs / 1000);
                sisaSekarang = Math.max(0, sisaAtSave - elapsedSeconds);
            }

            updateTimerDisplay(sisaSekarang);
            if (state === 'running') startTimer(sisaSekarang);
            else { stopTimer(); updateTimerDisplay(sisaSekarang); }

            if (dataWaktu.ronde && String(dataWaktu.ronde) !== config.ronde) {
                config.ronde = String(dataWaktu.ronde);
                els('.kp-ronde').forEach(function (e) { e.textContent = 'Ronde ' + config.ronde; });
                els('.ronde_pertandingan').forEach(function (e) { e.textContent = 'Round ' + config.ronde; });
                updateButtonStates(ringkasan);
            }
            return;
        }

        // LEGACY fallback: old format dari polling response
        if (dataWaktu.data_waktu) {
            var dw = dataWaktu.data_waktu;
            var sisa = parseInt(dw.sisa_waktu) || 0;
            updateTimerDisplay(sisa);
            if (dataWaktu.status_pertandingan === 'berlangsung') startTimer(sisa);
            else { stopTimer(); updateTimerDisplay(sisa); }
            return;
        }

        // LEGACY fallback: socket event format
        if (dataWaktu.action || dataWaktu.aksi) {
            var action = dataWaktu.action || dataWaktu.aksi;
            var waktu = parseInt(dataWaktu.waktu) || 0;
            if (action === 'START' || action === 'RESUME') startTimer(waktu);
            else if (action === 'STOP' || action === 'PAUSE') { stopTimer(); updateTimerDisplay(waktu); }
            else if (action === 'RESET') { stopTimer(); updateTimerDisplay(waktu); }
            else if (action === 'TOGGLE') { if (timerInterval) { stopTimer(); updateTimerDisplay(waktu); } else startTimer(waktu); }

            if (dataWaktu.ronde && String(dataWaktu.ronde) !== config.ronde) {
                config.ronde = String(dataWaktu.ronde);
                els('.kp-ronde').forEach(function (e) { e.textContent = 'Ronde ' + config.ronde; });
                els('.ronde_pertandingan').forEach(function (e) { e.textContent = 'Round ' + config.ronde; });
                updateButtonStates(ringkasan);
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  POLLING (3 second interval — parity legacy refresh_status_pertandingan)
    // ═══════════════════════════════════════════════════════════════════════

    let pollTimer = null;
    let socketConnected = false;

    function startPolling() {
        if (pollTimer) clearInterval(pollTimer);
        var interval = socketConnected ? 8000 : 3000;

        pollTimer = setInterval(function () {
            postJSON(config.endpointRefresh, {})
                .then(function (data) {
                    if (!data) return;
                    if (data.status === true && data.reload === true) { window.location.reload(); return; }

                    if (data.status === false) {
                        // Update pertandingan state
                        if (data.pertandingan) pertandingan = data.pertandingan;
                        else {
                            pertandingan.skor_merah = data.skor_merah;
                            pertandingan.skor_biru = data.skor_biru;
                        }

                        if (data.ronde && String(data.ronde) !== config.ronde) {
                            config.ronde = String(data.ronde);
                            pertandingan.ronde_pertandingan = data.ronde;
                            els('.kp-ronde').forEach(function (e) { e.textContent = 'Ronde ' + config.ronde; });
                            els('.ronde_pertandingan').forEach(function (e) { e.textContent = 'Round ' + config.ronde; });
                        }

                        // Full monitoring render
                        if (data.data_nilai) {
                            dataNilai = data.data_nilai;
                            if (data.data_nilai.ringkasan) ringkasan = data.data_nilai.ringkasan;
                            updateTampilanNilai(data.data_nilai);
                        } else if (data.ringkasan) {
                            ringkasan = data.ringkasan;
                            updateSkor(pertandingan.skor_merah, pertandingan.skor_biru);
                            updateButtonStates(data.ringkasan);
                            highlightNilaiAkhir(data.ringkasan);
                            updateTampilanRingkasanNilai(data.ringkasan);
                        }

                        // Timer
                        if (data.data_waktu) handleTimerData(data.data_waktu);

                        // Verifikasi
                        verifikasiBerlangsung = data.verifikasi_pertandingan_berlangsung || data.verifikasi_berlangsung || null;
                        periksaSistemDialog();

                        // Riwayat
                        if (data.riwayat_verifikasi_pertandingan || data.riwayat_verifikasi) {
                            riwayatVerifikasi = data.riwayat_verifikasi_pertandingan || data.riwayat_verifikasi;
                            jawabanRiwayat = data.jawaban_riwayat_verifikasi_pertandingan || jawabanRiwayat;
                            updateTampilanRiwayatVerifikasi(riwayatVerifikasi, jawabanRiwayat);
                        }
                    }
                })
                .catch(function () { /* silent */ });
        }, interval);
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SOCKET.IO
    // ═══════════════════════════════════════════════════════════════════════

    function initSocket() {
        if (typeof io === 'undefined') return;
        var url = window.SOCKET_URL || 'http://localhost:3000';
        var socket = io(url, { reconnection: true, reconnectionDelay: 2000 });

        socket.on('connect', function () {
            socketConnected = true;
            socket.emit('JOIN_ROOM', { id_pertandingan: config.idPertandingan });
            
            // CRITICAL #4: Fetch fresh state immediately on reconnect to prevent UI staleness
            postJSON(config.endpointRefresh, {})
                .then(function (data) {
                    if (!data) return;
                    if (data.status === true && data.reload === true) { window.location.reload(); return; }
                    
                    if (data.status === false) {
                        if (data.pertandingan) pertandingan = data.pertandingan;
                        else {
                            pertandingan.skor_merah = data.skor_merah;
                            pertandingan.skor_biru = data.skor_biru;
                        }
                        
                        if (data.ronde && String(data.ronde) !== config.ronde) {
                            config.ronde = String(data.ronde);
                            pertandingan.ronde_pertandingan = data.ronde;
                            els('.kp-ronde').forEach(function (e) { e.textContent = 'Ronde ' + config.ronde; });
                            els('.ronde_pertandingan').forEach(function (e) { e.textContent = 'Round ' + config.ronde; });
                        }
                        
                        if (data.data_nilai) {
                            dataNilai = data.data_nilai;
                            if (data.data_nilai.ringkasan) ringkasan = data.data_nilai.ringkasan;
                            updateTampilanNilai(data.data_nilai);
                        }
                        
                        updateSkor(pertandingan.skor_merah, pertandingan.skor_biru);
                    }
                })
                .catch(function () { /* silent fail — polling will retry */ });
            
            if (pollTimer) { clearInterval(pollTimer); startPolling(); }
        });

        socket.on('disconnect', function () {
            socketConnected = false;
            if (pollTimer) { clearInterval(pollTimer); startPolling(); }
        });

        socket.on('NILAI_UPDATE', function (data) {
            if (!data || String(data.id_pertandingan) !== config.idPertandingan) return;
            if (data.skor_merah !== undefined) {
                pertandingan.skor_merah = data.skor_merah;
                pertandingan.skor_biru = data.skor_biru;
                updateSkor(data.skor_merah, data.skor_biru);
            }
            if (data.ringkasan) {
                ringkasan = data.ringkasan;
                updateButtonStates(data.ringkasan);
                highlightNilaiAkhir(data.ringkasan);
            }
        });

        socket.on('UPDATE_SKOR', function (data) {
            if (!data || String(data.id_pertandingan) !== config.idPertandingan) return;
            if (data.skor_merah !== undefined) {
                pertandingan.skor_merah = data.skor_merah;
                pertandingan.skor_biru = data.skor_biru;
                updateSkor(data.skor_merah, data.skor_biru);
            }
            if (data.ronde && String(data.ronde) !== config.ronde) window.location.reload();
        });

        socket.on('UPDATE_WAKTU', function (data) {
            if (!data || String(data.id_pertandingan) !== config.idPertandingan) return;
            handleTimerData(data);
        });

        socket.on('VERIFIKASI_JATUHAN', function (data) {
            if (!data || String(data.id_pertandingan) !== config.idPertandingan) return;
            verifikasiBerlangsung = { jenis_verifikasi: 'jatuhan' };
            openVerifikasiModal('jatuhan');
            pollJawabanVerifikasi('jatuhan');
        });

        socket.on('VERIFIKASI_PELANGGARAN', function (data) {
            if (!data || String(data.id_pertandingan) !== config.idPertandingan) return;
            verifikasiBerlangsung = { jenis_verifikasi: 'pelanggaran' };
            openVerifikasiModal('pelanggaran');
            pollJawabanVerifikasi('pelanggaran');
        });

        socket.on('MATCH_STATUS_CHANGE', function (data) {
            if (!data || String(data.id_pertandingan) !== config.idPertandingan) return;
            window.location.reload();
        });

        socket.on('ROOM_RESET', function (data) {
            if (!data || String(data.id_pertandingan) !== config.idPertandingan) return;
            window.location.reload();
        });
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  INIT
    // ═══════════════════════════════════════════════════════════════════════

    function init() {
        bindDewanButtons();
        initVerifikasiModals();

        // Initial render from server data
        if (dataNilai && dataNilai.juri) {
            updateTampilanNilai(dataNilai);
        }
        if (riwayatVerifikasi && riwayatVerifikasi.length > 0) {
            updateTampilanRiwayatVerifikasi(riwayatVerifikasi, jawabanRiwayat);
        }

        // Init timer from pertandingan data
        if (pertandingan && pertandingan.data_waktu) {
            try {
                var dw = typeof pertandingan.data_waktu === 'string'
                    ? JSON.parse(pertandingan.data_waktu)
                    : pertandingan.data_waktu;
                // Legacy format: data_waktu[ronde][1] = waktu sekarang
                if (dw && dw[config.ronde]) {
                    var sisa = parseInt(dw[config.ronde][1]) || 0;
                    updateTimerDisplay(Math.floor(sisa / 1000));
                } else if (dw && dw.sisa_waktu) {
                    updateTimerDisplay(parseInt(dw.sisa_waktu) || 0);
                }
            } catch (e) { /* ignore */ }
        }

        // Check if verifikasi already in progress
        if (verifikasiBerlangsung && verifikasiBerlangsung.status === 'berlangsung') {
            periksaSistemDialog();
        }

        startPolling();
        initSocket();
        initDeveloperOption();
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  DEVELOPER OPTION
    // ═══════════════════════════════════════════════════════════════════════

    function initDeveloperOption() {
        var btnOpen = document.getElementById('btn-open-developer-option');
        if (!btnOpen) return;

        var passcode = wrapper.dataset.developerPasscode || '4321';
        var modalEl = document.getElementById('modalDeveloperOption');
        var devModal = modalEl ? new bootstrap.Modal(modalEl, { keyboard: false }) : null;

        // Open button: prompt passcode
        btnOpen.addEventListener('click', function () {
            if (typeof Swal === 'undefined') {
                // Fallback jika SweetAlert tidak tersedia
                var input = prompt('Enter PIN Code:');
                if (input === String(passcode)) {
                    if (devModal) devModal.show();
                } else {
                    alert('Wrong Passcode!');
                }
                return;
            }
            Swal.fire({
                title: 'Attention!',
                text: 'Please Enter Your PIN Code',
                input: 'password',
                inputAttributes: { autocomplete: 'off' },
                showCancelButton: true,
                confirmButtonText: 'Submit',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#c60000',
            }).then(function (result) {
                if (result.isConfirmed) {
                    if (String(result.value) === String(passcode)) {
                        if (devModal) devModal.show();
                    } else {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: 'Wrong Passcode!' });
                    }
                }
            });
        });

        if (!modalEl) return;

        // Sync skor display saat modal dibuka
        modalEl.addEventListener('show.bs.modal', function () {
            var skorBiruEl = document.getElementById('dev-skor-biru');
            var skorMerahEl = document.getElementById('dev-skor-merah');
            var rondeEl = document.getElementById('dev-ronde');
            if (skorBiruEl) skorBiruEl.textContent = document.getElementById('skor-biru') ? document.getElementById('skor-biru').textContent : '0';
            if (skorMerahEl) skorMerahEl.textContent = document.getElementById('skor-merah') ? document.getElementById('skor-merah').textContent : '0';
            if (rondeEl) rondeEl.textContent = config.ronde || '1';
        });

        // Input buttons
        modalEl.querySelectorAll('.dev-btn-input, .dev-btn-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var sudut  = this.dataset.sudut;
                var mode   = this.dataset.mode;
                var jumlah = this.dataset.jumlah;
                if (!sudut || !mode) return;

                var self = this;
                self.disabled = true;
                self.style.opacity = '0.5';

                postJSON(config.endpointEdit, { sudut: sudut, mode: mode, jumlah: jumlah })
                    .then(function (data) {
                        if (data && data.status === true) {
                            pertandingan.skor_merah = data.skor_merah;
                            pertandingan.skor_biru  = data.skor_biru;
                            if (data.ringkasan) ringkasan = data.ringkasan;
                            updateSkor(data.skor_merah, data.skor_biru);
                            if (data.ringkasan) {
                                updateButtonStates(data.ringkasan);
                                highlightNilaiAkhir(data.ringkasan);
                                updateTampilanRingkasanNilai(data.ringkasan);
                            }
                            // Sync skor di dalam modal
                            var skorBiruEl  = document.getElementById('dev-skor-biru');
                            var skorMerahEl = document.getElementById('dev-skor-merah');
                            if (skorBiruEl)  skorBiruEl.textContent  = data.skor_biru  ?? 0;
                            if (skorMerahEl) skorMerahEl.textContent = data.skor_merah ?? 0;

                            // Pulse green
                            self.style.transition = 'box-shadow 0.3s';
                            self.style.boxShadow = '0 0 0 3px rgba(40,167,69,0.7)';
                            setTimeout(function () { self.style.boxShadow = ''; }, 500);

                            if (typeof Toastr !== 'undefined') {
                                toastr.success('Input berhasil diterapkan');
                            }
                        } else {
                            self.style.boxShadow = '0 0 0 3px rgba(220,53,69,0.7)';
                            setTimeout(function () { self.style.boxShadow = ''; }, 500);
                            showError(data && data.message ? data.message : 'Gagal menyimpan');
                        }
                    })
                    .catch(function () { showError('Koneksi gagal'); })
                    .finally(function () {
                        self.disabled = false;
                        self.style.opacity = '';
                    });
            });
        });
    }

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
