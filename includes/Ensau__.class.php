<?php
class Ensau
// version: 3.2
// date: 2011-10-21
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
	var $Imager;
    
    /*
    В парамс передаём режим работы faculties, 
	people, people, search_group, groups либо timetable и иже с ним =)
    */
    function Ensau($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
    {
        global $DB, $Engine, $Auth;
		
        $this->module_id = $module_id;
        $this->node_id = $node_id;
        $this->module_uri = $module_uri;
        $this->additional = $additional;
        $this->output = array();        
        $this->output["messages"] = array("good" => array(), "bad" => array());
        
        $parts = explode(";", $global_params);
        $this->db_prefix = $parts[0];
        $parts2 = explode(";", $params);
        
        $mode = explode("/", $module_uri);
		
		$array = @parse_ini_file(INCLUDES . "ENsau.ini", true);
		$this->settings = @parse_ini_file('ENsau_mail.ini', true);
		
		$this->images_dir = (isset($array["general_settings"]["images_dir"])) && CF::IsNonEmptyStr($array["general_settings"]["images_dir"]) ? $array["general_settings"]["images_dir"] : false;
		
		if ($this->images_dir && (isset($array["imager_settings"]["ini_file"]) && $array["imager_settings"]["ini_file"]))
		{
			require_once INCLUDES . "Imager.class";
			$this->Imager = new Imager($array["imager_settings"]["ini_file"], COMMON_IMAGES . $this->images_dir . "/", HTTP_COMMON_IMAGES . $this->images_dir . "/");
		}

		else
		{
			$this->Imager = false;
		}
        
        switch ($this->mode = $parts2[0])
        {
            case "speciality":
                $this->output["mode"] = "speciality";
                $this->speciality($this->module_uri);
                break;
            case "specialities":
                $this->output["mode"] = "specialities";
                $this->specialities($parts2[1]);
                break;
            case "speciality_directory":
                $this->output["mode"] = "speciality_directory";
                $this->speciality_directory();
                break;
            case "search_spec":
                $this->output["mode"] = "search_spec";
				break; 
            case "faculties":
                $this->output["mode"] = "faculties";
                $this->faculties();
                break;
            case "search":
                $this->output["mode"] = "search";
                $this->search_people($additional);
                break;            
            case "search_people":
                $this->output["mode"] = "search_people";
                break;            
            case "people":
                $this->output["mode"] = "people";
                $this->people($this->module_uri);
                break;
			/*case "edit_people":
				if ($Engine->OperationAllowed($this->module_id, "people.handle", -1, $Auth->usergroup_id)) {
					$this->output["mode"] = "edit_people";
					$this->edit_people($this->module_uri);
				}
                break;*/
			case "prof_choice":
				$this->output["mode"] = "prof_choice";
				$this->prof_choice();
				break;
            case "search_group":
                $this->output["mode"] = "search_group";
                break;
            case "groups":
                $this->output["mode"] = "groups";
                $this->groups($this->module_uri);                
                break;
			case "search_subjects":
                $this->output["mode"] = "search_subjects";
                break;
			case "students":
				$this->output["mode"] = "students";
				$this->students($this->module_uri);
				break;
			case "subjects":
                $this->output["mode"] = "subjects";
                $this->subjects($this->module_uri);
                break;
			case "dep_subjects":
				$this->dep_subjects($parts2[1], $mode);
				break;
			case "files_list":
				$this->output["mode"] = "files";
				$this->files_list($this->module_uri);
				break;
            case "timetable":
                $this->timetable();
                $this->output["mode"] = "timetable";
                break;
            case "timetable_make":
                $this->output["mode"] = "timetable_make";
                $this->timetable_make();
                break;
            case "timetable_subjects_by_departments":
                $this->output["mode"] = "timetable_subjects_by_departments";
                $this->timetable_subjects_by_departments($mode);
                break;
            case "timetable_graphics":
                $this->output["mode"] = "timetable_graphics";
                $this->timetable_graphics($mode);
                break;
            case "timetable_groups":
                $this->output["mode"] = "timetable_groups";
                $this->timetable_groups($mode);
                break;
            case "timetable_faculties":
                $this->output["mode"] = "timetable_faculties";
                $this->timetable_faculties($mode);
                break;
            case "timetable_departmens":
                $this->output["mode"] = "timetable_departmens";
                $this->timetable_departmens($mode);
                break;
            case "timetable_teachers":
                $this->output["mode"] = "timetable_teachers";
                if (isset($parts2[1])) {
					$this->output["mode"] = "select_teachers";
					$this->select_teachers($parts2[1]);
				}
				else
					$this->timetable_teachers($mode);
                break;
            case "timetable_subjects":
                $this->output["mode"] = "timetable_subjects";
                $this->timetable_subjects($mode);
                break;
            case "timetable_auditorium":
                $this->output["mode"] = "timetable_auditorium";
                $this->timetable_auditorium($mode);
                break;
            case "timetable_show":
                $this->output["mode"] = "timetable_show";
                $this->timetable_show();
                break;
			case "rekom":
				$this->output["mode"] = "rekom";
				$this->rekom();
				break;
			case "department_files":
				if(isset($parts2[1])) {
					$this->output["mode"] = "department_files";
					$this->department_files($parts2[1]);
				}
				break;
			case "faculties_files":
				if(isset($parts2[1])) {
					$this->output["mode"] = "faculties_files";
					$this->faculties_files($parts2[1]);
				}
				break;
			case "pulpit_list":
				$this->output["mode"] = "pulpit_list";
				if(isset($parts2[1]))
					$this->pulpit_list($parts2[1]);
				break;
			case "faculty_spec":
				$this->output["mode"] = "faculty_specialities";
				if(isset($parts2[1]))
					$this->faculty_specialities($parts2[1]);
				break;
            case "groups_list":
                $this->output["mode"] = "groups_list";
				//die(print_r($mode));
                if($mode[0])
                {
                    if($mode[0] == "add_group")
                    {
                        $this->add_group();
                    }
                    else
                    {
                        if(isset($mode[1]) && ( $mode[1] == "delete_group" || $mode[1] == "edit_group"))
                        {
                            $this->edit_group($mode);
                        }
                        else
                        {	
                            if($mode[1])
                                $this->edit_student($mode);
                            else
                                $this->students_list($mode);    
                        }    
                    }
                }
                else
                {
                    $this->timetable_show();    
                }
                break;
			
            default:
                die("Ensau module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
                
        }
    }
	
	 function search_people($search_query = false) {
		global $DB, $Engine;
		$output = array();
		$search_query_parts = explode(' ', $search_query);
		
				$DB->SetTable($this->db_prefix."people");
				
				$DB->AddFields(array("id", "last_name", "name", "patronymic", "status_id", "photo"));
				

					$res = $DB->Select();				
				
				$max_occurs = 0;
				while ($row = $DB->FetchObject($res))
				{
						$search_arr = array($row->last_name, $row->name, $row->patronymic);
						$occurs_count = 0;
						foreach ($search_arr as $name_part)
							foreach ($search_query_parts as $query_part)
							if ($name_part == trim($query_part)) {
								$occurs_count++;
							}	
						if ($occurs_count) {
							if ($row->status_id == 2) {
								if ($res = $DB->Exec("SELECT name FROM nsau_departments WHERE id IN (SELECT department_id FROM nsau_teachers WHERE people_id=".$row->id.") ")) {
									$row->dep = $DB->FetchAssoc($res);
								}
							}
							if ($row->photo)
								$row->photo = "http://".DEFAULT_DOMAIN.HTTP_ROOT.IMAGES_DIR."people/".$row->photo;
							if ($occurs_count>$max_occurs)
								$max_occurs = $occurs_count;
							$result = $row;
							$result->occurs = $occurs_count;
							$results[] = $result;
						}			
						
				} 
				if (isset($results)) {
					foreach($results as $result)
						if ($result->occurs == $max_occurs)
							$output[] = array(
								"uri" => "/people/".$result->id."/",//$result["uri"],
								"title" => $result->last_name.' '.$result->name.' '.$result->patronymic.( isset($result->dep) ? '- '.$result->dep : ''),
								"text" => "",//$result->last_name.' '.$result->name.' '.$result->patronymic,
								"pic" => $result->photo
								//"relevance" => (float)$row->relevance,
							);	
				}
				$DB->FreeRes($res); 
		
		
							
		$this->output["results"] = $output;
	}
	
	function faculty_specialities($faculties_id) {
		global $DB, $Engine;
		$DB->SetTable($this->db_prefix."specialities", "sp");
		//$DB->AddTable($this->db_prefix."spec_type", "spt");
		$DB->AddCondFS("sp.id_faculty", "=", $faculties_id);
		$DB->AddJoin($this->db_prefix."spec_type.code", "sp.code");
		//$DB->AddCondFS("sp.code", "=", "spt.code");
		$DB->AddField("sp.name");
		$DB->AddField("sp.code");
		$DB->AddField($this->db_prefix."spec_type.type");
		//echo $DB->SelectQuery();
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			if($row["type"])
				$this->output["faculty_spec"][$row["type"]][] = array(
						"name" => $row["name"],
						"code" => $row["code"],
					);
		}
	}
	
	function faculties_files($faculties_id) {
		global $DB, $Engine;
		$DB->SetTable($this->db_prefix."faculties");
		$DB->AddField("name");
		$DB->AddCondFS("id", "=", $faculties_id);
		$res_fac = $DB->Select();
		$row_fac = $DB->FetchObject($res_fac);
		
		$DB->SetTable($this->db_prefix."departments");
		$DB->AddField("id");
		$DB->AddField("name");
		$DB->AddCondFS("faculty_id", "=", $faculties_id);
		$res_dep = $DB->Select();
		while($row_dep = $DB->FetchAssoc($res_dep)) {			
			$DB->SetTable($this->db_prefix."subjects");
			$DB->AddField("id");
			$DB->AddField("name");
			$DB->AddCondFS("department_id", "=", $row_dep["id"]);
			$res1 = $DB->Select();
			$subject_array = null;
			while($row1 = $DB->FetchAssoc($res1)) {
				$DB->SetTable($this->db_prefix."files_subj");
				$DB->AddField("file_id");
				$DB->AddCondFS("subject_id", "=", $row1["id"]);
				$res2 = $DB->Select();
				$file_array = null;
				while($row2 = $DB->FetchAssoc($res2)) {
					$DB->SetTable($this->db_prefix."files");
					$DB->AddField("id");
					$DB->AddField("name");
					$DB->AddField("descr");
					$DB->AddCondFS("id", "=", $row2["file_id"]);
					$res3 = $DB->Select();
					while($row3 = $DB->FetchAssoc($res3)) {
						$file_array[] = array(
									"file_name" => $row3["name"],
									"file_descr" => $row3["descr"],
									"file_id" => $row3["id"]
								);
					}
				}
				if($file_array) {
					$subject_array[] = array(
													"subject_name" => $row1["name"],
													"file_list" => $file_array
												);
				}
			}
			//$subject_array["department_name"] = $row_dep->name;
			if($subject_array) {
				$this->output["faculties_files"][] = array(
												"faculties_name" => $row_dep["name"],
												"subject_list" => $subject_array
											);
			}	
		}
		$this->output["faculties_name"] = $row_fac->name;
	}
	
    function department_files($department_id) {
		global $DB, $Engine;
		$DB->SetTable($this->db_prefix."departments");
		$DB->AddField("name");
		$DB->AddCondFS("id", "=", $department_id);
		$res_dep = $DB->Select();
		$row_dep = $DB->FetchObject($res_dep);
		
		$DB->SetTable($this->db_prefix."subjects");
		$DB->AddField("id");
		$DB->AddField("name");
		$DB->AddCondFS("department_id", "=", $department_id);
		$res1 = $DB->Select();
		while($row1 = $DB->FetchAssoc($res1)) {
			$DB->SetTable($this->db_prefix."files_subj");
			$DB->AddField("file_id");
			$DB->AddCondFS("subject_id", "=", $row1["id"]);
			$res2 = $DB->Select();
			$file_array = null;
			while($row2 = $DB->FetchAssoc($res2)) {
				$DB->SetTable($this->db_prefix."files");
				$DB->AddField("id");
				$DB->AddField("name");
				$DB->AddField("descr");
				$DB->AddCondFS("id", "=", $row2["file_id"]);
				$res3 = $DB->Select();
				while($row3 = $DB->FetchAssoc($res3)) {
					$file_array[] = array(
								"file_name" => $row3["name"],
								"file_descr" => $row3["descr"],
								"file_id" => $row3["id"]
							);
				}
			}
			if($file_array) {
				$this->output["department_files"][] = array(
												"subject_name" => $row1["name"],
												"file_list" => $file_array
											);
			}
		}
		$this->output["department_name"] = $row_dep->name;
	}
    
    function timetable_graphics($mode)
    {
        global $DB, $Engine;
        if(empty($mode[0]))
        {
            if(!isset($_POST["graphic_name"]))
            {
                $this->output["graphics_list"]=array();
                $DB->SetTable($this->db_prefix."graphics_list");
                $DB->AddOrder("type");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $this->output["graphics_list"][] = $row;
                }
            }
            else
            {
                $DB->SetTable($this->db_prefix."graphics_list");
                $DB->AddValue("name",$_POST["graphic_name"]);
                $DB->AddValue("type",$_POST["type"]);
                if(isset($_POST["begin_day"]) && isset($_POST["begin_month"]) && isset($_POST["begin_year"]))
                {
                    $DB->AddValue("begin_session",$_POST["begin_year"]."-".$_POST["begin_month"]."-".$_POST["begin_day"]);
                }
                if(isset($_POST["end_day"]) && isset($_POST["end_month"]) && isset($_POST["end_year"]))
                {
                    $DB->AddValue("end_session",$_POST["end_year"]."-".$_POST["end_month"]."-".$_POST["end_day"]);
                }
                $DB->Insert();
                CF::Redirect($Engine->engine_uri);
            }
        }    
        else
        {
            if(!empty($mode[0]) && $mode[0] == "edit")
            {
                if(!isset($_POST["graphic_name"]))
                {
                    $DB->SetTable($this->db_prefix."graphics_list");
                    $DB->AddCondFS("id","=",$mode[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        $this->output["graphics_list_data"]=$row;
                    }
                }
                else
                {
                    $DB->SetTable($this->db_prefix."graphics_list");
                    $DB->AddCondFS("id","=",$mode[1]);
                    $DB->AddValue("name",$_POST["graphic_name"]);
                    $DB->AddValue("type",$_POST["type"]);
                    if(isset($_POST["begin_day"]) && isset($_POST["begin_month"]) && isset($_POST["begin_year"]))
                    {
                        $DB->AddValue("begin_session",$_POST["begin_year"]."-".$_POST["begin_month"]."-".$_POST["begin_day"]);
                    }
                    if(isset($_POST["end_day"]) && isset($_POST["end_month"]) && isset($_POST["end_year"]))
                    {
                        $DB->AddValue("end_session",$_POST["end_year"]."-".$_POST["end_month"]."-".$_POST["end_day"]);
                    }
                    $DB->Update();
                    CF::Redirect($Engine->engine_uri);
                }
            }
            if(!empty($mode[0]) && $mode[0] == "delete")
            {
                $DB->Exec("delete from `nsau_groups_graphics` where `id_graphics`=(select `id` from `nsau_graphics` where `id_graphics_list`=".$mode[1].")");
                $DB->Exec("delete from `nsau_hours` where `id_graphics`=(select `id` from `nsau_graphics` where `id_graphics_list`=".$mode[1].")");
                $DB->Exec("delete from `nsau_graphics` where `id_graphics_list`=".$mode[1]);
                $DB->SetTable($this->db_prefix."graphics_list");
                $DB->AddCondFS("id","=",$mode[1]);
                $DB->Delete();
                CF::Redirect($Engine->engine_uri);
            }
            if(empty($mode[1]))
            {
                if(!empty($_POST) && !isset($_POST["subject_id"]))
                {
                    foreach($_POST as $data)
                    {
                        $i=1;
                        foreach($data["l"] as $l)
                        {
                            $DB->SetTable($this->db_prefix."hours");
                            $DB->AddCondFS("id_graphics","=",$data["name"]);
                            $DB->AddCondFS("week_num","=",$i);
                            $DB->AddCondFS("type","=",0);
                            $res = $DB->Select();
                            if($row = $DB->FetchAssoc($res))
                            { 
                                if(!empty($l) && $l!=$row["hours_per_week"])
                                {
                                    
                                    $DB->SetTable($this->db_prefix."hours");
                                    $DB->AddCondFS("id","=",$row["id"]);             
                                    $DB->AddValue("hours_per_week",$l);
                                    $DB->Update();
                                }
                                else
                                {
                                    if(empty($l))
                                    {
                                        $DB->SetTable($this->db_prefix."hours");
                                        $DB->AddCondFS("id","=",$row["id"]);
                                        $DB->Delete();
                                           
                                    }
                                }
                            }
                            else
                            {
                                if(!empty($l))
                                {
                                    $DB->SetTable($this->db_prefix."hours");
                                    $DB->AddValue("hours_per_week",$l);
                                    $DB->AddValue("week_num",$i);
                                    $DB->AddValue("id_graphics",$data["name"]);
                                    $DB->AddValue("type",0);
                                    $DB->Insert();
                                }
                            }
                            $i++;
                        }
                        $i=1;
                        foreach($data["p"] as $l)
                        {
                            $DB->SetTable($this->db_prefix."hours");
                            $DB->AddCondFS("id_graphics","=",$data["name"]);
                            $DB->AddCondFS("week_num","=",$i);
                            $DB->AddCondFS("type","=",1);
                            $res = $DB->Select();
                            if($row = $DB->FetchAssoc($res))
                            { 
                                if(!empty($l) && $l!=$row["hours_per_week"])
                                {
                                    
                                    $DB->SetTable($this->db_prefix."hours");
                                    $DB->AddCondFS("id","=",$row["id"]);             
                                    $DB->AddValue("hours_per_week",$l);
                                    $DB->Update();
                                }
                                else
                                {
                                    if(empty($l))
                                    {
                                        $DB->SetTable($this->db_prefix."hours");
                                        $DB->AddCondFS("id","=",$row["id"]);
                                        $DB->Delete();
                                           
                                    }
                                }
                            }
                            else
                            {
                                if(!empty($l))
                                {
                                    $DB->SetTable($this->db_prefix."hours");
                                    $DB->AddValue("hours_per_week",$l);
                                    $DB->AddValue("week_num",$i);
                                    $DB->AddValue("id_graphics",$data["name"]);
                                    $DB->AddValue("type",1);
                                    $DB->Insert();
                                }
                            }
                            $i++;
                        }
                    }
                }
                if(isset($_POST["subject_id"]) && isset($_POST["type"]) && isset($_POST["kol_groups"]) && isset($_POST["lecture_hours"]) && isset($_POST["practice_hours"]) && isset($_POST["attestation_type"]) && isset($_POST["groups"]))
                {
                    $DB->SetTable($this->db_prefix."subjects");
                    $DB->AddCondFS("id","=",$_POST["subject_id"]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        $department_id = $row["department_id"]; 
                    }     
                    $DB->SetTable($this->db_prefix."graphics");
                    $DB->AddValue("id_graphics_list",$mode[0]);
                    $DB->AddValue("department_id",$department_id);
                    $DB->AddValue("subject_id",$_POST["subject_id"]);
                    $DB->AddValue("type",$_POST["type"]);
                    $DB->AddValue("kol_groups",$_POST["kol_groups"]);
                    $DB->AddValue("lecture_hours", $_POST["lecture_hours"]);
                    $DB->AddValue("practice_hours",$_POST["practice_hours"]);
                    $DB->AddValue("attestation_type",$_POST["attestation_type"]);
                    if(isset($_POST["ust_lecture"]))
                    {
                        $DB->AddValue("ust_lecture",$_POST["ust_lecture"]);
                    }
                    if(isset($_POST["ust_practice"]))
                    {
                        $DB->AddValue("ust_practice",$_POST["ust_practice"]);
                    }
                    if(isset($_POST["Contr"]))
                    {
                        if($_POST["Contr"] == "on")
                            $DB->AddValue("Contr",1);
                    }
                    if(isset($_POST["Curse"]))
                    {
                        if($_POST["Curse"]=="on")
                            $DB->AddValue("Curse",1);
                    }
                    $DB->Insert();
                    $tmp = str_replace(" ", "", $_POST["groups"]);
                    $groups = explode(";",$tmp);
                    $max_id = $DB->Exec("Select max(`id`) from `nsau_graphics`");
                    if($row3 = $DB->FetchAssoc($max_id))
                    {
                        foreach($row3 as $r)
                        {
                            for($i=0; $i<count($groups)-1; $i++)
                            {
                                $res = $DB->Exec("insert into `nsau_groups_graphics` set `id_group` = (select `id` from `nsau_groups` where `name`=".$groups[$i]."), `id_graphics` = ".$r);
                            }                    
                        }  
                    }      
                     
                }
                 
                $DB->SetTable($this->db_prefix."graphics_list");
                $DB->AddCondFS("id","=",$mode[0]);
                $res1 = $DB->Select();
                if($row1 = $DB->FetchAssoc($res1))
                {
                    $this->output["g_name"]=$row1["name"];
                    $this->output["begin_session"]=$row1["begin_session"];    
                    $this->output["end_session"]=$row1["end_session"];
                    $this->output["g_id"]=$mode[0];
                    $this->output["uri"]=$Engine->engine_uri;
                    $this->output["g_type"]=$row1["type"];
                }
                $this->output["graphics_data"]=array();
                
                $DB->SetTable($this->db_prefix."dates_for_weeks");
                $dates = $DB->Select();
                while($d = $DB->FetchAssoc($dates))
                {
                    $this->output["dates"][]=$d;
                }
                
                
                
                $DB->SetTable($this->db_prefix."graphics","g");
                $DB->AddTable($this->db_prefix."departments","d");
                $DB->AddTable($this->db_prefix."subjects","s");
                $DB->AddTable($this->db_prefix."faculties","f");
                $DB->AddCondFS("g.id_graphics_list","=",$mode[0]);
                $DB->AddCondFF("g.department_id","=","d.id");
                $DB->AddCondFF("d.faculty_id","=","f.id");
                $DB->AddCondFF("g.subject_id","=","s.id");
                $DB->AddField("g.id","id_graphic");
                $DB->AddField("d.name","department_name");
                $DB->AddField("d.id","department_id");
                $DB->AddField("f.id","faculty_id");
                $DB->AddField("f.name","faculty_name");
                $DB->AddField("f.short_name","faculty_short_name");
                $DB->AddField("s.name","subject_name");
                $DB->AddField("s.id","subject_id");
                $DB->AddField("g.type","type");
                $DB->AddField("g.kol_groups","kol_groups");
                $DB->AddField("g.lecture_hours","lecture_hours");
                $DB->AddField("g.practice_hours","practice_hours");
                $DB->AddField("g.ust_lecture","ust_lecture");
                $DB->AddField("g.ust_practice","ust_practice");
                $DB->AddField("g.Contr","Contr");
                $DB->AddField("g.Curse","Curse");
                $DB->AddField("g.attestation_type","attestation_type");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $row["pr"]=array();
                    $row["lec"]=array();
                    $DB->SetTable($this->db_prefix."hours");
                    $DB->AddCondFS("id_graphics","=",$row["id_graphic"]);
                    $DB->AddOrder("id_graphics");
                    $DB->AddOrder("week_num");
                    $res2 = $DB->Select();
                    while($row2 = $DB->FetchAssoc($res2))
                    {
                        if($row2["type"])
                            $row["pr"][] = $row2;
                        else
                            $row["lect"][] = $row2;
                    }
                    $res1 = $DB->Exec("SELECT count(`nsau_people`.`id`),
    `nsau_groups_graphics`.`id_group`, `nsau_groups`.`name`  FROM `nsau_people`, `nsau_groups_graphics`, `nsau_groups` WHERE `nsau_people`.`id_group` = `nsau_groups_graphics`.`id_group` and `nsau_groups`.`id`=`nsau_groups_graphics`.`id_group` and `nsau_groups_graphics`.`id_graphics` =".$row["id_graphic"]." group by `nsau_groups`.`name`");
                    $sum = 0;
                    while($row1 = $DB->FetchAssoc($res1))
                    {
                            $row["groups"][]=array("group_id" => $row1["id_group"], "group_name" => $row1["name"]);
                            $sum += $row1["count(`nsau_people`.`id`)"];
                    }
                    $row["sum_students"]=$sum;
                    $this->output["graphics_data"][]=$row;
                }
            }
            else
            {
                if($mode[1] == "calendar")
                {
                    if(isset($_POST["end_date"]) && isset($_POST["begin_date"]))
                    {
                        foreach($_POST["begin_date"] as $date)
                        {
                            $DB->SetTable($this->db_prefix."dates_for_weeks");
                            $DB->AddCondFS("num_week","=",$date["num_week"]);
                            $DB->AddValue("begin_date",$date["year"]."-".$date["month"]."-".$date["day"]);
                            $DB->Update();
                        }
                        foreach($_POST["end_date"] as $date)
                        {
                            $DB->SetTable($this->db_prefix."dates_for_weeks");
                            $DB->AddCondFS("num_week","=",$date["num_week"]);
                            $DB->AddValue("end_date",$date["year"]."-".$date["month"]."-".$date["day"]);
                            $DB->Update();
                        }
                    }
                    //CF::Debug($_POST);
                    $this->output["calendar"] = array();
                    $DB->SetTable($this->db_prefix."dates_for_weeks");
                    $DB->AddOrder("num_week");
                    $res = $DB->Select();
                    while($row = $DB->FetchAssoc($res))
                    {
                        $this->output["calendar_data"][] = $row;
                    }
                    $this->output["calendar"]["back_link"]=$Engine->engine_uri.$mode[0]."/";
                }
                if($mode[1] == "edit")
                {                      
                    if(!isset($_POST["subject_id"]))
                    {
                        $DB->SetTable($this->db_prefix."graphics","g");
                        $DB->AddTable($this->db_prefix."departments","d");
                        $DB->AddTable($this->db_prefix."subjects","s");
                        $DB->AddTable($this->db_prefix."faculties","f");
                        $DB->AddTable($this->db_prefix."graphics_list","gl");
                        $DB->AddCondFS("gl.id","=",$mode[0]);
                        $DB->AddCondFS("g.id","=",$mode[2]);
                        $DB->AddCondFF("g.department_id","=","d.id");
                        $DB->AddCondFF("d.faculty_id","=","f.id");
                        $DB->AddCondFF("g.subject_id","=","s.id");
                        $DB->AddField("gl.name","g_name");
                        $DB->AddField("gl.type","g_type");
                        $DB->AddField("g.id","id_graphic");
                        $DB->AddField("d.name","department_name");
                        $DB->AddField("d.id","department_id");
                        $DB->AddField("f.id","faculty_id");
                        $DB->AddField("f.name","faculty_name");
                        $DB->AddField("f.short_name","faculty_short_name");
                        $DB->AddField("s.name","subject_name");
                        $DB->AddField("s.id","subject_id");
                        $DB->AddField("g.type","type");
                        $DB->AddField("g.kol_groups","kol_groups");
                        $DB->AddField("g.lecture_hours","lecture_hours");
                        $DB->AddField("g.practice_hours","practice_hours");
                        $DB->AddField("g.ust_lecture","ust_lecture");
                        $DB->AddField("g.ust_practice","ust_practice");
                        $DB->AddField("g.Contr","Contr");
                        $DB->AddField("g.Curse","Curse");
                        $DB->AddField("g.attestation_type","attestation_type");
                        $res = $DB->Select();
                        if($row = $DB->FetchAssoc($res))
                        {
                            $res1 = $DB->Exec("SELECT count(`nsau_people`.`id`),
        `nsau_groups_graphics`.`id_group`, `nsau_groups`.`name`  FROM `nsau_people`, `nsau_groups_graphics`, `nsau_groups` WHERE `nsau_people`.`id_group` = `nsau_groups_graphics`.`id_group` and `nsau_groups`.`id`=`nsau_groups_graphics`.`id_group` and `nsau_groups_graphics`.`id_graphics` =".$mode[2]." group by `nsau_groups`.`name`");
                            $sum = 0;
                            while($row1 = $DB->FetchAssoc($res1))
                            {
                                    $row["groups"][]=array("group_id" => $row1["id_group"], "group_name" => $row1["name"]);
                                    $sum += $row1["count(`nsau_people`.`id`)"];
                            }
                            $row["sum_students"]=$sum;
                            $this->output["edit_graphic"]=$row;
                        }
                    }
                    else
                    {
                        if(isset($_POST["type"]) && isset($_POST["kol_groups"]) && isset($_POST["lecture_hours"]) && isset($_POST["practice_hours"]) && isset($_POST["attestation_type"]) && isset($_POST["groups"]))
                        {
                            $DB->SetTable($this->db_prefix."subjects");
                            $DB->AddCondFS("id","=",$_POST["subject_id"]);
                            $res = $DB->Select();
                            if($row = $DB->FetchAssoc($res))
                            {
                                $department_id = $row["department_id"]; 
                            }     
                            $DB->SetTable($this->db_prefix."graphics");
                            $DB->AddCondFS("id","=",$mode[2]);
                            $DB->AddValue("id_graphics_list",$mode[0]);
                            $DB->AddValue("department_id",$department_id);
                            $DB->AddValue("subject_id",$_POST["subject_id"]);
                            $DB->AddValue("type",$_POST["type"]);
                            $DB->AddValue("kol_groups",$_POST["kol_groups"]);
                            $DB->AddValue("lecture_hours", $_POST["lecture_hours"]);
                            $DB->AddValue("practice_hours",$_POST["practice_hours"]);
                            $DB->AddValue("attestation_type",$_POST["attestation_type"]);
                            $DB->AddValue("ust_lecture",$_POST["ust_lecture"]);
                            $DB->AddValue("ust_practice",$_POST["ust_practice"]);
                            if(isset($_POST["Contr"]) && $_POST["Contr"]=="on")
                            {
                                $DB->AddValue("Contr",1);
                            }
                            else
                            {
                                $DB->AddValue("Contr",0);
                            }
                            if(isset($_POST["Curse"]) && $_POST["Curse"]=="on")
                            {
                                $DB->AddValue("Curse",1);
                            }
                            else
                            {
                                $DB->AddValue("Curse",0);
                            }
                            $DB->Update();
                            $tmp = str_replace(" ", "", $_POST["groups"]);
                            $groups = explode(";",$tmp);
                            $max_id = $DB->Exec("Select max(`id`) from `nsau_graphics`");
                            for($i=0; $i<count($groups)-1; $i++)
                            {
                                $res = $DB->Exec("delete from `nsau_groups_graphics` where `id_graphics` = ".$mode[2]);
                            }
                            for($i=0; $i<count($groups)-1; $i++)
                            {
                                $res = $DB->Exec("insert into `nsau_groups_graphics` set `id_group` = (select `id` from `nsau_groups` where `name`=".$groups[$i]."), `id_graphics` = ".$mode[2]);
                            }
                            CF::Redirect($Engine->engine_uri.$mode[0]."/");    
                        }
                    }
                }
                else
                {
                    if($mode[1] == "delete")
                    {
                        $DB->SetTable($this->db_prefix."groups_graphics");
                        $DB->AddCondFS("id_graphics","=",$mode[2]);
                        $DB->Delete();
                        $DB->SetTable($this->db_prefix."hours");
                        $DB->AddCondFS("id_graphics","=",$mode[2]);
                        $DB->Delete();
                        $DB->SetTable($this->db_prefix."graphics");
                        $DB->AddCondFS("id","=",$mode[2]);
                        $DB->Delete();    
                        CF::Redirect($Engine->engine_uri.$mode[0]."/");
                    }
                }
            }
            $DB->SetTable($this->db_prefix."subjects","s");
            $DB->AddTable($this->db_prefix."departments","d");
            $DB->AddTable($this->db_prefix."faculties","f");
            $DB->AddCondFF("s.department_id","=","d.id");
            $DB->AddCondFF("f.id","=","d.faculty_id");
            $DB->AddOrder("s.department_id");
            $DB->AddOrder("d.faculty_id");
            $DB->AddField("s.id","subject_id");
            $DB->AddField("s.name","subject_name");
            $DB->AddField("d.id","department_id");
            $DB->AddField("d.name","department_name");
            $DB->AddField("d.faculty_id","faculty_id");
            $DB->AddField("f.name","faculty_name");
            $DB->AddField("f.short_name","faculty_short_name");
            $res = $DB->Select();
            while($row = $DB->FetchAssoc($res))
            {
                $this->output["subjects"][] = $row;
            }                                        
        }
    }
    
    
    function timetable_subjects_by_departments($mode)
    {
        global $DB, $Engine;
        if(empty($mode[0]))
        {
            $DB->SetTable($this->db_prefix."departments","d");
            $DB->AddTable($this->db_prefix."faculties","f");
            $DB->AddOrder("d.faculty_id");
            $DB->AddCondFF("d.faculty_id","=","f.id");
            $DB->AddField("d.name","name");
            $DB->AddField("d.id","id");
            $DB->AddField("f.id","faculty_id");
            $DB->AddField("f.name","faculty_name");
            $res =  $DB->Select();
            while($row = $DB->FetchAssoc($res))
            {
                $this->output["departments_list"][] = $row;
            }
            $DB->SetTable($this->db_prefix."faculties");
            $res = $DB->Select();
            while($row = $DB->FetchAssoc($res))
            {
                $this->output["faculties"][] = $row;    
            }
        }
        else
        {
            if($mode[0]=="show" && empty($mode[2]))
            {
                if(isset($_POST["subject"]))
                {
                    $DB->SetTable($this->db_prefix."subjects_by_departments");
                    $DB->AddValue("subject_id",$_POST["subject"]);
                    $DB->AddValue("faculty_id",$_POST["faculty"]);
                    $DB->AddValue("department_id",$mode[1]);
                    $DB->AddValue("year",$_POST["year"]);
                    $DB->AddValue("type",$_POST["type"]);
                    $DB->AddValue("teacher_id",$_POST["teacher_id"]);
                    $DB->AddValue("groups",$_POST["groups"]);
                    $DB->AddValue("subgroups",$_POST["subgroups"]);
                    $DB->AddValue("auditorium",$_POST["auditorium"]);
                    if(isset($_POST["rectors"]))
                        $DB->AddValue("participation_rectors",1);
                    else
                        $DB->AddValue("participation_rectors",0);
                    if(isset($_POST["prorectors"]))
                        $DB->AddValue("participation_prorectors",1);
                    else
                        $DB->AddValue("participation_prorectors",0);
                    if(isset($_POST["decanats"]))
                        $DB->AddValue("participation_decanats",1);
                    else
                        $DB->AddValue("participation_decanats",0);
                    if(isset($_POST["academic_senate"]))
                        $DB->AddValue("participation_academic_senate",1);
                    else
                        $DB->AddValue("participation_academic_senate",0);
                    $DB->AddValue("state",$_POST["state"]);
                    if(isset($_POST["rate"]))
                        $DB->AddValue("rate",$_POST["rate"]);
                    if(isset($_POST["comments"]))
                        $DB->AddValue("comments",$_POST["comments"]);                        
                    $DB->Insert();                    
                }
                $this->output["show_mode"]="show";
                $DB->SetTable($this->db_prefix."subjects");
                $DB->AddCondFS("department_id","=",$mode[1]);
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $this->output["subjects_list"][] = $row;
                }
                $DB->SetTable($this->db_prefix."teachers","t");
                $DB->AddTable($this->db_prefix."people","p");
                $DB->AddCondFS("t.department_id","=",$mode[1]);
                $DB->AddAltFF("t.people_id","=","p.id");
                $DB->AddField("p.last_name","last_name");             
                $DB->AddField("p.name","name");
                $DB->AddField("p.patronymic","patronymic");
                $DB->AddField("p.id","id");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {              
                    $this->output["teachers_list"][] = $row;
                }
                $DB->SetTable($this->db_prefix."faculties");
                $res = $DB->Select();

                while($row = $DB->FetchAssoc($res))
                    $this->output["faculty_list"][] = $row;
                $DB->SetTable($this->db_prefix."subjects_by_departments","sbd");
                $DB->AddTable($this->db_prefix."subjects","s");
                $DB->AddTable($this->db_prefix."people","p");
                $DB->AddTable($this->db_prefix."faculties","f");
                $DB->AddCondFF("sbd.faculty_id","=","f.id");
                $DB->AddCondFF("sbd.teacher_id","=","p.id");
                $DB->AddCondFF("sbd.subject_id","=","s.id");
                $DB->AddCondFS("sbd.department_id","=",$mode[1]);
                $DB->AddField("sbd.id","id");
                $DB->AddField("sbd.subject_id","subject_id");
                $DB->AddField("s.name","subject");
                $DB->AddField("sbd.faculty_id","faculty_id");
                $DB->AddField("f.name","faculty");
                $DB->AddField("f.short_name","faculty_short");
                $DB->AddField("sbd.department_id","department_id");
                $DB->AddField("sbd.year","year");
                $DB->AddField("sbd.teacher_id","teacher_id");
                $DB->AddField("p.last_name","last_name");
                $DB->AddField("p.name","name");
                $DB->AddField("p.patronymic","patronymic");
                $DB->AddField("sbd.type","type");
                $DB->AddField("sbd.groups","groups");
                $DB->AddField("sbd.subgroups","subgroups");
                $DB->AddField("sbd.auditorium","auditorium");
                $DB->AddField("sbd.participation_rectors","rectors");
                $DB->AddField("sbd.participation_prorectors","prorectors");
                $DB->AddField("sbd.participation_decanats","decanats");
                $DB->AddField("sbd.participation_academic_senate","academic_senate");
                $DB->AddField("sbd.comments","comments");
                $DB->AddField("sbd.state","state");
                $DB->AddField("sbd.rate","rate");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                    $this->output["subjects_by_departments_list"][] = $row;
                $DB->SetTable($this->db_prefix."departments");
                $DB->AddCondFS("id","=",$mode[1]);
                $res = $DB->Select();
                if($row = $DB->FetchAssoc($res))
                    $this->output["department_name"]=$row["name"];
            }
            if(!empty($mode[2]) && $mode[2] == "edit")
            {
                if(!isset($_POST["id"]))
                {
                    $this->output["show_mode"]="edit";
                    $DB->SetTable($this->db_prefix."subjects_by_departments");
                    $DB->AddCondFS("id","=",$mode[3]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                        $this->output["edit_row"] = $row;
                    $DB->SetTable($this->db_prefix."subjects");
                    $DB->AddCondFS("department_id","=",$mode[1]);
                    $res = $DB->Select();
                    while($row = $DB->FetchAssoc($res))
                    {
                        $this->output["subjects_list"][] = $row;
                    }
                    $DB->SetTable($this->db_prefix."teachers","t");
                    $DB->AddTable($this->db_prefix."people","p");
                    $DB->AddCondFS("t.department_id","=",$mode[1]);
                    $DB->AddAltFF("t.people_id","=","p.id");
                    $DB->AddField("p.last_name","last_name");             
                    $DB->AddField("p.name","name");
                    $DB->AddField("p.patronymic","patronymic");
                    $DB->AddField("p.id","id");
                    $res = $DB->Select();
                    while($row = $DB->FetchAssoc($res))
                    {              
                        $this->output["teachers_list"][] = $row;
                    }
                    $DB->SetTable($this->db_prefix."faculties");
                    $res = $DB->Select();
                    while($row = $DB->FetchAssoc($res))
                        $this->output["faculty_list"][] = $row;
                }
                else
                {
                    $DB->SetTable($this->db_prefix."subjects_by_departments");
                    $DB->AddCondFS("id","=",$_POST["id"]);
                    $DB->AddValue("subject_id",$_POST["subject"]);
                    $DB->AddValue("faculty_id",$_POST["faculty"]);
                    $DB->AddValue("department_id",$mode[1]);
                    $DB->AddValue("year",$_POST["year"]);
                    $DB->AddValue("type",$_POST["type"]);
                    $DB->AddValue("teacher_id",$_POST["teacher_id"]);
                    $DB->AddValue("groups",$_POST["groups"]);
                    $DB->AddValue("subgroups",$_POST["subgroups"]);
                    $DB->AddValue("auditorium",$_POST["auditorium"]);
                    if(isset($_POST["rectors"]))
                        $DB->AddValue("participation_rectors",1);
                    else
                        $DB->AddValue("participation_rectors",0);
                    if(isset($_POST["prorectors"]))
                        $DB->AddValue("participation_prorectors",1);
                    else
                        $DB->AddValue("participation_prorectors",0);
                    if(isset($_POST["decanats"]))
                        $DB->AddValue("participation_decanats",1);
                    else
                        $DB->AddValue("participation_decanats",0);
                    if(isset($_POST["academic_senate"]))
                        $DB->AddValue("participation_academic_senate",1);
                    else
                        $DB->AddValue("participation_academic_senate",0);
                    $DB->AddValue("state",$_POST["state"]);
                    if(isset($_POST["rate"]))
                        $DB->AddValue("rate",$_POST["rate"]);
                    if(isset($_POST["comments"]))
                        $DB->AddValue("comments",$_POST["comments"]);
                    $DB->Update();
                    CF::Redirect($Engine->engine_uri.$mode[0]."/".$mode[1]."/");
                }    
            }
            if(!empty($mode[2]) && $mode[2] == "delete")
            {
                $DB->SetTable($this->db_prefix."subjects_by_departments");
                $DB->AddCondFS("id","=",$mode[3]);
                $DB->Delete();
                CF::Redirect($Engine->engine_uri.$mode[0]."/".$mode[1]."/");
            }
        }
    }
    
    function edit_group($mode)							/*++++++++++++++++++++++++++++++++++++++++++++*/
    {
        global $DB, $Engine;
        if($mode[1] == "delete_group")
        {
            $DB->SetTable($this->db_prefix."people");
            $DB->AddCondFS("id_group", "=", $mode[0]);
            $DB->AddCondFS("status_id","<>","4");
            $res = $DB->Select();
            if(!($row = $DB->FetchAssoc($res)))
            {    
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddCondFS("id", "=", $mode[0]);
                $DB->Delete();
                CF::Redirect($Engine->engine_uri);    
            }
            else
            {
                $this->output["messages"]["bad"][] = 305;
                CF::Redirect($Engine->engine_uri."/".$mode[0]."/");   
            }
        }
        else
        {
            if(!isset($_POST["id"]))
                {
                    $DB->SetTable($this->db_prefix."groups");
                    $DB->AddCondFS("id", "=", $mode[0]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        $DB->SetTable($this->db_prefix."faculties");
                        $DB->AddCondFS("id", "=", $row["id_faculty"]);
                        $res1 = $DB->Select();
                        if($row1 = $DB->FetchAssoc($res1))
                            $row["faculty_name"] = $row1["name"];
                        $DB->SetTable($this->db_prefix."faculties");
                        $res1 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res1))
                        {
                            $this->output["faculties_edit_group"][]=$row2;
                        }                        
                        $this->output["edit_group"][]=$row;
                    }
                }
                else
                {
                    $DB->SetTable($this->db_prefix."groups");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $DB->AddValue("name", $_POST["group_name"]);
                    $DB->AddValue("id_faculty", $_POST["faculty"]);
                    $DB->AddValue("year", $_POST["year"]);
                    $DB->Update();
                    $mode[0]=NULL;
                    CF::Redirect($Engine->engine_uri);
                }    
        }
    }
    
    
    function add_group()							/*++++++++++++++++++++++++++++++++++++++++++++++++++*/
    {
        global $DB, $Engine;
        if(isset($_POST["group_name"]))
        {
            $DB->SetTable($this->db_prefix."groups");
            $DB->AddCondFS("name","=",$_POST["group_name"]);
            $res = $DB->Select();
            if($row = $DB->FetchAssoc($res))
            {
                $this->output["messages"]["bad"][] = 304;    
            }
            else
            {
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddValue("name", $_POST["group_name"]);
                $DB->AddValue("id_faculty", $_POST["faculty"]);
                $DB->AddValue("year", $_POST["year"]);
                $DB->Insert();
                CF::Redirect($Engine->engine_uri);    
            }
        }
        $DB->SetTable($this->db_prefix."faculties");
        $res = $DB->Select();
        while($row = $DB->FetchAssoc($res))
        {
            $this->output["faculty_list"][] = $row;
        }
    }
    
    function update_user($row, $group_id)
    {
        global $DB;
        //CF::Debug($_POST);
        if(isset($_POST["username"]) && isset($_POST["email"])) {
            $DB->SetTable("auth_users");
            $DB->AddCondFS("id","=",$row["user_id"]);
            $res3 = $DB->Select();
            if($row3 = $DB->FetchAssoc($res3)) {
                $DB->SetTable("auth_users");
                $DB->AddCondFS("id","=",$row["user_id"]);
                $DB->AddValue("email", $_POST["email"]);
                $DB->AddValue("username", $_POST["username"]);
                $DB->AddValue("usergroup_id",$group_id);
                //$DB->AddValue("displayed_name", $_POST["last_name"]." ".$_POST["first_name"]." ".$_POST["patronymic"]);
                $DB->AddValue("displayed_name", $_POST["first_name"]." ".$_POST["patronymic"]);
                if(isset($_POST["password1"]) && isset($_POST["password2"]) && $_POST["password1"] == $_POST["password2"])
                {
                    $DB->AddValue("password",md5($_POST["password1"]));
                }    
                $DB->Update();
            } else {
				$this->insert_user($row["id"], $group_id);
			} 
        }
    }
    
    function insert_user($id, $group_id)
    {
        global $DB;
        if(isset($_POST["username"]) && isset($_POST["email"])) {
			
            $DB->SetTable("auth_users");
            $DB->AddValue("email", $_POST["email"]);
            $DB->AddValue("username", $_POST["username"]);
            $DB->AddValue("usergroup_id",$group_id);
            //$DB->AddValue("displayed_name", $_POST["last_name"]." ".$_POST["first_name"]." ".$_POST["patronymic"]);
            $DB->AddValue("displayed_name", $_POST["first_name"]." ".$_POST["patronymic"]);
            if(isset($_POST["password1"]) && isset($_POST["password2"]) && $_POST["password1"] == $_POST["password2"])
            {
               $DB->AddValue("password",md5($_POST["password1"]));
            }
            $DB->Insert();
            //$DB->SetTable("auth_users");
            //$DB->AddCondFS("email","=",$_POST["email"]);
            //$res4 = $DB->Select();
			$res4 = $DB->Exec("SELECT max(LAST_INSERT_ID(id)) as 'id' FROM auth_users limit 1");
            if($row4 = $DB->FetchAssoc($res4)) {
                $DB->SetTable($this->db_prefix."people");
                $DB->AddCondFS("id","=",$id);
                $DB->AddValue("user_id",$row4["id"]);
                $DB->Update();
				return $row4["id"];
            }    
        }
		return false;
    }
       
    //редактирование студента с id=$mode[1]
    function edit_student($mode)
    {
        global $DB, $Engine;
        if(isset($mode[2]) && $mode[2] == "expulsion")
        {
            $DB->SetTable($this->db_prefix."people");
            $DB->AddCondFS("id", "=", $mode[1]);
            $DB->AddValue("status_id","4");    
            $DB->Update();
            CF::Redirect($Engine->engine_uri.$mode[0]."/");
        }
        if(isset($_POST["last_name"]))
        {
            $DB->SetTable($this->db_prefix."people");
            $DB->AddCondFS("id", "=", $mode[1]);
            $DB->AddValue("last_name",$_POST["last_name"]);
            $DB->AddValue("name",$_POST["first_name"]);
            $DB->AddValue("patronymic", $_POST["patronymic"]);
            $DB->AddValue("male", $_POST["male"]);
            $DB->AddValue("year", $_POST["year"]);
            $DB->AddValue("id_group",$_POST["group"]);
            $DB->AddValue("subgroup", $_POST["subgroup"]);
            //$DB->AddValue("id_speciality", $_POST["id_speciality"]);
            $DB->Update();
            $DB->SetTable($this->db_prefix."people");
            $DB->AddCondFS("id", "=", $mode[1]);
            $res = $DB->Select();
			$row = $DB->FetchAssoc($res);
            if($row["user_id"])
            {
                /*if(isset($_POST["username"]) && isset($_POST["email"]))
                {
                $DB->SetTable("auth_users");
                $DB->AddCondFS("id","=",$row["user_id"]);
                $res3 = $DB->Select();
                if($row3 = $DB->FetchAssoc($res3))
                {
                    $DB->SetTable("auth_users");
                    $DB->AddCondFS("id","=",$row["user_id"]);
                    $DB->AddValue("email", $_POST["email"]);
                    $DB->AddValue("username", $_POST["username"]);
                    $DB->AddValue("displayed_name", $_POST["last_name"]." ".$_POST["first_name"]." ".$_POST["patronymic"]);    
                    $DB->Update();
                }    
                }*/
                $this->update_user($row, 3);
            }
                else
                {
                    /*if(isset($_POST["username"]) && isset($_POST["email"]))
                    {
                        $DB->SetTable("auth_users");
                        $DB->AddValue("email", $_POST["email"]);
                        $DB->AddValue("username", $_POST["username"]);
                        $DB->AddValue("displayed_name", $_POST["last_name"]." ".$_POST["first_name"]." ".$_POST["patronymic"]);
                        $DB->Insert();
                        $DB->SetTable("auth_users");
                        $DB->AddCondFS("email","=",$_POST["email"]);
                        $res4 = $DB->Select();
                        if($row4 = $DB->FetchAssoc($res4))
                        {
                            $DB->SetTable($this->db_prefix."people");
                            $DB->AddCondFS("id","=",$mode[1]);
                            $DB->AddValue("user_id",$row4["id"]);
                            if(!isset($_POST["password1"]) || !isset($_POST["password2"]) || $_POST["password1"] != $_POST["password2"])
                            {
                                $this->output["messages"]["bad"][] = 301;    
                            }
                            else
                            {
                                $DB->AddValue("password",$_POST["password1"]);
                            }
                            $DB->Update();    
                        }    
                    }*/
                    $this->insert_user($mode[1],3);
                }
                    
            }
        if(isset($mode[2]) && $mode[2] == "marks")
        {
            $this->marks($mode);               
        }
        else
        {
            $DB->SetTable($this->db_prefix."people");
            $DB->AddCondFS("id", "=", $mode[1]);
            $res = $DB->Select();
            if($row = $DB->FetchAssoc($res))
            {
                $DB->SetTable("auth_users");
                $DB->AddCondFS("id", "=", $row["user_id"]);
                $res2 = $DB->Select();
                if($row2 = $DB->FetchAssoc($res2))
                {
                    $row["username"] = $row2["username"];
                    $row["displayed_name"] = $row2["displayed_name"];
                    $row["email"] = $row2["email"];
                    $row["password"] = $row2["password"];
                }
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddCondFS("id", "=", $row["id_group"]);
                $res2 = $DB->Select();
                if($row2 = $DB->FetchAssoc($res2))
                {
                    $row["group_name"] = $row2["name"];
                    $row["group_year"] = $row2["year"];
                    $row["id_faculty"] = $row2["id_faculty"];
                }
                $this->output["edit_student"] = $row;
                $DB->SetTable($this->db_prefix."specialities");
                $DB->AddCondFS("id_faculty", "=", $row["id_faculty"]);
                $res3 = $DB->Select();
                while($row3 = $DB->FetchAssoc($res3))
                {
                    $this->output["specialities"][] = $row3;
                }
            }
            $DB->SetTable($this->db_prefix."faculties");
            $res = $DB->Select();
            while($row = $DB->FetchAssoc($res))
            {
                $row["groups"] = array();
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddCondFS("id_faculty","=", $row["id"]);
                $res2 = $DB->Select();
                while($row2 = $DB->FetchAssoc($res2))
                {
                    $row["groups"][] = $row2;
                }
                $this->output["faculties_list"][] = $row;                   
            }
        }     
    }
    
    function marks($mode)
    {
        global $DB, $Engine;
        if(isset($_POST["student_id"]))
        {
            if(empty($_POST["day"]) || empty($_POST["month"]) || empty($_POST["year"]) || empty($_POST["subject"]) || empty($_POST["mark"]) || empty($_POST["term"]))
            {
                $this->output["messages"]["bad"][] = 306;     
            }
            else
            {
                $date = $_POST["year"]."-".$_POST["month"]."-".$_POST["day"];
                $DB->SetTable($this->db_prefix."marks");
                $DB->AddValue("student_id",$_POST["student_id"]);
                $DB->AddValue("subject_id",$_POST["subject"]);
                $DB->AddValue("mark",$_POST["mark"]);
                $DB->AddValue("term", $_POST["term"]);
                $DB->AddValue("date", $date);
                $DB->Insert();
                $DB->SetTable($this->db_prefix."marks");
                $DB->AddCondFS("student_id","=",$mode[1]);
                $DB->AddOrder("term");
                $DB->AddOrder("date");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable($this->db_prefix."subjects");
                    $DB->AddCondFS("id","=",$row["subject_id"]);
                    $res2 = $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2))
                    {
                        $row["subject"] = $row2["name"];
                    }
                    $this->output["marks"][]=$row;
                }
                $DB->SetTable($this->db_prefix."people");
                $DB->AddCondFS("id","=", $mode[1]);
                $res2 = $DB->Select();
                if($row2 = $DB->FetchAssoc($res2))
                {
                    $this->output["student_last_name"] = $row2["last_name"];
                    $this->output["student_name"] = $row2["name"];
                    $this->output["student_patronymic"] = $row2["patronymic"];
                    $this->output["student_id"] = $mode[1];
                }
                $res2 = $DB->Exec("select max(`term`) from `nsau_marks` where `student_id`=".$mode[1]);
                if($row2 = $DB->FetchAssoc($res2))
                {
                    $this->output["max_term"] = $row2;
                }
                $DB->SetTable($this->db_prefix."subjects");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {   
                    $this->output["subjects"][] = $row;
                }
            }
        }
        else
        if(!empty($mode[3]))
        {
            if($mode[3] == "delete")
            {
                $DB->SetTable($this->db_prefix."marks");
                $DB->AddCondFS("id","=",$mode[4]);
                $DB->Delete();
                CF::Redirect($Engine->engine_uri.$mode[0]."/".$mode[1]."/".$mode[2]."/");
            }
            if($mode[3] == "edit")
            {
                if(isset($_POST["mark_id"]))
                {
                    if(empty($_POST["day"]) || empty($_POST["month"]) || empty($_POST["year"]) || empty($_POST["subject"]) || empty($_POST["mark"]) || empty($_POST["term"]))
                    {
                        $this->output["messages"]["bad"][] = 306;     
                    }
                    else
                    {
                        $date = $_POST["year"]."-".$_POST["month"]."-".$_POST["day"];
                        $DB->SetTable($this->db_prefix."marks");
                        $DB->AddCondFS("id","=",$_POST["mark_id"]);
                        $DB->AddValue("subject_id",$_POST["subject"]);
                        $DB->AddValue("mark",$_POST["mark"]);
                        $DB->AddValue("term", $_POST["term"]);
                        $DB->AddValue("date", $date);
                        $DB->Update();
                        CF::Redirect($Engine->engine_uri.$mode[0]."/".$mode[1]."/".$mode[2]."/");
                        unset($mode[3]);
                        unset($mode[4]);
                    }    
                }
                else
                {
                    $DB->SetTable($this->db_prefix."marks");
                    $DB->AddCondFS("id","=",$mode[4]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        $DB->SetTable($this->db_prefix."subjects");
                        $DB->AddCondFS("id","=",$row["subject_id"]);
                        $res2 = $DB->Select();
                        if($row2 = $DB->FetchAssoc($res2))
                        {
                            $row["subject_name"] = $row2["name"];
                        }
                        $date = explode("-",$row["date"]);
                        $row["year"] = $date[0];
                        $row["month"] = $date[1];
                        $row["day"] = $date[2];
                        $this->output["edit_mark"]=$row;
                    }
                    $DB->SetTable($this->db_prefix."subjects");
                    $res = $DB->Select();
                    while($row = $DB->FetchAssoc($res))
                    {   
                        $this->output["subjects"][] = $row;
                    }           
                }
            } 
        }
        else
        {
            $this->output["marks"]=array();
            $DB->SetTable($this->db_prefix."marks");
            $DB->AddCondFS("student_id","=",$mode[1]);
            $DB->AddOrder("term");
            $DB->AddOrder("date");
            $res = $DB->Select();
            while($row = $DB->FetchAssoc($res))
            {
                $DB->SetTable($this->db_prefix."subjects");
                $DB->AddCondFS("id","=",$row["subject_id"]);
                $res2 = $DB->Select();
                if($row2 = $DB->FetchAssoc($res2))
                {
                    $row["subject"] = $row2["name"];
                }
                $this->output["marks"][]=$row;
            }
            $DB->SetTable($this->db_prefix."people");
            $DB->AddCondFS("id","=", $mode[1]);
            $res2 = $DB->Select();
            if($row2 = $DB->FetchAssoc($res2))
            {
                $this->output["student_last_name"] = $row2["last_name"];
                $this->output["student_name"] = $row2["name"];
                $this->output["student_patronymic"] = $row2["patronymic"];
                $this->output["student_id"] = $mode[1];
            }
            $res2 = $DB->Exec("select max(`term`) from `nsau_marks` where `student_id`=".$mode[1]);
            if($row2 = $DB->FetchAssoc($res2))
            {
                $this->output["max_term"] = $row2;
            }
            $DB->SetTable($this->db_prefix."subjects");
            $res = $DB->Select();
            while($row = $DB->FetchAssoc($res))
            {   
                $this->output["subjects"][] = $row;
            }
        }                   
    }
    
    
    
    //формирует список студентов, числящихся в группе $mode[0]
    function students_list($mode)
    {
        global $DB, $Auth;
        if(isset($_POST["last_name"]))
        {
            if(!isset($_POST["password1"]) || !isset($_POST["password2"]) || $_POST["password1"] != $_POST["password2"])
            {
                $this->output["messages"]["bad"][] = 301;    
            }
            else
            {	//die(print_r($_POST));
                $DB->SetTable("auth_users");
                $DB->AddValue("is_active", 1);
                $DB->AddValue("username", $_POST["username"]);
                $DB->AddValue("password", md5($_POST["password1"]));
                $DB->AddValue("displayed_name", $_POST["last_name"]." ".$_POST["first_name"]." ".$_POST["patronymic"]);
                $DB->AddValue("email", $_POST["email"]);
                $DB->AddValue("usergroup_id", 3);
                $DB->AddValue("create_time","NOW()", "X");
                $DB->AddValue("create_user_id", $Auth->user_id);
                $DB->Insert();
                $res = $DB->Exec("SELECT max(LAST_INSERT_ID(id)) FROM auth_users limit 1");
                if($row = $DB->FetchAssoc($res))
                {
                    foreach($row as $row2)
                    {
                        $DB->SetTable($this->db_prefix."people");
                        $DB->AddValue("user_id",$row2);
                        $DB->AddValue("last_name", $_POST["last_name"]);
                        $DB->AddValue("name", $_POST["first_name"]);
                        $DB->AddValue("patronymic", $_POST["patronymic"]);
                        $DB->AddValue("male", $_POST["male"]);
                        $DB->AddValue("status_id", 8);
                        $DB->AddValue("year", $_POST["year"]);
                        $DB->AddValue("id_group", $_POST["group_id"]);
                        if(isset($_POST["subgroup"]))
                            $DB->AddValue("subgroup", $_POST["subgroup"]);
                        //$DB->AddValue("id_speciality", $_POST["id_speciality"]);
                        if($DB->Insert())
                        {
                            $this->output["messages"]["good"][] = 101;
                        }
                        else
                        {
                            $this->output["messages"]["bad"][] = 301;
                        }
                        
                    }    
                }
            }
        }
        $DB->SetTable($this->db_prefix."people");
        $DB->AddCondFS("id_group", "=", $mode[0]);
        $DB->AddCondFS("status_id", "!=", "4");
        $DB->AddOrder("last_name");
        $DB->AddOrder("name");
        $DB->AddOrder("patronymic");
        $res = $DB->Select();
        while($row = $DB->FetchAssoc($res))
        {
            $DB->SetTable("auth_users");
            $DB->AddCondFS("id", "=", $row["user_id"]);
            $res2 = $DB->Select();
            if($row2 = $DB->FetchAssoc($res2))
            {
                $row["username"] = $row2["username"];
                $row["displayed_name"] = $row2["displayed_name"];
                $row["email"] = $row2["email"];
            }
            else
            {
                $row["username"] = 0;
                $row["displayed_name"] = 0;
                $row["email"] = 0;    
            }
            $this->output["students_list"][] = $row;
        }
        $DB->SetTable($this->db_prefix."groups");    
        $DB->AddCondFS("id", "=", $mode[0]);
        $res = $DB->Select();
        if($row = $DB->FetchAssoc($res))
        {
            $DB->SetTable($this->db_prefix."specialities");
            $DB->AddCondFS("id_faculty", "=", $row["id_faculty"]);
            $res3 = $DB->Select();
            while($row3 = $DB->FetchAssoc($res3))
            {
                $this->output["specialities"][] = $row3;
            }
            $this->output["group_name"] = $row["name"];
            $this->output["year"] = $row["year"];
            $this->output["id_faculty"]= $row["id_faculty"];
            $DB->SetTable($this->db_prefix."faculties");    
            $DB->AddCondFS("id", "=", $row["id_faculty"]);
            $res2 = $DB->Select();
            if($row2 = $DB->FetchAssoc($res2))
            {
                $this->output["faculty_name"]= $row2["name"];    
            } 
        }
        $this->output["group_id"] = $mode[0];
        $this->output["students_list_mode"] = "students_list_mode";
    }
    
    function timetable_show()
    {
        global $DB;
        $DB->SetTable($this->db_prefix."faculties");
        $DB->AddOrder("id");
        $res2 = $DB->Select();
        while($row2 = $DB->FetchAssoc($res2))
        {
            for($i = 1; $i<7; $i++)
            {
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddCondFS("id_faculty", "=", $row2["id"]);
                $DB->AddCondFS("year", "=", $i);
                $res = $DB->Select();
                while( $row = $DB->FetchAssoc($res))
                {   
                    $row2["groups"]["$i"][] = $row;
                }
            }       
            $this->output["faculties"][] = $row2;
        }
    }
    
    
    function speciality_directory()
    {
        global $DB, $Auth, $Engine;
        
                   
            if(!empty($_GET["code"]) && $_GET["action"]=="delete")
            {
                if(!empty($_GET["section"]) && $_GET["section"]=="1")
                {
                    $DB->SetTable($this->db_prefix."specialities");
                    $DB->AddCondFS("code","=", $_GET["code"]);
                    if($DB->Delete())
                    {
                        $DB->SetTable($this->db_prefix."spec_type");
                        $DB->AddCondFS("code","=", $_GET["code"]);
                        if($DB->Delete())
                            $this->output["messages"]["good"][] = 105;
                        else
                            $this->output["messages"]["bad"][] = 310;
                    }
                    else
                    {
                        $this->output["messages"]["bad"][] = 310;
                    }
                    
                    $DB->SetTable($this->db_prefix."spec_type");
                    $DB->AddCondFS("code","=", $_GET["code"]);
                    $DB->Delete();
                }
                elseif(!empty($_GET["subsection"]) && $_GET["subsection"]=="1")
                {
                    $DB->SetTable($this->db_prefix."spec_type");
                    $DB->AddCondFS("code","=", $_GET["code"]);
                    $DB->AddCondFS("type","=", $_GET["type"]);
                    $DB->AddCondFS("year_open","=", $_GET["year_open"]); 
                    if($DB->Delete())
                    {
                        $this->output["messages"]["good"][] = 104;
                    }
                    else
                    {
                        $this->output["messages"]["bad"][] = 309;
                    }
                } 
            } 
     
            
           
         /*  $DB->SetTable($this->db_prefix."specialities", "st");
           // $DB->AddTable($this->db_prefix."faculties","f");
            $DB->AddField("st.code");
            $DB->AddField("st.name");
            $DB->AddField("st.id_faculty");
          //  $DB->AddField("f.id");
           // $DB->AddField("f.name");
            $DB->AddField("st.direction");
            $DB->AddField("st.description");
            $DB->AddOrder("st.code");  
            //$DB->AddCondFF("st.id_faculty","=", "f.id");
            $res = $DB->Select();
            */
            $res = $DB->Exec("SELECT st.code, st.name, st.id_faculty, st.direction, st.description, f.id, f.name as fname FROM ".$this->db_prefix."specialities as st left join ".$this->db_prefix."faculties as f on st.id_faculty = f.id order by st.code"); 
            
            $this->output["speciality_directory"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
				if ($Engine->OperationAllowed(5, "specialities.handle", (int)$row["code"], $Auth->usergroup_id))
					$row["handle"] = 1;
				else
					$row["handle"] = 0;
                $this->output["speciality_directory"][] = $row;
            }
            
            $DB->SetTable($this->db_prefix."spec_type");
            $DB->AddField("code");
            $DB->AddField("year_open");
            $DB->AddField("type");
            $DB->AddOrder("code");
            $res = $DB->Select(); 
            $this->output["speciality"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
                $this->output["speciality"][] = $row;
            }
            /*if (!count($this->output["speciality"]))
				$Engine->HTTP404();*/
            
            $DB->SetTable($this->db_prefix."school","s");
            $res = $DB->Select(); 
            $this->output["directory"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
                $this->output["directory"][] = $row;
            }
			
			/*$DB->SetTable("engine_privileges");
			$DB->AddCondFS("operation_name", "=", "specialities.handle");
			$DB->AddCondFS("usergroup_id", "=", $Auth->usergroup_id);
			
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res))
				$this->output["specs_to_handle"][] = $row["entry_id"];*/
    }
    
     
    function speciality($module_uri)
    {
        global $DB, $Engine, $Auth;
        
		if (!empty($_POST["action_sub"]) && $_POST["action_sub"]=="add_subsection") {
            /*$DB->SetTable($this->db_prefix."spec_type");
			$DB->AddCondFS("code", "=", $_POST["code"]);
			$DB->AddCondFS("type", "=", $_POST["type_sub"]);
			$res =  = $DB->Select();
			if($row = $DB->FetchAssoc($res)) {
				echo "error";
			} else {
				echo "ok";
			}*/
            $DB->SetTable($this->db_prefix."spec_type");
            $DB->AddValue("code", $_POST["code"]);
            $DB->AddValue("type", $_POST["type_sub"]); 
            $DB->AddValue("year_open", $_POST["year_open_sub"]);

            if(!empty($_POST["internal_tuition_sub"]) && $_POST["internal_tuition_sub"]=="on")
                $DB->AddValue("internal_tuition", 1);
            else
                $DB->AddValue("internal_tuition", 0);
                    
            if(!empty($_POST["correspondence_tuition_sub"]) && $_POST["correspondence_tuition_sub"]=="on")
                $DB->AddValue("correspondence_tuition", 1);
            else
                $DB->AddValue("correspondence_tuition", 0);
            if($DB->Insert()) {
                $this->output["messages"]["good"][] = 102;
				if($_POST["type_sub"] == "secondary" && !empty($_POST["data_training"])) {
					$DB->SetTable($this->db_prefix."specialities", "s");
					$DB->AddCondFS("s.code", "=", $_POST["code"]);
					$DB->AddValue("data_training", $_POST["data_training"]);
				}
            } else {
                $this->output["messages"]["bad"][] = 307;
            }
        } 
        
		if (!empty($_POST["code_add"]) && $_POST["action_op"]=="add") {
            $DB->SetTable($this->db_prefix."specialities");
            $DB->AddValue("id_faculty", 0);
            $DB->AddValue("code", $_POST["code_add"]); 
            $DB->AddValue("name", $_POST["speciality_name"]);
            $DB->AddValue("direction", $_POST["direction"]);
            $DB->AddValue("description", $_POST["description"]);
            $DB->AddValue("year_open", $_POST["year_open"]);
            /*if(empty($_POST["internal_tuition"]))
                $DB->AddValue("internal_tuition", 0);
            else
                $DB->AddValue("internal_tuition", $_POST["internal_tuition"]);
                
            if(empty($_POST["correspondence_tuition"]))
                $DB->AddValue("correspondence_tuition", 0);
            else
				$DB->AddValue("correspondence_tuition", $_POST["correspondence_tuition"]);*/
                
            if(!empty($_POST["data_training"]) && $_POST["type"] == "secondary")
                $DB->AddValue("data_training", $_POST["data_training"]);
				
            /*$DB->AddValue("higher", 0);
            $DB->AddValue("secondary", 0);
            $DB->AddValue("bachelor", 0);
            $DB->AddValue("magistracy", 0); */
            if($DB->Insert(true, true)) {echo "asdf";
                $DB->SetTable($this->db_prefix."spec_type");
                $DB->AddValue("code", $_POST["code_add"]);
                $DB->AddValue("type", $_POST["type"]); 
                $DB->AddValue("year_open", $_POST["year_open"]);
                    
                if(empty($_POST["internal_tuition"]))
                    $DB->AddValue("internal_tuition", 0);
                else
                    $DB->AddValue("internal_tuition", $_POST["internal_tuition"]);
                        
                if(empty($_POST["correspondence_tuition"]))
                    $DB->AddValue("correspondence_tuition", 0);
                else
                    $DB->AddValue("correspondence_tuition", $_POST["correspondence_tuition"]);
                    
                if($DB->Insert()) {
                    $this->output["messages"]["good"][] = 103;
                } else {
                    $this->output["messages"]["bad"][] = 311;
                }
            $DB->SetTable($this->db_prefix."specialities", "s");
            $DB->AddField("s.code");
            $DB->AddField("s.name");
            $DB->AddCondFS("s.code", "=", $_POST["code_add"]);
            $res = $DB->Select();
            $this->output["specialities"] = array();
            while($row = $DB->FetchAssoc($res)) {
                $this->output["specialities"][] = $row;
            }   
            } else {
                $this->output["messages"]["bad"][] = 308;
            }
        }
        
        $DB->SetTable($this->db_prefix."school","s");
        $res = $DB->Select(); 
        $this->output["directory"] = array();
        while ($row = $DB->FetchAssoc($res)) {
            $this->output["directory"][] = $row;
        }
   
        if (!empty($module_uri)) {
            $parts = explode ("/", $module_uri); // код специальности
            
            if(!empty($parts[1])) {
				if (!empty($_POST[$this->node_id]["save"]["cancel"])) {   
				    1;
				} else {
				    if (!empty($_POST["code"])) {
						
				        $DB->SetTable($this->db_prefix."specialities", "s");
				        $DB->AddCondFS("s.code", "=", $parts[0]);
				        $DB->AddValue("s.code", $_POST["code"]);
				        $DB->AddValue("s.name", $_POST["speciality_name"]);
				        if (isset($_POST["faculty"])) $DB->AddValue("s.id_faculty", $_POST["faculty"]);
				        $DB->AddValue("s.direction", $_POST["direction"]);
				        $DB->AddValue("s.description", $_POST["description"]);
						
				        if($DB->Update()) {
				            foreach ($_POST as $edit_type ) {     
				                if (is_array($edit_type)) {
									$DB->SetTable($this->db_prefix."spec_type", "st");
				                    $DB->AddCondFS("st.code", "=", $parts[1]);
				                    $DB->AddCondFS("st.type", "=", $edit_type["st_type"]);  
				                    $DB->AddCondFS("st.year_open", "=", $edit_type["st_year"]);
				                    $DB->AddValue("st.code", $_POST["code"]);
				                    $DB->AddValue("st.type", $edit_type["type"]);
									$DB->AddValue("st.data_training", $edit_type["data_training"]);
									$DB->AddValue("st.description", $edit_type["description"]);

				                    $DB->AddValue("st.year_open", $edit_type["year_open"]);
				                    if (isset($edit_type["internal_tuition"]) && $edit_type["internal_tuition"]=='on')
										$DB->AddValue("st.internal_tuition", 1);
				                    else
										$DB->AddValue("st.internal_tuition", 0);
				                    if(isset($edit_type["correspondence_tuition"]) && $edit_type["correspondence_tuition"]=='on')
										$DB->AddValue("st.correspondence_tuition", 1);
				                    else
										$DB->AddValue("st.correspondence_tuition", 0);
										/*
				                    if(isset($edit_type["data_training"]) && $edit_type["type"]=='secondary')
										$DB->AddValue("s.data_training", $edit_type["data_training"]);
									else
										$DB->AddValue("s.data_training", null);
										*/
				                    if($DB->Update()) {
				                        1;
				                    } else {
				                        $this->output["messages"]["bad"][] = 312;
				                    }
				                }   
				            }
				            $this->output["messages"]["good"][] = 106;
				        } else {
				            $this->output["messages"]["bad"][] = 312;
				        }
				    }
				}
				
				$DB->SetTable($this->db_prefix."specialities", "s");
				$DB->AddField("s.code");
				$DB->AddField("s.name");
				$DB->AddField("s.direction");
				$DB->AddField("s.description");
				//$DB->AddField("s.data_training");
				$DB->AddCondFS("s.code", "=", $parts[1]);
				$res=$DB->Select();
				$this->output["speciality"]=array();
				while ($row=$DB->FetchAssoc($res)) {
				    $this->output["speciality"][]=$row;
					
					/*if($parts[0] == "spo") {
						if($row["data_training"] != null) {
							$this->output["type_klass"] = "Специалист";
							$this->output["data_training"] = $row["data_training"];
						} else {
							$this->output["type_klass"] = "Специалист";
							$this->output["data_training"] = "неизвестен";
						}
					} elseif($parts[0] == "mag") {
						$this->output["type_klass"] = "Магистр";
						$this->output["data_training"] = "5 лет";
					} elseif($parts[0] == "bach") {
						$this->output["type_klass"] = "Бакалавр";
						$this->output["data_training"] = "4 года";
					} elseif($parts[0] == "vpo") {
						$this->output["type_klass"] = "Специалист";
						$this->output["data_training"] = "5 лет";
					} else {
						$this->output["type_klass"] = "неизвестен";
						$this->output["data_training"] = "неизвестен";
					}*/
				}
				
				
				
				if (!count($this->output["speciality"]))
					$Engine->HTTP404();
				$DB->SetTable($this->db_prefix."spec_type","st");
				$DB->AddTable($this->db_prefix."specialities","spec");
				$DB->AddField("st.year_open");
				$DB->AddField("st.internal_tuition"); 
				$DB->AddField("st.correspondence_tuition");
				$DB->AddField("st.type"); 
				$DB->AddField("st.data_training"); 
				$DB->AddField("st.qualification"); 
				$DB->AddField("st.description", "tdescr"); 
				$DB->AddCondFF("st.code", "=", "spec.code");
				$DB->AddCondFS("st.code", "=", $parts[1]);
				
				$res=$DB->Select();
				
				$this->output["speciality_edit"]=array();
				
				while($row = $DB->FetchAssoc($res)) {
					/*if($row["type"] == "secondary") {
						if($row["data_training"] == null) {
							$row["data_training"] = "неизвестен";
						}
					} elseif($row["type"] == "magistracy") {
						$row["data_training"] = "5 лет";
					} elseif($row["type"] == "bachelor") {
						$row["data_training"] = "4 года";
					} elseif($row["type"] == "higher") {
						$row["data_training"] = "5 лет";
					} else {
						$row["data_training"] = "";
					}*/				 
				    $this->output["speciality_edit"][] = $row;
				} 
				//die(print_r($this->output["speciality_edit"]));
				$DB->SetTable($this->db_prefix."school");
				$res = $DB->Select(); 
				$this->output["direction"] = array();
				while ($row = $DB->FetchAssoc($res)) {
				    $this->output["direction"][] = $row;
				}
			} else {
				if (!empty($_POST[$this->node_id]["save"]["cancel"])) {  
				    1;
				} else {
					if (!empty($_POST["code"]))	{
					
						$DB->SetTable($this->db_prefix."specialities", "s");
						$DB->AddCondFS("s.code", "=", $parts[0]);
						$DB->AddValue("s.code", $_POST["code"]);
						if (isset($_POST["faculty"])) $DB->AddValue("s.id_faculty", $_POST["faculty"]);
						$DB->AddValue("s.name", $_POST["speciality_name"]);
						$DB->AddValue("s.direction", $_POST["direction"]);
						$DB->AddValue("s.description", $_POST["description"]);
						if($DB->Update()) {
							foreach ($_POST as $edit_type ) {
							
								if (is_array($edit_type)) {
									/*if($edit_type["st_type"]=="higher")
										die(print_r($edit_type));*/
									
									$DB->SetTable($this->db_prefix."spec_type", "st");
									$DB->AddCondFS("st.code", "=", $parts[0]);
									$DB->AddCondFS("st.type", "=", $edit_type["st_type"]);  
									$DB->AddCondFS("st.year_open", "=", $edit_type["st_year"]);
									$DB->AddValue("st.code", $_POST["code"]);
									$DB->AddValue("st.type", $edit_type["type"]);
									$DB->AddValue("st.data_training", $edit_type["data_training"]);
									$DB->AddValue("st.qualification", $edit_type["qualification"]);
									$DB->AddValue("st.description", $edit_type["description"]);
										  
									$DB->AddValue("st.year_open", $edit_type["year_open"]);
									if (isset($edit_type["internal_tuition"]) && $edit_type["internal_tuition"]=='on')
										$DB->AddValue("st.internal_tuition", 1);
									else
										$DB->AddValue("st.internal_tuition", 0);
									if(isset($edit_type["correspondence_tuition"]) && $edit_type["correspondence_tuition"]=='on')
										$DB->AddValue("st.correspondence_tuition", 1);
									else
										$DB->AddValue("st.correspondence_tuition", 0);
										/*
				                    if(isset($edit_type["data_training"]) && $edit_type["type"]=='secondary')
										$DB->AddValue("s.data_training", $edit_type["data_training"]);
									else
										$DB->AddValue("s.data_training", null);
										*/
									//die($DB->UpdateQuery());
									if($DB->Update()) {
										1;
									} else {
										$this->output["messages"]["bad"][] = 312;
									}	
								}
							}
									
									
									$DB->SetTable($this->db_prefix."exams");
									$res = $DB->Select();
											
									while ($row = $DB->FetchAssoc($res)) {
										$eid = "exam_".$row["id"];
										//die(print_r($_POST["exam_4"]));
										
										if (isset($_POST["secondary_exams"][$eid]) && $_POST["secondary_exams"][$eid] == "on") {
										//die($eid);
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											$res_se = $DB->Select();
											if (!$DB->FetchAssoc($res_se)) {
												$DB->SetTable($this->db_prefix."spec_exams");
												$DB->AddValue("speciality_code", $_POST["code"]);
												$DB->AddValue("exam_id", $row["id"]);
												$DB->AddValue("type", "secondary");
												$DB->Insert();
											}
										} else {
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											$DB->AddCondFS("type", "=", "secondary");
											$DB->Delete();
										}
										
										if (isset($_POST["bachelor_exams"][$eid]) && $_POST["bachelor_exams"][$eid] == "on") {
										//die($eid);
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											$res_se = $DB->Select();
											if (!$DB->FetchAssoc($res_se)) {
												$DB->SetTable($this->db_prefix."spec_exams");
												$DB->AddValue("speciality_code", $_POST["code"]);
												$DB->AddValue("exam_id", $row["id"]);
												$DB->AddValue("type", "bachelor");
												$DB->Insert();
											}
										} else {
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											$DB->AddCondFS("type", "=", "bachelor");
											$DB->Delete();
										}
										
										if (isset($_POST["magistracy_exams"][$eid]) && $_POST["magistracy_exams"][$eid] == "on") {
										//die($eid);
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											
											$res_se = $DB->Select();
											if (!$DB->FetchAssoc($res_se)) {
												$DB->SetTable($this->db_prefix."spec_exams");
												$DB->AddValue("speciality_code", $_POST["code"]);
												$DB->AddValue("exam_id", $row["id"]);
												$DB->AddValue("type", "magistracy");
												$DB->Insert();
											}
										} else {
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											$DB->AddCondFS("type", "=", "magistracy");
											$DB->Delete();
										}
										
										if (isset($_POST["higher_exams"][$eid]) && $_POST["higher_exams"][$eid] == "on") {
										//die($eid);
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											$res_se = $DB->Select();
											if (!$DB->FetchAssoc($res_se)) {
												$DB->SetTable($this->db_prefix."spec_exams");
												$DB->AddValue("speciality_code", $_POST["code"]);
												$DB->AddValue("exam_id", $row["id"]);
												$DB->AddValue("type", "higher");
												$DB->Insert();
											}
										} else {
											$DB->SetTable($this->db_prefix."spec_exams");
											$DB->AddCondFS("speciality_code", "=", $_POST["code"]);
											$DB->AddCondFS("exam_id", "=", $row["id"]);
											$DB->AddCondFS("type", "=", "higher");
											$DB->Delete();
										}
										
										$DB->FreeRes();
									}
							$this->output["messages"]["good"][] = 106;
						} else {
							$this->output["messages"]["bad"][] = 312;
						}
					}
				}
				
				$DB->SetTable($this->db_prefix."faculties", "f");
				$DB->AddField("f.id");
				$DB->AddField("f.name");
				$res=$DB->Select();
				while ($row=$DB->FetchAssoc($res)) {
					$this->output["faculty"][]=$row;
				}
				
				$DB->SetTable($this->db_prefix."specialities", "s");
				$DB->AddField("id_faculty");
				$DB->AddField("s.code");
				$DB->AddField("s.name");
				$DB->AddField("s.direction");
				$DB->AddField("s.description");
				$DB->AddCondFS("s.code", "=", $parts[0]);
				$res=$DB->Select();
				$this->output["speciality"]=array();
				while ($row=$DB->FetchAssoc($res)) {
					$this->output["speciality"][]=$row;
					$code = $row["code"];
				}
				if (!count($this->output["speciality"]))
					$Engine->HTTP404();
				$DB->SetTable($this->db_prefix."spec_type","st");
				$DB->AddTable($this->db_prefix."specialities","spec");
				$DB->AddField("st.year_open");
				$DB->AddField("st.internal_tuition"); 
				$DB->AddField("st.correspondence_tuition");
				$DB->AddField("st.type"); 
				$DB->AddField("st.data_training");
				$DB->AddField("st.qualification"); 
				$DB->AddField("st.description"); 
				
				$DB->AddCondFF("st.code", "=", "spec.code");
				$DB->AddCondFS("st.code", "=", $parts[0]);
				$res=$DB->Select();
				$this->output["speciality_edit"]=array();
				while ($row=$DB->FetchAssoc($res)) {
					/*if($row["type"] == "secondary") {
						if($row["data_training"] == null) {
							$row["data_training"] = "неизвестен";
						}
					} elseif($row["type"] == "magistracy") {
						$row["data_training"] = "5 лет";
					} elseif($row["type"] == "bachelor") {
						$row["data_training"] = "4 года";
					} elseif($row["type"] == "higher") {
						$row["data_training"] = "5 лет";
					} else {
						$row["data_training"] = "";
					}*/
					$this->output["speciality_edit"][]=$row;
				}	 
				
				$DB->SetTable($this->db_prefix."school");
				$res = $DB->Select(); 
				$this->output["direction"] = array();
				while ($row = $DB->FetchAssoc($res)) {
					$this->output["direction"][] = $row;
				}
				
				$DB->SetTable($this->db_prefix."exams");
				$res = $DB->Select();
				while($row = $DB->FetchAssoc($res))	{
					$DB->SetTable($this->db_prefix."spec_exams");
					$DB->AddCondFS("speciality_code", "=", $code);
					$DB->AddCondFS("exam_id", "=", $row["id"]);
					$res2 = $DB->Select();
					
					if ($DB->FetchAssoc($res2))
						$row["need"] = 1;
					else
						$row["need"] = 0;
							
					//if (!$row["required"])
						$this->output["exams"][] = $row;                   
				}
			}
		} else
            if(isset($_GET["name_search"])) {
				$parts = explode(" ", trim($_GET["name_search"]));
				$DB->SetTable($this->db_prefix."specialities", "s");
				foreach($parts as $part) {
					$DB->AddAltFS("s.name", "LIKE", "%".$part."%");
					$DB->AddAltFS("s.code", "LIKE", "%".$part."%");        
					$DB->AppendAlts();
				}	            
				$DB->AddOrder("s.code");
				$res = $DB->Select();
				$this->output["specialities"] = array();
				while($row = $DB->FetchAssoc($res)) {
					$this->output["specialities"][] = $row;
				}
			};
		if ($this->output['speciality'][0]['name'] != '')
			$Engine->AddFootstep("", $this->output['speciality'][0]['name']."  /");
        $this->output["privileges"]["specialities.handle"] = false;
        $this->output["privileges"]["specialities.handle"] = $Engine->OperationAllowed(5, "specialities.handle", -1, $Auth->usergroup_id);
    }
    
    function specialities($type)
    {
        global $DB, $Engine, $Auth;
        
            $DB->SetTable($this->db_prefix."spec_type","st");
            $DB->AddTable($this->db_prefix."specialities", "s");
            $DB->AddTable($this->db_prefix."school", "sch");
            $DB->AddField("st.code");
            $DB->AddField("s.name");
            $DB->AddField("s.direction");
            $DB->AddField("st.year_open");
			$DB->AddField("st.type");
            $DB->AddField("st.internal_tuition");
            $DB->AddField("st.correspondence_tuition");
            $DB->AddField("sch.name", "name_dir");
            $DB->AddOrder("s.direction");
            $DB->AddCondFS("st.type", "=", $type);
            $DB->AddCondFF("s.code","=","st.code");
            $DB->AddCondFF("s.direction","=","sch.code");

        if(isset($_GET["sort"]) && $_GET["sort"]=="name") 
        {
            $DB->AddOrder("s.name");
            $res = $DB->Select(); 
            $this->output["type"] = $type;       
            $this->output["specialities"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
                $this->output["specialities"][] = $row;
            }
        }
        if(isset($_GET["sort"]) && $_GET["sort"]=="year") 
        { 
            $DB->AddOrder("st.year_open");
            $res = $DB->Select(); 
            $this->output["type"] = $type;       
            $this->output["specialities"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
                $this->output["specialities"][] = $row;
            }
        }
        if(isset($_GET["sort"]) && $_GET["sort"]=="code") 
        { 
            $DB->AddOrder("st.code");
            $res = $DB->Select(); 
            $this->output["type"] = $type;       
            $this->output["specialities"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
                $this->output["specialities"][] = $row;
            }
        }

        if(isset($_GET["sort"]) && $_GET["sort"]=="correspondence_tuition") 
        { 
            $DB->AddCondFS("st.correspondence_tuition", "=", 1); 
            $DB->AddOrder("st.correspondence_tuition");
            $res = $DB->Select(); 
            $this->output["type"] = $type;
            $this->output["type_tuition"] = "correspondence_true";        
            $this->output["specialities"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
                $this->output["specialities"][] = $row;
            }
        }
        elseif(!isset($_GET["sort"]) || $_GET["sort"]=="all")
        {
            $DB->AddOrder("st.code");
            $res = $DB->Select(); 
            $this->output["type"] = $type;       
            $this->output["specialities"] = array();
            while ($row = $DB->FetchAssoc($res))
            {
                $this->output["specialities"][] = $row;
            }
        }
			
			
            $this->output["privileges"]["specialities.handle"] = false;
            $this->output["privileges"]["specialities.handle"] = $Engine->OperationAllowed(5, "specialities.handle", -1, $Auth->usergroup_id); 
    }
       
    function faculties()
    {
        global $DB;
        $DB->SetTable($this->db_prefix."faculties");
		$DB->AddCondFN("pid");
		$DB->AddOrder("pos");
        $res = $DB->Select();
        $this->output["faculties"] = array();
        while ($row = $DB->FetchAssoc($res))
        {
            $this->output["faculties"][] = $row;
        }
        
    }
    
    function people($module_uri)
    {
        global $DB, $Engine, $Auth;		
        if(!empty($module_uri))
        {
		
            $parts = explode ("/", $module_uri);
			
			if ($parts[0] == "edit") {
				if ($Engine->OperationAllowed($this->module_id, "people.handle", -1, $Auth->usergroup_id)) {
					$this->output["mode"] = "edit_people";
					$this->edit_people($this->module_uri);
				}
				else
					CF::Redirect("/people/".$parts[1]."/");
			}
			elseif ($parts[0] == "delete") {
				if ($Engine->OperationAllowed($this->module_id, "people.handle", -1, $Auth->usergroup_id)) {
					$this->output["mode"] = "delete_people";
					$this->delete_people($this->module_uri);
				}
				else
					CF::Redirect("/people/".$parts[1]."/");
			}
			elseif ($parts[0] == "add") {
				if ($Engine->OperationAllowed($this->module_id, "people.add", -1, $Auth->usergroup_id)) {
					$this->output["mode"] = "add_people";
					$this->add_people();
				}
				else
					CF::Redirect("/people/".$parts[1]."/");
			}
			else {
				$DB->SetTable($this->db_prefix."people", "p");
				$DB->AddTable($this->db_prefix."statuses", "s");
				$DB->AddTable($this->db_prefix."groups", "g");
				$DB->AddField("p.last_name");
				$DB->AddField("p.name");
				$DB->AddField("p.id");
				$DB->AddField("p.patronymic");
				$DB->AddField("p.id_group");
				$DB->AddField("p.photo");
				$DB->AddField("p.comment");
				$DB->AddField("p.year");
				$DB->AddField("p.prad");
				$DB->AddField("s.name", "status");
				$DB->AddCondFS("p.id", "=", $parts[0]);
				$DB->AddCondFF("p.status_id", "=", "s.id");
				$res = $DB->Select(1);
				
				if($row = $DB->FetchAssoc($res)) {
					if ($row["status"] == "Преподаватель" && $row["prad"]) {
						$row["status"] = $row["prad"];
						$row["prad"] = "";
					}
				
					$this->output["man"] = $row;
					$this->files_list($parts[0]);
				}
				else {
					CF::Redirect("/people/");
				}
				if(!empty($row["id_speciality"]))
				{
					$DB->SetTable($this->db_prefix."specialities");
					$DB->AddCondFS("code", "=", $row["id_speciality"]);
					if($res = $DB->Select(1))
						$this->output["man"]["speciality"] = $DB->FetchAssoc($res);
					
				}
				
				if ($row["status"] == "Абитуриент") {
					$DB->SetTable($this->db_prefix."spec_abit");
					$DB->AddCondFS("people_id", "=", $parts[0]);
					$DB->AddCondFS("took", "=", 0);
					$DB->AddCondFS("out", "=", 0);
					$res = $DB->Select();
					
					while ($row1 = $DB->FetchAssoc($res)) {
						$DB->SetTable($this->db_prefix."specialities");
						$spec_code = explode(".", $row1["speciality_id"]);
						$DB->AddCondFS("code", "=", $spec_code[0]);
						$res_sp = $DB->Select();
						if ($row_sp = $DB->FetchAssoc($res_sp))
							$new_spec = $row_sp;
						else {
							$new_spec["code"] = $spec_code[0];
						}
						$new_spec["type_id"] = $row1["type_id"];
						$new_spec["ege"] = $row1["ege"];
						$new_spec["qualification"] = $row1["qualification"];
						$this->output["abit_specs"][] = $new_spec;
					}
				}
				
				if(!empty($row["id_group"]))
				{
					$DB->SetTable($this->db_prefix."groups");
					$DB->AddField("name");
					$DB->AddCondFS("id", "=", $row["id_group"]);
					if($res = $DB->Select(1))
					{
						$row2 = $DB->FetchAssoc($res);
						$this->output["man"]["group_name"] = $row2["name"];
					}
				}
				else
						$this->output["man"]["group_name"] = "";
						
					$DB->SetTable("nsau_people", "p");
					$DB->AddTable("nsau_teachers","t");
					$DB->AddTable("nsau_departments","d");
					//$DB->AddCondFS("p.user_id","=", $Auth->user_id);
					$DB->AddCondFS("p.id","=", $parts[0]); 
					$DB->AddCondFF("t.department_id", "=", "d.id");
					$DB->AddCondFF("t.people_id", "=", "p.id");
					$DB->AddField("d.name","name");
					$DB->AddField("d.url","url");
					$DB->AddField("d.id","id");
					
					$res = $DB->Select();
					while($row = $DB->FetchObject($res))
						if(isset($row->name)) {
							$this->output["displayed_departments"][$row->id]["name"] = $row->name;
							$this->output["displayed_departments"][$row->id]["url"] = $row->url;
						}
					//die(print_r($this->output["displayed_departments"]));
				if ($Engine->OperationAllowed($this->module_id, "people.handle", -1, $Auth->usergroup_id))
					$this->output["allow_peopledit"] = 1;
				else
					$this->output["allow_peopledit"] = 0;
				
				$Engine->AddFootstep("/people/".$parts[0]."/", $row["last_name"]." ".$row["name"]." ".$row["patronymic"], '', false);
            }
        }
        else        
            if(isset($_GET["person_name"]) && trim($_GET["person_name"]) != "")
            {
                $parts = explode(" ", trim($_GET["person_name"]));
                $DB->SetTable($this->db_prefix."people");
                foreach($parts as $part)
                {
                    $DB->AddAltFS("name", "LIKE", "%".$part."%");
                    $DB->AddAltFS("last_name", "LIKE", "%".$part."%");        
                    $DB->AddAltFS("patronymic", "LIKE", "%".$part."%");
                    $DB->AppendAlts();
                }            
                $DB->AddOrder("last_name");
                $res = $DB->Select();
                $this->output["people"] = array();
                while($row = $DB->FetchAssoc($res))
                {
                    $this->output["people"][] = $row;
                }
				
				if (count($this->output["people"]) == 1)
					CF::Redirect("/people/".$this->output["people"][0]["id"]."/");
				elseif (!count($this->output["people"]))
					$this->output["messages"]["bad"][] = 315;
            } else {
				$this->output["messages"]["bad"][] = 315;
			};
			$this->output["allow_addman"] = $Engine->OperationAllowed($this->module_id, "people.add", -1, $Auth->usergroup_id);
    }
	
	function edit_people ($module_uri) {
		global $DB, $Engine;

		$parts = explode ("/", $module_uri);
		
		if (!isset($_POST["mode"])) {
			$DB->SetTable("nsau_people");
			$DB->AddCondFS("id", "=", $parts[1]);
			$res = $DB->Select();
			$man = $DB->FetchAssoc($res);
			
			if($man["status_id"] == 2) {
				$DB->SetTable($this->db_prefix."people");
                $DB->AddCondFS("id", "=", $parts[1]);
                $res = $DB->Select();
                if($man) {
                    $DB->SetTable($this->db_prefix."teachers");
                    $DB->AddCondFS("people_id", "=", $parts[1]);
                    $res2 = $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2))
                        $man["department1_id"] = $row2["department_id"];
                    else
                        $man["department1_id"] = 0;
                    if($row2 = $DB->FetchAssoc($res2))
                        $man["department2_id"] = $row2["department_id"];
                    else
                        $man["department2_id"] = 0;
                    if($man["user_id"]) {
                        $DB->SetTable("auth_users");
                        $DB->AddCondFS("id", "=", $man["user_id"]);
                        $res2 = $DB->Select();
                        if($row2 = $DB->FetchAssoc($res2)) {
                            $man["username"] = $row2["username"];
                            $man["email"] = $row2["email"];
                            $man["password"] = $row2["password"];
                        }
                    }
                    $this->output["edit_teacher"][]=$row;
                    if($Engine->OperationAllowed(5, "teachers.own.handle", -1, $Auth->usergroup_id) && !$Engine->OperationAllowed(5, "teachers.all.handle", -1, $Auth->usergroup_id)) {
						$DB->SetTable($this->db_prefix."people", "p");
                        $DB->AddTable($this->db_prefix."teachers", "t");
                        $DB->AddTable($this->db_prefix."departments", "d");
                        $DB->AddCondFS("p.user_id", "=", $Auth->user_id);
                        $DB->AddCondFF("t.people_id", "=", "p.id");
                        $DB->AddCondFF("t.department_id", "=", "d.id");
                        $DB->AddField("d.name", "department_name");
                        $DB->AddField("t.department_id", "department_id");
                        $res = $DB->Select();
                        if($row = $DB->FetchAssoc($res)) {
                            $this->output["current_department_id"]=$row["department_id"];
                            $this->output["current_department_name"]=$row["department_name"];   
                        }
                    }
                }
                $DB->SetTable($this->db_prefix."departments");
                $DB->AddOrder("faculty_id");
                $res =  $DB->Select();
                while($row = $DB->FetchAssoc($res)) {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("id", "=", $row["faculty_id"]);
                    $res2 =  $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2)) {
                        $row["faculty_name"]=$row2["name"];
                    }
                    $this->output["department"][] = $row;    
                }
			} else {			
				$DB->SetTable("nsau_statuses");
				$res = $DB->Select();
				while ($row = $DB->FetchAssoc($res))
					$this->output["statuses"][] = $row;
					
				$DB->SetTable("nsau_groups");
				$res = $DB->Select();
				while ($row = $DB->FetchAssoc($res))
					$this->output["groups"][] = $row;
					
				$DB->SetTable("nsau_types");
				$res = $DB->Select();
				while ($row = $DB->FetchAssoc($res))
					$this->output["specialities"][] = $row;
				
				$DB->SetTable("auth_usergroups");
				$res = $DB->Select();
				while ($row = $DB->FetchAssoc($res))
					$this->output["usergroups"][] = $row;
				
				$DB->SetTable("auth_users");
				$DB->AddOrder("displayed_name");
				$res = $DB->Select();
				while ($row = $DB->FetchAssoc($res))
					$this->output["users"][] = $row;
					
				$DB->SetTable("nsau_spec_abit");
				$DB->AddCondFS("people_id", "=", $parts[1]);
				$res = $DB->Select();
				$row = $DB->FetchAssoc($res);
				
				
				$this->output["type_id"] = $row["type_id"];
			}	
			$this->output["editperson"] = $man;		
			
			
			$Engine->AddFootstep("/people/".$parts[1]."/", $man["last_name"]." ".$man["name"]." ".$man["patronymic"]);
		} else {
			if($_POST["mode"] == "edit_people") {
				if (!$_POST["user"]) {
					$DB->SetTable("auth_users");
					$DB->AddValue("username", $_POST["add_login"]);
					$DB->AddValue("password", md5($_POST["add_pass"]));
					$DB->AddValue("displayed_name", $_POST["add_lastname"]." ".$_POST["add_name"]." ".$_POST["add_patronymic"]);
					$DB->AddValue("email", $_POST["add_email"]);
					$DB->AddValue("usergroup_id", $_POST["add_usergroup"]);
					$DB->AddValue("create_time", "NOW()", "X");
					$DB->AddValue("create_user_id", $Auth->user_id);
	
					$DB->Insert();
					
					$manid = $DB->LastInsertID();
				}
				elseif ($_POST["user"] == 1)
					$manid = $_POST["add_user"];
				else
					$manid = 0;
				
				$DB->SetTable("nsau_people");
				$DB->AddCondFS("id", "=", $parts[1]);
				$res = $DB->Select();
				$man = $DB->FetchAssoc($res);
			
				$DB->SetTable("nsau_people");
				$DB->AddCondFS("id", "=", $parts[1]);
				$DB->AddValue("name", $_POST["edit_name"]);
				$DB->AddValue("last_name", $_POST["edit_lastname"]);
				$DB->AddValue("patronymic", $_POST["edit_patronymic"]);
				$DB->AddValue("status_id", $_POST["edit_status"]);
				$DB->AddValue("male", $_POST["edit_male"]);
				$DB->AddValue("id_group", $_POST["edit_group"]);
				$DB->AddValue("subgroup", $_POST["edit_subgroup"]);
				$DB->AddValue("year", $_POST["edit_year"]);
				//$DB->AddValue("id_speciality", $_POST["edit_speciality"]);
				$DB->AddValue("comment", $_POST["edit_comment"]);
				$DB->AddValue("user_id", $manid);
				
				if (isset($_POST["delete_photo"]) && $_POST["delete_photo"] && $man["photo"]) {
					$DB->AddValue("photo", "");
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$man["photo"];
					if (file_exists($path))
						unlink ($path);
						
					$photo = explode(".", $man["photo"]);
					$photo_tn[0] = $photo[0]."_tn";
					$photo_tn[1] = $photo[1];
					$photo_tn = implode(".", $photo_tn);
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$photo_tn;
					if (file_exists($path))
						unlink ($path);
				}
				elseif ($_FILES["edit_photo"]["tmp_name"] && $FILE_DATA = $_FILES["edit_photo"]) {
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$man["photo"];
					if (file_exists($path))
						unlink ($path);
						
					$photo = explode(".", $man["photo"]);
					$photo_tn[0] = $photo[0]."_tn";
					$photo_tn[1] = $photo[1];
					
					$photo_tn = implode(".", $photo_tn);
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$photo_tn;
					if (file_exists($path))
						unlink ($path);
				
					if (is_array($result = $this->Imager->GrabUploadedFile($FILE_DATA)))
					{
						$this->status = false;
						$this->output["messages"] = array_merge($this->output["messages"], $result);
					}
					
					if (is_array($result = $this->Imager->CreateOutputFiles($parts[1])))
					{
						$this->status = false;
						$this->output["messages"] = array_merge($this->output["messages"], $result);
					}
					$DB->AddValue("photo", $this->Imager->base_filename.".".$this->Imager->ext);
					
				}
				//die("111");
				$DB->Update();
				
				$DB->SetTable("nsau_spec_abit");
				$DB->AddCondFS("people_id", "=", $parts[1]);
				$DB->AddValue("type_id", $_POST["edit_speciality"]);
				$DB->Update();
			} elseif($_POST["mode"] == "edit_teachers") {
				$DB->SetTable("nsau_people");
				$DB->AddCondFS("id", "=", $parts[1]);
				$res = $DB->Select();
				$man = $DB->FetchAssoc($res);
				
				$DB->SetTable($this->db_prefix."people");
                $DB->AddCondFS("id", "=", $parts[1]);
                $DB->AddValue("last_name", $_POST["last_name"]);
                $DB->AddValue("name", $_POST["first_name"]);
                $DB->AddValue("patronymic", $_POST["patronymic"]);
                $DB->AddValue("male", $_POST["male"]);
				$DB->AddValue("comment", $_POST["comment"]);
				$DB->AddValue("exp_teach", $_POST["exp_teach"]);
				$DB->AddValue("exp_full", $_POST["exp_full"]);
				$DB->AddValue("prad", $_POST["prad"]);
				
				if (isset($_POST["delete_photo"]) && $_POST["delete_photo"] && $man["photo"]) {
					$DB->AddValue("photo", "");
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$man["photo"];
					if (file_exists($path))
						unlink ($path);
						
					$photo = explode(".", $man["photo"]);
					$photo_tn[0] = $photo[0]."_tn";
					$photo_tn[1] = $photo[1];
					$photo_tn = implode(".", $photo_tn);
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$photo_tn;
					if (file_exists($path))
						unlink ($path);
				} elseif ($_FILES["edit_photo"]["tmp_name"] && $FILE_DATA = $_FILES["edit_photo"]) {
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$man["photo"];
					if (file_exists($path))
						unlink ($path);
						
					$photo = explode(".", $man["photo"]);
					$photo_tn[0] = $photo[0]."_tn";
					$photo_tn[1] = $photo[1];
					
					$photo_tn = implode(".", $photo_tn);
					$path = $_SERVER["DOCUMENT_ROOT"]."/images/peoples/".$photo_tn;
					if (file_exists($path))
						unlink ($path);
				
					if (is_array($result = $this->Imager->GrabUploadedFile($FILE_DATA))) {
						$this->status = false;
						$this->output["messages"] = array_merge($this->output["messages"], $result);
					}
					
					if (is_array($result = $this->Imager->CreateOutputFiles($parts[1]))) {
						$this->status = false;
						$this->output["messages"] = array_merge($this->output["messages"], $result);
					}
					$DB->AddValue("photo", $this->Imager->base_filename.".".$this->Imager->ext);					
				}				
                $DB->Update();
                    
                $DB->SetTable($this->db_prefix."people");
                $DB->AddCondFS("id", "=", $parts[1]);
                $res = $DB->Select();
                if($row = $DB->FetchAssoc($res)) {
                    $this->update_user($row, 2); 
                } else {
                    $this->insert_user($parts[1], 2);
                }
                    
                if(isset($_POST["department1_id"]) && $_POST["department1_id"]) {
                    $DB->SetTable($this->db_prefix."teachers");
                    $DB->AddCondFS("people_id", "=", $parts[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res)) {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddCondFS("id", "=", $row["id"]);
                        $DB->AddValue("department_id", $_POST["department1_id"]);
                        $DB->Update();
                    } else {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddValue("people_id", $parts[1]);
                        $DB->AddValue("department_id", $_POST["department1_id"]);
                        $DB->Insert();    
                    }    
                } else {
                    $DB->SetTable($this->db_prefix."teachers");
                    $DB->AddCondFS("people_id", "=", $parts[1]);
                    $DB->Delete();    
                }
                if(isset($_POST["department2_id"]) && $_POST["department2_id"]) {  
                    $DB->SetTable($this->db_prefix."teachers");
                    $DB->AddCondFS("people_id", "=", $parts[1]);
                    $DB->AddCondFS("department_id", "<>", $_POST["department1_id"]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res)) {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddCondFS("id", "=", $row["id"]);
                        $DB->AddValue("department_id", $_POST["department2_id"]);
                        $DB->Update();    
                    } else {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddValue("people_id", $parts[1]);
                        $DB->AddValue("department_id", $_POST["department2_id"]);
                        $DB->Insert();    
                    }
                } else {
                    $DB->SetTable($this->db_prefix."teachers");
                    $DB->AddCondFS("people_id", "=", $parts[1]);
                    $DB->AddCondFS("department_id", "<>", $_POST["department1_id"]);
                    $DB->Delete();    
                }
			}
			
			CF::Redirect("/people/".$parts[1]."/");
		}
	}
	
	function add_people()
	{
		global $DB, $Auth;
		
		if (!isset($_POST["add_lastname"])) {			
			$DB->SetTable("nsau_statuses");
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res))
				$this->output["statuses"][] = $row;
				
			$DB->SetTable("nsau_groups");
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res))
				$this->output["groups"][] = $row;

			$DB->SetTable("nsau_types");
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res))
				$this->output["specialities"][] = $row;
				
			$DB->SetTable("auth_usergroups");
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res))
				$this->output["usergroups"][] = $row;
			
			$DB->SetTable("auth_users");
			$DB->AddOrder("displayed_name");
			$res = $DB->Select();
			while ($row = $DB->FetchAssoc($res))
				$this->output["users"][] = $row;
			
			//$Engine->AddFootstep("/people/".$parts[1]."/", $man["last_name"]." ".$man["name"]." ".$man["patronymic"]);
		}
		else {
			if (!$_POST["user"]) {
				$DB->SetTable("auth_users");
				$DB->AddValue("username", $_POST["add_login"]);
				$DB->AddValue("password", md5($_POST["add_pass"]));
				$DB->AddValue("displayed_name", $_POST["add_lastname"]." ".$_POST["add_name"]." ".$_POST["add_patronymic"]);
				$DB->AddValue("email", $_POST["add_email"]);
				$DB->AddValue("usergroup_id", $_POST["add_usergroup"]);
				$DB->AddValue("create_time", "NOW()", "X");
                $DB->AddValue("create_user_id", $Auth->user_id);

				$DB->Insert();
				
				$manid = $DB->LastInsertID();
			}
			elseif ($_POST["user"] == 1)
				$manid = $_POST["add_user"];
			else
				$manid = 0;
			
			if (!$_POST["add_year"])
				$_POST["add_year"] = "0000";
			
			$DB->SetTable("nsau_people");
			$DB->AddValue("name", $_POST["add_name"]);
			$DB->AddValue("last_name", $_POST["add_lastname"]);
			$DB->AddValue("patronymic", $_POST["add_patronymic"]);
			$DB->AddValue("status_id", $_POST["add_status"]);
			$DB->AddValue("male", $_POST["add_male"]);
			$DB->AddValue("id_group", $_POST["add_group"]);
			$DB->AddValue("subgroup", $_POST["add_subgroup"]);
			$DB->AddValue("year", $_POST["add_year"]);
			//$DB->AddValue("id_speciality", $_POST["add_speciality"]);
			$DB->AddValue("comment", $_POST["add_comment"]);
			$DB->AddValue("user_id", $manid);
			$DB->Insert();
			
			$peopleid = $DB->LastInsertID();
			
			$spec = $_POST["add_speciality"];
				
			$DB->SetTable("nsau_types");
			$DB->AddCondFS("id", "=", $spec);
			$res = $DB->Select();
			$row = $DB->FetchAssoc($res);
			
			//die(print_r($row));
			
			$spec_parts = explode(".", $row["code"]);
			$speciality_id = $spec_parts[0];
			if (isset($spec_parts[1]))
				$qual = $spec_parts[1];
			else
				$qual = "";
			
			$DB->SetTable("nsau_spec_abit");
			$DB->AddValue("speciality_id", $speciality_id);
			$DB->AddValue("type_id", $spec);
			$DB->AddValue("qualification", $qual);
			$DB->AddValue("type", $row["type"]);
			$DB->AddValue("people_id", $peopleid);
			$DB->Insert();
			
			if ($_FILES["add_photo"]["tmp_name"] && $FILE_DATA = $_FILES["add_photo"]) {								
				if (is_array($result = $this->Imager->GrabUploadedFile($FILE_DATA)))
				{
					$this->status = false;
					$this->output["messages"] = array_merge($this->output["messages"], $result);
				}
				
				if (is_array($result = $this->Imager->CreateOutputFiles($peopleid)))
				{
					$this->status = false;
					$this->output["messages"] = array_merge($this->output["messages"], $result);
				}
				
				$DB->SetTable("nsau_people");
				$DB->AddCondFS("id", "=", $peopleid);
				$DB->AddValue("photo", $this->Imager->base_filename.".".$this->Imager->ext);
				$DB->Update();
			}
			
			
			CF::Redirect("/people/".$peopleid."/");
		}
	}
	
	function delete_people($module_uri)
	{
		global $DB, $Engine;

		$parts = explode ("/", $module_uri);
		
		$DB->SetTable($this->db_prefix."people");
		$DB->AddField("user_id");
		$DB->AddField("last_name");
		$DB->AddField("name");
		$DB->AddField("patronymic");
		$DB->AddField("photo");
		$DB->AddCondFS("id", "=", $parts[1]);
		$res = $DB->Select();
		$man = $DB->FetchAssoc($res);
		
		$this->output["people_id"] = $parts[1];
		
		if ($man["user_id"])
			$this->output["is_user"] = 1;
			
		if ($man["photo"]) {
			$thumb = explode(".", $man["photo"]);
			$thumb[0] .= "_tn";
			$thumb = implode(".", $thumb);
		}
		
		if (!isset($_GET["confirm"])) {
			if ($man) {
				$this->output["person_name"] = $man["last_name"]." ".$man["name"]." ".$man["patronymic"];
			}
		}
		
		elseif ($_GET["confirm"] == 1) {
			$DB->SetTable($this->db_prefix."people");
			$DB->AddCondFS("id", "=", $parts[1]);
			$DB->Delete();
			if ($man["photo"] && $thumb) {
				unlink('/images/'.$man["photo"]);
				unlink('/images/'.$thumb);
			}
			CF::Redirect("/people/");
		}
		elseif ($_GET["confirm"] == 2) {
			$DB->SetTable($this->db_prefix."people");
			$DB->AddCondFS("id", "=", $parts[1]);
			$DB->Delete();
			if ($man["photo"] && $thumb) {
				unlink('/images/'.$man["photo"]);
				unlink('/images/'.$thumb);
			}
			
			$DB->SetTable("auth_users");
			$DB->AddCondFS("id", "=", $man["user_id"]);
			$DB->Delete();
			CF::Redirect("/people/");
		}
		else {
			CF::Redirect("/people/".$parts[1]."/");
		}
	}
	
	function files_list($user_id)
	{
		global $DB;

		$DB->SetTable($this->db_prefix."people");
		$DB->AddField("user_id");
		$DB->AddCondFS("id", "=", $user_id);
		$res = $DB->Select();
		$row = $DB->FetchAssoc($res);
		$user_id = $row["user_id"];
		
		
		$DB->SetTable($this->db_prefix."files", "f");
		
		//$DB->AddJoin("engine_actions_log.entry_id", "f.id");
		//$DB->AddCondFS("engine_actions_log.entry_type", "=", "file");
		//$DB->AddCondFS("engine_actions_log.action", "=", "create");
		
		$DB->AddCondFS("f.user_id", "=", $user_id);
		$DB->AddCondFS("f.user_id", "!=", 0);
		$DB->AddField("f.id","fid");
        $DB->AddField("f.down_count","fcount");          
        $DB->AddField("f.name","fname");
		$DB->AddField("f.filename", "filename");
		$DB->AddField("f.descr", "fdescr");
		
		//$DB->AddField("engine_actions_log.time", "time");
		
     	if(isset($_GET["sort"])) {
			if($_GET["sort"] == "time")
				$DB->AddOrder("f.id", ($_GET["sort_desc"] == "false") ? false : true);
			if($_GET["sort"] == "name")
				$DB->AddOrder("f.".$_GET["sort"], ($_GET["sort_desc"] == "false") ? false : true);
			//if($_GET["sort"] == "subj")
			//	$DB->AddOrder("s.name", ($_GET["sort_desc"] == "false") ? false : true);
		}
		//echo $DB->SelectQuery();
		$file_list = null;
		$folder_list = null;
		$subj_list = null;
		$res1 = $DB->Select();
		//$DB->FreeRes();
		
		$DB->SetTable($this->db_prefix."files", "f");
        $DB->AddTable($this->db_prefix."files_subj", "fs");
        $DB->AddTable($this->db_prefix."subjects", "s");
		$DB->AddCondFS("f.user_id", "=", $user_id);
		$DB->AddCondFS("f.user_id", "!=", 0);
        $DB->AddCondFF("f.id","=","fs.file_id");
        $DB->AddCondFF("fs.subject_id","=","s.id");
        $DB->AddField("f.id","fid");   
        $DB->AddField("f.down_count","fcount");     
		$DB->AddField("s.id", "subject_id");  
		$DB->AddField("s.name", "subject_name");
		if(isset($_GET["sort"])) {
			if($_GET["sort"] == "subj") {
				$DB->AddOrder("s.name", ($_GET["sort_desc"] == "false") ? false : true);
			} else {
				$DB->AddOrder("s.id");
			}	
		} else {
			$DB->AddOrder("s.name");
		}
		
		$res2 = $DB->Select();
		//$DB->FreeRes();
		
		$DB->SetTable($this->db_prefix."files", "f");
        $DB->AddTable($this->db_prefix."files_folders", "fd");
		$DB->AddCondFS("f.user_id", "=", $user_id);
		$DB->AddCondFS("f.user_id", "!=", 0);
		$DB->AddCondFS("fd.user_id", "=", $user_id);
        $DB->AddCondFF("f.folder_id","=","fd.id");
        $DB->AddField("f.id","fid");
        $DB->AddField("f.down_count","fcount");
		$DB->AddField("fd.id", "fd_id");  
		$DB->AddField("fd.name", "fd_name");
		//die($DB->SelectQuery());
		if(isset($_GET["sort"])) {
			if($_GET["sort"] == "subj") {
				$DB->AddOrder("fd.name", ($_GET["sort_desc"] == "false") ? false : true);
			} else {
				$DB->AddOrder("fd.id");
			}	
		} else {
			$DB->AddOrder("fd.name");
		}		
		$res3 = $DB->Select();
		
		if(isset($_GET["sort"]) && $_GET["sort"] != "subj") {
			while($row2 = $DB->FetchAssoc($res2)) {
				$subj_list[$row2["fid"]][] = array(
							"subj_id" => $row2["subject_id"],
							"subj_name" => $row2["subject_name"]
						); 
				//$this->output["files_list"]
			}
			while($row1 = $DB->FetchAssoc($res1)) {
				$file_list[$row1["fid"]] = array(
							"file_name" => $row1["fname"],
							"file_n" => $row1["filename"],
							"file_discr" => $row1["fdescr"],
							"file_count" => $row1["fcount"],
							"time" => $row1["time"],
							"subj_list" => isset($subj_list[$row1["fid"]]) ? $subj_list[$row1["fid"]] : null
						); 
				//$this->output["files_list"]
			}			
			$this->output["files_subj_list"] = $file_list;
		} else {
			while($row1 = $DB->FetchAssoc($res1)) {
				$file_list[$row1["fid"]] = array(
							"file_id" => $row1["fid"],
							"file_name" => $row1["fname"],
							"file_n" => $row1["filename"],
							"file_discr" => $row1["fdescr"],
							"file_count" => $row1["fcount"],
							"time" => $row1["time"]
						); 
				//$this->output["files_list"]
			}
			$buf_array = array();
			$file_sub = null;
			while($row2 = $DB->FetchAssoc($res2)) {
				$file_sub[$row2["subject_id"]][] = $file_list[$row2["fid"]];
				$buf_array[$row2["fid"]] = $file_list[$row2["fid"]];
				unset($file_list[$row2["fid"]]);
				$subj_list[$row2["subject_id"]] = array(
							"subj_name" => $row2["subject_name"],
							"file_list" => $file_sub
						); 
				//$this->output["files_list"]
			}
			$file_sub = null;
			while($row3 = $DB->FetchAssoc($res3)) {
				$file_sub[$row3["fd_id"]][] = isset($file_list[$row3["fid"]]) ? $file_list[$row3["fid"]] : $buf_array[$row3["fid"]];				
				$buf_array[$row3["fid"]] = $file_list[$row3["fid"]];
				unset($file_list[$row3["fid"]]);
				$folder_list[$row3["fd_id"]] = array(
							"fd_name" => $row3["fd_name"],
							"file_list" => $file_sub
						); 
				//$this->output["files_list"]
			}
			//die(print_r($file_list));
			$this->output["files_subj_list"] = $subj_list;
			$this->output["files_folder_list"] = $folder_list;
			$this->output["files_nosubj_list"] = $file_list;
		}
		$DB->FreeRes();
		
	}
    
    function groups($module_uri)
    {
        global $DB, $Engine, $Auth;
        
        if(!empty($module_uri))
        {
            $parts = explode ("/", $module_uri);
			
            if($Engine->OperationAllowed($this->module_id, "timetable.handle", -1, $Auth->usergroup_id))
            {
                if(!empty($parts[1]))//значит есть в ссылке edit или delete => редактируем  или удаляем запись расписания группы
                {
                    if($parts[1] == "delete" && !empty($parts[2]))//удаление элемента с id = $parts[2]
                    {
                        $DB->SetTable($this->db_prefix."timetable");
                        $DB->AddCondFS("id","=",$parts[2]);
                        $DB->Delete();
                        CF::Redirect($Engine->engine_uri.$parts[0]."/");
                    }
                    
                    if($parts[1] == "edit")//редактирование элемента с id = $parts[2]
                    {
                        if(!isset($_POST["id"]))
                        {
                            $DB->SetTable($this->db_prefix."timetable");
                            $DB->AddCondFS("id","=",$parts[2]);
                            $res = $DB->Select();
                            $this->output["edit_tometable"] = array();
                            if($row = $DB->FetchAssoc($res))
                            {
                                $this->output["edit_timetable"] = $row;    
                            }
                        }
                        else
                        {
                            $DB->SetTable($this->db_prefix."timetable");
                            $DB->AddCondFS("id","=",$_POST["id"]);
                            $DB->AddValue("group_id", $parts[0]);
                            if($_POST["subgroup"])
                                $DB->AddValue("subgroup",$_POST["subgroup"]);
                            else
                                $DB->AddValue("subgroup","");
                            $DB->AddValue("num",$_POST["num"]);
                            $DB->AddValue("dow",$_POST["dow"]);
                            if(!$_POST["parity"])
                            {
                                $DB->AddValue("parity","both");
                            }                        
                            else
                            {
                                if($_POST["parity"] == 1)
                                {
                                    $DB->AddValue("parity","odd");    
                                }
                                else
                                {
                                    $DB->AddValue("parity","even");
                                }
                            }
                            $DB->AddValue("subject_id",$_POST["subject"]);
                            $DB->AddValue("auditorium_id",$_POST["auditorium"]);
                            $DB->Update();
                            CF::Redirect($Engine->engine_uri.$parts[0]."/");   
                        }
                    }
                    
                }
            }
            if(empty($parts[1]))
            {
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddCondFS("id", "=", $parts[0]); // id группы
                $res = $DB->Select(1);
                if($row = $DB->FetchAssoc($res))
                    $this->output["group"] = $row;
                if(!empty($row["id_faculty"]))
                {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("id", "=", $row["id_faculty"]);                
                    if($res = $DB->Select(1))
                        $this->output["group"]["faculty"] = $DB->FetchAssoc($res);                
                }
                if(isset($_POST["num"]))//добавление записи в расписание текущей группы с id = $parts[0]
                {
                    $DB->SetTable($this->db_prefix."timetable");
                    $DB->AddValue("group_id", $parts[0]);
                    if($_POST["subgroup"])
                        $DB->AddValue("subgroup",$_POST["subgroup"]);
                    else
                        $DB->AddValue("subgroup","");
                    $DB->AddValue("num",$_POST["num"]);
                    $DB->AddValue("dow",$_POST["dow"]);
                    if(!$_POST["parity"])
                    {
                        $DB->AddValue("parity","both");
                    }                        
                    else
                    {
                        if($_POST["parity"] == 1)
                        {
                            $DB->AddValue("parity","odd");    
                        }
                        else
                        {
                            $DB->AddValue("parity","even");
                        }
                    }
                    $DB->AddValue("subject_id",$_POST["subject"]);
                    $DB->AddValue("auditorium_id",$_POST["auditorium"]);
                    $DB->Insert();
                }
            }
            $this->timetable($parts[0]);
        }
        else        
            if(isset($_GET["group"]) && trim($_GET["group"]) != "")
            {                
                $DB->SetTable($this->db_prefix."groups", "g");
                $DB->AddTable($this->db_prefix."faculties", "f");
                $DB->AddField("g.id", "id");
                $DB->AddField("g.name", "name");         
                $DB->AddField("f.name", "faculty");
                $DB->AddCondFS("g.name", "LIKE", "%".$_GET["group"]."%");                
                $DB->AddCondFF("g.id_faculty", "=", "f.id");
                $res = $DB->Select();
                $this->output["groups"] = array();
                while($row = $DB->FetchAssoc($res))
                {
                    $this->output["groups"][] = $row;                    
                }                
            } else {
				$this->output["messages"]["bad"][] = 314;
			};
    }
    
    
    
    function subjects($module_uri)
    {
	
        global $DB;
        if(!empty($module_uri))
        {
		
            $parts = explode ("/", $module_uri);
            $DB->SetTable($this->db_prefix."subjects");
            $DB->AddCondFS("id", "=", $parts[0]); // id предмета
            $res = $DB->Select(1);
            if($row = $DB->FetchAssoc($res))
                $this->output["subject"]["name"] = $row["name"];
            
            //$this->timetable($parts[0]); // id группы
			
			$DB->SetTable("nsau_files_subj");
			$DB->AddField("file_id");
			$DB->AddCondFS("subject_id", "=", $parts[0]);
			$res_att = $DB->Select();
			
			$i = 0;
			while ($row_att = $DB->FetchAssoc($res_att)) {
				$DB->SetTable("nsau_files");
				$DB->AddField("id");
				$DB->AddField("name");
				$DB->AddField("filename");
				$DB->AddField("descr");
				$DB->AddField("author");
				$DB->AddField("year");
				$DB->AddField("volume");
				$DB->AddField("edition");
				$DB->AddField("place");
				$DB->AddField("user_id");
				$DB->AddCondFS("id", "=", $row_att["file_id"]);
				$res = $DB->Select();
				$row = $DB->FetchAssoc($res);
				$row["name"] .= ".".$row["filename"];
				
				
				$DB->SetTable("nsau_people");
				$DB->AddField("id");
				$DB->AddField("last_name");
				$DB->AddField("name");
				$DB->AddField("patronymic");
				$DB->AddCondFS("user_id", "=", $row["user_id"]);
				$res_user = $DB->Select();
				$row_user = $DB->FetchAssoc($res_user);
								
				if (sizeof($row_user) && isset($row_user["last_name"]))
					$row["user_name"] = $row_user["last_name"]." ".$row_user["name"]." ".$row_user["patronymic"];
				else {
					$DB->SetTable("auth_users");
					$DB->AddField("displayed_name");
					$DB->AddCondFS("id", "=", $row["user_id"]);
					$res_user = $DB->Select();
					$row_user = $DB->FetchAssoc($res_user);
					$row["user_name"] = $row_user["displayed_name"];
				}
				
				$row["user_id"] = $row_user["id"];
				$this->output["subject"]["files"][$i] = $row;
				$i++;
			}
                      
        }
        else        
		
            if(isset($_GET["subject_name"]))
            {                
                $DB->SetTable($this->db_prefix."subjects", "s");
                $DB->AddField("s.id", "id");
                $DB->AddField("s.name", "name");
                $DB->AddCondFS("s.name", "LIKE", "%".$_GET["subject_name"]."%");                
                $res = $DB->Select();
                $this->output["subjects"] = array();
                while($row = $DB->FetchAssoc($res))
                {
                    $this->output["subjects"][] = $row;                    
                }                
            };
    }
	
    
    
    function timetable($group_id = NULL)
    {
        global $DB, $Auth, $Engine;
        $this->output["now"] = $now = $now = getdate();
        $DB->SetTable($this->db_prefix."timetable", "t");
        $DB->AddTable($this->db_prefix."subjects", "s");
        $DB->AddTable($this->db_prefix."buildings", "b");
        $DB->AddTable($this->db_prefix."auditorium","a");
        $DB->AddField("t.num");
        $DB->AddField("t.dow");
        $DB->AddField("t.parity");
        $DB->AddField("t.id");
        $DB->AddField("t.subgroup");
        $DB->AddField("s.name", "subject");
        $DB->AddField("t.auditorium_id");
        $DB->AddField("a.name","auditorium");
        $DB->AddField("b.label");
        $DB->AddField("b.name", "building");
        $DB->AddCondFF("t.subject_id", "=", "s.id");
        $DB->AddCondFF("t.auditorium_id", "=", "a.id");
        $DB->AddCondFF("a.building_id","=","b.id");
        $DB->AddOrder("t.dow");
        $DB->AddOrder("t.num");
        if(!is_null($group_id))
            $DB->AddCondFS("t.group_id", "=", $group_id);
        $parity = $this->timetable_weekparity();
		$is_both = true;
		if(!isset($_GET['parity']) || $_GET['parity'] != "both") {
			$DB->AddAlt($DB->AddAltFS("t.parity", "=", "both"), $DB->AddAltFS("t.parity", "=", "$parity"));
			$is_both = false;
		}
		if(isset($_GET['subgr']) && $_GET['subgr'] != "all") {
			$DB->AppendAlts();
			$DB->AddAlt($DB->AddAltFS("t.subgroup", "=", ""), $DB->AddAltFS("t.subgroup", "=", ($_GET['subgr'] == "a") ? "а" : "б"));
		}
		$this->output["parity"] = $parity;
        $DB->AddOrder("t.num");
        $res = $DB->Select();
        $this->output["timetable"] = array();
        while($row = $DB->FetchAssoc($res)) {
			if($is_both) {
				if($row["parity"] == "odd" || $row["parity"] == "both") {
					$this->output["timetable"]["odd"][$row["dow"]][$row["num"]][] = $row;
				}
				if($row["parity"] == "even" || $row["parity"] == "both") {
					$this->output["timetable"]["even"][$row["dow"]][$row["num"]][] = $row;
				}
			} else {
				if($row["parity"] == $parity || $row["parity"] == "both") {
					$this->output["timetable"][$parity][$row["dow"]][$row["num"]][] = $row;
				}
			}     
        } 
        $DB->SetTable($this->db_prefix."buildings");
        $DB->AddOrder("id");
        $res = $DB->Select();
        $this->output["buildings"]=array();
        while($row = $DB->FetchAssoc($res))
        {
            $this->output["buildings"][] = $row;
        }
		
        if($Engine->OperationAllowed($this->module_id, "timetable.handle", -1, $Auth->usergroup_id))
        {
            $this->output["edit_acces"] = 1;
        }
        else
        {
            $this->output["edit_acces"] = 0;
        }
        $DB->SetTable($this->db_prefix."subjects");
        $res = $DB->Select();
        while($row = $DB->FetchAssoc($res))
        {
            $this->output["subjects"][] = $row;    
        }
        $DB->SetTable($this->db_prefix."auditorium","a");
        $DB->AddTable($this->db_prefix."buildings","b");
        $DB->AddField("a.id","id");
        $DB->AddField("a.name","name");
        $DB->AddField("b.name","building_name");
        $DB->AddField("b.id","building_id");
        $DB->AddCondFF("a.building_id","=","b.id");
        $DB->AddCondFS("a.is_using","=",0);
        $DB->AddOrder("a.building_id");
        $res = $DB->Select();
        while($row = $DB->FetchAssoc($res))
        {
            $this->output["auditorium"][] = $row;
        }
    }
    
	
	
	function students($group_id = NULL)
	{
		if(trim($group_id) == "")
			return;
		global $DB;
		$group_id = explode ("/", $group_id);
		$gid = $group_id[0];
		$DB->SetTable($this->db_prefix."people");
		$DB->AddField("id");
		$DB->AddField("last_name");
		$DB->AddField("name");
		$DB->AddField("patronymic");
        $DB->AddOrder("last_name");
        $DB->AddOrder("name");
        $DB->AddOrder("patronymic");
		$DB->AddCondFS("id_group", "=", $gid);
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res)){
			$this->output["students"][] = $row;
		}
	}
    
    
    
    function timetable_make()
    {
        
    }
    
    function timetable_groups($mode)						/*+++++++++++++++++++++++++++++++++++++*/
    {
        global $DB, $Engine;
        if(isset($_POST["mode"]))
            $mode[0] = $_POST["mode"];
        switch($mode[0])
        {
            case "edit":
            {
                if(!isset($_POST["id"]))
                {
                    $DB->SetTable($this->db_prefix."groups");
                    $DB->AddCondFS("id", "=", $mode[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res)) {
                        /*$DB->SetTable($this->db_prefix."faculties");
                        $DB->AddCondFS("id", "=", $row["id_faculty"]);
                        $res1 = $DB->Select();
                        if($row1 = $DB->FetchAssoc($res1))
                            $row["faculty_name"] = $row1["name"];
                        $DB->SetTable($this->db_prefix."faculties");
						$DB->AddOrder("name");
                        $res1 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res1)) {
                            $this->output["faculties"][]=$row2;
                        }
							
                        $DB->SetTable($this->db_prefix."specialities");
                        $DB->AddCondFS("id_faculty", "=", $row["id_faculty"]);
						$DB->AddOrder("name");
                        $res1 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res1)) {
                            $this->output["spec"][]=$row2;
							if(isset($row["specialities_code"]) && $row2["code"] == $row["specialities_code"])
								$row["spec_code"] = $row["specialities_code"];
                        }*/
						
						$DB->SetTable($this->db_prefix."faculties");
						$DB->AddCondFP("is_active");
						$DB->AddOrder("name");
						$res1 = $DB->Select();
						while($row1 = $DB->FetchAssoc($res1)) { 
							$DB->SetTable($this->db_prefix."specialities");
							$DB->AddCondFS("id_faculty", "=", $row1["id"]);
							$DB->AddOrder("name");
							$res2 = $DB->Select();
							while($row2 = $DB->FetchAssoc($res2)) {
								$this->output["faculties"][$row1["id"]]["fac_name"] = $row1["name"]; 
								$this->output["faculties"][$row1["id"]]["spec"][]=$row2;
								if(isset($row["specialities_code"]) && $row2["code"] == $row["specialities_code"])
									$row["spec_code"] = $row["specialities_code"];
							}
							if($row1["id"] == $row["id_faculty"])
								$row["faculty_name"] = $row1["name"];
						}
						
                        $this->output["edit_group"][]=$row;
                        $this->output["groups_mode"]="edit";
                    }
                }
                else
                {
                    $DB->SetTable($this->db_prefix."groups");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $DB->AddValue("name", $_POST["group_name"]);					
					$fac = explode("|", $_POST["faculty"]);
                    $DB->AddValue("id_faculty", $fac[0]);
                    $DB->AddValue("year", $_POST["year"]);
                    $DB->AddValue("specialities_code", $fac[1]);
                    $DB->AddValue("qualification", $_POST["qualif"]);
                    $DB->Update();
                    $mode[0]=NULL;
                    $this->output["groups_mode"]=NULL;
                    CF::Redirect($Engine->engine_uri);
                }
                break;
            }
            case "delete":
            {
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddCondFS("id", "=", $mode[1]);
                $DB->Delete();
                $mode[0]=NULL;
                $this->output["groups_mode"]=NULL;
                CF::Redirect($Engine->engine_uri);    
            }
            case "edit_timetable":
            {
                
                //$this->output["edit_timetable"][]=$row;
                $this->output["groups_mode"]="edit_timetable";    
            }
            default:
            {
                if(isset($_POST["group_name"]))
                {
                    $DB->SetTable($this->db_prefix."groups");
                    $DB->AddCondFS("name", "=", $_POST["group_name"]);
					$fac = explode("|", $_POST["faculties"]);
                    $DB->AddCondFS("id_faculty", "=", $fac[0]);
                    $DB->AddCondFS("specialities_code", "=", $fac[1]);
                    $DB->AddCondFS("qualification", "=", $_POST["qualif"]);
                    $res = $DB->Select();
                    if(!($row = $DB->FetchAssoc($res)))
                    {
                        $DB->SetTable($this->db_prefix."groups");
                        $DB->AddValue("name", $_POST["group_name"]);
                        $DB->AddValue("id_faculty", $fac[0]);
                        $DB->AddValue("specialities_code", $fac[1]);
                        $DB->AddValue("qualification", $_POST["qualif"]);
                        $DB->AddValue("year", $_POST["year"]);
                        $DB->Insert();    
                    }   
                }
                $DB->SetTable($this->db_prefix."groups");
                $DB->AddOrder("id_faculty");
                $DB->AddOrder("name");
                $res =  $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("id", "=", $row["id_faculty"]);
                    $res2 = $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2))
                    {
                        $row["faculty_name"] = $row2["name"];
                        if($row2["pid"])
                        {
                            $DB->SetTable($this->db_prefix."faculties");
                            $DB->AddCondFS("id", "=", $row2["pid"]);
                            $res3 = $DB->Select();
                            if($row3 = $DB->FetchAssoc($res3))
                            {
                                $row["institute_name"] = $row3["name"];
                            }    
                        }
                        $row["institute_name"]=NULL;
                    }
                    $this->output["timetable_groups"][] = $row;
                }
				
                $DB->SetTable($this->db_prefix."faculties");
				$DB->AddCondFP("is_active");
                $DB->AddOrder("name");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res)) {
					$DB->SetTable($this->db_prefix."specialities");
					$DB->AddCondFS("id_faculty", "=", $row["id"]);
					$DB->AddOrder("name");
					$res1 = $DB->Select();
                    while($row2 = $DB->FetchAssoc($res1)) {
						$this->output["faculties"][$row["id"]]["fac_name"] = $row["name"];  
                        $this->output["faculties"][$row["id"]]["spec"][]=$row2;
                    }  
                }
                break;
            }
        }
        
    }
    
    function timetable_faculties($mode)
    {
        global $DB, $Engine;
        if(isset($_POST["mode"]))
            $mode[0] = $_POST["mode"];
        switch($mode[0])
        {
            case "edit":
            {
                if(!isset($_POST["id"]))
                {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("id", "=", $mode[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        if($row["pid"])
                        {
                            $DB->SetTable($this->db_prefix."faculties");
                            $DB->AddCondFS("id", "=", $row["pid"]);
							$DB->AddOrder("pos");
                            $res1 = $DB->Select();
                            if($row2 = $DB->FetchAssoc($res1))
                                $row["pid_name"] = $row2["name"];
                        }
                        $DB->SetTable($this->db_prefix."faculties");
						$DB->AddOrder("pos");
                        $res1 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res1))
                        {
                            $this->output["faculties"][]=$row2;
                        }                        
                        $this->output["edit_faculty"][]=$row;
                        $this->output["faculty_mode"]="edit";
                    }
                }
                else
                {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $DB->AddValue("name", $_POST["faculty_name"]);
                    $DB->AddValue("pid", $_POST["pid"]);
					$DB->AddValue("link", $_POST["faculty_link"]);
					$DB->AddValue("pos", $_POST["pos"]);
					$DB->AddValue("foundation_year", $_POST["foundation_year"]);
					$DB->AddValue("comment", $_POST["comment"]);
					if (isset($_POST["is_active"]) && $_POST["is_active"])
						$DB->AddValue("is_active", 1);
					else
						$DB->AddValue("is_active", 0);
                    $DB->Update();
                    $mode[0]=NULL;
                    $this->output["faculty_mode"]=NULL;
                    CF::Redirect($Engine->engine_uri);
                }
                break;
            }
            case "delete":
            {
                $DB->SetTable($this->db_prefix."departments");
                $DB->AddCondFS("faculty_id", "=", $mode[1]);
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable($this->db_prefix."teachers");
                    $DB->AddCondFS("department_id", "=", $row["id"]);
                    $DB->Delete();
                }
                $DB->SetTable($this->db_prefix."departments");
                $DB->AddCondFS("faculty_id", "=", $mode[1]);
                $DB->Delete();
                $DB->SetTable($this->db_prefix."faculties");
                $DB->AddCondFS("id", "=", $mode[1]);
                $DB->Delete();
                $mode[0]=NULL;
                $this->output["faculty_mode"]=NULL;
                CF::Redirect($Engine->engine_uri);
            }
            default:
            {
                if(isset($_POST["faculty_name"]))
                {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("name", "=", $_POST["faculty_name"]);
                    $res = $DB->Select();
                    if(!($row = $DB->FetchAssoc($res)))
                    {
                        $DB->SetTable($this->db_prefix."faculties");
                        $DB->AddValue("name", $_POST["faculty_name"]);
                        $DB->AddValue("pid", $_POST["pid"]);
						$DB->AddValue("pos", $_POST["pos"]);
						$DB->AddValue("link", $_POST["faculty_link"]);
						$DB->AddValue("short_name", $_POST["faculty_shortname"]);
						$DB->AddValue("foundation_year", $_POST["foundation_year"]);
						$DB->AddValue("comment", $_POST["comment"]);
						if (isset($_POST["is_active"]) && $_POST["is_active"])
							$DB->AddValue("is_active", 1);
						else
							$DB->AddValue("is_active", 0);
                        $DB->Insert();    
                    }   
                }            
        
                $DB->SetTable($this->db_prefix."faculties");
                $DB->AddCondFS("pid", "=", 0);
				$DB->AddOrder("pos");
                $res =  $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("pid", "=", $row["id"]);
                    $res2 =  $DB->Select();
                    while($row2 = $DB->FetchAssoc($res2))
                    {
                        $row["subfaculties"][]=$row2;
                    }
                    $this->output["timetable_faculties"][] = $row;
                }
                break;
            }  
        }
    }
	
	function pulpit_list($faculty_id ) {
		global $DB, $Engine;
		$output = null;
		$DB->SetTable($this->db_prefix."departments");
        $DB->AddCondFS("faculty_id", "=", $faculty_id);
        $res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
            $output[] = array(
				"url" => $row["url"],
				"name" => $row["name"]
			);
		}
		$this->output["pulpit"] = $output;
		$DB->SetTable($this->db_prefix."faculties");
        $DB->AddCondFS("id", "=", $faculty_id);
        $res2 = $DB->Select();
		if($row2 = $DB->FetchAssoc($res2))
            $this->output["faculty_name"] = $row2["name"];
	}
    
    function timetable_departmens($mode)
    {
        global $DB, $Engine;
        if(isset($_POST["mode"]))
            $mode[0] = $_POST["mode"];
        switch($mode[0])
        {
            case "edit":
            {
                if(!isset($_POST["id"]))
                {
                    $DB->SetTable($this->db_prefix."departments");
                    $DB->AddCondFS("id", "=", $mode[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        $DB->SetTable($this->db_prefix."faculties");
                        $DB->AddCondFS("id", "=", $row["faculty_id"]);
                        $res2 = $DB->Select();
                        if($row2 = $DB->FetchAssoc($res2))
                            $row["faculty_name"] = $row2["name"];
                        $DB->SetTable($this->db_prefix."faculties");
                        $res2 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res2))
                            $this->output["faculties"][] = $row2;
                        $this->output["edit_department"][]=$row;
                    }
                    $this->output["departments_mode"]="edit";
                }
                else
                {
                    $DB->SetTable($this->db_prefix."departments");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $DB->AddValue("name", $_POST["department_name"]);
                    $DB->AddValue("url", $_POST["department_url"]);
                    $DB->AddValue("faculty_id", $_POST["faculty"]);
                    $DB->Update();
                    $mode[0]=NULL;
                    $this->output["departments_mode"]=NULL;
                    CF::Redirect($Engine->engine_uri);
                }
                break;
            }
            case "delete":
            {
                $DB->SetTable($this->db_prefix."teachers");
                $DB->AddCondFS("department_id", "=", $mode[1]);
                $DB->Delete();
                $DB->SetTable($this->db_prefix."departments");
                $DB->AddCondFS("id", "=", $mode[1]);
                $DB->Delete();
                $mode[0]=NULL;
                $this->output["departments_mode"]=NULL;
                CF::Redirect($Engine->engine_uri);
                break;
            }
            default:
            {
                if(isset($_POST["department_name"]))
                {
                    $DB->SetTable($this->db_prefix."departments");
                    $DB->AddCondFS("name", "=", $_POST["department_name"]);
                    $DB->AddCondFS("faculty_id", "=", $_POST["faculty_id"]);
                    $res = $DB->Select();
                    if(!($row = $DB->FetchAssoc($res)))
                    {
                        $DB->SetTable($this->db_prefix."departments");
                        $DB->AddValue("name", $_POST["department_name"]);
                        $DB->AddValue("url", $_POST["department_url"]);
                        $DB->AddValue("faculty_id", $_POST["faculty_id"]);
                        $DB->Insert();    
                    }        
                }
        
        
                $DB->SetTable($this->db_prefix."departments");
                $DB->AddOrder("faculty_id");
                $res =  $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddCondFS("id", "=", $row["faculty_id"]);
                    $res2 =  $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2))
                    {
                        $row["faculty_name"] = $row2["name"];
                        $row["faculty_id"] = $row2["id"];
                    }
                    $this->output["timetable_departments"][] = $row;
                }
                $DB->SetTable($this->db_prefix."faculties");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $this->output["faculties"][] = $row;    
                }
                break;
            }
        }    
    }
    
    
    function timetable_subjects($mode)
    {
        global $DB, $Engine, $Auth;
        if(isset($_POST["mode"]))
            $mode[0] = $_POST["mode"];
            
            
        switch($mode[0])
        {
            case "edit":
            {
                if(!isset($_POST["id"]))
                {
                    $DB->SetTable($this->db_prefix."subjects");
                    $DB->AddCondFS("id", "=", $mode[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        $DB->SetTable($this->db_prefix."departments");
                        $DB->AddCondFS("id", "=", $row["department_id"]);
                        $res2 = $DB->Select();
                        if($row2 = $DB->FetchAssoc($res2))
                            $row["department_name"] = $row2["name"];
                        $DB->SetTable($this->db_prefix."departments");
                        $DB->AddOrder("name");
                        $res2 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res2))
						//	if ($Engine->OperationAllowed($this->module_id, "subjects.faculty.handle", $row2["faculty_id"], $Auth->usergroup_id) || $Engine->OperationAllowed($this->module_id, "subjects.department.handle", $row2["id"], $Auth->usergroup_id))
								$this->output["departments"][] = $row2;
                        $this->output["edit_subject"][]=$row;
                    }
                    $this->output["subjects_mode"]="edit";
                }
                else
                {
                    $DB->SetTable($this->db_prefix."subjects");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $DB->AddValue("name", $_POST["subject_name"]);
                    $DB->AddValue("department_id", $_POST["department"]);
                    $DB->Update();
                    $mode[0]=NULL;
                    $this->output["subjects_mode"]=NULL;
                    CF::Redirect($Engine->engine_uri);
                }
                break;
            }
            case "delete":
            {
                $DB->SetTable($this->db_prefix."subjects");
                $DB->AddCondFS("id", "=", $mode[1]);
                $DB->Delete();
                $mode[0]=NULL;
                $this->output["subjects_mode"]=NULL;
                CF::Redirect($Engine->engine_uri);    
            }
            default:
            { 
                if(isset($_POST["subject_name"]) && isset($_POST["department_id"])) {
					if($_POST["department_id"] != 0) {
						$DB->SetTable($this->db_prefix."subjects");
						$DB->AddCondFS("name", "=", $_POST["subject_name"]);
						$DB->AddCondFS("department_id", "=", $_POST["department_id"]);
						$res = $DB->Select();
						if(!($row = $DB->FetchAssoc($res))) {
						    $DB->SetTable($this->db_prefix."subjects");
						    $DB->AddValue("name", $_POST["subject_name"]);
						    $DB->AddValue("department_id", $_POST["department_id"]);
						    $DB->Insert();   
						    $Engine->LogAction($this->module_id, "item", $DB->LastInsertId(), "add"); 
						}
					} else {
						$this->output["error"] = 313;
					}      
                } 
                
				$DB->SetTable($this->db_prefix."subjects");
                $DB->AddOrder("department_id");                
                $res = $DB->Select();
				$dep_id = 0;
				$subj_array = null;
				while($row = $DB->FetchAssoc($res)) {
					$subj_array[$row["department_id"]][] = array(
														"subj_name" => $row["name"],
														"subj_id" => $row["id"]		 
													);
				}
				
				$dep_array = null;
				$DB->SetTable($this->db_prefix."subjects", "s");
                $DB->AddTable($this->db_prefix."departments","d");
                $DB->AddCondFF("d.id","=","s.department_id");
				$DB->AddOrder("d.faculty_id");            
                $DB->AddField("d.id","id");          
                $DB->AddField("d.name","name");          
                $DB->AddField("d.faculty_id","faculty_id");
				$res = $DB->Select(null, null, true, true, true);
				while($row = $DB->FetchAssoc($res)) {
					$dep_array[$row["faculty_id"]][] = array(
													"dep_name" => $row["name"],
													"dep_id" => $row["id"],
													"subj_list" => $subj_array[$row["id"]]
												);
				}
				
				$DB->SetTable($this->db_prefix."subjects", "s");
                $DB->AddTable($this->db_prefix."departments","d");
                $DB->AddTable($this->db_prefix."faculties","f");
                $DB->AddCondFF("d.id","=","s.department_id");
                $DB->AddCondFF("f.id","=","d.faculty_id");
				$DB->AddOrder("f.id");            
                $DB->AddField("f.id","id");          
                $DB->AddField("f.name","name");          
				$res = $DB->Select(null, null, true, true, true);
				while($row = $DB->FetchAssoc($res)) {
					//echo $row["id"]."<br/>";
					if ($Engine->OperationAllowed($this->module_id, "subjects.faculty.handle", $row["id"], $Auth->usergroup_id))
					$this->output["fac_dep_subj"][] = array(
									"fac_name" => $row["name"],
									"fac_id" => $row["id"],
									"dep_list" => $dep_array[$row["id"]]
								);
				}
				$DB->SetTable($this->db_prefix."subjects");
                $DB->AddCondFS("department_id","=", "0");
				$DB->AddOrder("name");            
                $DB->AddField("id");          
                $DB->AddField("name");          
				$res = $DB->Select();
				while($row = $DB->FetchAssoc($res)) {
					$this->output["nodep_subj"][] = array(
												"subj_name" => $row["name"],
												"subj_id" => $row["id"]		
											);
				}
				
                
                /*$DB->SetTable($this->db_prefix."subjects");
                $DB->AddOrder("department_id");                
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable($this->db_prefix."departments");
                    $DB->AddCondFS("id", "=", $row["department_id"]);
                    $DB->AddOrder("name");
                    $res2 = $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2))
                        $row["department_name"] = $row2["name"];
                    $this->output["subjects"][] = $row;
                } */
                $DB->SetTable($this->db_prefix."departments","d");
                $DB->AddTable($this->db_prefix."faculties","f");
                $DB->AddCondFF("f.id","=","d.faculty_id");
                $DB->AddField("d.id","id");
                $DB->AddField("d.name","name");
                $DB->AddField("d.faculty_id","fac_id");
                $DB->AddField("f.id","faculty_id");
                $DB->AddField("f.name","faculty_name");
				$DB->AddOrder("f.id");       
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {	
					if ($Engine->OperationAllowed($this->module_id, "subjects.faculty.handle", $row["fac_id"], $Auth->usergroup_id) || $Engine->OperationAllowed($this->module_id, "subjects.department.handle", $row["id"], $Auth->usergroup_id) || $Auth->usergroup_id==1)
						$this->output["departments"][] = $row;
                }
				
                break;
            }  
        }        
    }
    
    
    function timetable_auditorium($mode)
    {
        global $DB, $Engine, $Auth;
        if(isset($_POST["mode"]))
            $mode[0] = $_POST["mode"];
            
            
        switch($mode[0])
        {
            case "edit":
            {
                if(!isset($_POST["id"]))
                {
                    $DB->SetTable($this->db_prefix."auditorium");
                    $DB->AddCondFS("id", "=", $mode[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res))
                    {
                        $DB->SetTable($this->db_prefix."buildings");
                        $DB->AddCondFS("id", "=", $row["building_id"]);
                        $res2 = $DB->Select();
                        if($row2 = $DB->FetchAssoc($res2))
                        {
                            $row["building_name"] = $row2["name"];
                            $row["building_label"] = $row2["label"];
                        }
                        $DB->SetTable($this->db_prefix."buildings");
                        $DB->AddOrder("name");
                        $res2 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res2))
                            $this->output["buildings"][] = $row2;
                        $DB->SetTable($this->db_prefix."departments");
                        $DB->AddOrder("name");
                        $res2 = $DB->Select();
                        while($row2 = $DB->FetchAssoc($res2))
                        {       
                            $this->output["departments"][] = $row2;
                        }
                        $this->output["edit_auditorium"][]=$row;
                    }
                    $this->output["auditorium_mode"]="edit";
                }
                else
                {
                    $DB->SetTable($this->db_prefix."auditorium");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $DB->AddValue("is_using",$_POST["is_using"]);
                    $DB->AddValue("name", $_POST["auditorium_name"]);
                    $DB->AddValue("building_id", $_POST["building"]);
                    $DB->AddValue("roominess",$_POST["roominess"]);
                    if(isset($_POST["department_id"]))
                    {
                        $DB->AddValue("department_id",$_POST["department_id"]);
                    }
                    if(isset($_POST["type"]))
                    {
                        $DB->AddValue("type",$_POST["type"]);
                    }
                    else
                    {
                        $DB->AddValue("type",0);
                    }
                    $DB->Update();
                    $mode[0]=NULL;
                    $this->output["auditorium_mode"]=NULL;
                    CF::Redirect($Engine->engine_uri);
                }
                break;
            }
            case "delete":
            {
                $DB->SetTable($this->db_prefix."auditorium");
                $DB->AddCondFS("id", "=", $mode[1]);
                $DB->Delete();
                $mode[0]=NULL;
                $this->output["auditorium_mode"]=NULL;
                CF::Redirect($Engine->engine_uri);    
            }
            default:
            {	
                if(isset($_POST["auditorium_name"]))
                {
                    $DB->SetTable($this->db_prefix."auditorium");
                    $DB->AddCondFS("name", "=", $_POST["auditorium_name"]);
                    $DB->AddCondFS("building_id", "=", $_POST["building_id"]);
                    $res = $DB->Select();
                    if(!($row = $DB->FetchAssoc($res)))
                    {
                        $DB->SetTable($this->db_prefix."auditorium");
                        $DB->AddValue("is_using",$_POST["is_using"]);
                        $DB->AddValue("name", $_POST["auditorium_name"]);
                        $DB->AddValue("building_id", $_POST["building_id"]);
                        $DB->AddValue("roominess",$_POST["roominess"]);
                        if(isset($_POST["department_id"]))
                        {
                            $DB->AddValue("department_id",$_POST["department_id"]);
                        }
                        if(isset($_POST["type"]))
                        {
                            $DB->AddValue("type",$_POST["type"]);
                        }
                        else
                        {
                            $DB->AddValue("type",0);
                        }
                        $DB->Insert();    
                    }        
                }
                
                
                $DB->SetTable($this->db_prefix."auditorium");
                $DB->AddOrder("building_id");
                $DB->AddOrder("name");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable($this->db_prefix."buildings");
                    $DB->AddCondFS("id", "=", $row["building_id"]);
                    $res2 = $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2))
                    {
                        $row["building_name"] = $row2["name"];
                        $row["label"] = $row2["label"];
                    }
                    $this->output["auditorium"][] = $row;
                } 
                $DB->SetTable($this->db_prefix."buildings");
                $DB->AddOrder("name");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {
                    $this->output["buildings"][] = $row;
                }
                $DB->SetTable($this->db_prefix."departments");
                $DB->AddOrder("name");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res))
                {	
					$this->output["departments"][] = $row;
                }
                break;
            }  
        }        
    }
    
    
    
    
    
    function timetable_teachers($mode, $dep_id)
    {
        global $DB, $Engine, $Auth;
        if(isset($_POST["mode"]))
            $mode[0] = $_POST["mode"];

        switch($mode[0]) {
            case "edit": {
				$is_error = false;
				if(isset($_POST["id"])) {
					$isset_dep = false;
					foreach($_POST["department"] as $dep) {
						if($dep["new"]) {
							$isset_dep = true;
							continue;
						}
					}
					if(!$isset_dep) {
						$this->output["messages"]["bad"][] = 318;
						$is_error = true;
					}
					if((!isset($_POST["last_name"]) || empty($_POST["last_name"])) && (!isset($_POST["first_name"]) || empty($_POST["first_name"]))) {
						$this->output["messages"]["bad"][] = 317; 
						$is_error = true;
					}
					if(!empty($_POST["username"]) || !empty($_POST["password1"]) || !empty($_POST["password2"]) || !empty($_POST["email"])) {
						if(empty($_POST["username"]) || (!$_POST["isset_user"] && (empty($_POST["password1"]) && empty($_POST["password2"]) || $_POST["password1"] != $_POST["password2"]))) {
							$this->output["messages"]["bad"][] = 319; 
							$is_error = true;
						}
					}
					$this->output["display_variant"]["edit_item"] = "edit";
				}
				if(!isset($_POST["id"]) || $is_error) {
                    $DB->SetTable($this->db_prefix."people");
                    $DB->AddCondFS("id", "=", $mode[1]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res)) {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddCondFS("people_id", "=", $mode[1]);
                        $res2 = $DB->Select();
						while($row2 = $DB->FetchAssoc($res2)) {
							$row["department"][] = $row2["department_id"];
						}
                        if($row["user_id"]) {
                            $DB->SetTable("auth_users");
                            $DB->AddCondFS("id","=",$row["user_id"]);
                            $res2 = $DB->Select();
                            if($row2 = $DB->FetchAssoc($res2)) {
                                $row["username"]=$row2["username"];
                                $row["email"]=$row2["email"];
                                $row["password"]=$row2["password"];
                            }
                        }
                        $this->output["edit_teacher"][]=$row;
                        if($Engine->OperationAllowed(5, "teachers.own.handle", -1, $Auth->usergroup_id) && !$Engine->OperationAllowed(5, "teachers.all.handle", -1, $Auth->usergroup_id))
                        {
                            
                            $DB->SetTable($this->db_prefix."people","p");
                            $DB->AddTable($this->db_prefix."teachers","t");
                            $DB->AddTable($this->db_prefix."departments","d");
                            $DB->AddCondFS("p.user_id","=",$Auth->user_id);
                            $DB->AddCondFF("t.people_id","=","p.id");
                            $DB->AddCondFF("t.department_id","=","d.id");
                            $DB->AddField("d.name","department_name");
                            $DB->AddField("t.department_id","department_id");
                            $res = $DB->Select();
                            if($row = $DB->FetchAssoc($res))
                            {
                                $this->output["current_department_id"]=$row["department_id"];
                                $this->output["current_department_name"]=$row["department_name"];   
                            }
                        }
                        
                    }
                    $DB->SetTable($this->db_prefix."faculties");
                    $DB->AddOrder("name");
                    $res = $DB->Select();
                    while($row = $DB->FetchAssoc($res)) {
						$DB->SetTable($this->db_prefix."departments");
                        $DB->AddCondFS("faculty_id", "=", $row["id"]);
						$DB->AddOrder("name");
                        $res2 =  $DB->Select();
                        while($row2 = $DB->FetchAssoc($res2)) {
							$this->output["faculties"][$row["id"]]["fac_name"] = $row["name"];
							$this->output["faculties"][$row["id"]]["departments"][] = $row2;
                        }   
                    }
                    $this->output["teachers_mode"]="edit";
                }
                else {
                    $DB->SetTable($this->db_prefix."people");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $DB->AddValue("last_name", $_POST["last_name"]);
                    $DB->AddValue("name", $_POST["first_name"]);
                    $DB->AddValue("patronymic", $_POST["patronymic"]);
                    $DB->AddValue("male", $_POST["male"]);
					$DB->AddValue("comment", $_POST["comment"]);
					$DB->AddValue("exp_teach", $_POST["exp_teach"]);
					$DB->AddValue("exp_full", $_POST["exp_full"]);
					$DB->AddValue("prad", $_POST["prad"]);
                    $DB->Update();
                    
                    $DB->SetTable($this->db_prefix."people");
                    $DB->AddCondFS("id", "=", $_POST["id"]);
                    $res = $DB->Select();
                    if($row = $DB->FetchAssoc($res)) {
						$Engine->LogAction($this->module_id, "teacher", $row["user_id"], "edit");
                        $this->update_user($row, 2);       
                    } else {
						$user_id = $this->insert_user($_POST["id"],2);
                        if($user_id !=== false) {
							$Engine->LogAction($this->module_id, "teacher", $user_id, "edit");
						}						
                    }
					
                    if(isset($_POST["department"])) {
						foreach($_POST["department"] as $dep) {
							if($dep["new"]) {
								$DB->SetTable($this->db_prefix."teachers");
								$DB->AddCondFS("people_id", "=", $_POST["id"]);
								$DB->AddCondFS("department_id", "=", $dep["old"]);
								$res = $DB->Select();
								if($row = $DB->FetchAssoc($res)) {
									$DB->SetTable($this->db_prefix."teachers");
									$DB->AddCondFS("id", "=", $row["id"]);
									$DB->AddCondFS("department_id", "=", $dep["old"]);
									$DB->AddValue("department_id", $dep["new"]);
									$DB->Update();
								} else {
									$DB->SetTable($this->db_prefix."teachers");
									$DB->AddValue("people_id", $_POST["id"]);
									$DB->AddValue("department_id", $dep["new"]);
									$DB->Insert();    
								}  
							} else {
								$DB->SetTable($this->db_prefix."teachers");
								$DB->AddCondFS("people_id", "=", $_POST["id"]);
								$DB->AddCondFS("department_id", "=", $dep["old"]);
								$DB->Delete(); 
							}							  
						}
					}
					
                    /*if(isset($_POST["department1_id"]) && $_POST["department1_id"])
                    {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddCondFS("people_id", "=", $_POST["id"]);
                        $res = $DB->Select();
                        if($row = $DB->FetchAssoc($res))
                        {
                            $DB->SetTable($this->db_prefix."teachers");
                            $DB->AddCondFS("id", "=", $row["id"]);
                            $DB->AddValue("department_id", $_POST["department1_id"]);
                            $DB->Update();
                        }
                        else
                        {
                            $DB->SetTable($this->db_prefix."teachers");
                            $DB->AddValue("people_id", $_POST["id"]);
                            $DB->AddValue("department_id", $_POST["department1_id"]);
                            $DB->Insert();    
                        }    
                    }
                    else
                    {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddCondFS("people_id", "=", $_POST["id"]);
                        $DB->Delete();    
                    }
                    if(isset($_POST["department2_id"]) && $_POST["department2_id"])
                    {  
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddCondFS("people_id", "=", $_POST["id"]);
                        $DB->AddCondFS("department_id", "<>", $_POST["department1_id"]);
                        $res = $DB->Select();
                        if($row = $DB->FetchAssoc($res))
                        {
                            $DB->SetTable($this->db_prefix."teachers");
                            $DB->AddCondFS("id", "=", $row["id"]);
                            $DB->AddValue("department_id", $_POST["department2_id"]);
                            $DB->Update();    
                        }
                        else
                        {
                            $DB->SetTable($this->db_prefix."teachers");
                            $DB->AddValue("people_id", $_POST["id"]);
                            $DB->AddValue("department_id", $_POST["department2_id"]);
                            $DB->Insert();    
                        }
                    }
                    else
                    {
                        $DB->SetTable($this->db_prefix."teachers");
                        $DB->AddCondFS("people_id", "=", $_POST["id"]);
                        $DB->AddCondFS("department_id", "<>", $_POST["department1_id"]);
                        $DB->Delete();    
                    }*/
                    $mode[0]=NULL;
                    $this->output["teachers_mode"]=NULL;
                    CF::Redirect($Engine->engine_uri);
                }
                break;
            }
            case "delete":
            {
                $DB->SetTable($this->db_prefix."teachers");
                $DB->AddCondFS("people_id", "=", $mode[1]);
                $DB->Delete();
                /*$DB->SetTable($this->db_prefix."people");
                $DB->AddCondFS("id","=",$mode[1]);
                $res = $DB->Select();
                if($row = $DB->FetchAssoc($res))
                {
                    $DB->SetTable("auth_users");
                    $DB->AddCondFS("id","=",$row["user_id"]);
                    $DB->Delete();
                }
                
                $DB->SetTable($this->db_prefix."people");
                $DB->AddCondFS("id", "=", $mode[1]);
                $DB->Delete();*/
				$Engine->LogAction($this->module_id, "teacher", $mode[1], "delete"); 
                $mode[0]=NULL;
                $this->output["teachers_mode"]=NULL;
                CF::Redirect($Engine->engine_uri);
                break;
            }
            case "search_teacher":
            {
                //если установлена текущая кафедра, то добавляем преподавателей на текущую кафедру
                //CF::Debug($_POST);
                if(isset($_POST["department_id"]))
                {
                    foreach($_POST["teachers_id"] as $teacher)
                    {
                        if(isset($teacher["check"]) && $teacher["check"] == "on")
                        {
                            $DB->SetTable($this->db_prefix."teachers");
                            $DB->AddValue("people_id",$teacher["id"]);
                            $DB->AddValue("department_id",$_POST["department_id"]);
                            $DB->Insert();
                        }                 
                    }
                    CF::Redirect($Engine->engine_uri);   
                }
                
                //то есть мы кого-то ищем
                if(isset($_POST["search_teacher"]))
                {
                    //выделяем отдельно ФИО
                    $parts = explode(" ", trim($_POST["search_teacher"]));
                    
                    //выбираем текущую кафедру (в зависимости от принадлежности пользователя к какой-то каферде)
                    //надо подумать, как сделать, если завкафедры числится не только на той кафедре, где он зав. !!!!!!!!!!!!!!!!!!!!!!
                    $DB->SetTable($this->db_prefix."people","p");
                    $DB->AddTable($this->db_prefix."teachers","t");
                    $DB->AddTable("auth_users","a");
                    $DB->AddCondFS("a.id","=",$Auth->user_id);
                    $DB->AddCondFF("a.id","=","p.user_id");
                    $DB->AddCondFS("a.usergroup_id","=",6);//смотрим, чтобы это был именно завкафедры
                    $DB->AddCondFF("t.people_id","=","p.id");
                    $res2 = $DB->Select();
                    if($row2 = $DB->FetchAssoc($res2))
                    {
                        //если пользователь - завкафедры, занесли кафедру как текущую
                        $this->output["current_department"]=$row2["department_id"];
                        //ищем подходящих преподавателей
                        $DB->SetTable($this->db_prefix."people");
                        //$DB->AddTable($this->db_prefix."teachers","t");
                        foreach($parts as $part)
                        {
                            $DB->AddAltFS("name", "LIKE", "%".$part."%");
                            $DB->AddAltFS("last_name", "LIKE", "%".$part."%");        
                            $DB->AddAltFS("patronymic", "LIKE", "%".$part."%");
                            $DB->AppendAlts();
                            $DB->AddCondFS("status_id","=",2);
                        }            
                        $DB->AddOrder("last_name");
                        $res = $DB->Select();
                        $this->output["search_teachers"] = array();
                        while($row = $DB->FetchAssoc($res))
                        {
                            $DB->SetTable($this->db_prefix."teachers");
                            $DB->AddCondFS("people_id","=",$row["id"]);
                            $DB->AddCondFS("department_id","=",$row2["department_id"]);
                            $res3 = $DB->Select();
                            if($row3 = $DB->FetchAssoc($res3))
                            {
                                $row["in_current_department"] = 1;
                            }
                            else
                            {
                                $row["in_current_department"] = 0;    
                            }
                            $this->output["search_teachers"][] = $row;
                        }
                        $this->output["auth_usergroup"] = $Auth->usergroup_id;
                        //CF::Debug($this->output["auth_usergroup"]);           
                    }
                    else
                    {
                        $DB->SetTable($this->db_prefix."people");
                        foreach($parts as $part)
                        {
                            $DB->AddAltFS("name", "LIKE", "%".$part."%");
                            $DB->AddAltFS("last_name", "LIKE", "%".$part."%");        
                            $DB->AddAltFS("patronymic", "LIKE", "%".$part."%");
                            $DB->AppendAlts();
                            $DB->AddCondFS("status_id","=",2);
                        }            
                        $DB->AddOrder("last_name");
                        $res = $DB->Select();
                        $this->output["search_teachers"] = array();
                        while($row = $DB->FetchAssoc($res))
                        {
                            $this->output["search_teachers"][] = $row;
                        }
                    }
                    $this->output["teachers_mode"]="search_teacher";
                    $this->output["uri"]=$Engine->engine_uri;
                }
                break;
            }
            default:
            {
				$is_error = false;
				$user_valid = true;
				if(isset($_POST["last_name"])) {
					$isset_dep = false;
					foreach($_POST["department"] as $dep) {
						if($dep) {
							$isset_dep = true;
							continue;
						}
					}
					if(!$isset_dep) {
						$this->output["messages"]["bad"][] = 318;
						$is_error = true;
					}
					if((!isset($_POST["last_name"]) || empty($_POST["last_name"])) && (!isset($_POST["first_name"]) || empty($_POST["first_name"]))) {
						$this->output["messages"]["bad"][] = 317; 
						$is_error = true;
					}
					if(!empty($_POST["username"]) || !empty($_POST["password1"]) || !empty($_POST["password2"]) || !empty($_POST["email"])) {
						if(empty($_POST["username"]) || ((empty($_POST["password1"]) && empty($_POST["password2"]) || $_POST["password1"] != $_POST["password2"]))) {
							$this->output["messages"]["bad"][] = 319; 
							$is_error = true;
							$user_valid = false;
						}
					}
					if(empty($_POST["username"]) || empty($_POST["password1"]) || empty($_POST["password2"])) {
						$user_valid = false;
					}
					$this->output["display_variant"]["add_item"]["last_name"] = $_POST["last_name"];
					$this->output["display_variant"]["add_item"]["first_name"] = $_POST["first_name"];
					$this->output["display_variant"]["add_item"]["patronymic"] = $_POST["patronymic"];
					$this->output["display_variant"]["add_item"]["department"] = $_POST["department"];
					$this->output["display_variant"]["add_item"]["comment"] = $_POST["comment"];
					$this->output["display_variant"]["add_item"]["male"] = $_POST["male"];
					$this->output["display_variant"]["add_item"]["exp_full"] = $_POST["exp_full"];
					$this->output["display_variant"]["add_item"]["exp_teach"] = $_POST["exp_teach"];
					$this->output["display_variant"]["add_item"]["prad"] = $_POST["prad"];
					$this->output["display_variant"]["add_item"]["username"] = $_POST["username"];
					$this->output["display_variant"]["add_item"]["email"] = $_POST["email"];
				}
                if(isset($_POST["last_name"]) && !$is_error)
                {
                    if(!isset($_POST["check"]))
                    {
                        $DB->SetTable($this->db_prefix."people");
                        $DB->AddCondFS("last_name", "=", $_POST["last_name"]);
                        if(!empty($_POST["first_name"]))
                            $DB->AddCondFS("name", "=", $_POST["first_name"]);
                        if(!empty($_POST["patronymic"]))
                            $DB->AddCondFS("patronymic", "=", $_POST["patronymic"]);
                        $res = $DB->Select();
                        if($row = $DB->FetchAssoc($res)) {							
							$DB->SetTable($this->db_prefix."teachers");
							$DB->AddCondFS("people_id", "=", $row["id"]);
							$res1 = $DB->Select();
							while($row1 = $DB->FetchAssoc($res1)) {
								if($row1["department_id"] == $_POST["department"][0] || $row1["department_id"] == $_POST["department"][1]) {
									$row["dep_identic"] = true;
									$DB->SetTable($this->db_prefix."departments");
									$DB->AddCondFS("id", "=", $row1["department_id"]);
									$res2 = $DB->Select();
									if($row2 = $DB->FetchAssoc($res2)) {
										$row["department"][$row2["id"]] = $row2["name"];
									}
								}
							}
							$row["last_name"] = $_POST["last_name"];
							$row["name"] = $_POST["first_name"];
							$row["patronymic"] = $_POST["patronymic"];
							$row["male"] = $_POST["male"];
							$this->output["is_exist"]["people"] = $row;
							$this->output["is_exist"]["department"] = $_POST["department"];
							$this->output["is_exist"]["exp_full"] = $_POST["exp_full"];
							$this->output["is_exist"]["exp_teach"] = $_POST["exp_teach"];
							$this->output["is_exist"]["prad"] = $_POST["prad"];
							$this->output["is_exist"]["comment"] = $_POST["comment"];
							$this->output["is_exist"]["username"] = $_POST["username"];
							$this->output["is_exist"]["email"] = $_POST["email"];
							$this->output["is_exist"]["password1"] = $_POST["password1"];
							$this->output["is_exist"]["password2"] = $_POST["password2"];
							/*if($row1 = $DB->FetchAssoc($res1)) {
								$DB->SetTable($this->db_prefix."departments");
								$DB->AddCondFS("id", "=", $_POST["department1_id"]);
								$res2 = $DB->Select();
								if($row2 = $DB->FetchAssoc($res2))
								{
									$row["department1_name"]=$row2["name"];
									$row["department1_id"]=$row2["id"];
								}
								$DB->SetTable($this->db_prefix."departments");
								$DB->AddCondFS("id", "=", $_POST["department2_id"]);
								$res2 = $DB->Select();
								if($row2 = $DB->FetchAssoc($res2))
								{
									$row["department2_name"]=$row2["name"];
									$row["department2_id"]=$row2["id"];
								}
								$this->output["is_exist"][] = $row;
							}*/
                        }
                        else   
                        {
                            $DB->SetTable($this->db_prefix."people");
                            $DB->AddValue("user_id", "0");
                            $DB->AddValue("last_name", $_POST["last_name"]);
                            $DB->AddValue("name", $_POST["first_name"]);
                            $DB->AddValue("patronymic", $_POST["patronymic"]);
                            $DB->AddValue("male", $_POST["male"]);
							$DB->AddValue("comment", $_POST["comment"]);
							$DB->AddValue("prad", $_POST["prad"]);
							$DB->AddValue("exp_full", $_POST["exp_full"]);
							$DB->AddValue("exp_teach", $_POST["exp_teach"]);
                            $DB->AddValue("status_id", 2);
                            $DB->Insert();
                            $res2 = $DB->Exec("SELECT max(LAST_INSERT_ID(id)) FROM nsau_people limit 1");
                            if($row2 = $DB->FetchAssoc($res2))
                            {
                                foreach($row2 as $row3)
                                {
                                    if($user_valid) {
										$user_id = $this->insert_user($row3,2);
										if($user_id !=== false)
											$Engine->LogAction($this->module_id, "teacher", $user_id, "edd");
                                    }
									$Engine->AddPrivilege(5, "people.handle", $row3, $Auth->usergroup_id, 1, "add_teachers");
									if(isset($_POST["department"])) {
										foreach($_POST["department"] as $dep) {
											if($dep) {
												$DB->SetTable($this->db_prefix."teachers");
												$DB->AddValue("people_id", $row3);
												$DB->AddValue("department_id", $dep);
												//$DB->AddValue("rate", $_POST["rate1"]);
												$DB->Insert();
											}											
										}
									}
									/*if($_POST["department1_id"])
									{
										$DB->SetTable($this->db_prefix."teachers");
										$DB->AddValue("people_id", $row3);
										$DB->AddValue("department_id", $_POST["department1_id"]);
										$DB->Insert();
									}
									if(isset($_POST["department2_id"]) && $_POST["department2_id"])
									{
										$DB->SetTable($this->db_prefix."teachers");
										$DB->AddValue("people_id", $row3);
										$DB->AddValue("department_id", $_POST["department2_id"]);
										$DB->Insert();     
									}*/
									
									/**/
									if(isset($_POST["email"]) && !empty($_POST["email"])) {
										$deport_mass = null;
										if(isset($_POST["department1_id"])) {
											$DB->SetTable($this->db_prefix."departments");
											$DB->AddCondFS("id", "=", $_POST["department1_id"]);
											$res1 = $DB->Select();
											if($row1 = $DB->FetchAssoc($res1)) {
												$deport_mass = $row1["name"];
											}	
										}
										if(isset($_POST["department2_id"])) {
											$DB->SetTable($this->db_prefix."departments");
											$DB->AddCondFS("id", "=", $_POST["department1_id"]);
											$res1 = $DB->Select();
											if($row1 = $DB->FetchAssoc($res1)) {
												if(!is_null($deport_mass)) {
													$deport_mass .= ", ".$row1["name"];
												} else {
													$deport_mass = $row1["name"];
												}
											}										
										}
										if(is_null($deport_mass)) {
											$deport_mass = "не указана";
										}
										
										$mail_message_patterns = array(
											'headers' => array(
													'%site_short_name%' => "НГАУ<admin@".$_SERVER['HTTP_HOST'].">",
													'%server_name%' => $_SERVER['HTTP_HOST'],
													'%email%' => $_POST["email"],
													'%subject%' => SITE_SHORT_NAME
												),
											'subject' => array(
													'%site_short_name%' => SITE_SHORT_NAME
												),
											'content' => array(
													'%last_name%' => $_POST["last_name"],
													'%first_name%' => $_POST["first_name"],
													'%patronymic%' => $_POST["patronymic"],
													'%server_name%' => $_SERVER['HTTP_HOST'],
													'%department%' => $deport_mass,
													'%login%' => $_POST["username"],
													'%password%' => $_POST["password1"]
												),
											'message' => array(
													'%site_short_name%' => SITE_SHORT_NAME,
													'%time%' => date('Y-m-d H:i:s'),
													'%content%' => &$this->settings['content']['mess']
												)
										);
										
										$this->settings['content']['mess'] = str_replace( array_keys($mail_message_patterns['content']), $mail_message_patterns['content'], $this->settings['content']['mess']);
										
										foreach ($this->settings['notify_settings'] as $ind=>$setting) {
											$this->settings['notify_settings'][$ind] = str_replace( array_keys($mail_message_patterns[$ind]), $mail_message_patterns[$ind], $setting);
										}
									
										mail($_POST["email"], $this->settings['notify_settings']['subject'], $this->settings['notify_settings']['message'], $this->settings['notify_settings']['headers']);
									
									}
									/**/
								}
							}  
							$this->output["display_variant"] = null;                                
						}
					}
					else
					{
						if($_POST["check"] == 'Да')
						{
							$DB->SetTable($this->db_prefix."people");
							$DB->AddValue("last_name", $_POST["last_name"]);
							$DB->AddValue("name", $_POST["first_name"]);
							$DB->AddValue("patronymic", $_POST["patronymic"]);
							$DB->AddValue("male", $_POST["male"]);
							$DB->AddValue("comment", $_POST["comment"]);
							$DB->AddValue("status_id", 2);
							$DB->AddValue("prad", $_POST["prad"]);
							$DB->AddValue("exp_full", $_POST["exp_full"]);
							$DB->AddValue("exp_teach", $_POST["exp_teach"]);
							$DB->Insert();
							$res2 = $DB->Exec("SELECT max(LAST_INSERT_ID(id)) FROM nsau_people limit 1");
							if($row2 = $DB->FetchAssoc($res2))
							{
								foreach($row2 as $row3)
								{
									if($user_valid) {
										$user_id = $this->insert_user($row3,2);										
										if($user_id !=== false)
											$Engine->LogAction($this->module_id, "teacher", $user_id, "add");
									}
									$Engine->AddPrivilege(5, "people.handle", $row3, $Auth->usergroup_id, 1, "add_teachers");
									if(isset($_POST["department"])) {
										foreach($_POST["department"] as $dep) {
											if($dep) {
												$DB->SetTable($this->db_prefix."teachers");
												$DB->AddValue("people_id", $row3);
												$DB->AddValue("department_id", $dep);
												//$DB->AddValue("rate", $_POST["rate1"]);
												$DB->Insert();	
											}											
										}
									}
									
									/*if(isset($_POST["department1"]))
									{
										$DB->SetTable($this->db_prefix."teachers");
										$DB->AddValue("people_id", $row3);
										$DB->AddValue("department_id", $_POST["department1"]);
										//$DB->AddValue("rate", $_POST["rate1"]);
										$DB->Insert();
									}
									if(isset($_POST["department2"]))
									{
										$DB->SetTable($this->db_prefix."teachers");
										$DB->AddValue("people_id", $row3);
										$DB->AddValue("department_id", $_POST["department2"]);
										//$DB->AddValue("rate", $_POST["rate2"]);
										$DB->Insert();     
									}*/
								}    
							}    
						}
						$this->output["display_variant"] = null;
					}
				}
            //CF::Debug($Auth);
				if($Engine->OperationAllowed(5, "teachers.all.handle", -1, $Auth->usergroup_id))
				{
					$DB->SetTable($this->db_prefix."teachers", "t");
					$DB->AddTable($this->db_prefix."people","p");
					$DB->AddTable($this->db_prefix."departments","d");
					$DB->AddField("d.name","department_name");
					$DB->AddField("d.id","department_id");
					$DB->AddField("p.name","name");
					$DB->AddField("p.last_name","last_name");
					$DB->AddField("p.patronymic","patronymic");
					$DB->AddField("p.comment","comment");
					$DB->AddField("p.exp_teach","exp_teach");
					$DB->AddField("p.exp_full","exp_full");
					$DB->AddField("p.prad","prad");
					$DB->AddField("p.male","male");
					$DB->AddField("p.id","id");
					//$DB->AddField("t.rate","rate");
					//$DB->AddOrder("t.department_id");
					$DB->AddCondFF("p.id","=", "t.people_id");
					$DB->AddCondFF("d.id","=","t.department_id");
                    $DB->AddOrder("d.id");
                    $DB->AddOrder("p.last_name");
					//echo $DB->SelectQuery();
					$res =  $DB->Select();
					while($row = $DB->FetchAssoc($res)) {
						$this->output["timetable_teachers"][$row["department_id"]]["dep_name"] = $row["department_name"]; 
						$this->output["timetable_teachers"][$row["department_id"]]["teachers"][] = $row;
						$this->output["allow_handle"][$row["department_id"]] = 1;
					}
					$this->output["current_department_id"] = 0;
					
				}
				else
				{	
					if($Engine->OperationAllowed(5, "teachers.own.handle", -1, $Auth->usergroup_id))
					{	
						//$DB->SetTable("auth_users","a");
						$DB->SetTable($this->db_prefix."people","p");
						$DB->AddTable($this->db_prefix."teachers","t");
						$DB->AddTable($this->db_prefix."departments","d");
						$DB->AddCondFS("p.user_id","=",$Auth->user_id);
						$DB->AddCondFF("t.people_id","=","p.id");
						$DB->AddCondFF("t.department_id","=","d.id");
						$DB->AddField("d.name","department_name");
						$DB->AddField("t.department_id","department_id");
						$res = $DB->Select();
						if($row = $DB->FetchAssoc($res))
						{
							$DB->SetTable($this->db_prefix."teachers", "t");
							$DB->AddTable($this->db_prefix."people","p");
							$DB->AddTable($this->db_prefix."departments","d");
							$DB->AddField("d.name","department_name");
							$DB->AddField("d.id","department_id");
							$DB->AddField("p.name","name");
							$DB->AddField("p.last_name","last_name");
							$DB->AddField("p.patronymic","patronymic");
							$DB->AddField("p.comment","comment");
							$DB->AddField("p.exp_teach","exp_teach");
							$DB->AddField("p.exp_full","exp_full");
							$DB->AddField("p.prad","prad");
							$DB->AddField("p.male","male");
							$DB->AddField("p.id","id");
							//$DB->AddField("t.rate","rate");
							//$DB->AddOrder("t.department_id");
							$DB->AddCondFF("p.id","=", "t.people_id");
							$DB->AddCondFF("d.id","=","t.department_id");
							$DB->AddCondFS("t.department_id","=",$row["department_id"]);
							$DB->AddOrder("d.id");
							$DB->AddOrder("p.last_name");
							$res =  $DB->Select();
							while($row2 = $DB->FetchAssoc($res)) {
								$this->output["timetable_teachers"][$row["department_id"]]["dep_name"] = $row2["department_name"]; 
								$this->output["timetable_teachers"][$row["department_id"]]["teachers"][] = $row2;
								$this->output["allow_handle"][$row["department_id"]] = 1;
							}    
							$this->output["current_department_id"] = $row["department_id"];
							$this->output["current_department_name"] = $row["department_name"];
						}
					}
					else {
						$DB->SetTable($this->db_prefix."teachers", "t");
						$DB->AddTable($this->db_prefix."people","p");
						$DB->AddTable($this->db_prefix."departments","d");
						$DB->AddField("d.name","department_name");
						$DB->AddField("d.id","department_id");
						$DB->AddField("d.faculty_id","faculty_id");
						$DB->AddField("p.name","name");
						$DB->AddField("p.last_name","last_name");
						$DB->AddField("p.patronymic","patronymic");
						$DB->AddField("p.comment","comment");
						$DB->AddField("p.exp_teach","exp_teach");
						$DB->AddField("p.exp_full","exp_full");
						$DB->AddField("p.prad","prad");
						$DB->AddField("p.male","male");
						$DB->AddField("p.id","id");
						//$DB->AddField("t.rate","rate");
						//$DB->AddOrder("t.department_id");
						$DB->AddCondFF("p.id","=", "t.people_id");
						$DB->AddCondFF("d.id","=","t.department_id");
						$DB->AddOrder("d.id");
						$DB->AddOrder("p.last_name");
						$res =  $DB->Select();
						while($row = $DB->FetchAssoc($res)) {
							$this->output["timetable_teachers"][$row["department_id"]]["dep_name"] = $row["department_name"]; 
							$this->output["timetable_teachers"][$row["department_id"]]["teachers"][] = $row; 
							if ($Engine->OperationAllowed(5, "teachers.faculty.handle", $row["faculty_id"], $Auth->usergroup_id)) {								
								$this->output["allow_handle"][$row["department_id"]] = 1;
							}
						}
						$this->output["current_department_id"] = 0;
					}
				}
				
				$DB->SetTable($this->db_prefix."faculties");
                $DB->AddOrder("name");
                $res = $DB->Select();
                while($row = $DB->FetchAssoc($res)) {
					$DB->SetTable($this->db_prefix."departments");
                    $DB->AddCondFS("faculty_id", "=", $row["id"]);
					$DB->AddOrder("name");
                    $res2 =  $DB->Select();
                    while($row2 = $DB->FetchAssoc($res2)) {
						if ($Engine->OperationAllowed(5, "teachers.faculty.handle", $row["id"], $Auth->usergroup_id) || $Engine->OperationAllowed(5, "teachers.own.handle", $row2["id"], $Auth->usergroup_id)) {
							$this->output["faculties"][$row["id"]]["fac_name"] = $row["name"];
							$this->output["faculties"][$row["id"]]["departments"][] = $row2;
						}						
                    }   
                }
				break;
            }
        }
    }
	
	function select_teachers($dep_id) {
		global $DB;
		
		$DB->SetTable($this->db_prefix."teachers", "t");
		$DB->AddTable($this->db_prefix."people","p");
		$DB->AddTable($this->db_prefix."departments","d");
		$DB->AddField("d.name","department_name");
		$DB->AddField("d.id","department_id");
		$DB->AddField("d.faculty_id","faculty_id");
		$DB->AddField("p.name","name");
		$DB->AddField("p.last_name","last_name");
		$DB->AddField("p.patronymic","patronymic");
		$DB->AddField("p.comment","comment");
		$DB->AddField("p.exp_teach","exp_teach");
		$DB->AddField("p.exp_full","exp_full");
		$DB->AddField("p.prad","prad");
		$DB->AddField("p.male","male");
		$DB->AddField("p.comment","comment");
		$DB->AddField("p.id","id");
		//$DB->AddField("t.rate","rate");
		$DB->AddCondFF("p.id","=", "t.people_id");
		$DB->AddCondFF("d.id","=","t.department_id");
		$DB->AddCondFS("d.id","=",$dep_id);
		$res =  $DB->Select();
		
		while ($row = $DB->FetchAssoc($res))
			$this->output["select_teachers"][] = $row;
	}
	
	function dep_subjects($dep_id, $module_uri) {
		global $DB;
		
		$DB->SetTable($this->db_prefix."subjects");
		$DB->AddCondFS("department_id", "=", $dep_id);
		$res = $DB->Select();
		
		while ($row = $DB->FetchAssoc($res))
			$subjects[] = $row;

		if (count($module_uri)==1 && !$module_uri[0]) {
			$this->output["subjects"] = $subjects;
			$this->output["mode"] = "dep_subjects";
		}
		elseif (CF::IsIntNumeric($module_uri[0])) {
			$DB->SetTable($this->db_prefix."file_view");
			$DB->AddOrder("pos");
			$res = $DB->Select();
			
			while ($row = $DB->FetchAssoc($res)) {
				$DB->SetTable($this->db_prefix."files_subj");
				$DB->AddCondFS("subject_id", "=", $module_uri[0]);
				$DB->AddCondFS("view_id", "=", $row["id"]);
				$res_file = $DB->Select();
				if ($row_file = $DB->FetchAssoc($res_file))
					$row["file_id"] = $row_file["file_id"];
					
				$this->output["views"][] = $row;
			}
			
			//die(print_r($this->output["views"]));
			
			$DB->SetTable($this->db_prefix."subjects");
			$DB->AddCondFS("id", "=", $module_uri[0]);
			$res = $DB->Select();
			$this->output["subject"] = $DB->FetchAssoc($res);
			
			$DB->SetTable($this->db_prefix."departments");
			$DB->AddCondFS("id", "=", $dep_id);
			$res = $DB->Select();
			$this->output["department"] = $DB->FetchAssoc($res);
			
			$this->output["mode"] = "dep_subj_views";
		}
	}

	function prof_choice() {
		global $DB;
		
		$sql = 'select * from nsau_specialities where code in (select code from nsau_spec_type where type="magistracy")';
		$res = $DB->Exec($sql);
		
		while($row = $DB->FetchAssoc($res))
			$this->output["mag_spec"][] = $row;
		
		$DB->SetTable("nsau_school");
		$res = $DB->Select();
		
		while($row = $DB->FetchAssoc($res))
			$this->output["schools"][] = $row;
		
		$sql = 'select * from nsau_specialities where code in (select code from nsau_spec_type where type="higher" or type="bachelor")';
		$res = $DB->Exec($sql);
		
		while($row = $DB->FetchAssoc($res)) {
			$this->output["high_spec"][$row["direction"]][] = $row;
		}
		//CF::Debug($this->output["high_spec"]);
	}
	
	function rekom() {
		global $DB;
		error_reporting(0);
		$DB->SetTable("nsau_exams");
		$res = $DB->Select();
		$on = array();
		
		while ($row = $DB->FetchAssoc($res))
			$exams[$row["id"]] = $row;
			
		$DB->SetTable("nsau_spec_exams");
		$res = $DB->Select();

		while ($row = $DB->FetchAssoc($res))
			$spec_exams[$row["speciality_code"]][] = $row["exam_id"];
			//die(print_r($_POST));
		if (isset($_POST["ege_rus_on"]) && $_POST["ege_rus_on"]=="true" && $_POST["ege_rus"] > $exams[1]["min_points"])
			$on[] = 1;
		elseif ($exams[1]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_math_on"]) && $_POST["ege_math_on"]=="true" && $_POST["ege_math"] > $exams[2]["min_points"])
			$on[] = 2;
		elseif ($exams[2]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_inf_on"]) && $_POST["ege_inf_on"]=="true" && $_POST["ege_inf"] > $exams[3]["min_points"])
			$on[] = 3;
		elseif ($exams[3]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_hist_on"]) && $_POST["ege_hist_on"]=="true" && $_POST["ege_hist"] > $exams[4]["min_points"])
			$on[] = 4;
		elseif ($exams[4]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_phys_on"]) && $_POST["ege_phys_on"]=="true" && $_POST["ege_phys"] > $exams[5]["min_points"])
			$on[] = 5;
		elseif ($exams[5]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_biol_on"]) && $_POST["ege_biol_on"]=="true" && $_POST["ege_biol"] > $exams[6]["min_points"])
			$on[] = 6;
		elseif ($exams[6]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_chem_on"]) && $_POST["ege_chem_on"]=="true" && $_POST["ege_chem"] > $exams[7]["min_points"])
			$on[] = 7;
		elseif ($exams[7]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_geo_on"]) && $_POST["ege_geo_on"]=="true" && $_POST["ege_geo"] > $exams[8]["min_points"])
			$on[] = 8;
		elseif ($exams[8]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_lang_on"]) && $_POST["ege_lang_on"]=="true" && $_POST["ege_lang"] > $exams[9]["min_points"])
			$on[] = 9;
		elseif ($exams[9]["required"])
			$this->output["decline"] = 1;
		if (isset($_POST["ege_soc_on"]) && $_POST["ege_soc_on"]=="true" && $_POST["ege_soc"] > $exams[10]["min_points"])
			$on[] = 10;
		elseif ($exams[10]["required"])
			$this->output["decline"] = 1;
			
		$DB->SetTable("nsau_specialities");
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res))
			$specialities[$row["code"]] = $row["code"]." - ".$row["name"];
		
		foreach ($spec_exams as $code => $exams_ids) {
			//$exams_ids[] = 1;
			//$exams_ids[] = 2;
			//if (!count(array_diff($exams_ids, $on)))
			$dec = 0;
			foreach ($exams_ids as $eid)
				if (!in_array($eid, $on))
					$dec = 1;
					
			if (!$dec && !in_array($specialities[$code], $this->output["rekoms"]))
				$this->output["rekoms"][] = $specialities[$code];
		}
		
		/*if (in_array(1, $on) && in_array(2, $on) && in_array(6, $on)) {
			$this->output["rekoms"][] = $specialities["110204"];
			$this->output["rekoms"][] = $specialities["110102"];
			$this->output["rekoms"][] = $specialities["110200"];
			$this->output["rekoms"][] = $specialities["110203"];
			$this->output["rekoms"][] = $specialities["250100"];
		}
		elseif (in_array(1, $on) && in_array(2, $on) && in_array(8, $on))
			$this->output["rekoms"][] = $specialities["250100"];*/
			
			//die(print_r($this->output["rekoms"]));
		
		if (/*!isset($this->output["rekoms"]) || */!is_array($this->output["rekoms"]) || !count($this->output["rekoms"]))
			$this->output["decline"] = 1;
	}
	
	function parsexls() {
		
	}
	function timetable_weekparity() {			//четность текущей недели
		$today = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$first = mktime(0, 0, 0, 9, 1, (date("m") < 9 ? date("Y") - 1 : date("Y")));
		$first_week = date("w", mktime(0, 0, 0, 9, 1, (date("m") < 9 ? date("Y") - 1 : date("Y"))));
		$day = ($today - $first)/(24*60*60);
		$day += $first_week == 0 ? 6 : $first_week - 1;
		$j = (($day - ($day % 7)) / 7) + 1;
		if($j%2 == 1) return "odd"; 					//нечетная
		else return "even";						//четная
	}
    function Output()
    {
        return $this->output;
    }

}
?>