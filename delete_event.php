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

$eventId = (int) ($_POST['event_id'] ?? 0);

if ($eventId <= 0) {
    header('Location: admin.php?msg=' . rawurlencode('Invalid event.') . '&type=error');
    exit;
}

ensure_events_table($pdo);
ensure_registrations_table($pdo);

$pdo->prepare('DELETE FROM registrations WHERE event_id = :event_id')->execute(['event_id' => $eventId]);
$pdo->prepare('DELETE FROM events WHERE id = :id')->execute(['id' => $eventId]);

header('Location: admin.php?msg=' . rawurlencode('Event deleted.') . '&type=success');
exit;
