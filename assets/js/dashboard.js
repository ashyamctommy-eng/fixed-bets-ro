// ============================================================
// USER DASHBOARD JAVASCRIPT - FIXED BETS RO 🇷🇴
// ============================================================

/**
 * Mark notification as read
 */
function markAsRead(notifId) {
    fetch('ajax_mark_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'notification_id=' + notifId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`.notif-item[data-id="${notifId}"]`);
            if (item) {
                item.classList.remove('unread');
            }
            updateNotifCount(-1);
        }
    })
    .catch(() => {});
}

/**
 * Mark all notifications as read
 */
function markAllAsRead() {
    fetch('ajax_mark_all_read.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notif-item.unread').forEach(item => {
                item.classList.remove('unread');
            });
            updateNotifCount(0, true);
            showAlert('All notifications marked as read.', 'success');
        }
    })
    .catch(() => {});
}

/**
 * Delete notification
 */
function deleteNotification(notifId) {
    if (!confirm('Delete this notification?')) return;
    
    fetch('ajax_delete_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'notification_id=' + notifId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.querySelector(`.notif-item[data-id="${notifId}"]`);
            if (item) item.remove();
            showAlert('Notification deleted.', 'success');
        }
    })
    .catch(() => {});
}

/**
 * Update notification count badge
 */
function updateNotifCount(delta, reset = false) {
    const badges = document.querySelectorAll('.notif-count, .nav-badge');
    badges.forEach(badge => {
        let count = parseInt(badge.textContent) || 0;
        
        if (reset) {
            count = 0;
        } else {
            count = Math.max(0, count + delta);
        }
        
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
}

/**
 * Click to read notification
 */
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.notif-item.unread').forEach(item => {
        item.addEventListener('click', function(e) {
            if (e.target.closest('.notif-actions')) return;
            const id = this.dataset.id;
            if (id) markAsRead(id);
        });
    });
});
