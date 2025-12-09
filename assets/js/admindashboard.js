// ===== Custom Notification System =====
function showNotification(message, type = 'success', duration = 3000) {
    // Create notification container if it doesn't exist
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
        `;
        document.body.appendChild(notificationContainer);
    }

    // Create notification element
    const notification = document.createElement('div');
    const bgColor = type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196F3';
    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
    
    notification.style.cssText = `
        background-color: ${bgColor};
        color: white;
        padding: 16px 20px;
        margin-bottom: 12px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 15px;
        font-weight: 500;
        animation: slideIn 0.3s ease-out;
    `;
    
    notification.innerHTML = `
        <span style="font-size: 20px; font-weight: bold;">${icon}</span>
        <span>${message}</span>
    `;
    
    notificationContainer.appendChild(notification);

    // Auto-remove notification
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => notification.remove(), 300);
    }, duration);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

document.addEventListener('DOMContentLoaded', function() {
    // --- Reusable Logout Modal Logic ---
    const logoutLink = document.getElementById('logout-link');
    const modalContainer = document.getElementById('logout-modal-container');

    if (logoutLink && modalContainer) {
        fetch('../../components/logout_modal.html')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                modalContainer.innerHTML = html;
                const logoutModal = document.getElementById('logout-modal');
                const cancelLogout = document.getElementById('cancel-logout');

                if (logoutModal && cancelLogout) {
                    logoutLink.addEventListener('click', (e) => { e.preventDefault(); logoutModal.classList.add('show'); });
                    cancelLogout.addEventListener('click', () => logoutModal.classList.remove('show'));
                    logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('show'); });
                }
            })
            .catch(error => console.error('Error loading logout modal:', error));
    }

    // --- Calendar Logic ---
    const calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        window.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            googleCalendarApiKey: window.GOOGLE_API_KEY || '',
            eventSources: [
                {
                    googleCalendarId: 'en.philippines#holiday@group.v.calendar.google.com',
                    className: 'gcal-event'
                },
                {
                    url: '../../server/api/events.php?action=get',
                    failure: function() {
                        console.error('Error fetching custom events');
                    }
                }
            ],
            editable: true,
            selectable: true,
            dateClick: function(info) {
                const title = prompt('Enter Event Title:');
                if (title) {
                    // Save event to database
                    fetch('../../server/api/events.php?action=add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            title: title,
                            start: info.dateStr,
                            end: info.dateStr
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Event added successfully!', 'success');
                            // Reload calendar to show new event
                            window.calendar.refetchEvents();
                        } else {
                            showNotification('Error: ' + (data.error || 'Could not add event'), 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error adding event', 'error');
                    });
                }
            }
        });
        window.calendar.render();
    }

    // --- Grading Period Modal Logic ---
    const gradingPeriodModal = document.getElementById('grading-period-modal');
    const addGradingPeriodBtn = document.getElementById('add-grading-period-btn');
    const gradingPeriodForm = document.getElementById('grading-period-form');
    const gpCancelBtn = document.getElementById('gp-cancel-btn');

    if (addGradingPeriodBtn && gradingPeriodModal) {
        addGradingPeriodBtn.addEventListener('click', () => {
            gradingPeriodModal.style.display = 'flex';
        });

        gpCancelBtn.addEventListener('click', () => {
            gradingPeriodModal.style.display = 'none';
            gradingPeriodForm.reset();
        });

        gradingPeriodModal.addEventListener('click', (e) => {
            if (e.target === gradingPeriodModal) {
                gradingPeriodModal.style.display = 'none';
                gradingPeriodForm.reset();
            }
        });

        gradingPeriodForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const quarter = document.getElementById('gp-quarter').value;
            const startDate = document.getElementById('gp-start-date').value;
            const endDate = document.getElementById('gp-end-date').value;

            if (!quarter || !startDate || !endDate) {
                showNotification('Please fill all fields', 'error');
                return;
            }

            // Validate dates
            if (new Date(startDate) >= new Date(endDate)) {
                showNotification('Start date must be before end date', 'error');
                return;
            }

            console.log('Adding grading period:', {quarter, startDate, endDate});

            // Show loading state
            const submitBtn = gradingPeriodForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';

            fetch('../../server/api/grading_periods.php?action=add_grading_period', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    quarter: parseInt(quarter),
                    start_date: startDate,
                    end_date: endDate
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.text().then(text => {
                    try {
                        return { status: response.status, data: JSON.parse(text) };
                    } catch (e) {
                        return { status: response.status, data: { error: 'Invalid JSON response: ' + text } };
                    }
                });
            })
            .then(({ status, data }) => {
                console.log('API Response:', { status, data });
                
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                
                if (status === 200 && data.success) {
                    showNotification('✓ Grading period added successfully!', 'success', 4000);
                    gradingPeriodModal.style.display = 'none';
                    gradingPeriodForm.reset();
                    // Reload calendar to show new grading period
                    if (window.calendar) {
                        window.calendar.refetchEvents();
                    }
                } else if (status === 403) {
                    showNotification('Admin access required to add grading periods', 'error', 4000);
                } else if (status === 401) {
                    showNotification('Session expired. Please log in again.', 'error', 4000);
                } else {
                    showNotification('Error: ' + (data.error || 'Could not add grading period'), 'error', 4000);
                }
            })
            .catch(error => {
                console.error('Network Error:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                showNotification('Network error: ' + error.message, 'error', 4000);
            });
        });
    }
});