<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
(Dotenv::createImmutable(__DIR__.'/..'))->load();

if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] == 1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

date_default_timezone_set('America/Mexico_City');
