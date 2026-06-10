<?php

/**
 * Helper untuk emit event ke DPS Scoring Realtime Server (Socket.IO)
 * dari sisi PHP (controller scoring). Parity legacy websocket_helper.php.
 *
 * Karena Socket.IO server di port 3000 dan PHP tidak punya native socket.io client,
 * kita gunakan pendekatan HTTP POST ke endpoint internal yang relay ke socket.io.
 * Alternatif: direct socket emit via curl ke Engine.IO transport — terlalu fragile.
 *
 * Solusi yang lebih robust: endpoint /emit di realtime-server yang menerima JSON
 * dan broadcast ke room. Ini sudah umum dipakai di arsitektur PHP + Socket.IO.
 */

if (! function_exists('realtime_emit')) {
    /**
     * Emit event ke realtime server via HTTP internal endpoint.
     *
     * @param string $event   Nama event (KONTROL_SKOR, KONTROL_WAKTU, RESET_ROOM)
     * @param array  $payload Data event (harus berisi id_pertandingan)
     * @return bool  True jika berhasil dikirim
     */
    function realtime_emit(string $event, array $payload): bool
    {
        $rtHost = env('RT_HOST', 'http://localhost:3000');
        $url    = rtrim($rtHost, '/') . '/emit';

        $data = json_encode([
            'event'   => $event,
            'payload' => $payload,
        ]);

        // Non-blocking: timeout pendek (2 detik) agar tidak menahan response scoring.
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'X-Internal: 1'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 2,
            CURLOPT_CONNECTTIMEOUT => 1,
        ]);
        $result = curl_exec($ch);
        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            return true;
        }

        // Gagal emit tidak boleh menggagalkan scoring (fire-and-forget).
        log_message('warning', "[realtime_emit] Gagal emit {$event}: HTTP {$code}, resp: {$result}");
        return false;
    }
}

if (! function_exists('realtime_emit_skor')) {
    /**
     * Shortcut emit UPDATE_SKOR setelah recalc.
     */
    function realtime_emit_skor(int $idPertandingan, int $skorMerah, int $skorBiru, string $ronde, int $seq = 0): bool
    {
        return realtime_emit('KONTROL_SKOR', [
            'id_pertandingan' => $idPertandingan,
            'skor_merah'      => $skorMerah,
            'skor_biru'       => $skorBiru,
            'ronde'           => $ronde,
            'seq'             => $seq ?: (int) (microtime(true) * 1000),
        ]);
    }
}

if (! function_exists('realtime_emit_waktu')) {
    /**
     * Shortcut emit KONTROL_WAKTU dari sekretaris.
     */
    function realtime_emit_waktu(int $idPertandingan, string $status, $dataWaktu = null, int $seq = 0): bool
    {
        return realtime_emit('KONTROL_WAKTU', [
            'id_pertandingan'     => $idPertandingan,
            'status_pertandingan' => $status,
            'data_waktu'          => $dataWaktu,
            'seq'                 => $seq ?: (int) (microtime(true) * 1000),
        ]);
    }
}

if (! function_exists('realtime_reset_room')) {
    /**
     * Reset room saat partai selesai (bersihkan snapshot di server).
     */
    function realtime_reset_room(int $idPertandingan): bool
    {
        return realtime_emit('RESET_ROOM', [
            'id_pertandingan' => $idPertandingan,
        ]);
    }
}

// ==========================================================================
// Event relay functions (non-stateful, fire-and-forget broadcast ke room)
// ==========================================================================

if (! function_exists('realtime_emit_nilai_update')) {
    /**
     * Broadcast perubahan nilai tanding ke Juri/KP/Layar.
     * Dipanggil setelah Juri/KP edit penilaian tanding.
     */
    function realtime_emit_nilai_update(int $idPertandingan, array $data = []): bool
    {
        return realtime_emit('NILAI_UPDATE', array_merge([
            'id_pertandingan' => $idPertandingan,
        ], $data));
    }
}

if (! function_exists('realtime_emit_verifikasi_jatuhan')) {
    /**
     * Broadcast permintaan verifikasi jatuhan ke semua Juri.
     */
    function realtime_emit_verifikasi_jatuhan(int $idPertandingan, array $data = []): bool
    {
        return realtime_emit('VERIFIKASI_JATUHAN', array_merge([
            'id_pertandingan' => $idPertandingan,
        ], $data));
    }
}

if (! function_exists('realtime_emit_verifikasi_pelanggaran')) {
    /**
     * Broadcast permintaan verifikasi pelanggaran ke semua Juri.
     */
    function realtime_emit_verifikasi_pelanggaran(int $idPertandingan, array $data = []): bool
    {
        return realtime_emit('VERIFIKASI_PELANGGARAN', array_merge([
            'id_pertandingan' => $idPertandingan,
        ], $data));
    }
}

if (! function_exists('realtime_emit_match_status_change')) {
    /**
     * Broadcast perubahan status pertandingan (berlangsung, selesai, verifikasi, dll).
     */
    function realtime_emit_match_status_change(int $idPertandingan, string $status, array $data = []): bool
    {
        return realtime_emit('MATCH_STATUS_CHANGE', array_merge([
            'id_pertandingan'     => $idPertandingan,
            'status_pertandingan' => $status,
        ], $data));
    }
}

if (! function_exists('realtime_emit_pertandingan_selesai')) {
    /**
     * Broadcast pertandingan selesai ke Layar (trigger redirect ke hasil).
     */
    function realtime_emit_pertandingan_selesai(int $idPertandingan, array $data = []): bool
    {
        return realtime_emit('PERTANDINGAN_SELESAI', array_merge([
            'id_pertandingan' => $idPertandingan,
        ], $data));
    }
}

if (! function_exists('realtime_emit_tanding_berlangsung')) {
    /**
     * Broadcast ke gelanggang bahwa pertandingan tanding sedang berlangsung.
     * Layar home/standby akan redirect ke layar/tanding.
     */
    function realtime_emit_tanding_berlangsung(int $idGelanggang, int $idPertandingan): bool
    {
        return realtime_emit('TANDING_BERLANGSUNG', [
            'id_gelanggang'   => $idGelanggang,
            'id_pertandingan' => $idPertandingan,
        ]);
    }
}

if (! function_exists('realtime_emit_seni_berlangsung')) {
    /**
     * Broadcast ke gelanggang bahwa penampilan seni sedang berlangsung.
     * Layar home/standby akan redirect ke layar/seni.
     */
    function realtime_emit_seni_berlangsung(int $idGelanggang, int $idPenampilanSeni): bool
    {
        return realtime_emit('SENI_BERLANGSUNG', [
            'id_gelanggang'      => $idGelanggang,
            'id_penampilan_seni' => $idPenampilanSeni,
        ]);
    }
}

// ==========================================================================
// Seni-specific relay events
// ==========================================================================

if (! function_exists('realtime_emit_akses_penilaian')) {
    /**
     * Broadcast toggle akses penilaian seni ke Juri/Layar.
     * Dipanggil oleh KP saat buka/tutup akses.
     */
    function realtime_emit_akses_penilaian(int $idPenampilanSeni, string $akses): bool
    {
        return realtime_emit('AKSES_PENILAIAN', [
            'id_penampilan_seni' => $idPenampilanSeni,
            'akses_penilaian'    => $akses,
        ]);
    }
}

if (! function_exists('realtime_emit_seni_akses_ditutup')) {
    /**
     * Broadcast bahwa akses penilaian seni ditutup (specific event for Layar).
     */
    function realtime_emit_seni_akses_ditutup(int $idPenampilanSeni): bool
    {
        return realtime_emit('SENI_AKSES_DITUTUP', [
            'id_penampilan_seni' => $idPenampilanSeni,
        ]);
    }
}

if (! function_exists('realtime_emit_hukuman_update')) {
    /**
     * Broadcast perubahan hukuman seni ke Juri (agar display hukuman ter-update).
     */
    function realtime_emit_hukuman_update(int $idPenampilanSeni, array $data = []): bool
    {
        return realtime_emit('HUKUMAN_UPDATE', array_merge([
            'id_penampilan_seni' => $idPenampilanSeni,
        ], $data));
    }
}

if (! function_exists('realtime_emit_juri_ready_update')) {
    /**
     * Broadcast juri ready status ke KP.
     */
    function realtime_emit_juri_ready_update(int $idPenampilanSeni, array $data = []): bool
    {
        return realtime_emit('JURI_READY_UPDATE', array_merge([
            'id_penampilan_seni' => $idPenampilanSeni,
        ], $data));
    }
}

if (! function_exists('realtime_emit_update_nilai_seni')) {
    /**
     * Broadcast perubahan nilai seni ke Layar (live score display).
     */
    function realtime_emit_update_nilai_seni(int $idPenampilanSeni, array $data = []): bool
    {
        return realtime_emit('UPDATE_NILAI_SENI', array_merge([
            'id_penampilan_seni' => $idPenampilanSeni,
        ], $data));
    }
}

if (! function_exists('realtime_emit_penampilan_selesai')) {
    /**
     * Broadcast penampilan seni selesai ke Juri/KP/Layar.
     */
    function realtime_emit_penampilan_selesai(int $idPenampilanSeni, array $data = []): bool
    {
        return realtime_emit('PENAMPILAN_SELESAI', array_merge([
            'id_penampilan_seni' => $idPenampilanSeni,
        ], $data));
    }
}

if (! function_exists('realtime_emit_seni_selesai')) {
    /**
     * Broadcast seni selesai ke Layar (trigger redirect ke hasil).
     */
    function realtime_emit_seni_selesai(int $idPenampilanSeni, array $data = []): bool
    {
        return realtime_emit('SENI_SELESAI', array_merge([
            'id_penampilan_seni' => $idPenampilanSeni,
        ], $data));
    }
}

if (! function_exists('realtime_emit_kontrol_waktu_seni')) {
    /**
     * Broadcast kontrol waktu seni (timer start/stop/reset).
     */
    function realtime_emit_kontrol_waktu_seni(int $idPenampilanSeni, string $statusPenampilan, $waktuTampil = null): bool
    {
        return realtime_emit('KONTROL_WAKTU_SENI', [
            'id_penampilan_seni' => $idPenampilanSeni,
            'status_penampilan'  => $statusPenampilan,
            'waktu_tampil'       => $waktuTampil,
        ]);
    }
}
