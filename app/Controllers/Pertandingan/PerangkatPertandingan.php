<?php

namespace App\Controllers\Pertandingan;

use App\Controllers\BaseController;
use App\Models\PerangkatPertandinganModel;
use App\Models\PertandinganModel;

/**
 * Controller perangkat pertandingan (landing + auth device).
 *
 * Parity dengan legacy controllers/pertandingan/Perangkat_pertandingan.php:
 * - index(): bila sudah login, redirect ke halaman sesuai posisi; bila belum, tampilkan login.
 * - login(): verifikasi kredensial perangkat (bcrypt), set session, redirect.
 * - logout(): hancurkan session.
 */
class PerangkatPertandingan extends BaseController
{
    protected PerangkatPertandinganModel $perangkatModel;

    public function __construct()
    {
        $this->perangkatModel = new PerangkatPertandinganModel();
    }

    /**
     * Pemetaan posisi -> slug halaman tujuan (parity dengan redirect legacy).
     */
    private function rutePerPosisi(string $posisi): string
    {
        return match ($posisi) {
            'ketua_pertandingan' => '/ketua-pertandingan',
            'juri'               => '/juri',
            'sekretaris'         => '/sekretaris-pertandingan',
            'timer'              => '/sekretaris-pertandingan',
            'layar'              => '/layar',
            'broadcast_operator' => '/broadcast-operator',
            default              => '/perangkat-pertandingan',
        };
    }

    public function index()
    {
        $session = session();

        if ($session->get('level') === 'perangkat_pertandingan') {
            $tujuan = $this->rutePerPosisi((string) $session->get('posisi'));
            if ($tujuan !== '/perangkat-pertandingan') {
                return redirect()->to($tujuan);
            }
        }

        return view('pertandingan/login', [
            'title' => 'Login Perangkat Pertandingan',
        ]);
    }

    public function login()
    {
        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()->to('/perangkat-pertandingan')
                ->with('error', 'Username dan password wajib diisi.');
        }

        $perangkat = $this->perangkatModel->attemptLogin($username, $password);

        if ($perangkat === null) {
            return redirect()->to('/perangkat-pertandingan')
                ->with('error', 'Username atau password salah!');
        }

        session()->set([
            'id_perangkat_pertandingan' => $perangkat->id_perangkat_pertandingan,
            'level'                     => 'perangkat_pertandingan',
            'username'                  => $perangkat->username,
            'posisi'                    => $perangkat->posisi,
            'nama'                      => $perangkat->nama,
            'id_gelanggang'             => $perangkat->id_gelanggang,
            'nama_gelanggang'           => $perangkat->nama_gelanggang ?? null,
            'id_pertandingan'           => null,
        ]);

        return redirect()->to($this->rutePerPosisi($perangkat->posisi));
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/perangkat-pertandingan');
    }

    /**
     * Halaman standby umum (dipakai sebelum partai dimulai).
     * Bisa dipanggil oleh berbagai posisi.
     */
    public function standby()
    {
        $session       = session();
        $idGelanggang  = (int) $session->get('id_gelanggang');
        $pertandingan  = null;

        if ($idGelanggang > 0) {
            $pertandingan = (new PertandinganModel())->getPertandinganBerlangsung($idGelanggang);
        }

        return view('pertandingan/standby', [
            'title'        => 'Menunggu Pertandingan',
            'posisi'       => $session->get('posisi'),
            'nama'         => $session->get('nama'),
            'pertandingan' => $pertandingan,
        ]);
    }

    /**
     * Endpoint polling status pertandingan (dipakai halaman standby).
     * Versi ringan Fase 1: hanya menandai perlu reload bila ada partai
     * berlangsung di gelanggang. Logika per-posisi yang lebih kaya
     * (parity penuh dengan refresh_status_pertandingan legacy) disusun
     * di fase masing-masing role, dan polling ini akan digantikan push
     * event Socket.IO di Fase 8.
     */
    public function refreshStatus()
    {
        $session = session();

        if ($session->get('level') !== 'perangkat_pertandingan') {
            return $this->response->setStatusCode(403)->setJSON(['status' => false]);
        }

        $idGelanggang = (int) $session->get('id_gelanggang');
        $pertandingan = $idGelanggang > 0
            ? (new PertandinganModel())->getPertandinganBerlangsung($idGelanggang)
            : null;

        return $this->response->setJSON([
            'status' => true,
            'reload' => $pertandingan !== null,
        ]);
    }
}
