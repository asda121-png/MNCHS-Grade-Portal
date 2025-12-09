// --- DATA ---
const studentData = {
    "2025-2026": [
        {
            id: 0,
            name: "Liam Anderson",
            grade: 10,
            subjects: [
                { subject: "Mathematics 10", icon: "fa-calculator", teacher: "Mr. Smith", grades: { q1: 88, q2: 89, q3: 90, q4: 91 } },
                { subject: "Science 10", icon: "fa-flask", teacher: "Dr. Al-Fayed", grades: { q1: 92, q2: 93, q3: 91, q4: 94 } },
                { subject: "English 10", icon: "fa-book", teacher: "Ms. Johnson", grades: { q1: 85, q2: 88, q3: 89, q4: 90 } },
                { subject: "Filipino 10", icon: "fa-flag", teacher: "Bb. Reyes", grades: { q1: 87, q2: 86, q3: 88, q4: 89 } },
                { subject: "Araling Panlipunan 10", icon: "fa-landmark", teacher: "G. Cruz", grades: { q1: 88, q2: 87, q3: 89, q4: 90 } },
                { subject: "Edukasyon sa Pagpapakatao 10", icon: "fa-hands-helping", teacher: "Gng. Santos", grades: { q1: 90, q2: 91, q3: 90, q4: 92 } },
                { subject: "MAPEH 10", icon: "fa-palette", teacher: "Mr. Davis", grades: { q1: 86, q2: 88, q3: 87, q4: 89 } },
                { subject: "TLE 10 (Computer)", icon: "fa-laptop-code", teacher: "Ms. Garcia", grades: { q1: 91, q2: 90, q3: 92, q4: 93 } },
            ],
            observedValues: [
                { 
                    value: "1. Maka-Diyos", 
                    behaviors: [
                        { statement: "Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.", q1: "AO", q2: "AO", q3: "SO", q4: "AO" },
                        { statement: "Shows adherence to ethical acts.", q1: "AO", q2: "SO", q3: "AO", q4: "AO" }
                    ] 
                },
                { value: "2. Makatao", behaviors: [ { statement: "Is sensitive to individual, social, and cultural differences.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates contributions toward solidarity.", q1: "SO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "3. Makakalikasan", behaviors: [ { statement: "Cares for the environment and utilizes resources wisely, judiciously, and economically.", q1: "SO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "4. Makabansa", behaviors: [ { statement: "Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates appropriate behavior in carrying out activities in the school, community, and country.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] }
            ]
        },
        {
            id: 1,
            name: "Sophia Anderson",
            grade: 8,
            subjects: [
                { subject: "Mathematics 8", icon: "fa-calculator", teacher: "Mr. Jones", grades: { q1: 95, q2: 96, q3: 94, q4: 97 } },
                { subject: "Science 8", icon: "fa-atom", teacher: "Mrs. White", grades: { q1: 94, q2: 95, q3: 96, q4: 95 } },
                { subject: "English 8", icon: "fa-pen-nib", teacher: "Ms. P.", grades: { q1: 93, q2: 92, q3: 94, q4: 95 } },
                { subject: "Filipino 8", icon: "fa-flag", teacher: "Bb. Reyes", grades: { q1: 92, q2: 93, q3: 91, q4: 94 } },
                { subject: "Araling Panlipunan 8", icon: "fa-landmark", teacher: "G. Cruz", grades: { q1: 94, q2: 95, q3: 93, q4: 96 } },
                { subject: "Edukasyon sa Pagpapakatao 8", icon: "fa-hands-helping", teacher: "Gng. Santos", grades: { q1: 96, q2: 95, q3: 97, q4: 96 } },
                { subject: "MAPEH 8", icon: "fa-palette", teacher: "Mr. Davis", grades: { q1: 93, q2: 94, q3: 92, q4: 95 } },
                { subject: "TLE 8 (Home Economics)", icon: "fa-utensils", teacher: "Ms. Garcia", grades: { q1: 95, q2: 94, q3: 96, q4: 97 } },
            ],
            observedValues: [
                { value: "1. Maka-Diyos", behaviors: [ { statement: "Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Shows adherence to ethical acts.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "2. Makatao", behaviors: [ { statement: "Is sensitive to individual, social, and cultural differences.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates contributions toward solidarity.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "3. Makakalikasan", behaviors: [ { statement: "Cares for the environment and utilizes resources wisely, judiciously, and economically.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "4. Makabansa", behaviors: [ { statement: "Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates appropriate behavior in carrying out activities in the school, community, and country.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] }
            ]
        }
    ],
    "2024-2025": [
        {
            id: 0,
            name: "Liam Anderson",
            grade: 9,
            subjects: [
                { subject: "Mathematics 9", icon: "fa-calculator", teacher: "Mr. Smith", grades: { q1: 85, q2: 86, q3: 87, q4: 88 } },
                { subject: "Science 9", icon: "fa-flask", teacher: "Dr. Al-Fayed", grades: { q1: 90, q2: 91, q3: 89, q4: 92 } },
                { subject: "English 9", icon: "fa-book", teacher: "Ms. Johnson", grades: { q1: 86, q2: 87, q3: 88, q4: 89 } },
                { subject: "Filipino 9", icon: "fa-flag", teacher: "Bb. Reyes", grades: { q1: 88, q2: 89, q3: 87, q4: 90 } },
                { subject: "Araling Panlipunan 9", icon: "fa-landmark", teacher: "G. Cruz", grades: { q1: 89, q2: 88, q3: 90, q4: 91 } },
                { subject: "Edukasyon sa Pagpapakatao 9", icon: "fa-hands-helping", teacher: "Gng. Santos", grades: { q1: 91, q2: 90, q3: 92, q4: 91 } },
                { subject: "MAPEH 9", icon: "fa-palette", teacher: "Mr. Davis", grades: { q1: 87, q2: 89, q3: 88, q4: 90 } },
                { subject: "TLE 9 (Electronics)", icon: "fa-microchip", teacher: "Ms. Garcia", grades: { q1: 92, q2: 91, q3: 93, q4: 94 } },
            ],
            observedValues: [
                { value: "1. Maka-Diyos", behaviors: [ { statement: "Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.", q1: "SO", q2: "AO", q3: "SO", q4: "AO" }, { statement: "Shows adherence to ethical acts.", q1: "AO", q2: "SO", q3: "AO", q4: "AO" } ] },
                { value: "2. Makatao", behaviors: [ { statement: "Is sensitive to individual, social, and cultural differences.", q1: "SO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates contributions toward solidarity.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "3. Makakalikasan", behaviors: [ { statement: "Cares for the environment and utilizes resources wisely, judiciously, and economically.", q1: "SO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "4. Makabansa", behaviors: [ { statement: "Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates appropriate behavior in carrying out activities in the school, community, and country.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] }
            ]
        },
        {
            id: 1,
            name: "Sophia Anderson",
            grade: 7,
            subjects: [
                { subject: "Mathematics 7", icon: "fa-calculator", teacher: "Mr. Jones", grades: { q1: 92, q2: 93, q3: 91, q4: 94 } },
                { subject: "Science 7", icon: "fa-atom", teacher: "Mrs. White", grades: { q1: 91, q2: 92, q3: 93, q4: 92 } },
                { subject: "English 7", icon: "fa-pen-nib", teacher: "Ms. P.", grades: { q1: 90, q2: 91, q3: 92, q4: 93 } },
                { subject: "Filipino 7", icon: "fa-flag", teacher: "Bb. Reyes", grades: { q1: 93, q2: 92, q3: 94, q4: 91 } },
                { subject: "Araling Panlipunan 7", icon: "fa-landmark", teacher: "G. Cruz", grades: { q1: 92, q2: 94, q3: 93, q4: 95 } },
                { subject: "Edukasyon sa Pagpapakatao 7", icon: "fa-hands-helping", teacher: "Gng. Santos", grades: { q1: 95, q2: 94, q3: 96, q4: 95 } },
                { subject: "MAPEH 7", icon: "fa-palette", teacher: "Mr. Davis", grades: { q1: 91, q2: 93, q3: 92, q4: 94 } },
                { subject: "TLE 7 (Cookery)", icon: "fa-utensils", teacher: "Ms. Garcia", grades: { q1: 94, q2: 93, q3: 95, q4: 96 } },
            ],
            observedValues: [
                { value: "1. Maka-Diyos", behaviors: [ { statement: "Expresses one's spiritual beliefs while respecting the spiritual beliefs of others.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Shows adherence to ethical acts.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "2. Makatao", behaviors: [ { statement: "Is sensitive to individual, social, and cultural differences.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates contributions toward solidarity.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "3. Makakalikasan", behaviors: [ { statement: "Cares for the environment and utilizes resources wisely, judiciously, and economically.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] },
                { value: "4. Makabansa", behaviors: [ { statement: "Demonstrates pride in being a Filipino; exercises the rights and responsibilities of a Filipino citizen.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" }, { statement: "Demonstrates appropriate behavior in carrying out activities in the school, community, and country.", q1: "AO", q2: "AO", q3: "AO", q4: "AO" } ] }
            ]
        }
    ]
};

let currentYear = "2025-2026";
let currentStudentId = 0;
let currentQuarter = 'q1';

// --- FUNCTIONS ---

function init() {
    renderDashboard();
    setupLogoutModal();
}

function changeStudent() {
    const select = document.getElementById('studentSelect');
    currentStudentId = parseInt(select.value);
    renderDashboard();
}

function changeYear() {
    const select = document.getElementById('yearSelect');
    currentYear = select.value;
    renderDashboard();
}

function renderDashboard() {
    const student = studentData[currentYear].find(s => s.id === currentStudentId);
    
    // Update SY Label
    document.getElementById('sy-label').innerText = currentYear;

    // Update GPA Card
    let totalFinal = 0;
    student.subjects.forEach(s => {
        const final = Math.round((s.grades.q1 + s.grades.q2 + s.grades.q3 + s.grades.q4) / 4);
        totalFinal += final;
    });
    const gpa = student.subjects.length > 0 ? (totalFinal / student.subjects.length).toFixed(1) : '--';
    document.getElementById('card-gpa').innerText = gpa;

    // Render Grades Table
    const tbody = document.getElementById('grades-table-body');
    tbody.innerHTML = '';
    
    student.subjects.forEach(s => {
        const final = Math.round((s.grades.q1 + s.grades.q2 + s.grades.q3 + s.grades.q4) / 4);
        const remarks = final >= 75 ? "Passed" : "Failed";

        // Color Logic for Remarks
        let remarkClass = remarks === "Passed" ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200';

        // Helper to color individual grades slightly if they are low/high
        const getColor = (grade) => {
            if(grade < 75) return 'text-red-600 font-bold';
            return 'text-gray-700';
        }

        const row = `
            <tr class="hover-pop hover:bg-maroon-50 cursor-pointer group border-b border-gray-50 last:border-0" onclick="document.getElementById('detail-modal').classList.remove('hidden')">
                <td class="p-5 pl-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-maroon-200 group-hover:text-maroon-900 transition duration-300">
                            <i class="fas ${s.icon}"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-sm group-hover:text-maroon-900 transition">${s.subject}</p>
                            <p class="text-xs text-gray-400 group-hover:text-maroon-400">Core Subject</p>
                        </div>
                    </div>
                </td>
                <td class="p-5 text-gray-600 font-medium group-hover:text-gray-800 text-sm">${s.teacher}</td>
                
                <td class="p-4 text-center ${getColor(s.grades.q1)} group-hover:font-bold transition">${s.grades.q1}</td>
                <td class="p-4 text-center ${getColor(s.grades.q2)} group-hover:font-bold transition">${s.grades.q2}</td>
                <td class="p-4 text-center ${getColor(s.grades.q3)} group-hover:font-bold transition">${s.grades.q3}</td>
                <td class="p-4 text-center ${getColor(s.grades.q4)} group-hover:font-bold transition">${s.grades.q4}</td>

                <td class="p-5 text-center bg-gray-50/50 border-l border-r border-gray-100 group-hover:bg-maroon-100/30">
                    <span class="font-bold text-lg text-maroon-900">${final}</span>
                </td>

                <td class="p-5 text-center">
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide ${remarkClass} group-hover:shadow-sm">${remarks}</span>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });

    // Render Observed Values Table
    const valuesTbody = document.getElementById('values-table-body');
    valuesTbody.innerHTML = '';
    if (student.observedValues) {
        student.observedValues.forEach(coreValue => {
            coreValue.behaviors.forEach((behavior, index) => {
                const row = `
                    <tr class="hover:bg-gray-50">
                        ${index === 0 ? `<td rowspan="${coreValue.behaviors.length}" class="p-5 pl-8 align-top font-bold text-maroon-900 bg-red-50/50 border-r border-gray-100">${coreValue.value}</td>` : ''}
                        <td class="p-5 text-gray-600">${behavior.statement}</td>
                        <td class="p-4 text-center font-semibold text-gray-700">${behavior.q1 || ''}</td>
                        <td class="p-4 text-center font-semibold text-gray-700">${behavior.q2 || ''}</td>
                        <td class="p-4 text-center font-semibold text-gray-700">${behavior.q3 || ''}</td>
                        <td class="p-4 text-center font-semibold text-gray-700">${behavior.q4 || ''}</td>
                    </tr>
                `;
                valuesTbody.innerHTML += row;
            });
        });
    }
}

async function setupLogoutModal() {
    try {
        const response = await fetch('../../components/logout_modal.html');
        const modalHTML = await response.text();
        document.getElementById('logout-modal-container').innerHTML = modalHTML;

        document.getElementById('cancel-logout').addEventListener('click', closeLogoutModal);
        // Optional: Also close modal if clicking on the overlay
        document.querySelector('#logout-modal.modal-overlay').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeLogoutModal();
            }
        });
    } catch (error) {
        console.error('Error fetching or setting up logout modal:', error);
    }
}

function showLogoutModal() {
    document.querySelector('#logout-modal.modal-overlay')?.classList.add('show');
}

function closeLogoutModal() {
    document.querySelector('#logout-modal.modal-overlay')?.classList.remove('show');
}

function downloadReportCard() {
    const downloadBtn = document.getElementById('download-card-btn');
    const originalContent = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
    downloadBtn.disabled = true;

    setTimeout(() => {
        try {
            if (!window.jspdf || !window.jspdf.jsPDF) {
                throw new Error('jsPDF library not loaded');
            }

            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });
            
            const pageWidth = doc.internal.pageSize.getWidth();
            const pageHeight = doc.internal.pageSize.getHeight();
            const student = studentData[currentYear].find(s => s.id === currentStudentId);
            const finalGpa = document.getElementById('card-gpa').innerText;

            let yPosition = 15;

            // --- Header with School Name ---
            doc.setFontSize(16);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(128, 0, 0);
            doc.text("Mati National Comprehensive High School", pageWidth / 2, yPosition, { align: "center" });
            
            yPosition += 8;
            doc.setFontSize(12);
            doc.setFont("helvetica", "normal");
            doc.setTextColor(80, 80, 80);
            doc.text("Report on Learning Progress and Achievement", pageWidth / 2, yPosition, { align: "center" });
            
            // --- Divider Line ---
            yPosition += 8;
            doc.setDrawColor(128, 0, 0);
            doc.setLineWidth(0.5);
            doc.line(14, yPosition, pageWidth - 14, yPosition);
            
            // --- Student Information Section ---
            yPosition += 10;
            doc.setFontSize(10);
            doc.setFont("helvetica", "bold");
            doc.setTextColor(128, 0, 0);
            doc.text("STUDENT INFORMATION", 14, yPosition);
            
            yPosition += 8;
            doc.setFont("helvetica", "normal");
            doc.setTextColor(0, 0, 0);
            doc.setFontSize(9);
            
            const infoData = [
                { label: "Student Name:", value: student.name },
                { label: "Grade Level:", value: `Grade ${student.grade}` },
                { label: "School Year:", value: currentYear }
            ];
            
            infoData.forEach(info => {
                doc.setFont("helvetica", "bold");
                doc.text(info.label, 14, yPosition);
                doc.setFont("helvetica", "normal");
                doc.text(info.value, 50, yPosition);
                yPosition += 6;
            });

            // --- Grades Section ---
            yPosition += 8;
            doc.setFont("helvetica", "bold");
            doc.setFontSize(10);
            doc.setTextColor(128, 0, 0);
            doc.text("ACADEMIC GRADES", 14, yPosition);
            
            yPosition += 8;
            
            // Table dimensions
            const colWidths = [35, 18, 12, 12, 12, 12, 12, 15];
            const rowHeight = 6;
            const startX = 14;
            
            // Table header
            doc.setFillColor(128, 0, 0);
            doc.setTextColor(255, 255, 255);
            doc.setFont("helvetica", "bold");
            doc.setFontSize(8);
            
            const headers = ['Learning Area', 'Teacher', 'Q1', 'Q2', 'Q3', 'Q4', 'Final', 'Remarks'];
            let xPos = startX;
            headers.forEach((header, idx) => {
                doc.rect(xPos, yPosition - 4, colWidths[idx], rowHeight, 'F');
                const align = idx > 1 ? 'center' : 'left';
                const textX = align === 'center' ? xPos + colWidths[idx] / 2 : xPos + 1;
                doc.text(header, textX, yPosition, { maxWidth: colWidths[idx] - 2, align: align });
                xPos += colWidths[idx];
            });
            
            yPosition += rowHeight;
            
            // Table body - Grades rows
            doc.setTextColor(0, 0, 0);
            doc.setFont("helvetica", "normal");
            doc.setFontSize(8);
            
            student.subjects.forEach(s => {
                const final = Math.round((s.grades.q1 + s.grades.q2 + s.grades.q3 + s.grades.q4) / 4);
                const rowData = [
                    s.subject.substring(0, 18),
                    s.teacher.substring(0, 10),
                    String(s.grades.q1),
                    String(s.grades.q2),
                    String(s.grades.q3),
                    String(s.grades.q4),
                    String(final),
                    final >= 75 ? "Passed" : "Failed"
                ];
                
                xPos = startX;
                rowData.forEach((cell, idx) => {
                    doc.rect(xPos, yPosition - 4, colWidths[idx], rowHeight);
                    const align = idx > 1 ? 'center' : 'left';
                    const textX = align === 'center' ? xPos + colWidths[idx] / 2 : xPos + 1;
                    doc.text(cell, textX, yPosition, { maxWidth: colWidths[idx] - 2, align: align });
                    xPos += colWidths[idx];
                });
                
                yPosition += rowHeight;
            });
            
            // Final Average Row - Separate row
            doc.setFont("helvetica", "bold");
            doc.setFillColor(200, 200, 200);
            doc.setTextColor(0, 0, 0);
            
            xPos = startX;
            // "FINAL GENERAL AVERAGE" spans columns 0-5
            const spanWidth = colWidths[0] + colWidths[1] + colWidths[2] + colWidths[3] + colWidths[4] + colWidths[5];
            doc.rect(xPos, yPosition - 4, spanWidth, rowHeight, 'F');
            doc.text("FINAL GENERAL AVERAGE", xPos + 2, yPosition, { maxWidth: spanWidth - 4 });
            xPos += spanWidth;
            
            // Final column
            doc.setFillColor(128, 0, 0);
            doc.setTextColor(255, 255, 255);
            doc.rect(xPos, yPosition - 4, colWidths[6], rowHeight, 'F');
            doc.text(finalGpa, xPos + colWidths[6] / 2, yPosition, { align: 'center' });
            xPos += colWidths[6];
            
            // Remarks column empty
            doc.setTextColor(0, 0, 0);
            doc.setFillColor(255, 255, 255);
            doc.rect(xPos, yPosition - 4, colWidths[7], rowHeight);
            xPos += colWidths[7];
            
            yPosition += rowHeight + 8;

            // --- Observed Values Section ---
            if (yPosition > pageHeight - 50) {
                doc.addPage();
                yPosition = 15;
            }

            doc.setFont("helvetica", "bold");
            doc.setFontSize(10);
            doc.setTextColor(128, 0, 0);
            doc.text("LEARNER'S OBSERVED VALUES", 14, yPosition);
            
            yPosition += 8;

            // Values table header
            doc.setFillColor(128, 0, 0);
            doc.setTextColor(255, 255, 255);
            doc.setFont("helvetica", "bold");
            doc.setFontSize(8);
            
            const valuesHeaders = ['Core Value', 'Behavior Statement', 'Q1', 'Q2', 'Q3', 'Q4'];
            const valuesColWidths = [25, 65, 15, 15, 15, 15];
            xPos = startX;
            valuesHeaders.forEach((header, idx) => {
                doc.rect(xPos, yPosition - 4, valuesColWidths[idx], rowHeight, 'F');
                const align = idx > 0 ? 'center' : 'left';
                const textX = align === 'center' ? xPos + valuesColWidths[idx] / 2 : xPos + 1;
                doc.text(header, textX, yPosition, { maxWidth: valuesColWidths[idx] - 2, align: align });
                xPos += valuesColWidths[idx];
            });
            
            yPosition += rowHeight;
            
            // Values table body
            doc.setTextColor(0, 0, 0);
            doc.setFont("helvetica", "normal");
            doc.setFontSize(7);
            
            student.observedValues.forEach(coreValue => {
                coreValue.behaviors.forEach((behavior, behaviorIdx) => {
                    const coreValueDisplay = behaviorIdx === 0 ? coreValue.value : '';
                    
                    const valuesRowData = [
                        coreValueDisplay.substring(0, 23),
                        behavior.statement.substring(0, 55),
                        behavior.q1 || 'NO',
                        behavior.q2 || 'NO',
                        behavior.q3 || 'NO',
                        behavior.q4 || 'NO'
                    ];
                    
                    xPos = startX;
                    valuesRowData.forEach((cell, idx) => {
                        doc.rect(xPos, yPosition - 4, valuesColWidths[idx], rowHeight);
                        const align = (idx > 0) ? 'center' : 'left';
                        const textX = align === 'center' ? xPos + valuesColWidths[idx] / 2 : xPos + 1;
                        doc.text(String(cell), textX, yPosition, { 
                            maxWidth: valuesColWidths[idx] - 2, 
                            align: align,
                            lineHeightFactor: 1
                        });
                        xPos += valuesColWidths[idx];
                    });
                    
                    yPosition += rowHeight;
                    
                    // Check if we need a new page
                    if (yPosition > pageHeight - 15) {
                        doc.addPage();
                        yPosition = 15;
                        
                        // Redraw values table header on new page
                        doc.setFillColor(128, 0, 0);
                        doc.setTextColor(255, 255, 255);
                        doc.setFont("helvetica", "bold");
                        doc.setFontSize(8);
                        
                        xPos = startX;
                        valuesHeaders.forEach((header, idx) => {
                            doc.rect(xPos, yPosition - 4, valuesColWidths[idx], rowHeight, 'F');
                            const align = idx > 0 ? 'center' : 'left';
                            const textX = align === 'center' ? xPos + valuesColWidths[idx] / 2 : xPos + 1;
                            doc.text(header, textX, yPosition, { maxWidth: valuesColWidths[idx] - 2, align: align });
                            xPos += valuesColWidths[idx];
                        });
                        
                        yPosition += rowHeight;
                        doc.setTextColor(0, 0, 0);
                        doc.setFont("helvetica", "normal");
                        doc.setFontSize(7);
                    }
                });
            });

            // --- Footer ---
            const totalPages = doc.internal.pages.length - 1;
            for (let page = 1; page <= totalPages; page++) {
                doc.setPage(page);
                doc.setFontSize(7);
                doc.setTextColor(150);
                doc.text(
                    `Generated on ${new Date().toLocaleDateString()} | Page ${page} of ${totalPages}`,
                    pageWidth / 2,
                    pageHeight - 8,
                    { align: "center" }
                );
            }

            // --- Save the PDF ---
            const filename = `ReportCard_${student.name.replace(/ /g, '_')}_${currentYear}.pdf`;
            doc.save(filename);

            downloadBtn.innerHTML = originalContent;
            downloadBtn.disabled = false;
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF: ' + error.message);
            downloadBtn.innerHTML = originalContent;
            downloadBtn.disabled = false;
        }
    }, 500);
}

window.onload = init;