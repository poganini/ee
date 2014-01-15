<?php
/*
������ ���������� ���������� �����
*/

class EStructure
// version: 3.10
// date: 2013-11-22
{
	var $output;
	var $module_id;
    var $module_uri;
	var $node_id; 
	var $pid = 0;
	var $depth = 1;
	
	function EStructure($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
        global $Engine, $Auth, $DB;
		$this->output = array();
		$this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->module_uri = $module_uri;
		
		$this->output["manage_subFolders"] = $Engine->OperationAllowed(0, "folder.subFolders.handle", 0, $Auth->usergroup_id);
		
		$this->output['module_id'] = $module_id;
		$this->output['ajax_allowed'] = 0;
		if (sizeof($params)) {
			$params_parts = explode(";", $params);
			$open = $params_parts[0];
			
			if ($open == "open")
				$this->output["open"] = "1";
			else
				$this->output["open"] = "0";
			if(isset($params_parts[1]) && $params_parts[1])	{
				if($params_parts[1])
					$this->output["ajax_mode"] = $params_parts[1];
				else
					$this->output["ajax_mode"] = "normal";
			}
			if(isset($params_parts[2]))	$this->pid = $params_parts[2]; 
			$this->depth = isset($params_parts[3])? $params_parts[3] : 999; 
		}
		$DB->SetTable('engine_modules');
		$DB->AddCondFS('name', '=', "EAjax");
		$res_ajax_modul = $DB->Select();
		if($row_ajax_modul = $DB->FetchObject($res_ajax_modul)) {
			$this->depth = 1;
			$this->output['ajax_allowed'] = 1;
		}
		if(!isset($this->output["ajax_mode"]) || $this->output["ajax_mode"] == "normal") {
			if(isset($_POST['modul'])) {
				if($_POST['modul'] == "structure") {
					if(isset($_POST['action'])/*&& isset($_POST['this_id']) && isset($_POST['next_id'])*/) {//echo $_POST['action'];
						$this_id = explode("_", $_POST['this_id']);
						$next_id = explode("_", $_POST['next_id']);
						/*$this->output["action"] = $_POST['action'];
						$this->output["this_id"] = $this_id[1];
						$this->output["next_id"] = $next_id[1];*/
						switch($_POST['action']) {
							case "update":
								
								break;
							case "up":
								$this->posup($this_id[1], $next_id[1]);
								break;
							case "down":
								$this->posdown($this_id[1], $next_id[1]);
								break;
						}
					}
				} else {
					$this->output["scripts_mode"] = $this->output["mode"] = "none";
					return;
				}
			}
			$this->ParseURI();
		} else {
			switch($this->output["ajax_mode"]) {
				case "ajax_add_folder": {
					$this->output["mode"] = "ajax_add_folder";
					if (isset($_REQUEST["unassign_data"]) && is_array($_REQUEST["unassign_data"])) {
						
						$this->add($_REQUEST["unassign_data"]["pid"],
								iconv("UTF-8", "windows-1251", $_REQUEST["unassign_data"]["name"]),
								iconv("UTF-8", "windows-1251", $_REQUEST["unassign_data"]["descr"]),
								iconv("UTF-8", "windows-1251", $_REQUEST["unassign_data"]["uripart"]),
								isset($_REQUEST["unassign_data"]["texter"]) ? ($_REQUEST["unassign_data"]["texter"] ? "on" : "off") : "off"
						);
						$this->output["ajax_add_folder"] = $this->FolderList($_REQUEST["unassign_data"]["pid"], 1);
					}
				}
				break;
				case "ajax_del_folder": {
					$this->output["mode"] = "ajax_del_folder";
					if (isset($_REQUEST["unassign_data"]) && is_array($_REQUEST["unassign_data"]) && CF::IsNaturalNumeric($_REQUEST["unassign_data"]["id"])) {
						$this->delete($_REQUEST["unassign_data"]["id"]);
						//$this->output["ajax_add_folder"] = $this->FolderList($_REQUEST["unassign_data"]["pid"]);
					}
				}
				break;
				case "ajax_get_folder": {
					$this->output["mode"] = "ajax_get_folder";
					if (isset($_REQUEST["unassign_data"]) && is_array($_REQUEST["unassign_data"]) && CF::IsNaturalNumeric($_REQUEST["unassign_data"]["pid"])) {
						//$this->delete($_REQUEST["unassign_data"]["id"]);
						
						$this->output["ajax_get_folder"] = $this->FolderList($_REQUEST["unassign_data"]["pid"],1);
					}
				}
				break;
				case "ajax_hide_folder": {
					$this->output["mode"] = "ajax_hide_folder";
					if (isset($_REQUEST["unassign_data"]) && is_array($_REQUEST["unassign_data"]) && CF::IsNaturalNumeric($_REQUEST["unassign_data"]["id"])) {
						//$this->delete($_REQUEST["unassign_data"]["id"]);
						$this->hide($_REQUEST["unassign_data"]["id"]);
						$pid = $this->GetFolderParent($_REQUEST["unassign_data"]["id"]);
						if(!($pid === false)) {
							$this->output["ajax_hide_folder"] = $this->FolderList($pid,1);
							$this->output["ajax_hide_folder"] = $this->output["ajax_hide_folder"][$_REQUEST["unassign_data"]["id"]];
						}					
						//echo $_REQUEST["unassign_data"]["operation"];
					}
				}
				break;
				case "ajax_pos_folder": {
					$this->output["mode"] = "ajax_pos_folder";
					if (isset($_REQUEST["unassign_data"]) && is_array($_REQUEST["unassign_data"]) && CF::IsNaturalNumeric($_REQUEST["unassign_data"]["id"])) {
						//$this->delete($_REQUEST["unassign_data"]["id"]);
						//$this->hide($_REQUEST["unassign_data"]["id"]);
						//echo $_REQUEST["unassign_data"]["operation"];
						$this->output["ajax_pos_folder"]["from_id"] = $_REQUEST["unassign_data"]["id"];
						if($_REQUEST["unassign_data"]["operation"] == "posup") {
							$this->posup($_REQUEST["unassign_data"]["id"]);
						} elseif($_REQUEST["unassign_data"]["operation"] == "posdown") {
							$this->posdown($_REQUEST["unassign_data"]["id"]);
						}
					}
				}
				/*case "test": {
					$folders = $this->FolderList( (isset($additional['branchId']) ? $additional['branchId'] : 0),1);
					$response = array();
					if (isset($additional['fields'])) {
						foreach ($folders as $folder) {
							if (!$folder['id']) 
								$folder['have_subfolder'] = 0;
							$branch = array();
							foreach($additional['fields'] as $fieldType=>$field) {
								if (isset($folder[$field]))
									$branch[$fieldType] = ( $fieldType == 'displayField' ? iconv('windows-1251', 'utf-8', $folder[$field]) : $folder[$field]);						
							}
							$response[] = $branch;
						}
					}
					$this->output = array('response' => $response);
				}
				break;*/
				default: {
					
				}
				break;
			}
		}
	}
	
	function ParseURI()
	{
        global $Engine;
		
		$this->footsteps = array();
				
        $parts = explode("/", $this->module_uri);        
		$this->Folders(isset($parts[0]) ? $parts[0] : NULL, isset($parts[1]) ? $parts[1] : NULL);

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
                if (isset($_POST[$this->node_id]["add"]))
                    $this->add($_POST[$this->node_id]["add"]["id"],
                            $_POST[$this->node_id]["add"]["name"],
                            $_POST[$this->node_id]["add"]["descr"],
                            $_POST[$this->node_id]["add"]["uri_part"],
                            isset($_POST[$this->node_id]["add"]["texter"]) ? ($_POST[$this->node_id]["add"]["texter"] ? "on" : "off") : "off"
                    );
        } 
    }
    
	function Folders($action, $id)
	{
		if($action)
			switch ($action)
			{
				case "add": $this->add($id);
					break;
				case "delete": $this->delete($id);
					break;
				case "hide":
                case "show": $this->hide($id);
					break;
				case "posup": $this->posup($id);
					break;
				case "posdown": $this->posdown($id);
					break;
				case "edit":
                    $this->edit($id);
                    $this->output["folders"] = $this->FolderTree(0);
                    $this->output["scripts_mode"] = $this->output["mode"] = "edit";
					break;
				default:                    
					CF::Redirect();
			}
		else
        {
           //if ($Engine->ModuleOperationAllowed("cat.items.handle", $this->cat_id)) // ��� ���� ����� ���������!!!
           {
                $this->output["scripts_mode"] = $this->output["mode"] = "default";
                $this->ProcessHTTPdata();
           } 
			$this->output["pid"] = $this->pid;           
			$this->output["folders"] = $this->FolderList($this->pid, $this->depth);
        }
	}    
    

	function FolderList($pid, $depth = 1, $manage_sub = 0, $pid_stop_manage = 0, $delete_sub = 0)
	{
		global $DB, $Engine, $Auth, $ADMIN_USER;
		
		$DB->SetTable("engine_folders");
		$DB->AddCondFS("pid", "=", $pid);        
        //$DB->AddCondFP("id");
        $DB->AddOrder("pos");
		$this->output["is_admin"] = $ADMIN_USER;
        
		if (!$manage_sub)
			$_SESSION["cpid"] = $pid;
		
        $list = NULL;
		if ($res = $DB->Select())
            $list = array(); // ������ �����
			
		if ($Engine->OperationAllowed(0, "module.items.handle", 0, $Auth->usergroup_id))
			$_SESSION["allow_text"] = 1;
		else
			$_SESSION["allow_text"] = 0;
		
		while ($row = $DB->FetchObject($res))
		{
				if ($Engine->OperationAllowed(0, "folder.subFolders.handle", $row->id, $Auth->usergroup_id))
					$manage_sub = 1;
				else
					$manage_sub = 0;
				if ($Engine->OperationAllowed(0, "folder.delete", $row->id, $Auth->usergroup_id))
					$delete_sub = 1;
				else
					$delete_sub = 0;
				$have_subfolder = 0;
				$depth_m = $depth-1;
				$sublist = null;
				if($depth_m && $row->id) {
                    $sublist = $this->FolderList($row->id, $depth_m, $manage_sub, $pid);
					/*if(!is_null($sublist))
						$have_subfolder = 1;*/
				}/* else {
					$DB->SetTable("engine_folders");
					$DB->AddCondFS("pid", "=", $row->id);
					$subres = $DB->Select();
					if ($subrow = $DB->FetchObject($subres)) {
						$have_subfolder = 1;
					}
                }*/
				
				/*if ($row->pid == $pid_stop_manage || ($_SESSION["cpid"] == $pid && isset($_SESSION["lap_gone"]))) {
					$manage_sub = 0;
					$delete_sub = 0;
				}*/
			//echo $row->id." - ".$manage_sub."<br>";
				$chain = array();
				
				if ($pid = $row->pid) {
					$chain[] = $pid;
					if ($Engine->OperationAllowed(0, "folder.subFolders.handle", $pid, $Auth->usergroup_id)) {
						$manage_sub = 1;
					}
					while ($pid) {
						$DB->SetTable("engine_folders");
						$DB->AddField("pid");
						$DB->AddCondFS("id", "=", $pid);
						$res_p = $DB->Select();
						$row_p = $DB->FetchObject($res_p);
						$pid = $row_p->pid;
						if ($pid)
							array_unshift ($chain, $pid);
						if ($Engine->OperationAllowed(0, "folder.subFolders.handle", $pid, $Auth->usergroup_id)) {
							$manage_sub = 1;
						}
					}
				} 
				$DB->SetTable("engine_folders");
				$DB->AddCondFS("pid", "=", $row->id);
				$subres = $DB->Select();
				if ($subrow = $DB->FetchObject($subres)) {					
					if(!$manage_sub && !$subrow->is_active) {
						$have_subfolder = 0;
					} else {
						$have_subfolder = 1;
					}					
				}
				$ms = $manage_sub;
				$mfd = $manage_sub;
				$mp = $manage_sub;
								
                if($Engine->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row->id, $Auth->usergroup_id)) {
				    $list[$row->id] = array(
					    "uri" => $Engine->FolderURIbyID($row->id),
					    "title" => $row->title,
					    "descr" => $row->descr,
						"id" => $row->id,
						"pid" => $row->pid,
					    "is_active" => $row->is_active,
                        "parser_node_id" => $row->parser_node_id,
					    //"manage_subFolders" => $Engine->OperationAllowed(0, "folder.subFolders.handle", $row->id, $Auth->usergroup_id),
						"manage_subFolders" => $ms,
					    "manage_systemProps" => $Engine->OperationAllowed(0, "folder.systemProps.handle", $row->id, $Auth->usergroup_id),
                        //"manage_props" => $Engine->OperationAllowed(0, "folder.props.handle", $row->id, $Auth->usergroup_id),					
						"manage_props" => $mp,
					    //"manage_folder.delete" => $Engine->OperationAllowed(0, "folder.delete", $row->id, $Auth->usergroup_id),
						"manage_folder.delete" => $mfd,
					    "have_subfolder" => $have_subfolder,
					    "subitems" => $sublist,
						"chain" => $chain
					);
				}

				$_SESSION["lap_gone"] = 1;
		}
		return $list;
	}
	
	// ����������� ������ ������� FolderList ��� ������ ����������� ������ �����
	function FolderTree($pid = 0) 
	{
		global $DB, $Engine, $Auth, $ADMIN_USER;
		
		$DB->SetTable("engine_folders");
		$DB->AddCondFS("pid", "=", $pid);        
        //$DB->AddCondFP("id");
        $DB->AddOrder("pos");
		$this->output["is_admin"] = $ADMIN_USER;
		
		$list = array(); 
		$res = $DB->Select();
		while($row = $DB->FetchObject($res)) 
		{
			if(!$Engine->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id) || !$Engine->OperationAllowed(0, "folder.list", $row->id, $Auth->usergroup_id))
				continue;
			
			$sublist = null;
			if($row->id) {
				$sublist = $this->FolderTree($row->id, $depth_m, $manage_sub, $pid);
			}
			
			$list[$row->id] = array(
				"uri" => $Engine->FolderURIbyID($row->id),
				"title" => $row->title,
				"descr" => $row->descr,
				"id" => $row->id,
				"pid" => $row->pid,
				"is_active" => $row->is_active,
				"subitems" => $sublist,
			);
		}
		
		return $list; 
	}
	
	function add($id, $name = NULL, $descr = NULL, $uri_part = NULL, $texter = "off")
	{
        global $DB, $Engine, $Auth;
        if (!empty($name) && !empty($descr) && !empty($uri_part))
            {
				$allow = 0;
				$id_to_check = $id;
				do {
					$DB->SetTable("engine_folders");
					$DB->AddCondFS("id", "=", $id_to_check);
					$res = $DB->Select();
					if ($row = $DB->FetchAssoc($res)) {
						if ($Engine->OperationAllowed(0, "folder.subFolders.handle", $row["id"], $Auth->usergroup_id) || $Engine->OperationAllowed(0, "folder.subFolders.handle", $row["pid"], $Auth->usergroup_id)) {
							$allow = 1;
						}
						$id_to_check = $row["pid"];
					}
				}
				while ($row["pid"] && !$allow);
                if ($Engine->OperationAllowed(0, "folder.subFolders.handle", $id, $Auth->usergroup_id) || $allow) {

					$DB->SetTable(ENGINE_DB_PREFIX."folders");
					$DB->AddValue("title", $name);
					$DB->AddValue("descr", $descr);
					$DB->AddValue("uri_part", $uri_part);
					$DB->AddValue("pid", $id);
					$DB->AddCondFS("pid","=", $id);
					$DB->SetPoser();
					if ($folder_id = $DB->InsertPosed()) {
						$this->output['creat_folder_id'] = $folder_id;
						$this->output["message"] = "������ ��������";
						$Engine->LogAction($this->module_id, "folder", $folder_id, "create");
						if($texter == "on") {
							$DB->SetTable("engine_nodegroups");
							$DB->AddCondFS("name", "=", "content");
							$DB->AddField("id");
							if($res = $DB->Select()) {
								if($row = $DB->FetchObject($res))
									$nodegroup_id = $row->id;
							}
							
							$DB->SetTable("engine_modules");
							$DB->AddCondFS("name", "=", "ETexter");
							$DB->AddField("id");
							
							if($res = $DB->Select()) {
								if($row = $DB->FetchObject($res)) {
									$DB->SetTable("text_items");
									$DB->AddValue("text", NULL);
									$DB->Insert();
									$texter_id = $DB->LastInsertID();
									
									$DB->SetTable("engine_nodes");
									$DB->AddValue("is_active", "1");
									$DB->AddValue("folder_id", $folder_id);
									$DB->AddValue("module_id", $row->id);
									$DB->AddValue("params", $texter_id);
									$DB->AddValue("nodegroup_id", $nodegroup_id);
									$DB->AddValue("pos", "1");
									$DB->Insert();

									if (!$Engine->OperationAllowed($row->id, "item.alter", $texter_id, $Auth->usergroup_id))
										$Engine->AddPrivilege($row->id, "item.alter", $texter_id, $Auth->usergroup_id, 1, "create; �������� ��������, ���������� ����� �� ��� ��������������");
									/*$DB->SetTable("engine_privileges");
									$DB->AddValue("module_id", 1);
									$DB->AddValue("operation_name", "item.alter");
									$DB->AddValue("entry_id", $texter_id);
									$DB->AddValue("usergroup_id", $Auth->usergroup_id);
									$DB->AddValue("is_allowed", 1);
									$DB->Insert();
									$Engine->LogAction($this->module_id, "privilegies", $folder_id, "create; ");*/
								} else
									$this->output["message"] .= ", �� ������ ETexter �� ������";
							}
						};
						$chain = array();
						
						if ($pid = $id) {
							$chain[] = $pid;
							while ($pid) {
								$DB->SetTable("engine_folders");
								$DB->AddField("pid");
								$DB->AddCondFS("id", "=", $pid);
								$res_p = $DB->Select();
								$row_p = $DB->FetchObject($res_p);
								$pid = $row_p->pid;
								if ($pid)
									array_unshift ($chain, $pid);
							}
						}
						
						$i = 0;
						$get_str = "";

						foreach ($chain as $key => $value) {
							if (!$i) $get_str .= "?";
							$get_str .= "pid".$i."=".$value;
							if ($i < count($chain)-1) $get_str .= "&";
							
							$i++;
						}

						
						if(!isset($_REQUEST["unassign_data"]))
							CF::Redirect($Engine->engine_uri.$get_str);
					}
                }
            }
        else
            $this->output["message"] = "���-�� �� ��� ��������. ���������� � ��������������.";
        
	}
	
	function delete($id, $count = 1)
	{
		global $DB, $Engine;
			if($id)
			{
				$DB->SetTable("engine_folders");
				$DB->AddAltFS("id", "=", $id);
				$DB->Delete();
				$Engine->LogAction($this->module_id, "folder", $id, "delete");
				$this->output["message"] = "������ �����. ������������ :(";
				
				$DB->SetTable("engine_nodes");
				$DB->AddCondFS("folder_id", "=", $id);
				$DB->Delete();
			}
			else
			{
				$this->output["message"] = "������ ������� ������� ��������!";
			}
		
        $DB->SetTable("engine_folders");
		$DB->AddCondFS("pid", "=", $id);
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)) {
			$this->delete($row["id"], $count++);
		}
		
		$i = 0;
		$get_str = "";
		foreach ($_GET as $key => $value)
		{
			if (!$i) $get_str .= "?";
			if (substr($key, 0, 3) == "pid") {
				$get_str .= $key."=".$value;
				if ($i < count($_GET)-1) $get_str .= "&";
			}
			$i++;
		}
		
		if (!$get_str)
			$get_str .= "?";
		else
			$get_str .= "&";
		//$get_str .= "deleted=".$count;
		$count++;
		if (!isset($_REQUEST["unassign_data"])) {
			$this->output["deleted"] = $count;
			$this->output["scripts_mode"] = $this->output["mode"] = "default";
			$this->output["folders"] = $this->FolderList(0, 4);
			CF::Redirect($Engine->engine_uri);
		}		
		//die($id);
	}
    
    function hide($id)
    {
        global $DB, $Engine;
        $DB->SetTable("engine_folders");
        $DB->AddValue("is_active", "!is_active", "X");
        $DB->AddCondFS("id", "=", $id);
        $DB->Update(1);
		
		$i = 0;
		$get_str = "";
		foreach ($_GET as $key => $value)
		{
			if (!$i) $get_str .= "?";
			if (substr($key, 0, 3) == "pid") {
				$get_str .= $key."=".$value;
				if ($i < count($_GET)-1) $get_str .= "&";
			}
			$i++;
		}
		
		if(!isset($_REQUEST["unassign_data"]))
			CF::Redirect($Engine->engine_uri.$get_str);
    }
	
	function posup($id, $next_id = null)
    {
        global $DB, $Engine;
        $DB->SetTable("engine_folders");
		$DB->AddField("pos");
		$DB->AddField("pid");
		$DB->AddCondFS("id", "=", $id);
		$res = $DB->Select();
		
		$row = $DB->FetchObject($res);
		$pos_cur = $row->pos;
		$pid = $row->pid;
		if($next_id != null) {
			while($id_next != $next_id) {
				$DB->SetTable("engine_folders");
				$DB->AddField("id");
				$DB->AddField("pos");
				$DB->AddCondFS("pos", "<", $pos_cur);
				$DB->AddCondFS("pid", "=", $pid);
				$DB->AddOrder("pos", 1);
				
				$res = $DB->Select();
				$row = $DB->FetchObject($res);
				$pos_next = $row->pos;
				$id_next = $row->id;
			
				$DB->SetTable("engine_folders");
				$DB->AddValue("pos", $pos_next, "X");
				$DB->AddCondFS("id", "=", $id);
				$DB->Update(1);
				
				$DB->SetTable("engine_folders");
				$DB->AddValue("pos", $pos_cur, "X");
				$DB->AddCondFS("id", "=", $id_next);
				$DB->Update(1);
				$pos_cur = $pos_next;
			}
		} else {
			$DB->SetTable("engine_folders");
			$DB->AddField("id");
			$DB->AddField("pos");
			$DB->AddCondFS("pos", "<", $pos_cur);
			$DB->AddCondFS("pid", "=", $pid);
			$DB->AddOrder("pos", 1);
			
			$res = $DB->Select();
			$row = $DB->FetchObject($res);
			$pos_next = $row->pos;
			$id_next = $row->id;
			
			$DB->SetTable("engine_folders");
			$DB->AddValue("pos", $pos_next, "X");
			$DB->AddCondFS("id", "=", $id);
			$DB->Update(1);
			
			$DB->SetTable("engine_folders");
			$DB->AddValue("pos", $pos_cur, "X");
			$DB->AddCondFS("id", "=", $id_next);
			$DB->Update(1);
		
			$i = 0;
			$get_str = "";
			foreach ($_GET as $key => $value)
			{
				if (!$i) $get_str .= "?";
				if (substr($key, 0, 3) == "pid") {
					$get_str .= $key."=".$value;
					if ($i < count($_GET)-1) $get_str .= "&";
				}
				$i++;
			}
			if(!isset($_REQUEST["unassign_data"])) {
				CF::Redirect($Engine->engine_uri.$get_str);
			} else {
				$this->output["ajax_pos_folder"]["to_id"] = $id_next;
				$this->output["ajax_pos_folder"]["pid"] = $pid;
			}
		}
    }
	
	
	function posdown($id, $next_id = null)
    {
        global $DB, $Engine;
        $DB->SetTable("engine_folders");
		$DB->AddField("pos");
		$DB->AddField("pid");
		$DB->AddCondFS("id", "=", $id);
		$res = $DB->Select();
		
		$row = $DB->FetchObject($res);
		$pos_cur = $row->pos;
		$pid = $row->pid;
		
		if($next_id != null) {
			while($id_next != $next_id) {
				$DB->SetTable("engine_folders");
				$DB->AddField("id");
				$DB->AddField("pos");
				$DB->AddCondFS("pos", ">", $pos_cur);
				$DB->AddCondFS("pid", "=", $pid);
				$DB->AddOrder("pos");
				
				$res = $DB->Select();
				$row = $DB->FetchObject($res);
				$pos_next = $row->pos;
				$id_next = $row->id;
			
				$DB->SetTable("engine_folders");
				$DB->AddValue("pos", $pos_next, "X");
				$DB->AddCondFS("id", "=", $id);
				$DB->Update(1);
				
				$DB->SetTable("engine_folders");
				$DB->AddValue("pos", $pos_cur, "X");
				$DB->AddCondFS("id", "=", $id_next);
				$DB->Update(1);
				$pos_cur = $pos_next;
			}
		} else {
			$DB->SetTable("engine_folders");
			$DB->AddField("id");
			$DB->AddField("pos");
			$DB->AddCondFS("pos", ">", $pos_cur);
			$DB->AddCondFS("pid", "=", $pid);
			$DB->AddOrder("pos");
			$res = $DB->Select();
			
			$row = $DB->FetchObject($res);
			$pos_next = $row->pos;
			$id_next = $row->id;
			
			$DB->SetTable("engine_folders");
			$DB->AddValue("pos", $pos_next, "X");
			$DB->AddCondFS("id", "=", $id);
			$DB->Update(1);
			
			$DB->SetTable("engine_folders");
			$DB->AddValue("pos", $pos_cur, "X");
			$DB->AddCondFS("id", "=", $id_next);
			$DB->Update(1);
			
			$i = 0;
			$get_str = "";
			foreach ($_GET as $key => $value)
			{
				if (!$i) $get_str .= "?";
				if (substr($key, 0, 3) == "pid") {
					$get_str .= $key."=".$value;
					if ($i < count($_GET)-1) $get_str .= "&";
				}
				$i++;
			}
			if(!isset($_REQUEST["unassign_data"])) {
				CF::Redirect($Engine->engine_uri.$get_str);
			} else {
				$this->output["ajax_pos_folder"]["to_id"] = $id_next;
				$this->output["ajax_pos_folder"]["pid"] = $pid;
			}
		}
    }
    
    function edit($id)
    {
        global $DB, $Engine, $Auth;
		
		if ($Engine->OperationAllowed(0, "folder.nodes.handle", 0, $Auth->usergroup_id)) {
			$this->output["nodes_allowed"] = 1;
		} else {
			$this->output["nodes_allowed"] = 0;
		}
		
		
        if ((isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id])) || isset($_POST["edit_nodes"]))
        {
            if (isset($_POST[$this->node_id]["save"]))
            {
				$DB->SetTable("engine_nodegroups");
				$res_ng = $DB->Select();
			
				$transmit_nodes = 0;
				
				while ($nodegroup = $DB->FetchAssoc($res_ng)) {
					$DB->SetTable("engine_nodegroups_inheritance");
					if (isset($_POST[$this->node_id]["save"]["nodegroups_inheritance"][$nodegroup["id"]]) && $_POST[$this->node_id]["save"]["nodegroups_inheritance"][$nodegroup["id"]] == "on") {
						$DB->AddCondFS("folder_id", "=", $id);
						$DB->AddCondFS("nodegroup_id", "=", $nodegroup["id"]);
						$res_already_inherited = $DB->Select();
						
						if (!$DB->FetchAssoc($res_already_inherited)) {
							$DB->SetTable("engine_nodegroups_inheritance");
							$DB->AddValue("folder_id", $id);
							$DB->AddValue("nodegroup_id", $nodegroup["id"]);
							$DB->Insert();
						}
						
						$transmit_nodes = 1;
					}
					else {
						$DB->AddCondFS("folder_id", "=", $id);
						$DB->AddCondFS("nodegroup_id", "=", $nodegroup["id"]);
						$DB->Delete();
					}
				}
			
                $DB->SetTable("engine_folders");
                $DB->AddCondFS("id", "=", $id);
                $DB->AddValue("title", $_POST[$this->node_id]["save"]["title"]);
                $DB->AddValue("descr", $_POST[$this->node_id]["save"]["descr"]);
				$DB->AddValue("pid", $_POST[$this->node_id]["save"]["pid"]);
                $DB->AddValue("uri_part", $_POST[$this->node_id]["save"]["uri_part"]);
				$DB->AddValue("main_template", $_POST[$this->node_id]["save"]["main_template"]);
				$DB->AddValue("meta_descr", $_POST[$this->node_id]["save"]["meta_descr"]);
				$DB->AddValue("keywords", $_POST[$this->node_id]["save"]["keywords"]);
				$DB->AddValue("transmit_nodes", $transmit_nodes);
                $DB->Update(1);
				$Engine->LogAction($this->module_id, "folder", $id, "alter");
				
                CF::Redirect($Engine->engine_uri);
            }
			elseif (isset($_POST["edit_nodes"]))
			{	
				$this->edit_nodes($id);
			}
            else
                if (isset($_POST[$this->node_id]["add_node"]))
                {                    
                    $DB->SetTable("engine_nodes");
                    $DB->AddValue("folder_id", $id);
                    $DB->AddValue("nodegroup_id", $_POST[$this->node_id]["add_node"]["nodegroup_id"]);
                    $DB->AddValue("module_id", $_POST[$this->node_id]["add_node"]["module_id"]);
                    $DB->AddValue("pos", $_POST[$this->node_id]["add_node"]["pos"]);
                    $DB->AddValue("params", $_POST[$this->node_id]["add_node"]["params"]);
                    
                    $DB->Insert();
                }
        }
        
        {
            $DB->SetTable("engine_folders");
            $DB->AddCondFS("id", "=", $id);
            $res = $DB->Select(1);
            if($row = $DB->FetchAssoc($res)) {
                $this->output["folder"] = $row;
				$DB->SetTable("engine_folders");
				$DB->AddCondFS("id", "=", $row["pid"]);
				$res_parent = $DB->Select();
				$row_parent = $DB->FetchAssoc($res_parent);
				$this->output["parent_folder"] = $row_parent["title"];
                
                $this->output["modules"] = array();
                $DB->SetTable("engine_modules");
                $res = $DB->Select();
                
                while($row2 = $DB->FetchAssoc($res)) {
					if($row2["id"])
						$this->output["modules"][$row2["id"]] = $row2;
                }
				
                $DB->SetTable("engine_nodes");
                $DB->AddCondFS("folder_id", "=", $id);                
                $this->output["nodes"] = array();
                $res = $DB->Select();
                while($row3 = $DB->FetchAssoc($res))
                {
                    $this->output["nodes"][$row3["id"]] = $row3;
                }
                
                $DB->SetTable("engine_nodegroups");                
                $this->output["nodegroups"] = array();
                $res = $DB->Select();
                while($row4 = $DB->FetchAssoc($res))
                {				
                    $this->output["nodegroups"][$row4["id"]] = $row4;
					
					$DB->SetTable("engine_nodegroups_inheritance");
					$DB->AddCondFS("folder_id", "=", $id);
					$DB->AddCondFS("nodegroup_id", "=", $row4["id"]);
					$res_inh = $DB->Select();
					if ($DB->FetchAssoc($res_inh))
						$this->output["nodegroups"][$row4["id"]]["inheritance"] = 1;
					else
						$this->output["nodegroups"][$row4["id"]]["inheritance"] = 0;
                }
                
				/*$DB->SetTable("engine_folders");
				$DB->AddField("id");
				$DB->AddField("title");
				$DB->AddOrder("title");
				$res = $DB->Select(1);
				while($row5 = $DB->FetchAssoc($res))
                {
                    $this->output["folders"][] = $row5;
                }*/
            }
        }        
    }
	
	function edit_nodes($id)
	{
		global $DB, $Engine, $Auth;
		
		if($Engine->OperationAllowed(0, "folder.nodes.handle", $id, $Auth->usergroup_id))
		{
			foreach ($_POST as $node_id => $node) {
				if (isset($node["edit_node"])) {
					if (isset($node["edit_node"]["delete"]) && $node["edit_node"]["delete"] == "on") {
						$DB->SetTable("engine_nodes");
						$DB->AddCondFS("id", "=", $node_id);
						$DB->Delete();
					}
					else {
						$DB->SetTable("engine_nodes");
						$DB->AddValue("nodegroup_id", $node["edit_node"]["nodegroup_id"]);
						$DB->AddValue("pos", $node["edit_node"]["pos"]);
						$DB->AddValue("params", $node["edit_node"]["params"]);
						$DB->AddValue("folder_id", $node["edit_node"]["folder_id"]);
						
						if (isset($node["edit_node"]["is_active"]) && $node["edit_node"]["is_active"] == 'on')
							$DB->AddValue("is_active", 1);
						else
							$DB->AddValue("is_active", 0);
						
						$DB->AddCondFS("id", "=", $node_id);
						$DB->Update(1);
					}
				}
			}
			
			if (isset($_POST[$this->node_id]["edit_parser"]["parser_node_id"])) {
				$DB->SetTable("engine_folders");
				$DB->AddValue("parser_node_id", $_POST[$this->node_id]["edit_parser"]["parser_node_id"]);
				$DB->AddCondFS("id", "=", $id);
				$DB->Update(1);
			}
			
			CF::Redirect();
		}
		else
		{
			$this->output["messages"]["bad"][] = "������������ ����";
		}
	}
	
	function GetFolderParent($id) {
		global $DB, $Engine;
		
		$DB->SetTable("engine_folders");
		$DB->AddField("pid");
		$DB->AddCondFS("id", "=", $id);		
        $res = $DB->Select();
		if ($row = $DB->FetchObject($res)){
			return $row->pid;
		}
		return false;
	}
    
	function Output()
	{
		return $this->output;
	}
}

?>