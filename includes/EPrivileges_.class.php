<?php

class EPrivileges
// version: 0.3
// date: 2010-04-19
{
	var $output;
	var $node_id;
	var $module_id;
	var $mode;
	var $db_prefix;
	

	function EPrivileges($global_params, $params, $module_id, $node_id, $module_uri, $additional)
	{
		global $Engine, $Auth, $DB;
	 		
		$this->node_id = $node_id;
		$this->output["module_id"] = $this->module_id = $module_id;
		
		$this->output["mode"] = $this->mode = $params;
		$this->output["user_displayed_name"] = $Auth->user_displayed_name;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
		
		/*if (!$global_params)
		{
			die("EPrivileges module error #1001: config file not specified.");
		}
		elseif (!$array = @parse_ini_file(INCLUDES . $global_params, true))
		{
			die("EPrivileges module error #1002: cannot process config file '$global_params'.");
		}*/
		
		if (ALLOW_AUTH && $Engine->OperationAllowed(0, "engine.privileges.handle", -1, $Auth->usergroup_id))
		{
			switch ($this->mode)
			{
				case "give_privileges":
					if (!$module_uri) {
						$this->output["mode"] = "show_modules";
						$this->show_modules();
					}
					elseif (isset($_GET["delete_name"]) && isset($_GET["delete_module"])) {
						$this->delete_privilege();
					}
					else {
						$module_uri = explode("/", $module_uri);
						$this->output["mode"] = "privileges_form";
						if (isset($_POST[$this->node_id]["give_privileges"]["module_id"])) {
							$this->insert_privilege();
						}
						$this->show_form($module_uri[0]);
						$this->show_privileges($module_uri[0]);
					}
					
					break;
				case "group_privileges":
					$module_uri = explode("/", $module_uri);
					if (isset($_GET["delete_name"]) && isset($_GET["delete_module"])) {
						$this->delete_privilege();
					}
					if($module_uri[0] == "edit") {
						$this->output["mode"] = "privileges_form";
						$this->show_privileges(NULL, $module_uri[1]);
					}
					break;				
				case "add_operation":
					$this->add_operation();
					break;
						
				case "get_modules":
					$this->get_modules();
					break;
					
				case "get_tables":
					$this->get_tables();
					break;		
				
				case "get_cols":
					$this->get_cols();
					break;
				
				case "get_op_entries_info":
					$this->output= ( (isset($additional) && isset($additional['opName'])) ? $this->get_op_entries_info($additional['opName']) : false);
					break;
					
				case "get_operation_entries":
					$reqParams = isset($additional) ? $additional : $_REQUEST['data'];
					if (isset($reqParams['fields']['source_table'], $reqParams['fields']['key_field'], $reqParams['fields']['display_field'], $reqParams['branch_id']))
						$this->output = array('response' => $this->get_items_tree($reqParams['fields']['source_table'], $reqParams['fields']['key_field'], $reqParams['fields']['parent_field'] != 'null' ? $reqParams['fields']['parent_field'] : null, $reqParams['fields']['display_field'], $reqParams['branch_id'], $reqParams['fields']['children_table'] != 'null' ? $reqParams['fields']['children_table'] : null, $reqParams['fields']['children_table_parent_field'] != 'null' ? $reqParams['fields']['children_table_parent_field'] : null, $reqParams['fields']['alias_path'] != 'null' ? $reqParams['fields']['alias_path'] : null));
					else 
						$this->output = array();
					break;
			}
		}
		else
		{
			$this->output["messages"]["bad"][] = "Доступ запрещён.";
		}
	}
	
	
	function fillJoinPart($aliasParts) 
	{
		$tables = array();
		$fields = array();
		$joinPart = '';
		foreach($aliasParts as $aliasPart) {
			$aliasInfo = explode('.', $aliasPart);
			$tables[] = $aliasInfo[0];
			$fields[] = $aliasInfo[1];
		}
		for($ind=1; $ind < count($tables); $ind++) {
			if ($tables[$ind] != $tables[$ind-1]) {
				$joinPart.= ' left join '.$tables[$ind].' on '.$tables[$ind].'.'.$fields[$ind].'='.$tables[$ind-1].'.'.$fields[$ind-1];
			}
		}
		return $joinPart;
	}
	
	
	function get_entries_aliases($aliasInfo, $displayParentItem = null) 
	{ 
		global $Engine, $DB;
					
		$joinedTables = false;
		//$aliasInfo = mysql_real_escape_string($aliasInfo);
		$aliasParts = explode('->', $aliasInfo);
		$displayValueInfo = explode('.', $aliasParts[0]);
		$displayValue = $displayValueInfo[1]; 
		$displayValueTable = $displayValueInfo[0];
		$keyValueInfo = explode('.', $aliasParts[count($aliasParts)-1]);
		$keyValue = $keyValueInfo[1]; 
		$keyValueTable = $keyValueInfo[0];
		if ($displayValueTable != $keyValueTable) {
			$joinedTables = true;
			$displayValue = $displayValueTable.'.'.$displayValue; 
			$keyValue = $keyValueTable.'.'.$keyValue;
		}
		$queryPattern = "select %DISPLAY_VALUE% as id, %KEY_VALUE% as alias from %DISPLAY_VALUE_TABLE% %JOIN_PART% where %DISPLAY_VALUE_COND% %KEY_VALUE% is not null";
		
		$query = str_replace(array('%DISPLAY_VALUE%', '%DISPLAY_VALUE_TABLE%', '%KEY_VALUE%', '%JOIN_PART%', '%DISPLAY_VALUE_COND%'), 
				array($displayValue, $displayValueTable, $keyValue, $joinedTables ? $this->fillJoinPart($aliasParts) : '', is_null($displayParentItem) ? '' : $displayParentItem['field'].'='.$displayParentItem['value'].' and ' ), $queryPattern);
		
		$res = $DB->Exec($query);
		
		$entriesInfo = array();
		while($row = $DB->FetchAssoc($res)) {
			//$row['title'] = iconv('windows-1251', 'utf-8', $row['title']);
			$entriesInfo[$row['id']][] = $row['alias'];
		}
		
		return $entriesInfo;
	}
	
	
	function get_op_entries_info($op_name) 
	{
		global $DB;
		$op_info = array();
		$DB->SetTable("operations_entries");
		$DB->AddCondFS("operation_name", "=", $op_name);
		$res = $DB->Select();
		$levelCount = $DB->NumRows($res);
		while ($row = $DB->FetchAssoc($res)) 
			$op_info[$row['level']] = $row;
		foreach($op_info as $ind=>$info) {
			$op_info[$ind]['has_children_field'] = 'hasChildren';
			if ($ind < $levelCount - 1) {
				$op_info[$ind]['children_table'] = $op_info[$ind+1]['source_table']; 
				$op_info[$ind]['children_table_parent_field'] = $op_info[$ind+1]['parent_field'];
			} else if ($ind == 0 && $op_info[0]['parent_field']) {
				$op_info[$ind]['children_table'] = $op_info[0]['source_table']; 
				$op_info[$ind]['children_table_parent_field'] = $op_info[0]['parent_field'];
			}
		}
		return array('info'=>count($op_info) ? $op_info : null);
	}
	
	
	function get_tables()
	{
		global $DB;
		$tables =  array();
		$res = $DB->Exec('show tables from nsau');
		while ($row = $DB->FetchRow($res))
			$tables[] = iconv('windows-1251', 'utf-8', $row[0]);
		$this->output = $tables;
	}
	
	
	function get_cols()
	{
		global $DB;
		$cols =  array();
		$res = $DB->Exec("select column_name from information_schema.columns where table_name = '".$_REQUEST['data']['table']."' and table_schema = 'nsau'");
		while ($row = $DB->FetchRow($res))
			$cols[] = iconv('windows-1251', 'utf-8', $row[0]);
		$this->output = $cols;
	}
	
	function get_modules()
	{
		global $DB;
		$modules = array(array('id'=>0, 'comment'=> iconv('windows-1251', 'utf-8', 'Системные операции')));
		$DB->SetTable("engine_operations", "op");
		$DB->AddTable("engine_modules", "m");
		$DB->AddCondFF("op.module_id", "=", "m.id");
		$DB->AddField("m.id");
		$DB->AddField("m.comment");
		$res = $DB->Select(null, null, true, true, true);
		while ($row = $DB->FetchAssoc($res)) {
			if($row['id'])
				$modules[] = array('id'=>$row['id'], 'comment'=>iconv('windows-1251', 'utf-8', $row['comment']));
		}
		$this->output = $modules;
	}
	
	
	function add_operation()
	{
		global $DB;
		$DB->SetTable("engine_operations");
		foreach ($_REQUEST['data'] as $field=>$value) {
			if ($field == 'comment')
				$value = iconv('utf-8', 'windows-1251', $value);
			$DB->AddValue($field, $value);
		}
		$this->output = $DB->Insert();
	}
	
	
	function show_modules()
	{
		global $DB, $Engine;
		
		$DB->SetTable("engine_modules");
		$DB->AddField("id");
		$DB->AddField("comment");
		$res = $DB->Select();
		
		while ($row = $DB->FetchAssoc($res)) {
			if($row["id"]) {
				$DB->SetTable("engine_operations");
				$DB->AddField("operation_name");
				$DB->AddCondFS("module_id", "=", $row["id"]);
				$res_ops = $DB->Select();
				
				if ($DB->FetchAssoc($res_ops))
					$this->output["modules"][] = $row;
			}			
		}
	}
	
	
	function show_form($module_id) {
		global $DB;
		
		$DB->SetTable("engine_modules");
		$DB->AddField("comment");
		$DB->AddCondFS("id", "=", $module_id);
		$res = $DB->Select();
		
		if (!$module_id || $row = $DB->FetchAssoc($res)) {
			if ($module_id)
				$this->output["module_name"] = $row["comment"];
			else
				$this->output["module_name"] = "Системные операции";
				
			$this->output["op_module_id"] = $module_id;
			
			$DB->SetTable("engine_operations");
			$DB->AddField("operation_name");
			//$DB->AddField("is_inheritable");
			//$DB->AddField("entry_alias_path");
			$DB->AddField("comment");
			$DB->AddCondFS("module_id", "=", $module_id);
			$DB->AddOrder("comment");
			$res = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res)) {
				$this->output["operations"][] = $row;
			}
			
			if (!empty($this->output["operations"])) { 
				$DB->SetTable("operations_entries");
				$DB->AddCondFS("operation_name", "=", $this->output["operations"][0]["operation_name"]);
				$res = $DB->Select();
			
				if ($DB->AffectedRows($res)) {
					$this->output["entries_info"] = 1;
				}
			}
			
			$DB->SetTable("auth_usergroups");
			$DB->AddField("id");
			$DB->AddField("comment");
			$DB->AddOrder("comment");
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res)) {
				$this->output["usergroups"][] = $row;
			}
		}
		else {
			$this->show_form(0);
		}
	}

	
	function insert_privilege()
	{
		global $DB, $Engine, $Auth;
		
		if (isset ($_POST[$this->node_id]["give_privileges"]["all_entries"]) && $_POST[$this->node_id]["give_privileges"]["all_entries"] == "on")
			$entry_ids = array(-1);
		else
		{
			$entry_ids =  (strpos($_POST[$this->node_id]["give_privileges"]["entry_id"], ',') === false) ? array($_POST[$this->node_id]["give_privileges"]["entry_id"]) : explode(',', $_POST[$this->node_id]["give_privileges"]["entry_id"]);
			if (isset ($_POST[$this->node_id]["give_privileges"]["child_entries"]) && $_POST[$this->node_id]["give_privileges"]["child_entries"] == "on")
			{   
				//$childEntries = $this->get_items_tree();
				$DB->SetTable("operations_entries");
				$DB->AddField("level");
				$DB->AddField("alias_path");
				$DB->AddField("source_table");
				$DB->AddField("key_field");
				$DB->AddField("parent_field");
				$DB->AddField("display_field");  // необязательно, добавить вариант вызова ф-ии get_items_tree без displayField
					
				$DB->AddCondFS("operation_name", "=", $_POST[$this->node_id]["give_privileges"]["operation_name"]);
				if ($_POST[$this->node_id]["give_privileges"]["entry_branch_level"])
					$DB->AddAltFS("level", "=", $_POST[$this->node_id]["give_privileges"]["entry_branch_level"]);
				$DB->AddAltFS("level", "=", 0);
				$DB->AppendAlts();
				$DB->AddOrder("level", true);
				$res = $DB->Select(1);
				{
					if ($row = $DB->FetchAssoc($res))
					{
						list($table_folder_name, $table_field_name, $table_parent_field_name) = explode('.', $row[0]);
						$parent_ids = $entry_ids;
						while (!empty($parent_ids))
						{
							$res = $DB->Exec("select ".$table_field_name." from ".$table_folder_name." where ".$table_parent_field_name." in (".implode(',', $parent_ids).")");
							$parent_ids = array();
							while ($row = $DB->FetchRow($res))
							{
								$entry_ids[] = $parent_ids[] = $row[0];
							}
						}
					}
				} 
			}
		}
		
		$do = $_POST[$this->node_id]["give_privileges"]["is_allowed"] ? "разрешение" : "запрет";
		
		$DB->SetTable("auth_usergroups");
		$DB->AddCondFS("id", "=", $_POST[$this->node_id]["give_privileges"]["usergroup_id"]);
		$res = $DB->Select();
		$row = $DB->FetchAssoc($res);
		
		$is_error = false;
		foreach($entry_ids as $entry_id)
			if (!$Engine->AddPrivilege($_POST[$this->node_id]["give_privileges"]["module_id"], $_POST[$this->node_id]["give_privileges"]["operation_name"], $entry_id, $_POST[$this->node_id]["give_privileges"]["usergroup_id"], $_POST[$this->node_id]["give_privileges"]["is_allowed"], $_POST[$this->node_id]["give_privileges"]["operation_name"].";".$_POST[$this->node_id]["give_privileges"]["usergroup_id"]))
			{
				$is_error = true;
				break;
			} 
		if ($is_error)	$this->output["messages"]["bad"][] = "Ошибка";
		else 	$this->output["messages"]["good"][] = "Операция прошла успешно";
	}
	
	
	function show_privileges($module_id)
	{
		global $DB;
		
		$DB->SetTable("engine_privileges");
		$DB->AddField("operation_name");
		$DB->AddField("entry_id");
		$DB->AddField("usergroup_id");
		$DB->AddField("is_allowed");
		$DB->AddCondFS("module_id", "=", $module_id);
		$res = $DB->Select();
		
		$i = 0;

		while ($row = $DB->FetchAssoc($res)) {
			$DB->SetTable("engine_operations");
			$DB->AddField("comment");
			$DB->AddCondFS("operation_name", "=", $row["operation_name"]);
			$DB->AddCondFS("module_id", "=", $module_id);
			$res_op = $DB->Select();
			$row_op = $DB->FetchAssoc($res_op);
			if ($row_op["comment"])
				$this->output["privileges"][$i]["operation"] = $row_op["comment"];
			else
				$this->output["privileges"][$i]["operation"] = $row["operation_name"];
				
			$this->output["privileges"][$i]["operation_code"] = $row["operation_name"];
			$this->output["privileges"][$i]["module_id"] = $module_id;
			
			if ($row["usergroup_id"] != "-1" && $row["usergroup_id"]) {
				$DB->SetTable("auth_usergroups");
				$DB->AddField("comment");
				$DB->AddCondFS("id", "=", $row["usergroup_id"]);
				$res_ug = $DB->Select();
				$row_ug = $DB->FetchAssoc($res_ug);
				
				$this->output["privileges"][$i]["usergroup"] = $row_ug["comment"];
			}
			else {
				$this->output["privileges"][$i]["usergroup"] = $row["usergroup_id"];
			}
			
			$this->output["privileges"][$i]["entry_id"] = $row["entry_id"];
			$this->output["privileges"][$i]["is_allowed"] = $row["is_allowed"];
			$this->output["privileges"][$i]["usergroup_id"] = $row["usergroup_id"];
			$i++;
		}
		
	}
	
	
	function delete_privilege ()
	{
		global $DB, $Engine;
		
		$Engine->DeletePrivilege($_GET["delete_module"], $_GET["delete_name"], $_GET["delete_entry"], $_GET["delete_usergroup"], $_GET["delete_allowed"]);
		CF::Redirect($Engine->unqueried_uri);
	}
	
	
	function get_items_tree($table, $keyField, $parentField, $displayField, $curBranchId, $childrenTable, $childrenTableParentField, $aliasPath) {
	
		global $DB, $Engine;
		$subTree = array();
		
		$res = $DB->Exec("SELECT ".$keyField.",".$displayField." FROM ". $table. ( is_null($parentField) ? "" : " WHERE ".$parentField. " = ".$curBranchId));
		while ($row = $DB->FetchAssoc($res)) {
			$row[$displayField] = iconv('windows-1251', 'utf-8', $row[$displayField]);
			$subTree[] = $row;
		} 
		
		if (!is_null($aliasPath)) {
			$aliases = $this->get_entries_aliases($aliasPath, array('field'=> $parentField, 'value'=> $curBranchId));
		}
		
		foreach($subTree as $ind=>$item) {
			if (!is_null($aliasPath))
				$subTree[$ind]['aliasField'] = $aliases[$item[$keyField]];
			if (!is_null($childrenTable)) { 
				$row = $DB->FetchRow($DB->Exec("SELECT COUNT(*) FROM " .$childrenTable. " WHERE " .$childrenTableParentField. " = ". $item[$keyField] ));
				$subTree[$ind]['hasChildren'] =  ($row[0] && $item[$keyField]) ? 1 : 0; //($row = $DB->FetchRow($DB->Exec("SELECT COUNT(*) FROM " .$childrenTable. " WHERE " .$childrenTableParentField. " = ". $item[$keyField] )) ? 1 : 0);
			} 
			/*else if (!is_null($parentField)) {  // продумать вариант, когда parentField может быть null
				$row = $DB->FetchRow($DB->Exec("SELECT COUNT(*) FROM " .$table. " WHERE " .$parentField. " = ". $item[$keyField] ));
				$subTree[$ind]['hasChildren'] = ($row[0] && $item[$keyField]) ? 1 : 0;
			}
			else break; */
		} 
		
		return $subTree;
	}

	
	function ManageHTTPdata()
	{
		return;
	}
	
	
	function Output()
	{
		return $this->output;
	}
}

?>