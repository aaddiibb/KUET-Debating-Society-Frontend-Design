<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html');
    exit;
}

$emailValue = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($emailValue === '' || $password === '') {
    header('Location: login.html?error=' . rawurlencode('Please enter both email and password.') . '&email=' . rawurlencode($emailValue));
    exit;
}

ensure_members_table($pdo);

$statement = $pdo->prepare('SELECT id, full_name, email, password_hash FROM members WHERE email = :email LIMIT 1');
$statement->execute(['email' => $emailValue]);
$member = $statement->fetch();

if ($member === false || !password_verify($password, (string) ($member['password_hash'] ?? ''))) {
    header('Location: login.html?error=' . rawurlencode('Invalid email or password.') . '&email=' . rawurlencode($emailValue));
    exit;
}

$memberId = $member['id'] ?? null;
$memberName = (string) ($member['full_name'] ?? '');
$memberEmail = (string) ($member['email'] ?? $emailValue);

if (empty($memberId) || $memberName === '' || $memberEmail === '') {
    header('Location: login.html?error=' . rawurlencode('Your account record is missing required profile data.') . '&email=' . rawurlencode($emailValue));
    exit;
}

session_regenerate_id(true);
$_SESSION['member_id'] = $memberId;
$_SESSION['member_name'] = $memberName;
$_SESSION['member_email'] = $memberEmail;

header('Location: dashboard.php');
exit;
