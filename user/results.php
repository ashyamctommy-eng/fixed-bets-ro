<?php
// ============================================================
// USER - RESULTS
// FIXED BETS RO
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireVIPAccess($pdo);

// Stats
$wins = (int)$pdo->query("SELECT COUNT(*) FROM vip_games WHERE result_status = 'win' AND is_archived = 0")->fetchColumn();
$losses = (int)$pdo->query("SELECT COUNT(*) FROM vip_games WHERE result_status = 'loss' AND is_archived = 0")->fetchColumn();
$pending = (int)$pdo->query("SELECT COUNT(*) FROM vip_games WHERE result_status = 'pending' AND is_archived = 0")->fetchColumn();
$voids = (int)$pdo->query("SELECT COUNT(*) FROM vip_games WHERE result_status IN ('void', 'push') AND is_archived = 0")->fetchColumn();
$settled = $wins + $losses;
$winRate = $settled > 0 ? round(($wins / $settled) * 100) : 0;

// Filter
$filter = $_GET['filter'] ?? 'all';
$where = "WHERE is_archived = 0";
if ($filter === 'win') $where = "WHERE result_status = 'win' AND is_archived = 0";
if ($filter === 'loss') $where = "WHERE result_status = 'loss' AND is_archived = 0";
if ($filter === 'pending') $where = "WHERE result_status = 'pending' AND is_archived = 0";
if ($filter === 'void') $where = "WHERE result_status = 'void' AND is_archived = 0";
if ($filter === 'push') $where = "WHERE result_status = 'push' AND is_archived = 0";

$results = $pdo->query("SELECT * FROM vip_games $where ORDER BY match_date DESC, created_at DESC")->fetchAll();

$pageTitle = 'Results';
require_once __DIR__ . '/../includes/user_header.php';
?>

<!-- Stats Overview -->
<div class="dash-grid" style="margin-bottom: 1.5rem;">
    <div class="stat-card" style="background: rgba(40,167,69,0.1); border-color: rgba(40,167,69,0.3);">
        <div class="stat-icon"><?= svgIcon('check', 'stat-icon-svg') ?></div>
        <div class="stat-value" style="color: #5cdb7a;"><?= $wins ?></div>
        <div class="stat-label">Wins</div>
    </div>
    <div class="stat-card" style="background: rgba(220,53,69,0.1); border-color: rgba(220,53,69,0.3);">
        <div class="stat-icon"><?= svgIcon('x', 'stat-icon-svg') ?></div>
        <div class="stat-value" style="color: #ff6b7a;"><?= $losses ?></div>
        <div class="stat-label">Losses</div>
    </div>
    <div class="stat-card" style="background: rgba(255,193,7,0.1); border-color: rgba(255,193,7,0.3);">
        <div class="stat-icon"><?= svgIcon('clock', 'stat-icon-svg') ?></div>
        <div class="stat-value" style="color: #ffe066;"><?= $pending ?></div>
        <div class="stat-label">Pending</div>
    </div>
    <div class="stat-card gold">
        <div class="stat-icon"><?= svgIcon('chart', 'stat-icon-svg') ?></div>
        <div class="stat-value" style="color: var(--gold);"><?= $winRate ?>%</div>
        <div class="stat-label">Win Rate</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><?= svgIcon('minus', 'stat-icon-svg') ?></div>
        <div class="stat-value"><?= $voids ?></div>
        <div class="stat-label">Voids and pushes</div>
    </div>
</div>

<div class="dash-card">
    <div class="dash-card-header">
        <h3><?= svgIcon('history') ?> Results History</h3>
        <div class="filter-bar">
            <a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="?filter=win" class="btn btn-sm <?= $filter === 'win' ? 'btn-primary' : 'btn-secondary' ?>">Wins</a>
            <a href="?filter=loss" class="btn btn-sm <?= $filter === 'loss' ? 'btn-primary' : 'btn-secondary' ?>">Losses</a>
            <a href="?filter=pending" class="btn btn-sm <?= $filter === 'pending' ? 'btn-primary' : 'btn-secondary' ?>">Pending</a>
            <a href="?filter=void" class="btn btn-sm <?= $filter === 'void' ? 'btn-primary' : 'btn-secondary' ?>">Voids</a>
            <a href="?filter=push" class="btn btn-sm <?= $filter === 'push' ? 'btn-primary' : 'btn-secondary' ?>">Pushes</a>
        </div>
    </div>
    <div class="dash-card-body" style="padding: 0;">
        <?php if (count($results) > 0): ?>
        <?php foreach ($results as $r): ?>
        <div class="result-item">
            <div class="result-info">
                <div class="result-match"><?= e($r['match_name']) ?></div>
                <?php if ($r['league']): ?>
                <div class="result-league"><?= svgIcon('trophy') ?> <?= e($r['league']) ?></div>
                <?php endif; ?>
                <div class="result-selection"><?= svgIcon('target') ?> <?= e($r['selection']) ?> @<?= number_format($r['odds'], 2) ?></div>
                <small style="color: var(--text-muted);"><?= svgIcon('calendar') ?> <?= formatDate($r['match_date'], 'd M Y') ?></small>
                <?php if (!empty($r['final_score'])): ?>
                <small class="settled-score">Final score <?= e($r['final_score']) ?></small>
                <?php endif; ?>
            </div>
            <div style="text-align: center;">
                <?php if ($r['confidence']): ?>
                <span class="game-confidence <?= strtolower($r['confidence']) ?>" style="display: inline-block;"><?= e($r['confidence']) ?></span>
                <?php endif; ?>
            </div>
            <div style="text-align: right;">
                <?= getResultBadge($r['result_status']) ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state">
            <?= svgIcon('chart', 'empty-icon-svg') ?>
            <p>No results found for this filter.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/user_footer.php'; ?>
