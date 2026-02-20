<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_auth();

if (!is_post()) {
    redirect('/dashboard.php');
}

validate_csrf();
$title = trim((string) ($_POST['title'] ?? ''));

if ($title === '') {
    flash('danger', 'Journal title is required.');
    redirect('/dashboard.php');
}

if (mb_strlen($title) > 120) {
    flash('danger', 'Journal title must be 120 characters or fewer.');
    redirect('/dashboard.php');
}

$journalId = create_journal($db, (int) current_user_id(), $title);
flash('success', 'Journal created.');
redirect('/journal.php?id=' . $journalId);
