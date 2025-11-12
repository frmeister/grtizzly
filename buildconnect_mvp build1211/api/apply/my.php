<?php
require_once __DIR__.'/../db.php'; require_auth();
if(user()['role']!=='worker'){ echo json_encode(['ok'=>false,'error':'only_worker']); exit; }
$st=pdo()->prepare("SELECT a.*, j.title, j.city, j.specialization, j.wage, j.status as job_status, j.id as job_id,
                           (SELECT u.name FROM users u WHERE u.id=j.employer_id) as employer_name
                    FROM applications a JOIN jobs j ON j.id=a.job_id
                    WHERE a.worker_id=? ORDER BY a.id DESC");
$st->execute([user()['id']]); echo json_encode(['ok'=>true,'items'=>$st->fetchAll()], JSON_UNESCAPED_UNICODE);
