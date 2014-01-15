<?php

class ETasks
// version: 1.3.5
// date: 2013-11-21
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
    var $items_num;
    
    var $users;
	var $settings;
	
		

	function ETasks($global_params, $params, $module_id, $node_id, $module_uri) {
		global $Engine, $Auth, $DB;
		
		//if (is_null($global_params))
		if (!($Auth->usergroup_id && $Engine->OperationAllowed($module_id, 'tasks.handle', -1, $Auth->usergroup_id)) && ( $params != 'apply_support' && $params != 'apply_support_link' || !($Auth->usergroup_id && $Engine->OperationAllowed($module_id, 'tasks.apply_support', -1, $Auth->usergroup_id) )) ) {
			$this->output = "acc_denied";
		} else {
			$this->settings = @parse_ini_file('ETasks.ini', true);
			$this->module_id = $module_id;	
			$this->module_uri = $module_uri;
			$this->users = array();
			$tasks_system_usergroups = array();
			$DB->SetTable("auth_usergroups");
			if ($res_groups = $DB->Select()) {
				while ($row = $DB->FetchAssoc($res_groups)) {
					if ($Engine->OperationAllowed($module_id, 'tasks.handle', -1, $row['id']))
						$tasks_system_usergroups[] = $row['id'];
				}
				$DB->FreeRes($res_groups);
			}
			
			
			if (!empty($tasks_system_usergroups)) {
				$DB->SetTable("auth_users");
				foreach ($tasks_system_usergroups as $usergroup) 
					$DB->AddAltFS('usergroup_id', '=', $usergroup);
				$DB->AppendAlts();
				$DB->AddCondFS('is_active', '=', 1);
				$res_users = $DB->Select();
				while ($row = $DB->FetchAssoc($res_users))
					$this->users[] = array('id' => $row['id'], 'name'=> $row['displayed_name']);
					
				$DB->FreeRes($res_users);
			}
			$this->output['scripts_mode'] = $this->output['mode'] = 'tasks';

			$this->output['full_task_statuses'] =  array("new", "current", "tobechecked", "done");
			$this->output['module_id'] = $module_id;
		
			if ($params) {  
				$parts = explode(';', $params);
				switch($parts[0]) { 
					case 'executor_reports':
					
						$this->output = array();
						$this->output['id'] = '0';
						$this->output['module_id'] = $module_id;
						$this->output['show_mode'] = 'executor_reports';
						$this->output['full_task_statuses'] = $this->output['task_statuses'] = array("new", "current", "tobechecked", "done");
						if (isset($_REQUEST['reload_data']) && !empty($_REQUEST['reload_data'])) $this->output['tasks']['executor_reports'] = $this->GetTasks($_REQUEST['reload_data']);
						
						if ($this->module_uri == 'print/')
							$this->output['to_print'] = true;
						$this->output['users'] = $this->users;
						$this->output['scripts_mode'][] = $this->output['mode'] = 'tasks';
						$this->output["scripts_mode"][] = 'input_calendar';
						 
						break;
					case 'add_task':
						$this->output = (isset($_REQUEST['add_data']) && $this->AddTask($_REQUEST['add_data']) ) ? 'Задача добавлена' : 'Ошибка при добавлении задачи. Попробуйте выполнить действие еще раз.';
						break;
					case 'update_executors':
						$this->output = array();
						$this->output[] = array('msg' => (isset($_REQUEST['update_data'], $_REQUEST['update_data']['task_id'], $_REQUEST['update_data']['executor_id'], $_REQUEST['update_data']['old_executor_id']) && $updateRes = $this->UpdateExecutors($_REQUEST['update_data']['executor_id'], $_REQUEST['update_data']['task_id'], $_REQUEST['update_data']['old_executor_id']) )  ? iconv('windows-1251', 'utf-8', 'Исполнитель добавлен') : iconv('windows-1251', 'utf-8', 'Ошибка при добавлении исполнителя. Попробуйте выполнить действие еще раз.'));
						$this->output[] = array('responseData'=> $updateRes);
						
						//$this->output = (isset($_REQUEST['add_data'], $_REQUEST['add_data']['task_id'], $_REQUEST['add_data']['executor_id'], $_REQUEST['add_data']['is_new_executor']) && $this->UpdateExecutors($_REQUEST['add_data']['executor_id'], $_REQUEST['add_data']['task_id'], $_REQUEST['add_data']['is_new_executor']) ) ? 'Исполнитель добавлен' : 'Ошибка при добавлении исполнителя. Попробуйте выполнить действие еще раз.';
						break;
					case 'unassign_task':
						$this->output = (isset($_REQUEST['unassign_data']) && $this->UnassignTask($_REQUEST['unassign_data']) ) ? 'Задача снята' : 'Ошибка при снятии задачи. Попробуйте выполнить действие еще раз.';;
						break;
					case 'update_task':
						$this->output = array();
						$this->output[] = array('msg' => (isset($_REQUEST['update_data']) && $updateRes = $this->UpdateTask($_REQUEST['update_data']) ) ? 'Задача обновлена' : 'Ошибка при обновлении задачи. Попробуйте выполнить действие еще раз.');
						$this->output[] = array('responseData'=> $updateRes);
						break;
					case 'update_pager':
						break;
					case 'reload_tasks':
						
						//header("Content-type: application/json; charset=windows-1251");
						header('Content-Type: text/html,  charset=windows-1251');
						header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
						header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
						//header("Expires: " .  date("r", time() - 24*7*3600));
						Header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
						header("Pragma: no-cache");
						$this->output = array();
						//echo "<h1>",  date("H:i:s"), "</h1>";//exit;		
						$filters = isset($_REQUEST['reload_data']['filters']['statuses'])? explode(';', $_REQUEST['reload_data']['filters']['statuses']) : false;
						if ($filters) $_SESSION['tasks_user_filters'] = serialize($filters);
						$tasks = $this->GetTasks($_REQUEST['reload_data']);
						$this->output = (isset($_REQUEST['reload_data']) ? $tasks : false);
						
						if ($this->module_uri != 'print/' && $tasks) {
							list($this->items_num) = $this->GetTasksCount($_REQUEST['reload_data']['filters']);
							require_once INCLUDES . "Pager.class";
							$Pager = new Pager($this->items_num, $this->settings['pager']['items_per_page'], $this->settings['pager']['page_limit'], null, null, array('page'=>intval($_REQUEST['reload_data']['page'])));
							$this->output[] =  array('pager_output'=>$Pager->Act());
						} else {
							$this->output[] =  array('pager_output'=> array());
						}//echo "<h1>",  date("H:i:s"), "</h1>";//exit;							
					break;
					case 'user_task_count': {
						if($Auth->usergroup_id && ($Engine->OperationAllowed($module_id, 'tasks.handle', -1, $Auth->usergroup_id) || $Engine->OperationAllowed($module_id, 'tasks.edit_all', -1, $Auth->usergroup_id))) {
							$this->GetTaskCount($Auth->user_id);
							$this->output['scripts_mode'] = $this->output['mode'] = 'user_task_count';
						}
					}
					break;
					case 'task_count': {
						if (!is_array(unserialize($_SESSION['tasks_user_filters']))) $_SESSION['tasks_user_filters'] = serialize(array("new", "current", "tobechecked", "done"));
						$filters = isset($_SESSION['tasks_user_filters']) ? unserialize($_SESSION['tasks_user_filters']) : false;
						$this->output = array();
						$this->output['users'] = $this->users;
						$this->output['tasks'] = array();
						$this->output['tasks']['my'] = $this->GetUserTasks($Auth->user_id, array('statuses'=>implode(';', $filters)));
						$this->output['show_mode'] = 'my';
						$this->output['id'] = $Auth->user_id;
						$this->output['module_id'] = $module_id;
						$this->output['full_task_statuses'] =  array("new", "current", "tobechecked", "done");
						$this->output['task_statuses'] = $filters  ?: $this->output['full_task_statuses']; 
						$this->output['statuses_titles'] = array("new" => 'Новая', "current"=> 'Выполняется', "tobechecked"=> 'На проверке', "done"=> 'Выполнена');
						$this->output['edit_all'] = $Engine->OperationAllowed($module_id, "tasks.edit_all", -1, $Auth->usergroup_id);
						
						
						// инициализация pager-а
						list($this->items_num) = $this->GetTasksCount(array('author' => $Auth->user_id, 'statuses'=>implode(';',$filters)));
						require_once INCLUDES . "Pager.class";
						$Pager = new Pager($this->items_num, $this->settings['pager']['items_per_page'], $this->settings['pager']['page_limit']); 
						$this->output["pager_output"] = $Pager->Act();
				
						if($Auth->usergroup_id && ($Engine->OperationAllowed($module_id, 'tasks.handle', -1, $Auth->usergroup_id) || $Engine->OperationAllowed($module_id, 'tasks.edit_all', -1, $Auth->usergroup_id))) {
							$this->GetTaskCount($Auth->user_id);
							$this->output['scripts_mode'] = $this->output['mode'] = 'task_count';
						}
					}
					break;
					case "apply_support_link": {
						$this->output['scripts_mode'] = $this->output['mode'] = 'apply_support_link';
						$DB->AddTable("engine_nodes");
						$DB->AddCondFS("params", " LIKE ", "%apply_support%");
						$DB->AddCondFS("module_id", "=", $this->module_id);
						$DB->AddField("folder_id");
						$res = $DB->Select(1);
						while($row = $DB->FetchAssoc($res)) {
							$folder_id = $row["folder_id"];
							$this->output["apply_uri"] = $Engine->FolderURIbyID($folder_id);
						}
						$this->output["allow_handle"] = $Engine->OperationAllowed($module_id, 'tasks.handle', -1, $Auth->usergroup_id);
					}
					break;
					case "apply_support": {
						$this->output['scripts_mode'] = $this->output['mode'] = 'apply_support';
						$this->output['module_id'] = $module_id;
						$this->ApplySupport(isset($_REQUEST["add_data"]) ? $_REQUEST["add_data"] : null);
					}
					break;
					default:
					
					break;
				}
				
			} else {
				if (!is_array(unserialize($_SESSION['tasks_user_filters']))) $_SESSION['tasks_user_filters'] = serialize(array("new", "current", "tobechecked", "done"));
				$filters = isset($_SESSION['tasks_user_filters']) ? unserialize($_SESSION['tasks_user_filters']) : false;
				$this->output = array();
				$this->output['users'] = $this->users;
				$this->output['tasks'] = array();
				$this->output['tasks']['my'] = $this->GetUserTasks($Auth->user_id, array('statuses'=>implode(';', $filters)));
				$this->output['show_mode'] = 'my';
				$this->output['id'] = $Auth->user_id;
				$this->output['module_id'] = $module_id;
				$this->output['full_task_statuses'] =  array("new", "current", "tobechecked", "done");
				$this->output['task_statuses'] = $filters  ?: $this->output['full_task_statuses']; 
				$this->output['statuses_titles'] = array("new" => 'Новая', "current"=> 'Выполняется', "tobechecked"=> 'На проверке', "done"=> 'Выполнена');
				$this->output['edit_all'] = $Engine->OperationAllowed($module_id, "tasks.edit_all", -1, $Auth->usergroup_id);
				$this->output['assigners']  = $this->GetAssigners();
				
				// инициализация pager-а
				list($this->items_num) = $this->GetTasksCount(array('author' => $Auth->user_id, 'statuses'=>implode(';',$filters)));
				require_once INCLUDES . "Pager.class";
				$Pager = new Pager($this->items_num, $this->settings['pager']['items_per_page'], $this->settings['pager']['page_limit']); 
				$this->output["pager_output"] = $Pager->Act();
                $this->output["scripts_mode"][] = 'input_calendar';
				$this->output['scripts_mode'][] = $this->output['mode'] = 'tasks';
				$this->output['plugins'][] = 'jquery.ui.datepicker.min';
				$this->GetTaskCount($Auth->user_id);
			} 
		}
	}
	
	function GetAssigners() {
		global $DB;
		$assigners = array();
		$res = $DB->Exec("SELECT DISTINCT author_name FROM tasks WHERE cancelled=0 ORDER BY author_name");
		while($row = $DB->FetchAssoc($res)) {
			$assigners[] = $row['author_name'];
		}
		return $assigners;
	}

	function GetTaskCount($user_id) {
		global $DB, $Auth, $Engine;
		$res = $DB->Exec("SELECT count(`tasks`.`id`) as `count` FROM `tasks_executors`, `tasks` WHERE `tasks_executors`.`executor_id` = ".$user_id." and `tasks_executors`.`task_id`=`tasks`.`id` and `tasks`.`status` = 'new' and `tasks`.`cancelled` = 0");
		$row = $DB->FetchAssoc($res);
		$this->output['tasks_count']['new'] = $row['count'];
		$res = $DB->Exec("SELECT count(`tasks`.`id`) as `count` FROM `tasks_executors`, `tasks` WHERE `tasks_executors`.`executor_id` = ".$user_id." and `tasks_executors`.`task_id`=`tasks`.`id` and `tasks`.`status` = 'current' and `tasks`.`cancelled` = 0");
		$row = $DB->FetchAssoc($res);
		$this->output['tasks_count']['current'] = $row['count'];
		$res = $DB->Exec("SELECT count(`tasks`.`id`) as `count` FROM `tasks_executors`, `tasks` WHERE `tasks_executors`.`executor_id` = ".$user_id." and `tasks_executors`.`task_id`=`tasks`.`id` and `tasks`.`status` = 'tobechecked' and `tasks`.`cancelled` = 0");
		$row = $DB->FetchAssoc($res);
		$this->output['tasks_count']['tobechecked'] = $row['count'];
	}
	
	function AddTask($data)
	{
		global $DB, $Auth, $Engine;
		
		$DB->SetTable('tasks');
		$DB->AddValues(array(
            "author_id" => $Auth->user_id,
            "author_name" => ( CODEPAGE == 'cp1251' ? iconv('utf-8', 'cp1251' , $data['task_author']) : $data['task_author']),
			"executor_id" => null, //$data['task_executor'],
			"status" => "new",
			"description" => ( CODEPAGE == 'cp1251' ? iconv('utf-8', 'cp1251' , $data['task_descr']) : $data['task_descr']),  
			));
		if ($DB->Insert())
		{
			//$this->MailTask($data['task_executor'], 'new');
			$Engine->LogAction($this->module_id, 'task', $DB->LastInsertId(), 'create');
			return true;
		}
		else return false;
	}
	
	
	function MakeDBConds(&$DB, $filters)
	{
		if (isset($filters['author']) && $filters['author'])
				$DB->AddCondFS('author_id', '=', $filters['author']);
		if (isset($filters['assigner']) && $filters['assigner'])
				$DB->AddCondFS('author_name', '=', iconv('utf-8', 'windows-1251', $filters['assigner']));
		if (isset($filters['tasks_ids']) && $filters['tasks_ids'])
		{
			foreach($filters['tasks_ids'] as $taskId)
				$DB->AddAltFS('id', '=', $taskId);
			$DB->AppendAlts();
		}
		if (isset($filters['assign_date_from']) && $filters['assign_date_from'])
			$DB->AddCondFS('creation_date', '>=', $filters['assign_date_from']);
		if (isset($filters['assign_date_to']) && $filters['assign_date_to'])
			$DB->AddCondFS('creation_date', '<=', $filters['assign_date_to']);
		if (isset($filters['statuses']) && $filters['statuses'])
		{	
			$statuses = explode(';', $filters['statuses']);
			foreach($statuses as $status)
				$DB->AddAltFS('status', '=', $status);
			$DB->AppendAlts();
		}
		else return false;
		return true;
	}
	
	
	
	function UpdateExecutors($executorId, $taskId, $oldExecutorId = false)
	{
		global $DB, $Engine;
		$DB->SetTable('tasks_executors');
		$DB->AddValues(array(
            "task_id" => $taskId,
            "executor_id" => $executorId
			));
		if($oldExecutorId) 
		{
			$DB->AddCondFS("executor_id", "=", $oldExecutorId);
			$DB->AddCondFS("task_id", "=", $taskId);
			if ($DB->Update())
			{
				$DB->SetTable('tasks');
				$DB->AddCondFS('id', '=', $taskId);
				if ($res = $DB->Select()) 
				{
					$row = $DB->FetchAssoc($res);
					if ($oldExecutorId != $row['author_id'])
						$this->MailTask($oldExecutorId, 'remove');
				}
				$DB->FreeRes($res);
				$this->updateTask(array('id' => $taskId, 'executor_id' => $executorId, 'status' => $row['status']), true);
				
				$Engine->LogAction($this->module_id, 'task', $oldExecutorId.'->'.$executorId, 'update executor');
				return true;
			}	
			else return false;
			
		}
		else if (!$oldExecutorId && $DB->Insert())
		{
			$DB->FreeRes();
			$DB->SetTable('tasks');
			$DB->AddCondFS('id', '=', $taskId);
			if ($res = $DB->Select()) 
				$row = $DB->FetchAssoc($res);
			$res = $this->updateTask(array('id' => $taskId, 'assignment_date' => (is_null($row['executor_id'])  ? 1 : 0), 'executor_id' => $executorId, 'status' => $row['status']));
			$Engine->LogAction($this->module_id, 'task', $taskId.'-'.$executorId.'_'.$row['status'], 'create executor');
			return $res;
		}
		else return false;
	}
	
	
	function GetExecutorsByTask($taskId, $encode = false)
	{
		global $DB;
		$executors = array();
		$DB->SetTable("tasks_executors", "te");
		$DB->AddTable("auth_users","au");
        $DB->AddCondFF("te.executor_id","=","au.id");
        $DB->AddCondFS('te.task_id', '=', $taskId);
        $DB->AddField("au.id","executor_id");
        $DB->AddField("au.displayed_name","executor_name");
		if ($res  = $DB->Select())
		{
			while ($row = $DB->FetchAssoc($res)) 
			{
				$executors[] =  array('name' => ($encode ? iconv('windows-1251', 'utf-8', $row['executor_name']) : $row['executor_name']), 'id' => $row['executor_id']);
			}
			$DB->FreeRes($res);
		}
		return $executors;
	}
	
	function GetTasksCount($filters = false)
	{	
		global $DB;
			
		if ($filters) 
		{
			if (isset($filters['executor']) && $filters['executor']) 
			{
				$DB->SetTable('tasks_executors');
				$DB->AddField('task_id');
				$DB->AddCondFS('executor_id', '=', $filters['executor']);
				if ($res = $DB->Select())
				{
					$filters['tasks_ids'] = array();
					while ($row = $DB->FetchAssoc($res)) 
						$filters['tasks_ids'][] = $row['task_id'];
					$DB->FreeRes();
				} 
			}
		}
		
		$DB->SetTable("tasks");
		$DB->AddExp("COUNT(*)");
		if ($filters && !empty($filters))
			$this->MakeDBConds($DB, $filters);
		$res  = $DB->Select();
		return $DB->FetchRow($res);
	}
	
	function GetTasks($data)
	{
		global $DB;
		
		if (isset($data['filters'])) 
		{
			if (isset($data['filters']['executor']) && $data['filters']['executor']) 
			{
				$DB->SetTable('tasks_executors');
				$DB->AddField('task_id');
				$DB->AddCondFS('executor_id', '=', $data['filters']['executor']);
				if ($res = $DB->Select())
				{
					$data['filters']['tasks_ids'] = array();
					while ($row = $DB->FetchAssoc($res)) 
						$data['filters']['tasks_ids'][] = $row['task_id'];
					$DB->FreeRes();
				} 
				if (!isset($data['filters']['tasks_ids']) || empty($data['filters']['tasks_ids'])) 
					return false;
			}
		}
		$DB->SetTable('tasks');
		if (isset($data['filters']) && !$this->MakeDBConds($DB, $data['filters']))
			return false;
		$DB->AddCondFS('cancelled', '=', 0);
		$DB->AddOrder('creation_date', true);
		if (!isset($data['from'])) $data['from'] = 1;
		if ($this->module_uri != 'print/')
			$res = $DB->Select($this->settings['pager']['items_per_page'], (intval($data['from'])-1)*$this->settings['pager']['items_per_page']);
		else $res = $DB->Select();
		if ($res) {
			$tasks = array();
			while ($row = $DB->FetchAssoc($res)) {
				$row['description'] = ( CODEPAGE == 'cp1251' ? iconv('windows-1251', 'utf-8', $row['description']) : $row['description']);
				$row['executor_comment'] = ( CODEPAGE == 'cp1251' ? iconv('windows-1251', 'utf-8', $row['executor_comment']) : $row['executor_comment']);
				$row['executors'] = $this->GetExecutorsByTask($row['id'], CODEPAGE == 'cp1251');
				$author_name = is_null($row['author_name']) ? $this->GetUserNameById($row['author_id']) : $row['author_name'] ;
		 		$row['author_name'] = ( CODEPAGE == 'cp1251' ? iconv('windows-1251', 'utf-8', $author_name) : $author_name);
		 		$row['assigner_name'] = ( CODEPAGE == 'cp1251' ? iconv('windows-1251', 'utf-8', $this->GetUserNameById($row['author_id'])) : $this->GetUserNameById($row['author_id']));
		 		$tasks[] = $row;
		 	}
		 	
		 	return $tasks;		
		 }
		return false;
	}
	
	
	function GetUserNameById($user_id)
	{  
		foreach ($this->users as $user) {
			if ($user['id'] == $user_id)
				return $user['name'];
		}
		return false;
	}

	
	function GetUserTasks($user_id, $filters = false)
	{
		global $DB, $Auth;
		
		$author_name = $this->GetUserNameById($user_id);
		$DB->SetTable("tasks");
		$DB->AddCondFS('author_id', '=', $user_id);
		$DB->AddCondFS('cancelled', '=', 0);
		if ($filters) 
		{	
			if (!$this->MakeDBConds($DB, $filters))
				return false;
		}
		$DB->AddOrder('creation_date', true);
		if ($res = $DB->Select($this->settings['pager']['items_per_page'])) {
			$tasks = array();
			while ($row = $DB->FetchAssoc($res)) {
				$row['executors'] = $this->GetExecutorsByTask($row['id']);
				$row['executor_name'] = $this->GetUserNameById($row['executor_id']);
				$row['author_name'] = is_null($row['author_name']) ? $author_name : $row['author_name'];
				$tasks[] = $row;
		 	}
		 	return $tasks;		
		 }
		return false;
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
	
	function MailTask($user_id, $message_type, $additional = array())
	{
		global $Engine, $DB;
		
		$DB->SetTable("auth_users");
		$DB->AddCondFS('id', '=', $user_id);
		$res = $DB->Select();
		while ($row = $DB->FetchObject($res))
		{
			if ($row->email)
			{	
				$mail_message_patterns = array(
					'headers' => array('%site_short_name%' =>  '=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', 'НГАУ')).'?= '."<admin@".$_SERVER['HTTP_HOST'].">", '%server_name%' => $_SERVER['HTTP_HOST'], '%email%' => $row->email),
					'subject' => array('%site_short_name%' => SITE_SHORT_NAME, '%content%' => &$this->settings['subject'][$message_type]),
					'content' => array('%server_name%' => $_SERVER['HTTP_HOST'], '%executor_name%' => isset($additional['executor_name']) ? $additional['executor_name'] : '', '%tasks_system_folder%' => isset($_REQUEST['page_uri']) ? $_REQUEST['page_uri'] : ''),
					'message' => array('%site_short_name%' => SITE_SHORT_NAME, '%author_name%' => $row->displayed_name, '%time%' => date('Y-m-d H:i:s'), '%content%' => &$this->settings['content'][$message_type])
				);
				
				foreach ($this->settings['subject'] as $ind=>$setting)
					$this->settings['subject'][$ind] = str_replace( array_keys($mail_message_patterns['content']), $mail_message_patterns['content'], $setting);
				

				foreach ($this->settings['content'] as $ind=>$setting)
					$this->settings['content'][$ind] = str_replace( array_keys($mail_message_patterns['content']), $mail_message_patterns['content'], $setting);
				
				foreach ($this->settings['notify_settings'] as $ind=>$setting)
				{
					 $this->settings['notify_settings'][$ind] = str_replace( array_keys($mail_message_patterns[$ind]), $mail_message_patterns[$ind], $setting);
				}
				
				//mail($row->email, $this->settings['notify_settings']['subject'], $this->settings['notify_settings']['message'], $this->settings['notify_settings']['headers']);
				$mailRes = mail($row->email, '=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', $this->settings['notify_settings']['subject'])).'?=', $this->settings['notify_settings']['message'], $this->settings['notify_settings']['headers']);
				$Engine->LogAction($this->module_id, 'task', $user_id, 'mail to executor'.' '.$row->email.'_'.strlen($this->settings['notify_settings']['message']).'_'.$mailRes);
			}
		}
	}
	
	function ManageHTTPdata()
	{
		return;
	}
	
	
	function UnassignTask($data)
	{
		global $DB;
		if (isset($data['id']))
		{ 
			$DB->SetTable('tasks');
			$DB->AddCondFS('id', '=', $data['id']);
			$DB->AddValue('cancelled', 1);
			return $DB->Update();
		}
		return false;
	}
	
	
	function updateTask($data, $noStatusUpdate = false)
	{
		global $DB, $Engine;
		$response = array('id' => isset($data['id']) ? $data['id'] : null);
		
		$DB->SetTable('tasks');
		foreach ($data as $key=>$value)
			if ($key != 'id') 
			{
				if ($key == 'assignment_date' && $value==1) {
					$assignment_date = date('Y-m-d H:i:s');
					$DB->AddValue('assignment_date', $assignment_date);
					$response['assignment_date'] = $assignment_date;
				} 
				else if ($key != 'assignment_date')
				{				
					$DB->AddValue($key, ($key == 'executor_comment' || $key == 'author_name' || $key == 'description'? ( CODEPAGE == 'cp1251' ? iconv('utf-8', 'cp1251' , urldecode($value)) : urldecode($value)) : $value), "S");
					if ($key == 'status' && !$noStatusUpdate && ($value == 'current' || $value == 'done'))
					{
						if ($value == 'current')
							$DB->AddValue('execution_start_date', date('Y-m-d H:i:s'));
						else 
							$DB->AddValue('execution_finish_date', date('Y-m-d H:i:s'));
					}
				}
			}
		$DB->AddCondFS("id", "=", $data['id']);
		$res = $DB->Update();
			
			if (isset($data['status'])) 
			{
				if ($res) $DB->FreeRes($res);
				if (!isset($data['executor_id'])) 
				{
					$executors = $this->GetExecutorsByTask($data['id']);
				} else 
				{
					$executors = array(array('id' => $data['executor_id'], 'name' => $this->GetUserNameById($data['executor_id'])));
				}  
					
				$DB->SetTable('tasks');
				$DB->AddCondFS('id', '=', $data['id']);
				if ($res = $DB->Select())
				{
					$row = $DB->FetchAssoc($res);
					
					foreach ($executors as $executor)
					{
						if ($executor['id'] != $row['author_id'])
						{
							
							if ($data['status'] == 'current' || $data['status'] == 'tobechecked')
								$this->MailTask($row['author_id'], $data['status'], array('executor_name' => $executor['name']));
							else $this->MailTask($executor['id'], $data['status']);
						}
					}
				}
			}	
			return $response;
		
	}
	
	function ApplySupport($add_data = array()) {
		global $DB, $Engine, $Auth;
		
		$DB->SetTable("nsau_people");
		$DB->AddFields(array("id", "comment", "name", "last_name", "patronymic"));
		$DB->AddCondFS("user_id", "=", $Auth->user_id);
		//$this->output["people"] = null; 
		$res = $DB->Select();
		while($row = $DB->FetchAssoc($res)) {
			$people = $row;
			$people["full_name"] = $people["last_name"]." ".$people["name"]." ". $people["patronymic"];
			$this->output["people"] = $people;
		}
		
		$this->output["examples"] = array("У нас не работает принтер", "Не запускается Word", "Сканер не сканирует");
		
		if(count($add_data) > 0) {
			$app_subject = $add_data["app_subject"];
			$DB->SetTable('tasks');
			$DB->AddValues(array(
					"author_id" => $Auth->user_id,
					"author_name" => $Auth->usergroup_comment, 
					"status" => "new",
					//"executor_id" => (int)$this->settings['apply']['executor_id_'.$app_subject], 
					"description" => ( CODEPAGE == 'cp1251' ? iconv('utf-8', 'cp1251' , $add_data['app_descr']) : $data['app_descr']).($people ? " (<a href=\"http://".$_SERVER["HTTP_HOST"]."/people/".$people["id"]."/\">".$people["full_name"]."</a>) " : " (".$Auth->user_displayed_name.") "),
					));//echo $DB->InsertQuery(); exit;
			//if($DB->Insert()) 
			{
				$this->output = "Заявка успешно добавлена";
				/* $taskId = $DB->LastInsertId();
				//$this->MailTask((int)$this->settings['apply']['executor_id_'.$app_subject], "new"); 
				$Engine->LogAction($this->module_id, 'task', $taskId, 'create');
				if((int)$this->settings['apply_'.$app_subject]['executor_id'] != 0) {
					$executorId = (int)$this->settings['apply_'.$app_subject]['executor_id'];
					$this->UpdateExecutors($executorId, $taskId);
				} */
				
				$this->settings['apply'] = array_merge($this->settings['apply'], $this->settings['apply_'.$app_subject]);
				$subject = explode(" ", $add_data["app_descr"]);
				$subject = array_slice($subject, 0, 6); 
				$subject = implode(" ", $subject);
				
				$mail_message_patterns = array(
							'headers' => array(
								'%site_short_name%' => '=?koi8-r?B?'.base64_encode(iconv('windows-1251', 'koi8-r', 'НГАУ')).'?= '."<admin@".$_SERVER['HTTP_HOST'].">",
								'%server_name%' => $_SERVER['HTTP_HOST'],
								'%email%' => $this->settings['apply_'.$app_subject]["extra_email"],
								'%subject%' => $subject
							),
							'subject' => array(
								'%site_short_name%' => SITE_SHORT_NAME
							),
							'message' => array(
								'%time%' => date('Y-m-d H:i:s'),
								'%site_short_name%' => SITE_SHORT_NAME,
								'%author%' => ($people ? $people["full_name"]." (http://".$_SERVER["HTTP_HOST"]."/people/".$people["id"]."/)" : $Auth->user_displayed_name),
								'%assign_to%' => $this->settings['apply_'.$app_subject]["redmine_assign"],
								'%project%' => $this->settings['apply_'.$app_subject]["redmine_project"],
								'%content%' => ( CODEPAGE == 'cp1251' ? iconv('utf-8', 'cp1251' , $add_data['app_descr']) : $data['app_descr']),
							)
						);
				
						foreach ($this->settings['apply'] as $ind=>$setting) {
							$this->settings['apply'][$ind] = str_replace( array_keys($mail_message_patterns[$ind]), $mail_message_patterns[$ind], $setting);
						}
						
						mail($this->settings['apply_'.$app_subject]["extra_email"], '=?koi8-r?B?'.base64_encode(iconv('utf-8', 'koi8-r', $subject)).'?=', $this->settings['apply']['message'], $this->settings['apply']['headers'],/* $this->settings['student_registr_ok']['mess'],  */" -f admin@".$_SERVER['HTTP_HOST']."");
			}
			return ;
		}
	}

	
	function Output()
	{
		return $this->output;
	}
	
	
	
}