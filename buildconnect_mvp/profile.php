<?php include 'header.php'; require_login(); $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['update_profile'])){
    $stmt=db()->prepare("UPDATE users SET phone=?, education=?, experience_years=? WHERE id=?");
    $stmt->execute([trim($_POST['phone']??''), trim($_POST['education']??''), (int)($_POST['experience_years']??0), $u['id']]);
    refresh_session_user($u['id']); $msg='Профиль обновлён.';
  } elseif(isset($_POST['deposit_topup'])){
    $amount=max(0,(float)($_POST['amount']??0));
    if($amount>0){
      db()->prepare("UPDATE users SET deposit_balance=deposit_balance+? WHERE id=?")->execute([$amount,$u['id']]);
      db()->prepare("INSERT INTO transactions(user_id,type,amount,ref) VALUES(?,?,?,?)")->execute([$u['id'],'deposit',$amount,'topup']);
      refresh_session_user($u['id']); $msg="Депозит пополнен на {$amount} ₽.";
    }
  }
}
$u=current_user();
?>
<?php if($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3">
      <h4>Профиль</h4>
      <div class="small-muted mb-2">Баланс депозита: <span class="balance"><?= number_format($u['deposit_balance'],2,',',' ') ?> ₽</span></div>
      <form method="post">
        <div class="mb-2"><label class="form-label">Имя</label><input class="form-control" value="<?= htmlspecialchars($u['name']) ?>" disabled></div>
        <div class="mb-2"><label class="form-label">Email</label><input class="form-control" value="<?= htmlspecialchars($u['email']) ?>" disabled></div>
        <div class="mb-2"><label class="form-label">Телефон</label><input name="phone" class="form-control" value="<?= htmlspecialchars($u['phone']) ?>"></div>
        <div class="mb-2"><label class="form-label">Образование</label><input name="education" class="form-control" value="<?= htmlspecialchars($u['education']) ?>"></div>
        <div class="mb-2"><label class="form-label">Стаж (лет)</label><input name="experience_years" class="form-control" type="number" value="<?= (int)$u['experience_years'] ?>"></div>
        <button class="btn btn-primary" name="update_profile">Сохранить</button>
      </form>
      <hr>
      <form method="post" class="d-flex gap-2">
        <input class="form-control" type="number" name="amount" step="100" placeholder="Сумма пополнения">
        <button class="btn btn-outline-success" name="deposit_topup">Пополнить депозит</button>
      </form>
    </div>
  </div>
  <div class="col-md-6">
    <?php if($u['role']==='worker'): ?>
      <div class="card p-3 mb-3">
        <h5>Мои отклики</h5>
        <?php 
          $stmt=db()->prepare("SELECT a.*, j.title FROM applications a JOIN jobs j ON j.id=a.job_id WHERE a.worker_id=? ORDER BY a.created_at DESC");
          $stmt->execute([$u['id']]); $apps=$stmt->fetchAll();
          if(!$apps) echo "<div class='text-muted'>Пока нет откликов.</div>";
          foreach($apps as $a){
            echo "<div class='border rounded p-2 mb-2'><div class='d-flex justify-content-between'><strong>".htmlspecialchars($a['title'])."</strong><span class='badge bg-secondary'>{$a['status']}</span></div><div class='small-muted'>{$a['created_at']}</div></div>";
          }
        ?>
      </div>
    <?php else: ?>
      <div class="card p-3 mb-3">
        <h5>Мои вакансии</h5>
        <?php 
          $stmt=db()->prepare("SELECT * FROM jobs WHERE employer_id=? ORDER BY created_at DESC");
          $stmt->execute([$u['id']]); $jobs=$stmt->fetchAll();
          if(!$jobs) echo "<div class='text-muted'>Вы ещё не размещали вакансии.</div>";
          foreach($jobs as $j){
            echo "<div class='border rounded p-2 mb-2'><div class='d-flex justify-content-between align-items-center'><strong>".htmlspecialchars($j['title'])."</strong><a class='btn btn-sm btn-outline-primary' href='view_applications.php?job_id={$j['id']}'>Отклики</a></div><div class='small-muted'>{$j['created_at']} • Статус: {$j['status']}</div></div>";
          }
        ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php include 'footer.php'; ?>
