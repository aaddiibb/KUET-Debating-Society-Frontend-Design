<?php

declare(strict_types=1);

session_start();

if (empty($_SESSION['member_id']) || empty($_SESSION['member_name']) || empty($_SESSION['member_email'])) {
    header('Location: login.html');
    exit;
}

$memberId    = (int) $_SESSION['member_id'];
$memberName  = htmlspecialchars((string) $_SESSION['member_name'], ENT_QUOTES, 'UTF-8');
$memberEmail = htmlspecialchars((string) $_SESSION['member_email'], ENT_QUOTES, 'UTF-8');

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

ensure_members_table($pdo);
ensure_registrations_table($pdo);
ensure_events_table($pdo);

$statement = $pdo->prepare('SELECT student_id, department, created_at FROM members WHERE id = :id LIMIT 1');
$statement->execute(['id' => $memberId]);
$memberRow = $statement->fetch() ?: [];

$studentId       = escape_html((string) ($memberRow['student_id'] ?? '—'));
$department      = escape_html((string) ($memberRow['department'] ?? '—'));
$joinedAt        = (string) ($memberRow['created_at'] ?? '');
$joinedFormatted = $joinedAt !== '' ? escape_html(format_datetime($joinedAt)) : '—';

$today = (new DateTimeImmutable('today'))->format('Y-m-d');

$eventsStatement = $pdo->prepare(
    'SELECT e.id, e.event_name, e.event_date, e.description
     FROM registrations r
     INNER JOIN events e ON e.id = r.event_id
     WHERE r.member_id = :member_id
     ORDER BY e.event_date DESC'
);
$eventsStatement->execute(['member_id' => $memberId]);
$registeredEvents = $eventsStatement->fetchAll();

$upcomingCount  = 0;
$completedCount = 0;
foreach ($registeredEvents as $ev) {
    if ((string) $ev['event_date'] >= $today) {
        $upcomingCount++;
    } else {
        $completedCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>KUET Debating Society | Member Profile</title>
	<link rel="stylesheet" href="theme.css">
	<link rel="stylesheet" href="profile.css">
</head>
<body>
	<header class="topbar">
		<div class="wrap topbar-inner">
			<div class="brand">
				<span class="brand-mark" aria-hidden="true"></span>
				<span>KUET Debating Society</span>
			</div>
			<nav class="topbar-nav" aria-label="Member navigation">
				<a href="dashboard.php" class="topbar-link">Dashboard</a>
				<a href="events.php" class="topbar-link">Events</a>
				<a href="logout.php" class="topbar-link">Logout</a>
			</nav>
		</div>
	</header>

	<main class="profile-main">
		<!-- Intro -->
		<section class="wrap intro-section reveal" aria-labelledby="profile-page-title">
			<p class="kicker">Member Profile</p>
			<h1 id="profile-page-title">Your Profile</h1>
			<p class="intro-text">
				Review your membership details and participation history.
			</p>
		</section>

		<div class="wrap profile-layout reveal delay-1">
			<!-- Profile Card -->
			<article class="card profile-card" aria-labelledby="profile-summary-title">
				<h2 id="profile-summary-title">Membership Details</h2>
				<div class="profile-grid">
					<p><span>Full Name:</span> <?= $memberName ?></p>
					<p><span>Student ID:</span> <?= $studentId ?></p>
					<p><span>Department:</span> <?= $department ?></p>
					<p><span>Email:</span> <?= $memberEmail ?></p>
					<p><span>Member Since:</span> <?= $joinedFormatted ?></p>
					<p><span>Status:</span> Active</p>
				</div>
			</article>

			<!-- Activity Summary -->
			<article class="card activity-card" aria-labelledby="activity-title">
				<h2 id="activity-title">Activity Summary</h2>
				<div class="activity-stats">
					<div class="activity-stat">
						<span class="activity-value"><?= count($registeredEvents) ?></span>
						<span class="activity-label">Events Registered</span>
					</div>
					<div class="activity-stat">
						<span class="activity-value"><?= $upcomingCount ?></span>
						<span class="activity-label">Upcoming</span>
					</div>
					<div class="activity-stat">
						<span class="activity-value"><?= $completedCount ?></span>
						<span class="activity-label">Completed</span>
					</div>
				</div>
				<?php if (count($registeredEvents) === 0): ?>
					<p class="activity-empty">
						You have not registered for any events yet.
						<a href="events.php" class="inline-link">Browse upcoming events &rarr;</a>
					</p>
				<?php endif; ?>
			</article>
		</div>

		<!-- Registered Events History -->
		<?php if ($registeredEvents !== []): ?>
			<section class="wrap reveal delay-1 events-section" aria-labelledby="events-history-title">
				<h2 class="section-heading" id="events-history-title">Event History</h2>
				<div class="events-table-wrap">
					<table class="events-table">
						<thead>
							<tr>
								<th>Event</th>
								<th>Date</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($registeredEvents as $ev): ?>
								<tr>
									<td>
										<span class="event-name-cell"><?= escape_html((string) $ev['event_name']) ?></span>
										<span class="event-desc-cell"><?= escape_html((string) $ev['description']) ?></span>
									</td>
									<td><?= escape_html(format_date((string) $ev['event_date'])) ?></td>
									<td>
										<?php if ((string) $ev['event_date'] >= $today): ?>
											<span class="badge badge-upcoming">Upcoming</span>
										<?php else: ?>
											<span class="badge badge-completed">Completed</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<p style="margin-top: 14px;">
					<a href="events.php" class="inline-link">Register for more events &rarr;</a>
				</p>
			</section>
		<?php endif; ?>
	</main>

	<footer>
		<div class="wrap">
			KUET Debating Society | Khulna University of Engineering &amp; Technology, Bangladesh
		</div>
	</footer>
</body>
</html>
