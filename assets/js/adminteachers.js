document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const gradeFilter = document.getElementById("gradeFilter");
  const statusFilter = document.getElementById("statusFilter");
  const teacherTableBody = document.getElementById("teacherTableBody");
  const addTeacherButton = document.getElementById("addTeacherBtn");
  const addModal = document.getElementById("addTeacherModal");
  const viewModal = document.getElementById("viewTeacherModal");
  const logoutLink = document.getElementById("logout-link");
  const modalContainer = document.getElementById("logout-modal-container");

  const deriveSchoolLevel = (gradeValue) => {
    const gradeNumber = parseInt(gradeValue, 10);
    if (Number.isNaN(gradeNumber)) return "";
    return gradeNumber <= 10 ? "Junior High School" : "Senior High School";
  };

  if (logoutLink && modalContainer) {
    fetch("../../components/logout_modal.html")
      .then((response) => response.text())
      .then((html) => {
        modalContainer.innerHTML = html;
        const logoutModal = document.getElementById("logout-modal");
        const cancelLogout = document.getElementById("cancel-logout");
        if (!logoutModal || !cancelLogout) return;
        logoutLink.addEventListener("click", (e) => {
          e.preventDefault();
          logoutModal.classList.add("show");
        });
        cancelLogout.addEventListener("click", () =>
          logoutModal.classList.remove("show")
        );
        logoutModal.addEventListener("click", (e) => {
          if (e.target === logoutModal) logoutModal.classList.remove("show");
        });
      })
      .catch((error) => console.error("Error loading logout modal:", error));
  }

  if (!searchInput || !teacherTableBody) {
    console.warn("Teacher page elements not found");
    return;
  }

  let openAddModal = null;
  let closeAddModal = null;

  if (addModal) {
    const closeModalButton = addModal.querySelector(".close-button");
    const cancelButton = addModal.querySelector("#cancelButton");
    const addTeacherForm = document.getElementById("addTeacherForm");
    const nextButton = document.getElementById("nextButton");
    const prevButton = document.getElementById("prevButton");
    const saveButton = document.getElementById("saveButton");
    const formSteps = addModal.querySelectorAll(".form-step");
    const stepIndicators = addModal.querySelectorAll(".step-indicator .step");
    const modalTitle = addModal.querySelector("#modalTitle");
    const gradeLevelSelect = document.getElementById("teacherGradeLevel");
    const schoolLevelSelect = document.getElementById("teacherSchoolLevel");
    const teacherStatusSelect = document.getElementById("teacherStatus");
    const teacherAdviserClassSelect = document.getElementById(
      "teacherAdviserClass"
    );

    let currentStep = 1;
    const defaultModalTitle = modalTitle ? modalTitle.textContent : "";
    const defaultSaveText = saveButton ? saveButton.textContent : "";

    // Load adviser classes for dropdown
    const loadAdviserClasses = async () => {
      try {
        const response = await fetch(
          "../../server/api/teachers.php?action=get_classes"
        );
        const result = await response.json();

        if (result.success && teacherAdviserClassSelect) {
          // Keep the first option ("Not an Adviser")
          const currentFirstOption = teacherAdviserClassSelect.options[0];
          teacherAdviserClassSelect.innerHTML = "";
          teacherAdviserClassSelect.appendChild(currentFirstOption);

          // Add all available classes
          result.data.forEach((classItem) => {
            const option = document.createElement("option");
            option.value = classItem.id;
            option.textContent = `${classItem.name} - ${classItem.section}`;
            teacherAdviserClassSelect.appendChild(option);
          });
        }
      } catch (error) {
        console.error("Error loading adviser classes:", error);
      }
    };

    // Load adviser classes on modal open
    loadAdviserClasses();

    // Show/hide adviser class field based on role selection
    if (teacherStatusSelect) {
      teacherStatusSelect.addEventListener("change", (e) => {
        if (teacherAdviserClassSelect) {
          if (e.target.value === "adviser") {
            teacherAdviserClassSelect.parentElement.style.display = "block";
            teacherAdviserClassSelect.required = true;

            // Show note about section limitation
            let noteElement =
              teacherAdviserClassSelect.parentElement.querySelector(
                ".adviser-note"
              );
            if (!noteElement) {
              noteElement = document.createElement("div");
              noteElement.className = "adviser-note";
              noteElement.style.cssText = `
                color: #800000;
                font-size: 0.85rem;
                margin-top: 8px;
                padding: 8px 12px;
                background-color: #fff5f5;
                border-left: 3px solid #800000;
                border-radius: 4px;
              `;
              noteElement.textContent =
                "Note: An adviser can only teach 1 section. Ensure the teacher is assigned to only one section.";
              teacherAdviserClassSelect.parentElement.appendChild(noteElement);
            }
            noteElement.style.display = "block";
          } else {
            teacherAdviserClassSelect.parentElement.style.display = "none";
            teacherAdviserClassSelect.required = false;
            teacherAdviserClassSelect.value = "";

            // Hide note
            const noteElement =
              teacherAdviserClassSelect.parentElement.querySelector(
                ".adviser-note"
              );
            if (noteElement) noteElement.style.display = "none";
          }
        }
      });
    }

    const setFieldValue = (id, value = "") => {
      const field = document.getElementById(id);
      if (field) field.value = value;
    };

    const syncSchoolLevelWithGrade = (gradeValue) => {
      if (!schoolLevelSelect) return;
      const derived = deriveSchoolLevel(gradeValue);
      if (!derived) return;
      schoolLevelSelect.value = derived;
    };

    const updateFormSteps = () => {
      formSteps.forEach((step) => {
        step.classList.toggle(
          "active",
          parseInt(step.dataset.step, 10) === currentStep
        );
      });
      stepIndicators.forEach((step) => {
        step.classList.toggle(
          "active",
          parseInt(step.dataset.step, 10) === currentStep
        );
      });
      if (prevButton)
        prevButton.style.display = currentStep === 1 ? "none" : "inline-flex";
      if (nextButton)
        nextButton.style.display =
          currentStep === formSteps.length ? "none" : "inline-flex";
      if (saveButton)
        saveButton.style.display =
          currentStep === formSteps.length ? "inline-flex" : "none";
    };

    closeAddModal = () => {
      addModal.classList.remove("show");
      if (addTeacherForm) addTeacherForm.reset();
      currentStep = 1;
      updateFormSteps();
      if (modalTitle) modalTitle.textContent = defaultModalTitle;
      if (saveButton) saveButton.textContent = defaultSaveText;
      if (addTeacherForm) {
        delete addTeacherForm.dataset.mode;
        delete addTeacherForm.dataset.teacherId;
      }
    };

    openAddModal = (mode = "add", data = null) => {
      if (addTeacherForm) addTeacherForm.reset();
      if (modalTitle)
        modalTitle.textContent =
          mode === "edit" ? "Edit Teacher" : defaultModalTitle;
      if (saveButton)
        saveButton.textContent =
          mode === "edit" ? "Update Teacher" : defaultSaveText;
      if (addTeacherForm) {
        addTeacherForm.dataset.mode = mode;
        addTeacherForm.dataset.teacherId = data?.id || "";
      }
      if (data) {
        setFieldValue("teacherEmployeeID", data.employeeId || "");
        let firstName = data.firstName || "";
        let middleName = data.middleName || "";
        let lastName = data.lastName || "";
        let suffix = data.suffix || "";

        if (!firstName && !lastName) {
          const nameParts = (data.name || "")
            .trim()
            .split(/\s+/)
            .filter(Boolean);
          firstName = nameParts.shift() || "";
          lastName = nameParts.length > 0 ? nameParts.pop() : "";
          middleName = nameParts.join(" ");
        }

        setFieldValue("teacherFirstName", firstName);
        setFieldValue("teacherMiddleName", middleName);
        setFieldValue("teacherLastName", lastName);
        setFieldValue("teacherSuffix", suffix);
        setFieldValue("teacherEmail", data.email || "");
        setFieldValue("teacherPhone", data.phone || "");
        setFieldValue("teacherDepartment", data.department || "");
        setFieldValue("teacherSpecialization", data.specialization || "");
        const gradeLevelValue = data.gradeLevel || "";
        setFieldValue("teacherGradeLevel", gradeLevelValue);
        const initialSchoolLevel =
          data.schoolLevel || deriveSchoolLevel(gradeLevelValue);
        setFieldValue("teacherSchoolLevel", initialSchoolLevel || "");
        setFieldValue("teacherAdviserClass", data.adviserClass || "");
        setFieldValue("teacherStatus", data.status || "subject-teacher");
        if (!data.schoolLevel && gradeLevelValue) {
          syncSchoolLevelWithGrade(gradeLevelValue);
        }
      }
      addModal.classList.add("show");
      currentStep = 1;
      updateFormSteps();
    };

    if (addTeacherButton) {
      addTeacherButton.addEventListener("click", () => openAddModal("add"));
    }

    if (closeModalButton)
      closeModalButton.addEventListener("click", closeAddModal);
    if (cancelButton) cancelButton.addEventListener("click", closeAddModal);

    addModal.addEventListener("click", (e) => {
      if (e.target === addModal) closeAddModal();
    });

    if (nextButton) {
      nextButton.addEventListener("click", () => {
        if (currentStep < formSteps.length) {
          currentStep++;
          updateFormSteps();
        }
      });
    }

    if (prevButton) {
      prevButton.addEventListener("click", () => {
        if (currentStep > 1) {
          currentStep--;
          updateFormSteps();
        }
      });
    }

    if (gradeLevelSelect) {
      gradeLevelSelect.addEventListener("change", (event) => {
        syncSchoolLevelWithGrade(event.target.value);
      });
    }

    if (addTeacherForm) {
      addTeacherForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const teacherId = addTeacherForm.dataset.teacherId || null;
        const employeeId =
          document.getElementById("teacherEmployeeID")?.value || "";
        const firstName =
          document.getElementById("teacherFirstName")?.value || "";
        const middleName =
          document.getElementById("teacherMiddleName")?.value || "";
        const lastName =
          document.getElementById("teacherLastName")?.value || "";
        const suffix = document.getElementById("teacherSuffix")?.value || "";
        const email = document.getElementById("teacherEmail")?.value || "";
        const phone = document.getElementById("teacherPhone")?.value || "";
        const department =
          document.getElementById("teacherDepartment")?.value || "";
        const specialization =
          document.getElementById("teacherSpecialization")?.value || "";
        const schoolLevel =
          document.getElementById("teacherSchoolLevel")?.value || "";
        const gradeLevel =
          document.getElementById("teacherGradeLevel")?.value || "";
        const role =
          document.getElementById("teacherStatus")?.value || "subject-teacher";
        const adviserClass =
          document.getElementById("teacherAdviserClass")?.value || "";
        const nameParts = [firstName, middleName, lastName, suffix];
        const fullName = nameParts.filter((part) => part).join(" ");
        const actionText =
          addTeacherForm.dataset.mode === "edit" ? "Updating" : "Saving";
        const identifier = addTeacherForm.dataset.teacherId
          ? `ID: ${addTeacherForm.dataset.teacherId}\n`
          : "";
        const roleDisplay =
          role === "adviser" && adviserClass
            ? `Adviser to ${adviserClass}`
            : role;
        const assignmentSummary = [
          department ? `Department: ${department}` : "",
          specialization ? `Specialization: ${specialization}` : "",
          schoolLevel ? `School Level: ${schoolLevel}` : "",
          gradeLevel ? `Grade Level: ${gradeLevel}` : "",
          role ? `Role: ${roleDisplay}` : "",
        ]
          .filter(Boolean)
          .join("\n");
        const summary = [
          `${actionText} teacher:`,
          identifier ? identifier.trimEnd() : "",
          `Employee ID: ${employeeId}`,
          `Name: ${fullName || "N/A"}`,
          assignmentSummary,
        ]
          .filter(Boolean)
          .join("\n");

        // --- REGEX VALIDATION ---
        const nameRegex = /^[A-Za-z .'-]+$/;
        const emailRegex = /^[\w-.]+@([\w-]+\.)+[\w-]{2,}$/;
        const phoneRegex = /^09\d{9}$/;

        if (!nameRegex.test(firstName)) {
          alert(
            "First name must contain only letters, spaces, apostrophes, periods, or hyphens."
          );
          document.getElementById("teacherFirstName").focus();
          return;
        }
        if (!nameRegex.test(lastName)) {
          alert(
            "Last name must contain only letters, spaces, apostrophes, periods, or hyphens."
          );
          document.getElementById("teacherLastName").focus();
          return;
        }
        if (email && !emailRegex.test(email)) {
          alert("Please enter a valid email address.");
          document.getElementById("teacherEmail").focus();
          return;
        }
        if (phone && !phoneRegex.test(phone)) {
          alert(
            "Phone number must be a valid Philippine mobile number (e.g., 09123456789)."
          );
          document.getElementById("teacherPhone").focus();
          return;
        }

        // If in edit mode and teacher has ID, save role information via API
        if (addTeacherForm.dataset.mode === "edit" && teacherId) {
          try {
            const response = await fetch(
              "../../server/api/teachers.php?action=update_teacher",
              {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                  teacher_id: teacherId,
                  is_adviser: role === "adviser" ? 1 : 0,
                  adviser_class_id: role === "adviser" ? adviserClass : null,
                }),
              }
            );
            const result = await response.json();

            if (!result.success) {
              alert(`${actionText} failed: ${result.message}`);
              return;
            }
          } catch (error) {
            alert(`Error updating teacher: ${error.message}`);
            return;
          }
        }

        alert(summary);
        closeAddModal();
        // Reload page to show updated data
        location.reload();
      });
    }
  }

  if (viewModal) {
    const viewFields = {
      employeeId: document.getElementById("viewTeacherEmployeeId"),
      name: document.getElementById("viewTeacherName"),
      email: document.getElementById("viewTeacherEmail"),
      username: document.getElementById("viewTeacherUsername"),
      schoolLevel: document.getElementById("viewTeacherSchoolLevel"),
      department: document.getElementById("viewTeacherDepartment"),
      specialization: document.getElementById("viewTeacherSpecialization"),
      gradeLevel: document.getElementById("viewTeacherGradeLevel"),
      adviserClass: document.getElementById("viewTeacherAdviserClass"),
      hireDate: document.getElementById("viewTeacherHireDate"),
      status: document.getElementById("viewTeacherStatus"),
    };

    const displayText = (value, modifier) => {
      if (value === null || value === undefined) return "N/A";
      const stringValue = String(value).trim();
      if (!stringValue) return "N/A";
      const result = modifier ? modifier(stringValue) : stringValue;
      return result !== "" ? result : "N/A";
    };

    const formatDate = (value) => {
      const normalized = value.split("T")[0].split(" ")[0];
      if (normalized === "0000-00-00") return "N/A";
      const [year, month, day] = normalized
        .split("-")
        .map((part) => parseInt(part, 10));
      if (!year || !month || !day) return value;
      const date = new Date(Date.UTC(year, month - 1, day));
      if (Number.isNaN(date.getTime())) return value;
      return date.toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
    };

    viewModal.addEventListener("click", (e) => {
      if (e.target === viewModal) viewModal.classList.remove("show");
    });

    viewModal.querySelectorAll('[data-close="view"]').forEach((button) => {
      button.addEventListener("click", () =>
        viewModal.classList.remove("show")
      );
    });

    viewModal.showDetails = (data) => {
      if (!data) return;
      if (viewFields.employeeId)
        viewFields.employeeId.textContent = displayText(
          data.employeeId || data.id
        );
      if (viewFields.name) viewFields.name.textContent = displayText(data.name);
      if (viewFields.email)
        viewFields.email.textContent = displayText(data.email);
      if (viewFields.username)
        viewFields.username.textContent = displayText(data.username);
      if (viewFields.schoolLevel)
        viewFields.schoolLevel.textContent = displayText(
          data.schoolLevel || deriveSchoolLevel(data.gradeLevel)
        );
      if (viewFields.department)
        viewFields.department.textContent = displayText(data.department);
      if (viewFields.specialization)
        viewFields.specialization.textContent = displayText(
          data.specialization
        );
      if (viewFields.gradeLevel)
        viewFields.gradeLevel.textContent = displayText(
          data.gradeLevelLabel || data.gradeLevel
        );
      if (viewFields.adviserClass)
        viewFields.adviserClass.textContent = displayText(
          data.adviserClass || "Not an Adviser"
        );
      if (viewFields.hireDate)
        viewFields.hireDate.textContent = displayText(
          data.hireDate,
          formatDate
        );
      if (viewFields.status)
        viewFields.status.textContent = displayText(data.status);
      viewModal.classList.add("show");
    };
  }

  const tableRows = teacherTableBody.getElementsByTagName("tr");

  const applyFilters = () => {
    const searchText = searchInput.value.toLowerCase();
    const gradeValue = gradeFilter ? gradeFilter.value : "";
    const statusValue = statusFilter ? statusFilter.value.toLowerCase() : "";
    for (let i = 0; i < tableRows.length; i++) {
      const row = tableRows[i];
      const employeeId =
        row.dataset.teacherEmployeeId || row.cells[0]?.textContent || "";
      const name = row.dataset.teacherName || row.cells[1]?.textContent || "";
      const gradeLevelLabel =
        row.dataset.teacherGradeLevelReadable ||
        row.dataset.teacherGradeLevel ||
        "";
      const sectionsLabel = row.dataset.teacherSectionsReadable || "";
      const schoolLevelLabel = row.dataset.teacherSchoolLevel || "";
      const searchMatch =
        employeeId.toLowerCase().includes(searchText) ||
        name.toLowerCase().includes(searchText) ||
        gradeLevelLabel.toLowerCase().includes(searchText) ||
        sectionsLabel.toLowerCase().includes(searchText) ||
        schoolLevelLabel.toLowerCase().includes(searchText);

      const rowGrade = row.dataset.teacherGradeLevel || "";
      const gradeMatch = !gradeValue || rowGrade === gradeValue;

      const rowStatus = (row.dataset.teacherStatus || "").toLowerCase();
      const isAdviser = row.dataset.teacherIsAdviser === "1";

      // Status filter matching logic
      let statusMatch = true;
      if (statusValue) {
        if (
          statusValue === "adviser" ||
          statusValue === "adviser & subject teacher"
        ) {
          // Match if teacher is an adviser (all advisers are also subject teachers)
          statusMatch = isAdviser || rowStatus.includes("adviser");
        } else if (statusValue === "subject teacher") {
          // Match only non-adviser teachers
          statusMatch = !isAdviser && !rowStatus.includes("adviser");
        } else {
          statusMatch = rowStatus.includes(statusValue);
        }
      }

      row.style.display =
        searchMatch && gradeMatch && statusMatch ? "" : "none";
    }
  };

  searchInput.addEventListener("keyup", applyFilters);
  if (gradeFilter) gradeFilter.addEventListener("change", applyFilters);
  if (statusFilter) statusFilter.addEventListener("change", applyFilters);

  teacherTableBody.addEventListener("click", (e) => {
    const link = e.target.closest("a");
    if (!link) return;
    e.preventDefault();
    const action = link.dataset.action || "";
    const row = link.closest("tr");
    if (!row) return;

    const { dataset } = row;
    const gradeLevel = dataset.teacherGradeLevel || "";
    const gradeLevelLabel =
      dataset.teacherGradeLevelReadable ||
      (gradeLevel ? `Grade ${gradeLevel}` : "");
    const sectionsReadable = dataset.teacherSectionsReadable || "";
    const sectionsRaw = dataset.teacherSections || "";
    const schoolLevel =
      dataset.teacherSchoolLevel || deriveSchoolLevel(gradeLevel);

    const teacherData = {
      id: dataset.teacherId || "",
      employeeId:
        dataset.teacherEmployeeId || row.cells[0]?.textContent.trim() || "",
      name: dataset.teacherName || row.cells[1]?.textContent.trim() || "",
      firstName: dataset.teacherFirstName || "",
      middleName: dataset.teacherMiddleName || "",
      lastName: dataset.teacherLastName || "",
      suffix: dataset.teacherSuffix || "",
      email: dataset.teacherEmail || "",
      username: dataset.teacherUsername || "",
      department: dataset.teacherDepartment || "",
      specialization: dataset.teacherSpecialization || "",
      gradeLevel,
      gradeLevelLabel,
      schoolLevel,
      adviserClass: dataset.teacherAdviserClass || "",
      hireDate: dataset.teacherHireDate || "",
      status: dataset.teacherStatus || row.cells[5]?.textContent.trim() || "",
      phone: dataset.teacherPhone || "",
    };

    if (
      action === "view" &&
      viewModal &&
      typeof viewModal.showDetails === "function"
    ) {
      viewModal.showDetails(teacherData);
      return;
    }

    if (action === "edit" && typeof openAddModal === "function") {
      openAddModal("edit", teacherData);
      return;
    }

    alert(`No handler configured for action: ${action}`);
  });
});
