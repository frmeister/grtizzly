<?php
require_once __DIR__.'/../db.php'; require_auth();
$job_id=intval($_GET['job_id']??0);
$st=pdo()->prepare('SELECT a.*, u.name as worker_name, u.deposit_balance, u.phone, u.contact, u.email, u.avatar FROM applications a JOIN users u ON u.id=a.worker_id WHERE a.job_id=? ORDER BY a.id DESC');
$st->execute([$job_id]); echo json_encode(['items'=>$st->fetchAll()], JSON_UNESCAPED_UNICODE);
