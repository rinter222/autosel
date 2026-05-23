<?php
require_once __DIR__ . '/config.php';

function registerUser($username, $email, $password, $user_group = 'group2') {
    $conn = getDbConnection();
    
    if (!$conn) {
        return [
            'success' => false,
            'message' => 'Ошибка подключения к базе данных',
            'user_id' => null
        ];
    }
    
    // Проверка username
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
    
    // Проверка email
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
    
    // Регистрация пользователя
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
    
    $stmt = $conn->prepare("SELECT id, username, email, password_hash, user_group, created_at, last_login FROM users WHERE username = ? OR email = ?");
    
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
    
    // Проверка пароля
    if (!password_verify($password, $user['password_hash'])) {
        return [
            'success' => false,
            'user' => null
        ];
    }
    
    // Обновление last_login
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Удаляем хеш пароля из результата
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
    
    $stmt = $conn->prepare("SELECT id, username, email, user_group, created_at, last_login FROM users ORDER BY created_at DESC");
    
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
    
    $stmt = $conn->prepare("SELECT id, subject, content_text, status, submitted_at FROM content_submissions WHERE user_id = ? ORDER BY submitted_at DESC");
    
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
            'pages' => []
        ];
    }
    
    // Общая статистика
    $result = $conn->query("SELECT SUM(visit_count) as total_visits FROM visit_stats");
    
    if (!$result) {
        return [
            'total_visits' => 0,
            'pages' => []
        ];
    }
    
    $stats = $result->fetch_assoc();
    $total_visits = (int)($stats['total_visits'] ?? 0);
    
    // Статистика по страницам
    $stmt = $conn->prepare("SELECT page_url, visit_count, last_visit FROM visit_stats ORDER BY visit_count DESC");
    
    if (!$stmt) {
        return [
            'total_visits' => $total_visits,
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
        'total_visits' => $total_visits,
        'pages' => $pages
    ];
}
?>