window.addEventListener('load', function () {
    // 以下、各フィールドのリアルタイムバリデーション設定
    document.edit.name.addEventListener('input', validateNameField);
    document.edit.kana.addEventListener('input', validateKanaField);
    document.edit.email.addEventListener('input', validateEmailField);
    document.edit.tel.addEventListener('input', validateTelField);
    document.edit.postal_code.addEventListener('input', validatePostalCodeField);

    // ファイル形式チェック（表）
    const front = document.getElementById('document1');
    if (front) {
        front.addEventListener('change', function () {
            validateFileField(this, '本人確認書類（表）');
        });
    }

    // ファイル形式チェック（裏）
    const back = document.getElementById('document2');
    if (back) {
        back.addEventListener('change', function () {
            validateFileField(this, '本人確認書類（裏）');
        });
    }
});

/**
 * 名前のリアルタイムバリデーション
 */
function validateNameField() {
    clearFieldError(document.edit.name);
    const value = document.edit.name.value;
    if (value === "") {
        errorElement(document.edit.name, "お名前が入力されていません");
    } else if (value.trim() === "") {
        errorElement(document.edit.name, "スペースのみでは入力できません");
    }
}

/**
 * ふりがなのリアルタイムバリデーション
 */
function validateKanaField() {
    clearFieldError(document.edit.kana);
    const rawValue = document.edit.kana.value;
    const noSpaces = rawValue.replace(/[\s　]/g, ""); // 全角・半角スペース除去

    if (rawValue !== "" && noSpaces === "") {
        errorElement(document.edit.kana, "スペースのみでは入力できません");
    } else if (noSpaces === "") {
        errorElement(document.edit.kana, "ふりがなが入力されていません");
    } else if (!validateKana(noSpaces)) {
        errorElement(document.edit.kana, "ひらがなで入力してください");
    }
}

/**
 * メールのリアルタイムバリデーション
 */
function validateEmailField() {
    clearFieldError(document.edit.email);
    const rawValue = document.edit.email.value;
    const trimmed = rawValue.trim();

    if (rawValue !== "" && trimmed === "") {
        errorElement(document.edit.email, "スペースのみでは入力できません");
    } else if (trimmed === "") {
        errorElement(document.edit.email, "メールアドレスが入力されていません");
    } else if (!validateMail(trimmed)) {
        errorElement(document.edit.email, "有効なメールアドレスを入力してください");
    }
}

/**
 * 電話番号のリアルタイムバリデーション
 */
function validateTelField() {
    clearFieldError(document.edit.tel);
    const rawValue = document.edit.tel.value;
    const trimmed = rawValue.trim();

    if (rawValue !== "" && trimmed === "") {
        errorElement(document.edit.tel, "スペースのみでは入力できません");
    } else if (trimmed === "") {
        errorElement(document.edit.tel, "電話番号が入力されていません");
    } else if (!validateTel(trimmed)) {
        errorElement(document.edit.tel, "電話番号は「090-1234-5678」のようにハイフンを含めて正しく入力してください");
    }
}

/**
 * 郵便番号のリアルタイムバリデーション
 */
function validatePostalCodeField() {
    clearFieldError(document.edit.postal_code);
    const rawValue = document.edit.postal_code.value;
    const trimmed = rawValue.trim();

    if (rawValue !== "" && trimmed === "") {
        errorElement(document.edit.postal_code, "スペースのみでは入力できません");
    } else if (trimmed === "") {
        errorElement(document.edit.postal_code, "郵便番号が入力されていません");
    } else if (!validatePostalCode(trimmed)) {
        errorElement(document.edit.postal_code, "郵便番号はXXX-XXXX または XXXXXXX の形式で入力してください");
    }
}

/**
 * ファイルの形式チェック
 */
function validateFileField(input, label) {
    clearFieldError(input);
    const file = input.files[0];
    if (!file) return;
    const allowedExtensions = ['jpg', 'jpeg', 'png'];
    const fileName = file.name;
    const extension = fileName.split('.').pop().toLowerCase();
    if (!allowedExtensions.includes(extension)) {
        errorElement(input, `ファイル形式は PNG または JPEG のみ許可されています`);
    }
}

/**
 * フィールドのエラー削除関数（リアル・PHP両対応）
 */
function clearFieldError(field) {
    // 郵便番号特有のサーバーエラー削除
    if (field.name === "postal_code") {
        const placeholder = document.querySelector(".postal-error-placeholder");
        if (placeholder) placeholder.innerHTML = "";

        const serverError = document.querySelector(".error-msg2");
        if (serverError && serverError.textContent.includes("郵便番号")) {
            serverError.remove();
        }
    }

    // 隣接ノードのエラー削除
    let next = field.nextSibling;
    while (next) {
        if (next.nodeType === 1 && (
            next.classList.contains("error") ||
            next.classList.contains("error-msg") ||
            next.classList.contains("error-msg2")
        )) {
            let toRemove = next;
            next = next.nextSibling;
            toRemove.remove();
        } else {
            break;
        }
    }

    // 親要素内のサーバーサイドエラーも削除
    const parent = field.closest("div") || field.parentNode;
    const serverErrors = parent.querySelectorAll(".error-msg, .error-msg2");
    serverErrors.forEach(function (el) {
        el.remove();
    });

    field.classList.remove("error-form");
}

/**
 * エラーメッセージ表示関数
 */
function errorElement(form, msg) {
    form.className = "error-form";
    var newElement = document.createElement("div");
    newElement.className = "error-msg";
    newElement.textContent = msg;

    // 郵便番号だけ placeholder に表示
    if (form.name === "postal_code") {
        const placeholder = document.querySelector(".postal-error-placeholder");
        if (placeholder) {
            placeholder.innerHTML = "";
            placeholder.appendChild(newElement);
            return;
        }
    }

    form.parentNode.insertBefore(newElement, form.nextSibling);
}

// バリデーション関数群
function validateMail(val) {
    return /^[A-Za-z0-9]{1}[A-Za-z0-9_.-]*@[A-Za-z0-9_.-]+\.[A-Za-z0-9]+$/.test(val);
}

function validateTel(val) {
    return /^[0-9]{2,4}-[0-9]{2,4}-[0-9]{3,4}$/.test(val);
}

function validateKana(val) {
    return /^[ぁ-んー\s　]+$/.test(val);
}

function validatePostalCode(val) {
    return /^[0-9]{3}-?[0-9]{4}$/.test(val);
}
