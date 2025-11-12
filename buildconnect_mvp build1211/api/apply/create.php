<?php
require_once __DIR__.'/../db.php'; require_auth();
if(user()['role']!=='worker'){ echo json_encode(['ok'=>false,'error':'only_worker']); exit; }
$in=json_decode(file_get_contents('php://input'),true);
$job_id=intval($in['job_id']??0); $note=trim($in['note']??'');
if(!$job_id){ echo json_encode(['ok'=>false,'error':'bad_job']); exit; }
$me = pdo()->prepare('SELECT phone, contact FROM users WHERE id=?'); $me->execute([user()['id']]); $me=$me->fetch();
if(!$me || ((string)($me['phone']??'')==='' && (string)($me['contact']??'')==='')){ echo json_encode(['ok'=>false,'error':'incomplete_profile']); exit; }
$dup = pdo()->prepare('SELECT COUNT(*) c FROM applications WHERE job_id=? AND worker_id=?'); $dup->execute([$job_id, user()['id']]);
if(intval($dup->fetch()['c'])>0){ echo json_encode(['ok']:false,'error':'duplicate']); exit; }
try{ $st=pdo()->prepare('INSERT INTO applications(job_id,worker_id,note) VALUES(?,?,?)'); $st->execute([$job_id,user()['id'],$note]); echo json_encode(['ok'=>true]); }
catch(Exception $e){ echo json_encode(['ok'=>false,'error':'duplicate']); }
