<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model perangkat_pertandingan (device penilaian).
 *
 * Skema legacy db_sudinpora — TIDAK ada kolom created_at/updated_at,
 * jadi useTimestamps = false (wajib, lihat AGENTS.md).
 *
 * Auth perangkat berbasis tabel ini (bcrypt password), bukan tabel admin/user utama.
 */
class PerangkatPertandinganModel extends Model
{
    protected $table            = 'perangkat_pertandingan';
    protected $primaryKey       = 'id_perangkat_pertandingan';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'id_gelanggang',
        'nama',
        'username',
        'password',
        'posisi',
        'session_id',
    ];

    /**
     * Ambil perangkat berikut data gelanggang berdasarkan username.
     * Mengembalikan satu baris object atau null.
     */
    public function findByUsername(string $username): ?object
    {
        return $this->select('perangkat_pertandingan.*, gelanggang.nama_gelanggang, gelanggang.nomor_gelanggang')
            ->join('gelanggang', 'gelanggang.id_gelanggang = perangkat_pertandingan.id_gelanggang', 'left')
            ->where('perangkat_pertandingan.username', $username)
            ->first();
    }

    /**
     * Verifikasi kredensial perangkat.
     * Parity dengan legacy Perangkat_pertandingan_model::login()/cek_password()
     * (password_verify terhadap hash bcrypt di kolom password).
     *
     * @return object|null data perangkat+gelanggang bila valid, null bila gagal
     */
    public function attemptLogin(string $username, string $password): ?object
    {
        $perangkat = $this->findByUsername($username);

        if ($perangkat === null || empty($perangkat->password)) {
            return null;
        }

        if (! password_verify($password, $perangkat->password)) {
            return null;
        }

        return $perangkat;
    }
}
