<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (is_post()) {
    validate_csrf();
    logout_user();
}

redirect('/login.php');
