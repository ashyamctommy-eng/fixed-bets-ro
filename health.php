<?php
// ============================================================
// HEALTH CHECK — FIXED BETS RO 🇷🇴
// No database required — returns 200 if web server is alive
// ============================================================

header('Content-Type: application/json');
http_response_code(200);
echo json_encode([
    'status' => 'healthy',
    'app' => 'FIXED BETS RO 🇷🇴',
    'time' => date('Y-m-d H:i:s')
]);
