<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contact.html');
    exit;
}

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

$name    = trim((string) ($_POST['name'] ?? ''));
$email   = trim((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    header('Location: contact.html?error=' . rawurlencode('All fields are required.'));
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: contact.html?error=' . rawurlencode('Please enter a valid email address.'));
    exit;
}

ensure_contacts_table($pdo);

$insert = $pdo->prepare(
    'INSERT INTO contacts (name, email, subject, message) VALUES (:name, :email, :subject, :message)'
);
$insert->execute([
    'name'    => $name,
    'email'   => $email,
    'subject' => $subject,
    'message' => $message,
]);

header('Location: contact.html?sent=1');
exit;
