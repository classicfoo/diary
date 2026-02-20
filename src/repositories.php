<?php

declare(strict_types=1);

function find_user_by_email(PDO $db, string $email): ?array
{
    $stmt = $db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => mb_strtolower(trim($email))]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function create_user(PDO $db, string $name, string $email, string $password): int
{
    $stmt = $db->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
    $stmt->execute([
        'name' => trim($name),
        'email' => mb_strtolower(trim($email)),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    return (int) $db->lastInsertId();
}

function get_user(PDO $db, int $userId): ?array
{
    $stmt = $db->prepare('SELECT id, name, email FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function list_journals(PDO $db, int $userId): array
{
    $stmt = $db->prepare('SELECT * FROM journals WHERE user_id = :user_id ORDER BY updated_at DESC');
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetchAll();
}

function create_journal(PDO $db, int $userId, string $title): int
{
    $stmt = $db->prepare('INSERT INTO journals (user_id, title) VALUES (:user_id, :title)');
    $stmt->execute([
        'user_id' => $userId,
        'title' => trim($title),
    ]);

    return (int) $db->lastInsertId();
}

function get_journal(PDO $db, int $journalId, int $userId): ?array
{
    $stmt = $db->prepare('SELECT * FROM journals WHERE id = :id AND user_id = :user_id LIMIT 1');
    $stmt->execute(['id' => $journalId, 'user_id' => $userId]);
    $journal = $stmt->fetch();

    return $journal ?: null;
}

function list_entries(PDO $db, int $journalId): array
{
    $stmt = $db->prepare('SELECT * FROM entries WHERE journal_id = :journal_id ORDER BY entry_date DESC, updated_at DESC');
    $stmt->execute(['journal_id' => $journalId]);

    return $stmt->fetchAll();
}

function get_entry(PDO $db, int $entryId, int $journalId): ?array
{
    $stmt = $db->prepare('SELECT * FROM entries WHERE id = :id AND journal_id = :journal_id LIMIT 1');
    $stmt->execute(['id' => $entryId, 'journal_id' => $journalId]);
    $entry = $stmt->fetch();

    return $entry ?: null;
}

function create_entry(PDO $db, int $journalId, string $title, string $content, string $entryDate): int
{
    $stmt = $db->prepare('INSERT INTO entries (journal_id, title, content, entry_date) VALUES (:journal_id, :title, :content, :entry_date)');
    $stmt->execute([
        'journal_id' => $journalId,
        'title' => trim($title),
        'content' => trim($content),
        'entry_date' => $entryDate,
    ]);

    $entryId = (int) $db->lastInsertId();
    touch_journal($db, $journalId);
    return $entryId;
}

function update_entry(PDO $db, int $entryId, int $journalId, string $title, string $content, string $entryDate): void
{
    $stmt = $db->prepare(
        'UPDATE entries
         SET title = :title,
             content = :content,
             entry_date = :entry_date,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id AND journal_id = :journal_id'
    );

    $stmt->execute([
        'title' => trim($title),
        'content' => trim($content),
        'entry_date' => $entryDate,
        'id' => $entryId,
        'journal_id' => $journalId,
    ]);

    touch_journal($db, $journalId);
}

function delete_entry(PDO $db, int $entryId, int $journalId): void
{
    $stmt = $db->prepare('DELETE FROM entries WHERE id = :id AND journal_id = :journal_id');
    $stmt->execute(['id' => $entryId, 'journal_id' => $journalId]);

    touch_journal($db, $journalId);
}

function touch_journal(PDO $db, int $journalId): void
{
    $stmt = $db->prepare('UPDATE journals SET updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $stmt->execute(['id' => $journalId]);
}
