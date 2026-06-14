<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
.daftar-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.badge-belum  { background: #6c757d; }
.badge-berlangsung { background: #0d6efd; }
.badge-selesai { background: #198754; }
.badge-berhenti { background: #dc3545; }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'daftar_nilai_tanding']) ?>
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
                            <th>Babak</th>
                            <th>Atlet Biru</th>
                            <th>Atlet Merah</th>
                            <th>Skor (Biru – Merah)</th>
                            <th>Pemenang</th>
                            <th>Kemenangan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pertandinganList)): ?>
                        <tr>
                            <td colspan="9" class="text-muted py-4">
                                <i class="fas fa-info-circle me-2"></i>Belum ada data pertandingan di gelanggang ini.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($pertandinganList as $idx => $p): ?>
                        <?php
                            $statusClass = match($p->status_pertandingan ?? '') {
                                'berlangsung'  => 'badge-berlangsung',
                                'selesai'      => 'badge-selesai',
                                'berhenti'     => 'badge-berhenti',
                                default        => 'badge-belum',
                            };
                            $statusLabel = match($p->status_pertandingan ?? '') {
                                'belum_dimulai' => 'Belum Mulai',
                                'berlangsung'   => 'Berlangsung',
                                'selesai'       => 'Selesai',
                                'berhenti'      => 'Berhenti',
                                'standby'       => 'Standby',
                                'istirahat'     => 'Istirahat',
                                default         => ucfirst($p->status_pertandingan ?? '-'),
                            };
                            $pemenang = match($p->pemenang ?? '') {
                                'biru'  => '<span class="badge bg-primary">Biru</span>',
                                'merah' => '<span class="badge bg-danger">Merah</span>',
                                default => '-',
                            };
                        ?>
                        <tr>
                            <td><?= $idx + 1 ?></td>
                            <td class="fw-semibold">Partai <?= esc($p->nomor_partai) ?></td>
                            <td><small><?= esc($p->babak ?? '-') ?></small></td>
                            <td class="text-start">
                                <?= esc($p->nama_biru ?? '-') ?>
                                <?php if (!empty($p->kontingen_biru)): ?>
                                <br><small class="text-muted"><?= esc($p->kontingen_biru) ?></small>
                                <?php endif ?>
                            </td>
                            <td class="text-start">
                                <?= esc($p->nama_merah ?? '-') ?>
                                <?php if (!empty($p->kontingen_merah)): ?>
                                <br><small class="text-muted"><?= esc($p->kontingen_merah) ?></small>
                                <?php endif ?>
                            </td>
                            <td class="fw-bold"><?= (int)($p->skor_biru ?? 0) ?> – <?= (int)($p->skor_merah ?? 0) ?></td>
                            <td><?= $pemenang ?></td>
                            <td><small><?= esc($p->jenis_kemenangan ?? '-') ?></small></td>
                            <td><span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                        </tr>
                        <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Simple client-side search via DataTables (no AJAX — data already server-rendered)
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#tabel-nilai-tanding').DataTable({
            pageLength: 25,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [6, 7] }],
        });
    }
});
</script>
<?= $this->endSection() ?>
