document.addEventListener('DOMContentLoaded', function () {
    // --- Element Selectors ---
    const searchInput = document.getElementById('searchInput');
    const gradeFilter = document.getElementById('gradeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const studentTableBody = document.getElementById('studentTableBody');
    const addStudentButton = document.querySelector('.btn-primary');
    const modal = document.getElementById('addStudentModal');
    const logoutLink = document.getElementById('logout-link');
    const modalContainer = document.getElementById('logout-modal-container');

    // --- Logout Modal Setup ---
    if (logoutLink && modalContainer) {
        fetch('../../components/logout_modal.html')
            .then(response => response.text())
            .then(html => {
                modalContainer.innerHTML = html;
                const logoutModal = document.getElementById('logout-modal');
                const cancelLogout = document.getElementById('cancel-logout');
                if (logoutModal && cancelLogout) {
                    logoutLink.addEventListener('click', (e) => {
                        e.preventDefault();
                        logoutModal.classList.add('show');
                    });
                    cancelLogout.addEventListener('click', () => logoutModal.classList.remove('show'));
                    logoutModal.addEventListener('click', (e) => {
                        if (e.target === logoutModal) logoutModal.classList.remove('show');
                    });
                }
            })
            .catch(error => console.error('Error loading logout modal:', error));
    }

    // Skip if core elements don't exist
    if (!searchInput || !gradeFilter || !statusFilter || !studentTableBody) {
        console.warn('Student page elements not found');
        return;
    }

    // --- Add Student Modal Setup (if modal exists) ---
    if (modal) {
        const closeModalButton = modal.querySelector('.close-button');
        const cancelButton = modal.querySelector('#cancelButton');
        const addStudentForm = document.getElementById('addStudentForm');
        const nextButton = document.getElementById('nextButton');
        const prevButton = document.getElementById('prevButton');
        const saveButton = document.getElementById('saveButton');
        const formSteps = document.querySelectorAll('.form-step');
        const stepIndicators = document.querySelectorAll('.step-indicator .step');

        let currentStep = 1;

        const closeModal = () => {
            modal.classList.remove('show');
            if (addStudentForm) addStudentForm.reset();
            currentStep = 1;
        };

        const updateFormSteps = () => {
            formSteps.forEach(step => {
                step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
            });
            stepIndicators.forEach(step => {
                step.classList.toggle('active', parseInt(step.dataset.step) === currentStep);
            });
            if (prevButton) prevButton.style.display = currentStep === 1 ? 'none' : 'inline-flex';
            if (nextButton) nextButton.style.display = currentStep === formSteps.length ? 'none' : 'inline-flex';
            if (saveButton) saveButton.style.display = currentStep === formSteps.length ? 'inline-flex' : 'none';
        };

        if (addStudentButton) {
            addStudentButton.addEventListener('click', () => {
                modal.classList.add('show');
                currentStep = 1;
                updateFormSteps();
            });
        }

        if (closeModalButton) closeModalButton.addEventListener('click', closeModal);
        if (cancelButton) cancelButton.addEventListener('click', closeModal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        if (nextButton) {
            nextButton.addEventListener('click', () => {
                if (currentStep < formSteps.length) {
                    currentStep++;
                    updateFormSteps();
                }
            });
        }

        if (prevButton) {
            prevButton.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateFormSteps();
                }
            });
        }

        if (addStudentForm) {
            addStudentForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const lrn = document.getElementById('studentLRN')?.value || '';
                const firstName = document.getElementById('studentFirstName')?.value || '';
                const middleName = document.getElementById('studentMiddleName')?.value || '';
                const lastName = document.getElementById('studentLastName')?.value || '';
                const suffix = document.getElementById('studentSuffix')?.value || '';
                const nameParts = [firstName, middleName, lastName, suffix];
                const fullName = nameParts.filter(part => part).join(' ');
                alert(`Saving student:\nLRN: ${lrn}\nName: ${fullName}\n\n(This is a simulation. Data is not actually saved.)`);
                closeModal();
            });
        }
    }

    // --- Table Filtering ---
    const applyFilters = () => {
        const searchText = searchInput.value.toLowerCase();
        const selectedGrade = gradeFilter.value;
        const selectedStatus = statusFilter.value;
        const tableRows = studentTableBody.getElementsByTagName('tr');

        for (let i = 0; i < tableRows.length; i++) {
            const row = tableRows[i];
            const lrn = row.cells[0]?.textContent.toLowerCase() || '';
            const name = row.cells[1]?.textContent.toLowerCase() || '';
            const gradeSection = row.cells[2]?.textContent || '';
            const statusCell = row.cells[3]?.textContent || '';

            const searchMatch = lrn.includes(searchText) || name.includes(searchText);
            const gradeMatch = selectedGrade === "" || gradeSection.includes(selectedGrade);
            const statusMatch = selectedStatus === "" || statusCell.includes(selectedStatus);

            row.style.display = (searchMatch && gradeMatch && statusMatch) ? "" : "none";
        }
    };

    // Event listeners for filters
    searchInput.addEventListener('keyup', applyFilters);
    gradeFilter.addEventListener('change', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // Table action links
    studentTableBody.addEventListener('click', function(e) {
        if (e.target.tagName === 'A') {
            const action = e.target.textContent;
            const lrn = e.target.closest('tr').cells[0].textContent;
            alert(`Performing '${action}' action for student with LRN: ${lrn}`);
        }
    });
});