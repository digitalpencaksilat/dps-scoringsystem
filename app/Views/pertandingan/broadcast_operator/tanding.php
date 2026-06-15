<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('navbar') ?>
<?= view('pertandingan/components/navbar', ['nav_role' => 'broadcast_operator', 'nav_active' => 'tanding']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="bo-wrapper">
    <header class="bo-topbar d-none">
        <span class="bo-brand penilaian-display-font">Broadcast Operator</span>
        <span class="bo-gelanggang"><?= esc($nama_gelanggang ?? 'Gelanggang') ?></span>
        <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="bo-logout"><i class="fas fa-right-from-bracket"></i></a>
    </header>

    <div class="bo-body">
        <div class="bo-overlay-link">
            <span>URL Overlay (OBS Browser Source):</span>
            <code id="overlay-url"><?= base_url('broadcast-operator/overlay/' . (int) session()->get('id_gelanggang')) ?></code>
            <button class="btn btn-sm btn-outline-light rounded-pill" id="btn-copy"><i class="fas fa-copy me-1"></i>Salin</button>
        </div>

        <h2 class="bo-section-title">Pilih Scene Aktif</h2>
        <div class="bo-scene-grid">
            <?php if (empty($scenes)) : ?>
                <p class="text-muted">Belum ada scene untuk gelanggang ini.</p>
            <?php else : ?>
                <?php foreach ($scenes as $s) : ?>
                    <button type="button"
                            class="bo-scene-btn <?= $s->status === 'active' ? 'active' : '' ?>"
                            data-scene="<?= esc($s->scene, 'attr') ?>">
                        <i class="fas fa-clapperboard"></i>
                        <span><?= esc(str_replace('-', ' ', $s->scene)) ?></span>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/broadcast.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';

    document.querySelectorAll('.bo-scene-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const body = new URLSearchParams();
            body.append(csrfName, csrfHash);
            body.append('scene', btn.dataset.scene);
            fetch('<?= base_url('broadcast-operator/set-scene') ?>', {
                method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
            })
            .then(r => r.json())
            .then(d => {
                if (d.csrf_hash) csrfHash = d.csrf_hash;
                if (d.status) {
                    document.querySelectorAll('.bo-scene-btn').forEach(b => b.classList.toggle('active', b.dataset.scene === d.scene));
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', timer: 1400, showConfirmButton: false });
                }
            })
            .catch(() => Swal.fire({ icon: 'warning', title: 'Koneksi gagal', timer: 1400, showConfirmButton: false }));
        });
    });

    document.getElementById('btn-copy').addEventListener('click', function () {
        const url = document.getElementById('overlay-url').textContent;
        navigator.clipboard.writeText(url).then(() => {
            Swal.fire({ icon: 'success', title: 'URL disalin', timer: 1200, showConfirmButton: false });
        });
    });
})();
</script>
<?= $this->endSection() ?>
