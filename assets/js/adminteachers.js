document.addEventListener('DOMContentLoaded', function() {
    let teachers = []; // Will be populated from API
    
    const tableBody = document.getElementById('teachers-table-body');
    const modal = document.getElementById('teacher-modal');
    const form = document.getElementById('teacher-form');
    const addBtn = document.getElementById('add-teacher-btn');
    const closeBtn = document.getElementById('close-teacher-modal');
    
    // Dynamic Department Fields
    const deptSelect = document.getElementById('department');
    const shsGroup = document.getElementById('shs-strand-group');
    const jhsGroup = document.getElementById('jhs-dept-group');
    const shsSelect = document.getElementById('shs-strand');
    const jhsSelect = document.getElementById('jhs-dept');
    const subjectContainer = document.getElementById('subject-input-wrapper');
    const subjectLabel = document.getElementById('subject-label');
    const sectionsContainer = document.getElementById('sections-container');

    // Subject Data Mapping
    const subjectData = {
        "English Department": {
            JHS: ["English 7", "English 8", "English 9", "English 10"],
            SHS: ["Oral Communication", "Reading and Writing", "Creative Writing", "21st Century Literature"]
        },
        "Mathematics Department": {
            JHS: ["Mathematics 7", "Mathematics 8", "Mathematics 9", "Mathematics 10"],
            SHS: ["General Mathematics", "Statistics and Probability", "Pre-Calculus", "Basic Calculus"]
        },
        "Science Department": {
            JHS: ["Science 7", "Science 8", "Science 9", "Science 10"],
            SHS: ["Earth and Life Science", "Physical Science", "Biology", "Chemistry", "Physics"]
        },
        "Social Studies Department": {
            JHS: ["Araling Panlipunan 7", "Araling Panlipunan 8", "Araling Panlipunan 9", "Araling Panlipunan 10"],
            SHS: ["Philippine Politics and Governance", "Contemporary Philippine Arts from the Regions", "Introduction to World Religions and Belief Systems"]
        },
        "Filipino / Language Department": {
            JHS: ["Filipino 7", "Filipino 8", "Filipino 9", "Filipino 10"],
            SHS: ["Komunikasyon at Pananaliksik sa Wika at Kulturang Pilipino", "Pagbasa at Pagsusuri ng Iba’t Ibang Teksto Tungo sa Pananaliksik", "21st Century Literature (Filipino)"]
        },
        "MAPEH Department": {
            JHS: [
                "Music 7", "Music 8", "Music 9", "Music 10",
                "Arts 7", "Arts 8", "Arts 9", "Arts 10",
                "PE 7", "PE 8", "PE 9", "PE 10",
                "Health 7", "Health 8", "Health 9", "Health 10"
            ],
            SHS: [] 
        },
        "TLE / TVL Department": {
            JHS: ["Technology and Livelihood Education 7", "Technology and Livelihood Education 8", "Technology and Livelihood Education 9", "Technology and Livelihood Education 10"],
            SHS: []
        }
    };

    // Section Data Mapping
    const shsSections = {
        "11": [
            "STEM 11-A", "STEM 11-B", 
            "ABM 11-C", "ABM 11-D", 
            "HUMSS 11-E", "HUMSS 11-F", 
            "GAS 11-G", "GAS 11-H", 
            "ICT 11-I", "HE 11-J", "IA 11-K", "AFA 11-L", 
            "Sports 11-M", "Sports 11-N", "Sports 11-O", 
            "Arts 11-P", "Arts 11-Q"
        ],
        "12": [
            "STEM 12-A", "STEM 12-B", 
            "ABM 12-C", "ABM 12-D", 
            "HUMSS 12-E", "HUMSS 12-F", 
            "GAS 12-G", "GAS 12-H", 
            "ICT 12-I", "HE 12-J", "IA 12-K", "AFA 12-L", 
            "Sports 12-M", "Sports 12-N", "Sports 12-O", 
            "Arts 12-P", "Arts 12-Q"
        ]
    };

    function generateJHSSections() {
        const sections = {};
        for (let i = 7; i <= 10; i++) {
            sections[i] = [];
            for (let j = 1; j <= 20; j++) {
                sections[i].push(`Grade ${i} - Section ${j}`);
            }
        }
        return sections;
    }
    const jhsSections = generateJHSSections();

    function updateFormFields() {
        const level = deptSelect.value;
        
        // 1. Update Subject/Grade Level Input
        subjectContainer.innerHTML = '';
        if (level === 'Junior High School') {
            subjectLabel.textContent = 'Grade Level Handled';
            const gradesDiv = document.createElement('div');
            gradesDiv.style.display = 'flex';
            gradesDiv.style.gap = '15px';
            
            [7, 8, 9, 10].forEach(grade => {
                const label = document.createElement('label');
                label.style.display = 'flex';
                label.style.alignItems = 'center';
                label.style.gap = '5px';
                label.style.fontWeight = 'normal';
                
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.value = grade;
                cb.name = 'grade_level_checkbox';
                cb.style.width = 'auto';
                
                label.appendChild(cb);
                label.appendChild(document.createTextNode(`Grade ${grade}`));
                gradesDiv.appendChild(label);
            });
            subjectContainer.appendChild(gradesDiv);
            
        } else if (level === 'Senior High School') {
            subjectLabel.textContent = 'Subject Handled (Select One)';
            const select = document.createElement('select');
            select.id = 'subjects';
            select.innerHTML = '<option value="">Select Subject</option>';
            
            let subjects = [];
            for (const dept in subjectData) {
                if (subjectData[dept].SHS) {
                    subjects = subjects.concat(subjectData[dept].SHS);
                }
            }
            
            subjects.sort().forEach(sub => {
                const opt = document.createElement('option');
                opt.value = sub;
                opt.textContent = sub;
                select.appendChild(opt);
            });
            subjectContainer.appendChild(select);
        } else {
            subjectContainer.innerHTML = '<input type="text" disabled placeholder="Select department first">';
        }

        // 2. Update Sections Checkboxes & Advisory Dropdown
        updateSectionsAndAdvisory(level);
    }

    function updateSectionsAndAdvisory(level) {
        sectionsContainer.innerHTML = '';
        const advisorySelect = document.getElementById('advisory-section');
        // Keep the first option
        advisorySelect.innerHTML = '<option value="">-- No Advisory Class --</option>';

        let allSections = [];

        if (level === 'Senior High School') {
            allSections = [...shsSections["11"], ...shsSections["12"]];
        } else if (level === 'Junior High School') {
            allSections = [...jhsSections[7], ...jhsSections[8], ...jhsSections[9], ...jhsSections[10]];
        }

        if (allSections.length === 0) {
            sectionsContainer.innerHTML = '<p style="color:#999; font-size:0.9rem;">Select a department/level to view sections.</p>';
            return;
        }

        // Create Grid for Sections
        const grid = document.createElement('div');
        grid.className = 'sections-grid';

        // Helper to normalize section names for comparison (remove spaces, lowercase)
        const normalize = s => s ? s.replace(/\s/g, '').toLowerCase() : '';

        // Get list of sections already taken as advisory by OTHER teachers
        const currentTeacherId = document.getElementById('teacher-id').value;
        
        // Get set of all currently advised sections (by anyone)
        const allAdvisedSections = new Set();
        teachers.forEach(t => {
            if (t.advisory) {
                allAdvisedSections.add(normalize(t.advisory));
            }
        });

        // Get set of sections advised by OTHER teachers (for dropdown exclusion)
        const otherAdvisedSections = new Set();
        teachers.forEach(t => {
            if (t.id != currentTeacherId && t.advisory) {
                otherAdvisedSections.add(normalize(t.advisory));
            }
        });

        allSections.forEach(section => {
            const normalizedSection = normalize(section);

            // 1. Populate Assigned Sections Checkboxes
            const label = document.createElement('label');
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.gap = '5px';
            label.style.fontSize = '0.85rem';
            
            const cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.value = section;
            cb.name = 'section_checkbox';
            cb.style.width = 'auto';
            
            // Requirement: Section only available if there is an advisor handling it
            if (!allAdvisedSections.has(normalizedSection)) {
                cb.disabled = true;
                label.style.opacity = '0.5';
                label.title = "This section has no advisor assigned yet.";
            }
            
            // Add listener for mutual exclusion
            cb.addEventListener('change', function() {
                if (this.checked && advisorySelect.value === this.value) {
                    alert('You cannot select the same section for both Advisory and Subject Teaching.');
                    this.checked = false;
                }
            });

            label.appendChild(cb);
            label.appendChild(document.createTextNode(section));
            grid.appendChild(label);

            // 2. Populate Advisory Dropdown (if not taken by others)
            if (!otherAdvisedSections.has(normalizedSection)) {
                const opt = document.createElement('option');
                
                // Try to resolve ID from window.dbClasses
                let val = section;
                if (window.dbClasses) {
                    // Try to match section string to DB record
                    const found = window.dbClasses.find(c => {
                        const dbStr1 = c.section;
                        const dbStr2 = c.class_name + ' ' + c.section;
                        const dbStr3 = c.class_name + '-' + c.section;
                        const dbStr4 = c.class_name + ' - ' + c.section;
                        
                        return normalize(dbStr1) === normalizedSection || 
                               normalize(dbStr2) === normalizedSection || 
                               normalize(dbStr3) === normalizedSection ||
                               normalize(dbStr4) === normalizedSection;
                    });
                    if (found) val = found.id;
                }
                
                opt.value = val;
                opt.textContent = section;
                advisorySelect.appendChild(opt);
            }
        });

        sectionsContainer.appendChild(grid);

        // Add listener to Advisory Select for mutual exclusion
        advisorySelect.addEventListener('change', function() {
            const selectedAdvisory = this.value;
            if (!selectedAdvisory) return;

            const checkboxes = document.querySelectorAll('input[name="section_checkbox"]');
            checkboxes.forEach(cb => {
                if (cb.value === selectedAdvisory && cb.checked) {
                    alert('You cannot select the same section for both Advisory and Subject Teaching.');
                    this.value = ""; // Reset dropdown
                }
            });
            
            // Disable the checkbox for the selected advisory? 
            // The prompt says "cant select". Alerting and resetting is a valid way to enforce.
        });
    }

    // Toggle function exposed to global scope for onchange attribute
    window.toggleDepartmentFields = function() {
        shsGroup.style.display = 'none';
        jhsGroup.style.display = 'none';
        shsSelect.required = false;
        jhsSelect.required = false;

        if (deptSelect.value === 'Senior High School') {
            shsGroup.style.display = 'block';
            shsSelect.required = true;
        } else if (deptSelect.value === 'Junior High School') {
            jhsGroup.style.display = 'block';
            jhsSelect.required = true;
        }
        updateFormFields();
    };

    // Add listener to JHS Dept select to update subjects when it changes
    // jhsSelect.addEventListener('change', updateFormFields); // Not strictly needed for subjects anymore as it depends on level

    // Render Table
    function renderTable() {
        tableBody.innerHTML = teachers.map(t => `
            <tr>
                <td>${t.empNo}</td>
                <td>${t.name}</td>
                <td>${t.dept}</td>
                <td>${t.advisory || '<span style="color:#999;font-style:italic;">None</span>'}</td>
                <td>
                    <button onclick="editTeacher(${t.id})" style="background:none;border:none;color:#800000;cursor:pointer;margin-right:10px;"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteTeacher(${t.id})" style="background:none;border:none;color:#f44336;cursor:pointer;"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
        `).join('');
    }

    function loadTeachers() {
        fetch('../../server/api/teachers.php?action=get_all')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    teachers = data.teachers;
                    renderTable();
                }
            })
            .catch(error => console.error('Error loading teachers:', error));
    }

    loadTeachers();

    // Open Modal
    addBtn.addEventListener('click', () => {
        document.getElementById('modal-title').textContent = 'Add Teacher Profile';
        form.reset();
        document.getElementById('teacher-id').value = '';
        // Reset dynamic fields
        shsGroup.style.display = 'none';
        jhsGroup.style.display = 'none';
        updateFormFields(); // Reset subjects/sections
        
        modal.style.display = 'flex';
    });

    // Close Modal
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });

    window.onclick = (event) => {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    // Handle Form Submit
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const id = document.getElementById('teacher-id').value;
        const empNo = document.getElementById('employee-no').value;
        const name = document.getElementById('full-name').value;
        
        // Determine correct department value based on selection
        let dept = document.getElementById('department').value;
        if (dept === 'Senior High School') {
            dept = document.getElementById('shs-strand').value;
        } else if (dept === 'Junior High School') {
            dept = document.getElementById('jhs-dept').value;
        }
        
        // Gather Subjects
        let subjects = '';
        if (deptSelect.value === 'Senior High School') {
            subjects = document.getElementById('subjects').value;
        } else {
            // JHS Grade Levels
            subjects = Array.from(document.querySelectorAll('input[name="grade_level_checkbox"]:checked'))
                .map(cb => 'Grade ' + cb.value)
                .join(', ');
        }

        // Gather Sections
        const sections = Array.from(document.querySelectorAll('input[name="section_checkbox"]:checked'))
            .map(cb => cb.value)
            .join(', ');
        
        // const sections = document.getElementById('sections').value; // Now using the gathered sections
        const advisory = document.getElementById('advisory-section').value;

        const payload = {
            id: id,
            empNo: empNo,
            name: name,
            dept: dept,
            subjects: subjects,
            sections: sections,
            advisory: advisory
        };

        const action = id ? 'update' : 'add';

        fetch(`../../server/api/teachers.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(id ? 'Teacher updated!' : 'Teacher added!');
                modal.style.display = 'none';
                loadTeachers();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the teacher.');
        });
    });

    // Expose functions to global scope for onclick handlers
    window.editTeacher = function(id) {
        const teacher = teachers.find(t => t.id == id);
        if (teacher) {
            document.getElementById('modal-title').textContent = 'Edit Teacher Profile';
            document.getElementById('teacher-id').value = teacher.id;
            document.getElementById('employee-no').value = teacher.empNo;
            document.getElementById('full-name').value = teacher.name;
            
            // Handle Department Logic for Edit
            const shsStrands = ['STEM', 'ABM', 'HUMSS', 'GAS', 'TVL-ICT', 'TVL-HE', 'TVL-IA', 'TVL-AFA', 'SPORTS', 'ARTS AND DESIGN'];
            const jhsDepts = ['English Department', 'Mathematics Department', 'Science Department', 'Social Studies Department', 'Filipino / Language Department', 'MAPEH Department', 'TLE / TVL Department'];
            
            shsGroup.style.display = 'none';
            jhsGroup.style.display = 'none';
            
            if (shsStrands.includes(teacher.dept)) {
                deptSelect.value = 'Senior High School';
                shsGroup.style.display = 'block';
                shsSelect.value = teacher.dept;
            } else if (jhsDepts.includes(teacher.dept)) {
                deptSelect.value = 'Junior High School';
                jhsGroup.style.display = 'block';
                jhsSelect.value = teacher.dept;
            } else {
                deptSelect.value = teacher.dept; // Fallback
            }
            
            // Update form fields to generate checkboxes/selects
            updateFormFields();

            // Restore Subjects/Grades
            if (teacher.subjects) {
                if (deptSelect.value === 'Senior High School') {
                    const subjSelect = document.getElementById('subjects');
                    if(subjSelect) subjSelect.value = teacher.subjects;
                } else {
                    const grades = teacher.subjects.split(',').map(s => s.trim().replace('Grade ', ''));
                    const checkboxes = document.querySelectorAll('input[name="grade_level_checkbox"]');
                    checkboxes.forEach(cb => {
                        if (grades.includes(cb.value)) cb.checked = true;
                    });
                }
            }

            // Restore Sections
            if (teacher.sections) {
                const secList = teacher.sections.split(',').map(s => s.trim());
                document.querySelectorAll('input[name="section_checkbox"]').forEach(cb => {
                    if (secList.includes(cb.value)) cb.checked = true;
                });
            }

            // document.getElementById('sections').value = teacher.sections;
            document.getElementById('advisory-section').value = teacher.advisory_id || '';
            modal.style.display = 'flex';
        }
    };

    window.deleteTeacher = function(id) {
        if(confirm('Are you sure you want to delete this teacher profile?')) {
            fetch(`../../server/api/teachers.php?action=delete`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) loadTeachers();
                else alert('Error deleting teacher');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the teacher.');
            });
        }
    };
});