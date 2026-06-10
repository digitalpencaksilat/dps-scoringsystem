/**
 * Juri Seni PERSILAT — modular JS
 * Parity legacy: juri/seni/persilat.js (v72)
 * Handles: unsur nilai rendering, kebenaran pointer, ready toggle, wrong move,
 *          auto-save (debounced), offline localStorage fallback, socket.io
 */
(function () {
    'use strict';

    const wrapper = document.getElementById('juri-seni-wrapper');
    if (!wrapper) return;

    const config = {
        idPenampilan: wrapper.dataset.idPenampilan,
        endpointEdit: wrapper.dataset.endpointEdit,
        endpointRefresh: wrapper.dataset.endpointRefresh,
        endpointToggleReady: wrapper.dataset.endpointToggleReady,
        csrfName: wrapper.dataset.csrfName,
        csrfHash: wrapper.dataset.csrfHash,
        akses: wrapper.dataset.akses,
        jenisSeni: wrapper.dataset.jenisSeni || 'tunggal',
        rangeMin: parseFloat(wrapper.dataset.rangeMin || '0.00'),
        rangeMax: parseFloat(wrapper.dataset.rangeMax || '0.10'),
    };

    const format = (typeof SENI_FORMAT !== 'undefined') ? SENI_FORMAT : null;
    const mode = (typeof SENI_MODE !== 'undefined') ? SENI_MODE : 'sederhana';
    let dataNilai = (typeof SENI_DATA !== 'undefined') ? SENI_DATA : {};
    let isReady = (typeof SENI_READY !== 'undefined') ? !!SENI_READY : false;
    let saveTimer = null;
    let locked = false;
    let pollInterval = null;

    // ─── CSRF ─────────────────────────────────────────────────────────────

    function rotateCsrf(newHash) {
        if (newHash) config.csrfHash = newHash;
    }

    function buildBody(extra) {
        const body = new URLSearchParams();
        body.append(config.csrfName, config.csrfHash);
        if (extra) Object.entries(extra).forEach(([k, v]) => body.append(k, v));
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
            rotateCsrf(data.csrf_hash || (data.response && data.response.csrf_hash));
            setOnline(true);
            return data;
        }).catch(err => {
            setOnline(false);
            throw err;
        });
    }

    // ─── Online/Offline Indicator ─────────────────────────────────────────

    function setOnline(online) {
        const el = document.getElementById('online-indicator');
        if (!el) return;
        if (online) {
            el.classList.remove('offline');
        } else {
            el.classList.add('offline');
            el.querySelector('.online-text').textContent = 'Offline';
        }
    }

    // ─── Score Calculation ────────────────────────────────────────────────

    function hitungTotal() {
        if (!dataNilai || !dataNilai.unsur_nilai) return 0;
        let total = 0;
        Object.values(dataNilai.unsur_nilai).forEach(v => {
            total += parseFloat(v) || 0;
        });
        // Subtract kebenaran potongan
        if (dataNilai.kebenaran_potongan) {
            total -= parseFloat(dataNilai.kebenaran_potongan) || 0;
        }
        // Subtract hukuman
        if (dataNilai.total_hukuman) {
            total -= parseFloat(dataNilai.total_hukuman) || 0;
        }
        return Math.max(0, total);
    }

    function hitungKebenaranTotal() {
        const max = parseFloat(dataNilai.kebenaran_max || 10);
        const pot = parseFloat(dataNilai.kebenaran_potongan || 0);
        return Math.max(0, max - pot);
    }

    function renderTotalNilai() {
        const el = document.getElementById('total-nilai');
        if (el) el.textContent = hitungTotal().toFixed(2);
    }

    function renderKebenaran() {
        const elMax = document.getElementById('kebenaran-max');
        const elPot = document.getElementById('kebenaran-potongan');
        const elTotal = document.getElementById('kebenaran-total');
        if (elMax) elMax.textContent = parseFloat(dataNilai.kebenaran_max || 10).toFixed(2);
        if (elPot) elPot.textContent = parseFloat(dataNilai.kebenaran_potongan || 0).toFixed(2);
        if (elTotal) elTotal.textContent = hitungKebenaranTotal().toFixed(2);
    }

    // ─── Render Unsur Nilai (Sederhana Mode) ──────────────────────────────

    function renderUnsurNilai() {
        const container = document.getElementById('unsur-nilai-container');
        if (!container || !format || !format.unsur_nilai) return;

        container.innerHTML = '';
        format.unsur_nilai.forEach((unsur, idx) => {
            const key = unsur.key || unsur.id || ('unsur_' + idx);
            const currentVal = (dataNilai.unsur_nilai && dataNilai.unsur_nilai[key]) || 0;

            const row = document.createElement('div');
            row.className = 'unsur-row';
            row.innerHTML = `
                <span class="unsur-label">${escHtml(unsur.nama || unsur.label || key)}</span>
                <span class="unsur-value-display" id="unsur-val-${key}">${parseFloat(currentVal).toFixed(2)}</span>
                <div class="unsur-btn-group">
                    <button type="button" class="unsur-btn unsur-btn-minus" data-key="${key}" data-dir="-1">−</button>
                    <button type="button" class="unsur-btn unsur-btn-plus" data-key="${key}" data-dir="1">+</button>
                </div>
            `;
            container.appendChild(row);
        });

        // Bind events
        container.querySelectorAll('.unsur-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const key = this.dataset.key;
                const dir = parseInt(this.dataset.dir, 10);
                adjustUnsur(key, dir);
            });
        });
    }

    function adjustUnsur(key, direction) {
        if (!dataNilai.unsur_nilai) dataNilai.unsur_nilai = {};
        let val = parseFloat(dataNilai.unsur_nilai[key] || 0);
        const step = 0.01;

        val += direction * step;
        // Clamp
        if (val < config.rangeMin) val = config.rangeMin;
        if (val > config.rangeMax) val = config.rangeMax;

        dataNilai.unsur_nilai[key] = val.toFixed(2);

        // Update display
        const el = document.getElementById('unsur-val-' + key);
        if (el) el.textContent = val.toFixed(2);

        renderTotalNilai();
        debouncedSave();
    }

    // ─── Render Gerakan (Terperinci Mode) ─────────────────────────────────

    function renderGerakan() {
        const container = document.getElementById('gerakan-container');
        if (!container || !format || !format.gerakan) return;

        container.innerHTML = '';
        format.gerakan.forEach((ger, idx) => {
            const key = ger.key || ger.id || ('ger_' + idx);
            const isWrong = dataNilai.gerakan_salah && dataNilai.gerakan_salah[key];
            const deduction = isWrong ? (parseFloat(ger.deduction || config.rangeMax) || 0) : 0;

            const row = document.createElement('div');
            row.className = 'gerakan-row' + (isWrong ? ' is-wrong' : '');
            row.dataset.key = key;
            row.innerHTML = `
                <span class="gerakan-num">${idx + 1}</span>
                <span class="gerakan-label">${escHtml(ger.nama || ger.label || ('Gerakan ' + (idx + 1)))}</span>
                <span class="gerakan-value">${isWrong ? '-' + deduction.toFixed(2) : '✓'}</span>
                <div class="gerakan-actions">
                    <button type="button" class="gerakan-btn ${isWrong ? 'gerakan-btn-correct' : 'gerakan-btn-wrong'}"
                            data-key="${key}" data-deduction="${ger.deduction || config.rangeMax}">
                        <i class="fas ${isWrong ? 'fa-undo' : 'fa-xmark'}"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
        });

        // Bind events
        container.querySelectorAll('.gerakan-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                toggleGerakan(this.dataset.key, parseFloat(this.dataset.deduction));
            });
        });
    }

    function toggleGerakan(key, deduction) {
        if (!dataNilai.gerakan_salah) dataNilai.gerakan_salah = {};

        if (dataNilai.gerakan_salah[key]) {
            delete dataNilai.gerakan_salah[key];
        } else {
            dataNilai.gerakan_salah[key] = deduction;
        }

        // Recalculate kebenaran_potongan
        let totalPotongan = 0;
        Object.values(dataNilai.gerakan_salah).forEach(d => {
            totalPotongan += parseFloat(d) || 0;
        });
        dataNilai.kebenaran_potongan = totalPotongan.toFixed(2);

        renderGerakan();
        renderKebenaran();
        renderTotalNilai();
        debouncedSave();
    }

    // ─── Auto-Save (Debounced 800ms) ──────────────────────────────────────

    function debouncedSave() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(doSave, 800);
    }

    function doSave() {
        if (locked) return;
        locked = true;

        const payload = JSON.stringify(dataNilai);

        // Save to localStorage as fallback
        try {
            localStorage.setItem('seni_backup_' + config.idPenampilan, payload);
        } catch (e) { /* ignore */ }

        postJSON(config.endpointEdit, { data_nilai: payload })
            .then(data => {
                if (data && data.status === true) {
                    // Success — clear backup
                    try { localStorage.removeItem('seni_backup_' + config.idPenampilan); } catch (e) {}
                }
            })
            .catch(() => {
                // Offline — data in localStorage
            })
            .finally(() => { locked = false; });
    }

    // ─── Ready Toggle ─────────────────────────────────────────────────────

    function setupReadyBtn() {
        const btn = document.getElementById('btn-ready');
        if (!btn) return;

        updateReadyUI();
        btn.addEventListener('click', () => {
            postJSON(config.endpointToggleReady, { ready: isReady ? '0' : '1' })
                .then(data => {
                    if (data && data.status === true) {
                        isReady = !isReady;
                        updateReadyUI();
                    }
                })
                .catch(() => {});
        });
    }

    function updateReadyUI() {
        const btn = document.getElementById('btn-ready');
        const text = document.getElementById('ready-text');
        if (!btn) return;
        if (isReady) {
            btn.classList.add('is-ready');
            if (text) text.textContent = 'NOT READY';
        } else {
            btn.classList.remove('is-ready');
            if (text) text.textContent = 'READY';
        }
    }

    // ─── Wrong Move Button (global — adds to current pointer) ─────────────

    function setupWrongMove() {
        const btn = document.getElementById('btn-wrong-move');
        if (!btn) return;

        btn.addEventListener('click', () => {
            if (mode === 'terperinci') {
                // In terperinci, wrong move marks the next unmarked gerakan
                if (!format || !format.gerakan) return;
                for (let i = 0; i < format.gerakan.length; i++) {
                    const key = format.gerakan[i].key || format.gerakan[i].id || ('ger_' + i);
                    if (!dataNilai.gerakan_salah || !dataNilai.gerakan_salah[key]) {
                        toggleGerakan(key, parseFloat(format.gerakan[i].deduction || config.rangeMax));
                        break;
                    }
                }
            } else {
                // In sederhana, increment kebenaran_potongan by step
                let pot = parseFloat(dataNilai.kebenaran_potongan || 0);
                pot += parseFloat(config.rangeMax) || 0.10;
                dataNilai.kebenaran_potongan = pot.toFixed(2);
                renderKebenaran();
                renderTotalNilai();
                debouncedSave();
            }
        });
    }

    // ─── Next Move Set (terperinci only) ──────────────────────────────────

    function setupNextMove() {
        const btn = document.getElementById('btn-next-move');
        if (!btn) return;

        btn.addEventListener('click', () => {
            // Scroll to next unscored gerakan
            const container = document.getElementById('gerakan-container');
            if (!container) return;
            const rows = container.querySelectorAll('.gerakan-row:not(.is-wrong)');
            if (rows.length > 0) {
                rows[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                rows[0].style.boxShadow = '0 0 0 2px #ffc107';
                setTimeout(() => { rows[0].style.boxShadow = ''; }, 1500);
            }
        });
    }

    // ─── Hukuman Rendering (from KP, read-only) ───────────────────────────

    function renderHukuman() {
        const container = document.getElementById('hukuman-container');
        if (!container || !dataNilai.hukuman) {
            if (container) container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        let html = '<div class="d-flex align-items-center gap-2 mb-1"><i class="fas fa-triangle-exclamation text-warning"></i><small class="text-white fw-bold">Hukuman (dari KP)</small></div>';

        if (Array.isArray(dataNilai.hukuman)) {
            dataNilai.hukuman.forEach(h => {
                html += `<div class="hukuman-row"><span class="hukuman-label">${escHtml(h.nama || h.label || 'Hukuman')}</span><span class="hukuman-value">-${parseFloat(h.nilai || 0).toFixed(2)}</span></div>`;
            });
        }

        const totalH = parseFloat(dataNilai.total_hukuman || 0);
        if (totalH > 0) {
            html += `<div class="hukuman-row border-top border-secondary pt-1 mt-1"><span class="hukuman-label fw-bold">Total Hukuman</span><span class="hukuman-value">-${totalH.toFixed(2)}</span></div>`;
        }

        container.innerHTML = html;
    }

    // ─── Polling ──────────────────────────────────────────────────────────

    function startPolling() {
        pollInterval = setInterval(() => {
            postJSON(config.endpointRefresh, {})
                .then(data => {
                    if (data && data.reload === true) {
                        window.location.reload();
                    }
                    if (data && data.hukuman) {
                        dataNilai.hukuman = data.hukuman;
                        dataNilai.total_hukuman = data.total_hukuman || 0;
                        renderHukuman();
                        renderTotalNilai();
                    }
                    if (data && data.akses) {
                        config.akses = data.akses;
                        handleAksesChange(data.akses);
                    }
                })
                .catch(() => {});
        }, 5000);
    }

    function handleAksesChange(akses) {
        const actionBar = document.querySelector('.seni-action-bar');
        const overlay = document.querySelector('.locked-overlay');
        if (akses === 'ditutup') {
            if (actionBar) actionBar.classList.add('is-locked');
            if (!overlay) {
                const ov = document.createElement('div');
                ov.className = 'locked-overlay';
                ov.innerHTML = '<div class="locked-message"><i class="fas fa-lock fa-3x mb-3"></i><p class="fs-5">Penilaian Ditutup</p></div>';
                wrapper.appendChild(ov);
            }
        } else {
            if (actionBar) actionBar.classList.remove('is-locked');
            if (overlay) overlay.remove();
        }
    }

    // ─── Socket.IO Integration ────────────────────────────────────────────

    function initSocket() {
        if (typeof io === 'undefined') return;
        const url = window.SOCKET_URL || 'http://localhost:3000';
        const socket = io(url);

        socket.emit('JOIN_ROOM', { id_penampilan_seni: config.idPenampilan });

        socket.on('HUKUMAN_UPDATE', data => {
            if (data && String(data.id_penampilan_seni) === config.idPenampilan) {
                dataNilai.hukuman = data.hukuman;
                dataNilai.total_hukuman = data.total_hukuman || 0;
                renderHukuman();
                renderTotalNilai();
            }
        });

        socket.on('AKSES_PENILAIAN', data => {
            if (data && String(data.id_penampilan_seni) === config.idPenampilan) {
                handleAksesChange(data.akses);
            }
        });

        socket.on('PENAMPILAN_SELESAI', data => {
            if (data && String(data.id_penampilan_seni) === config.idPenampilan) {
                window.location.reload();
            }
        });
    }

    // ─── Offline Recovery ─────────────────────────────────────────────────

    function checkOfflineBackup() {
        try {
            const backup = localStorage.getItem('seni_backup_' + config.idPenampilan);
            if (backup) {
                const parsed = JSON.parse(backup);
                // If server data is empty but we have local, restore
                if ((!dataNilai.unsur_nilai || Object.keys(dataNilai.unsur_nilai).length === 0) && parsed.unsur_nilai) {
                    dataNilai = parsed;
                    doSave(); // Try to sync back
                }
            }
        } catch (e) { /* ignore */ }
    }

    // ─── Utility ──────────────────────────────────────────────────────────

    function escHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ─── Init ─────────────────────────────────────────────────────────────

    function init() {
        checkOfflineBackup();

        if (mode === 'sederhana') {
            renderUnsurNilai();
        } else if (mode === 'terperinci') {
            renderGerakan();
            renderKebenaran();
            setupNextMove();
        }

        renderHukuman();
        renderTotalNilai();
        setupReadyBtn();
        setupWrongMove();
        startPolling();
        initSocket();
    }

    init();

})();
