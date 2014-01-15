<?php

class EFiles
// version: 1.7
// date: 2010-02-25
{
	var $output;
	var $node_id;
	var $module_id;
	var $mode;
	
	
	function EFiles($global_params, $params, $module_id, $node_id, $module_uri)
	{
		global $Engine, $Auth, $DB;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		
		$this->output["mode"] = $this->mode = $params;
		$this->output["user_displayed_name"] = $Auth->user_displayed_name;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();

		if (ALLOW_AUTH)
		{
			switch ($this->mode)
			{
				case "files_list":
					$this->output["mode"] = "files_list";
					
					if (isset($_FILES['upload_file']) && !empty($_FILES['upload_file']['name'])) {
						$orig_name = explode(".", $_FILES['upload_file']['name']);
						//die(print_r($orig_name));
						$name_parts = count($orig_name);
						$ext = $orig_name[$name_parts-1];
						unset($orig_name[$name_parts-1]);
						$original_name = implode($orig_name, ".");
						//$new_name = md5(time()*rand(1,1000)).'.'.$ext;
						
						$DB->SetTable("nsau_files");
						$DB->AddValues(array(
							"name" => $original_name,
							"descr" => $_POST["upload_descr"],
							"filename" => $ext,
							"user_id" => $_SESSION["user_id"],
						));
							
						$DB->Insert();
						
						$new_id = $DB->LastInsertID();
						$new_name = $new_id.'.'.$ext;
						$path = $_SERVER['DOCUMENT_ROOT'].'/files/'.$new_name;
						
						$Engine->LogAction($this->module_id, "file", $DB->LastInsertID(), "create");
						
						if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $path)) {
							$DB->SetTable("nsau_files");
							$DB->AddCondFS("id", "=", $new_id);
							$DB->Delete();
							$this->output["messages"]["bad"][] = "Не удалось закачать файл";
						}
						else {
							CF::Redirect();
						}
					}
					
					if (isset($_POST["sel_subject"]) && $_POST["sel_subject"]) {
						$DB->SetTable("nsau_files_subj");
						$DB->AddValues(array(
							"file_id" => $_POST["sel_file"],
							"subject_id" => $_POST["sel_subject"],
						));
							
						if (!$DB->Insert()) $this->output["messages"]["bad"][] = "Заданная привязка уже существует";
					}
					
					if (isset($_GET["del_subj"]) && isset($_GET["del_file"])) {
						$DB->SetTable("nsau_files_subj");
						$DB->AddCondFS("file_id", "=", $_GET["del_file"]);
						$DB->AddCondFS("subject_id", "=", $_GET["del_subj"]);
						$DB->Delete();
						CF::Redirect("/office/");
					}
					
					if (isset($_GET["delete_file"])) {
						$DB->SetTable("nsau_files");
						$DB->AddCondFS("id", "=", $_GET["delete_file"]);
						$res = $DB->Select();
						$row = $DB->FetchAssoc($res);
						//die(print_r($_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["id"].".".$row["filename"]));
						$str = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["id"].".".$row["filename"];
						if (unlink($str)) {
							$DB->SetTable("nsau_files");
							$DB->AddCondFS("id", "=", $_GET["delete_file"]);
							$DB->Delete();
							$Engine->LogAction($this->module_id, "file", $_GET["delete_file"], "delete");
							
							$DB->SetTable("nsau_files_subj");
							$DB->AddCondFS("file_id", "=", $_GET["delete_file"]);
							$DB->Delete();
						}
						
						CF::Redirect("/office/");
					}
					
					$this->files_list();
				break;
				
				case "attach_file":
					$this->output["mode"] = "attach_file";				
					
					$this->attach_file();
				break;
				
				case "get_file":
					if ($Engine->OperationAllowed($this->module_id, "files.download", 0, $Auth->usergroup_id))
						$this->output["allow_download"] = 1;
					else
						$this->output["allow_download"] = 0;
					$uri_array = explode("/", $module_uri);
					if (isset($_GET["get"])) {
						$this->output["mode"] = "get_file";
						
		//				if (isset($_SESSION["user_id"]))
							$this->get_file($uri_array[0]);
					}
					else {
						$this->output["mode"] = "link_to_file";
						
						$this->output["file_id"] = $uri_array[0];
						
						$DB->SetTable("nsau_files");
						$DB->AddCondFS("id", "=", $uri_array[0]);
						$res = $DB->Select();
						$row = $this->output["file"] = $DB->FetchAssoc($res);
						
						$filepath = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$uri_array[0].".".$row["filename"];
						$filesize = filesize($filepath);
						if ($filesize > 999 && $filesize < 1000000) {
							$filesize /= 1000;
							if ($filesize > 100)
								$filesize = substr($filesize, 0, 3);
							else
								$filesize = substr($filesize, 0, 4);
							$fileend = "кб";
						}
						elseif ($filesize >= 1000000) {
							$filesize /= 1000000;
							$filesize = substr($filesize, 0, 4);
							$fileend = "Мб";
						}
						else
							$fileend = "байт";
						$this->output["filesize"] = $filesize." ".$fileend;
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
			}
		}

		else
		{
			$this->output["messages"]["bad"][] = "Доступ запрещён.";
		}
	}
	
	function files_list()
	{
		global $DB;
		$DB->SetTable("nsau_files");
		$DB->AddField("id");
		$DB->AddField("name");
		$DB->AddField("filename");
		$DB->AddField("descr");
		if (isset($_SESSION["user_id"]))
			$user_id = $_SESSION["user_id"];
		else
			$user_id = -1;
		$DB->AddCondFS("user_id", "=", $user_id);
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res))
        {
			$row["name"] .= ".".$row["filename"];
			$DB->SetTable("nsau_files_subj");
			$DB->AddField("subject_id");
			$DB->AddCondFS("file_id", "=", $row["id"]);
			$res_subj_id = $DB->Select();
			
				$i = 0;
				while($row_subj_id = $DB->FetchAssoc($res_subj_id)) {
					$DB->SetTable("nsau_subjects");
					$DB->AddField("name");
					$DB->AddCondFS("id", "=", $row_subj_id["subject_id"]);
					$res_subj_name = $DB->Select();
					$row_subj_name = $DB->FetchAssoc($res_subj_name);
					$row["subjlist"][$i]["id"] = $row_subj_id["subject_id"];
					$row["subjlist"][$i]["name"] = $row_subj_name["name"];
					$i++;
				}
			
            $this->output["files_list"][] = $row;
        }
		$this->output["maxsize"] = ini_get("upload_max_filesize");
	}
	
	
	function attach_file()
	{
		global $DB, $Engine, $Auth;
		$this->files_list();
		
		if ($Engine->OperationAllowed($this->module_id, "files.attach", 0, $Auth->usergroup_id))
			$this->output["allow_attach"] = 1;
		else
			$this->output["allow_attach"] = 0;
		
		$DB->SetTable("nsau_subjects");
		$DB->AddField("id");
		$DB->AddField("name");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res))
        {
            $this->output["subjects_list"][] = $row;
        }
	
	}
	
	function get_file($file_id)
	{
		global $DB, $Engine;
		
		$DB->SetTable("nsau_files");
		$DB->AddField("name");
		$DB->AddField("filename");
		$DB->AddCondFS("id", "=", $file_id);
		$res = $DB->Select();
		$row = $DB->FetchAssoc();

		$str_from = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$file_id.".".$row["filename"];
		$str_to = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$row["name"].".".$row["filename"];

		$filepath = $_SERVER["DOCUMENT_ROOT"]."/".FILES_DIR.$file_id.".".$row["filename"];
		
		
		if (file_exists($filepath)) {

//			die(realpath($str_to));
//			header('Content-Disposition: attachment; filename="'.$row["name"].".".$row["filename"].'"');

//			die(basename($str_to));
			header('Content-Disposition: attachment; filename="'.basename($str_to).'"');
			
//			header('Content-type: application/x-'.$row["filename"].'; name="'.basename($str_to).'"');

			header('Content-type: application/force-download');

			readfile($filepath);
			$Engine->LogAction($this->module_id, "file", $file_id, "download");
		}
		else {
			$Engine->HTTP404();
		}
	}
	
	function Output()
	{
		return $this->output;
	}
	
}
	
?>