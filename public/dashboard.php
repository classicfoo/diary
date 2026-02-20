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
        <span class="icon-dot">+</span>
        <span class="icon-dot">⋮</span>
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
                <div class="card journal-card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h3 journal-title mb-3"><?= e((string) $journal['title']) ?></h2>
                        <p class="text-muted small mb-4">Updated <?= e((string) $journal['updated_at']) ?></p>
                        <a href="/journal.php?id=<?= (int) $journal['id'] ?>" class="btn btn-light border mt-auto">Open Journal</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mobile-journal-grid mobile-only">
        <?php foreach ($journals as $journal): ?>
            <a href="/journal.php?id=<?= (int) $journal['id'] ?>" class="mobile-journal-book">
                <div class="mobile-journal-panel">
                    <h2><?= e((string) $journal['title']) ?></h2>
                    <div class="mobile-journal-actions">
                        <span>✎</span>
                        <span>🔒</span>
                        <span>⚙</span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<script>
(() => {
    const form = document.getElementById('mobile-new-journal-form');
    if (!form) return;
    const titleInput = form.querySelector('input[name=\"title\"]');

    form.addEventListener('submit', (event) => {
        const existing = titleInput.value.trim();
        if (existing !== '') return;

        const title = window.prompt('Journal title:', \"Michael's Journal\");
        if (!title || title.trim() === '') {
            event.preventDefault();
            return;
        }

        titleInput.value = title.trim();
    });
})();
</script>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
