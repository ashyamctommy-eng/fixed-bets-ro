<?php
// Public, settled-pick history for Fixed Bets RO.

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/functions.php';

$allowedFilters = ['all', 'win', 'loss', 'void', 'push'];
$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, $allowedFilters, true)) $filter = 'all';

$selectedDate = trim($_GET['date'] ?? '');
if ($selectedDate !== '' && !validateDate($selectedDate)) {
    $selectedDate = '';
}

$summarySql = "SELECT result_status, COUNT(*) AS total FROM vip_games WHERE result_status IN ('win', 'loss', 'void', 'push')";
$summaryParams = [];
if ($selectedDate !== '') {
    $summarySql .= ' AND match_date = ?';
    $summaryParams[] = $selectedDate;
}
$summarySql .= ' GROUP BY result_status';
$summaryStmt = $pdo->prepare($summarySql);
$summaryStmt->execute($summaryParams);
$summary = ['win' => 0, 'loss' => 0, 'void' => 0, 'push' => 0];
foreach ($summaryStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $summary[$row['result_status']] = (int)$row['total'];
}

$where = ["result_status IN ('win', 'loss', 'void', 'push')"];
$params = [];
if ($filter !== 'all') {
    $where[] = 'result_status = ?';
    $params[] = $filter;
}
if ($selectedDate !== '') {
    $where[] = 'match_date = ?';
    $params[] = $selectedDate;
}
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM vip_games WHERE " . implode(' AND ', $where));
$countStmt->execute($params);
$totalResults = (int)$countStmt->fetchColumn();
$perPage = 20;
$totalPages = max(1, (int)ceil($totalResults / $perPage));
$page = min(max(1, (int)($_GET['page'] ?? 1)), $totalPages);
$offset = ($page - 1) * $perPage;
$resultsStmt = $pdo->prepare("
    SELECT id, match_name, league, selection, odds, match_date, match_time,
           result_status, final_score, settled_at, created_at
    FROM vip_games
    WHERE " . implode(' AND ', $where) . "
    ORDER BY match_date DESC, settled_at DESC, created_at DESC
    LIMIT $perPage OFFSET $offset
");
$resultsStmt->execute($params);
$results = $resultsStmt->fetchAll(PDO::FETCH_ASSOC);

$settledRecord = $summary['win'] + $summary['loss'];
$winRate = $settledRecord > 0 ? round(($summary['win'] / $settledRecord) * 100) : 0;
$yesterday = date('Y-m-d', strtotime('-1 day'));
$pageTitle = 'Results History';
$themeClass = getSiteSetting($pdo, 'dashboard_theme') ?? 'dark';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <meta name="description" content="Review Fixed Bets RO's dated record of settled football predictions, including wins, losses, voids, and pushes.">
    <title><?= e($pageTitle) ?> | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/results.css">
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/favicon.svg">
</head>
<body class="theme-<?= e($themeClass) ?>">
<div class="results-site">
    <header class="results-topbar">
        <a class="results-brand" href="<?= SITE_URL ?>/"><?= e(SITE_NAME) ?></a>
        <nav aria-label="Main navigation">
            <a class="active" href="<?= SITE_URL ?>/results.php"><?= svgIcon('chart') ?> Results</a>
            <a href="<?= SITE_URL ?>/login.php"><?= svgIcon('user') ?> Member login</a>
        </nav>
    </header>

    <main class="public-results">
        <section class="results-hero">
            <div class="results-eyebrow"><?= svgIcon('shield-check') ?> Public record</div>
            <h1>Settled picks. Clear outcomes.</h1>
            <p>Review the actual published selections and recorded results. Upcoming VIP picks remain member-only.</p>
        </section>

        <section class="results-summary" aria-label="Results summary">
            <article class="summary-card">
                <span class="summary-icon"><?= svgIcon('check') ?></span>
                <strong><?= $summary['win'] ?></strong>
                <span>Wins</span>
            </article>
            <article class="summary-card">
                <span class="summary-icon"><?= svgIcon('x') ?></span>
                <strong><?= $summary['loss'] ?></strong>
                <span>Losses</span>
            </article>
            <article class="summary-card">
                <span class="summary-icon"><?= svgIcon('minus') ?></span>
                <strong><?= $summary['void'] + $summary['push'] ?></strong>
                <span>Voids and pushes</span>
            </article>
            <article class="summary-card summary-card-highlight">
                <span class="summary-icon"><?= svgIcon('chart') ?></span>
                <strong><?= $winRate ?>%</strong>
                <span>Win rate <small>(wins ÷ wins and losses)</small></span>
            </article>
        </section>

        <section class="results-panel">
            <div class="results-panel-head">
                <div>
                    <h2><?= svgIcon('history') ?> Results history</h2>
                    <p>Only settled picks are shown. Archived picks remain in this record.</p>
                </div>
                <a class="text-link" href="?date=<?= e($yesterday) ?>"><?= svgIcon('calendar') ?> Yesterday</a>
            </div>

            <form method="GET" class="results-filters">
                <label>
                    <span>Match date</span>
                    <input type="date" name="date" value="<?= e($selectedDate) ?>">
                </label>
                <label>
                    <span>Outcome</span>
                    <select name="filter">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All outcomes</option>
                        <option value="win" <?= $filter === 'win' ? 'selected' : '' ?>>Wins</option>
                        <option value="loss" <?= $filter === 'loss' ? 'selected' : '' ?>>Losses</option>
                        <option value="void" <?= $filter === 'void' ? 'selected' : '' ?>>Voids</option>
                        <option value="push" <?= $filter === 'push' ? 'selected' : '' ?>>Pushes</option>
                    </select>
                </label>
                <button class="btn btn-primary btn-sm" type="submit"><?= svgIcon('filter') ?> Apply filters</button>
                <a class="btn btn-secondary btn-sm" href="<?= SITE_URL ?>/results.php">Reset</a>
            </form>

            <?php if ($results): ?>
            <p class="results-count">Showing <?= $offset + 1 ?>–<?= $offset + count($results) ?> of <?= $totalResults ?> settled picks</p>
            <div class="public-result-list">
                <?php foreach ($results as $result): ?>
                <article class="public-result">
                    <div class="public-result-main">
                        <div class="public-result-kicker">
                            <span><?= e($result['league'] ?: 'Football') ?></span>
                            <time datetime="<?= e($result['match_date']) ?>"><?= formatDate($result['match_date'], 'd M Y') ?></time>
                        </div>
                        <h3><?= e($result['match_name']) ?></h3>
                        <p class="public-result-selection"><?= e($result['selection']) ?></p>
                        <div class="public-result-meta">
                            <span>Odds <strong><?= number_format((float)$result['odds'], 2) ?></strong></span>
                            <?php if (!empty($result['final_score'])): ?>
                            <span>Final score <strong><?= e($result['final_score']) ?></strong></span>
                            <?php endif; ?>
                            <?php if (!empty($result['settled_at'])): ?>
                            <span>Settled <time datetime="<?= e($result['settled_at']) ?>"><?= formatDateTime($result['settled_at'], 'd M, H:i') ?></time></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="public-result-outcome"><?= getResultBadge($result['result_status']) ?></div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php if ($totalPages > 1): ?>
            <nav class="results-pagination" aria-label="Results pages">
                <?php if ($page > 1): ?>
                    <?php $previousQuery = ['page' => $page - 1, 'filter' => $filter]; if ($selectedDate !== '') $previousQuery['date'] = $selectedDate; ?>
                    <a class="btn btn-sm btn-secondary" href="?<?= e(http_build_query($previousQuery)) ?>">Previous</a>
                <?php endif; ?>
                <span>Page <?= $page ?> of <?= $totalPages ?></span>
                <?php if ($page < $totalPages): ?>
                    <?php $nextQuery = ['page' => $page + 1, 'filter' => $filter]; if ($selectedDate !== '') $nextQuery['date'] = $selectedDate; ?>
                    <a class="btn btn-sm btn-secondary" href="?<?= e(http_build_query($nextQuery)) ?>">Next</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
            <?php else: ?>
            <div class="results-empty">
                <?= svgIcon('chart', 'empty-icon-svg') ?>
                <h3>No settled picks for this selection</h3>
                <p>Choose another date or show all outcomes to review the full record.</p>
            </div>
            <?php endif; ?>
        </section>

        <p class="results-disclaimer">Past results do not guarantee future outcomes. Betting involves risk; only bet what you can afford to lose.</p>
    </main>

    <footer class="results-footer">
        <span>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?></span>
        <a href="<?= SITE_URL ?>/"><?= svgIcon('home') ?> Home</a>
    </footer>
</div>
</body>
</html>
