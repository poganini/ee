<?php

class EFeedback
// ������ ����� �������� �����
// version: 2.2.1
// date: 2012-05-21
//   global params: 0:[!]config_file
//   params: <none>
{
	var $module_id;
	var $node_id;
	var $output;

	var $mode;
	var $display_variant;
	var $form_data;

	var $config;
	var $options;
	    
	    /*� params ��������� ���� ini*/
	function EFeedback($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = null)
	{
		global $Engine, $Auth;
		// ������������� ������
		$this->output = array();
		$this->output["module_id"] = $this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->output["messages"] = array("good" => array(), "bad" => array());
		$this->display_variant = NULL;
		$this->form_data = array();

/*		// ��������� ����� ����������
		$config_file = $params; */
		
		/*$parts = explode(';', $params);
		if ($parts[0] == 'save_user_message') {
			$this->output =  $this->ProcessUserMessage($_POST['data'], $Auth->user_id);
			return;
		}
		else  if ($parts[0] == 'set_user_message_as_checked'){
			$this->output = $this->SetUserMessageAsChecked($_POST['data']);
			return;
		}
		else if ($parts[0] == 'user_alerts'){
			$this->output["scripts_mode"] = $this->output["mode"] = $parts[0];
			$itemsPerPage = isset($parts[2]) ? $parts[2] : 2 ;
			$pageNum = isset($parts[1]) ? $itemsPerPage*($parts[1]-1) : 0 ;
			$this->output["user_messages"] = $userMessages = $this->GetUserAlerts($pageNum, $itemsPerPage);
			require_once INCLUDES . "Pager.class";
			list($messageCount) = $this->GetUserAlertsCount();
			$callback = $this->module_id."%user_alerts;[PAGE_NUM]%onResponse";
			$Pager = new Pager($messageCount, $itemsPerPage, 4, null, null, array(), $callback); 
			$this->output["pager_output"] = $Pager->Act();
			if (isset($parts[1])) {
				$this->output = array();
				foreach ($userMessages as $ind=>$message) {
					$userMessages[$ind]['text'] = iconv('windows-1251', 'utf-8', $message['text']);
					$userMessages[$ind]['comment'] = iconv('windows-1251', 'utf-8', $message['comment']);
				}
				$this->output[] = $userMessages;
			}
			return;
		}
		else if ($parts[0] == 'user_alert_form'){
			$this->output["feedback_module_id"] = $this->module_id;
			$this->output["scripts_mode"] = $this->output["mode"] = $parts[0];
			return;
		}*/
		
		$this->ProcessConfigFile($params);          							// ������������ ���� ������������

		$maxlen = array();                                 						// C����� ������ ��� ������������ ���� �����.

		foreach ($this->config["form_elems"] as $key => $data) { 				// �������� �� ����� �����, ��������� � ������������,...
			if ($data["type"] != "f") {                       					// ...� ���� ��� ���� - �� "����",...			
				$this->form_data[$key] = "";                   					// ...�� ���������� ����� ������ �������� ��� ���� �����,...
				$maxlen[$key] = isset($data["conds"]["maxlen"]) ? $data["conds"]["maxlen"]["value"] : false;	// ...� ����� ��������� ������������ ����� ��� ���� ����� (��� false, ���� ��� �� �������).
			}
		}

		if ($this->config["antispam_settings"]["enable_hhf"]) { 				// ��� ���������� ��������� HHF...		
			$key = $this->config["antispam_settings"]["hhf_elem_name"];
			$this->form_data[$key] = "";										// ...����� ���������� ���������� ������ �������� � ��� ����...
			$maxlen[$key] = $this->config["antispam_settings"]["hhf_elem_maxlen"]; 		// ...� ��� maxlen.
		}
		
		$this->output["scripts_mode"] = $this->output["mode"] = $this->config["general_settings"]["output_mode"];

		$this->output["maxlen"] = $maxlen;                 						// ����� � output ������ ������������ ���� �����,...
		$this->output["having_files"] = $this->config["general_settings"]["having_files"]; 		// ...��, ���� �� � ����� �����,...
		$this->output["max_filesize"] = $this->config["general_settings"]["max_filesize"]; 		 // ...� ����� ������������ ������ �����.

		$this->ProcessHTTPdata();                          						// ������������ ������ GET � POST

		$this->output["display_variant"] = $this->display_variant;
		$this->output["form_data"] = $this->form_data;
		$this->output["options"] = $this->options;
	}

	function GetUserAlertsCount()  {
		global $Engine,$DB;
		$DB->SetTable("nsau_users_messages");
		$DB->AddExp("COUNT(*)");
		$res = $DB->Select();
		return $DB->FetchRow($res);
	}

	function GetUserAlerts($offset = 0, $limit = 0, $showAll = true)
	{
		global $Engine,$DB;
		$messages = array();
		/*$DB->SetTable("nsau_users_messages", "um");
		$DB->AddTable("auth_users", "u");
		$DB->AddCondFF("u.id", "=", "um.user_id");
		$DB->AddFields(array("um.date", "um.page", "um.text", "um.comment", "um.is_checked"));
		//$DB->AddField("u.displayed_name", "name"); */
		if (!$showAll) 
			$DB->AddCondFS('is_checked', '=', 1);
		if ($res = $DB->Exec("select um.id, um.date, um.page, um.text, um.comment, um.is_checked, u.displayed_name as username from nsau_users_messages as um left join auth_users as u on u.id = um.user_id order by um.date desc ".($limit ? "limit ".$offset.",".$limit : "" ) )) {
			while ($row = $DB->FetchAssoc($res))
				$messages[] = $row;
		}
		return $messages;
	}
	
	function SetUserMessageAsChecked($data)
	{
		global $Engine, $DB;
		$DB->SetTable("nsau_users_messages");
		$DB->AddCondFS("id", "=", $data['id']);
		$DB->AddValue("is_checked", 1);
		return $DB->Update();
	}

	function ProcessUserMessage($data, $userId)
	{
		global $Engine, $DB;
		$data['page'] = str_replace('"', '', $data['page']);
		$DB->SetTable("nsau_users_messages");
		$DB->AddValues(array('user_id' => $userId, 'text'=>iconv('utf-8', 'windows-1251', $data['text']), 'comment'=>iconv('utf-8', 'windows-1251', $data['comment']), 'page'=> $data['page']));
		return $DB->Insert();
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
			foreach (array("send_message") as $POST_ACTION)
			{
				if (isset($_POST[$this->node_id][$POST_ACTION]) && is_array($_POST[$this->node_id][$POST_ACTION]))
				{
					$POST_DATA = $_POST[$this->node_id][$POST_ACTION];
					break;
				}
			}

			if (isset($POST_DATA))
			{
//				foreach ($POST_DATA as $key => $elem)
//				{
//					$POST_DATA[$key] = trim($elem);
//				}


				switch ($POST_ACTION)
				{
					case "send_message":
						if (/*$Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id)*/ 1)
						{
							foreach ($this->config["form_elems"] as $key => $data)
							{
								if (($data["type"] = "f") && !isset($POST_DATA[$key]))
								{
									if ($this->config["general_settings"]["require_all_elems"])
									{
										break;
									}

									else
									{
										$POST_DATA[$key] = "";
									}
								}
							}

							if ($this->config["antispam_settings"]["enable_captcha"] && !isset($POST_DATA[$this->config["antispam_settings"]["captcha_elem_name"]]))
							{
								break;
							}


							if ($this->config["antispam_settings"]["enable_hhf"] && !isset($POST_DATA[$this->config["antispam_settings"]["hhf_elem_name"]]))
							{
								break;
							}


							$status = true;



							foreach ($this->config["form_elems"] as $key => $data)
							{
								switch ($data["type"])
								{
									case "s":
									case "n":

										if (($data["type"] == "s") && $this->config["general_settings"]["trim_strings"])
										{
											$POST_DATA[$key] = trim($POST_DATA[$key]);
										}

										elseif (($data["type"] == "n") && $this->config["general_settings"]["preprocess_numbers"])
										{
											$POST_DATA[$key] = str_replace(",", ".", $POST_DATA[$key]);
											$POST_DATA[$key] = preg_replace("/[^\d\.]/", "", $POST_DATA[$key]);

											if (CF::IsNonEmptyStr($POST_DATA[$key]))
											{
												$POST_DATA[$key] = floatval($POST_DATA[$key]);

												if (($POST_DATA[$key] - floor($POST_DATA[$key])) == 0)
												{
													$POST_DATA[$key] = intval($POST_DATA[$key]);
												}
											}
										}


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


									case "f":
										break; // !!! ����� ���� �� ������������...
								}
							}

							if (!$status)
							{
								$this->display_variant = "form_fill_error";

								foreach ($this->config["form_elems"] as $key => $data)
								{
									if ($data["type"] != "f")
									{
										$this->form_data[$key] = $POST_DATA[$key];
									}
								}

								if ($this->config["antispam_settings"]["enable_hhf"])
																													 // ��� ���������� ��������� HHF...
								{
									$key = $this->config["antispam_settings"]["hhf_elem_name"];

									if (!$this->config["antispam_settings"]["hhf_ntype_elem"] && $this->config["general_settings"]["trim_strings"])
									{
										$POST_DATA[$key] = trim($POST_DATA[$key]);
									}

									elseif ($this->config["antispam_settings"]["hhf_ntype_elem"] && $this->config["general_settings"]["preprocess_numbers"])
									{
										$POST_DATA[$key] = str_replace(",", ".", $POST_DATA[$key]);
										$POST_DATA[$key] = preg_replace("/[^\d\.]/", "", $POST_DATA[$key]);
										$POST_DATA[$key] = floatval($POST_DATA[$key]);

										if (($POST_DATA[$key] - floor($POST_DATA[$key])) == 0)
										{
											$POST_DATA[$key] = intval($POST_DATA[$key]);
										}
									}

									$this->form_data[$key] = $POST_DATA[$key];
																													 // ...����� ���������� ���������� �������� � ��� ����
								}
							}


							elseif ($this->config["antispam_settings"]["enable_hhf"] && CF::IsNonEmptyStr($POST_DATA[$this->config["antispam_settings"]["hhf_elem_name"]]))
							{
								if ($this->config["antispam_settings"]["hhf_report_ok"])
								{
									$this->display_variant = "send_ok";
								}

								else
								{
									$this->display_variant = "send_fail"; // !!! ��������, ��� ���� �����, ���� ��������� ��������� hhf_report_ok. ���� ��� ������� ������ ��������
								}
							}


							else
							{
								$message_body = file_get_contents(INCLUDES . $this->config["general_settings"]["message_body_template"]); // !!! ������� �������� ������������� � ����������� �����! � ��������, ��� ������, ���� ���� �� ������/����������

								foreach ($this->config["form_elems"] as $key => $data)
								{
									if ($data["type"] != "f")
									{
										$message_body = str_replace("{" . $key . "}", $POST_DATA[$key], $message_body);
									}
								}

								// !!! �������� ����������� ������� �� ������� !!! �����������
								$this->config["general_settings"]["message_body_newline_char"] = "\n";
								$this->config["general_settings"]["message_headers_newline_char"] = "\r\n";


								$message_body = str_replace("%ip%", $_SERVER["REMOTE_ADDR"], $message_body);
								$message_body = preg_replace("/%time:([^%]+)%/e", "date(\"\\1\")", $message_body);
								$message_body = preg_replace("/%stime:([^%]+)%/e", "gmdate(\"\\1\", " . (time() + TIMEZONE_OFFSET_SECONDS) . ")", $message_body);
								$message_body = implode($this->config["general_settings"]["message_body_newline_char"], CF::BreakByLbr($message_body));
								$message_body = wordwrap($message_body, 70, $this->config["general_settings"]["message_body_newline_char"]);

								if (mail(
									$this->config["general_settings"]["message_to"],
									'=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $this->config["general_settings"]["message_subject"])).'?=',
									//$this->config["general_settings"]["message_subject"],
									$message_body,
									implode($this->config["general_settings"]["message_headers_newline_char"], $this->config["additional_headers"])
									))
								{
									$this->display_variant = "send_ok";
								}

								else
								{
									$this->display_variant = "send_fail";
								}
							}
						}
						break;


					case "save_item":
						if ($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) && isset($POST_DATA["id"], $POST_DATA["cat_id"], $POST_DATA["uripart"], $POST_DATA["year"], $POST_DATA["month"], $POST_DATA["day"], $POST_DATA["hours"], $POST_DATA["minutes"], $POST_DATA["seconds"], $POST_DATA["title"], /*$POST_DATA["image_ext"], */$POST_DATA["short_text"], $POST_DATA["full_text"]) && CF::IsNaturalNumeric($POST_DATA["id"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["cat_id"]))
						{
							if ($this->Imager && isset($_FILES[$this->node_id . "_file"]) && CF::IsNonEmptyStr($_FILES[$this->node_id . "_file"]["tmp_name"]) && $_FILES[$this->node_id . "_file"]["size"])
							{
								$FILE_DATA = $_FILES[$this->node_id . "_file"];
							}

							else
							{
								$FILE_DATA = false;
							}

							$this->status = true;


							if ($FILE_DATA)
							{
								if (is_array($result = $this->Imager->GrabUploadedFile($FILE_DATA)))
								{
									$this->status = false;
									$this->output["messages"] = array_merge($this->output["messages"], $result);
								}
							}


							if (!$POST_DATA["cat_id"])
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 301; // �� ������� ���������!)
							}

							if (CF::IsNonEmptyStr($POST_DATA["uripart"]) && (preg_match("/^\d+$/", $POST_DATA["uripart"]) || !preg_match("/^[a-z0-9_-]+$/i", $POST_DATA["uripart"])))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // ������������ ������ uripart (������ ��������� �����, �����, ����� � ������� (������ ��������� �����, �����, ����� � �������; ������, ��������� ������� ��&nbsp;����, �����������!)
							}

							if (!CF::IsNonEmptyStr($POST_DATA["short_text"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 303; // �� ����� ������� �����
							}


							if ($this->status)
							{
								$smart_time = $DB->SmartTime($POST_DATA["year"], $POST_DATA["month"], $POST_DATA["day"], $POST_DATA["hours"], $POST_DATA["minutes"], $POST_DATA["seconds"]);

								$DB->SetTable($this->db_prefix . "items");
								$DB->AddValues(array(
									"cat_id" => $POST_DATA["cat_id"],
									"uripart" => $POST_DATA["uripart"],
									"title" => $POST_DATA["title"],
									"short_text" => $POST_DATA["short_text"],
									"full_text" => $POST_DATA["full_text"],
									));

								if (1/*$this->maintain_cache*/) // !!! ������� ������
								{
									$DB->AddValue("cached_text", substr(CF::RefineText($POST_DATA["full_text"]), 0, 65535)); // !!! ������� ������������� ������� ������ �� ������ MySQLhandle
								}

								$DB->AddValue("time", $smart_time[0], $smart_time[1]);
								$DB->AddCondFS("id", "=", $POST_DATA["id"]);

								if (!$DB->Update(1))
								{
									$status = false;
									$this->output["messages"]["bad"][] = 402; // ������ �� ��� ���������� ���������
								}

								else
								{
									$this->output["messages"]["good"][] = 102; // �������� ������� ��������
									$Engine->LogAction($this->module_id, "item", $POST_DATA["id"], "alter");
								}
							}


							if ($FILE_DATA && $this->status)
							{
								if (is_array($result = $this->Imager->CreateOutputFiles($POST_DATA["id"])))
								{
									$this->status = false;
									$this->output["messages"] = array_merge($this->output["messages"], $result);
								}

								if ($this->status)
								{

									$DB->SetTable($this->db_prefix . "items");
									$DB->AddValues(array(
										"image_data" => $this->Imager->ext,
//										"image_width" => $MAIN_OUTPUT_WIDTH,
//										"image_height" => $MAIN_OUTPUT_HEIGHT,
										));
									$DB->AddCondFS("id", "=", $POST_DATA["id"]);

									if (!$DB->Update(1))
									{
										$this->output["messages"]["bad"][] = 450; // ������ ���������� ���������� �� �����������
									}

									else
									{
										$this->output["messages"]["good"][] = 150; // ���������� �� ����������� ���������
									}
								}
							}


							if (!$this->status)
							{
								$this->display_variant = "edit_item";
								$this->form_data = array(
									"id" => $POST_DATA["id"],
									"cat_id" => $POST_DATA["cat_id"],
									"uripart" => $POST_DATA["uripart"],
									"year" => $POST_DATA["year"],
									"month" => $POST_DATA["month"],
									"day" => $POST_DATA["day"],
									"hours" => $POST_DATA["hours"],
									"minutes" => $POST_DATA["minutes"],
									"seconds" => $POST_DATA["seconds"],
									"title" => htmlspecialchars($POST_DATA["title"]),
									"short_text" => htmlspecialchars($POST_DATA["short_text"]),
									"full_text" => htmlspecialchars($POST_DATA["full_text"])
									);
							}

							if ($FILE_DATA)
							{
								@unlink($FILE_DATA["tmp_name"]);
							}
						}
						break;



					case "add_comment":
					{
						if ($Engine->ModuleOperationAllowed("cat.comment", $this->current_cat_id) && isset(/*$POST_DATA["item_id"], */$POST_DATA["author_name"], $POST_DATA["author_from"], $POST_DATA["author_email"], $POST_DATA["text"]) && CF::IsNaturalNumeric($POST_DATA["item_id"]))
						{
							$this->status = true;

							if ($this->status && !CF::IsNonEmptyStr($POST_DATA["author_name"] = trim(htmlspecialchars($POST_DATA["author_name"]))))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 311; // �� ��������� ���� "�����"
							}

							if ($this->status && CF::IsNonEmptyStr($POST_DATA["author_email"] = trim(htmlspecialchars($POST_DATA["author_email"]))) && !CF::ValidateEmail($POST_DATA["author_email"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 312; // �������� ������ ���� e-mail
							}


							if ($this->status)
							{
								if ($this->comments_message_size_limit)
								{
									$POST_DATA["text"] = substr($POST_DATA["text"], 0, $this->comments_message_size_limit);
								}

								$POST_DATA["text"] = trim(htmlspecialchars($POST_DATA["text"]));

								if (!CF::IsNonEmptyStr($POST_DATA["text"]))
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 313; // �� ��������� ���� "����� �����������"
								}
							}


							if ($this->status && $this->comments_captcha_path)
							{
								if (!isset($_SESSION["code"]))
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 314; // ��� ������������� �������
								}

								elseif ($POST_DATA["code"] === "")
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 315; // �� ������ ��� �������������
								}

								elseif ($POST_DATA["code"] != $_SESSION["code"])
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 316; // ������� ������ ��� �������������
								}
							}


							if ($this->status && CF::IsNaturalNumeric($this->comments_antiflood_time_limit))
							{
								$DB->SetTable($this->db_prefix . "comments");
								$DB->AddExp("COUNT(*)");
								$DB->AddCondFS("sid", "=", $Auth->sid);
								$DB->AddCondFX("time", ">=", "(NOW() - INTERVAL '$this->comments_antiflood_time_limit' SECOND)", false);
								$res = $DB->Select(1);
								list($num) = $DB->FetchRow($res);
								$DB->FreeRes($res);

								if ($num)
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 317; // �������� ��������� ��� �� ������
								}
							}


							if ($this->status)
							{
								$POST_DATA["author_from"] = trim(htmlspecialchars($POST_DATA["author_from"]));

								$DB->SetTable($this->db_prefix . "comments");
								$DB->AddValue("is_active", $this->use_comments_premoderation ? 0 : 1);
								$DB->AddValue("time", "NOW()", "X");
								$DB->AddValue("item_id", $this->current_item_id/*$POST_DATA["item_id"]*/);  // item_id ������ �� ��������� �� �����, � ����� ����������� ����� ��������� ������ � �������� ���������, ���� ID ��������� ��������� ������
								$DB->AddValue("sid", $Auth->sid);
								$DB->AddValue("ip", $Auth->ip);
								$DB->AddValue("author_name", $POST_DATA["author_name"]);
								$DB->AddValue("author_from", $POST_DATA["author_from"]);
								$DB->AddValue("author_email", $POST_DATA["author_email"]);
								$DB->AddValue("text", $POST_DATA["text"]);

								if ($DB->Insert())
								{
									$this->output["messages"]["good"][] = 106; // ����������� ������� ��������
									$Engine->LogAction($this->module_id, "comment", $DB->LastInsertID(), "create");
								}

								else
								{
									$this->output["messages"]["bad"][] = 406; // ������ �� ��� ���������� �����������
								}
							}


							if (!$this->status)
							{
								$this->comment_form_data = array(
									"author_name" => $POST_DATA["author_name"],
									"author_from" => $POST_DATA["author_from"],
									"author_email" => $POST_DATA["author_email"],
									"text" => $POST_DATA["text"],
									"code" => $this->comments_captcha_path,
									);
							}
						}
					}



					case "save_comment":
					{
						if ($Engine->ModuleOperationAllowed("cat.comments.handle", $this->current_cat_id) && isset($POST_DATA["id"], $POST_DATA["author_name"], $POST_DATA["author_from"], $POST_DATA["author_email"], $POST_DATA["text"]) && CF::IsNaturalNumeric($POST_DATA["id"]))
						{
							$this->status = true;

							if ($this->status && !CF::IsNonEmptyStr($POST_DATA["author_name"] = trim(htmlspecialchars($POST_DATA["author_name"]))))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 311; // �� ��������� ���� "�����"
							}

							if ($this->status && CF::IsNonEmptyStr($POST_DATA["author_email"] = trim(htmlspecialchars($POST_DATA["author_email"]))) && !CF::ValidateEmail($POST_DATA["author_email"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 312; // �������� ������ ���� e-mail
							}


							if ($this->status)
							{
								if ($this->comments_message_size_limit)
								{
									$POST_DATA["text"] = substr($POST_DATA["text"], 0, $this->comments_message_size_limit);
								}

								$POST_DATA["text"] = trim(htmlspecialchars($POST_DATA["text"]));

								if (!CF::IsNonEmptyStr($POST_DATA["text"]))
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 313; // �� ��������� ���� "����� �����������"
								}
							}


							if ($this->status)
							{
								$POST_DATA["author_from"] = trim(htmlspecialchars($POST_DATA["author_from"]));

								$DB->SetTable($this->db_prefix . "comments");

								if ($this->auto_activate_comments)
								{
									$DB->AddValue("is_active", 1);
								}

								$DB->AddValue("time", "NOW()", "X");
								$DB->AddValue("sid", $Auth->sid);
								$DB->AddValue("ip", $Auth->ip);
								$DB->AddValue("author_name", $POST_DATA["author_name"]);
								$DB->AddValue("author_from", $POST_DATA["author_from"]);
								$DB->AddValue("author_email", $POST_DATA["author_email"]);
								$DB->AddValue("text", $POST_DATA["text"]);
								$DB->AddCondFS("id", $POST_DATA["id"]);
								$DB->AddCondFS("item_id", $this->current_item_id);

								if ($DB->Update(1))
								{
									if ($DB->AffectedRows())
									// ��������� �� ����� ��� ������� ��������� ID ���������
									{
										$this->output["messages"]["good"][] = 107; // ����������� ������� �������
										$Engine->LogAction($this->module_id, "comment", $POST_DATA["id"], "alter");
									}
								}

								else
								{
									$this->output["messages"]["bad"][] = 407; // ������ �� ��� ���������� �����������
								}
							}


							if (!$this->status)
							{
								$this->comment_form_data = array(
									"id" => $POST_DATA["id"],
									"author_name" => $POST_DATA["author_name"],
									"author_from" => $POST_DATA["author_from"],
									"author_email" => $POST_DATA["author_email"],
									"text" => $POST_DATA["text"],
									);
							}
						}
					}
				}
			}
		}

		elseif (isset($_GET["node"], $_GET["action"]) && ($_GET["node"] == $this->node_id))
		{
			switch ($_GET["action"])
			{
				default:
					break;
			}
		}
	}

	function Output()
	{
		return $this->output;
	}
}

?>