/**
 * Integrasi test Fase 8: simulasi PHP emit (via HTTP /emit, persis seperti
 * realtime_helper.php) -> server broadcast -> Layar client terima UPDATE_SKOR.
 */
const { io } = require('socket.io-client');
const http = require('http');

const URL = 'http://localhost:3000';
const ROOM = 96;
let passed = 0, failed = 0;
function check(n, c) { c ? (passed++, console.log('PASS', n)) : (failed++, console.log('FAIL', n)); }

function phpEmit(event, payload) {
    return new Promise((resolve) => {
        const body = JSON.stringify({ event, payload });
        const req = http.request(URL + '/emit', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Content-Length': Buffer.byteLength(body), 'X-Internal': '1' },
        }, (res) => { let d = ''; res.on('data', c => d += c); res.on('end', () => resolve(JSON.parse(d))); });
        req.write(body); req.end();
    });
}

const layar = io(URL);
layar.on('connect', () => {
    layar.emit('JOIN_ROOM', ROOM);

    layar.on('UPDATE_SKOR', (d) => {
        check('Layar terima UPDATE_SKOR (skor_merah=12)', d && d.skor_merah === 12);
        check('payload bawa ronde', d && d.ronde === '2');
    });
    layar.on('UPDATE_WAKTU', (d) => {
        check('Layar terima UPDATE_WAKTU (berlangsung)', d && d.status_pertandingan === 'berlangsung');
    });
    layar.on('ROOM_RESET', () => {
        check('Layar terima ROOM_RESET saat partai selesai', true);
        setTimeout(() => { console.log(`\n=== ${passed} passed, ${failed} failed ===`); layar.close(); process.exit(failed === 0 ? 0 : 1); }, 200);
    });

    // Simulasikan urutan event seperti controller PHP.
    setTimeout(async () => {
        await phpEmit('KONTROL_SKOR', { id_pertandingan: ROOM, skor_merah: 12, skor_biru: 5, ronde: '2', seq: 10 });
        await phpEmit('KONTROL_WAKTU', { id_pertandingan: ROOM, status_pertandingan: 'berlangsung', data_waktu: { '2': [0, 120000, 60000] }, seq: 11 });
        await phpEmit('RESET_ROOM', { id_pertandingan: ROOM });
    }, 300);
});

setTimeout(() => { console.log('TIMEOUT'); process.exit(1); }, 6000);
