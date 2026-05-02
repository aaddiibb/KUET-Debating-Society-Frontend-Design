<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$allowedDepartments = [
    'cse' => 'CSE',
    'eee' => 'EEE',
    'me' => 'ME',
    'ce' => 'CE',
    'other' => 'Other',
];

$submitted = [
    'fullName' => '',
    'studentId' => '',
    'department' => '',
    'email' => '',
    'password' => '',
    'confirmPassword' => '',
    'agree' => '',
];

$errors = [];

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function render_signup_page(array $submitted, array $errors, array $allowedDepartments): void
{
    $fullName = escape_html($submitted['fullName']);
    $studentId = escape_html($submitted['studentId']);
    $email = escape_html($submitted['email']);
    $selectedDepartment = $submitted['department'];

    $fieldError = static function (string $key) use ($errors): string {
        return isset($errors[$key]) ? '<p class="error-message" role="alert">' . escape_html($errors[$key]) . '</p>' : '';
    };

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>KUET Debating Society | Member Signup</title>';
    echo '<link rel="stylesheet" href="signup.css">';
    echo '</head>';
    echo '<body>';
    echo '<main class="signup-page">';
    echo '<section class="signup-card" aria-labelledby="signup-title">';
    echo '<p class="club-name">KUET Debating Society</p>';
    echo '<h1 id="signup-title">Member Signup</h1>';
    echo '<p class="intro-text">Create your account to join club activities and updates.</p>';

    if ($errors !== []) {
        echo '<div class="flash-message flash-error" role="alert">Please correct the highlighted fields and try again.</div>';
    }

	if (isset($errors['general']) && $errors['general'] !== '') {
		echo '<p class="error-message" role="alert">' . escape_html((string) $errors['general']) . '</p>';
	}

    echo '<form action="signup.php" method="post" class="signup-form">';
    echo '<div class="form-group">';
    echo '<label for="full-name">Full Name</label>';
    echo '<input type="text" id="full-name" name="fullName" placeholder="Enter your full name" required value="' . $fullName . '">';
    echo $fieldError('fullName');
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="student-id">Student ID</label>';
    echo '<input type="text" id="student-id" name="studentId" placeholder="Enter your student ID" required value="' . $studentId . '">';
    echo $fieldError('studentId');
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="department">Department</label>';
    echo '<select id="department" name="department" required>';
    echo '<option value="" disabled' . ($selectedDepartment === '' ? ' selected' : '') . '>Select your department</option>';
    foreach ($allowedDepartments as $value => $label) {
        $selected = $selectedDepartment === $value ? ' selected' : '';
        echo '<option value="' . escape_html($value) . '"' . $selected . '>' . escape_html($label) . '</option>';
    }
    echo '</select>';
    echo $fieldError('department');
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="email">Email Address</label>';
    echo '<input type="email" id="email" name="email" placeholder="member@kuet.ac.bd" required value="' . $email . '">';
    echo $fieldError('email');
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="password">Password</label>';
    echo '<input type="password" id="password" name="password" placeholder="Create a password" required>';
    echo $fieldError('password');
    echo '</div>';

    echo '<div class="form-group">';
    echo '<label for="confirm-password">Confirm Password</label>';
    echo '<input type="password" id="confirm-password" name="confirmPassword" placeholder="Re-enter password" required>';
    echo $fieldError('confirmPassword');
    echo '</div>';

    echo '<label class="agree-row">';
    echo '<input type="checkbox" name="agree" value="yes" required' . ($submitted['agree'] === 'yes' ? ' checked' : '') . '>';
    echo 'I agree to the club membership terms.';
    echo '</label>';
    echo $fieldError('agree');

    echo '<button type="submit" class="signup-btn">Create Account</button>';
    echo '</form>';

    echo '<p class="helper-text">Already a member? <a href="login.html" class="text-link">Go to login page</a></p>';
    echo '</section>';
    echo '</main>';
    echo '<script src="signup.js" defer></script>';
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
    render_signup_page($submitted, $errors, $allowedDepartments);
    exit;
}

$submitted['fullName'] = trim((string) ($_POST['fullName'] ?? ''));
$submitted['studentId'] = trim((string) ($_POST['studentId'] ?? ''));
$submitted['department'] = trim((string) ($_POST['department'] ?? ''));
$submitted['email'] = trim((string) ($_POST['email'] ?? ''));
$submitted['password'] = (string) ($_POST['password'] ?? '');
$submitted['confirmPassword'] = (string) ($_POST['confirmPassword'] ?? '');
$submitted['agree'] = isset($_POST['agree']) ? 'yes' : '';

if ($submitted['fullName'] === '') {
    $errors['fullName'] = 'Full name is required.';
}

if ($submitted['studentId'] === '') {
    $errors['studentId'] = 'Student ID is required.';
} elseif (!preg_match('/^\d{7}$/', $submitted['studentId'])) {
    $errors['studentId'] = 'Student ID must be exactly 7 digits.';
}

if ($submitted['department'] === '') {
    $errors['department'] = 'Department is required.';
} elseif (!array_key_exists($submitted['department'], $allowedDepartments)) {
    $errors['department'] = 'Please select a valid department.';
}

if ($submitted['email'] === '') {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($submitted['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
} elseif (!str_ends_with(strtolower($submitted['email']), 'kuet.ac.bd')) {
    $errors['email'] = 'Email must end with kuet.ac.bd.';
}

if ($submitted['password'] === '') {
    $errors['password'] = 'Password is required.';
} elseif (strlen($submitted['password']) < 8) {
    $errors['password'] = 'Password must be at least 8 characters.';
}

if ($submitted['confirmPassword'] === '') {
    $errors['confirmPassword'] = 'Please confirm your password.';
} elseif ($submitted['password'] !== $submitted['confirmPassword']) {
    $errors['confirmPassword'] = 'Passwords do not match.';
}

if ($submitted['agree'] !== 'yes') {
    $errors['agree'] = 'You must accept the membership terms.';
}

if ($errors !== []) {
    render_signup_page($submitted, $errors, $allowedDepartments);
    exit;
}

ensure_members_table($pdo);

$duplicateStatement = $pdo->prepare(
    'SELECT 1 FROM members WHERE email = :email OR student_id = :studentId LIMIT 1'
);
$duplicateStatement->execute([
    'email' => $submitted['email'],
    'studentId' => $submitted['studentId'],
]);

if ($duplicateStatement->fetchColumn() !== false) {
    $errors['email'] = 'Email or student ID already exists.';
    $errors['studentId'] = 'Email or student ID already exists.';
    render_signup_page($submitted, $errors, $allowedDepartments);
    exit;
}

$passwordHash = password_hash($submitted['password'], PASSWORD_DEFAULT);

$insertStatement = $pdo->prepare(
    'INSERT INTO members (full_name, student_id, department, email, password_hash) VALUES (:fullName, :studentId, :department, :email, :passwordHash)'
);
$insertStatement->execute([
    'fullName' => $submitted['fullName'],
    'studentId' => $submitted['studentId'],
    'department' => $allowedDepartments[$submitted['department']],
    'email' => $submitted['email'],
    'passwordHash' => $passwordHash,
]);

$successMessage = rawurlencode('Account created successfully. Please log in.');
header('Location: login.html?signup=success&message=' . $successMessage);
exit;