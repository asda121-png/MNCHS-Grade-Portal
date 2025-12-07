document.addEventListener('DOMContentLoaded', function () {
    // --- Element Selectors ---
    const searchInput = document.getElementById('searchInput');
    const gradeFilter = document.getElementById('gradeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const studentTableBody = document.getElementById('studentTableBody');
    const tableRows = studentTableBody.getElementsByTagName('tr');
    const addStudentButton = document.querySelector('.btn-primary');

    // --- Modal Selectors ---
    const modal = document.getElementById('addStudentModal');
    const closeModalButton = modal.querySelector('.close-button');
    const cancelButton = modal.querySelector('#cancelButton');
    const addStudentForm = document.getElementById('addStudentForm');
    const nextButton = document.getElementById('nextButton');
    const prevButton = document.getElementById('prevButton');
    const saveButton = document.getElementById('saveButton');
    const formSteps = document.querySelectorAll('.form-step');
    const stepIndicators = document.querySelectorAll('.step-indicator .step');

    // --- Event Listeners ---
    searchInput.addEventListener('keyup', applyFilters);
    gradeFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // --- Modal Event Listeners ---
    let currentStep = 1;

    addStudentButton.addEventListener('click', () => {
        modal.classList.add('show');
        currentStep = 1;
        updateFormSteps();
    });

    const closeModal = () => {
        modal.classList.remove('show');
        addStudentForm.reset(); // Clear the form fields
        currentStep = 1;
    };

    closeModalButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) { // Close if clicking on the overlay background
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
        formSteps.forEach(step => {
            step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
        });

        stepIndicators.forEach(step => {
            step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
        });

        if (currentStep === 1) {
            prevButton.style.display = 'none';
        } else {
            prevButton.style.display = 'inline-flex';
        }

        if (currentStep === formSteps.length) {
            nextButton.style.display = 'none';
            saveButton.style.display = 'inline-flex';
        } else {
            nextButton.style.display = 'inline-flex';
            saveButton.style.display = 'none';
        }
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

    addStudentForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Prevent actual form submission
        const lrn = document.getElementById('studentLRN').value;
        const firstName = document.getElementById('studentFirstName').value;
        const middleName = document.getElementById('studentMiddleName').value;
        const lastName = document.getElementById('studentLastName').value;
        const suffix = document.getElementById('studentSuffix').value;
        const nameParts = [firstName, middleName, lastName, suffix];
        const fullName = nameParts.filter(part => part).join(' ');
        alert(`Saving student:\nLRN: ${lrn}\nName: ${fullName}\n\n(This is a simulation. Data is not actually saved.)`);
        closeModal();
    });

    // Use event delegation for action links inside the table
    studentTableBody.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            const action = e.target.textContent; // 'View' or 'Edit'
            const lrn = e.target.closest('tr').cells[0].textContent;
            alert(`Performing '${action}' action for student with LRN: ${lrn}`);
        }
    });

    // --- Main Filter Function ---
    function applyFilters() {
        const searchText = searchInput.value.toLowerCase();
        const selectedGrade = gradeFilter.value;
        const selectedStatus = statusFilter.value;
        for (let i = 0; i < tableRows.length; i++) {
            const row = tableRows[i];
            const lrn = row.cells[0].textContent.toLowerCase();
            const name = row.cells[1].textContent.toLowerCase();
            const gradeSection = row.cells[2].textContent;
            const status = row.cells[3].querySelector('.status-badge').textContent;
            const searchMatch = lrn.includes(searchText) || name.includes(searchText);
            const gradeMatch = selectedGrade === "" || gradeSection.includes(selectedGrade);
            const statusMatch = selectedStatus === "" || status === selectedStatus;
            row.style.display = (searchMatch && gradeMatch && statusMatch) ? "" : "none";
        }
    }
});