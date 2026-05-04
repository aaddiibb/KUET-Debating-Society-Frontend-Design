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

$memberStatement = $pdo->prepare('SELECT department, created_at FROM members WHERE id = :id LIMIT 1');
$memberStatement->execute(['id' => $memberId]);
$memberRow = $memberStatement->fetch() ?: [];

$department = (string) ($memberRow['department'] ?? '');
$joinedAt   = (string) ($memberRow['created_at'] ?? '');

$today = (new DateTimeImmutable('today'))->format('Y-m-d');

$upcomingStatement = $pdo->prepare(
    'SELECT event_name, event_date FROM events WHERE event_date >= :today ORDER BY event_date ASC LIMIT 3'
);
$upcomingStatement->execute(['today' => $today]);
$upcomingEvents = $upcomingStatement->fetchAll();

$noticesStatement = $pdo->query('SELECT notice_title, created_at FROM notices ORDER BY created_at DESC LIMIT 3');
$recentNotices = $noticesStatement->fetchAll();

$registrationsStatement = $pdo->prepare(
    'SELECT e.event_name, e.event_date
     FROM registrations r
     INNER JOIN events e ON e.id = r.event_id
     WHERE r.member_id = :member_id
     ORDER BY e.event_date ASC
     LIMIT 3'
);
$registrationsStatement->execute(['member_id' => $memberId]);
$myRegistrations = $registrationsStatement->fetchAll();
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
		<section class="wrap welcome-section reveal" aria-labelledby="welcome-title">
			<p class="kicker">Member Area</p>
			<h1 id="welcome-title">Welcome back, <span class="member-name"><?= $memberName ?></span></h1>
			<p class="welcome-text">
				Track your upcoming club activities, read latest announcements, and view your profile details in one place.
			</p>
		</section>

		<section class="wrap cards-section reveal delay-1" aria-label="Member dashboard cards">
			<article class="card info-card" aria-labelledby="events-title">
				<h2 id="events-title">Upcoming Events</h2>
				<ul class="info-list">
					<?php if ($upcomingEvents === []): ?>
						<li>
							<span class="item-title">No upcoming events yet</span>
							<span class="item-meta">Check back later or browse the events page.</span>
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
			</article>

			<article class="card info-card" aria-labelledby="notices-title">
				<h2 id="notices-title">Recent Notices</h2>
				<ul class="info-list">
					<?php if ($recentNotices === []): ?>
						<li>
							<span class="item-title">No notices posted yet</span>
							<span class="item-meta">Admins can post notices from the admin panel.</span>
						</li>
					<?php else: ?>
						<?php foreach ($recentNotices as $notice): ?>
							<li>
								<span class="item-title"><?= escape_html((string) $notice['notice_title']) ?></span>
								<span class="item-meta"><?= escape_html(format_datetime((string) $notice['created_at'])) ?></span>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</article>

			<article class="card info-card" aria-labelledby="profile-title">
				<h2 id="profile-title">Your Membership</h2>
				<div class="profile-grid">
					<p><span>Name:</span> <?= $memberName ?></p>
					<p><span>Department:</span> <?= escape_html($department !== '' ? $department : '—') ?></p>
					<p><span>Status:</span> Active</p>
					<p><span>Joined:</span> <?= escape_html($joinedAt !== '' ? format_datetime($joinedAt) : '—') ?></p>
					<p><span>Email:</span> <?= $memberEmail ?></p>
				</div>
				<?php if ($myRegistrations !== []): ?>
					<ul class="info-list" style="margin-top:14px;">
						<?php foreach ($myRegistrations as $registration): ?>
							<li>
								<span class="item-title"><?= escape_html((string) $registration['event_name']) ?></span>
								<span class="item-meta">Registered &bull; <?= escape_html(format_date((string) $registration['event_date'])) ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					<p class="welcome-text" style="margin-top:14px;">You have not registered for any events yet.</p>
				<?php endif; ?>
			</article>
		</section>
	</main>
</body>
</html>
