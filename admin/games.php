<?php
// ============================================================
// ADMIN - GAME MANAGEMENT
// FIXED BETS RO
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
                
                if ($gameId <= 0 || empty($matchName) || empty($selection) || $odds <= 0) {
                    $error = 'All required fields must be filled.';
                } else {
                    $existingGame = $pdo->prepare("SELECT result_status FROM vip_games WHERE id = ?");
                    $existingGame->execute([$gameId]);
                    $existingResult = $existingGame->fetchColumn();
                    if ($existingResult === false) {
                        $error = 'Pick not found.';
                    } elseif ($existingResult !== 'pending') {
                        $error = 'Settled picks are locked to protect the published history. Use the result correction flow for outcome changes.';
                    } else {
                        $stmt = $pdo->prepare("UPDATE vip_games SET match_name=?, league=?, selection=?, odds=?, match_date=?, match_time=?, confidence=?, notes=? WHERE id=?");
                        $stmt->execute([$matchName, $league, $selection, $odds, $matchDate, $matchTime, $confidence, $notes, $gameId]);
                        $success = 'Pick details updated.';
                    }
                }
                break;

            case 'settle_result':
                $gameId = (int)($_POST['game_id'] ?? 0);
                $resultStatus = $_POST['result_status'] ?? '';
                $finalScore = trim(strip_tags((string)($_POST['final_score'] ?? '')));
                $correctionReason = trim(strip_tags((string)($_POST['correction_reason'] ?? '')));
                $validResults = ['win', 'loss', 'void', 'push'];

                if ($gameId <= 0 || !in_array($resultStatus, $validResults, true)) {
                    $error = 'Choose a valid final outcome.';
                } elseif (strlen($finalScore) > 32) {
                    $error = 'Final score must be 32 characters or fewer.';
                } elseif (in_array($resultStatus, ['win', 'loss'], true) && $finalScore === '') {
                    $error = 'Enter the final score for a win or loss.';
                } else {
                    $lookup = $pdo->prepare("SELECT id, match_name, result_status, final_score FROM vip_games WHERE id = ?");
                    $lookup->execute([$gameId]);
                    $existing = $lookup->fetch(PDO::FETCH_ASSOC);

                    if (!$existing) {
                        $error = 'Pick not found.';
                    } elseif (
                        $existing['result_status'] !== 'pending'
                        && ($existing['result_status'] !== $resultStatus || (string)($existing['final_score'] ?? '') !== $finalScore)
                        && $correctionReason === ''
                    ) {
                        $error = 'Add a correction reason before changing a settled result.';
                    } else {
                        $settledAt = date('Y-m-d H:i:s');
                        $stmt = $pdo->prepare("UPDATE vip_games SET result_status = ?, final_score = ?, settled_at = ? WHERE id = ?");
                        $stmt->execute([$resultStatus, $finalScore !== '' ? $finalScore : null, $settledAt, $gameId]);

                        $oldResult = $existing['result_status'];
                        $oldScore = $existing['final_score'] ?? 'not recorded';
                        $detail = "Pick #{$gameId} ({$existing['match_name']}): {$oldResult} / {$oldScore} -> {$resultStatus} / " . ($finalScore !== '' ? $finalScore : 'no score');
                        if ($correctionReason !== '') {
                            $detail .= "; correction reason: {$correctionReason}";
                        }
                        try {
                            logActivity($pdo, (int)$_SESSION['user_id'], 'settle_pick', $detail);
                        } catch (Throwable $e) {
                            error_log('Pick settlement audit log failed: ' . $e->getMessage());
                        }

                        $success = $oldResult === 'pending' ? 'Pick result settled.' : 'Settled result corrected and logged.';
                        unset($_SESSION[CSRF_TOKEN_NAME]);
                    }
                }
                break;
            
            case 'delete_game':
                $gameId = (int)($_POST['game_id'] ?? 0);
                if ($gameId > 0) {
                    $lookup = $pdo->prepare("SELECT result_status FROM vip_games WHERE id = ?");
                    $lookup->execute([$gameId]);
                    $status = $lookup->fetchColumn();
                    if ($status === false) {
                        $error = 'Pick not found.';
                    } elseif ($status !== 'pending') {
                        $error = 'Settled picks cannot be deleted. Archive the pick to keep its public record.';
                    } else {
                        $pdo->prepare("DELETE FROM vip_games WHERE id = ?")->execute([$gameId]);
                        $success = 'Unsettled pick deleted.';
                    }
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

// Search and filters
$allowedFilters = ['active', 'archived', 'pending_result', 'win', 'loss', 'void', 'push'];
$filter = $_GET['filter'] ?? 'active';
if (!in_array($filter, $allowedFilters, true)) $filter = 'active';
$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$conditions = [];
$params = [];

if ($filter === 'archived') {
    $conditions[] = 'is_archived = 1';
} else {
    $conditions[] = 'is_archived = 0';
    if ($filter === 'pending_result') $conditions[] = "result_status = 'pending'";
    elseif (in_array($filter, ['win', 'loss', 'void', 'push'], true)) {
        $conditions[] = 'result_status = ?';
        $params[] = $filter;
    }
}
if ($search !== '') {
    $conditions[] = '(match_name LIKE ? OR league LIKE ? OR selection LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}

$where = 'WHERE ' . implode(' AND ', $conditions);
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM vip_games $where");
$countStmt->execute($params);
$totalGames = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalGames / $perPage));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $perPage;
$gamesStmt = $pdo->prepare("SELECT * FROM vip_games $where ORDER BY match_date DESC, created_at DESC LIMIT $perPage OFFSET $offset");
$gamesStmt->execute($params);
$games = $gamesStmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><?= svgIcon('x') ?> <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= svgIcon('check') ?> <?= $success ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <div>
            <h2>VIP Picks</h2>
            <p class="card-subtitle">Publish selections, then record the actual final outcome.</p>
        </div>
        <button class="btn btn-primary btn-sm" type="button" onclick="openModal('addGameModal')"><?= svgIcon('plus') ?> Add pick</button>
    </div>
    <div class="card-body">
        <form method="GET" class="games-toolbar">
            <label class="search-field">
                <?= svgIcon('search') ?>
                <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search match, league, or selection" aria-label="Search picks">
            </label>
            <select name="filter" aria-label="Filter picks">
                <option value="active" <?= $filter === 'active' ? 'selected' : '' ?>>Active picks</option>
                <option value="pending_result" <?= $filter === 'pending_result' ? 'selected' : '' ?>>Pending results</option>
                <option value="win" <?= $filter === 'win' ? 'selected' : '' ?>>Wins</option>
                <option value="loss" <?= $filter === 'loss' ? 'selected' : '' ?>>Losses</option>
                <option value="void" <?= $filter === 'void' ? 'selected' : '' ?>>Voids</option>
                <option value="push" <?= $filter === 'push' ? 'selected' : '' ?>>Pushes</option>
                <option value="archived" <?= $filter === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
            <button class="btn btn-secondary btn-sm" type="submit"><?= svgIcon('filter') ?> Apply</button>
        </form>
        <?php if (count($games) > 0): ?>
        <div class="table-scroll">
        <table class="data-table games-table">
            <thead>
                <tr>
                    <th>Match</th>
                    <th>Selection</th>
                    <th>Odds</th>
                    <th>Kickoff</th>
                    <th>Level</th>
                    <th>Outcome / Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($games as $game): ?>
                <tr>
                    <td data-label="Match">
                        <strong><?= e($game['match_name']) ?></strong><br>
                        <small style="color: var(--text-muted);"><?= e($game['league'] ?: 'League not set') ?></small>
                    </td>
                    <td data-label="Selection" class="games-selection">
                        <?= e($game['selection']) ?>
                    </td>
                    <td data-label="Odds" class="games-odds">
                        <?= number_format($game['odds'], 2) ?>
                    </td>
                    <td data-label="Kickoff" class="games-kickoff">
                        <?= formatDate($game['match_date'], 'd M Y') ?><br>
                        <small style="color: var(--text-muted);"><?= $game['match_time'] ? formatDate($game['match_time'], 'H:i') : '—' ?></small>
                    </td>
                    <td data-label="Level">
                        <?php if ($game['confidence']): ?>
                        <span class="game-confidence <?= strtolower($game['confidence']) ?>">
                            <?= e($game['confidence']) ?>
                        </span>
                        <?php else: ?>
                        <span class="badge badge-secondary">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Outcome / Score">
                        <?= getResultBadge($game['result_status']) ?>
                        <?php if (!empty($game['final_score'])): ?>
                        <div class="settled-score"><?= e($game['final_score']) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($game['settled_at'])): ?>
                        <small class="settled-time">Settled <?= formatDateTime($game['settled_at'], 'd M, H:i') ?></small>
                        <?php endif; ?>
                    </td>
                    <td data-label="Actions">
                        <div class="actions game-actions">
                            <?php if ($game['result_status'] === 'pending'): ?>
                            <button type="button" class="btn btn-sm btn-primary" onclick="settleGame(<?= (int)$game['id'] ?>)"><?= svgIcon('check') ?> Settle</button>
                            <button type="button" class="btn btn-sm btn-secondary icon-button" aria-label="Edit pick" title="Edit pick" onclick="editGame(<?= (int)$game['id'] ?>)"><?= svgIcon('edit') ?></button>
                            <form method="POST" onsubmit="return confirm('Delete this unsettled pick?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_game">
                                <input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger icon-button" aria-label="Delete pick" title="Delete pick"><?= svgIcon('trash') ?></button>
                            </form>
                            <?php else: ?>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="settleGame(<?= (int)$game['id'] ?>)"><?= svgIcon('edit') ?> Correct result</button>
                            <?php endif; ?>
                            <?php if (!$game['is_archived']): ?>
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="archive_game">
                                <input type="hidden" name="game_id" value="<?= (int)$game['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-secondary icon-button" aria-label="Archive pick" title="Archive pick"><?= svgIcon('archive') ?></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Pick pages">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php $pageQuery = ['filter' => $filter, 'page' => $i]; if ($search !== '') $pageQuery['q'] = $search; ?>
            <a href="?<?= e(http_build_query($pageQuery)) ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>" <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
        <?php else: ?>
        <div class="empty-state">
            <?= svgIcon('target', 'empty-icon-svg') ?>
            <p>No picks match these filters. Adjust your search or add a pick.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ADD GAME MODAL -->
<div class="modal-backdrop" id="addGameModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3><?= svgIcon('plus') ?> Add VIP pick</h3>
                <button type="button" class="modal-close" aria-label="Close" onclick="closeModal('addGameModal')">&times;</button>
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
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes (optional)</label>
                    <textarea name="notes" placeholder="Additional information about this pick..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addGameModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><?= svgIcon('plus') ?> Publish pick</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT GAME MODAL -->
<div class="modal-backdrop" id="editGameModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3><?= svgIcon('edit') ?> Edit pick</h3>
                <button type="button" class="modal-close" aria-label="Close" onclick="closeModal('editGameModal')">&times;</button>
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
                <div class="form-group">
                    <label>Confidence</label>
                    <select name="confidence" id="eg_confidence">
                        <option value="">— Select —</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" id="eg_notes"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editGameModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><?= svgIcon('check') ?> Save pick</button>
            </div>
        </form>
    </div>
</div>

<!-- SETTLE / CORRECT RESULT MODAL -->
<div class="modal-backdrop" id="settleResultModal">
    <div class="modal">
        <form method="POST" id="settleResultForm">
            <div class="modal-header">
                <div>
                    <h3><?= svgIcon('check') ?> <span id="settleModalTitle">Settle result</span></h3>
                    <p class="card-subtitle" id="settleMatchName"></p>
                </div>
                <button type="button" class="modal-close" aria-label="Close" onclick="closeModal('settleResultModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="settle_result">
                <input type="hidden" name="game_id" id="settleGameId">
                <div class="form-group">
                    <label for="settleOutcome">Final outcome *</label>
                    <select name="result_status" id="settleOutcome" required>
                        <option value="win">Win</option>
                        <option value="loss">Loss</option>
                        <option value="void">Void</option>
                        <option value="push">Push</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="settleScore">Final score</label>
                    <input type="text" name="final_score" id="settleScore" maxlength="32" placeholder="e.g. 2-1">
                    <small class="form-hint">Required for a win or loss. Leave blank for void or push if no score is available.</small>
                </div>
                <div class="form-group" id="correctionReasonGroup" hidden>
                    <label for="correctionReason">Correction reason *</label>
                    <textarea name="correction_reason" id="correctionReason" maxlength="500" placeholder="Explain why the recorded result is being corrected."></textarea>
                </div>
                <div class="settlement-note">
                    <?= svgIcon('shield-check') ?>
                    <span>The published selection and odds stay unchanged. Corrections are recorded in the admin activity log.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('settleResultModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><?= svgIcon('check') ?> Save outcome</button>
            </div>
        </form>
    </div>
</div>

<script>
const gamesData = <?= json_encode($games, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;

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
    document.getElementById('eg_notes').value = g.notes || '';
    openModal('editGameModal');
}

function settleGame(id) {
    const g = gamesData.find(game => game.id == id);
    if (!g) return;
    const correcting = g.result_status !== 'pending';
    document.getElementById('settleGameId').value = g.id;
    document.getElementById('settleMatchName').textContent = g.match_name;
    document.getElementById('settleOutcome').value = correcting ? g.result_status : 'win';
    document.getElementById('settleScore').value = g.final_score || '';
    document.getElementById('settleModalTitle').textContent = correcting ? 'Correct result' : 'Settle result';
    document.getElementById('correctionReasonGroup').hidden = !correcting;
    document.getElementById('correctionReason').required = correcting;
    document.getElementById('correctionReason').value = '';
    openModal('settleResultModal');
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
