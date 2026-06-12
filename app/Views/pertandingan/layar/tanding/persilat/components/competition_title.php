<div class="row bg-white bg-gradient-180-white mb-2 justify-content-around opacity" id="competition-title">
    <div class="col-1 col-xxl-1 px-0 py-2 d-flex justify-content-center align-items-center">
        <img src="<?= base_url('assets/images/brand/dps/logo-international-federation.png') ?>" alt="Persilat" class="img-fluid"
            onerror="this.style.display='none'">
    </div>
    <div class="col-8 col-xxl-9">
        <div class="row mb-1">
            <div class="col-12 bg-gradient-180-gray-dark rounded-top rounded-3">
                <p class="h2 text-center m-0 text-white my-2">
                    <?= esc($event_name ?? 'Pencak Silat Championship') ?>
                </p>
            </div>
        </div>
        <div class="row justify-content-around py-1">
            <div class="col-4">
                <p class="h3 m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block rounded text-truncate">
                    <?= strtoupper(esc($pertandingan->nama_kategori_usia ?? '')) ?>
                </p>
            </div>
            <div class="col-4">
                <p class="h3 m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block rounded text-truncate">
                    <?= strtoupper(esc($pertandingan->jenis_kelamin ?? '')) ?>
                </p>
            </div>
            <div class="col-4">
                <p class="h3 m-0 py-1 text-center bg-gradient-180-gray-dark text-white d-block rounded text-truncate">
                    <?= strtoupper(esc($pertandingan->label ?? '')) ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-1 col-xxl-1 px-0 py-2 d-flex justify-content-center align-items-center">
        <img src="<?= base_url('assets/images/brand/dps/logo-federation.png') ?>" alt="Federation" class="img-fluid"
            onerror="this.style.display='none'">
    </div>
</div>
