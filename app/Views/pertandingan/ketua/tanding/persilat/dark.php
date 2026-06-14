<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-tanding.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'tanding']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP        = (int) $pertandingan->id_pertandingan;
    $ronde      = (string) $pertandingan->ronde_pertandingan;
    $totalRonde = (int) ($pertandingan->total_ronde ?? 3);
    $namaMerah  = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru   = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah  = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru   = $atlet_biru->nama_kontingen ?? '-';
    $fotoMerah  = $atlet_merah->foto ?? null;
    $fotoBiru   = $atlet_biru->foto ?? null;
    $semua      = $ringkasan->semua_ronde ?? null;
    $perRonde   = $ringkasan->per_ronde ?? null;

    // Data nilai juri (passed from controller)
    $dataJuri = $data_nilai['juri'] ?? [];
    $jumlahJuri = count($dataJuri);
?>

<div class="kp-wrapper kp-dark" id="kp-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-total-ronde="<?= $totalRonde ?>"
     data-endpoint-edit="<?= base_url('ketua-pertandingan/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('ketua-pertandingan/refresh-status-pertandingan/' . $idP) ?>"
     data-endpoint-verifikasi-create="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/create/' . $idP) ?>"
     data-endpoint-verifikasi-update="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/update/' . $idP) ?>"
     data-endpoint-verifikasi-jawaban="<?= base_url('ketua-pertandingan/verifikasi-pertandingan/get-jawaban/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-jumlah-juri="<?= $jumlahJuri ?>">

    <!-- ═══ Top Bar ═══ -->
    <header class="kp-topbar">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('ketua-pertandingan') ?>" class="text-white opacity-75" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="kp-ronde penilaian-display-font">R<?= esc($ronde) ?></span>
            <span class="kp-timer penilaian-display-font" id="kp-timer">--:--</span>
        </div>
        <span class="kp-title">KETUA PERTANDINGAN</span>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-light border-secondary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMatchInfo" title="Info Pertandingan">
                <i class="fas fa-info-circle"></i>
            </button>
            <a href="<?= base_url('ketua-pertandingan/tanding/light') ?>" class="btn btn-sm btn-outline-light border-secondary" title="Light Mode">
                <i class="fas fa-sun"></i>
            </a>
            <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="kp-logout" title="Keluar">
                <i class="fas fa-right-from-bracket"></i>
            </a>
        </div>
    </header>

    <!-- ═══ Header Atlet + Skor ═══ -->
    <div class="kp-header">
        <div class="kp-atlet kp-atlet-biru">
            <div class="kp-atlet-foto">
                <?php if ($fotoBiru): ?>
                    <img src="<?= base_url('uploads/foto/' . $fotoBiru) ?>" alt="<?= esc($namaBiru) ?>">
                <?php else: ?>
                    <img src="<?= base_url('assets/images/icon/siluette_atlet.png') ?>" alt="Atlet">
                <?php endif; ?>
            </div>
            <div class="kp-atlet-info">
                <div class="kp-atlet-nama"><?= esc($namaBiru) ?></div>
                <small class="kp-atlet-kontingen"><?= esc($kontBiru) ?></small>
            </div>
        </div>
        <div class="kp-skor-center">
            <div class="kp-skor-angka kp-skor-biru penilaian-display-font" id="skor-biru"><?= (int) $pertandingan->skor_biru ?></div>
            <div class="kp-skor-separator">
                <span class="kp-ronde-badge">R<?= esc($ronde) ?></span>
            </div>
            <div class="kp-skor-angka kp-skor-merah penilaian-display-font" id="skor-merah"><?= (int) $pertandingan->skor_merah ?></div>
        </div>
        <div class="kp-atlet kp-atlet-merah">
            <div class="kp-atlet-info text-end">
                <div class="kp-atlet-nama"><?= esc($namaMerah) ?></div>
                <small class="kp-atlet-kontingen"><?= esc($kontMerah) ?></small>
            </div>
            <div class="kp-atlet-foto">
                <?php if ($fotoMerah): ?>
                    <img src="<?= base_url('uploads/foto/' . $fotoMerah) ?>" alt="<?= esc($namaMerah) ?>">
                <?php else: ?>
                    <img src="<?= base_url('assets/images/icon/siluette_atlet.png') ?>" alt="Atlet">
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══ Tab Navigation (Monitor Nilai / Dewan Pertandingan) ═══ -->
    <ul class="nav nav-pills kp-nav-pills" id="kpMainTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-btn-monitor" data-bs-toggle="pill" data-bs-target="#tab-monitor" type="button" role="tab">
                <i class="fas fa-chart-line me-1"></i> Monitor Nilai
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-btn-dewan" data-bs-toggle="pill" data-bs-target="#tab-dewan" type="button" role="tab">
                <i class="fas fa-gavel me-1"></i> Dewan Pertandingan
            </button>
        </li>
    </ul>

    <div class="tab-content kp-tab-content" id="kpMainTabContent">

        <!-- ══════ TAB: Monitor Nilai ══════ -->
        <div class="tab-pane fade" id="tab-monitor" role="tabpanel">
            <!-- Sub-tabs for monitoring -->
            <ul class="nav nav-tabs kp-sub-tabs" id="monitorSubTab" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sub-allround" type="button">Semua Ronde</button></li>
                <?php for ($i = 1; $i <= $totalRonde; $i++): ?>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sub-ronde<?= $i ?>" type="button">Ronde <?= $i ?></button></li>
                <?php endfor; ?>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sub-hukuman" type="button">Hukuman</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sub-striking" type="button">Striking</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sub-verifikasi" type="button">Verifikasi</button></li>
            </ul>

            <div class="tab-content kp-monitor-content">
                <!-- All Round -->
                <div class="tab-pane fade show active" id="sub-allround">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm table-borderless kp-nilai-table">
                            <thead>
                                <tr>
                                    <th class="text-primary">Biru</th>
                                    <th class="text-muted">Label</th>
                                    <th class="text-danger">Merah</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-allround-body">
                                <!-- Juri Scores -->
                                <?php foreach ($dataJuri as $idx => $juri): ?>
                                <tr>
                                    <td class="text-primary fw-bold juri-<?= $juri->id_perangkat_pertandingan ?>-nilai-biru">0</td>
                                    <td class="text-muted small">Juri <?= $idx + 1 ?></td>
                                    <td class="text-danger fw-bold juri-<?= $juri->id_perangkat_pertandingan ?>-nilai-merah">0</td>
                                </tr>
                                <?php endforeach; ?>
                                <!-- Valid Score -->
                                <tr>
                                    <td class="text-primary val-cell semua-ronde-biru-nilai-verified">-</td>
                                    <td class="text-muted small">Valid Score</td>
                                    <td class="text-danger val-cell semua-ronde-merah-nilai-verified">-</td>
                                </tr>
                                <!-- Dropping -->
                                <tr>
                                    <td class="text-primary val-cell semua-ronde-biru-rincian-nilai-jatuhan">-</td>
                                    <td class="text-muted small">Dropping</td>
                                    <td class="text-danger val-cell semua-ronde-merah-rincian-nilai-jatuhan">-</td>
                                </tr>
                                <!-- Verbal Warning -->
                                <tr>
                                    <td class="text-primary val-cell semua-ronde-biru-binaan">-</td>
                                    <td class="text-muted small">Verbal Warning</td>
                                    <td class="text-danger val-cell semua-ronde-merah-binaan">-</td>
                                </tr>
                                <!-- Penalty -->
                                <tr>
                                    <td class="text-primary val-cell semua-ronde-biru-rincian-nilai-hukuman">-</td>
                                    <td class="text-muted small">Penalty</td>
                                    <td class="text-danger val-cell semua-ronde-merah-rincian-nilai-hukuman">-</td>
                                </tr>
                                <!-- Final Score -->
                                <tr class="final-row border-top border-secondary">
                                    <td class="text-primary fw-bolder text-center" id="total-biru-allround" style="font-size:1.1rem;">0</td>
                                    <td class="text-white fw-bold small">Final Score</td>
                                    <td class="text-danger fw-bolder text-center" id="total-merah-allround" style="font-size:1.1rem;">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Per Ronde -->
                <?php for ($i = 1; $i <= $totalRonde; $i++): ?>
                <div class="tab-pane fade" id="sub-ronde<?= $i ?>">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm table-borderless kp-nilai-table">
                            <thead>
                                <tr>
                                    <th>Juri</th>
                                    <th class="text-primary">Biru</th>
                                    <th class="text-danger">Merah</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-ronde-<?= $i ?>-body">
                                <?php foreach ($dataJuri as $idx => $juri): ?>
                                <tr>
                                    <td>Juri <?= $idx + 1 ?></td>
                                    <td class="text-primary ronde-<?= $i ?>-juri-<?= $juri->id_perangkat_pertandingan ?>-biru">0</td>
                                    <td class="text-danger ronde-<?= $i ?>-juri-<?= $juri->id_perangkat_pertandingan ?>-merah">0</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endfor; ?>

                <!-- Hukuman Summary (Dinamis dari ringkasan_nilai JSON) -->
                <div class="tab-pane fade" id="sub-hukuman">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm table-borderless kp-nilai-table">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th class="text-primary text-center">Biru</th>
                                    <th class="text-danger text-center">Merah</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-hukuman-body">
                                <?php if (isset($semua) && isset($semua->biru)): ?>
                                    <?php foreach ($semua->biru as $jenis => $nilai): ?>
                                        <?php if (!in_array($jenis, ['pukulan','tendangan','jatuhan','nilai_akhir'], true)): ?>
                                        <tr>
                                            <td><?= esc(ucwords(str_replace('_', ' ', $jenis))) ?></td>
                                            <td class="text-primary text-center ringkasan-biru-<?= esc($jenis) ?>"><?= (int) $nilai ?></td>
                                            <td class="text-danger text-center ringkasan-merah-<?= esc($jenis) ?>"><?= (int) ($semua->merah->{$jenis} ?? 0) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data hukuman</td></tr>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr class="kp-total-row border-top border-secondary">
                                    <td class="fw-bold">Total Hukuman</td>
                                    <td class="text-primary text-center fw-bold ringkasan-biru-total_hukuman">0</td>
                                    <td class="text-danger text-center fw-bold ringkasan-merah-total_hukuman">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Striking Summary (Dinamis: pukulan/tendangan/jatuhan) -->
                <div class="tab-pane fade" id="sub-striking">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm table-borderless kp-nilai-table">
                            <thead>
                                <tr>
                                    <th>Teknik</th>
                                    <th class="text-primary text-center">Biru</th>
                                    <th class="text-danger text-center">Merah</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-striking-body">
                                <?php if (isset($semua) && isset($semua->biru)): ?>
                                    <?php foreach ($semua->biru as $jenis => $nilai): ?>
                                        <?php if (in_array($jenis, ['pukulan','tendangan','jatuhan'], true)): ?>
                                        <tr>
                                            <td><?= esc(ucwords($jenis)) ?></td>
                                            <td class="text-primary text-center ringkasan-biru-<?= esc($jenis) ?>"><?= (int) $nilai ?></td>
                                            <td class="text-danger text-center ringkasan-merah-<?= esc($jenis) ?>"><?= (int) ($semua->merah->{$jenis} ?? 0) ?></td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center text-muted py-3">Belum ada data teknik</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Verifikasi History -->
                <div class="tab-pane fade" id="sub-verifikasi">
                    <div class="table-responsive">
                        <table class="table table-dark table-sm table-borderless kp-nilai-table">
                            <thead>
                                <tr>
                                    <th>Ronde</th>
                                    <th>Waktu</th>
                                    <th>Jenis</th>
                                    <?php for ($j = 1; $j <= $jumlahJuri; $j++): ?>
                                    <th>J<?= $j ?></th>
                                    <?php endfor; ?>
                                    <th>Hasil</th>
                                </tr>
                            </thead>
                            <tbody id="tabel-verifikasi-body">
                                <tr><td colspan="<?= 4 + $jumlahJuri ?>" class="text-center text-muted py-3">Belum ada riwayat verifikasi</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════ TAB: Dewan Pertandingan (Button Controller) ══════ -->
        <div class="tab-pane fade show active" id="tab-dewan" role="tabpanel">
            <div class="kp-dewan">
                <!-- Biru Panel -->
                <section class="kp-dewan-panel kp-dewan-biru">
                    <h3 class="kp-dewan-title">
                        <span class="kp-dot-biru"></span>
                        SUDUT BIRU
                    </h3>

                    <!-- Jatuhan -->
                    <div class="kp-dewan-group">
                        <button type="button" class="kp-big-btn kp-big-jatuhan" data-sudut="biru" data-mode="jatuhan" data-jumlah="3">
                            <i class="fas fa-person-falling"></i>
                            <span class="kp-big-label">Jatuhan Sah</span>
                            <span class="kp-big-value">+3</span>
                        </button>
                        <button type="button" class="kp-big-btn kp-big-hapus-jatuhan" data-sudut="biru" data-mode="jatuhan" data-jumlah="hapus">
                            <i class="fas fa-trash-can"></i>
                            <span class="kp-big-label">Hapus Jatuhan</span>
                        </button>
                    </div>

                    <!-- Binaan + Teguran + Peringatan Grid -->
                    <div class="kp-dewan-grid">
                        <button type="button" class="kp-icon-btn kp-btn-binaan" data-sudut="biru" data-mode="binaan_1" data-jumlah="1">
                            <img src="<?= base_url('assets/images/icon/binaan-1.png') ?>" alt="Binaan 1">
                            <span>Binaan 1</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-binaan" data-sudut="biru" data-mode="binaan_2" data-jumlah="2">
                            <img src="<?= base_url('assets/images/icon/binaan-2.png') ?>" alt="Binaan 2">
                            <span>Binaan 2</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-teguran" data-sudut="biru" data-mode="teguran_1" data-jumlah="-1">
                            <img src="<?= base_url('assets/images/icon/teguran-1.png') ?>" alt="Teguran 1">
                            <span>Teguran 1</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-teguran" data-sudut="biru" data-mode="teguran_2" data-jumlah="-2">
                            <img src="<?= base_url('assets/images/icon/teguran-2.png') ?>" alt="Teguran 2">
                            <span>Teguran 2</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-peringatan" data-sudut="biru" data-mode="peringatan_1" data-jumlah="-5">
                            <img src="<?= base_url('assets/images/icon/peringatan-1.png') ?>" alt="Peringatan 1">
                            <span>Peringatan 1</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-peringatan" data-sudut="biru" data-mode="peringatan_2" data-jumlah="-10">
                            <img src="<?= base_url('assets/images/icon/peringatan-2.png') ?>" alt="Peringatan 2">
                            <span>Peringatan 2</span>
                        </button>
                    </div>

                    <!-- Rekap -->
                    <div class="kp-rekap" id="rekap-biru">
                        <?php $rb = $semua->biru ?? null; ?>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Teguran</span><span class="kp-rekap-val rk-teguran"><?= ($rb->teguran_1 ?? 0) + ($rb->teguran_2 ?? 0) ?></span></div>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Peringatan</span><span class="kp-rekap-val rk-peringatan"><?= ($rb->peringatan_1 ?? 0) + ($rb->peringatan_2 ?? 0) ?></span></div>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Jatuhan</span><span class="kp-rekap-val rk-jatuhan"><?= $rb->jatuhan ?? 0 ?></span></div>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Binaan</span><span class="kp-rekap-val rk-binaan"><?= ($rb->binaan_1 ?? 0) + ($rb->binaan_2 ?? 0) ?></span></div>
                    </div>
                </section>

                <!-- Center: Verifikasi Buttons -->
                <section class="kp-dewan-center">
                    <button type="button" class="kp-verif-btn kp-verif-jatuhan" id="btn-verifikasi-jatuhan">
                        <i class="fas fa-person-falling"></i>
                        <span>Verifikasi<br>Jatuhan</span>
                    </button>
                    <button type="button" class="kp-verif-btn kp-verif-pelanggaran" id="btn-verifikasi-pelanggaran">
                        <i class="fas fa-triangle-exclamation"></i>
                        <span>Verifikasi<br>Pelanggaran</span>
                    </button>
                </section>

                <!-- Merah Panel -->
                <section class="kp-dewan-panel kp-dewan-merah">
                    <h3 class="kp-dewan-title">
                        <span class="kp-dot-merah"></span>
                        SUDUT MERAH
                    </h3>

                    <!-- Jatuhan -->
                    <div class="kp-dewan-group">
                        <button type="button" class="kp-big-btn kp-big-jatuhan" data-sudut="merah" data-mode="jatuhan" data-jumlah="3">
                            <i class="fas fa-person-falling"></i>
                            <span class="kp-big-label">Jatuhan Sah</span>
                            <span class="kp-big-value">+3</span>
                        </button>
                        <button type="button" class="kp-big-btn kp-big-hapus-jatuhan" data-sudut="merah" data-mode="jatuhan" data-jumlah="hapus">
                            <i class="fas fa-trash-can"></i>
                            <span class="kp-big-label">Hapus Jatuhan</span>
                        </button>
                    </div>

                    <!-- Binaan + Teguran + Peringatan Grid -->
                    <div class="kp-dewan-grid">
                        <button type="button" class="kp-icon-btn kp-btn-binaan" data-sudut="merah" data-mode="binaan_1" data-jumlah="1">
                            <img src="<?= base_url('assets/images/icon/binaan-1.png') ?>" alt="Binaan 1">
                            <span>Binaan 1</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-binaan" data-sudut="merah" data-mode="binaan_2" data-jumlah="2">
                            <img src="<?= base_url('assets/images/icon/binaan-2.png') ?>" alt="Binaan 2">
                            <span>Binaan 2</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-teguran" data-sudut="merah" data-mode="teguran_1" data-jumlah="-1">
                            <img src="<?= base_url('assets/images/icon/teguran-1.png') ?>" alt="Teguran 1">
                            <span>Teguran 1</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-teguran" data-sudut="merah" data-mode="teguran_2" data-jumlah="-2">
                            <img src="<?= base_url('assets/images/icon/teguran-2.png') ?>" alt="Teguran 2">
                            <span>Teguran 2</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-peringatan" data-sudut="merah" data-mode="peringatan_1" data-jumlah="-5">
                            <img src="<?= base_url('assets/images/icon/peringatan-1.png') ?>" alt="Peringatan 1">
                            <span>Peringatan 1</span>
                        </button>
                        <button type="button" class="kp-icon-btn kp-btn-peringatan" data-sudut="merah" data-mode="peringatan_2" data-jumlah="-10">
                            <img src="<?= base_url('assets/images/icon/peringatan-2.png') ?>" alt="Peringatan 2">
                            <span>Peringatan 2</span>
                        </button>
                    </div>

                    <!-- Rekap -->
                    <div class="kp-rekap" id="rekap-merah">
                        <?php $rm = $semua->merah ?? null; ?>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Teguran</span><span class="kp-rekap-val rk-teguran"><?= ($rm->teguran_1 ?? 0) + ($rm->teguran_2 ?? 0) ?></span></div>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Peringatan</span><span class="kp-rekap-val rk-peringatan"><?= ($rm->peringatan_1 ?? 0) + ($rm->peringatan_2 ?? 0) ?></span></div>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Jatuhan</span><span class="kp-rekap-val rk-jatuhan"><?= $rm->jatuhan ?? 0 ?></span></div>
                        <div class="kp-rekap-item"><span class="kp-rekap-label">Binaan</span><span class="kp-rekap-val rk-binaan"><?= ($rm->binaan_1 ?? 0) + ($rm->binaan_2 ?? 0) ?></span></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Offcanvas: Match Info ═══ -->
<div class="offcanvas offcanvas-start kp-offcanvas" tabindex="-1" id="offcanvasMatchInfo">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="fas fa-info-circle me-2"></i>Informasi Pertandingan</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="list-group list-group-flush kp-info-list">
            <li class="list-group-item"><strong>Gelanggang</strong><span><?= esc($pertandingan->nama_gelanggang ?? $pertandingan->nomor_gelanggang ?? '-') ?></span></li>
            <li class="list-group-item"><strong>Partai</strong><span>#<?= esc($pertandingan->nomor_partai ?? '-') ?></span></li>
            <li class="list-group-item"><strong>Kategori</strong><span><?= esc($pertandingan->nama_kategori_usia ?? '-') ?> - <?= esc(ucfirst($pertandingan->jenis_kelamin ?? '')) ?></span></li>
            <li class="list-group-item"><strong>Kelas</strong><span><?= esc($pertandingan->label ?? '') ?> (<?= $pertandingan->berat_minimal ?? '' ?>–<?= $pertandingan->berat_maksimal ?? '' ?> kg)</span></li>
            <li class="list-group-item"><strong>Babak</strong><span><?= esc(ucfirst($pertandingan->babak ?? '-')) ?></span></li>
            <li class="list-group-item"><strong>Ronde</strong><span><?= esc($ronde) ?> / <?= $totalRonde ?></span></li>
        </ul>
    </div>
</div>

<!-- ═══ Modal Verifikasi Jatuhan (Fullscreen with Juri Cards) ═══ -->
<div class="modal fade" id="modalVerifikasiJatuhan" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content kp-verifikasi-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-person-falling me-2 text-warning"></i>Verifikasi Jatuhan</h5>
                <span class="kp-verif-status" id="verif-jatuhan-status">Menunggu jawaban juri...</span>
            </div>
            <div class="modal-body">
                <div class="kp-juri-cards" id="juri-cards-jatuhan">
                    <?php foreach ($dataJuri as $idx => $juri): ?>
                    <div class="kp-juri-card" id="card-jatuhan-juri-<?= $juri->id_perangkat_pertandingan ?>">
                        <div class="kp-juri-card-header">Juri <?= $idx + 1 ?></div>
                        <div class="kp-juri-card-body">
                            <span class="kp-juri-answer">Menunggu...</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer kp-verif-footer">
                <button type="button" class="btn btn-lg kp-verif-valid-biru" data-jawaban="biru">
                    <i class="fas fa-check me-2"></i>Jatuhan Valid (Biru)
                </button>
                <button type="button" class="btn btn-lg kp-verif-invalid" data-jawaban="invalid">
                    <i class="fas fa-xmark me-2"></i>TIDAK VALID
                </button>
                <button type="button" class="btn btn-lg kp-verif-valid-merah" data-jawaban="merah">
                    <i class="fas fa-check me-2"></i>Jatuhan Valid (Merah)
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg" data-jawaban="batal">
                    <i class="fas fa-ban me-2"></i>Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Verifikasi Pelanggaran ═══ -->
<div class="modal fade" id="modalVerifikasiPelanggaran" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content kp-verifikasi-modal">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-triangle-exclamation me-2 text-warning"></i>Verifikasi Pelanggaran</h5>
                <span class="kp-verif-status" id="verif-pelanggaran-status">Menunggu jawaban juri...</span>
            </div>
            <div class="modal-body">
                <div class="kp-juri-cards" id="juri-cards-pelanggaran">
                    <?php foreach ($dataJuri as $idx => $juri): ?>
                    <div class="kp-juri-card" id="card-pelanggaran-juri-<?= $juri->id_perangkat_pertandingan ?>">
                        <div class="kp-juri-card-header">Juri <?= $idx + 1 ?></div>
                        <div class="kp-juri-card-body">
                            <span class="kp-juri-answer">Menunggu...</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer kp-verif-footer">
                <button type="button" class="btn btn-lg kp-verif-valid-biru" data-jawaban="biru">
                    <i class="fas fa-check me-2"></i>Pelanggaran Valid (Biru)
                </button>
                <button type="button" class="btn btn-lg kp-verif-invalid" data-jawaban="invalid">
                    <i class="fas fa-xmark me-2"></i>TIDAK VALID
                </button>
                <button type="button" class="btn btn-lg kp-verif-valid-merah" data-jawaban="merah">
                    <i class="fas fa-check me-2"></i>Pelanggaran Valid (Merah)
                </button>
                <button type="button" class="btn btn-outline-secondary btn-lg" data-jawaban="batal">
                    <i class="fas fa-ban me-2"></i>Batalkan
                </button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    // Init data from PHP
    const KP_INIT = {
        dataNilai: <?= json_encode($data_nilai ?? new stdClass()) ?>,
        pertandingan: <?= json_encode($pertandingan) ?>,
        ringkasan: <?= json_encode($ringkasan ?? new stdClass()) ?>,
        verifikasiBerlangsung: <?= json_encode($verifikasi_berlangsung ?? null) ?>,
        riwayatVerifikasi: <?= json_encode($riwayat_verifikasi ?? []) ?>,
    };
</script>
<script src="<?= base_url('assets/js/penilaian/kp_tanding.js') ?>"></script>
<?= $this->endSection() ?>
