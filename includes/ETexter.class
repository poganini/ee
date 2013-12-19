<?php

class ETexter
// version: 2.3
// date: 2012-05-10
{
	var $module_id;
	var $node_id;
	var $privileges;
	var $output;

	var $mode;
	var $db_prefix;
	var $maintain_cache;
	var $item_id;


    /* В $global_params передаётся префикс БД 
			В $params передаётся либо id текста, либо search;id папки
    */
	function ETexter($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		global $DB, $Engine, $Auth;
		$this->module_id = $module_id;
		$this->node_id = $node_id;
		$this->additional = $additional;
		$this->output = array();		

		$parts = explode(";", $global_params);
		$this->db_prefix = $parts[0];
		$this->maintain_cache = !(isset($parts[1]) && !$parts[1]);
		$this->output["module_id"] = $module_id;
		//$this->output["mode_edit"] = null;

		/*if(isset($_POST['modul'])) {
			if($_POST['modul'] == "texter") {//echo $_POST['node']."  ".$_POST['action']."  ".$_POST['modul']."  ".$_POST['item_id'];
				$params = $_POST['item_id'];
			} else {
				$this->output["scripts_mode"] = $this->output["mode"] = "none";
				return;
			}
		}*/
		if(!CF::IsNaturalNumeric($params)) {
			
			$parts = explode(";", $params);
			if(CF::IsNaturalNumeric($parts[0])) {
				$this->output["mode_edit"] = $parts[1];
				$params = $parts[0];
			}
		}
		if (CF::IsNaturalNumeric($params))
		{
			$this->output["messages"] = array(
			"good" => array(),
			"bad" => array(),
			);
			$this->item_id = $params;			
			$this->output["privileges"] = $this->privileges = array(
				"item.view" => $Engine->OperationAllowed($this->module_id, "item.view", $this->item_id, $Auth->usergroup_id),//$Engine->ModuleOperationAllowed("item.view", $this->item_id),
				"item.alter" => $Engine->OperationAllowed($this->module_id, "item.alter", $this->item_id, $Auth->usergroup_id),//$Engine->ModuleOperationAllowed("item.alter", $this->item_id),
			);
			$this->output['plugins'][] = 'jquery.fancybox-1.3.4';
			$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = "normal";
			$this->output["item_id"] = $this->item_id;
			
			
			
			if ($this->privileges["item.view"])
			{
				if ($this->privileges["item.alter"])
				{
					$this->ManageHTTPdata();
				}

				$DB->Exec("
					SELECT `text`
					FROM `" . $this->db_prefix . "items`
					WHERE `id` = '" . $DB->Escape($this->item_id) . "'
					LIMIT 1
					", true);
				if (!list($this->output["text"]) = $DB->FetchRow())
				{
					$this->output["messages"]["bad"][] = 302; // текст не найден
				}
				
				$DB->FreeRes();
			}

			else
			{
				$this->output["text"] = false;
				$this->output["messages"]["bad"][] = 300; // нет доступа для операции
			}
		}

		else
		{
			$parts = explode(";", $params);
			$this->output["scripts_mode"] = $this->output["mode"] = $this->mode = $parts[0];

			switch ($this->mode)
			{
				case "search":
					$this->Search($parts[1], $additional);
					break;
			}
		}
		
		$this->output["scripts_mode"] = $this->output["mode"] = $this->mode;
	}


	function Search($init_folder_id, $input)
	{		
		global $DB, $Engine, $Auth;
		$output = array();

		$this->privileges = array(
			"module.search" => $Engine->OperationAllowed($this->module_id, "module.search", NULL, $Auth->usergroup_id),
			);		
		if ($this->privileges["module.search"])
		{
			$folders = $Engine->ListSubfolders($init_folder_id, true, true);			

			if ($folders)
			{
				// Встроенная MySQL функция поиска, но почему-то не работает
				//$match_string = "MATCH (`" . $this->db_prefix . "items`.`cache`) AGAINST ('" . $DB->Escape($input) . "')";

				$DB->SetTable(ENGINE_DB_PREFIX . "nodes");
				$DB->AddJoin($this->db_prefix . "items.id", "params"); // !!! пожалуй, нехорошо :)
				$DB->AddFields(array("folder_id", $this->db_prefix . "items.cache"));
				//$DB->AddExp($match_string, "relevance", NULL, false);
				$DB->AddCondFS("module_id", "=", $this->module_id);

				foreach ($folders as $elem)
				{
					$DB->AddAltFS("folder_id", "=", $elem);
				}

				$DB->AppendAlts();
				$DB->AddCondXP("`" . $this->db_prefix . "items`.`cache` LIKE '%" . str_replace(array("%", "_"), array("\\%","\\_"), $DB->Escape($input)) . "%'", false);
				//$DB->AddCondXP($match_string, false);
				//$DB->AddExpOrder($match_string, true, NULL, false);
				//$DB->AddCondFS("cache", "LIKE", "%".str_replace("%", "\\%", $DB->Escape($input))."%");				
				$res = $DB->Select();				
				

				while ($row = $DB->FetchObject($res))
				{
					if (!isset($output[$row->folder_id]))
					{						
						$result = $Engine->FolderDataByID($row->folder_id);						
						$output[] = array(
							"uri" => $result["uri"],
							"title" => $result["title"],
							"text" => $row->cache,
							//"relevance" => (float)$row->relevance,
							);
					}
				}

				$DB->FreeRes($res);
			}
		}

		else
		{
			$this->output["messages"]["bad"][] = 300; // нет доступа для операции
		}		
		
		$this->output["results"] = $output;
	}



	function ManageHTTPdata()
	{
		global $DB, $Engine;

		if (isset($_POST[$this->node_id]["save"]["cancel"]))
		{
			1;
		}

		elseif (isset($_POST[$this->node_id]["save"]["text"]))
		{
			$DB->SetTable($this->db_prefix . "items");
			$DB->AddValue("text", $_POST[$this->node_id]["save"]["text"]);

			if ($this->maintain_cache)
			{
				$DB->AddValue("cache", substr(CF::RefineText($_POST[$this->node_id]["save"]["text"]), 0, 65535)); // !!! сделать корректировку размера строки на уровне MySQLhandle
			}

			$DB->AddCondFS("id", "=", $this->item_id);

			if ($DB->Update(1))
			{
				$this->output["messages"]["good"][] = 101; // текст успешно сохранён
				$Engine->LogAction($this->module_id, "texter", $this->item_id, "edit");
				CF::Redirect();
			}

			else
			{
				$this->output["messages"]["bad"][] = 401; // ошибка БД при сохранении текста
			}
		}
	}



	function RenewCache($item_id = NULL, $text = NULL)
	{
		global $DB;

		if ($item_id && CF::IsNonEmptyStr($text))
		{
			$DB->SetTable($this->db_prefix . "items");
			$DB->AddValue("cache", substr(CF::RefineText($text), 0, 65535)); // !!! сделать корректировку размера строки на уровне MySQLhandle
			$DB->AddCondFS("id", "=", $item_id);

			if ($DB->Update(1))
			{
				$this->output["messages"]["good"][] = 102; // кэш успешно обновлён
			}

			else
			{
				$this->output["messages"]["bad"][] = 402; // ошибка БД при обновлении кэша
			}
		}

		else
		{
			$DB->SetTable($this->db_prefix . "items");
			$DB->AddFields(array("id", "text"));

			if ($item_id)
			{
				$DB->AddCondFS("id", "=", $item_id);
			}

			$res = $DB->Select($item_id ? 1 : NULL);

			while ($row = $DB->FetchObject($res))
			{
				$DB->SetTable($this->db_prefix . "items");
				$DB->AddValue("cache", substr(CF::RefineText($row->text), 0, 65535)); // !!! сделать корректировку размера строки на уровне MySQLhandle
				$DB->AddCondFS("id", "=", $row->id);

				if ($DB->Update(1))
				{
					$this->output["messages"]["good"][] = 102; // кэш успешно обновлён
				}

				else
				{
					$this->output["messages"]["bad"][] = 402; // ошибка БД при обновлении кэша
				}
			}

			$DB->FreeRes($res);
		}
	}



/*	function Add($text = NULL)
	{
		global $DB;
		$DB->SetDbt($this->db_prefix . "items");
		$DB->AddValue("text", $text);
		return $DB->Insert();
	}



	function DeleteRedundant()
	{
		global $DB;
		$DB->SetDbt($this->db_prefix . "items");
		$DB->AddValue("text", $text);
		return $DB->Delete();
	} */



	function Output()
	{
		return $this->output;
	}
}

?>