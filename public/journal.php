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

$entries = list_entries($db, $journalId, (string) ($journal['sort_order'] ?? 'updated_desc'));
foreach ($entries as &$entry) {
    $entry['title'] = decode_legacy_entities((string) ($entry['title'] ?? ''));
    $entry['content'] = decode_legacy_entities((string) ($entry['content'] ?? ''));
}
unset($entry);
$activeEntryId = isset($_GET['entry']) ? (int) $_GET['entry'] : (isset($entries[0]['id']) ? (int) $entries[0]['id'] : 0);
$activeEntry = $activeEntryId > 0 ? get_entry($db, $activeEntryId, $journalId) : null;
if ($activeEntry) {
    $activeEntry['title'] = decode_legacy_entities((string) ($activeEntry['title'] ?? ''));
    $activeEntry['content'] = decode_legacy_entities((string) ($activeEntry['content'] ?? ''));
}
$mobileView = (string) ($_GET['view'] ?? 'list');
$isMobileEdit = $mobileView === 'edit' && $activeEntry;

$pageTitle = (string) $journal['title'];
$pageClass = 'page-journal';
$appNavColor = (string) ($journal['bg_color'] ?? '#1e1f23');
$journalAccentColor = (string) ($journal['bg_color'] ?? '#2f79bb');
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $journalAccentColor)) {
    $journalAccentColor = '#2f79bb';
}
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
        <form method="post" action="/entries_create.php" id="mobile-create-entry-form">
            <?= csrf_input() ?>
            <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
            <input type="hidden" name="entry_date" value="" data-fill-local-date="true">
        </form>
        <form method="post" action="/logout.php" id="mobile-logout-form">
            <?= csrf_input() ?>
        </form>
        <?php if ($isMobileEdit && $activeEntry): ?>
        <form method="post" action="/entries_delete.php" id="mobile-delete-entry-form">
            <?= csrf_input() ?>
            <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
            <input type="hidden" name="entry_id" value="<?= (int) $activeEntry['id'] ?>">
        </form>
        <?php endif; ?>
        <?php if ($isMobileEdit): ?>
            <button type="submit" form="mobile-create-entry-form" class="mobile-icon-btn" title="New entry">＋</button>
        <?php else: ?>
            <button type="button" id="mobile-search-toggle" class="mobile-icon-btn" title="Search entries">⌕</button>
            <button type="submit" form="mobile-create-entry-form" class="mobile-icon-btn" title="New entry">＋</button>
        <?php endif; ?>
        <div class="dropdown">
            <button class="mobile-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Menu">☰</button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="/dashboard.php">Journals</a></li>
                <?php if ($isMobileEdit): ?>
                    <li><a class="dropdown-item" href="/journal.php?id=<?= (int) $journalId ?>">All Entries</a></li>
                <?php endif; ?>
                <li><button class="dropdown-item" type="submit" form="mobile-create-entry-form">New Entry</button></li>
                <?php if ($isMobileEdit && $activeEntry): ?>
                    <li><button class="dropdown-item text-danger" type="button" id="mobile-delete-entry-btn">Delete Entry</button></li>
                <?php endif; ?>
                <li><button class="dropdown-item text-danger" type="submit" form="mobile-logout-form">Sign out</button></li>
            </ul>
        </div>
    </div>
</div>

<div class="journal-workspace <?= $isMobileEdit ? 'mobile-mode-edit' : 'mobile-mode-list' ?>" style="--journal-accent: <?= e($journalAccentColor) ?>;">
    <aside class="journal-sidebar">
        <div class="sidebar-head">
            <h2 class="h5 mb-0"><?= e((string) $journal['title']) ?></h2>
        </div>
        <form method="post" action="/entries_create.php" class="p-3 border-bottom">
            <?= csrf_input() ?>
            <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
            <input type="hidden" name="entry_date" value="" data-fill-local-date="true">
            <button class="btn btn-primary w-100 mobile-new-entry-btn" type="submit">New Entry</button>
        </form>
        <div class="entry-search-wrap" id="entry-search-wrap">
            <input type="search" id="entry-search" class="form-control form-control-sm" placeholder="Search entries">
        </div>
        <div class="entry-list">
            <?php if (!$entries): ?>
                <p class="text-light opacity-75 small p-3 mb-0">No entries yet.</p>
            <?php endif; ?>
            <?php foreach ($entries as $entry): ?>
                <a class="entry-link <?= $activeEntry && (int) $activeEntry['id'] === (int) $entry['id'] ? 'active' : '' ?>" href="/journal.php?id=<?= (int) $journalId ?>&entry=<?= (int) $entry['id'] ?>&view=edit">
                    <strong><?= e((string) ($entry['title'] !== '' ? $entry['title'] : 'Untitled')) ?></strong>
                    <p><?= e(mb_substr((string) $entry['content'], 0, 90)) ?><?= mb_strlen((string) $entry['content']) > 90 ? '...' : '' ?></p>
                    <span data-local-date="<?= e((string) $entry['entry_date']) ?>"><?= e(format_entry_date((string) $entry['entry_date'])) ?></span>
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
                    <div class="mobile-date-strip mobile-only" id="mobile-active-entry-date" data-local-date="<?= e((string) $activeEntry['entry_date']) ?>"><?= e(format_entry_date((string) $activeEntry['entry_date'])) ?></div>
                    <form method="post" action="/entries_autosave.php" class="vstack gap-3" id="autosave-form">
                        <?= csrf_input() ?>
                        <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
                        <input type="hidden" name="entry_id" value="<?= (int) $activeEntry['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 editor-title-row">
                            <h3 class="h4 m-0">Edit Entry</h3>
                            <div class="d-flex align-items-center gap-2">
                                <span id="autosave-status" class="autosave-status autosave-idle">Autosave ready</span>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm border" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Entry actions">⋮</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button class="dropdown-item text-danger" type="submit" form="desktop-delete-entry-form" onclick="return confirm('Delete this entry?');">Delete Entry</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label">Entry title</label>
                                <input type="hidden" name="title" id="entry-title-input" value="<?= e((string) $activeEntry['title']) ?>">
                                <div id="entry-title-edit" class="form-control form-control-lg entry-title-input" contenteditable="true" role="textbox" aria-label="Entry title"><?= e((string) $activeEntry['title']) ?></div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="entry_date" id="entry-date-input" class="form-control form-control-lg" required value="<?= e((string) $activeEntry['entry_date']) ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Your entry</label>
                            <div class="prism-editor diary-editor" id="entry-editor">
                                <textarea name="content" id="entry-content-input" class="prism-editor__textarea editor-content" placeholder="Your entry here..."><?= e((string) $activeEntry['content']) ?></textarea>
                                <pre class="prism-editor__preview"><code id="entry-content-preview"></code></pre>
                            </div>
                        </div>
                    </form>
                    <form method="post" action="/entries_delete.php" id="desktop-delete-entry-form" class="d-none">
                        <?= csrf_input() ?>
                        <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
                        <input type="hidden" name="entry_id" value="<?= (int) $activeEntry['id'] ?>">
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php if ($activeEntry): ?>
<script src="/assets/journal-editor.js"></script>
<script>
(() => {
    const form = document.getElementById('autosave-form');
    if (!form) return;

    const statusEl = document.getElementById('autosave-status');
    const titleInput = document.getElementById('entry-title-input');
    const titleEditable = document.getElementById('entry-title-edit');
    const dateInput = document.getElementById('entry-date-input');
    const contentInput = document.getElementById('entry-content-input');
    const activeLink = document.querySelector('.entry-link.active');
    const activeTitle = activeLink ? activeLink.querySelector('strong') : null;
    const activeDate = activeLink ? activeLink.querySelector('span') : null;
    const mobileActiveDate = document.getElementById('mobile-active-entry-date');
    const endpoint = form.getAttribute('action');
    const editorEl = document.getElementById('entry-editor');

    let timer = null;
    let saving = false;
    let pending = false;
    const toTitleCase = (value) => value
        .toLowerCase()
        .replace(/\b([a-z])/g, (match) => match.toUpperCase());
    const normalizeTitle = () => {
        const raw = (titleEditable.textContent || '').replace(/\s+/g, ' ').trim();
        return raw ? toTitleCase(raw) : '';
    };
    const buildState = () => JSON.stringify({
        title: normalizeTitle(),
        entry_date: dateInput.value.trim(),
        content: contentInput.value
    });
    let lastSavedState = buildState();

    const setStatus = (text, mode) => {
        statusEl.textContent = text;
        statusEl.classList.remove('autosave-idle', 'autosave-saving', 'autosave-saved', 'autosave-error');
        statusEl.classList.add(mode);
    };

    const payload = () => {
        const normalizedTitle = normalizeTitle();
        titleInput.value = normalizedTitle;
        const formData = new FormData(form);
        formData.set('title', normalizedTitle);
        formData.set('entry_date', dateInput.value);
        formData.set('content', contentInput.value);
        return formData;
    };

    const save = async () => {
        if (saving) {
            pending = true;
            return;
        }

        const normalizedTitle = normalizeTitle();
        if (dateInput.value.trim() === '') {
            setStatus('Date is required', 'autosave-error');
            return;
        }

        const currentState = buildState();
        if (currentState === lastSavedState) {
            setStatus('Saved', 'autosave-saved');
            return;
        }

        saving = true;
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
            titleInput.value = normalizedTitle;
            titleEditable.textContent = normalizedTitle;
            lastSavedState = buildState();
            if (activeTitle) activeTitle.textContent = normalizedTitle || 'Untitled';
            const pretty = typeof window.diaryFormatLocalDate === 'function'
                ? window.diaryFormatLocalDate(dateInput.value)
                : dateInput.value;
            if (activeDate) {
                activeDate.textContent = pretty;
                activeDate.setAttribute('data-local-date', dateInput.value);
            }
            if (mobileActiveDate) {
                mobileActiveDate.textContent = pretty;
                mobileActiveDate.setAttribute('data-local-date', dateInput.value);
            }
        } catch (error) {
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
        const currentState = buildState();
        if (currentState === lastSavedState) {
            setStatus('Saved', 'autosave-saved');
            if (timer) clearTimeout(timer);
            return;
        }
        setStatus('Unsaved changes', 'autosave-idle');
        if (timer) clearTimeout(timer);
        timer = setTimeout(save, 800);
    };

    ['input', 'change'].forEach((eventName) => {
        dateInput.addEventListener(eventName, scheduleSave);
        contentInput.addEventListener(eventName, scheduleSave);
    });
    titleEditable.addEventListener('input', scheduleSave);
    titleEditable.addEventListener('blur', scheduleSave);

    if (window.initDiaryEntryEditor && editorEl) {
        window.initDiaryEntryEditor(editorEl, scheduleSave);
    }

    form.addEventListener('submit', (event) => {
        event.preventDefault();
    });

    window.addEventListener('beforeunload', (event) => {
        if (!saving && buildState() === lastSavedState) return;
        event.preventDefault();
        event.returnValue = '';
    });
})();
</script>
<?php endif; ?>
<script>
(() => {
    const searchToggle = document.getElementById('mobile-search-toggle');
    const searchWrap = document.getElementById('entry-search-wrap');
    const searchInput = document.getElementById('entry-search');
    const entryLinks = Array.from(document.querySelectorAll('.entry-link'));
    const mobileDeleteBtn = document.getElementById('mobile-delete-entry-btn');
    const mobileDeleteForm = document.getElementById('mobile-delete-entry-form');

    if (searchToggle && searchWrap && searchInput) {
        searchToggle.addEventListener('click', () => {
            searchWrap.classList.toggle('open');
            if (searchWrap.classList.contains('open')) {
                searchInput.focus();
            }
        });

        searchInput.addEventListener('input', () => {
            const query = searchInput.value.trim().toLowerCase();
            entryLinks.forEach((link) => {
                const text = link.textContent.toLowerCase();
                link.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    if (mobileDeleteBtn && mobileDeleteForm) {
        mobileDeleteBtn.addEventListener('click', () => {
            if (!window.confirm('Delete this entry?')) return;
            mobileDeleteForm.submit();
        });
    }
})();
</script>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
