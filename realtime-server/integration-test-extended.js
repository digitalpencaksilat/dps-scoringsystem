/**
 * Extended integration test — verifies socket events added in production-readiness fixes:
 *   - PERTANDINGAN_SELESAI (fix #3 tanding)
 *   - PENAMPILAN_SELESAI + SENI_SELESAI (fix #3 seni)
 *   - NILAI_UPDATE (fix #6 - layar tanding live score)
 *   - HUKUMAN_UPDATE (fix #5 - juri seni live updates from KP)
 *   - AKSES_PENILAIAN + SENI_AKSES_DITUTUP (fix #5 - juri seni gating)
 *   - UPDATE_NILAI_SENI (fix #5 - juri seni nilai refresh)
 *   - KONTROL_WAKTU_SENI (fix #5 - juri seni timer)
 *
 * Run: node integration-test-extended.js
 */
const { io } = require('socket.io-client');
const http = require('http');

const URL = 'http://localhost:3000';
const ROOM_TANDING = 9999;
const ROOM_SENI_ID = 8888;

let passed = 0, failed = 0;
const checks = [];

function check(name, condition) {
    checks.push({ name, condition });
    condition ? (passed++, console.log('✓ PASS', name)) : (failed++, console.log('✗ FAIL', name));
}

function phpEmit(event, payload) {
    return new Promise((resolve, reject) => {
        const body = JSON.stringify({ event, payload });
        const req = http.request(URL + '/emit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Content-Length': Buffer.byteLength(body),
                'X-Internal': '1',
            },
        }, (res) => {
            let d = '';
            res.on('data', c => d += c);
            res.on('end', () => {
                try { resolve(JSON.parse(d)); }
                catch (e) { resolve({ raw: d, status: res.statusCode }); }
            });
        });
        req.on('error', reject);
        req.write(body);
        req.end();
    });
}

// ─── Setup tanding client (Layar tanding) ────────────────────────────────────
const layarTanding = io(URL);
const layarSeni = io(URL);
const juriSeni = io(URL);

const received = {
    PERTANDINGAN_SELESAI: false,
    NILAI_UPDATE: false,
    PENAMPILAN_SELESAI_layar: false,
    PENAMPILAN_SELESAI_juri: false,
    SENI_SELESAI: false,
    UPDATE_NILAI_SENI: false,
    HUKUMAN_UPDATE: false,
    AKSES_PENILAIAN: false,
    SENI_AKSES_DITUTUP: false,
    KONTROL_WAKTU_SENI: false,
};

layarTanding.on('connect', () => {
    layarTanding.emit('JOIN_ROOM', ROOM_TANDING);

    layarTanding.on('PERTANDINGAN_SELESAI', (d) => {
        received.PERTANDINGAN_SELESAI = true;
        check('Layar tanding terima PERTANDINGAN_SELESAI', d && d.id_pertandingan === ROOM_TANDING);
        check('PERTANDINGAN_SELESAI bawa sudut_pemenang', d && d.sudut_pemenang === 'merah');
        check('PERTANDINGAN_SELESAI bawa jenis_kemenangan', d && d.jenis_kemenangan === 'Poin');
    });

    layarTanding.on('NILAI_UPDATE', (d) => {
        received.NILAI_UPDATE = true;
        check('Layar tanding terima NILAI_UPDATE', d && d.id_pertandingan === ROOM_TANDING);
        check('NILAI_UPDATE bawa skor_merah & skor_biru', d && d.skor_merah === 8 && d.skor_biru === 3);
    });
});

layarSeni.on('connect', () => {
    layarSeni.emit('JOIN_ROOM', { id_penampilan_seni: ROOM_SENI_ID });

    layarSeni.on('PENAMPILAN_SELESAI', (d) => {
        received.PENAMPILAN_SELESAI_layar = true;
        check('Layar seni terima PENAMPILAN_SELESAI', d && d.id_penampilan_seni === ROOM_SENI_ID);
    });

    layarSeni.on('SENI_SELESAI', (d) => {
        received.SENI_SELESAI = true;
        check('Layar seni terima SENI_SELESAI', d && d.id_penampilan_seni === ROOM_SENI_ID);
        check('SENI_SELESAI bawa id_pemenang', d && d.id_pemenang === ROOM_SENI_ID);
    });

    layarSeni.on('UPDATE_NILAI_SENI', (d) => {
        received.UPDATE_NILAI_SENI = true;
        check('Layar seni terima UPDATE_NILAI_SENI', d && d.id_penampilan_seni === ROOM_SENI_ID);
    });
});

juriSeni.on('connect', () => {
    juriSeni.emit('JOIN_ROOM', { id_penampilan_seni: ROOM_SENI_ID });

    juriSeni.on('PENAMPILAN_SELESAI', (d) => {
        received.PENAMPILAN_SELESAI_juri = true;
        check('Juri seni terima PENAMPILAN_SELESAI', d && d.id_penampilan_seni === ROOM_SENI_ID);
    });

    juriSeni.on('HUKUMAN_UPDATE', (d) => {
        received.HUKUMAN_UPDATE = true;
        check('Juri seni terima HUKUMAN_UPDATE', d && d.id_penampilan_seni === ROOM_SENI_ID);
    });

    juriSeni.on('AKSES_PENILAIAN', (d) => {
        received.AKSES_PENILAIAN = true;
        check('Juri seni terima AKSES_PENILAIAN', d && d.id_penampilan_seni === ROOM_SENI_ID);
        check('AKSES_PENILAIAN bawa akses', d && d.akses === 'dibuka');
    });

    juriSeni.on('SENI_AKSES_DITUTUP', (d) => {
        received.SENI_AKSES_DITUTUP = true;
        check('Juri seni terima SENI_AKSES_DITUTUP', d && d.id_penampilan_seni === ROOM_SENI_ID);
    });

    juriSeni.on('KONTROL_WAKTU_SENI', (d) => {
        received.KONTROL_WAKTU_SENI = true;
        check('Juri seni terima KONTROL_WAKTU_SENI', d && d.id_penampilan_seni === ROOM_SENI_ID);
        check('KONTROL_WAKTU_SENI bawa status_penampilan', d && d.status_penampilan === 'sedang_tampil');
    });
});

// ─── Run sequence ─────────────────────────────────────────────────────────────
setTimeout(async () => {
    console.log('\n--- Emitting events ---\n');

    // Tanding events
    await phpEmit('NILAI_UPDATE', {
        id_pertandingan: ROOM_TANDING,
        skor_merah: 8,
        skor_biru: 3,
    });

    await phpEmit('PERTANDINGAN_SELESAI', {
        id_pertandingan: ROOM_TANDING,
        sudut_pemenang: 'merah',
        jenis_kemenangan: 'Poin',
    });

    // Seni events
    await phpEmit('AKSES_PENILAIAN', {
        id_penampilan_seni: ROOM_SENI_ID,
        akses: 'dibuka',
    });

    await phpEmit('SENI_AKSES_DITUTUP', {
        id_penampilan_seni: ROOM_SENI_ID,
    });

    await phpEmit('HUKUMAN_UPDATE', {
        id_penampilan_seni: ROOM_SENI_ID,
        diskualifikasi: 1,
    });

    await phpEmit('UPDATE_NILAI_SENI', {
        id_penampilan_seni: ROOM_SENI_ID,
    });

    await phpEmit('KONTROL_WAKTU_SENI', {
        id_penampilan_seni: ROOM_SENI_ID,
        status_penampilan: 'sedang_tampil',
        waktu_tampil: 120,
    });

    await phpEmit('PENAMPILAN_SELESAI', {
        id_penampilan_seni: ROOM_SENI_ID,
        id_pemenang: ROOM_SENI_ID,
    });

    await phpEmit('SENI_SELESAI', {
        id_penampilan_seni: ROOM_SENI_ID,
        id_pemenang: ROOM_SENI_ID,
        jenis_kemenangan: 'poin',
    });

    // Wait for events to propagate, then summarize
    setTimeout(() => {
        console.log(`\n=== Summary: ${passed} passed, ${failed} failed ===`);
        if (failed === 0) {
            console.log('✓ All real-time event contracts working');
        } else {
            console.log('✗ Some events did not arrive — check server.js RELAY_EVENTS or PHP helper payload.');
        }
        layarTanding.close();
        layarSeni.close();
        juriSeni.close();
        process.exit(failed === 0 ? 0 : 1);
    }, 500);
}, 600);

setTimeout(() => {
    console.log('\n✗ TIMEOUT — some events never received:');
    Object.entries(received).forEach(([ev, got]) => {
        if (!got) console.log(`  - ${ev}`);
    });
    process.exit(1);
}, 8000);
