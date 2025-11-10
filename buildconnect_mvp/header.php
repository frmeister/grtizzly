<?php require_once __DIR__ . '/db.php'; $u=current_user(); ?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= APP_NAME ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom mb-3">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php"><?= APP_NAME ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Вакансии</a></li>
        <?php if ($u && $u['role']==='employer'): ?>
        <li class="nav-item"><a class="nav-link" href="post_job.php">Создать вакансию</a></li>
        <?php endif; ?>
        <?php if ($u): ?><li class="nav-item"><a class="nav-link" href="profile.php">Профиль</a></li><?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if ($u): ?>
          <li class="nav-item"><span class="navbar-text me-3">Здравствуйте, <?= htmlspecialchars($u['name']) ?> (<?= $u['role'] ?>)</span></li>
          <li class="nav-item"><a class="btn btn-outline-secondary" href="logout.php">Выход</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-primary" href="auth.php">Вход / Регистрация</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
