<?php

/**
 * ダッシュボード画面
 *
 * ** ダッシュボード画面は、TOPから遷移してきます
 * ** ダッシュボードで行う処理は以下です
 * ** 1. DB接続情報、クラス定義を読み込む
 * ** 2. ユーザー情報を取得
 * ** 3. HTML を描画（検索フォーム・テーブル・ページネーション・TOPへ戻る）
 */

require_once 'Db.php';
require_once 'User.php';
require_once 'Sort.php';      // sortLink() 定義
require_once 'Page.php';      // paginationLinks() 定義

// ---------------------------------------------
// 1. リクエストパラメータ取得・初期化
// ---------------------------------------------
$nameKeyword = '';
$sortBy      = $sortBy  ?? null;  // sort.php でセット済み
$sortOrd     = $sortOrd ?? 'asc'; // sort.php でセット済み
$page        = $page    ?? 1;     // page.php でセット済み

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search_submit'])) {
    $nameKeyword = trim($_GET['search_name'] ?? '');
    $sortBy  = null;
    $sortOrd = 'asc';
    $page    = 1;
} else {
    $nameKeyword = trim($_GET['search_name'] ?? '');
}

// ---------------------------------------------
// 2. 総件数取得・ページネーション
// ---------------------------------------------
$userModel  = new User($pdo);
$totalCount = $userModel->countUsersWithKeyword($nameKeyword);

$limit = 10;
list($page, $offset, $totalPages) = getPaginationParams($totalCount, $limit);

// ---------------------------------------------
// 3. 一覧取得
// ---------------------------------------------
$users = $userModel->fetchUsersWithKeyword(
    $nameKeyword,
    $sortBy,
    $sortOrd,
    $offset,
    $limit
);

// ---------------------------------------------
// 4. HTML 出力
// ---------------------------------------------
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>mini System</title>
    <link rel="stylesheet" href="style_new.css">
</head>

<body class="dashboard-page">

    <h1 class="page-title">
        <a href="index.php">mini System</a>
    </h1>


    <div>
        <h2 class="page-subtitle">ダッシュボード</h2>
    </div>

    <section class="dashboard-panel">


        <!-- 検索フォーム -->
        <form method="get" action="dashboard.php" class="name-search-form">
            <label for="search_name">名前で検索：</label>
            <input
                type="text"
                name="search_name"
                id="search_name"
                value="<?= htmlspecialchars($nameKeyword, ENT_QUOTES) ?>"
                placeholder="名前の一部を入力">
            <button type="submit" name="search_submit" class="flip-button flip-button-search">
                <div class="inner">
                    <div class="face front">検索</div>
                    <div class="face back"></div>
            </button>
        </form>

        <!-- 件数表示 -->
        <div class="result-count">
            検索結果：<strong><?= $totalCount ?></strong> 件
        </div>

        <!-- テーブル -->
        <table class="common-table">
            <thead>
                <tr>
                    <th>編集</th>
                    <th>名前</th>
                    <th><?= sortLink('kana', 'ふりがな', $sortBy, $sortOrd, $nameKeyword) ?></th>
                    <th>性別</th>
                    <th>生年月日</th>
                    <th><?= sortLink('postal_code', '郵便番号', $sortBy, $sortOrd, $nameKeyword) ?></th>
                    <th>住所</th>
                    <th>電話番号</th>
                    <th><?= sortLink('email', 'メールアドレス', $sortBy, $sortOrd, $nameKeyword) ?></th>
                    <th>画像①</th>
                    <th>画像②</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr>
                        <td colspan="11" class="no-data">該当するデータがありません。</td>
                    </tr>
                    <?php else: foreach ($users as $val): ?>
                        <tr>
                            <td>
                                <a href="edit.php?id=<?= urlencode($val['id']) ?>"
                                    class="edit-btn">
                                    <i class="fas fa-edit"></i> 編集
                                </a>
                            </td>
                            <td><?= htmlspecialchars($val['name'], ENT_QUOTES) ?></td>
                            <td><?= htmlspecialchars($val['kana'], ENT_QUOTES) ?></td>
                            <td><?= $val['gender_flag'] === '1' ? '男性' : ($val['gender_flag'] === '2' ? '女性' : '未回答') ?></td>
                            <td><?= date('Y年n月j日', strtotime($val['birth_date'])) ?></td>
                            <td><?= htmlspecialchars($val['postal_code']) ?></td>
                            <td><?= htmlspecialchars($val['prefecture'] . $val['city_town'] . $val['building']) ?></td>
                            <td><?= htmlspecialchars($val['tel']) ?></td>
                            <td><?= htmlspecialchars($val['email']) ?></td>
                            <td>
                                <?= (int)$val['has_front'] === 1
                                    ? "<a class=\"dl-link\" href=\"Showdocument.php?user_id=" . urlencode($val['id']) . "&type=front\" target=\"_blank\">DL</a>"
                                    : "無し" ?>
                            </td>
                            <td>
                                <?= (int)$val['has_back'] === 1
                                    ? "<a class=\"dl-link\" href=\"Showdocument.php?user_id=" . urlencode($val['id']) . "&type=back\" target=\"_blank\">DL</a>"
                                    : "無し" ?>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>

        <!-- ページネーション -->
        <div class="pagination">
            <?= paginationLinks($page, $totalPages, $nameKeyword, $sortBy, $sortOrd) ?>
        </div>

        <!-- TOPへ戻る -->
        <a href="index.php">
            <button class="flip-button flip-button-home">
                <div class="inner">
                    <div class="face front">TOPに戻る</div>
                    <div class="face back"></div> <!-- ここにアイコンが入る -->
                </div>
            </button>
        </a>
    </section>
</body>

</html>