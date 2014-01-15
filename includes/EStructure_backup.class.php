<?php
/*
Модуль управления структурой сайта
*/

class EStructure
// version: 2.5
// date: 2010-01-22
{
	var $output;
	var $module_id;
    var $module_uri;
	var $node_id;    
	
	function EStructure($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
        global $Engine, $Auth;

		$this->output = array();
		$this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->module_uri = $module_uri;        
        
        $this->output["manage_subFolders"] = $Engine->OperationAllowed(0, "folder.subFolders.handle", 0, $Auth->usergroup_id);
        
		$this->ParseURI();
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
                            isset($_POST[$this->node_id]["add"]["texter"]) ? $_POST[$this->node_id]["add"]["texter"] : NULL
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
					$this->output["folders"] = $this->FolderList(0, 4);
                    $this->output["mode"] = "edit";
					break;
				default:                    
					CF::Redirect();
			}
		else
        {
           //if ($Engine->ModuleOperationAllowed("cat.items.handle", $this->cat_id)) // Тут надо право проверить!!!
           {
                $this->output["mode"] = "default";
                $this->ProcessHTTPdata();
           } 
			$this->output["folders"] = $this->FolderList(0, 4);
        }
	}    
    
	function FolderList($pid, $depth = 1)
	{
		global $DB, $Engine, $Auth;
		
		$DB->SetTable("engine_folders");
		$DB->AddCondFS("pid", "=", $pid);        
        //$DB->AddCondFP("id");
        $DB->AddOrder("pos");
        
        $list = NULL;
		if ($res = $DB->Select())
            $list = array(); // Список папок
			
		
		while ($row = $DB->FetchObject($res))
		{
				$depth_m = $depth-1;
				if($depth_m && $row->id)
                    $sublist = $this->FolderList($row->id, $depth);
                else 
                    $sublist = NULL;
					
				$chain = array();
					
				if ($pid = $row->pid) {
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
                
                if($Engine->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id))
				    $list[$row->id] = array(
					    "uri" => $Engine->FolderURIbyID($row->id),
					    "title" => $row->title,
					    "descr" => $row->descr,
						"id" => $row->id,
						"pid" => $row->pid,
					    "is_active" => $row->is_active,
                        "parser_node_id" => $row->parser_node_id,
					    "manage_subFolders" => $Engine->OperationAllowed(0, "folder.subFolders.handle", $row->id, $Auth->usergroup_id),
					    "manage_systemProps" => $Engine->OperationAllowed(0, "folder.systemProps.handle", $row->id, $Auth->usergroup_id),
                        "manage_props" => $Engine->OperationAllowed(0, "folder.props.handle", $row->id, $Auth->usergroup_id),					
					    "manage_folder.delete" => $Engine->OperationAllowed(0, "folder.delete", $row->id, $Auth->usergroup_id),
					    "subitems" => $sublist,
						"chain" => $chain
					);				
		}
		return $list;
	}
	
	function add($id, $name = NULL, $descr = NULL, $uri_part = NULL, $texter = "off")
	{
        global $DB, $Engine;
        if (!empty($name) && !empty($descr) && !empty($uri_part))
            {
                $DB->SetTable(ENGINE_DB_PREFIX."folders");
                $DB->AddValue("title", $name);
                $DB->AddValue("descr", $descr);
                $DB->AddValue("uri_part", $uri_part);
                $DB->AddValue("pid", $id);
                $DB->AddCondFS("pid","=", $id);
                $DB->SetPoser();                
                if ($folder_id = $DB->InsertPosed())
                    {
                        $this->output["message"] = "Раздел добавлен";
						$Engine->LogAction($this->module_id, "folder", $folder_id, "create");
                        if($texter == "on")
                        {
                            $DB->SetTable("engine_nodegroups");
                            $DB->AddCondFS("name", "=", "content");
                            $DB->AddField("id");
                            if($res = $DB->Select())
                            {
                                if($row = $DB->FetchObject($res))
                                    $nodegroup_id = $row->id;
                            }
                            
                            $DB->SetTable("engine_modules");
                            $DB->AddCondFS("name", "=", "ETexter");
                            $DB->AddField("id");
                            
                            if($res = $DB->Select())
                            {
                                if($row = $DB->FetchObject($res))
                                {                                    
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
                                    
                                    $DB->InsertPosed();
                                }
                                else
                                        $this->output["message"] .= ", но модуль ETexter не найден";
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
						
						foreach ($chain as $key => $value)
						{
							if (!$i) $get_str .= "?";
							$get_str .= "pid".$i."=".$value;
							if ($i < count($chain)-1) $get_str .= "&";
							
							$i++;
						}
						
						CF::Redirect($Engine->engine_uri.$get_str);
                    }
                
            }
        else
            $this->output["message"] = "Что-то не так делается. Обратитесь к администратору.";
        
	}
	
	function delete($id)
	{
		global $DB, $Engine;
			if($id)
			{
				$DB->SetTable("engine_folders");
				$DB->AddAltFS("id", "=", $id);
				$DB->Delete();
				$Engine->LogAction($this->module_id, "folder", $id, "delete");
				$this->output["message"] = "Раздел удалён. Безвозвратно :(";
			}
			else
			{
				$this->output["message"] = "Нельзя удалять главную страницу!";
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
		
        CF::Redirect($Engine->engine_uri.$get_str);
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
		
        CF::Redirect($Engine->engine_uri.$get_str);
    }
	
	function posup($id)
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
		
        CF::Redirect($Engine->engine_uri.$get_str);
    }
	
	
	function posdown($id)
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
		
        CF::Redirect($Engine->engine_uri.$get_str);
    }
    
    function edit($id)
    {
        global $DB, $Engine;        
		
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
            if($row = $DB->FetchAssoc($res))
            {
                $this->output["folder"] = $row;
				
				$DB->SetTable("engine_folders");
				$DB->AddCondFS("id", "=", $row["pid"]);
				$res_parent = $DB->Select();
				$row_parent = $DB->FetchAssoc($res_parent);
				$this->output["parent_folder"] = $row_parent["title"];
                
                $this->output["modules"] = array();
                $DB->SetTable("engine_modules");
                $res = $DB->Select();                
                
                while($row2 = $DB->FetchAssoc($res))
                {
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
			$this->output["messages"]["bad"][] = "Недостаточно прав";
		}
	}
    
	function Output()
	{
		return $this->output;
	}
}

?>