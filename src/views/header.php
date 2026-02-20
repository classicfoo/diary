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
<body class="<?= e($pageClass ?? '') ?>">
<?php if (is_authenticated()): ?>
<nav class="navbar navbar-expand-lg app-topbar px-3 py-2">
    <div class="container-fluid">
        <a class="navbar-brand app-logo" href="/dashboard.php">penzu</a>
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
    <a class="navbar-brand app-logo m-0" href="/">penzu</a>
</nav>
<?php endif; ?>

<main class="container app-main">
    <?php $flash = consume_flash(); ?>
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
