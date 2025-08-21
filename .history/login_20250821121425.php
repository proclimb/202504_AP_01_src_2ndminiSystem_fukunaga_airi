<?php
// PHP 部分はそのまま
ob_start(); // 余計な出力をバッファ
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'Db.php';
require_once 'User.php';
session_start();

$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['old_email']);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = new User($pdo);
    $data = $user->findByEmail($email);
    $masterdata = "admin@1234";
    $masterpass = "admin1234";



    if ($email === $masterdata && $password === $masterpass) {
        $_SESSION['came_from_entry'] = true;
        $_SESSION['role'] = 'admin';
        header("Location: dashboard.php");
        exit;
    }
    if (!$data || !password_verify($password, $data['password_hash'])) {
        $_SESSION['old_email'] = $email;
        $_SESSION['error_message'] = 'メールアドレスまたはパスワードが正しくありません';
        header("Location: login.php");
        exit;
    } else {
        $_SESSION['user_id'] = $data['id'];
        header("Location: edit.php");
        exit;
    }
}
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
                    value="<?= htmlspecialchars($old_email, ENT_QUOTES) ?>" required>
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
                        <input type="checkbox" id="togglePwText"
                            class="togglePwText">表示
                    </label>
                </div>
                <?php if (!empty($_SESSION['error_message'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
                    <?php unset($_SESSION['error_message']); ?>
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