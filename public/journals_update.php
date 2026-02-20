<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_auth();

if (!is_post()) {
    redirect('/dashboard.php');
}

validate_csrf();

$userId = (int) current_user_id();
$journalId = (int) ($_POST['journal_id'] ?? 0);
$action = (string) ($_POST['action'] ?? '');

$journal = get_journal($db, $journalId, $userId);
if (!$journal) {
    flash('danger', 'Journal not found.');
    redirect('/dashboard.php');
}

if ($action === 'rename') {
    $title = trim((string) ($_POST['title'] ?? ''));
    if ($title === '') {
        flash('danger', 'Journal name is required.');
        redirect('/dashboard.php');
    }
    if (mb_strlen($title) > 120) {
        flash('danger', 'Journal name must be 120 characters or fewer.');
        redirect('/dashboard.php');
    }

    update_journal_title($db, $journalId, $userId, $title);
    flash('success', 'Journal name updated.');
    redirect('/dashboard.php');
}

if ($action === 'settings') {
    $bgColor = (string) ($_POST['bg_color'] ?? '#2f79bb');
    $sortOrder = (string) ($_POST['sort_order'] ?? 'updated_desc');

    update_journal_settings($db, $journalId, $userId, $bgColor, $sortOrder);
    flash('success', 'Journal settings updated.');
    redirect('/dashboard.php');
}

flash('danger', 'Invalid journal action.');
redirect('/dashboard.php');
