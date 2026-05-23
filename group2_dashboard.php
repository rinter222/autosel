<?php
define('APP_INIT', true);
require_once 'config.php';
require_once 'db.php';
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_group'] !== 'group2') {
    header('Location: auth.php');
    exit();
}

$successMsg = '';
$errorMsg = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_content'])) {
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    $editorEmail = trim($_POST['editor_email']);


    $contentLength = mb_strlen($content);
    
    if ($contentLength < 10) {
        $errorMsg = 'Ошибка: текст должен содержать минимум 10 символов (сейчас: ' . $contentLength . ')';
    } elseif ($contentLength > 10000) {
        $errorMsg = 'Ошибка: текст не должен превышать 10000 символов';
    } elseif (empty($subject)) {
        $errorMsg = 'Ошибка: укажите тему материала';
    } else {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            $errorMsg = 'Ошибка подключения к базе данных';
        } else {
            $stmt = $conn->prepare("
                INSERT INTO content_submissions 
                (user_id, subject, content_text, editor_email, status, submitted_at) 
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            
            if ($stmt) {
                $stmt->bind_param("isss", $_SESSION['user_id'], $subject, $content, $editorEmail);
                
                if ($stmt->execute()) {
                    $successMsg = 'Материал успешно отправлен редактору на проверку!';
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
</head>
<body>

    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo">Autosel</div>
            <nav>
                <a href="index.php" class="active">Главная</a>
                <a href="about.php">О нас</a>
                <a href="contacts.php">Контакты</a>
                <a href="promotions.php">Акции</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Пользователь авторизован -->
                    <?php if ($_SESSION['user_group'] === 'group1'): ?>
                        <a href="group1_dashboard.php" style="background: linear-gradient(45deg, #ffd700, #ff8c00); color: #1a2a6c;">
                            📊 Панель управления
                        </a>
                    <?php elseif ($_SESSION['user_group'] === 'group2'): ?>
                        <a href="group2_dashboard.php" style="background: linear-gradient(45deg, #ffd700, #ff8c00); color: #1a2a6c;">
                            ✍️ Отправить материал
                        </a>
                    <?php endif; ?>
                    
                    <span style="color: #ffd700;">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" style="background: rgba(220, 53, 69, 0.8);">Выйти</a>
                <?php else: ?>
                    <!-- Пользователь не авторизован -->
                    <a href="auth.php" style="background: linear-gradient(45deg, #ffd700, #ff8c00); color: #1a2a6c;">
                        🔐 Войти
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <h1 class="page-title">Отправка материала на проверку редактору</h1>

        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
        <?php endif; ?>
        <?php if ($errorMsg): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

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
                              placeholder="Ваш материал (минимум 10 символов)..." 
                              required 
                              minlength="10" 
                              maxlength="10000"><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                    <span class="char-counter" id="charCounter">Введено символов: 0 / 10000</span>
                    <small class="form-hint">* Минимальная длина: 10 символов. Максимальная: 10000</small>
                </div>

                <div class="form-group">
                    <label for="editor_email">Email редактора *</label>
                    <input type="email" 
                           id="editor_email" 
                           name="editor_email" 
                           value="admin@autosel.local" 
                           required>
                    <small class="form-hint">Материал будет сохранён в базе для проверки редактором</small>
                </div>

                <button type="submit" name="submit_content" class="btn-submit" id="submitBtn">
                    Отправить на проверку
                </button>
            </form>
        </div>

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
        document.addEventListener('DOMContentLoaded', function() {
            const textarea = document.getElementById('content');
            const counter = document.getElementById('charCounter');
            const submitBtn = document.getElementById('submitBtn');
            const minLength = 10;
            const maxLength = 10000;

            function updateCounter() {
                const currentLength = textarea.value.length;
                
                counter.textContent = `Введено символов: ${currentLength} / ${maxLength}`;
                

                if (currentLength < minLength) {
                    counter.classList.add('warning');
                    counter.textContent += ` (нужно ещё ${minLength - currentLength})`;
                } else {
                    counter.classList.remove('warning');
                }
                
                submitBtn.disabled = (currentLength < minLength || currentLength > maxLength);
            }

            textarea.addEventListener('input', updateCounter);
            
            updateCounter();

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
    <script src="counter.js"></script>
</body>
</html>
