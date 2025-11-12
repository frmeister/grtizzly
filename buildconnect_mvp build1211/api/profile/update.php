<?php
require_once __DIR__.'/../db.php'; require_auth();
$in=json_decode(file_get_contents('php://input'),true);
$st=pdo()->prepare('UPDATE users SET phone=?, contact=?, education=?, exp_years=?, avatar=? WHERE id=?');
$st->execute([trim($in['phone']??''), trim($in['contact']??''), trim($in['education']??''), intval($in['exp_years']??0), trim($in['avatar']??''), user()['id']]);
refresh_user(); echo json_encode(['ok'=>true,'user'=>user()]);
