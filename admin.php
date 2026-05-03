<?php

declare(strict_types=1);

session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.html');
    exit;
}

require __DIR__ . '/db.php';

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function ensure_members_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS members (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(255) NOT NULL,
            student_id VARCHAR(20) NOT NULL,
            department VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_members_email (email),
            UNIQUE KEY uniq_members_student_id (student_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}

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

ensure_members_table($pdo);
ensure_events_table($pdo);
ensure_notices_table($pdo);

$members = $pdo->query('SELECT id, full_name, student_id, department, email, created_at FROM members ORDER BY created_at DESC')->fetchAll();
$events = $pdo->query('SELECT id, event_name, event_date, description, created_at FROM events ORDER BY event_date ASC')->fetchAll();
$notices = $pdo->query('SELECT id, notice_title, notice_body, created_at FROM notices ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KUET Debating Society | Admin Panel</title>
</head>
<body>
    <header>
        <h1>Admin Panel</h1>
        <p>Manage members, events, and notices.</p>
    </header>

    <main>
        <section aria-labelledby="members-title">
            <h2 id="members-title">All Members</h2>
            <?php if ($members === []): ?>
                <p>No members found.</p>
            <?php else: ?>
                <table border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Student ID</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo (int) $member['id']; ?></td>
                                <td><?php echo escape_html((string) $member['full_name']); ?></td>
                                <td><?php echo escape_html((string) $member['student_id']); ?></td>
                                <td><?php echo escape_html((string) $member['department']); ?></td>
                                <td><?php echo escape_html((string) $member['email']); ?></td>
                                <td><?php echo escape_html((string) $member['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <hr>

        <section aria-labelledby="events-title">
            <h2 id="events-title">All Events</h2>
            <?php if ($events === []): ?>
                <p>No events found.</p>
            <?php else: ?>
                <table border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><?php echo (int) $event['id']; ?></td>
                                <td><?php echo escape_html((string) $event['event_name']); ?></td>
                                <td><?php echo escape_html((string) $event['event_date']); ?></td>
                                <td><?php echo escape_html((string) $event['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3>Add Event</h3>
            <form action="add_event.php" method="post">
                <p>
                    <label for="event_name">Event Name</label><br>
                    <input type="text" id="event_name" name="event_name" required>
                </p>
                <p>
                    <label for="event_date">Event Date</label><br>
                    <input type="date" id="event_date" name="event_date" required>
                </p>
                <p>
                    <label for="description">Description</label><br>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </p>
                <button type="submit">Add Event</button>
            </form>
        </section>

        <hr>

        <section aria-labelledby="notices-title">
            <h2 id="notices-title">All Notices</h2>
            <?php if ($notices === []): ?>
                <p>No notices found.</p>
            <?php else: ?>
                <table border="1" cellpadding="8" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Body</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notices as $notice): ?>
                            <tr>
                                <td><?php echo (int) $notice['id']; ?></td>
                                <td><?php echo escape_html((string) $notice['notice_title']); ?></td>
                                <td><?php echo escape_html((string) $notice['notice_body']); ?></td>
                                <td><?php echo escape_html((string) $notice['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <h3>Add Notice</h3>
            <form action="add_notice.php" method="post">
                <p>
                    <label for="notice_title">Notice Title</label><br>
                    <input type="text" id="notice_title" name="notice_title" required>
                </p>
                <p>
                    <label for="notice_body">Notice Body</label><br>
                    <textarea id="notice_body" name="notice_body" rows="4" required></textarea>
                </p>
                <button type="submit">Add Notice</button>
            </form>
        </section>
    </main>
</body>
</html>
