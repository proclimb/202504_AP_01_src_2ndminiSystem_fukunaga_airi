<?php

class Validator
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    private $error_message = [];

    // 呼び出し元で使う
    public function validate($data)
    {
        $this->error_message = [];

        // 名前
        $trimmed_name = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['name'] ?? '');
        if (empty($trimmed_name)) {
            $this->error_message['name'] = 'お名前にスペースは使用できません ';
        } elseif (mb_strlen($trimmed_name) > 20) {
            $this->error_message['name'] = '名前は20文字以内で入力してください';
        } elseif (!preg_match('/^[ぁ-んァ-ヶー一-龠々ｦ-ﾟー\s　]+$/u', $trimmed_name)) {
            $this->error_message['name'] = '名前に使用できない文字が含まれています';
        } elseif (preg_match('/[0-9!"#\$%&\'\(\)\*=\+\,\-\.\/\\:;<=>?@\[\]^_`\{|\}~]/u', $trimmed_name)) {
            $this->error_message['name'] = '名前に使用できない文字が含まれています';
        }

        // ふりがな
        $trimmed_kana = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['kana'] ?? '');
        if (empty($trimmed_kana)) {
            $this->error_message['kana'] = '入力値の前後に空白（スペース）は含めないでください ';
        } elseif (preg_match('/[^ぁ-んー]/u', $trimmed_kana)) {
            $this->error_message['kana'] = 'ひらがなで入力してください';
        } elseif (mb_strlen($trimmed_kana) > 20) {
            $this->error_message['kana'] = 'ふりがなは20文字以内で入力してください';
        }


        // 生年月日
        if (empty($data['birth_year']) || empty($data['birth_month']) || empty($data['birth_day'])) {
            $this->error_message['birth_date'] = '生年月日が入力されていません';
        } elseif (!$this->isValidDate($data['birth_year'] ?? '', $data['birth_month'] ?? '', $data['birth_day'] ?? '')) {
            $this->error_message['birth_date'] = '生年月日が正しくありません';
        } elseif (strtotime($data['birth_year'] . '-' . $data['birth_month'] . '-' . $data['birth_day']) > strtotime(date('Y-m-d'))) {
            $this->error_message['birth_date'] = '生年月日が未来日になっています。正しい日付を入力してください ';
        }

        // 郵便番号
        if (empty($data['postal_code'])) {
            $this->error_message['postal_code'] = '郵便番号が入力されていません';
        } elseif (!preg_match('/^[0-9]{3}-[0-9]{4}$/', $data['postal_code'] ?? '')) {
            $this->error_message['postal_code'] = '郵便番号はXXX-XXXXの形式で入力してください';
        }

        // 住所
        $trimmed_pref = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['prefecture'] ?? '');
        $trimmed_city = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['city_town'] ?? '');
        $trimmed_building = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['building'] ?? '');
        if (empty($trimmed_pref) || empty($trimmed_city)) {
            $this->error_message['address'] = '入力値の前後に空白（スペース）は含めないでください ';
        } elseif (mb_strlen($trimmed_pref) > 10) {
            $this->error_message['address'] = '都道府県は10文字以内で入力してください';
        } elseif (mb_strlen($trimmed_city) > 50 || mb_strlen($trimmed_building) > 50) {
            $this->error_message['address'] = '市区町村・番地もしくは建物名は50文字以内で入力してください';
        }

        // 電話番号
        if (empty($data['tel'])) {
            $this->error_message['tel'] = '電話番号が入力されていません';
        } elseif (
            !preg_match('/^0\d{1,4}-\d{1,4}-\d{3,4}$/', $data['tel'] ?? '') ||
            mb_strlen($data['tel']) < 12 ||
            mb_strlen($data['tel']) > 13
        ) {
            $this->error_message['tel'] = '電話番号は12~13桁で正しく入力してください';
        }
        // メールアドレス
        if (empty($data['email'])) {
            $this->error_message['email'] = 'メールアドレスが入力されていません';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error_message['email'] = '有効なメールアドレスを入力してください';
        }

        // 郵便番号と住所の整合性チェック
        if (!empty($data['postal_code']) && !empty($data['prefecture']) && !empty($data['city_town'])) {
            try {
                $sql = "SELECT COUNT(*) FROM address_master WHERE REPLACE(postal_code, '-', '') = :postal_code AND prefecture = :prefecture AND city LIKE :city_town";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':postal_code' => preg_replace('/[^0-9]/', '', $data['postal_code']),
                    ':prefecture' => preg_replace('/\s/u', '', mb_convert_kana($data['prefecture'], 'ASKV')),
                    ':city_town' => preg_replace('/\s/u', '', mb_convert_kana($data['city_town'], 'ASKV')) . '%',
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
//現在の進捗
/*入力値の前後、空文字のみの入力をはじく
名前の間の半角、全角スペースの許容
住所と郵便番号が一致しているか
名前に使用できない文字が含まれています英語、数字記号をはじく
*/
