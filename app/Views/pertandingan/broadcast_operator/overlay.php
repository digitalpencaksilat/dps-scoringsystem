<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Overlay</title>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --corner-red:#c62828; --corner-blue:#1565c0; --brand-secondary:#c5a017; }
        html, body { margin: 0; height: 100%; background: transparent; font-family: 'Poppins', sans-serif; overflow: hidden; }
        .overlay-hidden { display: none !important; }

        /* Lower third scoreboard */
        .ov-scoreboard {
            position: fixed; left: 50%; bottom: 5vh; transform: translateX(-50%);
            display: flex; align-items: stretch; border-radius: 14px; overflow: hidden;
            box-shadow: 0 12px 40px rgba(0,0,0,0.45); font-family: 'Oswald', sans-serif;
            opacity: 0; transition: opacity .4s ease;
        }
        .ov-scoreboard.show { opacity: 1; }
        .ov-side { display: flex; flex-direction: column; justify-content: center; padding: 10px 22px; color: #fff; min-width: 220px; }
        .ov-side.biru  { background: linear-gradient(180deg,#1565c0,#0d47a1); align-items: flex-start; }
        .ov-side.merah { background: linear-gradient(180deg,#c62828,#8e0000); align-items: flex-end; }
        .ov-nama { font-size: 22px; font-weight: 600; line-height: 1.1; }
        .ov-kontingen { font-size: 13px; opacity: 0.85; font-family: 'Poppins'; }
        .ov-skor { font-size: 30px; font-weight: 700; margin-top: 2px; }
        .ov-center {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            background: #0b0d12; color: #fff; padding: 8px 20px; min-width: 90px;
        }
        .ov-ronde { font-size: 13px; color: var(--brand-secondary); letter-spacing: 2px; }
        .ov-status { font-size: 12px; opacity: 0.8; font-family: 'Poppins'; text-transform: uppercase; }

        /* Lower third intro */
        .ov-lower-third {
            position: fixed; left: 6vw; bottom: 8vh; background: rgba(11,13,18,0.92);
            border-left: 6px solid var(--brand-secondary); padding: 14px 26px; border-radius: 8px;
            color: #fff; font-family: 'Oswald', sans-serif; opacity: 0; transition: opacity .4s ease;
        }
        .ov-lower-third.show { opacity: 1; }
        .ov-lt-title { font-size: 28px; font-weight: 700; }
        .ov-lt-sub { font-size: 16px; opacity: 0.85; font-family: 'Poppins'; }
    </style>
</head>
<body data-id-gelanggang="<?= (int) $id_gelanggang ?>">
    <!-- Scoreboard lower-third -->
    <div class="ov-scoreboard" id="ov-scoreboard">
        <div class="ov-side biru">
            <span class="ov-nama" id="ov-nama-biru">—</span>
            <span class="ov-kontingen" id="ov-kont-biru">—</span>
            <span class="ov-skor" id="ov-skor-biru">0</span>
        </div>
        <div class="ov-center">
            <span class="ov-ronde">R<span id="ov-ronde">-</span></span>
            <span class="ov-status" id="ov-status">—</span>
        </div>
        <div class="ov-side merah">
            <span class="ov-nama" id="ov-nama-merah">—</span>
            <span class="ov-kontingen" id="ov-kont-merah">—</span>
            <span class="ov-skor" id="ov-skor-merah">0</span>
        </div>
    </div>

    <!-- Lower third intro -->
    <div class="ov-lower-third" id="ov-lowerthird">
        <div class="ov-lt-title" id="ov-lt-title">Digital Pencak Silat</div>
        <div class="ov-lt-sub" id="ov-lt-sub">Pertandingan</div>
    </div>

    <script>
    (function () {
        const gid = document.body.dataset.idGelanggang;
        const endpoint = '<?= base_url('broadcast-operator/refresh-graphic') ?>/' + gid;
        const sb = document.getElementById('ov-scoreboard');
        const lt = document.getElementById('ov-lowerthird');

        function setText(id, val) { const e = document.getElementById(id); if (e) e.textContent = val; }

        function apply(data) {
            const scene = (data && data.scene) || 'kosong';
            const p = data && data.pertandingan;

            // Default sembunyikan semua.
            sb.classList.remove('show');
            lt.classList.remove('show');

            if (!p) return;

            setText('ov-skor-merah', p.skor_merah);
            setText('ov-skor-biru', p.skor_biru);
            setText('ov-nama-merah', p.nama_merah);
            setText('ov-nama-biru', p.nama_biru);
            setText('ov-kont-merah', p.kontingen_merah);
            setText('ov-kont-biru', p.kontingen_biru);
            setText('ov-ronde', p.ronde);
            setText('ov-status', (p.status || '').replace(/_/g, ' '));

            if (scene === 'scoreboard') {
                sb.classList.add('show');
            } else if (scene === 'lower-third-intro' || scene === 'intro') {
                setText('ov-lt-title', p.nama_biru + ' vs ' + p.nama_merah);
                setText('ov-lt-sub', p.kontingen_biru + ' — ' + p.kontingen_merah);
                lt.classList.add('show');
            }
            // scene lain (highlight-*) bisa ditambah di Fase berikut.
        }

        function poll() {
            fetch(endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json()).then(apply).catch(() => {});
        }
        setInterval(poll, 1500);
        poll();
    })();
    </script>
</body>
</html>
