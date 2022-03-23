<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if ('dev' == $_ENV['APP_ENV']) {
    // Display errors in dev
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
