/**
 * PUB CampusMate — Notification Drawer JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    const markReadButtons = document.querySelectorAll('.btn-mark-read');

    markReadButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const notifId = btn.dataset.id;
            try {
                const res = await window.pubFetch(`/notifications/${notifId}/read`, { method: 'POST' });
                if (res.ok) {
                    const row = btn.closest('.notification-item');
                    if (row) {
                        row.classList.remove('unread');
                        btn.remove();
                    }
                }
            } catch (err) {
                console.error('Error marking notification as read:', err);
            }
        });
    });
});
