<?php

class EPrivate
// version: 2.4.5
// date: 2013-11-22
{
	var $output;
	var $node_id;
	var $module_id;
	var $mode;
	var $db_prefix;
	var $additional;
	var $private_params = array();

	function EPrivate($global_params, $params, $module_id, $node_id, $module_uri, $additional)
	{
		global $Engine, $Auth, $DB;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		$this->additional = $additional;
		$this->private_params['saved_urls_param'] = 'user_saved_urls';
		$this->output["feedback_module_id"] = $this->module_id = $module_id;
		$this->output["mode"] = $this->mode = $params;
		$this->output["feedback_module_id"] = $this->module_id;
		$this->output["user_displayed_name"] = $Auth->user_displayed_name;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
		
		if (isset($_SESSION["messages"])) {
			$this->output["messages"] = $_SESSION["messages"];
			unset($_SESSION["messages"]);
		}
		
		if (!$global_params)
		{
			die("EPrivate module error #1001: config file not specified.");
		}

		elseif (!$array = @parse_ini_file(INCLUDES . $global_params, true))
		{
			die("EPrivate module error #1002: cannot process config file '$global_params'.");
		}

		if (ALLOW_AUTH)
		{
			switch ($this->mode)
			{
				case "normal":
				case "normal_new":
					if ($Auth->logged_in)
					{
						$this->output["status"] = true;

						if (isset($_GET["node"], $_GET["action"]) && ($_GET["node"] == $this->node_id) && ($_GET["action"] == "logout"))
						{
							$Auth->Logout();
							$Engine->ResetAdminUser();
							$this->output["messages"]["good"][] = "Пользователь завершил сеанс работы.";
							$this->output["status"] = false;
						}
								
					}

					elseif (isset($_POST[$this->node_id]["login"]["username"], $_POST[$this->node_id]["login"]["password"]))
					{
						if(trim($_POST[$this->node_id]["login"]["username"]) != "") {
							if ($this->output["status"] = $Auth->Login($_POST[$this->node_id]["login"]["username"], $_POST[$this->node_id]["login"]["password"]))
							{
								$Engine->ResetAdminUser();
								$this->output["user_displayed_name"] = $Auth->user_displayed_name;
								$this->output["messages"]["good"][] = "Авторизация пользователя прошла успешно.";
								$_SESSION["messages"] = $this->output["messages"]; 
								CF::redirect($Engine->engine_uri); 
								
								
								
							} else {
								$this->output["messages"]["bad"][] = "Неверное имя пользователя и/или пароль.";
							}
						} else {
							$this->output["status"] = false;
							$this->output["messages"]["bad"][] = "Необходимо ввести логин.";
						}
					}

					else
					{
						$this->output["status"] = false;
					}
					break;

				case "logout":
					if ($Auth->logged_in)
					{
						$Engine->LogAction(0, "auth", $Auth->user_id, "logout");
						$Auth->Logout();
						$Engine->ResetAdminUser();
						CF::Redirect("/");
						$this->output["messages"]["good"][] = "Пользователь завершил сеанс работы.";
						$this->output["status"] = true;
					}

					else
					{
						$this->output["status"] = false;
					}

					break;
					
				case "remember":
					if(isset($_POST[$this->node_id]["remember"]["login_email"])) {
						$this->remember($_POST[$this->node_id]["remember"]["login_email"], $array);
						$this->output["mode"] = "remember";
					}
					elseif(isset($_GET["code"])){
						if ($this->checkcode($_GET["code"], $array)) {
							$this->output["remember_code"] = $_GET["code"];
							$this->output["mode"] = "passchange";
						}
						else {
							$this->output["mode"] = "message";
							$this->output["messages"]["bad"][] = "Неверный код подтверждения";
						}
					}
					elseif(isset($_POST[$this->node_id]["remember"]["new_password"])) {
						if ($_POST[$this->node_id]["remember"]["new_password"] != $_POST[$this->node_id]["remember"]["confirm_password"]) {
							$this->output["remember_code"] = $_POST[$this->node_id]["remember"]["code"];
							$this->output["mode"] = "passchange";
							$this->output["messages"]["bad"][] = "Пароль подтверждён неверно. Пожалуйста, повторите ввод.";
						}
						elseif($this->updatepass($_POST[$this->node_id]["remember"]["new_password"], $_POST[$this->node_id]["remember"]["code"], $array)) {
							$this->output["mode"] = "message";
							$this->output["messages"]["good"][] = "Пароль изменён успешно. Вы можете пройти процедуру авторизации.";
						}
						else {
							$this->output["mode"] = "message";
							$this->output["messages"]["bad"][] = "Неверный код подтверждения.";
						}
					}					
					break;
					
				case "edit_params":
					$this->output["mode"] = "edit_params";
					$this->edit_params();
					break;
					
				case "show_saveurl_icon": 
					if (!$Auth->logged_in)
						$this->output["mode"] = "";
					else {
						global $DB, $Engine;
						$this->output["user_saved_urls"] = array();
						
						$paramName = $this->private_params['saved_urls_param'];
						$DB->SetTable('engine_userparams_values');
						$DB->AddCondFS('userparam_name', '=', $paramName);
						$DB->AddCondFS('user_id', '=', $Auth->user_id);
						$DB->AddField('value');
						$res = $DB->Select(1);
						if ($row = $DB->FetchAssoc($res)) {
							$folderIds = explode(';', $row['value']);
							if ($folderIds)
								foreach($folderIds as $folderId) {
									if ($folderId) {
										if (strpos(strval($folderId), '#') !== false) {
											$folderId = explode('#', $folderId);
											$folderTitle = $folderId[1];
											$folderId = $folderId[0];
										}
										if (strpos(strval($folderId), strval('%')) !== false) {
											$idParts = explode('%', $folderId);
											$folderId = $idParts[0];
											$dataTail = $idParts[1];
										} else $dataTail = '';
										if ($folderData = $Engine->FolderDatabyID($folderId)) {
											if ($folderTitle) $folderData['title'] = $folderTitle; 
											$folderData['uri'] = explode('/', $folderData['uri']);
											unset($folderData['uri'][0]);
											$folderData['uri'] = '/'.implode('/', $folderData['uri']).$dataTail;
											$folderData['id'] = $folderId.$dataTail;
											$this->output["user_saved_urls"][] = $folderData;
										}
									}
								}
						}
						
					}
					break;
					
				case "del_saved_url":
					$this->output = false; 
					if ($this->additional && $this->additional['urlId']) {
					
						$paramName = $this->private_params['saved_urls_param'];
						global $DB;
						$DB->SetTable('engine_userparams_values');
						$DB->AddCondFS('userparam_name', '=', $paramName);
						$DB->AddCondFS('user_id', '=', $Auth->user_id);
						$DB->AddField('value');
						$res = $DB->Select(1);
						if ($row = $DB->FetchAssoc($res)) {
							$value = preg_replace('/'.$this->additional['urlId'].'(%\S+;|#\S+|;)/', '', $row['value']);
							$DB->SetTable('engine_userparams_values');
							$DB->AddCondFS('userparam_name', '=', $paramName);
							$DB->AddCondFS('user_id', '=', $Auth->user_id);
							$DB->AddValue('value', $value);
							
							$this->output = $DB->Update();
							$this->output = '1';
						}
						
					}
					break;
					
				case "test":
					
					if ($this->additional && $this->additional['saved_url']) {
						$paramName = $this->private_params['saved_urls_param'];
						global $DB;
						$DB->SetTable('engine_userparams');
						$DB->AddCondFS('name', '=', $paramName);
						$DB->AddField('id');
						$res = $DB->Select(1);
						$row = $DB->FetchAssoc($res);
						if ($row) {
							$paramId = $row['id'];
							$uriParts = explode('/', $this->additional['saved_url']);
							
							$i=2;
							$uriTail = '';
							while (is_numeric($uriParts[count($uriParts)-$i])) {
								$uriTail =  $uriParts[count($uriParts)-$i].'/'.$uriTail;   
								$i++;
							}
							
							
							$finiteFoldersArr = array();
							do {
								
								$DB->SetTable('engine_folders');
								if (!empty($finiteFoldersArr)) {
									while ($row = $DB->FetchAssoc($res)) {
										$DB->AddAltFS('id', '=', $row['pid']);
									}
									$DB->AppendAlts();
									$i++;
								} 
								$DB->AddCondFS('uri_part', '=', $uriParts[count($uriParts)-$i]);
								
								$DB->AddField('id');
								$DB->AddField('pid');
								$DB->AddField('title');
								$DB->AddField('descr');
								$res = $DB->Select();
								
								if (empty($finiteFoldersArr)) {
									while ($row = $DB->FetchAssoc($res)) {
										$finiteFoldersArr[] = $row;
									}
								} else {
									$ids = array();
									while ($row = $DB->FetchAssoc($res)) {
										$ids[] = $row['id'];
									}
									foreach($finiteFoldersArr as $ind=>$folder) {
										if (isset($finiteFoldersArr[$ind]) && !in_array($folder['pid'], $ids))
											unset($finiteFoldersArr[$ind]);
										else $finiteFoldersArr[$ind]['pid'] = $ids[array_search($folder['pid'], $ids)];
										
									}
									
								}
								
							} while (count($finiteFoldersArr)>1);
														
							if (!empty($finiteFoldersArr)) {
								
								$foundFolder = $finiteFoldersArr[current(array_keys($finiteFoldersArr))];
								
								
								$folderTitle = (isset($this->additional['url_name']) && $this->additional['url_name']) ? iconv('utf-8', 'windows-1251', $this->additional['url_name']) : $foundFolder['title'];
								$folderDescr = $foundFolder['descr'];
								$DB->SetTable('engine_userparams_values');
								$DB->AddCondFS('userparam_id', '=', $paramId);
								$DB->AddCondFS('user_id', '=', $Auth->user_id);
								$DB->AddField('value');
								
								$res2 = $DB->Select(1); 
								if ($row2 = $DB->FetchAssoc($res2)) {
									$value = $row2['value'].$foundFolder['id'].($uriTail ? '%'.$uriTail : '').'#'.$folderTitle.';';
									
								} else {
									$value = $foundFolder['id'].($uriTail ? '%'.$uriTail : '').'#'.$folderTitle.';';
								}
								$DB->SetTable('engine_userparams_values');
								$DB->AddCondFS('userparam_id', '=', $paramId);
								$DB->AddCondFS('user_id', '=', $Auth->user_id);
								$DB->AddValue('value', $value);
								if ($row2) 
									$DB->Update();
								else {
									$DB->AddValue('user_id', $Auth->user_id);
									$DB->AddValue('userparam_id', $paramId); 
									$DB->AddValue('userparam_name', $paramName);
									$DB->Insert();
									
								}
								$this->output =  json_encode(array('folderId' => $foundFolder['id'], 'descr' => iconv('windows-1251', 'utf-8', $folderDescr), 'url'=>$this->additional['saved_url'], 'title'=> iconv('windows-1251', 'utf-8', $folderTitle)));
							}
						}
						
					
					}
					break;
			}
		}

		else
		{
			$this->output["messages"]["bad"][] = "Доступ запрещён.";
		}
	}
	
	function remember($login_email, $array)
	{
		global $DB, $Engine, $Auth;
		
		if (strpos($login_email, "@"))
		{
			$field = "email";
			$field_name = "e-mail";
		}
		else
		{
			$field = "username";
			$field_name = "логин";
		}
			
		$DB->SetTable("auth_users");
		$DB->AddField("id");
		$DB->AddField("email");
		$DB->AddField("username");
		$DB->AddField("displayed_name");
		$DB->AddCondFS($field, "=", $login_email);
		$res = $DB->Select();
		if (!$row = $DB->FetchAssoc($res)) {
			$this->output["messages"]["bad"][] = "Указанный ".$field_name." отсутствует в базе";
		}
		else {
			$code = md5(time() * rand(1,1000));
			$DB->SetTable($array["remember_settings"]["table"]);
			$DB->AddValue("user_id", $row["id"]);
            $DB->AddValue("code", $code);
			$DB->Insert();
			$this->sendcode($row["email"], $code, $row["username"], $row["displayed_name"], $array);
			$Engine->LogAction(0, "auth", $row['id'], "login_remember");
			$this->output["messages"]["good"][] = "<font color=\"green\"><b>На указанный при регистрации e-mail выслано письмо с кодом подтверждения. Пожалуйста, перейдите по указанной в письме ссылке.</b></font>";
		}
		
	}
	
function sendcode($email, $code, $username, $name, $array)
	{
		
		//Формируем письмо
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=windows-1251\r\n";
		$headers .= "Content-Language: ru\r\n"; // На антиспамы, похоже, не влияет
		// Если автор без кавычек, то меньше баллов у антиспамов
		$headers .= "From: ".'=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $array["remember_settings"]["from_name"])).'?='." <".$array["remember_settings"]["from_email"].">\r\n";
    $headers .= "Return-Path: ".'=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $array["remember_settings"]["from_name"])).'?='." <".$array["remember_settings"]["from_email"].">\r\n";
		//$headers .= "Reply-To: \"".$array["remember_settings"]["from_name"]."\" <".$array["remember_settings"]["from_email"].">\r\n";  // Если не слать, то меньше баллов по антиспаму
		$headers .= "X-Priority: 3\r\n"; // Если выше чем 3 приоритет, то срабатывают антиспамы
		//$headers .= "Content-Transfer-Encoding: 8bit\r\n"; // Если не слать, то баллов по антиспамам меньше
		//$headers .= "X-MSMail-Priority: High\r\n";
		$headers .= "User-Agent: NSAU Mailer\r\n"; // На антиспамы, похоже, не влияет
		$headers .= "X-Mailer: NSAU Mailer\r\n";   // На антиспамы, похоже, не влияет
		//$headers .= "X-Mailer: PHP/" . phpversion();
		//$subject = $array["remember_settings"]["letter_theme"];
		$subject = '=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $array["remember_settings"]["letter_theme"])).'?=';
		$message = "Уважаемый(-ая) ".$name."! <br>\n Кто-то (возможно, Вы) сделал запрос на изменение пароля Вашего аккаунта '".$username."' на сайте ".SITE_SHORT_NAME.". Чтобы подтвердить данное действие, перейдите <a href=\"http://".$_SERVER["SERVER_NAME"]."/remember/?code=".$code."\">по этой ссылке</a> или скопируйте в адресную строку браузера: http://".$_SERVER["SERVER_NAME"]."/remember/?code=".$code;
		if (!mail($email, $subject, $message, $headers, " -f ".$array["remember_settings"]["from_email"]))
			$this->output["messages"]["bad"][] = "Сбой почтовой программы.";
	}
	
	function checkcode($code, $array)
	{
		global $DB;
		
		$DB->SetTable($array["remember_settings"]["table"]);
		$DB->AddField("user_id");
		$DB->AddCondFS("code", "=", $code);
		$res = $DB->Select();
		if ($row = $DB->FetchAssoc($res))
			return $row["user_id"];
		else
			return false;
	}
	
	function updatepass($pass, $code, $array)
	{
		global $DB;
		
		if ($user_id = $this->checkcode($code, $array)){
			$DB->SetTable("auth_users");
			$DB->AddValue("password", md5($pass));
			$DB->AddCondFS("id", "=", $user_id);
			$DB->Update();
			
			$DB->SetTable($array["remember_settings"]["table"]);
			$DB->AddCondFS("code", "=", $code);
			$DB->Delete();
			return true;
		}
		else
			return false;
	}
	
	
	function edit_params() {
		global $DB, $Auth;
		
		if (!isset($_POST[$this->node_id]["params"])) {
			$DB->SetTable("engine_userparams_for_usergroups");
			$DB->AddAltFS("usergroup_id", "=", $Auth->usergroup_id);
			$DB->AddAltFS("usergroup_id", "=", 0);
			$res = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res)) {
				$DB->SetTable("engine_userparams");
				$DB->AddCondFS("id", "=", $row["userparam_id"]);
				$res_param = $DB->Select();
				$userparams[] = $DB->FetchAssoc($res_param);
			}
			
			foreach ($userparams as $up) {
				$DB->SetTable("engine_userparams_values");
				$DB->AddCondFS("userparam_id", "=", $up["id"]);
				$DB->AddCondFS("user_id", "=", $Auth->user_id);
				$res = $DB->Select();
				
				$row = $DB->FetchAssoc($res);
				$up["value"] = $row["value"];
				$this->output["userparams"][] = $up;
			}
			
			/*$DB->SetTable("engine_userparams_values");
			$DB->AddCondFS("user_id", "=", $Auth->user_id);
			$res = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res)) {
				if ($row["userparam_name"] == "maillogin")
					$this->output["maillogin"] = $row["value"];
				elseif ($row["userparam_name"] == "mailpass")
					$this->output["mailpass"] = $row["value"];
			}*/
			
		}
		else {
			foreach ($_POST[$this->node_id]["params"] as $title => $value) {
				$DB->SetTable("engine_userparams_values");
				$DB->AddCondFS("userparam_name", "=", $title);
				$DB->AddCondFS("user_id", "=", $Auth->user_id);
				$res = $DB->Select();
				
				if ($row = $DB->FetchAssoc($res)) {			
					$DB->SetTable("engine_userparams_values");
					$DB->AddValue("value", $value);
					$DB->AddCondFS("userparam_name", "=", $title);
					$DB->AddCondFS("user_id", "=", $Auth->user_id);
					$DB->Update();
				}
				else {
					$DB->SetTable("engine_userparams");
					$DB->AddField("id");
					$DB->AddCondFS("name", "=", $title);
					$res1 = $DB->Select();
					$row1 = $DB->FetchAssoc($res1);

					$DB->SetTable("engine_userparams_values");
					$DB->AddValue("userparam_id", $row1["id"]);
					$DB->AddValue("userparam_name", $title);
					$DB->AddValue("user_id", $Auth->user_id);
					$DB->AddValue("value", $value);
					$DB->Insert();
				}
			}
			
			header ("Location: ./");
		}
	}
	

	function ManageHTTPdata()
	{
		return;
	}
	
	
	function Output()
	{
		return $this->output;
	}
}

?>