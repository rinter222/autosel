<?php

session_start();

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    // setcookie с временем в прошлом удаляет куку
    // Параметры: имя, значение, время истечения, путь
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header('Location: index.html?logged_out=1');

exit();
?>
