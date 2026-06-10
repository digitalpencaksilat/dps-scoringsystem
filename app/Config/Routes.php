<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

/*
 * ------------------------------------------------------------------
 * MODUL PENILAIAN / PERANGKAT PERTANDINGAN (PERSILAT)
 * ------------------------------------------------------------------
 * Slug parity dengan legacy config/routes/pertandingan.php.
 * Controller berada di namespace App\Controllers\Pertandingan.
 */

// --- Perangkat pertandingan (login/landing/standby) ---
$routes->get('perangkat-pertandingan', 'Pertandingan\PerangkatPertandingan::index');
$routes->post('perangkat-pertandingan/login', 'Pertandingan\PerangkatPertandingan::login');
$routes->get('perangkat-pertandingan/logout', 'Pertandingan\PerangkatPertandingan::logout');

$routes->group('perangkat-pertandingan', ['filter' => 'perangkat'], static function ($routes) {
    $routes->get('standby', 'Pertandingan\PerangkatPertandingan::standby');
    $routes->post('refresh-status', 'Pertandingan\PerangkatPertandingan::refreshStatus');
});

/*
 * Slug per posisi. Controller asli diaktifkan per fase.
 */

// --- JURI (PERSILAT tanding + seni) ---
$routes->group('juri', ['filter' => 'perangkat:juri'], static function ($routes) {
    // Tanding
    $routes->get('', 'Pertandingan\\Juri::tanding');
    $routes->get('tanding', 'Pertandingan\\Juri::tanding');
    $routes->get('tanding/(:segment)', 'Pertandingan\\Juri::tanding/$1');
    $routes->post('edit-penilaian-tanding/(:num)', 'Pertandingan\\Juri::editPenilaianTanding/$1');
    $routes->post('refresh-status-pertandingan/(:num)', 'Pertandingan\\Juri::refreshStatusPertandingan/$1');
    $routes->post('refresh-status-pertandingan', 'Pertandingan\\Juri::refreshStatusPertandingan');
    $routes->post('submit-jawaban-verifikasi/(:num)', 'Pertandingan\\Juri::submitJawabanVerifikasi/$1');

    // Seni
    $routes->get('seni', 'Pertandingan\\Juri::seni');
    $routes->get('seni/(:segment)', 'Pertandingan\\Juri::seni/$1');
    $routes->post('edit-penilaian-seni/(:num)', 'Pertandingan\\Juri::editPenilaianSeni/$1');
    $routes->post('refresh-status-seni/(:num)', 'Pertandingan\\Juri::refreshStatusSeni/$1');
    $routes->post('refresh-status-seni', 'Pertandingan\\Juri::refreshStatusSeni');
    $routes->post('toggle-ready-seni/(:num)', 'Pertandingan\\Juri::toggleReadySeni/$1');
});

$routes->get('ketua-pertandingan', 'Pertandingan\PerangkatPertandingan::standby', ['filter' => 'perangkat:ketua_pertandingan']);
$routes->group('ketua-pertandingan', ['filter' => 'perangkat:ketua_pertandingan'], static function ($routes) {
    $routes->get('tanding', 'Pertandingan\KetuaPertandingan::tanding');
    $routes->get('tanding/(:segment)', 'Pertandingan\KetuaPertandingan::tanding/$1');
    $routes->post('edit-penilaian-tanding/(:num)', 'Pertandingan\KetuaPertandingan::editPenilaianTanding/$1');
    $routes->post('refresh-status-pertandingan/(:num)', 'Pertandingan\KetuaPertandingan::refreshStatusPertandingan/$1');
    $routes->post('refresh-status-pertandingan', 'Pertandingan\KetuaPertandingan::refreshStatusPertandingan');
});
$routes->get('sekretaris-pertandingan', 'Pertandingan\SekretarisPertandingan::index', ['filter' => 'perangkat:sekretaris,timer']);
$routes->group('sekretaris-pertandingan', ['filter' => 'perangkat:sekretaris,timer'], static function ($routes) {
    $routes->get('home', 'Pertandingan\SekretarisPertandingan::home');
    $routes->get('jadwal-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::jadwalTanding/$1');
    $routes->get('jadwal-seni/(:num)', 'Pertandingan\SekretarisPertandingan::jadwalSeni/$1');
    $routes->get('mulai-penampilan/(:num)', 'Pertandingan\SekretarisPertandingan::mulaiPenampilan/$1');
    $routes->get('timer-tanding', 'Pertandingan\SekretarisPertandingan::timerTanding');
    $routes->get('timer-seni', 'Pertandingan\SekretarisPertandingan::timerSeni');
    $routes->get('mulai-pertandingan/(:num)', 'Pertandingan\SekretarisPertandingan::mulaiPertandingan/$1');
    $routes->get('pindah-partai-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::pindahPartaiTanding/$1');
    $routes->post('ubah-waktu-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::ubahWaktuTanding/$1');
    $routes->post('toggle-timer-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::toggleTimerTanding/$1');
    $routes->post('pindah-ronde-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::pindahRondeTanding/$1');
    $routes->post('selesaikan-pertandingan/(:num)', 'Pertandingan\SekretarisPertandingan::selesaikanPertandingan/$1');
    $routes->post('refresh-status-pertandingan/(:num)', 'Pertandingan\SekretarisPertandingan::refreshStatusPertandingan/$1');
    $routes->post('refresh-status-pertandingan', 'Pertandingan\SekretarisPertandingan::refreshStatusPertandingan');
    $routes->post('refresh-status-seni', 'Pertandingan\SekretarisPertandingan::refreshStatusSeni');
    $routes->post('toggle-timer-seni/(:num)', 'Pertandingan\SekretarisPertandingan::toggleTimerSeni/$1');
    $routes->post('timer-reset-seni/(:num)', 'Pertandingan\SekretarisPertandingan::timerResetSeni/$1');
    $routes->post('selesaikan-penampilan-seni/(:num)', 'Pertandingan\SekretarisPertandingan::selesaikanPenampilanSeni/$1');
    $routes->post('pilih-pemenang-battle-seni/(:num)', 'Pertandingan\SekretarisPertandingan::pilihPemenangBattleSeni/$1');
    $routes->get('pindah-partai-seni/(:num)', 'Pertandingan\SekretarisPertandingan::pindahPartaiSeni/$1');
    $routes->post('diskualifikasi-penampilan-seni/(:num)', 'Pertandingan\SekretarisPertandingan::diskualifikasiPenampilanSeni/$1');
    $routes->post('batalkan-diskualifikasi-seni/(:num)', 'Pertandingan\SekretarisPertandingan::batalkanDiskualifikasiSeni/$1');
    $routes->post('input-manual-juara-seni', 'Pertandingan\SekretarisPertandingan::inputManualJuaraSeni');
    $routes->get('get-data-penentuan-juara/(:num)', 'Pertandingan\SekretarisPertandingan::getDataPenentuanJuara/$1');
    $routes->post('ganti-format-penilaian-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::gantiFormatPenilaianTanding/$1');
    $routes->get('form-edit-atlet-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::formEditAtletTanding/$1');
    $routes->post('edit-atlet-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::editAtletTanding/$1');
    $routes->get('info-penimbangan/(:num)', 'Pertandingan\SekretarisPertandingan::infoPenimbangan/$1');
    $routes->post('ganti-format-penilaian-seni/(:num)', 'Pertandingan\SekretarisPertandingan::gantiFormatPenilaianSeni/$1');
    $routes->get('print-seni-pool/(:num)', 'Pertandingan\SekretarisPertandingan::printSeniPool/$1');
});
$routes->get('layar', 'Pertandingan\Layar::index', ['filter' => 'perangkat:layar']);
$routes->group('layar', ['filter' => 'perangkat:layar'], static function ($routes) {
    $routes->get('tanding', 'Pertandingan\Layar::tanding');
    $routes->get('tanding/(:segment)', 'Pertandingan\Layar::tanding/$1');
    $routes->post('refresh-status-pertandingan/(:num)', 'Pertandingan\Layar::refreshStatusPertandingan/$1');
    $routes->post('refresh-status-pertandingan', 'Pertandingan\Layar::refreshStatusPertandingan');
});
$routes->get('broadcast-operator', 'Pertandingan\BroadcastOperator::index', ['filter' => 'perangkat:broadcast_operator']);
$routes->group('broadcast-operator', ['filter' => 'perangkat:broadcast_operator'], static function ($routes) {
    $routes->get('tanding', 'Pertandingan\BroadcastOperator::tanding');
    $routes->post('set-scene', 'Pertandingan\BroadcastOperator::setScene');
});

// Overlay OBS + state grafis: PUBLIK (tanpa auth perangkat) agar bisa dipakai
// sebagai Browser Source. Berbasis id_gelanggang pada URL.
$routes->get('broadcast-operator/overlay/(:num)', 'Pertandingan\BroadcastOperator::overlay/$1');
$routes->get('broadcast-operator/refresh-graphic/(:num)', 'Pertandingan\BroadcastOperator::refreshBroadcastGraphic/$1');
