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
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (e) => {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault(); // Prevent form submission
                alert("Passwords do not match. Please try again.");
                confirmPassword.focus();
            }
        });
    }
});