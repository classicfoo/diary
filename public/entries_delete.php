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
$entryId = (int) ($_POST['entry_id'] ?? 0);

$journal = get_journal($db, $journalId, $userId);
if (!$journal) {
    flash('danger', 'Invalid journal.');
    redirect('/dashboard.php');
}

$entry = get_entry($db, $entryId, $journalId);
if ($entry) {
    delete_entry($db, $entryId, $journalId);
    flash('success', 'Entry deleted.');
}

redirect('/journal.php?id=' . $journalId);
