<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (is_authenticated()) {
    redirect('/dashboard.php');
}

redirect('/login.php');
