<?php

class Validator
{
    private $pdo;
    private $error_message = [];

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 入力全体のバリデーション
    public function validate($data)
    {
        $this->error_message = [];

        // 名前
        $trimmed_name = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['name'] ?? '');
        if (empty($trimmed_name)) {
            $this->error_message['name'] = 'スペースのみでは入力できません';
        } elseif (mb_strlen($trimmed_name) > 20) {
            $this->error_message['name'] = '名前は20文字以内で入力してください';
        } elseif (!preg_match('/^[ぁ-んァ-ヶー一-龠々ｦ-ﾟー\s　]+$/u', $trimmed_name) || preg_match('/[0-9!"#\$%&\'\(\)\*=\+\,\-\.\/\\:;<=>?@\[\]^_`\{|\}~]/u', $trimmed_name)) {
            $this->error_message['name'] = '名前に使用できない文字が含まれています';
        }

        // ふりがな
        $kana_input = $data['kana'] ?? '';
        $trimmed_kana = preg_replace('/^[\s　]+|[\s　]+$/u', '', $kana_input);
        if (mb_strlen(trim($kana_input)) > 0 && empty($trimmed_kana)) {
            $this->error_message['kana'] = 'スペースのみでは入力できません';
        } elseif (empty($trimmed_kana)) {
            $this->error_message['kana'] = 'ふりがなが入力されていません';
        } elseif (preg_match('/[^ぁ-んー\s　]/u', $trimmed_kana)) {
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
            $this->error_message['birth_date'] = '生年月日が未来日になっています。正しい日付を入力してください';
        }

        // 郵便番号
        $postal = $data['postal_code'] ?? '';
        if (empty($postal)) {
            $this->error_message['postal_code'] = '郵便番号が入力されていません';
        } elseif (!preg_match('/^\d{3}-?\d{4}$/', $postal)) {
            $this->error_message['postal_code'] = '郵便番号はXXX-XXXX または XXXXXXX の形式で入力してください';
        }

        // 住所
        $pref = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['prefecture'] ?? '');
        $city = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['city_town'] ?? '');
        $building = preg_replace('/^[\s　]+|[\s　]+$/u', '', $data['building'] ?? '');

        $address_valid = true;
        if (empty($pref) || empty($city)) {
            $this->error_message['address'] = '住所(都道府県もしくは市区町村・番地)が入力されていません';
            $address_valid = false;
        } elseif (mb_strlen($pref) > 10) {
            $this->error_message['address'] = '都道府県は10文字以内で入力してください';
            $address_valid = false;
        } elseif (mb_strlen($city) > 50 || mb_strlen($building) > 50) {
            $this->error_message['address'] = '市区町村・番地もしくは建物名は50文字以内で入力してください';
            $address_valid = false;
        }

        // 郵便番号と住所の整合性
        if (
            $address_valid &&
            !empty($postal) && !empty($pref) && !empty($city)
        ) {
            try {
                $postal_code = preg_replace('/[^0-9]/', '', $postal);
                $prefecture = preg_replace('/\s/u', '', mb_convert_kana($pref, 'ASKV'));
                $city_town = preg_replace('/\s/u', '', mb_convert_kana($city, 'ASKV'));

                $sql = "SELECT COUNT(*) FROM address_master WHERE REPLACE(postal_code, '-', '') = :postal_code AND prefecture = :prefecture AND REPLACE(CONCAT(city, town), ' ', '') LIKE :city_town";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    ':postal_code' => $postal_code,
                    ':prefecture' => $prefecture,
                    ':city_town' => $city_town . '%',
                ]);
                if ($stmt->fetchColumn() == 0) {
                    $this->error_message['address'] = '郵便番号と住所が一致しません';
                }
            } catch (\PDOException $e) {
                $this->error_message['address'] = 'DBエラー: ' . $e->getMessage();
            }
        }

        // 電話番号
        if (empty($data['tel'])) {
            $this->error_message['tel'] = '電話番号が入力されていません';
        } elseif (
            !preg_match('/^0\d{1,4}-\d{1,4}-\d{3,4}$/', $data['tel']) ||
            mb_strlen($data['tel']) < 12 || mb_strlen($data['tel']) > 13
        ) {
            $this->error_message['tel'] = '電話番号は12~13桁で正しく入力してください';
        }

        // メールアドレス
        if (empty($data['email'])) {
            $this->error_message['email'] = 'メールアドレスが入力されていません';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error_message['email'] = '有効なメールアドレスを入力してください';
        }

        // ▼ ① ファイルだけは最初に処理（拡張子OKならセッション保存）
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $files = [
            'document1' => '本人確認書類（表）',
            'document2' => '本人確認書類（裏）'
        ];
        $tmpDir = __DIR__ . '/../tmp_uploads/';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        foreach ($files as $key => $label) {
            if (isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExtensions)) {
                    $this->error_message[$key] = "ファイル形式は PNG または JPEG のみ許可されています";
                } else {
                    $filename = uniqid($key . '_') . '.' . $ext;
                    $destination = $tmpDir . $filename;

                    if (move_uploaded_file($_FILES[$key]['tmp_name'], $destination)) {
                        $_SESSION[$key . '_path'] = $destination;
                        $_SESSION[$key . '_original_name'] = $_FILES[$key]['name'];
                    }
                }
            } elseif (!empty($_SESSION[$key . '_path']) && file_exists($_SESSION[$key . '_path'])) {
                continue;
            }
        }
        return empty($this->error_message);
    }

    // エラーメッセージ取得（全体）
    public function getErrors()
    {
        return $this->error_message;
    }

    // 郵便番号専用のエラーのみ取得（JSと併用）
    public function getPostalCodeError()
    {
        return $this->error_message['postal_code'] ?? '';
    }

    // 生年月日チェック
    private function isValidDate($year, $month, $day)
    {
        return checkdate((int)$month, (int)$day, (int)$year);
    }
}
