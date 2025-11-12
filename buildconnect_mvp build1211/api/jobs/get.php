<?php
require_once __DIR__.'/../db.php';
$id=intval($_GET['id']??0);
$st=pdo()->prepare('SELECT j.*, u.name as employer_name FROM jobs j JOIN users u ON u.id=j.employer_id WHERE j.id=?');
$st->execute([$id]); $j=$st->fetch();
if(!$j){ http_response_code(404); echo json_encode(['error':'not_found']); exit; }
$me = $_SESSION['user'] ?? null;
if($j['status']!=='open' && (!$me || ($me['role']!=='moderator' && $me['id']!=$j['employer_id']))){
  http_response_code(403); echo json_encode(['error':'forbidden']); exit;
}
echo json_encode($j, JSON_UNESCAPED_UNICODE);
