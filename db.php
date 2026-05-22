<?php

require_once __DIR__ . '/config.php';

function getDbConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Ошибка подключения к БД: " . $conn->connect_error);
            return null;
        }
        
        $conn->set_charset(DB_CHARSET);
        $conn->options(MYSQLI_INIT_COMMAND, "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    }
    
    return $conn;
}

function registerUser($username, $email, $password, $user_group = 'group2') {
    $conn = getDbConnection();
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Ошибка подключения к базе данных',
            'user_id' => null
        ];
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Ошибка подготовки запроса',
            'user_id' => null
        ];
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Пользователь с таким именем уже существует',
            'user_id' => null
        ];
    }
    $stmt->close();
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Ошибка подготовки запроса',
            'user_id' => null
        ];
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Пользователь с таким email уже существует',
            'user_id' => null
        ];
    }
    $stmt->close();
    
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, user_group, created_at) VALUES (?, ?, ?, ?, NOW())");
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Ошибка подготовки запроса',
            'user_id' => null
        ];
    }
    
    $stmt->bind_param("ssss", $username, $email, $password_hash, $user_group);
    
    if ($stmt->execute()) {
        $user_id = $conn->insert_id;
        $stmt->close();
        
        return [
            'success' => true,
            'message' => 'Пользователь успешно зарегистрирован',
            'user_id' => $user_id
        ];
    } else {
        error_log("Ошибка регистрации пользователя: " . $stmt->error);
        $stmt->close();
        
        return [
            'success' => false,
            'message' => 'Ошибка при регистрации пользователя',
            'user_id' => null
        ];
    }
}

function loginUser($login, $password) {
    $conn = getDbConnection();
    
    if (!$conn) {
        return [
            'success' => false,
            'user' => null
        ];
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, password_hash, user_group, is_active, last_login FROM users WHERE username = ? OR email = ?");
    if (!$stmt) {
        return [
            'success' => false,
            'user' => null
        ];
    }
    
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return [
            'success' => false,
            'user' => null
        ];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!$user['is_active']) {
        return [
            'success' => false,
            'user' => null
        ];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return [
            'success' => false,
            'user' => null
        ];
    }
    
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    unset($user['password_hash']);
    
    return [
        'success' => true,
        'user' => $user
    ];
}


function getAllUsers() {
    $conn = getDbConnection();
    
    if (!$conn) {
        return [];
    }
    
    $stmt = $conn->prepare("SELECT id, username, email, user_group, is_active, created_at, last_login FROM users ORDER BY created_at DESC");
    if (!$stmt) {
        return [];
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    
    return $users;
}


function getUserSubmissions($user_id) {
    $conn = getDbConnection();
    
    if (!$conn) {
        return [];
    }
    
    $stmt = $conn->prepare("SELECT id, title, content_type, status, submitted_at, reviewed_at, reviewer_notes FROM content_submissions WHERE user_id = ? ORDER BY submitted_at DESC");
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $submissions = [];
    while ($row = $result->fetch_assoc()) {
        $submissions[] = $row;
    }
    
    $stmt->close();
    
    return $submissions;
}


function getVisitStatistics() {
    $conn = getDbConnection();
    
    if (!$conn) {
        return [
            'total_visits' => 0,
            'unique_visitors' => 0,
            'pages' => []
        ];
    }
    
  
    $result = $conn->query("SELECT COUNT(*) as total_visits, COUNT(DISTINCT ip_address) as unique_visitors FROM visit_stats");
    
    if (!$result) {
        return [
            'total_visits' => 0,
            'unique_visitors' => 0,
            'pages' => []
        ];
    }
    
    $stats = $result->fetch_assoc();
    
   
    $stmt = $conn->prepare("SELECT page_url, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors, MAX(visited_at) as last_visit FROM visit_stats GROUP BY page_url ORDER BY visits DESC");
    if (!$stmt) {
        return [
            'total_visits' => (int)$stats['total_visits'],
            'unique_visitors' => (int)$stats['unique_visitors'],
            'pages' => []
        ];
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pages = [];
    while ($row = $result->fetch_assoc()) {
        $pages[] = $row;
    }
    
    $stmt->close();
    
    return [
        'total_visits' => (int)$stats['total_visits'],
        'unique_visitors' => (int)$stats['unique_visitors'],
        'pages' => $pages
    ];
}
