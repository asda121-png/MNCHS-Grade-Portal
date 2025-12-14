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

  const gradeLevelSelect = document.getElementById("grade_level");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");
  const successNotification = document.getElementById("successNotification");
  const strandContainer = document.getElementById("strand_container");
  const strandSelect = document.getElementById("strand");

  // --- Event Listeners ---
  nextPhase1Btn.addEventListener("click", () => {
    // Check validity of Phase 1 inputs
    const phase1Inputs = phase1.querySelectorAll("input[required]");
    let allValid = true;
    phase1Inputs.forEach((input) => {
      if (!input.checkValidity()) {
        allValid = false;
        // This will trigger the browser's own validation UI
        registrationForm.reportValidity();
      }
    });

    if (allValid) {
      // Hide Phase 1
      phase1.classList.add("hidden");
      nextPhase1Btn.classList.add("hidden");

      // Show Phase 2 content and its buttons
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
    const phase2Inputs = phase2.querySelectorAll(
      "select[required], input[required]"
    );
    let allValid = true;
    phase2Inputs.forEach((input) => {
      if (!input.checkValidity()) {
        allValid = false;
        registrationForm.reportValidity();
      }
    });

    if (allValid) {
      // Hide Phase 2
      phase2.classList.add("hidden");
      phase2Buttons.classList.add("hidden");

      // Show Phase 3 content and its buttons
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

  gradeLevelSelect.addEventListener("change", () => {
    const selectedGrade = gradeLevelSelect.value;
    if (selectedGrade === "11" || selectedGrade === "12") {
      strandSelect.removeAttribute("disabled");
      strandSelect.setAttribute("required", "");
    } else {
      strandSelect.setAttribute("disabled", "");
      strandSelect.removeAttribute("required");
      strandSelect.value = ""; // Reset value
    }
  });

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

    // Manually check if passwords match
    if (passwordInput.value !== confirmPasswordInput.value) {
      alert("Passwords do not match. Please try again.");
      allValid = false;
    }

    if (allValid) {
      // Gather all form data
      const formData = new FormData(registrationForm);

      fetch("student_register_api.php", {
        method: "POST",
        body: formData,
      })
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
        .catch(() => {
          alert("An error occurred. Please try again.");
        });
    }
  });
});
