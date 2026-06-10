/**
 * DPS Scoring Realtime Server (PERSILAT) — Socket.IO
 *
 * Memperluas kontrak legacy (realtime-server/server.js di htdocs/dps):
 *  - Room per id_pertandingan (parity: JOIN_ROOM).
 *  - Timer: KONTROL_WAKTU (sekretaris) -> UPDATE_WAKTU (broadcast ke room).
 *  - Skor (BARU): KONTROL_SKOR (juri/ketua/sekretaris) -> UPDATE_SKOR (broadcast).
 *  - STATE_SYNC (BARU): server menyimpan snapshot terakhir per room; klien yang
 *    baru join / reconnect langsung menerima state terakhir (recovery).
 *  - Sequence number per room (anti out-of-order): event lama (seq < terakhir) diabaikan.
 *
 * State authoritative tetap di DB (CI4). Server ini murni transport/relay + cache
 * snapshot terakhir untuk recovery cepat. Lihat docs/RENCANA_MIGRASI_PENILAIAN_PERSILAT.md §6.2.
 */

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const PORT = process.env.RT_PORT ? parseInt(process.env.RT_PORT, 10) : 3000;
const ORIGIN = process.env.RT_ORIGIN || '*'; // batasi di produksi (docs §6.3)

const app = express();
app.use(cors({ origin: ORIGIN }));
app.use(express.json());

app.get('/health', (req, res) => {
    res.json({ status: 'ok', rooms: Object.keys(snapshots).length, uptime: process.uptime() });
});

/**
 * Endpoint internal /emit — dipanggil dari PHP (realtime_helper.php) untuk
 * mem-broadcast event ke room tanpa perlu socket.io client di PHP.
 * Body: { event: 'KONTROL_SKOR'|'KONTROL_WAKTU'|'RESET_ROOM', payload: {...} }
 *
 * Catatan keamanan (docs §6.3): di produksi, batasi akses /emit ke localhost
 * (PHP & Node di host yang sama) via firewall / bind 127.0.0.1, atau verifikasi
 * header X-Internal + shared secret.
 */
app.post('/emit', (req, res) => {
    const { event, payload } = req.body || {};
    if (!event || !payload || payload.id_pertandingan === undefined) {
        return res.status(400).json({ status: false, message: 'event/payload tidak valid' });
    }

    const room = getRoom(payload.id_pertandingan);
    const snap = snapshots[room];
    const seq = (typeof payload.seq === 'number') ? payload.seq : (snap.seq + 1);

    if (event === 'RESET_ROOM') {
        delete snapshots[room];
        io.to(room).emit('ROOM_RESET', { id_pertandingan: room });
        return res.json({ status: true, event, room });
    }

    // Anti out-of-order untuk event ber-state.
    if (seq < snap.seq) {
        return res.json({ status: true, dropped: true, reason: 'stale seq' });
    }
    snap.seq = seq;

    if (event === 'KONTROL_SKOR') {
        snap.skor = { ...payload, seq };
        io.to(room).emit('UPDATE_SKOR', snap.skor);
    } else if (event === 'KONTROL_WAKTU') {
        snap.waktu = { ...payload, seq };
        io.to(room).emit('UPDATE_WAKTU', snap.waktu);
    } else {
        return res.status(400).json({ status: false, message: 'event tidak dikenal' });
    }

    return res.json({ status: true, event, room, seq });
});

const server = http.createServer(app);
const io = new Server(server, { cors: { origin: ORIGIN } });

/**
 * snapshots[roomId] = { seq, waktu, skor }
 *  - seq   : sequence terakhir yang diterima room
 *  - waktu : payload UPDATE_WAKTU terakhir
 *  - skor  : payload UPDATE_SKOR terakhir
 */
const snapshots = {};

function getRoom(id) {
    const room = String(id);
    if (!snapshots[room]) {
        snapshots[room] = { seq: 0, waktu: null, skor: null };
    }
    return room;
}

io.on('connection', (socket) => {
    console.log('[connect]', socket.id);

    // Klien (Layar/Juri/KP/Sekretaris/Broadcast) join room pertandingan.
    socket.on('JOIN_ROOM', (idPertandingan) => {
        const room = getRoom(idPertandingan);
        socket.join(room);
        console.log(`[join] ${socket.id} -> room ${room}`);

        // Recovery: kirim snapshot terakhir ke klien yang baru join.
        const snap = snapshots[room];
        if (snap.waktu) socket.emit('UPDATE_WAKTU', snap.waktu);
        if (snap.skor) socket.emit('UPDATE_SKOR', snap.skor);
        socket.emit('STATE_SYNC', { seq: snap.seq, waktu: snap.waktu, skor: snap.skor });
    });

    // Timer dari sekretaris -> broadcast ke seluruh room (kecuali pengirim).
    socket.on('KONTROL_WAKTU', (data) => {
        const room = getRoom(data && data.id_pertandingan);
        const snap = snapshots[room];
        const seq = (data && typeof data.seq === 'number') ? data.seq : (snap.seq + 1);

        // Anti out-of-order: abaikan event basi.
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
        const room = getRoom(data && data.id_pertandingan);
        const snap = snapshots[room];
        const seq = (data && typeof data.seq === 'number') ? data.seq : (snap.seq + 1);

        if (seq < snap.seq) {
            console.log(`[drop skor] room ${room} seq ${seq} < ${snap.seq}`);
            return;
        }
        snap.seq = seq;
        snap.skor = { ...data, seq };
        // Termasuk pengirim? Tidak — pengirim sudah punya state-nya. Broadcast ke lainnya.
        socket.to(room).emit('UPDATE_SKOR', snap.skor);
    });

    // Reset snapshot ketika partai selesai (opsional, dipanggil sekretaris).
    socket.on('RESET_ROOM', (idPertandingan) => {
        const room = String(idPertandingan);
        delete snapshots[room];
        io.to(room).emit('ROOM_RESET', { id_pertandingan: room });
    });

    socket.on('disconnect', () => {
        console.log('[disconnect]', socket.id);
    });
});

server.listen(PORT, () => {
    console.log(`DPS Scoring Realtime Server berjalan di port ${PORT} (origin: ${ORIGIN})`);
});
