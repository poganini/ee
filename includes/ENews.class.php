<?php

class ENews
// version: 2.8.16
// date: 2014-04-10
{
	var $output;
	var $module_id;
	var $node_id;
	var $module_uri;
	var $db_prefix;
	var $mode;
	var $array;

	var $use_tags;
	
	var $item_ext;
	var $root_cat_id;
	var $Imager;
	var $current_cat_id;
	var $current_cat_data;
	var $current_item_id;
	var $current_item_data;
	var $use_hfu;
	var $show_users;

	var $naming_type;

	var $allow_comments;
	var $use_comments_premoderation;
	var $comments_antiflood_time_limit;
	var $comments_message_size_limit;
	var $comments_captcha_path;

	var $privileges;

	var $status;
	var $display_variant;
	var $form_data;
	var $comment_form_data;
	
	var $title_max_chars;
	

	// � $global_params (���� engine_modules.global_params) ��������� ���������������� ����
	// � $params (���� engine_nodes.params) ��������� ����� ����� � ������� ����� (announce, full_list), ����� ��������� (0, 1, 2 ...)

	function ENews($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		global $DB, $Engine, $EE, $Auth;

		$this->module_id = $module_id;
		$this->module_uri = $module_uri;
		$this->node_id = $node_id;
		$this->output = array();
		$this->output["messages"] = array("good" => array(), "bad" => array());
		$this->output["manage_access"] = $node_id && $Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id); // !!! ���������� �� ����������

//		$this->output["manage_access"] = $Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id);  // !!! �����������, �.�. ���������� current_cat_id ����� NULL

		$this->privileges = array();
		$this->privileges["cat.items.handle"] = false;
		$this->privileges["cat.comments.handle"] = false;
		

		if (!$global_params)
		{
			die("ENews module error #1001: config file not specified.");
		}

		elseif (!$array = @parse_ini_file(INCLUDES . $global_params, true))
		{
			die("ENews module error #1002: cannot process config file '$global_params'.");
		}
		
		$this->array = $array; 
        		
		$this->db_prefix = $array["general_settings"]["db_prefix"];
		$this->use_tags = $array["general_settings"]["use_tags"]; // ������������ �����
		$this->item_ext = (isset($array["general_settings"]["item_ext"])) && CF::IsNonEmptyStr($array["general_settings"]["item_ext"]) ? $array["general_settings"]["item_ext"] : "html";
		$this->images_dir = (isset($array["general_settings"]["images_dir"])) && CF::IsNonEmptyStr($array["general_settings"]["images_dir"]) ? $array["general_settings"]["images_dir"] : false;
		$this->title_max_chars = isset($array["general_settings"]["title_max_chars"]) ? $array["general_settings"]["title_max_chars"] : 0;
		

		if ($this->images_dir && (isset($array["imager_settings"]["ini_file"]) && $array["imager_settings"]["ini_file"])) {
			require_once INCLUDES . "Imager.class";
			$this->Imager = new Imager($array["imager_settings"]["ini_file"], COMMON_IMAGES . $this->images_dir . "/", HTTP_COMMON_IMAGES . $this->images_dir . "/");
		} else {
			$this->Imager = false;
		}

		// ��������� ���������������
		$this->allow_comments = (bool)$array["comments_settings"]["allow_comments"];
		$this->use_comments_premoderation = (bool)$array["comments_settings"]["use_premoderation"];
		$this->comments_antiflood_time_limit = (int)$array["comments_settings"]["antiflood_time_limit"];
		$this->comments_message_size_limit = $array["comments_settings"]["message_size_limit"] ? (int)$array["comments_settings"]["message_size_limit"] : false;
		$this->comments_captcha_path = CF::IsNaturalOrZeroNumeric($array["comments_settings"]["captcha_path"])
			? $Engine->FolderURIbyID($array["comments_settings"]["captcha_path"])
			: (CF::IsNonEmptyStr($array["comments_settings"]["captcha_path"]) ? $array["comments_settings"]["captcha_path"] : '');



		/*$this->output["use_hfu"] = $this->use_hfu = !(isset($parts[4]) && !$parts[4]);
		$this->show_users = (isset($parts[5]) && $parts[5]);*/
        
		$parts = explode(";", $params);
		$this->output["scripts_mode"] = array();
		$this->output["scripts_mode"][] = $this->output["mode"] = $this->mode = $parts[0];
		
		$this->output["show_news_share"] = in_array( APPLIED_DOMAIN, explode(",", $array["news_share_settings"]["allowed_domains"]));
		
		if (!isset($EE['show_modes'])) $EE['show_modes'] = array();
		$EE['show_modes'][] = $this->mode;
		 
		
//echo $this->output["scripts_mode"] = $this->output["mode"]." ".$parts[1]." ".$parts[2]." ".$parts[3]." ".$parts[4];
		if (isset($_POST["news_cat_id"])) {
			$parts[1] = (int)$_POST["news_cat_id"];
		}
		
		if (isset($_POST["news_folder_id"])) {
			$parts[2] = (int)$_POST["news_folder_id"];
		}
		
		if (isset($parts[1])) {
			$this->current_cat_id = (int)$parts[1];
			$this->title_max_chars = isset($this->title_max_chars[$this->current_cat_id]) ? $this->title_max_chars[$this->current_cat_id] : 0;
			//$this->output["manage_access"] = $node_id && $Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id);
			$this->output["manage_access"] = $node_id && $Engine->OperationAllowed($this->module_id, "cat.items.handle", $this->current_cat_id, $Auth->usergroup_id);
			//die(print_r($this->current_cat_id));
		}

		else {
			die("ENews module error #1010 at node $this->node_id: cat ID not specified.");
		}
		
		$this->output["title_max_chars"] = $this->title_max_chars;
		
		//if ($this->manage_access)
		//var_dump ($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id));
//		if ($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id))
//		{
//			$this->ProcessHTTPdata();
//		}

		switch ($this->mode) {			
			case "announse_ajax":
			case "announce": {			
				if (isset($parts[2])) {
					$folder_id = $parts[2];
				} else {
					die("ENews module error #1002 at node $this->node_id: NEWS FOLDER ID not specified in ANNOUNCE mode.");
				} 
				if (isset($parts[3])) {
					$this->output["root_cat_id"] = $this->root_cat_id = $parts[3];
				} else {
					die("ENews module error #1003 at node $this->node_id: NEWS FOLDER CATEGORY ID not specified in ANNOUNCE mode.");
				}
				if (isset($_POST["display_limit"])) {
					$display_limit = (int)$_POST["display_limit"];
				} elseif (isset($parts[4])) {
					$display_limit = $parts[4];
				} else {
					die("ENews module error #1004 at node $this->node_id: NEWS DISPLAY LIMIT not specified in ANNOUNCE mode.");
				}
				$this->output['parems'] = $parts[1].';'.$parts[2].';'.$parts[3].';'.$parts[4].';'.$parts[5].';'.$parts[6];

				$include_subcats = (isset($parts[5]) && !$parts[5]) ? false : true;
				$this->output["cat_options"] = $this->FolderCatOptions();
				
				$this->output["sub_mode"] = "none";
				if(isset($parts[6])) {
					//if($parts[5] == "image" || $parts[5] == "text" || $parts[5] == "title")
						$this->output["sub_mode"] = $parts[6];
				}
				
				if (isset($parts[7])) {
					$sort_params = isset($parts[7]) ? explode(',', $parts[7]) : false;
				}
				if (isset($parts[8])) {
					$sort_orders = isset($parts[8]) ? explode(',', $parts[8]) : false;
				}
				
				if (isset($_POST["skip"]) && CF::IsNaturalNumeric($_POST["skip"]))
					$this->Announce($folder_id, $display_limit, $include_subcats, $_POST["skip"], $sort_params, $sort_orders);
				else
					$this->Announce($folder_id, $display_limit, $include_subcats, $sort_params, $sort_orders);					
			}
			break;		

			case "calendar": {
					if (isset($parts[1]) || isset($_REQUEST['pid_cat']))  {
						$pid_cat = isset($_REQUEST['pid_cat']) ? $_REQUEST['pid_cat'] : $parts[1];
						if (isset($_REQUEST['date'])) {
							$dateParts = explode('-', $_REQUEST['date']);
							$month = $dateParts[0].'-'.$dateParts[1];
						}
						$search_date = isset($_REQUEST['search_date']) ? $_REQUEST['search_date'] : ( isset($_REQUEST['date']) ? $month : date("Y-m"));
						$newsDates = $this->GetNewsDates($search_date, $pid_cat);
						if (isset($_REQUEST['pid_cat'])) {
							$this->output = ($newsDates ? ','.implode(',', $newsDates).',' : false);
							return;
						} else {
							$this->output['newsDates'] = $newsDates;
							if (isset($_REQUEST['date'])) { 
								$dateParts = explode('-', $_REQUEST['date']); 
								$this->output["year"] = $dateParts[0];
								$this->output["month"] = $dateParts[1];
							}
						}
						if (isset($parts[3])) $this->output["href_base"] = $Engine->FolderURIbyID($parts[3]); 
					}
			}
			break;

			case "header_news": {
				$this->output["news_text"] = "������ ������� � 3 �������� �������� ����������";
			}
			break;

			case "new_full_list": {
				$this->output["show_event_input"] = 1;
				$this->output["show_catselect"] = 1;
				$this->output["scripts_mode"][] = 'input_datetimepicker';
			}
			case "full_list": { 
				$this->output["scripts_mode"][] = 'input_datetimepicker';
				$this->output["root_cat_id"] = $this->root_cat_id = $parts[1];
				$per_page_input = (isset($parts[2]) && $parts[2]) ? $parts[2] : ""; // ������ ��������� ��� Pager
				$include_subcats = (isset($parts[3]) && $parts[3]); // ����� �������� ������� ������������
				$tag_id = (isset($parts[4]) && $parts[4]) ? $parts[4] : ""; // id �����, ���� ����
				$date_sort_order = (isset($parts[5]) ? $parts[5] : true);
				$date_filter =  (isset($_REQUEST['date']) && $_REQUEST['date']) ? $_REQUEST['date'] : (isset($parts[6]) ? $parts[6] : false) ;
				$output_folder_id = (isset($parts[7]) ? $parts[7] : false);
				
				$this->output["cat_options"] = $this->FolderCatOptions(); // !!! ��������
				
				$this->FullList($per_page_input, $include_subcats, $tag_id, $date_sort_order, $date_filter, $output_folder_id);
				
				$this->privileges["cat.create.items"]["create"] = $Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id);
				$this->privileges["cat.create.items"]["user_id"] = $Auth->user_id;
				$this->privileges["cat.items.handle"] = $Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id);
				$this->privileges["cat.comments.handle"] = $Engine->ModuleOperationAllowed("cat.comments.handle", $this->current_cat_id);
				$this->output["comment_form_data"] = $this->comment_form_data;
				$this->output["cats"] = $this->ListCats();
				
				if ($this->privileges["cat.items.handle"] || $this->privileges["cat.create.items"]["create"])
				{
					$this->output["input_file_max_size"] = $this->Imager ? $this->Imager->input_file_max_size : false;
				}
			}
			break;
		
			case "online_webcast": {
				global $DB, $Auth, $Engine;
				$DB->SetTable($this->db_prefix . "items");
				$DB->AddField("id");
				$DB->AddAltFS("cat_id", "=", $parts[1]);	
				//$DB->AppendAlts();
				$DB->AddCondFX("start_event", "<=", "NOW()");
				$DB->AddCondFX("end_event", ">=", "NOW()");
				$DB->AddCondFP("is_active");
				$res = $DB->Select();
				if($row = $DB->FetchObject($res)) {
					$this->output['online_webcast'] = 1;
				}
			}
			break;

			case "search": {
				if ($additional && (!isset($parts[1]) || CF::IsNaturalNumeric($parts[1]))) {
					$this->output["results"] = $this->SearchAlter($additional, isset($parts[1]) ? $parts[1] : 0, isset($parts[2]) ? $parts[2] : false, isset($parts[3]) ? $parts[3] : false);
				} else {
					return false;
				}
			}
			break;
				
			case "comments": { 
				$this->output["comments"] = $this->ListComments(-1, false, $parts[1]);
			}
			break;
			
			case "subscribe": {
				$this->output["messages"]["confirmation_result"] = "";
				if (isset($_GET["code"])) {
					if ($this->ConfirmSubscriber($_GET["code"])) {
						$this->output["messages"]["confirmation_result"] = "<p class=\"message\">�������� ������������!</p>\n";
					}
					else {
						$this->output["messages"]["confirmation_result"] = "<p class=\"message red\">������� �������� ��� �������������!</p>\n";
					}
				}
				
				if (isset($_POST[$this->node_id]["subscribe"]["email"])) {
					$confirm = md5(rand(0, 99999999).time()); 
          
          $this->display_variant = "subscribe"; 
          
          if(!CF::ValidateEmail($_POST[$this->node_id]["subscribe"]["email"])) {
            $status = false; 
            $this->output["messages"]["bad"][] = 312;
          } 						
					else {	
					if ($array["subscribes_settings"]["need_confirmation"]) {
						$is_confirmed = 0;
						
						// ��������� ������
						$headers = "MIME-Version: 1.0\r\n";
						$headers .= "Content-type: text/html; charset=windows-1251\r\n";
						$headers .= "From: ".SITE_SHORT_NAME." <".$array["subscribes_settings"]["from_email"].">\r\n";
			            $headers .= "Return-Path: ".$array["subscribes_settings"]["from_email"]."\r\n";
						/*$headers .= "To: ".$name." <".$email"].">\r\n";*/ // �� �����, ������ ��� �� ������� ������� � ���� while �� ��� ������������
						$headers .= "Reply-To: ".SITE_SHORT_NAME." <".$array["subscribes_settings"]["from_email"].">\r\n";
						$headers .= "X-Priority: 1\r\n";
						$headers .= "X-MSMail-Priority: High\r\n";
						//$headers .= "X-Mailer: mailer www.mydomain.ru";
						$headers .= "X-Mailer: PHP/" . phpversion();
	
						$subject = SITE_SHORT_NAME."������������� ��������";
						
						$message = "������������! ����� ����������� �������� �� �������� �������� ����� \"".SITE_SHORT_NAME."\", ��������� �� ������: http://".$_SERVER["SERVER_NAME"]."/".$array["subscribes_settings"]["confirm_uri"]."?code=".$confirm;
						
						mail($_POST[$this->node_id]["subscribe"]["email"], $subject, $message, $headers, " -f ".$array["subscribes_settings"]["from_email"]);
					}
					else {
						$is_confirmed = 1;
					}						
						
					$DB->SetTable($this->db_prefix . "subscribers");
					$DB->AddValues(array(
							"is_confirmed" => $is_confirmed,
							"confirm_code" => $confirm,
							"email" => $_POST[$this->node_id]["subscribe"]["email"],
							"register_time" => date("Y-m-d H:i:s"),
						));
					if (!$DB->Insert()) {
						$status = false;
						$this->output["messages"]["bad"][] = 401; // ������ �� ��� ���������� ���������
					}
					else {
						$this->output["messages"]["good"][] = "��� ������ ��� �������������";
					}
          }
				}
        //$this->ClearSubscribers(); 
			}
			break;
			
			case "RSS":
			case "RSS2": {
                if (isset($parts[2])) {
                    $folder_id = $parts[2];
                } else {
                    die("ENews module error #1002 at node $this->node_id: NEWS FOLDER ID not specified in ANNOUNCE mode.");
                }
                if (isset($parts[3])) {
                    $this->output["root_cat_id"] = $this->root_cat_id = $parts[3];
                } else {
                    die("ENews module error #1003 at node $this->node_id: NEWS FOLDER CATEGORY ID not specified in ANNOUNCE mode.");
                }
                if (isset($parts[4])) {
                    $display_limit = $parts[4];
                } else {
                    die("ENews module error #1004 at node $this->node_id: NEWS DISPLAY LIMIT not specified in ANNOUNCE mode.");
                }

                $include_subcats = (isset($parts[5]) && !$parts[5]) ? false : true;
                $this->output["cat_options"] = $this->FolderCatOptions();
                $this->RSS($folder_id, $display_limit, $include_subcats);
			}
            break;

			case "events_news": {
				global $DB, $Engine;
				//$this->output['plugins'][] = 'jquery.colorbox';
				$this->output['plugins'][] = 'jquery.ui.datepicker.min';
				$this->output['plugins'][] = 'jquery-ui-timepicker-addon';
				//$this->output["root_cat_id"] = $this->root_cat_id = $parts[1];
				if (isset($parts[1])) {
					$parts[1] = explode(',',$parts[1]);
					//$folder_id = $parts[2];
				} else {
					die("ENews module error #1010 at node $this->node_id: cat ID not specified.");
				}
				if (isset($parts[2])) {
					$parts[2] = explode(',',$parts[2]);
					//$folder_id = $parts[2];
				} else {
					die("ENews module error #1002 at node $this->node_id: NEWS FOLDER ID not specified in ANNOUNCE mode.");
				}
				foreach($parts[1] as $i => $news_cat) {
					$this->output["news_cat"][] = $news_param[$i]['news_cat'] = $news_cat;
				}
				foreach($parts[2] as $i => $news_folder) {
					$this->output["folder_id"][] = $news_param[$i]['folder_id'] = $news_folder;
				}
				
				$event_month = date('m');
				$event_year = date('Y');
				
				$DB->SetTable($this->db_prefix . "items");
				$DB->AddFields(array("id", "is_active", "time", "start_event"));
				foreach($news_param as $param) {
					$DB->AddAltFS("cat_id", "=", $param['news_cat']);
				}
				$DB->AddCondFP("is_active");
				$start_month = $DB->SmartTime(date('Y'), date('m'), date('d'), 00, 00, 00);
				$DB->AddCondFS("start_event", ">=", $start_month[0]);
				$DB->AddOrder("start_event");
			
				$res = $DB->Select(1);
				if($row = $DB->FetchObject($res)) {
					$time = $row->start_event;
					if(empty($row->start_event) || is_null($row->start_event) || $row->start_event == '0000-00-00 00:00:00') {
						$time = $row->time;
					}
					$event_month = date('m', strtotime($time));
					$event_year = date('Y', strtotime($time));
				}
				
				foreach($news_param as $param) {
					$this->getEvents($param['folder_id'], $param['news_cat'], $event_month, $event_year);
				}
				//$this->output["news_cat"] = $this->current_cat_id;
				//$this->output["folder_id"] = $folder_id;
				//$this->getEvents($folder_id, date('m'), date('Y'));
				
				//$this->root_cat_id = $news_cat;
				
			}
			break;
		
			case "ajax_get_event": {
				global $DB, $Engine;
				if(isset($_REQUEST['data'])) {
					//$this->output["root_cat_id"] = $this->root_cat_id = $parts[1];
					//if (strlen($month) == 1) {$month = '0'.$month;}
					if (isset($parts[1])) {
						$parts[1] = explode(',',$parts[1]);
						//$folder_id = $parts[2];
					}
					if (isset($_REQUEST['data']['folder_id'])) {
						$parts[2] = explode(',',$_REQUEST['data']['folder_id']);
						//$folder_id = $parts[2];
					}
					foreach($parts[1] as $i => $news_cat) {
						$news_param[$i]['news_cat'] = $news_cat;
					}
					foreach($parts[2] as $i => $news_folder) {
						$news_param[$i]["folder_id"] = $news_folder;
					}
					$this->output["news_events"]['current_event_date'] = date('Y-m-d');
					if(isset($_REQUEST['data']['month'],$_REQUEST['data']['year']) && !empty($_REQUEST['data']['month']) && !empty($_REQUEST['data']['year'])) {
						foreach($news_param as $param) {
							$this->getEvents($param['folder_id'], $param['news_cat'], $_REQUEST['data']['month'], $_REQUEST['data']['year']);
						}
					} else {
						$event_month = date('m');
						$event_year = date('Y');
						
						$DB->SetTable($this->db_prefix . "items");
						$DB->AddFields(array("id", "is_active", "time", "start_event"));
						foreach($news_param as $param) {
							$DB->AddAltFS("cat_id", "=", $param['news_cat']);
						}
						$DB->AddCondFP("is_active");
						$start_month = $DB->SmartTime(date('Y'), date('m'), date('d'), 00, 00, 00);
						$DB->AddCondFS("start_event", ">=", $start_month[0]);
						$DB->AddOrder("start_event");
					
						$res = $DB->Select(1);
						if($row = $DB->FetchObject($res)) {
							$time = $row->start_event;
							if(empty($row->start_event) || is_null($row->start_event) || $row->start_event == '0000-00-00 00:00:00') {
								$time = $row->time;
							}
							$event_month = date('m', strtotime($time));
							$event_year = date('Y', strtotime($time));
						}
						
						$this->output["news_events"]['defaultDate'] = ($event_year - date('Y')).'y '.($event_month - date('m')).'m';
						foreach($news_param as $param) {
							$this->getEvents($param['folder_id'], $param['news_cat'], $event_month, $event_year);
						}
					}
				}
			}
			break;

			default: {
				die("ENews module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
			}
			break;
		}

		$this->output["status"] = $this->status;
		$this->output["module_id"] = $this->module_id;
		$this->output["display_variant"] = $this->display_variant;
		$this->output["form_data"] = $this->form_data;
		$this->output["privileges"] = $this->privileges;
		//if (isset($_GET["test"])) { print_r($this->output["news_events"]);}
	}

	function getEvents($folder_id, $news_cat, $month = null, $year = null) {
		global $DB, $Engine;
		
		$this->root_cat_id = $news_cat;
		$DB->SetTable($this->db_prefix . "items");
		$DB->AddFields(array("id", "is_active", "image_data", "time", "title", "short_text", "full_text", "create_user_id", "start_event", "end_event"));
		$DB->AddAltFS("cat_id", "=", $news_cat);
		$DB->AddCondFP("is_active");
		//$DB->AddCondFX("time", "<=", "NOW()");
		/*if (!$Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) && !$Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id)) {
			$DB->AddCondFP("is_active");
			$DB->AddCondFX("time", "<=", "NOW()");
		}*/
		
		//$smart_create_time = $DB->SmartTime(date("Y",mktime(0,0,0,$month,1,$year)), date("m",mktime(0,0,0,$month,1,$year)), 1, 00, 00, 00);
		
		//$start_month = date('Y-d-m H:i:s', mktime(0,0,0,$month,1,$year));
		//$end_month = date('Y-d-m H:i:s', mktime(0,0,0,$month+1,0,$year));
		
		$start_month = $DB->SmartTime(date('Y', mktime(0,0,0,$month,1,$year)), date('m', mktime(0,0,0,$month,1,$year)), date('d', mktime(0,0,0,$month,1,$year)), 00, 00, 00);
		$end_month = $DB->SmartTime(date('Y', mktime(0,0,0,$month+1,0,$year)), date('m', mktime(0,0,0,$month+1,0,$year)), date('d', mktime(0,0,0,$month+1,0,$year)), 23, 59, 59);
		
		$todey = date('Y-m-d');
		
		$future = false;
		$DB->AddCondFS("start_event", ">=", $start_month[0]);
		$DB->AddCondFS("start_event", "<=", $end_month[0]);
		$DB->AddOrder("start_event");
		
		$res = $DB->Select();
		while ($row = $DB->FetchObject($res)) {
			if ($row->image_data && $this->Imager) {
				$this->Imager->SetProps($row->id, $row->image_data);
				$output_files = $this->Imager->ListOutputFiles();
			} else {
				$output_files = array();
			}
			$time = $row->start_event;
			if(empty($row->start_event) || is_null($row->start_event) || $row->start_event == '0000-00-00 00:00:00') {
				$time = $row->time;
			}
			if($this->mode == 'ajax_get_event') {
				$row->title = iconv("windows-1251", "UTF-8",$row->title);
				$row->short_text = iconv("windows-1251", "UTF-8",$row->short_text);
				$row->full_text = iconv("windows-1251", "UTF-8",CF::IsNonEmptyStr($row->full_text));
			}
			//$event_date = (date('Y', strtotime($time))).'-'.(date('m', strtotime($time))).'-'.(date('d', strtotime($time)));
			$event_date = date('Y-m-d', strtotime($time));
			$this->output["news_events"][$event_date][] = array(
				"id" => $row->id,
				"is_active" => (bool) $row->is_active,
				"link" => $this->ItemURIbyID($row->id, $folder_id),
				"time" => array(
					'year' => date('Y', strtotime($time)),
					'month' => date('m', strtotime($time))-1,
					'day' => date('d', strtotime($time))
				),
				"title" => $row->title,
				"image_data" => $row->image_data,
				"short_text" => $row->short_text,
				"has_full_text" => $row->full_text,
//				"author_id" => $row->author_id,
//				"author_name" => $this->show_users ? $this->displayed_name : "",
				"output_files" => $output_files
			);
			if(!isset($this->output["news_events"]['current_event_date']) || ($event_date <= $todey && $event_date > $this->output["news_events"]['current_event_date']) || ($event_date > $todey && !$future)) {
				$this->output["news_events"]['current_event_date'] = $event_date;
				if($event_date > $todey && !$future) {
					$future = true;
				}
			}
			
			/*if($event_date <= $todey) {
				$this->output["news_events"]['current_event_date'] = $event_date;
			}
			if($event_date > $todey && !$future) {
				$this->output["news_events"]['current_event_date'] = $event_date;
				$future = true;
			}*/
		}

		//$this->output["news_events"]['todey'] = (date('Y')).'-'.(date('m')).'-'.(date('d'));
		//echo json_encode($this->output["news_events"]);
	}
	
	function GetNewsDates($yearMonth, $pidCat = false) {
		global $DB;
		if ($pidCat) {
			$DB->SetTable($this->db_prefix . "cats");
			$DB->AddField("id");
			$DB->AddCondFS('pid', '=', $pidCat);
			if ($res = $DB->Select()) {
				$catIds = array();
				while ($row = $DB->FetchAssoc($res)) {
					$catIds[] = $row['id'];
				}
			}
			$DB->FreeRes();
		}
		$DB->SetTable($this->db_prefix . "items");
		$DB->AddField("create_time");
		$DB->AddCondFS("create_time", "LIKE", $yearMonth.'%');
		if (isset($catIds) && !empty($catIds)) {
			foreach ($catIds as $id)
				$DB->AddAltFS("cat_id", "=", $id);
			$DB->AppendAlts();
		} else if ($pidCat) {
			$DB->AddAltFS("cat_id", "=", $pidCat);
			$DB->AppendAlts();
		}
		if ($res = $DB->Select()) {
			$newsDates = array();
			 while ($row = $DB->FetchAssoc($res)) {
			 		$timeParts = explode(' ', $row['create_time']);
			 		$dateParts = explode('-', $timeParts[0]);
			 		if ($dateParts[2] != '00' && !in_array($dateParts[2], $newsDates))
			 			$newsDates[] = intval($dateParts[2]);
			 }
			return $newsDates;
		}
		return false;
	}
	
	function FolderCatOptions() {
		global $DB, $Engine;
		$output = array();

		$DB->SetTable($this->db_prefix . "cats");
		$DB->AddFields(array("options"));
		$DB->AddCondFS("id", "=", $this->root_cat_id);

		if (!$Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id)) {
			$DB->AddCondFP("is_active");
		}
		$res = $DB->Select(1);
		if ($row = $DB->FetchObject($res)) {
			$DB->FreeRes($res);
			foreach (explode(";", $row->options) as $elem) {
				if ($elem) {
					$parts = explode(":", trim($elem));
					$output[$parts[0]] = $parts[1];
				}
			}
		}

		return $output;
	}
	
	function RSS($folder_id, $display_limit, $include_subcats) {
        global $DB, $Engine;
        $this->output["folder_uri"] = $folder_uri = $Engine->FolderURIbyID($folder_id); // ������� ������� ������ �����

        $this->output["news"] = array();

        $DB->SetTable($this->db_prefix . "items");
        $DB->AddFields(array("id", "is_active", "image_data", "time", "title", "short_text", "full_text"));
		if ($this->module_uri) {
			$news_info = explode(".", $this->module_uri);
        	$DB->AddCondFS("id", "=", $news_info[0]);
        	$this->output["full_text_mode"] = true;
        }
        $DB->AddAltFS("cat_id", "=", $this->current_cat_id);
        if ($include_subcats) {
            foreach ($this->ListCats($this->current_cat_id, true) as $elem) {
                $DB->AddAltFS("cat_id", "=", $elem);
            }
        }

        $DB->AppendAlts();
//        if ($this->show_users)
//        {
//            $DB->AddJoin(AUTH_DB_PREFIX . "users.id", "author_id");
//            $DB->AddField(AUTH_DB_PREFIX . "users.displayed_name");
//        }

        $DB->AddCondFP("is_active");
        if($this->mode != "new_full_list") {
			$DB->AddCondFX("time", "<=", "NOW()"); // ������� ������?
		}

        $DB->AddOrder("time", true);
        $res = $DB->Select($display_limit);

        while ($row = $DB->FetchObject($res)) {
            if ($row->image_data && $this->Imager) {
                $this->Imager->SetProps($row->id, $row->image_data);
                $output_files = $this->Imager->ListOutputFiles();
            } else {
                $output_files = array();
            }

            $this->output["news"][] = array(
                "id" => $row->id,
                "is_active" => (bool) $row->is_active,
                "link" => $this->ItemURIbyID($row->id, $folder_id),
                "time" => $row->time,
                "title" => $row->title,
                "image_data" => $row->image_data,
                "short_text" => $row->short_text,
                "has_full_text" => CF::IsNonEmptyStr($row->full_text),
            	"full_text" => $row->full_text,
//                "author_id" => $row->author_id,
//                "author_name" => $this->show_users ? $this->displayed_name : "",
                "output_files" => $output_files
                   );
        }

        $DB->FreeRes($res);
    }



	function ProcessHTTPdata()
	{
		global $DB, $Auth, $Engine;

		if (isset($_POST[$this->node_id]["cancel"])) {
			1;
		} elseif (isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id])) {
			foreach (array("add_item", "save_item", "add_comment", "save_comment") as $POST_ACTION) {
				if (isset($_POST[$this->node_id][$POST_ACTION]) && is_array($_POST[$this->node_id][$POST_ACTION])) {
					$POST_DATA = $_POST[$this->node_id][$POST_ACTION];
					break;
				}
			}
			if (isset($POST_DATA)) {
				foreach ($POST_DATA as $key => $elem) {
					if(!is_array($elem)) {
						$POST_DATA[$key] = trim($elem);
					}
				}
				switch ($POST_ACTION) {
					case "add_item": {
						if (($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) || $Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id)) && isset($POST_DATA["cat_id"], $POST_DATA["uripart"]/*, $POST_DATA["public"]["year"], $POST_DATA["public"]["month"], $POST_DATA["public"]["day"], $POST_DATA["public"]["hours"], $POST_DATA["public"]["minutes"], $POST_DATA["seconds"]*/, $POST_DATA["title"], /*$POST_DATA["image_ext"], */$POST_DATA["short_text"], $POST_DATA["full_text"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["cat_id"])) {
							if ($this->Imager && isset($_FILES[$this->node_id . "_file"]) && CF::IsNonEmptyStr($_FILES[$this->node_id . "_file"]["tmp_name"]) && $_FILES[$this->node_id . "_file"]["size"]) {
								$FILE_DATA = $_FILES[$this->node_id . "_file"];
							} else {
								$FILE_DATA = false;
							}
							$this->status = true;

							if ($FILE_DATA) {
								if (is_array($result = $this->Imager->GrabUploadedFile($FILE_DATA))) {
									$this->status = false;
									$this->output["messages"] = array_merge($this->output["messages"], $result);
								}
							}

							if (!$POST_DATA["cat_id"]) {
								$this->status = false;
								$this->output["messages"]["bad"][] = 301; // �� ������� ���������!)
							}
							
							if (strlen($POST_DATA["title"]) < 3) {
								$this->status = false;
								$this->output["messages"]["bad"][] = 912; // �� ������� ���������!)
							}
							
							if ( $this->title_max_chars != 0 && strlen($POST_DATA["title"]) > $this->title_max_chars)
							{
								$this->status = false;
								$this->output["title_max_chars"] = $this->title_max_chars;
								$this->output["messages"]["bad"][] = 913; // ����� ��������� ��������� ���������� !)
							}

							if (CF::IsNonEmptyStr($POST_DATA["uripart"]) && (preg_match("/^\d+$/", $POST_DATA["uripart"]) || !preg_match("/^[a-z0-9_-]+$/i", $POST_DATA["uripart"]))) {
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // ������������ ������ uripart (������ ��������� �����, �����, ����� � �������; ������, ��������� ������� ��&nbsp;����, �����������!)
							}

							if (!CF::IsNonEmptyStr($POST_DATA["short_text"])) {
								$this->status = false;
								$this->output["messages"]["bad"][] = 303; // �� ����� ������� �����
							}

							if ($this->mode == 'new_full_list' && (!isset($POST_DATA["start_event"]["date"]) || !$POST_DATA["start_event"]["date"])) {
								$this->status = false;
								$this->output["messages"]["bad"][] = 304; // �� ����� ������� �����
							}

							if ($this->status) {
								//$smart_time = $DB->SmartTime($POST_DATA["public"]["year"], $POST_DATA["public"]["month"], $POST_DATA["public"]["day"], $POST_DATA["public"]["hours"], $POST_DATA["public"]["minutes"], $POST_DATA["seconds"]);
								if(isset($POST_DATA["public"]["date"]) && !empty($POST_DATA["public"]["date"])) {
									$smart_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["public"]["date"])), date('m', strtotime($POST_DATA["public"]["date"])), date('d', strtotime($POST_DATA["public"]["date"])), date('H', strtotime($POST_DATA["public"]["date"])), date('i', strtotime($POST_DATA["public"]["date"])), 00);
								} else {
									$smart_time = $DB->SmartTime(date('Y'), date('m'), date('d'), date('H'), date('i'), date('s', strtotime('00')));
								}

								$DB->SetTable($this->db_prefix . "items");
								$DB->AddValues(array(
									"cat_id" => $POST_DATA["cat_id"],
									"uripart" => $POST_DATA["uripart"],
									"title" => $POST_DATA["title"],
									"short_text" => $POST_DATA["short_text"],
									"full_text" => $POST_DATA["full_text"]
									));

								if (1/*$this->maintain_cache*/) { // !!! ������� ������								
									$DB->AddValue("cached_text", substr(CF::RefineText($POST_DATA["full_text"]), 0, 65535)); // !!! ������� ������������� ������� ������ �� ������ MySQLhandle
								}

								$DB->AddValue("time", $smart_time[0], $smart_time[1]);
								if(isset($POST_DATA["start_event"]["date"]) && !empty($POST_DATA["start_event"]["date"])) {
									$start_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["start_event"]["date"])), date('m', strtotime($POST_DATA["start_event"]["date"])), date('d', strtotime($POST_DATA["start_event"]["date"])), date('H', strtotime($POST_DATA["start_event"]["date"])), date('i', strtotime($POST_DATA["start_event"]["date"])), date('s', strtotime('00')));
									$DB->AddValue("start_event", $start_time[0], $start_time[1]);
								} else {
									$DB->AddValue("start_event", $smart_time[0], $smart_time[1]);
								}
								if(isset($POST_DATA["end_event"]["date"]) && !empty($POST_DATA["end_event"]["date"])) {
									$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["end_event"]["date"])), date('m', strtotime($POST_DATA["end_event"]["date"])), date('d', strtotime($POST_DATA["end_event"]["date"])), date('H', strtotime($POST_DATA["end_event"]["date"])), date('i', strtotime($POST_DATA["end_event"]["date"])), date('s', strtotime('00')));
									if($start_time > $end_time) {
										$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["start_event"]["date"])), date('m', strtotime($POST_DATA["start_event"]["date"])), date('d', strtotime($POST_DATA["start_event"]["date"])), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									}
									$DB->AddValue("end_event", $end_time[0], $end_time[1]);
								} else {
									if(isset($POST_DATA["start_event"]["date"]) && !empty($POST_DATA["start_event"]["date"])) {
										$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["start_event"]["date"])), date('m', strtotime($POST_DATA["start_event"]["date"])), date('d', strtotime($POST_DATA["start_event"]["date"])), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									} elseif(isset($POST_DATA["public"]["date"]) && !empty($POST_DATA["public"]["date"])) {
										$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["public"]["date"])), date('m', strtotime($POST_DATA["public"]["date"])), date('d', strtotime($POST_DATA["public"]["date"])), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									} else {
										$end_time = $DB->SmartTime(date('Y'), date('m'), date('d'), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									}
									$DB->AddValue("end_event", $end_time[0], $end_time[1]);
								}
								/*if(isset($POST_DATA["start_event"]["year"], $POST_DATA["start_event"]["month"], $POST_DATA["start_event"]["day"], $POST_DATA["start_event"]["hours"], $POST_DATA["start_event"]["minutes"], $POST_DATA["seconds"])) {
									$start_time = $DB->SmartTime($POST_DATA["start_event"]["year"], $POST_DATA["start_event"]["month"], $POST_DATA["start_event"]["day"], $POST_DATA["start_event"]["hours"], $POST_DATA["start_event"]["minutes"], $POST_DATA["seconds"]);
									$DB->AddValue("start_event", $start_time[0], $start_time[1]);
								} else {
									$DB->AddValue("start_event", $smart_time[0], $smart_time[1]);
								}
								if(isset($POST_DATA["end_event"]["year"], $POST_DATA["end_event"]["month"], $POST_DATA["end_event"]["day"], $POST_DATA["end_event"]["hours"], $POST_DATA["end_event"]["minutes"], $POST_DATA["seconds"])) {
									$end_time = $DB->SmartTime($POST_DATA["end_event"]["year"], $POST_DATA["end_event"]["month"], $POST_DATA["end_event"]["day"], $POST_DATA["end_event"]["hours"], $POST_DATA["end_event"]["minutes"], $POST_DATA["seconds"]);
									$DB->AddValue("end_event", $end_time[0], $end_time[1]);
								} else {
									$DB->AddValue("start_event", $smart_time[0], $smart_time[1]);
								}*/
								
								$smart_create_time = $DB->SmartTime(date("Y"), date("n"), date("j"), date("G"), date("i"), date("s"));
								
								$DB->AddValue("create_time", $smart_create_time[0], $smart_create_time[1]);
								$DB->AddValue("create_user_id", $Auth->user_id);
								$DB->AddValue("create_username", $Auth->username);
								$DB->AddValue("alter_time", "0000-00-00 00:00:00");
								//echo $DB->InsertQuery();
								if (!$DB->Insert()) {
									$status = false;
									$this->output["messages"]["bad"][] = 401; // ������ �� ��� ���������� ���������
								} else {
									$this->output["messages"]["good"][] = 101; // �������� ������� ��������
									$ITEM_ID = $DB->LastInsertID();
									
									$this->SendToSubscribers($ITEM_ID);
									
									if($this->use_tags && $POST_DATA["tags"]) {
									 	$tags = explode(",", $POST_DATA["tags"]); // ��������� �� �������
									 	foreach ($tags as $tag) {
									 		$tag = trim($tag);
									 		$DB->SetTable("tags_tags");
									 		$DB->AddField("id");
									 		$DB->AddCondFS("tag", "=", $tag);
									 		$res = $DB->Select(1);
									 		if($row = $DB->FetchObject($res)) { // ���� ����� ����� ��� ����
									 			$DB->FreeRes();
									 			$DB->SetTable("tags_posts");
									 			$DB->AddValue("tag_id", $row->id);
									 			$DB->AddValue("module_id", $this->module_id);
									 			$DB->AddValue("entry_id", $ITEM_ID);
									 			$DB->AddValue("link_suffix", $Engine->engine_uri.$ITEM_ID.".".$this->item_ext); // !!!! ����� ��������!
									 			$DB->AddValue("cache_title", $POST_DATA["title"]);
									 			$DB->AddValue("cache_short", $POST_DATA["short_text"]); 
									 			$DB->Insert();									 			
									 			//echo "<strong>".$row->id."</strong><br />";
									 		} else {
									 			// ������� ����� ����� � ����
									 			$DB->FreeRes();
									 			$DB->SetTable("tags_tags");
									 			$DB->AddValue("tag", $tag);
									 			$DB->Insert();
									 			$tag_id = $DB->LastInsertID();
									 			
									 			// ������������ ����� � �������
									 			$DB->FreeRes();
									 			$DB->SetTable("tags_posts");
									 			$DB->AddValue("tag_id", $tag_id);
									 			$DB->AddValue("module_id", $this->module_id);
									 			$DB->AddValue("entry_id", $ITEM_ID);
									 			$DB->AddValue("link_suffix", $Engine->engine_uri.$ITEM_ID.".".$this->item_ext); // !!!! ����� ��������!
									 			$DB->AddValue("cache_title", $POST_DATA["title"]);
									 			$DB->AddValue("cache_short", $POST_DATA["short_text"]);
									 			$DB->Insert();									 			
									 		}
									 	}
									 	//CF::Debug($tags);
									}
									$Engine->LogAction($this->module_id, "item", $ITEM_ID, "create");
								}
							}
							if ($this->status && $FILE_DATA) {
								if (is_array($result = $this->Imager->CreateOutputFiles($ITEM_ID))) {
									$this->status = false;
									$this->output["messages"] = array_merge($this->output["messages"], $result);
								}
								if ($this->status) {
									$DB->SetTable($this->db_prefix . "items");
									$DB->AddValues(array(
										"image_data" => $this->Imager->ext,
//										"image_width" => $MAIN_OUTPUT_WIDTH,
//										"image_height" => $MAIN_OUTPUT_HEIGHT,
									));
									$DB->AddCondFS("id", "=", $ITEM_ID);

									if (!$DB->Update(1)) {
										$this->output["messages"]["bad"][] = 450; // ������ ���������� ���������� �� �����������
									} else {
										$this->output["messages"]["good"][] = 150; // ���������� �� ����������� ���������
									}
								}
							}

							if (!$this->status) {
								if (isset($ITEM_ID)) {
									$DB->Exec("
										DELETE FROM `" . $this->db_prefix . "items`
										WHERE `id` = '" . $DB->Escape($ITEM_ID) . "'
										LIMIT 1
										");
								}

								$this->display_variant = "add_item";
								$this->output["scripts_mode"][] = 'tiny_mce_news';
								$this->output['plugins'][] = 'jquery.ui.datepicker.min';
								$this->output['plugins'][] = 'jquery-ui-timepicker-addon';
								$this->form_data = array(
									"cat_id" => $POST_DATA["cat_id"],
									"uripart" => $POST_DATA["uripart"],
									"public_time" => $POST_DATA["public"]["date"],/*array(
										"year" => $POST_DATA["public"]["year"],
										"month" => $POST_DATA["public"]["month"],
										"day" => $POST_DATA["public"]["day"],
										"hours" => $POST_DATA["public"]["hours"],
										"minutes" => $POST_DATA["public"]["minutes"],
									),*/
									"start_event" => isset($POST_DATA["start_event"]["date"]) ? $POST_DATA["start_event"]["date"] : '',/*array(
										"year" => isset($POST_DATA["start_event"]["year"]) ? $POST_DATA["start_event"]["year"] : '',
										"month" => isset($POST_DATA["start_event"]["month"]) ? $POST_DATA["start_event"]["month"] : '',
										"day" => isset($POST_DATA["start_event"]["day"]) ? $POST_DATA["start_event"]["day"] : '',
										"hours" => isset($POST_DATA["start_event"]["hours"]) ? $POST_DATA["start_event"]["hours"] : '',
										"minutes" => isset($POST_DATA["start_event"]["minutes"]) ? $POST_DATA["start_event"]["minutes"] : '',
									),*/
									"end_event" => isset($POST_DATA["end_event"]["date"]) ? $POST_DATA["end_event"]["date"] : '',/*array(
										"year" => isset($POST_DATA["end_event"]["year"]) ? $POST_DATA["end_event"]["year"] : '',
										"month" => isset($POST_DATA["end_event"]["month"]) ? $POST_DATA["end_event"]["month"] : '',
										"day" => isset($POST_DATA["end_event"]["day"]) ? $POST_DATA["end_event"]["day"] : '',
										"hours" => isset($POST_DATA["end_event"]["hours"]) ? $POST_DATA["end_event"]["hours"] : '',
										"minutes" => isset($POST_DATA["end_event"]["minutes"]) ? $POST_DATA["end_event"]["minutes"] : '',
									),*/
									"seconds" => $POST_DATA["seconds"],
									"title" => htmlspecialchars($POST_DATA["title"]),
									"short_text" => htmlspecialchars($POST_DATA["short_text"]),
									"full_text" => htmlspecialchars($POST_DATA["full_text"])
								);
							}

							if ($FILE_DATA) {
								@unlink($FILE_DATA["tmp_name"]);
							}
						}
					}
					break;

					case "save_item":
						if (($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) || $Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id)) && isset($POST_DATA["id"], $POST_DATA["cat_id"], $POST_DATA["uripart"]/*, $POST_DATA["public"]["year"], $POST_DATA["public"]["month"], $POST_DATA["public"]["day"], $POST_DATA["public"]["hours"], $POST_DATA["public"]["minutes"], $POST_DATA["seconds"]*/, $POST_DATA["title"], /*$POST_DATA["image_ext"], */$POST_DATA["short_text"], $POST_DATA["full_text"]) && CF::IsNaturalNumeric($POST_DATA["id"]) && CF::IsNaturalOrZeroNumeric($POST_DATA["cat_id"]))
						{
							if (isset($POST_DATA["del_image"]) && $POST_DATA["del_image"]) 
								$this->DeleteNewsImage($POST_DATA["id"]);
						
							if ($this->Imager && isset($_FILES[$this->node_id . "_file"]) && CF::IsNonEmptyStr($_FILES[$this->node_id . "_file"]["tmp_name"]) && $_FILES[$this->node_id . "_file"]["size"])
							{ 
								$FILE_DATA = $_FILES[$this->node_id . "_file"];
							}

							else
							{
								$FILE_DATA = false;
							}

							$this->status = true;

                            if ($FILE_DATA)
							{
								if (is_array($result = $this->Imager->GrabUploadedFile($FILE_DATA)))
								{
									$this->status = false;
									$this->output["messages"] = array_merge($this->output["messages"], $result);
								}
							}

							if (!$POST_DATA["cat_id"])
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 301; // �� ������� ���������!)
							}
							
							if (strlen($POST_DATA["title"]) < 3)
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 912; // �� ������� ���������!)
							}
							
							if ( $this->title_max_chars != 0 && strlen($POST_DATA["title"]) > $this->title_max_chars)
							{
								$this->status = false;
								$this->output["title_max_chars"] = $this->title_max_chars;
								$this->output["messages"]["bad"][] = 913; // �� ������� ���������!)
							}

							if (CF::IsNonEmptyStr($POST_DATA["uripart"]) && (preg_match("/^\d+$/", $POST_DATA["uripart"]) || !preg_match("/^[a-z0-9_-]+$/i", $POST_DATA["uripart"])))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // ������������ ������ uripart (������ ��������� �����, �����, ����� � ������� (������ ��������� �����, �����, ����� � �������; ������, ��������� ������� ��&nbsp;����, �����������!)
							}

							if (!CF::IsNonEmptyStr($POST_DATA["short_text"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 303; // �� ����� ������� �����
							}


							if ($this->status)
							{
								//$smart_time = $DB->SmartTime($POST_DATA["public"]["year"], $POST_DATA["public"]["month"], $POST_DATA["public"]["day"], $POST_DATA["public"]["hours"], $POST_DATA["public"]["minutes"], $POST_DATA["public"]["seconds"]);
								if(isset($POST_DATA["public"]["date"]) && !empty($POST_DATA["public"]["date"])) {
									$smart_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["public"]["date"])), date('m', strtotime($POST_DATA["public"]["date"])), date('d', strtotime($POST_DATA["public"]["date"])), date('H', strtotime($POST_DATA["public"]["date"])), date('i', strtotime($POST_DATA["public"]["date"])), 00);
								} else {
									$smart_time = $DB->SmartTime(date('Y'), date('m'), date('d'), date('H'), date('i'), date('s', strtotime('00')));
								}

								$DB->SetTable($this->db_prefix . "items");
								$DB->AddValues(array(
									"cat_id" => $POST_DATA["cat_id"],
									"uripart" => $POST_DATA["uripart"],
									"title" => $POST_DATA["title"],
									"short_text" => $POST_DATA["short_text"],
									"full_text" => $POST_DATA["full_text"],
									));

								if (1/*$this->maintain_cache*/) { // !!! ������� ������								
									$DB->AddValue("cached_text", substr(CF::RefineText($POST_DATA["full_text"]), 0, 65535)); // !!! ������� ������������� ������� ������ �� ������ MySQLhandle
								}

								$DB->AddValue("time", $smart_time[0], $smart_time[1]);
								$DB->AddCondFS("id", "=", $POST_DATA["id"]);
								
								if(isset($POST_DATA["start_event"]["date"]) && !empty($POST_DATA["start_event"]["date"])) {
									$start_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["start_event"]["date"])), date('m', strtotime($POST_DATA["start_event"]["date"])), date('d', strtotime($POST_DATA["start_event"]["date"])), date('H', strtotime($POST_DATA["start_event"]["date"])), date('i', strtotime($POST_DATA["start_event"]["date"])), date('s', strtotime('00')));
									$DB->AddValue("start_event", $start_time[0], $start_time[1]);
								} else {
									$DB->AddValue("start_event", $smart_time[0], $smart_time[1]);
								}
								if(isset($POST_DATA["end_event"]["date"]) && !empty($POST_DATA["end_event"]["date"])) {
									$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["end_event"]["date"])), date('m', strtotime($POST_DATA["end_event"]["date"])), date('d', strtotime($POST_DATA["end_event"]["date"])), date('H', strtotime($POST_DATA["end_event"]["date"])), date('i', strtotime($POST_DATA["end_event"]["date"])), date('s', strtotime('00')));
									if($start_time > $end_time) {
										$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["start_event"]["date"])), date('m', strtotime($POST_DATA["start_event"]["date"])), date('d', strtotime($POST_DATA["start_event"]["date"])), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									}
									$DB->AddValue("end_event", $end_time[0], $end_time[1]);
								} else {
									if(isset($POST_DATA["start_event"]["date"]) && !empty($POST_DATA["start_event"]["date"])) {
										$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["start_event"]["date"])), date('m', strtotime($POST_DATA["start_event"]["date"])), date('d', strtotime($POST_DATA["start_event"]["date"])), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									} elseif(isset($POST_DATA["public"]["date"]) && !empty($POST_DATA["public"]["date"])) {
										$end_time = $DB->SmartTime(date('Y', strtotime($POST_DATA["public"]["date"])), date('m', strtotime($POST_DATA["public"]["date"])), date('d', strtotime($POST_DATA["public"]["date"])), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									} else {
										$end_time = $DB->SmartTime(date('Y'), date('m'), date('d'), date('H', mktime(18, 0, 0, 0, 0, 0)), date('i', strtotime('00')), date('s', strtotime('00')));
									}
									$DB->AddValue("end_event", $end_time[0], $end_time[1]);
								}
								/*if(isset($POST_DATA["start_event"]["year"], $POST_DATA["start_event"]["month"], $POST_DATA["start_event"]["day"], $POST_DATA["start_event"]["hours"], $POST_DATA["start_event"]["minutes"], $POST_DATA["seconds"])) {
									$start_time = $DB->SmartTime($POST_DATA["start_event"]["year"], $POST_DATA["start_event"]["month"], $POST_DATA["start_event"]["day"], $POST_DATA["start_event"]["hours"], $POST_DATA["start_event"]["minutes"], $POST_DATA["seconds"]);
									$DB->AddValue("start_event", $start_time[0], $start_time[1]);
								} else {
									$DB->AddValue("start_event", $smart_time[0], $smart_time[1]);
								}
								if(isset($POST_DATA["end_event"]["year"], $POST_DATA["end_event"]["month"], $POST_DATA["end_event"]["day"], $POST_DATA["end_event"]["hours"], $POST_DATA["end_event"]["minutes"], $POST_DATA["seconds"])) {
									$end_time = $DB->SmartTime($POST_DATA["end_event"]["year"], $POST_DATA["end_event"]["month"], $POST_DATA["end_event"]["day"], $POST_DATA["end_event"]["hours"], $POST_DATA["end_event"]["minutes"], $POST_DATA["seconds"]);
									$DB->AddValue("end_event", $end_time[0], $end_time[1]);
								} else {
									$DB->AddValue("start_event", $smart_time[0], $smart_time[1]);
								}*/

								if (!$DB->Update(1))
								{
									$status = false;
									$this->output["messages"]["bad"][] = 402; // ������ �� ��� ���������� ���������
								}

								else
								{
									$this->output["messages"]["good"][] = 102; // �������� ������� ��������
									
									if($this->use_tags)
									{
										// ������ ��� ����� ����� � ��������
										$DB->SetTable("tags_posts");
										$DB->AddCondFS("module_id", "=", $this->module_id);
										$DB->AddCondFS("entry_id", "=", $POST_DATA["id"]);
										$DB->Delete();
										if ($POST_DATA["tags"]) {
											$tags = explode(",", $POST_DATA["tags"]); // ��������� �� �������
											foreach ($tags as $tag)
											{
												$tag = trim($tag);
												$DB->SetTable("tags_tags");
												$DB->AddField("id");
												$DB->AddCondFS("tag", "=", $tag);
												$res = $DB->Select(1);
												if($row = $DB->FetchObject($res)) // ���� ����� ����� ��� ����
												{
													$DB->FreeRes();
													$DB->SetTable("tags_posts");
													$DB->AddValue("tag_id", $row->id);
													$DB->AddValue("module_id", $this->module_id);
													$DB->AddValue("entry_id", $POST_DATA["id"]);
													$DB->AddValue("link_suffix", $Engine->engine_uri.$POST_DATA["id"].".".$this->item_ext); // !!!! ����� ��������!
													$DB->AddValue("cache_title", $POST_DATA["title"]);
													$DB->AddValue("cache_short", $POST_DATA["short_text"]); 
													$DB->Insert();									 			
													// echo "<strong>".$row->id."</strong><br />";
												}
												else
												{
													// ������� ����� ����� � ����
													$DB->FreeRes();
													$DB->SetTable("tags_tags");
													$DB->AddValue("tag", $tag);
													$DB->Insert();
													$tag_id = $DB->LastInsertID();
													
													// ������������ ����� � �������
													$DB->FreeRes();
													$DB->SetTable("tags_posts");
													$DB->AddValue("tag_id", $tag_id);
													$DB->AddValue("module_id", $this->module_id);
													$DB->AddValue("entry_id", $POST_DATA["id"]);
													$DB->AddValue("link_suffix", $Engine->engine_uri.$POST_DATA["id"].".".$this->item_ext); // !!!! ����� ��������!
													$DB->AddValue("cache_title", $POST_DATA["title"]);
													$DB->AddValue("cache_short", $POST_DATA["short_text"]);
													$DB->Insert();									 			
												}
											}
										}
									 	//CF::Debug($tags);
									}
									$Engine->LogAction($this->module_id, "item", $POST_DATA["id"], "alter");
								}
							}

							if ($FILE_DATA && $this->status)
							{
								if (is_array($result = $this->Imager->CreateOutputFiles($POST_DATA["id"])))
								{
									$this->status = false;
									$this->output["messages"] = array_merge($this->output["messages"], $result);
								}

								if ($this->status)
								{

									$DB->SetTable($this->db_prefix . "items");
									$DB->AddValues(array(
										"image_data" => $this->Imager->ext,
//										"image_width" => $MAIN_OUTPUT_WIDTH,
//										"image_height" => $MAIN_OUTPUT_HEIGHT,
										));
									$DB->AddCondFS("id", "=", $POST_DATA["id"]);

									if (!$DB->Update(1))
									{
										$this->output["messages"]["bad"][] = 450; // ������ ���������� ���������� �� �����������
									}

									else
									{
										$this->output["messages"]["good"][] = 150; // ���������� �� ����������� ���������
									}
								}
							}


							if (!$this->status)
							{
								$this->output["scripts_mode"][] = 'tiny_mce_news';
								$this->output['plugins'][] = 'jquery.ui.datepicker.min';
								$this->output['plugins'][] = 'jquery-ui-timepicker-addon';
								
								$this->display_variant = "edit_item";
								$this->form_data = array(
									"id" => $POST_DATA["id"],
									"cat_id" => $POST_DATA["cat_id"],
									"uripart" => $POST_DATA["uripart"],
									"public_time" => $POST_DATA["public"]["date"],/*array(
										"year" => $POST_DATA["public"]["year"],
										"month" => $POST_DATA["public"]["month"],
										"day" => $POST_DATA["public"]["day"],
										"hours" => $POST_DATA["public"]["hours"],
										"minutes" => $POST_DATA["public"]["minutes"],
									),*/
									"start_event" => isset($POST_DATA["start_event"]["date"]) ? $POST_DATA["start_event"]["date"] : '',/*array(
										"year" => isset($POST_DATA["start_event"]["year"]) ? $POST_DATA["start_event"]["year"] : '',
										"month" => isset($POST_DATA["start_event"]["month"]) ? $POST_DATA["start_event"]["month"] : '',
										"day" => isset($POST_DATA["start_event"]["day"]) ? $POST_DATA["start_event"]["day"] : '',
										"hours" => isset($POST_DATA["start_event"]["hours"]) ? $POST_DATA["start_event"]["hours"] : '',
										"minutes" => isset($POST_DATA["start_event"]["minutes"]) ? $POST_DATA["start_event"]["minutes"] : '',
									),*/
									"end_event" => isset($POST_DATA["end_event"]["date"]) ? $POST_DATA["end_event"]["date"] : '',/*array(
										"year" => isset($POST_DATA["end_event"]["year"]) ? $POST_DATA["end_event"]["year"] : '',
										"month" => isset($POST_DATA["end_event"]["month"]) ? $POST_DATA["end_event"]["month"] : '',
										"day" => isset($POST_DATA["end_event"]["day"]) ? $POST_DATA["end_event"]["day"] : '',
										"hours" => isset($POST_DATA["end_event"]["hours"]) ? $POST_DATA["end_event"]["hours"] : '',
										"minutes" => isset($POST_DATA["end_event"]["minutes"]) ? $POST_DATA["end_event"]["minutes"] : '',
									),*/
									"seconds" => $POST_DATA["seconds"],
									"title" => htmlspecialchars($POST_DATA["title"]),
									"short_text" => htmlspecialchars($POST_DATA["short_text"]),
									"full_text" => htmlspecialchars($POST_DATA["full_text"])
								);
							}

							if ($FILE_DATA)
							{
								@unlink($FILE_DATA["tmp_name"]);
							}
						}
						break;

					case "add_comment":
					{
						if ($Engine->ModuleOperationAllowed("cat.comment", $this->current_cat_id) && isset(/*$POST_DATA["item_id"], */$POST_DATA["author_name"], $POST_DATA["author_from"], $POST_DATA["author_email"], $POST_DATA["text"]) && CF::IsNaturalNumeric($POST_DATA["item_id"]))
						{
							$this->status = true;

							if ($this->status && !CF::IsNonEmptyStr($POST_DATA["author_name"] = trim(htmlspecialchars($POST_DATA["author_name"]))))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 311; // �� ��������� ���� "�����"
							}

							if ($this->status && CF::IsNonEmptyStr($POST_DATA["author_email"] = trim(htmlspecialchars($POST_DATA["author_email"]))) && !CF::ValidateEmail($POST_DATA["author_email"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 312; // �������� ������ ���� e-mail
							}


							if ($this->status)
							{
								if ($this->comments_message_size_limit)
								{
									$POST_DATA["text"] = substr($POST_DATA["text"], 0, $this->comments_message_size_limit);
								}

								$POST_DATA["text"] = trim(htmlspecialchars($POST_DATA["text"]));

								if (!CF::IsNonEmptyStr($POST_DATA["text"]))
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 313; // �� ��������� ���� "����� �����������"
								}
							}


							if ($this->status && $this->comments_captcha_path)
							{
								if (!isset($_SESSION["code"]))
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 314; // ��� ������������� �������
								}

								elseif ($POST_DATA["code"] === "")
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 315; // �� ������ ��� �������������
								}

								elseif ($POST_DATA["code"] != $_SESSION["code"])
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 316; // ������� ������ ��� �������������
								}
							}


							if ($this->status && CF::IsNaturalNumeric($this->comments_antiflood_time_limit))
							{
								$DB->SetTable($this->db_prefix . "comments");
								$DB->AddExp("COUNT(*)");
								$DB->AddCondFS("sid", "=", $Auth->sid);
								$DB->AddCondFX("time", ">=", "(NOW() - INTERVAL '$this->comments_antiflood_time_limit' SECOND)", false);
								$res = $DB->Select(1);
								list($num) = $DB->FetchRow($res);
								$DB->FreeRes($res);

								if ($num)
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 317; // �������� ��������� ��� �� ������
								}
							}


							if ($this->status)
							{
								$POST_DATA["author_from"] = trim(htmlspecialchars($POST_DATA["author_from"]));

								$DB->SetTable($this->db_prefix . "comments");
								$DB->AddValue("is_active", $this->use_comments_premoderation ? 0 : 1);
								$DB->AddValue("time", "NOW()", "X");
								$DB->AddValue("item_id", $POST_DATA["item_id"]);  //  !!! $this->current_item_id // item_id ������ �� ��������� �� �����, � ����� ����������� ����� ��������� ������ � �������� ���������, ���� ID ��������� ��������� ������
								$DB->AddValue("sid", $Auth->sid);
								$DB->AddValue("ip", $Auth->ip);
								$DB->AddValue("author_name", $POST_DATA["author_name"]);
								$DB->AddValue("author_from", $POST_DATA["author_from"]);
								$DB->AddValue("author_email", $POST_DATA["author_email"]);
								$DB->AddValue("text", $POST_DATA["text"]);

								if ($DB->Insert())
								{
									$this->output["messages"]["good"][] = 106; // ����������� ������� ��������
									$Engine->LogAction($this->module_id, "comment", $DB->LastInsertID(), "create comment");
									
									$DB->Exec("
											UPDATE `" . $this->db_prefix . "items`
											SET `num_comments` = `num_comments` + 1
											WHERE `id` = " . $this->current_item_id . "
											LIMIT 1
											");
								}

								else
								{
									$this->output["messages"]["bad"][] = 406; // ������ �� ��� ���������� �����������
								}
							}


							if (!$this->status)
							{
								$this->comment_form_data = array(
									"author_name" => $POST_DATA["author_name"],
									"author_from" => $POST_DATA["author_from"],
									"author_email" => $POST_DATA["author_email"],
									"text" => $POST_DATA["text"],
									"code" => $this->comments_captcha_path,
									);
							}
						}
					}



					case "save_comment":
					{
						if ($Engine->ModuleOperationAllowed("cat.comments.handle", $this->current_cat_id) && isset($POST_DATA["id"], $POST_DATA["author_name"], $POST_DATA["author_from"], $POST_DATA["author_email"], $POST_DATA["text"]) && CF::IsNaturalNumeric($POST_DATA["id"]))
						{
							$this->status = true;

							if ($this->status && !CF::IsNonEmptyStr($POST_DATA["author_name"] = trim(htmlspecialchars($POST_DATA["author_name"]))))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 311; // �� ��������� ���� "�����"
							}

							if ($this->status && CF::IsNonEmptyStr($POST_DATA["author_email"] = trim(htmlspecialchars($POST_DATA["author_email"]))) && !CF::ValidateEmail($POST_DATA["author_email"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 312; // �������� ������ ���� e-mail
							}


							if ($this->status)
							{
								if ($this->comments_message_size_limit)
								{
									$POST_DATA["text"] = substr($POST_DATA["text"], 0, $this->comments_message_size_limit);
								}

								$POST_DATA["text"] = trim(htmlspecialchars($POST_DATA["text"]));

								if (!CF::IsNonEmptyStr($POST_DATA["text"]))
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 313; // �� ��������� ���� "����� �����������"
								}
							}


							if ($this->status)
							{
								$POST_DATA["author_from"] = trim(htmlspecialchars($POST_DATA["author_from"]));

								$DB->SetTable($this->db_prefix . "comments");

								if ($this->auto_activate_comments)
								{
									$DB->AddValue("is_active", 1);
								}

								$DB->AddValue("time", "NOW()", "X");
								$DB->AddValue("sid", $Auth->sid);
								$DB->AddValue("ip", $Auth->ip);
								$DB->AddValue("author_name", $POST_DATA["author_name"]);
								$DB->AddValue("author_from", $POST_DATA["author_from"]);
								$DB->AddValue("author_email", $POST_DATA["author_email"]);
								$DB->AddValue("text", $POST_DATA["text"]);
								$DB->AddCondFS("id", $POST_DATA["id"]);
								$DB->AddCondFS("item_id", $this->current_item_id);

								if ($DB->Update(1))
								{
									if ($DB->AffectedRows())
									// ��������� �� ����� ��� ������� ��������� ID ���������
									{
										$this->output["messages"]["good"][] = 107; // ����������� ������� �������
										$Engine->LogAction($this->module_id, "comment", $POST_DATA["id"], "alter comment");
									}
								}

								else
								{
									$this->output["messages"]["bad"][] = 407; // ������ �� ��� ���������� �����������
								}
							}


							if (!$this->status)
							{
								$this->comment_form_data = array(
									"id" => $POST_DATA["id"],
									"author_name" => $POST_DATA["author_name"],
									"author_from" => $POST_DATA["author_from"],
									"author_email" => $POST_DATA["author_email"],
									"text" => $POST_DATA["text"],
									);
							}
						}
					}
				}
			}
		}
		elseif (isset($_GET["node"], $_GET["action"]) && ($_GET["node"] == $this->node_id))
		{
			switch ($_GET["action"])
			{
				case "add_item": {
					if ($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) || $Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id)) {
						$this->output["scripts_mode"][] = 'tiny_mce_news';
						$this->output['plugins'][] = 'jquery.ui.datepicker.min';
						$this->output['plugins'][] = 'jquery-ui-timepicker-addon';
						$this->display_variant = "add_item";
						$this->form_data = array(
							"cat_id" => $this->current_cat_id,
							"uripart" => "",
							"public_time" => "",/*array(
								"year" => "",
								"month" => "",
								"day" => "",
								"hours" => "",
								"minutes" => "",
							),*/
							"start_event" => "",/*array(
								"year" => "",
								"month" => "",
								"day" => "",
								"hours" => "",
								"minutes" => "",
							),*/
							"end_event" => "",/*array(
								"year" => "",
								"month" => "",
								"day" => "",
								"hours" => "",
								"minutes" => "",
							),*/
							//"seconds" => "",
							"title" => "",
							"short_text" => "",
							"full_text" => "",
							"tags" => ""
						);
					}
				}
				break;

				case "edit_item": {
					if (($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) || $Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id)) && isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"])) {
						$this->output["scripts_mode"][] = 'tiny_mce_news';
						$this->output['plugins'][] = 'jquery.ui.datepicker.min';
						$this->output['plugins'][] = 'jquery-ui-timepicker-addon';
						$this->display_variant = "edit_item";
						$this->form_data = array(
							"id" => "",
							"cat_id" => "",
							"uripart" => "",
							"public_time" => "",/*array(
								"year" => "",
								"month" => "",
								"day" => "",
								"hours" => "",
								"minutes" => "",
								"seconds" => "",
							),*/
							"start_event" => "",/*array(
								"year" => "",
								"month" => "",
								"day" => "",
								"hours" => "",
								"minutes" => "",
								"seconds" => "",
							),*/
							"end_event" => "",/*array(
								"year" => "",
								"month" => "",
								"day" => "",
								"hours" => "",
								"minutes" => "",
								"seconds" => "",
							),*/
							"title" => "",
							"short_text" => "",
							"full_text" => "",
						);

						/*$res = $DB->Exec("
							SELECT `cat_id`, `uripart`,
							YEAR(`time`) AS 'public_year', MONTH(`time`) AS 'public_month', DAYOFMONTH(`time`) AS 'public_day', HOUR(`time`) AS 'public_hours', MINUTE(`time`) AS 'public_minutes', SECOND(`time`) AS 'public_seconds',
							YEAR(`start_event`) AS 'start_year', MONTH(`start_event`) AS 'start_month', DAYOFMONTH(`start_event`) AS 'start_day', HOUR(`start_event`) AS 'start_hours', MINUTE(`start_event`) AS 'start_minutes', SECOND(`start_event`) AS 'start_seconds',
							YEAR(`end_event`) AS 'end_year', MONTH(`end_event`) AS 'end_month', DAYOFMONTH(`end_event`) AS 'end_day', HOUR(`end_event`) AS 'end_hours', MINUTE(`end_event`) AS 'end_minutes', SECOND(`end_event`) AS 'end_seconds',
							`title`, `short_text`, `full_text`
							FROM `" . $this->db_prefix . "items`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1
							");*/
						$res = $DB->Exec("
							SELECT `cat_id`, `uripart`, `time` AS 'public_time',`start_event` AS 'start_time',`end_event` AS 'end_time',`title`, `short_text`, `full_text`, `image_data`
							FROM `" . $this->db_prefix . "items`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1
							");
						
						if ($this->use_tags)	// ���� ���������� �����
						{
							$res_tags = $DB->Exec("SELECT *
								FROM `tags_tags` t, `tags_posts` p
								WHERE t.id = p.tag_id
								AND p.module_id =".$this->module_id."
								AND p.entry_id =".$_GET["id"]);
							$tags = "";
							if ($row_tags = $DB->FetchObject($res_tags))
							{
								$tags .= $row_tags->tag;
							}
							while ($row_tags = $DB->FetchObject($res_tags))
							{
								$tags .= ", ".$row_tags->tag;
							}
						};

						if ($row = $DB->FetchObject($res))
						{
							$DB->FreeRes($res);
							$this->status = true;
							$this->form_data = array(
								"id" => $_GET["id"],
								"cat_id" => $row->cat_id,
								"uripart" => $row->uripart,
								"public_time" => $row->public_time,/*array(
									"year" => sprintf("%04d", $row->public_year),
									"month" => sprintf("%02d", $row->public_month),
									"day" => sprintf("%02d", $row->public_day),
									"hours" => sprintf("%02d", $row->public_hours),
									"minutes" => sprintf("%02d", $row->public_minutes),
								),*/
								"start_event" => $row->start_time,/*array(
									"year" => sprintf("%04d", $row->start_year),
									"month" => sprintf("%02d", $row->start_month),
									"day" => sprintf("%02d", $row->start_day),
									"hours" => sprintf("%02d", $row->start_hours),
									"minutes" => sprintf("%02d", $row->start_minutes),
								),*/
								"end_event" => $row->end_time,/*array(
									"year" => sprintf("%04d", $row->end_year),
									"month" => sprintf("%02d", $row->end_month),
									"day" => sprintf("%02d", $row->end_day),
									"hours" => sprintf("%02d", $row->end_hours),
									"minutes" => sprintf("%02d", $row->end_minutes),
								),*/
								//"seconds" => sprintf("%02d", $row->public_seconds),
								"title" => $row->title,
								"image_data" => $row->image_data,
								"short_text" => $row->short_text,
								"full_text" => $row->full_text,
								"tags" => $this->use_tags ? $tags : ""
							);
						}

						else
						{
							$DB->FreeRes($res);
							$this->status = false;
							$this->output["messages"]["bad"][] = 310; // �������� �� ������ � ���� ������
						}
					}
				}
				break;

				case "hide_item":
				case "unhide_item": {
					if (($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) || $Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id)) && isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$this->display_variant = $_GET["action"];
						$this->form_data = array();
						$value = ($_GET["action"] == "unhide_item") ? 1 : 0;

						if ($DB->Exec("
							UPDATE `" . $this->db_prefix . "items`
							SET `is_active` = '$value'
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1
							"))
						{
							$this->status = true;
							$this->output["messages"]["good"][] = $value ? 104 : 103; // ������� ������� ������/�������
							$Engine->LogAction($this->module_id, "item", $_GET["id"], $value ? "hide" : "unhide");
						}
						else
						{
							$this->status = false;
							$this->output["messages"]["bad"][] = $value ? 404 : 403; // ������ �� ��� �������/�������� �������
						}
					}
				}
				break;

				case "delete_item": {
					if ($Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) && isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$this->display_variant = "delete_item";
						$this->form_data = array();

												
						$res = $DB->Exec("
							SELECT `image_data`
							FROM `" . $this->db_prefix . "items`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1
							");

						if ($row = $DB->FetchObject($res))
						{
							$DB->FreeRes($res);

							if ($DB->Exec("
								DELETE FROM `" . $this->db_prefix . "items`
								WHERE `id` = '" . $_GET["id"] . "'
								LIMIT 1
								"))
							{
								$this->output["messages"]["good"][] = 105; // ������� ������� �������

								if ($this->Imager)
								{
									$this->Imager->SetProps($_GET["id"], $row->image_data);
									$this->Imager->DeleteOutputFiles();
								}

//								$DB->Exec("UPDATE `" . $this->db_prefix . "cats`
//									`items_num` = `items_num` - 1
//									WHERE `id` = '" . $row->cat_id . "'
//									LIMIT 1");

								$Engine->LogAction($this->module_id, "item", $_GET["id"], "delete");
							}

							else
							{
								$this->output["messages"]["bad"][] = 405; // ������ �� ��� �������� �������
							}
						}

						else
						{
							$DB->FreeRes($res);
							$this->output["messages"]["bad"][] = 310; // �������� �� ������ � ���� ������
						}
					}
				}
				break;

				case "edit_comment": {
					if ($Engine->ModuleOperationAllowed("cat.comments.handle", $this->current_cat_id) && isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$this->display_variant = "edit_item";
						$this->form_data = array(
							"id" => 0,
							"author_name" => "",
							"author_from" => "",
							"author_email" => "",
							"text" => "",
							);

						$res = $DB->Exec("
							SELECT `author_name`, `author_from`,  `author_email`, `text`
							FROM `" . $this->db_prefix . "comments`
							WHERE `id` = '" . $_GET["id"] . "'
							LIMIT 1
							");

						if ($row = $DB->FetchObject($res))
						{
							$DB->FreeRes($res);
							$this->status = true;
							$this->form_data = array(
								"id" => $_GET["id"],
								"author_name" => $row->author_name,
								"author_from" => $row->author_from,
								"author_email" => $row->author_email,
								"text" => $row->text,
								);
						}

						else
						{
							$DB->FreeRes($res);
							$this->status = false;
							$this->output["messages"]["bad"][] = 318; // ����������� �� ������ � ���� ������
						}
					}
				}
				break;

				case "hide_comment":
				case "unhide_comment": { 
					if ($Engine->ModuleOperationAllowed("cat.comments.handle", $this->current_cat_id) && isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$this->display_variant = $_GET["action"];
						$this->form_data = array();
						$value = ($_GET["action"] == "unhide_comment") ? 1 : 0;

						if ($DB->Exec("
							UPDATE `" . $this->db_prefix . "comments`
							SET `is_active` = '$value'
							WHERE `id` = '" . $_GET["id"] . "' AND `item_id` = '$this->current_item_id'
							LIMIT 1
							"))
						{
							if ($DB->AffectedRows())
							{
								$this->status = true;
								$this->output["messages"]["good"][] = $value ? 109 : 108; // ����������� ������� �����/������
								$Engine->LogAction($this->module_id, "comment", $_GET["id"], $value ? "hide comment" : "unhide comment");
							}
						}

						else
						{
							$this->status = false;
							$this->output["messages"]["bad"][] = $value ? 409 : 408; // ������ �� ��� �������/�������� �����������
						}

					}
				}
				break;

				case "delete_comment": {
					if ($Engine->ModuleOperationAllowed("cat.comments.handle", $this->current_cat_id) && isset($_GET["id"]) && CF::IsNaturalNumeric($_GET["id"]))
					{
						$this->display_variant = "delete_comment";
						$this->form_data = array();
//
//						if ($row = $DB->FetchObject($res))
//						{
//							$DB->FreeRes($res);

							if ($DB->Exec("
								DELETE FROM `" . $this->db_prefix . "comments`
								WHERE `id` = '" . $_GET["id"] . "' AND `item_id` = '$this->current_item_id'
								LIMIT 1
								"))
							{
								if ($DB->AffectedRows())
								{
									$this->output["messages"]["good"][] = 110; // ����������� ������� �����
									$Engine->LogAction($this->module_id, "comment", $_GET["id"], "delete comment");
									
									$DB->Exec("
										UPDATE `" . $this->db_prefix . "items`
										SET `num_comments` = `num_comments` - 1
										WHERE `id` = " . $this->current_item_id . "
										LIMIT 1
										");
								}
							}

							else
							{
								$this->output["messages"]["bad"][] = 410; // ������ �� ��� �������� �����������
							}
//						}

//						else
//						{
//							$DB->FreeRes($res);
//							$this->output["messages"]["bad"][] = 310; // �������� �� ������ � ���� ������
//						}
					}
				}
				break;
			}
		}
	}

	
	function DeleteNewsImage($news_id) 
	{
		global $Engine, $DB;
	
		$res = $DB->Exec("
			SELECT `image_data`
			FROM `" . $this->db_prefix . "items`
			WHERE `id` = '" . $news_id . "'
			LIMIT 1
		");

		if ($row = $DB->FetchObject($res))
		{ 
			$DB->FreeRes($res);
			if ($this->Imager && !is_null($row->image_data))
			{
				$this->Imager->SetProps($news_id, $row->image_data);
				$this->Imager->DeleteOutputFiles();
				
				$res = $DB->Exec("
					UPDATE `". $this->db_prefix . "items`
					SET image_data = null WHERE `id` = '" . $news_id . "'
					LIMIT 1
				");	
				$Engine->LogAction($this->module_id, "item", $news_id, "delete_image");
			}
		}
		else
		{
			$this->output["messages"]["bad"][] = 405; // ������ �� ��� �������� �������
		}
		$DB->FreeRes($res);
	}
	
	function Announce($folder_id, $display_limit, $include_subcats, $skip = NULL, $sort_params = false, $sort_orders = false)
	{
		global $DB, $Engine;
        $this->output["folder_uri"] = $folder_uri = $Engine->FolderURIbyID($folder_id); // ������� ������� ������ �����
        $folder_data = $Engine->FolderDataByID($folder_id);
		$this->output["folder_title"] = $folder_data["title"];        

		$this->output["news"] = array();
		
		//������� ����� ���������� ��� ������ ���������		
		$DB->SetTable($this->db_prefix . "items");
		$DB->AddField("id");
		$DB->AddAltFS("cat_id", "=", $this->current_cat_id);

		if ($include_subcats)
		{
			foreach ($this->ListCats($this->current_cat_id, true) as $elem)
			{
				$DB->AddAltFS("cat_id", "=", $elem);
			}
		}

		$DB->AppendAlts();
		
		$DB->AddCondFP("is_active");
		if($this->mode != "new_full_list") {
			$DB->AddCondFX("time", "<=", "NOW()"); // ������� ������?
		}
		$res = $DB->Select();
		
		$num_rows = $DB->NumRows($res);
		

		$DB->SetTable($this->db_prefix . "items");
		$DB->AddFields(array("id", "is_active", "image_data", "time", "title", "short_text", "full_text", "start_event"));
		$DB->AddAltFS("cat_id", "=", $this->current_cat_id);

		if ($include_subcats)
		{
			foreach ($this->ListCats($this->current_cat_id, true) as $elem)
			{
				$DB->AddAltFS("cat_id", "=", $elem);
			}
		}

		$DB->AppendAlts();

//		if ($this->show_users)
//		{
//			$DB->AddJoin(AUTH_DB_PREFIX . "users.id", "author_id");
//			$DB->AddField(AUTH_DB_PREFIX . "users.displayed_name");
//		}

		$DB->AddCondFP("is_active");
		if($this->mode != "new_full_list") {
			$DB->AddCondFX("time", "<=", "NOW()"); // ������� ������?
		}

		if ($sort_params) {
			foreach($sort_params as $ind=>$param)
				$DB->AddOrder($param, (isset($sort_orders[$ind]) ? $sort_orders[$ind] : true));
		}

		$DB->AddOrder("time", true);
		$res = $DB->Select($display_limit, $skip);
		
		header("Content-type: text/html; charset=windows-1251");

		while ($row = $DB->FetchObject($res))
		{
			if ($row->image_data && $this->Imager)
			{
				$this->Imager->SetProps($row->id, $row->image_data);
				$output_files = $this->Imager->ListOutputFiles();
			}

			else
			{
				$output_files = array();
			}

			$this->output["news"][] = array(
				"id" => $row->id,
				"is_active" => (bool) $row->is_active,
				"link" => $this->ItemURIbyID($row->id, $folder_id),
				"time" => $row->time,
				"title" => $row->title,
				"image_data" => $row->image_data,
				"short_text" => $row->short_text,
				"has_full_text" => CF::IsNonEmptyStr($row->full_text),
//				"author_id" => $row->author_id,
//				"author_name" => $this->show_users ? $this->displayed_name : "",
				"output_files" => $output_files,
				"start_event" => $row->start_event,			
				);
				
			$this->output["cat_id"] = $this->current_cat_id;
			
			if ($skip <= $num_rows - $display_limit)
				$this->output["prev_skip"] = $skip + $display_limit;
			
			if ($skip >= $display_limit)
				$this->output["next_skip"] = $skip - $display_limit;
				
			$this->output["news_folder_id"] = $folder_id;
			$this->output["display_limit"] = $display_limit;
		}

		//$this->output["comments"] = $this->ListComments($row->id, $this->current_cat_id, $display_limit);
			
		  

		$DB->FreeRes($res);
	}


	function DateToCyr($date) {
		$monthes = array('January' => '������', 'February' => '�������', 'March' => '����', 'April' => '������', 'May' => '���', 'June' => '����', 'July' => '����', 'August' => '������', 'September' => '��������', 'October' => '�������', 'November' => '������', 'December' => '�������');
		foreach ($monthes as $key=>$value)
			if (strpos($date, $key) != -1) {
				return str_replace($key, $value, $date);
			}
		return $date;
	}
					

	function ParseURI()
	{
		global $DB, $Engine;
		$parts = array_filter(explode("/", $this->module_uri));
		
		$this->current_uri = $Engine->engine_uri;

		$this->current_cat_data = array(
			"uri" => $this->current_uri,
			"title" => "",
			"descr" => "",
			);

//		if (!$at_root)
//		{
			foreach ($parts as $key => $uripart)
			{ 
				if (CF::IsNonEmptyStr($uripart))
				{
					$is_item = (substr($uripart, -(strlen($this->item_ext) + 1)) == ".$this->item_ext"); // ���������, ����� �� ����� ���� ����������� ����������

					if ($is_item)
					{
						//if ($this->use_hfu)
//						{
							$parts2 = explode(".$this->item_ext", $uripart);
							$ENTRY_KEY = $parts2[0];

							if ($ENTRY_KEY === "")
							{
								continue; // ����������� �� /category/.html ;)
							}
//						}

						//else
//						{
//							1; //���� �� ��������
//						}

						$DB->SetTable($this->db_prefix . "items");
						$DB->AddFields(array("id", "is_active", "image_data", "uripart", "time", "title", "short_text", "full_text", "num_views", "num_comments", "start_event"));
						//$DB->AddCondFS("cat_id", "=", $this->current_cat_id);

//						if ($this->show_users)
//						{
//							$DB->AddJoin(AUTH_DB_PREFIX . "users.id", "author_id");
//							$DB->AddField(AUTH_DB_PREFIX . "users.displayed_name");
//						}


						if (/*!$this->use_hfu || */CF::IsNaturalNumeric($ENTRY_KEY))
						{	
							$DB->AddCondFS("id", "=", $ENTRY_KEY);
						}

						else
						{
							$DB->AddCondFS("uripart", "LIKE", $parts2[0]);
						}


						//if (!$this->manage_access)
						if (!$Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id)) {
							$DB->AddCondFP("is_active");
							if($this->mode != "new_full_list") {
								$DB->AddCondFX("time", "<=", "NOW()"); // ������� ������?
							}
						}

						$res = $DB->Select(1);
					
						if ($row = $DB->FetchObject($res))
						{	
							if ($row->image_data && $this->Imager)
							{
								$this->Imager->SetProps($row->id, $row->image_data);
								$output_files = $this->Imager->ListOutputFiles();
							}

							else
							{
								$output_files = array();
							}
							$this->output["scripts_mode"][] = 'input_datetimepicker';
							$this->mode = $this->output["scripts_mode"][] = $this->output["mode"] = "detail";
							
							$this->current_item_id = (int)$row->id;
							if ($this->use_tags)	// ���� ���������� �����
							{
								$res_tags = $DB->Exec("SELECT *
									FROM `tags_tags` t, `tags_posts` p
									WHERE t.id = p.tag_id
									AND p.module_id =".$this->module_id."
									AND p.entry_id =".$this->current_item_id);
								$tags = array();
								while ($row_tags = $DB->FetchObject($res_tags))
								{
									$tags[] = $row_tags->tag;
								}
							}
							$this->output["item"] = $this->current_item_data = array(
								"id" => $this->current_item_id,
								"is_active" => (bool)$row->is_active,
								"uripart" => $row->uripart ? $row->uripart : $row->id,
								"time" => $row->time,
								"title" => $row->title,
			//					"short_text" => $row->short_text,
			//					"full_text" => $row->full_text,
								"text" => $row->full_text ? $row->full_text : $row->short_text,
//								"author_id" => $row->author_id,
//								"author_name" => $this->show_users ? $this->displayed_name : "",
								"tags" => $this->use_tags ? $tags : "",
								"output_files" => $output_files,
								"num_views" => (int)$row->num_views + 1, 
								"num_comments" => (int)$row->num_comments,
								"start_event" => $row->start_event,
								);

								$this->current_uri .= "$ENTRY_KEY.$this->item_ext";
								$Engine->AddFootstep($this->current_uri, $row->title, "", false, false, $this->module_id);
						//}

/*						else
						{
							$this->output["item"] = false;
							$this->output["messages"]["bad"][] = 301; // ������� �� �������
						}*/

						break;
					}
				}

				else
				{
					$DB->SetTable($this->db_prefix . "cats");
					$DB->AddFields(array("id", "uripart", "title", "descr", "options"));
					$DB->AddCondFS("pid", "=", $this->current_cat_id);
					$DB->AddCondFS("uripart", "LIKE", $uripart);

					if (!$Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id))
					{
						$DB->AddCondFP("is_active");
					}

					$res = $DB->Select(1);

					if ($row = $DB->FetchObject($res))
					{
						$DB->FreeRes($res);
						$this->current_cat_id = (int)$row->id;
						$this->current_cat_data = array(
							"uri" => $this->current_cat_id,
							"title" => $row->title,
							"descr" => $row->descr,
							);

						foreach (explode(";", $row->options) as $elem)
						{
							if ($elem)
							{
								$parts = explode(":", trim($elem));
								$this->output["cat_options"][$parts[0]] = $parts[1];
							}
						}

						$this->current_uri .= "$row->uripart/";
						$Engine->AddFootstep($this->current_uri, $row->title, "", true, false, $this->module_id);
					}

					else
					{
						$DB->FreeRes($res);
						break;
					}
				}
			}
		}


/*		$this->current_uri = $Engine->engine_uri;

		foreach ($this->footsteps as $data)
		{
			$this->current_uri .= "$data->uripart/";
		}

		if ($this->mode == "detail")
		{
			$this->current_uri .= "$ENTRY_KEY.$this->item_ext";
		}*/


		// !!! ������� ���������� �������� URI

		if ($_GET)
		{
			$this->current_uri .= ("?" . http_build_query($_GET));
		}

		if ($_SERVER["REQUEST_URI"] != $this->current_uri)
		{
			$Engine->HTTP404();
//			CF::Redirect($this->current_uri);
		}

//		$at_root = !$this->footsteps;

//		$this->output["footsteps"] = $this->footsteps;
		$this->output["cat_id"] = $this->current_cat_id;
		$this->output["current_cat_data"] = $this->current_cat_data;
	}



	function ListComments($item_id, $cat_id, $limit = false)
	{
		global $DB, $Engine;
		$output = array();

		if ( (CF::IsNaturalNumeric($item_id) || $item_id==-1) && $Engine->ModuleOperationAllowed("cat.comment", $cat_id)) // !!! ����������� ������, �������� ��� ��������� ������
		{
			$DB->SetTable($this->db_prefix . "comments");
			$DB->AddFields(array("id", "is_active", "time", "ip", "author_name", "author_from", "author_email", "text"));

			if ($cat_id != -1 && !$Engine->ModuleOperationAllowed("cat.comments.handle", $cat_id))
			{
				$DB->AddCondFP("is_active");
			}

			if ($item_id != -1) $DB->AddCondFS("item_id", "=", $item_id);
			$DB->AddOrder("time", true); // !!! �� �������� ������� � ���������: ������ ���� ����� ����� �����������
			if ($limit && CF::IsNaturalNumeric($limit)) $res = $DB->Select($limit); 
			else $res = $DB->Select();
			
			while ($row = $DB->FetchObject($res))
			{
				$output[] = array(
					"id" => (int)$row->id,
					"is_active" => (bool)$row->is_active,
					"time" => $row->time,
					"ip" => $row->ip,
					"author_name" => $row->author_name,
					"author_from" => $row->author_from,
					"author_email" => $row->author_email,
					"text" => $row->text
					);
			}

			$DB->FreeRes($res);
		}

		return $output;
	}

	function FullList($per_page_input, $include_subcats = false, $tag_id = null, $date_sort_order = true, $date_filter = false, $output_folder_id = false)
	{ 
	
		global $DB, $Engine, $Auth;
		$this->output["news"] = array();
		$this->output["folder_uri"] = $folder_uri = $Engine->engine_uri; // !!! ���������

		$this->ParseURI();
		$this->ProcessHTTPdata();
		

		$flag = ($this->allow_comments && $Engine->ModuleOperationAllowed("cat.comment", $this->current_cat_id));
		$this->output["display_comments"] = $flag;
		$this->comment_form_data = array(
			"author_name" => ((isset($Auth) && isset($Auth->user_displayed_name)) ? $Auth->user_displayed_name : ""),
			"author_from" => "",
			"author_email" => "",
			"text" => "",
			"code" => $this->comments_captcha_path
			);

		if ($this->current_item_id && $this->allow_comments)
		{
			$this->output["comments"] = $this->ListComments($this->current_item_id, $this->current_cat_id);
		}
	
		if ($this->mode != "detail")
		{
			if ($per_page_input)
			{ 
				require_once INCLUDES . "Pager.class";

				$DB->SetTable($this->db_prefix . "items");
				$DB->AddExp("COUNT(*)");
				$DB->AddAltFS("cat_id", "=", $this->current_cat_id);
				
				if ($tag_id) {
					$DB->AddJoin("tags_posts.entry_id", "id");
					$DB->AddCondFS("tags_posts.tag_id", "=", $tag_id);
				}

				if ($include_subcats)
				{
					foreach ($this->ListCats($this->current_cat_id, true) as $elem)
					{
						$DB->AddAltFS("cat_id", "=", $elem);
					}
				}

				$DB->AppendAlts();

				//if (!$this->manage_access)
				if (!$Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) && !$Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id))
				{
					$DB->AddCondFP("is_active");
					if($this->mode != "new_full_list") {
						$DB->AddCondFX("time", "<=", "NOW()"); // ������� ������?
					}
				}

				$res = $DB->Select();
				list($num) = $DB->FetchRow($res);
				$DB->FreeRes($res);


				$parts = explode("|", $per_page_input);
				$Pager = new Pager($num, @$parts[0], @$parts[1], @$parts[2], @$parts[3]);
				$this->output["pager_output"] = $result = $Pager->Act();
			}


			$DB->SetTable($this->db_prefix . "items");
			$DB->AddFields(array("id", "is_active", "image_data", "time", "title", "short_text", "full_text", "create_user_id", "start_event", "num_views", "num_comments"));
			$DB->AddAltFS("cat_id", "=", $this->current_cat_id);

			if ($include_subcats)
			{
				foreach ($this->ListCats($this->current_cat_id, true) as $elem)
				{
					$DB->AddAltFS("cat_id", "=", $elem);
				}
			}

			$DB->AppendAlts();
			//$DB->AddCondFX("time", "<=", "NOW()");
			
			if ($date_filter) { 
				if (count(explode('-', $date_filter)) < 3) {
					$first_day_filter =  '-01';
					$last_day_filter =  '-31';
				} else {
					$first_day_filter =  '';
					$last_day_filter =  '';
				}
				$DB->AddCondFS("time", ">=", $date_filter.$first_day_filter.' 00:00:00');
				$DB->AddCondFS("time", "<=", $date_filter.$last_day_filter.' 23:59:59');
			}
			
			if ($tag_id) {
				$DB->AddField("tags_posts.link_suffix");
				$DB->AddJoin("tags_posts.entry_id", "id");
				$DB->AddCondFS("tags_posts.tag_id", "=", $tag_id);
			}
			//$DB->AddCondFS("tags_posts.entry_id", "=", $this->db_prefix . "items.id");
			
//			if ($this->show_users)
//			{
//				$DB->AddJoin(AUTH_DB_PREFIX . "users.id", "author_id");
//				$DB->AddField(AUTH_DB_PREFIX . "users.displayed_name");
//			}

			if (!$Engine->ModuleOperationAllowed("cat.items.handle", $this->current_cat_id) && !$Engine->ModuleOperationAllowed("cat.create.items", $this->current_cat_id)) {
				$DB->AddCondFP("is_active");
				if($this->mode != "new_full_list") {
					$DB->AddCondFX("time", "<=", "NOW()"); // ������� ������?
				}
			}

			$DB->AddOrder("time", $date_sort_order);			
			$DB->AddOrder("start_event", $date_sort_order);
			
	

			$res = $DB->Select($per_page_input ? $result["db_limit"] : NULL, $per_page_input ? $result["db_from"] : NULL);

			while ($row = $DB->FetchObject($res)) {
				if ($row->image_data && $this->Imager) {
					$this->Imager->SetProps($row->id, $row->image_data);
					$output_files = $this->Imager->ListOutputFiles();
				} else {
					$output_files = array();
				}
				
				if ($tag_id) //���� �������� ������� �� �����, �� ����� ����� ���� �� ������� �����
					$link = $row->link_suffix;
				else //����� �� ������� ��������
					$link = $this->ItemURIbyID($row->id, ($output_folder_id ? $output_folder_id : $Engine->folder_id));

				$this->output["news"][] = array(
					"id" => $row->id,
					"is_active" => (bool) $row->is_active,
					"link" => $link,
					"time" => $row->time,
					"title" => $row->title,
					"short_text" => $row->short_text,
					"has_full_text" => CF::IsNonEmptyStr($row->full_text),
//					"author_id" => $row->author_id,
//					"author_name" => $this->show_users ? $this->displayed_name : "",
					"output_files" => $output_files,
					"create_user_id" => $row->create_user_id,
					"num_views" => (int)$row->num_views,
					"num_comments" => (int)$row->num_comments,
					"start_event" => $row->start_event,
				);
			}
			$DB->FreeRes($res);
		}
		
	if (CF::IsNaturalNumeric($this->current_item_id))
		{
			$DB->Exec("
				UPDATE `" . $this->db_prefix . "items`
				SET `num_views` = `num_views` + 1
				WHERE `id` = " . $this->current_item_id . "
				LIMIT 1
				");
		}
	}
	
	function CatURIbyID($id, $folder_id)
	{
		global $DB, $Engine;
		$footsteps = array();

		while ($id != $this->root_cat_id)
		{
			$res = $DB->Exec("
				SELECT `pid`, `uripart`
				FROM `" . $this->db_prefix . "cats`
				WHERE `id` = '$id'
				LIMIT 1
				");

			if ($row = $DB->FetchObject($res))
			{
				$DB->FreeRes($res);
				if (CF::IsNonEmptyStr($row->uripart)) // ������� �� poganini ;-)
					$footsteps[$id] = $row->uripart; // ���� �� ���� ������ ���� www.example.com/news//1.html
				$id = $row->pid;
			}

			else
			{
				return false;
			}
		}

		$output = $Engine->FolderURIbyID($folder_id);

		foreach ($footsteps as $elem)
		{
			$output .= "$elem/";
		}

		return $output;
	}



	function ItemURIbyID($id, $folder_id)
	{
		if (CF::IsNaturalNumeric($id))
		{
			global $DB;

			$res = $DB->Exec("
				SELECT `id`, `cat_id`, `uripart`
				FROM `" . $this->db_prefix . "items`
				WHERE `id` = '$id'
				LIMIT 1
				");

			if ($row = $DB->FetchObject($res))
			{
				$DB->FreeRes($res);

				if ($result = $this->CatURIbyID($row->cat_id, $folder_id))
				{
					return $result . (CF::IsNonEmptyStr($row->uripart) ? $row->uripart : $row->id) . "." . $this->item_ext;
				}
			}

			return false;
		}
	}



	function ListCats($pid = 0, $flat = false)
	{
		global $DB, $Engine;
		$output = array();

		$res = $DB->Exec("SELECT `id`, `uripart`, `title`, `descr`
			FROM `" . $this->db_prefix . "cats`
			WHERE `pid` = '$pid' AND `is_active`");
            
		while ($row = $DB->FetchObject($res))
		{
			if ($flat)
			{
				$output[] = $row->id;
				$output = array_merge(
					$output,
					$this->ListCats($row->id, $flat)
					);
			}

			else
			{
				$output[$row->id] = array(
					"uripart" => $row->uripart, // ��������, ���� �������� ������ ����
					"title" => $row->title,
					"descr" => $row->descr,
					"subitems" => $this->ListCats($row->id, $flat),
					"allow" => $Engine->ModuleOperationAllowed("cat.items.handle", $row->id) || $Engine->ModuleOperationAllowed("cat.create.items", $row->id)
					);
			}
		}

		$DB->FreeRes($res);
		return $output;
	}

	/* ������� �� ��������������� */

	/* ������� �� �������� */

	function SendToSubscribers($news_id) // ��������� ������� �����������
	{
		global $DB;

		$array = $this->array;
		
		$query = "SELECT ".$this->db_prefix."subscribers.email FROM ".$this->db_prefix."subscribers, /*".$this->db_prefix."subscribe_cats, */".$this->db_prefix."items
		WHERE
			/*".$this->db_prefix."subscribe_cats.id_subscriber = ".$this->db_prefix."subscribers.id
		AND
			".$this->db_prefix."subscribe_cats.cat_id = ".$this->db_prefix."items.cat_id
		AND*/
			".$this->db_prefix."subscribers.is_confirmed
		AND
			".$this->db_prefix."items.id = '" . $DB->Escape($news_id) . "'";

		$res = $DB->Exec($query);

		// ��������� ������
		$headers = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=windows-1251\r\n";
		$headers .= "From: ".SITE_SHORT_NAME." <".$array["subscribes_settings"]["from_email"].">\r\n";
		/*$headers .= "To: ".$name." <".$email"].">\r\n";*/ // �� �����, ������ ��� �� ������� ������� � ���� while �� ��� ������������
		$headers .= "Reply-To: ".SITE_SHORT_NAME." <".$array["subscribes_settings"]["from_email"].">\r\n";
		$headers .= "X-Priority: 1\r\n";
		$headers .= "X-MSMail-Priority: High\r\n";
		//$headers .= "X-Mailer: mailer www.mydomain.ru";
		$headers .= "X-Mailer: PHP/" . phpversion();

		$subject = $array["subscribes_settings"]["letter_theme"];
		
		$query = "SELECT uripart, title, short_text FROM ".$this->db_prefix."items WHERE id=".$news_id;
		
		$res_news = $DB->Exec($query);
		
		$row_news = $DB->FetchObject($res_news);
		
		if (!($uripart = $row_news->uripart)) $uripart = $news_id;
		$message = "<b><a href=\"http://".$_SERVER["SERVER_NAME"]."/".$array["subscribes_settings"]["news_uri"].$uripart.".html\">".$row_news->title."</a></b><br /><br />".$row_news->short_text;

		$count = 0; // ����� ������� ������� �����
		// �������� ��������
		while ($row = $DB->FetchObject($res))
		{
			if (mail($row->email, $subject, $message, $headers)) $count++;
		}
		$DB->FreeRes($res);
		return $count;
	}



	function ClearSubscribers() // �������� ��������������� �����������
	{
		global $DB;

		$query = "DELETE FROM ".$this->db_prefix."subscribers, ".$this->db_prefix."subscribed_cats
		WHERE
			".$this->db_prefix."subscribers.id = ".$this->db_prefix." subscribe_cats.subscriber_id
		AND
		 !".$this->db_prefix."subscribers.is_confirmed
		AND
			".$this->db_prefix."subscribers.register_time + INTERVAL 1 MONTH < NOW()";

		$res = $DB->Exec($query);
		$count = $DB->AffectedRows($res); // ����� �������� �� ��������������� �����������
		$DB->FreeRes($res);
		return $count;
	}



	function AddSubscriber($email, $cats) // ��������� ����������, ������� �-���� � ������ ��������� ��������
	{
		global $DB;
		// ��������� �-����, ���� ������ ���, � ���� ����, �� ���������� ���������� �-�����
		$query = "INSERT IGNORE INTO ".$this->db_prefix."subscribers (`email`, `register_time`) VALUES (".$DB->Escape($email).", NOW())";
		$res = $DB->Exec($query);
		$DB->FreeRes($res);
		// ��������� ��������� ����������, ��� ������������� ������� �������
		$query = "INSERT IGNORE INTO ".$this->db_prefix."subscribe_cats (`subscriber_id`, `cat_id`) VALUES ";
		$query_values = "";
		$first_value = true; // ������ ���� ������ �� ��������, ����� ��� ���������� �������
		foreach ($cats as $i => $cat_id) // ������� ������ ��������� � �������
		{
			if(!$first_value)
				$query_values .= ", ";
			else
				$first_value = false;
			$query_values .= "(LAST_INSERT_ID(), ".$cat_id.")";
		}
		$query = $query . $query_values;
		$res = $DB->Exec($query);
		$DB->FreeRes($res);
	}


	function ConfirmSubscriber($code) // ������������� �����������    CONCAT(s.id, s.email)
	{
		global $DB;
		$query = "UPDATE IGNORE ".$this->db_prefix."subscribers s SET s.is_confirmed=1
		WHERE
				`confirm_code` = '".$DB->Escape($code)."'";
		$res = $DB->Exec($query);
		$count = $DB->AffectedRows($res); // ����� ������������� �����������, ��-�������� ������ ���� ����
		$DB->FreeRes($res);
		return $count;
	}


	function DeleteSubscriber($code)  // �������� ����������� CONCAT(s.id, s.email)
	{
		global $DB;
		$query = "DELETE FROM ".$this->db_prefix."subscribers s, ".$this->db_prefix."subscribe_cats c
		WHERE
			s.id = c.subscriber_id
		AND
			`code` = '".$DB->Escape($code)."'";
		$res = $DB->Exec($query);
		$count = $DB->AffectedRows($res); // ����� �������� �����������, ��-�������� ������ ���� ����
		$DB->FreeRes($res);
		return $count;
	}


	function GetCode($email) // ��������� ����
	{
		global $DB;
		$query = "SELECT `code` FROM ".$db_prefix."subscribers s
		WHERE
				s.email = \"".$DB->Escape($email)."\"
		LIMIT 0, 1";
		$res = $DB->Exec($query);
		if ($row = $DB->FetchObject($res))
		{
			$code = $row->code;
		};
		$DB->FreeRes($res);
		return $code;
	}


	function SearchAlter($search_input, $cat_ids, $include_subcats, $news_folder_id)
	{
		global $DB, $Engine, $Auth;
		$output = array();
		
		$cat_ids = explode(',', $cat_ids);
		if ($include_subcats) {
			$DB->SetTable($this->db_prefix . "cats");
			$DB->AddField('id');
			foreach($cat_ids as $id)
				$DB->AddAltFS('pid', '=', $id); 
			$DB->AppendAlts();
			if ($res = $DB->Select())
			{
				while ($row = $DB->FetchAssoc($res))
					$cat_ids[] = $row['id'];
			}
			
			$DB->FreeRes();
		}
		
		
		$DB->SetTable($this->db_prefix . "items");
		$DB->AddFields(array("id", "title", "short_text", "cached_text"));
		$DB->AddCondFP("is_active");

		
		foreach ($cat_ids as $id)
		{
			$DB->AddAltFS("cat_id", "=", $id);
		}
		
		

		$DB->AppendAlts();
		$DB->AddAltFS("title", "LIKE", "%$search_input%");
		$DB->AddAltFS("short_text", "LIKE", "%$search_input%");
		$DB->AddAltFS("cached_text", "LIKE", "%$search_input%");
		$DB->AppendAlts();
		$DB->AddOrder("time", true);
		$res = $DB->Select();

		while ($row = $DB->FetchObject($res))
		{ 
			$output[] = array(
				"uri" => '/'.($news_folder_id ?  $Engine->FolderURIbyID($news_folder_id) : 'news/').$row->id.'.'.$this->item_ext, //$this->ItemURIbyID($row->id, $node_folder_id),
				"title" => $row->title,
				"text" => $row->cached_text ? strip_tags($row->cached_text) : strip_tags($row->short_text) ,
			);
		}

		$DB->FreeRes($res);
		
		return $output;
	}


	function Search($node_id, $search_input)
	{
		global $DB, $Engine, $Auth;
		$output = array();

		$DB->SetTable(ENGINE_DB_PREFIX . "nodes");
		$DB->AddFields(array("folder_id", "params"));
		$DB->AddCondFS("id", "=", $node_id);
		$DB->AddCondFS("module_id", "=", $this->module_id);
		$DB->AddCondFP("is_active");

		$res = $DB->Select(1);

		if ($row = $DB->FetchObject($res))
		{
			$DB->FreeRes($res);
			$parts = explode(";", $row->params);

			switch ($parts[0])
			{
				case "full_list":
					if (isset($parts[1]))
					{
						$node_folder_id = (int)$row->folder_id;
						$this->root_cat_id = (int)$parts[1];
					}

					else
					{
						die($Engine->debug_level ? "ENews module error #1099: illegal node provided for search (#$node_id)." : "");
					}
					break;


				default:
					die($Engine->debug_level ? "ENews module error #1099: illegal node provided for search (#$node_id)." : "");
					break;
			}
		}

		else
		{
			$DB->FreeRes($res);
			die($Engine->debug_level ? "ENews module error #1099: illegal node provided for search (#$node_id)." : "");
		}


		$cats = array();

		foreach (array_merge(array($this->root_cat_id), $this->ListCats($this->root_cat_id, true)) as $elem)
		{
			if ($Engine->OperationAllowed($this->module_id, "cat.search", $elem, $Auth->usergroup_id))
			{
				$cats[] = $elem;
			}
		}


		if ($cats) // !!! ���� ���� ���������, � ������� �������� �����
		{
			$DB->SetTable($this->db_prefix . "items");
			$DB->AddFields(array("id", "title", "cached_text"));
			$DB->AddCondFP("is_active");

			foreach ($cats as $elem)
			{
				$DB->AddAltFS("cat_id", "=", $elem);
			}

			$DB->AppendAlts();
			$DB->AddAltFS("title", "LIKE", "%$search_input%");
			$DB->AddAltFS("cached_text", "LIKE", "%$search_input%");
			$DB->AppendAlts();
			$DB->AddOrder("time", true);
			$res = $DB->Select();

			while ($row = $DB->FetchObject($res))
			{
				$output[] = array(
					"uri" =>  $this->ItemURIbyID($row->id, $node_folder_id),
					"title" => $row->title,
					"text" => $row->cached_text,
					);
			}

			$DB->FreeRes($res);
		}
		
		return $output;
	}



	function Output()
	{
		return $this->output;
	}
}

?>