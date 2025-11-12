<?php
require_once __DIR__.'/../db.php';
$in=json_decode(file_get_contents('php://input'),true);
$email= isset($in['email']) ? trim($in['email']) : ''; 
$pass = isset($in['password']) ? $in['password'] : '';
$st=pdo()->prepare('SELECT * FROM users WHERE email=?'); $st->execute(array($email)); $u=$st->fetch();
if($u && password_verify($pass,$u['pass_hash'])){ $_SESSION['user']=$u; echo json_encode(array('ok'=>true,'user'=>$u)); }
else { echo json_encode(array('ok'=>false,'error'=>'bad')); }
