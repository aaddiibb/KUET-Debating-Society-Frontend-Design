<?php

declare(strict_types=1);

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

$allowedDepartments = [
    'cse' => 'CSE',
    'eee' => 'EEE',
    'me'  => 'ME',
    'ce'  => 'CE',
    'other' => 'Other',
];

$submitted = [
    'fullName'        => '',
    'studentId'       => '',
    'department'      => '',
    'email'           => '',
    'password'        => '',
    'confirmPassword' => '',
    'agree'           => '',
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted['fullName']        = trim((string) ($_POST['fullName'] ?? ''));
    $submitted['studentId']       = trim((string) ($_POST['studentId'] ?? ''));
    $submitted['department']      = trim((string) ($_POST['department'] ?? ''));
    $submitted['email']           = trim((string) ($_POST['email'] ?? ''));
    $submitted['password']        = (string) ($_POST['password'] ?? '');
    $submitted['confirmPassword'] = (string) ($_POST['confirmPassword'] ?? '');
    $submitted['agree']           = isset($_POST['agree']) ? 'yes' : '';

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

    if ($errors === []) {
        ensure_members_table($pdo);

        $duplicateStatement = $pdo->prepare(
            'SELECT 1 FROM members WHERE email = :email OR student_id = :studentId LIMIT 1'
        );
        $duplicateStatement->execute([
            'email'     => $submitted['email'],
            'studentId' => $submitted['studentId'],
        ]);

        if ($duplicateStatement->fetchColumn() !== false) {
            $errors['email']     = 'Email or student ID already exists.';
            $errors['studentId'] = 'Email or student ID already exists.';
        }
    }

    if ($errors === []) {
        $passwordHash = password_hash($submitted['password'], PASSWORD_DEFAULT);

        $insertStatement = $pdo->prepare(
            'INSERT INTO members (full_name, student_id, department, email, password_hash)
             VALUES (:fullName, :studentId, :department, :email, :passwordHash)'
        );
        $insertStatement->execute([
            'fullName'     => $submitted['fullName'],
            'studentId'    => $submitted['studentId'],
            'department'   => $allowedDepartments[$submitted['department']],
            'email'        => $submitted['email'],
            'passwordHash' => $passwordHash,
        ]);

        header('Location: login.html?signup=success&message=' . rawurlencode('Account created successfully. Please log in.'));
        exit;
    }
}

$fullName          = escape_html($submitted['fullName']);
$studentId         = escape_html($submitted['studentId']);
$email             = escape_html($submitted['email']);
$selectedDept      = $submitted['department'];

$fieldError = static function (string $key) use ($errors): string {
    return isset($errors[$key])
        ? '<p class="error-message" role="alert">' . escape_html($errors[$key]) . '</p>'
        : '';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>KUET Debating Society | Member Signup</title>
	<link rel="stylesheet" href="signup.css">
</head>
<body>
	<main class="signup-page">
		<section class="signup-card" aria-labelledby="signup-title">
			<p class="club-name">KUET Debating Society</p>
			<h1 id="signup-title">Member Signup</h1>
			<p class="intro-text">Create your account to join club activities and updates.</p>

			<?php if ($errors !== []): ?>
				<div class="flash-message flash-error" role="alert">Please correct the highlighted fields and try again.</div>
			<?php endif; ?>

			<form action="signup.php" method="post" class="signup-form">
				<div class="form-group">
					<label for="full-name">Full Name</label>
					<input type="text" id="full-name" name="fullName" placeholder="Enter your full name" required value="<?= $fullName ?>">
					<?= $fieldError('fullName') ?>
				</div>

				<div class="form-group">
					<label for="student-id">Student ID</label>
					<input type="text" id="student-id" name="studentId" placeholder="Enter your student ID" required value="<?= $studentId ?>">
					<?= $fieldError('studentId') ?>
				</div>

				<div class="form-group">
					<label for="department">Department</label>
					<select id="department" name="department" required>
						<option value="" disabled <?= $selectedDept === '' ? 'selected' : '' ?>>Select your department</option>
						<?php foreach ($allowedDepartments as $value => $label): ?>
							<option value="<?= escape_html($value) ?>" <?= $selectedDept === $value ? 'selected' : '' ?>><?= escape_html($label) ?></option>
						<?php endforeach; ?>
					</select>
					<?= $fieldError('department') ?>
				</div>

				<div class="form-group">
					<label for="email">Email Address</label>
					<input type="email" id="email" name="email" placeholder="member@kuet.ac.bd" required value="<?= $email ?>">
					<?= $fieldError('email') ?>
				</div>

				<div class="form-group">
					<label for="password">Password</label>
					<input type="password" id="password" name="password" placeholder="Create a password" required>
					<?= $fieldError('password') ?>
				</div>

				<div class="form-group">
					<label for="confirm-password">Confirm Password</label>
					<input type="password" id="confirm-password" name="confirmPassword" placeholder="Re-enter password" required>
					<?= $fieldError('confirmPassword') ?>
				</div>

				<label class="agree-row">
					<input type="checkbox" name="agree" value="yes" required <?= $submitted['agree'] === 'yes' ? 'checked' : '' ?>>
					I agree to the club membership terms.
				</label>
				<?= $fieldError('agree') ?>

				<button type="submit" class="signup-btn">Create Account</button>
			</form>

			<p class="helper-text">Already a member? <a href="login.html" class="text-link">Go to login page</a></p>
		</section>
	</main>
	<script src="signup.js" defer></script>
</body>
</html>
