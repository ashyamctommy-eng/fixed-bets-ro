<?php
// ============================================================
// ADMIN - GAME MANAGEMENT
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Game Management';
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        switch ($action) {
            case 'add_game':
                $matchName = sanitize($_POST['match_name'] ?? '');
                $league = sanitize($_POST['league'] ?? '');
                $selection = sanitize($_POST['selection'] ?? '');
                $odds = floatval($_POST['odds'] ?? 0);
                $matchDate = $_POST['match_date'] ?? '';
                $matchTime = $_POST['match_time'] ?? '';
                $confidence = sanitize($_POST['confidence'] ?? '');
                $notes = sanitize($_POST['notes'] ?? '');
                
                if (empty($matchName) || empty($selection) || $odds <= 0 || empty($matchDate)) {
                    $error = 'Match name, selection, odds, and date are required.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO vip_games (match_name, league, selection, odds, match_date, match_time, confidence, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$matchName, $league, $selection, $odds, $matchDate, $matchTime, $confidence, $notes]);
                    $success = 'Game <strong>' . e($matchName) . '</strong> added!';
                    unset($_SESSION[CSRF_TOKEN_NAME]);
                }
                break;
            
            case 'edit_game':
                $gameId = (int)($_POST['game_id'] ?? 0);
                $matchName = sanitize($_POST['match_name'] ?? '');
                $league = sanitize($_POST['league'] ?? '');
                $selection = sanitize($_POST['selection'] ?? '');
                $odds = floatval($_POST['odds'] ?? 0);
                $matchDate = $_POST['match_date'] ?? '';
                $matchTime = $_POST['match_time'] ?? '';
                $confidence = sanitize($_POST['confidence'] ?? '');
                $notes = sanitize($_POST['notes'] ?? '');
                $resultStatus = $_POST['result_status'] ?? 'pending';
                
                if ($gameId <= 0 || empty($matchName) || empty($selection) || $odds <= 0) {
                    $error = 'All required fields must be filled.';
                } else {
                    $stmt = $pdo->prepare("UPDATE vip_games SET match_name=?, league=?, selection=?, odds=?, match_date=?, match_time=?, confidence=?, notes=?, result_status=? WHERE id=?");
                    $stmt->execute([$matchName, $league, $selection, $odds, $matchDate, $matchTime, $confidence, $notes, $resultStatus, $gameId]);
                    $success = 'Game updated!';
                }
                break;
            
            case 'delete_game':
                $gameId = (int)($_POST['game_id'] ?? 0);
                if ($gameId > 0) {
                    $pdo->prepare("DELETE FROM vip_games WHERE id = ?")->execute([$gameId]);
                    $success = 'Game deleted.';
                }
                break;
            
            case 'archive_game':
                $gameId = (int)($_POST['game_id'] ?? 0);
                if ($gameId > 0) {
                    $pdo->prepare("UPDATE vip_games SET is_archived = 1 WHERE id = ?")->execute([$gameId]);
                    $success = 'Game archived.';
                }
                break;
        }
    }
}

// Filter
$filter = $_GET['filter'] ?? 'active';
$where = "WHERE is_archived = 0";
if ($filter === 'archived') $where = "WHERE is_archived = 1";
if ($filter === 'win') $where = "WHERE result_status = 'win' AND is_archived = 0";
if ($filter === 'loss') $where = "WHERE result_status = 'loss' AND is_archived = 0";
if ($filter === 'pending_result') $where = "WHERE result_status = 'pending' AND is_archived = 0";

$games = $pdo->query("SELECT * FROM vip_games $where ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><span>❌</span> <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><span>✅</span> <?= $success ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>🎯 VIP Games</h2>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <div class="filter-bar">
                <a href="?filter=active" class="btn btn-sm <?= $filter === 'active' ? 'btn-primary' : 'btn-secondary' ?>">Active</a>
                <a href="?filter=pending_result" class="btn btn-sm <?= $filter === 'pending_result' ? 'btn-primary' : 'btn-secondary' ?>">⏳ Pending</a>
                <a href="?filter=win" class="btn btn-sm <?= $filter === 'win' ? 'btn-primary' : 'btn-secondary' ?>">✅ Wins</a>
                <a href="?filter=loss" class="btn btn-sm <?= $filter === 'loss' ? 'btn-primary' : 'btn-secondary' ?>">❌ Losses</a>
                <a href="?filter=archived" class="btn btn-sm <?= $filter === 'archived' ? 'btn-primary' : 'btn-secondary' ?>">📦 Archived</a>
            </div>
            <button class="btn btn-primary btn-sm" onclick="openModal('addGameModal')">➕ Add Game</button>
        </div>
    </div>
    <div class="card-body">
        <?php if (count($games) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Match</th>
                    <th>Selection</th>
                    <th>Odds</th>
                    <th>Date</th>
                    <th>Confidence</th>
                    <th>Result</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                <tr>
                    <td>
                        <strong><?= e($game['match_name']) ?></strong><br>
                        <small style="color: var(--text-muted);"><?= e($game['league']) ?></small>
                    </td>
                    <td style="max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?= e($game['selection']) ?>
                    </td>
                    <td style="color: var(--gold); font-weight: 800; font-size: 1.1rem;">
                        <?= number_format($game['odds'], 2) ?>
                    </td>
                    <td style="font-size: 0.85rem;">
                        <?= formatDate($game['match_date'], 'd M Y') ?><br>
                        <small style="color: var(--text-muted);"><?= $game['match_time'] ? formatDate($game['match_time'], 'H:i') : '—' ?></small>
                    </td>
                    <td>
                        <?php if ($game['confidence']): ?>
                        <span class="game-confidence <?= strtolower($game['confidence']) ?>">
                            <?= e($game['confidence']) ?>
                        </span>
                        <?php else: ?>
                        <span class="badge badge-secondary">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= getResultBadge($game['result_status']) ?></td>
                    <td>
                        <div class="actions" style="display: flex; gap: 4px;">
                            <button class="btn btn-sm btn-secondary" onclick="editGame(<?= $game['id'] ?>)">✏️</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this game?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_game">
                                <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                            <?php if (!$game['is_archived']): ?>
                            <form method="POST" style="display:inline;">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="archive_game">
                                <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-secondary">📦</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">🎯</span>
            <p>No games found in this category. Add your first VIP game!</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ADD GAME MODAL -->
<div class="modal-backdrop" id="addGameModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3>➕ Add VIP Game</h3>
                <button class="modal-close" onclick="closeModal('addGameModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_game">
                
                <div class="form-group">
                    <label>Match Name *</label>
                    <input type="text" name="match_name" required placeholder="e.g. Barcelona vs Real Madrid">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>League</label>
                        <input type="text" name="league" placeholder="e.g. La Liga">
                    </div>
                    <div class="form-group">
                        <label>Odds *</label>
                        <input type="number" step="0.01" min="1.01" name="odds" required placeholder="2.10">
                    </div>
                </div>
                <div class="form-group">
                    <label>Selection *</label>
                    <input type="text" name="selection" required placeholder="e.g. Over 2.5 Goals / Team A to Win">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Match Date *</label>
                        <input type="date" name="match_date" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label>Match Time</label>
                        <input type="time" name="match_time" value="20:00">
                    </div>
                </div>
                <div class="form-group">
                    <label>Confidence Level</label>
                    <select name="confidence">
                        <option value="">— Select —</option>
                        <option value="High">🔥 High</option>
                        <option value="Medium">⚡ Medium</option>
                        <option value="Low">⚠️ Low</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes (optional)</label>
                    <textarea name="notes" placeholder="Additional information about this pick..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addGameModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">✅ Add Game</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT GAME MODAL -->
<div class="modal-backdrop" id="editGameModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3>✏️ Edit Game</h3>
                <button class="modal-close" onclick="closeModal('editGameModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="edit_game">
                <input type="hidden" name="game_id" id="eg_id">
                
                <div class="form-group">
                    <label>Match Name *</label>
                    <input type="text" name="match_name" id="eg_match" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>League</label>
                        <input type="text" name="league" id="eg_league">
                    </div>
                    <div class="form-group">
                        <label>Odds *</label>
                        <input type="number" step="0.01" min="1.01" name="odds" id="eg_odds" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Selection *</label>
                    <input type="text" name="selection" id="eg_selection" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Match Date *</label>
                        <input type="date" name="match_date" id="eg_date" required>
                    </div>
                    <div class="form-group">
                        <label>Match Time</label>
                        <input type="time" name="match_time" id="eg_time">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Confidence</label>
                        <select name="confidence" id="eg_confidence">
                            <option value="">— Select —</option>
                            <option value="High">🔥 High</option>
                            <option value="Medium">⚡ Medium</option>
                            <option value="Low">⚠️ Low</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Result Status</label>
                        <select name="result_status" id="eg_result">
                            <option value="pending">⏳ Pending</option>
                            <option value="win">✅ Win</option>
                            <option value="loss">❌ Loss</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="eg_notes"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editGameModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">💾 Update Game</button>
            </div>
        </form>
    </div>
</div>

<script>
const gamesData = <?= json_encode($games) ?>;

function editGame(id) {
    const g = gamesData.find(game => game.id == id);
    if (!g) return;
    document.getElementById('eg_id').value = g.id;
    document.getElementById('eg_match').value = g.match_name;
    document.getElementById('eg_league').value = g.league || '';
    document.getElementById('eg_odds').value = g.odds;
    document.getElementById('eg_selection').value = g.selection;
    document.getElementById('eg_date').value = g.match_date;
    document.getElementById('eg_time').value = g.match_time || '';
    document.getElementById('eg_confidence').value = g.confidence || '';
    document.getElementById('eg_result').value = g.result_status;
    document.getElementById('eg_notes').value = g.notes || '';
    openModal('editGameModal');
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
