<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/kp-seni.css') ?>">
<style>
/* Penalty row styling — parity legacy kp_seni_custom.css */
.penalty-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    gap: 1rem;
}
.penalty-label {
    color: #fff;
    font-weight: 600;
    font-size: 0.9rem;
    min-width: 180px;
}
.penalty-label small { color: #aaa; font-weight: 400; }
.penalty-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}
.btn-reset-single {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px;
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
}
.btn-reset-single:hover { background: rgba(255,255,255,0.15); }
.btn-penalty-value {
    min-width: 60px;
    font-weight: 700;
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
    border-radius: 6px;
}
.penalty-current-value {
    background: #212529;
    color: #fff;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 0.4rem 1rem;
    border-radius: 6px;
    min-width: 50px;
    text-align: center;
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid bg-black min-vh-100 px-4 penampilan_seni_<?= $penampilan_seni_berlangsung->id_penampilan_seni ?>">

    <!-- HEADER -->
    <div class="row shadow-lg mb-3">
        <div class="col-12 col-md-4 bg-dark py-2 d-flex align-items-center justify-content-center">
            <p class="h5 my-1 text-white fw-bolder">
                <?= $penampilan_seni_berlangsung->nama_seni ?? 'Seni' ?> —
                <?= $penampilan_seni_berlangsung->nama_kategori_usia ?? '' ?>
                <?= ($penampilan_seni_berlangsung->jenis_kelamin ?? '') === 'Putra' ? 'Putra' : 'Putri' ?>
            </p>
        </div>
        <div class="col-12 col-md-4 bg-warning d-flex justify-content-center flex-column py-2">
            <p class="h5 text-truncate m-0 fw-bolder text-center text-white">
                <?= str_replace('<br>', ' ', $penampilan_seni_berlangsung->anggota_kelompok_peserta_seni ?? '-') ?>
            </p>
            <p class="text-truncate m-0 text-sm text-center text-white">
                <?= $penampilan_seni_berlangsung->nama_kontingen ?? '' ?>
            </p>
        </div>
        <div class="col-12 col-md-4 bg-dark d-flex justify-content-center flex-column py-2">
            <p class="h6 fw-bolder text-uppercase m-0 text-white text-center">
                <?= ucfirst($penampilan_seni_berlangsung->sistem_penampilan ?? 'pool') ?> —
                Pool <?= $penampilan_seni_berlangsung->nomor_pool ?? '' ?>
            </p>
        </div>
    </div>

    <div class="row">
        <!-- LEFT SIDEBAR: Akses + Total Penalties + Ready Juri -->
        <div class="col-lg-3">
            <!-- Tombol Akses Penilaian -->
            <div class="card bg-dark border-0 mb-3 shadow-none">
                <div class="card-body p-3">
                    <?php
                        $isOpen = ($penampilan_seni_berlangsung->akses_penilaian ?? 'dibuka') === 'dibuka';
                        $btnClass = $isOpen ? 'btn-danger' : 'btn-success';
                        $btnText = $isOpen ? 'Lock Scoring' : 'Unlock Scoring';
                        $nextAction = $isOpen ? 'ditutup' : 'dibuka';
                    ?>
                    <div class="row h-100 align-content-center">
                        <div class="col-12">
                            <button id="btn-toggle-akses-penilaian"
                                class="btn <?= $btnClass ?> w-100 text-white d-flex align-items-center justify-content-center shadow-lg rounded-3 border-0 px-1"
                                style="height: 80px;"
                                onclick="ketua_pertandingan.ganti_akses_penilaian('<?= $nextAction ?>')">
                                <span class="h5 text-uppercase fw-bolder m-0 p-0 text-white ls-1 text-center" style="white-space: nowrap;"><?= $btnText ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Penalties -->
            <div class="card bg-dark border-0 mb-3 shadow-none">
                <div class="card-body p-3 text-center">
                    <h2 class="text-white mb-3 fw-bolder">Total Penalties</h2>
                    <div class="bg-white rounded py-2">
                        <h1 class="display-1 fw-bolder mb-0 text-dark total_hukuman" style="font-size: 5rem;">0</h1>
                    </div>
                </div>
            </div>

            <!-- Status Ready Juri -->
            <div class="card bg-dark border-0 mb-3 shadow-none">
                <div class="card-body p-3">
                    <h6 class="text-white fw-bolder mb-3 text-uppercase ls-1" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                        <i class="fas fa-circle-check me-2 text-success"></i>Status Kesiapan Juri
                    </h6>
                    <div id="monitor-ready-juri">
                        <div class="d-flex align-items-center justify-content-center py-3">
                            <div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>
                            <small class="text-secondary">Memuat data...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Input Penalties -->
        <div class="col-lg-9">
            <div class="card bg-dark border-0 shadow-none min-vh-75">
                <div class="card-header bg-transparent py-3 border-0">
                    <h2 class="card-title text-white fw-bolder mb-0">Input Penalties</h2>
                </div>
                <div class="card-body pt-0">
                    <!-- FORM INPUT HUKUMAN (parity legacy) -->
                    <?php
                        $idPsBerlangsung = (int) $penampilan_seni_berlangsung->id_penampilan_seni;
                        $sampelJsonHukuman = null;
                        if (!empty($data_nilai[$idPsBerlangsung]) && isset($data_nilai[$idPsBerlangsung][0]->penilaian)) {
                            $parsedSampel = json_decode($data_nilai[$idPsBerlangsung][0]->penilaian);
                            $sampelJsonHukuman = $parsedSampel->penilaian->hukuman ?? null;
                        }
                    ?>
                    <?php if ($sampelJsonHukuman !== null): ?>
                    <div class="row mb-3">
                        <div class="col-12 px-0">
                            <?php foreach ($sampelJsonHukuman as $jenisHukuman => $valueHukuman): ?>
                            <div class="penalty-row">
                                <div class="penalty-label">
                                    <?php
                                        $label = str_replace("(", "<br><small>(", $valueHukuman->metadata->label ?? ucwords(str_replace('_', ' ', $jenisHukuman)));
                                        $label = str_replace(")", ")</small>", $label);
                                        echo $label;
                                    ?>
                                </div>
                                <div class="penalty-actions">
                                    <!-- Reset Button -->
                                    <?php if ($valueHukuman->tipe == 'pilihan ganda'): ?>
                                        <button class="btn btn-reset-single text-white"
                                            onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'terpilih' : '', 'nilai_hukuman' : 'reset'}, this)">
                                            <i class="fa fa-undo"></i> Reset
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-reset-single text-white"
                                            onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'nilai_hukuman' : 'reset'}, this)">
                                            <i class="fa fa-undo"></i> Reset
                                        </button>
                                    <?php endif; ?>

                                    <!-- Penalty Value Buttons -->
                                    <?php if ($valueHukuman->tipe == 'pilihan ganda'): ?>
                                        <div class="d-flex gap-2">
                                            <?php foreach ($valueHukuman->detail_hukuman->pilihan as $key => $value): ?>
                                                <?php if ($value == 'disk'): ?>
                                                    <button class="btn btn-danger btn-penalty-value btn_hukuman_<?= $jenisHukuman ?>"
                                                        onclick="ketua_pertandingan.diskualifikasi_peserta()">
                                                        <?= $key ?>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-warning btn-penalty-value btn_hukuman_<?= $jenisHukuman ?>"
                                                        onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'terpilih' : '<?= $key ?>', 'nilai_hukuman' : <?= $value ?>}, this)">
                                                        <?= $key ?>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php elseif ($valueHukuman->tipe == 'repetisi'): ?>
                                        <button class="btn btn-danger btn-penalty-value btn_hukuman_<?= $jenisHukuman ?>"
                                            onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'jumlah_repetisi' : 1}, this)">
                                            -<?= $valueHukuman->detail_hukuman->faktor_pengali ?>
                                        </button>
                                    <?php elseif ($valueHukuman->tipe == 'satu kali'): ?>
                                        <button class="btn btn-danger btn-penalty-value btn_hukuman_<?= $jenisHukuman ?>"
                                            onclick="ketua_pertandingan.edit_hukuman('<?= $jenisHukuman ?>', {'nilai_hukuman' : <?= $valueHukuman->detail_hukuman->faktor_pengali ?>}, this)">
                                            -<?= $valueHukuman->detail_hukuman->faktor_pengali ?>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Current Value Display -->
                                    <div class="d-flex gap-1 align-items-center">
                                        <?php if ($valueHukuman->tipe == 'repetisi'): ?>
                                            <div class="penalty-current-value jumlah_repetisi_<?= $jenisHukuman ?>" style="display:none;">
                                                <?= $valueHukuman->detail_hukuman->jumlah_repetisi ?? 0 ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="penalty-current-value nilai_hukuman_<?= $jenisHukuman ?>">
                                            <?= $valueHukuman->detail_hukuman->nilai_hukuman ?? 0 ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <p class="text-secondary h5">Tidak ada data hukuman tersedia.<br>Pastikan ada juri yang sudah menilai.</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer: Disqualify + Reset All -->
                <div class="card-footer bg-transparent border-0 pt-0 pb-4 px-4">
                    <div class="d-flex gap-3">
                        <button <?= (($penampilan_seni_berlangsung->diskualifikasi ?? 0) == 1) ? '' : 'style="display:none;"' ?>
                            class="btn btn-lg btn-info py-3 fs-5 btn-batal-diskualifikasi flex-fill fw-bolder"
                            onclick="ketua_pertandingan.batalkan_diskualifikasi_peserta()">
                            Cancel Disqualification
                        </button>
                        <button <?= (($penampilan_seni_berlangsung->diskualifikasi ?? 0) == 0) ? '' : 'style="display:none;"' ?>
                            class="btn btn-lg btn-warning py-3 btn-diskualifikasi fs-5 fw-bolder flex-fill"
                            onclick="ketua_pertandingan.diskualifikasi_peserta()">
                            Disqualify Participant
                        </button>
                        <button class="btn btn-success btn-lg text-dark py-3 fs-5 fw-bolder flex-fill"
                            onclick="ketua_pertandingan.reset_semua_hukuman(this)">
                            Reset all penalties
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/kp_seni_persilat.js') ?>"></script>
<script>
    var $data_nilai = <?= json_encode($data_nilai ?? [], JSON_NUMERIC_CHECK) ?>;
    var $penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung, JSON_NUMERIC_CHECK) ?>;
    var $semua_penampilan_seni = <?= json_encode($semua_penampilan_seni ?? [], JSON_NUMERIC_CHECK) ?>;
    var $autorefresh = true;

    $(document).ready(function() {
        ketua_pertandingan.init(
            <?= $penampilan_seni_berlangsung->id_penampilan_seni ?>,
            $data_nilai,
            $penampilan_seni_berlangsung,
            $semua_penampilan_seni,
            $autorefresh
        );
    });
</script>
<?= $this->endSection() ?>
