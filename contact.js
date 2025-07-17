window.addEventListener('load', function () {
    document.edit.name.addEventListener('input', validateNameField);
    document.edit.kana.addEventListener('input', validateKanaField);
    document.edit.email.addEventListener('input', validateEmailField);
    document.edit.tel.addEventListener('input', validateTelField);
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
 * 該当フィールドのエラーのみ削除する関数
 */
function clearFieldError(field) {
    // フィールド直後のすべてのエラーメッセージを削除
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
    // エラースタイル削除
    field.classList.remove("error-form");
}



/**
 * 以下は既存の共通関数（そのまま使用）
 */

var errorElement = function (form, msg) {
    form.className = "error-form";
    var newElement = document.createElement("div");
    newElement.className = "error";
    var newText = document.createTextNode(msg);
    newElement.appendChild(newText);
    form.parentNode.insertBefore(newElement, form.nextSibling);
};

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