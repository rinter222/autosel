<?php

header('Content-Type: application/json; charset=utf-8');

session_start();

// Путь к файлу, где хранятся данные
$dataFile = __DIR__ . '/online.json';

// Пользователь считается "онлайн", если был активен за последние 90 секунд
$timeout = 90;
$now = time();

// Получаем существующие сессии
if (file_exists($dataFile)) {
    $online = json_decode(file_get_contents($dataFile), true) ?: [];
} else {
    $online = [];
}

// Удаляем устаревшие сессии
$online = array_filter($online, function($lastActive) use ($now, $timeout) {
    return ($now - $lastActive) <= $timeout;
});

// Обновляем текущего пользователя
$online[session_id()] = $now;

// Сохраняем обратно
file_put_contents($dataFile, json_encode($online, JSON_PRETTY_PRINT));

// Возвращаем количество
echo json_encode(['count' => count($online)]);