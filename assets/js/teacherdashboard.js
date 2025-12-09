document.addEventListener('DOMContentLoaded', function() {
    const profileLink = document.querySelector('.profile-link');
    const profileDropdown = document.querySelector('.profile-dropdown');

    if (profileLink && profileDropdown) {
        profileLink.addEventListener('click', function(e) {
            profileDropdown.classList.toggle('show');
        });

        // Close the dropdown when clicking outside
        window.addEventListener('click', function(event) {
            if (!profileLink.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }

    // --- Reusable Logout Modal Logic ---
    const logoutLink = document.getElementById('logout-link');
    const modalContainer = document.getElementById('logout-modal-container');

    // Fetch and inject the modal
    fetch('../../components/logout_modal.html')
        .then(response => response.text())
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

    // Calendar logic
    var calendarEl = document.getElementById('calendar');
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            googleCalendarApiKey: window.GOOGLE_API_KEY || '', // Use the key from the global scope
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
                            // Reload calendar to show new event
                            calendar.refetchEvents();
                        } else {
                            alert('Error: ' + (data.error || 'Could not add event'));
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
        });
        calendar.render();
    }
});