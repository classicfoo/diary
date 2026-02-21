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
$title = trim((string) ($_POST['title'] ?? ''));
$content = trim((string) ($_POST['content'] ?? ''));
$entryDate = trim((string) ($_POST['entry_date'] ?? ''));

$title = decode_legacy_entities($title);
$content = decode_legacy_entities($content);
$title = to_title_case($title);

$journal = get_journal($db, $journalId, $userId);
if (!$journal) {
    flash('danger', 'Invalid journal.');
    redirect('/dashboard.php');
}

$entry = get_entry($db, $entryId, $journalId);
if (!$entry) {
    flash('danger', 'Entry not found.');
    redirect('/journal.php?id=' . $journalId);
}

if ($entryDate === '' || !DateTimeImmutable::createFromFormat('Y-m-d', $entryDate)) {
    flash('danger', 'Entry date is invalid.');
    redirect('/journal.php?id=' . $journalId . '&entry=' . $entryId);
}

update_entry($db, $entryId, $journalId, $title, $content, $entryDate);
flash('success', 'Entry saved.');
redirect('/journal.php?id=' . $journalId . '&entry=' . $entryId);
