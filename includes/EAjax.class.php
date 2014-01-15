<?php
class EAjax
// version: 2.1
// date: 2013-10-11
{
	var $output;

	function GetBaseUrl()
	{
		global $Engine, $DB;
		return $_SERVER['REQUEST_URI'];
	}

	function EAjax($global_params, $params, $module_id, $node_id, $module_uri)
	{
		global $Engine, $Auth, $DB;
	 	
	 	
	  	$this->output = null;
		
		
		if (isset($_REQUEST) && isset($_REQUEST['module'])) {
			
			$DB->SetTable('engine_modules');
			$DB->AddCondFs('id', '=', $_REQUEST['module']);
			if ($res = $DB->Select()) {
				$row = $DB->FetchObject($res);
				/**/
				if (!isset($Engine->modules_privileges[$_REQUEST['module']])) {
					$Engine->modules_privileges += $Engine->GetPrivileges($_REQUEST['module'], NULL, NULL, $Auth->usergroup_id);
				}
				/**/
				include_once INCLUDES . $row->name . CLASS_EXT;
				
				if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'json') {
					ob_start(); 
				}
					
				$Module = new $row->name(
									$row->global_params,
									isset($_REQUEST['params']) ? $_REQUEST['params'] : null,
									$_REQUEST['module'],
									isset($_REQUEST['node']) ? $_REQUEST['node'] : null,
									isset($_REQUEST['module_uri']) ? $_REQUEST['module_uri'] : null,
									isset($_REQUEST['data']) ? $_REQUEST['data'] : null
									);
		
				$data =  $Module->Output();
					
				if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'json') {
					$content = ob_get_contents();
					ob_end_clean();
					if(isset($data["json"])) $data = $data["json"]; 
					if(!empty($content)) $data["output"] = $content;
					$data = CF::ArrMap($data, function($str){ return CF::Win2Utf($str); }); 
					$data = json_encode($data);
				}
				
				$MODULE_OUTPUT = $this->output["data"] = $data;
				if(isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'html_code') {
					include_once THEMES_DIR . $row->name . TEMPLATE_EXT;
					$this->output["mode"] = "html_code";
					exit;
				}
			}
		} 
	
	}

	function Output()
	{  
		return $this->output;
	}
	
}