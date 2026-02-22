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
    $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, nav_color) VALUES (:name, :email, :password_hash, :nav_color)');
    $stmt->execute([
        'name' => trim($name),
        'email' => mb_strtolower(trim($email)),
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'nav_color' => '#1e1f23',
    ]);

    return (int) $db->lastInsertId();
}

function get_user(PDO $db, int $userId): ?array
{
    $stmt = $db->prepare('SELECT id, name, email, nav_color FROM users WHERE id = :id LIMIT 1');
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
    $stmt = $db->prepare('INSERT INTO journals (user_id, title, bg_color, accent_color, sort_order) VALUES (:user_id, :title, :bg_color, :accent_color, :sort_order)');
    $stmt->execute([
        'user_id' => $userId,
        'title' => trim($title),
        'bg_color' => '#2f79bb',
        'accent_color' => '#2f79bb',
        'sort_order' => 'updated_desc',
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

function list_entries(PDO $db, int $journalId, string $sortOrder = 'updated_desc'): array
{
    $allowed = [
        'created_desc' => 'created_at DESC',
        'created_asc' => 'created_at ASC',
        'updated_desc' => 'updated_at DESC',
        'updated_asc' => 'updated_at ASC',
    ];
    $order = $allowed[$sortOrder] ?? $allowed['updated_desc'];

    $stmt = $db->prepare('SELECT * FROM entries WHERE journal_id = :journal_id ORDER BY ' . $order);
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

function update_journal_title(PDO $db, int $journalId, int $userId, string $title): void
{
    $stmt = $db->prepare(
        'UPDATE journals
         SET title = :title,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        'title' => trim($title),
        'id' => $journalId,
        'user_id' => $userId,
    ]);
}

function update_journal_settings(PDO $db, int $journalId, int $userId, string $bgColor, string $sortOrder): void
{
    $allowedSort = ['created_desc', 'created_asc', 'updated_desc', 'updated_asc'];
    $safeSort = in_array($sortOrder, $allowedSort, true) ? $sortOrder : 'updated_desc';
    $safeColor = preg_match('/^#[0-9a-fA-F]{6}$/', $bgColor) ? $bgColor : '#2f79bb';

    $stmt = $db->prepare(
        'UPDATE journals
         SET bg_color = :bg_color,
             accent_color = :accent_color,
             sort_order = :sort_order,
             updated_at = CURRENT_TIMESTAMP
         WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        'bg_color' => $safeColor,
        'accent_color' => $safeColor,
        'sort_order' => $safeSort,
        'id' => $journalId,
        'user_id' => $userId,
    ]);
}

function delete_journal(PDO $db, int $journalId, int $userId): void
{
    $stmt = $db->prepare('DELETE FROM journals WHERE id = :id AND user_id = :user_id');
    $stmt->execute([
        'id' => $journalId,
        'user_id' => $userId,
    ]);
}

function update_user_nav_color(PDO $db, int $userId, string $navColor): void
{
    $safeColor = preg_match('/^#[0-9a-fA-F]{6}$/', $navColor) ? $navColor : '#1e1f23';
    $stmt = $db->prepare('UPDATE users SET nav_color = :nav_color WHERE id = :id');
    $stmt->execute([
        'nav_color' => $safeColor,
        'id' => $userId,
    ]);
}
