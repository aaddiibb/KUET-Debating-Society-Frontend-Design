<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

ensure_events_table($pdo);
ensure_registrations_table($pdo);

$statement = $pdo->query('SELECT COUNT(*) FROM events');
if ((int) $statement->fetchColumn() === 0) {
    $seedRows = [
        ['event_name' => 'British Parliamentary Fundamentals', 'event_date' => '2026-04-24', 'description' => 'Interactive orientation session focused on motion analysis, role strategy, and floor confidence.'],
        ['event_name' => 'Policy Debate Simulation Day',        'event_date' => '2026-05-03', 'description' => 'Full-day simulation with policy motions, timed caucuses, and post-round judge evaluations.'],
        ['event_name' => 'Women in Debate Workshop',            'event_date' => '2026-05-17', 'description' => 'Mentorship-driven workshop promoting inclusive participation, rebuttal design, and public voice.'],
        ['event_name' => 'National Debate Qualifier Prep Camp', 'event_date' => '2026-06-06', 'description' => 'High-intensity prep for teams aiming to represent KUET at national university competitions.'],
    ];
    $insertStatement = $pdo->prepare('INSERT INTO events (event_name, event_date, description) VALUES (:event_name, :event_date, :description)');
    foreach ($seedRows as $row) {
        $insertStatement->execute($row);
    }
}

$today = new DateTimeImmutable('today');
$todayStr = $today->format('Y-m-d');

$upcomingStatement = $pdo->prepare('SELECT id, event_name, event_date, description FROM events WHERE event_date >= :today ORDER BY event_date ASC');
$upcomingStatement->execute(['today' => $todayStr]);
$upcomingEvents = $upcomingStatement->fetchAll();

$pastStatement = $pdo->prepare('SELECT id, event_name, event_date, description FROM events WHERE event_date < :today ORDER BY event_date DESC');
$pastStatement->execute(['today' => $todayStr]);
$pastEvents = $pastStatement->fetchAll();

$memberId = isset($_SESSION['member_id']) ? (int) $_SESSION['member_id'] : null;
$registeredEventIds = [];

if ($memberId !== null) {
    $registeredStatement = $pdo->prepare('SELECT event_id FROM registrations WHERE member_id = :member_id');
    $registeredStatement->execute(['member_id' => $memberId]);
    foreach ($registeredStatement->fetchAll() as $row) {
        $registeredEventIds[(int) $row['event_id']] = true;
    }
}

$flash = '';
$flashType = '';
$registered = (string) ($_GET['registered'] ?? '');
if ($registered === 'success') {
    $flash = 'You have successfully registered for the event.';
    $flashType = 'success';
} elseif ($registered === 'already') {
    $flash = 'You are already registered for that event.';
    $flashType = 'success';
} elseif ($registered === 'error') {
    $flash = 'Registration failed. The event may no longer be available.';
    $flashType = 'error';
}

$isLoggedIn = $memberId !== null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>KUET Debating Society | Events</title>
	<link rel="stylesheet" href="theme.css">
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
				Browse every KUET Debating Society event in one place. Review upcoming opportunities and
				revisit completed activities to stay connected with the club timeline.
			</p>
			<nav class="cta-group" aria-label="Primary navigation">
				<a class="btn btn-ghost" href="home.html">Home</a>
				<a class="btn btn-primary" href="events.php" aria-current="page">Events</a>
				<?php if ($isLoggedIn): ?>
					<a class="btn btn-secondary" href="dashboard.php">Dashboard</a>
					<a class="btn btn-secondary" href="logout.php">Logout</a>
				<?php else: ?>
					<a class="btn btn-secondary" href="login.html">Login</a>
					<a class="btn btn-secondary" href="signup.html">Signup</a>
				<?php endif; ?>
			</nav>
		</div>
	</header>

	<main>
		<?php if ($flash !== ''): ?>
			<div class="wrap" style="padding-top:20px;">
				<p class="flash-banner flash-<?= $flashType ?>" role="alert"><?= escape_html($flash) ?></p>
			</div>
		<?php endif; ?>

		<section class="wrap reveal delay-1" id="events">
			<h2 class="section-title">Upcoming Events</h2>
			<p class="section-sub">Events on or after today — register to secure your spot.</p>

			<div class="events-grid" id="upcoming-events-grid">
				<?php if ($upcomingEvents === []): ?>
					<p>No upcoming events are available right now.</p>
				<?php else: ?>
					<?php foreach ($upcomingEvents as $event): ?>
						<?php $eventId = (int) $event['id']; ?>
						<article class="event-card upcoming">
							<p class="event-date"><?= escape_html(format_date((string) $event['event_date'])) ?></p>
							<h3><?= escape_html((string) $event['event_name']) ?></h3>
							<p><?= escape_html((string) $event['description']) ?></p>
							<?php if ($isLoggedIn && isset($registeredEventIds[$eventId])): ?>
								<span class="register-btn registered" aria-disabled="true">Registered</span>
							<?php elseif ($isLoggedIn): ?>
								<form action="register.php" method="post">
									<input type="hidden" name="event_id" value="<?= $eventId ?>">
									<button class="register-btn" type="submit">Register</button>
								</form>
							<?php else: ?>
								<a class="register-btn" href="login.html">Login to Register</a>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</section>

		<section class="wrap reveal delay-1" id="past-events">
			<h2 class="section-title">Past Events</h2>
			<p class="section-sub">Completed events, most recent first.</p>

			<div class="events-grid" id="past-events-grid">
				<?php if ($pastEvents === []): ?>
					<p>No past events yet.</p>
				<?php else: ?>
					<?php foreach ($pastEvents as $event): ?>
						<article class="event-card past">
							<p class="event-date"><?= escape_html(format_date((string) $event['event_date'])) ?></p>
							<h3><?= escape_html((string) $event['event_name']) ?></h3>
							<p><?= escape_html((string) $event['description']) ?></p>
						</article>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</section>
	</main>

	<footer>
		<div class="wrap">
			KUET Debating Society | Khulna University of Engineering &amp; Technology, Bangladesh
		</div>
	</footer>
</body>
</html>
