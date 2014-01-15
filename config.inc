<?php

// EEngine config file
// version: 2.1.2
// date: 2013-11-15


// задаём имя сайта
define("SITE_NAME", "Новосибирский Государственный Аграрный Университет");
define("SITE_SHORT_NAME", "НГАУ");

// задаём параметры функционирования сайта
define("OPERATE_MODE", "N");                            // режим работы: N - нормальный, S - технические работы, B - сайт заблокирован
define("BLOCKED_MODE_PAGE", "blocked.tpl");             // страница, отображаемая посетителям в режиме блокировки сайта
define("SERVICE_MODE_PAGE", "service.tpl");             // страница, отображаемая посетителям в режиме технических работ
define("SERVICE_MODE_LOGIN_PAGE", "office/");     		// страница авторизации пользователей в режиме технических работ
define("MAIL_LOGIN_PAGE", "mail/");       				// страница авторизации пользователей в почтовый ящик
define("ADMIN_USERGROUP_ID", 1);                        // ID администраторской группы пользователей
define("INHERIT_TEMPLATES", 1);							// наследовать ли шаблон родительской страницы
define("COUNT_LOADING_TIME", 0);						// считать ли время загрузки страниц и количество sql-запросов
define("CACHE_TIME", 0);								// время (в секундах) кэширования страниц: 0 - кэширование отключено

// обрабатываем домен, через который посетитель зашёл на сайт
// массив доменов ключ массива - домен, первый подэлемент - "домашний" ли домен, второй подэлемент - корневая папка движка
$DOMAINS = array(
	"nsau.local" => array(true, 0),
	"portal.nsau.edu.ru" => array(false, 0),
	"nsau.edu.ru" => array(false, 0),
	"нгау.рф" => array(false, 0),
	"vetfac.nsau.edu.ru" => array(false, 169),
	"nripk.ru" => array(false, 1775),
	"bulletin.nsau.edu.ru" => array(false, 1023)
	);

define("SATELLITE_DOMAINS", "nripk.ru");
define("AUTO_WWW_ALIAS", true);                         // автоматическое приравнивание домена вида www.example.com к example.com
define("DEFAULT_DOMAIN", "nsau.local");                // если посетитель зашёл с домена, не указанного в списке, каким считать домен

if (isset($DOMAINS[$_SERVER["SERVER_NAME"]]))
// домен найден в списке, применяем его
{
	define("APPLIED_DOMAIN", $_SERVER["SERVER_NAME"]);
}

elseif (AUTO_WWW_ALIAS
	&& (strpos($_SERVER["SERVER_NAME"], "www.") === 0)
	&& isset($DOMAINS[substr($_SERVER["SERVER_NAME"], 4)]))
// в списке найден такой же домен, но без WWW, применяем его
{
	define("APPLIED_DOMAIN", substr($_SERVER["SERVER_NAME"], 4));
}

else
// домен не найден в списке, применяем домен по умолчанию
{
	define("APPLIED_DOMAIN", DEFAULT_DOMAIN);
}


// по применённому домену определяем, "дома" мы или на удалённом сервере
define("AT_HOME", $DOMAINS[APPLIED_DOMAIN][0]);




if (AT_HOME)
// ...и в зависимости от этого задаём параметры отладки, БД и основных путей...
// ..."дома":
{
$DEBUG_LEVEL = 2;                                     // уровень отладки: 0 - нет, 1 - частичная, 2 - полная, 3 - избыточная (все запросы БД)
	define("DEBUG_ONLY_FOR_ADMIN", false);                // отладка только для администраторов
	define("DB_TYPE", "mysql");                           // тип базы данных
	define("DB_HOST", "localhost");                       // сервер БД
	define("DB_PORT", 3306);                              // порт сервера БД
	//define("DB_USER", "root");                                 // имя пользователя БД
	//define("DB_PASS", "");                                 // пароль пользователя БД

	define("DB_USER", "nsau");                                // имя пользователя БД
	define("DB_PASS", "EbAhRwRcLvpxHATe");                                // пароль пользователя БД
	define("DB_NAME", "nsau");                                // имя базы данных

	define("DOC_ROOT", $_SERVER["DOCUMENT_ROOT"] . "/");  // корень сервера в файловой системе (FS)
	define("INCLUDES", DOC_ROOT . "includes/");           // FS-путь к инклюдам
	define("HTTP_ROOT", "/");                             // HTTP-корень сайта
}

else
// ...на удалённом сервере:
{
	$DEBUG_LEVEL = 0;                                      // уровень отладки: 0 - нет, 1 - частичная, 2 - полная, 3 - избыточная (все запросы БД)
	define("DEBUG_ONLY_FOR_ADMIN", false);                  // отладка только для администраторов
	define("DB_TYPE", "mysql");                            // тип базы данных
	define("DB_HOST", "192.168.30.21");                        // сервер БД
	define("DB_PORT", 3306);                               // порт сервера БД
	define("DB_USER", "nsau");                                 // имя пользователя БД
	define("DB_PASS", "EbAhRwRcLvpxHATe");                                 // пароль пользователя БД
	define("DB_NAME", "nsau");                                 // имя базы данных

	define("DOC_ROOT", $_SERVER["DOCUMENT_ROOT"] . "/");   // корень сервера в файловой системе (FS)
	define("INCLUDES", DOC_ROOT . "includes/");            // FS-путь к инклюдам
	define("HTTP_ROOT", "/");                              // HTTP-корень сайта
}


 

// задаём основной язык и кодировку
define("MAIN_LANGUAGE", "ru_RU");
define("CODEPAGE", "cp1251");
//define("CODEPAGE", "utf8");


// задаём разницу часового пояса с Гринвичем (в часах)
define("TIMEZONE_OFFSET_HOURS", 7);  // Зимнее 6, Летнее 7
define("TIMEZONE", "Asia/Novosibirsk");

define("ENGINE_DB_PREFIX", "engine_");                   // префикс базы данных для движка
define("AUTH_DB_PREFIX", "auth_");                       // префикс базы данных для системы авторизации
define("ALLOW_AUTH", true);                              // разрешать ли авторизацию пользователей
define("SIMPLE_NODEGROUPS_INHERITANCE", false);          // использовать ли упрощённое наследование nodegroups
define("ALLOW_FOLDEROPTIONS", false);                    // разрешать ли использование опций для виртуальных папок


// настраиваем сеансы работы (сессии)
define("SESSION_HANDLER_VARNAME", "ESessionHandler");     // имя переменной для алтернативного обработчика сессий (false - не использовать)
ini_set("session.gc_maxlifetime", 180000);                 // время "жизни" сессии пользователя по умолчанию


// задаём основные папки и пути
define("THEMES_DIR", "themes/");                         // имя папки тем
define("IMAGES_DIR", "images/");                         // имя папки изображений
define("STYLES_DIR", "styles/");                         // имя папки стилей
define("SCRIPTS_DIR", "scripts/");                       // имя папки скриптов
define("FILES_DIR", "files/");                           // имя папки пользовательских файлов

define("CLASS_EXT", ".class.php");                           // расширение файла для классов
define("TEMPLATE_EXT", ".tpl");                          // расширение файла для шаблонов
define("PRETEMPLATE_EXT", ".ptpl");                      // расширение файла для предварительных шаблонов (подключаемых ДО основного шаблона)
define("ALLOW_PRETEMPLATES", true);                      // разрешать использование предварительных шаблонов

//define("DEFAULT_MAIN_TEMPLATE", "_main");                // имя основного шаблона по умолчанию
define("DEFAULT_MAIN_TEMPLATE", "_mainnew");                // имя основного шаблона по умолчанию
define("HTTP403_TEMPLATE", "_403");                      // имя шаблона страницы HTTP 403 (внутри папки темы)
define("HTTP404_TEMPLATE", "_404");                      // имя шаблона страницы HTTP 404 (внутри папки темы)


// настройка параметров ключевых слов
define("APPEND_KEYWORDS", false);                        // добавлять, а не заменять ключевые слова для подпапок ("before"/"after"/false)
define("KEYWORDS_GLUE", ", ");                           // строка для склейки ключевых слов

define("AJAX_REQUEST_MARKER", "application/text+xml+javascript");			// переменная-маркер, передаваемая в ajax-запроса, чтобы движок мог их распознать

?>