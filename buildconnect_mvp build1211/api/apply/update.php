<?php
require_once __DIR__.'/../db.php'; require_auth();
if(user()['role']!=='employer'){ echo json_encode(['ok'=>false,'error':'only_employer']); exit; }
$in=json_decode(file_get_contents('php://input'),true);
$id=intval($in['id']??0); $action=$in['action']??'';
$map=['accept'=>'accepted','reject'=>'rejected','complete'=>'completed'];
if(!isset($map[$action])){ echo json_encode(['ok'=>false,'error':'action']); exit; }
$st=pdo()->prepare('UPDATE applications SET status=? WHERE id=?'); $st->execute([$map[$action],$id]);
echo json_encode(['ok'=>true]);
