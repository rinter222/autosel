<?php
define('APP_INIT', true);
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акции в AutoSel — Скидки, Trade-in бонусы, подарки при покупке авто</title>
		<meta name="description" content="Специальные предложения: скидки до 20%, Trade-in с доплатой, льготный кредит от 7% и подарки. Только в автосалоне AutoSel!">
		<meta name="keywords" content="акции на авто, скидки, распродажа, Trade-in, кредит на автомобиль, подарки при покупке, автосалон Тюмень">
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

<div id="promotions-page">
	<h1 class="section-title">Акции и предложения</h1>
	<div class="promotions-content">
		<p>У нас всегда есть выгодные предложения для наших клиентов! Следите за нашими акциями и получайте дополнительные бонусы при покупке автомобиля.</p>

		<div class="promotion-card">
			<h3 class="promotion-title">🎉 Новогодняя распродажа</h3>
			<p>Специальные цены на автомобили 2022-2023 годов выпуска. Скидки до 20% + бесплатное ТО на первые 10 000 км пробега.</p>
			<p><strong>Действует до 31 декабря 2025 года</strong></p>
		</div>

		<div class="promotion-card">
			<h3 class="promotion-title">🚗 Trade-in бонус</h3>
			<p>Обменяйте ваш старый автомобиль на новый с дополнительной скидкой до 150 000 рублей. Оценка вашего авто бесплатно!</p>
			<p><strong>Без ограничений по марке и году выпуска</strong></p>
		</div>

		<div class="promotion-card">
			<h3 class="promotion-title">💰 Льготное кредитование</h3>
			<p>Процентная ставка от 7% годовых при покупке автомобиля в кредит. Первый взнос от 0%, срок кредита до 7 лет.</p>
			<p><strong>Только для клиентов AutoSel</strong></p>
		</div>

		<div class="promotion-card">
			<h3 class="promotion-title">🎁 Подарок при покупке</h3>
			<p>При покупке любого автомобиля вы получаете в подарок комплект зимней резины или навигационную систему на выбор.</p>
			<p><strong>Выбор подарка при заключении договора</strong></p>
		</div>

	<p>Подпишитесь на нашу рассылку, чтобы быть в курсе всех акций и специальных предложений!</p>
	</div>
</div>
<script src="protect.js"></script>
<script src="counter.js"></script>
</body>
</html>