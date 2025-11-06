<?php
// api/check_session.php

// Use a custom, writable path for sessions to avoid server permission issues.
ini_set('session.save_path', realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../tmp'));
session_start();

// The header and error reporting are now handled by db.php
// require 'db.php'; // We don't need a DB connection just to check a session variable.

if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
    echo json_encode([
        'loggedIn' => true,
        'userId' => $_SESSION['user_id'],
        'username' => $_SESSION['username']
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}