window.addEventListener('load', function () {
    document.edit.name.addEventListener('input', validateNameField);
    document.edit.kana.addEventListener('input', validateKanaField);
    document.edit.email.addEventListener('input', validateEmailField);
    document.edit.tel.addEventListener('input', validateTelField);
    document.edit.postal_code.addEventListener('input', validatePostalCodeField);
    document.edit.password.addEventListener('input', validatePasswordField);
    document.edit.password2.addEventListener('input', validatePassword2Field);

    const front = document.getElementById('document1');
    if (front) front.addEventListener('change', () => validateFileField(front, '本人確認書類（表）'));

    const back = document.getElementById('document2');
    if (back) back.addEventListener('change', () => validateFileField(back, '本人確認書類（裏）'));
});

function validateNameField() {
    const field = document.edit.name;
    clearFieldError(field);
    if (field.value === "") {
        errorElement(field, "お名前が入力されていません");
    } else if (field.value.trim() === "") {
        errorElement(field, "スペースのみでは入力できません");
    }
}

function validateKanaField() {
    const field = document.edit.kana;
    clearFieldError(field);
    const raw = field.value;
    const noSpace = raw.replace(/[\s　]/g, "");
    if (raw === "") {
        errorElement(field, "ふりがなが入力されていません");
    } else if (noSpace === "") {
        errorElement(field, "スペースのみでは入力できません");
    } else if (!validateKana(noSpace)) {
        errorElement(field, "ひらがなで入力してください");
    }
}

function validateEmailField() {
    const field = document.edit.email;
    clearFieldError(field);
    const val = field.value;
    if (val === "") {
        errorElement(field, "メールアドレスが入力されていません");
    } else if (val.trim() === "") {
        errorElement(field, "スペースのみでは入力できません");
    } else if (!validateMail(val.trim())) {
        errorElement(field, "有効なメールアドレスを入力してください");
    }
}

function validateTelField() {
    const field = document.edit.tel;
    clearFieldError(field);
    const val = field.value;
    if (val === "") {
        errorElement(field, "電話番号が入力されていません");
    } else if (val.trim() === "") {
        errorElement(field, "スペースのみでは入力できません");
    } else if (!validateTel(val.trim())) {
        errorElement(field, "電話番号は「090-1234-5678」の形式で入力してください");
    }
}

function validatePostalCodeField() {
    const field = document.edit.postal_code;
    clearFieldError(field);
    const val = field.value;
    if (val === "") {
        errorElement(field, "郵便番号が入力されていません");
    } else if (val.trim() === "") {
        errorElement(field, "スペースのみでは入力できません");
    } else if (!validatePostalCode(val.trim())) {
        errorElement(field, "郵便番号はXXX-XXXX または XXXXXXX の形式で入力してください");
    }
}

function validatePasswordField() {
    const field = document.edit.password;
    clearFieldError(field);
    const val = field.value;
    if (val === "") {
        errorElement(field, "パスワードが入力されていません");
    } else if (val.trim() === "") {
        errorElement(field, "スペースのみでは入力できません");
    } else if (val.length < 8) {
        errorElement(field, "8文字以上64文字以下で入力してください");
    } else if (val.length > 64) {
        errorElement(field, "8文字以上64文字以下で入力してください");
    }
}

function validatePassword2Field() {
    const field = document.edit.password2;
    clearFieldError(field);
    const val = field.value;
    if (val === "") {
        errorElement(field, "確認用パスワードが入力されていません");
    } else if (val.trim() === "") {
        errorElement(field, "スペースのみでは入力できません");
    } else if (val !== document.edit.password.value) {
        errorElement(field, "パスワードが一致しません");
    }
}

function validateFileField(input, label) {
    clearFieldError(input);
    const file = input.files[0];
    if (!file) return;
    const allowed = ['jpg', 'jpeg', 'png'];
    const ext = file.name.split('.').pop().toLowerCase();
    if (!allowed.includes(ext)) {
        errorElement(input, `ファイル形式は PNG または JPEG のみ許可されています`);
    }
}

function clearFieldError(field) {
    if (field.name === "postal_code") {
        const placeholder = document.querySelector(".postal-error-placeholder");
        if (placeholder) placeholder.innerHTML = "";
        const serverError = document.querySelector(".error-msg2");
        if (serverError && serverError.textContent.includes("郵便番号")) serverError.remove();
    }

    // クライアント側のエラーだけ削除する
    let next = field.nextSibling;
    while (next) {
        if (next.nodeType === 1 && next.classList.contains("error-msg")) {
            let toRemove = next;
            next = next.nextSibling;
            toRemove.remove();
        } else {
            break;
        }
    }

    field.classList.remove("error-form");
}

function errorElement(field, msg) {
    field.classList.add("error-form");
    const e = document.createElement("div");
    e.className = "error-msg";
    e.textContent = msg;
    if (field.name === "postal_code") {
        const placeholder = document.querySelector(".postal-error-placeholder");
        if (placeholder) {
            placeholder.innerHTML = "";
            placeholder.appendChild(e);
            return;
        }
    }
    field.parentNode.insertBefore(e, field.nextSibling);
}

// 各種バリデーション関数
function validateMail(val) {
    return /^[A-Za-z0-9_.-]+@[A-Za-z0-9_.-]+\.[A-Za-z0-9]+$/.test(val);
}
function validateTel(val) {
    return /^[0-9]{2,4}-[0-9]{2,4}-[0-9]{3,4}$/.test(val);
}
function validateKana(val) {
    return /^[ぁ-んー\\s　]+$/.test(val);
}
function validatePostalCode(val) {
    return /^[0-9]{3}-?[0-9]{4}$/.test(val);
}
