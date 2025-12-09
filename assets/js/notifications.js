// Notification System
class NotificationManager {
    constructor() {
        this.badge = document.querySelector('.notification-badge');
        this.bell = document.querySelector('.notification-bell');
        this.updateInterval = 30000; // Update every 30 seconds
        this.isDropdownOpen = false;
        this.init();
    }

    init() {
        // Initial badge update
        this.updateBadge();
        // Update notification count every 30 seconds
        setInterval(() => this.updateBadge(), this.updateInterval);
        
        // Click handler for notification bell
        if (this.bell) {
            this.bell.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle dropdown
                if (this.isDropdownOpen) {
                    this.closeDropdown();
                } else {
                    this.showNotifications();
                }
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (this.isDropdownOpen && !e.target.closest('.notification-bell') && !e.target.closest('.notification-dropdown')) {
                this.closeDropdown();
            }
        });
    }

    updateBadge() {
        fetch('../../server/api/notifications.php?action=get_count')
            .then(response => response.json())
            .then(data => {
                if (data.success && this.badge) {
                    const count = data.count;
                    if (count > 0) {
                        this.badge.textContent = count > 99 ? '99+' : count;
                        this.badge.style.display = 'flex';
                    } else {
                        this.badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error fetching notification count:', error));
    }

    showNotifications() {
        // Show loading state
        const container = this.createDropdownContainer();
        container.innerHTML = `
            <div style="padding: 20px; text-align: center; color: #636e72;">
                <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i>
                <p style="margin-top: 10px;">Loading...</p>
            </div>
        `;
        
        fetch('../../server/api/notifications.php?action=get_all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.displayNotificationList(data.notifications);
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
                container.innerHTML = `
                    <div style="padding: 20px; text-align: center; color: #e74c3c;">
                        <p>Error loading notifications</p>
                    </div>
                `;
            });
    }

    closeDropdown() {
        const dropdown = document.querySelector('.notification-dropdown');
        if (dropdown) {
            dropdown.remove();
        }
        this.isDropdownOpen = false;
    }

    createDropdownContainer() {
        // Remove existing dropdown
        const existing = document.querySelector('.notification-dropdown');
        if (existing) existing.remove();

        const container = document.createElement('div');
        container.className = 'notification-dropdown';
        container.style.cssText = `
            position: absolute;
            top: 60px;
            right: -120px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            min-width: 380px;
            max-width: 420px;
            max-height: 500px;
            overflow-y: auto;
            z-index: 2000;
        `;

        // Add to header
        this.bell.parentElement.appendChild(container);
        this.isDropdownOpen = true;
        
        return container;
    }

    displayNotificationList(notifications) {
        const container = document.querySelector('.notification-dropdown');
        
        if (notifications.length === 0) {
            container.innerHTML = `
                <div style="padding: 40px 20px; text-align: center; color: #636e72;">
                    <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                    <p style="font-weight: 500;">No notifications</p>
                </div>
            `;
        } else {
            let html = `
                <div style="padding: 12px 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; border-radius: 8px 8px 0 0;">
                    <strong style="color: #2d3436; font-size: 0.95rem;">Notifications</strong>
                    <button onclick="this.closest('.notification-dropdown').remove()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #636e72; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">&times;</button>
                </div>
                <div style="max-height: 420px; overflow-y: auto;">
            `;
            
            notifications.forEach(notif => {
                const timeAgo = this.formatTime(notif.created_at);
                const unreadIndicator = !notif.is_read ? '<div style="width: 8px; height: 8px; background: #e74c3c; border-radius: 50%; flex-shrink: 0;"></div>' : '<div style="width: 8px; height: 8px; background: transparent; border-radius: 50%; flex-shrink: 0;"></div>';
                
                html += `
                    <div style="padding: 12px 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f5f6fa'" onmouseout="this.style.background='white'" class="notification-item" data-id="${notif.id}">
                        <div style="display: flex; gap: 10px; align-items: flex-start;">
                            ${unreadIndicator}
                            <div style="flex: 1;">
                                <div style="font-weight: 500; color: #2d3436; font-size: 0.95rem;">${this.escapeHtml(notif.title)}</div>
                                ${notif.message ? `<div style="font-size: 0.85rem; color: #636e72; margin-top: 4px; line-height: 1.4;">${this.escapeHtml(notif.message)}</div>` : ''}
                                <div style="font-size: 0.8rem; color: #bbb; margin-top: 6px;">${timeAgo}</div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `
                </div>
                <div style="padding: 10px 15px; text-align: center; border-top: 1px solid #eee; background: #f9fafb; border-radius: 0 0 8px 8px;">
                    <button onclick="fetch('../../server/api/notifications.php', {method: 'POST', body: JSON.stringify({action: 'mark_read'})}).then(() => location.reload())" style="background: none; border: none; color: #800000; cursor: pointer; font-weight: 500; font-size: 0.9rem;">Mark all as read</button>
                </div>
            `;

            container.innerHTML = html;
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatTime(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diff = now - date;
        
        // Convert to seconds
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);

        if (seconds < 60) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        
        return date.toLocaleDateString();
    }
}

// Initialize notification manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new NotificationManager();
});
