<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('content') ?>
<?php
    $idP   = (int) $pertandingan->id_pertandingan;
    $ronde = (string) $pertandingan->ronde_pertandingan;
    $namaMerah = $atlet_merah->nama_pendaftar ?? 'Atlet Merah';
    $namaBiru  = $atlet_biru->nama_pendaftar ?? 'Atlet Biru';
    $kontMerah = $atlet_merah->nama_kontingen ?? '-';
    $kontBiru  = $atlet_biru->nama_kontingen ?? '-';
?>
<div class="juri-wrapper" data-id-pertandingan="<?= $idP ?>" data-ronde="<?= esc($ronde, 'attr') ?>">
    <header class="juri-topbar">
        <span class="juri-ronde-badge penilaian-display-font">Ronde <?= esc($ronde) ?></span>
        <span class="juri-format">PERSILAT &middot; Juri</span>
        <a href="<?= base_url('perangkat-pertandingan/logout') ?>" class="juri-logout" title="Keluar">
            <i class="fas fa-right-from-bracket"></i>
        </a>
    </header>

    <div class="juri-corners">
        <!-- SUDUT BIRU -->
        <section class="juri-corner corner-biru">
            <div class="corner-head">
                <div class="corner-skor penilaian-display-font" id="skor-biru">0</div>
                <div class="corner-atlet">
                    <div class="corner-nama"><?= esc($namaBiru) ?></div>
                    <div class="corner-kontingen"><?= esc($kontBiru) ?></div>
                </div>
            </div>
            <div class="corner-buttons">
                <button type="button" class="btn-nilai" data-sudut="biru" data-nilai="1">
                    <span class="nilai-angka penilaian-display-font">1</span>
                    <span class="nilai-label">Pukulan</span>
                </button>
                <button type="button" class="btn-nilai" data-sudut="biru" data-nilai="2">
                    <span class="nilai-angka penilaian-display-font">2</span>
                    <span class="nilai-label">Tendangan</span>
                </button>
                <button type="button" class="btn-hapus" data-sudut="biru">
                    <i class="fas fa-rotate-left"></i>
                    <span class="nilai-label">Hapus</span>
                </button>
            </div>
        </section>

        <!-- SUDUT MERAH -->
        <section class="juri-corner corner-merah">
            <div class="corner-head">
                <div class="corner-skor penilaian-display-font" id="skor-merah">0</div>
                <div class="corner-atlet">
                    <div class="corner-nama"><?= esc($namaMerah) ?></div>
                    <div class="corner-kontingen"><?= esc($kontMerah) ?></div>
                </div>
            </div>
            <div class="corner-buttons">
                <button type="button" class="btn-nilai" data-sudut="merah" data-nilai="1">
                    <span class="nilai-angka penilaian-display-font">1</span>
                    <span class="nilai-label">Pukulan</span>
                </button>
                <button type="button" class="btn-nilai" data-sudut="merah" data-nilai="2">
                    <span class="nilai-angka penilaian-display-font">2</span>
                    <span class="nilai-label">Tendangan</span>
                </button>
                <button type="button" class="btn-hapus" data-sudut="merah">
                    <i class="fas fa-rotate-left"></i>
                    <span class="nilai-label">Hapus</span>
                </button>
            </div>
        </section>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-tanding.css') ?>">
<?php if (($theme ?? 'light') === 'dark') : ?>
<style>body.penilaian-body{background:#0f1115;}.juri-topbar{background:#1a1d24;color:#fff;}</style>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const wrapper = document.querySelector('.juri-wrapper');
    const idPertandingan = wrapper.dataset.idPertandingan;
    const endpoint = '<?= base_url('juri/edit-penilaian-tanding') ?>/' + idPertandingan;
    let csrfName  = '<?= csrf_token() ?>';
    let csrfHash  = '<?= csrf_hash() ?>';
    let locked = false; // anti double-submit

    function kirim(sudut, entryObj, btn) {
        if (locked) return;
        locked = true;
        if (btn) btn.classList.add('is-loading');

        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        body.append('sudut', sudut);
        body.append('entry', JSON.stringify(entryObj));

        fetch(endpoint, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(r => r.json())
        .then(data => {
            // Rotasi CSRF token (regenerate aktif).
            const newHash = data.csrf_hash || null;
            if (newHash) csrfHash = newHash;
            if (data && data.status === true) {
                renderSkor(data.response);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: (data && data.message) || 'Input ditolak.', timer: 1800, showConfirmButton: false });
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'warning', title: 'Koneksi', text: 'Gagal mengirim nilai.', timer: 1500, showConfirmButton: false });
        })
        .finally(() => {
            locked = false;
            if (btn) btn.classList.remove('is-loading');
        });
    }

    function hitungSkorSudut(sudutData) {
        // sudutData = decoded penilaian_(merah|biru)
        if (!sudutData || !sudutData.ringkasan) return 0;
        return sudutData.ringkasan.nilai_akhir || 0;
    }

    function renderSkor(response) {
        if (!response) return;
        document.getElementById('skor-merah').textContent = hitungSkorSudut(response.merah);
        document.getElementById('skor-biru').textContent  = hitungSkorSudut(response.biru);
    }

    document.querySelectorAll('.btn-nilai').forEach(function (btn) {
        btn.addEventListener('click', function () {
            kirim(btn.dataset.sudut, { nilai: parseInt(btn.dataset.nilai, 10) }, btn);
        });
    });
    document.querySelectorAll('.btn-hapus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            kirim(btn.dataset.sudut, { action: 'remove' }, btn);
        });
    });

    // Render skor awal dari data server.
    renderSkor(<?= json_encode($data_nilai) ?>);

    // Polling status partai (placeholder; diganti Socket.IO di Fase 8).
    setInterval(function () {
        const body = new URLSearchParams();
        body.append(csrfName, csrfHash);
        fetch('<?= base_url('juri/refresh-status-pertandingan') ?>/' + idPertandingan, {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: body
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.reload === true) { window.location.reload(); }
            else if (data && data.status === false && data.data_nilai) { renderSkor(data.data_nilai); }
        })
        .catch(() => {});
    }, 4000);
})();
</script>
<?= $this->endSection() ?>
