window.addEventListener('load', function () {
    // テキスト入力のバリデーション
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

    const value = document.edit.kana.value;
    if (value === "") {
        errorElement(document.edit.kana, "ふりがなが入力されていません");
    } else if (!validateKana(value)) {
        errorElement(document.edit.kana, "ひらがなで入力してください");
    }
}

/**
 * メールのリアルタイムバリデーション
 */
function validateEmailField() {
    clearFieldError(document.edit.email);

    const value = document.edit.email.value;
    if (value === "") {
        errorElement(document.edit.email, "メールアドレスが入力されていません");
    } else if (!validateMail(value)) {
        errorElement(document.edit.email, "有効なメールアドレスを入力してください");
    }
}

/**
 * 電話番号のリアルタイムバリデーション
 */
function validateTelField() {
    clearFieldError(document.edit.tel);

    const value = document.edit.tel.value;
    if (value === "") {
        errorElement(document.edit.tel, "電話番号が入力されていません");
    } else if (!validateTel(value)) {
        errorElement(document.edit.tel, "電話番号は12~13桁で正しく入力してください");
    }
}

/**
 * 郵便番号のリアルタイムバリデーション
 */
function validatePostalCodeField() {
    clearFieldError(document.edit.postal_code);

    const value = document.edit.postal_code.value;
    if (value === "") {
        errorElement(document.edit.postal_code, "郵便番号が入力されていません");
    } else if (!validatePostalCode(value)) {
        errorElement(document.edit.postal_code, "郵便番号はXXX-XXXXの形式で入力してください");
    }
}

/**
 * ファイルの形式チェック
 */
function validateFileField(input, label) {
    clearFieldError(input);

    const file = input.files[0];
    if (!file) return;

    const allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    const fileName = file.name;
    const extension = fileName.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(extension)) {
        errorElement(input, `${label}は jpg / jpeg / png のいずれかでアップロードしてください。`);
    }
}

/**
 * 該当フィールドのエラーのみ削除する関数
 */
function clearFieldError(field) {
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

    const parent = field.parentNode;
    const serverErrors = parent.querySelectorAll(".error-msg, .error-msg2");
    serverErrors.forEach(function (el) {
        el.remove();
    });

    field.classList.remove("error-form");

    // postal_code専用：placeholderがあれば消す
    if (field.name === "postal_code") {
        const placeholder = document.querySelector(".postal-error-placeholder");
        if (placeholder) placeholder.innerHTML = "";
    }
}

/**
 * 共通関数（修正あり）
 */
var errorElement = function (form, msg) {
    form.className = "error-form";
    var newElement = document.createElement("div");
    newElement.className = "error-msg";
    var newText = document.createTextNode(msg);
    newElement.appendChild(newText);

    // 郵便番号用だけ専用の場所に表示
    if (form.name === "postal_code") {
        const placeholder = document.querySelector(".postal-error-placeholder");
        if (placeholder) {
            placeholder.innerHTML = "";
            placeholder.appendChild(newElement);
            return;
        }
    }

    form.parentNode.insertBefore(newElement, form.nextSibling);
};

/**
 * バリデーション関数群
 */
var removeElementsByClass = function (className) {
    var elements = document.getElementsByClassName(className);
    while (elements.length > 0) {
        elements[0].parentNode.removeChild(elements[0]);
    }
};

var removeClass = function (className) {
    var elements = Array.from(document.getElementsByClassName(className));
    elements.forEach(function (el) {
        el.classList.remove(className);
    });
};

var validateMail = function (val) {
    return /^[A-Za-z0-9]{1}[A-Za-z0-9_.-]*@[A-Za-z0-9_.-]+\.[A-Za-z0-9]+$/.test(val);
};

var validateTel = function (val) {
    return /^[0-9]{2,4}-[0-9]{2,4}-[0-9]{3,4}$/.test(val);
};

var validateKana = function (val) {
    return /^[ぁ-んー\s　]+$/.test(val);
};

var validatePostalCode = function (val) {
    return /^[0-9]{3}-[0-9]{4}$/.test(val);
};
