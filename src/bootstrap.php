<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_name($config['session_name']);
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/repositories.php';

$db = db_connect($config['db_path']);
initialize_database($db);
