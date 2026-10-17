/**
 * PUB CampusMate — Main Application JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggle for Mobile View
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const appSidebar = document.querySelector('.app-sidebar');

    if (sidebarToggleBtn && appSidebar) {
        sidebarToggleBtn.addEventListener('click', () => {
            appSidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth < 992 && !appSidebar.contains(e.target) && !sidebarToggleBtn.contains(e.target)) {
                appSidebar.classList.remove('show');
            }
        });
    }

    // 2. Setup CSRF Token for Fetch Requests
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    window.csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    window.pubFetch = async (url, options = {}) => {
        options.headers = options.headers || {};
        options.headers['X-CSRF-TOKEN'] = window.csrfToken;
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
        return fetch(url, options);
    };

    // 3. Auto dismiss alerts after 5 seconds
    const autoAlerts = document.querySelectorAll('.alert-dismissible');
    autoAlerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
});
