/**
 * Juri Seni PERSILAT — Full Parity Legacy
 * Parity: /dps/assets/penilaian/js/application/juri/seni/persilat.js
 * Handles: kebenaran pointer, unsur_nilai +/- , hukuman (read-only from KP),
 *          offline localStorage fallback, auto-sync, ready toggle, akses lock
 */
const juri = {
  penampilan_seni: null,
  id_penampilan_seni: null,
  data_nilai: null,
  mode: null,
  is_offline: false,
  button_audio: new Audio(BASE_URL + "assets/sound/button.mp3"),

  set_offline_data: function(clean_data) {
    juri.is_offline = true;
    localStorage.setItem('offline_seni_' + juri.id_penampilan_seni, JSON.stringify(clean_data));
    juri.update_offline_ui(true);
  },
  set_online_status: function(status) {
    juri.is_offline = !status;
    juri.update_offline_ui(!status);
  },
  update_offline_ui: function(is_offline) {
    let $indicator = $('#offline-indicator');
    if ($indicator.length > 0) {
      if (is_offline) {
        $indicator.removeClass('bg-success').addClass('bg-danger blink-indicator')
          .html('<small><i class="fas fa-exclamation-triangle me-1"></i> Lokal</small>').show();
      } else {
        $indicator.removeClass('bg-danger').addClass('bg-success blink-indicator')
          .html('<small><i class="fas fa-wifi me-1"></i> Online</small>').show();
      }
    }
  },
  sync_offline_data: function() {
    let offline_data = localStorage.getItem('offline_seni_' + juri.id_penampilan_seni);
    if (offline_data) {
      console.log("Mencoba sync data offline...");
      $.post(
        BASE_URL + "juri/edit-penilaian-seni/" + juri.id_penampilan_seni,
        { data_nilai: offline_data, [CSRF_NAME]: CSRF_HASH },
        function (data) {
          if (data && data.status == true) {
            console.log("Sync data offline berhasil");
            localStorage.removeItem('offline_seni_' + juri.id_penampilan_seni);
            juri.set_online_status(true);
          }
          if (data && data.csrf_hash) CSRF_HASH = data.csrf_hash;
        },
        "json"
      ).fail(function() {
        console.log("Sync data offline gagal: masih belum ada koneksi");
      });
    }
  },

  init_penilaian_seni: function($penampilan_seni, $data_nilai, $mode, $kelas_aksen_warna) {
    $mode = $mode || "juri";
    $kelas_aksen_warna = $kelas_aksen_warna || "bg-gradient-180-warning";
    juri.set_variable($penampilan_seni, $data_nilai, $mode, $kelas_aksen_warna);
    juri.update_tampilan_nilai();
    juri.refresh_status_seni();
    var $data_pointer = juri.pointer.get_data_pointer();
    juri.pointer.update_tampilan_pointer($data_pointer);
  },

  set_variable: function($penampilan_seni, $data_nilai, $mode, $kelas_aksen_warna) {
    juri.mode = $mode || "juri";
    juri.penampilan_seni = $penampilan_seni;
    juri.id_penampilan_seni = $penampilan_seni.id_penampilan_seni;
    juri.data_nilai = $data_nilai;
    juri.kelas_aksen_warna = $kelas_aksen_warna || "bg-gradient-180-warning";
  },

  set_hukuman_ringkasan: function($penampilan_seni, $data_nilai) {
    juri.data_nilai.penilaian.hukuman = $data_nilai.penilaian.hukuman;
    juri.data_nilai.penilaian.ringkasan = $data_nilai.penilaian.ringkasan;
  },

  update_data_nilai: function($element) {
    juri.hitung_total_nilai();
    if (juri.data_nilai.penilaian.ringkasan.total_hukuman === undefined) {
      juri.hitung_total_hukuman();
    }
    juri.hitung_nilai_akhir();

    // Clean data: only unsur_nilai + total_nilai (no penalties)
    var clean_data = {
      penilaian: {
        unsur_nilai: juri.data_nilai.penilaian.unsur_nilai,
        ringkasan: {
          total_nilai: juri.data_nilai.penilaian.ringkasan.total_nilai,
          nilai_minimal: juri.data_nilai.penilaian.ringkasan.nilai_minimal,
        },
      },
    };

    $.post(
      BASE_URL + "juri/edit-penilaian-seni/" + juri.id_penampilan_seni,
      {
        data_nilai: JSON.stringify(clean_data),
        [CSRF_NAME]: CSRF_HASH,
      },
      function (data, textStatus, jqXHR) {
        if (data && data.status == true) {
          console.log("Technical scoring updated successfully");
          if (juri.is_offline) juri.set_online_status(true);
        } else {
          console.log("gagal update nilai");
          juri.set_offline_data(clean_data);
        }
        if (data && data.csrf_hash) CSRF_HASH = data.csrf_hash;
      },
      "json"
    ).fail(function() {
      console.log("Koneksi terputus saat update nilai");
      juri.set_offline_data(clean_data);
    });

    juri.update_tampilan_nilai();
    var $data_pointer = juri.pointer.get_data_pointer();
    juri.pointer.update_tampilan_pointer($data_pointer);
  },

  edit_nilai_kebenaran_jurus: function($jurus, $nomor_rangkaian_gerak, $perubahan, $element) {
    var $nilai_maksimal = parseFloat(
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].nilai_maksimal
    );
    var $nilai_diperoleh =
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].nilai_diperoleh;
    var $nilai_diperoleh_selanjutnya = Number($nilai_diperoleh + $perubahan).toFixed(2);
    $nilai_diperoleh_selanjutnya = Number($nilai_diperoleh_selanjutnya);

    if ($nilai_diperoleh_selanjutnya <= $nilai_maksimal && $nilai_diperoleh_selanjutnya >= 0) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
        .rangkaian_gerak[$nomor_rangkaian_gerak].nilai_diperoleh = $nilai_diperoleh_selanjutnya;

      var $total_nilai_diperoleh = juri.data_nilai.penilaian.unsur_nilai.kebenaran.nilai_diperoleh;
      $total_nilai_diperoleh = Number($total_nilai_diperoleh + $perubahan).toFixed(2);
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.nilai_diperoleh = Number($total_nilai_diperoleh);

      if ($perubahan > 0) {
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
          .rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan -= 1;
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.total_kesalahan_gerak -= 1;
      } else {
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$jurus]
          .rangkaian_gerak[$nomor_rangkaian_gerak].jumlah_kesalahan += 1;
        juri.data_nilai.penilaian.unsur_nilai.kebenaran.total_kesalahan_gerak += 1;
      }
    } else {
      console.log("macet");
    }

    juri.update_data_nilai($element);
    try { juri.button_audio.play(); } catch(e) {}
  },

  pointer: {
    pindah_gerakan: function($arah_gerakan, $perubahan_nilai, $element) {
      var $data_pointer = juri.pointer.get_data_pointer();
      if ($data_pointer === null) return;
      juri.edit_nilai_kebenaran_jurus(
        $data_pointer.pointer_jurus,
        $data_pointer.pointer_rangkaian_gerak,
        $perubahan_nilai,
        $element
      );
      juri.pointer.move_pointer($data_pointer, $arah_gerakan);
    },
    pindah_pointer_rangkaian_gerak: function($arah_gerakan) {
      var $data_pointer = juri.pointer.get_data_pointer();
      if ($data_pointer === null) return;
      var $nomor_selanjutnya = $data_pointer.pointer_rangkaian_gerak + $arah_gerakan;
      if (juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$data_pointer.pointer_jurus]
          .rangkaian_gerak[$nomor_selanjutnya] !== undefined) {
        $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
        $data_pointer.pointer_gerakan = 1;
        try { juri.button_audio.play(); } catch(e) {}
      } else {
        var $daftar_jurus = Object.keys(juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus);
        var $idx = $daftar_jurus.indexOf($data_pointer.pointer_jurus);
        var $jurus_next = $daftar_jurus[$idx + $arah_gerakan];
        if ($jurus_next != undefined) {
          $data_pointer.pointer_jurus = $jurus_next;
          $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
          $data_pointer.pointer_gerakan = 1;
        }
      }
      juri.pointer.set_data_pointer($data_pointer);
      juri.pointer.update_tampilan_pointer($data_pointer);
    },
    move_pointer: function($data_pointer, $arah_gerakan) {
      juri.pointer.unlock_tombol();
      var $new_ptr = $data_pointer.pointer_gerakan + $arah_gerakan;
      if ($new_ptr > 0 && $new_ptr <= $data_pointer.jumlah_gerakan) {
        $data_pointer.pointer_gerakan += $arah_gerakan;
      } else {
        var $nomor_selanjutnya = $data_pointer.pointer_rangkaian_gerak + 1;
        if (juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$data_pointer.pointer_jurus]
            .rangkaian_gerak[$nomor_selanjutnya] !== undefined) {
          $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
          $data_pointer.pointer_gerakan = 1;
        } else {
          var $daftar_jurus = Object.keys(juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus);
          var $idx = $daftar_jurus.indexOf($data_pointer.pointer_jurus);
          var $jurus_next = $daftar_jurus[$idx + 1];
          if ($jurus_next != undefined) {
            $data_pointer.pointer_jurus = $jurus_next;
            $data_pointer.pointer_rangkaian_gerak += $arah_gerakan;
            $data_pointer.pointer_gerakan = 1;
          }
        }
      }
      juri.pointer.set_data_pointer($data_pointer);
      juri.pointer.update_tampilan_pointer($data_pointer);
    },
    reset_pointer: function($button) {
      var $daftar_jurus = Object.keys(juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus);
      juri.pointer.set_pointer_jurus($daftar_jurus[0]);
      juri.pointer.set_pointer_rangkaian_gerak(1);
      juri.pointer.set_pointer_gerakan(1);
      juri.update_data_nilai($button);
      juri.pointer.unlock_tombol();
    },
    get_data_pointer: function() {
      if (juri.data_nilai.penilaian.unsur_nilai.kebenaran == undefined) return null;
      var $ptr_jurus = juri.pointer.get_pointer_jurus();
      var $ptr_rg = juri.pointer.get_pointer_rangkaian_gerak();
      return {
        pointer_jurus: $ptr_jurus,
        pointer_rangkaian_gerak: $ptr_rg,
        pointer_gerakan: juri.pointer.get_pointer_gerakan(),
        jumlah_kesalahan_rangkaian_gerak: juri.pointer.get_jumlah_kesalahan($ptr_jurus, $ptr_rg),
        jumlah_kebenaran_rangkaian_gerak: juri.pointer.get_jumlah_kebenaran($ptr_jurus, $ptr_rg),
        jumlah_gerakan: juri.pointer.get_jumlah_gerakan($ptr_jurus, $ptr_rg),
        nilai_diperoleh_rangkaian_gerak: juri.pointer.get_nilai_diperoleh($ptr_jurus, $ptr_rg),
        nilai_maksimal_rangkaian_gerak: juri.pointer.get_nilai_maksimal($ptr_jurus, $ptr_rg),
      };
    },
    set_data_pointer: function($dp) {
      juri.pointer.set_pointer_jurus($dp.pointer_jurus);
      juri.pointer.set_pointer_rangkaian_gerak($dp.pointer_rangkaian_gerak);
      juri.pointer.set_pointer_gerakan($dp.pointer_gerakan);
    },
    update_tampilan_pointer: function($dp) {
      if ($dp === null) return;
      $(".pointer_jurus").html(($dp.pointer_jurus || '').replace("_", " "));
      $(".pointer_rangkaian_gerak").html($dp.pointer_rangkaian_gerak);
      $(".pointer_gerakan").html($dp.pointer_gerakan);
      $(".jumlah_kesalahan_rangkaian_gerak").html($dp.jumlah_kesalahan_rangkaian_gerak);
      $(".jumlah_kebenaran_rangkaian_gerak").html($dp.jumlah_kebenaran_rangkaian_gerak);

      $(".container_rangkaian_gerak").removeClass("bg-gradient-180-blue bg-gradient-180-red bg-gradient-180-warning text-white");
      $(".container_" + $dp.pointer_jurus + "_" + $dp.pointer_rangkaian_gerak)
        .addClass(juri.kelas_aksen_warna + " text-white");
    },
    // Getters
    get_pointer_jurus: function() {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_jurus;
    },
    get_pointer_rangkaian_gerak: function() {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_rangkaian_gerak;
    },
    get_pointer_gerakan: function() {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_gerakan;
    },
    get_jumlah_gerakan: function($j, $rg) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$j].rangkaian_gerak[$rg].jumlah_gerakan;
    },
    get_jumlah_kesalahan: function($j, $rg) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$j].rangkaian_gerak[$rg].jumlah_kesalahan;
    },
    get_jumlah_kebenaran: function($j, $rg) {
      var kesalahan = juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$j].rangkaian_gerak[$rg].jumlah_kesalahan;
      var gerakan = juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$j].rangkaian_gerak[$rg].jumlah_gerakan;
      return gerakan - kesalahan;
    },
    get_nilai_diperoleh: function($j, $rg) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$j].rangkaian_gerak[$rg].nilai_diperoleh;
    },
    get_nilai_maksimal: function($j, $rg) {
      return juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus[$j].rangkaian_gerak[$rg].nilai_maksimal;
    },
    // Setters
    set_pointer_jurus: function(v) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_jurus = v;
    },
    set_pointer_rangkaian_gerak: function(v) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_rangkaian_gerak = v;
    },
    set_pointer_gerakan: function(v) {
      juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.pointer_gerakan = v;
    },
    lock_tombol: function() {
      $(".button_gerakan_benar, .button_gerakan_salah").prop("disabled", true).addClass("btn-disabled");
    },
    unlock_tombol: function() {
      $(".button_gerakan_benar, .button_gerakan_salah").removeAttr("disabled").removeClass("btn-disabled");
    },
  },

  edit_unsur_nilai: function($jenis_unsur_nilai, $perubahan, $element) {
    var $input = $($element).parents(".container_" + $jenis_unsur_nilai).find("input");
    var $nilai_maksimal = parseFloat(juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_maksimal);
    var $nilai_minimal = parseFloat(juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_minimal || 0);
    var $next = Number(parseFloat($input.val()) + parseFloat($perubahan)).toFixed(2);

    if ($next <= $nilai_maksimal && $next >= $nilai_minimal) {
      juri.data_nilai.penilaian.unsur_nilai[$jenis_unsur_nilai].nilai_diperoleh += $perubahan;
      juri.update_data_nilai($element);
    }
    try { juri.button_audio.play(); } catch(e) {}
  },

  edit_hukuman: function($jenis_hukuman, $data, $element) {
    // Juri cannot edit penalties — KP only
    if (typeof Swal !== "undefined") {
      Swal.fire({
        title: "Akses Ditolak",
        text: "Hanya Ketua Pertandingan yang dapat mengubah nilai hukuman.",
        icon: "info",
        confirmButtonText: "OK",
      });
    } else {
      alert("Hanya Ketua Pertandingan yang dapat mengubah nilai hukuman.");
    }
    return false;
  },

  hitung_total_nilai: function() {
    juri.data_nilai.penilaian.ringkasan.total_nilai = 0;
    $.each(juri.data_nilai.penilaian.unsur_nilai, function(i, unsur_nilai) {
      var $bobot = Number(unsur_nilai.nilai_diperoleh).toFixed(2);
      $bobot = Number($bobot);
      juri.data_nilai.penilaian.ringkasan.total_nilai += $bobot;
    });
    var $nilai_minimal = juri.data_nilai.penilaian.ringkasan.nilai_minimal || 0;
    var $total = $nilai_minimal + juri.data_nilai.penilaian.ringkasan.total_nilai;
    $total = Number($total).toFixed(2);
    juri.data_nilai.penilaian.ringkasan.total_nilai = Number($total);
  },

  hitung_total_hukuman: function() {
    juri.data_nilai.penilaian.ringkasan.total_hukuman = 0;
    if (juri.data_nilai.penilaian.hukuman) {
      $.each(juri.data_nilai.penilaian.hukuman, function(i, hukuman) {
        if (hukuman && hukuman.detail_hukuman) {
          juri.data_nilai.penilaian.ringkasan.total_hukuman += hukuman.detail_hukuman.nilai_hukuman;
        }
      });
    }
  },

  hitung_nilai_akhir: function() {
    juri.data_nilai.penilaian.ringkasan.nilai_akhir =
      juri.data_nilai.penilaian.ringkasan.total_nilai -
      (juri.data_nilai.penilaian.ringkasan.total_hukuman || 0);
  },

  update_tampilan_nilai: function() {
    $.each(juri.data_nilai.penilaian, function(jenis, value_jenis) {
      if (jenis == "unsur_nilai") {
        $.each(value_jenis, function(key_unsur, val_unsur) {
          if (key_unsur == "kebenaran") {
            if (val_unsur.jurus) {
              $.each(val_unsur.jurus, function(jurus, val_jurus) {
                $.each(val_jurus.rangkaian_gerak, function(nomor_rg, val_rg) {
                  $(".kebenaran_" + jurus + "_" + nomor_rg).val(val_rg.jumlah_kesalahan);
                  $(".kebenaran_" + jurus + "_" + nomor_rg).attr("max", val_rg.nilai_maksimal);
                });
              });
            }
            var $total_potongan = (val_unsur.nilai_maksimal - val_unsur.nilai_diperoleh).toFixed(2);
            $(".total_pengurangan_kebenaran_gerak").html($total_potongan);
            $(".total_nilai_kebenaran").val(val_unsur.nilai_diperoleh.toFixed(2));
          } else {
            $(".nilai_" + key_unsur).val(val_unsur.nilai_diperoleh.toFixed(2));
          }
        });
      } else if (jenis == "hukuman") {
        $.each(value_jenis, function(jenis_hukuman, hukuman) {
          if (!hukuman || !hukuman.detail_hukuman) return;
          var detail = hukuman.detail_hukuman;
          if (hukuman.tipe == "pilihan ganda" || hukuman.tipe == "satu kali") {
            $(".nilai_hukuman_" + jenis_hukuman).val(detail.nilai_hukuman);
          } else if (hukuman.tipe == "repetisi") {
            $(".jumlah_repetisi_" + jenis_hukuman).val(detail.jumlah_repetisi);
            $(".nilai_hukuman_" + jenis_hukuman).val(detail.nilai_hukuman);
          }
        });
      } else if (jenis == "ringkasan") {
        $(".total_nilai").val(value_jenis.total_nilai);
        $(".total_hukuman").val(value_jenis.total_hukuman);
        if (juri.mode == "juri") {
          $(".nilai_akhir").val(value_jenis.nilai_akhir);
        } else {
          $(".nilai_akhir").html(value_jenis.nilai_akhir);
        }
      }
    });
  },

  update_tampilan_nilai_akhir: function() {
    $.each(juri.data_nilai.penilaian, function(jenis, value_jenis) {
      if (jenis == "ringkasan") {
        $(".total_nilai").val(value_jenis.total_nilai);
        $(".total_hukuman").val(value_jenis.total_hukuman);
        if (juri.mode == "juri") {
          $(".nilai_akhir").val(value_jenis.nilai_akhir);
        } else {
          $(".nilai_akhir").html(value_jenis.nilai_akhir);
        }
      }
    });
  },

  update_akses_penilaian: function($akses) {
    if ($akses == "dibuka") {
      var overlay = document.getElementById("overlay");
      if (overlay) {
        overlay.classList.remove("slideInDown");
        overlay.classList.add("slideOutUp");
        setTimeout(function() { overlay.remove(); }, 1000);
      }
    } else {
      var overlay = document.getElementById("overlay");
      if (!overlay) {
        overlay = document.createElement("div");
        overlay.id = "overlay";
        overlay.className = "position-fixed top-0 start-0 w-100 h-100 bg-dark d-flex justify-content-center align-items-center animated slideInDown";
        overlay.style.zIndex = "9999";
        overlay.style.opacity = "0.95";
        var text = document.createElement("div");
        text.className = "text-white h1";
        text.innerText = "Scoring Access Locked";
        overlay.appendChild(text);
        document.body.appendChild(overlay);
      }
    }
  },

  diskualifikasi_peserta: function() {
    Swal.fire("info", "Diskualifikasi peserta akan dilakukan oleh sekretaris pertandingan", "info");
  },

  refresh_status_seni: function() {
    // Sync offline data if exists
    if (localStorage.getItem('offline_seni_' + juri.id_penampilan_seni)) {
      juri.sync_offline_data();
    }

    $.post(
      BASE_URL + "juri/refresh-status-seni/" + juri.id_penampilan_seni,
      { [CSRF_NAME]: CSRF_HASH },
      function(data) {
        if (data && data.csrf_hash) CSRF_HASH = data.csrf_hash;

        if ((data.status === true && data.reload === true) || data.data_nilai === null) {
          window.location.reload();
          return;
        }

        if (data.penampilan_seni && juri.penampilan_seni.format_penilaian !== data.penampilan_seni.format_penilaian) {
          window.location.reload();
          return;
        }

        if (data.data_nilai) {
          try {
            // Preserve local technical scoring
            var technicalScoring = juri.data_nilai.penilaian.unsur_nilai;

            // Take server-authoritative penalties and ringkasan
            var serverPenalties = data.data_nilai.penilaian.hukuman;
            var serverRingkasan = data.data_nilai.penilaian.ringkasan;

            juri.data_nilai.penilaian.hukuman = serverPenalties;
            juri.data_nilai.penilaian.ringkasan.total_hukuman = serverRingkasan.total_hukuman;
            juri.data_nilai.penilaian.ringkasan.nilai_akhir = serverRingkasan.nilai_akhir;

            // Restore local technical
            juri.data_nilai.penilaian.unsur_nilai = technicalScoring;
            juri.hitung_total_nilai();

            // Update UI
            juri.update_tampilan_nilai_akhir();

            if (data.penampilan_seni) {
              juri.update_akses_penilaian(data.penampilan_seni.akses_penilaian);
            }
          } catch(e) {
            console.error("Sync error:", e);
            window.location.reload();
          }
        }
      },
      "json"
    ).fail(function() {
      console.error("Failed to refresh jury status");
      juri.set_online_status(false);
    }).always(function() {
      setTimeout(function() {
        juri.refresh_status_seni();
      }, 2000);
    });
  },

  toggle_ready: function($btn) {
    var currentStatus = parseInt($($btn).attr('data-status') || '0');
    var newStatus = currentStatus === 1 ? 0 : 1;
    $($btn).prop('disabled', true);

    $.post(
      BASE_URL + 'juri/toggle-ready-seni/' + juri.id_penampilan_seni,
      { status_ready: newStatus, [CSRF_NAME]: CSRF_HASH },
      function(data) {
        if (data && data.csrf_hash) CSRF_HASH = data.csrf_hash;
        if (data && data.status === true) {
          $($btn).attr('data-status', newStatus);
          if (newStatus === 1) {
            $($btn).removeClass('btn-primary').addClass('btn-success')
              .find('.ready-icon').html('✅').end()
              .find('.ready-text').html('READY');
          } else {
            $($btn).removeClass('btn-success').addClass('btn-primary')
              .find('.ready-icon').html('🔵').end()
              .find('.ready-text').html('READY');
          }
        }
      },
      'json'
    ).fail(function() {
      console.error('Koneksi gagal saat toggle ready');
    }).always(function() {
      $($btn).prop('disabled', false);
    });
  },
};
