<?php
/**
 * Shared Navigation Bar — All Perangkat Pertandingan
 * 
 * Seamless role-aware navbar with dark glassmorphism styling.
 * Serves: sekretaris, juri, ketua_pertandingan, layar, broadcast_operator.
 * 
 * Usage:
 *   <?= view('pertandingan/components/navbar', [
 *       'nav_role'    => 'ketua_pertandingan',
 *       'nav_active'  => 'home',
 *   ]) ?>
 * 
 * Params:
 *   nav_role          string  — posisi ('sekretaris','juri','ketua_pertandingan','layar','broadcast_operator')
 *   nav_active        string  — active nav key ('home','tanding','seni','daftar_nilai_tanding','daftar_nilai_seni','dashboard')
 *   nav_page_type     string  — 'home' | 'timer' | 'scoring' (controls menu variant, default 'home')
 *   nav_modal_sound   ?string — modal ID for sound (sekretaris timer)
 *   nav_modal_format  ?string — modal ID for format (sekretaris timer)
 *   nav_modal_time    ?string — modal ID for time (sekretaris timer)
 *   nav_extra_items   array   — extra nav items [{icon, label, href?, modal?, onclick?}, ...]
 *   nav_brand_logo    ?string — override brand logo URL (default: logo-match-operator.png)
 *   nav_brand_title   ?string — override brand title text
 *   nav_brand_subtitle ?string — override brand subtitle
 * 
 * Legacy sekretaris-only params (backwards compat, deprecated):
 *   active, page_type, modal_sound, modal_format, modal_time, extra_items
 */

// ─── Resolve role ──────────────────────────────────────────────────────────
$role = $nav_role
    ?? session()->get('posisi')
    ?? session()->get('role')
    ?? 'layar';

// Timer-role users are sekretaris under the hood
if ($role === 'timer') {
    $role = 'sekretaris';
}

// ─── Resolve active key ────────────────────────────────────────────────────
$active = $nav_active
    ?? $active          // legacy sekretaris prop
    ?? 'home';

// Map legacy 'dashboard' → 'home'
if ($active === 'dashboard') {
    $active = 'home';
}

// ─── Page type ─────────────────────────────────────────────────────────────
$pageType = $nav_page_type
    ?? $page_type       // legacy sekretaris prop
    ?? 'home';

// ─── Timer modals ──────────────────────────────────────────────────────────
$modalSound  = $nav_modal_sound  ?? $modal_sound  ?? null;
$modalFormat = $nav_modal_format ?? $modal_format ?? null;
$modalTime   = $nav_modal_time   ?? $modal_time   ?? null;

// ─── Extra items ───────────────────────────────────────────────────────────
$extraItems = $nav_extra_items
    ?? $extra_items     // legacy sekretaris prop
    ?? $nav_extra       // legacy shared prop
    ?? [];

// ─── Role Metadata ─────────────────────────────────────────────────────────
$homeUrls = [
    'sekretaris'           => 'sekretaris-pertandingan',
    'juri'                 => 'juri',
    'ketua_pertandingan'   => 'ketua-pertandingan',
    'layar'                => 'layar',
    'broadcast_operator'   => 'broadcast-operator',
];
$homeUrl = $homeUrls[$role] ?? 'perangkat-pertandingan';

$roleLabels = [
    'sekretaris'           => 'Match Secretary',
    'juri'                 => 'Juri',
    'ketua_pertandingan'   => 'Ketua Pertandingan',
    'layar'                => 'Layar',
    'broadcast_operator'   => 'Broadcast Operator',
];
$roleLabel = $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', $role));

$roleBadge = [
    'sekretaris'           => 'Secretary',
    'juri'                 => 'Juri',
    'ketua_pertandingan'   => 'Ketua',
    'layar'                => 'Layar',
    'broadcast_operator'   => 'B. Operator',
];
$roleBadgeText = $roleBadge[$role] ?? $roleLabel;

// Role-specific icons
$roleIcons = [
    'sekretaris'           => 'fa-solid fa-user-shield',
    'juri'                 => 'fa-solid fa-scale-balanced',
    'ketua_pertandingan'   => 'fa-solid fa-shield-halved',
    'layar'                => 'fa-solid fa-tv',
    'broadcast_operator'   => 'fa-solid fa-broadcast-tower',
];

// ─── Brand overrides ───────────────────────────────────────────────────────
$brandLogo     = $nav_brand_logo     ?? 'assets/images/brand/dps/logo-match-operator.png';
$brandTitle    = $nav_brand_title    ?? $roleLabel;
$brandSubtitle = $nav_brand_subtitle ?? 'Digital Pencak Silat';

// ─── Build nav items per role ──────────────────────────────────────────────
$navItems = [];

switch ($role) {
    case 'sekretaris':
        if ($pageType === 'timer') {
            // Timer view: Dashboard + conditional modals + extras
            $navItems[] = [
                'key'   => 'home',
                'href'  => $homeUrl,
                'label' => 'Dashboard',
                'icon'  => 'fa-solid fa-home',
                'blank' => true,
            ];
            if ($modalSound) {
                $navItems[] = [
                    'key'   => 'sound',
                    'modal' => $modalSound,
                    'label' => 'Sound',
                    'icon'  => 'fa-solid fa-volume-high',
                ];
            }
            if ($modalFormat) {
                $navItems[] = [
                    'key'   => 'format',
                    'modal' => $modalFormat,
                    'label' => 'Format',
                    'icon'  => 'fa-solid fa-right-left',
                ];
            }
            if ($modalTime) {
                $navItems[] = [
                    'key'   => 'time',
                    'modal' => $modalTime,
                    'label' => 'Time',
                    'icon'  => 'fa-regular fa-clock',
                ];
            }
        } else {
            // Home view: Dashboard + Control Timer dropdown
            $navItems[] = [
                'key'   => 'home',
                'href'  => $homeUrl,
                'label' => 'Dashboard',
                'icon'  => 'fa-solid fa-home',
            ];

            $navItems[] = [
                'key'     => 'timer',
                'label'   => 'Control Timer',
                'icon'    => 'fa-solid fa-stopwatch',
                'dropdown' => [
                    [
                        'href'  => $homeUrl . '/timer-tanding',
                        'label' => 'Timer Tanding',
                        'icon'  => 'fa-solid fa-fist-raised',
                        'blank' => true,
                    ],
                    [
                        'href'  => $homeUrl . '/timer-seni',
                        'label' => 'Timer Seni (Pool)',
                        'icon'  => 'fa-solid fa-theater-masks',
                        'blank' => true,
                    ],
                    [
                        'href'  => $homeUrl . '/timer-seni',
                        'label' => 'Timer Seni (Battle)',
                        'icon'  => 'fa-solid fa-fire',
                        'blank' => true,
                    ],
                ],
            ];
        }
        break;

    case 'ketua_pertandingan':
        $navItems = [
            ['key' => 'home',                'href' => $homeUrl,                                    'label' => 'Home',              'icon' => 'fa-solid fa-home'],
            ['key' => 'tanding',             'href' => $homeUrl . '/tanding',                       'label' => 'Tanding',           'icon' => 'fa-solid fa-fist-raised'],
            ['key' => 'seni',                'href' => $homeUrl . '/seni',                          'label' => 'Seni',              'icon' => 'fa-solid fa-theater-masks'],
            ['key' => 'daftar_nilai_tanding', 'href' => $homeUrl . '/daftar-nilai-tanding',          'label' => 'Review Tanding',    'icon' => 'fa-solid fa-clipboard-check'],
            ['key' => 'daftar_nilai_seni',    'href' => $homeUrl . '/daftar-nilai-seni',             'label' => 'Review Seni',       'icon' => 'fa-solid fa-clipboard-list'],
        ];
        break;

    case 'juri':
        $navItems = [
            ['key' => 'home',    'href' => $homeUrl,                 'label' => 'Home',     'icon' => 'fa-solid fa-home'],
            ['key' => 'tanding', 'href' => $homeUrl . '/tanding',    'label' => 'Tanding',  'icon' => 'fa-solid fa-fist-raised'],
            ['key' => 'seni',    'href' => $homeUrl . '/seni',       'label' => 'Seni',     'icon' => 'fa-solid fa-theater-masks'],
        ];
        break;

    case 'layar':
        $navItems = [
            ['key' => 'home',    'href' => $homeUrl,                 'label' => 'Home',     'icon' => 'fa-solid fa-home'],
            ['key' => 'tanding', 'href' => $homeUrl . '/tanding',    'label' => 'Tanding',  'icon' => 'fa-solid fa-fist-raised'],
            ['key' => 'seni',    'href' => $homeUrl . '/seni',       'label' => 'Seni',     'icon' => 'fa-solid fa-theater-masks'],
        ];
        break;

    case 'broadcast_operator':
        $navItems = [
            ['key' => 'home',    'href' => $homeUrl,                 'label' => 'Home',     'icon' => 'fa-solid fa-home'],
            ['key' => 'tanding', 'href' => $homeUrl . '/tanding',    'label' => 'Tanding',  'icon' => 'fa-solid fa-fist-raised'],
        ];
        break;
}
?>

<style>
:root {
	--dp-navbar-bg: linear-gradient(135deg, #1a1d22 0%, #0d0f13 100%);
	--dp-navbar-border: rgba(198, 0, 0, 0.15);
	--dp-navbar-height: clamp(3.5rem, 8vh, 4.5rem);
	--dp-accent-red: #c60000;
}

.navbar-dps {
	background: var(--dp-navbar-bg);
	border-bottom: 2px solid var(--dp-navbar-border);
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
	backdrop-filter: blur(10px);
	min-height: var(--dp-navbar-height);
	padding: 0;
	position: sticky;
	top: 0;
	z-index: 1040;
}

.navbar-dps .container-fluid {
	padding: clamp(0.4rem, 1vh, 0.75rem) clamp(0.75rem, 2vw, 1.5rem);
}

/* Brand */
.navbar-brand-dps {
	display: flex;
	align-items: center;
	gap: clamp(0.5rem, 1.5vw, 0.85rem);
	padding: 0;
	margin: 0;
	text-decoration: none;
	transition: opacity 0.2s ease;
}

.navbar-brand-dps:hover {
	opacity: 0.85;
}

.navbar-brand-dps-img {
	height: clamp(2.2rem, 5vh, 3rem);
	width: auto;
	filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
}

.navbar-brand-dps-text {
	display: none;
	flex-direction: column;
	line-height: 1.1;
}

.navbar-brand-dps-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.95rem, 2vw, 1.15rem);
	font-weight: 700;
	color: #fff;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin: 0;
}

.navbar-brand-dps-subtitle {
	font-size: clamp(0.65rem, 1.4vw, 0.75rem);
	color: rgba(255, 255, 255, 0.6);
	font-weight: 500;
}

@media (min-width: 768px) {
	.navbar-brand-dps-text {
		display: flex;
	}
}

/* Toggler */
.navbar-toggler-dps {
	border: 1px solid rgba(255, 255, 255, 0.1);
	background: rgba(198, 0, 0, 0.1);
	padding: 0.4rem 0.6rem;
	border-radius: 0.4rem;
	transition: all 0.2s ease;
}

.navbar-toggler-dps:hover {
	background: rgba(198, 0, 0, 0.2);
	border-color: var(--dp-accent-red);
}

.navbar-toggler-dps:focus {
	box-shadow: 0 0 0 0.2rem rgba(198, 0, 0, 0.25);
	outline: none;
}

.navbar-toggler-icon-dps {
	display: inline-block;
	width: 1.4em;
	height: 1.4em;
	vertical-align: middle;
	background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.85)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
	background-repeat: no-repeat;
	background-position: center;
	background-size: 100%;
}

/* Nav Items */
.navbar-nav-dps {
	gap: clamp(0.3rem, 1vw, 0.6rem);
}

.nav-link-dps {
	font-family: 'Poppins', sans-serif;
	font-size: clamp(0.8rem, 1.6vw, 0.9rem);
	font-weight: 500;
	color: rgba(255, 255, 255, 0.8);
	padding: clamp(0.4rem, 1vh, 0.6rem) clamp(0.6rem, 1.5vw, 0.85rem);
	border-radius: 0.4rem;
	display: flex;
	align-items: center;
	gap: 0.4rem;
	transition: all 0.2s ease;
	white-space: nowrap;
	text-decoration: none;
	cursor: pointer;
}

.nav-link-dps:hover {
	color: #fff;
	background: rgba(198, 0, 0, 0.15);
}

.nav-link-dps.active {
	color: #fff;
	background: linear-gradient(135deg, var(--dp-accent-red) 0%, #900000 100%);
	box-shadow: 0 2px 8px rgba(198, 0, 0, 0.3);
}

.nav-link-dps i {
	font-size: 0.95em;
	flex-shrink: 0;
}

/* Dropdown */
.dropdown-dps {
	position: relative;
}

.dropdown-menu-dps {
	background: linear-gradient(180deg, #1a1d22 0%, #101317 100%);
	border: 1px solid rgba(255, 255, 255, 0.1);
	border-radius: 0.5rem;
	box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
	padding: 0.4rem;
	min-width: 200px;
	margin-top: 0.3rem;
}

.dropdown-item-dps {
	font-family: 'Poppins', sans-serif;
	font-size: clamp(0.8rem, 1.6vw, 0.875rem);
	color: rgba(255, 255, 255, 0.85);
	padding: clamp(0.5rem, 1.2vh, 0.7rem) clamp(0.7rem, 1.8vw, 1rem);
	border-radius: 0.35rem;
	display: flex;
	align-items: center;
	gap: 0.5rem;
	transition: all 0.15s ease;
	text-decoration: none;
}

.dropdown-item-dps:hover {
	color: #fff;
	background: rgba(198, 0, 0, 0.2);
}

.dropdown-divider-dps {
	height: 1px;
	background: rgba(255, 255, 255, 0.08);
	margin: 0.3rem 0;
	border: none;
}

/* Badge */
.badge-role-dps {
	background: rgba(198, 0, 0, 0.15);
	border: 1px solid rgba(198, 0, 0, 0.3);
	color: #fff;
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.7rem, 1.5vw, 0.8rem);
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	padding: clamp(0.35rem, 0.8vh, 0.5rem) clamp(0.6rem, 1.5vw, 0.85rem);
	border-radius: 0.35rem;
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
}

.badge-role-dps i {
	color: var(--dp-accent-red);
	font-size: 1.1em;
}

/* Logout Button */
.btn-logout-dps {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.75rem, 1.5vw, 0.85rem);
	font-weight: 600;
	color: #fff;
	background: linear-gradient(135deg, var(--dp-accent-red) 0%, #900000 100%);
	border: 1px solid rgba(255, 255, 255, 0.1);
	padding: clamp(0.4rem, 1vh, 0.55rem) clamp(0.7rem, 1.8vw, 1rem);
	border-radius: 0.4rem;
	display: inline-flex;
	align-items: center;
	gap: 0.4rem;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	transition: all 0.2s ease;
	box-shadow: 0 2px 8px rgba(198, 0, 0, 0.25);
	text-decoration: none;
}

.btn-logout-dps:hover {
	background: linear-gradient(135deg, #d60000 0%, #a00000 100%);
	box-shadow: 0 4px 16px rgba(198, 0, 0, 0.4);
	transform: translateY(-1px);
	color: #fff;
}

.btn-logout-dps i {
	font-size: 1em;
}

/* Responsive */
@media (max-width: 991px) {
	.navbar-collapse-dps {
		background: linear-gradient(180deg, #1a1d22 0%, #101317 100%);
		border: 1px solid rgba(255, 255, 255, 0.08);
		border-radius: 0.5rem;
		padding: 0.75rem;
		margin-top: 0.75rem;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
	}

	.navbar-nav-dps {
		flex-direction: column;
		gap: 0.3rem;
	}

	.nav-link-dps {
		width: 100%;
		justify-content: flex-start;
	}

	.dropdown-menu-dps {
		position: static !important;
		transform: none !important;
		width: 100%;
		margin-top: 0.3rem;
		box-shadow: none;
		border-left: 2px solid var(--dp-accent-red);
	}

	.badge-role-dps {
		margin: 0.5rem 0;
	}

	.btn-logout-dps {
		width: 100%;
		justify-content: center;
		margin-top: 0.5rem;
	}
}

@media (prefers-reduced-motion: reduce) {
	.nav-link-dps,
	.btn-logout-dps,
	.dropdown-item-dps {
		transition: none !important;
	}
}

.nav-link-dps:focus-visible,
.btn-logout-dps:focus-visible {
	outline: 2px solid rgba(198, 0, 0, 0.6);
	outline-offset: 2px;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-dps">
	<div class="container-fluid">
		<a class="navbar-brand-dps" href="<?= base_url($homeUrl) ?>">
			<img src="<?= base_url($brandLogo) ?>"
				class="navbar-brand-dps-img"
				alt="DPS <?= esc($roleLabel) ?>"
				onerror="this.style.display='none'">
			<div class="navbar-brand-dps-text">
				<span class="navbar-brand-dps-title"><?= esc($brandTitle) ?></span>
				<span class="navbar-brand-dps-subtitle"><?= esc($brandSubtitle) ?></span>
			</div>
		</a>

		<button class="navbar-toggler navbar-toggler-dps" type="button" data-bs-toggle="collapse" data-bs-target="#navbarDpsPerangkat" aria-controls="navbarDpsPerangkat" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon-dps"></span>
		</button>

		<div class="collapse navbar-collapse navbar-collapse-dps" id="navbarDpsPerangkat">
			<ul class="navbar-nav navbar-nav-dps me-auto mb-2 mb-lg-0 ms-lg-3">
				<?php foreach ($navItems as $item): ?>
					<?php if (!empty($item['dropdown']) && is_array($item['dropdown'])): ?>
						<li class="nav-item dropdown dropdown-dps">
							<a class="nav-link-dps dropdown-toggle <?= $active === $item['key'] ? 'active' : '' ?>"
								href="#"
								role="button"
								data-bs-toggle="dropdown"
								aria-expanded="false">
								<?php if (!empty($item['icon'])): ?><i class="<?= esc($item['icon']) ?>"></i><?php endif; ?>
								<?= esc($item['label']) ?>
							</a>
							<ul class="dropdown-menu dropdown-menu-dps">
								<?php 
								$ddCount = count($item['dropdown']);
								$ddIdx = 0;
								foreach ($item['dropdown'] as $dd): 
								?>
								<li>
									<a class="dropdown-item-dps"
										href="<?= base_url($dd['href']) ?>"
										<?= !empty($dd['blank']) ? 'target="_blank" rel="noopener"' : '' ?>>
										<?php if (!empty($dd['icon'])): ?><i class="<?= esc($dd['icon']) ?>"></i><?php endif; ?>
										<?= esc($dd['label']) ?>
									</a>
								</li>
								<?php if (++$ddIdx < $ddCount): ?>
								<li><hr class="dropdown-divider-dps"></li>
								<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php else: ?>
						<li class="nav-item">
							<a class="nav-link-dps <?= $active === $item['key'] ? 'active' : '' ?>"
								<?php if (!empty($item['modal'])): ?>
									data-bs-toggle="modal" data-bs-target="#<?= esc($item['modal'], 'attr') ?>"
								<?php elseif (!empty($item['onclick'])): ?>
									onclick="<?= esc($item['onclick'], 'attr') ?>"
								<?php elseif (!empty($item['href'])): ?>
									href="<?= base_url($item['href']) ?>"
									<?= !empty($item['blank']) ? 'target="_blank" rel="noopener"' : '' ?>
								<?php else: ?>
									href="#"
								<?php endif; ?>>
								<?php if (!empty($item['icon'])): ?><i class="<?= esc($item['icon']) ?>"></i><?php endif; ?>
								<?= esc($item['label']) ?>
							</a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if (!empty($extraItems) && is_array($extraItems)): ?>
					<?php foreach ($extraItems as $item): ?>
					<li class="nav-item">
						<a class="nav-link-dps<?= !empty($item['cursor']) ? ' cursor-pointer' : '' ?>"
							<?= !empty($item['onclick']) ? 'onclick="' . esc($item['onclick'], 'attr') . '"' : '' ?>
							<?= !empty($item['modal']) ? 'data-bs-toggle="modal" data-bs-target="#' . esc($item['modal']) . '"' : '' ?>
							<?= !empty($item['href']) ? 'href="' . esc($item['href']) . '"' : '' ?>>
							<?php if (!empty($item['icon'])): ?><i class="fas <?= esc($item['icon']) ?>"></i> <?php endif; ?>
							<?= esc($item['label'] ?? '') ?>
						</a>
					</li>
					<?php endforeach; ?>
				<?php endif; ?>
			</ul>

			<ul class="navbar-nav navbar-nav-dps ms-auto align-items-lg-center">
				<li class="nav-item d-none d-lg-block">
					<span class="badge-role-dps">
						<i class="<?= $roleIcons[$role] ?? 'fa-solid fa-user-shield' ?>"></i> <?= esc($roleBadgeText) ?>
					</span>
				</li>
				<li class="nav-item">
					<a class="btn-logout-dps" href="<?= base_url('perangkat-pertandingan/logout') ?>">
						<i class="fa-solid fa-power-off"></i> Logout
					</a>
				</li>
			</ul>
		</div>
	</div>
</nav>
