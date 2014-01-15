<?php

class EGuestbook
// version: 2.7.2
// date: 2013-07-12
{
	var $output;
	var $mode;
	var $module_id;
	var $node_id;

	var $db_prefix;
	var $auth_handle_mode;
	var $use_premoderation;
	var $allow_time_alter;
	var $allow_empty_theme;
	var $break_lines;
	var $display_type;
	var $convert_koi8;
	var $additional_fields;
	var $captcha_settings;
	var $notify_settings;

	var $status;
	var $display_variant;
	var $form_data;

	var $display_access;
	var $manage_access;
	var $full_access;

    /* ¬ params передаЄтс€ режим: announce, addform, combo, либо posts
    	ѕростой распространнЄный режим combo;10 */
	function EGuestbook($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		global $Engine;

		$this->node_id = $node_id;
		$this->module_id = $module_id;
		$this->output = array();
		$this->output["messages"] = array("good" => array(), "bad" => array());

		$this->output["display_access"] = $this->display_access = true/*$display_access*/;
		$this->output["manage_access"] = $this->manage_access = $node_id && $Engine->ModuleOperationAllowed("module.posts.handle");
		//$Auth->logged_in/*$manage_access*/;
		//$this->output["full_access"] = $this->full_access = $Auth->logged_in/*$full_access*/;

		$parts = explode(";", $params);
		$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = $parts[0];

		$this->ProcessINI($parts[2]);

		switch ($this->mode)
		{
			case "announce": {
				if (!isset($parts[1]) || !CF::IsNaturalNumeric($parts[1]))
				{
					die("EGuestbook module error #1002 at node $this->node_id: MODULE FOLDER ID not specified or illegal in ANNOUNCE mode.");
				}

				if (!isset($parts[2]) || !CF::IsNaturalNumeric($parts[2]))
				{
					die("EGuestbook module error #1003 at node $this->node_id: DISPLAY LIMIT not specified or illegal in ANNOUNCE mode.");
				}

				$this->Announce($parts[1], $parts[2]);
			}
			break;

			case "addform": {
				$this->AddForm();
			}
			break;

			case "combo": {
				$this->AddForm(); // break отсутствует, так и надо :)
			}

			case "posts": {
				$per_page_input = (isset($parts[1]) && $parts[1]) ? $parts[1] : ""; // строка настройки дл€ Pager

				if ($this->manage_access) {
					$this->ProcessHTTPdata();

					$this->output["display_variant"] = $this->display_variant;
					$this->output["form_data"] = $this->form_data;
					$this->output["status"] = $this->status;
				}
				$this->Posts($per_page_input);
			}
			break;

			default: {
				die("EGuestbook module error #1100: unknown mode &mdash; $this->mode.");
			}
			break;
		}
	}

	function ProcessINI($filename)
	{
		if (!$filename)
		{
			die("EGuestbook module error #1001 at node $this->node_id: config file not specified.");
		}

		elseif (!$array = parse_ini_file(INCLUDES . $filename, true))
		{
			die("EGuestbook module error #1002 at node $this->node_id: cannot process config file '$filename'.");
		}

		else
		{
			$this->db_prefix = $array["general_settings"]["db_prefix"];
		}

		$this->output["allow_empty_theme"] = $this->allow_empty_theme = $array["general_settings"]["allow_empty_theme"];
		$this->output["auth_handle_mode"] = $this->auth_handle_mode = $array["general_settings"]["auth_handle_mode"];
		$this->use_premoderation = (bool) $array["general_settings"]["use_premoderation"];
		$this->output["show_number"] = (bool) $array["general_settings"]["show_number"];
		$this->allow_time_alter = (bool) $array["general_settings"]["allow_time_alter"];
		$this->break_lines = (bool) $array["general_settings"]["break_lines"];
		$this->output["display_type"] = $this->display_type = $array["general_settings"]["display_type"]; // !!! сделать опции
        $this->convert_koi8 = (bool) $array["general_settings"]["convert_to_koi8"];

		$this->additional_fields = $array["additional_fields"];

		foreach ($this->additional_fields as $key => $data)
		{
			$parts = explode(",", $data);
			$this->additional_fields[$key] = array(
				"subkey" => trim($parts[0]),
				"empty_error_code" => (isset($parts[1]) && CF::IsNaturalNumeric($parts[1])) ? (int) $parts[1] : false,
				"mask" => (isset($parts[1]) && CF::IsNonEmptyStr($parts[2] = trim($parts[2]))) ? str_replace("%comma%", ",", $parts[2]) : false,
				"unmatch_error_code" => (isset($parts[3]) && CF::IsNaturalNumeric($parts[3])) ? (int) $parts[3] : false
				);
		}

		$this->captcha_settings = $array["captcha_settings"];
		$this->captcha_settings["enable"] = (bool) $this->captcha_settings["enable"];

		if ($this->captcha_settings["enable"])
		{
			if ($this->captcha_settings["use_engine_folder"])
			{
				$this->captcha_settings["path"] = $GLOBALS["Engine"]->FolderURIbyID($this->captcha_settings["path"]);
			}
		}


		$this->notify_settings = $array["notify_settings"];
		$this->notify_settings["enable"] = (bool) $this->notify_settings["enable"];
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
			foreach (array("save_post") as $POST_ACTION)
			{
				if (isset($_POST[$this->node_id][$POST_ACTION]) && is_array($_POST[$this->node_id][$POST_ACTION]))
				{
					$POST_DATA = $_POST[$this->node_id][$POST_ACTION];
					break;
				}
			}

			if (isset($POST_DATA))
			{
				foreach ($POST_DATA as $key => $elem)
				{
					$POST_DATA[$key] = trim($elem);
				}


				switch ($POST_ACTION)
				{
					case "save_post":
						if (isset($POST_DATA["id"], $POST_DATA["replace_response_stats"], $POST_DATA["post_text"], $POST_DATA["author_name"], $POST_DATA["response_text"]) && CF::IsNaturalNumeric($POST_DATA["id"]) && (!$this->allow_time_alter || isset($POST_DATA["post_year"], $POST_DATA["post_month"], $POST_DATA["post_day"], $POST_DATA["post_hours"], $POST_DATA["post_minutes"], $POST_DATA["post_seconds"], $POST_DATA["response_year"], $POST_DATA["response_month"], $POST_DATA["response_day"], $POST_DATA["response_hours"], $POST_DATA["response_minutes"], $POST_DATA["response_seconds"]))) {
							$this->status = true;

							if (!CF::IsNonEmptyStr($POST_DATA["author_name"])) {
								$this->status = false;
								$this->output["messages"]["bad"][] = 301; // пустое им€ отправител€
							}

							if (!CF::IsNonEmptyStr($POST_DATA["post_text"])) {
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // не задан текст сообщени€
							}

							if ($this->status) {
								$DB->SetTable($this->db_prefix . "guestbook");
								$DB->AddValues(array(
									"is_active" => 1,
									"post_text" => $POST_DATA["post_text"],
									"post_category" => $POST_DATA["post_category"],
									"author_name" => $POST_DATA["author_name"],
									"response_text" => $POST_DATA["response_text"],
									));

								if ($this->allow_time_alter) {
									$smart_time = $DB->SmartTime($POST_DATA["post_year"], $POST_DATA["post_month"], $POST_DATA["post_day"], $POST_DATA["post_hours"], $POST_DATA["post_minutes"], $POST_DATA["post_seconds"]);
									$DB->AddValue("post_time", $smart_time[0], $smart_time[1]);
								}

								foreach ($this->additional_fields as $key => $data) {
									if (isset($POST_DATA[$data["subkey"]])) {
										$DB->AddValue($key, $POST_DATA[$data["subkey"]]);
									}
								}

								if (CF::IsNonEmptyStr($POST_DATA["response_text"])) {
									if ($POST_DATA["replace_response_stats"]) { // если ещЄ не было ответа, а теперь он поступил, записываем текущего пользовател€ и врем€
										if (!$this->allow_time_alter) {
											$DB->AddValue("response_time", "NOW()", "X");
										}
										$DB->AddValue("respondent_id", $Auth->user_id);
									}

									if ($this->allow_time_alter) {
										$smart_time = $DB->SmartTime($POST_DATA["response_year"], $POST_DATA["response_month"], $POST_DATA["response_day"], $POST_DATA["response_hours"], $POST_DATA["response_minutes"], $POST_DATA["response_seconds"]);
										$DB->AddValue("response_time", $smart_time[0], $smart_time[1]);
									}
								} else { //if (!$POST_DATA["replace_response_stats"]) // если текст пустой, но перед этим не был пустым   !!! подумать над более правильной формулировкой услови€
									$DB->AddValue("response_time", 0);
									$DB->AddValue("respondent_id", 0); // id пользовател€, удалившего ответ
									$DB->AddValue("unrespondent_id", $Auth->user_id); // id пользовател€, удалившего ответ
								}

								$DB->AddCondFS("id", "=", $POST_DATA["id"]);

								if (is_string($DB->Update(1))) {
									$status = false;
									$this->output["messages"]["bad"][] = 402; // ошибка Ѕƒ при сохранении сообщени€ и ответа
								} else {
									$Engine->LogAction($this->module_id, "item", $POST_DATA["id"], "update"); 
									$this->output["messages"]["good"][] = 102; // сообщение и ответ успешно сохранены
									// ¬аш вопрос рассмотрен
									if ($this->notify_settings["enable_to_author"]) { //  && $value)									
										$DB->SetTable($this->db_prefix . "guestbook");
										$DB->AddCondFS("id", "=", $POST_DATA["id"]);
										$res = $DB->Select(1);
										
										if($row = $DB->FetchObject($res))
										{
											$array = array(		// ћассив подстановок перменных с процентами из инишника
												"%server_name%" => $_SERVER["SERVER_NAME"],
												"%site_name%" => SITE_NAME,
												"%site_short_name%" => SITE_SHORT_NAME,
												"%author_name%" => $row->author_name,
												"%author_email%" => $row->author_email,
												"%post_text%" => $row->post_text,
												"%post_time%" => date($this->notify_settings["time_format"]),
												"\\r\\n" => "\r\n" // ƒа, вот так оно и работает :)
												);
											if(!empty($row->author_email))
											{
												$post = str_replace(array_keys($array),
													/*$this->convert_koi8 ? convert_cyr_string(array_values($array),"w","k") :*/ array_values($array), $this->notify_settings);
												//CF::Debug($post);
												if(mail($post["to_author"], $post["subject_to_author"], $post["message_to_author"], $post["headers"]));
											}
										};
										
										
									}
									////////
								}    // else (Update удачно)
							}


							if (!$this->status)
							{
								$this->display_variant = "edit_post";
								$this->form_data = array(
									"id" => $POST_DATA["id"],
									"replace_response_stats" => $POST_DATA["replace_response_stats"],
									"post_text" => htmlspecialchars($POST_DATA["post_text"]),
									"author_name" => htmlspecialchars($POST_DATA["author_name"]),
									"response_text" => htmlspecialchars($POST_DATA["response_text"]),
									);

								if ($this->allow_time_alter)
								{
									$this->form_data["post_year"] = $POST_DATA["post_year"];
									$this->form_data["post_month"] = $POST_DATA["post_month"];
									$this->form_data["post_day"] = $POST_DATA["post_day"];
									$this->form_data["post_hours"] = $POST_DATA["post_hours"];
									$this->form_data["post_minutes"] = $POST_DATA["post_minutes"];
									$this->form_data["post_seconds"] = $POST_DATA["post_seconds"];
									$this->form_data["response_year"] = $POST_DATA["response_year"];
									$this->form_data["response_month"] = $POST_DATA["response_month"];
									$this->form_data["response_day"] = $POST_DATA["response_day"];
									$this->form_data["response_hours"] = $POST_DATA["response_hours"];
									$this->form_data["response_minutes"] = $POST_DATA["response_minutes"];
									$this->form_data["response_seconds"] = $POST_DATA["response_seconds"];
								}

								foreach ($this->additional_fields as $data)
								{
									if (isset($POST_DATA[$data["subkey"]]))
									{
										$this->form_data[$data["subkey"]] = htmlspecialchars($POST_DATA[$data["subkey"]]);
									}
								}
							}
							
						}
						break;
				}
			}
		}


		elseif (isset($_GET["node"], $_GET["action"]) && ($_GET["node"] == $this->node_id))
		{
			switch ($_GET["action"])
			{
				case "edit_post":
					if (isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$DB->SetTable($this->db_prefix . "guestbook");
						$DB->AddFields(array("post_text", "post_category", "author_name", "response_time", "response_text", "respondent_id"));

						if ($this->allow_time_alter)
						{
							$DB->AddExp("YEAR(`post_time`)", "post_year");
							$DB->AddExp("MONTH(`post_time`)", "post_month");
							$DB->AddExp("DAYOFMONTH(`post_time`)", "post_day");
							$DB->AddExp("HOUR(`post_time`)", "post_hours");
							$DB->AddExp("MINUTE(`post_time`)", "post_minutes");
							$DB->AddExp("SECOND(`post_time`)", "post_seconds");
							$DB->AddExp("YEAR(`response_time`)", "response_year");
							$DB->AddExp("MONTH(`response_time`)", "response_month");
							$DB->AddExp("DAYOFMONTH(`response_time`)", "response_day");
							$DB->AddExp("HOUR(`response_time`)", "response_hours");
							$DB->AddExp("MINUTE(`response_time`)", "response_minutes");
							$DB->AddExp("SECOND(`response_time`)", "response_seconds");
						}

						$DB->AddFields(array_keys($this->additional_fields));
						$DB->AddCondFS("id", "=", $_GET["id"]);
						$res = $DB->Select(1);

						if ($row = $DB->FetchObject($res))
						{ 
							$DB->FreeRes($res);
							$this->status = true;

							$replace_response_stats = !$row->respondent_id; // !!! подумать, как сделать лучше

							$this->display_variant = "edit_post";
							$this->form_data = array(
								"id" => $_GET["id"],
								"replace_response_stats" => (int) $replace_response_stats,
								"post_text" => htmlspecialchars($row->post_text),
								"post_category" => htmlspecialchars($row->post_category),
								"author_name" => htmlspecialchars($row->author_name),
								"response_text" => htmlspecialchars($row->response_text),
								"categories" => $this->GetPostCategories()
								);

							if ($this->allow_time_alter)
							{
								$this->form_data["post_year"] = sprintf("%04d", $row->post_year);
								$this->form_data["post_month"] = sprintf("%02d", $row->post_month);
								$this->form_data["post_day"] = sprintf("%02d", $row->post_day);
								$this->form_data["post_hours"] = sprintf("%02d", $row->post_hours);
								$this->form_data["post_minutes"] = sprintf("%02d", $row->post_minutes);
								$this->form_data["post_seconds"] = sprintf("%02d", $row->post_seconds);
								$this->form_data["response_year"] = $replace_response_stats ? "" : sprintf("%04d", $row->response_year);
								$this->form_data["response_month"] = $replace_response_stats ? "" : sprintf("%02d", $row->response_month);
								$this->form_data["response_day"] = $replace_response_stats ? "" : sprintf("%02d", $row->response_day);
								$this->form_data["response_hours"] = $replace_response_stats ? "" : sprintf("%02d", $row->response_hours);
								$this->form_data["response_minutes"] = $replace_response_stats ? "" : sprintf("%02d", $row->response_minutes);
								$this->form_data["response_seconds"] = $replace_response_stats ? "" : sprintf("%02d", $row->response_seconds);
							}

							foreach ($this->additional_fields as $key => $data)
							{
								$this->form_data[$data["subkey"]] = $row->$key;
							}
						}

						else
						{
							$DB->FreeRes($res);
							$this->status = false;
							$this->output["messages"]["bad"][] = 310; // сообщение не найдено в базе
						}
					}
					break;


				case "hide_post":
				case "unhide_post":
					if (isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$this->display_variant = $_GET["action"];
						$this->form_data = array();
						$value = ($_GET["action"] == "unhide_post") ? 1 : 0;

						if (is_string($DB->Exec("UPDATE `" . $this->db_prefix . "guestbook`
							SET `is_active` = '$value'
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1")))
						{
							$this->status = false;
							$this->output["messages"]["bad"][] = $value ? 404 : 403; // ошибка Ѕƒ при скрытии/открытии сообщени€
						}

						else
						{
							$this->status = true;
							$this->output["messages"]["good"][] = $value ? 104 : 103; // сообщени€е успешно скрыто/открыто
							// сюда добавим !!!!
							
							if ($this->notify_settings["enable_to_author"]  && $value)
							{
								$DB->SetTable($this->db_prefix . "guestbook");
								$DB->AddCondFS("id", "=", $_GET["id"]);
								$res = $DB->Select(1);
								
								if($row = $DB->FetchObject($res))
								{
									$array = array(		// ћассив подстановок перменных с процентами из инишника
										"%server_name%" => $_SERVER["SERVER_NAME"],
										"%site_name%" => SITE_NAME,
										"%site_short_name%" => SITE_SHORT_NAME,
										"%author_name%" => $row->author_name,
										"%author_email%" => $row->author_email,
										"%post_text%" => $row->post_text,
										"%post_time%" => date($this->notify_settings["time_format"]),
										"\\r\\n" => "\r\n" // ƒа, вот так оно и работает :)
										);
									if(!empty($row->author_email))
									{
										$post = str_replace(array_keys($array), array_values($array), $this->notify_settings);
										mail($post["to_author"], $post["subject_to_author"], $post["message_to_author"], $post["headers"]);
									}
								};
								
								
							}
							
							//
						}
					}
					break;


				case "delete_post":
					$this->display_variant = "delete_post";
					$this->form_data = array();

					if (isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						if (is_string($DB->Exec("DELETE FROM `" . $this->db_prefix . "guestbook`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1")))
						{
							$this->output["messages"]["bad"][] = 305; // ошибка Ѕƒ при удалении сообщени€
						}

						else
						{
							$this->output["messages"]["good"][] = 105; // сообщение успешно удалено
						}
					}
					break;
			}
		}
	}

	function Announce($module_folder_id, $display_limit)
	{
		global $DB, $Engine;

		$this->output["module_folder_uri"] = $Engine->FolderURIbyID($module_folder_id);

		$DB->SetTable($this->db_prefix . "guestbook");
		$DB->AddFields(array("id", "post_time", "post_text", "post_category", "author_id", "author_name", "author_from", "author_phone", "author_email", "response_time", "response_text", "respondent_id"));

		if ($this->auth_handle_mode != "ignore")
		{
			$DB->AddJoin(AUTH_DB_PREFIX . "users.id", "author_id");
			$DB->AddFields(array(AUTH_DB_PREFIX . "users.displayed_name", AUTH_DB_PREFIX . "users.email"));
		}

		$DB->AddCondFP("is_active");
		$DB->AddOrder("post_time", true);
		$res = $DB->Select($display_limit);


		while ($row = $DB->FetchObject($res))
		{
			$this->output["posts"][] = array(
				"id" => $row->id,
				"post_time" => $row->post_time,
				"post_text" => $this->break_lines ? nl2br($row->post_text) : $row->post_text,
				"post_category" => $row->post_category,
				"author_id" => $row->author_id,
				"author_name" => $row->author_name, // временно
				"author_from" => $row->author_from,
				"author_phone" => $Engine->ModuleOperationAllowed("module.post.contacts") ? $row->author_phone : "",
				"author_email" => $Engine->ModuleOperationAllowed("module.post.contacts") ? $row->author_email : "",
				"response_time" => $row->response_time,
				"response_text" => $this->break_lines ? nl2br($row->response_text) : $row->response_text,
				"respondent_id" => $row->respondent_id
				);
		}

		$DB->FreeRes($res);
	}

	function GetPostCategories() 
	{
		global $DB;
		
		$categories = array();
		$DB->SetTable($this->db_prefix . "guestbook");
		if ($res = $DB->Exec("DESCRIBE ".$this->db_prefix . "guestbook post_category")) {
			preg_match_all('/\'(.*?)\'/is', $DB->FetchObject($res)->Type, $matches);
			if (isset($matches[1])) 
				$categories = $matches[1];
			$DB->FreeRes($res);
		}
		return $categories;
	}

	function AddForm()
	{
		global $Engine, $DB;

		$this->output["name"] = "";
		$this->output["phone"] = "";
		$this->output["post"] = "";
		$this->output["categories"] = $this->GetPostCategories();
		
		if (isset($_POST["are_categories"]))
		{
			unset($_SESSION[$this->db_prefix.'categories']);
			foreach($_POST as $key=>$value)
				if ($key != 'are_categories')
					$_SESSION[$this->db_prefix.'categories'][] = preg_replace('/_/', ' ', $key);
			
		}
		else if (!isset($_SESSION[$this->db_prefix.'categories']) && !isset($_SESSION[$this->db_prefix.'categories_set'])) {
			$_SESSION[$this->db_prefix.'categories'] = $this->output["categories"];
			$_SESSION[$this->db_prefix.'categories'][] = "NOCAT";
			$_SESSION[$this->db_prefix.'categories_set'] = 1;
		}  
		
		$this->output["checked_categories"] = $_SESSION[$this->db_prefix.'categories'];

		foreach ($this->additional_fields as $data)
		{
			$this->output[$data["subkey"]] = "";
		}

		$this->status = true;

		if (isset($_POST[$this->node_id]["name"], $_POST[$this->node_id]["post"]) && (!$this->captcha_settings["enable"] || isset($_POST[$this->node_id]["code"])))
		{
			foreach ($_POST[$this->node_id] as $key => $elem)
			{
				$_POST[$this->node_id][$key] = trim(strip_tags($elem));
			}


			if ($_POST[$this->node_id]["name"] === "")
			{
				$this->status = false;
				$this->output["messages"]["bad"][] = 301; // пустое им€
			}

			if ($_POST[$this->node_id]["post"] === "")
			{
				$this->status = false;
				$this->output["messages"]["bad"][] = 302; // пустое сообщение
			}


			if ($this->captcha_settings["enable"])
			{
				if (!isset($_SESSION["code"]))
				{
					$this->status = false;
					$this->output["messages"]["bad"][] = 303; // код подтверждени€ устарел
				}

				elseif ($_POST[$this->node_id]["code"] === "")
				{
					$this->status = false;
					$this->output["messages"]["bad"][] = 304; // не указан код подтверждени€
				}

				elseif ($_POST[$this->node_id]["code"] != $_SESSION["code"])
				{
					$this->status = false;
					$this->output["messages"]["bad"][] = 305; // неверно указан код подтверждени€
				}
			}


			foreach ($this->additional_fields as $data)
			{
				if (!isset($_POST[$this->node_id][$data["subkey"]])) // ≈сли не определены в POST дополнительные пол€
				{
					$this->status = false;		// “о, без вс€кого вывода ошибок, облом :) 
					break;
				}

				else
				{
					$_POST[$this->node_id][$data["subkey"]] = trim(strip_tags($_POST[$this->node_id][$data["subkey"]]));
				}


				if ($data["empty_error_code"] && !CF::IsNonEmptyStr($_POST[$this->node_id][$data["subkey"]]))
				{
					$this->status = false;
					$this->output["messages"]["bad"][] = $data["empty_error_code"];
				}

				elseif ($data["unmatch_error_code"] && CF::IsNonEmptyStr($_POST[$this->node_id][$data["subkey"]]) && !preg_match($data["mask"], $_POST[$this->node_id][$data["subkey"]]))
				{
					$this->status = false;
					$this->output["messages"]["bad"][] = $data["unmatch_error_code"];
				}
			}

            if ($this->status)
			{
				$DB->SetTable($this->db_prefix . "guestbook");
				//$DB->AddExp("max(id)", "maxid");
				$DB->AddExp("max(pos)", "maxpos");
				//$maxid = 0;
				$maxpos = 0;
				//echo $DB->SelectQuery();exit; 
				$res = $DB->Select(1);
				if($row = $DB->FetchArray($res)) {
					//$maxid = $row[0] + 1;
					$maxpos = $row['maxpos'] + 1;
				}
				$DB->SetTable($this->db_prefix . "guestbook");
				$DB->AddValue("post_time", "NOW()", "X");
				$DB->AddValue("author_name", $_POST[$this->node_id]["name"]); // временно
				$DB->AddValue("post_text", $_POST[$this->node_id]["post"]);
				//if(!$this->allow_empty_theme || ($this->allow_empty_theme && isset($_POST[$this->node_id]["category"])))
				$DB->AddValue("post_category", $_POST[$this->node_id]["category"]);
				//$DB->AddValue("id", $maxid);
				$DB->AddValue("pos", $maxpos);

				foreach ($this->additional_fields as $key => $data)
				{
					$DB->AddValue($key, $_POST[$this->node_id][$data["subkey"]]);
				}

				$DB->AddValue("is_active", $this->use_premoderation ? 0 : 1);
				
				$sql = $DB->InsertQuery(); 
				$sql .= " ON DUPLICATE KEY UPDATE pos=values(pos) + 1";//echo $sql;exit;  

				///if (is_string($DB->Insert()))
				if (!$DB->Exec($sql))
				{
					$this->status = false;
					$this->output["messages"]["bad"][] = 306; // ошибка добавлени€ в Ѕƒ
				}

				else
				{
					$_SESSION["guestbook_success"] = true;
					$DB->Init();

					if ($this->notify_settings["enable"])
					{
						$array = array(		// ћассив подстановок перменных с процентами из инишника
							"%server_name%" => $_SERVER["SERVER_NAME"],
							"%site_name%" => SITE_NAME,
							"%site_short_name%" => SITE_SHORT_NAME,
							"%author_name%" => $_POST[$this->node_id]["name"],
							"%post_number%" => $maxpos,//$DB->LastInsertID(),
							"%post_text%" => $_POST[$this->node_id]["post"],
							"%post_time%" => date($this->notify_settings["time_format"]),
							"\\r\\n" => "\r\n" // ƒа, вот так оно и работает :)
							);
						
						$post = str_replace(array_keys($array), array_values($array), $this->notify_settings);
						
						mail($post["to"], '=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $post["subject"])).'?=',	$post["message"], $post["headers"]);
					}
					$Engine->LogAction($this->module_id, "item", $DB->LastInsertId(), "add"); 
					CF::Redirect($Engine->unqueried_uri); // чтобы очистить POST, а заодно оказатьс€ на первой странице и увидеть свое сообщение
				}
			}

			if (!$this->status)
			{
				$this->output["name"] = htmlspecialchars($_POST[$this->node_id]["name"]);
				$this->output["post"] = htmlspecialchars($_POST[$this->node_id]["post"]);

				foreach ($this->additional_fields as $data)
				{
					$this->output[$data["subkey"]] = htmlspecialchars(@$_POST[$this->node_id][$data["subkey"]]);
				}
			}
		}

		elseif (isset($_SESSION["guestbook_success"]) && ($_SESSION["guestbook_success"] === true))
		{
			$this->status = true;
			unset($_SESSION["guestbook_success"]);
			$this->output["messages"]["good"][] = 101; // сообщение успешно добавлено
		}

		elseif (isset($_SESSION["guestbook_success"]))
		{
			unset($_SESSION["guestbook_success"]);
		}

		else
		{
			$this->status = false;
		}

		$this->output["captcha_uri"] = $this->captcha_settings["enable"] ? $this->captcha_settings["path"] : false;
		$this->output["status"] = $this->status;
	}

	function Posts($per_page_input)
	{
		global $DB, $Engine;
		
		if ($per_page_input)
		{
			$this->output["posts"] = array();

			require_once INCLUDES . "Pager.class";

			$DB->SetTable($this->db_prefix . "guestbook");
			$DB->AddExp("COUNT(*)");

			if (!$this->manage_access)
			{
				$DB->AddCondFP("is_active");
			}
			
			if (isset($_SESSION[$this->db_prefix.'categories'])) {
				foreach($_SESSION[$this->db_prefix.'categories'] as $checkedCategory)
					$DB->AddAltFS("post_category", "=", $checkedCategory);
				if (in_array('NOCAT', $_SESSION[$this->db_prefix.'categories'])) $DB->AddAltFS("post_category", "=", "");
			} else $DB->AddAltFS("post_category", "=", "NULL");

			$DB->AppendAlts(); 
			
			$res = $DB->Select();
			list($num) = $DB->FetchRow($res);
			$DB->FreeRes($res);


			$parts = explode("|", $per_page_input);
			$Pager = new Pager($num, @$parts[0], @$parts[1], @$parts[2], @$parts[3]);
			$this->output["pager_output"] = $result = $Pager->Act();
		}


		$DB->SetTable($this->db_prefix . "guestbook");
		$DB->AddFields(array("id", "is_active", "post_time", "post_text", "post_category", "author_id", "author_name", "author_from", "author_phone", "author_email", 
		"response_time", "response_text", "respondent_id", "pos"));

		if ($this->auth_handle_mode != "ignore")
		{
			$DB->AddJoin(AUTH_DB_PREFIX . "users.id", "author_id");
			$DB->AddFields(array(AUTH_DB_PREFIX . "users.displayed_name", AUTH_DB_PREFIX . "users.email"));
		}

		if (!$this->manage_access)
		{
			$DB->AddCondFP("is_active");
		}
		
		if (isset($_SESSION[$this->db_prefix.'categories'])) {
			foreach($_SESSION[$this->db_prefix.'categories'] as $checkedCategory)
				$DB->AddAltFS("post_category", "=", preg_replace('/_/', ' ', $checkedCategory));
			if (in_array('NOCAT', $_SESSION[$this->db_prefix.'categories'])) $DB->AddAltFS("post_category", "=", "");
		} else $DB->AddAltFS("post_category", "=", "NULL");
		
		$DB->AppendAlts();

		$DB->AddOrder("post_time", true);
		$res = $DB->Select($per_page_input ? $result["db_limit"] : NULL, $per_page_input ? $result["db_from"] : NULL);


		while ($row = $DB->FetchObject($res))
		{
			$this->output["posts"][] = array(
				"id" => $row->id,
				"is_active" => (bool)$row->is_active,
				"post_time" => $row->post_time,
				"post_text" => $this->break_lines ? nl2br($row->post_text) : $row->post_text,
				"post_category" => $row->post_category,
				"author_id" => $row->author_id,
				"author_name" => $row->author_name, // временно  poganini: что временно?
				"author_from" => $row->author_from,
				"author_phone" => $Engine->ModuleOperationAllowed("module.post.contacts") ? $row->author_phone : "",
				"author_email" => $Engine->ModuleOperationAllowed("module.post.contacts") ? $row->author_email : "",
				"response_time" => $row->response_time,
				"response_text" => $this->break_lines ? nl2br($row->response_text) : $row->response_text,
				"respondent_id" => $row->respondent_id, 
				"pos" => $row->pos
				);
		}

		$DB->FreeRes($res);
	}

	function Output()
	{
		return $this->output;
	}
}

?>