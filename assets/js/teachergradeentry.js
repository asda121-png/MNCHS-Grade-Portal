document.addEventListener('DOMContentLoaded', function() {
    // Pre-select class if passed in URL
    const urlParams = new URLSearchParams(window.location.search);
    const classParam = urlParams.get('class');
    if (classParam) {
        const classSelect = document.getElementById('class-select');
        if (classSelect) {
            classSelect.value = classParam;
        }
    }

    // --- Fetch and Update Grading Periods ---
    const quarterSelect = document.getElementById('quarter-select');
    
    fetch('../../server/api/grading_periods.php?action=get_active_periods')
        .then(response => response.json())
        .then(periods => {
            if (periods && periods.length > 0) {
                return fetch('../../server/api/grading_periods.php?action=get_current_quarter')
                    .then(response => response.json())
                    .then(data => {
                        const currentQuarter = data.current_quarter;
                        
                        // Disable quarters that don't have grading periods
                        const enabledQuarters = periods.map(p => p.quarter);
                        const options = quarterSelect.querySelectorAll('option');
                        
                        options.forEach(option => {
                            const value = parseInt(option.value);
                            if (value > 0) {
                                if (enabledQuarters.includes(value)) {
                                    option.disabled = false;
                                    option.textContent = `${value}${value === 1 ? 'st' : value === 2 ? 'nd' : value === 3 ? 'rd' : 'th'} Quarter`;
                                } else {
                                    option.disabled = true;
                                    option.textContent = `${value}${value === 1 ? 'st' : value === 2 ? 'nd' : value === 3 ? 'rd' : 'th'} Quarter`;
                                }
                            }
                        });

                        // Set current quarter as default if available
                        if (currentQuarter && enabledQuarters.includes(currentQuarter)) {
                            quarterSelect.value = currentQuarter;
                        } else if (enabledQuarters.length > 0) {
                            quarterSelect.value = enabledQuarters[0];
                        }
                    });
            }
        })
        .catch(error => console.error('Error fetching grading periods:', error));

    // --- Load Students Logic ---
    const loadStudentsBtn = document.getElementById('load-students-btn');
    const studentsTbody = document.getElementById('students-tbody');
    const classSelect = document.getElementById('class-select');

    if (loadStudentsBtn && studentsTbody && classSelect) {
        loadStudentsBtn.addEventListener('click', function() {
            // In a real application, you would make an AJAX/fetch call here
            // to get student data from the server based on the selected class.
            // For this example, we'll use mock data.

            console.log(`Loading students for class: ${classSelect.value}`);

            // Clear existing rows
            studentsTbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 2rem;">Loading...</td></tr>';

            // Simulate a network request
            setTimeout(() => {
                // This is where you would process the response from the server.
                // For now, we just replace the content with the sample rows.
                studentsTbody.innerHTML = `
                    <tr>
                        <td>123456789012</td>
                        <td>Aguilar, Juan D.</td>
                        <td><input type="number" name="q1[]" min="60" max="100"></td>
                        <td><input type="number" name="q2[]" min="60" max="100"></td>
                        <td><input type="number" name="q3[]" min="60" max="100"></td>
                        <td><input type="number" name="q4[]" min="60" max="100"></td>
                        <td class="final-grade"></td>
                        <td class="remarks"></td>
                    </tr>
                    <tr>
                        <td>123456789013</td>
                        <td>Bautista, Maria C.</td>
                        <td><input type="number" name="q1[]" min="60" max="100"></td>
                        <td><input type="number" name="q2[]" min="60" max="100"></td>
                        <td><input type="number" name="q3[]" min="60" max="100"></td>
                        <td><input type="number" name="q4[]" min="60" max="100"></td>
                        <td class="final-grade"></td>
                        <td class="remarks"></td>
                    </tr>
                    <tr>
                        <td>123456789014</td>
                        <td>Cruz, Pedro S.</td>
                        <td><input type="number" name="q1[]" min="60" max="100"></td>
                        <td><input type="number" name="q2[]" min="60" max="100"></td>
                        <td><input type="number" name="q3[]" min="60" max="100"></td>
                        <td><input type="number" name="q4[]" min="60" max="100"></td>
                        <td class="final-grade"></td>
                        <td class="remarks"></td>
                    </tr>`; // End of innerHTML

                // After rendering, lock/unlock the correct quarter inputs
                const selectedQuarter = document.getElementById('quarter-select').value;
                const gradeInputs = studentsTbody.querySelectorAll('input[type="number"]');

                gradeInputs.forEach(input => {
                    // Extract quarter number from input name (e.g., "q1[]" -> "1")
                    const inputQuarter = input.name.replace('q', '').replace('[]', '');

                    if (inputQuarter === selectedQuarter) {
                        input.disabled = false;
                    } else {
                        input.disabled = true;
                    }
                });
            }, 500); // 0.5 second delay to simulate loading
        });
    }

    // --- Real-time Final Grade Calculation ---
    function updateFinalGrade(row) {
        const q1 = row.querySelector('input[name="q1[]"]');
        const q2 = row.querySelector('input[name="q2[]"]');
        const q3 = row.querySelector('input[name="q3[]"]');
        const q4 = row.querySelector('input[name="q4[]"]');
        const finalGradeCell = row.querySelector('.final-grade');
        const remarksCell = row.querySelector('.remarks');

        const g1 = parseInt(q1.value, 10);
        const g2 = parseInt(q2.value, 10);
        const g3 = parseInt(q3.value, 10);
        const g4 = parseInt(q4.value, 10);

        // Check if all grades are entered and are valid numbers
        if (!isNaN(g1) && !isNaN(g2) && !isNaN(g3) && !isNaN(g4)) {
            const finalGrade = Math.round((g1 + g2 + g3 + g4) / 4);
            finalGradeCell.textContent = finalGrade;

            if (finalGrade >= 75) {
                remarksCell.textContent = 'Passed';
                remarksCell.className = 'remarks passed';
            } else {
                remarksCell.textContent = 'Failed';
                remarksCell.className = 'remarks failed';
            }
        } else {
            // If not all grades are entered, clear the cells
            finalGradeCell.textContent = '';
            remarksCell.textContent = '';
            remarksCell.className = 'remarks';
        }
    }

    // Event listener for grade inputs
    studentsTbody.addEventListener('input', function(e) {
        if (e.target.tagName === 'INPUT' && e.target.type === 'number') {
            const row = e.target.closest('tr');
            updateFinalGrade(row);
        }
    });

    // --- Grade Saving Logic ---
    const gradeForm = document.querySelector('form'); // There's only one form on this page
    if (gradeForm) {
        gradeForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission

            const saveBtn = gradeForm.querySelector('.btn-save');
            if (saveBtn) {
                // Change button state to show loading
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving Grades...';

                const classSelect = document.getElementById('class-select');
                const quarterSelect = document.getElementById('quarter-select');
                const selectedClass = classSelect.value;
                const selectedQuarter = quarterSelect.value;

                // Simulate saving process and then redirect
                setTimeout(() => {
                    // Redirect to the values entry page, passing the selected class and quarter
                    window.location.href = `teachervaluesentry.html?class=${encodeURIComponent(selectedClass)}&quarter=${encodeURIComponent(selectedQuarter)}`;
                }, 1500); // 1.5 second delay for simulation
            }
        });
    }

    // Initial calculation for pre-filled grades on page load
    document.querySelectorAll('#students-tbody tr').forEach(row => {
        updateFinalGrade(row);
    });
});