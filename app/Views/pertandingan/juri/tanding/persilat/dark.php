<?php
/**
 * View juri tanding PERSILAT tema gelap.
 * Mewarisi seluruh markup/logic dari light.php; pembedaan tema ditangani
 * variabel $theme (lihat blok <style> kondisional di light.php).
 */
echo view('pertandingan/juri/tanding/persilat/light', array_merge(get_defined_vars(), ['theme' => 'dark']));
