<?php

class ESearch
// version: 2.5
// date: 2012-06-04
{
	var $output;
	var $node_id;
	//var $module_uri;
	//var $db_prefix;
	var $mode;

	var $min_input_length;
	var $max_input_length;
	var $strip_tags;
	var $substitute;
	var $max_output_length;
	var $attach;

	var $status;
	//var $display_variant;
	var $form_data;
	var $search_modules_params;

    /* В $params передаём режим работы модуля либо "form" либо "results", в случае "form" передаём через точку с запятой id папки результатов и id ноды
    	а в $global_params передаём имя ini-файла, например, "ESearch.ini"
     */    
	function ESearch($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		global $Engine, $DB, $Auth;
        		
		$this->output = array();
		$this->output["messages"] = array("good" => array(), "bad" => array());
		$this->node_id = $node_id;


		$this->output["display_access"] = $this->display_access = 1;
		$this->output["full_access"] = $this->output["manage_access"] = $this->manage_access = $this->full_access = $Auth->logged_in;
		$parts = explode(";", $params);
		$this->output["mode"] = $this->mode = $parts[0];


		switch ($this->mode)
		{
			case "form":
				if (isset($parts[1]) && CF::IsNaturalNumeric($parts[1]))
				{
					$this->output["results_folder"] = $Engine->FolderURIbyID($parts[1]);
				}

				else
				{
					die("ESearch module error #1001 at node $this->node_id: illegal RESULTS FOLDER ID.");
				}


				/*if (isset($parts[2]) && CF::IsNaturalNumeric($parts[2]))
				{
					$this->output["results_node_id"] = intval($parts[2]);
				}

				else
				{
					die("ESearch module error #1002 at node $this->node_id: illegal RESULTS NODE ID.");
				}*/
				break;


			case "results":
				$this->search_modules_params = isset($parts[1]) ? $parts[1] : false;
				$this->ProcessINI($global_params);
				$this->Results();
				break;


			default:
				die("ESearch module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
				break;
		}


		$this->output["status"] = $this->status;
		//$this->output["display_variant"] = $this->display_variant;
		$this->output["form_data"] = $this->form_data;
	}



	function ProcessINI($config_file)
	{
		if (!$config_file)
		{
			die("ESearch module error #1001: config file not specified.");
		}

		elseif (!$result = @parse_ini_file(INCLUDES . $config_file, true))
		{
			die("ESearch module error #1002: cannot process config file '$config_file'.");
		}


		$this->output["min_input_length"] = $this->min_input_length = (int) $result["general_settings"]["min_input_length"];
		$this->output["max_input_length"] = $this->max_input_length = (int) $result["general_settings"]["max_input_length"];
//		$this->strip_tags = (bool) $result["general_settings"]["strip_tags"];
//		$this->substitute = (bool) $result["general_settings"]["substitute"];
		$this->output["max_output_length"] = $this->max_output_length = (int) $result["general_settings"]["max_output_length"];
		$this->output["method"] = $this->method = $result["general_settings"]["method"];
		$this->attach = array();
		if ($this->search_modules_params) 
		{ 
			$this->attach = explode(',', $this->search_modules_params);
			foreach ($this->attach as $ind=>$value)
				$this->attach[$ind] = str_replace('-', ';', $value);
		}
		else
		foreach ($result["attach"] as $key => $elem)
		{
			$this->attach[$key] = $elem;
		}
	}


	function Highlight($search_string, $results)
	{
		$add_strings_len = 200;
		foreach($results as $ind=>$result)
		{	
			$text = $result['text'];
			$occur_results = array();
			while ($pos = stripos($text, $search_string)) {
				if ($pos - $add_strings_len < 0)
				{
					$before_string = substr($text, 0, $pos);
				}
				else
				{
					$before_string = '...'.substr($text, $pos-$add_strings_len, $add_strings_len);
				}
				$after_string = substr($text, $pos + strlen($search_string), $add_strings_len);
				$occur_results[] = $before_string.'<strong>'.substr($text, $pos, strlen($search_string)).'</strong>'.$after_string.'...';
				$text = substr($text, $pos + strlen($search_string) + 1);
				
			}
			
			$results[$ind]['text'] = $occur_results;
		}
		return $results;
	}

	function Results()
	{
		global $DB, $Auth;
		
		$this->status = true;
		$this->output["results"] = array();

		$utfString = iconv("utf-8", "cp1251", $_GET["search"]);
		$_GET["search"] = $utfString ? $utfString : urldecode(iconv("utf-8", "cp1251", urlencode($_GET["search"])));
		
		if (isset($_POST["search"]) || $_POST["search"]) {
				$search_string = $_POST["search"];
		}
		else {
			if (isset($_GET["search"]) || $_GET["search"])
				$search_string = $_GET["search"];
		}
		
		if (isset($search_string))
		{		
			$search_string = trim($search_string);
			$search_string_length = strlen($search_string);
			$this->output["search_string"] = $search_string = htmlspecialchars($search_string); // !!! htmlspecialchars применяю пока и к самой строке - подумать!

			if ($search_string_length < $this->min_input_length)
			{
				$this->output["messages"]["bad"][] = 302; // поисковая строка меньше минимально допустимой
				$this->status = false;
			}
			
			if ($search_string_length > $this->max_input_length)
			{
				$this->output["messages"]["bad"][] = 303; // поисковая строка больше максимально допустимой
				$this->status = false;
			}
			else
			{
				$this->output["count"] = 0;
				$this->output["results"] = array();				
				//CF::Debug($this->output["results"]);
				foreach ($this->attach as $key => $data)
				{ 
					if (preg_match("/^\d+(:.+)?$/i", $data))
					{		
						$parts = explode(":", $data);	
						 									
							$res2 = $DB->Exec("SELECT `name`, `global_params`
								FROM `" . ENGINE_DB_PREFIX . "modules`
								WHERE `id` = '" . $parts[0] . "'
								LIMIT 1");
	
							if ($row2 = $DB->FetchObject($res2))
							{
								require_once INCLUDES . $row2->name . ".class";
								//echo "INCLUDES " . $row2->name . ".class";
								//echo "<p>global_params:".$row2->global_params."</p>";
								//echo "<p>params:".$parts[1]."</p>";
								$Module = new $row2->name(
									$row2->global_params,
									$parts[1],
									$parts[0],								
									NULL,
									NULL,
									$search_string
									);							
								$result = $Module->Output();							
								$this->output["results"][$key] = array(
									"count" => count($result["results"]),
									"results" => $this->Highlight($search_string, $result["results"]),
									);
	
								$this->output["count"] += $this->output["results"][$key]["count"];
							}
	
							else
							{
								die("ESearch module error #1003: unknown module id ({$parts[0]}) in ATTACH string for attached item # $key.");
							}
	
							$DB->FreeRes($res2);
						
							
					}

					else
					{
						die("ESearch module error #1004: illegal ATTACH format for attached item # $key (expecting format: module_id:param1;param2;param3...).");
					}
				}
			}
		}

		else
		{
			$this->output["messages"]["bad"][] = 301; // не задана поисковая строка
		}
	}

	function Output()
	{
		return $this->output;
	}
}

?>