<?php

class EMenu
// ������ ������ ����
// version: 3.5
// date: 2014-02-10
{
	var $output;
	var $module_id;
	var $node_id;
	var $mode;

	var $db_prefix;

	var $group_id;
	var $max_depth;


	function EMenu($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL) {
		global $Engine, $Auth, $DB;

		$this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->output["messages"] = array(
			"good" => array(),
			"bad" => array()
			);

		$this->db_prefix = $global_params;

		$parts = explode(";", $params); // 0 - group_id; 1 - max_depth; 2 - init_pid
		
		$this->output['module_id'] = $module_id;
		if (CF::IsNaturalNumeric($parts[0]) || $parts[0] == 'nripk_menu') {   
			$this->group_id = ($parts[0] == 'nripk_menu' ? (int)$parts[1] : (int)$parts[0]);
			$this->max_depth = ($parts[0] == 'nripk_menu' ? ( isset($parts[2]) ?  CF::Seek4NaturalNumeric((int)$parts[2], true) : true ) : ( isset($parts[1]) ? CF::Seek4NaturalNumeric((int)$parts[1], true) : true ));
			$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = "list_group_items";
			$this->output["group_id"] = 5;
			if ($parts[0] == 'nripk_menu') $this->output['mode'] = 'nripk_menu';
			
			if ($Engine->ModuleOperationAllowed("group.view", $this->group_id)) {  
				$this->output["items"] = $this->ListGroupItems(
					isset($parts[2]) ? CF::Seek4NaturalorZeroNumeric($parts[2], 0) : 0,
					1
					);
					
			}
			else { 
				$this->output["items"] = array();
			}
		}
		else {
			$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = $parts[0];

			switch ($this->mode) {
				case "manage": {
					$DB->SetTable("menu_groups");
					$this->output["plugins"][] = "jquery.ui.autocomplete.min";
					$res = $DB->Select();
					while ($row = $DB->FetchAssoc($res))
						$this->output["manage_groups"][$row["id"]] = $Engine->OperationAllowed($this->module_id, "module.groups.handle", $row["id"], $Auth->usergroup_id);
					
					$module_uri = explode("/",$module_uri);
					if ($module_uri[0] == 'add') {
						$this->AddItem($module_uri[0], $module_uri[1], $module_uri[2]);
					} elseif ($module_uri[0] == 'add_group') { 
						if (isset($_POST['descr']) && $_POST['descr']) {
							$this->AddGroup($module_uri);
						} else
							$this->output["group_add"] = true;
					} elseif (isset($module_uri[1])) {
						$this->MenuItems($module_uri[0],$module_uri[1]);
					} else {
						$this->MenuItems();
					}
				}
				break;
                    
				case "main_menu": {
					$output = null;
					
                    $this->group_id = (int)$parts[1];
					$this->max_depth = isset($parts[2]) ? CF::Seek4NaturalNumeric($parts[2], true) : true;
					$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = "main_menu";
					$this->output["group_id"] =  /*5*/$this->group_id;

					if (count($Engine->footsteps) >= 0) { 
						$array = array();
						$array[] = array(
							"uri" => HTTP_ROOT,
							"title" => "�������"/*SITE_SHORT_NAME*/,
							"descr" => "�� ������� �������� �����",
							"is_folder" => true,
							"folder_id" => $Engine->root_folder_id,
							"is_hidden" => false,
							"module_id" => 0,
						);
						$array = array_merge($array, $Engine->footsteps);
						
						foreach($array as $step) {
							if($step["is_folder"]) {
								if($step["folder_id"] == $Engine->root_folder_id) { 
									if ($Engine->ModuleOperationAllowed("group.view", $this->group_id)) {
										$output[] = array(
											"title" => $step["title"],
											"descr" => $step["descr"],
											"uri" => $step["uri"],
											"uri_is_current" => CF::URIin($step["uri"]),
											"subitems" => $this->ListGroupItems(
														isset($parts[3]) ? CF::Seek4NaturalorZeroNumeric($parts[3], 0) : 0,
														1
													)
										);
									} 
								} else if (!isset($parts[4]) || !$parts[4]) {
									$DB->SetTable("engine_folders");
									$DB->AddCondFS("id", "=", $step["folder_id"]);
									$DB->AddCondFP("is_active");
									$DB->AddOrder("pos");      
									$res2 = $DB->Select();
									while ($row2 = $DB->FetchObject($res2)) {	
										if($row2->is_active && $Engine->OperationAllowed(0, "folder.view", $row2->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row2->id, $Auth->usergroup_id)) {
											$uri2 = ($Engine->FolderURIbyID($row2->id)); 
											$sub = $this->SubmenuSpisok($row2->id, $uri2, 2);
											$output[] = array(
												"submenu_id" => (int)$row2->id,
												"uri_part" => $row2->uri_part,
												"title" => $row2->title,
												"descr" => $row2->descr,
												"uri" => $uri2,
												"uri_is_current" => CF::URIin($uri2),
												"subitems" => $sub
											);
										}
									}
									$DB->FreeRes($res2);
								}
								
							} else {
								if(!$step["is_hidden"]) {
									$output[] = array(
										"title" => $step["title"],
										"descr" => $step["descr"],
										"uri" => $step["uri"],
										"uri_is_current" => CF::URIin($step["uri"])
									);
								}
							}							
						}						
					}	
					/*if ($Engine->ModuleOperationAllowed("group.view", $this->group_id)) {  
						$this->output["items"] = $this->ListGroupItems(
							isset($parts[2]) ? CF::Seek4NaturalorZeroNumeric($parts[3], 0) : 0,
							1
						);
					} else { 
						$this->output["items"] = array();
					}*/
					$this->output["items"] = $output;
					/*	echo "<pre>";
						print_r($output);
						echo "</pre>";*/
				}
				break;
                    
				case "img_menu": {
					$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = "img_menu";
					
					$output = null;					
					$this->max_depth = true;
                    $this->group_id = (int)$parts[1];
					$limit = isset($parts[2]) && CF::IsNaturalNumeric($parts[2]) ? $parts[2] : null;
					$pid = isset($parts[3]) ? CF::Seek4NaturalorZeroNumeric($parts[3], 0) : 0;
					$depth = 1;
					$this->output["group_id"] = $this->group_id;
					$this->output["pid"] = $pid;
					$this->output["ajax_upload"] = 0;
					$this->output["count"] = 0;

					$res = $DB->Exec("SELECT count(`menu_items`.`id`) AS 'count' FROM `menu_items` LEFT JOIN `engine_folders` ON `menu_items`.`folder_id` = `engine_folders`.`id` WHERE `menu_items`.`is_active` AND `menu_items`.`pid` = '".$pid."' AND `menu_items`.`group_id` = '".$this->group_id."' AND `engine_folders`.`is_active`");
					$row = $DB->FetchAssoc($res);
					if($row['count']-$limit > 0) {
						$this->output["ajax_upload"] = 1;
					}
					if ($Engine->ModuleOperationAllowed("group.view", $this->group_id)) {  
						$this->output["items"] = $this->ListGroupItems($pid, $depth, $limit, 0);
					} else { 
						$this->output["items"] = array();
					}
					$this->output["count"] = $row['count'];
				}
				break;
			
				case 'ajax_get_menu_item': {
					$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = 'ajax_get_menu_item';
					$this->max_depth = true;
                    $this->group_id = (int)$parts[1];
					$limit_from = isset($parts[2]) && CF::IsNaturalNumeric($parts[2]) ? $parts[2] : null;
					$pid = isset($parts[3]) ? CF::Seek4NaturalorZeroNumeric($parts[3], 0) : 0;
					$depth = 1;
					
					/*if (!isset($Engine->modules_privileges[$_REQUEST['module']])) {
						$Engine->modules_privileges += $Engine->GetPrivileges($_REQUEST['module'], NULL, NULL, $Auth->usergroup_id);
					}*/
					$res = $DB->Exec("SELECT count(`menu_items`.`id`) AS 'count' FROM `menu_items` LEFT JOIN `engine_folders` ON `menu_items`.`folder_id` = `engine_folders`.`id` WHERE `menu_items`.`is_active` AND `menu_items`.`pid` = '".$pid."' AND `menu_items`.`group_id` = '".$this->group_id."' AND `engine_folders`.`is_active`");
					$row = $DB->FetchAssoc($res);
					
					
					if($Engine->OperationAllowed($this->module_id, "group.view", $this->group_id, $Auth->usergroup_id, $Engine->modules_privileges) && ($row['count']-$limit_from > 0)) {  
						$this->output["items"] = $this->ListGroupItems($pid, $depth, 3, $limit_from);
					} else { 
						$this->output["items"] = array();
					}
				}
				break;
				
				case "ajax_autocomplete_folder": {
					$json = array();
					$this->SearchFolders($_REQUEST['data']['q'], $json, $_REQUEST['data']['pid'], $_REQUEST['data']['group_id']);
					
					$this->output["json"] = $json;
				}
				break; 
			
				case "submenu": {
                    $this->Submenu($parts, $module_uri, ((isset($parts[3]) && $parts[3]) ? true : true));
				}
				break;
				
				default: {
					die("EMenu module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
				}
				break;
			}
		}
	}

	function AddItem($add_mode, $group_id, $parent_id) {
		$items = $this->FolderTree(0, 4);
		$this->output["folders"] = $items;
		$this->output["group_id"] = $group_id;
		$this->output["pid"] = $parent_id;
			
		if (isset($_POST["title"])) $this->add($_POST["pid"], $_POST["title"], $_POST["descr"], $_POST["group_id"], $_POST["folder_id"], $_POST["direct_link"]);

	}
	
	function AddGroup($moduleUri) {
		global $DB, $Engine, $Auth;
		$DB->SetTable("menu_groups");
		$DB->AddValue("descr", $_POST['descr']);
        $DB->SetPoser();
      	$insert_id = $DB->InsertPosed();
		$Engine->LogAction($this->module_id, "MenuGroup", $insert_id, "add");
		CF::Redirect(str_replace('add_group', '', $_SERVER['REQUEST_URI']));
	}
	
	
	function FolderList($pid, $depth = 1) {
		global $DB, $Engine, $Auth;
		$DB->SetTable("engine_folders");
		$DB->AddCondFS("pid", "=", $pid);
        //$DB->AddCondFP("id");
        $DB->AddOrder("pos");        
        
        $list = NULL;
		if ($res = $DB->Select())
            $list = array(); // ������ �����
		while ($row = $DB->FetchObject($res)) {
			//if ($row["id"]==635) die("lurk");
				if($depth-1 && $row->id)
                  $sublist = $this->FolderList($row->id, $depth-1);
                else if($depth === false && $row->id) 
                  $sublist = $this->FolderList($row->id, false);
                else 
                    $sublist = NULL;
                
                if($Engine->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row->id, $Auth->usergroup_id))
				    $list[$row->id] = array(
					    "uri" => $Engine->FolderURIbyID($row->id),
					    "title" => $row->title,
					    "descr" => $row->descr,
					    "is_active" => $row->is_active,
                        "parser_node_id" => $row->parser_node_id,
					    "manage_subFolders" => $Engine->OperationAllowed(0, "folder.subFolders.handle", $row->id, $Auth->usergroup_id),
					    "manage_systemProps" => $Engine->OperationAllowed(0, "folder.systemProps.handle", $row->id, $Auth->usergroup_id),
                        "manage_props" => $Engine->OperationAllowed(0, "folder.props.handle", $row->id, $Auth->usergroup_id),					
					    "manage_folder.delete" => $Engine->OperationAllowed(0, "folder.delete", $row->id, $Auth->usergroup_id),
					    "subitems" => $sublist
					);
		}
		return $list;
	
	}
  
  function FolderTree($pid/*, $depth = 1*/) {
		global $DB, $Engine, $Auth;
		$DB->SetTable("engine_folders");
		$DB->AddCondFS("pid", "=", $pid);
        //$DB->AddCondFP("id");
        $DB->AddOrder("pos");        
        
        $list = NULL;
		if ($res = $DB->Select())
            $list = array(); // ������ �����
		while ($row = $DB->FetchObject($res)) {
			//if ($row["id"]==635) die("lurk");
				if(/*$depth-1 &&*/ $row->id)
                  $sublist = $this->FolderTree($row->id);
                else 
                    $sublist = NULL;
                
                if($Engine->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row->id, $Auth->usergroup_id))
				    $list[$row->id] = array(
					    //"uri" => $Engine->FolderURIbyID($row->id),
					    "title" => $row->title,
					    "descr" => $row->descr,
					    "is_active" => $row->is_active,
                        "parser_node_id" => $row->parser_node_id,
					    /*"manage_subFolders" => $Engine->OperationAllowed(0, "folder.subFolders.handle", $row->id, $Auth->usergroup_id),
					    "manage_systemProps" => $Engine->OperationAllowed(0, "folder.systemProps.handle", $row->id, $Auth->usergroup_id),
                        "manage_props" => $Engine->OperationAllowed(0, "folder.props.handle", $row->id, $Auth->usergroup_id),					
					    "manage_folder.delete" => $Engine->OperationAllowed(0, "folder.delete", $row->id, $Auth->usergroup_id),*/
					    "subitems" => $sublist
					);
		}
		return $list;
	
	}
  
			


	function ListGroupItems($pid, $depth, $limit = null, $limit_from = null)
	{
		global $Engine, $DB, $Auth;

		$subitems_depth = $depth + 1;
		$allow_subitems = (($this->max_depth === true) || ($subitems_depth <= $this->max_depth));
		$output = array();

		$DB->SetTable($this->db_prefix . "items");
		$DB->AddJoin(ENGINE_DB_PREFIX . "folders.id", "folder_id");
		$DB->AddFields(array("id", "is_active", "folder_id", "link_suffix", "direct_link", "attach", "title", "descr", "options"));
		$DB->AddField(ENGINE_DB_PREFIX . "folders.title", "folder_title");
		$DB->AddField(ENGINE_DB_PREFIX . "folders.descr", "folder_descr");
        $DB->AddCondFP("is_active");
		$DB->AddCondFS("pid", "=", $pid);
		$DB->AddCondFS("group_id", "=", $this->group_id);

		if (!/*$Engine->ModuleOperationAllowed("group.items.handle", $this->group_id)*/$Engine->OperationAllowed($this->module_id, "group.items.handle", $this->group_id, $Auth->usergroup_id, $Engine->modules_privileges))
		{
			$DB->AddCondFP("is_active");
		}

		$DB->AddCondFP(ENGINE_DB_PREFIX . "folders.is_active");
		$DB->AddOrder("pos");
		//echo $DB->SelectQuery();
		$res = $DB->Select($limit, $limit_from, false);

		while ($row = $DB->FetchObject($res))
		{
			if ($row->direct_link
				|| (/*$Engine->ModuleOperationAllowed("item.view", $row->id)*/$Engine->OperationAllowed($this->module_id, "item.view", $row->id, $Auth->usergroup_id, $Engine->modules_privileges)
					&& $Engine->OperationAllowed(0, "folder.list", $row->folder_id, $Auth->usergroup_id)
					&& $Engine->OperationAllowed(0, "folder.view", $row->folder_id, $Auth->usergroup_id)))
			{
				$uri = is_null($row->direct_link) ? '' : ($row->direct_link ? $row->direct_link : ($Engine->FolderURIbyID($row->folder_id) . $row->link_suffix));
				$options = array();

				foreach (explode(";", $row->options) as $elem)
				{
					if ($elem)
					{
						$parts = explode(":", trim($elem));
						$options[$parts[0]] = $parts[1];
					}
				}

				if ($allow_subitems && $row->attach)
				{
					if (preg_match("/^\d+(:.+)?$/i", $row->attach))
					{
						$parts = explode(":", $row->attach);

						$res2 = $DB->Exec("
							SELECT `name`, `global_params`
							FROM `" . ENGINE_DB_PREFIX . "modules`
							WHERE `id` = '" . $parts[0] . "'
							LIMIT 1
							");

						if ($row2 = $DB->FetchObject($res2))
						{
							$DB->FreeRes($res2);
							require_once INCLUDES . $row2->name . ".class";
							$Module = new $row2->name(
								$row2->global_params,
								isset($parts[1]) ? $parts[1] : NULL,
								(int)$parts[0]
								);
							$subitems = $Module->Output();
						}

						else
						{
							$DB->FreeRes($res2);
							die($Engine->debug_level ? "EMenu module error #1001 at node $this->node_id: unknown module id ({$parts[0]}) in ATTACH string for menu item #$row->id." : "");
						}
					}

					else
					{
						die($Engine->debug_level ? "EMenu module error #1002 at node $this->node_id: illegal ATTACH format for menu item # $row->id (expecting format: module_id:param1;param2;param3...)." : "");
					}
				}

				elseif ($allow_subitems)
				{
					$subitems = $this->ListGroupItems($row->id, $subitems_depth);
				}

				else
				{
					$subitems = array();
				}
				
				$output[] = array(
					"menu_item_id" => (int)$row->id,
					"uri" => $uri,
					"uri_is_current" => CF::MenuURIis($uri),
					"title" => CF::Seek4NonEmptyStr($row->title, $row->direct_link ? "" : $row->folder_title ),
					"descr" => CF::Seek4NonEmptyStr($row->descr, $row->direct_link ? "" : $row->folder_descr),
					"options" => $options,
					"subitems" => $subitems
					);
			}
		}
		
		$DB->FreeRes($res);
		return $output;
	}
	
	function SearchFolders($q, &$array, $pid = 0, $group_id = null) 
	{
		global $Engine, $DB, $Auth;
		$DB->SetTable("engine_folders");
		//if($pid == 0) $DB->AddCondFS("pid", "=", $pid);
		$DB->AddCondFS("title", "LIKE", "" . CF::Utf2Win($q) . "%"); 
		//$DB->AddCondFS("group_id", "=", $group_id);
		//$DB->AddField("title", "name");
		//$DB->AddCondFP("id");
		$DB->AddCondFS("is_active", "=", 1); 
		$DB->AddOrder("pos");//echo $DB->SelectQuery(10); 
		$res = $DB->Select(10); 
		while($row = $DB->FetchAssoc($res)) {
			if($Engine->OperationAllowed(0, "folder.view", $row->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row->id, $Auth->usergroup_id))
			$array[] = $row;
			//$this->SearchFolders($q, $array, (int)$row->id, $group_id); 
		}
	}


	function Output()
	{
		return $this->output;
	}
	
	
	function MenuItems($action = NULL, $id = NULL)
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
				case "edit": $this->edit($id);
					break;
				case "edit_menugroup": $this->edit_menugroup($id);
					break;
				default:
					CF::Redirect();
			}
		else
        {
		
           //if ($Engine->ModuleOperationAllowed("cat.items.handle", $this->cat_id)) // ��� ���� ����� ���������!!!
           {
                $this->ProcessHTTPdata();
           } 
		   
			$this->output["groups"] = $this->MenuGroupsList();
        }
	}
	
	
	function add($id, $title = NULL, $descr = NULL, $group_id = NULL, $folder_id = NULL, $direct_link = NULL)
	{
        global $DB, $Engine;
		
        if (!empty($group_id))
            {
				$DB->SetTable("menu_items");
                $DB->AddValue("title", $title);
                $DB->AddValue("descr", $descr);
				$DB->AddValue("direct_link", $direct_link);
                $DB->AddValue("group_id", $group_id);
                $DB->AddValue("pid", $id);
				$DB->AddValue("folder_id", $folder_id);
				$DB->AddValue("is_active", 1);
                $DB->AddCondFS("pid","=", $id);
                $DB->SetPoser();                
                if ($new_id = $DB->InsertPosed()) {
					$Engine->LogAction($this->module_id, "MenuItem", $new_id, "add");
					$this->output["message"] = "����� ���� ��������";
				}
            }
        else
            $this->output["message"] = "���-�� �� ��� ��������. ���������� � ��������������.";
			
		if (isset($_FILES['attach']) && !empty($_FILES['attach']['name']))
			{	
				$orig_name = explode(".", $_FILES['attach']['name']);
				$name_parts = count($orig_name);
				$ext = $orig_name[$name_parts-1];
				
				//$new_id = $DB->LastInsertID();
				$new_name = $new_id.'.'.$ext;
				$path = $_SERVER['DOCUMENT_ROOT'].'/images/icons/'.$new_name;
				
				if (move_uploaded_file($_FILES['attach']['tmp_name'], $path)) {
				
					$DB->SetTable("menu_items");
					$DB->AddValue("options", "icon:".$new_name);
					$DB->AddCondFS("id", "=", $new_id);
					$DB->Update();
				}
			}
			
			CF::Redirect($Engine->engine_uri);
	}
	
	
	function delete($id)
	{
		global $DB, $Engine;
        
            $DB->SetTable("menu_items");
            $DB->AddAltFS("id", "=", $id);
            $DB->Delete();
			$Engine->LogAction($this->module_id, "MenuItem", $id, "delete");
            $this->output["message"] = "����� ���� �����. ������������ :(";
        
        CF::Redirect($Engine->engine_uri);
	}
    
    function hide($id)
    {
        global $DB, $Engine;
        $DB->SetTable("menu_items");
        $DB->AddValue("is_active", "!is_active", "X");
        $DB->AddCondFS("id", "=", $id);
		
        $DB->Update(1);
		$Engine->LogAction($this->module_id, "MenuItem", $id, "hide_show");
        CF::Redirect($Engine->engine_uri);
    }
	
	function posup($id)
    {
        global $DB, $Engine;
		
        $DB->SetTable("menu_items");
		$DB->AddField("pos");
		$DB->AddField("group_id");
		$DB->AddCondFS("id", "=", $id);
		$res = $DB->Select();
		
		$row = $DB->FetchObject($res);
		$pos_cur = $row->pos;
		$group_id = $row->group_id;
		
		$DB->SetTable("menu_items");
		$DB->AddField("id");
		$DB->AddField("pos");
		$DB->AddCondFS("pos", "<", $pos_cur);
		$DB->AddCondFS("group_id", "=", $group_id);
		$DB->AddOrder("pos", 1);
		$res = $DB->Select();
		
		$row = $DB->FetchObject($res);
		$pos_next = $row->pos;
		$id_next = $row->id;
		
        $DB->SetTable("menu_items");
		$DB->AddValue("pos", $pos_next, "X");
        $DB->AddCondFS("id", "=", $id);
        $DB->Update(1);
		
		$DB->SetTable("menu_items");
		$DB->AddValue("pos", $pos_cur, "X");
        $DB->AddCondFS("id", "=", $id_next);
        $DB->Update(1);
		$Engine->LogAction($this->module_id, "MenuItem", $id, "posup");
        CF::Redirect($Engine->engine_uri);
    }
	
	
	function posdown($id)
    {
        global $DB, $Engine;
        $DB->SetTable("menu_items");
		$DB->AddField("pos");
		$DB->AddField("group_id");
		$DB->AddCondFS("id", "=", $id);
		$res = $DB->Select();
		
		$row = $DB->FetchObject($res);
		$pos_cur = $row->pos;
		$group_id = $row->group_id;
		
		$DB->SetTable("menu_items");
		$DB->AddField("id");
		$DB->AddField("pos");
		$DB->AddCondFS("pos", ">", $pos_cur);
		$DB->AddCondFS("group_id", "=", $group_id);
		$DB->AddOrder("pos");
		$res = $DB->Select();
		
		$row = $DB->FetchObject($res);
		$pos_next = $row->pos;
		$id_next = $row->id;
		
        $DB->SetTable("menu_items");
		$DB->AddValue("pos", $pos_next, "X");
        $DB->AddCondFS("id", "=", $id);
        $DB->Update(1);
		
		$DB->SetTable("menu_items");
		$DB->AddValue("pos", $pos_cur, "X");
        $DB->AddCondFS("id", "=", $id_next);
        $DB->Update(1);
		$Engine->LogAction($this->module_id, "MenuItem", $id, "posdown");
        CF::Redirect($Engine->engine_uri);
    }
	
	
    
    /*function edit()
    {
        global $DB, $Engine;        
        CF::Redirect($Engine->engine_uri);
    }*/
	
	
	function MenuGroupsList()
	{
		
		global $DB, $Engine, $Auth;
		$list = array(); // ������ ����� ����
		$DB->SetTable("menu_groups");
        $DB->AddCondFP("id");
        $DB->AddOrder("pos");
		$res = $DB->Select();
		
		while ($row = $DB->FetchObject($res))
		{

                $sublist = $this->MenuList(0, 2, $row->id);

                    
				$list[$row->id] = array(
					"id" => $row->id,
					"name" => $row->name,
					"descr" => $row->descr,
					"manage_groups" => $Engine->OperationAllowed($this->module_id, "module.groups.handle", $row->id, $Auth->usergroup_id),
                    "manage_items" => $Engine->OperationAllowed($this->module_id, "group.items.handle", $row->id, $Auth->usergroup_id),					
					"subitems" => $sublist
					);
		}
		return $list;
	}
	
	
	function MenuList($pid, $depth = 1, $group_id)
	{
		global $DB, $Engine, $Auth;
		$list = array(); // ������ ������� ����
		$DB->SetTable("menu_items");
		$DB->AddJoin(ENGINE_DB_PREFIX . "folders.id", "folder_id");
		$DB->AddFields(array("id", "is_active", "folder_id", "group_id", "link_suffix", "direct_link", "attach", "title", "descr", "options"));
		$DB->AddField(ENGINE_DB_PREFIX . "folders.title", "folder_title");
		$DB->AddField(ENGINE_DB_PREFIX . "folders.descr", "folder_descr");
		$DB->AddCondFS("pid", "=", $pid);
		$DB->AddCondFS("group_id", "=", $group_id);
        $DB->AddCondFP("id");
		
        $DB->AddOrder("pos");
		$res = $DB->Select();
		while ($row = $DB->FetchObject($res))
		{
				if($depth--)
                    $sublist = $this->MenuList($row->id, $depth, $group_id);
                else 
                    $sublist = NULL;
					
				if (!$title = $row->title)
					$title = $row->folder_title;
					
				if (!$descr = $row->descr)
					$descr = $row->folder_descr;
					

				$list[$row->id] = array(
					"uri" => $Engine->FolderURIbyID($row->folder_id),
					"title" => $title,
					"descr" => $descr,
					"direct_link" => $row->direct_link,
					"group_id" => $row->group_id,
					"is_active" => $row->is_active,
					"manage_groups" => $Engine->OperationAllowed($this->module_id, "module.groups.handle", $row->id, $Auth->usergroup_id),
                    "manage_items" => $Engine->OperationAllowed($this->module_id, "group.items.handle", $row->id, $Auth->usergroup_id),					
					"subitems" => $sublist
					);				
		}
		return $list;
	}
	
	function edit($id)
    {
        global $DB, $Engine;        
        
        if (isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id])) {		
			if (isset($_POST[$this->node_id]["save"]["icon_delete"]) && $_POST[$this->node_id]["save"]["icon_delete"] == "on") {
				$DB->SetTable("menu_items");
				$DB->AddField("options");
				$DB->AddCondFS("id", "=", $id);
				$res = $DB->Select();
				$row = $DB->FetchAssoc($res);
				
				foreach (explode(";", $row["options"]) as $elem) {
					if ($elem) {
						$parts = explode(":", trim($elem));
						$options[$parts[0]] = $parts[1];
					}
				}
				
				if (isset($options["icon"])) {
					$path = $_SERVER['DOCUMENT_ROOT'].'/images/icons/'.$options["icon"];
					unlink($path);
				}
				
				$DB->SetTable("menu_items");
				$DB->AddValue("options", "");
				$DB->AddCondFS("id", "=", $id);
				$DB->Update();
			}
			
			elseif (isset($_FILES['attach']) && !empty($_FILES['attach']['name'])) {	
				$DB->SetTable("menu_items");
				$DB->AddField("options");
				$DB->AddCondFS("id", "=", $id);
				$res = $DB->Select();
				$row = $DB->FetchAssoc($res);
				
				foreach (explode(";", $row["options"]) as $elem) {
					if ($elem) {
						$parts = explode(":", trim($elem));
						$options[$parts[0]] = $parts[1];
					}
				}
				/*if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/images/icons/')) {
					mkdir($_SERVER['DOCUMENT_ROOT'].'/images/icons/');
				}*/
				if (isset($options["icon"])) {
					$path = $_SERVER['DOCUMENT_ROOT'].'/images/icons/'.$options["icon"];
					unlink($path);
				}
			
				$orig_name = explode(".", $_FILES['attach']['name']);
				$name_parts = count($orig_name);
				$ext = $orig_name[$name_parts-1];
				
				$new_name = $id.'.'.$ext;
				$path = $_SERVER['DOCUMENT_ROOT'].'/images/icons/'.$new_name;
				
				if (move_uploaded_file($_FILES['attach']['tmp_name'], $path)) {	
					$DB->SetTable("menu_items");
					$DB->AddValue("options", "icon:".$new_name);
					$DB->AddCondFS("id", "=", $id);
					$DB->Update();
				}
			}
		
            if (isset($_POST[$this->node_id]["save"])) {
                $DB->SetTable("menu_items");
                $DB->AddCondFS("id", "=", $id);
                $DB->AddValue("title", $_POST[$this->node_id]["save"]["title"]);
                $DB->AddValue("descr", $_POST[$this->node_id]["save"]["descr"]);
                $DB->AddValue("direct_link", $_POST[$this->node_id]["save"]["direct_link"]);
				$DB->AddValue("folder_id", $_POST[$this->node_id]["save"]["folder_id"]);
                $DB->Update(1);
				$Engine->LogAction($this->module_id, "MenuItem", $id, "edit");
                CF::Redirect($Engine->engine_uri);
            }
        }
        
        {
            $DB->SetTable("menu_items");
            $DB->AddCondFS("id", "=", $id);
            $res = $DB->Select(1);
            if($row = $DB->FetchAssoc($res))
            {
                $this->output["menu_item"] = $row;
                $this->output["folders"] = array();
                $items = $this->FolderTree(0, false);
				//die(print_r($items));
				$this->output["folders"] = $items;
               
            }
        }
    }
	
	function edit_menugroup ($id) {
		global $DB, $Engine;        
        
        if (isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id])) {
			if (isset($_POST[$this->node_id]["save"]["descr"])) {
				$DB->SetTable("menu_groups");
				$DB->AddValue("descr", $_POST[$this->node_id]["save"]["descr"]);
				$DB->AddCondFS("id", "=", $id);
				$DB->Update();
				$Engine->LogAction($this->module_id, "MenuGroup", $id, "edit");
				CF::Redirect($Engine->engine_uri);
			}
		}
		else {
			$DB->SetTable("menu_groups");
			$DB->AddField("descr");
			$DB->AddCondFS("id", "=", $id);
			$res = $DB->Select();

			$row = $DB->FetchAssoc($res);
			
			$this->output["menu_group"]["descr"] = $row["descr"];
			$this->output["scripts_mode"] = $this->output["mode"] = "edit_menugroup";
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
                if (isset($_POST[$this->node_id]["add"]))
                    $this->add($_POST[$this->node_id]["add"]["id"],
                            $_POST[$this->node_id]["add"]["name"],
                            $_POST[$this->node_id]["add"]["descr"],
                            $_POST[$this->node_id]["add"]["uri_part"],
                            isset($_POST[$this->node_id]["add"]["texter"]) ? $_POST[$this->node_id]["add"]["texter"] : NULL
                    );
        } 
    }
    
    
    function SubmenuSpisok($pid, $uri, $depth = null, $hiddenItemsIds = array())
    {         
        global $Engine, $DB, $Auth;
        $output = null;
		
        $DB->SetTable("engine_folders");
        $DB->AddCondFS("pid", "=", $pid);
        $DB->AddCondFS("id", "!=", "0");
        $DB->AddCondFP("is_active");
        $DB->AddOrder("pos");
        //echo $DB->SelectQuery();
        $res2 = $DB->Select();
        while (($row2 = $DB->FetchObject($res2)) && (is_null($depth) || $depth > 0)) {
            if($row2->is_active && !in_array($row2->id, $hiddenItemsIds) && $Engine->OperationAllowed(0, "folder.view", $row2->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row2->id, $Auth->usergroup_id)) {	
                $uri2 = $uri . $row2->uri_part."/" ? $uri . $row2->uri_part."/" : ($Engine->FolderURIbyID($row2->id));
				if(is_null($depth)) {
					$sub = $this->SubmenuSpisok($row2->id, $uri2, $hiddenItemsIds);
					$output[] = array(
					    "submenu_id" => (int)$row2->id,
					    "uri_part" => $row2->uri_part,
					    "title" => $row2->title,
					    "descr" => $row2->descr,
					    "uri" => $uri2,
					    "uri_is_current" => CF::URIin($uri2),
						"submenu" => $sub
					);
				} elseif($depth > 0) {
					$sub = $this->SubmenuSpisok($row2->id, $uri2, $depth-1, $hiddenItemsIds);
					$output[] = array(
					    "submenu_id" => (int)$row2->id,
					    "uri_part" => $row2->uri_part,
					    "title" => $row2->title,
					    "descr" => $row2->descr,
					    "uri" => $uri2,
					    "uri_is_current" => CF::URIin($uri2),
						"submenu" => $sub
					);
				}
                
                //$output["submenu"] = $this->SubmenuSpisok($row2->id, $uri); 
            }
        }
        $DB->FreeRes($res2);
		
        if(isset($output))
            return $output;
        else
            return NULL;    
    }
      
    
    function SubitemsList($group_id, $parts, $submenu_only = false)
    //���������� ������ ���������� �������� ������ ����.
    {
        global $Engine, $DB, $Auth;
        
        $output = array();
		$hiddenItemsIds = isset($parts[4]) ?  explode(',', $parts[4]) : array() ;
		
        if(empty($parts[3])) { 
			$depth = (isset($parts[2]) && $parts[2] > 0) ? $parts[2]-1 : null;
			$DB->SetTable("menu_items");
			$DB->AddCondFS("group_id", "=", $group_id);
			$DB->AddCondFP("is_active");
			$res = $DB->Select();
			while ($row = $DB->FetchObject($res)) {
				$uri = $row->direct_link ? $row->direct_link : ($Engine->FolderURIbyID($row->folder_id) . $row->link_suffix);
				if(CF::URIin($uri)) {
					$DB->SetTable("engine_folders");
					$DB->AddCondFS("pid", "=", $row->folder_id);
					$DB->AddCondFP("is_active");
					$DB->AddOrder("pos");
                  
					$res2 = $DB->Select();
					while ($row2 = $DB->FetchObject($res2)) {
						
						if( $row2->is_active && $Engine->OperationAllowed(0, "folder.view", $row2->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row2->id, $Auth->usergroup_id)) {
							$uri2 = $uri . $row2->uri_part ."/" ? $uri . $row2->uri_part."/" : ($Engine->FolderURIbyID($row2->id));           
							$sub = $this->SubmenuSpisok($row2->id, $uri2, $depth, $hiddenItemsIds);
							$output[] = array(
								"submenu_id" => (int)$row2->id,
								"uri_part" => $row2->uri_part,
								"title" => $row2->title,
								"descr" => $row2->descr,
								"uri" => $uri2,
								"uri_is_current" => CF::URIin($uri2),
								"submenu" => $sub
							);
						}
					}
					$DB->FreeRes($res2);
				}        
			}
        } else { 
			$depth = (isset($parts[2]) && $parts[2] > 0) ? $parts[2] : null;
            $DB->SetTable("engine_folders");
            $DB->AddCondFS("id", "=", $parts[3]);
            $DB->AddCondFP("is_active");
            $DB->AddOrder("pos");      
            $res2 = $DB->Select();
            while ($row2 = $DB->FetchObject($res2))
            {	
                if($row2->is_active && $Engine->OperationAllowed(0, "folder.view", $row2->id, $Auth->usergroup_id) && $Engine->OperationAllowed(0, "folder.list", $row2->id, $Auth->usergroup_id))
                {
                    $uri2 = /*$row2->uri_part ."/" ? $row2->uri_part."/" : */($Engine->FolderURIbyID($row2->id));           
                    $sub = $this->SubmenuSpisok($row2->id, $uri2, $depth, $hiddenItemsIds);
					if($submenu_only) {
						$output = $sub;
					} else {
						$output[] = array(
							"submenu_id" => (int)$row2->id,
							"uri_part" => $row2->uri_part,
							"title" => $row2->title,
							"descr" => $row2->descr,
							"uri" => $uri2,
							"uri_is_current" => CF::URIin($uri2),
							"submenu" => $sub
						);
					}
                }
            }
            $DB->FreeRes($res2);    
        }
        return $output;
    }
    
    function Submenu($parts, $module_uri, $submenu_only = false)
    {
        global $Engine;
		
        $this->group_id = (int)$parts[1];
        if ($Engine->ModuleOperationAllowed("group.view", $this->group_id)) { 
            $this->output["items"] = $this->SubitemsList($this->group_id, $parts, $submenu_only);           
        }
    }	
}

?>