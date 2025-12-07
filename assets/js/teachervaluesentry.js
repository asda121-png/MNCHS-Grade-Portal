document.addEventListener('DOMContentLoaded', function() {
    // --- Values Entry Logic ---
    const loadStudentsBtn = document.getElementById('load-students-btn');
    const studentSelectionContainer = document.getElementById('student-selection-container');
    const valuesFormContainer = document.getElementById('values-form-container');
    const studentList = document.getElementById('student-list');
    const studentNameHeader = document.getElementById('student-name-header');
    const valuesTbody = document.getElementById('values-tbody');
    const backToListBtn = document.getElementById('back-to-list-btn');
    const searchInput = document.getElementById('student-search-input');
    const classSelect = document.getElementById('class-select');
    const quarterSelect = document.getElementById('quarter-select');

    const mockStudents = [
        { id: 1, name: 'Aguilar, Juan D.' },
        { id: 2, name: 'Bautista, Maria C.' },
        { id: 3, name: 'Cruz, Pedro S.' }
    ];

    const coreValuesData = [{
        name: '1. Maka-Diyos',
        statements: [
            "Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.",
            "Shows adherence to ethical acts."
        ]
    }, {
        name: '2. Makatao',
        statements: [
            "Is sensitive to individual, social, and cultural differences.",
            "Demonstrates contributions toward solidarity."
        ]
    }, {
        name: '3. Makakalikasan',
        statements: [
            "Cares for the environment and utilizes resources wisely, judiciously, and economically."
        ]
    }, {
        name: '4. Makabansa',
        statements: [
            "Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.",
            "Demonstrates appropriate behavior in carrying out activities in the school, community, and country."
        ]
    }];

    function renderStudentList(studentsToRender) {
        studentList.innerHTML = '';
        if (studentsToRender.length === 0) {
            studentList.innerHTML = '<li style="text-align: center; color: var(--text-light); cursor: default;">No students found.</li>';
            return;
        }
        studentsToRender.forEach(student => {
            const li = document.createElement('li');
            li.innerHTML = `<i class="fas fa-user"></i> ${student.name}`;
            li.dataset.studentId = student.id;
            li.dataset.studentName = student.name;
            studentList.appendChild(li);
        });
    }

    // 1. Handle "Load Students" button click
    loadStudentsBtn.addEventListener('click', () => {
        studentList.innerHTML = '<li>Loading...</li>';
        studentSelectionContainer.style.display = 'block';
        valuesFormContainer.style.display = 'none';
        searchInput.value = ''; // Clear search on new load

        // Simulate fetching students
        setTimeout(() => {
            renderStudentList(mockStudents);
        }, 500);
    });

    // Handle search input
    searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const filteredStudents = mockStudents.filter(student => student.name.toLowerCase().includes(searchTerm));
        renderStudentList(filteredStudents);
    });

    // 2. Handle clicking on a student from the list
    studentList.addEventListener('click', (e) => {
        if (e.target && e.target.tagName === 'LI') {
            const studentId = e.target.dataset.studentId;
            const studentName = e.target.dataset.studentName;

            // Show the form and hide the list
            studentSelectionContainer.style.display = 'none';
            valuesFormContainer.style.display = 'block';
            studentNameHeader.textContent = `Editing Values for: ${studentName}`;

            // Populate the values table for the selected student
            populateValuesForm(studentId);
        }
    });

    // 3. Handle "Back to List" button click
    backToListBtn.addEventListener('click', () => {
        studentSelectionContainer.style.display = 'block';
        valuesFormContainer.style.display = 'none';
    });

    // Function to build the form for a student
    function populateValuesForm(studentId) {
        valuesTbody.innerHTML = ''; // Clear previous form

        let valueIndex = 0;
        coreValuesData.forEach(coreValue => {
            coreValue.statements.forEach((statement, statementIndex) => {
                const tr = document.createElement('tr');

                // Add the Core Value cell only for the first statement in the group
                if (statementIndex === 0) {
                    const coreValueTd = document.createElement('td');
                    coreValueTd.className = 'student-name-col'; // Using this class for similar styling
                    coreValueTd.rowSpan = coreValue.statements.length;
                    coreValueTd.textContent = coreValue.name;
                    tr.appendChild(coreValueTd);
                }

                const statementTd = document.createElement('td');
                statementTd.className = 'behavior-col';
                statementTd.textContent = statement;

                const ratingTd = document.createElement('td');
                const select = document.createElement('select');
                select.name = `student_${studentId}_v${valueIndex + 1}`;
                select.innerHTML = `
                    <option value="AO">AO</option>
                    <option value="SO">SO</option>
                    <option value="RO">RO</option>
                    <option value="NO">NO</option>
                `;
                ratingTd.appendChild(select);

                tr.appendChild(statementTd);
                tr.appendChild(ratingTd);
                valuesTbody.appendChild(tr);
                valueIndex++;
            });
        });
    }

    // --- Pre-select filters from URL and auto-load ---
    const urlParams = new URLSearchParams(window.location.search);
    const classParam = urlParams.get('class');
    const quarterParam = urlParams.get('quarter');

    if (classParam && quarterParam) {
        if (classSelect) {
            classSelect.value = classParam;
        }
        if (quarterSelect) {
            quarterSelect.value = quarterParam;
        }
        // Automatically trigger the "Load Students" button
        loadStudentsBtn.click();
    }
});