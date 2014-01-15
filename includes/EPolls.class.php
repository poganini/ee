<?php

class EPolls
// version: 2.1
// date: 2010-03-11
{
	var $output;
	var $node_id;
	var $module_id;
	var $module_uri;
	var $db_prefix;
	var $mode;

	var $display_access;
	var $manage_access;
	var $full_access;

	var $cookie_prefix;

	var $issue_id;
	var $set_id;
	
	var $status;
	var $display_variant;
	var $form_data;


	function EPolls($global_params, $params, $module_id, $node_id = NULL, $module_uri = NULL, $additional = NULL)
	{
		global $DB, $Engine, $Auth;
		
		$this->node_id = $node_id;
		$this->module_id = $module_id;
		$this->module_uri = $module_uri;
		$this->output = array();
		$this->output["messages"] = array("good" => array(), "bad" => array());

		$this->output["display_access"] = $this->display_access = 1;
		$this->output["manage_access"] = $this->manage_access = $Auth->logged_in;
		$this->output["full_access"] = $this->full_access = $Auth->logged_in;

		$this->cookie_prefix = "poll_";
		
		$parts = explode(";", $global_params);
		$this->db_prefix = $parts[0];
		$this->item_ext = (isset($parts[1]) && CF::IsNonEmptyStr($parts[1])) ? $parts[1] : "html";

		$parts = explode(";", $params);
		$this->output["mode"] = $this->mode = $parts[0];

//		if ($this->manage_access)
//		{

			if ($this->mode == "poll") // !!! подумать, как лучше извлечь issue_id перед выполнением ProcessHTTPdata()
			{
				if (isset($parts[2]))
				{ 
					$this->issue_id = $issue_id = $parts[2]; // !!! подумать, стоит ли делать issue_id свойством класса
				}
				
					

				else
				{
					//die("EPolls module error #1001 at node $this->node_id: POLL ID not specified in POLL mode.");
				}
			}

			$this->ProcessHTTPdata();
//		}
		
		if (!is_array($this->output)) return;
		
		$this->output["status"] = $this->status;
		$this->output["display_variant"] = $this->display_variant;
		$this->output["form_data"] = $this->form_data;




		switch ($this->mode)
		{
			case "poll":
	
				if (isset($parts[3]) && $parts[3])
				{
					$this->output['set_id'] = $this->set_id = $parts[2];
				}
				else
				if (isset($parts[2]))
				{
					$this->issue_id = $issue_id = $parts[2]; // !!! подумать, стоит ли делать issue_id свойством класса | не стоит
				}

				else
				{
					//die("EPolls module error #1001 at node $this->node_id: POLL ID not specified in POLL mode.");
				}


				if (isset($parts[1]))
				{
					$this->output["folder_id"] = $parts[1];
					$this->output["results_link"] = $Engine->FolderURIbyID($parts[1]);
				}

				else
				{
					die("EPolls module error #1002 at node $this->node_id: RESULTS FOLDER ID not specified in POLL mode.");
				}
				$this->output["module_id"] = $this->module_id;
				$this->Poll($folder_id);
				break;


			case "full_list":
			
				$this->output["uri"] = $Engine->unqueried_uri;
				if (isset($parts[1]))
					$this->ListIssues($parts[1]);
				else $this->ListIssues();
				$this->output["hidden_folder_id"] = $_REQUEST['folder_id'];
				$this->output["hidden_node_id"] = $_REQUEST['node_id'];
				$this->output["hidden_issue_id"] = $_REQUEST['issue_id'];
				break;

			case "add_issue":
				$time = time() + 30*24*3600;
				
				$this->output["day_end"] = date("d", $time);
				$this->output["month_end"] = date("m", $time);
				$this->output["year_end"] = date("Y", $time);
				$this->output["hidden_node_id"] = $parts[1];
				$this->output["hidden_folder_id"] = $parts[2];
			
				$this->output["mode"] = "add_issue";
				$this->output["polls_sets"] = $this->GetPollsSets();
				if ($Engine->OperationAllowed($this->module_id, "polls.handle", 0, $Auth->usergroup_id))
					$this->output["allow_handle"] = 1;
				else
					$this->output["allow_handle"] = 0;
				break;

			default:
				die("EPolls module error #1100 at node $this->node_id: unknown mode &mdash; $this->mode.");
				break;
		}
	}


	function GetPollsSets()
	{
		global $DB, $Engine;
		$sets = array();
		$DB->SetTable($this->db_prefix . "sets");
		$DB->AddFields(array("id", "name"));
		$res = $DB->Select();
		while ($row = $DB->FetchAssoc($res))
		{
			$sets[] = $row;
		}
		$DB->FreeRes($res);
		return $sets;
	}

	function Poll(/*$issue_id, */$folder_id)
	{
		global $DB, $Engine;
		
		if (isset($this->set_id))
		{
			$this->GetPollSetTitle($this->set_id);
			$DB->SetTable($this->db_prefix . "issues");
			$DB->AddField("id");
			$DB->AddCondFS("poll_set_id", "=", $this->set_id);
			if ($res = $DB->Select())
			{
				$this->issue_id = array();
				while ($row = $DB->FetchAssoc($res))
				{
					$this->issue_id[] = $row["id"]; // !!! делать ли ID ключом? | не делать!
				}	
			}
			$DB->FreeRes();
		}
		
		if (isset($this->issue_id)) 
		{
			$this->output["issue_id"] = $this->issue_id;
				
			$DB->SetTable($this->db_prefix . "issues");
			$DB->AddFields(array("id", "poll_set_pos", "title", "begin_time", "end_time"));
			if (is_array($this->issue_id))
			{
				foreach($this->issue_id as $id)
					$DB->AddAltFS("id", "=", $id);
				$DB->AppendAlts();
			}
			else
				$DB->AddCondFS("id", "=", $this->issue_id);
			
			if (!$this->manage_access)
			{
				$DB->AddCondFP("is_active");
			}
	
			$res = $DB->Select();
	
			while ($row = $DB->FetchObject($res))
			{
				$this->output["title"][] = $row->title;
		
				if (strtotime($row->end_time) <= (time() + TIMEZONE_OFFSET_SECONDS))
				{
					if (!isset($this->output["display_mode"])) $this->output["display_mode"] = "ended";
				}
		
				elseif ( (is_array($this->issue_id) && !$this->VoteAllowed($this->issue_id[0])) || !$this->VoteAllowed($this->issue_id))
				{
					if (!isset($this->output["display_mode"])) $this->output["display_mode"] = "voted";
				}
		
				else
				{
					if (!isset($this->output["display_mode"])) $this->output["display_mode"] = "choices";
					$this->output["choices"][$row->id] = $this->ListChoices($row->id);
					
					
				}
			}
			if (is_array($this->issue_id))
			{
				$this->output["choicesStr"] = "";
				foreach ($this->output['choices'] as $choices) 
				{
					foreach ($choices as $key=>$data)
						if ($data["is_active"])
							$this->output["choicesStr"].= $key.":".$data["title"].";";
					$this->output["choicesStr"].= '%';
				}
			}
			
			$DB->FreeRes($res);
		}
		if ($Engine->OperationAllowed($this->module_id, "polls.handle", 0, $Auth->usergroup_id))
			$this->output["allow_handle"] = 1;
		else
			$this->output["allow_handle"] = 0;
	}



	function VoteAllowed($issue_id)
	{
		global $DB, $Auth;

		if (isset($_COOKIE[$this->cookie_prefix . $issue_id]))
		{
			return false;
		}

		$DB->SetTable($this->db_prefix . "votes");
		$DB->AddExp("COUNT(*)");
		$DB->AddCondFS("issue_id", "=", $issue_id);
		//$DB->AddAltFS("session_id", "=", $Auth->sid);

		if ($Auth->user_id)
		{
			$DB->AddCondFS("user_id", "=", $Auth->user_id);
		} else 
			$DB->AddCondFS("user_id", "=", -1);

		$DB->AppendAlts();
		$res = $DB->Select(1);
		$row = $DB->FetchRow($res);
		$DB->FreeRes($res);

		if ($row[0])
		{
			return false;
		}

		return true;
	}



	function ListChoices($issue_id)
	{
		global $DB;
		$output = array();

		$DB->SetTable($this->db_prefix . "choices");
		$DB->AddFields(array("id", "is_active", "title"));
		$DB->AddCondFS("issue_id", "=", $issue_id);

		if (!$this->manage_access)
		{
			$DB->AddCondFP("is_active");
		}

		$DB->AddOrder("pos");
		$res = $DB->Select();

		while ($row = $DB->FetchAssoc($res))
		{
			$output[$row["id"]] = $row; // !!! делать ли ID ключом?
			unset($output[$row["id"]]["id"]);
		}

		$DB->FreeRes($res);

		return $output;
	}



	function ListIssues($node_id = false)
	{
		global $DB, $Engine, $Auth;
		$this->output["issues"] = array();
		
		if ($node_id)
		{
			$DB->SetTable("engine_nodes");
			$DB->AddCondFS("id", "=", $node_id);
			if ($res = $DB->Select())
			{
				$row = $DB->FetchAssoc($res);
				$node_params = explode(';', $row['params']);
				if ($node_params[3])
				$poll_set_id = $node_params[2];
				else $issue_id = $node_params[2];
			}
			$DB->FreeRes($res);
		}
		
		$DB->SetTable($this->db_prefix . "issues");
		$DB->AddFields(array("id", "title", "begin_time", "end_time"));
		if (isset($issue_id))
		{
			$DB->AddCondFS("id", "=", $issue_id);
		}
		else
		if (isset($poll_set_id))
		{
			$DB->AddCondFS("poll_set_id", "=", $poll_set_id);
			$DB->AddOrder("poll_set_pos", false);
		}
		
		if (!$this->manage_access)
		{
			$DB->AddCondFP("is_active");
		}

		
		$DB->AddOrder("begin_time", true);
		$DB->AddOrder("end_time", true);
		
		$DB->AddOrder("title");
		$res = $DB->Select();

		while ($row = $DB->FetchObject($res))
		{
			$row->id = (int)$row->id;
			$this->output["issues"][$row->id] = array(
				"id" => $row->id,
				"title" => $row->title,
				"begin_time" => $row->begin_time,
				"end_time" => $row->end_time,
				"results" => $this->ListResults($row->id),
				);
		}

		$DB->FreeRes($res);
		
		if (isset($poll_set_id))
			$this->GetPollSetTitle($poll_set_id);
		
		if ($Engine->OperationAllowed($this->module_id, "polls.handle", 0, $Auth->usergroup_id))
			$this->output["allow_handle"] = 1;
		else
			$this->output["allow_handle"] = 0;
	}

	function GetPollSetTitle($set_id)
	{
		global $DB, $Engine;
		$DB->SetTable($this->db_prefix . "sets");
		$DB->AddField("name");
		$DB->AddCondFS("id", "=", $set_id);
		if ($res = $DB->Select())
		{
			$this->output['poll_set_title'] = $DB->FetchObject($res)->name;
		}
		$DB->FreeRes();
	}

	function ListResults($issue_id)
	{
		global $DB;
		$output = $this->ListChoices($issue_id);
		$total_num_votes = 0;

		foreach ($output as $key => $data)
		{
			$DB->SetTable($this->db_prefix . "votes");
			$DB->AddExp("COUNT(*)");
			$DB->AddCondFS("choice_id", "=", $key);

//			if (!$this->manage_access) // !!! подумать, как лучше выдавать данные админу
//			{
				$DB->AddCondFP("is_active");
//			}

			$res = $DB->Select();
			list($num_votes) = $DB->FetchRow($res);
			$DB->FreeRes($res);

			$output[$key]["num_votes"] = $num_votes;
			$total_num_votes += $num_votes;
		}

		foreach ($output as $key => $data)
		{
			$output[$key]["percentage_votes"] = $data["num_votes"] ? (100 * $data["num_votes"] / $total_num_votes) : 0;
		}

		return $output;
	}



	function ChoiceBelongs($choice_id, $issue_id, $admin_mode = false)
	{
		global $DB;

		$DB->SetTable($this->db_prefix . "choices");
		$DB->AddJoin($this->db_prefix . "issues.id", $this->db_prefix . "choices.issue_id");
		$DB->AddExp("COUNT(`" . $this->db_prefix . "choices`.`id`)");
		$DB->AddCondFS("id", "=", $choice_id);
		$DB->AddCondFS("issue_id", "=", $issue_id);

		if (!$admin_mode)
		{
			$DB->AddCondFP("is_active");
			$DB->AddCondFP($this->db_prefix . "issues.is_active");
			$DB->AddCondFX($this->db_prefix . "issues.end_time", ">=", "NOW()");
		}

		$res = $DB->Select(1);
		$row = $DB->FetchRow($res);
		$DB->FreeRes($res);

		return (bool) $row[0];
	}



	function SetCookie($issue_id)
	{
		global $DB;

		$DB->SetTable($this->db_prefix . "issues");
		$DB->AddField("end_time");
		$DB->AddCondFS("id", "=", $issue_id);
		$res = $DB->Select(1);

		if ($row = $DB->FetchRow($res))
		{
			$output = setcookie($this->cookie_prefix . $issue_id, 1, strtotime($row[0]));
		}

		else
		{
			$output = false;
		}

		$DB->FreeRes($res);
		return $output;
	}



	function ProcessHTTPdata()
	{
		global $DB, $Auth, $Engine;
					
	
	
		if (isset($_POST[$this->node_id]["cancel"]))
		{
			1;
		}

		elseif (isset($_POST[$this->node_id]) && is_array($_POST[$this->node_id]))
		{
			foreach (array("vote", "add_item", "save_item") as $POST_ACTION)
			{
				if (isset($_POST[$this->node_id][$POST_ACTION]) && is_array($_POST[$this->node_id][$POST_ACTION]))
				{
					$POST_DATA = $_POST[$this->node_id][$POST_ACTION];
					break;
				}
			}

			if (isset($POST_DATA))
			{
			
				foreach ($POST_DATA as $key => $elem)
				{
					$POST_DATA[$key] = trim($elem);
				}

				switch ($POST_ACTION)
				{
					case "vote":
						if (isset($POST_DATA["resultStr"])) {
							$POST_DATA["choice"] = explode(';', $POST_DATA["resultStr"]);
							$POST_DATA["issue_ids"] = explode(';', $POST_DATA["issueIdsStr"]);
							
						}
						if ($this->mode == "poll" && isset($POST_DATA["choice"])) 
						{
							if (!is_array($POST_DATA["choice"]))
								$POST_DATA["choice"] = array($POST_DATA["choice"]);
							$cookieSet = false;
							foreach ($POST_DATA["choice"] as $ind=>$choice)
							{
								if ($choice && CF::IsNaturalNumeric($choice))
								{
									if (isset($POST_DATA["issue_ids"])) {
										$this->issue_id = $POST_DATA["issue_ids"][$ind];
										$this->set_id = $POST_DATA["setId"];
									}
									if ($this->ChoiceBelongs($choice, $this->issue_id) && $this->VoteAllowed($this->issue_id))
									{
										$DB->SetTable($this->db_prefix . "votes");
										$DB->AddValue("issue_id", $this->issue_id);
										$DB->AddValue("choice_id", $choice);
										$DB->AddValue("time", "NOW()", "X");
										$DB->AddValue("is_active", 1);
										$DB->AddValue("session_id", $Auth->sid);
										$DB->AddValue("ip", $Auth->ip); // !!! может быть, лучше брать не из Auth?
										//$DB->AddValue("ua_string", $Auth->ua_str); // !!! может быть, лучше брать не из Auth?
										$DB->AddValue("user_id", $Auth->user_id);
										$DB->AddValue("username", $Auth->username);
			
										if (!is_string($DB->Insert()))
										{
											$DB->SetTable($this->db_prefix . "choices");
											$DB->AddValue("num_votes", "`num_votes` + 1", "X");
											$DB->AddCondFS("id", "=", $choice);
											$DB->Update(1);
			
											$DB->SetTable($this->db_prefix . "issues");
											$DB->AddValue("num_votes", "`num_votes` + 1", "X");
											$DB->AddCondFS("id", "=", $this->issue_id);
											$DB->Update(1);
											
											if (!$cookieSet) {
												$this->SetCookie($this->issue_id);
												$cookieSet = true;
											}
											if (!isset($POST_DATA["resultStr"]))
											{
												CF::Redirect();
											}
										}
									}
								}
							}
							if (isset($POST_DATA["resultStr"]))
							{
								$this->output = '1';
								return;
							}
						}
						break;

					case "add_item":
						
						if (isset($POST_DATA["title"],
							$POST_DATA["begin_year"], $POST_DATA["begin_month"], $POST_DATA["begin_day"],
							$POST_DATA["begin_hours"], $POST_DATA["begin_minutes"], $POST_DATA["begin_seconds"],
							$POST_DATA["end_year"], $POST_DATA["end_month"], $POST_DATA["end_day"],
							$POST_DATA["end_hours"], $POST_DATA["end_minutes"], $POST_DATA["end_seconds"],
							$POST_DATA["choices"]) && $Engine->OperationAllowed($this->module_id, "polls.handle", 0, $Auth->usergroup_id))
						{
							$POST_DATA["choices"] = explode("\n", $POST_DATA["choices"]);
						
							$this->status = true;

							if (!CF::IsNonEmptyStr($POST_DATA["title"]))
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 301; // не указан вопрос
							}

							$choices = array();
							foreach ($POST_DATA["choices"] as $key => $data)
							{
								if (CF::IsNonEmptyStr($data))
								{
									$choices[] = array(
										//"pos" => $data["pos"],
										//"is_active" => (isset($data["is_active"]) && ($data["is_active"] == "on")),
										//"title" => $data["title"],
										"pos" => 0,
										"is_active" => 1,
										"title" => trim(strip_tags($data)),
										);
								}
							}

							if (!$choices)
							{
								$this->status = false;
								$this->output["messages"]["bad"][] = 302; // нет ни одного ответа
							}

							if ($this->status)
							{ 
								$smart_begin_time = $DB->SmartTime($POST_DATA["begin_year"], $POST_DATA["begin_month"], $POST_DATA["begin_day"], $POST_DATA["begin_hours"], $POST_DATA["begin_minutes"], $POST_DATA["begin_seconds"]);
								$smart_end_time = $DB->SmartTime($POST_DATA["end_year"], $POST_DATA["end_month"], $POST_DATA["end_day"], $POST_DATA["end_hours"], $POST_DATA["end_minutes"], $POST_DATA["end_seconds"], "1 MONTH");

								
								if (isset($POST_DATA['is_compound_poll']) && $POST_DATA['is_compound_poll'])
								{
									
									if (isset($POST_DATA['poll_set_new_name']) && $POST_DATA['poll_set_new_name'])
									{
										$DB->SetTable($this->db_prefix . "sets");
										$DB->AddValue("name", $POST_DATA['poll_set_new_name']);
										$DB->Insert();
										$poll_set_id = $DB->LastInsertID();
										$DB->FreeRes();
									}
									else if (isset($POST_DATA['poll_set_id']))
									{
										$poll_set_id = $POST_DATA['poll_set_id'];
									}
									
								}
								
								
								if (isset($poll_set_id))
								{		
									$DB->SetTable($this->db_prefix . "issues");
									$DB->AddExp("COUNT(*)");
									$DB->AddCondFS("poll_set_id", "=", $poll_set_id);
									$res = $DB->Select(1);
									list($set_issues_count) = $DB->FetchRow($res);
									$DB->FreeRes();
								}
								
								$DB->SetTable($this->db_prefix . "issues");
								$DB->AddValue("title", $POST_DATA["title"]);
								$DB->AddValue("begin_time", $smart_begin_time[0], $smart_begin_time[1]);
								$DB->AddValue("end_time", $smart_end_time[0], $smart_end_time[1]);
								$DB->AddValue("is_active", 1);
								if (isset($poll_set_id))
								{
									$DB->AddValue("poll_set_id", $poll_set_id);
									$DB->AddValue("poll_set_pos", $set_issues_count+1);
								}
								
								if ($POST_DATA['hidden_issue_id']) 
									$DB->AddCondFS('id', '=', $POST_DATA['hidden_issue_id']);
								
								
								if ( (!($POST_DATA['hidden_issue_id']) && $DB->Insert()) || ($POST_DATA['hidden_issue_id'] && $DB->Update()) )
								{
									$this->output["messages"]["good"][] = 101; // опрос успешно добавлен
									if ($POST_DATA['hidden_issue_id'])
									{
										$ISSUE_ID = $POST_DATA['hidden_issue_id'];
									} 
									else
									{ 
										$ISSUE_ID =  $DB->LastInsertID();
										$sql = "UPDATE `engine_nodes` SET `params` = \"poll;".$POST_DATA["hidden_folder_id"].";".( isset($poll_set_id) ? $poll_set_id.";1" : $ISSUE_ID.";0")."\" WHERE `id` = ".$POST_DATA["hidden_node_id"]." LIMIT 1;";
										$DB->Exec($sql);
										
									}
									$DB->FreeRes();
									$this->AddChoices($choices, $ISSUE_ID);
									$DB->SetTable("engine_nodes");
									
									$DB->AddCondFS('id', '=', $POST_DATA['hidden_node_id']);
									if ($res = $DB->Select())
									{
										if ($row = $DB->FetchObject($res) && !$POST_DATA['is_compound'])
											CF::Redirect($Engine->FolderURIbyID($row->folder_id));											
									}
									else CF::Redirect();
									
								}

								else
								{
									$this->status = false;
									$this->output["messages"]["bad"][] = 401; // ошибка БД при добавлении опроса
									CF::Redirect();
								}
							}


							if ($this->status)
							{
								//$this->AddChoices($choices, $ISSUE_ID);
							}
							
							
							

						}
						break;



					case "save_issue":
						break;
				}
			}
		}
	}




	function AddChoices($choices, $issue_id)
	{
		global $DB;
	
		foreach ($choices as $data)
		{
			$DB->SetTable($this->db_prefix . "choices");
			//$DB->AddValues($this->db_prefix . "choices");
			$DB->AddValue("issue_id", $issue_id);
			$DB->AddValue("is_active", $data["is_active"]);
			$DB->AddValue("pos", $data["pos"]);
			$DB->AddValue("title", $data["title"]);
			$DB->Insert();
			$DB->FreeRes();
		}
	}




	function Output()
	{
		return $this->output;
	}
}

?>