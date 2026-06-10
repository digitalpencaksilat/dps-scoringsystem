<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
.daftar-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="daftar-container">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="fw-bold mb-1">Daftar Nilai Tanding</h5>
            <p class="text-muted mb-0 small">Rekap semua pertandingan di gelanggang ini</p>
        </div>
        <a href="<?= base_url('ketua-pertandingan') ?>" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-bordered text-center align-middle mb-0" id="tabel-nilai-tanding">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Partai</th>
                            <th>Atlet Biru</th>
                            <th>Atlet Merah</th>
                            <th>Skor</th>
                            <th>Pemenang</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data loaded via DataTables AJAX or server-rendered -->
                        <tr>
                            <td colspan="7" class="text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>Data akan dimuat dari server
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// TODO: Implement DataTables AJAX loading for daftar nilai tanding
// Endpoint: ketua-pertandingan/api/daftar-nilai-tanding (to be created)
</script>
<?= $this->endSection() ?>
