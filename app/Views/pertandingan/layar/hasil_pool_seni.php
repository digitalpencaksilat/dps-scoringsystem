<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar-seni.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="layar-hasil-wrapper theme-dark">
    <div class="layar-hasil-header">
        <h1 class="penilaian-display-font">HASIL PENILAIAN SENI</h1>
        <p class="layar-hasil-subtitle">Sistem Pool — Peringkat Akhir</p>
    </div>

    <div class="layar-hasil-table-container">
        <table class="layar-hasil-table">
            <thead>
                <tr>
                    <th class="text-center" style="width:80px">Peringkat</th>
                    <th>Peserta</th>
                    <th>Kontingen</th>
                    <th class="text-center" style="width:150px">Nilai Akhir</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($daftar)): ?>
                    <?php foreach ($daftar as $idx => $item): ?>
                        <tr class="<?= $idx === 0 ? 'rank-gold' : ($idx === 1 ? 'rank-silver' : ($idx === 2 ? 'rank-bronze' : '')) ?>">
                            <td class="text-center">
                                <span class="rank-badge penilaian-display-font"><?= $idx + 1 ?></span>
                            </td>
                            <td class="peserta-cell">
                                <span class="nama-peserta"><?= esc($item->nama_kelompok ?? $item->nama_pendaftar ?? '-') ?></span>
                            </td>
                            <td><?= esc($item->nama_kontingen ?? '-') ?></td>
                            <td class="text-center">
                                <span class="nilai-cell penilaian-display-font">
                                    <?= number_format((float)($item->nilai_akhir ?? 0), 3) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">Belum ada data penilaian</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="layar-hasil-footer">
        <a href="<?= base_url('layar/home') ?>" class="btn btn-outline-light btn-lg">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Auto-return to home after 30 seconds
setTimeout(() => { window.location.href = '<?= base_url('layar/home') ?>'; }, 30000);
</script>
<?= $this->endSection() ?>
