<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/db.php';

$errorMessage = '';
$emailValue = '';

function render_login_page(string $errorMessage, string $emailValue): void
{
    $escapedError = htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8');
    $escapedEmail = htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8');

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>KUET Debating Society | Member Login</title>';
    echo '<link rel="stylesheet" href="login.css">';
    echo '</head>';
    echo '<body>';
    echo '<main class="login-page">';
    echo '<section class="login-card" aria-labelledby="login-title">';
    echo '<p class="club-name">KUET Debating Society</p>';
    echo '<h1 id="login-title">Member Login</h1>';
    echo '<p class="intro-text">Sign in to access club updates and activities.</p>';

    if ($errorMessage !== '') {
        echo '<p class="error-message" role="alert">' . $escapedError . '</p>';
    }

    echo '<form action="login.php" method="post" class="login-form">';
    echo '<div class="form-group">';
    echo '<label for="email">Email Address</label>';
    echo '<input type="email" id="email" name="email" placeholder="member@kuet.ac.bd" required value="' . $escapedEmail . '">';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label for="password">Password</label>';
    echo '<input type="password" id="password" name="password" placeholder="Enter your password" required>';
    echo '</div>';
    echo '<div class="utility-row">';
    echo '<a href="#" class="text-link forgot-link">Forgot password?</a>';
    echo '</div>';
    echo '<button type="submit" class="login-btn">Login</button>';
    echo '</form>';
    echo '<p class="helper-text">Need an account? <a href="signup.html" class="text-link">Go to signup page</a></p>';
    echo '</section>';
    echo '</main>';
    echo '<script src="login.js" defer></script>';
    echo '</body>';
    echo '</html>';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_login_page('', '');
    exit;
}

$emailValue = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($emailValue === '' || $password === '') {
    render_login_page('Please enter both email and password.', $emailValue);
    exit;
}

$candidateTables = ['members', 'member', 'users'];
$member = null;

foreach ($candidateTables as $tableName) {
    try {
        $statement = $pdo->prepare("SELECT * FROM {$tableName} WHERE email = :email LIMIT 1");
        $statement->execute(['email' => $emailValue]);
        $member = $statement->fetch();

        if ($member !== false) {
            break;
        }
    } catch (PDOException $exception) {
        $message = $exception->getMessage();
        if (stripos($message, 'doesn\'t exist') === false && stripos($message, 'Base table or view not found') === false) {
            throw $exception;
        }
    }
}

if ($member === false || $member === null) {
    render_login_page('Invalid email or password.', $emailValue);
    exit;
}

$memberPasswordHash = $member['password'] ?? $member['password_hash'] ?? $member['hashed_password'] ?? '';

if (!is_string($memberPasswordHash) || $memberPasswordHash === '' || !password_verify($password, $memberPasswordHash)) {
    render_login_page('Invalid email or password.', $emailValue);
    exit;
}

$memberId = $member['id'] ?? $member['member_id'] ?? $member['memberId'] ?? null;
$memberName = $member['name'] ?? $member['full_name'] ?? $member['fullName'] ?? $member['member_name'] ?? '';
$memberEmail = $member['email'] ?? $emailValue;

if ($memberId === null || $memberName === '' || $memberEmail === '') {
    render_login_page('Your account record is missing required profile data.', $emailValue);
    exit;
}

session_regenerate_id(true);
$_SESSION['member_id'] = $memberId;
$_SESSION['member_name'] = $memberName;
$_SESSION['member_email'] = $memberEmail;

header('Location: dashboard.php');
exit;