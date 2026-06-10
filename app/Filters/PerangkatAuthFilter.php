<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Filter auth perangkat pertandingan.
 *
 * Memastikan sesi memiliki level 'perangkat_pertandingan'. Bila argumen posisi
 * diberikan (mis. 'juri', 'ketua_pertandingan'), sesi juga harus cocok posisinya.
 *
 * Parity dengan legacy: setiap controller pertandingan mengecek
 * session->userdata('level') == 'perangkat_pertandingan' dan posisi tertentu,
 * lalu redirect('perangkat-pertandingan') bila gagal.
 *
 * Penggunaan di Routes:
 *   ['filter' => 'perangkat']                 // sekadar harus login perangkat
 *   ['filter' => 'perangkat:juri']            // harus login & posisi juri
 *   ['filter' => 'perangkat:sekretaris,timer']// salah satu posisi diperbolehkan
 */
class PerangkatAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if ($session->get('level') !== 'perangkat_pertandingan') {
            return redirect()->to('/perangkat-pertandingan');
        }

        // Bila daftar posisi yang diizinkan diberikan, validasi.
        if (! empty($arguments)) {
            $posisi = (string) $session->get('posisi');
            if (! in_array($posisi, $arguments, true)) {
                return redirect()->to('/perangkat-pertandingan');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
