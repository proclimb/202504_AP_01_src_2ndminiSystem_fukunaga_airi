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

// 2.ダッシュボードから送信した変数を設定
$id = $_GET['id'];

// 3-1.Userクラスをインスタンス化
$user = new User($pdo);

// 3-2.UserクラスのfindById()メソッドで1件検索
$_POST = $user->findById($id);

// 4.html の描画
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

    <div class="form-container">
        <form action="login.php" method="post" name="login">
            <div>
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">パスワード</label>
                <input type="password" id="password" name="password" required></label>
                <label class="show-pw">
                    <input type="checkbox" id="togglePw"> 表示
                </label>
            </div>
    </div>
    <button type="submit">ログイン</button>
    </form>
    <script src="pass.js"></script>
</body>

</html>