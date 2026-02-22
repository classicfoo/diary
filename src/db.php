<?php

declare(strict_types=1);

function db_connect(string $dbPath): PDO
{
    $dir = dirname($dbPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');

    return $pdo;
}

function initialize_database(PDO $db): void
{
    $db->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            nav_color TEXT NOT NULL DEFAULT "#1e1f23",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS journals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            bg_color TEXT NOT NULL DEFAULT "#2f79bb",
            accent_color TEXT NOT NULL DEFAULT "#2f79bb",
            sort_order TEXT NOT NULL DEFAULT "updated_desc",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            journal_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            content TEXT NOT NULL DEFAULT "",
            entry_date TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (journal_id) REFERENCES journals(id) ON DELETE CASCADE
        )'
    );

    ensure_journals_columns($db);
    ensure_users_columns($db);
}

function ensure_journals_columns(PDO $db): void
{
    $columns = $db->query('PRAGMA table_info(journals)')->fetchAll();
    $names = [];
    foreach ($columns as $column) {
        $names[] = (string) ($column['name'] ?? '');
    }

    if (!in_array('bg_color', $names, true)) {
        $db->exec('ALTER TABLE journals ADD COLUMN bg_color TEXT NOT NULL DEFAULT "#2f79bb"');
    }

    if (!in_array('sort_order', $names, true)) {
        $db->exec('ALTER TABLE journals ADD COLUMN sort_order TEXT NOT NULL DEFAULT "updated_desc"');
    }

    if (!in_array('accent_color', $names, true)) {
        $db->exec('ALTER TABLE journals ADD COLUMN accent_color TEXT NOT NULL DEFAULT "#2f79bb"');
    }
}

function ensure_users_columns(PDO $db): void
{
    $columns = $db->query('PRAGMA table_info(users)')->fetchAll();
    $names = [];
    foreach ($columns as $column) {
        $names[] = (string) ($column['name'] ?? '');
    }

    if (!in_array('nav_color', $names, true)) {
        $db->exec('ALTER TABLE users ADD COLUMN nav_color TEXT NOT NULL DEFAULT "#1e1f23"');
    }
}
