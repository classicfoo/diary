# Product Requirements Document (PRD)

## Product
Journal-style private diary web application built with PHP, SQLite, and Bootstrap.

## Goals
- Let users create private journals and write diary entries.
- Provide a familiar UX inspired by private journal apps: sign in screen, journal cards/dashboard, and split-pane editor.
- Ship as a lightweight local-first app with no external backend dependency.

## Non-Goals
- Real-time collaboration.
- Rich media uploads in v1.
- Mobile native app.

## Target Users
- Individuals who want a simple private diary.
- Users who want multiple journals and chronological entries.

## Core Features (v1)
1. Authentication
- Sign up with name, email, password.
- Login/logout.
- Password hashing using PHP `password_hash`.
- Session-based auth.

2. Journal Management
- Create one or more journals.
- View all journals in a dashboard card grid.
- Open a journal.

3. Entry Management
- Create, view, edit, and delete entries in a journal.
- Entry fields: title, content, entry date.
- Left sidebar list of entries sorted by date.
- Main editor pane for selected entry.

4. Security and Data Integrity
- SQLite with foreign keys.
- Ownership checks on all journal/entry actions.
- CSRF protection on mutating requests.
- Input validation and output escaping.

5. UX / UI
- Bootstrap-based responsive design.
- Visual styling that mirrors provided screenshots:
  - Wood-like background
  - Red top nav
  - Journal card dashboard
  - Split sidebar + editor layout

## Functional Requirements
- Unauthenticated users are redirected to login for protected pages.
- Users can only access their own journals and entries.
- First journal can be created from dashboard.
- Creating a new entry opens it in editor view.
- Save action confirms persistence and updates list ordering.

## Data Model
- `users(id, name, email, password_hash, created_at)`
- `journals(id, user_id, title, created_at, updated_at)`
- `entries(id, journal_id, title, content, entry_date, created_at, updated_at)`

## Routes (v1)
- `GET /` -> redirect based on auth state
- `GET /login`, `POST /login`
- `GET /signup`, `POST /signup`
- `POST /logout`
- `GET /dashboard`
- `POST /journals`
- `GET /journal.php?id={journal_id}`
- `POST /entries/create`
- `POST /entries/update`
- `POST /entries/delete`

## Success Criteria
- User can sign up, log in, create a journal, create/edit/delete entries, and log out.
- Data persists in SQLite across app restarts.
- Pages render cleanly in desktop and mobile layouts.

## Tech Stack
- PHP 8+
- SQLite3 (PDO)
- Bootstrap 5 (CDN)
- Vanilla JS for lightweight interactivity

## Risks
- Rich text editing complexity: use textarea in v1 to keep implementation robust.
- Session handling misconfiguration: use secure defaults and clear auth helpers.
