<?php
session_start();
require_once __DIR__ . '/functions/db.php';
require_once __DIR__ . '/functions/validator.php';

// ログインしていなければログイン画面へ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// DBからユーザー情報を取得
$stmt = $pdo->prepare("SELECT id, name, email, postal_code, address FROM users WHERE id = :id");
$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "ユーザー情報が見つかりません";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>マイページ編集</title>
    <script src="js/validator.js" defer></script>
</head>

<body>
    <h1>マイページ編集</h1>
    <form action="update.php" method="post" id="editForm" novalidate>
        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>">

        <label>名前:
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </label><br>

        <label>メールアドレス:
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </label><br>

        <label>郵便番号:
            <input type="text" name="postal_code" value="<?= htmlspecialchars($user['postal_code']) ?>" required>
        </label><br>

        <label>住所:
            <input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
        </label><br>

        <button type="submit">更新</button>
    </form>

    <?php if (isset($_SESSION['error_message'])): ?>
        <p style="color:red;"><?= $_SESSION['error_message'] ?></p>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</body>

</html>