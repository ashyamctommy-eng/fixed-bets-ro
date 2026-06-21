<?php
// ============================================================
// ADMIN DASHBOARD
// FIXED BETS RO 🇷🇴
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
$newUsersToday = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'vip_user' AND DATE(created_at) = CURDATE()")->fetchColumn();

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
    ORDER BY created_at DESC
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="stats-grid">
    <div class="stat-card gold">
        <span class="stat-icon">👥</span>
        <div class="stat-value"><?= $totalUsers ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">⭐</span>
        <div class="stat-value"><?= $vipUsers ?></div>
        <div class="stat-label">VIP Active</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🔒</span>
        <div class="stat-value"><?= $restrictedUsers ?></div>
        <div class="stat-label">Restricted</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🆕</span>
        <div class="stat-value"><?= $newUsersToday ?></div>
        <div class="stat-label">New Today</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🎯</span>
        <div class="stat-value"><?= $totalGames ?></div>
        <div class="stat-label">Games</div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🔔</span>
        <div class="stat-value"><?= $totalNotifications ?></div>
        <div class="stat-label">Notifications</div>
    </div>
</div>

<div class="dash-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h2>👥 Recent Users</h2>
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
                                <?= e($user['status_icon']) ?> <?= e($user['status_name']) ?>
                            </span>
                            <?php else: ?>
                            <span class="badge badge-secondary">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="vip-badge <?= $user['vip_access'] ? 'active' : 'inactive' ?>">
                                <?= $user['vip_access'] ? '⭐ ON' : '❌ OFF' ?>
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
                <span class="empty-icon">👤</span>
                <p>No users yet. Create your first user!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Games -->
    <div class="card">
        <div class="card-header">
            <h2>🎯 Recent Games</h2>
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
                        <td><?= getResultBadge($game['result_status']) ?></td>
                        <td style="color: var(--text-muted); font-size: 0.85rem;">
                            <?= formatDate($game['match_date'], 'd M') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">🎯</span>
                <p>No games posted yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
