<?php

class EGallery
// version: 2.0 alpha 9
// date: 2008-15-12
{
	var $output;
	var $mode;
	var $db_prefix;
	var $node_id;
	var $module_id;
	var $module_uri;
	var $current_section_id;
	var $current_section_data;
	var $subsections;
	var $items;
	var $current_uri;

	var $files_dir;
	var $http_files_dir;
	var $icons_dir;
	var $http_icons_dir;
	var $naming_type;
	var $input_file_max_size;
	var $input_image_max_dim;
	var $allow_image_clip;
	var $allow_image_enlarge;
	var $allow_image_leave;
	var $allow_nonimages;
	var $input_image_formats;
	var $output_image_formats;
	var $nonimage_formats;
	var $nonimage_exts;
	var $output_files;
	var $main_output_file;

	var $display_variant;
	var $form_data;

	var $display_access;
	var $manage_access;
	var $full_access;

	var $status;

	/*	section;0;pos;pos;pos;8;1 */
	function EGallery($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		global $Engine, $DB, $Auth;


		$this->output = array();
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		$this->module_uri = $module_uri;
		$this->output["messages"] = array("good" => array(), "bad" => array());

		$this->output["display_access"] = $this->display_access = true; //$display_access;
		//$this->output["manage_access"] = $this->manage_access = $manage_access;
		//$this->output["full_access"] = $this->full_access = $full_access;
		
		//$this->output["full_access"] = $this->full_access = $this->output["manage_access"] = $this->manage_access = $Auth->logged_in;

		if (!$global_params)
		{
			die("EGallery module error #1001 at node $this->node_id: config file not specified.");
		}

		elseif (!$array = @parse_ini_file(INCLUDES . $global_params, true))
		{
			die("EGallery module error #1002 at node $this->node_id: cannot process config file '$global_params'.");
		}

		else
		{
			$this->db_prefix = $array["general_settings"]["db_prefix"];
			$this->files_dir = ($array["general_settings"]["use_images_dir"] ? COMMON_IMAGES : FILES) . $array["general_settings"]["files_dir"];
			$this->http_files_dir = ($array["general_settings"]["use_images_dir"] ? HTTP_COMMON_IMAGES : HTTP_FILES) . $array["general_settings"]["files_dir"];
			$this->icons_dir = ($array["general_settings"]["use_images_dir"] ? COMMON_IMAGES : FILES) . $array["general_settings"]["icons_dir"];
			$this->http_icons_dir = ($array["general_settings"]["use_images_dir"] ? HTTP_COMMON_IMAGES : HTTP_FILES) . $array["general_settings"]["icons_dir"];
			$this->naming_type = $array["general_settings"]["naming_type"];
			$this->output["input_file_max_size"] = $this->input_file_max_size = $array["general_settings"]["input_file_max_size"];
			$this->output["input_image_max_dim"] = $this->input_image_max_dim = $array["general_settings"]["input_image_max_dim"];
			$this->allow_image_clip = (bool) $array["general_settings"]["allow_image_clip"];
			$this->allow_image_enlarge = (bool) $array["general_settings"]["allow_image_enlarge"];
			$this->allow_image_leave = (bool) $array["general_settings"]["allow_image_leave"];
			$this->allow_nonimages = (bool) $array["general_settings"]["allow_nonimages"];


			$this->input_image_formats = array();

			foreach ($array["input_image_formats"] as $key => $data)
			{
				$parts = explode(",", $data);
				$this->input_image_formats[$key] = array(
					"name" => trim($parts[0]),
					"read_function" => trim($parts[1]),
					"output_format" => (int) trim($parts[2]),
					);
			}


			$this->output_image_formats = array();

			foreach ($array["output_image_formats"] as $key => $data)
			{
				$parts = explode(",", $data);
				$this->output_image_formats[$key] = array(
					"output_function" => trim($parts[0]),
					"ext" => trim($parts[1]),
					"use_interlacing" => isset($parts[2]) ? (bool) trim($parts[2]) : false,
					"quality" => isset($parts[3]) ? (int) trim($parts[3]) : false,
					);
			}


			$this->nonimage_formats = array();
			$this->nonimage_exts = array();

			if ($this->allow_nonimages)
			{
				foreach ($array["nonimage_formats"] as $key => $data)
				{
					$parts = explode(",", $data);
					$parts2 = explode("|", $parts[0]);

					foreach ($parts2 as $elem)
					{
						$this->nonimage_exts[$elem] = $key;
					}

					$parts2 = explode("|", $parts[2]);

					$icons = array();

					foreach ($parts2 as $elem)
					{
						$parts3 = explode(":", $elem);
						$icons[(int) $parts3[0]] = $this->http_icons_dir . $parts3[1];
					}

					$this->nonimage_formats[$key] = array(
						"name" => trim($parts[1]),
						"icons" => $icons,
						);
				}
			}


			$this->output_files = array();

			foreach ($array["output_files"] as $key => $data)
			{
				$parts = explode(",", $data);

				$this->output_files[$key] = array(
					"width" => (int) trim($parts[0]),
					"height" => (int) trim($parts[1]),
					"name_suffix" => isset($parts[2]) ? trim($parts[2]) : false,
					"allow_upload" => isset($parts[3]) ? (bool) trim($parts[3]) : false,
					);
			}

			$this->main_output_file = $array["general_settings"]["main_output_file"];
		}


		$parts = explode(";", $params);
		$this->output["mode"] = $this->mode = $parts[0];

		switch ($this->mode) {
			case "idpo_announce": {
				$this->output["scripts_mode"] = "idpo_announce";
			}
			case "announce": {
				if (!$this->module_uri) {
					$section_id = isset($parts[1]) ? $parts[1] : 0;
					$items_sort_by = isset($parts[2]) ? $parts[2] : "update_time";
					$items_num = isset($parts[3]) ? $parts[3] : false;
					$include_subsections = (isset($parts[4]) && $parts[4]);
					$group_by_subsections = (isset($parts[5]) && $parts[5]);
					
					$DB->SetTable($this->db_prefix . "sections");
					$DB->AddFields(array("title", "descr", "text"));
					$DB->AddCondFP("is_active");
					$DB->AddCondFS("id", "=", $section_id);
					$res = $DB->Select(1);
	
					if ($row = $DB->FetchObject($res)) {
						$this->output["section_data"] = array(
							"title" => $row->title,
							"descr" => $row->descr,
							"text" => $row->text,
							"items" => $this->ListItems($section_id, $items_sort_by, $items_num, $include_subsections, $group_by_subsections),
						);
					}
					$DB->FreeRes($res);
				}
			}
			break;

			case "section": {
				$this->output["scripts_mode"] = "gallery_multiupload";
				if ($this->module_uri || !isset($parts[7])) {
					$init_section_id = isset($parts[1]) ? (int)$parts[1] : 0;
					$subsections_sort_by = isset($parts[2]) ? $parts[2] : "update_time";
					$items_sort_by = isset($parts[3]) ? $parts[3] : "update_time";
					$subsections_items_sort_by = isset($parts[4]) ? $parts[4] : "update_time";
					$subsections_items_num = isset($parts[5]) ? $parts[5] : false;
					$include_subsections_subitems = (isset($parts[6]) && $parts[6]);
					
					
					$Engine->OperationAllowed($this->module_id, "gallery.handle", $this->current_cat_id, $Auth->usergroup_id);
					$this->output["full_access"] = $this->full_access = $this->output["manage_access"] = $this->manage_access = $Engine->OperationAllowed($this->module_id, "gallery.handle", $init_section_id, $Auth->usergroup_id);
					
					$this->ParseURI($init_section_id);
	
					if ($this->manage_access || 1)
					{
						$this->ProcessHTTPdata();
	
						$this->output["display_variant"] = $this->display_variant;
						$this->output["form_data"] = $this->form_data;
						$this->output["status"] = $this->status;
					}
	
					$parts = explode("?", $this->current_uri);
	
					$this->output["section_data"] = array("id" => $this->current_section_id);
					$this->output["subsections"] = $this->subsections = $this->ListContent($this->current_section_id, $parts[0], $subsections_sort_by, $subsections_items_num, $subsections_items_sort_by, $include_subsections_subitems);
					$this->output["items"] = $this->items = $this->ListItems($this->current_section_id, $items_sort_by);
				}
			}
			break;

			case "search": {
				if ($additional && isset($parts[1]) && CF::IsNaturalNumeric($parts[1])) {
					$this->output["results"] = $this->Search($parts[1], $additional);
				} elseif ($additional) {
					die(); //!!!
				} else {
					die(); //!!!
				}
			}
			break;

			default: {
				die("EGallery module error #1000 at node $this->node_id: unknown mode: $this->mode.");
			}
			break;
		}
	}

	function ParseURI($init_section_id = 0)
	{
		global $DB, $Engine;
		$parts = array_filter(explode("/", $this->module_uri));
		$this->current_uri = $Engine->engine_uri;
		$this->current_section_id = $init_section_id;


		$DB->SetTable($this->db_prefix . "sections");
		$DB->AddFields(array("title", "descr", "text"));
		$DB->AddCondFX("id", "=", $this->current_section_id);

		if (!$this->manage_access)
		{
			$DB->AddCondFP("is_active");
		}

		$res = $DB->Select(1);

		if ($row = $DB->FetchObject($res))
		{
			$DB->FreeRes($res);
			$this->current_section_data = array(
				"uri" => $this->current_uri,
				"title" => $row->title,
				"descr" => $row->descr,
				"text" => $row->text,
				);
		}

		else
		{
			$DB->FreeRes($res); // !!! скорее всего, должна выдаваться ошибка, что раздел не найден (404?)
			$this->current_section_data = array(
				"uri" => $this->current_uri,
				"title" => "",
				"descr" => "",
				"text" => "",
				);
		}

//		if (!$at_root)
//		{
			foreach ($parts as $key => $uri_part)
			{
				if (CF::IsNonEmptyStr($uri_part))
				{
					$DB->SetTable($this->db_prefix . "sections");
					$DB->AddFields(array("id", "uri_part", "title", "descr", "text"));
					$DB->AddCondFX("pid", "=", $this->current_section_id);
					$DB->AddCondFS("uri_part", "LIKE", $uri_part);

					if (!$this->manage_access)
					{
						$DB->AddCondFP("is_active");
					}

					$res = $DB->Select(1);

					if ($row = $DB->FetchObject($res))
					{
						$DB->FreeRes($res);
						$this->current_section_id = $row->id;
						$this->current_uri .= "$row->uri_part/";
						$this->current_section_data = array(
							"uri" => $this->current_uri,
							"title" => $row->title,
							"descr" => $row->descr,
							"text" => $row->text,
							);
						$Engine->AddFootstep($this->current_uri, $row->title, "", true, false, $this->module_id);
					}

					else
					{
						$DB->FreeRes($res);
						break;
					}
				}
			}
//		}



		if ($_GET)
		{
			$this->current_uri .= ("?" . http_build_query($_GET));
		}

		if ($_SERVER["REQUEST_URI"] != $this->current_uri)
		{
//			CF::Redirect($this->current_uri);
//echo $this->current_uri;
			$Engine->HTTP404();
		}

		$this->output["current_section_id"] = $this->current_section_id;
		$this->output["current_section_data"] = $this->current_section_data;
	}



	function ResizeImage($input_file, $output_file, $format, $input_width, $input_height, $output_width, $output_height, $use_interlace, $quality)
	{
		if ($this->allow_image_clip)
		{
			$do_resize = (/*$this->allow_image_enlarge || */(($width_ratio = $input_width / $output_width) != ($height_ratio = $input_height / $output_height)));
			//echo $input_width, " : ", $output_width, "<br />",  $input_height, " : ", $output_height, "<br /><br />";
		}

		else
		{
			$do_resize = (/*$this->allow_image_enlarge || */($input_width > $output_width) || ($input_height > $output_height));
		}


		if (!$do_resize && $this->allow_image_leave)
		{
			return copy($input_file, $output_file);
		}

		else
		{
			$input_image = $this->input_image_formats[$format]["read_function"]($input_file);


			if (!$this->allow_image_clip)
			{
				//if (/*$this->allow_image_enlarge || */($input_width > $output_width) || ($input_height > $output_height))
				$resized_width = $input_width;
				$resized_height = $input_height;

				$ratio = max($input_width / $output_width, $input_height / $output_height);
				$output_width = $input_width / $ratio;
				$output_height = $input_height / $ratio;

				$input_x = $input_y = 0;
			}

			elseif ($width_ratio < $height_ratio)
			{
				$resized_width = $input_width;
				$resized_height = round($output_height * $width_ratio);
				$input_x = 0;
				$input_y = round(($input_height - $resized_height) / 2);
			}

			else
			{
				$resized_width = round($output_width * $height_ratio);
				$resized_height = $input_height;
				$input_x = round(($input_width - $resized_width) / 2);
				$input_y = 0;
			}


			if (!$function = CF::Seek4Function("imagecreatetruecolor", "imagecreate"))
			{
				return false;
			}

			$output_image = $function($output_width, $output_height);
			imageinterlace($output_image, $use_interlace);


			if (!$function = CF::Seek4Function("imagecopyresampled", "imagecopyresized"))
			{
				return false;
			}

			$function($output_image, $input_image, 0, 0, $input_x, $input_y, $output_width, $output_height, $resized_width, $resized_height);

			return is_int($quality) ? $this->output_image_formats[$format]["output_function"]($output_image, $output_file, $quality) : $this->output_image_formats[$format]["output_function"]($output_image, $output_file);
		}
	}



	function ProcessHTTPdata()
	{
		global $DB, $Auth;

		if (isset($_POST[$this->node_id]["cancel"]))
		{
			1;
		}

		elseif (isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id]))
		{
			foreach (array("add_section", "save_section", "add_item", "save_item") as $POST_ACTION)
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
					case "add_section":
						if (isset($POST_DATA["pid"], $POST_DATA["uri_part"], $POST_DATA["title"], $POST_DATA["descr"], $POST_DATA["text"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["pid"]))
						{
							$this->status = true;

							if (!isset($POST_DATA["pos"]) || !CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
							{
								$POST_DATA["pos"] = false;
							}

							if (!CF::IsNonEmptyStr($POST_DATA["uri_part"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 300; // пустое uri_part
							}

							elseif (!preg_match("/^[a-z0-9_-]+$/i", $POST_DATA["uri_part"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 301; // недопустимый формат uri_part (только латинские буквы, цифры, дефис и подчерк)
							}


							if (!CF::IsNonEmptyStr($POST_DATA["title"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // пустой заголовок раздела
							}


							if ($this->status)
							{
								$DB->SetTable($this->db_prefix . "sections");
								$DB->AddValues(array(
									"pid" => $POST_DATA["pid"],
									"uri_part" => $POST_DATA["uri_part"],
									"title" => $POST_DATA["title"],
									"descr" => $POST_DATA["descr"],
									"text" => $POST_DATA["text"],
									"add_user_id" => $Auth->user_id,
									));
								
								if (1/*$this->maintain_cache*/) // !!! сделать опцией
								{
									$DB->AddValue("cache", substr(CF::RefineText($POST_DATA["text"]), 0, 65535)); // !!! сделать корректировку размера строки на уровне MySQLhandle
								}								
								
								if (CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
								{
									$DB->AddValue("pos", $POST_DATA["pos"]);
								}
								
								$DB->AddValue("add_time", "NOW()", "X");
								$DB->AddValue("update_time", "NOW()", "X");

								if (!($DB->Insert()))
								{
									$status = false;
									$this->output["messages"]["bad"][] = 303; // ошибка БД при добавлении раздела
								}

								else
								{
									$this->output["messages"]["good"][] = 101; // раздел успешно добавлен
								}
							}


							if (!$this->status)
							{
								$this->display_variant = "add_section";
								$this->form_data = array(
									"pid" => $POST_DATA["pid"],
									"pos" => $POST_DATA["pos"],
									"uri_part" => $POST_DATA["uri_part"],
									"title" => htmlspecialchars($POST_DATA["title"]),
									"descr" => htmlspecialchars($POST_DATA["descr"]),
									"text" => htmlspecialchars($POST_DATA["text"])
									);
							}
						}
						break;


					case "save_section":
						if (isset($POST_DATA["id"], $POST_DATA["pid"], $POST_DATA["uri_part"], $POST_DATA["title"], $POST_DATA["descr"], $POST_DATA["text"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["id"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["pid"]))
						{
							$this->status = true;

							if (!isset($POST_DATA["pos"]) || !CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
							{
								$POST_DATA["pos"] = false;
							}

							if (!CF::IsNonEmptyStr($POST_DATA["uri_part"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 300; // пустое uri_part
							}

							elseif (!preg_match("/^[a-z0-9_-]+$/i", $POST_DATA["uri_part"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 301; // недопустимый формат uri_part (только латинские буквы, цифры, дефис и подчерк)
							}


							if (!CF::IsNonEmptyStr($POST_DATA["title"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // пустой заголовок раздела
							}


							if ($this->status)
							{
								$DB->SetTable($this->db_prefix . "sections");
								$DB->AddValues(array(
									"pid" => $POST_DATA["pid"],
									"uri_part" => $POST_DATA["uri_part"],
									"title" => $POST_DATA["title"],
									"descr" => $POST_DATA["descr"],
									"text" => $POST_DATA["text"]
									));
								
								if (1/*$this->maintain_cache*/) // !!! сделать опцией
								{
									$DB->AddValue("cache", substr(CF::RefineText($POST_DATA["text"]), 0, 65535)); // !!! сделать корректировку размера строки на уровне MySQLhandle
								}												
								
								if (CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
								{
									$DB->AddValue("pos", $POST_DATA["pos"]);
								}
								
								$DB->AddValue("update_time", "NOW()", "X");
								$DB->AddCondFS("id", "=", $POST_DATA["id"]);

								if (!($DB->Update(1)))
								{
									$status = false;
									$this->output["messages"]["bad"][] = 320; // ошибка БД при сохранении свойств раздела
								}

								else
								{
									$this->output["messages"]["good"][] = 105; // свойства раздела успешно сохранены
								}
							}


							if (!$this->status)
							{
								$this->display_variant = "edit_section";
								$this->form_data = array(
									"id" =>  $POST_DATA["id"],
									"pid" => $POST_DATA["pid"],
									"pos" => $POST_DATA["pos"],
									"uri_part" => $POST_DATA["uri_part"],
									"title" => htmlspecialchars($POST_DATA["title"]),
									"descr" => htmlspecialchars($POST_DATA["descr"]),
									"text" => htmlspecialchars($POST_DATA["text"])
									);
							}
						}
						break;


					case "add_item":
						if (isset($_FILES['Filedata'])) {
							$multiupload_mode = true;
							$FILE_DATA = $_FILES['Filedata'];
						} else 	$FILE_DATA = $_FILES[$this->node_id . "_file"];
					
						if (isset($POST_DATA["section_id"], $POST_DATA["descr"], $POST_DATA["text"], $FILE_DATA) && CF::IsNaturalOrZeroNumeric($POST_DATA["section_id"]) && CF::IsNonEmptyStr($FILE_DATA["tmp_name"]))
						
						//if (isset($POST_DATA["section_id"], $POST_DATA["descr"], $POST_DATA["text"], $_FILES[$this->node_id . "_file"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["section_id"]) && CF::IsNonEmptyStr($_FILES[$this->node_id . "_file"]["tmp_name"]))
						{
							//$FILE_DATA = $_FILES[$this->node_id . "_file"];
							$this->status = true;

							if (!isset($POST_DATA["pos"]) || !CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
							{	
								$POST_DATA["pos"] = false;
							}

							if (!($FILE_DATA["size"]))
							{	
								$this->status = false;
								$this->output["messages"]["bad"][] = 304; // не выбран файл для добавления
							}

							elseif ($FILE_DATA["size"] > $this->input_file_max_size)
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 305; // размер файла превышает максимально допустимый
								@unlink($FILE_DATA["tmp_name"]);
							}

							elseif (!file_exists($FILE_DATA["tmp_name"]))
							{	
								$this->status = false;
								$this->output["messages"]["bad"][] = 306; // не удалось найти закачанный файл
							}

							elseif (!is_uploaded_file($FILE_DATA["tmp_name"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 307; // непредвиденная ошибка (файл не является закачанным)
							}

							else
							{	
								$FILENAME_PARTS = explode(".", $FILE_DATA["name"]);

								$result = @getimagesize($FILE_DATA["tmp_name"]);

								if ($result && isset($this->input_image_formats[$result[2]]))
								{
									$INPUT_IS_IMAGE = true;
									list($INPUT_WIDTH, $INPUT_HEIGHT, $INPUT_FORMAT) = $result;

									if (!$INPUT_WIDTH || !$INPUT_HEIGHT)
									{
										$this->status = false;
										$this->output["messages"]["bad"][] = 308; // не удаётся определить высоту и/или ширину
									}

									elseif (($INPUT_WIDTH > $this->input_image_max_dim) || ($INPUT_HEIGHT > $this->input_image_max_dim))
									{
										$this->status = false;
										$this->output["messages"]["bad"][] = 309; // высота и/или ширина превышают допустимые
									}

									else
									{
										$EXT = $this->output_image_formats[$INPUT_FORMAT]["ext"];
									}
								}

								else
								{
									$INPUT_IS_IMAGE = false;
									if (count($FILENAME_PARTS) < 2)
									{
										$this->status = false;
										$this->output["messages"]["bad"][] = 310; // файл не-изображения без расширения
									}

									elseif (!isset($this->nonimage_exts[$EXT = strtolower(array_pop($FILENAME_PARTS))]) && !isset($this->nonimage_exts["*"]))
									{
										$this->status = false;
										$this->output["messages"]["bad"][] = 311; // неизвестное расширение файла не-изображения
									}
								}
							}


							if ($this->status)
							{
								do
								{
									$FILENAME = substr(md5(uniqid($_SERVER["SERVER_NAME"])), 0, 16);
								}

								while (file_exists($this->files_dir . $FILENAME . "." . $EXT));


								$MAIN_OUTPUT_FILENAME = $this->files_dir . $FILENAME . $this->output_files[$this->main_output_file]["name_suffix"] . "." . $EXT;


								if ($INPUT_IS_IMAGE)
								{
									foreach ($this->output_files as $key => $data)
									{
										if (!$this->ResizeImage(
											$FILE_DATA["tmp_name"],
											$this->files_dir . $FILENAME . $data["name_suffix"] . "." . $EXT,
											$INPUT_FORMAT,
											$INPUT_WIDTH,
											$INPUT_HEIGHT,
											$data["width"] ? $data["width"] : $INPUT_WIDTH,
											$data["height"] ? $data["height"] : $INPUT_HEIGHT,
											$this->output_image_formats[$INPUT_FORMAT]["use_interlacing"],
											$this->output_image_formats[$INPUT_FORMAT]["quality"]
											))
										{
											$this->status = false;
											$this->output["messages"]["bad"][] = 312; // ошибка создания файла изображения
											break;
										}
									}

									if (!$this->status) // если были проблемы, удаляем созданные файлы
									{
										foreach ($this->output_files as $data)
										{
											@unlink($this->files_dir . $FILENAME . $data["name_suffix"] . "." . $EXT);
										}
									}
								}

								else
								{
									if (!copy($FILE_DATA["tmp_name"], $MAIN_OUTPUT_FILENAME))
									{
										$this->status = false;
										$this->output["messages"]["bad"][] = 313; // ошибка копирования файла не-изображения
									}
								}



								if ($this->status)
								{
									if ($INPUT_IS_IMAGE)
									{
										@list($MAIN_OUTPUT_WIDTH, $MAIN_OUTPUT_HEIGHT) = @getimagesize($MAIN_OUTPUT_FILENAME);
									}

									$DB->SetTable($this->db_prefix . "items");
									$DB->AddValues(array(
										"section_id" => $POST_DATA["section_id"],
										"name" => $FILENAME,
										"ext" => $EXT,
										"descr" => $POST_DATA["descr"],
										"text" => $POST_DATA["text"],
										"is_image" => $INPUT_IS_IMAGE ? 1 : 0,
										"filesize" => @filesize($MAIN_OUTPUT_FILENAME),
										"width" => $INPUT_IS_IMAGE ? $MAIN_OUTPUT_WIDTH : 0,
										"height" => $INPUT_IS_IMAGE ? $MAIN_OUTPUT_HEIGHT : 0,
										"add_user_id" => $Auth->user_id,
										));
									
									if (CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
									{
										$DB->AddValue("pos", $POST_DATA["pos"]);
									}									
									
									$DB->AddValue("add_time", "NOW()", "X");
									$DB->AddValue("update_time", "NOW()", "X");

									if (!($DB->Insert()))
									{
										$status = false;
										$this->output["messages"]["bad"][] = 314; // ошибка БД при добавлении файла
									}

									else
									{
										$this->output["messages"]["good"][] = 102; // файл успешно добавлен
										$DB->Exec("
											UPDATE `" . $this->db_prefix . "sections`
											SET `update_time` = NOW(), `items_num` = `items_num` + 1
											WHERE `id` = '" . $POST_DATA["section_id"] . "'
											LIMIT 1");
									}
								}
							}

							if (!$this->status)
							{
								$this->display_variant = "add_item";
								$this->form_data = array(
									"section_id" => $POST_DATA["section_id"],
									"pos" => $POST_DATA["pos"],
									"descr" => htmlspecialchars($POST_DATA["descr"]),
									"text" => htmlspecialchars($POST_DATA["text"])
									);
							}

							@unlink($FILE_DATA["tmp_name"]);
							
							if (isset($multiupload_mode) && $multiupload_mode) {
								exit(isset($error_msg) ? 1 : $error_msg);
							}
						}
						break;



					case "save_item":
						if (isset($POST_DATA["id"], $POST_DATA["section_id"], $POST_DATA["descr"], $POST_DATA["text"]) && CF::IsNaturalNumeric($POST_DATA["id"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["section_id"]))
						{
							$this->status = true;

							if (!isset($POST_DATA["pos"]) || !CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
							{
								$POST_DATA["pos"] = false;
							}

							$DB->SetTable($this->db_prefix . "items");
							$DB->AddValues(array(
								"section_id" => $POST_DATA["section_id"],
								"descr" => $POST_DATA["descr"],
								"text" => $POST_DATA["text"],
								));
							
							if (CF::IsNaturalOrZeroNumeric($POST_DATA["pos"]))
							{
								$DB->AddValue("pos", $POST_DATA["pos"]);
							}
								
							$DB->AddValue("update_time", "NOW()", "X");
							$DB->AddCondFS("id", "=", $POST_DATA["id"]);

							if (!($DB->Update(1)))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 318; // ошибка БД при сохранении свойств файла

								$this->display_variant = "edit_item";
								$this->form_data = array(
									"id" => $POST_DATA["id"],
									"section_id" => $POST_DATA["section_id"],
									"pos" => $POST_DATA["pos"],
									"descr" => htmlspecialchars($POST_DATA["descr"]),
									"text" => htmlspecialchars($POST_DATA["text"]),
									);
							}

							else
							{
								$DB->Exec("
									UPDATE `" . $this->db_prefix . "sections`
									SET `update_time` = NOW()
									WHERE `id` = '" . $POST_DATA["section_id"] . "'
									LIMIT 1");

								$this->output["messages"]["good"][] = 104; // свойства файла	успешно сохранены
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
				case "add_section":
					$this->display_variant = "add_section";
					$this->form_data = array(
						"pid" => $this->current_section_id,
						"pos" => "",
						"uri_part" => "",
						"title" => "",
						"descr" => "",
						"text" => ""
						);
					break;


				case "add_item":
					$this->display_variant = "add_item";
					$this->form_data = array(
						"section_id" => $this->current_section_id,
						"pos" => "",
						"descr" => "",
						"text" => ""
						);
					break;


				case "delete_item":
					$this->display_variant = "delete_item";
					$this->form_data = array();

					if (isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$res = $DB->Exec("
							SELECT `section_id`, `name`, `ext`
							FROM `" . $this->db_prefix . "items`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1");

						if ($row = $DB->FetchObject($res))
						{
							$DB->FreeRes($res);

							foreach ($this->output_files as $key => $data)
							{
								@unlink($this->files_dir . $row->name . $data["name_suffix"] . "." . $row->ext);
							}

							if (!($DB->Exec("
								DELETE FROM `" . $this->db_prefix . "items`
								WHERE `id` = '" . $_GET["id"] . "'
								LIMIT 1")))
							{
								$this->output["messages"]["bad"][] = 315; // ошибка БД при удалении файла
							}

							else
							{
								$this->output["messages"]["good"][] = 103; // файл успешно удалён
								$DB->Exec("
									UPDATE `" . $this->db_prefix . "sections`
									SET `items_num` = `items_num` - 1
									WHERE `id` = '" . $row->section_id . "'
									LIMIT 1");
							}
						}

						else
						{
							$DB->FreeRes($res);
							$this->output["messages"]["bad"][] = 316; // удаляемый файл не найден в БД
						}
					}
					break;


				case "edit_item":
					$this->display_variant = "edit_item";
					$this->form_data = array(
						"id" => "",
						"section_id" => "",
						"pos" => "",
						"descr" => "",
						"text" => "",
						);

					if (isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$res = $DB->Exec("
							SELECT `section_id`, `pos`, `descr`, `text`
							FROM `" . $this->db_prefix . "items`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1");

						if ($row = $DB->FetchObject($res))
						{
							$DB->FreeRes($res);
							$this->status = true;
							$this->form_data = array(
								"id" => $_GET["id"],
								"section_id" => $row->section_id,
								"pos" => $row->pos,
								"descr" => $row->descr,
								"text" => $row->text,
								);
						}

						else
						{
							$DB->FreeRes($res);
							$this->status = false;
							$this->output["messages"]["bad"][] = 317; // редактируемый файл не найден в БД
						}
					}
					break;


				case "edit_section":
					$this->display_variant = "edit_section";
					$this->form_data = array(
						"id" => "",
						"pos" => "",
						"pid" => "",
						"uri_part" => "",
						"title" => "",
						"descr" => "",
						"text" => "",
						);

					if (isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$res = $DB->Exec("
							SELECT `pid`, `pos`, `uri_part`, `title`, `descr`, `text`
							FROM `" . $this->db_prefix . "sections`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1");

						if ($row = $DB->FetchObject($res))
						{
							$DB->FreeRes($res);
							$this->status = true;
							$this->form_data = array(
								"id" => $_GET["id"],
								"pos" => $row->pos,
								"pid" => $row->pid,
								"uri_part" => $row->uri_part,
								"title" => htmlspecialchars($row->title),
								"descr" => htmlspecialchars($row->descr),
								"text" => htmlspecialchars($row->text),
								);
						}

						else
						{
							$DB->FreeRes($res);
							$this->status = false;
							$this->output["messages"]["bad"][] = 319; // редактируемый раздел не найден в БД
						}
					}
					break;


				case "hide_section":
				case "unhide_section":
					$this->display_variant = $_GET["action"];
					$this->form_data = array();

					if (isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$value = ($_GET["action"] == "unhide_section") ? 1 : 0;

						if (!($DB->Exec("
							UPDATE `" . $this->db_prefix . "sections`
							SET `is_active` = '$value'
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1")))
						{
							$this->status = false;
							$this->output["messages"]["bad"][] = $value ? 322 : 321; // ошибка БД при скрытии/открытии раздела
						}

						else
						{
							$this->status = true;
							$this->output["messages"]["good"][] = $value ? 107 : 106; // раздел успешно скрыт/открыт
						}
					}
					break;
			}
		}
	}



	function ListContent($section_id, $uri_prefix, $subsections_sort_by, $items_num, $items_sort_by, $include_subsections_subitems)
	{
		global $DB;
		$output = array();

		$DB->SetTable($this->db_prefix . "sections");
		$DB->AddFields(array("id", "is_active", "uri_part", "title", "descr", "text", "items_num", "add_time", "add_user_id", "update_time"));
		$DB->AddCondFS("pid", "=", $section_id);

		if (!$this->manage_access)
		{
			$DB->AddCondFP("is_active");
		}

		switch ($subsections_sort_by)
		{
			case "add_time":
			case "update_time":
				$DB->AddOrder($subsections_sort_by, true);
				break;

			case "pos":
				$DB->AddOrder($subsections_sort_by);
				break;

			case "random":
				$DB->AddExpOrder("RAND()");
				break;
		}

		$res = $DB->Select();

		while($row = $DB->FetchObject($res))
		{
			$output[$row->id] = array(
				"is_active" => $row->is_active,
				"link" => $uri_prefix . $row->uri_part . "/",
				"title" => $row->title,
				"descr" => $row->descr,
				"text" => $row->text,
				"items_num" => $row->items_num,
				"items" => $items_num ? $this->ListItems($row->id, $items_sort_by, $items_num, $include_subsections_subitems) : array(),
				"add_time" => $row->add_time,
				"add_user_id" => $row->add_user_id,
				"update_time" => $row->update_time,
				);
		}

		$DB->FreeRes($res);
		return $output;
	}


//	function ListSubsections($section_id)
//	{
//		global $DB;
//		$output = array();

//		$DB->SetTable($this->db_prefix . "sections");
//		$DB->AddFields(array("id"));
//		$DB->AddCondFS("pid", "=", $section_id);
//		$DB->AddCondFP("is_active");
//		$res = $DB->Select();

//		while (list($id) = $DB->FetchRow($res))
//		{
//			$output[] = $row->id;
//			$output = array_merge($output, $this->ListSubsections($id));
//		}

//		$DB->FreeRes($res);
//		return $output;
//	}



	function ListItems($section_id, $sort_by, $limit = NULL, $include_subsections = false, $group_by_subsections = false)
	{
		global $DB;
		$output = array();

		$DB->SetTable($this->db_prefix . "items");
		$DB->AddFields(array("id", "pos", "name", "ext", "descr", "text", "is_image", "filesize", "width", "height", "add_time", "add_user_id", "update_time", "viewed", "rating"));

		

		$DB->AddAltFS("section_id", "=", $section_id);

		if ($include_subsections)
		{
			foreach ($this->ListSections($section_id, true) as $elem)
			{
				$DB->AddAltFS("section_id", "=", $elem);
			}
		}

		$DB->AppendAlts();

		if ($group_by_subsections)
		{
			$DB->AddJoin($this->db_prefix."sections.id", "section_id");
			$DB->AddField($this->db_prefix."sections.title", "section_name");
			$DB->AddField($this->db_prefix."sections.uri_part", "section_uri");
			$DB->AddExp("COUNT(section_id) AS icount");
			$DB->AddGrouping("section_id");
		}
		
		switch ($sort_by)
		{
			case "add_time":
			case "update_time":
				$DB->AddOrder($sort_by, true);
				break;

			case "pos":
				$DB->AddOrder($sort_by);
				break;

			case "random":
				$DB->AddExpOrder("RAND()");
				break;
		}

		$res = $DB->Select($limit);

		while ($row = $DB->FetchObject($res))
		{
			$output_files = array();

			foreach ($this->output_files as $key => $data)
			{
				$output_files[$key] = ($row->is_image || ($key == $this->main_output_file)) ? ($this->http_files_dir . $row->name . $data["name_suffix"] . "." . $row->ext) : ($this->nonimage_formats[$this->nonimage_exts[$row->ext]]["icons"][$key]);
			}

//			$output[$row->id] = array(
			$output[] = array(
				"id" => $row->id,
				"icount" => (isset($row->icount)?$row->icount:''), 
				"section_name" => (isset($row->section_name)?$row->section_name:''),
				"section_uri" => (isset($row->section_uri)?$row->section_uri:''),
				"output_files" => $output_files,
				"descr" => $row->descr,
				"text" => $row->text,
				"is_image" => (bool) $row->is_image,
				"filesize" => $row->filesize,
				"width" => $row->width,
				"height" => $row->height,
				"add_time" => $row->add_time,
				"add_user_id" => $row->add_user_id,
				"update_time" => $row->update_time,
				"viewed" => $row->viewed,
				"rating" => $row->rating
				);
		}

		$DB->FreeRes($res);
		return $output;
	}



	function ListSections($pid = 0, $flat = false)
	{
		global $DB;
		$output = array();

		$res = $DB->Exec("
			SELECT `id`, `uri_part`, `title`, `descr`, `text`
			FROM `" . $this->db_prefix . "sections`
			WHERE `pid` = '$pid' AND `is_active`");

		while ($row = $DB->FetchObject($res))
		{
			if ($flat)
			{
				$output[] = $row->id;
				$output = array_merge(
					$output,
					$this->ListSections($row->id, $flat)
					);
			}

			else
			{
				$output[$row->id] = array(
					"uri_part" => $row->uri_part, // возможно, надо выдавать полный путь
					"title" => $row->title,
					"descr" => $row->descr,
					"text" => $row->text,
					"subitems" => $this->ListSections($row->id, $flat),
					);
			}
		}

		$DB->FreeRes($res);
		return $output;
	}




	function SectionURIbyID($id, $folder_id, $init_section_id = 0)
	{
		global $DB, $Engine;
		$footsteps = array();

		while ($id != $init_section_id)
		{
			$res = $DB->Exec("
				SELECT `pid`, `uri_part`
				FROM `" . $this->db_prefix . "sections`
				WHERE `id` = '$id'
				LIMIT 1
				");

			if ($row = $DB->FetchObject($res))
			{
				$DB->FreeRes($res);
				
				if (CF::IsNonEmptyStr($row->uri_part)) // Строчка от poganini ;-)
				{
					$footsteps[$id] = $row->uri_part; // Дабы не было ссылок типа www.example.com/news//1.html
				}
				
				$id = $row->pid;
			}

			else
			{
				return false;
			}
		}
		
		if (!$footsteps)
		{
			$output = $Engine->FolderURIbyID($folder_id);
	
			foreach ($footsteps as $elem)
			{
				$output .= "$elem/";
			}
	
			return $output;
		}
		
		else
		{
			return false;
		}
	}



	function Search($node_id, $search_input)
	{
		global $DB, $Engine, $Auth;
		$output = array();

		$DB->SetTable(ENGINE_DB_PREFIX . "nodes");
		$DB->AddFields(array("folder_id", "params"));
		$DB->AddCondFS("id", "=", $node_id);
		$DB->AddCondFS("module_id", "=", $this->module_id);
		$DB->AddCondFP("is_active");
		$res = $DB->Select(1);

		if ($row = $DB->FetchObject($res))
		{
			$DB->FreeRes($res);
			$parts = explode(";", $row->params);

			switch ($parts[0])
			{
				case "section":
					if (isset($parts[1]))
					{
						$node_folder_id = (int)$row->folder_id;
						$init_section_id = isset($parts[1]) ? (int)$parts[1] : 0;												
					}

					else
					{
						die($Engine->debug_level ? "EGallery module error #1099: illegal node provided for search (#$node_id)." : "");
					}
					break;


				default:
					die($Engine->debug_level ? "EGallery module error #1099: illegal node provided for search (#$node_id)." : "");
					break;
			}
		}

		else
		{
			$DB->FreeRes($res);
			die($Engine->debug_level ? "EGallery module error #1099: illegal node provided for search (#$node_id)." : "");
		}


		//$cats = array();
		//
		//foreach (array_merge(array($this->root_cat_id), $this->ListCats($this->root_cat_id, true)) as $elem)
		//{
		//	if ($Engine->OperationAllowed($this->module_id, "cat.search", $elem, $Auth->usergroup_id))
		//	{
		//		$cats[] = $elem;
		//	}
		//}


		//if ($cats) // !!! если есть категории, в которых разрешён поиск
		//{
			$DB->SetTable($this->db_prefix . "sections");
			$DB->AddFields(array("id", "title", "cache"));
			$DB->AddCondFP("is_active");

			//foreach ($cats as $elem)
			//{
			//	$DB->AddAltFS("cat_id", "=", $elem);
			//}

			//$DB->AppendAlts();
			$DB->AddAltFS("title", "LIKE", "%$search_input%");
			$DB->AddAltFS("cache", "LIKE", "%$search_input%");
			$DB->AppendAlts();
			$DB->AddOrder("update_time", true);
			$res = $DB->Select();

			while ($row = $DB->FetchObject($res))
			{
				if ($uri = $this->SectionURIbyID($row->id, $node_folder_id, $init_section_id))
				{
					$output[] = array(
						"uri" => $uri,
						"title" => $row->title,
						"text" => $row->cache,
						);
				}
			}

			$DB->FreeRes($res);
		//}

		return $output;
	}






	function Output()
	{
		return $this->output;
	}
}

?>