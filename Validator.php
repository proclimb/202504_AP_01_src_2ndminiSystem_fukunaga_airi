<?php

class Validator
{
    private $pdo;
    private $error_message = [];

    public function __construct(PDO $pdo) // PDOの型ヒントを追加
    {
        $this->pdo = $pdo;
    }

    /**
     * 入力データをバリデートする
     * @param array $data バリデート対象のデータ
     * @return bool バリデーションが成功したかどうか
     */
    public function validate(array $data): bool
    {
        $this->error_message = []; // エラーメッセージをリセット

        // 名前
        // 前後のスペース・全角スペースを除去し、空文字チェック
        $trimmed_name = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['name'] ?? '');
        if (empty($trimmed_name)) {
            $this->error_message['name'] = '名前が入力されていません。スペースのみでは入力できません。';
        } elseif (mb_strlen($trimmed_name) > 20) {
            $this->error_message['name'] = '名前は20文字以内で入力してください。';
        } elseif (!preg_match('/^[ぁ-んァ-ヶー一-龠々ｦ-ﾟー\s　]+$/u', $trimmed_name)) {
            // ひらがな、カタカナ、漢字、長音記号、踊り字、スペース・全角スペースのみを許可
            $this->error_message['name'] = '名前に使用できない文字が含まれています。英数字や記号は使用できません。';
        }

        // ふりがな
        $kana_input = $data['kana'] ?? '';
        $trimmed_kana = preg_replace('/^[\s　]+|[\s　]+$/u', '', $kana_input);

        if (empty($trimmed_kana)) {
            $this->error_message['kana'] = 'ふりがなが入力されていません。スペースのみでは入力できません。';
        } elseif (preg_match('/[^ぁ-んー\s　]/u', $trimmed_kana)) {
            // ひらがな、長音記号、スペース・全角スペースのみを許可
            $this->error_message['kana'] = 'ふりがなはひらがなで入力してください。';
        } elseif (mb_strlen($trimmed_kana) > 20) {
            $this->error_message['kana'] = 'ふりがなは20文字以内で入力してください。';
        }

        // 生年月日
        $birth_year = $data['birth_year'] ?? '';
        $birth_month = $data['birth_month'] ?? '';
        $birth_day = $data['birth_day'] ?? '';

        if (empty($birth_year) || empty($birth_month) || empty($birth_day)) {
            $this->error_message['birth_date'] = '生年月日が入力されていません。';
        } elseif (!$this->isValidDate($birth_year, $birth_month, $birth_day)) {
            $this->error_message['birth_date'] = '生年月日が正しくありません。';
        } else {
            $input_date_str = sprintf('%04d-%02d-%02d', (int)$birth_year, (int)$birth_month, (int)$birth_day);
            if (strtotime($input_date_str) > strtotime(date('Y-m-d'))) {
                $this->error_message['birth_date'] = '生年月日が未来日になっています。正しい日付を入力してください。';
            }
        }

        // 郵便番号
        $postal_code = $data['postal_code'] ?? '';
        if (empty($postal_code)) {
            $this->error_message['postal_code'] = '郵便番号が入力されていません。';
        } elseif (!preg_match('/^\d{3}-?\d{4}$/', $postal_code)) {
            $this->error_message['postal_code'] = '郵便番号はXXX-XXXX または XXXXXXX の形式で入力してください。';
        }

        // 住所
        $trimmed_pref = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['prefecture'] ?? '');
        $trimmed_city_town = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['city_town'] ?? '');
        $trimmed_building = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['building'] ?? '');

        if (empty($trimmed_pref)) {
            $this->error_message['prefecture'] = '都道府県が入力されていません。';
        } elseif (mb_strlen($trimmed_pref) > 10) {
            $this->error_message['prefecture'] = '都道府県は10文字以内で入力してください。';
        }

        if (empty($trimmed_city_town)) {
            $this->error_message['city_town'] = '市区町村・番地が入力されていません。';
        } elseif (mb_strlen($trimmed_city_town) > 50) {
            $this->error_message['city_town'] = '市区町村・番地は50文字以内で入力してください。';
        }

        if (mb_strlen($trimmed_building) > 50) {
            $this->error_message['building'] = '建物名は50文字以内で入力してください。';
        }

        // 電話番号
        $tel = $data['tel'] ?? '';
        if (empty($tel)) {
            $this->error_message['tel'] = '電話番号が入力されていません。';
        } elseif (!preg_match('/^0\d{1,4}-\d{1,4}-\d{3,4}$/', $tel)) {
            $this->error_message['tel'] = '電話番号はハイフンを含めて正しい形式で入力してください。';
        } elseif (mb_strlen($tel) < 12 || mb_strlen($tel) > 13) {
            // 日本の電話番号はハイフン込みで12桁または13桁が一般的
            $this->error_message['tel'] = '電話番号は12～13桁で正しく入力してください。';
        }

        // メールアドレス
        $email = $data['email'] ?? '';
        if (empty($email)) {
            $this->error_message['email'] = 'メールアドレスが入力されていません。';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error_message['email'] = '有効なメールアドレスを入力してください。';
        }

        // --- 郵便番号と住所の整合性チェック（主要な改善点） ---
        // 各フィールドの個別のエラーがない場合のみ実行
        if (
            empty($this->error_message['postal_code']) &&
            empty($this->error_message['prefecture']) &&
            empty($this->error_message['city_town'])
        ) {

            try {
                // 入力の整形
                $normalized_zip = preg_replace('/[^0-9]/', '', $postal_code);
                // 全角カタカナ、半角カナを全角ひらがなに変換し、スペース除去
                $normalized_pref = mb_convert_kana($trimmed_pref, 'HVc'); // 半角カタカナ->全角ひらがな, 全角->半角カタカナ, 空白削除
                $normalized_pref = preg_replace('/\s+/u', '', $normalized_pref); // 念のためスペース除去

                $normalized_city_town_input = mb_convert_kana($trimmed_city_town, 'HVc');
                $normalized_city_town_input = preg_replace('/\s+/u', '', $normalized_city_town_input);

                // DBから該当する郵便番号と都道府県の住所候補をすべて取得
                $sql_fetch_addresses = "
                    SELECT city, town
                    FROM address_master
                    WHERE REPLACE(postal_code, '-', '') = :zip
                      AND prefecture = :pref
                ";
                $stmt_fetch = $this->pdo->prepare($sql_fetch_addresses);
                $stmt_fetch->bindValue(':zip', $normalized_zip, PDO::PARAM_STR);
                $stmt_fetch->bindValue(':pref', $normalized_pref, PDO::PARAM_STR);
                $stmt_fetch->execute();
                $db_addresses = $stmt_fetch->fetchAll(PDO::FETCH_ASSOC);

                $found_match = false;
                if (!empty($db_addresses)) {
                    foreach ($db_addresses as $db_address) {
                        // DBから取得したcityとtownを結合して正規化
                        $db_full_address = $db_address['city'] . ($db_address['town'] ?? ''); // townがNULLの場合を考慮
                        $normalized_db_full_address = mb_convert_kana($db_full_address, 'HVc');
                        $normalized_db_full_address = preg_replace('/\s+/u', '', $normalized_db_full_address);

                        // 入力されたcity_townとDBの結合された住所を比較
                        if ($normalized_db_full_address === $normalized_city_town_input) {
                            $found_match = true;
                            break;
                        }
                    }
                }

                if (!$found_match) {
                    $this->error_message['address_match'] = '入力された郵便番号と住所が一致しません。市区町村・番地の入力をご確認ください。';
                }
            } catch (PDOException $e) {
                // 開発環境では詳細なエラーを表示、本番環境では一般的なメッセージ
                error_log('DB Error: ' . $e->getMessage()); // エラーログに出力
                $this->error_message['address_match'] = '住所の確認中にエラーが発生しました。時間をおいて再度お試しください。';
            }
        }

        return empty($this->error_message);
    }

    /**
     * エラーメッセージを取得する
     * @return array エラーメッセージの配列
     */
    public function getErrors(): array
    {
        return $this->error_message;
    }

    /**
     * 生年月日の日付整合性チェック
     * @param string $year 年
     * @param string $month 月
     * @param string $day 日
     * @return bool 有効な日付であればtrue
     */
    private function isValidDate(string $year, string $month, string $day): bool
    {
        // checkdate関数は数値型を期待するため、キャスト
        return checkdate((int)$month, (int)$day, (int)$year);
    }
}
