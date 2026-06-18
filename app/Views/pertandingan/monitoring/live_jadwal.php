<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
/* ── Dark Modern Theme (matching standby design) ── */
:root {
    --bg-gradient: linear-gradient(160deg, #0f0c29 0%, #1a1a2e 40%, #16213e 100%);
    --card-bg: rgba(255, 255, 255, 0.04);
    --card-border: 1px solid rgba(255, 255, 255, 0.08);
    --card-idle-bg: rgba(255, 255, 255, 0.025);
    --card-text: #fff;
    --card-glow: 0 0 30px rgba(198, 0, 0, 0.08);
    --corner-blue-bg: linear-gradient(160deg, #1565c0, #0d47a1);
    --corner-red-bg: linear-gradient(160deg, #c62828, #8e0000);
    --settings-btn-bg: var(--brand-primary, #c60000);
    --settings-btn-color: #fff;
}

[data-theme="light"] {
    --bg-gradient: linear-gradient(160deg, #e8ecf1 0%, #dce2e8 40%, #c8d4e0 100%);
    --card-bg: rgba(255, 255, 255, 0.7);
    --card-border: 1px solid rgba(0, 0, 0, 0.08);
    --card-idle-bg: rgba(0, 0, 0, 0.02);
    --card-text: #1a1a2e;
    --card-glow: 0 0 20px rgba(198, 0, 0, 0.05);
    --settings-btn-bg: var(--brand-primary, #c60000);
    --settings-btn-color: #fff;
}

body {
    margin: 0; padding: 0; overflow: hidden;
    background: var(--bg-gradient) !important;
    color: var(--card-text);
    font-family: 'Poppins', sans-serif;
}

/* ── Dot grid background ── */
body::before {
    content: '';
    position: fixed; inset: 0;
    opacity: 0.03;
    background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none; z-index: 0;
}

/* ── Glow pulse ── */
body::after {
    content: '';
    position: fixed;
    top: -30%; left: -30%;
    width: 160%; height: 160%;
    background: radial-gradient(ellipse at center, rgba(198, 0, 0, 0.04) 0%, transparent 60%);
    animation: mntr-glow 8s ease-in-out infinite alternate;
    pointer-events: none; z-index: 0;
}

@keyframes mntr-glow {
    0%   { transform: translate(0, 0) scale(1); }
    100% { transform: translate(3%, 2%) scale(1.08); }
}

#displayWrapper {
    width: 100vw; height: 100vh;
    position: relative; overflow: hidden;
    z-index: 1;
}

#displayContainer {
    transition: transform 0.3s ease;
    width: 100%; height: 100%;
    position: absolute; top: 0;
    padding: 2vh 2vw;
    display: flex; flex-wrap: wrap;
    align-content: center; justify-content: center;
}

/* ── Match Column ── */
.match-column {
    transition: width 0.3s ease;
    padding: 1vh 0.8vw;
}

/* ── Card ── */
.mntr-card {
    background: var(--card-bg);
    border: var(--card-border);
    border-radius: 1rem;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    box-shadow: var(--card-glow);
    overflow: hidden;
    height: 100%;
    transition: all 0.3s ease;
}
.mntr-card.active-card {
    border-color: rgba(198, 0, 0, 0.25);
    box-shadow: 0 0 40px rgba(198, 0, 0, 0.12);
}

/* ── Header: Gelanggang + Nomor Partai ── */
.mntr-header {
    padding: 2vh 1.5vw;
    text-align: center;
}
.mntr-gelanggang-name {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(1.2rem, 2.2vw, 2rem);
    font-weight: 700; letter-spacing: 2px;
    color: #fff; margin: 0;
}
.mntr-partai-num {
    font-family: 'Oswald', sans-serif;
    font-size: clamp(3rem, 6vw, 5.5rem);
    font-weight: 700; line-height: 1;
    color: var(--brand-primary, #c60000);
    text-shadow: 0 0 20px rgba(198, 0, 0, 0.3);
    margin: 1vh 0 0;
    letter-spacing: 2px;
}
.mntr-partai-num.idle-num {
    color: rgba(255, 255, 255, 0.2);
    text-shadow: none;
    font-size: clamp(2rem, 4vw, 3.5rem);
}

/* ── Type badge ── */
.mntr-type-badge {
    display: inline-block;
    font-size: clamp(0.65rem, 0.9vw, 0.75rem);
    font-weight: 600; letter-spacing: 2px;
    text-transform: uppercase;
    padding: 0.3rem 1rem; border-radius: 1rem;
    margin-bottom: 1vh;
}
.mntr-type-badge.tanding {
    background: rgba(198, 0, 0, 0.15); color: #ff6b6b;
    border: 1px solid rgba(198, 0, 0, 0.2);
}
.mntr-type-badge.seni {
    background: rgba(197, 160, 23, 0.12); color: #ffd700;
    border: 1px solid rgba(197, 160, 23, 0.2);
}
.mntr-type-badge.idle {
    background: rgba(255, 255, 255, 0.04); color: rgba(255,255,255,0.3);
    border: 1px solid rgba(255, 255, 255, 0.06);
}

/* ── Footer: Athletes ── */
.mntr-footer {
    display: flex;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.mntr-athlete {
    flex: 1;
    text-align: center;
    padding: 1.5vh 0.5vw;
    display: flex; flex-direction: column;
    justify-content: center; gap: 0.3vh;
}
.mntr-athlete.biru { background: var(--corner-blue-bg); }
.mntr-athlete.merah { background: var(--corner-red-bg); }
.mntr-athlete.vs {
    flex: 0 0 auto; min-width: 3rem;
    background: rgba(255,255,255,0.05);
    font-family: 'Oswald', sans-serif;
    font-weight: 700; color: var(--brand-secondary, #c5a017);
}
.mntr-athlete.seni-single {
    background: linear-gradient(160deg, rgba(197,160,23,0.15), rgba(197,160,23,0.05));
}

.mntr-athlete-name {
    font-family: 'Poppins', sans-serif;
    font-size: clamp(0.75rem, 1.1vw, 1rem);
    font-weight: 500; color: #fff;
    line-height: 1.3;
}
.mntr-athlete-kontingen {
    font-size: clamp(0.6rem, 0.8vw, 0.75rem);
    opacity: 0.6; color: #fff;
}

/* ── Skor display ── */
.mntr-skor-row {
    display: flex; gap: 1px;
    margin-top: 1px;
}
.mntr-skor {
    flex: 1;
    text-align: center; padding: 0.8vh 0;
    font-family: 'Oswald', sans-serif;
    font-size: clamp(1.2rem, 2vw, 1.8rem);
    font-weight: 700; color: #fff;
}
.mntr-skor.biru { background: var(--corner-blue-bg); opacity: 0.9; }
.mntr-skor.merah { background: var(--corner-red-bg); opacity: 0.9; }

/* ── Idle state ── */
.mntr-idle {
    text-align: center; padding: 4vh 1vw;
    color: rgba(255,255,255,0.25);
}
.mntr-idle i { font-size: clamp(1.5rem, 3vw, 2.5rem); margin-bottom: 1vh; display: block; }
.mntr-idle span { font-size: clamp(0.7rem, 0.9vw, 0.8rem); letter-spacing: 1px; }

/* ── Buttons ── */
.mntr-btn {
    position: fixed; z-index: 1000;
    opacity: 0.5; transition: all 0.3s;
    border-radius: 50%; width: 46px; height: 46px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    border: none; text-decoration: none;
}
.mntr-btn:hover { opacity: 1; }
.mntr-btn.settings {
    bottom: 20px; right: 20px;
    background: var(--settings-btn-bg); color: var(--settings-btn-color);
}
.mntr-btn.settings:hover {
    transform: rotate(90deg);
    background: var(--settings-btn-color); color: var(--settings-btn-bg);
}
.mntr-btn.back {
    bottom: 20px; left: 20px;
    background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6);
}
.mntr-btn.back:hover { background: rgba(255,255,255,0.15); color: #fff; }

/* ── Animations ── */
.update-transition {
    transition: opacity 0.5s ease-in-out, transform 0.5s ease;
    display: inline-block;
}
.fade-out-up { opacity: 0; transform: translateY(-20px); }
.fade-in-down { opacity: 0; transform: translateY(20px); }

/* ── Rotation ── */
.rotate-0 { transform: none; width: 100vw !important; height: auto !important; }
.rotate-90 { transform: rotate(90deg); transform-origin: bottom left; width: 100vh !important; height: 100vw !important; position: absolute; top: -100vh; left: 0; }
.rotate-270 { transform: rotate(270deg); transform-origin: top right; width: 100vh !important; height: 100vw !important; position: absolute; top: 0; left: -100vh; }

/* ── Settings Modal Dark Mode ── */
.mntr-modal .modal-content {
    background: linear-gradient(160deg, #1a1a2e, #16213e);
    border: 1px solid rgba(255,255,255,0.08);
    color: #fff;
}
.mntr-modal .modal-header {
    background: rgba(255,255,255,0.03);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.mntr-modal .modal-footer {
    background: rgba(255,255,255,0.03);
    border-top: 1px solid rgba(255,255,255,0.06);
}
.mntr-modal .form-label { color: rgba(255,255,255,0.7); }
.mntr-modal .form-select, .mntr-modal .form-range {
    background: rgba(255,255,255,0.06); color: #fff;
    border: 1px solid rgba(255,255,255,0.1);
}
.mntr-modal .form-select option { background: #1a1a2e; color: #fff; }
.mntr-modal .text-muted { color: rgba(255,255,255,0.4) !important; }
.mntr-modal .alert-warning {
    background: rgba(255,193,7,0.08); color: #ffc107;
    border: 1px solid rgba(255,193,7,0.12);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$jumlah_gelanggang = $jumlah_gelanggang ?? 0;
$is_layout_khusus  = ($jumlah_gelanggang == 2);
$default_col_class = $is_layout_khusus ? 'col-12' : 'col-12 col-xl-6';
?>

<div id="displayWrapper">
    <div id="displayContainer" class="row gx-2 gy-2">
        <?php foreach ($data_partai as $p):
            $idGlg    = $p['id_gelanggang'];
            $nomor    = $p['nomor_partai'];
            $jenis    = $p['jenis_partai'] ?? 'idle';
            $isActive = $jenis !== 'idle';
        ?>
            <div class="match-column <?= esc($default_col_class) ?>" id="col-gelanggang-<?= $idGlg ?>">
                <div class="mntr-card <?= $isActive ? 'active-card' : '' ?>">

                    <!-- Header -->
                    <div class="mntr-header">
                        <span class="mntr-type-badge <?= esc($jenis) ?>">
                            <?= $jenis === 'tanding' ? 'Tanding' : ($jenis === 'seni' ? 'Seni' : 'Idle') ?>
                        </span>
                        <div class="mntr-gelanggang-name update-transition" id="gelanggang-name-<?= $idGlg ?>">
                            <?= esc($p['nama_gelanggang']) ?>
                        </div>
                        <div class="mntr-partai-num <?= !$isActive ? 'idle-num' : '' ?> update-transition" id="partai-val-<?= $idGlg ?>">
                            <?= $nomor !== null ? sprintf('%03d', $nomor) : '---' ?>
                        </div>
                    </div>

                    <!-- Footer / Athletes -->
                    <?php if ($jenis === 'tanding'): ?>
                        <div class="mntr-footer">
                            <div class="mntr-athlete biru">
                                <div class="mntr-athlete-name update-transition" id="atlet-biru-<?= $idGlg ?>"><?= esc($p['nama_atlet_biru'] ?? '-') ?></div>
                                <div class="mntr-athlete-kontingen" id="kontingen-biru-<?= $idGlg ?>"><?= esc($p['nama_kontingen_biru'] ?? '') ?></div>
                            </div>
                            <div class="mntr-athlete vs">
                                <span style="font-size: clamp(1rem, 1.5vw, 1.3rem);">VS</span>
                            </div>
                            <div class="mntr-athlete merah">
                                <div class="mntr-athlete-name update-transition" id="atlet-merah-<?= $idGlg ?>"><?= esc($p['nama_atlet_merah'] ?? '-') ?></div>
                                <div class="mntr-athlete-kontingen" id="kontingen-merah-<?= $idGlg ?>"><?= esc($p['nama_kontingen_merah'] ?? '') ?></div>
                            </div>
                        </div>
                        <?php if (($p['skor_biru'] ?? 0) > 0 || ($p['skor_merah'] ?? 0) > 0): ?>
                        <div class="mntr-skor-row">
                            <div class="mntr-skor biru update-transition" id="skor-biru-<?= $idGlg ?>"><?= (int) ($p['skor_biru'] ?? 0) ?></div>
                            <div class="mntr-skor merah update-transition" id="skor-merah-<?= $idGlg ?>"><?= (int) ($p['skor_merah'] ?? 0) ?></div>
                        </div>
                        <?php endif; ?>

                    <?php elseif ($jenis === 'seni'): ?>
                        <div class="mntr-footer">
                            <div class="mntr-athlete seni-single" style="flex:1">
                                <div class="mntr-athlete-name update-transition" id="atlet-seni-<?= $idGlg ?>">
                                    <?= esc($p['nama_atlet'] ?? '-') ?>
                                </div>
                                <div class="mntr-athlete-kontingen"><?= esc($p['nama_kontingen'] ?? '') ?></div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="mntr-idle">
                            <i class="fas fa-circle-pause"></i>
                            <span>Menunggu Partai</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Settings Button -->
<button class="mntr-btn settings" id="btnOpenSettings" title="Pengaturan Tampilan">
    <i class="fas fa-gear"></i>
</button>

<!-- Back Button -->
<a href="<?= base_url('monitoring') ?>" class="mntr-btn back" title="Kembali">
    <i class="fas fa-arrow-left"></i>
</a>

<!-- Settings Modal -->
<div class="modal fade mntr-modal" id="settingsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" style="font-size: 1.1rem;">
                    <i class="fas fa-sliders me-2" style="color: var(--brand-primary)"></i>Display Settings
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning py-1 px-2 small mb-3 text-center">
                    <i class="fas fa-pause me-1"></i> Auto-refresh paused.
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small mb-1">Tema</label>
                        <select class="form-select form-select-sm" id="themeSelect">
                            <option value="dark">Dark Modern (Default)</option>
                            <option value="light">Light</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small mb-1">Rotation</label>
                        <select class="form-select form-select-sm" id="rotation">
                            <option value="0">Normal</option>
                            <option value="90">90&deg; CW</option>
                            <option value="270">90&deg; CCW</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-2">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small mb-1">Column Width</label>
                        <select class="form-select form-select-sm" id="columnWidth">
                            <option value="default">Auto</option>
                            <option value="col-12 col-xl-6">50%</option>
                            <option value="col-12 col-xl-4">33%</option>
                            <option value="col-12 col-xl-3">25%</option>
                            <option value="col-12">100%</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label d-flex justify-content-between fw-bold small mb-1">
                            <span>Header Font</span>
                            <span class="text-muted" id="fontSizeValue"></span>
                        </label>
                        <input type="range" class="form-range" id="fontSizeRange" min="5" max="30" step="0.5">
                    </div>
                </div>
                <hr style="border-color:rgba(255,255,255,0.06)">
                <h6 class="text-muted small mb-2 fw-bold">Athlete Names</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label d-flex justify-content-between fw-bold small mb-1">
                            <span>VS Size</span>
                            <span class="text-muted" id="vsTextValue"></span>
                        </label>
                        <input type="range" class="form-range" id="vsTextRange" min="1" max="8" step="0.1">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label d-flex justify-content-between fw-bold small mb-1">
                            <span>Names Size</span>
                            <span class="text-muted" id="playerNamesValue"></span>
                        </label>
                        <input type="range" class="form-range" id="playerNamesRange" min="1" max="8" step="0.1">
                    </div>
                </div>
            </div>
            <div class="modal-footer py-1">
                <button type="button" class="btn btn-sm btn-outline-danger me-auto" onclick="resetSettings()">Reset</button>
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveSettings()">Save</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const defaultPhpColClass = "<?= $default_col_class ?>";
const defaultFontSize = "<?= $is_layout_khusus ? 20 : 11.5 ?>";
let refreshTimer;
const REFRESH_INTERVAL = 30000;
let isUpdating = false;

document.addEventListener('DOMContentLoaded', function() {
    loadSettings();
    applySettings();
    startAutoRefresh();
    setupModalEvents();
    document.getElementById('btnOpenSettings').addEventListener('click', openSettings);
});

function startAutoRefresh() {
    clearTimeout(refreshTimer);
    console.log("Timer running...");
    refreshTimer = setTimeout(performUpdate, REFRESH_INTERVAL);
}

function stopAutoRefresh() { clearTimeout(refreshTimer); }
async function performUpdate() {
    if (isUpdating) return;
    isUpdating = true;
    try {
        const response = await fetch(window.location.href);
        const text = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(text, 'text/html');
        updateElements(newDoc);
    } catch (e) { console.error("Update error:", e); }
    finally { isUpdating = false; startAutoRefresh(); }
}

function updateElements(newDoc) {
    document.querySelectorAll('[id^="partai-val-"]').forEach(function(el) {
        var nw = newDoc.getElementById(el.id);
        if (nw && el.innerText.trim() !== nw.innerText.trim()) animateChange(el, nw.innerText.trim());
    });
    document.querySelectorAll('[id^="atlet-biru-"], [id^="atlet-merah-"], [id^="atlet-seni-"]').forEach(function(el) {
        var nw = newDoc.getElementById(el.id);
        if (nw && el.innerText.trim() !== nw.innerText.trim()) animateChange(el, nw.innerText.trim());
    });
}

function animateChange(element, newValue) {
    element.classList.add('fade-out-up');
    setTimeout(function() {
        element.innerText = newValue;
        element.classList.remove('fade-out-up');
        element.classList.add('fade-in-down');
        void element.offsetWidth;
        element.classList.remove('fade-in-down');
    }, 300);
}

function setupModalEvents() {
    var modal = document.getElementById('settingsModal');
    modal.addEventListener('show.bs.modal', stopAutoRefresh);
    modal.addEventListener('hidden.bs.modal', startAutoRefresh);
}

function loadSettings() {
    var s = JSON.parse(localStorage.getItem('matchDisplaySettings') || '{}');
    document.getElementById('themeSelect').value = s.theme || 'dark';
    document.getElementById('fontSizeRange').value = s.fontSize || defaultFontSize;
    document.getElementById('columnWidth').value = s.columnWidth || 'default';
    document.getElementById('rotation').value = s.rotation || '0';
    document.getElementById('vsTextRange').value = s.vsTextSize || 2;
    document.getElementById('playerNamesRange').value = s.playerNamesSize || 1.5;
    updateAllLabels();
}
function updateAllLabels() {
    document.getElementById('fontSizeValue').textContent = document.getElementById('fontSizeRange').value + 'em';
    document.getElementById('vsTextValue').textContent = document.getElementById('vsTextRange').value + 'em';
    document.getElementById('playerNamesValue').textContent = document.getElementById('playerNamesRange').value + 'em';
}
function openSettings() { new bootstrap.Modal(document.getElementById('settingsModal')).show(); }
function resetSettings() {
    if (confirm('Reset semua pengaturan ke default?')) {
        localStorage.removeItem('matchDisplaySettings');
        loadSettings(); applySettings();
    }
}
function saveSettings() {
    var s = {
        theme: document.getElementById('themeSelect').value,
        fontSize: document.getElementById('fontSizeRange').value,
        columnWidth: document.getElementById('columnWidth').value,
        rotation: document.getElementById('rotation').value,
        vsTextSize: document.getElementById('vsTextRange').value,
        playerNamesSize: document.getElementById('playerNamesRange').value
    };
    localStorage.setItem('matchDisplaySettings', JSON.stringify(s));
    applySettings();
    bootstrap.Modal.getInstance(document.getElementById('settingsModal')).hide();
}
function applySettings() {
    var s = JSON.parse(localStorage.getItem('matchDisplaySettings') || '{}');
    if ((s.theme || 'dark') === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
    document.querySelectorAll('.mntr-gelanggang-name').forEach(function(el) {
        el.style.fontSize = (s.fontSize || defaultFontSize) + 'em';
    });
    document.querySelectorAll('.mntr-athlete-name').forEach(function(el) {
        el.style.fontSize = (s.playerNamesSize || 1.5) + 'em';
    });
    document.querySelectorAll('.mntr-athlete.vs span').forEach(function(el) {
        el.style.fontSize = (s.vsTextSize || 2) + 'em';
    });
    var cls = (s.columnWidth && s.columnWidth !== 'default') ? s.columnWidth : defaultPhpColClass;
    document.querySelectorAll('.match-column').forEach(function(el) {
        el.className = el.className.replace(/\bcol-[a-z0-9-]+\b/g, '').trim();
        el.className = 'match-column ' + cls;
    });
    var c = document.getElementById('displayContainer');
    c.className = c.className.replace(/rotate-\d+/g, '').trim();
    c.classList.add('rotate-' + (s.rotation || '0'));
    var w = document.getElementById('displayWrapper');
    if (s.rotation === '90' || s.rotation === '270') {
        w.style.padding = '0'; document.body.style.overflow = 'hidden';
    } else {
        w.style.padding = ''; document.body.style.overflow = '';
    }
}
['fontSizeRange','vsTextRange','playerNamesRange'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', updateAllLabels);
});
</script>
<?= $this->endSection() ?>
