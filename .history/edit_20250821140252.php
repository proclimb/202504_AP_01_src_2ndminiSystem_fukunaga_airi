<?php

/**
 * 更新・削除画面（edit.php）
 * - admin: ?id=xxx で任意ユーザを編集可能
 * - user : 自分の情報のみ編集
 * - 初回表示: DBの値をフォームにセット
 * - POST   : バリデーション通過で update.php へ
 */


// ★ 開発中だけ有効化（原因特定用）
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once 'Db.php';
require_once 'User.php';
require_once 'Validator.php';

session_cache_limiter('none');
session_start();

// --- 認可ガード ---
if (empty($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

// ★ PDO を必ず初期化（Db.php の実装に合わせて修正）
// ← ここを確実に通す
$user = new User($pdo);

$error_message = [];
$isAdmin = ($_SESSION['role'] === 'admin');

// --- 1) 編集対象IDの決定 ---
if ($isAdmin && isset($_GET['id'])) {
    $targetId = (int)$_GET['id'];
} else {
    $targetId = $_SESSION['user_id'] ?? null;
    if (!$targetId) {
        header('Location: login.php');
        exit;
    }
}

// --- 2) 対象ユーザー取得（例外を見たいので try/catch） ---
try {
    $userData = $user->findById($targetId);
} catch (Throwable $e) {
    http_response_code(500);
    exit('DBエラー: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
}

if (!$userData) {
    http_response_code(404);
    exit('該当ユーザーが見つかりません。');
}

// --- 3) フォーム値（初回はDB値、POST時は入力を反映） ---
$form = $userData;

// --- 4) POST時の処理 ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 画面入力で上書き（未入力は既存値維持）
    $form = array_merge($userData, $_POST);

    // Validator 互換のため birth_date を分解
    if (
        !empty($form['birth_date']) &&
        preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $form['birth_date'], $m)
    ) {
        $form['birth_year']  = $m[1];
        $form['birth_month'] = $m[2];
        $form['birth_day']   = $m[3];
    }

    // id を必ず持たせる
    $form['id'] = $targetId;

    $validator = new Validator($pdo);
    if ($validator->validate($form)) {
        $_SESSION['edit_data'] = $form;
        header('Location: update.php');
        exit;
    } else {
        $error_message = $validator->getErrors();
        if (!isset($form['gender'])) {
            $form['gender'] = '1';
        }
    }
}

// 既存HTMLが $_POST[...] を参照しているため合わせる
$_POST = $form;
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

    <!-- ★ フォームは1つだけに統一 -->
    <form id="edit-form" action="edit.php<?= $isAdmin && isset($_GET['id']) ? '?id=' . (int)$_GET['id'] : '' ?>"
        method="post" enctype="multipart/form-data" onsubmit="return validate();" name="edit">

        <input type="hidden" name="id" value="<?= htmlspecialchars($_POST['id'] ?? '', ENT_QUOTES) ?>">

        <h1 class="contact-title">更新内容入力</h1>
        <p>更新内容をご入力の上、「更新」ボタンをクリックしてください。</p>
        <p>削除する場合は「削除」ボタンをクリックしてください。</p>

        <div>
            <div>
                <label>お名前<span>必須</span></label>
                <input type="text" name="name" placeholder="例）山田太郎"
                    value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES) ?>">
                <?php if (isset($error_message['name'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($error_message['name'], ENT_QUOTES) ?></div>
                <?php endif ?>
            </div>

            <div>
                <label>ふりがな<span>必須</span></label>
                <input type="text" name="kana" placeholder="例）やまだたろう"
                    value="<?= htmlspecialchars($_POST['kana'] ?? '', ENT_QUOTES) ?>">
                <?php if (isset($error_message['kana'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($error_message['kana'], ENT_QUOTES) ?></div>
                <?php endif ?>
            </div>

            <div>
                <label>性別<span>必須</span></label>
                <?php $gender = $_POST['gender'] ?? '1'; ?>
                <label class="gender">
                    <input type="radio" name="gender" value="1" <?= $gender === '1' ? 'checked' : '' ?>>男性
                </label>
                <label class="gender">
                    <input type="radio" name="gender" value="2" <?= $gender === '2' ? 'checked' : '' ?>>女性
                </label>
                <label class="gender">
                    <input type="radio" name="gender" value="3" <?= $gender === '3' ? 'checked' : '' ?>>その他
                </label>
            </div>

            <div>
                <label>生年月日<span>必須</span></label>
                <input type="text" name="birth_date"
                    value="<?= htmlspecialchars($_POST['birth_date'] ?? '', ENT_QUOTES) ?>"
                    readonly class="readonly-field">
            </div>

            <div>
                <label>郵便番号<span>必須</span></label>
                <div class="postal-row">
                    <input class="half-width" type="text" name="postal_code" id="postal_code"
                        placeholder="例）100-0001"
                        value="<?= htmlspecialchars($_POST['postal_code'] ?? '', ENT_QUOTES) ?>">
                    <button type="button" class="postal-code-search" id="searchAddressBtn">住所検索</button>
                </div>
                <?php if (isset($error_message['postal_code'])): ?>
                    <div class="error-msg2"><?= htmlspecialchars($error_message['postal_code'], ENT_QUOTES) ?></div>
                <?php endif ?>
            </div>

            <div>
                <label>住所<span>必須</span></label>
                <input type="text" name="prefecture" id="prefecture" placeholder="都道府県"
                    value="<?= htmlspecialchars($_POST['prefecture'] ?? '', ENT_QUOTES) ?>">
                <input type="text" name="city_town" id="city_town" placeholder="市区町村・番地"
                    value="<?= htmlspecialchars($_POST['city_town'] ?? '', ENT_QUOTES) ?>">
                <input type="text" name="building" placeholder="建物名・部屋番号  **省略可**"
                    value="<?= htmlspecialchars($_POST['building'] ?? '', ENT_QUOTES) ?>">
                <?php if (isset($error_message['address'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($error_message['address'], ENT_QUOTES) ?></div>
                <?php endif ?>
            </div>

            <div>
                <label>電話番号<span>必須</span></label>
                <input type="text" name="tel" placeholder="例）000-000-0000"
                    value="<?= htmlspecialchars($_POST['tel'] ?? '', ENT_QUOTES) ?>">
                <?php if (isset($error_message['tel'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($error_message['tel'], ENT_QUOTES) ?></div>
                <?php endif ?>
            </div>

            <div>
                <label>メールアドレス<span>必須</span></label>
                <input type="text" name="email" placeholder="例）guest@example.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>">
            </div>

            <div>
                <label>本人確認書類（表）</label>
                <input type="file" name="document1" id="document1" accept="image/png, image/jpeg, image/jpg">
                <span id="filename1" class="filename-display"></span>
                <div class="preview-container">
                    <img id="preview1" src="#" alt="プレビュー画像１" style="display:none; max-width:200px; margin-top:8px;">
                </div>
            </div>

            <div>
                <label>本人確認書類（裏）</label>
                <input type="file" name="document2" id="document2" accept="image/png, image/jpeg, image/jpg">
                <span id="filename2" class="filename-display"></span>
                <div class="preview-container">
                    <img id="preview2" src="#" alt="プレビュー画像２" style="display:none; max-width:200px; margin-top:8px;">
                </div>
            </div>
        </div>

        <div class="btn-group">
            <!-- 更新ボタン -->
            <button type="submit" class="flip-button">
                <div class="inner">
                    <div class="face front">更新</div>
                    <div class="face back"></div>
                </div>
            </button>

            <!-- 削除ボタン -->
            <button type="submit" class="flip-button flip-button-delete"
                formaction="delete.php" formmethod="post"
                onclick="return confirm('本当に削除しますか？');">
                <div class="inner">
                    <div class="face front">削除</div>
                    <div class="face back"></div>
                </div>
            </button>

            <!-- 管理者のみ：ダッシュボード戻る -->
            <?php if ($isAdmin): ?>
                <button type="button" class="flip-button flip-button-back"
                    onclick="location.href='dashboard.php'">
                    <div class="inner">
                        <div class="face front">ダッシュボードに戻る</div>
                        <div class="face back"></div>
                    </div>
                </button>
            <?php endif; ?>
            <?php if (!$isAdmin): ?>
                <!-- 全員共通：TOPへ（必要なら） -->
                <a href="index.php" class="flip-button flip-button-home" style="text-decoration:none;">
                    <div class="inner">
                        <div class="face front">TOPに戻る</div>
                        <div class="face back"></div>
                    </div>
                </a>
            <?php endif; ?>
        </div>
    </form>
</body>

</html>