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

function ensure_members_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS members (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            student_id VARCHAR(20) NOT NULL,
            department VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_members_email (email),
            UNIQUE KEY uniq_members_student_id (student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
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

ensure_members_table($pdo);

$statement = $pdo->prepare('SELECT id, full_name, email, password_hash FROM members WHERE email = :email LIMIT 1');
$statement->execute(['email' => $emailValue]);
$member = $statement->fetch();

if ($member === false) {
    render_login_page('Invalid email or password.', $emailValue);
    exit;
}

$memberPasswordHash = (string) ($member['password_hash'] ?? '');

if ($memberPasswordHash === '' || !password_verify($password, $memberPasswordHash)) {
    render_login_page('Invalid email or password.', $emailValue);
    exit;
}

$memberId = $member['id'] ?? null;
$memberName = (string) ($member['full_name'] ?? '');
$memberEmail = (string) ($member['email'] ?? $emailValue);

if (empty($memberId) || $memberName === '' || $memberEmail === '') {
    render_login_page('Your account record is missing required profile data.', $emailValue);
    exit;
}

session_regenerate_id(true);
$_SESSION['member_id'] = $memberId;
$_SESSION['member_name'] = $memberName;
$_SESSION['member_email'] = $memberEmail;

header('Location: dashboard.php');
exit;