<?php

class EFiles
// version: 2.12.12
// date: 2013-12-19
{
	var $output;
	var $node_id;
	var $module_id;
	var $mode;
	var $subParams;
	var $upload_max_filesize;
	
	function EFiles($global_params, $params, $module_id, $node_id, $module_uri)
	{
		
		global $Engine, $Auth, $DB;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		
		if ($module_uri=='not_allowed') $Engine->HTTP403();
		
		if ($params && preg_match('/;/', $params)) {
			$paramParts = explode(';', $params);
			/*$this->output["scripts_mode"] = */$this->output["mode"] = $this->mode = $paramParts[0];
			$this->subParams = array_slice($paramParts, 1);
		} else{
			/*$this->output["scripts_mode"] = */$this->output["mode"] = $this->mode = $params;
		}
		$this->output['plugins'] = $this->output["scripts_mode"] = null;
		$this->output["user_displayed_name"] = $Auth->user_displayed_name;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
		$this->output["module_id"] = $this->module_id;
		//if ($this->output["mode"] != "files_list")
		//die(print_r($_POST));
		//die($this->output["mode"]);
		$reder = explode("?", $Engine->unqueried_uri);
		$rederect = $reder[0];
		
		$ini_settings = @parse_ini_file(INCLUDES . "EFiles.ini", true);
		
		$this->upload_max_filesize = trim(ini_get('upload_max_filesize'));
		$last = strtolower($this->upload_max_filesize[strlen($this->upload_max_filesize)-1]);
		switch($last) {
			case 'g':
				$this->upload_max_filesize *= 1024;
			case 'm':
				$this->upload_max_filesize *= 1024;
			case 'k':
				$this->upload_max_filesize *= 1024;
		}
		$this->output['upload_max_filesize'] = $this->upload_max_filesize;
		$this->output["maxsize"] = ini_get("upload_max_filesize");
		
		$this->output["msoffice_upload"] =  $Engine->OperationAllowed($this->module_id, 'files.upload.msoffice', -1, $Auth->usergroup_id);
		
		ini_set("session.upload_progress.name", "UPLOAD_IDENTIFIER");//var_dump($_POST[ini_get("session.upload_progress.name")]);//print_r($_SESSION);//echo ini_get("session.upload_progress.name");
			
		//echo $_REQUEST['params'];
		if (ALLOW_AUTH || $this->mode == "els")
		{
			switch ($this->mode)
			{
				case "ajax_add_file": {
					/*echo "<pre>";
					print_r($_FILES);
					print_r($_REQUEST);
					echo "</pre>";*/
					$this->output["mode"] = "ajax_add_file";
					if (isset($_FILES['upload_file'])) {
						if($_FILES['upload_file']['size'] > $this->upload_max_filesize) {
							$this->output["messages"]["bad"][] = "Размер вашего файла превышает максимально допустимый (".trim(ini_get('upload_max_filesize'))."Б)";
						} else {
							$file_name = /*iconv("UTF-8", "windows-1251", */$this->cp1251_to_utf8($_FILES['upload_file']['name'])/*)*/;
							$orig_name = explode(".", $file_name);
							//die(print_r($orig_name));
							$name_parts = count($orig_name);
							$ext = $orig_name[$name_parts-1];
							unset($orig_name[$name_parts-1]);
							$original_name = implode($orig_name, ".");
							//echo "<script>alert('".mb_detect_encoding($_FILES['upload_file']['name'])."');</script>";
							//$new_name = md5(time()*rand(1,1000)).'.'.$ext;
							if(array_search($ext, array('doc','docx')) !== false && !$Engine->OperationAllowed($this->module_id, 'files.upload.msoffice', -1, $Auth->usergroup_id)) {
								$this->output["messages"]["bad"][] = "В соответсвии с регламентом на портале не размещаются форматы файлов Microsoft Office. <br />Эти данные необходимо конвертировать в формат pdf. <br />За помощью обратитесь к системному администратору вашего подразделения.";
							} elseif(!isset($_POST["editfile"]) && (!isset($_POST["upload_descr"]) || empty($_POST["upload_descr"]))) {
								$this->output["messages"]["bad"][] = "Укажите полное название файла.";
							} elseif(isset($_POST["editfile"]) && (!isset($_POST["upload_descr_".$_POST["editfile"]]) || empty($_POST["upload_descr_".$_POST["editfile"]]))) {
								$this->output["messages"]["bad"][] = "Укажите полное название файла.";
							} else {
								if (!isset($_POST["editfile"])) { 
									$DB->SetTable("nsau_files");
									$DB->AddValues(array(
										"name" => $original_name,
										"descr" => $this->cp1251_to_utf8($_POST["upload_descr"]),
										"author" => $this->cp1251_to_utf8($_POST["upload_author"]),
										"year" => $this->cp1251_to_utf8($_POST["upload_year"]),
										"volume" => $_POST["upload_volume"],
										"edition" => $_POST["upload_edition"],
										"place" => $this->cp1251_to_utf8($_POST["upload_place"]),
										"filename" => $ext,
										"user_id" => $_SESSION["user_id"],
									));
		
									if (isset($_POST["is_html"]) && $_POST["is_html"]) {
										$DB->AddValue("is_html", 1);
									}
									
									$DB->Insert();
									$new_id = $DB->LastInsertID();
									$Engine->LogAction($this->module_id, "file", $new_id, "create");
								}
								else {
									$DB->SetTable("nsau_files");
									$DB->AddCondFS("id", "=", $_POST["editfile"]);
									$res = $DB->Select();
									$row = $DB->FetchAssoc($res);
									unlink ($_SERVER['DOCUMENT_ROOT'].'/files/'.$_POST["editfile"].'.'.$row["filename"]);
									
									$DB->SetTable("nsau_files");
									$DB->AddValues(array(
										"name" => $original_name,
										"filename" => $ext,
									));
									$DB->AddCondFS("id", "=", $_POST["editfile"]);
									$DB->Update();
									
									$new_id = $_POST["editfile"];
									$Engine->LogAction($this->module_id, "file", $new_id, "update");
								}							
								if (isset($_POST["allow_download"]) && $_POST["allow_download"]) {
									//$Engine->AddPrivilege($this->module_id, "files.download", $new_id, -1, 1, "create;право на скачивание всем группам");
									$Engine->AddPrivilege($this->module_id, "files.download", $new_id, 0, 1, "create;право на скачивание всем группам");
								}
								
								if(isset($_POST["editfile"])) {
									if (isset($_POST["allow_download_edit_".$_POST["editfile"]]) && $_POST["allow_download_edit_".$_POST["editfile"]]) {
										/*$DB->SetTable("engine_privileges");
										 $DB->AddValues(arFray(
										 		"module_id" => $this->module_id,
										 		"operation_name" => "files.download",
										 		"entry_id" => $new_id,
										 		"usergroup_id" => "0",
										 		"is_allowed" => "1"
										 ));
										$DB->Insert();*/
											
										$Engine->AddPrivilege($this->module_id, "files.download", $_POST["editfile"], 0, 1, "create;право на скачивание всем группам");
									} else if (!isset($_POST["allow_download"]) && isset($_POST["editfile"])) {
										$Engine->DeletePrivilege($this->module_id, "files.download", $_POST["editfile"], 0, 1);
									}
									
									$DB->SetTable("nsau_files");
									$DB->AddField("filename");
									$DB->AddCondFS("id", "=", (int)$_POST["editfile"]);
									$res = $DB->Select();
									$row = $DB->FetchAssoc($res);
									$ext = $row["filename"];
									$DB->FreeRes();
									
									$transfer_allowed = 0;
									if (isset($_POST["sel_teacher_".$_POST["editfile"]]) && $_POST["sel_teacher_".$_POST["editfile"]] != 0) {
										$array = $Engine->GetPrivileges($this->module_id, 'files.transfer', null, $Auth->usergroup_id);
										$dep_arr = null;
										foreach($array[$this->module_id]['files.transfer'] as $entry => $allowed) {
											if($Engine->OperationAllowed($this->module_id, 'files.transfer', $entry, $Auth->usergroup_id)) {
												$transfer_allowed = 1;
												break;
											}
										}
									}
									
									$DB->SetTable("nsau_files");
									$DB->AddCondFS("id", "=", (int)$_POST["editfile"]);
									if (isset($_POST["upload_descr_".$_POST["editfile"]]))
										$DB->AddValue("descr", iconv("UTF-8", "windows-1251", $_POST["upload_descr_".$_POST["editfile"]]));
									if (isset($_POST["upload_author_".$_POST["editfile"]]))
										$DB->AddValue("author", iconv("UTF-8", "windows-1251", $_POST["upload_author_".$_POST["editfile"]]));
									if (isset($_POST["upload_year_".$_POST["editfile"]]))
										$DB->AddValue("year", iconv("UTF-8", "windows-1251", $_POST["upload_year_".$_POST["editfile"]]));
									if (isset($_POST["upload_volume_".$_POST["editfile"]]))
										$DB->AddValue("volume", $_POST["upload_volume_".$_POST["editfile"]]);
									if (isset($_POST["upload_edition_".$_POST["editfile"]]))
										$DB->AddValue("edition", $_POST["upload_edition_".$_POST["editfile"]]);
									if (isset($_POST["upload_place_".$_POST["editfile"]]))
										$DB->AddValue("place", iconv("UTF-8", "windows-1251", $_POST["upload_place_".$_POST["editfile"]]));
										
									if($transfer_allowed) {
										$DB->AddValue("user_id", $_POST["sel_teacher_".$_POST["editfile"]]);
									}
									
									if (isset($_POST["sel_folder_".$_POST["editfile"]]) && !$transfer_allowed) {
										if($_POST["sel_folder_".$_POST["editfile"]]) {
											$DB->AddValue("folder_id", $_POST["sel_folder_".$_POST["editfile"]]);
										} else {
											$DB->AddValue("folder_id", NULL);
										}
									} elseif($transfer_allowed) {
										$DB->AddValue("folder_id", NULL);
									}
									if (isset($_POST["editfile"], $_POST["is_html_".$_POST["editfile"]])) {
										if ($ext == "zip") {
											$redirect = '';
											$DB->AddValue("is_html", 1);
										} else {
											$redirect = null;
											$this->output["messages"]["bad"][] = "Ваш файл не является html-документом.";
										}
									} else $DB->AddValue("is_html", 0);
									if($DB->Update()) {
										$DB->SetTable("nsau_files_subj");
										$DB->AddCondFS("file_id", "=", (int)$_POST["editfile"]);
										$DB->AddValue("approved", NULL);//echo $DB->UpdateQuery();exit;
										if($DB->Update()) {
											$Engine->LogAction($this->module_id, "file", (int)$_POST["editfile"]/*.":".$row["spec_id"]*/, "reset_attach");
										}
									};
								}
														
								$zip_error = "";
								//die($_SERVER["DOCUMENT_ROOT"]);
								if (isset($_POST["is_html"]) || isset($_POST["is_html_".$_POST["editfile"]])) {
									if ($ext != "zip") {
										$zip_error = "nozip";
									}
									else {
										if (isset($_POST["editfile"]))
											$this->RemoveDir($_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$new_id."/");
									
										$zip = new ZipArchive();
										$zip->open($_FILES['upload_file']['tmp_name']);
										for ($i=0; $i<$zip->numFiles;$i++) {
											$curfile = $zip->statIndex($i);
											if ($curfile["name"] == "index.html" || $curfile["name"] == "index.htm")
												$index_exists = 1;
										}
										
										if (!isset($index_exists))
											$zip_error = "noindex";
										elseif (!mkdir($_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$new_id."/", 0777)) {
											$zip_error = "nodir";
										}
										else {
											$zip->extractTo($_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$new_id."/");
										}
									}
								}
								
								$new_name = $new_id.'.'.$ext;
								$path = $_SERVER['DOCUMENT_ROOT'].'/files/'.$new_name;
								
								
								
								if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $path)) {
									$DB->SetTable("nsau_files");
									$DB->AddCondFS("id", "=", $new_id);
									$DB->Delete();
									$this->output["messages"]["bad"][] = "Не удалось закачать файл";
									
								}
								elseif($zip_error == "nozip") {
									$DB->SetTable("nsau_files");
									$DB->AddCondFS("id", "=", $new_id);
									$DB->Delete();
									$this->output["messages"]["bad"][] = "Для онлайн-просмотра необходим формат файла zip";
								}
								elseif($zip_error == "nodir") {
									$DB->SetTable("nsau_files");
									$DB->AddCondFS("id", "=", $new_id);
									$DB->Delete();
									$this->output["messages"]["bad"][] = "Невозможно создать папку для распаковки архива";
								}
								elseif($zip_error == "noindex") {
									$DB->SetTable("nsau_files");
									$DB->AddCondFS("id", "=", $new_id);
									$DB->Delete();
									$this->output["messages"]["bad"][] = "В архиве отсутствует индексный файл - index.html либо index.htm";
								}
								else {
									$DB->SetTable("nsau_files"); 
									$DB->AddValue("hash", md5_file($path));
									$DB->AddCondFS("id", "=", $new_id); 
									$DB->Update(); 							
									$this->files_list($new_id);
								}
							}
						}
					} else {
						if(!isset($_POST["editfile"])) { $this->output["messages"]["bad"][] = "Файл не обнаружен";}
						else {
							$DB->SetTable("nsau_files");
							$DB->AddCondFS("id", "=", $_POST["editfile"]);
							$res = $DB->Select();
							if($row = $DB->FetchAssoc($res)) {
								$ext = $row["filename"];
								$transfer_allowed = 0;
								if (isset($_POST["sel_teacher_".$_POST["editfile"]]) && $_POST["sel_teacher_".$_POST["editfile"]] != 0) {
									$array = $Engine->GetPrivileges($this->module_id, 'files.transfer', null, $Auth->usergroup_id);
									$dep_arr = null;
									foreach($array[$this->module_id]['files.transfer'] as $entry => $allowed) {
										if($Engine->OperationAllowed($this->module_id, 'files.transfer', $entry, $Auth->usergroup_id)) {
											$transfer_allowed = 1;
											break;
										}
									}
								}
								
								$editfile = $_POST["editfile"];
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $_POST["editfile"]);
								
								if (isset($_POST["upload_descr_".$_POST["editfile"]]))
									$DB->AddValue("descr", iconv("UTF-8", "windows-1251", $_POST["upload_descr_".$_POST["editfile"]]));
								if (isset($_POST["upload_author_".$_POST["editfile"]]))
									$DB->AddValue("author", iconv("UTF-8", "windows-1251", $_POST["upload_author_".$_POST["editfile"]]));
								if (isset($_POST["upload_year_".$_POST["editfile"]]))
									$DB->AddValue("year", iconv("UTF-8", "windows-1251", $_POST["upload_year_".$_POST["editfile"]]));
								if (isset($_POST["upload_volume_".$_POST["editfile"]]))
									$DB->AddValue("volume", $_POST["upload_volume_".$_POST["editfile"]]);
								if (isset($_POST["upload_edition_".$_POST["editfile"]]))
									$DB->AddValue("edition", $_POST["upload_edition_".$_POST["editfile"]]);
								if (isset($_POST["upload_place_".$_POST["editfile"]]))
									$DB->AddValue("place", iconv("UTF-8", "windows-1251", $_POST["upload_place_".$_POST["editfile"]]));
									
								if($transfer_allowed) {
									$DB->AddValue("user_id", $_POST["sel_teacher_".$_POST["editfile"]]);
								}
								
								if (isset($_POST["sel_folder_".$_POST["editfile"]]) && !$transfer_allowed) {
									if($_POST["sel_folder_".$_POST["editfile"]]) {
										$DB->AddValue("folder_id", $_POST["sel_folder_".$_POST["editfile"]]);
									} else {
										$DB->AddValue("folder_id", NULL);
									}
								} elseif($transfer_allowed) {
									$DB->AddValue("folder_id", NULL);
								}							
							
								//echo $DB->UpdateQuery();
								if($DB->Update()) {
									if (isset($_POST["allow_download_edit_".$_POST["editfile"]]) && $_POST["allow_download_edit_".$_POST["editfile"]]) {
										//$Engine->AddPrivilege($this->module_id, "files.download", $new_id, -1, 1, "create;право на скачивание всем группам");
										$Engine->AddPrivilege($this->module_id, "files.download", $_POST["editfile"], 0, 1, "create;право на скачивание всем группам");
									} else if (!isset($_POST["allow_download_edit_".$_POST["editfile"]]) && isset($_POST["editfile"])) {
										$Engine->DeletePrivilege($this->module_id, "files.download", $_POST["editfile"], 0, 1);
									}
									
									$this->files_list($_POST["editfile"]);
									
									//$res = $DB->Select();
									//$row = $DB->FetchAssoc($res);
									
									//$file_name = /*iconv("UTF-8", "windows-1251", */$this->cp1251_to_utf8($_FILES['upload_file']['name'])/*)*/;
									//$orig_name = explode(".", $file_name);
									//die(print_r($orig_name));
									/*$name_parts = count($orig_name);
									$ext = $orig_name[$name_parts-1];
									unset($orig_name[$name_parts-1]);
									$original_name = implode($orig_name, ".");*/
								}							
							}
						}
					}
				}
				break;
			
				case "ajax_del_file": {
					$this->output["mode"] = "ajax_del_file";
					if (isset($_REQUEST['data']["f_id"])) {
						$DB->SetTable("nsau_files");
						$DB->AddCondFS("id", "=", $_REQUEST['data']["f_id"]);
						$res = $DB->Select();
						if($row = $DB->FetchAssoc($res)) {
							if($row['is_html'] == 1) {
								function removeDirRec($dir) {
									if ($objs = glob($dir."/*")) {
										foreach($objs as $obj) {
											is_dir($obj) ? removeDirRec($obj) : unlink($obj);
										}
									}
									return rmdir($dir);
								}						
							
								$str = $_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$row["id"]."/";
								if (removeDirRec($str) || !file_exists($str)) {
									$this->output["ajax_del_file"] = 1;
								} else {
									$this->output["ajax_del_file"] = 0;
								}
							} else {
								$str = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["id"].".".$row["filename"];
								if (unlink($str) || !file_exists($str)) {
									$this->output["ajax_del_file"] = 1;
								} else {
									$this->output["ajax_del_file"] = 0;
								}
							}
							if(isset($this->output["ajax_del_file"]) && $this->output["ajax_del_file"] == 1) {
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $_REQUEST['data']["f_id"]);
								$DB->Delete();
								$Engine->LogAction($this->module_id, "file", $_REQUEST['data']["f_id"], "delete");
								
								$DB->SetTable("nsau_files_subj");
								$DB->AddCondFS("file_id", "=", $_REQUEST['data']["f_id"]);
								$DB->Delete();
							}
						}						
						//die(print_r($_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["id"].".".$row["filename"]));
						
					} else {
						$this->output["ajax_del_file"] = 0;
					}
				}
				break;
				
				case "ajax_del_attach": {
					$this->output["mode"] = "ajax_del_attach";
					if (isset($_REQUEST['data']["a_id"])) {
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("id", "=", $_REQUEST['data']["a_id"]);
						$DB->AddField("file_id");
						$DB->AddField("spec_id");
						$res = $DB->Select(1);
						while($row = $DB->FetchArray()){
							$Engine->LogAction($this->module_id, "file", $row["file_id"]/*.":".$row["spec_id"]*/, "del_attach");
						}
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("id", "=", $_REQUEST['data']["a_id"]);
						$DB->Delete();
						$this->output["ajax_del_attach"] = 1;
					} else {
						$this->output["ajax_del_attach"] = 0;
					}
				}
				break; 
			
				case "ajax_update_file_select": {
					$this->output["mode"] = "ajax_update_file_select";
					$this->files_list();
				}
				break;
				
				case "ajax_search_subj": {
					$this->output["subjects_list"] = array(); 
					$this->output["mode"] = "ajax_search_subj";
					$DB->SetTable("nsau_subjects", "subj");
					$DB->AddTable("nsau_departments", "dep");
					$DB->AddCondFF("subj.department_id","=", "dep.id");
					$DB->AddCondFS("subj.name", "LIKE", "%".iconv("utf-8", "Windows-1251", $_REQUEST['data']["q"])."%"); 
					$DB->AddField("subj.id", "sid");
					$DB->AddField("subj.name", "sname");
					$DB->AddField("dep.id", "did");
					$DB->AddField("dep.name", "dname");
					$DB->AddOrder("dep.name");
					$DB->AddOrder("subj.name");
					//echo $DB->SelectQuery();
					$res = $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["subjects_list"][$row["did"]]["name"] = $this->cp1251_to_utf8($row["dname"]);
						//$row["dname"] = $this->cp1251_to_utf8($row["dname"]); 
						$this->output["subjects_list"][$row["did"]]["subj"][] = $row;
					}
				}
				break;
				
				case "ajax_autocomplete_subj": {
					$json = array();
					$this->output["mode"] = "ajax_autocomplete_subj";
					$DB->SetTable("nsau_subjects", "subj");
					//$DB->AddTable("nsau_departments", "dep");
					//$DB->AddCondFF("subj.department_id","=", "dep.id");
					$DB->AddJoin("nsau_departments dep.id", "subj.department_id");
					$DB->AddCondFS("subj.name", "LIKE", "".iconv("utf-8", "Windows-1251", $_REQUEST['data']["q"])."%");
					$DB->AddField("subj.id", "id");
					$DB->AddField("subj.name", "name");
					$DB->AddField("dep.id", "did");
					$DB->AddField("dep.name", "dname");
					$DB->AddOrder("subj.name");
					$DB->AddOrder("dep.name");//echo $DB->SelectQuery(); 
					$res = $DB->Select();//$privs = $Engine->GetPrivileges($this->module_id, "files.attach", null, $Auth->usergroup_id);print_r($privs);
					$count = 0; 
					while($row = $DB->FetchAssoc($res)) {
						if(!$Engine->OperationAllowed($this->module_id, "files.attach", $row["did"], $Auth->usergroup_id)) continue;
						if($row["dname"]) $row["name"] = $row["dname"] . " > ".$row["name"];
						$json[] = $row;
						
						$count++; 
						if($count >= 10) break; 
					}
					
					header("Cache-Control: no-cache, must-revalidate");
					header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
					header("Pragma: no-cache"); 
						
					$this->output["json"] = $json;
				}
				break;
				
				/* case "ajax_autocomplete_subj": {
					$json = array();
					$this->output["mode"] = "ajax_autocomplete_subj";
					$DB->SetTable("nsau_subjects", "subj");
					//$DB->AddTable("nsau_departments", "dep");
					//$DB->AddCondFF("subj.department_id","=", "dep.id");
					$DB->AddJoin("nsau_departments.id", "subj.department_id");
					$DB->AddCondFS("subj.name", "LIKE", "".iconv("utf-8", "Windows-1251", $_REQUEST['data']["q"])."%");
					$DB->AddField("subj.id", "id");
					$DB->AddField("subj.name", "name");
					$DB->AddField("nsau_departments.id", "did");
					$DB->AddField("nsau_departments.name", "dname");
					$DB->AddOrder("subj.name");
					$DB->AddOrder("nsau_departments.name");
					$res = $DB->Select(10);
					while($row = $DB->FetchAssoc($res)) {
						//$json[$row["id"]]["name"] = $this->cp1251_to_utf8($row["dname"]);
						//$json[$row["did"]]["id"] = $this->cp1251_to_utf8($row["id"]);
						//$row["dname"] = $this->cp1251_to_utf8($row["dname"]);
						if($row["dname"]) $row["name"] = $row["dname"] . " > ".$row["name"];
						$json[] = $row;
					}
						
					$this->output["json"] = $json;
				} */
			
				case "abitfile": {
					if (isset($_FILES['abit'])) {
						$fname = explode(".", $_FILES['abit']['name']);
						if ($fname[count($fname)-1] != "csv")
							$this->output["messages"]["bad"][] = "Необходим файл в формате csv";
						else {
							$f = fopen($_FILES['abit']['tmp_name'], "r");
							$content = fread($f, filesize($_FILES['abit']['tmp_name']));
							$parts = explode("\r\n", $content);
							array_shift($parts);
							$DB->Exec("truncate table abit_temp");
							
							$toobad = 0;
							
							
								foreach ($parts as $part) {
									if ($part) {
										$string = explode(";", $part);
										if (count($string) > 1 && count($string) != 15 && !$toobad) {
											$this->output["messages"]["bad"][] = "Неверное количество полей: ".count($string)." вместо 15";
											$toobad = 1;
										}
										elseif (count($string) > 1 && !$toobad) {
											$DB->SetTable("abit_temp");
											$DB->AddValue("fp", $string[0]);
											$DB->AddValue("f1", $string[1]);
											$DB->AddValue("f2", $string[2]);
											$DB->AddValue("f3", $string[3]);
											$DB->AddValue("f4", $string[4]);
											$DB->AddValue("fz", $string[5]);
											$DB->AddValue("fdog", $string[6]);
											$DB->AddValue("fn", $string[7]);
											$DB->AddValue("fd", $string[8]);
											$DB->AddValue("f5", $string[9]);
											$DB->AddValue("f6", $string[10]);
											$DB->AddValue("fs1", $string[11]);
											$DB->AddValue("fs2", $string[12]);
											
											if ($string[13] == "ЛОЖЬ")
												$DB->AddValue("fout", "0");
											else
												$DB->AddValue("fout", "1");
												
											if ($string[14] == "ЛОЖЬ")
												$DB->AddValue("ftook", "0");
											else
												$DB->AddValue("ftook", "1");
											
											if (!$DB->Insert())
												$this->output["messages"]["bad"][] = "Не удалось добавить абитуриента ".$string[3];
										}
									}
								}
							
							if (!$toobad) {
								if (isset($_POST["spo"]) && $_POST["spo"]=="on")
									$sql = "CALL ab22_spo();";
									
								else
									$sql = "CALL ab22();";
							
								if ($DB->Exec($sql))
									$this->output["messages"]["good"][] = "Абитуриенты загружены";
								else
									$this->output["messages"]["bad"][] = "Процедура вернула ошибку: ".$DB->Error();
							}
						}
					}
				}
				break;
				
				case "els": {

					global $Engine, $DB, $Auth;
					
					
					$this->output["scripts_mode"] = 'normal';
					$filesShowLimit = 20;
					$pagesShowLimit = 10;
					if (isset($_POST['data'])) {
						$data = $_POST['data'];
						foreach ($_POST['data'] as $ind=>$value) {
						
						}
						
						
						$DB->SetTable("nsau_files", "f");
						$DB->AddTable("nsau_files_subj", "s");
						$DB->AddTable("nsau_file_view", "v");
						$DB->AddTable("nsau_subjects", "su");
						$DB->AddExp("COUNT(distinct f.id)");
						
						$DB->AddCondFF("f.id", "=", "s.file_id");
						$DB->AddCondFF("v.id", "=", "s.view_id");
						$DB->AddCondFF("su.id", "=", "s.subject_id");
						$DB->AddCondFS("s.approved", "=", 1);
						if (isset($data['file_subj']) && $data['file_subj']) 
							$DB->AddCondFS("s.subject_id", "=", $data['file_subj']);
						if (isset($data['file_spec']) && $data['file_spec']) 
							$DB->AddCondFS("s.spec_id", "=", $data['file_spec']);
						if (isset($data['file_education']) && $data['file_education']) 
							$DB->AddCondFS("s.education", "=", CF::Utf2Win($data['file_education']));
						if (isset($data['file_year']) && $data['file_year']) 
							$DB->AddCondFS("f.year", "=", $data['file_year']);
						if (isset($data['file_umkd']) && $data['file_umkd']) 
							$DB->AddCondFS("s.view_id", "=", $data['file_umkd']);
						if (isset($data['file_author']) && $data['file_author']) 
							$DB->AddCondFS("f.author", " LIKE ", '%'.iconv('utf-8', 'windows-1251', mysql_real_escape_string($data['file_author'])).'%');
						if (isset($data['file_title']) && $data['file_title']) { 
							$DB->AddAltFS("f.name", " LIKE ", '%'.iconv('utf-8', 'windows-1251', mysql_real_escape_string($data['file_title'])).'%');
							$DB->AddAltFS("f.descr", " LIKE ", '%'.iconv('utf-8', 'windows-1251', mysql_real_escape_string($data['file_title'])).'%');
							$DB->AppendAlts();
						}
						if (isset($data['sort_field']) && $data['sort_field']) {
							if ($data['sort_field'] == 'year')
								$DB->AddOrder("f.year", (isset($data['sort_dir']) && $data['sort_dir'] ? ($data['sort_dir'] == "desc" ? true : false ): true));
							else {
								$DB->AddOrder("f.descr", (isset($data['sort_dir']) && $data['sort_dir'] ? ($data['sort_dir'] == "desc" ? false : true ) : false));
								$DB->AddOrder("f.name", (isset($data['sort_dir']) && $data['sort_dir'] ? ($data['sort_dir'] == "desc" ? false : true ) : false));
							}
						}
						
						$res1 = $DB->Select(1, null , true, false, true);
						list($count) = $DB->FetchRow($res1);
						
						$DB->ResetFields();
						$DB->AddField("f.id", "id");
						$DB->AddField("f.user_id", "user_id");
						$DB->AddField("f.name", "name");
						$DB->AddField("f.descr", "descr");
						$DB->AddField("f.author", "author");
						$DB->AddField("f.place", "place");
						$DB->AddField("f.year", "year");
						$DB->AddField("s.education", "education");
						$DB->AddField("s.view_id", "view_id");
						$DB->AddField("s.id", "file_attach_id");
						$DB->AddField("v.view_name", "view");
						$DB->AddField("su.name", "subj");
						$DB->AddGrouping("f.id");
						
						if (isset($data['page']) && is_numeric($data['page'])) {
							$page = $data['page'];
							while (($filesShowLimit*($page-1)) >= $count && ($page>1)) {
								$page--;
							}
						} else $page = 1; 
												
						$res = $DB->Select($filesShowLimit, isset($data['page']) ? $filesShowLimit*($page-1) : null ,true, true, true);
						
						$files = array();
						$uniqueIds = array();
						while ($row = $DB->FetchAssoc($res)) {
							$row["pid"] = null;
							foreach(array('name', 'descr', 'author', 'place', 'view', 'subj', 'education') as $field)
								$row[$field] = iconv('windows-1251', 'utf-8', $row[$field]);
							if (!in_array($row['id'], $uniqueIds)) {
								$uniqueIds[] = $row['id'];
								$user_ids[] = $row["user_id"];
								if (!isset($files[$row["user_id"]]))
									$files[$row["user_id"]] = array();
								$files[$row["user_id"]][] = $row;
							}
						}

						if(!empty($user_ids)) {
							$res = $DB->Exec("SELECT last_name, name, patronymic, id, user_id FROM nsau_people WHERE user_id IN (".implode(',', $user_ids).")");
							while ($row = $DB->FetchAssoc($res)) {
								foreach($files[$row["user_id"]] as $ind=>$file) {
									$files[$row["user_id"]][$ind]["pid"] = $row["id"];
									$files[$row["user_id"]][$ind]["pname"] = iconv('windows-1251', 'utf-8', $row["last_name"]." ".$row["name"]." ".$row["patronymic"]);
								}
							}
						}
						
						$resFiles = array();
						foreach($files as $user_files)
							foreach($user_files as $file)
								$resFiles[] = $file;
						
											
												
						require_once INCLUDES . "Pager.class";
						$callback = $this->module_id."%els%".(isset($data['sort_field']) ? "sort_field:".$data['sort_field']."," : "").
															(isset($data['sort_dir']) ? "sort_dir:".$data['sort_dir']."," : "").
															(isset($data['file_title']) ? "file_title:".$data['file_title']."," : "").
															(isset($data['file_author']) ? "file_author:".$data['file_author']."," : "").
															(isset($data['file_year']) ? "file_year:".$data['file_year']."," : "").
															(isset($data['file_spec']) ? "file_spec:".$data['file_spec']."," : "").
															(isset($data['file_umkd']) ? "file_umkd:".$data['file_umkd']."," : "").
															"page:[PAGE_NUM]";

						$Pager = new Pager($count, $filesShowLimit, $pagesShowLimit, null, null, array('page'=> $page), $callback); 
						$this->output = json_encode(array('search_res'=>$resFiles,'pager_output'=>$Pager->Act()));
						
					} else {
						$DB->SetTable("nsau_file_view");
						$res= $DB->Select();
						while ($row = $DB->FetchAssoc($res))
							$umkds[] = $row;
						$DB->SetTable("nsau_specialities");
						$DB->AddOrder("name");
						$res= $DB->Select();
						while ($row = $DB->FetchAssoc($res))
							$specs[] = $row;
						$DB->SetTable("nsau_subjects");
						$DB->AddOrder("name");
						$res= $DB->Select();
						while ($row = $DB->FetchAssoc($res))
							$subjects[] = $row;
						$this->output["umkd"] = $umkds;
						$this->output["spec"] = $specs;
						$this->output["subjects"] = $subjects;
						$DB->SetTable("nsau_files", "f");
						$DB->AddTable("nsau_files_subj", "fs");
						$DB->AddCondFF("fs.file_id", "=", "f.id");
						$DB->AddField("f.year", "year");
						$DB->AddOrder("f.year", true);
						$res = $DB->Select(null, null, true, true, true);
						while ($row = $DB->FetchAssoc($res))
							$years[] = $row["year"];
						
						$this->output["years"] = $years;
						$this->output["scripts_mode"] = $this->output["mode"] = "els";
						$this->output["feedback_module_id"] = $this->module_id;
						$this->output["show_publisher"] = $Engine->OperationAllowed($this->module_id, "files.show.publisher", -1, $Auth->usergroup_id);	
						$this->output["allow_download"] = ($Auth->logged_in && $Auth->user_id) ? 1 : 0 ;
					}

				}
				break;
			
				case "handle_umkd": {
					$DB->SetTable("nsau_file_view"); 
					if (isset($_POST["umkd_id"])) {  
						if ($_POST["umkd_id"]) {
							$DB->AddCondFS("id", "=", $_POST["umkd_id"]); 
							if ($_POST["umkd_text"]) {
								$DB->AddValues(array(
									"view_name" => $_POST["umkd_text"],
								));
								if ($res = $DB->Update())
									$Engine->LogAction($this->module_id, "umkd_item", $_POST["umkd_id"], "update");
							} else {
								if ($res = $DB->Delete())
									$Engine->LogAction($this->module_id, "umkd_item", $_POST["umkd_id"], "delete");
								else 
									$this->output["messages"]["info"][] = "delete_constraint";
							}
							$DB->FreeRes($res);
						} else {
							$DB->AddValues(array(
								"view_name" => $_POST["umkd_text"],
								"mark" => "",
								"pos" => 0,
							));
							if ($res = $DB->Insert()) 
								$Engine->LogAction($this->module_id, "umkd_item", $DB->LastInsertID(), "insert");
							$DB->FreeRes($res);
						}
					}
					$DB->Init();
					$DB->SetTable("nsau_file_view"); 
					$this->output["umkd_list"] = array();
					$DB->AddField("id");
					$DB->AddField("view_name");
					$DB->AddOrder("pos");
					$res = $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["umkd_list"][] = $row;
					}
					$this->output["scripts_mode"] = $this->output["mode"] = "umkd";
				}
				break;
				
				case "files_list": {
					$this->output["scripts_mode"][] = $this->output["mode"] = "files_list";
					$this->output["scripts_mode"][] = "show_attach_file";
					$this->output['plugins'][] = 'jquery.form';
					
					$redirect = null;
					if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && stripos($_SERVER["HTTP_USER_AGENT"], "msie")) 
						$this->output["messages"]["bad"][] = "Размер вашего файла превышает максимально допустимый (150 МБ)";
					if (isset($_FILES['upload_file']) && !empty($_FILES['upload_file']['name'])) {
						$file_name = $this->cp1251_to_utf8($_FILES['upload_file']['name']);					
						$orig_name = explode(".", $file_name);
						//die(print_r($orig_name));
						$name_parts = count($orig_name);
						$ext = $orig_name[$name_parts-1];
						unset($orig_name[$name_parts-1]);
						$original_name = implode($orig_name, ".");
						//$new_name = md5(time()*rand(1,1000)).'.'.$ext;
						if(array_search($ext, array('doc','docx')) !== false && !$Engine->OperationAllowed($this->module_id, 'files.upload.msoffice', -1, $Auth->usergroup_id)) {
							$this->output["messages"]["bad"][] = "В соответсвии с регламентом на портале не размещаются форматы файлов Microsoft Office. <br />Эти данные необходимо конвертировать в формат pdf. <br />За помощью обратитесь к системному администратору вашего подразделения.";
						} elseif(!isset($_POST["editfile"]) && (!isset($_POST["upload_descr"]) || empty($_POST["upload_descr"]))) {
							$this->output["messages"]["bad"] = "Укажите полное название файла.";
						} elseif(isset($_POST["editfile"]) && (!isset($_POST["upload_descr_".$_POST["editfile"]]) || empty($_POST["upload_descr_".$_POST["editfile"]]))) {
							$this->output["messages"]["bad"] = "Укажите полное название файла.";
						} else {													
							if (!isset($_POST["editfile"])) { 
								$DB->SetTable("nsau_files");
								$DB->AddValues(array(
									"name" => $original_name,
									"descr" => $this->cp1251_to_utf8($_POST["upload_descr"]),
									"author" => $this->cp1251_to_utf8($_POST["upload_author"]),
									"year" => $this->cp1251_to_utf8($_POST["upload_year"]),
									"volume" => $_POST["upload_volume"],
									"edition" => $_POST["upload_edition"],
									"place" => $this->cp1251_to_utf8($_POST["upload_place"]),
									"filename" => $ext,
									"user_id" => $_SESSION["user_id"],
								));
	
								if (isset($_POST["is_html"]) && $_POST["is_html"])
									$DB->AddValue("is_html", 1);
								
								$DB->Insert();
								$new_id = $DB->LastInsertID();
								$Engine->LogAction($this->module_id, "file", $new_id, "create");
							}
							else { 
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $_POST["editfile"]);
								$res = $DB->Select();
								$row = $DB->FetchAssoc($res);
								unlink ($_SERVER['DOCUMENT_ROOT'].'/files/'.$_POST["editfile"].'.'.$row["filename"]);
								
								$str = $_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$_POST["editfile"]."/";
								if(file_exists($str)) {
									function removeDirRec($dir) {
										if ($objs = glob($dir."/*")) {
											foreach($objs as $obj) {
												is_dir($obj) ? removeDirRec($obj) : unlink($obj);
											}
										}
										return rmdir($dir);
									}
									removeDirRec($str);
								}
								
								$DB->SetTable("nsau_files");
								$DB->AddValues(array(
									"name" => $original_name,
									"filename" => $ext,
								));
								$DB->AddCondFS("id", "=", $_POST["editfile"]);
								$DB->Update();
								
								$new_id = $_POST["editfile"];
								$Engine->LogAction($this->module_id, "file", $new_id, "update");
							}
							
							if (isset($_POST["allow_download"]) && $_POST["allow_download"]) {
								//$Engine->AddPrivilege($this->module_id, "files.download", $new_id, -1, 1, "create;право на скачивание всем группам");
								$Engine->AddPrivilege($this->module_id, "files.download", $new_id, 0, 1, "create;право на скачивание всем группам");
							}
							 
	
							
							$zip_error = "";
							//die($_SERVER["DOCUMENT_ROOT"]);
							if (isset($_POST["is_html"])) {
								if ($ext != "zip") {
									$zip_error = "nozip";
								}
								else {
									if (isset($_POST["editfile"]))
										$this->RemoveDir($_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$new_id."/");
								
									$zip = new ZipArchive();
									$zip->open($_FILES['upload_file']['tmp_name']);
									for ($i=0; $i<$zip->numFiles;$i++) {
										$curfile = $zip->statIndex($i);
										if ($curfile["name"] == "index.html" || $curfile["name"] == "index.htm")
											$index_exists = 1;
									}
									
									if (!isset($index_exists))
										$zip_error = "noindex";
									elseif (!mkdir($_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$new_id."/", 0777)) {
										$zip_error = "nodir";
									}
									else {
										$zip->extractTo($_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$new_id."/");
									}
								}
							}
							
							$new_name = $new_id.'.'.$ext;
							$path = $_SERVER['DOCUMENT_ROOT'].'/files/'.$new_name;
							
							$Engine->LogAction($this->module_id, "file", $new_id, "create");
							
							if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $path)) {
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $new_id);
								$DB->Delete();
								$this->output["messages"]["bad"][] = "Не удалось закачать файл";
							}
							elseif($zip_error == "nozip") {
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $new_id);
								$DB->Delete();
								$this->output["messages"]["bad"][] = "Для онлайн-просмотра необходим формат файла zip";
							}
							elseif($zip_error == "nodir") {
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $new_id);
								$DB->Delete();
								$this->output["messages"]["bad"][] = "Невозможно создать папку для распаковки архива";
							}
							elseif($zip_error == "noindex") {
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $new_id);
								$DB->Delete();
								$this->output["messages"]["bad"][] = "В архиве отсутствует индексный файл - index.html либо index.htm";
							}
							else {
								$DB->SetTable("nsau_files");
								$DB->AddValue("hash", md5_file($path));
								$DB->AddCondFS("id", "=", $new_id);
								$DB->Update();
								if (!isset($_POST["editfrom"])) {
									$redirect = '';
									//CF::Redirect();
								} else {
									$redirect = '/file/'.$_POST["editfile"].'/';
									//CF::Redirect('/file/'.$_POST["editfile"].'/');
								}
							}
						}
					} 
					 
					if(isset($_POST["editfile"]) && (!isset($_POST["upload_descr_".$_POST["editfile"]]) || empty($_POST["upload_descr_".$_POST["editfile"]]))) {
						$this->output["messages"]["bad"] = "Укажите полное название файла.";
					} elseif(isset($_POST["editfile"])) {
						if (isset($_POST["allow_download_edit_".$_POST["editfile"]]) && $_POST["allow_download_edit_".$_POST["editfile"]]) {
								/*$DB->SetTable("engine_privileges");
								$DB->AddValues(arFray(
									"module_id" => $this->module_id,
									"operation_name" => "files.download",
									"entry_id" => $new_id,
									"usergroup_id" => "0",
									"is_allowed" => "1"
								));
								$DB->Insert();*/
							
							$Engine->AddPrivilege($this->module_id, "files.download", $_POST["editfile"], 0, 1, "create;право на скачивание всем группам");								
						} else if (!isset($_POST["allow_download"]) && isset($_POST["editfile"])) {
							$Engine->DeletePrivilege($this->module_id, "files.download", $_POST["editfile"], 0, 1);						
						}

						$DB->SetTable("nsau_files");
						$DB->AddField("filename");
						$DB->AddCondFS("id", "=", (int)$_POST["editfile"]);
						$res = $DB->Select();
						$row = $DB->FetchAssoc($res);
						$ext = $row["filename"];
						$DB->FreeRes();
						
						$transfer_allowed = 0;
						if (isset($_POST["sel_teacher_".$_POST["editfile"]]) && $_POST["sel_teacher_".$_POST["editfile"]] != 0) {
							$array = $Engine->GetPrivileges($this->module_id, 'files.transfer', null, $Auth->usergroup_id);
							$dep_arr = null;
							foreach($array[$this->module_id]['files.transfer'] as $entry => $allowed) {
								if($Engine->OperationAllowed($this->module_id, 'files.transfer', $entry, $Auth->usergroup_id)) {
									$transfer_allowed = 1;
									break;
								}
							}
						}
						
						$DB->SetTable("nsau_files");
						$DB->AddCondFS("id", "=", (int)$_POST["editfile"]);
						if (isset($_POST["upload_descr_".$_POST["editfile"]])) 
							$DB->AddValue("descr", iconv("UTF-8", "windows-1251", $_POST["upload_descr_".$_POST["editfile"]]));
						if (isset($_POST["upload_author_".$_POST["editfile"]])) 
							$DB->AddValue("author", iconv("UTF-8", "windows-1251", $_POST["upload_author_".$_POST["editfile"]]));
						if (isset($_POST["upload_year_".$_POST["editfile"]])) 
							$DB->AddValue("year", iconv("UTF-8", "windows-1251", $_POST["upload_year_".$_POST["editfile"]]));
						if (isset($_POST["upload_volume_".$_POST["editfile"]])) 
							$DB->AddValue("volume", $_POST["upload_volume_".$_POST["editfile"]]);
						if (isset($_POST["upload_edition_".$_POST["editfile"]])) 
							$DB->AddValue("edition", $_POST["upload_edition_".$_POST["editfile"]]);
						if (isset($_POST["upload_place_".$_POST["editfile"]])) 
							$DB->AddValue("place", iconv("UTF-8", "windows-1251", $_POST["upload_place_".$_POST["editfile"]]));
							
						if($transfer_allowed) {
							$DB->AddValue("user_id", $_POST["sel_teacher_".$_POST["editfile"]]);
						}
						
						if (isset($_POST["sel_folder_".$_POST["editfile"]]) && !$transfer_allowed) {
							if($_POST["sel_folder_".$_POST["editfile"]]) {
								$DB->AddValue("folder_id", $_POST["sel_folder_".$_POST["editfile"]]);
							} else {
								$DB->AddValue("folder_id", NULL);
							}							
						} elseif($transfer_allowed) {
							$DB->AddValue("folder_id", NULL);
						}
						if (isset($_POST["editfile"], $_POST["is_html_".$_POST["editfile"]])) {
							if ($ext == "zip") {
								$redirect = '';
								$DB->AddValue("is_html", 1);
							} else {
								$redirect = null;
								$this->output["messages"]["bad"][] = "Ваш файл не является html-документом.";
							}
						} else $DB->AddValue("is_html", 0);
						if($DB->Update()) {
							$DB->SetTable("nsau_files_subj");
							$DB->AddCondFS("file_id", "=", (int)$_POST["editfile"]);
							$DB->AddValue("approved", NULL);//echo $DB->UpdateQuery();exit; 
							if($DB->Update()) {
								$Engine->LogAction($this->module_id, "file", (int)$_POST["editfile"]/*.":".$row["spec_id"]*/, "reset_attach");
							}							
						};
						
						if(!is_null($redirect)) {
							if (!isset($_POST["editfrom"])) {
								$redirect = '';
								//CF::Redirect();
							} else {
								$redirect = '/file/'.$_POST["editfile"].'/';
								//CF::Redirect('/file/'.$_POST["editfile"].'/');
							}
						}
					}
					if(!is_null($redirect)) {
						CF::Redirect($redirect);
					}
					
					/*if ((isset($_POST["sel_subject"]) && $_POST["sel_subject"]) || (isset($_POST["sel_spec"]) && $_POST["sel_spec"])) {
						echo "<pre>";
						print_r($_POST);
						echo "</pre>";
						$DB->SetTable("nsau_files_subj");
						$DB->AddValue("file_id", $_POST["sel_file"]);
						if(isset($_POST["sel_subject"]) && !empty($_POST["sel_subject"]))
							$DB->AddValue("subject_id", $_POST["sel_subject"]);
						if(isset($_POST["sel_spec"]) && !empty($_POST["sel_spec"]))
							$DB->AddValue("spec_id", $_POST["sel_spec"]);
						if(isset($_POST["sel_spec_type"]) && !empty($_POST["sel_spec_type"]))
							$DB->AddValue("spec_type", $_POST["sel_spec_type"]);
						if(isset($_POST["sel_view"]) && !empty($_POST["sel_view"]))
							$DB->AddValue("view_id", $_POST["sel_view"]);
						if(isset($_POST["sel_education"]) && !empty($_POST["sel_education"]))
							$DB->AddValue("education", $_POST["sel_education"]);
						if (!$DB->Insert()) $this->output["messages"]["bad"][] = "Указан незарегистрированный в базе данных предмет либо заданная привязка уже существует";
					}*/
					if(isset($_POST['referenc_file']) && $_POST['referenc_file'] == 1) {
						$this->referenc_file();
					}
					
					/*if (isset($_POST["sel_spec"]) && $_POST["sel_spec"]) {
						$DB->SetTable("nsau_files_spec");
						$ins = $DB->Exec("INSERT INTO `nsau_files_spec` (`file_id`, `spec_id`, `spec_type`, `view_id`) VALUES (".$_POST["sel_file"].", '".$_POST["sel_spec"]."',".($_POST["sel_spec_type"] ? $_POST["sel_spec_type"] : 0).", ".((!isset($_POST["sel_view"]) || $_POST["sel_view"] == "") ? "null" : intval($_POST["sel_view"])).") ");
						if (!$ins) $this->output["messages"]["bad"][] = "Указано незарегистрированное в базе данных направление либо заданная привязка уже существует";
					}*/
					if (isset($_POST["sel_folder"]) && $_POST["sel_folder"]) {
						$DB->SetTable("nsau_files");
						//print_r($_POST);exit;						
						$DB->AddCondFS("user_id","=", $Auth->user_id);
						$DB->AddCondFS("id","=", $_POST["sel_file"]);
						$DB->AddValue("folder_id", $_POST["sel_folder"]);
						$DB->Update();
					}
					
					if(isset($_GET["del_attach"])) {
					$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("id", "=", $_GET["del_attach"]);
						$DB->AddField("file_id");
						$DB->AddField("spec_id");
						$res = $DB->Select(1);
						while($row = $DB->FetchArray()){
							$Engine->LogAction($this->module_id, "file", $row["file_id"]/*.":".$row["spec_id"]*/, "del_attach");
						}
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("id", "=", $_GET["del_attach"]);
						$DB->Delete();
						CF::Redirect($rederect);
					}
					
					/*if (isset($_GET["del_subj"]) && isset($_GET["del_file"])) {
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("file_id", "=", $_GET["del_file"]);
						$DB->AddCondFS("subject_id", "=", $_GET["del_subj"]);
						$DB->AddCondFO("spec_id", " is null ");
						$DB->Delete();
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("file_id", "=", $_GET["del_file"]);
						$DB->AddCondFS("subject_id", "=", $_GET["del_subj"]);
						$DB->AddValue("subject_id", NULL);
						$DB->Update();
						//CF::Redirect("/office/dobavit-fajl/");
						CF::Redirect($rederect);
					}
					
					if (isset($_GET["del_spec"]) && isset($_GET["del_file"])) {
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("file_id", "=", $_GET["del_file"]);
						$DB->AddCondFS("spec_id", "=", $_GET["del_spec"]);
						$DB->AddCondFO("subject_id", " is null ");
						if ($_GET["del_view"]) $DB->AddCondFS("view_id", "=", $_GET["del_view"]);
						$DB->Delete();
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("file_id", "=", $_GET["del_file"]);
						$DB->AddCondFS("spec_id", "=", $_GET["del_spec"]);
						if ($_GET["del_view"]) $DB->AddCondFS("view_id", "=", $_GET["del_view"]);
						$DB->AddValue("spec_id", NULL);
						$DB->AddValue("spec_type", NULL);
						$DB->Update();
						CF::Redirect($rederect);
					}*/
					
					if (isset($_GET["del_folder"]) && isset($_GET["del_file"])) {
						$DB->SetTable("nsau_files");
						$DB->AddCondFS("id", "=", $_GET["del_file"]);
						$DB->AddCondFS("user_id", "=", $Auth->user_id);
						$DB->AddCondFS("folder_id", "=", $_GET["del_folder"]);
						$DB->AddValue("folder_id", NULL);
						$DB->Update();
						CF::Redirect($rederect);
					}
					
					
					if (isset($_GET["delete_file"])) {
						$DB->SetTable("nsau_files");
						$DB->AddCondFS("id", "=", $_GET["delete_file"]);
						$res = $DB->Select();
						if($row = $DB->FetchAssoc($res)) {
							if($row['is_html'] == 1) {
								function removeDirRec($dir) {
									if ($objs = glob($dir."/*")) {
										foreach($objs as $obj) {
											is_dir($obj) ? removeDirRec($obj) : unlink($obj);
										}
									}
									return rmdir($dir);
								}						
							
								$str = $_SERVER["DOCUMENT_ROOT"]."/htmldocs/".$row["id"]."/";
								if (removeDirRec($str) || !file_exists($str)) {
									$this->output["ajax_del_file"] = 1;
								} else {
									$this->output["ajax_del_file"] = 0;
								}
							} else {
								$str = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["id"].".".$row["filename"];
								if (unlink($str) || !file_exists($str)) {
									$this->output["ajax_del_file"] = 1;
								} else {
									$this->output["ajax_del_file"] = 0;
								}
							}
							if(isset($this->output["ajax_del_file"]) && $this->output["ajax_del_file"] == 1) {
								$DB->SetTable("nsau_files");
								$DB->AddCondFS("id", "=", $_REQUEST['data']["f_id"]);
								$DB->Delete();
								$Engine->LogAction($this->module_id, "file", $_REQUEST['data']["f_id"], "delete");
								
								$DB->SetTable("nsau_files_subj");
								$DB->AddCondFS("file_id", "=", $_REQUEST['data']["f_id"]);
								$DB->Delete();
							}
						}	
						//die(print_r($_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["id"].".".$row["filename"]));
						/*$str = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["id"].".".$row["filename"];
						if (unlink($str)) {
							$DB->SetTable("nsau_files");
							$DB->AddCondFS("id", "=", $_GET["delete_file"]);
							$DB->Delete();
							$Engine->LogAction($this->module_id, "file", $_GET["delete_file"], "delete");
							
							$DB->SetTable("nsau_files_subj");
							$DB->AddCondFS("file_id", "=", $_GET["delete_file"]);
							$DB->Delete();
						}*/
						
						CF::Redirect($rederect);
					}
					
					if ($Engine->OperationAllowed($this->module_id, "files.upload", 0, $Auth->usergroup_id))
						$this->output["allow_upload"] = 1;
					else
						$this->output["allow_upload"] = 0;
					
					$this->files_list();
				}
				break;
				
				case "attach_file": {
					$this->output["scripts_mode"] = $this->output["mode"] = "attach_file";
					$this->output["plugins"][] = "jquery.ui.autocomplete.min";				
					
					$this->attach_file();
				}
				break;
			
				case "create_folder": {
					$this->output["scripts_mode"] = $this->output["mode"] = "create_folder";				
					
					$this->create_folder();
				}
				break;
				
				case "get_file": {
					$uri_array = explode("/", $module_uri);
					
					$DB->SetTable("nsau_files");
					$DB->AddCondFS("id", "=", $uri_array[0]);
					$res = $DB->Select();
					$row = $DB->FetchAssoc($res);
					
					$DB->SetTable("nsau_files_subj");
					$DB->AddCondFS("file_id", "=", $uri_array[0]);
					$res = $DB->Select();
					
					$allowed_ips = explode(';', $ini_settings['allowed_ips']);
					if  (substr($_SERVER["REMOTE_ADDR"], 0, 7) == "192.168" || in_array($_SERVER["REMOTE_ADDR"], $allowed_ips) ||
						 $Engine->OperationAllowed($this->module_id, "files.download", $uri_array[0], $Auth->usergroup_id) ) 
						$this->output["allow_download"] = 1;
					else
						$this->output["allow_download"] = 0;
					
					if (isset($_GET["get"])) {
						$this->output["scripts_mode"] = $this->output["mode"] = "get_file";
						
		//				if (isset($_SESSION["user_id"]))
							$this->get_file($uri_array[0]);
					}
					else {
						$this->output["scripts_mode"] = $this->output["mode"] = "link_to_file";
						
						$this->output["file_id"] = $uri_array[0];
						if (!$uri_array[0]) CF::Redirect("/");
						if (intval($uri_array[0])!=0) {
						$DB->SetTable("nsau_files");
						$DB->AddCondFS("id", "=", $uri_array[0]);
						$res = $DB->Select();
						$row = $this->output["file"] = $DB->FetchAssoc($res);
						$this->output["file"]["is_allowed"] = $this->output["allow_download"];
						if ($this->output["file"]["is_allowed"] && !$row["is_html"]) {
							CF::Redirect("/file/".$uri_array[0]."?get=".md5(time()));
							exit;
						} 
						}
						{
						if (($row["is_html"] && $this->output["allow_download"]) || (intval($uri_array[0])==0)) {  // также здесь обрабатываем обращения к папкам, которые были добавлены в htmldocs напрямую, напр. /magistratura
							//CF::Redirect('/htmldocs/'.$uri_array[0].'/', 'http://nsau.edu.ru/file/');
							$host = $_SERVER['HTTP_HOST']; 
							
							$res = curl_init('http://'.$host.'/htmldocs/'.$uri_array[0].'/'.$uri_array[1]);
							
							curl_setopt($ch, CURLOPT_POST      ,1);
 							curl_setopt($ch, CURLOPT_POSTFIELDS, 'not_allowed=1');
 							curl_setopt($res, CURLOPT_HEADER, 0);
							curl_setopt($res, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($res, CURLOPT_REFERER, 'http://'.$host.'/file/'.$uri_array[0].'/');
							$resp = curl_exec($res);
							curl_close($res);
							
							preg_match_all('/src="(.*?)"/is', $resp, $matches); 
							foreach ($matches[1] as $ind=>$match) {
                $patterns[$ind] = '/src="(?!http\:\/\/)(.*?)"/';
								$replacements[$ind] = 'src="http://'.$host.'/htmldocs/'.$uri_array[0].'/'.$match.'"';
							}
              
				              preg_match_all('/<link(?:[^>]*?)href="(.*?)"(?|[^>]*?)>/is', $resp, $matches);
				              foreach ($matches[1] as $ind=>$match) {
				                $patterns[] = '/<link(?:[^>]*?)href="(.*?)"(?|[^>]*?)>/is';
				                $replacements[] = '<link rel="stylesheet" href="http://'.$host.'/htmldocs/'.$uri_array[0].'/'.$match.'?rand='.rand(0, 999999).time().'" type="text/css" />';
				              }

				              header("Cache-Control: no-cache, must-revalidate"); 
				              header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
							
							if ($resp = preg_replace($patterns, $replacements, $resp, 1))
								echo $resp;
							else  if (!file_exists($_SERVER["DOCUMENT_ROOT"].'/htmldocs/'.$uri_array[0].'/')) {
								$Engine->HTTP404();		
							} else $Engine->HTTP403();
							exit;
						}
						
						if ($Auth->usergroup_id == 1 || $row["user_id"] == $_SESSION["user_id"])
							$this->output["allow_edit"] = 1;
						else
							$this->output["allow_edit"] = 0;
						
						$Engine->AddFootstep ("", $row["name"]);
						
						if ($Auth->usergroup_id == 1) {
							$DB->FreeRes();
							/*$DB->SetTable("auth_users", "au");
							$DB->AddTable("nsau_people", "p");
							$DB->AddField("au.id", "uid");
							$DB->AddField("au.displayed_name", "uname");
							$DB->AddField("p.id", "pid");
							$DB->AddCondFF("au.id", "=", "p.user_id");
							$DB->AddCondFS("au.id", "=", $row["user_id"]);*/

							if ($res = $DB->Exec("SELECT  au.displayed_name as uname, p.id as pid FROM auth_users AS au LEFT JOIN nsau_people AS p on au.id=p.user_id where au.id=".$row["user_id"]) ) {
								$row2 = $DB->FetchAssoc($res);
								if ($row2 && !empty($row2)) $this->output["user_author"] = array("href"=>(is_null($row2["pid"]) ? "" : "/people/".$row2["pid"]), "name"=>$row2["uname"]);

							}
						}
						
						$filepath = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$uri_array[0].".".$row["filename"];
						if(file_exists($filepath) || $row["is_html"]) {
							$filesize = filesize($filepath);
							if ($filesize > 999 && $filesize < 1048576) {
								$filesize /= 1024;
								if ($filesize > 100)
									$filesize = substr($filesize, 0, 3);
								else
									$filesize = substr($filesize, 0, 4);
								$fileend = "кб";
							}
							elseif ($filesize >= 1048576) {
								$filesize /= 1048576;
								$filesize = substr($filesize, 0, 4);
								$fileend = "Мб";
							}
							else
								$fileend = "байт";
							$this->output["filesize"] = $filesize." ".$fileend;
						} else {
							$Engine->HTTP404();	
						}
						}						
					}
				}
				break;
				
				case "get_files_by_params": {
					$this->output = array();
					if (isset($_REQUEST['data']) && isset($_REQUEST['data']['type']))
					{
						$files_params = array('subject_id', 'faculty_id', 'umkd_id', 'spec_id', 'spec_type');
						foreach($_REQUEST['data'] as $key=>$value)
							if (in_array($key, $files_params))
								$data[$key] = $value;
						$this->output['files'] = $this->get_files_by_params($_REQUEST['data']['type'], $data);			
					}
				}
				break;

				case "get_upload_progress": {
					$this->output["scripts_mode"] = $this->output["mode"] = "get_upload_progress";
					/*if(function_exists("uploadprogress_get_info")) {
						echo '222';
					}*///print_r($_SESSION);exit;
					if(function_exists("uploadprogress_get_info")) {
						if (isset($_POST['data']['upfile_id'])) {
							$status = uploadprogress_get_info($_POST['data']['upfile_id']);
						    if ($status) {
								$this->output["get_upload_progress"]['process_uploaded'] = round($status['bytes_uploaded']/$status['bytes_total']*100);
								$this->output["get_upload_progress"]['est_sec'] = $status['est_sec'];
						    }
						}
					}
					else {
						if (isset($_POST['data']['upfile_id'])) {
							$key = ini_get("session.upload_progress.prefix") . $_POST['data']['upfile_id'];echo $key;print_r($_SESSION); ///$_POST[ini_get("session.upload_progress.name")];
							$status = $_SESSION[$key]; print_r($status); 
						}
					}
				}
				break;
				
				/*case "logout":
					if ($Auth->logged_in)
					{
						$Auth->Logout();
						$Engine->ResetAdminUser();
						$this->output["messages"]["good"][] = "Пользователь завершил сеанс работы.";
						$this->output["status"] = true;
					}

					else
					{
						$this->output["status"] = false;
					}

					break;*/
				case "download_people_list": {
					if (isset($_GET["file_id"])) {
						$this->download_people_list();
					}
				}
				break;
			
				case "reports": {
					$this->output["scripts_mode"] = "reports";
					$this->form_report();
				}
				break;
			
				case "files_search": {
					$this->output["mode"] = "files_search";
					$this->output["scripts_mode"][] = "show_attach_file";
					
					$spec_type_code = array(
							'secondary' => 51,
							'bachelor' => 62,
							'higher' => 65,
							'magistracy' => 68
					);
					$spec_type_name = array(
							'secondary' => 'Среднее специальное',
							'bachelor' => 'Бакалавриат',
							'higher' => 'Специалитет',
							'magistracy' => 'Магистратура'
					);
					
					if ($Engine->OperationAllowed($this->module_id, "files.moderation", -1, $Auth->usergroup_id)) {
						$this->output["scripts_mode"][] = "ajax_files_moderation";
						$this->output['plugins'][] = 'jquery.ui.draggable';
						$this->output['plugins'][] = 'jquery.ui.position.min';
						$this->output['plugins'][] = 'jquery.ui.dialog';
						$this->output['plugins'][] = 'jquery.ui.autocomplete.min';
						$this->output['plugins'][] = 'jquery.editable-select';
						$this->output["allow_files_moderation"] = 1;
						//$this->moderation_files_list();
						if(isset($_POST['referenc_file']) && $_POST['referenc_file'] == 1) {
							$this->referenc_file();
						}
						
						if(isset($_POST['file_access'], $_POST['file_attach_id']) && $this->output["allow_files_moderation"]) {
							$DB->SetTable("nsau_files_subj");
							$DB->AddCondFS("id", "=", $_POST['file_attach_id']);
							$DB->AddValue("approved", $_POST["file_access"]);
							if(!empty($_POST["file_attach_comment"])) {
								$DB->AddValue("comment", $_POST["file_attach_comment"]);
							}
							if($DB->Update()) {
								$Engine->LogAction($this->module_id, "file_approved", $_POST['file_attach_id'], "update");
								CF::Redirect($rederect);
							} else {
								$this->output["messages"]["bad"][] = "Ошибка обновления записи.";
							}
						}
					} else {
						$this->output["allow_files_moderation"] = 0;
					}
					
					$DB->SetTable("nsau_subjects", "subj");
					$DB->AddTable("nsau_departments", "dep");
					$DB->AddCondFF("subj.department_id","=", "dep.id");		
					$DB->AddField("subj.id", "sid");
					$DB->AddField("subj.name", "sname");
					$DB->AddField("dep.id", "did");
					$DB->AddField("dep.name", "dname");
					$DB->AddOrder("dep.name");
					$DB->AddOrder("subj.name");
					//echo $DB->SelectQuery();
					$res = $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["subjects_list"][$row["did"]]["name"] = $row["dname"];
						$this->output["subjects_list"][$row["did"]]["subj"][] = $row;			
					}
					
					$DB->SetTable("nsau_subjects", "subj");
					
					$DB->AddField("subj.id", "sid");
					$DB->AddField("subj.name", "sname");
					$DB->AddCondFS("subj.department_id", "=", 0); 
					//$DB->AddField("dep.id", "did");
					//$DB->AddField("dep.name", "dname");
					//$DB->AddOrder("dep.name");
					//$DB->AddOrder("subj.name");
					//echo $DB->SelectQuery();
					$res = $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["subjects_list"][0]["name"] = "Дисциплины, не привязанные к кафедре";
						$this->output["subjects_list"][0]["subj"][] = $row;
					}
					
					$DB->SetTable("nsau_specialities", "spec");
					$DB->AddField("spec.id", "spec_id");
					$DB->AddField("spec.code", "code");
					$DB->AddField("spec.name", "spname");
					$DB->AddOrder("spec.name");
					//echo $DB->SelectQuery();
					$res = $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["spec_list"][$row['spec_id']]['name'] = $row['spname'];
						$this->output["spec_list"][$row['spec_id']]['code'] = $row['code'];	
						$DB->SetTable("nsau_spec_type");
						$DB->AddCondFS("spec_id","=", $row['spec_id']);
						$DB->AddCondFS("code","=", $row['code']);
						$DB->AddOrder("type");
						$res1 = $DB->Select();
						while($row1 = $DB->FetchAssoc($res1)) {
							$this->output["spec_list"][$row['spec_id']]['type'][$row1['id']]['name'] = $spec_type_name[$row1['type']];
							$this->output["spec_list"][$row['spec_id']]['type'][$row1['id']]['code'] = $spec_type_code[$row1['type']];
						}
						$DB->SetTable("nsau_profiles");
						$DB->AddCondFS("spec_id","=", $row['spec_id']);
						$res2 = $DB->Select();
						while($row2 = $DB->FetchAssoc($res2)) {
							$this->output["spec_list"][$row['spec_id']]['profile'][$row2['id']]['name'] = $row2['name'];
							$this->output["spec_list"][$row['spec_id']]['profile'][$row2['id']]['id'] = $row2['id'];
						}		
					}
					
					$DB->SetTable("nsau_file_view");
					$DB->AddOrder("pos");
					$res = $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["files_view"][] = $row;
					}
					
					$DB->SetTable("nsau_files", "f");
					$DB->AddTable("nsau_files_subj", "fs");
					$DB->AddCondFF("fs.file_id", "=", "f.id");
					$DB->AddField("f.year", "year");
					$DB->AddOrder("f.year", true);
					$res = $DB->Select(null, null, true, true, true);
					while ($row = $DB->FetchAssoc($res)) {
						if(!empty($row["year"])) {
							$this->output["years"][] = $row["year"];
						}
					}
					
					$DB->SetTable("auth_users", "u");
					$DB->AddTable("nsau_files", "f");
					$DB->AddTable("nsau_files_subj", "s");
					$DB->AddField("u.id");
					$DB->AddField("u.displayed_name", "user_name"); 					
					$DB->AddCondFF("u.id", "=", "f.user_id");
					$DB->AddCondFS("u.is_active", "=", 1);
					$DB->AddCondFF("s.file_id", "=", "f.id"); 
					$DB->AddOrder("u.displayed_name"); 
					//echo $DB->SelectQuery(null,null,true); 
					
					$res = $DB->Select(null, null, true, true, true);
					
					while ($row = $DB->FetchAssoc($res)) {
						if(!empty($row["id"])) {
							$DB->SetTable("nsau_people");
							$DB->AddField("id");
							$DB->AddField("user_id");
							$DB->AddField("last_name");
							$DB->AddField("name");
							$DB->AddField("patronymic");
							$DB->AddCondFS("user_id", "=", $row["id"]);
							$res_people = $DB->Select();
							if($row_people = $DB->FetchAssoc($res_people)) {
								$row["user_name"] = $row_people["last_name"]." ".$row_people["name"]." ".$row_people["patronymic"];
							}
							
							$this->output["users"][] = $row;
						}
					}
										
					if(!empty($_REQUEST['file_name']) || !empty($_REQUEST['file_spec']) || !empty($_REQUEST['file_view']) || !empty($_REQUEST['file_author']) || !empty($_REQUEST['file_subject']) || !empty($_REQUEST['file_year']) || !empty($_REQUEST["file_user"]) || !empty($_REQUEST["file_status"])) {
						$this->moderation_files_list();
						//$this->output["show_unmoderated_files"] = true; 
					} else { 
						$this->moderation_files_list(true);
						$this->output["show_unmoderated_files"] = true; 
					}
				}
				break;
			
				case "ajax_files_moderation": {
					if ($Engine->OperationAllowed($this->module_id, "files.moderation", -1, $Auth->usergroup_id)) {
						$this->output["allow_files_moderation"] = 1;
					} else {
						$this->output["allow_files_moderation"] = 0;
					}
					if(isset($_REQUEST['data']['file_access'], $_REQUEST['data']['file_attach_id']) && $this->output["allow_files_moderation"]) {
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("id", "=", $_REQUEST['data']['file_attach_id']);
						$DB->AddValue("approved", $_REQUEST['data']["file_access"]);
						$DB->AddValue("comment", iconv("UTF-8", "windows-1251",$_REQUEST['data']["file_attach_comment"]));
						if($DB->Update()) {
							$this->output["ajax_files_moderation"] = "completed";
							if($_REQUEST['data']["file_access"] == 1)
								$Engine->LogAction($this->module_id, "file_approved", $_REQUEST['data']['file_attach_id'], "approve");
							elseif($_REQUEST['data']["file_access"] == -1) $Engine->LogAction($this->module_id, "file_approved", $_REQUEST['data']['file_attach_id'], "decline");
						} else {
							$this->output["ajax_files_moderation"] = "error";
						}
					}
				}
				break;
				
				case "ajax_teachers": {
					$this->output["user_id"] = $Auth->user_id; 
					$DB->SetTable("nsau_files"); 
					$DB->AddCondFS("id", "=", $_REQUEST["data"]["f_id"]);
					$res = $DB->Select(1);
					if($row = $DB->FetchAssoc()) {
						$this->output["file"] = $row; 
					} 
					
					$array = $Engine->GetPrivileges($this->module_id, 'files.transfer', null, $Auth->usergroup_id);
					$dep_arr = null;
					foreach($array[$this->module_id]['files.transfer'] as $entry => $allowed) {
						if($Engine->OperationAllowed($this->module_id, 'files.transfer', $entry, $Auth->usergroup_id)) {
							if($entry == -1) {
								$dep_arr = -1;
								continue;
							} else {
								$dep_arr[] = $entry;
							}
						}
					}
					if(!is_null($dep_arr)) {
						$DB->SetTable("nsau_teachers", "t");
						$DB->AddTable("nsau_people", "p");
						$DB->AddTable("nsau_departments", "d");
						$DB->AddCondFF("t.people_id","=", "p.id");
						$DB->AddCondFF("d.id","=", "t.department_id");
						$DB->AddCondFS("p.user_id","!=", 0);
						$DB->AddField("t.department_id", "d_id");
						$DB->AddField("d.name", "d_name");
						$DB->AddField("p.user_id", "user_id");
						$DB->AddField("p.last_name", "last_name");
						$DB->AddField("p.name", "name");
						$DB->AddField("p.patronymic", "patronymic");
						if($dep_arr != -1) {
							foreach($dep_arr as $dep_id) {
								$DB->AddCondFS("t.department_id","=", $dep_id);
							}
						}
						
						$DB->AddOrder('d.name');
						$DB->AddOrder('p.last_name');
						$res = $DB->Select();
						while($row = $DB->FetchAssoc($res)) {
							$this->output["teacher_transfer"][$row['d_id']]['dname'] = $row['d_name'];
							$this->output["teacher_transfer"][$row['d_id']]['teacher'][$row['user_id']] = $row['last_name'].' '.$row['name'].' '.$row['patronymic'];
						}
					}
				}
			}
		} else {
			$this->output["messages"]["bad"][] = "Доступ запрещён.";
		}
	}
	
	function cp1251_to_utf8 ($txt)  {
		$in_arr = array (
			chr(208), chr(192), chr(193), chr(194),
			chr(195), chr(196), chr(197), chr(168),
			chr(198), chr(199), chr(200), chr(201),
			chr(202), chr(203), chr(204), chr(205),
			chr(206), chr(207), chr(209), chr(210),
			chr(211), chr(212), chr(213), chr(214),
			chr(215), chr(216), chr(217), chr(218),
			chr(219), chr(220), chr(221), chr(222),
			chr(223), chr(224), chr(225), chr(226),
			chr(227), chr(228), chr(229), chr(184),
			chr(230), chr(231), chr(232), chr(233),
			chr(234), chr(235), chr(236), chr(237),
			chr(238), chr(239), chr(240), chr(241),
			chr(242), chr(243), chr(244), chr(245),
			chr(246), chr(247), chr(248), chr(249),
			chr(250), chr(251), chr(252), chr(253),
			chr(254), chr(255)
		);  
	 
		$out_arr = array (
			chr(208).chr(160), chr(208).chr(144), chr(208).chr(145),
			chr(208).chr(146), chr(208).chr(147), chr(208).chr(148),
			chr(208).chr(149), chr(208).chr(129), chr(208).chr(150),
			chr(208).chr(151), chr(208).chr(152), chr(208).chr(153),
			chr(208).chr(154), chr(208).chr(155), chr(208).chr(156),
			chr(208).chr(157), chr(208).chr(158), chr(208).chr(159),
			chr(208).chr(161), chr(208).chr(162), chr(208).chr(163),
			chr(208).chr(164), chr(208).chr(165), chr(208).chr(166),
			chr(208).chr(167), chr(208).chr(168), chr(208).chr(169),
			chr(208).chr(170), chr(208).chr(171), chr(208).chr(172),
			chr(208).chr(173), chr(208).chr(174), chr(208).chr(175),
			chr(208).chr(176), chr(208).chr(177), chr(208).chr(178),
			chr(208).chr(179), chr(208).chr(180), chr(208).chr(181),
			chr(209).chr(145), chr(208).chr(182), chr(208).chr(183),
			chr(208).chr(184), chr(208).chr(185), chr(208).chr(186),
			chr(208).chr(187), chr(208).chr(188), chr(208).chr(189),
			chr(208).chr(190), chr(208).chr(191), chr(209).chr(128),
			chr(209).chr(129), chr(209).chr(130), chr(209).chr(131),
			chr(209).chr(132), chr(209).chr(133), chr(209).chr(134),
			chr(209).chr(135), chr(209).chr(136), chr(209).chr(137),
			chr(209).chr(138), chr(209).chr(139), chr(209).chr(140),
			chr(209).chr(141), chr(209).chr(142), chr(209).chr(143)
		);  
	 
		$txt = str_replace($out_arr,$in_arr,$txt);
		return $txt;
	}
	
	function referenc_file() {
		global $DB, $Engine;
		$reder = explode("?", $Engine->unqueried_uri);
		$rederect = $reder[0];
		//$rederect = $Engine->uri;
		if(isset($_POST["sel_file"], $_POST["sel_view"], $_POST["sel_subject"], $_POST["spec_item"]) && !empty($_POST["sel_file"]) && !empty($_POST["sel_subject"]) && !empty($_POST["sel_view"]) && (is_array($_POST["spec_item"]) && count($_POST["spec_item"]))) {
			$is_error = false;			
			$spec_type_code = array(
				'secondary' => 51,
				'bachelor' => 62,
				'higher' => 65,
				'magistracy' => 68
			);
			foreach($_POST["spec_item"] as $spec_id => $code) {
				$spec_type = null;
				$DB->SetTable("nsau_spec_type");
				$DB->AddCondFS("id","=", $_POST["sel_spec_type"][$spec_id]);
				$res1 = $DB->Select();
				if($row1 = $DB->FetchAssoc($res1)) {
					$spec_type = $row1['type_code'];
				}
				
				
				$DB->SetTable("nsau_files_subj");
				$DB->AddValue("file_id", $_POST["sel_file"]);
				$DB->AddValue("subject_id", $_POST["sel_subject"]);
				$DB->AddValue("view_id", $_POST["sel_view"]);
				
				$DB->AddValue("spec_id", $spec_id);
				$DB->AddValue("spec_code", $code);
				if(!isset($_POST["sel_spec_type"][$spec_id]) || empty($_POST["sel_spec_type"][$spec_id])) {
					$_POST["sel_spec_type"][$spec_id] = 65;
				}
				if(!is_null($spec_type)) {
					$DB->AddValue("spec_type", $spec_type);
				}
				$DB->AddValue("spec_type_id", $_POST["sel_spec_type"][$spec_id]);
				$DB->AddValue("education", $_POST["sel_education"][$spec_id]);
				if(/*$_POST["sel_education"][$spec_id] == "Заочная"*/!empty($_POST["sel_semester"][$spec_id])) {
					$DB->AddValue("semester", $_POST["sel_semester"][$spec_id]);
				}
				
				if(isset($_POST["sel_profile"][$spec_id])) {
					$DB->AddValue("profile_id", $_POST["sel_profile"][$spec_id]);
				}
				
				if (!$DB->Insert()) {
					$is_error = true;
					$this->output["messages"]["bad"][] = "Ошибка добавления привязки. Возможно такая привязка уже существует.";
				}
				else {
					//$Engine->LogAction($this->module_id, "file", $_POST["sel_file"]/*.":".$spec_id*/, "add_attach");
					$attach_id = $DB->LastInsertID();
					$Engine->LogAction($this->module_id, "file_approved", $attach_id, "attach");
				}
			}
			$Engine->LogAction($this->module_id, "file", $_POST["sel_file"]/*.":".$spec_id*/, "add_attach");
			if(!$is_error) {
				$data_array = array(
						"file_name" => $_REQUEST['file_name'],
						"file_author" => $_REQUEST['file_author'],
						"file_spec" => $_REQUEST['file_spec'],
						"file_subject" =>  $_REQUEST['file_subject'],
						"file_view" =>  $_REQUEST['file_view'],
						"file_year" =>  $_REQUEST['file_year'],
						"file_user" =>  $_REQUEST['file_user'],
						"file_status" =>  $_REQUEST['file_status'],
						"file_education" => $_REQUEST['file_education'],
						"page" => $_REQUEST["page"]
				);
				foreach($data_array as $key => $value){
					if(empty($value) /*&& $key != "file_status"*/) unset($data_array[$key]);
				}
				if(!empty($data_array)) $rederect .= "?".http_build_query($data_array);
				CF::Redirect($rederect);
			}
		} else {
			$this->output["messages"]["bad"][] = "Не все поля заполнены.";
		}
		/*if(isset($_POST["sel_file"], $_POST["sel_spec"], $_POST["sel_spec_type"], $_POST["sel_education"], $_POST["sel_subject"], $_POST["sel_view"])
		   && !empty($_POST["sel_file"]) && !empty($_POST["sel_spec"]) && !empty($_POST["sel_spec_type"]) && !empty($_POST["sel_education"]) && !empty($_POST["sel_subject"]) && !empty($_POST["sel_view"])) {
			
			$DB->SetTable("nsau_files_subj");
			$DB->AddValue("file_id", $_POST["sel_file"]);
			$DB->AddValue("subject_id", $_POST["sel_subject"]);
			$DB->AddValue("spec_id", $_POST["sel_spec"]);
			$DB->AddValue("spec_type", $_POST["sel_spec_type"]);
			$DB->AddValue("view_id", $_POST["sel_view"]);
			$DB->AddValue("education", $_POST["sel_education"]);
			if (!$DB->Insert()) $this->output["messages"]["bad"][] = "Указан незарегистрированный в базе данных предмет";
		} else {
			$this->output["messages"]["bad"][] = "Не все поля заполнены.";
		}*/
	}
	
	function download_people_list($file_id = null) {
		global $DB, $Engine;
		/*$DB->SetTable("engine_actions_log");
		$DB->AddField("username");
		$DB->AddField("ip");
		$DB->AddCondFS("entry_type", "=", "file");
		$DB->AddCondFS("action", "=", "download");*/
		/*$DB->AddCondFS("username", "!=", "");
		$DB->AddGrouping("username");*/
		if($file_id == null) {
			//$DB->AddCondFS("entry_id", "=", $_GET["file_id"]);
			//echo $DB->SelectQuery(null, null, true);
			//$res = $DB->Select(null, null, true, true, true);
			$res = $DB->Exec("SELECT DISTINCT `username`, `ip` FROM `engine_actions_log`
								WHERE `entry_type` = 'file' AND `action` = 'download' AND `entry_id` = '".$_GET["file_id"]."' AND `username` != '' GROUP BY `username`
								UNION
								SELECT DISTINCT `username`, `ip` FROM `engine_actions_log`
								WHERE `entry_type` = 'file' AND `action` = 'download' AND `entry_id` = '".$_GET["file_id"]."' AND `username` = '';");
			while($row = $DB->FetchAssoc($res)) {
				$this->output["people_list"][] = array(
					"username" => $row["username"],
					"ip" => $row["ip"]
					);
			}
			$DB->SetTable("nsau_files");
			$DB->AddField("name");
			$DB->AddCondFS("id", "=", $_GET["file_id"]);
			$res = $DB->Select();
			$row = $DB->FetchAssoc($res);
			$this->output["file_name"] = $row["name"];
			
			/*$DB->SetTable("engine_actions_log");
			$DB->AddField("user_id");
			$DB->AddCondFS("entry_id", "=", $_GET["file_id"]);
			$DB->AddCondFS("entry_type", "=", "file");
			$DB->AddCondFS("action", "=", "create");
			$res = $DB->Select(null, null, true, true, true);
			$row = $DB->FetchAssoc($res);
			if($row["user_id"] == $_SESSION["user_id"])
				$this->output["alien"] = true;*/
			
			return false;
		} else {
			$res1 = $DB->Exec("SELECT SUM(`count1`) AS 'xcount1' FROM (SELECT DISTINCT `username`, `ip`,count(DISTINCT `username`) AS 'count1' FROM `engine_actions_log`
								WHERE `entry_type` = 'file' AND `action` = 'download' AND `entry_id` = '".$file_id."' AND `username` != '' GROUP BY `username`)
							AS `count1`");
			
			$res2 = $DB->Exec("SELECT count(DISTINCT `ip`) AS 'count2' FROM `engine_actions_log`
								WHERE `entry_type` = 'file' AND `action` = 'download' AND `entry_id` = '".$file_id."' AND `username` = ''");
			$row2 = $DB->FetchAssoc($res2);
			if($row2["count2"] != 0) {
				$res2 = $DB->Exec("SELECT SUM(`count2`) AS 'xcount2' FROM (SELECT DISTINCT `username`, `ip`,count(DISTINCT `ip`) AS 'count2' FROM `engine_actions_log`
									WHERE `entry_type` = 'file' AND `action` = 'download' AND `entry_id` = '".$file_id."' AND `username` = '')
								AS `count2`");
			} else {
				$res2 = null;
			}
			
			//$DB->AddExp("count(DISTINCT `username` , `ip`)", "xcount");
			//echo $DB->SelectQuery(null, null, true);
			//$res = $DB->Select(null, null, true, true);
			$row1 = $DB->FetchAssoc($res1);
			if($res2 != null) {
				$row2 = $DB->FetchAssoc($res2);
			} else {
				$row2["xcount2"] = 0;
			}
			$result = $row1["xcount1"] + $row2["xcount2"];
			return $result;
		}
		
	}
	
	function files_list($file_id = null)
	{
		global $DB, $Auth, $Engine;
		$DB->SetTable("nsau_files");
		if(!is_null($file_id)) {
			$DB->AddCondFS("id", "=", $file_id);			
		}
		$DB->AddField("id");
		$DB->AddField("name");
		$DB->AddField("filename");
		$DB->AddField("is_html");
		$DB->AddField("descr");
		$DB->AddField("author");
		$DB->AddField("year");
		$DB->AddField("volume");
		$DB->AddField("edition");
		$DB->AddField("place");
		$DB->AddField("down_count");
		$DB->AddField("folder_id");
		$DB->AddField("user_id");
		if (isset($_SESSION["user_id"]))
			$user_id = $_SESSION["user_id"];
		else
			$user_id = -1;
		$DB->AddCondFS("user_id", "=", $user_id);
		if($_SERVER["SERVER_ADDR"] == "127.0.0.1") $res = $DB->Select(5);
		else 
		$res = $DB->Select();
		
		while($row = $DB->FetchAssoc($res)) {
			if(empty($row["descr"])) {
				$row["descr"] = $row["name"];
			}
			$row["name"] .= ".".$row["filename"];
			$row["download_count"] = $row["down_count"];//$this->download_people_list($row["id"]);
			//$row["is_allowed"] = $Engine->OperationAllowed($this->module_id, 'files.download', $row["id"], 0);
			$row["is_allowed"] = $Engine->GetPrivileges($this->module_id, 'files.download', $row["id"], 0);
			$row["is_allowed"] = (isset($row["is_allowed"][$this->module_id]['files.download'][$row["id"]]) && $row["is_allowed"][$this->module_id]['files.download'][$row["id"]][0] == 1) ? 1 : 0;
			$DB->SetTable("nsau_files_subj");
			$DB->AddField("id");
			$DB->AddField("subject_id");
			$DB->AddField("spec_id");
			$DB->AddField("spec_code");
			$DB->AddField("spec_type");
			$DB->AddField("view_id");
			$DB->AddField("profile_id");
			$DB->AddField("education");
			$DB->AddField("semester");
			$DB->AddField("approved");
			$DB->AddField("comment");
			$DB->AddCondFS("file_id", "=", $row["id"]);
			$DB->AddCondFO("subject_id", " is not null ");
			$res_attach = $DB->Select(null, null, true, true, true);
			
			while($row_attach = $DB->FetchAssoc($res_attach)) {
				$DB->SetTable("nsau_subjects");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["subject_id"]);
				$res_sub = $DB->Select();
				if($row_sub = $DB->FetchAssoc($res_sub)) {
					$row["attach_file_list"][$row_attach['id']]['subject_name'] = $row_sub["name"];
				}
				$row["attach_file_list"][$row_attach['id']]['subject_id'] = $row_attach["subject_id"];
				$DB->SetTable("nsau_specialities");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["spec_id"]);
				$row_sub = $DB->Select();
				if($row_sub = $DB->FetchAssoc($row_sub)) {
					$row["attach_file_list"][$row_attach['id']]['spec_name'] = $row_attach["spec_code"].'.'.$row_attach["spec_type"].' - '.$row_sub["name"];
				}
				$row["attach_file_list"][$row_attach['id']]['spec_code'] = $row_attach["spec_code"];
				$row["attach_file_list"][$row_attach['id']]['spec_id'] = $row_attach["spec_id"];
				$DB->SetTable("nsau_file_view");
				$DB->AddField("view_name");
				$DB->AddCondFS("id", "=", $row_attach["view_id"]);
				$row_view = $DB->Select();
				if($row_view = $DB->FetchAssoc($row_view)) {
					$row["attach_file_list"][$row_attach['id']]['spec_view'] = $row_view["view_name"];
				}
				$DB->SetTable("nsau_profiles");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["profile_id"]);
				$row_profile = $DB->Select();
				if($row_profile = $DB->FetchAssoc($row_profile)) {
					$row["attach_file_list"][$row_attach['id']]['profile'] = $row_profile['name'];
				}
				$row["attach_file_list"][$row_attach['id']]['spec_education'] = $row_attach["education"];
				$row["attach_file_list"][$row_attach['id']]['semester'] = $row_attach["semester"];
				$row["attach_file_list"][$row_attach['id']]['approved'] = $row_attach["approved"];
				$row["attach_file_list"][$row_attach['id']]['comment'] = $row_attach["comment"];
			}			
					
			/*$DB->SetTable("nsau_files_subj");
			$DB->AddField("spec_id");
			$DB->AddField("view_id");
			$DB->AddField("spec_type");
			$DB->AddCondFS("file_id", "=", $row["id"]);
			$DB->AddCondFO("spec_id", " is not null ");
			$res_spec_info = $DB->Select(null, null, true, true, true);
			
			$i = 0;
			while($row_spec_info = $DB->FetchAssoc($res_spec_info)) {
				$DB->SetTable("nsau_specialities");
				$DB->AddField("name");
				$DB->AddCondFS("code", "=", $row_spec_info["spec_id"]);
				$res_spec_name = $DB->Select();
				$row_spec_name = $DB->FetchAssoc($res_spec_name);
				$row["speclist"][$i]["code"] = $row_spec_info["spec_id"];
				$row["speclist"][$i]["type"] = $row_spec_info["spec_type"];
				$row["speclist"][$i]["view"] = $row_spec_info["view_id"];
				$row["speclist"][$i]["name"] = $row_spec_name["name"];
				$i++;
			}*/
			
			$DB->SetTable("nsau_files_folders");
			$DB->AddCondFS("id", "=", $row["folder_id"]);
			$DB->AddCondFS("user_id", "=", $user_id);
			$res_folder_info = $DB->Select();
			
			$i = 0;
			while($row_foder_info = $DB->FetchAssoc($res_folder_info)) {
				$row["folder"][$i]["id"] = $row_foder_info["id"];
				$row["folder"][$i]["name"] = $row_foder_info["name"];
				$i++;
			}
			
            $this->output["files_list"][] = $row;
			
        }
		$DB->SetTable("nsau_files_folders");
		$DB->AddCondFS("user_id","=", $user_id);
		$res_1 = $DB->Select();
		while($row_1 = $DB->FetchAssoc($res_1)) {
		   $this->output["folder_list"][] = $row_1;
		}
		$this->output["upfile_id"] = md5(uniqid(mt_rand()));
		$this->output["upfile_name"] = ini_get("session.upload_progress.name");
		
		$array = $Engine->GetPrivileges($this->module_id, 'files.transfer', null, $Auth->usergroup_id);
		$dep_arr = null;
		foreach($array[$this->module_id]['files.transfer'] as $entry => $allowed) {
			if($Engine->OperationAllowed($this->module_id, 'files.transfer', $entry, $Auth->usergroup_id)) {
				if($entry == -1) {
					$dep_arr = -1;
					continue;
				} else {
					$dep_arr[] = $entry;
				}
			}
		}
		if(!is_null($dep_arr)) {
			$DB->SetTable("nsau_teachers", "t");
			$DB->AddTable("nsau_people", "p");
			$DB->AddTable("nsau_departments", "d");
			$DB->AddCondFF("t.people_id","=", "p.id");
			$DB->AddCondFF("d.id","=", "t.department_id");
			$DB->AddCondFS("p.user_id","!=", 0);
			$DB->AddField("t.department_id", "d_id");
			$DB->AddField("d.name", "d_name");
			$DB->AddField("p.user_id", "user_id");
			$DB->AddField("p.last_name", "last_name");
			$DB->AddField("p.name", "name");
			$DB->AddField("p.patronymic", "patronymic");
			if($dep_arr != -1) {
				foreach($dep_arr as $dep_id) {
					$DB->AddCondFS("t.department_id","=", $dep_id);
				}
			}
			
			$DB->AddOrder('d.name');
			$DB->AddOrder('p.last_name');
			$res = $DB->Select();
			while($row = $DB->FetchAssoc($res)) {
				$this->output["teacher_transfer"][$row['d_id']]['dname'] = $row['d_name'];
				$this->output["teacher_transfer"][$row['d_id']]['teacher'][$row['user_id']] = $row['last_name'].' '.$row['name'].' '.$row['patronymic'];
			}
		}
	}
	
	function moderation_files_list($get_unmoderated_files = false) {
		global $DB, $Auth, $Engine;
		
		$file_list = null;
		$DB->SetTable("nsau_files", "f");
		$DB->AddTable("nsau_files_subj", "s");
		$DB->AddExp("COUNT(distinct f.id)");
		
		$DB->AddCondFF("f.id", "=", "s.file_id");
		
		if ($get_unmoderated_files) {
			$DB->AddCondFO("s.approved", " is null ");
		}
		else if(!$this->output["allow_files_moderation"]) {
			$DB->AddCondFS("s.approved", "=", 1);
		}
		if (isset($_REQUEST['file_spec']) && $_REQUEST['file_spec']) {
			$DB->AddCondFS("s.spec_id", "=", $_REQUEST['file_spec']);
		}
		if (isset($_REQUEST['file_subject']) && $_REQUEST['file_subject']) {
			$DB->AddCondFS("s.subject_id", "=", $_REQUEST['file_subject']);
		}
		if (isset($_REQUEST['file_year']) && $_REQUEST['file_year']) {
			$DB->AddCondFS("f.year", "=", $_REQUEST['file_year']);
		}
		if (isset($_REQUEST['file_view']) && $_REQUEST['file_view'] != "") {
			if($_REQUEST['file_view'] != -1) $DB->AddCondFS("s.view_id", "=", $_REQUEST['file_view']);
			else $DB->AddCondFO("s.view_id", "is NULL"); //$DB->AddCondFN("s.view_id");
		}
		if (isset($_REQUEST['file_education']) && $_REQUEST['file_education'] != "") {
			if($_REQUEST['file_education'] != '-') $DB->AddCondFS("s.education", "=", $_REQUEST['file_education']);
			else $DB->AddCondFO("s.education", "is NULL");
		}
		if (isset($_REQUEST['file_author']) && $_REQUEST['file_author']) {
			$DB->AddCondFS("f.author", " LIKE ", '%'.mysql_real_escape_string($_REQUEST['file_author']).'%');
		}
		if (isset($_REQUEST['file_user']) && $_REQUEST['file_user']) {
			$DB->AddCondFS("f.user_id", "=", $_REQUEST['file_user']);
		}
		if (isset($_REQUEST['file_status']) && $_REQUEST['file_status']) {
			$DB->AddCondFS("s.approved", "=", $_REQUEST['file_status']);
		}
		elseif (isset($_REQUEST['file_status']) && !$_REQUEST['file_status']) {
			$DB->AddCondFO("s.approved", "is null");
		}
		if (isset($_REQUEST['file_name']) && $_REQUEST['file_name']) { 
			$DB->AddAltFS("f.name", " LIKE ", '%'.mysql_real_escape_string($_REQUEST['file_name']).'%');
			$DB->AddAltFS("f.descr", " LIKE ", '%'.mysql_real_escape_string($_REQUEST['file_name']).'%');
			$DB->AppendAlts();
		}
						/*if (isset($data['sort_field']) && $data['sort_field']) {
							if ($data['sort_field'] == 'year')
								$DB->AddOrder("f.year", (isset($data['sort_dir']) && $data['sort_dir'] ? ($data['sort_dir'] == "desc" ? true : false ): true));
							else {
								$DB->AddOrder("f.descr", (isset($data['sort_dir']) && $data['sort_dir'] ? ($data['sort_dir'] == "desc" ? false : true ) : false));
								$DB->AddOrder("f.name", (isset($data['sort_dir']) && $data['sort_dir'] ? ($data['sort_dir'] == "desc" ? false : true ) : false));
							}
						}*/
		//$DB->AddGrouping("f.id");
		
		$res1 = $DB->Select(1, null , true, false, true);
		list($count) = $DB->FetchRow($res1);
		
		$this->output["count"] = $count; 
		
		$DB->ResetFields();
		$DB->AddField("f.id", "id");
		$DB->AddField("f.name", "name");
		$DB->AddField("f.filename", "filename");
		$DB->AddField("f.descr", "descr");
		$DB->AddField("f.author", "author");
		$DB->AddField("f.place", "place");
		$DB->AddField("f.year", "year");
		$DB->AddField("f.user_id", "user_id");
		$DB->AddField("s.id", "file_attach_id");
		$DB->AddField("s.profile_id", "profile");
		
		/*$DB->AddField("s.id", 's_id');
		$DB->AddField("s.subject_id", 'subject_id');
		$DB->AddField("s.spec_id", 'spec_id');
		$DB->AddField("s.spec_code", 'spec_code');
		$DB->AddField("s.spec_type", 'spec_type');
		$DB->AddField("s.view_id", 'view_id');
		$DB->AddField("s.education", 'education');
		$DB->AddField("s.approved", 'approved');
		$DB->AddField("s.comment", 'comment');*/
		
		$DB->AddOrder("f.id", true);
		$DB->AddOrder("s.id", true);
		$DB->AddGrouping("f.id");//echo $DB->SelectQuery();  
						
						/*if (isset($data['page']) && is_numeric($data['page'])) {
							$page = $data['page'];
							while (($filesShowLimit*($page-1)) >= $count && ($page>0)) {
								$page--;
							}
						} else $page = 1;
												
						$res = $DB->Select($filesShowLimit, isset($data['page']) ? $filesShowLimit*($page-1) : null ,true, true, true);*/
		require_once INCLUDES . "Pager.class";
		
		
		//$num = $DB->Count("f.id");
		$data_array = array(
			"file_name" => $_REQUEST['file_name'],
			"file_author" => $_REQUEST['file_author'],
			"file_spec" => $_REQUEST['file_spec'],
			"file_subject" =>  $_REQUEST['file_subject'],
			"file_view" =>  $_REQUEST['file_view'],
			"file_year" =>  $_REQUEST['file_year'], 
			"file_user" =>  $_REQUEST['file_user'],
			"file_status" =>  $_REQUEST['file_status'],
			"file_education" => $_REQUEST['file_education'], 
			"page" => $_REQUEST["page"]
		); 
		foreach($data_array as $key => $value){
			if(empty($value) && $key != "file_status") unset($data_array[$key]);
		}
		$Pager = new Pager($count, null, null, $preceding_uri, "page", $data_array);
		$this->output["pager_output"] = $result = $Pager->Act();
		$res = $DB->Select($result["db_limit"], $result["db_from"], false, true);
		
		$files_dates = array(); 
		
		while ($row = $DB->FetchAssoc($res)) {
			$file_list[$row["id"]]["id"] = $row["id"];
			$file_list[$row["id"]]["descr"] = empty($row["descr"]) ? $row["name"] : $row["descr"];
			$file_list[$row["id"]]["name"] = $row["name"].".".$row["filename"];
			$file_list[$row["id"]]["author"] = $row["author"];
			$file_list[$row["id"]]["year"] = $row["year"];
			
			$DB->SetTable("nsau_files_subj", "s");
			
			$DB->AddField("s.id", 's_id');
			$DB->AddField("s.subject_id", 'subject_id');
			$DB->AddField("s.profile_id", 'profile_id');
			$DB->AddField("s.spec_id", 'spec_id');
			$DB->AddField("s.spec_code", 'spec_code');
			$DB->AddField("s.spec_type", 'spec_type');
			$DB->AddField("s.view_id", 'view_id');
			$DB->AddField("s.file_id", 'file_id');
			$DB->AddField("s.education", 'education');
			$DB->AddField("s.approved", 'approved');
			$DB->AddField("s.semester", 'semester');
			$DB->AddField("s.comment", 'comment');
			
			$DB->AddCondFS("s.file_id", "=", $row["id"]); 

			if ($get_unmoderated_files) {
				$DB->AddCondFO("s.approved", " is null ");
			}
			else if(!$this->output["allow_files_moderation"]) {
				$DB->AddCondFS("s.approved", "=", 1);
			}
			
			$DB->AddOrder("s.id", true);
			//$DB->AddOrder("s.approved", true); 
			
			$res_attach = $DB->Select();
			$count_attach = 0;
			
			while($row_attach = $DB->FetchAssoc($res_attach)) {
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['id'] = $row_attach["s_id"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['subject_id'] = $row_attach["subject_id"];
				
				$DB->SetTable("nsau_subjects");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["subject_id"]);
				$res_sub = $DB->Select();
				if($row_sub = $DB->FetchAssoc($res_sub)) {
					$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['subject_name'] = $row_sub["name"];
				}
				
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['spec_id'] = $row_attach["spec_id"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['spec_code'] = $row_attach["spec_code"];
				
				$DB->SetTable("nsau_specialities");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["spec_id"]);
				$row_spec = $DB->Select();
				if($row_spec = $DB->FetchAssoc($row_spec)) {
					$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['spec_name'] = $row_attach["spec_code"].'.'.$row_attach["spec_type"].' - '.$row_spec["name"];
				}
				
				$DB->SetTable("nsau_file_view");
				$DB->AddField("view_name");
				$DB->AddCondFS("id", "=", $row_attach["view_id"]);
				$row_view = $DB->Select();
				if($row_view = $DB->FetchAssoc($row_view)) {
					$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['spec_view'] = $row_view["view_name"];
				}
				
				$DB->SetTable("nsau_profiles");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["profile_id"]);
				$row_profile = $DB->Select();
				if($row_profile = $DB->FetchAssoc($row_profile)) {
					$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['profile'] = $row_profile['name'];
				}
				
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['spec_education'] = $row_attach["education"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['approved'] = $row_attach["approved"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['comment'] = $row_attach["comment"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['s_id']]['semester'] = $row_attach["semester"];
				
				if($row["user_id"]) {
					$DB->SetTable("nsau_people");
					$DB->AddField("id");
					$DB->AddField("user_id");
					$DB->AddField("last_name");
					$DB->AddField("name");
					$DB->AddField("patronymic");
					$DB->AddCondFS("user_id", "=", $row["user_id"]);
					$res_people = $DB->Select();
					if($row_people = $DB->FetchAssoc($res_people)) {
						$file_list[$row["id"]]["people"]["people_name"] = $row_people["last_name"]." ".$row_people["name"]." ".$row_people["patronymic"];
						$file_list[$row["id"]]["people"]["people_id"] = $row_people["id"];
					}
				}
				
				$DB->SetTable("engine_actions_log");
				$DB->AddField("entry_id");
				$DB->AddField("time");
				$DB->AddField("action");
				$DB->AddCondFS("module_id", "=", $this->module_id);
				$DB->AddCondFS("entry_type", "=", "file");
				$DB->AddAltFS("action", "=", "add_attach");
				//$DB->AddAltFS("action", "=", "approve");
				//$DB->AddAltFS("action", "=", "decline");
				$DB->AppendAlts(); 
				$DB->AddCondFS("entry_id", "=", $row["id"]);
				$DB->AddOrder("time", 1);//print_r($DB);
				//echo $DB->SelectQuery(1, $count_attach);
				$res_log = $DB->Select(1, $count_attach);
				while ($row_log = $DB->FetchAssoc($res_log)){
					$time_parts = explode(" ", $row_log["time"]);
					$date = implode(".", array_reverse(explode("-", $time_parts[0])));
					if (isset($file_list[$row_log["entry_id"]]) && $row_log["action"] == "create")
						$file_list[$row["id"]]["attach_file_list"][$row['s_id']]["create"] = $row_log["time"];
					//$file_list[$row_log["entry_id"]]["attach"] = $row_log["time"];
					//if(!isset($file_list[$row["id"]]["attach_file_list"][$row['s_id']]["update"])) $file_list[$row["id"]]["attach_file_list"][$row['s_id']]["update"] = $row_log["time"];
					$files_dates["attach"][$row_attach["s_id"]] = $date;
					$attach_dates[$row_log["action"]][$row_attach['s_id']] = $date;
				}
				
				$DB->SetTable("engine_actions_log");
				$DB->AddField("entry_id");
				$DB->AddField("time");
				$DB->AddField("action");
				$DB->AddCondFS("module_id", "=", $this->module_id);
				$DB->AddCondFS("entry_type", "=", "file_approved");
				$DB->AddAltFS("action", "=", "attach");
				$DB->AddAltFS("action", "=", "approve");
				$DB->AddAltFS("action", "=", "decline");
				$DB->AppendAlts(); 
				$DB->AddCondFS("entry_id", "=", $row_attach["s_id"]);
				$DB->AddOrder("time", 1);
				//echo $DB->SelectQuery();
				$res_log = $DB->Select();
				while ($row_log = $DB->FetchAssoc($res_log)){
					$time_parts = explode(" ", $row_log["time"]);
					$date = implode(".", array_reverse(explode("-", $time_parts[0])));
					if (isset($file_list[$row_log["entry_id"]]) && $row_log["action"] == "create")
						$file_list[$row["id"]]["attach_file_list"][$row['s_id']]["create"] = $row_log["time"];
					//$file_list[$row_log["entry_id"]]["attach"] = $row_log["time"];
					//$files_dates["attach"][$row_attach["s_id"]] = $date;
					//if(!isset($file_list[$row["id"]]["attach_file_list"][$row['s_id']]["update"])) 
					//$file_list[$row["id"]]["attach_file_list"][$row['s_id']]["update"] = $date;
					$files_dates[$row_log["action"]][$row_log["entry_id"]] = $date;
					if($row_log["action"] != "attach") $files_dates["update"][$row_log["entry_id"]] = $date;
				}
				
				//exit;
				$count_attach++; 
			}
			
			/* $file_list[$row["id"]]["attach_file_list"][$row['s_id']]['subject_id'] = $row["subject_id"];
				
			$DB->SetTable("nsau_subjects");
			$DB->AddField("name");
			$DB->AddCondFS("id", "=", $row["subject_id"]);
			$res_sub = $DB->Select();
			if($row_sub = $DB->FetchAssoc($res_sub)) {
				$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['subject_name'] = $row_sub["name"];
			}
			
			$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['spec_id'] = $row["spec_id"];
			$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['spec_code'] = $row["spec_code"];
			
			$DB->SetTable("nsau_specialities");
			$DB->AddField("name");
			$DB->AddCondFS("id", "=", $row["spec_id"]);
			$row_sub = $DB->Select();
			if($row_sub = $DB->FetchAssoc($row_sub)) {
				$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['spec_name'] = $row["spec_code"].'.'.$row["spec_type"].' - '.$row_sub["name"];
			}
			
			$DB->SetTable("nsau_file_view");
			$DB->AddField("view_name");
			$DB->AddCondFS("id", "=", $row["view_id"]);
			$row_view = $DB->Select();
			if($row_view = $DB->FetchAssoc($row_view)) {
				$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['spec_view'] = $row_view["view_name"];
			}
			
			$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['spec_education'] = $row["education"];
			$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['approved'] = $row["approved"];
			$file_list[$row["id"]]["attach_file_list"][$row['s_id']]['comment'] = $row["comment"];
				
			if($row["user_id"]) {
				$DB->SetTable("nsau_people");
				$DB->AddField("id");
				$DB->AddField("user_id");
				$DB->AddField("last_name");
				$DB->AddField("name");
				$DB->AddField("patronymic");
				$DB->AddCondFS("user_id", "=", $row["user_id"]);
				$res_people = $DB->Select();
				if($row_people = $DB->FetchAssoc($res_people)) {
					$file_list[$row["id"]]["people"]["people_name"] = $row_people["last_name"]." ".$row_people["name"]." ".$row_people["patronymic"];
					$file_list[$row["id"]]["people"]["people_id"] = $row_people["id"];
				}
			} */
			$DB->SetTable("engine_actions_log");
			$DB->AddField("entry_id");
			$DB->AddField("time");
			$DB->AddField("action");
			$DB->AddCondFS("module_id", "=", $this->module_id);
			$DB->AddCondFS("entry_type", "=", "file");
			$DB->AddAltFS("action", "=", "create");
			$DB->AddAltFS("action", "=", "attach");
			$DB->AddAltFS("action", "=", "update");
			$DB->AppendAlts(); 
			$DB->AddCondFS("entry_id", "=", $row["id"]);
			//echo $DB->SelectQuery();//exit; 
			$res_log = $DB->Select();
			while ($row_log = $DB->FetchAssoc($res_log)){
				$time_parts = explode(" ", $row_log["time"]);
				$date = implode(".", array_reverse(explode("-", $time_parts[0])));
				if (isset($file_list[$row_log["entry_id"]]) && $row_log["action"] == "create")
					$file_list[$row_log["entry_id"]]["create"] = $row_log["time"];
				$files_dates[$row_log["action"]][$row_log["entry_id"]] = $date;
			}
		} 
		
		//print_r($attach_dates);
		$this->output["attach_dates"] = $attach_dates;

		// определяем даты загрузок файлов
		/* $files_dates = array();
		$DB->SetTable("engine_actions_log");
		$DB->AddField("entry_id");
		$DB->AddField("time");
		$DB->AddField("action");
		$DB->AddCondFS("module_id", "=", $this->module_id);
		$DB->AddAltFS("action", "=", "create");
		$DB->AddAltFS("action", "=", "attach");
		$DB->AddAltFS("action", "=", "update");
		$DB->AppendAlts();
		$DB->AddOrder("time");
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res))
		{
			$time_parts = explode(" ", $row["time"]);
			$date = implode(".", array_reverse(explode("-", $time_parts[0])));
			

			if (isset($file_list[$row["entry_id"]]) && $row["action"] == "create")
				$file_list[$row["entry_id"]]["create"] = $row["time"];
			$files_dates[$row["action"]][$row["entry_id"]] = $date;
		} */
		$this->output["files_dates"] = $files_dates;
		
		//uasort($file_list, function($file1, $file2){ return $file1["create"] > $file2["create"]; });
		//uasort($file_list, create_function('$file1, $file2', 'return $file1["create"] > $file2["create"];'));
		
		$this->output["files_list"] = $file_list;
		$this->output["form_back"] = array(
			"file_name" => $_REQUEST['file_name'],
			"file_author" => $_REQUEST['file_author'],
			"file_spec" => $_REQUEST['file_spec'],
			"file_subject" =>  $_REQUEST['file_subject'],
			"file_view" =>  $_REQUEST['file_view'],
			"file_year" =>  $_REQUEST['file_year'], 
			"file_user" =>  $_REQUEST['file_user'],
			"file_status" => $_REQUEST['file_status'],
			"file_education" => $_REQUEST['file_education']
		);
		
		
		
		
		
		
		
		
		
		
		/*$file_list = null;
		
		$DB->SetTable("nsau_files_subj");
		$DB->AddField("id");
		$DB->AddField("file_id");
		$DB->AddField("subject_id");
		$DB->AddField("spec_id");
		$DB->AddField("spec_code");
		$DB->AddField("spec_type");
		$DB->AddField("view_id");
		$DB->AddField("education");
		$DB->AddField("approved");
		$DB->AddField("comment");
		$DB->AddCondFO("subject_id", " is not null ");
		//$DB->AddAltFO("approved", " is null ");
		//$DB->AddAltFS("approved", "=", 0);
		if(!empty($_POST['file_spec'])) {
			$DB->AddCondFS("spec_id", "=", $_POST['file_spec']);
		}
		if(!empty($_POST['file_view'])) {
			$DB->AddCondFS("view_id", "=", $_POST['file_view']);
		}
		if(!empty($_POST['file_subject'])) {
			$DB->AddCondFS("subject_id", "=", $_POST['file_subject']);
		}
		$res_attach = $DB->Select(null, null, true, true, true);
		while($row_attach = $DB->FetchAssoc($res_attach)) {
			$DB->SetTable("nsau_files");
			$DB->AddField("id");
			$DB->AddField("name");
			$DB->AddField("filename");
			$DB->AddField("is_html");
			$DB->AddField("descr");
			$DB->AddField("user_id");
			$DB->AddField("author");
			$DB->AddField("year");
			$DB->AddCondFS("id", "=", $row_attach["file_id"]);
			if(!empty($_POST['file_name'])) {
				$DB->AddCondFS("name", "=", $_POST['file_name']);
				$DB->AddAltFS("descr", "=", $_POST['file_name']);
			}
			if(!empty($_POST['file_author'])) {
				$DB->AddCondFS("author", "=", $_POST['file_author']);
			}
			if(!empty($_POST['file_year'])) {
				$DB->AddCondFS("year", "=", $_POST['file_year']);
			}
			$res = $DB->Select();
			while($row = $DB->FetchAssoc($res)) {
				$file_list[$row["id"]]["id"] = $row["id"];
				$file_list[$row["id"]]["descr"] = empty($row["descr"]) ? $row["name"] : $row["descr"];
				$file_list[$row["id"]]["name"] = $row["name"].".".$row["filename"];
				$file_list[$row["id"]]["author"] = $row["author"];
				$file_list[$row["id"]]["year"] = $row["year"];
				
				$DB->SetTable("nsau_subjects");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["subject_id"]);
				$res_sub = $DB->Select();
				if($row_sub = $DB->FetchAssoc($res_sub)) {
					$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['subject_name'] = $row_sub["name"];
				}
				$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['subject_id'] = $row_attach["subject_id"];
				$DB->SetTable("nsau_specialities");
				$DB->AddField("name");
				$DB->AddCondFS("id", "=", $row_attach["spec_id"]);
				$row_sub = $DB->Select();
				if($row_sub = $DB->FetchAssoc($row_sub)) {
					$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['spec_name'] = $row_attach["spec_code"].'.'.$row_attach["spec_type"].' - '.$row_sub["name"];
				}
				$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['spec_code'] = $row_attach["spec_code"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['spec_id'] = $row_attach["spec_id"];
				$DB->SetTable("nsau_file_view");
				$DB->AddField("view_name");
				$DB->AddCondFS("id", "=", $row_attach["view_id"]);
				$row_view = $DB->Select();
				if($row_view = $DB->FetchAssoc($row_view)) {
					$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['spec_view'] = $row_view["view_name"];
				}
				$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['spec_education'] = $row_attach["education"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['approved'] = $row_attach["approved"];
				$file_list[$row["id"]]["attach_file_list"][$row_attach['id']]['comment'] = $row_attach["comment"];
				
				if($row["user_id"]) {
					$DB->SetTable("nsau_people_copy");
					$DB->AddField("id");
					$DB->AddField("user_id");
					$DB->AddField("last_name");
					$DB->AddField("name");
					$DB->AddField("patronymic");
					$DB->AddCondFS("user_id", "=", $row["user_id"]);
					$res_people = $DB->Select();
					if($row_people = $DB->FetchAssoc($res_people)) {
						$file_list[$row["id"]]["people"]["people_name"] = $row_people["last_name"]." ".$row_people["name"]." ".$row_people["patronymic"];
						$file_list[$row["id"]]["people"]["people_id"] = $row_people["id"];
					}
				}
			}
		}
		$this->output["files_list"] = $file_list;*/
	}
	
	function form_report() 
	{
		global $DB, $Auth;
	
		$this->output['module_id'] = $this->module_id;
		$this->output['submode'] = $this->subParams[0];
		
		switch ($this->output['submode']) {
			case 'departments':	
				
				
				if (isset($_GET['dep_id'])) 
				{
					$res = (isset($_GET['summary']) && $_GET['summary']) ? $DB->Exec("select count(distinct nf.id) as f_count,  su.name as sname, nfsu.subject_id as subj from nsau_files as nf right join nsau_files_subj as nfsu on nfsu.file_id = nf.id right join nsau_subjects as su on nfsu.subject_id = su.id where su.department_id= ".$_GET['dep_id']." group by nfsu.subject_id") : $DB->Exec("select count(distinct nf.id) as f_count,  ns.id_faculty as fac, su.name as sname, nfsu.subject_id as subj, f.name as fname from nsau_files as nf right join nsau_files_subj as nfsu on nfsu.file_id = nf.id right join nsau_subjects as su on nfsu.subject_id = su.id left join nsau_specialities as ns on ns.id = nfsu.spec_id left join nsau_faculties as f on f.id = ns.id_faculty where su.department_id=".$_GET['dep_id']." group by ns.id_faculty, nfsu.subject_id");
				
					if ($res)
						while($row = $DB->FetchAssoc($res))  {
							if ($row['f_count']) {
								if (isset($_GET['summary']) && $_GET['summary']) 
									$this->output["files_count"][$row['sname']] = $row['f_count'];
								else
									$this->output["files_count"][$row['fname']][$row['sname']] = $row['f_count'];
							}
						}
					$DB->FreeRes($res);
				
					$DB->SetTable("nsau_subjects");
					$DB->AddField("id");
					$DB->AddField("name");
					$DB->AddCondFS("department_id", "=", $_GET['dep_id']);
					if ($res = $DB->Select()) 
					{
						while($row = $DB->FetchAssoc($res)) {

							$this->output["subjects"][] = $row;
						}

						$DB->FreeRes($res);
					}
					
					
				}
/*				$DB->SetTable("nsau_faculties");
				
					$DB->AddField("id");
					$DB->AddField("name");
					$DB->AddField("short_name");
					$DB->AddCondFS("pid", "=", "0");
					$DB->AddCondFS("is_active", "=", "1");*/
					if ($res = $DB->Exec("select id, name, short_name from nsau_faculties where id in(select distinct faculty_id from nsau_departments)")) 
					{
						while($row = $DB->FetchAssoc($res)) 
						{
							$this->output["faculties"][] = $row;
						}
					}
				$DB->FreeRes($res);
				$DB->SetTable("nsau_departments");
				$DB->AddField("id");
				$DB->AddField("faculty_id");
				$DB->AddField("name");
				$DB->AddCondFS("is_active", "=", "1");
				if ($res = $DB->Select()) 
				{
					while($row = $DB->FetchAssoc($res))
					{
						$this->output["departments"][] = $row;
					}
				} 
				if (isset($_GET["summary"]) && $_GET["summary"])
					$this->output["show_summary"] = true; 
			break;
			
			case 'specialities':
				/*$DB->SetTable("nsau_specialities", "s");
				$DB->AddTable("nsau_spec_type", "st");
				$DB->AddField("s.code");
				$DB->AddField("s.name");
				$DB->AddField("st.type");
				$DB->AddCondFF("s.code", "=", "st.code");
				$DB->AddOrder("s.name");*/
				if ($res = $DB->Exec("SELECT fs.spec_id as id, fs.spec_code as code, s.name,  fs.spec_type as type, count(*) as total FROM nsau_files_subj fs left join nsau_specialities as s on fs.spec_id=s.id  group by fs.spec_code having fs.spec_code is not null order by s.name"))
				//if ($res = $DB->Exec("select distinct s.id, s.code, s.name, st.type from nsau_specialities as s inner join nsau_spec_type as st on st.code = s.code order by s.name")) 
				{
					$spec_types = array("bachelor" => 62, "secondary" => 51, "magistracy" => 68, "higher" => 65);
					while($row = $DB->FetchAssoc($res))
					{
						//$row["type"] = $spec_types[$row["type"]]; 
						$this->output["specialities"][] = $row;
					}
					$DB->FreeRes($res);
				}
				
				$DB->SetTable("nsau_file_view");
				$DB->AddField("id");
				$DB->AddField("view_name");
				$DB->AddOrder("pos");
				if ($res = $DB->Select()) 
				{
					while($row = $DB->FetchAssoc($res)) 
						$this->output["umkd"][] = $row;
					$DB->FreeRes($res);
				}
				
				if (isset($_GET['spec_id']))
				{
					$spec_id = mysql_real_escape_string($_GET['spec_id']);
					/*$spec_info = explode('.', $_GET['spec_id']);
					$spec_code = $spec_info[0];
					$spec_type = $spec_info[1];*/
				}
				if (isset($_GET['spec_id']) && $res = $DB->Exec("select count(nf.id) as f_count, nfv.view_name as uname, su.name as sname from nsau_files as nf  right join nsau_files_subj as nfsu on nfsu.file_id=nf.id right join nsau_subjects as su on su.id = nfsu.subject_id right join nsau_file_view as nfv on nfv.id = nfsu.view_id where nfsu.spec_id=".$spec_id."  group by nfsu.view_id, nfsu.subject_id"))
				{ 
					while($row = $DB->FetchAssoc($res))  {
						if ($row['f_count'])
							$this->output["files_count"][$row['uname']][$row['sname']] = $row['f_count'];
					}
					
					$DB->FreeRes($res);
				
				}
				
				if (isset($_GET['spec_id'])  && $res = $DB->Exec("select su.id, su.name, d.name as dname from nsau_subjects as su left join nsau_departments as d on d.id=su.department_id where d.faculty_id in (select id_faculty from nsau_specialities ".( isset($spec_id) ? "where id=".$spec_id : "" ).") "))
				/*$DB->SetTable("nsau_subjects", "su");
				$DB->AddTable("nsau_departments", "d");
				$DB->AddField("su.id");
				$DB->AddField("su.name");
				$DB->AddField("d.name", "dname");
				$DB->AddCondFF("su.department_id","=", "d.id");
				if ($res = $DB->Select()) */
				{
					while($row = $DB->FetchAssoc($res)) 
						$this->output["subjects"][] = $row;
					$DB->FreeRes($res);
				}
				
			break;
		}
		
	}
	
	function get_files_by_params($query_type, $query_data) {
		global $DB, $Engine, $Auth;
		$files = array();
		$cyr_text_fields = array('name', 'descr', 'author', 'place', 'view_name');
		switch ($query_type) {
			case 'for_dep':
				$res = (isset($query_data["faculty_id"])) ? $DB->Exec("SELECT distinct f.id,f.name,f.descr, nfw.view_name, f.author,f.year,f.volume,f.edition,f.place FROM nsau_files AS f inner JOIN nsau_files_subj AS su on su.file_id = f.id AND su.subject_id = ".$query_data['subject_id']."  INNER JOIN nsau_specialities as s on s.id=su.spec_id AND s.id_faculty =".$query_data['faculty_id']." LEFT JOIN nsau_file_view AS nfw on nfw.id = su.view_id") : $DB->Exec("SELECT distinct f.id,f.name,f.descr, nfw.view_name, f.author,f.year,f.volume,f.edition,f.place FROM nsau_files AS f inner JOIN nsau_files_subj AS su on su.file_id = f.id AND su.subject_id = ".$query_data['subject_id']."  LEFT JOIN nsau_file_view AS nfw on nfw.id = su.view_id") ;
			break;
			
			case 'for_spec':
				$res = $DB->Exec("SELECT distinct f.id,f.name,f.descr, nfw.view_name, f.author,f.year,f.volume,f.edition,f.place FROM nsau_files AS f inner JOIN nsau_files_subj AS su on su.file_id = f.id AND su.subject_id = ".$query_data['subject_id']."  AND su.view_id = ".$query_data['umkd_id']." AND su.spec_id = ".$query_data['spec_id']." LEFT JOIN nsau_file_view AS nfw on nfw.id = su.view_id");//." AND sp.spec_type = ".$query_data['spec_id']);
			break;
		}
		if ($res) {
			while($row = $DB->FetchAssoc($res)) {
				foreach ($row as $key=>$value) 
					if (in_array($key, $cyr_text_fields))
						$row[$key] = iconv('windows-1251', 'utf-8', $value);
					
				$files[] = $row;
			}
			$DB->FreeRes($res);
		}
		return $files;
	}
	
	
	
	function get_parent_usergroup_id() {
		global $DB, $Engine, $Auth;

		$DB->SetTable("auth_usergroups", "au");	
		$DB->AddTable("nsau_departments","d");
		$DB->AddCondFS("au.id","=", $Auth->usergroup_id);
		$DB->AddCondFF("d.name","=", "au.comment");
		$DB->AddField("d.faculty_id","faculty_id");
		$res = $DB->Select();
		$row = $DB->FetchObject($res);		
		if (isset($row->faculty_id)) {
			$DB->SetTable("auth_usergroups", "au");	
			$DB->AddTable("nsau_faculties","f");
			$DB->AddCondFS("f.id","=", $row->faculty_id);
			$DB->AddCondFF("au.comment","=", "f.name");
			$DB->AddField("au.id","parent_usergroup_id");
			$res = $DB->Select();
			$row = $DB->FetchObject($res);
			return (isset($row->parent_usergroup_id)?$row->parent_usergroup_id:-1);
		}
		return -1;

	}
	
	function attach_file() {
		global $DB, $Engine, $Auth;
		$this->files_list();
		$spec_type_code = array(
			'secondary' => 51,
			'bachelor' => 62,
			'higher' => 65,
			'magistracy' => 68
		);
		$spec_type_name = array(
			'secondary' => 'Среднее специальное',
			'bachelor' => 'Бакалавриат',
			'higher' => 'Специалитет',
			'magistracy' => 'Магистратура'
		);
		
		if ($Engine->OperationAllowed($this->module_id, "files.attach", -1, $Auth->usergroup_id) 
			|| $Engine->OperationAllowed($this->module_id, "files.attach", -1, $this->get_parent_usergroup_id()))
			$this->output["allow_attach"] = 1;
		else
			$this->output["allow_attach"] = 0;
		
		$peopleDeps = array();
		$DB->SetTable("nsau_people", "p");
		$DB->AddTable("nsau_teachers","t");
		$DB->AddCondFS("p.user_id","=", $Auth->user_id);
		$DB->AddCondFF("p.id","=", "t.people_id");     
		$DB->AddField("t.department_id","dep_id");
		$res = $DB->Select();
		while ($row = $DB->FetchObject($res))
			$peopleDeps[] = $row->dep_id;
			
		if(!isset($deps)) {
			$DB->SetTable("nsau_departments", "dep");
			$DB->AddField("dep.id","dep_id");
			$DB->AddField("dep.faculty_id","fac_id");
			$res = $DB->Select();
			while ($row = $DB->FetchObject($res))
				$deps[] = $row;
		}
		
		if ($deps)
			foreach ($deps as $dep)
				if (in_array($dep->dep_id, $peopleDeps) || $Engine->OperationAllowed($this->module_id, "files.attach", $dep->dep_id, $Auth->usergroup_id)) { 
					$dep_ids[$dep->dep_id] = 1;
				}
		if (isset($dep_ids) && count($dep_ids)) 
			$this->output["allow_attach"] = 1;
			
		$DB->SetTable("nsau_subjects", "subj");
		$DB->AddTable("nsau_departments", "dep");
		$DB->AddCondFF("subj.department_id","=", "dep.id");
		if(isset($deps))
			foreach ($deps as $dep)
				if (isset($dep_ids, $dep_ids[$dep->dep_id]))
					$DB->AddAltFS("subj.department_id","=", $dep->dep_id);		
		$DB->AddField("subj.id", "sid");
		$DB->AddField("subj.name", "sname");
		$DB->AddField("dep.id", "did");
		$DB->AddField("dep.name", "dname");
		$DB->AddOrder("dep.name");
		$DB->AddOrder("subj.name");
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			if(!$Engine->OperationAllowed($this->module_id, "files.attach", $row["did"], $Auth->usergroup_id)) continue;
			$this->output["subjects_list"][$row["did"]]["name"] = $row["dname"];
            $this->output["subjects_list"][$row["did"]]["subj"][] = $row;			
        }
		
		$DB->SetTable("nsau_specialities", "spec");
		$DB->AddField("spec.id", "spec_id");
		$DB->AddField("spec.code", "code");
		$DB->AddField("spec.name", "spname");
		$DB->AddOrder("spec.name");		
		/*if(isset($deps))
			foreach ($deps as $dep)
				if (isset($dep_ids, $dep_ids[$dep->dep_id]))
					$DB->AddAltFS("id_faculty","=", $dep->fac_id);*/
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$this->output["spec_list"][$row['spec_id']]['name'] = $row['spname'];
			$this->output["spec_list"][$row['spec_id']]['code'] = $row['code'];
			$DB->SetTable("nsau_spec_type");
			$DB->AddCondFS("spec_id","=", $row['spec_id']);
			$DB->AddCondFS("code","=", $row['code']);
			$DB->AddOrder("type");
			$res1 = $DB->Select();
			while($row1 = $DB->FetchAssoc($res1)) {
				$this->output["spec_list"][$row['spec_id']]['type'][$row1['id']]['name'] = $spec_type_name[$row1['type']];
				$this->output["spec_list"][$row['spec_id']]['type'][$row1['id']]['code'] = $spec_type_code[$row1['type']];
			}
			$DB->SetTable("nsau_profiles");
			$DB->AddCondFS("spec_id","=", $row['spec_id']);
			$res2 = $DB->Select();
			while($row2 = $DB->FetchAssoc($res2)) {
				$this->output["spec_list"][$row['spec_id']]['profile'][$row2['id']]['name'] = $row2['name'];
				$this->output["spec_list"][$row['spec_id']]['profile'][$row2['id']]['id'] = $row2['id'];
			}
        }
		
        $DB->SetTable("nsau_file_view");
		$DB->AddOrder("pos");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
            $this->output["files_view"][] = $row;
        }
        
		/*//die(print_r($this->output["subjects_list"])); 
		$DB->SetTable("nsau_specialities");
		$DB->AddField("code");
		$DB->AddField("name");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res))
        {
            $this->output["specs_list"][] = $row;
        }
		
		
		$this->output["specs_types_list"] = array(51 => "51 - Среднее специальное", 62 => "62 - Бакалавриат", 65 => "65 - Специалитет", 68 => "68 - Магистратура");
        
		
				*/	
	}
	/*
	function attach_file()
	{
		global $DB, $Engine, $Auth;
		$this->files_list();
		
		if ($Engine->OperationAllowed($this->module_id, "files.attach", 0, $Auth->usergroup_id) 
			|| $Engine->OperationAllowed($this->module_id, "files.attach", 0, $this->get_parent_usergroup_id()))
			$this->output["allow_attach"] = 1;
		else
			$this->output["allow_attach"] = 0;
		
		$DB->SetTable("nsau_people", "p");
		$DB->AddTable("nsau_teachers","t");
		$DB->AddCondFS("p.user_id","=", $Auth->user_id);
		$DB->AddCondFF("p.id","=", "t.people_id");     
		$DB->AddField("t.department_id","dep_id");
		$res = $DB->Select();
		while ($row = $DB->FetchObject($res))
			$deps[] = $row;
			
		if(!isset($deps)) {
			$DB->SetTable("nsau_departments", "dep");
			$DB->AddField("dep.id","dep_id");
			$res = $DB->Select();
			while ($row = $DB->FetchObject($res))
				$deps[] = $row;
		}
		
		if ($deps)
			foreach ($deps as $dep)
				if ($this->output["allow_attach"] || $Engine->OperationAllowed($this->module_id, "files.attach", $dep->dep_id, $Auth->usergroup_id)) { 
					$dep_ids[$dep->dep_id] = 1;
				}
		if (isset($dep_ids) && count($dep_ids)) 
			$this->output["allow_attach"] = 1;
			
		$DB->SetTable("nsau_subjects", "subj");
		$DB->AddTable("nsau_departments", "dep");
		$DB->AddCondFF("subj.department_id","=", "dep.id");    
		if(isset($deps))
			foreach ($deps as $dep)
				if (isset($dep_ids, $dep_ids[$dep->dep_id]))
					$DB->AddAltFS("subj.department_id","=", $dep->dep_id);
		
		$DB->AddField("subj.id", "sid");
		$DB->AddField("subj.name", "sname");
		$DB->AddField("dep.id", "did");
		$DB->AddField("dep.name", "dname");
		$DB->AddOrder("dep.name");
		$DB->AddOrder("subj.name");
		//die($DB->SelectQuery());
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			//if($row["sid"]!=130) die(print_r($row));
            $this->output["subjects_list"][$row["did"]]["name"] = $row["dname"];
            $this->output["subjects_list"][$row["did"]]["subj"][] = $row;
        }
        
        
		//die(print_r($this->output["subjects_list"])); 
		$DB->SetTable("nsau_specialities");
		$DB->AddField("code");
		$DB->AddField("name");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res))
        {
            $this->output["specs_list"][] = $row;
        }
		
		
		$this->output["specs_types_list"] = array(51 => "51 - Среднее специальное", 62 => "62 - Бакалавриат", 65 => "65 - Специалитет", 68 => "68 - Магистратура");
        
		
		$DB->SetTable("nsau_file_view");
		$DB->AddOrder("pos");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res))
        {
            $this->output["files_view"][] = $row;
        }			
	}*/

	function create_folder()
	{
		global $DB, $Engine, $Auth;
		
		if ($Engine->OperationAllowed($this->module_id, "files.attach", 0, $Auth->usergroup_id) 
			|| $Engine->OperationAllowed($this->module_id, "files.attach", 0, $this->get_parent_usergroup_id()))
			$this->output["allow_attach"] = 1;
		else
			$this->output["allow_attach"] = 0;
		if(isset($_POST["add"])) {
			$DB->SetTable("nsau_files_folders");
			$DB->AddValue("name", $_POST["add"]["folder_name"]);
			$DB->AddValue("user_id", $Auth->user_id);
			$DB->Insert();
			CF::Redirect($rederect);
		} elseif(isset($_POST["delete"])) {
			$DB->SetTable("nsau_files");					
			$DB->AddCondFS("user_id","=", $Auth->user_id);
			$DB->AddCondFS("folder_id", "=", $_POST["delete"]["folder_id"]);
			$DB->AddValue("folder_id", NULL);
			$DB->Update();
			
			$DB->SetTable("nsau_files_folders");
			$DB->AddCondFS("user_id","=", $Auth->user_id);
			$DB->AddCondFS("id","=", $_POST["delete"]["folder_id"]);
			$DB->Delete();
			CF::Redirect($rederect);
		}
		$DB->SetTable("nsau_files_folders");
		$DB->AddCondFS("user_id","=", $Auth->user_id);
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
            $this->output["folder_list"][] = $row;
        }
		
	}
	
	function get_file($file_id)
	{
		global $DB, $Engine;

		$DB->SetTable("nsau_files");
		$DB->AddField("name");
		$DB->AddField("filename");
		$DB->AddField("is_html");
		$DB->AddField("down_count");
		$DB->AddCondFS("id", "=", $file_id);
		$res = $DB->Select();
		$row = $DB->FetchAssoc(); 
		
		//if ($row["is_html"])
			//CF::Redirect('/htmldocs/'.$file_id.'/');
			//die(print_r($row));

		$str_from = rtrim($_SERVER["DOCUMENT_ROOT"], "/")."/".FILES_DIR.$file_id.".".$row["filename"];
		$str_to = rtrim($_SERVER["DOCUMENT_ROOT"], "/")."/".FILES_DIR.$row["name"].".".$row["filename"];
		$fname = $row["name"].".".$row["filename"];

		$filepath = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$file_id.".".$row["filename"];
		
		if (file_exists($filepath)) {
// 			$this->output["file"] = $filepath; //file_get_contents($filepath);
// 			$this->output["scripts_mode"] = $this->output["mode"] = "download";
// 			$this->output["str_to"] = $str_to;

			$filesize = filesize($filepath); 

			header('Content-Disposition: attachment; filename="'.$fname.'"');
			header('Content-type: application/octet-stream');
			header('Content-length: '.$filesize);
			header("Accept-Ranges: bytes");

			//readfile($filepath);
			$f = fopen($filepath, "r");
			//fpassthru($f);  
			while(!feof($f)) {
				// Читаем килобайтный блок, отдаем его в вывод и сбрасываем в буфер
				echo fread($f, 1024);
				flush();
			}
			fclose($f); 
			 
			$Engine->LogAction($this->module_id, "file", $file_id, "download");
			
			$count = $this->download_people_list($file_id);
			$DB->SetTable("nsau_files");
			$DB->AddCondFS("id", "=", $file_id);
			$DB->AddValue("down_count", $count);
			$DB->Update();
		}
		else {
			$Engine->HTTP404();
		}
	}

	function ProcessPostRequest() {
		
	}

	function Output()
	{
		return $this->output;
	}
	
	function RemoveDir($path)
	{
		if(file_exists($path) && is_dir($path))
		{
			$dirHandle = opendir($path);
			while (false !== ($file = readdir($dirHandle))) 
			{
				if ($file!='.' && $file!='..')// исключаем папки с назварием '.' и '..' 
				{
					$tmpPath=$path.'/'.$file;
					chmod($tmpPath, 0777);
					
					if (is_dir($tmpPath))
					{  // если папка
						RemoveDir($tmpPath);
					} 
					else 
					{ 
						if(file_exists($tmpPath))
						{
							// удаляем файл 
							unlink($tmpPath);
						}
					}
				}
			}
			closedir($dirHandle);
			
			// удаляем текущую папку
			if(file_exists($path))
			{
				rmdir($path);
			}
		}
		else
		{
			echo "Удаляемой папки не существует или это файл!";
		}
	}
	
}
	
?>