<?php
/**
 * Shared Competition Title Header — untuk Layar Tanding & Seni
 * Parity: sama persis di kedua halaman, no perbedaan
 *
 * Variables yang dibutuhkan:
 * - $event_name: nama event
 * - $info_left: isi kolom kiri bawah (array atau string)
 * - $info_center: isi kolom tengah bawah (array atau string) - opsional
 * - $info_right: isi kolom kanan bawah (array atau string)
 */
?>
<div class="row bg-white bg-gradient-180-white mb-2 justify-content-around opacity" id="competition-title">
    <div class="col-1 col-xxl-1 px-0 py-2 d-flex justify-content-center align-items-center">
        <img src="<?= base_url('assets/images/brand/dps/logo-international-federation.png') ?>"
             alt="Persilat" class="img-fluid" onerror="this.style.display='none'">
    </div>
    <div class="col-8 col-xxl-9">
        <div class="row mb-1">
            <div class="col-12 bg-gradient-180-gray-dark">
                <p class="h2 text-center m-0 text-white my-2" style="font-family: 'Oswald', sans-serif; font-weight: 700; letter-spacing: 1px;">
                    <?= esc($event_name ?? 'Pencak Silat Championship') ?>
                </p>
            </div>
        </div>
        <div class="row justify-content-around py-1">
            <?php
                $infoCols = array_filter([$info_left, $info_center, $info_right], function($v) { return $v !== null; });
                $colSize = 'col-' . (12 / count($infoCols));
            ?>
            <?php foreach ($infoCols as $info): ?>
                <div class="<?= $colSize ?>">
                    <p class="h3 m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block text-truncate">
                        <?php
                            if (is_array($info)) {
                                echo esc(implode(' - ', array_filter($info)));
                            } else {
                                echo esc($info);
                            }
                        ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="col-1 col-xxl-1 px-0 py-2 d-flex justify-content-center align-items-center">
        <img src="<?= base_url('assets/images/brand/dps/logo-federation.png') ?>"
             alt="Federation" class="img-fluid" onerror="this.style.display='none'">
    </div>
</div>
