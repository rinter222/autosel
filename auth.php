<?php
define('APP_INIT', true);
require_once 'config.php';
require_once 'db.php';

session_start();

// Генерация CSRF-токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Проверка авторизованного пользователя
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_group'] === 'group1') {
        header('Location: group1_dashboard.php');
        exit;
    } elseif ($_SESSION['user_group'] === 'group2') {
        header('Location: group2_dashboard.php');
        exit;
    }
}

$errors = [];
$success = '';

// Rate limiting для входа
function checkRateLimit() {
    $maxAttempts = MAX_LOGIN_ATTEMPTS;
    $lockoutTime = 60; // 1 минута
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }
    
    if (time() - $_SESSION['last_attempt'] > $lockoutTime) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt'] = time();
    }
    
    if ($_SESSION['login_attempts'] >= $maxAttempts) {
        return false;
    }
    
    return true;
}

function incrementLoginAttempt() {
    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt'] = time();
}

function resetLoginAttempts() {
    $_SESSION['login_attempts'] = 0;
}

// Обработка POST запроса входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    // Проверка CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Неверный CSRF-токен';
    } elseif (!checkRateLimit()) {
        $errors[] = 'Слишком много попыток входа. Попробуйте через минуту.';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($login) || empty($password)) {
            $errors[] = 'Заполните все поля';
        } else {
            $result = loginUser($login, $password);
            
            if ($result['success']) {
                resetLoginAttempts();
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['username'] = htmlspecialchars($result['user']['username']);
                $_SESSION['email'] = htmlspecialchars($result['user']['email']);
                $_SESSION['user_group'] = $result['user']['user_group'];
                
                if ($result['user']['user_group'] === 'group1') {
                    header('Location: group1_dashboard.php');
                } else {
                    header('Location: group2_dashboard.php');
                }
                exit;
            } else {
                incrementLoginAttempt();
                $errors[] = 'Неправильный логин или пароль';
            }
        }
    }
}

// Обработка POST запроса регистрации
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // Проверка CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Неверный CSRF-токен';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $userGroup = $_POST['user_group'] ?? 'group2';
        
        // Валидация username
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors[] = 'Имя пользователя должно быть от 3 до 50 символов';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Имя пользователя может содержать только буквы, цифры и подчеркивание';
        }
        
        // Валидация email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Неверный формат email';
        }
        
        // Валидация пароля
        if (strlen($password) < 8) {
            $errors[] = 'Пароль должен быть не менее 8 символов';
        }
        
        // Проверка совпадения паролей
        if ($password !== $confirmPassword) {
            $errors[] = 'Пароли не совпадают';
        }
        
        // Валидация группы
        if (!in_array($userGroup, ['group1', 'group2'])) {
            $errors[] = 'Неверная группа пользователей';
        }
        
        if (empty($errors)) {
            $result = registerUser($username, $email, $password, $userGroup);
            
            if ($result['success']) {
                $success = 'Регистрация успешна! Теперь вы можете войти.';
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход и регистрация - AutoSel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .auth-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #e74c3c;
        }
        
        .auth-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            font-weight: bold;
            color: #666;
            transition: all 0.3s;
        }
        
        .auth-tab.active {
            color: #e74c3c;
            border-bottom: 3px solid #e74c3c;
            margin-bottom: -2px;
        }
        
        .auth-tab:hover {
            color: #e74c3c;
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #e74c3c;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-submit:hover {
            background: #c0392b;
        }
        
        .error-message {
            background: #fee;
            color: #c0392b;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c0392b;
        }
        
        .success-message {
            background: #efe;
            color: #27ae60;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #27ae60;
        }
        
        .password-strength {
            margin-top: 5px;
            font-size: 14px;
            color: #666;
        }
        
        .password-strength.weak { color: #e74c3c; }
        .password-strength.medium { color: #f39c12; }
        .password-strength.strong { color: #27ae60; }
    </style>
</head>
<body>
    <main class="container">
        <div class="auth-container">
            <h1 style="text-align: center; margin-bottom: 30px;">Личный кабинет</h1>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <?php echo htmlspecialchars($error); ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-message">
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="auth-tabs">
                <div class="auth-tab active" data-tab="login">Вход</div>
                <div class="auth-tab" data-tab="register">Регистрация</div>
            </div>
            
            <!-- Форма входа -->
            <form class="auth-form active" method="POST" action="">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="login">Логин или Email</label>
                    <input type="text" id="login" name="login" required 
                           placeholder="Введите имя пользователя или email">
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Введите пароль">
                </div>
                
                <button type="submit" class="btn-submit">Войти</button>
            </form>
            
            <!-- Форма регистрации -->
            <form class="auth-form" method="POST" action="">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="reg-username">Имя пользователя</label>
                    <input type="text" id="reg-username" name="username" required 
                           minlength="3" maxlength="50"
                           placeholder="От 3 до 50 символов">
                    <small style="color: #666;">Только буквы, цифры и подчеркивание</small>
                </div>
                
                <div class="form-group">
                    <label for="reg-email">Email</label>
                    <input type="email" id="reg-email" name="email" required 
                           placeholder="example@mail.ru">
                </div>
                
                <div class="form-group">
                    <label for="reg-password">Пароль</label>
                    <input type="password" id="reg-password" name="password" required 
                           minlength="8" placeholder="Минимум 8 символов">
                    <div class="password-strength" id="password-strength"></div>
                </div>
                
                <div class="form-group">
                    <label for="reg-confirm-password">Подтверждение пароля</label>
                    <input type="password" id="reg-confirm-password" name="confirm_password" required 
                           placeholder="Повторите пароль">
                </div>
                
                <div class="form-group">
                    <label for="reg-user-group">Группа пользователей</label>
                    <select id="reg-user-group" name="user_group" required>
                        <option value="group2">Группа 2 - Отправка материалов</option>
                        <option value="group1">Группа 1 - Просмотр статистики</option>
                    </select>
                    <small style="color: #666;">
                        Group1: доступ к статистике<br>
                        Group2: возможность отправлять материалы
                    </small>
                </div>
                
                <button type="submit" class="btn-submit">Зарегистрироваться</button>
            </form>
        </div>
    </main>
    
    <script>
        // Переключение вкладок
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.dataset.tab;
                
                // Убираем активный класс со всех вкладок и форм
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
                
                // Добавляем активный класс текущей вкладке и форме
                this.classList.add('active');
                document.querySelector(`.auth-form[data-tab="${tabName}"]`)?.classList.add('active');
                document.querySelector(`form:nth-child(${tabName === 'login' ? '4' : '5'})`)?.classList.add('active');
                
                // Простое переключение по индексу
                if (tabName === 'login') {
                    document.querySelectorAll('.auth-form')[0].classList.add('active');
                } else {
                    document.querySelectorAll('.auth-form')[1].classList.add('active');
                }
            });
        });
        
        // Подсчет силы пароля
        document.getElementById('reg-password')?.addEventListener('input', function() {
            const password = this.value;
            const strengthEl = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthEl.textContent = '';
                strengthEl.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                strengthEl.textContent = 'Слабый пароль';
                strengthEl.className = 'password-strength weak';
            } else if (strength <= 3) {
                strengthEl.textContent = 'Средний пароль';
                strengthEl.className = 'password-strength medium';
            } else {
                strengthEl.textContent = 'Сильный пароль';
                strengthEl.className = 'password-strength strong';
            }
        });
        
        // Валидация перед отправкой формы регистрации
        document.querySelectorAll('.auth-form')[1]?.addEventListener('submit', function(e) {
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('reg-confirm-password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Пароли не совпадают!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Пароль должен быть не менее 8 символов!');
                return false;
            }
        });
        
        // Исправление переключения вкладок
        document.querySelectorAll('.auth-tab').forEach((tab, index) => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.auth-form').forEach(f => f.classList.remove('active'));
                
                this.classList.add('active');
                document.querySelectorAll('.auth-form')[index].classList.add('active');
            });
        });
    </script>
</body>
</html>
