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

$journal = get_journal($db, $journalId, $userId);
if (!$journal) {
    flash('danger', 'Invalid journal.');
    redirect('/dashboard.php');
}

$title = 'Untitled';
$entryDate = now_date();
$entryId = create_entry($db, $journalId, $title, '', $entryDate);

flash('success', 'Entry created.');
redirect('/journal.php?id=' . $journalId . '&entry=' . $entryId);
