<?php
require __DIR__ . '/config.php';

if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

$role = ($_POST['role'] ?? 'admin') === 'employee' ? 'employee' : 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCode = trim($_POST['user_code'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($userCode === '') {
        $errors['user_code'] = ucfirst($role) . ' ID is required.';
    }
    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (!$errors) {
        $stmt = $db->prepare('SELECT * FROM users WHERE user_code = :code AND role = :role');
        $stmt->execute([':code' => $userCode, ':role' => $role]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'role' => $user['role'],
                'code' => $user['user_code'],
            ];
            header('Location: dashboard.php');
            exit;
        }
        $errors['general'] = 'Invalid ID or password for the selected role.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log In | MZBP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <section class="brand-panel"><?= logo_html() ?></section>

    <section class="form-panel">
        <h1>LOG IN</h1>

        <?php if ($success): ?>
            <div class="alert success"><?= e($success) ?></div>
        <?php endif; ?>
        <?php if (isset($errors['general'])): ?>
            <div class="alert error"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php" novalidate id="loginForm">
            <label for="user_code" id="idLabel"><?= ucfirst($role) ?> ID:</label>
            <input type="text" id="user_code" name="user_code" value="<?= old('user_code') ?>"
                   class="<?= isset($errors['user_code']) ? 'invalid' : '' ?>">
            <?php if (isset($errors['user_code'])): ?><small class="err"><?= e($errors['user_code']) ?></small><?php endif; ?>

            <label for="password">Password:</label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password"
                       class="<?= isset($errors['password']) ? 'invalid' : '' ?>">
                <button type="button" class="toggle-pw" data-target="password">Show</button>
            </div>
            <?php if (isset($errors['password'])): ?><small class="err"><?= e($errors['password']) ?></small><?php endif; ?>

            <button type="submit" class="btn-login">Login</button>

            <div class="role-switch">
                <input type="radio" name="role" id="r-admin" value="admin" <?= $role === 'admin' ? 'checked' : '' ?>>
                <label for="r-admin">Admin</label>
                <input type="radio" name="role" id="r-employee" value="employee" <?= $role === 'employee' ? 'checked' : '' ?>>
                <label for="r-employee">Employee</label>
            </div>
        </form>

        <p class="switch-link">No account yet? <a href="register.php">Register here</a></p>
    </section>
</main>
<script src="script.js"></script>
</body>
</html>
