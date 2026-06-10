<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idPenampilan  = (int) $penampilan->id_penampilan_seni;
    $namaPeserta   = $penampilan->nama_peserta ?? $penampilan->nama_kontingen ?? 'Peserta';
    $kontingen     = $penampilan->nama_kontingen ?? '-';
    $kategori      = ($penampilan->nama_seni ?? '') . ' - ' . ($penampilan->nama_kategori_usia ?? '');
    $gelanggang    = $penampilan->nama_gelanggang ?? 'Gelanggang';
    $aksesStr      = $akses_penilaian ?? 'dibuka';
    $isDQ          = (int) ($penampilan->diskualifikasi ?? 0);
?>

<div class="container-fluid bg-black min-vh-100 d-flex flex-column p-0"
     id="kp-seni-wrapper"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-endpoint-edit="<?= base_url('ketua-pertandingan/edit-penilaian-seni/' . $idPenampilan) ?>"
     data-endpoint-refresh="<?= base_url('ketua-pertandingan/refresh-status-seni/' . $idPenampilan) ?>"
     data-endpoint-akses="<?= base_url('ketua-pertandingan/ganti-akses-penilaian/' . $idPenampilan) ?>"
     data-endpoint-dq="<?= base_url('ketua-pertandingan/diskualifikasi-seni/' . $idPenampilan) ?>"
     data-endpoint-undq="<?= base_url('ketua-pertandingan/batalkan-diskualifikasi-seni/' . $idPenampilan) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-akses="<?= esc($aksesStr, 'attr') ?>">

    <!-- ═══ Header Bar ═══ -->
    <div class="kp-seni-header">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('ketua-pertandingan') ?>" class="text-white opacity-75" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="badge bg-dark border border-secondary"><?= esc($gelanggang) ?></span>
            <span class="kp-seni-kategori"><?= esc($kategori) ?></span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge <?= $aksesStr === 'dibuka' ? 'bg-success' : 'bg-danger' ?>" id="badge-akses">
                <?= $aksesStr === 'dibuka' ? 'DIBUKA' : 'DITUTUP' ?>
            </span>
            <a href="<?= base_url('ketua-pertandingan/seni/light') ?>" class="btn btn-sm btn-outline-light border-secondary" title="Light Mode">
                <i class="fas fa-sun"></i>
            </a>
        </div>
    </div>

    <!-- ═══ Peserta Info ═══ -->
    <div class="kp-seni-peserta">
        <div>
            <span class="peserta-nama"><?= esc($namaPeserta) ?></span>
            <span class="peserta-kontingen"><?= esc($kontingen) ?></span>
        </div>
        <?php if ($isDQ): ?>
            <span class="badge bg-danger fs-6">DISKUALIFIKASI</span>
        <?php endif; ?>
    </div>

    <!-- ═══ Tabel Skor Juri (Pool System) ═══ -->
    <div class="flex-grow-1 overflow-auto p-3">
        <div class="card bg-dark border-0 rounded-3">
            <div class="card-header border-secondary d-flex align-items-center justify-content-between">
                <h6 class="text-white mb-0"><i class="fas fa-users me-2"></i>Rekap Nilai Per Juri</h6>
                <span class="badge bg-secondary" id="juri-ready-count">0 / <?= count($data_nilai_juri) ?> Ready</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-sm table-bordered text-center align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 40px;">#</th>
                                <th class="text-start">Juri</th>
                                <th>Nilai Akhir</th>
                                <th style="width: 60px;">Ready</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-juri">
                            <?php foreach ($data_nilai_juri as $idx => $juri): ?>
                            <tr data-id-perangkat="<?= (int) $juri->id_perangkat_pertandingan ?>">
                                <td><?= $idx + 1 ?></td>
                                <td class="text-start"><?= esc($juri->nama_perangkat ?? ('Juri ' . ($idx + 1))) ?></td>
                                <td class="fw-bold penilaian-display-font juri-nilai"><?= number_format((float) ($juri->nilai_akhir_per_juri ?? 0), 2) ?></td>
                                <td>
                                    <?php if ((int) ($juri->status_ready ?? 0)): ?>
                                        <i class="fas fa-check-circle text-success"></i>
                                    <?php else: ?>
                                        <i class="fas fa-clock text-muted"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <td colspan="2" class="text-end fw-bold">Median (Nilai Akhir)</td>
                                <td class="fw-bold penilaian-display-font text-warning" id="median-display">-</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- ═══ Hukuman Input ═══ -->
        <div class="card bg-dark border-0 rounded-3 mt-3">
            <div class="card-header border-secondary">
                <h6 class="text-white mb-0"><i class="fas fa-triangle-exclamation me-2 text-warning"></i>Input Hukuman</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-5">
                        <select class="form-select form-select-sm bg-dark text-white border-secondary" id="select-jenis-hukuman">
                            <option value="">-- Pilih Jenis Hukuman --</option>
                            <option value="pengulangan_gerakan">Pengulangan Gerakan</option>
                            <option value="waktu">Waktu Lewat</option>
                            <option value="kostum">Kostum / Senjata</option>
                            <option value="keluar_arena">Keluar Arena</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control form-control-sm bg-dark text-white border-secondary"
                               id="input-jumlah-hukuman" placeholder="Jumlah" min="1" max="10" value="1">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-warning btn-sm w-100 fw-bold" id="btn-tambah-hukuman">
                            <i class="fas fa-plus me-1"></i> Tambah Hukuman
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Bottom Action Bar ═══ -->
    <div class="kp-seni-actions">
        <button type="button" class="btn-kp-action btn-akses" id="btn-toggle-akses">
            <i class="fas <?= $aksesStr === 'dibuka' ? 'fa-lock' : 'fa-lock-open' ?>"></i>
            <span><?= $aksesStr === 'dibuka' ? 'Tutup Penilaian' : 'Buka Penilaian' ?></span>
        </button>
        <button type="button" class="btn-kp-action btn-dq" id="btn-diskualifikasi">
            <i class="fas fa-ban"></i>
            <span><?= $isDQ ? 'Batalkan DQ' : 'Diskualifikasi' ?></span>
        </button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/kp_seni.js') ?>"></script>
<?= $this->endSection() ?>
