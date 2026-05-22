<?php
/**
 * Configuration file for AutoSel website
 * 
 * This file contains database connection settings and application constants.
 * Include this file at the beginning of each PHP script.
 */

// Prevent direct access
if (!defined('APP_INIT')) {
    die('Direct access not allowed');
}

// ============================================
// DATABASE CONFIGURATION
// ============================================
define('DB_HOST', 'mysql.web-prj.ru');
define('DB_NAME', 'autosel');
define('DB_USER', 'YOUR_FTP_LOGIN');      // Replace with your FTP login
define('DB_PASS', 'YOUR_FTP_PASSWORD');   // Replace with your FTP password
define('DB_CHARSET', 'utf8mb4');

// ============================================
// SESSION SETTINGS
// ============================================
define('SESSION_LIFETIME', 3600); // Session lifetime in seconds (1 hour)

// Configure session settings
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);

// ============================================
// SECURITY SETTINGS
// ============================================
define('MAX_LOGIN_ATTEMPTS', 5); // Maximum login attempts before lockout

// ============================================
// APPLICATION SETTINGS
// ============================================
define('EDITOR_EMAIL', 'redakciya@autosel.local');

// ============================================
// ERROR REPORTING (Development Mode)
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// ============================================
// TIMEZONE SETTINGS
// ============================================
date_default_timezone_set('Europe/Moscow');

// ============================================
// DATABASE CONNECTION FUNCTION
// ============================================
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
