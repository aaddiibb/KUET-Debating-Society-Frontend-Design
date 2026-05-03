<?php

declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin_login.html');
    exit;
}

$username = trim((string) ($_POST['username'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

// Default credentials (change these before deploying anywhere public).
$adminUsername = 'admin';
$adminPasswordHash = '$2y$10$D/6OkAJP2Ig04AgTdiVaTeF43tcYJCmMvd8wC0KpUXOR5Rn9ZnVUG'; // admin12345

if ($username === '' || $password === '') {
    header('Location: admin_login.html?error=' . rawurlencode('Please enter both username and password.'));
    exit;
}

if ($username !== $adminUsername || !password_verify($password, $adminPasswordHash)) {
    header('Location: admin_login.html?error=' . rawurlencode('Invalid admin credentials.'));
    exit;
}

session_regenerate_id(true);
$_SESSION['admin_logged_in'] = true;

header('Location: admin.php');
exit;
