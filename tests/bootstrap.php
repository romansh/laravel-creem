<?php
// 1. Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// 2. Create storage directories for Testbench
$paths = [
    __DIR__ . '/../storage/framework/cache',
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/views',
    __DIR__ . '/../storage/framework/testing',
];

foreach ($paths as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}

