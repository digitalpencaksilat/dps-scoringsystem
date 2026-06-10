<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
.custom-container { max-width: 1000px; margin: 0 auto; }
.card-custom {
    border: none; border-top: 3px solid var(--brand-primary, #d90429);
    border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease; background: #fff;
}
.card-custom:hover { transform: translateY(-4px); box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
.card-header-custom {
    background: transparent; border-bottom: 1px solid rgba(0,0,0,0.06);
    padding: 20px 24px; display: flex; align-items: center; gap: 14px;
}
.icon-circle {
    width: 60px; height: 60px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: #fff; flex-shrink: 0;
}
.icon-circle-red { background: linear-gradient(135deg, #d90429, #b90422); }
.icon-circle-blue { background: linear-gradient(135deg, #1d2af4, #0118d8); }
.icon-circle-gold { background: linear-gradient(135deg, #c5a017, #9a7d12); }
.icon-circle-dark { background: linear-gradient(135deg, #333, #111); }
.card-title-custom { font-size: 1.1rem; font-weight: 700; color: #212529; margin: 0; }
.card-subtitle-custom { font-size: 0.8rem; color: #6c757d; margin-top: 2px; }
.card-body-custom { padding: 20px 24px; }
.btn-brand-red {
    background: linear-gradient(135deg, #d90429, #b90422); color: #fff;
    border: none; border-radius: 8px; padding: 12px 20px;
    font-weight: 600; font-size: 0.9rem; text-decoration: none;
    display: flex; align-items: center; gap: 8px;
    transition: transform 0.15s, box-shadow 0.15s; width: 100%; justify-content: center;
}
.btn-brand-red:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(217,4,41,0.3); color: #fff; }
.btn-brand-outline {
    background: transparent; color: #d90429; border: 2px solid #d90429;
    border-radius: 8px; padding: 12px 20px; font-weight: 600; font-size: 0.9rem;
    text-decoration: none; display: flex; align-items: center; gap: 8px;
    transition: all 0.15s; width: 100%; justify-content: center;
}
.btn-brand-outline:hover { background: #d90429; color: #fff; }
.btn-brand-dark {
    background: linear-gradient(135deg, #333, #111); color: #fff;
    border: none; border-radius: 8px; padding: 12px 20px;
    font-weight: 600; font-size: 0.9rem; text-decoration: none;
    display: flex; align-items: center; gap: 8px;
    transition: transform 0.15s; width: 100%; justify-content: center;
}
.btn-brand-dark:hover { transform: translateY(-2px); color: #fff; }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'ketua_pertandingan', 'nav_active' => 'home']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid min-vh-100 d-flex align-items-center" style="background: var(--bg-color, #f4f6f9);">
    <div class="custom-container w-100 py-4">
        <!-- Title -->
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark mb-1">Panel Ketua Pertandingan</h4>
            <p class="text-muted mb-0">Kontrol hukuman, verifikasi, dan akses penilaian</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- ═══ Card Tanding ═══ -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-header-custom">
                        <div class="icon-circle icon-circle-red">
                            <i class="fas fa-fist-raised"></i>
                        </div>
                        <div>
                            <h5 class="card-title-custom">Tanding</h5>
                            <p class="card-subtitle-custom">Hukuman & jatuhan pertandingan</p>
                        </div>
                    </div>
                    <div class="card-body-custom">
                        <div class="d-flex flex-column gap-3">
                            <a href="<?= base_url('ketua-pertandingan/tanding/dark') ?>" class="btn-brand-red">
                                <i class="fas fa-circle-half-stroke"></i> Kontrol Tanding (Dark)
                            </a>
                            <a href="<?= base_url('ketua-pertandingan/tanding/light') ?>" class="btn-brand-outline">
                                <i class="fas fa-sun"></i> Kontrol Tanding (Light)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Card Seni ═══ -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-header-custom">
                        <div class="icon-circle icon-circle-gold">
                            <i class="fas fa-hand-sparkles"></i>
                        </div>
                        <div>
                            <h5 class="card-title-custom">Seni</h5>
                            <p class="card-subtitle-custom">Hukuman, akses & diskualifikasi</p>
                        </div>
                    </div>
                    <div class="card-body-custom">
                        <div class="d-flex flex-column gap-3">
                            <a href="<?= base_url('ketua-pertandingan/seni/dark') ?>" class="btn-brand-red">
                                <i class="fas fa-gavel"></i> Kontrol Seni (Dark)
                            </a>
                            <a href="<?= base_url('ketua-pertandingan/seni/light') ?>" class="btn-brand-outline">
                                <i class="fas fa-sun"></i> Kontrol Seni (Light)
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ═══ Card Daftar Nilai ═══ -->
            <div class="col-lg-4 col-md-6">
                <div class="card card-custom h-100">
                    <div class="card-header-custom">
                        <div class="icon-circle icon-circle-dark">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div>
                            <h5 class="card-title-custom">Daftar Nilai</h5>
                            <p class="card-subtitle-custom">Rekap semua partai/penampilan</p>
                        </div>
                    </div>
                    <div class="card-body-custom">
                        <div class="d-flex flex-column gap-3">
                            <a href="<?= base_url('ketua-pertandingan/daftar-nilai-tanding') ?>" class="btn-brand-dark">
                                <i class="fas fa-table"></i> Daftar Nilai Tanding
                            </a>
                            <a href="<?= base_url('ketua-pertandingan/daftar-nilai-seni') ?>" class="btn-brand-dark">
                                <i class="fas fa-table"></i> Daftar Nilai Seni
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
