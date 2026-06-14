<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/layar.css') ?>">
<style>
.layar-dashboard {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}
.layar-dashboard-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    gap: 2rem;
}
.layar-mode-cards {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
    justify-content: center;
}
.layar-mode-card {
    background: rgba(255,255,255,0.05);
    border: 2px solid rgba(255,255,255,0.15);
    border-radius: 1rem;
    padding: 2.5rem 2rem;
    text-align: center;
    min-width: 220px;
    max-width: 280px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    color: #fff;
}
.layar-mode-card:hover {
    background: rgba(255,255,255,0.1);
    border-color: var(--brand-primary, #c60000);
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(198,0,0,0.25);
    color: #fff;
}
.layar-mode-card .card-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}
.layar-mode-card .card-icon.tanding { color: #ff6b6b; }
.layar-mode-card .card-icon.seni { color: #ffd93d; }
.layar-mode-card .card-icon.auto { color: #6bcf7f; }
.layar-mode-card h3 {
    font-family: 'Oswald', sans-serif;
    font-weight: 600;
    font-size: 1.3rem;
    margin-bottom: 0.5rem;
}
.layar-mode-card p {
    font-size: 0.85rem;
    opacity: 0.7;
    margin: 0;
}
.layar-auto-status {
    text-align: center;
    padding: 1rem;
}
.layar-auto-status .status-dot {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6bcf7f;
    animation: pulse-dot 1.5s ease-in-out infinite;
    margin-right: 0.5rem;
}
@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.3); }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'layar', 'nav_active' => 'home']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="layar-dashboard bg-black text-white" id="layar-dashboard"
     data-endpoint-tanding="<?= base_url('layar/refresh-status-pertandingan') ?>"
     data-endpoint-seni="<?= base_url('layar/refresh-status-seni') ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <div class="layar-dashboard-content">
        <!-- Logo -->
        <div>
            <img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
                 alt="DPS Scoring System" style="max-height: 80px; opacity: 0.9;"
                 onerror="this.src='<?= base_url('assets/images/brand/dps/logo.png') ?>'">
        </div>

        <!-- Gelanggang Info -->
        <div class="text-center">
            <h2 class="penilaian-display-font mb-1"><?= esc($nama_gelanggang ?? 'Gelanggang') ?></h2>
            <p class="text-white-50 small mb-0">Digital Pencak Silat — Scoring System</p>
        </div>

        <!-- Mode Selection Cards -->
        <div class="layar-mode-cards">
            <a href="<?= base_url('layar/tanding') ?>" class="layar-mode-card">
                <div class="card-icon tanding"><i class="fa-solid fa-hand-fist"></i></div>
                <h3>Tanding</h3>
                <p>Papan skor pertandingan tanding</p>
            </a>

            <a href="<?= base_url('layar/seni') ?>" class="layar-mode-card">
                <div class="card-icon seni"><i class="fa-solid fa-star"></i></div>
                <h3>Seni</h3>
                <p>Papan skor penampilan seni</p>
            </a>
        </div>

        <div class="layar-auto-status">
            <span class="text-white-50 small">Pilih mode papan skor untuk melanjutkan</span>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Dashboard layar — pilih mode manual. Tidak ada auto-redirect ke layar
// pertandingan/seni yang sedang berlangsung; user bebas memilih lewat kartu.
</script>
<?= $this->endSection() ?>
