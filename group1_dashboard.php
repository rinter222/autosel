<?php
define('APP_INIT', true);
require_once 'config.php';
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_group'] !== 'group1') {
    header('Location: auth.php');
    exit();
}


$users = getAllUsers();
$stats = getVisitStatistics();

if (is_array($users)) {
    usort($users, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

function isUserOnline($lastLogin) {
    if (!$lastLogin) return false;
    $lastTime = strtotime($lastLogin);
    return (time() - $lastTime) < 300;
}

$totalVisits = $stats['total_visits'] ?? 0;
$pages = $stats['pages'] ?? [];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - Статистика сайта</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header style="background: #333; color: #fff; padding: 1rem 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div style="font-size: 1.5rem; font-weight: bold;">Autosel</div>
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

    <main class="dashboard-wrapper">
        <div class="dashboard-header">
            <h1>Панель администратора - Статистика сайта</h1>
            <a href="logout.php" class="btn-logout" style="margin:0;">Выйти</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo number_format($totalVisits); ?></h3>
                <p>Общее число посещений</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($pages); ?></h3>
                <p>Отслеживаемых страниц</p>
            </div>
            <div class="stat-card">
                <h3><?php echo is_array($users) ? count($users) : 0; ?></h3>
                <p>Зарегистрированных пользователей</p>
            </div>
        </div>

        <h2 class="section-title">Список всех пользователей</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Группа</th>
                        <th>Дата регистрации</th>
                        <th>Последний вход</th>
                        <th>Статус</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (is_array($users) && !empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['user_group']); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td><?php echo $user['last_login'] ? htmlspecialchars($user['last_login']) : '—'; ?></td>
                            <td class="<?php echo isUserOnline($user['last_login']) ? 'status-online' : 'status-offline'; ?>">
                                <?php echo isUserOnline($user['last_login']) ? '● Онлайн' : '○ Офлайн'; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; color:#6c757d;">Пользователи не найдены</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h2 class="section-title">Статистика посещений</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Страница</th>
                        <th>Посещения</th>
                        <th>Последнее посещение</th>
                        <th>Доля от общего</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pages)): ?>
                        <?php foreach ($pages as $page): 
                            $percent = $totalVisits > 0 ? ($page['visit_count'] / $totalVisits) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($page['page_url']); ?></td>
                            <td><?php echo number_format($page['visit_count']); ?></td>
                            <td><?php echo htmlspecialchars($page['last_visit']); ?></td>
                            <td class="progress-cell">
                                <div class="progress-bg">
                                    <div class="progress-fill" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                                <span class="progress-text"><?php echo number_format($percent, 1); ?>%</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center; color:#6c757d;">Статистика посещений пока отсутствует</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer style="background: #333; color: #fff; text-align: center; padding: 1rem; margin-top: 3rem;">
        <p>&copy; <?php echo date('Y'); ?> Autosel. Все права защищены.</p>
    </footer>

</body>
</html>
