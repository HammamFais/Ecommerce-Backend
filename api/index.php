<?php

// Vercel hanya mengizinkan penulisan ke /tmp, jadi storage Laravel dipindah ke sana.
$storagePath = '/tmp/storage';

foreach (['framework/views', 'framework/cache/data', 'framework/sessions', 'logs'] as $dir) {
    if (! is_dir("{$storagePath}/{$dir}")) {
        @mkdir("{$storagePath}/{$dir}", 0777, true);
    }
}

putenv("APP_STORAGE_PATH={$storagePath}");
$_ENV['APP_STORAGE_PATH'] = $storagePath;

// Vercel memanggil file ini sebagai /api/index.php; tanpa ini semua route Laravel tidak cocok.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';

require __DIR__.'/../public/index.php';
