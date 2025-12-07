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
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            editable: true,
            selectable: true,
            eventSources: [{
                googleCalendarId: 'en.philippines#holiday@group.v.calendar.google.com',
                className: 'gcal-event'
            }],
            dateClick: function(info) {
                const title = prompt('Enter Event Title:');
                if (title) { calendar.addEvent({ title: title, start: info.dateStr, allDay: true }); }
            }
        });
        calendar.render();
    }
});