<?php

declare(strict_types=1);

session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.html');
    exit;
}

require __DIR__ . '/db.php';
require __DIR__ . '/includes/functions.php';

ensure_all_tables($pdo);

$members = $pdo->query('SELECT id, full_name, student_id, department, email, created_at FROM members ORDER BY created_at DESC')->fetchAll();
$events  = $pdo->query('SELECT id, event_name, event_date, description, created_at FROM events ORDER BY event_date ASC')->fetchAll();
$notices = $pdo->query('SELECT id, notice_title, notice_body, created_at FROM notices ORDER BY created_at DESC')->fetchAll();

$totalRegistrations = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();

$today = (new DateTimeImmutable('today'))->format('Y-m-d');
$upcomingCount = (int) $pdo->prepare('SELECT COUNT(*) FROM events WHERE event_date >= :today')->execute(['today' => $today]);
$upcomingStmt  = $pdo->prepare('SELECT COUNT(*) FROM events WHERE event_date >= :today');
$upcomingStmt->execute(['today' => $today]);
$upcomingCount = (int) $upcomingStmt->fetchColumn();

$flash = (string) ($_GET['msg'] ?? '');
$flashType = (string) ($_GET['type'] ?? 'success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>KUET Debating Society | Admin Panel</title>
	<link rel="stylesheet" href="theme.css">
	<link rel="stylesheet" href="admin.css">
</head>
<body>
	<header class="topbar">
		<div class="wrap topbar-inner">
			<div class="brand">
				<span class="brand-mark" aria-hidden="true"></span>
				<span>KUET Debating Society</span>
			</div>
			<nav class="topbar-nav" aria-label="Admin navigation">
				<span style="font-family:'Space Grotesk',sans-serif; font-weight:700; color:var(--muted); font-size:0.9rem;">Admin Panel</span>
				<a href="logout.php" class="topbar-link">Logout</a>
			</nav>
		</div>
	</header>

	<main class="admin-main">
		<div class="wrap">
			<?php if ($flash !== ''): ?>
				<p class="admin-flash <?= escape_html($flashType) ?>" role="alert"><?= escape_html($flash) ?></p>
			<?php endif; ?>

			<!-- Summary Stats -->
			<section class="admin-stats" aria-label="Summary statistics">
				<div class="admin-stat-card">
					<span class="admin-stat-value"><?= count($members) ?></span>
					<span class="admin-stat-label">Total Members</span>
				</div>
				<div class="admin-stat-card">
					<span class="admin-stat-value"><?= count($events) ?></span>
					<span class="admin-stat-label">Total Events</span>
				</div>
				<div class="admin-stat-card">
					<span class="admin-stat-value"><?= $upcomingCount ?></span>
					<span class="admin-stat-label">Upcoming Events</span>
				</div>
				<div class="admin-stat-card">
					<span class="admin-stat-value"><?= $totalRegistrations ?></span>
					<span class="admin-stat-label">Total Registrations</span>
				</div>
				<div class="admin-stat-card">
					<span class="admin-stat-value"><?= count($notices) ?></span>
					<span class="admin-stat-label">Active Notices</span>
				</div>
			</section>

			<!-- Members -->
			<section class="admin-section" aria-labelledby="members-title">
				<h2 id="members-title">All Members</h2>
				<?php if ($members === []): ?>
					<p class="empty-state">No members yet.</p>
				<?php else: ?>
					<div class="table-wrap">
						<table class="admin-table">
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
										<td><?= (int) $member['id'] ?></td>
										<td><?= escape_html((string) $member['full_name']) ?></td>
										<td><?= escape_html((string) $member['student_id']) ?></td>
										<td><?= escape_html((string) $member['department']) ?></td>
										<td><?= escape_html((string) $member['email']) ?></td>
										<td><?= escape_html(format_datetime((string) $member['created_at'])) ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>
			</section>

			<!-- Events -->
			<section class="admin-section" aria-labelledby="events-title">
				<h2 id="events-title">All Events</h2>
				<?php if ($events === []): ?>
					<p class="empty-state">No events yet.</p>
				<?php else: ?>
					<div class="table-wrap">
						<table class="admin-table">
							<thead>
								<tr>
									<th>ID</th>
									<th>Name</th>
									<th>Date</th>
									<th>Description</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($events as $event): ?>
									<tr>
										<td><?= (int) $event['id'] ?></td>
										<td><?= escape_html((string) $event['event_name']) ?></td>
										<td><?= escape_html(format_date((string) $event['event_date'])) ?></td>
										<td><?= escape_html((string) $event['description']) ?></td>
										<td>
											<form action="delete_event.php" method="post" onsubmit="return confirm('Delete this event?')">
												<input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
												<button type="submit" class="btn-delete">Delete</button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<h3>Add Event</h3>
				<form action="add_event.php" method="post" class="admin-form">
					<div class="form-field">
						<label for="event_name">Event Name</label>
						<input type="text" id="event_name" name="event_name" required>
					</div>
					<div class="form-field">
						<label for="event_date">Event Date</label>
						<input type="date" id="event_date" name="event_date" required>
					</div>
					<div class="form-field">
						<label for="description">Description</label>
						<textarea id="description" name="description" rows="4" required></textarea>
					</div>
					<button type="submit" class="btn-admin-submit">Add Event</button>
				</form>
			</section>

			<!-- Notices -->
			<section class="admin-section" aria-labelledby="notices-title">
				<h2 id="notices-title">All Notices</h2>
				<?php if ($notices === []): ?>
					<p class="empty-state">No notices yet.</p>
				<?php else: ?>
					<div class="table-wrap">
						<table class="admin-table">
							<thead>
								<tr>
									<th>ID</th>
									<th>Title</th>
									<th>Body</th>
									<th>Posted</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($notices as $notice): ?>
									<tr>
										<td><?= (int) $notice['id'] ?></td>
										<td><?= escape_html((string) $notice['notice_title']) ?></td>
										<td><?= escape_html((string) $notice['notice_body']) ?></td>
										<td><?= escape_html(format_datetime((string) $notice['created_at'])) ?></td>
										<td>
											<form action="delete_notice.php" method="post" onsubmit="return confirm('Delete this notice?')">
												<input type="hidden" name="notice_id" value="<?= (int) $notice['id'] ?>">
												<button type="submit" class="btn-delete">Delete</button>
											</form>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				<?php endif; ?>

				<h3>Add Notice</h3>
				<form action="add_notice.php" method="post" class="admin-form">
					<div class="form-field">
						<label for="notice_title">Notice Title</label>
						<input type="text" id="notice_title" name="notice_title" required>
					</div>
					<div class="form-field">
						<label for="notice_body">Notice Body</label>
						<textarea id="notice_body" name="notice_body" rows="4" required></textarea>
					</div>
					<button type="submit" class="btn-admin-submit">Add Notice</button>
				</form>
			</section>
		</div>
	</main>
</body>
</html>
