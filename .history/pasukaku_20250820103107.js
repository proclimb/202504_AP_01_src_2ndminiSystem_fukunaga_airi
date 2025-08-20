document.addEventListener("DOMContentLoaded", () => {
    const pw = document.getElementById("password");
    const errorMsg = document.createElement("div");
    errorMsg.classList.add("error-msg");
    pw.parentNode.appendChild(errorMsg);

    function validatePassword() {
        const value = pw.value.trim();

        if (value === "") {
            errorMsg.textContent = "パスワードを入力してください";
            return false;
        }

        // 英大文字・小文字を必須にする例
        if (!/[a-z]/.test(value) || !/[A-Z]/.test(value)) {
            errorMsg.textContent = "大文字と小文字を両方含めてください";
            return false;
        }

        errorMsg.textContent = "";
        return true;
    }

    pw.addEventListener("input", validatePassword);

    document.querySelector("form[name='login']").addEventListener("submit", (e) => {
        if (!validatePassword()) {
            e.preventDefault();
        }
    });
});