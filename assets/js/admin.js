// ============================================================
// ADMIN JAVASCRIPT - FIXED BETS RO
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
                const badgeIcon = isActive
                    ? '<svg class="ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>'
                    : '<svg class="ui-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>';
                badge.innerHTML = badgeIcon + (isActive ? ' VIP ON' : ' VIP OFF');
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
    const iconName = document.getElementById('statusIcon')?.value || 'tag';
    const iconPaths = {
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
    const icon = iconPaths[iconName] || iconPaths.tag;
    const colorValue = document.getElementById('statusColor')?.value || '#FFD700';
    const color = /^#[0-9a-f]{6}$/i.test(colorValue) ? colorValue : '#FFD700';
    const title = document.getElementById('statusTitle')?.value || 'Status Title';
    const message = document.getElementById('statusMessage')?.value || 'Status message goes here.';
    
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
