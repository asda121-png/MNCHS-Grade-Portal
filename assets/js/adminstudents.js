document.addEventListener('DOMContentLoaded', function () {
    // --- Element Selectors ---
    const searchInput = document.getElementById('searchInput');
    const gradeFilter = document.getElementById('gradeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const studentTableBody = document.getElementById('studentTableBody');
    const addStudentButton = document.getElementById('openAddStudentButton');
    const modal = document.getElementById('addStudentModal');
    const viewModal = document.getElementById('viewStudentModal');
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
    let closeAddModal = null;
    let openAddModal = null;

    if (modal) {
        const closeModalButton = modal.querySelector('.close-button');
        const cancelButton = modal.querySelector('#cancelButton');
        const addStudentForm = document.getElementById('addStudentForm');
        const nextButton = document.getElementById('nextButton');
        const prevButton = document.getElementById('prevButton');
        const saveButton = document.getElementById('saveButton');
        const formSteps = document.querySelectorAll('.form-step');
        const stepIndicators = document.querySelectorAll('.step-indicator .step');
        const modalTitle = modal.querySelector('#modalTitle');

        let currentStep = 1;
        const defaultModalTitle = modalTitle ? modalTitle.textContent : '';
        const defaultSaveText = saveButton ? saveButton.textContent : '';

        const setFieldValue = (id, value = '') => {
            const field = document.getElementById(id);
            if (field) field.value = value;
        };

        const updateFormSteps = () => {
            formSteps.forEach(step => {
                step.classList.toggle('active', parseInt(step.dataset.step, 10) === currentStep);
            });
            stepIndicators.forEach(step => {
                step.classList.toggle('active', parseInt(step.dataset.step, 10) === currentStep);
            });
            if (prevButton) prevButton.style.display = currentStep === 1 ? 'none' : 'inline-flex';
            if (nextButton) nextButton.style.display = currentStep === formSteps.length ? 'none' : 'inline-flex';
            if (saveButton) saveButton.style.display = currentStep === formSteps.length ? 'inline-flex' : 'none';
        };

        closeAddModal = () => {
            modal.classList.remove('show');
            if (addStudentForm) addStudentForm.reset();
            currentStep = 1;
            updateFormSteps();
            if (modalTitle) modalTitle.textContent = defaultModalTitle;
            if (saveButton) saveButton.textContent = defaultSaveText;
            if (addStudentForm) {
                delete addStudentForm.dataset.mode;
                delete addStudentForm.dataset.studentId;
            }
        };

        openAddModal = (mode = 'add', data = null) => {
            if (addStudentForm) addStudentForm.reset();
            if (modalTitle) modalTitle.textContent = mode === 'edit' ? 'Edit Student' : defaultModalTitle;
            if (saveButton) saveButton.textContent = mode === 'edit' ? 'Update Student' : defaultSaveText;
            if (addStudentForm) {
                addStudentForm.dataset.mode = mode;
                addStudentForm.dataset.studentId = data?.id || '';
            }
            if (data) {
                setFieldValue('studentLRN', data.lrn || '');
                let firstName = data.firstName || '';
                let middleName = data.middleName || '';
                let lastName = data.lastName || '';
                let suffix = data.suffix || '';

                if (!firstName && !lastName) {
                    const nameParts = (data.name || '').trim().split(/\s+/).filter(Boolean);
                    firstName = nameParts.shift() || '';
                    lastName = nameParts.length > 0 ? nameParts.pop() : '';
                    middleName = nameParts.join(' ');
                }

                setFieldValue('studentFirstName', firstName);
                setFieldValue('studentMiddleName', middleName);
                setFieldValue('studentLastName', lastName);
                setFieldValue('studentSuffix', suffix);
                setFieldValue('streetAddress', data.address || '');
                setFieldValue('city', data.city || '');
                setFieldValue('province', data.province || '');
                setFieldValue('fatherName', data.fatherName || '');
                setFieldValue('motherName', data.motherName || '');
                setFieldValue('guardianName', data.guardianName || '');
                setFieldValue('emergencyContactPerson', data.emergencyContactPerson || '');
                setFieldValue('emergencyContactNumber', data.emergencyContactNumber || '');
                setFieldValue('studentGradeSection', data.gradeSection || '');
                setFieldValue('studentStatus', data.status || 'Enrolled');
            }
            modal.classList.add('show');
            currentStep = 1;
            updateFormSteps();
        };

        if (addStudentButton) {
            addStudentButton.addEventListener('click', () => openAddModal('add'));
        }

        if (closeModalButton) closeModalButton.addEventListener('click', closeAddModal);
        if (cancelButton) cancelButton.addEventListener('click', closeAddModal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeAddModal();
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
                const actionText = addStudentForm.dataset.mode === 'edit' ? 'Updating' : 'Saving';
                const studentIdentifier = addStudentForm.dataset.studentId ? `ID: ${addStudentForm.dataset.studentId}\n` : '';
                alert(`${actionText} student (simulated):\n${studentIdentifier}LRN: ${lrn}\nName: ${fullName}`);
                closeAddModal();
            });
        }
    }

    if (viewModal) {
        const viewFields = {
            lrn: document.getElementById('viewStudentLrn'),
            studentNumber: document.getElementById('viewStudentNumber'),
            name: document.getElementById('viewStudentName'),
            grade: document.getElementById('viewStudentGrade'),
            section: document.getElementById('viewStudentSection'),
            status: document.getElementById('viewStudentStatus'),
            email: document.getElementById('viewStudentEmail'),
            username: document.getElementById('viewStudentUsername'),
            guardian: document.getElementById('viewStudentGuardian'),
            address: document.getElementById('viewStudentAddress'),
            dob: document.getElementById('viewStudentDob'),
            enrollment: document.getElementById('viewStudentEnrollment'),
        };

        const closeViewModal = () => viewModal.classList.remove('show');

        const displayText = (value, modifier) => {
            if (value === null || value === undefined) return 'N/A';
            const stringValue = String(value).trim();
            if (!stringValue) return 'N/A';
            const result = modifier ? modifier(stringValue) : stringValue;
            if (result === null || result === undefined || result === '') return 'N/A';
            return result;
        };

        const formatDate = (value) => {
            const normalized = value.split('T')[0].split(' ')[0];
            if (normalized === '0000-00-00') return 'N/A';
            const [year, month, day] = normalized.split('-').map(part => parseInt(part, 10));
            if (!year || !month || !day) return value;
            const date = new Date(Date.UTC(year, month - 1, day));
            if (Number.isNaN(date.getTime())) return value;
            return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        };

        viewModal.addEventListener('click', (e) => {
            if (e.target === viewModal) closeViewModal();
        });

        viewModal.querySelectorAll('[data-close="view"]').forEach(button => {
            button.addEventListener('click', closeViewModal);
        });

        viewModal.showDetails = (data) => {
            if (!data) return;
            if (viewFields.lrn) viewFields.lrn.textContent = displayText(data.lrn);
            if (viewFields.studentNumber) viewFields.studentNumber.textContent = displayText(data.studentNumber || data.id);
            if (viewFields.name) viewFields.name.textContent = displayText(data.name);
            if (viewFields.grade) {
                viewFields.grade.textContent = displayText(
                    data.gradeLevel,
                    (val) => val.toLowerCase().includes('grade') ? val : `Grade ${val}`
                );
            }
            if (viewFields.section) viewFields.section.textContent = displayText(data.section);
            if (viewFields.status) viewFields.status.textContent = displayText(data.status);
            if (viewFields.email) viewFields.email.textContent = displayText(data.email);
            if (viewFields.username) viewFields.username.textContent = displayText(data.username);
            if (viewFields.guardian) viewFields.guardian.textContent = displayText(data.guardianContact);
            if (viewFields.address) viewFields.address.textContent = displayText(data.address);
            if (viewFields.dob) viewFields.dob.textContent = displayText(data.dateOfBirth, formatDate);
            if (viewFields.enrollment) viewFields.enrollment.textContent = displayText(data.enrollmentDate, formatDate);
            viewModal.classList.add('show');
        };
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
        const link = e.target.closest('a');
        if (!link) return;
        e.preventDefault();
        const action = link.dataset.action || '';
        const row = link.closest('tr');
        if (!row) return;

        const { dataset } = row;
        const gradeLevel = dataset.studentGrade || '';
        const section = dataset.studentSection || '';
        const gradeLabel = gradeLevel ? (gradeLevel.toLowerCase().includes('grade') ? gradeLevel : `Grade ${gradeLevel}`) : '';
        const gradeSection = gradeLabel && section ? `${gradeLabel} - ${section}` : gradeLabel || section || (row.cells[2]?.textContent.trim() || '');

        const studentData = {
            id: dataset.studentId || '',
            studentNumber: dataset.studentNumber || '',
            lrn: dataset.studentLrn || row.cells[0]?.textContent.trim() || '',
            name: dataset.studentName || row.cells[1]?.textContent.trim() || '',
            firstName: dataset.studentFirstName || '',
            middleName: dataset.studentMiddleName || '',
            lastName: dataset.studentLastName || '',
            suffix: dataset.studentSuffix || '',
            gradeLevel,
            section,
            gradeSection,
            status: dataset.studentStatus || row.cells[3]?.textContent.trim() || '',
            email: dataset.studentEmail || '',
            username: dataset.studentUsername || '',
            guardianContact: dataset.studentGuardian || '',
            address: dataset.studentAddress || '',
            dateOfBirth: dataset.studentDob || '',
            enrollmentDate: dataset.studentEnrolled || '',
            guardianName: dataset.studentGuardianName || '',
            emergencyContactPerson: dataset.studentEmergencyContactPerson || '',
            emergencyContactNumber: dataset.studentEmergencyContactNumber || dataset.studentGuardian || ''
        };

        if (action === 'view' && viewModal && typeof viewModal.showDetails === 'function') {
            viewModal.showDetails(studentData);
            return;
        }

        if (action === 'edit' && openAddModal) {
            openAddModal('edit', studentData);
            return;
        }

        alert(`No handler configured for action: ${action}`);
    });
});