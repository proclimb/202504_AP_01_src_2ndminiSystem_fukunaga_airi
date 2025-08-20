document.addEventListener("DOMContentLoaded", () => {
    const form = document.forms["login"];
    const email = document.getElementById("email");
    const pw = document.getElementById("password");

    form.addEventListener("submit", (e) => {
        if (email.value.trim() === "" || pw.value.trim() === "") {
            e.preventDefault();
            alert("メールアドレスとパスワードを入力してください");
        }
    });
});
