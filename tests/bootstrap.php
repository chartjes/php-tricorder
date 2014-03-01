<?php

// Bootstrap file for creating a test environment
require '../vendor/autoload.php';

PHP_OS == "Windows" || PHP_OS == "WINNT" ? define("SEPARATOR", "\\") : define("SEPARATOR", "/");
define('FIXTURE_DIR', __DIR__ . SEPARATOR. 'Tricorder' . SEPARATOR . 'Fixtures' . SEPARATOR);
