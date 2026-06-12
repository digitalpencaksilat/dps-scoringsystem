<div class="row opacity" id="header-tanding">
    <div class="col-lg-5">
        <div class="row flex-row-reverse flex-md-row h-100 bg-gradient-180-blue">
            <div class="col-3 d-flex justify-content-center align-items-center">
                <img src="<?= base_url('assets/images/icon/siluette_atlet.png') ?>" class="w-100 img-fluid" alt="">
            </div>
            <div class="col-9 py-3 ps-0 pe-2 d-flex justify-content-center flex-column">
                <h1 class="h3 lh-1 text-white m-0 fw-bolder text-truncate">
                    <?= esc($atlet_biru->nama_pendaftar ?? 'Atlet Biru') ?>
                </h1>
                <h2 class="h4 fw-light text-white m-0 text-truncate">
                    <?= strtoupper(esc($atlet_biru->nama_kontingen ?? '')) ?>
                </h2>
            </div>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="row h-100 px-2">
            <div class="col-12 h-100 bg-gradient-180-gray-dark d-flex flex-row align-items-center justify-content-center">
                <p class="h2 text-wrap text-center text-white m-0"><?= esc($pertandingan->babak ?? '') ?></p>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="row flex-row-reverse flex-md-row h-100 bg-gradient-180-red">
            <div class="col-9 py-3 pe-0 ps-2 d-flex justify-content-center flex-column">
                <h1 class="h3 text-end lh-1 text-white m-0 fw-bolder text-truncate">
                    <?= esc($atlet_merah->nama_pendaftar ?? 'Atlet Merah') ?>
                </h1>
                <h2 class="h4 text-end fw-light text-white m-0 text-truncate">
                    <?= strtoupper(esc($atlet_merah->nama_kontingen ?? '')) ?>
                </h2>
            </div>
            <div class="col-3 d-flex justify-content-center align-items-center">
                <img src="<?= base_url('assets/images/icon/siluette_atlet.png') ?>" class="w-100 img-fluid" style="transform: scaleX(-1);" alt="">
            </div>
        </div>
    </div>
</div>
