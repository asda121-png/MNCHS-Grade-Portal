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

document.addEventListener('DOMContentLoaded', () => {
    const phase1 = document.getElementById('phase1');
    const phase2 = document.getElementById('phase2');

    const nextPhase1Btn = document.getElementById('nextPhase1');
    const phase2Buttons = document.getElementById('phase2_buttons');
    const backPhase2Btn = document.getElementById('backPhase2');

    const registrationForm = document.getElementById('registrationForm');
    const phaseTracker = document.getElementById('phaseTracker');

    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const successNotification = document.getElementById('successNotification');

    // --- Event Listeners ---
    nextPhase1Btn.addEventListener('click', () => {
        const phase1Inputs = phase1.querySelectorAll('input[required]');
        let allValid = true;
        phase1Inputs.forEach(input => {
            if (!input.checkValidity()) {
                allValid = false;
                registrationForm.reportValidity();
            }
        });

        if (allValid) {
            phase1.classList.add('hidden');
            nextPhase1Btn.classList.add('hidden');

            phase2.classList.remove('hidden');
            phase2Buttons.classList.remove('hidden');
            phaseTracker.textContent = '2/2';
        }
    });

    backPhase2Btn.addEventListener('click', () => {
        phase2.classList.add('hidden');
        phase2Buttons.classList.add('hidden');

        phase1.classList.remove('hidden');
        nextPhase1Btn.classList.remove('hidden');
        phaseTracker.textContent = '1/2';
    });

    registrationForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const phase2Inputs = phase2.querySelectorAll('input[required]');
        let allValid = true;
        phase2Inputs.forEach(input => {
            if (!input.checkValidity()) {
                allValid = false;
                registrationForm.reportValidity();
            }
        });

        if (passwordInput.value !== confirmPasswordInput.value) {
            alert("Passwords do not match. Please try again.");
            confirmPasswordInput.focus();
            allValid = false;
        }

        if (allValid) {
            successNotification.classList.remove('hidden');
            successNotification.classList.add('flex');

            setTimeout(() => {
                window.location.href = '../../index.php';
            }, 2000);
        }
    });
});