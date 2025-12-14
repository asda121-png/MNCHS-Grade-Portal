document.addEventListener("DOMContentLoaded", function () {
  // --- Element Selectors ---
  const searchInput = document.getElementById("searchInput");
  const studentTableBody = document.getElementById("studentTableBody");
  const addStudentButton = document.getElementById("openAddStudentButton");
  const modal = document.getElementById("addStudentModal");
  const viewModal = document.getElementById("viewStudentModal");
  const logoutLink = document.getElementById("logout-link");
  const modalContainer = document.getElementById("logout-modal-container");

  const setBodyScrollLocked = (locked) => {
    document.body.style.overflow = locked ? "hidden" : "";
  };

  // --- Logout Modal Setup ---
  if (logoutLink && modalContainer) {
    fetch("../../components/logout_modal.html")
      .then((response) => response.text())
      .then((html) => {
        modalContainer.innerHTML = html;
        const logoutModal = document.getElementById("logout-modal");
        const cancelLogout = document.getElementById("cancel-logout");
        if (logoutModal && cancelLogout) {
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
        }
      })
      .catch((error) => console.error("Error loading logout modal:", error));
  }

  // Skip if core elements don't exist
  if (!searchInput || !studentTableBody) {
    console.warn("Student page elements not found");
    return;
  }

  // --- Add Student Modal Setup (if modal exists) ---
  let closeAddModal = null;
  let openAddModal = null;

  if (modal) {
    const closeModalButton = modal.querySelector(".close-button");
    const cancelButton = modal.querySelector("#cancelButton");
    const addStudentForm = document.getElementById("addStudentForm");
    const nextButton = document.getElementById("nextButton");
    const prevButton = modal.querySelector("#prevButton");
    const saveButton = document.getElementById("saveButton");
    const formSteps = document.querySelectorAll(".form-step");
    const stepIndicators = document.querySelectorAll(".step-indicator .step");
    const modalTitle = modal.querySelector("#modalTitle");
    const gradeLevelSelect = document.getElementById("studentGradeLevel");
    const sectionSelect = document.getElementById("studentSection");
    const gradeSectionHidden = document.getElementById("studentGradeSection");

    // Adviser class info from PHP (window.__ADVISER_CLASS__)
    const adviserClass = window.__ADVISER_CLASS__ || null;
    let adviserGrade = "";
    let adviserSection = "";
    if (adviserClass && typeof adviserClass === "object") {
      adviserGrade = adviserClass.grade;
      adviserSection = adviserClass.section;
    }

    let currentStep = 1;
    const defaultModalTitle = modalTitle ? modalTitle.textContent : "";
    const defaultSaveText = saveButton ? saveButton.textContent : "";

    const setFieldValue = (id, value = "") => {
      const field = document.getElementById(id);
      if (field) field.value = value;
    };

    const syncGradeSection = () => {
      if (!gradeSectionHidden) return;
      const gradeValue = gradeLevelSelect?.value || "";
      const sectionValue = sectionSelect?.value || "";
      const gradeLabel = gradeValue ? `Grade ${gradeValue}` : "";

      if (gradeLabel && sectionValue) {
        gradeSectionHidden.value = `${gradeLabel} - ${sectionValue}`;
        return;
      }

      gradeSectionHidden.value = gradeLabel || sectionValue || "";
    };

    const setSelectedOption = (select, value) => {
      if (!select) return;
      select.value = value || "";
    };

    const gradeSectionsMap =
      (typeof window !== "undefined" && window.__GRADE_SECTIONS__) || {};

    const populateSectionOptions = (gradeValue, selectedSection = "") => {
      if (!sectionSelect) return;
      const sections =
        (gradeSectionsMap && gradeValue && gradeSectionsMap[gradeValue]) || [];

      // Disable section selection until grade is chosen
      sectionSelect.disabled = !gradeValue;

      // Reset options to placeholder
      sectionSelect.innerHTML = "";
      const placeholder = document.createElement("option");
      placeholder.value = "";
      placeholder.textContent = gradeValue
        ? "Select section"
        : "Select grade first";
      sectionSelect.appendChild(placeholder);

      if (!gradeValue) {
        return;
      }

      if (!sections || sections.length === 0) {
        const empty = document.createElement("option");
        empty.value = "";
        empty.textContent = "No sections available";
        sectionSelect.appendChild(empty);
        return;
      }

      sections
        .slice()
        .sort((a, b) => String(a).localeCompare(String(b)))
        .forEach((section) => {
          const option = document.createElement("option");
          option.value = section;
          option.textContent = `Section ${section}`;
          sectionSelect.appendChild(option);
        });

      if (selectedSection) {
        sectionSelect.value = selectedSection;
      }
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
      modal.classList.remove("show");
      setBodyScrollLocked(false);
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

    openAddModal = (mode = "add", data = null) => {
      if (addStudentForm) addStudentForm.reset();
      if (modalTitle)
        modalTitle.textContent =
          mode === "edit" ? "Edit Student" : defaultModalTitle;
      if (saveButton)
        saveButton.textContent =
          mode === "edit" ? "Update Student" : defaultSaveText;
      if (addStudentForm) {
        addStudentForm.dataset.mode = mode;
        addStudentForm.dataset.studentId = data?.id || "";
      }
      if (mode === "add" && adviserGrade && adviserSection) {
        // Auto-fill and lock grade/section for adviser's class
        setFieldValue("studentGradeLevel", adviserGrade);
        populateSectionOptions(adviserGrade, adviserSection);
        setFieldValue("studentSection", adviserSection);
        if (gradeLevelSelect) {
          gradeLevelSelect.value = adviserGrade;
          gradeLevelSelect.disabled = true;
        }
        if (sectionSelect) {
          sectionSelect.value = adviserSection;
          sectionSelect.disabled = true;
        }
      } else if (data) {
        // Edit mode: fill with student data
        setFieldValue("studentLRN", data.lrn || "");
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
        setFieldValue("studentFirstName", firstName);
        setFieldValue("studentMiddleName", middleName);
        setFieldValue("studentLastName", lastName);
        setFieldValue("studentSuffix", suffix);
        setFieldValue("streetAddress", data.address || "");
        setFieldValue("city", data.city || "");
        setFieldValue("province", data.province || "");
        setFieldValue("fatherName", data.fatherName || "");
        setFieldValue("motherName", data.motherName || "");
        setFieldValue("guardianName", data.guardianName || "");
        setFieldValue(
          "emergencyContactPerson",
          data.emergencyContactPerson || ""
        );
        setFieldValue(
          "emergencyContactNumber",
          data.emergencyContactNumber || ""
        );
        const gradeValue = data.gradeLevel || "";
        setFieldValue("studentGradeLevel", gradeValue);
        populateSectionOptions(gradeValue, data.section || "");
        setFieldValue("studentGradeSection", data.gradeSection || "");
        syncGradeSection();
        setFieldValue("studentStatus", data.status || "Enrolled");
        if (gradeLevelSelect) gradeLevelSelect.disabled = false;
        if (sectionSelect) sectionSelect.disabled = false;
      } else {
        populateSectionOptions(gradeLevelSelect?.value || "", "");
        if (gradeLevelSelect) gradeLevelSelect.disabled = false;
        if (sectionSelect) sectionSelect.disabled = false;
      }
      modal.classList.add("show");
      setBodyScrollLocked(true);
      currentStep = 1;
      updateFormSteps();
    };

    if (gradeLevelSelect)
      gradeLevelSelect.addEventListener("change", () => {
        populateSectionOptions(gradeLevelSelect.value || "", "");
        syncGradeSection();
      });
    if (sectionSelect)
      sectionSelect.addEventListener("change", syncGradeSection);

    if (addStudentButton) {
      addStudentButton.addEventListener("click", () => openAddModal("add"));
    }

    if (closeModalButton)
      closeModalButton.addEventListener("click", closeAddModal);
    if (cancelButton) cancelButton.addEventListener("click", closeAddModal);

    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeAddModal();
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

    if (addStudentForm) {
      addStudentForm.addEventListener("submit", (e) => {
        e.preventDefault();

        // Collect all form data
        const formData = new FormData();
        formData.append(
          "lrn",
          document.getElementById("studentLRN")?.value || ""
        );
        formData.append(
          "firstName",
          document.getElementById("studentFirstName")?.value || ""
        );
        formData.append(
          "middleName",
          document.getElementById("studentMiddleName")?.value || ""
        );
        formData.append(
          "lastName",
          document.getElementById("studentLastName")?.value || ""
        );
        formData.append(
          "suffix",
          document.getElementById("studentSuffix")?.value || ""
        );
        formData.append(
          "email",
          document.getElementById("studentEmail")?.value || ""
        );
        formData.append(
          "streetAddress",
          document.getElementById("streetAddress")?.value || ""
        );
        formData.append("city", document.getElementById("city")?.value || "");
        formData.append(
          "province",
          document.getElementById("province")?.value || ""
        );
        formData.append(
          "fatherName",
          document.getElementById("fatherName")?.value || ""
        );
        formData.append(
          "motherName",
          document.getElementById("motherName")?.value || ""
        );
        formData.append(
          "guardianName",
          document.getElementById("guardianName")?.value || ""
        );
        formData.append(
          "guardianRelationship",
          document.getElementById("guardianRelationship")?.value || ""
        );
        formData.append(
          "guardianContact",
          document.getElementById("studentGuardian")?.value || ""
        );
        formData.append(
          "emergencyContact",
          document.getElementById("emergencyContact")?.value || ""
        );
        formData.append(
          "gradeLevel",
          document.getElementById("studentGradeLevel")?.value || ""
        );
        formData.append(
          "section",
          document.getElementById("studentSection")?.value || ""
        );
        formData.append(
          "status",
          document.getElementById("studentStatus")?.value || "Not Enrolled"
        );
        formData.append(
          "dateEnrolled",
          document.getElementById("studentDateEnrolled")?.value || ""
        );
        formData.append(
          "dateOfBirth",
          document.getElementById("studentDOB")?.value || ""
        );

        // Determine action (add or update)
        const isEdit = addStudentForm.dataset.mode === "edit";
        const action = isEdit ? "update" : "add";

        if (isEdit) {
          formData.append("studentId", addStudentForm.dataset.studentId);
        }

        // Show loading indicator
        const saveButton = document.getElementById("saveButton");
        const originalText = saveButton?.textContent || "Save Student";
        if (saveButton) saveButton.textContent = "Saving...";
        if (saveButton) saveButton.disabled = true;

        // Send to API
        fetch(`../../server/api/students.php?action=${action}`, {
          method: "POST",
          body: formData,
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              showNotification(
                isEdit
                  ? "Student updated successfully!"
                  : "Student saved successfully!",
                "success"
              );
              closeAddModal();
              // Reload page to refresh student list
              setTimeout(() => location.reload(), 1500);
            } else {
              showNotification(
                "Error: " + (data.error || "Failed to save student"),
                "error"
              );
            }
          })
          .catch((error) => {
            console.error("Error:", error);
            showNotification("Error saving student: " + error.message, "error");
          })
          .finally(() => {
            if (saveButton) {
              saveButton.textContent = originalText;
              saveButton.disabled = false;
            }
          });
      });
    }
  }

  if (viewModal) {
    const viewFields = {
      lrn: document.getElementById("viewStudentLrn"),
      studentNumber: document.getElementById("viewStudentNumber"),
      name: document.getElementById("viewStudentName"),
      grade: document.getElementById("viewStudentGrade"),
      section: document.getElementById("viewStudentSection"),
      status: document.getElementById("viewStudentStatus"),
      email: document.getElementById("viewStudentEmail"),
      username: document.getElementById("viewStudentUsername"),
      guardian: document.getElementById("viewStudentGuardian"),
      address: document.getElementById("viewStudentAddress"),
      dob: document.getElementById("viewStudentDob"),
      enrollment: document.getElementById("viewStudentEnrollment"),
    };

    const closeViewModal = () => {
      viewModal.classList.remove("show");
      setBodyScrollLocked(false);
    };

    const displayText = (value, modifier) => {
      if (value === null || value === undefined) return "N/A";
      const stringValue = String(value).trim();
      if (!stringValue) return "N/A";
      const result = modifier ? modifier(stringValue) : stringValue;
      if (result === null || result === undefined || result === "")
        return "N/A";
      return result;
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
      if (e.target === viewModal) closeViewModal();
    });

    viewModal.querySelectorAll('[data-close="view"]').forEach((button) => {
      button.addEventListener("click", closeViewModal);
    });

    viewModal.showDetails = (data) => {
      if (!data) return;
      if (viewFields.lrn) viewFields.lrn.textContent = displayText(data.lrn);
      if (viewFields.studentNumber)
        viewFields.studentNumber.textContent = displayText(
          data.studentNumber || data.id
        );
      if (viewFields.name) viewFields.name.textContent = displayText(data.name);
      if (viewFields.grade) {
        viewFields.grade.textContent = displayText(data.gradeLevel, (val) =>
          val.toLowerCase().includes("grade") ? val : `Grade ${val}`
        );
      }
      if (viewFields.section)
        viewFields.section.textContent = displayText(data.section);
      if (viewFields.status)
        viewFields.status.textContent = displayText(data.status);
      if (viewFields.email)
        viewFields.email.textContent = displayText(data.email);
      if (viewFields.username)
        viewFields.username.textContent = displayText(data.username);
      if (viewFields.guardian)
        viewFields.guardian.textContent = displayText(data.guardianContact);
      if (viewFields.address)
        viewFields.address.textContent = displayText(data.address);
      if (viewFields.dob)
        viewFields.dob.textContent = displayText(data.dateOfBirth, formatDate);
      if (viewFields.enrollment)
        viewFields.enrollment.textContent = displayText(
          data.enrollmentDate,
          formatDate
        );
      viewModal.classList.add("show");
      setBodyScrollLocked(true);
    };
  }

  // --- Table Filtering ---
  const applyFilters = () => {
    const searchText = searchInput.value.toLowerCase();
    const tableRows = studentTableBody.getElementsByTagName("tr");

    for (let i = 0; i < tableRows.length; i++) {
      const row = tableRows[i];
      const lrn = row.cells[0]?.textContent.toLowerCase() || "";
      const name = row.cells[1]?.textContent.toLowerCase() || "";

      const searchMatch = lrn.includes(searchText) || name.includes(searchText);

      row.style.display = searchMatch ? "" : "none";
    }
  };

  // Event listeners for filters
  searchInput.addEventListener("keyup", applyFilters);

  // Table action links
  studentTableBody.addEventListener("click", function (e) {
    const link = e.target.closest("a");
    if (!link) return;
    e.preventDefault();
    const action = link.dataset.action || "";
    const row = link.closest("tr");
    if (!row) return;

    const { dataset } = row;
    const gradeLevel = dataset.studentGrade || "";
    const section = dataset.studentSection || "";
    const gradeLabel = gradeLevel
      ? gradeLevel.toLowerCase().includes("grade")
        ? gradeLevel
        : `Grade ${gradeLevel}`
      : "";
    const gradeSection =
      gradeLabel && section
        ? `${gradeLabel} - ${section}`
        : gradeLabel || section || row.cells[2]?.textContent.trim() || "";

    const studentData = {
      id: dataset.studentId || "",
      studentNumber: dataset.studentNumber || "",
      lrn: dataset.studentLrn || row.cells[0]?.textContent.trim() || "",
      name: dataset.studentName || row.cells[1]?.textContent.trim() || "",
      firstName: dataset.studentFirstName || "",
      middleName: dataset.studentMiddleName || "",
      lastName: dataset.studentLastName || "",
      suffix: dataset.studentSuffix || "",
      gradeLevel,
      section,
      gradeSection,
      status: dataset.studentStatus || row.cells[3]?.textContent.trim() || "",
      email: dataset.studentEmail || "",
      username: dataset.studentUsername || "",
      guardianContact: dataset.studentGuardian || "",
      address: dataset.studentAddress || "",
      dateOfBirth: dataset.studentDob || "",
      enrollmentDate: dataset.studentEnrolled || "",
      guardianName: dataset.studentGuardianName || "",
      emergencyContactPerson: dataset.studentEmergencyContactPerson || "",
      emergencyContactNumber:
        dataset.studentEmergencyContactNumber || dataset.studentGuardian || "",
    };

    if (
      action === "view" &&
      viewModal &&
      typeof viewModal.showDetails === "function"
    ) {
      viewModal.showDetails(studentData);
      return;
    }

    if (action === "edit" && openAddModal) {
      openAddModal("edit", studentData);
      return;
    }

    alert(`No handler configured for action: ${action}`);
  });
});
