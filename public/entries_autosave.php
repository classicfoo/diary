<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_auth();

header('Content-Type: application/json; charset=utf-8');

if (!is_post()) {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

validate_csrf();
$userId = (int) current_user_id();
$journalId = (int) ($_POST['journal_id'] ?? 0);
$entryId = (int) ($_POST['entry_id'] ?? 0);
$title = trim((string) ($_POST['title'] ?? ''));
$content = (string) ($_POST['content'] ?? '');
$entryDate = trim((string) ($_POST['entry_date'] ?? ''));

$title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
$title = to_title_case($title);

$journal = get_journal($db, $journalId, $userId);
if (!$journal) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Invalid journal']);
    exit;
}

$entry = get_entry($db, $entryId, $journalId);
if (!$entry) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Entry not found']);
    exit;
}

if ($title === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Entry title is required']);
    exit;
}

if ($entryDate === '' || !DateTimeImmutable::createFromFormat('Y-m-d', $entryDate)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Entry date is invalid']);
    exit;
}

update_entry($db, $entryId, $journalId, $title, $content, $entryDate);

echo json_encode([
    'ok' => true,
    'message' => 'Saved',
]);
