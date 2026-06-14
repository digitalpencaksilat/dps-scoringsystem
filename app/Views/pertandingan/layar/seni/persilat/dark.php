<?= $this->extend('layouts/penilaian') ?>

<?= $this->section('styles') ?>
<style>
    body { background: #000 !important; overflow: hidden; }

    .fade-down { opacity: 0; transform: translateY(-30px); transition: opacity 0.5s ease, transform 0.5s ease; }
    .fade-up { opacity: 0; transform: translateY(30px); transition: opacity 0.5s ease, transform 0.5s ease; }
    .fade-left { opacity: 0; transform: translateX(-30px); transition: opacity 0.5s ease, transform 0.5s ease; }
    .fade-right { opacity: 0; transform: translateX(30px); transition: opacity 0.5s ease, transform 0.5s ease; }
    .fade-down.show, .fade-up.show, .fade-left.show, .fade-right.show { opacity: 1; transform: translate(0); }

    .bg-gradient-180-black { background: linear-gradient(180deg, #111 0%, #000 100%); }
    .bg-gradient-180-gray-dark { background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%); }
    .bg-gradient-180-white { background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%); }
    .bg-gradient-180-warning { background: linear-gradient(180deg, #ffc107 0%, #e0a800 100%); }

    .kolom_total_nilai {
        min-height: 22vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        transition: all 0.3s ease;
        background: linear-gradient(180deg, #2c2c2c 0%, #1a1a1a 100%);
    }
    .kolom_total_nilai .nilai-juri {
        font-size: clamp(3rem, 8vw, 5rem);
        font-weight: 900;
        line-height: 1;
        color: #fff;
        transition: all 0.3s ease;
    }
    .kolom_total_nilai .label-juri {
        font-size: 1rem;
        color: #aaa;
        margin-top: 0.25rem;
        transition: all 0.3s ease;
    }
    /* Terpilih: yellow/warning gradient (parity legacy bg-gradient-180-warning) */
    .urutan_total_nilai_juri .kolom_total_nilai.terpilih {
        background: linear-gradient(180deg, #ffc107 0%, #e0a800 100%) !important;
    }
    .urutan_total_nilai_juri .kolom_total_nilai.terpilih .nilai-juri {
        color: #000 !important;
        text-decoration: none !important;
        opacity: 1 !important;
    }
    .urutan_total_nilai_juri .kolom_total_nilai.terpilih .label-juri {
        color: #333 !important;
        opacity: 1 !important;
    }
    /* Tidak terpilih: strikethrough + opacity */
    .urutan_total_nilai_juri .kolom_total_nilai.tidak-terpilih .nilai-juri {
        text-decoration: line-through;
        opacity: 0.4;
    }
    .urutan_total_nilai_juri .kolom_total_nilai.tidak-terpilih .label-juri {
        opacity: 0.4;
    }

    .summary-box {
        min-height: 10vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 0.5rem;
    }
    .summary-box .summary-value {
        font-size: clamp(2rem, 5vw, 3.5rem);
        font-weight: 800;
        line-height: 1;
        color: #fff;
    }
    .summary-box .summary-label {
        font-size: 0.85rem;
        color: #aaa;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .kolom-nilai-akhir {
        min-height: 14vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .kolom-nilai-akhir .nilai_akhir {
        font-size: clamp(4rem, 12vw, 8rem);
        font-weight: 900;
        line-height: 1;
        color: #fff;
    }
    .kolom-nilai-akhir .label-nilai-akhir {
        font-size: 1rem;
        color: #ccc;
    }

    .kolom-waktu {
        min-height: 14vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .kolom-waktu .waktu_tampil {
        font-size: clamp(3.5rem, 10vw, 7rem);
        font-weight: 800;
        line-height: 1;
        color: #fff;
    }
    .kolom-waktu .label-waktu {
        font-size: 1rem;
        color: #ccc;
    }

    #daftar-peserta {
        min-height: 10vh;
    }
    #daftar-peserta .nama-peserta {
        font-size: clamp(1.5rem, 4vw, 2.5rem);
        font-weight: 700;
        color: #fff;
    }
    #daftar-peserta .nama-kontingen {
        font-size: clamp(1rem, 2.5vw, 1.5rem);
        font-weight: 400;
        color: #ccc;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid min-vh-100 bg-gradient-180-black overflow-hidden px-3 py-2">

    <!-- Competition Title -->
    <?= $this->include('pertandingan/layar/seni/persilat/components/header') ?>

    <!-- Peserta Info -->
    <div class="row mt-2 fade-down" id="daftar-peserta">
        <div class="col-12 bg-gradient-180-gray-dark rounded py-3 px-4 d-flex align-items-center">
            <div class="flex-grow-1">
                <p class="nama-peserta m-0">
                    <?php
                        // Prefer anggota_kelompok_peserta_seni from penampilan query (via kps join)
                        // Legacy parity: kps.anggota_kelompok_peserta_seni stores formatted display string
                        $displayName = 'Peserta';
                        if (!empty($penampilan_seni_berlangsung->anggota_kelompok_peserta_seni)) {
                            $displayName = esc($penampilan_seni_berlangsung->anggota_kelompok_peserta_seni);
                        } else {
                            $namaPeserta = [];
                            if (!empty($peserta_seni)) {
                                foreach ($peserta_seni as $ps) {
                                    $namaPeserta[] = esc($ps->nama_pendaftar ?? '');
                                }
                            }
                            $displayName = implode(' &bull; ', $namaPeserta) ?: 'Peserta';
                        }
                        echo $displayName;
                    ?>
                </p>
                <p class="nama-kontingen m-0">
                    <?= strtoupper(esc($peserta_seni[0]->nama_kontingen ?? '')) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Juri Score Columns -->
    <div class="row mt-2 g-2 urutan_total_nilai_juri">
        <?php
            $juriCount = 0;
            $idPenampilan = $penampilan_seni_berlangsung->id_penampilan_seni ?? 0;
            if (isset($data_nilai[$idPenampilan])) {
                $juriCount = count($data_nilai[$idPenampilan]);
            }
            // Create placeholder columns
            for ($idx = 0; $idx < max($juriCount, 5); $idx++):
        ?>
        <div class="col fade-down">
            <div class="kolom_total_nilai bg-gradient-180-gray-dark rounded" data-index="<?= $idx ?>">
                <span class="nilai-juri">-</span>
                <span class="label-juri">Juri <?= $idx + 1 ?></span>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <!-- Summary Row: Median Kebenaran | Std Dev | Median | Hukuman -->
    <div class="row mt-2 g-2">
        <div class="col-3 fade-up">
            <div class="summary-box bg-gradient-180-gray-dark rounded kolom-median-kebenaran">
                <span class="summary-value median_kebenaran">0</span>
                <span class="summary-label">Median Kebenaran</span>
            </div>
        </div>
        <div class="col-3 fade-up">
            <div class="summary-box bg-gradient-180-gray-dark rounded kolom-standar-deviasi">
                <span class="summary-value standar_deviasi">0</span>
                <span class="summary-label">Standard Deviation</span>
            </div>
        </div>
        <div class="col-3 fade-up">
            <div class="summary-box bg-gradient-180-gray-dark rounded kolom-median">
                <span class="summary-value median">0</span>
                <span class="summary-label">Median</span>
            </div>
        </div>
        <div class="col-3 fade-up">
            <div class="summary-box bg-gradient-180-gray-dark rounded kolom-hukuman">
                <span class="summary-value hukuman">0</span>
                <span class="summary-label">Penalty</span>
            </div>
        </div>
    </div>

    <!-- Final Score + Timer -->
    <div class="row mt-2 g-2">
        <div class="col-8 fade-left">
            <div class="kolom-nilai-akhir bg-gradient-180-gray-dark rounded">
                <span class="nilai_akhir">0.000</span>
                <span class="label-nilai-akhir">Final Score</span>
            </div>
        </div>
        <div class="col-4 fade-right">
            <div class="kolom-waktu bg-gradient-180-gray-dark rounded">
                <span class="waktu_tampil">00:00</span>
                <span class="label-waktu">Time</span>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/js/penilaian/plugins/timer.jquery.js') ?>"></script>
<script src="https://cdn.socket.io/4.7.5/socket.io.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url('assets/js/penilaian/layar_seni_persilat.js?v=' . time()) ?>"></script>
<script>
$(document).ready(function () {
    var $data_nilai = <?= json_encode($data_nilai, JSON_NUMERIC_CHECK) ?>;
    var $penampilan_seni_berlangsung = <?= json_encode($penampilan_seni_berlangsung, JSON_NUMERIC_CHECK) ?>;
    layar.init($penampilan_seni_berlangsung, $data_nilai);
    ui.start_animation();
});
</script>
<?= $this->endSection() ?>
