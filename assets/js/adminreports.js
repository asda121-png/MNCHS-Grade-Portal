document.addEventListener('DOMContentLoaded', function() {
    const generateButtons = document.querySelectorAll('.btn-generate');

    generateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const reportType = this.dataset.reportType;
            alert(`Generating report: ${reportType}. Backend functionality is needed to create and download the file.`);
        });
    });

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
            logoutLink.addEventListener('click', (e) => { e.preventDefault(); logoutModal.classList.add('show'); });
            cancelLogout.addEventListener('click', () => logoutModal.classList.remove('show'));
            logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('show'); });
        })
        .catch(error => console.error('Error loading logout modal:', error));
});