<?php

class EApply
// Модуль подачи и рассмотрения заявлений
// version: 1.4
// date: 2010-06-28
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
	    
	    /*В params передаётся файл ini*/
	function EApply($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL)
	{
		global $Engine;
		// Инициализация модуля
		$this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->output = array();
		$this->output["messages"] = array("good" => array(), "bad" => array());
		$this->display_variant = NULL;
		$this->form_data = array();

/*		// Обработка файла параметров
		$config_file = $params; */
		
		if (isset($_POST["spamcheck"]) && !empty($_POST["spamcheck"]))
			CF::Redirect();

		$this->ProcessConfigFile($params);          // Обрабатываем файл конфигурации

		$maxlen = array();                                 // Cоздаём массив для максимальных длин полей.

		foreach ($this->config["form_elems"] as $key => $data)
																											 // Проходим по полям формы, описанным в конфигурации,...
		{
			if ($data["type"] != "f")                        // ...и если тип поля - не "файл",...
			{
				$this->form_data[$key] = "";                   // ...то изначально задаём пустое значение для поля формы,...
				$maxlen[$key] = isset($data["conds"]["maxlen"]) ? $data["conds"]["maxlen"]["value"] : false;
																											 // ...а также сохраняем максимальную длину для поля формы (или false, если она не указана).
			}
		}

		if ($this->config["antispam_settings"]["enable_hhf"])
																											 // При включенном алгоритме HHF...
		{
			$key = $this->config["antispam_settings"]["hhf_elem_name"];
			$this->form_data[$key] = "";										 // ...также дописываем изначально пустое значение в его поле...
			$maxlen[$key] = $this->config["antispam_settings"]["hhf_elem_maxlen"];
																											 // ...и его maxlen.
		}
		
		$this->output["mode"] = $this->config["general_settings"]["output_mode"];

		$this->output["maxlen"] = $maxlen;                 // Выдаём в output массив максимальных длин полей,...
		$this->output["having_files"] = $this->config["general_settings"]["having_files"];
																											 // ...то, есть ли в форме файлы,...
		$this->output["max_filesize"] = $this->config["general_settings"]["max_filesize"];
																											 // ...и какой максимальный размер файла.

		$this->ProcessHTTPdata();                          // Обрабатываем данные GET и POST



		$this->output["display_variant"] = $this->display_variant;
		$this->output["form_data"] = $this->form_data;
		$this->output["options"] = $this->options;
		
		switch ($this->config["general_settings"]["output_mode"]) {
			case "abit":
				if (!isset($_POST[$this->node_id]["send_message"]) || !empty($this->output["messages"]["bad"])) {
					$this->getdataforabit();
					$module_uri = explode("/", $module_uri);
					if (isset($module_uri[0]) && $module_uri[0] == "manage") {
						if (!isset($_POST["edit_apply_id"])) {
							$this->show_applies();
							$this->output["mode"] = "applies_manage";
						}
						else
							$this->edit_apply($this->config["general_settings"]["message_headers_newline_char"], $this->config["additional_headers"]);
					}
					elseif (isset($module_uri[0]) && isset($module_uri[1]) && $module_uri[0] == "delete") {
						$this->delete_apply($module_uri[1]);
					}
					elseif (isset($module_uri[0]) && isset($module_uri[1]) && $module_uri[0] == "show") {
						$this->show_apply($module_uri[1]);
						$this->output["mode"] = "one_apply";
					}
					elseif (isset($module_uri[0]) && $module_uri[0] == "list") {
						$this->show_applies();
						$this->output["mode"] = "applies_list";
					}
				}
				else {
					$this->save_apply($_POST[$this->node_id]["send_message"], $this->config["additional_headers"]);
				}
			break;
		}
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
										break; // !!! файлы пока не обрабатываем...
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
																													 // При включенном алгоритме HHF...
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
																													 // ...также дописываем полученное значение в его поле
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
									$this->display_variant = "send_fail"; // !!! подумать, как себя вести, если отключена настройка hhf_report_ok. пока что выдаётся ошибка отправки
								}
							}


							else
							{
								$message_body = file_get_contents(INCLUDES . $this->config["general_settings"]["message_body_template"]); // !!! сделать проверку существования и доступности файла! и подумать, что делать, если файл не найден/недоступен
								//die(print_r($this->config["form_elems"]));
								foreach ($this->config["form_elems"] as $key => $data)
								{
									if ($data["type"] != "f")
									{
										if ($POST_DATA[$key] == "on")
											$POST_DATA[$key] = "да";
											
										$message_body = str_replace("{" . $key . "}", $POST_DATA[$key], $message_body);
									}
								}

								// !!! ньюлайны неправильно берутся из конфига !!! разобраться
								$this->config["general_settings"]["message_body_newline_char"] = "\n";
								$this->config["general_settings"]["message_headers_newline_char"] = "\r\n";


								$message_body = str_replace("%ip%", $_SERVER["REMOTE_ADDR"], $message_body);
								$message_body = preg_replace("/%time:([^%]+)%/e", "date(\"\\1\")", $message_body);
								$message_body = preg_replace("/%stime:([^%]+)%/e", "gmdate(\"\\1\", " . (time() + TIMEZONE_OFFSET_SECONDS) . ")", $message_body);
								$message_body = implode($this->config["general_settings"]["message_body_newline_char"], CF::BreakByLbr($message_body));
								$message_body = wordwrap($message_body, 70, $this->config["general_settings"]["message_body_newline_char"]);

								if (mail(
									$this->config["general_settings"]["message_to"],
									$this->config["general_settings"]["message_subject"],
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
								$this->output["messages"]["bad"][] = 301; // не указана категория!)
							}

							if (CF::IsNonEmptyStr($POST_DATA["uripart"]) && (preg_match("/^\d+$/", $POST_DATA["uripart"]) || !preg_match("/^[a-z0-9_-]+$/i", $POST_DATA["uripart"])))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // недопустимый формат uripart (только латинские буквы, цифры, дефис и подчерк (только латинские буквы, цифры, дефис и подчерк; строка, состоящая целиком из&nbsp;цифр, недопустима!)
							}

							if (!CF::IsNonEmptyStr($POST_DATA["short_text"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 303; // не задан краткий текст
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

								if (1/*$this->maintain_cache*/) // !!! сделать опцией
								{
									$DB->AddValue("cached_text", substr(CF::RefineText($POST_DATA["full_text"]), 0, 65535)); // !!! сделать корректировку размера строки на уровне MySQLhandle
								}

								$DB->AddValue("time", $smart_time[0], $smart_time[1]);
								$DB->AddCondFS("id", "=", $POST_DATA["id"]);

								if (!$DB->Update(1))
								{
									$status = false;
									$this->output["messages"]["bad"][] = 402; // ошибка БД при обновлении материала
								}

								else
								{
									$this->output["messages"]["good"][] = 102; // материал успешно обновлен
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
										$this->output["messages"]["bad"][] = 450; // ошибка обновления информации об изображении
									}

									else
									{
										$this->output["messages"]["good"][] = 150; // информация об изображении обновлена
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
								$this->output["messages"]["bad"][] = 311; // не заполнено поле "Автор"
							}

							if ($this->status && CF::IsNonEmptyStr($POST_DATA["author_email"] = trim(htmlspecialchars($POST_DATA["author_email"]))) && !CF::ValidateEmail($POST_DATA["author_email"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 312; // неверный формат поля e-mail
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
									$this->output["messages"]["bad"][] = 313; // не заполнено поле "Текст комментария"
								}
							}


							if ($this->status && $this->comments_captcha_path)
							{
								if (!isset($_SESSION["code"]))
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 314; // код подтверждения устарел
								}

								elseif ($POST_DATA["code"] === "")
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 315; // не указан код подтверждения
								}

								elseif ($POST_DATA["code"] != $_SESSION["code"])
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 316; // неверно указан код подтверждения
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
									$this->output["messages"]["bad"][] = 317; // интервал антифлуда ещё не прошёл
								}
							}


							if ($this->status)
							{
								$POST_DATA["author_from"] = trim(htmlspecialchars($POST_DATA["author_from"]));

								$DB->SetTable($this->db_prefix . "comments");
								$DB->AddValue("is_active", $this->use_comments_premoderation ? 0 : 1);
								$DB->AddValue("time", "NOW()", "X");
								$DB->AddValue("item_id", $this->current_item_id/*$POST_DATA["item_id"]*/);  // item_id больше не передаётся из формы, в итоге комментарий можно добавлять только к текущему материалу, зато ID материала подделать нельзя
								$DB->AddValue("sid", $Auth->sid);
								$DB->AddValue("ip", $Auth->ip);
								$DB->AddValue("author_name", $POST_DATA["author_name"]);
								$DB->AddValue("author_from", $POST_DATA["author_from"]);
								$DB->AddValue("author_email", $POST_DATA["author_email"]);
								$DB->AddValue("text", $POST_DATA["text"]);

								if ($DB->Insert())
								{
									$this->output["messages"]["good"][] = 106; // комментарий успешно добавлен
									$Engine->LogAction($this->module_id, "comment", $DB->LastInsertID(), "create");
								}

								else
								{
									$this->output["messages"]["bad"][] = 406; // ошибка БД при добавлении комментария
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
								$this->output["messages"]["bad"][] = 311; // не заполнено поле "Автор"
							}

							if ($this->status && CF::IsNonEmptyStr($POST_DATA["author_email"] = trim(htmlspecialchars($POST_DATA["author_email"]))) && !CF::ValidateEmail($POST_DATA["author_email"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 312; // неверный формат поля e-mail
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
									$this->output["messages"]["bad"][] = 313; // не заполнено поле "Текст комментария"
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
									// сообщения не будет при попытке подделать ID материала
									{
										$this->output["messages"]["good"][] = 107; // комментарий успешно обновлён
										$Engine->LogAction($this->module_id, "comment", $POST_DATA["id"], "alter");
									}
								}

								else
								{
									$this->output["messages"]["bad"][] = 407; // ошибка БД при обновлении комментария
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

	
	function getdataforabit() {
		global $DB, $Auth, $Engine;
		
		$DB->SetTable("nsau_specialities");
		$DB->AddField("code");
		$DB->AddField("name");
		$DB->AddOrder("name");
		$res = $DB->Select();
		
		while ($row = $DB->FetchAssoc($res)) {
			$this->output["specialities"][] = $row;
		}
		
		if ($Engine->OperationAllowed($this->module_id, "applies.handle", -1, $Auth->usergroup_id)) {
			$this->output["allow_handle"] = 1;
		}
		else
			$this->output["allow_handle"] = 0;
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
	
	function show_applies() {
		global $DB, $Auth, $Engine;
		
		if (!$Engine->OperationAllowed($this->module_id, "applies.handle", -1, $Auth->usergroup_id)) {
			$Engine->HTTP403();
		}
		
		$DB->SetTable("nsau_applies");
		$res = $DB->Select();
			
		while ($row = $DB->FetchAssoc($res))
			$this->output["applies"][] = $row;
				
		$DB->SetTable("nsau_applies_statuses");
		$res = $DB->Select();
			
		while ($row = $DB->FetchAssoc($res))
			$this->output["statuses"][] = $row;
		
		if ($Engine->OperationAllowed($this->module_id, "applies.delete", -1, $Auth->usergroup_id)) {
			$this->output["allow_delete"] = 1;
		}
		else
			$this->output["allow_delete"] = 0;
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
		
		if ($Engine->OperationAllowed($this->module_id, "applies.delete", -1, $Auth->usergroup_id)) {
			$DB->SetTable("nsau_applies");
			$DB->AddCondFS("id", "=", $id);
			$DB->Delete();
			
			$Engine->LogAction($this->module_id, "item", $id, "delete");
			
			CF::Redirect($Engine->engine_uri."manage/");
		}
		else 
		{
			$this->output["messages"]["bad"][] = 401; // У вас нет права на удаление заявкок
		}
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