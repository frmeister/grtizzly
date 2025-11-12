<?php
require_once __DIR__.'/../db.php';
require_auth();
$u = user();
if(!$u || $u['role']!=='employer'){ echo json_encode(array('ok'=>false,'error'=>'only_employer')); exit; }
$in = json_decode(file_get_contents('php://input'), true);
$title = isset($in['title']) ? trim($in['title']) : '';
$desc  = isset($in['description']) ? trim($in['description']) : '';
$city  = isset($in['city']) ? trim($in['city']) : '';
$spec  = isset($in['specialization']) ? trim($in['specialization']) : '';
$wage  = isset($in['wage']) ? floatval($in['wage']) : 0;
$pay   = isset($in['pay_type']) ? $in['pay_type'] : 'fixed';
$days  = isset($in['duration_days']) ? intval($in['duration_days']) : 1;
if($title==='' || $wage<=0){ echo json_encode(array('ok'=>false,'error'=>'fields')); exit; }
$st=pdo()->prepare('INSERT INTO jobs(employer_id,title,description,city,specialization,wage,pay_type,duration_days,status) VALUES(?,?,?,?,?,?,?,?,?)');
$st->execute(array($u['id'],$title,$desc,$city,$spec,$wage,$pay,$days,'pending'));
echo json_encode(array('ok'=>true,'id'=>pdo()->lastInsertId(),'status'=>'pending'));
