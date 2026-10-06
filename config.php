<?php
// Shared setup: session, SQLite database (auto-created), helper functions.
session_start();

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

$db = new PDO('sqlite:' . $dataDir . '/app.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    role TEXT NOT NULL CHECK (role IN ('admin','employee')),
    user_code TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (role, user_code)
)");

// Escape output to prevent XSS.
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Keep what the user typed after a failed submit.
function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

// Render the logo (uses assets/logo.png if present, otherwise a text logo).
function logo_html(): string
{
    if (file_exists(__DIR__ . '/assets/logo.png')) {
        return '<img src="assets/logo.png" alt="MZBP logo" class="logo-img">';
    }
    return '<div class="logo-text">MZBP<span>+</span></div>';
}