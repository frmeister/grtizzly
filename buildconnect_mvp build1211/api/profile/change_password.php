<?php
require_once __DIR__.'/../db.php'; require_auth();
$in=json_decode(file_get_contents('php://input'),true);
$cur=$in['current']??''; $n1=$in['new1']??''; $n2=$in['new2']??'';
if(!$cur||!$n1||!$n2){ echo json_encode(['ok'=>false,'error':'missing']); exit; }
$u=user();
if(!password_verify($cur,$u['pass_hash'])){ echo json_encode(['ok'=>false,'error':'wrong_current']); exit; }
if(strlen($n1)<6){ echo json_encode(['ok'=>false,'error':'weak']); exit; }
if($n1!==$n2){ echo json_encode(['ok'=>false,'error':'mismatch']); exit; }
$st=pdo()->prepare('UPDATE users SET pass_hash=? WHERE id=?'); $st->execute([password_hash($n1,PASSWORD_DEFAULT), $u['id']]);
echo json_encode(['ok'=>true]);
