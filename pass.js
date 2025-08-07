// toggle-password.js
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('togglePw');
    const passwordField = document.querySelector('input[name="password"]');

    if (toggle && passwordField) {
        toggle.addEventListener('change', function () {
            passwordField.type = this.checked ? 'text' : 'password';
        });
    }

    const passwordField2 = document.querySelector('input[name="password2"]');
    const toggle2 = document.getElementById('togglePw2');
    if (toggle2 && passwordField2) {
        toggle2.addEventListener('change', function () {
            passwordField2.type = this.checked ? 'text' : 'password';
        });
    }
});
