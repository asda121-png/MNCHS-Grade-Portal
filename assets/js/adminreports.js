document.addEventListener('DOMContentLoaded', function() {
    const generateButtons = document.querySelectorAll('.btn-generate');
    const reportModal = document.getElementById('reportModal');
    const reportForm = document.getElementById('reportForm');
    const reportTypeInput = document.getElementById('reportTypeInput');
    const modalSections = document.querySelectorAll('.form-section');
    const modalTitle = document.getElementById('reportModalTitle');
    const closeModalButton = document.getElementById('closeReportModal');
    const cancelModalButton = document.getElementById('cancelReportModal');

    const studentMasterlistGrade = document.getElementById('studentMasterlistGrade');
    const studentMasterlistYear = document.getElementById('studentMasterlistYear');
    const sectionGradesGrade = document.getElementById('sectionGradesGrade');
    const sectionGradesSection = document.getElementById('sectionGradesSection');
    const sectionGradesSubject = document.getElementById('sectionGradesSubject');
    const sectionGradesYear = document.getElementById('sectionGradesYear');

    const reportOptions = window.reportOptions || {
        gradeLevels: [],
        sectionsByGrade: {},
        subjects: [],
        academicYears: []
    };

    if (sectionGradesSection) {
        sectionGradesSection.disabled = true;
    }

    const populateSelect = (selectElement, items, formatter) => {
        if (!selectElement) {
            return;
        }
        selectElement.innerHTML = '';
        items.forEach(item => {
            const option = document.createElement('option');
            const { value, label } = formatter(item);
            option.value = value;
            option.textContent = label;
            selectElement.appendChild(option);
        });
    };

    // Initial data population
    if (studentMasterlistGrade) {
        populateSelect(studentMasterlistGrade, [{ value: '', label: 'All grade levels' }, ...reportOptions.gradeLevels], item => {
            if (typeof item === 'object') {
                return item;
            }
            return { value: item, label: `Grade ${item}` };
        });
    }

    if (sectionGradesGrade) {
        populateSelect(sectionGradesGrade, [{ value: '', label: 'Select grade level' }, ...reportOptions.gradeLevels], item => {
            if (typeof item === 'object') {
                return item;
            }
            return { value: item, label: `Grade ${item}` };
        });
    }

    const academicYearOptions = reportOptions.academicYears.length
        ? [{ value: '', label: 'All academic years' }, ...reportOptions.academicYears.map(year => ({
            value: year.year,
            label: year.is_active ? `${year.year} (Active)` : year.year
        }))]
        : [{ value: '', label: 'All academic years' }];

    populateSelect(studentMasterlistYear, academicYearOptions, item => item);
    populateSelect(sectionGradesYear, academicYearOptions, item => item);

    const subjectOptions = reportOptions.subjects.length
        ? [{ value: '', label: 'Select subject' }, ...reportOptions.subjects.map(subject => ({
            value: subject.id || '',
            label: subject.name || 'Unnamed Subject'
        }))]
        : [{ value: '', label: 'Select subject' }];

    populateSelect(sectionGradesSubject, subjectOptions, item => item);

    const updateSectionsDropdown = (gradeLevel, targetSelect) => {
        if (!targetSelect) {
            return;
        }

        targetSelect.innerHTML = '';
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = gradeLevel ? 'Select section' : 'Select grade level first';
        targetSelect.appendChild(defaultOption);

        if (!gradeLevel) {
            targetSelect.disabled = true;
            return;
        }

        const sections = reportOptions.sectionsByGrade[String(gradeLevel)] || reportOptions.sectionsByGrade[gradeLevel] || [];
        sections.forEach(section => {
            const option = document.createElement('option');
            option.value = section;
            option.textContent = section;
            targetSelect.appendChild(option);
        });
        targetSelect.disabled = sections.length === 0;
    };

    if (sectionGradesGrade) {
        sectionGradesGrade.addEventListener('change', (event) => {
            updateSectionsDropdown(event.target.value, sectionGradesSection);
        });
    }

    const toggleModal = (show) => {
        if (!reportModal) {
            return;
        }
        reportModal.classList.toggle('show', show);
        if (show) {
            reportModal.setAttribute('aria-hidden', 'false');
        } else {
            reportModal.setAttribute('aria-hidden', 'true');
        }
    };

    const setRequiredFields = (activeSection) => {
        modalSections.forEach(section => {
            const inputs = section.querySelectorAll('select');
            inputs.forEach(input => {
                if (section === activeSection) {
                    if (input.dataset.required === 'true') {
                        input.setAttribute('required', 'required');
                    } else {
                        input.removeAttribute('required');
                    }
                } else {
                    input.removeAttribute('required');
                }
            });
        });
    };

    const showSectionForReport = (reportType) => {
        let titleSuffix = '';
        modalSections.forEach(section => {
            const shouldShow = section.dataset.section === reportType;
            section.classList.toggle('hidden', !shouldShow);
            if (shouldShow) {
                setRequiredFields(section);
            }
        });

        switch (reportType) {
            case 'student-masterlist':
                titleSuffix = 'Master List of Students';
                break;
            case 'section-grades':
                titleSuffix = 'Grades per Section';
                break;
            default:
                titleSuffix = '';
        }

        modalTitle.textContent = titleSuffix ? `Configure ${titleSuffix}` : 'Configure Report';
    };

    const handleGenerateClick = (reportType) => {
        if (!reportType) {
            return;
        }

        if (reportType === 'teacher-list') {
            window.location.href = `export_report.php?type=${encodeURIComponent(reportType)}`;
            return;
        }

        reportForm.reset();
        updateSectionsDropdown('', sectionGradesSection);
        reportTypeInput.value = reportType;
        showSectionForReport(reportType);
        toggleModal(true);
    };

    generateButtons.forEach(button => {
        button.addEventListener('click', function() {
            handleGenerateClick(this.dataset.reportType);
        });
    });

    const closeModal = () => toggleModal(false);

    closeModalButton?.addEventListener('click', closeModal);
    cancelModalButton?.addEventListener('click', closeModal);

    reportModal?.addEventListener('click', (event) => {
        if (event.target === reportModal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    reportForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(reportForm);
        const params = new URLSearchParams();
        formData.forEach((value, key) => {
            if (value !== null && value !== '') {
                params.append(key, value);
            }
        });

        const reportType = formData.get('type');
        if (!reportType) {
            return;
        }

        window.location.href = `export_report.php?${params.toString()}`;
        closeModal();
    });

    // --- Reusable Logout Modal Logic ---
    const logoutLink = document.getElementById('logout-link');
    const modalContainer = document.getElementById('logout-modal-container');

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