<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_auth();

$user = get_user($db, current_user_id());
$journals = list_journals($db, (int) $user['id']);

$pageTitle = 'Dashboard';
$pageClass = 'page-dashboard';
require __DIR__ . '/../src/views/header.php';
?>
<div class="mobile-page-header mobile-only">
    <h1>Journals</h1>
    <div class="mobile-icons">
        <span class="pill">PRO</span>
        <button type="button" class="mobile-icon-btn" id="mobile-new-journal-btn" title="New journal">＋</button>
        <form method="post" action="/logout.php" id="mobile-logout-form-dashboard" class="m-0">
            <?= csrf_input() ?>
        </form>
        <button type="submit" class="mobile-icon-btn" form="mobile-logout-form-dashboard" title="Sign out">⋮</button>
    </div>
</div>

<div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4 desktop-section">
    <h1 class="h4 m-0">Displaying <?= count($journals) ?> journal<?= count($journals) === 1 ? '' : 's' ?></h1>
    <form method="post" action="/journals_create.php" class="d-flex gap-2 dashboard-create-form">
        <?= csrf_input() ?>
        <input type="text" name="title" class="form-control" placeholder="New journal title" required>
        <button class="btn btn-primary" type="submit">New Journal</button>
    </form>
</div>

<form method="post" action="/journals_create.php" class="mobile-new-journal mobile-only" id="mobile-new-journal-form">
    <?= csrf_input() ?>
    <input type="text" name="title" class="form-control" placeholder="New journal" required>
    <button class="btn btn-primary" type="submit">+ New Journal</button>
</form>

<form method="post" action="/journals_update.php" id="rename-journal-form" class="d-none">
    <?= csrf_input() ?>
    <input type="hidden" name="action" value="rename">
    <input type="hidden" name="journal_id" id="rename-journal-id" value="">
    <input type="hidden" name="title" id="rename-journal-title" value="">
</form>
<form method="post" action="/journals_update.php" id="delete-journal-form" class="d-none">
    <?= csrf_input() ?>
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="journal_id" id="delete-journal-id" value="">
</form>

<?php if (!$journals): ?>
    <div class="card dashboard-empty shadow-sm">
        <div class="card-body p-4">
            <h2 class="h5">Start your first journal</h2>
            <p class="mb-0 text-muted">Create a journal title above to begin writing entries.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row g-4 desktop-section">
        <?php foreach ($journals as $journal): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card journal-card shadow-sm h-100" style="--journal-bg: <?= e((string) ($journal['bg_color'] ?? '#2f79bb')) ?>">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h3 journal-title mb-3"><?= e((string) $journal['title']) ?></h2>
                        <p class="text-white-50 small mb-3">Updated <?= e((string) $journal['updated_at']) ?></p>
                        <div class="journal-actions mt-auto d-flex flex-wrap gap-2">
                            <a href="/journal.php?id=<?= (int) $journal['id'] ?>" class="btn btn-light border btn-sm">Open</a>
                            <button type="button" class="btn btn-light border btn-sm rename-journal-btn" data-journal-id="<?= (int) $journal['id'] ?>" data-journal-title="<?= e((string) $journal['title']) ?>">✎ Edit</button>
                            <button type="button" class="btn btn-light border btn-sm lock-journal-btn" disabled title="Coming soon">🔒 Lock</button>
                            <button
                                type="button"
                                class="btn btn-light border btn-sm settings-journal-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#journalSettingsModal"
                                data-journal-id="<?= (int) $journal['id'] ?>"
                                data-journal-title="<?= e((string) $journal['title']) ?>"
                                data-journal-bg-color="<?= e((string) ($journal['bg_color'] ?? '#2f79bb')) ?>"
                                data-journal-sort-order="<?= e((string) ($journal['sort_order'] ?? 'updated_desc')) ?>"
                            >⚙ Settings</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mobile-journal-grid mobile-only">
        <?php foreach ($journals as $journal): ?>
            <div class="mobile-journal-book" style="--journal-bg: <?= e((string) ($journal['bg_color'] ?? '#2f79bb')) ?>">
                <div class="mobile-journal-panel">
                    <h2><a href="/journal.php?id=<?= (int) $journal['id'] ?>" class="mobile-journal-title-link"><?= e((string) $journal['title']) ?></a></h2>
                    <div class="mobile-journal-actions">
                        <button type="button" class="rename-journal-btn" data-journal-id="<?= (int) $journal['id'] ?>" data-journal-title="<?= e((string) $journal['title']) ?>" aria-label="Edit journal">✎</button>
                        <button type="button" class="lock-journal-btn" disabled aria-label="Lock journal" title="Coming soon">🔒</button>
                        <button
                            type="button"
                            class="settings-journal-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#journalSettingsModal"
                            data-journal-id="<?= (int) $journal['id'] ?>"
                            data-journal-title="<?= e((string) $journal['title']) ?>"
                            data-journal-bg-color="<?= e((string) ($journal['bg_color'] ?? '#2f79bb')) ?>"
                            data-journal-sort-order="<?= e((string) ($journal['sort_order'] ?? 'updated_desc')) ?>"
                            aria-label="Journal settings"
                        >⚙</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal fade" id="journalSettingsModal" tabindex="-1" aria-labelledby="journalSettingsLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/journals_update.php" id="journal-settings-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="journalSettingsLabel">Journal Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="settings">
                    <input type="hidden" name="journal_id" id="settings-journal-id" value="">

                    <div class="mb-3">
                        <label class="form-label">Journal name</label>
                        <input type="text" id="settings-journal-title" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Background color</label>
                        <input type="color" name="bg_color" id="settings-bg-color" class="form-control form-control-color" value="#2f79bb" title="Choose journal background color">
                    </div>

                    <div>
                        <label class="form-label">Entry sort order</label>
                        <select name="sort_order" id="settings-sort-order" class="form-select">
                            <option value="updated_desc">Date modified (newest first)</option>
                            <option value="updated_asc">Date modified (oldest first)</option>
                            <option value="created_desc">Date created (newest first)</option>
                            <option value="created_asc">Date created (oldest first)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger me-auto" id="delete-journal-btn">Delete journal</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const mobileNewJournalBtn = document.getElementById('mobile-new-journal-btn');
    const mobileNewJournalForm = document.getElementById('mobile-new-journal-form');
    const titleInput = mobileNewJournalForm ? mobileNewJournalForm.querySelector('input[name="title"]') : null;

    if (mobileNewJournalBtn && mobileNewJournalForm) {
        mobileNewJournalBtn.addEventListener('click', () => {
            const title = window.prompt('Journal title:', "Michael's Journal");
            if (!title || title.trim() === '') return;
            titleInput.value = title.trim();
            mobileNewJournalForm.submit();
        });
    }

    if (mobileNewJournalForm && titleInput) {
        mobileNewJournalForm.addEventListener('submit', (event) => {
            const existing = titleInput.value.trim();
            if (existing !== '') return;

            const title = window.prompt('Journal title:', "Michael's Journal");
            if (!title || title.trim() === '') {
                event.preventDefault();
                return;
            }

            titleInput.value = title.trim();
        });
    }

    const renameButtons = document.querySelectorAll('.rename-journal-btn');
    const renameForm = document.getElementById('rename-journal-form');
    const renameId = document.getElementById('rename-journal-id');
    const renameTitle = document.getElementById('rename-journal-title');

    renameButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.journalId || '';
            const currentTitle = button.dataset.journalTitle || 'Untitled Journal';
            const nextTitle = window.prompt('Edit journal name:', currentTitle);
            if (!nextTitle || nextTitle.trim() === '') return;
            renameId.value = id;
            renameTitle.value = nextTitle.trim();
            renameForm.submit();
        });
    });

    const lockButtons = document.querySelectorAll('.lock-journal-btn:not([disabled])');
    lockButtons.forEach((button) => {
        button.addEventListener('click', () => {
            window.alert('Lock journal is coming soon.');
        });
    });

    const settingsModal = document.getElementById('journalSettingsModal');
    if (!settingsModal) return;
    const deleteJournalBtn = document.getElementById('delete-journal-btn');
    const deleteJournalForm = document.getElementById('delete-journal-form');
    const deleteJournalId = document.getElementById('delete-journal-id');

    settingsModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const id = trigger.dataset.journalId || '';
        const title = trigger.dataset.journalTitle || '';
        const bgColor = trigger.dataset.journalBgColor || '#2f79bb';
        const sortOrder = trigger.dataset.journalSortOrder || 'updated_desc';

        document.getElementById('settings-journal-id').value = id;
        document.getElementById('settings-journal-title').value = title;
        document.getElementById('settings-bg-color').value = bgColor;
        document.getElementById('settings-sort-order').value = sortOrder;
        deleteJournalId.value = id;
    });

    if (deleteJournalBtn && deleteJournalForm) {
        deleteJournalBtn.addEventListener('click', () => {
            if (!window.confirm('Delete this journal and all its entries?')) return;
            deleteJournalForm.submit();
        });
    }
})();
</script>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
