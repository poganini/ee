<?php

class EPrivileges
// version: 1.1
// date: 2014-01-12
{
	var $output;
	var $node_id;
	var $module_id;
	var $mode;
	var $db_prefix;


	function EPrivileges($global_params, $params, $module_id, $node_id, $module_uri)
	{
		global $Engine, $Auth, $DB;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		
		$this->output["mode"] = $this->mode = $params;
		$this->output["user_displayed_name"] = $Auth->user_displayed_name;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
		
		if (isset($_SESSION["messages"])) {
			$this->output["messages"] = $_SESSION["messages"];
			unset($_SESSION["messages"]);
		}
		
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
					$this->output["scripts_mode"] = $this->output["mode"] = "give_privileges";
          $this->output["plugins"][] = "jquery.ui.autocomplete.min";
					if (!$module_uri) {
						
						$this->output["mode"] = "show_modules";
						$this->show_modules();
					}
					elseif (isset($_GET["delete_name"]) && isset($_GET["delete_module"])) {
						$this->delete_privilege();
					}
					else {
						$module_uri = explode("/", $module_uri);
						$this->output["scripts_mode"] = $this->output["mode"] = "privileges_form";
            
            $this->output['current_module_id'] = $this->module_id;
						
						
						if (isset($_POST[$this->node_id]["give_privileges"]["module_id"]))
							$this->insert_privilege();
						
						$this->show_form($module_uri[0]);
						//$this->show_element($module_uri[0]);
					
						$this->show_privileges($module_uri[0]);
						
						/*echo "<pre>111";
		print_r($this->output["scripts_mode"]);
		echo "</pre>";*/
					}
					
					break;
				case "group_privileges":
					$module_uri = explode("/", $module_uri);
if (isset($_GET["delete_name"]) && isset($_GET["delete_module"])) {
						$this->delete_privilege();
					}
					if($module_uri[0] == "edit") {
						 $this->output["scripts_mode"] = $this->output["mode"] = "privileges_form";
						$this->show_privileges(NULL, $module_uri[1]);
					}
					break;
		        case "ajax_autocomplete_element":
				case "ajax_get_elements":
					$this->output["scripts_mode"] = $this->output["mode"] = "ajax_get_elements"; 
          
		            $this->output["elements"] = array();
		            if($this->mode != "ajax_autocomplete_element") $this->output["elements"][] = array('id' => -1, 'name'  => CF::Win2Utf('Все элементы'));
              
					$DB->SetTable("engine_operations");
					//$DB->AddField("operation_name");
					//$DB->AddField("comment");
					$DB->AddField("table_name");
			        $DB->AddField("name_field");
					$DB->AddCondFS("module_id", "=", $_REQUEST["data"]["module"]);
					$DB->AddCondFS("operation_name", "=", $_REQUEST["data"]["operation"]);
          
					$res = $DB->Select();
					while ($row = $DB->FetchAssoc($res)) {
						if($row["table_name"]) {
							$DB->SetTable($row["table_name"]);
							$DB->AddField("id");
							if(empty($row["name_field"])) {
								$DB->AddField("name");
								$name_exp = "name";
							}			                
			                else {
								$DB->AddExp($row["name_field"], "name");
								$name_exp = $row["name_field"];
							}
			                
			              
			                if(isset($_REQUEST["data"]["q"])) {
			                  $DB->AddCondXS($name_exp, "LIKE", "".iconv("utf-8", "Windows-1251", $_REQUEST['data']["q"])."%");
			                }
			              
			                $DB->AddOrder("name");
							$res_elem = $DB->Select(); 
							
							while($row_elem = $DB->FetchAssoc($res_elem)) {
								$row_elem['name'] = CF::Win2Utf($row_elem['name']);
				                $this->output["elements"][] = $row_elem; 
							}
						}
					}
          
          if($this->mode == "ajax_autocomplete_element") {
            $this->output["json"] = $this->output["elements"];
          } 
          break; 					
			}
				
		}

		else
		{
			$this->output["messages"]["bad"][] = "Доступ запрещён.";
		}
	}
	
	
	function show_modules()
	{
		global $DB, $Engine;
		
		$DB->SetTable("engine_modules");
		$DB->AddField("id");
		$DB->AddField("comment");
		$res = $DB->Select();
		
		while ($row = $DB->FetchAssoc($res)) {
			if($row['id']) {
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
				
			$this->output["module_id"] = $module_id;
			
			$DB->SetTable("engine_operations");
			$DB->AddField("operation_name");
			$DB->AddField("comment");
			$DB->AddField("table_name");
			$DB->AddCondFS("module_id", "=", $module_id);
			$res = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res)) {
				$this->output["operations"][] = $row;
			}
			
				/*echo "<pre>111";
		print_r($this->output["operations"]);
		echo "</pre>";	*/
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
	
	function show_element($module_id) {
		global $DB;
		
		$DB->SetTable("engine_modules");
		$DB->AddField("comment");
		$DB->AddCondFS("id", "=", $module_id);
		$res = $DB->Select();
		
			while ($row = $DB->FetchAssoc($res)) {
				
			$this->output["module_id"] = $module_id;
			}
			$DB->SetTable("engine_operations");
			//$DB->AddField("operation_name");
			//$DB->AddField("comment");
			$DB->AddField("table_name"); 
      $DB->AddField("name_field");
			$DB->AddCondFS("module_id", "=", $module_id);
			$res = $DB->Select();
			 //$this->output["elements"] = array();
			while ($row = $DB->FetchAssoc($res)) {
				$this->output["elements"][] = $row;
			
			}
				
				
		
	foreach($this->output["elements"] as $element){
if($element["table_name"]!=NULL){
		/*echo "<pre>222";
		print_r($this->output["elements"]);
		echo "</pre>";	*/
		
			
			
			
			$DB->SetTable($element["table_name"]);
			
			$DB->AddField("id");
      if(empty($element["name_field"]))
			$DB->AddField("name");
      else 
      $DB->AddExp($element["name_field"], "name");
      
      
      $DB->AddOrder("name");
			
			$res = $DB->Select();
			 //$this->output["elements"] = array();
			while ($row = $DB->FetchAssoc($res)) {
				$this->output["name_elements"][] = $row;
			
			}
			/*$result = count($this->output["name_elements"]);
			echo $result;*/
			/*echo "<pre>888";
		print_r($this->output["name_elements"]);
		echo "</pre>";*/
		} 
	}
	
	
	
		}
	
	function insert_privilege()
	{
		global $DB, $Engine, $Auth;
		
		if (isset ($_POST[$this->node_id]["give_privileges"]["all_entries"]) && $_POST[$this->node_id]["give_privileges"]["all_entries"] == "on")
			$entry_id = -1;
		else
			$entry_id = $_POST[$this->node_id]["give_privileges"]["entry_id"];
		
/*		$DB->SetTable("engine_privileges");
		$DB->AddValue("module_id", $_POST[$this->node_id]["give_privileges"]["module_id"]);
		$DB->AddValue("operation_name", $_POST[$this->node_id]["give_privileges"]["operation_name"]);
		
		if (isset ($_POST[$this->node_id]["give_privileges"]["all_entries"]) && $_POST[$this->node_id]["give_privileges"]["all_entries"] == "on")
			$DB->AddValue("entry_id", "-1");
		else
			$DB->AddValue("entry_id", $_POST[$this->node_id]["give_privileges"]["entry_id"]);
			
		$DB->AddValue("usergroup_id", $_POST[$this->node_id]["give_privileges"]["usergroup_id"]);
		$DB->AddValue("is_allowed", $_POST[$this->node_id]["give_privileges"]["is_allowed"]);*/
		
		//if ($DB->Insert())
		
		$do = $_POST[$this->node_id]["give_privileges"]["is_allowed"] ? "разрешение" : "запрет";
		
		$DB->SetTable("auth_usergroups");
		$DB->AddCondFS("id", "=", $_POST[$this->node_id]["give_privileges"]["usergroup_id"]);
		$res = $DB->Select();
		
		$row = $DB->FetchAssoc($res);
		
		if ($Engine->AddPrivilege($_POST[$this->node_id]["give_privileges"]["module_id"], $_POST[$this->node_id]["give_privileges"]["operation_name"], $entry_id, $_POST[$this->node_id]["give_privileges"]["usergroup_id"], $_POST[$this->node_id]["give_privileges"]["is_allowed"], $_POST[$this->node_id]["give_privileges"]["operation_name"].";".$_POST[$this->node_id]["give_privileges"]["usergroup_id"]))
			$this->output["messages"]["good"][] = "Операция прошла успешно";
		else
			$this->output["messages"]["bad"][] = "Ошибка";
		
		$_SESSION["messages"] = $this->output["messages"]; 
		CF::redirect($Engine->unqueried_uri);
	}
	
	function show_privileges($module_id = NULL, $usergroup_id = NULL)
	{
		global $DB;
		
		$DB->SetTable("engine_privileges");
		$DB->AddField("operation_name");
		$DB->AddField("entry_id");
        $DB->AddField("module_id");
		$DB->AddField("usergroup_id");
		$DB->AddField("is_allowed");
		if($module_id !== NULL) $DB->AddCondFS("module_id", "=", $module_id);
		if($usergroup_id !== NULL) $DB->AddCondFS("usergroup_id", "=", $usergroup_id);
		$res = $DB->Select();
		
		$i = 0;

		while ($row = $DB->FetchAssoc($res)) {
			$DB->SetTable("engine_operations");
			$DB->AddField("comment");
			$DB->AddField("table_name");
			$DB->AddField("name_field");
			$DB->AddCondFS("operation_name", "=", $row["operation_name"]);
			$DB->AddCondFS("module_id", "=", $row["module_id"]);
			$res_op = $DB->Select();
			$row_op = $DB->FetchAssoc($res_op);
			
			if ($row_op["comment"])
				$this->output["privileges"][$i]["operation"] = $row_op["comment"];
			else
				$this->output["privileges"][$i]["operation"] = $row["operation_name"];
				
			$this->output["privileges"][$i]["operation_code"] = $row["operation_name"];
			$this->output["privileges"][$i]["module_id"] = $row["module_id"];
			
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
			
			if(!empty($row_op["table_name"]) && $row["entry_id"] != -1) {
				$DB->SetTable($row_op["table_name"]);
				if(empty($row_op["name_field"]))
                $DB->AddField("name");
                else 
                $DB->AddExp($row_op["name_field"], "name");
				
				$DB->AddCondFS("id", "=", $row["entry_id"]);
				//echo $DB->SelectQuery();
				$res_entry = $DB->Select(1);
				while($row_entry = $DB->FetchAssoc($res_entry)) {
					if(!empty($row_entry["name"])) $this->output["privileges"][$i]["entry_name"] = $row_entry["name"];
				}
			}
			
			$i++;
		}
		
	}
	
	
	function delete_privilege ()
	{
		global $DB, $Engine;
		
		/*$DB->SetTable("engine_privileges");
		$DB->AddCondFS("operation_name", "=", $_GET["delete_name"]);
		$DB->AddCondFS("module_id", "=", $_GET["delete_module"]);
		$DB->AddCondFS("usergroup_id", "=", $_GET["delete_usergroup"]);
		$DB->AddCondFS("entry_id", "=", $_GET["delete_entry"]);
		$DB->Delete();*/
		
		$Engine->DeletePrivilege($_GET["delete_module"], $_GET["delete_name"], $_GET["delete_entry"], $_GET["delete_usergroup"], $_GET["delete_allowed"]);
		
		CF::Redirect($Engine->unqueried_uri);
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