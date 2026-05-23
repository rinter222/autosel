<?php
define('APP_INIT', true);
require_once 'config.php';

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strpos($_SERVER['REQUEST_URI'], 'counter.php') !== false) {
    header('Content-Type: text/html; charset=utf-8');
} else {
    header('Content-Type: application/json; charset=utf-8');
}

if (!defined('DB_HOST')) {
    require_once 'config.php';
}

$page_url = $_SERVER['HTTP_REFERER'] ?? $_SERVER['REQUEST_URI'] ?? 'index.html';
$page_url = parse_url($page_url, PHP_URL_PATH);
$page_url = $page_url ?: 'index.html';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Если ошибка подключения - возвращаем 0
    if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        echo json_encode(['count' => 0, 'error' => 'DB connection failed']);
    } else {
        echo "0";
    }
    exit();
}

$conn->set_charset(DB_CHARSET ?? 'utf8mb4');

$stmt = $conn->prepare("
    INSERT INTO visit_stats (page_url, visit_count, last_visit) 
    VALUES (?, 1, NOW())
    ON DUPLICATE KEY UPDATE 
        visit_count = visit_count + 1,
        last_visit = NOW()
");

if ($stmt) {
    $stmt->bind_param("s", $page_url);
    $stmt->execute();
    $stmt->close();
}

$result = $conn->query("SELECT SUM(visit_count) as total FROM visit_stats");
$total_visits = 0;
if ($result) {
    $row = $result->fetch_assoc();
    $total_visits = $row['total'] ?? 0;
}

$pages_result = $conn->query("SELECT COUNT(*) as pages FROM visit_stats");
$total_pages = 0;
if ($pages_result) {
    $row = $pages_result->fetch_assoc();
    $total_pages = $row['pages'] ?? 0;
}

$conn->close();

if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false || 
    isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    echo json_encode([
        'count' => (int)$total_visits,
        'pages' => (int)$total_pages,
        'current_page' => $page_url
    ]);
} else {
    echo (int)$total_visits;
}
?>