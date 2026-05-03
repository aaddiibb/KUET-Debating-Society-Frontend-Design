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

$eventName = trim((string) ($_POST['event_name'] ?? ''));
$eventDate = trim((string) ($_POST['event_date'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));

if ($eventName === '' || $eventDate === '' || $description === '') {
    header('Location: admin.php');
    exit;
}

ensure_events_table($pdo);

$insert = $pdo->prepare(
    'INSERT INTO events (event_name, event_date, description) VALUES (:event_name, :event_date, :description)'
);
$insert->execute([
    'event_name' => $eventName,
    'event_date' => $eventDate,
    'description' => $description,
]);

header('Location: admin.php');
exit;
