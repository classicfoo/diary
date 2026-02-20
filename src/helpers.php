<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function consume_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function old_input(string $key, string $default = ''): string
{
    if (!isset($_SESSION['old'][$key])) {
        return $default;
    }

    return (string) $_SESSION['old'][$key];
}

function set_old_input(array $data): void
{
    $_SESSION['old'] = $data;
}

function clear_old_input(): void
{
    unset($_SESSION['old']);
}

function now_date(): string
{
    return (new DateTimeImmutable('now'))->format('Y-m-d');
}

function format_entry_date(string $date): string
{
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);
    if (!$parsed) {
        return $date;
    }

    return $parsed->format('j F Y');
}
