<?php
// ============================================================
// ADMIN DASHBOARD
// FIXED BETS RO
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Dashboard';

// Get stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'vip_user'")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'vip_user' AND vip_access = 1")->fetchColumn();
$vipUsers = $activeUsers;
$restrictedUsers = $pdo->query("SELECT COUNT(*) FROM users u JOIN custom_statuses s ON u.status_id = s.id WHERE u.role = 'vip_user' AND s.restrict_access = 1")->fetchColumn();
$totalGames = $pdo->query("SELECT COUNT(*) FROM vip_games WHERE is_archived = 0")->fetchColumn();
$totalNotifications = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
$today = date('Y-m-d');
$newUsersTodayStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'vip_user' AND DATE(created_at) = ?");
$newUsersTodayStmt->execute([$today]);
$newUsersToday = $newUsersTodayStmt->fetchColumn();
$wins = (int)$pdo->query("SELECT COUNT(*) FROM vip_games WHERE result_status = 'win'")->fetchColumn();
$losses = (int)$pdo->query("SELECT COUNT(*) FROM vip_games WHERE result_status = 'loss'")->fetchColumn();
$pendingPicks = (int)$pdo->query("SELECT COUNT(*) FROM vip_games WHERE result_status = 'pending' AND is_archived = 0")->fetchColumn();
$settledPicks = $wins + $losses;
$winRate = $settledPicks > 0 ? round(($wins / $settledPicks) * 100) : 0;

// Recent users
$recentUsers = $pdo->query("
    SELECT u.*, cs.name as status_name, cs.color as status_color, cs.icon as status_icon
    FROM users u
    LEFT JOIN custom_statuses cs ON u.status_id = cs.id
    WHERE u.role = 'vip_user'
    ORDER BY u.created_at DESC
    LIMIT 5
")->fetchAll();

// Recent games
$recentGames = $pdo->query("
    SELECT * FROM vip_games
    WHERE is_archived = 0
    ORDER BY match_date DESC, created_at DESC
    LIMIT 5
")->fetchAll();

$recentActivity = $pdo->query("
    SELECT a.*, u.full_name, u.username
    FROM activity_log a
    LEFT JOIN users u ON u.id = a.user_id
    ORDER BY a.created_at DESC
    LIMIT 6
")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="stats-grid">
    <div class="stat-card gold">
        <span class="stat-icon"><?= svgIcon('users', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('star', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $vipUsers ?></div>
        <div class="stat-label">VIP Active</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('lock', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $restrictedUsers ?></div>
        <div class="stat-label">Restricted</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('user', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $newUsersToday ?></div>
        <div class="stat-label">New Today</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('target', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $totalGames ?></div>
        <div class="stat-label">Active Picks</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('clock', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $pendingPicks ?></div>
        <div class="stat-label">Awaiting Result</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('trophy', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $settledPicks ?></div>
        <div class="stat-label">Settled Picks</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('chart', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $winRate ?>%</div>
        <div class="stat-label">Win Rate <small>(wins/losses only)</small></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><?= svgIcon('bell', 'stat-icon-svg') ?></span>
        <div class="stat-value"><?= $totalNotifications ?></div>
        <div class="stat-label">Notifications</div>
    </div>
</div>

<div class="dash-grid admin-overview-grid">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h2><?= svgIcon('users') ?> Recent Users</h2>
            <a href="users.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body">
            <?php if (count($recentUsers) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Status</th>
                        <th>VIP</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentUsers as $user): ?>
                    <tr>
                        <td>
                            <strong><?= e($user['full_name']) ?></strong><br>
                            <small style="color: var(--text-muted);">@<?= e($user['username']) ?></small>
                        </td>
                        <td>
                            <?php if ($user['status_name']): ?>
                            <span class="badge" style="background: <?= e($user['status_color']) ?>;">
                                <?= statusSvgIcon($user['status_icon'], $user['status_name']) ?> <?= e($user['status_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-secondary">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="vip-badge <?= $user['vip_access'] ? 'active' : 'inactive' ?>">
                                <?= $user['vip_access'] ? svgIcon('check') . ' ON' : svgIcon('x') . ' OFF' ?>
                            </span>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                            <?= formatDate($user['created_at'], 'd M Y') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <?= svgIcon('user', 'empty-icon-svg') ?>
                <p>No users yet. Create your first user!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Games -->
    <div class="card">
        <div class="card-header">
            <h2><?= svgIcon('target') ?> Recent Picks</h2>
            <a href="games.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="card-body">
            <?php if (count($recentGames) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Match</th>
                        <th>Odds</th>
                        <th>Result</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentGames as $game): ?>
                    <tr>
                        <td>
                            <strong><?= e($game['match_name']) ?></strong><br>
                            <small style="color: var(--text-muted);"><?= e($game['selection']) ?></small>
                        </td>
                        <td style="color: var(--gold); font-weight: 700;"><?= e($game['odds']) ?></td>
                        <td>
                            <?= getResultBadge($game['result_status']) ?>
                            <?php if (!empty($game['final_score'])): ?><div class="settled-score"><?= e($game['final_score']) ?></div><?php endif; ?>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                            <?= formatDate($game['match_date'], 'd M') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <?= svgIcon('target', 'empty-icon-svg') ?>
                <p>No games posted yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card activity-card">
    <div class="card-header">
        <div>
            <h2><?= svgIcon('activity') ?> Recent Admin Activity</h2>
            <p class="card-subtitle">User changes and pick-result updates</p>
        </div>
    </div>
    <div class="card-body activity-list">
        <?php if (count($recentActivity) > 0): ?>
            <?php foreach ($recentActivity as $activity): ?>
            <div class="activity-row">
                <span class="activity-mark"><?= svgIcon($activity['action'] === 'settle_pick' ? 'trophy' : 'activity') ?></span>
                <div class="activity-copy">
                    <strong><?= e(ucwords(str_replace('_', ' ', $activity['action']))) ?></strong>
                    <span><?= e($activity['details'] ?: 'No additional details') ?></span>
                    <small><?= e($activity['full_name'] ?: 'System') ?> · <?= formatDateTime($activity['created_at']) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="empty-copy">No admin activity has been recorded yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
