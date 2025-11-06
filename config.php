<?php
// config.php

function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception("FATAL ERROR: The .env file was not found. Please ensure it exists in the project root. Script looked at: " . $path);
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

// Looks for the .env file in the same directory as this script.
loadEnv(__DIR__ . '/.env');