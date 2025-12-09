/**
 * Toggles the visibility of a password field and its corresponding eye icons.
 * @param {string} inputId The ID of the password input field.
 * @param {string} eyeOpenId The ID of the "eye open" SVG icon.
 * @param {string} eyeClosedId The ID of the "eye closed" SVG icon.
 */
function togglePassword(inputId, eyeOpenId, eyeClosedId) {
    const passwordInput = document.getElementById(inputId);
    const eyeOpen = document.getElementById(eyeOpenId);
    const eyeClosed = document.getElementById(eyeClosedId);

    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';
    eyeOpen.classList.toggle('hidden', isPassword);
    eyeClosed.classList.toggle('hidden', !isPassword);
}