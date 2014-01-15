<?php

class EIdpo
// version: 1.1
// date: 2013-04-15
{
	var $output;
	var $mode;
	var $module_id;
	var $node_id;


	var $captcha_settings;



	var $display_variant;
	var $form_data;


	var $config;
	var $options;
		
	var $isAdminUser;
	 
	function EIdpo($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL)
	{
		global $Engine, $DB;
		// Инициализация модуля
		$this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->output = array();
		$this->output["messages"] = array("good" => array(), "bad" => array());
		$this->output["display_variant"] = NULL;

		$this->form_data = array();

		$this->ProcessConfigFile($global_params);          // Обрабатываем файл конфигурации
		$this->output["programs"] = $this->programs = $this->config["programs"];

		$params = explode(";", $params);
		$this->mode = $this->output["mode"] = $params[0];


		if(isset($params[1])){
			$this->ProcessConfigFile($params[1]);
		}

		//$this->isAdminUser = $this->output["is_admin_user"] = $Engine->OperationAllowed($this->module_id, "olymp_users.handle", -1, $Auth->usergroup_id);




		$this->ProcessHTTPdata();                          // Обрабатываем данные GET и POST



		$this->captcha_settings = $this->config["captcha_settings"];

		if ($this->captcha_settings["enable"])
		{
			if ($this->captcha_settings["use_engine_folder"])
			{
				$this->captcha_settings["path"] = $GLOBALS["Engine"]->FolderURIbyID($this->captcha_settings["path"]);
			}
		}
		$this->output["captcha_uri"] = $this->captcha_settings["enable"] ? $this->captcha_settings["path"] : false;

		$this->captcha_settings = $array["captcha_settings"];
		$this->captcha_settings["enable"] = (bool) $this->captcha_settings["enable"];

		switch ($this->mode) {
			case "user_request":
				if ($Engine->module_uri) {
					$request_params = explode("/", $Engine->module_uri);
					if ($request_params[0] < count($this->output["programs"]))
						$this->output["form_data"]["program_id"] = $request_params[0];

				}

				break;
					
			case "requests_list":
				global $DB;
				$DB->SetTable("idpo_requests");
				$res = $DB->Select();
				while ($row = $DB->FetchAssoc($res)) {
					$requests[] = $row;
				}
				$this->output["requests"] = $requests;
				break;
					
			case "magazine_request":
				break;
				
			case "list_magazine_requests":
				global $DB;
				$DB->SetTable("idpo_magazine_requests");
				$DB->AddOrder("id", true); 
				$res = $DB->Select();
				while ($row = $DB->FetchAssoc($res)) {
					$requests[] = $row;
				}
				$this->output["requests"] = $requests;
				break;
					
			default:
				break;
		}
	}


	function ProcessConfigFile($config_file)
	{

		if (!CF::IsNonEmptyStr($config_file))
		{
			$this->FatalError(1001);
		}
		elseif (!file_exists(INCLUDES . $config_file))
		{
			$this->FatalError(1002, $config_file);
		}
		elseif (!$result = parse_ini_file(INCLUDES . $config_file, true))
		{
			$this->FatalError(1003, $config_file);
		}
		$this->config = $result;



	}




	function ProcessHTTPdata()
	{
		global $DB, $Engine;

		if (isset($_POST[$this->node_id]["cancel"]))
		{
			1;
		}

		elseif (isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id]))
		{
			foreach (array("save_request") as $POST_ACTION)
			{
				if (isset($_POST[$this->node_id][$POST_ACTION]) && is_array($_POST[$this->node_id][$POST_ACTION]))
				{
					$POST_DATA = $_POST[$this->node_id][$POST_ACTION];
					break;
				}
			}
				
			if (isset($POST_DATA))
			{

				switch ($this->mode)
				{
						
					case "user_request":
						//echo isset($_POST[$this->node_id]["code"]);

						if (isset($_POST[$this->node_id]["code"])&&($_POST[$this->node_id]["code"] == $_SESSION["code"])&& isset($_SESSION["code"])&& CF::IsNonEmptyStr($_POST[$this->node_id]["code"])&& isset($POST_DATA["program"], $POST_DATA["name"], $POST_DATA["email"]) && CF::IsNonEmptyStr($POST_DATA["program"])&& CF::IsNonEmptyStr($POST_DATA["name"])&& CF::IsNonEmptyStr($POST_DATA["email"]))
						{
							$DB->SetTable("idpo_requests");
							$DB->AddValues(array(
									"program" => $POST_DATA["program"],
									"name" => $POST_DATA["name"],
									"email" => $POST_DATA["email"],
									"phone" => isset($POST_DATA["phone"]) ? $POST_DATA["phone"] : null,
									"request_text" => isset($POST_DATA["text"]) ? $POST_DATA["text"] : null,
							));

							if (!$DB->Insert(1))
							{
								$this->output["display_variant"] = "save_fail";
							}
							else
							{
									
								$msg_body_template = file_get_contents(INCLUDES . $this->config["request_mail_settings"]["message_body_template"]);
								$vacancies = 10;
								$start_date = "01/01/2013";
								$replaces = array("{PROGRAM}" => $this->programs[$POST_DATA["program"]], "{NAME}" => $POST_DATA["name"]);
								$msg_body = strtr($msg_body_template, $replaces);
								mail(
								$POST_DATA["email"],
								$this->config["request_mail_settings"]["message_subject"],
								implode("\n",  CF::BreakByLbr($msg_body)),
								implode("\r\n", $this->config["mail_headers"])
								);
								mail(
								$this->config["request_notification_mail_settings"]["to"],
								$this->config["request_notification_mail_settings"]["message_subject"],
								implode("\n",  CF::BreakByLbr($this->config["request_notification_mail_settings"]["message_body"])),
								implode("\r\n", $this->config["mail_headers"])
								);

								$this->output["display_variant"] = "request_ok";
							}

								
						}
						else
						{
								
							///
							if (!$POST_DATA["program"])
							{
								$this->output["messages"]["bad"][] = 301;
							}

							if (!$POST_DATA["name"])
							{
								$this->output["messages"]["bad"][] = 302;
							}
							if (!$POST_DATA["email"])
							{
								$this->output["messages"]["bad"][] = 306;

							}
								
								
							if (!isset($_SESSION["code"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 303; // код подтверждения устарел
							}
								
							elseif ($_POST[$this->node_id]["code"] === "")
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 304; // не указан код подтверждения
							}
								
							elseif ($_POST[$this->node_id]["code"] != $_SESSION["code"])
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 305; // неверно указан код подтверждения
							}
								
							$this->output["form_data"] = $POST_DATA;

							$this->output["display_variant"] = "form_fill_error";

						}

							

						break;
					case "magazine_request":
						if (isset($_POST[$this->node_id]["code"])&&($_POST[$this->node_id]["code"] == $_SESSION["code"])&& isset($_SESSION["code"])&& CF::IsNonEmptyStr($_POST[$this->node_id]["code"])&& isset($POST_DATA["period"], $POST_DATA["name"], $POST_DATA["email"], $POST_DATA["type"], $POST_DATA["payment"]) && CF::IsNonEmptyStr($POST_DATA["period"])&& CF::IsNonEmptyStr($POST_DATA["name"])&& CF::IsNonEmptyStr($POST_DATA["email"]) && CF::IsNonEmptyStr($POST_DATA["type"]) && CF::IsNonEmptyStr($POST_DATA["payment"]))
						{
							$DB->SetTable("idpo_magazine_requests");
							$DB->AddValues(array(
									"period" => $POST_DATA["period"],
									"name" => $POST_DATA["name"],
									"email" => $POST_DATA["email"],
									"phone" => isset($POST_DATA["phone"]) ? $POST_DATA["phone"] : null,
									"type" => $POST_DATA["type"],
									"payment" => $POST_DATA["payment"],
									"request_text" => isset($POST_DATA["text"]) ? $POST_DATA["text"] : '',
							));//echo $DB->InsertQuery();exit; 

							if (!$DB->Insert(1))
							{
								$this->output["display_variant"] = "save_fail";
							}
							else
							{
								$msg_body_template = file_get_contents(INCLUDES . $this->config["request_mail_settings"]["message_body_template"]);
								$vacancies = 10;
								$start_date = "01/01/2013";
								$replaces = array("{NAME}" => $POST_DATA["name"]);
								$msg_body = strtr($msg_body_template, $replaces);
								mail(
								$POST_DATA["email"],
								$this->config["request_mail_settings"]["message_subject"],
								implode("\n",  CF::BreakByLbr($msg_body)),
								implode("\r\n", $this->config["mail_headers"])
								);
								$msg_body_template = file_get_contents(INCLUDES . $this->config["request_notification_mail_settings"]["message_body_template"]);
								$replaces = array("{NAME}" => $POST_DATA["name"], "{EMAIL}" => $POST_DATA["email"], 
										"{PHONE}" => $POST_DATA["phone"],
										"{PAYMENT}" => $POST_DATA["payment"], 
										"{PERIOD}" => $POST_DATA["period"], 
										"{TYPE}" => $POST_DATA["type"], 
										);
								$msg_body = strtr($msg_body_template, $replaces); 
								mail(
								$this->config["request_notification_mail_settings"]["to"],
								$this->config["request_notification_mail_settings"]["message_subject"],
								implode("\n",  CF::BreakByLbr($msg_body)),
								implode("\r\n", $this->config["mail_headers"])
								);
									
								$this->output["display_variant"] = "request_ok";
							}
						}
						else
						{
							if (!$POST_DATA["name"])
							{
								$this->output["messages"]["bad"][] = 302;
							}
							if (!$POST_DATA["email"])
							{
								$this->output["messages"]["bad"][] = 306;
									
							}


							if (!isset($_SESSION["code"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 303; // код подтверждения устарел
							}

							elseif ($_POST[$this->node_id]["code"] === "")
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 304; // не указан код подтверждения
							}

							elseif ($_POST[$this->node_id]["code"] != $_SESSION["code"])
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 305; // неверно указан код подтверждения
							}

							$this->output["form_data"] = $POST_DATA;

							$this->output["display_variant"] = "form_fill_error";
						}
						break;
				}
			}



				
		}
	}

	function Output()
	{
		return $this->output;
	}
}

?>