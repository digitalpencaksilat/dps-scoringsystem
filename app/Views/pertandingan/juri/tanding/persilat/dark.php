<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-tanding.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $totalRonde = (int) ($pertandingan->total_ronde ?? 3);
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';
    $skorMerah = (int) ($pertandingan->skor_merah ?? 0);
    $skorBiru  = (int) ($pertandingan->skor_biru ?? 0);
    // Force dark theme
    $isDark = true;
?>

<div class="container-fluid bg-black min-vh-100 d-flex flex-column p-0"
     id="juri-wrapper"
     data-id-pertandingan="<?= $idP ?>"
     data-ronde="<?= esc($ronde, 'attr') ?>"
     data-endpoint-edit="<?= base_url('juri/edit-penilaian-tanding/' . $idP) ?>"
     data-endpoint-refresh="<?= base_url('juri/refresh-status-pertandingan/' . $idP) ?>"
     data-endpoint-verifikasi="<?= base_url('juri/submit-jawaban-verifikasi/' . $idP) ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <!-- ═══ Header: Atlet + Skor ═══ -->
    <div class="card bg-dark card-container-juri border-0 rounded-0 m-0">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <!-- Atlet Biru -->
                <div class="text-center flex-grow-1" style="max-width: 30%;">
                    <div class="fw-bolder text-white text-truncate" style="font-size: 0.95rem;">
                        <?= esc($namaBiru) ?>
                    </div>
                    <small class="text-muted"><?= esc($kontBiru) ?></small>
                </div>

                <!-- Skor Center -->
                <div class="text-center">
                    <div class="d-flex align-items-center gap-3">
                        <span class="fw-bolder text-primary" style="font-size: 2.2rem;" id="skor-biru"><?= $skorBiru ?></span>
                        <div class="text-center">
                            <div class="badge bg-danger rounded-pill px-3 py-1 mb-1" style="font-size: 0.75rem;">
                                Ronde <?= esc($ronde) ?>
                            </div>
                            <div class="text-white" style="font-size: 0.7rem; letter-spacing: 1px;">PERSILAT</div>
                        </div>
                        <span class="fw-bolder text-danger" style="font-size: 2.2rem;" id="skor-merah"><?= $skorMerah ?></span>
                    </div>
                </div>

                <!-- Atlet Merah -->
                <div class="text-center flex-grow-1" style="max-width: 30%;">
                    <div class="fw-bolder text-white text-truncate" style="font-size: 0.95rem;">
                        <?= esc($namaMerah) ?>
                    </div>
                    <small class="text-muted"><?= esc($kontMerah) ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ Tabel Skor Per Ronde ═══ -->
    <div class="px-3 py-2">
        <table class="table table-sm table-bordered text-center align-middle mb-0 table-dark" style="font-size: 0.8rem;">
            <thead>
                <tr>
                    <th class="bg-gradient-180-blue text-white" style="width: 15%;">Biru</th>
                    <?php for ($r = 1; $r <= $totalRonde; $r++): ?>
                    <th class="text-white">R<?= $r ?></th>
                    <?php endfor; ?>
                    <th class="bg-gradient-180-red text-white" style="width: 15%;">Merah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold text-primary" id="total-biru">0</td>
                    <?php for ($r = 1; $r <= $totalRonde; $r++): ?>
                    <td class="text-white">
                        <span id="ronde-biru-<?= $r ?>">0</span> : <span id="ronde-merah-<?= $r ?>">0</span>
                    </td>
                    <?php endfor; ?>
                    <td class="fw-bold text-danger" id="total-merah">0</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ═══ Action Buttons (2 columns: Biru | Merah) ═══ -->
    <div class="flex-grow-1 d-flex">
        <!-- Sudut BIRU -->
        <div class="flex-grow-1 d-flex flex-column gap-2 p-2 bg-gradient-180-blue">
            <button type="button" class="btn-scoring btn-pukulan flex-grow-1" data-sudut="biru" data-nilai="1">
                <img src="<?= base_url('assets/images/icons/pukulan.png') ?>" alt="Pukulan" class="scoring-icon" onerror="this.style.display='none'">
                <span class="scoring-label">Pukulan</span>
                <span class="scoring-value penilaian-display-font">+1</span>
            </button>
            <button type="button" class="btn-scoring btn-tendangan flex-grow-1" data-sudut="biru" data-nilai="2">
                <img src="<?= base_url('assets/images/icons/tendangan.png') ?>" alt="Tendangan" class="scoring-icon" onerror="this.style.display='none'">
                <span class="scoring-label">Tendangan</span>
                <span class="scoring-value penilaian-display-font">+2</span>
            </button>
        </div>

        <!-- Sudut MERAH -->
        <div class="flex-grow-1 d-flex flex-column gap-2 p-2 bg-gradient-180-red">
            <button type="button" class="btn-scoring btn-pukulan flex-grow-1" data-sudut="merah" data-nilai="1">
                <img src="<?= base_url('assets/images/icons/pukulan.png') ?>" alt="Pukulan" class="scoring-icon" onerror="this.style.display='none'">
                <span class="scoring-label">Pukulan</span>
                <span class="scoring-value penilaian-display-font">+1</span>
            </button>
            <button type="button" class="btn-scoring btn-tendangan flex-grow-1" data-sudut="merah" data-nilai="2">
                <img src="<?= base_url('assets/images/icons/tendangan.png') ?>" alt="Tendangan" class="scoring-icon" onerror="this.style.display='none'">
                <span class="scoring-label">Tendangan</span>
                <span class="scoring-value penilaian-display-font">+2</span>
            </button>
        </div>
    </div>

    <!-- ═══ Hapus Button (bottom) ═══ -->
    <div class="d-flex">
        <button type="button" class="btn-hapus-scoring flex-grow-1" data-sudut="biru">
            <i class="fas fa-rotate-left me-1"></i> Hapus Biru
        </button>
        <button type="button" class="btn-hapus-scoring flex-grow-1 border-start" data-sudut="merah">
            <i class="fas fa-rotate-left me-1"></i> Hapus Merah
        </button>
    </div>
</div>

<!-- ═══ Modal Verifikasi Jatuhan ═══ -->
<div class="modal fade" id="modalVerifikasiJatuhan" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-warning">
            <div class="modal-header border-warning">
                <h5 class="modal-title"><i class="fas fa-gavel me-2"></i>Verifikasi Jatuhan</h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fs-5 mb-3">Apakah terjadi <strong>jatuhan</strong> pada sudut <span id="verifikasi-jatuhan-sudut" class="fw-bold text-uppercase"></span>?</p>
            </div>
            <div class="modal-footer border-warning justify-content-center gap-3">
                <button type="button" class="btn btn-success btn-lg px-5" data-jawaban="ya">
                    <i class="fas fa-check me-2"></i>Ya
                </button>
                <button type="button" class="btn btn-danger btn-lg px-5" data-jawaban="tidak">
                    <i class="fas fa-xmark me-2"></i>Tidak
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══ Modal Verifikasi Pelanggaran ═══ -->
<div class="modal fade" id="modalVerifikasiPelanggaran" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-warning">
            <div class="modal-header border-warning">
                <h5 class="modal-title"><i class="fas fa-triangle-exclamation me-2"></i>Verifikasi Pelanggaran</h5>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fs-5 mb-3">Apakah terjadi <strong>pelanggaran</strong> pada sudut <span id="verifikasi-pelanggaran-sudut" class="fw-bold text-uppercase"></span>?</p>
            </div>
            <div class="modal-footer border-warning justify-content-center gap-3">
                <button type="button" class="btn btn-success btn-lg px-5" data-jawaban="ya">
                    <i class="fas fa-check me-2"></i>Ya
                </button>
                <button type="button" class="btn btn-danger btn-lg px-5" data-jawaban="tidak">
                    <i class="fas fa-xmark me-2"></i>Tidak
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const JURI_TANDING_INIT = <?= json_encode($data_nilai) ?>;
    const JURI_TANDING_RONDE = <?= json_encode($ronde) ?>;
    const JURI_TANDING_TOTAL_RONDE = <?= $totalRonde ?>;
</script>
<script src="<?= base_url('assets/js/penilaian/juri_tanding_persilat.js') ?>"></script>
<?= $this->endSection() ?>
