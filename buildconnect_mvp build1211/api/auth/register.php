<?php
require_once __DIR__.'/../db.php';
$raw = file_get_contents('php://input');
$in = json_decode($raw,true);
$name = isset($in['name']) ? trim($in['name']) : '';
$email = isset($in['email']) ? trim($in['email']) : '';
$pass = isset($in['password']) ? $in['password'] : '';
$role = isset($in['role']) ? $in['role'] : 'worker';
$allowed = array('worker','employer','moderator');
if(!in_array($role,$allowed)) $role='worker';
if($name==='' || $email==='' || $pass===''){ echo json_encode(array('ok'=>false,'error'=>'missing')); exit; }
try{
  $st=pdo()->prepare('INSERT INTO users(role,name,email,pass_hash) VALUES(?,?,?,?)');
  $st->execute(array($role,$name,$email,password_hash($pass,PASSWORD_DEFAULT)));
  $id=pdo()->lastInsertId(); $_SESSION['user']=pdo()->query('SELECT * FROM users WHERE id='.(int)$id)->fetch();
  echo json_encode(array('ok'=>true,'user'=>$_SESSION['user']));
}catch(Exception $e){ echo json_encode(array('ok'=>false,'error'=>'email')); }
