<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $semua = $ringkasan->semua_ronde ?? null;

    // Tombol KP: label, mode, jumlah (null/'hapus' = hapus). Negatif = hukuman.
    $aksiHukuman = [
        ['label' => 'Teguran 1',    'mode' => 'teguran_1',    'jumlah' => -1,  'cls' => 'warn'],
        ['label' => 'Teguran 2',    'mode' => 'teguran_2',    'jumlah' => -2,  'cls' => 'warn'],
        ['label' => 'Peringatan 1', 'mode' => 'peringatan_1', 'jumlah' => -5,  'cls' => 'danger'],
        ['label' => 'Peringatan 2', 'mode' => 'peringatan_2', 'jumlah' => -10, 'cls' => 'danger'],
        ['label' => 'Jatuhan (+3)', 'mode' => 'jatuhan',      'jumlah' => 3,   'cls' => 'ok'],
        ['label' => 'Binaan',       'mode' => 'binaan',       'jumlah' => 1,   'cls' => 'muted'],
    ];
?>
<div class="kp-wrapper" data-id-pertandingan="<?= $idP ?>" data-ronde="<?= esc($ronde, 'attr') ?>">
    <header class="kp-topbar">
        <span class="kp-ronde penilaian-display-font">Ronde <?= esc($ronde) ?></span>
        <span class="kp-title">PERSILAT &middot; Ketua Pertandingan</span>
        <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="kp-logout" title="Keluar"><i class="fas fa-right-from-bracket"></i></a>
    </header>

    <div class="kp-scoreboard">
        <div class="kp-score corner-biru">
            <div class="kp-score-nama"><?= esc($namaBiru) ?></div>
            <div class="kp-score-angka penilaian-display-font" id="skor-biru"><?= (int) $pertandingan->skor_biru ?></div>
        </div>
        <div class="kp-score-vs penilaian-display-font">VS</div>
        <div class="kp-score corner-merah">
            <div class="kp-score-nama"><?= esc($namaMerah) ?></div>
            <div class="kp-score-angka penilaian-display-font" id="skor-merah"><?= (int) $pertandingan->skor_merah ?></div>
        </div>
    </div>

    <div class="kp-controls">
        <?php foreach (['biru', 'merah'] as $sudut) : ?>
            <section class="kp-panel corner-<?= $sudut ?>">
                <h2 class="kp-panel-title"><?= $sudut === 'biru' ? esc($namaBiru) : esc($namaMerah) ?></h2>
                <div class="kp-buttons">
                    <?php foreach ($aksiHukuman as $aksi) : ?>
                        <button type="button" class="kp-btn kp-<?= $aksi['cls'] ?>"
                                data-sudut="<?= $sudut ?>"
                                data-mode="<?= esc($aksi['mode'], 'attr') ?>"
                                data-jumlah="<?= esc((string) $aksi['jumlah'], 'attr') ?>">
                            <?= esc($aksi['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="kp-rekap" id="rekap-<?= $sudut ?>">
                    <?php $r = $semua->{$sudut} ?? null; ?>
                    <span>Teguran: <b class="rk-teguran"><?= ($r->teguran_1 ?? 0) + ($r->teguran_2 ?? 0) ?></b></span>
                    <span>Peringatan: <b class="rk-peringatan"><?= ($r->peringatan_1 ?? 0) + ($r->peringatan_2 ?? 0) ?></b></span>
                    <span>Jatuhan: <b class="rk-jatuhan"><?= $r->jatuhan ?? 0 ?></b></span>
                    <span>Binaan: <b class="rk-binaan"><?= $r->binaan_1 ?? 0 ?></b></span>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/ketua-tanding.css') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const wrapper = document.querySelector('.kp-wrapper');
    const idP = wrapper.dataset.idPertandingan;
    const endpoint = '<?= base_url('ketua-pertandingan/edit-penilaian-tanding') ?>/' + idP;
    const csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    let locked = false;

    function applyRingkasan(ring) {
        if (!ring || !ring.semua_ronde) return;
        ['merah', 'biru'].forEach(function (s) {
            const r = ring.semua_ronde[s] || {};
            const box = document.getElementById('rekap-' + s);
            if (!box) return;
            box.querySelector('.rk-teguran').textContent    = (r.teguran_1 || 0) + (r.teguran_2 || 0);
            box.querySelector('.rk-peringatan').textContent = (r.peringatan_1 || 0) + (r.peringatan_2 || 0);
            box.querySelector('.rk-jatuhan').textContent    = r.jatuhan || 0;
            box.querySelector('.rk-binaan').textContent     = r.binaan_1 || 0;
        });
    }

    function kirim(sudut, mode, jumlah, btn) {
        if (locked) return;
        locked = true;
        if (btn) btn.classList.add('is-loading');

        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        body.append('sudut', sudut);
        body.append('mode', mode);
        body.append('jumlah', jumlah);

        fetch(endpoint, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body })
            .then(r => r.json())
            .then(data => {
                if (data.csrf_hash) csrfHash = data.csrf_hash;
                if (data && data.status === true) {
                    document.getElementById('skor-merah').textContent = data.skor_merah;
                    document.getElementById('skor-biru').textContent  = data.skor_biru;
                    applyRingkasan(data.ringkasan);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', timer: 1500, showConfirmButton: false });
                }
            })
            .catch(() => Swal.fire({ icon: 'warning', title: 'Koneksi gagal', timer: 1500, showConfirmButton: false }))
            .finally(() => { locked = false; if (btn) btn.classList.remove('is-loading'); });
    }

    document.querySelectorAll('.kp-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            kirim(btn.dataset.sudut, btn.dataset.mode, btn.dataset.jumlah, btn);
        });
    });

    // Polling (placeholder; diganti Socket.IO Fase 8).
    setInterval(function () {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('ketua-pertandingan/refresh-status-pertandingan') ?>/' + idP, {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.reload === true) { window.location.reload(); }
            else if (data && data.status === false) {
                document.getElementById('skor-merah').textContent = data.skor_merah;
                document.getElementById('skor-biru').textContent  = data.skor_biru;
                applyRingkasan(data.ringkasan);
            }
        })
        .catch(() => {});
    }, 4000);
})();
</script>
<?= $this->endSection() ?>
