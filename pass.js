// toggle-password.js
document.addEventListener('DOMContentLoaded', function () {
    // ★ パスワード本体用トグル
    const toggleText = document.getElementById('togglePwText');
    const passwordField = document.querySelector('input[name="password"]');
    if (toggleText && passwordField) {
        toggleText.style.cursor = 'pointer';
        toggleText.addEventListener('click', function () {
            const isPwd = passwordField.type === 'password';
            passwordField.type = isPwd ? 'text' : 'password';
            toggleText.textContent = isPwd ? '非表示' : '表示';
        });
    }

    // ★ 確認用パスワード用トグル（もし必要なら HTML 側も id="togglePw2Text" を用意）
    const toggleText2 = document.getElementById('togglePw2Text');
    const passwordField2 = document.querySelector('input[name="password2"]');
    if (toggleText2 && passwordField2) {
        toggleText2.style.cursor = 'pointer';
        toggleText2.addEventListener('click', function () {
            const isPwd2 = passwordField2.type === 'password';
            passwordField2.type = isPwd2 ? 'text' : 'password';
            toggleText2.textContent = isPwd2 ? '非表示' : '表示';
        });
    }
});
