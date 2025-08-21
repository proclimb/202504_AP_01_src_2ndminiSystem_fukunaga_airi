<?php

/**
 * 更新・削除画面
 *
 * ** 更新・削除画面は、ダッシュボード、更新・削除確認の2画面から遷移してきます
 * * **
 * ** 【説明】
 * **   更新・削除では、入力チェックと画面遷移をjavascriptで行います
 * **   そのため、登録の時とは違い、セッションを使用しないパターンのプログラムになります
 * **
 * ** 各画面毎の処理は以下です
 * ** 1.DB接続情報、クラス定義をそれぞれのファイルから読み込む
 * ** 2.DBからユーザ情報を取得する為、$_GETからID情報を取得する
 * ** 3.ユーザ情報を取得する
 * **   1.Userクラスをインスタスタンス化する
 * **     ＊User(設計図)に$user(実体)を付ける
 * **   2.メソッドを実行じユーザー情報を取得する
 * ** 4.html を描画
 */

//  1.DB接続情報、クラス定義の読み込み
require_once 'Db.php';
require_once 'User.php';
require_once 'Validator.php';

session_cache_limiter('none');
session_start();

// ← マイページなので GETのidは使わない


$user = new User($pdo);
$error_message = [];

// POSTが来たらバリデーション → OKなら更新確認や更新処理へ
if (!empty($_POST)) {
    // birth_date を年/月/日に分解（Validator互換）
    if (
        !empty($_POST['birth_date']) &&
        preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $_POST['birth_date'], $m)
    ) {
        $_POST['birth_year']  = $m[1];
        $_POST['birth_month'] = $m[2];
        $_POST['birth_day']   = $m[3];
    }

    $validator = new Validator($pdo);
    if ($validator->validate($_POST)) {
        // セッションやPOSTで値を渡して確認画面へ
        $_SESSION['edit_data'] = $_POST;
        header('Location: update.php');
        exit;
    } else {
        $error_message = $validator->getErrors();
        if (!isset($_POST['gender'])) $_POST['gender'] = '1';
    }
if ($_SESSION['role'] === 'admin' && isset($_GET['id'])) {
    // 管理者が他人を編集
    $id = (int)$_GET['id'];
} else {
    // 一般ユーザーは自分だけ
    $id = $_SESSION['user_id'] ?? null;
    if (!$id) {
        header("Location: login.php");
        exit;
    }
}

$userData = $user->findById($id);
$_POST = $userData; // ← 今のフォームが $_POST を参照してるのでここに流し込む

// 4.html の描画
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mini System</title>
    <link rel="stylesheet" href="style_new.css">
    <link rel="stylesheet" href="form.css">
    <script src="postalcodesearch.js"></script>
    <script src="contact.js"></script>
</head>

<body>
    <h1 class="page-title">
        <a href="index.php">mini System</a>
    </h1>
    <div>
        <h2>更新・削除画面</h2>
    </div>
    <div>
        <form id="edit-form" action="edit.php" method="post" enctype="multipart/form-data" onsubmit="return validate();" name="edit">
            <input type="hidden" name="id" value=" <?php echo $_POST['id'] ?>">
            <h1 class="contact-title">更新内容入力</h1>
            <p>更新内容をご入力の上、「更新」ボタンをクリックしてください。</p>
            <p>削除する場合は「削除」ボタンをクリックしてください。</p>
            <div>
                <div>

                    <label>お名前<span>必須</span></label>
                    <input
                        type="text"
                        name="name"
                        placeholder="例）山田太郎"
                        value="<?= htmlspecialchars($_POST['name']) ?>">
                    <?php if (isset($error_message['name'])) : ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error_message['name']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>ふりがな<span>必須</span></label>
                    <input
                        type="text"
                        name="kana"
                        placeholder="例）やまだたろう"
                        value="<?= htmlspecialchars($_POST['kana']) ?>">
                    <?php if (isset($error_message['kana'])) : ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error_message['kana']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>性別<span>必須</span></label>
                    <?php $gender = $_POST['gender'] ?? '1'; ?>
                    <label class="gender">
                        <input
                            type="radio"
                            name="gender"
                            value='1'
                            <?= ($_POST['gender'] ?? '1') == '1'
                                ? 'checked' : '' ?>>男性</label>
                    <label class="gender">
                        <input
                            type="radio"
                            name="gender"
                            value='2'
                            <?= ($_POST['gender'] ?? '') == '2'
                                ? 'checked' : '' ?>>女性</label>
                    <label class="gender">
                        <input
                            type="radio"
                            name="gender"
                            value='3'
                            <?= ($_POST['gender'] ?? '') == '3'
                                ? 'checked' : '' ?>>その他</label>
                </div>
                <div>
                    <div>
                        <label>生年月日<span>必須</span></label>
                        <input
                            type="text"
                            name="birth_date"
                            value="<?php echo $_POST['birth_date'] ?>"
                            readonly
                            class="readonly-field">
                    </div>
                </div>
                <div>
                    <label>郵便番号<span>必須</span></label>
                    <div class="postal-row">
                        <input
                            class="half-width"
                            type="text"
                            name="postal_code"
                            id="postal_code"
                            placeholder="例）100-0001"
                            value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>">
                        <button type="button"
                            class="postal-code-search"
                            id="searchAddressBtn">住所検索</button>
                    </div>
                    <?php if (isset($error_message['postal_code'])) : ?>
                        <div class="error-msg2">
                            <?= htmlspecialchars($error_message['postal_code']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>住所<span>必須</span></label>
                    <input
                        type="text"
                        name="prefecture"
                        id="prefecture"
                        placeholder="都道府県"
                        value="<?= htmlspecialchars($_POST['prefecture'] ?? '') ?>">
                    <input
                        type="text"
                        name="city_town"
                        id="city_town"
                        placeholder="市区町村・番地"
                        value="<?= htmlspecialchars($_POST['city_town'] ?? '') ?>">
                    <input
                        type="text"
                        name="building"
                        placeholder="建物名・部屋番号  **省略可**"
                        value="<?= htmlspecialchars($_POST['building'] ?? '') ?>">
                    <?php if (isset($error_message['address'])) : ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error_message['address']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>電話番号<span>必須</span></label>
                    <input
                        type="text"
                        name="tel"
                        placeholder="例）000-000-0000"
                        value="<?= htmlspecialchars($_POST['tel']) ?>">
                    <?php if (isset($error_message['tel'])) : ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error_message['tel']) ?></div>
                    <?php endif ?>
                </div>
                <div>
                    <label>メールアドレス<span>必須</span></label>
                    <input
                        type="text"
                        name="email"
                        placeholder="例）guest@example.com"
                        value="<?= htmlspecialchars($_POST['email']) ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($_POST['id'] ?? '') ?>">
                    <?php if (isset($error_message['email'])) : ?>
                        <div class="error-msg">
                            <?= htmlspecialchars($error_message['email']) ?></div>
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
                </div>
            </div>
            <div class="btn-group">
                <!-- 更新ボタン（edit-form の中に入れる場合） -->
                <form id="edit-form" action="edit.php" method="post" enctype="multipart/form-data" onsubmit="return validate();">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($_POST['id'], ENT_QUOTES) ?>">
                    <!-- …各入力フィールド… -->

                    <div class="btn-group">
                        <!-- 更新ボタン：デフォルトの action="edit.php" -->
                        <button type="submit" class="flip-button">
                            <div class="inner">
                                <div class="face front">更新</div>
                                <div class="face back"></div>
                            </div>
                        </button>

                        <!-- 削除ボタン：クリック時だけ action="delete.php" に上書き -->
                        <button
                            type="submit"
                            class="flip-button flip-button-delete"
                            formaction="delete.php"
                            formmethod="post" onclick="return confirm('本当に削除しますか？');">
                            <div class="inner">
                                <div class="face front">削除</div>
                                <div class="face back"></div>
                            </div>
                        </button>

                        <!-- 戻るボタン：フォーム送信ではなく画面遷移 -->
                        <button
                            type="button"
                            class="flip-button flip-button-back"
                            onclick="location.href='dashboard.php'">
                            <div class="inner">
                                <div class="face front">ダッシュボードに戻る</div>
                                <div class="face back"></div>
                            </div>
                        </button>
                    </div>
                </form>


                <!-- 編集フォームここまで -->


</body>

</html>