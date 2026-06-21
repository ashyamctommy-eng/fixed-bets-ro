<?php
// ============================================================
// ADMIN - NOTIFICATION MANAGEMENT
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Notification Management';
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        switch ($action) {
            case 'send_personal':
                $userId = (int)($_POST['user_id'] ?? 0);
                $title = sanitize($_POST['title'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $priority = $_POST['priority'] ?? 'normal';
                
                if ($userId <= 0 || empty($title) || empty($message)) {
                    $error = 'Select a user and fill in title and message.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, priority) VALUES (?, ?, ?, 'personal', ?)");
                    $stmt->execute([$userId, $title, $message, $priority]);
                    $success = 'Personal notification sent!';
                    unset($_SESSION[CSRF_TOKEN_NAME]);
                }
                break;
            
            case 'send_global':
                $title = sanitize($_POST['title'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $priority = $_POST['priority'] ?? 'normal';
                
                if (empty($title) || empty($message)) {
                    $error = 'Title and message are required.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type, priority) VALUES (?, ?, 'global', ?)");
                    $stmt->execute([$title, $message, $priority]);
                    $success = 'Global notification sent to all users!';
                    unset($_SESSION[CSRF_TOKEN_NAME]);
                }
                break;
            
            case 'delete_notification':
                $id = (int)($_POST['notification_id'] ?? 0);
                if ($id > 0) {
                    $pdo->prepare("DELETE FROM notifications WHERE id = ?")->execute([$id]);
                    $success = 'Notification deleted.';
                }
                break;
            
            case 'edit_notification':
                $id = (int)($_POST['notification_id'] ?? 0);
                $title = sanitize($_POST['title'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $priority = $_POST['priority'] ?? 'normal';
                
                if ($id <= 0 || empty($title) || empty($message)) {
                    $error = 'Invalid data.';
                } else {
                    $stmt = $pdo->prepare("UPDATE notifications SET title=?, message=?, priority=? WHERE id=?");
                    $stmt->execute([$title, $message, $priority, $id]);
                    $success = 'Notification updated.';
                }
                break;
        }
    }
}

// Get all VIP users for dropdown
$vipUsers = $pdo->query("SELECT id, full_name, username FROM users WHERE role = 'vip_user' ORDER BY full_name ASC")->fetchAll();

// Get all notifications
$notifications = $pdo->query("
    SELECT n.*, u.full_name as user_name, u.username 
    FROM notifications n
    LEFT JOIN users u ON n.user_id = u.id
    ORDER BY n.created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><span>❌</span> <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><span>✅</span> <?= $success ?></div><?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Personal Notification -->
    <div class="card">
        <div class="card-header"><h2>👤 Personal Notification</h2></div>
        <div class="card-body">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="send_personal">
                
                <div class="form-group">
                    <label>Select User *</label>
                    <select name="user_id" required>
                        <option value="">— Choose a user —</option>
                        <?php foreach ($vipUsers as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= e($u['full_name']) ?> (@<?= e($u['username']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Notification title">
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" required style="min-height: 80px;" placeholder="Notification message..."></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="normal">📌 Normal</option>
                        <option value="high">⚠️ High</option>
                        <option value="urgent">🚨 Urgent</option>
                        <option value="low">ℹ️ Low</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">📨 Send Notification</button>
            </form>
        </div>
    </div>

    <!-- Global Notification -->
    <div class="card">
        <div class="card-header"><h2>🌍 Global Notification</h2></div>
        <div class="card-body">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="send_global">
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Global announcement title">
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" required style="min-height: 120px;" placeholder="This will be visible to ALL users..."></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority">
                        <option value="normal">📌 Normal</option>
                        <option value="high">⚠️ High</option>
                        <option value="urgent">🚨 Urgent</option>
                        <option value="low">ℹ️ Low</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">🌍 Send to All Users</button>
            </form>
        </div>
    </div>
</div>

<!-- Notification History -->
<div class="card">
    <div class="card-header">
        <h2>📋 Notification History</h2>
    </div>
    <div class="card-body">
        <?php if (count($notifications) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Target</th>
                    <th>Priority</th>
                    <th>Read</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notifications as $n): ?>
                <tr>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <strong><?= e($n['title']) ?></strong>
                    </td>
                    <td>
                        <?= $n['type'] === 'global' ? '<span class="badge badge-info">🌍 Global</span>' : '<span class="badge badge-warning">👤 Personal</span>' ?>
                    </td>
                    <td style="font-size: 0.85rem;">
                        <?= $n['type'] === 'global' ? 'All Users' : e($n['user_name'] ?? 'Deleted User') ?>
                    </td>
                    <td>
                        <span class="announcement-priority <?= $n['priority'] ?>"><?= e($n['priority']) ?></span>
                    </td>
                    <td style="font-size: 0.85rem;">
                        <?= $n['type'] === 'personal' ? ($n['is_read'] ? '✅ Read' : '📩 Unread') : '—' ?>
                    </td>
                    <td style="font-size: 0.8rem; color: var(--text-muted);">
                        <?= formatDateTime($n['created_at'], 'd M H:i') ?>
                    </td>
                    <td>
                        <div class="actions" style="display: flex; gap: 4px;">
                            <button class="btn btn-sm btn-secondary" onclick="editNotif(<?= $n['id'] ?>)">✏️</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this notification?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_notification">
                                <input type="hidden" name="notification_id" value="<?= $n['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">🔔</span>
            <p>No notifications sent yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- EDIT NOTIFICATION MODAL -->
<div class="modal-backdrop" id="editNotifModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3>✏️ Edit Notification</h3>
                <button class="modal-close" onclick="closeModal('editNotifModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="edit_notification">
                <input type="hidden" name="notification_id" id="en_id">
                
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" id="en_title" required>
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea name="message" id="en_message" required style="min-height: 100px;"></textarea>
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" id="en_priority">
                        <option value="normal">📌 Normal</option>
                        <option value="high">⚠️ High</option>
                        <option value="urgent">🚨 Urgent</option>
                        <option value="low">ℹ️ Low</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editNotifModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">💾 Update</button>
            </div>
        </form>
    </div>
</div>

<script>
const notifData = <?= json_encode($notifications) ?>;

function editNotif(id) {
    const n = notifData.find(item => item.id == id);
    if (!n) return;
    document.getElementById('en_id').value = n.id;
    document.getElementById('en_title').value = n.title;
    document.getElementById('en_message').value = n.message;
    document.getElementById('en_priority').value = n.priority;
    openModal('editNotifModal');
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
