<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
.juri-home-wrapper {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    background: var(--bg-color, #f4f6f9);
}

.juri-home-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: var(--brand-dark, #212529);
    color: #fff;
}

.juri-home-title {
    font-size: 1rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.juri-home-body {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

.juri-home-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    max-width: 700px;
    width: 100%;
}

.card-kategori {
    background: #fff;
    border-radius: 16px;
    padding: 32px 24px;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    border-top: 4px solid var(--brand-primary, #c60000);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.card-kategori:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.card-kategori .icon-circle {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(198, 0, 0, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 1.6rem;
    color: var(--brand-primary, #c60000);
}

.card-kategori h5 {
    font-weight: 700;
    font-size: 1.1rem;
    color: var(--brand-dark);
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-kategori .header-line {
    width: 40px;
    height: 3px;
    background: var(--brand-primary, #c60000);
    margin: 0 auto 20px;
    border-radius: 2px;
}

.btn-brand-red {
    display: block;
    width: 100%;
    padding: 12px 20px;
    background: var(--brand-primary, #c60000);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    text-align: center;
    margin-bottom: 10px;
    transition: background 0.15s;
}

.btn-brand-red:hover {
    background: #a50000;
    color: #fff;
}

.btn-brand-red:last-child {
    margin-bottom: 0;
}

.btn-brand-outline {
    display: block;
    width: 100%;
    padding: 12px 20px;
    background: transparent;
    color: var(--brand-primary, #c60000);
    border: 2px solid var(--brand-primary, #c60000);
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    text-align: center;
    margin-bottom: 10px;
    transition: background 0.15s, color 0.15s;
}

.btn-brand-outline:hover {
    background: var(--brand-primary, #c60000);
    color: #fff;
}

.btn-brand-outline:last-child {
    margin-bottom: 0;
}

@media (max-width: 575.98px) {
    .juri-home-grid {
        grid-template-columns: 1fr;
    }
    .card-kategori {
        padding: 24px 18px;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="juri-home-wrapper">
    <header class="juri-home-topbar">
        <span class="juri-home-title"><i class="fas fa-gavel me-2"></i>Panel Juri</span>
        <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="text-white text-decoration-none" title="Keluar">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </header>

    <div class="juri-home-body">
        <div class="juri-home-grid">
            <!-- Tanding -->
            <div class="card-kategori">
                <div class="icon-circle">
                    <i class="fa-solid fa-hand-fist"></i>
                </div>
                <h5>Tanding</h5>
                <div class="header-line"></div>
                <a href="<?= base_url('juri/tanding/light') ?>" class="btn-brand-red">
                    <i class="fas fa-gavel me-1"></i> Tanding (Light)
                </a>
                <a href="<?= base_url('juri/tanding/dark') ?>" class="btn-brand-outline">
                    <i class="fas fa-moon me-1"></i> Tanding (Dark)
                </a>
            </div>

            <!-- Seni -->
            <div class="card-kategori">
                <div class="icon-circle">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <h5>Seni</h5>
                <div class="header-line"></div>
                <a href="<?= base_url('juri/seni/sederhana') ?>" class="btn-brand-red">
                    <i class="fas fa-star me-1"></i> Seni (Sederhana)
                </a>
                <a href="<?= base_url('juri/seni/terperinci') ?>" class="btn-brand-outline">
                    <i class="fas fa-expand me-1"></i> Seni (Terperinci)
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
