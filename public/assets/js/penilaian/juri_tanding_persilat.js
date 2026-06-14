/**
 * Juri Tanding PERSILAT — full parity legacy persilat.js
 * Features: scoring input, rincian nilai per ronde (spans + soft-delete),
 *           winner highlight, ronde highlight, auto-scroll, verifikasi modal,
 *           polling 3s, socket.io, periksa_sistem_dialog, animation
 */
const juri = {
    data_nilai: null,
    data_waktu: null,
    waktu_sekarang: null,
    pertandingan: null,
    id_pertandingan: null,
    ronde_pertandingan: null,
    pemenang: null,
    verifikasi_pertandingan: null,
    jawaban_verifikasi_pertandingan: null,
    totalRonde: 3,
    modalVerifikasiJatuhan: null,
    modalVerifikasiPelanggaran: null,

    // ─── CSRF ──────────────────────────────────────────────────────────────
    csrfName: null,
    csrfHash: null,

    rotateCsrf(newHash) {
        if (newHash) {
            juri.csrfHash = newHash;
            const wrapper = document.getElementById('juri-wrapper');
            if (wrapper) wrapper.dataset.csrfHash = newHash;
        }
    },

    // ─── INIT ──────────────────────────────────────────────────────────────
    init(data) {
        const wrapper = document.getElementById('juri-wrapper');
        if (!wrapper) return;

        juri.csrfName = wrapper.dataset.csrfName;
        juri.csrfHash = wrapper.dataset.csrfHash;
        juri.totalRonde = data.totalRonde || 3;

        juri.setup_modals();
        juri.start_animation();
        juri.set_variable(
            data.dataNilai,
            data.pertandingan,
            data.pemenang,
            data.verifikasiPertandingan,
            data.jawabanVerifikasi
        );
        juri.update_tampilan_nilai();
        juri.set_ronde();
        juri.periksa_sistem_dialog();
        juri.bind_buttons();
        juri.refresh_status_pertandingan();
        juri.init_socket();
    },

    setup_modals() {
        let elJatuhan = document.getElementById('modalVerifikasiJatuhan');
        if (elJatuhan && juri.modalVerifikasiJatuhan === null) {
            juri.modalVerifikasiJatuhan = new bootstrap.Modal(elJatuhan, { keyboard: false });
        }

        let elPelanggaran = document.getElementById('modalVerifikasiPelanggaran');
        if (elPelanggaran && juri.modalVerifikasiPelanggaran === null) {
            juri.modalVerifikasiPelanggaran = new bootstrap.Modal(elPelanggaran, { keyboard: false });
        }
    },

    set_variable(dataNilai, pertandingan, pemenang, verifikasi, jawaban) {
        juri.data_nilai = dataNilai;
        juri.pertandingan = pertandingan;
        juri.data_waktu = pertandingan.data_waktu ? (typeof pertandingan.data_waktu === 'string' ? JSON.parse(pertandingan.data_waktu) : pertandingan.data_waktu) : null;
        juri.waktu_sekarang = juri.data_waktu ? juri.data_waktu[pertandingan.ronde_pertandingan]?.[1] ?? 0 : 0;
        juri.id_pertandingan = pertandingan.id_pertandingan;
        juri.ronde_pertandingan = pertandingan.ronde_pertandingan;
        juri.pemenang = pemenang;
        juri.verifikasi_pertandingan = verifikasi;
        juri.jawaban_verifikasi_pertandingan = jawaban;
    },

    // ─── ANIMATION (parity legacy) ─────────────────────────────────────────
    start_animation() {
        $('#header-tanding').addClass('animated fadeInDown').removeClass('opacity');
        setTimeout(() => {
            $('.card-container-juri').addClass('animated fadeIn').removeClass('opacity');
            setTimeout(() => {
                $('#tabel-nilai-juri tr').each(function (index) {
                    setTimeout(() => {
                        $(this).addClass('animated fadeInDown').removeClass('opacity');
                    }, 300 * index);
                });
                setTimeout(() => {
                    $('#button-biru button').each(function (index) {
                        setTimeout(() => {
                            $(this).addClass('animated fadeIn').removeClass('opacity');
                        }, 350 * index);
                    });
                    $('#button-merah button').each(function (index) {
                        setTimeout(() => {
                            $(this).addClass('animated fadeIn').removeClass('opacity');
                        }, 350 * index);
                    });
                }, 1700);
            }, 600);
        }, 600);
    },

    // ─── RENDER NILAI (parity legacy update_tampilan_nilai) ─────────────────
    update_tampilan_nilai() {
        if (!juri.data_nilai) return;

        $.each(juri.data_nilai, function (sudut, v) {
            if (!v || !v.ronde_pertandingan) return;

            $.each(v.ronde_pertandingan, function (ronde, nilai) {
                let jumlah_rincian = nilai.rincian ? nilai.rincian.length : 0;
                $('.' + sudut + '-ronde-' + ronde + '-nilai').empty();

                let rincian_nilai = '';

                if (jumlah_rincian > 0) {
                    $.each(nilai.rincian, function (index, entry) {
                        // Soft-deleted entries
                        if (entry.is_deleted === true) {
                            const timestamp = entry.deleted_at
                                ? new Date(entry.deleted_at * 1000).toLocaleTimeString()
                                : '';
                            if (parseInt(entry.nilai) > 0) {
                                rincian_nilai += '<span class="fw-lighter text-decoration-line-through px-2 d-inline-block" title="deleted at ' + timestamp + '" style="color:#999999">' + entry.nilai + '</span>';
                            }
                            return true;
                        }

                        // Normal entries
                        if (parseInt(entry.nilai) > 0) {
                            if (entry.status === 'input') {
                                // Belum diverifikasi (masih input state)
                                rincian_nilai += '<span class="fw-lighter text-decoration-line-through px-2 d-inline-block" style="color:#999999">' + entry.nilai + '</span>';
                            } else {
                                rincian_nilai += '<span class="px-2 d-inline-block">' + entry.nilai + '</span>';
                            }
                        }
                    });
                }

                if (rincian_nilai === '') {
                    $('.' + sudut + '-ronde-' + ronde + '-nilai').html('<span>&emsp;</span>');
                } else {
                    $('.' + sudut + '-ronde-' + ronde + '-nilai').html(rincian_nilai);
                }

                // Total per ronde
                let totalRonde = (nilai.ringkasan && nilai.ringkasan.nilai_akhir !== undefined)
                    ? nilai.ringkasan.nilai_akhir : 0;
                $('.' + sudut + '-ronde-' + ronde + '-total').html(totalRonde);
            });
        });

        // Total skor (dari pertandingan object)
        let skorBiru = parseInt(juri.pertandingan.skor_biru) || 0;
        let skorMerah = parseInt(juri.pertandingan.skor_merah) || 0;

        $('#total_nilai_akhir_biru').html(skorBiru);
        $('#total_nilai_akhir_merah').html(skorMerah);

        // Winner highlight (gradient toggle — parity legacy)
        if (skorMerah > skorBiru) {
            $('#total_nilai_akhir_biru').parent()
                .removeClass('bg-gradient-180-blue').addClass('bg-gradient-180-gray-dark');
            $('#total_nilai_akhir_merah').parent()
                .addClass('bg-gradient-180-red').removeClass('bg-gradient-180-gray-dark');
        } else if (skorBiru > skorMerah) {
            $('#total_nilai_akhir_merah').parent()
                .removeClass('bg-gradient-180-red').addClass('bg-gradient-180-gray-dark');
            $('#total_nilai_akhir_biru').parent()
                .addClass('bg-gradient-180-blue').removeClass('bg-gradient-180-gray-dark');
        } else {
            $('#total_nilai_akhir_merah').parent()
                .removeClass('bg-gradient-180-red').addClass('bg-gradient-180-gray-dark');
            $('#total_nilai_akhir_biru').parent()
                .removeClass('bg-gradient-180-blue').addClass('bg-gradient-180-gray-dark');
        }
    },

    // ─── RONDE HIGHLIGHT ───────────────────────────────────────────────────
    set_ronde() {
        $('td.ronde-1, td.ronde-2, td.ronde-3').removeClass('bg-warning');
        $('td.ronde-' + juri.ronde_pertandingan).addClass('bg-warning');
    },

    // ─── BUTTON BINDINGS ───────────────────────────────────────────────────
    bind_buttons() {
        // Scoring buttons
        document.querySelectorAll('.btn-scoring-legacy').forEach(btn => {
            btn.addEventListener('click', function () {
                const sudut = this.dataset.sudut;
                const nilai = parseInt(this.dataset.nilai, 10);
                juri.edit_penilaian_tanding(sudut, nilai, this);
            });
        });

        // Hapus buttons
        document.querySelectorAll('.btn-hapus-legacy').forEach(btn => {
            btn.addEventListener('click', function () {
                juri.edit_penilaian_tanding(this.dataset.sudut, null, this);
            });
        });

        // Verifikasi jawaban buttons
        document.querySelectorAll('.btn-jawaban-verifikasi').forEach(btn => {
            btn.addEventListener('click', function () {
                juri.submit_jawaban_verifikasi_pertandingan(this.dataset.jawaban);
            });
        });
    },

    // ─── EDIT PENILAIAN (parity legacy) ────────────────────────────────────
    edit_penilaian_tanding(sudut, nilai, btn) {
        // Disable button temporarily
        $(btn).prop('disabled', true);

        let entry;
        if (nilai !== null) {
            entry = {
                nilai: nilai,
                waktu_pertandingan: juri.waktu_sekarang,
                timestamp: null,
                status: 'input'
            };
        } else {
            entry = { action: 'remove' };
        }

        const body = new URLSearchParams();
        body.append(juri.csrfName, juri.csrfHash);
        body.append('sudut', sudut);
        body.append('entry', JSON.stringify(entry));

        fetch(document.getElementById('juri-wrapper').dataset.endpointEdit, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(r => r.json())
        .then(data => {
            juri.rotateCsrf(data.csrf_hash);
            $(btn).prop('disabled', false);

            if (data.status === true) {
                juri.data_nilai = data.response;
                juri.update_tampilan_nilai();

                // Auto-scroll ke kanan (nilai terbaru)
                let containerClass = '.' + sudut + '-ronde-' + juri.ronde_pertandingan + '-nilai';
                let container = $(containerClass);
                if (container.length > 0) {
                    container.animate({ scrollLeft: container[0].scrollWidth }, 300);
                }
            } else {
                Swal.fire('Error', 'Gagal mengubah penilaian', 'error');
            }
        })
        .catch(() => {
            $(btn).prop('disabled', false);
            Swal.fire('Error', 'Koneksi terputus', 'warning');
        });
    },

    // ─── VERIFIKASI ────────────────────────────────────────────────────────
    submit_jawaban_verifikasi_pertandingan(jawaban) {
        const body = new URLSearchParams();
        body.append(juri.csrfName, juri.csrfHash);
        body.append('jawaban', jawaban);

        fetch(document.getElementById('juri-wrapper').dataset.endpointVerifikasi, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(r => r.json())
        .then(data => {
            juri.rotateCsrf(data.csrf_hash);
            if (data.status === false) {
                Swal.fire({
                    title: 'Error',
                    text: 'System Error, Failed sending verification answer',
                    icon: 'error'
                });
            } else {
                juri.close_modal_verifikasi_jatuhan();
                juri.close_modal_verifikasi_pelanggaran();
                Swal.fire({
                    title: 'Success',
                    text: 'Answer Has Been Sent',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        })
        .catch(() => {
            Swal.fire({
                title: 'Error',
                text: 'System Error, Failed sending verification answer (connection lost)',
                icon: 'error'
            });
        });
    },

    open_modal_verifikasi_jatuhan() {
        if (juri.modalVerifikasiJatuhan) {
            juri.modalVerifikasiJatuhan.show();
        }
    },
    close_modal_verifikasi_jatuhan() {
        if (juri.modalVerifikasiJatuhan) {
            juri.modalVerifikasiJatuhan.hide();
        }
    },
    open_modal_verifikasi_pelanggaran() {
        if (juri.modalVerifikasiPelanggaran) {
            juri.modalVerifikasiPelanggaran.show();
        }
    },
    close_modal_verifikasi_pelanggaran() {
        if (juri.modalVerifikasiPelanggaran) {
            juri.modalVerifikasiPelanggaran.hide();
        }
    },

    // ─── PERIKSA SISTEM DIALOG (parity legacy) ─────────────────────────────
    periksa_sistem_dialog() {
        if (juri.verifikasi_pertandingan == null || juri.verifikasi_pertandingan == undefined) {
            juri.close_modal_verifikasi_jatuhan();
            juri.close_modal_verifikasi_pelanggaran();
            return;
        }

        const verif = juri.verifikasi_pertandingan;
        const jawaban = juri.jawaban_verifikasi_pertandingan;
        const belumJawab = (jawaban == null || jawaban.jawaban == null);

        if (
            verif.jenis_verifikasi === 'jatuhan' &&
            verif.status === 'berlangsung' &&
            belumJawab &&
            (juri.modalVerifikasiJatuhan === null || juri.modalVerifikasiJatuhan._isShown === false)
        ) {
            juri.open_modal_verifikasi_jatuhan();
        } else if (
            verif.jenis_verifikasi === 'pelanggaran' &&
            verif.status === 'berlangsung' &&
            belumJawab &&
            (juri.modalVerifikasiPelanggaran === null || juri.modalVerifikasiPelanggaran._isShown === false)
        ) {
            juri.open_modal_verifikasi_pelanggaran();
        }
    },

    // ─── POLLING (3s, parity legacy) ───────────────────────────────────────
    refresh_status_pertandingan() {
        const body = new URLSearchParams();
        body.append(juri.csrfName, juri.csrfHash);

        fetch(document.getElementById('juri-wrapper').dataset.endpointRefresh, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(r => r.json())
        .then(data => {
            juri.rotateCsrf(data.csrf_hash);

            if (data.status === true && data.reload === true) {
                window.location.reload();
                return;
            }

            if (data.status === false) {
                // Pertandingan sama — update data
                if (data.data_nilai == null) {
                    window.location.reload();
                    return;
                }

                juri.set_variable(
                    data.data_nilai,
                    data.pertandingan,
                    data.pemenang,
                    data.verifikasi_pertandingan,
                    data.jawaban_verifikasi_pertandingan
                );
                juri.update_tampilan_nilai();
                juri.set_ronde();
                juri.periksa_sistem_dialog();
            }
        })
        .catch(() => {})
        .finally(() => {
            setTimeout(() => {
                juri.refresh_status_pertandingan();
            }, 3000);
        });
    },

    // ─── WARNING PINDAH BABAK (parity legacy) ──────────────────────────────
    warning_pindah_babak() {
        Swal.fire({
            title: 'Error',
            text: 'Perpindahan babak hanya dapat dilakukan oleh operator !',
            icon: 'error'
        });
    },

    // ─── SOCKET.IO ─────────────────────────────────────────────────────────
    init_socket() {
        if (typeof io === 'undefined') return;

        const socket = io(window.REALTIME_URL || 'http://localhost:3000');
        socket.emit('JOIN_ROOM', { id_pertandingan: juri.id_pertandingan });

        socket.on('NILAI_UPDATE', data => {
            if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
                if (data.skor_merah !== undefined) {
                    juri.pertandingan.skor_merah = data.skor_merah;
                }
                if (data.skor_biru !== undefined) {
                    juri.pertandingan.skor_biru = data.skor_biru;
                }
                juri.update_tampilan_nilai();
            }
        });

        socket.on('VERIFIKASI_JATUHAN', data => {
            if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
                // Set verifikasi object so periksa_sistem_dialog picks it up
                juri.verifikasi_pertandingan = {
                    jenis_verifikasi: 'jatuhan',
                    status: 'berlangsung'
                };
                juri.jawaban_verifikasi_pertandingan = null;
                juri.periksa_sistem_dialog();
            }
        });

        socket.on('VERIFIKASI_PELANGGARAN', data => {
            if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
                juri.verifikasi_pertandingan = {
                    jenis_verifikasi: 'pelanggaran',
                    status: 'berlangsung'
                };
                juri.jawaban_verifikasi_pertandingan = null;
                juri.periksa_sistem_dialog();
            }
        });

        socket.on('MATCH_STATUS_CHANGE', data => {
            if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
                window.location.reload();
            }
        });

        socket.on('KONTROL_WAKTU', data => {
            if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
                if (data.ronde_pertandingan) {
                    juri.ronde_pertandingan = data.ronde_pertandingan;
                    juri.set_ronde();
                }
            }
        });

        // FIX #4: Add UPDATE_WAKTU listener for accurate per-tick timer sync
        // (was missing — waktu_sekarang previously only refreshed via 3s polling,
        //  causing waktu_pertandingan stamps on score entries to lag up to 3s).
        socket.on('UPDATE_WAKTU', data => {
            if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
                if (data.data_waktu && juri.ronde_pertandingan && data.data_waktu[juri.ronde_pertandingan]) {
                    // data_waktu structure: { ronde: [start_ms, total_ms, remaining_ms] }
                    juri.waktu_sekarang = data.data_waktu[juri.ronde_pertandingan][2];
                }
                if (data.status_pertandingan) {
                    juri.status_pertandingan = data.status_pertandingan;
                }
            }
        });

        // FIX #4: Add ROOM_RESET listener (match/round reset → reload to recover state)
        socket.on('ROOM_RESET', data => {
            if (data && String(data.id_pertandingan) === String(juri.id_pertandingan)) {
                window.location.reload();
            }
        });
    }
};

// ─── Bootstrap on DOM Ready ────────────────────────────────────────────────
$(document).ready(function () {
    if (typeof JURI_INIT !== 'undefined') {
        juri.init(JURI_INIT);
    }
});
