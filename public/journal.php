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

$pageTitle = (string) $journal['title'];
require __DIR__ . '/../src/views/header.php';
?>
<div class="journal-workspace">
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
                <a class="entry-link <?= $activeEntry && (int) $activeEntry['id'] === (int) $entry['id'] ? 'active' : '' ?>" href="/journal.php?id=<?= (int) $journalId ?>&entry=<?= (int) $entry['id'] ?>">
                    <strong><?= e((string) $entry['title']) ?></strong>
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
                    <form method="post" action="/entries_update.php" class="vstack gap-3">
                        <?= csrf_input() ?>
                        <input type="hidden" name="journal_id" value="<?= (int) $journalId ?>">
                        <input type="hidden" name="entry_id" value="<?= (int) $activeEntry['id'] ?>">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <h3 class="h4 m-0">Edit Entry</h3>
                            <button class="btn btn-primary" type="submit">Save now</button>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label class="form-label">Entry title</label>
                                <input type="text" name="title" class="form-control form-control-lg" required value="<?= e((string) $activeEntry['title']) ?>">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" name="entry_date" class="form-control form-control-lg" required value="<?= e((string) $activeEntry['entry_date']) ?>">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Your entry</label>
                            <textarea name="content" class="form-control editor-content" required><?= e((string) $activeEntry['content']) ?></textarea>
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
<?php require __DIR__ . '/../src/views/footer.php'; ?>
