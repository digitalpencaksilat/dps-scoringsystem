/**
 * DPS Scoring Realtime Server (PERSILAT) — Socket.IO
 *
 * Room model:
 *  - Tanding: room = id_pertandingan (stateful, snapshot for recovery)
 *  - Seni: room = "seni_{id_penampilan_seni}" (stateful for nilai)
 *  - Gelanggang: room = "gelanggang_{id}" (for BERLANGSUNG notifications)
 *
 * Stateful events (snapshot + seq):
 *  - KONTROL_WAKTU → UPDATE_WAKTU
 *  - KONTROL_SKOR → UPDATE_SKOR
 *  - RESET_ROOM → ROOM_RESET
 *
 * Relay events (no snapshot, fire-and-forget broadcast):
 *  - NILAI_UPDATE, VERIFIKASI_JATUHAN, VERIFIKASI_PELANGGARAN,
 *    MATCH_STATUS_CHANGE, HUKUMAN_UPDATE, AKSES_PENILAIAN,
 *    PENAMPILAN_SELESAI, JURI_READY_UPDATE, UPDATE_NILAI_SENI,
 *    SENI_AKSES_DITUTUP, SENI_SELESAI, PERTANDINGAN_SELESAI,
 *    TANDING_BERLANGSUNG, SENI_BERLANGSUNG, KONTROL_WAKTU_SENI
 *
 * State authoritative tetap di DB (CI4). Server ini murni transport/relay + cache
 * snapshot terakhir untuk recovery cepat.
 */

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const PORT = process.env.RT_PORT ? parseInt(process.env.RT_PORT, 10) : 3000;
const ORIGIN = process.env.RT_ORIGIN || '*';

const app = express();
app.use(cors({ origin: ORIGIN }));
app.use(express.json());

// --- Stateful events (snapshot per room) ---
const STATEFUL_EVENTS = ['KONTROL_SKOR', 'KONTROL_WAKTU', 'RESET_ROOM'];

// --- Relay events (broadcast tanpa snapshot) ---
const RELAY_EVENTS = [
    'NILAI_UPDATE',
    'VERIFIKASI_JATUHAN',
    'VERIFIKASI_PELANGGARAN',
    'MATCH_STATUS_CHANGE',
    'HUKUMAN_UPDATE',
    'AKSES_PENILAIAN',
    'PENAMPILAN_SELESAI',
    'JURI_READY_UPDATE',
    'UPDATE_NILAI_SENI',
    'SENI_AKSES_DITUTUP',
    'SENI_SELESAI',
    'PERTANDINGAN_SELESAI',
    'TANDING_BERLANGSUNG',
    'SENI_BERLANGSUNG',
    'KONTROL_WAKTU_SENI',
];

app.get('/health', (req, res) => {
    res.json({
        status: 'ok',
        rooms: Object.keys(snapshots).length,
        uptime: process.uptime(),
        connected_clients: io.engine.clientsCount || 0,
    });
});

/**
 * Endpoint internal /emit — dipanggil dari PHP (realtime_helper.php).
 * Body: { event: string, payload: object, room?: string }
 *
 * Room resolution:
 *  - Jika payload.room disediakan, pakai itu langsung.
 *  - Jika payload.id_pertandingan ada, room = String(id_pertandingan)
 *  - Jika payload.id_penampilan_seni ada, room = "seni_{id}"
 *  - Jika payload.id_gelanggang ada, room = "gelanggang_{id}"
 */
app.post('/emit', (req, res) => {
    const { event, payload } = req.body || {};
    if (!event || !payload) {
        return res.status(400).json({ status: false, message: 'event/payload tidak valid' });
    }

    // Resolve room
    const room = resolveRoom(payload);
    if (!room) {
        return res.status(400).json({ status: false, message: 'tidak bisa resolve room dari payload' });
    }

    // --- Stateful events ---
    if (event === 'RESET_ROOM') {
        delete snapshots[room];
        io.to(room).emit('ROOM_RESET', { id_pertandingan: room, ...payload });
        return res.json({ status: true, event: 'ROOM_RESET', room });
    }

    if (event === 'KONTROL_SKOR') {
        const snap = getOrCreateSnapshot(room);
        const seq = resolveSeq(payload, snap);
        if (seq < snap.seq) {
            return res.json({ status: true, dropped: true, reason: 'stale seq' });
        }
        snap.seq = seq;
        snap.skor = { ...payload, seq };
        io.to(room).emit('UPDATE_SKOR', snap.skor);
        return res.json({ status: true, event: 'UPDATE_SKOR', room, seq });
    }

    if (event === 'KONTROL_WAKTU') {
        const snap = getOrCreateSnapshot(room);
        const seq = resolveSeq(payload, snap);
        if (seq < snap.seq) {
            return res.json({ status: true, dropped: true, reason: 'stale seq' });
        }
        snap.seq = seq;
        snap.waktu = { ...payload, seq };
        io.to(room).emit('UPDATE_WAKTU', snap.waktu);
        return res.json({ status: true, event: 'UPDATE_WAKTU', room, seq });
    }

    // --- Relay events (fire-and-forget, no snapshot) ---
    if (RELAY_EVENTS.includes(event)) {
        io.to(room).emit(event, payload);
        return res.json({ status: true, event, room });
    }

    // Unknown event — reject
    return res.status(400).json({ status: false, message: `event tidak dikenal: ${event}` });
});

const server = http.createServer(app);
const io = new Server(server, { cors: { origin: ORIGIN } });

/**
 * snapshots[roomId] = { seq, waktu, skor }
 */
const snapshots = {};

function getOrCreateSnapshot(room) {
    if (!snapshots[room]) {
        snapshots[room] = { seq: 0, waktu: null, skor: null };
    }
    return snapshots[room];
}

function resolveSeq(payload, snap) {
    return (typeof payload.seq === 'number') ? payload.seq : (snap.seq + 1);
}

/**
 * Resolve room ID dari payload.
 * Priority: room > id_pertandingan > id_penampilan_seni > id_gelanggang
 */
function resolveRoom(payload) {
    if (payload.room) return String(payload.room);
    if (payload.id_pertandingan !== undefined) return String(payload.id_pertandingan);
    if (payload.id_penampilan_seni !== undefined) return 'seni_' + String(payload.id_penampilan_seni);
    if (payload.id_gelanggang !== undefined) return 'gelanggang_' + String(payload.id_gelanggang);
    return null;
}

io.on('connection', (socket) => {
    console.log('[connect]', socket.id);

    /**
     * JOIN_ROOM — client joins a room.
     * Accepts: string (id_pertandingan) or object { id_pertandingan, id_penampilan_seni, id_gelanggang }
     */
    socket.on('JOIN_ROOM', (data) => {
        let rooms = [];

        if (typeof data === 'object' && data !== null) {
            // Join multiple rooms if needed
            if (data.id_pertandingan !== undefined) {
                rooms.push(String(data.id_pertandingan));
            }
            if (data.id_penampilan_seni !== undefined) {
                rooms.push('seni_' + String(data.id_penampilan_seni));
            }
            if (data.id_gelanggang !== undefined) {
                rooms.push('gelanggang_' + String(data.id_gelanggang));
            }
            // Fallback: jika object tapi tidak ada key yang dikenal, coba pakai sebagai id
            if (rooms.length === 0 && data.id !== undefined) {
                rooms.push(String(data.id));
            }
        } else {
            // Legacy: terima string/number langsung sebagai id_pertandingan
            rooms.push(String(data));
        }

        rooms.forEach((room) => {
            socket.join(room);
            console.log(`[join] ${socket.id} -> room ${room}`);

            // Recovery: kirim snapshot terakhir (hanya untuk stateful rooms)
            const snap = snapshots[room];
            if (snap) {
                if (snap.waktu) socket.emit('UPDATE_WAKTU', snap.waktu);
                if (snap.skor) socket.emit('UPDATE_SKOR', snap.skor);
                socket.emit('STATE_SYNC', { room, seq: snap.seq, waktu: snap.waktu, skor: snap.skor });
            }
        });
    });

    // Timer dari sekretaris -> broadcast ke seluruh room (kecuali pengirim).
    socket.on('KONTROL_WAKTU', (data) => {
        const room = resolveRoom(data || {});
        if (!room) return;

        const snap = getOrCreateSnapshot(room);
        const seq = resolveSeq(data, snap);

        if (seq < snap.seq) {
            console.log(`[drop waktu] room ${room} seq ${seq} < ${snap.seq}`);
            return;
        }
        snap.seq = seq;
        snap.waktu = { ...data, seq };
        socket.to(room).emit('UPDATE_WAKTU', snap.waktu);
    });

    // Skor dari juri/ketua/sekretaris -> broadcast ke seluruh room.
    socket.on('KONTROL_SKOR', (data) => {
        const room = resolveRoom(data || {});
        if (!room) return;

        const snap = getOrCreateSnapshot(room);
        const seq = resolveSeq(data, snap);

        if (seq < snap.seq) {
            console.log(`[drop skor] room ${room} seq ${seq} < ${snap.seq}`);
            return;
        }
        snap.seq = seq;
        snap.skor = { ...data, seq };
        socket.to(room).emit('UPDATE_SKOR', snap.skor);
    });

    // Reset snapshot ketika partai selesai.
    socket.on('RESET_ROOM', (data) => {
        const id = (typeof data === 'object') ? (data.id_pertandingan || data.id) : data;
        const room = String(id);
        delete snapshots[room];
        io.to(room).emit('ROOM_RESET', { id_pertandingan: room });
    });

    // --- Generic relay: client-to-client event forwarding ---
    // Klien bisa emit event relay langsung (tanpa melalui /emit HTTP).
    // Format: { event, room/id_pertandingan/id_penampilan_seni, ...payload }
    socket.on('RELAY', (data) => {
        if (!data || !data.event) return;
        const room = resolveRoom(data);
        if (!room) return;
        if (!RELAY_EVENTS.includes(data.event)) return;

        socket.to(room).emit(data.event, data);
    });

    // Legacy compat: trigger_refresh events (just relay)
    socket.on('trigger_refresh_tanding', (data) => {
        const room = resolveRoom(data || {});
        if (room) socket.to(room).emit('trigger_refresh_tanding', data);
    });

    socket.on('trigger_refresh_seni', (data) => {
        const room = resolveRoom(data || {});
        if (room) socket.to(room).emit('trigger_refresh_seni', data);
    });

    socket.on('disconnect', () => {
        console.log('[disconnect]', socket.id);
    });
});

server.listen(PORT, () => {
    console.log(`DPS Scoring Realtime Server berjalan di port ${PORT} (origin: ${ORIGIN})`);
});
