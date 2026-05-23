<?php
define('APP_INIT', true);
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О компании AutoSel — Надежный автодилер с 2015 года</title>
		<meta name="description" content="AutoSel — официальный дилер в Москве. Более 5000 клиентов, гарантия, сервис, Trade-in. Работаем с 2015 года.">
		<meta name="keywords" content="о компании, автодилер Тюмень, история автосалона, отзывы, официальный дилер, гарантия">
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
<html>
    <header>
        <div class="header-content">
            <div class="logo">AutoSel</div>
            <nav>
                <ul>
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
                </ul>
            </nav>
        </div>
    </header>
<body>
<div id="about-page">
	<h1 class="section-title">О компании AutoSel</h1>
	<div class="about-content">
    <p>AutoSel — это ведущий дилер автомобилей премиум-класса и массового сегмента в России. Мы работаем на рынке уже более 15 лет и гордимся тем, что помогли тысячам клиентов найти идеальный автомобиль.</p>
    <p>Наша миссия — сделать покупку автомобиля максимально простой, удобной и приятной для каждого клиента. Мы предлагаем широкий выбор новых и подержанных автомобилей, гибкие условия финансирования и профессиональное обслуживание.</p>
    <img src="images/showroom.jpg" alt="AutoSel Showroom">
    <p>В нашем шоуруме представлены автомобили всех популярных марок, а наши консультанты всегда готовы помочь вам выбрать идеальный автомобиль, учитывая ваши предпочтения, бюджет и потребности.</p>
    <p>Мы заботимся о каждом клиенте и стремимся обеспечить высокий уровень сервиса на всех этапах сотрудничества — от выбора автомобиля до его обслуживания после покупки.</p>
  </div>
</div>
<script src="protect.js"></script>
<script src="counter.js"></script>
</body>
</html>