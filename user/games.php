<?php
// ============================================================
// USER - VIP GAMES
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireVIPAccess($pdo);

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalGames = $pdo->query("SELECT COUNT(*) FROM vip_games WHERE is_archived = 0")->fetchColumn();
$totalPages = ceil($totalGames / $perPage);

$games = $pdo->prepare("
    SELECT * FROM vip_games WHERE is_archived = 0 
    ORDER BY created_at DESC 
    LIMIT ? OFFSET ?
");
$games->execute([$perPage, $offset]);
$gamesList = $games->fetchAll();

$pageTitle = 'VIP Games';
require_once __DIR__ . '/../includes/user_header.php';
?>

<div class="dash-card">
    <div class="dash-card-header">
        <h3>🎯 VIP Games</h3>
        <span style="color: var(--text-muted); font-size: 0.85rem;"><?= $totalGames ?> total</span>
    </div>
    <div class="dash-card-body">
        <?php if (count($gamesList) > 0): ?>
        <?php foreach ($gamesList as $game): ?>
        <div class="game-card">
            <div class="game-card-header">
                <div>
                    <div class="game-match"><?= e($game['match_name']) ?></div>
                    <?php if ($game['league']): ?>
                    <div class="game-league">🏆 <?= e($game['league']) ?></div>
                    <?php endif; ?>
                </div>
                <div style="text-align: right;">
                    <?php if ($game['confidence']): ?>
                    <span class="game-confidence <?= strtolower($game['confidence']) ?>"><?= e($game['confidence']) ?></span>
                    <?php endif; ?>
                    <div class="game-odds" style="margin-top: 4px;">@<?= number_format($game['odds'], 2) ?></div>
                </div>
            </div>
            
            <div style="font-size: 1.05rem; color: var(--gold); font-weight: 700; margin-bottom: 0.75rem; padding: 0.5rem 0; border-top: 1px solid var(--border-color);">
                🎯 <?= e($game['selection']) ?>
            </div>
            
            <div class="game-details">
                <div class="game-detail">
                    <span class="game-detail-label">Date</span>
                    <span class="game-detail-value"><?= formatDate($game['match_date'], 'd M Y') ?></span>
                </div>
                <?php if ($game['match_time']): ?>
                <div class="game-detail">
                    <span class="game-detail-label">Time</span>
                    <span class="game-detail-value">⏰ <?= $game['match_time'] ?></span>
                </div>
                <?php endif; ?>
                <div class="game-detail">
                    <span class="game-detail-label">Odds</span>
                    <span class="game-detail-value" style="color: var(--gold); font-weight: 700;">@<?= number_format($game['odds'], 2) ?></span>
                </div>
                <div class="game-detail">
                    <span class="game-detail-label">Result</span>
                    <span class="game-detail-value"><?= getResultBadge($game['result_status']) ?></span>
                </div>
                <div class="game-detail">
                    <span class="game-detail-label">Posted</span>
                    <span class="game-detail-value" style="font-size: 0.8rem; color: var(--text-muted);">
                        <?= timeElapsed($game['created_at']) ?>
                    </span>
                </div>
            </div>
            
            <?php if ($game['notes']): ?>
            <div class="game-notes">
                <strong style="color: var(--text-secondary);">📝 Notes:</strong><br>
                <?= nl2br(e($game['notes'])) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        
        <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem;">
            <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="btn btn-sm btn-secondary">← Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="btn btn-sm btn-secondary">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">🎯</span>
            <p>No VIP games available yet. Check back soon for premium picks!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/user_footer.php'; ?>
