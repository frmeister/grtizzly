<?php
require_once __DIR__.'/../db.php';
$q=$_GET['q']??''; $city=$_GET['city']??''; $spec=$_GET['spec']??''; $mine=isset($_GET['mine']); $pending=isset($_GET['pending']);
$me = $_SESSION['user'] ?? null;
$sql="SELECT j.*, (strftime('%s','now')-strftime('%s',j.created_at))<86400 as is_new,
(SELECT COUNT(*) FROM applications a WHERE a.job_id=j.id) as applicants FROM jobs j";
$w=["1=1"]; $p=[];
if($mine && $me){ $w[]="j.employer_id=?"; $p[]=$me['id']; } else if($pending){ $w[]="j.status='pending'"; } else { $w[]="j.status='open'"; }
if($q!==''){ $w[]="(j.title LIKE ? OR j.description LIKE ?)"; $p[]="%$q%"; $p[]="%$q%"; }
if($city!==''){ $w[]="j.city LIKE ?"; $p[]="%$city%"; }
if($spec!==''){ $w[]="j.specialization=?"; $p[]=$spec; }
$sql.=" WHERE ".join(" AND ",$w)." ORDER BY j.id DESC LIMIT 200";
$st=pdo()->prepare($sql); $st->execute($p); echo json_encode(['jobs'=>$st->fetchAll()], JSON_UNESCAPED_UNICODE);
