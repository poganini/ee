<?php
class ELogs
// version: 1.5
// date: 2011-07-28
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
    
    // paging params
    var $items_per_page = 50;
    var $page_limit = 50;
    var $items_num;

	function ELogs($global_params, $params, $module_id, $node_id, $module_uri)
	{
		global $Engine, $Auth, $DB;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		$this->output["messages"]["good"] = $this->output["messages"]["bad"] = array();
		
		$parts = explode(";", $params);
		$this->mode = $parts[0];
		
		$this->output["allow_handle"] = 1;
		
		switch($this->mode)
		{
			case "viewlogs":
			$this->output["mode"] = "viewlogs";
			
			
			$this->output["show_earlier"] = ((isset($_POST["earlier"]) && $_POST["earlier"]) ? 1 : 0); 
			 
			// программно обновл€ем $_GET дл€ pager-а 
			$_GET['page'] = $this->output["page"] = (isset($_POST['page']) ? $_POST['page'] : 0 );
				
			 
			$DB->SetTable("engine_actions_log");
			$DB->AddField("module_id");
			if (isset($_POST['earlier']) && $_POST['earlier']) {
				$DB->AddCondFS('time', '>', $this->GetAgoDate(date("Y-m-d"), 'month'));
			} else {
				$DB->AddCondFS('time', '>', $this->GetAgoDate(date("Y-m-d"), 'day'));
			}
			$res = $DB->Select(null, null, true, true ,true);
			while ($row = $DB->FetchAssoc($res))
				$module_ids[] = $row["module_id"];
			$filtered_modules_ids = array();
			if (!empty($_POST)) {
				if (isset($_POST["checkmod"]))
					foreach($_POST["checkmod"] as $key=>$value)
						$filtered_modules_ids[] = $key;
			} else $filtered_modules_ids = $module_ids;
			$DB->SetTable("engine_modules");
			foreach($module_ids as $module_id)
				$DB->AddAltFS("id", "=", $module_id);
			$res_modules = $DB->Select();
			while ($row = $DB->FetchAssoc($res_modules))
				$modules[$row["id"]] = array('comment' => $row["comment"], 'checked' => in_array($row["id"], $filtered_modules_ids));
			$modules[0] = array('comment' => "—истемные операции", 'checked' => in_array(0, $filtered_modules_ids));
			$this->output["modules"] = $modules;
			
						
			
			
			
			$DB->SetTable("auth_users");
			$res_users = $DB->Select();
			while ($row = $DB->FetchAssoc($res_users))
				$users[$row["id"]] = $row["displayed_name"];
			
			
			
			
			if (!empty($filtered_modules_ids))
			{ 
			
				$DB->SetTable("engine_actions_log");
			if (isset($_POST['earlier']) && $_POST['earlier']) {
				$DB->AddCondFS('time', '>', $this->GetAgoDate(date("Y-m-d"), 'month'));
			} else {
				$DB->AddCondFS('time', '>', $this->GetAgoDate(date("Y-m-d"), 'day'));
			}
			if (!empty($filtered_modules_ids))
				foreach($filtered_modules_ids as $module_id)
					$DB->AddAltFS("module_id", "=", $module_id);
			
			$DB->AddExp("COUNT(*)");
			$res  = $DB->Select();
			list($this->items_num) = $DB->FetchRow($res);
			
			
			$DB->SetTable("engine_actions_log");
			if (isset($_POST['earlier']) && $_POST['earlier']) {
				$DB->AddCondFS('time', '>', $this->GetAgoDate(date("Y-m-d"), 'month'));
			} else {
				$DB->AddCondFS('time', '>', $this->GetAgoDate(date("Y-m-d"), 'day'));
			}
			if (!empty($filtered_modules_ids))
				foreach($filtered_modules_ids as $module_id)
					$DB->AddAltFS("module_id", "=", $module_id);
			$DB->AddOrder("time", "desc");
			$from = ((isset($_POST['page']) && $_POST['page'] > 0) ? ($_POST['page']-1)*$this->items_per_page : 0);
			$res = $DB->Select($this->items_per_page, $from);
			
				$i=0;
				//$row = $DB->FetchAssoc($res);
				while ($row = $DB->FetchAssoc($res) /*&& $i < 300*/) {
					//if($i >= 300) return;
					$i++;
					if ($row["user_id"]) {
						if (isset($users[$row["user_id"]]))
							$row["username"] = $users[$row["user_id"]];
						else
							$row["username"] = "неизвестный пользователь";
						
						$DB->SetTable("auth_usergroups");
						$DB->AddCondFS("id", "=", $row["usergroup_id"]);
						$res_gr = $DB->Select();
						
						$row_gr = $DB->FetchAssoc($res_gr);
						
						$row["usergroup_name"] = htmlspecialchars($row_gr["comment"]);
					}
					else {
						$row["username"] = $row["ip"];
					}
						
					$row["module"] = $modules[$row["module_id"]];
					//$this->output["modules"][$row["module_id"]] = $row["module"];
					
					$year = substr($row["time"], 0, 4);
					$month = substr($row["time"], 5, 2);
					$day = substr($row["time"], 8, 2);
					$hours = substr($row["time"], 11, 2);
					$minutes = substr($row["time"], 14, 2);
					$seconds = substr($row["time"], 17, 2);
					
					$time = mktime($hours, $minutes, $seconds, $month, $day, $year);
					$now = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
					$day_begin = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")) - date("H")*3600 - date("i")*60 - date("s");
					$yesterday_begin = $day_begin - 24*3600;
					$week_ago = $now - 24*3600*7;
					$month_ago = $now - 24*3600*30;
					
					if (isset ($_GET["limit"]) && $_GET["limit"] == "week")
						$limit = $week_ago;
					else   //иначе за мес€ц
						$limit = $month_ago;
					
					if ($time > $day_begin)
						$this->output["logs"]["today"][] = $row;
					elseif ($time > $yesterday_begin)
						$this->output["logs"]["yesterday"][] = $row;
					elseif ($time > $limit)
						$this->output["logs"]["earlier"][] = $row;
						
					/*if (isset($_GET["earlier"]) && $_GET["earlier"])
						$this->output["show_earlier"] = 1;
					else
						$this->output["show_earlier"] = 0;*/
						
					require_once INCLUDES . "Pager.class";
					//$parts = explode("|", $per_page_input);
					$Pager = new Pager($this->items_num, $this->items_per_page, $this->page_limit); //$num, @$parts[0], @$parts[1], @$parts[2], @$parts[3]);
					$this->output["pager_output"] = $result = $Pager->Act();
				}
			}
			break;
		}	
	}
	
	
	function GetAgoDate($cur_date, $interval)
	{
		$data_parts = explode('-', $cur_date);
		switch ($interval) 
		{
			case 'month':
				$last_month = intval($data_parts[1])-1;
				if (!$last_month) {
					return (intval($data_parts[0])-1).'-12-'.$data_parts[2];
				} else return $data_parts[0].'-'.( $last_month < 10 ? '0'.$last_month : $last_month ).'-'.$data_parts[2];
				break;
			case 'day':
				$last_day = intval($data_parts[2])-1;
				if (!$last_day) {
					return $data_parts[0].'-'.(intval($data_parts[1])-1).'-01';
				} else return $data_parts[0].'-'.$data_parts[1].'-'.( $last_day < 10 ? '0'.$last_day : $last_day );
		}
		 
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