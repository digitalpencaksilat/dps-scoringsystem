/**
 * Juri Tanding PERSILAT — modular JS
 * Handles: scoring input, CSRF rotation, polling, verifikasi modal, socket events
 */
(function () {
    'use strict';

    const wrapper = document.getElementById('juri-wrapper');
    if (!wrapper) return;

    const config = {
        idPertandingan: wrapper.dataset.idPertandingan,
        ronde: wrapper.dataset.ronde,
        endpointEdit: wrapper.dataset.endpointEdit,
        endpointRefresh: wrapper.dataset.endpointRefresh,
        endpointVerifikasi: wrapper.dataset.endpointVerifikasi,
        csrfName: wrapper.dataset.csrfName,
        csrfHash: wrapper.dataset.csrfHash,
    };

    let locked = false;
    let pollInterval = null;

    // ─── CSRF ─────────────────────────────────────────────────────────────

    function rotateCsrf(newHash) {
        if (newHash) config.csrfHash = newHash;
    }

    function buildBody(extra) {
        const body = new URLSearchParams();
        body.append(config.csrfName, config.csrfHash);
        if (extra) {
            Object.entries(extra).forEach(([k, v]) => body.append(k, v));
        }
        return body;
    }

    // ─── AJAX Helper ──────────────────────────────────────────────────────

    function postJSON(url, params) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: buildBody(params),
        }).then(r => r.json()).then(data => {
            rotateCsrf(data.csrf_hash);
            return data;
        });
    }

    // ─── Skor Rendering ───────────────────────────────────────────────────

    function hitungSkorSudut(sudutData) {
        if (!sudutData || !sudutData.ringkasan) return 0;
        return sudutData.ringkasan.nilai_akhir || 0;
    }

    function renderSkor(response) {
        if (!response) return;
        const merah = response.merah || response.response?.merah;
        const biru = response.biru || response.response?.biru;
        if (merah) document.getElementById('skor-merah').textContent = hitungSkorSudut(merah);
        if (biru) document.getElementById('skor-biru').textContent = hitungSkorSudut(biru);
    }

    // ─── Scoring Input ────────────────────────────────────────────────────

    function kirimNilai(sudut, entryObj, btn) {
        if (locked) return;
        locked = true;
        if (btn) btn.classList.add('is-loading');

        postJSON(config.endpointEdit, {
            sudut: sudut,
            entry: JSON.stringify(entryObj),
        })
        .then(data => {
            if (data && data.status === true) {
                renderSkor(data.response || data);
                pulseBtn(btn, true);
            } else {
                pulseBtn(btn, false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: (data && data.message) || 'Input ditolak.', timer: 1800, showConfirmButton: false });
                }
            }
        })
        .catch(() => {
            pulseBtn(btn, false);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Koneksi', text: 'Gagal mengirim nilai.', timer: 1500, showConfirmButton: false });
            }
        })
        .finally(() => {
            locked = false;
            if (btn) btn.classList.remove('is-loading');
        });
    }

    function pulseBtn(btn, success) {
        if (!btn) return;
        const cls = success ? 'pulse-success' : 'pulse-error';
        btn.classList.add(cls);
        setTimeout(() => btn.classList.remove(cls), 300);
    }

    // ─── Event Listeners: Buttons ─────────────────────────────────────────

    document.querySelectorAll('.btn-nilai').forEach(btn => {
        btn.addEventListener('click', function () {
            kirimNilai(this.dataset.sudut, { nilai: parseInt(this.dataset.nilai, 10) }, this);
        });
    });

    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function () {
            kirimNilai(this.dataset.sudut, { action: 'remove' }, this);
        });
    });

    // ─── Polling ──────────────────────────────────────────────────────────

    function startPolling() {
        pollInterval = setInterval(() => {
            postJSON(config.endpointRefresh, {})
                .then(data => {
                    if (data && data.reload === true) {
                        window.location.reload();
                    } else if (data && data.status === false && data.data_nilai) {
                        renderSkor(data.data_nilai);
                    }
                })
                .catch(() => {}); // silent fail on network error
        }, 4000);
    }

    // ─── Verifikasi Modals ────────────────────────────────────────────────

    function showVerifikasiModal(jenis, sudut) {
        const modalId = jenis === 'jatuhan' ? 'modalVerifikasiJatuhan' : 'modalVerifikasiPelanggaran';
        const sudutEl = document.getElementById(`verifikasi-${jenis}-sudut`);
        if (sudutEl) {
            sudutEl.textContent = sudut.toUpperCase();
            sudutEl.className = `fw-bold text-uppercase ${sudut === 'merah' ? 'text-danger' : 'text-primary'}`;
        }

        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();

        // Setup jawaban buttons
        const footer = document.querySelector(`#${modalId} .modal-footer`);
        footer.querySelectorAll('button').forEach(btn => {
            btn.onclick = function () {
                submitVerifikasi(jenis, sudut, this.dataset.jawaban);
                modal.hide();
            };
        });
    }

    function submitVerifikasi(jenis, sudut, jawaban) {
        postJSON(config.endpointVerifikasi, {
            jenis: jenis,
            sudut: sudut,
            jawaban: jawaban,
        }).catch(() => {});
    }

    // ─── Socket.IO Integration (if available) ─────────────────────────────

    function initSocket() {
        if (typeof io === 'undefined') return;

        const socket = io(window.REALTIME_URL || 'http://localhost:3000');
        socket.emit('JOIN_ROOM', { id_pertandingan: config.idPertandingan });

        socket.on('NILAI_UPDATE', data => {
            if (data && data.id_pertandingan == config.idPertandingan) {
                if (data.skor_merah !== undefined) {
                    document.getElementById('skor-merah').textContent = data.skor_merah;
                }
                if (data.skor_biru !== undefined) {
                    document.getElementById('skor-biru').textContent = data.skor_biru;
                }
            }
        });

        socket.on('VERIFIKASI_JATUHAN', data => {
            if (data && data.id_pertandingan == config.idPertandingan) {
                showVerifikasiModal('jatuhan', data.sudut);
            }
        });

        socket.on('VERIFIKASI_PELANGGARAN', data => {
            if (data && data.id_pertandingan == config.idPertandingan) {
                showVerifikasiModal('pelanggaran', data.sudut);
            }
        });

        socket.on('MATCH_STATUS_CHANGE', data => {
            if (data && data.id_pertandingan == config.idPertandingan) {
                window.location.reload();
            }
        });
    }

    // ─── Init ─────────────────────────────────────────────────────────────

    renderSkor(typeof JURI_TANDING_INIT !== 'undefined' ? JURI_TANDING_INIT : null);
    startPolling();
    initSocket();

})();
