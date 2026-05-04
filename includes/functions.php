<?php

declare(strict_types=1);

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

function ensure_contacts_table(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS contacts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}

function ensure_all_tables(PDO $pdo): void
{
    ensure_members_table($pdo);
    ensure_events_table($pdo);
    ensure_notices_table($pdo);
    ensure_registrations_table($pdo);
}

/** Formats a Y-m-d date string as "Month D, YYYY". */
function format_date(string $dateValue): string
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateValue);
    if ($date === false) {
        return $dateValue;
    }
    return $date->format('F j, Y');
}

/** Formats a "Y-m-d H:i:s" timestamp as "Month D, YYYY". */
function format_datetime(string $datetimeValue): string
{
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $datetimeValue);
    if ($date === false) {
        return $datetimeValue;
    }
    return $date->format('F j, Y');
}
