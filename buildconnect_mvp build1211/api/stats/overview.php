<?php
require_once __DIR__.'/../db.php';
$active=pdo()->query("SELECT COUNT(*) c FROM jobs WHERE status='open'")->fetch(); $active = $active?intval($active['c']):0;
$workers=pdo()->query("SELECT COUNT(*) c FROM users WHERE role='worker'")->fetch(); $workers = $workers?intval($workers['c']):0;
$employers=pdo()->query("SELECT COUNT(*) c FROM users WHERE role='employer'")->fetch(); $employers = $employers?intval($employers['c']):0;
echo json_encode(array('active'=>$active,'workers'=>$workers,'employers'=>$employers));
