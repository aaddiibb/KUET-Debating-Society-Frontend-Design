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

function ensure_notices_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS notices (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            notice_title VARCHAR(255) NOT NULL,
            notice_body TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}

$noticeTitle = trim((string) ($_POST['notice_title'] ?? ''));
$noticeBody = trim((string) ($_POST['notice_body'] ?? ''));

if ($noticeTitle === '' || $noticeBody === '') {
    header('Location: admin.php');
    exit;
}

ensure_notices_table($pdo);

$insert = $pdo->prepare(
    'INSERT INTO notices (notice_title, notice_body) VALUES (:notice_title, :notice_body)'
);
$insert->execute([
    'notice_title' => $noticeTitle,
    'notice_body' => $noticeBody,
]);

header('Location: admin.php');
exit;
