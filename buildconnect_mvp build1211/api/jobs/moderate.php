<?php
require_once __DIR__.'/../db.php'; require_auth();
if(user()['role']!=='moderator'){ echo json_encode(['ok':false,'error':'only_moderator']); exit; }
$in=json_decode(file_get_contents('php://input'),true);
$id=intval($in['id']??0); $action=$in['action']??'';
if(!$id || !in_array($action,['approve','reject'])){ echo json_encode(['ok'=>false,'error':'bad_params']); exit; }
$status = $action==='approve' ? 'open' : 'rejected';
$st=pdo()->prepare('UPDATE jobs SET status=? WHERE id=?'); $st->execute([$status,$id]);
echo json_encode(['ok'=>true,'status'=>$status]);
