<?php
/*
 * .ini
 * 
 * modelCalcParams = classDiff, profileDiff 
 */

class Pl {
	public $prof;
	
	public function calcForm() {
		$prevMatches = $this->getMatchesForPeriod($begDate, $endDate);
		$this->updateForm(0, $prevMatches);
	}
	
	public function updateForm($curForm, $matchesArr) {
		return $this->updateForm();
	}
}


class Initiator {
	private static $paramKeys = array();
	private static $isExecuted = false;
	
	private static function handleIniSection($iniSection) {
		foreach($iniSection as $settingName=>$settingValue) {
			if (is_array($settingValue) && !array_key_exists(0, $settingValue)) {
				self::handleIniSection($settingValue);
			}
			elseif (property_exists(__CLASS__, $settingName)) {
				self::$$settingName = $settingValue;
			}
		}
	}
	
	public static function execute() {
		if (!self::$isExecuted) { 
			$iniFile = __CLASS__.'.ini';
			if ($iniSettings = parse_ini_file($iniFile, true)) {
				self::handleIniSection($iniSettings);
				self::$isExecuted = true;
			} else throw new Exception('Ini file not found');
		}
	}
	
	public static function __callStatic($methodName, $args) {
		if (substr($methodName, 0, 3) == "get") {
			$propName = strtolower($methodName[3]).substr($methodName, 4);
			
			if (property_exists(__CLASS__, $propName)) {
				return self::$$propName;
			}
			else throw new Exception('Unknown variable '.__CLASS__.'::'.$propName.' requested');
		}
	}
	
	
}

abstract class Model {
	protected final function __construct($args) { 
		foreach($args as $key=>$value)
			if (property_exists(get_called_class(), $key)) {
				$this->$key = $value;
			}
	}
	
	public final function __get($propName) {
		return $this->$propName;
	}
}

class Res extends Model {
	private static $resDistrsSet;
	protected  $keyOrder;
	private static $instance = null;
	
	public static function getInstance($args) {
		return (is_null(self::$instance) ? new self($args) : self::$instance);
	} 
	
	public function buildResDistrsSet() {
		$resDistrsSet = array();
		self::$resDistrsSet = $resDistrsSet;
	}

	public function getResDistrByParamValuesSet($paramValuesSet) {
		foreach($paramValuesSet as $key=>$keyValue)
			$orderedParamValuesSet[array_key($key, $keyOrder)] = $keyValue;
		$hashKey = '';
		foreach($orderedParamValuesSet as $keyValue)
			$hashKey.= $keyValue;
		
		
		foreach(self::$resDistrSet as $key=>$distr) {
			if (str_pos($key, $hashKey) == 0) {
				$distrsFound[] = $distr;
			}
			if (!isset($distrsFound)) {
				return null;
			} else if (count($distrsFound)>1) {
				$sumDistr = array();
				foreach($distrsFound as $distr) {
					foreach($distr as $foraType=>$foraTypeDistr)
						foreach($distr as $foraValue=>$foraValueCount)
							$sumDistr[$foraType][$foraValue] += $foraValueCount;
				}
				return $sumDistr;
			} else {
				return $distrsFound[0];
			}
		}
	}
}

class Math {
	public $pl1;
	public $pl2;
	
	public function calcRateDiff() {
	
	}

	public function calcProfDiff() {
		$prof1 = $this->pl1->prof;	
		$prof2 = $this->pl2->prof;
	}
	
	public function calcFormDiff() {
		return $this->pl1->calcForm() - $this->pl2->calcForm();
	}
	
	public function calcMotDiff() {
	
	}
	
	public function getForecastParamValues($paramSet = array()) {
		$params  = array();
		if (!empty($paramSet))
			foreach($paramSet as $param) {
				switch($param) {
					case 'rate':
						$params[] = $this->calcRateDiff();
					break;
					case 'prof':
						$params[] = $this->calcProfDiff();
					break;
					case 'form':
						$params[] = $this->calcFormDiff();
					break;
					case 'mot':
						$params[] = $this->calcMotDiff();
					break;
					default:
					break;
				}
		}
		return $params;
	}
}

class EVpn
// version: 1.7
// date: 2013-11-25
{
    var $module_id;
    var $node_id;
    var $module_uri;
    var $privileges;
    var $output;
	var $params;
    
	var $mode;
	var $settings;
	
	var $iniSettings;
   
	const SETS_RES = 'bySets';
	const GAMES_RES = 'byGames';
		
	function callCURl($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$page = curl_exec($ch);
		curl_close($ch);
		return $page; 
	}
	
	function orderTournMatches($curTourn, $plrMatches) 
	{
		$curTournMatches = array_filter($plrMatches, 
			function($item) use($curTourn) { 
				return $item['tournament'] == $curTourn;
			});
		$curTournMatchesCount = count($curTournMatches)-1;
		$iterMatch = $plrMatches[count($plrMatches)-1];
		end($plrMatches);
		while($iterMatch['tournament'] == $curTourn) {
			$iterMatch['orderNum'] = $curTournMatchesCount - $iterMatch['orderNum'];
			$ind = key($plrMatches);
			$plrMatches[$ind] = $iterMatch;
			prev($plrMatches); 
			$iterMatch = current($plrMatches);
		}
	}
	
	
	function getMatchInfo($matchId = false)
	{
		global $DB;
		$DB->SetTable('matches');
		if ($matchId)
			$DB->AddCondFS('id', '=', $matchId);
		$DB->Select();
	}
	
	function calcClassDiff($rate1, $rate2) 
	{
		$intervalLimits = array(0, 30, 55, 90, 1000);
		for($i=0; $i< count($intervalLimits )-1; $i++)
		{
			if (!isset($rate1Interval) && $rate1 > $intervalLimits[$i] && $rate1 <= $intervalLimits[$i+1])
			{
				$rate1Interval = $i;
			}
			if (!isset($rate2Interval) && $rate2 > $intervalLimits[$i] && $rate2 <= $intervalLimits[$i+1])
			{
				$rate2Interval = $i;
			}
		}
		
		return $rate1Interval - $rate2Interval;
	}
	
	function calcMatchRes($res, $resType = false)
	{
		$setsScore = explode(',', $res);
		foreach($setsScore as $setScore)
		{
			$playersGames = explode(':', $setScore);
			$setsDiff += $playersGames[0] > $playersGames[1] ? 1 : -1;
			$gamesDiff +=  $playersGames[0] - $playersGames[1];
		}
		
		return $resType ? ($resType == SETS_RES ? $setsDiff : $gamesDiff ) : array(self::SETS_RES => $setsDiff, self::GAMES_RES => $gamesDiff);
	}
	
	function checkParamInfluence($paramAlias, $paramExpr)
	{
		$foraTypes = array(self::SETS_RES, self::GAMES_RES);
		//$matchesInfo = $this->getMatchInfo(); 
		$matchesInfo = array(
			array('res' => '6:3,5:7,6:2', 'prop1'=>30, 'prop2'=>10),
			array('res' => '6:4,4:6,6:1', 'prop1'=>30, 'prop2'=>10),
			array('res' => '6:4,6:7,3:6', 'prop1'=>30, 'prop2'=>10), 
			array('res' => '4:6,6:2,6:4', 'prop1'=>35, 'prop2'=>10)
		);
		$ind = 0;
		foreach($matchesInfo as $match) {
			$res = $this->calcMatchRes($match['res']); 
			foreach($foraTypes as $type)
			{
				eval('$ind = '.str_replace("[OBJ]", '$match', $paramExpr).';');
				$resDistrByParam[$ind][$type][$res[$type]]++;
			}	
		}
		foreach($resDistrByParam as $paramValue=>$resDistrByParamValues) {
			foreach ($resDistrByParamValues as $type=>$resTypeValues) {
				$sumResCount = 0;	
				foreach ($resTypeValues as $res=>$resCount) {
					$sumResCount += $resCount; 
				}
				foreach ($resTypeValues as $res=>$resCount) {
					$resDistrByParam[$paramValue][$type][$res] = round($resCount / $sumResCount, 2).'('.$resCount.')'; 
				}
			}
		}
		return array($paramAlias => $resDistrByParam);
	}
	
	function getForasProbs($fora, $playersDifference)
	{
		$playersDifference = array($rateDiff, $profileDiff, $formDiff, $motivDiff, $percDiff);
		$foraTypes = array('bySets', 'byGames');
		return $forasProbs;
	}
	
	function calcPlayerForm($player1Form, $player2Form, $playersDifference, $matchRes) 
	{
		$forasProbs = Util::calcForasProbs($this->calcMatchRes($matchRes, GAMES_RES), $playersDifference);
		$foraTypes = array('bySets', 'byGames');
		foreach($foraTypes as $foraType)
			$normalDeltaFora += ($forasProbs[$foraType]['fora'] > $forasProbs[$foraType]['median'] ? 1 : -1)*(0.5-0.5* $forasProbs[$foraType]['fora'] / $forasProbs[$foraType]['median'])/2;
		$normalDeltaFora /= count($foraTypes);
		$player1Form += ceil((0.5 + $normalDeltaFora)/0.2);
		$player2Form += ceil((0.5 - $normalDeltaFora)/0.2);
		
		return array('player1Form' => $player1Form, 'player2Form' => $player2Form);
	}
	
    function EVpn($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
    {
		if (isset($_GET['test'])) phpinfo();

	    global $DB, $DBVpn, $Engine, $Auth, $DEBUG_LEVEL;
        $this->module_id = $module_id;
        $this->node_id = $node_id;
        $this->module_uri = $module_uri;
        $this->additional = $additional;
        $this->output = array();
        $this->output["module_id"] = $module_id; 
        $this->output["messages"] = array("good" => array(), "bad" => array());
        
        $uri_parts = explode("/", $this->module_uri);
      
       	if ($Auth->user_id) {
		 	$config = parse_ini_file(INCLUDES . $global_params, true);
			if ($config === false) 
				die("Не найден конфигурационный файл модуля");
			$this->settings = $config['settings'];
			$this->output['pass_min_length'] = $this->settings['pass_min_length'];
      
			try {
				$DBVpn = new MySQLhandle($config["db_params"]["HOST"], $config["db_params"]["USER"], $config["db_params"]["PASS"], $config["db_params"]["NAME"], $config["db_params"]["PORT"], $DEBUG_LEVEL, false);
			}
			catch (Exception $e) {
				//if(!$DEBUG_LEVEL) return ;
				//if($DEBUG_LEVEL)
					$this->output['messages']['bad'][] = sprintf($e->getMessage(), $e->getCode());   
				$this->output["exception"] = true;//return ;
			}
			//if($DBVpn->Error()) return; //Если не удалось соединиться - выходим
			//$DBVpn->Exec("SET collation_connection='utf8'");
      /*$DBVpn->SetTable('clients', 'c');
			$DBVpn->AddField('c.id');
			$DBVpn->AddField('c.name');
      $DBVpn->AddTable("vpn_accounts","a"); 
      //$DBVpn->AddJoin("id","client_id");
      $DBVpn->AddField('a.login');
      $DBVpn->AddField('a.id', 'acc_id');
      $DBVpn->AddCondFF("c.id","=", "a.client_id");
      if(isset($_GET['sortby']) && $_GET['sortby'] == 'login'){
        $DBVpn->AddOrder("a.login", ((isset($_GET['order']) && $_GET['order'] == 'DESC') ? 1 : 0));
      } 
      else {
        $DBVpn->AddOrder("c.name", ((isset($_GET['order']) && $_GET['order'] == 'DESC') ? 1 : 0));
        //$DBVpn->AddExpOrder("CONVERT(c.name COLLATE latin1_swedish_ci using utf8)", ((isset($_GET['order']) && $_GET['order'] == 'DESC') ? 1 : 0));
      }*/
      
      $sql = "SELECT c.id, c.name, a.login, a.id as acc_id FROM clients c LEFT JOIN vpn_accounts a ON (c.id=a.client_id)";
      if(isset($_GET['sortby']) && $_GET['sortby'] == 'login'){
        $sql .= " ORDER BY a.login ".((isset($_GET['order']) && $_GET['order'] == 'DESC') ? 'DESC' : 'ASC');
      }
      else {
        $sql .= " ORDER BY c.name ".((isset($_GET['order']) && $_GET['order'] == 'DESC') ? 'DESC' : 'ASC');
      } 
      
      //echo $DBVpn->SelectQuery(); 
			//$res = $DBVpn->Select(); 
	if($DBVpn) {
      $res = $DBVpn->Exec($sql); 
			while($row = $DB->FetchAssoc($res)) {
				$row['name'] = iconv('koi8-r', 'windows-1251', $row['name']);
				$clients[] = $row; 
			}
			$this->output['clients'] = $clients;
	}
						
			switch ($uri_parts[0]) {
			
				
			
				case "create":
					if (isset($_POST) && isset($_POST['cinfo']) && !empty($_POST['cinfo'])) {
						$DBVpn = new MySQLhandle($config["db_params"]["HOST"], $config["db_params"]["USER"], $config["db_params"]["PASS"], $config["db_params"]["NAME"], $config["db_params"]["PORT"], $DEBUG_LEVEL);
						$DBVpn->SetTable('clients');
						foreach($_POST['cinfo'] as $field=>$value) {
							$value = iconv('windows-1251', 'koi8-r', $value);
							$DBVpn->AddValue($field, $value);
						}
						$DBVpn->AddValue("cdate", date("YmdHis"));
						$DBVpn->AddValue("operator", $config["db_params"]["USER"]);
						$DBVpn->Insert();
						$newUserId = $DBVpn->LastInsertID();
						$subject = "новый пользователь VPN";
						$message = "Зарегистрирован новый пользователь VPN: ".$_POST['cinfo']['name']." . Регистрация выполнена пользователем ".$Auth->username;
						$this->SendInfoMail($config["mail_params"]["notice_email"], $subject, $message);
						$Engine->LogAction($this->module_id, "vpn_user", $newUserId, "add");	
						CF::redirect($Engine->engine_uri);
					}
					$this->output['mode'] = 'create';
				break;
				
				case "edit":
					$clientTypes = array(); 
					$DBVpn->SetTable("client_types");
					$DBVpn->AddField("id");
					$DBVpn->AddField("name");
					$res = $DBVpn->Select();
					while($row = $DBVpn->FetchAssoc($res)) {
						$clientTypes[$row['id']] = iconv('koi8-r', 'windows-1251', $row['name']);
					}
					$this->output['clientInfo'] = $clientInfo = $this->GetClientInfo($uri_parts[1]);
					if (isset($_POST) && isset($_POST['cinfo']) && !empty($_POST['cinfo'])) {
						$subject = "Обновлен пользователь VPN";
						$message = "Обновлен пользователь VPN: ".$client_name." . Обновление выполнено пользователем ".$Auth->username;
						$message .= "\nИзменены следующие поля: \n\n";
						$fields = array(
								"name" => "ФИО или название организации", 
								"office" => "Юридический адрес", 
								"whereis" => "Физический адрес", 
								"phone" => "Телефон", 
								"techphone" => "Телефон тех.специалиста", 
								"fax" => "Факс",
								"person" => "Руководитель",
								"technic" => "Технический специалист",
								"email" => "Электронная почта",
								"type" => "Тип клиента"
								);
						//print_r($_POST['cinfo']); exit;
						$DBVpn->SetTable('clients');
						foreach($_POST['cinfo'] as $field=>$value) {
							if($value != $clientInfo[$field] && $field != "type") {
								$Engine->LogAction($this->module_id, "vpn_user", $uri_parts[1], "changing user field '".$field."' from '".$clientInfo[$field]."' to '".$value."'");
								$message .= $fields[$field].": с '".$clientInfo[$field]."' на '".$value."'\n";
							}
							elseif($field == "type") {
								if(/* iconv('koi8-r', 'windows-1251', $clientTypes[$value]) != $clientInfo[$field] */$value != $clientInfo['type_id']) {
									$Engine->LogAction($this->module_id, "vpn_user", $uri_parts[1], "changing user field '".$field."' from '".$clientInfo[$field]."' to '". $clientTypes[$value]."'");
									$message .= $fields[$field].": с '".$clientInfo[$field]."' на '". $clientTypes[$value]."'\n";
								}   
							}
							$value = iconv('windows-1251', 'koi8-r', $value);
							$DBVpn->AddValue($field, $value);
						}
						
						$DBVpn->AddValue("operator", $config["db_params"]["USER"]);
						$DBVpn->AddCondFS("id", '=', $uri_parts[1]);
						//echo $DBVpn->UpdateQuery();exit; 
						$DBVpn->Update();
						$this->SendInfoMail($config["mail_params"]["notice_email"], $subject, $message);
						$Engine->LogAction($this->module_id, "vpn_user", $uri_parts[1], "edit");
						CF::Redirect($Engine->engine_uri."edit/".$uri_parts[1]); 	
					}
					
					$this->output['mode'] = 'edit';
				break;
				
				case "delete":
					$DBVpn->SetTable('vpn_accounts');
					$DBVpn->AddCondFS('client_id', '=', $_GET['client_id']);
					$res = $DBVpn->Select();
					if ($DBVpn->NumRows($res)) {
						$_SESSION["messages"]["bad"][] = "Пользователь не может быть удалён, так как имеет присоединенный аккаунт.";
						CF::redirect($Engine->engine_uri);
					} else {
						$DBVpn->SetTable('clients');
						$DBVpn->AddCondFS('id', '=', $_GET['client_id']);
						$res = $DBVpn->Select(1);
						$row = $DBVpn->FetchAssoc($res);
						$client_name = iconv('koi8-r', 'windows-1251', $row['name']);
						$subject = "удалён пользователь VPN";				
						$message = "Удалён пользователь VPN: ".$client_name." . Удаление выполнено пользователем ".$Auth->username;
						$this->SendInfoMail($config["mail_params"]["notice_email"], $subject, $message);
						
						$DBVpn->SetTable('clients');
						$DBVpn->AddCondFS('id', '=', $_GET['client_id']);
						$DBVpn->Delete();
						$_SESSION["messages"]["good"][] = "Пользователь успешно удалён.";
						$Engine->LogAction($this->module_id, "vpn_user", $_GET['client_id'], "delete");	
						CF::redirect($Engine->engine_uri);
					}
					$this->output['mode'] = 'list';
				break;
				
				case "view":
					$this->output['clientInfo'] = $this->GetClientInfo($uri_parts[1]);
					$this->output['mode'] = 'view';
				break;
				
				case "editAcc":
					if (isset($_POST, $_GET['acc'], $_POST['tariff_id'], $_POST['ip_ex'])) {
						$ips = explode(".",$_POST["ip_ex"]);
						$DBVpn->SetTable('vpn_nets');
						$DBVpn->AddCondFS('ip', '=', sprintf("%u", ip2long($ips[0].".".$ips[1].".".$ips[2].".0")));
						$res = $DBVpn->Select(1);
						if ($row = $DBVpn->FetchRow($res)) {
							$DBVpn->SetTable('vpn_accounts');
							$DBVpn->AddField('id');
							$DBVpn->AddCondFS('login', '=', $_GET['acc']);
							$res = $DBVpn->Select(1);
							$row = $DBVpn->FetchRow($res);
							$accountId = $row[0];
							$DBVpn->SetTable('vpn_balances');
							$DBVpn->AddCondFS('id', '=', $accountId);
							$DBVpn->Delete();
							
							$DBVpn->SetTable('tariff_plans');
							$DBVpn->AddField('tariff_type');
							$DBVpn->AddCondFS('id', '=', $_POST['tariff_id']);
							$res = $DBVpn->Select(1);
							$row = $DBVpn->FetchRow($res);
							
							if (in_array($row[0],$this->settings['pa_tariff_type'])) {
								$DBVpn->SetTable('vpn_balances');
								$DBVpn->AddValues(array('id' => $accountId));
								$DBVpn->Insert();
							}
							
							$DBVpn->SetTable('vpn_accounts');
							$DBVpn->AddCondFS('id', '=', $accountId);
							$DBVpn->AddValues(array('login' => $_GET['acc'], 'ip_ex' => sprintf("%u",ip2long($_POST['ip_ex'])), 'tariff_id' => $_POST['tariff_id']));
							
							if ($DBVpn->Update()) {
								if ($_POST["pw1"] == $_POST["pw2"] && strlen($_POST["pw1"]) > 5) {
									$sysCallRes = shell_exec($_SERVER['DOCUMENT_ROOT'].$this->settings['cmd_scripts_dir']."account_set_pw ".$_POST["login"]." ".addcslashes(addcslashes($_POST["pw1"], " !$&*()[];\"'|<>?"),"\\"));
									if ($sysCallRes == '') 
										CF::redirect($Engine->engine_uri.'view/'.$_GET['client_id']);
								}
							}
							
							$this->output['messages']['bad'][] = 'Ошибка обновления аккаунта'.(isset($sysCallRes) ? ':'.$sysCallRes : '');
						} else {
							$this->output['messages']['bad'][] = 'Некорректный ip-адрес.';
						}
					}
					
					$DBVpn->SetTable('vpn_accounts', 'va');
					$DBVpn->AddTable('tariff_plans', 'tp');
					$DBVpn->AddFields(array('tp.name', 'va.id', 'va.login', 'va.ip_ex', 'va.tariff_id'));
					$DBVpn->AddCondFF('tp.tariff_type', '=', 'va.tariff_id');
					$DBVpn->AddCondFS('va.login', '=', $_GET["acc"]);
					$res = $DBVpn->Select(1);
					$row = $DBVpn->FetchAssoc($res);
					$row['name'] = iconv('koi8-r', 'windows-1251', $row['name']);
					$row['ip_ex'] = ereg_replace("\.0$", "", long2ip($row['ip_ex'])); 
					$this->output["accountInfo"] = $row;
					
					$DBVpn->SetTable('tariff_plans');
					$DBVpn->AddField('id');
					$DBVpn->AddField('name');
					$res = $DBVpn->Select();
					$tariff_plans = array();
					while ($row = $DBVpn->FetchAssoc($res)) {
						$row['name'] = iconv('koi8-r', 'windows-1251', $row['name']);
						$tariff_plans[] = $row;
					}
					$this->output["tariff_plans"] = $tariff_plans;
					$this->output['client_id'] = $_GET["client_id"];
					$DBVpn->SetTable('clients');
					$DBVpn->AddField('name');
					$DBVpn->AddCondFS('id', '=', $_GET["client_id"]);
					$res = $DBVpn->Select(1);
					$row = $DBVpn->FetchRow($res);
					$this->output['client_name'] = iconv('koi8-r', 'windows-1251', $row[0]);
					$this->output['mode'] = 'editAcc';
				break;
								
				case "delAcc":
					$DBVpn->SetTable('vpn_accounts');
					$DBVpn->AddCondFS("login", "=", $_GET["acc"]);
					$res = $DBVpn->Select(1);
					$acc = $DBVpn->FetchAssoc($res);
					$DBVpn->SetTable('vpn_accounts');
					$DBVpn->AddCondFS("login", "=", $_GET["acc"]);
					if ($DBVpn->Delete()) {
						$DBVpn->SetTable('vpn_accounts_deleted');
						$DBVpn->AddValues(array("account_id" => $acc["id"],"client_id" => $acc["client_id"],"login" => $acc["login"], "ip_ex" => $acc["ip_ex"], "tariff_id" => $acc["login"], "tariff_type" => 0));
						$DBVpn->Insert();
						$sysCallRes = shell_exec($_SERVER['DOCUMENT_ROOT'].$this->settings['cmd_scripts_dir'].'account_delete '.$_GET['acc']);
						if ($sysCallRes == '')
							CF::redirect($Engine->engine_uri.'view/'.$_GET["client_id"]);
					}
					$this->output['messages']['bad'][] = 'Ошибка при удалении аккаунта'.(isset($sysCallRes) ? ': '.$sysCallRes : '.');
					$this->output['client_id'] = $_GET["client_id"];
					$this->output['mode'] = 'delAcc';
				break;
				
				case "createAcc":
					if (isset($_POST, $_POST['client_id'], $_POST['login'], $_POST['pw1'], $_POST['pw2'], $_POST['ip4']) 
						&& $_POST['login'] && $_POST['pw1'] && $_POST['pw2'] && $_POST['pw1']==$_POST['pw2'] && $_POST['ip4']) {
						$DBVpn->SetTable("vpn_accounts");
						$DBVpn->AddValues(array("client_id"=>$_POST["client_id"],"login"=>$_POST["login"],"ip_ex"=>($_POST["net"]+$_POST["ip4"]),"tariff_id"=>$_POST["tariff_id"]));
						$res = $DBVpn->Insert();
						if ($res) {
							$newAccountId = $DBVpn->LastInsertID();
							$DBVpn->SetTable("tariff_plans");
							$DBVpn->AddField("tariff_type");
							$DBVpn->AddCondFS("id", "=", $_POST['tariff_id']);
							$res = $DBVpn->Select(1);
							$row = $DBVpn->FetchRow($res);
							if (in_array($row[0], $this->settings['pa_tariff_type'])) {
								$DBVpn->SetTable("vpn_balances");
								$DBVpn->AddValues(array('id' => $newAccountId));
								$DBVpn->Insert();
							}
							
							$DBVpn->SetTable('vpn_accounts_status');
							$DBVpn->AddValues(array('id' => $newAccountId, 'status' => 1, 'descr' => iconv('windows-1251', 'koi8-r', $this->settings['accounts_statuses_descrs'][0]), 'cdate' => date("YmdHis"), 'operator'=> ''));
							$DBVpn->Insert();
														 
							$sysCallRes = shell_exec($_SERVER['DOCUMENT_ROOT'].$this->settings['cmd_scripts_dir'].'account_create '.$_POST['login'].' '.addcslashes(addcslashes($_POST["pw1"], " !$&*()[];\"'|<>?"),"\\"));
							if ($sysCallRes == '') 
								CF::redirect($Engine->engine_uri.'view/'.$_POST['client_id']);
							else 
								$this->output['messages']['bad'][] = 'Ошибка при создании аккаунта: '.$sysCallRes;
						} else 
								$this->output['messages']['bad'][] = 'Ошибка при создании аккаунта: выбранные логин/пароль либо ip-адрес уже существуют';
					}
					
					$this->output['mode'] = 'createAcc';
					
					$DBVpn->SetTable("vpn_nets");
					$res = $DBVpn->Select();
					while ($row = $DBVpn->FetchRow($res)) {
						if (!isset($firstRow))
							$firstRow = $row;
						if (isset($_REQUEST["net"]) && $_REQUEST["net"]==$row[1]) {
							$net_ip=$row[1];
						}
						$ips[] = $row[1];
					}
					$this->output['ips'] = $ips;
					$this->output['net_ip'] = isset($net_ip) ? $net_ip : $firstRow[1];
					$this->output['free_ips'] = $this->GetFreeVpnIp($this->output['net_ip'], 256);
					
					$DBVpn->SetTable("tariff_plans");
					$res = $DBVpn->Select();
					while ($row = $DBVpn->FetchAssoc($res)) {
						$row['name'] = iconv('koi8-r', 'windows-1251', $row['name']);
						$tariff_plans[] = $row;
					}
					$this->output['tariff_plans'] = $tariff_plans;
					$this->output['module_uri'] = $this->module_uri;
					
					$DBVpn->SetTable("clients");
					$DBVpn->AddField("name");
					$DBVpn->AddCondFS("id", "=", $_REQUEST['client_id']);
					$res = $DBVpn->Select(1);
					$row = $DBVpn->FetchRow($res);
					$this->output['client_name'] = iconv('koi8-r', 'windows-1251', $row[0]);
					
				break;
				
				default: 
					if (isset($_SESSION["messages"])) {
						$this->output["messages"] = $_SESSION["messages"];
						unset($_SESSION["messages"]);
					}
					$this->output['mode'] = 'list';
				break;
			}
		}
	}
	

	function SendInfoMail($email, $subject, $message) {
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=windows-1251\r\n";
		$headers .= "From: info@nsau.edu.ru\r\n";
		$headers .= "Reply-To: info@nsau.edu.ru\r\n";
		$headers .= "X-Priority: 3\r\n";
		$headers .= "X-MSMail-Priority: Normal\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();
		$subject = SITE_SHORT_NAME.": ".$subject;
					
		mail($email, '=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $subject)).'?=', $message, $headers);
	}
	
	
	function GetClientInfo($clientId) {
		global $DBVpn;
		$DBVpn->SetTable('clients');
		$DBVpn->AddCondFS('id', '=', $clientId);
		$res = $DBVpn->Select(1);
		$clientInfo = array();
		if ($row = $DBVpn->FetchAssoc($res)) {
			$res = $DBVpn->Exec("select name as type from client_types where id=".$row['type']);
			$row2 = $DBVpn->FetchRow($res);
			$row['type_id'] = $row['type'];
			$row['type'] = $row2[0];
			foreach($row as $field=>$value)
				$row[$field] = iconv('koi8-r', 'windows-1251', $value);
				$clientInfo = $row;
		}
		$accounts = array();
		$DBVpn->SetTable('vpn_accounts', 'va');
		$DBVpn->AddTable('tariff_plans', 'tp');
		$DBVpn->AddFields(array('tp.name', 'va.id', 'va.login', 'va.ip_ex'));
		$DBVpn->AddCondFF('tp.tariff_type', '=', 'va.tariff_id');
		$DBVpn->AddCondFS('va.client_id', '=', $clientId);
		$res = $DBVpn->Select();
		$accountsNames = array();
		while ($row = $DBVpn->FetchAssoc($res)) {
			if (empty($accountsNames) || !in_array($row['login'], $accountsNames)) {
				$accountsNames[] = $row['login'];
				$row['name'] =  iconv('koi8-r', 'windows-1251', $row['name']);
				$accounts[] = $row;
			}
		}
		$clientInfo['accounts'] = $accounts;
		return $clientInfo;
	}
	
	
	
	function GetFreeVpnIp($network,$netmask)
	{
		global $DBVpn;
		
		$ip = array();
		$DBVpn->SetTable("vpn_accounts");
		$DBVpn->AddField("ip_ex");
		$res = $DBVpn->Select();
		
		$used_ip = array(); 
		while ($tmp = $DBVpn->FetchRow($res)) 	
		{
			$used_ip[]=$tmp[0];
		}
		$monthdate = mktime(0, 0, -1, date("m"), 1, date("Y"));
		$res = $DBVpn->Exec("select ip_ex from vpn_accounts_deleted where date>FROM_UNIXTIME('$monthdate')");
				
		$deleted_ip = array(); 
		while ($tmp=$DBVpn->FetchRow($res)) 	
		{
			$deleted_ip[] = $tmp[0];
		}
		if ($netmask<2)	$ip[] = $network;
		for ($i = ($network+1); $i < ($network+abs($netmask)-1); $i++)
		{
			if (!in_array($i,$used_ip) && !in_array($i,$deleted_ip)) $ip[] = $i;
		}
	  	return $ip;
	}

	
	function getLoginbyIP($db,$ip)
	{
		$username = $db->getOne("select login from vpn_accounts where ip_ex=".sprintf("%u",ip2long($ip)));
		if ($username == "")
		{
			$handle = popen("grep -v ^# /etc/ppp/ppp.secret | grep ".$ip."$", "r");
			$line = fgets($handle, 512);
			$login = preg_split("/\s+/", $line);
			pclose($handle);
			if ($login[0] != "")
				$username = "<s>".$login[0]."</s>";
		}
		
		if (isset($username) && $username!="")
		{
			$handle = popen ("ifconfig | grep ' $ip '", "r");
			$line = fgets($handle, 1024);
			pclose($handle);
	
			if (ereg(" $ip ", $line))
				$username = "<b>".$username."</b>";
		}
		return $username;
	}
	
	
    function Output()
    {
    	return $this->output;
    }
}

?>