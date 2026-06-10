/**
 * Ketua Pertandingan — Tanding JS
 * Handles: hukuman/jatuhan/binaan input, CSRF rotation, polling,
 *          verifikasi modals, socket.io, rekap display update
 */
(function () {
    'use strict';

    const wrapper = document.getElementById('kp-wrapper');
    if (!wrapper) return;

    const config = {
        idPertandingan: wrapper.dataset.idPertandingan,
        ronde: wrapper.dataset.ronde,
        endpointEdit: wrapper.dataset.endpointEdit,
        endpointRefresh: wrapper.dataset.endpointRefresh,
        csrfName: wrapper.dataset.csrfName,
        csrfHash: wrapper.dataset.csrfHash,
    };

    let locked = false;

    // ─── CSRF & Fetch ─────────────────────────────────────────────────────

    function rotateCsrf(newHash) {
        if (newHash) config.csrfHash = newHash;
    }

    function buildBody(extra) {
        const body = new URLSearchParams();
        body.append(config.csrfName, config.csrfHash);
        if (extra) Object.entries(extra).forEach(([k, v]) => body.append(k, String(v)));
        return body;
    }

    function postJSON(url, params) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: buildBody(params),
        }).then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        }).then(data => {
            rotateCsrf(data.csrf_hash);
            return data;
        });
    }

    // ─── Rekap Rendering ──────────────────────────────────────────────────

    function applyRingkasan(ring) {
        if (!ring || !ring.semua_ronde) return;
        ['merah', 'biru'].forEach(function (s) {
            const r = ring.semua_ronde[s] || {};
            const box = document.getElementById('rekap-' + s);
            if (!box) return;
            const teg = box.querySelector('.rk-teguran');
            const per = box.querySelector('.rk-peringatan');
            const jat = box.querySelector('.rk-jatuhan');
            const bin = box.querySelector('.rk-binaan');
            if (teg) teg.textContent = (r.teguran_1 || 0) + (r.teguran_2 || 0);
            if (per) per.textContent = (r.peringatan_1 || 0) + (r.peringatan_2 || 0);
            if (jat) jat.textContent = r.jatuhan || 0;
            if (bin) bin.textContent = r.binaan_1 || 0;
        });
    }

    function updateSkor(skorMerah, skorBiru) {
        const elM = document.getElementById('skor-merah');
        const elB = document.getElementById('skor-biru');
        if (elM) elM.textContent = skorMerah;
        if (elB) elB.textContent = skorBiru;
    }

    // ─── Button Click (hukuman/jatuhan/binaan) ────────────────────────────

    function kirim(sudut, mode, jumlah, btn) {
        if (locked) return;
        locked = true;
        if (btn) btn.classList.add('is-loading');

        postJSON(config.endpointEdit, { sudut: sudut, mode: mode, jumlah: jumlah })
            .then(data => {
                if (data && data.status === true) {
                    updateSkor(data.skor_merah, data.skor_biru);
                    applyRingkasan(data.ringkasan);
                    pulseBtn(btn, true);
                } else {
                    pulseBtn(btn, false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || '', timer: 1500, showConfirmButton: false });
                    }
                }
            })
            .catch(() => {
                pulseBtn(btn, false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Koneksi gagal', timer: 1500, showConfirmButton: false });
                }
            })
            .finally(() => { locked = false; if (btn) btn.classList.remove('is-loading'); });
    }

    function pulseBtn(btn, success) {
        if (!btn) return;
        const cls = success ? 'pulse-ok' : 'pulse-fail';
        btn.classList.add(cls);
        setTimeout(() => btn.classList.remove(cls), 400);
    }

    // Bind all KP buttons
    document.querySelectorAll('.kp-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const sudut = this.dataset.sudut;
            const mode = this.dataset.mode;
            const jumlah = this.dataset.jumlah;

            // Confirm for heavy penalties
            if (mode === 'peringatan_1' || mode === 'peringatan_2') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Konfirmasi ' + this.textContent.trim(),
                        text: 'Sudut ' + sudut.toUpperCase() + ' — nilai ' + jumlah,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#c62828',
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, Terapkan',
                    }).then(result => {
                        if (result.isConfirmed) kirim(sudut, mode, jumlah, btn);
                    });
                } else {
                    if (confirm('Terapkan ' + this.textContent.trim() + ' ke sudut ' + sudut + '?')) {
                        kirim(sudut, mode, jumlah, btn);
                    }
                }
            } else {
                kirim(sudut, mode, jumlah, btn);
            }
        });
    });

    // ─── Polling ──────────────────────────────────────────────────────────

    setInterval(function () {
        postJSON(config.endpointRefresh, {})
            .then(data => {
                if (data && data.reload === true) {
                    window.location.reload();
                } else if (data && data.status === false) {
                    updateSkor(data.skor_merah, data.skor_biru);
                    applyRingkasan(data.ringkasan);
                }
            })
            .catch(() => {});
    }, 4000);

    // ─── Verifikasi Modals ────────────────────────────────────────────────

    function showVerifikasi(jenis, sudut) {
        const modalId = jenis === 'jatuhan' ? 'modalVerifikasiJatuhan' : 'modalVerifikasiPelanggaran';
        const sudutEl = document.getElementById('verifikasi-' + jenis + '-sudut');
        if (sudutEl) {
            sudutEl.textContent = sudut.toUpperCase();
            sudutEl.className = 'fw-bold text-uppercase ' + (sudut === 'merah' ? 'text-danger' : 'text-primary');
        }
        const modalEl = document.getElementById(modalId);
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        modalEl.querySelectorAll('.modal-footer button').forEach(btn => {
            btn.onclick = function () {
                // KP validates jatuhan/pelanggaran — apply or reject
                const jawaban = this.dataset.jawaban;
                if (jawaban === 'valid') {
                    // Already applied via KP button click; just close
                } else {
                    // Revert: send hapus command
                    kirim(sudut, jenis, 'hapus', null);
                }
                modal.hide();
            };
        });
    }

    // ─── Socket.IO ────────────────────────────────────────────────────────

    function initSocket() {
        if (typeof io === 'undefined') return;
        const url = window.SOCKET_URL || 'http://localhost:3000';
        const socket = io(url);

        socket.emit('JOIN_ROOM', { id_pertandingan: config.idPertandingan });

        socket.on('NILAI_UPDATE', data => {
            if (data && String(data.id_pertandingan) === config.idPertandingan) {
                if (data.skor_merah !== undefined) updateSkor(data.skor_merah, data.skor_biru);
            }
        });

        socket.on('VERIFIKASI_JATUHAN', data => {
            if (data && String(data.id_pertandingan) === config.idPertandingan) {
                showVerifikasi('jatuhan', data.sudut);
            }
        });

        socket.on('VERIFIKASI_PELANGGARAN', data => {
            if (data && String(data.id_pertandingan) === config.idPertandingan) {
                showVerifikasi('pelanggaran', data.sudut);
            }
        });

        socket.on('MATCH_STATUS_CHANGE', data => {
            if (data && String(data.id_pertandingan) === config.idPertandingan) {
                window.location.reload();
            }
        });
    }

    initSocket();

    // ─── CSS pulse classes (injected dynamically) ─────────────────────────
    const style = document.createElement('style');
    style.textContent = `
        .pulse-ok { animation: kpPulseOk 0.35s ease; }
        .pulse-fail { animation: kpPulseFail 0.35s ease; }
        @keyframes kpPulseOk { 0%{box-shadow:inset 0 0 0 0 rgba(46,125,50,0.6)} 100%{box-shadow:inset 0 0 30px 0 rgba(46,125,50,0)} }
        @keyframes kpPulseFail { 0%{box-shadow:inset 0 0 0 0 rgba(198,40,40,0.6)} 100%{box-shadow:inset 0 0 30px 0 rgba(198,40,40,0)} }
    `;
    document.head.appendChild(style);

})();
