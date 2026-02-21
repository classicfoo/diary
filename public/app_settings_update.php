<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_auth();

if (!is_post()) {
    redirect('/dashboard.php');
}

validate_csrf();

$color = (string) ($_POST['nav_color'] ?? '#1e1f23');
update_user_nav_color($db, (int) current_user_id(), $color);
$_SESSION['app_nav_color'] = preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#1e1f23';

flash('success', 'App settings updated.');
redirect('/dashboard.php');
