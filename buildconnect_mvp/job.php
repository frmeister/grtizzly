<?php include 'header.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT jobs.*, users.name as employer_name FROM jobs JOIN users ON users.id=jobs.employer_id WHERE jobs.id=?");
$stmt->execute([$id]); $job=$stmt->fetch();
if(!$job){ echo "<div class='alert alert-danger'>Вакансия не найдена</div>"; include 'footer.php'; exit; }
if($_SERVER['REQUEST_METHOD']==='POST' && is_worker()){
  $note=trim($_POST['cover_note'] ?? '');
  $stmt=db()->prepare("INSERT INTO applications(job_id,worker_id,cover_note) VALUES(?,?,?)");
  $stmt->execute([$id,$u['id'],$note]);
  echo "<div class='alert alert-success'>Отклик отправлен!</div>";
}
?>
<div class="card p-3">
  <h3><?= htmlspecialchars($job['title']) ?></h3>
  <div class="small-muted mb-2">Работодатель: <?= htmlspecialchars($job['employer_name']) ?> • Место: <?= htmlspecialchars($job['location'] ?: 'указано в описании') ?> • Создано: <?= htmlspecialchars($job['created_at']) ?></div>
  <p><?= nl2br(htmlspecialchars($job['description'])) ?></p>
  <div class="mb-3">Ставка: <strong><?= number_format($job['wage'],0,'',' ') ?> ₽</strong> • Срок: <?= (int)$job['duration_days'] ?> дн.</div>
  <?php if (is_worker()): ?>
  <form method="post" class="border-top pt-3">
    <label class="form-label">Короткое сопроводительное</label>
    <textarea class="form-control mb-2" name="cover_note" rows="3" placeholder="Расскажите о себе и опыте..."></textarea>
    <button class="btn btn-primary">Откликнуться</button>
  </form>
  <?php elseif (!$u): ?>
    <a class="btn btn-primary" href="auth.php">Войдите, чтобы откликнуться</a>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
