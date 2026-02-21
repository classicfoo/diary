<?php

declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';

if (is_authenticated()) {
    redirect('/dashboard.php');
}

if (is_post()) {
    validate_csrf();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['password_confirmation'] ?? '');

    set_old_input(['name' => $name, 'email' => $email]);

    if ($name === '' || $email === '' || $password === '') {
        flash('danger', 'Name, email, and password are required.');
        redirect('/signup.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('danger', 'Enter a valid email address.');
        redirect('/signup.php');
    }

    if (strlen($password) < 8) {
        flash('danger', 'Password must be at least 8 characters.');
        redirect('/signup.php');
    }

    if ($password !== $confirmPassword) {
        flash('danger', 'Password confirmation does not match.');
        redirect('/signup.php');
    }

    if (find_user_by_email($db, $email)) {
        flash('danger', 'Email is already registered.');
        redirect('/signup.php');
    }

    $userId = create_user($db, $name, $email, $password);
    clear_old_input();
    login_user($userId);
    $_SESSION['app_nav_color'] = '#1e1f23';
    flash('success', 'Your account is ready.');
    redirect('/dashboard.php');
}

$pageTitle = 'Sign up';
require __DIR__ . '/../src/views/header.php';
?>
<div class="row justify-content-center auth-wrap">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm auth-card">
            <div class="card-header text-center fs-3">Create account</div>
            <div class="card-body p-4">
                <form method="post" action="/signup.php" class="vstack gap-3">
                    <?= csrf_input() ?>
                    <div>
                        <label class="form-label text-uppercase small fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control form-control-lg" required value="<?= e(old_input('name')) ?>">
                    </div>
                    <div>
                        <label class="form-label text-uppercase small fw-semibold">Email address</label>
                        <input type="email" name="email" class="form-control form-control-lg" required value="<?= e(old_input('email')) ?>">
                    </div>
                    <div>
                        <label class="form-label text-uppercase small fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required>
                    </div>
                    <div>
                        <label class="form-label text-uppercase small fw-semibold">Confirm password</label>
                        <input type="password" name="password_confirmation" class="form-control form-control-lg" required>
                    </div>
                    <button class="btn btn-primary btn-lg" type="submit">Create Account</button>
                </form>
                <p class="text-center mt-3 mb-0">Already have an account? <a href="/login.php">Sign in</a></p>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../src/views/footer.php'; ?>
