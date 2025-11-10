<?php
// ==== CONFIG ====
// По умолчанию SQLite. Можно переопределить через переменные окружения.

define('DB_DRIVER',  getenv('DB_DRIVER')  ?: 'sqlite');   // 'sqlite' или 'mysql'
define('DB_HOST',    getenv('DB_HOST')    ?: '127.0.0.1');
define('DB_NAME',    getenv('DB_NAME')    ?: 'buildconnect');
define('DB_USER',    getenv('DB_USER')    ?: 'root');
define('DB_PASS',    getenv('DB_PASS')    ?: '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'BuildConnect MVP');
define('BASE_URL', '');  // если сайт в подпапке, например '/buildconnect'