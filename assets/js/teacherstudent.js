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

  if (!searchInput || !studentTableBody) return;

  // ---------------- ADD STUDENT MODAL ----------------
  let openAddModal = null;
  let closeAddModal = null;

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

    const adviserClass = window.__ADVISER_CLASS__ || null;
    let adviserGrade = adviserClass?.grade || "";
    let adviserSection = adviserClass?.section || "";

    let currentStep = 1;
    const defaultModalTitle = modalTitle?.textContent || "";
    const defaultSaveText = saveButton?.textContent || "";

    const gradeSectionsMap = window.__GRADE_SECTIONS__ || {};

    const populateSectionOptions = (grade, selected = "") => {
      if (!sectionSelect) return;

      sectionSelect.innerHTML = "";
      sectionSelect.disabled = !grade;

      const placeholder = document.createElement("option");
      placeholder.value = "";
      placeholder.textContent = grade ? "Select section" : "Select grade first";
      sectionSelect.appendChild(placeholder);

      if (!grade || !gradeSectionsMap[grade]) return;

      gradeSectionsMap[grade].forEach((sec) => {
        const opt = document.createElement("option");
        opt.value = sec;
        opt.textContent = `Section ${sec}`;
        sectionSelect.appendChild(opt);
      });

      sectionSelect.value = selected;
    };

    const syncGradeSection = () => {
      if (!gradeSectionHidden) return;
      const g = gradeLevelSelect?.value || "";
      const s = sectionSelect?.value || "";
      gradeSectionHidden.value = g && s ? `Grade ${g} - ${s}` : `Grade ${g}`;
    };

    const updateSteps = () => {
      formSteps.forEach((s) =>
        s.classList.toggle("active", parseInt(s.dataset.step) === currentStep)
      );

      stepIndicators.forEach((s) =>
        s.classList.toggle("active", parseInt(s.dataset.step) === currentStep)
      );

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
      addStudentForm?.reset();
      currentStep = 1;
      updateSteps();
      modalTitle.textContent = defaultModalTitle;
      saveButton.textContent = defaultSaveText;
      delete addStudentForm.dataset.mode;
      delete addStudentForm.dataset.studentId;
    };

    openAddModal = (mode = "add", data = null) => {
      addStudentForm.reset();
      addStudentForm.dataset.mode = mode;
      addStudentForm.dataset.studentId = data?.id || "";

      modalTitle.textContent =
        mode === "edit" ? "Edit Student" : defaultModalTitle;
      saveButton.textContent =
        mode === "edit" ? "Update Student" : defaultSaveText;

      if (mode === "add" && adviserGrade && adviserSection) {
        gradeLevelSelect.value = adviserGrade;
        gradeLevelSelect.disabled = true;
        populateSectionOptions(adviserGrade, adviserSection);
        sectionSelect.disabled = true;
      } else {
        gradeLevelSelect.disabled = false;
        sectionSelect.disabled = false;
      }

      modal.classList.add("show");
      setBodyScrollLocked(true);
      currentStep = 1;
      updateSteps();
    };

    gradeLevelSelect?.addEventListener("change", () => {
      populateSectionOptions(gradeLevelSelect.value);
      syncGradeSection();
    });

    sectionSelect?.addEventListener("change", syncGradeSection);
    addStudentButton?.addEventListener("click", () => openAddModal("add"));
    closeModalButton?.addEventListener("click", closeAddModal);
    cancelButton?.addEventListener("click", closeAddModal);

    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeAddModal();
    });

    nextButton?.addEventListener("click", () => {
      if (currentStep < formSteps.length) currentStep++;
      updateSteps();
    });

    prevButton?.addEventListener("click", () => {
      if (currentStep > 1) currentStep--;
      updateSteps();
    });

    // ---------------- FORM SUBMIT ----------------
    addStudentForm?.addEventListener("submit", (e) => {
      e.preventDefault();

      const formData = new FormData(addStudentForm);
      const isEdit = addStudentForm.dataset.mode === "edit";
      const action = isEdit ? "update" : "add";

      if (isEdit)
        formData.append("studentId", addStudentForm.dataset.studentId);

      saveButton.textContent = "Saving...";
      saveButton.disabled = true;

      fetch(`../../server/api/students.php?action=${action}`, {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            showNotification("Student saved successfully", "success");
            closeAddModal();
            setTimeout(() => location.reload(), 1500);
          } else {
            showNotification(data.message || "Failed", "error");
          }
        })
        .catch((err) => showNotification("Error: " + err.message, "error"))
        .finally(() => {
          saveButton.textContent = defaultSaveText;
          saveButton.disabled = false;
        });
    });
  }

  // ---------------- VIEW MODAL ----------------
  const closeViewModal = () => {
    if (!viewModal) return;
    viewModal.classList.remove("show");
    setBodyScrollLocked(false);
  };

  if (viewModal) {
    viewModal.querySelectorAll('[data-close="view"]').forEach((btn) => {
      btn.addEventListener("click", closeViewModal);
    });

    viewModal.showDetails = (data) => {
      if (!data) return;
      viewModal.classList.add("show");
      setBodyScrollLocked(true);
    };
  }

  // ---------------- SEARCH FILTER ----------------
  const applyFilters = () => {
    const text = searchInput.value.toLowerCase();
    [...studentTableBody.rows].forEach((row) => {
      const match =
        row.cells[0].textContent.toLowerCase().includes(text) ||
        row.cells[1].textContent.toLowerCase().includes(text);
      row.style.display = match ? "" : "none";
    });
  };

  searchInput.addEventListener("keyup", applyFilters);
});
