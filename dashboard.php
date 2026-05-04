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

ensure_all_tables($pdo);

$today = (new DateTimeImmutable('today'))->format('Y-m-d');

$upcomingStatement = $pdo->prepare(
    'SELECT event_name, event_date FROM events WHERE event_date >= :today ORDER BY event_date ASC LIMIT 3'
);
$upcomingStatement->execute(['today' => $today]);
$upcomingEvents = $upcomingStatement->fetchAll();

$noticesStatement = $pdo->query('SELECT notice_title, notice_body, created_at FROM notices ORDER BY created_at DESC LIMIT 3');
$recentNotices = $noticesStatement->fetchAll();

$registrationsStatement = $pdo->prepare(
    'SELECT e.event_name, e.event_date
     FROM registrations r
     INNER JOIN events e ON e.id = r.event_id
     WHERE r.member_id = :member_id
     ORDER BY e.event_date ASC'
);
$registrationsStatement->execute(['member_id' => $memberId]);
$myRegistrations = $registrationsStatement->fetchAll();

$totalRegistrations = count($myRegistrations);

$upcomingRegistrations = array_filter($myRegistrations, static function (array $r) use ($today): bool {
    return (string) $r['event_date'] >= $today;
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>KUET Debating Society | Member Dashboard</title>
	<link rel="stylesheet" href="theme.css">
	<link rel="stylesheet" href="dashboard.css">
</head>
<body>
	<header class="topbar">
		<div class="wrap topbar-inner">
			<div class="brand">
				<span class="brand-mark" aria-hidden="true"></span>
				<span>KUET Debating Society</span>
			</div>
			<nav class="topbar-nav" aria-label="Member navigation">
				<a href="events.php" class="topbar-link">Events</a>
				<a href="profile.php" class="topbar-link">Profile</a>
				<a href="logout.php" class="topbar-link">Logout</a>
			</nav>
		</div>
	</header>

	<main class="dashboard-main">
		<!-- Welcome -->
		<section class="wrap welcome-section reveal" aria-labelledby="welcome-title">
			<p class="kicker">Member Area</p>
			<h1 id="welcome-title">Welcome back, <span class="member-name"><?= $memberName ?></span></h1>
			<p class="welcome-text">
				Here is your activity summary. Browse upcoming events, read the latest notices, and track your registrations.
			</p>
		</section>

		<!-- Quick Stats -->
		<section class="wrap quick-stats reveal delay-1" aria-label="Activity summary">
			<div class="stat-card">
				<span class="stat-value"><?= $totalRegistrations ?></span>
				<span class="stat-label">Events Registered</span>
			</div>
			<div class="stat-card">
				<span class="stat-value"><?= count($upcomingRegistrations) ?></span>
				<span class="stat-label">Upcoming</span>
			</div>
			<div class="stat-card">
				<span class="stat-value"><?= count($upcomingEvents) ?></span>
				<span class="stat-label">New Events Available</span>
			</div>
			<div class="stat-card">
				<a href="events.php" class="stat-action">Register for an Event &rarr;</a>
			</div>
		</section>

		<!-- Cards -->
		<section class="wrap cards-section reveal delay-1" aria-label="Member dashboard cards">
			<article class="card info-card" aria-labelledby="events-title">
				<h2 id="events-title">Upcoming Events</h2>
				<ul class="info-list">
					<?php if ($upcomingEvents === []): ?>
						<li>
							<span class="item-title">No upcoming events yet</span>
							<span class="item-meta">Check back later or <a href="events.php" class="inline-link">browse all events</a>.</span>
						</li>
					<?php else: ?>
						<?php foreach ($upcomingEvents as $event): ?>
							<li>
								<span class="item-title"><?= escape_html((string) $event['event_name']) ?></span>
								<span class="item-meta"><?= escape_html(format_date((string) $event['event_date'])) ?></span>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
				<a href="events.php" class="card-footer-link">View all events &rarr;</a>
			</article>

			<article class="card info-card" aria-labelledby="notices-title">
				<h2 id="notices-title">Recent Notices</h2>
				<ul class="info-list">
					<?php if ($recentNotices === []): ?>
						<li>
							<span class="item-title">No notices posted yet</span>
							<span class="item-meta">Check back later for club announcements.</span>
						</li>
					<?php else: ?>
						<?php foreach ($recentNotices as $notice): ?>
							<li>
								<span class="item-title"><?= escape_html((string) $notice['notice_title']) ?></span>
								<span class="item-meta"><?= escape_html((string) $notice['notice_body']) ?></span>
								<span class="item-date"><?= escape_html(format_datetime((string) $notice['created_at'])) ?></span>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</article>

		</section>
	</main>
</body>
</html>
