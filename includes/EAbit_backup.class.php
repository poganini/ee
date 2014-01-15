<?php
class EAbit
// version: 1.4
// date: 2011-08-02
{
    var $module_id;
    var $node_id;
    var $module_uri;
    var $privileges;
    var $output;

    var $mode;
    var $db_prefix;
    var $maintain_cache;
    var $status_id;
    
    static $basic_mode_specs_codes = array(62, 65, 68);
    static $spo_mode_specs_codes = array(51);
    
    /*
    В params передаём: 	1-й параметр: режим работы ( 'list' - выдача списка абитуриентов, 
    												'request' - выдача информации о количестве поданных заявлений, 
    												'edit_spec' - редактирование данных о количестве мест на специальностях);
     					2-й параметр: в режиме 'list' - id статуса Абитуриент
    */
    function EAbit($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
    {
        global $DB, $Engine, $Auth;
        $this->module_id = $module_id;
        $this->node_id = $node_id;
        $this->module_uri = $module_uri;
        $this->additional = $additional;
        
        $parts = explode(";", $global_params);
        $this->db_prefix = $parts[0];
        
        $this->output = array();        
        $this->output["messages"] = array("good" => array(), "bad" => array());
        
        $parts = explode(";", $params);
		$this->mode = $parts[0];
		
        switch ($this->mode)
			{
				case "abitfile":
					$this->output["abitfile"] = 1;
					if (isset($_FILES['abit'])) {
						$fname = explode(".", $_FILES['abit']['name']);
						if ($fname[count($fname)-1] != "csv")
							$this->output["messages"]["bad"][] = "Необходим файл в формате csv";
						else {
							$f = fopen($_FILES['abit']['tmp_name'], "r");
							$content = fread($f, filesize($_FILES['abit']['tmp_name']));
							$parts = explode("\r\n", $content);
							array_shift($parts);
							$DB->Exec("truncate table abit_temp");
							
							$toobad = 0;
							
								
								foreach ($parts as $part) {
									if ($part) {
										$string = explode(";", $part);
										if (count($string) > 1 && count($string) != 15 && !$toobad) {
											$this->output["messages"]["bad"][] = "Неверное количество полей: ".count($string)." вместо 15";
											$toobad = 1;
										}
										elseif (count($string) > 1 && !$toobad) {
											$DB->SetTable("abit_temp");
											$DB->AddValue("fp", $string[0]);
											$DB->AddValue("f1", $string[1]);
											$DB->AddValue("f2", $string[2]);
											$DB->AddValue("f3", $string[3]);
											$DB->AddValue("f4", $string[4]);
											$DB->AddValue("fz", $string[5]);
											$DB->AddValue("fdog", $string[6]);
											$DB->AddValue("fn", $string[7]);
											$DB->AddValue("fd", $string[8]);
											$DB->AddValue("f5", $string[9]);
											$DB->AddValue("f6", $string[10]);
											$DB->AddValue("fs1", $string[11]);
											$DB->AddValue("fs2", $string[12]);
											
											if ($string[13] == "ЛОЖЬ")
												$DB->AddValue("fout", "0");
											else
												$DB->AddValue("fout", "1");
												
											if ($string[14] == "ЛОЖЬ")
												$DB->AddValue("ftook", "0");
											else
												$DB->AddValue("ftook", "1");
											
											if (!$DB->Insert())
												$this->output["messages"]["bad"][] = "Не удалось добавить абитуриента ".$string[3];
										}
									}
								}
							
							if (!$toobad) {
								if (isset($_POST["spo"]) && $_POST["spo"]=="on")
									$sql = "CALL ab22_spo();";
									
								else
									$sql = "CALL ab22();";
							
								if ($DB->Exec($sql))
									$this->output["messages"]["good"][] = "Абитуриенты загружены";
									
								else
									$this->output["messages"]["bad"][] = "Процедура вернула ошибку: ".$DB->Error();
									//die($DB->Error());
							}
						}
					}
				break;
			
				case "list":
					$this->status_id = $parts[1];
        			$this->People();
					break;
                    
				case "request":
					$this->GetRequestsInfo(isset($parts[2])?$parts[2]:'all');
					break;
				
				case "edit_spec":
					if (isset($_POST['data_str']) && $Engine->ModuleOperationAllowed("places_info.handle", 0)) {
						$this->UpdateSpecsInfo($_POST['data_str']);
					}
					$this->GetSpecsInfo();
					break;
					
				case "daystogo":
					$this->DaysToGo();
					break;
				
				default:
					die("EMenu module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
					break;
			}
        
	}
	
	function People()
	{
		global $DB;
		$this->TypesList();
		$DB->SetTable($this->db_prefix."people", "p");
		$DB->AddTable($this->db_prefix."spec_abit", "a");		
		$DB->AddTable($this->db_prefix."specialities", "s");		
		$DB->AddField("a.speciality_id");
		$DB->AddField("a.qualification");
		$DB->AddField("a.type");
		$DB->AddField("a.type_id");
		$DB->AddField("a.dogovor");
		$DB->AddField("a.documents");
		$DB->AddField("a.notice");
		$DB->AddField("a.position");
		$DB->AddField("a.ege");
		$DB->AddField("a.acceptance");
		$DB->AddField("s.name", "s_name");
		$DB->AddField("p.id");
		$DB->AddField("p.last_name");
		$DB->AddField("p.name");
		$DB->AddField("p.patronymic");
		$DB->AddCondFF("p.id","=","a.people_id");
		$DB->AddCondFF("s.code","=","a.speciality_id");
		$DB->AddCondFS("p.status_id","=",$this->status_id);
		$DB->AddCondFS("a.out","=","0");
		$DB->AddCondFS("a.took","=","0");
		
		$DB->AddOrder("a.type");
		
		if(isset($_GET["type"]))
		{
			$DB->AddTable($this->db_prefix."abit_types", "t");
			$DB->AddField("t.descr");
			$DB->AddCondFF("t.position","=","a.position");
			$DB->AddCondFS("a.type_id","=",$_GET["type"]);
			$DB->AddOrder("a.position");
  		}
		
		//$DB->AddOrder("a.notice", 1);
		$DB->AddOrder("a.status1");
		$DB->AddOrder("a.dogovor", 1);
		$DB->AddOrder("a.acceptance");
		$DB->AddOrder("a.ege", 1);
		$DB->AddOrder("a.status2", 1);
		$DB->AddOrder("p.last_name");
        
        $res = $DB->Select();
        
        while($row = $DB->FetchAssoc($res))
        {
            $this->output["people"][] = $row;    
        }
        
        if(isset($_GET["type"]))
		{
			$DB->SetTable($this->db_prefix."types");
			$DB->AddCondFS("id","=",$_GET["type"]);
			$res = $DB->Select(1);
			if($row = $DB->FetchAssoc($res))
			{
        		$this->output["type"] = $row;
        	}        		
  		}
  		$this->output["date"] = $this->getUpdateDate(true);
	}
	
	function TypesList()
	{
		global $DB;
		$DB->SetTable($this->db_prefix."types");
		$DB->AddOrder("code");
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res))
		{
        	$this->output["types"][] = $row;
        }		
	}
	
	
	function GetSpecsInfo() {
		global $DB, $Engine, $Auth;
		
		$DB->SetTable($this->db_prefix."types");
		$DB->AddField($this->db_prefix  ."types.id");
		$DB->AddField($this->db_prefix  ."types.code");
		$DB->AddField($this->db_prefix  ."types.type");
		$DB->AddField($this->db_prefix  ."types.budget");
		$DB->AddField($this->db_prefix  ."types.commerce");
		$DB->AddField($this->db_prefix  ."types.purpose");
		$DB->AddJoin( $this->db_prefix  . "faculties.id", "faculty_id");
		$DB->AddField( $this->db_prefix  . "faculties.name", "faculty_name");
		$DB->AddOrder($this->db_prefix  . "faculties.pos");
		
		$res = $DB->Select();
		
		$rows = array();
		$sum = array();
		while($row = $DB->FetchObject())
		{	
			if (!isset($rows[$row->faculty_name])) 
				$rows[$row->faculty_name] = array("specs"=>array(), "budget"=>0, "commerce"=>0, "purpose"=>0);
			$rows[$row->faculty_name]["specs"][] = $row;
        	$rows[$row->faculty_name]["budget"] += $row->budget;
        	$rows[$row->faculty_name]["commerce"] += $row->commerce;
       	  	$rows[$row->faculty_name]["purpose"] += $row->purpose;
       	  	$sum["budget"] += $row->budget;
        	$sum["commerce"] += $row->commerce;
        	$sum["purpose"] += $row->purpose;
		}
		$rows["sum"] = $sum;
		$this->output["edit_info"] = $rows;
		$this->output["uri"] = $Engine->FolderURIById();
		$this->output["auth"] = $Engine->OperationAllowed(1, "module.items.handle", 0, $Auth->usergroup_id);
		$DB->FreeRes();
	}
	
	
	function GetRequestsInfo($request_mode)
	{	
		global $DB, $Engine;
		
		$DB->SetTable($this->db_prefix."types");
		$DB->AddField($this->db_prefix  ."types.id");
		$DB->AddField($this->db_prefix  ."types.code");
		$DB->AddField($this->db_prefix  ."types.type");
		$DB->AddField($this->db_prefix  ."types.budget");
		$DB->AddField($this->db_prefix  ."types.commerce");
		$DB->AddJoin( $this->db_prefix  . "faculties.id", "faculty_id");
		$DB->AddField( $this->db_prefix  . "faculties.name", "faculty_name");
		
		switch($request_mode){
			case 'basic':
				foreach (self::$basic_mode_specs_codes as $spec_code)
					$DB->AddAltFS("code", " LIKE ", "%".$spec_code);
				break;
			case 'spo':
				foreach (self::$spo_mode_specs_codes as $spec_code)
					$DB->AddAltFS("code", " LIKE ", "%".$spec_code);
				break;
			
			default:
				$request_mode = 'all';
				
		}
		
		$DB->AddOrder($this->db_prefix  . "faculties.pos");
		$DB->AddOrder($this->db_prefix  . "types.code");
		
		$res = $DB->Select();
		
		$rows = array();
		$codes = array();
		while($row = $DB->FetchObject())
		{
			$rows[] = $row;
			$codes[] = $row->code;
			$row->req_count = 0;
		}
		$DB->FreeRes();
				
		$DB->SetTable($this->db_prefix."spec_abit");
		$DB->AddExp("count(*)", "req_count");
		$DB->AddJoin( $this->db_prefix  . "types.id", "type_id");
		$DB->AddField($this->db_prefix  ."types.code");
		$DB->AddJoin( $this->db_prefix  . "faculties.id", $this->db_prefix  ."types.faculty_id");
		$DB->AddGrouping( "type_id");
		
		$res = $DB->Select();
		
		while($row = $DB->FetchObject())
		{
			foreach ($codes as $ind=>$code)
				if ($row->code == $code)
					$rows[$ind]->req_count = $row->req_count;
		}
		$DB->FreeRes();
		
		foreach ($rows as $row)
		{			
			$row->contest = round($row->req_count/($row->budget+$row->commerce),2);
        	$this->output["requests_info"][$row->faculty_name]["specs"][] = $row;
        	$this->output["requests_info"][$row->faculty_name]["budget"] += $row->budget;
        	$this->output["requests_info"][$row->faculty_name]["commerce"] += $row->commerce;
        	$sum["budget"] += $row->budget;
        	$sum["commerce"] += $row->commerce;
        	$this->output["requests_info"][$row->faculty_name]["req_count"] += $row->req_count;
        	$sum["req_count"] +=  $row->req_count;
        	$this->output["requests_info"][$row->faculty_name]["contest"] = round($this->output["requests_info"][$row->faculty_name]["req_count"]/($this->output["requests_info"][$row->faculty_name]["budget"]+$this->output["requests_info"][$row->faculty_name]["commerce"]), 2);  
        }
        	$sum["contest"] = round($sum["req_count"]/($sum["budget"]+$sum["commerce"]), 2);
        	$this->output["requests_info"]["sum"] = $sum;
        
        $DB->FreeRes();
		
		$this->output["abit_list_base_href"] = preg_replace('/request\S+/', 'list/', $Engine->engine_uri)."?type=";	
		$this->output["mode"] = $request_mode;
		$this->output["date"] = $this->getUpdateDate();
	}
	
	
	function getUpdateDate($with_time = false) {
		global $DB;
		$DB->SetTable("engine_dbupdates_log");
		$DB->AddField("update_time");
		$DB->AddCondFS("name", "=", $this->db_prefix."spec_abit");
		$res = $DB->Select();
		$row = $DB->FetchObject();
		$updateInfo = explode(' ', $row->update_time);
		
		$updateDate = ($with_time ? implode(':', array_splice(explode(':', $updateInfo[1]), 0, 2)) .' '. implode('.', array_reverse(explode('-', $updateInfo[0]))) : implode('.', array_reverse(explode('-', $updateInfo[0]))));
		$DB->FreeRes();
		return $updateDate;
	}
	
	function UpdateSpecsInfo($post_data) { 
		global $DB;
		$data_parts = explode(';', $post_data);
		array_pop($data_parts);
		foreach($data_parts as $data_part) {
			list($key, $value) = explode('=', $data_part);
			if (preg_match('/_/', $key)) { 
				$key_parts = preg_split('/_/', $key);
				$field = $key_parts[0];
				$id = $key_parts[1]; 
				$DB->SetTable($this->db_prefix."types");
				$DB->AddCondFS("id", "=", $id);
				$DB->AddValue($field, $value);
				$res = $DB->Update();
				$DB->FreeRes();
			}
		}
		
	}
	
	function DaysToGo() {
		
	/*	if (25-(int)date("d") < 1 || (int)date("m")!=7)
			return false;
		else
			$daystogo = 25-(int)date("d");
			
		$ost = $daystogo%10;
		
		if ($daystogo>=5 && $daystogo<=20)
			$word = "дней";
		elseif ($ost==1)
			$word = "день";
		elseif ($ost==2 || $ost==3 || $ost==4)
			$word = "дня";
		else
			$word = "дней";*/
			
		$changetime = mktime(17,0,0,7,25,2011);
		$curtime = time();
		
		if ($curtime < $changetime)
			$this->output["daystogo"] = "Документы принимаются сегодня до 17:00";
		else
			$this->output["daystogo"] = "Оригиналы документов принимаются до 4 августа, 13:00";

		//$this->output["word"] = $word;
	}
	
    function Output()
    {
    	return $this->output;
    }
	
}

?>