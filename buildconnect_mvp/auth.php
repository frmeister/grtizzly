<?php include 'header.php';
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(isset($_POST['register'])){
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $pass=$_POST['password']??''; $role=$_POST['role']??'worker';
    if($name && $email && $pass){
      try{
        $stmt=db()->prepare("INSERT INTO users(name,email,role,password_hash) VALUES(?,?,?,?)");
        $stmt->execute([$name,$email,$role,password_hash($pass,PASSWORD_DEFAULT)]);
        $id=db()->lastInsertId(); refresh_session_user($id); header("Location: index.php"); exit;
      } catch(Exception $e){ $err="Такой email уже зарегистрирован."; }
    } else { $err="Заполните все поля."; }
  } else {
    $email=trim($_POST['email']??''); $pass=$_POST['password']??'';
    $stmt=db()->prepare("SELECT * FROM users WHERE email=?"); $stmt->execute([$email]); $user=$stmt->fetch();
    if($user && password_verify($pass,$user['password_hash'])){ session_start(); $_SESSION['user']=$user; header("Location: index.php"); exit; }
    else { $err="Неверный email или пароль."; }
  }
}
?>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3">
      <h4>Вход</h4>
      <?php if($err): ?><div class="alert alert-danger"><?= htmlspecialchars($err) ?></div><?php endif; ?>
      <form method="post">
        <input class="form-control mb-2" name="email" placeholder="Email">
        <input class="form-control mb-2" type="password" name="password" placeholder="Пароль">
        <button class="btn btn-primary w-100">Войти</button>
      </form>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h4>Регистрация</h4>
      <form method="post">
        <input class="form-control mb-2" name="name" placeholder="Ваше имя">
        <input class="form-control mb-2" name="email" placeholder="Email">
        <div class="mb-2">
          <label class="form-label">Роль</label>
          <select class="form-select" name="role">
            <option value="worker">Соискатель</option>
            <option value="employer">Работодатель</option>
          </select>
        </div>
        <input class="form-control mb-2" type="password" name="password" placeholder="Пароль">
        <button class="btn btn-success w-100" name="register">Зарегистрироваться</button>
      </form>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
