document.addEventListener('DOMContentLoaded', function() {
    // --- Reusable Profile Dropdown Logic ---
    const profileLink = document.querySelector('.profile-link');
    const profileDropdown = document.querySelector('.profile-dropdown');

    if (profileLink && profileDropdown) {
        profileLink.addEventListener('click', function(e) {
            profileDropdown.classList.toggle('show');
        });

        window.addEventListener('click', function(event) {
            if (!profileLink.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove('show');
            }
        });
    }

    // --- Reusable Logout Modal Logic ---
    const logoutLink = document.getElementById('logout-link');
    const modalContainer = document.getElementById('logout-modal-container');

    if (logoutLink && modalContainer) {
        fetch('../../components/logout_modal.html')
            .then(response => response.text())
            .then(html => {
                modalContainer.innerHTML = html;
                const logoutModal = document.getElementById('logout-modal');
                const cancelLogout = document.getElementById('cancel-logout');
                logoutLink.addEventListener('click', (e) => { e.preventDefault(); logoutModal.classList.add('show'); });
                cancelLogout.addEventListener('click', () => logoutModal.classList.remove('show'));
                logoutModal.addEventListener('click', (e) => { if (e.target === modalContainer) logoutModal.classList.remove('show'); });
            })
            .catch(error => console.error('Error loading logout modal:', error));
    }
});