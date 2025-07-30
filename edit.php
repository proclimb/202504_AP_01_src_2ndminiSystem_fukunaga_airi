<?php

/**
 * 更新・削除画面
 */



require_once 'Db.php';
require_once 'User.php';
require_once 'Validator.php';

session_cache_limiter('none');
session_start();

if (!empty($_POST)) {
    if (!empty($_POST['birth_date']) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $_POST['birth_date'], $m)) {
        $_POST['birth_year'] = $m[1];
        $_POST['birth_month'] = $m[2];
        $_POST['birth_day'] = $m[3];
    }

    $validator = new Validator($pdo);
    $isValid = $validator->validate($_POST);

    if ($isValid) {
        $_SESSION['input_data'] = $_POST;
        header('Location:update.php');
        exit();
    } else {
        $error_message = $validator->getErrors();
        $error_message_files = [
            'document1' => $error_message['document1'] ?? null,
            'document2' => $error_message['document2'] ?? null,
        ];

        if (!isset($_POST['gender_flag'])) {
            $_POST['gender_flag'] = '1';
        }
    }
} else {
    $id = $_GET['id'];
    $user = new User($pdo);
    $_POST = $user->findById($id);
}


?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mini System</title>
    <link rel="stylesheet" href="style_new.css">
    <script>
        function checkPostalFormat(input) {
            const value = input.value.trim();
            const errBox = document.getElementById('js-postal-error');
            const format1 = /^\d{3}-\d{4}$/;
            const format2 = /^\d{7}$/;

            if (value === '') {
                errBox.textContent = '';
                return;
            }

            if (!format1.test(value) && !format2.test(value)) {
                errBox.textContent = '郵便番号はXXX-XXXX または XXXXXXX の形式で入力してください';
            } else {
                errBox.textContent = '';
            }
        }
    </script>
    <script src="postalcodesearch.js"></script>
    <script src="contact.js"></script>
</head>

<body>
    <div>
        <h1>mini System</h1>
    </div>
    <div>
        <h2>更新・削除画面</h2>
    </div>
    <div>
        <form action="edit.php" method="post" enctype="multipart/form-data" onsubmit="return validate();" name="edit">
            <input type="hidden" name="id" value="<?php echo $_POST['id'] ?>">
            <h1 class="contact-title">更新内容入力</h1>
            <p>更新内容をご入力の上、「更新」ボタンをクリックしてください。</p>
            <p>削除する場合は「削除」ボタンをクリックしてください。</p>
            <div>
                <div>
                    <label>お名前<span>必須</span></label>
                    <input type="text" name="name" placeholder="例）山田太郎" value="<?= htmlspecialchars($_POST['name']) ?>">
                    <?php if (isset($error_message['name'])) : ?>
                        <div class="error-msg"><?= htmlspecialchars($error_message['name']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>ふりがな<span>必須</span></label>
                    <input type="text" name="kana" placeholder="例）やまだたろう" value="<?= htmlspecialchars($_POST['kana']) ?>">
                    <?php if (isset($error_message['kana'])) : ?>
                        <div class="error-msg"><?= htmlspecialchars($error_message['kana']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>性別<span>必須</span></label>
                    <?php $gender = $_POST['gender_flag'] ?? '1'; ?>
                    <label class="gender"><input type="radio" name="gender" value='1' <?= $gender == '1' ? 'checked' : '' ?>>男性</label>
                    <label class="gender"><input type="radio" name="gender" value='2' <?= $gender == '2' ? 'checked' : '' ?>>女性</label>
                    <label class="gender"><input type="radio" name="gender" value='3' <?= $gender == '3' ? 'checked' : '' ?>>その他</label>
                </div>
                <div>
                    <label>生年月日<span>必須</span></label>
                    <input type="text" name="birth_date" value="<?php echo $_POST['birth_date'] ?>" readonly class="readonly-field">
                </div>
                <div>
                    <label>郵便番号<span>必須</span></label>
                    <div class="postal-row">
                        <input type="text" name="postal_code" id="postal_code" placeholder="例）100-0001" value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>" oninput="checkPostalFormat(this)">
                        <button type="button" class="postal-code-search" id="searchAddressBtn">住所検索</button>
                    </div>
                    <div class="postal-error-placeholder" id="js-postal-error"></div>
                    <?php if (isset($error_message['postal_code'])) : ?>
                        <div class="error-msg2 server-error"><?= htmlspecialchars($error_message['postal_code']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>住所<span>必須</span></label>
                    <input type="text" name="prefecture" id="prefecture" placeholder="都道府県" value="<?= htmlspecialchars($_POST['prefecture'] ?? '') ?>">
                    <input type="text" name="city_town" id="city_town" placeholder="市区町村・番地" value="<?= htmlspecialchars($_POST['city_town'] ?? '') ?>">
                    <input type="text" name="building" placeholder="建物名・部屋番号  **省略可**" value="<?= htmlspecialchars($_POST['building'] ?? '') ?>">
                    <?php if (isset($error_message['address'])) : ?>
                        <div class="error-msg"><?= htmlspecialchars($error_message['address']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>電話番号<span>必須</span></label>
                    <input type="text" name="tel" placeholder="例）000-000-0000" value="<?= htmlspecialchars($_POST['tel']) ?>">
                    <?php if (isset($error_message['tel'])) : ?>
                        <div class="error-msg"><?= htmlspecialchars($error_message['tel']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>メールアドレス<span>必須</span></label>
                    <input type="text" name="email" placeholder="例）guest@example.com" value="<?= htmlspecialchars($_POST['email']) ?>">
                    <?php if (isset($error_message['email'])) : ?>
                        <div class="error-msg"><?= htmlspecialchars($error_message['email']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>本人確認書類（表）</label>
                    <input
                        type="file"
                        name="document1"
                        id="document1"
                        accept="image/png, image/jpeg, image/jpg">
                    <span id="filename1" class="filename-display"></span>
                    <div class="preview-container">
                        <img id="preview1" src="#" alt="プレビュー画像１" style="display: none; max-width: 200px; margin-top: 8px;">
                    </div>
                    <!-- エラー時はドキュメントを保持せず、破棄する -->
                    <?php if (isset($error_message_files['document1'])) : ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error_message_files['document1']) ?></div>
                    <?php endif ?>
                </div>

                <div>
                    <label>本人確認書類（裏）</label>
                    <input
                        type="file"
                        name="document2"
                        id="document2"
                        accept="image/png, image/jpeg, image/jpg">
                    <span id="filename2" class="filename-display"></span>
                    <div class="preview-container">
                        <img id="preview2" src="#" alt="プレビュー画像２" style="display: none; max-width: 200px; margin-top: 8px;">
                    </div>
                    <!-- エラー時はドキュメントを保持せず、破棄する -->
                    <?php if (isset($error_message_files['document2'])) : ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error_message_files['document2']) ?></div>
                    <?php endif ?>

                </div>
            </div>
            <button type="submit">更新</button>
            <a href="dashboard.php"><input type="button" value="ダッシュボードに戻る"></a>
        </form>
        <form action="delete.php" method="post" name="delete">
            <input type="hidden" name="id" value="<?php echo $_POST['id'] ?>">
            <button type="submit">削除</button>
        </form>
    </div>
</body>

</html>