// Existing togglePassword function
function togglePassword(fieldId, eyeOpenId, eyeClosedId) {
  const pw = document.getElementById(fieldId);
  const eyeOpen = document.getElementById(eyeOpenId);
  const eyeClosed = document.getElementById(eyeClosedId);

  if (pw.type === "password") {
    pw.type = "text";
    eyeOpen.classList.add("hidden");
    eyeClosed.classList.remove("hidden");
  } else {
    pw.type = "password";
    eyeOpen.classList.remove("hidden");
    eyeClosed.classList.add("hidden");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const phase1 = document.getElementById("phase1");
  const phase2 = document.getElementById("phase2");
  const phase3 = document.getElementById("phase3");

  const nextPhase1Btn = document.getElementById("nextPhase1");
  const phase2Buttons = document.getElementById("phase2_buttons");
  const backPhase2Btn = document.getElementById("backPhase2");
  const nextPhase2Btn = document.getElementById("nextPhase2");
  const phase3Buttons = document.getElementById("phase3_buttons");
  const backPhase3Btn = document.getElementById("backPhase3");

  const registrationForm = document.getElementById("registrationForm");
  const phaseTracker = document.getElementById("phaseTracker");

  const sectionSelect = document.getElementById("section");
  const gradeLevelSelect = document.getElementById("grade_level");
  // Password fields removed as credentials are now auto-generated and emailed
  const successNotification = document.getElementById("successNotification");
  const strandContainer = document.getElementById("strand_container");
  const strandSelect = document.getElementById("strand");

  // --- Event Listeners ---
  nextPhase1Btn.addEventListener("click", () => {
    // Only validate required inputs that are visible in Phase 1
    const phase1Inputs = Array.from(
      phase1.querySelectorAll("input[required], select[required]")
    );
    let allValid = true;
    phase1Inputs.forEach((input) => {
      if (input.offsetParent !== null && !input.checkValidity()) {
        allValid = false;
        registrationForm.reportValidity();
      }
    });

    if (allValid) {
      phase1.classList.add("hidden");
      nextPhase1Btn.classList.add("hidden");
      phase2.classList.remove("hidden");
      phase2Buttons.classList.remove("hidden");
      phaseTracker.textContent = "2/3";
    }
  });

  backPhase2Btn.addEventListener("click", () => {
    // Hide Phase 2
    phase2.classList.add("hidden");
    phase2Buttons.classList.add("hidden");

    // Show Phase 1 content and its button
    phase1.classList.remove("hidden");
    nextPhase1Btn.classList.remove("hidden");
    phaseTracker.textContent = "1/3";
  });

  nextPhase2Btn.addEventListener("click", () => {
    // Only validate required inputs that are visible in Phase 2
    const phase2Inputs = Array.from(
      phase2.querySelectorAll("input[required], select[required]")
    );
    let allValid = true;
    phase2Inputs.forEach((input) => {
      if (input.offsetParent !== null && !input.checkValidity()) {
        allValid = false;
        registrationForm.reportValidity();
      }
    });

    if (allValid) {
      phase2.classList.add("hidden");
      phase2Buttons.classList.add("hidden");
      phase3.classList.remove("hidden");
      phase3Buttons.classList.remove("hidden");
      phaseTracker.textContent = "3/3";
    }
  });

  backPhase3Btn.addEventListener("click", () => {
    // Hide Phase 3
    phase3.classList.add("hidden");
    phase3Buttons.classList.add("hidden");

    // Show Phase 2 content and its buttons
    phase2.classList.remove("hidden");
    phase2Buttons.classList.remove("hidden");
    phaseTracker.textContent = "2/3";
  });

  // --- Dynamic Section and Strand Logic ---

  async function updateSections() {
    const gradeLevel = gradeLevelSelect.value;

    // Reset and disable section select
    sectionSelect.innerHTML = '<option value="">Loading...</option>';
    sectionSelect.disabled = true;

    if (!gradeLevel) {
      sectionSelect.innerHTML =
        '<option value="">Select grade level first</option>';
      return;
    }

    try {
      // The path to the API endpoint. Adjust if your file structure is different.
      const response = await fetch(
        `../../server/api/get_sections.php?grade_level=${gradeLevel}`
      );
      if (!response.ok) {
        throw new Error(
          `Network response was not ok, status: ${response.status}`
        );
      }
      const data = await response.json();

      if (data.success && data.data.sections.length > 0) {
        sectionSelect.innerHTML = '<option value="">Select a Section</option>';
        data.data.sections.forEach((sec) => {
          const option = document.createElement("option");
          option.value = sec.section_name;
          option.textContent = sec.section_name;
          sectionSelect.appendChild(option);
        });
        sectionSelect.disabled = false; // Enable the dropdown
      } else {
        sectionSelect.innerHTML =
          '<option value="">No sections available</option>';
      }
    } catch (error) {
      console.error("Error fetching sections:", error);
      sectionSelect.innerHTML =
        '<option value="">Failed to load sections</option>';
    }
  }

  function handleStrandVisibility() {
    const gradeLevel = parseInt(gradeLevelSelect.value, 10);
    const isSeniorHigh = gradeLevel === 11 || gradeLevel === 12;

    strandContainer.style.display = isSeniorHigh ? "block" : "none";
    strandSelect.disabled = !isSeniorHigh;
    strandSelect.required = isSeniorHigh;
    if (!isSeniorHigh) {
      strandSelect.value = ""; // Clear value if not SHS
    }
  }

  registrationForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const phase3Inputs = phase3.querySelectorAll("input[required]");
    let allValid = true;
    phase3Inputs.forEach((input) => {
      if (!input.checkValidity()) {
        allValid = false;
        registrationForm.reportValidity();
      }
    });

    // Password fields and validation removed: credentials are auto-generated and emailed

    if (allValid) {
      // Gather all form data

      const formData = new FormData(registrationForm);
      // Debug: log all form data before sending
      for (let pair of formData.entries()) {
        console.log(pair[0] + ": " + pair[1]);
      }

      fetch(
        "http://localhost/MNCHS%20Grade%20Portal/server/api/student_register_api.php",
        {
          method: "POST",
          body: formData,
        }
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            successNotification.classList.remove("hidden");
            successNotification.classList.add("flex");
            setTimeout(() => {
              window.location.href = "../../index.php";
            }, 2000);

            // Also allow immediate redirect if user clicks 'Sign in' during success notification
            const signInLink = document.getElementById("signInLink");
            if (signInLink) {
              signInLink.addEventListener("click", function (e) {
                e.preventDefault();
                window.location.href = "../../index.php";
              });
            }
          } else {
            alert(data.message || "Registration failed.");
          }
        })
        .catch((error) => {
          console.error("Registration error:", error);
          alert("An error occurred. Please try again.\n" + error);
        });
    }
  });

  // Combined event listener for grade level changes
  gradeLevelSelect.addEventListener("change", () => {
    updateSections();
    handleStrandVisibility();
  });

  // Initial setup on page load
  handleStrandVisibility();
  if (gradeLevelSelect.value) {
    updateSections();
  }
});
