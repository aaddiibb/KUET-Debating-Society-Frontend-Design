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

$noticeId = (int) ($_POST['notice_id'] ?? 0);

if ($noticeId <= 0) {
    header('Location: admin.php?msg=' . rawurlencode('Invalid notice.') . '&type=error');
    exit;
}

ensure_notices_table($pdo);

$pdo->prepare('DELETE FROM notices WHERE id = :id')->execute(['id' => $noticeId]);

header('Location: admin.php?msg=' . rawurlencode('Notice deleted.') . '&type=success');
exit;
