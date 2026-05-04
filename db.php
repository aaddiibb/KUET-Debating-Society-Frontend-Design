<?php

declare(strict_types=1);

function is_local_request(): bool
{
    if (PHP_SAPI === 'cli') {
        return true;
    }

    $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
    return $host === 'localhost'
        || strpos($host, 'localhost:') === 0
        || $host === '127.0.0.1'
        || strpos($host, '127.0.0.1:') === 0;
}

function render_database_error_page(string $publicMessage, ?string $details = null)
{
    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }

    $safeMessage = htmlspecialchars($publicMessage, ENT_QUOTES, 'UTF-8');
    $safeDetails = $details !== null ? htmlspecialchars($details, ENT_QUOTES, 'UTF-8') : '';

    echo '<!DOCTYPE html><html lang="en"><head>';
    echo '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>Database Connection Failed</title>';
    echo '<style>';
    echo 'body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f7f3e9;margin:0;padding:32px;color:#1d2a44;line-height:1.5}';
    echo '.card{max-width:760px;margin:0 auto;background:#fffdf7;border:1px solid #d7d3c8;border-radius:14px;padding:18px 20px}';
    echo 'h1{margin:0 0 10px;font-size:1.35rem}';
    echo 'p{margin:0 0 10px;color:#4f5f78}';
    echo 'code{background:#f2ece0;padding:2px 6px;border-radius:8px}';
    echo '.details{margin-top:12px;white-space:pre-wrap;background:#f2ece0;border:1px solid #d7d3c8;border-radius:12px;padding:12px;color:#1d2a44}';
    echo '.hint{margin-top:14px}';
    echo 'a{color:#2d8f8b;font-weight:600;text-decoration:none}a:hover{text-decoration:underline}';
    echo '</style></head><body>';
    echo '<div class="card">';
    echo '<h1>Database connection failed</h1>';
    echo '<p>' . $safeMessage . '</p>';
    echo '<div class="hint">';
    echo '<p>Fix checklist:</p>';
    echo '<p>1) Start <strong>MySQL</strong> in XAMPP Control Panel.</p>';
    echo '<p>2) Verify credentials in <code>db.php</code> (user/password) match phpMyAdmin.</p>';
    echo '<p>3) If you prefer not to hard-code passwords, set environment variables: <code>KDS_DB_HOST</code>, <code>KDS_DB_NAME</code>, <code>KDS_DB_USER</code>, <code>KDS_DB_PASS</code>.</p>';
    echo '</div>';

    if ($safeDetails !== '') {
        echo '<div class="details"><strong>Details (localhost only)</strong>\n' . $safeDetails . '</div>';
    }

    echo '<p style="margin-top:14px"><a href="home.html">← Back to Home</a></p>';
    echo '</div></body></html>';
    exit;
}

$host = getenv('KDS_DB_HOST') ?: 'localhost';
$dbName = getenv('KDS_DB_NAME') ?: 'kuet_ds';
$username = getenv('KDS_DB_USER') ?: 'root';
$password = getenv('KDS_DB_PASS') ?: '';
$charset = getenv('KDS_DB_CHARSET') ?: 'utf8mb4';

$serverDsn = "mysql:host={$host};charset={$charset}";
$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $serverPdo = new PDO($serverDsn, $username, $password, $options);
    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET {$charset} COLLATE utf8mb4_unicode_ci");
    $serverPdo = null;

    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
    error_log('Database connection failed: ' . $errorMessage);

    $details = is_local_request() ? $errorMessage : null;
    render_database_error_page('The site cannot connect to the database right now.', $details);
}
