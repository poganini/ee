<?php
class ELogs
// version: 1.2
// date: 2010-03-01
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
			$DB->SetTable("engine_actions_log");
			$DB->AddOrder("time", "desc");
			$res = $DB->Select();
			
			$DB->SetTable("auth_users");
			$res_users = $DB->Select();
			while ($row = $DB->FetchAssoc($res_users))
				$users[$row["id"]] = $row["displayed_name"];
				
			$DB->SetTable("engine_modules");
			$res_modules = $DB->Select();
			while ($row = $DB->FetchAssoc($res_modules))
				$modules[$row["id"]] = $row["comment"];
			$modules[0] = "не указан";
				
			while ($row = $DB->FetchAssoc($res)) {
				if ($row["user_id"])
					$row["username"] = $users[$row["user_id"]];
				else
					$row["username"] = $_SERVER["REMOTE_ADDR"];
				$row["module"] = $modules[$row["module_id"]];
				
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
			}
			break;
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