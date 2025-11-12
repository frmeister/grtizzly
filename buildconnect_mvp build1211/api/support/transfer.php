<?php
require_once __DIR__.'/../db.php'; require_auth();
if(user()['role']!=='moderator'){ echo json_encode(['ok'=>false,'error':'only_moderator']); exit; }
$in=json_decode(file_get_contents('php://input'),true);
$app_id=intval($in['application_id']??0); $amount=floatval($in['amount']??0);
if($amount<=0){ echo json_encode(['ok'=>false,'error':'amount']); exit; }
$st=pdo()->prepare('SELECT a.*, j.employer_id, a.worker_id FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.id=?');
$st->execute([$app_id]); $row=$st->fetch(); if(!$row){ echo json_encode(['ok'=>false,'error':'not_found']); exit; }
$wu=pdo()->prepare('SELECT deposit_balance FROM users WHERE id=?'); $wu->execute([$row['worker_id']]);
$wb=$wu->fetch()['deposit_balance'] ?? 0; if($wb < $amount){ echo json_encode(['ok'=>false,'error':'insufficient_worker_deposit']); exit; }
pdo()->beginTransaction(); try{
  pdo()->prepare('UPDATE users SET deposit_balance=deposit_balance-? WHERE id=?')->execute([$amount,$row['worker_id']]);
  pdo()->prepare('UPDATE users SET company_balance=company_balance+? WHERE id=?')->execute([$amount,$row['employer_id']]);
  pdo()->prepare('INSERT INTO transactions(user_id,type,amount,ref) VALUES(?,?,?,?)')->execute([$row['worker_id'],'penalty',-$amount,'support_transfer']);
  pdo()->prepare('INSERT INTO transactions(user_id,type,amount,ref) VALUES(?,?,?,?)')->execute([$row['employer_id'],'payout',$amount,'support_transfer']);
  pdo()->commit(); echo json_encode(['ok'=>true]);
} catch(Exception $e){ pdo()->rollBack(); echo json_encode(['ok'=>false,'error':'tx']); }
