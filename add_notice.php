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

$noticeTitle = trim((string) ($_POST['notice_title'] ?? ''));
$noticeBody  = trim((string) ($_POST['notice_body'] ?? ''));

if ($noticeTitle === '' || $noticeBody === '') {
    header('Location: admin.php?msg=' . rawurlencode('Both title and body are required.') . '&type=error');
    exit;
}

ensure_notices_table($pdo);

$insert = $pdo->prepare(
    'INSERT INTO notices (notice_title, notice_body) VALUES (:notice_title, :notice_body)'
);
$insert->execute([
    'notice_title' => $noticeTitle,
    'notice_body'  => $noticeBody,
]);

header('Location: admin.php?msg=' . rawurlencode('Notice added successfully.') . '&type=success');
exit;
