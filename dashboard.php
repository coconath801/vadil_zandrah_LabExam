<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | MZBP</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <section class="brand-panel"><?= logo_html() ?></section>
    <section class="form-panel welcome">
        <h1>WELCOME</h1>
        <div class="alert success">Login successful!</div>
        <p class="hello">Hello, <strong><?= e($user['name']) ?></strong></p>
        <p>Role: <strong><?= e(ucfirst($user['role'])) ?></strong></p>
        <p><?= e(ucfirst($user['role'])) ?> ID: <strong><?= e($user['code']) ?></strong></p>
        <a class="btn-login logout" href="logout.php">Log out</a>
    </section>
</main>
</body>
</html>