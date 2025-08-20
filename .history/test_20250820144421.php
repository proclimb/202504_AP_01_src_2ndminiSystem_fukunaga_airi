<?php
// DBに保存されてる password_hash をコピペ
$hash = '$10$FK3eRNNw0CHa8s.UznwQJefHujCQYmm4IZT.3p.rDip7eZA59cWrO';

// 入力する予定のパスワード
$input = 'aaaa1111';

var_dump(password_verify($input, $hash));
