document.addEventListener("DOMContentLoaded", () => {
  // Section and grade level elements for Step 4
  const sectionSelect = document.getElementById("teacherSection");
  const gradeLevelSelect = document.getElementById("teacherGradeLevel");

  // Disable section dropdown until grade level is selected
  if (sectionSelect && gradeLevelSelect) {
    sectionSelect.disabled = true;
    gradeLevelSelect.addEventListener("change", function () {
      const gradeLevel = gradeLevelSelect.value;
      console.log("Selected grade level:", gradeLevel); // Debug
      sectionSelect.innerHTML = '<option value="">Select section</option>';
      sectionSelect.disabled = !gradeLevel;
      if (!gradeLevel) return;
      // Use absolute path for fetch (works for localhost and Windows)
      const apiUrl =
        window.location.origin +
        "/MNCHS%20Grade%20Portal/server/api/get_sections.php?grade_level=" +
        encodeURIComponent(gradeLevel);
      console.log("Fetching sections from:", apiUrl); // Debug
      fetch(apiUrl)
        .then((res) => res.json())
        .then((data) => {
          console.log("API response for sections:", data); // Debug
          if (data.status === "success" && data.data.sections.length > 0) {
            data.data.sections.forEach((s) => {
              sectionSelect.innerHTML += `<option value="${s.section_name}">${s.section_name}</option>`;
            });
          } else {
            sectionSelect.innerHTML +=
              '<option value="">No sections found</option>';
          }
        })
        .catch((err) => {
          console.error("Error loading sections:", err); // Debug
          sectionSelect.innerHTML +=
            '<option value="">Error loading sections</option>';
        });
    });
  }
  // Modal elements
  const addTeacherModal = document.getElementById("addTeacherModal");
  const addTeacherBtn = document.getElementById("addTeacherBtn");
  const closeButtons = document.querySelectorAll(
    ".close-button, #cancelButton"
  );
  const addTeacherForm = document.getElementById("addTeacherForm");
  const modalTitle = document.getElementById("modalTitle");

  // View Modal elements
  const viewTeacherModal = document.getElementById("viewTeacherModal");
  const viewCloseButtons = document.querySelectorAll('[data-close="view"]');

  // Form step elements
  const steps = document.querySelectorAll(".step");
  const formSteps = document.querySelectorAll(".form-step");
  const nextButton = document.getElementById("nextButton");
  const prevButton = document.getElementById("prevButton");
  const saveButton = document.getElementById("saveButton");
  let currentStep = 1;

  // Table and filters
  const searchInput = document.getElementById("searchInput");
  const gradeFilter = document.getElementById("gradeFilter");
  const statusFilter = document.getElementById("statusFilter");
  const teacherTableBody = document.getElementById("teacherTableBody");

  // --- Modal Handling ---
  const openModal = (modal) => modal.classList.add("show");
  const closeModal = (modal) => modal.classList.remove("show");

  addTeacherBtn.addEventListener("click", () => {
    resetForm();
    modalTitle.textContent = "Add New Teacher";
    saveButton.textContent = "Save Teacher";
    openModal(addTeacherModal);
  });

  closeButtons.forEach((btn) => {
    btn.addEventListener("click", () => closeModal(addTeacherModal));
  });

  viewCloseButtons.forEach((btn) => {
    btn.addEventListener("click", () => closeModal(viewTeacherModal));
  });

  window.addEventListener("click", (e) => {
    if (e.target === addTeacherModal) closeModal(addTeacherModal);
    if (e.target === viewTeacherModal) closeModal(viewTeacherModal);
  });

  // --- Multi-step Form Logic ---
  const updateStepIndicator = () => {
    steps.forEach((step) => {
      const stepNum = parseInt(step.dataset.step, 10);
      if (stepNum === currentStep) {
        step.classList.add("active");
      } else {
        step.classList.remove("active");
      }
    });

    formSteps.forEach((formStep) => {
      formStep.classList.toggle(
        "active",
        parseInt(formStep.dataset.step, 10) === currentStep
      );
    });

    prevButton.style.display = currentStep > 1 ? "inline-block" : "none";
    // Step count: 4 steps, so nextButton on steps 1-3, saveButton only on 4
    nextButton.style.display = currentStep < 4 ? "inline-block" : "none";
    saveButton.style.display = currentStep === 4 ? "inline-block" : "none";
  };

  const validateStep = (step) => {
    const inputs = formSteps[step - 1].querySelectorAll(
      "input[required], select[required]"
    );
    for (const input of inputs) {
      if (!input.reportValidity()) {
        return false;
      }
    }
    return true;
  };

  nextButton.addEventListener("click", () => {
    if (validateStep(currentStep) && currentStep < 4) {
      currentStep++;
      updateStepIndicator();
    }
  });

  prevButton.addEventListener("click", () => {
    if (currentStep > 1) {
      currentStep--;
      updateStepIndicator();
    }
  });

  // --- Dynamic Form Fields ---
  const departmentSelect = document.getElementById("teacherDepartment");
  const specializationSelect = document.getElementById("teacherSpecialization");

  departmentSelect.addEventListener("change", () => {
    const selectedDepartment = departmentSelect.value;
    specializationSelect.disabled = !selectedDepartment;
    specializationSelect.value = "";

    Array.from(specializationSelect.options).forEach((option) => {
      if (option.value === "") return;
      option.style.display =
        option.dataset.department === selectedDepartment ? "block" : "none";
    });
  });

  const statusSelect = document.getElementById("teacherStatus");
  const adviserClassContainer = document.getElementById(
    "adviserClassContainer"
  );

  statusSelect.addEventListener("change", () => {
    adviserClassContainer.style.display =
      statusSelect.value === "adviser" ? "block" : "none";
  });

  // --- Form Submission ---
  addTeacherForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!validateStep(currentStep)) return;

    // Require grade level and section
    const gradeLevel = document.getElementById("teacherGradeLevel").value;
    const section = document.getElementById("teacherSection").value;
    if (!gradeLevel || !section) {
      alert("Please select both Grade Level and Section.");
      return;
    }

    saveButton.disabled = true;
    saveButton.textContent = "Saving...";

    const formData = new FormData(addTeacherForm);
    const data = Object.fromEntries(formData.entries());

    try {
      const response = await fetch(
        "http://localhost/MNCHS%20Grade%20Portal/server/api/teachers.php?action=add_teacher",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(data),
          credentials: "include", // Send cookies for session authentication
        }
      );

      const result = await response.json();

      if (response.ok && result.status === "success") {
        // The API should return the newly created teacher object in result.data.teacher
        if (result.data && result.data.teacher) {
          addTeacherToTable(result.data.teacher);
        }
        alert(
          `Success: ${result.data.message}\nUsername: ${result.data.username}\nTemporary Password: ${result.data.temp_password}`
        );
        closeModal(addTeacherModal);
      } else {
        throw new Error(result.message || "An unknown error occurred.");
      }
    } catch (error) {
      alert(`Error: ${error.message}`);
    } finally {
      saveButton.disabled = false;
      saveButton.textContent = "Save Teacher";
    }
  });

  const resetForm = () => {
    addTeacherForm.reset();
    currentStep = 1;
    updateStepIndicator();
    specializationSelect.disabled = true;
    adviserClassContainer.style.display = "none";
    // Reset validation states if any custom validation UI is used
  };

  /**
   * Adds a new teacher to the table display.
   * A teacher is shown with a row for each grade level (7-12).
   * @param {object} teacherData - The data for the new teacher from the server.
   */
  function addTeacherToTable(teacherData) {
    const tableBody = document.getElementById("teacherTableBody");
    const noTeachersRow = tableBody.querySelector('td[colspan="6"]');

    // If the "No teachers found" message is present, remove it.
    if (noTeachersRow) {
      noTeachersRow.parentElement.remove();
    }

    // A teacher is displayed for all grade levels by default.
    const gradeLevels = [7, 8, 9, 10, 11, 12];

    gradeLevels.forEach((gradeLevel) => {
      const isJHS = gradeLevel <= 10;
      const schoolLevel = isJHS ? "Junior High School" : "Senior High School";
      const gradeLevelLabel = `Grade ${gradeLevel}`;
      const gradeBadgeClass = isJHS ? "jhs" : "shs";
      const gradeBadgeLabel = isJHS ? "JHS" : "SHS";

      // Determine role display
      let roleHtml = "";
      let statusLabel = "Subject Teacher";
      if (teacherData.is_adviser == 1) {
        statusLabel = `Adviser (${
          teacherData.adviser_class_name || "Unassigned"
        }) & Subject Teacher`;
        roleHtml = `
            <span class="status-badge adviser" title="${statusLabel}">
                <i class="fas fa-user-tie"></i> Adviser
            </span>`;
      } else {
        roleHtml = `
            <span class="status-badge subject-teacher" title="Subject Teacher">
                <i class="fas fa-book"></i> Teacher
            </span>`;
      }

      const newRow = document.createElement("tr");
      newRow.dataset.teacherId = teacherData.id;
      newRow.dataset.teacherEmployeeId = teacherData.teacher_id || "";
      newRow.dataset.teacherName = `${teacherData.first_name} ${teacherData.last_name}`;
      newRow.dataset.teacherFirstName = teacherData.first_name;
      newRow.dataset.teacherLastName = teacherData.last_name;
      newRow.dataset.teacherUsername = teacherData.username;
      newRow.dataset.teacherEmail = teacherData.email;
      newRow.dataset.teacherDepartment = teacherData.department;
      newRow.dataset.teacherSpecialization = teacherData.specialization;
      newRow.dataset.teacherGradeLevel = gradeLevel;
      newRow.dataset.teacherGradeLevelReadable = gradeLevelLabel;
      newRow.dataset.teacherSections = ""; // New teachers have no sections initially
      newRow.dataset.teacherSectionsReadable = "Not Assigned";
      newRow.dataset.teacherSchoolLevel = schoolLevel;
      newRow.dataset.teacherHireDate = teacherData.hire_date;
      newRow.dataset.teacherStatus = statusLabel;
      newRow.dataset.teacherAdviserClass = teacherData.adviser_class_name || "";
      newRow.dataset.teacherIsAdviser = teacherData.is_adviser;

      newRow.innerHTML = `
            <td>${teacherData.teacher_id || "N/A"}</td>
            <td>
                <div class="teacher-info">
                    <div class="teacher-name">${teacherData.first_name} ${
        teacherData.last_name
      }</div>
                    <div class="teacher-department">${
                      teacherData.department || ""
                    }</div>
                </div>
            </td>
            <td>
                <span class="grade-badge ${gradeBadgeClass}">
                    ${gradeBadgeLabel} ${gradeLevel}
                </span>
            </td>
            <td>
                <div class="sections-container">
                    <span class="section-badge">—</span>
                </div>
            </td>
            <td>${roleHtml}</td>
            <td class="action-links">
                <a href="#" data-action="view" title="View"><i class="fas fa-eye"></i></a>
                <a href="#" data-action="edit" title="Edit"><i class="fas fa-edit"></i></a>
            </td>
        `;

      tableBody.prepend(newRow); // Add the new row to the top of the table
    });
  }

  // --- Table Filtering ---
  const filterTable = () => {
    const searchText = searchInput.value.toLowerCase();
    const gradeValue = gradeFilter.value;
    const statusValue = statusFilter.value;

    teacherTableBody.querySelectorAll("tr").forEach((row) => {
      const teacherName = row.dataset.teacherName.toLowerCase();
      const employeeId = row.dataset.teacherEmployeeId.toLowerCase();
      const gradeLevel = row.dataset.teacherGradeLevel;
      const status = row.dataset.teacherStatus.toLowerCase();

      const nameMatch =
        teacherName.includes(searchText) || employeeId.includes(searchText);
      const gradeMatch = !gradeValue || gradeLevel === gradeValue;
      const statusMatch = !statusValue || status.includes(statusValue);

      row.style.display = nameMatch && gradeMatch && statusMatch ? "" : "none";
    });
  };

  [searchInput, gradeFilter, statusFilter].forEach((el) =>
    el.addEventListener("input", filterTable)
  );

  // --- Table Actions (View/Edit) ---
  teacherTableBody.addEventListener("click", (e) => {
    const target = e.target.closest("a[data-action]");
    if (!target) return;

    const action = target.dataset.action;
    const row = target.closest("tr");

    if (action === "view") {
      populateViewModal(row.dataset);
      openModal(viewTeacherModal);
    } else if (action === "edit") {
      populateEditForm(row.dataset);
      modalTitle.textContent = "Edit Teacher";
      saveButton.textContent = "Update Teacher";
      openModal(addTeacherModal);
    }
    /**
     * Populates the add/edit teacher form with existing data and disables grade level selection.
     * @param {object} data - The dataset from the teacher row.
     */
    function populateEditForm(data) {
      resetForm();
      // Step 1: Personal Info
      document.getElementById("teacherEmployeeID").value =
        data.teacherEmployeeId || "";
      document.getElementById("teacherFirstName").value =
        data.teacherFirstName || "";
      document.getElementById("teacherLastName").value =
        data.teacherLastName || "";
      // Step 2: Contact Info
      document.getElementById("teacherEmail").value = data.teacherEmail || "";
      // Step 3: Assignment
      document.getElementById("teacherDepartment").value =
        data.teacherDepartment || "";
      document.getElementById("teacherSpecialization").value =
        data.teacherSpecialization || "";
      // Step 4: Grade Level (fixed)
      const gradeLevelSelect = document.getElementById("teacherGradeLevel");
      gradeLevelSelect.value = data.teacherGradeLevel || "";
      gradeLevelSelect.disabled = true;
      // Optionally, also disable section selection if needed
      // document.getElementById("teacherSection").disabled = true;
      // Move to step 4 directly for editing grade/section if desired
      currentStep = 4;
      updateStepIndicator();
    }
  });

  const populateViewModal = (data) => {
    document.getElementById("viewTeacherEmployeeId").textContent =
      data.teacherEmployeeId || "N/A";
    document.getElementById("viewTeacherName").textContent =
      data.teacherName || "N/A";
    document.getElementById("viewTeacherEmail").textContent =
      data.teacherEmail || "N/A";
    document.getElementById("viewTeacherUsername").textContent =
      data.teacherUsername || "N/A";
    document.getElementById("viewTeacherSchoolLevel").textContent =
      data.teacherSchoolLevel || "N/A";
    document.getElementById("viewTeacherDepartment").textContent =
      data.teacherDepartment || "N/A";
    document.getElementById("viewTeacherSpecialization").textContent =
      data.teacherSpecialization || "N/A";
    document.getElementById("viewTeacherGradeLevel").textContent =
      data.teacherGradeLevelReadable || "N/A";
    document.getElementById("viewTeacherStatus").textContent =
      data.teacherStatus || "N/A";
    document.getElementById("viewTeacherAdviserClass").textContent =
      data.teacherAdviserClass || "N/A";
    document.getElementById("viewTeacherHireDate").textContent =
      data.teacherHireDate
        ? new Date(data.teacherHireDate).toLocaleDateString()
        : "N/A";
  };

  // --- Logout Modal ---
  const logoutLink = document.getElementById("logout-link");
  const logoutModalContainer = document.getElementById(
    "logout-modal-container"
  );
  console.log("[DEBUG] logoutLink:", logoutLink);
  console.log("[DEBUG] logoutModalContainer:", logoutModalContainer);

  if (logoutLink && logoutModalContainer) {
    fetch("../../components/logout_modal.html")
      .then((response) => {
        console.log("[DEBUG] logout_modal.html fetch response:", response);
        return response.text();
      })
      .then((html) => {
        logoutModalContainer.innerHTML = html;
        const logoutModal = document.getElementById("logout-modal");
        const cancelLogoutBtn = document.getElementById("cancel-logout");
        console.log("[DEBUG] logoutModal:", logoutModal);
        console.log("[DEBUG] cancelLogoutBtn:", cancelLogoutBtn);
        if (logoutModal && cancelLogoutBtn) {
          logoutLink.addEventListener("click", (e) => {
            e.preventDefault();
            console.log("[DEBUG] Logout link clicked, showing modal");
            logoutModal.classList.add("show");
          });
          cancelLogoutBtn.addEventListener("click", () => {
            console.log("[DEBUG] Cancel logout clicked, hiding modal");
            logoutModal.classList.remove("show");
          });
          window.addEventListener("click", (e) => {
            if (e.target === logoutModal) {
              console.log("[DEBUG] Clicked outside modal, hiding modal");
              logoutModal.classList.remove("show");
            }
          });
        } else {
          console.warn(
            "[DEBUG] logoutModal or cancelLogoutBtn not found after injecting HTML"
          );
        }
      })
      .catch((error) =>
        console.error("[DEBUG] Error loading logout modal:", error)
      );
  } else {
    console.warn("[DEBUG] logoutLink or logoutModalContainer not found in DOM");
  }

  // --- Notifications ---
  const notificationManager = new NotificationManager({
    userId: "<?php echo $_SESSION['user_id'] ?? 'admin'; ?>",
    userType: "admin",
    bellSelector: ".notification-bell",
    badgeSelector: ".notification-badge",
    // Add other necessary selectors and URLs
  });

  // Initialize notifications
  // notificationManager.init();
});
