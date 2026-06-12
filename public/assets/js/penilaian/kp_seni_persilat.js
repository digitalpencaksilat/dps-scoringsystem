/**
 * KP Seni PERSILAT — Monitoring Nilai (Pool & Battle)
 * Parity legacy: ketua_pertandingan/seni/persilat.js
 */
const ketua_pertandingan = {
  id_penampilan_seni_berlangsung: null,
  penampilan_seni_berlangsung: null,
  semua_penampilan_seni: null,
  data_nilai: null,

  init: function($id_penampilan_seni, $data_nilai, $penampilan_seni_berlangsung, $semua_penampilan_seni, $autorefresh) {
    ketua_pertandingan.set_variable($id_penampilan_seni, $data_nilai, $penampilan_seni_berlangsung, $semua_penampilan_seni);
    ketua_pertandingan.update_tampilan_nilai($data_nilai);
    if ($autorefresh === true) {
      ketua_pertandingan.refresh_status_seni();
    }
  },

  set_variable: function($id_penampilan_seni, $data_nilai, $penampilan_seni_berlangsung, $semua_penampilan_seni) {
    ketua_pertandingan.id_penampilan_seni_berlangsung = $id_penampilan_seni;
    ketua_pertandingan.data_nilai = $data_nilai;
    ketua_pertandingan.penampilan_seni_berlangsung = $penampilan_seni_berlangsung;
    if ($semua_penampilan_seni) {
      ketua_pertandingan.semua_penampilan_seni = $semua_penampilan_seni;
    }
  },

  update_tampilan_nilai: function($data_nilai) {
    // Diskualifikasi badge
    if (ketua_pertandingan.penampilan_seni_berlangsung.diskualifikasi == 1) {
      $(".keterangan_" + ketua_pertandingan.id_penampilan_seni_berlangsung)
        .html('<span class="badge bg-danger">Diskualifikasi</span>');
    } else {
      $(".keterangan_" + ketua_pertandingan.id_penampilan_seni_berlangsung).html(" ");
    }

    // Update nilai_akhir per juri + highlight terpilih
    $.each($data_nilai, function(id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function(index_juri, penilaian_juri) {
        var $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;

        // Determine highlight color
        var $warna_highlight = "bg-warning";
        if ($(".penampilan_seni_" + id_penampilan_seni).hasClass("blue-corner")) {
          $warna_highlight = "bg-blue text-white";
        } else if ($(".penampilan_seni_" + id_penampilan_seni).hasClass("red-corner")) {
          $warna_highlight = "bg-red text-white";
        }

        // Nilai akhir per juri
        $(".penampilan_seni_" + id_penampilan_seni + " .nilai_akhir_juri_" + penilaian_juri.id_perangkat_pertandingan)
          .html($penilaian.ringkasan.nilai_akhir)
          .removeClass($warna_highlight);
      });
    });

    ketua_pertandingan.update_tampilan_urutan_nilai_tiap_juri($data_nilai);
    ketua_pertandingan.update_tampilan_unsur_nilai($data_nilai);
    ketua_pertandingan.update_tampilan_jenis_hukuman($data_nilai);
    ketua_pertandingan.update_tampilan_panel_input_hukuman($data_nilai);
    ketua_pertandingan.update_ringkasan_nilai(ketua_pertandingan.penampilan_seni_berlangsung);
    ketua_pertandingan.pilih_penilaian_juri($data_nilai);
    ketua_pertandingan.hitung_dan_tampilkan_median_kebenaran();
    ketua_pertandingan.hitung_dan_tampilkan_median_kebenaran_battle();
    ketua_pertandingan.hitung_dan_tampilkan_statistik();
  },

  update_tampilan_panel_input_hukuman: function($data_nilai) {
    var $sampel_nilai = $data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung];
    if (!$sampel_nilai || $sampel_nilai.length === 0) return;

    var $parsed = JSON.parse($sampel_nilai[0].penilaian).penilaian;
    if (!$parsed.hukuman) return;

    $.each($parsed.hukuman, function(jenis_hukuman, hukuman) {
      var detail = hukuman.detail_hukuman;
      if (hukuman.tipe == "pilihan ganda" || hukuman.tipe == "satu kali") {
        $(".penampilan_seni_" + ketua_pertandingan.id_penampilan_seni_berlangsung + " .nilai_hukuman_" + jenis_hukuman)
          .html(detail.nilai_hukuman);
      } else if (hukuman.tipe == "repetisi") {
        $(".penampilan_seni_" + ketua_pertandingan.id_penampilan_seni_berlangsung + " .jumlah_repetisi_" + jenis_hukuman)
          .html(detail.jumlah_repetisi);
        $(".penampilan_seni_" + ketua_pertandingan.id_penampilan_seni_berlangsung + " .nilai_hukuman_" + jenis_hukuman)
          .html(detail.nilai_hukuman);
      }
    });
    $(".penampilan_seni_" + ketua_pertandingan.id_penampilan_seni_berlangsung + " .total_hukuman")
      .html($parsed.ringkasan.total_hukuman);
  },

  update_tampilan_urutan_nilai_tiap_juri: function($data_nilai) {
    var allPenampilan = ketua_pertandingan.semua_penampilan_seni;
    if (!allPenampilan) return;

    $.each(allPenampilan, function(index, penampilan_seni) {
      var $kumpulan_nilai = $data_nilai[penampilan_seni.id_penampilan_seni];
      if (!$kumpulan_nilai) return;

      var urutan_total_nilai = [];
      for (var key in $kumpulan_nilai) {
        var $total_nilai_per_juri = JSON.parse($kumpulan_nilai[key].penilaian).penilaian.ringkasan.total_nilai;
        urutan_total_nilai.push([
          $kumpulan_nilai[key].id_perangkat_pertandingan,
          $total_nilai_per_juri,
          parseInt(key) + 1
        ]);
      }
      urutan_total_nilai.sort(function(a, b) { return a[1] - b[1]; });

      $.each($(".kolom_total_nilai_" + penampilan_seni.id_penampilan_seni), function(i, element) {
        if (urutan_total_nilai[i]) {
          $(element).find(".nomor_juri").html("Juri " + urutan_total_nilai[i][2]);
          $(element).find(".kolom_bobot_total_nilai").empty().append(
            '<p class="fw-bolder text-center text-white my-1 h5 total_nilai_juri_' + urutan_total_nilai[i][0] +
            ' juri_' + urutan_total_nilai[i][0] + '">' + urutan_total_nilai[i][1] + '</p>'
          );
        }
      });

      // Pool mode uses .kolom_total_nilai (no ID suffix)
      if ($(".kolom_total_nilai_" + penampilan_seni.id_penampilan_seni).length === 0) {
        $.each($(".penampilan_seni_sorted .kolom_total_nilai"), function(i, element) {
          if (urutan_total_nilai[i]) {
            $(element).find(".nomor_juri").html("Juri " + urutan_total_nilai[i][2]);
            $(element).find(".kolom_bobot_total_nilai").empty().append(
              '<p class="fw-bolder text-center text-white my-1 h5 total_nilai_juri_' + urutan_total_nilai[i][0] +
              ' juri_' + urutan_total_nilai[i][0] + '">' + urutan_total_nilai[i][1] + '</p>'
            );
          }
        });
      }
    });
  },

  update_ringkasan_nilai: function($penampilan_seni_berlangsung) {
    $.each(ketua_pertandingan.semua_penampilan_seni, function(index, penampilan_seni) {
      if (penampilan_seni.catatan_nilai_sama && penampilan_seni.catatan_nilai_sama !== "") {
        var $catatan = JSON.parse(penampilan_seni.catatan_nilai_sama);
        $.each($catatan, function(i, val) {
          $("." + i + "_" + penampilan_seni.id_penampilan_seni).html(Number(val).toFixed(6));
        });
      }

      // Nilai akhir
      if (penampilan_seni.nilai_akhir == null) {
        $(".nilai_akhir_" + penampilan_seni.id_penampilan_seni).html("0");
      } else {
        $(".nilai_akhir_" + penampilan_seni.id_penampilan_seni).html(Number(penampilan_seni.nilai_akhir).toFixed(3));
      }

      // Waktu tampil
      var waktu = parseInt(penampilan_seni.waktu_tampil) || 0;
      var menit = Math.floor(waktu / 60).toString().padStart(2, '0');
      var detik = (waktu % 60).toString().padStart(2, '0');
      $(".waktu_" + penampilan_seni.id_penampilan_seni).html(menit + ":" + detik);
    });
  },

  pilih_penilaian_juri: function($data_nilai) {
    $.each($data_nilai, function(id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function(index_juri, penilaian_juri) {
        var $warna_highlight = "bg-warning text-white";
        if ($(".penampilan_seni_" + id_penampilan_seni).hasClass("blue-corner")) {
          $warna_highlight = "bg-blue text-white";
        } else if ($(".penampilan_seni_" + id_penampilan_seni).hasClass("red-corner")) {
          $warna_highlight = "bg-red text-white";
        }

        if (penilaian_juri.terpilih == 1) {
          $(".penampilan_seni_" + id_penampilan_seni + " .juri_" + penilaian_juri.id_perangkat_pertandingan)
            .addClass($warna_highlight);
        } else {
          $(".penampilan_seni_" + id_penampilan_seni + " .juri_" + penilaian_juri.id_perangkat_pertandingan)
            .removeClass($warna_highlight);
        }
      });
    });
  },

  update_tampilan_unsur_nilai: function($data_nilai) {
    // Unsur nilai per juri
    $.each($data_nilai, function(id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function(index_juri, penilaian_juri) {
        var $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        $.each($penilaian.unsur_nilai, function(jenis_unsur_nilai, unsur_nilai) {
          $(".penampilan_seni_" + id_penampilan_seni + " ." + jenis_unsur_nilai + "_juri_" + penilaian_juri.id_perangkat_pertandingan)
            .html(unsur_nilai.nilai_diperoleh.toFixed(2));
        });
      });
    });

    // catatan_nilai_sama
    $.each(ketua_pertandingan.semua_penampilan_seni, function(index, penampilan_seni) {
      if (penampilan_seni.catatan_nilai_sama && penampilan_seni.catatan_nilai_sama !== "") {
        var $catatan = JSON.parse(penampilan_seni.catatan_nilai_sama);
        var $id = penampilan_seni.id_penampilan_seni;
        $(".penampilan_seni_" + $id + " .catatan_nilai_sama").empty();
        $.each($catatan, function(index_nilai, nilai) {
          $(".penampilan_seni_" + $id + " .catatan_nilai_sama").append(
            '<span class="d-block">' + index_nilai + " = " + nilai + "</span>"
          );
          $(".penampilan_seni_" + $id + " ." + index_nilai).empty()
            .append('<span class="d-block text-end">' + nilai + "</span>");
        });
      }
    });

    // Total nilai + total hukuman per juri
    $.each($data_nilai, function(id_penampilan_seni, penampilan_seni) {
      $.each(penampilan_seni, function(index_juri, penilaian_juri) {
        var $penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        $(".penampilan_seni_" + id_penampilan_seni + " .total_nilai_juri_" + penilaian_juri.id_perangkat_pertandingan)
          .html($penilaian.ringkasan.total_nilai);
        $(".penampilan_seni_" + id_penampilan_seni + " .total_hukuman_juri_" + penilaian_juri.id_perangkat_pertandingan)
          .html($penilaian.ringkasan.total_hukuman);
      });
    });
  },

  update_tampilan_jenis_hukuman: function($data_nilai) {
    var $total_hukuman = {};
    $.each($data_nilai, function(id_penampilan_seni, penampilan_seni) {
      $total_hukuman[id_penampilan_seni] = { rincian_hukuman: {}, total_hukuman: 0 };

      $.each(penampilan_seni, function(index_juri, penilaian_juri) {
        var $jenis_hukuman = JSON.parse(penilaian_juri.penilaian).penilaian.hukuman;
        if (!$jenis_hukuman) return;
        $.each($jenis_hukuman, function(nama_hukuman, data_hukuman) {
          if ($total_hukuman[id_penampilan_seni]["rincian_hukuman"][nama_hukuman] === undefined) {
            $total_hukuman[id_penampilan_seni]["rincian_hukuman"][nama_hukuman] = 0;
          }
          $total_hukuman[id_penampilan_seni]["rincian_hukuman"][nama_hukuman] += data_hukuman.detail_hukuman.nilai_hukuman;
        });
        $total_hukuman[id_penampilan_seni]["total_hukuman"] += JSON.parse(penilaian_juri.penilaian).penilaian.ringkasan.total_hukuman;
      });
    });

    $.each($total_hukuman, function(id_penampilan_seni, data_hukuman) {
      $.each(data_hukuman["rincian_hukuman"], function(nama_hukuman, nilai_hukuman) {
        $(".penampilan_seni_" + id_penampilan_seni + " .hukuman_" + nama_hukuman).html(nilai_hukuman);
      });
      $(".penampilan_seni_" + id_penampilan_seni + " .hukuman_" + id_penampilan_seni).html(data_hukuman["total_hukuman"]);
      // Also update the generic .hukuman_ class in stats
      $(".hukuman_" + id_penampilan_seni).not("[class*='hukuman_']").html(data_hukuman["total_hukuman"]);
    });
  },

  hitung_dan_tampilkan_median_kebenaran: function() {
    var id = ketua_pertandingan.id_penampilan_seni_berlangsung;
    var dataJuri = ketua_pertandingan.data_nilai[id];
    if (!dataJuri || dataJuri.length === 0) return;

    var nilaiKebenaran = [];
    $.each(dataJuri, function(i, penilaian_juri) {
      var penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
      if (penilaian.unsur_nilai && penilaian.unsur_nilai.kebenaran &&
          penilaian.unsur_nilai.kebenaran.nilai_diperoleh !== undefined) {
        nilaiKebenaran.push(parseFloat(penilaian.unsur_nilai.kebenaran.nilai_diperoleh));
      }
    });

    if (nilaiKebenaran.length === 0) return;
    nilaiKebenaran.sort(function(a, b) { return a - b; });

    var mid = Math.floor(nilaiKebenaran.length / 2);
    var median = nilaiKebenaran.length % 2 === 0
      ? (nilaiKebenaran[mid - 1] + nilaiKebenaran[mid]) / 2
      : nilaiKebenaran[mid];

    $(".kebenaran_median_" + id).html(median.toFixed(3));
  },

  hitung_dan_tampilkan_median_kebenaran_battle: function() {
    var semuaPenampilan = ketua_pertandingan.semua_penampilan_seni;
    var dataNilai = ketua_pertandingan.data_nilai;
    if (!semuaPenampilan || !dataNilai) return;

    $.each(semuaPenampilan, function(idx, penampilan) {
      var id = penampilan.id_penampilan_seni;
      var dataJuri = dataNilai[id];
      if (!dataJuri || dataJuri.length === 0) return;

      var nilaiKebenaran = [];
      $.each(dataJuri, function(i, penilaian_juri) {
        if (!penilaian_juri.penilaian) return;
        var penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        if (penilaian.unsur_nilai && penilaian.unsur_nilai.kebenaran &&
            penilaian.unsur_nilai.kebenaran.nilai_diperoleh !== undefined &&
            penilaian.unsur_nilai.kebenaran.nilai_diperoleh !== null) {
          nilaiKebenaran.push(parseFloat(penilaian.unsur_nilai.kebenaran.nilai_diperoleh));
        }
      });

      if (nilaiKebenaran.length === 0) return;
      nilaiKebenaran.sort(function(a, b) { return a - b; });

      var mid = Math.floor(nilaiKebenaran.length / 2);
      var median = nilaiKebenaran.length % 2 === 0
        ? (nilaiKebenaran[mid - 1] + nilaiKebenaran[mid]) / 2
        : nilaiKebenaran[mid];

      $(".kebenaran_median_" + id).html(median.toFixed(3));
    });
  },

  /**
   * Hitung dan tampilkan: Median total_nilai, Total Penalty, Standar Deviasi
   * untuk penampilan berlangsung + semua penampilan (pool/battle).
   */
  hitung_dan_tampilkan_statistik: function() {
    var semuaPenampilan = ketua_pertandingan.semua_penampilan_seni;
    var dataNilai = ketua_pertandingan.data_nilai;
    if (!semuaPenampilan || !dataNilai) return;

    $.each(semuaPenampilan, function(idx, penampilan) {
      var id = penampilan.id_penampilan_seni;
      var dataJuri = dataNilai[id];
      if (!dataJuri || dataJuri.length === 0) return;

      var totalNilaiList = [];
      var totalHukuman = 0;

      $.each(dataJuri, function(i, penilaian_juri) {
        if (!penilaian_juri.penilaian) return;
        var penilaian = JSON.parse(penilaian_juri.penilaian).penilaian;
        if (penilaian.ringkasan) {
          totalNilaiList.push(parseFloat(penilaian.ringkasan.total_nilai || 0));
          // Hukuman same across all juri (KP sets identically)
          totalHukuman = parseFloat(penilaian.ringkasan.total_hukuman || 0);
        }
      });

      if (totalNilaiList.length === 0) return;

      // Median total_nilai
      totalNilaiList.sort(function(a, b) { return a - b; });
      var count = totalNilaiList.length;
      var mid = Math.floor(count / 2);
      var medianTotalNilai = count % 2 === 0
        ? (totalNilaiList[mid - 1] + totalNilaiList[mid]) / 2
        : totalNilaiList[mid];

      // Standar deviasi
      var sum = 0;
      for (var i = 0; i < count; i++) sum += totalNilaiList[i];
      var mean = sum / count;
      var sumSquares = 0;
      for (var j = 0; j < count; j++) {
        sumSquares += Math.pow(totalNilaiList[j] - mean, 2);
      }
      var stdDev = Math.sqrt(sumSquares / count);

      // Tampilkan
      $(".median_" + id).html(medianTotalNilai.toFixed(3));
      $(".hukuman_" + id).html(totalHukuman.toFixed(2));
      $(".standar_deviasi_" + id).html(stdDev.toFixed(4));
    });
  },

  refresh_status_seni: function() {
    $.post(
      "ketua-pertandingan/refresh-status-seni/" + ketua_pertandingan.id_penampilan_seni_berlangsung,
      function(data) {
        if (data.status === true && data.reload === true) {
          location.reload();
        } else if (data.status === false && typeof data.data_nilai !== "undefined") {
          // Check if juri count changed → reload
          var currentJuriCount = ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung]
            ? ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung].length : 0;
          var newJuriCount = data.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung]
            ? data.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung].length : 0;

          if (currentJuriCount !== newJuriCount) {
            window.location.reload();
          } else {
            ketua_pertandingan.set_variable(
              data.penampilan_seni_berlangsung.id_penampilan_seni,
              data.data_nilai,
              data.penampilan_seni_berlangsung,
              data.semua_penampilan_seni
            );
            ketua_pertandingan.update_tampilan_nilai(data.data_nilai);

            // Update ready juri indicator
            if (data.status_ready_juri) {
              ketua_pertandingan.update_tampilan_ready_juri(data.status_ready_juri);
            }
          }
        } else {
          // No active penampilan — switch to summary tab
          if ($("#summaryNav").length > 0) {
            document.getElementById("summaryNav").click();
          }
        }
      },
      "json"
    ).always(function() {
      setTimeout(function() {
        ketua_pertandingan.refresh_status_seni();
      }, 3000);
    });
  },

  update_tampilan_ready_juri: function(status_ready_juri) {
    var $container = $('#monitor-ready-juri');
    if ($container.length === 0 || !status_ready_juri || status_ready_juri.length === 0) return;

    var html = '';
    var allReady = true;

    $.each(status_ready_juri, function(i, juri) {
      var isReady = parseInt(juri.status_ready) === 1;
      if (!isReady) allReady = false;

      var namaJuri = juri.nama ? juri.nama : 'Juri ' + (i + 1);
      var badgeClass = isReady ? 'bg-success' : 'bg-primary';
      var badgeIcon = isReady ? '✅' : '🔵';
      var badgeText = isReady ? 'Ready' : 'Ready?';

      html += '<div class="d-flex align-items-center justify-content-between mb-2 px-2 py-2 rounded" ' +
              'style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);">' +
              '<span class="text-white small fw-semibold text-truncate me-2" style="max-width: 60%;" title="' + namaJuri + '">' +
              namaJuri + '</span>' +
              '<span class="badge ' + badgeClass + ' px-2 py-1" style="font-size: 0.7rem; white-space: nowrap;">' +
              badgeIcon + ' ' + badgeText + '</span></div>';
    });

    var totalReady = status_ready_juri.filter(function(j) { return parseInt(j.status_ready) === 1; }).length;
    var summaryClass = allReady ? 'text-success' : 'text-warning';
    html += '<div class="mt-2 pt-2 border-top border-secondary text-center">' +
      '<small class="' + summaryClass + ' fw-bold">' +
      (allReady ? '✅ Semua Juri Ready' : totalReady + ' / ' + status_ready_juri.length + ' Juri Ready') +
      '</small></div>';

    $container.html(html);
  },

  // ═══════════════════════════════════════════════════════════════════════════
  // BUTTON CONTROLLER (Dewan Seni) — parity legacy
  // ═══════════════════════════════════════════════════════════════════════════

  /**
   * Edit hukuman single penalty.
   * Parity legacy: ketua_pertandingan.edit_hukuman($jenis_hukuman, $data, $element)
   */
  edit_hukuman: function($jenis_hukuman, $data, $element) {
    var $sampel_nilai = ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung][0].penilaian;
    var $parsed_sampel = JSON.parse($sampel_nilai).penilaian;

    if (!$parsed_sampel.hukuman[$jenis_hukuman]) {
      console.error("Invalid penalty type");
      return false;
    }

    var $hukuman = $parsed_sampel.hukuman[$jenis_hukuman];
    var $seluruh_nilai_juri = ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung];

    try {
      if ($data.nilai_hukuman === "reset") {
        return ketua_pertandingan._reset_single_penalty($jenis_hukuman, $hukuman, $seluruh_nilai_juri, $element);
      }
      return ketua_pertandingan._add_penalty($jenis_hukuman, $data, $hukuman, $seluruh_nilai_juri, $element);
    } catch (error) {
      console.error("Error editing penalty:", error);
      return false;
    }
  },

  /**
   * Helper: reset a single penalty type.
   */
  _reset_single_penalty: function($jenis_hukuman, $hukuman, $seluruh_nilai_juri, $element) {
    if ($hukuman.detail_hukuman.nilai_hukuman === 0) {
      return false;
    }

    $.each($seluruh_nilai_juri, function(index_juri, penilaian_juri) {
      var $penilaian = JSON.parse(penilaian_juri.penilaian);

      $penilaian.penilaian.ringkasan.total_hukuman -= $hukuman.detail_hukuman.nilai_hukuman;
      $penilaian.penilaian.ringkasan.nilai_akhir =
        $penilaian.penilaian.ringkasan.total_nilai - $penilaian.penilaian.ringkasan.total_hukuman;

      if ($hukuman.tipe === "pilihan ganda") {
        $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.terpilih = "";
      }
      $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman = 0;
      if ($hukuman.tipe === "repetisi") {
        $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.jumlah_repetisi = 0;
      }

      ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung][index_juri].penilaian = JSON.stringify($penilaian);
    });

    ketua_pertandingan.edit_penilaian_seni($element);
    return true;
  },

  /**
   * Helper: add a penalty value.
   */
  _add_penalty: function($jenis_hukuman, $data, $hukuman, $seluruh_nilai_juri, $element) {
    if ($hukuman.tipe === "repetisi" && $hukuman.detail_hukuman.jumlah_repetisi + $data.jumlah_repetisi < 0) {
      return false;
    }

    $.each($seluruh_nilai_juri, function(index_juri, penilaian_juri) {
      var $penilaian = JSON.parse(penilaian_juri.penilaian);

      var penaltyValue = $data.nilai_hukuman;
      if ($hukuman.tipe === "repetisi") {
        penaltyValue = $data.jumlah_repetisi * $hukuman.detail_hukuman.faktor_pengali;
      }

      $penilaian.penilaian.ringkasan.total_hukuman += penaltyValue;
      $penilaian.penilaian.ringkasan.nilai_akhir =
        $penilaian.penilaian.ringkasan.total_nilai - $penilaian.penilaian.ringkasan.total_hukuman;

      if ($hukuman.tipe === "pilihan ganda") {
        $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.terpilih = $data.terpilih;
      }
      if ($hukuman.tipe === "repetisi") {
        $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.jumlah_repetisi += $data.jumlah_repetisi;
      }
      $penilaian.penilaian.hukuman[$jenis_hukuman].detail_hukuman.nilai_hukuman += penaltyValue;

      ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung][index_juri].penilaian = JSON.stringify($penilaian);
    });

    ketua_pertandingan.edit_penilaian_seni($element);
    return true;
  },

  /**
   * Reset semua hukuman.
   * Parity legacy: ketua_pertandingan.reset_semua_hukuman($element)
   */
  reset_semua_hukuman: function($element) {
    var $seluruh_nilai_juri = ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung];

    if (!$seluruh_nilai_juri || $seluruh_nilai_juri.length === 0) {
      console.error("No jury data found");
      return false;
    }

    try {
      $.each($seluruh_nilai_juri, function(index_juri, penilaian_juri) {
        var $penilaian_per_juri = JSON.parse(penilaian_juri.penilaian);
        var $daftar_hukuman = $penilaian_per_juri.penilaian.hukuman;
        var originalTotalNilai = $penilaian_per_juri.penilaian.ringkasan.total_nilai;

        $.each($daftar_hukuman, function(jenis_hukuman, isi_jenis_hukuman) {
          switch (isi_jenis_hukuman.tipe) {
            case "pilihan ganda":
              $penilaian_per_juri.penilaian.hukuman[jenis_hukuman].detail_hukuman = {
                nilai_hukuman: 0,
                terpilih: ""
              };
              break;
            case "repetisi":
              $penilaian_per_juri.penilaian.hukuman[jenis_hukuman].detail_hukuman = {
                nilai_hukuman: 0,
                jumlah_repetisi: 0,
                faktor_pengali: isi_jenis_hukuman.detail_hukuman.faktor_pengali
              };
              break;
            case "satu kali":
              $penilaian_per_juri.penilaian.hukuman[jenis_hukuman].detail_hukuman = {
                nilai_hukuman: 0,
                faktor_pengali: isi_jenis_hukuman.detail_hukuman.faktor_pengali
              };
              break;
          }
        });

        $penilaian_per_juri.penilaian.ringkasan.total_hukuman = 0;
        $penilaian_per_juri.penilaian.ringkasan.nilai_akhir = originalTotalNilai;

        ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung][index_juri].penilaian = JSON.stringify($penilaian_per_juri);
      });

      ketua_pertandingan.edit_penilaian_seni($element);
      return true;
    } catch (error) {
      console.error("Error resetting penalties:", error);
      return false;
    }
  },

  /**
   * Submit penilaian seni ke server.
   * Parity legacy: ketua_pertandingan.edit_penilaian_seni($element)
   */
  edit_penilaian_seni: function($element) {
    $($element).prop("disabled", true);
    $.post(
      "ketua-pertandingan/edit-penilaian-seni/" + ketua_pertandingan.id_penampilan_seni_berlangsung,
      {
        data_nilai: JSON.stringify(
          ketua_pertandingan.data_nilai[ketua_pertandingan.id_penampilan_seni_berlangsung]
        )
      },
      function(data) {
        if (data.status == true) {
          ketua_pertandingan.set_variable(
            data.penampilan_seni_berlangsung.id_penampilan_seni,
            data.data_nilai,
            data.penampilan_seni_berlangsung,
            data.semua_penampilan_seni
          );
          ketua_pertandingan.update_tampilan_nilai(data.data_nilai);
          $($element).removeAttr("disabled");
        } else {
          console.log("gagal update nilai");
          $($element).removeAttr("disabled");
        }
      },
      "json"
    );
  },

  /**
   * Ganti akses penilaian (lock/unlock scoring).
   * Parity legacy: ketua_pertandingan.ganti_akses_penilaian($value)
   */
  ganti_akses_penilaian: function($value) {
    $.post(
      "ketua-pertandingan/ganti-akses-penilaian/" + ketua_pertandingan.id_penampilan_seni_berlangsung,
      { akses_penilaian: $value },
      function(data) {
        if ($("#btn-toggle-akses-penilaian").length > 0) {
          var $btn = $("#btn-toggle-akses-penilaian");
          if (data.akses_penilaian == "dibuka") {
            $btn.removeClass("btn-success").addClass("btn-danger");
            $btn.find("span").html("Lock Scoring");
            $btn.attr("onclick", "ketua_pertandingan.ganti_akses_penilaian('ditutup')");
          } else {
            $btn.removeClass("btn-danger").addClass("btn-success");
            $btn.find("span").html("Unlock Scoring");
            $btn.attr("onclick", "ketua_pertandingan.ganti_akses_penilaian('dibuka')");
          }
        }
      },
      "json"
    );
  },

  /**
   * Diskualifikasi peserta.
   * Parity legacy: ketua_pertandingan.diskualifikasi_peserta()
   */
  diskualifikasi_peserta: function() {
    Swal.fire({
      title: "Warning !",
      text: "Final score will be set to 0",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, Disqualify participants !"
    }).then(function(result) {
      if (result.value) {
        $.post(
          "ketua-pertandingan/diskualifikasi-seni/" + ketua_pertandingan.id_penampilan_seni_berlangsung,
          function(data) {
            $(".btn-diskualifikasi").fadeOut("fast", function() {
              $(".btn-batal-diskualifikasi").fadeIn();
            });
          },
          "json"
        );
      }
    });
  },

  /**
   * Batalkan diskualifikasi.
   * Parity legacy: ketua_pertandingan.batalkan_diskualifikasi_peserta()
   */
  batalkan_diskualifikasi_peserta: function() {
    Swal.fire({
      title: "Perhatian !",
      text: "Detailed score will be restored",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, Restore!"
    }).then(function(result) {
      if (result.value) {
        $.post(
          "ketua-pertandingan/batalkan-diskualifikasi-seni/" + ketua_pertandingan.id_penampilan_seni_berlangsung,
          function(data) {
            if (data.status == true) {
              $(".btn_selesai, .btn-timer").removeAttr("disabled");
            } else {
              Swal.fire("Error", data.message, "error");
            }
            $(".btn-batal-diskualifikasi").fadeOut("fast", function() {
              $(".btn-diskualifikasi").fadeIn();
            });
          },
          "json"
        );
      }
    });
  }
};
