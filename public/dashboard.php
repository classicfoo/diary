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
        <button type="submit" class="mobile-icon-btn" form="mobile-logout-form-dashboard" title="Sign out">☰</button>
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

<form method="post" action="/journals_create.php" class="d-none" id="mobile-new-journal-form">
    <?= csrf_input() ?>
    <input type="hidden" name="title" value="">
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
                        <p class="text-white-50 small mb-3">Updated <time data-utc-datetime="<?= e((string) $journal['updated_at']) ?>"><?= e((string) $journal['updated_at']) ?></time></p>
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
                        <input type="hidden" name="bg_color" id="settings-bg-color" value="#2f79bb">
                        <div class="journal-color-picker" id="journal-color-picker" role="group" aria-label="Journal background color">
                            <button type="button" class="journal-color-swatch" data-color="#2f79bb" style="--swatch:#2f79bb" aria-label="Blue"></button>
                            <button type="button" class="journal-color-swatch" data-color="#1f9d8f" style="--swatch:#1f9d8f" aria-label="Teal"></button>
                            <button type="button" class="journal-color-swatch" data-color="#355c7d" style="--swatch:#355c7d" aria-label="Slate blue"></button>
                            <button type="button" class="journal-color-swatch" data-color="#6c5ce7" style="--swatch:#6c5ce7" aria-label="Indigo"></button>
                            <button type="button" class="journal-color-swatch" data-color="#7a4e2d" style="--swatch:#7a4e2d" aria-label="Brown"></button>
                            <button type="button" class="journal-color-swatch" data-color="#cc5a71" style="--swatch:#cc5a71" aria-label="Rose"></button>
                            <button type="button" class="journal-color-swatch" data-color="#2d6a4f" style="--swatch:#2d6a4f" aria-label="Forest"></button>
                            <button type="button" class="journal-color-swatch" data-color="#283044" style="--swatch:#283044" aria-label="Navy gray"></button>
                            <button type="button" class="journal-color-swatch" data-color="#264653" style="--swatch:#264653" aria-label="Deep teal"></button>
                            <button type="button" class="journal-color-swatch" data-color="#2a9d8f" style="--swatch:#2a9d8f" aria-label="Mint teal"></button>
                            <button type="button" class="journal-color-swatch" data-color="#457b9d" style="--swatch:#457b9d" aria-label="Sky slate"></button>
                            <button type="button" class="journal-color-swatch" data-color="#1d3557" style="--swatch:#1d3557" aria-label="Midnight blue"></button>
                            <button type="button" class="journal-color-swatch" data-color="#4c6ef5" style="--swatch:#4c6ef5" aria-label="Royal blue"></button>
                            <button type="button" class="journal-color-swatch" data-color="#3b5bdb" style="--swatch:#3b5bdb" aria-label="Cobalt"></button>
                            <button type="button" class="journal-color-swatch" data-color="#5f3dc4" style="--swatch:#5f3dc4" aria-label="Deep violet"></button>
                            <button type="button" class="journal-color-swatch" data-color="#7048e8" style="--swatch:#7048e8" aria-label="Violet"></button>
                            <button type="button" class="journal-color-swatch" data-color="#7b2cbf" style="--swatch:#7b2cbf" aria-label="Plum"></button>
                            <button type="button" class="journal-color-swatch" data-color="#9d4edd" style="--swatch:#9d4edd" aria-label="Purple"></button>
                            <button type="button" class="journal-color-swatch" data-color="#b5179e" style="--swatch:#b5179e" aria-label="Magenta"></button>
                            <button type="button" class="journal-color-swatch" data-color="#d6336c" style="--swatch:#d6336c" aria-label="Pink"></button>
                            <button type="button" class="journal-color-swatch" data-color="#c2255c" style="--swatch:#c2255c" aria-label="Raspberry"></button>
                            <button type="button" class="journal-color-swatch" data-color="#e76f51" style="--swatch:#e76f51" aria-label="Coral"></button>
                            <button type="button" class="journal-color-swatch" data-color="#f4a261" style="--swatch:#f4a261" aria-label="Orange"></button>
                            <button type="button" class="journal-color-swatch" data-color="#f77f00" style="--swatch:#f77f00" aria-label="Amber"></button>
                            <button type="button" class="journal-color-swatch" data-color="#bc6c25" style="--swatch:#bc6c25" aria-label="Ochre"></button>
                            <button type="button" class="journal-color-swatch" data-color="#8d5524" style="--swatch:#8d5524" aria-label="Umber"></button>
                            <button type="button" class="journal-color-swatch" data-color="#606c38" style="--swatch:#606c38" aria-label="Olive"></button>
                            <button type="button" class="journal-color-swatch" data-color="#588157" style="--swatch:#588157" aria-label="Sage"></button>
                            <button type="button" class="journal-color-swatch" data-color="#2b9348" style="--swatch:#2b9348" aria-label="Green"></button>
                            <button type="button" class="journal-color-swatch" data-color="#386641" style="--swatch:#386641" aria-label="Pine"></button>
                            <button type="button" class="journal-color-swatch" data-color="#495057" style="--swatch:#495057" aria-label="Gray"></button>
                            <button type="button" class="journal-color-swatch" data-color="#343a40" style="--swatch:#343a40" aria-label="Charcoal"></button>
                        </div>
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
            const title = window.prompt('Journal title:', 'Untitled Journal');
            if (!title || title.trim() === '') return;
            titleInput.value = title.trim();
            mobileNewJournalForm.submit();
        });
    }

    if (mobileNewJournalForm && titleInput) {
        mobileNewJournalForm.addEventListener('submit', (event) => {
            const existing = titleInput.value.trim();
            if (existing !== '') return;

            const title = window.prompt('Journal title:', 'Untitled Journal');
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

    const settingsColorInput = document.getElementById('settings-bg-color');
    const colorSwatches = Array.from(document.querySelectorAll('.journal-color-swatch'));

    const normalizeHex = (value) => {
        const v = (value || '').trim();
        return /^#[0-9a-fA-F]{6}$/.test(v) ? v.toLowerCase() : '#2f79bb';
    };

    const selectColor = (value) => {
        const selected = normalizeHex(value);
        settingsColorInput.value = selected;
        colorSwatches.forEach((swatch) => {
            const isActive = normalizeHex(swatch.dataset.color) === selected;
            swatch.classList.toggle('active', isActive);
            swatch.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    };

    colorSwatches.forEach((swatch) => {
        swatch.addEventListener('click', () => {
            selectColor(swatch.dataset.color || '#2f79bb');
        });
    });

    settingsModal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const id = trigger.dataset.journalId || '';
        const title = trigger.dataset.journalTitle || '';
        const bgColor = trigger.dataset.journalBgColor || '#2f79bb';
        const sortOrder = trigger.dataset.journalSortOrder || 'updated_desc';

        document.getElementById('settings-journal-id').value = id;
        document.getElementById('settings-journal-title').value = title;
        selectColor(bgColor);
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
