<?php

if (!defined('DB_HOST')) {
    require_once 'config.php';
}


function recordVisit($page_url = null) {
    if ($page_url === null) {
        $page_url = $_SERVER['REQUEST_URI'] ?? 'index.html';
        $page_url = parse_url($page_url, PHP_URL_PATH);
        $page_url = $page_url ?: 'index.html';
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    

    if ($conn->connect_error) {
        return false;
    }
    
    $conn->set_charset(DB_CHARSET ?? 'utf8mb4');
    
    $stmt = $conn->prepare("
        INSERT INTO visit_stats (page_url, visit_count, last_visit) 
        VALUES (?, 1, NOW())
        ON DUPLICATE KEY UPDATE 
            visit_count = visit_count + 1,
            last_visit = NOW()
    ");
    
    if (!$stmt) {
        $conn->close();
        return false;
    }
    
    $stmt->bind_param("s", $page_url);
    $success = $stmt->execute();

    $stmt->close();
    $conn->close();
    
    return $success;
}

recordVisit();

function getVisitStats() {
    if (!defined('DB_HOST')) {
        require_once 'config.php';
    }
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        return ['total_visits' => 0, 'pages' => []];
    }
    
    $conn->set_charset(DB_CHARSET ?? 'utf8mb4');
    
    $total = $conn->query("SELECT SUM(visit_count) as total FROM visit_stats");
    $total_visits = $total ? ($total->fetch_assoc()['total'] ?? 0) : 0;

    $pages = [];
    $result = $conn->query("SELECT page_url, visit_count, last_visit FROM visit_stats ORDER BY visit_count DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pages[] = $row;
        }
    }
    
    $conn->close();
    
    return [
        'total_visits' => $total_visits,
        'pages' => $pages
    ];
}
?>
