<?php
define('APP_INIT', true);
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Контакты AutoSel — Адрес, телефон, часы работы в Тюмени</title>
		<meta name="description" content="Свяжитесь с нами: г. Тюмень, ул. Московский тракт, 118. Телефон: +7 (495) 123-45-67. Работаем ежедневно с 9:00 до 18:00.">
		<meta name="keywords" content="контакты, адрес автосалона, телефон, как добраться, Тюмень, автосалон, режим работы">
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
                    <li><a href="index.php" class="nav-link">Главная</a></li>
                    <li><a href="about.php" class="nav-link">О компании</a></li>
                    <li><a href="contacts.php" class="nav-link">Контакты</a></li>
                    <li><a href="promotions.php" class="nav-link">Акции</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span>👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <a href="logout.php">Выйти</a>
                    <?php else: ?>
                        <a href="auth.php">🔐 Войти</a>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

<div id="contacts-page">
	<h1 class="section-title">Наши контакты</h1>
	<div class="contacts-content">
		<p>Свяжитесь с нами, и мы поможем вам выбрать идеальный автомобиль!</p>

		<div class="contact-info">
			<div class="contact-card">
				<i>📍</i>
				<h3>Адрес</h3>
				<p>г. Тюмень, ул. Московский тракт, д. 118</p>
				<p>Пн-Сб: 9:00 - 18:00</p>
				<p>Вс: 10:00 - 17:00</p>
			</div>

			<div class="contact-card">
				<i>📞</i>
				<h3>Телефон</h3>
				<p>+7 (495) 123-45-67</p>
				<p>+7 (916) 987-65-43</p>
				<p>Звоните нам в любое время!</p>
			</div>

			<div class="contact-card">
				<i>✉️</i>
				<h3>Email</h3>
				<p>info@autosel.ru</p>
				<p>sales@autosel.ru</p>
				<p>Ответим в течение 24 часов</p>
			</div>
		</div>

		<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d910.3224679793964!2d65.49412529165434!3d57.134943569796455!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x43bbe191a7b6181f%3A0x44eff2e1d397a897!2z0YPQuy4g0JzQvtGB0LrQvtCy0YHQutC40Lkg0YLRgNCw0LrRgiwgMTE40JAsINCi0Y7QvNC10L3RjCwg0KLRjtC80LXQvdGB0LrQsNGPINC-0LHQuy4sIDYyNTA0OQ!5e0!3m2!1sru!2sru!4v1765211269193!5m2!1sru!2sru" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

	</div>
</div>
<script src="protect.js"></script>
<script src="counter.js"></script>
</body>
</html>