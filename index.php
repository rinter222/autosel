<?php
define('APP_INIT', true);
require_once 'config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoSel — Купить автомобиль в Тюмени</title>
		<meta name="description" content="Автоцентр AutoSel: большой выбор автомобилей Toyota, BMW, Audi, Mercedes, Volkswagen и Lada. Цены от 600 000 ₽. Trade-in, кредит, тест-драйв.">
		<meta name="keywords" content="купить авто, автосалон Тюмень, подержанные автомобили, новые машины, Toyota, BMW, Audi, Mercedes, Volkswagen, Lada, тест-драйв, Trade-in">
    <link rel="stylesheet" href="style.css">
		<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id=105619418', 'ym');

    ym(105619418, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", accurateTrackBounce:true, trackLinks:true});
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/105619418" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-4GJ1D231KE"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-4GJ1D231KE');
</script>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">AutoSel</div>
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

    <div class="container">
            <h1 class="section-title">Наш автопарк</h1>
            <div class="cars-grid">
                <!-- Автомобиль 1 -->
                <div class="car-card">
                    <img src="images/camry.jpg" alt="Toyota Camry" class="car-image">
                    <div class="car-info">
                        <h2 class="car-name">Toyota Camry</h2>
                        <div class="car-specs">
                            <p>Год: 2023</p>
                            <p>Двигатель: 2.5L 4-цилиндровый</p>
                            <p>Пробег: 5 000 км</p>
                            <p>Трансмиссия: Автоматическая</p>
                        </div>
                        <div class="car-price">от 2 800 000 ₽</div>
                        <a href="cars/camry.html" class="view-details-btn">Подробнее</a>
                    </div>
                </div>

                <!-- Автомобиль 2 -->
                <div class="car-card">
                    <img src="images/x5.jpg" alt="BMW X5" class="car-image">
                    <div class="car-info">
                        <h2 class="car-name">BMW X5</h2>
                        <div class="car-specs">
                            <p>Год: 2022</p>
                            <p>Двигатель: 3.0L 6-цилиндровый</p>
                            <p>Пробег: 12 000 км</p>
                            <p>Трансмиссия: Автоматическая</p>
                        </div>
                        <div class="car-price">от 4 500 000 ₽</div>
                        <a href="cars/x5.html" class="view-details-btn">Подробнее</a>
                    </div>
                </div>

                <!-- Автомобиль 3 -->
                <div class="car-card">
                    <img src="images/a6.jpg" alt="Audi A6" class="car-image">
                    <div class="car-info">
                        <h2 class="car-name">Audi A6</h2>
                        <div class="car-specs">
                            <p>Год: 2023</p>
                            <p>Двигатель: 2.0L 4-цилиндровый</p>
                            <p>Пробег: 8 000 км</p>
                            <p>Трансмиссия: Автоматическая</p>
                        </div>
                        <div class="car-price">от 3 200 000 ₽</div>
                        <a href="cars/a6.html" class="view-details-btn">Подробнее</a>
                    </div>
                </div>

                <!-- Автомобиль 4 -->
                <div class="car-card">
                    <img src="images/c-class.jpg" alt="Mercedes-Benz C-Class" class="car-image">
                    <div class="car-info">
                        <h2 class="car-name">Mercedes-Benz C-Class</h2>
                        <div class="car-specs">
                            <p>Год: 2022</p>
                            <p>Двигатель: 2.0L 4-цилиндровый</p>
                            <p>Пробег: 15 000 км</p>
                            <p>Трансмиссия: Автоматическая</p>
                        </div>
                        <div class="car-price">от 3 800 000 ₽</div>
                        <a href="cars/c-class.html" class="view-details-btn">Подробнее</a>
                    </div>
                </div>

                <!-- Автомобиль 5 -->
                <div class="car-card">
                    <img src="images/tiguan.jpg" alt="Volkswagen Tiguan" class="car-image">
                    <div class="car-info">
                        <h2 class="car-name">Volkswagen Tiguan</h2>
                        <div class="car-specs">
                            <p>Год: 2023</p>
                            <p>Двигатель: 2.0L 4-цилиндровый</p>
                            <p>Пробег: 3 000 км</p>
                            <p>Трансмиссия: Автоматическая</p>
                        </div>
                        <div class="car-price">от 2 500 000 ₽</div>
                        <a href="cars/tiguan.html" class="view-details-btn">Подробнее</a>
                    </div>
								</div>

								<!-- Автомобиль 6 -->
								<div class="car-card">
									<img src="images/priora.jpg" alt="Lada Priora" class="car-image">
									<div class="car-info">
										<h2 class="car-name">Lada Priora</h2>
                        <div class="car-specs">
                            <p>Год: 2018</p>
                            <p>Двигатель: 1.6L 4-цилиндровый</p>
                            <p>Пробег: 65 000 км</p>
                            <p>Трансмиссия: Механическая</p>
                        </div>
                        <div class="car-price">от 600 000 ₽</div>
                        <a href="cars/priora.html" class="view-details-btn">Подробнее</a>
									</div>
            </div>
						<!-- Карточка: Пользователей онлайн -->
						<div class="car-card">
    					<div class="car-info" style="text-align: center;">
        				<h2 class="car-name">Пользователей онлайн</h2>
        				<div class="car-price" id="online-count">—</div>
        					<p class="car-specs">
        					</p>
    						</div>
						</div>
        </div>
			</div>

    <footer class="footer">
        <div class="container">
            <p>© 2025 AutoSel</p>
        </div>
    </footer>
	<script src="protect.js"></script>
    <div id="online-count">Загрузка...</div>
    <script src="counter.js"></script>
</body>
</html>