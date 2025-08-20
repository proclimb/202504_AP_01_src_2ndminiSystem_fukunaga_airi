<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mini System</title>
    <style>
        /* ベースリセット */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: #000;
            font-family: 'Open Sans', sans-serif;
            color: #fff;
        }

        /* ヘッダー */
        .top-bar {
            position: relative;
            background: #000;
            height: 80px;
            display: flex;
            align-items: center;
            padding: 0 40px;
        }

        .site-logo {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
        }

        .custom-select-wrapper {
            margin-left: auto;
            width: 200px;
        }

        .custom-select-wrapper select {
            width: 100%;
            height: 40px;
            font-size: 1rem;
            border-radius: 4px;
            border: 1px solid #333;
            background: #111;
            color: #fff;
        }

        /* コンテンツ */
        .container {
            max-width: 600px;
            margin: 40px auto;
            text-align: center;
        }

        .item {
            margin: 30px 0;
        }

        .item button {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            font-weight: 500;
            position: relative;
            padding: 8px 0;
            cursor: pointer;
        }

        .item button::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            height: 3px;
            width: 0;
            background: #fff;
            transition: width .4s ease;
        }

        .item button:hover::after {
            width: 100%;
        }

        /* ─── ハンバーガーメニュー ─── */
        .menu-toggle {
            display: none;
        }

        .hamburger {
            margin-left: auto;
            width: 30px;
            height: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            cursor: pointer;
        }

        .hamburger span {
            display: block;
            height: 4px;
            background: #fff;
            border-radius: 2px;
            transition: transform .3s, opacity .3s;
        }

        /* メニュー本体をデフォルトで隠す */
        .menu {
            position: absolute;
            top: 80px;
            right: 40px;
            background: none;
            border: none;
            box-shadow: none;
            padding: 0;

            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity .3s ease, transform .3s ease, visibility .3s;
        }

        .menu a {
            display: block;
            padding: 12px 20px;
            color: #fff;
            text-decoration: none;
            white-space: nowrap;
            font-size: 1.2rem;
        }

        .menu a:hover {
            background: #222;
        }

        /* チェックオンでアイコンとメニューを切り替え */
        .menu-toggle:checked+.hamburger span:nth-child(1) {
            transform: translateY(10px) rotate(45deg);
        }

        .menu-toggle:checked+.hamburger span:nth-child(2) {
            opacity: 0;
        }

        .menu-toggle:checked+.hamburger span:nth-child(3) {
            transform: translateY(-10px) rotate(-45deg);
        }

        .menu-toggle:checked~.menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
    </style>
</head>

<body>

    <!-- ヘッダー -->
    <header class="top-bar">
        <h1 class="site-logo">Mini System</h1>
        <!-- プルダウントグル（ハンバーガーアイコン） -->
        <input type="checkbox" id="menu-toggle" class="menu-toggle">
        <label for="menu-toggle" class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </label>

        <!-- メニュー内容 -->
        <nav class="menu">
            <a href="input.php">登録画面</a>
            <a href="dashboard.php">ダッシュボード</a>
            <a href="Csvpreview.php">住所マスタ更新</a>
        </nav>
    </header>
    </header>

    <!-- ボタン -->
    <div class="container">
        <div class="item">
            <form action="input.php" method="post">
                <button type="submit">登録画面</button>
            </form>
        </div>
        <div class="item">
            <form action="dashboard.php" method="post">
                <button type="submit">ダッシュボード</button>
            </form>
        </div>
        <div class="item">
            <form action="Csvpreview.php" method="post">
                <button type="submit">住所マスタ更新</button>
            </form>
        </div>
        <div class="item">
            <form action="login.php" method="post">
                <button type="submit">ログイン</button>
            </form>
        </div>
    </div>



</body>

</html>