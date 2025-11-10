<?php include 'header.php'; require_login(); if(!is_employer()){ echo "<div class='alert alert-danger'>Доступ только для работодателей.</div>"; include 'footer.php'; exit; }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $title=trim($_POST['title']??''); $desc=trim($_POST['description']??''); $loc=trim($_POST['location']??'');
  $wage=(float)($_POST['wage']??0); $days=(int)($_POST['duration_days']??1);
  if($title && $wage>0){
    $stmt=db()->prepare("INSERT INTO jobs (employer_id,title,description,location,wage,duration_days) VALUES(?,?,?,?,?,?)");
    $stmt->execute([$u['id'],$title,$desc,$loc,$wage,$days]);
    echo "<div class='alert alert-success'>Вакансия опубликована!</div>";
  } else { echo "<div class='alert alert-danger'>Заполните поля «Название» и «Ставка».</div>"; }
}
?>
<div class="card p-3">
  <h4>Новая краткосрочная вакансия</h4>
  <form method="post">
    <div class="row g-2">
      <div class="col-md-6"><label class="form-label">Название</label><input class="form-control" name="title" required></div>
      <div class="col-md-3"><label class="form-label">Ставка (₽)</label><input class="form-control" type="number" name="wage" required></div>
      <div class="col-md-3"><label class="form-label">Срок, дней</label><input class="form-control" type="number" name="duration_days" value="1" min="1"></div>
    </div>
    <div class="row g-2 mt-1">
      <div class="col-md-6"><label class="form-label">Место работы</label><input class="form-control" name="location" placeholder="Город, адрес или объект"></div>
    </div>
    <label class="form-label mt-2">Описание</label>
    <textarea class="form-control" name="description" rows="4" placeholder="Что нужно сделать, какие требования..."></textarea>
    <button class="btn btn-success mt-3">Опубликовать</button>
  </form>
</div>
<?php include 'footer.php'; ?>
