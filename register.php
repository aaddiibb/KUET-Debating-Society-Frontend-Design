<?php

declare(strict_types=1);

session_start();

if (empty($_SESSION['member_id'])) {
    header('Location: login.html');
    exit;
}

require __DIR__ . '/db.php';

function ensure_events_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS events (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            event_name VARCHAR(255) NOT NULL,
            event_date DATE NOT NULL,
            description TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}

function ensure_registrations_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS registrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            member_id INT UNSIGNED NOT NULL,
            event_id INT UNSIGNED NOT NULL,
            registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_registration (member_id, event_id),
            KEY idx_registrations_member (member_id),
            KEY idx_registrations_event (event_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: events.php');
    exit;
}

$eventId = (int) ($_POST['event_id'] ?? 0);
if ($eventId <= 0) {
    header('Location: events.php');
    exit;
}

ensure_events_table($pdo);
ensure_registrations_table($pdo);

$eventCheck = $pdo->prepare('SELECT id FROM events WHERE id = :id LIMIT 1');
$eventCheck->execute(['id' => $eventId]);

if ($eventCheck->fetchColumn() === false) {
    header('Location: events.php');
    exit;
}

$insert = $pdo->prepare('INSERT INTO registrations (member_id, event_id) VALUES (:member_id, :event_id)');

try {
    $insert->execute([
        'member_id' => (int) $_SESSION['member_id'],
        'event_id' => $eventId,
    ]);
} catch (PDOException $exception) {
    // Duplicate registration (unique constraint) should not break the user flow.
    if ($exception->getCode() !== '23000') {
        throw $exception;
    }
}

header('Location: events.php');
exit;
