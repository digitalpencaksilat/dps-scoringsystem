<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Print Nilai') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/penilaian/print.css') ?>">
</head>
<body class="print-body">
<div class="print-container">

    <div class="print-header text-center">
        <h2 class="print-title"><?= esc(ucfirst($penampilan->jenis_seni ?? '')) ?> <?= esc($penampilan->jenis_kelamin ?? '') ?> <?= esc($penampilan->nama_seni ?? '') ?></h2>
        <p class="print-subtitle"><?= esc($penampilan->nama_kategori_usia ?? '') ?> &mdash; Pool <?= esc($penampilan->nomor_pool ?? '-') ?></p>
    </div>

    <table class="print-table">
        <thead class="print-thead">
            <tr>
                <th rowspan="2">Partai</th>
                <th rowspan="2">Nama Peserta</th>
                <th rowspan="2">Kontingen</th>

                <?php if ($jumlah_juri > 0): ?>
                <th colspan="<?= $jumlah_juri ?>">Kebenaran</th>
                <th colspan="<?= $jumlah_juri ?>">Kemantapan</th>
                <th colspan="<?= $jumlah_juri ?>">Hukuman</th>
                <th colspan="<?= $jumlah_juri ?>">Total</th>
                <?php endif; ?>

                <th rowspan="2">Waktu</th>
                <th rowspan="2">Nilai Akhir</th>
            </tr>
            <tr>
                <?php for ($i = 1; $i <= $jumlah_juri; $i++): ?>
                    <th>Juri <?= $i ?></th>
                <?php endfor; ?>
                <?php for ($i = 1; $i <= $jumlah_juri; $i++): ?>
                    <th>Juri <?= $i ?></th>
                <?php endfor; ?>
                <?php for ($i = 1; $i <= $jumlah_juri; $i++): ?>
                    <th>Juri <?= $i ?></th>
                <?php endfor; ?>
                <?php for ($i = 1; $i <= $jumlah_juri; $i++): ?>
                    <th>Juri <?= $i ?></th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kelompok_list as $kl): ?>
                <?php
                $pid  = (int) $kl->id_penampilan_seni;
                $kpid = (int) $kl->id_kelompok_peserta_seni;
                $anggota = $anggota_map[$kpid] ?? [];

                $nama = '-';
                if (!empty($anggota)) {
                    $namaArr = array_map(fn($a) => $a->nama_pendaftar, $anggota);
                    $nama = implode('<br>', array_map('esc', $namaArr));
                }

                $wtMin = intdiv((int) ($kl->waktu_tampil ?? 0), 60);
                $wtSec = ((int) ($kl->waktu_tampil ?? 0)) % 60;
                $waktuStr = $kl->waktu_tampil ? "{$wtMin}m {$wtSec}d" : 'belum tampil';

                $nilai = esc($kl->nilai_akhir ?? '0');
                if ($kl->diskualifikasi == 1) $nilai .= ' (DQ)';
                ?>
            <tr>
                <td><?= esc($kl->nomor_partai ?? '-') ?></td>
                <td><?= $nama ?></td>
                <td><?= esc($kl->nama_kontingen ?? '-') ?></td>

                <?php foreach ($perangkat_juri as $j): ?>
                    <?php $bd = $breakdown_map[$pid][(int) $j->id_perangkat_pertandingan] ?? ['kebenaran' => 0, 'kemantapan' => 0, 'hukuman' => 0, 'total' => 0]; ?>
                    <td><?= $bd['kebenaran'] ?></td>
                <?php endforeach; ?>

                <?php foreach ($perangkat_juri as $j): ?>
                    <?php $bd = $breakdown_map[$pid][(int) $j->id_perangkat_pertandingan] ?? ['kemantapan' => 0]; ?>
                    <td><?= $bd['kemantapan'] ?></td>
                <?php endforeach; ?>

                <?php foreach ($perangkat_juri as $j): ?>
                    <?php $bd = $breakdown_map[$pid][(int) $j->id_perangkat_pertandingan] ?? ['hukuman' => 0]; ?>
                    <td><?= $bd['hukuman'] ?></td>
                <?php endforeach; ?>

                <?php foreach ($perangkat_juri as $j): ?>
                    <?php $bd = $breakdown_map[$pid][(int) $j->id_perangkat_pertandingan] ?? ['total' => 0]; ?>
                    <td><?= $bd['total'] ?></td>
                <?php endforeach; ?>

                <td><?= $waktuStr ?></td>
                <td class="fw-bold"><?= $nilai ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (empty($kelompok_list)): ?>
        <p class="text-muted text-center">Belum ada data penampilan.</p>
    <?php endif; ?>

    <div class="print-footer">
        <p>Dicetak dari DPS Scoring System</p>
    </div>

</div>

<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>
</body>
</html>
