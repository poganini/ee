<?php
header("HTTP/1.0 404 Not Found");
?>
<!DOCTYPE html> 
<!--[if lt IE 7 ]><html class="no-js ie6" xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><![endif]-->
<!--[if IE 7 ]><html class="no-js ie7" xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><![endif]-->
<!--[if IE 8 ]><html class="no-js ie8" xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><![endif]-->
<!--[if IE 9 ]><html class="no-js ie9" xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><!--<![endif]-->
<head>
	<title>Нет такой страницы</title>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="Andrey Kudryashov (poganini): mail[sobachka]poganini.ru" />
    <meta http-equiv="imagetoolbar" content="no" />
	
    <link rel="icon" href="/themes/favicon2011.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/themes/favicon2011.ico" type="image/x-icon" />
	
    <script type="text/javascript" src="<?=HTTP_COMMON_SCRIPTS?>modernizr-2.0.6.min.js"></script>
	
	<link rel="stylesheet" href="/themes/styles/screen_2011.css?<?=filemtime($_SERVER['DOCUMENT_ROOT'].'/themes/styles/screen_2011.css')?>" type="text/css" />
<?php	clearstatcache(); ?>
    <link rel="stylesheet" href="/themes/styles/ee.css" type="text/css" />
	<!--[if lte IE 7]><link rel="stylesheet" href="/themes/styles/ie-fix.css" type="text/css" /><![endif]-->
    <link rel="stylesheet" href="/themes/styles/jquery.fancybox-1.3.5.css" type="text/css" media="screen" />
<link rel="stylesheet" href="/themes/styles/no-ie6.css" type="text/css" media="screen" />


	<script type="text/javascript" src="/scripts/jquery/jquery-1.5.1.js"></script>
	<script type="text/javascript" src="/scripts/jquery/jquery-ui-1.8.2.custom.min.js"></script>
	<script type="text/javascript" src="/scripts/jquery/jquery.ui.sortable.js"></script>
	<script type="text/javascript" src="/scripts/folders_sort.js"></script>
	<script type="text/javascript" src="/scripts/jquery/jquery.fancybox-1.3.4.js"></script>

	<script type="text/javascript" src="/scripts/no-ie6.js"></script>
	
	
	<script type="text/javascript" src="/scripts/app.js"></script>
</head>
<body>
<!--[if lte IE 7]><div id="no-ie6">
	<div id="no-ie6-img"></div>
	<p>Вы используете устаревший браузер Internet Explorer <![endif]--><!--[if IE 6]>6<![endif]--><!--[if IE 7]>7<![endif]--><!--[if lte IE 7]>. Требуется срочное <a href="http://www.microsoft.com/rus/windows/internet-explorer/">обновление</a>.</p>
	<a id="no-ie6_close" href="#" title="Закрыть">&times;</a>
</div><![endif]-->
    <div id="nofooter">
		<div class="wrapper">
		    <div id="header">
				<a id="logo" href="/">
					<span><span>&nbsp;</span></span>
				</a>
				<div id="login_search">
				
					<form id="login_form" action="/office/" method="post">
						<fieldset>
							<a href="/remember/">Вспомнить</a>
							<br />
							<a href="/support/registration/">Регистрация</a>
						</fieldset>
						<fieldset>
							<div>
								<input name="1266[login][username]" type="text" title="логин" placeholder="логин" />
								<div>&nbsp;</div>
							</div>
							<div>
								<input name="1266[login][password]" type="password" title="пароль" placeholder="пароль" />
								<div>&nbsp;</div>
							</div>
							<input class="submit" type="submit" value="Login" />
						</fieldset>
					</form>
		
					<form id="search_form" method="get" action="/search/">
						<fieldset>
							<a href="/map/">карта сайта</a>							
						</fieldset>
						<fieldset>
							<div>
								<input type="text" name="search" placeholder="поиск" title="поиск" />
								<div>&nbsp;</div>
								<input class="submit" type="submit" value="Искать" />
							</div>
						</fieldset>
					</form>
				</div>
				<div id="header_menu">
				<ul>
					<li>
						<a href="/news/" title="Новости НГАУ">

						Новости						</a>
					</li>
					<li>
						<a href="/about/" title="О вузе">
						О вузе						</a>
					</li>
					<li>
						<a href="/entrant/" title="Добро пожаловать в наш университет">

						Абитуриенту						</a>
					</li>
					<li>
						<a href="/student/" title="Студенту">
						Студенту						</a>
					</li>
					<li>
						<a href="/about/library/" title="Библиотека университета">

						Библиотека						</a>
					</li>
					<li>
						<a href="/directory/" class="active"title="Справочники университета">
						Справочники						</a>
					</li>
					<li class="last_li">&nbsp;</li>
				</ul>

					<div id="menu_left_bg">&nbsp;</div>
				</div>				
		    </div><!--header-->
		    <div id="content">
				<h1>Ошибка 404</h1>
				Такой страницы нет!<br>
				Возможно, вы ввели неправильно адрес либо воспользовались нерабочей 
				ссылкой. Попробуйте зайти на&nbsp;<a href="/">главную страницу</a> 
				и попасть с неё в желаемое место сайта.
		    </div><!--content-->
		</div>
		<div id="footer-pusher">&nbsp;</div>
    </div><!--nofooter -->
    <div id="footer">    
		<div class="wrapper">

			<div id="main-counter">
				 <!--LiveInternet counter--><script type="text/javascript"><!--
            //document.write("<a href='http://www.liveinternet.ru/click' "+
            document.write("<a href='http://www.liveinternet.ru/stat/portal.nsau.edu.ru/' "+
            "target=_blank><img src='http://counter.yadro.ru/hit?t23.14;r"+
            escape(document.referrer)+((typeof(screen)=="undefined")?"":
            ";s"+screen.width+"*"+screen.height+"*"+(screen.colorDepth?
            screen.colorDepth:screen.pixelDepth))+";u"+escape(document.URL)+
            ";h"+escape(document.title.substring(0,80))+";"+Math.random()+
            "' alt='' title='LiveInternet: показано число посетителей за"+
            " сегодня' "+
            "border='0' width='88' height='15'><\/a>")
            //--></script><!--/LiveInternet-->
			</div>
			<div id="copyright">
				<p>
					&copy;&nbsp;1998—2011 Новосибирский Государственный Аграрный Университет. <a href="http://www.mcx.ru/" title="Министерство сельского хозяйства Российской Федерации">Министерство сельского хозяйства РФ.</a><br />
					По вопросам функционирования портала обращайтесь в <a href="/support/">Службу технической поддержки.</a><div style="display: none"> немецкий <a href="http://www.tevis.com.ua/" target="_blank">клинкерный кирпич</a> фирма "ТЕВИС" .  Скорый <a href="http://nthelp.ru" target="_blank">ремонт ноутбуков</a> не даст развалиться Вашей компании. .  Гарантия на услугу <a href="http://uzercom.ru" target="_blank">компьютерная помощь</a> предоставляется в любом случае. .  Оригинальные автозапчасти, ремонт и аналоги <a href="http://www.union-motors.ru/parts" target="_blank">запчастей для ниссан</a> в техцентре. .  <a href="http://zaryad.com/tag/kapanadze/" target="_blank">капанадзе генератор</a> - проект Заряд </div>

				</p>
			</div>
		</div>
    </div><!--footer-->
</body>
</html>