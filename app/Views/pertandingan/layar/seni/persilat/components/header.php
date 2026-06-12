<div class="row bg-gradient-180-white rounded mb-1 py-2 fade-down" id="competition-title">
    <div class="col-1 d-flex justify-content-center align-items-center">
        <img src="<?= base_url('assets/images/brand/dps/logo-international-federation.png') ?>" alt="Persilat" class="img-fluid" style="max-height: 60px;"
            onerror="this.style.display='none'">
    </div>
    <div class="col-10">
        <div class="row mb-1">
            <div class="col-12 bg-gradient-180-gray-dark rounded-top rounded-3">
                <p class="h3 text-center m-0 text-white py-1">
                    <?= esc($event_name ?? 'Pencak Silat Championship') ?>
                </p>
            </div>
        </div>
        <div class="row justify-content-around">
            <div class="col-3">
                <p class="h5 m-0 py-1 text-center bg-gradient-180-gray-dark text-white rounded text-truncate">
                    <?= esc($partai_seni_berlangsung->nama_gelanggang ?? '') ?> — Partai <?= esc($partai_seni_berlangsung->nomor_partai ?? '') ?>
                </p>
            </div>
            <?php if (($kompetisi_seni->sistem_penampilan ?? 'pool') === 'battle' && !empty($partai_seni_berlangsung->babak_battle)): ?>
            <div class="col-3">
                <p class="h5 m-0 py-1 text-center bg-gradient-180-gray-dark text-white rounded text-truncate">
                    <?= strtoupper(esc($partai_seni_berlangsung->babak_battle ?? '')) ?>
                </p>
            </div>
            <?php endif; ?>
            <div class="col-3">
                <p class="h5 m-0 py-1 text-center bg-gradient-180-gray-dark text-white rounded text-truncate">
                    <?= strtoupper(esc($kompetisi_seni->nama_kategori_usia ?? '')) ?>
                </p>
            </div>
            <div class="col-3">
                <p class="h5 m-0 py-1 text-center bg-gradient-180-gray-dark text-white rounded text-truncate">
                    <?= strtoupper(esc($kompetisi_seni->jenis_seni ?? '')) ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-1 d-flex justify-content-center align-items-center">
        <img src="<?= base_url('assets/images/brand/dps/logo-federation.png') ?>" alt="Federation" class="img-fluid" style="max-height: 60px;"
            onerror="this.style.display='none'">
    </div>
</div>
