<?php
// ============================================================
// ADMIN - USER MANAGEMENT
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'User Management';
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        switch ($action) {
            // CREATE USER
            case 'create_user':
                $fullName = sanitize($_POST['full_name'] ?? '');
                $username = sanitize($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $email = sanitize($_POST['email'] ?? '');
                $telegram = sanitize($_POST['telegram'] ?? '');
                $whatsapp = sanitize($_POST['whatsapp'] ?? '');
                
                if (empty($fullName) || empty($username) || empty($password)) {
                    $error = 'Full name, username, and password are required.';
                } elseif (strlen($password) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } else {
                    // Check if username exists
                    $existing = getUserByUsername($pdo, $username);
                    if ($existing) {
                        $error = 'Username already exists.';
                    } else {
                        $hashed = password_hash($password, HASH_ALGO, ['cost' => HASH_COST]);
                        $stmt = $pdo->prepare("
                            INSERT INTO users (full_name, username, password, email, telegram, whatsapp, role)
                            VALUES (?, ?, ?, ?, ?, ?, 'vip_user')
                        ");
                        $stmt->execute([$fullName, $username, $hashed, $email, $telegram, $whatsapp]);
                        
                        $newUserId = $pdo->lastInsertId();
                        logActivity($pdo, $_SESSION['user_id'], 'create_user', "Created user: $username (ID: $newUserId)");
                        $success = "User <strong>" . e($fullName) . "</strong> created successfully!";
                        
                        // Regenerate CSRF
                        unset($_SESSION[CSRF_TOKEN_NAME]);
                    }
                }
                break;
            
            // EDIT USER
            case 'edit_user':
                $userId = (int)($_POST['user_id'] ?? 0);
                $fullName = sanitize($_POST['full_name'] ?? '');
                $email = sanitize($_POST['email'] ?? '');
                $telegram = sanitize($_POST['telegram'] ?? '');
                $whatsapp = sanitize($_POST['whatsapp'] ?? '');
                
                if ($userId <= 0 || empty($fullName)) {
                    $error = 'Invalid user data.';
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE users SET full_name = ?, email = ?, telegram = ?, whatsapp = ? WHERE id = ? AND role = 'vip_user'
                    ");
                    $stmt->execute([$fullName, $email, $telegram, $whatsapp, $userId]);
                    logActivity($pdo, $_SESSION['user_id'], 'edit_user', "Edited user ID: $userId");
                    $success = 'User updated successfully.';
                }
                break;
            
            // DELETE USER
            case 'delete_user':
                $userId = (int)($_POST['user_id'] ?? 0);
                if ($userId > 0) {
                    $user = getUserById($pdo, $userId);
                    if ($user && $user['role'] === 'vip_user') {
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        logActivity($pdo, $_SESSION['user_id'], 'delete_user', "Deleted user: {$user['username']} (ID: $userId)");
                        $success = 'User deleted successfully.';
                    } else {
                        $error = 'User not found or cannot be deleted.';
                    }
                }
                break;
            
            // RESET PASSWORD
            case 'reset_password':
                $userId = (int)($_POST['user_id'] ?? 0);
                $newPassword = $_POST['new_password'] ?? '';
                
                if ($userId <= 0 || empty($newPassword) || strlen($newPassword) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } else {
                    resetUserPassword($pdo, $userId, $newPassword);
                    logActivity($pdo, $_SESSION['user_id'], 'reset_password', "Reset password for user ID: $userId");
                    $success = 'Password reset successfully.';
                }
                break;
            
            // TOGGLE VIP
            case 'toggle_vip':
                $userId = (int)($_POST['user_id'] ?? 0);
                $vipStatus = (int)($_POST['vip_access'] ?? 0);
                
                $stmt = $pdo->prepare("UPDATE users SET vip_access = ? WHERE id = ?");
                $stmt->execute([$vipStatus, $userId]);
                logActivity($pdo, $_SESSION['user_id'], 'toggle_vip', "Set VIP=" . ($vipStatus ? 'ON' : 'OFF') . " for user ID: $userId");
                $success = 'VIP access updated.';
                break;
            
            // ASSIGN STATUS
            case 'assign_status':
                $userId = (int)($_POST['user_id'] ?? 0);
                $statusId = !empty($_POST['status_id']) ? (int)$_POST['status_id'] : null;
                
                $stmt = $pdo->prepare("UPDATE users SET status_id = ? WHERE id = ?");
                $stmt->execute([$statusId, $userId]);
                logActivity($pdo, $_SESSION['user_id'], 'assign_status', "Assigned status ID $statusId to user ID: $userId");
                $success = 'Status assigned successfully.';
                break;
        }
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalUsersCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'vip_user'")->fetchColumn();
$totalPages = ceil($totalUsersCount / $perPage);

$users = $pdo->prepare("
    SELECT u.*, cs.name as status_name, cs.color as status_color, cs.icon as status_icon,
           cs.title as status_title, cs.message as status_message
    FROM users u
    LEFT JOIN custom_statuses cs ON u.status_id = cs.id
    WHERE u.role = 'vip_user'
    ORDER BY u.created_at DESC
    LIMIT ? OFFSET ?
");
$users->execute([$perPage, $offset]);
$usersList = $users->fetchAll();

// Get all statuses for dropdown
$statuses = $pdo->query("SELECT * FROM custom_statuses WHERE is_active = 1 ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($error): ?>
<div class="alert alert-danger"><span>❌</span> <?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><span>✅</span> <?= $success ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>👥 All Users</h2>
        <button class="btn btn-primary btn-sm" onclick="openModal('createUserModal')">➕ Create User</button>
    </div>
    <div class="card-body">
        <?php if (count($usersList) > 0): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th>VIP</th>
                    <th>Last Login</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usersList as $user): ?>
                <tr>
                    <td>
                        <strong><?= e($user['full_name']) ?></strong><br>
                        <small style="color: var(--text-muted);">@<?= e($user['username']) ?></small>
                    </td>
                    <td>
                        <?php if ($user['email']): ?><small>📧 <?= e($user['email']) ?></small><br><?php endif; ?>
                        <?php if ($user['telegram']): ?><small>💬 <?= e($user['telegram']) ?></small><br><?php endif; ?>
                        <?php if ($user['whatsapp']): ?><small>📱 <?= e($user['whatsapp']) ?></small><?php endif; ?>
                        <?php if (!$user['email'] && !$user['telegram'] && !$user['whatsapp']): ?>
                        <small style="color: var(--text-muted);">—</small>
                        <?php endif; ?>
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
                    <td style="font-size: 0.85rem; color: var(--text-muted);">
                        <?= $user['last_login'] ? formatDateTime($user['last_login'], 'd M Y H:i') : 'Never' ?>
                    </td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);">
                        <?= formatDate($user['created_at'], 'd M Y') ?>
                    </td>
                    <td>
                        <div class="actions" style="display: flex; gap: 4px; flex-wrap: wrap;">
                            <button class="btn btn-sm btn-secondary" onclick="editUser(<?= $user['id'] ?>)">✏️</button>
                            <button class="btn btn-sm btn-secondary" onclick="resetPass(<?= $user['id'] ?>)">🔑</button>
                            <button class="btn btn-sm btn-secondary" onclick="toggleVip(<?= $user['id'] ?>, <?= $user['vip_access'] ?>)">
                                <?= $user['vip_access'] ? '🔴' : '⭐' ?>
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="assignStatus(<?= $user['id'] ?>)">🏷️</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete user <?= e(addslashes($user['full_name'])) ?>?');">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">👥</span>
            <p>No users yet. Click "Create User" to add your first VIP member.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- CREATE USER MODAL -->
<div class="modal-backdrop" id="createUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3>➕ Create New User</h3>
            <button class="modal-close" onclick="closeModal('createUserModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="johndoe">
                </div>
                <div class="form-group">
                    <label>Password * (min 6 characters)</label>
                    <input type="text" name="password" required placeholder="Generate or type password" minlength="6">
                    <small style="color: var(--text-muted);">
                        <button type="button" class="btn btn-sm btn-secondary" style="margin-top: 4px;" onclick="generatePass('password')">🎲 Generate</button>
                    </small>
                </div>
                <div class="form-group">
                    <label>Email (Optional)</label>
                    <input type="email" name="email" placeholder="user@example.com">
                </div>
                <div class="form-group">
                    <label>Telegram Username</label>
                    <input type="text" name="telegram" placeholder="@username">
                </div>
                <div class="form-group">
                    <label>WhatsApp Number</label>
                    <input type="text" name="whatsapp" placeholder="+407XXXXXXXX">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('createUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">✅ Create User</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal-backdrop" id="editUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3>✏️ Edit User</h3>
            <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" id="editFullName" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail">
                </div>
                <div class="form-group">
                    <label>Telegram Username</label>
                    <input type="text" name="telegram" id="editTelegram">
                </div>
                <div class="form-group">
                    <label>WhatsApp Number</label>
                    <input type="text" name="whatsapp" id="editWhatsapp">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- RESET PASSWORD MODAL -->
<div class="modal-backdrop" id="resetPassModal">
    <div class="modal">
        <div class="modal-header">
            <h3>🔑 Reset Password</h3>
            <button class="modal-close" onclick="closeModal('resetPassModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" id="resetUserId">
                
                <div class="form-group">
                    <label>New Password * (min 6 characters)</label>
                    <input type="text" name="new_password" id="newPassword" required minlength="6">
                    <small style="color: var(--text-muted);">
                        <button type="button" class="btn btn-sm btn-secondary" style="margin-top: 4px;" onclick="generatePass('newPassword')">🎲 Generate</button>
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('resetPassModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">🔑 Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- TOGGLE VIP MODAL -->
<div class="modal-backdrop" id="toggleVipModal">
    <div class="modal">
        <div class="modal-header">
            <h3>⭐ Toggle VIP Access</h3>
            <button class="modal-close" onclick="closeModal('toggleVipModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="toggle_vip">
                <input type="hidden" name="user_id" id="toggleVipUserId">
                <input type="hidden" name="vip_access" id="toggleVipValue">
                
                <p>Are you sure you want to toggle VIP access for this user?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('toggleVipModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">⭐ Toggle VIP</button>
            </div>
        </form>
    </div>
</div>

<!-- ASSIGN STATUS MODAL -->
<div class="modal-backdrop" id="assignStatusModal">
    <div class="modal">
        <div class="modal-header">
            <h3>🏷️ Assign Status</h3>
            <button class="modal-close" onclick="closeModal('assignStatusModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="assign_status">
                <input type="hidden" name="user_id" id="assignStatusUserId">
                
                <div class="form-group">
                    <label>Select Status</label>
                    <select name="status_id" id="assignStatusSelect">
                        <option value="">— No Status —</option>
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= $s['id'] ?>" style="background: var(--bg-card);">
                            <?= e($s['icon']) ?> <?= e($s['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('assignStatusModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">✅ Assign</button>
            </div>
        </form>
    </div>
</div>

<script>
// Pass user data to JS
const usersData = <?= json_encode($usersList) ?>;

function editUser(id) {
    const user = usersData.find(u => u.id == id);
    if (!user) return;
    document.getElementById('editUserId').value = user.id;
    document.getElementById('editFullName').value = user.full_name;
    document.getElementById('editEmail').value = user.email || '';
    document.getElementById('editTelegram').value = user.telegram || '';
    document.getElementById('editWhatsapp').value = user.whatsapp || '';
    openModal('editUserModal');
}

function resetPass(id) {
    document.getElementById('resetUserId').value = id;
    document.getElementById('newPassword').value = '';
    openModal('resetPassModal');
}

function toggleVip(id, current) {
    document.getElementById('toggleVipUserId').value = id;
    document.getElementById('toggleVipValue').value = current ? 0 : 1;
    openModal('toggleVipModal');
}

function assignStatus(id) {
    document.getElementById('assignStatusUserId').value = id;
    document.getElementById('assignStatusSelect').value = '';
    openModal('assignStatusModal');
}

function generatePass(fieldId) {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById(fieldId).value = pass;
}
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
