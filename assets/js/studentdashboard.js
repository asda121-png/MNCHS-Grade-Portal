document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown logic
    const profileLink = document.querySelector('.profile-link');
    const profileDropdown = document.querySelector('.profile-dropdown');

    if (profileLink && profileDropdown) {
        profileLink.addEventListener('click', function(e) {
            profileDropdown.classList.toggle('show');
        });
        // Close the dropdown when clicking outside
        window.addEventListener('click', function(event) {
            if (!profileLink.contains(event.target) && !profileDropdown.contains(event.target)) {profileDropdown.classList.remove('show'); }
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

            // Now that the modal is in the DOM, add event listeners
            const logoutModal = document.getElementById('logout-modal');
            const cancelLogout = document.getElementById('cancel-logout');

            logoutLink.addEventListener('click', (e) => { e.preventDefault(); logoutModal.classList.add('show'); });
            cancelLogout.addEventListener('click', () => logoutModal.classList.remove('show'));
            logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('show'); });
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
            events: {
                googleCalendarId: 'en.philippines#holiday@group.v.calendar.google.com',
                className: 'gcal-event' // optional, for styling
            }
        });
        calendar.render();
    }
});