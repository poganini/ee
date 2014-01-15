<?php

class EOlymp
// version: 1.1
// date: 2012-10-24
{
	var $module_id;
	var $node_id;
	var $output;

	var $mode;
	var $display_variant;
	var $form_data;

	var $config;
	var $options;
	
	var $usergroup_id;
	var $usergroup_name;
	
	var $isAdminUser;
   
	function EOlymp($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL)
	{
		global $Engine, $DB;
		// Инициализация модуля
		$this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->output = array();
		$this->output["messages"] = array("good" => array(), "bad" => array());
		$this->output["display_variant"] = NULL;
		$this->form_data = array();

		//$this->ProcessConfigFile($params);          // Обрабатываем файл конфигурации
		//$this->output["mode"] = $this->config["general_settings"]["output_mode"];

		$params = explode(";", $params);
		
		$this->isAdminUser = $this->output["is_admin_user"] = $Engine->OperationAllowed($this->module_id, "olymp_users.handle", -1, $Auth->usergroup_id);
		
		$this->usergroup_name = "Участники Олимпиады";
		$DB->SetTable("auth_usergroups");
		$DB->AddField("id");
		$DB->AddCondFS("comment", "=", $this->usergroup_name);
		$res = $DB->Select();
		if ($row = $DB->FetchAssoc($res))
			$this->usergroup_id = $row["id"];
		$DB->FreeRes();
		
		$this->ProcessHTTPdata();                          // Обрабатываем данные GET и POST
		$this->mode = $this->output["mode"] = $params[0];
		$this->output["scripts_mode"] = "default";

		switch ($this->mode) {
			case "form":
				$this->output["country_codes"] = "994, 374, 375, 7, 996, 373, 7, 992, 993, 998, 380, 976, 86";
				$this->output["countries"] = array( 
					array("name" => "Страны СНГ",
						  "countries" => array("Азербайджан", "Армения", "Белоруссия", "Казахстан", "Киргизия", 
							"Молдавия", "Россия", "Таджикистан", "Туркмения", "Узбекистан", "Украина")
					),					
					array("name" => "Зарубежные страны",
						  "countries" => array("Монголия", "Китай", "Другая страна")
					),
				);
				/*foreach ($countries as $ind=>$country)
					$this->output["countries"] [] = array("code" => ($ind+1), "name" => $country);
				*/
				$this->output["subjects"] = $this->GetSubjects();
			break;
			
			case "list":
				global $DB;
				$DB->SetTable("olymp_list");
				$DB->AddField("people_id");
				$DB->AddCondFS("subj_id", "=", $params[1]);
				$res = $DB->Select();
				$people_ids = array();
				while ($row = $DB->FetchAssoc($res))
					$people_ids[] = $row["people_id"];

				$people = array();
				if (!empty($people_ids)) {
					$DB->SetTable("olymp");
					$DB->AddFields(array("last_name", "name", "patronymic"));
					foreach($people_ids as $id)
						$DB->AddAltFS("id", "=", $id);
					$DB->AppendAlts();
					$res = $DB->Select();
					while ($row = $DB->FetchAssoc($res))
						$people[] = $row;
				}
				$this->output["people"] = $people;
			break;
			
			default:
			break;
		}
	}



	function GetSubjects() 
	{
		global $Engine, $DB;
		
		$subjects = array();
		$DB->SetTable("olymp_subj");
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res))
			$subjects[] = $row;
		return $subjects;
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
		$this->config["general_settings"] = $result["general_settings"];

		if ($this->config["general_settings"]["recheck_files_data"])
		{
			$this->config["general_settings"]["having_files"] = false;
			$this->config["general_settings"]["max_filesize"] = 0;
		}


		$this->config["additional_headers"] = $result["additional_headers"];


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

					if ($this->config["general_settings"]["recheck_files_data"] && ($elem_type == "f"))
					{
						if (!$this->config["general_settings"]["having_files"])
						{
							$this->config["general_settings"]["having_files"] = true;
						}

						if (($cond_name == "maxsize") && ($cond_value > $this->config["general_settings"]["max_filesize"]))
						{
							$this->config["general_settings"]["max_filesize"] = $cond_value;
						}
					}
				}
			}

			if ($this->config["general_settings"]["global_maxlen"] && in_array($elem_type, array("s", "n")) && !isset($elem_conds["maxlen"]))
			{
				$elem_conds["maxlen"] = array(
					"value" => $this->config["general_settings"]["global_maxlen"],
					"else_error" => false,
					);
			}

			$this->config["form_elems"][$elem_name] = array("type" => $elem_type, "conds" => $elem_conds);
		}


		$this->config["antispam_settings"] = $result["antispam_settings"];

		if ($result["antispam_settings"]["enable_captcha"]
			&& CF::IsNaturalOrZeroNumeric($result["antispam_settings"]["captcha_path"]))
		{
			$this->config["antispam_settings"]["captcha_path"] = $Engine->FolderURIbyID($result["antispam_settings"]["captcha_path"]);
		}
		
		$this->options = $result["options"];
	}




	function ProcessHTTPdata()
	{
		global $DB, $Auth, $Engine;

				
		if (isset($_POST[$this->node_id]["cancel"]))
		{
			1;
		}

		elseif (isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id]))
		{
			foreach (array("save_form") as $POST_ACTION)
			{
				if (isset($_POST[$this->node_id][$POST_ACTION]) && is_array($_POST[$this->node_id][$POST_ACTION]))
				{
					$POST_DATA = $_POST[$this->node_id][$POST_ACTION];
					break;
				}
			}

			if (isset($POST_DATA))
			{ 

				switch ($POST_ACTION)
				{
					
					case "save_form": 
						if ( (isset($POST_DATA["last_name"], $POST_DATA["name"], $POST_DATA["patronymic"], $POST_DATA["subj"]) && !empty($POST_DATA["subj"]) &&  CF::IsNonEmptyStr($POST_DATA["last_name"]) && CF::IsNonEmptyStr($POST_DATA["name"]) && CF::IsNonEmptyStr($POST_DATA["patronymic"])) && ($this->isAdminUser || (!$this->isAdminUser && isset($POST_DATA["username"], $POST_DATA["password1"], $POST_DATA["password2"], $POST_DATA["email"], $POST_DATA["passport_number"], $POST_DATA["passport_date"], $POST_DATA["passport_who"], $POST_DATA["passport_serial"], $POST_DATA["dob"], $POST_DATA["address"], $POST_DATA["institution_name"], $POST_DATA["graduation_date"], $POST_DATA["agreed"] ) && $POST_DATA["agreed"] && CF::IsNonEmptyStr($POST_DATA["username"]) && CF::IsNonEmptyStr($POST_DATA["password1"]) && ($POST_DATA["password1"] == $POST_DATA["password2"]) && CF::IsNonEmptyStr($POST_DATA["email"])  && CF::IsNonEmptyStr($POST_DATA["dob"]) && CF::IsNonEmptyStr($POST_DATA["address"]) && CF::IsNonEmptyStr($POST_DATA["institution_name"]) && CF::IsNonEmptyStr($POST_DATA["graduation_date"]) && CF::IsNonEmptyStr($POST_DATA["passport_serial"]) && CF::IsNonEmptyStr($POST_DATA["passport_number"]))) )
						{
							if (isset($POST_DATA["username"], $POST_DATA["password1"],$POST_DATA["email"]) && CF::IsNonEmptyStr($POST_DATA["username"]) && CF::IsNonEmptyStr($POST_DATA["password1"]) && CF::IsNonEmptyStr($POST_DATA["email"])) {
							$DB->SetTable("auth_users");	
							$DB->AddValues( array(
								"username" => $POST_DATA["username"],
								"password" => md5($POST_DATA["password1"]),
								"email" => $POST_DATA["email"],
								"displayed_name" => $POST_DATA["last_name"]." ".$POST_DATA["name"]." ".$POST_DATA["patronymic"], 
								"usergroup_id" => $this->usergroup_id,
								"create_user_id" => 0,
							));
							if (!$DB->Insert()) {
								$this->output["display_variant"] = "form_fail";
								break;
							}
							}
							//$smart_time = $DB->SmartTime($POST_DATA["year"], $POST_DATA["month"], $POST_DATA["day"], $POST_DATA["hours"], $POST_DATA["minutes"], $POST_DATA["seconds"]);
							$passport = $POST_DATA["passport_serial"]." ".$POST_DATA["passport_number"]." ".$POST_DATA["passport_date"]." ".$POST_DATA["passport_who"]; 

							$DB->SetTable("olymp");
							$DB->AddValues(array(
								"last_name" => $POST_DATA["last_name"],
								"name" => $POST_DATA["name"],
								"patronymic" => $POST_DATA["patronymic"],
								"passport" => $passport,
								"dob" => $POST_DATA["dob"],
								"gender" => $POST_DATA["gender"],
								"citizenship" => $POST_DATA["citizenship"],
								"address" => $POST_DATA["address"],
								"phone" => isset($POST_DATA["phone"]) ? $POST_DATA["phone"] : null,
								"cell_phone" => isset($POST_DATA["cell_phone"]) ? $POST_DATA["cell_phone"] : null,
								"institution_type" => $POST_DATA["institution_type"],
								"institution_name" => $POST_DATA["institution_name"],
								"graduation_date" => $POST_DATA["graduation_date"],
								));

								
							//$DB->AddValue("time", $smart_time[0], $smart_time[1]);
							//$DB->AddCondFS("id", "=", $POST_DATA["id"]);

							if (!$DB->Insert(1))
							{
								$this->output["display_variant"] = "form_fail"; 
							}
							else
							{
								$people_id = $DB->LastInsertID(); 
								foreach($POST_DATA["subj"] as $subj_id) {
									$DB->SetTable("olymp_list");
									$DB->AddValues(array(
										"subj_id" => $subj_id,
										"people_id" => $people_id,
									));
									if (!$DB->Insert(1)) {
										$this->output["display_variant"] = "form_fail";
										break 2;
									}
								}
								$this->output["display_variant"] = "form_ok";
							}
										
						} 
						else 
						{ 
							if (!$POST_DATA["last_name"])
							{
								$this->output["messages"]["bad"][] = 301; 
							}
	
							if (!$POST_DATA["name"])
							{
								$this->output["messages"]["bad"][] = 302; 
							}
							
							if (!$POST_DATA["patronymic"])
							{
								$this->output["messages"]["bad"][] = 303; 
							}
							
							if (empty($POST_DATA["subj"]))
							{
								$this->output["messages"]["bad"][] = 310; 
							}
							
							if (!$this->isAdminUser && (!$POST_DATA["passport_serial"] || !$POST_DATA["passport_number"] || !$POST_DATA["passport_date"] || !$POST_DATA["passport_who"]))
							{
								$this->output["messages"]["bad"][] = 304; 
							}
	
							if (!$this->isAdminUser && !$POST_DATA["dob"])
							{
								$this->output["messages"]["bad"][] = 305; 
							}
							
							if (!$this->isAdminUser && !$POST_DATA["address"])
							{
								$this->output["messages"]["bad"][] = 306; 
							}
							
							if (!$this->isAdminUser && !$POST_DATA["institution_name"])
							{
								$this->output["messages"]["bad"][] = 307; 
							}
							
							if (!$this->isAdminUser && !$POST_DATA["graduation_date"])
							{
								$this->output["messages"]["bad"][] = 308; 
							}
	
							if (!$this->isAdminUser && !($POST_DATA["username"]))
							{
								$this->output["messages"]["bad"][] = 311; 
							}
							
							if (!$this->isAdminUser && !($POST_DATA["password1"]))
							{
								$this->output["messages"]["bad"][] = 312; 
							}
							
							if (!$this->isAdminUser && empty($POST_DATA["email"]))
							{
								$this->output["messages"]["bad"][] = 313; 
							}
							
							if (!$this->isAdminUser && $POST_DATA["password1"] != $POST_DATA["password2"])
							{
								$this->output["messages"]["bad"][] = 314; 
							}
							
							if (!$this->isAdminUser && !$POST_DATA["agreed"])
							{
								$this->output["messages"]["bad"][] = 309; 
							}
							
							$this->output["form_data"] = $POST_DATA;
							$this->output["display_variant"] = "form_fill_error";
						}
						break;

				}
			}
		}

	}

	
	
	
	function save_apply($array, $ah) {
		global $DB;
		
		$DB->SetTable("nsau_applies");
		
		foreach ($array as $key => $value) {  //названия полей формы совпадают с названиями полей таблицы!
			if ($value == "on")
				$value = 1;
			$DB->AddValue($key, $value);
		}
		
		$DB->Insert();
		
		//Формируем письмо
			$message_body = "Добрый день, ".$array["name"]." ".$array["patronymic"]."! Ваша заявка принята. Отслеживать статус документов Вы можете по адресу: http://".$_SERVER["SERVER_NAME"]."/entrant/apply/show/".$DB->LastInsertID()."/";

			mail(
			$array["email"],
			"Портал НГАУ: Ваше заявление",
			$message_body,
			implode("\r\n", $ah)
			);
	}
	
	
	function edit_apply($nl, $ah) {
		global $DB, $Engine;
		
		$DB->SetTable("nsau_applies");
		$DB->AddCondFS("id", "=", $_POST["edit_apply_id"]);
		$res = $DB->Select();
		$row = $DB->FetchAssoc($res);

		if ($row["status_id"] != $_POST["status_id"] || $row["notices"] != $_POST["notices"]) {
			$DB->SetTable("nsau_applies");
			$DB->AddValue("status_id", $_POST["status_id"]);
			$DB->AddValue("notices", $_POST["notices"]);
			$DB->AddCondFS("id", "=", $_POST["edit_apply_id"]);
			$DB->Update();
			
			//Формируем письмо
			$message_body = "Добрый день, ".$row["name"]." ".$row["patronymic"]."! В статусе Вашего заявления или в замечаниях к нему произошли изменения. Пожалуйста, ознакомьтесь с ними по адресу: http://".$_SERVER["SERVER_NAME"]."/entrant/apply/show/".$row["id"]."/";

			mail(
			$row["email"],
			"Портал НГАУ: Ваше заявление",
			$message_body,
			implode("\r\n", $ah)
			);
		}
		
		CF::Redirect($Engine->engine_uri."manage/");
	}
	
	function delete_apply($id) {
		global $DB, $Engine;
		
		$DB->SetTable("nsau_applies");
		$DB->AddCondFS("id", "=", $id);
		$DB->Delete();
		
		CF::Redirect($Engine->engine_uri."manage/");
	}
	
	function show_apply($id) {
		global $DB;
		
		$DB->SetTable("nsau_applies");
		$DB->AddCondFS("id", "=", $id);
		$res = $DB->Select();
		$row = $DB->FetchAssoc($res);
		
		$this->output["apply"] = $row;
		
		$DB->SetTable("nsau_applies_statuses");
			$res = $DB->Select();
			
		while ($row = $DB->FetchAssoc($res))
			$this->output["statuses"][] = $row;
	}
	
	function Output()
	{
		return $this->output;
	}
}

?>