<?php
// ============================================================
// ADMIN - CUSTOM STATUS MANAGEMENT
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Status Management';
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        switch ($action) {
            case 'create_status':
                $name = sanitize($_POST['name'] ?? '');
                $icon = sanitize($_POST['icon'] ?? '📋');
                $color = sanitize($_POST['color'] ?? '#FFD700');
                $title = sanitize($_POST['title'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $restrictAccess = isset($_POST['restrict_access']) ? 1 : 0;
                
                if (empty($name) || empty($title) || empty($message)) {
                    $error = 'Name, title, and message are required.';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO custom_statuses (name, icon, color, title, message, restrict_access) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $icon, $color, $title, $message, $restrictAccess]);
                    $success = 'Status <strong>' . e($name) . '</strong> created!';
                    unset($_SESSION[CSRF_TOKEN_NAME]);
                }
                break;
            
            case 'edit_status':
                $statusId = (int)($_POST['status_id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $icon = sanitize($_POST['icon'] ?? '📋');
                $color = sanitize($_POST['color'] ?? '#FFD700');
                $title = sanitize($_POST['title'] ?? '');
                $message = sanitize($_POST['message'] ?? '');
                $restrictAccess = isset($_POST['restrict_access']) ? 1 : 0;
                $isActive = isset($_POST['is_active']) ? 1 : 0;
                
                if ($statusId <= 0 || empty($name) || empty($title) || empty($message)) {
                    $error = 'All required fields must be filled.';
                } else {
                    $stmt = $pdo->prepare("UPDATE custom_statuses SET name=?, icon=?, color=?, title=?, message=?, restrict_access=?, is_active=? WHERE id=?");
                    $stmt->execute([$name, $icon, $color, $title, $message, $restrictAccess, $isActive, $statusId]);
                    $success = 'Status updated successfully.';
                }
                break;
            
            case 'delete_status':
                $statusId = (int)($_POST['status_id'] ?? 0);
                if ($statusId > 0) {
                    // Unassign from users
                    $pdo->prepare("UPDATE users SET status_id = NULL WHERE status_id = ?")->execute([$statusId]);
                    $pdo->prepare("DELETE FROM custom_statuses WHERE id = ?")->execute([$statusId]);
                    $success = 'Status deleted.';
                }
                break;
        }
    }
}

$statuses = $pdo->query("SELECT * FROM custom_statuses ORDER BY is_active DESC, name ASC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><span>❌</span> <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><span>✅</span> <?= $success ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>🏷️ Custom Statuses</h2>
        <button class="btn btn-primary btn-sm" onclick="openModal('createStatusModal')">➕ Create Status</button>
    </div>
    <div class="card-body">
        <?php if (count($statuses) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Preview</th>
                    <th>Access</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statuses as $s): ?>
                <tr>
                    <td>
                        <span class="badge" style="background: <?= e($s['color']) ?>;">
                            <?= e($s['icon']) ?> <?= e($s['name']) ?>
                        </span>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <strong style="color: <?= e($s['color']) ?>;"><?= e($s['title']) ?></strong>
                    </td>
                    <td>
                        <?= $s['restrict_access'] ? '<span class="badge badge-danger">🔒 Restricted</span>' : '<span class="badge badge-success">✅ Open</span>' ?>
                    </td>
                    <td>
                        <?= $s['is_active'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?>
                    </td>
                    <td>
                        <div class="actions" style="display: flex; gap: 4px;">
                            <button class="btn btn-sm btn-secondary" onclick="editStatus(<?= $s['id'] ?>)">✏️</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this status? Users will lose this status assignment.');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_status">
                                <input type="hidden" name="status_id" value="<?= $s['id'] ?>">
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
            <span class="empty-icon">🏷️</span>
            <p>No statuses created yet. Click "Create Status" to add one.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- CREATE STATUS MODAL -->
<div class="modal-backdrop" id="createStatusModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3>➕ Create Status</h3>
                <button class="modal-close" onclick="closeModal('createStatusModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_status">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Status Name *</label>
                        <input type="text" name="name" id="cs_name" required placeholder="e.g. VIP Active">
                    </div>
                    <div class="form-group">
                        <label>Icon (emoji)</label>
                        <input type="text" name="icon" id="cs_icon" value="📋" placeholder="📋">
                    </div>
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="color" name="color" id="cs_color" value="#FFD700">
                </div>
                <div class="form-group">
                    <label>Title * (shown to user)</label>
                    <input type="text" name="title" id="cs_title" required placeholder="e.g. VIP Active">
                </div>
                <div class="form-group">
                    <label>Message * (shown to user)</label>
                    <textarea name="message" id="cs_message" required placeholder="Your account is active and ready for VIP betting."></textarea>
                </div>
                <div class="form-checkbox" style="margin-bottom: 1rem;">
                    <label>
                        <input type="checkbox" name="restrict_access" id="cs_restrict" value="1">
                        <span>🔒 Restrict access (hide games/results)</span>
                    </label>
                </div>
                
                <!-- Live Preview -->
                <div id="statusPreview">
                    <div class="status-preview" style="background: rgba(0,0,0,0.3); border: 2px solid #FFD700;">
                        <div style="font-size: 3rem; margin-bottom: 0.5rem;">📋</div>
                        <div class="status-title" style="color: #FFD700;">Status Title</div>
                        <div class="status-msg" style="color: var(--text-secondary);">Status message preview</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createStatusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">✅ Create Status</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT STATUS MODAL -->
<div class="modal-backdrop" id="editStatusModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3>✏️ Edit Status</h3>
                <button class="modal-close" onclick="closeModal('editStatusModal')">&times;</button>
            </div>
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="edit_status">
                <input type="hidden" name="status_id" id="es_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Status Name *</label>
                        <input type="text" name="name" id="es_name" required>
                    </div>
                    <div class="form-group">
                        <label>Icon (emoji)</label>
                        <input type="text" name="icon" id="es_icon">
                    </div>
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="color" name="color" id="es_color">
                </div>
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" id="es_title" required>
                </div>
                <div class="form-group">
                    <label>Message *</label>
                    <textarea name="message" id="es_message" required></textarea>
                </div>
                <div class="form-row">
                    <div class="form-checkbox">
                        <label>
                            <input type="checkbox" name="restrict_access" id="es_restrict" value="1">
                            <span>🔒 Restrict access</span>
                        </label>
                    </div>
                    <div class="form-checkbox">
                        <label>
                            <input type="checkbox" name="is_active" id="es_active" value="1" checked>
                            <span>✅ Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editStatusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">💾 Update Status</button>
            </div>
        </form>
    </div>
</div>

<script>
const statusesData = <?= json_encode($statuses) ?>;

function editStatus(id) {
    const s = statusesData.find(st => st.id == id);
    if (!s) return;
    document.getElementById('es_id').value = s.id;
    document.getElementById('es_name').value = s.name;
    document.getElementById('es_icon').value = s.icon;
    document.getElementById('es_color').value = s.color;
    document.getElementById('es_title').value = s.title;
    document.getElementById('es_message').value = s.message;
    document.getElementById('es_restrict').checked = s.restrict_access == 1;
    document.getElementById('es_active').checked = s.is_active == 1;
    openModal('editStatusModal');
}

// Live preview for create modal
document.addEventListener('DOMContentLoaded', function() {
    ['cs_name','cs_icon','cs_color','cs_title','cs_message'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });
});

function updatePreview() {
    const icon = document.getElementById('cs_icon')?.value || '📋';
    const color = document.getElementById('cs_color')?.value || '#FFD700';
    const title = document.getElementById('cs_title')?.value || 'Status Title';
    const message = document.getElementById('cs_message')?.value || 'Status message preview';
    
    const preview = document.getElementById('statusPreview');
    if (!preview) return;
    
    preview.innerHTML = `
        <div class="status-preview" style="background: rgba(0,0,0,0.3); border: 2px solid ${color};">
            <div style="font-size: 3rem; margin-bottom: 0.5rem;">${icon}</div>
            <div class="status-title" style="color: ${color};">${escapeHtml(title)}</div>
            <div class="status-msg" style="color: var(--text-secondary);">${escapeHtml(message)}</div>
        </div>
    `;
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
