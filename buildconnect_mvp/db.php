<?php
require_once __DIR__ . '/config.php';
function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;
  if (DB_DRIVER === 'mysql') {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  } else {
    $dbFile = __DIR__ . '/database.sqlite';
    $dsn = "sqlite:" . $dbFile;
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
  initialize_schema($pdo);
  return $pdo;
}
function initialize_schema(PDO $pdo) {
  $pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    phone TEXT,
    role TEXT NOT NULL CHECK (role IN ('worker','employer','admin')),
    education TEXT,
    experience_years INTEGER DEFAULT 0,
    password_hash TEXT NOT NULL,
    rating REAL DEFAULT 0,
    deposit_balance REAL DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
  );");
  $pdo->exec("CREATE TABLE IF NOT EXISTS jobs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employer_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    location TEXT,
    wage REAL DEFAULT 0,
    duration_days INTEGER DEFAULT 1,
    status TEXT NOT NULL DEFAULT 'open',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employer_id) REFERENCES users(id)
  );");
  $pdo->exec("CREATE TABLE IF NOT EXISTS applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    job_id INTEGER NOT NULL,
    worker_id INTEGER NOT NULL,
    cover_note TEXT,
    status TEXT NOT NULL DEFAULT 'applied',
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id),
    FOREIGN KEY (worker_id) REFERENCES users(id)
  );");
  $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type TEXT NOT NULL,
    amount REAL NOT NULL,
    ref TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
  );");
}
function current_user() { session_start(); return $_SESSION['user'] ?? null; }
function require_login() { if(!current_user()){ header("Location: auth.php"); exit; } }
function is_employer(){ $u=current_user(); return $u && $u['role']==='employer'; }
function is_worker(){ $u=current_user(); return $u && $u['role']==='worker'; }
function is_admin(){ $u=current_user(); return $u && $u['role']==='admin'; }
function find_user($id){ $s=db()->prepare("SELECT * FROM users WHERE id=?"); $s->execute([$id]); return $s->fetch(); }
function refresh_session_user($id){ session_start(); $_SESSION['user']=find_user($id); }
?>