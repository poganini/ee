<?php
class EUserGroups
// version: 1.3.1
// date: 2012-04-04
{
    var $module_id;
    var $node_id;
    var $module_uri;
    var $privileges;
    var $output;

    var $mode;
    var $db_prefix;
    var $maintain_cache;
    var $item_id;

	function EUserGroups($global_params, $params, $module_id, $node_id, $module_uri)
	{
		global $Engine, $Auth, $DB;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
		
		$parts = explode(";", $params);
		$parts_uri = explode("/", $module_uri);
		
		if (($parts_uri[0] == "delete" || $parts_uri[0] == "edit" || $parts) && isset($parts_uri[1]))
			$this->mode = $parts_uri[0];
		else
			$this->mode = $parts[0];
		
		if ($Engine->OperationAllowed(0, "engine.userGroups.handle", 0, $Auth->usergroup_id)) {
			$this->output["allow_handle"] = 1;
			
			switch($this->mode)
			{
				case "edit":
				{
					if(!isset($_POST["group_comment"]))
					{
						$DB->SetTable("auth_usergroups");
						$DB->AddCondFS("id", "=", $parts_uri[1]);
						$res = $DB->Select();
						if($row = $DB->FetchAssoc($res)) {
							$this->output["edit_group"] = $row;
							$this->output["mode"] = "edit_group";
						}
					}
					else
					{
						$DB->SetTable("auth_usergroups");
						$DB->AddCondFS("id", "=", $parts_uri[1]);
						//$DB->AddValue("name", $_POST["group_name"]);
						$DB->AddValue("comment", $_POST["group_comment"]);
						$DB->Update();
						$Engine->LogAction($this->module_id, "usergroup", $parts_uri[1], "alter");
						CF::Redirect($Engine->engine_uri);
					}
					break;
				}
				
				case "delete":
				{
					$DB->SetTable("auth_users");
					$DB->AddCondFS("usergroup_id", "=", $parts_uri[1]);
					$res = $DB->Select();
					
					if ($DB->FetchAssoc($res)) {
						CF::Redirect($Engine->engine_uri."?isusers=1");
					}
					else {				
						$DB->SetTable("auth_usergroups");
						$DB->AddCondFS("id", "=", $parts_uri[1]);
						$DB->Delete();
						$Engine->LogAction($this->module_id, "usergroup", $parts_uri[1], "delete");
						CF::Redirect($Engine->engine_uri);
					}
					break;
				}
				
				case "userslist":
				{
					if (isset($parts_uri[2]) && $parts_uri[2] == "edit" && isset($parts_uri[3])) {
						if(!isset($_POST["displayed_name"]))
						{
							$DB->SetTable("auth_users");
							$DB->AddCondFS("id", "=", $parts_uri[3]);
							$res = $DB->Select();
							if($row = $DB->FetchAssoc($res)) {
								$this->output["edit_user"] = $row;
								$this->output["mode"] = "edit_user";
							}
							
							$DB->SetTable("auth_usergroups");
							$DB->AddCondFS("id", "=", $parts_uri[1]);
							$res = $DB->Select();
							$row = $DB->FetchAssoc($res);
							$this->output["group"] = $row;
							
							$DB->SetTable("auth_usergroups");
							$res = $DB->Select();
							while ($row = $DB->FetchAssoc($res)) {
								$this->output["groups"][] = $row;
							}
						}
						elseif($_POST["pass"] != $_POST["repass"]) {
							$DB->SetTable("auth_users");
							$DB->AddCondFS("id", "=", $parts_uri[3]);
							$res = $DB->Select();
							if($row = $DB->FetchAssoc($res)) {
								$this->output["edit_user"] = $row;
								$this->output["mode"] = "edit_user";
							}
							$this->output["mode"] = "edit_user";
							$this->output["messages"]["bad"][] = "Пароль подтверждён неверно";
						}
						else
						{
							$DB->SetTable("auth_users");
							$DB->AddCondFS("email", "=", $_POST["email"]);
							$res = $DB->Select();
							
							if ($DB->FetchAssoc($res)) {
								$DB->SetTable("auth_users");
								$DB->AddCondFS("id", "=", $parts_uri[3]);
								$res = $DB->Select();
								if($row = $DB->FetchAssoc($res)) {
									$this->output["edit_user"] = $row;
									$this->output["mode"] = "edit_user";
								}
								$this->output["mode"] = "edit_user";
								$this->output["messages"]["bad"][] = "Пароль подтверждён неверно";
							}
						
							$DB->SetTable("auth_users");
							$DB->AddCondFS("id", "=", $parts_uri[3]);
							$DB->AddValue("displayed_name", $_POST["displayed_name"]);
							$DB->AddValue("username", $_POST["username"]);
							$DB->AddValue("usergroup_id", $_POST["new_usergroup_id"]);
							
							if (!isset($_POST["is_active"]))
								$DB->AddValue("is_active", 0);
							elseif ($_POST["is_active"] == "on")
								$DB->AddValue("is_active", 1);
							
							if (isset($_POST["pass"]) && $_POST["pass"] != "")
								$DB->AddValue("password", md5($_POST["pass"]));
							
							$DB->AddValue("email", $_POST["email"]);
							$DB->Update();
							$Engine->LogAction($this->module_id, "user", $parts_uri[3], "alter");

							CF::Redirect($Engine->engine_uri."userslist/".$parts_uri[1]."/");
						}
					}
					elseif (isset($parts_uri[2]) && $parts_uri[2] == "delete" && isset($parts_uri[3])) {
						$DB->SetTable("auth_users");
						$DB->AddCondFS("id", "=", $parts_uri[3]);
						$DB->Delete();
						$Engine->LogAction($this->module_id, "user", $parts_uri[3], "delete");
						CF::Redirect($Engine->engine_uri."userslist/".$parts_uri[1]."/");
					}
					elseif (!isset($_POST["displayed_name"])) {
						$this->output["displayed_name"] = "";
						$this->output["email"] = "";
						$this->output["username"] = "";
						
						$DB->SetTable("auth_usergroups");
						$DB->AddCondFS("id", "=", $parts_uri[1]);
						$res = $DB->Select();
						$this->output["group"] = $DB->FetchAssoc($res);
						
						$DB->SetTable("auth_users");
						$DB->AddCondFS("usergroup_id", "=", $parts_uri[1]);
						$res = $DB->Select();
						
						while ($row = $DB->FetchAssoc($res)) {
							$this->output["users"][] = $row;
						}
						
						$this->output["mode"] = "userslist";
					}
					else {
							$DB->SetTable("auth_usergroups");
							$DB->AddCondFS("id", "=", $parts_uri[1]);
							$res = $DB->Select();
							$this->output["group"] = $DB->FetchAssoc($res);
							
							$DB->SetTable("auth_users");
							$DB->AddCondFS("usergroup_id", "=", $parts_uri[1]);
							$res = $DB->Select();
							
							while ($row = $DB->FetchAssoc($res)) {
								$this->output["users"][] = $row;
							}
						if ($_POST["pass"] != $_POST["repass"])
						{
													
							$this->output["mode"] = "userslist";
							$this->output["messages"]["bad"][] = "Пароль подтверждён неверно";
							$this->output["displayed_name"] = $_POST["displayed_name"];
							$this->output["email"] = $_POST["email"];
							$this->output["username"] = $_POST["username"];
						}
						else {
							$DB->SetTable("auth_users");
							$DB->AddCondFS("username", "=", $_POST["username"]);
							$res = $DB->Select(1);
							if ($row = $DB->FetchRow($res)) {
							
								$this->output["mode"] = "userslist";
								$this->output["messages"]["bad"][] = "Логин уже используется другим пользователем";
								$this->output["displayed_name"] = $_POST["displayed_name"];
								$this->output["email"] = $_POST["email"];
							}
							else {
								$DB->SetTable("auth_users");
								$DB->AddValue("username", $_POST["username"]);
								$DB->AddValue("displayed_name", $_POST["displayed_name"]);
								$DB->AddValue("password", md5($_POST["pass"]));
								$DB->AddValue("email", $_POST["email"]);
								$DB->AddValue("usergroup_id", $_POST["usergroup_id"]);
								$DB->AddValue("is_active", 1);
								$DB->AddValue("create_user_id", $_SESSION["user_id"]);
								$DB->AddValue("create_time", date("Y-m-d H:i:s"));
								$DB->Insert();
								$Engine->LogAction($this->module_id, "user", $DB->LastInsertID(), "create");
								
								CF::Redirect($Engine->engine_uri."userslist/".$_POST["usergroup_id"]."/");
							}
						}
					}
					break;
				}
				
				case "all_groups":
				{
					if(!isset($_POST["group_comment"]))
					{
						$DB->SetTable("auth_usergroups");
						$res = $DB->Select();
						while($row = $DB->FetchAssoc($res))
						{
							$DB->SetTable("auth_users");
							$DB->AddCondFS("usergroup_id", "=", $row["id"]);
							$res_users = $DB->Select();
							$numrows = $DB->NumRows($res_users);
							$row["num_users"] = $numrows;
							
							$this->output["groups"][] = $row;
						}
						$this->output["mode"] = "all_groups";
						
						if (isset($_GET["isusers"])) {
							$this->output["messages"]["bad"][] = "Невозможно удалить группу, в которой есть пользователи.";
						}
						
						if (isset($_GET["rusname"])) {
							$this->output["messages"]["bad"][] = "Указанное русскоязычное название группы уже использовано";
						}
					}
					else {
						$DB->SetTable("auth_usergroups");
						$DB->AddCondFS("comment", "=", $_POST["group_comment"]);
						$res = $DB->Select();
												
						if ($DB->FetchAssoc($res))
							CF::Redirect($Engine->engine_uri."?rusname=1");
						else {
							$DB->SetTable("auth_usergroups");
							//$DB->AddValue("name", $_POST["group_name"]);
							$DB->AddValue("comment", $_POST["group_comment"]);
							$DB->AddValue("create_time", date("Y-m-d H:i:s"));
							$DB->AddValue("session_lifetime", 0);
							$DB->Insert();
							$Engine->LogAction($this->module_id, "usergroup", $DB->LastInsertID(), "create");
							
							CF::Redirect($Engine->engine_uri);
						}
					}
					break;
				}
			}
		}
		else
			$this->output["allow_handle"] = 0;
		
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