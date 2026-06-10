<div class="table-responsive">
    <table class="table align-middle">
        <thead><tr><th>Partai</th><th>Jenis</th><th>Kontingen</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
            <?php foreach ($daftar_seni as $s) : ?>
                <tr <?= (int) ($s->id_penampilan_seni ?? 0) === $idPS ? 'class="table-active"' : '' ?>>
                    <td class="fw-bold"><?= esc($s->nomor_partai) ?></td>
                    <td class="small"><?= esc(ucfirst($s->jenis_seni ?? '')) ?> <?= esc($s->nama_seni ?? '') ?></td>
                    <td class="small"><?= esc($s->nama_kontingen ?? '-') ?></td>
                    <td>
                        <?php
                        $bc = match ($s->status_penampilan ?? 'belum_tampil') {
                            'sudah_tampil'   => 'bg-success',
                            'sedang_tampil'  => 'bg-warning text-dark',
                            'standby', 'berhenti' => 'bg-info text-dark',
                            default          => 'bg-secondary',
                        };
                        ?>
                        <span class="badge <?= $bc ?>"><?= esc(str_replace('_', ' ', $s->status_penampilan ?? 'belum tampil')) ?></span>
                    </td>
                    <td class="text-end">
                        <?php if ((int) ($s->id_penampilan_seni ?? 0) === $idPS) : ?>
                            <span class="badge bg-warning text-dark">Aktif</span>
                        <?php elseif (in_array($s->status_penampilan ?? '', ['belum_tampil', 'standby'], true)) : ?>
                            <a href="<?= base_url('sekretaris-pertandingan/pindah-partai-seni/' . $s->id_penampilan_seni) ?>"
                               class="btn btn-sm btn-danger rounded-pill px-3"><i class="fas fa-play me-1"></i>Pilih</a>
                        <?php else : ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
