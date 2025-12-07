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
    const downloadBtn = document.getElementById('download-card-btn'); // Ensure your button has this ID
    const originalContent = downloadBtn.innerHTML;
    downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
    downloadBtn.disabled = true;

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'mm', format: 'a4' });

    const student = studentData[currentYear].find(s => s.id === currentStudentId);
    const finalGpa = document.getElementById('card-gpa').innerText;

    // --- PDF Header ---
    doc.setFontSize(16);
    doc.setFont("helvetica", "bold");
    doc.text("Muntinlupa National High School", doc.internal.pageSize.getWidth() / 2, 20, { align: "center" });
    doc.setFontSize(11);
    doc.setFont("helvetica", "normal");
    doc.text("Report on Learning Progress and Achievement", doc.internal.pageSize.getWidth() / 2, 27, { align: "center" });

    // --- Student Information ---
    doc.setFontSize(10);
    doc.text(`Student Name: ${student.name}`, 14, 40);
    doc.text(`Grade Level: ${student.grade}`, 14, 46);
    doc.text(`School Year: ${currentYear}`, doc.internal.pageSize.getWidth() - 14, 40, { align: "right" });

    // --- Grades Table ---
    const gradesHead = [['Learning Area', 'Q1', 'Q2', 'Q3', 'Q4', 'Final', 'Remarks']];
    const gradesBody = [];
    student.subjects.forEach(s => {
        const final = Math.round((s.grades.q1 + s.grades.q2 + s.grades.q3 + s.grades.q4) / 4);
        gradesBody.push([
            s.subject, s.grades.q1, s.grades.q2, s.grades.q3, s.grades.q4,
            { content: final, styles: { fontStyle: 'bold' } },
            final >= 75 ? "Passed" : "Failed"
        ]);
    });
    gradesBody.push([
        { content: 'Final General Average', colSpan: 5, styles: { fontStyle: 'bold', halign: 'right' } },
        { content: finalGpa, styles: { fontStyle: 'bold' } }, ''
    ]);

    doc.autoTable({
        head: gradesHead,
        body: gradesBody,
        startY: 52,
        theme: 'grid',
        headStyles: { fillColor: [128, 0, 0], halign: 'center' },
        columnStyles: {
            0: { cellWidth: 'auto' },
            1: { halign: 'center' }, 2: { halign: 'center' }, 3: { halign: 'center' }, 4: { halign: 'center' },
            5: { halign: 'center' }, 6: { halign: 'center' }
        }
    });

    // --- Values Table ---
    let lastY = doc.lastAutoTable.finalY;
    doc.setFontSize(11);
    doc.setFont("helvetica", "bold");
    doc.text("Learner's Observed Values", 14, lastY + 12);

    const valuesHead = [['Core Value', 'Behavior Statement', 'Q1', 'Q2', 'Q3', 'Q4']];
    const valuesBody = [];
    student.observedValues.forEach(coreValue => {
        coreValue.behaviors.forEach((behavior, index) => {
            if (index === 0) {
                valuesBody.push([
                    { content: coreValue.value, rowSpan: coreValue.behaviors.length, styles: { valign: 'middle', fontStyle: 'bold' } },
                    behavior.statement, behavior.q1, behavior.q2, behavior.q3, behavior.q4
                ]);
            } else {
                valuesBody.push([behavior.statement, behavior.q1, behavior.q2, behavior.q3, behavior.q4]);
            }
        });
    });

    doc.autoTable({
        head: valuesHead,
        body: valuesBody,
        startY: lastY + 15,
        theme: 'grid',
        headStyles: { fillColor: [128, 0, 0], halign: 'center' },
        columnStyles: {
            2: { halign: 'center' }, 3: { halign: 'center' }, 4: { halign: 'center' }, 5: { halign: 'center' }
        }
    });

    // --- Footer ---
    lastY = doc.internal.pageSize.getHeight() - 15;
    doc.setFontSize(8);
    doc.setTextColor(150);
    doc.text("This is a system-generated report. For inquiries, please contact the school registrar.", 14, lastY);

    // --- Save the PDF ---
    const filename = `ReportCard_${student.name.replace(/ /g, '_')}_${currentYear}.pdf`;
    doc.save(filename);

    downloadBtn.innerHTML = originalContent;
    downloadBtn.disabled = false;
}

window.onload = init;