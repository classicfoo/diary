<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (is_authenticated()) {
    redirect('/dashboard.php');
}

if (is_post()) {
    validate_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    set_old_input(['email' => $email]);

    if ($email === '' || $password === '') {
        flash('danger', 'Email and password are required.');
        redirect('/login.php');
    }

    $user = find_user_by_email($db, $email);
    if (!$user || !password_verify($password, (string) $user['password_hash'])) {
        flash('danger', 'Invalid credentials.');
        redirect('/login.php');
    }

    clear_old_input();
    login_user((int) $user['id']);
    flash('success', 'Welcome back.');
    redirect('/dashboard.php');
}

$pageTitle = 'Sign in';
require __DIR__ . '/../src/views/header.php';
?>
<div class="row justify-content-center auth-wrap">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm auth-card">
            <div class="card-header text-center fs-3">Sign in</div>
            <div class="card-body p-4">
                <form method="post" action="/login.php" class="vstack gap-3">
                    <?= csrf_input() ?>
                    <div>
                        <label class="form-label text-uppercase small fw-semibold">Email address</label>
                        <input type="email" name="email" class="form-control form-control-lg" required value="<?= e(old_input('email')) ?>">
                    </div>
                    <div>
                        <label class="form-label text-uppercase small fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required>
                    </div>
                    <button class="btn btn-primary btn-lg" type="submit">Sign In</button>
                </form>
                <p class="text-center mt-3 mb-0">Don't have an account? <a href="/signup.php">Sign up here</a></p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
