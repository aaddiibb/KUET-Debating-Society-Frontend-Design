<?php

declare(strict_types=1);

session_start();

if (empty($_SESSION['member_id'])) {
    header('Location: login.html');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$eventId = (int) ($_POST['event_id'] ?? 0);
if ($eventId <= 0) {
    header('Location: events.php?registered=error');
    exit;
}

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

ensure_events_table($pdo);
ensure_registrations_table($pdo);

$eventCheck = $pdo->prepare('SELECT id FROM events WHERE id = :id LIMIT 1');
$eventCheck->execute(['id' => $eventId]);

if ($eventCheck->fetchColumn() === false) {
    header('Location: events.php?registered=error');
    exit;
}

$insert = $pdo->prepare('INSERT INTO registrations (member_id, event_id) VALUES (:member_id, :event_id)');

try {
    $insert->execute([
        'member_id' => (int) $_SESSION['member_id'],
        'event_id'  => $eventId,
    ]);
    header('Location: events.php?registered=success');
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        header('Location: events.php?registered=already');
    } else {
        throw $exception;
    }
}

exit;
