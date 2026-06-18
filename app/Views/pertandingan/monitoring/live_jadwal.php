<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
:root {
    --brand-primary: #C60000;
    --bg-color: #f8f9fa;
    --bg-pattern: radial-gradient(#adb5bd 1.5px, transparent 1.5px);
    --bg-size: 20px 20px;
    --card-bg: linear-gradient(135deg, var(--brand-primary) 0%, #800000 100%);
    --card-text: #ffffff;
    --card-shadow: 0 8px 20px rgba(198, 0, 0, 0.25);
    --card-border: none;
    --text-color: #212529;
    --settings-btn-bg: var(--brand-primary);
    --settings-btn-color: #fff;
}

[data-theme="dark"] {
    --bg-color: #f8f9fa;
    --bg-pattern: radial-gradient(#adb5bd 1.5px, transparent 1.5px);
    --bg-size: 20px 20px;
    --card-bg: #212529;
    --card-text: #e2e8f0;
    --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
    --card-border: 1px solid rgba(0, 0, 0, 0.1);
    --text-color: #212529;
    --settings-btn-bg: #343a40;
    --settings-btn-color: #fff;
}

body {
    margin: 0; padding: 0; overflow-x: hidden;
    background-color: var(--bg-color);
    background-image: var(--bg-pattern);
    background-size: var(--bg-size);
    color: var(--text-color);
    transition: background-color 0.5s ease;
}

.card {
    background: var(--card-bg) !important;
    color: var(--card-text) !important;
    box-shadow: var(--card-shadow) !important;
    border: var(--card-border) !important;
    transition: all 0.3s ease;
}
.card .text-white { color: var(--card-text) !important; }

.settings-btn {
    position: fixed; bottom: 20px; right: 20px; z-index: 1000;
    opacity: 0.6; transition: all 0.3s;
    background-color: var(--settings-btn-bg); color: var(--settings-btn-color);
    border: none; border-radius: 50%; width: 50px; height: 50px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
}
.settings-btn:hover { opacity: 1; transform: rotate(90deg); background-color: var(--settings-btn-color); color: var(--settings-btn-bg); }

.back-btn {
    position: fixed; bottom: 20px; left: 20px; z-index: 1000;
    opacity: 0.6; transition: all 0.3s;
    background-color: #333; color: #fff;
    border: none; border-radius: 50%; width: 50px; height: 50px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    text-decoration: none;
}
.back-btn:hover { opacity: 1; background-color: #555; color: #fff; }

.icon-svg { width: 24px; height: 24px; fill: currentColor; }

#displayWrapper { width: 100vw; height: 100vh; position: relative; overflow: hidden; }
#displayContainer { transition: transform 0.3s ease; width: 100%; height: 100%; position: absolute; top: 0; }

.rotate-0 { transform: none; width: 100vw !important; height: auto !important; }
.rotate-90 { transform: rotate(90deg); transform-origin: bottom left; width: 100vh !important; height: 100vw !important; position: absolute; top: -100vh; left: 0; }
.rotate-270 { transform: rotate(270deg); transform-origin: top right; width: 100vh !important; height: 100vw !important; position: absolute; top: 0; left: -100vh; }

.gelanggang-text, .vs-text, .player-name { transition: font-size 0.3s ease; }
.match-column { transition: width 0.3s ease; }

.update-transition { transition: opacity 0.5s ease-in-out, transform 0.5s ease; display: inline-block; }
.fade-out-up { opacity: 0; transform: translateY(-20px); }
.fade-in-down { opacity: 0; transform: translateY(20px); }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$jumlah_gelanggang = $jumlah_gelanggang ?? 0;
$is_layout_khusus  = ($jumlah_gelanggang == 2);
$default_col_class = $is_layout_khusus ? 'col-12' : 'col-12 col-xl-6';
?>

<div id="displayWrapper">
    <div id="displayContainer" class="row mx-0">
        <?php foreach ($data_partai as $p): ?>
            <?php
            $idGlg    = $p['id_gelanggang'];
            $nomor    = $p['nomor_partai'];
            $jenis    = $p['jenis_partai'] ?? 'idle';
            ?>
            <div class="match-column <?= esc($default_col_class) ?>" id="col-gelanggang-<?= $idGlg ?>">
                <div class="card my-1">
                    <div class="card-body py-2">
                        <div class="col-12">
                            <p class="gelanggang-text lh-1 m-0 fw-bolder text-white py-2 text-center">
                                <?= esc($p['nama_gelanggang']) ?>
                                <span class="text-white update-transition" id="partai-val-<?= $idGlg ?>">
                                    <?= $nomor !== null ? sprintf('%03d', $nomor) : '---' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    <?php if (!$is_layout_khusus): ?>
                    <div class="card-footer row m-0 p-0" id="footer-val-<?= $idGlg ?>">
                        <?php if ($jenis === 'tanding'): ?>
                            <div class="col-12 col-lg-5 bg-info bg-gradient h5 text-center py-2 m-0 text-truncate text-white player-name update-transition" id="atlet-biru-<?= $idGlg ?>">
                                <?= esc($p['nama_atlet_biru'] ?? '-') ?>
                            </div>
                            <div class="col-12 col-lg-2 bg-white bg-gradient h5 text-center py-2 m-0 text-dark vs-text">VS</div>
                            <div class="col-12 col-lg-5 bg-danger bg-gradient h5 text-center py-2 m-0 text-white text-truncate player-name update-transition" id="atlet-merah-<?= $idGlg ?>">
                                <?= esc($p['nama_atlet_merah'] ?? '-') ?>
                            </div>
                        <?php elseif ($jenis === 'seni'): ?>
                            <div class="col-12 bg-secondary bg-gradient h5 text-center py-2 m-0 text-white text-truncate player-name update-transition" id="atlet-seni-<?= $idGlg ?>">
                                <?= esc($p['nama_atlet'] ?? '-') ?>
                            </div>
                        <?php else: ?>
                            <div class="col-12 bg-secondary bg-gradient h5 text-center py-2 m-0 text-white">
                                Menunggu Partai...
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Settings Button -->
<button class="settings-btn" id="btnOpenSettings" title="Pengaturan Tampilan">
    <svg class="icon-svg" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path d="M19.14,12.94c0.04-0.3,0.06-0.61,0.06-0.94c0-0.32-0.02-0.64-0.07-0.94l2.03-1.58c0.18-0.14,0.23-0.41,0.12-0.61 l-1.92-3.32c-0.12-0.22-0.37-0.29-0.59-0.22l-2.39,0.96c-0.5-0.38-1.03-0.7-1.62-0.94L14.4,2.81c-0.04-0.24-0.24-0.41-0.48-0.41 h-3.84c-0.24,0-0.43,0.17-0.47,0.41L9.25,5.35C8.66,5.59,8.12,5.92,7.63,6.29L5.24,5.33c-0.22-0.08-0.47,0-0.59,0.22L2.74,8.87 C2.62,9.08,2.66,9.34,2.86,9.48l2.03,1.58C4.84,11.36,4.8,11.69,4.8,12s0.02,0.64,0.07,0.94l-2.03,1.58 c-0.18,0.14-0.23,0.41-0.12,0.61l1.92,3.32c0.12,0.22,0.37,0.29,0.59,0.22l2.39-0.96c0.5,0.38,1.03,0.7,1.62,0.94l0.36,2.54 c0.05,0.24,0.24,0.41,0.48,0.41h3.84c0.24,0,0.44-0.17,0.47-0.41l0.36-2.54c0.59-0.24,1.13-0.56,1.62-0.94l2.39,0.96 c0.22,0.08,0.47,0,0.59-0.22l1.92-3.32c0.12-0.22,0.07-0.47-0.12-0.61L19.14,12.94z M12,15.6c-1.98,0-3.6-1.62-3.6-3.6 s1.62-3.6,3.6-3.6s3.6,1.62,3.6,3.6S13.98,15.6,12,15.6z" />
    </svg>
</button>

<!-- Back Button -->
<a href="<?= base_url('monitoring') ?>" class="back-btn" title="Kembali ke Menu Utama">
    <i class="fas fa-arrow-left fa-lg"></i>
</a>

<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light py-2">
                <h5 class="modal-title d-flex align-items-center" style="font-size: 1.1rem;">
                    <svg class="icon-svg me-2" style="width:18px;height:18px;fill:#333;" viewBox="0 0 24 24">
                        <path d="M4 18h16v-2H4v2zM4 13h16v-2H4v2zM4 6v2h16V6H4z" />
                    </svg>
                    Display Settings
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-dark">
                <div class="alert alert-warning py-1 px-2 small mb-3 text-center">
                    <i class="fas fa-pause me-1"></i> Auto-refresh paused.
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small mb-1">Tema / Theme</label>
                        <select class="form-select form-select-sm" id="themeSelect">
                            <option value="digital">Digital Pencak Silat (Red)</option>
                            <option value="dark">Dark Cards (Alternative)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small mb-1">Rotation</label>
                        <select class="form-select form-select-sm" id="rotation">
                            <option value="0">Normal</option>
                            <option value="90">90° Clockwise</option>
                            <option value="270">90° Counter-Clockwise</option>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-2">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold small mb-1">Column Width</label>
                        <select class="form-select form-select-sm" id="columnWidth">
                            <option value="default">Auto (Default)</option>
                            <option value="col-12 col-xl-6">50% (2 Col)</option>
                            <option value="col-12 col-xl-4">33% (3 Col)</option>
                            <option value="col-12 col-xl-3">25% (4 Col)</option>
                            <option value="col-12">100% (1 Col)</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label d-flex justify-content-between fw-bold small mb-1">
                            <span>Gelanggang Font</span>
                            <span class="text-muted" id="fontSizeValue"></span>
                        </label>
                        <input type="range" class="form-range form-range-sm" id="fontSizeRange" min="5" max="30" step="0.5">
                    </div>
                </div>
                <hr class="my-2 text-muted">
                <h6 class="text-muted small mb-2 fw-bold">Footer Text (Nama Atlet)</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label d-flex justify-content-between fw-bold small mb-1">
                            <span>VS Text Size</span>
                            <span class="text-muted" id="vsTextValue"></span>
                        </label>
                        <input type="range" class="form-range form-range-sm" id="vsTextRange" min="1" max="8" step="0.1">
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label d-flex justify-content-between fw-bold small mb-1">
                            <span>Names Size</span>
                            <span class="text-muted" id="playerNamesValue"></span>
                        </label>
                        <input type="range" class="form-range form-range-sm" id="playerNamesRange" min="1" max="8" step="0.1">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-1">
                <button type="button" class="btn btn-sm btn-outline-danger me-auto" onclick="resetSettings()">Reset</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
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

function stopAutoRefresh() {
    clearTimeout(refreshTimer);
    console.log("Timer paused.");
}

async function performUpdate() {
    if (isUpdating) return;
    isUpdating = true;
    try {
        console.log("Fetching new data...");
        const response = await fetch(window.location.href);
        const text = await response.text();
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(text, 'text/html');
        updateElements(newDoc);
    } catch (error) {
        console.error("Gagal melakukan update:", error);
    } finally {
        isUpdating = false;
        startAutoRefresh();
    }
}

function updateElements(newDoc) {
    document.querySelectorAll('[id^="partai-val-"]').forEach(function(currentEl) {
        const newEl = newDoc.getElementById(currentEl.id);
        if (newEl) {
            const currentVal = currentEl.innerText.trim();
            const newVal = newEl.innerText.trim();
            if (currentVal !== newVal) animateChange(currentEl, newVal);
        }
    });
    document.querySelectorAll('[id^="atlet-biru-"], [id^="atlet-merah-"], [id^="atlet-seni-"]').forEach(function(currentEl) {
        const newEl = newDoc.getElementById(currentEl.id);
        if (newEl) {
            const currentVal = currentEl.innerText.trim();
            const newVal = newEl.innerText.trim();
            if (currentVal !== newVal) animateChange(currentEl, newVal);
        } else {
            const gelanggangId = currentEl.id.split('-').pop();
            const currentFooter = document.getElementById('footer-val-' + gelanggangId);
            const newFooter = newDoc.getElementById('footer-val-' + gelanggangId);
            if (currentFooter && newFooter && currentFooter.innerHTML !== newFooter.innerHTML) {
                currentFooter.innerHTML = newFooter.innerHTML;
                applySettings();
            }
        }
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
    var settings = JSON.parse(localStorage.getItem('matchDisplaySettings') || '{}');
    document.getElementById('themeSelect').value = settings.theme || 'digital';
    document.getElementById('fontSizeRange').value = settings.fontSize || defaultFontSize;
    document.getElementById('columnWidth').value = settings.columnWidth || 'default';
    document.getElementById('rotation').value = settings.rotation || '0';
    document.getElementById('vsTextRange').value = settings.vsTextSize || 2;
    document.getElementById('playerNamesRange').value = settings.playerNamesSize || 1.5;
    updateAllLabels();
}

function updateAllLabels() {
    document.getElementById('fontSizeValue').textContent = document.getElementById('fontSizeRange').value + 'em';
    document.getElementById('vsTextValue').textContent = document.getElementById('vsTextRange').value + 'em';
    document.getElementById('playerNamesValue').textContent = document.getElementById('playerNamesRange').value + 'em';
}

function openSettings() {
    var modal = new bootstrap.Modal(document.getElementById('settingsModal'));
    modal.show();
}

function resetSettings() {
    if (confirm('Reset semua pengaturan ke default?')) {
        localStorage.removeItem('matchDisplaySettings');
        loadSettings();
        applySettings();
    }
}

function saveSettings() {
    var settings = {
        theme: document.getElementById('themeSelect').value,
        fontSize: document.getElementById('fontSizeRange').value,
        columnWidth: document.getElementById('columnWidth').value,
        rotation: document.getElementById('rotation').value,
        vsTextSize: document.getElementById('vsTextRange').value,
        playerNamesSize: document.getElementById('playerNamesRange').value
    };
    localStorage.setItem('matchDisplaySettings', JSON.stringify(settings));
    applySettings();
    var modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
    modal.hide();
}

function applySettings() {
    var settings = JSON.parse(localStorage.getItem('matchDisplaySettings') || '{}');
    if ((settings.theme || 'digital') === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    } else {
        document.documentElement.removeAttribute('data-theme');
    }
    document.querySelectorAll('.gelanggang-text').forEach(function(el) {
        el.style.fontSize = (settings.fontSize || defaultFontSize) + 'em';
    });
    document.querySelectorAll('.vs-text').forEach(function(el) {
        el.style.fontSize = (settings.vsTextSize || 2) + 'em';
    });
    document.querySelectorAll('.player-name').forEach(function(el) {
        el.style.fontSize = (settings.playerNamesSize || 1.5) + 'em';
    });
    var targetClass = (settings.columnWidth && settings.columnWidth !== 'default') ? settings.columnWidth : defaultPhpColClass;
    document.querySelectorAll('.match-column').forEach(function(el) {
        el.className = el.className.replace(/\bcol-[a-z0-9-]+\b/g, '').trim();
        el.className = 'match-column ' + targetClass;
    });
    var container = document.getElementById('displayContainer');
    container.className = container.className.replace(/rotate-\d+/g, '').trim();
    container.classList.add('rotate-' + (settings.rotation || '0'));
    var wrapper = document.getElementById('displayWrapper');
    if (settings.rotation === '90' || settings.rotation === '270') {
        wrapper.style.padding = '0';
        document.body.style.overflow = 'hidden';
    } else {
        wrapper.style.padding = '';
        document.body.style.overflow = '';
    }
}

['fontSizeRange', 'vsTextRange', 'playerNamesRange'].forEach(function(id) {
    document.getElementById(id).addEventListener('input', updateAllLabels);
});
</script>
<?= $this->endSection() ?>
