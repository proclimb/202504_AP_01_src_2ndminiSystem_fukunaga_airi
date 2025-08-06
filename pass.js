// toggle-password.js
document.addEventListener('DOMContentLoaded', () => {
    const pwField = document.getElementById('password');
    const toggle = document.getElementById('togglePw');
    console.log({ pwField, toggle });
    if (!pwField || !toggle) return;

    toggle.addEventListener('change', () => {
        pwField.type = toggle.checked ? 'text' : 'password';
    });
});