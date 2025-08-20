<?php
// セッション開始〜データ取得ロジック
session_cache_limiter('none');
session_start();
if (!isset($_SESSION['input_data'])) {
    header('Location: input.php');
    exit();
}
$_POST = $_SESSION['input_data'];
$hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
session_destroy();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>確認画面</title>
    <link rel="stylesheet" href="style_new.css">
</head>

<body>
    <!-- ヘッダー -->
    <h1 class="page-title">
        <a href="index.php">mini System</a>
    </h1>
    <!-- ページサブタイトル -->
    <div>
        <h2 class="page-subtitle">確認画面</h2>
    </div>

    <!-- ★ フォーム＆ボタンをまとめた一つのカードパネル -->
    <div class="confirm-panel">
        <form id="confirm-form" action="submit.php" method="post">
            <!-- 隠しフィールド -->
            <?php foreach (
                [
                    'name',
                    'kana',
                    'gender',
                    'birth_year',
                    'birth_month',
                    'birth_day',
                    'postal_code',
                    'prefecture',
                    'city_town',
                    'building',
                    'tel',
                    'email'
                ] as $key
            ): ?>
                <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($_POST[$key]) ?>">
            <?php endforeach; ?>

            <h1 class="contact-title">登録内容確認</h1>
            <div class="confirm-container">
                <p>以下の内容でよろしければ「登録する」をクリックしてください。</p>

                <div><label>お名前</label>
                    <p><?= htmlspecialchars($_POST['name']) ?></p>
                </div>
                <div><label>ふりがな</label>
                    <p><?= htmlspecialchars($_POST['kana']) ?></p>
                </div>
                <div><label>性別</label>
                    <p>
                        <?= $_POST['gender'] === '1'
                            ? '男性'
                            : ($_POST['gender'] === '2' ? '女性' : 'その他') ?>
                    </p>
                </div>
                <div><label>生年月日</label>
                    <p><?= htmlspecialchars("{$_POST['birth_year']}年 {$_POST['birth_month']}月 {$_POST['birth_day']}日") ?></p>
                </div>
                <div><label>郵便番号</label>
                    <p><?= htmlspecialchars("〒{$_POST['postal_code']}") ?></p>
                </div>
                <div><label>住所</label>
                    <p><?= htmlspecialchars("{$_POST['prefecture']}{$_POST['city_town']}{$_POST['building']}") ?></p>
                </div>
                <div><label>電話番号</label>
                    <p><?= htmlspecialchars($_POST['tel']) ?></p>
                </div>
                <div><label>メールアドレス</label>
                    <p><?= htmlspecialchars($_POST['email']) ?></p>
                </div>
                <div>
                    <p><input type="hidden" name="password_hash" value="<?= htmlspecialchars($hashedPassword) ?>"></p>
                </div>
            </div>

            <!-- ★ 同じカード内にボタン群を配置 -->
            <div class="btn-area">
                <button type="submit" class="flip-button flip-button-register">
                    <div class="inner">
                        <div class="face front">登録</div>
                        <div class="face back"></div>
                    </div>
                </button>

                <button type="button" class="flip-button flip-button-back" onclick="history.back()">
                    <div class="inner">
                        <div class="face front">内容を修正する</div>
                        <div class="face back"></div>
                    </div>
                </button>
            </div>
        </form>
    </div>
    <!-- /.confirm-panel -->
</body>

</html>