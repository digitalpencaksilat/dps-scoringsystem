
<?php
/**
 * Shared navigation bar for all perangkat pertandingan.
 * 
 * Expected variables:
 *  - $nav_role     : string  — posisi (juri, ketua_pertandingan, sekretaris, layar, broadcast_operator)
 *  - $nav_active   : string  — active page key (home, tanding, seni, daftar_nilai_tanding, daftar_nilai_seni)
 *  - $nav_extra    : array   — extra nav items [['url'=>..., 'label'=>..., 'key'=>...], ...]
 */

$role    = $nav_role ?? session()->get('posisi') ?? 'layar';
$active  = $nav_active ?? 'home';
$nama    = session()->get('nama') ?? 'Operator';
$gelanggang = session()->get('nama_gelanggang') ?? 'Gelanggang';

// Route prefix per role
$homeRoutes = [
    'juri'                 => '/juri',
    'ketua_pertandingan'   => '/ketua-pertandingan',
    'sekretaris'           => '/sekretaris-pertandingan',
    'timer'                => '/sekretaris-pertandingan',
    'layar'                => '/layar',
    'broadcast_operator'   => '/broadcast-operator',
];
$homeUrl = $homeRoutes[$role] ?? '/perangkat-pertandingan';

// Role display name
$roleLabels = [
    'juri'                 => 'Juri',
    'ketua_pertandingan'   => 'Ketua Pertandingan',
    'sekretaris'           => 'Sekretaris',
    'timer'                => 'Timer',
    'layar'                => 'Layar',
    'broadcast_operator'   => 'Broadcast Operator',
];
$roleLabel = $roleLabels[$role] ?? ucfirst($role);

// Build nav items per role
$navItems = [];
switch ($role) {
    case 'ketua_pertandingan':
        $navItems = [
            ['url' => '/ketua-pertandingan',                    'label' => 'Home',              'key' => 'home'],
            ['url' => '/ketua-pertandingan/tanding',            'label' => 'Tanding',           'key' => 'tanding'],
            ['url' => '/ketua-pertandingan/seni',               'label' => 'Seni',              'key' => 'seni'],
            ['url' => '/ketua-pertandingan/daftar-nilai-tanding','label' => 'Review Tanding',   'key' => 'daftar_nilai_tanding'],
            ['url' => '/ketua-pertandingan/daftar-nilai-seni',  'label' => 'Review Seni',       'key' => 'daftar_nilai_seni'],
        ];
        break;
    case 'juri':
        $navItems = [
            ['url' => '/juri',          'label' => 'Home',     'key' => 'home'],
            ['url' => '/juri/tanding',   'label' => 'Tanding', 'key' => 'tanding'],
            ['url' => '/juri/seni',      'label' => 'Seni',    'key' => 'seni'],
        ];
        break;
    case 'sekretaris':
    case 'timer':
        $navItems = [
            ['url' => '/sekretaris-pertandingan',              'label' => 'Home',          'key' => 'home'],
            ['url' => '/sekretaris-pertandingan/timer-tanding','label' => 'Timer Tanding', 'key' => 'tanding'],
            ['url' => '/sekretaris-pertandingan/timer-seni',   'label' => 'Timer Seni',    'key' => 'seni'],
        ];
        break;
    case 'layar':
        $navItems = [
            ['url' => '/layar',          'label' => 'Home',    'key' => 'home'],
            ['url' => '/layar/tanding',   'label' => 'Tanding','key' => 'tanding'],
            ['url' => '/layar/seni',      'label' => 'Seni',   'key' => 'seni'],
        ];
        break;
    case 'broadcast_operator':
        $navItems = [
            ['url' => '/broadcast-operator',         'label' => 'Home',    'key' => 'home'],
            ['url' => '/broadcast-operator/tanding', 'label' => 'Tanding', 'key' => 'tanding'],
        ];
        break;
}

// Merge extra items if provided
if (! empty($nav_extra) && is_array($nav_extra)) {
    $navItems = array_merge($navItems, $nav_extra);
}
?>

<nav class="navbar navbar-expand-md navbar-dark bg-dark sticky-top shadow-sm py-1">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2 py-0" href="<?= base_url($homeUrl) ?>">
            <img src="<?= base_url('assets/images/brand/dps/logo.png') ?>" alt="DPS" height="28"
                 onerror="this.style.display='none'">
            <span class="fw-bold small text-uppercase d-none d-md-inline"><?= esc($gelanggang) ?></span>
        </a>

        <!-- Toggler (mobile) -->
        <button class="navbar-toggler border-0 py-0" type="button" data-bs-toggle="collapse" data-bs-target="#navPerangkat">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navPerangkat">
            <!-- Left nav items -->
            <ul class="navbar-nav me-auto">
                <?php foreach ($navItems as $item): ?>
                <li class="nav-item">
                    <a class="nav-link <?= ($active === $item['key']) ? 'active fw-semibold' : '' ?>"
                       href="<?= base_url($item['url']) ?>">
                        <?= esc($item['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>

            <!-- Right side: role badge + logout -->
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <span class="badge bg-danger bg-opacity-75 text-white small">
                        <i class="fa-solid fa-user-shield me-1"></i><?= esc($roleLabel) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <span class="text-light small d-none d-lg-inline"><?= esc($nama) ?></span>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-outline-light py-0 px-2" href="<?= base_url('/perangkat-pertandingan/logout') ?>">
                        <i class="fa-solid fa-right-from-bracket me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
