<?php

/**
 * Timer Helper — Server-Authoritative Timer State.
 *
 * Mengatasi masalah klasik polling-based timer sync:
 *  - Tanpa drift compensation, polling 1-3s membuat timer "stutter" / mundur
 *  - Multi-client (layar, juri, KP, sekretaris) tidak akan sinkron
 *
 * Solusi: server adalah single source of truth. Saat sekretaris start/pause,
 * server simpan:
 *   - state              ('running' | 'paused')
 *   - sisa_waktu_at_save (sisa detik snapshot waktu di-save)
 *   - started_at_ms      (Unix epoch ms saat START ditekan, NULL kalau paused)
 *   - server_now_ms      (Unix epoch ms server "sekarang", utk client compensate)
 *   - ronde, sisa_waktu (legacy fields, tetap di-include untuk back-compat)
 *
 * Client menghitung sendiri waktu saat ini:
 *   if (state === 'running') {
 *       elapsed_ms = (server_now_ms - started_at_ms) + (Date.now() - response_arrival_ms);
 *       sisa = sisa_waktu_at_save - elapsed_ms / 1000;
 *   } else {
 *       sisa = sisa_waktu_at_save;
 *   }
 *
 * Hasilnya: timer smooth + sinkron lintas client walau hanya pakai polling.
 */

if (! function_exists('build_data_waktu_state')) {
    /**
     * Build data_waktu JSON dengan field server-authoritative untuk drift compensation.
     *
     * @param string $status    Status pertandingan ('berlangsung'|'berhenti'|'istirahat'|'standby'|'selesai')
     * @param int    $sisaWaktu Sisa detik saat ini (countdown).
     * @param int    $ronde     Ronde aktif (1-3).
     * @param mixed  $extra     Optional: existing data_waktu fields untuk preserve (array/object).
     *
     * @return array Enriched data_waktu siap di-encode JSON.
     */
    function build_data_waktu_state(string $status, int $sisaWaktu, int $ronde, $extra = null): array
    {
        $isRunning = ($status === 'berlangsung');
        $serverNowMs = (int) round(microtime(true) * 1000);

        $state = [
            'state'              => $isRunning ? 'running' : 'paused',
            'sisa_waktu_at_save' => max(0, $sisaWaktu),
            'started_at_ms'      => $isRunning ? $serverNowMs : null,
            'server_now_ms'      => $serverNowMs,
            // Legacy back-compat — masih dipakai render server-side awal page load.
            'sisa_waktu'         => max(0, $sisaWaktu),
            'ronde'              => $ronde,
        ];

        // Preserve any extra fields client kirim (e.g. ronde data per-round)
        if ($extra !== null) {
            $extraArr = is_object($extra) ? (array) $extra : (is_array($extra) ? $extra : []);
            // Don't overwrite our authoritative fields
            foreach (['state', 'sisa_waktu_at_save', 'started_at_ms', 'server_now_ms'] as $reserved) {
                unset($extraArr[$reserved]);
            }
            // Tetap merge sisa fields (mis. data per-ronde, custom flags)
            $state = array_merge($extraArr, $state);
        }

        return $state;
    }
}

if (! function_exists('compute_current_sisa_waktu')) {
    /**
     * Hitung sisa waktu sebenarnya berdasarkan state yang tersimpan + waktu sekarang.
     *
     * Dipakai server-side ketika kita perlu render "sisa waktu sekarang" di response
     * tanpa nunggu client kalkulasi (mis. response refreshStatusPertandingan).
     *
     * @param array|object $dataWaktu data_waktu yang tersimpan di DB (sudah di-decode).
     *
     * @return int Sisa detik saat ini, ter-compensate dengan elapsed time jika running.
     */
    function compute_current_sisa_waktu($dataWaktu): int
    {
        $arr = is_object($dataWaktu) ? (array) $dataWaktu : (is_array($dataWaktu) ? $dataWaktu : []);

        $sisaAtSave  = isset($arr['sisa_waktu_at_save']) ? (int) $arr['sisa_waktu_at_save'] : (int) ($arr['sisa_waktu'] ?? 0);
        $state       = $arr['state'] ?? 'paused';
        $startedAtMs = isset($arr['started_at_ms']) ? (int) $arr['started_at_ms'] : 0;

        if ($state !== 'running' || $startedAtMs <= 0) {
            return max(0, $sisaAtSave);
        }

        $nowMs = (int) round(microtime(true) * 1000);
        $elapsedSeconds = (int) floor(($nowMs - $startedAtMs) / 1000);
        return max(0, $sisaAtSave - $elapsedSeconds);
    }
}

if (! function_exists('inject_server_now_ms')) {
    /**
     * Inject server_now_ms ke data_waktu yang tersimpan, supaya client bisa
     * compute clock offset terhadap response time (network jitter compensation).
     *
     * @param array|object|null $dataWaktu data_waktu dari DB (decoded JSON).
     *
     * @return array|null Enriched data_waktu, atau null jika input null.
     */
    function inject_server_now_ms($dataWaktu): ?array
    {
        if ($dataWaktu === null) {
            return null;
        }
        $arr = is_object($dataWaktu) ? (array) $dataWaktu : (array) $dataWaktu;
        $arr['server_now_ms'] = (int) round(microtime(true) * 1000);
        return $arr;
    }
}
