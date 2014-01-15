<?php
class EMailbox
// version: 2.3.1
// date: 2012-10-10
{
    var $module_id;
    var $node_id;
    var $module_uri;
    var $privileges;
    var $output;

    var $mode;
    var $db_prefix;
    var $maintain_cache;
    var $status_id;
    var $min_quota = "300";

var $params;
    /*
    
    */
    function EMailbox($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
    {
	
        global $DB, $DBMail, $Engine, $Auth;
        $this->module_id = $module_id;
        $this->node_id = $node_id;
        $this->module_uri = $module_uri;
        $this->additional = $additional;
        
        $this->db_prefix = "";
      
		$this->ProcessConfigFile($global_params);
        
        $this->output = array();
        $this->output["messages"] = array("good" => array(), "bad" => array());
       // echo $params.' | ';
        $parts = explode(";", $params);
		
		$uri_parts = explode("/", $this->module_uri);
		
		//$Engine->LogAction($this->module_id, "mailbox", 0, $uri_parts[0]);
		
		if ($Auth->user_id) { 
			if (!AT_HOME) {
				$DBMail = new MySQLhandle($this->config["db_params"]["DBMAIL_HOST"], $this->config["db_params"]["DBMAIL_USER"], $this->config["db_params"]["DBMAIL_PASS"], $this->config["db_params"]["DBMAIL_NAME"], $this->config["db_params"]["DBMAIL_PORT"], $DEBUG_LEVEL);
				$DBMail->Exec("SET time_zone = '" . TIMEZONE_OFFSET_FORMATTED . "'");
				$DBMail->Exec("SET NAMES '" . CODEPAGE_DB . "'");
			}
			switch ($parts[0]) {
				case "manage": 
					if ($this->module_uri)
						$this->ManageMailboxes($uri_parts);
					else
						$this->ListMailboxes(); 
						
				break;
				
				case "checkexists":
					$this->CheckExists();
				break;
			
				default:  
					$this->CheckStatus($parts[0]);
				break;
			}
		}
	}
	
	function CheckStatus($mode = null)
	{
	
		global $DB, $Auth;
		$DB->SetTable($this->db_prefix."engine_userparams_values");
		$DB->AddCondFS("userparam_name", "=", "maillogin");
		$DB->AddCondFS("user_id", "=", $Auth->user_id);
        $res = $DB->Select();
		$ml = $mp = 0;
        if(($row = $DB->FetchAssoc($res)) && ($maillogin = $row["value"]))
        {
            $ml = 1;
        }
		
		$DB->SetTable($this->db_prefix."engine_userparams_values");
		$DB->AddCondFS("userparam_name", "=", "mailpass");
		$DB->AddCondFS("user_id", "=", $Auth->user_id);
        $res = $DB->Select();
        if(($row = $DB->FetchAssoc($res)) && ($mailpass = $row["value"]))
        {
            $mp = 1;
        }
		
		if ($ml && $mp && $mode != "hidden_form")
			$this->EnterBox($maillogin, $mailpass);
		/*elseif ($ml && $mp)
			$this->EnterByForm($maillogin, $mailpass);
		else
			$this->FillRequest();*/
		elseif (isset($maillogin) && isset($mailpass))
			$this->EnterByForm($maillogin, $mailpass);
	}
	
	function EnterByForm($ml, $mp) {
		$this->output["ml"] = $ml;
		$this->output["mp"] = $mp;
		$this->output["mode"] = "hidden_form";
	}
	
	function EnterBox($maillogin, $mailpass)
	{ 
		$host = "192.168.30.22";
		$path = MAIL_LOGIN_PAGE;


		
		$snoopy = new Snoopy;
		$snoopy->host = $host;
		$snoopy->port = 80;
		$snoopy->agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12";
		$snoopy->referer = "http://nsau.edu.ru/mail2/";
		$submit_url = "http://nsau.edu.ru/mail2/";
		$submit_vars["_user"] = $maillogin;
		$submit_vars["_pass"] = $mailpass;
		$submit_vars["_action"] = "login";
		$submit_vars["_timezone"] = "6";
		$submit_vars["emulate"] = "1";
		$submit_vars["_token"] = "ddec7bd94555239ff00d9b14d00926f1";
		$sessid = substr($snoopy->results, 0, 32);
		$_SESSION["auth_time"] = 1289793265;
		$_SESSION["user_id"] = 23;
		$_SESSION["username"] = "vlatt@nsau.edu.ru";
		$_SESSION["imap_host"] = "192.168.30.29";
		$_SESSION["password"] = "SM0rFF14dXwDUTVGo+9bJFi6oMGTqz8D";
		$_SESSION["login_time"] = "1289793265";
		
		$snoopy->submit($submit_url, $submit_vars);
		$response_cookies = $snoopy->headers[6];
		$rc_parts = explode(";", $response_cookies);
		$cookie_value = substr($rc_parts[0], strlen($rc_parts[0])-32, 32);
		/*CF::Redirect('/mail2/');*/ die("12121");
	}
	
	function FillRequest() {
		$this->output["mode"] = "request";
	}
	
	function ManageMailboxes($mode) { 
		global $DBMail, $Engine;
		 
		$this->params = $mode;
		if ($mode[0] == "create") {
			$this->MakeEditMailbox("save_post");
		}
		elseif ($mode[0] == "edit") {
			$DBMail->SetTable("mailbox");
			$DBMail->AddCondFS("username", "=", $mode[1]);
			$result = $DBMail->Select();
			if ($row = $DBMail->FetchAssoc($result))
				$this->MakeEditMailbox("edit_post", $row);
			else 
				CF::Redirect($Engine->engine_uri); /**/
		}
		elseif ($mode[0] == "delete") {
			$this->DeleteMailbox($mode[1]);
		}
	}
	
	function MakeEditMailbox($do, $box = null) {   //создание и редактирование
		global $DBMail, $Engine, $DB, $Auth;
	
		if (!isset($_POST[$this->node_id][$do]) && $do == "save_post")
		{
			$this->output["mode"] = $do;
			$this->output["quota_handle"] = $Engine->ModuleOperationAllowed("mail.quota.handle");
		}
		elseif (!isset($_POST[$this->node_id][$do]) && $do == "edit_post")
		{
			$this->output["mode"] = $do;
			$username_parts = explode("@", $box["username"]);
			$box["username"] = $username_parts[0];
			$box["password"] = "";
			$box["repeat_password"] = "";
			$box["quota"] = round($box["quota"]/(1024*1024));
			$this->output["quota_handle"] = $Engine->ModuleOperationAllowed("mail.quota.handle");
			$this->output["data"] = $box;
		}
		else
		{
			$POST_DATA = $_POST[$this->node_id][$do];
			if ($POST_DATA["password"] && $POST_DATA["repeat_password"] && $POST_DATA["password"]!=$POST_DATA["repeat_password"])
				$this->output["messages"]["bad"][] = 308;
			if (!$POST_DATA["password"] && $do=="save_post")
				$this->output["messages"]["bad"][] = 302;
			
			foreach ($this->config["form_elems"] as $key => $data)
			{
				switch ($data["type"])
				{
					case "s":
					case "n":


						foreach ($data["conds"] as $key2 => $data2)
						{
							switch ($key2)
							{
								case "minlen":
									if (strlen($POST_DATA[$key]) < $data2["value"])
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "maxlen":
									if (!$data2["else_error"])
									{
										$POST_DATA[$key] = substr($POST_DATA[$key], 0, $data2["value"]);
									}

									elseif (strlen($POST_DATA[$key]) > $data2["value"])
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "values":
									$parts = explode("|", $data2["value"]);

									if (!in_array($POST_DATA[$key], $parts))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "!values":
									$parts = explode("|", $data2["value"]);

									if (in_array($POST_DATA[$key], $parts))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "regexp":
									$mask = str_replace("//", ":", $data2["value"]);

									if (!preg_match($mask, $POST_DATA[$key]))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "!regexp":
									$mask = str_replace("//", ":", $data2["value"]);

									if (preg_match($mask, $POST_DATA[$key]))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "email":
									if (!CF::ValidateEmail($POST_DATA[$key]))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "?email":
									if (!(($POST_DATA[$key] === "") || CF::ValidateEmail($POST_DATA[$key])))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "int":
									if (!is_int($POST_DATA[$key]))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "int>":
									if (!(is_int($POST_DATA[$key]) && ($POST_DATA[$key] > $data2["value"])))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "int>=":
									if (!(is_int($POST_DATA[$key]) && ($POST_DATA[$key] >= $data2["value"])))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "int<":
									if (!(is_int($POST_DATA[$key]) && ($POST_DATA[$key] < $data2["value"])))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "int<=":
									if (!(is_int($POST_DATA[$key]) && ($POST_DATA[$key] <= $data2["value"])))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;


								case "int!=":
									if (!(is_int($POST_DATA[$key]) && ($POST_DATA[$key] != $data2["value"])))
									{
										$status = false;
										$this->output["messages"]["bad"][] = $data2["else_error"];
									}
									break;
							}
						}
						break;
				}
			}
			if (sizeof($this->output["messages"]["bad"])) {
				$this->output["mode"] = $do;
				if (!isset($POST_DATA["active"]))
					$POST_DATA["active"] = 0;
				$this->output["data"] = $POST_DATA;
			}
			elseif ($do == "save_post") {
				$DBMail->SetTable("mailbox");
				$active = 0;
				$POST_DATA["username"] = strtolower($POST_DATA["username"].$POST_DATA["domain"]);  //"@nsau.edu.ru";
				$POST_DATA["domain"] = substr($POST_DATA["domain"], 1);
				foreach ($POST_DATA as $key => $value) {
					if ($key == "active" && $value == "on") {
						$DBMail->AddValue($key, 1);
						$active = 1;
					}
					elseif ($key == "password")
						$DBMail->AddValue($key, md5($value));
					elseif ($key == "quota") {
						if ($Engine->ModuleOperationAllowed("mail.quota.handle"))
							$DBMail->AddValue($key, $value*1024*1024);
						else
							$DBMail->AddValue($key, $this->min_quota*1024*1024);
					}
					elseif ($key != "repeat_password")
						$DBMail->AddValue($key, $value);
				}
				if (!$active)
					$DBMail->AddValue("active", 0);
				//$DBMail->AddValue("domain", $POST_DATA["domain"]);
				$DBMail->AddValue("maildir", $POST_DATA["domain"]."/".$POST_DATA["username"]."/");
				$DBMail->Insert();
				
				$DBMail->SetTable("alias");
				$DBMail->AddValue("address", $POST_DATA["username"]);
				$DBMail->AddValue("goto", $POST_DATA["username"]);
				$DBMail->AddValue("domain", $POST_DATA["domain"]);
				$DBMail->AddValue("created", date("Y-m-d H:i:s"));
				$DBMail->AddValue("modified", date("Y-m-d H:i:s"));
				$DBMail->AddValue("active", $active ? 1 : 0);
				if ($DBMail->Insert()) {
				
				$DB->SetTable("auth_users");
				$DB->AddCondFS("id", "=", $Auth->user_id);
				$res = $DB->Select();
				$row = $DB->FetchAssoc($res);
				
				// Формируем письмо
				$headers = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=windows-1251\r\n";
				$headers .= "From: info@nsau.edu.ru\r\n";
				$headers .= "Reply-To: info@nsau.edu.ru\r\n";
				//$headers .= "Content-Transfer-Encoding: 8bit";
				$headers .= "X-Priority: 1\r\n";
				$headers .= "X-MSMail-Priority: High\r\n";
				$headers .= "X-Mailer: PHP/" . phpversion();
				$subject = SITE_SHORT_NAME.": новый почтовый ящик";

				$message = "Зарегистрирован новый почтовый ящик: ".$POST_DATA["username"]." . Ящик создан пользователем ".$row["displayed_name"];
					
				//mail($this->config["notice_email"], iconv('cp1251', 'utf-8', $subject), iconv('cp1251', 'utf-8', $message), $headers);
				mail($this->config["notice_email"], '=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $subject)).'?=', $message, $headers);
				$Engine->LogAction($this->module_id, "mailbox", $DBMail->LastInsertID(), "create");
				}
				CF::Redirect($Engine->engine_uri);
			}
			elseif ($do == "edit_post") {
				$DBMail->SetTable("mailbox");
				$active = 0;
				$POST_DATA["username"] = strtolower($POST_DATA["username"].$POST_DATA["domain"]); //"@nsau.edu.ru";
				$POST_DATA["domain"] = substr($POST_DATA["domain"], 1);
				foreach ($POST_DATA as $key => $value) {
					if ($key == "active" && $value == "on") {
						$DBMail->AddValue($key, 1);
						$active = 1;
					}
					elseif ($key == "password" && $value)
						$DBMail->AddValue($key, md5($value));
					elseif ($key == "quota") {
						if ($Engine->ModuleOperationAllowed("mail.quota.handle"))
							$DBMail->AddValue($key, $value*1024*1024);
						else
							$DBMail->AddValue($key, $box["quota"]);
					}
					elseif ($key != "repeat_password" && $key != "password")
						$DBMail->AddValue($key, $value);
				}
				if (!$active)
					$DBMail->AddValue("active", 0);
				//$DBMail->AddValue("domain", $POST_DATA["domain"]);
				$DBMail->AddValue("maildir", $POST_DATA["domain"]."/".$POST_DATA["username"]."/");
				$DBMail->AddCondFS("username", "=", $box["username"]);
				
				$DBMail->Update();
				$Engine->LogAction($this->module_id, "mailbox", $POST_DATA["username"], "update");
				CF::Redirect($Engine->engine_uri);
			}
		}
	}
	
	function ListMailboxes()
	{
		global $DBMail;
	
		/*$DBMail->SetTable("mailbox", "m");
		$DBMail->AddTable("quota", "q");
		$DBMail->AddCondFF("q.username", "=", "m.username");
		if (isset($_GET["sortby"]) && $_GET["sortby"] == "name")
			$DBMail->AddOrder("m.name");
		else if (isset($_GET["sortby"]) && $_GET["sortby"] == "size")
			$DBMail->AddOrder("m.username");
		else
			$DBMail->AddOrder("m.username");*/
			
		$result = $DBMail->Exec("Select distinct *, m.username as username from mailbox as m left join quota as q on m.username = q.username  order by ".((isset($_GET["sortby"]) && $_GET["sortby"] == "name")? "m.name" : ((isset($_GET["sortby"]) && $_GET["sortby"] == "size")? "q.bytes DESC":"m.username")));
		
		while ($row = $DBMail->FetchAssoc($result))
			$this->output["mailboxes"][] = $row;
			
		$this->output["mode"] = "list_mailboxes";
	}
	
	function DeleteMailbox($username)
	{  
		
		global $Auth, $DB, $Engine;
		$Engine->LogAction($this->module_id, "mailbox", 0, $this->module_uri);
		
		
		$DB->SetTable("auth_users");
		$DB->AddCondFS("id", "=", $Auth->user_id);
		$res = $DB->Select();
		$row = $DB->FetchAssoc($res);
	
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=utf-8\r\n";
		$headers .= "From: info@nsau.edu.ru\r\n";
		$headers .= "Reply-To: info@nsau.edu.ru\r\n";
		$headers .= "X-Priority: 2\r\n";
		$headers .= "Content-Transfer-Encoding: 8bit\r\n";
		$headers .= "X-MSMail-Priority: Normal\r\n";
		$headers .= "X-Mailer: PHP/" . phpversion();
		$subject = SITE_SHORT_NAME.": удаление почтового ящика";

		$message = "От пользователя ".$row["displayed_name"]." поступила заявка на удаление почтового ящика ".$username.".";
		$Engine->LogAction($this->module_id, "mailbox", $username, "delete");
		mail($this->config["notice_email"], iconv('cp1251', 'utf-8', $subject), iconv('cp1251', 'utf-8', $message), $headers);

		$this->output["mode"] = "delete_request";
		//header('location: /office/mailboxes');
	}
	
	function ProcessConfigFile($config_file)
	{
		global $Engine;


		if (!CF::IsNonEmptyStr($config_file))
		{
			$this->FatalError(1001);
		}
		elseif (!file_exists(INCLUDES . $config_file))
		{
			$this->FatalError(1002, $config_file);
		}
		elseif (!@$result = parse_ini_file(INCLUDES . $config_file, true))
		{
			$this->FatalError(1003, $config_file);
		}


		$this->config = array();
		
		$this->config["form_elems"] = array();

		foreach ($result["form_elems"] as $key => $elem)
		{
			$parts = explode(":", $key);
			$elem_name = $parts[0];
			$elem_type = (isset($parts[1]) && in_array($parts[1], array("s", "n", "f"))) ? $parts[1] : "s";

			$elem_conds = array();
			$parts = explode(";", $elem);

			foreach ($parts as $elem2)
			{
				$parts2 = explode(":", trim($elem2));
				$flag = isset($parts2[2]) && $parts2[2];

				if ($flag || (($parts2[0] == "maxlen") && (count($parts2) >= 2)))
				{
					$cond_name = $parts2[0];
					$cond_value = isset($parts2[1]) ? $parts2[1] : NULL;

					$elem_conds[$cond_name] = array(
						"value" => $cond_value,
						"else_error" => $flag ? $parts2[2] : false,
						);
				}
			}

			$this->config["form_elems"][$elem_name] = array("type" => $elem_type, "conds" => $elem_conds);
		}
		
		//die(print_r($result));
		$this->config["notice_email"] = $result["other"]["notice_email"];
		$this->config["db_params"] = $result["db_params"];

	}
	
	function FatalError($error_key)
	{
		$message = "";

		if ($GLOBALS["Engine"]->debug_level)
		{
			$errors = array(
				1001 => "Config file (global param #1) not specified",
				1002 => "Cannot find config file <code>%1\$s</code>",
				1003 => "Cannot process config file <code>%1\$s</code>",
				);

			$message .= "<strong>" . get_class($this) . "</strong> module ({$this->module_id}@{$this->node_id})";
			$message .= " fatal error #" . $error_key;

			if (isset($errors[$error_key]))
			{
				$args = func_get_args();
				array_shift($args);

				if ($result = @vsprintf($errors[$error_key], $args))
				{
					$message .= ":<br />\n&nbsp;&nbsp;" . vsprintf($errors[$error_key], $args) . ".";
				}
			}
		}

		die($message);
	}
	
	function CheckExists()
	{
		global $DBMail;
		
		$this->output["mode"] = "checkexists";
		$DBMail->SetTable("mailbox");
		$DBMail->AddCondFS("username", "=", $_POST["username"].$_POST["domain"]);  //."@nsau.edu.ru");
		$res = $DBMail->Select();
		$row = $DBMail->FetchAssoc($res);
		if (($row && $row["username"]!=$_POST["oldname"].$_POST["domain"]) || !$_POST["username"])
			$this->output["exists"] = 1;
		else
			$this->output["exists"] = 0;
	}
	
    function Output()
    {
        return $this->output;
    }
}

?>