/**
 * Smoke test realtime-server: round-trip skor + recovery snapshot.
 * Bukan bagian aplikasi — hanya untuk verifikasi Fase 8.
 */
const { io } = require('socket.io-client');

const URL = 'http://localhost:3000';
const ROOM = 999;
let passed = 0, failed = 0;
function check(name, cond) { (cond ? (passed++, console.log('PASS', name)) : (failed++, console.log('FAIL', name))); }

const layar = io(URL);
layar.on('connect', () => {
    layar.emit('JOIN_ROOM', ROOM);

    layar.on('UPDATE_SKOR', (d) => {
        check('layar terima UPDATE_SKOR skor_merah=5', d && d.skor_merah === 5);
        check('UPDATE_SKOR punya seq', typeof d.seq === 'number');

        // Setelah skor masuk snapshot, klien baru (juri) harus dapat recovery.
        const juri = io(URL);
        juri.on('connect', () => juri.emit('JOIN_ROOM', ROOM));
        juri.on('STATE_SYNC', (s) => {
            check('recovery STATE_SYNC bawa skor terakhir', s && s.skor && s.skor.skor_merah === 5);
            setTimeout(() => {
                console.log(`\n=== ${passed} passed, ${failed} failed ===`);
                layar.close(); juri.close();
                process.exit(failed === 0 ? 0 : 1);
            }, 200);
        });
    });

    // Sekretaris emit skor setelah layar join.
    setTimeout(() => {
        const sekre = io(URL);
        sekre.on('connect', () => {
            sekre.emit('JOIN_ROOM', ROOM);
            setTimeout(() => sekre.emit('KONTROL_SKOR', { id_pertandingan: ROOM, skor_merah: 5, skor_biru: 2, seq: 1 }), 100);
        });
    }, 200);
});

setTimeout(() => { console.log('TIMEOUT'); process.exit(1); }, 6000);
