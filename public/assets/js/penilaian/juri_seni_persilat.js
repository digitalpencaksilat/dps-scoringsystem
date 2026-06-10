/**
 * Juri Seni PERSILAT — modular JS
 * Handles: kebenaran pointer system, subjective input, hukuman sync,
 *          auto-save, ready toggle, polling, offline fallback
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
    };

    let dataNilai = (typeof SENI_DATA !== 'undefined') ? SENI_DATA : null;
    let formatPenilaian = (typeof SENI_FORMAT !== 'undefined') ? SENI_FORMAT : null;
    let isReady = (typeof SENI_READY !== 'undefined') ? !!SENI_READY : false;
    let mode = (typeof SENI_MODE !== 'undefined') ? SENI_MODE : 'sederhana';

    let saveTimeout = null;
    let isSaving = false;
    let pollInterval = null;

    // ─── CSRF ─────────────────────────────────────────────────────────────

    function rotateCsrf(newHash) {
        if (newHash) config.csrfHash = newHash;
    }

    function buildBody(extra) {
        const body = new URLSearchParams();
        body.append(config.csrfName, config.csrfHash);
        if (extra) {
            Object.entries(extra).forEach(([k, v]) => body.append(k, typeof v === 'object' ? JSON.stringify(v) : v));
        }
        return body;
    }

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

    // ─── Format Detection ─────────────────────────────────────────────────

    function hasKebenaran() {
        return formatPenilaian?.penilaian?.unsur_nilai?.kebenaran != null;
    }

    function getUnsurNilai() {
        return formatPenilaian?.penilaian?.unsur_nilai || {};
    }

    function getHukumanFormat() {
        return formatPenilaian?.penilaian?.hukuman || {};
    }

    // ─── Render UI ────────────────────────────────────────────────────────

    function render() {
        renderUnsurNilai();
        renderHukuman();
        renderTotal();
        renderReadyBtn();
    }

    function renderUnsurNilai() {
        const container = document.getElementById('unsur-nilai-container');
        if (!container) return;
        container.innerHTML = '';

        const unsurNilai = getUnsurNilai();

        Object.entries(unsurNilai).forEach(([key, unsur]) => {
            if (key === 'kebenaran') {
                container.appendChild(renderKebenaran(unsur));
            } else {
                container.appendChild(renderSubjectiveUnsur(key, unsur));
            }
        });
    }

    // ─── Kebenaran Rendering ──────────────────────────────────────────────

    function renderKebenaran(formatKebenaran) {
        const section = createElement('div', 'scoring-section kebenaran-section');
        const header = createElement('div', 'section-header');
        header.innerHTML = '<i class="fas fa-bullseye me-2"></i>Kebenaran Jurus';

        const dataKebenaran = dataNilai?.penilaian?.unsur_nilai?.kebenaran || formatKebenaran;
        const jurusData = dataKebenaran.jurus || {};

        const subtotalEl = createElement('span', 'ms-auto unsur-subtotal');
        subtotalEl.id = 'kebenaran-subtotal';
        subtotalEl.textContent = formatNumber(dataKebenaran.nilai_diperoleh || 0);
        header.appendChild(subtotalEl);
        section.appendChild(header);

        Object.entries(jurusData).forEach(([jurusName, jurusInfo]) => {
            const group = createElement('div', 'kebenaran-jurus-group');
            const label = createElement('div', 'jurus-label');
            label.textContent = jurusName.replace(/_/g, ' ');
            group.appendChild(label);

            const grid = createElement('div', 'kebenaran-grid');
            const rangkaianGerak = jurusInfo.rangkaian_gerak || {};

            Object.entries(rangkaianGerak).forEach(([rgKey, rgData]) => {
                const jumlahGerakan = rgData.jumlah_gerakan || 1;
                const kesalahan = rgData.jumlah_kesalahan || 0;

                // In sederhana mode: show 1 cell per rangkaian_gerak with error count
                if (mode === 'sederhana') {
                    const cell = createElement('div', 'nilai-cell');
                    cell.dataset.jurus = jurusName;
                    cell.dataset.rangkaianGerak = rgKey;
                    cell.textContent = kesalahan;
                    cell.classList.add(kesalahan === 0 ? 'perfect' : (kesalahan >= jumlahGerakan ? 'zero' : 'reduced'));
                    cell.title = `${jurusName} #${rgKey}: ${kesalahan} kesalahan / ${jumlahGerakan} gerakan`;
                    cell.addEventListener('click', () => incrementKesalahan(jurusName, rgKey, jumlahGerakan));
                    cell.addEventListener('contextmenu', (e) => {
                        e.preventDefault();
                        decrementKesalahan(jurusName, rgKey);
                    });
                    grid.appendChild(cell);
                } else {
                    // Terperinci: show individual cells per gerakan
                    for (let g = 1; g <= jumlahGerakan; g++) {
                        const cell = createElement('div', 'nilai-cell');
                        cell.dataset.jurus = jurusName;
                        cell.dataset.rangkaianGerak = rgKey;
                        cell.dataset.gerakan = g;
                        const isError = g <= kesalahan;
                        cell.textContent = isError ? '×' : '✓';
                        cell.classList.add(isError ? 'zero' : 'perfect');
                        cell.title = `Gerakan ${g}/${jumlahGerakan}`;
                        cell.addEventListener('click', () => toggleGerakan(jurusName, rgKey, g, jumlahGerakan));
                        grid.appendChild(cell);
                    }
                }
            });

            group.appendChild(grid);
            section.appendChild(group);
        });

        return section;
    }

    // ─── Subjective Unsur Rendering ───────────────────────────────────────

    function renderSubjectiveUnsur(key, formatUnsur) {
        const section = createElement('div', 'scoring-section');
        const header = createElement('div', 'section-header');

        const labelText = formatUnsur.metadata?.label || key.replace(/_/g, ' ');
        header.innerHTML = `<i class="fas fa-sliders me-2"></i>${capitalize(labelText)}`;
        section.appendChild(header);

        const dataUnsur = dataNilai?.penilaian?.unsur_nilai?.[key] || formatUnsur;
        const nilaiMin = formatUnsur.nilai_minimal || 0;
        const nilaiMax = formatUnsur.nilai_maksimal || 10;
        const step = formatUnsur.metadata?.step || 0.01;
        const nilaiDiperoleh = dataUnsur.nilai_diperoleh ?? formatUnsur.nilai_diperoleh ?? 0;

        const row = createElement('div', 'unsur-input-row');

        const inputWrap = createElement('div', 'unsur-input');
        const input = document.createElement('input');
        input.type = 'number';
        input.min = nilaiMin;
        input.max = nilaiMax;
        input.step = step;
        input.value = nilaiDiperoleh;
        input.id = `input-${key}`;
        input.addEventListener('input', () => {
            let val = parseFloat(input.value) || 0;
            val = Math.max(nilaiMin, Math.min(nilaiMax, val));
            updateSubjectiveUnsur(key, val);
        });
        inputWrap.appendChild(input);

        const range = createElement('div', 'unsur-range');
        range.textContent = `${nilaiMin} — ${nilaiMax}`;

        row.appendChild(inputWrap);
        row.appendChild(range);
        section.appendChild(row);

        return section;
    }

    // ─── Hukuman Rendering ────────────────────────────────────────────────

    function renderHukuman() {
        const container = document.getElementById('hukuman-container');
        if (!container) return;
        container.innerHTML = '';

        const dataHukuman = dataNilai?.penilaian?.hukuman || {};
        const formatHukuman = getHukumanFormat();

        if (Object.keys(formatHukuman).length === 0 && Object.keys(dataHukuman).length === 0) {
            container.innerHTML = '<div class="text-muted small">Belum ada hukuman</div>';
            return;
        }

        const hukumanSource = Object.keys(dataHukuman).length > 0 ? dataHukuman : formatHukuman;

        Object.entries(hukumanSource).forEach(([key, hukuman]) => {
            const row = createElement('div', 'hukuman-row');
            const label = createElement('span', 'hukuman-label');
            label.textContent = key.replace(/_/g, ' ');

            const value = createElement('span', 'hukuman-value');
            let nilaiHukuman = 0;

            if (hukuman.detail_hukuman) {
                nilaiHukuman = parseFloat(hukuman.detail_hukuman.nilai_hukuman || 0);
            } else if (hukuman.nilai_hukuman !== undefined) {
                nilaiHukuman = parseFloat(hukuman.nilai_hukuman || 0);
            }

            value.textContent = nilaiHukuman > 0 ? `-${nilaiHukuman}` : '0';
            value.classList.add(nilaiHukuman > 0 ? '' : 'zero');

            row.appendChild(label);
            row.appendChild(value);
            container.appendChild(row);
        });
    }

    // ─── Score Calculation ────────────────────────────────────────────────

    function calculateTotal() {
        if (!dataNilai || !dataNilai.penilaian) return 0;

        const ringkasan = dataNilai.penilaian.ringkasan;
        if (!ringkasan) return 0;

        return parseFloat(ringkasan.total_nilai || 0);
    }

    function recalculateRingkasan() {
        if (!dataNilai || !dataNilai.penilaian) return;

        const unsurNilai = dataNilai.penilaian.unsur_nilai || {};
        let totalUnsur = 0;

        Object.entries(unsurNilai).forEach(([key, unsur]) => {
            totalUnsur += parseFloat(unsur.nilai_diperoleh || 0);
        });

        let totalHukuman = 0;
        const hukuman = dataNilai.penilaian.hukuman || {};
        Object.values(hukuman).forEach(h => {
            if (h.detail_hukuman) {
                totalHukuman += parseFloat(h.detail_hukuman.nilai_hukuman || 0);
            }
        });

        dataNilai.penilaian.ringkasan = dataNilai.penilaian.ringkasan || {};
        dataNilai.penilaian.ringkasan.total_unsur_nilai = round(totalUnsur, 4);
        dataNilai.penilaian.ringkasan.total_hukuman = round(totalHukuman, 4);
        dataNilai.penilaian.ringkasan.total_nilai = round(totalUnsur - totalHukuman, 4);
    }

    function renderTotal() {
        recalculateRingkasan();
        const totalEl = document.getElementById('total-nilai');
        if (totalEl) {
            totalEl.textContent = formatNumber(calculateTotal());
        }
    }

    // ─── Data Mutation ────────────────────────────────────────────────────

    function incrementKesalahan(jurusName, rgKey, maxGerakan) {
        if (config.akses === 'ditutup') return;
        const rg = dataNilai.penilaian.unsur_nilai.kebenaran.jurus[jurusName].rangkaian_gerak[rgKey];
        if (rg.jumlah_kesalahan < maxGerakan) {
            rg.jumlah_kesalahan++;
            recalcKebenaran();
            render();
            debounceSave();
        }
    }

    function decrementKesalahan(jurusName, rgKey) {
        if (config.akses === 'ditutup') return;
        const rg = dataNilai.penilaian.unsur_nilai.kebenaran.jurus[jurusName].rangkaian_gerak[rgKey];
        if (rg.jumlah_kesalahan > 0) {
            rg.jumlah_kesalahan--;
            recalcKebenaran();
            render();
            debounceSave();
        }
    }

    function toggleGerakan(jurusName, rgKey, gerakanNum, maxGerakan) {
        if (config.akses === 'ditutup') return;
        const rg = dataNilai.penilaian.unsur_nilai.kebenaran.jurus[jurusName].rangkaian_gerak[rgKey];
        // Toggle: if gerakanNum <= current errors, remove error; else add
        if (gerakanNum <= rg.jumlah_kesalahan) {
            rg.jumlah_kesalahan = gerakanNum - 1;
        } else {
            rg.jumlah_kesalahan = gerakanNum;
        }
        rg.jumlah_kesalahan = Math.max(0, Math.min(maxGerakan, rg.jumlah_kesalahan));
        recalcKebenaran();
        render();
        debounceSave();
    }

    function recalcKebenaran() {
        const kebenaran = dataNilai.penilaian.unsur_nilai.kebenaran;
        if (!kebenaran || !kebenaran.jurus) return;

        const step = kebenaran.metadata?.step || formatPenilaian?.penilaian?.unsur_nilai?.kebenaran?.metadata?.step || 0.01;
        let totalKesalahan = 0;
        let totalGerakan = 0;

        Object.values(kebenaran.jurus).forEach(jurus => {
            const rangkaianGerak = jurus.rangkaian_gerak || {};
            Object.values(rangkaianGerak).forEach(rg => {
                totalKesalahan += rg.jumlah_kesalahan || 0;
                totalGerakan += rg.jumlah_gerakan || 0;
                // Update nilai_diperoleh per rangkaian
                rg.nilai_diperoleh = round((rg.jumlah_gerakan - rg.jumlah_kesalahan) * step, 4);
                rg.nilai_maksimal = round(rg.jumlah_gerakan * step, 4);
            });
        });

        kebenaran.total_kesalahan_gerak = totalKesalahan;
        kebenaran.total_gerakan = totalGerakan;
        kebenaran.nilai_diperoleh = round((totalGerakan - totalKesalahan) * step, 4);
        kebenaran.nilai_maksimal = round(totalGerakan * step, 4);
    }

    function updateSubjectiveUnsur(key, value) {
        if (config.akses === 'ditutup') return;
        if (!dataNilai.penilaian.unsur_nilai[key]) {
            dataNilai.penilaian.unsur_nilai[key] = {};
        }
        dataNilai.penilaian.unsur_nilai[key].nilai_diperoleh = round(value, 4);
        renderTotal();
        debounceSave();
    }

    // ─── Save (debounced) ─────────────────────────────────────────────────

    function debounceSave() {
        if (saveTimeout) clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => save(), 800);
    }

    function save() {
        if (isSaving || config.akses === 'ditutup') return;
        isSaving = true;

        const btnSimpan = document.getElementById('btn-simpan');
        if (btnSimpan) btnSimpan.classList.add('saving');

        const nilaiAkhir = calculateTotal();

        // Save to localStorage as fallback
        try {
            localStorage.setItem(`seni_backup_${config.idPenampilan}`, JSON.stringify(dataNilai));
        } catch (e) { /* ignore */ }

        postJSON(config.endpointEdit, {
            data_nilai: JSON.stringify(dataNilai),
            nilai_akhir_per_juri: nilaiAkhir.toString(),
        })
        .then(data => {
            if (data && data.status === true) {
                // Clear backup on success
                try { localStorage.removeItem(`seni_backup_${config.idPenampilan}`); } catch (e) {}
            } else {
                console.warn('Save gagal:', data?.message);
            }
        })
        .catch(err => {
            console.warn('Network error, data saved locally:', err);
        })
        .finally(() => {
            isSaving = false;
            if (btnSimpan) btnSimpan.classList.remove('saving');
        });
    }

    // ─── Ready Toggle ─────────────────────────────────────────────────────

    function renderReadyBtn() {
        const btn = document.getElementById('btn-ready');
        const text = document.getElementById('ready-text');
        if (!btn || !text) return;

        if (isReady) {
            btn.classList.add('is-ready');
            text.textContent = 'READY ✓';
        } else {
            btn.classList.remove('is-ready');
            text.textContent = 'READY';
        }
    }

    function toggleReady() {
        postJSON(config.endpointToggleReady, {})
            .then(data => {
                if (data && data.status === true) {
                    isReady = data.ready;
                    renderReadyBtn();
                }
            })
            .catch(() => {});
    }

    // ─── Polling (hukuman sync + status check) ────────────────────────────

    function startPolling() {
        pollInterval = setInterval(() => {
            postJSON(config.endpointRefresh, {})
                .then(data => {
                    if (data && data.reload === true) {
                        window.location.reload();
                        return;
                    }
                    if (data && data.status === false) {
                        // Update hukuman from server (KP authoritative)
                        if (data.hukuman) {
                            dataNilai.penilaian.hukuman = data.hukuman;
                            renderHukuman();
                            renderTotal();
                        }
                        // Check akses
                        if (data.akses_penilaian === 'ditutup' && config.akses !== 'ditutup') {
                            config.akses = 'ditutup';
                            document.getElementById('scoring-body')?.classList.add('is-locked');
                        }
                    }
                })
                .catch(() => {});
        }, 3000);
    }

    // ─── Manual Save Button ───────────────────────────────────────────────

    document.getElementById('btn-simpan')?.addEventListener('click', () => {
        if (saveTimeout) clearTimeout(saveTimeout);
        save();
    });

    document.getElementById('btn-ready')?.addEventListener('click', () => {
        // Save before toggle ready
        save();
        setTimeout(toggleReady, 300);
    });

    // ─── Offline Recovery ─────────────────────────────────────────────────

    function tryRecoverFromLocal() {
        try {
            const backup = localStorage.getItem(`seni_backup_${config.idPenampilan}`);
            if (backup && !dataNilai) {
                dataNilai = JSON.parse(backup);
                console.info('Recovered seni data from localStorage');
            }
        } catch (e) { /* ignore */ }
    }

    // ─── Utilities ────────────────────────────────────────────────────────

    function createElement(tag, className) {
        const el = document.createElement(tag);
        if (className) el.className = className;
        return el;
    }

    function formatNumber(num) {
        return parseFloat(num).toFixed(2);
    }

    function round(num, decimals) {
        return Math.round(num * Math.pow(10, decimals)) / Math.pow(10, decimals);
    }

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // ─── Init ─────────────────────────────────────────────────────────────

    if (!dataNilai && formatPenilaian) {
        // Use format as initial data structure
        dataNilai = JSON.parse(JSON.stringify(formatPenilaian));
    }

    tryRecoverFromLocal();
    render();
    startPolling();

})();
