/**
 * Layar Tanding PERSILAT — Full Parity Legacy
 * Parity: /dps/assets/penilaian/js/application/layar/tanding/persilat.js
 *       + inline ui object from v3.php
 *
 * Handles: Socket.IO timer sync, HTTP polling fallback, score display,
 *   judge action indicators, penalty highlights, verification modals,
 *   stinger round transitions, score change animations.
 */

const layar = {
    interval_refresh: 1000,
    data_nilai: null,
    pertandingan: null,
    id_pertandingan: null,
    ronde_pertandingan: null,
    verifikasi_pertandingan: null,
    waktu_sekarang: 1,
    waktu_per_ronde: null,
    stopwatch: null,
    ringkasan_nilai: null,
    skor_biru_verifikasi: 0,
    skor_merah_verifikasi: 0,
    sistem_dialog_terdahulu: null,
    socket: null,
    modalVerifikasiJatuhan: null,
    modalVerifikasiPelanggaran: null,
    modalHasilVerifikasi: null,

    init: function ($data_nilai, $pertandingan, $verifikasi_pertandingan, $interval_refresh) {
        $interval_refresh = $interval_refresh || 1000;
        layar.setup_modals();

        // Socket.IO
        if (typeof io !== 'undefined' && typeof SOCKET_URL !== 'undefined') {
            layar.socket = io(SOCKET_URL, { reconnection: true, reconnectionDelay: 1000 });
        }

        layar.set_variable($data_nilai, $pertandingan, $verifikasi_pertandingan, $interval_refresh);

        // Socket events
        if (layar.socket) {
            layar.socket.emit('JOIN_ROOM', layar.id_pertandingan);
            layar.socket.on('UPDATE_WAKTU', function (data) {
                layar.waktu_sekarang = data.waktu;
                layar.pertandingan.status_pertandingan = data.action;
                var seconds = Math.floor(layar.waktu_sekarang / 1000);

                if (seconds <= 0) {
                    layar.stopwatch.timer("remove");
                    layar.stopwatch.html("00:00");
                } else {
                    if (data.action == 'berlangsung') {
                        layar.stopwatch.timer("remove");
                        layar.stopwatch.timer({
                            format: "%M:%S",
                            seconds: 0,
                            duration: seconds,
                            countdown: true,
                            action: 'start'
                        });
                    } else {
                        layar.stopwatch.timer("remove");
                        layar.stopwatch.timer({
                            format: "%M:%S",
                            seconds: 0,
                            duration: seconds,
                            countdown: true
                        });
                        layar.stopwatch.timer("pause");
                    }
                }
            });
        }

        ui.update_tampilan_nilai();

        // Fallback timer if no socket
        if (typeof layar.socket === 'undefined' || !layar.socket || !layar.socket.connected) {
            layar.update_timer();
        }

        layar.refresh_status_pertandingan();
    },

    set_variable: function ($data_nilai, $pertandingan, $verifikasi_pertandingan, $interval_refresh) {
        layar.data_nilai = $data_nilai;
        layar.pertandingan = $pertandingan;
        if ($interval_refresh) layar.interval_refresh = $interval_refresh;

        layar.id_pertandingan = $pertandingan.id_pertandingan;
        layar.waktu_per_ronde = $pertandingan.waktu_per_ronde;

        if (layar.ronde_pertandingan !== $pertandingan.ronde_pertandingan) {
            layar.ronde_pertandingan = $pertandingan.ronde_pertandingan;
        }

        if ($pertandingan.ringkasan_nilai !== undefined && $pertandingan.ringkasan_nilai !== null) {
            if (typeof $pertandingan.ringkasan_nilai === 'string') {
                layar.ringkasan_nilai = JSON.parse($pertandingan.ringkasan_nilai);
            } else {
                layar.ringkasan_nilai = $pertandingan.ringkasan_nilai;
            }
        }

        layar.verifikasi_pertandingan = $verifikasi_pertandingan;
        layar.stopwatch = $(".stopwatch");
        layar.ronde_sekarang = $pertandingan.ronde_pertandingan;

        // data_waktu: { "1": [total, remaining], "2": [...], "3": [...] }
        if ($pertandingan.data_waktu && $pertandingan.data_waktu[layar.ronde_sekarang]) {
            layar.waktu_sekarang = $pertandingan.data_waktu[layar.ronde_sekarang][1];
        }
    },

    setup_modals: function () {
        var elJatuhan = document.getElementById("modalVerifikasiJatuhan");
        if (elJatuhan && layar.modalVerifikasiJatuhan === null) {
            layar.modalVerifikasiJatuhan = new bootstrap.Modal(elJatuhan, { keyboard: false });
        }

        var elPelanggaran = document.getElementById("modalVerifikasiPelanggaran");
        if (elPelanggaran && layar.modalVerifikasiPelanggaran === null) {
            layar.modalVerifikasiPelanggaran = new bootstrap.Modal(elPelanggaran, { keyboard: false });
        }

        var elHasil = document.getElementById("modalHasilVerifikasi");
        if (elHasil && layar.modalHasilVerifikasi === null) {
            layar.modalHasilVerifikasi = new bootstrap.Modal(elHasil, { keyboard: false });
        }
    },

    update_timer: function () {
        var seconds = Math.floor(layar.waktu_sekarang / 1000);

        if (seconds <= 0) {
            layar.stopwatch.timer("remove");
            layar.stopwatch.html("00:00");
            return;
        }

        if (layar.pertandingan.status_pertandingan == "berlangsung") {
            var state = layar.stopwatch.data("state");
            if (state !== "running") {
                layar.stopwatch.timer("remove");
                layar.stopwatch.timer({
                    format: "%M:%S",
                    seconds: 0,
                    duration: seconds,
                    countdown: true,
                    action: 'start'
                });
            }
        } else {
            layar.stopwatch.timer("remove");
            layar.stopwatch.timer({
                format: "%M:%S",
                seconds: 0,
                duration: seconds,
                countdown: true
            });
            layar.stopwatch.timer("pause");
        }
    },

    close_modal_verifikasi_jatuhan: function () {
        if (layar.modalVerifikasiJatuhan) {
            layar.modalVerifikasiJatuhan.hide();
        }
    },

    close_modal_verifikasi_pelanggaran: function () {
        if (layar.modalVerifikasiPelanggaran) {
            layar.modalVerifikasiPelanggaran.hide();
        }
    },

    periksa_sistem_dialog: function () {
        if (layar.verifikasi_pertandingan == null || layar.verifikasi_pertandingan == undefined) {
            layar.close_modal_verifikasi_pelanggaran();
            layar.close_modal_verifikasi_jatuhan();
        } else {
            if (layar.verifikasi_pertandingan.jenis_verifikasi === "jatuhan") {
                if (layar.verifikasi_pertandingan.status == "berlangsung") {
                    ui.open_modal_verifikasi_jatuhan();
                    layar.close_modal_verifikasi_pelanggaran();
                } else if (
                    layar.verifikasi_pertandingan.status == "selesai" &&
                    (layar.modalVerifikasiJatuhan && layar.modalVerifikasiJatuhan._isShown == true)
                ) {
                    layar.close_modal_verifikasi_jatuhan();
                    setTimeout(function () {
                        if (layar.verifikasi_pertandingan.hasil_verifikasi == "biru") {
                            ui.open_modal_hasil_verifikasi("blue", "Valid Drop!");
                        } else if (layar.verifikasi_pertandingan.hasil_verifikasi == "merah") {
                            ui.open_modal_hasil_verifikasi("red", "Valid Drop!");
                        } else if (layar.verifikasi_pertandingan.hasil_verifikasi == "invalid") {
                            ui.open_modal_hasil_verifikasi("warning", "Invalid Drop!");
                        }
                    }, 700);
                } else {
                    layar.close_modal_verifikasi_jatuhan();
                }
            } else if (layar.verifikasi_pertandingan.jenis_verifikasi === "pelanggaran") {
                if (layar.verifikasi_pertandingan.status == "berlangsung") {
                    ui.open_modal_verifikasi_pelanggaran();
                    layar.close_modal_verifikasi_jatuhan();
                } else if (
                    layar.verifikasi_pertandingan.status == "selesai" &&
                    (layar.modalVerifikasiPelanggaran && layar.modalVerifikasiPelanggaran._isShown == true)
                ) {
                    layar.close_modal_verifikasi_pelanggaran();
                    setTimeout(function () {
                        if (layar.verifikasi_pertandingan.hasil_verifikasi == "biru") {
                            ui.open_modal_hasil_verifikasi("blue", "Valid Violation!");
                        } else if (layar.verifikasi_pertandingan.hasil_verifikasi == "merah") {
                            ui.open_modal_hasil_verifikasi("red", "Valid Violation!");
                        } else if (layar.verifikasi_pertandingan.hasil_verifikasi == "invalid") {
                            ui.open_modal_hasil_verifikasi("warning", "Invalid Violation!");
                        }
                    }, 700);
                } else {
                    layar.close_modal_verifikasi_pelanggaran();
                }
            }
        }
    },

    refresh_status_pertandingan: function () {
        $.post(
            BASE_URL + "layar/refresh-status-pertandingan/" + layar.id_pertandingan,
            function (data) {
                if (data.csrf_hash) { CSRF_HASH = data.csrf_hash; }

                if (data.status === true && data.reload === true) {
                    window.location.reload();
                } else if (data.status === false) {
                    // Stinger on round change
                    if (
                        layar.ronde_pertandingan !== null &&
                        data.pertandingan &&
                        layar.ronde_pertandingan !== data.pertandingan.ronde_pertandingan
                    ) {
                        if (typeof stinger !== "undefined") {
                            stinger.set_text("Round " + data.pertandingan.ronde_pertandingan);
                            stinger.start_animation(function () {
                                setTimeout(function () {
                                    stinger.end_animation();
                                }, 6000);
                            });
                        }
                    }

                    layar.set_variable(
                        data.data_nilai,
                        data.pertandingan,
                        data.verifikasi_pertandingan
                    );
                    ui.update_tampilan_nilai();

                    if (typeof layar.socket === 'undefined' || !layar.socket || !layar.socket.connected) {
                        layar.update_timer();
                    }

                    setTimeout(function () {
                        layar.periksa_sistem_dialog();
                    }, 1000);
                }
            },
            "json"
        ).always(function () {
            setTimeout(function () {
                layar.refresh_status_pertandingan();
            }, layar.interval_refresh);
        });
    }
};

// ═══════════════════════════════════════════════════════════════════════════
//  UI Object — display logic (parity v3.php inline)
// ═══════════════════════════════════════════════════════════════════════════
const ui = {
    start_animation: function () {
        var groups = [
            {
                elements: [
                    { selector: '#competition-title', class: 'fade-down' },
                    { selector: '#header-tanding', class: 'fade-down' },
                    { selector: '#nomor-partai', class: 'fade-left' },
                    { selector: '#waktu', class: 'fade-down' },
                    { selector: '#ronde', class: 'fade-right' }
                ],
                delay: 1000
            },
            {
                elements: [
                    { selector: '.kolom-skor-biru', class: 'fade-left' },
                    { selector: '.kolom-skor-merah', class: 'fade-right' },
                    { selector: '.indikator-pelanggaran-biru', class: 'fade-left' },
                    { selector: '.indikator-pelanggaran-merah', class: 'fade-right' }
                ],
                delay: 2700
            },
            {
                elements: [
                    { selector: '.indikator-jatuhan-biru', class: 'fade-left' },
                    { selector: '.indikator-jatuhan-merah', class: 'fade-right' },
                    { selector: '.indikator-poin', class: 'fade-up', group: true }
                ],
                delay: 4000
            }
        ];

        groups.forEach(function (group) {
            var delay = group.delay;
            group.elements.forEach(function (item) {
                var targets = document.querySelectorAll(item.selector);
                targets.forEach(function (el) {
                    el.classList.add('opacity', item.class);
                    setTimeout(function () {
                        el.classList.add('show');
                    }, delay);
                    if (!item.group) delay += 120;
                });
                delay += 200;
            });
        });
    },

    update_tampilan_nilai: function () {
        if (!layar.data_nilai || !layar.data_nilai.juri) return;

        $.each(layar.data_nilai.juri, function (key, perangkat_pertandingan) {
            $.each(perangkat_pertandingan.penilaian_tanding, function (key_sudut, nilai_sudut) {
                if (!nilai_sudut || !nilai_sudut.ringkasan) return true; // skip if no data yet

                var $element = $('.juri-' + perangkat_pertandingan.id_perangkat_pertandingan + '-' + key_sudut + '-indikator');

                var $nilai_lama = $element.data('totalNilaiTerinput' + key_sudut);
                var $jatuhan_lama = $element.data('totalJatuhan' + key_sudut);
                var $hukuman_lama = $element.data('totalHukuman' + key_sudut);

                var $nilai_baru = nilai_sudut.ringkasan.total_nilai_terinput || 0;
                var $jatuhan_baru = (nilai_sudut.kategori_nilai || {}).jatuhan || 0;
                var $hukuman_baru = nilai_sudut.ringkasan.total_hukuman || 0;

                if (
                    $nilai_lama !== undefined && $nilai_baru - $nilai_lama > 0 &&
                    $hukuman_lama !== undefined && $hukuman_baru == $hukuman_lama ||
                    $hukuman_lama !== undefined && $hukuman_baru - $hukuman_lama < 0
                ) {
                    if (key_sudut == 'merah') {
                        $element.removeClass('bg-gradient-180-gray-dark').addClass('bg-gradient-180-red');
                        $element.find('.icon-pukulan-inverted').addClass('d-none');
                        $element.find('.icon-tendangan').addClass('d-none');
                        if ($nilai_baru - $nilai_lama == 1) {
                            $element.find('.icon-pukulan-inverted').removeClass('d-none');
                        } else if ($nilai_baru - $nilai_lama == 2) {
                            $element.find('.icon-tendangan').removeClass('d-none');
                        } else if ($nilai_baru - $nilai_lama == 3) {
                            if ($jatuhan_baru > $jatuhan_lama) {
                                $element.find('.icon-jatuhan').removeClass('d-none');
                            } else {
                                $element.find('.icon-pukulan-inverted').removeClass('d-none');
                                $element.find('.icon-tendangan').removeClass('d-none');
                            }
                        }
                    } else {
                        $element.removeClass('bg-gradient-180-gray-dark').addClass('bg-gradient-180-blue');
                        $element.find('.icon-pukulan').addClass('d-none');
                        $element.find('.icon-tendangan-inverted').addClass('d-none');
                        if ($nilai_baru - $nilai_lama == 1) {
                            $element.find('.icon-pukulan').removeClass('d-none');
                        } else if ($nilai_baru - $nilai_lama == 2) {
                            $element.find('.icon-tendangan-inverted').removeClass('d-none');
                        } else if ($nilai_baru - $nilai_lama == 3) {
                            if ($jatuhan_baru > $jatuhan_lama) {
                                $element.find('.icon-jatuhan').removeClass('d-none');
                            } else {
                                $element.find('.icon-pukulan').removeClass('d-none');
                                $element.find('.icon-tendangan-inverted').removeClass('d-none');
                            }
                        }
                    }

                    // Hukuman indicator
                    if ($hukuman_baru - $hukuman_lama < 0) {
                        $element.find('.icon-hukuman').removeClass('d-none');
                    }

                    ui.reset_highlight_juri($element, 1500);
                }

                // Update data
                $element.data('totalNilaiTerinput' + key_sudut, nilai_sudut.ringkasan.total_nilai_terinput || 0);
                $element.data('totalHukuman' + key_sudut, nilai_sudut.ringkasan.total_hukuman || 0);
                $element.data('totalJatuhan' + key_sudut, (nilai_sudut.kategori_nilai || {}).jatuhan || 0);
            });
        });

        // Update jatuhan count
        if (layar.ringkasan_nilai && layar.ringkasan_nilai.semua_ronde) {
            $('.total_jatuhan_biru').html(layar.ringkasan_nilai.semua_ronde.biru.jatuhan);
            $('.total_jatuhan_merah').html(layar.ringkasan_nilai.semua_ronde.merah.jatuhan);
        }

        // Update skor dan ronde
        $('.skor_merah').html(layar.pertandingan.skor_merah);
        $('.skor_biru').html(layar.pertandingan.skor_biru);
        $('.ronde_pertandingan').html(layar.ronde_pertandingan);

        ui.highlight_nilai_akhir();
        ui.highlight_hukuman();
        ui.adjustScoreFontSize();
    },

    highlight_nilai_akhir: function () {
        if (!layar.ringkasan_nilai || !layar.ringkasan_nilai.semua_ronde) return;

        var skorMerah = parseInt(layar.pertandingan.skor_merah);
        var skorBiru = parseInt(layar.pertandingan.skor_biru);
        var rn = layar.ringkasan_nilai.semua_ronde;

        if (skorBiru > skorMerah) {
            ui.highlight_nilai_sudut('biru');
        } else if (skorBiru < skorMerah) {
            ui.highlight_nilai_sudut('merah');
        } else {
            // Tiebreaker hierarchy (10 levels)
            if (rn.biru.peringatan_2 > rn.merah.peringatan_2) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.peringatan_2 < rn.merah.peringatan_2) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.peringatan_1 > rn.merah.peringatan_1) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.peringatan_1 < rn.merah.peringatan_1) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.teguran_2 > rn.merah.teguran_2) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.teguran_2 < rn.merah.teguran_2) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.teguran_1 > rn.merah.teguran_1) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.teguran_1 < rn.merah.teguran_1) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.binaan_2 > rn.merah.binaan_2) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.binaan_2 < rn.merah.binaan_2) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.binaan_1 > rn.merah.binaan_1) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.binaan_1 < rn.merah.binaan_1) { ui.highlight_nilai_sudut('biru'); }
            // Technical richness tiebreaker
            else if (rn.biru.jatuhan > rn.merah.jatuhan) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.jatuhan < rn.merah.jatuhan) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.tendangan > rn.merah.tendangan) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.tendangan < rn.merah.tendangan) { ui.highlight_nilai_sudut('merah'); }
            else if (rn.biru.pukulan > rn.merah.pukulan) { ui.highlight_nilai_sudut('biru'); }
            else if (rn.biru.pukulan < rn.merah.pukulan) { ui.highlight_nilai_sudut('merah'); }
            else { ui.highlight_nilai_sudut('sama'); }
        }
    },

    highlight_nilai_sudut: function ($sudut) {
        if ($sudut == 'biru') {
            $('.skor_biru').parent().removeClass('bg-gradient-180-white').addClass('bg-gradient-180-blue');
            $('.skor_biru').addClass('text-white');
            $('.skor_merah').removeClass('text-white');
            $('.skor_merah').parent().removeClass('bg-gradient-180-red').addClass('bg-gradient-180-white');
        } else if ($sudut == 'merah') {
            $('.skor_biru').removeClass('text-white');
            $('.skor_biru').parent().removeClass('bg-gradient-180-blue').addClass('bg-gradient-180-white');
            $('.skor_merah').addClass('text-white');
            $('.skor_merah').parent().removeClass('bg-gradient-180-white').addClass('bg-gradient-180-red text-white');
        } else {
            $('.skor_biru').parent().removeClass('bg-gradient-180-blue').addClass('bg-gradient-180-white');
            $('.skor_merah').parent().removeClass('bg-gradient-180-red').addClass('bg-gradient-180-white');
            $('.skor_biru').removeClass('text-white');
            $('.skor_merah').removeClass('text-white');
        }
    },

    highlight_hukuman: function () {
        if (!layar.data_nilai || !layar.data_nilai.juri || !layar.data_nilai.juri[0]) return;

        var highlights = {
            merah: { peringatan: 0, teguran: 0, binaan: 0 },
            biru: { peringatan: 0, teguran: 0, binaan: 0 }
        };

        var isValidEntry = function (entry) { return !entry.is_deleted; };

        // Check Peringatan (-10, -5) across all rounds
        $.each(layar.data_nilai.juri[0].penilaian_tanding, function (sudut, nilai_per_sudut) {
            if (!nilai_per_sudut || !nilai_per_sudut.ronde_pertandingan) return true;
            $.each(nilai_per_sudut.ronde_pertandingan, function (index_ronde, nilai_per_ronde) {
                if (!nilai_per_ronde || !nilai_per_ronde.rincian) return true;
                var validEntries = nilai_per_ronde.rincian.filter(isValidEntry);
                validEntries.forEach(function (entry) {
                    var nilai = parseInt(entry.nilai);
                    if ((nilai === -10 || nilai === -5) &&
                        Math.abs(nilai) > Math.abs(highlights[sudut].peringatan)) {
                        highlights[sudut].peringatan = nilai;
                    }
                });
            });
        });

        // Check Teguran (-2, -1) in current round
        $.each(layar.data_nilai.juri[0].penilaian_tanding, function (sudut, nilai_per_sudut) {
            if (!nilai_per_sudut || !nilai_per_sudut.ronde_pertandingan) return true;
            var rondeData = nilai_per_sudut.ronde_pertandingan[layar.ronde_pertandingan];
            if (!rondeData || !rondeData.rincian) return;
            var currentRoundEntries = rondeData.rincian.filter(isValidEntry);
            currentRoundEntries.forEach(function (entry) {
                var nilai = parseInt(entry.nilai);
                if ((nilai === -2 || nilai === -1) &&
                    Math.abs(nilai) > Math.abs(highlights[sudut].teguran)) {
                    highlights[sudut].teguran = nilai;
                }
            });
        });

        // Check Binaan
        $.each(layar.data_nilai.juri[0].penilaian_tanding, function (sudut, nilai_per_sudut) {
            if (!nilai_per_sudut || !nilai_per_sudut.ronde_pertandingan) return true;
            var rondeData = nilai_per_sudut.ronde_pertandingan[layar.ronde_pertandingan];
            if (!rondeData || !rondeData.catatan) return;
            var binaan = rondeData.catatan.binaan;
            highlights[sudut].binaan = binaan > 0 ? binaan : 0;
        });

        // Update visual indicators
        $.each(['merah', 'biru'], function (_, sudut) {
            var dimClass = sudut === 'biru' ? 'bg-dim-blue' : 'bg-dim-red';
            var gradientClass = sudut === 'biru' ? 'bg-gradient-180-blue' : 'bg-gradient-180-red';

            // Reset all
            $('.indikator-pelanggaran-' + sudut + ' .indikator-binaan, .indikator-pelanggaran-' + sudut + ' .indikator-teguran, .indikator-pelanggaran-' + sudut + ' .indikator-peringatan')
                .removeClass('bg-gradient-180-blue bg-gradient-180-red')
                .addClass(dimClass);

            // Binaan
            if (highlights[sudut].binaan > 0) {
                $('.indikator-pelanggaran-' + sudut + ' .indikator-binaan-1').addClass(gradientClass).removeClass(dimClass);
                if (highlights[sudut].binaan === 2) {
                    $('.indikator-pelanggaran-' + sudut + ' .indikator-binaan-2').addClass(gradientClass).removeClass(dimClass);
                }
            }

            // Teguran
            if (highlights[sudut].teguran === -1) {
                $('.indikator-pelanggaran-' + sudut + ' .indikator-teguran-1').addClass(gradientClass).removeClass(dimClass);
            } else if (highlights[sudut].teguran === -2) {
                $('.indikator-pelanggaran-' + sudut + ' .indikator-teguran-1, .indikator-pelanggaran-' + sudut + ' .indikator-teguran-2')
                    .addClass(gradientClass).removeClass(dimClass);
            }

            // Peringatan
            if (highlights[sudut].peringatan === -5) {
                $('.indikator-pelanggaran-' + sudut + ' .indikator-peringatan-1').addClass(gradientClass).removeClass(dimClass);
            } else if (highlights[sudut].peringatan === -10) {
                $('.indikator-pelanggaran-' + sudut + ' .indikator-peringatan-1, .indikator-pelanggaran-' + sudut + ' .indikator-peringatan-2')
                    .addClass(gradientClass).removeClass(dimClass);
            }
        });
    },

    reset_highlight_juri: function ($element, $timeout) {
        $timeout = $timeout || 3000;
        if ($element.data('highlightTimeout')) {
            clearTimeout($element.data('highlightTimeout'));
        }
        var timeoutId = setTimeout(function () {
            $element.addClass('bg-gradient-180-gray-dark');
            $element.removeClass('bg-gradient-180-red bg-gradient-180-blue');
            $element.find('img').addClass('d-none');
            $element.removeData('highlightTimeout');
        }, $timeout);
        $element.data('highlightTimeout', timeoutId);
    },

    open_modal_verifikasi_jatuhan: function () {
        if (layar.modalVerifikasiJatuhan && layar.modalVerifikasiJatuhan._isShown === false) {
            var $modal = $(layar.modalVerifikasiJatuhan._element);
            $.each($modal.find('div.card'), function (i, v) {
                $(v).find('.card-body > p').html('Waiting Response');
            });
            layar.modalVerifikasiJatuhan.show();
        }
    },

    open_modal_verifikasi_pelanggaran: function () {
        if (layar.modalVerifikasiPelanggaran) {
            var $modal = $(layar.modalVerifikasiPelanggaran._element);
            $.each($modal.find('div.card'), function (i, v) {
                $(v).find('.card-body > p').html('Waiting Response');
                $(v).addClass('bg-gradient-180-gray-dark').removeClass('bg-red bg-blue');
            });
            layar.modalVerifikasiPelanggaran.show();
        }
    },

    open_modal_hasil_verifikasi: function ($background, $text) {
        if (layar.modalHasilVerifikasi && layar.modalHasilVerifikasi._isShown === false) {
            var $modal = $(layar.modalHasilVerifikasi._element);
            if ($background != null) {
                $modal.find('.modal-body').removeClass().addClass('modal-body bg-' + $background);
            }
            if ($text != null) {
                $modal.find('#textModalHasilVerifikasi').html($text);
            }
            layar.modalHasilVerifikasi.show();

            setTimeout(function () {
                layar.modalHasilVerifikasi.hide();
            }, 4000);
        }
    },

    adjustScoreFontSize: function () {
        var scores = document.querySelectorAll('.display-score');
        scores.forEach(function (scoreElement) {
            var score = parseInt(scoreElement.innerText);
            if (score >= 100) {
                scoreElement.style.fontSize = "clamp(5rem, 18vw, 13em)";
            } else {
                scoreElement.style.fontSize = "clamp(5rem, 20vw, 15em)";
            }
        });
    }
};

// ═══════════════════════════════════════════════════════════════════════════
//  Score Change Animation (MutationObserver)
// ═══════════════════════════════════════════════════════════════════════════
$(document).ready(function () {
    var previousScoreBiru = parseInt($('.skor_biru').text()) || 0;
    var previousScoreMerah = parseInt($('.skor_merah').text()) || 0;

    var observer = new MutationObserver(function () {
        var currentScoreBiru = parseInt($('.skor_biru').text()) || 0;
        var currentScoreMerah = parseInt($('.skor_merah').text()) || 0;

        if (currentScoreBiru !== previousScoreBiru) {
            $('.skor_biru').addClass('score-changed');
            setTimeout(function () { $('.skor_biru').removeClass('score-changed'); }, 500);
        }

        if (currentScoreMerah !== previousScoreMerah) {
            $('.skor_merah').addClass('score-changed');
            setTimeout(function () { $('.skor_merah').removeClass('score-changed'); }, 500);
        }

        previousScoreBiru = currentScoreBiru;
        previousScoreMerah = currentScoreMerah;
    });

    var config = { childList: true, subtree: true, characterData: true };
    var skorBiru = document.querySelector('.skor_biru');
    var skorMerah = document.querySelector('.skor_merah');
    if (skorBiru) observer.observe(skorBiru, config);
    if (skorMerah) observer.observe(skorMerah, config);
});
