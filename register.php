<?php
require __DIR__ . '/config.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$role = ($_POST['role'] ?? 'admin') === 'employee' ? 'employee' : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $userCode = trim($_POST['user_code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Required fields + format checks
    if ($name === '') {
        $errors['full_name'] = 'Full name is required.';
    } elseif (!preg_match("/^[\p{L} .'-]{2,60}$/u", $name)) {
        $errors['full_name'] = 'Name may only contain letters, spaces, . \' and - (2-60 characters).';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($userCode === '') {
        $errors['user_code'] = ucfirst($role) . ' ID is required.';
    } elseif (!preg_match('/^[A-Za-z0-9-]{3,20}$/', $userCode)) {
        $errors['user_code'] = 'ID must be 3-20 letters, numbers or dashes.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Password needs an uppercase letter, a lowercase letter and a number.';
    }

    if ($confirm === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    // Duplicate checks
    if (!isset($errors['email'])) {
        $stmt = $db->prepare('SELECT 1 FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'That email is already registered.';
        }
    }
    if (!isset($errors['user_code'])) {
        $stmt = $db->prepare('SELECT 1 FROM users WHERE user_code = :code AND role = :role');
        $stmt->execute([':code' => $userCode, ':role' => $role]);
        if ($stmt->fetch()) {
            $errors['user_code'] = 'That ' . $role . ' ID is already taken.';
        }
    }

    if (!$errors) {
        $stmt = $db->prepare('INSERT INTO users (full_name, email, role, user_code, password_hash)
                              VALUES (:n, :e, :r, :c, :p)');
        $stmt->execute([
            ':n' => $name,
            ':e' => $email,
            ':r' => $role,
            ':c' => $userCode,
            ':p' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        $_SESSION['flash_success'] = 'Registration successful! You can now log in.';
        header('Location: index.php');
        exit;
    }
}

function field_error(array $errors, string $key): string
{
    return isset($errors[$key]) ? '<small class="err">' . e($errors[$key]) . '</small>' : '';
}
function cls(array $errors, string $key): string
{
    return isset($errors[$key]) ? 'invalid' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | MZBP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <section class="brand-panel"><?= logo_html() ?></section>

    <section class="form-panel">
        <h1>REGISTER</h1>

        <?php if ($errors): ?>
            <div class="alert error">Please fix the highlighted fields below.</div>
        <?php endif; ?>

        <form method="post" action="register.php" novalidate id="registerForm">
            <label for="full_name">Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?= old('full_name') ?>" class="<?= cls($errors, 'full_name') ?>">
            <?= field_error($errors, 'full_name') ?>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>" class="<?= cls($errors, 'email') ?>">
            <?= field_error($errors, 'email') ?>

            <label for="user_code" id="idLabel"><?= ucfirst($role) ?> ID:</label>
            <input type="text" id="user_code" name="user_code" value="<?= old('user_code') ?>" class="<?= cls($errors, 'user_code') ?>">
            <?= field_error($errors, 'user_code') ?>

            <label for="password">Password:</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" class="<?= cls($errors, 'password') ?>">
                <button type="button" class="toggle-pw" data-target="password">Show</button>
            </div>
            <?= field_error($errors, 'password') ?>

            <label for="confirm_password">Confirm Password:</label>
            <div class="pw-wrap">
                <input type="password" id="confirm_password" name="confirm_password" class="<?= cls($errors, 'confirm_password') ?>">
                <button type="button" class="toggle-pw" data-target="confirm_password">Show</button>
            </div>
            <?= field_error($errors, 'confirm_password') ?>

            <button type="submit" class="btn-login">Register</button>

            <div class="role-switch">
                <input type="radio" name="role" id="r-admin" value="admin" <?= $role === 'admin' ? 'checked' : '' ?>>
                <label for="r-admin">Admin</label>
                <input type="radio" name="role" id="r-employee" value="employee" <?= $role === 'employee' ? 'checked' : '' ?>>
                <label for="r-employee">Employee</label>
            </div>
        </form>

        <p class="switch-link">Already have an account? <a href="index.php">Log in</a></p>
    </section>
</main>
<script src="js/script.js"></script>
</body>
</html>