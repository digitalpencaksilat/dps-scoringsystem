<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
.daftar-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
.badge-dq { background: #dc3545; }
.badge-ok { background: #198754; }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'daftar_nilai_seni']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="daftar-container">
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h5 class="fw-bold mb-1">Daftar Nilai Seni</h5>
            <p class="text-muted mb-0 small">Rekap semua penampilan seni di gelanggang ini</p>
        </div>
        <a href="<?= base_url('ketua-pertandingan') ?>" class="btn btn-sm btn-outline-danger">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <!-- Tab: Pool / Battle -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-pool" type="button">
                <i class="fas fa-list me-1"></i> Pool Seni
                <span class="badge bg-secondary ms-1"><?= count($poolList ?? []) ?></span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-battle" type="button">
                <i class="fas fa-swords me-1"></i> Battle Seni
                <span class="badge bg-secondary ms-1"><?= count($battleList ?? []) ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ── Pool ─────────────────────────────────────────── -->
        <div class="tab-pane fade show active" id="tab-pool" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered text-center align-middle mb-0" id="tabel-nilai-pool">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>Urut</th>
                                    <th class="text-start">Peserta / Kontingen</th>
                                    <th>Nilai Akhir</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($poolList)): ?>
                                <tr>
                                    <td colspan="5" class="text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Belum ada data pool seni.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($poolList as $idx => $row): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= esc($row->nomor_urut ?? '-') ?></td>
                                    <td class="text-start">
                                        <?= esc($row->nama_pendaftar ?? '-') ?>
                                        <?php if (!empty($row->nama_kontingen)): ?>
                                        <br><small class="text-muted"><?= esc($row->nama_kontingen) ?></small>
                                        <?php endif ?>
                                    </td>
                                    <td class="fw-bold">
                                        <?php if ($row->diskualifikasi): ?>
                                        <span class="badge badge-dq">DQ</span>
                                        <?php if (!empty($row->alasan_diskualifikasi)): ?>
                                        <br><small class="text-muted"><?= esc($row->alasan_diskualifikasi) ?></small>
                                        <?php endif ?>
                                        <?php else: ?>
                                        <?= number_format((float)($row->nilai_akhir ?? 0), 2) ?>
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $row->diskualifikasi ? 'badge-dq' : 'badge-ok' ?>">
                                            <?= $row->diskualifikasi ? 'DQ' : 'OK' ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach ?>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Battle ────────────────────────────────────────── -->
        <div class="tab-pane fade" id="tab-battle" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered text-center align-middle mb-0" id="tabel-nilai-battle">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>Babak</th>
                                    <th class="text-start">Peserta Biru</th>
                                    <th>Nilai Biru</th>
                                    <th class="text-start">Peserta Merah</th>
                                    <th>Nilai Merah</th>
                                    <th>Pemenang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($battleList)): ?>
                                <tr>
                                    <td colspan="7" class="text-muted py-4">
                                        <i class="fas fa-info-circle me-2"></i>Belum ada data battle seni.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($battleList as $idx => $row): ?>
                                <?php
                                    $nilaiBiru  = $row->dq_biru  ? '<span class="badge badge-dq">DQ</span>' : number_format((float)($row->nilai_biru  ?? 0), 2);
                                    $nilaiMerah = $row->dq_merah ? '<span class="badge badge-dq">DQ</span>' : number_format((float)($row->nilai_merah ?? 0), 2);
                                    $pemenang   = '-';
                                    if (!empty($row->id_biru) && !empty($row->id_merah)) {
                                        if (!$row->dq_biru && !$row->dq_merah) {
                                            if ((float)($row->nilai_biru ?? 0) > (float)($row->nilai_merah ?? 0)) {
                                                $pemenang = '<span class="badge bg-primary">Biru</span>';
                                            } elseif ((float)($row->nilai_merah ?? 0) > (float)($row->nilai_biru ?? 0)) {
                                                $pemenang = '<span class="badge bg-danger">Merah</span>';
                                            } else {
                                                $pemenang = '<span class="badge bg-warning text-dark">Seri</span>';
                                            }
                                        } elseif ($row->dq_biru) {
                                            $pemenang = '<span class="badge bg-danger">Merah</span>';
                                        } else {
                                            $pemenang = '<span class="badge bg-primary">Biru</span>';
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><small><?= esc($row->babak ?? '-') ?></small></td>
                                    <td class="text-start">
                                        <?= esc($row->nama_biru ?? '-') ?>
                                        <?php if (!empty($row->kontingen_biru)): ?>
                                        <br><small class="text-muted"><?= esc($row->kontingen_biru) ?></small>
                                        <?php endif ?>
                                    </td>
                                    <td class="fw-bold"><?= $nilaiBiru ?></td>
                                    <td class="text-start">
                                        <?= esc($row->nama_merah ?? '-') ?>
                                        <?php if (!empty($row->kontingen_merah)): ?>
                                        <br><small class="text-muted"><?= esc($row->kontingen_merah) ?></small>
                                        <?php endif ?>
                                    </td>
                                    <td class="fw-bold"><?= $nilaiMerah ?></td>
                                    <td><?= $pemenang ?></td>
                                </tr>
                                <?php endforeach ?>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /tab-content -->
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#tabel-nilai-pool').DataTable({
            pageLength: 25,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            order: [[1, 'asc']],
        });
        $('#tabel-nilai-battle').DataTable({
            pageLength: 25,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' },
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [6] }],
        });
    }
});
</script>
<?= $this->endSection() ?>
