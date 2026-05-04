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

$statement = $pdo->prepare('SELECT student_id, department, created_at FROM members WHERE id = :id LIMIT 1');
$statement->execute(['id' => $memberId]);
$memberRow = $statement->fetch() ?: [];

$studentId  = escape_html((string) ($memberRow['student_id'] ?? '—'));
$department = escape_html((string) ($memberRow['department'] ?? '—'));
$joinedAt   = (string) ($memberRow['created_at'] ?? '');
$joinedFormatted = $joinedAt !== '' ? escape_html(format_datetime($joinedAt)) : '—';
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
		<section class="wrap intro-section reveal" aria-labelledby="profile-page-title">
			<p class="kicker">Member Profile</p>
			<h1 id="profile-page-title">Your Profile Information</h1>
			<p class="intro-text">
				Review your membership details for club communications and events.
			</p>
		</section>

		<section class="wrap profile-section reveal delay-1" aria-label="Member profile details">
			<article class="card profile-card">
				<h2>Profile Summary</h2>
				<div class="profile-grid">
					<p><span>Full Name:</span> <?= $memberName ?></p>
					<p><span>Student ID:</span> <?= $studentId ?></p>
					<p><span>Department:</span> <?= $department ?></p>
					<p><span>Email:</span> <?= $memberEmail ?></p>
					<p><span>Join Date:</span> <?= $joinedFormatted ?></p>
					<p><span>Status:</span> Active</p>
				</div>
			</article>
		</section>
	</main>

	<footer>
		<div class="wrap">
			KUET Debating Society | Khulna University of Engineering &amp; Technology, Bangladesh
		</div>
	</footer>
</body>
</html>
