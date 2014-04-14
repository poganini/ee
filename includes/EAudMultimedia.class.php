<?php

class EAudMultimedia
// version: 1.00.4
// date: 2014-01-20
{
  var $output;
	var $node_id;
	var $module_id;
	var $mode;
  
  function EAudMultimedia($global_params, $params, $module_id, $node_id, $module_uri)
  {
    global $Engine, $Auth, $DB,$ADMIN_USER;
		$this->node_id = $node_id;
		$this->module_id = $module_id;
    $this->module_uri = $module_uri;
    $this->output = array(); 
    $this->output["messages"] = array("good" => array(), "bad" => array());
    $this->output["module_id"] = $module_id; 
    
    $uri_parts = explode("/", $this->module_uri);
    $parts2 = explode(";", $params);
    //print_r($uri_parts); 
    
    if($uri_parts[0] == 'history') {
      $this->output["history"] = 1;
    }
    
    $DB->SetTable("nsau_auditorium", "a");
    $DB->AddTable("nsau_buildings", "b");
    //$DB->AddCondFS("a.building_id", "=", 14);
    $DB->AddCondFS("a.multimedia", "=", 1); 
    $DB->AddCondFF("a.building_id", "=", "b.id");
    $DB->AddFields(array("a.id", "a.name","b.label"));
    $DB->AddField("b.id", "bid");
    $DB->AddField("b.name", "bname");
    $DB->AddOrder('a.name');
    $res = $DB->Select();
    while($row = $DB->FetchAssoc($res)){
    	$this->output['buildings'][$row["bid"]]["name"] = $row["bname"];
	    $this->output['buildings'][$row["bid"]]['auditories'][] = $row;
    }
    
    $this->output["applications"] = array();
    
    if (ALLOW_AUTH && ($Engine->OperationAllowed($this->module_id, "multimedia.apply", -1, $Auth->usergroup_id) || $Engine->OperationAllowed($this->module_id, "multimedia.list", -1, $Auth->usergroup_id)  ))
    {
       
    switch ($parts2[0]) {
      case "ajax_delete_app":
      //$this->output = array();
      $DB->SetTable("nsau_auditorium_multimedia");
      $DB->AddCondFS("id", "=", $_POST['app_data']['id']);
      //echo $DB->DeleteQuery();
      $DB->Delete();
      
      $this->output["mode"] = "ajax_delete_app"; 
      echo 'Ok';
      break;
      default:
      if(isset($_POST['auditorium'], $_POST['num'], $_POST['date'])){
        $DB->SetTable("nsau_auditorium_multimedia");
        $DB->AddCondFS("auditorium_id", "=", $_POST['auditorium']);
        $DB->AddCondFS("date", "=", $_POST['date']);
        $DB->AddCondFS("num", "=", $_POST['num']);
        //echo $DB->SelectQuery();
        
        $res = $DB->Select(1);
        if($row = $DB->FetchAssoc($res)){
          //if($row["lecturer_id"] != $Auth->user_id)
          $this->output["messages"]["bad"][] = "Аудитория уже занята на это время";
          //else $this->output["messages"]["good"][] = "Заявка успешно добавлена";
        }
        else {
          $DB->SetTable("nsau_auditorium_multimedia"); 
          $DB->AddValue("auditorium_id", $_POST['auditorium']);
          $DB->AddValue("num", $_POST['num']);
          $DB->AddValue("date", $_POST['date']);
          $DB->AddValue("lecturer_id", $Auth->user_id);
          //echo $DB->InsertQuery();
          $res = $DB->Insert();
          if($res){
            $this->output["messages"]["good"][] = "Заявка успешно добавлена";
          } 
        }
      } 
      
      $show_all = $this->output["show_all"] = $Engine->OperationAllowed($this->module_id, "multimedia.list", -1, $Auth->usergroup_id);
      $DB->SetTable("nsau_auditorium_multimedia", "m"); 
        $DB->AddTable("nsau_auditorium", "a");
        $DB->AddFields(array("m.id", "m.date", "m.num", "m.auditorium_id", "a.name"));
        $DB->AddTable("nsau_buildings", "b");
        //$DB->AddCondFS("a.building_id", "=", 14);
        $DB->AddCondFS("a.multimedia", "=", 1);
        $DB->AddCondFF("a.building_id", "=", "b.id");
        $DB->AddFields(array("b.label"));
        $DB->AddTable("nsau_people", "p");
        $DB->AddCondFF("p.user_id", "=", "m.lecturer_id"); 
        $DB->AddFields(array("p.last_name", "p.patronymic"));
        $DB->AddField("p.name", "first_name");
        if(!$show_all)
        $DB->AddCondFS("m.lecturer_id", "=", $Auth->user_id);
        if(empty($this->output["history"]))
        $DB->AddCondFS("m.date", ">=", date("Y-m-d")); 
        $DB->AddCondFF("m.auditorium_id", "=", "a.id");
        $DB->AddOrder("m.date", true); 
        //echo $DB->SelectQuery(); 
        $res = $DB->Select(); 
        while($row = $DB->FetchAssoc($res)){
          $this->output["applications"][] = $row;
        }
        
      $this->output['mode'] = 'apply'; 
      $this->output["scripts_mode"] = 'apply';
      $this->output['plugins'][] = 'jquery.ui.datepicker.min'; 
      break; 
    }
    
    
    }
    else
		{
			$this->output["messages"]["bad"][] = "Доступ запрещён.";
		}
    //print_r($this->output); 
  }
  
  function Output()
    {
        return $this->output;
    }
}

?>