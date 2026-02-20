<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require_auth();

$userId = (int) current_user_id();
$journalId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($journalId < 1) {
    flash('danger', 'Journal not found.');
    redirect('/dashboard.php');
}

$journal = get_journal($db, $journalId, $userId);
if (!$journal) {
    flash('danger', 'You do not have access to this journal.');
    redirect('/dashboard.php');
}

$entries = list_entries($db, $journalId);
$activeEntryId = isset($_GET['entry']) ? (int) $_GET['entry'] : (isset($entries[0]['id']) ? (int) $entries[0]['id'] : 0);
$activeEntry = $activeEntryId > 0 ? get_entry($db, $activeEntryId, $journalId) : null;
$mobileView = (string) ($_GET['view'] ?? 'list');
$isMobileEdit = $mobileView === 'edit' && $activeEntry;

$pageTitle = (string) $journal['title'];
$pageClass = 'page-journal';
require __DIR__ . '/../src/views/header.php';
?>
<div class="mobile-page-header mobile-only">
    <?php if ($isMobileEdit): ?>
        <a href="/journal.php?id=<?= (int) $journalId ?>" class="mobile-back">←</a>
    <?php else: ?>
        <a href="/dashboard.php" class="mobile-back">←</a>
    <?php endif; ?>
    <h1><?= e((string) $journal['title']) ?></h1>
    <div class="mobile-icons">
        <?php if ($isMobileEdit): ?>
            <span class="icon-dot">⇩</span>
            <span class="icon-dot">＋</span>
        <?php else: ?>
            <span class="icon-dot">⌕</span>
            <span class="icon-dot">＋</span>
        <?php endif; ?>
        <span class="icon-dot">⋮</span>
    </div>
</div>

<div class="journal-workspace <?= $isMobileEdit ? 'mobile-mode-edit' : 'mobile-mode-list' ?>">
    <aside class="journal-sidebar">
        <div class="sidebar-head">
            <h2 class="h5 mb-0"><?= e((string) $journal['title']) ?></h2>
        </div>
        <form method="post" action="/entries_create.php" class="p-3 border-bottom">
            <?= csrf_input() ?>
            <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
            <button class="btn btn-primary w-100" type="submit">+ New Entry</button>
        </form>
        <div class="entry-list">
            <?php if (!$entries): ?>
                <p class="text-light opacity-75 small p-3 mb-0">No entries yet.</p>
            <?php endif; ?>
            <?php foreach ($entries as $entry): ?>
                <a class="entry-link <?= $activeEntry && (int) $activeEntry['id'] === (int) $entry['id'] ? 'active' : '' ?>" href="/journal.php?id=<?= (int) $journalId ?>&entry=<?= (int) $entry['id'] ?>&view=edit">
                    <strong><?= e((string) $entry['title']) ?></strong>
                    <p><?= e(mb_substr((string) $entry['content'], 0, 90)) ?><?= mb_strlen((string) $entry['content']) > 90 ? '...' : '' ?></p>
                    <span><?= e((string) $entry['entry_date']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <section class="editor-pane">
        <?php if (!$activeEntry): ?>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="h5">No entry selected</h3>
                    <p class="mb-0 text-muted">Create your first entry using the button on the left.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="mobile-date-strip mobile-only"><?= e((string) $activeEntry['entry_date']) ?></div>
                    <form method="post" action="/entries_autosave.php" class="vstack gap-3" id="autosave-form">
                        <?= csrf_input() ?>
                        <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
                        <input type="hidden" name="entry_id" value="<?= (int) $activeEntry['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 editor-title-row">
                            <h3 class="h4 m-0">Edit Entry</h3>
                            <span id="autosave-status" class="autosave-status autosave-idle">Autosave ready</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label">Entry title</label>
                                <input type="text" name="title" id="entry-title-input" class="form-control form-control-lg" required value="<?= e((string) $activeEntry['title']) ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="entry_date" id="entry-date-input" class="form-control form-control-lg" required value="<?= e((string) $activeEntry['entry_date']) ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Your entry</label>
                            <textarea name="content" id="entry-content-input" class="form-control editor-content"><?= e((string) $activeEntry['content']) ?></textarea>
                        </div>
                    </form>
                    <hr>
                    <form method="post" action="/entries_delete.php" onsubmit="return confirm('Delete this entry?');">
                        <?= csrf_input() ?>
                        <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
                        <input type="hidden" name="entry_id" value="<?= (int) $activeEntry['id'] ?>">
                        <button class="btn btn-outline-danger" type="submit">Delete Entry</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if ($activeEntry): ?>
<script>
(() => {
    const form = document.getElementById('autosave-form');
    if (!form) return;

    const statusEl = document.getElementById('autosave-status');
    const titleInput = document.getElementById('entry-title-input');
    const dateInput = document.getElementById('entry-date-input');
    const contentInput = document.getElementById('entry-content-input');
    const activeLink = document.querySelector('.entry-link.active');
    const activeTitle = activeLink ? activeLink.querySelector('strong') : null;
    const activeDate = activeLink ? activeLink.querySelector('span') : null;
    const endpoint = form.getAttribute('action');

    let timer = null;
    let saving = false;
    let dirty = false;
    let pending = false;

    const setStatus = (text, mode) => {
        statusEl.textContent = text;
        statusEl.classList.remove('autosave-idle', 'autosave-saving', 'autosave-saved', 'autosave-error');
        statusEl.classList.add(mode);
    };

    const payload = () => {
        const formData = new FormData(form);
        formData.set('title', titleInput.value);
        formData.set('entry_date', dateInput.value);
        formData.set('content', contentInput.value);
        return formData;
    };

    const save = async () => {
        if (saving) {
            pending = true;
            return;
        }

        if (titleInput.value.trim() === '' || dateInput.value.trim() === '') {
            setStatus('Title and date are required', 'autosave-error');
            return;
        }

        saving = true;
        dirty = false;
        setStatus('Saving...', 'autosave-saving');

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                body: payload(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'Autosave failed');
            }

            setStatus('Saved', 'autosave-saved');
            if (activeTitle) activeTitle.textContent = titleInput.value.trim() || 'Untitled';
            if (activeDate) activeDate.textContent = dateInput.value;
        } catch (error) {
            dirty = true;
            setStatus(error.message || 'Autosave failed', 'autosave-error');
        } finally {
            saving = false;
            if (pending) {
                pending = false;
                save();
            }
        }
    };

    const scheduleSave = () => {
        dirty = true;
        setStatus('Unsaved changes', 'autosave-idle');
        if (timer) clearTimeout(timer);
        timer = setTimeout(save, 800);
    };

    ['input', 'change'].forEach((eventName) => {
        titleInput.addEventListener(eventName, scheduleSave);
        dateInput.addEventListener(eventName, scheduleSave);
        contentInput.addEventListener(eventName, scheduleSave);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
    });

    window.addEventListener('beforeunload', (event) => {
        if (!dirty && !saving) return;
        event.preventDefault();
        event.returnValue = '';
    });
})();
</script>
<?php endif; ?>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
