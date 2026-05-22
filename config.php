<?php
if (!defined('APP_INIT')) {
    die('Direct access not allowed');
}

define('DB_HOST', 'mysql.web-prj.ru');
define('DB_NAME', 'autosel');
define('DB_USER', 'autosel');
define('DB_PASS', 'Me47682$');
define('DB_CHARSET', 'utf8mb4');

define('SESSION_LIFETIME', 3600);

ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);


define('MAX_LOGIN_ATTEMPTS', 5);


define('EDITOR_EMAIL', 'redakciya@autosel.local');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

date_default_timezone_set('Europe/Moscow');

function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            if (ini_get('display_errors')) {
                die("Database connection failed. Please check configuration.");
            } else {
                die("Service temporarily unavailable.");
            }
        }
    }
    
    return $pdo;
}
