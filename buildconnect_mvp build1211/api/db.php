<?php
require_once __DIR__ . '/config.php';
function pdo() {
  static $pdo=null; if($pdo) return $pdo;
  if (DB_DRIVER==='mysql'){
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC));
  } else {
    $dsn = 'sqlite:'.__DIR__.'/database.sqlite';
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  }
  migrate($pdo);
  return $pdo;
}
function addColumnIfNotExists($db, $table, $column, $type) { try { $db->exec("ALTER TABLE $table ADD COLUMN $column $type"); } catch(Exception $e) {} }
function migrate($db){
  $db->exec("CREATE TABLE IF NOT EXISTS users(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role TEXT NOT NULL,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    pass_hash TEXT NOT NULL,
    phone TEXT, contact TEXT, avatar TEXT,
    education TEXT, exp_years INTEGER DEFAULT 0, rating REAL DEFAULT 0,
    deposit_balance REAL DEFAULT 0, company_balance REAL DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
  );");
  addColumnIfNotExists($db,'users','contact','TEXT');
  addColumnIfNotExists($db,'users','avatar','TEXT');
  $db->exec("CREATE TABLE IF NOT EXISTS jobs(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employer_id INTEGER NOT NULL,
    title TEXT NOT NULL, description TEXT, city TEXT, specialization TEXT,
    wage REAL DEFAULT 0, pay_type TEXT DEFAULT 'fixed', duration_days INTEGER DEFAULT 1,
    status TEXT DEFAULT 'open', created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(employer_id) REFERENCES users(id)
  );");
  addColumnIfNotExists($db,'jobs','status',"TEXT DEFAULT 'open'");
  $db->exec("CREATE TABLE IF NOT EXISTS applications(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL, worker_id INTEGER NOT NULL,
    note TEXT, status TEXT DEFAULT 'applied', created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(job_id) REFERENCES jobs(id), FOREIGN KEY(worker_id) REFERENCES users(id)
  );");
  try { $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_app_unique ON applications(job_id, worker_id)"); } catch(Exception $e){}
  $db->exec("CREATE TABLE IF NOT EXISTS transactions(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL, type TEXT NOT NULL, amount REAL NOT NULL, ref TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY(user_id) REFERENCES users(id)
  );");
}
function user(){ return isset($_SESSION['user'])?$_SESSION['user']:null; }
function refresh_user(){ if(!user()) return null; $_SESSION['user']=pdo()->query('SELECT * FROM users WHERE id='.(int)$_SESSION['user']['id'])->fetch(); return $_SESSION['user']; }
function require_auth(){ if(!user()){ http_response_code(401); echo json_encode(array('ok'=>false,'error'=>'auth')); exit; } }
