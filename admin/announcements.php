<?php
// ============================================================
// ADMIN - ANNOUNCEMENTS MANAGEMENT
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Announcements';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        switch ($action) {
            case 'add_announcement':
                $title = sanitize($_POST['title'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $priority = $_POST['priority'] ?? 'normal';
                
                if (empty($title) || empty($message)) {
                    $error = 'Title and message are required.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO announcements (title, message, priority) VALUES (?, ?, ?)");
                    $stmt->execute([$title, $message, $priority]);
                    $success = 'Announcement posted!';
                    unset($_SESSION[CSRF_TOKEN_NAME]);
                }
                break;
            
            case 'edit_announcement':
                $id = (int)($_POST['announcement_id'] ?? 0);
                $title = sanitize($_POST['title'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $priority = $_POST['priority'] ?? 'normal';
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
                if ($id <= 0 || empty($title) || empty($message)) {
                    $error = 'Invalid data.';
                } else {
                    $stmt = $pdo->prepare("UPDATE announcements SET title=?, message=?, priority=?, is_active=? WHERE id=?");
                    $stmt->execute([$title, $message, $priority, $isActive, $id]);
                    $success = 'Announcement updated.';
                }
                break;
            
            case 'delete_announcement':
                $id = (int)($_POST['announcement_id'] ?? 0);
                if ($id > 0) {
                    $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([$id]);
                    $success = 'Announcement deleted.';
                }
                break;
        }
    }
}

$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><span>❌</span> <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><span>✅</span> <?= $success ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>📢 Announcements</h2>
        <button class="btn btn-primary btn-sm" onclick="openModal('addAnnouncementModal')">➕ Post Announcement</button>
    </div>
    <div class="card-body">
        <?php if (count($announcements) > 0): ?>
        <?php foreach ($announcements as $a): ?>
        <div class="announcement-card <?= $a['priority'] ?>">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                <div style="flex: 1;">
                    <div class="announcement-title"><?= e($a['title']) ?></div>
                    <div class="announcement-message"><?= nl2br(e($a['message'])) ?></div>
                    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                        <span class="announcement-date">📅 <?= formatDateTime($a['created_at']) ?></span>
                        <span class="announcement-priority <?= $a['priority'] ?>"><?= e($a['priority']) ?></span>
                        <?php if (!$a['is_active']): ?>
                        <span class="badge badge-secondary">Hidden</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="actions" style="display: flex; gap: 4px; flex-shrink: 0;">
                    <button class="btn btn-sm btn-secondary" onclick="editAnnouncement(<?= $a['id'] ?>)">✏️</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this announcement?');">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="delete_announcement">
                        <input type="hidden" name="announcement_id" value="<?= $a['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">📢</span>
            <p>No announcements yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ADD ANNOUNCEMENT MODAL -->
<div class="modal-backdrop" id="addAnnouncementModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3>📢 Post Announcement</h3>
                <button class="modal-close" onclick="closeModal('addAnnouncementModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_announcement">
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Announcement title">
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" required placeholder="Announcement content..." style="min-height: 120px;"></textarea>
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addAnnouncementModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">✅ Post</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT ANNOUNCEMENT MODAL -->
<div class="modal-backdrop" id="editAnnouncementModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3>✏️ Edit Announcement</h3>
                <button class="modal-close" onclick="closeModal('editAnnouncementModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="edit_announcement">
                <input type="hidden" name="announcement_id" id="ea_id">
                
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" id="ea_title" required>
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" id="ea_message" required style="min-height: 120px;"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Priority</label>
                        <select name="priority" id="ea_priority">
                            <option value="normal">📌 Normal</option>
                            <option value="high">⚠️ High</option>
                            <option value="urgent">🚨 Urgent</option>
                            <option value="low">ℹ️ Low</option>
                        </select>
                    </div>
                    <div class="form-checkbox" style="align-self: flex-end; padding-bottom: 0.5rem;">
                        <label>
                            <input type="checkbox" name="is_active" id="ea_active" value="1" checked>
                            <span>Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editAnnouncementModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">💾 Update</button>
            </div>
        </form>
    </div>
</div>

<script>
const announcementsData = <?= json_encode($announcements) ?>;

function editAnnouncement(id) {
    const a = announcementsData.find(item => item.id == id);
    if (!a) return;
    document.getElementById('ea_id').value = a.id;
    document.getElementById('ea_title').value = a.title;
    document.getElementById('ea_message').value = a.message;
    document.getElementById('ea_priority').value = a.priority;
    document.getElementById('ea_active').checked = a.is_active == 1;
    openModal('editAnnouncementModal');
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
