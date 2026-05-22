<?php
require_once 'config.php';
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_group'] !== 'group1') {
    header('Location: auth.php');
    exit();
}

// Получаем данные из БД
$users = getAllUsers();
$stats = getVisitStatistics();

// Гарантируем сортировку по дате регистрации (DESC), если db.php не делает этого
if (is_array($users)) {
    usort($users, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
}

// Функция проверки статуса онлайн (последний вход < 5 минут)
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
    <style>
        /* Стили только для дашборда (не конфликтуют с style.css) */
        .dashboard-wrapper { max-width: 1200px; margin: 2rem auto; padding: 0 1rem; font-family: sans-serif; }
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 2px solid #333; padding-bottom: 1rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .stat-card { background: #f8f9fa; padding: 1.5rem; border-radius: 8px; text-align: center; border: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .stat-card h3 { margin: 0; font-size: 2.2rem; color: #2c3e50; }
        .stat-card p { margin: 0.5rem 0 0; color: #6c757d; font-size: 0.95rem; }
        .section-title { margin: 2rem 0 1rem; color: #333; border-left: 4px solid #007bff; padding-left: 0.8rem; }
        .table-container { overflow-x: auto; background: #fff; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; min-width: 600px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f1f3f5; font-weight: 600; color: #495057; }
        tr:hover { background: #f8f9fa; }
        .status-online { color: #28a745; font-weight: 600; }
        .status-offline { color: #6c757d; }
        .progress-cell { width: 25%; }
        .progress-bg { background: #e9ecef; height: 6px; border-radius: 3px; overflow: hidden; width: 100%; }
        .progress-fill { height: 100%; background: #007bff; transition: width 0.3s ease; }
        .progress-text { font-size: 0.85rem; color: #6c757d; margin-top: 4px; display: block; }
        .btn-logout { display: inline-block; margin-top: 2rem; padding: 0.8rem 1.8rem; background: #dc3545; color: #fff; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background 0.2s; }
        .btn-logout:hover { background: #c82333; }
        .user-greeting { color: #495057; font-weight: 500; }
        @media (max-width: 768px) {
            .dashboard-header { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            th, td { padding: 0.7rem 0.5rem; font-size: 0.9rem; }
        }
    </style>
</head>
<body>

    <!-- Шапка сайта (адаптирована под ваш шаблон) -->
    <header style="background: #333; color: #fff; padding: 1rem 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <div style="font-size: 1.5rem; font-weight: bold;">Autosel</div>
            <nav style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                <a href="index.html" style="color: #fff; text-decoration: none;">Главная</a>
                <a href="about.html" style="color: #fff; text-decoration: none;">О нас</a>
                <a href="contacts.html" style="color: #fff; text-decoration: none;">Контакты</a>
                <span class="user-greeting">👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </nav>
        </div>
    </header>

    <main class="dashboard-wrapper">
        <div class="dashboard-header">
            <h1>Панель администратора - Статистика сайта</h1>
            <a href="logout.php" class="btn-logout" style="margin:0;">Выйти</a>
        </div>

        <!-- Блок общей статистики -->
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

        <!-- Блок "Список всех пользователей" -->
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

        <!-- Блок "Статистика посещений" -->
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
