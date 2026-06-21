// ============================================================
// ADMIN JAVASCRIPT - FIXED BETS RO 🇷🇴
// ============================================================

/**
 * Delete item with AJAX
 */
function deleteItem(url, element, message = 'Are you sure you want to delete this?') {
    if (!confirm(message)) return;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            element.closest('tr')?.remove();
            showAlert(data.message || 'Deleted successfully.', 'success');
        } else {
            showAlert(data.message || 'Delete failed.', 'danger');
        }
    })
    .catch(() => {
        // Fallback: reload page
        if (confirm('AJAX failed. Reload page to confirm deletion?')) {
            window.location.reload();
        }
    });
}

/**
 * Toggle VIP access
 */
function toggleVIP(userId, checkbox) {
    const isActive = checkbox.checked ? 1 : 0;
    
    fetch('ajax_toggle_vip.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'user_id=' + userId + '&vip_access=' + isActive
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = checkbox.closest('tr').querySelector('.vip-badge');
            if (badge) {
                badge.className = 'vip-badge ' + (isActive ? 'active' : 'inactive');
                badge.textContent = isActive ? '⭐ VIP ON' : '❌ VIP OFF';
            }
            showAlert(data.message || 'VIP access updated.', 'success');
        } else {
            checkbox.checked = !checkbox.checked;
            showAlert(data.message || 'Update failed.', 'danger');
        }
    })
    .catch(() => {
        checkbox.checked = !checkbox.checked;
        showAlert('Connection error. Please try again.', 'danger');
    });
}

/**
 * Live status preview
 */
function updateStatusPreview() {
    const name = document.getElementById('statusName')?.value || 'Status Name';
    const icon = document.getElementById('statusIcon')?.value || '📋';
    const color = document.getElementById('statusColor')?.value || '#FFD700';
    const title = document.getElementById('statusTitle')?.value || 'Status Title';
    const message = document.getElementById('statusMessage')?.value || 'Status message goes here.';
    
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

/**
 * Live status preview event listeners
 */
document.addEventListener('DOMContentLoaded', function() {
    const inputs = ['statusName', 'statusIcon', 'statusColor', 'statusTitle', 'statusMessage'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', updateStatusPreview);
            el.addEventListener('change', updateStatusPreview);
        }
    });
});
