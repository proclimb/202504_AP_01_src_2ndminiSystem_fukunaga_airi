<?php

class Validator
{
    private $pdo;
    private $error_message = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // バリデーション実行
    public function validate($data)
    {
        $this->error_message = [];

        // 全ての入力値から半角・全角スペースを完全に削除
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // スペース（半角 \s と全角　）をすべて除去
                $data[$key] = preg_replace('/[\s　]+/u', '', $value);
            }
        }

        // 名前
        $name = $data['name'] ?? '';
        if ($name === '') {
            $this->error_message['name'] = '名前が入力されていません';
        } elseif (mb_strlen($name) > 20) {
            $this->error_message['name'] = '名前は20文字以内で入力してください';
        } elseif (!preg_match('/^[ぁ-んァ-ヶー一-龠々ｦ-ﾟー]+$/u', $name)) {
            $this->error_message['name'] = '名前に使用できない文字が含まれています';
        } elseif (preg_match('/[0-9!"#\$%&\'\(\)\*=\+\,\-\.\/\\:;<=>?@\[\]^_`\{|\}~]/u', $name)) {
            $this->error_message['name'] = '名前に使用できない文字が含まれています';
        }

        // ふりがな
        $kana = $data['kana'] ?? '';
        if ($kana === '') {
            $this->error_message['kana'] = 'ふりがなが入力されていません';
        } elseif (preg_match('/[^ぁ-んー]/u', $kana)) {
            $this->error_message['kana'] = 'ひらがなで入力してください';
        } elseif (mb_strlen($kana) > 20) {
            $this->error_message['kana'] = 'ふりがなは20文字以内で入力してください';
        }

        // 生年月日
        if (empty($data['birth_year']) || empty($data['birth_month']) || empty($data['birth_day'])) {
            $this->error_message['birth_date'] = '生年月日が入力されていません';
        } elseif (!$this->isValidDate($data['birth_year'], $data['birth_month'], $data['birth_day'])) {
            $this->error_message['birth_date'] = '生年月日が正しくありません';
        } elseif (strtotime($data['birth_year'] . '-' . $data['birth_month'] . '-' . $data['birth_day']) > strtotime(date('Y-m-d'))) {
            $this->error_message['birth_date'] = '生年月日が未来日になっています。正しい日付を入力してください';
        }

        // 郵便番号
        $postal_code = $data['postal_code'] ?? '';
        if ($postal_code === '') {
            $this->error_message['postal_code'] = '郵便番号が入力されていません';
        } elseif (!preg_match('/^\d{3}-?\d{4}$/', $postal_code)) {
            $this->error_message['postal_code'] = '郵便番号はXXX-XXXX または XXXXXXX の形式で入力してください';
        }

        // 住所
        $prefecture = $data['prefecture'] ?? '';
        $city_town = $data['city_town'] ?? '';
        $building = $data['building'] ?? '';

        if ($prefecture === '' || $city_town === '') {
            $this->error_message['address'] = '住所(都道府県もしくは市区町村・番地)が入力されていません';
        } elseif (mb_strlen($prefecture) > 10) {
            $this->error_message['address'] = '都道府県は10文字以内で入力してください';
        } elseif (mb_strlen($city_town) > 50 || mb_strlen($building) > 50) {
            $this->error_message['address'] = '市区町村・番地もしくは建物名は50文字以内で入力してください';
        }

        // 電話番号
        $tel = $data['tel'] ?? '';
        if ($tel === '') {
            $this->error_message['tel'] = '電話番号が入力されていません';
        } elseif (
            !preg_match('/^0\d{1,4}-\d{1,4}-\d{3,4}$/', $tel) ||
            mb_strlen($tel) < 12 ||
            mb_strlen($tel) > 13
        ) {
            $this->error_message['tel'] = '電話番号は12~13桁で正しく入力してください';
        }

        // メールアドレス
        $email = $data['email'] ?? '';
        if ($email === '') {
            $this->error_message['email'] = 'メールアドレスが入力されていません';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error_message['email'] = '有効なメールアドレスを入力してください';
        }

        // 郵便番号と住所の整合性チェック
        if ($postal_code !== '' && $prefecture !== '' && $city_town !== '') {
            try {
                $sql = "SELECT COUNT(*) FROM address_master WHERE REPLACE(postal_code, '-', '') = :postal_code AND prefecture = :prefecture AND city LIKE :city_town";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':postal_code' => preg_replace('/[^0-9]/', '', $postal_code),
                    ':prefecture' => mb_convert_kana($prefecture, 'ASKV'),
                    ':city_town' => mb_convert_kana($city_town, 'ASKV') . '%',
                ]);
                $count = $stmt->fetchColumn();
                if ($count == 0) {
                    $this->error_message['address'] = '郵便番号と住所が一致しません';
                }
            } catch (\PDOException $e) {
                $this->error_message['address'] = 'DBエラー: ' . $e->getMessage();
            }
        }

        return empty($this->error_message);
    }

    // エラーメッセージ取得
    public function getErrors()
    {
        return $this->error_message;
    }

    // 生年月日の日付整合性チェック
    private function isValidDate($year, $month, $day)
    {
        return checkdate((int)$month, (int)$day, (int)$year);
    }
}
