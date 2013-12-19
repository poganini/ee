<?php 

// EEngine startup file
// version: 2.5
// date: 2013-11-25



$start_time = microtime();

$start_array = explode(" ",$start_time);

$start_time = $start_array[1] + $start_array[0]; 
	
// подключаем конфигурационный файл
if (!@include_once "config.inc")
{
	die($DEBUG_LEVEL ? "<strong>EEngine startup</strong> fatal error #1001:<br />\n&nbsp;&nbsp;Configuration file <code>config.inc</code> not found or access denied." : "");
}


// блок кросс-авторизации
if (isset($_GET['authpass'])) {
	
	unset($_GET['authpass']);
	header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	//ini_set('session.cookie_domain', '.'.APPLIED_DOMAIN);
	
	if (isset($_GET['s'])) {
		setcookie("PHPSESSID", $_GET['s'], time()+3600, '/', '.'.APPLIED_DOMAIN);
		//session_id($_GET['s']);
		unset($_GET['s']);
	}
	//session_start();
	if (!empty($_GET))
		foreach($_GET as $key=>$value) {
			setcookie($key, $value, time()+3600, '/', '.'.APPLIED_DOMAIN);
		}
	echo 1;
	exit;
}


// в режиме блокировки выводим соответствующую страницу
if (OPERATE_MODE == "B")
{
	@include BLOCKED_MODE_PAGE;
	exit();
}


// задаём первоначальный уровень отладки отображения ошибок
if (DEBUG_ONLY_FOR_ADMIN)
{
	$DEBUG_LEVEL = 0;
}

  error_reporting($DEBUG_LEVEL ? E_ALL : 0);


// подключаем модуль отображения критических ошибок
if (@include_once INCLUDES . "FatalErrorDisplay" . CLASS_EXT)
{
	$FED = new FatalErrorDisplay(
		"<strong>EEngine startup</strong>",
		array(
			1003 => "Patch functions file <code>%1\$s</code> not found or access denied",
			1004 => "Common functions file <code>%1\$s</code> not found or access denied",
			1005 => "Wrong codepage <code>%1\$s</code>",
			1006 => "Database handler file <code>%1\$s</code> (<code>%2\$s</code> database type) not found or access denied",
			1007 => "Wrong database type <code>%1\$s</code>",
			1008 => "Session handler file <code>%1\$s</code> not found or access denied",
			1009 => "Auth system file <code>%1\$s</code> not found or access denied",
			1010 => "Engine main file <code>%1\$s</code> not found or access denied",
			),
		$DEBUG_LEVEL
		);
}

else
{
	die($DEBUG_LEVEL ? "<strong>EEngine startup</strong> fatal error #1002:<br />\n&nbsp;&nbsp;Fatal errors display module file <code>" . INCLUDES . "FatalErrorDisplay" . CLASS_EXT . "</code> not found or access denied." : "");
}

@include_once INCLUDES . "Snoopy.class.php";

// подключаем необходимые функции
if (!@include_once INCLUDES . "PHP4_patches.inc")
{
	$FED->FatalError(1003, INCLUDES . "PHP4_patches.inc");
}

if (!@include_once INCLUDES . "CF" . CLASS_EXT)
{
	$FED->FatalError(1004, INCLUDES . "CF" . CLASS_EXT);
}


// формируем значения для часового пояса
ini_set("date.timezone", TIMEZONE);
define("TIMEZONE_OFFSET_SECONDS", TIMEZONE_OFFSET_HOURS * 3600);
define("TIMEZONE_OFFSET_FORMATTED", ((TIMEZONE_OFFSET_HOURS >= 0) ? "+" : "-") . sprintf("%02d", floor(abs(TIMEZONE_OFFSET_HOURS))) . ":" . sprintf("%02d", 60 * (abs(TIMEZONE_OFFSET_HOURS) - floor(abs(TIMEZONE_OFFSET_HOURS)))));


// настраиваем кодировку
switch (CODEPAGE)
{
	case "utf8":
		define("CODEPAGE_HTML", "utf-8");
		define("CODEPAGE_DB", "utf8");
		setlocale(LC_CTYPE, MAIN_LANGUAGE . ".UTF-8");
		break;

	case "cp1251":
		define("CODEPAGE_HTML", "windows-1251");
		define("CODEPAGE_DB", "cp1251");
		setlocale(LC_CTYPE, MAIN_LANGUAGE . ".CP1251");
		break;

	default:
		$FED->FatalError(1005, CODEPAGE);
		break;
}


// настраиваем пути
define("THEMES", DOC_ROOT . THEMES_DIR);                // FS-путь к темам
define("HTTP_THEMES", HTTP_ROOT . THEMES_DIR);          // HTTP-путь к темам
define("COMMON_IMAGES", DOC_ROOT . IMAGES_DIR);         // FS-путь к общим изображениям
define("HTTP_COMMON_IMAGES", HTTP_ROOT . IMAGES_DIR);   // HTTP-путь к общим изображениям
define("HTTP_COMMON_SCRIPTS", HTTP_ROOT . SCRIPTS_DIR); // HTTP-путь к общим скриптам
define("FILES", DOC_ROOT . FILES_DIR);                  // FS-путь к пользовательским файлам
define("HTTP_FILES", HTTP_ROOT . FILES_DIR);            // HTTP-путь к пользовательским файлам


// подключаем класс БД и создаём общее соединение с базой данных
switch (DB_TYPE)
{
	case "mysql":
		if (!@include_once INCLUDES . "MySQLhandle" . CLASS_EXT)
		{
			$FED->FatalError(1006, INCLUDES . "MySQLhandle" . CLASS_EXT, DB_TYPE);
		}

		try {
			$DB = new MySQLhandle(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, $DEBUG_LEVEL);
		} 
		catch(Exception $e) {
			die(sprintf($e->getMessage(), $e->getCode()));
		}
		$DB->Exec("SET time_zone = '" . TIMEZONE_OFFSET_FORMATTED . "'");
		$DB->Exec("SET NAMES '" . CODEPAGE_DB . "'");
		
		/*if (!AT_HOME) {
			$DBMail = new MySQLhandle(DBMAIL_HOST, DBMAIL_USER, DBMAIL_PASS, DBMAIL_NAME, DBMAIL_PORT, $DEBUG_LEVEL);
			$DBMail->Exec("SET time_zone = '" . TIMEZONE_OFFSET_FORMATTED . "'");
			$DBMail->Exec("SET NAMES '" . CODEPAGE_DB . "'");
		}*/
		break;


	case "mysql_old":
		if (!@include_once INCLUDES . "MySQLhandle" . CLASS_EXT)
		{
			$FED->FatalError(1006, INCLUDES . "MySQLhandle" . CLASS_EXT, DB_TYPE);
		}

		try {
			$DB = new MySQLhandle(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, $DEBUG_LEVEL);
		}
		catch(Exception $e) {
			die(sprintf($e->getMessage(), $e->getCode()));
		}
		$DB->Exec("SET character_set_client='" . CODEPAGE_DB . "'");
		$DB->Exec("SET character_set_results='" . CODEPAGE_DB . "'");
		$DB->Exec("SET collation_connection='" . CODEPAGE_DB . "_general_ci'"); // !!! возможно, следует вводить отдельную переменную
		break;


	default:
		$FED->FatalError(1007, DB_TYPE);
		break;
}


// поключаем и настраиваем альтернативный обработчик сеансов работы (сессий)
if (CF::IsNonEmptyStr(SESSION_HANDLER_VARNAME))
{
  $SessionClass = SESSION_HANDLER_VARNAME;
	if (!@include_once INCLUDES . SESSION_HANDLER_VARNAME . CLASS_EXT)
	{
		$FED->FatalError(1008, INCLUDES . SESSION_HANDLER_VARNAME . CLASS_EXT);
	}

	$GLOBALS[SESSION_HANDLER_VARNAME] = new $SessionClass($DB);

	session_set_save_handler(
		array(&$GLOBALS[SESSION_HANDLER_VARNAME], "Open"),
		array(&$GLOBALS[SESSION_HANDLER_VARNAME], "Close"),
		array(&$GLOBALS[SESSION_HANDLER_VARNAME], "Read"),
		array(&$GLOBALS[SESSION_HANDLER_VARNAME], "Write"),
		array(&$GLOBALS[SESSION_HANDLER_VARNAME], "Destroy"),
		array(&$GLOBALS[SESSION_HANDLER_VARNAME], "GCdummy")
		);
}
// а иначе хотя бы задаём максимальную вероятность, что устаревшая сессия будет удалена
else
{
	ini_set("session.gc_probability", 100);
}


 
// включаем систему аутентификации
if (!@include_once INCLUDES . "Auth" . CLASS_EXT)
{
	$FED->FatalError(1009, INCLUDES . "Auth" . CLASS_EXT);
}

$Auth = ALLOW_AUTH ? new Auth(AUTH_DB_PREFIX, SESSION_HANDLER_VARNAME) : new AuthDummy;
$ADMIN_USER = ($Auth->usergroup_id == ADMIN_USERGROUP_ID);



// в случае необходимости меняем уровень отображения ошибок PHP
if ($DEBUG_LEVEL && DEBUG_ONLY_FOR_ADMIN && $ADMIN_USER)
{
	error_reporting(E_ALL); // !!! возможно, нужно сделать разные уровни отображения для разных уровней отладки
	$DB->SetDebugLevel($DEBUG_LEVEL);
}

// в режиме технических работ выводим соответствующую страницу, если пользователь не админ и не находится на странице авторизации.
if ((OPERATE_MODE == "S") && !($ADMIN_USER || CF::URIin(HTTP_ROOT . SERVICE_MODE_LOGIN_PAGE)))
{
	@include SERVICE_MODE_PAGE;
	exit();
}


// проверяем доступность файла движка
if (!@include_once INCLUDES . "EEngine" . CLASS_EXT)
{
	$FED->FatalError(1010, INCLUDES . "EEngine" . CLASS_EXT);
}

// запускаем движок с установленным ранее уровнем обработки ошибок, и корневой папкой, определяемой по применённому домену
$Engine = new EEngine($DEBUG_LEVEL, $DOMAINS[APPLIED_DOMAIN][1]);

if (defined("CACHE_TIME") && CACHE_TIME && !$ADMIN_USER && $allowed = $Engine->OperationAllowed(0, "engine.cache", -1, $Auth->usergroup_id)) {
	//Кэширование:
	$DB->SetTable("engine_cache");
	$DB->AddCondFS("uri", "=", $_SERVER["REQUEST_URI"]);
	$DB->AddCondFS("usergroup_id", "=", $Auth->usergroup_id);
	$DB->AddCondFS("current_time", ">=", date("Y-m-d H:i:s", time() - CACHE_TIME));
	$res = $DB->Select();
	if ($row = $DB->FetchAssoc($res)) {
		echo $row["cache"];
	}
	else {
		ob_start();
		
		$Engine->Act();
		
		$queries_num = $DB->GetQueriesNum();
		
		$DB->SetTable("engine_cache");
		$DB->AddCondFS("uri", "=", $_SERVER["REQUEST_URI"]);
		$DB->AddCondFS("usergroup_id", "=", $Auth->usergroup_id);
		$DB->Delete();
		
		if ($allowed) {
			$DB->SetTable("engine_cache");
			$DB->AddValue("cache", ob_get_contents());
			$DB->AddValue("usergroup_id", $Auth->usergroup_id);
			$DB->AddValue("uri", $_SERVER["REQUEST_URI"]);
			$DB->AddValue("current_time", date("Y-m-d H:i:s"));
			$DB->Insert();
		}
		
		ob_end_flush();
	}
}
else
	$Engine->Act();

//Подсчёт времени загрузки:

$end_time = microtime();

$end_array = explode(" ",$end_time);
$end_time = $end_array[1] + $end_array[0];

$loading_time = $end_time - $start_time;

if (!isset($queries_num))
	$queries_num = $DB->GetQueriesNum();

if (defined("COUNT_LOADING_TIME") && COUNT_LOADING_TIME && is_integer($Engine->folder_id)) {
	$DB->SetTable("engine_loading_time");
	$DB->AddValue("folder_id", $Engine->folder_id);
	$DB->AddValue("loading_time", $loading_time);
	$DB->AddValue("queries_num", $queries_num);
	$DB->Insert();
}

?>