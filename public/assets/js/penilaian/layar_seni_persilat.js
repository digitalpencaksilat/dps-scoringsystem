/**
 * Layar Seni PERSILAT — Full Parity Legacy
 * Parity: /dps/assets/penilaian/js/application/layar/seni/persilat.js
 *       + inline ui object from dark.php
 *
 * Handles: Socket.IO timer sync, HTTP polling, juri score display,
 *   median/std_dev/penalty calculations from catatan_nilai_sama,
 *   sorted juri columns, terpilih/tidak highlighting.
 */

const layar = {
    penampilan_seni_berlangsung: null,
    data_nilai: null,
    id_penampilan_seni: null,
    stopwatch: null,
    socket: null,
    format_penilaian: null,
    interval_refresh: 1000,

    init: function ($penampilan_seni_berlangsung, $data_nilai) {
        layar.set_variable($penampilan_seni_berlangsung, $data_nilai);

        // Socket.IO
        if (typeof io !== 'undefined' && typeof SOCKET_URL !== 'undefined') {
            layar.socket = io(SOCKET_URL, { reconnection: true, reconnectionDelay: 1000 });

            layar.socket.emit('JOIN_ROOM', layar.id_penampilan_seni);

            layar.socket.on('UPDATE_WAKTU', function (data) {
                var waktu = data.waktu;
                var seconds = Math.floor(waktu / 1000);

                if (data.action === 'sedang_tampil') {
                    layar.stopwatch.timer("remove");
                    if (seconds > 0) {
                        layar.stopwatch.timer({
                            format: "%M:%S",
                            seconds: 0,
                            duration: seconds,
                            countdown: false,
                            action: 'start'
                        });
                    }
                } else {
                    layar.stopwatch.timer("remove");
                    if (seconds > 0) {
                        layar.stopwatch.timer({
                            format: "%M:%S",
                            seconds: seconds,
                            countdown: false
                        });
                    }
                    layar.stopwatch.timer("pause");
                }
            });

            // FIX #3: Auto-redirect ke hasil page saat penampilan selesai
            layar.socket.on('PENAMPILAN_SELESAI', function (data) {
                if (data && String(data.id_penampilan_seni) === String(layar.id_penampilan_seni)) {
                    // Layar perlu reload supaya status_penampilan terbaca dari DB
                    setTimeout(function () { window.location.reload(); }, 500);
                }
            });

            layar.socket.on('SENI_SELESAI', function (data) {
                if (data && String(data.id_penampilan_seni) === String(layar.id_penampilan_seni)) {
                    window.location = '/layar/standby';
                }
            });

            // FIX #6: Live nilai update saat juri/KP edit penilaian
            layar.socket.on('UPDATE_NILAI_SENI', function (data) {
                if (data && String(data.id_penampilan_seni) === String(layar.id_penampilan_seni)) {
                    layar.refresh_status_seni();
                }
            });

            // Hukuman update dari KP (juri yang lihat angka berubah)
            layar.socket.on('HUKUMAN_UPDATE', function (data) {
                if (data && String(data.id_penampilan_seni) === String(layar.id_penampilan_seni)) {
                    layar.refresh_status_seni();
                }
            });

            // Akses penilaian dibuka/ditutup oleh KP
            layar.socket.on('AKSES_PENILAIAN', function (data) {
                if (data && String(data.id_penampilan_seni) === String(layar.id_penampilan_seni)) {
                    layar.refresh_status_seni();
                }
            });

            layar.socket.on('SENI_AKSES_DITUTUP', function (data) {
                if (data && String(data.id_penampilan_seni) === String(layar.id_penampilan_seni)) {
                    layar.refresh_status_seni();
                }
            });

            // Room reset (mis. ganti partai / batalkan)
            layar.socket.on('ROOM_RESET', function (data) {
                window.location = '/layar/standby';
            });
        }

        ui.update_tampilan_nilai();
        layar.update_timer();
        layar.refresh_status_seni();
    },

    set_variable: function ($penampilan_seni_berlangsung, $data_nilai) {
        layar.penampilan_seni_berlangsung = $penampilan_seni_berlangsung;
        layar.data_nilai = $data_nilai;
        layar.id_penampilan_seni = $penampilan_seni_berlangsung.id_penampilan_seni;
        layar.format_penilaian = $penampilan_seni_berlangsung.format_penilaian || null;
        layar.stopwatch = $(".waktu_tampil");
    },

    update_timer: function () {
        var waktuTampil = parseInt(layar.penampilan_seni_berlangsung.waktu_tampil) || 0;

        if (waktuTampil <= 0) {
            layar.stopwatch.html("00:00");
            return;
        }

        if (layar.penampilan_seni_berlangsung.status_penampilan === 'sedang_tampil') {
            var state = layar.stopwatch.data("state");
            if (state !== "running") {
                layar.stopwatch.timer("remove");
                layar.stopwatch.timer({
                    format: "%M:%S",
                    seconds: 0,
                    duration: waktuTampil,
                    countdown: false,
                    action: 'start'
                });
            }
        } else {
            layar.stopwatch.timer("remove");
            if (waktuTampil > 0) {
                layar.stopwatch.timer({
                    format: "%M:%S",
                    seconds: waktuTampil,
                    countdown: false
                });
                layar.stopwatch.timer("pause");
            }
        }
    },

    refresh_status_seni: function () {
        $.post(
            BASE_URL + "layar/refresh-status-seni/" + layar.id_penampilan_seni,
            function (data) {
                if (data.csrf_hash) { CSRF_HASH = data.csrf_hash; }

                if (data.status === true) {
                    if (data.reload === true) {
                        window.location.reload();
                    } else if (data.hasil_pool_seni === true) {
                        window.location.href = BASE_URL + "layar/hasil-pool-seni/" + data.id_kompetisi_seni;
                    } else if (data.hasil_battle_seni === true) {
                        window.location.href = BASE_URL + "layar/hasil-battle-seni/" + data.id_battle_seni;
                    }
                    return;
                }

                if (data.status === false && data.penampilan_seni_berlangsung) {
                    // Check if format_penilaian changed → reload
                    if (layar.format_penilaian !== null &&
                        data.penampilan_seni_berlangsung.format_penilaian !== layar.format_penilaian) {
                        window.location.reload();
                        return;
                    }

                    // Check if juri count changed → reload
                    var idP = data.penampilan_seni_berlangsung.id_penampilan_seni;
                    var oldCount = layar.data_nilai[layar.id_penampilan_seni]
                        ? layar.data_nilai[layar.id_penampilan_seni].length : 0;
                    var newCount = data.data_nilai[idP] ? data.data_nilai[idP].length : 0;
                    if (oldCount > 0 && newCount > 0 && oldCount !== newCount) {
                        window.location.reload();
                        return;
                    }

                    layar.set_variable(data.penampilan_seni_berlangsung, data.data_nilai);
                    ui.update_tampilan_nilai();

                    // Only update timer if no socket connected
                    if (!layar.socket || !layar.socket.connected) {
                        layar.update_timer();
                    }
                }
            },
            "json"
        ).always(function () {
            setTimeout(function () {
                layar.refresh_status_seni();
            }, layar.interval_refresh);
        });
    }
};

// ═══════════════════════════════════════════════════════════════════════════
//  UI Object — display logic
// ═══════════════════════════════════════════════════════════════════════════
const ui = {
    start_animation: function () {
        var delay = 300;
        var elements = document.querySelectorAll('.fade-down, .fade-up, .fade-left, .fade-right');
        elements.forEach(function (el, index) {
            setTimeout(function () {
                el.classList.add('show');
            }, delay + (index * 200));
        });
    },

    update_tampilan_nilai: function () {
        ui.update_tampilan_urutan_nilai_tiap_juri();
        ui.update_summary();
        ui.update_nilai_akhir();
    },

    /**
     * Parse penilaian JSON per juri, extract total_nilai, sort ascending,
     * and display in juri columns. Mark terpilih/tidak.
     * 
     * Parity legacy: layar/seni/persilat.js update_tampilan_urutan_nilai_tiap_juri()
     * Logic:
     * - Sort juri by total_nilai ascending (lowest to highest)
     * - Display columns in sorted order (column 1 = lowest score, last = highest)
     * - Mark terpilih juri with special styling (those used for median calculation)
     */
    update_tampilan_urutan_nilai_tiap_juri: function () {
        var idP = layar.id_penampilan_seni;
        var dataNilaiArr = layar.data_nilai[idP];

        if (!dataNilaiArr || dataNilaiArr.length === 0) {
            return;
        }

        // Parse scores from each juri
        var juriScores = [];
        for (var idx = 0; idx < dataNilaiArr.length; idx++) {
            var juriObj = dataNilaiArr[idx];
            var penilaian = juriObj.penilaian;
            var parsed = null;

            if (typeof penilaian === 'string') {
                try { parsed = JSON.parse(penilaian); } catch (e) { parsed = null; }
            } else {
                parsed = penilaian;
            }

            var totalNilai = 0;
            if (parsed && parsed.penilaian && parsed.penilaian.ringkasan) {
                totalNilai = parseFloat(parsed.penilaian.ringkasan.total_nilai) || 0;
            }

            juriScores.push({
                index: idx,
                id_perangkat: juriObj.id_perangkat_pertandingan,
                total_nilai: totalNilai,
                terpilih: juriObj.terpilih
            });
        }

        // Sort by total_nilai ascending (for display order parity)
        var sorted = juriScores.slice().sort(function (a, b) {
            return a.total_nilai - b.total_nilai;
        });

        // Update DOM columns
        var columns = document.querySelectorAll('.urutan_total_nilai_juri .kolom_total_nilai');

        sorted.forEach(function (juri, displayIdx) {
            if (displayIdx >= columns.length) return;
            var col = columns[displayIdx];
            var nilaiEl = col.querySelector('.nilai-juri');
            var labelEl = col.querySelector('.label-juri');

            if (nilaiEl) {
                nilaiEl.textContent = juri.total_nilai > 0 ? juri.total_nilai.toFixed(3) : '-';
            }
            if (labelEl) {
                labelEl.textContent = 'Juri ' + (juri.index + 1);
            }

            // Terpilih highlighting (parity legacy: bg-gradient-180-warning + text-white for selected)
            col.classList.remove('terpilih', 'tidak-terpilih');

            if (juri.terpilih === 1 || juri.terpilih === '1') {
                col.classList.add('terpilih');
            } else {
                col.classList.add('tidak-terpilih');
            }
        });

        // Hide extra columns if fewer juri
        for (var extra = sorted.length; extra < columns.length; extra++) {
            var colWrapper = columns[extra].closest('.col');
            if (colWrapper) colWrapper.style.display = 'none';
        }
    },

    /**
     * Update summary boxes: median, median_kebenaran, standar_deviasi, hukuman.
     * Reads from penampilan_seni_berlangsung.catatan_nilai_sama (JSON).
     */
    update_summary: function () {
        var catatan = layar.penampilan_seni_berlangsung.catatan_nilai_sama;
        var parsed = null;

        if (catatan) {
            if (typeof catatan === 'string') {
                try { parsed = JSON.parse(catatan); } catch (e) { parsed = null; }
            } else {
                parsed = catatan;
            }
        }

        if (parsed) {
            if (parsed.median_kebenaran !== undefined) {
                $('.median_kebenaran').text(parseFloat(parsed.median_kebenaran).toFixed(3));
            }
            if (parsed.standar_deviasi !== undefined) {
                // Use 6 decimals for std dev (parity legacy uses 10, display uses 6)
                $('.standar_deviasi').text(parseFloat(parsed.standar_deviasi).toFixed(6));
            }
            if (parsed.median !== undefined) {
                $('.median').text(parseFloat(parsed.median).toFixed(3));
            }
            if (parsed.hukuman !== undefined) {
                var hukuman = parseFloat(parsed.hukuman);
                $('.hukuman').text(hukuman > 0 ? '-' + hukuman.toFixed(1) : '0');
            }
        } else {
            // Fallback: calculate from data_nilai if catatan_nilai_sama not yet populated
            ui.calculate_summary_from_data();
        }
    },

    /**
     * Fallback: calculate median/std_dev/hukuman from raw juri data
     * when catatan_nilai_sama is not yet populated.
     */
    calculate_summary_from_data: function () {
        var idP = layar.id_penampilan_seni;
        var dataNilaiArr = layar.data_nilai[idP];
        if (!dataNilaiArr || dataNilaiArr.length === 0) return;

        var totalNilaiArr = [];
        var kebenaranArr = [];
        var totalHukuman = 0;

        for (var idx = 0; idx < dataNilaiArr.length; idx++) {
            var juriObj = dataNilaiArr[idx];
            var penilaian = juriObj.penilaian;
            var parsed = null;

            if (typeof penilaian === 'string') {
                try { parsed = JSON.parse(penilaian); } catch (e) { continue; }
            } else {
                parsed = penilaian;
            }

            if (parsed && parsed.penilaian) {
                var ringkasan = parsed.penilaian.ringkasan || {};
                var unsurNilai = parsed.penilaian.unsur_nilai || {};

                var tn = parseFloat(ringkasan.total_nilai) || 0;
                if (tn > 0) totalNilaiArr.push(tn);

                // Kebenaran (untuk median kebenaran)
                if (unsurNilai.kebenaran !== undefined && unsurNilai.kebenaran.nilai_diperoleh !== undefined) {
                    kebenaranArr.push(parseFloat(unsurNilai.kebenaran.nilai_diperoleh) || 0);
                }

                // Hukuman from ringkasan
                totalHukuman += parseFloat(ringkasan.total_hukuman) || 0;
            }
        }

        // Median
        if (totalNilaiArr.length > 0) {
            var median = ui.calcMedian(totalNilaiArr);
            $('.median').text(median.toFixed(3));
        }

        // Median Kebenaran
        if (kebenaranArr.length > 0) {
            var medianKebenaran = ui.calcMedian(kebenaranArr);
            $('.median_kebenaran').text(medianKebenaran.toFixed(3));
        }

        // Std deviation (6 decimals)
        if (totalNilaiArr.length > 1) {
            var stdDev = ui.calcStdDev(totalNilaiArr);
            $('.standar_deviasi').text(stdDev.toFixed(6));
        }

        // Hukuman
        if (totalHukuman !== 0) {
            $('.hukuman').text(totalHukuman > 0 ? '-' + totalHukuman.toFixed(1) : '0');
        }
    },

    calcMedian: function (arr) {
        var sorted = arr.slice().sort(function (a, b) { return a - b; });
        var mid = Math.floor(sorted.length / 2);
        return sorted.length % 2 !== 0 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2;
    },

    calcStdDev: function (arr) {
        var n = arr.length;
        var mean = arr.reduce(function (a, b) { return a + b; }, 0) / n;
        var variance = arr.reduce(function (sum, val) { return sum + Math.pow(val - mean, 2); }, 0) / n;
        return Math.sqrt(variance);
    },

    update_nilai_akhir: function () {
        var nilaiAkhir = parseFloat(layar.penampilan_seni_berlangsung.nilai_akhir) || 0;
        $('.nilai_akhir').text(nilaiAkhir.toFixed(3));
    }
};
