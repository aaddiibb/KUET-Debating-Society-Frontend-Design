<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/db.php';

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

function seed_events(PDO $pdo): void
{
    $statement = $pdo->query('SELECT COUNT(*) FROM events');
    $eventCount = (int) $statement->fetchColumn();

    if ($eventCount > 0) {
        return;
    }

    $seedRows = [
        [
            'event_name' => 'British Parliamentary Fundamentals',
            'event_date' => '2026-04-24',
            'description' => 'Interactive orientation session focused on motion analysis, role strategy, and floor confidence.',
        ],
        [
            'event_name' => 'Policy Debate Simulation Day',
            'event_date' => '2026-05-03',
            'description' => 'Full-day simulation with policy motions, timed caucuses, and post-round judge evaluations.',
        ],
        [
            'event_name' => 'Women in Debate Workshop',
            'event_date' => '2026-05-17',
            'description' => 'Mentorship-driven workshop promoting inclusive participation, rebuttal design, and public voice.',
        ],
        [
            'event_name' => 'National Debate Qualifier Prep Camp',
            'event_date' => '2026-06-06',
            'description' => 'High-intensity prep for teams aiming to represent KUET at national university competitions.',
        ],
    ];

    $insertStatement = $pdo->prepare(
        'INSERT INTO events (event_name, event_date, description) VALUES (:event_name, :event_date, :description)'
    );

    foreach ($seedRows as $row) {
        $insertStatement->execute($row);
    }
}

function format_event_date(string $dateValue): string
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateValue);

    if ($date === false) {
        return $dateValue;
    }

    return $date->format('F j, Y');
}

ensure_events_table($pdo);
ensure_registrations_table($pdo);
seed_events($pdo);

$statement = $pdo->query('SELECT id, event_name, event_date, description FROM events ORDER BY event_date ASC');
$events = $statement->fetchAll();

$memberId = $_SESSION['member_id'] ?? null;
$registeredEventIds = [];

if (!empty($memberId)) {
	$registeredStatement = $pdo->prepare('SELECT event_id FROM registrations WHERE member_id = :member_id');
	$registeredStatement->execute(['member_id' => $memberId]);

	foreach ($registeredStatement->fetchAll() as $row) {
		$registeredEventIds[(int) $row['event_id']] = true;
	}
}

$today = new DateTimeImmutable('today');
$upcomingEvents = [];
$pastEvents = [];

foreach ($events as $event) {
    $eventDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $event['event_date']);

    if ($eventDate === false || $eventDate >= $today) {
        $upcomingEvents[] = $event;
    } else {
        $pastEvents[] = $event;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>KUET Debating Society | Events</title>
	<link rel="stylesheet" href="events.css">
</head>
<body>
	<header class="hero">
		<div class="wrap reveal">
			<div class="brand">
				<span class="brand-mark"></span>
				<span>KUET Debating Society</span>
			</div>
			<h1>All Club Events</h1>
			<p class="tagline">Plan your next debate journey.</p>
			<p>
				Browse every KUET Debating Society event in one place. Review upcoming opportunities and revisit completed activities to stay connected with the club timeline.
			</p>
			<nav class="cta-group" aria-label="Primary navigation">
				<a class="btn btn-ghost" href="home.html">Home</a>
				<a class="btn btn-primary" href="events.php" aria-current="page">Events</a>
				<a class="btn btn-secondary" href="login.html">Login</a>
				<a class="btn btn-secondary" href="signup.html">Signup</a>
			</nav>
		</div>
	</header>

	<main>
		<section class="wrap reveal delay-1" id="events">
			<h2 class="section-title">Upcoming Events</h2>
			<p class="section-sub">Events on or after today are shown here.</p>

			<div class="events-grid" id="upcoming-events-grid">
				<?php if ($upcomingEvents === []): ?>
					<p>No upcoming events are available right now.</p>
				<?php else: ?>
					<?php foreach ($upcomingEvents as $event): ?>
						<article class="event-card upcoming">
							<p class="event-date"><?php echo escape_html(format_event_date((string) $event['event_date'])); ?></p>
							<h3><?php echo escape_html((string) $event['event_name']); ?></h3>
							<p><?php echo escape_html((string) $event['description']); ?></p>
							<?php $eventId = (int) $event['id']; ?>
							<?php if (!empty($memberId) && isset($registeredEventIds[$eventId])): ?>
								<span class="register-btn" aria-disabled="true">Registered</span>
							<?php else: ?>
								<form action="register.php" method="post">
									<input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
									<button class="register-btn" type="submit">Register</button>
								</form>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</section>

		<section class="wrap reveal delay-1" id="past-events">
			<h2 class="section-title">Past Events</h2>
			<p class="section-sub">Completed events appear below in reverse chronological context from the database order.</p>

			<div class="events-grid" id="past-events-grid">
				<?php if ($pastEvents === []): ?>
					<p>No past events are available right now.</p>
				<?php else: ?>
					<?php foreach ($pastEvents as $event): ?>
						<article class="event-card past">
							<p class="event-date"><?php echo escape_html(format_event_date((string) $event['event_date'])); ?></p>
							<h3><?php echo escape_html((string) $event['event_name']); ?></h3>
							<p><?php echo escape_html((string) $event['description']); ?></p>
							<?php $eventId = (int) $event['id']; ?>
							<?php if (!empty($memberId) && isset($registeredEventIds[$eventId])): ?>
								<span class="register-btn" aria-disabled="true">Registered</span>
							<?php else: ?>
								<form action="register.php" method="post">
									<input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
									<button class="register-btn" type="submit">Register</button>
								</form>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<footer>
		<div class="wrap">
			KUET Debating Society | Khulna University of Engineering & Technology, Bangladesh
		</div>
	</footer>
</body>
</html>