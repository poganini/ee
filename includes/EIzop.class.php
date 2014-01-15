<?php

class EIzop
// version: 1.01.11
// date: 2013-10-15
{
	var $output;
	var $node_id;
	var $module_id;
	var $mode;
	function EIzop($global_params, $params, $module_id, $node_id, $module_uri)
	{
		global $Engine, $Auth, $DB;
		
		$this->module_uri = $module_uri;
		$this->module_id = $this->output["module_id"] = $module_id;
		
		//$DB->Exec("SET NAMES 'utf8'");
		$DB->Exec("SET NAMES '" . CODEPAGE_DB . "'");
		
		$parts = explode(";", $global_params);
		$this->db_prefix = $parts[0];
		
		$this->output["messages"] = array("good" => array(), "bad" => array());
		
		if(isset($_SESSION["messages"])) {
			$this->output["messages"] = $_SESSION["messages"];
			unset($_SESSION["messages"]); 
		}
		
		$mode = explode("/", $module_uri);

		$parts = explode(";", $params);
		if(! CF::IsNaturalNumeric($parts[0]) )
		$this->output["mode"] = $this->mode = $parts[0];//echo $module_uri;
		
		switch($this->mode){
			case "show_specialities":
				$this->output["uri"]=$Engine->engine_uri;
				if (isset($parts[1]) && CF::IsNaturalNumeric($parts[1])){
					if($mode[0]){
						$spec_id = $mode[1];
						$spec_type = isset($mode[2]) ? $mode[2] : false;
						$this->output["mode"] = "list_materials"; 
						$this->list_materials($spec_id, $spec_type);
					}
					else {
						$branch_id = $parts[1];
						$spec_type = isset($parts[2]) ? $parts[2] : false;
						$this->list_specialities($branch_id, $spec_type);
					} 
				}
			break;
			case "list":
				$parts2 = explode("/", $module_uri); 				 
				$this->list_entrants($parts2[0]);  
			break;
			case "manage_branches":
				$this->manage_branches($this->module_uri);  
			break;
			case "manage_places":
				$this->manage_places();
			break;
			case "quotas":
				$this->quotas();
			break;
			case "request":
				$this->requests_info();
			break;
			case "ajax_edit_subj":
				$DB->SetTable($this->db_prefix."curriculum");
				$DB->AddCondFS("id", "=", $_POST["edit_cur_id"]);
				if($_POST['action_del']) {
					//$DB->Delete();
					$this->output["curriculum"] = array('id' => $_POST['edit_cur_id']);
					if($DB->Delete()){
						$this->output["curriculum"]['deleted'] = 1;
					}
					else {
						$this->output["curriculum"]['deleted'] = 0; 
					}
				}
				else { 
					$DB->AddValue("subject_id", $_POST["edit_subject_id"]);
					if(!empty($_POST["edit_profile_id"])) $DB->AddValue("profile_id", $_POST["edit_profile_id"]);
					$DB->AddValue("semester", $_POST["edit_semester"]);
					$DB->Update();
					$DB->SetTable($this->db_prefix."curriculum");
					$DB->AddCondFS("id", "=", $_POST["edit_cur_id"]);
					$res = $DB->Select();
					if($row = $DB->FetchAssoc($res)) { 
						$DB->SetTable("nsau_subjects", "s");
						$DB->AddField("name");
						$DB->AddField("department_id");
						$DB->AddCondFS("id", "=", $row["subject_id"]); 
						$res_subj = $DB->Select();
						$row["department"] = ""; 
						if($row_subj = $DB->FetchAssoc($res_subj)) {
							$row["subj_name"] = $this->win2utf($row_subj["name"]); 
							$DB->SetTable("nsau_departments");
							$DB->AddCondFS("id", "=", $row_subj["department_id"]);
							$res_dep = $DB->Select(1); 
							if($row_dep = $DB->FetchAssoc($res_dep))
							{
								$row["department"] = $row_dep["name"]; 
							}
						}
						
						$row["profile_name"] = '';
						$DB->SetTable("nsau_profiles", "p");
						$DB->AddField("name");
						$DB->AddCondFS("id", "=", $row["profile_id"]);
						$res_prof = $DB->Select();
						if($row_prof = $DB->FetchAssoc($res_prof)) {
							$row["profile_name"] = $this->win2utf($row_prof["name"]);
						}
						$this->output["curriculum"] = $row;
					}
				} 
			break; 
			case "ajax_subjects": {
					$this->output["subjects_list"] = array(); 
					$this->output["mode"] = $this->mode;
					$DB->SetTable($this->db_prefix."curriculum");
					$DB->AddCondFS("id", "=", $_REQUEST["data"]["s_id"]);
					$res = $DB->Select(1);
					while($row = $DB->FetchAssoc($res)) {
						$this->output["subject_id"] = $row["subject_id"];
					}
					$DB->SetTable("nsau_subjects", "subj");
					//$DB->AddTable("nsau_departments", "dep");
					//$DB->AddCondFF("subj.department_id","=", "dep.id");
					$DB->AddJoin("nsau_departments.id", "subj.department_id");
					$DB->AddField("subj.id", "sid");
					$DB->AddField("subj.name", "sname");
					$DB->AddField("nsau_departments.id", "did");
					$DB->AddField("nsau_departments.name", "dname");
					$DB->AddExpOrder("(nsau_departments.id is Null)", false);
					$DB->AddOrder("nsau_departments.name");
					$DB->AddOrder("subj.name");
					//echo $DB->SelectQuery();
					$res = $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["subjects_list"][(int)$row["did"]]["name"] = $row["dname"] ? $this->cp1251_to_utf8($row["dname"]) : "Дисциплины, не привязанные к кафедре";
						//$row["dname"] = $this->cp1251_to_utf8($row["dname"]); 
						$this->output["subjects_list"][(int)$row["did"]]["subj"][] = $row;
					}
				}
			break;
			case "ajax_autocomplete_subj": {
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
					if($row["dname"]) $row["name"] = $row["dname"] . " > ".$row["name"];
					$json[] = $row;
				}
					
				$this->output["json"] = $json;
			}
			break;
			case "izop_old":
				$branch = $parts[1];
				$this->output["mode"] = "izop_old"; 
				$spec_id = isset($_GET['spec_id']) ? $_GET['spec_id'] : false;
				$this->izop_old($branch, $spec_id);  
			break; 
			default:
				$spec_type = 0;
				//echo $params;
				//print_r($parts2);
				$code = $parts[0];
				$parts2 = explode(".", $code);
				$code = $parts2[0];
				if(isset($parts2[1])){
					$spec_type = $parts2[1];
				}
				$spec_id = $code;
				
				$DB->SetTable("nsau_specialities");
				$DB->AddCondFS("code", "=", $code);
				//echo $DB->SelectQuery();
				$res = $DB->Select(1);
				while($row = $DB->FetchAssoc($res)){
					//$this->output["spec_name"] = $row["name"];
					$spec_id = $row['id'];
				}
				$this->list_materials($spec_id, $spec_type);
			break;	
		} 
	}

	function list_materials($spec_id, $spec_type = false, $profile_id = 0){
		global $DB, $Auth, $Engine; 
		
		//$this->output["curriculum"] = array();
		
		$DB->SetTable("nsau_specialities");
		$DB->AddCondFS("id", "=", $spec_id);
		//echo $DB->SelectQuery();
		$res = $DB->Select(1);
		while($row = $DB->FetchAssoc($res)){
			$this->output["spec_name"] = $row["name"];
			$code = $row['code'];
			$Engine->AddFootstep($this->module_uri, $row["name"], $row["name"], false, false, $this->module_id);
		}
		
		$DB->AddTable("engine_nodes"); 
		$DB->AddCondFS("params", " LIKE ", "%manage_branches%");
		$DB->AddCondFS("module_id", "=", $this->module_id);
		$DB->AddField("folder_id"); 
		$res = $DB->Select(1);
		while($row = $DB->FetchAssoc($res)) {
			$folder_id = $row["folder_id"];
			$this->output["admin_uri"] = $Engine->FolderURIbyID($folder_id); 
		}

		$this->output["allow_edit"] = $Engine->OperationAllowed($this->module_id, "izop.manage_subjects", $spec_id, $Auth->usergroup_id); 
		
		$this->output["code"] = $code;
		$this->output["spec_type"] = $spec_type;
		$this->output["spec_id"] = $spec_id;
		if(!empty($spec_type)) $this->output["code"] .= ".".$spec_type;
		//exit;
		
		if(empty($profile_id)){
			$DB->SetTable("nsau_profiles");
			$DB->AddCondFS("spec_id", "=", $spec_id);
			//echo $DB->SelectQuery();
			$DB->AddExp("count(*)", "cnt");
			$res = $DB->Select();
			if ($row = $DB->FetchAssoc($res)) {
				if($row["cnt"]){
					$DB->SetTable("nsau_profiles");
					$DB->AddCondFS("spec_id", "=", $spec_id);
					//$DB->ResetFields();
					//echo $DB->SelectQuery(); 
					$res = $DB->Select(); 
					while ($row = $DB->FetchAssoc($res)) {
						$this->list_materials($spec_id, $spec_type, $row["id"]);
					}
					return ;
				}
			}
		}
		else {
			$DB->SetTable("nsau_profiles");
			$DB->AddCondFS("id", "=", $profile_id);
			//echo $DB->SelectQuery();
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res)) {
				$this->output['profiles'][$row['id']] = $row; 
			}
		}
		
		$files = array();
		$approved = array();
		$subjects = array();
		
		if(!isset($this->output['forms'])) $this->output['forms'] = array(); 
		
		//$this->output["curriculum"] = array();
		
		/* $DB->SetTable($this->db_prefix."curriculum", "c");
		$DB->AddTable("nsau_files_subj","s");
		$DB->AddTable("nsau_files","f");
		$DB->AddTable("nsau_file_view", "v");
		$DB->AddTable("nsau_subjects", "p");
		$DB->AddCondFS("c.spec_id", "=", $spec_id);
		$DB->AddCondFF("c.spec_id", "=", "s.spec_id"); 
		$DB->AddCondFF("s.semester", "=", "c.semester");
		$DB->AddCondFF("c.subject_id", "=", "s.subject_id");
		$DB->AddCondFF("p.id", "=", "s.subject_id");
		$DB->AddCondFS("s.approved", "=", 1);
		$DB->AddCondFF("f.id", "=", "s.file_id");
		$DB->AddCondFF("v.id", "=", "s.view_id");
		if(!empty($profile_id)) {
			$DB->AddCondFF("c.profile_id", "=", "s.profile_id"); 
			$DB->AddCondFS("s.profile_id", "=", $profile_id); 
		}
		$DB->AddField("f.id");
		$DB->AddField("f.name");
		$DB->AddField("s.spec_type");
		$DB->AddField("s.spec_id");
		$DB->AddField("f.descr");
		$DB->AddField("c.profile_id");
		$DB->AddField("c.semester");
		$DB->AddField("v.view_name","v_n");
		$DB->AddField("v.id","view_id");
		$DB->AddField("p.id","p_id");
		$DB->AddField("p.name","p_name");
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)) {
			$this->output["curriculum"][(int)$row['profile_id']][$row["semester"]][$row["p_id"]]["name"] = $row["p_name"]; 
			$this->output["curriculum"][(int)$row['profile_id']][$row["semester"]][$row["p_id"]]["views"][$row["view_id"]]["name"] = $row["v_n"]; 
			$this->output["curriculum"][(int)$row['profile_id']][$row["semester"]][$row["p_id"]]["views"][$row["view_id"]]["files"][$row["id"]] = $row;
		} */
		
		//$this->output["curriculum"] = array();
		
		$DB->SetTable($this->db_prefix."curriculum", "c");
		$DB->AddTable("nsau_subjects", "s");
		$DB->AddJoin("nsau_departments.id", "s.department_id");
		$DB->AddFields(array("c.id", "c.subject_id", "c.spec_id", "c.profile_id", "c.semester", "s.name", "c.form"));
		$DB->AddField("nsau_departments.name", "dname"); 
		$DB->AddCondFS("c.spec_id", "=", $spec_id);
		//$DB->AddCondFF("c.spec_id", "=", "s.spec_id");
		//$DB->AddCondFF("s.semester", "=", "c.semester");
		$DB->AddCondFF("c.subject_id", "=", "s.id");
		if(!empty($profile_id)) {
			//$DB->AddCondFF("c.profile_id", "=", "s.profile_id");
			$DB->AddCondFS("c.profile_id", "=", $profile_id);
		}
		
		$DB->AddOrder("c.semester"); 
		//echo $DB->SelectQuery()."<br />\n";
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)) {
			$DB->SetTable("nsau_files","f");
			$DB->AddTable("nsau_files_subj","s");
			//$DB->AddJoin("nsau_subjects.id", "s.subject_id");
			//$DB->AddTable("nsau_subjects", "sub");
			$DB->AddTable("nsau_file_view", "v");
			$DB->AddCondFS("s.subject_id", "=", $row["subject_id"]);
			$DB->AddCondFS("s.spec_id", "=", $row["spec_id"]);
			$DB->AddCondFS("s.semester", "=", $row["semester"]);
			if(!empty($profile_id)) 
				$DB->AddCondFS("s.profile_id", "=", $row["profile_id"]); 
			$DB->AddCondFF("v.id", "=", "s.view_id");
			$DB->AddCondFF("f.id", "=", "s.file_id");
			//$DB->AddCondFF("sub.id", "=", "s.subject_id");
			$DB->AddCondFS("s.approved", "=", 1);
			$DB->AddField("f.id"); 
			$DB->AddFields("f.name");
			$DB->AddField("f.descr"); 
			$DB->AddField("v.view_name","v_name");
			$DB->AddField("v.id","view_id");
			$DB->AddField("s.subject_id"); 
			$DB->AddOrder("s.semester"); 
			//$DB->AddField("sub.name", "sname"); 
			//echo $DB->SelectQuery();
			$res_file = $DB->Select();
			while($row_file = $DB->FetchAssoc($res_file)) {
				//$row[$row_file["subject_id"]][$row_file["view_id"]][$row_file["id"]] = $row_file;
				$row["views"][$row_file["view_id"]]["name"] = $row_file["v_name"]; 
				$row["views"][$row_file["view_id"]]["files"][$row_file["id"]] = $row_file;
			}

			$this->output["forms"][(int)$row['profile_id']][$row['form']] = $row['form'];
			
			$this->output["curriculum"][(int)$row['profile_id']][$row['form']][$row["semester"]][$row["subject_id"]] = $row;
		} 
		
		$DB->SetTable($this->db_prefix."spec_curriculums", "c");
		$DB->AddTable("nsau_files", "f");
		$DB->AddField("f.id"); 
		$DB->AddField("f.descr");
		$DB->AddFields(array("c.form", "c.profile_id"));
		$DB->AddCondFS("c.spec_id", "=", $spec_id);
		$DB->AddCondFF("c.file_id", "=", "f.id");
		//echo $DB->SelectQuery();  

		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)) {
			$curriculums[$row["profile_id"]][] = $row; 
		}
		
		$DB->SetTable("nsau_subjects","p");
		$DB->AddField("p.id","p_id");
		$DB->AddField("p.name","p_n");
		$DB->AddTable("nsau_files", "ptr");
		$DB->AddFields(array("ptr.id","ptr.name", "ptr.descr"));
		$DB->AddTable("nsau_files_subj","s");
		$DB->AddFields(array("s.file_id","s.spec_code","s.spec_type","s.subject_id", "s.education", "s.spec_id","s.profile_id","s.semester","s.approved"));
		$DB->AddTable("nsau_file_view", "v");
		$DB->AddField("v.view_name","v_n");
		$DB->AddField("v.id","view_id");
		//$DB->AddField("s.profile_id");

		$DB->AddCondFS("s.approved", "=", "1");
		//$DB->AddCondFS("s.approved", "!=", "-1");
		//$DB->AddCondFN("s.semester");
		$DB->AddCondFS("s.spec_id", "=", $spec_id);
		$DB->AddCondFS("s.education", "=", "Заочная");
		$DB->AddCondFF("s.file_id", "=", "ptr.id");
		$DB->AddCondFF("s.subject_id", "=", "p.id");
		$DB->AddCondFF("s.view_id", "=", "v.id");
		if(!empty($spec_type)){
			$DB->AddCondFS("s.spec_type", "=", $spec_type);
		}
		$DB->AddOrder("s.semester");
		$DB->AddOrder("ptr.id");
		$DB->AddOrder("p.id");
		$DB->AddOrder("v.id");
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)){
			$code1[]=$row;
			//print_r($row);
			$this->output["files_list"][] = $row;
			$this->output["views"][$row['view_id']] = $row['v_n'];
			$this->output["subjects"][$row['p_id']] = $row['p_n'];
			$approved[(int)$row['profile_id']][(int)$row['semester']][$row['subject_id']] += $row['approved'];
			$types[(int)$row['profile_id']][(int)$row['semester']][$row['subject_id']] = $row['view_id'];
			$files[(int)$row['profile_id']][(int)$row['semester']][$row['subject_id']]["name"] = $row["p_n"]; 
			$files[(int)$row['profile_id']][(int)$row['semester']][$row['subject_id']]["views"][$row["view_id"]]["name"] = $row["v_n"]; 
			$files[(int)$row['profile_id']][(int)$row['semester']][$row['subject_id']][$row["view_id"]][$row['id']] = $row;
			$files[(int)$row['profile_id']][(int)$row['semester']][$row['subject_id']]["views"][$row["view_id"]]["files"][$row['id']] = $row;
		}
		
		// TODO: сделать разбиение материалов по профилям
		$this->output['files'] = $files; 
		$this->output['approved'] = $approved;
		$this->output['types'] = $types; 
		$this->output['curriculums'] = $curriculums;
		//exit;
	}

	function list_specialities($branch_id, $spec_type = false){
		global $DB, $Auth;
		
		$spec_type_codes = array(
				'51'=>'Среднее професиональное образование',
				'68'=>'Магистратура',
				'62'=>'Бакалавриат',
				'65'=>'Специалитет'
			);
		
		$this->output["specialities"] = array();
		
		$DB->SetTable($this->db_prefix . "branches", "b");
		$DB->AddTable($this->db_prefix . "branches_specs", "bs");
		$DB->AddTable("nsau_specialities", "s");
		$DB->AddFields(array("s.id", "s.code", "s.name"));
		$DB->AddTable("nsau_spec_type","st");
		//$DB->AddFields(array("st.type", "st.type_code"));
		$DB->AddField("st.type_code", "spec_type"); 
		$DB->AddCondFS("b.id", "=", $branch_id);
		$DB->AddCondFF("b.id", "=", "bs.branch_id"); 
		$DB->AddCondFF("s.id", "=", "bs.spec_id"); 
		$DB->AddCondFF("s.id","=","st.spec_id");
		//$DB->AddCondFF("bs.spec_type", "=", "st.type_code");
		$DB->AddCondFX("st.type_code", "IN", "(62, 65)");
		$DB->AddAltFS("st.type_code", "=", 62);
		$DB->AddAltFS("st.type_code", "=", 65);
		$DB->AppendAlts(); 
		if(!empty($spec_type)) 
			$DB->AddCondFS("st.type_code", "=", $spec_type); 
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$this->output["specialities"][$row["spec_type"]][] = $row;
		}

		$this->output["spec_type_codes"] = $spec_type_codes;
	}
	
	function manage_branches($module_uri){
		global $DB, $Auth, $Engine;
		
		$pars = explode('/', $module_uri);
		
		$this->output["branches"] = array();
		
		if(isset($_POST["branch_name"]) && isset($_POST["mode"]) && $_POST["mode"] == "add") {
			$DB->SetTable($this->db_prefix."branches");
			$DB->AddCondFS("name", "=", $_POST["branch_name"]); 
			$res = $DB->Select(); 
			if(!($row = $DB->FetchAssoc($res))) {
				$DB->SetTable($this->db_prefix."branches");
				$DB->AddValue("name", $_POST["branch_name"]);

				$DB->Insert();
			}
			CF::Redirect($Engine->engine_uri);
		}
		elseif(isset($pars[0], $pars[1]) && !empty($pars[0]) && $pars[0] == "edit_branch" && CF::IsNaturalNumeric($pars[1])){
			$this->output["sub_mode"] = "edit_branch";
			$this->edit_branch($pars[1], $module_uri);
		}
		elseif(isset($pars[0], $pars[1]) && !empty($pars[0]) && $pars[0] == "del_branch_spec" && CF::IsNaturalNumeric($pars[1])){
			$DB->SetTable($this->db_prefix."branches_specs");
			$DB->AddCondFS("id", "=", $pars[1]); 
			if($DB->Delete()) {
				$this->output["messages"]["good"][] = "Специальность удалена с отделения"; 
			} else {
				$this->output["messages"]["bad"][] = "Специальность не удалена с отделения"; 
			}
			
			$_SESSION["messages"] = $this->output["messages"];
			$Engine->LogAction($this->module_id, "branch_spec", $pars[1], "delete_branch_spec");
			CF::Redirect($Engine->engine_uri);
		}
		elseif(isset($pars[0], $pars[1]) && !empty($pars[0]) && $pars[0] == "del_branch" && CF::IsNaturalNumeric($pars[1])){
			$DB->SetTable($this->db_prefix."branches");
			$DB->AddCondFS("id", "=", $pars[1]);
			if($DB->Delete()) {
				$DB->SetTable($this->db_prefix."branches_specs");
				$DB->AddCondFS("branch_id", "=", $pars[1]);
				if($DB->Delete()){
					$this->output["messages"]["good"][] = "Отделение успешно удалено.";
				}
				else {
					$this->output["messages"]["bad"][] = "Отделение не удалено. ";
				}
			}
			else {
				$this->output["messages"]["bad"][] = "Отделение не удалено. ";
			}
			$_SESSION["messages"] = $this->output["messages"]; 
			$Engine->LogAction($this->module_id, "branch", $pars[1], "delete");
			CF::Redirect($Engine->engine_uri);
		}
		elseif(isset($pars[0], $pars[1]) && !empty($pars[0]) && $pars[0] == "edit_curriculum" && CF::IsNaturalNumeric($pars[1])){
			$this->edit_curriculum($pars[1], $module_uri);			
		}
		elseif(isset($pars[0], $pars[1]) && !empty($pars[0]) && $pars[0] == "download_curriculum" && CF::IsNaturalNumeric($pars[1])){
			$DB->SetTable($this->db_prefix."curriculum", "c");
			$DB->AddTable("nsau_subjects", "s");
			$DB->AddCondFS("c.spec_id", "=", $pars[1]);
			$DB->AddCondFF("c.subject_id", "=", "s.id");
			//echo $DB->SelectQuery(); 
			header('Content-Disposition: attachment; filename="curriculum'.date('Ymdhi').'.csv"');
			header('Content-type: application/octet-stream');
			$res = $DB->Select();
			echo "Дисциплина;Семестр;Профиль\n";
			while ($row = $DB->FetchAssoc($res)) {
				echo $row["name"].";".$row["semester"]; 
				if($row["profile_id"]){
					$DB->SetTable("nsau_profiles");
					$DB->AddCondFS("id", "=", $row["profile_id"]); 
					$res_prof = $DB->Select(1); 
					while($row_prof = $DB->FetchAssoc($res_prof)) {
						echo ";".$row_prof["name"];
					}
				}
				echo "\n";
			}
			exit; 
		}
		else {
			$DB->SetTable($this->db_prefix."branches");
			$res = $DB->Select();
			while($row = $DB->FetchAssoc($res)) {
				$this->output["branches"][$row["id"]] = $row; 
				$DB->SetTable($this->db_prefix."branches_specs", "bs");
				$DB->AddTable("nsau_specialities", "s");
				$DB->AddTable("nsau_spec_type", "st");
				$DB->AddCondFF("s.id", "=", "bs.spec_id");
				$DB->AddCondFS("bs.branch_id", "=", $row["id"]);
				$DB->AddField("bs.id", "branch_spec_id");
				$DB->AddCondFF("st.spec_id", "=", "s.id"); 
				$DB->AddCondFX("st.type_code", "IN", "(62,65)");  
				$DB->AddFields(array("s.id","s.name", "s.code", "st.type_code"));			
				//echo $DB->SelectQuery();
				$res_spec = $DB->Select();
				while($row_spec = $DB->FetchAssoc($res_spec)) {
					$row_spec["allow_edit"] = $Engine->OperationAllowed($this->module_id, "izop.manage_subjects", $row["id"], $Auth->usergroup_id);
					$this->output["branches"][$row["id"]]["specialities"][$row_spec["id"]] = $row_spec;
				}
			}
		}
	}
	
	function edit_branch($branch_id) {
		global $DB, $Auth, $Engine;
		$DB->SetTable($this->db_prefix."branches");
		$DB->AddCondFS("id", "=", $branch_id); 
		$res = $DB->Select(); 
		if($row = $DB->FetchAssoc($res)) {
			$this->output["branch"] = $row;
			$Engine->AddFootstep($Engine->unqueried_uri, $row["name"], "Редактирование отделения", false, false, $this->module_id);
		}
		
		$DB->SetTable("nsau_specialities");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$this->output["specialities"][$row["id"]] = $row;
		}
		$DB->SetTable($this->db_prefix."branches_specs", "bs");
		$DB->AddTable("nsau_specialities", "s");
		$DB->AddCondFF("s.id", "=", "bs.spec_id");
		$DB->AddCondFS("bs.branch_id", "=", $branch_id);
		$DB->AddField("bs.id", "branch_spec_id");
		$DB->AddFields(array("s.id","s.name", "s.code"));
		//echo $DB->SelectQuery(); 
		$res_spec = $DB->Select();
		while($row_spec = $DB->FetchAssoc($res_spec)) {
			$this->output["specialities"][$row_spec["id"]]["selected"] = 1; 
		}

		if (!empty($_POST["id"])) {
			$DB->SetTable($this->db_prefix."branches");
			$DB->AddValue("name", $_POST["branch_name"]); 
			$DB->AddCondFS("id", "=", $_POST["id"]);
			$DB->Update();

			$DB->SetTable($this->db_prefix."branches_specs");
			$DB->AddCondFS("branch_id", "=", $_POST["id"]);
			$DB->Delete(); 
			
			if(!empty($_POST["spec"])) {
				foreach($_POST["spec"] as $spec_id) {
					$DB->SetTable($this->db_prefix."branches_specs");
					$DB->AddValue("branch_id", $_POST["id"]);
					$DB->AddValue("spec_id", $spec_id);
					//echo $DB->InsertQuery();
					$DB->Insert();
				}
			}
			
			$Engine->LogAction($this->module_id, "branch", $branch_id, "update_branch");
			CF::Redirect($Engine->engine_uri);
		}
	}
	
	function manage_places()
	{
		global $DB, $Auth, $Engine;
		if(isset($_POST["id"], $_POST["budget"], $_POST["purpose"], $_POST["commerce"], $_POST["mode"]) && $_POST["mode"] == "edit"){
			foreach($_POST["id"] as $id) {
				$DB->SetTable($this->db_prefix."types");
				$DB->AddCondFS("id", "=", $id); 
				//$DB->AddValue($field, $val)
				$DB->AddValues(array("code" => $_POST["code"][$id], "type" => $_POST["type"][$id])); 
				if(!empty($_POST["budget"][$id])) $DB->AddValue("budget", $_POST["budget"][$id]);
				if(!empty($_POST["purpose"][$id])) $DB->AddValue("purpose", $_POST["purpose"][$id]);
				if(!empty($_POST["commerce"][$id])) $DB->AddValue("commerce", $_POST["commerce"][$id]);
				if(!empty($_POST["sortorder"][$id])) $DB->AddValue("sortorder", $_POST["sortorder"][$id]);
				$DB->AddValue("pid", $_POST["parent"][$id]);
				//echo $DB->UpdateQuery();exit;
				$DB->Update();  
			}
			CF::Redirect($Engine->engine_uri);
		}
		if(isset($_POST["mode"]) && $_POST["mode"] == "add") {
			$DB->SetTable($this->db_prefix."types");
			$DB->AddValues(array("code" => $_POST["code"], "type" => $_POST["type"]));
			if(!empty($_POST["budget"])) $DB->AddValue("budget", $_POST["budget"]);
			if(!empty($_POST["purpose"])) $DB->AddValue("purpose", $_POST["purpose"]);
			if(!empty($_POST["commerce"])) $DB->AddValue("commerce", $_POST["commerce"]);
			if(!empty($_POST["sortorder"])) $DB->AddValue("sortorder", $_POST["sortorder"]);
			$DB->AddValue("pid", $_POST["parent"]);
			  
			if($DB->Insert()) {
				CF::Redirect($Engine->engine_uri);
			}
			else {
				$this->output["messages"]["bad"][] = "Не удалось добавить направление"; 
			}
		}
		$DB->SetTable($this->db_prefix."types");
		$DB->AddOrder("sortorder");
		$DB->AddOrder("pid");
		//$DB->AddOrder("type");
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$this->output["types"][] = $row; 
		}
		$this->output["uri"] = $Engine->FolderURIById();
	}
	
	function edit_curriculum($spec_id) {
		global $DB, $Auth, $Engine;
		
		if(!$Engine->OperationAllowed($this->module_id, "izop.manage_subjects", $spec_id, $Auth->usergroup_id)) {
			$Engine->HTTP403(); 
		}
		
		$this->output["scripts_mode"] = "curriculum";
		$this->output["plugins"][] = "jquery.ui.autocomplete.min";
		
		$this->output["form_back"] = array();
		
		$this->output['forms'] = array('Полная', 'Ускоренная');
		 
		if(!empty($_POST)) {
			$this->save_curriculum($spec_id); 
		}
		
		if(isset($_SESSION[$this->mode]["form_back"])) {
			$this->output["form_back"] = array_merge($_SESSION[$this->mode]["form_back"], $this->output["form_back"]);  
		} 
			
		$DB->SetTable("nsau_specialities");
		$DB->AddCondFS("id", "=", $spec_id);
		$res = $DB->Select();
		if($row = $DB->FetchAssoc($res)) {
			$this->output['spec'] = $row;
			$Engine->AddFootstep($this->engine_uri, $row["name"], "Редактирование учебного плана специальности \"".$row["name"]."\"", false, false, $this->module_id);
		}		
		
		$this->output["files"] = array();
		$DB->SetTable("nsau_files");
		$DB->AddCondFS("user_id", "=", $Auth->user_id);
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$this->output['files'][$row['id']] = $row;
		}
		
		$curriculum_files = array();
		
		$DB->SetTable($this->db_prefix."spec_curriculums", "c");
		$DB->AddTable("nsau_files", "f");
		$DB->AddField("f.id");
		$DB->AddField("f.descr");
		$DB->AddExp("f.*");
		$DB->AddFields(array("c.profile_id", "c.form"));
		$DB->AddCondFS("c.spec_id", "=", $spec_id);
		$DB->AddCondFF("c.file_id", "=", "f.id");
		//echo $DB->SelectQuery();
		
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)) {
			$curriculum_files[$row["profile_id"]][$row["form"]]= $row["id"];
			$this->output['files'][$row['id']] = $row;
		}
		
		$this->output["curriculum_files"] = $curriculum_files;
			
		$DB->SetTable("nsau_profiles");
		$DB->AddCondFS("spec_id", "=", $spec_id);
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$this->output['profiles'][$row['id']] = $row;
		}
			
		$DB->SetTable($this->db_prefix."curriculum", "c");
		$DB->AddTable("nsau_subjects", "s");
		$DB->AddCondFS("c.spec_id", "=", $spec_id);
		$DB->AddCondFF("c.subject_id", "=", "s.id");
		$DB->AddExp("c.*");
		$DB->AddFields(array("s.name", "s.department_id"));
		$DB->AddOrder("c.semester");
		$DB->AddOrder("c.id");
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			if(!empty($row["profile_id"]) && !isset($this->output["curriculum"]["profiles"][$row["profile_id"]])) {
				$this->output["curriculum"]["profiles"][$row['profile_id']] = $this->output['profiles'][$row['profile_id']];
			}
		
			$this->output["curriculum"]["profiles"][$row["profile_id"]]['title'] = '';
		
			/* if(!empty($row["profile_id"])) */ {
				$DB->SetTable("nsau_departments");
				$DB->AddCondFS("id", "=", $row["department_id"]);
				//echo $DB->SelectQuery();
				$res_dep = $DB->Select();
				if($row_dep = $DB->FetchAssoc($res_dep)) {
					$row['department'] = $row_dep['name'];
				}
			}
		
			$DB->SetTable("nsau_files","f");
			$DB->AddTable("nsau_files_subj","s");
			$DB->AddTable("nsau_file_view", "v");
			$DB->AddCondFS("s.subject_id", "=", $row["subject_id"]);
			$DB->AddCondFS("s.spec_id", "=", $row["spec_id"]);
			$DB->AddCondFS("s.semester", "=", $row["semester"]);
			if(!empty($row["profile_id"]))
				$DB->AddCondFS("s.profile_id", "=", $row["profile_id"]);
			$DB->AddCondFF("v.id", "=", "s.view_id");
			$DB->AddCondFF("f.id", "=", "s.file_id");
			$DB->AddCondFS("s.approved", "=", 1);
			/*$DB->AddField("f.id");
			 $DB->AddFields("f.name");
			$DB->AddField("f.descr");
			$DB->AddField("v.view_name","v_name");
			$DB->AddField("v.id","view_id");
			$DB->AddField("s.subject_id");*/
			$DB->AddExp("count(*)", "num");
			//echo $DB->SelectQuery();
			$res_num = $DB->Select();
			if($row_num = $DB->FetchArray()) {
				$row['num_files'] = $row_num[0];
			}
		
			$this->output['curriculum']["profiles"][$row['profile_id']]['forms'][$row["form"]]['subjects'][$row['id']] = $row;
		}
			
		$DB->SetTable("nsau_subjects", "s");
		$DB->AddTable("nsau_departments", "d");
		$DB->AddCondFF("s.department_id", "=", "d.id");
		$DB->AddField("s.name", "sname");
		$DB->AddField("s.id", "sid");
		$DB->AddField("d.name", "dname");
		$DB->AddField("d.id", "did");
		$DB->AddOrder("d.name");
		$DB->AddOrder("s.name");
		$DB->AddCondFP("d.is_active");
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)) {
			$this->output['curriculum']['departments'][$row['did']]['name'] = $row['dname'];
			$this->output['curriculum']['departments'][$row['did']]['subjects'][$row['sid']] = $row['sname'];
		}
			
		$DB->SetTable("nsau_subjects");
		$DB->AddCondFS("department_id", "=", 0);
		$DB->AddOrder("name");
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)) {
			$this->output['curriculum']['departments'][0]['name'] = 'Дисциплины, не привязанные к кафедре';
			$this->output['curriculum']['departments'][0]['subjects'][$row['id']] = $row['name'];
		}
	}
	
	function save_curriculum($spec_id) {
		global $DB, $Auth, $Engine;
		
		if(isset($_POST["mode"])) {
			$error = false;
			//$DB->SetTable($this->db_prefix."curriculum");
			$query = "";
			$inserts = array();
			if($_POST["mode"] == "upload")
			{
				//print_r($_FILES);
				$toobad = 0;
				if(is_uploaded_file($_FILES['csvfile']['tmp_name'])) {
					$fname = explode(".", $_FILES['csvfile']['name']);
					if ($fname[count($fname)-1] != "csv") {
						$this->output["messages"]["bad"][] = "Необходим файл в формате csv";
					}
					else {
		
						$DB->SetTable($this->db_prefix."curriculum");
						$DB->AddCondFS("spec_id", "=", $_POST["id"]);
						if(!empty($_POST["profile_id"]))
							$DB->AddCondFS("profile_id", "=", $_POST["profile_id"]);
						if(!empty($_POST["form"]))
							$DB->AddCondFS("form", "=", $_POST["form"]);
						$DB->Delete();
							
						$f = fopen($_FILES['csvfile']['tmp_name'], "r");
						$content = fread($f, filesize($_FILES['csvfile']['tmp_name']));
						$parts = explode("\n", $content);
						$headers = explode(";", $parts[0]);
						array_shift($parts); //print_r($parts);
						foreach ($parts as $ind => $part) {
							$string = explode(";", $part);//print_r($string);
							/* if ((count($string) < 2 || count($string) > 3 ) && !$toobad) {
							 $this->output["mess"]['curriculum'][] = "Неверное количество полей: ".count($string)." вместо 2 или 3. Строка $ind: ".implode(";", $string);
							$toobad = 1;
							}
							else  */ {
							//print_r($string); //exit;
		
							$string = array_map("trim", $string);
							list($subj_name, $semester) = $string;//echo $subj_name;exit;
							if(empty($subj_name)) continue;
							//
							$subj_name = preg_replace("/^\d+\.+\s*/", "", $subj_name);
							//echo $subj_name;exit;
							$profile = '';
							if(count($string) == 3) $profile = $string[2];
							$DB->SetTable("nsau_subjects");
							$DB->AddCondFS("name", "=", $subj_name);
							$DB->AddExp("count(*)","num");
							//echo $DB->SelectQuery();
		
							$res = $DB->Select(null, null, false, false);
							if($row = $DB->FetchAssoc($res)){
								$count = $row['num'];
								if(!$count /*&&  $Engine->OperationAllowed(5, "subjects.department.handle", -1, $Auth->usergroup_id) */) {
									$DB->Exec("SET FOREIGN_KEY_CHECKS=0;");
									$DB->SetTable("nsau_subjects");
									$DB->AddValue("name", $subj_name);
									$DB->AddValue("department_id", 0);
									//echo $DB->InsertQuery();exit;
									$DB->Insert();
								}
							}
		
							$DB->ResetFields();
							$DB->SetTable("nsau_subjects");
							$DB->AddCondFS("name", "=", $subj_name);
							//$DB->SetTable("nsau_subjects");
							//$DB->AddCondFS("name", "=", $subj_name);
							//echo $DB->SelectQuery();exit;
							$res = $DB->Select();
		
							while($row = $DB->FetchAssoc($res)) {
								$DB->SetTable($this->db_prefix."curriculum");
								$DB->AddValue("subject_id", $row['id']);
								$DB->AddValue("spec_id", $_POST["id"]);
								$DB->AddValue("form", $_POST["form"]);
								$DB->AddValue("semester", $semester);
								if($profile){
									//$DB->SetTable("nsau_profiles");
									//$DB->AddField("id");
									//$DB->AddCondFS("name", "=", $profile);
									$res_prof = $DB->Exec("SELECT id FROM nsau_profiles WHERE `name`='".$profile."' LIMIT 1", true);//$DB->Select(1);
									while($row_prof = $DB->FetchAssoc($res_prof)) {
										$DB->AddValue("profile_id", $row_prof["id"]);
									}
								}
								elseif(!empty($_POST["profile_id"])) {
									$DB->AddValue("profile_id", $_POST["profile_id"]);
								}
		
								//echo $DB->InsertQuery(true);
								$DB->Insert(true,true);
							}
							//exit;
							}
						}
						if($toobad) $error = true;
					}
				}
				else {
					$error = true;
					$this->output["mess"]['curriculum'][] = "Не выбран файл.";
				}
				if($error) {
					if(!empty($_POST["semester"])) $this->output["form_back"]["semester"] = $_POST["semester"];
					if(!empty($_POST["form"])) $this->output["form_back"]["form"] = $_POST["form"];
					if(!empty($_POST["profile_id"])) $this->output["form_back"]["profile_id"] = $_POST["profile_id"];
					foreach($_POST['subject_id'] as $subject_id) {
						$this->output["form_back"]['add_subject']['select_subject'][$subject_id] = $_POST['subject_code'][$subject_id];
					}
				}
				else {
					$Engine->LogAction($this->module_id, "curriculum", $spec_id, "upload_curriculum");
					$this->output["form_back"]["semester"] = $_POST["semester"];
					$this->output["form_back"]["form"] = $_POST["form"];
					$_SESSION[$this->mode]["form_back"] = $this->output["form_back"];
					CF::Redirect($Engine->engine_uri."edit_curriculum/".$spec_id);
				}
			}
			elseif($_POST["mode"] == "add_curriculum_files") {
				foreach($_POST["curriculum_file"] as $profile_id => $form_files) {
					foreach($form_files as $form => $file_id) {
						$DB->SetTable($this->db_prefix."spec_curriculums");
						$DB->AddCondFS("spec_id", "=", $spec_id);
						$DB->AddCondFS("profile_id", "=", $profile_id);
						$DB->AddCondFS("form", "=", $form);
		
						//echo $DB->DeleteQuery();
						$DB->Delete(NULL, NULL, false);
		
						if($file_id != 0) {
							$DB->AddValues(array(
									"spec_id" => $spec_id,
									"profile_id" => $profile_id,
									"form" => $form,
									"file_id" => $file_id
							));
							///echo $DB->InsertQuery();
							$DB->Insert();
						}
					}
				}
				//exit;
				$this->output["messages"]["good"][] = "План-графики успешно обновлены";
				$_SESSION["messages"] = $this->output["messages"];
				$Engine->LogAction($this->module_id, "curriculum_files", $spec_id, "update_curriculum_files");
				CF::Redirect($Engine->engine_uri."edit_curriculum/".$spec_id);
			}
			else {
				if(!empty($_POST["subject_id"])) {
					foreach($_POST["subject_id"] as $subject_id) {
						$DB->Init();
						$DB->SetTable($this->db_prefix."curriculum");
						$DB->AddValue("spec_id", $_POST["id"]);
						$DB->AddValue("subject_id", $subject_id);
						$DB->AddValue("semester", $_POST["semester"]);
						if(!empty($_POST["profile_id"])) {
							$DB->AddValue("profile_id", $_POST["profile_id"]);
						}
						if(!empty($_POST["form"])) {
							$DB->AddValue("form", $_POST["form"]);
						}
						//$query .= $DB->InsertQuery().";\n";
						$inserts[] = "('".$_POST["id"]."', '".$subject_id."', '".$_POST["semester"]."'".(!empty($_POST["profile_id"]) ? ", '".(int)$_POST["profile_id"]."'" : "").", '".$_POST["form"]."')";
						/*$res = $DB->Insert();
						 if(!$res) {
						$error = true;
						$this->output["mess"]['curriculum'][] = "Вставка не удалась. Возможно такая дисциплина уже присутствует.";
						break;
						}*/
					}
		
					$query = "INSERT INTO ".$this->db_prefix."curriculum (`spec_id`, `subject_id`, `semester`".(!empty($_POST["profile_id"]) ? ", `profile_id`" : "").", `form`) VALUES ".implode(",\n ", $inserts);
					//echo $DB->InsertQuery();
					//echo $query;
					//exit;
					$res = $DB->Exec($query, false, true);
					if(!$res) {
						$this->output["mess"]['curriculum'][] = "Вставка не удалась. Возможно такая дисциплина уже присутствует.";
					}
					else {
						$this->output["form_back"]["semester"] = $_POST["semester"];
						$this->output["form_back"]["form"] = $_POST["form"];
						$_SESSION[$this->mode]["form_back"] = $this->output["form_back"];
						$Engine->LogAction($this->module_id, "spec_subjects", $spec_id, "update_curriculum");
						CF::Redirect($Engine->engine_uri."edit_curriculum/".$_POST["id"]);
					}
				}
				else {
					$error = true;
					$this->output["mess"]['curriculum'][] = "Не выбранны дисциплины.";
				}
			}
			if($error) {
				$this->output["form_back"]["semester"] = $_POST["semester"];
				if(!empty($_POST["form"])) $this->output["form_back"]["form"] = $_POST["form"];
				if(!empty($_POST["profile_id"])) $this->output["form_back"]["profile_id"] = $_POST["profile_id"];
				foreach($_POST['subject_id'] as $subject_id) {
					$this->output["form_back"]['add_subject']['select_subject'][$subject_id] = $_POST['subject_code'][$subject_id];
				}
			}
		}
		elseif(!empty($_POST['edit_cur_id'])) {
			$DB->SetTable($this->db_prefix."curriculum");
			$DB->AddCondFS("id", "=", $_POST["edit_cur_id"]);
			if($_POST['action_del']) {
				//$DB->Delete();
				$this->output["curriculum"] = array('id' => $_POST['edit_cur_id']);
				if(!$DB->Delete()){
					$this->output["messages"]["bad"][] = "Дисциплина не удалена. ";
				}
				else {
					CF::Redirect($Engine->engine_uri."edit_curriculum/".$spec_id);
				}
			}
			else {
				$DB->AddValue("subject_id", $_POST["edit_subject_id"]);
				if(!empty($_POST["edit_profile_id"])) $DB->AddValue("profile_id", $_POST["edit_profile_id"]);
				$DB->AddValue("semester", $_POST["edit_semester"]);
				if(!$DB->Update()){
					$this->output["messages"]["bad"][] = "Дисциплина не обновлена. ";
				}
				else {
					CF::Redirect($Engine->engine_uri."edit_curriculum/".$spec_id);
				}
			}
		}
	}
	
	function requests_info(){
		global $DB, $Auth, $Engine;
		/* $DB->SetTable($this->db_prefix."types");
		//$DB->AddTable("nsau_spec_abit", "a");
		$DB->AddJoin("nsau_spec_abit.type_id", "id");
		$DB->AddFields(array("id", "code", "type", "budget", "commerce", "purpose")); 
		$DB->AddExp("count(*) as req_count");
		$DB->AddCondFS("nsau_spec_abit.form", "=", "заочная");
		$DB->AddCondFS("pid", "=", 0);
		$DB->AddGrouping("id");
		$DB->AddOrder($this->db_prefix."types.sortorder");
		$DB->AddOrder($this->db_prefix."types.pid");
		//echo $DB->SelectQuery();
		$summary = array("total" => 0, "budget" => 0, "purpose" => 0, "commerce" => 0, "req_count" => 0);
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$row["total"] = (int)$row["budget"] + (int)$row["commerce"];
			if(!empty($row["budget"])) 
			$row["contest"] = round($row["req_count"]/$row["budget"], 2);
			else $row["contest"] = 0;
			if($row["pid"] == 0)
				foreach($summary as $key=>$value){
				$summary[$key] += (int)$row[$key];
			}
			$this->output["types"][] = $row;
		}
		$summary["type"] = "ИТОГО";
		$summary["id"] = 0;
		$this->output["types"][] = $summary; */
		$DB->SetTable($this->db_prefix."types");
		$DB->AddOrder("sortorder");
		$DB->AddOrder("pid");
		$DB->AddCondFS("pid", "=", 0);
		$types = array();
		$summary = array("total" => 0, "budget" => 0, "purpose" => 0, "commerce" => 0, "req_count" => 0);
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$row["req_count"] = 0;
			$row["contest"] = 0;
			if($row["pid"] == 0)
				foreach($summary as $key=>$value){
				$summary[$key] += (int)$row[$key];
			}
			$types[$row["id"]] = $row; 
		}
		
		$DB->SetTable("nsau_spec_abit"); 
		$DB->AddField("type_id"); 
		$DB->AddExp("count(*) as req_count");
		$DB->AddCondFS("form", "=", "заочная");
		$DB->AddCondFS("qualification", "!=", "51");
		$DB->AddGrouping("type_id");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$types[$row["type_id"]]["req_count"] = $row["req_count"];
			$summary["req_count"] += $row["req_count"]; 
			if($types[$row["type_id"]]["budget"])
			$types[$row["type_id"]]["contest"] = round($types[$row["type_id"]]["req_count"]/$types[$row["type_id"]]["budget"], 2);
		}
		$summary["type"] = "ИТОГО";
		$summary["id"] = 0;
		$summary["contest"] = round($summary["req_count"]/$summary["budget"], 2);
		$this->output["types"] = $types;
		//$this->output["types"][0] = $summary; 
		$this->output["summary"] = $summary; 
		
		$this->output["date"] = $this->getUpdateDate(); 
	}
	
	function quotas(){
		global $DB, $Auth, $Engine;
		$DB->SetTable($this->db_prefix."types");
		$DB->AddOrder("sortorder");
		$DB->AddOrder("pid");
		$summary = array("total" => 0, "budget" => 0, "purpose" => 0, "commerce" => 0); 
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$row["total"] = (int)$row["budget"] + (int)$row["commerce"]; 
			$this->output["types"][] = $row;
			if($row["pid"] == 0)
			foreach($summary as $key=>$value){
				$summary[$key] += (int)$row[$key]; 
			}
		}
		$summary["type"] = "ИТОГО";
		$summary["id"] = 0;
		$this->output["types"][] = $summary; 
	}
	
	function list_entrants($type_id = 0){
		global $DB, $Auth, $Engine;
		if(isset($_GET["type"])) $type_id = $_GET["type"];
		$this->output["type_id"] = $type_id;  
		if($type_id!=0) {
			$DB->SetTable($this->db_prefix."types");
			$DB->AddCondFS("id", "=", $type_id); 
			$res = $DB->Select(1); 
			while($row = $DB->FetchAssoc($res)) {
				$this->output["type"] = $row;
			}
			
			$this->output["date"] = $this->getUpdateDate(true);
			
			//$DB->SetTable("nsau_spec_abit", "a");
			//$DB->AddJoin("nsau_abit_types.position", "a.position"); 
			$DB->SetTable("nsau_people", "p");
			$DB->AddTable("nsau_spec_abit", "a");
			$DB->AddJoin("nsau_abit_types.position", "a.position");
			//$DB->AddFields(array("a.position", "a.documents", "a.ege"));
			$DB->AddExp("*");
			$DB->AddCondFS("a.form", "=", "заочная");
			$DB->AddCondFS("a.qualification", "!=", "51");
			$DB->AddCondFS("a.type_id", "=", $type_id);
			//$DB->AddCondFN("a.took");
			$DB->AddCondFS("a.took", "=", 0);
			//$DB->AddCondFN("a.out");
			$DB->AddCondFS("a.out", "=", 0);
			$DB->AddCondFF("p.id", "=", "a.people_id");
			$DB->AddOrder("a.position");
			$DB->AddOrders(array("a.position", "a.status1"));
			$DB->AddOrder("a.dogovor", 1);
			$DB->AddOrder("a.ege", 1);
			$DB->AddOrder("a.status2", 1);
			$DB->AddOrder("p.last_name");
			$DB->AddOrder("p.name");
			$DB->AddOrder("p.patronymic");
			
			//echo $DB->SelectQuery();
			$res = $DB->Select(); 
			while($row = $DB->FetchAssoc($res)) {
				$this->output["people"][] = $row; 
			} 
		}
		
		$DB->SetTable($this->db_prefix."types");
		$DB->AddOrder("sortorder");
		$DB->AddOrder("pid");
		$DB->AddCondFS("pid", "=", 0);
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$this->output["types"][] = $row;
		}
	}
	
	function getUpdateDate($with_time = false) {
		global $DB;
		$DB->SetTable("engine_dbupdates_log");
		$DB->AddField("update_time");
		$DB->AddCondFS("name", "=", "nsau_spec_abit");
		$res = $DB->Select();
		$row = $DB->FetchObject();
		$updateInfo = explode(' ', $row->update_time);
	
		$updateDate = ($with_time ? implode(':', array_splice(explode(':', $updateInfo[1]), 0, 2)) .' '. implode('.', array_reverse(explode('-', $updateInfo[0]))) : implode('.', array_reverse(explode('-', $updateInfo[0]))));
		$DB->FreeRes();
		return $updateDate;
	}
	
	function izop_old($branch, $spec_id) {
		//echo $branch; 
		$url = 'http://izop.nsau.edu.ru/material_'.$branch.'.php?section=method';
		//echo $url;
		$html = $this->curl_get_result($url);
		//echo htmlspecialchars($html);
		if(preg_match(("@\<!\-\- Специальности \-\-\>(.*)<!\-\- \/Специальности \-\-\>@s"), $html, $matches)) {
			//print_r($matches);
			$html = ($matches[1]);
			//echo htmlspecialchars($html); 
			if(preg_match_all("'<\s*a\s.*?href\s*=\s*			# find <a href=
						([\"\'])?					# find single or double quote
						(?(1) (.*?)\\1 | ([^\s\>]+))		# if quote found, match up to next matching
													# quote, otherwise match up to next space
						'isx", $html, $links)) {
				//print_r($links); 
						} 
		}
		$this->output["branch"] = $branch;
		$this->output["spec_id"] = $spec_id;
	}
	
	function Output()
	{
		return $this->output;
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
	
	function win2utf($s)
	{
	     if(function_exists("iconv")) return iconv("cp1251","utf-8",$s);
	     $t = "";
	     $c209 = chr(209); $c208 = chr(208); $c129 = chr(129);
	     for($i=0; $i<strlen($s); $i++)    {
	         $c=ord($s[$i]);
	         if ($c>=192 and $c<=239) $t.=$c208.chr($c-48);
	         elseif ($c>239) $t.=$c209.chr($c-112);
	         elseif ($c==184) $t.=$c209.chr(145);
	         elseif ($c==168) $t.=$c208.$c129;
	         else $t.=$s[$i];
	     }
	     return $t;
	}

	function utf2win($s){
	  if(function_exists("iconv")) return iconv("utf-8", "cp1251", $s);
	  $str = "";
	  for($i = 0; $i < strlen($s);){
	    $ch = $s[$i];
	    $code = ord($ch);//printf("\u%04x", $code);
	    if($code >> 7 == 0) { $str .= $ch; $l = 1;}
	    if($code >> 5 == 0x06){
	      $l = 2;
	      $ch1 = $code & 0x1F;
	      $ch2 = (ord($s[$i+1]) & 0x3F);

	      $res = $ch2 + ($ch1 << 6);
	      if($res == 0x401)
	      {
	        $res = 0xA8;
	      }
	      elseif($res == 0x451)
	      {
	        $res = 0xB8;
	      }
	      else
	      {
	        $res -= 848;
	      }
	      $str .= chr($res);
	    }
	    $i += $l;
	  }
	  return $str;
	}
	
	function curl_get_result($url, $curl_params = array()/*, $username = "", $password = ""*/) {
		$ch = curl_init();
		$timeout = 5;
		$url = str_replace(' ', '%20', $url);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		//curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
		//curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_COOKIEFILE, './cookiefile.txt');
		curl_setopt($ch, CURLOPT_COOKIEJAR, './cookiefile.txt');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		/*if($username)
		 {
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
		curl_setopt($ch, CURLOPT_USERPWD, "username:password");
		}*/
		if(is_array($curl_params))
			foreach($curl_params as $opt => $value){
			curl_setopt($ch, $opt, $value);
		}
		$data = curl_exec($ch) or die(curl_error($ch));
		curl_close($ch);
		return $data;
	}
}

?>