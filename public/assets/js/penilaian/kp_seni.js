/**
 * Ketua Pertandingan — Seni JS
 * Handles: hukuman input, toggle akses penilaian, diskualifikasi,
 *          polling juri ready status, median calculation, socket.io
 */
(function () {
    'use strict';

    const wrapper = document.getElementById('kp-seni-wrapper');
    if (!wrapper) return;

    const config = {
        idPenampilan: wrapper.dataset.idPenampilan,
        endpointEdit: wrapper.dataset.endpointEdit,
        endpointRefresh: wrapper.dataset.endpointRefresh,
        endpointAkses: wrapper.dataset.endpointAkses,
        endpointDq: wrapper.dataset.endpointDq,
        endpointUndq: wrapper.dataset.endpointUndq,
        csrfName: wrapper.dataset.csrfName,
        csrfHash: wrapper.dataset.csrfHash,
        akses: wrapper.dataset.akses || 'dibuka',
        sistem: wrapper.dataset.sistem || 'pool',
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

    // ─── Hukuman Input ────────────────────────────────────────────────────

    function setupHukuman() {
        const btn = document.getElementById('btn-tambah-hukuman');
        if (!btn) return;

        btn.addEventListener('click', function () {
            const jenis = document.getElementById('select-jenis-hukuman').value;
            const jumlah = parseInt(document.getElementById('input-jumlah-hukuman').value, 10) || 1;

            if (!jenis) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Pilih jenis hukuman', timer: 1500, showConfirmButton: false });
                }
                return;
            }

            if (locked) return;
            locked = true;
            btn.classList.add('is-loading');

            postJSON(config.endpointEdit, {
                jenis_hukuman: jenis,
                jumlah: jumlah,
            })
            .then(data => {
                if (data && data.status === true) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Hukuman diterapkan', timer: 1200, showConfirmButton: false });
                    }
                    // Reset form
                    document.getElementById('select-jenis-hukuman').value = '';
                    document.getElementById('input-jumlah-hukuman').value = '1';
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: data.message || '', timer: 1800, showConfirmButton: false });
                    }
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Koneksi gagal', timer: 1500, showConfirmButton: false });
                }
            })
            .finally(() => { locked = false; btn.classList.remove('is-loading'); });
        });
    }

    // ─── Toggle Akses Penilaian ───────────────────────────────────────────

    function setupToggleAkses() {
        const btn = document.getElementById('btn-toggle-akses');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (locked) return;
            locked = true;
            btn.classList.add('is-loading');

            postJSON(config.endpointAkses, {})
                .then(data => {
                    if (data && data.status === true) {
                        config.akses = data.akses_penilaian;
                        updateAksesUI(data.akses_penilaian);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ icon: 'success', title: data.message || 'Berhasil', timer: 1200, showConfirmButton: false });
                        }
                    }
                })
                .catch(() => {})
                .finally(() => { locked = false; btn.classList.remove('is-loading'); });
        });
    }

    function updateAksesUI(akses) {
        const btn = document.getElementById('btn-toggle-akses');
        const badge = document.getElementById('badge-akses');
        if (btn) {
            const icon = btn.querySelector('i');
            const text = btn.querySelector('span');
            if (akses === 'dibuka') {
                if (icon) icon.className = 'fas fa-lock';
                if (text) text.textContent = 'Tutup Penilaian';
            } else {
                if (icon) icon.className = 'fas fa-lock-open';
                if (text) text.textContent = 'Buka Penilaian';
            }
        }
        if (badge) {
            badge.textContent = akses === 'dibuka' ? 'DIBUKA' : 'DITUTUP';
            badge.className = 'badge ' + (akses === 'dibuka' ? 'bg-success' : 'bg-danger');
        }
    }

    // ─── Diskualifikasi ───────────────────────────────────────────────────

    function setupDiskualifikasi() {
        const btn = document.getElementById('btn-diskualifikasi');
        if (!btn) return;

        btn.addEventListener('click', function () {
            const isDQ = btn.querySelector('span').textContent.includes('Batalkan');
            const endpoint = isDQ ? config.endpointUndq : config.endpointDq;
            const confirmText = isDQ ? 'Batalkan diskualifikasi?' : 'Diskualifikasi peserta ini?';

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: confirmText,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#c62828',
                    cancelButtonText: 'Batal',
                    confirmButtonText: isDQ ? 'Ya, Batalkan' : 'Ya, Diskualifikasi',
                }).then(result => {
                    if (result.isConfirmed) doDiskualifikasi(endpoint, isDQ);
                });
            } else {
                if (confirm(confirmText)) doDiskualifikasi(endpoint, isDQ);
            }
        });
    }

    function doDiskualifikasi(endpoint, isDQ) {
        if (locked) return;
        locked = true;

        postJSON(endpoint, {})
            .then(data => {
                if (data && data.status === true) {
                    const btn = document.getElementById('btn-diskualifikasi');
                    const text = btn ? btn.querySelector('span') : null;
                    if (text) text.textContent = isDQ ? 'Diskualifikasi' : 'Batalkan DQ';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: data.message || 'Berhasil', timer: 1200, showConfirmButton: false });
                    }
                }
            })
            .catch(() => {})
            .finally(() => { locked = false; });
    }

    // ─── Polling ──────────────────────────────────────────────────────────

    function startPolling() {
        setInterval(function () {
            postJSON(config.endpointRefresh, {})
                .then(data => {
                    if (data && data.reload === true) {
                        window.location.reload();
                        return;
                    }
                    if (data && data.status === false) {
                        // Update akses
                        if (data.akses_penilaian && data.akses_penilaian !== config.akses) {
                            config.akses = data.akses_penilaian;
                            updateAksesUI(data.akses_penilaian);
                        }
                        // Update juri ready
                        if (data.juri_ready) updateJuriReady(data.juri_ready);
                    }
                })
                .catch(() => {});
        }, 4000);
    }

    function updateJuriReady(juriList) {
        let readyCount = 0;
        const total = juriList.length;

        juriList.forEach(juri => {
            if (juri.ready) readyCount++;
        });

        const badge = document.getElementById('juri-ready-count');
        if (badge) badge.textContent = readyCount + ' / ' + total + ' Ready';

        // Update median if all ready
        if (readyCount === total && total > 0) {
            const values = juriList.map(j => j.nilai_akhir).filter(v => v > 0).sort((a, b) => a - b);
            if (values.length > 0) {
                const median = calcMedian(values);
                const medianEl = document.getElementById('median-display');
                if (medianEl) medianEl.textContent = median.toFixed(2);
            }
        }
    }

    function calcMedian(sorted) {
        const mid = Math.floor(sorted.length / 2);
        if (sorted.length % 2 === 0) {
            return (sorted[mid - 1] + sorted[mid]) / 2;
        }
        return sorted[mid];
    }

    // ─── Socket.IO ────────────────────────────────────────────────────────

    function initSocket() {
        if (typeof io === 'undefined') return;
        const url = window.SOCKET_URL || 'http://localhost:3000';
        const socket = io(url);

        // Re-join room on initial connect AND every reconnect (parity with kp_tanding.js)
        socket.on('connect', () => {
            socket.emit('JOIN_ROOM', { id_penampilan_seni: config.idPenampilan });
        });

        socket.on('JURI_READY_UPDATE', data => {
            if (data && String(data.id_penampilan_seni) === config.idPenampilan) {
                if (data.juri_ready) updateJuriReady(data.juri_ready);
            }
        });

        socket.on('PENAMPILAN_SELESAI', data => {
            if (data && String(data.id_penampilan_seni) === config.idPenampilan) {
                window.location.reload();
            }
        });
    }

    // ─── Init ─────────────────────────────────────────────────────────────

    setupHukuman();
    setupToggleAkses();
    setupDiskualifikasi();
    startPolling();
    initSocket();

})();
