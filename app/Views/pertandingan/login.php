<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-brand">
            <h1>Digital Pencak Silat</h1>
            <p>Perangkat Pertandingan</p>
        </div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger py-2 small" role="alert">
                <i class="fas fa-circle-exclamation me-1"></i>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('perangkat-pertandingan/login') ?>" method="post" autocomplete="off">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Username</label>
                <input type="text" class="form-control" id="username" name="username"
                       placeholder="Contoh: juri1_A" required autofocus>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-penilaian-primary w-100">
                <i class="fas fa-right-to-bracket me-2"></i>Masuk
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
