<?php include 'header.php'; require_login(); if(!is_employer()){ echo "<div class='alert alert-danger'>Доступ только для работодателей.</div>"; include 'footer.php'; exit; }
$job_id=(int)($_GET['job_id'] ?? 0);
$stmt=db()->prepare("SELECT * FROM jobs WHERE id=? AND employer_id=?"); $stmt->execute([$job_id,$u['id']]); $job=$stmt->fetch();
if(!$job){ echo "<div class='alert alert-danger'>Вакансия не найдена.</div>"; include 'footer.php'; exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $app_id=(int)($_POST['app_id']??0); $action=$_POST['action']??'';
  if($action==='accept'){ db()->prepare("UPDATE applications SET status='accepted' WHERE id=?")->execute([$app_id]); }
  elseif($action==='complete'){ db()->prepare("UPDATE applications SET status='completed' WHERE id=?")->execute([$app_id]); }
  elseif($action==='reject'){ db()->prepare("UPDATE applications SET status='rejected' WHERE id=?")->execute([$app_id]); }
  echo "<div class='alert alert-success'>Обновлено.</div>";
}
$stmt=db()->prepare("SELECT a.*, u.name as worker_name, u.deposit_balance FROM applications a JOIN users u ON u.id=a.worker_id WHERE a.job_id=? ORDER BY a.created_at DESC");
$stmt->execute([$job_id]); $apps=$stmt->fetchAll();
?>
<div class="card p-3">
  <h4>Отклики на вакансию: <?= htmlspecialchars($job['title']) ?></h4>
  <?php if(!$apps): ?><div class="text-muted">Откликов пока нет.</div><?php endif; ?>
  <?php foreach($apps as $a): ?>
    <div class="border rounded p-2 mb-2">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div><strong><?= htmlspecialchars($a['worker_name']) ?></strong> — статус: <span class="badge bg-secondary"><?= $a['status'] ?></span></div>
          <div class="small-muted">Депозит: <?= number_format($a['deposit_balance'],0,'',' ') ?> ₽ • Отклик: <?= htmlspecialchars($a['cover_note']) ?></div>
        </div>
        <form method="post" class="d-flex gap-1">
          <input type="hidden" name="app_id" value="<?= (int)$a['id'] ?>">
          <button class="btn btn-sm btn-success" name="action" value="accept">Принять</button>
          <button class="btn btn-sm btn-outline-primary" name="action" value="complete">Завершено</button>
          <button class="btn btn-sm btn-outline-danger" name="action" value="reject">Отклонить</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>
