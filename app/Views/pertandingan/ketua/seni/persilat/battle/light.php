<?php
/**
 * View KP seni battle PERSILAT tema terang.
 * Identical structure to dark.php but with light styling overrides.
 */
?>
<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-seni.css') ?>">
<style>.kp-light-mode { background: var(--bg-color, #f4f6f9) !important; }</style>
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
    $sudutPeserta  = $penampilan->sudut ?? 'biru';
?>

<div class="container-fluid kp-light-mode min-vh-100 d-flex flex-column p-0"
     id="kp-seni-wrapper"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-endpoint-edit="<?= base_url('ketua-pertandingan/edit-penilaian-seni/' . $idPenampilan) ?>"
     data-endpoint-refresh="<?= base_url('ketua-pertandingan/refresh-status-seni/' . $idPenampilan) ?>"
     data-endpoint-akses="<?= base_url('ketua-pertandingan/ganti-akses-penilaian/' . $idPenampilan) ?>"
     data-endpoint-dq="<?= base_url('ketua-pertandingan/diskualifikasi-seni/' . $idPenampilan) ?>"
     data-endpoint-undq="<?= base_url('ketua-pertandingan/batalkan-diskualifikasi-seni/' . $idPenampilan) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>"
     data-akses="<?= esc($aksesStr, 'attr') ?>"
     data-sistem="battle">

    <!-- ═══ Header Bar ═══ -->
    <div class="kp-seni-header kp-seni-header-light">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= base_url('ketua-pertandingan') ?>" class="text-dark" title="Kembali">
                <i class="fas fa-arrow-left"></i>
            </a>
            <span class="badge bg-secondary"><?= esc($gelanggang) ?></span>
            <span class="text-muted small"><?= esc($kategori) ?></span>
            <span class="badge bg-info">Battle</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge <?= $aksesStr === 'dibuka' ? 'bg-success' : 'bg-danger' ?>" id="badge-akses">
                <?= $aksesStr === 'dibuka' ? 'DIBUKA' : 'DITUTUP' ?>
            </span>
            <a href="<?= base_url('ketua-pertandingan/seni/dark') ?>" class="btn btn-sm btn-outline-dark" title="Dark Mode">
                <i class="fas fa-moon"></i>
            </a>
        </div>
    </div>

    <!-- ═══ Peserta Info ═══ -->
    <div class="kp-seni-peserta kp-seni-peserta-light">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-gradient-180-<?= $sudutPeserta ?> text-white px-3 py-2 fs-6">
                <?= strtoupper($sudutPeserta) ?>
            </span>
            <div>
                <span class="peserta-nama text-dark"><?= esc($namaPeserta) ?></span>
                <span class="peserta-kontingen text-muted"><?= esc($kontingen) ?></span>
            </div>
        </div>
        <?php if ($isDQ): ?>
            <span class="badge bg-danger fs-6">DISKUALIFIKASI</span>
        <?php endif; ?>
    </div>

    <!-- ═══ Battle Tabs ═══ -->
    <div class="flex-grow-1 overflow-auto p-3">
        <ul class="nav nav-pills nav-fill mb-3" id="battleTabs">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-biru"><i class="fas fa-circle text-primary me-1"></i> Biru</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-merah"><i class="fas fa-circle text-danger me-1"></i> Merah</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-summary"><i class="fas fa-chart-bar me-1"></i> Ringkasan</button></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-biru">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-primary text-white"><h6 class="mb-0">Nilai Juri — Sudut Biru</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered text-center align-middle mb-0">
                            <thead class="table-light"><tr><th>#</th><th class="text-start">Juri</th><th>Nilai</th><th>Ready</th></tr></thead>
                            <tbody id="tbody-juri-biru">
                                <?php foreach ($data_nilai_juri as $idx => $juri): ?>
                                <tr><td><?= $idx + 1 ?></td><td class="text-start"><?= esc($juri->nama_perangkat ?? ('Juri ' . ($idx + 1))) ?></td><td class="fw-bold">-</td><td><i class="fas fa-clock text-muted"></i></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot><tr class="table-warning"><td colspan="2" class="text-end fw-bold">Median Biru</td><td class="fw-bold text-primary" id="median-biru">-</td><td></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-merah">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-danger text-white"><h6 class="mb-0">Nilai Juri — Sudut Merah</h6></div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered text-center align-middle mb-0">
                            <thead class="table-light"><tr><th>#</th><th class="text-start">Juri</th><th>Nilai</th><th>Ready</th></tr></thead>
                            <tbody id="tbody-juri-merah">
                                <?php foreach ($data_nilai_juri as $idx => $juri): ?>
                                <tr><td><?= $idx + 1 ?></td><td class="text-start"><?= esc($juri->nama_perangkat ?? ('Juri ' . ($idx + 1))) ?></td><td class="fw-bold">-</td><td><i class="fas fa-clock text-muted"></i></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot><tr class="table-warning"><td colspan="2" class="text-end fw-bold">Median Merah</td><td class="fw-bold text-danger" id="median-merah">-</td><td></td></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="tab-summary">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body text-center py-4">
                        <div class="d-flex justify-content-center align-items-center gap-5">
                            <div><div class="text-primary fs-3 fw-bold penilaian-display-font" id="summary-biru">0.00</div><small class="text-muted">Biru</small></div>
                            <div class="text-muted fs-4">VS</div>
                            <div><div class="text-danger fs-3 fw-bold penilaian-display-font" id="summary-merah">0.00</div><small class="text-muted">Merah</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hukuman -->
        <div class="card border-0 shadow-sm rounded-3 mt-3">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-triangle-exclamation me-2 text-warning"></i>Input Hukuman</h6></div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-5"><select class="form-select form-select-sm" id="select-jenis-hukuman"><option value="">-- Pilih Jenis --</option><option value="pengulangan_gerakan">Pengulangan Gerakan</option><option value="waktu">Waktu Lewat</option><option value="kostum">Kostum / Senjata</option><option value="keluar_arena">Keluar Arena</option><option value="lainnya">Lainnya</option></select></div>
                    <div class="col-md-3"><input type="number" class="form-control form-control-sm" id="input-jumlah-hukuman" placeholder="Jumlah" min="1" max="10" value="1"></div>
                    <div class="col-md-4"><button type="button" class="btn btn-warning btn-sm w-100 fw-bold" id="btn-tambah-hukuman"><i class="fas fa-plus me-1"></i> Tambah</button></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Actions -->
    <div class="kp-seni-actions kp-seni-actions-light">
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
