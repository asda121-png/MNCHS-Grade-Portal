/**
 * Main JavaScript - Password visibility toggle
 */

// Toggle password visibility for login page (called with this context)
function togglePasswordVisibility(button) {
    const container = button.closest('.relative');
    const passwordInput = container.querySelector('input[type="password"], input[type="text"]');
    const eyeOpen = container.querySelector('.eye-open');
    const eyeClosed = container.querySelector('.eye-closed');

    if (!passwordInput) return;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        eyeOpen.classList.remove('hidden');
        eyeClosed.classList.add('hidden');
    }
}

// Toggle password visibility for registration pages (called with IDs)
function togglePassword(fieldId, eyeOpenId, eyeClosedId) {
    const passwordInput = document.getElementById(fieldId);
    const eyeOpen = document.getElementById(eyeOpenId);
    const eyeClosed = document.getElementById(eyeClosedId);

    if (!passwordInput) return;

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        if (eyeOpen) eyeOpen.classList.add('hidden');
        if (eyeClosed) eyeClosed.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        if (eyeOpen) eyeOpen.classList.remove('hidden');
        if (eyeClosed) eyeClosed.classList.add('hidden');
    }
}

// Prevent default form submission on button click if needed
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('button[onclick*="togglePassword"]');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
