<?php
// PHP 部分はそのまま
require_once 'Db.php';
require_once 'User.php';
$id    = $_GET['id'];
$user  = new User($pdo);
$_POST = $user->findById($id);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mini System</title>
    <link rel="stylesheet" href="style_new.css">
</head>

<body>

    <h1 class="page-title">
        <a href="index.php">mini System</a>
    </h1>
    <div>
        <h2 class="page-subtitle">ログイン画面</h2>
    </div>
    <div class="form-container login-form">
        <form action="login.php" method="post" name="login">
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
                    required>
            </div>

            <div class="form-block">
                <label>パスワード<span>必須</span></label>
                <div class="password-wrapper">
                    <input type="password"
                        class="password-input"
                        name="password"
                        id="password"
                        placeholder="ここにパスワードを打ち込んでください">
                    <label class="show-pw">
                        <input type="checkbox" id="togglePw"> 表示
                    </label>
                </div>
                <?php if (isset($error_message['password'])) : ?>
                    <div class="error-msg"><?= htmlspecialchars($error_message['password']) ?></div>
                <?php endif ?>
            </div>


            <div class="form-group button-group">
                <label></label>
                <button type="submit">ログイン</button>
            </div>
        </form>
    </div>

    <script src="pass.js" defer></script>
</body>

</html>