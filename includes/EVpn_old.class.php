<?php
class EVpn
// version: 1.1.2
// date: 2012-05-17
{
    var $module_id;
    var $node_id;
    var $module_uri;
    var $privileges;
    var $output;
	var $params;
    
	var $mode;
	var $settings;
   
	   
    function EVpn($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
    {
	    global $DB, $DBVpn, $Engine, $Auth;
        $this->module_id = $module_id;
        $this->node_id = $node_id;
        $this->module_uri = $module_uri;
        $this->additional = $additional;
        $this->output = array();
        $this->output["messages"] = array("good" => array(), "bad" => array());
        
     	if ($Auth->user_id) {
		 	$config = parse_ini_file(INCLUDES . $global_params, true);
			if ($config === false) 
				die("Не найден конфигурационный файл модуля");
			$this->settings = $config['settings'];
					
			$DBVpn = new MySQLhandle($config["db_params"]["HOST"], $config["db_params"]["USER"], $config["db_params"]["PASS"], $config["db_params"]["NAME"], $config["db_params"]["PORT"], $DEBUG_LEVEL);
			$DBVpn->SetTable('clients');
			$DBVpn->AddField('id');
			$DBVpn->AddField('name');
			$res = $DBVpn->Select();
			while($row = $DB->FetchAssoc($res)) {
				$row['name'] = iconv('koi8-r', 'windows-1251', $row['name']);
				$clients[] = $row; 
			}
			$this->output['clients'] = $clients;
			$this->output['pass_min_length'] = $this->settings['pass_min_length'];	
			
			$uri_parts = explode("/", $this->module_uri);
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
						$message = "Зарегистрирован новый пользователь VPN: ".$_POST['cinfo']['name']." . Регистрация выполнена пользователем ".$Auth->user_displayed_name."(".$Auth->username.")";
						$this->SendInfoMail($config["mail_params"]["notice_email"], $subject, $message);
						$Engine->LogAction($this->module_id, "vpn_user", $newUserId, "add");	
						CF::redirect($Engine->engine_uri);
					}
					$this->output['mode'] = 'create';
				break;
				
				case "edit":
					if (isset($_POST) && isset($_POST['cinfo']) && !empty($_POST['cinfo'])) {
						$DBVpn->SetTable('clients');
						foreach($_POST['cinfo'] as $field=>$value) {
							$value = iconv('windows-1251', 'koi8-r', $value);
							$DBVpn->AddValue($field, $value);
						}
						$DBVpn->AddValue("operator", $config["db_params"]["USER"]);
						$DBVpn->AddCondFS("id", '=', $uri_parts[1]);
						$DBVpn->Update();
						$subject = "Отредактирован пользователь VPN";
						$message = "Отредактирован пользователь VPN: ".$_POST['cinfo']['name']." . Редактирование выполнено пользователем ".$Auth->user_displayed_name."(".$Auth->username.")";
						$this->SendInfoMail($config["mail_params"]["notice_email"], $subject, $message);
						$Engine->LogAction($this->module_id, "vpn_user", $uri_parts[1], "edit");	
					}
					$this->output['clientInfo'] = $this->GetClientInfo($uri_parts[1]);
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
						$message = "Удалён пользователь VPN: ".$client_name." . Удаление выполнено пользователем ".$Auth->user_displayed_name."(".$Auth->username.")";
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