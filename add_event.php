<?php

declare(strict_types=1);

session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php');
    exit;
}

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

$eventName   = trim((string) ($_POST['event_name'] ?? ''));
$eventDate   = trim((string) ($_POST['event_date'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));

if ($eventName === '' || $eventDate === '' || $description === '') {
    header('Location: admin.php?msg=' . rawurlencode('All fields are required.') . '&type=error');
    exit;
}

if (DateTimeImmutable::createFromFormat('Y-m-d', $eventDate) === false) {
    header('Location: admin.php?msg=' . rawurlencode('Invalid event date format.') . '&type=error');
    exit;
}

ensure_events_table($pdo);

$insert = $pdo->prepare(
    'INSERT INTO events (event_name, event_date, description) VALUES (:event_name, :event_date, :description)'
);
$insert->execute([
    'event_name'  => $eventName,
    'event_date'  => $eventDate,
    'description' => $description,
]);

header('Location: admin.php?msg=' . rawurlencode('Event added successfully.') . '&type=success');
exit;
