<?php
// ============================================================
// ADMIN - CUSTOM STATUS MANAGEMENT
// FIXED BETS RO
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
                $icon = sanitize($_POST['icon'] ?? 'tag');
                if (!in_array($icon, ['tag', 'check', 'clock', 'card', 'calendar', 'shield', 'search', 'lock', 'refresh'], true)) $icon = 'tag';
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
                $icon = sanitize($_POST['icon'] ?? 'tag');
                if (!in_array($icon, ['tag', 'check', 'clock', 'card', 'calendar', 'shield', 'search', 'lock', 'refresh'], true)) $icon = 'tag';
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

<?php if ($error): ?><div class="alert alert-danger"><?= svgIcon('x') ?> <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?= svgIcon('check') ?> <?= $success ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?= svgIcon('tag') ?> Custom statuses</h2>
        <button type="button" class="btn btn-primary btn-sm" onclick="openModal('createStatusModal')"><?= svgIcon('plus') ?> Create status</button>
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
                            <?= statusSvgIcon($s['icon'], $s['name']) ?> <?= e($s['name']) ?>
                        </span>
                    </td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <strong style="color: <?= e($s['color']) ?>;"><?= e($s['title']) ?></strong>
                    </td>
                    <td>
                        <?= $s['restrict_access'] ? '<span class="badge badge-danger">' . svgIcon('lock') . ' Restricted</span>' : '<span class="badge badge-success">' . svgIcon('check') . ' Open</span>' ?>
                    </td>
                    <td>
                        <?= $s['is_active'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-secondary">OFF</span>' ?>
                    </td>
                    <td>
                        <div class="actions" style="display: flex; gap: 4px;">
                            <button type="button" class="btn btn-sm btn-secondary icon-button" aria-label="Edit status" title="Edit status" onclick="editStatus(<?= (int)$s['id'] ?>)"><?= svgIcon('edit') ?></button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this status? Users will lose this status assignment.');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_status">
                                <input type="hidden" name="status_id" value="<?= $s['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger icon-button" aria-label="Delete status" title="Delete status"><?= svgIcon('trash') ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <?= svgIcon('tag', 'empty-icon-svg') ?>
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
                <h3><?= svgIcon('plus') ?> Create status</h3>
                <button type="button" class="modal-close" aria-label="Close" onclick="closeModal('createStatusModal')">&times;</button>
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
                        <label>SVG icon style</label>
                        <select name="icon" id="cs_icon">
                            <option value="tag">Tag</option>
                            <option value="check">Check</option>
                            <option value="clock">Clock</option>
                            <option value="card">Payment</option>
                            <option value="calendar">Calendar</option>
                            <option value="shield">Shield</option>
                            <option value="search">Search</option>
                            <option value="lock">Lock</option>
                            <option value="refresh">Refresh</option>
                        </select>
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
                        <span>Restrict access (hide games/results)</span>
                    </label>
                </div>
                
                <!-- Live Preview -->
                <div id="statusPreview">
                    <div class="status-preview" style="background: rgba(0,0,0,0.3); border: 2px solid #FFD700;">
                        <div class="status-preview-icon"><?= svgIcon('tag') ?></div>
                        <div class="status-title" style="color: #FFD700;">Status Title</div>
                        <div class="status-msg" style="color: var(--text-secondary);">Status message preview</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createStatusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><?= svgIcon('plus') ?> Create status</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT STATUS MODAL -->
<div class="modal-backdrop" id="editStatusModal">
    <div class="modal">
        <form method="POST">
            <div class="modal-header">
                <h3><?= svgIcon('edit') ?> Edit status</h3>
                <button type="button" class="modal-close" aria-label="Close" onclick="closeModal('editStatusModal')">&times;</button>
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
                        <label>SVG icon style</label>
                        <select name="icon" id="es_icon">
                            <option value="tag">Tag</option>
                            <option value="check">Check</option>
                            <option value="clock">Clock</option>
                            <option value="card">Payment</option>
                            <option value="calendar">Calendar</option>
                            <option value="shield">Shield</option>
                            <option value="search">Search</option>
                            <option value="lock">Lock</option>
                            <option value="refresh">Refresh</option>
                        </select>
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
                            <span>Restrict access</span>
                        </label>
                    </div>
                    <div class="form-checkbox">
                        <label>
                            <input type="checkbox" name="is_active" id="es_active" value="1" checked>
                            <span>Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editStatusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary"><?= svgIcon('check') ?> Save status</button>
            </div>
        </form>
    </div>
</div>

<script>
const statusesData = <?= json_encode($statuses, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?>;

function editStatus(id) {
    const s = statusesData.find(st => st.id == id);
    if (!s) return;
    document.getElementById('es_id').value = s.id;
    document.getElementById('es_name').value = s.name;
    document.getElementById('es_icon').value = statusIconKey(s.icon, s.name);
    document.getElementById('es_color').value = s.color;
    document.getElementById('es_title').value = s.title;
    document.getElementById('es_message').value = s.message;
    document.getElementById('es_restrict').checked = s.restrict_access == 1;
    document.getElementById('es_active').checked = s.is_active == 1;
    openModal('editStatusModal');
}

const statusIconPaths = {
    tag: '<path d="m20.6 13.4-7.2 7.2a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8Z"/><circle cx="7" cy="7" r="1"/>',
    check: '<path d="m5 12 4 4L19 6"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    card: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
    calendar: '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
    shield: '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>',
    search: '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
    lock: '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3M12 14v3"/>',
    refresh: '<path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9A7 7 0 0 1 18 6l2 6M4 12l2 6a7 7 0 0 0 12.4-3"/>'
};

function statusIconKey(icon, name) {
    if (Object.prototype.hasOwnProperty.call(statusIconPaths, icon)) return icon;
    const normalized = String(name || '').toLowerCase();
    if (normalized.includes('payment')) return 'card';
    if (normalized.includes('expired') || normalized.includes('renewal')) return 'calendar';
    if (normalized.includes('verif') || normalized.includes('suspend')) return 'shield';
    if (normalized.includes('review')) return 'search';
    if (normalized.includes('restrict')) return 'lock';
    if (normalized.includes('active') || normalized.includes('open')) return 'check';
    if (normalized.includes('pending')) return 'clock';
    return 'tag';
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
    const icon = statusIconPaths[document.getElementById('cs_icon')?.value] || statusIconPaths.tag;
    const color = document.getElementById('cs_color')?.value || '#FFD700';
    const title = document.getElementById('cs_title')?.value || 'Status Title';
    const message = document.getElementById('cs_message')?.value || 'Status message preview';
    
    const preview = document.getElementById('statusPreview');
    if (!preview) return;
    
    preview.innerHTML = `
        <div class="status-preview" style="background: rgba(0,0,0,0.3); border: 2px solid ${color};">
            <div class="status-preview-icon"><svg class="ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${icon}</svg></div>
            <div class="status-title" style="color: ${color};">${escapeHtml(title)}</div>
            <div class="status-msg" style="color: var(--text-secondary);">${escapeHtml(message)}</div>
        </div>
    `;
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
