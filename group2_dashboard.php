<?php
require_once 'config.php';
require_once 'db.php';
session_start();

// 🔒 Проверка доступа: только для пользователей группы 2
if (!isset($_SESSION['user_id']) || $_SESSION['user_group'] !== 'group2') {
    header('Location: auth.php');
    exit();
}

$successMsg = '';
$errorMsg = '';

// 📩 Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_content'])) {
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    $editorEmail = trim($_POST['editor_email']);

    // Валидация длины текста (mb_strlen для корректного учёта кириллицы)
    $contentLength = mb_strlen($content);
    
    if ($contentLength < 500) {
        $errorMsg = 'Ошибка: текст должен содержать минимум 500 символов (сейчас: ' . $contentLength . ')';
    } elseif ($contentLength > 10000) {
        $errorMsg = 'Ошибка: текст не должен превышать 10000 символов';
    } elseif (empty($subject)) {
        $errorMsg = 'Ошибка: укажите тему материала';
    } else {
        // 🔗 Подключение к БД и сохранение данных
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            $errorMsg = 'Ошибка подключения к базе данных';
        } else {
            // Подготовленный запрос (защита от SQL-инъекций)
            $stmt = $conn->prepare("
                INSERT INTO content_submissions 
                (user_id, subject, content_text, editor_email, status, submitted_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            
            if ($stmt) {
                $stmt->bind_param("isss", $_SESSION['user_id'], $subject, $content, $editorEmail);
                
                if ($stmt->execute()) {
                    $successMsg = 'Материал успешно отправлен редактору на проверку!';
                    // Очистка формы после успешной отправки
                    $_POST = [];
                } else {
                    $errorMsg = 'Ошибка при сохранении в базу данных: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $errorMsg = 'Ошибка подготовки запроса: ' . $conn->error;
            }
            $conn->close();
        }
    }
}

// 📜 Получение истории отправок текущего пользователя
$history = [];
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn->connect_error) {
    $stmt = $conn->prepare("
        SELECT id, subject, status, submitted_at 
        FROM content_submissions 
        WHERE user_id = ? 
        ORDER BY submitted_at DESC
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отправка материала - Autosel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Встроенные стили для страницы группы 2 */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
        }
        .dashboard-header {
            background: #333;
            color: #fff;
            padding: 1rem 0;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .header-content .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .header-content nav {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .header-content nav a {
            color: #fff;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .header-content nav a:hover {
            opacity: 0.8;
        }
        .user-info {
            color: #ccc;
            font-size: 0.95rem;
        }
        
        .main-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-title {
            margin: 0 0 1.5rem 0;
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 0.5rem;
        }
        
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 6px;
            border-left: 4px solid;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }
        
        .submission-form {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #444;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 150px;
            font-family: inherit;
        }
        
        .char-counter {
            display: block;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.3rem;
        }
        
        .char-counter.warning {
            color: #dc3545;
            font-weight: 600;
        }
        
        .form-hint {
            display: block;
            font-size: 0.8rem;
            color: #888;
            margin-top: 0.25rem;
        }
        
        .btn-submit {
            background: #007bff;
            color: #fff;
            padding: 0.85rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-submit:hover {
            background: #0056b3;
        }
        
        .btn-submit:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .section-title {
            margin: 2.5rem 0 1rem 0;
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .history-table th,
        .history-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .history-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .history-table tr:last-child td {
            border-bottom: none;
        }
        
        .history-table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #333;
        }
        
        .badge-approved {
            background: #28a745;
        }
        
        .badge-rejected {
            background: #dc3545;
        }
        
        .empty-history {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
            font-style: italic;
        }
        
        .btn-logout {
            display: inline-block;
            padding: 0.5rem 1.2rem;
            background: #dc3545;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .btn-logout:hover {
            background: #c82333;
        }
        
        footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .main-container {
                padding: 0 0.75rem;
            }
            
            .submission-form,
            .history-table {
                border-radius: 6px;
            }
            
            .history-table th,
            .history-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

    <!-- Шапка сайта -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo">Autosel</div>
            <nav>
                <a href="index.html">Главная</a>
                <a href="about.html">О нас</a>
                <a href="contacts.html">Контакты</a>
                <span style="color: #666;">|</span>
                <span class="user-info">👤 <?php echo htmlspecialchars($_SESSION['username']); ?> (Группа 2)</span>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Отправка материала на проверку редактору</h1>

        <!-- Сообщения об успехе/ошибке -->
        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <!-- Форма отправки контента -->
        <div class="submission-form">
            <form method="POST" action="" id="submissionForm">
                <div class="form-group">
                    <label for="subject">Тема материала *</label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           placeholder="Введите тему материала..." 
                           required 
                           maxlength="200"
                           value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="content">Текст материала *</label>
                    <textarea id="content" 
                              name="content" 
                              placeholder="Ваш материал (минимум 500 символов)..." 
                              required 
                              minlength="500" 
                              maxlength="10000"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    <span class="char-counter" id="charCounter">Введено символов: 0 / 10000</span>
                    <small class="form-hint">* Минимальная длина: 500 символов. Максимальная: 10000</small>
                </div>

                <div class="form-group">
                    <label for="editor_email">Email редактора *</label>
                    <input type="email" 
                           id="editor_email" 
                           name="editor_email" 
                           value="redakciya@autosel.local" 
                           required>
                    <small class="form-hint">Материал будет сохранён в базе для проверки редактором</small>
                </div>

                <button type="submit" name="submit_content" class="btn-submit" id="submitBtn">
                    Отправить на проверку
                </button>
                
                <p style="margin-top: 1rem; font-size: 0.85rem; color: #6c757d;">
                    ℹ️ Согласно заданию, почтовый сервер не настраивается. 
                    Данные сохраняются в базу данных для последующей проверки редактором.
                </p>
            </form>
        </div>

        <!-- История отправок -->
        <h2 class="section-title">История отправок</h2>
        
        <?php if (!empty($history)): ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Дата отправки</th>
                        <th>Тема</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): 
                        $status = $item['status'];
                        $badgeClass = 'badge-pending';
                        $statusText = 'На проверке';
                        
                        if ($status === 'approved') {
                            $badgeClass = 'badge-approved';
                            $statusText = 'Одобрено';
                        } elseif ($status === 'rejected') {
                            $badgeClass = 'badge-rejected';
                            $statusText = 'Отклонено';
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['submitted_at']); ?></td>
                        <td><?php echo htmlspecialchars($item['subject']); ?></td>
                        <td>
                            <span class="badge <?php echo $badgeClass; ?>">
                                <?php echo htmlspecialchars($statusText); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-history">
                Вы пока не отправляли материалов на проверку
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Autosel. Все права защищены.</p>
    </footer>

    <script>
        // ⚡ Подсчет символов в textarea в реальном времени
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('content');
            const counter = document.getElementById('charCounter');
            const submitBtn = document.getElementById('submitBtn');
            const minLength = 500;
            const maxLength = 10000;

            function updateCounter() {
                const currentLength = textarea.value.length;
                
                // Обновляем текст счетчика
                counter.textContent = `Введено символов: ${currentLength} / ${maxLength}`;
                
                // Подсветка предупреждения, если меньше минимума
                if (currentLength < minLength) {
                    counter.classList.add('warning');
                    counter.textContent += ` (нужно ещё ${minLength - currentLength})`;
                } else {
                    counter.classList.remove('warning');
                }
                
                // Блокировка кнопки отправки, если текст слишком короткий
                submitBtn.disabled = (currentLength < minLength || currentLength > maxLength);
            }

            // Слушаем ввод текста
            textarea.addEventListener('input', updateCounter);
            
            // Инициализация при загрузке
            updateCounter();

            // Дополнительная валидация перед отправкой формы
            document.getElementById('submissionForm').addEventListener('submit', function(e) {
                const length = textarea.value.length;
                
                if (length < minLength) {
                    e.preventDefault();
                    alert(`Текст должен содержать минимум ${minLength} символов!\nСейчас: ${length}`);
                    textarea.focus();
                    return false;
                }
                
                if (length > maxLength) {
                    e.preventDefault();
                    alert(`Текст не должен превышать ${maxLength} символов!\nСейчас: ${length}`);
                    return false;
                }
            });
        });
    </script>
</body>
</html>
