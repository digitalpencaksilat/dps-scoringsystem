<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Pertandingan\PerangkatPertandingan::index');

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
    $routes->get('', 'Pertandingan\\Juri::index');
    $routes->get('home', 'Pertandingan\\Juri::index');
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

$routes->get('ketua-pertandingan', 'Pertandingan\KetuaPertandingan::index', ['filter' => 'perangkat:ketua_pertandingan']);
$routes->group('ketua-pertandingan', ['filter' => 'perangkat:ketua_pertandingan'], static function ($routes) {
    $routes->get('home', 'Pertandingan\KetuaPertandingan::index');

    // Tanding
    $routes->get('tanding/monitoring', 'Pertandingan\KetuaPertandingan::monitoringTanding');
    $routes->get('tanding/monitoring/(:segment)', 'Pertandingan\KetuaPertandingan::monitoringTanding/$1');
    $routes->get('tanding/dewan', 'Pertandingan\KetuaPertandingan::dewanTanding');
    $routes->get('tanding/dewan/(:segment)', 'Pertandingan\KetuaPertandingan::dewanTanding/$1');
    $routes->get('tanding', 'Pertandingan\KetuaPertandingan::tanding');
    $routes->get('tanding/(:segment)', 'Pertandingan\KetuaPertandingan::tanding/$1');
    $routes->post('edit-penilaian-tanding/(:num)', 'Pertandingan\KetuaPertandingan::editPenilaianTanding/$1');
    $routes->post('refresh-status-pertandingan/(:num)', 'Pertandingan\KetuaPertandingan::refreshStatusPertandingan/$1');
    $routes->post('refresh-status-pertandingan', 'Pertandingan\KetuaPertandingan::refreshStatusPertandingan');

    // Seni
    $routes->get('seni', 'Pertandingan\KetuaPertandingan::seni');
    $routes->get('seni/(:segment)', 'Pertandingan\KetuaPertandingan::seni/$1');
    $routes->get('dewan-seni', 'Pertandingan\KetuaPertandingan::dewanSeni');
    $routes->get('dewan-seni/(:segment)', 'Pertandingan\KetuaPertandingan::dewanSeni/$1');
    $routes->post('edit-penilaian-seni/(:num)', 'Pertandingan\KetuaPertandingan::editPenilaianSeni/$1');
    $routes->post('refresh-status-seni/(:num)', 'Pertandingan\KetuaPertandingan::refreshStatusSeni/$1');
    $routes->post('refresh-status-seni', 'Pertandingan\KetuaPertandingan::refreshStatusSeni');
    $routes->post('ganti-akses-penilaian/(:num)', 'Pertandingan\KetuaPertandingan::gantiAksesPenilaian/$1');
    $routes->post('diskualifikasi-seni/(:num)', 'Pertandingan\KetuaPertandingan::diskualifikasiPenampilanSeni/$1');
    $routes->post('batalkan-diskualifikasi-seni/(:num)', 'Pertandingan\KetuaPertandingan::batalkanDiskualifikasi/$1');

    // Daftar Nilai
    $routes->get('daftar-nilai-tanding', 'Pertandingan\\KetuaPertandingan::daftarNilaiTanding');
    $routes->get('daftar-nilai-seni', 'Pertandingan\\KetuaPertandingan::daftarNilaiSeni');

    // Verifikasi Pertandingan (jatuhan / pelanggaran)
    $routes->post('verifikasi-pertandingan/create/(:num)', 'Pertandingan\\KetuaPertandingan::createVerifikasi/$1');
    $routes->post('verifikasi-pertandingan/update/(:num)', 'Pertandingan\\KetuaPertandingan::updateVerifikasi/$1');
    $routes->get('verifikasi-pertandingan/get-jawaban/(:num)', 'Pertandingan\\KetuaPertandingan::getJawabanVerifikasi/$1');
});
$routes->get('sekretaris-pertandingan', 'Pertandingan\SekretarisPertandingan::index', ['filter' => 'perangkat:sekretaris,timer']);
$routes->group('sekretaris-pertandingan', ['filter' => 'perangkat:sekretaris,timer'], static function ($routes) {
    $routes->get('home', 'Pertandingan\SekretarisPertandingan::home');
    $routes->get('jadwal-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::jadwalTanding/$1');
    $routes->get('jadwal-seni/(:num)', 'Pertandingan\SekretarisPertandingan::jadwalSeni/$1');
    $routes->get('mulai-penampilan/(:num)', 'Pertandingan\SekretarisPertandingan::mulaiPenampilan/$1');
    $routes->get('mulai-ulang-penampilan/(:num)', 'Pertandingan\SekretarisPertandingan::mulaiUlangPenampilan/$1');
    $routes->get('timer-tanding', 'Pertandingan\SekretarisPertandingan::timerTanding');
    $routes->get('timer-seni', 'Pertandingan\SekretarisPertandingan::timerSeni');
    $routes->get('mulai-pertandingan/(:num)', 'Pertandingan\SekretarisPertandingan::mulaiPertandingan/$1');
    $routes->post('pindah-partai-tanding/(:num)', 'Pertandingan\SekretarisPertandingan::pindahPartaiTanding/$1');
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
    $routes->post('pindah-partai-seni', 'Pertandingan\SekretarisPertandingan::pindahPartaiSeni');
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
    $routes->get('home', 'Pertandingan\Layar::index');
    $routes->get('standby', 'Pertandingan\Layar::standby');

    // Tanding
    $routes->get('tanding', 'Pertandingan\Layar::tanding');
    $routes->get('tanding/(:segment)', 'Pertandingan\Layar::tanding/$1');
    $routes->post('refresh-status-pertandingan/(:num)', 'Pertandingan\Layar::refreshStatusPertandingan/$1');
    $routes->post('refresh-status-pertandingan', 'Pertandingan\Layar::refreshStatusPertandingan');

    // Tanding extras
    $routes->get('transisi', 'Pertandingan\Layar::transisi');
    $routes->get('transisi/(:segment)', 'Pertandingan\Layar::transisi/$1');
    $routes->get('hasil-tanding/(:num)', 'Pertandingan\Layar::hasilTanding/$1');
    $routes->get('statistik-tanding/(:num)', 'Pertandingan\Layar::statistikTanding/$1');

    // Seni
    $routes->get('seni', 'Pertandingan\Layar::seni');
    $routes->get('seni/(:segment)', 'Pertandingan\Layar::seni/$1');
    $routes->post('refresh-status-seni/(:num)', 'Pertandingan\Layar::refreshStatusSeni/$1');
    $routes->post('refresh-status-seni', 'Pertandingan\Layar::refreshStatusSeni');
    $routes->get('hasil-pool-seni/(:num)', 'Pertandingan\Layar::hasilPoolSeni/$1');
    $routes->get('hasil-battle-seni/(:num)', 'Pertandingan\Layar::hasilBattleSeni/$1');
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