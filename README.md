# Journal (PHP + SQLite)

A lightweight diary app inspired by Journal apps.

## Stack
- PHP 8+
- SQLite (PDO)
- Bootstrap 5

## Features
- User signup/login/logout
- Journal creation dashboard
- Journal entry create/edit/delete
- Session auth + CSRF protection
- SQLite persistence

## Run Locally
1. Ensure PHP 8+ is installed.
2. From project root, run:

```bash
php -S localhost:8000 -t public
```

3. Open `http://localhost:8000`.

The app auto-creates `storage/diary.sqlite` and required tables at first run.
