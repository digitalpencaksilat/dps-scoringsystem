<?php
/**
 * Navbar Component for Sekretaris Pertandingan
 * 
 * Usage:
 *   <?= view('components/navbar_sekretaris', ['active' => 'dashboard', 'page_type' => 'home']) ?>
 * 
 * Props:
 *   - active: 'dashboard' | 'timer' (optional, for highlighting active nav)
 *   - page_type: 'home' | 'timer' (optional, controls which menu items show)
 */

$active = $active ?? 'dashboard';
$pageType = $page_type ?? 'home';
?>

<style>
:root {
	--navbar-bg: linear-gradient(135deg, #1a1d22 0%, #0d0f13 100%);
	--navbar-border: rgba(198, 0, 0, 0.15);
	--navbar-height: clamp(3.5rem, 8vh, 4.5rem);
	--accent-red: #c60000;
}

.navbar-sekretaris {
	background: var(--navbar-bg);
	border-bottom: 2px solid var(--navbar-border);
	box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
	backdrop-filter: blur(10px);
	min-height: var(--navbar-height);
	padding: 0;
	position: sticky;
	top: 0;
	z-index: 1040;
}

.navbar-sekretaris .container-fluid {
	padding: clamp(0.4rem, 1vh, 0.75rem) clamp(0.75rem, 2vw, 1.5rem);
}

/* Brand */
.navbar-brand-sekretaris {
	display: flex;
	align-items: center;
	gap: clamp(0.5rem, 1.5vw, 0.85rem);
	padding: 0;
	margin: 0;
	text-decoration: none;
	transition: opacity 0.2s ease;
}

.navbar-brand-sekretaris:hover {
	opacity: 0.85;
}

.navbar-brand-img {
	height: clamp(2.2rem, 5vh, 3rem);
	width: auto;
	filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.3));
}

.navbar-brand-text {
	display: none;
	flex-direction: column;
	line-height: 1.1;
}

.navbar-brand-title {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.95rem, 2vw, 1.15rem);
	font-weight: 700;
	color: #fff;
	text-transform: uppercase;
	letter-spacing: 0.5px;
	margin: 0;
}

.navbar-brand-subtitle {
	font-size: clamp(0.65rem, 1.4vw, 0.75rem);
	color: rgba(255, 255, 255, 0.6);
	font-weight: 500;
}

@media (min-width: 768px) {
	.navbar-brand-text {
		display: flex;
	}
}

/* Toggler */
.navbar-toggler-sekretaris {
	border: 1px solid rgba(255, 255, 255, 0.1);
	background: rgba(198, 0, 0, 0.1);
	padding: 0.4rem 0.6rem;
	border-radius: 0.4rem;
	transition: all 0.2s ease;
}

.navbar-toggler-sekretaris:hover {
	background: rgba(198, 0, 0, 0.2);
	border-color: var(--accent-red);
}

.navbar-toggler-sekretaris:focus {
	box-shadow: 0 0 0 0.2rem rgba(198, 0, 0, 0.25);
	outline: none;
}

.navbar-toggler-icon-sekretaris {
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
.navbar-nav-sekretaris {
	gap: clamp(0.3rem, 1vw, 0.6rem);
}

.nav-link-sekretaris {
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

.nav-link-sekretaris:hover {
	color: #fff;
	background: rgba(198, 0, 0, 0.15);
}

.nav-link-sekretaris.active {
	color: #fff;
	background: linear-gradient(135deg, var(--accent-red) 0%, #900000 100%);
	box-shadow: 0 2px 8px rgba(198, 0, 0, 0.3);
}

.nav-link-sekretaris i {
	font-size: 0.95em;
	flex-shrink: 0;
}

/* Dropdown */
.dropdown-sekretaris {
	position: relative;
}

.dropdown-menu-sekretaris {
	background: linear-gradient(180deg, #1a1d22 0%, #101317 100%);
	border: 1px solid rgba(255, 255, 255, 0.1);
	border-radius: 0.5rem;
	box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
	padding: 0.4rem;
	min-width: 200px;
	margin-top: 0.3rem;
}

.dropdown-item-sekretaris {
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

.dropdown-item-sekretaris:hover {
	color: #fff;
	background: rgba(198, 0, 0, 0.2);
}

.dropdown-divider-sekretaris {
	height: 1px;
	background: rgba(255, 255, 255, 0.08);
	margin: 0.3rem 0;
	border: none;
}

/* Badge */
.badge-role {
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

.badge-role i {
	color: var(--accent-red);
	font-size: 1.1em;
}

/* Logout Button */
.btn-logout-sekretaris {
	font-family: 'Oswald', sans-serif;
	font-size: clamp(0.75rem, 1.5vw, 0.85rem);
	font-weight: 600;
	color: #fff;
	background: linear-gradient(135deg, var(--accent-red) 0%, #900000 100%);
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

.btn-logout-sekretaris:hover {
	background: linear-gradient(135deg, #d60000 0%, #a00000 100%);
	box-shadow: 0 4px 16px rgba(198, 0, 0, 0.4);
	transform: translateY(-1px);
	color: #fff;
}

.btn-logout-sekretaris i {
	font-size: 1em;
}

/* Responsive */
@media (max-width: 991px) {
	.navbar-collapse {
		background: linear-gradient(180deg, #1a1d22 0%, #101317 100%);
		border: 1px solid rgba(255, 255, 255, 0.08);
		border-radius: 0.5rem;
		padding: 0.75rem;
		margin-top: 0.75rem;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
	}

	.navbar-nav-sekretaris {
		flex-direction: column;
		gap: 0.3rem;
	}

	.nav-link-sekretaris {
		width: 100%;
		justify-content: flex-start;
	}

	.dropdown-menu-sekretaris {
		position: static !important;
		transform: none !important;
		width: 100%;
		margin-top: 0.3rem;
		box-shadow: none;
		border-left: 2px solid var(--accent-red);
	}

	.badge-role {
		margin: 0.5rem 0;
	}

	.btn-logout-sekretaris {
		width: 100%;
		justify-content: center;
		margin-top: 0.5rem;
	}
}

@media (prefers-reduced-motion: reduce) {
	.nav-link-sekretaris,
	.btn-logout-sekretaris,
	.dropdown-item-sekretaris {
		transition: none !important;
	}
}

.nav-link-sekretaris:focus-visible,
.btn-logout-sekretaris:focus-visible {
	outline: 2px solid rgba(198, 0, 0, 0.6);
	outline-offset: 2px;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-sekretaris">
	<div class="container-fluid">
		<a class="navbar-brand-sekretaris" href="<?= base_url('sekretaris-pertandingan') ?>">
			<img src="<?= base_url('assets/images/brand/dps/logo-match-operator.png') ?>"
				class="navbar-brand-img"
				alt="DPS Match Operator"
				onerror="this.style.display='none'">
			<div class="navbar-brand-text">
				<span class="navbar-brand-title">Match Secretary</span>
				<span class="navbar-brand-subtitle">Digital Pencak Silat</span>
			</div>
		</a>

		<button class="navbar-toggler navbar-toggler-sekretaris" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSekretaris" aria-controls="navbarSekretaris" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon-sekretaris"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarSekretaris">
			<ul class="navbar-nav navbar-nav-sekretaris me-auto mb-2 mb-lg-0 ms-lg-3">
				<li class="nav-item">
					<a class="nav-link-sekretaris <?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('sekretaris-pertandingan') ?>">
						<i class="fas fa-home"></i> Dashboard
					</a>
				</li>

				<?php if ($pageType === 'home'): ?>
				<li class="nav-item dropdown dropdown-sekretaris">
					<a class="nav-link-sekretaris dropdown-toggle <?= $active === 'timer' ? 'active' : '' ?>" href="#" id="dropdownTimer" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="fas fa-stopwatch"></i> Control Timer
					</a>
					<ul class="dropdown-menu dropdown-menu-sekretaris" aria-labelledby="dropdownTimer">
						<li><a class="dropdown-item-sekretaris" href="<?= base_url('sekretaris-pertandingan/timer-tanding') ?>" target="_blank">
							<i class="fas fa-fist-raised"></i> Timer Tanding
						</a></li>
						<li><hr class="dropdown-divider-sekretaris"></li>
						<li><a class="dropdown-item-sekretaris" href="<?= base_url('sekretaris-pertandingan/timer-seni') ?>" target="_blank">
							<i class="fas fa-theater-masks"></i> Timer Seni (Pool)
						</a></li>
						<li><a class="dropdown-item-sekretaris" href="<?= base_url('sekretaris-pertandingan/timer-seni/battle') ?>" target="_blank">
							<i class="fas fa-fire"></i> Timer Seni (Battle)
						</a></li>
					</ul>
				</li>
				<?php endif; ?>

				<?php if ($pageType === 'timer'): ?>
				<?php if (isset($modal_sound)): ?>
				<li class="nav-item">
					<a class="nav-link-sekretaris" data-bs-toggle="modal" data-bs-target="#<?= $modal_sound ?>">
						<i class="fas fa-volume-up"></i> Sound
					</a>
				</li>
				<?php endif; ?>
				<?php if (isset($modal_format)): ?>
				<li class="nav-item">
					<a class="nav-link-sekretaris" data-bs-toggle="modal" data-bs-target="#<?= $modal_format ?>">
						<i class="fas fa-exchange-alt"></i> Format
					</a>
				</li>
				<?php endif; ?>
				<?php if (isset($modal_time)): ?>
				<li class="nav-item">
					<a class="nav-link-sekretaris" data-bs-toggle="modal" data-bs-target="#<?= $modal_time ?>">
						<i class="fas fa-clock"></i> Time
					</a>
				</li>
				<?php endif; ?>
				<?php if (!empty($extra_items) && is_array($extra_items)): ?>
					<?php foreach ($extra_items as $item): ?>
					<li class="nav-item">
						<a class="nav-link-sekretaris<?= !empty($item['cursor']) ? ' cursor-pointer' : '' ?>"
							<?= !empty($item['onclick']) ? 'onclick="' . esc($item['onclick'], 'attr') . '"' : '' ?>
							<?= !empty($item['modal']) ? 'data-bs-toggle="modal" data-bs-target="#' . esc($item['modal']) . '"' : '' ?>
							<?= !empty($item['href']) ? 'href="' . esc($item['href']) . '"' : '' ?>>
							<?php if (!empty($item['icon'])): ?><i class="fas <?= esc($item['icon']) ?>"></i> <?php endif; ?>
							<?= esc($item['label'] ?? '') ?>
						</a>
					</li>
					<?php endforeach; ?>
				<?php endif; ?>
				<?php endif; ?>
			</ul>

			<ul class="navbar-nav navbar-nav-sekretaris ms-auto align-items-lg-center">
				<li class="nav-item d-none d-lg-block">
					<span class="badge-role">
						<i class="fas fa-user-shield"></i> Secretary
					</span>
				</li>
				<li class="nav-item">
					<a class="btn-logout-sekretaris" href="<?= base_url('perangkat-pertandingan/logout') ?>">
						<i class="fas fa-power-off"></i> Logout
					</a>
				</li>
			</ul>
		</div>
	</div>
</nav>
