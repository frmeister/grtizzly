<?php include 'header.php';
$kw = $_GET['q'] ?? '';
$stmt = db()->prepare("SELECT jobs.*, users.name as employer_name FROM jobs JOIN users ON users.id=jobs.employer_id
  WHERE (title LIKE :kw OR description LIKE :kw OR location LIKE :kw) AND status='open' ORDER BY jobs.created_at DESC");
$stmt->execute([':kw'=>"%$kw%"]); $jobs=$stmt->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <form class="d-flex" method="get">
    <input name="q" class="form-control me-2" type="search" placeholder="Поиск вакансий..." value="<?= htmlspecialchars($kw) ?>">
    <button class="btn btn-outline-secondary">Поиск</button>
  </form>
  <div><?php if ($u && $u['role']==='employer'): ?><a class="btn btn-success" href="post_job.php">+ Новая вакансия</a><?php endif; ?></div>
</div>
<?php if(!$jobs): ?><div class="alert alert-warning">Подходящих вакансий пока нет.</div><?php endif; ?>
<div class="row g-3">
<?php foreach($jobs as $job): ?>
  <div class="col-md-6">
    <div class="card card-job p-3 h-100">
      <h5 class="mb-1"><?= htmlspecialchars($job['title']) ?></h5>
      <div class="small-muted mb-2">Работодатель: <?= htmlspecialchars($job['employer_name']) ?> • Место: <?= htmlspecialchars($job['location'] ?: 'указано в описании') ?></div>
      <p class="mb-2"><?= nl2br(htmlspecialchars(mb_strimwidth($job['description'],0,300,'…'))) ?></p>
      <div class="d-flex justify-content-between align-items-center">
        <div class="small-muted">Ставка: <strong><?= number_format($job['wage'],0,'',' ') ?> ₽</strong> • Срок: <?= (int)$job['duration_days'] ?> дн.</div>
        <a class="btn btn-outline-primary btn-sm" href="job.php?id=<?= (int)$job['id'] ?>">Откликнуться</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>
