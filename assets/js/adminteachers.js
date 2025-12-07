document.addEventListener('DOMContentLoaded', function () {
    // --- Element Selectors ---
    const searchInput = document.getElementById('searchInput');
    const teacherTableBody = document.getElementById('teacherTableBody');
    const tableRows = teacherTableBody.getElementsByTagName('tr');
    const addTeacherButton = document.getElementById('addTeacherBtn');

    // --- Modal Selectors ---
    const modal = document.getElementById('addTeacherModal');
    const closeModalButton = modal.querySelector('.close-button');
    const cancelButton = modal.querySelector('#cancelButton');
    const addTeacherForm = document.getElementById('addTeacherForm');
    const nextButton = document.getElementById('nextButton');
    const prevButton = document.getElementById('prevButton');
    const saveButton = document.getElementById('saveButton');
    const formSteps = document.querySelectorAll('.form-step');
    const stepIndicators = document.querySelectorAll('.step-indicator .step');

    // --- Event Listeners ---
    searchInput.addEventListener('keyup', applyFilters);

    // --- Modal Event Listeners ---
    let currentStep = 1;

    addTeacherButton.addEventListener('click', () => {
        modal.classList.add('show');
        currentStep = 1;
        updateFormSteps();
        addTeacherForm.reset();
    });

    const closeModal = () => {
        modal.classList.remove('show');
        addTeacherForm.reset();
        currentStep = 1;
    };

    closeModalButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    nextButton.addEventListener('click', () => {
        if (currentStep < formSteps.length) {
            currentStep++;
            updateFormSteps();
        }
    });

    prevButton.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateFormSteps();
        }
    });

    function updateFormSteps() {
        formSteps.forEach(step => step.classList.toggle('active', parseInt(step.dataset.step) === currentStep));
        stepIndicators.forEach(step => step.classList.toggle('active', parseInt(step.dataset.step) === currentStep));
        prevButton.style.display = currentStep === 1 ? 'none' : 'inline-flex';
        nextButton.style.display = currentStep === formSteps.length ? 'none' : 'inline-flex';
        saveButton.style.display = currentStep === formSteps.length ? 'inline-flex' : 'none';
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
            logoutLink.addEventListener('click', (e) => { e.preventDefault(); logoutModal.classList.add('show'); });
            cancelLogout.addEventListener('click', () => logoutModal.classList.remove('show'));
            logoutModal.addEventListener('click', (e) => { if (e.target === logoutModal) logoutModal.classList.remove('show'); });
        })
        .catch(error => console.error('Error loading logout modal:', error));

    // --- Main Filter Function ---
    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        for (let i = 0; i < tableRows.length; i++) {
            const row = tableRows[i];
            const employeeId = row.cells[0].textContent.toLowerCase();
            const name = row.cells[1].textContent.toLowerCase();
            const searchMatch = employeeId.includes(searchText) || name.includes(searchText);
            row.style.display = searchMatch ? "" : "none";
        }
    }
});