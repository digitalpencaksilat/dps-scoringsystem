<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/penilaian/juri-seni.css') ?>">
<style>
/* ─── Full-viewport layout ─────────────────────────────────────────────── */
html, body { height: 100%; overflow: hidden; margin: 0; }

#juri-seni-app {
    display: flex;
    flex-direction: column;
    height: 100dvh;
    background: #0d0f11;
    overflow: hidden;
}

/* ─── Header strip ─────────────────────────────────────────────────────── */
#seni-header {
    flex-shrink: 0;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 0;
    background: #111;
    border-bottom: 2px solid transparent;
}
#seni-header.accent-warning  { border-bottom-color: #ffc107; }
#seni-header.accent-blue     { border-bottom-color: #1d2af4; }
#seni-header.accent-red      { border-bottom-color: #dd0a35; }
#seni-header.accent-gold     { border-bottom-color: #c5a017; }

.hdr-peserta {
    padding: 8px 12px;
    overflow: hidden;
}
.hdr-peserta-nama {
    font-size: clamp(0.72rem, 2.5vw, 0.95rem);
    font-weight: 700;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.2;
}
.hdr-peserta-sub {
    font-size: clamp(0.58rem, 1.8vw, 0.72rem);
    color: rgba(255,255,255,0.5);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.hdr-right { text-align: right; }

.hdr-score-box {
    background: #1a1a1a;
    padding: 6px 16px;
    text-align: center;
    min-width: 80px;
}
.hdr-score-label {
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: rgba(255,255,255,0.4);
}
.hdr-score-val {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(1.6rem, 5vw, 2.4rem);
    font-weight: 700;
    color: #fff;
    line-height: 1;
}

/* ─── Status bar ───────────────────────────────────────────────────────── */
#seni-statusbar {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #161a1d;
    padding: 3px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.07);
}
.statusbar-left {
    font-size: clamp(0.6rem, 1.8vw, 0.75rem);
    font-weight: 600;
    color: rgba(255,255,255,0.6);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
#offline-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    background: #16a34a;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-radius: 4px;
    flex-shrink: 0;
}
#offline-indicator .dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #fff;
    animation: blink 1.2s ease-in-out infinite;
}
#offline-indicator.bg-danger {
    background: #dc2626;
}
#offline-indicator.bg-danger .dot {
    animation: none;
}
@keyframes blink { 0%,100%{opacity:1;} 50%{opacity:0.3;} }

/* ─── Scoring body ─────────────────────────────────────────────────────── */
#seni-body {
    flex: 1 1 0;
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    padding: 8px 10px;
    gap: 8px;
}
#seni-body::-webkit-scrollbar { width: 3px; }
#seni-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 3px; }

/* ─── Kebenaran card ───────────────────────────────────────────────────── */
.kebenaran-card {
    background: #1c1f23;
    border-radius: 10px;
    padding: 10px 12px;
    flex-shrink: 0;
}
.kebenaran-formula {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.keb-box { text-align: center; min-width: 50px; }
.keb-label {
    font-size: 0.55rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(255,255,255,0.4);
    display: block;
    margin-bottom: 2px;
}
.keb-val {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(1.2rem, 3.5vw, 1.7rem);
    font-weight: 700;
    color: #fff;
    line-height: 1;
}
.keb-val.is-deduct { color: #f87171; }
.keb-val.is-result { color: #34d399; }
.keb-op {
    font-size: 1.3rem;
    color: rgba(255,255,255,0.25);
    font-weight: 300;
}

/* ─── Movement Detail Button (replaces pointer display) ─────────────────── */
.btn-movement-detail {
    background: #1c1f23;
    border: 2px dashed rgba(124, 58, 237, 0.5);
    color: #c4b5fd;
    border-radius: 10px;
    padding: 14px 16px;
    font-size: clamp(0.8rem, 2.5vw, 1rem);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    cursor: pointer;
    transition: all 0.15s;
    -webkit-tap-highlight-color: transparent;
    flex-shrink: 0;
}
.btn-movement-detail:hover {
    background: #2a1f3d;
    border-color: #7c3aed;
    color: #fff;
}
.btn-movement-detail:active { transform: scale(0.97); }
.btn-movement-detail i { font-size: clamp(1rem, 3vw, 1.4rem); }

/* ─── Unsur nilai (stamina → selector style) ───────────────────────────── */
.unsur-card {
    background: #1c1f23;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding: 12px;
    flex-shrink: 0;
}
.unsur-card-label {
    color: rgba(255,255,255,0.85);
    font-size: clamp(0.75rem, 2.5vw, 0.95rem);
    font-weight: 600;
    line-height: 1.2;
}
.unsur-selector {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    justify-content: center;
}
.unsur-sel-btn {
    width: clamp(50px, 12vw, 64px);
    height: clamp(48px, 12vw, 60px);
    border: 2px solid rgba(255,255,255,0.15);
    border-radius: 8px;
    background: transparent;
    color: rgba(255,255,255,0.7);
    font-size: clamp(0.7rem, 2vw, 0.85rem);
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.12s;
    -webkit-tap-highlight-color: transparent;
}
.unsur-sel-btn:active { transform: scale(0.93); }
.unsur-sel-btn.active {
    background: #fbbf24;
    color: #000;
    border-color: #fbbf24;
}

/* ─── Action bar ───────────────────────────────────────────────────────── */
#seni-action-bar {
    flex-shrink: 0;
    display: flex;
    gap: 2px;
    background: #000;
}
#seni-action-bar.is-locked { pointer-events: none; opacity: 0.3; }

.btn-seni-act {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    height: clamp(90px, 20vh, 160px);
    border: none;
    font-size: clamp(0.7rem, 2.5vw, 1rem);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: transform 0.06s, filter 0.1s;
    -webkit-tap-highlight-color: transparent;
    color: #fff;
    line-height: 1.2;
}
.btn-seni-act:active { transform: scale(0.97); filter: brightness(0.85); }
.btn-seni-act i { font-size: clamp(1.3rem, 4vw, 1.8rem); }

.btn-act-ready          { background: #2563eb; }
.btn-act-ready.is-ready { background: #16a34a; }
.btn-act-wrong          { background: #dc2626; }

/* ─── Locked overlay ───────────────────────────────────────────────────── */
#overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.88);
    display: flex; align-items: center; justify-content: center;
    z-index: 9999;
    flex-direction: column;
    gap: 12px;
}

/* ─── Movement Detail Modal ─────────────────────────────────────────────── */
#modalDetailGerak {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 9998;
    align-items: center;
    justify-content: center;
    padding: 10px;
}
#modalDetailGerak.show {
    display: flex;
}
.modal-detail-content {
    background: #1c1f23;
    border-radius: 12px;
    max-width: 95%;
    max-height: 90vh;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.modal-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 8px;
}
.modal-detail-title {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
}
.modal-detail-close {
    background: none;
    border: none;
    color: rgba(255,255,255,0.6);
    font-size: 1.5rem;
    cursor: pointer;
}
.modal-detail-close:hover { color: #fff; }

/* Tabel movement detail */
.rincian-table {
    width: 100%;
    font-size: clamp(0.6rem, 1.6vw, 0.75rem);
    background: transparent;
    color: rgba(255,255,255,0.8);
}
.rincian-table thead {
    background: #0a0d11;
}
.rincian-table th {
    color: rgba(255,255,255,0.7);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 6px 8px;
    font-weight: 600;
    text-align: center;
}
.rincian-table td {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.1);
    padding: 6px 8px;
    text-align: center;
}
.rincian-aksi {
    display: flex;
    gap: 4px;
    justify-content: center;
}
.btn-rincian {
    width: 32px;
    height: 30px;
    border: none;
    border-radius: 5px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.06s;
    -webkit-tap-highlight-color: transparent;
    color: #fff;
}
.btn-rincian:active { transform: scale(0.88); }
.btn-rincian-minus { background: #dc2626; }
.btn-rincian-plus  { background: #4b5563; }
</style>
<?= $this->endSection() ?>

<?= $this->section('navbar') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $idPenampilan = (int) $penampilan_seni->id_penampilan_seni;
    $aksesLocked  = ($akses_penilaian === 'ditutup');

    $step = 0.01;
    if (isset($data_nilai->penilaian->unsur_nilai->kebenaran->metadata->step)) {
        $step = (float) $data_nilai->penilaian->unsur_nilai->kebenaran->metadata->step;
    }

    // Accent class
    $accentClass = 'accent-gold';
    if ($color_accent === 'bg-gradient-180-blue')       $accentClass = 'accent-blue';
    elseif ($color_accent === 'bg-gradient-180-red')    $accentClass = 'accent-red';
    elseif ($color_accent === 'bg-gradient-180-warning') $accentClass = 'accent-warning';

    $namaPeserta = '';
    foreach ($peserta_seni as $ps) {
        $namaPeserta .= ($namaPeserta ? ' · ' : '') . esc($ps->nama_pendaftar);
    }
    $namaKontingen = esc($peserta_seni[0]->nama_kontingen ?? '-');
    // Session key dari PerangkatPertandingan login adalah `nama` (bukan `nama_perangkat`)
    $namaJuri = strtoupper(session()->get('nama') ?? session()->get('nama_perangkat') ?? 'JURI');
    $kategori = esc($penampilan_seni->nama_kategori_usia ?? '') . ' - ' . ucwords($penampilan_seni->jenis_kelamin ?? '') . ' ' . strtoupper($penampilan_seni->jenis_seni ?? '');
    $nomorPartai = $partai_seni ? ' · #' . esc($partai_seni->nomor_partai ?? '') : '';
?>

<div class="min-vh-100 bg-black p-0" id="juri-seni-app"
     data-id-penampilan="<?= $idPenampilan ?>"
     data-csrf-name="<?= csrf_token() ?>"
     data-csrf-hash="<?= csrf_hash() ?>">

    <!-- ═══ HEADER ═══ -->
    <div id="seni-header" class="<?= $accentClass ?>">
        <div class="hdr-peserta">
            <div class="hdr-peserta-nama"><?= $namaPeserta ?></div>
            <div class="hdr-peserta-sub"><?= $namaKontingen ?></div>
        </div>
        <div class="hdr-score-box">
            <div class="hdr-score-label">Skor Akhir</div>
            <div class="hdr-score-val">
                <input class="nilai_akhir" type="text" value="0" disabled
                    style="background:transparent;border:none;color:inherit;font:inherit;text-align:center;width:100%;padding:0;">
            </div>
        </div>
        <div class="hdr-peserta hdr-right">
            <div class="hdr-peserta-nama"><?= $kategori ?></div>
            <div class="hdr-peserta-sub"><?= $namaJuri . $nomorPartai ?></div>
        </div>
    </div>

    <!-- ═══ STATUS BAR ═══ -->
    <div id="seni-statusbar">
        <div class="statusbar-left">
            <i class="fas fa-user-tie me-1"></i><?= $namaJuri ?>
        </div>
        <!-- Legacy JS hook + visible online indicator with heartbeat -->
        <div id="offline-indicator" class="bg-success">
            <span class="dot"></span>
            <span>Online</span>
        </div>
    </div>

    <!-- ═══ SCORING BODY ═══ -->
    <div id="seni-body">

        <?php if (isset($data_nilai->penilaian->unsur_nilai->kebenaran)): ?>
        <!-- Kebenaran -->
        <div class="kebenaran-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                <span style="font-size:0.65rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.45);">
                    <i class="fas fa-check-circle me-1"></i>Correctness
                </span>
            </div>
            <div class="kebenaran-formula">
                <div class="keb-box">
                    <span class="keb-label">Max</span>
                    <div class="keb-val"><?= $data_nilai->penilaian->unsur_nilai->kebenaran->nilai_maksimal ?></div>
                </div>
                <span class="keb-op">−</span>
                <div class="keb-box">
                    <span class="keb-label">Deduct</span>
                    <div class="keb-val is-deduct total_pengurangan_kebenaran_gerak">0</div>
                </div>
                <span class="keb-op">=</span>
                <div class="keb-box">
                    <span class="keb-label">Score</span>
                    <!-- JS uses .val() on this -->
                    <input class="total_nilai_kebenaran" type="text" value="0" disabled
                        style="background:transparent;border:none;color:#34d399;font-family:'Oswald',sans-serif;font-size:clamp(1.2rem,3.5vw,1.7rem);font-weight:700;text-align:center;width:70px;padding:0;">
                </div>
            </div>
        </div>

        <!-- Movement Detail Button (replaces pointer display) -->
        <button type="button" class="btn-movement-detail" onclick="juriSeniShowMovementDetail()">
            <i class="fas fa-list-ol"></i>
            <span>Movement Detail</span>
        </button>
        <?php endif; ?>

        <!-- Unsur Nilai selain kebenaran (stamina etc) — selector style BESAR -->
        <?php if (isset($data_nilai->penilaian->unsur_nilai)): ?>
            <?php foreach ($data_nilai->penilaian->unsur_nilai as $jenis_unsur_nilai => $unsur_nilai): ?>
                <?php if ($jenis_unsur_nilai !== 'kebenaran'): ?>
                <?php
                    $nilaiMin = (float) ($unsur_nilai->nilai_minimal ?? 0);
                    $nilaiMax = (float) ($unsur_nilai->nilai_maksimal ?? 0.10);
                    $stepUnsur = (float) ($unsur_nilai->metadata->step ?? 0.01);
                ?>
                <div class="unsur-card container_<?= $jenis_unsur_nilai ?>">
                    <!-- Hidden input for JS .val() binding -->
                    <input class="nilai_<?= $jenis_unsur_nilai ?>" type="hidden" value="0">
                    <div class="unsur-card-label">
                        <?= esc($unsur_nilai->metadata->label ?? ucfirst($jenis_unsur_nilai)) ?>
                    </div>
                    <div class="unsur-selector" data-unsur="<?= $jenis_unsur_nilai ?>" data-step="<?= $stepUnsur ?>">
                        <?php
                        $options = [];
                        for ($v = $nilaiMin; $v <= $nilaiMax + 0.001; $v += $stepUnsur) {
                            $options[] = round($v, 2);
                        }
                        foreach ($options as $opt):
                        ?>
                        <button type="button" class="unsur-sel-btn <?= ($opt == 0) ? 'active' : '' ?>"
                            data-value="<?= number_format($opt, 2) ?>"
                            onclick="juriSeniSelectUnsur('<?= $jenis_unsur_nilai ?>', <?= $opt ?>, this)">
                            <?= number_format($opt, 2) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

    </div><!-- /#seni-body -->

    <!-- ═══ ACTION BAR ═══ -->
    <div id="seni-action-bar" class="<?= $aksesLocked ? 'is-locked' : '' ?>">
        <!-- READY -->
        <button class="btn-seni-act btn-act-ready <?= $status_ready ? 'is-ready' : '' ?>"
            data-status="<?= $status_ready ?>"
            onclick="juri.toggle_ready(this)">
            <i class="fas fa-circle ready-icon"></i>
            <span class="ready-text">Ready</span>
        </button>

        <?php if (isset($data_nilai->penilaian->unsur_nilai->kebenaran)): ?>
        <!-- WRONG MOVE -->
        <button class="btn-seni-act btn-act-wrong button_gerakan_salah"
            onclick="juri.pointer.pindah_gerakan(1, -<?= $step ?>, this)">
            <i class="fas fa-xmark"></i>
            <span>Wrong</span>
        </button>
        <?php endif; ?>
    </div>

</div><!-- /#juri-seni-app -->

<!-- ═══ Movement Detail Modal ═══ -->
<div id="modalDetailGerak">
    <div class="modal-detail-content">
        <div class="modal-detail-header">
            <div class="modal-detail-title">Movement Detail</div>
            <button class="modal-detail-close" onclick="juriSeniHideMovementDetail()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modalDetailBody">
            <!-- Rincian tabel diisi via JS -->
        </div>
    </div>
</div>

<!-- ═══ Locked Overlay (ID must be "overlay" for JS update_akses_penilaian) ═══ -->
<?php if ($aksesLocked): ?>
<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark d-flex justify-content-center align-items-center animated slideInDown" style="z-index:9999;opacity:0.95;">
    <div style="text-align:center;">
        <i class="fas fa-lock" style="font-size:2.5rem;color:#f87171;margin-bottom:12px;display:block;"></i>
        <div class="text-white h3">Scoring Access Locked</div>
        <div style="color:rgba(255,255,255,0.4);font-size:0.85rem;">Menunggu instruksi Ketua Pertandingan</div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/juri_seni_persilat.js') ?>"></script>
<script>
// Fix audio NotSupportedError: provide silent fallback if button.mp3 doesn't exist
(function() {
    if (typeof juri !== 'undefined' && juri.button_audio) {
        juri.button_audio = { play: function() { return Promise.resolve(); } };
    }
})();

/**
 * Selector-style unsur nilai (stamina):
 * Directly sets the value instead of +/- incremental
 */
function juriSeniSelectUnsur(jenisUnsur, value, btn) {
    // Update visual
    $(btn).closest('.unsur-selector').find('.unsur-sel-btn').removeClass('active');
    $(btn).addClass('active');

    // Update hidden input (for JS internal read)
    $(btn).closest('.container_' + jenisUnsur).find('input.nilai_' + jenisUnsur).val(value.toFixed(2));

    // Update data model
    juri.data_nilai.penilaian.unsur_nilai[jenisUnsur].nilai_diperoleh = value;

    // Trigger recalc + server sync
    juri.update_data_nilai(btn);
}

/**
 * Show movement detail modal dengan tabel rincian
 */
function juriSeniShowMovementDetail() {
    var html = '<table class="rincian-table">';
    html += '<thead><tr>';
    html += '<th style="width:42px;">Set</th>';
    html += '<th>Move</th>';
    html += '<th style="width:60px;">Error</th>';
    html += '<th style="width:50px;">Max</th>';
    html += '<th style="width:64px;">Score</th>';
    html += '<th style="width:88px;">Aksi</th>';
    html += '</tr></thead><tbody>';

    var step = 0.01;
    if (juri.data_nilai.penilaian.unsur_nilai.kebenaran && juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata) {
        step = juri.data_nilai.penilaian.unsur_nilai.kebenaran.metadata.step || 0.01;
    }

    $.each(juri.data_nilai.penilaian.unsur_nilai.kebenaran.jurus, function(namaJurus, valJurus) {
        $.each(valJurus.rangkaian_gerak, function(nomorRg, rg) {
            html += '<tr class="container_rangkaian_gerak container_' + namaJurus + '_' + nomorRg + '">';
            html += '<td>' + nomorRg + '</td>';
            html += '<td>' + rg.jumlah_gerakan + '</td>';
            html += '<td><input type="number" class="form-control-sm kebenaran_' + namaJurus + '_' + nomorRg + '" value="' + rg.jumlah_kesalahan + '" disabled style="width:100%;"></td>';
            html += '<td>' + rg.nilai_maksimal + '</td>';
            html += '<td>' + rg.nilai_diperoleh.toFixed(2) + '</td>';
            html += '<td>';
            html += '<div class="rincian-aksi">';
            html += '<button type="button" class="btn-rincian btn-rincian-minus" onclick="juri.edit_nilai_kebenaran_jurus(\'' + namaJurus + '\', ' + nomorRg + ', -' + step + ', this)">';
            html += '<i class="fas fa-minus"></i></button>';
            html += '<button type="button" class="btn-rincian btn-rincian-plus" onclick="juri.edit_nilai_kebenaran_jurus(\'' + namaJurus + '\', ' + nomorRg + ', ' + step + ', this)">';
            html += '<i class="fas fa-plus"></i></button>';
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        });
    });

    html += '</tbody></table>';
    $('#modalDetailBody').html(html);
    $('#modalDetailGerak').addClass('show');
}

function juriSeniHideMovementDetail() {
    $('#modalDetailGerak').removeClass('show');
}

$(function() {
    var $data_nilai      = <?= json_encode($data_nilai, JSON_NUMERIC_CHECK) ?>;
    var $penampilan_seni = <?= json_encode($penampilan_seni) ?>;
    juri.init_penilaian_seni($penampilan_seni, $data_nilai);

    // Override button_audio to prevent NotSupportedError
    juri.button_audio = { play: function() { return Promise.resolve(); } };

    // Sync selector display from initial data
    $.each(juri.data_nilai.penilaian.unsur_nilai, function(key, unsur) {
        if (key === 'kebenaran') return;
        var currentVal = parseFloat(unsur.nilai_diperoleh || 0).toFixed(2);
        var $container = $('.container_' + key);
        $container.find('.unsur-sel-btn').removeClass('active');
        $container.find('.unsur-sel-btn[data-value="' + currentVal + '"]').addClass('active');
        $container.find('input.nilai_' + key).val(currentVal);
    });
});
</script>
<?= $this->endSection() ?>
