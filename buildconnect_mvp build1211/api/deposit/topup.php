<?php
require_once __DIR__.'/../db.php'; require_auth();
if(user()['role']!=='worker'){ echo json_encode(['ok'=>false,'error':'only_worker']); exit; }
$in=json_decode(file_get_contents('php://input'),true);
$amount=floatval($in['amount']??0); if($amount<=0){ echo json_encode(['ok'=>false,'error':'amount']); exit; }
pdo()->prepare('UPDATE users SET deposit_balance=deposit_balance+? WHERE id=?')->execute([$amount,user()['id']]);
pdo()->prepare('INSERT INTO transactions(user_id,type,amount,ref) VALUES(?,?,?,?)')->execute([user()['id'],'deposit',$amount,'topup']);
refresh_user(); echo json_encode(['ok'=>true,'balance'=>user()['deposit_balance']]);
