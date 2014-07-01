<?php
class EAbit
// version: 1.16
// date: 2014-07-01
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
    
    static $basic_mode_specs_codes = array(62, 65);
    static $spo_mode_specs_codes = array(51); 
    static $magistracy_mode_specs_codes = array(68);
    
    var $isMagistracy = false; 
    
    /*
    В params передаём: 	1-й параметр: режим работы ( 'list' - выдача списка абитуриентов, 
    												'request' - выдача информации о количестве поданных заявлений, 
    												'edit_spec' - редактирование данных о количестве мест на специальностях);
     					2-й параметр: в режиме 'list' - id статуса Абитуриент
    */   
    function EAbit($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
    {
        global $DB, $Engine, $Auth;
        $this->module_id = $this->output["module_id"] = $module_id;
        $this->node_id = $node_id;
        $this->module_uri = $module_uri;
        $this->additional = $additional;
        
        $parts = explode(";", $global_params);
        $this->db_prefix = $parts[0];
        
        $this->isMagistracy = ($parts[2] == "magistracy" ? true : false);
        
        $this->output = array();
		$this->output["module_id"] = $this->module_id;
        $this->output["messages"] = array("good" => array(), "bad" => array());
        
        $parts = explode(";", $params);
        $this->output["scripts_mode"] = $this->output["mode"] = $this->mode = $parts[0];
        $this->output["abitprogressfile"] = $abitProgressFile = '/files/abit.tmp';
        $this->output['plugins'][] = 'jquery.form';
		
        switch ($this->mode) {
        	case "abitfile_processing":
        		 
        		$f = fopen($_SERVER['DOCUMENT_ROOT'].$abitProgressFile, 'w');
				fwrite($f, 2);
				fclose($f);
					
				$time0 = time();
				set_time_limit(0);
				if (isset($_POST["type"]) && $_POST["type"]=="spo") {
					$sql = "CALL ab22_spo(51);";
				}
				elseif (isset($_POST["type"]) && $_POST["type"]=="spo-ex") {
					$sql = "CALL ab23_spo(51, 'заочная');";
				} 
				else if (isset($_POST["type"]) && $_POST["type"]=="vpo") {
					$sql = "CALL ab22('очная');";
				} 
				else if (isset($_POST["type"]) && $_POST["type"]=="vpo_ev") {
					$sql = "CALL ab22('вечерняя');";
				} 
				else if (isset($_POST["type"]) && $_POST["type"]=="izop") {
					$sql = "CALL ab22_izop('заочная');";
				} else {
					//$sql = "CALL ab23_spo();";
				}
						
				if ($DB->Exec($sql)) {
					$workInterval = time()-$time0; 
					$min = floor($workInterval/60); $sec = $workInterval - $min*60;
					$workInterval = $min.' мин. '.$sec.' сек.';
					$this->output["messages"]["good"][] = "Абитуриенты загружены";
					if (isset($_POST["type"]) && $_POST["type"]=="spo") {
						$Engine->LogAction($this->module_id, "spofile", -1, "upload_spo - ".$workInterval);
					} else if (isset($_POST["type"]) && $_POST["type"]=="spo-ex") {
						$Engine->LogAction($this->module_id, "vpofile", -1, "upload_spo_ex - ".$workInterval);
					} else if (isset($_POST["type"]) && $_POST["type"]=="vpo") {
						$Engine->LogAction($this->module_id, "vpofile", -1, "upload_vpo - ".$workInterval);
					} else if (isset($_POST["type"]) && $_POST["type"]=="vpo_ev") {
						$Engine->LogAction($this->module_id, "vpofile", -1, "upload_vpo_ev - ".$workInterval);
					} else if (isset($_POST["type"]) && $_POST["type"]=="izop") {
						$Engine->LogAction($this->module_id, "izopfile", -1, "upload_izop - ".$workInterval);
					} else {
						$Engine->LogAction($this->module_id, "fullfile", -1, "upload_full - ".$workInterval);
					}
          
          $DB->SetTable("engine_dbupdates_log"); 
          $DB->AddValue("update_time", "NOW()", "X"); 
          $DB->AddCondFS("name", "=", "nsau_spec_abit"); 
          $DB->Update(1);   
				} else {
					$this->output["messages"]["bad"][] = "Процедура вернула ошибку: ".$DB->Error();
				}
							
				
				$f = fopen($_SERVER['DOCUMENT_ROOT'].$abitProgressFile, 'w');
				fwrite($f, '');
				fclose($f);
        	break;
        	
        	case "ajax_abitfile" : 
        	
			case "abitfile": {
										
				$this->output["abitfile"] = 1;
				if($Engine->OperationAllowed($this->module_id, "places_info.handle.vpo", -1, $Auth->usergroup_id)) {
					$this->output["handle_vpo"] = 1;
				}
				if($Engine->OperationAllowed($this->module_id, "places_info.handle.izop", -1, $Auth->usergroup_id)) {
					$this->output["handle_izop"] = 1;
				}
				if($Engine->OperationAllowed($this->module_id, "places_info.handle.spo", -1, $Auth->usergroup_id)) {
					$this->output["handle_spo"] = 1;
				}
				if (isset($_FILES['abit'])) { 
					while (file_get_contents($_SERVER['DOCUMENT_ROOT'].$abitProgressFile)) {
					}  
					$fname = explode(".", $_FILES['abit']['name']);
					if ($fname[count($fname)-1] != "csv") {
						$this->output["messages"]["bad"][] = "Необходим файл в формате csv";
					} else {
						$f = fopen($_SERVER['DOCUMENT_ROOT'].$abitProgressFile, 'w');
						fwrite($f, 1);
						fclose($f);
						$f = fopen($_FILES['abit']['tmp_name'], "r");
						$content = fread($f, filesize($_FILES['abit']['tmp_name']));
						if (!file_exists($_SERVER['DOCUMENT_ROOT'].'/files/abit')) {
							chdir($_SERVER['DOCUMENT_ROOT'].'/files');
							mkdir('abit');
						} 
						$df = fopen($_SERVER['DOCUMENT_ROOT'].'/files/abit/'.date('j-m-y-h-i-s').'.csv', "w+");
						fwrite($df, $content);
						fclose($df);
						fclose($f);			
						$parts = explode("\r\n", $content);
						$headers = explode(";", $parts[0]);
						array_shift($parts);
						$DB->Exec("delete from abit_temp where 1");
						
						$studyForms = array('spo'=> 'спо', 'vpo_ev'=>'вечерняя', 'vpo'=> 'очная', 'izop' => 'заочная', 'spo-ex' => 'заочная');
						$curForm = isset($studyForms[$_POST["type"]]) ? $studyForms[$_POST["type"]] : '';
						$toobad = 0;
						foreach ($parts as $ind => $part) {
							if ($part) {
								$string = explode(";", $part);
								if (count($string) > 1 && count($string) != 17 && count($string) != 24 && !$toobad) {
									$this->output["messages"]["bad"][] = "Неверное количество полей: ".count($string)." вместо 24";
									$toobad = 1;
								} elseif (count($string) > 1 && !$toobad) {
									$fieldNames = array("f1" => 1, "f2" => 2, "f3" => 3/*, "fd" => 8*/, "f5" => 9, "f6" => 10);
									$DB->SetTable("abit_temp");
									foreach($fieldNames as $name => $ind2) {
										//$DB->AddCondFS($name, "=", CF::win2utf($string[$ind]));
										$DB->AddCondFS($name, "=", ($string[$ind2]));
									}
									//echo $DB->SelectQuery(); 
									$res = $DB->Select(null, null, false, false);
									if($row = $DB->FetchArray($res)) {
										//echo $DB->DeleteQuery();
										$DB->Delete();
									}
									//exit;
									//$DB->Delete();
									$fieldNames = array("fp", "f1", "f2", "f3", "f4", /*(isset($_POST["type"]) && $_POST["type"]=="vpo") ? */"fz" /*: "fid"*/, "fdog", "fn", "fd", "f5", "f6", "fs1", "fs2", "fout", "ftook", "fcl", "fform", "fzach", "fent", "fex1", "fex2", "fex3", "fgain", "fqual");
									$DB->SetTable("abit_temp");
									foreach($fieldNames as $ind2 => $fName) {
										if ($ind2 < 4 && !$string[$ind2]) {
											$this->output["messages"]["bad"][] = "Не заполнено поле ".$headers[$ind2]." в строке ".(++$ind);
											$toobad = 1;
											$DB->Init(); 
											break 2;
										}
										if ($ind2==13 || $ind2==14)
											$string[$ind2] = ($string[$ind2] == "ЛОЖЬ" ? 0 : 1);
										else if ($ind2 == 15)
											$string[$ind2] = str_replace('\'', '"', str_replace('"', '',str_replace('""','\'', $string[$ind2])));	
										else if ($ind2 == 16) {
											$string[$ind2] = strtolower_ru($string[$ind2]);
											if ($string[$ind2] != $curForm) {
												$this->output["messages"]["bad"][] = 'Данные списка не совпадают с указанной для него формой обучения';
												$toobad = 1;
												$DB->Init();
												break 2;
											}
										}
										$DB->AddValue($fName, $string[$ind2]);
									}//echo $DB->InsertQuery();exit;
									
									/*$DB->AddValue("fp", $string[0]);
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
									
									if ($string[13] == "ЛОЖЬ") {
										$DB->AddValue("fout", "0");
									} else {
										$DB->AddValue("fout", "1");
									}
										
									if ($string[14] == "ЛОЖЬ") {
										$DB->AddValue("ftook", "0");
									} else {
										$DB->AddValue("ftook", "1");
									}*/
									
									if (!$DB->Insert()) {
										$this->output["messages"]["bad"][] = "Не удалось добавить абитуриента ".$string[3];
									}
								}
							}
						}
						
						if (!$toobad) {
							/*$f = fopen($_SERVER['DOCUMENT_ROOT'].$abitProgressFile, 'w');   ajax-вариант обработки
							fwrite($f, $_POST["type"]);
							fclose($f);
						
							CF::Redirect($Engine->uri);
							$f = fopen($_SERVER['DOCUMENT_ROOT'].$abitProgressFile, 'w');
							fwrite($f, 2);
							fclose($f); */
							$time0 = time();
							set_time_limit(0);
							if (isset($_POST["type"]) && $_POST["type"]=="spo") {
								$sql = "CALL ab22_spo(51);";
							}
							elseif (isset($_POST["type"]) && $_POST["type"]=="spo-ex") {
								$sql = "CALL ab23_spo(51, 'заочная');";
							} 
							else if (isset($_POST["type"]) && $_POST["type"]=="vpo") {
								$sql = "CALL ab22('очная');";
							} 
							else if (isset($_POST["type"]) && $_POST["type"]=="vpo_ev") {
								$sql = "CALL ab22('вечерняя');";
							}
							else if (isset($_POST["type"]) && $_POST["type"]=="izop") {
								$sql = "CALL ab23_izop('заочная');";
							} else {
								//$sql = "CALL ab23_spo();";
							}
						
							if ($DB->Exec($sql)) {
								$workInterval = time()-$time0; 
								$min = floor($workInterval/60); $sec = $workInterval - $min*60;
								$workInterval = $min.' мин. '.$sec.' сек.';
								$this->output["messages"]["good"][] = "Абитуриенты загружены";
								if (isset($_POST["type"]) && $_POST["type"]=="spo") {
									$Engine->LogAction($this->module_id, "spofile", -1, "upload_spo - ".$workInterval);
								} else if (isset($_POST["type"]) && $_POST["type"]=="spo-ex") {
									$Engine->LogAction($this->module_id, "spofile", -1, "upload_spo_ex - ".$workInterval);
								} else if (isset($_POST["type"]) && $_POST["type"]=="vpo") {
									$Engine->LogAction($this->module_id, "vpofile", -1, "upload_vpo - ".$workInterval);
								} else if (isset($_POST["type"]) && $_POST["type"]=="vpo_ev") {
									$Engine->LogAction($this->module_id, "vpofile", -1, "upload_vpo_ev - ".$workInterval);
								} else if (isset($_POST["type"]) && $_POST["type"]=="izop") {
									$Engine->LogAction($this->module_id, "izopfile", -1, "upload_izop - ".$workInterval);
								} else {
									$Engine->LogAction($this->module_id, "fullfile", -1, "upload_full - ".$workInterval);
								} 
                
                $DB->SetTable("engine_dbupdates_log"); 
                $DB->AddValue("update_time", "NOW()", "X"); 
                $DB->AddCondFS("name", "=", "nsau_spec_abit");
                $DB->Update(1); 
							} else {
								$this->output["messages"]["bad"][] = "Процедура вернула ошибку: ".$DB->Error();
							}
							
						}
						$f = fopen($_SERVER['DOCUMENT_ROOT'].$abitProgressFile, 'w');
						fwrite($f, '');
						fclose($f);

					}
				}
			}
			break;
			
			case "list": {
				$this->status_id = $parts[1];
				if (isset($parts[2])) {
					if ($parts[2] == "spo")
						$this->People(1, 0);
					else if ($parts[2] == "spo-ex")
						$this->People(1,0,1);
					else if ($parts[2] == "vpo-ev")
						$this->People(0,1);
          else if ($parts[2] == "magistracy")
						$this->People(0,0,0,1);
				} else 
        			$this->People(0,0);
			}
			break;
                    
			case "request": {
				$this->GetRequestsInfo(isset($parts[2])?$parts[2]:'all');
			}
			break;
				
			case "edit_spec": {
				if (isset($_POST['data_str'])) {
					$this->UpdateSpecsInfo($_POST['data_str']);
				}
        if(isset($_POST["mode"]) && $_POST["mode"] == "add") {
      $DB->SetTable("nsau_spec_type");
      $DB->AddCondFS("code", "=", trim($_POST["code"]));
      $res = $DB->Select(1);
      $row = $DB->FetchObject();
      if(!$row) {
        $this->output["messages"]["bad"][] = "Нет специальности с таким кодом"; 
      }
      else {
        $DB->SetTable($this->db_prefix."types");
        $DB->AddValues(array("code" => $_POST["code"], "type" => $_POST["type"], "qual_id" => $_POST["qualification"]));
        if(!empty($_POST["budget"])) $DB->AddValue("budget", $_POST["budget"]);
        if(!empty($_POST["purpose"])) $DB->AddValue("purpose", $_POST["purpose"]);
        if(!empty($_POST["commerce"])) $DB->AddValue("commerce", $_POST["commerce"]);
        if(!empty($_POST["sortorder"])) $DB->AddValue("sortorder", $_POST["sortorder"]);
        //$DB->AddValue("pid", $_POST["parent"]);
  			  
        if($DB->Insert()) {
          CF::Redirect($Engine->engine_uri);
        }
        else {
          $this->output["messages"]["bad"][] = "Не удалось добавить направление"; 
        }
        //
      }
		}
    
        $DB->SetTable("nsau_faculties"); 
        $res = $DB->Select();
        
        $this->output["faculties"] = array(); 
        
        while($row = $DB->FetchObject()) {
          $this->output["faculties"][] = $row; 
        }
        
        $DB->SetTable($this->db_prefix . "qualifications"); 
        $res = $DB->Select();
        
        $this->output["qualifications"] = array(); 
        
        while($row = $DB->FetchObject()) {
          $this->output["qualifications"][] = $row; 
        }
        
				$this->GetSpecsInfo();
			}
			break;
					
			case "daystogo": {
				$this->DaysToGo();
			}
			break;
				
			default: {
				die("EMenu module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
			}
			break;
		}
	}
	
	function People($isSpo = 0, $isEvening = 0, $isExt = 0, $isMagistracy = 0)
	{
		global $DB;
		$this->output['isEvening'] = $isEvening;
		$this->output['isExt'] = $isExt;
		$this->TypesList($isSpo, $isEvening, $isExt, $isMagistracy);
		/*$DB->SetTable($this->db_prefix."people", "p");
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
		$DB->AddField("a.form");
		$DB->AddField("s.name", "s_name");
		$DB->AddField("p.id");
		$DB->AddField("p.last_name");
		$DB->AddField("p.name");
		$DB->AddField("p.patronymic");
		//$DB->AddCondFS("a.position", "=", 3);
		$DB->AddCondFF("p.id","=","a.people_id");
		$DB->AddCondFF("s.code","=","a.speciality_id");
		$DB->AddCondFS("p.status_id","=",$this->status_id);
		//$DB->AddCondFS("a.qualification","=",51);
		//$DB->AddCondFS("a.speciality_id","=","080114"); 
		$DB->AddCondFS("a.out","=","0");
		$DB->AddCondFS("a.took","=","0");

		$DB->AddOrder("a.type");

		if(isset($_GET["type"]))
		{
			$DB->AddTable($this->db_prefix."abit_types", "t");
			$DB->AddField("t.descr");
			//$DB->AddCondFF("t.position","=","a.position");
			$DB->AddCondFS("a.type_id","=",$_GET["type"]);
			$DB->AddOrder("a.position");
  		}

		//$DB->AddOrder("a.notice", 1);
		//$DB->AddGrouping("p.id");
		$DB->AddOrder("a.status1");
		$DB->AddOrder("a.dogovor", 1);
		//$DB->AddOrder("a.acceptance");
		$DB->AddOrder("a.ege", 1);
		$DB->AddOrder("a.documents", 1);
		$DB->AddOrder("a.status2", 1);
		$DB->AddOrder("p.last_name");*/
		
		if (isset($_GET["type"]))
			$sql = "select p.*, a.*, t.*, coalesce(q.name, a.qual_name) as qual_name from nsau_people p, nsau_spec_abit a left join nsau_abit_types t on t.position=a.position left join ".$this->db_prefix."qualifications q on q.id=a.qualification where a.out=0 and a.took=0 and a.form='".($isEvening ? 'вечерняя' : ($isSpo ? ($isExt ? 'заочная' : 'спо') : 'очная'))."' and a.people_id=p.id and a.type_id=".$_GET["type"]." and a.speciality_id ".($isSpo ? " REGEXP '[[:alnum:]]{2}\.02\.[[:alnum:]]{2}' " : " NOT REGEXP '[[:alnum:]]{2}\.02\.[[:alnum:]]{2}' ")." order by a.position, a.status1/*, a.dogovor desc*/, replace(a.ege, ',', '.') + 0.0 desc, a.status2 desc, p.last_name";
		else
			$sql = "select * from nsau_people p, nsau_spec_abit a left join nsau_abit_types t on t.position=a.position where a.out=0 and a.took=0 and a.form='".($isEvening ? 'вечерняя' : ($isSpo ? ($isExt ? 'заочная' : 'спо') : 'очная'))."' and a.people_id=p.id  group by p.last_name, p.name, p.patronymic order by a.position, a.status1/*, a.dogovor desc*/, (replace(a.ege, ',', '.') + 0.0) desc, a.status2 desc, p.last_name";
		
		//echo $sql; 
    /*$DB->SetTable("nsau_people", "p");
		$DB->AddTable("nsau_spec_abit", "a");
		$DB->AddCondFF("a.people_id", "=", "p.id");
		$DB->AddCondFS("p.status_id","=",$this->status_id);
		$DB->AddCondFS("a.out","=","0");
		$DB->AddCondFS("a.took","=","0");*/
		
		//$DB->AddGroupings("last_name", "name", "patronymic");
		
		//$DB->AddOrder("a.type");
/*		if (isset($_GET["type"])) {
			$DB->AddCondFS("a.type_id", "=", $_GET["type"]);
			$DB->AddOrder("a.position");
		}*/
		
		//echo $sql;//die($DB->SelectQuery());
        //$res = $DB->Select();
		$res = $DB->Exec($sql);
        
        while($row = $DB->FetchAssoc($res))
        {
            if($row["ent"] == '') $row["ent"] = 'Экзамены';
            $this->output["people"][$row["ent"]][] = $row;    
        }
        //die(print_r($this->output["people"])); 
        if(isset($_GET["type"]))
		{
			$DB->SetTable($this->db_prefix."types");
			$DB->AddExp("*");
			if($isSpo && $isExt) 
				$DB->AddField("budget_ext", "budget");
			$DB->AddCondFS("id","=",$_GET["type"]);
			$res = $DB->Select(1);
			if($row = $DB->FetchAssoc($res))
			{
        		$this->output["type"] = $row;
            
            $this->output["exams"] = array();  
            
            $DB->SetTable($this->db_prefix . "exams", "e");
            $DB->AddTable($this->db_prefix . "spec_exams", "se");
            $DB->AddTable($this->db_prefix . "spec_type", "st"); 
            $DB->AddJoin($this->db_prefix . "qualifications q.id", "se.qual_id", "nsau_spec_exams"); 
            $DB->AddCondFS("se.speciality_code", "=", $row["code"]); 
            $DB->AddCondFF("se.exam_id", "=", "e.id");
            $DB->AddCondFF("se.spec_type_id", "=", "st.id");
            $DB->AddExp('e.*'); 
            $DB->AddField("st.qual_id");
            $DB->AddField("q.name", "qual_name");  
            //echo $DB->SelectQuery(); 
            
            $res_exams = $DB->Select(); 
            
            while($row_exams = $DB->FetchAssoc($res_exams)) {
              $this->output["exams"][$row_exams['id']] = $row_exams;
            } 
        	}        		
  		}
      
      $this->output["is_magistracy"] = $isMagistracy;
      $this->output["is_spo"] = $isSpo;
  		$this->output["date"] = $this->getUpdateDate(true);
	}
	
	function TypesList($isSpo, $isEvening, $isExt = 0, $isMagistracy = 0)
	{
		global $DB;
		$DB->SetTable($this->db_prefix."types", "t");
		//$DB->AddCondFS("t.code", $isSpo ? "LIKE" : "NOT LIKE", "%.51");
    $DB->AddCondFS("s.type", $isSpo ? "=" : "!=", "secondary");
    $DB->AddCondFS("s.type", $isMagistracy ? "=" : "!=", "magistracy");
		if ($isEvening) 
			$DB->AddCondFS("t.is_evening", "=", 1);
		if ($isExt)
			$DB->AddCondFS("t.has_ext", "=", 1);
		$DB->AddOrder("t.code"); 
    $DB->AddTable("nsau_spec_type", "s");
    $DB->AddExp("t.*");
    $DB->AddCondFF("t.code", "=", "s.code"); 
    $DB->AddCondFS("s.has_vacancies", "=", "1");
    
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res))
		{
        	$this->output["types"][$row["id"]] = $row;
        }		
	}
	
	
	function GetSpecsInfo() {
		global $DB, $Engine, $Auth;
		
		$DB->SetTable($this->db_prefix."types");
		$DB->AddField($this->db_prefix  ."types.id");
		$DB->AddField($this->db_prefix  ."types.code");
		$DB->AddField($this->db_prefix  ."types.type");
		$DB->AddField($this->db_prefix  ."types.has_ext");
		$DB->AddField($this->db_prefix  ."types.budget");
		$DB->AddField($this->db_prefix  ."types.budget_ext");
		$DB->AddField($this->db_prefix  ."types.commerce");
		$DB->AddField($this->db_prefix  ."types.purpose");
		
    //$DB->AddJoin( $this->db_prefix  . "specialities.code", "code", $this->db_prefix."types");
    $DB->AddTable($this->db_prefix  . "specialities"); 
    $DB->AddField($this->db_prefix  ."specialities.id", "spec_id");
    $DB->AddJoin( $this->db_prefix  . "faculties.id", $this->db_prefix."specialities.id_faculty", $this->db_prefix."specialities");
		$DB->AddField( $this->db_prefix  . "faculties.name", "faculty_name");
		$DB->AddOrder($this->db_prefix  . "faculties.pos");
    
    $DB->AddTable("nsau_spec_type");
    $DB->AddCondFF($this->db_prefix."types.code", "=", "nsau_spec_type.code");
    $DB->AddCondFF($this->db_prefix."specialities.code", "=", "nsau_spec_type.code");  
    $DB->AddCondFS("nsau_spec_type.has_vacancies", "=", "1");
    
    //echo $DB->SelectQuery(); 
		
		$res = $DB->Select();
		
		$rows = array();
		$sum = array("has_ext" => false, "budget"=>0, "budget_ext" => 0, "commerce"=>0, "purpose"=>0);
		while($row = $DB->FetchObject())
		{	
			if (!isset($rows[$row->faculty_name])) 
				$rows[$row->faculty_name] = array("specs"=>array(), "has_ext" => false, "budget"=>0, "budget_ext" => 0, "commerce"=>0, "purpose"=>0);
			$rows[$row->faculty_name]["specs"][$row->id] = $row;
        	$rows[$row->faculty_name]["budget"] += $row->budget;
        	$rows[$row->faculty_name]["budget_ext"] += $row->budget_ext;
        	$rows[$row->faculty_name]["commerce"] += $row->commerce;
       	  	$rows[$row->faculty_name]["purpose"] += $row->purpose;
       	  	$rows[$row->faculty_name]["has_ext"] |= $row->has_ext;
       	  	$sum["budget"] += $row->budget;
       	  	$sum["budget_ext"] += $row->budget_ext;
        	$sum["commerce"] += $row->commerce;
        	$sum["purpose"] += $row->purpose;
        	$sum["has_ext"] |= $row->has_ext;
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
		$eveningSpecsPlaceCount = 25;
		$DB->SetTable($this->db_prefix."types");
    
		$DB->AddField($this->db_prefix  ."types.id");
		$DB->AddField($this->db_prefix  ."types.code");
		$DB->AddField($this->db_prefix  ."types.type");
		if($request_mode != "spo-ex")
			$DB->AddField($this->db_prefix  ."types.budget");
		else 
			$DB->AddField($this->db_prefix  ."types.budget_ext", "budget");
		$DB->AddField($this->db_prefix  ."types.commerce");
		//$DB->AddJoin( $this->db_prefix  . "faculties.id", "faculty_id", $this->db_prefix."types");
    $DB->AddTable($this->db_prefix  . "spec_type");
		$DB->AddField( $this->db_prefix  . "faculties.name", "faculty_name");
    
    $DB->AddTable($this->db_prefix  . "specialities"); 
    $DB->AddField($this->db_prefix  ."specialities.id", "spec_id");
    $DB->AddCondFF($this->db_prefix."specialities.code", "=", "nsau_spec_type.code"); 
    $DB->AddJoin( $this->db_prefix  . "faculties.id", $this->db_prefix."specialities.id_faculty", $this->db_prefix."specialities");
    $DB->AddField( $this->db_prefix  . "faculties.name", "faculty_name");
		$DB->AddOrder($this->db_prefix  . "faculties.pos"); 
    
    $DB->AddCondFF($this->db_prefix  ."types.code", "=", $this->db_prefix  . "spec_type.code");
    $DB->AddCondFS($this->db_prefix  . "spec_type.has_vacancies", "=", 1);  
		
		switch($request_mode){
			case 'basic': {
				foreach (self::$basic_mode_specs_codes as $spec_code) {
					//$DB->AddAltFS("code", " LIKE ", "%".$spec_code);
          //$DB->AddAltFS("nsau_spec_type.type_code", "=", $spec_code);
          $DB->AddAltFS($this->db_prefix  . "spec_type.type", "=", "higher"); 
          $DB->AddAltFS($this->db_prefix  . "spec_type.type", "=", "bachelor"); 
				}
				
			}
			break;
			
			case 'vpo-ev': {
				foreach (self::$basic_mode_specs_codes as $spec_code) {
					//$DB->AddAltFS("code", " LIKE ", "%".$spec_code);
          $DB->AddAltFS($this->db_prefix  . "spec_type.type_code", "=", $spec_code);
          $DB->AddAltFS($this->db_prefix  . "spec_type.type", "=", "higher"); 
          $DB->AddAltFS($this->db_prefix  . "spec_type.type", "=", "bachelor"); 
				}
				$DB->AddCondFS("is_evening", "=", 1);
				
			}
			break;
			
			case 'spo': {
				foreach (self::$spo_mode_specs_codes as $spec_code) {
					//$DB->AddAltFS("code", " LIKE ", "%".$spec_code);
          //$DB->AddAltFS("nsau_spec_type.type_code", "=", $spec_code);
          $DB->AddAltFS($this->db_prefix  . "spec_type.type", "=", "secondary"); 
				}
			}
			break;
			
			case 'spo-ex': {
				foreach (self::$spo_mode_specs_codes as $spec_code) {
					//$DB->AddAltFS("code", " LIKE ", "%".$spec_code);
          //$DB->AddAltFS("nsau_spec_type.type_code", "=", $spec_code);
          $DB->AddAltFS($this->db_prefix  . "spec_type.type", "=", "secondary"); 
				}
				$DB->AddCondFS("has_ext", "=", 1);
			}
			break; 
      
      case 'magistracy': {
				foreach (self::$magistracy_mode_specs_codes as $spec_code) {
					//$DB->AddAltFS("code", " LIKE ", "%".$spec_code);
          //$DB->AddAltFS("nsau_spec_type.type_code", "=", $spec_code);
          $DB->AddAltFS($this->db_prefix  . "spec_type.type", "=", "magistracy"); 
				}
				
			}
			break;
			
			default: {
				$request_mode = 'all';
			}
		}
    //echo $DB->SelectQuery();
		
		$DB->AddOrder($this->db_prefix  . "faculties.pos");
		$DB->AddOrder($this->db_prefix  . "types.code");
		
		//echo $DB->SelectQuery(); 
		$res = $DB->Select(); 
		
		$rows = array();
		$codes = array();
		while($row = $DB->FetchObject()) {
			
			$rows[$row->id] = $row;
			$codes[$row->id] = $row->code;
			$row->req_count = 0;
		}
		$DB->FreeRes();
				
		$DB->SetTable($this->db_prefix."spec_abit");
		$DB->AddExp("count(*)", "req_count");
		$DB->AddJoin( $this->db_prefix  . "types.id", "type_id", $this->db_prefix."spec_abit");
		$DB->AddField($this->db_prefix  ."types.code");
		$DB->AddJoin( $this->db_prefix  . "faculties.id", $this->db_prefix  ."types.faculty_id", $this->db_prefix."spec_abit");
    /*$DB->AddTable("nsau_spec_type");
    $DB->AddCondFF($this->db_prefix  ."types.code", "=", "nsau_spec_type.code");
    $DB->AddCondFS("nsau_spec_type.has_vacancies", "=", 1); 
    $DB->AddTable("nsau_qualifications");
    //$DB->AddCondFF($this->db_prefix."spec_abit.qual_name", "=", "nsau_qualifications.name");
    //$DB->AddCondFF("nsau_spec_type.qual_id", "=", "nsau_qualifications.id");
    $DB->AddCondFF($this->db_prefix."spec_abit.qualification", "=", "nsau_qualifications.id");
    $DB->AddCondFF("nsau_spec_type.qual_id", "=", $this->db_prefix."spec_abit.qualification");*/
    
		if ($request_mode == 'vpo-ev')
			$DB->AddCondFS("form", "=", "вечерняя");
		else if ($request_mode == 'spo')
			$DB->AddCondFS("form", "=", "спо");
		else if ($request_mode == 'spo-ex') {
			$DB->AddCondFS("form", "=", "заочная");
			//$DB->AddCondFS("qualification", "=", "51");
      $DB->AddCondFS("nsau_spec_type.type", "=", "secondary");
		}
		else if ($request_mode == 'izop') {
			$DB->AddCondFS("form", "=", "заочная");
			//$DB->AddCondFS("qualification", "!=", "51");
      $DB->AddCondFS("nsau_spec_type.type", "!=", "secondary");
		}
		else
			$DB->AddCondFS("form", "=", "очная");
		$DB->AddGrouping( "type_id");
		/*$sql = $DB->SelectQuery();
		$sql_parts = explode("GROUP BY", $sql);
		$sql_parts[0] .= " WHERE (qualification=51 and people_id not in (select people_id from nsau_spec_abit where qualification!=51)) or qualification!=51 ";*/
		
		//echo $DB->SelectQuery(); 
		$res = $DB->Select();
		
		//$sql = implode("GROUP BY", $sql_parts);
		
		//$res = $DB->Exec($sql);
		
		while($row = $DB->FetchObject()) {
			foreach ($codes as $ind=>$code) {
				if ($row->code == $code) {
					$rows[$ind]->req_count = $row->req_count;
				}
			}
		}
		$DB->FreeRes();
		
		foreach ($rows as $row) {			
		if ($request_mode == 'vpo-ev') {
			$row->commerce = $eveningSpecsPlaceCount;
			$row->budget = 0;
		}
			$row->contest = round($row->req_count/($row->budget/*+$row->commerce*/),2);
        	$this->output["requests_info"][$row->faculty_name]["specs"][] = $row;
        	$this->output["requests_info"][$row->faculty_name]["budget"] += $row->budget;
        	$this->output["requests_info"][$row->faculty_name]["commerce"] += $row->commerce;
        	$sum["budget"] += $row->budget;
        	$sum["commerce"] += $row->commerce;
        	$this->output["requests_info"][$row->faculty_name]["req_count"] += $row->req_count;
        	$sum["req_count"] +=  $row->req_count;
        	$this->output["requests_info"][$row->faculty_name]["contest"] = round($this->output["requests_info"][$row->faculty_name]["req_count"]/($this->output["requests_info"][$row->faculty_name]["budget"]/*+$this->output["requests_info"][$row->faculty_name]["commerce"]*/), 2);  
        }
        $sum["contest"] = round($sum["req_count"]/($sum["budget"]/*+$sum["commerce"]*/), 2);
        $this->output["requests_info"]["sum"] = $sum;
        
        $DB->FreeRes();
		
		$this->output["abit_list_base_href"] = preg_replace('/request\S+/', 'list'.($request_mode == 'spo' ? '-spo' : ($request_mode == 'vpo-ev' ? '-vpo-ev' : ($request_mode == 'spo-ex' ? '-spo-ex' : '')/*''*/)).'/', $Engine->engine_uri)."?type=";	
		$this->output["request_mode"] = $request_mode;
		$this->output["date"] = $this->getUpdateDate();
	}
	
	
	function getUpdateDate($with_time = false) {
		global $DB;
		$DB->Init();
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
		global $DB, $Auth, $Engine;
    
		$data_parts = explode(';', $post_data);
		array_pop($data_parts);
		foreach($data_parts as $data_part) {
			list($key, $value) = explode('=', $data_part);
			if (preg_match('/\-/', $key)) { 
				$key_parts = preg_split('/\-/', $key);
				$field = $key_parts[0];
				$id = $key_parts[1]; 
				$DB->SetTable($this->db_prefix."types");
				$DB->AddCondFS("id", "=", $id);
				$DB->AddValue($field, $value);
				$res = $DB->Update();
				$DB->FreeRes();
			}
		}
		CF::Redirect($Engine->engine_uri);
	}
	
	function DaysToGo() {
		
		//$changetime1 = mktime(0,0,0,8,15,2012);
		
		$curtime = time();
		
		$changepoints = array(
			
			array('notice'=>'До окончания приёма<br /> документов осталось', 'time'=> mktime(0,0,0,7,27,2012), 'show_mode' => 'calendar'),
			array('notice'=>'До окончания приёма<br /> документов по СПО осталось', 'time'=> mktime(0,0,0,8,15,2012), 'show_mode' => 'calendar'),
			array('notice'=>'До завершения приёма<br /> документов на СПО осталось', 'time'=> mktime(0,0,0,8,27,2013), 'show_mode' => 'calendar'),
			array('notice'=>'Оригиналы документов по СПО принимаются до 22.08.2012 17:00', 'time'=> mktime(17,0,0,8,22,2012), 'show_mode' => 'text'),
			array('notice'=>'Начало занятий с 3.09 по расписанию', 'time'=> mktime(0,0,0,9,22,2012), 'show_mode' => 'text'),
	

		);

		foreach($changepoints as $ind=>$changepoint) {
			
			if ($curtime < $changepoint['time']) {
				

				$this->output["show_mode"] = $changepoint['show_mode'];

				if ($this->output["show_mode"] == 'calendar') {
					$secstogo = $changepoint['time'] - $curtime;
					$daystogo = (int)($secstogo/(60*60*24));
					$ost = $daystogo%10;
					if ($ost==1 && ($daystogo < 10 || $daystogo > 20))
						$word = "день";
					elseif (($ost==2 || $ost==3 || $ost==4) && ($daystogo < 10 || $daystogo > 20))
						$word = "дня";
					else
						$word = "дней";
					$this->output['word'] = $word;
					$this->output["days"] = $daystogo;
				}
				$this->output["notice"] = $changepoint['notice'];
				$this->output["has_info"] = 1;
				return;
			}
		}
		
		/*if ($curtime < $changetime1 && $daystogo)
			$this->output["daystogo"] = "До окончания приёма<br /> документов осталось";
		else 
			$this->output["daystogo"] = "Оригиналы документов принимаются до 09.08 13:00";
		$this->output["word"] = $word;
		$this->output["days"] = $daystogo;*/
		
		//$this->output["scripts_mode"] = $this->output["mode"] = "daystogo";
	}
	
    function Output()
    {
    	return $this->output;
    }
	
}


function str_replace_limit($search, $replace, $subject, &$count, $limit = -1){
$count = 0;
// Invalid $limit provided
if(!($limit===strval(intval(strval($limit))))){
  trigger_error('Invalid $limit `'.$limit.'` provided. Expecting an '.
    'integer', E_USER_WARNING);
  return $subject;
}
// Invalid $limit provided
if($limit<-1){
  trigger_error('Invalid $limit `'.$limit.'` provided. Expecting -1 or '.
    'a positive integer', E_USER_WARNING);
  return $subject;
}
// No replacements necessary
if($limit===0){
  trigger_error('Invalid $limit `'.$limit.'` provided. Expecting -1 or '.
    'a positive integer', E_USER_NOTICE);
  return $subject;
}
// Use str_replace() when possible
if($limit===-1){
  return str_replace($search, $replace, $subject, $count);
}
if(is_array($subject)){
  // Loop through $subject values
  foreach($subject as $key => $this_subject){
   // Skip values that are arrays
   if(!is_array($this_subject)){
    // Call this function again
    $this_function = __FUNCTION__;
    $subject[$key] = $this_function($search, $replace, $this_subject, $this_count, $limit);
    // Adjust $count
    $count += $this_count;
    // Adjust $limit
    if($limit!=-1){
     $limit -= $this_count;
    }
    // Reached $limit
    if($limit===0){
     return $subject;
    }
   }
  }
  return $subject;
} elseif(is_array($search)){
  // Clear keys of $search
  $search = array_values($search);
  // Clear keys of $replace
  if(is_array($replace)){
   $replace = array_values($replace);
  }
  // Loop through $search
  foreach($search as $key => $this_search){
   // Don't support multi-dimensional arrays
   $this_search = strval($this_search);
   // If $replace is an array, use $replace[$key] if exists, else ''
   if(is_array($replace)){
    if(array_key_exists($key, $replace)){
     $this_replace = strval($replace[$key]);
    } else {
     $this_replace = '';
    }
   } else {
    $this_replace = strval($replace);
   }
   // Call this function again for
   $this_function = __FUNCTION__;
   $subject = $this_function($this_search, $this_replace, $subject, $this_count, $limit);
   // Adjust $count
   $count += $this_count;
   // Adjust $limit
   if($limit!=-1){
    $limit -= $this_count;
   }
   // Reached $limit
   if($limit===0){
    return $subject;
   }
  }
  return $subject;
} else {
  $search = strval($search);
  $replace = strval($replace);
  // Get position of first $search
  $pos = strpos($subject, $search);
  // Return $subject if $search cannot be found
  if($pos===false){
   return $subject;
  }
  // Get length of $search
  $search_len = strlen($search);
  // Loop until $search cannot be found or $limit is reached
  for($i=0;(($i<$limit)||($limit===-1));$i++){
   $subject = substr_replace($subject, $replace, $pos, $search_len);
   // Increase $count
   $count++;
   // Get location of next $search
   $pos = strpos($subject, $search);
   // Break out of loop
   if($pos===false){
    break;
   }
  }
  return $subject;
}
}

function strtolower_ru($text) {
	$alfavitlover = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
	$alfavitupper = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
		
	return str_replace($alfavitupper,$alfavitlover,strtolower($text));
}

function ucfirst_ru($text) {
	$alfavitlover = array('ё','й','ц','у','к','е','н','г', 'ш','щ','з','х','ъ','ф','ы','в', 'а','п','р','о','л','д','ж','э', 'я','ч','с','м','и','т','ь','б','ю');
	$alfavitupper = array('Ё','Й','Ц','У','К','Е','Н','Г', 'Ш','Щ','З','Х','Ъ','Ф','Ы','В', 'А','П','Р','О','Л','Д','Ж','Э', 'Я','Ч','С','М','И','Т','Ь','Б','Ю');
  
  $line = iconv("UTF-8", "Windows-1251", $text); // convert to windows-1251
  $line = ucfirst($line);
  $line = iconv("Windows-1251", "UTF-8", $line); // convert back to utf-8
  
  return $line;  
		
	return str_replace_limit($alfavitupper,$alfavitlover,strtolower($text),$count, 1);
}

?>