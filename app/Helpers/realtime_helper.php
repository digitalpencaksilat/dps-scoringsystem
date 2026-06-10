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
