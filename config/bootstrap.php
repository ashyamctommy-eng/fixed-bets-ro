<?php
// ============================================================
// BOOTSTRAP — FIXED BETS RO 🇷🇴
// ============================================================
// Load this early in every request to ensure DB schema exists.
// Safe to include multiple times — migration is idempotent.
// ============================================================

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/config.php';

// Run auto-migration on every request until tables exist
// (the check inside is cheap: one "SHOW TABLES" query)
require_once __DIR__ . '/schema_check.php';
runSchemaMigration($pdo);
