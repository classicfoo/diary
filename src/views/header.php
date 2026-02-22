<?php declare(strict_types=1); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Diary') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/app.css" rel="stylesheet">
</head>
<?php
$flash = consume_flash();
$brandText = $brandText ?? 'Journal';
$appNavColor = $appNavColor ?? ($_SESSION['app_nav_color'] ?? '#1e1f23');
$safeNavColor = preg_match('/^#[0-9a-fA-F]{6}$/', (string) $appNavColor) ? (string) $appNavColor : '#1e1f23';
$flashTypeMap = [
    'success' => 'toast-success',
    'danger' => 'toast-danger',
    'warning' => 'toast-warning',
    'info' => 'toast-info',
];
$flashClass = $flash ? ($flashTypeMap[$flash['type']] ?? 'toast-info') : '';
?>
<body class="<?= e($pageClass ?? '') ?>"<?= is_authenticated() ? ' style="--app-nav-color: ' . e($safeNavColor) . ';"' : '' ?>>
<?php if (is_authenticated()): ?>
<nav class="navbar navbar-expand-lg app-topbar px-3 py-2">
    <div class="container-fluid">
        <a class="navbar-brand app-logo" href="/dashboard.php"><?= e((string) $brandText) ?></a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <a href="/dashboard.php" class="btn btn-light btn-sm fw-semibold">Dashboard</a>
            <form action="/logout.php" method="post" class="m-0">
                <?= csrf_input() ?>
                <button type="submit" class="btn btn-outline-light btn-sm">Sign out</button>
            </form>
        </div>
    </div>
</nav>
<?php else: ?>
<nav class="navbar app-topbar px-3 py-2 justify-content-center">
    <a class="navbar-brand app-logo m-0" href="/"><?= e((string) $brandText) ?></a>
</nav>
<?php endif; ?>

<?php if ($flash): ?>
<div class="app-toast-wrap" id="app-toast-wrap">
    <div class="app-toast <?= e($flashClass) ?>" id="app-toast" role="status" aria-live="polite">
        <?= e($flash['message']) ?>
    </div>
</div>
<?php endif; ?>

<main class="container app-main">
